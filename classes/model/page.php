<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * CMS page model
 *
 * @package     Admin
 * @category    Model
 * @author      Kyle Treubig
 * @copyright   (c) 2010 Kyle Treubig
 * @license     MIT
 */
class Model_Page extends Versioned_Sprig {

	public function _init() {
		parent::_init();
		$this->_fields += array(
			'title' => new Sprig_Field_Tracked(array(
				'empty' => TRUE,
			)),
			'text'  => new Sprig_Field_Versioned,
			'revisions' => new Sprig_Field_HasMany(array(
				'model' => 'Page_Revision',
			)),
			'comment' => new Sprig_Field_Char(array(
				'in_db' => FALSE,
			)),
		);
	}
}

