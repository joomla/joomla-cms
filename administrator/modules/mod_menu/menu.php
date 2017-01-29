<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_menu
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * Tree based class to render the admin menu
 *
 * @since  1.5
 */
class JAdminCssMenu
{
	/**
	 * CSS string to add to document head
	 *
	 * @var  string
	 */
	protected $_css = null;

	/**
	 * Root node
	 *
	 * @var  object
	 */
	protected $_root = null;

	/**
	 * Current working node
	 *
	 * @var  object
	 */
	protected $_current = null;

	/**
	 * Counter
	 *
	 * @var  int
	 */
	protected static $counter = 0;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->_root    = new JMenuNode('ROOT');
		$this->_current = &$this->_root;
	}

	/**
	 * Method to add a child
	 *
	 * @param   JMenuNode  $node        The node to process
	 * @param   boolean    $setCurrent  True to set as current working node
	 *
	 * @return  void
	 */
	public function addChild(JMenuNode $node, $setCurrent = false)
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
	 * @param   bool  $clear  Whether to clear the existing menu items or just reset the pointer to root element
	 *
	 * @return  void
	 */
	public function reset($clear = false)
	{
		if ($clear)
		{
			$this->_root = new JMenuNode('ROOT');
		}

		$this->_current = &$this->_root;
	}

	/**
	 * Method to add a separator node
	 *
	 * @return  void
	 */
	public function addSeparator()
	{
		$this->addChild(new JMenuNode(null, null, 'separator', false));
	}

	/**
	 * Method to render the menu
	 *
	 * @param   string  $id     The id of the menu to be rendered
	 * @param   string  $class  The class of the menu to be rendered
	 *
	 * @return  void
	 */
	public function renderMenu($id = '', $class = '')
	{
		$depth = 1;

		// Recurse through children if they exist
		while ($this->_current->hasChildren())
		{
			echo "<div role=\"navigation\" aria-label=\"Main menu\">'
			. '<ul id='menu' class='nav navbar-nav nav-stacked main-nav clearfix' tabindex='0' role=\"menubar\">\n";

			foreach ($this->_current->getChildren() as $child)
			{
				$this->_current = &$child;
				$this->renderLevel($depth++);
			}

			echo "</ul></div>\n";
		}

		if ($this->_css)
		{
			// Add style to document head
			JFactory::getDocument()->addStyleDeclaration($this->_css);
		}
	}

	/**
	 * Method to render a given level of a menu
	 *
	 * @param   integer  $depth  The level of the menu to be rendered
	 *
	 * @return  void
	 */
	public function renderLevel($depth)
	{
		// Build the CSS class suffix
		$class = '';

		if ($this->_current->hasChildren())
		{
			$class = ' class="parent"';
		}

		// Create unique identifier
		self::$counter++;
		$unique = self::$counter;

		// Print the item
		$ariaPopup = $this->_current->hasChildren() ? 'aria-haspopup="true"' : '';
		echo '<li' . $class . ' role="menuitem" tabindex="' . $unique . '" ' . $ariaPopup . '>';

		// Print a link if it exists
		$linkClass = array();
		$dataToggle = '';

		if ($this->_current->hasChildren())
		{
			$linkClass[] = 'collapse-arrow';
			$dataToggle = '';

			// If the menu item has children, override the href
			$this->_current->link = '#collapse' . $unique;
		}
		else
		{
			$linkClass[] = 'no-dropdown';
		}

		$iconClass = $this->getIconClass($this->_current->class);

		if ($this->_current->active === true)
		{
			$linkClass[] = 'active';
		}

		// Implode out $linkClass for rendering
		$linkClass = ' class="' . implode(' ', $linkClass) . '"';

		// Convert blank href to collapse trigger
		if ($this->_current->link === '#')
		{
			$this->_current->link = '#collapse' . $unique;
		}

		if ($this->_current->link != null && $this->_current->target != null)
		{
			echo "<a" . $linkClass . $dataToggle . " href=\"" . $this->_current->link . "\" target=\"" . $this->_current->target . "\">" . $iconClass
				. '<span class="sidebar-item-title">' . $this->_current->title . "</span></a>";
		}
		elseif ($this->_current->link != null && $this->_current->target == null)
		{
			echo "<a" . $linkClass . $dataToggle . " href=\"" . $this->_current->link . "\">" . $iconClass
				. '<span class="sidebar-item-title" >' . $this->_current->title . "</span></a>";
		}
		elseif ($this->_current->title != null)
		{
			echo "<a" . $linkClass . $dataToggle . ">" . $iconClass
				. '<span class="sidebar-item-title" >' . $this->_current->title . "</span></a>";
		}
		else
		{
			echo '<span></span>';
		}

		// Recurse through children if they exist
		while ($this->_current->hasChildren())
		{
			echo '<ul id="collapse' . $unique . '" class="nav panel-collapse collapse-level-1 collapse" role="menu" aria-hidden="true">' . "\n";

			foreach ($this->_current->getChildren() as $child)
			{
				$this->_current = &$child;
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
	 * @param   string  $identifier  Icon identification string
	 *
	 * @return  string	CSS class name
	 *
	 * @since   1.5
	 */
	public function getIconClass($identifier)
	{
		static $classes;

		// Initialise the known classes array if it does not exist
		if (!is_array($classes))
		{
			$classes = array();
		}

		$html = '';

		/*
		 * If we don't already know about the class... build it and mark it
		 * known so we don't have to build it again
		 */
		if (!isset($classes[$identifier]))
		{
			if (substr($identifier, 0, 6) == 'class:')
			{
				// We were passed a class name
				$class = substr($identifier, 6);
				$html  = '<span class="fa fa-' . $class . '"></span>';
			}
			else
			{
				if ($identifier == null)
				{
					return null;
				}

				// Build the CSS class for the icon
				$class = preg_replace('#\.[^.]*$#', '', basename($identifier));
				$class = preg_replace('#\.\.[^A-Za-z0-9\.\_\- ]#', '', $class);
				$html  = '<span class="fa fa-' . $class . '"></span>';
			}

			if ($class == 'disabled')
			{
				return null;
			}
		}

		return $html;
	}

	/**
	 * Populate the menu items in the menu object for disabled state
	 *
	 * @param   Registry  $params   Menu configuration parameters
	 * @param   bool      $enabled  Whether the menu should be enabled or disabled
	 *
	 * @return  void
	 *
	 * @since   3.7.0
	 */
	public function load($params, $enabled)
	{
		$menutype = $params->get('menutype', '*');

		$this->reset(true);

		if ($menutype == '*')
		{
			require_once __DIR__ . '/preset/' . ($enabled ? 'enabled.php' : 'disabled.php');
		}
		else
		{
			$items = ModMenuHelper::getMenuItems($menutype);
			$app   = JFactory::getApplication();
			$me    = JFactory::getUser();

			$authMenus   = $me->authorise('core.manage', 'com_menus');
			$authModules = $me->authorise('core.manage', 'com_modules');

			if ($enabled && $params->get('check') && ($authMenus || $authModules))
			{
				$elements = ArrayHelper::getColumn($items, 'element');

				$rMenu   = $authMenus && !in_array('com_menus', $elements);
				$rModule = $authModules && !in_array('com_modules', $elements);

				if ($rMenu || $rModule)
				{
					$recovery = $app->getUserStateFromRequest('mod_menu.recovery', 'recover_menu', 0, 'int');

					if ($recovery)
					{
						$app->enqueueMessage(JText::_('MOD_MENU_WARNING_IMPORTANT_ITEMS_INACCESSIBLE_RECOVERY'), 'info');

						$params->set('recovery', true);

						// In recovery mode, load the preset inside a special root node.
						$this->addChild(new JMenuNode(JText::_('MOD_MENU_RECOVERY_MENU_ROOT'), '#'), true);

						require_once __DIR__ . '/preset/enabled.php';

						$this->getParent();
					}
					elseif ($rMenu && $rModule)
					{
						$app->enqueueMessage(JText::_('MOD_MENU_WARNING_IMPORTANT_ITEMS_INACCESSIBLE'), 'warning');
					}
					else
					{
						$app->enqueueMessage(JText::_('MOD_MENU_WARNING_IMPORTANT_ITEMS_INACCESSIBLE_' . ($rMenu ? 'MENUS' : 'MODULES')), 'warning');
					}
				}
			}

			// Create levels
			$items = ModMenuHelper::parseItems($items);

			// Menu items for dynamic db driven setup to load here
			$this->loadItems($items, $enabled);
		}
	}

	/**
	 * Load the menu items from an array
	 *
	 * @param   array  $items    Menu items loaded from database
	 * @param   bool   $enabled  Whether the menu should be enabled or disabled
	 *
	 * @return  void
	 *
	 * @since   3.7.0
	 */
	protected function loadItems($items, $enabled = true)
	{
		foreach ($items as $item)
		{
			if ($item->type == 'separator')
			{
				$this->addSeparator();

				continue;
			}

			if ($item->type == 'heading' && !count($item->submenu))
			{
				// Exclude if it is a heading type menu item, and has no children.
			}
			elseif (!$enabled)
			{
				$this->addChild(new JMenuNode($item->text, $item->link, 'disabled'));
			}
			else
			{
				$this->addChild(new JMenuNode($item->text, $item->link, $item->parent_id == 1 ? null : 'class:'), true);

				$this->loadItems($item->submenu);

				$components = array();

				if ($item->type == 'container')
				{
					$components = ModMenuHelper::getComponents(true, true);
				}

				// Add a separator between dynamic menu items and components menu items
				if (count($item->submenu) && count($components))
				{
					$this->addSeparator();
				}

				// Adding component submenu the old way, this assumes 2-level menu only
				foreach ($components as &$component)
				{
					if (empty($component->submenu))
					{
						$this->addChild(new JMenuNode($component->text, $component->link, $component->img));
					}
					else
					{
						$this->addChild(new JMenuNode($component->text, $component->link, $component->img), true);

						foreach ($component->submenu as $sub)
						{
							$this->addChild(new JMenuNode($sub->text, $sub->link, $sub->img));
						}

						$this->getParent();
					}
				}

				$this->getParent();
			}
		}
	}
}

/**
 * A Node for JAdminCssMenu
 *
 * @see    JAdminCssMenu
 * @since  1.5
 */
class JMenuNode
{
	/**
	 * Node Title
	 *
	 * @var  string
	 */
	public $title = null;

	/**
	 * Node Id
	 *
	 * @var  string
	 */
	public $id = null;

	/**
	 * Node Link
	 *
	 * @var  string
	 */
	public $link = null;

	/**
	 * Link Target
	 *
	 * @var  string
	 */
	public $target = null;

	/**
	 * CSS Class for node
	 *
	 * @var  string
	 */
	public $class = null;

	/**
	 * Active Node?
	 *
	 * @var  boolean
	 */
	public $active = false;

	/**
	 * Parent node
	 *
	 * @var  JMenuNode
	 */
	protected $_parent = null;

	/**
	 * Array of Children
	 *
	 * @var  array
	 */
	protected $_children = array();

	/**
	 * Constructor for the class.
	 *
	 * @param   string   $title      The title of the node
	 * @param   string   $link       The node link
	 * @param   string   $class      The CSS class for the node
	 * @param   boolean  $active     True if node is active, false otherwise
	 * @param   string   $target     The link target
	 * @param   string   $titleicon  The title icon for the node
	 */
	public function __construct($title, $link = null, $class = null, $active = false, $target = null, $titleicon = null)
	{
		$this->title  = $titleicon ? $title . $titleicon : $title;
		$this->link   = JFilterOutput::ampReplace($link);
		$this->class  = $class;
		$this->active = $active;

		$this->id = null;

		if (!empty($link) && $link !== '#')
		{
			$uri    = new JUri($link);
			$params = $uri->getQuery(true);
			$parts  = array();

			foreach ($params as $value)
			{
				$parts[] = str_replace(array('.', '_'), '-', $value);
			}

			$this->id = implode('-', $parts);
		}

		$this->target = $target;
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
	 * @param   JMenuNode  &$parent  The JMenuNode for parent to be set or null
	 *
	 * @return  void
	 */
	public function setParent(JMenuNode &$parent = null)
	{
		$hash = spl_object_hash($this);

		if (!is_null($this->_parent))
		{
			unset($this->_parent->_children[$hash]);
		}

		if (!is_null($parent))
		{
			$parent->_children[$hash] = &$this;
		}

		$this->_parent = &$parent;
	}

	/**
	 * Get the children of this node
	 *
	 * @return  array  The children
	 */
	public function &getChildren()
	{
		return $this->_children;
	}

	/**
	 * Get the parent of this node
	 *
	 * @return  mixed  JMenuNode object with the parent or null for no parent
	 */
	public function &getParent()
	{
		return $this->_parent;
	}

	/**
	 * Test if this node has children
	 *
	 * @return  boolean  True if there are children
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
