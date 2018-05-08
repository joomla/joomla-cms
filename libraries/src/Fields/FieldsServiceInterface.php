<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Fields;

defined('JPATH_PLATFORM') or die;

/**
 * The fields service.
 *
 * @since  __DEPLOY_VERSION__
 */
interface FieldsServiceInterface
{
	/**
	 * Returns a valid section for the given section. If it is not valid then null
	 * is returned.
	 *
	 * @param   string  $section  The section to get the mapping for
	 * @param   object  $item     The item
	 *
	 * @return  string|null  The new section
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function validateSection($section, $item = null);

	/**
	 * Returns valid contexts.
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getContexts(): array;
}
