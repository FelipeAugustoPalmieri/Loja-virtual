<?php
class ControllerCheckoutConfirmAsaas extends Controller {
    public function add() {
        $this->response->addHeader('Content-Type: application/json');

        if (!$this->customer->isLogged()){
            $this->response->setOutput(json_encode(array("success"=>false, "message"=> "Por favor, se logue!")));
            return false;
        }

        if(isset($this->request->post['method']) && ($this->request->post['method'] == "boleto" || $this->request->post['method'] == "pix") && $this->request->post['parcela'] > 1){
            $this->response->setOutput(json_encode(array("success"=>false, "message"=> "Pagamento em boleto ou pix não aceita parcela")));
            return false;
        }

        $order_data = array();
        //COMECO DO TOTAL
        $totals = array();
        $taxes = $this->cart->getTaxes();
        $total = 0;

        // Because __call can not keep var references so we put them into an array.
        $total_data = array(
            'totals' => &$totals,
            'taxes'  => &$taxes,
            'total'  => &$total
        );

        $this->load->model('setting/extension');

        $sort_order = array();

        $results = $this->model_setting_extension->getExtensions('total');

        foreach ($results as $key => $value) {
            $sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
        }

        array_multisort($sort_order, SORT_ASC, $results);

        /*foreach ($results as $result) {
            if ($this->config->get('total_' . $result['code'] . '_status')) {
                $this->load->model('extension/total/' . $result['code']);

                // We have to put the totals in an array so that they pass by reference.
                $this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
            }
        }

        $sort_order = array();

        foreach ($totals as $key => $value) {
            $sort_order[$key] = $value['sort_order'];
        }

        array_multisort($sort_order, SORT_ASC, $totals);

        $order_data['totals'] = $totals;*/
        //FIM DO TOTAL
        //CONFIGURACOES
        $this->load->language('checkout/checkout');

        $order_data['invoice_prefix'] = $this->config->get('config_invoice_prefix');
        $order_data['store_id'] = $this->config->get('config_store_id');
        $order_data['store_name'] = $this->config->get('config_name');

        if ($order_data['store_id']) {
            $order_data['store_url'] = $this->config->get('config_url');
        } else {
            if ($this->request->server['HTTPS']) {
                $order_data['store_url'] = HTTPS_SERVER;
            } else {
                $order_data['store_url'] = HTTP_SERVER;
            }
        }
        //FIM DE CONFIGURACOES
        //INICIO DO CLIENTE
        $this->load->model('account/customer');

        $customer_info = $this->model_account_customer->getCustomer($this->customer->getId());

        $order_data['customer_id'] = $this->customer->getId();
        $order_data['customer_group_id'] = $customer_info['customer_group_id'];
        $order_data['firstname'] = $customer_info['firstname'];
        $order_data['lastname'] = $customer_info['lastname'];
        $order_data['email'] = $customer_info['email'];
        $order_data['telephone'] = $customer_info['telephone'];
        $order_data['custom_field'] = json_decode($customer_info['custom_field'], true);

        //FIM DO CLIENTE
        //PAGAMENTO
        $this->load->model('account/address');
        $address = $this->model_account_address->getAddress($customer_info['address_id']);

        $order_data['payment_firstname'] = $address['firstname'];
        $order_data['payment_lastname'] = $address['lastname'];
        $order_data['payment_company'] = $address['company'];
        $order_data['payment_address_1'] = $address['address_1'];
        $order_data['payment_address_2'] = $address['address_2'];
        $order_data['payment_city'] = $address['city'];
        $order_data['payment_postcode'] = $address['postcode'];
        $order_data['payment_zone'] = $address['zone'];
        $order_data['payment_zone_id'] = $address['zone_id'];
        $order_data['payment_country'] = $address['country'];
        $order_data['payment_country_id'] = $address['country_id'];
        $order_data['payment_address_format'] = $address['address_format'];
        $order_data['payment_custom_field'] = (isset($address['custom_field']) ? $address['custom_field'] : array());

        $order_data['payment_method'] = 'Pagamento Via Asaas'.(isset($this->request->post['method'])?" ".$this->request->post['method']:"");
        $order_data['payment_code'] = '';
        
        //FIM DE PAGAMENTO
        //ENDERECO
        $order_data['shipping_firstname'] = $address['firstname'];
        $order_data['shipping_lastname'] = $address['lastname'];
        $order_data['shipping_company'] = $address['company'];
        $order_data['shipping_address_1'] = $address['address_1'];
        $order_data['shipping_address_2'] = $address['address_2'];
        $order_data['shipping_city'] = $address['city'];
        $order_data['shipping_postcode'] = $address['postcode'];
        $order_data['shipping_zone'] = $address['zone'];
        $order_data['shipping_zone_id'] = $address['zone_id'];
        $order_data['shipping_country'] = $address['country'];
        $order_data['shipping_country_id'] = $address['country_id'];
        $order_data['shipping_address_format'] = $address['address_format'];
        $order_data['shipping_custom_field'] = (isset($address['custom_field']) ? $address['custom_field'] : array());
        if(isset($this->request->post['frete'])){
            if ($this->request->post['frete'] == "option1") {
                $order_data['shipping_method'] = "Retirar no Local";
                $order_data['shipping_code'] = "pickup.pickup";
                $this->session->data['shipping_method']['title'] = "Retirar no Local";
				$this->session->data['shipping_method']['cost'] = 0;
            } else {
                $order_data['shipping_method'] = "Frete preço 35.00";
                $order_data['shipping_code'] = "flat.flat";
                $this->session->data['shipping_method']['title'] = "Frete preço 35.00";
				$this->session->data['shipping_method']['cost'] = 35;
            }
        }
        //FIM ENDERECO

        //PRODUTO
        $order_data['products'] = array();

        foreach ($this->cart->getProducts() as $product) {
            $option_data = array();

            foreach ($product['option'] as $option) {
                $option_data[] = array(
                    'product_option_id'       => $option['product_option_id'],
                    'product_option_value_id' => $option['product_option_value_id'],
                    'option_id'               => $option['option_id'],
                    'option_value_id'         => $option['option_value_id'],
                    'name'                    => $option['name'],
                    'value'                   => $option['value'],
                    'type'                    => $option['type']
                );
            }

            $order_data['products'][] = array(
                'product_id' => $product['product_id'],
                'name'       => $product['name'],
                'model'      => $product['model'],
                'option'     => $option_data,
                'download'   => $product['download'],
                'quantity'   => $product['quantity'],
                'subtract'   => $product['subtract'],
                'price'      => $product['price'],
                'total'      => $product['total'],
                'tax'        => $this->tax->getTax($product['price'], $product['tax_class_id']),
                'reward'     => $product['reward']
            );
        }
        //FIM PRODUTO

        foreach ($results as $result) {
            if ($this->config->get('total_' . $result['code'] . '_status')) {
                $this->load->model('extension/total/' . $result['code']);

                // We have to put the totals in an array so that they pass by reference.
                $this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
            }
        }
        $sort_order = array();
        foreach ($totals as $key => $value) {
            $sort_order[$key] = $value['sort_order'];
        }
        array_multisort($sort_order, SORT_ASC, $totals);
        $order_data['totals'] = $totals;

        $order_data['comment'] = "";
        $order_data['total'] = $total_data['total'];

        if (isset($this->request->cookie['tracking'])) {
            $order_data['tracking'] = $this->request->cookie['tracking'];

            $subtotal = $this->cart->getSubTotal();

            // Affiliate
            $affiliate_info = $this->model_account_customer->getAffiliateByTracking($this->request->cookie['tracking']);

            if ($affiliate_info) {
                $order_data['affiliate_id'] = $affiliate_info['customer_id'];
                $order_data['commission'] = ($subtotal / 100) * $affiliate_info['commission'];
            } else {
                $order_data['affiliate_id'] = 0;
                $order_data['commission'] = 0;
            }

            // Marketing
            $this->load->model('checkout/marketing');

            $marketing_info = $this->model_checkout_marketing->getMarketingByCode($this->request->cookie['tracking']);

            if ($marketing_info) {
                $order_data['marketing_id'] = $marketing_info['marketing_id'];
            } else {
                $order_data['marketing_id'] = 0;
            }
        } else {
            $order_data['affiliate_id'] = 0;
            $order_data['commission'] = 0;
            $order_data['marketing_id'] = 0;
            $order_data['tracking'] = '';
        }

        $order_data['language_id'] = $this->config->get('config_language_id');
        $order_data['currency_id'] = $this->currency->getId($this->session->data['currency']);
        $order_data['currency_code'] = $this->session->data['currency'];
        $order_data['currency_value'] = $this->currency->getValue($this->session->data['currency']);
        $order_data['ip'] = $this->request->server['REMOTE_ADDR'];

        if (!empty($this->request->server['HTTP_X_FORWARDED_FOR'])) {
            $order_data['forwarded_ip'] = $this->request->server['HTTP_X_FORWARDED_FOR'];
        } elseif (!empty($this->request->server['HTTP_CLIENT_IP'])) {
            $order_data['forwarded_ip'] = $this->request->server['HTTP_CLIENT_IP'];
        } else {
            $order_data['forwarded_ip'] = '';
        }

        if (isset($this->request->server['HTTP_USER_AGENT'])) {
            $order_data['user_agent'] = $this->request->server['HTTP_USER_AGENT'];
        } else {
            $order_data['user_agent'] = '';
        }

        if (isset($this->request->server['HTTP_ACCEPT_LANGUAGE'])) {
            $order_data['accept_language'] = $this->request->server['HTTP_ACCEPT_LANGUAGE'];
        } else {
            $order_data['accept_language'] = '';
        }

        $this->load->model('checkout/order');

        $this->session->data['order_id'] = $this->model_checkout_order->addOrder($order_data);

        // Set the order history
        if (isset($this->request->post['order_status_id'])) {
            $order_status_id = $this->request->post['order_status_id'];
        } else {
            $order_status_id = $this->config->get('config_order_status_id');
        }

        $idAsaas = $this->addClientAsaas();

        $retornoPagamento = $this->registroPagamento($idAsaas, $this->session->data['order_id'], $customer_info);

        if(isset($retornoPagamento["errors"]) && $retornoPagamento["errors"][0]){
            $this->response->setOutput(json_encode(array("success"=>false, "message"=> $retornoPagamento["errors"][0]["description"])));
            return false;
        }

        $this->model_checkout_order->addOrderIdAsaas($this->session->data['order_id'], $retornoPagamento['id']);

        $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $order_status_id);

