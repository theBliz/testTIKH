<?php

class MarketCloud extends CI_Model {
	const PUBLICKEY = 'uMny+CePVQAUHq9aZg9uzFdsAFETzVOfDLaiaaCMpA4=';
	const SECRETKEY = '9bc852d1-413e-41ee-9d01-8df9d2481faf';
	private $token;
	private $authString = NULL;
	
	public function __construct()
	{
		parent::__construct();
	}
	
	private function isLogged()
	{
		if(!is_null($this->authString) && !is_null($this->token) && !empty($this->authString) && !empty($this->token))
			return TRUE;
		else
			return FALSE;
	}
    
    public function autenticate($private = NULL, $public = NULL)
    {
		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => "http://api.marketcloud.it/v0/tokens",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => "{\"publicKey\" : \"".($public ? $public : self::PUBLICKEY)."\",\"secretKey\" : \"".($private ? $private : self::SECRETKEY)."\",\"timestamp\" : ".time()."}",
			CURLOPT_HTTPHEADER => array(
				"accept: application/json",
				"content-type: application/json"
			),
		));
		
		$response = curl_exec($curl);
		$response = json_decode($response);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
			return FALSE;
		} else {
			$this->token = $response['token'];
			$this->authString = ($public ? $public : self::PUBLICKEY).':'.$response['token'];
			return TRUE;
		}
    }
	
	public function fetchEntity($entity = '', $id = NULL, $select = NULL)
	{
		if(!$this->isLogged()) return FALSE;
		if(empty($entity)) return FALSE;
		
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => "http://api.marketcloud.it/v0/".$entity.((!is_null($id))?'/'.$id:''),
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_HTTPHEADER => array(
				"accept: application/json",
				"authorization: ".$this->authString,
				"content-type: application/json"
			),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err)
		{
			return "cURL Error #:" . $err;
		} else
		{
			return json_decode($response);
		}
	}

	public function setEntity($entity, $data, $id = NULL)
	{
		if(!$this->isLogged()) return FALSE;
		if(is_null($entity)) return FALSE;
		
		if(is_array($data) && !empty($data))
		{
			$dataSet = '';
			foreach ($data as $attributo => $valore)
			{
				$dataSet .= ' "'.$attributo.'" : "'.$valore.'",';
			}
			$dataSet = trim($dataSet, ',');
		}
		else
		{
			return FALSE;
		}
		
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => "http://api.marketcloud.it/v0/".$entity.((!is_null($id))?'/'.$id:''),
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "PUT",
			CURLOPT_POSTFIELDS => "{".$dataSet."}",
			CURLOPT_HTTPHEADER => array(
				"accept: application/json",
				"authorization: ".$this->authString,
				"content-type: application/json"
			),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
			return "cURL Error #:" . $err;
		} else {
			return json_decode(response);
		}
	}
}
