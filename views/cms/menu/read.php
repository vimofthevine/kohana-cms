<div class="box">
	<h2><?php echo __('Manage content') ?></h2>
	<p>
		<ul><li>
	   <?php echo HTML::anchor(Route::get('admin/resource')->uri(array(
            'action' => 'create',
            'path'   => $folder->parent.'/'.$folder->name)), 'Create Folder') ?>
	</li><li>
        <?php echo HTML::anchor(Route::get('admin/resource')->uri(array(
            'action' => 'upload',
            'path'   => $folder->parent.'/'.$folder->name)), 'Upload File') ?>
	</li></ul>
	</p>
</div>
