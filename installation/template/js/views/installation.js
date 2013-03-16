/**
 * @package Joomla.Installation
 * @subpackage JavaScript
 * @copyright Copyright (C) 2005 - 2012 Open Source Matters. All rights
 *            reserved.
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

/* jslint plusplus: true, browser: true, sloppy: true */
/* global jQuery, Request, Joomla, alert, Backbone */

define([ 'jquery', 'underscore', 'backbone' ], function($, _, Backbone) {

	var InstallationView = Backbone.View.extend({

		// The DOM events specific to an item.
		events : {
			'click #goToPageSiteButton':		'goToPageSiteButton',
			'click #goToPageDatabaseButton':	'goToPageDatabaseButton',
			'click #goToPageButton':			'goToPageButton',			
			'click #removeFolderButon':			'removeFolder',
			'click #submitformButton':			'submitform',
			'click #verifyFtpSettingsButon':	'verifyFtpSettings',
			'click #detectFtpRootButton':		'detectFtpRoot'
		},

		initialize : function() {
			this.sampleDataLoaded = false;
	        this.busy = false;
			this.spinner = new Spinner(this.$el);
	        this.baseUrl = base;
		},
		
		toggle: function toggle(id, el, value) {
			var val = $('input[name=jform['+el+']]:checked').value;
			if(val == value) {
				$(id).removeClass('hide');
			} else {
				$(id).addClass('hide');
			}
	    },
	    
	    goToPageSiteButton: function goToPageSiteButton() {
	    	
	    },
	    
	    goToPageDatabaseButton: function goToPageDatabaseButton() {
	    	
	    },
	    
	    goToPageButton: function goToPageButton() {
	    	
	    },
	    
	    removeFolder: function removeFolder() {
	    	
	    },
	    
	    submitform: function submitform() {
	    	
	    },
	    
	    verifyFtpSettings: function verifyFtpSettings() {
	    	
	    },
	    
	    detectFtpRoot: function detectFtpRoot() {
	    	
	    },
	    
	});

	return InstallationView;
});