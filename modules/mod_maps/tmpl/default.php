<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_tags_popular
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

?>

<div class="google-maps map<?php echo $moduleclass_sfx; ?>">
	<script type="text/javascript">
		var map, geocoder, pos;

		function initialize() {

			var address = "<?php echo $params->get('mapCenterAddress')?>";
			var centerCordinate = "<?php echo $params->get('mapCenterCoordinate') ?>";
			var mapCenterType = "<?php echo $params->get('mapCenterType') ?>";
			var mapOptions = {
				zoom: <?php echo $params->get('zoom', '15')?>,
				mapTypeId: google.maps.MapTypeId.<?php echo $params->get('mapType','ROADMAP'); ?>,
				zoomControl: false,
				panControl: false
			};
			map = new google.maps.Map(document.getElementById('map-canvas-<?php echo $id; ?>'), mapOptions);

			if (mapCenterType == "address" && address != null) {
				geocoder = new google.maps.Geocoder();
				codeAddress(address);
			}
			if (mapCenterType == "coordinate" && centerCordinate != null) {
				mapLongLat();
			}
		}

		function codeAddress(address) {
			geocoder.geocode({ 'address': address}, function (results, status) {
				if (status == google.maps.GeocoderStatus.OK) {
					map.setCenter(results[0].geometry.location);
					var marker = new google.maps.Marker({
						map: map,
						position: results[0].geometry.location
					});
				} else {
					alert("Geocode was not successful for the following reason: " + status);
				}
			});
		}

		function mapLongLat() {
			pos = new google.maps.LatLng(<?php echo $params->get('mapCenterCoordinate'); ?>);
			map.setCenter(pos);
			var marker = new google.maps.Marker({
				map: map,
				position: pos
			});
		}

		google.maps.event.addDomListener(window, 'load', initialize);

	</script>
	<div id="map-canvas-<?php echo $id; ?>"></div>
</div>
