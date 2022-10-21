<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('behavior.formvalidator');

?>
<div class="com-users-reset reset">
    <?php if ($this->params->get('show_page_heading')) : ?>
        <div class="page-header">
            <h1>
                <?php echo $this->escape($this->params->get('page_heading')); ?>
            </h1>
        </div>
    <?php endif; ?>
    <form id="user-registration" action="<?php echo Route::_('index.php?option=com_users&task=reset.request'); ?>" method="post" class="com-users-reset__form form-validate form-horizontal well">
        <?php foreach ($this->form->getFieldsets() as $fieldset) : ?>
            <fieldset>
                <?php if (isset($fieldset->label)) : ?>
                    <legend><?php echo Text::_($fieldset->label); ?></legend>
                <?php endif; ?>
                <?php echo $this->form->renderFieldset($fieldset->name); ?>
            </fieldset>
        <?php endforeach; ?>
        <div class="com-users-reset__submit control-group">
            <div class="controls">
                <button type="submit" class="btn btn-primary validate">
                    <?php echo Text::_('JSUBMIT'); ?>
                </button>
            </div>
        </div>
        <?php echo HTMLHelper::_('form.token'); ?>
    </form>
</div>
