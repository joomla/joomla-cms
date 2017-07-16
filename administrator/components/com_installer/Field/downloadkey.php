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

		$db    = \JFactory::getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName('e.element'))
			->from('#__update_sites AS s')
			->innerJoin('#__update_sites_extensions AS se ON (se.update_site_id = s.update_site_id)')
			->innerJoin('#__extensions AS e ON (e.extension_id = se.extension_id)')
			->where($db->quoteName('s.update_site_id') . ' = ' . (int) $this->form->getValue('update_site_id'));
		$db->setQuery($query);
		$element = $db->loadResult();

		$installXmlFile = simplexml_load_file(
			JPATH_ADMINISTRATOR . '/components/' . $element . '/' . substr($element, 4) . '.xml'
		);

		$prefix = (string) $installXmlFile->dlid['prefix'];
		$sufix = (string) $installXmlFile->dlid['sufix'];

		$html = '<input type="text" name="jform[extra_query]" class="form-control" value="';
		$html .= substr(substr($value, strlen($prefix)), 0, -strlen($sufix)) . '">';
		$html .= '<input type="hidden" name="dlidprefix" value="' . $prefix . '">';
		$html .= '<input type="hidden" name="dlidsufix" value="' . $sufix . '">';

		return $html;
	}
}
