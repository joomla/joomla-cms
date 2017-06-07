<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

\JHtml::_('behavior.core');
\JHtml::_('stylesheet', 'vendor/shepherd/shepherd-theme-arrows.css', ['relative' => true]);
\JHtml::_('script', 'vendor/tether/tether.min.js', ['version' => 'auto', 'relative' => true]);
\JHtml::_('script', 'vendor/shepherd/shepherd.min.js', ['version' => 'auto', 'relative' => true]);
\JHtml::_('script', $displayData['file'], ['version' => 'auto', 'relative' => true]);

$doTask = $displayData['doTask'];
$text   = $displayData['text'];
?>
<button onclick="<?php echo $doTask; ?>" rel="guide" class="btn btn-outline-info btn-sm float-sm-right">
	<span class="icon-question-sign"></span>
	<?php echo $text; ?>
</button>