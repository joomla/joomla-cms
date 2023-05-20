<?php

/**
 * @package           JED
 *
 * @subpackage        Tickets
 *
 * @copyright     (C) 2022 Open Source Matters, Inc.  <https://www.joomla.org>
 * @license           GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to file
use Jed\Component\Jed\Administrator\Helper\JedHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects
// phpcs:enable PSR1.Files.SideEffects

/* @var $displayData array */
$headerlabeloptions = ['hiddenLabel' => true];
$fieldhiddenoptions = ['hidden' => true];

$ticket_reviewer        = $displayData['ticket_creator'];
$sample_reviewer        = $displayData['sample_creator'];
$ticket_help_extensions = $displayData['extensions'];

try {
    $register_date  = date_format(new dateTime($ticket_reviewer->registerDate), "d-m-Y H:i");
    $register_date2 = date_format(new dateTime($sample_reviewer->registerDate), "d-m-Y H:i");
} catch (Exception $e) {
}
try {
    $lastvisit_date  = date_format(new dateTime($ticket_reviewer->lastvisitDate), "d-m-Y H:i");
    $lastvisit_date2 = date_format(new dateTime($sample_reviewer->lastvisitDate), "d-m-Y H:i");
} catch (Exception $e) {
}

?>

<div class="span10 form-horizontal">
    <p>Ticket Creator</p>
    <div class="row ticket-header-row">
        <div class="col-md-3 ticket-header">
            <h1>Name</h1>

            <span><?php echo $ticket_reviewer->name; ?></span>
        </div>
        <div class="col-md-2  ticket-header">
            <h1>Username</h1>
            <span><?php echo $ticket_reviewer->username; ?></span>
        </div>
        <div class="col-md-3  ticket-header">
            <h1>Email</h1>
            <span><?php echo $ticket_reviewer->email; ?></span>
        </div>
        <div class="col-md-2  ticket-header">
            <h1>Registered</h1>
            <span><?php echo JedHelper::prettyDate($ticket_reviewer->registerDate); ?></span>

        </div>
        <div class="col-md-2  ticket-header">
            <h1>Last Visit</h1>
            <span><?php echo JedHelper::prettyDate($ticket_reviewer->lastvisitDate); ?></span>

        </div>
    </div>
    <p><br/>As Test User does not have any other live data we are using CMSJunkie user as Ticket Creator</p>
    <div class="row ticket-header-row">
        <div class="col-md-3 ticket-header">
            <h1>Name</h1>

            <span><?php echo $sample_reviewer->name; ?></span>
        </div>
        <div class="col-md-2  ticket-header">
            <h1>Username</h1>
            <span><?php echo $sample_reviewer->username; ?></span>
        </div>
        <div class="col-md-3  ticket-header">
            <h1>Email</h1>
            <span><?php echo $sample_reviewer->email; ?></span>
        </div>
        <div class="col-md-2  ticket-header">
            <h1>Registered</h1>
            <span><?php echo JedHelper::prettyDate($sample_reviewer->registerDate); ?></span>

        </div>
        <div class="col-md-2  ticket-header">
            <h1>Last Visit</h1>
            <span><?php echo JedHelper::prettyDate($sample_reviewer->lastvisitDate); ?></span>

        </div>
    </div>

    <?php

    echo HTMLHelper::_('uitab.startTabSet', 'viewHelpTab', ['active' => 'viewhelpextension']);

echo HTMLHelper::_('uitab.addTab', 'viewHelpTab', 'viewhelpextension', Text::_('Registered Extensions', true));
?>

    <div class="widget">

        <h1>Registered Extensions</h1>
        <div class="container">
            <div class="row">
                <?php
        //echo "<pre>";print_r($ticket_help_extensions);echo "</pre>";exit();
            $slidesOptions = [//"active" => "slide0" // It is the ID of the active tab.
            ];
echo HTMLHelper::_('bootstrap.startAccordion', 'ticket_help_extensions_group', $slidesOptions);

$slideid = 0;

try {
    $extension_model = Factory::getApplication()->bootComponent('com_jed')->getMVCFactory()
        ->createModel('Extension', 'Administrator', ['ignore_request' => true]);
} catch (Exception $e) {
}

