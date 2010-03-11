<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * CMS page revision model
 *
 * @package     CMS
 * @author      Kyle Treubig
 * @copyright   (c) 2010 Kyle Treubig
 * @license     MIT
 */
class Model_Page_Revision extends Versioned_Revision {

	public function _init() {
		parent::_init();
		$this->_fields += array(
			'entry' => new Sprig_Field_BelongsTo(array(
				'model' => 'Page',
			)),
		);
	}
}

