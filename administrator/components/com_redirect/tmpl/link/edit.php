<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_redirect
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var \Joomla\Component\Redirect\Administrator\View\Link\HtmlView $this */

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate');

?>

<form action="<?php echo Route::_('index.php?option=com_redirect&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="link-form" aria-label="<?php echo Text::_('COM_REDIRECT_FORM_' . ((int) $this->item->id === 0 ? 'NEW' : 'EDIT'), true); ?>" class="form-validate">
    <fieldset class="main-card mt-3">
        <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'basic', 'recall' => true, 'breakpoint' => 768]); ?>

            <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'basic', empty($this->item->id) ? Text::_('COM_REDIRECT_NEW_LINK') : Text::sprintf('COM_REDIRECT_EDIT_LINK', $this->item->id)); ?>

            <?php echo $this->form->renderField('old_url'); ?>
            <?php echo $this->form->renderField('new_url'); ?>
            <?php echo $this->form->renderField('published'); ?>
            <?php echo $this->form->renderField('comment'); ?>
            <?php echo $this->form->renderField('id'); ?>
            <?php echo $this->form->renderField('created_date'); ?>
            <?php echo $this->form->renderField('modified_date'); ?>
            <?php if (ComponentHelper::getParams('com_redirect')->get('mode')) : ?>
                <?php echo $this->form->renderFieldset('advanced'); ?>
            <?php endif; ?>
            <?php echo HTMLHelper::_('uitab.endTab'); ?>
        <?php echo HTMLHelper::_('uitab.endTabSet'); ?>

        <input type="hidden" name="task" value="">
        <?php echo HTMLHelper::_('form.token'); ?>
    </fieldset>
</form>
