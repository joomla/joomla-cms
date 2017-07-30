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
<a<?php echo $id; ?> href="<?php echo $displayData['link']; ?>" class="<?php echo $pulse; ?>"<?php echo $target . $onclick . $title; ?> role="button">
	<div class="quickicon-icon"><span class="<?php echo $displayData['image']; ?>" aria-hidden="true"></span></div>
	<div class="quickicon-text"><?php echo $text; ?></div>
</a>