foreach ($ticket_help_extensions as $ext) {
    $linked_extension_form = $extension_model->getForm($ext, false, 'jf_registeredextension_form_' . $ext->id);
    try {
        $ico = JedHelper::getPublishedIcon($ext->published);
    } catch (Exception $e) {
    }

    echo HTMLHelper::_('bootstrap.addSlide', 'ticket_help_extensions_group', $ext->id . ' - ' . $ext->title . ' (' .
        $ext->version . ') ' . JedHelper::prettyDate($ext->created_on) . '&nbsp;&nbsp;' . $ico . '</span>', 'ticket_help_extensions_group' . '_slide' . ($slideid++));

    $linked_extension_form->bind($ext);
    echo $linked_extension_form->renderField('title');
    echo $linked_extension_form->renderField('alias');
    echo $linked_extension_form->renderField('published');
    echo $linked_extension_form->renderField('created_by');
    echo $linked_extension_form->renderField('modified_by');
    echo $linked_extension_form->renderField('created_on');
    echo $linked_extension_form->renderField('modified_on');
    echo $linked_extension_form->renderField('joomla_versions');
    echo $linked_extension_form->renderField('popular');
    echo $linked_extension_form->renderField('requires_registration');
    echo $linked_extension_form->renderField('gpl_license_type');
    echo $linked_extension_form->renderField('jed_internal_note');
    echo $linked_extension_form->renderField('can_update');
    echo $linked_extension_form->renderField('video');
    echo $linked_extension_form->renderField('version');
    echo $linked_extension_form->renderField('uses_updater');
    echo $linked_extension_form->renderField('includes');
    echo $linked_extension_form->renderField('approved');
    echo $linked_extension_form->renderField('approved_time');
    echo $linked_extension_form->renderField('second_contact_email');
    echo $linked_extension_form->renderField('jed_checked');
    echo $linked_extension_form->renderField('uses_third_party');
    echo $linked_extension_form->renderField('primary_category_id');
    echo $linked_extension_form->renderField('logo');
    echo $linked_extension_form->renderField('approved_notes');
    echo $linked_extension_form->renderField('approved_reason');
    echo $linked_extension_form->renderField('published_notes');
    echo $linked_extension_form->renderField('published_reason');
    echo $linked_extension_form->renderField('state');
    echo "<p>Extension Summary here</p>";
    echo JHtml::_('bootstrap.endSlide');
}

echo HTMLHelper::_('bootstrap.endAccordion');

?>


            </div>

        </div>
    </div>
    <?php
    echo HTMLHelper::_('uitab.endTab');

echo HTMLHelper::_('uitab.addTab', 'viewHelpTab', 'viewhelpreviews', Text::_('Reviews', true)); ?>
    <div class="widget">
        <h1>Reviews</h1>
        <div class="container">
            <div class="row">
                <?php

            echo HTMLHelper::_('bootstrap.startAccordion', 'ticket_help_reviews_group', $slidesOptions);

$slideid = 0;
foreach ($displayData['reviews'] as $review) {
    if ($review->published === 1) {
        $ico = '<span class="fas fa-bolt"></span>';
    } else {
        $ico = '';
    }

    echo HTMLHelper::_('bootstrap.addSlide', 'ticket_help_reviews_group', $review->id . ' - ' . $review->title . '&nbsp;' .
        JedHelper::prettyDate($review->created_on) . '&nbsp;', 'ticket_help_reviews_group' . '_slide' . ($slideid++));
    echo "<p>Review Summary here</p>";
    echo JHtml::_('bootstrap.endSlide');
}
echo HTMLHelper::_('bootstrap.endAccordion');

?>


            </div>

        </div>
    </div>
    <?php echo HTMLHelper::_('uitab.endTab');

echo HTMLHelper::_('uitab.addTab', 'viewHelpTab', 'viewhelpoldtickets', Text::_('Old Tickets', true)); ?>
    <div class="widget">
        <h1>Old Tickets</h1>
        <div class="container">
            <div class="row">
                <?php

            echo HTMLHelper::_('bootstrap.startAccordion', 'ticket_help_oldtickets_group', $slidesOptions);

$slideid = 0;
foreach ($displayData['oldtickets'] as $oldticket) {
    echo HTMLHelper::_('bootstrap.addSlide', 'ticket_help_oldtickets_group', $oldticket->id . ' - ' . $oldticket->subject . '&nbsp;' .
        JedHelper::prettyDate($oldticket->date) . '&nbsp;', 'ticket_help_oldtickets_group' . '_slide' . ($slideid++));

    echo "<p>" . strip_tags($oldticket->messages->message) . "</p>";
    echo JHtml::_('bootstrap.endSlide');
}
echo HTMLHelper::_('bootstrap.endAccordion');

?>


            </div>

        </div>
    </div>
    <?php echo HTMLHelper::_('uitab.endTab');


echo HTMLHelper::_('uitab.endTabSet'); ?>
</div>

