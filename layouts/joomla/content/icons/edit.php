<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$article = $displayData['article'];
$overlib = $displayData['overlib'];
$legacy  = $displayData['legacy'];

if ($legacy)
{
	$icon = $article->state ? 'edit.png' : 'edit_unpublished.png';

	if (strtotime($article->publish_up) > strtotime(Factory::getDate())
		|| ((strtotime($article->publish_down) < strtotime(Factory::getDate())) && $article->publish_down != Factory::getDbo()->getNullDate()))
	{
		$icon = 'edit_unpublished.png';
	}
}
else
{
	$icon = $article->state ? 'pencil-square-o' : 'eye-slash';

	if (strtotime($article->publish_up) > strtotime(Factory::getDate())
		|| ((strtotime($article->publish_down) < strtotime(Factory::getDate())) && $article->publish_down != Factory::getDbo()->getNullDate()))
	{
		$icon = 'eye-slash';
	}
}

?>
<?php if ($legacy) : ?>
	<?php echo HTMLHelper::_('image', 'system/' . $icon, Text::_('JGLOBAL_EDIT'), null, true); ?>
<?php else : ?>
	<span class="hasTooltip fa fa-<?php echo $icon; ?>" title="<?php echo HTMLHelper::tooltipText(Text::_('COM_CONTENT_EDIT_ITEM'), $overlib, 0, 0); ?>"></span>
	<?php echo Text::_('JGLOBAL_EDIT'); ?>
<?php endif; ?>
