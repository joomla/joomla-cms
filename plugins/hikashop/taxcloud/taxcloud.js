(function(){
	window.taxcloudData = {
		tics: null,
		fields:{}
	};

	var taxcloudTics = function(data) {
		window.taxcloudData.tics = data.tic_list;
		for(var i in window.taxcloudData.fields) {
			if(window.taxcloudData.fields.hasOwnProperty(i))
				window.taxcloudData.fields[i].init();
		}
	};
	window.taxcloudTics = taxcloudTics;

	var taxcloud = function(id) {
		var t = this, d = document, w = window;
		t.id = id;
		if(w.taxcloudData.tics === null) {
			var datetime = new Date().getTime().toString(),
				script = document.createElement("script");
			script.setAttribute("src", t.json + "&time=" + datetime);
			script.setAttribute("type", "text/javascript");
			d.body.appendChild(script);

			w.taxcloudData.tics = true;
		}
		w.taxcloudData.fields[t.id] = (this);
	};
	taxcloud.prototype = {
		json: 'https://taxcloud.net/tic/default.aspx?format=jsonp',
		init: function() {
			var t = this, d = document, w = window,
				field = d.getElementById(t.id);

			if(field.value.length > 0) {
				var value = parseInt(field.value);
				field.type = "hidden";
				var tic = t.find(value);
				if(tic) {
					var el = d.createElement('span');
					el.id = t.id + '_result';
					el.innerHTML = tic.id + ' - <span title="'+tic.title+'">' + tic.label + '</span> <a class="hkIcon-delete hkIcon" href="#remove" onclick="return window.taxcloudData.fields[\''+t.id+'\'].reinit();"></a>';
					field.parentNode.appendChild(el);
				} else {
					t.reinit();
				}
			} else {
				field.type = "hidden";
				t.reinit();
			}
		},
		find: function(value) {
			var t = this, d = document, w = window;
			for(var i = 0; i < w.taxcloudData.tics.length; i++) {
				var tic = w.taxcloudData.tics[i].tic, tic_id = parseInt(tic.id);
				if(tic_id == value)
					return tic;
				if(tic.children) {
					var r = t.rfind(value, tic.children);
					if(r)
						return r;
				}
			}
			return null;
		},
		rfind: function(value, data) {
			var t = this;
			for(var j = 0; j < data.length; j++) {
				var tic_id = parseInt(data[j].tic.id);
				if(tic_id == value)
					return data[j].tic;
				if(data[j].tic.children) {
					var r = t.rfind(value, data[j].tic.children);
					if(r)
						return r;
				}
			}
			return null;
		},
		reinit: function() {
			var t = this, d = document, w = window,
				field = d.getElementById(t.id),
				res = d.getElementById(t.id + '_result');
			if(res)
				res.parentNode.removeChild(res);
			if(field) {
				field.value = '-1';
				var select = d.createElement('div');
				select.id = t.id + '_select';
				select.innerHTML = '<span id="'+t.id+'_breadcrumb"></span><select id="'+t.id+'_drop"></select>';
				field.parentNode.appendChild(select);
				var drop = d.getElementById(t.id + '_drop');
				w.Oby.addEvent(drop, 'change', function(){ t.selection(drop); });
				t.populate();
			}
			return false;
		},
		populate: function(parent, value){
			var t = this, d = document, w = window,
				field = d.getElementById(t.id),
				drop = d.getElementById(t.id + '_drop');
			if(parent === undefined)
				parent = false;
			if(drop) {
				var opt = d.createElement('option');
				opt.value = '-1';
				opt.innerHTML = '[Please Select]';
				drop.appendChild(opt);

				if(!parent) {
					for(var i = 0; i < window.taxcloudData.tics.length; i++) {
						opt = d.createElement('option');
						opt.value = window.taxcloudData.tics[i].tic.id;
						opt.title = window.taxcloudData.tics[i].tic.label;
						opt.innerHTML = window.taxcloudData.tics[i].tic.label;
						if(window.taxcloudData.tics[i].tic.children)
							opt.innerHTML += '...';
						drop.appendChild(opt);
					}
				} else {
					value = parseInt(value);
					for(var i = 0; i < parent.children.length; i++) {
						opt = d.createElement('option');
						opt.value = parent.children[i].tic.id;
						opt.title = parent.children[i].tic.label;
						opt.innerHTML = parent.children[i].tic.label;
						if(parent.children[i].tic.children)
							opt.innerHTML += '...';
						drop.appendChild(opt);
					}
				}
			}
		},
		selection: function(el) {
			var t = this, d = document, w = window,
				field = d.getElementById(t.id),
				bread = d.getElementById(t.id + '_breadcrumb'),
				drop = d.getElementById(t.id + '_drop'),
				n = t.find(el.value);

			if(el.value === '')
				return false;

			if(field) {
				field.value = el.value;
			}
			if(bread) {
				if(bread.innerHTML.length > 0)
					bread.innerHTML += ' - ';
				bread.innerHTML += n.label;
			}
			if(n && n.children) {
				drop.innerHTML = '';
				t.populate(n, el.value);
			} else {
				drop.parentNode.removeChild(drop);
				var res = d.getElementById(t.id + '_select');
				if(res)
					res.parentNode.removeChild(res);

				res = d.createElement('span');
				res.id = t.id + '_result';
				res.innerHTML = n.id + ' - <span title="'+n.title+'">' + n.label + '</span> <a class="hkIcon-delete hkIcon" href="#remove" onclick="return window.taxcloudData.fields[\''+t.id+'\'].reinit();"></a>';
				field.parentNode.appendChild(res);
			}
			return false;
		}
	};
	window.taxcloud = taxcloud;
})();