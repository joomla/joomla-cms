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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/**
 * Defines the trait for a Taggable Table Class.
 *
 * @since  __DEPLOY_VERSION__
 */
trait TaggableTableTrait
{
	/**
	 * The tags helper property
	 *
	 * @var    TagsHelper
	 * @since  __DEPLOY_VERSION__
	 * @note   The tags helper property is set to public for backwards compatibility for Joomla 4.0. It will be made a
	 *         protected property in Joomla 5.0
	 */
	public $tagsHelper;

	/**
	 * Get the tags helper
	 *
	 * @return  TagsHelper  The tags helper object
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getTagsHelper(): ?TagsHelper
	{
		return $this->tagsHelper;
	}

	/**
	 * Set the tags helper
	 *
	 * @param   TagsHelper   $tagsHelper  The tags helper to be set
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setTagsHelper(TagsHelper $tagsHelper): void
	{
		$this->tagsHelper = $tagsHelper;
	}

	/**
	 * Clears the tags helper
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function clearTagsHelper(): void
	{
		$this->tagsHelper = null;
	}
}
