/**
 * @package Joomla.Installation
 * @subpackage JavaScript
 * @copyright Copyright (C) 2005 - 2012 Open Source Matters. All rights
 *            reserved.
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

/* jslint plusplus: true, browser: true, sloppy: true */
/* global jQuery, Request, Joomla, alert, Backbone */

// Require.js allows us to configure shortcut alias
require.config({

	// The shim config allows us to configure dependencies for
	// scripts that do not call define() to register a module
	shim : {
		'jquery' : {
			exports : '$'
		},
		'bootstrap' : {
			deps : [ 'jquery', ]
		},
		'chosen' : {
			deps : [ 'jquery', ]
		},
		'underscore' : {
			exports : '_',
			deps : [ 'jquery', ]
		},
		'backbone' : {
			deps : [ 'underscore', ],
			exports : 'Backbone'
		},

	},

	paths : {
		domready : 'template/js/lib/RequireJS/domReady.min',
		jquery : '../media/jui/js/jquery',
		bootstrap : '../media/jui/js/bootstrap.min',
		chosen : '../media/jui/js/chosen.jquery.min',
		underscore : 'template/js/lib/underscore/underscore',
		backbone : 'template/js/lib/backbone/backbone.min',
		serialize : 'template/js/lib/jquery/jquery.ba-serializeobject.min',
		uiinit : 'template/js/helpers/uiinit'
	}
});

require([ "jquery", "domready", "uiinit", "template/js/views/installation" ],
		function($, domReady, UiInit, InstallationView) {

			domReady(function() {

				(new InstallationView({
					el : $('#container-installation')
				}));

			});

		});
