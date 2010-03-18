<h1>
	Changes for "<?php echo $page->title; ?>" (Ver <?php echo $ver1 ?>
	to Ver <?php echo $ver2 ?>)
	<small><?php echo HTML::anchor(Route::get('admin_cms')
	->uri(array('action'=>'history','id'=>$page->id)), 'back') ?></small>
</h1>
<?php echo $diff; ?>
