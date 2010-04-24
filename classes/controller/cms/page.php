<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Page controller
 *
 * @package     CMS
 * @author      Kyle Treubig
 * @copyright   (c) 2010 Kyle Treubig
 * @license     MIT
 */
class Controller_Cms_Page extends Controller_Template_Website {

	/**
	 * Load pages from database, static view files,
	 * or display 404 error page.
	 */
	public function action_load() {
		Kohana::$log->add(Kohana::DEBUG, 'Executing Controller_Cms_Page::action_load');

		$page = Request::instance()->param('page');
		$page = Security::xss_clean($page);

		// Check if page is in cache
		if (Kohana::$caching === TRUE AND ($file = Kohana::cache('page_'.$page)))
		{
			$this->template->content = $file;
			return;
		}

		// Default values
		$contents = NULL;
		$found = FALSE;

		// Check if page is in database
		$db = DB::select('title','text')
			->from('pages')
			->where('slug','=',$page)
			->execute();

		if ($db->count() == 1)
		{
			$contents = $db->current();
			$contents = $contents['text'];
			$found = TRUE;
		}
		// Check if page is a static view
		else if (Kohana::find_file('views', 'static/'.$page))
		{
			$contents = new View('static/'.$page);
			$found = TRUE;
		}
		// Show 404
		else
		{
			Kohana::$log->add(Kohana::ERROR, 'Page controller error loading non-existent page, '.$page);
			$contents = new View('errors/404');
		}

		if (Kohana::$caching === TRUE AND $found)
		{
			Kohana::cache('page_'.$page, $contents);
		}

		$this->template->content = $contents;
	}
}

