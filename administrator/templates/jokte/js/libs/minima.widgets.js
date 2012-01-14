/** 
 * @package     Minima
 * @author      Júlio Pontes
 * @author      Marco Barbosa
 * @copyright   Copyright (C) 2011 Júlio Pontes. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

var MinimaWidgetsClass = new Class({

    Implements: [Options],

    storage: null,
    
    options: {},

    // minima node
    minima : null,
    spinner: null,
    timeout: 0,

    // columns elements caching
    columns: {},
    // boxes elements caching
    boxes: {},
    
    initialize: function() {        
        // reset the localStorage
        //localStorage.clear();
        // set the main node for DOM selection
        this.minima = document.id(this.options.minima) || document.id('minima');
        // save the columns for caching
        this.columns = this.minima.getElements('.col');
        // if we have any column to work with..
        if (this.columns.length) {
            // create a spinner element
            this.spinner = new Spinner( document.id('content-cpanel') );
            // show the spinner
            this.spinner.show(true);
            // cache the boxes elements
            this.boxes = this.minima.getElements('.box');            
            // initialize LocalStorage
            this.storage = new LocalStorage();
            // load and prepare the saved positions
            this.loadPositions();
            // add widgets events
            // disabled - no menus for now!
            //this.addEvents();
            // attach the drag and drop events
            this.attachDrag();            
        } 
    },
    
    addEvents: function() {
        var that = this;
        this.boxes.each(function(widget){
            widgetId = widget.id.replace('widget-','');
            widget.getElement('a.nav-settings').addEvent('click',function(){
                that.settings(widgetId);
            });
            widget.getElement('a.nav-hide').addEvent('click',function(){
                if(widget.hasClass('expand')){
                    widget.removeClass('expand');
                    widget.addClass('minimize');
                    this.set('text','Show');
                }
                else if(widget.hasClass('minimize')){
                    widget.removeClass('minimize');
                    widget.addClass('expand');
                    this.set('text','Hide');
                }
            });
        });
    },
    
    loadPositions: function() {
        // get widgets from the storage
        widgets = this.storage.get('widgets');
        // get out if it's not set
        
        // storage at first time
        if (typeOf(widgets) == 'array' && widgets.length === 0) this.storagePositions();        
        
        if (typeOf(widgets) == 'array')
        {
            // show the loading spinner
            // loop through each column and fix it
            this.columns.each(function(position){
                widgets.each(function(widget, index){
                    if (widget.position == position.id) {
                        if( widget.id != "" )
                            $(position.id).grab($(widget.id));
                    }
                });
            });
        } else {
            // loop through each column and fix it
            var positionIndex = 0;
            var that = this;
            $$('.box').each(function(widgetElement, index){
                that.columns[positionIndex].grab(widgetElement);
                positionIndex++;
                if(positionIndex >= that.columns.length) positionIndex = 0;
            });
        }
        // all done, show them
        // hide the spinner
        this.spinner.hide(true); 
        // display the widgets one by one
        this.displayWidgets();
    },

    // animates the transition
    displayWidgets: function() {                
        // fade in the boxes
        this.boxes.each(function(el, i) {
            setTimeout(function() {                
                el.fade('in');
            }, 300 * (i * 1.5));
        });
    },
    
    storagePositions: function() {
        // ordernation array
        ordernation = new Array(); 
        this.columns.each(function(position){
            position.getElements('.box').each(function(widget, index){
                var widgetObj = {'id': widget.id,'order': index,'position': position.id};
                ordernation.push(widgetObj);
            });
        });
        this.storage.set('widgets',ordernation);
    },
    // attach the drag and drop events
    attachDrag: function(){
        var that = this;
        // create new sortables
        new Sortables( this.columns, {
            clone : true,
            handle : '.box-top',
            opacity: 0.6,
            revert: {
                duration: 500,
                trasition: 'elastic:out'
            },
            onStart: function() {
                // add class to body to tell it's dragging
                minima.addClass('onDrag');
            },
            onComplete: function(widget) {
                // not dragging anymore
                minima.removeClass('onDrag');
                // save the positions after dropping
                that.storagePositions();
            }
        });
    },
    
    // Config action to open a modal configuration of a module
    settings: function(id) {
        var url = 'index.php?option=com_modules&client_id=0&task=module.edit&id='+id+'&tmpl=component&view=module&layout=modal';
        SqueezeBox.open(url,{handler: 'iframe', size: {x: 900, y: 550}});
    }
    
});

window.addEvent('domready', function() {
    var MinimaWidgets = new MinimaWidgetsClass();
});