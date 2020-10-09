<?php

/**
 * @version Opencart v3.0.2.0
 */
class ModelExtensionPaymentOpenpayPse extends OpenpayModel {

    public function getMethod($address, $total) {
        $this->language->load('extension/payment/openpay_pse');
        $this->load->model('localisation/currency');
        
        // Método de pago disponible únicamente para COP
        if ($this->config->get('config_currency') != 'COP') {
            return array();
        }

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int) $this->config->get('payment_openpay_pse_geo_zone_id') . "' AND country_id = '" . (int) $address['country_id'] . "' AND (zone_id = '" . (int) $address['zone_id'] . "' OR zone_id = '0')");

        if ($this->config->get('payment_openpay_pse_total') > 0 && $this->config->get('payment_openpay_pse_total') > $total) {
            $status = false;
        } elseif (!$this->config->get('payment_openpay_pse_geo_zone_id')) {
            $status = true;
        } elseif ($query->num_rows) {
            $status = true;
        } else {
            $status = false;
        }

        $method_data = array();

        if ($status) {
            $method_data = array(
                'code' => 'openpay_pse',
                'title' => $this->config->has('payment_openpay_pse_title') ? $this->config->get('payment_openpay_pse_title') : $this->language->get('text_title'),
                'sort_order' => $this->config->get('payment_openpay_pse_sort_order'),
                'terms' => '',
            );
        }

        return $method_data;
    }   

}

?>