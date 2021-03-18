(function ($) {

    var csscls = PhpDebugBar.utils.makecsscls('phpdebugbar-widgets-')
    var InfoWidget = PhpDebugBar.Widgets.InfoWidget = PhpDebugBar.Widget.extend({

        tagName: 'table',

        className: csscls('info'),

        render: function () {
            this.bindAttr('data', function (data) {
                this.$el.empty()
                var tr

                /*
                // @todo enable Info link
                var link = $('<a />')
                    .text('Info')
                    .attr('href', 'index.php?option=com_content&view=debug&id=' + data.requestId)
                    .attr('target', '_blank');

                tr = $('<tr />')
                    .append($('<td />').text('Info'))
                    .append($('<td />').append(link));
                this.$el.append(tr);
                */

                tr = $('<tr />')
                    .append($('<td />').text('Joomla! Version'))
                    .append($('<td />').text(data.joomlaVersion))
                this.$el.append(tr)

                tr = $('<tr />')
                    .append($('<td />').text('PHP Version'))
                    .append($('<td />').text(data.phpVersion))
                this.$el.append(tr)

                tr = $('<tr />')
                    .append($('<td />').text('Identity'))
                    .append($('<td />').text(data.identity.type))
                this.$el.append(tr)

                tr = $('<tr />')
                    .append($('<td />').text('Response'))
                    .append($('<td />').text(data.response.status_code))
                this.$el.append(tr)

                tr = $('<tr />')
                    .append($('<td />').text('Template'))
                    .append($('<td />').text(data.template.template))
                this.$el.append(tr)

                tr = $('<tr />')
                    .append($('<td />').text('Database'))
                    .append($('<td />').html(
                        '<dl>'
                        + '<dt>Server</dt><dd>' + data.database.dbserver + '</dd>'
                        + '<dt>Version</dt><dd>' + data.database.dbversion + '</dd>'
                        + '<dt>Collation</dt><dd>' + data.database.dbcollation + '</dd>'
                        + '<dt>Conn Collation</dt><dd>' + data.database.dbconnectioncollation + '</dd>'
                        + '</dl>'
                    ))
                this.$el.append(tr)
            })
        }
    })
})(PhpDebugBar.$)
