<?php

/**
 * @package        JED
 *
 * @copyright  (C) 2022 Open Source Matters, Inc.  <https://www.joomla.org>
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Jed\Component\Jed\Administrator\View\Extension\HtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Actionlogs\Administrator\Helper\ActionlogsHelper;

/** @var HtmlView $this */

HTMLHelper::_('script', 'com_jed/jed.js', ['version' => 'auto', 'relative' => true]);

try {
    Factory::getApplication()->getDocument()->getWebAssetManager()
        ->useScript('form.validate')
        ->useScript('keepalive')
        ->usePreset('choicesjs')
        ->useScript('webcomponent.field-fancy-select');
} catch (Exception $e) {
}

Text::script('COM_JED_EXTENSIONS_ERROR_DURING_SEND_EMAIL', true);
Text::script('COM_JED_EXTENSIONS_MISSING_MESSAGE_ID', true);
Text::script('COM_JED_EXTENSIONS_MISSING_DEVELOPER_ID', true);
Text::script('COM_JED_EXTENSIONS_MISSING_EXTENSION_ID', true);
Text::script('COM_JED_EXTENSIONS_ERROR_SAVING_APPROVE', true);
Text::script('COM_JED_EXTENSIONS_EXTENSION_APPROVED_REASON_REQUIRED', true);
Text::script('COM_JED_EXTENSIONS_ERROR_SAVING_PUBLISH', true);
Text::script('COM_JED_EXTENSIONS_EXTENSION_PUBLISHED_REASON_REQUIRED', true);

$extensionUrl = Uri::root() . 'extension/' . $this->extension->alias;
$downloadUrl  = 'index.php?option=com_jed&task=extension.download&id=' . $this->extension->id;

Factory::getDocument()
    ->addScriptOptions('joomla.userId', Factory::getUser()->id, false)
    ->addScriptDeclaration(
        <<<JS
	Joomla.submitbutton = function(task)
	{
	    switch (task) {
            case 'extension.preview': 
                window.open('{$extensionUrl}');
                break;
            case 'extension.download':
                window.open('{$downloadUrl}');
                break;
            default:
                if (task === 'extension.cancel' || document.formvalidator.isValid(document.getElementById('extension-form')))
                {
                    Joomla.submitform(task, document.getElementById("extension-form"));
                }
                break;
	    }
	}
JS
    );

