<?php

//Openpay Banks Controller 
class OpenpayBanksController extends MainController {

    protected $rebilling_periods;
    protected $available_ps;
    protected $decimalZero;
    protected $stripePlans;

    public function __construct($registry) {

        parent::__construct($registry);
        
        $minTotal = $this->currency->convert(0.5, 'USD', $this->currency->getCode());

        if (!defined('MODULE_CODE'))
            define('MODULE_CODE', 'OPENPAY');
        if (!defined('MODULE_NAME'))
            define('MODULE_NAME', 'openpay_banks');
        if (!defined('MIN_TOTAL'))
            define('MIN_TOTAL', $minTotal);
        if (!defined('TRANSACTION_CREATE_CUSTOMER'))
            define('TRANSACTION_CREATE_CUSTOMER', 'Customer creation');
        if (!defined('TRANSACTION_CREATE_CHARGE'))
            define('TRANSACTION_CREATE_CHARGE', 'Charge creation');
        if (!defined('TRANSACTION_CAPTURE_CHARGE'))
            define('TRANSACTION_CAPTURE_CHARGE', 'Charge capture');
        if (!defined('TRANSACTION_REFUND_CHARGE'))
            define('TRANSACTION_REFUND_CHARGE', 'Charge refund');
        if (!defined('TRANSACTION_CREATE_PLAN'))
            define('TRANSACTION_CREATE_PLAN', 'Plan creation');
        if (!defined('TRANSACTION_CREATE_SUBSCRIPTION'))
            define('TRANSACTION_CREATE_SUBSCRIPTION', 'Subscription creation');
        if (!defined('TRANSACTION_CANCEL_SUBSCRIPTION'))
            define('TRANSACTION_CANCEL_SUBSCRIPTION', 'Subscription cancel');
        if (!defined('TRANSACTION_CREATE_INVOICE'))
            define('TRANSACTION_CREATE_INVOICE', 'Create invoice');
        if (!defined('TRANSACTION_PAID_INVOICE'))
            define('TRANSACTION_PAID_INVOICE', 'Invoice paid');
        if (!defined('TRANSACTION_CREATE_ORDER'))
            define('TRANSACTION_CREATE_ORDER', 'Order #%d was created');

        if (!defined('PRO_MODE'))
            define('PRO_MODE', false);

        $this->decimalZero = array('BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA', 'PYG', 'RWF', 'VND', 'VUV', 'XAF', 'XOF', 'XPF',);

        $this->available_ps = array('pp_express', 'openpay_banks');
    }

    protected function getMerchantId() {
        if ($this->config->get('openpay_bank_test_mode')) {
            return $this->config->get('openpay_bank_test_merchant_id');
        }
        return $this->config->get('openpay_bank_live_merchant_id');
    }
    
    public function getMerchantInfo($id, $sk, $mode){
        
        $sandbox_url = "https://sandbox-api.openpay.mx/v1";
        $live_url = "https://api.openpay.mx/v1";
        
        $file = $this->sanitizePath($this->request->server['DOCUMENT_ROOT'] . '/vendor/openpay/Openpay.php');
        if (file_exists($file)){
            require_once( $file );
        } else {
            $result = new stdClass();
            $result->error = 'Openpay library is missing';
            return $result;
        }
        
        $url = ($mode ? $sandbox_url : $live_url)."/".$id;
                
        $username = $sk;
        $password = "";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
        $result=curl_exec ($ch);
        curl_close ($ch);
        
        $array = json_decode($result, true);
        if (array_key_exists('id', $array)) {
            return true;
        }else{
            return false;
        }
        
    }
    
    protected function isProductionMode(){
        if ($this->config->get('openpay_bank_test_mode')) {
            return false;
        }  else {
            return true;
        }
    }

    protected function getSecretApiKey() {
        if ($this->config->get('openpay_bank_test_mode')) {
            return $this->config->get('openpay_bank_test_secret_key');
        }
        return $this->config->get('openpay_bank_live_secret_key');
    }

