<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.language.help');
JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Platform.
 * Provides a select list of help sites.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 */
class JFormFieldHelpsite extends JFormFieldList
{

	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $type = 'Helpsite';

	/**
	 * Method to get the help site field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   11.1
	 */
	protected function getOptions()
	{
		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), JHelp::createSiteList(JPATH_ADMINISTRATOR . '/help/helpsites.xml', $this->value));

		return $options;
	}
}
