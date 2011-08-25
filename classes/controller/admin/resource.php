<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Resource management controller
 *
 * @package     Admin
 * @category    Controller
 * @author      Kyle Treubig
 * @copyright   (c) 2010 Kyle Treubig
 * @license     MIT
 */
class Controller_Admin_Resource extends Controller_Admin {

	protected $_resource = 'asset';

	protected $_acl_map = array(
		'create'  => 'create',
		'upload'  => 'upload',
		'delete'  => 'delete',
		'default' => 'manage',
	);

	protected $_acl_required = 'all';

	protected $_view_map = array(
		'create'  => 'admin/layout/narrow_column',
		'upload'  => 'admin/layout/narrow_column',
		'read'    => 'admin/layout/wide_column_with_menu',
		'default' => 'admin/layout/wide_column',
	);

	protected $_view_menu_map = array(
		'read'    => 'cms/menu/read',
	);

	protected $_current_nav = 'admin/resource';

	private $_base;

	/**
	 * Get upload base
	 */
	public function before() {
		parent::before();
		$this->_base = DOCROOT.Kohana::config('cms.upload.folder').'/';
	}

	/**
	 * Generate menu for asset management
	 */
	protected function _menu() {
		return View::factory('cms/menu/default');
	}

	/**
	 * Redirect index action to list
	 */
	public function action_index() {
		$this->request->redirect( $this->request->uri(
			array('action' => 'list')), 301);
	}

	/**
	 * Read and display contents of folder or file
	 */
	public function action_read() {
		Kohana::$log->add(Kohana::DEBUG, 'Executing Controller_Admin_Asset::action_read');

		$dir = $this->request->param('path');
		if (is_dir($this->_base.$dir))
		{
			$this->dir($dir);
		}
		else
		{
			$this->file($dir);
		}
	}

	/**
	 * Display a specified file
	 */
	private function file($file) {
		Kohana::$log->add(Kohana::DEBUG, 'Executing Controller_Admin_Asset::file');

		// If file exists, redirect to file
		if (is_file($this->_base.$file))
		{
			$url = URL::site(Kohana::config('cms.upload.folder').'/'.$file, TRUE);
			$this->request->redirect($url, 301);
		}
		else
		{
			Kohana::$log->add(Kohana::ERROR, 'Asset controller error while loading file, '.$file);
			$this->request->status = 404;
			$this->template->content = View::factory('errors/404');
		}
	}

	/**
	 * Show contents of folder
	 *
	 * @param   string  folder path
	 */
	private function dir($dir) {
		Kohana::$log->add(Kohana::DEBUG, 'Executing Controller_Admin_Asset::dir');
		$this->template->content = View::factory('cms/files/directory_contents');
	
		// $folder is binded in global mode to be readable from 'content' and 'menu'
		$this->template->bind_global('folder', $folder);

		$folder = new Model_Resource_Folder($dir);
		$folder->read_contents();
	}

	/**
	 * Create a folder
	 */
	public function action_create() {
		Kohana::$log->add(Kohana::DEBUG, 'Executing Controller_Admin_Asset::action_create');
		$this->template->content = View::factory('cms/files/create_directory')
			->bind('folder', $folder)
			->bind('errors', $errors);

		$folder = new Model_Resource_Folder($this->request->param('path'), TRUE);

		if ($_POST)
		{
			try
			{
				$folder->create($_POST['directory']);

				Message::instance()->info('The folder, :name, has been created.',
					array(':name' => $folder));

				if ( ! $this->_internal)
					$this->request->redirect( $this->request->uri(array(
						'action' => 'read',
						'path' => $folder->parent.'/'.$folder->name,
					)) );
			}
			catch (Validate_Exception $ve)
			{
				$errors = $ve->array->errors('admin');
			}
			catch (Resource_Exception $e)
			{
				Message::instance()->error('The folder, :name, could not be created.',
					array(':name' => $folder));
				if ( ! $this->_internal)
					$this->request->redirect( $this->request->uri(
						array('action'=>'read', 'path'=>$folder->parent)) );
			}
		}
	}

	/**
	 * Upload a file
	 */
	public function action_upload() {
		Kohana::$log->add(Kohana::DEBUG, 'Executing Controller_Admin_Asset::action_upload');
		$this->template->content = View::factory('cms/files/upload_file')
			->bind('file', $file)
			->bind('errors', $errors);

		$file = new Model_Resource_File($this->request->param('path'), TRUE);

		if ($_POST)
		{
			try
			{
				$file->upload(array_merge($_FILES, $_POST));

				Message::instance()->info('The file, :name, has been created.',
					array(':name' => $file));

				if ( ! $this->_internal)
					$this->request->redirect( $this->request->uri(
						array('action' => 'read', 'path' => $file->parent)) );
			}
			catch (Validate_Exception $ve)
			{
				$errors = $ve->array->errors('admin');
			}
			catch (Resource_Exception $e)
			{
				Message::instance()->error('The file, :name, could not be created.',
					array(':name' => $file));
				if ( ! $this->_internal)
					$this->request->redirect( $this->request->uri(
						array('action'=>'read', 'path'=>$file->parent)) );
			}
		}
	}

	/**
	 * Delete a folder or file
	 */
	public function action_delete() {
		Kohana::$log->add(Kohana::DEBUG, 'Executing Controller_Admin_Asset::action_delete');

		$dir = $this->request->param('path');
		$obj = new Model_Resource_File($dir);

		if (isset($_POST['no']))
		{
			Request::instance()->redirect( Route::get('admin/resource')
				->uri(array('path' => $obj->parent)) );
		}

		$this->template->content = View::factory('cms/files/delete')
			->bind('resource', $resource)
			->bind('legend', $legend);

		if (is_dir($obj->path))
		{
			$resource = new Model_Resource_Folder($dir);
			$legend   = __('Delete :name folder?', array(':name' => $dir));

			if (isset($_POST['yes']))
			{
				try {
					$resource->delete();

					Message::instance()->info('The folder, :name, has been deleted.',
						array(':name' => $resource));

					if ( ! $this->_internal)
						$this->request->redirect( $this->request->uri(
							array('action'=>'read', 'path'=>$resource->parent)) );
				}
				catch (Resource_Exception $e)
				{
					Message::instance()->error('The folder, :name, could not be deleted.',
						array(':name' => $resource));

					if ( ! $this->_internal)
						$this->request->redirect( $this->request->uri(
							array('action'=>'read', 'path'=>$resource->parent)) );
				}
			}
		}
		else
		{
			$resource = new Model_Resource_File($dir);
			$legend   = __('Delete :name file?', array(':name' => $dir));

			if (isset($_POST['yes']))
			{
				try {
					$resource->delete();

					Message::instance()->info('The file, :name, has been deleted.',
						array(':name' => $resource));

					if ( ! $this->_internal)
						$this->request->redirect( $this->request->uri(
							array('action'=>'read', 'path'=>$resource->parent)) );
				}
				catch (Resource_Exception $e)
				{
					Message::instance()->error('The file, :name, could not be deleted.',
						array(':name' => $resource));

					if ( ! $this->_internal)
						$this->request->redirect( $this->request->uri(
							array('action'=>'read', 'path'=>$resource->parent)) );
				}
			}
		}
	}

}	// End of Controller_Admin_Resource

