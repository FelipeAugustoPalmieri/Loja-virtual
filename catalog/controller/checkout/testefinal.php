<?php
class ControllerCheckoutTestefinal extends Controller {
    public function index() {
        $data = [];

        $this->load->language('checkout/testefinal');

        $data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_cart'),
			'href' => $this->url->link('checkout/cart')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('checkout/testefinal', '', true)
		);

        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header'); 
        
        $this->response->setOutput($this->load->view('checkout/testefinal', $data));
    }
} 