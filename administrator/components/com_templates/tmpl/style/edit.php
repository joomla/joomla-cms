<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

/** @var \Joomla\Component\Templates\Administrator\View\Style\HtmlView $this */

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate');

$this->useCoreUI = true;

$user = $this->getCurrentUser();
?>

<form action="<?php echo Route::_('index.php?option=com_templates&layout=edit&id=' . (int) $this->item->id); ?>"
    method="post" name="adminForm" id="style-form" aria-label="<?php echo Text::_('COM_TEMPLATES_STYLE_FORM_EDIT'); ?>"
    class="form-validate">

    <?php echo LayoutHelper::render('joomla.edit.title_alias', $this); ?>

    <div class="main-card">
        <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'details', 'recall' => true, 'breakpoint' => 768]); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', Text::_('JDETAILS')); ?>

        <div class="row">
            <div class="col-lg-9">
                <h2>
                    <?php echo Text::_($this->item->template); ?>
                </h2>
                <div class="info-labels">
                    <span class="badge bg-secondary">
                        <?php echo $this->item->client_id == 0 ? Text::_('JSITE') : Text::_('JADMINISTRATOR'); ?>
                    </span>
                </div>
                <div>
                    <?php
                    $this->fieldset    = 'description';
                    $short_description = Text::_($this->item->xml->description);
                    $long_description  = LayoutHelper::render('joomla.edit.fieldset', $this);

                    if (!$long_description) {
                        $truncated = HTMLHelper::_('string.truncate', $short_description, 550, true, false);

                        if (strlen($truncated) > 500) {
                            $long_description  = $short_description;
                            $short_description = HTMLHelper::_('string.truncate', $truncated, 250);

                            if ($short_description == $long_description) {
                                $long_description = '';
                            }
                        }
                    }
                    ?>
                    <p><?php echo $short_description; ?></p>
                    <?php if ($long_description) : ?>
                    <p class="readmore">
                        <a href="#" onclick="document.querySelector('[aria-controls=description]').click();">
                            <?php echo Text::_('JGLOBAL_SHOW_FULL_DESCRIPTION'); ?>
                        </a>
                    </p>
                    <?php endif; ?>
                </div>
                <?php
                $this->fieldset = 'basic';
                $html = LayoutHelper::render('joomla.edit.fieldset', $this);
                echo $html ? '<hr>' . $html : '';
                ?>
            </div>
            <div class="col-lg-3">
                <?php
                // Set main fields.
                $this->fields = [
                    'home',
                    'client_id',
                    'template'
                ];
                ?>
                <?php echo LayoutHelper::render('joomla.edit.global', $this); ?>
                <?php echo $this->form->renderField('inheritable'); ?>
                <?php echo $this->form->renderField('parent'); ?>
            </div>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php if (isset($long_description) && $long_description != '') : ?>
            <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'description', Text::_('JGLOBAL_FIELDSET_DESCRIPTION')); ?>
            <fieldset id="fieldset-description" class="options-form">
                <legend><?php echo Text::_('JGLOBAL_FIELDSET_DESCRIPTION'); ?></legend>
                <div>
                    <?php echo $long_description; ?>
                </div>
            </fieldset>
            <?php echo HTMLHelper::_('uitab.endTab'); ?>
        <?php endif; ?>

        <?php
        $this->fieldsets = [];
        $this->ignore_fieldsets = ['basic', 'description', 'assigned'];
        echo LayoutHelper::render('joomla.edit.params', $this);
        ?>

        <?php if ($user->authorise('core.edit', 'com_menus') && $this->item->client_id == 0 && $this->canDo->get('core.edit.state')) : ?>
            <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'assignment', Text::_('COM_TEMPLATES_MENUS_ASSIGNMENT')); ?>
            <?php if ($this->form->getGroup('assigned')) : ?>
                <?php
                $this->ignore_fieldsets = ['basic'];
                $this->fieldset         = 'assigned';

                foreach ($this->form->getFieldsets() as $fieldSet) {
                    if ($fieldSet->name !== 'assigned') {
                        $this->ignore_fieldsets[] = $fieldSet->name;
                    }
                }

                echo LayoutHelper::render('joomla.edit.fieldset', $this);
                ?>
            <?php else : ?>
                <fieldset id="fieldset-assignment" class="options-form">
                    <legend><?php echo Text::_('COM_TEMPLATES_MENUS_ASSIGNMENT'); ?></legend>
                    <div>
                        <?php echo $this->loadTemplate('assignment'); ?>
                    </div>
                </fieldset>
            <?php endif; ?>
            <?php echo HTMLHelper::_('uitab.endTab'); ?>
        <?php endif; ?>

        <?php echo HTMLHelper::_('uitab.endTabSet'); ?>

        <input type="hidden" name="task" value="">
        <?php echo HTMLHelper::_('form.token'); ?>
    </div>
</form>
