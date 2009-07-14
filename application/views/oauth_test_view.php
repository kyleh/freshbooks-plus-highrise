<?php echo $this->load->view('_common/header'); ?>
<div id="banner_wrap">
  <div id="banner">
    <div class="banner_title">Debug Dump View</div>
  </div>
</div>
<div id="content">

	<h3>Debug Dump View:</h3>
	
	<?php var_dump($debug); ?>
	
</div><!-- end div content -->
<!-- load the footer -->
<?php echo $this->load->view('_common/footer'); ?>