<?php
Class Synctest extends Controller
{
	function __construct()
	{
		parent::Controller();
		$this->load->helper(array('form', 'url', 'html'));
		$this->load->library('Highrise_to_freshbooks');
		$this->output->enable_profiler(TRUE);
	}
	
	function index()
	{
		$loggedin = $this->session->userdata('loggedin');
		if (!$loggedin) {
		redirect('user/index');
		$data['navigation'] = False;
		}else{
		$data['navigation'] = True;	
		}
		$data['cssfile'] = base_url() . 'public/stylesheets/default.css';
		$data['title']   = 'Highrise to Freshbooks Sync Tool :: Sync Contacts';
		$data['heading'] = 'Sync HighRise Contacts';
	
		//get API settings
		$this->load->model('Settings_model', 'settings');
		$apisettings = $this->settings->get_settings();
		if (!$apisettings) {
			//if no API settings redirect to settings page
			redirect('settings/index');
		}
		
		//get Highrise Tags
		$get_hr_tags = new FreshbooksSync();
		$raw_xml = $get_hr_tags->loadxml($apisettings[0]->hrurl.'/tags.xml',$apisettings[0]->hrtoken);
		$data['xml'] = $raw_xml;
		
		
		
		$this->load->view('sync_view_test', $data);
	}
	
	function sync_contacts()
	{
		$loggedin = $this->session->userdata('loggedin');
		if (!$loggedin) {
		redirect('user/index');
		$data['navigation'] = False;
		}else{
		$data['navigation'] = True;	
		}
		$data['cssfile'] = base_url() . 'public/stylesheets/default.css';
		$data['title']   = 'Highrise to Freshbooks Sync Tool :: Sync Contacts Results';
		$data['heading'] = 'Sync HighRise Contacts Results';
		$result = '';
		$result_set = array();
		$clients_to_sync = array();
		$num = 1;
		
		// get API settings
		$this->load->model('Settings_model', 'settings');
		$apisettings = $this->settings->get_settings();
		if (!$apisettings) {
			//if no API settings redirect to settings page
			redirect('settings/index');
		}
		
		//get highrise clients
		$highrise_object = new FreshbooksSync();
		// set highrise url based on tag selection
		if ($_POST['tagfilter'] == 'nofilter') {
			$hrurl = $apisettings[0]->hrurl."/people.xml";
		}else{
			$tag_id = $_POST['tagfilter'];
			$hrurl = $apisettings[0]->hrurl."/people.xml?tag_id=".$tag_id;
		}
		$hrtoken = $apisettings[0]->hrtoken;
		$highrise_clients = $highrise_object->loadxml($hrurl, $hrtoken);
		//$data['hrclients'] = $highrise_clients;
		
		//get freshbooks clients
		$this->benchmark->mark('getFbClients_start');
		$freshbooks_object = new FreshbooksSync();
		$fburl = $apisettings[0]->fburl;
		$fbtoken = $apisettings[0]->fbtoken;

		//XML for Freshbooks request
		$xmlItem =<<<EOL
		<?xml version="1.0" encoding="utf-8"?>
		<request method="client.list">
		  <page>1</page>
		  <per_page>250</per_page>
		</request>
EOL;
		
		$freshbooks_clients = $freshbooks_object->sendXMLRequest($fburl, $fbtoken, $xmlItem);
		$this->benchmark->mark('getFbClients_end');
		//$data['fbclients'] = $freshbooks_clients;
		
		$this->benchmark->mark('AddToFbArray_start');
		
		foreach($highrise_clients->person as $hr_client){
			//if empty add email address to HR contact data to satisify Freshbooks requirements
			if($hr_client->{'contact-data'}->{'email-addresses'}->{'email-address'}->address == ''){
				$hr_client->{'contact-data'}->{'email-addresses'}->{'email-address'}->address = $hr_client->{'first-name'}.$hr_client->{'last-name'}."@TEMP-EMAIL-ADDRESS.com";
			}
			//convert highrise email object to string for comparison
			$hr_email = (string)$hr_client->{'contact-data'}->{'email-addresses'}->{'email-address'}->address;
			
			$possible_client = "new";
			foreach ($freshbooks_clients->clients->client as $fb_client) {
				$fb_email = (string)$fb_client->email;
				if ($fb_email == $hr_email) {
					$possible_client = "not new";
				}
			}
		
			if($possible_client == 'new'){
				$client = array('fname' => (string)$hr_client->{'first-name'}, 'lname' => (string)$hr_client->{'last-name'});
				
				// get company data
				$company_url = $apisettings[0]->hrurl."/companies/".$hr_client->{'company-id'}.".xml";
				$hr_company = $highrise_object->loadxml($company_url, $hrtoken);
				
				$client['company'] = (string)$hr_company->name;
				$client['email'] = (string)$hr_email;
				$client['work_num'] = '';
				$client['mobile_num'] = '';
				$client['fax_num'] = '';
				$client['home_num'] = '';
				//$company = $hr_company->name;
				//$email = $hr_email;

				//set all existing phone numbers
				//initialize #'s to blank
				$work_num = '';
				$mobile_num = '';
				$fax_num = '';
				$home_num = '';
				foreach($hr_client->{'contact-data'}->{'phone-numbers'}->{'phone-number'} as $phonenum){
					switch ($phonenum->{'location'}) {
						case 'Work':
							$client['work_num'] = (string)$phonenum->{'number'};
							break;
						case 'Mobile':
							$client['mobile_num'] = (string)$phonenum->{'number'};
							break;
						case 'Fax':
							$client['fax_num'] = (string)$phonenum->{'number'};
							break;
						case 'Home':
							$client['home_num'] = (string)$phonenum->{'number'};
							break;
					}
				}
				$client['street'] = (string)$hr_client->{'contact-data'}->{'addresses'}->address->street;
				$client['city'] = (string)$hr_client->{'contact-data'}->{'addresses'}->address->city;
				$client['state'] = (string)$hr_client->{'contact-data'}->{'addresses'}->address->state;
				$client['country'] = (string)$hr_client->{'contact-data'}->{'addresses'}->address->country;
				$client['zip'] = (string)$hr_client->{'contact-data'}->{'addresses'}->address->zip;
			
			$clients_to_sync[] = $client;
			unset($client);
			}
	
		}//end foreach
		
		$this->benchmark->mark('AddToFbArray_end');
		//$data['result'] = $result_set;
		$data['clients'] = $clients_to_sync;
		$this->load->view('sync_results_view_test', $data);
	}

}
?>