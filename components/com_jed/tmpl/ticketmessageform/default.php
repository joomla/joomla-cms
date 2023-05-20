<?php

/**
 * @package       JED
 *
 * @subpackage    TICKETS
 *
 * @copyright     (C) 2022 Open Source Matters, Inc.  <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Jed\Component\Jed\Site\Helper\JedHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate');
HTMLHelper::_('bootstrap.tooltip');

// Load admin language file
$lang = Factory::getLanguage();
$lang->load('com_jed', JPATH_SITE);

$user    = JedHelper::getUser();
$canEdit = JedHelper::canUserEdit($this->item);


?>

<div class="ticketmessage-edit front-end-edit">
    <?php if (!$canEdit) : ?>
        <h3>
            <?php throw new Exception(Text::_('COM_JED_GENERAL_ERROR_MESSAGE_NOT_AUTHORISED'), 403); ?>
        </h3>
    <?php else : ?>
        <?php if (!empty($this->item->id)) : ?>
            <h1><?php echo Text::sprintf('JGLOBAL_EDIT', $this->item->id); ?></h1>
        <?php else : ?>
            <h1><?php echo Text::_('JGLOBAL_FIELD_ADD'); ?></h1>
        <?php endif; ?>

        <form id="form-ticketmessage"
              action="<?php echo Route::_('index.php?option=com_jed&task=ticketmessageform.save'); ?>"
              method="post" class="form-validate form-horizontal" enctype="multipart/form-data">

            <input type="hidden" name="jform[id]" value="<?php echo $this->item->id ?? ''; ?>"/>

            <input type="hidden" name="jform[ordering]"
                   value="<?php echo $this->item->ordering ?? ''; ?>"/>

            <input type="hidden" name="jform[state]"
                   value="<?php echo $this->item->state ?? ''; ?>"/>

            <input type="hidden" name="jform[checked_out]"
                   value="<?php echo $this->item->checked_out ?? ''; ?>"/>

            <input type="hidden" name="jform[checked_out_time]"
                   value="<?php echo $this->item->checked_out_time ?? ''; ?>"/>

            <?php echo $this->form->getInput('created_by'); ?>
            <?php echo $this->form->getInput('modified_by'); ?>
            <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'message')); ?>
            <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'message', Text::_('COM_JED_TAB_MESSAGE', true)); ?>
            <?php echo $this->form->renderField('subject'); ?>

            <?php echo $this->form->renderField('message'); ?>

            <?php echo $this->form->renderField('ticket_id'); ?>

            <?php echo HTMLHelper::_('uitab.endTab'); ?>
            <div class="control-group">
                <div class="controls">

                    <?php if ($this->canSave) : ?>
                        <button type="submit" class="validate btn btn-primary">
                            <span class="fas fa-check" aria-hidden="true"></span>
                            <?php echo Text::_('JSUBMIT'); ?>
                        </button>
                    <?php endif; ?>
                    <a class="btn btn-danger"
                       href="<?php echo Route::_('index.php?option=com_jed&task=ticketmessageform.cancel'); ?>"
                       title="<?php echo Text::_('JCANCEL'); ?>">
                        <span class="fas fa-times" aria-hidden="true"></span>
                        <?php echo Text::_('JCANCEL'); ?>
                    </a>
                </div>
            </div>

            <input type="hidden" name="option" value="com_jed"/>
            <input type="hidden" name="task"
                   value="ticketmessageform.save"/>
            <?php echo HTMLHelper::_('form.token'); ?>
        </form>
    <?php endif; ?>
</div>
