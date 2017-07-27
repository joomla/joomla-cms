<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Google
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Registry\Registry;

/**
 * Google Maps embed class for the Joomla Platform.
 *
 * @since       12.3
 * @deprecated  4.0  Use the `joomla/google` package via Composer instead
 */
class JGoogleEmbedMaps extends JGoogleEmbed
{
	/**
	 * @var    JHttp  The HTTP client object to use in sending HTTP requests.
	 * @since  12.3
	 */
	protected $http;

	/**
	 * Constructor.
	 *
	 * @param   Registry  $options  Google options object
	 * @param   JUri      $uri      URL of the page being rendered
	 * @param   JHttp     $http     Http client for geocoding requests
	 *
	 * @since   12.3
	 */
	public function __construct(Registry $options = null, JUri $uri = null, JHttp $http = null)
	{
		parent::__construct($options, $uri);
		$this->http = $http ? $http : new JHttp($this->options);
	}

	/**
	 * Method to get the API key
	 *
	 * @return  string  The Google Maps API key
	 *
	 * @since   12.3
	 */
	public function getKey()
	{
		return $this->getOption('key');
	}

	/**
	 * Method to set the API key
	 *
	 * @param   string  $key  The Google Maps API key
	 *
	 * @return  JGoogleEmbedMaps  The object for method chaining
	 *
	 * @since   12.3
	 */
	public function setKey($key)
	{
		$this->setOption('key', $key);

		return $this;
	}

	/**
	 * Method to get the id of the map div
	 *
	 * @return  string  The ID
	 *
	 * @since   12.3
	 */
	public function getMapId()
	{
		return $this->getOption('mapid') ? $this->getOption('mapid') : 'map_canvas';
	}

	/**
	 * Method to set the map div id
	 *
	 * @param   string  $id  The ID
	 *
	 * @return  JGoogleEmbedMaps  The object for method chaining
	 *
	 * @since   12.3
	 */
	public function setMapId($id)
	{
		$this->setOption('mapid', $id);

		return $this;
	}

	/**
	 * Method to get the class of the map div
	 *
	 * @return  string  The class
	 *
	 * @since   12.3
	 */
	public function getMapClass()
	{
		return $this->getOption('mapclass') ? $this->getOption('mapclass') : '';
	}

	/**
	 * Method to set the map div class
	 *
	 * @param   string  $class  The class
	 *
	 * @return  JGoogleEmbedMaps  The object for method chaining
	 *
	 * @since   12.3
	 */
	public function setMapClass($class)
	{
		$this->setOption('mapclass', $class);

		return $this;
	}

	/**
	 * Method to get the style of the map div
	 *
	 * @return  string  The style
	 *
	 * @since   12.3
	 */
	public function getMapStyle()
	{
		return $this->getOption('mapstyle') ? $this->getOption('mapstyle') : '';
	}

	/**
	 * Method to set the map div style
	 *
	 * @param   string  $style  The style
	 *
	 * @return  JGoogleEmbedMaps  The object for method chaining
	 *
	 * @since   12.3
	 */
	public function setMapStyle($style)
	{
		$this->setOption('mapstyle', $style);

		return $this;
	}

	/**
	 * Method to get the map type setting
	 *
	 * @return  string  The class
	 *
	 * @since   12.3
	 */
	public function getMapType()
	{
		return $this->getOption('maptype') ? $this->getOption('maptype') : 'ROADMAP';
	}

	/**
	 * Method to set the map type ()
	 *
	 * @param   string  $type  Valid types are ROADMAP, SATELLITE, HYBRID, and TERRAIN
	 *
	 * @return  JGoogleEmbedMaps  The object for method chaining
	 *
	 * @since   12.3
	 */
	public function setMapType($type)
	{
		$this->setOption('maptype', strtoupper($type));

		return $this;
	}

	/**
	 * Method to get additional map options
	 *
	 * @return  string  The options
	 *
	 * @since   12.3
	 */
	public function getAdditionalMapOptions()
	{
		return $this->getOption('mapoptions') ? $this->getOption('mapoptions') : array();
	}

	/**
	 * Method to add additional map options
	 *
	 * @param   array  $options  Additional map options
	 *
	 * @return  JGoogleEmbedMaps  The object for method chaining
	 *
	 * @since   12.3
	 */
	public function setAdditionalMapOptions($options)
	{
		$this->setOption('mapoptions', $options);

		return $this;
	}

	/**
	 * Method to get additional map options
	 *
	 * @return  string  The options
	 *
	 * @since   12.3
	 */
	public function getAdditionalJavascript()
	{
		return $this->getOption('extrascript') ? $this->getOption('extrascript') : '';
	}

