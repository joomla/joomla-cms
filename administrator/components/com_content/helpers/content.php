<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Content component helper.
 *
 * @since  1.6
 */
class ContentHelper extends JHelperContent
{
	public static $extension = 'com_content';

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
		JHtmlSidebar::addEntry(
			JText::_('JGLOBAL_ARTICLES'),
			'index.php?option=com_content&view=articles',
			$vName == 'articles'
		);
		JHtmlSidebar::addEntry(
			JText::_('COM_CONTENT_SUBMENU_CATEGORIES'),
			'index.php?option=com_categories&extension=com_content',
			$vName == 'categories');
		JHtmlSidebar::addEntry(
			JText::_('COM_CONTENT_SUBMENU_FEATURED'),
			'index.php?option=com_content&view=featured',
			$vName == 'featured'
		);
	}

	/**
	 * Applies the content tag filters to arbitrary text as per settings for current user group
	 *
	 * @param   text  $text  The string to filter
	 *
	 * @return  string  The filtered string
	 *
	 * @deprecated  4.0  Use JComponentHelper::filterText() instead.
	 */
	public static function filterText($text)
	{
		JLog::add('ContentHelper::filterText() is deprecated. Use JComponentHelper::filterText() instead.', JLog::WARNING, 'deprecated');

		return JComponentHelper::filterText($text);
	}

	/**
	 * Adds Count Items for Category Manager.
	 *
	 * @param   stdClass[]  &$items  The banner category objects
	 *
	 * @return  stdClass[]
	 *
	 * @since   3.5
	 */
	public static function countItems(&$items)
	{
		$db = JFactory::getDbo();

		foreach ($items as $item)
		{
			$item->count_trashed = 0;
			$item->count_archived = 0;
			$item->count_unpublished = 0;
			$item->count_published = 0;
			$query = $db->getQuery(true);
			$query->select('state, count(*) AS count')
				->from($db->qn('#__content'))
				->where('catid = ' . (int) $item->id)
				->group('state');
			$db->setQuery($query);
			$articles = $db->loadObjectList();

			foreach ($articles as $article)
			{
				if ($article->state == 1)
				{
					$item->count_published = $article->count;
				}

				if ($article->state == 0)
				{
					$item->count_unpublished = $article->count;
				}

				if ($article->state == 2)
				{
					$item->count_archived = $article->count;
				}

				if ($article->state == -2)
				{
					$item->count_trashed = $article->count;
				}
			}
		}

		return $items;
	}


	/**
	 * Detects if the given filter is currently selected. If the $unique argument is
	 * set to true, the function also checks that the given filter is the only one 
	 * being selected.
	 *
	 * @param   string  $filterName  The name of the filter.
	 * @param   boolean $unique      Flag.
	 *
	 * @return  boolean  True if the given arguments match, false otherwise.
	 *
	 */
	public static function checkSelectedFilter($filterName, $unique = false)
	{
	  $post = JFactory::getApplication()->input->post->getArray();

	  //Ensure the given filter has been selected.
	  if(isset($post['filter'][$filterName]) && !empty($post['filter'][$filterName])) {
	    //Ensure that only the given filter has been selected.
	    if($unique) {
	      $filter = 0;
	      foreach($post['filter'] as $value) {
		if(!empty($value)) {
		  $filter++;
		}
	      }

	      if($filter > 1) {
		return false;
	      }
	    }

	    return true;
	  }

	  return false;
	}


	/**
	 * Orders the articles against the selected tag. The ordering is set into the
	 * specific mapping table.
	 *
	 * @param   array $pks           The article ids to order.
	 * @param   integer $tagId       The id of the tag currently selected.
	 * @param   integer $limitStart  The offset of the item to start at.
	 *
	 * @return  boolean  True if the ordering succeeds, false otherwise.
	 *
	 */
	public static function mappingTableOrder($pks, $tagId, $limitStart)
	{
	  //Check first the user can edit state.
	  $user = JFactory::getUser();
	  if(!$user->authorise('core.edit.state', 'com_content')) {
	    return false;
	  }

	  //Start ordering from 1 by default.
	  $ordering = 1;
	  //When pagination is used set ordering from limitstart value.
	  if($limitStart) {
	    $ordering = (int)$limitStart + 1;
	  }

	  $db = JFactory::getDbo();
	  $query = $db->getQuery(true);

	  //Update the ordering values of the mapping table. 
	  foreach($pks as $pk) {
	    $query->clear();
	    $query->update('#__content_tag_map')
		  //Update the item ordering via the mapping table.
		  ->set('ordering='.$ordering)
		  ->where('article_id='.(int)$pk)
		  ->where('tag_id='.(int)$tagId);
	    $db->setQuery($query);
	    $db->query();

	    $ordering++;
	  }

	  return true;
	}
}
