<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
*Settings Controller
*Controller to add/edit and check for valid settings
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

Class Settings extends Controller
{
	
	function  __construct()
  {
		parent::Controller();
		$this->load->helper(array('form', 'url', 'html'));
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
	
	//validate freshbooks and highrise api functions
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
	
	//method to add/edit/verify settings
	public function index($settings_status='ok')
	{
		if ($this->_check_login())
		{
			$data['navigation'] = TRUE;	
		}
		
		$data['title']   = 'Highrise to Freshbooks Sync Tool :: API Settings';
		$data['submitname'] = 'Save API Settings';
		
		if ($settings_status == 'invalid') {
			$data['error_data'] = $this->session->flashdata('error');;
		}

		//check for settings
		$this->load->model('Settings_model', 'settings');
		$current_settings = $this->settings->get_settings();
		if ($current_settings) {
			$data['submitname'] = 'Update API Settings';
			//set form fields
			$data['fburl']   = $current_settings->fburl;
			$data['fbtoken'] = $current_settings->fbtoken;
			$data['hrurl']   = $current_settings->hrurl;
			$data['hrtoken'] = $current_settings->hrtoken;
		}
		
		//load form validation helper
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<p class="error">', '</p>');
		//validation rules
		$this->form_validation->set_rules('fburl', 'FreshBooks API URL', 'required');
		$this->form_validation->set_rules('fbtoken', 'FreshBooks API Token', 'required');
		$this->form_validation->set_rules('hrurl', 'Highrise URL', 'required');
		$this->form_validation->set_rules('hrtoken', 'Highrise API Token', 'required');

		if ($this->form_validation->run() == FALSE){
			$this->load->view('settings/settings_view', $data);
		}else{
			$this->load->model('Settings_model', 'settings');
			if ($_POST['submit']  == 'Update API Settings') {
				$this->settings->update_settings();
			}else{
				$this->settings->insert_settings();
			}
			
			//validate settings
			$params = $this->_get_settings();
			$this->load->library('Highrise_to_freshbooks', $params);
			$check_fb_settings = $this->_validate_api_settings();
			
			$this->load->view('settings/settings_success_view', $data);
		}//end if
	}
}