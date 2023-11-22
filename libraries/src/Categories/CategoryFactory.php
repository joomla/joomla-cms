<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Categories;

use Joomla\Database\DatabaseAwareInterface;
use Joomla\Database\DatabaseAwareTrait;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Option based categories factory.
 *
 * @since  3.10.0
 */
class CategoryFactory implements CategoryFactoryInterface
{
    use DatabaseAwareTrait;

    /**
     * The namespace to create the categories from.
     *
     * @var    string
     * @since  4.0.0
     */
    private $namespace;

    /**
     * The namespace must be like:
     * Joomla\Component\Content
     *
     * @param   string  $namespace  The namespace
     *
     * @since   4.0.0
     */
    public function __construct($namespace)
    {
        $this->namespace = $namespace;
    }

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
    public function createCategory(array $options = [], string $section = ''): CategoryInterface
    {
        $className = trim($this->namespace, '\\') . '\\Site\\Service\\' . ucfirst($section) . 'Category';

        if (!class_exists($className)) {
            throw new SectionNotFoundException();
        }

        $category = new $className($options);

        if ($category instanceof DatabaseAwareInterface) {
            $category->setDatabase($this->getDatabase());
        }

        return $category;
    }
}
