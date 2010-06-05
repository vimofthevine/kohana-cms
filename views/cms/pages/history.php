<h2><?php echo __('":title" History', array(':title' => $page->title)) ?>
	<small><?php echo HTML::anchor(Request::$current->uri(array('action'=>'list')), 'back') ?></small>
</h2>
<?php
    echo form::open();

	echo '<p class="submit">';
	echo Form::submit('submit', __('View Diff'));
	echo '</p>';

	// Create revision list
	$grid = new Grid;
	$grid->column('radio')->field('version')->title('Version 1')->name('ver1');
	$grid->column('radio')->field('version')->title('Version 2')->name('ver2');
	$grid->column()->field('version')->title('Revision');
	$grid->column()->field('editor')->title('Editor');
	$grid->column('date')->field('date')->title('Date');
	$grid->column()->field('comments')->title('Comments');
	$grid->data($revisions);

    echo $grid;
    echo form::close();
?>
