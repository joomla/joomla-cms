<?php
/**
 * @version		$Id: pagination.php 10707 2008-08-21 09:52:47Z eddieajau $
 * @package		Joomla.Framework
 * @subpackage	HTML
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// No direct access
defined('JPATH_BASE') or die;

/**
 * Pagination Class.  Provides a common interface for content pagination for the
 * Joomla! Framework
 *
 * @package 	Joomla.Framework
 * @subpackage	HTML
 * @since		1.5
 */
class JPagination extends JObject
{
	/**
	 * The record number to start dislpaying from
	 *
	 * @access public
	 * @var int
	 */
	public $limitstart = null;

	/**
	 * Number of rows to display per page
	 *
	 * @access public
	 * @var int
	 */
	public $limit = null;

	/**
	 * Total number of rows
	 *
	 * @access public
	 * @var int
	 */
	public $total = null;

	/**
	 * View all flag
	 *
	 * @access protected
	 * @var boolean
	 */
	protected $_viewall = false;

	/**
	 * Constructor
	 *
	 * @param	int		The total number of items
	 * @param	int		The offset of the item to start at
	 * @param	int		The number of items to display per page
	 */
	function __construct($total, $limitstart, $limit)
	{
		// Value/Type checking
		$this->total		= (int) $total;
		$this->limitstart	= (int) max($limitstart, 0);
		$this->limit		= (int) max($limit, 0);

		if ($this->limit > $this->total) {
			$this->limitstart = 0;
		}

		if (!$this->limit)
		{
			$this->limit = $total;
			$this->limitstart = 0;
		}

		if ($this->limitstart > $this->total) {
			$this->limitstart -= $this->limitstart % $this->limit;
		}

		// Set the total pages and current page values
		if ($this->limit > 0)
		{
			$this->set('pages.total', ceil($this->total / $this->limit));
			$this->set('pages.current', ceil(($this->limitstart + 1) / $this->limit));
		}

		// Set the pagination iteration loop values
		$displayedPages	= 10;
		$this->set('pages.start', (floor(($this->get('pages.current') -1) / $displayedPages)) * $displayedPages +1);
		if ($this->get('pages.start') + $displayedPages -1 < $this->get('pages.total')) {
			$this->set('pages.stop', $this->get('pages.start') + $displayedPages -1);
		} else {
			$this->set('pages.stop', $this->get('pages.total'));
		}

		// If we are viewing all records set the view all flag to true
		if ($this->limit == $total) {
			$this->_viewall = true;
		}
	}

	/**
	 * Return the rationalised offset for a row with a given index.
	 *
	 * @access	public
	 * @param	int		$index The row index
	 * @return	int		Rationalised offset for a row with a given index
	 * @since	1.5
	 */
	public function getRowOffset($index)
	{
		return $index +1 + $this->limitstart;
	}

	/**
	 * Return the pagination data object, only creating it if it doesn't already exist
	 *
	 * @access	public
	 * @return	object	Pagination data object
	 * @since	1.5
	 */
	public function getData()
	{
		static $data;
		if (!is_object($data)) {
			$data = $this->_buildDataObject();
		}
		return $data;
	}

	/**
	 * Create and return the pagination pages counter string, ie. Page 2 of 4
	 *
	 * @access	public
	 * @return	string	Pagination pages counter string
	 * @since	1.5
	 */
	public function getPagesCounter()
	{
		// Initialize variables
		$html = null;
		if ($this->get('pages.total') > 1) {
			$html .= JText::_('Page')." ".$this->get('pages.current')." ".JText::_('of')." ".$this->get('pages.total');
		}
		return $html;
	}

	/**
	 * Create and return the pagination result set counter string, ie. Results 1-10 of 42
	 *
	 * @access	public
	 * @return	string	Pagination result set counter string
	 * @since	1.5
	 */
	public function getResultsCounter()
	{
		// Initialize variables
		$html = null;
		$fromResult = $this->limitstart + 1;

		// If the limit is reached before the end of the list
		if ($this->limitstart + $this->limit < $this->total) {
			$toResult = $this->limitstart + $this->limit;
		} else {
			$toResult = $this->total;
		}

		// If there are results found
		if ($this->total > 0) {
			$msg = JText::sprintf('Results of', $fromResult, $toResult, $this->total);
			$html .= "\n".$msg;
		} else {
			$html .= "\n".JText::_('No records found');
		}

		return $html;
	}

