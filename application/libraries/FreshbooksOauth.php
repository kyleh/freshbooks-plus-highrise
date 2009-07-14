<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Handles all API requests for Highrise to FreshBooks Sync.
 *
 * @package Highrise_to_freshbooks
 * @author Kyle Hendricks kyleh@mendtechnologies.com
 **/

class FreshbooksOauth{

	private $oauth_consumer_secret = 'b83b947d1287299e9e04ccf92f4061dd';
	private $oauth_consumer_key = 'highrisehq';
	private $oauth_token = '';
	private $oauth_token_secret = '';
	private $oauth_signature_method = 'PLAINTEXT';
	private $oauth_version = '1.0';
	private $callback_url = 'http://www.devinprogress.com/highrise_oa/index.php/settings/';
	private $relm_url = '';
	private $request_url = '';
	private $authorize_url = '';
	private $access_url = '';

	function __construct($settings){
		$this->relm_url = $settings['fb_url'].'/api/2.1/xml-in';
		$this->request_url = $settings['fb_url'].'/oauth/oauth_request.php';
		$this->authorize_url = $settings['fb_url'].'/oauth/oauth_authorize.php';
		$this->access_url = $settings['fb_url'].'/oauth/oauth_access.php';
		$this->oauth_token = $settings['fb_oauth_token'];
		$this->oauth_token_secret = $settings['fb_oauth_token_secret'];
	}
	
	private function _get_nonce()
	{
		return uniqid('').time()*13;
	}
	
	private function _get_timestamp()
	{
		return time();
	}
	
	// private function calc_base_string($params, $method, $url)
	// {
	// 	ksort($params);
	// 	$oauth_vars = '';
	// 	foreach($params as $key => $value)
	// 	{
	// 		$oauth_vars .= $key.'='.$value.'&';
	// 	}
	// 	$base_string = $method.'&'.rawurlencode($url).'&'.rawurlencode(rtrim($oauth_vars, '&'));
	// 
	// 	return $base_string;
	// }
	// 
	// function calc_hmac_sha1($base_string, $consumer_secret, $token_secret) 
	// {
	// 
	// 	$key = urlencode($consumer_secret).'&'.urlencode($token_secret);
	// 	if (function_exists('hash_hmac'))
	// 	{
	// 		$signature = base64_encode(hash_hmac("sha1", $base_string, $key, true));
	// 	}
	// 	else
	// 	{
	// 	    $blocksize	= 64;
	// 	    $hashfunc	= 'sha1';
	// 	    if (strlen($key) > $blocksize)
	// 	    {
	// 	        $key = pack('H*', $hashfunc($key));
	// 	    }
	// 	    $key	= str_pad($key,$blocksize,chr(0x00));
	// 	    $ipad	= str_repeat(chr(0x36),$blocksize);
	// 	    $opad	= str_repeat(chr(0x5c),$blocksize);
	// 	    $hmac 	= pack(
	// 	                'H*',$hashfunc(
	// 	                    ($key^$opad).pack(
	// 	                        'H*',$hashfunc(
	// 	                            ($key^$ipad).$base_string
	// 	                        )
	// 	                    )
	// 	                )
	// 	            );
	// 		$signature = base64_encode($hmac);
	// 	}
	// 	return $signature;
	// }
	
	private function _create_oauth_header($params)
	{
		///$relm = $this->relm_url;
		//$auth_relm = 'Authorization: OAuth realm="'.$this->raw_relm.'"';
		$h   = array();
		$h[] = 'Authorization: OAuth realm=""';
		foreach ($params as $name => $value)
		{
				$h[] = $name.'="'.$value.'"';
		}
		$hs = implode($h, ",\n    ");
		return $hs;
	}
	
