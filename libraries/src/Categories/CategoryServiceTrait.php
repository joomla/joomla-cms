<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Categories;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Helper\ContentHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Trait for component categories service.
 *
 * @since  4.0.0
 */
trait CategoryServiceTrait
{
    /**
     * The categories factory
     *
     * @var  CategoryFactoryInterface
     *
     * @since  4.0.0
     */
    private $categoryFactory;

    /**
     * Returns the category service.
     *
     * @param   array   $options  The options
     * @param   string  $section  The section
     *
     * @return  CategoryInterface
     *
     * @since   4.0.0
     * @throws  SectionNotFoundException
     */
    public function getCategory(array $options = [], $section = ''): CategoryInterface
    {
        return $this->categoryFactory->createCategory($options, $section);
    }

    /**
     * Sets the internal category factory.
     *
     * @param   CategoryFactoryInterface  $categoryFactory  The categories factory
     *
     * @return  void
     *
     * @since  4.0.0
     */
    public function setCategoryFactory(CategoryFactoryInterface $categoryFactory)
    {
        $this->categoryFactory = $categoryFactory;
    }

    /**
     * Adds Count Items for Category Manager.
     *
     * @param   \stdClass[]  $items    The category objects
     * @param   string       $section  The section
     *
     * @return  void
     *
     * @since   4.0.0
     * @throws  \Exception
     */
    public function countItems(array $items, string $section)
    {
        $config = (object) [
            'related_tbl'   => $this->getTableNameForSection($section),
            'state_col'     => $this->getStateColumnForSection($section),
            'group_col'     => 'catid',
            'relation_type' => 'category_or_group',
        ];

        ContentHelper::countRelations($items, $config);
    }

    /**
     * Prepares the category form
     *
     * @param   Form          $form  The form to change
     * @param   array|object  $data  The form data
     *
     * @return void
     */
    public function prepareForm(Form $form, $data)
    {
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
