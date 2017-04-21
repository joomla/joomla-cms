(function ($) {

    var csscls = PhpDebugBar.utils.makecsscls('phpdebugbar-widgets-');
    var languageFilesWidget = PhpDebugBar.Widgets.languageFilesWidget = PhpDebugBar.Widget.extend({

        tagName: 'table',

        className: csscls('languageFiles'),

        render: function () {
            this.bindAttr('data', function (data) {
                this.$el.empty();
                var tr;


                console.log(data);

                for (i in data) {
                    //console.log(i);
                    console.log(data[i]);
                    var ul = $('<ul />');
                    for (l in data[i]) {
                        console.log(data[i][l]);
                        var css = data[i][l] ? 'alert-success' : 'alert-warning';
                        //var span = $('<span />').addClass('label label-warning')
                        var li = $('<li />')
                            .addClass(css)
                            .text(l).appendTo(ul);
                    }
                    tr = $('<tr />')
                        .append($('<td />').text(i))
                        .append($('<td />').append(ul));
                    this.$el.append(tr);
                }

                return;

                for (var i = 0; i < data.length; i++) {
                    console.log(data[i]);
                    tr = $('<tr />')
                        .append($('<td />').text(data[i]))
                       // .append($('<td />').append(link));
                    this.$el.append(tr);

                   // var li = $('<li />').addClass(csscls('list-item')).appendTo(this.$el);
                   // this.get('itemRenderer')(li, data[i]);
                }

                return;


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
