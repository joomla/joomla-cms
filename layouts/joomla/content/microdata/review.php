<?php
/**
 * @package     Joomla.Cms
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

$language = $displayData['item']->language;
?>
<dd>
	<meta data-sd="inLanguage" content="<?php echo ($language === '*') ? JFactory::getConfig()->get('language') : $language; ?>" />
</dd>