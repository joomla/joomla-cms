<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('ContentHelper', JPATH_ADMINISTRATOR . '/components/com_content/helpers/content.php');

/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 */
abstract class JHtmlContentAdministrator
{
	/**
	 * Get the associated language flags
	 *
	 * @param   int  $articleid  The article item id
	 *
	 * @return  string  The language HTML
	 */
	public static function association($articleid)
	{
		// Defaults
		$html = '';

		// Get the associations
		if ($associations = JLanguageAssociations::getAssociations('com_content', '#__content', 'com_content.item', $articleid))
		{

			foreach ($associations as $tag => $associated)
			{
				$associations[$tag] = (int) $associated->id;
			}

			// Get the associated menu items
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select('c.*')
				->from('#__content as c')
				->select('cat.title as category_title')
				->join('LEFT', '#__categories as cat ON cat.id=c.catid')
				->where('c.id IN (' . implode(',', array_values($associations)) . ')')
				->join('LEFT', '#__languages as l ON c.language=l.lang_code')
				->select('l.image')
				->select('l.title as language_title');
			$db->setQuery($query);

			try
			{
				$items = $db->loadObjectList('id');
			}
			catch (runtimeException $e)
			{
				throw new Exception($e->getMessage(), 500);

				return false;
			}

			$flags = array();

			// Construct html
			foreach ($associations as $tag => $associated)
			{
				if ($associated != $articleid)
				{
					$flags[] = JText::sprintf(
						'COM_CONTENT_TIP_ASSOCIATED_LANGUAGE',
						JHtml::_('image', 'mod_languages/' . $items[$associated]->image . '.gif',
							$items[$associated]->language_title,
							array('title' => $items[$associated]->language_title),
							true
						),
						$items[$associated]->title, $items[$associated]->category_title
					);
				}
			}

			$html = JHtml::_('tooltip', implode('<br />', $flags), JText::_('COM_CONTENT_TIP_ASSOCIATION'), 'admin/icon-16-links.png');

		}

		return $html;
	}

	/**
	 * @param   int $value	The state value
	 * @param   int $i
	 */
	public static function featured($value = 0, $i, $canChange = true)
	{
		JHtml::_('bootstrap.tooltip');

		// Array of image, task, title, action
		$states	= array(
			0	=> array('unfeatured',	'articles.featured',	'COM_CONTENT_UNFEATURED',	'COM_CONTENT_TOGGLE_TO_FEATURE'),
			1	=> array('featured',	'articles.unfeatured',	'COM_CONTENT_FEATURED',		'COM_CONTENT_TOGGLE_TO_UNFEATURE'),
		);
		$state	= JArrayHelper::getValue($states, (int) $value, $states[1]);
		$icon	= $state[0];
		if ($canChange)
		{
			$html	= '<a href="#" onclick="return listItemTask(\'cb'.$i.'\',\''.$state[1].'\')" class="btn btn-micro hasTooltip' . ($value == 1 ? ' active' : '') . '" title="'.JText::_($state[3]).'"><i class="icon-'
					. $icon.'"></i></a>';
		}
		else
		{
			$html	= '<a class="btn btn-micro hasTooltip disabled' . ($value == 1 ? ' active' : '') . '" title="'.JText::_($state[2]).'"><i class="icon-'
					. $icon.'"></i></a>';
		}

		return $html;
	}
}
