<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  mod_login
 *
 * @copyright   (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $app->getDocument()->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('field.passwordview')
    ->registerAndUseScript('mod_login.admin', 'mod_login/admin-login.min.js', [], ['defer' => true], ['core', 'form.validate']);

Text::script('JSHOWPASSWORD');
Text::script('JHIDEPASSWORD');
Text::script('MOD_LOGIN_USERNAME_REQUIRED_FOR_PASSKEY');

// Identify passkey button from extraButtons (by id/class/onclick containing passkey/webauthn/passwordless)
$passkeyButton = null;
$otherButtons = [];

if (!empty($extraButtons)) {
    foreach ($extraButtons as $button) {
        $isPasskey = false;

        // Check id
        if (!empty($button['id']) && preg_match('/passkey|webauthn|passwordless/i', $button['id'])) {
            $isPasskey = true;
        }

        // Check class
        if (!$isPasskey && !empty($button['class']) && preg_match('/passkey|webauthn|passwordless/i', $button['class'])) {
            $isPasskey = true;
        }

        // Check onclick
        if (!$isPasskey && !empty($button['onclick']) && preg_match('/passkey|webauthn|passwordless/i', $button['onclick'])) {
            $isPasskey = true;
        }

        // Check label
        if (!$isPasskey && !empty($button['label']) && preg_match('/passkey|webauthn|passwordless/i', $button['label'])) {
            $isPasskey = true;
        }

        if ($isPasskey && $passkeyButton === null) {
            $passkeyButton = $button;
        } else {
            $otherButtons[] = $button;
        }
    }
}
?>
<form class="form-validate" action="<?php echo Route::_('index.php', true); ?>" method="post" id="form-login">
    <fieldset>
        <legend class="visually-hidden"><?php echo Text::_('MOD_LOGIN'); ?></legend>

        <div class="form-group login-username">
            <label for="mod-login-username">
                <?php echo Text::_('JGLOBAL_USERNAME'); ?>
            </label>
            <div class="input-group">
                <input
                    name="username"
                    id="mod-login-username"
                    type="text"
                    class="form-control"
                    required="required"
                    autofocus
                    autocomplete="username"
                >
            </div>
        </div>

        <?php if ($passkeyButton) : ?>
            <div class="login-methods form-group">
                <button type="button"
                    id="btn-login-password"
                    class="btn btn-primary"
                    aria-controls="login-password-section">
                    <span class="icon-key icon-fw" aria-hidden="true"></span>
                    <?php echo Text::_('MOD_LOGIN_CONTINUE_WITH_PASSWORD'); ?>
                </button>

                <?php
                $dataAttributeKeys = array_filter(array_keys($passkeyButton), function ($key) {
                    return substr($key, 0, 5) == 'data-';
                });
                ?>
                <button type="button"
                        class="btn btn-secondary <?php echo htmlspecialchars($passkeyButton['class'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                        data-is-passkey="true"
                    <?php foreach ($dataAttributeKeys as $key) : ?>
                        <?php echo htmlspecialchars($key, ENT_QUOTES, 'UTF-8'); ?>="<?php echo htmlspecialchars($passkeyButton[$key], ENT_QUOTES, 'UTF-8'); ?>"
                    <?php endforeach; ?>
                    <?php if (!empty($passkeyButton['onclick'])) : ?>
                        onclick="<?php echo htmlspecialchars($passkeyButton['onclick'], ENT_QUOTES, 'UTF-8'); ?>"
                    <?php endif; ?>
                    title="<?php echo Text::_('MOD_LOGIN_CONTINUE_WITH_PASSKEY'); ?>"
                    id="<?php echo htmlspecialchars($passkeyButton['id'], ENT_QUOTES, 'UTF-8'); ?>"
                >
                    <?php if (!empty($passkeyButton['icon'])) : ?>
                        <span class="<?php echo htmlspecialchars($passkeyButton['icon'], ENT_QUOTES, 'UTF-8'); ?>" aria-hidden="true"></span>
                    <?php elseif (!empty($passkeyButton['image'])) : ?>
                        <?php echo $passkeyButton['image']; ?>
                    <?php elseif (!empty($passkeyButton['svg'])) : ?>
                        <?php echo $passkeyButton['svg']; ?>
                    <?php endif; ?>
                    <?php echo Text::_('MOD_LOGIN_CONTINUE_WITH_PASSKEY') ?>
                </button>
            </div>
        <?php endif; ?>

        <div id="login-password-section" class="login-password form-group">
            <label for="mod-login-password">
                <?php echo Text::_('JGLOBAL_PASSWORD'); ?>
            </label>
            <div class="input-group">
                <input
                    name="passwd"
                    id="mod-login-password"
                    type="password"
                    class="form-control input-full"
                    autocomplete="current-password"
                    required
                >
                <button type="button" class="btn btn-primary input-password-toggle">
                    <span class="icon-eye icon-fw" aria-hidden="true"></span>
                    <span class="visually-hidden"><?php echo Text::_('JSHOWPASSWORD'); ?></span>
                </button>
            </div>
        </div>

        <div class="login-submit form-group">
            <button type="submit" id="btn-login-submit" class="btn btn-primary w-100 btn-lg">
                <?php echo Text::_('JLOGIN'); ?>
            </button>
        </div>

        <div class="text-center">
            <div>
                <?php echo HTMLHelper::link(
                    Text::_('MOD_LOGIN_CREDENTIALS_LINK'),
                    Text::_('MOD_LOGIN_CREDENTIALS'),
                    [
                        'target' => '_blank',
                        'rel'    => 'noopener nofollow',
                        'title'  => Text::sprintf('JBROWSERTARGET_NEW_TITLE', Text::_('MOD_LOGIN_CREDENTIALS'))
                    ]
                ); ?>
            </div>
        </div>

        <?php if (!empty($otherButtons)) : ?>
            <div class="login-other-methods mt-4">
                <hr>
                <p class="text-center fw-bold"><?php echo Text::_('MOD_LOGIN_OR'); ?></p>

                <?php foreach ($otherButtons as $button) :
                    $dataAttributeKeys = array_filter(array_keys($button), function ($key) {
                        return substr($key, 0, 5) == 'data-';
                    });
                    ?>
                    <div class="form-group">
                        <button type="button"
                                class="btn btn-secondary w-100 <?php echo htmlspecialchars($button['class'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                            <?php foreach ($dataAttributeKeys as $key) : ?>
                                <?php echo htmlspecialchars($key, ENT_QUOTES, 'UTF-8'); ?>="<?php echo htmlspecialchars($button[$key], ENT_QUOTES, 'UTF-8'); ?>"
                            <?php endforeach; ?>
                            <?php if (!empty($button['onclick'])) : ?>
                                onclick="<?php echo htmlspecialchars($button['onclick'], ENT_QUOTES, 'UTF-8'); ?>"
                            <?php endif; ?>
                            title="<?php echo Text::_($button['label']); ?>"
                            id="<?php echo htmlspecialchars($button['id'], ENT_QUOTES, 'UTF-8'); ?>"
                        >
                            <?php if (!empty($button['icon'])) : ?>
                                <span class="<?php echo htmlspecialchars($button['icon'], ENT_QUOTES, 'UTF-8'); ?>" aria-hidden="true"></span>
                            <?php elseif (!empty($button['image'])) : ?>
                                <?php echo $button['image']; ?>
                            <?php elseif (!empty($button['svg'])) : ?>
                                <?php echo $button['svg']; ?>
                            <?php endif; ?>
                            <?php echo Text::_($button['label']); ?>
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($langs)) : ?>
            <div class="login-language mt-4">
                <div class="form-group">
                    <label for="lang">
                        <?php echo Text::_('MOD_LOGIN_LANGUAGE'); ?>
                    </label>
                    <?php echo $langs; ?>
                </div>
            </div>
        <?php endif; ?>

        <input type="hidden" name="option" value="com_login">
        <input type="hidden" name="task" value="login">
        <input type="hidden" name="return" value="<?php echo htmlspecialchars($return, ENT_QUOTES, 'UTF-8'); ?>">
        <?php echo HTMLHelper::_('form.token'); ?>
    </fieldset>
</form>
