<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

$textPrefix = $displayData['textPrefix'] ?? '';
$title = $displayData['title'] ?? '';
$icon = $displayData['icon'] ?? 'icon-copy article';
$componentLangString = $textPrefix . '_EMPTYSTATE_TITLE';
$moduleLangString = $textPrefix . '_EMPTYSTATE_MODULE_TITLE' . (array_key_exists('textSuffix', $displayData) ? $displayData['textSuffix'] : '');

// Did we have a definitive title provided to the view?, if not lets find one
if (!$title)
{
	// Can we find a *_EMPTYSTATE_MODULE_TITLE translation, Else use the components *_EMPTYSTATE_TITLE string
	$title = Factory::getApplication()->getLanguage()->hasKey($moduleLangString) ? $moduleLangString : $componentLangString;
}
?>
<div class="mb-4">
	<p class="fw-bold text-center text-muted">
		<span class="<?php echo $icon; ?>" aria-hidden="true"></span> <?php echo Text::_($title); ?>
	</p>
</div>
