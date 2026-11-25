<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

Factory::getApplication()->getDocument()
    ->getWebAssetManager()
    ->registerAndUseStyle('layouts.chromes.outline', 'layouts/chromes/outline.css');

$module = $displayData['module'];

?>
<div class="mod-preview">
    <div class="mod-preview-info">
        <div class="mod-preview-position">
            <?php echo Text::sprintf('JGLOBAL_PREVIEW_POSITION', htmlspecialchars((string) $module->position, ENT_QUOTES, 'UTF-8')); ?>
        </div>
        <div class="mod-preview-style">
            <?php echo Text::sprintf('JGLOBAL_PREVIEW_STYLE', htmlspecialchars((string) $module->style, ENT_QUOTES, 'UTF-8')); ?>
        </div>
    </div>
    <div class="mod-preview-wrapper">
        <?php echo $module->content; ?>
    </div>
</div>
