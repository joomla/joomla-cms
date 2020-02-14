<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined('JPATH_BASE') or die;

$selector = empty($displayData['selector']) ? '' : $displayData['selector'];

?>

<ul class="joomla-tabs nav nav-tabs mb-3" id="<?php echo $selector; ?>Tabs"></ul>
<div class="tab-content" id="<?php echo $selector; ?>Content">
