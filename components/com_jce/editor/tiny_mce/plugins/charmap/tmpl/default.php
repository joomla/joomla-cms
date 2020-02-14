<?php

/**
 * @copyright 	Copyright (c) 2009-2019 Ryan Demmer. All rights reserved
 * @license   	GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */
defined('JPATH_PLATFORM') or die;
?>

<form onsubmit="return false;" action="#">
	<div id="charmap" role="presentation" class="uk-grid uk-grid-small">
		<div id="charmapView" class="uk-width-5-6"><!-- Chars will be rendered here --></div>
		<div id="charmapDescription" class="uk-width-1-6">
			<div id="codeV"></div>
			<div id="codeN"></div>
			<div class="box">
				<div class="title">HTML-Code</div>
				<div id="codeA"></div>
			</div>
			<div class="box">
				<div class="title">NUM-Code</div>
				<div id="codeB"></div>
			</div>
		</div>
	</div>
</form>
