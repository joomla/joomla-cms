<?php

/**
 * @package       JED
 *
 * @subpackage    VEL
 *
 * @copyright     (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Jed\Component\Jed\Administrator\Helper\JedHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;


HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');
$wa = $this->document->getWebAssetManager();
$wa->getRegistry()->addExtensionRegistryFile('com_jed');
$wa->useStyle('com_jed.jedVel');
$wa->useScript('keepalive')
    ->useScript('form.validate')
    ->useScript('com_jed.vulnerableItemBuildTitle')
    ->useScript('com_jed.vulnerableItemBuildPublicDescription')
    ->useScript('com_jed.vulnerableItemhideshowOtherbox');
HTMLHelper::_('bootstrap.tooltip');

$headerlabeloptions = array('hiddenLabel' => true);
$fieldhiddenoptions = array('hidden' => true);
?>

<form
        action="<?php echo Route::_('index.php?option=com_jed&layout=edit&id=' . (int) $this->item->id); ?>"
        method="post" enctype="multipart/form-data" name="adminForm" id="velvulnerableitem-form"
        class="form-validate form-horizontal">


    <div class="com_jed_vel">
        <div class="row-fluid">
            <!-- header boxes -->
            <?php echo LayoutHelper::render('vulnerableitem.header', $this->form); ?>

        </div>

    </div> <!-- end div class  com_jed_vel -->


    <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'Details')); ?>
    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'Details', Text::_('COM_JED_GENERAL_TAB_DETAILS', true)); ?>
    <div class="row-fluid">
        <div class="span10 form-horizontal">
            <fieldset class="adminform">

                <?php echo $this->form->renderField('internal_description'); ?>
                <?php echo $this->form->renderField('report_id'); ?>
                <?php
                foreach ((array) $this->item->report_id as $value) {
                    if (!is_array($value)) {
                        echo '<input type="hidden" class="report_id" name="jform[report_idhidden][' . $value . ']" value="' . $value . '" />';
                    }
                }
                ?>
                <?php echo $this->form->renderField('jed'); ?>


                <?php echo $this->form->renderField('update_notice'); ?>

                <?php echo $this->form->renderField('xml_manifest'); ?>
                <?php if (!empty($this->item->xml_manifest)) : ?>
                    <?php $xml_manifestFiles = array(); ?>
                    <?php foreach ((array) $this->item->xml_manifest as $fileSingle) : ?>
                        <?php if (!is_array($fileSingle)) : ?>
                            <a href="<?php echo Route::_(Uri::root() . '/tmp' . DIRECTORY_SEPARATOR . $fileSingle, false); ?>"><?php echo $fileSingle; ?></a> |
                            <?php $xml_manifestFiles[] = $fileSingle; ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <input type="hidden" name="jform[xml_manifest_hidden]" id="jform_xml_manifest_hidden"
                           value="<?php echo implode(',', $xml_manifestFiles); ?>"/>
                <?php endif; ?>
                <?php echo $this->form->renderField('manifest_location'); ?>
                <?php echo $this->form->renderField('install_data'); ?>
                <?php echo $this->form->renderField('discovered_by'); ?>
                <?php echo $this->form->renderField('discoverer_public'); ?>
                <?php echo $this->form->renderField('fixed_by'); ?>
                <?php echo $this->form->renderField('coordinated_by'); ?>
            </fieldset>
        </div>
    </div>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>
    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'CommonVulnerabilityScoringSystem', Text::_('COM_JED_VEL_TAB_COMMONVULNERABILITYSCORINGSYSTEM', true)); ?>
    <div class="row-fluid">
        <div class="span10 form-horizontal">
            <?php echo LayoutHelper::render('vulnerableitem.common_vulnerability', $this->form); ?>

        </div>
    </div>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>
    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'PublicDescription', Text::_('COM_JED_VEL_TAB_PUBLIC_DESCRIPTION', true)); ?>
    <div class="row-fluid">
        <div class="span10 form-horizontal">
            <?php echo LayoutHelper::render('vulnerableitem.public_description', $this->form); ?>
        </div>
    </div>
    <?php echo HTMLHelper::_('uitab.endTab');
    $velcounter = 0;
    foreach ($this->VELLinkedReports['velreport']['data'] as $velreport) {
        $velcounter++;

        echo JHtml::_('uitab.addTab', 'myTab', 'report' . $velcounter, 'Report:' . JedHelper::prettyDate($velreport->created)); ?>
        <div class="row-fluid form-horizontal-desktop">
            <div class="span10 form-horizontal">
                <?php echo LayoutHelper::render('vulnerableitem.linked_velreport', $this->VELLinkedReports['velreport']['form']); ?>

            </div>
        </div>
        <?php echo JHtml::_('uitab.endTab');
    }
    $velcounter = 0;
    foreach ($this->VELLinkedReports['veldeveloperupdate']['data'] as $veldeveloperupdate) {
        $velcounter++;
        echo JHtml::_('uitab.addTab', 'myTab', 'devupdate' . $velcounter, 'Developer Update:' . JedHelper::prettyDate($veldeveloperupdate->created)); ?>
        <div class="row-fluid form-horizontal-desktop">
            <div class="span10 form-horizontal">
                <?php echo LayoutHelper::render('vulnerableitem.linked_veldeveloperupdate', $this->VELLinkedReports['veldeveloperupdate']['form']); ?>

            </div>
        </div>
        <?php echo JHtml::_('uitab.endTab');
    }
    $velcounter = 0;
    foreach ($this->VELLinkedReports['velabandonware']['data'] as $velabandonware) {
        $velcounter++;
        echo JHtml::_('uitab.addTab', 'myTab', 'abandonware' . $velcounter, 'Abandoned Report:' . JedHelper::prettyDate($velabandonware->created)); ?>
        <div class="row-fluid form-horizontal-desktop">
            <div class="span10 form-horizontal">
                <?php echo LayoutHelper::render('vulnerableitem.linked_velabandonware', $this->VELLinkedReports['velabandonware']['form']); ?>

            </div>
        </div>
        <?php echo JHtml::_('uitab.endTab');
    }
    ?>


    <?php echo HTMLHelper::_('uitab.endTab'); ?>

    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'Publishing', Text::_('COM_JED_TAB_PUBLISHING', true)); ?>
    <div class="row-fluid">
        <div class="span10 form-horizontal">
            <fieldset class="adminform">
                <legend><?php echo Text::_('COM_JED_GENERAL_PUBLISHING_LABEL'); ?></legend>

                <?php echo $this->form->renderField('id'); ?>
                <?php echo $this->form->renderField('recommendation'); ?>
                <?php echo $this->form->renderField('state'); ?>

                <?php echo $this->form->renderField('created_by'); ?>
                <?php echo $this->form->renderField('created'); ?>
                <?php echo $this->form->renderField('modified_by'); ?>
                <?php echo $this->form->renderField('modified'); ?>
                <?php echo $this->form->renderField('checked_out'); ?>
                <?php echo $this->form->renderField('checked_out_time'); ?>
            </fieldset>
        </div>
    </div>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>

    <?php echo HTMLHelper::_('uitab.endTabSet'); ?>

    <input type="hidden" name="task" value=""/>
    <?php echo HTMLHelper::_('form.token'); ?>

</form> 
