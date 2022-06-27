<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_custom
 *
 * @copyright   (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;

$modId = 'mod-custom' . $module->id;

if ($params->get('backgroundimage')) {
    /** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
    $wa = $app->getDocument()->getWebAssetManager();
    $wa->addInlineStyle('
#' . $modId . '{background-image: url("' . Uri::root(true) . '/' . HTMLHelper::_('cleanImageURL', $params->get('backgroundimage'))->url . '");}
', ['name' => $modId]);
}
?>

<div class="mod-custom custom banner-overlay" id="<?php echo $modId; ?>">
    <div class="overlay">
        <?php echo $module->content; ?>
    </div>
</div>
