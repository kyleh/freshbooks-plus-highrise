<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * User Model
 *
 * Model to access user table in database.
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

Class User_model extends Model {

	public function __construct()
	{
	    // Call the Model constructor
	    parent::Model();
	}

	/**
	 * Public method get_user_by_url() of User_model class/CI model.
	 *
	 * Queries users table using the FreshBooks url.
	 * 
	 * @param  string  $fb_url  (optional) FreshBooks Url.
	 *
	 * @return bool|object  False on fail. Object of user data on success.
	 *
	*/
	public function get_user_by_url($fb_url=NULL)
	{
		if ($fb_url){
			$this->db->where('fb_url', $fb_url);
		} else {
			return false;
		}
		
		$this->db->from('users');
		$query = $this->db->get();
		
		if ($query->num_rows() > 0) {
			return $query->row();
		} else {
			return false;
		}
	}
	
	/**
	 * Public method insert_user() method of User_model class/CI model.
	 *
	 * Inserts a new user into the database.
	 * 
	 * @param  string  $fb_url  Pre processed FreshBooks Url subdomain from session variable
	 * @param  string  $password  password var from session variable
	 * 
	 * @return int|bool  Returns user id on success and bool FALSE on fail.
	 *
	*/
	public function insert_user()
	{
		//checks for new user session variables
		if ($this->session->userdata('password') && $this->session->userdata('subdomain')) {
			$data = array(
				'fb_url' => $this->session->userdata('subdomain'),
				'password' => $this->session->userdata('password')
				);
		}else{
			throw new Exception('Unable to insert user.  Session data has timed out.  Please try again.');
		}
		
		$this->db->insert('users', $data);
		return $this->db->insert_id();
	}
	
	/**
	 * Public method update_password() method of User_model class/CI model.
	 *
	 * Inserts a new user into the database.
	 * 
	 * @param  string  $user_id  userid stored in session variable
	 * @param  string  $new_password  new stored in session variable
	 * 
	 * @return int|bool  Returns user id on success and bool FALSE on fail.
	 *
	*/
	public function update_password()
	{
		$user_id = $this->session->userdata('userid');
		if (!$user_id) {
			throw new Exception('Unable to update settings. Missing user id. Please login and try again');
		}
		$new_password = $this->session->userdata('new_pw');
		$data = array(
			'password' => $new_password
			);
		
		$this->db->where('id',$user_id);
		$this->db->update('users',$data);
	}
	
}