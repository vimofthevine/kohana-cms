<script type="text/javascript">
	$(document).ready(function() {
		$("textarea").markItUp(mySettings);
	});
</script>

<section>
    <h1><?php echo $legend; ?></h1>
<?php

	echo Form::open();

	foreach ($page->inputs(FALSE) as $field=>$input)
	{
		echo isset($errors[$field]) ? '<h2>'.$errors[$field].'</h2>' : '';
		echo $page->label($field),PHP_EOL;
		echo $input,PHP_EOL;
		echo '<br />',PHP_EOL;
	}

	echo Form::submit('submit', $submit);
	echo Form::close();

?>
</section>
