<?php

/**
 * @package           JED
 *
 * @subpackage        Tickets
 *
 * @copyright     (C) 2022 Open Source Matters, Inc.  <https://www.joomla.org>
 * @license           GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Jed\Component\Jed\Administrator\Helper\JedHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

//var_dump($this);
HTMLHelper::_('bootstrap.framework');
HTMLHelper::_('jquery.framework');

$wa = $this->document->getWebAssetManager();

$wa->useStyle('com_jed.jedTickets')
    ->useStyle('com_jed.jquery_dataTables');
$wa->useScript('keepalive')
    ->useScript('form.validate')
    ->useScript('com_jed.ticketGetmessagetemplate')
    ->useScript('com_jed.ticketVELDeveloperUpdateActionButton')
    ->useScript('com_jed.jquery_dataTables');
if ($this->linked_item_type === 3) { //Review
    $wa->useScript('com_jed.ticketPublishUnPublishReview');
}
//->useScript('com_jed.bootstrap_dataTables')
//->useScript('com_jed.responsive_dataTables')
//->useScript('com_jed.responsive_bootstrap')
HTMLHelper::_('bootstrap.tooltip');

$headerlabeloptions = array('hiddenLabel' => true);
$fieldhiddenoptions = array('hidden' => true);

$container   = Factory::getContainer();
$userFactory = $container->get('user.factory');
?>


<form
        action="<?php echo Route::_('index.php?option=com_jed&layout=edit&id=' . (int) $this->item->id); ?>"
        method="post" enctype="multipart/form-data" name="adminForm" id="jedticket-form"
        class="form-validate form-horizontal">


    <div class="com_jed_ticket">
        <div class="row-fluid">
            <!-- header boxes -->
            <?php echo LayoutHelper::render('ticket.header', $this->form); ?>

        </div>

    </div> <!-- end div class  com_jed_ticket -->


    <br/>

    <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'ticket')); ?>

    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'ticket', Text::_('COM_JED_TAB_TICKET', true)); ?>
    <!-- Ticket Summary Tab -->
    <div class="row">
        <div class="col-8">


            <div class="widget">
                <h1>Message History</h1>
                <div class="container">
                    <div class="row">
                        <?php

                        $slidesOptions = array("active" => 'ticket_messages_group' . '_slide' . count($this->ticket_messages) // It is the ID of the active tab.
                        );
                        echo HTMLHelper::_('bootstrap.startAccordion', 'ticket_messages_group', $slidesOptions);

                        $slideid = 0;
                        foreach ($this->ticket_messages as $ticketMessage) {
                            if ($ticketMessage->message_direction == 0) {
                                $inout = "jed-ticket-message-out";
                            } else {
                                $inout = "jed-ticket-message-in";
                            }

                            echo HTMLHelper::_('bootstrap.addSlide', 'ticket_messages_group', '<span class="' . $inout . '">' . $ticketMessage->subject . ' - ' . JedHelper::prettyDate($ticketMessage->created_on) . '</span>', 'ticket_messages_group' . '_slide' . ($slideid++));
                            echo  $ticketMessage->message ;
                            echo JHtml::_('bootstrap.endSlide');
                        }
                        echo HTMLHelper::_('bootstrap.endAccordion');

                        ?>


                    </div>

                </div>
            </div>

            <div class="widget">
                <h1>Message Templates</h1>
                <div class="container">
                    <div class="row">
                        <?php echo $this->form->renderField('messagetemplates', null, null, $headerlabeloptions); ?>

                    </div>
                </div>
            </div>

            <div class="widget">
                <h1>Compose Message</h1>
                <div class="container">
                    <div class="row">
                        <?php echo $this->form->renderField('message_subject', null, null, $headerlabeloptions); ?>
                        <?php echo $this->form->renderField('message_text', null, null, $headerlabeloptions); ?>

                        <button type="button" class="btn btn-primary"
                                onclick="Joomla.submitbutton('jedticket.sendmessage')">


                            <?php echo Text::_('Send Message'); ?>

                        </button>


                    </div>
                </div>
            </div>


        </div>
        <div class="col-4">
            <div class="widget">
                <h1>Created By</h1>
                <div class="container">
                    <div class="row">
                        <div class="col"><?php echo $this->form->renderField('created_by', null, null, $headerlabeloptions); ?></div>
                        <div class="col"><?php
                            echo 'on ';

                            echo JedHelper::prettyDate($this->item->created_on);


                        ?></div>
                    </div>

                </div>


            </div>
            <div class="widget">
                <h1>Related Object</h1>
                <div class="container">
                    <p><?php echo $this->related_object_string; ?></p>
                </div>
            </div>
            <div class="widget">
                <h1>Internal Notes</h1>
                <div class="container">
                    <?php
                    $slidesOptions = array();//"active" => "slide0" // It is the ID of the active tab.

                    echo HTMLHelper::_('bootstrap.startAccordion', 'internal_notes_group', $slidesOptions);

                    $slideid = 0;
                    foreach ($this->internal_notes as $internalNote) {
                        $user = JedHelper::getUserById($internalNote->created_by);
                        echo HTMLHelper::_('bootstrap.addSlide', 'internal_notes_group', '' . $internalNote->summary . ' - ' . JedHelper::prettyDate($internalNote->created_on) . ' by ' . $user->name, 'internal_notes_group' . '_slide' . ($slideid++));
                        echo "<p>" . $internalNote->note . "</p>";
                        echo JHtml::_('bootstrap.endSlide');
                    }
                    echo HTMLHelper::_('bootstrap.endAccordion');

                    ?>
                    <div class="widget">
                        <h1>Add Note &nbsp;&nbsp;<button type="button" class=""
                                                         onclick="Joomla.submitbutton('jedticket.storeInternalNote')">
                                <span class="icon-save"></span>
                            </button>
                        </h1>
                        <?php echo $this->form->renderField('summary', null, null, $headerlabeloptions); ?>
                        <?php echo $this->form->renderField('internal_notes', null, null, $headerlabeloptions); ?>

                    </div>
                </div>
            </div>

        </div>
    </div>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>

    <?php
    $add_debug_tab     = false;
    $add_extension_tab = false;

    if ($this->linked_item_type === 1) { /* Unknown Type */
        echo HTMLHelper::_('uitab.addTab', 'myTab', 'LinkedUnknown', 'Unknown');

        echo LayoutHelper::render('ticket.linked_unknown', $this->linked_form);

        echo HTMLHelper::_('uitab.endTab');
    }
    if ($this->linked_item_type === 2) { /* Extension */
        echo HTMLHelper::_('uitab.addTab', 'myTab', 'LinkedExtension', 'Linked Extension');

        echo LayoutHelper::render('ticket.linked_extension', $this->linked_form);

        echo HTMLHelper::_('uitab.endTab');
    }
    if ($this->linked_item_type === 3) { /* Review */
        echo HTMLHelper::_('uitab.addTab', 'myTab', 'LinkedReview', 'Linked Review');

        $passdata = array("linked_form" => $this->linked_form,
                          "linked_data" => $this->linked_item_data,
                          "extension"   => $this->linked_extension_data);
        echo LayoutHelper::render('ticket.linked_review', $passdata);

        echo HTMLHelper::_('uitab.endTab');
        $add_debug_tab     = true;
        $add_extension_tab = true;
    }
    if ($this->linked_item_type === 4) { /* VEL Report */
        echo HTMLHelper::_('uitab.addTab', 'myTab', 'LinkedVELReport', 'Linked VEL Report');

        echo LayoutHelper::render('ticket.linked_velreport', $this->linked_form);

        echo HTMLHelper::_('uitab.endTab');
    }
    if ($this->linked_item_type === 5) { /* VEL Developer Update */
        echo HTMLHelper::_('uitab.addTab', 'myTab', 'LinkedDeveloperUpdate', 'Linked Developer Update');

        echo LayoutHelper::render('ticket.linked_veldeveloperupdate', $this->linked_form);
        echo HTMLHelper::_('uitab.endTab');
    }
    if ($this->linked_item_type === 6) { /* VEL Abandonware */
        echo HTMLHelper::_('uitab.addTab', 'myTab', 'LinkedAbandonedReport', 'Linked Abandonware Report');

        echo LayoutHelper::render('ticket.linked_velabandonware', $this->linked_form);

        echo HTMLHelper::_('uitab.endTab');
    }
    if ($add_extension_tab == true) {
        echo HTMLHelper::_('uitab.addTab', 'myTab', 'LinkedExtension', 'Linked Extension');

        echo LayoutHelper::render('ticket.linked_extension', $this->linked_extension_data);

        echo HTMLHelper::_('uitab.endTab');
    }
    if ($add_debug_tab == true) {
        echo HTMLHelper::_('uitab.addTab', 'myTab', 'TicketHelp', 'Ticket Help');

        echo LayoutHelper::render('ticket.ticket_help', $this->ticket_help);

        echo HTMLHelper::_('uitab.endTab');
    }

    ?>



    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'Publishing', Text::_('COM_JED_TAB_PUBLISHING', true)); ?>
    <div class="row-fluid">
        <div class="span10 form-horizontal">
            <fieldset class="adminform">
                <legend><?php echo Text::_('COM_JED_GENERAL_PUBLISHING_LABEL'); ?></legend>
                <?php echo $this->form->renderField('state'); ?>

                <?php echo $this->form->renderField('created_on'); ?>
                <?php echo $this->form->renderField('modified_by'); ?>
                <?php echo $this->form->renderField('modified_on'); ?>
                <input type="hidden" name="jform[created_by_num]"
                       value="<?php echo $this->item->created_by; ?>"/>
                <?php echo $this->form->renderField('id'); ?>
                <?php echo $this->form->renderField('uploaded_files_preview'); //,null,null,$fieldhiddenoptions); ?>
                <?php echo $this->form->renderField('uploaded_files_location'); //,null,null,$fieldhiddenoptions); ?>
                <?php echo $this->form->renderField('linked_item_type', null, null, $fieldhiddenoptions); ?>
                <?php echo $this->form->renderField('linked_item_id', null, null, $fieldhiddenoptions); ?>
                <?php echo $this->form->renderField('parent_id', null, null, $fieldhiddenoptions); ?>
                <?php echo $this->form->renderField('id', null, null, $fieldhiddenoptions); ?>
            </fieldset>
        </div>
    </div>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>


    <?php echo HTMLHelper::_('uitab.endTabSet'); ?>

    <input type="hidden" name="task" value=""/>
    <?php echo HTMLHelper::_('form.token'); ?>

</form>
<?php


?>

