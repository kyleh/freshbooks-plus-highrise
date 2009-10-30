<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Handles all API requests for Highrise.
 *
 * @package Highrise
 * @author Kyle Hendricks kyleh@mendtechnologies.com
 **/

class Highrise{

	/**
	 * Highrise URL
	 *
	 * @var string
	 **/
	private $hr_url;
	
	/**
	 * Highrise API token.
	 *
	 * @var string
	 **/
	private $hr_token;
	
	function __construct($params)
	{
		$this->hr_url = $params['hrurl'];
		$this->hr_token = $params['hrtoken'];
	}
	
	/**
	 * Public validate_hr_settings() method of Highrise library
	 *
	 * Sends request to Highrise for users
	 *
	 * @return bool true  returns true on success and redirects to FreshBooks settings page on false
	 *
	*/	
	public function validate_hr_settings()
	{
		$url = $this->hr_url.'/users.xml';
		$request = $this->_highrise_api_request($url);
		return $request;
	}
	
	/**
	 * Public get_hr_tags() method of Highrise library
	 *
	 * Gets Highrise Tags
	 *
	 * @return list of Highrise tags
	 *
	*/	
	public function get_hr_tags()
	{
		$url = $this->hr_url.'/tags.xml';
		return $this->_highrise_api_request($url);
	}
	
	/**
	 * Public get_hr_clients() method of Highrise library
	 *
	 * Gets Highrise Clients with optional tag filter
	 *
	 * @return list of Highrise clients
	 *
	*/	
	public function get_hr_clients($tag_id)
	{
		if ($tag_id == 'nofilter') {
			$url = $this->hr_url."/people.xml";
		}else{
			$url = $this->hr_url."/people.xml?tag_id=".$tag_id;
		}
		return $this->_highrise_api_request($url);
	}
	
	/**
	 * Public get_hr_company() method of Highrise library
	 *
	 * Gets Highrise company given a company id
	 *
	 * @return Highrise company data
	 *
	*/	
	public function get_hr_company($companyid)
	{
		$url = $this->hr_url."/companies/".$companyid.".xml";
		return $this->_highrise_api_request($url);
	}

	/**
	 * Sends requests to Highrise API.
	 *
	 * @param $url string
	 * @return string/object	string containing error desc on error, xmlobject on success 
	 **/
	private function _highrise_api_request($url)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_USERPWD, $this->hr_token);
		curl_setopt($ch, CURLOPT_HTTPGET, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,20);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		$info = curl_getinfo($ch);
		$result = curl_exec($ch);
		curl_close($ch);
		
		//check for wrong url subdomain or http/https
		if (preg_match("/redirected/", $result) && preg_match("/highrisehq.com\/login/", $result)) {
			throw new Exception('Error: Unable to connect to Highrise API. Please check your Highrise API URL setting and try again.');
		}elseif(preg_match("/redirected/", $result) && preg_match("/highrisehq.com\/users.xml/", $result)){
			return 'switchssl';
		}
		
		if($result == FALSE){//unable to establish connection returns bool false
			throw new Exception('Error: Unable to connect to Highrise API. Please check your Highrise API URL setting and try again.');
		}elseif(preg_match("/denied/", $result)){
			throw new Exception('Error: <strong>'.$result.'</strong> Please check your Highrise API Token setting and try again.');
		}elseif(preg_match("/<?xml/", $result)){
			return simplexml_load_string($result);
		}else{
			throw new Exception('Unable to connect to the API. Please try the request again.');
		}
	}
}