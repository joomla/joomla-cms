<?php

/**
 * @package               JED
 *
 * @subpackage            Tickets
 *
 * @copyright         (C) 2022 Open Source Matters, Inc.  <https://www.joomla.org>
 * @license               GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Jed\Component\Jed\Administrator\Controller;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects


use Exception;
use Jed\Component\Jed\Administrator\Helper\JedemailHelper;
use Jed\Component\Jed\Administrator\Helper\JedHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;

/**
 * JED Ticket Controller class
 *
 * @since  4.0.0
 */
class JedticketController extends FormController
{
    /**
     * A string showing the plural of the current object
     *
     * @var    string
     *
     * @since  4.0.0
     */
    protected $view_list = 'jedtickets';


    /**
     * @since 4.0.0
     * @throws Exception
     */
    public function assignDeveloperUpdatetoVEL()
    {
        $db = Factory::getContainer()->get('DatabaseDriver');

        $this->task = $_POST['task'];

        if ($this->task == "jedticket.assignDeveloperUpdatetoVEL") {
            $ticketId    = $_POST["jform"]['id'];
            $reportId    = $_POST["jform"]['linked_item_id'];
            $vel_item_id = $_POST["jform"]['vel_item_id'];
            $queryUpdate = $db->getQuery(true)
                ->update('#__jed_vel_developer_update')
                ->set($db->qn('vel_item_id') . ' = ' . $vel_item_id)
                ->where($db->qn('id') . ' = ' . $reportId);


            $db->setQuery($queryUpdate);
            $db->execute();

            $app = Factory::getApplication();
            $app->enqueueMessage('Developer Update linked to Existing VEL Item', 'success');
            $this->setRedirect(
                Route::_('index.php?option=com_jed&view=jedticket&layout=edit&id=' . (int)$ticketId, false)
            );
        }
    }

    /**
     * Takes an Abandoned Component Report and creates a VEL entry for it.
     *
     * @since 4.0.0
     */
    public function copyAbandonedReporttoVEL()
    {
        $db = Factory::getContainer()->get('DatabaseDriver');

        $this->task = $_POST['task'];

        if ($this->task == "jedticket.copyAbandonedReporttoVEL") {
            $reportId = $_POST["jform"]['linked_item_id'];

            $querySelect = $db->getQuery(true)
                ->select(
                    "0,`extension_name` as vulnerable_item_name, `extension_version` as vulnerable_item_version, CONCAT(`extension_name`,', ',`extension_version`,', ','Abandoned') AS title,'',3 AS 'status'," . $reportId . ",'','' AS 'risk_level',   `extension_version`, `extension_version`,'' AS 'patch_version','','',5 AS 'exploit_type','' AS 'exploit_other_description','' AS 'xml_manifest','' AS 'manifest_location', '' AS 'install_data', `reporter_fullname` AS 'discovered_by',''"
                )
                ->from('#__jed_vel_abandoned_report')
                ->where('id = ' . $reportId);

            $queryInsert = $db->getQuery(true)
                ->insert('#__jed_vel_vulnerable_item')
                ->columns(
                    $db->qn([
                        'id',
                        'vulnerable_item_name',
                        'vulnerable_item_version',
                        'title',
                        'internal_description',
                        'status',
                        'report_id',
                        'jed',
                        'risk_level',
                        'start_version',
                        'vulnerable_version',
                        'patch_version',
                        'recommendation',
                        'update_notice',
                        'exploit_type',
                        'exploit_other_description',
                        'xml_manifest',
                        'manifest_location',
                        'install_data',
                        'discovered_by',
                        'public_description',
                    ])
                )
                ->values($querySelect);
            //echo $queryInsert->__toString();exit();
            $db->setQuery($queryInsert);
            $db->execute();

            $newVel = $db->insertid();

            $queryUpdate = $db->getQuery(true)
                ->update('#__jed_vel_abandoned_report')
                ->set([
                    $db->qn('passed_to_vel') . ' = 1',
                    ($db->qn('vel_item_id') . ' = ' . $newVel),
                ])
                ->where($db->qn('id') . ' = ' . $reportId);

            $db->setQuery($queryUpdate);
            $db->execute();

            /* CHECK IN THIS MODEL */
            $model = $this->getModel();
            $cid   = $this->input->get('id', [], 'array');
            $model->checkIn($cid[0]);

            $this->setRedirect(
                Route::_(
                    'index.php?option=com_jed&view=velvulnerableitem&task=velvulnerableitem.edit&id=' . (int)$newVel,
                    false
                )
            );
        }
    }

