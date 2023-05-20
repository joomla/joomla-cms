<?php

/**
 * @package           JED
 *
 * @subpackage        Tickets
 *
 * @copyright     (C) 2022 Open Source Matters, Inc.  <https://www.joomla.org>
 * @license           GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Jed\Component\Jed\Administrator\View\Jedticket;

// No direct access
// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Exception;
use Jed\Component\Jed\Administrator\Helper\JedHelper;
use Jed\Component\Jed\Administrator\Model\ExtensionModel;
use Jed\Component\Jed\Administrator\Model\ExtensionvarieddatumModel;
use Jed\Component\Jed\Administrator\Model\ReviewModel;
use Jed\Component\Jed\Administrator\Model\VelabandonedreportModel;
use Jed\Component\Jed\Administrator\Model\VeldeveloperupdateModel;
use Jed\Component\Jed\Administrator\Model\VelreportModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * View class for a display of JED Ticket.
 *
 * @since  4.0.0
 */
class HtmlView extends BaseHtmlView
{
    protected CMSObject $state;
    protected mixed $item;
    protected mixed $form;

    /**
     * What type of object is linked to the ticket
     *
     * @var    int
     *
     * @since  4.0.0
     */
    protected int $linked_item_type;

    /**
     * The model of the linked item
     *
     * @var    mixed
     *
     * @since  4.0.0
     */
    protected mixed $linked_item_Model;

    /**
     * A list of messages sent / received for this ticket
     *
     * @var    mixed
     *
     * @since  4.0.0
     */
    protected mixed $ticket_messages;

    /**
     * A list of internal notes for this ticket
     *
     * @var    mixed
     *
     * @since  4.0.0
     */
    protected mixed $internal_notes;

    /**
     * A string containing html linking ticket to remote object
     *
     * @var    string
     *
     * @since  4.0.0
     */
    protected string $related_object_string;

    /**
     * The linked Form object
     *
     * @var    ?Form
     *
     * @since  4.0.0
     */
    protected mixed $linked_form;

    /**
     * The linked Form data
     *
     * @var    mixed
     *
     * @since  4.0.0
     */
    protected mixed $linked_item_data;
    /**
     * The linked extension form
     *
     * @var    Form
     *
     * @since  4.0.0
     */
    protected mixed $linked_extension_form;
    /**
     * The linked extension
     *
     * @var    mixed
     *
     * @since  4.0.0
     */
    protected mixed $linked_extension_data;

    /**
     * The linked extension varied data
     *
     * @var    mixed
     *
     * @since  4.0.0
     */
    protected mixed $linked_extension_varieddata;
    /**
     * The linked extension varied data  form
     *
     * @var    Form
     *
     * @since  4.0.0
     */
    protected mixed $linked_extension_varieddata_form;
    /**
     * Ticket Help
     *
     * @var    mixed
     *
     * @since  4.0.0
     */
    protected mixed $ticket_help;


    /**
     * Add the page title and toolbar.
     *
     * @return void
     *
     * @since 4.0.0
     * @throws Exception
     *
     */
    protected function addToolbar()
    {
        Factory::getApplication()->input->set('hidemainmenu', true);

        $user  = JedHelper::getUser();
        $isNew = ($this->item->id == 0);

        if (isset($this->item->checked_out)) {
            $checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
        } else {
            $checkedOut = false;
        }

        $canDo = JedHelper::getActions();

        ToolbarHelper::title(Text::_('COM_JED_TITLE_JEDTICKET'), "generic");

        // If not checked out, can save the item.
        if (!$checkedOut && ($canDo->get('core.edit') || ($canDo->get('core.create')))) {
            ToolbarHelper::apply('jedticket.apply');
            ToolbarHelper::save('jedticket.save');
        }

        if (!$checkedOut && ($canDo->get('core.create'))) {
            ToolbarHelper::custom(
                'jedticket.save2new',
                'save-new.png',
                'save-new_f2.png',
                'JTOOLBAR_SAVE_AND_NEW',
                false
            );
        }

        // If an existing item, can save to a copy.
        if (!$isNew && $canDo->get('core.create')) {
            ToolbarHelper::custom(
                'jedticket.save2copy',
                'save-copy.png',
                'save-copy_f2.png',
                'JTOOLBAR_SAVE_AS_COPY',
                false
            );
        }

        if (empty($this->item->id)) {
            ToolbarHelper::cancel('jedticket.cancel');
        } else {
            ToolbarHelper::cancel('jedticket.cancel', 'JTOOLBAR_CLOSE');
        }
    }

