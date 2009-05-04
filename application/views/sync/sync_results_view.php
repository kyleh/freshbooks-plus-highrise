<!-- 
Sync Results View Template
Display sync results
Created by Kyle Hendricks - Mend Technologies - kyleh@mendtechnologies.com
Ver. 1.0 5/3/2009
-->
<?php echo $this->load->view('common/header'); ?>
<div id="banner_wrap">
  <div id="banner">
    <div class="banner_title"><?php echo $heading ?></div>
  </div>
</div>
<div id="content">

	<h3>Client Sync Results:</h3>
	<?php if(isset($error)): ?>
		<p class='error'><?php echo $error; ?></p>
	<?php elseif (!empty($result)): ?>
		<?php
		$num = 0;
		$count = count($result);
		?>
		<table id="sync_results_table">
			<tr>
				<th>Status</th>
				<th>Company</th>
				<th>Contact Name</th>
				<th>Message</th>
			</tr>
		<?php while ($num < $count): ?>
		<?php
			if($num % 2 == 0){
				$class = '';
			}else{
				$class='alt';
			}
		?>
			<tr class="<?php echo $class ?>">
				<td><?php echo $result[$num]['Status'] ?></td>
				<td><?php echo $result[$num]['Company'] ?></td>
				<td><?php echo $result[$num]['Name'] ?></td>
				<td><?php echo $result[$num]['Message']?></td>
			</tr>
		
		<?php $num++; ?>
		<?php endwhile ?>
		</table>
		
	<?php else: ?>	
		<p>Selected Highrise clients are already in sync with Freshbooks.</p>
	<?php endif ?>
	
	<div style="margin-top:30px;"><a href="<?php echo $fb_url; ?>" class="submit" target="_blank">Go to FreshBooks</a></div>
</div><!-- end div content -->
<!-- load the footer -->
<?php echo $this->load->view('common/footer'); ?>