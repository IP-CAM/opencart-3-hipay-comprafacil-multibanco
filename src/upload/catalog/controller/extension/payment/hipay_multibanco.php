<?php

class ControllerExtensionPaymentHipayMultibanco extends Controller {

    const HIPAY_ENTITY1_MULTIBANCO = "https://comprafacil1.hipay.pt";
    const HIPAY_ENTITY2_MULTIBANCO = "https://comprafacil2.hipay.pt";
    const HIPAY_GENERATE_MULTIBANCO_PRODUCTION = "/webservice/comprafacilWS.asmx?wsdl";
    const HIPAY_GENERATE_MULTIBANCO_SANDBOX = "/webservice-test/comprafacilWS.asmx?wsdl";

    private $endpoint;

    public function index() {

        $this->load->language('extension/payment/hipay_multibanco');

        $data['button_confirm'] = $this->language->get('button_confirm');
        $data['text_loading'] = $this->language->get('text_loading');
        $data['text_title'] = $this->language->get('text_title');
        $data['continue'] = $this->url->link('checkout/success');

        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/hipay_multibanco')) {
            return $this->load->view($this->config->get('config_template') . '/template/payment/hipay_multibanco', $data);
        } else {
            return $this->load->view('extension/payment/hipay_multibanco', $data);
        }
    }

    public function confirm() {

        $json = array();

        if ($this->session->data['payment_method']['code'] == 'hipay_multibanco') {
            $this->load->language('extension/payment/hipay_multibanco');
            $this->load->model('checkout/order');
            $this->load->model('extension/payment/hipay_multibanco');

            $data = $this->model_checkout_order->getOrder($this->session->data['order_id']);

            if ($this->config->get('payment_hipay_multibanco_entity') == "12089 / 10241") {
                $this->endpoint = self::HIPAY_ENTITY1_MULTIBANCO;
            } else {
                $this->endpoint = self::HIPAY_ENTITY2_MULTIBANCO;
            }

            if (!$this->config->get('payment_hipay_multibanco_sandbox')) {
                $this->endpoint .= self::HIPAY_GENERATE_MULTIBANCO_PRODUCTION;
            } else {
                $this->endpoint .= self::HIPAY_GENERATE_MULTIBANCO_SANDBOX;
            }

            if (isset($this->session->data['guest'])) {
                $customer_email = $this->session->data['guest']['email'];
            } else {
                $customer_email = $this->customer->getEmail();
            }

            $parameters = array(
                "origin" => $this->url->link('extension/payment/hipay_multibanco/notification') . "&ord=" . $data['order_id'],
                "username" => $this->config->get('payment_hipay_multibanco_api_user'),
                "password" => $this->config->get('payment_hipay_multibanco_api_password'),
                "amount" => number_format($data['total'], 2, ".", ""),
                "additionalInfo" => '',
                "name" => '',
                "address" => '',
                "postCode" => '',
                "city" => '',
                "NIC" => '',
                "externalReference" => $data['order_id'],
                "contactPhone" => '',
                "email" => $customer_email,
                "IDUserBackoffice" => -1,
                "timeLimitDays" => $this->config->get('payment_hipay_multibanco_timelimitdays'),
                "sendEmailBuyer" => false
            );
            $this->model_extension_payment_hipay_multibanco->logger(json_encode($parameters));

            $client = new SoapClient($this->endpoint);
            $result = $client->getReferenceMB($parameters);

            $this->model_extension_payment_hipay_multibanco->logger(json_encode($result));

            if ($result->getReferenceMBResult) {
                $this->model_extension_payment_hipay_multibanco->addMultibancoReference($data, $parameters, $result);
                $orderDescription = $this->language->get('hipay_pending') . " - " . $this->language->get('multibanco_payment_desc') . "\n" . $this->language->get('multibanco_entity') . ": " . $result->entity . "\n" . $this->language->get('multibanco_reference') . ": " . $result->reference . "\n" . $this->language->get('multibanco_amount') . ": &euro; " . $result->amountOut;
                if ($this->config->get('payment_hipay_multibanco_timelimitdays') > 1) {
                    $timeLimitDays = $this->config->get('payment_hipay_multibanco_timelimitdays') + 1;
                }
                $multibanco_expiry_date = strtotime('+' . $timeLimitDays . ' days');
                $orderDescription .= "\n" . $this->language->get('multibanco_expiry_date') . " " . date('Y-m-d', $multibanco_expiry_date);

                $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('payment_hipay_multibanco_order_status_id_pending'), $orderDescription, true);
                $json['redirect'] = $this->url->link('checkout/success');
            } else {
                $this->model_extension_payment_hipay_multibanco->logger('order:' . $this->session->data['order_id'] . " " . $result->error);
                $json['error'] = $result->error;
                $json['redirect'] = $this->url->link('checkout/failure');
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function notification() {
        if (!isset($_GET['ord']))
            exit;

        $order_id = filter_input(INPUT_GET, 'ord');
        $entity = filter_input(INPUT_GET, 'ent');
        $reference = filter_input(INPUT_GET, 'ref');

        $this->load->model('extension/payment/hipay_multibanco');
        $result = $this->model_extension_payment_hipay_multibanco->getMultibancoReference($order_id);

        if ($result->row["reference"] != $reference)
            exit;

        if ($result->row["entity"] != $entity)
            exit;

        if ($result->row["processed"] != '0')
            exit;


        if ($this->config->get('payment_hipay_multibanco_entity') == "12089 / 10241") {
            $this->endpoint = self::HIPAY_ENTITY1_MULTIBANCO;
        } else {
            $this->endpoint = self::HIPAY_ENTITY2_MULTIBANCO;
        }

        if (!$result->row["sandbox"]) {
            $this->endpoint .= self::HIPAY_GENERATE_MULTIBANCO_PRODUCTION;
        } else {
            $this->endpoint .= self::HIPAY_GENERATE_MULTIBANCO_SANDBOX;
        }

        $parameters = array(
            "reference" => $reference,
            "username" => $this->config->get('payment_hipay_multibanco_api_user'),
            "password" => $this->config->get('payment_hipay_multibanco_api_password')
        );

        $client = new SoapClient($this->endpoint);
        $status = $client->getInfoReference($parameters);
        if (!$status->paid)
            exit;

        $this->load->model('checkout/order');
        $this->load->language('extension/payment/hipay_multibanco');
        $this->model_checkout_order->addOrderHistory($order_id, $this->config->get('payment_hipay_multibanco_order_status_id_paid'), $this->language->get('hipay_success'));

        $this->model_extension_payment_hipay_multibanco->updateProcessMultibancoReference($order_id);

        return;
    }

}
