<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var Joomla\Component\Users\Administrator\View\Mail\HtmlView $this */

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate');

$comUserParams = ComponentHelper::getParams('com_users');
?>

<form action="<?php echo Route::_('index.php?option=com_users&view=mail'); ?>" name="adminForm" method="post" id="mail-form" aria-label="<?php echo Text::_('COM_USERS_MASSMAIL_FORM_NEW'); ?>" class="main-card p-4 form-validate">
    <div class="row">
        <div class="col-lg-9">
            <div class="control-group">
                <?php echo $this->form->getLabel('subject'); ?>
                <span class="input-group">
                    <?php if (!empty($comUserParams->get('mailSubjectPrefix'))) : ?>
                        <span class="input-group-text"><?php echo $comUserParams->get('mailSubjectPrefix'); ?></span>
                    <?php endif; ?>
                    <?php echo $this->form->getInput('subject'); ?>
                </span>
            </div>
            <div class="control-group">
                <?php echo $this->form->getLabel('message'); ?>
                <?php echo $this->form->getInput('message'); ?>
                <?php if (!empty($comUserParams->get('mailBodySuffix'))) : ?>
                    <div class="mt-1 card">
                        <div class="card-body">
                            <?php echo $comUserParams->get('mailBodySuffix'); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-lg-3">
            <?php echo $this->form->renderField('recurse'); ?>
            <?php echo $this->form->renderField('mode'); ?>
            <?php echo $this->form->renderField('disabled'); ?>
            <?php echo $this->form->renderField('bcc'); ?>
            <?php echo $this->form->renderField('group'); ?>
        </div>
    </div>
    <input type="hidden" name="task" value="">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
