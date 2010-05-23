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
		unset($this->template->menu->menu['Pages'][0]);
	}

	/**
	 * Default action to list
	 */
	public function action_index() {
		$this->action_list();
	}

	/**
	 * Generate menu for page management
	 */
	private function menu() {
		return View::factory('cms/pages/menu');
	}

	/**
	 * Display menu for page management
	 */
	public function action_menu() {
		if ( ! $this->internal_request)
		{
			Request::instance()->redirect(Route::get('admin_main')->uri(array('controller'=>'page')));
		}

		$this->template->content = new View('cms/pages/menu');
	}

	/**
	 * Display list of pages
	 */
	public function action_list() {
		Kohana::$log->add(Kohana::DEBUG, 'Executing Controller_Admin_Page::action_list');

		$pages = Sprig::factory('page')->load(NULL, FALSE);

		// Check if there are any pages to display
		if (count($pages) == 0)
		{
			$hmvc = View::factory('cms/pages/hmvc/none');

			$view = View::factory('cms/pages/list')
				->set('menu', $this->menu())
				->set('list', $hmvc);

			$this->template->content = $this->internal_request ? $hmvc : $view;
			return;
		}

		// Create page list
		$grid = new Grid;
		$grid->column()->field('id')->title('ID');
		$grid->column()->field('title')->title('Title');
		$grid->column()->field('version')->title('Ver');
		$grid->column('action')->title('Edit')->text('Edit')->class('edit')
			->route(Route::get('admin_main'))->params(array('controller'=>'page', 'action'=>'edit'));
		$grid->column('action')->title('Hist')->text('History')->class('history')
			->route(Route::get('admin_main'))->params(array('controller'=>'page', 'action'=>'history'));
		$grid->data($pages);

		// Setup HMVC view with data
		$hmvc = View::factory('cms/pages/hmvc/list')
			->set('grid', $grid);

		// Setup template view
		$view = View::factory('cms/pages/list')
			->set('menu', $this->menu())
			->set('list', $hmvc);

		// Set request response
		$this->template->content = $this->internal_request ? $hmvc : $view;
	}

	/**
	 * Page list for dashboard (widget-ized)
	 */
	public function action_list_widget() {
		Kohana::$log->add(Kohana::DEBUG, 'Executing Controller_Admin_Page::action_list_widget');

		if ( ! $this->internal_request)
		{
			Request::instance()->redirect( Route::get('admin_main')->uri(array('controller'=>'page')) );
		}

		$pages = Sprig::factory('page')->load(NULL, FALSE);

		// Check if there are any pages to display
		if (count($pages) == 0)
		{
			$hmvc = View::factory('cms/pages/hmvc/none');

			$view = View::factory('cms/pages/list')
				->set('menu', $this->menu())
				->set('list', $hmvc);

			$this->template->content = $this->internal_request ? $hmvc : $view;
			return;
		}

		// Create page list
		$grid = new Grid;
		$grid->column()->field('title')->title('Title');
		$grid->column()->field('version')->title('Ver');
		$grid->column('action')->title('Edit')->text('Edit')->class('edit')
			->route(Route::get('admin_main'))->params(array('controller'=>'page', 'action'=>'edit'));
		$grid->data($pages);

		// Set request response
		$this->template->content = View::factory('cms/pages/hmvc/list')
			->set('grid', $grid);
	}

	/**
	 * Page editor
	 */
	public function action_edit() {
		Kohana::$log->add(Kohana::DEBUG, 'Executing Controller_Admin_Page::action_edit');

		$id = Request::instance()->param('id');
		$page = Sprig::factory('page', array('id'=>$id))->load();

		// If page is invalid, return to list
		if ( ! $page->loaded())
		{
			$message = __('That page does not exist');

			// Return message if an ajax request
			if (Request::$is_ajax)
			{
				$this->template->content = $message;
			}
			// Else set flash message and redirect
			else
			{
				Message::instance()->error($message);
				Request::instance()->redirect( Route::get('admin_main')->uri(array('controller'=>'page')) );
			}
		}

		// Restrict access
		if ( ! $this->a2->allowed($page, 'edit'))
		{
			$message = __('You do not have permission to modify :title.', array(':title'=>$page->title));

			// Return message if an ajax request
			if (Request::$is_ajax)
			{
				$this->template->content = $message;
			}
			// Else set flash message and redirect
			else
			{
				Message::instance()->error($message);
				Request::instance()->redirect( Route::get('admin_main')->uri(array('controller'=>'page')) );
			}
		}

		$page->values($_POST);
		$page->editor = $this->a1->get_user()->id;

		// Setup HMVC view with data
		$hmvc = View::factory('cms/pages/hmvc/form')
			->set('legend', __('Edit').' "'.$page->title.'"')
			->set('submit', __('Save'))
			->set('page', $page);

		if (count($_POST))
		{
			try
			{
				$page->update();
				$message = __('The page, :title, has been updated.', array(':title'=>$page->title));

				// Return message if an ajax request
				if (Request::$is_ajax)
				{
					$this->template->content = $message;
				}
				// Else set flash message and redirect
				else
				{
					Message::instance()->info($message);
					Request::instance()->redirect( Route::get('admin_main')->uri(array('controller'=>'page')) );
				}
			}
			catch (Validate_Exception $e)
			{
				$hmvc->errors = count($_POST) ? $e->array->errors('cms') : array();
			}
		}

		// Set template scripts and styles
		$this->template->scripts[] = 'http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.js';
		$this->template->scripts[] = Route::get('media')->uri(array('file'=>'js/markitup/jquery.markitup.js'));
		$this->template->scripts[] = Route::get('media')->uri(array('file'=>'js/markitup/sets/html/set.js'));
		$this->template->styles[Route::get('media')->uri(array('file'=>'js/markitup/skins/markitup/style.css'))] = 'screen';
		$this->template->styles[Route::get('media')->uri(array('file'=>'js/markitup/sets/html/style.css'))] = 'screen';

		// Setup template view
		$view = View::factory('cms/pages/form')
			->set('menu', $this->menu())
			->set('form', $hmvc);

		// Set request response
		$this->template->content = $this->internal_request ? $hmvc : $view;
	}

	/**
	 * List revision history for a page
	 */
	public function action_history() {
		Kohana::$log->add(Kohana::DEBUG, 'Executing Controller_Admin_Page::action_history');

		$id = Request::instance()->param('id');
		$page = Sprig::factory('page', array('id'=>$id))->load();

		// If page is invalid, return to list
		if ( ! $page->loaded())
		{
			$message = __('That page does not exist');

			// Return message if an ajax request
			if (Request::$is_ajax)
			{
				$this->template->content = $message;
			}
			// Else set flash message and redirect
			else
			{
				Message::instance()->error($message);
				Request::instance()->redirect( Route::get('admin_main')->uri(array('controller'=>'page')) );
			}
		}

		// Restrict access
		if ( ! $this->a2->allowed($page, 'edit'))
		{
			$message = __('You do not have permission to view revision history for :title.', array(':title'=>$page->title));

			// Return message if an ajax request
			if (Request::$is_ajax)
			{
				$this->template->content = $message;
			}
			// Else set flash message and redirect
			else
			{
				Message::instance()->error($message);
				Request::instance()->redirect( Route::get('admin_main')->uri(array('controller'=>'page')) );
			}
		}

		$ver1 = isset($_POST['ver1']) ? $_POST['ver1'] : NULL;
		$ver2 = isset($_POST['ver2']) ? $_POST['ver2'] : NULL;

		if ($ver1 !== NULL AND $ver2 !== NULL)
		{
			Request::instance()->redirect( Route::get('admin_cms_diff')->uri(array(
				'id'         => $id,
				'ver1'       => $_POST['ver1'],
				'ver2'       => $_POST['ver2'],
			)) );
		}

		// Create revision list
		$grid = new Grid;
		$grid->column('radio')->field('version')->title('Version 1')->name('ver1');
		$grid->column('radio')->field('version')->title('Version 2')->name('ver2');
		$grid->column()->field('version')->title('Revision');
		$grid->column()->field('editor')->title('Editor')->callback(array($this, 'print_username'));
		$grid->column('date')->field('date')->title('Date');
		$grid->column()->field('comments')->title('Comments')->callback(array($this, 'parse_comments'));
		$grid->link('submit')->text('View Diff')
			->action(Route::get('admin_main')->uri(array('controller'=>'page', 'action'=>'diff')) );
		$grid->data($page->revisions);

		// Setup HMVC view with data
		$hmvc = View::factory('cms/pages/hmvc/history')
			->set('page', $page)
			->set('grid', $grid);

		// Setup template view
		$view = View::factory('cms/pages/history')
			->set('menu', $this->menu())
			->set('history', $hmvc);

		// Set request response
		$this->template->content = $this->internal_request ? $hmvc : $view;
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
	 * Print username callback
	 *
	 * @param   object  user
	 * @return  string
	 */
	public function print_username($user) {
		if ( ! $user->loaded())
		{
			$user->load();
		}
		return $user->username;
	}

	/**
	 * Show inline difference between two versions
	 */
	public function action_diff() {
		Kohana::$log->add(Kohana::DEBUG, 'Executing Controller_Admin_Page::action_diff');

		$id   = Request::instance()->param('id');
		$ver1 = Request::instance()->param('ver1');
		$ver2 = Request::instance()->param('ver2');
		$page = Sprig::factory('page', array('id'=>$id))->load();

		// If page is invalid, return to list
		if ( ! $page->loaded())
		{
			$message = __('That page does not exist');

			// Return message if an ajax request
			if (Request::$is_ajax)
			{
				$this->template->content = $message;
			}
			// Else set flash message and redirect
			else
			{
				Message::instance()->error($message);
				Request::instance()->redirect( Route::get('admin_main')->uri(array('controller'=>'page')) );
			}
		}

		// Restrict access
		if ( ! $this->a2->allowed($page, 'edit'))
		{
			$message = __('You do not have permission to view revision history for :title.', array(':title'=>$page->title));

			// Return message if an ajax request
			if (Request::$is_ajax)
			{
				$this->template->content = $message;
			}
			// Else set flash message and redirect
			else
			{
				Message::instance()->error($message);
				Request::instance()->redirect( Route::get('admin_main')->uri(array('controller'=>'page')) );
			}
		}

		// Get versions of the text
		$page->version($ver2);
		$new_text = $page->text;
		$page->version($ver1);
		$old_text = $page->text;

		$diff = Versioned::inline_diff($old_text, $new_text);

		// Setup HMVC view with data
		$hmvc = View::factory('cms/pages/hmvc/diff')
			->set('page', $page)
			->set('ver1', $ver1)
			->set('ver2', $ver2)
			->set('diff', $diff);

		// Setup template view
		$view = View::factory('cms/pages/diff')
			->set('menu', $this->menu())
			->set('diff', $hmvc);

		// Set request response
		$this->template->content = $this->internal_request ? $hmvc : $view;
	}

}	// End of Controller_Admin_Page

