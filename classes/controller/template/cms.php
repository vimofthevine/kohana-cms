<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Basic Website Template Controller
 *
 * @package     CMS
 * @author      Kyle Treubig
 * @copyright   (c) 2010 Kyle Treubig
 * @license     MIT
 */
abstract class Controller_Template_Cms extends Controller_Template {

	/**
	 * @var Template file
	 */
	public $template = 'cms/template';

	/*
	 * @var Internal request
	 */
	protected $internal_request = FALSE;

	/**
	 * Configure website controller
	 */
	public function before() {
		parent::before();

		// Set common variables
		$this->a2 = A2::instance('auth');
		$this->a1 = $this->a2->a1;
		$this->session = Session::instance();

		// Check if internal request
		if ($this->request !== Request::instance())
		{
			$this->internal_request = TRUE;
		}
	}

	/**
	 * Perform pre-render actions on website controller
	 */
	public function after() {
		if ($this->internal_request)
		{
			$content = $this->template->content;
			$this->template = $content;
		}

		parent::after();
	}
}