	// public function oauth_request()
	// {
	// 	$method = 'POST';
	// 	$url = $this->request_url;
	// 	
	// 	$params = array(
	// 		'oauth_nonce' => $this->get_nonce(),
	// 	  'oauth_timestamp' => $this->get_timestamp(),
	// 	  'oauth_consumer_key' => $this->oauth_consumer_key,
	// 	  'oauth_signature_method' => $this->oauth_signature_method,
	// 		'oauth_version' => $this->oauth_version,
	// 		);
	// 	
	// 	$base_string = $this->calc_base_string($params, $method, $url);
	// 	$consumer_secret = $this->oauth_consumer_secret;
	// 	$token_secret = '';
	// 	$signature = $this->calc_hmac_sha1($base_string, $consumer_secret, $token_secret);
	// 	$oauth_signature = urlencode($signature);
	// 	$params['oauth_signature'] = urlencode($signature);
	// 	
	// 	$uri = "oauth_version={$params['oauth_version']}&";
	// 	$uri .= "oauth_consumer_key={$params['oauth_consumer_key']}&";
	// 	$uri .= "oauth_signature_method={$params['oauth_signature_method']}&";
	// 	$uri .= "oauth_signature={$params['oauth_signature']}&";
	// 	$uri .= "oauth_timestamp={$params['oauth_timestamp']}&";
	// 	$uri .= "oauth_nonce={$params['oauth_nonce']}";
	// 	
	// 	$header = array();
	// 	$header[] = $this->create_oauth_header($params);
	// 	
	// 	$ch = curl_init();
	// 	
	// 	curl_setopt($ch, CURLOPT_URL, $url);
	// 	curl_setopt($ch, CURLOPT_POST, true);
	// 	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	// 	curl_setopt($ch, CURLOPT_POSTFIELDS, $uri);
	// 	curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,10);
	// 	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	// 	$result = curl_exec($ch);
	// 	curl_close($ch);
	// 	parse_str($result, $oauth_request_results);
	// 	
	// 	return $oauth_request_results;
	// }
	
	
	//obtains a request token using PLAINTEXT method
	private function _obtain_request_token()
	{
		$method = 'POST';
		$url = $this->request_url;
		//signature for plaintext method
		$signature = urlencode($this->oauth_consumer_secret.'&');
		$timestamp = $this->_get_timestamp();
		$nonce = $this->_get_nonce();
		
		$params = array(
			'oauth_consumer_key' => $this->oauth_consumer_key,
			'oauth_signature_method' => $this->oauth_signature_method,
			'oauth_signature' => $signature,
			'oauth_timestamp' => $timestamp,
			'oauth_nonce' => $nonce
			);
		
		$post_fields = http_build_query($params);
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
		curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,10);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$result = curl_exec($ch);
		$info = curl_getinfo($ch);
		curl_close($ch);
		
		$http_code = $info['http_code'];
		if ($info['http_code'] != 200) {
			throw new Exception('Error connecting to FreshBooks.  Please check your settings and try again');
		}else{
			parse_str($result, $oauth_request_results);
			return $oauth_request_results;
		}
	}
	
	//creates authorization url to access freshbooks
	//returns array containing url + token + token secret
	public function create_authorize_url()
	{
		
		$request_results = $this->_obtain_request_token();
		$url = $this->authorize_url;
		$callback_url = urlencode($this->callback_url);
		
		$uri = "oauth_token={$request_results['oauth_token']}&";
		$uri .= "oauth_callback={$callback_url}";
		$uri .= 'request_token_ready';
		
		//returns url, token, and token secret
		$authorize_data = array(
			'url' => $url.'?'.$uri,
			'token' => $request_results['oauth_token'],
			'token_secret' => $request_results['oauth_token_secret'],
			);
		
		return $authorize_data;
	}
	
	public function obtain_access_token($oauth_settings)
	{
		$method = 'POST';
		$url = $this->access_url;
		//signature for plaintext method
		$signature = urlencode($this->oauth_consumer_secret.'&'.$oauth_settings['token_secret']);
		$timestamp = $this->_get_timestamp();
		$nonce = $this->_get_nonce();
		$oauth_token = $oauth_settings['token'];
		
		$params = array(
			'oauth_consumer_key' => $this->oauth_consumer_key,
			'oauth_token' => $oauth_token,
			'oauth_signature_method' => $this->oauth_signature_method,
			'oauth_signature' => $signature,
			'oauth_timestamp' => $timestamp,
			'oauth_nonce' => $nonce
			);
		
		$post_fields = http_build_query($params);
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
		curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,10);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$result = curl_exec($ch);
		$info = curl_getinfo($ch);
		curl_close($ch);
		
		$http_code = $info['http_code'];
		if ($info['http_code'] != 200) {
			throw new Exception('Error connecting to FreshBooks.  Please check your settings and try again');
		}else{
			parse_str($result, $oauth_request_results);
			return $oauth_request_results;
		}
	}
	
	public function get_fb_assets($xml=NULL){
		$method = 'POST';
		$url = $this->relm_url;

		//signature for plaintext method
		$signature = urlencode($this->oauth_consumer_secret.'&'.$this->oauth_token_secret);
		$timestamp = $this->_get_timestamp();
		$nonce = $this->_get_nonce();
		
		$params = array(
			'oauth_consumer_key' => $this->oauth_consumer_key,
			'oauth_token' => $this->oauth_token,
			'oauth_signature_method' => $this->oauth_signature_method,
			'oauth_signature' => $signature,
			'oauth_timestamp' => $timestamp,
			'oauth_nonce' => $nonce
			);
		
		$header = array();
		$header[] = $this->_create_oauth_header($params);
				
		//return $header;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,10);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$result = curl_exec($ch);
		curl_close($ch);

		return $result;
	}
	
	function test_fb_settings()
	{
		$xml =<<<EOL
		<?xml version="1.0" encoding="utf-8"?>
		<request method="client.list">
		  <page>1</page>
		  <per_page>100</per_page>
		</request>
EOL;
	
	$clients = $this->get_fb_assets($xml);
	
	return $clients;
		
	}
	
	
	
	
	
	
}