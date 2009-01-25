<?php
/**
* @version		$Id$
* @package		Joomla.Administrator
* @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$version = new JVersion();

?>
<div>
	<?php echo $version->URL; ?>
</div>

<div class="smallgrey">
	<?php echo $version->getLongVersion(); ?>
</div>

<div>
	<a href="http://www.joomla.org/content/blogcategory/32/66/" target="_blank"><?php echo JText::_( 'Check for latest Version' ); ?></a>
</div>