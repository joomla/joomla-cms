<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Pathway;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Menu\MenuItem;
use Joomla\Module\Menu\Administrator\Menu\CssMenu;
use Joomla\Registry\Registry;

/**
 * Class to manage the administrator application pathway.
 *
 * @since  4.0.0
 */
class AdministratorPathway extends Pathway
{
	/**
	 * Keep track if the parents have been set
	 *
	 * @var    boolean
	 * @since  4.0.0
	 */
	private $parentSet = false;

	/**
	 * Class constructor.
	 *
	 * @param   AdministratorApplication  $app  Application Object
	 *
	 * @since   4.0.0
	 */
	public function __construct(AdministratorApplication $app = null)
	{
		$this->pathway = array();
		$menu          = new CssMenu($app);
		$params        = new Registry;
		$root          = $menu->load($params, true);
		$input         = $app->input;
		$option        = $input->getCmd('option');
		$view          = $input->getCmd('view');
		$extension     = $input->getCmd('extension');
		$context       = $input->getCmd('context');
		$menuType      = $input->getCmd('menutype');
		$clientId      = $input->get('client_id', false);

		$link = 'index.php?option=' . $option;

		if ($view)
		{
			$link .= '&view=' . $view;
		}

		if ($extension)
		{
			$link .= '&extension=' . $extension;
		}

		if ($context)
		{
			$link .= '&context=' . $context;
		}

		if ($menuType || $menuType === '')
		{
			$link .= '&menutype=' . $menuType;
		}

		if (!$menuType && $clientId !== false)
		{
			$link .= '&client_id=' . $clientId;
		}

		// Add the children
		$this->addChildren($root->getChildren(), $link, $option);
	}

	/**
	 * Render the breadcrumbs.
	 *
	 * @param   array   $children  The children to render
	 * @param   string  $link      The link of the current page
	 * @param   string  $option    The extension of the current page
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	private function addChildren(array $children, string $link, string $option): void
	{
		// Loop through the children to add them
		foreach ($children as $child)
		{
			// Check if there are any children
			if ($child->hasChildren() && !$this->parentSet)
			{
				$this->addChildren($child->getChildren(), $link, $option);
			}

			// If parents are set, do nothing more
			if ($this->parentSet)
			{
				return;
			}

			// Check if we need to add the item
			if (($child->link === $link || $child->link === 'index.php'))
			{
				if ($child->link !== 'index.php')
				{
					$this->parentSet = true;
					$this->addParent($child, $link);
				}

				// Add the item
				$activeLink = $child->link === $link ? '' : $child->link;
				$this->addItem(Text::_($child->text), $activeLink);

			}
		}
	}

	/**
	 * Add parents of a given menu item.
	 *
	 * @param   MenuItem  $child  The child object to render parents for
	 * @param   string    $link   The link to match
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	private function addParent(MenuItem $child, string $link): void
	{
		$parent = $child->getParent();

		if ($parent->level !== null)
		{
			$this->addParent($parent, $link);
		}

		$activeLink = $child->link === $link ? '' : $child->link;

		if (in_array($parent->type, array('heading', 'container')))
		{
			$activeLink = 'index.php?option=com_cpanel&view=cpanel&dashboard=' . $parent->dashboard;
		}

		$this->addItem(Text::_($parent->text), $activeLink);
	}
}
