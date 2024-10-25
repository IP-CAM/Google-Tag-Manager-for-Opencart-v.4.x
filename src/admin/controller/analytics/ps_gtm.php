<?php
namespace Opencart\Admin\Controller\Extension\PsGtm\Analytics;
/**
 * Class PsGtm
 *
 * @package Opencart\Admin\Controller\Extension\PsGtm\Analytics
 */
class PsGtm extends \Opencart\System\Engine\Controller
{
    /**
     * @return void
     */
    public function index(): void
    {
        $this->load->language('extension/ps_gtm/analytics/ps_gtm');

        $this->document->setTitle($this->language->get('heading_title'));

        $data['breadcrumbs'] = [];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true),
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=analytics', true),
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/analytics/ps_gtm', 'user_token=' . $this->session->data['user_token'], true),
        ];


        $separator = version_compare(VERSION, '4.0.2.0', '>=') ? '.' : '|';

        $data['action'] = $this->url->link('extension/ps_gtm/analytics/ps_gtm' . $separator . 'save', 'user_token=' . $this->session->data['user_token']);
        $data['fix_event_handler'] = $this->url->link('extension/ps_gtm/analytics/ps_gtm' . $separator . 'fixEventHandler', 'user_token=' . $this->session->data['user_token']);
        $data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=analytics');

		$data['analytics_ps_gtm_status'] = $this->config->get('analytics_ps_gtm_status');
		$data['analytics_ps_gtm_gtm_id'] = $this->config->get('analytics_ps_gtm_gtm_id');

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/ps_gtm/analytics/ps_gtm', $data));
    }

    public function save(): void
    {
        $this->load->language('extension/ps_gtm/analytics/ps_gtm');

        $json = [];

        if (!$this->user->hasPermission('modify', 'extension/ps_gtm/analytics/ps_gtm')) {
            $json['error'] = $this->language->get('error_permission');
        }

        if (!$json) {
            if (!isset($this->request->post['analytics_ps_gtm_gtm_id'])) {
                $json['error']['input-gtm-id'] = $this->language->get('error_gtm_id');
            } elseif (preg_match('/^GTM-[A-Z0-9]{8}$/', $this->request->post['analytics_ps_gtm_gtm_id']) !== 1) {
                $json['error']['input-gtm-id'] = $this->language->get('error_gtm_id_invalid');
            }
        }

        if (!$json) {
            $this->load->model('setting/setting');

            $this->model_setting_setting->editSetting('analytics_ps_gtm', $this->request->post);

            $json['success'] = $this->language->get('text_success');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function install(): void
    {
        if ($this->user->hasPermission('modify', 'extension/ps_gtm/analytics/ps_gtm')) {
            $this->load->model('setting/event');

            $separator = version_compare(VERSION, '4.0.2.0', '>=') ? '.' : '|';

            if (version_compare(VERSION, '4.0.1.0', '>=')) {
                $this->model_setting_event->addEvent([
                    'code' => 'analytics_ps_gtm',
                    'description' => '',
                    'trigger' => 'catalog/view/common/header/before',
                    'action' => 'extension/ps_gtm/analytics/ps_gtm' . $separator . 'eventCatalogViewCommonHeaderBefore',
                    'status' => '1',
                    'sort_order' => '0'
                ]);
            } else {
                $this->model_setting_event->addEvent(
                    'analytics_ps_gtm',
                    'catalog/view/common/header/before',
                    'extension/ps_gtm/analytics/ps_gtm' . $separator . 'eventCatalogViewCommonHeaderBefore'
                );
            }
        }
    }

    public function uninstall(): void
    {
        if ($this->user->hasPermission('modify', 'extension/ps_gtm/analytics/ps_gtm')) {
            $this->load->model('setting/event');

            $this->model_setting_event->deleteEventByCode('analytics_ps_gtm');
        }
    }

    public function fixEventHandler(): void
    {
        $this->load->language('extension/ps_gtm/analytics/ps_gtm');

        $json = [];

        if (!$this->user->hasPermission('modify', 'extension/ps_gtm/analytics/ps_gtm')) {
            $json['error'] = $this->language->get('error_permission');
        }

        if (!$json) {
            $this->load->model('setting/event');

            $this->model_setting_event->deleteEventByCode('analytics_ps_gtm');

            $separator = version_compare(VERSION, '4.0.2.0', '>=') ? '.' : '|';

            if (version_compare(VERSION, '4.0.1.0', '>=')) {
                $result = $this->model_setting_event->addEvent([
                    'code' => 'analytics_ps_gtm',
                    'description' => '',
                    'trigger' => 'catalog/view/common/header/before',
                    'action' => 'extension/ps_gtm/analytics/ps_gtm' . $separator . 'eventCatalogViewCommonHeaderBefore',
                    'status' => '1',
                    'sort_order' => '0'
                ]);
            } else {
                $result = $this->model_setting_event->addEvent(
                    'analytics_ps_gtm',
                    'catalog/view/common/header/before',
                    'extension/ps_gtm/analytics/ps_gtm' . $separator . 'eventCatalogViewCommonHeaderBefore'
                );
            }

            if ($result > 0) {
                $json['success'] = $this->language->get('text_success');
            } else {
                $json['error'] = $this->language->get('error_event');
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}
