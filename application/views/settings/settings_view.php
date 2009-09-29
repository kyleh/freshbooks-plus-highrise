<?php echo $this->load->view('_common/header'); ?>
<div class="container">
	<h3>Connect to your Highrise Account</h3>
	<p>Click <strong>My Info</strong> then <strong>Reveal authentication token</strong> and copy and paste the token into the input field below. <strong>Your Highrise URL</strong> is simple, just get the URL that you log into and paste it here. For example, http://yourname.highrisehq.com. </p>
	

	<?php if(isset($error)): ?>
			<p class="error"><?php echo $error; ?></p>
	<?php endif ?>
	
	<?php echo validation_errors(); ?>
	
	<img src="<?php echo(base_url()); ?>public/images/hr_screenshot.jpg" style="float:right;" height="236" width="340" alt="FreshBooks + Highrise" />
	
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
		<input class="submit" type="submit" name="submit" value="<?php echo isset($submitname) ? $submitname : 'Save API Settings'; ?>" />
	</div>
	</form>
</div><!-- end div content -->
<!-- load the footer -->
<?php echo $this->load->view('_common/footer'); ?>