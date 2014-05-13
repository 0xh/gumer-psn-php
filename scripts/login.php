<?php
	class PSNLogIn {
		private $psnURLS, $psnVars;
		private $email, $password;
		private $cookieDir, $cookieFile;
				
		public function __construct() {
			$this->cookieFile = realpath(dirname(__FILE__)) .'/tmp/psn_' .str_replace('.', '_', $_SERVER["REMOTE_ADDR"]) .'.cookie';
			
			$this->psnVars = array(
				'SENBaseURL' => 'https://auth.api.sonyentertainmentnetwork.com',
				'redirectURL_oauth' => 'com.scee.psxandroid.scecompcall://redirect',
				'client_id' => 'b0d0d7ad-bb99-4ab1-b25e-afa0c76577b0',
				'scope' => 'sceapp',
				'scope_psn' => 'psn:sceapp',
				'client_secret' => 'Zo4y8eGIa3oazIEp',
				'duid' => '00000005006401283335353338373035333434333134313a433635303220202020202020202020202020202020',
				'cltm' => '1399637146935',
				'service_entity' => 'urn:service-entity:psn'
			);
			
			$this->psnURLS = array(
				'signIn' => $this->psnVars['SENBaseURL'] .'/2.0/oauth/authorize?response_type=code&service_entity=' .$this->psnVars['service_entity'] .'&returnAuthCode=true&cltm=' .$this->psnVars['cltm'] .'&redirect_uri=' .$this->psnVars['redirectURL_oauth'] .'&client_id=' .$this->psnVars['client_id'] .'&scope=' .$this->psnVars['scope_psn'],
				'signInPost' => $this->psnVars['SENBaseURL'] .'/login.do',
				'oauth' => 'https://auth.api.sonyentertainmentnetwork.com/2.0/oauth/token'
			);
		}
		
		public function setEmail($email) {
			$this->email = $email;
		}
		
		public function setPassword($password) {
			$this->password = $password;
		}
		
		public function initCURL() {
			if(file_exists($this->cookieFile))	
				unlink($this->cookieFile);
				
			touch($this->cookieFile);
		
			$this->curl = curl_init();
			
			$options = array(
				CURLOPT_SSL_VERIFYPEER => 0,
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_COOKIEFILE => $this->cookieFile
			);

			curl_setopt_array($this->curl, $options);
		}
		
		public function closeCURL() {
			curl_close($this->curl);
			
			if(file_exists($this->cookieFile))	
				unlink($this->cookieFile);
		}
		
		public function login() {
			$this->initCURL();
			
			$options = array(
				CURLOPT_URL => $this->psnURLS['signIn'],
				CURLOPT_HEADER => 1,
				CURLOPT_POST => 0
			);

			curl_setopt_array($this->curl, $options);
			
			$output = curl_exec($this->curl);		
			$header = $this->get_headers_from_curl_response($output);
			
			$tokens = $this->getAuth($header['Location']);
			
			$this->closeCURL();
			
			echo json_encode($tokens);
		}
		
		private function getAuth($location) {
			$headerData = array(
				'Origin: https://auth.api.sonyentertainmentnetwork.com',
				'Referer: ' .$location
			);
		
			$postData = array(
				'j_username' => $this->email,
				'j_password' => $this->password,
				'params' => 'service_entity=psn'
			);
			
			$options = array(
				CURLOPT_URL => $this->psnURLS['signInPost'],
				CURLOPT_POST => 1,
				CURLOPT_HEADER => 1,
				CURLOPT_POSTFIELDS => http_build_query($postData),
				CURLOPT_HTTPHEADER => $headerData
			);

			curl_setopt_array($this->curl, $options);
			
			$output = curl_exec($this->curl);
			$header = $this->get_headers_from_curl_response($output);
			
			$options = array(
				CURLOPT_URL => $header['Location'],
				CURLOPT_POST => 0,
				CURLOPT_HEADER => 1
			);

			curl_setopt_array($this->curl, $options);
			
			$output = curl_exec($this->curl);
			$header = $this->get_headers_from_curl_response($output);
			
			$location = urldecode($header['Location']);
			$authCode = substr($location, strpos($location, 'authCode=') +9, 6);
			
			return $this->getAccessToken($authCode);
		}
		
		function get_headers_from_curl_response($response) {
			$headers = array();
			$header_text = substr($response, 0, strpos($response, "\r\n\r\n"));
			
			foreach(explode("\r\n", $header_text) as $i => $line) {
				if($i === 0) {
					$headers['http_code'] = $line;
				} else {
					list ($key, $value) = explode(': ', $line);

					$headers[$key] = $value;
				}
			}

			return $headers;
		}
		
		private function getAccessToken($authCode) {
			$dataArray = array(
				'grant_type' => 'authorization_code',
				'client_id' => $this->psnVars['client_id'],
				'client_secret' => $this->psnVars['client_secret'],
				'code' => $authCode,
				'redirect_uri' => $this->psnVars['redirectURL_oauth'],
				'state' => 'x',
				'scope' => $this->psnVars['scope_psn'],
				'duid' => $this->psnVars['duid']
			);
			
			$postData = http_build_query($dataArray);
			
			$options = array(
				CURLOPT_URL => $this->psnURLS['oauth'],
				CURLOPT_POST => 1,
				CURLOPT_HEADER => 0,
				CURLOPT_POSTFIELDS => $postData
			);

			curl_setopt_array($this->curl, $options);
			
			$output = curl_exec($this->curl);			
			$jsonData = json_decode($output, true);
			
			return $jsonData;
		}
		
		public function refreshTokens($refreshToken) {
			$this->initCURL();
			
			$dataArray = array(
				'grant_type' => 'refresh_token',
				'client_id' => $this->psnVars['client_id'],
				'client_secret' => $this->psnVars['client_secret'],
				'refresh_token' => $refreshToken,
				'redirect_uri' => $this->psnVars['redirectURL'],
				'state' => 'x',
				'scope' => $this->psnVars['scope_psn'],
				'duid' => $this->psnVars['duid']
			);
			
			$postData = http_build_query($dataArray);
			
			curl_setopt($this->curl, CURLOPT_POSTFIELDS, $postData);
			curl_setopt($this->curl, CURLOPT_URL, $this->psnURLS['oauth']);
			
			$output = curl_exec($this->curl);
			$this->closeCURL();
			
			$jsonData = json_decode($output, true);
			
			if (empty($jsonData['access_token']) || empty($jsonData['refresh_token'])) {
				return -4;
				
			} else {
				$toReturn = array(
					'accessToken' => $jsonData['access_token'],
					'refreshToken' => $jsonData['refresh_token']
				);
				
				return json_encode($toReturn);
			}
		}
		
		public function print_r($r) {
			echo '<pre>';
			print_r($r);
			echo '</pre>';
		}
	}
?>