	/**
	 * Create and return the pagination page list string, ie. Previous, Next, 1 2 3 ... x
	 *
	 * @access	public
	 * @return	string	Pagination page list string
	 * @since	1.0
	 */
	public function getPagesLinks()
	{
		$appl = JFactory::getApplication();

		$lang = &JFactory::getLanguage();

		// Build the page navigation list
		$data = $this->_buildDataObject();

		$list = array();

		$itemOverride = false;
		$listOverride = false;

		$chromePath = JPATH_THEMES.DS.$appl->getTemplate().DS.'html'.DS.'pagination.php';
		if (file_exists($chromePath))
		{
			require_once $chromePath;
			if (function_exists('pagination_item_active') && function_exists('pagination_item_inactive')) {
				$itemOverride = true;
			}
			if (function_exists('pagination_list_render')) {
				$listOverride = true;
			}
		}

		// Build the select list
		if ($data->all->base !== null) {
			$list['all']['active'] = true;
			$list['all']['data'] = ($itemOverride) ? pagination_item_active($data->all) : $this->_item_active($data->all);
		} else {
			$list['all']['active'] = false;
			$list['all']['data'] = ($itemOverride) ? pagination_item_inactive($data->all) : $this->_item_inactive($data->all);
		}

		if ($data->start->base !== null) {
			$list['start']['active'] = true;
			$list['start']['data'] = ($itemOverride) ? pagination_item_active($data->start) : $this->_item_active($data->start);
		} else {
			$list['start']['active'] = false;
			$list['start']['data'] = ($itemOverride) ? pagination_item_inactive($data->start) : $this->_item_inactive($data->start);
		}
		if ($data->previous->base !== null) {
			$list['previous']['active'] = true;
			$list['previous']['data'] = ($itemOverride) ? pagination_item_active($data->previous) : $this->_item_active($data->previous);
		} else {
			$list['previous']['active'] = false;
			$list['previous']['data'] = ($itemOverride) ? pagination_item_inactive($data->previous) : $this->_item_inactive($data->previous);
		}

		$list['pages'] = array(); //make sure it exists
		foreach ($data->pages as $i => $page)
		{
			if ($page->base !== null) {
				$list['pages'][$i]['active'] = true;
				$list['pages'][$i]['data'] = ($itemOverride) ? pagination_item_active($page) : $this->_item_active($page);
			} else {
				$list['pages'][$i]['active'] = false;
				$list['pages'][$i]['data'] = ($itemOverride) ? pagination_item_inactive($page) : $this->_item_inactive($page);
			}
		}

		if ($data->next->base !== null) {
			$list['next']['active'] = true;
			$list['next']['data'] = ($itemOverride) ? pagination_item_active($data->next) : $this->_item_active($data->next);
		} else {
			$list['next']['active'] = false;
			$list['next']['data'] = ($itemOverride) ? pagination_item_inactive($data->next) : $this->_item_inactive($data->next);
		}
		if ($data->end->base !== null) {
			$list['end']['active'] = true;
			$list['end']['data'] = ($itemOverride) ? pagination_item_active($data->end) : $this->_item_active($data->end);
		} else {
			$list['end']['active'] = false;
			$list['end']['data'] = ($itemOverride) ? pagination_item_inactive($data->end) : $this->_item_inactive($data->end);
		}

		if ($this->total > $this->limit){
			return ($listOverride) ? pagination_list_render($list) : $this->_list_render($list);
		}
		else{
			return '';
		}
	}

	/**
	 * Return the pagination footer
	 *
	 * @access	public
	 * @return	string	Pagination footer
	 * @since	1.0
	 */
	public function getListFooter()
	{
		$appl = JFactory::getApplication();

		$list = array();
		$list['limit']			= $this->limit;
		$list['limitstart']		= $this->limitstart;
		$list['total']			= $this->total;
		$list['limitfield']		= $this->getLimitBox();
		$list['pagescounter']	= $this->getPagesCounter();
		$list['pageslinks']		= $this->getPagesLinks();

		$chromePath		= JPATH_THEMES.DS.$appl->getTemplate().DS.'html'.DS.'pagination.php';
		if (file_exists($chromePath))
		{
			require_once $chromePath;
			if (function_exists('pagination_list_footer')) {
				return pagination_list_footer($list);
			}
		}
		return $this->_list_footer($list);
	}

