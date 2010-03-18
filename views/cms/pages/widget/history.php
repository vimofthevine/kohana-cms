<h1>"<?php echo $page->title; ?>" History
	<small><?php echo HTML::anchor(Route::get('admin_cms')->uri(), 'back') ?></small>
</h1>
<?php
    echo form::open();
    echo $grid;
    echo form::close();
?>
