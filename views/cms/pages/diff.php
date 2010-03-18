<div class="grid_4">
<?php
	$url = Route::get('admin_cms')->uri(array('action'=>'menu'));
	echo Request::factory($url)->execute()->response;
?>
</div>

<div class="grid_12">
<?php
	$url = Route::get('admin_cms_diff')->uri(array(
		'id' => $page->id,
		'ver1' => Request::instance()->param('ver1'),
		'ver2' => Request::instance()->param('ver2'),
	));
	echo Request::factory($url)->execute()->response;
?>
</div>
