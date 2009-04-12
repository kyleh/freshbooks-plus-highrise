<!-- Sync View Main Page Template -->
<?php echo $this->load->view('common/header'); ?>

<div id="banner_wrap">
  <div id="banner">
    <div class="banner_title">FreshBooks Oauth Test</div>
  </div>
</div>

<div id="content">

<?php if (isset($clients)): ?>
<p>Here is a var dump of the result set.  I have the header response to be included in the raw response for debug purposes.  To view the returned xml you will have to view the source code in the browser.</p>

<p>The request for FreshBooks assets is using OAuth authenication headers to authenticate with FreshBooks which is working fine as you can see by the header.  The request knows I want clients so it must be able to see and interpet my xml request block.  The problem is the xml block returned by FreshBooks is empty.</p>


<p>I'm using the following XML for the request using curl with the 'OAuth authentication header' as the header variable and the XML variable as the postfields variable.</p>
<pre>
<code>
	<?php
		echo "<br />\n";  
		echo htmlspecialchars('<?xml version="1.0" encoding="utf-8"?>')."\n";
		echo htmlspecialchars('<request method="client.list">')."\n\t";
		echo htmlspecialchars('<page>1</page>')."\n\t";
		echo htmlspecialchars('<per_page>10</per_page>')."\n";
		echo htmlspecialchars('</request>')."\n";
		?>
</code>
</pre>

<p><strong>View source code to view XML results from FreshBooks:</strong></p>
<p>BEGIN VAR DUMP:</p>

<?php var_dump($clients); ?>

<p>END VAR DUMP:</p>


<strong>Here is the response I get from FreshBooks:</strong>

<pre>
<?php
echo "<br />\n";
echo htmlspecialchars('<response xmlns="http://www.freshbooks.com/api/" status="ok">')."\n\t";
echo htmlspecialchars('<clients>')."\n\t";
echo htmlspecialchars('<clients/>')."\n";
?>
</pre>

<?php endif ?>

<p><?php echo anchor('sync/get_oauth_assets', 'Get A List of Contacts From Freshbooks', array('class' => 'submit')); ?></p>
</div><!-- end div content -->

<!-- load the footer -->
<?php echo $this->load->view('common/footer'); ?>