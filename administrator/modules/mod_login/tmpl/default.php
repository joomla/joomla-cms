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
?>
<form class="form-validate" action="<?php echo Route::_('index.php', true); ?>" method="post" id="form-login">
    <fieldset>
        <legend class="visually-hidden"><?php echo Text::_('MOD_LOGIN'); ?></legend>
        <div class="form-group">
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
        <div class="form-group">
            <label for="mod-login-password">
                <?php echo Text::_('JGLOBAL_PASSWORD'); ?>
            </label>
            <div class="input-group">

                <input
                    name="passwd"
                    id="mod-login-password"
                    type="password"
                    class="form-control input-full"
                    required="required"
                    autocomplete="current-password"
                >
                <button type="button" class="btn btn-primary input-password-toggle">
                    <span class="icon-eye icon-fw" aria-hidden="true"></span>
                    <span class="visually-hidden"><?php echo Text::_('JSHOWPASSWORD'); ?></span>
                </button>

            </div>
        </div>

        <div class="mt-4">
            <?php if (!empty($langs)) : ?>
                <div class="form-group">
                    <label for="lang">
                        <?php echo Text::_('MOD_LOGIN_LANGUAGE'); ?>
                    </label>
                    <?php echo $langs; ?>
                </div>
            <?php endif; ?>
            <?php foreach ($extraButtons as $button) :
                $dataAttributeKeys = array_filter(array_keys($button), function ($key) {
                    return substr($key, 0, 5) == 'data-';
                });
                ?>
            <div class="form-group">
                <button type="button"
                        class="btn btn-secondary w-100 <?php echo $button['class'] ?? '' ?>"
                        <?php foreach ($dataAttributeKeys as $key) : ?>
                            <?php echo $key ?>="<?php echo $button[$key] ?>"
                        <?php endforeach; ?>
                        <?php if ($button['onclick']) : ?>
                        onclick="<?php echo $button['onclick'] ?>"
                        <?php endif; ?>
                        title="<?php echo Text::_($button['label']) ?>"
                        id="<?php echo $button['id'] ?>"
                >
                    <?php if (!empty($button['icon'])) : ?>
                        <span class="<?php echo $button['icon'] ?>"></span>
                    <?php elseif (!empty($button['image'])) : ?>
                        <?php echo $button['image']; ?>
                    <?php elseif (!empty($button['svg'])) : ?>
                        <?php echo $button['svg']; ?>
                    <?php endif; ?>
                    <?php echo Text::_($button['label']) ?>
                </button>
            </div>
            <?php endforeach; ?>
            <div class="form-group">
                <button type="submit" id="btn-login-submit" class="btn btn-primary w-100 btn-lg"><?php echo Text::_('JLOGIN'); ?></button>
            </div>
            <input type="hidden" name="option" value="com_login">
            <input type="hidden" name="task" value="login">
            <input type="hidden" name="return" value="<?php echo $return; ?>">
            <?php echo HTMLHelper::_('form.token'); ?>
        </div>
    </fieldset>
</form>
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
