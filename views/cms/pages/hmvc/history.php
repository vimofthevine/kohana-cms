<h2>"<?php echo $page->title; ?>" History
	<small><?php echo HTML::anchor(Route::get('admin_main')->uri(array('controller'=>'page')), 'back') ?></small>
</h2>
<?php
    echo form::open();
    echo $grid;
    echo form::close();
?>