?>
    <form action="index.php?option=com_jed&view=extension&layout=edit&id=<?php
    echo (int)$this->extension->id; ?>"
          method="post" name="adminForm" id="extension-form" class="form-validate">

        <?php
        echo LayoutHelper::render('joomla.edit.title_alias', $this); ?>

        <div class="form-horizontal">
            <?php
            echo $this->form->renderField('created_by'); ?>
            <?php
            echo $this->form->renderField('jedChecker'); ?>
            <?php
            echo HTMLHelper::_('uitab.startTabSet', 'extensionTab', ['active' => 'general']); ?>

            <?php
            echo HTMLHelper::_('uitab.addTab', 'extensionTab', 'general', Text::_('COM_JED_EXTENSIONS_INFO_TAB')); ?>
            <div class="row-fluid form-horizontal-desktop">
                <div class="span9">
                    <div class="form-horizontal">
                        <?php
                        echo $this->form->renderFieldset('info'); ?>
                    </div>
                </div>
                <div class="span3">
                    <div class="form-vertical">
                        <?php
                        echo $this->form->renderFieldset('publication'); ?>
                    </div>
                </div>
            </div>
            <?php
            echo HTMLHelper::_('uitab.endTab');

            foreach ($this->extension->varied_data as $vr) {
                $varied_form = $this->extensionvarieddatum_form;

                $varied_form->bind($vr);
                echo HTMLHelper::_('uitab.addTab', 'extensionTab', 'viewextensionsupply_tab_' . $vr->supply_type, Text::_($vr->supply_type, true) . '&nbsp;' . Text::_('COM_JED_EXTENSIONS_VERSION', true));
                echo $varied_form->renderFieldset('info');

                echo $varied_form->renderField('tags');
                echo $varied_form->renderField('state');
                echo $varied_form->renderField('created_by');

                echo HTMLHelper::_('uitab.endTab');
            }
            //      echo "<pre>";print_r($this->extension);echo "</pre>";exit();
            ?>


            <?php
            /*echo HTMLHelper::_(
                       'uitab.addTab',
                       'extensionTab',
                       'info',
                       Text::_('COM_JED_EXTENSIONS_CONTENT_TAB')
                   ); ?>
                   <div class="row-fluid">
                       <div class="span12">
                           <div class="form-horizontal">
                               <?php echo $this->form->renderFieldset('content'); ?>
                           </div>
                       </div>
                   </div>
                   <?php echo HTMLHelper::_('uitab.endTab'); */ ?>

            <?php
            echo HTMLHelper::_(
                'uitab.addTab',
                'extensionTab',
                'image',
                Text::_('COM_JED_EXTENSIONS_CONTENT_IMAGE')
            ); ?>
            <div class="row-fluid">
                <div class="span12">
                    <div class="form-horizontal">
                        <?php
                        echo $this->form->renderFieldset('image'); ?>
                    </div>
                </div>
            </div>
            <?php
            echo HTMLHelper::_('uitab.endTab'); ?>

            <?php
            echo HTMLHelper::_(
                'uitab.addTab',
                'extensionTab',
                'downloads',
                Text::_('COM_JED_EXTENSIONS_DOWNLOADS_TAB')
            ); ?>
            <div class="row-fluid">
                <div class="span12">
                    <div class="form-horizontal">
                        <?php
                        echo $this->form->renderField('downloadIntegrationType'); ?>
                        <?php
                        echo $this->form->renderField('requiresRegistration'); ?>
                        <?php
                        echo $this->form->renderField('downloadIntegrationUrl'); ?>
                        <h3><?php
                            echo Text::_('COM_JED_EXTENSIONS_DOWNLOAD_ALTERNATIVE_DOWNLOAD'); ?></h3>
                        <?php
                        echo $this->form->renderField('downloadIntegrationType1'); ?>
                        <?php
                        echo $this->form->renderField('downloadIntegrationType2'); ?>
                        <?php
                        echo $this->form->renderField('downloadIntegrationType3'); ?>
                        <?php
                        echo $this->form->renderField('downloadIntegrationType4'); ?>
                    </div>
                </div>
            </div>
            <?php
            echo HTMLHelper::_('uitab.endTab'); ?>

            <?php
            echo HTMLHelper::_(
                'uitab.addTab',
                'extensionTab',
                'reviews',
                Text::_('COM_JED_EXTENSIONS_REVIEWS_TAB')
            ); ?>
            <div class="row-fluid">
                <div class="span12">
                    <div class="form-horizontal">
                        <?php
                        echo $this->form->renderFieldset('reviews'); ?>
                    </div>
                    <?php
                    echo HTMLHelper::_(
                        'link',
                        'index.php?option=com_jed&view=reviews&filter[extension]=' . $this->extension->id,
                        Text::_('COM_JED_EXTENSIONS_REVIEW_LINK') . ' <span class="icon-new-tab"></span>',
                        'target="_blank"'
                    );
                    ?>
                </div>
            </div>
            <?php
            echo HTMLHelper::_('uitab.endTab'); ?>

            <?php
            echo HTMLHelper::_(
                'uitab.addTab',
                'extensionTab',
                'communication',
                Text::_('COM_JED_EXTENSIONS_COMMUNICATION_TAB')
            ); ?>
            <div class="row-fluid">
                <div class="span12">
                    <div class="form-horizontal">
                        <?php
                        echo $this->form->renderFieldset('communication'); ?>
                        <div class="control-group">
                            <div class="control-label">
                            </div>
                            <div class="controls">
                                <button class="btn btn-success js-messageType js-sendMessage" onclick="jed.sendMessage(); return false;">
                                    <?php
                                    echo Text::_('COM_JED_SEND_EMAIL'); ?>
                                </button>

                                <button class="btn btn-success js-messageType js-storeNote" style="display: none;" onclick="jed.storeNote(); return false;">
                                    <?php
                                    echo Text::_('COM_JED_STORE_NOTE'); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            echo HTMLHelper::_('uitab.endTab'); ?>

            <?php
            echo HTMLHelper::_(
                'uitab.addTab',
                'extensionTab',
                'history',
                Text::_('COM_JED_EXTENSIONS_HISTORY_TAB')
            ); ?>
            <div class="row-fluid">
                <div class="span12">
                    <table class="table table-striped table-condensed">
                        <thead>
                        <tr>
                            <td><?php
                                echo Text::_('COM_JED_EXTENSION_HISTORY_DATE'); ?></td>
                            <td><?php
                                echo Text::_('COM_JED_EXTENSION_HISTORY_TYPE'); ?></td>
                            <td><?php
                                echo Text::_('COM_JED_EXTENSION_HISTORY_TEXT'); ?></td>
                            <td><?php
                                echo Text::_('COM_JED_EXTENSION_HISTORY_MEMBER'); ?></td>
                            <td><?php
                                echo Text::_('COM_JED_EXTENSION_HISTORY_USER'); ?></td>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        if (isset($this->extension->history)) :
                            foreach ($this->extension->history as $history) :
                                ?>
                                <tr><?php
                                ?>
                                <td><?php
                                echo HTMLHelper::_('date', $history->logDate, Text::_('COM_JED_DATETIME_FORMAT')); ?></td><?php
?>
                                <td><?php
                                echo Text::_('COM_JED_EXTENSION_HISTORY_LOG_' . $history->type); ?></td><?php

