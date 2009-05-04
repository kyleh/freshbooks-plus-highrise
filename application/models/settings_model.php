<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
*Settings Model
*Model to interact with settings table in database
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

Class Settings_model extends Model {

	function __construct()
	{
		// Call the Model constructor
		parent::Model();
	}
    
	function got_settings()
	{
		$this->db->where('userid', $this->session->userdata('userid'));
		$this->db->from('apisettings');
		$query = $this->db->get();
		return $query->num_rows(); 
	}
    
	 	/**
	 * Gets API settings.
	 *
	 * @return settings object row if records exit, False on no records
	 **/
	function get_settings()
	{
		$this->db->where('userid', $this->session->userdata('userid'));
		$this->db->from('apisettings');
		$query = $this->db->get();
		if ($query->num_rows > 0) {
			return $query->row();
		}else{
			return FALSE;
		}
	}

	function insert_settings()
	{
		$data = array(
			'userid' => $this->session->userdata('userid'),
			'fburl' => $this->input->post('fburl'),
			'fbtoken' => $this->input->post('fbtoken'),
			'hrurl' => $this->input->post('hrurl'),
			'hrtoken' => $this->input->post('hrtoken')
		);

		$this->db->insert('apisettings', $data);
	}
	
	function update_settings()
	{
		$data = array(
			'userid' => $this->session->userdata('userid'),
			'fburl' => $this->input->post('fburl'),
			'fbtoken' => $this->input->post('fbtoken'),
			'hrurl' => $this->input->post('hrurl'),
			'hrtoken' => $this->input->post('hrtoken')
		);

		$this->db->where('userid', $this->session->userdata('userid'));
		$this->db->update('apisettings',$data);
	}	

}