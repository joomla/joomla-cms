(function () {
    var csscls = PhpDebugBar.utils.makecsscls('phpdebugbar-widgets-')
    PhpDebugBar.Widgets.InfoWidget = PhpDebugBar.Widget.extend({

        tagName: 'table',

        className: csscls('info'),

        render: function () {
            this.bindAttr('data', function (data) {
                this.el.replaceChildren()

                var addRow = function (label, value) {
                    var row = document.createElement('tr')
                    var heading = document.createElement('td')
                    var content = document.createElement('td')

                    heading.textContent = label
                    content.textContent = value == null ? '' : value
                    row.append(heading, content)
                    this.el.append(row)
                }.bind(this)

                addRow('Joomla! Version', data.joomlaVersion)
                addRow('PHP Version', data.phpVersion)
                addRow('Identity', data.identity.type)
                addRow('Response', data.response.status_code)
                addRow('Template', data.template.template)

                var row = document.createElement('tr')
                var label = document.createElement('td')
                var details = document.createElement('td')
                var list = document.createElement('dl')

                label.textContent = 'Database'
                ;[
                    ['Server', data.database.dbserver],
                    ['Version', data.database.dbversion],
                    ['Collation', data.database.dbcollation],
                    ['Conn Collation', data.database.dbconnectioncollation]
                ].forEach(function (item) {
                    var term = document.createElement('dt')
                    var description = document.createElement('dd')

                    term.textContent = item[0]
                    description.textContent = item[1] == null ? '' : item[1]
                    list.append(term, description)
                })

                details.append(list)
                row.append(label, details)
                this.el.append(row)
            })
        }
    })
})()
