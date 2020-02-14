<?php

/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

$k2Route 	 = JPATH_SITE . '/components/com_k2/helpers/route.php';
$k2Unilities = JPATH_SITE . '/components/com_k2/helpers/utilities.php';

if(!file_exists($k2Route) && !file_exists($k2Unilities)) {
	$k2cats = array(''=>'K2 Isn\'t installed');
	return false;
} else {
	require_once $k2Route;
	require_once $k2Unilities;
}


abstract class SppagebuilderHelperK2{

	public static function getItems( $count = 5, $ordering = 'latest', $catid = '', $include_subcategories = true, $post_format = '' ) {

		$authorised = JAccess::getAuthorisedViewLevels(JFactory::getUser()->get('id'));
		$componentParams = JComponentHelper::getParams('com_k2');

		$app = JFactory::getApplication();
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$jnow = JFactory::getDate();
		$nowDate = K2_JVERSION == '15' ? $jnow->toMySQL() : $jnow->toSql();
		$nullDate = $db->getNullDate();

		$query
		->select('a.*')
		->from($db->quoteName('#__k2_items', 'a'))
		->select($db->quoteName('b.alias', 'category_alias'))
		->select($db->quoteName('b.name', 'category'))
		->join('LEFT', $db->quoteName('#__k2_categories', 'b') . ' ON (' . $db->quoteName('a.catid') . ' = ' . $db->quoteName('b.id') . ')');

		$query->where($db->quoteName('a.published') . ' = ' . $db->quote(1));
		$query->where($db->quoteName('a.trash') . ' != ' . $db->quote(1));
		$query->where('(a.publish_up = ' . $db->Quote($nullDate) . ' OR a.publish_up <= ' . $db->Quote($nowDate) . ')');
		$query->where('(a.publish_down = ' . $db->Quote($nullDate) . ' OR a.publish_down >= ' . $db->Quote($nowDate) . ')');

		// Category filter
		if ( ($catid != '' || is_array($catid)) ) {
			if (!is_array($catid)) {
				$catid = array($catid);
			}
			if (!in_array('', $catid)) {
				$categories = self::getCategories( $catid, $include_subcategories );
				$categories = array_merge($categories, $catid);
				$query->where($db->quoteName('a.catid')." IN (" . implode( ',', $categories ) . ")");
			}
		}

		// has order by
		if ($ordering == 'hits') {
			$query->order($db->quoteName('a.hits') . ' DESC');
		}elseif($ordering == 'featured'){
			$query->where($db->quoteName('a.featured') . ' = ' . $db->quote(1));
			$query->order($db->quoteName('a.created') . ' DESC');
		}else{
			$query->order($db->quoteName('a.created') . ' DESC');
		}

		// Language filter
		if ($app->getLanguageFilter()) {
			$query->where('a.language IN (' . $db->Quote(JFactory::getLanguage()->getTag()) . ',' . $db->Quote('*') . ')');
		}

		// continue query
		$query->where($db->quoteName('a.access')." IN (" . implode( ',', $authorised ) . ")");
		$query->order($db->quoteName('a.created') . ' DESC')
		->setLimit($count);

		$db->setQuery($query);
		$items = $db->loadObjectList();

		foreach ($items as &$item) {
			$item->slug    	= $item->id . ':' . $item->alias;
			$item->catslug 	= $item->catid . ':' . $item->category_alias;
			$item->username = JFactory::getUser($item->created_by)->name;

			$item->link 	= urldecode(JRoute::_(K2HelperRoute::getItemRoute($item->id.':'.urlencode($item->alias), $item->catid.':'.urlencode($item->category_alias))));

			$date = JFactory::getDate($item->modified);
			$timestamp = '?t='.$date->toUnix();

			$item->image_thumbnail = false;
			if (JFile::exists(JPATH_SITE.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.md5("Image".$item->id).'_XS.jpg'))
			{
				$item->image_thumbnail = true;
				$item->imageXSmall = JURI::base(true).'/media/k2/items/cache/'.md5("Image".$item->id).'_XS.jpg';
				if ($componentParams->get('imageTimestamp'))
				{
					$item->imageXSmall .= $timestamp;
				}
			}

			if (JFile::exists(JPATH_SITE.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.md5("Image".$item->id).'_S.jpg'))
			{
				$item->image_thumbnail = true;
				$item->image_small = JURI::base(true).'/media/k2/items/cache/'.md5("Image".$item->id).'_S.jpg';
				if ($componentParams->get('imageTimestamp'))
				{
					$item->image_small .= $timestamp;
				}
			}

			if (JFile::exists(JPATH_SITE.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.md5("Image".$item->id).'_M.jpg'))
			{
				$item->image_thumbnail = true;
				$item->image_medium = JURI::base(true).'/media/k2/items/cache/'.md5("Image".$item->id).'_M.jpg';
				if ($componentParams->get('imageTimestamp'))
				{
					$item->image_medium .= $timestamp;
				}
			}

			if (JFile::exists(JPATH_SITE.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.md5("Image".$item->id).'_L.jpg'))
			{
				$item->image_thumbnail = true;
				$item->image_large = JURI::base(true).'/media/k2/items/cache/'.md5("Image".$item->id).'_L.jpg';
				if ($componentParams->get('imageTimestamp'))
				{
					$item->image_large .= $timestamp;
				}
			}

			if (JFile::exists(JPATH_SITE.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.md5("Image".$item->id).'_Generic.jpg'))
			{
				$item->image_thumbnail = true;
				$item->imageGeneric = JURI::base(true).'/media/k2/items/cache/'.md5("Image".$item->id).'_Generic.jpg';
				if ($componentParams->get('imageTimestamp'))
				{
					$item->imageGeneric .= $timestamp;
				}
			}

		}

		return $items;
	}

