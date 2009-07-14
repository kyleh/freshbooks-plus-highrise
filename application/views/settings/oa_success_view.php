<?php echo $this->load->view('_common/header'); ?>
<div id="banner_wrap">
  <div id="banner">
    <div class="banner_title">FreshBooks Authorization Success</div>
  </div>
</div>
<div id="content">
	<div class="step_title">
			Congratulations!  Your ready to sync.
	</div><!-- end div banner_title -->
	
	<p><?php echo anchor('sync/index', 'Proceed to Sync Contacts Page', array('class' => 'submit')); ?></p>
		
</div><!-- end div content -->
<!-- load the footer -->
<?php echo $this->load->view('_common/footer'); ?>