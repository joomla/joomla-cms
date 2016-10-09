<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Platform.
 * Provides a select list of help sites.
 *
 * @since  1.6
 */
class JFormFieldHelpsite extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.6
	 */
	public $type = 'Helpsite';

	/**
	 * Method to get the help site field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   1.6
	 */
	protected function getOptions()
	{
		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), JHelp::createSiteList(JPATH_ADMINISTRATOR . '/help/helpsites.xml', $this->value));

		return $options;
	}

	/**
	 * Override to add refresh button
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   3.2
	 */
	protected function getInput()
	{
		JHtml::_('script', 'system/helpsite.js', array('version' => 'auto', 'relative' => true));

		$showDefault = $this->getAttribute('showDefault') === 'false' ? 'false' : 'true';

		$html = parent::getInput();
		$button = '<button
						type="button"
						class="btn btn-small"
						id="helpsite-refresh"
						rel="' . $this->id . '"
						showDefault="' . $showDefault . '"
					>
					<span>' . JText::_('JGLOBAL_HELPREFRESH_BUTTON') . '</span>
					</button>';

		return $html . $button;
	}
}
