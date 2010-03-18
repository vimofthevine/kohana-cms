<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Asset management controller
 *
 * @package     Controller
 * @author      Kyle Treubig
 * @copyright   (c) 2010 Kyle Treubig
 * @license     MIT
 */
class Controller_Admin_Asset extends Controller_Template_Admin {

	/**
	 * @var Upload folder
	 */
	private $folder = '';

	/**
	 * Register controller as an admin controller
	 */
	public function before() {
		parent::before();

		$this->restrict('asset', 'manage');
		if ( ! $this->internal_request)
		{
			unset($this->template->menu->menu['Pages'][0]);
		}

		$this->folder = DOCROOT.Kohana::config('cms.upload.folder').'/';
	}

	/**
	 * Default action to read
	 */
	public function action_index() {
		$this->action_read();
	}

	/**
	 * Read and display contents of folder or file
	 */
	public function action_read() {
		$dir = Request::instance()->param('file');
		if (is_dir($this->folder.$dir))
		{
			$this->dir($dir);
		}
		else
		{
			$this->file($dir);
		}
	}

	/**
	 * Show file
	 */
	private function file($file) {
		$this->auto_render = FALSE;
		$request = Request::instance();
		$ext = pathinfo($file, PATHINFO_EXTENSION);
		$path = $this->folder.$file;
		if (is_file($path))
		{
			$request->response = file_get_contents($path);
		}
		else
		{
			Kohana::$log->add(Kohana::ERROR, 'Asset controller error while loading file, '.$file);
			$request->status = 404;
		}

		$request->headers['Content-Type'] = File::mime_by_ext($ext);
	}

	/**
	 * Show contents of folder
	 */
	private function dir($dir) {
		$this->template->content = $this->internal_request
			? new View('cms/files/widget/list')
			: new View('cms/files/list');
		$this->template->content->current_dir = empty($dir) ? 'Root' : $dir;
		$this->template->content->dir = $dir;

		$folders = array();
		$files = array();

		if ( ! empty($dir))
		{
			$slash = strrpos($dir, "/");
			$folders['root_dir']['name'] = '..';
			$folders['root_dir']['path'] = $slash ? substr($dir, 0, $slash) : '';
			$folders['root_dir']['size'] = '';
			$folders['root_dir']['date'] = '';
		}

		$dir = empty($dir) ? $dir : $dir.'/';
		$path = $this->folder.$dir;
		$map = Filesystem::map($path);
		ksort($map);

		foreach ($map as $key=>$value)
		{
			if (is_array($map[$key]))
			{
				$folders[$key]['name'] = $key;
				$folders[$key]['path'] = $dir.$key;
				$folders[$key]['size'] = $this->count_size($map[$key]);
				$folders[$key]['date'] = '';
			}
			else
			{
				$files[$value]['name'] = $value;
				$files[$value]['path'] = $dir.$value;
				$files[$value]['size'] = $this->convert_size(filesize($path.$value));
				$files[$value]['date'] = date("m/d/Y H:i:s", filemtime($path.$value));
			}
		}

		ksort($files);

		$grid = new Grid;
		$grid->column('action')->field('path')->title('File/Folder Name')->display_field('name')
			->action(Route::get('admin_cms_asset')->uri(array('action'=>'read')));
		$grid->column()->field('size')->title('Size');
		$grid->column()->field('date')->title('Date Modified');
		$grid->column('action')->field('path')->title('Actions')->text('Delete')->class('delete')
			->action(Route::get('admin_cms_asset')->uri(array('action'=>'delete')));
		$grid->data($folders);
		$grid->data($files);

		$this->template->content->grid = $grid;
	}

