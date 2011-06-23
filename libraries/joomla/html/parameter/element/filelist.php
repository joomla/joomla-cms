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
 * @package     Joomla.Platform
 * @subpackage  Parameter
 * @since       11.1
 * @deprecated  use JForm instead
 */

class JElementFilelist extends JElement
{
	/**
	* Element name
	*
	* @var    string
	*/
	protected $_name = 'Filelist';
	
	/**
	 *
	 * @since   11.1
	 * 
	 * @deprecated
	 */
	public function fetchElement($name, $value, &$node, $control_name)
	{
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');

		// path to images directory
		$path		= JPATH_ROOT.DS.$node->attributes('directory');
		$filter		= $node->attributes('filter');
		$exclude	= $node->attributes('exclude');
		$stripExt	= $node->attributes('stripext');
		$files		= JFolder::files($path, $filter);

		$options = array ();

		if (!$node->attributes('hide_none'))
		{
			$options[] = JHtml::_('select.option', '-1', JText::_('JOPTION_DO_NOT_USE'));
		}

		if (!$node->attributes('hide_default'))
		{
			$options[] = JHtml::_('select.option', '', JText::_('JOPTION_USE_DEFAULT'));
		}

		if (is_array($files))
		{
			foreach ($files as $file)
			{
				if ($exclude)
				{
					if (preg_match(chr(1) . $exclude . chr(1), $file))
					{
						continue;
					}
				}
				if ($stripExt)
				{
					$file = JFile::stripExt($file);
				}
				$options[] = JHtml::_('select.option', $file, $file);
			}
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
