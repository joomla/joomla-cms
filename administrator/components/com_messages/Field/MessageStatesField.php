<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_messages
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Messages\Administrator\Field;

defined('JPATH_BASE') or die;

use Joomla\CMS\Form\FormHelper;
use Joomla\Component\Messages\Administrator\Helper\MessagesHelper;

FormHelper::loadFieldClass('list');

/**
 * Message States field.
 *
 * @since  3.6.0
 */
class MessageStatesField extends \JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var     string
	 * @since   3.6.0
	 */
	protected $type = 'MessageStates';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   3.6.0
	 */
	protected function getOptions()
	{
		// Merge state options with any additional options in the XML definition.
		return array_merge(parent::getOptions(), MessagesHelper::getStateOptions());
	}
}
