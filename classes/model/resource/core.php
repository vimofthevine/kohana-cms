<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Uploadable resource
 *
 * @package     Admin
 * @category    Model
 * @author      Kyle Treubig
 * @copyright   (c) 2010 Kyle Treubig
 * @license     MIT
 */
abstract class Model_Resource_Core {

	/**
	 * @var string  Upload root location (from config), relative to DOCROOT
	 */
	private $root = '';

	/**
	 * @var string  Full resource path, including DOCROOT
	 */
	public $path = '';

	/**
	 * @var string  Parent directory path, relative to upload root
	 */
	public $parent = '';

	/**
	 * @var string  Resource name
	 */
	public $name = '';

	/**
	 * Create new resource object.  Gets upload base location
	 * from config and determines the parent directory.
	 *
	 * @param   string  resource or parent folder path
	 * @param   boolean TRUE if path is parent folder
	 * @param   boolean FALSE if path is current file
	 */
	public function __construct($path = '', $parent = FALSE) {
		$this->root = Kohana::config('cms.upload.folder').'/';

		if ($parent)
		{
			$this->parent = trim($path, '/');
			$this->path = DOCROOT.$this->root.$this->parent;
		}
		else
		{
			$last_slash = strrpos($path, '/');
			$this->parent = $last_slash ? substr($path, 0, $last_slash) : '';
			$this->name   = $last_slash ? substr($path, ($last_slash+1)) : $path;
			$this->path = DOCROOT.$this->root.$this->parent.'/'.$this->name;
		}
	}

}	// End of Model_Resource_Core