    protected function getPublicApiKey() {
        if ($this->config->get('openpay_bank_test_mode')) {
            return $this->config->get('openpay_bank_test_public_key');
        }
        return $this->config->get('openpay_bank_live_public_key');
    }

    public function getOpenpayCustomer($customer_id){
        $result = new stdClass();
        $file = $this->sanitizePath($this->request->server['DOCUMENT_ROOT'] . '/vendor/openpay/Openpay.php');
        if (file_exists($file)){
            require_once( $file );
        } else {
            $result->error = 'Openpay library is missing';
            return $result;
        }
        
        $sk = $this->getSecretApiKey();
        $id = $this->getMerchantId();
        
        $openpay = Openpay::getInstance($id, $sk);
        Openpay::setProductionMode($this->isProductionMode());   
        
        $customer = $openpay->customers->get($customer_id);
        return $customer;
    }
    
    public function createOpenpayCustomer($customer_data){
        $result = new stdClass();
        
        $file = $this->sanitizePath($this->request->server['DOCUMENT_ROOT'] . '/vendor/openpay/Openpay.php');
        if (file_exists($file)){
            require_once( $file );
        } else {
            $result->error = 'Openpay library is missing';
            return $result;
        }
        
        $sk = $this->getSecretApiKey();
        $id = $this->getMerchantId();
        
        $openpay = Openpay::getInstance($id, $sk);
        Openpay::setProductionMode($this->isProductionMode());   
        
        try {
            $customer = $openpay->customers->add($customer_data);
                
            $this->load->model('payment/openpay_banks');
            $this->model_payment_openpay_banks->addTransaction(array('type' => TRANSACTION_CREATE_CUSTOMER, 'customer_ref' => $customer->id));
            $this->model_payment_openpay_banks->addCustomer(array('customer_id' => 1, 'openpay_customer_id' => $customer->id));
            return $customer;
            
        } catch (OpenpayApiTransactionError $e) {
            $result->error = $this->error($e);
        } catch (OpenpayApiRequestError $e) {
            $result->error = $this->error($e);
        } catch (OpenpayApiConnectionError $e) {
            $result->error = $this->error($e);
        } catch (OpenpayApiAuthError $e) {
            $result->error = $this->error($e);
        } catch (OpenpayApiError $e) {
            $result->error = $this->error($e);
        } catch (Exception $e) {
            $result->error = $this->error($e);
        }
        
        return $result;
        
    }
    
    public function getOpenpayCharge($customer, $charge_id){
        $result = new stdClass();
        
        $file = $this->sanitizePath($this->request->server['DOCUMENT_ROOT'] . '/vendor/openpay/Openpay.php');
        if (file_exists($file)){
            require_once( $file );
        } else {
            $result->error = 'Openpay library is missing';
            return $result;
        }
        
        $sk = $this->getSecretApiKey();
        $id = $this->getMerchantId();
        
        $openpay = Openpay::getInstance($id, $sk);
        Openpay::setProductionMode($this->isProductionMode());
        
        try {
            $charge =$customer->charges->get($charge_id);
            return $charge;
        } catch (OpenpayApiTransactionError $e) {
            $result->error = $this->error($e);
        } catch (OpenpayApiRequestError $e) {
            $result->error = $this->error($e);
        } catch (OpenpayApiConnectionError $e) {
            $result->error = $this->error($e);
        } catch (OpenpayApiAuthError $e) {
            $result->error = $this->error($e);
        } catch (OpenpayApiError $e) {
            $result->error = $this->error($e);
        } catch (Exception $e) {
            $result->error = $this->error($e);
        }
        
        return $result;
    }
    
