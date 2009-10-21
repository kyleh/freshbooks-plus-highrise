<!-- 
Sync View Template
Sync view that displays highrise tags
Created by Kyle Hendricks - Mend Technologies - kyleh@mendtechnologies.com
Ver. 1.0 5/3/2009
-->
<?php echo $this->load->view('_common/header'); ?>
<div class="container">
	<?php if (isset($debug)): ?>
		<p class="error"><?php var_dump($debug); ?></p>
	<?php endif ?>	
	
	<?php if (isset($error)): ?>
		<p class="error"><?php echo $error; ?></p>
	<?php endif ?>	
	<?php if (isset($hr_tags)): ?>
	<?php echo form_open('sync/sync_contacts', array('onSubmit' => "return submitonce(this)"))."\n"; ?>
		<div id="tagform">
			<h2>Choose Highrise contacts to import</h2>
			<ul class="tags">
	          <li>
				<input type="radio" name="tagfilter" value="nofilter" checked="checked" >
				<label>Import everybody!</label>
			  </li>
			<?php foreach($hr_tags as $key => $value): ?>
			  <li>
				<?php if ($value == ''): ?>
					<input disabled type="radio" name="tagfilter" value="<?php echo $value; ?>">
					<label>Only contacts tagged <strong><?php echo $key; ?></strong><?php if($value == ''){echo ' (tag not is use by Highrise)';} ?></label>
				<?php else: ?>
					<input type="radio" name="tagfilter" value="<?php echo $value; ?>">
					<label>Only contacts tagged <strong><?php echo $key; ?></strong><?php if($value == ''){echo ' (tag not is use by Highrise)';} ?></label>
				<?php endif ?>
			  </li>
			<?php endforeach; ?>
			</ul>
			<input class="submit" type=submit onclick="" id="submit" value="Import into FreshBooks">
		</div><!-- end div tagform -->
	</form>
	<?php endif ?>
	
</div><!-- end div content -->

<!-- load the footer -->
<?php echo $this->load->view('_common/footer'); ?>