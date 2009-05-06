<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Handles all API requests for Highrise to FreshBooks Sync.
 *
 * @package Highrise_to_freshbooks
 * @author Kyle Hendricks kyleh@mendtechnologies.com
 **/

class Freshbooks_oauth{

	private $oauth_consumer_secret = 'c5d3c6a30d307d28b1a409c4e4399ecc';
	private $oauth_consumer_key = 'highrisehq';
	private $oauth_token = '';
	private $oauth_token_secret = '';
	private $oauth_signature_method = 'HMAC-SHA1';
	private $oauth_version = '1.0';
	private $callback_url = 'http://www.devinprogress.com/highrise_oa/index.php/settings/';
	private $relm_url = '';
	private $request_url = '';
	private $authorize_url = '';
	private $access_url = '';

	function __construct($settings){
		$this->relm_url = $settings['fburl'].'/api/2.1/xml-in';
		$this->request_url = $settings['fburl'].'/oauth/oauth_request.php';
		$this->authorize_url = $settings['fburl'].'/oauth/oauth_authorize.php';
		$this->access_url = $settings['fburl'].'/oauth/oauth_access.php';
		$this->oauth_token = $settings['fb_oauth_token'];
		$this->oauth_token_secret = $settings['fb_oauth_token_secret'];
	}
	
	private function get_nonce()
	{
		return uniqid('').time()*13;
	}
	
	private function get_timestamp()
	{
		return time();
	}
	
	private function calc_base_string($params, $method, $url)
	{
		ksort($params);
		$oauth_vars = '';
		foreach($params as $key => $value)
		{
			$oauth_vars .= $key.'='.$value.'&';
		}
		$base_string = $method.'&'.rawurlencode($url).'&'.rawurlencode(rtrim($oauth_vars, '&'));
	
		return $base_string;
	}
	
	function calc_hmac_sha1($base_string, $consumer_secret, $token_secret) 
	{

		$key = urlencode($consumer_secret).'&'.urlencode($token_secret);
		if (function_exists('hash_hmac'))
		{
			$signature = base64_encode(hash_hmac("sha1", $base_string, $key, true));
		}
		else
		{
		    $blocksize	= 64;
		    $hashfunc	= 'sha1';
		    if (strlen($key) > $blocksize)
		    {
		        $key = pack('H*', $hashfunc($key));
		    }
		    $key	= str_pad($key,$blocksize,chr(0x00));
		    $ipad	= str_repeat(chr(0x36),$blocksize);
		    $opad	= str_repeat(chr(0x5c),$blocksize);
		    $hmac 	= pack(
		                'H*',$hashfunc(
		                    ($key^$opad).pack(
		                        'H*',$hashfunc(
		                            ($key^$ipad).$base_string
		                        )
		                    )
		                )
		            );
			$signature = base64_encode($hmac);
		}
		return $signature;
	}
	
	function create_oauth_header($params)
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
	
