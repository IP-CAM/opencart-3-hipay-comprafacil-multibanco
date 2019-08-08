<?php

class ControllerExtensionPaymentHipayMultibanco extends Controller {

    private $error = array();
    private $extension_version = "1.0.0.0";

    public function index() {
        $this->load->language('extension/payment/hipay_multibanco');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('payment_hipay_multibanco', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['bank'])) {
            $data['error_bank'] = $this->error['bank'];
        } else {
            $data['error_bank'] = array();
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/payment/hipay_multibanco', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['action'] = $this->url->link('extension/payment/hipay_multibanco', 'user_token=' . $this->session->data['user_token'], true);

        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);

        $this->load->model('localisation/language');

        $data['payment_hipay_multibanco_extension_version'] = $this->extension_version;

        $data['payment_hipay_multibanco_entities'] = ["11249 / 12101", "12089 / 10241"];
        $data['payment_hipay_multibanco_timelimitdays_options'] = ["0", "1", "3", "30", "90"];

        $data['payment_hipay_multibanco_soap'] = 0;
        if (extension_loaded('soap')) {
            $data['payment_hipay_multibanco_soap'] = 1;
        }

        if (isset($this->request->post['payment_hipay_multibanco_sandbox'])) {
            $data['payment_hipay_multibanco_sandbox'] = $this->request->post['payment_hipay_multibanco_sandbox'];
        } else {
            $data['payment_hipay_multibanco_sandbox'] = $this->config->get('payment_hipay_multibanco_sandbox');
        }

        if (isset($this->request->post['payment_hipay_multibanco_api_user'])) {
            $data['payment_hipay_multibanco_api_user'] = $this->request->post['payment_hipay_multibanco_api_user'];
        } else {
            $data['payment_hipay_multibanco_api_user'] = $this->config->get('payment_hipay_multibanco_api_user');
        }

        if (isset($this->request->post['payment_hipay_multibanco_api_password'])) {
            $data['payment_hipay_multibanco_api_password'] = $this->request->post['payment_hipay_multibanco_api_password'];
        } else {
            $data['payment_hipay_multibanco_api_password'] = $this->config->get('payment_hipay_multibanco_api_password');
        }

        if (isset($this->request->post['payment_hipay_multibanco_entity'])) {
            $data['payment_hipay_multibanco_entity'] = $this->request->post['payment_hipay_multibanco_entity'];
        } else {
            $data['payment_hipay_multibanco_entity'] = $this->config->get('payment_hipay_multibanco_entity');
        }

        if (isset($this->request->post['payment_hipay_multibanco_timelimitdays'])) {
            $data['payment_hipay_multibanco_timelimitdays'] = $this->request->post['payment_hipay_multibanco_timelimitdays'];
        } else {
            $data['payment_hipay_multibanco_timelimitdays'] = $this->config->get('payment_hipay_multibanco_timelimitdays');
        }

        if (isset($this->request->post['payment_hipay_multibanco_total_min'])) {
            $data['payment_hipay_multibanco_total_min'] = $this->request->post['payment_hipay_multibanco_total_min'];
        } else {
            $data['payment_hipay_multibanco_total_min'] = $this->config->get('payment_hipay_multibanco_total_min');
        }

        if (isset($this->request->post['payment_hipay_multibanco_total_max'])) {
            $data['payment_hipay_multibanco_total_max'] = $this->request->post['payment_hipay_multibanco_total_max'];
        } else {
            $data['payment_hipay_multibanco_total_max'] = $this->config->get('payment_hipay_multibanco_total_max');
        }

        if (isset($this->request->post['payment_hipay_multibanco_debug'])) {
            $data['payment_hipay_multibanco_debug'] = $this->request->post['payment_hipay_multibanco_debug'];
        } else {
            $data['payment_hipay_multibanco_debug'] = $this->config->get('payment_hipay_multibanco_debug');
        }

        if (isset($this->request->post['payment_hipay_multibanco_order_status_id_paid'])) {
            $data['payment_hipay_multibanco_order_status_id_paid'] = $this->request->post['payment_hipay_multibanco_order_status_id_paid'];
        } else {
            $data['payment_hipay_multibanco_order_status_id_paid'] = $this->config->get('payment_hipay_multibanco_order_status_id_paid');
        }

        if (isset($this->request->post['payment_hipay_multibanco_order_status_id_pending'])) {
            $data['payment_hipay_multibanco_order_status_id_pending'] = $this->request->post['payment_hipay_multibanco_order_status_id_pending'];
        } else {
            $data['payment_hipay_multibanco_order_status_id_pending'] = $this->config->get('payment_hipay_multibanco_order_status_id_pending');
        }

