<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

$id      = empty($displayData['id']) ? '' : (' id="' . $displayData['id'] . '"');
$target  = empty($displayData['target']) ? '' : (' target="' . $displayData['target'] . '"');
$onclick = empty($displayData['onclick']) ? '' : (' onclick="' . $displayData['onclick'] . '"');
$title   = empty($displayData['title']) ? '' : (' title="' . $this->escape($displayData['title']) . '"');
$text    = empty($displayData['text']) ? '' : ('<span class="j-links-link">' . $displayData['text'] . '</span>');

$pulse = '';

if ($id !== '')
{
	$pulse = ($displayData['id'] === 'plg_quickicon_joomlaupdate' || $displayData['id'] === 'plg_quickicon_extensionupdate') ? ' pulse' : '';
}

?>
<div class="col-4 col-md-3"<?php echo $id; ?>>
	<a href="<?php echo $displayData['link']; ?>" class="d-flex align-items-stretch<?php echo $pulse; ?>"<?php echo $target . $onclick . $title; ?>>
		<span class="mr-1 icon-<?php echo $displayData['image']; ?> text-center"></span>
		<span class="d-flex align-items-center hidden-xs-down"><?php echo $text; ?></span>
	</a>
	<span class="hidden-sm-up quickicon-text-xs"><?php echo $text; ?></span>
</div>
