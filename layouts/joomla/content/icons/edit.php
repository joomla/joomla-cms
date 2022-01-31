<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

$article = $displayData['article'];
$tooltip = $displayData['tooltip'];
$nowDate = strtotime(Factory::getDate());

$icon = $article->state ? 'edit' : 'eye-slash';
$currentDate   = Factory::getDate()->format('Y-m-d H:i:s');
$isUnpublished = ($article->publish_up > $currentDate)
	|| !is_null($article->publish_down) && ($article->publish_down < $currentDate);

if ($isUnpublished)
{
	$icon = 'eye-slash';
}
$aria_described = 'editarticle-' . (int) $article->id;

?>
<span class="icon-<?php echo $icon; ?>" aria-hidden="true"></span>
	<?php echo Text::_('JGLOBAL_EDIT'); ?>
<div role="tooltip" id="<?php echo $aria_described; ?>">
	<?php echo $tooltip; ?>
</div>
