<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
*Sync Controller
*Controller to display sync view with highrise tags and handle sync requests
*Created by Kyle Hendricks - Mend Technologies - kyleh@mendtechnologies.com
*Ver. 1.0 5/3/2009
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
		$params = $this->_get_settings();
		$this->load->library('Highrise_to_freshbooks', $params);
		//Debug View True=on False=off
		$this->output->enable_profiler(FALSE);
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
	 * Gets API settings from database.
	 *
	 * @return array Array of API settings on success, redirect to settings page on fail
	 **/
	private function _get_settings()
	{
		$this->load->model('Settings_model','settings');
		$settings = $this->settings->get_settings();
		if ( ! $settings)
		{
			redirect('settings/index');
		}
		else
		{
			return array(
							'fburl' => $settings->fburl,
							'fbtoken' => $settings->fbtoken,
							'hrurl' => $settings->hrurl,
							'hrtoken' => $settings->hrtoken,
							);
		}
	}

	/**
	 * Gets validate freshbooks and highrise api settings
	 *
	 **/
	private function _validate_api_settings()
	{
		//initialize error container array
		$error_data = array();
		//validate freshbooks settings
		try {
			$fb_settings_status = $this->highrise_to_freshbooks->validate_freshbooks_settings();
		} catch (Exception $e) {
			$error_data = array($e->getMessage());
		}
		//validate highrise settings
		try {
			$hr_settings_status = $this->highrise_to_freshbooks->validate_highrise_settings();
		} catch (Exception $e) {
			$error_data = array($e->getMessage());
		}
		
		if (!empty($error_data)) 
		{
			$this->load->library('session');
			$this->session->set_flashdata('error', $error_data);
			redirect('settings/index/invalid');
		}
		
		return;
	}
	
	/**
	 * Sorts multidimentional of results by status
	 *
	 **/
	private function _results_sort($x, $y)
	{
		return strcasecmp($x['Status'], $y['Status']);
	}
	
	//gets highrise tags and displays sync page
	public function index()
	{
		if ($this->_check_login())
		{
			$data['navigation'] = TRUE;
			$validate_api_settings = $this->_validate_api_settings();
		}
		
		$data['title'] = 'Highrise to Freshbooks Sync Tool :: Sync Contacts';
	
		//get Highrise Tags
		try {
			$hr_tags = $this->highrise_to_freshbooks->get_highrise_tags();
		} catch (Exception $e) {
			$data['error'] = $e->getMessage();
			$this->load->view('sync/sync_view', $data);
			return;
		}
		//if no error return hr tags
		$data['hr_tags'] = $hr_tags;
		$this->load->view('sync/sync_view', $data);
	}

	//syncs highrise contacts to freshbooks
	public function sync_contacts()
	{
		if ($this->_check_login())
		{
			$data['navigation'] = TRUE;	
		}
		
		$data['title']   = 'Highrise to Freshbooks Sync Tool :: Sync Contacts Results';
		$data['heading'] = 'Sync HighRise Contacts Results';
		
		$settings = $this->_get_settings();
		//set url to freshbooks account for link on results page
		$url_segments = parse_url($settings['fburl']);
		$data['fb_url'] = $url_segments['scheme'].'://'.$url_segments['host'].'/menu.php';
		
		//get highrise clients - exit on api error
		$tag_id = $_POST['tagfilter'];
		try {
			$hr_clients = $this->highrise_to_freshbooks->get_highrise_clients($tag_id);
		} catch (Exception $e) {
			$data['error'] = $e->getMessage();
			$this->load->view('sync/sync_results_view', $data);
			return;
		}
		
		//Generate an array of freshbooks client email addresses for comparison
		//to highrise client email addresses
		//process first FB page
		try {
			$fb_clients = $this->highrise_to_freshbooks->get_freshbooks_clients();
		} catch (Exception $e) {
			$data['error'] = $e->getMessage();
			$this->load->view('sync/sync_results_view', $data);
			return;
		}
		
		//no errors on client api request extract email into array
		$fb_emails = array();
		foreach ($fb_clients->clients->client as $client) {
			$fb_emails[] = (string)$client->email;
		}
		
		//set pages var based on actual pages returned by FB
		$fb_pages = (integer)$fb_clients->clients->attributes()->pages;
		
		//process additional FB pages if they exist
		while ($fb_pages > 1) {
			try {
				$fb_clients = $this->highrise_to_freshbooks->get_freshbooks_clients($fb_pages);
			} catch (Exception $e) {
				$data['error'] = $e->getMessage();
				$this->load->view('sync/sync_results_view', $data);
				return;
			}
		  //no errors on client api request extract email into array
			foreach ($fb_clients->clients->client as $client) {
				$fb_emails[] = (string)$client->email;
			}
			$fb_pages--;
		}//end while
		
		try {
			$sync_results = $this->highrise_to_freshbooks->sync_clients($hr_clients, $fb_emails);
		} catch (Exception $e) {
			$data['error'] = $e->getMessage();
			$this->load->view('sync/sync_results_view', $data);
			return;
		}
		//on successful sync - sort array - set results - load view
		usort($sync_results, array("Sync", '_results_sort'));
		$data['result'] = $sync_results;
		$this->load->view('sync/sync_results_view', $data);
	}

}