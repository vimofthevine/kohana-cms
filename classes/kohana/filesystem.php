<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Filesystem Library
 *
 * @package     Kohana
 * @category    Helpers
 * @author      ExpressionEngine Dev Team
 * @copyright   Copyright (c) 2008, EllisLab, Inc.
 * @license     http://codeigniter.com/user_guide/license.html
 */
class Kohana_Filesystem {

	/**
	 * Create a Directory Map
	 *
	 * Reads the specified directory and builds an array
	 * representation of it.  Sub-folders contained with the
	 * directory will be mapped as well.
	 *
	 * @access  public
	 * @param   string  path to source
	 * @param   bool    whether to limit the result to the top level only
	 * @return  array
	 */
	public static function map($source_dir, $top_level_only = FALSE) {
		if ($fp = @opendir($source_dir))
		{
			$source_dir = rtrim($source_dir, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
			$filedata = array();

			while (($file = readdir($fp)) !== FALSE)
			{
				if (strncmp($file, '.', 1) == 0)
				{
					continue;
				}

				if ($top_level_only == FALSE && @is_dir($source_dir.$file))
				{
					$temp_array = array();
					$temp_array = self::map($source_dir.$file.DIRECTORY_SEPARATOR);
					$filedata[$file] = $temp_array;
				}
				else
				{
					$filedata[] = $file;
				}
			}

			closedir($fp);
			return $filedata;
		}
	}

}

