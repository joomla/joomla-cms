<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_menu
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Tree based class to render the admin menu
 *
 * @package     Joomla.Administrator
 * @subpackage  mod_menu
 * @since       1.5
 */
class JAdminCssMenu extends JObject
{
	/**
	 * CSS string to add to document head
	 * @var string
	 */
	protected $_css = null;

	/**
	 * Root node
	 *
	 * @var    object
	 */
	protected $_root = null;

	/**
	 * Current working node
	 *
	 * @var    object
	 */
	protected $_current = null;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->_root = new JMenuNode('ROOT');
		$this->_current = & $this->_root;
	}

	/**
	 * Method to add a child
	 *
	 * @param   JMenuNode  &$node       The node to process
	 * @param   boolean    $setCurrent  True to set as current working node
	 *
	 * @return  void
	 */
	public function addChild(JMenuNode &$node, $setCurrent = false)
	{
		$this->_current->addChild($node);
		if ($setCurrent)
		{
			$this->_current = &$node;
		}
	}

	/**
	 * Method to get the parent
	 *
	 * @return  void
	 */
	public function getParent()
	{
		$this->_current = &$this->_current->getParent();
	}

	/**
	 * Method to get the parent
	 *
	 * @return  void
	 */
	public function reset()
	{
		$this->_current = &$this->_root;
	}

	public function addSeparator()
	{
		$this->addChild(new JMenuNode(null, null, 'separator', false));
	}

	public function renderMenu($id = 'menu', $class = '')
	{
		$depth = 1;

		if (!empty($id)) {
			$id = 'id="' . $id . '"';
		}

		if (!empty($class)) {
			$class = 'class="' . $class . '"';
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

	public function renderLevel($depth)
	{
		/*
		 * Build the CSS class suffix
		 */
		$class = '';
		if ($this->_current->hasChildren()) {
			$class = ' class="dropdown"';
		}

		if ($this->_current->class == 'separator') {
			$class = ' class="divider"';
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
		$dataToggle = '';
		$dropdownCaret = '';

		if ($this->_current->hasChildren()) {
				$linkClass = ' class="dropdown-toggle"';
				$dataToggle = ' data-toggle="dropdown"';
				$dropdownCaret = ' <span class="caret"></span>';
		}

		if ($this->_current->link != null && $this->_current->target != null) {
			echo "<a".$linkClass." ".$dataToggle." href=\"".$this->_current->link."\" target=\"".$this->_current->target."\" >".$this->_current->title.$dropdownCaret."</a>";
		} elseif ($this->_current->link != null && $this->_current->target == null) {
			echo "<a".$linkClass." ".$dataToggle." href=\"".$this->_current->link."\">".$this->_current->title.$dropdownCaret."</a>";
		} elseif ($this->_current->title != null) {
			echo "<a".$linkClass." ".$dataToggle.">".$this->_current->title.$dropdownCaret."</a>";
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
				echo '<ul'.$id.' class="dropdown-menu menu-component">'."\n";
			} else {
				echo '<ul class="dropdown-menu">'."\n";
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
	public function getIconClass($identifier)
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
 * A Node for JAdminCssMenu
 *
 * @package     Joomla.Administrator
 * @subpackage  mod_menu
 * @since       1.5
 * @see         JAdminCssMenu
 */
class JMenuNode extends JObject
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

	/**
	 * Parent node
	 * @var    object
	 */
	protected $_parent = null;

	/**
	 * Array of Children
	 *
	 * @var    array
	 */
	protected $_children = array();

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

			foreach ($params as $name => $value)
			{
				$parts[] = str_replace(array('.', '_'), '-', $value);
			}

			$this->id = implode('-', $parts);
		}

		$this->target	= $target;
	}

	/**
	 * Add child to this node
	 *
	 * If the child already has a parent, the link is unset
	 *
	 * @param   JMenuNode  &$child  The child to be added
	 *
	 * @return  void
	 */
	public function addChild(JMenuNode &$child)
	{
		$child->setParent($this);
	}

	/**
	 * Set the parent of a this node
	 *
	 * If the node already has a parent, the link is unset
	 *
	 * @param   JMenuNode   &$parent  The JMenuNode for parent to be set or null
	 *
	 * @return  void
	 */
	public function setParent(JMenuNode &$parent = null)
	{
		$hash = spl_object_hash($this);
		if (!is_null($this->_parent))
		{
			unset($this->_parent->children[$hash]);
		}
		if (!is_null($parent))
		{
			$parent->_children[$hash] = & $this;
		}
		$this->_parent = & $parent;
	}

	/**
	 * Get the children of this node
	 *
	 * @return  array    The children
	 */
	public function &getChildren()
	{
		return $this->_children;
	}

	/**
	 * Get the parent of this node
	 *
	 * @return  mixed   JMenuNode object with the parent or null for no parent
	 */
	public function &getParent()
	{
		return $this->_parent;
	}

	/**
	 * Test if this node has children
	 *
	 * @return   boolean  True if there are children
	 */
	public function hasChildren()
	{
		return (bool) count($this->_children);
	}

	/**
	 * Test if this node has a parent
	 *
	 * @return  boolean  True if there is a parent
	 */
	public function hasParent()
	{
		return $this->getParent() != null;
	}
}
