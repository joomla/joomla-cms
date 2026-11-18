(function () {
    var csscls = PhpDebugBar.utils.makecsscls('phpdebugbar-widgets-')

    PhpDebugBar.Widgets.languageFilesWidget = PhpDebugBar.Widget.extend({
        tagName: 'table',

        className: csscls('languageFiles'),

        render: function () {
            this.bindAttr('data', function (data) {
                this.el.replaceChildren()

                var header = document.createElement('tr')
                var extensionHeader = document.createElement('th')
                var fileHeader = document.createElement('th')
                extensionHeader.textContent = 'Extension'
                fileHeader.textContent = 'File'
                header.append(extensionHeader, fileHeader)
                this.el.append(header)

                Object.keys(data.loaded || {}).forEach(function (extension) {
                    var row = document.createElement('tr')
                    var extensionCell = document.createElement('td')
                    var filesCell = document.createElement('td')
                    var list = document.createElement('ul')

                    extensionCell.textContent = extension

                    Object.keys(data.loaded[extension] || {}).forEach(function (file) {
                        var loaded = data.loaded[extension][file]
                        var item = document.createElement('li')
                        var path = file.replace(data.jroot, '')
                        var text = (loaded ? '+ ' : '- ') + path

                        item.classList.add(loaded ? 'alert-success' : 'alert-warning')

                        if (data.xdebugLink) {
                            var link = document.createElement('a')
                            link.textContent = text
                            link.href = data.xdebugLink.replace('%f', file).replace('%l', '1')
                            item.append(link)
                        } else {
                            item.textContent = text
                        }

                        list.append(item)
                    })

                    filesCell.append(list)
                    row.append(extensionCell, filesCell)
                    this.el.append(row)
                }, this)
            })
        }
    })
})()
