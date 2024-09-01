<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Templates\Administrator\Table;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\ParameterType;
use Joomla\Event\DispatcherInterface;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Template style table class.
 *
 * @since  1.6
 */
class StyleTable extends Table
{
    /**
     * Constructor
     *
     * @param   DatabaseDriver        $db          Database connector object
     * @param   ?DispatcherInterface  $dispatcher  Event dispatcher for this table
     *
     * @since   1.6
     */
    public function __construct(DatabaseDriver $db, ?DispatcherInterface $dispatcher = null)
    {
        parent::__construct('#__template_styles', 'id', $db, $dispatcher);
    }

    /**
     * Overloaded bind function to pre-process the params.
     *
     * @param   array  $array   Named array
     * @param   mixed  $ignore  An optional array or space separated list of properties to ignore while binding.
     *
     * @return  null|string null if operation was satisfactory, otherwise returns an error
     *
     * @since   1.6
     */
    public function bind($array, $ignore = '')
    {
        if (isset($array['params']) && \is_array($array['params'])) {
            $registry        = new Registry($array['params']);
            $array['params'] = (string) $registry;
        }

        // Verify that the default style is not unset
        if ($array['home'] == '0' && $this->home == '1') {
            $this->setError(Text::_('COM_TEMPLATES_ERROR_CANNOT_UNSET_DEFAULT_STYLE'));

            return false;
        }

        return parent::bind($array, $ignore);
    }

    /**
     * Overloaded check method to ensure data integrity.
     *
     * @return  boolean  True on success.
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

        if (empty($this->title)) {
            $this->setError(Text::_('COM_TEMPLATES_ERROR_STYLE_REQUIRES_TITLE'));

            return false;
        }

        return true;
    }

    /**
     * Overloaded store method to ensure unicity of default style.
     *
     * @param   boolean  $updateNulls  True to update fields even if they are null.
     *
     * @return  boolean  True on success.
     *
     * @since   1.6
     */
    public function store($updateNulls = false)
    {
        if ($this->home != '0') {
            $clientId = (int) $this->client_id;
            $query    = $this->_db->createQuery()
                ->update($this->_db->quoteName('#__template_styles'))
                ->set($this->_db->quoteName('home') . ' = ' . $this->_db->quote('0'))
                ->where($this->_db->quoteName('client_id') . ' = :clientid')
                ->where($this->_db->quoteName('home') . ' = :home')
                ->bind(':clientid', $clientId, ParameterType::INTEGER)
                ->bind(':home', $this->home);
            $this->_db->setQuery($query);
            $this->_db->execute();
        }

        return parent::store($updateNulls);
    }

    /**
     * Overloaded store method to unsure existence of a default style for a template.
     *
     * @param   mixed  $pk  An optional primary key value to delete.  If not set the instance property value is used.
     *
     * @return  boolean  True on success.
     *
     * @since   1.6
     */
    public function delete($pk = null)
    {
        $k  = $this->_tbl_key;
        $pk = \is_null($pk) ? $this->$k : $pk;

        if (!\is_null($pk)) {
            $clientId = (int) $this->client_id;
            $query    = $this->_db->createQuery()
                ->select($this->_db->quoteName('id'))
                ->from($this->_db->quoteName('#__template_styles'))
                ->where($this->_db->quoteName('client_id') . ' = :clientid')
                ->where($this->_db->quoteName('template') . ' = :template')
                ->bind(':template', $this->template)
                ->bind(':clientid', $clientId, ParameterType::INTEGER);
            $this->_db->setQuery($query);
            $results = $this->_db->loadColumn();

            if (\count($results) == 1 && $results[0] == $pk) {
                $this->setError(Text::_('COM_TEMPLATES_ERROR_CANNOT_DELETE_LAST_STYLE'));

                return false;
            }
        }

        return parent::delete($pk);
    }
}
