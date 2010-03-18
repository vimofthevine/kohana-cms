<?php defined('SYSPATH') or die('No direct script access.');

return array(
	'directory' => array(
		'alpha_dash' => 'Folder names must consist of only alphabetical, numeric, underscore, and dash characters',
	),
	'file' => array(
		'Upload::not_empty' => 'A file must be specified in order to upload',
		'Upload::size'      => 'The file specified must not exceed 1 MB',
		'Upload::type'      => 'The file specified must be of a valid upload type',
		'Upload::valid'     => 'The file specified for upload is invalid',
	),
	'name' => array(
		'alpha_dash' => 'New file name must consist of only alphabetical, numeric, underscore, and dash characters',
		'filename_available' => 'The new file name you have chosen is already in use.  To overwrite, delete the original file first',
	),
);
