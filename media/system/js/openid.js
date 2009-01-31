/**
 * @version		$Id: modal.js 5263 2006-10-02 01:25:24Z webImagery $
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 */

/**
 * JOpenID javascript behavior
 *
 * Used for switching between normal and openid login forms
 *
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
		var switcher = new Element('a', { 'styles': {'cursor': 'pointer'},'id': 'openid-link'});
		switcher.inject($('form-login'));
		var link = new Element('a', { 'styles': {'text-align' : 'right', 'display' : 'block', 'font-size' : 'xx-small'}, 'href' : 'http://openid.net'});
		link.set('html', JLanguage.WHAT_IS_OPENID);

		//Initialise members
		this.switcher = switcher;
		this.link     = link;
		this.state    = Cookie.read('login-openid');
		this.length   = $('form-login-password').getSize().y;

		this.switch(this.state, 0);

		this.switcher.addEvent('click', (function(event) {
			this.state = this.state ^ 1;
			this.switch(this.state, 300);
			Cookie.write('login-openid', this.state);
		}).bind(this));
	},

	switch : function(state, time)
	{
		var password = $('form-login-password');
		var username = $('modlgn_username');
		var passwordTween = new Fx.Tween(password, {'property': 'height'}, { 'duration': time });
		var passwordOpaqTween = new Fx.Tween(password, {'property': 'opacity'}, { 'duration': time });
		if(state == 0)
		{
			username.removeClass('system-openid');
			var text = JLanguage.LOGIN_WITH_OPENID;
			passwordTween.start(0, this.length);
		}
		else
		{
			username.addClass('system-openid');
			var text = JLanguage.NORMAL_LOGIN;
			passwordTween.start(this.length, 0);
		}

		passwordOpaqTween.start(state, 1-state);
		this.switcher.set('html', text);
	}
});

var JOpenID_com = new Class({

	state    : false,
	link     : null,
	switcher : null,

	initialize: function()
	{
		var switcher = new Element('a', { 'styles': {'cursor': 'pointer'},'id': 'com-openid-link'});
		switcher.inject($('com-form-login'));
		var link = new Element('a', { 'styles': {'text-align' : 'right', 'display' : 'block', 'font-size' : 'xx-small'}, 'href' : 'http://openid.net'});
		link.set('html', JLanguage.WHAT_IS_OPENID);

		//Initialise members
		this.switcher = switcher;
		this.link     = link;
		this.state    = Cookie.read('login-openid');
		this.length   = $('com-form-login-password').getSize().y;

		this.switch(this.state, 0);

		this.switcher.addEvent('click', (function(event) {
			this.state = this.state ^ 1;
			this.switch(this.state, 300);
			Cookie.write('login-openid', this.state);
		}).bind(this));
	},


	switch : function(state, time)
	{
		var password = $('com-form-login-password');
		var username = $('username');
		var passwordTween = new Fx.Tween(password, {'property': 'height'}, { 'duration': time });
		var passwordOpaqTween = new Fx.Tween(password, {'property': 'opacity'}, { 'duration': time });
		if(state == 0)
		{
			username.removeClass('system-openid');
			var text = JLanguage.LOGIN_WITH_OPENID;
			passwordTween.start(0, this.length);
		}
		else
		{
			username.addClass('system-openid');
			var text = JLanguage.NORMAL_LOGIN;
			passwordTween.start(this.length, 0);
		}

		passwordOpaqTween.start(state, 1-state);
		this.switcher.set('html', text);
	}
});


document.openid = null
document.com_openid = null
window.addEvent('domready', function(){
  if ($defined(modlogin)) {
  	var openid = new JOpenID();
  	document.openid = openid;
  }
  if ($defined(comlogin)) {
  	var com_openid = new JOpenID_com();
  	document.com_openid = openid;
  }
});
