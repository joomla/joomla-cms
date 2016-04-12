<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_newsfeeds
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

JFormHelper::loadFieldClass('list');

JLoader::register('MessagesHelper', JPATH_ADMINISTRATOR . '/components/com_messages/helpers/messages.php');
/**
 * Form Field class for the Joomla Framework.
 *
 * @since  1.6
 */
class JFormFieldMessageStates extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since   3.5.2
	 */
	protected $type = 'MessageStates';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   3.5.2
	 */
	protected function getOptions()
	{
		$options = MessagesHelper::getStateOptions();

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
