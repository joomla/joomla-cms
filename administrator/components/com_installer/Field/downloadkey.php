<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('text');

/**
 * Download Key field.
 *
 * @since  __DEPLOY_VERSION__
 */
class JFormFieldDownloadkey extends JFormFieldText
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION_
	 */
	public $type = 'Downloadkey';

	/**
	 * Method to get the field input for Download key.
	 *
	 * @return  string  The field input.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getInput()
	{
		$extraQuery = InstallerHelper::getExtraQuery($this->form->getValue('update_site_id'));

		$html = '<input type="text" name="jform[extra_query]" class="form-control" value="' . $extraQuery['value'] . '">';
		$html .= '<input type="hidden" name="dlidprefix" value="' . $extraQuery['prefix'] . '">';
		$html .= '<input type="hidden" name="dlidsufix" value="' . $extraQuery['sufix'] . '">';

		return $html;
	}
}
