/**
* @version		$Id: modal.js 5263 2006-10-02 01:25:24Z webImagery $
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * JOpenID javascript behavior
 *
 * Used for switching between normal and openid login forms
 *
 * @author		Johan Janssens <johan.janssens@joomla.org>
 * @package		Joomla
 * @since		1.5
 * @version     1.0
 */
var JOpenID = new Class({

	state    : false,
	link     : null,
	switcher : null,

	initialize: function()
	{
		//Create dynamic elements
		var switcher = new Element('a', { 'styles': {'cursor': 'pointer'},'id': 'openid-link'});
		switcher.inject($('form-login'));

		var link = new Element('a', { 'styles': {'text-align' : 'right', 'display' : 'block', 'font-size' : 'xx-small'}, 'href' : 'http://openid.net'});
		link.setHTML('What is OpenId?');

		//Initialise members
		this.switcher = switcher;
		this.link     = link;
		this.state    = Cookie.get('login-openid');
		this.lenght   = $('form-login-password').getSize().size.y;

		this.switch(this.state, 0);

		this.switcher.addEvent('click', (function(event) {
			this.state = this.state ^ 1;
			this.switch(this.state, 300);
			Cookie.set('login-openid', this.state);
		}).bind(this));
	},

	switch : function(state, time)
	{
		var password = $('form-login-password');
		var username = $('username');

		if(state == 0)
		{
			username.removeClass('system-openid');
			var text = 'Login with an OpenID';
			//this.link.remove();
			password.effect('height',  {duration: time}).start(0, this.lenght);
		}
		else
		{
			username.addClass('system-openid');
			var text = 'Go back to normal login';
			//this.link.inject($('form-login-username'));
			password.effect('height',  {duration: time}).start(this.lenght, 0);
		}

		password.effect('opacity', {duration: time}).start(state,1-state);

		this.switcher.setHTML(text);
	}
});

document.openid = null
window.addEvent('domready', function(){
  var openid = new JOpenID()
  document.openid = openid
});
