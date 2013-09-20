<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$url = $displayData['url'];
$text = $displayData['text'];

?>
<a href="<?php echo $url; ?>" target="_blank" rel="help" class="btn btn-small">
	<span class="icon-question-sign"></span>
	<?php echo $text; ?>
</a>