if ($history->type === 'mail') {
    ?>
                                    <td>
    <?php
    echo $history->subject; ?>
                                    <?php
                                    echo $history->body; ?>
                                    </td><?php
                                    ?>
                                    <td><?php
                                    echo $history->memberName; ?></td><?php
?>
                                    <td><?php
                                    echo HTMLHelper::_('link', 'index.php?option=com_users&task=user.edit&id=' . $history->developerId, $history->developerName); ?> &lt;<?php
    echo $history->developerEmail; ?>&gt;</td><?php
}
if ($history->type === 'note') {
    ?>
                                    <td>
    <?php
    echo $history->body; ?>
                                    </td><?php
                                    ?>
                                    <td><?php
                                    echo $history->memberName; ?></td><?php
?>
                                    <td><?php
                                    echo HTMLHelper::_('link', 'index.php?option=com_users&task=user.edit&id=' . $history->developerId, $history->developerName); ?></td><?php
} elseif ($history->type === 'actionLog') {
    ?>
                                    <td><?php
                                    echo ActionlogsHelper::getHumanReadableLogMessage($history); ?></td><?php
?>
                                    <td><?php
                                    echo $history->name; ?></td><?php
?>
                                    <td></td><?php
}
?></tr><?php
                            endforeach;
                        endif;
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php
            echo HTMLHelper::_('uitab.endTab'); ?>

            <?php
            echo HTMLHelper::_('uitab.endTabSet'); ?>

        </div>

        <?php
        try {
            echo HTMLHelper::_(
                'bootstrap.renderModal',
                'approveModal',
                [
                    'title'      => Text::_('COM_JED_EXTENSIONS_APPROVE_STATE'),
                    'footer'     => $this->loadTemplate('approve_footer'),
                    'modalWidth' => '30vh'
                ],
                $this->loadTemplate('approve_body')
            );
        } catch (Exception $e) {
        } ?>

        <?php
        try {
            echo HTMLHelper::_(
                'bootstrap.renderModal',
                'publishModal',
                [
                    'title'      => Text::_('COM_JED_EXTENSIONS_PUBLISH_STATE'),
                    'footer'     => $this->loadTemplate('publish_footer'),
                    'modalWidth' => '30vh'
                ],
                $this->loadTemplate('publish_body')
            );
        } catch (Exception $e) {
        } ?>

        <input type="hidden" name="option" value="com_jed"/>
        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="boxchecked" value="0"/>
        <?php
        echo HTMLHelper::_('form.token'); ?>
    </form>

<?php
/* OLD CC GENERATED */
/*
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;


HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate');
HTMLHelper::_('bootstrap.tooltip');
?>

<form
    action="<?php echo Route::_('index.php?option=com_jed&layout=edit&id=' . (int) $this->extension->id); ?>"
    method="post" enctype="multipart/form-data" name="adminForm" id="extension-form" class="form-validate form-horizontal">


    <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'extension')); ?>
    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'extension', Text::_('COM_JED_TAB_EXTENSION', true)); ?>
    <div class="row-fluid">
        <div class="span10 form-horizontal">
            <fieldset class="adminform">
                <legend><?php echo Text::_('COM_JED_FIELDSET_EXTENSION'); ?></legend>
                <?php echo $this->form->renderField('id'); ?>
                <?php echo $this->form->renderField('title'); ?>
                <?php echo $this->form->renderField('alias'); ?>
                <?php echo $this->form->renderField('published'); ?>
                <?php echo $this->form->renderField('created_by'); ?>
                <?php echo $this->form->renderField('modified_by'); ?>
                <?php echo $this->form->renderField('created_on'); ?>
                <?php echo $this->form->renderField('modified_on'); ?>
                <?php echo $this->form->renderField('joomla_versions'); ?>
                <?php echo $this->form->renderField('popular'); ?>
                <?php echo $this->form->renderField('requires_registration'); ?>
                <?php echo $this->form->renderField('gpl_license_type'); ?>
                <?php echo $this->form->renderField('jed_internal_note'); ?>
                <?php echo $this->form->renderField('can_update'); ?>
                <?php echo $this->form->renderField('video'); ?>
                <?php echo $this->form->renderField('version'); ?>
                <?php echo $this->form->renderField('uses_updater'); ?>
                <?php echo $this->form->renderField('includes'); ?>
                <?php echo $this->form->renderField('approved'); ?>
                <?php echo $this->form->renderField('approved_time'); ?>
                <?php echo $this->form->renderField('second_contact_email'); ?>
                <?php echo $this->form->renderField('jed_checked'); ?>
                <?php echo $this->form->renderField('uses_third_party'); ?>
                <?php echo $this->form->renderField('primary_category_id'); ?>
                <?php echo $this->form->renderField('logo'); ?>
                <?php echo $this->form->renderField('approved_notes'); ?>
                <?php echo $this->form->renderField('approved_reason'); ?>
                <?php echo $this->form->renderField('published_notes'); ?>
                <?php echo $this->form->renderField('published_reason'); ?>
                <?php echo $this->form->renderField('state'); ?>
            </fieldset>
        </div>
    </div>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>


    <?php echo HTMLHelper::_('uitab.endTabSet'); ?>

    <input type="hidden" name="task" value=""/>
    <?php echo HTMLHelper::_('form.token'); ?>

</form>
*/
