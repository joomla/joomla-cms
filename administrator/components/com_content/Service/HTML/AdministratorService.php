<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Administrator\Service\HTML;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\Utilities\ArrayHelper;

/**
 * Content HTML helper
 *
 * @since  3.0
 */
class AdministratorService
{

	/**
	 * Render the list of associated items
	 *
	 * @param   integer  $articleid  The article item id
	 *
	 * @return  string  The language HTML
	 *
	 * @throws  \Exception
	 */
	public function association($articleid)
	{
		// Defaults
		$html = '';

		// Get the associations
		if ($associations = Associations::getAssociations('com_content', '#__content', 'com_content.item', $articleid))
		{
			foreach ($associations as $tag => $associated)
			{
				$associations[$tag] = (int) $associated->id;
			}

			// Get the associated menu items
			$db = Factory::getDbo();
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
			catch (\RuntimeException $e)
			{
				throw new \Exception($e->getMessage(), 500, $e);
			}

			if ($items)
			{
				foreach ($items as &$item)
				{
					$text    = $item->lang_sef ? strtoupper($item->lang_sef) : 'XX';
					$url     = Route::_('index.php?option=com_content&task=article.edit&id=' . (int) $item->id);
					$tooltip = htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8') . '<br>' . Text::sprintf('JCATEGORY_SPRINTF', $item->category_title);
					$classes = 'hasPopover badge badge-secondary';

					$item->link = '<a href="' . $url . '" title="' . $item->language_title . '" class="' . $classes
						. '" data-content="' . $tooltip . '" data-placement="top">'
						. $text . '</a>';
				}
			}

			HTMLHelper::_('bootstrap.popover');

			$html = LayoutHelper::render('joomla.content.associations', $items);
		}

		return $html;
	}

	/**
	 * Show the feature/unfeature links
	 *
	 * @param   integer  $value      The state value
	 * @param   integer  $i          Row number
	 * @param   boolean  $canChange  Is user allowed to change?
	 *
	 * @return  string       HTML code
	 */
	public function featured($value = 0, $i, $canChange = true)
	{
		// Array of image, task, title, action
		$states = array(
			0 => array('unfeatured', 'articles.featured', 'COM_CONTENT_UNFEATURED', 'JGLOBAL_TOGGLE_FEATURED'),
			1 => array('featured', 'articles.unfeatured', 'COM_CONTENT_FEATURED', 'JGLOBAL_TOGGLE_FEATURED'),
		);
		$state = ArrayHelper::getValue($states, (int) $value, $states[1]);
		$icon  = $state[0];

		if ($canChange)
		{
			$html = '<a href="#" onclick="return listItemTask(\'cb' . $i . '\',\'' . $state[1] . '\')" class="tbody-icon hasTooltip'
				. ($value == 1 ? ' active' : '') . '" title="' . HTMLHelper::_('tooltipText', $state[3])
				. '"><span class="icon-' . $icon . '" aria-hidden="true"></span></a>';
		}
		else
		{
			$html = '<a class="tbody-icon hasTooltip disabled' . ($value == 1 ? ' active' : '') . '" title="'
				. HTMLHelper::_('tooltipText', $state[2]) . '"><span class="icon-' . $icon . '" aria-hidden="true"></span></a>';
		}

		return $html;
	}
}
