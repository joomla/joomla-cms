<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Categories;

use Joomla\CMS\Table\Table;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Trait for controllers of components which assign records to categories.
 *
 * The using class has to provide the application (`$this->app`) and the component name (`$this->option`), as
 * the MVC controllers do.
 *
 * @since  6.2.1
 */
trait CategoryChangeTrait
{
    /**
     * Method to get the id of the category given in the data, as the database will store it.
     *
     * The value is converted with an integer cast, like the database does when the record is stored. A value such
     * as "300abc" or "0.300e3" is stored in category 300, so it has to be checked as category 300.
     *
     * @param   array  $data  The data to save, the target category is taken from the catid key.
     *
     * @return  integer|null  The category id, 0 if there is none, null if the value is not a scalar.
     *
     * @since   6.2.1
     */
    private function getTargetCategoryId(array $data): ?int
    {
        $catid = $data['catid'] ?? null;

        if ($catid !== null && !\is_scalar($catid)) {
            return null;
        }

        return (int) $catid;
    }

    /**
     * Method to check if a new record may be created in the category given in the data.
     *
     * The create permission is checked in the target category. A numeric category is read with an integer cast,
     * like the database does when the record is stored, so that spellings such as "0.300e3" cannot bypass the check.
     * Nothing is checked if the data does not contain a category id, a category passed as text (new category) is
     * created by the model. A category that is not a scalar value is refused.
     *
     * @param   array  $data  The data to save, the target category is taken from the catid key.
     *
     * @return  boolean  True if a record may be created with the given category.
     *
     * @since   6.2.1
     */
    protected function allowCategoryAdd(array $data): bool
    {
        $catid = $this->getTargetCategoryId($data);

        if ($catid === null) {
            return false;
        }

        if (!$catid) {
            return true;
        }

        return $this->app->getIdentity()->authorise('core.create', $this->option . '.category.' . $catid);
    }

    /**
     * Method to check if a record may be saved with the category given in the data.
     *
     * Moving an existing record to another category requires the create permission in the target category. Nothing
     * is checked if the data does not contain a target category id or the category does not change. A new
     * record (no record id) is checked like in allowCategoryAdd(). A category passed as text is not checked, it is
     * created by the model.
     *
     * @param   Table    $table     A table object of the record type, used to load the stored record.
     * @param   integer  $recordId  The id of the record that is saved.
     * @param   array    $data      The data to save, the target category is taken from the catid key.
     *
     * @return  boolean  True if the record may be saved with the given category.
     *
     * @since   6.2.1
     */
    protected function allowCategoryChange(Table $table, int $recordId, array $data): bool
    {
        if (!$recordId) {
            return $this->allowCategoryAdd($data);
        }

        $catid = $this->getTargetCategoryId($data);

        if ($catid === null) {
            return false;
        }

        if (!$catid || !$table->load($recordId) || (int) $table->catid === $catid) {
            return true;
        }

        return $this->app->getIdentity()->authorise('core.create', $this->option . '.category.' . $catid);
    }
}
