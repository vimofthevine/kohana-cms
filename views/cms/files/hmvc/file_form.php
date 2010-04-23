<h2>Upload a File</h2>
<?php echo Form::open(NULL, array('enctype'=>'multipart/form-data')); ?> 

<?php echo isset($errors['file']) ? '<p class="error">'.$errors['file'].'</p>': ''; ?> 
<p>
	<?php echo Form::label('file', 'Local File'); ?> 
	<?php echo Form::file('file'); ?>
</p>

<?php echo isset($errors['name']) ? '<p class="error">'.$errors['name'].'</p>': ''; ?> 
<p>
	<?php echo Form::label('name', 'New File Name <small>(optional, without the filetype extension)</small>'); ?> 
	<?php echo Form::input('name', $name); ?> 
</p>

<p class="submit">
	<?php echo Form::submit('submit', 'Upload File'); ?> 
</p>

<?php echo Form::close(); ?> 
