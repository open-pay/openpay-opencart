<?php

/**
 * @version Opencart v3.0.2.0
 */
if (!defined('OWNER'))
    define('OWNER', 'Customer');

class ControllerExtensionPaymentOpenpayCards extends Controller
{
    
    protected $processing_status_id = 2;
    protected $complete_status_id = 5;
    protected $pending_status_id = 1;
    protected $refunded_status_id = 11;

    public function index() {
        $this->language->load('extension/payment/openpay_cards');

        $data['action'] = $this->url->link('extension/payment/openpay_cards/confirm', '', true);

        $data['text_credit_card'] = $this->language->get('text_credit_card');
        $data['text_wait'] = $this->language->get('text_wait');

        $data['help_cvc_front'] = $this->language->get('help_cvc_front');
        $data['help_cvc_back'] = $this->language->get('help_cvc_back');

        $data['entry_cc_owner'] = $this->language->get('entry_cc_owner');
        $data['entry_cc_number'] = $this->language->get('entry_cc_number');
        $data['entry_cc_expire_date'] = $this->language->get('entry_cc_expire_date');
        $data['entry_cc_cvv2'] = $this->language->get('entry_cc_cvv2');
        $data['error_error'] = $this->language->get('error_error');
        $data['text_success_payment'] = $this->language->get('text_success_payment');

        $data['button_confirm'] = $this->language->get('button_confirm');
        $data['button_back'] = $this->language->get('button_back');

        $data['merchant_id'] = $this->getMerchantId();
        $data['public_key'] = $this->getPublicApiKey();
        $data['test_mode'] = $this->isTestMode();

        $data['months'] = array();

        $now = new dateTime('2000-01-01');
        for ($i = $now->format('n'), $interval = new DateInterval('P1M'); $i <= 12; $i++, $now->add($interval)) {
            $data['months'][] = array(
                'text' => $now->format('m'),
                'value' => $now->format('m'),
            );
        }

        $data['year_expire'] = array();

        $now = new dateTime;
        for ($i = $now->format('y'), $interval = new DateInterval('P1Y'), $stop = $i + 10; $i <= $stop; $i++, $now->add($interval)) {
            $data['year_expire'][] = array(
                'text' => $now->format('y'),
                'value' => $now->format('y'),
            );
        }

        $data['months_interest_free'] = $this->getMonthsInterestFree();
        $data['use_card_points'] = $this->useCardPoints();
        $data['save_cc'] = $this->canSaveCC() && $this->customer->isLogged();                
        $data['cc_options'] = $this->getCreditCardList();

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $data['total'] = $order_info['total'];

        return $this->load->view('extension/payment/openpay_cards', $data);
    }

