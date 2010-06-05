<?php defined('SYSPATH') or die('No direct script access.');

/**
 * File resource model
 *
 * @package     Admin
 * @category    Model
 * @author      Kyle Treubig
 * @copyright   (c) 2010 Kyle Treubig
 * @license     MIT
 */
class Model_Resource_File  extends Model_Resource_Core {

	/**
	 * Upload a file
	 *
	 * @throws  Validate_Exception  when an invalid name/file is given
	 * @throws  Resource_Exception  when an error occurs saving the file
	 * @param   array   Array of post and file data
	 */
	public function upload($data) {
		Kohana::$log->add(Kohana::DEBUG, 'Executing Model_Resource_File::upload');

		$data = Validate::factory($data)
			->rule('filename', 'alpha_dash')
			->rules('file', array(
				'Upload::valid' => array(),
				'Upload::not_empty' => array(),
				'Upload::type' => array(
					'Upload::type' => Kohana::config('cms.upload.types'),
				),
				'Upload::size' => array('1M'))
			)
			->callback('filename', array($this, 'filename_available'));

		if ( ! $data->check())
		{
			$this->name = $data['filename'];
			throw new Validate_Exception($data);
		}

		$this->name = empty($data['filename'])
			? $data['file']['name']
			: $data['filename'].'.'.$this->get_ext($data['file']['name']);
		Kohana::$log->add(Kohana::DEBUG, 'Saving uploaded file to '
			.$this->path.'/'.$this->name);

		try
		{
			Upload::save($data['file'], $this->name, $this->path, "0777");
			$this->path .= '/'.$this->name;
		}
		catch (Exception $e)
		{
			Kohana::$log->add(Kohana::ERROR, 'Exception encountered uploading file to '
				.$this->path.'/'.$this->name.'. '.$e->getMessage());
			throw new Resource_Exception('Unable to upload file :name',
				array(':name' => $this));
		}
	}

	/**
	 * Get file info
	 *
	 * @throws  Resource_Exception  when an error occurs reading the file info
	 * @return  array   File info
	 */
	public function info() {
		Kohana::$log->add(Kohana::DEBUG, 'Executing Model_Resource_File::info');

		if ($this->name == '')
		{
			throw new Resource_Exception('A file must be specified to read.');
		}

		try
		{
			return stat($this->path);
		}
		catch (Exception $e)
		{
			Kohana::$log->add(Kohana::ERROR, 'Exception encountered retrieving file info for '
				.$this->path.'. '.$e->getMessage());
			throw new Resource_Exception('Unable to retrieve file info for :name',
				array(':name' => $this));
		}
	}

	/**
	 * Rename a file
	 *
	 * @throws  Validate_Exception  when an invalid name is given
	 * @throws  Resource_Exception  when an error occurs renaming the file
	 * @param   string  New file name
	 */
	public function rename($name) {
		Kohana::$log->add(Kohana::DEBUG, 'Executing Model_Resource_File::rename');

		$data = Validate::factory(array('filename' => $name))
			->rules('filename', array(
				'not_empty'  => NULL,
				'alpha_dash' => NULL,
			))
			->callback('filename', array($this, 'filename_available'));

		if ( ! $data->check())
		{
			$this->name = $data['filename'];
			throw new Validate_Exception($data);
		}

		$new_name = $data['filename'].'.'.$this->get_ext($data['file']['name']);
		$new_path = strtr($this->path, array($this->name, $new_name));
		Kohana::$log->add(Kohana::DEBUG, 'Renaming :name1 to :name2', array(
			':name1' => $this->path,
			':name2' => $new_path,
		));

		try
		{
			rename($this->path, $new_path);
			$this->path = $new_path;
		}
		catch (Exception $e)
		{
			Kohana::$log->add(Kohana::ERROR, 'Exception encountered renaming file from '
				.$this->path.' to '.$newpath.'. '.$e->getMessage());
			throw new Resource_Exception('Unable to rename :name1 to :name2', array(
				':name1' => $this->parent.'/'.$this->name,
				':name2' => $this->parent.'/'.$new_name,
			));
		}
	}

	/**
	 * Delete the file
	 *
	 * @throws  Resource_Exception  when an error occurs
	 */
	public function delete() {
		Kohana::$log->add(Kohana::DEBUG, 'Executing Model_Resource_File::delete');

		if ($this->name == '')
		{
			throw new Resource_Exception('A file must be specified to delete');
		}

		try
		{
			unlink($this->path);
		}
		catch (Exception $e)
		{
			Kohana::$log->add(Kohana::ERROR, 'Exception encountered deleting '
				.$this->path.'. '.$e->getMessage());
			throw new Resource_Exception('Unable to delete file :name',
				array(':name' => $this));
		}
	}

	/**
	 * Magic __toString() function to print current directory
	 */
	public function __toString() {
		return trim($this->parent.'/'.$this->name, '/');
	}

	/**
	 * Callback to check if a given file name is already used
	 * in the current directory
	 *
	 * @param   Validate    validation object
	 * @param   string      field name
	 */
	public function filename_available(Validate $array, $field) {
		$ext = $this->get_ext($array['file']['name']);
		$name = $array[$field].'.'.$ext;
		$path = $this->path.'/'.$name;

		Kohana::$log->add(Kohana::DEBUG, 'Validating new file name '.$path);
		$exists = is_file($path);

		if ( ! empty($array[$field]) AND $exists)
		{
			Kohana::$log->add(Kohana::INFO, 'Upload filename given already exists. '.$path);
			$array->error($field, 'filename_available', array($array[$field]));
		}
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

}	// End of Model_Resource_File

