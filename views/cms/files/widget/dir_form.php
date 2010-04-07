<h2>Create a Folder</h2>
<?php echo Form::open(); ?> 

<p>
	Folder names may contain only alpha-numeric characters, underscores, or dashes.
	Folder names may not contain spaces.
</p>

<?php echo isset($errors['directory']) ? '<p class="error">'.$errors['directory'].'</p>': ''; ?> 
<p>
	<?php echo Form::label('directory', 'Folder Name'); ?> 
	<?php echo Form::input('directory', $folder); ?> 
</p>

<p class="submit">
	<?php echo Form::submit('submit', 'Create Folder'); ?> 
</p>

<?php echo Form::close(); ?> 
