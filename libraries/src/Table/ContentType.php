<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
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
 * Tags table
 *
 * @since  3.1
 */
class ContentType extends Table
{
    /**
     * Constructor
     *
     * @param   DatabaseDriver        $db          Database connector object
     * @param   ?DispatcherInterface  $dispatcher  Event dispatcher for this table
     *
     * @since   3.1
     */
    public function __construct(DatabaseDriver $db, ?DispatcherInterface $dispatcher = null)
    {
        parent::__construct('#__content_types', 'type_id', $db, $dispatcher);
    }

    /**
     * Overloaded check method to ensure data integrity.
     *
     * @return  boolean  True on success.
     *
     * @since   3.1
     * @throws  \UnexpectedValueException
     */
    public function check()
    {
        try {
            parent::check();
        } catch (\Exception $e) {
            $this->setError($e->getMessage());

            return false;
        }

        // Check for valid name.
        if (trim($this->type_title) === '') {
            throw new \UnexpectedValueException(\sprintf('The title is empty'));
        }

        $this->type_title = ucfirst($this->type_title);

        if (empty($this->type_alias)) {
            throw new \UnexpectedValueException(\sprintf('The type_alias is empty'));
        }

        return true;
    }

    /**
     * Overridden Table::store.
     *
     * @param   boolean  $updateNulls  True to update fields even if they are null.
     *
     * @return  boolean  True on success.
     *
     * @since   3.1
     */
    public function store($updateNulls = false)
    {
        // Verify that the alias is unique
        $table = new self($this->getDbo(), $this->getDispatcher());

        if ($table->load(['type_alias' => $this->type_alias]) && ($table->type_id != $this->type_id || $this->type_id == 0)) {
            $this->setError(Text::_('COM_TAGS_ERROR_UNIQUE_ALIAS'));

            return false;
        }

        return parent::store($updateNulls);
    }

    /**
     * Method to expand the field mapping
     *
     * @param   boolean  $assoc  True to return an associative array.
     *
     * @return  mixed  Array or object with field mappings. Defaults to object.
     *
     * @since   3.1
     */
    public function fieldmapExpand($assoc = true)
    {
        return $this->fieldmap = json_decode($this->fieldmappings, $assoc);
    }

    /**
     * Method to get the id given the type alias
     *
     * @param   string  $typeAlias  Content type alias (for example, 'com_content.article').
     *
     * @return  mixed  type_id for this alias if successful, otherwise null.
     *
     * @since   3.2
     */
    public function getTypeId($typeAlias)
    {
        $db    = $this->getDbo();
        $query = $db->getQuery(true);
        $query->select($db->quoteName('type_id'))
            ->from($db->quoteName($this->_tbl))
            ->where($db->quoteName('type_alias') . ' = :type_alias')
            ->bind(':type_alias', $typeAlias);
        $db->setQuery($query);

        return $db->loadResult();
    }

    /**
     * Method to get the Table object for the content type from the table object.
     *
     * @return  mixed  Table object on success, otherwise false.
     *
     * @since   3.2
     *
     * @throws  \RuntimeException
     */
    public function getContentTable()
    {
        $result    = false;
        $tableInfo = json_decode($this->table);

        if (\is_object($tableInfo) && isset($tableInfo->special)) {
            if (\is_object($tableInfo->special) && isset($tableInfo->special->type) && isset($tableInfo->special->prefix)) {
                $class = $tableInfo->special->class ?? 'Joomla\\CMS\\Table\\Table';

                if (!class_implements($class, 'Joomla\\CMS\\Table\\TableInterface')) {
                    // This isn't an instance of TableInterface. Stop.
                    throw new \RuntimeException('Class must be an instance of Joomla\\CMS\\Table\\TableInterface');
                }

                $result = $class::getInstance($tableInfo->special->type, $tableInfo->special->prefix);
            }
        }

        return $result;
    }
}
