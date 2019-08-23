<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

JLoader::register('ContentHelper', JPATH_ADMINISTRATOR . '/components/com_content/helpers/content.php');

/**
 * Content HTML helper
 *
 * @since  3.0
 */
abstract class JHtmlContentAdministrator
{
	/**
	 * Render the list of associated items
	 *
	 * @param   integer  $articleid  The article item id
	 *
	 * @return  string  The language HTML
	 *
	 * @throws  Exception
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
				->select('l.sef as lang_sef')
				->select('l.lang_code')
				->from('#__content as c')
				->select('cat.title as category_title')
				->join('LEFT', '#__categories as cat ON cat.id=c.catid')
				->where('c.id IN (' . implode(',', array_values($associations)) . ')')
				->where('c.id != ' . $articleid)
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
				throw new Exception($e->getMessage(), 500, $e);
			}

			if ($items)
			{
				foreach ($items as &$item)
				{
					$text    = $item->lang_sef ? strtoupper($item->lang_sef) : 'XX';
					$url     = JRoute::_('index.php?option=com_content&task=article.edit&id=' . (int) $item->id);

					$tooltip = htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8') . '<br />' . JText::sprintf('JCATEGORY_SPRINTF', $item->category_title);
					$classes = 'hasPopover label label-association label-' . $item->lang_sef;

					$item->link = '<a href="' . $url . '" title="' . $item->language_title . '" class="' . $classes
						. '" data-content="' . $tooltip . '" data-placement="top">'
						. $text . '</a>';
				}
			}

			JHtml::_('bootstrap.popover');

			$html = JLayoutHelper::render('joomla.content.associations', $items);
		}

		return $html;
	}

	/**
	 * Show the feature/unfeature links
	 *
	 * @param   integer  $value          The state value
	 * @param   integer  $i              Row number
	 * @param   boolean  $canChange      Is user allowed to change?
	 * @param   string   $featuredUp     An optional start featured date.
	 * @param   string   $featuredDown   An optional finish featured date.
	 *
	 * @return  string       HTML code
	 */
	public static function featured($value = 0, $i, $canChange = true, $featuredUp = null, $featuredDown = null)
	{
		JHtml::_('bootstrap.tooltip');

		// Array of image, task, title, action
		$states = array(
			0 => array('unfeatured', 'articles.featured', 'COM_CONTENT_UNFEATURED', 'JGLOBAL_TOGGLE_FEATURED'),
			1 => array('featured', 'articles.unfeatured', 'COM_CONTENT_FEATURED', 'JLIB_HTML_FEATURED_ITEM'),
		);
		$state = ArrayHelper::getValue($states, (int) $value, $states[1]);

		// Special state for dates
		if (((int) $value == 1) && ($featuredUp || $featuredDown))
		{
			$nullDate = JFactory::getDbo()->getNullDate();
			$nowDate = JFactory::getDate()->toUnix();

			$tz = JFactory::getUser()->getTimezone();

			$featuredUp = ($featuredUp != $nullDate) ? JFactory::getDate($featuredUp, 'UTC')->setTimeZone($tz) : false;
			$featuredDown = ($featuredDown != $nullDate) ? JFactory::getDate($featuredDown, 'UTC')->setTimeZone($tz) : false;

			// Create tip text, only we have featured up or down settings
			$tips = array();

			// Add tips and set icon
			if ($featuredUp > $nullDate)
			{
				if ($nowDate < $featuredUp->toUnix())
				{
					$tips[] = JText::sprintf('JLIB_HTML_FEATURED_START', JHtml::_('date', $featuredUp, JText::_('DATE_FORMAT_LC5'), 'UTC'));
					$state[0] = 'pending';
				}
				else
				{
					$tips[] = JText::sprintf('JLIB_HTML_FEATURED_STARTED', JHtml::_('date', $featuredUp, JText::_('DATE_FORMAT_LC5'), 'UTC'));
				}
			}

			if ($featuredDown > $nullDate)
			{
				if ($nowDate > $featuredDown->toUnix())
				{
					$tips[] = JText::sprintf('JLIB_HTML_FEATURED_FINISHED', JHtml::_('date', $featuredDown, JText::_('DATE_FORMAT_LC5'), 'UTC'));
					$state[0] = 'expired';
				}
				else
				{
					$tips[] = JText::sprintf('JLIB_HTML_FEATURED_FINISH', JHtml::_('date', $featuredDown, JText::_('DATE_FORMAT_LC5'), 'UTC'));
				}
			}

			// Add tips to titles
			if (!empty($tips))
			{
				$tip = implode('<br />', $tips);
				$state[1] = JText::_($state[1]);
				$state[2] = JText::_($state[2]) . '<br />' . $tip;
				$state[3] = JText::_($state[3]) . '<br />' . $tip;
			}
		}

		$icon = $state[0];

		if ($canChange)
		{
			$html = '<a href="#" onclick="return listItemTask(\'cb' . $i . '\',\'' . $state[1] . '\')" class="btn btn-micro hasTooltip'
				. ($value == 1 ? ' active' : '') . '" title="' . JHtml::_('tooltipText', $state[3])
				. '"><span class="icon-' . $icon . '" aria-hidden="true"></span></a>';
		}
		else
		{
			$html = '<a class="btn btn-micro hasTooltip disabled' . ($value == 1 ? ' active' : '') . '" title="'
				. JHtml::_('tooltipText', $state[2]) . '"><span class="icon-' . $icon . '" aria-hidden="true"></span></a>';
		}

		return $html;
	}
}
