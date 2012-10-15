<?php

#
# 
# by erusev
#
#

class GoogleOAuth
{
	const SCOPE_ANALYTICS = 'https://www.googleapis.com/auth/analytics.readonly';
	
	# More scope constants to be added...
	
	const AUTH_URL = 'https://accounts.google.com/o/oauth2/auth';
	const TOKEN_URL = 'https://accounts.google.com/o/oauth2/token';
	
	static function create($client_id, $client_secret)
	{
		return new GoogleOAuth($client_id, $client_secret);
	}
	
	function __construct($client_id, $client_secret)
	{
		$this->client_id = $client_id;
		$this->client_secret = $client_secret;
	}
	
	private $client_id;
	private $client_secret;
	
	# 
	# Public 
	# 
	
	# Caller URL should be listed in Google Console.
	
	function obtain_refresh_token($scope)
	{
		$url_scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' 
			? 'https://' 
			: 'http://';
		
		$redirect_uri = $url_scheme . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		
		if (isset($_GET['code']))
		{
			# Removes "code" parameter from $redirect_uri.
			$redirect_uri = str_replace('code=' . $_GET['code'], '', $redirect_uri);
			$redirect_uri = preg_replace('/(\?|&)$/', '', $redirect_uri);
			
			$token_url = self::TOKEN_URL;
			
			$data = array(
				'code' => $_GET['code'],
				'client_id' => $this->client_id,
				'client_secret' => $this->client_secret,
				'redirect_uri' => $redirect_uri,
				'grant_type' => 'authorization_code',
			);
			
			$response = $this->post_request($token_url, $data);
			$response = json_decode($response);
			
			return $response->refresh_token;
		}
		else
		{
			$data = array(
				'access_type' => 'offline',
				'approval_prompt' => 'force',
				'client_id' => $this->client_id,
				'redirect_uri' => $redirect_uri,
				'response_type' => 'code',
				'scope' => $scope,
			);
			
			$query = http_build_query($data);
			
			$auth_url = self::AUTH_URL . '?' . $query;
			
			header('Location: ' . $auth_url);
		}
	}
	
	function obtain_access_token($refresh_token)
	{
		$data = array(
			'client_id' => $this->client_id,
			'client_secret' => $this->client_secret,
			'refresh_token' => $refresh_token,
			'grant_type' => 'refresh_token',
		);
		
		$response = $this->post_request(self::TOKEN_URL, $data);
		$response = json_decode($response);
		
		return $response->access_token;
	}
	
	# 
	# Private 
	# 
	
	private function post_request($url, array $data)
	{
		$handler = curl_init();

		curl_setopt($handler, CURLOPT_URL, $url);
		curl_setopt($handler, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($handler, CURLOPT_POST, 1);
		curl_setopt($handler, CURLOPT_POSTFIELDS, $data);
		
		$response = curl_exec($handler);
		
		curl_close($handler);

		return $response;
	}
}

