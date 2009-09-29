<!-- 
Sync Results View Template
Display sync results
Created by Kyle Hendricks - Mend Technologies - kyleh@mendtechnologies.com
Ver. 1.0 5/3/2009
-->
<?php echo $this->load->view('_common/header'); ?>
<div class="container">

	<h3>Client Sync Results:</h3>
	<?php if(isset($error)): ?>
		<p class='error'><?php echo $error; ?></p>
	<?php endif ?>
		
		<?php if (!empty($sync_results)): ?>
			<table id="sync_results_table">
			<?php foreach ($sync_results as $result): ?>
				<?php $class = ($result['message'] != 'Success') ? 'sync_error' : '' ?>
				<tr class="<?php echo $class; ?>">
					<td><img src="<?php echo(base_url()); ?>public/images/person.gif" height="55" width="55" alt="Person Placeholder" /></td>
					<td><?php echo '<span class=\'blue\'>'.$result['first_name'].' '.$result['last_name'].'</span><br />'.$result['email'].'<br />'.$result['work_num'] ?></td>
					<td><?php echo $result['company'] ?></td>
					<td><?php echo $result['message'] ?></td>
				</tr>
			<?php endforeach ?>
			</table>
	<?php else: ?>	
		<p>Selected Highrise clients are already in sync with Freshbooks.</p>
	<?php endif ?>
	<?php if (isset($fburl)): ?>
		<div style="margin-top:30px;"><a href="<?php echo $fburl; ?>" class="submit" target="_blank">Go to FreshBooks</a></div>
	<?php endif ?>
	

</div><!-- end div content -->
<!-- load the footer -->
<?php echo $this->load->view('_common/footer'); ?>