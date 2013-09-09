/**
 * @copyright    Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * JMediaManager behavior for image editor
 *
 * @package        Joomla.Extensions
 * @subpackage  Media
 * @since        3.2
 */
jQuery(function($)
{
    $(document).ready(function()
    {
        var jcrop_api;
        $("#rotateleft").click(function(){ addToHistory('1'); ajaxCall('rotateLeft')});
        $("#rotateright").click(function(){ addToHistory('2'); ajaxCall('rotateRight')});
        $("#flipvertical").click(function(){ addToHistory('3'); ajaxCall('flipVertical')});
        $("#fliphorizontal").click(function(){ addToHistory('4'); ajaxCall('flipHorizontal')});
        $("#flipboth").click(function(){ addToHistory('5'); ajaxCall('flipBoth')});
        $("#undo").click(function(){ ajaxCall('undo', setUndo())});
        $("#redo").click(function(){ ajaxCall('redo', setRedo())});
        $("#save").click(function(){ ajaxCall('save')});
        $("#crop").click(function(){ ajaxCall('crop')});
        $("#resize").click(function(){ ajaxCall('resize')});
        $("#saveDuplicate").click(function(){ajaxCall('duplicate')});
        $("#resetRatio").click(function(){
            jcrop_api.setOptions({aspectRatio: ""});
            $("#ratio-x").val("");
            $("#ratio-y").val("");
        });

        $("#redo").attr('disabled', 'disabled');
        $("#undo").attr('disabled', 'disabled');
        $("#editing").Jcrop({
            bgColor: 'transparent',
            boxWidth: 500,
            boxHeight: 400,
            onChange: showCoords,
            onSelect: showCoords,
            onRelease:  clearCoords
        },function(){
            jcrop_api = this;

        });

        jcrop_api.setImage($("#fullPath").val());

        function showCoords(c)
        {
            $('#x1').val(Math.round(c.x));
            $('#y1').val(Math.round(c.y));
            $('#x2').val(Math.round(c.x2));
            $('#y2').val(Math.round(c.y2));
            $('#w').val(Math.round(c.w));
            $('#h').val(Math.round(c.h));
        };

        function clearCoords()
        {
            $('#coordinates input').val('');
        };

        $('#coordinates').on('change','input',function(e){
            var x1 = $('#x1').val(),
                x2 = $('#x2').val(),
                y1 = $('#y1').val(),
                y2 = $('#y2').val();
                jcrop_api.setSelect([x1,y1,x2,y2]);
        });

        $('#aspect-ratio').on('change','input',function(e){
            ratio_x = $("#ratio-x").val();
            ratio_y = $("#ratio-y").val();
            if (ratio_x =="") return;
            if (ratio_y =="") return;

            if ((!isNaN(ratio_x)) && (!isNaN(ratio_y))){
                var t = ratio_x / ratio_y;
               // alert(t);
                jcrop_api.setOptions({aspectRatio: t});
            }
        });

        function ajaxCall(operation, step){
            $token = $("#hidden_form").find("input:first").attr("name");
            $.ajax({
                type: "POST",
                url: "index.php?option=com_media&controller=ajax&format=raw",
                data: "editing="+ $("#editing_path").val()
                      + "&" + $token + "=1"
                      + "&isOriginal=" + $("#isOriginal").val()
                      + "&operation=" + operation
                      + "&step=" + step
                      + "&x1=" +    $('#x1').val()
                      + "&y1=" +    $('#y1').val()
                      + "&w=" +     $('#w').val()
                      + "&h=" +     $('#h').val()
                      + "&imageWidth=" +     $('#imageWidth').val()
                      + "&imageHeight=" +     $('#imageHeight').val()
                      + "&duplicateName=" +     $('#duplicateName').val(),
                success: function(result) {
                        var msg = JSON.parse(result);
                        var operation = msg.operation;
                        switch (operation){
                            case 'duplicate':
                                var duplicatePath = msg.duplicatePath;
                                if (duplicatePath=='false'){
                                    $("#duplicateAlert").html(msg['message']);
                                }else{
                                    window.location ='index.php?option=com_media&view=editor&editing=' + duplicatePath;
                                }
                                break;
                            default:
                                jcrop_api.setImage(msg['newimage']);
                                $("#isOriginal").val(1);
                                break;
                        }

                }
            });
        }

        function addToHistory(step){
            $("#current").val($("#current").val() + step);
            $("#history").val($("#current").val());
            $("#undo").removeAttr('disabled');
            $("#redo").attr('disabled', 'disabled');
        }

        function setUndo(){
            var current = $("#current");
            $res =  current.val().charAt(current.val().length - 1);
            current.val(current.val().substring(0, current.val().length-1));
            if (current.val() =="") {
                $("#undo").attr('disabled', 'disabled');
            }
            $("#redo").removeAttr('disabled');
            return $res;
        }

        function setRedo(){
            var current = $("#current");
            var history = $("#history");
            current.val(current.val() + history.val().charAt(current.val().length));
            if (current.val() == history.val()) $("#redo").attr('disabled', 'disabled');
            $("#undo").removeAttr('disabled');
            return current.val().charAt(current.val().length-1);
        }
    });
});