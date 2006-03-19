<?php
/**
 * @version $Id$
 * @package Joomla
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

class JAdminCSSMenu extends JObject
{
	/**
	 * 
	 */
	var $_root = null;
	
	/**
	 * 
	 */
	var $_current = null;

	/**
	 * 
	 */
	var $_css = null;

	function __construct()
	{
		$this->_root =  & new JMenuNode('ROOT');
		$this->_current = & $this->_root;
	}

	function addChild($title, $link = null, $class = null, $active = false)
	{
		$newNode = & new JMenuNode($title, $link, $class, $active);
		$this->_current->addChild($newNode);
	}

	function addTree(&$node)
	{
		$this->_current->addChild($node);
	}

	function renderMenu($suffix = '-smenu')
	{
		global $mainframe;
		
		/*
		 * Build the CSS class suffix
		 */
		if ($this->_current->active)
		{
			$sfx = $suffix.'_active';
		} else
		{
			$sfx = $suffix;
		}
		
		/*
		 * Recurse through children if they exist
		 */
		while ($this->_current->hasChildren())
		{
			echo "<ul>\n";
			foreach ($this->_current->getChildren() as $child)
			{
				$this->_current = & $child;
				$this->renderLevel($suffix);
			}
			echo "</ul>\n";
		}

		if ($this->_css)
		{
			// Add style to document head
			$doc = & $mainframe->getDocument();
			$doc->addStyleDeclaration($this->_css);
		}
	}

	function renderLevel($suffix = '-smenu')
	{
		
		/*
		 * Build the CSS class suffix
		 */
		if ($this->_current->active)
		{
			$sfx = $suffix.'_active';
		} else
		{
			$sfx = $suffix;
		}
		
		/*
		 * Print the item
		 */
		echo "<li class=\"item".$sfx.$this->getIconClass($this->_current->class)."\">";
		
		/*
		 * Print a link if it exists
		 */
		if ($this->_current->link != null)
		{
			echo "<a href=\"".$this->_current->link."\">".$this->_current->title."</a> \n";
		} else
		{
			echo $this->_current->title."\n";
		}
		
		/*
		 * Recurse through children if they exist
		 */
		while ($this->_current->hasChildren())
		{
			echo "<ul>\n";
			foreach ($this->_current->getChildren() as $child)
			{
				$this->_current = & $child;
				$this->renderLevel($suffix);
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
	 * @since	1.1
	 */
	function getIconClass($identifier)
	{
		global $mainframe;
		
		static $classes;

		// Initialize the known classes array if it does not exist
		if (!is_array($classes))
		{
			$classes = array();
		}
		
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
				$classes[$identifier] = " icon-16-$class";
			} else
			{
				// We were passed an image path... is it a themeoffice one?
				if (substr($identifier, 0, 15) == 'js/ThemeOffice/')
				{
					// Strip the filename without extension and use that for the classname
					$class = JFile::stripExt(basename($identifier));
					$classes[$identifier] = " icon-16-$class";
				} else
				{
					if ($identifier == null)
					{
						return null;
					}
					// Build the CSS class for the icon
					$class = JFile::makeSafe(JFile::stripExt(basename($identifier)));
					$this->_css  .= "\n.icon-16-$class {\n" .
							"\tbackground: url($identifier) no-repeat;\n" .
							"}\n";
					
					$classes[$identifier] = " icon-16-$class";
				}
			}
		}
		
		return $classes[$identifier];
	}
}

class JMenuNode extends JObject
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
	 * Parent node
	 */
	var $_parent = null;

	/**
	 * Array of Children
	 */
	var $_children = array();

	function __construct($title, $link = null, $class = null, $active = false)
	{
		$this->title		= $title;
		$this->link		= $link;
		$this->class	= $class;
		$this->active	= $active;
	}
	
	function addChild( &$node )
	{
		$node->setParent($this);
		$this->_children[] = & $node;
	}

	function setParent( &$node )
	{
		$this->_parent = & $node;
	}

	function hasChildren()
	{
		return count($this->_children);
	}

	function getChildren()
	{
		return $this->_children;
	}
}
?>