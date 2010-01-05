<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
*Sync Controller
*Controller to display sync view with highrise tags and handle sync requests
*Created by Kyle Hendricks - Mend Technologies - kyleh@mendtechnologies.com
*Ver. 1.0 October 2009
*
*Copyright (c) 2009, Kyle Hendricks - Mend Technologies
*All rights reserved.
*Redistribution and use in source and binary forms, with or without
*modification, are permitted provided that the following conditions are met:
** Redistributions of source code must retain the above copyright notice,
*this list of conditions and the following disclaimer.
** Redistributions in binary form must reproduce the above copyright
*notice, this list of conditions and the following disclaimer in the
*documentation and/or other materials provided with the distribution.
** Neither the name of the <ORGANIZATION> nor the names of its
*contributors may be used to endorse or promote products derived from this
*software without specific prior written permission.
*THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
*ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
*WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
*DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
*ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
*(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
*LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
*ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
*(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
*SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
**/

Class Sync extends Controller
{
	function __construct()
	{
		parent::Controller();
		$this->load->helper(array('form', 'url', 'html'));
		//Debug View True=on False=off
		$this->output->enable_profiler(FALSE);
	}
	
	/**
	 * Public index() method of Sync Controller
	 *
	 * Gets Highrise tags and sets up/displays sync page
	 * 
	*/
	public function index()
	{
		if ($this->_check_login())
		{
			$data['navigation'] = TRUE;
			$settings = $this->_get_settings();
			$validate = $this->_validate_fb_settings($settings);
			if ($validate != 'valid') {return;}
			$validate = $this->_validate_hr_settings($settings);
			if ($validate != 'valid') {return;}
		}
		
		$data['title'] = 'Highrise to Freshbooks Sync Tool :: Sync Contacts';
		
		//highrise tags to use for syncing - to add tags simply add value to array
		$tags = array('client');
		$settings = $this->_get_settings();
		$this->load->library('Highrise', $settings);
		
		//store hr tags in array with tag name as index
		//check hr tags for tags used in sync
		//compile an array of sync tags with tag name as index
		//and highrise tag id if they exist 
		try {
			$hr_tags = $this->highrise->get_hr_tags();
			$hr_tag_array = array();
			foreach ($hr_tags->tag as $hrt) {
				$index = (string)$hrt->id;
				$hr_tag_array[$index] = (string)$hrt->name;
			}
			$sync_tags = array();
			foreach ($tags as $tag) {
				$id = array_search($tag, $hr_tag_array);
				$sync_tags[$tag] = $id;
			}
		} catch (Exception $e) {
			$data['error'] = $e->getMessage();
		}
			
		$data['hr_tags'] = $sync_tags;
		$this->load->view('sync/sync_view', $data);
	}

	/**
	 * Public sync_contacts() method of Sync Controller
	 *
	 * Syncs Highrise contacts to FrehBooks
	 * 
	*/
	public function sync_contacts()
	{
		if ($this->_check_login())
		{
			$data['navigation'] = TRUE;	
		}
		
		$data['title']   = 'Highrise to Freshbooks Sync Tool :: Sync Contacts Results';
		$data['heading'] = 'Sync HighRise Contacts Results';
		
		//get all hr contacts with selected tag filter
		$settings = $this->_get_settings();
		$this->load->library('Highrise', $settings);
		$tag_id = $this->input->post('tagfilter');
		//if tag is in use by HR get hr_clients else send message that tag is not in use
		if ($tag_id) {
			try {
				$get_hr_clients = $this->highrise->get_hr_clients($tag_id);
				//process into multidim assoc array with email as key
				$hr_contacts = array();
				foreach ($get_hr_clients->person as $hr_client) {
					$hr_email = htmlspecialchars(trim((string)$hr_client->{'contact-data'}->{'email-addresses'}->{'email-address'}->address));
					$fname = ($hr_client->{'first-name'}) ? htmlspecialchars(trim((string)$hr_client->{'first-name'})) : 'Not Available';
					$lname = ($hr_client->{'last-name'}) ? htmlspecialchars(trim((string)$hr_client->{'last-name'})) : 'Not Available';
					$company_id = ($hr_client->{'company-id'}) ? (int)$hr_client->{'company-id'} : '';
					//phone numbers
					$work_num = '';
					$mobile_num = '';
					$fax_num = '';
					$home_num = '';
					foreach($hr_client->{'contact-data'}->{'phone-numbers'}->{'phone-number'} as $phonenum){
	 					switch ($phonenum->{'location'}) {
	 						case 'Work':
	 							$work_num = htmlspecialchars(trim((string)$phonenum->{'number'}));
	 							break;
	 						case 'Mobile':
	 							$mobile_num = htmlspecialchars(trim((string)$phonenum->{'number'}));
	 							break;
	 						case 'Fax':
	 							$fax_num = htmlspecialchars(trim((string)$phonenum->{'number'}));
	 							break;
	 						case 'Home':
	 							$home_num = htmlspecialchars(trim((string)$phonenum->{'number'}));
	 							break;
	 					}
	 				}
	 				//address info
					$street = htmlspecialchars(trim((string)$hr_client->{'contact-data'}->{'addresses'}->address->street));
	 				$city = htmlspecialchars(trim((string)$hr_client->{'contact-data'}->{'addresses'}->address->city));
	 				//state abbreviation to full spelling conversion for FB compatability
					$states = array(
						'AL'=>'Alabama', 'AK'=>'Alaska', 'AZ'=>'Arizona', 'AR'=>'Arkansas', 'AF'=>'Armed Forces Africa', 'AA'=>'Armed Forces Americas', 'AC'=>'Armed Forces Canada', 'AE'=>'Armed Forces Europe', 'AM'=>'Armed Forces Middle East', 'AP'=>'Armed Forces Pacific', 'CA'=>'California', 'CO'=>'Colorado', 'CT'=>'Connecticut', 'DE'=>'Delaware', 'DC'=>'District of Columbia', 'FL'=>'Florida', 'GA'=>'Georgia', 'GU'=>'Guam', 'ID'=>'Idaho', 'IL'=>'Illinois', 'IN'=>'Indiana', 'IA'=>'Iowa', 'KY'=>'Kansas', 'KS'=>'Kentucky', 'LA'=>'Louisiana', 'ME'=>'Maine', 'MD'=>'Maryland', 'MA'=>'Massachusetts', 'MI'=>'Michigan', 'MN'=>'Minnesota', 'MS'=>'Mississippi', 'MO'=>'Missouri', 'MT'=>'Montana', 'NE'=>'Nebraska', 'NV'=>'Nevada', 'NH'=>'New Hampshire', 'NJ'=>'New Jersey', 'NM'=>'New Mexico', 'NY'=>'New York', 'NC'=>'North Carolina', 'ND'=>'North Dakota', 'OH'=>'Ohio', 'OK'=>'Oklahoma', 'OR'=>'Oregon', 'PA'=>'Pennsylvania', 'PR'=>'Puerto Rico', 'RI'=>'Rhode Island', 'SC'=>'South Carolina', 'SD'=>'South Dakota', 'TN'=>'Tennessee', 'TX'=>'Texas', 'UT'=>'Utah', 'VT'=>'Vermont', 'VA'=>'Virginia', 'VI'=>'Virgin Islands', 'WA'=>'Washington', 'WV'=>'West Virginia', 'WI'=>'Wisconsin', 'WY'=>'Wyoming'
						);
	 				$state_raw = trim((string)$hr_client->{'contact-data'}->{'addresses'}->address->state);
	 					$state_raw = trim($state_raw);
	 					$state_length = strlen($state_raw);
	 					$state_abr = strtoupper($state_raw); 
	 					if ($state_length  == 2 && array_key_exists($state_abr, $states)) {
	 							$state = $states[$state_abr];
	 					}else{
	 						$state = $state_raw;
	 					}
	 				$country = htmlspecialchars(trim((string)$hr_client->{'contact-data'}->{'addresses'}->address->country));
	 				$zip = htmlspecialchars(trim((string)$hr_client->{'contact-data'}->{'addresses'}->address->zip));
	 			
					//build array
					$hr_contacts[$hr_email] = array('first_name' => $fname, 'last_name' => $lname, 'company' => '', 'email' => $hr_email, 'company_id' => $company_id, 'work_num' => $work_num, 'mobile_num' => $mobile_num, 'fax_num' => $fax_num, 'home_num' => $home_num, 'street' => $street, 'city' => $city, 'state' => $state, 'country' => $country, 'zip' => $zip);
				}
				
			} catch (Exception $e) {
				$data['error'] = $e->getMessage();
				$this->load->view('sync/sync_results_view', $data);
				return;
			}
		}else{
			//return to sync page with message - no contacts with selected tag
			$data['error'] = 'No contacts with selected tag in Highrise.';
			$this->load->view('sync/sync_results_view', $data);
			return;
		}
		
		//get FB client data and process into multidim assoc array with email as key
		try {
			$this->load->library('FreshbooksOauth', $settings);
			//get first page of FB clients
			$fb_clients = $this->freshbooksoauth->get_fb_clients();
			//process results into md array for comparison by key with hr client arrray
			$fb_contacts = array();
			foreach ($fb_clients->clients->client as $client) {
				$email = htmlspecialchars(trim((string)$client->email));
				$fb_contacts[$email] = $email;
			}
			//set pages var based on actual pages returned by FB
			$fb_pages = (integer)$fb_clients->clients->attributes()->pages;
			//process additional FB pages if they exist
			while ($fb_pages > 1) {
					$fb_clients = $this->freshbooksoauth->get_fb_clients($fb_pages);
			  //no errors on client api request extract email into array
				foreach ($fb_clients->clients->client as $client) {
					$email = htmlspecialchars(trim((string)$client->email));
					$fb_contacts[$email] = $email;
				}
				$fb_pages--;
			}//end while
		} catch (Exception $e) {
			$data['error'] = $e->getMessage();
			$this->load->view('sync/sync_results_view', $data);
			return;
		}
		
		//compare arrays and remove any clients from HR array that have same key(email) as FB array
		$clients_to_sync = array_diff_key($hr_contacts, $fb_contacts);
		//sync clients into Freshbooks
		$sync_results = array();
		foreach ($clients_to_sync as $client) {
			try {
				if ($client['company_id']) {
					$hr_company = $this->highrise->get_hr_company($client['company_id']);
					$company_name = htmlspecialchars(trim((string)$hr_company->name));
					$client['company'] = $company_name;
				}
				$add_client = $this->freshbooksoauth->add_fb_client($client);
					if ($add_client->error) {
						$client['message'] = 'Error: '.(string)$add_client->error;
					}else{
						$client['message'] = 'Success';
					}
				$sync_results[] = $client;
			} catch (Exception $e) {
				$client['message'] = $e->getMessage;
				$sync_results[] = $client;
			}
		}
		
		$data['sync_results']= $sync_results;
		$data['fburl'] = $settings['fb_url'];
		$this->load->view('sync/sync_results_view', $data);
	}
	
	/**
	 * Gets API settings from database.
	 *
	 * @return array Array of API settings on success, redirect to settings page on fail
	 **/
	private function _get_settings()
	{
		$this->load->model('Settings_model','settings');
		$api_settings = $this->settings->get_settings();
		$fb_url = 'https://'.$this->session->userdata('subdomain').'.freshbooks.com';
				
		if ($api_settings) {
			
			$fb_settings = ($api_settings->fb_oauth_token_secret == '' || $api_settings->fb_oauth_token == '') ? FALSE : TRUE;
			$hr_settings = ($api_settings->hrurl == '' || $api_settings->hrtoken == '') ? FALSE : TRUE;
			if ($hr_settings) {
				$hr_url_prefix = ($this->session->userdata('hrssl') == 'yes') ? 'https://' : 'http://';
				$hr_url = $hr_url_prefix.$api_settings->hrurl.'.highrisehq.com';
			}else{
				$hr_url = '';
			}
			
			return array(
				'fb_settings' => $fb_settings,
				'hr_settings' => $hr_settings,
				'fb_url' => $fb_url,
				'consumer_key' => $this->config->item('consumer_key'),
				'consumer_secret' => $this->config->item('consumer_secret'),
				'callback_url' => $this->config->item('callback_url'),
				'fb_oauth_token_secret' => $api_settings->fb_oauth_token_secret,
				'fb_oauth_token' => $api_settings->fb_oauth_token,
				'hrurl' => $hr_url,
				'hrtoken' => $api_settings->hrtoken,
				);
		}else{
			return array(
				'fb_settings' => FALSE,
				'hr_settings' => FALSE,
				'fb_url' => $fb_url,
				'consumer_key' => $this->config->item('consumer_key'),
				'consumer_secret' => $this->config->item('consumer_secret'),
				'callback_url' => $this->config->item('callback_url'),
				'fb_oauth_token_secret' => '',
				'fb_oauth_token' => '',
				'hrurl' => '',
				'hrtoken' => '',
				);
		}
	}

	/**
	 * Checks user login status.
	 *
	 * @return bool	True on success, False and redirect to login on fail
	 **/
	private function _check_login()
	{
		$loggedin = $this->session->userdata('loggedin');
		if ( ! $loggedin)
		{
			redirect('user/index');
			return FALSE;
		}
		else
		{
			return TRUE;
		}
	}
	
	/**
	 * Private _validate_hr_settings() method of Sync Controller
	 *
	 * Checks for Highrise settings in database.  If present then it tests that the
	 * settings are valid by using the Highrise API.
	 *
	 * @return bool true  returns true on success and redirects to Highrise settings page on false
	 *
	*/
	private function _validate_hr_settings($settings)
	{
		$this->load->library('Highrise', $settings);
		try {
			$validate_hr_settings = $this->highrise->validate_hr_settings();
			if ($validate_hr_settings == 'switchssl') {
				//Flip the ssl switch
				$hr_ssl_status = ($this->session->userdata('hrssl') == 'yes') ? 'yes' : 'no';
				$this->session->unset_userdata('hrssl');
				$this->session->set_userdata('hrssl', $hr_ssl_status);
			}
			return 'valid';
		} catch (Exception $e) {
			//if hr settings fail to validate return with message
			$data['title']   = 'Highrise to Freshbooks Sync Tool :: API Settings';
			$raw_domain = $settings['hrurl'];
			$data['hrurl'] = preg_replace('%http[a-z]*://|\.[a-zA-Z0-9]*\.com%', '', $raw_domain);
			$data['hrtoken'] = $settings['hrtoken'];
			$data['submitname'] = 'Update API Settings';
			$data['error'] = $e->getMessage();
			$this->load->view('settings/settings_view', $data); 
			return;
		}
	}

	/**
	 * Private _validate_fb_settings() method of Sync Controller
	 *
	 * Checks for FreshBooks oauth settings in database.  If present then it tests that the
	 * settings are valid by using the FreshBooks API.
	 *
	 * @return bool true  returns true on success and redirects to FreshBooks settings page on false
	 *
	*/	
	private function _validate_fb_settings($settings)
	{
		$this->load->library('FreshbooksOauth', $settings);
		try {
			$validate_fb_settings = $this->freshbooksoauth->validate_fb_settings();
			return 'valid';
		} catch (Exception $e) {
			//if settings no longer valid and freshbooks is not down for maintenance then start oauth process 
			$error = $e->getMessage();
			//if down for maintenance redirect to oauth error page with message
			if ($error == 525) {
				$data['error'] = 'FreshBooks is currently down for maintenance, please try again later.';
				$data['navigation'] = TRUE;
				$data['title'] = 'Highrise to FreshBooks Sync :: Oauth Error';
				$this->load->view('settings/oauth_error_view', $data);
				return;
			}
			//start the oauth process
			redirect('settings/freshbooks_oauth');
			return;
		}
	}

}