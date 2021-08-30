<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$id      = empty($displayData['id']) ? '' : (' id="' . $displayData['id'] . '"');
$target  = empty($displayData['target']) ? '' : (' target="' . $displayData['target'] . '"');
$rel     = empty($displayData['rel']) ? '' : (' rel="' . $displayData['rel'] . '"');
$onclick = empty($displayData['onclick']) ? '' : (' onclick="' . $displayData['onclick'] . '"');
$title   = empty($displayData['title']) ? '' : (' title="' . $this->escape($displayData['title']) . '"');
$text    = empty($displayData['text']) ? '' : ('<span class="j-links-link">' . $displayData['text'] . '</span>')

?>
<li<?php echo $id; ?>>
	<a href="<?php echo JFilterOutput::ampReplace($displayData['link']); ?>"<?php echo $target . $rel . $onclick . $title; ?>>
		<span class="icon-<?php echo $displayData['image']; ?>" aria-hidden="true"></span> <?php echo $text; ?>
	</a>
</li>
