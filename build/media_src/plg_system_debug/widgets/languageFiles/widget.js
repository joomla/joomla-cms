(function ($) {

    var csscls = PhpDebugBar.utils.makecsscls('phpdebugbar-widgets-');
    var languageFilesWidget = PhpDebugBar.Widgets.languageFilesWidget = PhpDebugBar.Widget.extend({

        tagName: 'table',

        className: csscls('languageFiles'),

        render: function () {
            this.bindAttr('data', function (data) {
                this.$el.empty();
                var tr;
                for (var extension in data) {
                    var ul = $('<ul />');
                    for (var file in data[extension]) {
                        var css = data[extension][file] ? 'alert-success' : 'alert-warning';
                        var li = $('<li />')
                            .addClass(css)
                            .text(file).appendTo(ul);
                    }
                    tr = $('<tr />')
                        .append($('<td />').text(extension))
                        .append($('<td />').append(ul));
                    this.$el.append(tr);
                }
            });
        }
    });
})(PhpDebugBar.$);
