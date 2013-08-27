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
$class = $displayData['class'];
$text = $displayData['text'];

?>
<a href="<?php echo $url; ?>" class="btn btn-small">
	<span class="<?php echo $class; ?>"></span>
	<?php echo $text; ?>
</a>
