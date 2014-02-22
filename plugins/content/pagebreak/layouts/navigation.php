<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.pagenavigation.layout
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

 defined('_JEXEC') or die;
 
 $displayData = new JRegistry($displayData);
?>

<ul><li><?php echo $displayData->get('prev'); ?></li><li><?php echo $displayData->get('next'); ?></li></ul>