	/**
	 * Creates a dropdown box for selecting how many records to show per page
	 *
	 * @access	public
	 * @return	string	The html for the limit # input box
	 * @since	1.0
	 */
	public function getLimitBox()
	{
		$appl = JFactory::getApplication();

		// Initialize variables
		$limits = array ();

		// Make the option list
		for ($i = 5; $i <= 30; $i += 5) {
			$limits[] = JHtml::_('select.option', "$i");
		}
		$limits[] = JHtml::_('select.option', '50');
		$limits[] = JHtml::_('select.option', '100');
		$limits[] = JHtml::_('select.option', '0', JText::_('all'));

		$selected = $this->_viewall ? 0 : $this->limit;

		// Build the select list
		if ($appl->isAdmin()) {
			$html = JHtml::_('select.genericlist',  $limits, 'limit', 'class="inputbox" size="1" onchange="submitform();"', 'value', 'text', $selected);
		} else {
			$html = JHtml::_('select.genericlist',  $limits, 'limit', 'class="inputbox" size="1" onchange="this.form.submit()"', 'value', 'text', $selected);
		}
		return $html;
	}

	/**
	 * Return the icon to move an item UP
	 *
	 * @access	public
	 * @param	int		$i The row index
	 * @param	boolean	$condition True to show the icon
	 * @param	string	$task The task to fire
	 * @param	string	$alt The image alternate text string
	 * @return	string	Either the icon to move an item up or a space
	 * @since	1.0
	 */
	public function orderUpIcon($i, $condition = true, $task = 'orderup', $alt = 'Move Up', $enabled = true)
	{
		$alt = JText::_($alt);

		$html = '&nbsp;';
		if (($i > 0 || ($i + $this->limitstart > 0)) && $condition)
		{
			if ($enabled) {
				$html	= '<a href="#reorder" onclick="return listItemTask(\'cb'.$i.'\',\''.$task.'\')" title="'.$alt.'">';
				$html	.= '   <img src="images/uparrow.png" width="16" height="16" border="0" alt="'.$alt.'" />';
				$html	.= '</a>';
			} else {
				$html	= '<img src="images/uparrow0.png" width="16" height="16" border="0" alt="'.$alt.'" />';
			}
		}

		return $html;
	}

	/**
	 * Return the icon to move an item DOWN
	 *
	 * @access	public
	 * @param	int		$i The row index
	 * @param	int		$n The number of items in the list
	 * @param	boolean	$condition True to show the icon
	 * @param	string	$task The task to fire
	 * @param	string	$alt The image alternate text string
	 * @return	string	Either the icon to move an item down or a space
	 * @since	1.0
	 */
	public function orderDownIcon($i, $n, $condition = true, $task = 'orderdown', $alt = 'Move Down', $enabled = true)
	{
		$alt = JText::_($alt);

		$html = '&nbsp;';
		if (($i < $n -1 || $i + $this->limitstart < $this->total - 1) && $condition)
		{
			if ($enabled) {
				$html	= '<a href="#reorder" onclick="return listItemTask(\'cb'.$i.'\',\''.$task.'\')" title="'.$alt.'">';
				$html	.= '  <img src="images/downarrow.png" width="16" height="16" border="0" alt="'.$alt.'" />';
				$html	.= '</a>';
			} else {
				$html	= '<img src="images/downarrow0.png" width="16" height="16" border="0" alt="'.$alt.'" />';
			}
		}

		return $html;
	}

	protected function _list_footer($list)
	{
		// Initialize variables
		$html = "<div class=\"list-footer\">\n";

		$html .= "\n<div class=\"limit\">".JText::_('Display Num').$list['limitfield']."</div>";
		$html .= $list['pageslinks'];
		$html .= "\n<div class=\"counter\">".$list['pagescounter']."</div>";

		$html .= "\n<input type=\"hidden\" name=\"limitstart\" value=\"".$list['limitstart']."\" />";
		$html .= "\n</div>";

		return $html;
	}

