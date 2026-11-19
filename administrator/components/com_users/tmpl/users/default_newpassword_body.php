<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

/** @var \Joomla\Component\Users\Administrator\View\Users\HtmlView $this */

$fieldDefaults = [
    'class'         => '',
    'onchange'      => '',
    'autofocus'     => false,
    'disabled'      => false,
    'readonly'      => false,
    'required'      => false,
    'dataAttribute' => '',
];

$modeField = array_merge($fieldDefaults, [
    'id'      => 'newpassword-mode',
    'name'    => 'passwordMode',
    'label'   => Text::_('COM_USERS_USERS_SEND_NEW_PASSWORD_MODE'),
    'class'   => 'btn-group',
    'value'   => 'password',
    'options' => [
        HTMLHelper::_('select.option', 'password', Text::_('COM_USERS_USERS_SEND_NEW_PASSWORD_MODE_PASSWORD')),
        HTMLHelper::_('select.option', 'link', Text::_('COM_USERS_USERS_SEND_NEW_PASSWORD_MODE_LINK')),
    ],
]);

$requireResetField = array_merge($fieldDefaults, [
    'id'      => 'newpassword-require-reset',
    'name'    => 'requireReset',
    'label'   => Text::_('COM_USERS_REQUIRE_PASSWORD_RESET'),
    'value'   => '1',
    'options' => [
        HTMLHelper::_('select.option', '0', Text::_('JNO')),
        HTMLHelper::_('select.option', '1', Text::_('JYES')),
    ],
]);

$showOn = fn (string $mode): string => $this->escape(json_encode(FormHelper::parseShowOnConditions('passwordMode:' . $mode)));

$modeDescription = '<span data-showon="' . $showOn('password') . '">' . Text::_('COM_USERS_USERS_SEND_NEW_PASSWORD_MODE_PASSWORD_DESC') . '</span>'
    . '<span data-showon="' . $showOn('link') . '">' . Text::_('COM_USERS_USERS_SEND_NEW_PASSWORD_MODE_LINK_DESC') . '</span>';

?>

<div class="p-3">
    <p><?php echo Text::_('COM_USERS_USERS_CONFIRM_SEND_NEW_PASSWORD'); ?></p>
    <?php echo LayoutHelper::render('joomla.form.renderfield', [
        'name'        => $modeField['name'],
        'label'       => LayoutHelper::render('joomla.form.renderlabel', ['text' => $modeField['label'], 'for' => $modeField['id'], 'required' => false, 'classes' => []]),
        'input'       => LayoutHelper::render('joomla.form.field.radio.buttons', $modeField),
        'description' => $modeDescription,
        'options'     => ['showonEnabled' => true],
    ]); ?>
    <?php echo LayoutHelper::render('joomla.form.renderfield', [
        'name'    => $requireResetField['name'],
        'label'   => LayoutHelper::render('joomla.form.renderlabel', ['text' => $requireResetField['label'], 'for' => $requireResetField['id'], 'required' => false, 'classes' => []]),
        'input'   => LayoutHelper::render('joomla.form.field.radio.switcher', $requireResetField),
        'options' => ['showonEnabled' => true, 'rel' => ' data-showon="' . $showOn('password') . '"'],
    ]); ?>
</div>
<div class="btn-toolbar p-3">
    <joomla-toolbar-button task="users.sendNewPassword" class="ms-auto">
        <button type="button" class="btn btn-danger"><?php echo Text::_('COM_USERS_TOOLBAR_SEND_NEW_PASSWORD'); ?></button>
    </joomla-toolbar-button>
</div>
