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
	private $oauth_signature_method = 'PLAINTEXT';
	private $oauth_version = '1.0';
	private $callback_url = 'http://highrise.devinprogress.com/index.php/settings/request_token_ready';
	private $oauth_token = '';
	private $oauth_token_secret = '';
	private $relm_url = '';
	private $request_url = '';
	private $authorize_url = '';
	private $access_url = '';

	public function __construct($settings)
	{
		$this->relm_url = $settings['fb_url'].'/api/2.1/xml-in';
		$this->request_url = $settings['fb_url'].'/oauth/oauth_request.php';
		$this->authorize_url = $settings['fb_url'].'/oauth/oauth_authorize.php';
		$this->access_url = $settings['fb_url'].'/oauth/oauth_access.php';
		$this->oauth_token = $settings['fb_oauth_token'];
		$this->oauth_token_secret = $settings['fb_oauth_token_secret'];
	}
	
	////
	//Api Asset Functions
	////
	public function validate_fb_settings()
	{
		$xml  = '<?xml version="1.0" encoding="utf-8"?>';
		$xml .= '<request method="client.list">';
		$xml .= '<page>1</page>';
		$xml .= '<per_page>5</per_page>';
		$xml .= '</request>';
	
		$clients = $this->_fb_api_request($xml);
		return $clients ? TRUE : FALSE;
	}
	
	public function get_fb_clients($page=1)
	{
		$xml  = '<?xml version="1.0" encoding="utf-8"?>';
		$xml .= '<request method="client.list">';
		$xml .= "<page>{$page}</page>";
		$xml .= '<per_page>100</per_page>';
		$xml .= '</request>';
		
		return $this->_fb_api_request($xml); 
	}
	
	//takes array of client data and sends to fb
	public function add_fb_client($client)
	{
		$fname = $client['first_name'];
		$lname = $client['last_name'];
		$email = $client['email'];
		$company = $client['company'];
		$work_num = $client['work_num'];
		$home_num = $client['home_num'];
		$mobile_num = $client['mobile_num'];
		$fax_num = $client['fax_num'];
		$street = $client['street'];
		$city = $client['city'];
		$state = $client['state'];
		$country = $client['country'];
		$zip = $client['zip'];
		
		$xml  = '<?xml version="1.0" encoding="utf-8"?>';
		$xml .= '<request method="client.create">';
		$xml .= "<client>";
		$xml .= "<first_name>{$fname}</first_name>";
		$xml .= "<last_name>{$lname}</last_name>";
		$xml .= "<organization>{$company}</organization>";
		$xml .= "<email>{$email}</email>";
		$xml .= "<work_phone>{$work_num}</work_phone>";
		$xml .= "<home_phone>{$home_num}</home_phone>";
		$xml .= "<mobile>{$mobile_num}</mobile>";
		$xml .= "<fax>{$fax_num}</fax>";
		$xml .= "<p_street1>{$street}</p_street1>";
		$xml .= "<p_street2></p_street2>";
		$xml .= "<p_city>{$city}</p_city>";
		$xml .= "<p_state>{$state}</p_state>";
		$xml .= "<p_country>{$country}</p_country>";
		$xml .= "<p_code>{$zip}</p_code>";
		$xml .= '</client>';
		$xml .= '</request>';
		
		return $this->_fb_api_request($xml); 
	}
	
	private function _fb_api_request($xml=NULL)
	{
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
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,10);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$result = curl_exec($ch);
		$info = curl_getinfo($ch);
		curl_close($ch);
		
		$http_code = $info['http_code'];
		if ($info['http_code'] == 503) {
			throw new Exception('503');
		}elseif($info['http_code'] != 200){
			throw new Exception('Error connecting to FreshBooks.  Please check your settings and try again');
		}
		return simplexml_load_string($result);
	}
	
	////
	//OAuth functions
	////
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
		$signature = $this->oauth_consumer_secret.'&';
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
			throw new Exception('Error connecting to FreshBooks. '.$result);
		}else{
			parse_str($result, $oauth_request_results);
			return $oauth_request_results;
		}
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
		$signature = $this->oauth_consumer_secret.'&';
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
		
		throw new Exception('URL: '.$url.' Post Fields: '.$post_fields.' Results: '.$result);//TODO: Debug purposes only - REMOVE 
		
		$http_code = $info['http_code'];
		if ($info['http_code'] != 200) {
			throw new Exception('HTTP Code: '.$http_code.'.  Error connecting to FreshBooks.  Please make sure that you Enabled API in your FreshBooks settings.');
		}else{
			parse_str($result, $oauth_request_results);
			return $oauth_request_results;
		}
	}
	

}