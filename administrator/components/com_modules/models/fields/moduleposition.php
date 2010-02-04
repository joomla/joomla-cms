<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

require_once JPATH_LIBRARIES.'/joomla/form/fields/list.php';

/**
 * Form field to list the available positions for a module.
 *
 * TODO: This needs to be converted back into a combobox.
 *
 * @package		Joomla.Administrator
 * @subpackage	Modules
 * @since		1.6
 */
class JFormFieldModulePosition extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	public $type = 'ModulePosition';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array		An array of JHtml options.
	 */
	protected function _getOptions()
	{
		$db			= JFactory::getDbo();
		$query		= $db->getQuery(true);
		$clientId	= (int) $this->_form->getValue('client_id');
		$client		= JApplicationHelper::getClientInfo($clientId);

		jimport('joomla.filesystem.folder');

		// template assignment filter
		$query->select('DISTINCT(template)');
		$query->from('#__template_styles');
		$query->where('client_id = '.(int) $clientId);

		$db->setQuery($query);
		$templates = $db->loadResultArray();
		if ($error = $db->getErrorMsg()) {
			JError::raiseWarning(500, $error);
			return false;
		}

		$query->clear();
		$query->select('DISTINCT(position)');
		$query->from('#__modules');
		$query->where('`client_id` = '.(int) $clientId);

		$db->setQuery($query);
		$positions = $db->loadResultArray();
		if ($error = $db->getErrorMsg()) {
			JError::raiseWarning(500, $error);
			return false;
		}

		// Load the positions from the installed templates.
		foreach ($templates as $template) {
			$path = JPath::clean($client->path.'/templates/'.$template.'/templateDetails.xml');

			if (file_exists($path)) {
				$xml = simplexml_load_file($path);
				if (isset($xml->positions[0])) {
					foreach ($xml->positions[0] as $position) {
						$positions[] = (string) $position;
					}
				}
			}
		}
		$positions = array_unique($positions);
		sort($positions);

		$options = array();
		foreach ($positions as $position) {
			$options[]	= JHtml::_('select.option', $position, $position);
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::_getOptions(), $options);

		return $options;
	}
}