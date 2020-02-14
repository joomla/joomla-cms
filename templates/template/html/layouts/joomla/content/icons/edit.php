<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('JPATH_BASE') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$article = $displayData['article'];
$overlib = $displayData['overlib'];

$icon = $article->state ? 'pencil-square-o' : 'eye-slash';

if (strtotime($article->publish_up) > strtotime(Factory::getDate()) || ((strtotime($article->publish_down) < strtotime(Factory::getDate())) && $article->publish_down != Factory::getDbo()->getNullDate()))
{
	$icon = 'eye-slash';
}

?>
<SPAN class="link-edit-article">
	<span class="hasTooltip fa fa-<?php echo $icon; ?>" title="<?php echo HTMLHelper::tooltipText(Text::_('COM_CONTENT_EDIT_ITEM'), $overlib, 0, 0); ?>"></span>
	<?php echo Text::_('JGLOBAL_EDIT'); ?>
</SPAN>
