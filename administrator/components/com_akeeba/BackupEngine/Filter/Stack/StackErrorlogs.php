<?php
/**
 * Akeeba Engine
 *
 * @package   akeebaengine
 * @copyright Copyright (c)2006-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Engine\Filter\Stack;

defined('AKEEBAENGINE') || die();

use Akeeba\Engine\Filter\Base;

/**
 * Files exclusion filter based on regular expressions
 */
class StackErrorlogs extends Base
{
	function __construct()
	{
		$this->object  = 'file';
		$this->subtype = 'all';
		$this->method  = 'api';

		if (empty($this->filter_name))
		{
			$this->filter_name = strtolower(basename(__FILE__, '.php'));
		}

		parent::__construct();
	}

	protected function is_excluded_by_api($test, $root)
	{
		// Is it an error log? Exclude the file.
		if (in_array(basename($test), [
			'php_error',
			'php_errorlog',
			'error_log',
			'error.log',
		]))
		{
			return true;
		}

		// No match? Just include the file!
		return false;
	}

}
