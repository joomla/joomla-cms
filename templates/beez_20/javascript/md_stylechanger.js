var prefsLoaded = false;
var defaultFontSize =100;
var currentFontSize = defaultFontSize;

if (document.addEventListener) {
	document.addEventListener("DOMContentLoaded", onLoadEvents, false)
} else {
	window.onload = onLoadEvents;
}

window.onunload = saveSettings;

function onLoadEvents() {
	setUserOptions();
	addControls();
};

function revertStyles() {
        currentFontSize = defaultFontSize;
        changeFontSize(0);
};

function toggleColors() {
        if(currentStyle == "White"){
                setColor("Black");
        }else{
                setColor("White");
        }
};

function changeFontSize(sizeDifference) {
        currentFontSize = parseInt(currentFontSize) + parseInt(sizeDifference * 5);

        if(currentFontSize > 180){
                currentFontSize = 180;
        }else if(currentFontSize < 60){
                currentFontSize = 60;
        }

        setFontSize(currentFontSize);

};

function setFontSize(fontSize) {
        var stObj = (document.getElementById) ? document.getElementById('content_area') : document.all('content_area');
        document.body.style.fontSize = fontSize + '%';
        $('header').style.fontSize=fontSize + '%';
};


function createCookie(name,value,days) {
  if (days) {
    var date = new Date();
    date.setTime(date.getTime()+(days*24*60*60*1000));
    var expires = "; expires="+date.toGMTString();
  }
  else expires = "";
  document.cookie = name+"="+value+expires+"; path=/";
};

function readCookie(name) {
  var nameEQ = name + "=";
  var ca = document.cookie.split(';');
  for(var i=0;i < ca.length;i++) {
    var c = ca[i];
    while (c.charAt(0)==' ') c = c.substring(1,c.length);
    if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
  }
  return null;
};

function setUserOptions(){
        if(!prefsLoaded){
                cookie = readCookie("fontSize");
                currentFontSize = cookie ? cookie : defaultFontSize;
                setFontSize(currentFontSize);

                prefsLoaded = true;
        }
};

function addControls() {
	var container = document.getElementById('fontsize');
	
	var link = new Array(4);
	var linkClass = new Array("larger", "reset", "smaller");
	var linkTitle = new Array(biggerTitle, resetTitle, smallerTitle);
	var linkAction = new Array("changeFontSize(2); return false;", "revertStyles(); return false;", "changeFontSize(-2); return false;");
	var linkContent = new Array(document.createTextNode(bigger), document.createTextNode(reset), document.createTextNode(smaller));
	
	var headingText = document.createTextNode(fontSizeTitle);
	
	if (document.createElementNS) {
		var heading = document.createElementNS("http://www.w3.org/1999/xhtml","h3");
		var p = document.createElementNS("http://www.w3.org/1999/xhtml","p");
		link[3] = document.createElementNS("http://www.w3.org/1999/xhtml","a");
		var span = document.createElementNS("http://www.w3.org/1999/xhtml","span");
	} else {
		var heading = document.createElement("h3");
		var p = document.createElement("p");
		link[3] = document.createElement("a");
		var span = document.createElement("span");
	}
	
	p.setAttribute("class", "fontsize");
	span.setAttribute("class", "unseen");
	link[3].setAttribute("href", "index.php");

	for (var x = 0; x < 3; x++) {
		link[x] = link[3].cloneNode(true);
		link[x].setAttribute("class", linkClass[x]);
		link[x].setAttribute("title", linkTitle[x]);
		link[x].setAttribute("onclick", linkAction[x]);
		link[x].appendChild(linkContent[x]);
	}
	
	span.appendChild(document.createTextNode('\u00a0'));	//This is a nbsp
	heading.appendChild(headingText);
	
	p.appendChild(link[0]);
	p.appendChild(span.cloneNode(true));
	p.appendChild(link[1]);
	p.appendChild(link[2]);
	p.appendChild(span);
	
	container.appendChild(heading);
	container.appendChild(p);
};

function saveSettings() {
	createCookie("fontSize", currentFontSize, 365);
};