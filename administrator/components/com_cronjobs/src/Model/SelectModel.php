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
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Component\Cronjobs\Administrator\Cronjobs\CronOptions;
use Joomla\Component\Cronjobs\Administrator\Cronjobs\CronOption;
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
	 * @param   array                 $config   An array of configuration options (name, state, dbo, table_path, ignore_request).
	 * @param   ?MVCFactoryInterface  $factory  The factory.
	 *
	 * @throws Exception
	 * @since __DEPLOY_VERSION__
	 */
	public function __construct($config = array(), ?MVCFactoryInterface $factory = null)
	{
		$this->app = Factory::getApplication();
		parent::__construct($config, $factory);
	}

	/**
	 *
	 * @return CronOption[]  An array of CronOption objects
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function getItems(): array
	{
		$options = new CronOptions;
		$event = AbstractEvent::create(
			'onCronOptionsList',
			[
				'subject' => $options
			]
		);

		// TODO : Implement an object for $items
		PluginHelper::importPlugin('job');
		$this->app->getDispatcher()->dispatch('onCronOptionsList', $event);

		return $options->jobs;
	}
}
