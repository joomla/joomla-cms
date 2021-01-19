<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\CliCommands\MixIt;

defined('_JEXEC') || die;

use Akeeba\Backup\Admin\Model\DatabaseFilters;
use Akeeba\Engine\Factory;
use FOF30\Container\Container;

trait FilterRoots
{
	/**
	 * @param   string  $target
	 *
	 * @return  array
	 *
	 * @since   7.5.0
	 */
	private function getRoots(string $target): array
	{
		$container = Container::getInstance('com_akeeba', [], 'admin');
		$filters   = Factory::getFilters();
		$output    = [];

		switch ($target)
		{
			case 'fs':
				$rootInfo = $filters->getInclusions('dir');

				foreach ($rootInfo as $item)
				{
					$output[] = $item[0];
				}

				break;

			case 'db':
				/** @var DatabaseFilters $model */
				$model    = $container->factory->model('DatabaseFilters')->tmpInstance();
				$rootInfo = $model->get_roots();

				foreach ($rootInfo as $item)
				{
					$output[] = $item->value;
				}

				break;
		}

		return $output;
	}

}
