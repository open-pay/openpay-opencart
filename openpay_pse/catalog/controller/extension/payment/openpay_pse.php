<?php

/**
 * @version Opencart v3.0.2.0
 */
if (!defined('OWNER'))
    define('OWNER', 'Customer');

class ControllerExtensionPaymentOpenpayPse extends Controller {
    
    public function index() {
        $this->language->load('extension/payment/openpay_pse');

        unset($this->session->data['openpay_charge']);

        $data['action'] = $this->url->link('extension/payment/openpay_pse/confirm');

        $data['text_wait'] = $this->language->get('text_wait');

        $data['error_error'] = $this->language->get('error_error');
        $data['text_success_payment'] = $this->language->get('text_success_payment');

        $data['button_confirm'] = $this->language->get('button_confirm');
        $data['button_back'] = $this->language->get('button_back');

        return $this->load->view('extension/payment/openpay_pse', $data); 
    }

    public function confirm() {        
        if (array_key_exists('payment_method', $this->session->data) && $this->session->data['payment_method']['code'] == 'openpay_pse') {

            $this->document->setTitle('Imprimir Recibo de Pago');

            $json = array();

            if (empty($this->session->data['order_id'])) {
                $json['error'] = 'Missing order ID';
                $this->response->setOutput(json_encode($json));
                return;
            }

            $this->load->model('account/customer');
            $this->load->model('checkout/order');
            $this->language->load('extension/payment/openpay_pse');            
            
            $this->load->model('extension/payment/openpay_pse');
            
            $customer = false;
            if ($this->customer->isLogged()) {
                $customer = $this->model_extension_payment_openpay_pse->getCustomer($this->customer->getId());
            }
            
            if ($customer == false) {
                $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
                $customer_data = array(
                    'name' => $order_info['payment_firstname'],
                    'last_name' => $order_info['payment_lastname'],
                    'email' => $order_info['email'],
                    'phone_number' => $order_info['telephone'],
                    'requires_account' => false
                );

                if ($this->validateAddress($order_info)) {
                    $customer_data['customer_address'] = array(
                        'department' => $order_info['payment_zone'],
                        'city' => $order_info['payment_city'],
                        'additional' => $order_info['payment_address_1'].' '.$order_info['payment_address_2']
                    );
                }

                $customer = $this->createOpenpayCustomer($customer_data, $this->customer->getId());

                if (isset($customer->error)) {
                    $json['error'] = $customer->error;
                    $this->response->setOutput(json_encode($json));
                    return;
                }
            } else {
                $customer = $this->getOpenpayCustomer($customer['openpay_customer_id']);
            }

            if (array_key_exists('openpay_charge', $this->session->data)) {
                $charge = $this->getOpenpayCharge($customer, $this->session->data['openpay_charge']);
            } else {
                $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
                if ($this->currency->convert($order_info['total'], $order_info['currency_code'], $this->config->get('openpay_total_currency')) < (float) $this->config->get('payment_openpay_pse_total')) {
                    $json['error'] = $this->language->get('error_min_total');
                    $this->response->setOutput(json_encode($json));
                    return;
                }
                
                $amount = number_format((float)$order_info['total'], 2, '.', '');


                $charge_request = array(
                    'method' => 'bank_account',
                    'currency' => 'COP',
                    'amount' => $amount,
                    'description' => 'Order ID# ' . $this->session->data['order_id'],
                    'order_id' => $this->session->data['order_id'],
                    'iva' => $this->config->get('payment_openpay_pse_iva'),
                    'redirect_url' => $this->config->get('config_url').'index.php?route=extension/payment/openpay_pse/confirmPse'
                );
                $charge = $this->createOpenpayCharge($customer, $charge_request);


                if (isset($charge->error)) {
                    $json['error'] = $charge->error;
                    $this->response->setOutput(json_encode($json));
                    return;
                } else {
                    $pending_status_id = 1;
                    $this->session->data['openpay_charge'] = $charge->id;
                    $comment = 'En espera de pago PSE';
                    $notify = false;                   

                    $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $pending_status_id, $comment, $notify);

                    $this->model_extension_payment_openpay_pse->addOrder(array(
                        'order_id' => $this->session->data['order_id'],
                        'charge_ref' => $charge->id,
                        'capture_status' => $pending_status_id,
                        'description' => $charge->description,
                        'total' => $charge->amount,
                        'currency_code' => $charge->currency,
                    ));

                    $json['redirect'] = true;
                    $json['redirect_url'] = $charge->payment_method->url;    
                    $json['success'] = $this->url->link('checkout/success', '', true);

                    $this->log->write($json);

                    $this->response->setOutput(json_encode($json));

                }
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
            
            $this->load->model('account/customer');

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

    private function validateAddress($order_info) {
        if ($order_info['payment_address_1'] && $order_info['payment_city'] && $order_info['payment_zone']) {
            return true;
        }
        return false;
    }

    /**
     * Confirma el cargo con PSE
    */

    public function confirmPse(){
        $charge = $this->getOpenpayCharge($this->request->get['id']);

        if (isset($charge->error_code)) {
            $failed_status_id = 10;                        
            $comment = $charge->error;
            $notify = true;
            
            $this->load->model('checkout/order');        
            $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $failed_status_id, $comment, $notify);
            
            $this->clearCart();
            $this->response->redirect($this->url->link('checkout/failure', '', true));
        }

        if ($charge->status !== 'completed') {
            $failed_status_id = 10;                        
            $comment = 'Pago PSE Fallido';
            $notify = true;
            
            $this->load->model('checkout/order');        
            $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $failed_status_id, $comment, $notify);
            
            $this->clearCart();
            $this->response->redirect($this->url->link('checkout/failure', '', true));
        }

        $this->load->model('extension/payment/openpay_pse');
        $row = $this->model_extension_payment_openpay_pse->getOrderByCharge($this->request->get['id']);        
        $this->model_extension_payment_openpay_pse->updateTransactionStatus(array('trx_id' => $this->request->get['id'], 'type' => 'Update charge', 'status' => $charge->status));
        
        $this->load->model('checkout/order');        
        $this->model_checkout_order->addOrderHistory($row['order_id'], $this->config->get('payment_openpay_pse_order_status_id'), 'Pago PSE confirmado', true);                        
        
        $this->response->redirect($this->url->link('checkout/success', '', true));

    }

