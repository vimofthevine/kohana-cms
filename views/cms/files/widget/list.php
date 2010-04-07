<h2><?php echo $current_dir ?> directory 
	<small>
		<?php echo HTML::anchor(Route::get('admin_cms_asset')->uri(array('action'=>'create', 'file'=>$dir)), 'Create Folder') ?>
		<?php echo HTML::anchor(Route::get('admin_cms_asset')->uri(array('action'=>'upload', 'file'=>$dir)), 'Upload File') ?>
	</small>
</h2>
<?php echo $grid; ?>
