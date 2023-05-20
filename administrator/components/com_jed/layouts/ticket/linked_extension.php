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
// phpcs:disable PSR1.Files.SideEffects
// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/* @var $displayData array */

$headerlabeloptions = ['hiddenLabel' => true];
$fieldhiddenoptions = ['hidden' => true];

$extension_form = $displayData->extension_form;
$title          = $extension_form->getField('title') ? 'title' : ($extension_form->getField('name') ? 'name' : '');

?>
    <div class="row title-alias form-vertical mb-3">
        <div class="col-12 col-md-6">
            <?php echo $title ? $extension_form->renderField($title) : ''; ?>
        </div>
        <div class="col-12 col-md-6">
            <?php echo $extension_form->renderField('alias'); ?>
        </div>
    </div>
<?php
echo HTMLHelper::_('uitab.startTabSet', 'viewExtensionTab', ['active' => 'viewextension']);


echo HTMLHelper::_(
    'uitab.addTab',
    'viewExtensionTab',
    'general',
    Text::_('COM_JED_EXTENSIONS_INFO_TAB')
); ?>
    <div class="row-fluid form-horizontal-desktop">
        <div class="span9">
            <div class="form-horizontal">
                <?php echo $extension_form->renderFieldset('info'); ?>
            </div>
        </div>
        <div class="span3">
            <div class="form-vertical">
                <?php // echo $this->form->renderFieldset('publication');?>
            </div>
        </div>
    </div>
<?php echo HTMLHelper::_('uitab.endTab');

/*
echo HTMLHelper::_('uitab.addTab', 'viewExtensionTab', 'viewextension', Text::_('Extension', true));


JedHelper::lockFormFields($extension_form, array(''));

        //       echo $extension_form->renderField('title',null,null);
            //   echo $extension_form->renderField('alias',null,null);
                 echo $extension_form->renderField('published',null,null);
                 echo $extension_form->renderField('created_by',null,null);
                 echo $extension_form->renderField('modified_by',null,null);
                 echo $extension_form->renderField('created_on',null,null);
                 echo $extension_form->renderField('modified_on',null,null);
            //   echo $extension_form->renderField('joomla_versions',null,null);
                 echo $extension_form->renderField('popular',null,null);
                 echo $extension_form->renderField('requires_registration',null,null);
                 echo $extension_form->renderField('gpl_license_type',null,null);
                 echo $extension_form->renderField('jed_internal_note',null,null);
                 echo $extension_form->renderField('can_update',null,null);
                 echo $extension_form->renderField('video',null,null);
                 echo $extension_form->renderField('version',null,null);
                 echo $extension_form->renderField('uses_updater',null,null);
        //       echo $extension_form->renderField('includes',null,null);
                 echo $extension_form->renderField('approved',null,null);
                 echo $extension_form->renderField('approved_time',null,null);
                 echo $extension_form->renderField('second_contact_email',null,null);
                 echo $extension_form->renderField('jed_checked',null,null);
                 echo $extension_form->renderField('uses_third_party',null,null);
                 echo $extension_form->renderField('primary_category_id',null,null);
                 echo $extension_form->renderField('logo',null,null);
                 echo $extension_form->renderField('approved_notes',null,null);
                 echo $extension_form->renderField('approved_reason',null,null);
                 echo $extension_form->renderField('published_notes',null,null);
                 echo $extension_form->renderField('published_reason',null,null);
                 echo $extension_form->renderField('state',null,null);
echo HTMLHelper::_('uitab.endTab'); */

$varied_form = $displayData->varied_form;
//JedHelper::lockFormFields($varied_form,array(''));
foreach ($displayData->varied_data as $vr) {
    $varied_form->bind($vr);
    echo HTMLHelper::_('uitab.addTab', 'viewExtensionTab', 'viewextensionsupply_tab_' . $vr->supply_type, Text::_($vr->supply_type, true) . '&nbsp;' . Text::_('COM_JED_EXTENSIONS_VERSION', true));
    echo $varied_form->renderFieldset('info');

    echo $varied_form->renderField('tags');
    echo $varied_form->renderField('state');
    echo $varied_form->renderField('created_by');

    echo HTMLHelper::_('uitab.endTab');
}
echo HTMLHelper::_('uitab.addTab', 'viewExtensionTab', 'viewextensionreviews', Text::_('Reviews', true));
?>
    <table id="reviewsTable" class="display" style="width:100%">
        <thead>
        <tr>
            <th></th>
            <th>Title</th>
            <th>Score</th>
            <th>Created By</th>
            <th>Date Created</th>
        </tr>
        </thead>
        <tbody>

        </tbody>
        <tfoot>
        <tr>
            <th>&nbsp;</th>
            <th>Title</th>
            <th>Score</th>
            <th>Created By</th>
            <th>Date Created</th>
        </tr>
        </tfoot>
    </table>
<?php
echo HTMLHelper::_('uitab.endTab');

echo HTMLHelper::_('uitab.endTabSet');
//echo "<pre>";
//print_r($displayData);
//echo "</pre>";
//$rawData = $displayData->getData();

$reviews = json_encode($displayData->reviews['Free'], JSON_HEX_QUOT | JSON_HEX_TAG);
// Populate the media config
$config = [
    'outputJson' => $reviews,
    'table_id'   => 'reviewsTable',
];


try {
    $doc = Factory::getApplication()->getDocument();
    $doc->addScriptOptions('com_jed', $config);
    $wa = $doc->getWebAssetManager();
    $wa->useScript('com_jed.reviews_data');
} catch (Exception $e) {
}


?>
