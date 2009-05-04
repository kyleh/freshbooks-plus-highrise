<!-- 
Settings View Template
Outputs results from settings/index controller
Created by Kyle Hendricks - Mend Technologies - kyleh@mendtechnologies.com
Ver. 1.0 5/3/2009
-->
<?php echo $this->load->view('common/header'); ?>
<div id="banner_wrap">
  <div id="banner">
    <div class="banner_title">Highrise to FreshBooks API Settings</div>
  </div>
</div>
<div id="content">
	<div id="message">
		<h2>Your Settings Were Saved Successfully.</h2>
		<p><?php echo anchor('sync/index', 'Proceed to Sync Contacts Page', array('class' => 'submit')); ?></p>
	</div><!-- end div message -->

</div><!-- end div content -->
<!-- load the footer -->
<?php echo $this->load->view('common/footer'); ?>