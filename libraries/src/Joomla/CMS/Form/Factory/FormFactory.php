<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Factory;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\Form\FormHelper;

/**
 * Form field factory.
 *
 * @since  __DEPLOY_VERSION__
 */
class FormFactory implements FormFactoryInterface
{
	/**
	 * Method to load a form field object by the given type.
	 *
	 * @param   string   $type  The field type.
	 * @param   boolean  $new   Flag to toggle whether we should get a new instance of the object.
	 *
	 * @return  FormField|boolean  FormField object on success, false otherwise.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getField($type, $new = true)
	{
		return FormHelper::loadFieldType($type, $new);
	}

	/**
	 * Method to load a form rule object by the given type.
	 *
	 * @param   string   $type  The field type.
	 * @param   boolean  $new   Flag to toggle whether we should get a new instance of the object.
	 *
	 * @return  FormRule|boolean  FormRule object on success, false otherwise.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getRule($type, $new = true)
	{
		return FormHelper::loadRuleType($type, $new);
	}
}
