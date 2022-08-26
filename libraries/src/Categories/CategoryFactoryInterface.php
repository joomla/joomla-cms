<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Categories;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Category factory interface
 *
 * @since  3.10.0
 */
interface CategoryFactoryInterface
{
    /**
     * Creates a category.
     *
     * @param   array   $options  The options
     * @param   string  $section  The section
     *
     * @return  CategoryInterface
     *
     * @since   3.10.0
     *
     * @throws  SectionNotFoundException
     */
    public function createCategory(array $options = [], string $section = ''): CategoryInterface;
}
