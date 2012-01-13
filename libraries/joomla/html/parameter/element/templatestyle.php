<?php
/**
 * @package     Joomla.Platform
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Renders a list of template styles.
 *
 * @package     Joomla.Platform
 * @subpackage  Parameter
 * @since       11.1
 * @deprecated  12.1  Use JFormFieldTemplateStyle instead
 */
class JElementTemplateStyle extends JElement
{
	/**
	 * Element name
	 *
	 * @var    string
	 */
	protected $_name = 'TemplateStyle';

	/**
	 * Fetch the template style element
	 *
	 * @param   string       $name          Element name
	 * @param   string       $value         Element value
	 * @param   JXMLElement  &$node         JXMLElement node object containing the settings for the element
	 * @param   string       $control_name  Control name
	 *
	 * @return  string
	 *
	 * @deprecated  12.1  Use JFormFieldTemplateStyle::getGroups  Instead
	 * @since   11.1
	 */
	public function fetchElement($name, $value, &$node, $control_name)
	{
		// Deprecation warning.
		JLog::add('JElementTemplateStyle::_fetchElement() is deprecated.', JLog::WARNING, 'deprecated');

		$db = JFactory::getDBO();

		$query = 'SELECT * FROM #__template_styles ' . 'WHERE client_id = 0 ' . 'AND home = 0';
		$db->setQuery($query);
		$data = $db->loadObjectList();

		$default = JHtml::_('select.option', 0, JText::_('JOPTION_USE_DEFAULT'), 'id', 'description');
		array_unshift($data, $default);

		$selected = $this->_getSelected();
		$html = JHTML::_('select.genericlist', $data, $control_name . '[' . $name . ']', 'class="inputbox" size="6"', 'id', 'description', $selected);

		return $html;
	}

	/**
	 * Get the selected template style.
	 *
	 * @return  integer  The template style id.
	 *
	 * @since   11.1
	 * @deprecated    12.1  Use jFormFieldTemplateStyle instead.
	 */
	protected function _getSelected()
	{
		// Deprecation warning.
		JLog::add('JElementTemplateStyle::_getSelected() is deprecated.', JLog::WARNING, 'deprecated');

		$id = JRequest::getVar('cid', 0);
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select($query->qn('template_style_id'))->from($query->qn('#__menu'))->where($query->qn('id') . ' = ' . (int) $id[0]);
		$db->setQuery($query);
		$result = $db->loadResult();
		return $result;
	}
}
