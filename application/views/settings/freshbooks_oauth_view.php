<?php echo $this->load->view('common/header'); ?>
<div id="banner_wrap">
  <div id="banner">
    <div class="banner_title">FreshBooks API Authorization</div>
  </div>
</div>
<div id="content">
	<div class="step_title">
			Step 3 of 3
	</div><!-- end div banner_title -->

	<h3>Authorize FreshBooks API Settings</h3>
	
	<p>By clicking on the button below you will be redirected to FreshBooks to enter your account information and authorize this application to use your FreshBooks API settings.</p>

	<?php if (isset($error_data)): ?>
		<?php foreach ($error_data as $error): ?>
			<p class="error"><?php echo $error; ?></p>
		<?php endforeach ?>
	<?php endif ?>
	
	<div style="margin-top:30px;"><a href="<?php echo isset($auth_url) ? $auth_url : ''; ?>" class="submit" >Authorize API Settings</a></div>
</div><!-- end div content -->
<!-- load the footer -->
<?php echo $this->load->view('common/footer'); ?>