<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Handles all API requests for Highrise to FreshBooks Sync.
 *
 * @package Highrise_to_freshbooks
 * @author Kyle Hendricks kyleh@mendtechnologies.com
 **/

class FreshbooksOauth{

	private $oauth_consumer_secret = 'WdvG8BepAkfpErLtxpF9fePctpAVQ5DwW';
	private $oauth_consumer_key = 'highrisehq';
	private $oauth_token = '';
	private $oauth_token_secret = '';
	private $oauth_signature_method = 'PLAINTEXT';
	private $oauth_version = '1.0';
	private $callback_url = 'http://highrise.devinprogress.com/index.php/oa_settings/request_token_ready';
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
	
	//TODO: May not need with new implementation	
	private function _create_oauth_header($params)
	{
		$h   = array();
		$h[] = 'Authorization: OAuth realm=""';
		foreach ($params as $name => $value)
		{
				$h[] = $name.'="'.$value.'"';
		}
		$hs = implode($h, ",\n    ");
		return $hs;
	}
	
		//obtains a request token using PLAINTEXT method
	private function _obtain_request_token()
	{
		$method = 'POST';
		$url = $this->request_url;
		//signature for plaintext method
		$signature = $this->oauth_consumer_secret.'&'.$this->oauth_token_secret;
		$timestamp = $this->_get_timestamp();
		$nonce = $this->_get_nonce();
		$callback_url = $this->callback_url;
		
		$params = array(
			'oauth_consumer_key' => $this->oauth_consumer_key,
			'oauth_signature_method' => $this->oauth_signature_method,
			'oauth_signature' => $signature,
			'oauth_timestamp' => $timestamp,
			'oauth_nonce' => $nonce,
			'oauth_callback' => $callback_url
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
			throw new Exception('HTTP Code: '.$http_code.'.  Error connecting to FreshBooks.  Please check your settings and try again');
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
		
		$uri = "oauth_token={$request_results['oauth_token']}";
		
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
		$signature = $this->oauth_consumer_secret.'&'.$this->oauth_token_secret;
		$timestamp = $this->_get_timestamp();
		$nonce = $this->_get_nonce();
		$token = $oauth_settings['token'];
		$verifier = $oauth_settings['verifier'];
		
		$params = array(
			'oauth_consumer_key' => $this->oauth_consumer_key,
			'oauth_token' => $token,
			'oauth_signature_method' => $this->oauth_signature_method,
			'oauth_signature' => $signature,
			'oauth_timestamp' => $timestamp,
			'oauth_nonce' => $nonce,
			'oauth_verifier' => $verifier
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