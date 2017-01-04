<?php

/**
 * @version Opencart v 2.0.1.1
 */
class ModelExtensionPaymentOpenpayStores extends OpenpayModel {

    public function getMethod($address, $total) {

        $this->language->load('extension/payment/openpay_stores');

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int) $this->config->get('openpay_geo_zone_id') . "' AND country_id = '" . (int) $address['country_id'] . "' AND (zone_id = '" . (int) $address['zone_id'] . "' OR zone_id = '0')");

        if ($this->config->get('openpay_total') > 0 && $this->config->get('openpay_total') > $total) {
            $status = false;
        } elseif (!$this->config->get('openpay_geo_zone_id')) {
            $status = true;
        } elseif ($query->num_rows) {
            $status = true;
        } else {
            $status = false;
        }

        $method_data = array();

        if ($status) {
            $method_data = array(
                'code' => 'openpay_stores',
                'title' => $this->config->has('openpay_title') ? $this->config->get('openpay_title') : $this->language->get('text_title'),
                'sort_order' => $this->config->get('openpay_stores_sort_order'),
                'terms' => '',
            );
        }

        return $method_data;
    }

    public function recurringPayments() {

        if (defined('PRO_MODE')) {
            return PRO_MODE;
        }
        return false;
    }

}

?>