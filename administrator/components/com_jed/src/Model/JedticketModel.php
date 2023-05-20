<?php

/**
 * @package           JED
 *
 * @subpackage        Tickets
 *
 * @copyright     (C) 2022 Open Source Matters, Inc.  <https://www.joomla.org>
 * @license           GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Jed\Component\Jed\Administrator\Model;

// No direct access.
// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Table\Table;

/**
 * JED Ticket model.
 *
 * @since  4.0.0
 */
class JedticketModel extends AdminModel
{
    /**
     * @var    string    Alias to manage history control
     * @since   4.0.0
     */
    public $typeAlias = 'com_jed.jedticket';
    /**
     * @var      string    The prefix to use with controller messages.
     * @since  4.0.0
     */
    protected $text_prefix = 'COM_JED';
    /**
     * @var null  Item data
     * @since  4.0.0
     */
    protected $item = null;

    /**
     * @var int  Linked Item Type
     * @since  4.0.0
     */
    protected int $linked_item_type;
    /**
     * @var int  Id of linked Item
     * @since  4.0.0
     */
    protected int $linked_item_id;
    /**
     * @var int  User Id of ticket creator
     * @since  4.0.0
     */
    protected int $ticket_creator;

    /**
     * Method to get the record form.
     *
     * @param   array    $data      An optional array of data for the form to interogate.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  Form|bool  A Form object on success, false on failure
     *
     * @since  4.0.0
     *
     * @throws
     */
    public function getForm($data = [], $loadData = true, $formname = 'jform'): Form
    {
        // Get the form.
        $form = $this->loadForm(
            'com_jed.jedticket',
            'jedticket',
            ['control'        => $formname,
                  'load_data' => $loadData,
            ]
        );


        if (empty($form)) {
            return false;
        }

        return $form;
    }

    /**
     * Method to get a single record.
     *
     * @param   null  $pk  The id of the primary key.
     *
     * @return CMSObject Object on success
     *
     * @since  4.0.0
     * @throws Exception
     */
    public function getItem($pk = null): CMSObject
    {

        $pk   = (!empty($pk)) ? $pk : (int) $this->getState($this->getName() . '.id');
        $item = parent::getItem($pk);

        $this->linked_item_type = $item->linked_item_type;
        $this->linked_item_id   = $item->linked_item_id;
        $this->ticket_creator   = $item->created_by;

        return $item;
    }

    /**
     * Returns a reference to a Table object, always creating it.
     *
     * @param   string  $name
     * @param   string  $prefix  A prefix for the table class name. Optional.
     * @param   array   $options
     *
     * @return    Table    A database object
     *
     * @since  4.0.0
     * @throws Exception
     */
    public function getTable($name = 'Jedticket', $prefix = 'Administrator', $options = []): Table
    {
        return parent::getTable($name, $prefix, $options);
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return   mixed  The data for the form.
     *
     * @since  4.0.0
     *
     * @throws
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $data = Factory::getApplication()->getUserState('com_jed.edit.jedticket.data', []);

        if (empty($data)) {
            if ($this->item === null) {
                $this->item = $this->getItem();
            }

            $data = $this->item;
        }

        return $data;
    }

    /**
     *
     * Method to get VEL Report Item Data
     *
     * @return  array|bool  An array on success, false on failure
     *
     * @since 4.0.0
     */
    public function getReviewData(): array
    {
        // Create a new query object.
        $db = $this->getDatabase();

        $query = $db->getQuery(true);

        // Select all fields
        $query->select('a.*');

        // From the vel_report table
        $query->from($db->quoteName('#__jed_reviews', 'a'));

        // Filter by linked_item_id global.

        $idReview = $this->linked_item_id;
        if (is_numeric($idReview)) {
            $query->where('a.id = ' . (int) $idReview);
        } elseif (is_string($idReview)) {
            $query->where('a.id = ' . $db->quote($idReview));
        } else {
            $query->where('a.id = -5');
        }
        $query->select("uc.name AS review_creator");
        $query->join("LEFT", "#__users AS uc ON uc.id=a.created_by");
        $query->select("supply_options.title AS supply_type");
        $query->join("LEFT", "#__jed_extension_supply_options AS supply_options ON supply_options.id=a.supply_option_id");

        // Load the items
        $db->setQuery($query);
        $db->execute();
        if ($db->getNumRows()) {
            return $db->loadObjectList();
        }

        return false;
    }