    /**
     * Function to respond to copyReporttoVEL button on viewing a reported vulnerability
     * It needs to take the data and create a new vulnerable item and then open that
     * report for editing
     *
     * @since    4.0.0
     */
    public function copyReporttoVEL()
    {
        $db         = Factory::getContainer()->get('DatabaseDriver');
        $this->task = $_POST['task'];
        if ($this->task == "jedticket.copyReporttoVEL") {
            /*SELECT   `id`, `vulnerable_item_name`, `vulnerable_item_version`,
            `title`, `internal_description`, `status`, `report_id`, `jed`, `risk_level`,
             `start_version`, `vulnerable_version`, `patch_version`, `recommendation`, `update_notice`,
            `exploit_type`, `exploit_other_description`, `xml_manifest`, `manifest_location`,
            `install_data`, `discovered_by`, `discoverer_public`, `fixed_by`, `coordinated_by`,
            `jira`, `cve_id`, `cwe_id`, `cvssthirty_base`, `cvssthirty_base_score`, `cvssthirty_temp`,
            `cvssthirty_temp_score`, `cvssthirty_env`, `cvssthirty_env_score`, `public_description`,
            `alias`, `created_by`, `modified_by`, `created`, `modified`, `checked_out`, `checked_out_time`,
            `state` FROM  #__jed_vel_vulnerable_item` */
            $reportId = $_POST["jform"]['linked_item_id'];

            $exploit_string = Text::_(
                'COM_JED_VEL_GENERAL_FIELD_EXPLOIT_TYPE_OPTION_' . $_POST["jform"]['exploit_type']
            );

            //  var_dump($_POST);
            $querySelect = $db->getQuery(true)
                ->select(
                    "0,vulnerable_item_name, vulnerable_item_version, CONCAT(`vulnerable_item_name`,', ',`vulnerable_item_version`,', ','" . $exploit_string . "') as title,'',0 AS 'status',`id`,`jed_url`,'' AS 'risk_level',`vulnerable_item_version`, `vulnerable_item_version`,'' AS 'patch_version','','',`exploit_type`,`exploit_other_description`,
'' AS 'xml_manifest','' AS 'manifest_location', '' AS 'install_data', `reporter_fullname` AS 'discovered_by','',now()"
                )
                ->from('#__jed_vel_report')
                ->where('id = ' . $reportId);

            $queryInsert = $db->getQuery(true)
                ->insert('#__jed_vel_vulnerable_item')
                ->columns(
                    $db->qn([
                        'id',
                        'vulnerable_item_name',
                        'vulnerable_item_version',
                        'title',
                        'internal_description',
                        'status',
                        'report_id',
                        'jed',
                        'risk_level',
                        'start_version',
                        'vulnerable_version',
                        'patch_version',
                        'recommendation',
                        'update_notice',
                        'exploit_type',
                        'exploit_other_description',
                        'xml_manifest',
                        'manifest_location',
                        'install_data',
                        'discovered_by',
                        'public_description',
                        'created',
                    ])
                )
                ->values($querySelect);

            $db->setQuery($queryInsert);
            $db->execute();

            $newVel = $db->insertid();

            $queryUpdate = $db->getQuery(true)
                ->update('#__jed_vel_report')
                ->set([
                    $db->qn('passed_to_vel') . ' = 1',
                    ($db->qn('vel_item_id') . ' = ' . $newVel),
                ])
                ->where($db->qn('id') . ' = ' . $reportId);

            $db->setQuery($queryUpdate);
            $db->execute();


            /* CHECK IN THIS MODEL */
            $model = $this->getModel();
            $cid   = $this->input->get('id', [], 'array');
            $model->checkIn($cid[0]);
            $this->setRedirect(
                Route::_(
                    'index.php?option=com_jed&view=velvulnerableitem&task=velvulnerableitem.edit&id=' . (int)$newVel,
                    false
                )
            );
        }
    }

    /**
     * getTemplate
     *
     * function for ajax getting specific template
     *
     * @since 4.0.0
     * @throws Exception
     */

    public function getTemplate()
    {
        //  Session::checkToken('post') or die;
        $app     = Factory::getApplication();
        $camp_id = $app->input->get('itemId', 0, 'int');
        $db      = Factory::getContainer()->get('DatabaseDriver');


        $querySelect = $db->getQuery(true)
            ->select("subject,template,ticket_status")
            ->from('#__jed_message_templates')
            ->where('id = ' . $camp_id);
        $db->setQuery($querySelect);
        $results = $db->loadObject();

        echo $results->subject . '|' . $results->template . '|' . $results->ticket_status;
    }

    /**
     * Function to respond to gotoVEL button on viewing a ticket.
     * It checks in the current ticket and performs a redirect
     *
     * @since    4.0.0
     */
    public function gotoVEL()
    {
        $this->task = $_POST['task'];

        if ($this->task == "jedticket.gotoVEL") {
            $jform     = $_POST['jform'];
            $ticket_id = $jform['id'];

            $vel_id = $jform['vel_item_id'];
            /* CHECK IN THIS MODEL */
            $model = $this->getModel();

            $model->checkIn($ticket_id);
            $this->setRedirect(
                Route::_(
                    'index.php?option=com_jed&view=velvulnerableitem&task=velvulnerableitem.edit&id=' . (int)$vel_id,
                    false
                )
            );
        }
    }

