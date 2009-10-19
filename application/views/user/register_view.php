<!-- 
Login View Template
Login View Page
Created by Kyle Hendricks - Mend Technologies - kyleh@mendtechnologies.com
Ver. 1.0
-->
<?php echo $this->load->view('_common/login_header'); ?>

<div class="login-window">
	<table class="window" cellspacing="0">
		<tr class="one">
			<td class="one"></td>
			<td class="two"></td>
			<td class="three"></td>	
		</tr>
		<tr class="two">
			<td class="one"></td>
			<td class="two">
				<div class="bg_blue">
					<div style="min-height: 300px;" class="span-20 bg_white">
						<img src="<?php echo(base_url()); ?>public/images/freshbooks.highrise.gif" alt="Freshbooks + Highrise" width="352" height="75" />
						<div class="login-form">
							<div>
								<?php echo form_open('user/register')."\n"; ?>
									<p><label class="login-label" for="subdomain">FreshBooks URL:</label> 
									<input type="text" value="" name="fburl"/><br/><label class='login-label'>&nbsp;</label> <strong>xxxxx</strong>.freshbooks.com</p>
									
									<label for="password" class="login-label">Password:</label> <input type="password" name="password" />
									<br />
									<label for="confpassword" class="login-label">Confirm Password:</label> <input type="password" name="confpassword" />
									<input type="hidden" name="register" value="true">
									<button value="submit"><span><span>Create Account</span></span></button>
								</form>
							</div>
							<div class="login-form-footer"></div>
							<?php echo validation_errors(); ?>
							<?php if (isset($error)): ?>
								<span style="color:red; padding-top:10px"><?php echo $error ?></span>
							<?php endif ?>
						</div>
					</div><!-- end div login-form -->
					<div class="span-9 prepend-1 white">
						<h2>FreshBooks + Highrise</h2>
						<p>
							Did you close a deal in <a href="" target="_blank" style="color: white;">Highrise?</a> Move the contact into <a href="" style="color: white;">FreshBooks</a> by tagging it with client and using this handy connector. 
						</p>
						<p>Already have a FreshBooks and Highrise connector account? <?php echo anchor('user/index', 'Return to login.', array('style' => 'color:white')); ?></p>
					</div>

					<div class="clear"></div>
				</div>
			</td>
			<td class="three"></td>	
		</tr>
		<tr class="three">
			<td class="one"></td>
			<td class="two"></td>
			<td class="three"></td>	
		</tr>
	</table>
</div>