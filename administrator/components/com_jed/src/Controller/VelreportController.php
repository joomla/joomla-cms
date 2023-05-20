<?php

/**
 * @package       JED
 *
 * @subpackage    VEL
 *
 * @copyright     (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Jed\Component\Jed\Administrator\Controller;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;

use function defined;

/**
 * VEL Report Controller Class.
 *
 * @since  4.0.0
 */
class VelreportController extends FormController
{
    /**
     * A string showing the plural of the current object
     *
     * @var    string
     *
     * @since  4.0.0
     */
    protected $view_list = 'Velreports';


    /**
     * Function to respond to copyReporttoVEL button on viewing a reported vulnerability
     * It needs to take the data and create a new vulnerable item and then open that
     * report for editing
     *
     * @since    4.0.0
     */
    public function copyReporttoVEL()
    {
        $db = Factory::getContainer()->get('DatabaseDriver');

        $this->task = $_POST['task'];

        if ($this->task == "velreport.copyReporttoVEL") {
            $reportId = $_POST["jform"]['linked_item_id'];

            $exploit_string = Text::_('COM_JED_VEL_GENERAL_FIELD_EXPLOIT_TYPE_OPTION_' . $_POST["jform"]['exploit_type']);

            //  var_dump($_POST);
            $querySelect = $db->getQuery(true)
                ->select("0,CONCAT(`vulnerable_item_name`,', ',`vulnerable_item_version`,', ','" . $exploit_string . "') as title,'',0 AS 'status',`id`,`jed_url`,'' AS 'risk_level',`vulnerable_item_version`, `vulnerable_item_version`,'' AS 'patch_version','','',`exploit_type`,`exploit_other_description`,
'' AS 'xml_manifest','' AS 'manifest_location', '' AS 'install_data', `reporter_fullname` AS 'discovered_by',''")
                ->from('#__jed_vel_report')
                ->where('id = ' . $reportId);

            $queryInsert = $db->getQuery(true)
                ->insert('#__jed_vel_vulnerable_item')
                ->columns($db->qn(['id', 'title', 'internal_description', 'status', 'report_id', 'jed', 'risk_level', 'start_version', 'vulnerable_version', 'patch_version', 'recommendation',
                    'update_notice', 'exploit_type', 'exploit_other_description', 'xml_manifest', 'manifest_location', 'install_data', 'discovered_by', 'public_description']))
                ->values($querySelect);

            $db->setQuery($queryInsert);
            $db->execute();

            $newVel = $db->insertid();

            $queryUpdate = $db->getQuery(true)
                ->update('#__jed_vel_report')
                ->set([$db->qn('passed_to_vel') . ' = 1',
                    ($db->qn('vel_item_id') . ' = ' . $newVel)])
                ->where($db->qn('id') . ' = ' . $reportId);

            $db->setQuery($queryUpdate);
            $db->execute();
            $insertColumns                  = ['id', 'vel_item_id', 'communication_type', 'communication_id', 'developer_report_id', 'vel_report_id', 'created'];
            $insertVals                     = [0, $newVel, 1, -1, -1, $reportId, $db->quote(Factory::getDate()->toSql())];
            $queryInsertCommunicationRecord = $db->getQuery(true)
                ->insert('#__jed_communications')
                ->columns($db->qn($insertColumns))
                ->values(implode(',', $insertVals));
            $db->setQuery($queryInsertCommunicationRecord);

            $db->execute();


            $this->setRedirect(Route::_('index.php?option=com_jed&view=velvulnerableitem&task=velvulnerableitem.edit&id=' . (int) $newVel, false));
        }
    }
}
