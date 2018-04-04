<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Filter\OutputFilter;

?>
<h2 class="nav-header"><?php echo OutputFilter::ampReplace(JText::_($displayData)); ?></h2>
<ul class="j-links-group nav nav-list">
