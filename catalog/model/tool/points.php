<?php
class ModelToolPoints extends Model {

	private function getRequest($username, $controllerApi, $moduloApi, $parametros = ""){
			try{
					$result = file_get_contents(URL_APITBEST.$controllerApi."/".$moduloApi."?username=".$username."&token=".$this->encryptPassword($username).$parametros);
					return $result;
			}catch(Exception $e){
					throw new Exception('Problema com conexao do servidor '.$e->getMessage());
			}
	}

	private function tratarReturn($returnRequest, $password){
		if($returnRequest != null){
			//$pos403 = strpos($returnRequest, "403");
			//$pos500 = strpos($returnRequest, "500");
			//if($pos500 > 0){
			//		return "Erro no servidor, por favor contacte suporte.";
			//}

			$dados = json_decode($returnRequest, true);
			
			if($password){
				if(password_verify($password, $dados['users']['encrypted_password'])){
					return $dados;
				}else{
					return 'Login ou Senha incorreta';
				};	
			}else{
				return $dados;
			}					
		}
	}

	private function encryptPassword($username){
		return password_hash($username.TOKENTBEST, PASSWORD_BCRYPT);
	}

	private function isTbest(){
		if($this->customer->isLogged()){
			$this->load->model('account/customer');
			$customer = $this->model_account_customer->getCustomer($this->customer->getId());
			if($customer["logintbest"]){
				return array(
					'login' => $customer["logintbest"],
					'senha' => ""
				);
			}
		}
		return false;
	}

	private function fees($price, $shared_percentage){
		$price = str_replace(["R$",","],["","."], $price);
		return round((((float)$price * (float)$shared_percentage) / 100), 2);
	}

	public function isTbestP(){
		return $this->isTbest();
	}

	public function points($price, $infTbest){
		if(isset($infTbest['infoParceiro']['shared_percentage'])){
			$repasse = $this->fees($price, $infTbest['infoParceiro']['shared_percentage']);
		}else{
			$repasse = 0;
		}
        return round($repasse * $infTbest['infoConsumidor'], 2);
	}

	public function showPoints($price, $infTbest) {
		if($this->isTbest()){
			$pontos = $this->points($price, $infTbest);
			if($pontos > 0){
				$pontos = number_format($pontos, 2, ',', ' ');
			}
			if((float)str_replace(["R$",","],["","."], $price) > 0){
				return $price." - ".$pontos." pts";
			}else{
				return $price;
			}
		}else{
			return $price;
		}
	}

	public function getInfoTbest(){
		$customer = $this->isTbest();
		if($customer){
			$infoParceiro = $this->tratarReturn($this->getRequest($customer["login"], "physical", "info-parceiro", "&parceiro=1"), false);
			$infoConsumidor = $this->tratarReturn($this->getRequest($customer["login"], "physical", "info-consumidor-porcent", "&consumidor=".$customer["login"]), false);

			return $dados = array(
				'infoParceiro' => $infoParceiro,
				'infoConsumidor' => $infoConsumidor,
			);
		}
	}

	public function regiterPointTbest($invoice, $valor){
		$customer = $this->isTbest();
		if($customer){
			$parametros = "&invoice=".$invoice."&valor=".str_replace([".",","],["",""], number_format($valor, 2, ',', ''));
			$result = $this->getRequest("55273", "physical", "registra-venda", $parametros);
			return $result;
		}else{
			return "Cliente não logado, ou não faz parte da tbest.";
		}
	}

	public function getInfoPlanoMes(){
		$customer = $this->isTbest();
		if($customer){
			$inicial = $this->tratarReturn($this->getRequest($customer["login"], "physical", "get-points-month"), false);
			$final = $this->tratarReturn($this->getRequest($customer["login"], "physical", "get-points-plane"), false);

			return array(
				'inicial' => $inicial,
				'final' => $final,
				'saldo' => floatval($final)-floatval($inicial)
			);
		}
	}

	public function getUsuarioTbest($username, $password){
		if(utf8_strlen(trim($username)) > 0  && utf8_strlen(trim($password)) > 0){
			return $this->tratarReturn($this->getRequest($username, "physical", "documento-consumer", "&documento=".$username), $password);
		}else{
			return 'Login ou Senha obrigatório';
		}
	}

	public function getUsuarioTbestSemSenha($username){
		if(utf8_strlen(trim($username)) > 0){
			return $this->tratarReturn($this->getRequest($username, "physical", "documento-consumer", "&documento=".$username), false);
		}else{
			return 'Login ou Senha obrigatório';
		}
	}
}