if (typeof(MooTools) != 'undefined'){

		var subnav = new Array();

		Element.extend(
		{
			hide: function(timeout)
			{
				this.status = 'hide';
				clearTimeout (this.timeout);
				if (timeout)
				{
					this.timeout = setTimeout (this.anim.bind(this), timeout);
				}else{
					this.anim();
				}
			},

			show: function(timeout)
			{
				this.status = 'show';
				clearTimeout (this.timeout);
				if (timeout)
				{
					this.timeout = setTimeout (this.anim.bind(this), timeout);
				}else{
					this.anim();
				}
			},

			setActive: function () {
				//this.addClass(classname);
				this.className+='sfhover';
				/*
				for(var i=0;i<this.childNodes.length; i++) {
					if(this.childNodes[i].nodeName.toLowerCase() == 'a') {
						//$(this.childNodes[i]).addClass(classname);
						$(this.childNodes[i]).setActive();
						return;
					}
				}
				*/
			},

			setDeactive: function () {
				//this.removeClass(classname);
				this.className=this.className.replace(new RegExp("sfhover\\b"), "");
				/*
				for(var i=0;i<this.childNodes.length; i++) {
					if(this.childNodes[i].nodeName.toLowerCase() == 'a') {
						$(this.childNodes[i]).setDeactive();
						return;
					}
				}
				*/
			},

			anim: function() {
				if ((this.status == 'hide' && this.style.left != 'auto') || (this.status == 'show' && this.style.left == 'auto' && !this.hidding)) return;
				this.setStyle('overflow', 'hidden');
				if (this.status == 'show') {
					this.hidding = 0;
					this.hideAll();
					//this.parentNode.setActive();
				} else {
					//this.parentNode.setDeactive();
				}

				if (this.status == 'hide')
				{
					this.hidding = 1;
					//this.myFx1.stop();
					this.myFx2.stop();
					//this.myFx1.start(1,0);
					if (this.parent._id) this.myFx2.start(this.offsetWidth,0);
					else this.myFx2.start(this.offsetHeight,0);
				} else {
					this.setStyle('left', 'auto');
					//this.myFx1.stop();
					this.myFx2.stop();
					//this.myFx1.start(0,1);
					if (this.parent._id) this.myFx2.start(0,this.mw);
					else this.myFx2.start(0,this.mh);
				}
			},

			init: function() {
				this.mw = this.clientWidth;
				this.mh = this.clientHeight;
				//this.myFx1 = new Fx.Style(this, 'opacity');
				//this.myFx1.set(0);
				if (this.parent._id)
				{
					this.myFx2 = new Fx.Style(this, 'width', {duration: 300});
					this.myFx2.set(0);
				}else{
					this.myFx2 = new Fx.Style(this, 'height', {duration: 300});
					this.myFx2.set(0);
				}
				this.setStyle('left', '-999em');
				animComp = function(){
					if (this.status == 'hide')
					{
						this.setStyle('left', '-999em');
						this.hidding = 0;
					}
					this.setStyle('overflow', '');
				}
				this.myFx2.addEvent ('onComplete', animComp.bind(this));
			},

			hideAll: function() {
				for(var i=0;i<subnav.length; i++) {
					if (!this.isChild(subnav[i]))
					{
						subnav[i].hide(0);
					}
				}
			},

			isChild: function(_obj) {
				obj = this;
				while (obj.parent)
				{
					if (obj._id == _obj._id)
					{
						//alert(_obj._id);
						return true;
					}
					obj = obj.parent;
				}
				return false;
			}


		});


		var DropdownMenu = new Class({
			initialize: function(element)
			{
				//$(element).mh = 0;
				$A($(element).childNodes).each(function(el)
				{
					if(el.nodeName.toLowerCase() == 'li')
					{
						//if($(element)._id) $(element).mh += 30;
						$A($(el).childNodes).each(function(el2)
						{
							if(el2.nodeName.toLowerCase() == 'ul')
							{
								$(el2)._id = subnav.length+1;
								$(el2).parent = $(element);
								subnav.push ($(el2));
								el2.init();
								el.addEvent('mouseover', function()
								{
									el.setActive();
									el2.show(0);
									return false;
								});

								el.addEvent('mouseout', function()
								{
									el.setDeactive();
									el2.hide(20);
								});
								new DropdownMenu(el2);
								el.hasSub = 1;
							}
						});
						if (!el.hasSub)
						{
							el.addEvent('mouseover', function()
							{
								el.setActive();
								return false;
							});

							el.addEvent('mouseout', function()
							{
								el.setDeactive();
							});
						}
					}
				});
				return this;
			}
		});

		Window.onDomReady(function() {new DropdownMenu($E('#ja-mainnav ul.menu'))});

	}else {

		sfHover = function() {
		var sfEls = document.getElementById("ja-mainnav").getElementsByTagName("li");
		for (var i=0; i<sfEls.length; ++i) {
			sfEls[i].onmouseover=function() {
				this.className+="sfhover";
			}
			sfEls[i].onmouseout=function() {
				this.className=this.className.replace(new RegExp("sfhover\\b"), "");
			}
		}
	}
	if (window.attachEvent) window.attachEvent("onload", sfHover);
}