    public function confirm() {
        $json = array('redirect' => false);

        if (empty($this->request->post['token'])) {
            $json['error'] = 'Missing token';
            $this->response->setOutput(json_encode($json));
            return;
        }

        if (empty($this->session->data['order_id'])) {
            $json['error'] = 'Missing order ID';
            $this->response->setOutput(json_encode($json));
            return;
        }

        $this->load->model('checkout/order');
        $this->language->load('extension/payment/openpay_cards');
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        if ($this->currency->convert($order_info['total'], $order_info['currency_code'], $this->config->get('sp_total_currency')) < (float) $this->config->get('sp_total')) {
            $json['error'] = $this->language->get('error_min_total');
            $this->response->setOutput(json_encode($json));
            return;
        }
        
        $amount = number_format((float)$order_info['total'], 2, '.', '');

        $this->load->model('extension/payment/openpay_cards');
        $customer = $this->model_extension_payment_openpay_cards->getCustomer($this->customer->getId());

        if ($customer == false) {
            $this->log->write("Create Openapy Customer");

            $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
            $customer_data = array(
                'name' => $order_info['payment_firstname'],
                'last_name' => $order_info['payment_lastname'],
                'email' => $order_info['email'],
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
            if (isset($customer->error)) {
                $json['error'] = $customer->error;
                $this->response->setOutput(json_encode($json));
                return;
            }
        }
        
        $capture_config = $this->config->get('payment_openpay_cards_capture')  === null ? '1' : $this->config->get('payment_openpay_cards_capture');
        $capture = $capture_config === '1' ? true : false;
        $this->log->write('$capture => '.json_encode($capture));
        
        $charge_request = array(
            'method' => 'card',
            'currency' => $this->config->get('config_currency'),
            'amount' => $amount,
            'source_id' => $this->request->post['token'],
            'device_session_id' => $this->request->post['device_session_id'],
            'description' => 'Order ID# '.$this->session->data['order_id'],
            'order_id' => $this->session->data['order_id'],
            'use_card_points' => $this->request->post['use_card_points'],
            'capture' => $capture == '1' ? true : false
        );

        if (isset($this->request->post['interest_free']) && $this->request->post['interest_free'] > 1) {
            $charge_request['payment_plan'] = array('payments' => (int) $this->request->post['interest_free']);
        }
        
        if ($this->config->get('payment_openpay_cards_charge_type') == '3d') {
            $charge_request['use_3d_secure'] = true;
            $charge_request['redirect_url'] = $this->config->get('config_url').'index.php?route=extension/payment/openpay_cards/confirm3d';            
        }      
        
        if (isset($this->request->post['save_cc'])) {
            $card_data = array(            
                'token_id' => $this->request->post['token'],            
                'device_session_id' => $this->request->post['device_session_id']
            );
            $card = $this->createCreditCard($customer, $card_data);
            
            if (isset($card->error)) {            
                $json['error'] = $card->error;
                $this->response->setOutput(json_encode($json));            
                return;
            }
            
            $charge_request['source_id'] = $card->id;
        }
        
        $charge = $this->createOpenpayCharge($customer, $charge_request);

        if (isset($charge->error)) {            
            if ($this->config->get('payment_openpay_cards_charge_type') == 'auth' && $charge->error_code == '3005') {
                $charge_request['use_3d_secure'] = true;
                $charge_request['redirect_url'] = $this->config->get('config_url').'index.php?route=extension/payment/openpay_cards/confirm3d';            
                $charge = $this->createOpenpayCharge($customer, $charge_request);                
                $this->setCustomOrder($charge);
                
                $json['redirect'] = true;
                $json['redirect_url'] = $charge->payment_method->url;                
                $json['success'] = $this->url->link('checkout/success', '', true);
                $this->response->setOutput(json_encode($json));                
                return;
            }
            
            $json['error'] = $charge->error;
            $this->response->setOutput(json_encode($json));            
        } else {
            $this->setCustomOrder($charge);
            
            if (isset($charge->payment_method) && $charge->payment_method->type == 'redirect') {
                $json['redirect'] = true;
                $json['redirect_url'] = $charge->payment_method->url;
                $this->log->write($json);                       
            }       
            
            $json['success'] = $this->url->link('checkout/success', '', true);
            $this->response->setOutput(json_encode($json));
        }        
    }
    
    /**
     * Confirma el cargo con 3D Secure
     */
    public function confirm3d() {        
        $charge_type = $this->config->get('payment_openpay_cards_charge_type');
        
        if (!isset($this->request->get['id']) || !in_array($charge_type, array('auth', '3d'))) {            
            $this->response->redirect($this->url->link('common/home', '', true));
        }
        
        $charge = $this->getOpenpayCharge($this->request->get['id']);        
        if ($charge->status !== 'completed') {
            $failed_status_id = 10;                        
            $comment = 'Validación con 3D Secure fallida';
            $notify = true;
            
            $this->load->model('checkout/order');        
            $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $failed_status_id, $comment, $notify);
            
            //$this->session->data['error'] = 'Pago fallido.';
            $this->clearCart();
            $this->response->redirect($this->url->link('account/order', '', true));
        }               
        
        $this->load->model('extension/payment/openpay_cards');
        $row = $this->model_extension_payment_openpay_cards->getOrderByCharge($this->request->get['id']);        
        $this->model_extension_payment_openpay_cards->updateTransactionStatus(array('trx_id' => $this->request->get['id'], 'type' => 'Update charge', 'status' => $charge->status));
        
        $this->load->model('checkout/order');        
        $this->model_checkout_order->addOrderHistory($row['order_id'], $this->config->get('payment_openpay_cards_order_status_id'), 'Pago con 3D Secure confirmado', true);                        
        
        $this->response->redirect($this->url->link('checkout/success', '', true));        
    }
    
    
    private function setCustomOrder($charge) {        
        $capture_config = $this->config->get('payment_openpay_cards_capture')  === null ? '1' : $this->config->get('payment_openpay_cards_capture');
        $capture = $capture_config === '1' ? true : false;
        
        $status_id = $capture ? $this->config->get('payment_openpay_cards_order_status_id') : $this->pending_status_id;
        $comment = $capture ? 'Cargo realizado' : 'Pre-autorización';
        $notify = true;        
        
        if (isset($charge->payment_method) && $charge->payment_method->type == 'redirect') {            
            $status_id = $this->pending_status_id; // Si se usa 3D secure se marca como "Pendiente" => (1)
            $comment = 'En espera de confirmación 3D Secure';
            $notify = false;
        }                
            
        $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $status_id, $comment, $notify);
        $this->model_extension_payment_openpay_cards->addOrder(array(
            //'order_id' => $charge->order_id,
            'order_id' => $this->session->data['order_id'],
            'charge_ref' => $charge->id,
            'capture_status' => $status_id,
            'description' => $charge->description,
            'total' => $charge->amount,
            'currency_code' => $charge->currency,
        ));

