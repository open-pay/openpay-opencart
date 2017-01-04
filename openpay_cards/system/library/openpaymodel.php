<?php

class OpenpayModel extends MainModel {

    public function addOrder($data) {
        if (empty($data['order_id'])) {
            $this->debugLod->throwError(__METHOD__ . ' : order ID missing');
            return false;
        }
        if (empty($data['charge_ref'])) {
            $this->debugLog->throwError(__METHOD__ . ' : charge refference missing');
            return false;
        }

        $data['capture_status'] = isset($data['capture_status']) ? $data['capture_status'] : '';
        $data['description'] = isset($data['description']) ? $data['description'] : '';
        $data['total'] = isset($data['total']) ? $data['total'] : '';
        $data['currency_code'] = isset($data['currency_code']) ? $data['currency_code'] : '';

        $this->debugLog->write(__METHOD__ . ' : Order data to record', $data);
        $this->db->query("INSERT INTO " . DB_PREFIX . "openpay_order SET order_id = " . (int) $data['order_id'] . ", charge_ref = '" . $this->db->escape($data['charge_ref']) . "', date_added = NOW(), capture_status = " . (int) $data['capture_status'] . ", description  = '" . $this->db->escape($data['description']) . "', total = '" . (float) $data['total'] . "', currency_code = '" . $this->db->escape($data['currency_code']) . "'");
        if ($this->db->countAffected()) {
            $this->debugLog->write("Order #{$this->db->getLastId()} added to DB");
            return $this->db->getLastId();
        }
        $this->debugLog->throwError(__METHOD__ . ' : Error while adding order to DB');
        return false;
    }

    public function getOrder($order_id) {
        $this->debugLog->write(__METHOD__ . "Fetching order with order_id #$order_id");
        $order = $this->db->query("SELECT * FROM " . DB_PREFIX . "openpay_order WHERE order_id = '" . (int) $order_id . "'");
        if ($order->num_rows) {
            $this->debugLog->write("Order #{$order_id} fetched", $order->row);
            return $order->row;
        }
        $this->debugLog->throwError("Order with order_id #'$order_id' not found");
        return null;
    }

    public function getOrderByCharge($charge_ref) {
        $this->debugLog->write(__METHOD__ . "Fetching order with charge referense #$charge_ref");
        $order = $this->db->query("SELECT * FROM " . DB_PREFIX . "openpay_order WHERE charge_ref = '" . $this->db->escape($charge_ref) . "'");
        if ($order->num_rows) {
            $this->debugLog->write("Order for charge #{$charge_ref} is fetched", $order->row);
            return $order->row;
        }
        $this->debugLog->throwError("Order with charge referense #'$charge_ref' not found");
        return null;
    }

    public function addTransaction($data) {
        $info = array(
            'transaction_ref' => isset($data['transaction_ref']) ? $data['transaction_ref'] : '',
            'type' => isset($data['type']) ? $data['type'] : '',
            'amount' => isset($data['amount']) ? $data['amount'] : 0,
            'description' => isset($data['description']) ? $data['description'] : '',
            'initiator' => OWNER,
            'customer_ref' => isset($data['customer_ref']) ? $data['customer_ref'] : '',
            'source_ref' => isset($data['source_ref']) ? $data['source_ref'] : '',
            'plan_ref' => isset($data['plan_ref']) ? $data['plan_ref'] : '',
            'subscription_ref' => isset($data['subscription_ref']) ? $data['subscription_ref'] : '',
            'charge_ref' => isset($data['charge_ref']) ? $data['charge_ref'] : '',
            'invoice_ref' => isset($data['invoice_ref']) ? $data['invoice_ref'] : '',
            'refund_ref' => isset($data['refund_ref']) ? $data['refund_ref'] : '',
            'event_ref' => isset($data['refund_ref']) ? $data['refund_ref'] : '',
            'status' => isset($data['status']) ? $data['status'] : '',
        );
        $this->debugLog->write("Record transaction", $info);
        $this->db->query("INSERT INTO " . DB_PREFIX . "openpay_transaction SET transaction_ref = '" . $this->db->escape($info['transaction_ref']) . "', amount = " . (float) $info['amount'] . ", description = '" . $this->db->escape($info['description']) . "', initiator = '" . $info['initiator'] . "', customer_ref = '" . $this->db->escape($info['customer_ref']) . "', source_ref = '" . $this->db->escape($info['source_ref']) . "', plan_ref = '" . $this->db->escape($info['plan_ref']) . "', subscription_ref = '" . $this->db->escape($info['subscription_ref']) . "', charge_ref = '" . $this->db->escape($info['charge_ref']) . "', refund_ref = '" . $this->db->escape($info['refund_ref']) . "', event_ref = '" . $this->db->escape($info['event_ref']) . "', `type` = '" . $data['type'] . "', `status` = '" . $this->db->escape($info['status']) . "', invoice_ref = '" . $this->db->escape($info['invoice_ref']) . "'");
        if ($this->db->countAffected()) {
            $this->debugLog->write("Transaction #{$this->db->getLastId()} added to DB");
            return $this->db->getLastId();
        }
        $this->debugLog->throwError("Error while adding transaction");
        return false;
    }

