<?php
class ControllerAccountAcessotbest extends Controller {
	private $error = array();

	public function index() {
        if(!isset($this->request->get['username']) || !isset($this->request->get['token'])){
            echo "Dados Inválidos";
            return false;
        }

        if(!password_verify($this->request->get['username'].TOKENTBEST, $this->request->get['token'])){
            echo "Informações errada!";
            return false;
        }

        if ($this->customer->isLogged()) {
			$this->response->redirect($this->url->link('common/home', '', true));
		}

        $this->load->model('account/customer');
        
        $customer = $this->model_account_customer->getCustomerTbest($this->request->get['username']);
        
        if(isset($customer['customer_id'])){
            if($this->customer->login($customer['email'], '', true)){
                $this->load->model('account/address');

				if ($this->config->get('config_tax_customer') == 'payment') {
					$this->session->data['payment_address'] = $this->model_account_address->getAddress($this->customer->getAddressId());
				}

				if ($this->config->get('config_tax_customer') == 'shipping') {
					$this->session->data['shipping_address'] = $this->model_account_address->getAddress($this->customer->getAddressId());
				}

				$this->response->redirect($this->url->link('common/home', '', true));
            }
        }else{
            $this->load->model('tool/points');
            $result = $this->model_tool_points->getUsuarioTbestSemSenha($this->request->get['username']);

            if($result){
				
				$result['senha'] = $result['users']['encrypted_password'];

				$this->load->model('account/customer');
				$this->model_account_customer->addCustomerAjax($result);

				$this->model_account_customer->deleteLoginAttempts($result['legalperson']['email']);

				$this->customer->login($result['legalperson']['email'], $result['senha']);

				unset($this->session->data['guest']);

				$this->load->model('account/address');
                $this->model_account_address->addAddressAjax($result);
                
                $this->response->redirect($this->url->link('common/home', '', true));
			}
        }
    }
}