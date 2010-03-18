<div class="grid_4">
<?php
	$url = Route::get('admin_cms')->uri(array('action'=>'menu'));
	echo Request::factory($url)->execute()->response;
?>
</div>

<div class="grid_8">
<?php
	$url = Route::get('admin_cms_asset')->uri(array(
		'action' => 'upload',
		'file'   => Request::instance()->param('file'),
	));
	echo Request::factory($url)->execute()->response;
?>
</div>

<div class="grid_4">&nbsp;</div>
