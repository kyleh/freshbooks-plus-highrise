<?php echo $this->load->view('common/header'); ?>
<div id="banner_wrap">
  <div id="banner">
    <div class="banner_title">FreshBooks API Authorization</div>
  </div>
</div>
<div id="content">

	<h2>Oauth Debug View</h2>
	
	<?php var_dump($debug); ?>
		
</div><!-- end div content -->
<!-- load the footer -->
<?php echo $this->load->view('common/footer'); ?>