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
		$value = $this->form->getValue('extra_query');

		if ($this->form->dlidprefix == null)
		{
			$path = InstallerHelper::getInstalationXML($this->form->getValue('update_site_id'));

			$installXmlFile = simplexml_load_file($path);
			$prefix = (string) $installXmlFile->dlid['prefix'];
			$sufix  = (string) $installXmlFile->dlid['sufix'];
		}
		else
		{
			$prefix = $this->form->dlidprefix;
			$sufix = $this->form->dlidsufix;
		}


		$html = '<input type="text" name="jform[extra_query]" class="form-control" value="';
		$value = substr($value, strlen($prefix));

		if ($sufix != null)
		{
			$value = substr($value, 0, -strlen($sufix));
		}

		$html .= $value . '">';
		$html .= '<input type="hidden" name="dlidprefix" value="' . $prefix . '">';
		$html .= '<input type="hidden" name="dlidsufix" value="' . $sufix . '">';

		return $html;
	}
}
