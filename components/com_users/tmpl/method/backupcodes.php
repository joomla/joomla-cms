<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Prevent direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Component\Users\Site\View\Method\HtmlView;

/** @var  HtmlView $this */

HTMLHelper::_('bootstrap.tooltip', '.hasTooltip');

$cancelURL = Route::_('index.php?option=com_users&task=methods.display&user_id=' . $this->user->id);

if (!empty($this->returnURL)) {
    $cancelURL = $this->escape(base64_decode($this->returnURL));
}

if ($this->record->method != 'backupcodes') {
    throw new RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
}

?>
<h2>
    <?php echo Text::_('COM_USERS_USER_BACKUPCODES') ?>
</h2>

<div class="alert alert-info">
    <?php echo Text::_('COM_USERS_USER_BACKUPCODES_DESC') ?>
</div>

<table class="table table-striped">
    <?php for ($i = 0; $i < (count($this->backupCodes) / 2); $i++) : ?>
        <tr>
            <td>
                <?php if (!empty($this->backupCodes[2 * $i])) : ?>
                    <?php // This is a Key emoji; we can hide it from screen readers ?>
                    <span aria-hidden="true">&#128273;</span>
                    <?php echo $this->backupCodes[2 * $i] ?>
                <?php endif; ?>
            </td>
            <td>
                <?php if (!empty($this->backupCodes[1 + 2 * $i])) : ?>
                    <?php // This is a Key emoji; we can hide it from screen readers ?>
                    <span aria-hidden="true">&#128273;</span>
                    <?php echo $this->backupCodes[1 + 2 * $i] ?>
                <?php endif ;?>
            </td>
        </tr>
    <?php endfor; ?>
</table>

<p>
    <?php echo Text::_('COM_USERS_MFA_BACKUPCODES_RESET_INFO'); ?>
</p>

<a class="btn btn-danger" href="<?php echo Route::_(sprintf("index.php?option=com_users&task=method.regenerateBackupCodes&user_id=%s&%s=1%s", $this->user->id, Factory::getApplication()->getFormToken(), empty($this->returnURL) ? '' : '&returnurl=' . $this->returnURL)) ?>">
    <span class="icon icon-refresh" aria-hidden="true"></span>
    <?php echo Text::_('COM_USERS_MFA_BACKUPCODES_RESET'); ?>
</a>

<a href="<?php echo $cancelURL ?>"
   class="btn btn-secondary">
    <span class="icon icon-cancel-2 icon-ban-circle"></span>
    <?php echo Text::_('JCANCEL'); ?>
</a>
