<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormHelper;

FormHelper::loadFieldClass('predefinedlist');

/**
 * Field to show a list of available user active statuses
 *
 * @since  3.2
 */
class UseractiveField extends \JFormFieldPredefinedList
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since   3.2
	 */
	protected $type = 'UserActive';

	/**
	 * Available statuses
	 *
	 * @var  array
	 * @since  3.2
	 */
	protected $predefinedOptions = array(
		'0'  => 'COM_USERS_ACTIVATED',
		'1'  => 'COM_USERS_UNACTIVATED',
	);

	/**
	 * Method to instantiate the form field object.
	 *
	 * @param   Form  $form  The form to attach to the form field object.
	 *
	 * @since   11.1
	 */
	public function __construct($form = null)
	{
		parent::__construct($form);

		// Load the required language
		$lang = Factory::getLanguage();
		$lang->load('com_users', JPATH_ADMINISTRATOR);
	}
}
