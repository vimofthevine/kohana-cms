<script type="text/javascript">
	$(document).ready(function() {
		$("textarea").markItUp(mySettings);
	});
</script>

<h1><?php echo $legend; ?></h1>
<?php echo Form::open(); ?> 

<?php foreach ($page->inputs(FALSE) as $field=>$input): ?>
<?php echo isset($errors[$field]) ? '<p class="error">'.$errors[$field].'</p>' : ''; ?> 
<p>
	<?php echo $page->label($field); ?> 
	<?php echo $input; ?> 
</p>
<?php endforeach; ?>

<p class="submit">
	<?php echo Form::submit('submit', $submit); ?> 
</p>
<?php echo Form::close(); ?> 
