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

Class Oa_test extends Controller
{
	function __construct()
	{
		parent::Controller();
		$this->load->helper(array('form', 'url'));
		//$params = $this->_get_settings();
		//$this->load->library('Highrise_to_freshbooks', $params);
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
	function _get_settings()
	{
		$this->load->model('Oa_settings_model','settings');
		$settings = $this->settings->get_settings();
		if ( ! $settings)
		{
			redirect('settings/index');
		}
		else
		{
			return array(
							'fburl' => $settings->fburl,
							'fb_oauth_token_secret' => $settings->fb_oauth_token_secret,
							'fb_oauth_token' => $settings->fb_oauth_token,
							'hrurl' => $settings->hrurl,
							'hrtoken' => $settings->hrtoken,
							);
		}
	}
	
	//gets highrise tags and displays sync page
	public function index()
	{
		if ($this->_check_login())
		{
			$data['navigation'] = TRUE;
		}
		
		$data['title'] = 'Highrise to Freshbooks Sync Tool :: OAuth Test';
	
	
		$settings = $this->_get_settings();
		$this->load->library('FreshbooksOauth', $settings);
		
		$assets = $this->freshbooksoauth->get_fb_clients();
		
		$data['debug'] = $assets;
		$this->load->view('oauth_test_view', $data);
	}


}