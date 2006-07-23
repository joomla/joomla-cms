<?php
/**
* @version $Id$
* @package Joomla_1.0.0
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

// Include the syndicate functions only once
require_once (dirname(__FILE__).DS.'helper.php');

$params = modWrapper::getParams($params);

$load   = $params->get( 'load');
$url    = $params->get( 'url');
$target = $params->get( 'target' );
$width  = $params->get( 'width');
$height = $params->get( 'height');
$scroll = $params->get( 'scrolling' );
$class  = $params->get( 'moduleclass_sfx' );
?>

<script language="javascript" type="text/javascript">
	function iFrameHeight() {
		var h = 0;
		if ( !document.all ) {
			h = document.getElementById('blockrandom').contentDocument.height;
			document.getElementById('blockrandom').style.height = h + 60 + 'px';
		} else if( document.all ) {
			h = document.frames('blockrandom').document.body.scrollHeight;
			document.all.blockrandom.style.height = h + 20 + 'px';
		}
	}
</script>

<iframe <?php echo $load; ?> id="blockrandom"
name="<?php echo $target ?>"
src="<?php echo $url; ?>"
width="<?php echo $width ?>"
height="<?php echo $height ?>"
scrolling="<?php echo $scoll ?>"
align="top"
frameborder="0"
class="wrapper<?php echo $class ?>">
<?php echo JText::_('NO_IFRAMES'); ?>
</iframe>