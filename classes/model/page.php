<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * CMS page model
 *
 * @package     CMS
 * @author      Kyle Treubig
 * @copyright   (c) 2010 Kyle Treubig
 * @license     MIT
 */
class Model_Page extends Versioned_Sprig {

	public function _init() {
		parent::_init();
		$this->_fields += array(
			'title' => new Sprig_Field_Char(array(
				'empty' => TRUE,
			)),
			'revisions' => new Sprig_Field_HasMany(array(
				'model' => 'Page_Revision',
			)),
			'comment' => new Sprig_Field_Char(array(
				'in_db' => FALSE,
			)),
		);
	}
}

