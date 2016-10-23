<?php
/**
 * @package Tag View Feature for Joomla!
 * @copyright Copyright (c) 2016 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

defined('_JEXEC') or die;

require_once JPATH_COMPONENT_SITE.'/helpers/query.php';

/**
 * Content Component Model
 *
 * @package     Joomla.Site
 * @subpackage  com_content
 */
class ContentModelTag extends JModelList
{
  /**
   * Method to get a list of items.
   *
   * @return  mixed  An array of objects on success, false on failure.
   */

  /**
   * Constructor.
   *
   * @param   array  An optional associative array of configuration settings.
   * @see     JController
   * @since   1.6
   */
  public function __construct($config = array())
  {
    if(empty($config['filter_fields'])) {
      $config['filter_fields'] = array(
	      'id', 'a.id',
	      'title', 'a.title',
	      'alias', 'a.alias',
	      'checked_out', 'a.checked_out',
	      'checked_out_time', 'a.checked_out_time',
	      'catid', 'a.catid', 'category_title',
	      'state', 'a.state',
	      'access', 'a.access', 'access_level',
	      'created', 'a.created',
	      'created_by', 'a.created_by',
	      'modified', 'a.modified',
	      'ordering', 'a.ordering',
	      'featured', 'a.featured',
	      'language', 'a.language',
	      'hits', 'a.hits',
	      'publish_up', 'a.publish_up',
	      'publish_down', 'a.publish_down',
	      'author', 'a.author',
	      'filter_tag'
      );
    }

    parent::__construct($config);
  }


  /**
   * Method to auto-populate the model state.
   *
   * Note. Calling getState in this method will result in recursion.
   *
   * @since   1.6
   */
  protected function populateState($ordering = null, $direction = null)
  {
    $app = JFactory::getApplication('site');

    //Get and set the current tag id.
    $pk = $app->input->getInt('id');
    $this->setState('tag.id', $pk);

    //getParams function return global parameters overrided by the menu parameters (if any).
    //Note: Some specific parameters of this menu are not returned.
    $params = $app->getParams();
    $menuParams = new JRegistry;

    //Get the menu with its specific parameters.
    if($menu = $app->getMenu()->getActive()) {
      $menuParams->loadString($menu->params);
    }

    //Merge Global and Menu Item params into a new object.
    $mergedParams = clone $menuParams;
    $mergedParams->merge($params);

    // Load the parameters in the session.
    $this->setState('params', $mergedParams);

    // process show_noauth parameter

    //The user is not allowed to see the registered articles unless he has the proper view permissions.
    if(!$params->get('show_noauth')) {
      //Set the access filter to true. This way the SQL query checks against the user
      //view permissions and fetchs only the articles this user is allowed to see.
      $this->setState('filter.access', true);
    }
    //The user is allowed to see any of the registred articles (ie: introtext as a teaser). 
    else {
      //The user is allowed to see all the articles or some of them.
      //All of the articles are returned and it's up to thelayout to 
      //deal with the access (ie: redirect the user to login form when Read more
      //button is clicked).
      $this->setState('filter.access', false);
    }

    // List state information
    //Get the number of articles to display per page.
    //Note: The LIMIT clause is added in the setQuery function: libraries/joomla/database/query/driver.php
    $limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'uint');
    $this->setState('list.limit', $limit);

    //Get the limitstart variable (used for the pagination) from the form variable.
    $limitstart = $app->input->get('limitstart', 0, 'uint');
    $this->setState('list.start', $limitstart);

    // Optional filter text
    $this->setState('list.filter', $app->input->getString('filter-search'));

    //Check if the user is root. 
    $user = JFactory::getUser();
    if(!$user->get('isRoot')) {
      // Limit to published for people who are not super user.
      $this->setState('filter.published', 1);

      // Filter by start and end dates.
      $this->setState('filter.publish_date', true);
    }
    else {
      //Super users can access published, unpublished and archived articles.
      $this->setState('filter.published', array(0, 1, 2));
    }

    $this->setState('filter.language', JLanguageMultilang::isEnabled());

    $this->setState('layout', $app->input->getString('layout'));

