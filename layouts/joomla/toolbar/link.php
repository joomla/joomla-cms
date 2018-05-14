<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/**
 * @var  int     $id
 * @var  string  $name
 * @var  string  $class
 * @var  string  $text
 * @var  string  $btnClass
 * @var  string  $tagName
 * @var  string  $htmlAttributes
 */
extract($displayData, EXTR_OVERWRITE);

$margin = (strpos($url ?? '', 'index.php?option=com_config') === false) ? '' : ' ml-auto';
$target = empty($target) ? '' : 'target="' . $target . '"';
?>
<a
	id="<?php echo $id; ?>"
	class="<?php echo $btnClass; ?><?php echo $margin; ?>"
	href="<?php echo $url; ?>"
	<?php echo $target; ?>
	<?php echo $htmlAttributes; ?>>
	<span class="<?php echo $class; ?>" aria-hidden="true"></span>
	<?php echo $text; ?>
</a>
