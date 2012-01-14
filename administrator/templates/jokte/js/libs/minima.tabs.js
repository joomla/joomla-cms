/** 
 * @package     Minima
 * @author      Henrik Hussfelt, Marco Barbosa
 * @copyright   Copyright (C) 2010 Marco Barbosa. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

var MinimaTabsClass = new Class({
        
    Implements: [Options],

    options: {
    },

    elements: {
        tabs     : null,
        content  : null
    },
        
    initialize: function(options, elements){
        // Set options
        this.setOptions(options);
        // Set elements
        this.elements = elements;
    },

    // move outside tabs the proper place
    moveTabs: function(el) {        
        // the #submenu should have a .minimaTabs class
        //this.elements.subMenu.addClass('minima-tabs');            
        // move the tbas to the right place
        // which is above the title and toolbar-box
        el.inject( $('content'),'top' );
    },

    // shows the first tab content
    showFirst: function() {
        // Show first
        this.elements.content.pick().removeClass('hide');
    },

    // hide all contents
    hideAllContent: function() {
        // Hide all
        this.elements.content.addClass('hide');
    },

    // attaches the tabs actions
    addTabsAction: function() {
        // save the context
        var _this = this;            
        // go through each tab and do the magic
        this.elements.tabs.each(function(tab, index){                
            tab.addEvents({
                click: function(e){                        
                    // Stop the event
                    e.stop();
                    // Remove class active from all tabs
                    _this.elements.tabs.removeClass('active');
                    // Add class to clicked element
                    _this.elements.tabs[index].addClass('active');
                    // Hide the content
                    _this.elements.content.addClass('hide');
                    // Add class to clicked elements content
                    _this.elements.content[index].removeClass('hide');
                }
            }); //end of tab.addEvents
        }); // end of tabs.each
    }
    
});