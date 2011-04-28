<?php
/**
 * @package     Joomla.Platform
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Renders a filelist element
 *
 * @package		Joomla.Platform
 * @subpackage	Parameter
 * @since		11.1
 * @deprecated	JParameter is deprecated and will be removed in a future version. Use JForm instead.
 */

class JElementFolderlist extends JElement
{
	/**
	* Element name
	*
	* @var		string
	*/
	protected $_name = 'Folderlist';

	public function fetchElement($name, $value, &$node, $control_name)
	{
		jimport('joomla.filesystem.folder');

		// Initialise variables.
		$path		= JPATH_ROOT.DS.$node->attributes('directory');
		$filter		= $node->attributes('filter');
		$exclude	= $node->attributes('exclude');
		$folders	= JFolder::folders($path, $filter);

		$options = array ();
		foreach ($folders as $folder) {
			if ($exclude) {
				if (preg_match(chr(1).$exclude.chr(1), $folder)) {
					continue;
				}
			}
			$options[] = JHtml::_('select.option', $folder, $folder);
		}

		if (!$node->attributes('hide_none')) {
			array_unshift($options, JHtml::_('select.option', '-1', JText::_('JOPTION_DO_NOT_USE')));
		}

		if (!$node->attributes('hide_default')) {
			array_unshift($options, JHtml::_('select.option', '', JText::_('JOPTION_USE_DEFAULT')));
		}

		return JHtml::_('select.genericlist', $options, $control_name .'['. $name .']',
			array(
				'id' => 'param'.$name,
				'list.attr' => 'class="inputbox"',
				'list.select' => $value
			)
		);
	}
}