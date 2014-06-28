<h2><?php echo __(':dir directory', array(':dir'=>$folder)) ?></h2>

<?php
	// Create directory listing
	$grid = new Grid;
	$grid->column('action')->field('path')->title('File/Folder Name')->text('{name}')
		->route(Route::get('admin/resource'))->params(array('action'=>'read'))->param('path');
	$grid->column()->field('size')->title('Size');
	$grid->column()->field('date')->title('Date Modified');
	$grid->column('action')->field('path')->title('Actions')->text('Delete')->class('delete')
		->route(Route::get('admin/resource'))->params(array('action'=>'delete'))->param('path');
	$grid->data($folder->folders);
	$grid->data($folder->files);

	echo $grid->render();

