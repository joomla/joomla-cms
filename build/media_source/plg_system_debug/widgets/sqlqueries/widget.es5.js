(function ($) {

    var csscls = PhpDebugBar.utils.makecsscls('phpdebugbar-widgets-')

    /**
     * Widget for the displaying sql queries
     *
     * Options:
     *  - data
     */
    var SQLQueriesWidget = PhpDebugBar.Widgets.SQLQueriesWidget = PhpDebugBar.Widget.extend({

        className: csscls('sqlqueries'),

        onFilterClick: function (el) {
            $(el).toggleClass(csscls('excluded'))

            var excludedLabels = []
            this.$toolbar.find(csscls('.filter') + csscls('.excluded')).each(function () {
                excludedLabels.push(this.rel)
            })

            this.$list.$el.find('li[connection=' + $(el).attr('rel') + ']').toggle()

            this.set('exclude', excludedLabels)
        },
        onFilterDupesClick: function (el) {
            $(el).toggleClass(csscls('excluded'))

            var excludedLabels = []
            this.$toolbar.find(csscls('.filter') + csscls('.excluded')).each(function () {
                excludedLabels.push(this.rel)
            })

            this.$list.$el.find('li[dupeindex=' + $(el).attr('rel') + ']').toggle()

            this.set('exclude', excludedLabels)
        },
        onCopyToClipboard: function (el) {
            var code = $(el).parent('li').find('code').get(0)
            var copy = function () {
                try {
                    document.execCommand('copy')
                    alert('Query copied to the clipboard')
                } catch (err) {
                    console.log('Oops, unable to copy')
                }
            }
            var select = function (node) {
                if (document.selection) {
                    var range = document.body.createTextRange()
                    range.moveToElementText(node)
                    range.select()
                } else if (window.getSelection) {
                    var range = document.createRange()
                    range.selectNodeContents(node)
                    window.getSelection().removeAllRanges()
                    window.getSelection().addRange(range)
                }
                copy()
                window.getSelection().removeAllRanges()
            }
            select(code)
        },
        render: function () {
            this.$status = $('<div />').addClass(csscls('status')).appendTo(this.$el)

            this.$toolbar = $('<div></div>').addClass(csscls('toolbar')).appendTo(this.$el)

            var filters = [], self = this

            this.$list = new PhpDebugBar.Widgets.ListWidget({
                itemRenderer: function (li, stmt) {
                    $('<code />').addClass(csscls('sql')).html(PhpDebugBar.Widgets.highlight(stmt.sql, 'sql')).appendTo(li)
                    if (stmt.duration_str) {
                        $('<span title="Duration" />').addClass(csscls('duration')).text(stmt.duration_str).appendTo(li)
                    }
                    if (stmt.memory_str) {
                        $('<span title="Memory usage" />').addClass(csscls('memory')).text(stmt.memory_str).appendTo(li)
                    }
                    if (typeof(stmt.row_count) != 'undefined') {
                        $('<span title="Row count" />').addClass(csscls('row-count')).text(stmt.row_count).appendTo(li)
                    }
                    if (typeof(stmt.stmt_id) != 'undefined' && stmt.stmt_id) {
                        $('<span title="Prepared statement ID" />').addClass(csscls('stmt-id')).text(stmt.stmt_id).appendTo(li)
                    }
                    if (stmt.connection) {
                        $('<span title="Connection" />').addClass(csscls('database')).text(stmt.connection).appendTo(li)
                        li.attr('connection', stmt.connection)
                        if ($.inArray(stmt.connection, filters) == -1) {
                            filters.push(stmt.connection)
                            $('<a />')
                                .addClass(csscls('filter'))
                                .text(stmt.connection)
                                .attr('rel', stmt.connection)
                                .on('click', function () {
                                    self.onFilterClick(this)
                                })
                                .appendTo(self.$toolbar)
                            if (filters.length > 1) {
                                self.$toolbar.show()
                                self.$list.$el.css('margin-bottom', '20px')
                            }
                        }
                    }
                    if (typeof(stmt.is_success) != 'undefined' && !stmt.is_success) {
                        li.addClass(csscls('error'))
                        li.append($('<span />').addClass(csscls('error')).text('[' + stmt.error_code + '] ' + stmt.error_message))
                    }

                    var tableParams;

                    function showTableParams() {
                        if (tableParams) {
                            tableParams.show();
                            return;
                        }

                        // Render table
                        tableParams = $('<table>').addClass(csscls('params')).appendTo(li);
                        tableParams.append('<tr><th colspan="3">Query Parameters</th></tr>');
                        tableParams.append('<tr><td>ID</td><td>Value</td><td>Data Type</td></tr>');

                        var pRow;
                        for (var key in stmt.params) {
                            pRow = stmt.params[key];
                            tableParams.append('<tr><td>' + key + '</td><td>' + pRow.value + '</td><td>'
                              + pRow.dataType + '</td></tr>');
                        }

                        tableParams.show();
                    }

                    if (stmt.params && !$.isEmptyObject(stmt.params)) {
                        var btnParams = $('<span title="Params" />')
                          .text('Params')
                          .addClass(csscls('eye'))
                          .css('cursor', 'pointer')
                          .on('click', function () {
                              if (tableParams && tableParams.is(':visible')) {
                                  tableParams.hide()
                                  btnParams.addClass(csscls('eye'))
                                  btnParams.removeClass(csscls('eye-dash'))
                              } else {
                                  showTableParams();
                                  btnParams.addClass(csscls('eye-dash'))
                                  btnParams.removeClass(csscls('eye'))
                              }
                          })
                          .appendTo(li)
                    }

                    var tableExplain;

                    function showTableExplain() {
                        if (tableExplain) {
                            tableExplain.show();
                            return;
                        }

                        // Render table
                        tableExplain = $('<table>').addClass(csscls('explain')).appendTo(li);
                        tableExplain.append('<tr><th>' + stmt.explain_col.join('</th><th>') + '</th></tr>');

                        var i, entry, cols;
                        for (i in stmt.explain) {
                            cols  = []
                            entry = stmt.explain[i];

                            stmt.explain_col.forEach(function (key){
                                cols.push(entry[key]);
                            });

                            tableExplain.append('<tr><td>' + cols.join('</td><td>') + '</td></tr>');
                        }

                        tableExplain.show();
                    }

                    if (stmt.explain && !$.isEmptyObject(stmt.explain)) {
                        var btnExplain = $('<span title="Explain" />')
                          .text('Explain')
                          .addClass(csscls('eye'))
                          .css('cursor', 'pointer')
                          .on('click', function () {
                              if (tableExplain && tableExplain.is(':visible')) {
                                  tableExplain.hide()
                                  btnExplain.addClass(csscls('eye'))
                                  btnExplain.removeClass(csscls('eye-dash'))
                              } else {
                                  showTableExplain();
                                  btnExplain.addClass(csscls('eye-dash'))
                                  btnExplain.removeClass(csscls('eye'))
                              }
                          })
                          .appendTo(li)
                    }

                    var tableStack;

                    function showTableStack() {
                        if (tableStack) {
                            tableStack.show();
                            return;
                        }

                        // Render table
                        tableStack = $('<table><tr><th colspan="3">Call Stack</th></tr></table>')
                          .addClass(csscls('callstack')).appendTo(li);

                        var i, entry, location, caller, cssClass;
                        for (i in stmt.callstack) {
                            entry = stmt.callstack[i]
                            location = entry[3] ? entry[3].replace(self.root_path, '') + ':' + entry[4] : ''
                            caller = entry[2].replace(self.root_path, '')
                            cssClass = entry[1] ? 'caller' : ''

                            if (location && self.xdebug_link) {
                                location = '<a href="' + self.xdebug_link.replace('%f', entry[3]).replace('%l', entry[4]) + '">' + location + '</a>'
                            }
                            tableStack.append('<tr class="' + cssClass + '"><th>' + entry[0] + '</th><td>' + caller + '</td><td>' + location + '</td></tr>')
                        }

                        tableStack.show();
                    }

                    if (stmt.callstack && !$.isEmptyObject(stmt.callstack)) {
                        var btnStack = $('<span title="Call Stack" />')
                            .text('Stack')
                            .addClass(csscls('eye'))
                            .css('cursor', 'pointer')
                            .on('click', function () {
                                if (tableStack && tableStack.is(':visible')) {
                                    tableStack.hide()
                                    btnStack.addClass(csscls('eye'))
                                    btnStack.removeClass(csscls('eye-dash'))
                                } else {
                                    showTableStack();
                                    btnStack.addClass(csscls('eye-dash'))
                                    btnStack.removeClass(csscls('eye'))
                                }
                            })
                            .appendTo(li)
                    }

                    if (typeof(stmt.caller) != 'undefined' && stmt.caller) {
                        var caller = stmt.caller.replace(self.root_path, '')
                        if (self.xdebug_link) {
                            var parts = stmt.caller.split(':')
                            $('<a />')
                                .text(caller)
                                .addClass(csscls('editor-link'))
                                .attr('href', self.xdebug_link.replace('%f', parts[0]).replace('%l', parts[1]))
                                .appendTo(li)
                        } else {
                            $('<span title="Caller" />')
                                .text(caller)
                                .addClass(csscls('stmt-id'))
                                .appendTo(li)
                        }
                    }

                    $('<span title="Copy to clipboard" />')
                        .text('Copy')
                        .addClass(csscls('copy-clipboard'))
                        .css('cursor', 'pointer')
                        .on('click', function (event) {
                            self.onCopyToClipboard(this)
                            event.stopPropagation()
                        })
                        .appendTo(li)

                    li.attr('dupeindex', 'dupe-0')
                }
            })
            this.$list.$el.appendTo(this.$el)

            this.bindAttr('data', function (data) {
                // the collector maybe is empty
                if (data.length <= 0) {
                    return false
                }

                this.root_path = data.root_path
                this.xdebug_link = data.xdebug_link
                this.$list.set('data', data.statements)
                this.$status.empty()

                // Search for duplicate statements.
                for (var sql = {}, unique = 0, duplicate = 0, i = 0; i < data.statements.length; i++) {
                    var stmt = data.statements[i].sql
                    if (data.statements[i].params && !$.isEmptyObject(data.statements[i].params)) {
                        stmt += ' {' + $.param(data.statements[i].params, false) + '}'
                    }
                    sql[stmt] = sql[stmt] || {keys: []}
                    sql[stmt].keys.push(i)
                }
                // Add classes to all duplicate SQL statements.
                var cnt = 0
                for (var stmt in sql) {
                    if (sql[stmt].keys.length > 1) {
                        duplicate += sql[stmt].keys.length
                        cnt++
                        for (var i = 0; i < sql[stmt].keys.length; i++) {
                            this.$list.$el.find('.' + csscls('list-item')).eq(sql[stmt].keys[i])
                                .addClass(csscls('sql-duplicate'))
                                .attr('dupeindex', 'dupe-' + cnt)
                        }
                    } else {
                        unique++
                    }
                }

                if (duplicate) {
                    for (i = 0; i <= cnt; i++) {
                        $('<a />')
                            .addClass(csscls('filter'))
                            .text(i ? 'Duplicates ' + i : 'Uniques')
                            .attr('rel', 'dupe-' + i)
                            .on('click', function () {
                                self.onFilterDupesClick(this)
                            })
                            .appendTo(self.$toolbar)
                    }
                    self.$toolbar.show()
                    self.$list.$el.css('margin-bottom', '20px')
                }

                var t = $('<span />').text(data.nb_statements + ' statements were executed').appendTo(this.$status)
                if (data.nb_failed_statements) {
                    t.append(', ' + data.nb_failed_statements + ' of which failed')
                }
                if (duplicate) {
                    t.append(', ' + duplicate + ' of which were duplicates')
                    t.append(', ' + unique + ' unique')
                }
                if (data.accumulated_duration_str) {
                    this.$status.append($('<span title="Accumulated duration" />').addClass(csscls('duration')).text(data.accumulated_duration_str))
                }
                if (data.memory_usage_str) {
                    this.$status.append($('<span title="Memory usage" />').addClass(csscls('memory')).text(data.memory_usage_str))
                }
            })
        }

    })

})(PhpDebugBar.$)
