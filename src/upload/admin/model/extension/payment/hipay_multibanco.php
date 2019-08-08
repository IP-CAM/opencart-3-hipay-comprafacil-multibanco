<?php

class ModelExtensionPaymentHipayMultibanco extends Model {

    public function install() {
        $this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "hipay_multibanco` (
			  `hipay_multibanco_id` INT(11) NOT NULL AUTO_INCREMENT,
			  `order_id` INT(11) NOT NULL,
			  `reference` VARCHAR(20),
			  `entity` VARCHAR(10),
			  `date_added` DATETIME NOT NULL,
			  `date_modified` DATETIME NOT NULL,
			  `processed` TINYINT(1) DEFAULT 0,
			  `sandbox` TINYINT(1) DEFAULT 0,
			  `expiry_days` TINYINT(2) DEFAULT 0,
			  `total` DECIMAL( 10, 2 ) NOT NULL,
			  PRIMARY KEY (`hipay_multibanco_id`)
			) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;");
    }

    public function uninstall() {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "hipay_multibanco`;");
    }

    public function logger($message) {
        if ($this->config->get('payment_hipay_multibanco_debug')) {
            $log = new Log('hipay_multibanco_' . date('Ymd') . '.log');
            $log->write($message);
        }
    }

}