	public function oauth_request()
	{
		$method = 'POST';
		$url = $this->request_url;
		
		$params = array(
			'oauth_nonce' => $this->get_nonce(),
		  'oauth_timestamp' => $this->get_timestamp(),
		  'oauth_consumer_key' => $this->oauth_consumer_key,
		  'oauth_signature_method' => $this->oauth_signature_method,
			'oauth_version' => $this->oauth_version,
			);
		
		$base_string = $this->calc_base_string($params, $method, $url);
		$consumer_secret = $this->oauth_consumer_secret;
		$token_secret = '';
		$signature = $this->calc_hmac_sha1($base_string, $consumer_secret, $token_secret);
		$oauth_signature = urlencode($signature);
		$params['oauth_signature'] = urlencode($signature);
		
		$uri = "oauth_version={$params['oauth_version']}&";
		$uri .= "oauth_consumer_key={$params['oauth_consumer_key']}&";
		$uri .= "oauth_signature_method={$params['oauth_signature_method']}&";
		$uri .= "oauth_signature={$params['oauth_signature']}&";
		$uri .= "oauth_timestamp={$params['oauth_timestamp']}&";
		$uri .= "oauth_nonce={$params['oauth_nonce']}";
		
		$header = array();
		$header[] = $this->create_oauth_header($params);
		
		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $uri);
		curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,10);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$result = curl_exec($ch);
		curl_close($ch);
		parse_str($result, $oauth_request_results);
		
		return $oauth_request_results;
	}
	
	public function create_authorize_url()
	{
		
		$request_results = $this->oauth_request();
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
	
	public function oauth_access($oauth_settings)
	{
		$method = 'POST';
		$url = $this->access_url;
		
		$params = array(
			'oauth_nonce' => $this->get_nonce(),
		  'oauth_timestamp' => $this->get_timestamp(),
		  'oauth_consumer_key' => $this->oauth_consumer_key,
		  'oauth_signature_method' => $this->oauth_signature_method,
			'oauth_version' => $this->oauth_version,
			);
		$params['oauth_token'] = $oauth_settings['token'];
		// $header = array(
		// 	'Authorization: OAuth realm' => "\"\",",
		// 	'oauth_consumer_key' => "\"{$params['oauth_consumer_key']}\"";
		// 	'oauth_consumer_key' => "\"{$params['oauth_consumer_key']}\"";
		// );
		
		
		$base_string = $this->calc_base_string($params, $method, $url);
		$consumer_secret = $this->oauth_consumer_secret;
		$token_secret = $oauth_settings['token_secret'];
		$signature = $this->calc_hmac_sha1($base_string, $consumer_secret, $token_secret);
		$oauth_signature = urlencode($signature);
		$params['oauth_signature'] = urlencode($signature);
		
		$uri = "oauth_version={$params['oauth_version']}&";
		$uri .= "oauth_token={$params['oauth_token']}&";
		$uri .= "oauth_consumer_key={$params['oauth_consumer_key']}&";
		$uri .= "oauth_signature_method={$params['oauth_signature_method']}&";
		$uri .= "oauth_signature={$params['oauth_signature']}&";
		$uri .= "oauth_timestamp={$params['oauth_timestamp']}&";
		$uri .= "oauth_nonce={$params['oauth_nonce']}";
		
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
	  curl_setopt($ch, CURLOPT_POST, true);
	  curl_setopt($ch, CURLOPT_POSTFIELDS, $uri);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$result = curl_exec($ch);
		curl_close($ch);
		parse_str($result, $oauth_request_results);
		
		return $oauth_request_results;
		
	}
	
	public function get_fb_assets($xml){
		$method = 'POST';
		$url = $this->relm_url;
		
		$params = array(
			'oauth_token' => $this->oauth_token,
			'oauth_nonce' => $this->get_nonce(),
		  'oauth_timestamp' => $this->get_timestamp(),
		  'oauth_consumer_key' => $this->oauth_consumer_key,
		  'oauth_signature_method' => $this->oauth_signature_method,
			'oauth_version' => $this->oauth_version,
			);
		
		$base_string = $this->calc_base_string($params, $method, $url);
		$consumer_secret = $this->oauth_consumer_secret;
		$token_secret = $this->oauth_token_secret;
		$signature = $this->calc_hmac_sha1($base_string, $consumer_secret, $token_secret);
		$oauth_signature = urlencode($signature);
		$params['oauth_signature'] = urlencode($signature);
		
		
		// 
		// $uri = "oauth_version={$params['oauth_version']}&";
		// $uri .= "oauth_token={$params['oauth_token']}&";
		// $uri .= "oauth_consumer_key={$params['oauth_consumer_key']}&";
		// $uri .= "oauth_signature_method={$params['oauth_signature_method']}&";
		// $uri .= "oauth_signature={$params['oauth_signature']}&";
		// $uri .= "oauth_timestamp={$params['oauth_timestamp']}&";
		// $uri .= "oauth_nonce={$params['oauth_nonce']}";
		//
		//$uri = http_build_query($params);
		
		$header = array();
		$header[] = $this->create_oauth_header($params); 
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,10);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$result = curl_exec($ch);
		curl_close($ch);

		//return simplexml_load_string($result);
		return $result;
		
	}
	
	function get_fb_clients()
	{
		$xml =<<<EOL
		<?xml version="1.0" encoding="utf-8"?>
		<request method="client.list">
		  <page>1</page>
		  <per_page>10</per_page>
		</request>
EOL;
	
	$clients = $this->get_fb_assets($xml);
	
	return $clients;
		
	}
	
	
	
	
	
	
}