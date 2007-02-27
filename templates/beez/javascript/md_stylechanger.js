var prefsLoaded = false;
var defaultFontSize =100;
var currentFontSize = defaultFontSize;

function revertStyles(){

        currentFontSize = defaultFontSize;
        changeFontSize(0);

}

function toggleColors(){
        if(currentStyle == "White"){
                setColor("Black");
        }else{
                setColor("White");
        }
}

function changeFontSize(sizeDifference){
        currentFontSize = parseInt(currentFontSize) + parseInt(sizeDifference * 5);

        if(currentFontSize > 220){
                currentFontSize = 220;
        }else if(currentFontSize < 60){
                currentFontSize = 60;
        }

        setFontSize(currentFontSize);
};

function setFontSize(fontSize){
        var stObj = (document.getElementById) ? document.getElementById('content_area') : document.all('content_area');
        document.body.style.fontSize = fontSize + '%';

        //alert (document.body.style.fontSize);
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

window.onload = setUserOptions;

function setUserOptions(){
        if(!prefsLoaded){

                cookie = readCookie("fontSize");
                currentFontSize = cookie ? cookie : defaultFontSize;
                setFontSize(currentFontSize);

                prefsLoaded = true;
        }

}

window.onunload = saveSettings;

function saveSettings()
{
  createCookie("fontSize", currentFontSize, 365);
}