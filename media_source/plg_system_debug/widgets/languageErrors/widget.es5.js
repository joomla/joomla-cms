(function () {
    var csscls = PhpDebugBar.utils.makecsscls('phpdebugbar-widgets-')
    PhpDebugBar.Widgets.languageErrorsWidget = PhpDebugBar.Widget.extend({

        tagName: 'ul',

        className: csscls('languageErrors'),

        render: function () {
            this.bindAttr('data', function (data) {
                this.el.replaceChildren()

                ;(data.files || []).forEach(function (file) {
                    var relativePath = file[0].replace(data.jroot, '')
                    var item = document.createElement('li')
                    var text = relativePath + ':' + file[1]

                    if (data.xdebugLink) {
                        var link = document.createElement('a')
                        link.textContent = text
                        link.href = data.xdebugLink
                            .replace('%f', file[0])
                            .replace('%l', file[1])
                        item.append(link)
                    } else {
                        item.textContent = text
                    }

                    this.el.append(item)
                }, this)
            })
        }
    })
})()
