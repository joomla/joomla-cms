var MinimaFilterBarClass = new Class({

    Implements: [Options],

    options: {},

    // minima node
    minima: null,

    // labels statuses strings
    filterStatusLabels : {        
        "true": "Hide search & filters",
        "false":  "Show search & filters"
    },

    // boolean that tells if the filter is active or not
    isActive : false,

    // page title string
    pageTitle : "",

    // dom elements
    elements: {
        filterBar : null
    },
    
    // the filter elements
    filterSlide  : null,
    filterAnchor : null,

    // constructor
    initialize: function(options, elements, lang) {        
        // set the main node for DOM selection
        this.minima = $(this.options.minima) || $('minima');
        // set options
        this.setOptions(options);
        // set elements
        this.elements = elements;
        // set the labels
        if (lang.length) {
            this.setLabelsLanguage(lang['hideFilter'], lang['showFilter']);                
        }
    },

    // set the language
    setLabelsLanguage: function(hideFilter, showFilter) {        
        if (hideFilter.length && showFilter.length) {            
            this.filterStatusLabels['true']  = hideFilter;
            this.filterStatusLabels['false'] = showFilter;
        }
    },

    createSlideElements: function() {
        var _this = this;
        this.filterSlide  = new Fx.Slide(this.elements.filterBar).hide();
        this.filterAnchor = new Element('a', {
                                    'href': '#minima',
                                    'id': 'open-filter',
                                    'html': _this.filterStatusLabels['false'],                                    
                                    'events': {
                                        'click': function(e){
                                            var filterSearch = $('filter_search');
                                            e.stop();
                                            _this.filterSlide.toggle();
                                            this.toggleClass("active");                    
                                            if (this.hasClass("active") && filterSearch) {
                                                filterSearch.focus();
                                            }; 
                                            if ($('content-top').hasClass('fixed')) {
                                                window.scrollTo(0,0);
                                            };
                                        }
                                    }
                                });        
    },

    // put the new anchor in place
    fixAnchor: function() {
        this.minima.getElement('.pagetitle').grab(this.filterAnchor);        
    },

    // when filters change
    onFilterSelected: function() {
        var _this  = this;
        filterBar.getElements('input, select').each(function(el) {
            var value = el.get('value');
            if (value) {
                _this.isActive = true;
                _this.pageTitle += ( el.get('tag').toLowerCase() == "select" ) ?
                    el.getElement("option:selected").get("html").toLowerCase() + " " : _this.pageTitle += value.toLowerCase() + " ";
                _this.addFiltersToTitle();
            };
        });
    },

    // change title adding the filters as well
    addFiltersToTitle: function() {
        // and change <h2> showing the selected filters
        var h2Title = minima.getElement('.pagetitle h2');
        // if the string contains something
        if (this.pageTitle.length) {
            // change the h2 with the new string
            // don't add "- select -" strings
            if (!this.pageTitle.contains("-")) {
                h2Title.set( 'html', h2Title.get('html') + "<em>( "+this.pageTitle+")</em>" );
            }
        };
    },

    doFilterBar: function() {        
        var _this = this;
        // create the new elements necessary to work
        this.createSlideElements();
        // move anchor to proper place
        this.fixAnchor();
        // attach the listener to the inputs
        this.onFilterSelected();
        // do stuff if it's active
        if (this.isActive) {
            this.filterSlide.show(); 
            this.filterAnchor.set('html', this.filterStatusLabels[this.filterSlide.open]).addClass("active"); 
        };
        // toggle the css class and the status label (show/hide)
        this.filterSlide.addEvent('complete', function() {
            _this.filterAnchor.set('html', _this.filterStatusLabels[_this.filterSlide.open]);
        });
        // all prepared, show the filter-bar
        this.elements.filterBar.show();
        
    }

});