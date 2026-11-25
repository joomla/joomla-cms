<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  mod_multilangstatus
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$hideLinks = $app->getInput()->getBool('hidemainmenu');

if (!$multilanguageEnabled || $hideLinks) {
    return;
}

$app->getDocument()->getWebAssetManager()->useScript('joomla.dialog-autocreate');

$popupOptions = [
    'popupType'  => 'iframe',
    'src'        => Route::_('index.php?option=com_languages&view=multilangstatus&tmpl=component', false),
    'textHeader' => Text::_('MOD_MULTILANGSTATUS'),
];

?>
<button type="button" class="header-item-content multilanguage" title="<?php echo htmlspecialchars(Text::_('MOD_MULTILANGSTATUS')); ?>"
        data-joomla-dialog="<?php echo htmlspecialchars(json_encode($popupOptions, JSON_UNESCAPED_SLASHES)) ?>">
    <div class="header-item-icon">
        <span class="icon-language" aria-hidden="true"></span>
    </div>
    <div class="header-item-text">
        <?php echo Text::_('MOD_MULTILANGSTATUS'); ?>
    </div>
</button>
