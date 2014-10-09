<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('script', 'jui/jquery-popupwindow-min.js', false, true);

$url = $displayData['doTask'];
$text = $displayData['text'];

?>
<button href="<?php echo $url; ?>" rel="help" class="btn btn-small popup">
	<span class="icon-question-sign"></span>
	<?php echo $text; ?>
</button>
