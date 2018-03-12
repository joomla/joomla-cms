<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

JHtml::_('behavior.core');

$doTask = $displayData['doTask'];
$text   = $displayData['text'];

?>
<button onclick="<?php echo $doTask; ?>" rel="help" class="btn btn-small">
	<span class="icon-question-sign" aria-hidden="true"></span>
	<?php echo $text; ?>
</button>
