<?php
class ControllerCheckoutOrderAsaas extends Controller {
    public function confirmwebhook() {
        $json = array();

        $json = $this->request->post;

        $this->load->model('checkout/order');

        $this->model_checkout_order->addOrderHistory($json['payment']['externalReference'], 5, "Alterado via WebHook Asaas", true);

        $this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
    }
}