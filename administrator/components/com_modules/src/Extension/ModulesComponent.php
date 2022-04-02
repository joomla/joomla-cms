<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Modules\Administrator\Extension;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Extension\BootableExtensionInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\HTML\HTMLRegistryAwareTrait;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Workflow\WorkflowServiceInterface;
use Joomla\CMS\Workflow\WorkflowServiceTrait;
use Joomla\Component\Modules\Administrator\Service\HTML\Modules;
use Psr\Container\ContainerInterface;

/**
 * Component class for com_modules
 *
 * @since  4.0.0
 */
class ModulesComponent extends MVCComponent implements BootableExtensionInterface,
WorkflowServiceInterface
{
	use HTMLRegistryAwareTrait;
	use WorkflowServiceTrait;

	/**
	 * @var array
	 * @since  __DEPLOY_VERSION_
	 */
	protected $supportedFunctionality = [
		'core.state' => true,
	];

	/**
	 * The trashed condition
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	const CONDITION_NAMES = [
		self::CONDITION_PUBLISHED   => 'JPUBLISHED',
		self::CONDITION_UNPUBLISHED => 'JUNPUBLISHED',
		self::CONDITION_TRASHED     => 'JTRASHED',
	];

	/**
	 * The published condition
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	const CONDITION_PUBLISHED = 1;

	/**
	 * The unpublished condition
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	const CONDITION_UNPUBLISHED = 0;

	/**
	 * The trashed condition
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	const CONDITION_TRASHED = -2;


	/**
	 * Booting the extension. This is the function to set up the environment of the extension like
	 * registering new class loaders, etc.
	 *
	 * If required, some initial set up can be done from services of the container, eg.
	 * registering HTML services.
	 *
	 * @param   ContainerInterface  $container  The container
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function boot(ContainerInterface $container)
	{
		$this->getRegistry()->register('modules', new Modules);
	}

	/**
	 * Returns valid contexts
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getWorkflowContexts(): array
	{
		Factory::getLanguage()->load('com_modules', JPATH_ADMINISTRATOR);

		$contexts = [
			'com_modules.module'    => Text::_('COM_MODULES')
		];

		return $contexts;
	}

	/**
	 * Returns a table name for the state association
	 *
	 * @param   string  $section  An optional section to separate different areas in the component
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getWorkflowTableBySection(?string $section = null): string
	{
		return '#__modules';
	}

	/**
	 * Method to filter transitions by given id of state.
	 *
	 * @param   array  $transitions  The Transitions to filter
	 * @param   int    $pk           Id of the state
	 *
	 * @return  array
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function filterTransitions(array $transitions, int $pk): array
	{
		return ModulesHelper::filterTransitions($transitions, $pk);
	}

	/**
	 * Returns the workflow context based on the given category section
	 *
	 * @param   string  $section  The section
	 *
	 * @return  string|null
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getCategoryWorkflowContext(?string $section = null): string
	{
		return array_key_first($this->getWorkflowContexts());
	}
}
