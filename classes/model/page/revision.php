<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * CMS page revision model
 *
 * @package     Admin
 * @category    Model
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

	// Foreign keys helper, code taken from sprig/classes/sprig/core.php
	public function fk($table = NULL)
	{
		$key = 'page_'.$this->_primary_key;

		if ($table)
		{
			if ($table === TRUE)
			{
				$table = $this->_table;
			}

			return $table.'.'.$key;
		}

		return $key;
	}


	public function __get($key) {
		if ($key == 'comments')
		{
			$return = '<ul>';
			foreach (parent::__get('comments') as $comment)
			{
				$return .= '<li>'.$comment.'</li>';
			}
			$return .= '</ul>'.PHP_EOL;
			return $return;
		}
		return parent::__get($key);
	}
}

