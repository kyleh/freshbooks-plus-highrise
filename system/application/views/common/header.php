<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $title; ?></title>
<link rel="stylesheet" type="text/css" href="<?php echo(base_url()); ?>public/stylesheets/default.css" media="screen" />
<script src="<?php echo(base_url()); ?>public/js/jquery.js" type="text/javascript"></script>
<script src="<?php echo(base_url()); ?>public/js/formhelper.js" type="text/javascript"></script>
<script>

function submitonce(theform) {
	if( document.getElementById ) {
		input = document.getElementById('submit');
		p = document.createElement( 'p' );
		p.innerHTML = 'Synchronizing...';
		input.parentNode.replaceChild( p, input );
		return true;
	}
}
</script>
<!--[if lt IE 7.]>
<script src="<?php echo(base_url()); ?>public/js/pngfix.js" type="text/javascript"></script>
<![endif]-->

</head>
<body class="integrator">
<div id="header_wrap">
  <div id="header">
  	<img src="<?php echo(base_url()); ?>public/stylesheets/images/freshbooks.highrise.gif" class="logo" height="80" width="430" alt="FreshBooks + Highrise" />
<?php if ($navigation){ ?>
	<ul>
		<li><? echo anchor('settings/index', 'API Settings'); ?></li> | 
		<li><? echo anchor('sync/index', 'Sync Contacts'); ?></li> | 
		<li><? echo anchor('user/logout', 'Logout'); ?></li>
	</ul>
<?php } ?> 
  </div>
</div>