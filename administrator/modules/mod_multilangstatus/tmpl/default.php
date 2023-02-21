<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  mod_multilangstatus
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$hideLinks = $app->getInput()->getBool('hidemainmenu');

if (!$multilanguageEnabled || $hideLinks) {
    return;
}

$modalHTML = HTMLHelper::_(
    'bootstrap.renderModal',
    'multiLangModal',
    [
        'title'      => Text::_('MOD_MULTILANGSTATUS'),
        'url'        => Route::_('index.php?option=com_languages&view=multilangstatus&tmpl=component'),
        'height'     => '400px',
        'width'      => '800px',
        'bodyHeight' => 70,
        'modalWidth' => 80,
        'footer'     => '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">' . Text::_('JTOOLBAR_CLOSE') . '</button>',
    ]
);

$app->getDocument()->getWebAssetManager()
    ->registerAndUseScript('mod_multilangstatus.admin', 'mod_multilangstatus/admin-multilangstatus.min.js', [], ['type' => 'module', 'defer' => true]);
?>
<a data-bs-target="#multiLangModal" class="header-item-content multilanguage" title="<?php echo Text::_('MOD_MULTILANGSTATUS'); ?>" data-bs-toggle="modal" role="button">
    <div class="header-item-icon">
        <span class="icon-language" aria-hidden="true"></span>
    </div>
    <div class="header-item-text">
        <?php echo Text::_('MOD_MULTILANGSTATUS'); ?>
    </div>
</a>
<?php echo $modalHTML; ?>
