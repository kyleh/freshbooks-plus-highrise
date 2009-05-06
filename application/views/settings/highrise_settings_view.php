<?php echo $this->load->view('common/header'); ?>
<div id="banner_wrap">
  <div id="banner">
    <div class="banner_title">Highrise API Settings</div>
  </div>
</div>
<div id="content">
	<div class="step_title">
			Step 2 of 3
	</div><!-- end div banner_title -->

	<h3>Log into your Highrise Acount</h3>
	<p>Click <strong>My Info</strong> then <strong>Reveal authentication token</strong> and copy and paste the token into the input field below. <strong>Your Highrise URL</strong> is simple, just get the URL that you log into and paste it here. For example, http://yourname.highrisehq.com. </p>

	
	<?php if (isset($error_data)): ?>
		<?php foreach ($error_data as $error): ?>
			<p class="error"><?php echo $error; ?></p>
		<?php endforeach ?>
	<?php endif ?>
	
	<?php echo validation_errors(); ?>
	
	<?php echo form_open('settings/highrise_settings')."\n"; ?>
	<div id="apiform">
		<div class="api-input">
          <label>Highrise URL</label>
			<input class="input" type="text" name="hrurl" value="<?php echo isset($hrurl) ? $hrurl : set_value('hrurl'); ?>" size="50" />
        </div>
		<div class="api-input">
          <label>Highrise Token</label>
			<input class="input" type="text" name="hrtoken" value="<?php echo isset($hrtoken) ? $hrtoken : set_value('hrtoken'); ?>" size="50" />
        </div>
		<input class="submit" type="submit" name="submit" value="<?php echo $submitname ?>" />
	</div>
	</form>
</div><!-- end div content -->
<!-- load the footer -->
<?php echo $this->load->view('common/footer'); ?>