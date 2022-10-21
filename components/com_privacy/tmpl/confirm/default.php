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

/** @var \Joomla\Component\Privacy\Site\View\Confirm\HtmlView $this */

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('behavior.formvalidator');

?>
<div class="request-confirm<?php echo $this->pageclass_sfx; ?>">
    <?php if ($this->params->get('show_page_heading')) : ?>
        <div class="page-header">
            <h1>
                <?php echo $this->escape($this->params->get('page_heading')); ?>
            </h1>
        </div>
    <?php endif; ?>
    <form action="<?php echo Route::_('index.php?option=com_privacy&task=request.confirm'); ?>" method="post" class="form-validate form-horizontal well">
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
</div>