	/**
	 * Method to add additional javascript
	 *
	 * @param   array  $script  Additional javascript
	 *
	 * @return  JGoogleEmbedMaps  The object for method chaining
	 *
	 * @since   12.3
	 */
	public function setAdditionalJavascript($script)
	{
		$this->setOption('extrascript', $script);

		return $this;
	}

	/**
	 * Method to get the zoom
	 *
	 * @return  int  The zoom level
	 *
	 * @since   12.3
	 */
	public function getZoom()
	{
		return $this->getOption('zoom') ? $this->getOption('zoom') : 0;
	}

	/**
	 * Method to set the map zoom
	 *
	 * @param   int  $zoom  Zoom level (0 is whole world)
	 *
	 * @return  JGoogleEmbedMaps  The object for method chaining
	 *
	 * @since   12.3
	 */
	public function setZoom($zoom)
	{
		$this->setOption('zoom', $zoom);

		return $this;
	}

	/**
	 * Method to set the center of the map
	 *
	 * @return  mixed  A latitude longitude array or an address string
	 *
	 * @since   12.3
	 */
	public function getCenter()
	{
		return $this->getOption('mapcenter') ? $this->getOption('mapcenter') : array(0, 0);
	}

	/**
	 * Method to set the center of the map
	 *
	 * @param   mixed  $location       A latitude/longitude array or an address string
	 * @param   mixed  $title          Title of marker or false for no marker
	 * @param   array  $markeroptions  Options for marker
	 * @param   array  $markerevents   Events for marker
	 *
	 * @example with events call:
	 *		$map->setCenter(
	 *			array(0, 0),
	 *			'Map Center',
	 *			array(),
	 *			array(
	 *				'click' => 'function() { // code goes here }
	 *			)
	 *		)
	 *
	 * @return  JGoogleEmbedMaps  The latitude/longitude of the center or false on failure
	 *
	 * @since   12.3
	 */
	public function setCenter($location, $title = true, $markeroptions = array(), $markerevents = array())
	{
		if ($title)
		{
			$title = is_string($title) ? $title : null;

			if (!$marker = $this->addMarker($location, $title, $markeroptions, $markerevents))
			{
				return false;
			}

			$location = $marker['loc'];
		}
		elseif (is_string($location))
		{
			$geocode = $this->geocodeAddress($location);

			if (!$geocode)
			{
				return false;
			}

			$location = $geocode['geometry']['location'];
			$location = array_values($location);
		}

		$this->setOption('mapcenter', $location);

		return $this;
	}

	/**
	 * Method to add an event handler to the map.
	 * Event handlers must be passed in either as callback name or fully qualified function declaration
	 *
	 * @param   string  $type      The event name
	 * @param   string  $function  The event handling function body
	 *
	 * @example to add an event call:
	 *		$map->addEventHandler('click', 'function(){ alert("map click event"); }');
	 *
	 * @return  JGoogleEmbedMaps   The object for method chaining
	 *
	 * @since   12.3
	 */
	public function addEventHandler($type, $function)
	{
		$events = $this->listEventHandlers();

		$events[$type] = $function;

		$this->setOption('events', $events);

		return $this;
	}

	/**
	 * Method to remove an event handler from the map
	 *
	 * @param   string  $type  The event name
	 *
	 * @example to delete an event call:
	 *		$map->deleteEventHandler('click');
	 *
	 * @return  string  The event handler content
	 *
	 * @since   12.3
	 */
	public function deleteEventHandler($type = null)
	{
		$events = $this->listEventHandlers();

		if ($type === null || !isset($events[$type]))
		{
			return;
		}

		$event = $events[$type];
		unset($events[$type]);
		$this->setOption('events', $events);

		return $event;
	}

	/**
	 * List the events added to the map
	 *
	 * @return  array  A list of events
	 *
	 * @since   12.3
	 */
	public function listEventHandlers()
	{
		return $this->getOption('events') ? $this->getOption('events') : array();
	}

	/**
	 * Add a marker to the map
	 *
	 * @param   mixed  $location  A latitude/longitude array or an address string
	 * @param   mixed  $title     The hover-text for the marker
	 * @param   array  $options   Options for marker
	 * @param   array  $events    Events for marker
	 *
	 * @example with events call:
	 *		$map->addMarker(
	 *			array(0, 0),
	 *			'My Marker',
	 *			array(),
	 *			array(
	 *				'click' => 'function() { // code goes here }
	 *			)
	 *		)
	 *
	 * @return  mixed  The marker or false on failure
	 *
	 * @since   12.3
	 */
	public function addMarker($location, $title = null, $options = array(), $events = array())
	{
		if (is_string($location))
		{
			if (!$title)
			{
				$title = $location;
			}

			$geocode = $this->geocodeAddress($location);

			if (!$geocode)
			{
				return false;
			}

			$location = $geocode['geometry']['location'];
		}
		elseif (!$title)
		{
			$title = implode(', ', $location);
		}

		$location = array_values($location);
		$marker = array('loc' => $location, 'title' => $title, 'options' => $options, 'events' => $events);

		$markers = $this->listMarkers();
		$markers[] = $marker;
		$this->setOption('markers', $markers);

		return $marker;
	}

