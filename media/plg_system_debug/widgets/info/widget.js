(function ($) {

    var csscls = PhpDebugBar.utils.makecsscls('phpdebugbar-widgets-');
    var InfoWidget = PhpDebugBar.Widgets.InfoWidget = PhpDebugBar.Widget.extend({

        tagName: 'table',

        className: csscls('info'),

        render: function () {
            this.bindAttr('data', function (data) {
                this.$el.empty();
                var tr;

                var link = $('<a />')
                    .text('Info')
                    .attr('href', 'index.php?option=com_profiler&id=' + data.requestId)
                    .attr('target', '_blank');

                tr = $('<tr />')
                    .append($('<td />').text('Info'))
                    .append($('<td />').append(link));
                this.$el.append(tr);

                tr = $('<tr />')
                    .append($('<td />').text('Joomla! Version'))
                    .append($('<td />').text(data.joomlaVersion));
                this.$el.append(tr);

                tr = $('<tr />')
                    .append($('<td />').text('PHP Version'))
                    .append($('<td />').text(data.phpVersion));
                this.$el.append(tr);
            });
        }
    });
})(PhpDebugBar.$);
