<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var \Joomla\Component\Users\Administrator\View\Level\HtmlView $this */

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate');
?>

<form action="<?php echo Route::_('index.php?option=com_users&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="level-form" aria-label="<?php echo Text::_('COM_USERS_LEVEL_FORM_' . ((int) $this->item->id === 0 ? 'NEW' : 'EDIT'), true); ?>" class="form-validate main-card">
    <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'groups', 'recall' => true, 'breakpoint' => 768]); ?>
        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'groups', Text::_('COM_USERS_USERGROUP_DETAILS')); ?>
            <?php echo $this->form->renderField('title'); ?>
            <fieldset class="options-form">
                <legend><?php echo Text::_('COM_USERS_USER_GROUPS_HAVING_ACCESS'); ?></legend>
                <?php echo HTMLHelper::_('access.usergroups', 'jform[rules]', $this->item->rules, true); ?>
            </fieldset>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>
    <?php echo HTMLHelper::_('uitab.endTabSet'); ?>

    <?php echo $this->form->renderControlFields(); ?>
</form>
