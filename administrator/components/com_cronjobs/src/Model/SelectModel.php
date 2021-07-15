<?php
/**
 * Declares the SelectModel MVC Model.
 *
 * @package       Joomla.Administrator
 * @subpackage    com_cronjobs
 *
 * @copyright (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GPL v3
 */

namespace Joomla\Component\Cronjobs\Administrator\Model;

// Restrict direct access
defined('_JEXEC') or die;

use Exception;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Event\AbstractEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Plugin\PluginHelper;
use function defined;

/**
 * MVC Model for SelectView
 *
 * @since __DEPLOY_VERSION__
 */
class SelectModel extends ListModel
{
	/**
	 * @var CMSApplication
	 * @since __DEPLOY_VERSION__
	 */
	protected $app;


	/**
	 * SelectModel constructor.
	 *
	 * @throws Exception
	 * @since __DEPLOY_VERSION__
	 */
	public function __construct()
	{
		$this->app = Factory::getApplication();
		parent::__construct();
	}

	/**
	 *
	 * @return array  An array of items
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function getItems(): array
	{
		// TODO : Implement an object for $items
		$items = [];
		PluginHelper::importPlugin('job');
		$this->app->getDispatcher()->dispatch('onCronOptionsList',
			AbstractEvent::create(
				'onCronOptionsList',
				[
					'eventClass' => 'Joomla\Component\Cronjobs\Administrator\Model\SelectModel',
					'subject' => &$items
				]
			)
		);

		return $items;
	}
}
