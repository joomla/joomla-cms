<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Tag;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Helper\ContentHelper;

/**
 * Trait for component tags service.
 *
 * @since  __DEPLOY_VERSION__
 */
trait TagServiceTrait
{
	/**
	 * Adds Count Items for Tag Manager.
	 *
	 * @param   \stdClass[]  $items      The content objects
	 * @param   string       $extension  The name of the active view.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function countTagItems(array $items, string $extension)
	{
		$parts   = explode('.', $extension);
		$section = \count($parts) > 1 ? $parts[1] : null;

		$config = (object) array(
			'related_tbl'   => $this->getTableNameForSection($section),
			'state_col'     => $this->getStateColumnForSection($section),
			'group_col'     => 'tag_id',
			'extension'     => $extension,
			'relation_type' => 'tag_assigments',
		);

		ContentHelper::countRelations($items, $config);
	}

	/**
	 * Returns the table for the count items functions for the given section.
	 *
	 * @param   string  $section  The section
	 *
	 * @return  string|null
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getTableNameForSection(string $section = null)
	{
		return null;
	}

	/**
	 * Returns the state column for the count items functions for the given section.
	 *
	 * @param   string  $section  The section
	 *
	 * @return  string|null
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getStateColumnForSection(string $section = null)
	{
		return 'state';
	}
}