        if(isset($retornoPagamento['id']) && isset($retornoPagamento['invoiceUrl']))
        {
            $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $order_status_id, 'Fatura Criada no Asaas N: '.$retornoPagamento['id'].' Acesse a Fatura aqui: <a href="'.$retornoPagamento['invoiceUrl'].'" target="_blank"> Fatura </a>', true);

            if (isset($this->session->data['order_id'])) {
                $this->cart->clear();
                unset($this->session->data['shipping_method']);
                unset($this->session->data['shipping_methods']);
                unset($this->session->data['payment_method']);
                unset($this->session->data['payment_methods']);
                unset($this->session->data['comment']);
                unset($this->session->data['coupon']);
            }

            if(isset($this->request->post['method'])){
                switch($this->request->post['method']){
                    case "boleto":
                        $this->response->setOutput(json_encode(array("success"=>true, "link"=> $retornoPagamento['bankSlipUrl'])));
                        break;
                    case "cartao":
                        if($retornoPagamento['status'] == "CONFIRMED"){
                            $this->response->setOutput(json_encode(array("success"=>true, "message"=> "Pagamento realizado com sucesso")));
                        }else{
                            $this->response->setOutput(json_encode(array("success"=>false, "message"=> "")));
                        }
                        break;
                    case "pix":
                        $this->response->setOutput(json_encode(array("success"=>true, "transacaoId" => $retornoPagamento['id'], "dados"=> $retornoPagamento)));
                }
                return false;
            }
        }
    }

    public function gerarqrcode(){
        if (is_null($this->request->post['transacaoId'])) {
            $this->response->setOutput(json_encode(array("success"=>false, "message"=> "transação id não encontrado, por favor verifique o qrcode la no pedido!")));
        }

        $this->load->library('requestapi');
		$this->requestapi->url = URLASAAS;
        $this->requestapi->version = "v3";

        $response = $this->requestapi->api([
            'no_version'=> '',
            'endpoint' => 'payments/'.$this->request->post['transacaoId'].'/pixQrCode',
            'content_type' => '',
            'method' => 'GET',
            'auth_type' => '',
            'headers' => [
                'access_token: '.TOKENASAAS,
            ]
        ]);
        
        if(isset($response["errors"]) && $response["errors"][0]){
            $this->response->setOutput(json_encode(array("success"=>false, "message"=> $response["errors"][0]["description"])));
            return false;
        }

        $this->response->setOutput(json_encode(array("success"=>true, "dados"=> $response)));
    }

    public function addClientAsaas(){
        $this->load->library('requestapi');
		$this->requestapi->url = URLASAAS;
        $this->requestapi->version = "v3";
        
        $this->load->model('account/customer');

        $customer_info = $this->model_account_customer->getCustomer($this->customer->getId());
        if(empty($customer_info["id_asaas"])){
            $customField = json_decode($customer_info['custom_field'], true);
            if(isset($customField[2])){
                $cpf = $customField[2];
            }

            $response = $this->requestapi->api([
                'no_version'=> '',
                'endpoint' => 'customers',
                'content_type' => '',
                'parameters' => 'cpfCnpj='. preg_replace("/[^0-9]/", "", $cpf),
                'method' => 'GET',
                'auth_type' => '',
                'headers' => [
                    'access_token: '.TOKENASAAS,
                ]
            ]);
            
            if(count($response['data']) > 1){
                $this->response->redirect($this->url->link('checkout/error', ['mensagem'=>'Venho mais de uma informação por favor verificar o seu documento no cadastro do cliente'], true));
            }

            if(count($response['data']) > 0){
                $dadosCustomer = $response['data'][0];
                $this->model_account_customer->editIdAsaas($dadosCustomer["id"]);
                return $dadosCustomer["id"];
            }else{
                $response = $this->requestapi->api([
                    'no_version'=> '',
                    'endpoint' => 'customers',
                    'content_type' => '',
                    'parameters' => [
                        'name' => $customer_info['firstname'],
                        'cpfCnpj' => preg_replace("/[^0-9]/", "", $cpf),
                        'email' => $customer_info['email'],
                        'externalReference' => $customer_info['customer_id'],
                        'observations' => 'Criado via Loja Virtual'
                    ],
                    'method' => 'POST',
                    'auth_type' => '',
                    'headers' => [
                        'access_token: '.TOKENASAAS,
                    ]
                ]);
                
                return $response['id'];
            }
        }else{
            return $customer_info["id_asaas"];
        }
    }

    public function registroPagamento($customerId, $idOrder, $customer_info){
        $this->load->library('requestapi');
		$this->requestapi->url = URLASAAS;
        $this->requestapi->version = "v3";

        $this->load->model('checkout/order');

        $order = $this->model_checkout_order->getOrder($idOrder);
        $productOrder = $this->model_checkout_order->getOrderProducts($idOrder);
        $nameProducts = "";
        foreach($productOrder as $item){
            $nameProducts .= (empty($nameProducts)? $item['name'] : ','.$item['name']);
        }

        if(isset($this->request->post['method'])){
            if ($this->request->post['method'] == "boleto") {
                $valoroutros = 4;
            } else {
                $valoroutros = 0;
            }
        }

        $parcela = 1;
        if(isset($this->request->post['parcela']) && $this->request->post['parcela'] > 1){
            $parcela = $this->request->post['parcela'];
        }

        $valortotal = ((float)$order['total'] + (float)$valoroutros);
        $juros = 3.5;
            
        $valorJuros = ($valortotal * ($parcela * ($juros/100)));
        $totalJuros = (($parcela > 1) ? $valorJuros + $valortotal : $valortotal);

        $dadosparametros = [
            'customer' => $customerId,
            'billingType' => $this->request->post['method'] == "boleto" ? 'BOLETO' : (($this->request->post['method'] == "pix") ? 'PIX' : 'CREDIT_CARD'),
            'value' => $totalJuros,
            'dueDate' => date('Y-m-d', strtotime('+2 day')),
            'description' => 'Compra de: '.$nameProducts,
            'externalReference' => $idOrder,
            'remoteIp' => $this->request->server['REMOTE_ADDR'],
        ];
        if($parcela > 1 && $valortotal > 10){
            $parcelaarray = [
                'installmentCount' => $parcela,
                'installmentValue' => ($totalJuros / $parcela),
            ];

            $dadosparametros = array_merge($dadosparametros, $parcelaarray);
        }

        if ($this->request->post['method'] == "cartao") {
            list($mes, $ano) = explode("/", $this->request->post['expiry']);
            $customField = json_decode($customer_info['custom_field'], true);
            if(isset($customField[2])){
                $cpf = $customField[2];
            }
            $cartao['creditCard'] = [
                'holderName' => ($this->request->post['titular'] ? $customer_info['firstname'] : $this->request->post['nomecartao'] ),
                'number' => preg_replace("/[^0-9]/", "", $this->request->post['number']),
                'expiryMonth' => ((isset($mes))? $mes : "01"),
                'expiryYear' => ((isset($ano))? $ano : "2020"),
                'ccv' => $this->request->post['ccv']
            ];

            $this->load->model('account/address');
            $address = $this->model_account_address->getAddress($customer_info['address_id']);

            $infoCartao['creditCardHolderInfo'] = [
                'name' => ($this->request->post['titular'] ? $customer_info['firstname'] : $this->request->post['nomecartao'] ),
                'email' => $customer_info['email'],
                'cpfCnpj' => preg_replace("/[^0-9]/", "", ($this->request->post['titular'] ? $cpf : $this->request->post['cpfcartao'])),
                'postalCode' => $address['postcode'],
                'addressNumber' =>  (preg_replace("/[^0-9]/", "", $address['address_1']) != "" ? preg_replace("/[^0-9]/", "", $address['address_1']): "01"),
                'phone' => preg_replace("/[^0-9]/", "", $customer_info['telephone'])
            ];

            $dadosparametros = array_merge($dadosparametros, $cartao, $infoCartao);
        }
        
        $response = $this->requestapi->api([
            'no_version'=> '',
            'endpoint' => 'payments',
            'content_type' => '',
            'parameters' => $dadosparametros,
            'method' => 'POST',
            'auth_type' => '',
            'headers' => [
                'access_token: '.TOKENASAAS,
            ]
        ]);

        return $response;
    }
}