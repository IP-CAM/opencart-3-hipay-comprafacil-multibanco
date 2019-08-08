<?php

class ModelExtensionPaymentHipayMultibanco extends Model {

    public function getMethod($address, $total) {
        $this->load->language('extension/payment/hipay_multibanco');

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int) $this->config->get('payment_hipay_multibanco_geo_zone_id') . "' AND country_id = '" . (int) $address['country_id'] . "' AND (zone_id = '" . (int) $address['zone_id'] . "' OR zone_id = '0')");

        if ($this->session->data['currency'] != "EUR") {
            $status = false;
        } elseif ($this->config->get('payment_hipay_professional_total_min') != "" && $this->config->get('payment_hipay_professional_total_min') > $total) {
            $status = false;
        } elseif ($this->config->get('payment_hipay_professional_total_max') != "" && $this->config->get('payment_hipay_professional_total_max') < $total) {
            $status = false;
        } elseif (!$this->config->get('payment_hipay_multibanco_geo_zone_id')) {
            $status = true;
        } elseif ($query->num_rows) {
            $status = true;
        } else {
            $status = false;
        }

        $method_data = array();

        if ($status) {
            $method_data = array(
                'code' => 'hipay_multibanco',
                'title' => $this->language->get('text_title'),
                'terms' => '',
                'sort_order' => $this->config->get('payment_hipay_multibanco_sort_order')
            );
        }

        return $method_data;
    }

    public function addMultibancoReference($order_info, $parameters, $result) {
        if ($this->config->get('payment_hipay_multibanco_sandbox')) {
            $sandbox = 1;
        } else {
            $sandbox = 0;
        }
        $this->db->query("INSERT INTO `" . DB_PREFIX . "hipay_multibanco` SET `order_id` = '" . (int) $order_info['order_id'] . "', `reference` = '" . $this->db->escape($result->reference) . "', `date_added` = now(), `date_modified` = now(), `processed` = '0', `expiry_days` = '" . $this->db->escape($parameters['timeLimitDays']) . "', `sandbox` = '" . $this->db->escape($sandbox) . "', `entity` = '" . $this->db->escape($result->entity) . "', `total` = '" . $this->db->escape($result->amountOut) . "'");
        return $this->db->getLastId();
    }

    public function getMultibancoReference($order_id) {
        $query = $this->db->query("SELECT order_id, reference, entity, date_added, expiry_days, total, sandbox, processed FROM `" . DB_PREFIX . "hipay_multibanco` WHERE `order_id` = '" . $order_id . "' LIMIT 1");
        return $query;
    }

    public function updateProcessMultibancoReference($order_id) {
        $this->db->query("UPDATE `" . DB_PREFIX . "hipay_multibanco` SET `processed` = '1' WHERE `order_id` = '" . $order_id . "' LIMIT 1");
    }

    public function logger($message) {
        if ($this->config->get('payment_hipay_multibanco_debug')) {
            $log = new Log('hipay_multibanco_' . date('Ymd') . '.log');
            $log->write($message);
        }
    }

}
