<?php echo $this->load->view('_common/header'); ?>
<div class="container">

	<h3>FreshBooks API Settings</h3>
	
	<?php if (isset($error)): ?>
			<p class="error"><?php echo($error); ?></p>
	<?php endif ?>
	<?php if (isset($debug)): ?>
			<p class="error"><?php var_dump($debug); ?></p>
	<?php endif ?>
	
	
	<p>By clicking on the button below you will be redirected to FreshBooks to enter your account information and authorize this application to use your FreshBooks API settings.</p>

	
	<span style="margin:30px 20px 0 0;"><?php echo anchor('settings/freshbooks_oauth', 'Authorize API Settings', array('class' => 'submit')); ?></span>
	<span style="margin-top:30px;"><?php echo anchor('user/logout', 'Exit', array('class' => 'submit')); ?></span>
	
</div><!-- end div content -->
<!-- load the footer -->
<?php echo $this->load->view('_common/footer'); ?>