<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Language\Text;

?>
<h2 class="nav-header"><?php echo OutputFilter::ampReplace(Text::_($displayData)); ?></h2>
<ul class="j-links-group nav nav-list">
