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

/** @var \Joomla\Component\Users\Site\View\Profile\HtmlView $this */
?>
<fieldset id="users-profile-core" class="com-users-profile__core">
    <legend>
        <?php echo Text::_('COM_USERS_PROFILE_CORE_LEGEND'); ?>
    </legend>
    <dl class="dl-horizontal">
        <dt>
            <?php echo Text::_('COM_USERS_PROFILE_NAME_LABEL'); ?>
        </dt>
        <dd>
            <?php echo $this->escape($this->data->name); ?>
        </dd>
        <dt>
            <?php echo Text::_('COM_USERS_PROFILE_USERNAME_LABEL'); ?>
        </dt>
        <dd>
            <?php echo $this->escape($this->data->username); ?>
        </dd>
        <dt>
            <?php echo Text::_('COM_USERS_PROFILE_REGISTERED_DATE_LABEL'); ?>
        </dt>
        <dd>
            <?php echo HTMLHelper::_('date', $this->data->registerDate, Text::_('DATE_FORMAT_LC1')); ?>
        </dd>
        <dt>
            <?php echo Text::_('COM_USERS_PROFILE_LAST_VISITED_DATE_LABEL'); ?>
        </dt>
        <?php if ($this->data->lastvisitDate !== null) : ?>
            <dd>
                <?php echo HTMLHelper::_('date', $this->data->lastvisitDate, Text::_('DATE_FORMAT_LC1')); ?>
            </dd>
        <?php else : ?>
            <dd>
                <?php echo Text::_('COM_USERS_PROFILE_NEVER_VISITED'); ?>
            </dd>
        <?php endif; ?>
    </dl>
</fieldset>
