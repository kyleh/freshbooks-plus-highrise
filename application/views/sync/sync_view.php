<!-- Sync View Main Page Template -->
<?php echo $this->load->view('common/header'); ?>

<div id="banner_wrap">
  <div id="banner">
    <div class="banner_title">Sync Your Highrise Contacts with FreshBooks</div>
  </div>
</div>

<div id="content">
		
	<?php if (is_object($hr_tags)): ?>
	<?php echo form_open('sync/sync_contacts', array('onSubmit' => "return submitonce(this)"))."\n"; ?>
		<div id="tagform">
			<h2>Choose Highrise contacts to import</h2>
			<ul class="tags">
	          <li>
				<input type="radio" name="tagfilter" value="nofilter" checked="checked" >
				<label>Import everybody!</label>
			  </li>
			<?php foreach($hr_tags as $tag): ?>
			  <li>
				<input type="radio" name="tagfilter" value="<?php echo $tag->id; ?>">
				<label>Only contacts tagged <strong><?php echo $tag->name; ?></strong></label>
			  </li>
			<?php endforeach; ?>
			</ul>
			<input class="submit" type=submit onclick="" id="submit" value="Sync to Freshbooks">
		</div><!-- end div tagform -->
	</form>
	
	<div id="sync-right">
	  	<h2>Tag contacts before using this application</h2>
			  <p>If you plan on using tags, please log into your Highrise account and tag your contacts appropriately. If you decide not to use tags, then all Highrise contacts will be added during the synchronization process.</p>
	</div>
	
	<?php else: ?>
	<p><?php echo $hr_tags; ?></p>
	<?php endif ?>

</div><!-- end div content -->

<!-- load the footer -->
<?php echo $this->load->view('common/footer'); ?>