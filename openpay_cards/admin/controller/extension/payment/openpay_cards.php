<?php

/**
 * @version Opencart v 3.0.2.0
 */
if (!defined('OWNER'))
    define('OWNER', 'Admin');

class ControllerExtensionPaymentOpenpayCards extends Controller {

    private $error = array(); // This is used to set the errors, if any.
    
    public function index() {
        $min_total = 1;
        $this->language->load('extension/payment/openpay_cards');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {            
            $this->model_setting_setting->editSetting('payment_openpay_cards', $this->request->post);

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
        
        if (isset($this->error['test_public_key'])) {
            $data['error_test_public_key'] = $this->error['test_public_key'];
        } else {
            $data['error_test_public_key'] = '';
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
        
        if (isset($this->error['live_public_key'])) {
            $data['error_live_public_key'] = $this->error['live_public_key'];
        } else {
            $data['error_live_public_key'] = '';
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

        $data['entry_test_merchant_id'] = $this->language->get('entry_test_merchant_id');
        $data['entry_live_merchant_id'] = $this->language->get('entry_live_merchant_id');
        $data['entry_test_secret_key'] = $this->language->get('entry_test_secret_key');
        $data['entry_test_public_key'] = $this->language->get('entry_test_public_key');
        $data['entry_live_secret_key'] = $this->language->get('entry_live_secret_key');
        $data['entry_live_public_key'] = $this->language->get('entry_live_public_key');
        $data['entry_mode'] = $this->language->get('entry_mode');
        $data['entry_method'] = $this->language->get('entry_method');
        $data['entry_total'] = $this->language->get('entry_total');
        $data['entry_order_status'] = $this->language->get('entry_order_status');
        $data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
        $data['entry_status'] = $this->language->get('entry_status');
        $data['entry_sort_order'] = $this->language->get('entry_sort_order');
        $data['entry_completed_status'] = $this->language->get('entry_completed_status');        
        $data['entry_title'] = $this->language->get('entry_title');

        // ADD

        $data['entry_country'] = $this->language->get('entry_country');
        $data['entry_iva'] = $this->language->get('entry_iva');

        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');

        $data['help_title'] = $this->language->get('help_title');
        $data['help_installments'] = $this->language->get('help_installments');
        $data['help_iva'] = $this->language->get('help_iva');
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
            'href' => $this->url->link('extension/payment/openpay_cards', 'user_token=' . $this->session->data['user_token'], true),
        );

        $data['action'] = $this->url->link('extension/payment/openpay_cards', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token']. '&type=payment', true);    
                        
        $data['payment_openpay_cards_test_merchant_id'] = $this->fillSetting('payment_openpay_cards_test_merchant_id');
        $data['payment_openpay_cards_live_merchant_id'] = $this->fillSetting('payment_openpay_cards_live_merchant_id');
        $data['payment_openpay_cards_test_public_key'] = $this->fillSetting('payment_openpay_cards_test_public_key');
        $data['payment_openpay_cards_test_secret_key'] = $this->fillSetting('payment_openpay_cards_test_secret_key');
        $data['payment_openpay_cards_live_public_key'] = $this->fillSetting('payment_openpay_cards_live_public_key');
        $data['payment_openpay_cards_live_secret_key'] = $this->fillSetting('payment_openpay_cards_live_secret_key');
        $data['payment_openpay_cards_mode'] = $this->fillSetting('payment_openpay_cards_mode');
        $data['payment_openpay_cards_order_status_id'] = $this->fillSetting('payment_openpay_cards_order_status_id');
        $data['payment_openpay_cards_title'] = $this->fillSetting('payment_openpay_cards_title', $this->language->get('text_title'));
        $data['payment_openpay_cards_iva'] = $this->fillSetting('payment_openpay_cards_iva');
        
        $this->load->model('localisation/order_status');

        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
        $data['currency_symbol_left'] = $this->currency->getSymbolLeft($this->config->get('config_currency'));
        $data['currency_symbol_right'] = $this->currency->getSymbolRight($this->config->get('config_currency'));

        $this->load->model('localisation/geo_zone');
        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        $data['payment_openpay_cards_status'] = $this->fillSetting('payment_openpay_cards_status');
        $data['payment_openpay_cards_total'] = $this->fillSetting('payment_openpay_cards_total');
        $data['payment_openpay_cards_sort_order'] = $this->fillSetting('payment_openpay_cards_sort_order');
        $data['payment_openpay_cards_geo_zone_id'] = $this->fillSetting('payment_openpay_cards_geo_zone_id');
        
        /*
         * MESES SIN INTERESES
         */
        $data['months_interest_free'] = array('3' => '3 meses', '6' => '6 meses', '9' => '9 meses', '12' => '12 meses', '18' => '18 meses'); // Se definen los meses disponibles         
        if (isset($this->request->post['payment_openpay_cards_interest_free'])) {
            $data['payment_openpay_cards_interest_free'] = $this->request->post['payment_openpay_cards_interest_free'];        
        } elseif ($this->config->get('payment_openpay_cards_interest_free')) {
            $data['payment_openpay_cards_interest_free'] = $this->config->get('payment_openpay_cards_interest_free');        
        } else {
            $data['openpay_card_interest_free'] = array();
        } 
        
        /*
         * Cuotas
         */
        $data['installments'] = $this->getInstallments();
        if (isset($this->request->post['payment_openpay_cards_installments'])) {
            $data['payment_openpay_cards_installments'] = $this->request->post['payment_openpay_cards_installments'];        
        } elseif ($this->config->get('payment_openpay_cards_installments')) {
            $data['payment_openpay_cards_installments'] = $this->config->get('payment_openpay_cards_installments');        
        } else {
            $data['payment_openpay_cards_installments'] = array();
        } 
        
        // Tipo de cargo
        $data['charge_types'] = array('direct' => 'Directo', 'auth' => 'Autenticación selectiva', '3d' => '3D Secure'); 
        $data['payment_openpay_cards_charge_type'] = $this->fillSetting('payment_openpay_cards_charge_type');  
        $data['help_charge_types'] = '<p>* ¿Qué es cargo directo? Openpay se encarga de validar la operación y recharzarla cuando detecta riesgo.</p>
            <p>* ¿Qué es la autenticación selectiva? Es cuando el banco se encarga de validar la autenticidad del cuentahabiente, solo si Openpay detecta riesta en la operación.</p>
            <p>* ¿Qué es 3D Secure? El banco se encargará de validar su autenticidad del cuentahabiente en todas las operaciones.</p>';
        
        $data['use_card_points'] = $this->fillSetting('payment_openpay_cards_use_card_points', '0'); 
        $data['capture'] = $this->fillSetting('payment_openpay_cards_capture', '1');
        $data['country'] = $this->fillSetting('payment_openpay_cards_country', 'MX');
        $data['save_cc'] = $this->fillSetting('payment_openpay_cards_save_cc', '0');
        
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/payment/openpay_cards', $data));
    }

    public function getInstallments() {
        $installments = [];
        for($i=2; $i <= 36; $i++) {
            $installments[$i] = $i.' cuotas';
        }
        
        return $installments;
    }

    protected function validate() {
        $min_total = 1;
        //$this->model_setting_event->addEvent('openpay_cards_add_order', 'catalog/model/checkout/order/addOrderHistory/after', 'extension/payment/openpay_cards/eventAddOrderHistory');
        if (!$this->user->hasPermission('modify', 'extension/payment/openpay_cards')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        $country = $this->request->post['payment_openpay_cards_country'];
        if ($this->request->post['payment_openpay_cards_mode']) {
            if (empty($this->request->post['payment_openpay_cards_test_merchant_id'])) {
                $this->error['test_merchant_id'] = $this->language->get('error_test_merchant_id');
            }

            if (empty($this->request->post['payment_openpay_cards_test_secret_key'])) {
                $this->error['test_secret_key'] = $this->language->get('error_test_secret_key');
            }
            if (empty($this->request->post['payment_openpay_cards_test_public_key'])) {
                $this->error['test_public_key'] = $this->language->get('error_test_public_key');
            }

            if(!$this->getMerchantInfo($this->request->post['payment_openpay_cards_test_merchant_id'], $this->request->post['payment_openpay_cards_test_secret_key'], $this->request->post['payment_openpay_cards_mode'], $country)){
                $this->error['test_merchant_account'] = $this->language->get('error_test_merchant_account');
            }

        } else {
            if (empty($this->request->post['payment_openpay_cards_live_merchant_id'])) {
                $this->error['live_merchant_id'] = $this->language->get('error_live_merchant_id');
            }

            if (empty($this->request->post['payment_openpay_cards_live_secret_key'])) {
                $this->error['live_secret_key'] = $this->language->get('error_live_secret_key');
            }
            if (empty($this->request->post['payment_openpay_cards_live_public_key'])) {
                $this->error['live_public_key'] = $this->language->get('error_live_public_key');
            }

            if(!$this->getMerchantInfo($this->request->post['payment_openpay_cards_live_merchant_id'], $this->request->post['payment_openpay_cards_live_secret_key'], $this->request->post['payment_openpay_cards_mode'], $country)){
                $this->error['live_merchant_account'] = $this->language->get('error_live_merchant_account');
            }
        }

        if (!isset($this->request->post['payment_openpay_cards_total']) || (float) $this->request->post['payment_openpay_cards_total'] < (float) $min_total) {
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

    public function install() {        
        $this->load->model('setting/event');
        $this->load->model('extension/payment/openpay_cards');
        
        $this->model_setting_event->addEvent('openpay_cards_add_order_history', 'catalog/model/checkout/order/addOrderHistory/after', 'extension/payment/openpay_cards/eventAddOrderHistory');
        $this->model_extension_payment_openpay_cards->install();        
    }

    public function uninstall() {        
        $this->load->model('setting/event');        
        $this->model_setting_event->deleteEventByCode('openpay_cards_add_order_history');
        
        //$this->load->model('extension/payment/openpay_cards');
        //$this->model_extension_payment_openpay_cards->uninstall();
        
        //$this->load->model('setting/setting');
        //$this->setting_setting->deleteSetting('openpay');
    }
    
    private function fillSetting($setting_name, $default = '') {        
        return isset($this->request->post[$setting_name]) ? trim($this->request->post[$setting_name]) : ( $this->config->has($setting_name) ? trim($this->config->get($setting_name)) : $default );
    }

    private function getMerchantInfo($id, $sk, $mode, $country) {
        if($country === 'MX'){
            $url_base = $mode ? "https://sandbox-api.openpay.mx/v1" : "https://api.openpay.mx/v1";
        }else if($country === 'CO'){
            $url_base = $mode ? "https://sandbox-api.openpay.co/v1" : "https://api.openpay.co/v1";
        }

        $url = $url_base."/".trim($id);

        $username = trim($sk);
        $password = "";        

        $ch = curl_init();        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
                  
        $result = curl_exec($ch);
        curl_close($ch);

        $array = json_decode($result, true);
        if (array_key_exists('id', $array)) {
            return true;
        } else {
            return false;
        }
    }    

}

?>