	protected function _list_render($list)
	{
		// Initialize variables
		$html = null;

		// Reverse output rendering for right-to-left display
		$html .= '&lt;&lt; ';
		$html .= $list['start']['data'];
		$html .= ' &lt; ';
		$html .= $list['previous']['data'];
		foreach($list['pages'] as $page) {
			$html .= ' '.$page['data'];
		}
		$html .= ' '. $list['next']['data'];
		$html .= ' &gt;';
		$html .= ' '. $list['end']['data'];
		$html .= ' &gt;&gt;';

		return $html;
	}

	protected function _item_active(&$item)
	{
		$appl = JFactory::getApplication();
		if ($appl->isAdmin())
		{
			if ($item->base>0)
				return "<a title=\"".$item->text."\" onclick=\"javascript: document.adminForm.limitstart.value=".$item->base."; submitform();return false;\">".$item->text."</a>";
			else
				return "<a title=\"".$item->text."\" onclick=\"javascript: document.adminForm.limitstart.value=0; submitform();return false;\">".$item->text."</a>";
		} else {
			return "<a title=\"".$item->text."\" href=\"".$item->link."\" class=\"pagenav\">".$item->text."</a>";
		}
	}

	protected function _item_inactive(&$item)
	{
		$appl = JFactory::getApplication();
		if ($appl->isAdmin()) {
			return "<span>".$item->text."</span>";
		} else {
			return "<span class=\"pagenav\">".$item->text."</span>";
		}
	}

	/**
	 * Create and return the pagination data object
	 *
	 * @access	public
	 * @return	object	Pagination data object
	 * @since	1.5
	 */
	protected function _buildDataObject()
	{
		// Initialize variables
		$data = new stdClass();

		$data->all	= new JPaginationObject(JText::_('View All'));
		if (!$this->_viewall) {
			$data->all->base	= '0';
			$data->all->link	= JRoute::_("&limitstart=");
		}

		// Set the start and previous data objects
		$data->start	= new JPaginationObject(JText::_('Start'));
		$data->previous	= new JPaginationObject(JText::_('Prev'));

		if ($this->get('pages.current') > 1)
		{
			$page = ($this->get('pages.current') -2) * $this->limit;

			$page = $page == 0 ? '' : $page; //set the empty for removal from route

			$data->start->base	= '0';
			$data->start->link	= JRoute::_("&limitstart=");
			$data->previous->base	= $page;
			$data->previous->link	= JRoute::_("&limitstart=".$page);
		}

		// Set the next and end data objects
		$data->next	= new JPaginationObject(JText::_('Next'));
		$data->end	= new JPaginationObject(JText::_('End'));

		if ($this->get('pages.current') < $this->get('pages.total'))
		{
			$next = $this->get('pages.current') * $this->limit;
			$end  = ($this->get('pages.total') -1) * $this->limit;

			$data->next->base	= $next;
			$data->next->link	= JRoute::_("&limitstart=".$next);
			$data->end->base	= $end;
			$data->end->link	= JRoute::_("&limitstart=".$end);
		}

		$data->pages = array();
		$stop = $this->get('pages.stop');
		for ($i = $this->get('pages.start'); $i <= $stop; $i ++)
		{
			$offset = ($i -1) * $this->limit;

			$offset = $offset == 0 ? '' : $offset;  //set the empty for removal from route

			$data->pages[$i] = new JPaginationObject($i);
			if ($i != $this->get('pages.current') || $this->_viewall)
			{
				$data->pages[$i]->base	= $offset;
				$data->pages[$i]->link	= JRoute::_("&limitstart=".$offset);
			}
		}
		return $data;
	}
}

/**
 * Pagination object representing a particular item in the pagination lists
 *
 * @package 	Joomla.Framework
 * @subpackage	HTML
 * @since		1.5
 */
class JPaginationObject extends JObject
{
	public $text;
	public $base;
	public $link;

	public function __construct($text, $base=null, $link=null)
	{
		$this->text = $text;
		$this->base = $base;
		$this->link = $link;
	}
}