    /**
     * Display the view
     *
     * @param   string  $tpl  Template name
     *
     * @return void
     *
     * @since 4.0.0
     * @throws Exception
     *
     */
    public function display($tpl = null)
    {
        $this->state            = $this->get('State');
        $this->item             = $this->get('Item');
        $this->form             = $this->get('Form');
        $this->ticket_messages  = $this->get('TicketMessages');
        $this->internal_notes   = $this->get('TicketInternalNotes');
        $this->ticket_help      = $this->get('TicketHelp');
        $this->linked_item_type = $this->item->linked_item_type;

        if ($this->linked_item_type === 2) { // Extension
            $this->linked_item_Model     = new ExtensionModel();
            $this->related_object_string = "Sorry but extensions as related object are not currently coded.";
            //$this->linked_item_data = $this->get('ExtensionData');
            //$this->linked_form      = $this->linked_item_Model->getForm($this->linked_item_data, false);

            //$this->linked_form->bind($this->linked_item_data);
        }
        if ($this->linked_item_type === 3) { //Review
            $this->linked_item_Model     = new ReviewModel();
            $this->related_object_string = "Sorry but reviews as related object are not currently coded.";

            $this->linked_item_data = $this->get('ReviewData');
            $this->linked_form      = $this->linked_item_Model->getForm(
                $this->linked_item_data,
                false,
                'jf_linked_form'
            );

            $this->linked_form->bind($this->linked_item_data);

            //$this->linked_extension_data holds actual data plus extension_form

            $extension_model = new ExtensionModel();

            $this->linked_extension_data = $extension_model->getvariedItem(
                $this->linked_item_data[0]->extension_id,
                $this->linked_item_data[0]->supply_option_id
            );


            $this->linked_extension_form = $extension_model->getForm(
                $this->linked_extension_data,
                false,
                'jf_linked_extension_form'
            );
            $this->linked_extension_form->bind($this->linked_extension_data);
            $this->linked_extension_data->extension_form = $this->linked_extension_form;


            $extensionvarieddatum                   = new ExtensionvarieddatumModel();
            $this->linked_extension_varieddata      = $this->linked_extension_data->varied_data[$this->linked_item_data[0]->supply_type];
            $this->linked_extension_varieddata_form = $extensionvarieddatum->getForm(
                $this->linked_extension_varieddata,
                false,
                'jf_linked_extension_varieddata_form'
            );

            $this->linked_extension_varieddata_form->bind($this->linked_extension_varieddata);
            $this->linked_extension_data->varied_form = $this->linked_extension_varieddata_form;
        }
        if ($this->linked_item_type === 4) { // VEL Report
            $this->linked_item_Model = new VelreportModel();

            $this->linked_item_data = $this->get('VelReportData');

            $this->linked_form = $this->linked_item_Model->getForm($this->linked_item_data, false);
            $this->linked_form->bind($this->linked_item_data);
            if ($this->linked_item_data[0]->vel_item_id > 0) {
                $this->related_object_string = '<button type="button" class="btn btn-primary"  ' .
                    'onclick="Joomla.submitbutton(\'jedticket.gotoVEL\')">View VEL Item ' .
                    $this->linked_item_data[0]->vel_item_id . '</button>';
            } else {
                $this->related_object_string = "Awaiting creation of VEL Item";
            }
        }
        if ($this->linked_item_type === 5) { // VEL Developer Update
            $this->linked_item_Model = new VeldeveloperupdateModel();

            $this->linked_item_data = $this->get('VelDeveloperUpdateData');

            $this->linked_form = $this->linked_item_Model->getForm($this->linked_item_data, false);
            $this->linked_form->bind($this->linked_item_data);
            if ($this->linked_item_data[0]->vel_item_id > 0) {
                $this->related_object_string = '<button type="button" class="btn btn-primary" ' .
                    'onclick="Joomla.submitbutton(\'jedticket.gotoVEL\')">View VEL Item ' .
                    $this->linked_item_data[0]->vel_item_id . '</button>';
            } else {
                $this->related_object_string = "Awaiting Linking to VEL Item";
            }
        }
        if ($this->linked_item_type === 6) { // VEL Abandonware Report
            $this->linked_item_Model = new VelabandonedreportModel();
            $this->linked_item_data  = $this->get('VelAbandonedReportData');

            $this->linked_form = $this->linked_item_Model->getForm($this->linked_item_data, false);
            $this->linked_form->bind($this->linked_item_data);

            if ($this->linked_item_data[0]->vel_item_id > 0) {
                $this->related_object_string = '<button type="button" class="btn btn-primary"  onclick="Joomla.submitbutton(\'jedticket.gotoVEL\')">View VEL Item ' . $this->linked_item_data[0]->vel_item_id . '</button>';
            } else {
                $this->related_object_string = "Awaiting creation of VEL Item";
            }
        }


        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new Exception(implode("\n", $errors));
        }

        $this->addToolbar();

        parent::display($tpl);
    }

    /**
     * getArray
     *
     * @param   string  $st
     *
     * @return array
     *
     * @since 4.0.0
     */
    public function getArray(string $st): array
    {
        $l_version = str_replace('[', '', $st);

        $l_version = str_replace(']', '', $l_version);
        $l_version = str_replace('"', '', $l_version);

        return explode(',', $l_version);
    }
}