	/**
	 * Create a folder
	 */
	public function action_create() {
		$dir = Request::instance()->param('file');

		// Restrict access
		if ( ! $this->a2->allowed('asset', 'create'))
		{
			Message::instance()->error('You do not have permission to create new folders.');
			Request::instance()->redirect( Route::get('admin_cms_asset')->uri(array('file'=>$dir)) );
		}

		$post = Validate::factory($_POST)
			->rule('directory', 'not_empty')
			->rule('directory', 'alpha_dash')
			->filter('directory', 'trim', array("/"));

		try
		{
			if ($post->check())
			{
				$folder = $post['directory'];
				$directory = empty($dir) ? $dir : $dir.'/';
				$path = $this->folder.$directory.$folder;

				mkdir($path, 0777);
				Message::instance()->info('The folder, :name, has been created.', array(':name'=>$directory.$folder));
				Request::instance()->redirect( Route::get('admin_cms_asset')->uri(array('file'=>$dir)) );
			}
			else
			{
				$view = $this->internal_request
					? new View('cms/files/widget/dir_form')
					: new View('cms/files/create');
				$view->dir = $dir;
				$view->folder = $post['directory'];
				$view->errors = $post->errors('cms');

				$this->template->content = $view;
			}

		}
		catch (Exception $e)
		{
			Kohana::$log->add(Kohana::ERROR, 'An error occured creating folder, '.$path.'. '.$e->getMessage());
			Message::instance()->error('The folder, :name, could not be created.', array(':name'=>$directory.$folder));
			Request::instance()->redirect( Route::get('admin_cms_asset')->uri(array('file'=>$dir)) );
		}
	}

	/**
	 * UPload a file
	 */
	public function action_upload() {
		$dir = Request::instance()->param('file');

		// Restrict access
		if ( ! $this->a2->allowed('asset', 'upload'))
		{
			Message::instance()->error('You do not have permission to upload files.');
			Request::instance()->redirect( Route::get('admin_cms_asset')->uri(array('file'=>$dir)) );
		}

		$post = Validate::factory(array_merge($_POST, $_FILES))
			->rule('name', 'alpha_dash')
			->rules('file', array(
				'Upload::valid' => array(),
				'Upload::not_empty' => array(),
				'Upload::type' => array('Upload::type' => Kohana::config('cms.upload.types')),
				//'Upload::type' => array('Upload::type' => array('jpg','png','gif')),
				'Upload::size' => array('1M'))
			)
			->callback('name', array($this, 'filename_available'));

		$path = $this->folder;
		$path .= empty($dir) ? $dir : $dir.'/';
		$name = '';
		$directory = empty($dir) ? $dir : $dir.'/';

		try
		{
			if ($post->check())
			{
				$ext = $this->get_ext($_FILES['file']['name']);
				$name = empty($_POST['name'])
					? $_FILES['file']['name']
					: $_POST['name'].'.'.$ext;

				Kohana::$log->add(Kohana::DEBUG, "Saving uploaded file to ".$path.$name);
				Upload::save($_FILES['file'], $name, $path, "0777");
				Message::instance()->info('The file, :name, has been created.', array(':name'=>$directory.$name));
				Request::instance()->redirect( Route::get('admin_cms_asset')->uri(array('file'=>$dir)) );
			}
			else
			{
				$view = $this->internal_request
					? new View('cms/files/widget/file_form')
					: new View('cms/files/upload');
				$view->dir = $dir;
				//$view->values = array_intersect_key($post->as_array(), $_POST);
				$view->name = $post['name'];
				$view->errors = $post->errors('cms');

				$this->template->content = $view;
			}
		}
		catch (Exception $e)
		{
			Kohana::$log->add(Kohana::ERROR, 'An error occured creating file, '.$path.$name.'. '.$e->getMessage());
			Message::instance()->error('The file, :name, could not be created.', array(':name'=>$directory.$name));
			Request::instance()->redirect( Route::get('admin_cms_asset')->uri(array('file'=>$dir)) );
		}
	}

