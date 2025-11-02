<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Tag;

use Joomla\CMS\Helper\ContentHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Trait for component tags service.
 *
 * @since  4.0.0
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
     * @since   4.0.0
     */
    public function countTagItems(array $items, string $extension)
    {
        $parts   = explode('.', $extension);
        $section = \count($parts) > 1 ? $parts[1] : null;

        $config = (object) [
            'related_tbl'   => $this->getTableNameForSection($section),
            'state_col'     => $this->getStateColumnForSection($section),
            'group_col'     => 'tag_id',
            'extension'     => $extension,
            'relation_type' => 'tag_assigments',
        ];

        ContentHelper::countRelations($items, $config);
    }

    /**
     * Returns the table for the count items functions for the given section.
     *
     * @param   ?string  $section  The section
     *
     * @return  string|null
     *
     * @since   4.0.0
     */
    protected function getTableNameForSection(?string $section = null)
    {
        return null;
    }

    /**
     * Returns the state column for the count items functions for the given section.
     *
     * @param   ?string  $section  The section
     *
     * @return  string|null
     *
     * @since   4.0.0
     */
    protected function getStateColumnForSection(?string $section = null)
    {
        return 'state';
    }
}
