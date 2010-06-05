<?php defined('SYSPATH') or die('No direct script access.');

Route::set('admin/cms/diff', 'admin/page/diff/<id>/<ver1>/<ver2>')
	->defaults(array(
		'directory'  => 'admin',
		'controller' => 'page',
		'action'     => 'diff',
	));

Route::set('admin/cms', 'admin/page(/<action>(/<id>))')
	->defaults(array(
		'directory'  => 'admin',
		'controller' => 'page',
		'action'     => 'list',
	));

Route::set('admin/resource', 'admin/resource(/<action>(/<path>))',array(
	'action' => 'index|read|create|upload|delete',
	'path'   => '.+',
	))
	->defaults(array(
		'directory'  => 'admin',
		'controller' => 'resource',
		'action'     => 'read',
		'path'       => '',
	));

Route::set('media', 'media(/<file>)', array('file' => '.+'))
	->defaults(array(
		'controller' => 'media',
		'action'     => 'file',
		'file'       => NULL,
	));

Route::set('css', 'css(/<file>)', array('file' => '.+'))
	->defaults(array(
		'controller' => 'media',
		'action'     => 'css',
		'file'       => NULL,
	));