        $this->log->write("setCustomOrder #".$charge->order_id." confirmed");        
        
        return;
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
        if ($this->config->get('payment_openpay_cards_test_mode')) {
            return $this->config->get('payment_openpay_cards_test_merchant_id');
        }
        return $this->config->get('payment_openpay_cards_live_merchant_id');
    }
    
    private function getApiBaseUrl() {
        if ($this->isTestMode()) {
            return 'https://sandbox-api.openpay.mx/v1';
        } else {
            return 'https://api.openpay.mx/v1';
        }
    }
    
    private function isTestMode() {
        if ($this->config->get('payment_openpay_cards_test_mode') == '1') {
            return true;
        } else {
            return false;
        }
    }

    private function getSecretApiKey() {
        if ($this->config->get('payment_openpay_cards_test_mode')) {
            return $this->config->get('payment_openpay_cards_test_secret_key');
        }
        return $this->config->get('payment_openpay_cards_live_secret_key');
    }

    private function getPublicApiKey() {
        if ($this->config->get('payment_openpay_cards_test_mode')) {
            return $this->config->get('payment_openpay_cards_test_public_key');
        }
        return $this->config->get('payment_openpay_cards_live_public_key');
    }
    
    private function createOpenpayCustomer($customer_data, $oc_customer_id) {       
        try {            
            $customer = $this->openpayRequest('customers', 'POST', $customer_data);

            $this->load->model('extension/payment/openpay_cards');
            $this->model_extension_payment_openpay_cards->addTransaction(array('type' => 'Customer creation', 'customer_ref' => $customer->id));
            $this->model_extension_payment_openpay_cards->addCustomer(array('customer_id' => $oc_customer_id, 'openpay_customer_id' => $customer->id));
            return $customer;        
        } catch (Exception $e) {
            $result = new stdClass();
            $result->error = $this->error($e->getCode());
            return $result;
        }
    }
    
    private function getOpenpayCustomer($customer_id) {
        try {            
            $customer = $this->openpayRequest('customers/'.$customer_id, 'GET');
            return $customer;
        } catch (Exception $e) {
            $result = new stdClass();
            $result->error = $this->error($e->getCode());
            return $result;
        }        
    }
    
    private function createOpenpayCharge($customer, $charge_request) {
        try {                        
            $charge = $this->openpayRequest('customers/'.$customer->id.'/charges', 'POST', $charge_request);

            $this->load->model('extension/payment/openpay_cards');
            $this->model_extension_payment_openpay_cards->addTransaction(array('type' => 'Charge creation', 'charge_ref' => $charge->id, 'customer_ref' => $customer->id, 'amount' => $charge->amount, 'status' => $charge->status));

            return $charge;       
        } catch (Exception $e) {                        
            $result = new stdClass();
            $result->error = $this->error($e->getCode());
            $result->error_code = $e->getCode();
            return $result;
        }        
    }
    
    private function createCreditCard($customer, $data) {
        try {                        
            return $this->openpayRequest('customers/'.$customer->id.'/cards', 'POST', $data);            
        } catch (Exception $e) {                        
            $result = new stdClass();
            $result->error = $this->error($e->getCode());
            $result->error_code = $e->getCode();
            return $result;
        }        
    }
    
    private function getCreditCardList() {
        if (!$this->customer->isLogged()) {            
            return array(array('value' => 'new', 'name' => 'Nueva tarjeta'));
        }        
                
        $this->load->model('extension/payment/openpay_cards');
        $customer = $this->model_extension_payment_openpay_cards->getCustomer($this->customer->getId());
        
        if ($customer == false) {
            return array(array('value' => 'new', 'name' => 'Nueva tarjeta'));
        } 
        
        $list = array(array('value' => 'new', 'name' => 'Nueva tarjeta'));        
        try {            
            //$card = $this->openpayRequest('customers/'.$customer->id.'/cards', 'POST', $data);
            $openpay_customer = $this->getOpenpayCustomer($customer['openpay_customer_id']);            
            
            $cards = $this->getCreditCards($openpay_customer);            
            foreach ($cards as $card) {                
                array_push($list, array('value' => $card->id, 'name' => strtoupper($card->brand).' '.$card->card_number));
            }
            
            return $list;            
        } catch (Exception $e) {
            $result = new stdClass();
            $result->error = $this->error($e->getCode());
            $result->error_code = $e->getCode();
            return $result;
        }        
    }
    
    private function getCreditCards($customer) {        
        try {
            return $this->openpayRequest('customers/'.$customer->id.'/cards?offset=0&limit=10', 'GET');            
        } catch (Exception $e) {
            throw $e;
        }        
    }
    
    private function capture($trx_id, $total) {
        try {                        
            $charge = $this->openpayRequest('charges/'.$trx_id.'/capture', 'POST', array('amount' => $total));

            $this->load->model('extension/payment/openpay_cards');
            $this->model_extension_payment_openpay_cards->updateTransactionStatus(array('trx_id' => $trx_id, 'type' => 'Confirmed charge', 'status' => $charge->status));                        

            return $charge;       
        } catch (Exception $e) {                        
            throw $e;
        }        
    }
    
    private function refund($trx_id, $total, $comment = '') {
        try {                        
            $charge = $this->openpayRequest('charges/'.$trx_id.'/refund', 'POST', array('amount' => $total, 'description' => $comment));

            $this->load->model('extension/payment/openpay_cards');
            $this->model_extension_payment_openpay_cards->updateTransactionStatus(array('trx_id' => $trx_id, 'type' => 'Refunded charge', 'status' => $charge->status));                        

            return $charge;       
        } catch (Exception $e) {                        
            throw $e;
        }        
    }
    
    private function getOpenpayCharge($trx_id) {
        try {                        
            return $this->openpayRequest('charges/'.$trx_id, 'GET');            
        } catch (Exception $e) {            
            $result = new stdClass();
            $result->error = $this->error($e->getCode());
            return $result;
        }        
    }
    
    private function error($code) {
        //6001 el webhook ya existe

        switch ($code) {
            //ERRORES GENERALES
            case "1000":
                $msg = "Servicio no disponible.";
                break;

            case "1001":
                $msg = "Los campos no tienen el formato correcto, o la petición no tiene campos que son requeridos.";
                break;

            case "1004":
                $msg = "Servicio no disponible.";
                break;

            case "1005":
                $msg = "Servicio no disponible.";
                break;

            //ERRORES ALMACENAMIENTO
            case "2004":
                $msg = "El dígito verificador del número de tarjeta es inválido de acuerdo al algoritmo Luhn.";
                break;

            case "2005":
                $msg = "La fecha de expiración de la tarjeta es anterior a la fecha actual.";
                break;

            case "2006":
                $msg = "El código de seguridad de la tarjeta (CVV2) no fue proporcionado.";
                break;

            //ERRORES TARJETA
            case "3001":
                $msg = "La tarjeta fue rechazada.";
                break;

            case "3002":
                $msg = "La tarjeta ha expirado.";
                break;

            case "3003":
                $msg = "La tarjeta no tiene fondos suficientes.";
                break;

            case "3004":
                $msg = "La tarjeta fue rechazada.";
                break;

            case "3005":
                $msg = "La tarjeta fue rechazada.";
                break;

            case "3006":
                $msg = "La operación no esta permitida para este cliente o esta transacción.";
                break;

            case "3007":
                $msg = "Deprecado. La tarjeta fue declinada.";
                break;

            case "3008":
                $msg = "La tarjeta no es soportada en transacciones en línea.";
                break;

            case "3009":
                $msg = "La tarjeta fue reportada como perdida.";
                break;

            case "3010":
                $msg = "El banco ha restringido la tarjeta.";
                break;

            case "3011":
                $msg = "El banco ha solicitado que la tarjeta sea retenida. Contacte al banco.";
                break;

            case "3012":
                $msg = "Se requiere solicitar al banco autorización para realizar este pago.";
                break;

            case "6002":
                $msg = "Ha ocurrido un error al crear el webhook. Verifica en tu panel de Openpay que este haya sido creado, es necesario instalarlo para recibir notificaciones de pago.";
                break;

            default: //Demás errores 400
                $msg = "La petición no pudo ser procesada.";
                break;
        }

        return 'ERROR '.$code.'. '.$msg;
    }
    
    private function getMonthsInterestFree() {
        if ($this->config->get('payment_openpay_cards_interest_free')) {
            return $this->config->get('payment_openpay_cards_interest_free');
        } else {
            return array();
        }
    }
    
    private function useCardPoints() {
        return $this->config->get('payment_openpay_cards_use_card_points');
    }
    
    private function canSaveCC() {
        return $this->config->get('payment_openpay_cards_save_cc') == '1' ? true : false;
    }
    
    private function clearCart() {
        if (isset($this->session->data['order_id'])) {
            $this->cart->clear();
           
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
    
    public function eventAddOrderHistory($route, $args, $output) {   
        $order_id = (int) $args[0];
        
        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($order_id);
        
        // Validación de metódo de pago con tarjetas únicamente
        if ($order_info['payment_code'] != 'openpay_cards') {
            return;
        }
        
        $this->log->write('#eventAddOrderHistory Event fired: ' . $route);        
        $this->log->write('Input json_encode => '.json_encode($args));   
        
        $order_status_id = (int) $args[1]; 
        $comment = $args[2];        
        
        $this->load->model('extension/payment/openpay_cards');
        $openpay_order = $this->model_extension_payment_openpay_cards->getOrder($order_id);
        
        // Aún no se ha registrado la orden o se utiliza otro método de pago
        if ($openpay_order === null) {
            $this->log->write('Asignación inicial del estatus de la orden');
            return;
        }
        
        try {
            $charge = $this->getOpenpayCharge($openpay_order['charge_ref']);
            $this->log->write('Openpay charge_status => '.$charge->status);   
            $this->log->write('Openpay refunds_property_exists => '.json_encode(array('exists' => property_exists($charge, 'refunds'))));   
            
            
            // Capturar OC Pre-autorizadas
            if ($charge->status == 'in_progress' && in_array($order_status_id, array($this->complete_status_id, $this->processing_status_id))) {            
                $this->log->write('capture trx_id => '.$openpay_order['charge_ref']);

                $this->capture($openpay_order['charge_ref'], $openpay_order['total']);    
                                
                $this->model_checkout_order->addOrderHistory($order_id, $order_status_id, 'Cargo capturado exitosamente - Openpay', false);
            }

            // Realizar reembolso total
            if ($charge->status == 'completed' && !property_exists($charge, 'refunds') && $order_status_id == $this->refunded_status_id) {
                $this->log->write('refund trx_id => '.$openpay_order['charge_ref']);

                $refund = $this->refund($openpay_order['charge_ref'], $openpay_order['total'], $comment); 
                
                if (property_exists($refund, 'refunds')) {
                    $this->model_checkout_order->addOrderHistory($order_id, $order_status_id, 'Cargo reembolsado - Openpay', false);
                }                                                
            }  
            
            return;
        } catch (Exception $e) {
            $this->log->write('#eventAddOrderHistory ERROR => '. json_encode(array(
                'message' => $e->getMessage(), 
                'file' => $e->getFile(), 
                'line' => $e->getLine())
            ));
            
            //$this->model_checkout_order->addOrderHistory($order_id, $order_status_id, 'ERROR - '.$e->getMessage(), false);
//            $this->response->addHeader('Content-Type: application/json');
//            $this->response->setOutput(json_encode(array('error' => 'ERROR - '.$e->getMessage())));    
            throw $e;
        }              
    }

}

?>