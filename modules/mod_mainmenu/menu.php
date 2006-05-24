<?php
/**
 * @version $Id$
 * @package Joomla
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

jimport('joomla.common.base.tree');

/**
 * Main Menu Tree Class.
 *
 * @author		Louis Landry <louis.landry@joomla.org>
 * @package 	Joomla.Framework
 * @subpackage 	Utilities
 * @since		1.5
 */
class JMainMenu extends JTree
{
	/**
	 * Node/Id Hash for quickly handling node additions to the tree.
	 */
	var $_nodeHash = array();

	/**
	 * Node depth counter hash
	 */
	var $_depthHash = array();

	/**
	 * CSS to add to the document head
	 */
	var $_css = null;
	
	/**
	 * Menu parameters
	 */
	var $_params = null;

	/**
	 * Active menu item
	 */
	var $_active = 0;

	function __construct(&$params, $active = 0)
	{
		$this->_params =& $params;
		$this->_active = $active;
		$this->_root =& new JMenuNode(0, 'ROOT');
		$this->_nodeHash[0] =& $this->_root;
		$this->_current = & $this->_root;
	}

	function addNode(&$item)
	{

		switch ($item->type) {
			case 'separator' :
				$this->addChild(new JMenuNode(null, null, 'seperator', false));
				return;
				break;

			case 'component_item_link' :
				break;

			case 'content_item_link':
			case 'content_typed' :
				if ( $this->_params->get( 'unique_itemid' ) ) {
					$item->link .= '&Itemid='. $item->id;
				} else {
					$temp = split("&task=view&id=", $item->link);
					require_once (JApplicationHelper::getPath('helper', 'com_content'));

					$_Itemid = JContentHelper::getItemid($temp[1]);
					$item->link .= '&Itemid='.$_Itemid;
				}
				break;

			case 'url' :
				if (eregi('index.php\?', $item->link)) {
					if (!eregi('Itemid=', $item->link)) {
						$item->link .= '&Itemid='.$item->id;
					}
				}
				break;

			default :
				$item->link .= '&Itemid='.$item->id;
				break;
		}

		// replace & with amp; for xhtml compliance
		$item->link = ampReplace( $item->link );

		// Handle SSL links
		$iParams = & new JParameter($item->params);
		$iSecure = $iParams->def('secure', 0);
		if (strcasecmp(substr($item->link, 0, 4), 'http')) {
			$item->link = josURL($item->link, $iSecure);
		}

		// Create the node and add it
		$node =& new JMenuNode($item->id, $item->name, $item->link);
		$this->_nodeHash[$item->id] =& $node;
		$this->_current =& $this->_nodeHash[$item->parent];
		$this->addChild($node, true);
		
		// Handle active menu items
		if ($item->id == $this->_active) {
			while ($this->_current->title != 'ROOT') {
				$this->_current->active = true;
				$this->getParent();
			}
		}				
	}

	function render($id, $suffix = null)
	{
		global $mainframe;

		$depth = 0;
		$this->_current =& $this->_root;

		// Recurse through children if they exist
		while ($this->_current->hasChildren())
		{
			echo "<ul id=\"$id\" class=\"listmenu$suffix\">\n";
			foreach ($this->_current->getChildren() as $child)
			{
				$this->_current = & $child;
				$this->renderLevel($suffix, $depth);
			}
			echo "</ul>\n";
		}
	}

	function renderLevel($suffix = null, $depth)
	{
		$depth++;
		if (!isset($this->_depthHash[$depth])) {
			$this->_depthHash[$depth] = 0;
		}
		$this->_depthHash[$depth]++;
		$start = $this->_params->get('startLevel');
		$end = $this->_params->get('endLevel');

		$showChildren = false;
		if ($this->_current->active) {
			$showChildren = true;
		}
		if ($this->_params->get('showAllChildren')) {
			$showChildren = true;
		}
		
		if ($this->_active == $this->_current->id) {
			$active = " id=\"active\"";
		} else {
			$active = null;
		}

		// Build the CSS class selectors
		$classes = "level$depth item".$this->_depthHash[$depth];


		if ($this->_current->hasChildren() && (($depth < $end) || ($end == 0))) {
			$classes .= ' parent';
		}

		if($this->_current->class == 'separator') {
			$classes .= ' separator';
		}

		if($this->_current->active) {
			$classes .= ' active';
		}

		if ((($depth >= $start) || ($start == 0)) && (($depth <= $end) || ($end == 0))) {

			// Print the item
			echo "<li$active class=\"".$classes."\">";
	
			// Print a link if it exists
			if ($this->_current->link != null) {
				echo "<a href=\"".$this->_current->link."\">".$this->_current->title."</a>";
			} else if ($this->_current->title != null) {
				echo "<a>".$this->_current->title."</a>\n";
			} else {
				echo "<span></span>";
			}
		}

		if (($depth < $end) || ($end == 0) && $showChildren) {
			// Recurse through children if they exist
			while ($this->_current->hasChildren())
			{
				if (($depth >= $start) || ($start == 0)) {
					echo "\n<ul>\n";
				}
				foreach ($this->_current->getChildren() as $child)
				{
					$this->_current = & $child;
					$this->renderLevel($suffix, $depth);
				}
				if (($depth >= $start) || ($start == 0)) {
					echo "</ul>\n";
				}
			}
		} else {
			// Iterate to the end of the children
			foreach ($this->_current->getChildren() as $child) {
				$this->_current = & $child;
			}
		}

		// Close item
		if ((($depth >= $start) || ($start == 0)) && (($depth <= $end) || ($end == 0))) {
			echo "</li>\n";
		}
	}
}

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

	/**
	 * Active Node?
	 */
	var $active = false;

	function __construct($id, $title, $link = null, $class = null, $active = false)
	{
		$this->id		= $id;
		$this->title	= $title;
		$this->link		= $link;
		$this->class	= $class;
		$this->active	= $active;
	}
}
?>