    public function getTransactions($data) {
        $q = "SELECT * FROM " . DB_PREFIX . "openpay_transaction";
        $qa = array();
        if (!empty($data['transaction_ref']))
            $qa[] = "transaction_ref='" . $this->db->escape($data['transaction_ref']) . "'";
        if (!empty($data['customer_ref']))
            $qa[] = "customer_ref='" . $this->db->escape($data['customer_ref']) . "'";
        if (!empty($data['source_ref']))
            $qa[] = "source_ref='" . $this->db->escape($data['source_ref']) . "'";
        if (!empty($data['plan_ref']))
            $qa[] = "plan_ref='" . $this->db->escape($data['plan_ref']) . "'";
        if (!empty($data['subscription_ref']))
            $qa[] = "subscription_ref='" . $this->db->escape($data['subscription_ref']) . "'";
        if (!empty($data['charge_ref']))
            $qa[] = "charge_ref='" . $this->db->escape($data['charge_ref']) . "'";
        if (!empty($data['refund_ref']))
            $qa[] = "refund_ref='" . $this->db->escape($data['refund_ref']) . "'";
        if (!empty($data['event_ref']))
            $qa[] = "event_ref='" . $this->db->escape($data['event_ref']) . "'";
        if (!empty($data['invoice_ref']))
            $qa[] = "invoice_ref='" . $this->db->escape($data['invoice_ref']) . "'";
        if (!empty($data['status']))
            $qa[] = "`status`='" . $this->db->escape($data['status']) . "'";
        if (!empty($qa))
            $q .= ' WHERE ' . implode(' AND ', $qa);

        $this->debugLog->write("Quering transactions with query $q");

        $txn = $this->db->query($q);

        $this->debugLog->write('Fetched ' . $txn->num_rows . ' transaction(s)');

        if ($txn->num_rows)
            return $txn->rows;
        return null;
    }

    public function addCustomer($data) {
        $info = array(
            'customer_id' => isset($data['customer_id']) ? $data['customer_id'] : '',
            'openpay_customer_id' => isset($data['openpay_customer_id']) ? $data['openpay_customer_id'] : ''
        );
        $this->debugLog->write("Record customer", $info);
        $this->db->query("INSERT INTO " . DB_PREFIX . "openpay_customer SET customer_id = '" . $this->db->escape($info['customer_id']) . "', openpay_customer_id = '" . $info['openpay_customer_id'] . "', date_added = NOW()");
        if ($this->db->countAffected()) {
            $this->debugLog->write("Customer #{$this->db->getLastId()} added to DB");
            return $this->db->getLastId();
        }
        $this->debugLog->throwError("Error while adding transaction");
        return false;
    }

    public function getCustomer($customer_id) {
        $customer = $this->db->query("SELECT * FROM " . DB_PREFIX . "openpay_customer WHERE customer_id = '" . $this->db->escape($customer_id) . "'");
        if ($customer->num_rows) {
            $this->debugLog->write("Customer $customer_id exists in DB");
            return $customer->row;
        }
        $this->debugLog->write("Customer $customer_id do not exists in DB");
        return false;
    }

}

?>