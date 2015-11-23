<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

JLoader::register('CjForumHelper', JPATH_ADMINISTRATOR . '/components/com_cjforum/helpers/cjforum.php');

abstract class JHtmlCjForumAdministrator
{
	public static function association ($topicid)
	{
		// Defaults
		$html = '';
		
		// Get the associations
		if ($associations = JLanguageAssociations::getAssociations('com_cjforum', '#__cjforum_topics', 'com_cjforum.item', $topicid))
		{
			foreach ($associations as $tag => $associated)
			{
				$associations[$tag] = (int) $associated->id;
			}
			
			// Get the associated menu items
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select('c.*')
				->select('l.sef as lang_sef')
				->from('#__cjforum_topics as c')
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
			catch (RuntimeException $e)
			{
				throw new Exception($e->getMessage(), 500);
			}
			
			if ($items)
			{
				foreach ($items as &$item)
				{
					$text = strtoupper($item->lang_sef);
					$url = JRoute::_('index.php?option=com_cjforum&task=topic.edit&id=' . (int) $item->id);
					$tooltipParts = array(
							JHtml::_('image', 'mod_languages/' . $item->image . '.gif', $item->language_title, array('title' => $item->language_title), true), 
							$item->title,'(' . $item->category_title . ')');
					$item->link = JHtml::_('tooltip', implode(' ', $tooltipParts), null, null, $text, $url, null, 'hasTooltip label label-association label-' . $item->lang_sef);
				}
			}
			
			$html = JLayoutHelper::render('joomla.content.associations', $items);
		}
		
		return $html;
	}

	public static function featured ($value = 0, $i, $canChange = true)
	{
		JHtml::_('bootstrap.tooltip');
		
		// Array of image, task, title, action
		$states = array(
				0 => array(
						'unfeatured',
						'topics.featured',
						'COM_CJFORUM_UNFEATURED',
						'COM_CJFORUM_TOGGLE_TO_FEATURE'
				),
				1 => array(
						'featured',
						'topics.unfeatured',
						'COM_CJFORUM_FEATURED',
						'COM_CJFORUM_TOGGLE_TO_UNFEATURE'
				)
		);
		$state = JArrayHelper::getValue($states, (int) $value, $states[1]);
		$icon = $state[0];
		
		if ($canChange)
		{
			$html = '<a href="#" onclick="return listItemTask(\'cb' . $i . '\',\'' . $state[1] . '\')" class="btn btn-micro hasTooltip' .
					 ($value == 1 ? ' active' : '') . '" title="' . JHtml::tooltipText($state[3]) . '"><i class="icon-' . $icon . '"></i></a>';
		}
		else
		{
			$html = '<a class="btn btn-micro hasTooltip disabled' . ($value == 1 ? ' active' : '') . '" title="' . JHtml::tooltipText($state[2]) .
					 '"><i class="icon-' . $icon . '"></i></a>';
		}
		
		return $html;
	}
}
