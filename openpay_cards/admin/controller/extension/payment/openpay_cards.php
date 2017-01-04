<?php

/**
 * @version Opencart v 2.0.1.1
 */
if (!defined('OWNER'))
    define('OWNER', 'Admin');

class ControllerExtensionPaymentOpenpayCards extends OpenpayCardsController {

    public function __construct($registry) {
        parent::__construct($registry);
    }

    public function index() {

        $this->language->load('extension/payment/openpay_cards');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

            $setting = $this->model_setting_setting->getSetting('openpay');
            $this->merge($setting, $this->request->post, true);

            $this->model_setting_setting->editSetting('openpay', $setting);
            $this->model_setting_setting->editSetting('openpay_cards', $setting);

            $this->session->data['success'] = $this->language->get('text_success');
            
            $this->response->redirect($this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true));
        }

        $data['heading_title'] = $this->language->get('heading_title');

        $data['tab_api'] = $this->language->get('tab_api');
        $data['tab_general'] = $this->language->get('tab_general');
        $data['tab_status'] = $this->language->get('tab_status');

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
        $data['entry_new_status'] = $this->language->get('entry_new_status');
        $data['entry_title'] = $this->language->get('entry_title');

        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');

        $data['help_title'] = $this->language->get('help_title');
        $data['help_total'] = sprintf($this->language->get('help_total'), $this->currency->format(MIN_TOTAL, $this->config->get('config_currency')));
        $data['help_charge'] = $this->language->get('help_charge');

        foreach ($this->error as $key => $val) {
            if (is_array($val)) {
                $data['error_' . $key] = implode('<br>', $val);
            } else {
                $data['error_' . $key] = $val;
            }
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_payment'),
            'href' => $this->url->link($this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true)),
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/payment/openpay_cards', 'token=' . $this->session->data['token'], 'SSL'),
        );

        $data['action'] = $this->url->link('extension/payment/openpay_cards', 'token=' . $this->session->data['token'], 'SSL');
        $data['cancel'] = $this->url->link('extension/extension', 'token=' . $this->session->data['token']. '&type=payment', true);        

        $data['openpay_card_test_merchant_id'] = $this->fillSetting('openpay_card_test_merchant_id');
        $data['openpay_card_live_merchant_id'] = $this->fillSetting('openpay_card_live_merchant_id');
        $data['openpay_card_test_public_key'] = $this->fillSetting('openpay_card_test_public_key');
        $data['openpay_card_test_secret_key'] = $this->fillSetting('openpay_card_test_secret_key');
        $data['openpay_card_live_public_key'] = $this->fillSetting('openpay_card_live_public_key');
        $data['openpay_card_live_secret_key'] = $this->fillSetting('openpay_card_live_secret_key');
        $data['openpay_card_test_mode'] = $this->fillSetting('openpay_card_test_mode');
        $data['openpay_card_new_status_id'] = $this->fillSetting('openpay_card_new_status_id');
        $data['openpay_card_title'] = $this->fillSetting('openpay_card_title', $this->language->get('text_title'));

        $this->load->model('localisation/order_status');

        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
        $data['openpay_cards_geo_zone_id'] = $this->fillSetting('openpay_cards_geo_zone_id');
        $data['currency_symbol_left'] = $this->currency->getSymbolLeft($this->config->get('config_currency'));
        $data['currency_symbol_right'] = $this->currency->getSymbolRight($this->config->get('config_currency'));

        $this->load->model('localisation/geo_zone');
        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        $data['openpay_cards_status'] = $this->fillSetting('openpay_cards_status');
        $data['openpay_card_total'] = $this->fillSetting('openpay_card_total');
        $data['openpay_cards_sort_order'] = $this->fillSetting('openpay_cards_sort_order');
        $data['openpay_charge'] = $this->fillSetting('openpay_charge', 1);
        $data['openpay_cards_geo_zone_id'] = $this->fillSetting('openpay_cards_geo_zone_id');

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/payment/openpay_cards.tpl', $data));
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/payment/openpay_cards')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if ($this->request->post['openpay_card_test_mode']) {

            if (empty($this->request->post['openpay_card_test_merchant_id'])) {
                $this->error['test_merchant_id'] = $this->language->get('error_test_merchant_id');
            }

            if (empty($this->request->post['openpay_card_test_secret_key'])) {
                $this->error['test_secret_key'] = $this->language->get('error_test_secret_key');
            }
            if (empty($this->request->post['openpay_card_test_public_key'])) {
                $this->error['test_public_key'] = $this->language->get('error_test_public_key');
            }

            if(!$this->getMerchantInfo($this->request->post['openpay_card_test_merchant_id'], $this->request->post['openpay_card_test_secret_key'], $this->request->post['openpay_card_test_mode'])){
                $this->error['test_merchant_account'] = $this->language->get('error_test_merchant_account');
            }

        } else {

            if (empty($this->request->post['openpay_card_live_merchant_id'])) {
                $this->error['live_merchant_id'] = $this->language->get('error_live_merchant_id');
            }

            if (empty($this->request->post['openpay_card_live_secret_key'])) {
                $this->error['live_secret_key'] = $this->language->get('error_live_secret_key');
            }
            if (empty($this->request->post['openpay_card_live_public_key'])) {
                $this->error['live_public_key'] = $this->language->get('error_live_public_key');
            }

            if(!$this->getMerchantInfo($this->request->post['openpay_card_live_merchant_id'], $this->request->post['openpay_card_live_secret_key'], $this->request->post['openpay_card_test_mode'])){
                $this->error['live_merchant_account'] = $this->language->get('error_live_merchant_account');
            }

        }

        if (!isset($this->request->post['openpay_card_total']) || (float) $this->request->post['openpay_card_total'] < (float) MIN_TOTAL) {
            $this->error['total'] = sprintf($this->language->get('error_total'), $this->currency->format(MIN_TOTAL, $this->config->get('config_currency')));
        }

        if ($this->isEmptyArray($this->error)) {
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
        $this->load->model('extension/payment/openpay_cards');
        $this->model_extension_payment_openpay_cards->install();        
    }

    public function uninstall() {
        //$this->load->model('extension/payment/openpay_cards');
        //$this->model_extension_payment_openpay_cards->uninstall();
        
        //$this->load->model('setting/setting');
        //$this->setting_setting->deleteSetting('openpay');
    }

    public function orderAction() {
        if (defined('PRO_MOD') && PRO_MOD && isset($this->request->get['order_id']) && ( $charge = $this->fetchCharge($this->request->get['order_id']) )) {
            $this->language->load('extension/payment/openpay_cards');
            $this->load->model('extension/payment/openpay_cards');

            $data['text_openpay_header'] = $this->language->get('text_openpay_header');
            $data['text_charge_id'] = $this->language->get('text_charge_id');
            $data['text_amount'] = $this->language->get('text_amount');
            $data['text_capture'] = $this->language->get('text_capture');
            $data['text_capturing'] = $this->language->get('text_capturing');
            $data['text_refund'] = $this->language->get('text_refund');
            $data['text_captured'] = $this->language->get('text_captured');
            $data['text_processing'] = $this->language->get('text_processing');
            $data['text_transaction'] = $this->language->get('text_transaction');
            $data['text_date'] = $this->language->get('text_date');
            $data['text_type'] = $this->language->get('text_type');
            $data['text_amount'] = $this->language->get('text_amount');
            $data['text_description'] = $this->language->get('text_description');
            $data['text_initiator'] = $this->language->get('text_initiator');
            $data['text_status'] = $this->language->get('text_status');
            $data['text_amount_refunded'] = $this->language->get('text_amount_refunded');
            $data['text_refunded'] = $this->language->get('text_refunded');
            $data['text_charge_refunded'] = $this->language->get('text_charge_refunded');

            $data['error_error'] = $this->language->get('error_error');

            $data['charge'] = $charge;
            $data['amount'] = $this->currency->format($this->minToCurrency($charge->amount, $charge->currency), $charge->currency);
            $data['amount_refunded'] = $this->currency->format($this->minToCurrency($charge->amount_refunded, $charge->currency), $charge->currency);
            $data['non_formatted_amount'] = $this->minToCurrency($charge->amount, $charge->currency);
            $data['non_formatted_amount_refunded'] = $this->minToCurrency($charge->amount_refunded, $charge->currency);
            $data['order_id'] = $this->request->get['order_id'];
            $data['txn'] = $this->model_extension_payment_openpay_cards->getTransactions(array('charge_ref' => $charge->id));
            $data['url_capture'] = HTTPS_SERVER . 'index.php?route=payment/openpay_cards/jsonCapture&token=' . $this->session->data['token'] . '&order_id=' . $this->request->get['order_id'];
            $data['url_refund'] = HTTPS_SERVER . 'index.php?route=payment/openpay_cards/jsonRefund&token=' . $this->session->data['token'] . '&order_id=' . $this->request->get['order_id'];

            return $this->load->view('extension/payment/openpay_cards_order', $data);
        }
    }

}

?>