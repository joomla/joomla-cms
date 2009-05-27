<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.base.tree');
jimport('joomla.utilities.simplexml');

/**
 * mod_mainmenu Helper class
 *
 * @static
 * @package		Joomla.Site
 * @subpackage	mod_mainmenu
 * @since		1.5
 */
class modMainMenuHelper
{
	function buildXML(&$params)
	{
		$menu = new JMenuTree($params);
		$items = &JSite::getMenu();

		// Get Menu Items
		$rows = $items->getItems('menutype', $params->get('menutype'));
		$maxdepth = $params->get('maxdepth',10);

		// Build Menu Tree root down (orphan proof - child might have lower id than parent)
		$user = &JFactory::getUser();
		$ids = array();
		$ids[0] = true;
		$last = null;
		$unresolved = array();
		// pop the first item until the array is empty if there is any item
		if (is_array($rows)) {
			while (count($rows) && !is_null($row = array_shift($rows)))
			{
				if (array_key_exists($row->parent, $ids)) {
					$row->ionly = $params->get('menu_images_link');
					$menu->addNode($params, $row);

					// record loaded parents
					$ids[$row->id] = true;
				} else {
					// no parent yet so push item to back of list
					// SAM: But if the key isn't in the list and we dont _add_ this is infinite, so check the unresolved queue
					if (!array_key_exists($row->id, $unresolved) || $unresolved[$row->id] < $maxdepth) {
						array_push($rows, $row);
						// so let us do max $maxdepth passes
						// TODO: Put a time check in this loop in case we get too close to the PHP timeout
						if (!isset($unresolved[$row->id])) $unresolved[$row->id] = 1;
						else $unresolved[$row->id]++;
					}
				}
			}
		}
		return $menu->toXML();
	}

	function &getXML($type, &$params, $decorator)
	{
		static $xmls;

		if (!isset($xmls[$type])) {
			$cache = &JFactory::getCache('mod_mainmenu');
			$string = $cache->call(array('modMainMenuHelper', 'buildXML'), $params);
			$xmls[$type] = $string;
		}

		// Get document
		$xml = JFactory::getXMLParser('Simple');
		$xml->loadString($xmls[$type]);
		$doc = &$xml->document;

		$menu	= &JSite::getMenu();
		$active	= $menu->getActive();
		$start	= $params->get('startLevel');
		$end	= $params->get('endLevel');
		$sChild	= $params->get('showAllChildren');
		$path	= array();

		// Get subtree
		if ($start)
		{
			$found = false;
			$root = true;
			if (!isset($active)){
				$doc = false;
			}
			else{
				$path = $active->tree;
				for ($i=0,$n=count($path);$i<$n;$i++)
				{
					foreach ($doc->children() as $child)
					{
						if ($child->attributes('id') == $path[$i]) {
							$doc = &$child->ul[0];
							$root = false;
							break;
						}
					}

					if ($i == $start-1) {
						$found = true;
						break;
					}
				}
				if ((!is_a($doc, 'JSimpleXMLElement')) || (!$found) || ($root)) {
					$doc = false;
				}
			}
		}

		if ($doc && is_callable($decorator)) {
			$doc->map($decorator, array('end'=>$end, 'children'=>$sChild));
		}
		return $doc;
	}

	function render(&$params, $callback)
	{

		// Include the new menu class
		$xml = modMainMenuHelper::getXML($params->get('menutype'), $params, $callback);
		if ($xml) {
			$class = $params->get('class_sfx');
			$xml->addAttribute('class', 'menu'.$class);
			if ($tagId = $params->get('tag_id')) {
				$xml->addAttribute('id', $tagId);
			}

			$result = JFilterOutput::ampReplace($xml->toString((bool)$params->get('show_whitespace')));
			$result = str_replace(array('<ul/>', '<ul />'), '', $result);
			echo $result;
		}
	}
}

/**
 * Main Menu Tree Class.
 *
 * @package		Joomla.Site
 * @subpackage	Menus
 * @since		1.5
 */
class JMenuTree extends JTree
{
	/**
	 * Node/Id Hash for quickly handling node additions to the tree.
	 */
	var $_nodeHash = array();

	/**
	 * Menu parameters
	 */
	var $_params = null;

	/**
	 * Menu parameters
	 */
	var $_buffer = null;

	function __construct(&$params)
	{
		$this->_params		= &$params;
		$this->_root		= new JMenuNode(0, 'ROOT');
		$this->_nodeHash[0]	= &$this->_root;
		$this->_current		= &$this->_root;
	}

	function addNode(&$params, $item)
	{
		// Get menu item data
		$data = $this->_getItemData($params, $item);

		// Create the node and add it
		$node = new JMenuNode($item->id, $item->name, $item->access, $data);

		if (isset($item->mid)) {
			$nid = $item->mid;
		} else {
			$nid = $item->id;
		}
		$this->_nodeHash[$nid] = &$node;
		$this->_current = &$this->_nodeHash[$item->parent];

		if ($item->type == 'menulink' && !empty($item->query['Itemid'])) {
			$node->mid = $item->query['Itemid'];
		}

		if ($this->_current) {
			$this->addChild($node, true);
		} else {
			// sanity check
			JError::raiseError(500, 'Orphan Error. Could not find parent for Item '.$item->id);
		}
	}

