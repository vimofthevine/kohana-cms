<?php defined('SYSPATH') or die('No direct script access.');

return array(
	// Resource=>privilege denial messages
	'page' => array(
		'edit'     => 'You do not have permission to modify :resource.',
		'history'  => 'You do not have permission to view revision history for :resource.',
		'manage'   => 'You do not have permission to manage pages.',
	),
	'asset' => array(
		'create'   => 'You do not have permission to create new folders.',
		'upload'   => 'You do not have permission to upload files.',
		'manage'   => 'You do not have permission to manage uploaded files.',
	),
);
