<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Settings Model
 *
 * Model to access settings table in database.
 *
 * @author Kyle Hendricks - Mend Technologies - kyleh@mendtechnologies.com
 * @version 1.0 - October 2009
 *
 * @copyright 2009 - Kyle Hendricks - Mend Technologies
 *
 * All rights reserved.
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * 
 * Redistributions of source code must retain the above copyright notice,
 * this list of conditions and the following disclaimer.
 *
 * Redistributions in binary form must reproduce the above copyright
 * notice, this list of conditions and the following disclaimer in the
 * documentation and/or other materials provided with the distribution.
 *
 * Neither the name of the <ORGANIZATION> nor the names of its
 * contributors may be used to endorse or promote products derived from this
 * software without specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
 * ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
 * ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/

Class Settings_model extends Model {

    public function __construct()
    {
        // Call the Model constructor
        parent::Model();
    }
    
	 /**
	 * Gets API settings.
	 *
	 * @return settings object row if records exit, False on no records
	 **/
	public function get_settings()
	{
		$user_id = $this->session->userdata('userid');
		if (!$user_id) {
			throw new Exception('Unable to access settings. Missing user id. Please login and try again');
		}
		$this->db->where('userid', $user_id);
		$this->db->from('apisettings');
		$query = $this->db->get();
		if ($query->num_rows > 0) {
			return $query->row();
		}else{
			return FALSE;
		}
	}
	 
	/**
	 * Public method insert_fb_settings() of Settings_model class/CI model.
	 *
	 * Inserts FreshBooks OAuth credentials to the database.
	 * 
	 * @param  array  $settings  Array containing OAuth credentials
	 * @param  int    $user_id  session variable containing userid
	 *
	 * @return bool  False on fail True on success.
	 *
	*/
	public function insert_fb_settings($settings)
	{
		$user_id = $this->session->userdata('userid');
		if (!$user_id) {
			throw new Exception('Unable to insert settings. Missing user id. Please login and try again');
		}
		$data = array(
			'userid' => $user_id,
			'fb_oauth_token' => $settings['oauth_token'],
			'fb_oauth_token_secret' => $settings['oauth_token_secret'],
			);
		
		$this->db->insert('apisettings',$data);
	}
	
	/**
	 * Public method update_fb_settings() of Settings_model class/CI model.
	 *
	 * Updates FreshBooks OAuth credentials to the database.
	 * 
	 * @param  array  $settings  Array containing OAuth credentials
	 * @param  int    $user_id  session variable containing userid
	 *
	 * @return bool  False on fail True on success.
	 *
	*/
	public function update_fb_settings($settings)
	{
		$user_id = $this->session->userdata('userid');
		if (!$user_id) {
			throw new Exception('Unable to update settings. Missing user id. Please login and try again');
		}
		
		$data = array(
			'fb_oauth_token' => $settings['oauth_token'],
			'fb_oauth_token_secret' => $settings['oauth_token_secret']
			);
		
		$this->db->where('userid',$user_id);
		$this->db->update('apisettings',$data);
	}
	
	/**
	 * Public method update_hr_settings() of Settings_model class/CI model.
	 *
	 * Updates Highrise subdomain and access token in the database.
	 * 
	 * @param  string/array POST var hrurl - Highrise url from post data
	 * @param  string/array POST var hrtoken - Highrise token from post data
	 * @param  int    $user_id  session variable containing userid
	 *
	 * @return bool  False on fail True on success.
	 *
	*/
	public function update_hr_settings()
	{
		$user_id = $this->session->userdata('userid');
		if (!$user_id) {
			throw new Exception('Unable to update settings. Missing user id. Please login and try again');
		}
		$raw_domain = $this->input->post('hrurl');
		$hr_domain = preg_replace('%http[a-z]*://|\.[a-zA-Z0-9]*\.com%', '', $raw_domain);
		
		$data = array(
			'hrurl' => $hr_domain,
			'hrtoken' => $this->input->post('hrtoken')
		);

		$this->db->where('userid', $user_id);
		$this->db->update('apisettings',$data);
	}	
}