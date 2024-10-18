<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Table;

use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseDriver;
use Joomla\Event\DispatcherInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Languages table.
 *
 * @since  1.7.0
 */
class Language extends Table
{
    /**
     * Constructor
     *
     * @param   DatabaseDriver        $db          Database connector object
     * @param   ?DispatcherInterface  $dispatcher  Event dispatcher for this table
     *
     * @since   1.7.0
     */
    public function __construct(DatabaseDriver $db, ?DispatcherInterface $dispatcher = null)
    {
        parent::__construct('#__languages', 'lang_id', $db, $dispatcher);
    }

    /**
     * Overloaded check method to ensure data integrity
     *
     * @return  boolean  True on success
     *
     * @since   1.7.0
     */
    public function check()
    {
        try {
            parent::check();
        } catch (\Exception $e) {
            $this->setError($e->getMessage());

            return false;
        }

        if (trim($this->title) === '') {
            $this->setError(Text::_('JLIB_DATABASE_ERROR_LANGUAGE_NO_TITLE'));

            return false;
        }

        return true;
    }

    /**
     * Overrides Table::store to check unique fields.
     *
     * @param   boolean  $updateNulls  True to update fields even if they are null.
     *
     * @return  boolean  True on success.
     *
     * @since   2.5.0
     */
    public function store($updateNulls = false)
    {
        $table = new self($this->getDbo(), $this->getDispatcher());

        // Verify that the language code is unique
        if ($table->load(['lang_code' => $this->lang_code]) && ($table->lang_id != $this->lang_id || $this->lang_id == 0)) {
            $this->setError(Text::_('JLIB_DATABASE_ERROR_LANGUAGE_UNIQUE_LANG_CODE'));

            return false;
        }

        // Verify that the sef field is unique
        if ($table->load(['sef' => $this->sef]) && ($table->lang_id != $this->lang_id || $this->lang_id == 0)) {
            $this->setError(Text::_('JLIB_DATABASE_ERROR_LANGUAGE_UNIQUE_IMAGE'));

            return false;
        }

        // Verify that the image field is unique
        if ($this->image && $table->load(['image' => $this->image]) && ($table->lang_id != $this->lang_id || $this->lang_id == 0)) {
            $this->setError(Text::_('JLIB_DATABASE_ERROR_LANGUAGE_UNIQUE_IMAGE'));

            return false;
        }

        return parent::store($updateNulls);
    }

    /**
     * Method to compute the default name of the asset.
     * The default name is in the form table_name.id
     * where id is the value of the primary key of the table.
     *
     * @return  string
     *
     * @since   3.8.0
     */
    protected function _getAssetName()
    {
        return 'com_languages.language.' . $this->lang_id;
    }

    /**
     * Method to return the title to use for the asset table.
     *
     * @return  string
     *
     * @since   3.8.0
     */
    protected function _getAssetTitle()
    {
        return $this->title;
    }

    /**
     * Method to get the parent asset under which to register this one.
     * By default, all assets are registered to the ROOT node with ID,
     * which will default to 1 if none exists.
     * The extended class can define a table and id to lookup.  If the
     * asset does not exist it will be created.
     *
     * @param   ?Table    $table  A Table object for the asset parent.
     * @param   ?integer  $id     Id to look up
     *
     * @return  integer
     *
     * @since   3.8.0
     */
    protected function _getAssetParentId(?Table $table = null, $id = null)
    {
        $assetId = null;
        $asset   = new Asset($this->getDbo(), $this->getDispatcher());

        if ($asset->loadByName('com_languages')) {
            $assetId = $asset->id;
        }

        return $assetId ?? parent::_getAssetParentId($table, $id);
    }
}
