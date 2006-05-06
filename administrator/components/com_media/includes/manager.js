/**
 * Functions for the ImageManager, used by manager.php only	
 * @author $Author: Wei Zhuo $
 * @version $Id: manager.js 26 2004-03-31 02:35:21Z Wei Zhuo $
 * @package ImageManager
 */
	
	//initialise the form
	init = function () 
	{
		var uploadForm = document.getElementById('uploadForm');
		if(uploadForm) uploadForm.target = 'imgManager';

		var param = window.dialogArguments;
		if (param) 
		{
			document.getElementById("f_url").value = param["f_url"];
			document.getElementById("f_alt").value = param["f_alt"];
			document.getElementById("f_border").value = param["f_border"];
			document.getElementById("f_vert").value = param["f_vert"];
			document.getElementById("f_horiz").value = param["f_horiz"];
			document.getElementById("f_width").value = param["f_width"];
			document.getElementById("f_height").value = param["f_height"];
			setAlign(param["f_align"]);
		}

		document.getElementById("f_url").focus();
	}

	function onOK() 
	{
		// Get the image tag field information
		var url		= document.getElementById("f_url").value;
		var alt		= document.getElementById("f_alt").value;
		var border	= document.getElementById("f_border").value;
		var vert	= document.getElementById("f_vert").value;
		var horiz	= document.getElementById("f_horiz").value;
		var width	= document.getElementById("f_width").value;
		var height	= document.getElementById("f_height").value;
		var align	= document.getElementById("f_align").value;

		if (url != '') {
			// Set alt attribute
			if (alt != '') {
				alt = "alt='"+alt+"' ";
			}
			// Set border attribute
			if (border != '') {
				border = "border='"+border+"' ";
			}
			// Set width attribute
			if (width != '') {
				width = "width='"+width+"' ";
			}
			// Set height attribute
			if (height != '') {
				height = "height='"+height+"' ";
			}
			// Set align attribute
			if (align != '') {
				align = "align='"+align+"' ";
			}

			var tag = "<img src='"+url+"' "+alt+border+width+height+align+"/>";
		}
		
		window.parent.jInsertEditorText(tag);
		return false;
	};

	function populateFields(file) 
	{
		document.getElementById("f_url").value = "images/"+file;
	}


	function updateDir(selection) 
	{
		var newDir = selection.options[selection.selectedIndex].value;
		changeDir(newDir);
	}

	function goUpDir() 
	{
		var selection = document.getElementById('dirPath');
		var currentDir = selection.options[selection.selectedIndex].text;
		if(currentDir.length < 2)
			return false;
		var dirs = currentDir.split('/');
		
		var search = '';

		for(var i = 0; i < dirs.length - 2; i++)
		{
			search += dirs[i]+'/';
		}

		for(var i = 0; i < selection.length; i++)
		{
			var thisDir = selection.options[i].text;
			if(thisDir == search)
			{
				selection.selectedIndex = i;
				var newDir = selection.options[i].value;
				changeDir(newDir);
				break;
			}
		}
	}

	function toggleConstrains(constrains) 
	{
		var lockImage = document.getElementById('imgLock');
		var constrains = document.getElementById('constrain_prop');

		if(constrains.checked) 
		{
			lockImage.src = "img/locked.gif";	
			checkConstrains('width') 
		}
		else
		{
			lockImage.src = "img/unlocked.gif";	
		}
	}

	function checkConstrains(changed) 
	{
		//alert(document.form1.constrain_prop);
		var constrains = document.getElementById('constrain_prop');
		
		if(constrains.checked) 
		{
			var obj = document.getElementById('orginal_width');
			var orginal_width = parseInt(obj.value);
			var obj = document.getElementById('orginal_height');
			var orginal_height = parseInt(obj.value);

			var widthObj = document.getElementById('f_width');
			var heightObj = document.getElementById('f_height');
			
			var width = parseInt(widthObj.value);
			var height = parseInt(heightObj.value);

			if(orginal_width > 0 && orginal_height > 0) 
			{
				if(changed == 'width' && width > 0) {
					heightObj.value = parseInt((width/orginal_width)*orginal_height);
				}

				if(changed == 'height' && height > 0) {
					widthObj.value = parseInt((height/orginal_height)*orginal_width);
				}
			}			
		}
	}

	function showMessage(newMessage) 
	{
		var message = document.getElementById('message');
		var messages = document.getElementById('messages');
		if(message.firstChild)
			message.removeChild(message.firstChild);

		message.appendChild(document.createTextNode(i18n(newMessage)));
		
		messages.style.display = "block";
	}

	function addEvent(obj, evType, fn)
	{ 
		if (obj.addEventListener) { obj.addEventListener(evType, fn, true); return true; } 
		else if (obj.attachEvent) {  var r = obj.attachEvent("on"+evType, fn);  return r;  } 
		else {  return false; } 
	} 

	function doUpload() 
	{
		
		var uploadForm = document.getElementById('uploadForm');
		if(uploadForm)
			showMessage('Uploading');
	}

	function refresh()
	{
		var selection = document.getElementById('dirPath');
		updateDir(selection);
	}


	function newFolder() 
	{
		var selection = document.getElementById('dirPath');
		var dir = selection.options[selection.selectedIndex].value;

		Dialog("newFolder.html", function(param) 
		{
			if (!param) // user must have pressed Cancel
				return false;
			else
			{
				var folder = param['f_foldername'];
				if(folder == thumbdir)
				{
					alert(i18n('Invalid folder name, please choose another folder name.'));
					return false;
				}

				if (folder && folder != '' && typeof imgManager != 'undefined') 
					imgManager.newFolder(dir, encodeURI(folder)); 
			}
		}, null);
	}

	addEvent(window, 'load', init);
