<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined('JPATH_BASE') or die;

$id       = empty($displayData['id']) ? '' : $displayData['id'];
$active   = empty($displayData['active']) ? '' : $displayData['active'];
$selector = empty($displayData['selector']) ? '' : $displayData['selector'];
$title    = empty($displayData['title']) ? '' : $displayData['title'];
?>
<div id="<?php echo $id; ?>" class="tab-pane<?php echo $active; ?>" data-node="<?php echo htmlspecialchars($active, ENT_COMPAT, 'UTF-8') .'['. htmlspecialchars($id, ENT_COMPAT, 'UTF-8') .'['. htmlspecialchars($title, ENT_COMPAT, 'UTF-8'); ?>">
