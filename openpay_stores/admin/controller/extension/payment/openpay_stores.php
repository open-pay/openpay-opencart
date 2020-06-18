<?php

/**
 * @version Opencart v3.0.2.0
 */
if (!defined('OWNER'))
    define('OWNER', 'Admin');

class ControllerExtensionPaymentOpenpayStores extends Controller {
    
    public function index() {
        $min_total = 1;
        $this->language->load('extension/payment/openpay_stores');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {      
            $setting = $this->model_setting_setting->getSetting('payment_openpay_stores');
            $this->merge($setting, $this->request->post, true);
            
            $mode = $this->request->post['payment_openpay_stores_test_mode'] ? 'test' : 'live';            
            $webhook = $this->createWebhook($mode);
            if(!isset($webhook->error) && $webhook !== false){
                $setting['payment_openpay_stores_'.$mode.'_webhook'] = $webhook->id;
            }
            
            $this->model_setting_setting->editSetting('payment_openpay_stores', $setting);            
            $this->session->data['success'] = $this->language->get('text_success');            
            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
        }
        
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }
        
        if (isset($this->error['test_merchant_id'])) {
            $data['error_test_merchant_id'] = $this->error['test_merchant_id'];
        } else {
            $data['error_test_merchant_id'] = '';
        }
        
        if (isset($this->error['test_secret_key'])) {
            $data['error_test_secret_key'] = $this->error['test_secret_key'];
        } else {
            $data['error_test_secret_key'] = '';
        }
        
        if (isset($this->error['test_merchant_account'])) {
            $data['error_test_merchant_account'] = $this->error['test_merchant_account'];
        } else {
            $data['error_test_merchant_account'] = '';
        }
                
        if (isset($this->error['live_merchant_id'])) {
            $data['error_live_merchant_id'] = $this->error['live_merchant_id'];
        } else {
            $data['error_live_merchant_id'] = '';
        }
        
        if (isset($this->error['live_secret_key'])) {
            $data['error_live_secret_key'] = $this->error['live_secret_key'];
        } else {
            $data['error_live_secret_key'] = '';
        }               
        
        if (isset($this->error['live_merchant_account'])) {
            $data['error_live_merchant_account'] = $this->error['live_merchant_account'];
        } else {
            $data['error_live_merchant_account'] = '';
        }
        
        if (isset($this->error['total'])) {
            $data['error_total'] = $this->error['total'];
        } else {
            $data['error_total'] = '';
        }

        $data['heading_title'] = $this->language->get('heading_title');

        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_all_zones'] = $this->language->get('text_all_zones');
        $data['text_test'] = $this->language->get('text_test');
        $data['text_live'] = $this->language->get('text_live');
        $data['text_authorization'] = $this->language->get('text_authorization');
        $data['text_charge'] = $this->language->get('text_charge');
        $data['text_test_mode'] = $this->language->get('text_test_mode');
        $data['text_debug_mode'] = $this->language->get('text_debug_mode');
        $data['text_yes'] = $this->language->get('text_yes');
        $data['text_no'] = $this->language->get('text_no');
        $data['text_default_payment_mode'] = $this->language->get('text_default_payment_mode');
        $data['text_one_step_mode'] = $this->language->get('text_one_step_mode');
        $data['text_two_step_mode'] = $this->language->get('text_two_step_mode');
        $data['text_wait_page_load'] = $this->language->get('text_wait_page_load');
        $data['text_form'] = $this->language->get('text_form');

        $data['entry_mode'] = $this->language->get('entry_mode');
        $data['entry_method'] = $this->language->get('entry_method');
        $data['entry_total'] = $this->language->get('entry_total');
        $data['entry_order_status'] = $this->language->get('entry_order_status');        
        $data['entry_status'] = $this->language->get('entry_status');
        $data['entry_sort_order'] = $this->language->get('entry_sort_order');
        $data['entry_ipn'] = $this->language->get('entry_ipn');
        $data['entry_captured_status'] = $this->language->get('entry_captured_status');
        $data['entry_completed_status'] = $this->language->get('entry_completed_status');            

        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');