        if (isset($this->request->post['payment_hipay_multibanco_order_status_id_failed'])) {
            $data['payment_hipay_multibanco_order_status_id_failed'] = $this->request->post['payment_hipay_multibanco_order_status_id_failed'];
        } else {
            $data['payment_hipay_multibanco_order_status_id_failed'] = $this->config->get('payment_hipay_multibanco_order_status_id_failed');
        }

        if (isset($this->request->post['payment_hipay_multibanco_order_status_id_cancel'])) {
            $data['payment_hipay_multibanco_order_status_id_cancel'] = $this->request->post['payment_hipay_multibanco_order_status_id_cancel'];
        } else {
            $data['payment_hipay_multibanco_order_status_id_cancel'] = $this->config->get('payment_hipay_multibanco_order_status_id_cancel');
        }

        if (isset($this->request->post['payment_hipay_multibanco_order_status_id_expired'])) {
            $data['payment_hipay_multibanco_order_status_id_expired'] = $this->request->post['payment_hipay_multibanco_order_status_id_expired'];
        } else {
            $data['payment_hipay_multibanco_order_status_id_expired'] = $this->config->get('payment_hipay_multibanco_order_status_id_expired');
        }

        if (isset($this->request->post['payment_hipay_professional_total_min'])) {
            $data['payment_hipay_multibanco_total_min'] = $this->request->post['payment_hipay_multibanco_total_min'];
        } else {
            $data['payment_hipay_multibanco_total_min'] = $this->config->get('payment_hipay_multibanco_total_min');
        }
        if ($data['payment_hipay_multibanco_total_min'] === "")
            $data['payment_hipay_multibanco_total_min'] = 1;

        if (isset($this->request->post['payment_hipay_multibanco_total_max'])) {
            $data['payment_hipay_multibanco_total_max'] = $this->request->post['payment_hipay_multibanco_total_max'];
        } else {
            $data['payment_hipay_multibanco_total_max'] = $this->config->get('payment_hipay_multibanco_total_max');
        }
        if ($data['payment_hipay_multibanco_total_max'] === "")
            $data['payment_hipay_multibanco_total_max'] = 2500;


        $this->load->model('localisation/order_status');
        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        if (isset($this->request->post['payment_hipay_multibanco_geo_zone_id'])) {
            $data['payment_hipay_multibanco_geo_zone_id'] = $this->request->post['payment_hipay_multibanco_geo_zone_id'];
        } else {
            $data['payment_hipay_multibanco_geo_zone_id'] = $this->config->get('payment_hipay_multibanco_geo_zone_id');
        }

        $this->load->model('localisation/geo_zone');
        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        if (isset($this->request->post['payment_hipay_multibanco_status'])) {
            $data['payment_hipay_multibanco_status'] = $this->request->post['payment_hipay_multibanco_status'];
        } else {
            $data['payment_hipay_multibanco_status'] = $this->config->get('payment_hipay_multibanco_status');
        }

        if (isset($this->request->post['payment_hipay_multibanco_sort_order'])) {
            $data['payment_hipay_multibanco_sort_order'] = $this->request->post['payment_hipay_multibanco_sort_order'];
        } else {
            $data['payment_hipay_multibanco_sort_order'] = $this->config->get('payment_hipay_multibanco_sort_order');
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/payment/hipay_multibanco', $data));
    }

    public function install() {
        $this->load->model('extension/payment/hipay_multibanco');
        $this->model_extension_payment_hipay_multibanco->install();
    }

    public function uninstall() {
        $this->load->model('extension/payment/hipay_multibanco');
        $this->model_extension_payment_hipay_multibanco->uninstall();
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/payment/hipay_multibanco')) {
            $this->error['warning'] = $this->language->get('error_permission');
        } else {

            if (!$this->request->post['payment_hipay_multibanco_api_user']) {
                $this->error['warning'] = $this->language->get('error_mandatory') . ": " . $this->language->get('entry_api_user');
            }
            if (!$this->request->post['payment_hipay_multibanco_api_password']) {
                if (isset($this->error['warning']))
                    $this->error['warning'] .= " + " . $this->language->get('entry_api_password');
                else
                    $this->error['warning'] = $this->language->get('error_mandatory') . ": " . $this->language->get('entry_api_password');
            }
        }

        return !$this->error;
    }

}
