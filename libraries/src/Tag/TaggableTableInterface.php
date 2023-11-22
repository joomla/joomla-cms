<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Tag;

use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Table\TableInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Interface for a taggable Table class
 *
 * @since  3.10.0
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
     * @since   4.0.0
     */
    public function getTypeAlias();

    /**
     * Get the tags helper
     *
     * @return  ?TagsHelper  The tags helper object
     *
     * @since   4.0.0
     */
    public function getTagsHelper(): ?TagsHelper;

    /**
     * Set the tags helper
     *
     * @param   TagsHelper  $tagsHelper  The tags helper object
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function setTagsHelper(TagsHelper $tagsHelper): void;

    /**
     * Clears a set tags helper
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function clearTagsHelper(): void;
}
