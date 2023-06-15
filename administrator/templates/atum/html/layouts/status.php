<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  Templates.Atum
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * Module chrome for rendering the module in a submenu
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Language\Text;

$modulePosition = $displayData['modules'];

$renderer   = Factory::getDocument()->loadRenderer('module');
$modules    = ModuleHelper::getModules($modulePosition);
$moduleHtml = [];
$moduleCollapsedHtml = [];

foreach ($modules as $key => $mod) {
    $out = $renderer->render($mod);

    if ($out !== '') {
        if (strpos($out, 'data-bs-toggle="modal"') !== false) {
            $dom = new \DOMDocument();
            $dom->loadHTML('<?xml encoding="utf-8" ?>' . $out);
            $els = $dom->getElementsByTagName('a');

            if ($els[0]) {
                $moduleCollapsedHtml[] = $dom->saveHTML($els[0]);
            } else {
                $moduleCollapsedHtml[] = $out;
            }
        } else {
            $moduleCollapsedHtml[] = $out;
        }

        $moduleHtml[] = $out;
    }
}
?>
<div class="header-items d-flex ms-auto">
    <?php
    foreach ($moduleHtml as $mod) {
        echo '<div class="header-item">' . $mod . '</div>';
    }
    ?>
    <div class="header-more d-none" id="header-more-items" >
        <button class="header-more-btn dropdown-toggle" type="button" title="<?php echo Text::_('TPL_ATUM_MORE_ELEMENTS'); ?>" data-bs-toggle="dropdown" aria-expanded="false">
            <div class="header-item-icon"><span class="icon-ellipsis-h" aria-hidden="true"></span></div>
            <div class="visually-hidden"><?php echo Text::_('TPL_ATUM_MORE_ELEMENTS'); ?></div>
        </button>
        <div class="header-dd-items dropdown-menu">
            <?php
            foreach ($moduleCollapsedHtml as $key => $mod) {
                echo '<div class="header-dd-item dropdown-item" data-item="' . $key . '">' . $mod . '</div>';
            }
            ?>
        </div>
    </div>
</div>
