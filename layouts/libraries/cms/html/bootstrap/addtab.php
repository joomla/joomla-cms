<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$id       = empty($displayData['id']) ? '' : $displayData['id'];
$active   = empty($displayData['active']) ? '' : $displayData['active'];
$selector = empty($displayData['selector']) ? '' : $displayData['selector'];
$title    = empty($displayData['title']) ? '' : $displayData['title'];
?>
<div id="<?php echo $id; ?>" class="tab-pane<?php echo $active; ?>" data-node="<?php echo htmlspecialchars($active, ENT_COMPAT, 'UTF-8') .'['. htmlspecialchars($id, ENT_COMPAT, 'UTF-8') .'['. htmlspecialchars($title, ENT_COMPAT, 'UTF-8'); ?>">
