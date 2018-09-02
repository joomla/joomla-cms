(function ($) {

    var csscls = PhpDebugBar.utils.makecsscls('phpdebugbar-widgets-')
    var languageFilesWidget = PhpDebugBar.Widgets.languageFilesWidget = PhpDebugBar.Widget.extend({

        tagName: 'table',

        className: csscls('languageFiles'),

        render: function () {
            this.bindAttr('data', function (data) {
                this.$el.empty()
                var head = $('<tr />')
                    .append($('<th />').text('Extension'))
                    .append($('<th />').text('File'))
                this.$el.append(head)
                for (var extension in data.loaded) {
                    var ul = $('<ul />')
                    for (var file in data.loaded[extension]) {
                        var css = data.loaded[extension][file] ? 'alert-success' : 'alert-warning'
                        var status = data.loaded[extension][file] ? '+' : '-'
                        var relPath = status + ' ' + file.replace(data.jroot, '')
                        var li = $('<li />')
                            .addClass(css)
                        if (data.xdebugLink) {
                            var link = $('<a />')
                                .text(relPath)
                                .attr(
                                    'href',
                                    data.xdebugLink
                                        .replace('%f', file)
                                        .replace('%l', '1')
                                )
                            li.append(link)
                        } else {
                            li.text(relPath)
                        }

                        li.appendTo(ul)
                    }
                    var tr = $('<tr />')
                        .append($('<td />').text(extension))
                        .append($('<td />').append(ul))
                    this.$el.append(tr)
                }
            })
        }
    })
})(PhpDebugBar.$)
