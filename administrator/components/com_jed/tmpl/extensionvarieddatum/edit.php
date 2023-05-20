<?php

/**
 * @package    JED
 *
 * @copyright  (C) 2022 Open Source Matters, Inc.  <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

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
    action="<?php echo Route::_('index.php?option=com_jed&layout=edit&id=' . (int) $this->item->id); ?>"
    method="post" enctype="multipart/form-data" name="adminForm" id="extensionvarieddatum-form" class="form-validate form-horizontal">


    <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'field')); ?>
    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'field', Text::_('COM_JED_TAB_FIELD', true)); ?>
    <div class="row-fluid">
        <div class="span10 form-horizontal">
            <fieldset class="adminform">
                <legend><?php echo Text::_('COM_JED_FIELDSET_FIELD'); ?></legend>
                <?php echo $this->form->renderField('id'); ?>
                <?php echo $this->form->renderField('extension_id'); ?>
                <?php echo $this->form->renderField('supply_option_id'); ?>
                <?php echo $this->form->renderField('intro_text'); ?>
                <?php echo $this->form->renderField('description'); ?>
                <?php echo $this->form->renderField('homepage_link'); ?>
                <?php echo $this->form->renderField('download_link'); ?>
                <?php echo $this->form->renderField('demo_link'); ?>
                <?php echo $this->form->renderField('support_link'); ?>
                <?php echo $this->form->renderField('documentation_link'); ?>
                <?php echo $this->form->renderField('license_link'); ?>
                <?php echo $this->form->renderField('tags'); ?>
                <?php echo $this->form->renderField('state'); ?>
                <?php echo $this->form->renderField('created_by'); ?>
                <?php echo $this->form->renderField('update_url'); ?>
                <?php echo $this->form->renderField('update_url_ok'); ?>
                <?php echo $this->form->renderField('download_integration_type'); ?>
                <?php echo $this->form->renderField('download_integration_url'); ?>
                <?php echo $this->form->renderField('is_default_data'); ?>
                <?php echo $this->form->renderField('translation_link'); ?>
            </fieldset>
        </div>
    </div>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>


    <?php echo HTMLHelper::_('uitab.endTabSet'); ?>

    <input type="hidden" name="task" value=""/>
    <?php echo HTMLHelper::_('form.token'); ?>

</form>
