<h2>
	Changes for "<?php echo $page->title; ?>" (Ver <?php echo $ver1 ?>
	to Ver <?php echo $ver2 ?>)
	<small><?php echo HTML::anchor(Route::get('admin_main')
	->uri(array('controller'=>'page', 'action'=>'history', 'id'=>$page->id)), 'back') ?></small>
</h2>
<?php echo $diff; ?>
