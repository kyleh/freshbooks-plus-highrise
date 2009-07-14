<!-- 
Login View Template
Login View Page
Created by Kyle Hendricks - Mend Technologies - kyleh@mendtechnologies.com
Ver. 1.0 5/3/2009
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
								
								<?php echo form_open('user/verify')."\n"; ?>

								<label for="subdomain" class="login-label">FreshBooks Url:</label> 
								<input type="text" name="fburl" value="xxxx" />
								<br />
								<span id="url-help-text"><strong>xxxxx.freshbooks.com</strong></span>
								<br />
								<label for="password" class="login-label">Password</label> <input type="password" name="password" />
								
								<button value="submit"><span><span>Login</span></span></button>
								</form>
							</div>
							<div class="login-form-footer"></div>
							<?php echo validation_errors(); ?>
								<?php
								 //if(//$error){
									//echo '<span style="padding-left: 10px; color: red;">';
									//}; 
								?>
						</div>
					</div>

					<div class="span-9 prepend-1 white">
						<h2>FreshBooks + Highrise</h2>
						<p>
							Did you close a deal in <a href="" target="_blank" style="color: white;">Highrise?</a> Move the contact into <a href="" style="color: white;">FreshBooks</a> by tagging it with client and using this handy connector. 
						</p>
						<p>New to the FreshBooks and Highrise connector? <a href="" style="color: white;">Create an account.</a></p>
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










