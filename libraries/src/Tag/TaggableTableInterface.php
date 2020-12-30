<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Tag;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Table\TableInterface;

/**
 * Interface for a taggable Table class
 *
 * @since  __DEPLOY_VERSION__
 */
interface TaggableTableInterface extends TableInterface
{
	/**
	 * Get the type alias for the tags mapping table
	 *
	 * The type alias generally is the internal component name with the
	 * content type. Ex.: com_content.article
	 *
	 * @return  string  The alias as described above
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getTypeAlias();

	/**
	 * Get the tags helper
	 *
	 * @return  ?TagsHelper  The tags helper object
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getTagsHelper(): ?TagsHelper;

	/**
	 * Set the tags helper
	 *
	 * @string   TagsHelper  $tagsHelper  The tags helper object
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setTagsHelper(TagsHelper $tagsHelper): void;

	/**
	 * Clears a set tags helper
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function clearTagsHelper(): void;
}