        $data['help_title'] = $this->language->get('help_title');
        $data['help_total'] = sprintf($this->language->get('help_total'), $this->currency->format($min_total, $this->config->get('config_currency')));
        $data['help_charge'] = $this->language->get('help_charge');

        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', 'user_token=' . $this->session->data['user_token'], true),
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_payment'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true),
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/payment/openpay_stores', 'user_token=' . $this->session->data['user_token'], true),
        );

        $data['action'] = $this->url->link('extension/payment/openpay_stores', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token']. '&type=payment', true);

        $data['payment_openpay_stores_test_merchant_id'] = $this->fillSetting('payment_openpay_stores_test_merchant_id');
        $data['payment_openpay_stores_live_merchant_id'] = $this->fillSetting('payment_openpay_stores_live_merchant_id');        
        $data['payment_openpay_stores_test_secret_key'] = $this->fillSetting('payment_openpay_stores_test_secret_key');        
        $data['payment_openpay_stores_live_secret_key'] = $this->fillSetting('payment_openpay_stores_live_secret_key');
        $data['payment_openpay_stores_deadline'] = $this->fillSetting('payment_openpay_stores_deadline');
        $data['payment_openpay_stores_test_mode'] = $this->fillSetting('payment_openpay_stores_test_mode');        
        $data['payment_openpay_stores_order_status_id'] = $this->fillSetting('payment_openpay_stores_order_status_id');
        $data['payment_openpay_stores_title'] = $this->fillSetting('payment_openpay_stores_title', $this->language->get('text_title'));

        $this->load->model('localisation/order_status');

        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();        
        $data['currency_symbol_left'] = $this->currency->getSymbolLeft($this->config->get('config_currency'));
        $data['currency_symbol_right'] = $this->currency->getSymbolRight($this->config->get('config_currency'));

        $this->load->model('localisation/geo_zone');
        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        $data['payment_openpay_stores_status'] = $this->fillSetting('payment_openpay_stores_status');
        $data['payment_openpay_stores_total'] = $this->fillSetting('payment_openpay_stores_total');
        $data['payment_openpay_stores_sort_order'] = $this->fillSetting('payment_openpay_stores_sort_order');        
        $data['payment_openpay_stores_geo_zone_id'] = $this->fillSetting('payment_openpay_stores_geo_zone_id');
        
        $data['show_map'] = $this->fillSetting('payment_openpay_stores_show_map', '0');

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/payment/openpay_stores', $data));
    }

    protected function validate() {
        $min_total = 1;
        if (!$this->user->hasPermission('modify', 'extension/payment/openpay_stores')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        
        if ($this->request->post['payment_openpay_stores_test_mode']) {
            
            if (empty($this->request->post['payment_openpay_stores_test_merchant_id'])) {
                $this->error['test_merchant_id'] = $this->language->get('error_test_merchant_id');
            }
            
            if (empty($this->request->post['payment_openpay_stores_test_secret_key'])) {
                $this->error['test_secret_key'] = $this->language->get('error_test_secret_key');
            }            
            
            if(!$this->getMerchantInfo($this->request->post['payment_openpay_stores_test_merchant_id'], $this->request->post['payment_openpay_stores_test_secret_key'], $this->request->post['payment_openpay_stores_test_mode'])){
                $this->error['test_merchant_account'] = $this->language->get('error_test_merchant_account');                
            }
            
        } else {
            
            if (empty($this->request->post['payment_openpay_stores_live_merchant_id'])) {
                $this->error['live_merchant_id'] = $this->language->get('error_live_merchant_id');
            }
            
            if (empty($this->request->post['payment_openpay_stores_live_secret_key'])) {
                $this->error['live_secret_key'] = $this->language->get('error_live_secret_key');
            }            
            
            if(!$this->getMerchantInfo($this->request->post['payment_openpay_stores_live_merchant_id'], $this->request->post['payment_openpay_stores_live_secret_key'], $this->request->post['payment_openpay_stores_test_mode'])){
                $this->error['live_merchant_account'] = $this->language->get('error_live_merchant_account');
            }
            
        }

        if (!isset($this->request->post['payment_openpay_stores_total']) || (float) $this->request->post['payment_openpay_stores_total'] < (float) $min_total) {
            $this->error['total'] = sprintf($this->language->get('error_total'), $this->currency->format($min_total, $this->config->get('config_currency')));
        }

        if (empty($this->error)) {
            return true;
        }

        if (!empty($this->error['warning'])) {
            if (is_array($this->error['warning'])) {
                $this->error['warning'][] = $this->language->get('error_correct_data');
            } else {
                $this->error['warning'] = array($this->error['warning'], $this->language->get('error_correct_data'),);
            }
        } else {
            $this->error['warning'] = $this->language->get('error_correct_data');
        }
        return false;
    }
    
    private function createWebhook($mode){        
        if(!$this->config->get('payment_openpay_stores_'.$mode.'_webhook')){                        
            $protocol = (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) ? 'https://' : 'http://';            
            $webhook_data = array(
                'url' =>  $protocol.$_SERVER['HTTP_HOST'].'/index.php?route=extension/payment/openpay_stores/webhook',
                'event_types' => array("verification","charge.succeeded","charge.created","charge.cancelled","charge.failed")
            );
                        
            try {
                return $this->openpayRequest('webhooks', 'POST', $webhook_data);
            } catch (Exception $e) {                
                //$this->session->data['error'] = $e->getMessage();
                return false;
            }
        }
        
        return false;
    }

    public function install() {
        $this->load->model('extension/payment/openpay_stores');
        $this->model_extension_payment_openpay_stores->install();        
    }

    public function uninstall() {
        //$this->load->model('extension/payment/openpay_stores');
        //$this->model_extension_payment_openpay_stores->uninstall();
        
        //$this->load->model('setting/setting');
        //$this->setting_setting->deleteSetting('openpay');
    }

    private function fillSetting($setting_name, $default = '') {        
        return isset($this->request->post[$setting_name]) ? trim($this->request->post[$setting_name]) : ( $this->config->has($setting_name) ? trim($this->config->get($setting_name)) : $default );
    }
    
    private function merge(Array &$target, Array $with, $rewrite = false) {
        foreach ($with as $key => $value) {
            if ($rewrite || !isset($target[$key])) {
                $target[$key] = $value;
            }
        }
    }

    private function getMerchantInfo($id, $sk, $mode) {
        $sandbox_url = "https://sandbox-api.openpay.mx/v1";
        $live_url = "https://api.openpay.mx/v1";

        $url = ($mode ? $sandbox_url : $live_url)."/".trim($id);

        $username = trim($sk);
        $password = "";        

        $ch = curl_init();        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_USERAGENT, "Openpay-CARTMX/v2"); 

        $result = curl_exec($ch);
        curl_close($ch);

        $array = json_decode($result, true);
        if (array_key_exists('id', $array)) {
            return true;
        } else {
            return false;
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
        if ($this->config->get('payment_openpay_stores_test_mode')) {
            return $this->config->get('payment_openpay_stores_test_merchant_id');
        }
        return $this->config->get('payment_openpay_stores_live_merchant_id');
    }
    
    private function getApiBaseUrl() {
        if ($this->isTestMode()) {
            return 'https://sandbox-api.openpay.mx/v1';
        } else {
            return 'https://api.openpay.mx/v1';
        }
    }
    
    private function isTestMode() {
        if ($this->config->get('payment_openpay_stores_test_mode') == '1') {
            return true;
        } else {
            return false;
        }
    }

    private function getSecretApiKey() {
        if ($this->config->get('payment_openpay_stores_test_mode')) {
            return $this->config->get('payment_openpay_stores_test_secret_key');
        }
        return $this->config->get('payment_openpay_stores_live_secret_key');
    }

}

?>