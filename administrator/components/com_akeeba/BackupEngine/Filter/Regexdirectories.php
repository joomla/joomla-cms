<?php
/**
 * Akeeba Engine
 *
 * @package   akeebaengine
 * @copyright Copyright (c)2006-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Engine\Filter;

defined('AKEEBAENGINE') || die();

/**
 * Directory exclusion filter based on regular expressions
 */
class Regexdirectories extends Base
{
	public function __construct()
	{
		$this->object  = 'dir';
		$this->subtype = 'all';
		$this->method  = 'regex';

		if (empty($this->filter_name))
		{
			$this->filter_name = strtolower(basename(__FILE__, '.php'));
		}

		parent::__construct();
	}
}
