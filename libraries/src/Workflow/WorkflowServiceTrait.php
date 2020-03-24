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
use Joomla\CMS\MVC\Model\WorkflowModelInterface;

\defined('JPATH_PLATFORM') or die;

/**
 * Trait for component workflow service.
 *
 * @since  4.0.0
 */
trait WorkflowServiceTrait {
	abstract function getMVCFactory();

	/** @var array Supported functionality */
	protected $supportedFunctionality = [
		'joomla.state'    => true,
		'joomla.featured' => true,
	];

	/**
	 * Check if the functionality is supported by the context
	 *
	 * @param   string  $feature  The functionality
	 * @param   string  $context  The context of the functionality
	 *
	 * @return bool
	 */
	public function supportFunctionality($functionality, $context): bool {

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
	 * Returns an array of possible conditions for the component.
	 *
	 * @param   string  $extension  The component and section separated by ".".
	 *
	 * @return  array
	 *
	 * @since   4.0.0
	 */
	public static function getConditions(string $extension): array {

		return \defined('self::CONDITION_NAMES') ? self::CONDITION_NAMES : Workflow::CONDITION_NAMES;
	}

	public function isWorkflowActive($context): bool {

		$parts  = explode('.', $context);
		$config = ComponentHelper::getParams($parts[0]);

		if (!$config->get('workflows_enable', 1))
		{
			return false;
		}

		$component = $this->getMVCFactory();
		$appName   = Factory::getApplication()->getName();
		$model     = $component->createModel($parts[1], $appName, ['ignore_request' => true]);

		return $model instanceof WorkflowModelInterface;
	}
}
