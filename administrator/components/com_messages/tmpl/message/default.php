<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_messages
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Mail\MailHelper;
use Joomla\CMS\Router\Route;

/** @var \Joomla\Component\Messages\Administrator\View\Message\HtmlView $this */

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('core');

?>
<form action="<?php echo Route::_('index.php?option=com_messages'); ?>" method="post" name="adminForm" id="adminForm">
    <fieldset>
        <input type="hidden" name="task" value="">
        <input type="hidden" name="reply_id" value="<?php echo $this->item->message_id; ?>">
        <?php echo HTMLHelper::_('form.token'); ?>
    </fieldset>
</form>
<div class="card">
    <div class="card-body">
        <dl class="mb-0">
            <dt><?php echo Text::_('COM_MESSAGES_FIELD_USER_ID_FROM_LABEL'); ?></dt>
            <dd><?php echo $this->item->get('from_user_name'); ?></dd>

            <dt><?php echo Text::_('COM_MESSAGES_FIELD_DATE_TIME_LABEL'); ?></dt>
            <dd><?php echo HTMLHelper::_('date', $this->item->date_time, Text::_('DATE_FORMAT_LC2')); ?></dd>

            <dt><?php echo Text::_('COM_MESSAGES_FIELD_SUBJECT_LABEL'); ?></dt>
            <dd><?php echo $this->item->subject; ?></dd>

            <dt><?php echo Text::_('COM_MESSAGES_FIELD_MESSAGE_LABEL'); ?></dt>
            <dd><?php echo MailHelper::convertRelativeToAbsoluteUrls($this->item->message); ?></dd>
        </dl>
    </div>
</div>
