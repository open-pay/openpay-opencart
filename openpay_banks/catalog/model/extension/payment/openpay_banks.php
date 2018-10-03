<?php

/**
 * @version Opencart v3.0.2.0
 */
class ModelExtensionPaymentOpenpayBanks extends OpenpayModel {

    public function getMethod($address, $total) {
        $this->language->load('extension/payment/openpay_banks');
        $this->load->model('localisation/currency');
        
        // Método de pago disponible únicamente para MXN
        if ($this->session->data['currency'] != 'MXN') {
            return array();
        }

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int) $this->config->get('payment_openpay_banks_geo_zone_id') . "' AND country_id = '" . (int) $address['country_id'] . "' AND (zone_id = '" . (int) $address['zone_id'] . "' OR zone_id = '0')");

        if ($this->config->get('payment_openpay_banks_total') > 0 && $this->config->get('payment_openpay_banks_total') > $total) {
            $status = false;
        } elseif (!$this->config->get('payment_openpay_banks_geo_zone_id')) {
            $status = true;
        } elseif ($query->num_rows) {
            $status = true;
        } else {
            $status = false;
        }

        $method_data = array();

        if ($status) {
            $method_data = array(
                'code' => 'openpay_banks',
                'title' => $this->config->has('payment_openpay_banks_title') ? $this->config->get('payment_openpay_banks_title') : $this->language->get('text_title'),
                'sort_order' => $this->config->get('payment_openpay_banks_sort_order'),
                'terms' => '',
            );
        }

        return $method_data;
    }   

}

?>