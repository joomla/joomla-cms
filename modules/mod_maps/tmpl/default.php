<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_tags_popular
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @see         https://developers.google.com/maps/documentation/javascript/geocoding#GeocodingRequests
 */

defined('_JEXEC') or die;

?>

<div class="google-maps map<?php echo $moduleclass_sfx; ?>">
	<div id="map-canvas-<?php echo $id; ?>"></div>
</div>


<?php

// Compile Google Map JS
$js = '
		jQuery(document).ready(function() {
				var mapOptions = {
					zoom: '. $zoomLevel .',
					mapTypeId: google.maps.MapTypeId.'. $mapType .'
				};

				var map = new google.maps.Map(document.getElementById("map-canvas-'. $id .'"), mapOptions);
				';


if ($centerType == 'coordinate'):
	$js .= 'map.setCenter(new google.maps.LatLng('. $centerCoordinate .'));';
else:
	$js .= 'var geocoder = new google.maps.Geocoder();

				geocoder.geocode({"address": "'. $centerAddress .'"}, function(results, status) {
					switch (status) {
						case google.maps.GeocoderStatus.OK:
							map.setCenter(results[0].geometry.location);
							break;
						case google.maps.GeocoderStatus.ZERO_RESULTS:
							alert("The address could not be found");
							break;
						case google.maps.GeocoderStatus.OVER_QUERY_LIMIT:
								alert("You have exceeded your map quota");
							break;
						default:
							alert("An unknown error occured. Please try reloading the page");
					}
				});
				';
endif;

$js .= '});';
$doc->addScriptDeclaration($js);

// Compile CSS
$css = '#map-canvas-'. $id .' {
	margin: 0;
	padding: 0;
	width: '. modMapsHelper::getSize($width) .';
	height:' . modMapsHelper::getSize($height) .';
}';
$doc->addStyleDeclaration($css);