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
	
	public function validate_hr_settings()
	{
		$url = $this->hr_url.'/groups.xml';
		$request = $this->_highrise_api_request($url);
		return $request ? TRUE : FALSE;
	}
	
	public function get_hr_tags()
	{
		$url = $this->hr_url.'/tags.xml';
		return $this->_highrise_api_request($url);
	}
	
	public function get_hr_clients($tag_id)
	{
		if ($tag_id == 'nofilter') {
			$url = $this->hr_url."/people.xml";
		}else{
			$url = $this->hr_url."/people.xml?tag_id=".$tag_id;
		}
		return $this->_highrise_api_request($url);
	}
	
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
		//$info = curl_getinfo($ch);
		$result = curl_exec($ch);
		curl_close($ch);
		
		if($result == FALSE){//unable to establish connection returns bool false
			throw new Exception('Error: Unable to connect to Highrise API. Please check your Highrise API URL setting and try again.');
			//return 'Error: Unable to connect to Highrise API. Please check your Highrise API URL setting and try again.';
		}elseif(preg_match("/denied/", $result)){
			throw new Exception('Error: <strong>'.$result.'</strong> Please check your Highrise API Token setting and try again.');
			//return "Error: <strong>".$result."</strong> Please check your Highrise API Token setting and try again.";
		}elseif(preg_match("/<?xml/", $result)){
			return simplexml_load_string($result);
		}else{
			throw new Exception('Unable to connect to the API. Please try the request again.');
		}
	}
	
}