<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Admin page controller
 *
 * @package     Controller
 * @author      Kyle Treubig
 * @copyright   (c) 2010 Kyle Treubig
 * @license     MIT
 */
class Controller_Admin_Page extends Controller_Template_Admin {

	/**
	 * Register controller as an admin controller
	 */
	public function before() {
		parent::before();

		$this->restrict('page', 'manage');
	}

	/**
	 * Default action to list
	 */
	public function action_index() {
		$this->action_list();
	}

	/**
	 * Display list of pages
	 */
	public function action_list() {
		$pages = Sprig::factory('page')->load(NULL, FALSE);

		if (count($pages) == 0)
		{
			$this->template->content = new View('cms/pages/none');
			return;
		}

		$grid = new Grid;
		$grid->column()->field('id')->title('ID');
		$grid->column()->field('title')->title('Title');
		$grid->column()->field('version')->title('Version');
		$grid->column('action')->title('Edit')->text('edit')
			->action(Route::get('admin_cms')->uri(array('action'=>'edit')));
		$grid->column('action')->title('Hist')->text('history')
			->action(Route::get('admin_cms')->uri(array('action'=>'history')));
		$grid->data($pages);

		$this->template->content = new View('cms/pages/list');
		$this->template->content->grid = $grid;
	}

	/**
	 * Page editor
	 */
	public function action_edit() {
		$id = Request::instance()->param('id');
		$page = Sprig::factory('page', array('id'=>$id))->load();

		// If page is invalid, return to list
		if ( ! $page->loaded())
		{
			Message::instance()->error('That page does not exist');
			Request::instance()->redirect( Route::get('admin_cms')->uri() );
		}

		// Restrict access
		if ( ! $this->a2->allowed($page, 'edit'))
		{
			Message::instance()->error('You do not have permission to modify :title.', array(':title'=>$page->title));
			Request::instance()->redirect( Route::get('admin_cms')->uri() );
		}

		$page->values($_POST);
		$view = new View('cms/pages/form');
		$view->legend = __('Edit Page');
		$view->submit = __('Save');
		$view->page   = $page;

		if (count($_POST))
		{
			try
			{
				$page->update();
				Message::instance()->info('The page, :title, has been updated.', array(':title'=>$page->title));
				Request::instance()->redirect( Route::get('admin_cms')->uri() );
			}
			catch (Validate_Exception $e)
			{
				$view->errors = count($_POST) ? $e->array->errors('cms') : array();
			}
		}

		$this->template->scripts[] = 'http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.js';
		$this->template->scripts[] = Route::get('media')->uri(array('file'=>'js/markitup/jquery.markitup.js'));
		$this->template->scripts[] = Route::get('media')->uri(array('file'=>'js/markitup/sets/default/set.js'));
		//$style = Route::get('media')->uri(array('file'=>'js/markitup/skins/markitup/style.css'));
		//$this->template->styles[$style] =
		$this->template->styles[Route::get('media')->uri(array('file'=>'js/markitup/skins/markitup/style.css'))] = 'screen';
		$this->template->styles[Route::get('media')->uri(array('file'=>'js/markitup/sets/default/style.css'))] = 'screen';
		$this->template->content = $view;
	}

	/**
	 * List revision history for a page
	 */
	public function action_history() {
		$id = Request::instance()->param('id');
		$page = Sprig::factory('page', array('id'=>$id))->load();

		// If page is invalid, return to list
		if ( ! $page->loaded())
		{
			Message::instance()->error('That page does not exist');
			Request::instance()->redirect( Route::get('admin_cms')->uri() );
		}

		// Restrict access
		if ( ! $this->a2->allowed($page, 'edit'))
		{
			Message::instance()->error('You do not have permission to view revision history for :title.', array(':title'=>$page->title));
			Request::instance()->redirect( Route::get('admin_cms')->uri() );
		}

		$ver1 = isset($_POST['ver1']) ? $_POST['ver1'] : NULL;
		$ver2 = isset($_POST['ver2']) ? $_POST['ver2'] : NULL;

		if ($ver1 !== NULL AND $ver2 !== NULL)
		{
			Request::instance()->redirect( Route::get('admin_cms_diff')->uri(array(
				'action'     => 'diff',
				'id'         => $id,
				'ver1'       => $_POST['ver1'],
				'ver2'       => $_POST['ver2'],
			)) );
		}

		$grid = new Grid;
		$grid->column('radio')->field('version')->title('Version 1')->name('ver1');
		$grid->column('radio')->field('version')->title('Version 2')->name('ver2');
		$grid->column()->field('version')->title('Revision');
		$grid->column()->field('editor')->title('Editor')->member('username');
		$grid->column('date')->field('date')->title('Date');
		$grid->column()->field('comments')->title('Comments')->callback(array($this, 'parse_comments'));
		$grid->link('submit')->text('View Diff')
			->action(Route::get('admin_cms')->uri(array('action'=>'diff')) );
		$grid->link('button')->text('Back to List')
			->action(Route::get('admin_cms')->uri() );
		$grid->data($page->revisions);
		Kohana::$log->add(Kohana::DEBUG, "Made it this far");

		$this->template->content = new View('cms/pages/history');
		$this->template->content->page = $page;
		$this->template->content->grid = $grid;
	}

	/**
	 * Parse comment array as unordered list
	 *
	 * @param   array   comments
	 * @return  string
	 */
	public function parse_comments($comments) {
		$return = '<ul>';
		foreach ($comments as $comment)
		{
			$return .= '<li>'.$comment.'</li>';
		}
		$return .= '</ul>'.PHP_EOL;
		return $return;
	}

	/**
	 * Show inline difference between two versions
	 */
	public function action_diff() {
		$id   = Request::instance()->param('id');
		$ver1 = Request::instance()->param('ver1');
		$ver2 = Request::instance()->param('ver2');
		$page = Sprig::factory('page', array('id'=>$id))->load();

		// If page is invalid, return to list
		if ( ! $page->loaded())
		{
			Message::instance()->error('That page does not exist');
			Request::instance()->redirect( Route::get('admin_cms')->uri() );
		}

		// Restrict access
		if ( ! $this->a2->allowed($page, 'edit'))
		{
			Message::instance()->error('You do not have permission to view revision history for :title.', array(':title'=>$page->title));
			Request::instance()->redirect( Route::get('admin_cms')->uri() );
		}

		$page->version($ver2);
		$new_text = $page->text;
		$page->version($ver1);
		$old_text = $page->text;

		$diff = Versioned::inline_diff($old_text, $new_text);

		$this->template->content = new View('cms/pages/diff');
		$this->template->content->page = $page;
		$this->template->content->diff = $diff;
	}

}