    public function webhook(){
        $objeto = file_get_contents('php://input');
        $this->log->write('#webhook => '.$objeto);
        $json = json_decode($objeto);

        if(!$json) {
            return true;
        }
        
        $charge = $this->getOpenpayCharge($json->transaction->id);

        if ($charge->method == 'bank_account') {
            if ($json->type == 'charge.succeeded' && $charge->status == 'completed') {
                $comment = 'Pago recibido.';
                $notify = true;
                $this->load->model('checkout/order');
                $this->model_checkout_order->addOrderHistory($charge->order_id, $this->config->get('payment_openpay_pse_order_status_id'), $comment, $notify);
            }else if($json->type == 'transaction.expired' && $charge->status == 'cancelled'){
                $comment = 'Pago vencido.';
                $notify = true;
                $expired_status_id = 14;
                $this->load->model('checkout/order');
                $this->model_checkout_order->addOrderHistory($charge->order_id, $expired_status_id , $comment, $notify);
            }
        }
    }
    
    /**
     * Send requests to Openpay's API
     *     
     * @param string $resource    
     * @param string $method 
     * @param array $params
     */
    private function openpayRequest($resource, $method, $params = null) {
        $abs_url = $this->getApiBaseUrl().'/'.$this->getMerchantId().'/';
        $abs_url .= $resource;

        $username = $this->getSecretApiKey();
        $password = "";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $abs_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_USERAGENT, "Openpay-CARTCO/v2");         
                
        if ($params !== null) {            
            $data_string = json_encode($params);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);            
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: '.strlen($data_string))
            );
        }
        
        $result = curl_exec($ch);
        curl_close($ch);
        
        $this->log->write(array('method' => $method, 'url' => $abs_url, 'username' => $username, 'params' => json_encode($params), 'decode' => json_decode($result, true)));

        $response = json_decode($result);
        if (isset($response->error_code)) {
            throw new Exception($response->description, $response->error_code);
        }
        
        return $response;
    }
    
    private function getMerchantId() {
        if ($this->config->get('payment_openpay_pse_mode')) {
            return $this->config->get('payment_openpay_pse_test_merchant_id');
        }
        return $this->config->get('payment_openpay_pse_live_merchant_id');
    }
    
    private function getApiBaseUrl() {
        if ($this->isTestMode()) {
            return 'https://sandbox-api.openpay.co/v1';
        } else {
            return 'https://api.openpay.co/v1';
        }
    }
    
    private function isTestMode() {
        if ($this->config->get('payment_openpay_pse_mode') == '1') {
            return true;
        } else {
            return false;
        }
    }

    private function getSecretApiKey() {
        if ($this->config->get('payment_openpay_pse_mode')) {
            return $this->config->get('payment_openpay_pse_test_secret_key');
        }
        return $this->config->get('payment_openpay_pse_live_secret_key');
    }    
    
    private function createOpenpayCustomer($customer_data, $oc_customer_id) {       
        try {            
            $customer = $this->openpayRequest('customers', 'POST', $customer_data);
            
            $this->load->model('account/customer');            
            $this->load->model('extension/payment/openpay_pse');
            
            if ($this->customer->isLogged()) {                
                $this->model_extension_payment_openpay_pse->addCustomer(array('customer_id' => $oc_customer_id, 'openpay_customer_id' => $customer->id));                
            }
                        
            return $customer;        
        } catch (Exception $e) {
            $result = new stdClass();
            $result->error = $e->getMessage();
            return $result;
        }
    }
    
    private function getOpenpayCustomer($customer_id) {
        try {            
            $customer = $this->openpayRequest('customers/'.$customer_id, 'GET');
            return $customer;
        } catch (Exception $e) {
            $result = new stdClass();
            $result->error = $e->getMessage();
            return $result;
        }        
    }
    
    private function createOpenpayCharge($customer, $charge_request) {
        try {                        
            $charge = $this->openpayRequest('customers/'.$customer->id.'/charges', 'POST', $charge_request);

            $this->load->model('extension/payment/openpay_pse');
            $this->model_extension_payment_openpay_pse->addTransaction(array('type' => 'Charge creation', 'customer_ref' => $customer->id, 'charge_ref' => $charge->id, 'amount' => $charge->amount, 'status' => $charge->status));

            return $charge;       
        } catch (Exception $e) {                        
            $result = new stdClass();
            $result->error = $e->getMessage();
            $result->error_code = $e->getCode();
            return $result;
        }        
    }
    
    private function getOpenpayCharge($trx_id) {
        try {                        
            return $this->openpayRequest('charges/'.$trx_id, 'GET');            
        } catch (Exception $e) {            
            $result = new stdClass();
            $result->error = $e->getMessage();
            return $result;
        }        
    }

}

?>