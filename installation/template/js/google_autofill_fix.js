if(window.attachEvent)
    window.attachEvent("onload",setListeners);

function setListeners(){
    inputList = document.getElementsByTagName("INPUT");
    for(i=0;i<inputList.length;i++){
        inputList[i].attachEvent("onpropertychange",restoreStyles);
        inputList[i].style.backgroundColor = "";
    }
    selectList = document.getElementsByTagName("SELECT");
    for(i=0;i<selectList.length;i++){
        selectList[i].attachEvent("onpropertychange",restoreStyles);
        selectList[i].style.backgroundColor = "";
    }
}

function restoreStyles(){
    if(event.srcElement.style.backgroundColor != "")
        event.srcElement.style.backgroundColor = "";
}