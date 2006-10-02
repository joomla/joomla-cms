<?php
/**
 * @version $Id$
 * @package Joomla
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

jimport('joomla.common.abstract.tree');

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

	function addNode($item)
	{

		// Menu Link is a special type that is a link to another item
		if ($item->type == 'menulink') {
			$menu = &JMenu::getInstance();
			if ($tmp = $menu->getItem($item->link)) {
				$name = $item->name;
				$mid = $item->id;
				$parent = $item->parent;
				$item = clone($tmp);
				$item->name = $name;
				$item->mid = $mid;
				$item->parent = $parent;
			} else {
				return;
			}
		}

		switch ($item->type)
		{
			case 'separator' :
				$node =& new JMenuNode(null, $item->name, 'seperator', false);
				if (isset($item->mid)) {
					$nid = $item->mid;
				} else {
					$nid = $item->id;
				}
				$this->_nodeHash[$nid] =& $node;
				$this->_current =& $this->_nodeHash[$item->parent];
				$this->addChild($node, true);
				return;
				break;

			case 'url' :
				if (eregi('index.php\?', $item->link)) {
					if (!eregi('Itemid=', $item->link)) {
						$item->link .= '&Itemid='.$item->id;
					}
				}
				break;

			default :
				$item->link .= strpos( $item->link, '?' ) === false ? '?' : '&';
				$item->link .= 'Itemid='.$item->id;
				break;
		}

		// replace & with amp; for xhtml compliance
		$item->link = ampReplace( $item->link );

		// Handle SSL links
		//$iParams =& new JParameter($item->params);
		$iParams =& $item->mParams;
		$iSecure = $iParams->def('secure', 0);
		if (strcasecmp(substr($item->link, 0, 4), 'http')) {
			$item->url = JURI::resolve($item->link, $iSecure);
		} else {
			$item->url = $item->link;
		}

		// Create the node and add it
		$node =& new JMenuNode($item->id, $item->name, $item->url);
		$node->target = $item->browserNav;
		$node->window = $this->_params->get('window_open');

		if (isset($item->mid)) {
			$nid = $item->mid;
		} else {
			$nid = $item->id;
		}
		$this->_nodeHash[$nid] =& $node;
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

	function render($type, $suffix = null)
	{
		$depth = 0;
		$this->_current =& $this->_root;
		$class = $type.$suffix;

		// Recurse through children if they exist
		while ($this->_current->hasChildren())
		{
			echo "<ul class=\"$class\">\n";
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
		$start	= $this->_params->get('startLevel');
		$end	= $this->_params->get('endLevel');

		$showChildren = false;
		if ($this->_current->active) {
			$showChildren = true;
		}
		if ($this->_params->get('showAllChildren')) {
			$showChildren = true;
		}

		// Build the CSS class selectors
		$classes = "level$depth item".$this->_depthHash[$depth];
		$id = "";

		if ($this->_current->hasChildren() && (($depth < $end) || ($end == 0))) {
			$classes .= ' parent';
		}

		if($this->_current->class == 'separator') {
			$classes .= ' separator';
		}

		if($this->_current->active) {
			$classes .= ' active';
		}

		if ($this->_active == $this->_current->id) {
			$id = " id=\"current\"";
		}

		$inBounds = ((($depth >= $start) || ($start == 0)) && (($depth <= $end) || ($end == 0)));

		$parent = & $this->_current->getParent();
		$inActive = $parent->active;
		if ($start && ($depth <= $start) && !$inActive) {
			if ((($depth < $end) || ($end == 0)) && $showChildren) {
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
			}
			return;
		}
		if ($inBounds) {
			// Print the item
			echo "<li".$id." class=\"".$classes."\">";

			// Print a link if it exists
			if ($this->_current->link != null) {
				switch ($this->_current->target)
				{
					default:
					case 0:
						// _top
						echo "<a href=\"".$this->_current->link."\">".$this->_current->title."</a>";
						break;
					case 1:
						// _blank
						echo "<a href=\"".$this->_current->link."\" target=\"_blank\">".$this->_current->title."</a>";
						break;
					case 2:
						// window.open
						$attribs = 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,'
							. $this->_current->window;
						// hrm...this is a bit dickey
						$link = str_replace( 'index.php', 'index2.php', $this->_current->link );
						echo "<a href=\"javascript:void window.open('".$link."','targetWindow','".$attribs."')\">".$this->_current->title."</a>";
						break;
				}
			} else if ($this->_current->title != null) {
				echo "<a>".$this->_current->title."</a>\n";
			} else {
				echo "<span></span>";
			}
		}

		if ((($depth < $end) || ($end == 0)) && $showChildren) {
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
		if ($inBounds) {
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

	/**
	 * Target: 0=_top, 1=_blank, 2=window.open
	 */
	var $target = false;

	/**
	 * Atrributes for window.open
	 */
	var $window;

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