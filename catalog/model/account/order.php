<?php
class ModelAccountOrder extends Model {
	public function getOrder($order_id) {
		$order_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order` WHERE order_id = '" . (int)$order_id . "' AND customer_id = '" . (int)$this->customer->getId() . "' AND customer_id != '0' AND order_status_id > '0'");

		if ($order_query->num_rows) {
			$country_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE country_id = '" . (int)$order_query->row['payment_country_id'] . "'");

			if ($country_query->num_rows) {
				$payment_iso_code_2 = $country_query->row['iso_code_2'];
				$payment_iso_code_3 = $country_query->row['iso_code_3'];
			} else {
				$payment_iso_code_2 = '';
				$payment_iso_code_3 = '';
			}

			$zone_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE zone_id = '" . (int)$order_query->row['payment_zone_id'] . "'");

			if ($zone_query->num_rows) {
				$payment_zone_code = $zone_query->row['code'];
			} else {
				$payment_zone_code = '';
			}

			$country_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE country_id = '" . (int)$order_query->row['shipping_country_id'] . "'");

			if ($country_query->num_rows) {
				$shipping_iso_code_2 = $country_query->row['iso_code_2'];
				$shipping_iso_code_3 = $country_query->row['iso_code_3'];
			} else {
				$shipping_iso_code_2 = '';
				$shipping_iso_code_3 = '';
			}

			$zone_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE zone_id = '" . (int)$order_query->row['shipping_zone_id'] . "'");

			if ($zone_query->num_rows) {
				$shipping_zone_code = $zone_query->row['code'];
			} else {
				$shipping_zone_code = '';
			}

			return array(
				'order_id'                => $order_query->row['order_id'],
				'invoice_no'              => $order_query->row['invoice_no'],
				'invoice_prefix'          => $order_query->row['invoice_prefix'],
				'store_id'                => $order_query->row['store_id'],
				'store_name'              => $order_query->row['store_name'],
				'store_url'               => $order_query->row['store_url'],
				'customer_id'             => $order_query->row['customer_id'],
				'firstname'               => $order_query->row['firstname'],
				'lastname'                => $order_query->row['lastname'],
				'telephone'               => $order_query->row['telephone'],
				'email'                   => $order_query->row['email'],
				'payment_firstname'       => $order_query->row['payment_firstname'],
				'payment_lastname'        => $order_query->row['payment_lastname'],
				'payment_company'         => $order_query->row['payment_company'],
				'payment_address_1'       => $order_query->row['payment_address_1'],
				'payment_address_2'       => $order_query->row['payment_address_2'],
				'payment_postcode'        => $order_query->row['payment_postcode'],
				'payment_city'            => $order_query->row['payment_city'],
				'payment_zone_id'         => $order_query->row['payment_zone_id'],
				'payment_zone'            => $order_query->row['payment_zone'],
				'payment_zone_code'       => $payment_zone_code,
				'payment_country_id'      => $order_query->row['payment_country_id'],
				'payment_country'         => $order_query->row['payment_country'],
				'payment_iso_code_2'      => $payment_iso_code_2,
				'payment_iso_code_3'      => $payment_iso_code_3,
				'payment_address_format'  => $order_query->row['payment_address_format'],
				'payment_method'          => $order_query->row['payment_method'],
				'shipping_firstname'      => $order_query->row['shipping_firstname'],
				'shipping_lastname'       => $order_query->row['shipping_lastname'],
				'shipping_company'        => $order_query->row['shipping_company'],
				'shipping_address_1'      => $order_query->row['shipping_address_1'],
				'shipping_address_2'      => $order_query->row['shipping_address_2'],
				'shipping_postcode'       => $order_query->row['shipping_postcode'],
				'shipping_city'           => $order_query->row['shipping_city'],
				'shipping_zone_id'        => $order_query->row['shipping_zone_id'],
				'shipping_zone'           => $order_query->row['shipping_zone'],
				'shipping_zone_code'      => $shipping_zone_code,
				'shipping_country_id'     => $order_query->row['shipping_country_id'],
				'shipping_country'        => $order_query->row['shipping_country'],
				'shipping_iso_code_2'     => $shipping_iso_code_2,
				'shipping_iso_code_3'     => $shipping_iso_code_3,
				'shipping_address_format' => $order_query->row['shipping_address_format'],
				'shipping_method'         => $order_query->row['shipping_method'],
				'comment'                 => $order_query->row['comment'],
				'total'                   => $order_query->row['total'],
				'order_status_id'         => $order_query->row['order_status_id'],
				'language_id'             => $order_query->row['language_id'],
				'currency_id'             => $order_query->row['currency_id'],
				'currency_code'           => $order_query->row['currency_code'],
				'currency_value'          => $order_query->row['currency_value'],
				'date_modified'           => $order_query->row['date_modified'],
				'date_added'              => $order_query->row['date_added'],
				'ip'                      => $order_query->row['ip'],
				'id_asaas'                => $order_query->row['id_asaas'],
			);
		} else {
			return false;
		}
	}

	public function getOrders($start = 0, $limit = 20) {
		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 1;
		}

		$query = $this->db->query("SELECT o.order_id, o.firstname, o.lastname, os.name as status, o.date_added, o.total, o.currency_code, o.currency_value FROM `" . DB_PREFIX . "order` o LEFT JOIN " . DB_PREFIX . "order_status os ON (o.order_status_id = os.order_status_id) WHERE o.customer_id = '" . (int)$this->customer->getId() . "' AND o.order_status_id > '0' AND o.store_id = '" . (int)$this->config->get('config_store_id') . "' AND os.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY o.order_id DESC LIMIT " . (int)$start . "," . (int)$limit);

		return $query->rows;
	}

	public function getOrderProduct($order_id, $order_product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . (int)$order_product_id . "'");

		return $query->row;
	}

	public function getOrderProducts($order_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");

		return $query->rows;
	}

	public function getOrderOptions($order_id, $order_product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . (int)$order_product_id . "'");

		return $query->rows;
	}

	public function getOrderVouchers($order_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_voucher` WHERE order_id = '" . (int)$order_id . "'");

		return $query->rows;
	}