	function toXML()
	{
		// Initialize variables
		$this->_current = &$this->_root;

		// Recurse through children if they exist
		while ($this->_current->hasChildren())
		{
			$this->_buffer .= '<ul>';
			foreach ($this->_current->getChildren() as $child)
			{
				$this->_current = & $child;
				$this->_getLevelXML(0);
			}
			$this->_buffer .= '</ul>';
		}
		if ($this->_buffer == '') { $this->_buffer = '<ul />'; }
		return $this->_buffer;
	}

	function _getLevelXML($depth)
	{
		$depth++;

		// Start the item
		$rel = (!empty($this->_current->mid)) ? ' rel="'.$this->_current->mid.'"' : '';
		$this->_buffer .= '<li access="'.$this->_current->access.'" level="'.$depth.'" id="'.$this->_current->id.'"'.$rel.'>';

		// Append item data
		$this->_buffer .= $this->_current->link;

		// Recurse through item's children if they exist
		while ($this->_current->hasChildren())
		{
			$this->_buffer .= '<ul>';
			foreach ($this->_current->getChildren() as $child)
			{
				$this->_current = & $child;
				$this->_getLevelXML($depth);
			}
			$this->_buffer .= '</ul>';
		}

		// Finish the item
		$this->_buffer .= '</li>';
	}

	function _getItemData(&$params, $item)
	{
		$data = null;

		// Menu Link is a special type that is a link to another item
		if ($item->type == 'menulink')
		{
			$menu = &JSite::getMenu();
			if ($newItem = $menu->getItem($item->query['Itemid'])) {
    			$tmp = clone($newItem);
				$tmp->name	 = '<span><![CDATA['.$item->name.']]></span>';
				$tmp->mid	 = $item->id;
				$tmp->parent = $item->parent;
			} else {
				return false;
			}
		} else {
			$tmp = clone($item);
			$tmp->name = '<span><![CDATA['.$item->name.']]></span>';
		}

		$iParams = new JParameter($tmp->params);
		if ($params->get('menu_images') && $iParams->get('menu_image') && $iParams->get('menu_image') != -1) {
			switch ($params->get('menu_images_align', 0)){
				case 0 :
				$imgalign='align="left"';
				break;

				case 1 :
				$imgalign='align="right"';
				break;

				default :
				$imgalign='';
				break;
			}


			$image = '<img src="'.JURI::base(true).'/images/stories/'.$iParams->get('menu_image').'" '.$imgalign.' alt="'.$item->alias.'" />';
			if ($tmp->ionly){
				 $tmp->name = null;
			 }
		} else {
			$image = null;
		}
		switch ($tmp->type)
		{
			case 'separator' :
				return '<span class="separator">'.$image.$tmp->name.'</span>';
				break;

			case 'url' :
				if ((strpos($tmp->link, 'index.php?') === 0) && (strpos($tmp->link, 'Itemid=') === false)) {
					$tmp->url = $tmp->link.'&amp;Itemid='.$tmp->id;
				} else {
					$tmp->url = $tmp->link;
				}
				break;

			default :
				$router = JSite::getRouter();
				$tmp->url = $router->getMode() == JROUTER_MODE_SEF ? 'index.php?Itemid='.$tmp->id : $tmp->link.'&Itemid='.$tmp->id;
				break;
		}

		// Print a link if it exists
		if ($tmp->url != null)
		{
			// Handle SSL links
			$iSecure = $iParams->def('secure', 0);
			if ($tmp->home == 1) {
				$tmp->url = JURI::base();
			} elseif (strcasecmp(substr($tmp->url, 0, 4), 'http') && (strpos($tmp->link, 'index.php?') !== false)) {
				$tmp->url = JRoute::_($tmp->url, true, $iSecure);
			} else {
				$tmp->url = str_replace('&', '&amp;', $tmp->url);
			}

			switch ($tmp->browserNav)
			{
				default:
				case 0:
					// _top
					$data = '<a href="'.$tmp->url.'">'.$image.$tmp->name.'</a>';
					break;
				case 1:
					// _blank
					$data = '<a href="'.$tmp->url.'" target="_blank">'.$image.$tmp->name.'</a>';
					break;
				case 2:
					// window.open
					$attribs = 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,'.$this->_params->get('window_open');

					// hrm...this is a bit dickey
					$link = str_replace('index.php', 'index2.php', $tmp->url);
					$data = '<a href="'.$link.'" onclick="window.open(this.href,\'targetWindow\',\''.$attribs.'\');return false;">'.$image.$tmp->name.'</a>';
					break;
			}
		} else {
			$data = '<a>'.$image.$tmp->name.'</a>';
		}

		return $data;
	}
}

/**
 * Main Menu Tree Node Class.
 *
 * @package		Joomla.Site
 * @subpackage	Menus
 * @since		1.5
 */
class JMenuNode extends JNode
{
	/**
	 * Node Title
	 */
	var $title = null;

	/**
	 * Node Link
	 */
	var $link = null;

	/**
	 * CSS Class for node
	 */
	var $class = null;

	function __construct($id, $title, $access = null, $link = null, $class = null)
	{
		$this->id		= $id;
		$this->title	= $title;
		$this->access	= $access;
		$this->link		= $link;
		$this->class	= $class;
	}
}