    public function createOpenpayCharge($customer, $chargeRequest) {
        $result = new stdClass();
        
        $file = $this->sanitizePath($this->request->server['DOCUMENT_ROOT'] . '/vendor/openpay/Openpay.php');
        if (file_exists($file)){
            require_once( $file );
        } else {
            $result->error = 'Openpay library is missing';
            return $result;
        }
        
        $sk = $this->getSecretApiKey();
        $id = $this->getMerchantId();
        
        $openpay = Openpay::getInstance($id, $sk);
        Openpay::setProductionMode($this->isProductionMode());
        
        try {
            $charge = $customer->charges->create($chargeRequest);
            
            $this->load->model('payment/openpay_banks');
            $this->model_payment_openpay_banks->addTransaction(array('type' => TRANSACTION_CREATE_CHARGE, 'charge_ref' => $charge->id, 'amount' => $charge->amount, 'status' => $charge->status));
            
            return $charge;
        } catch (OpenpayApiTransactionError $e) {
            $result->error = $this->error($e);
        } catch (OpenpayApiRequestError $e) {
            $result->error = $this->error($e);
        } catch (OpenpayApiConnectionError $e) {
            $result->error = $this->error($e);
        } catch (OpenpayApiAuthError $e) {
            $result->error = $this->error($e);
        } catch (OpenpayApiError $e) {
            $result->error = $this->error($e);
        } catch (Exception $e) {
            $result->error = $this->error($e);
        }
        
        return $result;
        
    }
    
    
    public function createOpenpayWebhook($webhook_data) {

        $result = new stdClass();
        
        $file = $this->sanitizePath($this->request->server['DOCUMENT_ROOT'] . '/vendor/openpay/Openpay.php');
        if (file_exists($file)) {
            require_once( $file );
        } else {
            $result->error = 'Openpay library is missing';
            return $result;
        }
        
        $sk = $this->getSecretApiKey();
        $id = $this->getMerchantId();

        $openpay = Openpay::getInstance($id, $sk);
        Openpay::setProductionMode($this->isProductionMode());

        try {
            $webhook = $openpay->webhooks->add($webhook_data);
            return $webhook;
        } catch (OpenpayApiTransactionError $e) {
            $result->error = $this->error($e);
        } catch (OpenpayApiRequestError $e) {
            $result->error = $this->error($e);
        } catch (OpenpayApiConnectionError $e) {
            $result->error = $this->error($e);
        } catch (OpenpayApiAuthError $e) {
            $result->error = $this->error($e);
        } catch (OpenpayApiError $e) {
            $result->error = $this->error($e);
        } catch (Exception $e) {
            $result->error = $this->error($e);
        }

        return $result;
    }
    
    
    public function error($e, $backend = false) {
        
        //6001 el webhook ya existe
        
        switch ($e->getErrorCode()){
            
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
                $msg = "La tarjeta ha sido identificada como una tarjeta robada.";
                break;
            
            case "3005":
                $msg = "La tarjeta ha sido rechazada por el sistema antifraudes.";
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
        
        $error = 'ERROR '.$e->getErrorCode().'. '.$msg;
        return $error;
        
    }
    
    
    public function getLongGlobalDateFormat($input)
    {
            $time = strtotime($input);

            $string_month = $this->getLongStringForMonth(date('n', $time));

            // Formato "12 de Julio de 2014, a las 6:36 PM"
            return date('j', $time).' de '.$string_month.' de '.date('Y', $time).', a las '.date('g:i A',$time);
    }
    
    
    public function getLongStringForMonth($month_number)
    {
            $months_array = array(
                    1 => 'Enero',
                    2 => 'Febrero',
                    3 => 'Marzo',
                    4 => 'Abril',
                    5 => 'Mayo',
                    6 => 'Junio',
                    7 => 'Julio',
                    8 => 'Agosto',
                    9 => 'Septiembre',
                    10 => 'Octubre',
                    11 => 'Noviembre',
                    12 => 'Diciembre'
            );

            return isset($months_array[$month_number]) ? $months_array[$month_number] : '';
    }
    

}

?>
