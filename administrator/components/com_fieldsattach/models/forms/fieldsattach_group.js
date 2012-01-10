//OBJECT ARTICLES =========================================
function articles(id, title)
{
    this.array_of_articles = new Array();
    
   
}
//OBJECT ARTICLE =========================================
function article(id, title){
        this.id = id;
        this.title = title;
    }
//FUNCTION ADD ID =========================================
articles.prototype.AddId = function(id, title)    // Define Method
{ 
    var obj_article = new article;
    obj_article.id  = id;
    obj_article.title  = title;

    //FIND IF EXIST ---------------------------------------
    var find = false;
    for(var cont=0;cont<this.array_of_articles.length;cont++ )
    {
        if(this.array_of_articles[cont].id == id) {find = true;break}
    }

    //IF NOT EXIST ADD ---------------------------------------
    if(!find) this.array_of_articles[this.array_of_articles.length] = obj_article;

    //RENDER --------------------------------------------------
    this.render_articlesid();
}



//FUNCTION REMOVE ID =========================================
articles.prototype.RemoveId = function(id)    // Define Method
{  
    for(var cont=0;cont<this.array_of_articles.length;cont++ )
    {
        var elid = this.array_of_articles[cont].id ;
        if(elid == id) this.array_of_articles.splice(cont,1);    
    }
    //RENDER --------------------------------------------------
    this.render_articlesid();
}

//FUNCTION RENDER =========================================
articles.prototype.render_articlesid = function()    // Define Method
{
    document.id("jform_articlesid").value ="";
    document.getElementById("articleslist").innerHTML ="" ;
    //$("articleslist").value="";

    /*
    var myString = new String('red,green,blue');
    var myArray = myString.split(',');
    */
    for(var cont=0;cont<this.array_of_articles.length;cont++ )
    {
        var str = this.array_of_articles[cont].id ;
        if (this.array_of_articles.length-1>cont) str += ",";
        document.id("jform_articlesid").value += str;
        addLI("articleslist", this.array_of_articles[cont].id, this.array_of_articles[cont].title);
    }
}
//FUNCTION AD LI =========================================
function addLI(divid, id, text){
    var Parent = document.getElementById(divid);
    var NewLI = document.createElement("LI");

    text = '<div style="position:relative;width:100%; padding:5px 0 5px 0;border-bottom:#ddd solid 1px;"><div style="  padding:5px 0 5px 0;">'+text+'</div>  <div style="position:absolute; top:10px; right:5px;"><a href="javascript:obj.RemoveId('+id+')" >delete</a></div></div>';

    NewLI.innerHTML = text; 
    Parent.appendChild(NewLI);
} 

//CREATE OBJECT ARTICLES =========================================
obj = new articles; 


//MOOTOOLS EVENT =========================================
window.addEvent('domready', function() {
    init_obj();
});
 
