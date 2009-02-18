<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Handles all API requests for Highrise to FreshBooks Sync.
 *
 * @package Highrise_to_freshbooks
 * @author Kyle Hendricks kyleh@mendtechnologies.com
 **/

class Highrise_to_freshbooks{
	
	/**
	 * FreshBooks API URL.
	 *
	 * @var string
	 **/
	private $fb_url;
	
	/**
	 * FreshBooks API token.
	 *
	 * @var string
	 **/
	private $fb_token;
	
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
		$this->fb_url = $params['fburl'];
		$this->fb_token = $params['fbtoken'];
		$this->hr_url = $params['hrurl'];
		$this->hr_token = $params['hrtoken'];
	}
	
	/**
	 * Sends requests to Highrise API.
	 *
	 * @param $url string
	 * @return string/object	string containing error desc on error, xmlobject on success 
	 **/
	private function highrise_api_request($url)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_USERPWD, $this->hr_token);
		curl_setopt($ch, CURLOPT_HTTPGET, true);
		curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,10);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		$result = curl_exec($ch);
		curl_close($ch);
		
		if($result == FALSE){//unable to establish connection returns bool false
			return 'Error: Unable to connect to Highrise API. Please check your Highrise API URL setting and try again.';
		}elseif(preg_match("/denied/", $result)){
			return "Error: <strong>".$result."</strong> Please check your Highrise API Token setting and try again.";
		}else{
			return simplexml_load_string($result);
		}
		
	}
	
	/**
	 * Sends XML requests to FreshBooks API.
	 *
	 * @param $xml string
	 * @return string/object	string containing error desc on error, xmlobject on success 
	 **/
	private function freshbooks_api_request($xml)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->fb_url);
		curl_setopt($ch, CURLOPT_USERPWD, $this->fb_token);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,10);
		curl_setopt($ch,CURLOPT_USERAGENT,'FreshBooks + Highrise sync application');
		
		$result = curl_exec($ch);
		curl_close ($ch);
		
		//check for non xml result
		if($result == FALSE){
			return 'Error: Unable to connect to FreshBooks API.';
		}elseif(preg_match("/404 Error: Not Found/", $result) || preg_match("/DOCTYPE/", $result)){
			return "Error: <strong>404 Error: Not Found</strong>. Please check you FreshBooks API URL setting and try again.  The FreshBooks API url is different from your FreshBooks account url.";
		}
		
		//if xml check for FB status
		if(preg_match("/<?xml/", $result)){
			$fbxml = simplexml_load_string($result);
			if ($fbxml->attributes()->status == 'fail') {
				return 'Error: The following FreshBooks error occurred: '.$fbxml->error;
			}else{
				return $fbxml;
			}
		}
	}
	
	public function validate_highrise_settings()
	{
		$url = $this->hr_url.'/groups.xml';
		return $this->highrise_api_request($url);
	}
	
	public function get_highrise_tags()
	{
		$url = $this->hr_url.'/tags.xml';
		return $this->highrise_api_request($url);
	}
	
	public function get_highrise_clients($tag_id)
	{
		if ($tag_id == 'nofilter') {
			$url = $this->hr_url."/people.xml";
		}else{
			$url = $this->hr_url."/people.xml?tag_id=".$tag_id;
		}
		return $this->highrise_api_request($url);
	}
	
	private function get_highrise_company($companyid)
	{
		$url = $this->hr_url."/companies/".$companyid.".xml";
		return $this->highrise_api_request($url);
	}
	
	public function validate_freshbooks_settings()
	{
		$xml =<<<EOL
		<?xml version="1.0" encoding="utf-8"?>
		<request method="client.list">
		  <page>1</page>
		  <per_page>1</per_page>
		</request>
EOL;
		
		return $this->freshbooks_api_request($xml);
	}
	
	public function get_freshbooks_clients($page=1)
	{
		$xml =<<<EOL
		<?xml version="1.0" encoding="utf-8"?>
		<request method="client.list">
		  <page>{$page}</page>
		  <per_page>100</per_page>
		</request>
EOL;

		return $this->freshbooks_api_request($xml); 
	}
	
 	function sync_clients($hr_clients, $fb_emails)
 	{

 		$result_set = array();
 		//$result = '';
 		//$num = 1;
 		$states = array('AL'=>'Alabama', 'AK'=>'Alaska', 'AZ'=>'Arizona', 'AR'=>'Arkansas', 'AF'=>'Armed Forces Africa', 'AA'=>'Armed Forces Americas', 'AC'=>'Armed Forces Canada', 'AE'=>'Armed Forces Europe', 'AM'=>'Armed Forces Middle East', 'AP'=>'Armed Forces Pacific', 'CA'=>'California', 'CO'=>'Colorado', 'CT'=>'Connecticut', 'DE'=>'Delaware', 'DC'=>'District of Columbia', 'FL'=>'Florida', 'GA'=>'Georgia', 'GU'=>'Guam', 'ID'=>'Idaho', 'IL'=>'Illinois', 'IN'=>'Indiana', 'IA'=>'Iowa', 'KY'=>'Kansas', 'KS'=>'Kentucky', 'LA'=>'Louisiana', 'ME'=>'Maine', 'MD'=>'Maryland', 'MA'=>'Massachusetts', 'MI'=>'Michigan', 'MN'=>'Minnesota', 'MS'=>'Mississippi', 'MO'=>'Missouri', 'MT'=>'Montana', 'NE'=>'Nebraska', 'NV'=>'Nevada', 'NH'=>'New Hampshire', 'NJ'=>'New Jersey', 'NM'=>'New Mexico', 'NY'=>'New York', 'NC'=>'North Carolina', 'ND'=>'North Dakota', 'OH'=>'Ohio', 'OK'=>'Oklahoma', 'OR'=>'Oregon', 'PA'=>'Pennsylvania', 'PR'=>'Puerto Rico', 'RI'=>'Rhode Island', 'SC'=>'South Carolina', 'SD'=>'South Dakota', 'TN'=>'Tennessee', 'TX'=>'Texas', 'UT'=>'Utah', 'VT'=>'Vermont', 'VA'=>'Virginia', 'VI'=>'Virgin Islands', 'WA'=>'Washington', 'WV'=>'West Virginia', 'WI'=>'Wisconsin', 'WY'=>'Wyoming');

 		//compares email address from Highrise and Freshbooks to determine if client is already
 		//in Freshbooks
 		foreach($hr_clients->person as $hr_client){
 			//if empty add email address to HR contact data to satisify Freshbooks requirements
 			if($hr_client->{'contact-data'}->{'email-addresses'}->{'email-address'}->address == ''){
 				
				$fname = (string)$hr_client->{'first-name'};
 				$lname = (string)$hr_client->{'last-name'};
 				$name = $fname.' '.$lname;

 				// get Highrise company data
 				$hr_company = $this->get_highrise_company($hr_client->{'company-id'});
 				if (preg_match("/Error/", $hr_company)) {
 					return $hr_company;
 				}

 				if(!$hr_company->name == ''){
 					$company = (string)$hr_company->name;
 				}else{
 					$company = 'Not Available';
 				}

 				$result_set[] = array('Status' => 'Fail',
 												      'Company' => $company, 
 												      'Name' => $name,
															'Message' => 'Email Address Required');

 				//$hr_client->{'contact-data'}->{'email-addresses'}->{'email-address'}->address = $hr_client->{'first-name'}.$hr_client->{'last-name'}."@TEMP-EMAIL-ADDRESS.com";
 			}
 			//convert highrise email object to string for comparison
 			$hr_email = (string)$hr_client->{'contact-data'}->{'email-addresses'}->{'email-address'}->address;

 			//All HR clients start as potential new FB client - compare array of fb email addresses to hr email address - if match not new client
 			$possible_client = "new";
 			foreach ($fb_emails as $fb_email) {
 				if ($fb_email == $hr_email || $hr_email == '') {
 					$possible_client = "not new";
 				}
 			}

 			//if client not in Freshbooks then set client vars and add to Freshbooks and record result
 			//else compare next client
 			if($possible_client == 'new'){
 				//set client name
 				if (!$hr_client->{'first-name'} == '') {
 					$fname = (string)$hr_client->{'first-name'};
 				}else{
 					$fname = 'Not Available';
 				}

 				if (!$hr_client->{'last-name'} == '') {
 					$lname = (string)$hr_client->{'last-name'};
 				}else{
 					$lname = 'Not Available';
 				}

 				// get Highrise company data
 				$hr_company = $this->get_highrise_company($hr_client->{'company-id'});
 				if (preg_match("/Error/", $hr_company)) {
 					return $hr_company;
 				}

 				if(!$hr_company->name == ''){
 					$company = (string)$hr_company->name;
 				}else{
 					$company = 'Not Available';
 				}

 				$email = (string)$hr_email;
 				//set all existing phone numbers
 				//initialize #'s to blank
 				$work_num = '';
 				$mobile_num = '';
 				$fax_num = '';
 				$home_num = '';
 				foreach($hr_client->{'contact-data'}->{'phone-numbers'}->{'phone-number'} as $phonenum){
 					switch ($phonenum->{'location'}) {
 						case 'Work':
 							$work_num = (string)$phonenum->{'number'};
 							break;
 						case 'Mobile':
 							$mobile_num = (string)$phonenum->{'number'};
 							break;
 						case 'Fax':
 							$fax_num = (string)$phonenum->{'number'};
 							break;
 						case 'Home':
 							$home_num = (string)$phonenum->{'number'};
 							break;
 					}
 				}
 				//set address information
 				$street = (string)$hr_client->{'contact-data'}->{'addresses'}->address->street;
 				$city = (string)$hr_client->{'contact-data'}->{'addresses'}->address->city;
 				//state abbreviation to full spelling conversion for FB compatability
 				$state_raw = (string)$hr_client->{'contact-data'}->{'addresses'}->address->state;
 					$state_raw = trim($state_raw);
 					$state_length = strlen($state_raw);
 					$state_abr = strtoupper($state_raw); 
 					if ($state_length  == 2 & array_key_exists($state_abr, $states)) {
 							$state = $states[$state_abr];
 					}else{
 						$state = $state_raw;
 					}
 				//$state = (string)$hr_client->{'contact-data'}->{'addresses'}->address->state;
 				$country = (string)$hr_client->{'contact-data'}->{'addresses'}->address->country;
 				$zip = (string)$hr_client->{'contact-data'}->{'addresses'}->address->zip;

 			//build xml to send to Freshbooks
 			$xml =<<<EOL
 			<?xml version="1.0" encoding="utf-8"?>
 			<request method="client.create">
 			  <client>
 			    <first_name>{$fname}</first_name>
 			    <last_name>{$lname}</last_name>
 			    <organization>{$company}</organization>
 			    <email>{$email}</email>
 			    <username></username>
 			    <password></password>
 			    <work_phone>{$work_num}</work_phone>
 			    <home_phone>{$home_num}</home_phone>
 			    <mobile>{$mobile_num}</mobile>
 			    <fax>{$fax_num}</fax>
 			    <notes></notes>

 			    <p_street1>{$street}</p_street1>
 			    <p_street2></p_street2>
 			    <p_city>{$city}</p_city>
 			    <p_state>{$state}</p_state>
 			    <p_country>{$country}</p_country>
 			    <p_code>{$zip}</p_code>

 			    <s_street1></s_street1>
 			    <s_street2></s_street2>
 			    <s_city></s_city>
 			    <s_state></s_state>
 			    <s_country></s_country>
 			    <s_code></s_code>
 			  </client>
 			</request>
EOL;

 				//send new client xml to freshbooks
 				$result = $this->freshbooks_api_request($xml);
 				if (preg_match("/Error/", $result)) {
 					return $result;
 				}

 				if($result->client_id){
 					$name = $fname.' '.$lname;
 				 	//$client = array('Result Number' => $result_num,'Status' => 'Success', 'Company' => $company, 'Name' => $name);
 					$result_set[] = array('Status' => 'Success', 'Company' => $company, 'Name' => $name, 'Message' => 'Client Synced Successfully');
 					//unset($client);
 				} elseif ($result->error){
 					$name = $fname.' '.$lname;
 				 	$message = (string)$result->error;
 				 	//$client = array('Status' => $status,'Company' => $company, 'Name' => $name);
 					$result_set[] = array('Status' => 'Fail', 'Company' => $company, 'Name' => $name, 'Message' => $message);
 					//unset($client);
 				}
 			}	
 		}//endforeach
 		return $result_set;
 	}
	
}
