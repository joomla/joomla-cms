<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Administrator\Helper;

defined('_JEXEC') or die;

/**
 * Content component helper.
 *
 * @since  1.6
 */
class ContentHelper extends \Joomla\CMS\Helper\ContentHelper
{
	/**
	 * Configure the Linkbar.
	 *
	 * @param   string  $vName  The name of the active view.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public static function addSubmenu($vName)
	{
		\JHtmlSidebar::addEntry(
			\JText::_('JGLOBAL_ARTICLES'),
			'index.php?option=com_content&view=articles',
			$vName == 'articles'
		);
		\JHtmlSidebar::addEntry(
			\JText::_('COM_CONTENT_SUBMENU_CATEGORIES'),
			'index.php?option=com_categories&extension=com_content',
			$vName == 'categories'
		);
		\JHtmlSidebar::addEntry(
			\JText::_('COM_CONTENT_SUBMENU_FEATURED'),
			'index.php?option=com_content&view=featured',
			$vName == 'featured'
		);

		if (\JComponentHelper::isEnabled('com_fields') && \JComponentHelper::getParams('com_content')->get('custom_fields_enable', '1'))
		{
			\JHtmlSidebar::addEntry(
				\JText::_('JGLOBAL_FIELDS'),
				'index.php?option=com_fields&context=com_content.article',
				$vName == 'fields.fields'
			);
			\JHtmlSidebar::addEntry(
				\JText::_('JGLOBAL_FIELD_GROUPS'),
				'index.php?option=com_fields&view=groups&context=com_content.article',
				$vName == 'fields.groups'
			);
		}
	}
}