    // Set the featured articles state
    $this->setState('filter.featured', $params->get('show_featured'));
  }


  /**
   * Method to get a list of items.
   *
   * @return  mixed  An array of objects on success, false on failure.
   */
  public function getItems()
  {
    // Invoke the parent getItems method (using the getListQuery method) to get the main list
    $items = parent::getItems();

    if(!$items) {
      return array();
    }

    $input = JFactory::getApplication()->input;
    //Get some user data.
    $user = JFactory::getUser();
    $userId = $user->get('id');
    $guest = $user->get('guest');
    $groups = $user->getAuthorisedViewLevels();

    // Convert the params field into an object, saving original in _params
    foreach($items as $key => $item) {
      //Get the article parameters only.
      $articleParams = new JRegistry;
      $articleParams->loadString($item->attribs);
      //Set the params attribute, ie: the merged global and menu parameters set
      //in the populateState function.
      $item->params = clone $this->getState('params');
      $item->alternative_readmore = $articleParams->get('alternative_readmore');

      // For Blog layout, article params override menu item params only if menu param='use_article'.
      // Otherwise, menu item params control the layout.
      // If menu item is 'use_article' and there is no article param, use global.
      if($input->getString('layout') == 'blog' || $this->getState('params')->get('layout_type') == 'blog') {
	// Create an array of just the params set to 'use_article'
	$menuParamsArray = $this->getState('params')->toArray();
	$articleArray = array();

	foreach($menuParamsArray as $key => $value) {
	  if($value === 'use_article') {
	    // If the article has a value, use it
	    if($articleParams->get($key) != '') {
	      // Get the value from the article
	      $articleArray[$key] = $articleParams->get($key);
	    }
	    else {
	      // Otherwise, use the global value
	      $articleArray[$key] = $globalParams->get($key);
	    }
	  }
	}

	// Merge the selected article params
	if(count($articleArray) > 0) {
	  $articleParams = new JRegistry;
	  $articleParams->loadArray($articleArray);
	  $item->params->merge($articleParams);
	}
      }
      else { //Default layout (list).
	// Merge all of the article params.
	$item->params->merge($articleParams);
      }

      // Compute the asset access permissions.
      // Technically guest could edit a article, but lets not check that to improve performance a little.
      if(!$guest) {
	$asset = 'com_content.article.'.$item->id;

	// Check general edit permission first.
	if($user->authorise('core.edit', $asset)) {
	  $item->params->set('access-edit', true);
	}
	// Now check if edit.own is available.
	elseif(!empty($userId) && $user->authorise('core.edit.own', $asset)) {
	  // Check for a valid user and that they are the owner.
	  if($userId == $item->created_by) {
	    $item->params->set('access-edit', true);
	  }
	}
      }

      $access = $this->getState('filter.access');
      //Set the access view parameter.
      if($access) {
	// If the access filter has been set, we already have only the articles this user can view.
	$item->params->set('access-view', true);
      }
      else { // If no access filter is set, the layout takes some responsibility for display of limited information.
	if($item->catid == 0 || $item->category_access === null) {
	  //In case the article is not linked to a category, we just check permissions against the article access.
	  $item->params->set('access-view', in_array($item->access, $groups));
	}
	else { //Check the user permissions against the article access as well as the category access.
	  $item->params->set('access-view', in_array($item->access, $groups) && in_array($item->category_access, $groups));
	}
      }

      //Set the type of date to display, (default layout only).
      if($this->getState('params')->get('layout_type') != 'blog'
	  && $this->getState('params')->get('list_show_date')
	  && $this->getState('params')->get('order_date')) {
	switch($this->getState('params')->get('order_date')) {
	  case 'modified':
		  $item->displayDate = $item->modified;
		  break;

	  case 'published':
		  $item->displayDate = ($item->publish_up == 0) ? $item->created : $item->publish_up;
		  break;

	  default: //created
		  $item->displayDate = $item->created;
	}
      }

      // Get the tags
      $item->tags = new JHelperTags;
      $item->tags->getItemTags('com_content.article', $item->id);
    }

    return $items;
  }


  /**
   * Method to build an SQL query to load the list data (article items).
   *
   * @return  string    An SQL query
   * @since   1.6
   */
  protected function getListQuery()
  {
    $user = JFactory::getUser();
    $groups = implode(',', $user->getAuthorisedViewLevels());

    // Create a new query object.
    $db = $this->getDbo();
    $query = $db->getQuery(true);

    // Select required fields from the categories.
    $query->select($this->getState('list.select', 'a.id, a.title, a.alias, a.introtext, a.fulltext, a.checked_out, tm.tag_id,'.
				   'a.checked_out_time, a.catid, a.created, a.created_by, a.created_by_alias, a.state,'.
				   // Use created if modified is 0
				   'CASE WHEN a.modified='.$db->quote($db->getNullDate()).' THEN a.created ELSE a.modified END AS modified,'.
				   'a.modified_by, '.
				   // Use created if publish_up is 0
				   'CASE WHEN a.publish_up='.$db->quote($db->getNullDate()).' THEN a.created ELSE a.publish_up END AS publish_up,'.
				   'a.publish_down, a.images, a.urls, a.attribs, a.metadata, a.metakey, a.metadesc, a.access,'.
				   'a.hits, a.xreference, a.featured, a.language,'.' '.$query->length('a.fulltext').' AS readmore'))
	  ->from($db->quoteName('#__content').' AS a')
	  ->join('LEFT', '#__content_tag_map AS tm ON a.id=tm.article_id')
	  //Display articles labeled with the current tag.
	  ->where('tm.tag_id='.(int)$this->getState('tag.id'));

    // Join on tag table.
    $query->select('ta.title AS tag_title, ta.alias AS tag_alias')
	  ->join('LEFT', '#__tags AS ta ON ta.id='.(int)$this->getState('tag.id'))
	  //Ensure the current tag is published.
	  ->where('ta.published=1');

    // Join over the tags to get parent tag title.
    $query->select('tag_parent.title AS tag_parent_title, tag_parent.id AS tag_parent_id,'.
		   'tag_parent.path AS tag_parent_route, tag_parent.alias AS tag_parent_alias')
	  ->join('LEFT', '#__tags as tag_parent ON tag_parent.id = ta.parent_id');

    // Join on category table.
    $query->select('ca.title AS category_title, ca.alias AS category_alias, ca.access AS category_access')
	  ->join('LEFT', '#__categories AS ca ON ca.id = a.catid')
	  //Ensure the category of the article is published.
	  ->where('ca.published=1');

    // Join over the categories to get parent category title.
    $query->select('parent.title as parent_title, parent.id as parent_id, parent.path as parent_route, parent.alias as parent_alias')
	  ->join('LEFT', '#__categories as parent ON parent.id = ca.parent_id');

    // Join on voting table
    $query->select('ROUND(v.rating_sum / v.rating_count, 0) AS rating, v.rating_count AS rating_count')
	    ->join('LEFT', '#__content_rating AS v ON a.id = v.content_id');

    // Join over the users.
    $query->select('us.name AS author')
	  ->join('LEFT', '#__users AS us ON us.id = a.created_by');

    // Join over the asset groups.
    $query->select('al.title AS access_level');
    $query->join('LEFT', '#__viewlevels AS al ON al.id = a.access');

    // Filter by access level.
    if($access = $this->getState('filter.access')) {
      $query->where('a.access IN ('.$groups.')')
	    //Category access is also taken in account.
	    ->where('ca.access IN ('.$groups.')');
    }

    // Filter by state
    $published = $this->getState('filter.published');
    if(is_numeric($published)) {
      //Users are only allowed to see published articles.
      $query->where('a.state='.(int)$published);
    }
    elseif(is_array($published)) {
      //Only super users are allowed to see articles with different states.
      JArrayHelper::toInteger($published);
      $published = implode(',', $published);
      $query->where('a.state IN ('.$published.')');
    }

    //Do not show expired articles to users who are not Root.
    if($this->getState('filter.publish_date')) {
      // Filter by start and end dates.
      $nullDate = $db->quote($db->getNullDate());
      $nowDate = $db->quote(JFactory::getDate('now', JFactory::getConfig()->get('offset'))->toSql(true));

      $query->where('(a.publish_up = '.$nullDate.' OR a.publish_up <= '.$nowDate.')')
	    ->where('(a.publish_down = '.$nullDate.' OR a.publish_down >= '.$nowDate.')');
    }

    // Filter by language
    if($this->getState('filter.language')) {
      $query->where('a.language IN ('.$db->quote(JFactory::getLanguage()->getTag()).','.$db->quote('*').')');
    }

    // Filter by featured state
    $featured = $this->getState('filter.featured');

    switch($featured) {
      case 'hide':
	      $query->where('a.featured = 0');
	      break;

      case 'only':
	      $query->where('a.featured = 1');
	      break;

      case 'show':
      default:
	      // Normally we do not discriminate
	      // between featured/unfeatured items.
	      break;
    }

    // Filter by search in title
    $search = $this->getState('list.filter');
    //Get the field to search by.
    $field = $this->getState('params')->get('filter_field');
    if(!empty($search)) {
      $search = $db->quote('%'.$db->escape($search, true).'%');
      $query->where('(a.'.$field.' LIKE '.$search.')');
    }

    //Get the articles ordering by default set in the menu options. (Note: sec stands for secondary). 
    $articleOrderBy = $this->getState('params')->get('orderby_sec', 'rdate');
    //If articles are sorted by date (ie: date, rdate), order_date defines
    //which type of date should be used (ie: created, modified or publish_up).
    $articleOrderDate = $this->getState('params')->get('order_date');
    //Get the field to use in the ORDER BY clause according to the orderby_sec option.
    $orderBy = ContentHelperQuery::orderbySecondary($articleOrderBy, $articleOrderDate, true);

    $query->order($orderBy);

    return $query;
  }


  //Get the current tag.
  public function getTag()
  {
    $tagId = $this->getState('tag.id');
    $db = $this->getDbo();
    $query = $db->getQuery(true);
    $query->select('*')
	  ->from('#__tags')
	  ->where('id='.(int)$tagId);
    $db->setQuery($query);
    $tag = $db->loadObject();

    $this->setState('tag.level', $tag->level);

    $images = new JRegistry;
    $images->loadString($tag->images);
    $tag->images = $images;

    return $tag;
  }


  public function getChildren()
  {
    $tagId = $this->getState('tag.id');
    $user = JFactory::getUser();
    $groups = implode(',', $user->getAuthorisedViewLevels());

    //Add one to the start level as we don't want the current tag in the result.
    $startLevel = $this->getState('tag.level', 1) + 1;
    $endLevel = $this->getState('params')->get('tag_max_level', 0);

    if($endLevel > 0) { //Compute the end level from the start level.
      $endLevel = $startLevel + $endLevel;
    }
    elseif($endLevel == -1) { //Display all the subtags.
      $endLevel = 10;
    }

    //Ensure subtags are required.
    if($endLevel) {
      //Get the tag order type.
      $tagOrderBy = $this->getState('params')->get('orderby_pri');
      $orderBy = ContentHelperQuery::orderbyPrimary($tagOrderBy, true);

      $db = $this->getDbo();
      $query = $db->getQuery(true);
      $query->select('DISTINCT n.*')
	    ->from('#__tags AS n, #__tags AS p')
	    ->where('n.lft BETWEEN p.lft AND p.rgt')
	    ->where('n.level >= '.(int)$startLevel.' AND n.level <= '.(int)$endLevel)
	    ->where('n.access IN('.$groups.')')
	    ->where('n.published=1')
	    ->where('p.id='.(int)$tagId);

      if(!empty($orderBy)) {
	//Remove the comma and space from the end of the string.
	$orderBy = substr($orderBy, 0, -2);
	$query->order($orderBy);
      }

      $db->setQuery($query);
      $children = $db->loadObjectList();

      if(empty($children)) {
        return $children;
      }

      if($this->getState('params')->get('show_tagged_num_articles', 0)) {
	//Get the tag children ids.
	$ids = array();
	foreach($children as $child) {
	  $ids[] = $child->id;
	}

	//Compute the number of articles for each tag.
	$query->clear()
	      ->select('tm.tag_id, COUNT(*) AS numitems')
	      ->from('#__content_tag_map AS tm')
	      //We need content and categories table to check accesses.
	      ->join('LEFT', '#__content AS a ON a.id=tm.article_id')
	      ->join('LEFT', '#__categories AS ca ON ca.id=a.catid')
	      ->where('a.access IN('.$groups.')')
	      ->where('ca.access IN('.$groups.')');

	// Filter by state
	$published = $this->getState('filter.published');
	if(is_numeric($published)) {
	  //Only published articles are counted when user is not Root.
	  $query->where('a.state='.(int)$published);
	}
	elseif(is_array($published)) {
	  //Articles with different states are also taken in account for super users.
	  JArrayHelper::toInteger($published);
	  $published = implode(',', $published);
	  $query->where('a.state IN ('.$published.')');
	}

	//Do not count expired articles when user is not Root.
	if($this->getState('filter.publish_date')) {
	  // Filter by start and end dates.
	  $nullDate = $db->quote($db->getNullDate());
	  $nowDate = $db->quote(JFactory::getDate('now', JFactory::getConfig()->get('offset'))->toSql(true));

	  $query->where('(a.publish_up = '.$nullDate.' OR a.publish_up <= '.$nowDate.')')
		->where('(a.publish_down = '.$nullDate.' OR a.publish_down >= '.$nowDate.')');
	}

	$query->where('tm.tag_id IN('.implode(',', $ids).') GROUP BY tm.tag_id');
	$db->setQuery($query);
	$tags = $db->loadObjectList('tag_id');

	//Set the numitems attribute.
	foreach($children as $child) {
	  $child->numitems = 0;

	  if(isset($tags[$child->id])) {
	    $child->numitems = $tags[$child->id]->numitems;
	  }
	}
      }

      return $children;
    }

    return array();
  }


  /**
   * Increment the hit counter for the tag.
   *
   * @param   int  $pk  Optional primary key of the tag to increment.
   *
   * @return  boolean True if successful; false otherwise and internal error set.
   */
  public function hit($pk = 0)
  {
    $input = JFactory::getApplication()->input;
    $hitcount = $input->getInt('hitcount', 1);

    if($hitcount) {
      $pk = (!empty($pk)) ? $pk : (int) $this->getState('tag.id');

      $table = JTable::getInstance('Tag', 'JTable');
      $table->load($pk);
      $table->hit($pk);
    }

    return true;
  }
}