	public function getOrderTotals($order_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$order_id . "' ORDER BY sort_order");

		return $query->rows;
	}

	public function getOrderHistories($order_id) {
		$query = $this->db->query("SELECT date_added, os.name AS status, oh.comment, oh.notify FROM " . DB_PREFIX . "order_history oh LEFT JOIN " . DB_PREFIX . "order_status os ON oh.order_status_id = os.order_status_id WHERE oh.order_id = '" . (int)$order_id . "' AND os.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY oh.date_added");

		return $query->rows;
	}

	public function getTotalOrders() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order` o WHERE customer_id = '" . (int)$this->customer->getId() . "' AND o.order_status_id > '0' AND o.store_id = '" . (int)$this->config->get('config_store_id') . "'");

		return $query->row['total'];
	}

	public function getTotalOrderProductsByOrderId($order_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");

		return $query->row['total'];
	}

	public function getTotalOrderVouchersByOrderId($order_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order_voucher` WHERE order_id = '" . (int)$order_id . "'");

		return $query->row['total'];
	}

	#################################################
	################  COMPLEMENTO BLING #############
	#################################################

	//ORDERS BY FILTERS
	public function getAllOrdersFilters($start = 0, $limit = 20, $filters) {
		if ($start < 0) {
			$start = 0;
		}
		if ($limit < 1) {
			$limit = 1;
		}
		$filters = urldecode($filters);
		$filter = explode('|', $filters);
		
		$startDate = $filter[0];
		$finishDate = $filter[1];
		$status = $filter[2];
		
		if(empty($startDate)){
			$startDate = '1969-01-01';
		}
		if(empty($finishDate)){
			$finishDate = date('Y-m-d');
		}
		$sql = "SELECT o.order_id, o.firstname, o.lastname, o.email, o.telephone, o.total, o.currency_code, o.currency_value, o.customer_id,o.date_added,o.payment_firstname,o.payment_lastname,o.payment_company,o.payment_address_1,o.payment_address_2,o.payment_city,o.payment_postcode,o.payment_country,o.payment_zone,o.payment_method,o.payment_code,o.shipping_firstname,o.shipping_lastname,o.shipping_company,o.shipping_address_1,o.shipping_address_2,o.shipping_city,o.shipping_postcode,o.shipping_country,o.shipping_zone,o.shipping_method,o.shipping_code,o.comment, os.name as status
								   FROM `" . DB_PREFIX . "order` o LEFT JOIN " . DB_PREFIX . "order_status os ON (o.order_status_id = os.order_status_id)
								   WHERE ";

		if($status == 'tds'){
			$sql .= "o.order_status_id > 0";
		}else{
			$sql .= "o.order_status_id = '".$status."'";
		}
		$sql .= " AND ( o.date_added BETWEEN '".$startDate."' AND '".$finishDate."')  ORDER BY o.order_id DESC LIMIT " . (int)$start . "," . (int)$limit;
		
		$query = $this->db->query($sql);		
		return $query->rows;
	
	}

	//ORDER BY ID
	public function getOrderId($order_id){
		$query = $this->db->query("SELECT o.order_id, o.firstname, o.customer_id, o.lastname,  o.email, o.telephone, os.name as status, o.date_added, o.total, o.currency_code, o.currency_value, o.payment_firstname,o.payment_lastname,o.payment_company,o.payment_address_1,o.payment_address_2,o.payment_city,o.payment_postcode,o.payment_country,o.payment_zone,o.payment_method,o.payment_code,o.shipping_firstname,o.shipping_lastname,o.shipping_company,o.shipping_address_1,o.shipping_address_2,o.shipping_city,o.shipping_postcode,o.shipping_country,o.shipping_zone,o.shipping_method,o.shipping_code,o.comment 
								   FROM `" . DB_PREFIX . "order` o LEFT JOIN " . DB_PREFIX . "order_status os ON (o.order_status_id = os.order_status_id)  
								   WHERE o.order_id = '".$order_id."'"
								);
		return $query->rows;
	}
	
	//GETTING COUPON BY ORDER
	public function getCouponByOrder($order_id){
		$query = $this->db->query("SELECT  ot.value as valueCoupon, ot.code as codeCoupon
								   FROM " . DB_PREFIX . "order_total ot
								   WHERE ot.order_id = '".$order_id."'  AND (ot.code = 'coupon' or  ot.code = 'cupom') 
								   GROUP BY ot.value"
								  );
		return $query->rows;
	}
	
	//GETTING SHIPPING PRICE BY ORDER
	public function getShippingByOrder($order_id){
		$query = $this->db->query("SELECT  ot.value as valueShipping, ot.code as codeShipping
								   FROM " . DB_PREFIX . "order oo, " . DB_PREFIX . "order_total ot
								   WHERE ot.order_id = '".$order_id."' AND ( ot.code ='shipping' or ot.code = 'frete')
							 	   GROUP BY ot.value"
		);
		return $query->rows;
	}
	
	
	//PRODUCTS BY ID ORDER
	public function getProductOrderId($order_id){
		$query = $this->db->query("SELECT o.order_product_id, o.product_id, o.order_id, o.name, o.model, o.quantity, o.price, o.total, o.tax, o.reward, p.sku
					   FROM `" . DB_PREFIX . "order_product` o LEFT JOIN " . DB_PREFIX . "product p ON (o.product_id = p.product_id) 
					   WHERE `order_id` = '".$order_id."'"
					);
		return $query->rows;
	}

	//Get products variations in order
	public function getVariation($parameters){
		$query = $this->db->query("SELECT op.name as variationName, oo.name as nomeTipoVariacao, oo.value as tipoVariacao, opov.price as precoVaricao,  opov.price_prefix as prefixPrecoVaricao,  opov.weight as pesoVaricao, opov.weight_prefix as prefixPesoVaricao
				           FROM " . DB_PREFIX . "order_product op LEFT JOIN " . DB_PREFIX . "order_option oo ON (op.order_product_id = oo.order_product_id ) 
					   LEFT JOIN " . DB_PREFIX . "product_option_value  opov ON (oo.product_option_value_id = opov.product_option_value_id) 
					   WHERE op.order_id = '".$parameters['order_id']."' AND op.order_product_id = '".$parameters['order_product_id']."' AND oo.order_product_id is not null");
		
		return $query->rows;
	}
}