(function ($) {

    var csscls = PhpDebugBar.utils.makecsscls('phpdebugbar-widgets-');
    var languageFilesWidget = PhpDebugBar.Widgets.languageFilesWidget = PhpDebugBar.Widget.extend({

        tagName: 'table',

        className: csscls('languageFiles'),

        render: function () {
            this.bindAttr('data', function (data) {
                this.$el.empty();
                var tr;
                for (var extension in data.loaded) {
                    var ul = $('<ul />');
                    for (var file in data.loaded[extension]) {
                        var css = data.loaded[extension][file] ? 'alert-success' : 'alert-warning';
                        var relPath = file.replace(data.jroot, 'JROOT')
                        var li = $('<li />')
                            .addClass(css)
                        if (data['xdebug-link']) {
                            var link = $('<a />')
                                .text(relPath)
                                .attr(
                                    'href',
                                    data['xdebug-link']
                                        .replace('%f', file)
                                        .replace('%l', '1')
                                )
                            li.append(link)
                        } else {
                            li.text(relPath)
                        }

                        li.appendTo(ul);
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
