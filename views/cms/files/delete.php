<h2><?php echo $legend ?></h2>
<p>
	Are you sure you want to delete
	<?php echo $resource->parent.'/'.$resource->name ?>?
	This action cannot be undone.
</p>
<?php
	echo Form::open();
	echo Form::submit('yes', 'Yes');
	echo Form::submit('no', 'No');
	echo Form::close();
?>
