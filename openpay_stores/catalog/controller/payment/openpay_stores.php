<?php

/**
 * @version Opencart v2.0.1.1
 */
if (!defined('OWNER'))
    define('OWNER', 'Customer');

class ControllerPaymentOpenpayStores extends OpenpayController {

    public function __construct($registry) {
        parent::__construct($registry);
    }

    public function index() {
        $this->language->load('payment/openpay_stores');

        unset($this->session->data['openpay_charge']);

        $data['continue'] = $this->url->link('payment/openpay_stores/confirm');

        $data['text_wait'] = $this->language->get('text_wait');

        $data['error_error'] = $this->language->get('error_error');
        $data['text_success_payment'] = $this->language->get('text_success_payment');

        $data['button_confirm'] = $this->language->get('button_confirm');
        $data['button_back'] = $this->language->get('button_back');

        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/openpay_stores.tpl')) {
            $this->template = $this->config->get('config_template') . '/template/payment/openpay_stores.tpl';
        } else {
            $this->template = 'default/template/payment/openpay_stores.tpl';
        }

        return $this->load->view($this->template, $data);
    }

    public function confirm() {

        $this->document->addScript('catalog/view/javascript/jquery/jquery-2.1.1.min.js');
        $this->document->addStyle('catalog/view/javascript/bootstrap/css/bootstrap.min.css');

        if (array_key_exists('payment_method', $this->session->data) && $this->session->data['payment_method']['code'] == 'openpay_stores') {


            $this->document->setTitle('Imprimir Recibo de Pago');

            $json = array();

            $this->load->model('checkout/order');
            $this->language->load('payment/openpay_stores');


            $this->load->model('payment/openpay_stores');
            $customer = $this->model_payment_openpay_stores->getCustomer($this->customer->getId());

            if ($customer == false) {
                $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
                $customer_data = array(
                    'name' => $order_info['payment_firstname'],
                    'last_name' => $order_info['payment_lastname'],
                    'email' => $order_info['email'],
                    'requires_account' => false
                );

                $customer = $this->createOpenpayCustomer($customer_data);

                if (isset($customer->error)) {
                    $json['error'] = $customer->error;
                    $this->response->setOutput(json_encode($json));
                    return;
                }
            } else {
                $customer = $this->getOpenpayCustomer($customer['openpay_customer_id']);
            }


            if (file_exists($this->sanitizePath(dirname(__FILE__) . 'openpay_stores_pro.php'))) {
                include $this->sanitizePath(dirname(__FILE__) . 'openpay_stores_pro.php');
            }


            if (array_key_exists('openpay_charge', $this->session->data)) {
                $charge = $this->getOpenpayCharge($customer, $this->session->data['openpay_charge']);
            } else {
                $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
                if ($this->currency->convert($order_info['total'], $order_info['currency_code'], $this->config->get('openpay_total_currency')) < (float) $this->config->get('openpay_total')) {
                    $json['error'] = $this->language->get('error_min_total');
                    $this->response->setOutput(json_encode($json));
                    return;
                }

                $amount = round($order_info['total'], 2);

                $deadline = $this->config->get('openpay_deadline');
                $due_date = date('Y-m-d\TH:i:s', strtotime('+ ' . $deadline . ' hours'));
                $charge_request = array(
                    'method' => 'store',
                    'currency' => 'mxn',
                    'amount' => $amount,
                    'description' => 'Order ID# ' . $this->session->data['order_id'],
                    'order_id' => $this->session->data['order_id'],
                    'due_date' => $due_date
                );
                $charge = $this->createOpenpayCharge($customer, $charge_request);


                if (isset($charge->error)) {
                    $json['error'] = $charge->error;
                    $this->response->setOutput(json_encode($json));
                    return;
                } else {
                    $this->session->data['openpay_charge'] = $charge->id;
                    $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('openpay_captured_status_id'));

                    $this->model_payment_openpay_stores->addOrder(array(
                        'order_id' => $charge->order_id,
                        'charge_ref' => $charge->id,
                        'capture_status' => $this->config->get('openpay_captured_status_id'),
                        'description' => $charge->description,
                        'total' => $charge->amount,
                        'currency_code' => $charge->currency,
                    ));

                    $this->debugLog->write("Order #" . $charge->order_id . " confirmed");
                }

                $this->clearCart();

            }

            $data['barcode_url'] = $charge->payment_method->barcode_url;
            $data['reference'] = $charge->payment_method->reference;
            $data['due_date'] = $this->getLongGlobalDateFormat($charge->due_date);
            $data['creation_date'] = $this->getLongGlobalDateFormat($charge->creation_date);
            $data['currency'] = $charge->currency;
            $data['amount'] = number_format($charge->amount, 2);
            $data['order_id'] = $charge->order_id;
            $data['email'] = $this->customer->getEmail();
            $data['logo'] = $this->config->get('config_ssl') . 'image/' . $this->config->get('config_logo');

            $this->load->language('checkout/success');

            $data['continue'] = $this->url->link('common/home');

            if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/openpay_receipt.tpl')) {
                $this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/payment/openpay_receipt.tpl', $data));
            } else {
                $this->response->setOutput($this->load->view('default/template/payment/openpay_receipt.tpl', $data));
            }
        }else{
            header('Location: '.$this->url->link('common/home', '', 'SSL'));
        }
    }

    public function clearCart() {

        if (isset($this->session->data['order_id'])) {
            $this->cart->clear();

            // Add to activity log
            $this->load->model('account/activity');

            if ($this->customer->isLogged()) {
                $activity_data = array(
                    'customer_id' => $this->customer->getId(),
                    'name' => $this->customer->getFirstName() . ' ' . $this->customer->getLastName(),
                    'order_id' => $this->session->data['order_id']
                );

                $this->model_account_activity->addActivity('order_account', $activity_data);
            } else {
                $activity_data = array(
                    'name' => $this->session->data['guest']['firstname'] . ' ' . $this->session->data['guest']['lastname'],
                    'order_id' => $this->session->data['order_id']
                );

                $this->model_account_activity->addActivity('order_guest', $activity_data);
            }

            unset($this->session->data['shipping_method']);
            unset($this->session->data['shipping_methods']);
            //unset($this->session->data['payment_method']);
            unset($this->session->data['payment_methods']);
            unset($this->session->data['guest']);
            unset($this->session->data['comment']);
            unset($this->session->data['order_id']);
            unset($this->session->data['coupon']);
            unset($this->session->data['reward']);
            unset($this->session->data['voucher']);
            unset($this->session->data['vouchers']);
            unset($this->session->data['totals']);
        }

        return;

    }

    public function webhook(){
        $objeto = file_get_contents('php://input');
        $json = json_decode($objeto);

        if(!count($json)>0)
            return true;

        if ($json->type == 'charge.succeeded' && $json->transaction->method == 'store') {
            $this->load->model('checkout/order');
            $this->model_checkout_order->addOrderHistory($json->transaction->order_id, $this->config->get('openpay_new_status_id'), '', true);
        }
    }


}

?>