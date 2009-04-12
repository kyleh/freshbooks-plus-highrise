<?php echo $this->load->view('common/header'); ?>
<div id="banner_wrap">
  <div id="banner">
    <div class="banner_title"><?php echo $heading ?></div>
  </div>
</div>
<div id="content">

	<h3>Client Sync Results:</h3>
	<?php
		// foreach ($xml as $hr_clients) {
		// 	foreach ($hr_clients as $key => $value) {
		// 		echo $key . ' = ' . $value . '<br />';
		// 	}
		// 	echo '<br />';
		//}
	
	
	?>
	
	<?php var_dump($xml); ?>
	
</div><!-- end div content -->
<!-- load the footer -->
<?php echo $this->load->view('common/footer'); ?>