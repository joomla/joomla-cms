<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Platform.
 * Provides a select list of session handler options.
 *
 * @since  1.7.0
 */
class JFormFieldSessionHandler extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.7.0
	 */
	protected $type = 'SessionHandler';

	/**
	 * Method to get the session handler field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   1.7.0
	 */
	protected function getOptions()
	{
		$options = array();

		// Get the options from JSession.
		foreach (JSession::getStores() as $store)
		{
			$options[] = JHtml::_('select.option', $store, JText::_('JLIB_FORM_VALUE_SESSION_' . $store), 'value', 'text');
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
