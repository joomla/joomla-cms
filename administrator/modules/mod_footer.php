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
defined( '_VALID_MOS' ) or die( 'Restricted access' );

global $_VERSION, $_LANG;

?>
<div>
	<?php echo $_VERSION->URL; ?>
</div>

<div class="smallgrey">
	<?php echo $_VERSION->getLongVersion(); ?>
</div>

<div>
	<a href="http://www.joomla.org/content/blogcategory/32/66/" target="_blank"><?php echo $_LANG->_( 'Check for latest Version' ); ?></a>
</div>