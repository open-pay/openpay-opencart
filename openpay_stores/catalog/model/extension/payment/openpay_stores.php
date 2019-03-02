<?php

/**
 * @version Opencart v3.0.2.0
 */
class ModelExtensionPaymentOpenpayStores extends OpenpayModel {

    public function getMethod($address, $total) {
        $this->language->load('extension/payment/openpay_stores');
        $this->load->model('localisation/currency');
        
        $this->log->write('#ModelExtensionPaymentOpenpayStores config_currency => '.$this->config->get('config_currency'));
        
        // Método de pago disponible únicamente para MXN        
        if ($this->config->get('config_currency') != 'MXN') {
            return array();
        }                   
        
        // Si la venta es mayor a $10,000 MXN el método no es mostrado
        if ($total >= 10000) {
            return array();
        }
                
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int) $this->config->get('payment_openpay_stores_geo_zone_id') . "' AND country_id = '" . (int) $address['country_id'] . "' AND (zone_id = '" . (int) $address['zone_id'] . "' OR zone_id = '0')");

        if ($this->config->get('payment_openpay_stores_total') > 0 && $this->config->get('payment_openpay_stores_total') > $total) {
            $status = false;
        } elseif (!$this->config->get('payment_openpay_stores_geo_zone_id')) {
            $status = true;
        } elseif ($query->num_rows) {
            $status = true;
        } else {
            $status = false;
        }

        $method_data = array();

        if (true) {
            $method_data = array(
                'code' => 'openpay_stores',
                'title' => $this->config->has('payment_openpay_stores_title') ? $this->config->get('payment_openpay_stores_title') : $this->language->get('text_title'),
                'sort_order' => $this->config->get('payment_openpay_stores_stores_sort_order'),
                'terms' => '',
            );
        }

        return $method_data;
    }

}

?>