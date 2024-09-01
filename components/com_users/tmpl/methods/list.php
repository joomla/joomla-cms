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
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Users\Administrator\Helper\Mfa as MfaHelper;
use Joomla\Component\Users\Site\Model\MethodsModel;
use Joomla\Component\Users\Site\View\Methods\HtmlView;

/** @var HtmlView $this */

/** @var MethodsModel $model */
$model = $this->getModel();

$this->document->getWebAssetManager()->useScript('com_users.two-factor-list');

HTMLHelper::_('bootstrap.tooltip', '.hasTooltip');

$canAddEdit = MfaHelper::canAddEditMethod($this->user);
$canDelete  = MfaHelper::canDeleteMethod($this->user);
?>
<div id="com-users-methods-list-container">
    <?php foreach ($this->methods as $methodName => $method) :
        $methodClass = 'com-users-methods-list-method-name-' . htmlentities($method['name'])
            . ($this->defaultMethod == $methodName ? ' com-users-methods-list-method-default' : '');
        ?>
        <div class="com-users-methods-list-method <?php echo $methodClass?> mx-1 my-3 card <?php echo count($method['active']) ? 'border-secondary' : '' ?>">
            <div class="com-users-methods-list-method-header card-header <?php echo count($method['active']) ? 'border-secondary bg-secondary text-white' : '' ?> d-flex flex-wrap align-items-center gap-2">
                <div class="com-users-methods-list-method-image pt-1 px-3 pb-2 bg-light rounded-2">
                    <img src="<?php echo Uri::root() . $method['image'] ?>"
                         alt="<?php echo $this->escape($method['display']) ?>"
                         class="img-fluid"
                    >
                </div>
                <div class="com-users-methods-list-method-title flex-grow-1 d-flex flex-column">
                    <h2 class="h4 p-0 m-0 d-flex gap-3 align-items-center">
                        <span class="me-1 flex-grow-1">
                            <?php echo $method['display'] ?>
                        </span>
                        <?php if ($this->defaultMethod == $methodName) : ?>
                            <span id="com-users-methods-list-method-default-tag" class="badge bg-info me-1 fs-6">
                                <?php echo Text::_('COM_USERS_MFA_LIST_DEFAULTTAG') ?>
                            </span>
                        <?php endif; ?>
                    </h2>
                </div>
            </div>

            <div class="com-users-methods-list-method-records-container card-body">
                <div class="com-users-methods-list-method-info my-1 pb-1 small text-muted">
                    <?php echo $method['shortinfo'] ?>
                </div>

                <?php if (count($method['active'])) : ?>
                    <div class="com-users-methods-list-method-records pt-2 my-2">
                        <?php foreach ($method['active'] as $record) : ?>
                            <div class="com-users-methods-list-method-record d-flex flex-row flex-wrap justify-content-start border-top py-2">
                                <div class="com-users-methods-list-method-record-info flex-grow-1 d-flex flex-column align-items-start gap-1">
                                    <?php if ($methodName === 'backupcodes' && $canAddEdit) : ?>
                                        <div class="alert alert-info mt-1 w-100">
                                            <?php echo Text::sprintf('COM_USERS_MFA_BACKUPCODES_PRINT_PROMPT_HEAD', Route::_('index.php?option=com_users&task=method.edit&id=' . (int) $record->id . ($this->returnURL ? '&returnurl=' . $this->escape(urlencode($this->returnURL)) : '') . '&user_id=' . $this->user->id), 'text-decoration-underline') ?>
                                        </div>
                                    <?php else : ?>
                                        <h3 class="com-users-methods-list-method-record-title-container mb-1 fs-5">
                                            <?php if ($record->default) : ?>
                                                <span id="com-users-methods-list-method-default-badge-small"
                                                      class="text-warning me-1 hasTooltip"
                                                      title="<?php echo $this->escape(Text::_('COM_USERS_MFA_LIST_DEFAULTTAG')) ?>">
                                                    <span class="icon icon-star" aria-hidden="true"></span>
                                                    <span class="visually-hidden"><?php echo $this->escape(Text::_('COM_USERS_MFA_LIST_DEFAULTTAG')) ?></span>
                                                </span>
                                            <?php endif; ?>
                                            <span class="com-users-methods-list-method-record-title fw-bold">
                                                <?php echo $this->escape($record->title); ?>
                                            </span>
                                        </h3>
                                    <?php endif; ?>

                                    <div class="com-users-methods-list-method-record-lastused my-1 d-flex flex-row flex-wrap justify-content-start gap-5 text-muted small w-100">
                                        <span class="com-users-methods-list-method-record-createdon">
                                            <?php echo Text::sprintf('COM_USERS_MFA_LBL_CREATEDON', $model->formatRelative($record->created_on)) ?>
                                        </span>
                                        <span class="com-users-methods-list-method-record-lastused-date">
                                            <?php echo Text::sprintf('COM_USERS_MFA_LBL_LASTUSED', $model->formatRelative($record->last_used)) ?>
                                        </span>
                                    </div>

                                </div>

                                <?php if ($methodName !== 'backupcodes' && ($canAddEdit || $canDelete)) : ?>
                                <div class="com-users-methods-list-method-record-actions my-2 d-flex flex-row flex-wrap justify-content-center align-content-center align-items-start">
                                    <?php if ($canAddEdit) : ?>
                                    <a class="com-users-methods-list-method-record-edit btn btn-secondary btn-sm mx-1 hasTooltip"
                                       href="<?php echo Route::_('index.php?option=com_users&task=method.edit&id=' . (int) $record->id . ($this->returnURL ? '&returnurl=' . $this->escape(urlencode($this->returnURL)) : '') . '&user_id=' . $this->user->id)?>"
                                       title="<?php echo Text::_('JACTION_EDIT') ?> <?php echo $this->escape($record->title); ?>">
                                        <span class="icon icon-pencil" aria-hidden="true"></span>
                                        <span class="visually-hidden"><?php echo Text::_('JACTION_EDIT') ?> <?php echo $this->escape($record->title); ?></span>
                                    </a>
                                    <?php endif ?>

                                    <?php if ($method['canDisable'] && $canDelete) : ?>
                                    <a class="com-users-methods-list-method-record-delete btn btn-danger btn-sm mx-1 hasTooltip"
                                       href="<?php echo Route::_('index.php?option=com_users&task=method.delete&id=' . (int) $record->id . ($this->returnURL ? '&returnurl=' . $this->escape(urlencode($this->returnURL)) : '') . '&user_id=' . $this->user->id . '&' . Factory::getApplication()->getFormToken() . '=1')?>"
                                       title="<?php echo Text::_('JACTION_DELETE') ?> <?php echo $this->escape($record->title); ?>">
                                        <span class="icon icon-trash" aria-hidden="true"></span>
                                        <span class="visually-hidden"><?php echo Text::_('JACTION_DELETE') ?> <?php echo $this->escape($record->title); ?></span>
                                    </a>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ($canAddEdit && (empty($method['active']) || $method['allowMultiple'])) : ?>
                    <div class="com-users-methods-list-method-addnew-container border-top pt-2">
                        <a href="<?php echo Route::_('index.php?option=com_users&task=method.add&method=' . $this->escape(urlencode($method['name'])) . ($this->returnURL ? '&returnurl=' . $this->escape(urlencode($this->returnURL)) : '') . '&user_id=' . $this->user->id)?>"
                           class="com-users-methods-list-method-addnew btn btn-outline-primary btn-sm"
                        >
                            <span class="icon-plus-2" aria-hidden="true"></span>
                            <?php echo Text::sprintf('COM_USERS_MFA_ADD_AUTHENTICATOR_OF_TYPE', $method['display']) ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