	/**
	 * List the markers added to the map
	 *
	 * @return  array  A list of markers
	 *
	 * @since   12.3
	 */
	public function listMarkers()
	{
		return $this->getOption('markers') ? $this->getOption('markers') : array();
	}

	/**
	 * Delete a marker from the map
	 *
	 * @param   int  $index  Index of marker to delete (defaults to last added marker)
	 *
	 * @return  array The latitude/longitude of the deleted marker
	 *
	 * @since   12.3
	 */
	public function deleteMarker($index = null)
	{
		$markers = $this->listMarkers();

		if ($index === null)
		{
			$index = count($markers) - 1;
		}

		if ($index >= count($markers) || $index < 0)
		{
			throw new OutOfBoundsException('Marker index out of bounds.');
		}

		$marker = $markers[$index];
		unset($markers[$index]);
		$markers = array_values($markers);
		$this->setOption('markers', $markers);

		return $marker;
	}

	/**
	 * Checks if the javascript is set to be asynchronous
	 *
	 * @return  boolean  True if asynchronous
	 *
	 * @since   12.3
	 */
	public function isAsync()
	{
		return $this->getOption('async') === null ? true : $this->getOption('async');
	}

	/**
	 * Load javascript asynchronously
	 *
	 * @return  JGoogleEmbedMaps  The object for method chaining
	 *
	 * @since   12.3
	 */
	public function useAsync()
	{
		$this->setOption('async', true);

		return $this;
	}

	/**
	 * Load javascript synchronously
	 *
	 * @return  JGoogleEmbedAMaps  The object for method chaining
	 *
	 * @since   12.3
	 */
	public function useSync()
	{
		$this->setOption('async', false);

		return $this;
	}

	/**
	 * Method to get callback function for async javascript loading
	 *
	 * @return  string  The ID
	 *
	 * @since   12.3
	 */
	public function getAsyncCallback()
	{
		return $this->getOption('callback') ? $this->getOption('callback') : 'initialize';
	}

	/**
	 * Method to set the callback function for async javascript loading
	 *
	 * @param   string  $callback  The callback function name
	 *
	 * @return  JGoogleEmbedMaps  The object for method chaining
	 *
	 * @since   12.3
	 */
	public function setAsyncCallback($callback)
	{
		$this->setOption('callback', $callback);

		return $this;
	}

	/**
	 * Checks if a sensor is set to be required
	 *
	 * @return  boolean  True if asynchronous
	 *
	 * @since   12.3
	 */
	public function hasSensor()
	{
		return $this->getOption('sensor') === null ? false : $this->getOption('sensor');
	}

	/**
	 * Require access to sensor data
	 *
	 * @return  JGoogleEmbedMaps  The object for method chaining
	 *
	 * @since   12.3
	 */
	public function useSensor()
	{
		$this->setOption('sensor', true);

		return $this;
	}

	/**
	 * Don't require access to sensor data
	 *
	 * @return  JGoogleEmbedAMaps  The object for method chaining
	 *
	 * @since   12.3
	 */
	public function noSensor()
	{
		$this->setOption('sensor', false);

		return $this;
	}

	/**
	 * Checks how the script should be loaded
	 *
	 * @return  string  Autoload type (onload, jquery, mootools, or false)
	 *
	 * @since   12.3
	 */
	public function getAutoload()
	{
		return $this->getOption('autoload') ? $this->getOption('autoload') : 'false';
	}

	/**
	 * Automatically add the callback to the window
	 *
	 * @param   string  $type  The method to add the callback (options are onload, jquery, mootools, and false)
	 *
	 * @return  JGoogleEmbedAMaps  The object for method chaining
	 *
	 * @since   12.3
	 */
	public function setAutoload($type = 'onload')
	{
		$this->setOption('autoload', $type);

		return $this;
	}

