<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_newsfeeds
 *
 * @copyright   (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Newsfeeds\Administrator\Table;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\String\PunycodeHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Tag\TaggableTableInterface;
use Joomla\CMS\Tag\TaggableTableTrait;
use Joomla\CMS\User\CurrentUserInterface;
use Joomla\CMS\User\CurrentUserTrait;
use Joomla\CMS\Versioning\VersionableTableInterface;
use Joomla\Database\DatabaseInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\String\StringHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Newsfeed Table class.
 *
 * @since  1.6
 */
class NewsfeedTable extends Table implements VersionableTableInterface, TaggableTableInterface, CurrentUserInterface
{
    use TaggableTableTrait;
    use CurrentUserTrait;

    /**
     * Indicates that columns fully support the NULL value in the database
     *
     * @var    boolean
     * @since  4.0.0
     */
    protected $_supportNullValue = true;

    /**
     * Ensure the params, metadata and images are json encoded in the bind method
     *
     * @var    array
     * @since  3.3
     */
    protected $_jsonEncode = ['params', 'metadata', 'images'];

    /**
     * Constructor
     *
     * @param   DatabaseInterface     $db          Database connector object
     * @param   ?DispatcherInterface  $dispatcher  Event dispatcher for this table
     *
     * @since   1.6
     */
    public function __construct(DatabaseInterface $db, ?DispatcherInterface $dispatcher = null)
    {
        $this->typeAlias = 'com_newsfeeds.newsfeed';
        parent::__construct('#__newsfeeds', 'id', $db, $dispatcher);
        $this->setColumnAlias('title', 'name');
    }

    /**
     * Overloaded check method to ensure data integrity.
     *
     * @return  boolean  True on success.
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
        if (trim($this->name) == '') {
            $this->setError(Text::_('COM_NEWSFEEDS_WARNING_PROVIDE_VALID_NAME'));

            return false;
        }

        if (empty($this->alias)) {
            $this->alias = $this->name;
        }

        $this->alias = ApplicationHelper::stringURLSafe($this->alias, $this->language);

        if (trim(str_replace('-', '', $this->alias)) == '') {
            $this->alias = Factory::getDate()->format('Y-m-d-H-i-s');
        }

        // Check for a valid category.
        if (!$this->catid = (int) $this->catid) {
            $this->setError(Text::_('JLIB_DATABASE_ERROR_CATEGORY_REQUIRED'));

            return false;
        }

        // Check the publish down date is not earlier than publish up.
        if ((int) $this->publish_down > 0 && $this->publish_down < $this->publish_up) {
            $this->setError(Text::_('JGLOBAL_START_PUBLISH_AFTER_FINISH'));

            return false;
        }

        // Clean up description -- eliminate quotes and <> brackets
        if (!empty($this->metadesc)) {
            // Only process if not empty
            $bad_characters = ["\"", '<', '>'];
            $this->metadesc = StringHelper::str_ireplace($bad_characters, '', $this->metadesc);
        }

        if (!$this->hits) {
            $this->hits = 0;
        }

        return true;
    }

    /**
     * Overridden \Joomla\CMS\Table\Table::store to set modified data.
     *
     * @param   boolean  $updateNulls  True to update fields even if they are null.
     *
     * @return  boolean  True on success.
     *
     * @since   1.6
     */
    public function store($updateNulls = true)
    {
        $date = Factory::getDate();
        $user = $this->getCurrentUser();

        // Set created date if not set.
        if (!(int) $this->created) {
            $this->created = $date->toSql();
        }

        if ($this->id) {
            // Existing item
            $this->modified_by = $user->id;
            $this->modified    = $date->toSql();
        } else {
            // Field created_by can be set by the user, so we don't touch it if it's set.
            if (empty($this->created_by)) {
                $this->created_by = $user->id;
            }

            if (!(int) $this->modified) {
                $this->modified = $this->created;
            }

            if (empty($this->modified_by)) {
                $this->modified_by = $this->created_by;
            }
        }

        // Set publish_up, publish_down to null if not set
        if (!$this->publish_up) {
            $this->publish_up = null;
        }

        if (!$this->publish_down) {
            $this->publish_down = null;
        }

        // Verify that the alias is unique
        $table = new self($this->getDbo());

        if ($table->load(['alias' => $this->alias, 'catid' => $this->catid]) && ($table->id != $this->id || $this->id == 0)) {
            // Is the existing newsfeed trashed?
            $this->setError(Text::_('COM_NEWSFEEDS_ERROR_UNIQUE_ALIAS'));

            if ($table->published === -2) {
                $this->setError(Text::_('COM_NEWSFEEDS_ERROR_UNIQUE_ALIAS_TRASHED'));
            }

            return false;
        }

        // Save links as punycode.
        $this->link = PunycodeHelper::urlToPunycode($this->link);

        return parent::store($updateNulls);
    }

    /**
     * Get the type alias for the history table
     *
     * @return  string  The alias as described above
     *
     * @since   4.0.0
     */
    public function getTypeAlias()
    {
        return $this->typeAlias;
    }
}