    /**
     *
     * Method to get Ticket Help Data
     *
     * @return  array|bool  An array on success, false on failure
     *
     * @since 4.0.0
     */
    public function getTicketHelp(): array
    {
        // Setup Output
        $output = [];

        // Get Ticket Review User Id
        $container = Factory::getContainer();

        $userFactory = $container->get('user.factory');

        $user                     = $userFactory->loadUserById($this->ticket_creator);
        $output['ticket_creator'] = $user;
        $ticket_creator           = 1069;
        $user                     = $userFactory->loadUserById($ticket_creator); //CMSOvject user
        $output['sample_creator'] = $user;

        // Create a new query object.
        $db = $this->getDatabase();


        //Extensions
        $query = $db->getQuery(true);

        // Select all fields

        $query->select('a.*, `b`.`title`, `b`.`alias`');

        $query->from($db->quoteName('#__jed_extensions', 'a'));
        $query->join("inner", "`#__jed_extension_varied_data` as b", "`b`.`extension_id` = `a`.`id`");

        $query->where('a.created_by = ' . $ticket_creator);

        // Load the items
        $db->setQuery($query);
        $db->execute();
        $output['extensions'] = $db->loadObjectList();
        $ectr                 = 0;
        $epub                 = 0;
        $eapp                 = 0;
        foreach ($output['extensions'] as $e) {
            $ectr++;
            if ($e->published == 1) {
                $epub++;
            }
            if ($e->approved == 1) {
                $eapp++;
            }
        }
        $output['extensions-count']     = $ectr;
        $output['extensions-approved']  = $eapp;
        $output['extensions-published'] = $epub;

        //Reviews
        $query = $db->getQuery(true);

        // Select all fields

        $query->select('a.*');

        // From the vel_report table
        $query->from($db->quoteName('#__jed_reviews', 'a'));

        $query->where('a.created_by = ' . $ticket_creator);
        // Load the items
        $db->setQuery($query);
        $db->execute();
        $output['reviews'] = $db->loadObjectList();


        // old tickets This section commented out for public github
        //SELECT * FROM bl_j3_mar22.wqyh6_rsticketspro_tickets WHERE customer_id=71796;
        /*  $query = $db->getQuery(true);
          $query->select('*')
              ->from('bl_j3_mar22.wqyh6_rsticketspro_tickets')
              ->where('customer_id=' . $ticket_creator);
          $db->setQuery($query);*/

        //$output['oldtickets']
        $oldtickets = []; // $db->loadObjectList();
        foreach ($oldtickets as $oneItem) {
            //SELECT * FROM bl_j3_mar22wqyh6_rsticketspro_ticket_messages WHERE user_id=71796;
            $query = $db->getQuery(true);
            $query
                ->select('*')
                ->from('bl_j3_mar22.wqyh6_rsticketspro_ticket_messages')
                ->where('ticket_id=' . $db->quote($db->escape($oneItem->id)));

            $db->setQuery($query);
            $results = $db->loadObject();

            if ($results) {
                $oneItem->messages = $results;
            } else {
                $oneItem->messages = [];
            }

            //SELECT * FROM bl_j3_mar22.wqyh6_rsticketspro_ticket_notes WHERE ticket_id=2828;
            $query = $db->getQuery(true);
            $query
                ->select('*')
                ->from('bl_j3_mar22.wqyh6_rsticketspro_ticket_notes')
                ->where('ticket_id=' . $db->quote($db->escape($oneItem->id)));

            $db->setQuery($query);
            $results = $db->loadObject();

            if ($results) {
                $oneItem->notes = $results;
            } else {
                $oneItem->notes = [];
            }

            //SELECT * FROM wqyh6_rsticketspro_ticket_history WHERE ticket_id=2828;
            $query = $db->getQuery(true);
            $query
                ->select('*')
                ->from('bl_j3_mar22.wqyh6_rsticketspro_ticket_history')
                ->where('ticket_id=' . $db->quote($db->escape($oneItem->id)));

            $db->setQuery($query);
            $results = $db->loadObject();

            if ($results) {
                $oneItem->history = $results;
            } else {
                $oneItem->history = [];
            }
        }


        $output['oldtickets'] = $oldtickets;


        return $output;
    }

    /**
     *
     * Method to get Ticket Internal Notes Data
     *
     * @return  array  Object on success
     *
     * @since 4.0.0
     */
    public function getTicketInternalNotes(): array
    {
        /* Steps
            1 - Look to see if there are notes, if not set flag
            2 - If there are notes store them in array in reverse date order
            3 - Create Empty New notes array / flag for holding */
        $db = $this->getDatabase();

        $query = $db->getQuery(true);
        // Select some fields
        $query->select('a.*');

        // From the jed_ticket_internal_notes table
        $query->from($db->quoteName('#__jed_ticket_internal_notes', 'a'));

        // Filter by Ticket Id

        $ticketId = $this->item->id;
        if (is_numeric($ticketId)) {
            $query->where('a.ticket_id = ' . (int) $ticketId);
        } elseif (is_string($ticketId)) {
            $query->where('a.ticket_id = ' . $db->quote($ticketId));
        } else {
            $query->where('a.ticket_id = -5');
        }
        // Load the items
        $db->setQuery($query);
        $db->execute();
        if ($db->getNumRows()) {
            return $db->loadObjectList();
        }

        return [];
    }

