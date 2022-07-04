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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Component\Users\Site\View\Methods\HtmlView;

/** @var HtmlView $this */
?>
<div id="com-users-methods-list">
    <?php if (!$this->get('forHMVC', false)) : ?>
        <h2 id="com-users-methods-list-head">
            <?php echo Text::_('COM_USERS_MFA_LIST_PAGE_HEAD'); ?>
        </h2>
    <?php endif ?>

    <div id="com-users-methods-reset-container" class="d-flex align-items-center border border-1 rounded-3 p-2 bg-light">
        <div id="com-users-methods-reset-message" class="flex-grow-1">
            <?php echo Text::_('COM_USERS_MFA_LIST_STATUS_' . ($this->mfaActive ? 'ON' : 'OFF')) ?>
        </div>
        <?php if ($this->mfaActive) : ?>
            <div>
                <a href="<?php echo Route::_('index.php?option=com_users&task=methods.disable&' . Factory::getApplication()->getFormToken() . '=1' . ($this->returnURL ? '&returnurl=' . $this->escape(urlencode($this->returnURL)) : '') . '&user_id=' . $this->user->id) ?>"
                   class="btn btn-danger btn-sm">
                    <?php echo Text::_('COM_USERS_MFA_LIST_REMOVEALL'); ?>
                </a>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!count($this->methods)) : ?>
        <div id="com-users-methods-list-instructions" class="alert alert-info mt-2">
            <span class="icon icon-info-circle" aria-hidden="true"></span>
            <?php echo Text::_('COM_USERS_MFA_LIST_INSTRUCTIONS'); ?>
        </div>
    <?php elseif ($this->isMandatoryMFASetup) : ?>
        <div class="alert alert-info my-3">
            <h3 class="alert-heading">
                <?php echo Text::_('COM_USERS_MFA_MANDATORY_NOTICE_HEAD') ?>
            </h3>
            <p>
                <?php echo Text::_('COM_USERS_MFA_MANDATORY_NOTICE_BODY') ?>
            </p>
        </div>
    <?php endif ?>

    <?php $this->setLayout('list');
    echo $this->loadTemplate(); ?>
</div>
