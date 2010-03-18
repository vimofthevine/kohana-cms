<h1>Delete <?php echo $dir ?> folder?</h1>
<p>
	Are you sure you want to delete <?php echo $dir ?>?
	This action cannot be undone.
</p>
<?php
	echo Form::open();
	echo Form::submit('yes', 'Yes');
	echo Form::submit('no', 'No');
	echo Form::close();
?>
