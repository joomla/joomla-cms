<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Workflow;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Event\AbstractEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\WorkflowModelInterface;
use Joomla\Event\DispatcherAwareInterface;

\defined('JPATH_PLATFORM') or die;

/**
 * Trait for component workflow service.
 *
 * @since  4.0.0
 */
trait WorkflowServiceTrait
{
	/**
	 * Get a MVC factory
	 *
	 * @return  MVCFactoryInterface
	 *
	 * @since   4.0.0
	 */
	abstract public function getMVCFactory(): MVCFactoryInterface;

	/**
	 * Check if the functionality is supported by the component
	 * The variable $supportFunctionality has the following structure
	 * [
	 *   'core.featured' => [
	 *     'com_content.article',
	 *   ],
	 *   'core.state' => [
	 *     'com_content.article',
	 *   ],
	 * ]
	 *
	 * @param   string  $functionality  The functionality
	 * @param   string  $context        The context of the functionality
	 *
	 * @return boolean
	 */
	public function supportFunctionality($functionality, $context): bool
	{
		if (empty($this->supportedFunctionality[$functionality]))
		{
			return false;
		}

		if (!is_array($this->supportedFunctionality[$functionality]))
		{
			return true;
		}

		return in_array($context, $this->supportedFunctionality[$functionality], true);
	}

	/**
	 * Check if the functionality is used by a plugin
	 *
	 * @param   string  $functionality  The functionality
	 * @param   string  $extension      The extension
	 *
	 * @return boolean
	 * @throws \Exception
	 *
	 * @since   4.0.0
	 */
	public function isFunctionalityUsed($functionality, $extension): bool
	{
		static $used = [];

		$cacheKey = $extension . '.' . $functionality;

		if (isset($used[$cacheKey]))
		{
			return $used[$cacheKey];
		}

		// The container to get the services from
		$app = Factory::getApplication();

		if (!($app instanceof DispatcherAwareInterface))
		{
			return false;
		}

		$eventResult = $app->getDispatcher()->dispatch(
			'onWorkflowFunctionalityUsed',
			AbstractEvent::create(
				'onWorkflowFunctionalityUsed',
				[
					'eventClass'    => 'Joomla\CMS\Event\Workflow\WorkflowFunctionalityUsedEvent',
					'subject'       => $this,
					'extension'     => $extension,
					'functionality' => $functionality
				]
			)
		);

		$used[$cacheKey] = $eventResult->getArgument('used', false);

		return $used[$cacheKey];
	}

	/**
	 * Returns the model name, based on the context
	 *
	 * @param   string  $context  The context of the workflow
	 *
	 * @return boolean
	 *
	 * @since   4.0.0
	 */
	public function getModelName($context): string
	{
		$parts = explode('.', $context);

		if (count($parts) < 2)
		{
			return '';
		}

		array_shift($parts);

		return ucfirst(array_shift($parts));
	}

	/**
	 * Returns an array of possible conditions for the component.
	 *
	 * @param   string  $extension  The component and section separated by ".".
	 *
	 * @return  array
	 *
	 * @since   4.0.0
	 */
	public static function getConditions(string $extension): array
	{
		return \defined('self::CONDITION_NAMES') ? self::CONDITION_NAMES : Workflow::CONDITION_NAMES;
	}

	/**
	 * Check if the workflow is active
	 *
	 * @param   string  $context  The context of the workflow
	 *
	 * @return boolean
	 */
	public function isWorkflowActive($context): bool
	{
		$parts  = explode('.', $context);
		$config = ComponentHelper::getParams($parts[0]);

		if (!$config->get('workflow_enabled'))
		{
			return false;
		}

		$modelName = $this->getModelName($context);

		if (empty($modelName))
		{
			return false;
		}

		$component = $this->getMVCFactory();
		$appName   = Factory::getApplication()->getName();
		$model     = $component->createModel($modelName, $appName, ['ignore_request' => true]);

		return $model instanceof WorkflowModelInterface;
	}
}
