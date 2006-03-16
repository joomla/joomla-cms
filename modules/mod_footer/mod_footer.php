<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

global $_VERSION;

/*
 * 						::NOTE::
 * You may change this file to suit your site needs
 * 						::NOTE::
 */

$cur_year = mosCurrentDate('%Y');
$csite_name = $GLOBALS['mosConfig_sitename'];

if (strpos(JText :: _('FOOTER_LINE1'), '%date%'))
{
	$line1 = ereg_replace('%date%', $cur_year, JText :: _('FOOTER_LINE1'));
}
else
{
	$line1 = JText :: _('FOOTER_LINE1');
}
if (strpos($line1, '%sitename%'))
{
	$lineone = ereg_replace('%sitename%', $csite_name, $line1);
}
else
{
	$lineone = $line1;
}
?>
<div>
<?php echo $lineone; ?>
</div>
<div>
<?php echo JText::_( 'FOOTER_LINE2' ); ?>
</div>