<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Folder resource model
 *
 * @package     Admin
 * @category    Model
 * @author      Kyle Treubig
 * @copyright   (c) 2010 Kyle Treubig
 * @license     MIT
 */
class Model_Resource_Folder extends Model_Resource_Core {

	/**
	 * @var array   Files contained by folder
	 */
	public $files = array();

	/**
	 * @var array   Subfolders of the folder
	 */
	public $folders = array();

	/**
	 * Create a new folder
	 *
	 * @throws  Validate_Exception  when an invalid name is given
	 * @throws  Resource_Exception  when an error occurs creating the folder
	 * @param   string  Directory name
	 */
	public function create($name) {
		Kohana::$log->add(Kohana::DEBUG, 'Executing Model_Resource_Folder::create');

		$data = Validate::factory(array('directory' => $name))
			->filter('directory', 'trim', array('/'))
			->rule('directory', 'not_empty')
			->rule('directory', 'alpha_dash');

		if ( ! $data->check())
		{
			$this->name = $data['directory'];
			throw new Validate_Exception($data);
		}

		$this->path .= '/'.$data['directory'];
		$this->name = $data['directory'];

		try
		{
			mkdir($this->path, 0777);
		}
		catch (Exception $e)
		{
			Kohana::$log->add(Kohana::ERROR, 'Exception encountered creating folder '
				.$this->path.'. '.$e->getMessage());
			throw new Resource_Exception('Unable to create folder :name',
				array(':name' => $this));
		}
	}

	/**
	 * Read contents of the directory
	 *
	 * @throws  Resource_Exception  when an error occurs reading the folder
	 */
	public function read_contents() {
		Kohana::$log->add(Kohana::DEBUG, 'Executing Model_Resource_Folder::read_contents');

		// If not in root directory, display parent folder
		if ( ! empty($this->name))
		{
			$slash = strrpos($this->name, "/");
			$this->folders['root_dir']['name'] = '..';
			$this->folders['root_dir']['path'] = $this->parent;
			$this->folders['root_dir']['size'] = '';
			$this->folders['root_dir']['date'] = '';
		}

		// Get folder contents
		$map  = Filesystem::map($this->path);
		ksort($map);

		// Process folder contents
		foreach ($map as $key=>$value)
		{
			if (is_array($map[$key]))
			{
				$this->folders[$key]['name'] = $key;
				$this->folders[$key]['path'] = trim($this->parent.'/'.$this->name.'/'.$key, '/');
				$this->folders[$key]['size'] = $this->count_size($map[$key]);
				$this->folders[$key]['date'] = '';
			}
			else
			{
				$this->files[$value]['name'] = $value;
				$this->files[$value]['path'] = trim($this->parent.'/'.$this->name.'/'.$value, '/');
				$this->files[$value]['size'] = $this->convert_size(filesize($this->path.'/'.$value));
				$this->files[$value]['date'] = date("Y-m-d H:i:s", filemtime($this->path.'/'.$value));
			}
		}
		ksort($this->files);
	}

	/**
	 * Delete the folder
	 *
	 * @throws  Resource_Exception  when an error occurs
	 */
	public function delete() {
		Kohana::$log->add(Kohana::DEBUG, 'Executing Model_Resource_File::delete');

		// Make sure a folder is specified (cannot delete root folder)
		if ( ! trim($this->parent.'/'.$this->name, '/'))
		{
			throw new Resource_Exception('A folder must be specified to delete');
		}

		try
		{
			$this->recursive_delete($this->path);
		}
		catch (Exception $e)
		{
			Kohana::$log->add(Kohana::ERROR, 'Exception encountered deleting '
				.$this->path.'. '.$e->getMessage());
			throw new Resource_Exception('Unable to delete folder :name',
				array(':name' => $this));
		}
	}

	/**
	 * Recursively delete a directory and its contents
	 *
	 * @param   string  directory path
	 */
	private function recursive_delete($path) {
		if (is_file($path))
			return unlink($path);
		elseif (is_dir($path))
		{
			$scan = glob(rtrim($path.'/').'/*');
			foreach ($scan as $index => $str)
			{
				$this->recursive_delete($str);
			}
			return rmdir($path);
		}
	}

	/**
	 * Magic __toString() function to print current directory
	 */
	public function __toString() {
		return empty($this->name) ? 'Root' : trim($this->parent.'/'.$this->name, '/');
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

}	// End of Model_Resource_Folder

