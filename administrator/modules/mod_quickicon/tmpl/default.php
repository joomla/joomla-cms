<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_quickicon
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $app->getDocument()->getWebAssetManager();
$wa->registerAndUseScript('mod_quickicon', 'mod_quickicon/quickicon.min.js', [], ['defer' => true]);

$html = HTMLHelper::_('icons.buttons', $buttons);
?>
<?php if (!empty($html)) : ?>
	<nav class="quick-icons" aria-label="<?php echo Text::_('MOD_QUICKICON_NAV_LABEL') . ' ' . $module->title; ?>">
		<ul class="nav flex-wrap">
			<?php echo $html; ?>
		</ul>
	</nav>
<?php endif; ?>
