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
	<!--	<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false"></script>-->
	<script type="text/javascript">
		var map;
		var geocoder;
		var pos;
		var results;
		var status;
		function initialize() {


			var address = "<?php echo $params->get('mapCenterAddress')?>";
			var centerCordinate = "<?php echo $params->get('mapCenterCoordinate') ?>";
			var mapCenterType = "<?php echo $params->get('mapCenterType') ?>";
			var mapOptions = {
					zoom: <?php echo $params->get('zoom', '15')?>,
//					center: pos,
					mapTypeId: google.maps.MapTypeId.<?php echo $params->get('mapType','ROADMAP'); ?>,
					zoomControl: true,
					panControl: true,
					zoomControlOptions: {
						style: google.maps.ZoomControlStyle.LARGE
					}
				}
				;
			map = new google.maps.Map(document.getElementById('map-canvas-<?php echo $id; ?>'),
				mapOptions);


			if (mapCenterType == "address" && address != null) {
				geocoder = new google.maps.Geocoder();
				geocoder.geocode({ 'address': address}, function (results, status) {
					if (status == google.maps.GeocoderStatus.OK) {
						pos = new google.maps.LatLng(results[0].geometry.location.lat(), results[0].geometry.location.lng());
						map.setCenter(pos);
					}
				});
			}
			if (mapCenterType == "coordinate" && centerCordinate != null) {
				pos = new google.maps.LatLng(<?php echo $params->get('mapCenterCoordinate'); ?>);
				map.setCenter(pos);
			}

			var marker = new google.maps.Marker({
				position: pos,
				map: map
			});
		}
		google.maps.event.addDomListener(window, 'load', initialize);

	</script>
	<div id="map-canvas-<?php echo $id; ?>"></div>
</div>
