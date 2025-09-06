<?php

/**
 * @package     Joomla.Site
 * @subpackage  Templates.cassiopeia
 *
 * @copyright   (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') || die;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

extract($displayData);

$app = Factory::getApplication();
$extraButtons = $app->get('offline_buttons', []);

?>
<div class="outer">
    <div class="offline-card">
        <div class="header">
            <?php if (!empty($doc->params->logo)) : ?>
                <h1><?php echo $doc->params->logo; ?></h1>
            <?php else : ?>
                <h1><?php echo $doc->params->sitename; ?></h1>
            <?php endif; ?>
            <?php if ($app->get('offline_image')) : ?>
                <?php echo HTMLHelper::_('image', $app->get('offline_image'), $doc->params->sitename, [], false, 0); ?>
            <?php endif; ?>
            <?php if ($app->get('display_offline_message', 1) == 1 && str_replace(' ', '', $app->get('offline_message')) != '') : ?>
                <p><?php echo $app->get('offline_message'); ?></p>
            <?php elseif ($app->get('display_offline_message', 1) == 2) : ?>
                <p><?php echo Text::_('JOFFLINE_MESSAGE'); ?></p>
            <?php endif; ?>
            <div class="logo-icon">
                <svg version="1.1" xmlns="https://www.w3.org/2000/svg" x="0px" y="0px"
                    viewBox="0 0 74.8 74.8" enable-background="new 0 0 74.8 74.8" xml:space="preserve">
                    <g id="brandmark">
                        <path id="j-green" fill="#1C3D5C" d="M13.5,37.7L12,36.3c-4.5-4.5-5.8-10.8-4.2-16.5c-4.5-1-7.8-5-7.8-9.8c0-5.5,4.5-10,10-10 c5,0,9.1,3.6,9.9,8.4c5.4-1.3,11.3,0.2,15.5,4.4l0.6,0.6l-7.4,7.4l-0.6-0.6c-2.4-2.4-6.3-2.4-8.7,0c-2.4,2.4-2.4,6.3,0,8.7l1.4,1.4 l7.4,7.4l7.8,7.8l-7.4,7.4l-7.8-7.8L13.5,37.7L13.5,37.7z" />
                        <path id="j-orange" fill="#1C3D5C" d="M21.8,29.5l7.8-7.8l7.4-7.4l1.4-1.4C42.9,8.4,49.2,7,54.8,8.6C55.5,3.8,59.7,0,64.8,0 c5.5,0,10,4.5,10,10c0,5.1-3.8,9.3-8.7,9.9c1.6,5.6,0.2,11.9-4.2,16.3l-0.6,0.6l-7.4-7.4l0.6-0.6c2.4-2.4,2.4-6.3,0-8.7 c-2.4-2.4-6.3-2.4-8.7,0l-1.4,1.4L37,29l-7.8,7.8L21.8,29.5L21.8,29.5z" />
                        <path id="j-red" fill="#1C3D5C" d="M55,66.8c-5.7,1.7-12.1,0.4-16.6-4.1l-0.6-0.6l7.4-7.4l0.6,0.6c2.4,2.4,6.3,2.4,8.7,0 c2.4-2.4,2.4-6.3,0-8.7L53,45.1l-7.4-7.4l-7.8-7.8l7.4-7.4l7.8,7.8l7.4,7.4l1.5,1.5c4.2,4.2,5.7,10.2,4.4,15.7 c4.9,0.7,8.6,4.9,8.6,9.9c0,5.5-4.5,10-10,10C60,74.8,56,71.3,55,66.8L55,66.8z" />
                        <path id="j-blue" fill="#1C3D5C" d="M52.2,46l-7.8,7.8L37,61.2l-1.4,1.4c-4.3,4.3-10.3,5.7-15.7,4.4c-1,4.5-5,7.8-9.8,7.8 c-5.5,0-10-4.5-10-10C0,60,3.3,56.1,7.7,55C6.3,49.5,7.8,43.5,12,39.2l0.6-0.6L20,46l-0.6,0.6c-2.4,2.4-2.4,6.3,0,8.7 c2.4,2.4,6.3,2.4,8.7,0l1.4-1.4l7.4-7.4l7.8-7.8L52.2,46L52.2,46z" />
                    </g>
                </svg>
            </div>
        </div>
        <div class="login">
            <jdoc:include type="message" />
            <form action="<?php echo Route::_('index.php', true); ?>" method="post" id="form-login">
                <fieldset>
                    <label for="username"><?php echo Text::_('JGLOBAL_USERNAME'); ?></label>
                    <input name="username" class="form-control" id="username" type="text">

                    <label for="password"><?php echo Text::_('JGLOBAL_PASSWORD'); ?></label>
                    <input name="password" class="form-control" id="password" type="password">

                        <?php foreach ($extraButtons as $button) :
                            $dataAttributeKeys = array_filter(array_keys($button), function ($key) {
                                return substr($key, 0, 5) == 'data-';
                            });
                            ?>
                        <div class="mod-login__submit form-group">
                            <button type="button"
                                class="btn btn-secondary w-100 mt-4 <?php echo $button['class'] ?? '' ?>"
                                <?php foreach ($dataAttributeKeys as $key) : ?>
                                    <?php echo $key ?>="<?php echo $button[$key] ?>"
                                <?php endforeach; ?>
                                <?php if ($button['onclick']) : ?>
                                    onclick="<?php echo $button['onclick'] ?>"
                                <?php endif; ?>
                                title="<?php echo Text::_($button['label']) ?>"
                                id="<?php echo $button['id'] ?>">
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

                    <button type="submit" name="Submit" class="btn btn-primary"><?php echo Text::_('JLOGIN'); ?></button>

                    <input type="hidden" name="option" value="com_users">
                    <input type="hidden" name="task" value="user.login">
                    <input type="hidden" name="return" value="<?php echo base64_encode(Uri::base()); ?>">
                    <?php echo HTMLHelper::_('form.token'); ?>
                </fieldset>
            </form>
        </div>
    </div>
</div>
