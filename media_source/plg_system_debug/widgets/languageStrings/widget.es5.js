(function () {
    var csscls = PhpDebugBar.utils.makecsscls('phpdebugbar-widgets-')

    PhpDebugBar.Widgets.languageStringsWidget = PhpDebugBar.Widget.extend({
        tagName: 'table',

        className: csscls('languageStrings'),

        render: function () {
            this.bindAttr('data', function (data) {
                this.el.replaceChildren()

                Object.keys(data.orphans || {}).forEach(function (orphan) {
                    var row = document.createElement('tr')
                    var key = document.createElement('th')
                    var occurrences = document.createElement('td')
                    var list = document.createElement('ul')

                    key.textContent = orphan
                    key.style.verticalAlign = 'top'
                    key.style.width = '10%'

                    ;(data.orphans[orphan] || []).forEach(function (occurrence) {
                        var item = document.createElement('li')
                        var caller = occurrence.caller || ''
                        var relativePath = caller.replace(data.jroot, '')

                        if (data.xdebugLink && caller) {
                            var parts = caller.split(':')
                            var link = document.createElement('a')
                            link.textContent = relativePath
                            link.href = data.xdebugLink.replace('%f', parts[0]).replace('%l', parts[1] || '1')
                            item.append(link)
                        } else {
                            item.textContent = relativePath
                        }

                        if (occurrence.trace && occurrence.trace.length) {
                            var button = document.createElement('button')
                            var stack = document.createElement('table')
                            var header = document.createElement('tr')
                            var title = document.createElement('th')

                            button.type = 'button'
                            button.textContent = 'Stack'
                            button.addEventListener('click', function () {
                                stack.hidden = !stack.hidden
                            })

                            title.colSpan = 3
                            title.textContent = 'Call Stack'
                            header.append(title)
                            stack.append(header)
                            stack.classList.add(csscls('callstack'))
                            stack.hidden = true

                            occurrence.trace.forEach(function (entry) {
                                var traceRow = document.createElement('tr')
                                var depth = document.createElement('th')
                                var method = document.createElement('td')
                                var location = document.createElement('td')
                                var file = entry[3] || ''
                                var line = entry[4] || ''
                                var path = file.replace(data.jroot, '')

                                if (entry[1]) {
                                    traceRow.classList.add('caller')
                                }
                                depth.textContent = entry[0]
                                method.textContent = entry[2].replace(data.jroot, '')

                                if (file) {
                                    var locationText = path + (line ? ':' + line : '')

                                    if (data.xdebugLink) {
                                        var locationLink = document.createElement('a')
                                        locationLink.textContent = locationText
                                        locationLink.href = data.xdebugLink.replace('%f', file).replace('%l', line || '1')
                                        location.append(locationLink)
                                    } else {
                                        location.textContent = locationText
                                    }
                                }

                                traceRow.append(depth, method, location)
                                stack.append(traceRow)
                            })

                            item.append(button, stack)
                        }

                        list.append(item)
                    })

                    occurrences.append(list)
                    row.append(key, occurrences)
                    this.el.append(row)
                }, this)
            })
        }
    })
})()
