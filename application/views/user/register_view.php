<!-- 
Register View Template
Register for new account page
Created by Kyle Hendricks - Mend Technologies - kyleh@mendtechnologies.com
Ver. 1.0 5/3/2009
-->
<?php echo $this->load->view('common/header'); ?>
<div id="banner_wrap">
  <div id="banner">
    <div class="banner_title">Welcome to the Highrise to FreshBooks Sync Tool</div>
  </div>
</div>
<div id="content">
<div id="user_form_wrap">
	<div id="user_form">
		<div id="sub-header">
			<ul>
				<li><?php echo anchor('user/index', 'I Already Have An Account'); ?></li>
		    </ul>
		</div>
		<h1><?php echo $heading ?></h1>
		<?php echo validation_errors(); ?>
		<?php echo form_open('user/register')."\n"; ?>
		<div>
			<div class="login-input">
			  <label>Full Name</label>
				<input class="input" type="text" name="name" id="name" value="<?php echo set_value('name');?>" size="25" />
			  </label>
			</div>
			<div class="login-input">
			  <label>Email Address</label>
				<input class="input" type="text" name="email" id="email" value="<?php echo set_value('email');?>" size="25" />
			  </label>
			</div>
			<div class="login-input">
			  <label>Password</label>
				<input class="input" type="password" name="password" id="password" value="" value="<?php echo set_value('password');?>" size="25" />
			  </label>
			</div>
			<div class="login-input">
			  <label>Password Confirm</label>
				<input class="input" type="password" name="passconf" value="" value="<?php echo set_value('passconf');?>" size="25" />
			  </label>
			</div>
			<input class="submit" type="submit" name="submit" value="Submit" />
		</div>
		</form>
	</div>
</div>
</div><!-- end div content -->
<!-- load the footer -->
<?php echo $this->load->view('common/footer'); ?>
