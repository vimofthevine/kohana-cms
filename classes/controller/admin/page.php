<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Admin page controller
 *
 * @package     Admin
 * @category    Controller
 * @author      Kyle Treubig
 * @copyright   (c) 2010 Kyle Treubig
 * @license     MIT
 */
class Controller_Admin_Page extends Controller_Admin {

	protected $_resource = 'page';

	protected $_resource_required = array('edit', 'history', 'diff');

	protected $_acl_map = array(
		'edit'    => 'edit',
		'history' => 'history',
		'diff'    => 'history',
		'default' => 'manage',
	);

	protected $_acl_required = 'all';

	protected $_view_map = array(
		'edit' => 'admin/layout/wide_column_with_menu',
		'default' => 'admin/layout/wide_column',
	);

	protected $_view_menu_map = array(
		'edit' => 'cms/menu/edit',
	);

	protected $_current_nav = 'admin/page';

	/**
	 * Generate menu for page management
	 */
	protected function _menu() {
		return View::factory('cms/menu/default');
	}

	/**
	 * Load a specified user
	 */
	protected function _load_resource() {
		$id = $this->request->param('id', 0);
		$this->_resource = Sprig::factory('page', array('id'=>$id))->load();
		if ( ! $this->_resource->loaded())
		{
			throw new Kohana_Exception('That page does not exist.', NULL, 404);
		}
	}

	/**
	 * Redirect index action to list
	 */
	public function action_index() {
		$this->request->redirect( $this->request->uri(
			array('action' => 'list')), 301);
	}

	/**
	 * Display list of pages
	 */
	public function action_list() {
		Kohana::$log->add(Kohana::DEBUG, 'Executing Controller_Admin_Page::action_list');
		if ( $this->_internal)
		{
			$this->template->content = View::factory('cms/pages/list_widget')
				->bind('pages', $pages);
		}
		else
		{
			$this->template->content = View::factory('cms/pages/list')
				->bind('pages', $pages);
		}

		$pages = Sprig::factory('page')->load(NULL, FALSE);
	}

	/**
	 * Page editor
	 */
	public function action_edit() {
		Kohana::$log->add(Kohana::DEBUG, 'Executing Controller_Admin_Page::action_edit');
		$this->template->content = View::factory('cms/pages/form')
			->bind('legend', $legend)
			->set('submit', __('Save'))
			->bind('page', $this->_resource)
			->bind('errors', $errors);

		// Bind locally
		$page = & $this->_resource;
		$legend = __('Edit :title', array(':title' => $page->title));

		if ($_POST)
		{
			$page->values($_POST);
			$page->editor = $this->a1->get_user()->id;

			try
			{
				$page->update();

				Message::instance()->info('The page, :title, has been updated.',
					array(':title' => $page->title));

				if ( ! $this->_internal)
					$this->request->redirect( $this->request->uri(array('action'=>'list')) );
			}
			catch (Validate_Exception $e)
			{
				$errors = $e->array->errors('admin');
			}
		}

		// Set template scripts and styles
		$this->template->scripts[] = 'http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.js';
		$this->template->scripts[] = Route::get('media')->uri(array('file'=>'js/markitup/jquery.markitup.js'));
		$this->template->scripts[] = Route::get('media')->uri(array('file'=>'js/markitup/sets/html/set.js'));
		$this->template->styles[Route::get('media')->uri(array('file'=>'js/markitup/skins/markitup/style.css'))] = 'screen';
		$this->template->styles[Route::get('media')->uri(array('file'=>'js/markitup/sets/html/style.css'))] = 'screen';
	}

	/**
	 * List revision history for a page
	 */
	public function action_history() {
		Kohana::$log->add(Kohana::DEBUG, 'Executing Controller_Admin_Page::action_history');

		$ver1 = isset($_POST['ver1']) ? $_POST['ver1'] : NULL;
		$ver2 = isset($_POST['ver2']) ? $_POST['ver2'] : NULL;

		if ($ver1 !== NULL AND $ver2 !== NULL)
		{
			$this->request->redirect( Route::get('admin/cms/diff')->uri(array(
				'id'         => $this->_resource->id,
				'ver1'       => $_POST['ver1'],
				'ver2'       => $_POST['ver2'],
			)) );
		}

		$this->template->content = View::factory('cms/pages/history')
			->bind('page', $this->_resource)
			->bind('revisions', $revisions);
		$revisions = $this->_resource->revisions;
	}

	/**
	 * Show inline difference between two versions
	 */
	public function action_diff() {
		Kohana::$log->add(Kohana::DEBUG, 'Executing Controller_Admin_Page::action_diff');
		$this->template->content = View::factory('cms/pages/diff')
			->bind('page', $this->_resource)
			->bind('ver1', $ver1)
			->bind('ver2', $ver2)
			->bind('diff', $diff);

		// Bind locally
		$page = & $this->_resource;
		$ver1 = $this->request->param('ver1');
		$ver2 = $this->request->param('ver2');

		// Get versions of the text
		$page->version($ver2);
		$new_text = $page->text;
		$page->version($ver1);
		$old_text = $page->text;

		$diff = Versioned::inline_diff($old_text, $new_text);
	}

}	// End of Controller_Admin_Page

