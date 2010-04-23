<div class="box">
	<h2>Manage Content</h2>
	<p>
		<ul>
			<li><?php echo HTML::anchor(Route::get('admin_main')->uri(array('controller'=>'page','action'=>'list')), 'Page List') ?></li>
			<li><?php echo HTML::anchor(Route::get('admin_cms_asset')->uri(), 'Manage Files') ?></li>
		</ul>
	</p>
</div>
