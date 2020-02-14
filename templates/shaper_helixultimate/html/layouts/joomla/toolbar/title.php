<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined('JPATH_BASE') or die;

$icon = empty($displayData['icon']) ? 'generic' : preg_replace('#\.[^ .]*$#', '', $displayData['icon']);
?>
<h1 class="page-title">
	<span class="icon-<?php echo $icon; ?>" aria-hidden="true"></span>
	<?php echo $displayData['title']; ?>
</h1>
