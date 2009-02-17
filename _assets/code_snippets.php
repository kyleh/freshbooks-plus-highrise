<?php

			if($possible_client == 'new'){
				$fname = $hr_client->{'first-name'};
				$lname = $hr_client->{'last-name'};
				
				// get company data
				$company_url = $apisettings[0]->hrurl."/companies/".$hr_client->{'company-id'}.".xml";
				$hr_company = $highrise_object->loadxml($company_url, $hrtoken);
				$company = $hr_company->name;
				$email = $hr_email;

				//set all existing phone numbers
				//initialize #'s to blank
				$work_num = '';
				$mobile_num = '';
				$fax_num = '';
				$home_num = '';
				foreach($hr_client->{'contact-data'}->{'phone-numbers'}->{'phone-number'} as $phonenum){
					switch ($phonenum->{'location'}) {
						case 'Work':
							$work_num = $phonenum->{'number'};
							break;
						case 'Mobile':
							$mobile_num = $phonenum->{'number'};
							break;
						case 'Fax':
							$fax_num = $phonenum->{'number'};
							break;
						case 'Home':
							$home_num = $phonenum->{'number'};
							break;
					}
				}
				$street = $hr_client->{'contact-data'}->{'addresses'}->address->street;
				$city = $hr_client->{'contact-data'}->{'addresses'}->address->city;
				$state = $hr_client->{'contact-data'}->{'addresses'}->address->state;
				$country = $hr_client->{'contact-data'}->{'addresses'}->address->country;
				$zip = $hr_client->{'contact-data'}->{'addresses'}->address->zip;

				//build xml to send to Freshbooks
				$xmlClient =<<<EOL
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
			$result = $freshbooks_object->sendXMLRequest($fburl, $fbtoken, $xmlClient);
			if($result->client_id){
				$result_num = $num;
			 	$status = 'OK';
			 	$message = 'COMPANY: '.$company.'    '.'NAME: '.$fname.' '.$lname;
			 	$client = array('Result Number' => $result_num,'Status' => $status,'Message' => $message);
				$result_set[] = $client;
				unset($client);
			} elseif ($result->error){
			 	$result_num = $num;
			 	$status = 'fail';
			 	$message = (string)$result->error;
			 	$client = array('Result Number' => $result_num,'Status' => $status,'Message' => $message);
				$result_set[] = $client;
				unset($client);
			}

			$num++;
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
			 			if($hr_client->{'contact-data'}->{'email-addresses'}->{'email-address'}->address == '')
			 			{
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

			 				$result_set[] = ('Status' => 'Fail: Email Address Required',
			 												 'Company' => $company, 
			 												 'Name' => $name);

			 				//$hr_client->{'contact-data'}->{'email-addresses'}->{'email-address'}->address = $hr_client->{'first-name'}.$hr_client->{'last-name'}."@TEMP-EMAIL-ADDRESS.com";
			 			}
			 			//convert highrise email object to string for comparison
			 			$hr_email = (string)$hr_client->{'contact-data'}->{'email-addresses'}->{'email-address'}->address;

			 			//All HR clients start as potential new FB client - compare array of fb email addresses to hr email address - if match not new client
			 			$possible_client = "new";
			 			foreach ($fb_emails as $fb_email) {
			 				if ($fb_email == $hr_email || $hr_email = '') {
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
			 				 	$status = 'Success';
			 					$name = $fname.' '.$lname;
			 				 	//$client = array('Result Number' => $result_num,'Status' => 'Success', 'Company' => $company, 'Name' => $name);
			 					$result_set[] = array('Status' => 'Success', 'Company' => $company, 'Name' => $name);
			 					//unset($client);
			 				} elseif ($result->error){
			 				 	$status = 'Fail: '.(string)$result->error;
			 					$name = $fname.' '.$lname;
			 				 	//$message = (string)$result->error;
			 				 	//$client = array('Status' => $status,'Company' => $company, 'Name' => $name);
			 					$result_set[] = array('Status' => $status,'Company' => $company, 'Name' => $name);
			 					//unset($client);
			 				}
			 			}	
			 		}//endforeach
			 		return $result_set;
			 	}
