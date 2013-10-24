<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Template.hathor
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$doTask = $displayData['doTask'];
$text   = $displayData['text'];

?>

<a href="javascript:void(0)" onclick="<?php echo $doTask; ?>" rel="help" class="toolbar">
	<span class="icon-32-help"></span>
	<?php echo $text; ?>
</a>