	public static function getCategories($parent_id = 0, $include_subcategories = true, $child = false, $cats = array() ) {

		$app = JFactory::getApplication();
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select('*')
			->from($db->quoteName('#__k2_categories'))
			->where($db->quoteName('published') . ' = ' . $db->quote(1))
			->where($db->quoteName('access')." IN (" . implode( ',', JFactory::getUser()->getAuthorisedViewLevels() ) . ")")
			->where($db->quoteName('language')." IN (" . $db->Quote(JFactory::getLanguage()->getTag()).", ".$db->Quote('*') . ")")
			//->where($db->quoteName('parent') . ' = ' . $db->quote($parent_id))
			->where($db->quoteName('parent')." IN (" . implode( ',', $parent_id ) . ")")
			->order($db->quoteName('ordering') . ' ASC');

		$db->setQuery($query);

		$rows = $db->loadObjectList();
		foreach ($rows as $row) {
			if($include_subcategories) {
				array_push($cats, $row->id);
				if (self::hasChildren($row->id)) {
					$cats = self::getCategories(array($row->id), $include_subcategories, true, $cats);
				}
			}
		}
		return $cats;
	}

	private static function hasChildren($parent_id = 1) {
		$app = JFactory::getApplication();
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select('*')
			->from($db->quoteName('#__k2_categories'))
			->where($db->quoteName('published') . ' = ' . $db->quote(1))
			->where($db->quoteName('access')." IN (" . implode( ',', JFactory::getUser()->getAuthorisedViewLevels() ) . ")")
			->where($db->quoteName('language')." IN (" . $db->Quote(JFactory::getLanguage()->getTag()).", ".$db->Quote('*') . ")")
			->where($db->quoteName('parent') . ' = ' . $db->quote($parent_id))
			->order($db->quoteName('ordering') . ' DESC');

		$db->setQuery($query);

		$childrens = $db->loadObjectList();

		if(count((array) $childrens)) {
			return true;
		}

		return false;
	}

	public static function getcatTree(){
		$db = JFactory::getDBO();
		$query = 'SELECT m.* FROM #__k2_categories m WHERE trash = 0 ORDER BY parent, ordering';
		$db->setQuery($query);
		$mitems = $db->loadObjectList();
		$children = array();
		if ($mitems)
		{
				foreach ($mitems as $v)
				{
						if (K2_JVERSION != '15')
						{
								$v->title = $v->name;
								$v->parent_id = $v->parent;
						}
						$pt = $v->parent;
						$list = @$children[$pt] ? $children[$pt] : array();
						array_push($list, $v);
						$children[$pt] = $list;
				}
		}
		$list = JHTML::_('menu.treerecurse', 0, '', array(), $children, 9999, 0, 0);
		$mitems = array();

		foreach ($list as $item)
		{
				$item->treename = JString::str_ireplace('&#160;', '- ', $item->treename);
				$mitems[] = JHTML::_('select.option', $item->id, '   '.$item->treename);
		}

		return $mitems;
	}


}
