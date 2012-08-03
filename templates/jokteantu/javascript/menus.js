window.addEvent('domready', function(){
    $('tmen').getElement('div.moduletable').getElements('li a').each( function(item){
        if ( !item.hasClass('clicked') ) {
            item.addEvent('mouseenter', function() {
                    this.getParent('li').addClass('fxTop');
                    this.getParent('li').setStyle('opacity',0.01);
                    this.setStyle('color', '#fff');
                    this.getParent('li').fade(1);
                }).addEvent('mouseleave',function(){
                    this.setStyle('color', '#000');
                    this.getParent('li').removeClass('fxTop');
                });
        }
    })
    
    $('tmen').getElement('div.moduletable').getElements('li.deeper').each( function(item){
        var list = item.getElement('li');
        var myFx = new Fx.Slide(list).hide();
        
        item.addEvents({
            'mouseenter' : function(){
                myFx.cancel();
                myFx.slideIn();
            },
            'mouseleave' : function(){
                myFx.cancel();
                myFx.slideOut();
            }
        });
    })
    
    $('izquierda').getElement('div.moduletable').getElements('li.deeper').each( function(item){
        var list = item.getElement('li');
        var myFx = new Fx.Slide(list).hide();
        
        item.addEvents({
            'mouseenter' : function(){
                myFx.cancel();
                myFx.slideIn();
            },
            'mouseleave' : function(){
                myFx.cancel();
                myFx.slideOut();
            }
        });
    })

    $('derecha').getElement('div.moduletable').getElements('li.deeper').each( function(item){
        var list = item.getElement('li');
        var myFx = new Fx.Slide(list).hide();
        
        item.addEvents({
            'mouseenter' : function(){
                myFx.cancel();
                myFx.slideIn();
            },
            'mouseleave' : function(){
                myFx.cancel();
                myFx.slideOut();
            }
        });
    })
    
    $('piemenu').getElement('div.moduletable').getElements('li.deeper').each( function(item){
        var list = item.getElement('li');
        var myFx = new Fx.Slide(list).hide();
        
        item.addEvents({
            'mouseenter' : function(){
                myFx.cancel();
                myFx.slideIn();
            },
            'mouseleave' : function(){
                myFx.cancel();
                myFx.slideOut();
            }
        });
    })
});