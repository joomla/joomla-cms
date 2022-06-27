<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_redirect
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Redirect\Administrator\Table;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;

/**
 * Link Table for Redirect.
 *
 * @since  1.6
 */
class LinkTable extends Table
{
    /**
     * Constructor
     *
     * @param   DatabaseDriver  $db  Database object.
     *
     * @since   1.6
     */
    public function __construct(DatabaseDriver $db)
    {
        parent::__construct('#__redirect_links', 'id', $db);
    }

    /**
     * Overloaded check function
     *
     * @return  boolean
     *
     * @since   1.6
     */
    public function check()
    {
        try {
            parent::check();
        } catch (\Exception $e) {
            $this->setError($e->getMessage());

            return false;
        }

        $this->old_url = trim(rawurldecode($this->old_url));
        $this->new_url = trim(rawurldecode($this->new_url));

        // Check for valid name.
        if (empty($this->old_url)) {
            $this->setError(Text::_('COM_REDIRECT_ERROR_SOURCE_URL_REQUIRED'));

            return false;
        }

        // Check for NOT NULL.
        if (empty($this->referer)) {
            $this->referer = '';
        }

        // Check for valid name if not in advanced mode.
        if (empty($this->new_url) && ComponentHelper::getParams('com_redirect')->get('mode', 0) == false) {
            $this->setError(Text::_('COM_REDIRECT_ERROR_DESTINATION_URL_REQUIRED'));

            return false;
        } elseif (empty($this->new_url) && ComponentHelper::getParams('com_redirect')->get('mode', 0) == true) {
            // Else if an empty URL and in redirect mode only throw the same error if the code is a 3xx status code
            if ($this->header < 400 && $this->header >= 300) {
                $this->setError(Text::_('COM_REDIRECT_ERROR_DESTINATION_URL_REQUIRED'));

                return false;
            }
        }

        // Check for duplicates
        if ($this->old_url == $this->new_url) {
            $this->setError(Text::_('COM_REDIRECT_ERROR_DUPLICATE_URLS'));

            return false;
        }

        $db = $this->getDbo();

        // Check for existing name
        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->select($db->quoteName('old_url'))
            ->from($db->quoteName('#__redirect_links'))
            ->where($db->quoteName('old_url') . ' = :url')
            ->bind(':url', $this->old_url);
        $db->setQuery($query);
        $urls = $db->loadAssocList();

        foreach ($urls as $url) {
            if ($url['old_url'] === $this->old_url && (int) $url['id'] != (int) $this->id) {
                $this->setError(Text::_('COM_REDIRECT_ERROR_DUPLICATE_OLD_URL'));

                return false;
            }
        }

        return true;
    }

    /**
     * Overridden store method to set dates.
     *
     * @param   boolean  $updateNulls  True to update fields even if they are null.
     *
     * @return  boolean  True on success.
     *
     * @since   1.6
     */
    public function store($updateNulls = false)
    {
        $date = Factory::getDate()->toSql();

        if (!$this->id) {
            // New record.
            $this->created_date = $date;
            $this->modified_date = $date;
        }

        if (empty($this->modified_date)) {
            $this->modified_date = $this->created_date;
        }

        return parent::store($updateNulls);
    }
}
