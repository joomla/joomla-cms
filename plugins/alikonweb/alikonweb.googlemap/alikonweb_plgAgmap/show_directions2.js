  var directionDisplay;
  var directionsService;
  var map;
  var indirizzo_arrivo;
  function initialize() {
   directionsService = new google.maps.DirectionsService();
    directionsDisplay = new google.maps.DirectionsRenderer();
    var roma = new google.maps.LatLng(41.89610448989056, 12.4858);
    var myOptions = {
      zoom:9,
      mapTypeId: google.maps.MapTypeId.ROADMAP,
      center: roma,
	  streetViewControl: true
    }
	var trafficOptions = {
     getTileUrl: function(coord, zoom) {
     return "http://mt3.google.com/mapstt?" +
     "zoom=" + zoom + "&x=" + coord.x + "&y=" + coord.y + "&client=google";
     },
     tileSize: new google.maps.Size(256, 256),
     isPng: true
    };

    var trafficMapType = new google.maps.ImageMapType(trafficOptions);
 
    map = new google.maps.Map(document.getElementById('acmap'), myOptions);
	map.overlayMapTypes.insertAt(0, trafficMapType);
    directionsDisplay.setMap(map);
    directionsDisplay.setPanel(document.getElementById("directionsPanel"));
	//indirizzo_arrivo=document.getElementById("arrivo");
	calcRoute();
  }
  
  function calcRoute() {
    var start = document.getElementById("partenza").value;
	//var start = 'bari, italy';
    var end = document.getElementById("arrivo").value;
	//var end = address;
    var request = {
        origin:start, 
        destination:end,
        travelMode: google.maps.DirectionsTravelMode.DRIVING
    };
    directionsService.route(request, function(response, status) {
      if (status == google.maps.DirectionsStatus.OK) {
        directionsDisplay.setDirections(response);
	  } else {
        alert("Geocode was not successful for the following reason: " + status);
      }	

    });
  }
function calcRoute2() {
    //var start = document.getElementById("partenza").value;
	var start = $("partenza").value;
	//var start = 'bari, italy';
    var end = document.getElementById("arrivo").value;
	//var end = indirizzo_arrivo;
    var request = {
        origin:start, 
        destination:end,
        travelMode: google.maps.DirectionsTravelMode.DRIVING
    };
    directionsService.route(request, function(response, status) {
      if (status == google.maps.DirectionsStatus.OK) {
        directionsDisplay.setDirections(response);
      }
    });
  }
  
  window.addEvent("domready",function(){
   $("partenza").addEvent("blur",function(){
    calcRoute2();
   });
});