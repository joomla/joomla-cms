<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_quickicon
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $app->getDocument()->getWebAssetManager();
$wa->useScript('core');
$wa->registerAndUseScript('mod_quickicon', 'mod_quickicon/quickicon.min.js', ['relative' => true, 'version' => 'auto'], ['type' => 'module']);
$wa->registerAndUseScript('mod_quickicon-es5', 'mod_quickicon/quickicon-es5.min.js', ['relative' => true, 'version' => 'auto'], ['nomodule' => true, 'defer' => true]);

$html = HTMLHelper::_('icons.buttons', $buttons);
?>
<?php if (!empty($html)) : ?>
	<nav class="quick-icons px-3 pb-3" aria-label="<?php echo Text::_('MOD_QUICKICON_NAV_LABEL') . ' ' . $module->title; ?>">
		<ul class="nav flex-wrap">
			<?php echo $html; ?>
		</ul>
	</nav>
<?php endif; ?>
