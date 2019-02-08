(function ($) {

    var csscls = PhpDebugBar.utils.makecsscls('phpdebugbar-widgets-')
    var languageStringsWidget = PhpDebugBar.Widgets.languageStringsWidget = PhpDebugBar.Widget.extend({

        tagName: 'table',

        className: csscls('languageStrings'),

        render: function () {
            this.bindAttr('data', function (data) {
                this.$el.empty()
                for (var orphan in data.orphans) {
                    var tr = $('<tr />')
                    $('<th valign="top" style="width:10%" />').text(orphan).appendTo(tr)
                    var td = $('<th />').appendTo(tr)

                    var ul = $('<ul />').appendTo(td)

                    var tableStack

                    for (var oc in data.orphans[orphan]) {
                        var occurence = data.orphans[orphan][oc]
                        var relPath = occurence['caller'].replace(data.jroot, '')

                        var li = $('<li />')

                        if (data.xdebugLink) {
                            var parts = occurence['caller'].split(':')
                            var link = $('<a />')
                                .text(relPath)
                                .attr(
                                    'href',
                                    data.xdebugLink
                                        .replace('%f', parts[0])
                                        .replace('%l', parts[1])
                                )
                            li.append(link)
                        } else {
                            li.text(relPath)
                        }

                        if (occurence['trace'] && !$.isEmptyObject(occurence['trace'])) {
                            $('<span title="Call Stack" />')
                                .text('Stack')
                                .addClass(csscls('eye'))
                                .css('cursor', 'pointer')
                                .on('click', function (e) {
                                    var btn = $(e.target)
                                    var table = btn.next()
                                    if (table.is(':visible')) {
                                        table.hide()
                                        btn.addClass(csscls('eye'))
                                        btn.removeClass(csscls('eye-dash'))
                                    } else {
                                        table.show()
                                        btn.addClass(csscls('eye-dash'))
                                        btn.removeClass(csscls('eye'))
                                    }
                                })
                                .appendTo(li)

                            tableStack = $('<table><thead><tr><th colspan="3">Call Stack</th></tr></thead></table>')
                                .addClass(csscls('callstack'))
                                .appendTo(li)

                            for (var i in occurence['trace']) {
                                var entry = occurence['trace'][i]
                                var location = entry[3] ? entry[3].replace(data.jroot, '') + ':' + entry[4] : ''
                                var caller = entry[2].replace(data.jroot, '')
                                var cssClass = entry[1] ? 'caller' : ''
                                if (location && data.xdebugLink) {
                                    location = '<a href="' + data.xdebugLink.replace('%f', entry[3]).replace('%l', entry[4]) + '">' + location + '</a>'
                                }
                                tableStack.append('<tr class="' + cssClass + '"><th>' + entry[0] + '</th><td>' + caller + '</td><td>' + location + '</td></tr>')
                            }
                        }

                        li.appendTo(ul)
                    }

                    this.$el.append(tr)
                }
            })
        }
    })
})(PhpDebugBar.$)
