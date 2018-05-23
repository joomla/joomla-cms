<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/**
 * @var  string  $action
 * @var  array   $options
 */
extract($displayData, EXTR_OVERWRITE);
?>

<?php echo $action; ?>
