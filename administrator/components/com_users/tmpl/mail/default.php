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

Text::script('COM_USERS_MAIL_PLEASE_FILL_IN_THE_SUBJECT', true);
Text::script('COM_USERS_MAIL_PLEASE_SELECT_A_GROUP', true);
Text::script('COM_USERS_MAIL_PLEASE_FILL_IN_THE_MESSAGE', true);

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('com_users.admin-users-mail');

$comUserParams = ComponentHelper::getParams('com_users');
?>

<form class="main-card p-4" action="<?php echo Route::_('index.php?option=com_users&view=mail'); ?>" name="adminForm" method="post" id="adminForm" aria-label="<?php echo Text::_('COM_USERS_MASSMAIL_FORM_NEW'); ?>">
    <div class="row mt-2">
        <div class="col-md-9">
            <fieldset class="adminform">
                <div class="form-group">
                    <?php echo $this->form->getLabel('subject'); ?>
                    <span class="input-group">
                        <?php if (!empty($comUserParams->get('mailSubjectPrefix'))) : ?>
                            <span class="input-group-text"><?php echo $comUserParams->get('mailSubjectPrefix'); ?></span>
                        <?php endif; ?>
                        <?php echo $this->form->getInput('subject'); ?>
                    </span>
                </div>
                <div class="form-group">
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
            </fieldset>
            <input type="hidden" name="task" value="">
            <?php echo HTMLHelper::_('form.token'); ?>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <?php echo $this->form->getInput('recurse'); ?>
                <?php echo $this->form->getLabel('recurse'); ?>
            </div>
            <div class="form-group">
                <?php echo $this->form->getInput('mode'); ?>
                <?php echo $this->form->getLabel('mode'); ?>
            </div>
            <div class="form-group">
                <?php echo $this->form->getInput('disabled'); ?>
                <?php echo $this->form->getLabel('disabled'); ?>
            </div>
            <div class="form-group">
                <?php echo $this->form->getInput('bcc'); ?>
                <?php echo $this->form->getLabel('bcc'); ?>
            </div>
            <div class="form-group">
                <?php echo $this->form->getLabel('group'); ?>
                <?php echo $this->form->getInput('group'); ?>
            </div>
        </div>
    </div>
</form>
