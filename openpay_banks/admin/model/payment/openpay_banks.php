<?php

class ModelPaymentOpenpayBanks extends OpenpayModel {

    public function createOrderTable() {
        $this->db->query("CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "openpay_order (
				openpay_order_id INT(11) UNSIGNED AUTO_INCREMENT,
				order_id INT(11) UNSIGNED,
				charge_ref VARCHAR(255),
				date_added DATETIME,
				date_modified TIMESTAMP,
				capture_status TINYINT(1),
				description VARCHAR(255),
				total INT(11),
				currency_code VARCHAR(3),
				PRIMARY KEY(openpay_order_id),
				INDEX(charge_ref,order_id))
				ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin");
    }

    public function deleteOrderTable() {
        $this->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "openpay_order");
    }

    public function createTransactionTable() {
        $this->db->query("CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "openpay_transaction (
				transaction_id INT(11) UNSIGNED AUTO_INCREMENT,
				transaction_ref VARCHAR(255),
				`type` VARCHAR(255),
				date_added TIMESTAMP,
				amount INT(11),
				description VARCHAR(255),
				initiator VARCHAR(70),
				customer_ref VARCHAR(255),
				source_ref VARCHAR(255),
				plan_ref VARCHAR(255),
				subscription_ref VARCHAR(255),
				charge_ref VARCHAR(255),
				invoice_ref VARCHAR(255),
				refund_ref VARCHAR(255),
				event_ref VARCHAR(255),
				`status` VARCHAR(255),
				PRIMARY KEY(transaction_id))
				ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin");
    }

    public function deleteTransactionTable() {
        $this->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "openpay_transaction");
    }

    public function createCustomerTable() {
        $this->db->query("CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "openpay_customer (
				id INT(11) UNSIGNED AUTO_INCREMENT,
				customer_id INT(11),
				openpay_customer_id VARCHAR(20),
				date_added TIMESTAMP,
				PRIMARY KEY(id))
				ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin");
    }

    public function deleteCustomerTable() {
        $this->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "openpay_customer");
    }

}
