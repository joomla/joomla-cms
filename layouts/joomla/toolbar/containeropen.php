<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::_('script', 'system/toolbar.min.js', array('version' => 'auto', 'relative' => true));
?>
<div class="btn-toolbar d-flex" role="toolbar" aria-label="<?php echo Text::_('JTOOLBAR'); ?>" id="<?php echo $displayData['id']; ?>">
