<?php

/**
 * @package       JED
 *
 * @subpackage    Tickets
 *
 * @copyright     (C) 2022 Open Source Matters, Inc.  <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to $displayData file
// phpcs:disable PSR1.Files.SideEffects
// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects
// phpcs:enable PSR1.Files.SideEffects

use Jed\Component\Jed\Administrator\Helper\JedHelper;
use Joomla\CMS\HTML\HTMLHelper;

/* @var $displayData array */
$headerlabeloptions = ['hiddenLabel' => true];
$fieldhiddenoptions = ['hidden' => true];
$rawData            = $displayData->getData();
?>
<div class="row">
    <div class="col-8">
        <div class="widget">
            <h1>Ticket</h1>
            <div class="container">
                <?php echo $displayData->renderField('ticket_text', null, null, $headerlabeloptions); ?>
            </div>


        </div>
        <div class="widget">
            <h1>Message History</h1>
            <div class="container">
                <div class="row">
                    <?php
                    $slidesOptions = [/**"active" => "slide0" // It is the ID of the active tab.**/];
echo HTMLHelper::_('bootstrap.startAccordion', 'ticket_messages_group', $slidesOptions);

$slideid = 0;
foreach ($this->ticket_messages as $ticketMessage) {
    if ($ticketMessage->message_direction == 0) {
        $inout = "jed-ticket-message-out";
    } else {
        $inout = "jed-ticket-message-in";
    }

    echo HTMLHelper::_('bootstrap.addSlide', 'ticket_messages_group', '<span class="' . $inout . '">' . $ticketMessage->subject . ' - ' . JedHelper::prettyDate($ticketMessage->created_on), 'slide' . ($slideid++));
    echo "<p>" . $ticketMessage->message . "</p>";
    echo JHtml::_('bootstrap.endSlide');
}
echo HTMLHelper::_('bootstrap.endAccordion');
?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-4">
        <div class="widget">
            <h1>Created By</h1>
            <div class="container">
                <div class="row">
                    <div class="col"><?php echo $displayData->renderField('created_by', null, null, $headerlabeloptions); ?></div>
                    <div class="col"><?php
echo 'on ';
//var_dump($rawData);exit();
echo JedHelper::prettyDate($rawData['created_on']);
?></div>
                </div>
            </div>
        </div>
        <div class="widget">
            <h1>Related Object</h1>
            <div class="container">
                <p>In this box, show the respective item summary, extension, review, VEL item, Abandonware
                    Item</p>
            </div>
        </div>
        <div class="widget">
            <h1>Internal Notes</h1>
            <div class="container">
                <?php echo $displayData->renderField('internal_notes', null, null, $headerlabeloptions); ?>
            </div>
        </div>
    </div>
