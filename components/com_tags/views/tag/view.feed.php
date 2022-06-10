<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_tags
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * HTML View class for the Tags component
 *
 * @since  3.1
 */
class TagsViewTag extends JViewLegacy
{
	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 */
	public function display($tpl = null)
	{
		$app       = JFactory::getApplication();
		$document  = JFactory::getDocument();
		$ids       = (array) $app->input->get('id', array(), 'int');
		$i         = 0;
		$tagIds    = '';

		// Remove zero values resulting from input filter
		$ids = array_filter($ids);

		foreach ($ids as $id)
		{
			if ($i !== 0)
			{
				$tagIds .= '&';
			}

			$tagIds .= 'id[' . $i . ']=' . $id;

			$i++;
		}

		$document->link = JRoute::_('index.php?option=com_tags&view=tag&' . $tagIds);

		$app->input->set('limit', $app->get('feed_limit'));
		$siteEmail        = $app->get('mailfrom');
		$fromName         = $app->get('fromname');
		$feedEmail        = $app->get('feed_email', 'none');
		$document->editor = $fromName;

		if ($feedEmail !== 'none')
		{
			$document->editorEmail = $siteEmail;
		}

		// Get some data from the model
		$items    = $this->get('Items');

		if ($items !== false)
		{
			foreach ($items as $item)
			{
				// Strip HTML from feed item title
				$title = $this->escape($item->core_title);
				$title = html_entity_decode($title, ENT_COMPAT, 'UTF-8');

				// Strip HTML from feed item description text
				$description = $item->core_body;
				$author      = $item->core_created_by_alias ?: $item->author;
				$date        = ($item->displayDate ? date('r', strtotime($item->displayDate)) : '');

				// Load individual item creator class
				$feeditem              = new JFeedItem;
				$feeditem->title       = $title;
				$feeditem->link        = JRoute::_($item->link);
				$feeditem->description = $description;
				$feeditem->date        = $date;
				$feeditem->category    = $title;
				$feeditem->author      = $author;

				if ($feedEmail === 'site')
				{
					$item->authorEmail = $siteEmail;
				}
				elseif ($feedEmail === 'author')
				{
					$item->authorEmail = $item->author_email;
				}

				// Loads item info into RSS array
				$document->addItem($feeditem);
			}
		}
	}
}
