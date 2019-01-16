<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\String\StringHelper;

/**
 * JFormRule for com_contact to make sure the message body contains no banned word.
 *
 * @since  1.6
 */
class JFormRuleContactEmailMessage extends JFormRule
{
	/**
	 * Method to test a message for banned words
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the <field /> tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value. This acts as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 * @param   Registry          $input    An optional Registry object with the entire data set to validate against the entire form.
	 * @param   JForm             $form     The form object for which the field is being tested.
	 *
	 * @return  boolean  True if the value is valid, false otherwise.
	 */
	public function test(SimpleXMLElement $element, $value, $group = null, Registry $input = null, JForm $form = null)
	{
		$app       = JFactory::getApplication();
		$model     = JModelLegacy::getInstance('Contact', 'ContactModel');
		$stub      = $app->input->getString('id');
		$contactId = (int) $stub;

		$contact = $model->getItem($contactId);

		// Get item params, take menu parameters into account if necessary
		$active      = $app->getMenu()->getActive();
		$stateParams = clone $model->getState()->get('params');

		// If the current view is the active item and a contact view for this contact, then the menu item params take priority
		if ($active && strpos($active->link, 'view=contact') && strpos($active->link, '&id=' . (int) $contact->id))
		{
			// $item->params are the contact params, $active->params are the menu item params
			// Merge so that the menu item params take priority
			$contact->params->merge($active->params);
		}
		else
		{
			// Current view is not a single contact displayed by a specific menu item, so the contact params take priority here
			$stateParams->merge($contact->params);
			$contact->params = $stateParams;
		}

		$banned = $contact->params->get('banned_text');

		if ($banned)
		{
			foreach (explode(';', $banned) as $item)
			{
				if ($item != '' && StringHelper::stristr($value, $item) !== false)
				{
					return false;
				}
			}
		}

		return true;
	}
}
