<?php
/**
 * @package Tag View Feature for Joomla!
 * @copyright Copyright (c) 2016 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * HTML View class for the Content component.
 */
class ContentViewTag extends JViewLegacy
{
  /**
   * Execute and display a template script.
   *
   * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
   *
   * @return  mixed  A string if successful, otherwise a Error object.
   */

  /**
   * State data
   *
   * @var    \Joomla\Registry\Registry
   * @since  3.2
   */
  protected $state;

  /**
   * Tag items data
   *
   * @var    array
   * @since  3.2
   */
  protected $items;

  /**
   * Pagination object
   *
   * @var    JPagination
   * @since  3.2
   */
  protected $pagination;

  /**
   * @var    array  Array of leading items for blog display
   * @since  3.2
   */
  protected $lead_items = array();

  /**
   * @var    array  Array of intro (multicolumn display) items for blog display
   * @since  3.2
   */
  protected $intro_items = array();

  /**
   * @var    array  Array of links in blog display
   * @since  3.2
   */
  protected $link_items = array();

  /**
   * @var    integer  Number of columns in a multi column display
   * @since  3.2
   */
  protected $columns = 1;

  /**
   * @var    string  The name of the extension for the category
   * @since  3.2
   */
  protected $extension = 'com_content';

  public $params;
  public $tag;
  public $tagMaxLevel;
  protected $children;
  protected $user;

  public function display($tpl = null)
  {
    $app    = JFactory::getApplication();
    $user   = JFactory::getUser();
    $this->params = $app->getParams();

    // Get some data from the models
    $this->state = $this->get('State');
    $this->items = $this->get('Items');
    $this->tag = $this->get('Tag');
    $this->children = $this->get('Children');
    $this->pagination = $this->get('Pagination');
    $this->tagMaxLevel = $this->params->get('tag_max_level');

    // Prepare the data
    // Get the metrics for the structural page layout.
    $numLeading = $this->params->def('num_leading_articles', 1);
    $numIntro   = $this->params->def('num_intro_articles', 4);
    $numLinks   = $this->params->def('num_links', 4);

    //Get the user object.
    $this->user = JFactory::getUser();

    // Prepare the data.
    // Compute the article slugs.
    foreach($this->items as $item) {
      $item->slug = $item->alias ? ($item->id.':'.$item->alias) : $item->id;
      $item->catslug = $item->category_alias ? ($item->catid.':'.$item->category_alias) : $item->catid;
      $item->parent_slug = ($item->parent_alias) ? ($item->parent_id.':'.$item->parent_alias) : $item->parent_id;
      // No link for ROOT category 
      if($item->parent_alias == 'root') {
	$item->parent_slug = null;
      }

      $item->event = new stdClass;

      $dispatcher = JEventDispatcher::getInstance();

      // Old plugins: Ensure that text property is available
      if (!isset($item->text))
      {
	      $item->text = $item->introtext;
      }

      JPluginHelper::importPlugin('content');
      $dispatcher->trigger('onContentPrepare', array ('com_content.category', &$item, &$item->params, 0));

      // Old plugins: Use processed text as introtext
      $item->introtext = $item->text;

      $results = $dispatcher->trigger('onContentAfterTitle', array('com_content.category', &$item, &$item->params, 0));
      $item->event->afterDisplayTitle = trim(implode("\n", $results));

      $results = $dispatcher->trigger('onContentBeforeDisplay', array('com_content.category', &$item, &$item->params, 0));
      $item->event->beforeDisplayContent = trim(implode("\n", $results));

      $results = $dispatcher->trigger('onContentAfterDisplay', array('com_content.category', &$item, &$item->params, 0));
      $item->event->afterDisplayContent = trim(implode("\n", $results));
    }

    // Check for layout override only if this is not the active menu item
    // If it is the active menu item, then the view and tag id will match
    $app = JFactory::getApplication();
    $active = $app->getMenu()->getActive();
    $menus = $app->getMenu();

    //The tag has no itemId and thus is not linked to any menu item. 
    if((!$active) || ((strpos($active->link, 'view=tag') === false) ||
		      (strpos($active->link, '&id='.(string)$this->tag->id) === false))) {
      // Get the layout from the merged tag params
      if($layout = $this->params->get('tag_layout')) {
	$this->setLayout($layout);
      }
    }
    // At this point, we are in a menu item, so we don't override the layout
    elseif(isset($active->query['layout'])) {
      // We need to set the layout from the query in case this is an alternative menu item (with an alternative layout)
      $this->setLayout($active->query['layout']);
    }
    //Note: In case the layout parameter is not found within the query, the default layout
    //will be set.

    // For blog layouts, preprocess the breakdown of leading, intro and linked songs.
    // This makes it much easier for the designer to just interrogate the arrays.
    if(($this->params->get('layout_type') == 'blog') || ($this->getLayout() == 'blog')) {
      foreach($this->items as $i => $item) {
	if($i < $numLeading) {
	  $this->lead_items[] = $item;
	}
	elseif($i >= $numLeading && $i < $numLeading + $numIntro) {
	  $this->intro_items[] = $item;
	}
	elseif($i < $numLeading + $numIntro + $numLinks) {
	  $this->link_items[] = $item;
	}
	else {
	  continue;
	}
      }

      $this->columns = max(1, $this->params->def('num_columns', 1));

      $order = $this->params->def('multi_column_order', 1);

      if($order == 0 && $this->columns > 1) {
	// Call order down helper
	$this->intro_items = ContentHelperQuery::orderDownColumns($this->intro_items, $this->columns);
      }
    }

    //Set the name of the active layout in params, (needed for the filter ordering layout).
    $this->params->set('active_layout', $this->getLayout());
    $this->prepareDocument();

    return parent::display($tpl);
  }


  /** 
   * Method to prepares the document
   *
   * @return  void
   *
   * @since   3.2
   */
  protected function prepareDocument()
  {
    $app = JFactory::getApplication();
    // Because the application sets a default page title,
    // we need to get it from the menu item itself
    $menus = $app->getMenu();
    $menu = $menus->getActive();

    if($menu) {
      $this->params->def('page_heading', $this->params->get('page_title', $menu->title));
    }   

    $title = $this->params->get('page_title', '');

    // Check for empty title and add site name if param is set
    if(empty($title)) {
      $title = $app->get('sitename');
    }   
    elseif($app->get('sitename_pagetitles', 0) == 1) {
      $title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
    }   
    elseif($app->get('sitename_pagetitles', 0) == 2) {
      $title = JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
    }   

    if(empty($title)) {
      $title = $this->tag->title;
    }

    $this->document->setTitle($title);

    if($this->tag->metadesc) {
      $this->document->setDescription($this->tag->metadesc);
    }
    elseif($this->params->get('menu-meta_description')) {
      $this->document->setDescription($this->params->get('menu-meta_description'));
    }

    if($this->tag->metakey) {
      $this->document->setMetadata('keywords', $this->tag->metakey);
    }
    elseif($this->params->get('menu-meta_keywords')) {
      $this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
    }

    if($this->params->get('robots')) {
      $this->document->setMetadata('robots', $this->params->get('robots'));
    }

    if(!is_object($this->tag->metadata)) {
      $this->tag->metadata = new Registry($this->tag->metadata);
    }

    $mdata = $this->tag->metadata->toArray();

    if(($app->get('MetaAuthor') == '1') && $mdata['author']) {
      $this->document->setMetaData('author', $mdata['author']);
    }
    foreach($mdata as $k => $v) {
      if($v) {
        $this->document->setMetadata($k, $v);
      }
    }

    return;
  }
}
