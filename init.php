<?php defined('SYSPATH') or die('No direct script access.');

Route::set('admin_cms', 'admin/page(/<action>(/<id>))')
	->defaults(array(
		'directory'  => 'admin',
		'controller' => 'page',
		'action'     => 'index',
	));

Route::set('admin_cms_diff', 'admin/page/diff/<id>/<ver1>/<ver2>')
	->defaults(array(
		'directory'  => 'admin',
		'controller' => 'page',
		'action'     => 'diff',
	));

Route::set('media', 'media(/<file>)', array('file' => '.+'))
	->defaults(array(
		'controller' => 'media',
		'action'     => 'file',
		'file'       => NULL,
	));

