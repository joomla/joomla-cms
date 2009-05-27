/**
 * @version		$Id: openid.js 11793 2009-05-05 17:13:14Z pentacle $
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
 */
var JOpenID = new Class({
	state: false,
	switcher: null,
	length: null,
	form: null,
	passwordContainer: null,
	username: null,
	
	initialize: function(form) {
		this.form = $(form);
		this.username = this.form.getElement('input[name=username]');
		this.passwordContainer = this.form.getElement('input[name=passwd]').getParent();

		var switcher = new Element('a', {
			'styles': {'cursor': 'pointer'},
			'id': (this.form.id == 'com-form-login' ? 'com-' : '')+'openid-link',
			'html': JText._('LOGIN_WITH_OPENID'),
			'class': 'system-openid'
		});
		switcher.addEvent('click', (function(e) {
			this.state = this.state ^ 1;
			this.switch(300);
			Cookie.write('login-openid', this.state);
			return false;
		}).bind(this));
		switcher.inject(this.form);

		var link = new Element('a', {
			'styles': {'text-align' : 'right', 'display' : 'block', 'font-size' : 'xx-small'}, 
			'href' : 'http://openid.net'
		});
		link.set('html', JText._('WHAT_IS_OPENID'));
		link.inject(this.form);

		this.switcher = switcher;
		this.state    = Cookie.read('login-openid');
		this.length   = this.passwordContainer.getSize().y;
		if (this.state) {
			this.switch(0);
		}
	},
	switch : function(time) {
		var effect = new Fx.Morph(this.passwordContainer, {'duration': time});

		if (this.state == 0) {
			this.username.removeClass('system-openid');
			var text = JText._('LOGIN_WITH_OPENID');
			effect.start({
			    'height': [0, this.length],
			    'opacity': [this.state, 1-this.state]
			});
		}
		else {
			this.username.addClass('system-openid');
			var text = JText._('NORMAL_LOGIN');
			effect.start({
			    'height': [this.length, 0],
			    'opacity': [this.state, 1-this.state]
			});
		}
		this.switcher.set('html', text);
	}
});