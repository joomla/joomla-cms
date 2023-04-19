<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\Filter\OutputFilter;

?>
<h2 class="nav-header"><?php echo OutputFilter::ampReplace(Text::_($displayData)); ?></h2>
<ul class="j-links-group nav nav-list">
