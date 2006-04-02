function Xajax()
{
	if (xajaxDebug) this.DebugMessage = function(text) { alert("Xajax Debug:\n " + text) };
	
	this.workId = 'xajaxWork'+ new Date().getTime();
	this.depth = 0;
	
	//Get the XMLHttpRequest Object
	this.getRequestObject = function()
	{
		if (xajaxDebug) this.DebugMessage("Initializing Request Object..");
		var req;
		try
		{
			req=new ActiveXObject("Msxml2.XMLHTTP");
		}
		catch (e)
		{
			try
			{
				req=new ActiveXObject("Microsoft.XMLHTTP");
			}
			catch (e2)
			{
				req=null;
			}
		}
		if(!req && typeof XMLHttpRequest != "undefined")
			req = new XMLHttpRequest();
		
			if (xajaxDebug) {
				if (!req) this.DebugMessage("Request Object Instantiation failed.");
			}
			
		return req;
	}

	// xajax.$() is shorthand for document.getElementById()
	this.$ = function(sId)
	{
		if (!sId) {
			return null;
		}
		var returnObj = document.getElementById(sId);
		if (xajaxDebug && !returnObj && sId != this.workId) {
			this.DebugMessage("Element with the id \"" + sId + "\" not found.");
		}
		return returnObj;
	}
	
	// xajax.include(sFileName) dynamically includes an external javascript file
	this.include = function(sFileName)
	{
		var objHead = document.getElementsByTagName('head');
		var objScript = document.createElement('script');
		objScript.type = 'text/javascript';
		objScript.src = sFileName;
		objHead[0].appendChild(objScript);
	}
	
	// xajax.addHandler adds an event handler to an element
	this.addHandler = function(sElementId, sEvent, sFunctionName)
	{
		if (window.addEventListener)
		{
			eval("this.$('"+sElementId+"').addEventListener('"+sEvent+"',"+sFunctionName+",false);");
		}
		else
		{
			eval("this.$('"+sElementId+"').attachEvent('on"+sEvent+"',"+sFunctionName+",false);");
		}
	}
	
	// xajax.removeHandler removes an event handler from an element
	this.removeHandler = function(sElementId, sEvent, sFunctionName)
	{
		if (window.addEventListener)
		{
			eval("this.$('"+sElementId+"').removeEventListener('"+sEvent+"',"+sFunctionName+",false);");
		}
		else
		{
			eval("this.$('"+sElementId+"').detachEvent('on"+sEvent+"',"+sFunctionName+",false);");
		}
	}
	
	// xajax.create creates a new child node under a parent
	this.create = function(sParentId, sTag, sId)
	{
		var objParent = this.$(sParentId);
		objElement = document.createElement(sTag);
		objElement.setAttribute('id',sId);
		objParent.appendChild(objElement);
	}
	
	// xajax.insert inserts a new node before another node
	this.insert = function(sBeforeId, sTag, sId)
	{
		var objSibling = this.$(sBeforeId);
		objElement = document.createElement(sTag);
		objElement.setAttribute('id',sId);
		objSibling.parentNode.insertBefore(objElement, objSibling);
	}
	
	this.getInput = function(sType, sName, sId)
	{
		var Obj;
		if (sType == "radio" && !window.addEventListener)
		{
			Obj = document.createElement('<input type="radio" id="'+sId+'" name="'+sName+'">');
		}
		else
		{
			Obj = document.createElement('input');
			Obj.setAttribute('type',sType);
			Obj.setAttribute('name',sName);
			Obj.setAttribute('id',sId);
		}
		return Obj;
	}
	
	// xajax.createInput creates a new input node under a parent
	this.createInput = function(sParentId, sType, sName, sId)
	{
		var objParent = this.$(sParentId);
		var objElement = this.getInput(sType, sName, sId);
		objParent.appendChild(objElement);
	}
	
	// xajax.insertInput creates a new input node before another node
	this.insertInput = function(sBeforeId, sType, sName, sId)
	{
		var objSibling = this.$(sBeforeId);
		var objElement = this.getInput(sType, sName, sId);
		objSibling.parentNode.insertBefore(objElement, objSibling);
	}
	
	// xajax.remove deletes an element
	this.remove = function(sId)
	{
		objElement = this.$(sId);
		if (objElement.parentNode && objElement.parentNode.removeChild)
		{
			objElement.parentNode.removeChild(objElement);
		}
	}
	
	//xajax.replace searches for text in an attribute of an element and replaces it
	//with a different text
	this.replace = function(sId,sAttribute,sSearch,sReplace)
	{
		var bFunction = false;
		
		if (sAttribute == "innerHTML")
			sSearch = this.getBrowserHTML(sSearch);
		
		eval("var txt=document.getElementById('"+sId+"')."+sAttribute);
		if (typeof txt == "function")
        {
            txt = txt.toString();
            bFunction = true;
        }
		if (txt.indexOf(sSearch)>-1)
		{
			var newTxt = '';
			while (txt.indexOf(sSearch) > -1)
			{
				x = txt.indexOf(sSearch)+sSearch.length+1;
				newTxt += txt.substr(0,x).replace(sSearch,sReplace);
				txt = txt.substr(x,txt.length-x);
			}
			newTxt += txt;
			if (bFunction)
			{
				eval("newTxt =" + newTxt); 
				eval('this.$("'+sId+'").'+sAttribute+'=newTxt;');
			}
			else if (this.willChange(sId,sAttribute,newTxt))
			{
				eval('this.$("'+sId+'").'+sAttribute+'=newTxt;');
			}
		}
	}
	
	// xajax.getFormValues() builds a query string XML message from the elements of a form object
	this.getFormValues = function(frm)
	{
		var objForm;
		var submitDisabledElements = false;
		if (arguments.length > 1 && arguments[1] == true)
			submitDisabledElements = true;
		
		if (typeof(frm) == "string")
			objForm = this.$(frm);
		else
			objForm = frm;
		var sXml = "<xjxquery><q>";
		if (objForm && objForm.tagName == 'FORM')
		{
			var formElements = objForm.elements;
			for( var i=0; i < formElements.length; i++)
			{
				if (formElements[i].type && (formElements[i].type == 'radio' || formElements[i].type == 'checkbox') && formElements[i].checked == false)
					continue;
				if (formElements[i].disabled && formElements[i].disabled == true && submitDisabledElements == false) continue;
				var name = formElements[i].name;
				if (name)
				{
					if (sXml != '<xjxquery><q>')
						sXml += '&';
					if(formElements[i].type=='select-multiple')
					{
						for (var j = 0; j < formElements[i].length; j++)
						{
							if (formElements[i].options[j].selected == true)   sXml += name+"="+encodeURIComponent(formElements[i].options[j].value)+"&";
						}
					}
					else
					{
						sXml += name+"="+encodeURIComponent(formElements[i].value);
					}
				} 
			}
		}
		
		sXml +="</q></xjxquery>";
		
		return sXml;
	}
	
	// Generates an XML message that xajax can understand from a javascript object
	this.objectToXML = function(obj)
	{
		var sXml = "<xjxobj>";
		for (i in obj)
		{
			try
			{
				if (i == 'constructor')
					continue;
				if (obj[i] && typeof(obj[i]) == 'function')
					continue;
					
				var key = i;
				var value = obj[i];
				if (value && typeof(value)=="object" && 
					(value.constructor == Array
					 ) && this.depth <= 50)
				{
					this.depth++;
					value = this.objectToXML(value);
					this.depth--;
				}
				
				sXml += "<e><k>"+key+"</k><v>"+value+"</v></e>";
				
			}
			catch(e)
			{
				if (xajaxDebug) this.DebugMessage(e);
			}
		}
		sXml += "</xjxobj>";
	
		return sXml;
	}

	// Sends a XMLHttpRequest to call the specified PHP function on the server
	// * sRequestType is optional -- defaults to POST
	this.call = function(sFunction, aArgs, sRequestType)
	{
		var i,r,postData;
		if (document.body && xajaxWaitCursor)
			document.body.style.cursor = 'wait';
		if (xajaxStatusMessages == true) window.status = 'Sending Request...';
		if (xajaxDebug) this.DebugMessage("Starting xajax...");
		if (sRequestType == null) {
		   var xajaxRequestType = xajaxDefinedPost;
		}
		else {
			var xajaxRequestType = sRequestType;
		}
		var uri = xajaxRequestUri;
		var value;
		switch(xajaxRequestType)
		{
			case xajaxDefinedGet:{
				var uriGet = uri.indexOf("?")==-1?"?xajax="+encodeURIComponent(sFunction):"&xajax="+encodeURIComponent(sFunction);
				if (aArgs) {
					for (i = 0; i<aArgs.length; i++)
					{
						value = aArgs[i];
						if (typeof(value)=="object")
							value = this.objectToXML(value);
						uriGet += "&xajaxargs[]="+encodeURIComponent(value);
					}
				}
				uriGet += "&xajaxr=" + new Date().getTime();
				uri += uriGet;
				postData = null;
				} break;
			case xajaxDefinedPost:{
				postData = "xajax="+encodeURIComponent(sFunction);
				postData += "&xajaxr="+new Date().getTime();
				if (aArgs) {
					for (i = 0; i <aArgs.length; i++)
					{
						value = aArgs[i];
						if (typeof(value)=="object")
							value = this.objectToXML(value);
						postData = postData+"&xajaxargs[]="+encodeURIComponent(value);
					}
				}
				} break;
			default:
				alert("Illegal request type: " + xajaxRequestType); return false; break;
		}
		r = this.getRequestObject();
		if (!r) return false;
		r.open(xajaxRequestType==xajaxDefinedGet?"GET":"POST", uri, true);
		if (xajaxRequestType == xajaxDefinedPost)
		{
			try
			{
				r.setRequestHeader("Method", "POST " + uri + " HTTP/1.5");
				r.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
			}
			catch(e)
			{
				alert("Your browser does not appear to  support asynchronous requests using POST.");
				return false;
			}
		}
		r.onreadystatechange = function()
		{
			if (r.readyState != 4)
				return;
			
			if (r.status==200)
			{
				if (xajaxDebug && r.responseText.length < 1000) xajax.DebugMessage("Received:\n" + r.responseText);
				else if (xajaxDebug) xajax.DebugMessage("Received:\n" + r.responseText.substr(0,1000)+"...\n[long response]\n...</xajax>");
				if (r.responseXML)
					xajax.processResponse(r.responseXML);
				else {
					alert("Error: the XML response that was returned from the server is invalid.");
					document.body.style.cursor = 'default';
					if (xajaxStatusMessages == true) window.status = 'Invalid XML response error';				
				}
			}
			
			delete r;
		}
		if (xajaxDebug) this.DebugMessage("Calling "+sFunction +" uri="+uri+" (post:"+ postData +")");
		r.send(postData);
		if (xajaxStatusMessages == true) window.status = 'Waiting for data...';
		delete r;
		return true;
	}
	
	//Gets the text as it would be if it were being retrieved from
	//the innerHTML property in the current browser
	this.getBrowserHTML = function(html)
	{
		tmpXajax = this.$(this.workId);
		if (tmpXajax == null)
		{
			tmpXajax = document.createElement("div");
			tmpXajax.setAttribute('id',this.workId);
			tmpXajax.style.display = "none";
			tmpXajax.style.visibility = "hidden";
			document.body.appendChild(tmpXajax);
		}
		tmpXajax.innerHTML = html;
		var browserHTML = tmpXajax.innerHTML;
		tmpXajax.innerHTML = '';	
		
		return browserHTML;
	}
	
	// Tests if the new Data is the same as the extant data
	this.willChange = function(element, attribute, newData)
	{
		if (!document.body)
		{
			return true;
		}
		var oldData;
		if (attribute == "innerHTML")
		{
			newData = this.getBrowserHTML(newData);
		}
		eval("oldData=document.getElementById('"+element+"')."+attribute);
		if (newData != oldData)
			return true;
			
		return false;
	}
	
	//Process XML xajaxResponses returned from the request
	this.processResponse = function(xml)
	{
		if (xajaxStatusMessages == true) window.status = 'Processing...';
		var tmpXajax = null;
		xml = xml.documentElement;
		if (xml == null) {
			alert("Error: the XML response that was returned from the server cannot be processed.");
			document.body.style.cursor = 'default';
			if (xajaxStatusMessages == true) window.status = 'XML response processing error';
			return;
		}
		for (i=0; i<xml.childNodes.length; i++)
		{
			if (xml.childNodes[i].nodeName == "cmd")
			{
				var cmd;
				var id;
				var property;
				var data;
				var search;
				var type;
				var before;
				
				for (j=0; j<xml.childNodes[i].attributes.length; j++)
				{
					if (xml.childNodes[i].attributes[j].name == "n")
					{
						cmd = xml.childNodes[i].attributes[j].value;
					}
					if (xml.childNodes[i].attributes[j].name == "t")
					{
						id = xml.childNodes[i].attributes[j].value;
					}
					if (xml.childNodes[i].attributes[j].name == "p")
					{
						property = xml.childNodes[i].attributes[j].value;
					}
					if (xml.childNodes[i].attributes[j].name == "c")
					{
						type = xml.childNodes[i].attributes[j].value;
					}
				}
				if (xml.childNodes[i].childNodes.length > 1)
				{
					for (j=0; j<xml.childNodes[i].childNodes.length; j++)
					{
						if (xml.childNodes[i].childNodes[j].nodeName == "s")
						{
							if (xml.childNodes[i].childNodes[j].firstChild)
								search = xml.childNodes[i].childNodes[j].firstChild.nodeValue;
						}
						if (xml.childNodes[i].childNodes[j].nodeName == "r")
						{
							if (xml.childNodes[i].childNodes[j].firstChild)
								data = xml.childNodes[i].childNodes[j].firstChild.data;
						}
					}
				}
				else if (xml.childNodes[i].firstChild)
					data = xml.childNodes[i].firstChild.nodeValue;
				else
					data = "";
				
				var objElement = this.$(id);
				try
				{
					if (cmd=="al")
					{
						alert(data);
					}
					if (cmd=="js")
					{
						eval(data);
					}
					if (cmd=="in")
					{
						this.include(data);
					}
					if (cmd=="as")
					{
						if (this.willChange(id,property,data))
						{
							eval("objElement."+property+"=data;");
						}
					}
					if (cmd=="ap")
					{
						eval("objElement."+property+"+=data;");
					}
					if (cmd=="pp")
					{
						eval("objElement."+property+"=data+objElement."+property);
					}
					if (cmd=="rp")
					{
						this.replace(id,property,search,data)
					}
					if (cmd=="rm")
					{
						this.remove(id);
					}
					if (cmd=="ce")
					{
						this.create(id,data,property);
					}
					if (cmd=="ie")
					{
						this.insert(id,data,property);
					}
					if (cmd=="ci")
					{
						this.createInput(id,type,data,property);
					}
					if (cmd=="ii")
					{
						this.insertInput(id,type,data,property);
					}
					if (cmd=="ev")
					{
						eval("this.$('"+id+"')."+property+"= function(){"+data+";}");
					}
					if (cmd=="ah")
					{
						this.addHandler(id, property, data);
					}
					if (cmd=="rh")
					{
						this.removeHandler(id, property, data);
					}
				}
				catch(e)
				{
					alert(e);
				}
				delete objElement;
				delete cmd;
				delete id;
				delete property;
				delete search;
				delete data;
				delete type;
				delete before;
			}	
		}
		delete xml;
		document.body.style.cursor = 'default';
		if (xajaxStatusMessages == true) window.status = 'Done';
	}
}

var xajax = new Xajax();