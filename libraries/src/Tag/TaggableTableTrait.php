<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Tag;

use Joomla\CMS\Helper\TagsHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Defines the trait for a Taggable Table Class.
 *
 * @since  3.10.0
 */
trait TaggableTableTrait
{
    /**
     * The tags helper property
     *
     * @var    TagsHelper
     * @since  4.0.0
     * @note   The tags helper property is set to public for backwards compatibility for Joomla 4.0. It will be made a
     *         protected property in Joomla 5.0
     */
    public $tagsHelper;

    /**
     * Get the tags helper
     *
     * @return  TagsHelper  The tags helper object
     *
     * @since   4.0.0
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
     * @since   4.0.0
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
     * @since   4.0.0
     */
    public function clearTagsHelper(): void
    {
        $this->tagsHelper = null;
    }
}
