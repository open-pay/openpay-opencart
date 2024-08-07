<?php

/**
 * @version Opencart v3.0.2.0
 */
if (!defined('OWNER'))
    define('OWNER', 'Customer');

class ControllerExtensionPaymentOpenpayBanks extends Controller {
    
    public function index() {
        $this->language->load('extension/payment/openpay_banks');

        unset($this->session->data['openpay_charge']);

        $data['continue'] = $this->url->link('extension/payment/openpay_banks/confirm');

        $data['text_wait'] = $this->language->get('text_wait');

        $data['error_error'] = $this->language->get('error_error');
        $data['text_success_payment'] = $this->language->get('text_success_payment');

        $data['button_confirm'] = $this->language->get('button_confirm');
        $data['button_back'] = $this->language->get('button_back');

        return $this->load->view('extension/payment/openpay_banks', $data); 
    }

    public function confirm() {        
        if (array_key_exists('payment_method', $this->session->data) && $this->session->data['payment_method']['code'] == 'openpay_banks') {

            $this->document->setTitle('Imprimir Recibo de Pago');

            $json = array();

            if (empty($this->session->data['order_id'])) {
                $json['error'] = 'Missing order ID';
                $this->response->setOutput(json_encode($json));
                return;
            }

            $this->load->model('account/customer');
            $this->load->model('checkout/order');
            $this->language->load('extension/payment/openpay_banks');            
            
            $this->load->model('extension/payment/openpay_banks');
            
            $customer = false;
            if ($this->customer->isLogged()) {
                $customer = $this->model_extension_payment_openpay_banks->getCustomer($this->customer->getId());
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
                if ($this->currency->convert($order_info['total'], $order_info['currency_code'], $this->config->get('openpay_total_currency')) < (float) $this->config->get('payment_openpay_banks_total')) {
                    $json['error'] = $this->language->get('error_min_total');
                    $this->response->setOutput(json_encode($json));
                    return;
                }
                
                $amount = number_format((float)$order_info['total'], 2, '.', '');

                $deadline = $this->config->get('payment_openpay_banks_deadline');

                $due_date = date('Y-m-d\TH:i:s', strtotime('+' . $deadline . ' hours'));
                
                $origin_channel = 'PLUGIN_OPENCART';

                $charge_request = array(
                    'method' => 'bank_account',
                    'currency' => 'mxn',
                    'amount' => $amount,
                    'description' => 'Order ID# ' . $this->session->data['order_id'],
                    'order_id' => $this->session->data['order_id'],
                    'origin_channel' => $origin_channel
                );

                if ($deadline != "") {
                    $charge_request['due_date'] = $due_date;
                }
                $charge = $this->createOpenpayCharge($customer, $charge_request);


                if (isset($charge->error)) {
                    $json['error'] = $charge->error;
                    $this->response->setOutput(json_encode($json));
                    return;
                } else {
                    $pending_status_id = 1;
                    $this->session->data['openpay_charge'] = $charge->id;
                    $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $pending_status_id);

                    $this->model_extension_payment_openpay_banks->addOrder(array(
                        'order_id' => $charge->order_id,
                        'charge_ref' => $charge->id,
                        'capture_status' => $pending_status_id,
                        'description' => $charge->description,
                        'total' => $charge->amount,
                        'currency_code' => $charge->currency,
                    ));
                    
                    $this->sendReceipt($order_info, $this->getPdfUrl($charge));

                    $this->log->write("Order #" . $charge->order_id . " confirmed");
                }

                $this->clearCart();
            }

            
            $data['pdf'] = $this->getPdfUrl($charge);

            $this->load->language('checkout/success');

            $data['continue'] = $this->url->link('common/home');

            $this->response->setOutput($this->load->view('extension/payment/openpay_banks_receipt', $data));                       
        }else{
            header('Location: '.$this->url->link('common/home', '', 'SSL'));
        }
    }
    
    private function getPdfUrl($charge) {
        $pdf_base_url = $this->isTestMode() ? 'https://sandbox-dashboard.openpay.mx/spei-pdf' : 'https://dashboard.openpay.mx/spei-pdf';
        return $pdf_base_url.'/'.$this->getMerchantId().'/'.$charge->id;
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
                $this->model_checkout_order->addOrderHistory($charge->order_id, $this->config->get('payment_openpay_banks_order_status_id'), $comment, $notify);
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
        curl_setopt($ch, CURLOPT_USERAGENT, "Openpay-CARTMX/v2");         
                
        if ($params !== null) {            
            $data_string = json_encode($params);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);            
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: '.strlen($data_string),
                'X-Forwarded-For: '. $this->getClientIp())
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
        if ($this->config->get('payment_openpay_banks_mode')) {
            return $this->config->get('payment_openpay_banks_test_merchant_id');
        }
        return $this->config->get('payment_openpay_banks_live_merchant_id');
    }
    
    private function getApiBaseUrl() {
        if ($this->isTestMode()) {
            return 'https://sandbox-api.openpay.mx/v1';
        } else {
            return 'https://api.openpay.mx/v1';
        }
    }
    
    private function isTestMode() {
        if ($this->config->get('payment_openpay_banks_mode') == '1') {
            return true;
        } else {
            return false;
        }
    }

    private function getSecretApiKey() {
        if ($this->config->get('payment_openpay_banks_mode')) {
            return $this->config->get('payment_openpay_banks_test_secret_key');
        }
        return $this->config->get('payment_openpay_banks_live_secret_key');
    }    
    
    private function createOpenpayCustomer($customer_data, $oc_customer_id) {       
        try {            
            $customer = $this->openpayRequest('customers', 'POST', $customer_data);
            
            $this->load->model('account/customer');            
            $this->load->model('extension/payment/openpay_banks');
            
            if ($this->customer->isLogged()) {                
                $this->model_extension_payment_openpay_banks->addCustomer(array('customer_id' => $oc_customer_id, 'openpay_customer_id' => $customer->id));                
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

            $this->load->model('extension/payment/openpay_banks');
            $this->model_extension_payment_openpay_banks->addTransaction(array('type' => 'Charge creation', 'customer_ref' => $customer->id, 'charge_ref' => $charge->id, 'amount' => $charge->amount, 'status' => $charge->status));

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
    
    private function sendReceipt ($order_info, $pdf_url) {      
        $path = DIR_UPLOAD.'payment_instructions_'.$order_info['order_id'].'.pdf';
        file_put_contents($path, file_get_contents($pdf_url));
        $this->log->write('#sendReceipt => '.$path);                   
        
        $data['logo'] = $order_info['store_url'] . 'image/' . $this->config->get('config_logo');
        $data['store_name'] = $order_info['store_name'];
        $data['store_url'] = $order_info['store_url'];
        $data['order_id'] = $order_info['order_id'];        
        $data['link'] = $order_info['store_url'] . 'index.php?route=account/order/info&order_id=' . $order_info['order_id'];
        
        $this->load->model('setting/setting');
        $from = $this->model_setting_setting->getSettingValue('config_email', $order_info['store_id']);		
        if (!$from) {
            $from = $this->config->get('config_email');
        }
        
        $mail = new Mail($this->config->get('config_mail_engine'));
        $mail->parameter = $this->config->get('config_mail_parameter');
        $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
        $mail->smtp_username = $this->config->get('config_mail_smtp_username');
        $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
        $mail->smtp_port = $this->config->get('config_mail_smtp_port');
        $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

        $mail->setTo($order_info['email']);
        $mail->setFrom($from);
        $mail->setSender(html_entity_decode($order_info['store_name'], ENT_QUOTES, 'UTF-8'));
        $mail->setSubject('Instrucciones de pago - Orden #'.$order_info['order_id']);
        $mail->setHtml($this->load->view('extension/payment/openpay_banks_mail', $data));
        $mail->addAttachment($path);
        $mail->send();
    }

    private function getClientIp() {
        // Recogemos la IP de la cabecera de la conexión
        if (!empty($_SERVER['HTTP_CLIENT_IP']))   
        {
          $ipAdress = $_SERVER['HTTP_CLIENT_IP'];
        }
        // Caso en que la IP llega a través de un Proxy
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))  
        {
          $ipAdress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        // Caso en que la IP lleva a través de la cabecera de conexión remota
        else
        {
          $ipAdress = $_SERVER['REMOTE_ADDR'];
        }
        return $ipAdress;
      }
}

?>