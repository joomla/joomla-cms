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
use Joomla\CMS\Language\Text;

$article = $displayData['article'];
$tooltip = $displayData['tooltip'];
$nowDate = strtotime(Factory::getDate());

$icon = $article->state ? 'edit' : 'eye-slash';

if (($article->publish_up !== null && strtotime($article->publish_up) > $nowDate)
	|| ($article->publish_down !== null && strtotime($article->publish_down) < $nowDate
		&& $article->publish_down !== Factory::getDbo()->getNullDate()))
{
	$icon = 'eye-slash';
}
$aria_described = 'editarticle-' . (int) $article->id;

?>
<span class="fas fa-<?php echo $icon; ?>" aria-hidden="true"></span>
	<?php echo Text::_('JGLOBAL_EDIT'); ?>
<div role="tooltip" id="<?php echo $aria_described; ?>">
	<?php echo $tooltip; ?>
</div>
