<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
Class Settings extends Controller
{
	
	function  __construct()
  {
		parent::Controller();
		$this->load->helper(array('form', 'url', 'html'));
  }
	
	/**
	 * Private Functions prefixed by _ in CodeIgniter
	 **/

	/**
	 * Checks user login status.
	 *
	 * @return bool	True on success, False and redirect to login on fail
	 **/
	function _check_login()
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
	
	function index()
	{
		if ($this->_check_login())
		{
			$data['navigation'] = TRUE;	
		}
		
		$data['title']   = 'Highrise to Freshbooks Sync Tool :: API Settings';
		$data['submitname'] = 'Save API Settings';
		$data['fburl']   = '';
		$data['fbtoken'] = '';
		$data['hrurl']   = '';
		$data['hrtoken'] = '';
		
		$this->load->library('validation');
		$this->validation->set_error_delimiters('<div class="error">', '</div>');
		
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
		
		//validation rules
		$rules['fburl']		= "required";
		$rules['fbtoken']	= "required";
		$rules['hrurl']		= "required";
		$rules['hrtoken']	= "required";
		$this->validation->set_rules($rules);
		//set form fields
		$fields['fburl']	= 'Freshbooks URL';
		$fields['fbtoken']	= 'Freshbooks Token';
		$fields['hrurl']	= 'Highrise URL';
		$fields['hrtoken']	= 'Highrise Token';
		$this->validation->set_fields($fields);

		if ($this->validation->run() == FALSE){
			$this->load->view('settings/settings_view', $data);
		}else{
			$this->load->model('Settings_model', 'settings');
			if ($_POST['submit']  == 'Update API Settings') {
				$this->settings->update_settings();
			}else{
				$this->settings->insert_settings();
			}
			$this->load->view('settings/settings_success_view', $data);
		}
	}
}