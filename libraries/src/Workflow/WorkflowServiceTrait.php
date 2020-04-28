<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Workflow;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\WorkflowModelInterface;

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
	 * @since   __DEPLOY_VERSION__
	 */
	abstract public function getMVCFactory(): MVCFactoryInterface;

	/** @var array Supported functionality */
	protected $supportedFunctionality = [
		'joomla.state'    => true,
		'joomla.featured' => true,
	];

	/**
	 * Check if the functionality is supported by the context
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

		return in_array($context, $this->supportedFunctionality[$functionality]);
	}

	/**
	 * Returns the model name, based on the context
	 *
	 * @param   string  $context  The context of the workflow
	 *
	 * @return boolean
	 */
	public function getModelName($context) : string
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

		if (!$config->get('workflow_enabled', 1))
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