    /**
     * Method to get Ticket Messages
     *
     * @retun array|bool    An array on success, false on failure
     *
     * @since 4.0.0
     */
    public function getTicketMessages(): array
    {
        // Create a new query object.
        $db = $this->getDatabase();

        $query = $db->getQuery(true);

        // Select some fields
        $query->select('a.*');

        // From the jed_ticket_messages table
        $query->from($db->quoteName('#__jed_ticket_messages', 'a'));

        // Filter by Ticket Id

        $ticketId = $this->item->id;
        if (is_numeric($ticketId)) {
            $query->where('a.ticket_id = ' . (int) $ticketId);
        } elseif (is_string($ticketId)) {
            $query->where('a.ticket_id = ' . $db->quote($ticketId));
        } else {
            $query->where('a.ticket_id = -5');
        }


        // Load the items
        $db->setQuery($query);
        $db->execute();
        if ($db->getNumRows()) {
            return $db->loadObjectList();
        }

        return [];
    }

    /**
     *
     * Method to get VEL Abandonware Data
     *
     * @return  array|bool  An array on success, false on failure
     *
     * @since 4.0.0
     */
    public function getVelAbandonedReportData(): array
    {
        // Create a new query object.
        $db = $this->getDatabase();

        $query = $db->getQuery(true);

        // Select some fields
        $query->select('a.*');

        // From the vel_abandoned_report table
        $query->from($db->quoteName('#__jed_vel_abandoned_report', 'a'));

        // Filter by idVelDevUpdate global.

        $idVelAbandonedReport = $this->linked_item_id;
        if (is_numeric($idVelAbandonedReport)) {
            $query->where('a.id = ' . (int) $idVelAbandonedReport);
        } elseif (is_string($idVelAbandonedReport)) {
            $query->where('a.id = ' . $db->quote($idVelAbandonedReport));
        } else {
            $query->where('a.id = -5');
        }


        // Load the items
        $db->setQuery($query);
        $db->execute();
        if ($db->getNumRows()) {
            return $db->loadObjectList();
        }

        return false;
    }

    /**
     *
     * Method to get VEL Developer Update Data
     *
     * @return  array|bool  An array on success, false on failure
     *
     * @since 4.0.0
     */
    public function getVelDeveloperUpdateData(): array
    {
        // Create a new query object.
        $db = $this->getDatabase();

        $query = $db->getQuery(true);

        // Select some fields
        $query->select('a.*');

        // From the vel_report table
        $query->from($db->quoteName('#__jed_vel_developer_update', 'a'));

        // Filter by idVelDevUpdate global.

        $idVelDevUpdate = $this->linked_item_id;
        if (is_numeric($idVelDevUpdate)) {
            $query->where('a.id = ' . (int) $idVelDevUpdate);
        } elseif (is_string($idVelDevUpdate)) {
            $query->where('a.id = ' . $db->quote($idVelDevUpdate));
        } else {
            $query->where('a.id = -5');
        }


        // Load the items
        $db->setQuery($query);
        $db->execute();
        if ($db->getNumRows()) {
            return $db->loadObjectList();
        }

        return false;
    }

    /**
     *
     * Method to get VEL Report Item Data
     *
     * @return  array|bool  An array on success, false on failure
     *
     * @since 4.0.0
     */
    public function getVelReportData(): array
    {
        // Create a new query object.
        $db = $this->getDatabase();

        $query = $db->getQuery(true);

        // Select some fields
        $query->select('a.*');

        // From the vel_report table
        $query->from($db->quoteName('#__jed_vel_report', 'a'));

        // Filter by idVelReport global.

        $idVelReport = $this->linked_item_id;
        if (is_numeric($idVelReport)) {
            $query->where('a.id = ' . (int) $idVelReport);
        } elseif (is_string($idVelReport)) {
            $query->where('a.id = ' . $db->quote($idVelReport));
        } else {
            $query->where('a.id = -5');
        }


        // Load the items
        $db->setQuery($query);
        $db->execute();
        if ($db->getNumRows()) {
            return $db->loadObjectList();
        }

        return false;
    }
}
