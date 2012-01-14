/** 
 * @package     Minima
 * @author      Marco Barbosa
 * @copyright   Copyright (C) 2010 Marco Barbosa. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

 MinimaToolbarClass = new Class({

    // implements
    Implements: [Options],

    // class options
    options: {
        'toolbar' : $('toolbar'), // toolbar parent
        'toolbarElements' : $$('.toolbar-list li a'), // list of the anchor elements
        'label' : null
    },

    // arrays
    bulkActionsArray: new Array(), // array with the actions
    bulkNonActionsArray: new Array(), // array with the other elements      
        
    // minima node
    minima : null,

    // initialize the class
    initialize: function(options){
        // set the main node for DOM selection
        this.minima = document.id(this.options.minima) || document.id('minima');
        // Set options
        this.setOptions(options);
    }, // end of initialize

    // function to be called to run the class
    doToolbar: function() {
        this.sortItems();
        this.fixToolbar();        
    }, // end of doToolbar

    // sort the items between actions and non actions
    sortItems: function() {  
        // save this for further reference      
        var _this = this;
        // if we have elements to sort
        if (this.options.toolbarElements.length) {
            // go through each of them
            this.options.toolbarElements.each(function(item) {
                // whatever has a 'if' clause in the onclick value is a bulk action
                if (item.get('onclick') != null && item.get('onclick').contains('if')) {                
                   _this.bulkActionsArray.push(item.getParent('li'));                
                } else if (item.get('class') != "divider") {
                   _this.bulkNonActionsArray.push(item.getParent('li'));
                }
            });
        }       
    }, // end of sortItems

    // fix the toolbar
    fixToolbar: function() {
        // save this for further reference
        var _this = this;
        // only proccess if we have more than one in bulkActionsArray
        if (this.bulkActionsArray.length > 1) {         
            // creating new elements            
            var 
                // actions <ul>
                bulkListChildren = new Element('ul#actions').hide(), 
                // create parent <li> that will toggle the new <ul>
                bulkListParent   = new Element('li', { 
                    'id': 'bulkActions',
                    'events': {
                        'click': function(event){
                            // toggle reveal the children                        
                            this.toggleReveal(bulkListChildren,{duration: 200, styles: ['border']});
                            // switch classes to active
                            $$(_this.minima.getElement('#bulkActions > a:first-child'), _this).switchClass('active', 'inactive');
                        },
                        'outerClick': function(){
                            // hide the children
                            bulkListChildren.dissolve({duration: 250});
                            // remove the classes to inactive
                            _this.minima.getElement('#bulkActions > a:first-child').set('class','inactive');
                        }
                    }
                }),
                // parent <a>                
                bulkListAnchor = new Element('a[text= '+ this.options.label +']'),
                // arrow <span>
                spanArrow = new Element('span.arrow');                

            // sort the list alphabetically
            this.bulkActionsArray = this.bulkActionsArray.sort(function (a, b) {
                if ( a.get("text").toLowerCase() < b.get("text").toLowerCase() ) return -1;
                if ( a.get("text").toLowerCase() == b.get("text").toLowerCase() ) return 0;
                return 1;
            });

            // then add the list items
            this.bulkActionsArray.each(function(item, index) {
                // grab the action item into the list
                bulkListChildren.grab(item);
            });

            // add the new parent <li>
            // check if there's a #toolbar-new button, the #actions goes right after that
            var liLocation = ( $('toolbar-new') ) ? 'ul > li#toolbar-new' : 'ul > li';

            // inject the new bulk <li> element after according to liLocation
            bulkListParent.inject($('toolbar').getElement(liLocation), 'after');
            
            // add the new anchor and the ul children            
            bulkListParent.adopt(bulkListAnchor.grab(spanArrow), bulkListChildren);

        } // end bulkActions.lenght     
    } // end of fixToolbar

});