    /**
     * Function to link a submitted developer update to Vulnerable item.
     * It checks in the current ticket and performs a redirect
     *
     * @since    4.0.0
     */
    public function linkDeveloperUpdatetoVEL()
    {
        $db         = Factory::getContainer()->get('DatabaseDriver');
        $this->task = $_POST['task'];
        if ($this->task == "jedticket.linkDeveloperUpdatetoVEL") {
            $jform = $_POST['jform'];

            $vel_id          = $jform['vel_item_id'];
            $vel_devupdateid = $jform['veldeveloperupdate_id'];

            $queryUpdate = $db->getQuery(true)
                ->update('#__jed_vel_developer_update')
                ->set($db->qn('vel_item_id') . ' = ' . (int)$vel_id)
                ->where($db->qn('id') . ' = ' . (int)$vel_devupdateid);

            $db->setQuery($queryUpdate);
            $db->execute();

            /* CHECK IN THIS MODEL */
            $model = $this->getModel();
            $cid   = $this->input->get('id', [], 'array');
            $model->checkIn($cid[0]);
            $this->setRedirect(
                Route::_(
                    'index.php?option=com_jed&view=velvulnerableitem&task=velvulnerableitem.edit&id=' . (int)$vel_id,
                    false
                )
            );
        }
    }

    /**
     * Ticket Send and Store Message
     * @since 4.0.0
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function sendMessage()
    {
        $this->task = $_POST['task'];


        if ($this->task == "jedticket.sendmessage") {
            /* Functionality
            1 - Verify
            2 - Send email with mesage
            3 - Store message to database
            4 - Redirect back to ticket so message history reloads
            */
            // var_dump($_POST['jform']);exit();
            $subject = $_POST['jform']['message_subject'];
            $message = $_POST['jform']['message_text'];

            $id          = $_POST['jform']['id'];
            $ticket_user = JedHelper::getUserById($_POST['jform']['created_by_num']);
            JedemailHelper::sendEmail($subject, $message, $ticket_user, 'mark@burninglight.co.uk');
            $this->storeMessage($id, $subject, $message);
            $this->setRedirect(Route::_('index.php?option=com_jed&view=jedticket&layout=edit&id=' . (int)$id, false));
        }
    }

    /**
     * Stores a tickets internal note back to table
     * @since 4.0.0
     * @throws Exception
     */
    public function storeInternalNote()
    {
        $summary   = $_POST['jform']['summary'];
        $note      = $_POST['jform']['internal_notes'];
        $ticket_id = $_POST['jform']['id'];
        if ($summary == '') {
            /* Do Nothing */
        } else {
            $ticket_internal_notes_model = $this->getModel('Ticketinternalnote', 'Administrator');

            $internal_note['id']        = 0;
            $internal_note['ticket_id'] = $ticket_id;
            $internal_note['summary']   = $summary;
            $internal_note['note']      = $note;
            /*$internal_note['ordering']         = 0;
            $internal_note['state']            = 0;
            $internal_note['checked_out']      = 0;
            $internal_note['checked_out_time'] = '0000-00-00 00:00:00';
            $internal_note['created_by']       = $user->id;
            $internal_note['modified_by']      = $user->id;
            $internal_note['created_on']       = 'now()';*/

            $ticket_internal_notes_model->save($internal_note);
        }
        $this->setRedirect(
            Route::_('index.php?option=com_jed&view=jedticket&layout=edit&id=' . (int)$ticket_id, false)
        );
    }

    /**
     * Store Ticket Message back to database
     *
     * @param   int  $ticket_id
     * @param        $subject
     * @param        $message
     *
     *
     * @since 4.0.0
     */
    public function storeMessage(int $ticket_id, $subject, $message)
    {
        $user                                = JedHelper::getUser();
        $ticket_message_model                = $this->getModel('Ticketmessage', 'Administrator');

        $ticket_message['id']                = 0;
        $ticket_message['ticket_id']         = $ticket_id;
        $ticket_message['subject']           = $subject;
        $ticket_message['message']           = $message;
        $ticket_message['message_direction'] = 0; /* 1 for coming in, 0 for going out */
        $ticket_message['created_by']        = $user->id;
        $ticket_message['modified_by']       = $user->id;
        $ticket_message['created_on']        = 'now()';
        $ticket_message['modified_on']       = 'now()';
        $ticket_message['state']             = 0;
        $ticket_message['ordering']          = 0;
        $ticket_message['checked_out']       = 0;
        $ticket_message['checked_out_time']  = '0000-00-00 00:00:00';


        $ticket_message_model->save($ticket_message);
    }
}