	/**
	 * Get code to load Google Maps javascript
	 *
	 * @return  string  Javascript code
	 *
	 * @since   12.3
	 */
	public function getHeader()
	{
		$zoom = $this->getZoom();
		$center = $this->getCenter();
		$maptype = $this->getMapType();
		$id = $this->getMapId();
		$scheme = $this->isSecure() ? 'https' : 'http';
		$key = $this->getKey();
		$sensor = $this->hasSensor() ? 'true' : 'false';

		$setup = 'var mapOptions = {';
		$setup .= "zoom: {$zoom},";
		$setup .= "center: new google.maps.LatLng({$center[0]},{$center[1]}),";
		$setup .= "mapTypeId: google.maps.MapTypeId.{$maptype},";
		$setup .= substr(json_encode($this->getAdditionalMapOptions()), 1, -1);
		$setup .= '};';
		$setup .= "var map = new google.maps.Map(document.getElementById('{$id}'), mapOptions);";

		$events = $this->listEventHandlers();

		if (isset($events) && count($events))
		{
			foreach ($events as $type => $handler)
			{
				$setup .= "google.maps.event.addListener(map, '{$type}', {$handler});";
			}
		}

		$markers = $this->listMarkers();

		if (isset($markers) && count($markers))
		{
			$setup .= 'var marker;';

			foreach ($markers as $marker)
			{
				$loc = $marker['loc'];
				$title = $marker['title'];
				$options = $marker['options'];

				$setup .= 'marker = new google.maps.Marker({';
				$setup .= "position: new google.maps.LatLng({$loc[0]},{$loc[1]}),";
				$setup .= 'map: map,';
				$setup .= "title:'{$title}',";
				$setup .= substr(json_encode($options), 1, -1);
				$setup .= '});';

				if (isset($marker['events']) && is_array($marker['events']))
				{
					foreach ($marker['events'] as $type => $handler)
					{
						$setup .= 'google.maps.event.addListener(marker, "' . $type . '", ' . $handler . ');';
					}
				}
			}
		}

		$setup .= $this->getAdditionalJavascript();

		if ($this->isAsync())
		{
			$asynccallback = $this->getAsyncCallback();

			$output = '<script type="text/javascript">';
			$output .= "function {$asynccallback}() {";
			$output .= $setup;
			$output .= '}';

			$onload = 'function() {';
			$onload .= 'var script = document.createElement("script");';
			$onload .= 'script.type = "text/javascript";';
			$onload .= "script.src = '{$scheme}://maps.googleapis.com/maps/api/js?" . ($key ? "key={$key}&" : '')
				. "sensor={$sensor}&callback={$asynccallback}';";
			$onload .= 'document.body.appendChild(script);';
			$onload .= '}';
		}
		else
		{
			$output = "<script type='text/javascript' src='{$scheme}://maps.googleapis.com/maps/api/js?" . ($key ? "key={$key}&" : '') . "sensor={$sensor}'>";
			$output .= '</script>';
			$output .= '<script type="text/javascript">';

			$onload = 'function() {';
			$onload .= $setup;
			$onload .= '}';
		}

		switch ($this->getAutoload())
		{
			case 'onload':
			$output .= "window.onload={$onload};";
			break;

			case 'jquery':
			$output .= "jQuery(document).ready({$onload});";
			break;

			case 'mootools':
			$output .= "window.addEvent('domready',{$onload});";
			break;
		}

		$output .= '</script>';

		return $output;
	}

	/**
	 * Method to retrieve the div that the map is loaded into
	 *
	 * @return  string  The body
	 *
	 * @since   12.3
	 */
	public function getBody()
	{
		$id = $this->getMapId();
		$class = $this->getMapClass();
		$style = $this->getMapStyle();

		$output = "<div id='{$id}'";

		if (!empty($class))
		{
			$output .= " class='{$class}'";
		}

		if (!empty($style))
		{
			$output .= " style='{$style}'";
		}

		$output .= '></div>';

		return $output;
	}

	/**
	 * Method to get the location information back from an address
	 *
	 * @param   string  $address  The address to geocode
	 *
	 * @return  array  An array containing Google's geocode data
	 *
	 * @since   12.3
	 */
	public function geocodeAddress($address)
	{
		$uri = JUri::getInstance('https://maps.googleapis.com/maps/api/geocode/json');

		$uri->setVar('address', urlencode($address));

		if (($key = $this->getKey()))
		{
			$uri->setVar('key', $key);
		}

		$response = $this->http->get($uri->toString());

		if ($response->code < 200 || $response->code >= 300)
		{
			throw new RuntimeException('Error code ' . $response->code . ' received geocoding address: ' . $response->body . '.');
		}

		$data = json_decode($response->body, true);

		if (!$data)
		{
			throw new RuntimeException('Invalid json received geocoding address: ' . $response->body . '.');
		}

		if ($data['status'] != 'OK')
		{
			if (!empty($data['error_message']))
			{
				throw new RuntimeException($data['error_message']);
			}

			return;
		}

		return $data['results'][0];
	}
}
