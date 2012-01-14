/** 
 * @package     Minima
 * @author      Henrik Hussfelt, Marco Barbosa
 * @copyright   Copyright (C) 2010 Marco Barbosa. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 *				This Class is mainly for code-readability.
 */

var MinimaClass = new Class({
    
    Implements: [Options],

    options: {
    },

    elements: {
        systemMessage : null,
        jformTitle    : null
    },    

    // minima node
    minima : null,

    initialize: function(options, elements){
       // set the main node for DOM selection
        this.minima = document.id(this.options.minima) || document.id('minima');
        // Set options
        this.setOptions(options);
        // Set elements
        this.elements = elements;
    },

    showSystemMessage: function() {
        // system-message fade
        if (this.elements.systemMessage && this.elements.systemMessage.getElement("ul li:last-child")) {
            var _this = this,
                hideAnchor = new Element('a', {
                'href': '#',
                'id': 'hide-system-message',
                'html': 'hide',
                'events': {
                    'click': function(e){
                        _this.elements.systemMessage.dissolve({duration: 'short'})
                    }
                }
            });
            // inject hideAnchor in the system-message container
            this.elements.systemMessage.show().getElement("ul li:last-child").adopt(hideAnchor);
        };
    },

    dynamicTitle: function() {        
        
        // save the h2 element
        var h2Title     = this.minima.getElement('.pagetitle h2'),
            jformAlias  = $('jform_alias'),
            _this       = this;

        // change the h2 title dynamically
        // set the title of the page with the jform_title
        if(this.elements.jformTitle.get("value") != "") h2Title.set('html', this.elements.jformTitle.get("value"));
        
        // change while typing it
        this.elements.jformTitle.addEvent('keyup', function(event){
            // show h2 with the title typed
            if (_this.elements.jformTitle.get("value") != ""){
               h2Title.set('html', this.get("value"));
            }
            //fix alias automatically, removing extra chars, all lower cased
            // but only if it's a new content
            if (_this.minima.hasClass('no-id') && jformAlias) {
                jformAlias.set( 'value', this.get("value").standardize().replace(/\s+/g, '-').replace(/[^-\w]+/g, '').toLowerCase() );
            }
        });
        
    },

    makeRowsClickable: function() {
        // get the toggle element
        var toggle = $$('input[name=checkall-toggle]');        
        // add the real click event
        toggle.addEvent('click', function(){
            var rows = $$('.adminlist tbody tr');
            rows.toggleClass('selected');
        });

        $$('.adminlist tbody tr input[type=checkbox]').each(function(element){
                
            var parent = element.getParent('tr'), // get parent                
                boxchecked = $$('input[name=boxchecked]'); // get boxchecked

            // add click event
            element.addEvent('click', function(event){
                event && event.stopPropagation();

                if (element.checked) {
                    parent.addClass('selected');
                } else {
                    parent.removeClass('selected');
                }
            });

            // add click event
            parent.addEvent('click', function(){
                if (element.checked) {
                    element.set('checked', false);
                    boxchecked.set('value',0)
                }else{
                    element.set('checked', true);
                    boxchecked.set('value', 1);
                }
                element.fireEvent('click');
            });

        });

        // highlight the sorting column
        $$('.adminlist th img').getParent('th').addClass('active');
    }
    
});    