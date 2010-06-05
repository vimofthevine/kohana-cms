<h2><?php echo __('Page List') ?></h2>

<?php if (count($pages) == 0): ?>
<p>
	There are no pages at this time
	(<?php echo HTML::anchor(Route::$current
		->uri(array('action'=>'new')), 'create one') ?>).
</p>
<?php else:
	// Create page list
	$grid = new Grid;
	$grid->column()->field('title')->title('Title');
	$grid->column()->field('version')->title('Ver');
	$grid->column('action')->title('Edit')->text('Edit')->class('edit')
		->route(Route::get('admin/cms'))->params(array('action'=>'edit'));
	$grid->data($pages);

	echo $grid;

endif;
