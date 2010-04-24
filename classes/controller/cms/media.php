<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Media controller
 *
 * @package     CMS
 * @author      Kyle Treubig
 * @copyright   (c) 2010 Kyle Treubig
 * @license     MIT
 */
class Controller_Cms_Media extends Controller {

	/**
	 * Return a file from the media folder in the cascading file system
	 */
	public function action_file() {
		Kohana::$log->add(Kohana::DEBUG, 'Executing Controller_Cms_Media::action_file');

		$request = Request::instance();
		$file = $request->param('file');

		$ext = pathinfo($file, PATHINFO_EXTENSION);
		$file = substr($file, 0, -(strlen($ext) + 1));

		if ($file = Kohana::find_file('media', $file, $ext))
		{
			$request->response = file_get_contents($file);
		}
		else
		{
			Kohana::$log->add(Kohana::ERROR, 'Media controller error while loading file, '.$file);
			$request->status = 404;
		}

		$request->headers['Content-Type'] = File::mime_by_ext($ext);
	}

	/**
	 * Return a php file from the media/css folder, with the css mime type
	 */
	public function action_css() {
		Kohana::$log->add(Kohana::DEBUG, 'Executing Controller_Cms_Media::action_css');

		$request = Request::instance();
		$file = $request->param('file');

		$ext = pathinfo($file, PATHINFO_EXTENSION);
		$file = substr($file, 0, -(strlen($ext) + 1));

		if ($file = Kohana::find_file('media/css', $file, 'php'))
		{
			$request->response = require $file;
		}
		else
		{
			Kohana::$log->add(Kohana::ERROR, 'Media controller error while loading css file, '.$file);
			$request->status = 404;
		}

		$request->headers['Content-Type'] = File::mime_by_ext('css');
	}

}