	/**
	 * Delete a folder or file
	 */
	public function action_delete() {
		if (isset($_POST['no']))
		{
			Request::instance()->redirect( Route::get('admin_cms_asset')->uri() );
		}

		$dir = Request::instance()->param('file');
		$path = $this->folder.$dir;

		if (is_dir($path))
		{
			// Restrict access
			if ( ! $this->a2->allowed('asset', 'delete_folder'))
			{
				Message::instance()->error('You do not have permission to delete folders.');
				Request::instance()->redirect( Route::get('admin_cms_asset')->uri(array('file'=>$dir)) );
			}

			if (isset($_POST['yes']))
			{
				try {
					$this->delete_dir($dir);
					Message::instance()->info('The folder, :name, has been deleted.', array(':name'=>$dir));
					Request::instance()->redirect( Route::get('admin_cms_asset')->uri() );
				}
				catch (Exception $e)
				{
					Kohana::$log->add(Kohana::ERROR, 'An error occured deleting folder, '.$path.'. '.$e->getMessage());
					Message::instance()->error('The folder, :name, could not be deleted.', array(':name'=>$dir));
					Request::instance()->redirect( Route::get('admin_cms_asset')->uri(array('file'=>$dir)) );
				}
			}

			$view = $this->internal_request
				? new View('cms/files/widget/delete_dir')
				: new View('cms/files/delete');
			$view->dir = $dir;
			$this->template->content = $view;
		}
		else
		{
			// Restrict access
			if ( ! $this->a2->allowed('asset', 'delete_file'))
			{
				Message::instance()->error('You do not have permission to delete files.');
				Request::instance()->redirect( Route::get('admin_cms_asset')->uri(array('file'=>$dir)) );
			}

			if (isset($_POST['yes']))
			{
				try {
					$this->delete_file($dir);
					Message::instance()->info('The file, :name, has been deleted.', array(':name'=>$dir));
					Request::instance()->redirect( Route::get('admin_cms_asset')->uri() );
				}
				catch (Exception $e)
				{
					Kohana::$log->add(Kohana::ERROR, 'An error occured deleting file, '.$path.'. '.$e->getMessage());
					Message::instance()->error('The file, :name, could not be deleted.', array(':name'=>$dir));
					Request::instance()->redirect( Route::get('admin_cms_asset')->uri(array('file'=>$dir)) );
				}
			}

			$view = $this->internal_request
				? new View('cms/files/widget/delete_file')
				: new View('cms/files/delete');
			$view->file = $dir;
			$this->template->content = $view;
		}
	}

	/**
	 * Delete a folder and all of its files
	 */
	private function delete_dir($dir) {
		$path = $this->folder.$dir;
		$handle = opendir($path);
		for (; FALSE !== ($file = readdir($handle));)
		{
			if ($file != "." AND $file != "..")
			{
				$full_path = $path.'/'.$file;
				if (is_dir($full_path))
				{
					$this->delete_dir($dir.'/'.$file);
				}
				else
				{
					unlink($full_path);
				}
			}
		}
		closedir($handle);
		rmdir($path);
	}

	/**
	 * Delete a file
	 */
	private function delete_file($file) {
		$path = $this->folder.$file;
		unlink($path);
	}

	/**
	 * Convert file size to human-readable format
	 *
	 * @param   string  size in bytes
	 * @return  string
	 */
	private function convert_size($bytes) {
		if ($bytes <= 0)
			return '0 Byte';

		$convention = 1000;	// [1000->10^x|1024->2^x]
		$s = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB');
		$e = floor(log($bytes, $convention));
		return round($bytes/pow($convention, $e), 2).' '.$s[$e];
	}

	/**
	 * Count number of files in a folder
	 *
	 * @param   array   folder map array
	 * @return  int
	 */
	private function count_size($array) {
		$count = 0;
		if (is_array($array))
		{
			foreach ($array as $key=>$value)
			{
				if ( ! is_array($value))
				{
					$count++;
				}
				else
				{
					$count = $count + $this->count_size($value);
				}
			}
			return $count;
		}
		return $count;
	}

	/**
	 * Get extension of a given file name
	 *
	 * @param   string  file name
	 * @return  string
	 */
	private function get_ext($file) {
		$segments = explode(".", basename($file));
		return end($segments);
	}

	/**
	 * Check if file name is already used for the current directory
	 *
	 * @param   Validate    validation object
	 * @param   string      field name
	 */
	public function filename_available(Validate $array, $field) {
		$dir = Request::instance()->param('file').'/';
		$path = $this->folder;
		$name = $array[$field];
		$ext = $this->get_ext($_FILES['file']['name']);

		$exists = is_file($path.$dir.$name.'.'.$ext);
		Kohana::$log->add(Kohana::DEBUG, 'Validating new file name '.$path.$dir.$name.'.'.$ext);

		if ( ! empty($name) AND $exists)
		{
			$array->error($field, 'filename_available', array($array[$field]));
		}
	}

}

