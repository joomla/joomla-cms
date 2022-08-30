<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_privacy
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var \Joomla\Component\Privacy\Site\View\Request\HtmlView $this */

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('behavior.formvalidator');

?>
<div class="request-form<?php echo $this->pageclass_sfx; ?>">
    <?php if ($this->params->get('show_page_heading')) : ?>
        <div class="page-header">
            <h1>
                <?php echo $this->escape($this->params->get('page_heading')); ?>
            </h1>
        </div>
    <?php endif; ?>
    <?php if ($this->sendMailEnabled) : ?>
        <form action="<?php echo Route::_('index.php?option=com_privacy&task=request.submit'); ?>" method="post" class="form-validate form-horizontal well">
            <?php foreach ($this->form->getFieldsets() as $fieldset) : ?>
                <fieldset>
                    <?php if (!empty($fieldset->label)) : ?>
                        <legend><?php echo Text::_($fieldset->label); ?></legend>
                    <?php endif; ?>
                    <?php echo $this->form->renderFieldset($fieldset->name); ?>
                </fieldset>
            <?php endforeach; ?>
            <div class="control-group">
                <div class="controls">
                    <button type="submit" class="btn btn-primary validate">
                        <?php echo Text::_('JSUBMIT'); ?>
                    </button>
                </div>
            </div>
            <?php echo HTMLHelper::_('form.token'); ?>
        </form>
    <?php else : ?>
        <div class="alert alert-warning">
            <span class="icon-exclamation-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('WARNING'); ?></span>
            <?php echo Text::_('COM_PRIVACY_WARNING_CANNOT_CREATE_REQUEST_WHEN_SENDMAIL_DISABLED'); ?>
        </div>
    <?php endif; ?>
</div>
