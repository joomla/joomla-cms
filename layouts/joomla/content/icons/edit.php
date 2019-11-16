<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$article = $displayData['article'];
$overlib = $displayData['overlib'];
$nowDate = strtotime(Factory::getDate());

$icon = $article->state ? 'edit' : 'eye-slash';

if (($article->publish_up !== null && strtotime($article->publish_up) > $nowDate)
	|| ($article->publish_down !== null && strtotime($article->publish_down) < $nowDate
		&& $article->publish_down !== Factory::getDbo()->getNullDate()))
{
	$icon = 'eye-slash';
}

?>
<span class="hasTooltip fas fa-<?php echo $icon; ?>" title="<?php echo HTMLHelper::tooltipText(Text::_('COM_CONTENT_EDIT_ITEM'), $overlib, 0, 0); ?>"></span>
<?php echo Text::_('JGLOBAL_EDIT'); ?>
