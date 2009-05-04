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
	<h3>Log into your FreshBooks Account</h3> 
	<p>Click <strong>Settings</strong> then <strong>Enable FreshBooks API</strong>. Once you've enabled the API, you will see <strong>Your API URL</strong> & <strong>Your Authentication Token</strong> in the middle of that page. You'll need to enter those here to continue. </p>

	<h3>Log into your Highrise Acount</h3>
	<p>Click <strong>My Info</strong> then <strong>Reveal authentication token</strong> and copy and paste the token into the input field below. <strong>Your Highrise URL</strong> is simple, just get the URL that you log into and paste it here. For example, http://yourname.highrisehq.com. </p>
	
	<img src="<?php echo(base_url()); ?>public/stylesheets/images/settings.jpg" alt="FreshBooks screenshot of API settings page." style="float:right; border: 3px solid rgb(201, 201, 201); margin-left: 10px; margin-top: 10px;" />
	<?php if(isset($error_data)): ?>
		<?php foreach($error_data as $error): ?>
			<p class="error"><?php echo $error; ?></p>
		<?php endforeach ?>
	<?php endif ?>
	
	<?php echo validation_errors(); ?>
	
	<?php echo form_open('settings')."\n"; ?>
	<div id="apiform">
		<div class="api-input">
          <label>Freshbooks API URL</label>
			<input class="input" type="text" name="fburl" value="<?php echo isset($fburl) ? $fburl : set_value('fburl'); ?>" size="50" />
        </div>
		<div class="api-input">
          <label>Freshbooks Token</label>
			<input class="input" type="text" name="fbtoken" value="<?php echo isset($fbtoken) ? $fbtoken : set_value('fbtoken'); ?>" size="50" />
        </div>
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