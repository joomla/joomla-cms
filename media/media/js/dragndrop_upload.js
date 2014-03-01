
function sendFileToUploadController(formData,status)
{
	
    var uploadURL =document.getElementById('uploadForm').getProperty('action');
    var jqXHR=$.ajax({
            xhr: function() {
            var xhrobj = $.ajaxSettings.xhr();
            if (xhrobj.upload) {
                    xhrobj.upload.addEventListener('progress', function(event) {
                        var percent = 0;
                        var position = event.loaded || event.position;
                        var total = event.total;
                        if (event.lengthComputable) {
                            percent = Math.ceil(position / total * 100);
                        }
                        //Set progress
                        status.setProgress(percent);
                    }, false);
                }
            return xhrobj;
        },
    url: uploadURL,
    type: "POST",
    contentType:false,
    processData: false,
        cache: false,
        data: formData,
        success: function(data){
            status.setProgress(100);
          
        }
    }); 
 
    status.setAbort(jqXHR);
}
 

function createStatusbar(obj)
{
     this.statusbar = $("<tr></tr>");
     this.filename = $("<td 'width: 10%;'><div class='filename'></div></td>").appendTo(this.statusbar);
     this.size = $("<td 'width: 20%;'><div class='filesize'></div></td>").appendTo(this.statusbar);
     this.progressBar = $("<td style='width: 50%;'><div class='progress' ><div class='bar'></div></div></td>").appendTo(this.statusbar);
     this.abort = $("<td 'width: 10%;'><span class='badge badge-important'>&times;</span></td>").appendTo(this.statusbar);

     $("#upload-container").append(this.statusbar);
 
    this.setFileNameSize = function(name,size)
    {
        var sizeStr="";
        var sizeKB = size/1024;
        if(parseInt(sizeKB) > 1024)
        {
            var sizeMB = sizeKB/1024;
            sizeStr = sizeMB.toFixed(2)+" MB";
        }
        else
        {
            sizeStr = sizeKB.toFixed(2)+" KB";
        }
 
        this.filename.html(name);
        this.size.html(sizeStr);
    }
    this.setProgress = function(progress)
    {       
        var progressBarWidth =progress*this.progressBar.width()/ 100;  
        this.progressBar.find('.bar').animate({ width: progressBarWidth }, 10).html(progress + "% ");
        if(parseInt(progress) >= 100)
        {
         	this.abort.find('span').addClass('badge-success').removeClass('badge-important');
            this.abort.find('span').html('OK');
        }
    }
    this.setAbort = function(jqxhr)
    {
        var sb = this.statusbar;
        this.abort.click(function()
        {
       	    jqxhr.abort();
            sb.hide();
        });
    }
}
function handleFileUpload(files,obj)
{
   for (var i = 0; i < files.length; i++) 
   {
        var fd = new FormData();
        fd.append('Filedata[]', files[i]);
        fd.append('folder', document.getElementById('folder').value);
        fd.append(document.getElementById('form-token').value, '1');
        
        var status = new createStatusbar(obj); //To set progress.
        
        status.setFileNameSize(files[i].name,files[i].size);
        sendFileToUploadController(fd,status);
   }
   
   
  
}
$(document).ready(function()
{
var obj = $("#dragandrophandler");
obj.on('dragenter', function (e) 
{
    e.stopPropagation();
    e.preventDefault();
    $(this).css('border', '2px solid #0B85A1');
});
obj.on('dragover', function (e) 
{
     e.stopPropagation();
     e.preventDefault();
});
obj.on('drop', function (e) 
{
 
     $(this).css('border', '2px dotted #0B85A1');
     e.preventDefault();
     var files = e.originalEvent.dataTransfer.files;
 
     //We need to send dropped files to Server
     handleFileUpload(files,obj);
});
$(document).on('dragenter', function (e) 
{
    e.stopPropagation();
    e.preventDefault();
});
$(document).on('dragover', function (e) 
{
  e.stopPropagation();
  e.preventDefault();
  obj.css('border', '2px dotted #0B85A1');
});
$(document).on('drop', function (e) 
{
    e.stopPropagation();
    e.preventDefault();
});

// Reload folder iFrame when exit
$('#uploadModal').on('hide', function () {
	$('#folderframe').attr('src', function (i, val) { 
		// Setting folder name in iFrame url
		return val.replace(/&folder=.*&/,"&folder="+document.getElementById('folder').value+"&") ;
	});
});

});

