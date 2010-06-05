<div class="box">
	<h2><?php echo __('Manage Content') ?></h2>
	<p>
		<ul>
			<li><?php echo HTML::anchor(Route::get('admin/cms')->uri(), 'Page List') ?></li>
			<li><?php echo HTML::anchor(Route::get('admin/resource')->uri(), 'Manage Files') ?></li>
		</ul>
	</p>
</div>
