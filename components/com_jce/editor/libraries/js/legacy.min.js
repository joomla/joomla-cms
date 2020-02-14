(function($, Wf) {
    $.Plugin = Wf;
    $.String = Wf.String;
    $.Cookie = Wf.Cookie;
    $.URL = Wf.URL;
    $.JSON = Wf.JSON;
    $.Dialog = Wf.Modal;
})(jQuery, Wf);

var WFMediaPlayer = WFExtensions.add('MediaPlayer', {
    /**
     * Parameter Object
     */
    params: {
        extensions: 'flv,f4v',
        dimensions: {},
        path: ''
    },

    type: 'flash',

    init: function(o) {
        tinymce.extend(this, o);

        // return the MediaPlayer object
        return this;
    },

    setup: function() {},

    getTitle: function() {
        return this.title || this.name;
    },

    getType: function() {
        return this.type;
    },

    /**
     * Check whether a media type is supported
     */
    isSupported: function() {
        return false;
    },

    /**
     * Return a player parameter value
     * @param {String} Parameter
     */
    getParam: function(param) {
        return this.params[param] || '';
    },

    /**
     * Set Player Parameters
     * @param {Object} o Parameter Object
     */
    setParams: function(o) {
        tinymce.extend(this.params, o);
    },

    /**
     * Return the player path
     */
    getPath: function() {
        return this.getParam('path');
    },

    onSelectFile: function(file) {},

    onInsert: function() {},

    onChangeType: function() {}

});