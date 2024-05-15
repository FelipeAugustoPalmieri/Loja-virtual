<?php
class ControllerCheckoutCheckout extends Controller {
	public function index() {
		// Validate cart has products and has stock.
		if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
			$this->response->redirect($this->url->link('checkout/cart'));
		}
		// Validate minimum quantity requirements.
		$products = $this->cart->getProducts();

		foreach ($products as $product) {
			$product_total = 0;

			foreach ($products as $product_2) {
				if ($product_2['product_id'] == $product['product_id']) {
					$product_total += $product_2['quantity'];
				}
			}

			if ($product['minimum'] > $product_total) {
				$this->response->redirect($this->url->link('checkout/cart'));
			}
		}

		$this->load->model('tool/points');
		$this->load->model('account/customer');
		$this->load->model('account/address');
		$infTbest = $this->model_tool_points->getInfoTbest();
		$totalGeral = 0;
		foreach($products as $product){
			$total = $this->currency->format($product['price'] * $product['quantity'], $this->session->data['currency']);
			$totalGeral = $totalGeral+($product['price'] * $product['quantity']);
			$data['products'][] = array(
				'name' => $product['name'],
				'quantity' => $product['quantity'],
				'price' => $this->currency->format($product['price'], $this->session->data['currency']),
				'total' => $this->model_tool_points->showPoints($total, $infTbest),
			);
		}

		$data['totalGeral'] = $this->model_tool_points->showPoints($this->currency->format($totalGeral, $this->session->data['currency']), $infTbest);

		$this->load->language('checkout/checkout');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment.min.js');
		$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment-with-locales.min.js');
		$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.js');
		$this->document->addStyle('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.css');
		$this->document->addStyle('catalog/view/javascript/jquery/jquery-ui.min.css');
		// Required by klarna
		if ($this->config->get('payment_klarna_account') || $this->config->get('payment_klarna_invoice')) {
			$this->document->addScript('http://cdn.klarna.com/public/kitt/toc/v1.0/js/klarna.terms.min.js');
		}

		$this->document->addScript('catalog/view/javascript/jquery/jquery-ui.min.js');
		$this->document->addScript('https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.11/jquery.mask.min.js');

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
			'href' => $this->url->link('checkout/checkout', '', true)
		);

		$data['text_checkout_option'] = sprintf($this->language->get('text_checkout_option'), 1);
		$data['text_checkout_account'] = sprintf($this->language->get('text_checkout_account'), 2);
		$data['text_checkout_payment_address'] = sprintf($this->language->get('text_checkout_payment_address'), 2);
		$data['text_checkout_shipping_address'] = sprintf($this->language->get('text_checkout_shipping_address'), 3);
		$data['text_checkout_shipping_method'] = sprintf($this->language->get('text_checkout_shipping_method'), 4);
		
		if ($this->cart->hasShipping()) {
			$data['text_checkout_payment_method'] = sprintf($this->language->get('text_checkout_payment_method'), 5);
			$data['text_checkout_confirm'] = sprintf($this->language->get('text_checkout_confirm'), 6);
		} else {
			$data['text_checkout_payment_method'] = sprintf($this->language->get('text_checkout_payment_method'), 3);
			$data['text_checkout_confirm'] = sprintf($this->language->get('text_checkout_confirm'), 4);	
		}

		if (isset($this->session->data['error'])) {
			$data['error_warning'] = $this->session->data['error'];
			unset($this->session->data['error']);
		} else {
			$data['error_warning'] = '';
		}

		if(!$this->customer->isLogged()){
			$this->response->redirect($this->url->link('checkout/login'));
		}

		$data['customer_info'] = $this->model_account_customer->getCustomer($this->customer->getId());
		$customField = json_decode($data['customer_info']['custom_field'], true);
		if(isset($customField[2])){
			$data['customer_info']['cpf'] = $customField[2];
		}

		if(isset($this->session->data["errorasaas"])){
			$data["errorasaas"] = $this->session->data["errorasaas"];
			unset($this->session->data["errorasaas"]);
		}

		$data['address'] = $this->model_account_address->getAddress($data['customer_info']['address_id']);
		
		$data['logged'] = $this->customer->isLogged();

		$totalCart = $this->cart->getTotal();
		$juros = 3.5;
		for($i = 1; $i <= 10; $i++){
			$valorJuros = ($totalCart * ($i * ($juros/100)));
			$totalJuros = (($i > 1) ? $valorJuros + $totalCart : $totalCart);

			if(($totalJuros/$i) < 20  && $i > 1){
				$i = 11;
				break;
			} 
			$data['parcelas'][] = array(
				'parcela' => $i,
				'valor' => $this->currency->format(($totalJuros/$i), $this->session->data['currency'])
			);
		}

		$data['totalcart'] = $totalCart;

		if (isset($this->session->data['account'])) {
			$data['account'] = $this->session->data['account'];
		} else {
			$data['account'] = '';
		}

		$data['action'] = $this->url->link('checkout/confirm_asaas/add');

		$data['linkAddress'] = $this->url->link('account/address/add', 'redirect='.$this->url->link('checkout/checkout'), true);

		$data['shipping_required'] = $this->cart->hasShipping();

		$data['continue'] = $this->url->link('common/home');

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header'); 

		$this->response->setOutput($this->load->view('checkout/checkout', $data));
	}

	public function country() {
		$json = array();

		$this->load->model('localisation/country');

		$country_info = $this->model_localisation_country->getCountry($this->request->get['country_id']);

		if ($country_info) {
			$this->load->model('localisation/zone');

			$json = array(
				'country_id'        => $country_info['country_id'],
				'name'              => $country_info['name'],
				'iso_code_2'        => $country_info['iso_code_2'],
				'iso_code_3'        => $country_info['iso_code_3'],
				'address_format'    => $country_info['address_format'],
				'postcode_required' => $country_info['postcode_required'],
				'zone'              => $this->model_localisation_zone->getZonesByCountryId($this->request->get['country_id']),
				'status'            => $country_info['status']
			);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function customfield() {
		$json = array();

		$this->load->model('account/custom_field');

		// Customer Group
		if (isset($this->request->get['customer_group_id']) && is_array($this->config->get('config_customer_group_display')) && in_array($this->request->get['customer_group_id'], $this->config->get('config_customer_group_display'))) {
			$customer_group_id = $this->request->get['customer_group_id'];
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}

		$custom_fields = $this->model_account_custom_field->getCustomFields($customer_group_id);

		foreach ($custom_fields as $custom_field) {
			$json[] = array(
				'custom_field_id' => $custom_field['custom_field_id'],
				'required'        => $custom_field['required']
			);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function getParcelasFrete(){
		if(isset($this->request->post['optionFrete'])){
			$frete = ($this->request->post['optionFrete'] == "option2" ? 35 : 0);
		}
		$totalCart = $this->cart->getTotal() + $frete;
		$juros = 3.5;
		for($i = 1; $i <= 10; $i++){
			$valorJuros = ($totalCart * ($i * ($juros/100)));
			$totalJuros = (($i > 1) ? $valorJuros + $totalCart : $totalCart);

			if(($totalJuros/$i) < 20  && $i > 1){
				$i = 11;
				break;
			} 
			$data['parcelas'][] = array(
				'parcela' => $i,
				'valor' => $this->currency->format(($totalJuros/$i), $this->session->data['currency'])
			);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($data));
	}

	public function processarPagamentoBoleto(){
		print_r($this->request->post);
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode(array("success"=>true)));
	}
}