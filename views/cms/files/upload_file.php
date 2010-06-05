<h2><?php echo __('Upload a File') ?></h2>
<?php echo Form::open(NULL, array('enctype'=>'multipart/form-data')); ?> 

<?php echo isset($errors['file']) ? '<p class="error">'.$errors['file'].'</p>': ''; ?> 
<p>
	<?php echo Form::label('file', 'Local File'); ?> 
	<?php echo Form::file('file'); ?>
</p>

<?php echo isset($errors['filename']) ? '<p class="error">'.$errors['filename'].'</p>': ''; ?> 
<p>
	<?php echo Form::label('filename', 'New File Name <small>(optional, without the filetype extension)</small>'); ?> 
	<?php echo Form::input('filename', $file->name); ?> 
</p>

<p class="submit">
	<?php echo Form::submit('submit', 'Upload File'); ?> 
</p>

<?php echo Form::close(); ?> 
