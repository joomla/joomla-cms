<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\Controller;

// Protect from unauthorized access
use FOF30\Container\Container;

defined('_JEXEC') || die();

/**
 * File Filters controller
 */
class FileFilters extends DatabaseFilters
{
	/**
	 * Overridden FileFilters constructor. Sets the correct model and view names.
	 *
	 * @param   Container  $container  The component's container
	 * @param   array      $config     Optional configuration overrides
	 */
	public function __construct(Container $container, array $config)
	{
		if (!is_array($config))
		{
			$config = [];
		}

		$config = array_merge([
			'modelName'	=> 'FileFilters',
			'viewName'	=> 'FileFilters'
		], $config);

		parent::__construct($container, $config);
	}

}
