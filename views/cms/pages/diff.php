<h2>
	<?php echo __('Changes for ":title" (Ver :ver1 to Ver :ver2)',
		array(':title'=>$page->title, ':ver1'=>$ver1, ':ver2'=>$ver2)) ?>
	<small><?php echo HTML::anchor( Route::get('admin/cms')->uri(array(
		'action'=>'history', 'id'=>$page->id)), 'back') ?></small>
</h2>

<?php echo $diff; ?>
