<?php
/**
 * @version		$Id: menu.php 20770 2011-02-18 11:17:28Z infograf768 $
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.base.tree');

/**
 * @package		Joomla.Administrator
 * @subpackage	mod_menu
 */
class JAdminCssMenu extends JTree
{
	/**
	 * CSS string to add to document head
	 * @var string
	 */
	protected $_css = null;

	function __construct()
	{
		$this->_root = new JMenuNode('ROOT');
		$this->_current = & $this->_root;
	}

	function addSeparator()
	{
		$this->addChild(new JMenuNode(null, null, 'separator', false));
	}

	function renderMenu($id = 'menu', $class = '')
	{
		$depth = 1;

		if (!empty($id)) {
			$id='id="'.$id.'"';
		}

		if (!empty($class)) {
			$class='class="'.$class.'"';
		}

		/*
		 * Recurse through children if they exist
		 */
		while ($this->_current->hasChildren())
		{
			echo "<ul ".$id." ".$class.">\n";
			foreach ($this->_current->getChildren() as $child)
			{
				$this->_current = & $child;
				$this->renderLevel($depth++);
			}
			echo "</ul>\n";
		}

		if ($this->_css) {
			// Add style to document head
			$doc = JFactory::getDocument();
			$doc->addStyleDeclaration($this->_css);
		}
	}

	function renderLevel($depth)
	{
		/*
		 * Build the CSS class suffix
		 */
		$class = '';
		if ($this->_current->hasChildren()) {
			$class = ' class="node"';
		}

		if ($this->_current->class == 'separator') {
			$class = ' class="separator"';
		}

		if ($this->_current->class == 'disabled') {
			$class = ' class="disabled"';
		}

		/*
		 * Print the item
		 */
		echo "<li".$class.">";

		/*
		 * Print a link if it exists
		 */

		$linkClass = '';

		if ($this->_current->link != null) {
			$linkClass = $this->getIconClass($this->_current->class);
			if (!empty($linkClass)) {
				$linkClass = ' class="'.$linkClass.'"';
			}
		}

		if ($this->_current->link != null && $this->_current->target != null) {
			echo "<a".$linkClass." href=\"".$this->_current->link."\" target=\"".$this->_current->target."\" >".$this->_current->title."</a>";
		} elseif ($this->_current->link != null && $this->_current->target == null) {
			echo "<a".$linkClass." href=\"".$this->_current->link."\">".$this->_current->title."</a>";
		} elseif ($this->_current->title != null) {
			echo "<a>".$this->_current->title."</a>\n";
		} else {
			echo "<span></span>";
		}

		/*
		 * Recurse through children if they exist
		 */
		while ($this->_current->hasChildren())
		{
			if ($this->_current->class) {
				$id = '';
				if (!empty($this->_current->id)) {
					$id = ' id="menu-'.strtolower($this->_current->id).'"';
				}
				echo '<ul'.$id.' class="menu-component">'."\n";
			} else {
				echo '<ul>'."\n";
			}
			foreach ($this->_current->getChildren() as $child)
			{
				$this->_current = & $child;
				$this->renderLevel($depth++);
			}
			echo "</ul>\n";
		}
		echo "</li>\n";
	}

	/**
	 * Method to get the CSS class name for an icon identifier or create one if
	 * a custom image path is passed as the identifier
	 *
	 * @access	public
	 * @param	string	$identifier	Icon identification string
	 * @return	string	CSS class name
	 * @since	1.5
	 */
	function getIconClass($identifier)
	{
		static $classes;

		// Initialise the known classes array if it does not exist
		if (!is_array($classes)) {
			$classes = array();
		}

		/*
		 * If we don't already know about the class... build it and mark it
		 * known so we don't have to build it again
		 */
		if (!isset($classes[$identifier])) {
			if (substr($identifier, 0, 6) == 'class:') {
				// We were passed a class name
				$class = substr($identifier, 6);
				$classes[$identifier] = "icon-16-$class";
			} else {
				if ($identifier == null) {
					return null;
				}
				// Build the CSS class for the icon
				$class = preg_replace('#\.[^.]*$#', '', basename($identifier));
				$class = preg_replace('#\.\.[^A-Za-z0-9\.\_\- ]#', '', $class);

				$this->_css  .= "\n.icon-16-$class {\n" .
						"\tbackground: url($identifier) no-repeat;\n" .
						"}\n";

				$classes[$identifier] = "icon-16-$class";
			}
		}
		return $classes[$identifier];
	}
}

/**
 * @package		Joomla.Administrator
 * @subpackage	mod_menu
 */
class JMenuNode extends JNode
{
	/**
	 * Node Title
	 */
	public $title = null;

	/**
	 * Node Id
	 */
	public $id = null;

	/**
	 * Node Link
	 */
	public $link = null;

	/**
	 * Link Target
	 */
	public $target = null;

	/**
	 * CSS Class for node
	 */
	public $class = null;

	/**
	 * Active Node?
	 */
	public $active = false;

	public function __construct($title, $link = null, $class = null, $active = false, $target = null, $titleicon = null)
	{
		$this->title	= $titleicon ? $title.$titleicon : $title;
		$this->link		= JFilterOutput::ampReplace($link);
		$this->class	= $class;
		$this->active	= $active;

		$this->id = null;
		if (!empty($link) && $link !== '#') {
			$uri = new JURI($link);
			$params = $uri->getQuery(true);
			$parts = array();

			foreach ($params as $name => $value) {
				$parts[] = str_replace(array('.','_'), '-', $value);
 			}

 			$this->id = implode('-', $parts);
		}

		$this->target	= $target;
	}
}
