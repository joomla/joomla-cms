<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Workflow\Administrator\Table;

use Joomla\CMS\Access\Rules;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Transition table
 *
 * @since  4.0.0
 */
class TransitionTable extends Table
{
    /**
     * Indicates that columns fully support the NULL value in the database
     *
     * @var    boolean
     *
     * @since  4.0.0
     */
    protected $_supportNullValue = true;

    /**
     * An array of key names to be json encoded in the bind function
     *
     * @var    array
     *
     * @since  4.0.0
     */
    protected $_jsonEncode = [
        'options',
    ];

    /**
     * @param   DatabaseDriver  $db  Database connector object
     *
     * @since  4.0.0
     */
    public function __construct(DatabaseDriver $db)
    {
        parent::__construct('#__workflow_transitions', 'id', $db);
    }

    /**
     * Method to bind an associative array or object to the Table instance.
     * This method only binds properties that are publicly accessible and optionally
     * takes an array of properties to ignore when binding.
     *
     * @param   array|object  $src     An associative array or object to bind to the Table instance.
     * @param   array|string  $ignore  An optional array or space separated list of properties to ignore while binding.
     *
     * @return  boolean  True on success.
     *
     * @since   4.0.0
     * @throws  \InvalidArgumentException
     */
    public function bind($src, $ignore = [])
    {
        // Bind the rules.
        if (isset($src['rules']) && \is_array($src['rules'])) {
            $rules = new Rules($src['rules']);
            $this->setRules($rules);
        }

        return parent::bind($src, $ignore);
    }

    /**
     * Method to compute the default name of the asset.
     * The default name is in the form table_name.id
     * where id is the value of the primary key of the table.
     *
     * @return  string
     *
     * @since  4.0.0
     */
    protected function _getAssetName()
    {
        $k        = $this->_tbl_key;
        $workflow = new WorkflowTable($this->getDbo());
        $workflow->load($this->workflow_id);

        $parts = explode('.', $workflow->extension);

        $extension = array_shift($parts);

        return $extension . '.transition.' . (int) $this->$k;
    }

    /**
     * Method to return the title to use for the asset table.
     *
     * @return  string
     *
     * @since  4.0.0
     */
    protected function _getAssetTitle()
    {
        return $this->title;
    }

    /**
     * Get the parent asset id for the record
     *
     * @param   Table    $table  A Table object for the asset parent.
     * @param   integer  $id     The id for the asset
     *
     * @return  integer  The id of the asset's parent
     *
     * @since  4.0.0
     */
    protected function _getAssetParentId(Table $table = null, $id = null)
    {
        $asset = self::getInstance('Asset', 'JTable', ['dbo' => $this->getDbo()]);

        $workflow = new WorkflowTable($this->getDbo());
        $workflow->load($this->workflow_id);

        $parts = explode('.', $workflow->extension);

        $extension = array_shift($parts);

        $name = $extension . '.workflow.' . (int) $workflow->id;

        $asset->loadByName($name);
        $assetId = $asset->id;

        return !empty($assetId) ? $assetId : parent::_getAssetParentId($table, $id);
    }
}
