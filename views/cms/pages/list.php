<script type="text/javascript">
$(document).ready(function() 
{ 
    $("#pages").tablesorter(); 
} 
); 
</script>

<?php if (count($pages) == 0): ?>
<p>
	There are no pages at this time
	(<?php echo HTML::anchor(Route::$current
		->uri(array('action'=>'new')), 'create one') ?>).
</p>
<?php else: ?>

<table id='pages' class="tablesorter">
<thead>
<tr>
    <th>Id</th>
    <th>Title</th>
    <th>Ver</th>
    <th>Actions</th>
    <th></th>
</tr>
</thead>
<tbody>
	<?php foreach ($pages as $page): ?>
	<tr>
		<td><?= $page->id ?></td>
		<td><?= $page->title ?></td>
		<td><?= $page->version ?></td>
		<td><?= HTML::anchor($request->uri(array('action'=>'edit','id'=>$page->id)),"Edit", array('class'=>'edit')) ?></td>
		<td><?= HTML::anchor($request->uri(array('action'=>'history','id'=>$page->id)),"History", array('class'=>'history')) ?></td>
	</tr>
	<?php endforeach ?>
</tbody>
</table>
<?php endif;
