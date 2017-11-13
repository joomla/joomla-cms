<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Toolbar;

defined('_JEXEC') or die;

/**
 * Interface for creating toolbar objects
 *
 * @since  __DEPLOY_VERSION__
 */
interface ToolbarFactoryInterface
{
	/**
	 * Creates a new toolbar button.
	 *
	 * @param   Toolbar  $toolbar  The Toolbar instance to attach to the button
	 * @param   string   $type     Button Type
	 *
	 * @return  LegacyToolbarButton
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \InvalidArgumentException
	 */
	public function createButton(Toolbar $toolbar, string $type): LegacyToolbarButton;

	/**
	 * Creates a new Toolbar object.
	 *
	 * @param   string  $name  The toolbar name.
	 *
	 * @return  Toolbar
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function createToolbar(string $name = 'toolbar'): Toolbar;
}
