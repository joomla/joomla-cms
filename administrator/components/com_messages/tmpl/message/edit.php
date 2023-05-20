<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_messages
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate');

?>
<form action="<?php echo Route::_('index.php?option=com_messages'); ?>" method="post" name="adminForm" id="message-form" aria-label="<?php echo Text::_('COM_MESSAGES_FORM_NEW'); ?>" class="form-validate">
    <div class="adminform mt-2">
        <div class="card">
            <div class="card-body">
                <div class="form-group">
                    <?php echo $this->form->getLabel('user_id_to'); ?>
                    <?php echo $this->form->getInput('user_id_to'); ?>
                </div>
                <div class="form-group">
                    <?php echo $this->form->getLabel('subject'); ?>
                    <?php echo $this->form->getInput('subject'); ?>
                </div>
                <div class="form-group">
                    <?php echo $this->form->getLabel('message'); ?>
                    <?php echo $this->form->getInput('message'); ?>
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" name="task" value="">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
