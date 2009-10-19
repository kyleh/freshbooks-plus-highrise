<!-- 
Header Template
Created by Kyle Hendricks - Mend Technologies - kyleh@mendtechnologies.com
Ver. 1.0 5/3/2009
Copyright (c) 2009, Kyle Hendricks - Mend Technologies
All rights reserved. 
-->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title><?php echo isset($title) ? $title : ''; ?></title>
<link rel="stylesheet" type="text/css" href="<?php echo(base_url()); ?>public/stylesheets/default.css" media="screen" />

<script type="text/javascript" charset="utf-8">
	<!--
	function submitonce(theform) {
			if( document.getElementById ) {
				input = document.getElementById('submit');
				p = document.createElement( 'p' );
				p.setAttribute("class", "submit");
							p.setAttribute("className", "submit");
				p.innerHTML = 'Importing...';
				input.parentNode.replaceChild( p, input );
				return true;
			}
	}
	-->
</script>
<!--[if lt IE 7.]>
<script src="<?php echo(base_url()); ?>public/js/pngfix.js" type="text/javascript"></script>
<![endif]-->

</head>
<body class="integrator">
<div id="header_wrap">
	<div class="addon-header">
		<div class="container">
			<?php if (isset($navigation)): ?>
			<div class="account">
				<ul>
					<li><?php echo anchor('settings/index', 'API Settings'); ?></li>
					<li><?php echo anchor('sync/index', 'Import Contacts'); ?></li> 
					<li><?php echo anchor('user/logout', 'Log Out'); ?></li>
				</ul>
			</div>
			<?php endif ?>
			<span class="addon-name">FreshBooks + Highrise</span>
		</div>
	</div>
  	
</div>