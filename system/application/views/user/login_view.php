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
				<li><?php echo anchor('user/register', 'Sign Up For A New Account'); ?></li>
		    </ul>
		</div>
		<h1><?php echo $heading ?></h1>
		<?php
		 if($error){
			echo "<div class=\"error\">".$error."</div>";
			}; 
		?>
		<?php echo form_open('user/verify')."\n"; ?>
			<div>
				  <div class="login-input">
					<label>Email Address</label>
					<input class="input" type="text" name="email"/>
				  </div>
				  <div class="login-input">
					<label>Password</label>
					<input class="input" type="password" name="password"/>
				  </div>
				  <input class="submit" type="submit" name="submit" value="Login" />
			</div>
		</form>
	</div>
</div>
</div><!-- end div content -->
<!-- load the footer -->
<?php echo $this->load->view('common/footer'); ?>
