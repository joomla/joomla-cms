<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Workflow\Administrator\Controller;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Input\Input;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The stage controller
 *
 * @since  4.0.0
 */
class StageController extends FormController
{
    /**
     * The workflow in where the stage belongs to
     *
     * @var    integer
     * @since  4.0.0
     */
    protected $workflowId;

    /**
     * The extension
     *
     * @var    string
     * @since  4.0.0
     */
    protected $extension;

    /**
     * The section of the current extension
     *
     * @var    string
     * @since  4.0.0
     */
    protected $section;

    /**
     * Constructor.
     *
     * @param   array                 $config   An optional associative array of configuration settings.
     * @param   ?MVCFactoryInterface  $factory  The factory.
     * @param   ?CMSApplication       $app      The Application for the dispatcher
     * @param   ?Input                $input    Input
     *
     * @since   4.0.0
     * @throws  \InvalidArgumentException when no extension or workflow id is set
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null, $app = null, $input = null)
    {
        parent::__construct($config, $factory, $app, $input);

        // If workflow id is not set try to get it from input or throw an exception
        if (empty($this->workflowId)) {
            $this->workflowId = $this->input->getInt('workflow_id');

            if (empty($this->workflowId)) {
                throw new \InvalidArgumentException(Text::_('COM_WORKFLOW_ERROR_WORKFLOW_ID_NOT_SET'));
            }
        }

        // If extension is not set try to get it from input or throw an exception
        if (empty($this->extension)) {
            $extension = $this->input->getCmd('extension');

            $parts = explode('.', $extension);

            $this->extension = array_shift($parts);

            if (!empty($parts)) {
                $this->section = array_shift($parts);
            }

            if (empty($this->extension)) {
                throw new \InvalidArgumentException(Text::_('COM_WORKFLOW_ERROR_EXTENSION_NOT_SET'));
            }
        }
    }

    /**
     * Method to check if you can add a new record.
     *
     * @param   array  $data  An array of input data.
     *
     * @return  boolean
     *
     * @since   4.0.0
     */
    protected function allowAdd($data = [])
    {
        return $this->app->getIdentity()->authorise('core.create', $this->extension . '.workflow.' . (int) $this->workflowId);
    }

    /**
     * Method to check if you can edit a record.
     *
     * @param   array   $data  An array of input data.
     * @param   string  $key   The name of the key for the primary key.
     *
     * @return  boolean
     *
     * @since   4.0.0
     */
    protected function allowEdit($data = [], $key = 'id')
    {
        $recordId = isset($data[$key]) ? (int) $data[$key] : 0;
        $user     = $this->app->getIdentity();

        $record = $this->getModel()->getItem($recordId);

        if (empty($record->id)) {
            return false;
        }

        // Check "edit" permission on record asset (explicit or inherited)
        if ($user->authorise('core.edit', $this->extension . '.stage.' . $recordId)) {
            return true;
        }

        // Check "edit own" permission on record asset (explicit or inherited)
        if ($user->authorise('core.edit.own', $this->extension . '.stage.' . $recordId)) {
            return !empty($record) && $record->created_by == $user->id;
        }

        return false;
    }

    /**
     * Gets the URL arguments to append to an item redirect.
     *
     * @param   integer  $recordId  The primary key id for the item.
     * @param   string   $urlVar    The name of the URL variable for the id.
     *
     * @return  string  The arguments to append to the redirect URL.
     *
     * @since  4.0.0
     */
    protected function getRedirectToItemAppend($recordId = null, $urlVar = 'id')
    {
        $append = parent::getRedirectToItemAppend($recordId);

        $append .= '&workflow_id=' . $this->workflowId . '&extension=' . $this->extension . ($this->section ? '.' . $this->section : '');

        return $append;
    }

    /**
     * Gets the URL arguments to append to a list redirect.
     *
     * @return  string  The arguments to append to the redirect URL.
     *
     * @since  4.0.0
     */
    protected function getRedirectToListAppend()
    {
        $append = parent::getRedirectToListAppend();
        $append .= '&workflow_id=' . $this->workflowId . '&extension=' . $this->extension . ($this->section ? '.' . $this->section : '');

        return $append;
    }
}
