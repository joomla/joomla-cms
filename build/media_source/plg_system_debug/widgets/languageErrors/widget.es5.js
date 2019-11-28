(function ($) {

    var csscls = PhpDebugBar.utils.makecsscls('phpdebugbar-widgets-')
    var languageErrorsWidget = PhpDebugBar.Widgets.languageErrorsWidget = PhpDebugBar.Widget.extend({

        tagName: 'ul',

        className: csscls('languageErrors'),

        render: function () {
            this.bindAttr('data', function (data) {
                this.$el.empty()

                for (var file of data.files) {
                    var relPath = file[0].replace(data.jroot, '')
                    var li = $('<li />')
                    if (data.xdebugLink) {
                        var link = $('<a />')
                            .text(relPath + ':' + file[1])
                            .attr(
                                'href',
                                data.xdebugLink
                                    .replace('%f', file[0])
                                    .replace('%l', file[1])
                            )
                        li.append(link)
                    } else {
                        li.text(relPath + ':' + file[1])
                    }
                    this.$el.append(li)
                }
            })
        }
    })
})(PhpDebugBar.$)
