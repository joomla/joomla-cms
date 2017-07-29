<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_menu
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Module\Menu\Administrator\Menu;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;
use Joomla\Module\Menu\Administrator\Helper\MenuHelper;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * Tree based class to render the admin menu
 *
 * @since  1.5
 */
class CssMenu
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
		$this->_root    = new MenuNode('ROOT');
		$this->_current = &$this->_root;
	}

	/**
	 * Method to add a child
	 *
	 * @param   MenuNode  $node        The node to process
	 * @param   boolean   $setCurrent  True to set as current working node
	 *
	 * @return  void
	 */
	public function addChild(MenuNode $node, $setCurrent = false)
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
			$this->_root = new MenuNode('ROOT');
		}

		$this->_current = &$this->_root;
	}

	/**
	 * Method to add a separator node
	 *
	 * @param   string  $title  The separator label text. A dash "-" can be used to use a horizontal bar instead of text label.
	 *
	 * @return  void
	 */
	public function addSeparator($title = null)
	{
		if ($title == '-' || $title == '')
		{
			$title = null;
		}

		$this->addChild(new MenuNode($title, null, 'separator', false));
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
		echo "<div class='main-nav-container' role=\"navigation\" aria-label=\"Main menu\">";
		echo "<ul id='menu' class='nav navbar-nav nav-stacked main-nav clearfix' role=\"menubar\">";

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
			Factory::getDocument()->addStyleDeclaration($this->_css);
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
		echo '<li' . $class . ' role="menuitem" ' . $ariaPopup . '>';

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
		elseif ($this->_current->title != null && $this->_current->class != 'separator')
		{
			echo "<a" . $linkClass . $dataToggle . ">" . $iconClass
				. '<span class="sidebar-item-title" >' . $this->_current->title . "</span></a>";
		}
		else
		{
			echo '<span>' . $this->_current->title . '</span>';
		}

		// Recurse through children if they exist
		// @TODO - A better solution needed to add 2nd level title ($this->_current->title) & 'close'
		while ($this->_current->hasChildren())
		{
			echo '<ul id="collapse' . $unique . '" class="nav panel-collapse collapse-level-1 collapse" role="menu" aria-hidden="true">
		   <li>' . $this->_current->title . '<a href="#" class="close"><span aria-label="Close Menu">×</span></a></li>' . "\n";

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
			require __DIR__ . '/../preset/' . ($enabled ? 'enabled.php' : 'disabled.php');
		}
		else
		{
			$items = MenuHelper::getMenuItems($menutype);
			$types = ArrayHelper::getColumn($items, 'type');
			$app   = Factory::getApplication();
			$me    = Factory::getUser();

			$authMenus   = $me->authorise('core.manage', 'com_menus');
			$authModules = $me->authorise('core.manage', 'com_modules');

			if ($enabled && $params->get('check') && ($authMenus || $authModules))
			{
				$elements = ArrayHelper::getColumn($items, 'element');

				$rMenu      = $authMenus && !in_array('com_menus', $elements);
				$rModule    = $authModules && !in_array('com_modules', $elements);
				$rContainer = !in_array('container', $types);

				if ($rMenu || $rModule || $rContainer)
				{
					$recovery = $app->getUserStateFromRequest('mod_menu.recovery', 'recover_menu', 0, 'int');

					if ($recovery)
					{
						$params->set('recovery', true);

						// In recovery mode, load the preset inside a special root node.
						$this->addChild(new MenuNode(\JText::_('MOD_MENU_RECOVERY_MENU_ROOT'), '#'), true);

						require __DIR__ . '/preset/enabled.php';

						$this->addSeparator();

						$uri = clone Uri::getInstance();
						$uri->setVar('recover_menu', 0);

						$this->addChild(new MenuNode(\JText::_('MOD_MENU_RECOVERY_EXIT'), $uri->toString()));

						$this->getParent();
					}
					else
					{
						$missing = array();

						if ($rMenu)
						{
							$missing[] = \JText::_('MOD_MENU_IMPORTANT_ITEM_MENU_MANAGER');
						}

						if ($rModule)
						{
							$missing[] = \JText::_('MOD_MENU_IMPORTANT_ITEM_MODULE_MANAGER');
						}

						if ($rContainer)
						{
							$missing[] = \JText::_('MOD_MENU_IMPORTANT_ITEM_COMPONENTS_CONTAINER');
						}

						$uri = clone Uri::getInstance();
						$uri->setVar('recover_menu', 1);

						$table = Table::getInstance('MenuType');
						$table->load(array('menutype' => $menutype));
						$mType = $table->get('title', $menutype);

						$msg = \JText::sprintf('MOD_MENU_IMPORTANT_ITEMS_INACCESSIBLE_LIST_WARNING', $mType, implode(', ', $missing), $uri);

						$app->enqueueMessage($msg, 'warning');
					}
				}
			}

			// Create levels
			$items = MenuHelper::parseItems($items);

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
				$this->addSeparator($item->text);
			}
			elseif ($item->type == 'heading' && !count($item->submenu))
			{
				// Exclude if it is a heading type menu item, and has no children.
			}
			elseif ($item->type == 'container')
			{
				$exclude    = (array) $item->params->get('hideitems') ?: array();
				$components = MenuHelper::getComponents(true, false, $exclude);

				// Exclude if it is a container type menu item, and has no children.
				if (count($item->submenu) || count($components))
				{
					$this->addChild(new MenuNode($item->text, $item->link, $item->parent_id == 1 ? null : 'class:'), true);

					if ($enabled)
					{
						// Load explicitly assigned child items first.
						$this->loadItems($item->submenu);

						// Add a separator between dynamic menu items and components menu items
						if (count($item->submenu) && count($components))
						{
							$this->addSeparator($item->text);
						}

						// Adding component submenu the old way, this assumes 2-level menu only
						foreach ($components as $component)
						{
							if (empty($component->submenu))
							{
								$this->addChild(new MenuNode($component->text, $component->link, $component->img));
							}
							else
							{
								$this->addChild(new MenuNode($component->text, $component->link, $component->img), true);

								foreach ($component->submenu as $sub)
								{
									$this->addChild(new MenuNode($sub->text, $sub->link, $sub->img));
								}

								$this->getParent();
							}
						}
					}

					$this->getParent();
				}
			}
			elseif (!$enabled)
			{
				$this->addChild(new MenuNode($item->text, $item->link, 'disabled'));
			}
			else
			{
				$target = $item->browserNav ? '_blank' : null;

				$this->addChild(new MenuNode($item->text, $item->link, $item->parent_id == 1 ? null : 'class:', false, $target), true);
				$this->loadItems($item->submenu);
				$this->getParent();
			}
		}
	}
}
