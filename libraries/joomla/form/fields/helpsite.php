<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.html.html');
jimport('joomla.language.help');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package		Joomla.Platform
 * @subpackage	Form
 * @since		11.1
 */
class JFormFieldHelpsite extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	11.1
	 */
	public $type = 'Helpsite';

	/**
	 * Method to get the field options.
	 *
	 * @return	array	The field option objects.
	 * @since	11.1
	 */
	protected function getOptions()
	{
		// Get Joomla version.
		$version = new JVersion();
		$jver = explode( '.', $version->getShortVersion() );

		// Merge any additional options in the XML definition.
		$options = array_merge(
			parent::getOptions(),
			JHelp::createSiteList(JPATH_ADMINISTRATOR.'/help/helpsites-'.$jver[0].$jver[1].'.xml', $this->value)
		);

		return $options;
	}
}