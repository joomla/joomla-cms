/**
* @version		$Id$
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * Cookie handling prototype
 *
 * @author		Louis Landry <louis.landry@joomla.org>
 * @package		Joomla!
 * @subpackage	JavaScript
 * @since		1.5
 */
JCookie = function() { this.constructor.apply(this, arguments);}
JCookie.prototype = {

	constructor: function() 
	{	
		var self = this;
	},

	/**
	 * Method to get a cookie's value by name
	 *
	 * @param	string	Name of the cookie value to get
	 * @return	string	Value of the cookie if exists or null if it does not exist
	 * @since	1.5
	 */
	get: function( name )
	{
		// Does the name exist in the cookie?
		var c = document.cookie.indexOf( name + "=" );
		if ( ( !c ) && ( name != document.cookie.substring( 0, name.length ) ) ) {
			return null;
		}
		if ( c == -1 ) {
			return null;
		}

		// Get the length of the cookie value string.
		var start = c + name.length + 1;
		// Get the end of the cookie value string.
		var end = document.cookie.indexOf( ";", start );
		
		// Special case if there is only one cookie set.
		if ( end == -1 ) {
			end = document.cookie.length;
		}
		// Return the value string
		return unescape( document.cookie.substring( start, end ) );
	},
	
	/**
	 * Method to set a cookie value
	 *
	 * @param	string	Name of the cookie value to set
	 * @param	string	Value to set.
	 * @param	numeric	Cookie expiration time in milliseconds [optional]
	 * @param	string	Cookie path [optional]
	 * @param	string	Cookie domain [optional]
	 * @param	boolean	Secure cookie state [optional]
	 * @return	void 
	 * @since	1.5
	 */
	set: function( name, value, expires, path, domain, secure )
	{
		// Get a date object and set the time (in milliseconds)
		var today = new Date();
		today.setTime( today.getTime() );

		// Get the expiration date by adding time to current time
		var expirationDate = new Date( today.getTime() + (expires) );

		// Build the cookie string and set it.
		document.cookie = name + "=" +escape( value ) +
			( ( expires ) ? ";expires=" + expirationDate.toGMTString() : "" ) +
			( ( path ) ? ";path=" + path : "" ) + 
			( ( domain ) ? ";domain=" + domain : "" ) +
			( ( secure ) ? ";secure" : "" );
	},

	/**
	 * Method to remove a cookie value by name
	 *
	 * @param	string	Name of the cookie value to remove
	 * @param	string	Cookie path [optional]
	 * @param	string	Cookie domain [optional]
	 * @return	void
	 * @since	1.5
	 */
	unset: function( name, path, domain )
	{
		if ( this.get( name ) ) document.cookie = name + "=" +
				( ( path ) ? ";path=" + path : "") +
				( ( domain ) ? ";domain=" + domain : "" ) +
				";expires=Thu, 01-Jan-1970 00:00:01 GMT";
	}
}