<?php
/**
 * @package     Joomla.Platform
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

require_once dirname(__FILE__).'/list.php';

/**
 * Renders a select list of Asset Groups
 *
 * @package     Joomla.Platform
 * @subpackage  Parameter
 * @since       11.1
 * @deprecated .Use JForm instead
 */
class JElementContentLanguages extends JElementList
{
	/**
	* Element name
	*
	* @var    string
	*/
	protected $_name = 'ContentLanguages';

	/**
	 * Get the options for the element
	 *
	 * @param   object   $node
	 * @return  array
	 */
	protected function _getOptions(&$node)
	{
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);

		$query->select('a.lang_code AS value, a.title AS text, a.title_native');
		$query->from('#__languages AS a');
		$query->where('a.published >= 0');
		$query->order('a.title');

		// Get the options.
		$db->setQuery($query);
		$options = $db->loadObjectList();

		// Check for a database error.
		if ($db->getErrorNum()) {
			JError::raiseWarning(500, $db->getErrorMsg());
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::_getOptions($node), $options);

		return $options;
	}
}
