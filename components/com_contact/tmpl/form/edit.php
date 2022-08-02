<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('behavior.formvalidator');

$this->tab_name         = 'com-contact-form';
$this->ignore_fieldsets = ['details', 'item_associations', 'language'];
$this->useCoreUI        = true;
?>
<div class="edit item-page<?php echo $this->pageclass_sfx; ?>">
    <?php if ($this->params->get('show_page_heading')) : ?>
        <div class="page-header">
            <h1>
                <?php echo $this->escape($this->params->get('page_heading')); ?>
            </h1>
        </div>
    <?php endif; ?>

    <form action="<?php echo Route::_('index.php?option=com_contact&id=' . (int) $this->item->id); ?>" method="post"
        name="adminForm" id="adminForm" class="form-validate form-vertical">
        <fieldset>
            <?php echo HTMLHelper::_('uitab.startTabSet', $this->tab_name, ['active' => 'details', 'recall' => true, 'breakpoint' => 768]); ?>
            <?php echo HTMLHelper::_('uitab.addTab', $this->tab_name, 'details', empty($this->item->id) ? Text::_('COM_CONTACT_NEW_CONTACT') : Text::_('COM_CONTACT_EDIT_CONTACT')); ?>
            <?php echo $this->form->renderField('name'); ?>

            <?php if (is_null($this->item->id)) : ?>
                <?php echo $this->form->renderField('alias'); ?>
            <?php endif; ?>

            <?php echo $this->form->renderFieldset('details'); ?>
            <?php echo HTMLHelper::_('uitab.endTab'); ?>

            <?php echo HTMLHelper::_('uitab.addTab', $this->tab_name, 'misc', Text::_('COM_CONTACT_FIELDSET_MISCELLANEOUS')); ?>
            <?php echo $this->form->getInput('misc'); ?>
            <?php echo HTMLHelper::_('uitab.endTab'); ?>

            <?php if (Multilanguage::isEnabled()) : ?>
                <?php echo HTMLHelper::_('uitab.addTab', $this->tab_name, 'language', Text::_('JFIELD_LANGUAGE_LABEL')); ?>
                <?php echo $this->form->renderField('language'); ?>
                <?php echo HTMLHelper::_('uitab.endTab'); ?>
            <?php else : ?>
                <?php echo $this->form->renderField('language'); ?>
            <?php endif; ?>

            <?php echo LayoutHelper::render('joomla.edit.params', $this); ?>
            <?php echo HTMLHelper::_('uitab.endTabSet'); ?>

            <input type="hidden" name="task" value=""/>
            <input type="hidden" name="return" value="<?php echo $this->return_page; ?>"/>
            <?php echo HTMLHelper::_('form.token'); ?>
        </fieldset>
        <div class="mb-2">
            <button type="button" class="btn btn-primary" onclick="Joomla.submitbutton('contact.save')">
                <span class="icon-check" aria-hidden="true"></span>
                <?php echo Text::_('JSAVE'); ?>
            </button>
            <button type="button" class="btn btn-danger" onclick="Joomla.submitbutton('contact.cancel')">
                <span class="icon-times" aria-hidden="true"></span>
                <?php echo Text::_('JCANCEL'); ?>
            </button>
            <?php if ($this->params->get('save_history', 0) && $this->item->id) : ?>
                <?php echo $this->form->getInput('contenthistory'); ?>
            <?php endif; ?>
        </div>
    </form>
</div>
