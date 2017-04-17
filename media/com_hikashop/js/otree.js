/*
	oTree : Obscurelighty Project ( http://www.obscurelighty.com/ )
	Author: Jerome GLATIGNY <jerome@obscurelighty.com>
	Copyright (C) 2010-2015  Jerome GLATIGNY

	This file is part of Obscurelighty.

	Obscurelighty is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	Obscurelighty is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with Obscurelighty.  If not, see <http://www.gnu.org/licenses/>.

	The open source license for Obscurelighty and its modules permits you to use the
	software at no charge under the condition that if you use in an application you
	redistribute, the complete source code for your application must be available and
	freely redistributable under reasonable conditions. If you do not want to release the
	source code for your application, you may purchase a proprietary license from his author.
*/

/** oTree
 * version: 0.9.8
 * release date: 2014-09-14
 */
(function(){
	window.oTrees = [];

	/** oNode
	 * @param id The identifier number
	 * @param pid The parent identifier number
	 * @param state The node State
	 * 	0 - final node
	 * 	1 - directory node closed
	 * 	2 - directory node open
	 * 	3 - directory node dynamic (closed)
	 * 	4 - empty directory node
	 *	5 - root final node
	 * @param name The displayed name
	 * @param value The internal value for the node
	 * @param url The link url (href)
	 * @param icon The overloaded icon
	 */
	var oNode = function(id, pid, state, name, value, url, icon, checked, noselection) {
		var t = this;
		t.id = id;
		t.pid = pid;
		t.state = state;
		t.name = name;
		t.value = value;
		t.url = url;
		t.checked = checked || false;
		t.noselection = noselection || 0;
		t._isLast = -1;
		t.children = [];
		t.icon = icon || null;
	};
	oNode.prototype = {
		/** Add a Child
		 */
		add: function(id) {
			this.children[this.children.length] = id;
		},
		/** Remove a Child
		 */
		rem: function(id) {
			var t=this,f=false;
			for(var i = 0; i < t.children.length; i++) {
				if(f == true)
					t.children[i-1] = t.children[i];
				else if(t.children[i] == id)
					f = true;
			}
			if(f == true)
				t.children.splice(t.children.length-1, 1);
		}
	};
	window.oNode = oNode;

	/** oTree
	 * @param id
	 * @param conf
	 * @param callbackFct
	 * @param data
	 * @param render
	 */
	var oTree = function(id, conf, callbackFct, data, render) {
		if(window.oTrees[id])
			window.oTrees[id].destroy();

		var t = this;
		if(!conf) conf = {};
		t.config = {
			rootImg: conf.rootImg || '/media/com_hikashop/images/otree/',
			useSelection: (conf.useSelection === undefined) || conf.useSelection,
			checkbox: conf.checkbox || false,
			tricheckbox: conf.tricheckbox || false,
			showLoading: conf.showLoading || false,
			loadingText: conf.loadingText || ''
		};
		t.icon = {
			loading     : 'loading.gif',
			folder      : 'folder.gif',
			folderOpen  : 'folderopen.gif',
			node        : 'page.gif',
			line        : 'line.gif',
			join        : 'join.gif',
			joinBottom  : 'joinbottom.gif',
			plus        : 'plus.gif',
			plusBottom  : 'plusbottom.gif',
			minus       : 'minus.gif',
			minusBottom : 'minusbottom.gif',
			option      : 'option.gif'
		};
		t.lNodes = [];
		t.tmp = {
			trichecks: []
		};
		t.selectedNode = null;
		t.selectedFound = false;
		t.written = false;

		t.lNodes[0] = new oNode(0,-1);
		t.nbRemovedNodes = 0;

		t.iconWidth = 18;

		t.id = id;
		t.callbackFct = callbackFct;
		t.callbackSelection = null;
		t.callbackCheck = null;

		window.oTrees[id] = t;

		if(data) t.load(data);
		if(render) t.render(render);
	};
	oTree.prototype = {
		/** Destroy an oTree instance
		 */
		destroy: function() {
			var t = this;
			window.oTrees[t.id] = null;
			t.icon = null;
			t.config = null;
			t.lNodes = null;
			t.callbackFct = null;
			t.callbackSelection = null;
			t.loadingNode = null;
			t.nbRemovedNodes = 0;

			e = document.getElementById(t.id + '_otree');
			if(!e)
				e = document.getElementById(t.id);
			if(e)
				e.innerHTML = '';
			t.id = null;
		},
		/** Add a new Icon in the configuration
		 * @param name
		 * @param url
		 */
		addIcon: function(name, url) {
			this.icon[name] = url;
		},
		/** Create a new Node
		 * @param pid
		 * @param state
		 * @param name
		 * @param value
		 * @param url
		 * @param icon
		 * @return id
		 */
		add: function(pid, state, name, value, url, icon, checked, noselection) {
			var t=this,id=0;
			if(!t.lNodes[pid])
				return -1;
			if(t.nbRemovedNodes == 0) {
				id = t.lNodes.length;
			} else {
				for(var i = t.lNodes.length; i >= 1; i--) {
					if(t.lNodes[i] == null) {
						id = i;
						i = 0;
						t.nbRemovedNodes--;
						break;
					}
				}
			}
			t.lNodes[id] = new oNode(id, pid, state, name, value, url, icon, checked, noselection);
			t.lNodes[pid].add(id);
			return id;
		},
		/** Load a serialized tree
		 * @param data
		 * @param pid
		 */
		load: function(data, pid) {
			if(typeof(data) != "object") return;
			if(typeof(pid) == "undefined") pid = 0;
			var nId = 0, i, l = data.length;
			for(var id = 0; id < l; id++) {
				if(typeof(data[id]) == "object" && data[id]) {
					i = data[id];
					nId = this.add(pid, i.status, i.name, i.value, i.url, i.icon, i.checked, i.noselection);
					if(i.data) {
						this.load(i.data, nId);
					}
				}
			}
		},
		/** Create a new Node and insert it for a specific identifier
		 * @param id
		 * @param pid
		 * @param state
		 * @param name
		 * @param value
		 * @param url
		 * @param icon
		 */
		ins: function(id, pid, state, name, value, url, icon, checked, noselection) {
			if(!this.lNodes[id]) {
				this.lNodes[id] = new oNode(id, pid, state, name, value, url, icon, checked, noselection);
				this.lNodes[pid].add(id);
			}
		},
		/** Insert a Node
		 * @param node
		 */
		insertNode: function(node) {
			this.lNodes[node.id] = node;
			this.lNodes[node.pid].add(node.id);
		},
		/** Set a Node.
		 * like "insertNode" but does not create the link with the parent.
		 * @param node
		 */
		setNode: function(node) {
			this.lNodes[node.id] = node;
		},
		/** Move a Node
		 * @param node
		 * @param dest
		 */
		moveNode: function(node,dest) {
			var t = this;
			if(typeof(node) == "number") node = t.get(node);
			if(typeof(dest) == "number") dest = t.get(dest);
			var old = t.lNodes[node.pid];
			if(old) {
				old.rem(node.id);
				dest.add(node.id);
				node.pid = dest.id;
				t.update(old);
				t.update(dest);
			}
		},
		/** Remove a Node
		 * @param node The node to destroy (Node Object or Node Id)
		 * @param update Call an update on his parent or not
		 * @param rec Do not pass this parameter which is used for recursivity
		 */
		rem: function(node,update,rec) {
			var t=this;
			if(typeof(node) == "number") node = t.get(node);
			if(typeof(update) == "undefined") update = true;
			var p = t.get(node.pid);
			if(node && node.children.length > 0) {
				var o;
				for(var i = node.children.length - 1; i >= 0; i--) {
					o = node.children[i];
					t.rem(o, false, true);
					t.lNodes[o] = null;
				}
				node.children = [];
			}
			if(!rec) {
				var id = node.id;
				if(p) p.rem(id);
				t.lNodes[id] = null;
			}
			t.nbRemovedNodes++;
			if(update && p)
				t.update(p);
		},
		/** Update a Node
		 * This function will call a "render"
		 * @param node The node to update (Node Object or Node Id)
		 * @return boolean
		 */
		update: function(node) {
			if(node) {
				if(typeof(node) == "number") node = this.get(node);
				return this.render(this.id + '_d' + node.id, node.id);
			}
			return this.render();
		},
		/** Render the tree or just a part of it
		 * @param dest The render target (HTML Object or name of its ID)
		 * @param start The Node Id for the render root
		 * @return boolean
		 */
		render: function(dest, start) {
			var t = this, d = document, str = '', n;
			if(typeof(start) == "number")
				n = t.lNodes[start];
			else
				n = t.lNodes[0];

			t.processLast();
			t.tmp.trichecks = [];

			if(t.written == true || dest) {
				if(typeof(dest) == "boolean" || !dest) dest = t.id;
				if(t.written == false) {
					t.written = true;
					t.id = dest;
				}
				str = t.rnodes(n);
				var e = d.getElementById(dest + '_otree');
				if(!e) e = d.getElementById(dest);
				if(!e) return false;
				e.innerHTML = str;
			} else {
				str = '<div id="' + t.id + '_otree" class="oTree">' + t.rnodes(n) + '</div>';
				d.write(str);
				t.written = true;
			}

			if(t.config.tricheckbox && t.tmp.trichecks.length > 0) {
				var id, c;
				for(var i = t.tmp.trichecks.length - 1; i >= 0; i--) {
					id = t.tmp.trichecks[i];
					c = d.getElementById(t.id+'_c'+id);
					if(c)
						c.indeterminate = true;
				}
			}
			t.tmp.trichecks = [];

			return true;
		},
		/** Internal function
		 */
		rnodes: function(pNode) {
			var t=this,str = '';
			if(!pNode)
				return str;
			for(var i = 0; i < pNode.children.length; i++) {
				var n = pNode.children[i];
				if(t.lNodes[n])
					str += t.rnode(t.lNodes[n]);
			}
			return str;
		},
		/** Internal function
		 */
		rnode: function(node) {
			var t=this,str = '<div class="oTreeNode">', style = '', ret = '', toFind = node.pid, found = true;

			if(toFind > 0) {
				var white = 0;
				while(found) {
					found = false;
					if(toFind > 0 && toFind < t.lNodes.length && t.lNodes[toFind]) {
						if(t.lNodes[toFind]._isLast == -1)
							t.lNodes[toFind]._isLast = t.isLast(t.lNodes[toFind])?1:0;
						if(t.lNodes[toFind]._isLast == 1) {
							white++;
							if(white == 6) {
								ret = '<div class="e'+white+'"></div>' + ret;
								white = 0;
							}
						} else {
							if(white > 0)
								ret = '<div class="e'+white+'"></div>' + ret;
							white = 0;
							ret = '<img src="' + t.config.rootImg + t.icon.line + '" alt=""/>' + ret;
						}
						found = true;
						toFind = t.lNodes[toFind].pid;
					}
				}
				if(white > 0)
					ret = '<div class="e'+white+'"></div>' + ret;
			}
			str += ret;

			// Cursor
			var img, last = (node._isLast == 1);
			if(node.state == 0 || node.state == 4) {
				img = t.icon.join;
				if(last) img = t.icon.joinBottom;
				str += '<img src="' + t.config.rootImg + img + '" alt=""/>';
			} else if(node.state == 1 || node.state == 3) {
				img = t.icon.plus;
				if(last) img = t.icon.plusBottom;
				str += '<a href="#" onclick="window.oTrees.' + t.id + '.s(' + node.id + ');return false;"><img id="'+t.id+'_j'+node.id+'" src="' + t.config.rootImg + img + '" alt=""/></a>';
			} else if(node.state == 2) {
				img = t.icon.minus;
				if(last) img = t.icon.minusBottom;
				str += '<a href="#" onclick="window.oTrees.' + t.id + '.s(' + node.id + ');return false;"><img id="'+t.id+'_j'+node.id+'" src="' + t.config.rootImg + img + '" alt=""/></a>';
			}

			if(t.config.checkbox && !node.noselection) {
				var attr = '', chkName = t.config.checkbox;
				if(typeof(chkName) == "string") {
					if(chkName == "-")
						chkName = "";
					else if(chkName.substring(-1) != ']')
						chkName += '[]';
				} else
					chkName = t.id+'[]';

				if(node.checked) {
					if(t.config.tricheckbox && node.checked === 2)
						t.tmp.trichecks[t.tmp.trichecks.length] = node.id;
					else
						attr = ' checked="checked"';
				}
				str += '<input type="checkbox" id="'+t.id+'_c'+node.id+'" onchange="window.oTrees.' + t.id + '.chk(' + node.id + ',this.checked);" name="' + chkName + '" value="' + node.value + '"' + attr + '/>';
			}

			// Icon
			str += '<img id="' + t.id + '_i' + node.id + '" alt="" src="' + t.config.rootImg;
			var name = node.name;
			if(t.config.useSelection && node.url)
				name = '<a id="'+t.id+'_s'+node.id+'" class="node" href="' + node.url + '" onclick="window.oTrees.' + t.id + '.sel(' + node.id + ');">' + node.name + '</a>';
			else if(node.url)
				name = '<a id="'+t.id+'_s'+node.id+'" class="node" href="' + node.url + '">' + node.name + '</a>';
			else if((t.config.checkbox || t.config.useSelection) && !node.noselection)
				name = '<a id="'+t.id+'_s'+node.id+'" class="node" href="#" onclick="window.oTrees.' + t.id + '.sel(' + node.id + ');return false;">' + node.name + '</a>';
			else
				name = '<span class="node">' + node.name + '</span>';

			if(node.state == 0 || node.state == 5) {
				if(node.icon == null)
					str += t.icon.node + '"/>' + name;
				else
					str += t.icon[node.icon] + '"/>' + name;
			} else if(node.state == 1 || node.state == 3 || node.state == 4) {
				if(node.icon == null)
					str += t.icon.folder + '"/>' + name;
				else
					str += t.icon[node.icon] + '"/>' + name;
				style = 'style="display:none;"';
			} else if(node.state == 2) {
				if(node.icon == null)
					str += t.icon.folderOpen + '"/>' + name;
				else
					str += t.icon[node.icon] + '"/>' + name;
			}
			str += '</div>';

			if(node.state > 0)
				str += '<div id="' + t.id + '_d' + node.id + '" class="clip" ' + style + '>' + t.rnodes(node) + '</div>';
			return str;
		},
		/** Switch Node
		 * Open or Close a Directory Node
		 * @param node The node to switch (Node Object or Node Id)
		 */
		s: function(node) {
			if(typeof(node) == "number") node = this.get(node);
			if(node.state == 2)
				this.c(node);
			else
				this.o(node);
		},
		/** Open a Node
		 * @param node The node to open (Node Object or Node Id)
		 */
		o: function(node) {
			var t = this;
			if(typeof(node) == "number") node = t.get(node);

			// Closed Or Dynamic
			if(node && (node.state == 1 || node.state == 3)) {
				e = document.getElementById(t.id + '_d' + node.id);
				e.style.display = '';

				// Dynamic
				if(node.state == 3) {
					node.children = [];
					if(t.config.showLoading) {
						if(!t.loadingNode) {
							t.loadingNode = new oNode(0,node.id,0,t.config.loadingText,null,null,'loading');
							t.loadingNode._isLast = 1;
						} else
							t.loadingNode.pid = node.id;

						e.innerHTML = t.rnode(t.loadingNode);
					}

					if(t.callbackFct)
						t.callbackFct(this, node, e);
				}

				if(node.icon == null) {
					e = document.getElementById(t.id + '_i' + node.id);
					e.src = t.config.rootImg + t.icon.folderOpen;
				}

				e = document.getElementById(t.id + '_j' + node.id);
				if(t.isLast(node))
					e.src = t.config.rootImg + t.icon.minusBottom;
				else
					e.src = t.config.rootImg + t.icon.minus;
				node.state = 2;
			}
		},
		/** Close a Node
		 * @param node The node to close (Node Object or Node Id)
		 */
		c: function(node) {
			if(typeof(node) == "number") node = this.get(node);

			// Open
			if(node && node.state == 2) {
				var t=this,d=document;
				e = d.getElementById(t.id + '_d' + node.id);
				e.style.display = 'none';

				if(node.icon == null) {
					e = d.getElementById(t.id + '_i' + node.id);
					e.src = t.config.rootImg + t.icon.folder;
				}

				e = d.getElementById(t.id + '_j' + node.id);
				if(t.isLast(node))
					e.src = t.config.rootImg + t.icon.plusBottom;
				else
					e.src = t.config.rootImg + t.icon.plus;
				node.state = 1;
			}
		},
		/** Open To
		 * @param node The node to open to... (Node Object or Node Id)
		 */
		oTo: function(node) {
			if(typeof(node) == "number") node = this.get(node);
			if(node) {
				var t=this,toOpId = node.pid;
				while(toOpId > 0 && toOpId < t.lNodes.length) {
					this.o(t.lNodes[toOpId]);
					toOpId = t.lNodes[toOpId].pid;
				}
			}
		},
		/** Make a Selection
		 * @param id The Node Id to select (could be a node object)
		 */
		sel: function(id) {
			if(id === null) return;
			if(typeof(id) != "number") id = id.id;

			var t=this,d=document,cn = t.lNodes[id];
			if(!cn) return;
			if(!t.config.useSelection && !t.config.checkbox) return;
			if(t.config.checkbox) {
				t.chk(cn,-1);
			}
			if(!t.config.useSelection) return;
			if(t.selectedNode != id) {
				var e, previous = t.selectedNode;
				if(t.selectedNode || t.selectedNode == 0) {
					e = d.getElementById(t.id + '_s' + t.selectedNode);
					if(e)
						e.className = "node";
				}
				e = d.getElementById(t.id + '_s' + id);
				if(e)
					e.className = "nodeSel";
				t.selectedNode = id;
				if(t.callbackSelection)
					t.callbackSelection(this, t.selectedNode, previous);
			} else {
				var e = d.getElementById(t.id + '_s' + id);
				if(e)
					e.className = "nodeSel";
			}
		},
		/**
		 *
		 */
		chk: function(id, value, call, fromP) {
			if(id === null) return;
			if(typeof(id) == "object") id = id.id;
			if(!this.config.checkbox) return;

			var t=this,d=document,cn=t.lNodes[id];
			if(!cn) return;
			var oldState = cn.checked;
			if(typeof(value) == "number" && value < 0) {
				if(cn.checked == 2)
					cn.checked = true;
				else
					cn.checked = !cn.checked;
			} else
				cn.checked = value;
			var e = d.getElementById(t.id+'_c'+id);
			if(e) {
				e.checked = cn.checked;
				if(!t.config.tricheckbox)
					e.indeterminate = false;
				if(t.config.tricheckbox && oldState != cn.checked) {
					e.indeterminate = false;
					if(value === 2) {
						e.checked = false;
						e.indeterminate = true;
						cn.checked = 2;
					} else {
						// Check/uncheck all children
						for(var i = cn.children.length - 1; i >= 0; i--) {
							t.chk(cn.children[i], cn.checked, call, true);
						}
					}
					if(fromP === undefined) {
						// Check/uncheck parent if necessary
						var p = t.lNodes[cn.pid], o = null, cpt = 0;
						if(p) {
							for(var i = p.children.length - 1; i >= 0; i--) {
								o = t.lNodes[p.children[i]];
								if(o && o.checked && o.checked === true) {
									cpt++;
								}
							}
							if(cpt == p.children.length || cpt == 0) {
								t.chk(p, cn.checked, call);
							} else {
								t.chk(p, 2, call);
							}
						}
					}
				}
			}
			if((call === undefined || call === null || call) && t.callbackCheck)
				t.callbackCheck(this, id, value);
		},
		/**
		 *
		 */
		chks: function(ids,call,useId) {
			var t = this;
			if(!t.config.checkbox) return;
			if(useId === undefined) useId = true;
			if(call === undefined) call = false;
			if(typeof(ids) == "string") {
				// Check all
				if(ids == "*") {
					for(var i = 0; i < t.lNodes.length; i++) {
						if(t.lNodes[i] && !t.lNodes[i].checked)
							t.chk(t.lNodes[i],true,call);
					}
					return;
				}
				ids = ids.split(",");
			}
			for(var i = 0; i < t.lNodes.length; i++) {
				if(t.lNodes[i] && t.lNodes[i].checked)
					t.chk(t.lNodes[i],false,call);
			}
			if(useId) {
				for(var j = ids.length -1; j >= 0; j--) {
					var v = parseInt(ids[j]);
					t.chk(v,true,call);
				}
			} else {
				for(var j = ids.length -1; j >= 0; j--) {
					for(var i = 0; i < t.lNodes.length; i++) {
						if(t.lNodes[i] && t.lNodes[i].value == ids[j]) {
							t.chk(i,true,call);
							break;
						}
					}
				}
			}
		},
		/**
		 *
		 */
		getChk: function() {
			var t = this, ret = [];
			if(!t.config.checkbox) return false;
			for(var i = 0; i < t.lNodes.length; i++) {
				if(t.lNodes[i] && t.lNodes[i].checked && t.lNodes[i].checked === true && t.lNodes[i].value)
					ret.push(t.lNodes[i].value);
			}
			return ret;
		},
		/** Find a Node
		 * @param value The value to found
		 * @param mode The mode for node state
		 *	[null] - all nodes
		 *	0 - Final nodes
		 *	1 - Directory nodes
		 * @return the first node object which matched
		 */
		find: function(value, mode) {
			if(typeof(mode) == "undefined") mode = -1;

			var t = this;
			for(var i = 0; i < t.lNodes.length; i++) {
				if(t.lNodes[i] && t.lNodes[i].value == value) {
					if(mode == -1)
						return t.lNodes[i];
					if(mode == 0 && (t.lNodes[i].state == 0 || t.lNodes[i].state == 5))
						return t.lNodes[i];
					if(mode == 1 && t.lNodes[i].state >= 1 && t.lNodes[i].state != 5)
						return t.lNodes[i];
				}
			}
			return null;
		},
		/** Empty a directory
		 * @param node The node to empty (Node Object or Node Id)
		 */
		emptyDirectory: function(node) {
			if(typeof(node) == "number") node = this.get(node);
			if(node.state == 1 || node.state == 2 || node.state == 3) {
				var t = this, d = document,
					e = d.getElementById(t.id + '_j' + node.id),
					a = e.parentNode;

				var src = t.config.rootImg + t.icon.join;
				if(node._isLast == 1)
					src = t.config.rootImg + t.icon.joinBottom;

				a.parentNode.replaceChild(e, a);
				e.src = src;
				node.state = 4;

				if(node.icon == null) {
					e = d.getElementById(t.id + '_i' + node.id);
					if(!e) return;
					e.src = t.config.rootImg + t.icon.folder;
				}

				e = d.getElementById(t.id + '_d' + node.id);
				if(!e) return;
				e.style.display = 'none';
				e.innerHTML = '';

				if(node && node.children.length > 0) {
					var o;
					for(var i = node.children.length - 1; i >= 0; i--) {
						o = node.children[i];
						t.rem(o, false);
						t.lNodes[o] = null;
					}
				}
				node.children = [];
			}
		},
		/** Get a node
		 * @param id The node id
		 * @return the node object
		 */
		get: function(id) {
			if(id >= 0 && id < this.lNodes.length && this.lNodes[id]) {
				try {
					return this.lNodes[id];
				} catch(e) {
					return null;
				}
			}
			return null;
		},
		/** Internal function
		 */
		isLast: function(node) {
			try {
				var pChildren = this.lNodes[node.pid].children;
				return (pChildren[pChildren.length - 1] == node.id);
			} catch(e) {}
			return true;
		},
		/** Internal function
		 * currently unused. Deprecated?
		 */
		cleanLast: function() {
			for(var i = this.lNodes.length - 1; i >= 0; i--)
				this.lNodes[i]._isLast = -1;
		},
		/** Internal function
		 */
		processLast: function() {
			var t=this,n;
			for(var i = t.lNodes.length - 1; i >= 0; i--) {
				if(t.lNodes[i] && t.lNodes[i].children.length > 0) {
					n = t.lNodes[i].children[ t.lNodes[i].children.length - 1 ];
					t.lNodes[n]._isLast = 1;
					for(var j = t.lNodes[i].children.length - 2; j >= 0; j--) {
						t.lNodes[ t.lNodes[i].children[ j ]]._isLast = 0;
					}
				}
			}
		},
		/**
		 *
		 */
		deep: function(node, max) {
			if(typeof(node) == "number") node = this.get(node);
			if(node == null) return -1;
			if(typeof(max) == "undefined") max = 100;
			var ret = 0, toFind = node.pid;
			if(toFind == -1)
				return ret;
			while(toFind > 0 && this.lNodes[toFind]) {
				ret++;
				toFind = this.lNodes[toFind].pid;
				if(ret >= max)
					return ret;
			}
			return ret;
		},
		/**
		 *
		 */
		search: function(text) {
			var t=this,d=document,r=null,e=null,pid=0;

			if(text) {
				r = new RegExp(text,"i");
				for(var i = 0; i < t.lNodes.length; i++) {
					if(t.lNodes[i])
						t.lNodes[i].search = -1;
				}
				for(var i = 0; i < t.lNodes.length; i++) {
					if(t.lNodes[i]) {
						if(r.test(t.lNodes[i].name)) {
							if(t.lNodes[i].search <= 0) {
								t.lNodes[i].search = 2;
								pid = t.lNodes[i].pid;
								while(pid > 0 && t.lNodes[pid] && t.lNodes[pid].search <= 0) {
									t.lNodes[pid].search = 1;
									pid = t.lNodes[pid].pid;
								}
							} else {
								t.lNodes[i].search = 2;
							}
						} else {
							if(t.lNodes[i].search < 0)
								t.lNodes[i].search = 0;
						}
					}
				}

			}
			for(var i = 0; i < t.lNodes.length; i++) {
				if(t.lNodes[i]) {
					e = d.getElementById(t.id + '_s' + t.lNodes[i].id);
					if(!text) {
						t.lNodes[i].search = null;
						if(e) {
							e.parentNode.style.display = '';
							e.className = "node";
						}
					} else {
						if(e) {
							e.className = "node";
							if(t.lNodes[i].search > 0) {
								e.parentNode.style.display = '';
								if(t.lNodes[i].search > 1)
									e.className = "nodeSel";
							} else
								e.parentNode.style.display = 'none';
						}
					}
				}
			}
		}
	};
	oTree.version = 20140914;
	if(!window.oTree || window.oTree.version < oTree.version)
		window.oTree = oTree;
})();

/** oList
 * version: 0.1.2
 * release date: 2015-08-07
 */
(function(){
	window.oLists = [];

	/** oTree
	 * @param id
	 * @param conf
	 * @param callbackFct
	 * @param data
	 * @param render
	 */
	var oList = function(id, conf, callbackFct, data, render) {
		if(window.oLists[id])
			window.oLists[id].destroy();

		var t = this;
		if(!conf) conf = {};
		t.config = {
			hideBlocked: conf.hideBlocked || false,
			table: conf.table || false,
			defaultColumn: conf.defaultColumn || false,
			displayFormat: conf.displayFormat || false,
			gradientLoad: conf.gradientLoad || false
		};
		t.written = false;
		t.id = id;
		t.callbackFct = callbackFct;
		t.callbackSelection = null;
		t.callbackScroll = null;
		t.highlighted = null;
		t._fct = {};

		window.oLists[id] = t;

		if(data) t.load(data);
		if(render) t.render(render);
	};
	oList.prototype = {
		/** Destroy an oTree instance
		 */
		destroy: function() {
			var t = this, d = document;
			window.oLists[t.id] = null;
			t.config = null;
			t.lData = [];
			t.callbackFct = null;
			t.callbackSelection = null;
			t.callbackScroll = null;
			t.highlighted = null;

			t.deinitScroll();
			t._fct = {};

			e = d.getElementById(t.id + '_olist');
			if(!e) e = d.getElementById(t.id);
			if(e) e.innerHTML = '';
			t.id = null;
		},
		/** Load a serialized list
		 * @param data
		 * @param pid
		 */
		load: function(data) {
			if(typeof(data) != "object")
				return false;
			var t = this;
			t.lData = [];
			for(var d in data) {
				if(data.hasOwnProperty(d))
					t.lData[ t.lData.length ] = t.getData(d, data[d]);
			}
			t.sort();
			t.highlighted = null;
			return (t.lData.length > 0);
		},
		/**
		 *
		 */
		add: function(key, name) {
			this.lData[ this.lData.length ] = this.getData(key, name);
		},
		/**
		 *
		 */
		getData: function(key, data) {
			var t = this, o = {key: key};
			if(!t.config.table) {
				o.name = data;
				return o;
			}
			for(var h in t.config.table) {
				if(!t.config.table.hasOwnProperty(h))
					continue;
				o[h] = '';
				if(data[h])
					o[h] = data[h];
			}
			return o;
		},
		/**
		 *
		 */
		sort: function(byKey) {
			var t = this;
			if(!t.lData || t.lData.length == 0)
				return false;
			if(byKey) {
				t.lData.sort(function(a,b){
					var x = a.key.toLowerCase(), y = b.key.toLowerCase();
					return x < y ? -1 : ((x > y) ? 1 : 0);
				});
				return true;
			}
			if(!t.config.table) {
				t.lData.sort(function(a,b){
					var x = a.name.toLowerCase(), y = b.name.toLowerCase();
					return x < y ? -1 : ((x > y) ? 1 : 0);
				});
				return true;
			}

			return false;
		},
		/** Render the tree or just a part of it
		 * @param dest The render target (HTML Object or name of its ID)
		 * @param start The Node Id for the render root
		 * @return boolean
		 */
		render: function(dest) {
			var t = this, d = document, str = '';
			if(t.written == true || dest) {
				if(typeof(dest) == "boolean" || !dest) dest = t.id;
				if(t.written == false) {
					t.written = true;
					t.id = dest;
				}
				str = (!t.config.table) ? t.rlist() : t.rtable();
				var e = d.getElementById(dest + '_olist');
				if(!e) e = d.getElementById(dest);
				if(!e) return false;
				e.innerHTML = str;
			} else {
				str = '<div id="' + t.id + '_olist" class="oList">' + ((!t.config.table) ? t.rlist() : t.rtable()) + '</div>';
				d.write(str);
				t.written = true;
			}
			if(t.config.gradientLoad)
				t.initScroll();
			return true;
		},
		rlist: function() {
			var t = this, l = t.lData.length, n = null, str = '<ul>';
			for(var i = 0; i < l; i++) {
				n = t.lData[i];
				if(n && !n.hidden && (!n.block || !t.config.hideBlocked)) {
					if(t.highlighted === null || t.highlighted != i)
						str += '<li>';
					else
						str += '<li class="oListSelected">';
					if(n.block)
						str += '<span>' + (n.display ? n.display : n.name) + '</span></li>';
					else
						str += '<a href="#" onclick="window.oLists.' + t.id + '.sel(' + i + ');return false;">' + (n.display ? n.display : n.name) + '</a></li>';
				}
			}
			if(l > 0 && t.config.gradientLoad)
				str += '<li class="oListLoadMore"><span></span></li>';
			str += '<ul>';
			return str;
		},
		rtable: function() {
			var t = this, l = t.lData.length, n = null, str = '<table class="oListTable"><thead><tr>', extraClass = '';
			for(var h in t.config.table) {
				if(!t.config.table.hasOwnProperty(h))
					continue;
				str += '<th>'+t.config.table[h]+'</th>';
			}
			str += '</tr></thead><tbody>';
			for(var i = 0; i < l; i++) {
				n = t.lData[i];
				if(n && !n.hidden && (!n.block || !t.config.hideBlocked)) {
					extraClass = (t.highlighted === null || t.highlighted != i) ? '' : ' oListSelected';
					if(n.block)
						str += '<tr class="oListBlocked">';
					else
						str += '<tr onclick="window.oLists.' + t.id + '.sel(' + i + ');return false;" class="oListLine'+extraClass+'">';
					for(var h in t.config.table) {
						if(!t.config.table.hasOwnProperty(h))
							continue;
						str += '<td>' + n[h] + '</td>';
					}
					str += '</tr>';
				}
			}
			if(l > 0 && t.config.gradientLoad)
				str += '<tr class="oListBlocked oListLoadMore"><td colspan="'+t.config.table.length+'"></td></tr>';
			str += '</tbody></table>';
			return str;
		},
		initScroll: function(fct) {
			var t = this;
			if(!t.config.gradientLoad || t._fct['scroll'])
				return;
			if(!t.callbackScroll && fct !== undefined)
				t.callbackScroll = fct;
			if(!t.callbackScroll)
				return;
			var d = document, el = d.getElementById(t.id + '_olist');
			if(!el)
				return;
			t._lastScroll = 0;
			t._fct['scroll'] = window.Oby.addEvent(el, 'scroll', function(evt) {
				if(el.scrollHeight > t._lastScroll && el.scrollTop >= (el.scrollHeight - el.offsetHeight - 25)) {
					if(t.callbackScroll)
						t.callbackScroll(t);
					t._lastScroll = el.scrollHeight;
				}
			});
		},
		deinitScroll: function() {
			var t = this;
			if(t.config && t.config.gradientLoad)
				t.config.gradientLoad = false;
			if(!t._fct['scroll'])
				return;
			t.callbackScroll = null;
			var d = document, el = d.getElementById(t.id + '_list');
			if(!el)
				return;
			window.Oby.removeEvent(el, t._fct['scroll']);
		},
		/** Get a node
		 * @param id The node id
		 * @return the node object
		 */
		get: function(id) {
			if(id >= 0 && id < this.lData.length && this.lData[id]) {
				try {
					return this.lData[id];
				} catch(e) { return null; }
			}
			return null;
		},
		/** Make a Selection
		 * @param id The Node Id to select (could be a node object)
		 */
		sel: function(id) {
			if(id === null || id === undefined) return;
			var t=this,d=document,cn=t.lData[id];
			if(!cn) return;
			if(t.config.table) {
				if(t.config.displayFormat)
					cn = window.oNamebox.format(cn, t.config.displayFormat);
				else if(t.config.defaultColumn)
					cn = cn[t.config.defaultColumn];
			}
			if(t.callbackSelection)
				t.callbackSelection(this, id, cn);
		},
		/**
		 *
		 */
		block: function(value) {
			var t = this, p = false, m = (typeof(value) == 'object'), l = null;
			if(m) l = (value.length-1);
			for(var i = t.lData.length - 1; i >= 0; i--) {
				if(!m) {
					if(t.lData[i].key == value) {
						t.lData[i].block = true;
						p = true;
					}
				} else {
					for(var j = l; j >= 0; j--) {
						if(t.lData[i].key == value[j]) {
							t.lData[i].block = true;
							p = true;
							j = -1;
						}
					}
				}
			}
			if(p)
				t.render();
		},
		/**
		 *
		 */
		unblock: function(value) {
			var t = this, p = false;
			for(var i = t.lData.length - 1; i >= 0; i--) {
				if(value === true || t.lData[i].key == value) {
					delete t.lData[i].block;
					p = true;
				}
			}
			if(p)
				t.render();
		},
		/**
		 *
		 */
		find: function(text) {
			var t=this;
			text = text.toLowerCase();
			for(var i = t.lData.length - 1; i >= 0; i--) {
				if(t.lData[i].name.toLowerCase() == text)
					return i;
			}
			return null;
		},
		/**
		 *
		 */
		search: function(text) {
			var t=this,d=document,r=null,e=null,dataLng=0;
			if(text) {
				text = text.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&");
				r = new RegExp("(" + text + ")","i");
				for(var i = t.lData.length - 1; i >= 0; i--) {
					if(!t.lData[i])
						continue;
					if(!t.config.table) {
						if(r.test(t.lData[i].name)) {
							t.lData[i].hidden = false;
							dataLng++;
							t.lData[i].display = t.lData[i].name.replace(r, '<em>$1</em>');
						} else {
							t.lData[i].hidden = true;
							delete t.lData[i].display;
						}
					} else {
						var test = false;
						for(var v in t.lData[i]) {
							if(v != 'key' && t.lData[i].hasOwnProperty(v) && typeof(t.lData[i][v]) == 'string')
								test = r.test(t.lData[i][v]);
							if(test)
								break;
						}
						if(test) {
							t.lData[i].hidden = false;
							dataLng++;
						} else {
							t.lData[i].hidden = true;
							delete t.lData[i].display;
						}
					}
				}
			} else {
				for(var i = t.lData.length - 1; i >= 0; i--) {
					if(t.lData[i]) {
						delete t.lData[i].display;
						delete t.lData[i].hidden;
					}
				}
				dataLng = null;
			}
			t._dataLng = dataLng;
			t.highlighted = null;
			t.render();
		},
		highlightSet: function(id) {
			var t=this;
			if(t.highlighted === id)
				return true;
			if(id === null) {
				t.highlighted = null;
				return true;
			}
			if(!t.lData[id] || t.lData[id].hidden)
				return false;
			t.highlighted = id;
			t.render();

			// Force the ajax loading when we select the last element in the list
			if(t.lData.length > 5 && id == (t.lData.length - 1) && t.config.gradientLoad)
				window.oNameboxes[t.id].loadMore(t);

			var d = document, container = d.getElementById(t.id + '_olist'), el = null, e = null;
			if(!container) e = d.getElementById(t.id);
			if(!container)
				return true;

			el = container.firstChild;
			if(t.config.table)
				el = el.lastChild.firstChild;

			for(var i = el.children.length - 1; i >= 0; i--) {
				e = el.children[i];
				if(e.className != 'oListSelected')
					continue;
				if(container.scrollTop > e.offsetTop)
					container.scrollTop = e.offsetTop;
				else if((container.scrollTop + container.clientHeight) < (e.offsetTop + e.clientHeight))
					container.scrollTop = e.offsetTop + e.clientHeight - container.clientHeight;
			}
			return true;
		},
		highlightGet: function() {
			return this.highlighted;
		},
		highlightMove: function(inc, cpt) {
			var t=this,init=false,min=null;
			if(t.highlighted === null) {
				if(inc > 0)
					t.highlighted = -1;
				else
					t.highlighted =  t.lData.length;
				init = true;
			}
			if(cpt !== undefined && cpt > 0) cpt--; else cpt = 0;
			for(var i = t.highlighted + inc; i >= 0 && i < t.lData.length; i += inc) {
				if(!t.lData[i] || t.lData[i].hidden || t.lData[i].block)
					continue;
				if(cpt-- > 0) {
					min = i;
					continue;
				}
				return t.highlightSet(i);
			}
			if(min !== null)
				return t.highlightSet(min);
			if(init)
				t.highlighted = null;
			return false;
		},
		highlightNext: function(cpt) { return this.highlightMove(1,cpt); },
		highlightPrevious: function(cpt) { return this.highlightMove(-1,cpt); },
	};
	oList.version = 20150807;
	if(!window.oList || window.oList.version < oList.version)
		window.oList = oList;
})();

/**
 * oNamebox
 * version: 0.1.2
 * release date: 2015-08-07
 */
(function(){
	window.oNameboxes = [];
	/**
	 *
 	 * @param {Object} id
 	 * @param {Object} data
 	 * @param {Object} conf
	 */
	var oNamebox = function(id, data, conf) {
		var t = this;
		t.id = id;
		t.data = data;
		t.cache = {};
		conf = conf || {};
		t._conf = conf;
		t._fct = {};
		t._ctrlKey = false;
		t.config = {
			add: conf.add || false,
			add_url: conf.add_url || '',
			default_text: conf.default_text || '',
			default_value: conf.default_value || '',
			img_dir: conf.img_dir || '',
			onlyNode: conf.onlyNode || false,
			map: conf.map || '',
			min: conf.min || 3,
			sort: conf.sort || false,
			tree_url: conf.tree_url || '',
			tree_key: conf.tree_key || 'NODEID',
			url_keyword: conf.url_keyword || 'SEARCH',
			url_pagination: conf.url_pagination || 'PAGE',
			olist: conf.olist || {}
		};
		t.mode = conf.mode || 'list';
		t.multiple = (conf.multiple === undefined || conf.multiple === true);
		t.content = null;
		t.url = conf.url || '';
		t.cb = {};
		t.init();
		window.oNameboxes[id] = t;
	};
	/**
	 *
	 */
	oNamebox.deleteId = function(id) {
		var d = document, el = id;
		if(typeof(id) == "string") el = d.getElementById(id);
		if(!el) return;
		el.parentNode.removeChild(el);
	};
	oNamebox.cancelEvent = function(e) {
		return window.Oby.cancelEvent(e);
	};
	/**
	 *
	 */
	oNamebox.treeCbFct = function(t,url,keyword,tree,node,ev) {
		var o = window.Oby;
		o.xRequest(url.replace(keyword, node.value), null,
			function(xhr,params) {
				var json = o.evalJSON(xhr.responseText);
				if(json.length == 0)
					return tree.emptyDirectory(node);
				var s = json.length, n;
				for(var i = 0; i < s; i++) {
					n = json[i];
					tree.add(node.id, n.status, n.name, n.value, n.url, n.icon);
				}
				tree.update(node);
				if(tree.selectOnOpen) {
					n = tree.find(tree.selectOnOpen);
					if(n) tree.sel(n);
					tree.selectOnOpen = null;
				}
			},
			function(xhr, params) { tree.add(node.id, 0, "error"); tree.update(node); }
		);
		return false;
	};
	/**
	 *
	 */
	oNamebox.format = function(obj, format) {
		var m = format.match(/{[_a-zA-Z0-9]+}/g);
		if(!m)
			return obj;
		var ret = ''+format, r = null, v = '', k = '';
		for(var i = m.length - 1; i >= 0; i--) {
			r = new RegExp(m[i], 'g');
			k = m[i].replace(/{|}/g,'');
			v = '';
			if(obj[k])
				v = obj[k];
			ret = ret.replace(r, v);
		}
		return ret;
	};
	/**
	 *
	 */
	oNamebox.prototype = {
		/**
		 *
		 */
		init: function() {
			var t = this, d = document;
			if(t.mode == 'list') {
				t.container = d.getElementById(t.id+'_olist');
				t.content = new window.oList(t.id,t.config.olist,null,t.data,false);
				t.content.callbackSelection = function(ol,id,value) {
					var d = document, node = ol.get(id);
					if(node.key && node.name)
						t.set(node.name, node.key);
					else if(t.content.config.table && node.key)
						t.set(value, node.key);
					var c = d.getElementById(t.id+"_olist");
					if(c) c.style.display = "none";
					c = d.getElementById(t.id+"_text");
					if(c) c.value = "";
					ol.sel(null);
					if(t._ctrlKey && c)
						c.focus();
				};
				if(t.config.olist && t.config.olist.gradientLoad)
					t.content.callbackScroll = function(el) { t.loadMore(el); };
			} else {
				t.container = d.getElementById(t.id+'_otree');
				var options = {rootImg:(t.config.img_dir+'otree/'), showLoading:false};
				t.content = new window.oTree(t.id, options, null, t.data, false);
				t.content.addIcon("world","world.png");

				if(t.config.tree_url) {
					t.content.callbackFct = function(tree,node,ev) {
						return window.oNamebox.treeCbFct(t.content, t.config.tree_url, t.config.tree_key, tree, node, ev);
					};
				}

				t.content.callbackSelection = function(tree,id) {
					var d = document, node = tree.get(id);
					if(!t.config.onlyNode || node.state == 0) {
						if(node.value && node.name)
							t.set(node.name, node.value);
					} else if(node.state >= 1 && node.state <= 4) {
						tree.s(node);
						return;
					}
					var c = d.getElementById(t.id+"_otree");
					if(c) c.style.display = "none";
					c = d.getElementById(t.id+"_text");
					if(c) c.value = "";
					tree.sel(0);
					if(t._ctrlKey && c)
						c.focus();
				};
			}
			t.content.render(true);

			t.initKeyboard();

			if(t.config.sort) {
				if(!window.hkjQuery && !window.jQuery) return;
				if(!window.hkjQuery) window.hkjQuery = window.jQuery;
				hkjQuery(document).ready(function($) {
					$('#'+t.id).sortable({
						cursor: "move", items: "div",
						stop: function(event, ui) {
							$("#"+t.id+" .nametext").appendTo("#"+t.id);
							$("#"+t.id+"hikaclear").appendTo("#"+t.id);
						}
					});
					$("#"+t.id).disableSelection();
				});
			}
		},
		initKeyboard: function() {
			var t = this, d = document, w = window, o = w.Oby, c = t.content;

			t._fct['doc.keydown'] = o.addEvent(d, 'keydown', function(evt) {
				if(!evt) var evt = w.event;
				if(evt.keyCode == 17) t._ctrlKey = true;
			});
			t._fct['doc.keyup'] = o.addEvent(d, 'keyup', function(evt) {
				if(!evt) var evt = w.event;
				if(evt.keyCode == 17) t._ctrlKey = false;
			});

			var input_elem = d.getElementById(t.id + "_text");
			if(t.mode != 'list' || !input_elem)
				return;

			t._fct['keypress'] = o.addEvent(input_elem, 'keypress', function(evt) {
				if(!evt) var evt = w.event;
				if(evt.keyCode == 13) o.cancelEvent(evt);
			});
			t._fct['keydown'] = o.addEvent(input_elem, 'keydown', function(evt) {
				if(!evt) var evt = w.event;
				if(evt.keyCode == 8 && t.multiple && (t._inputEmpty === undefined || t._inputEmpty === null))
					t._inputEmpty = (this.value == '');
				else if(evt.keyCode == 13)
					t._inputEnter = true;
				else if(evt.keyCode == 33 || evt.keyCode == 34)
					o.cancelEvent(evt);
			});
			t._fct['keyup'] = o.addEvent(input_elem, 'keyup', function(evt) {
				if(!evt) var evt = w.event;
				if(evt.keyCode == 13) {
					// Enter
					if(!t._inputEnter)
						return;
					delete t._inputEnter;
					o.cancelEvent(evt);

					var id = c.highlightGet(), node = null;
					if(id == null && input_elem.value != '')
						id = t.content.find(input_elem.value);
					if(id === null && ((t.content._dataLng !== null && t.content._dataLng == 1) || t.content.lData.length == 1)) {
						for(var i = 0; i < t.content.lData.length - 1; i++) {
							if(!t.content.lData[i] || t.content.lData[i].block || t.content.lData[i].hidden)
								continue;
							id = i;
							break;
						}
					}
					if(id !== null)
						node = c.get(id);
					if(id !== null && node) {
						if(!node.block && !node.hidden && node.key && node.name) {
							t.set(node.name, node.key);
						} else if(!node.block && !node.hidden && c.config.table && node.key) {
							var value = node.value;
							if(c.config.displayFormat)
								value = w.oNamebox.format(node, c.config.displayFormat);
							else if(t.config.defaultColumn)
								value = node[c.config.defaultColumn];
							t.set(value, node.key);
						}
						if(input_elem.value != '') {
							input_elem.value = '';
							t.content.search(null);
						}
					} else if(input_elem.value != '' && t.config.add_url) {
						var add = d.getElementById(t.id + '_add');
						if(add)
							t.create(add.firstChild, true);
					}
				} else if(evt.keyCode == 40) {
					// Down
					c.highlightNext();
					o.cancelEvent(evt);
				} else if(evt.keyCode == 38) {
					// Up
					c.highlightPrevious();
					o.cancelEvent(evt);
				} else if(evt.keyCode == 34) {
					// Page down
					c.highlightNext(5);
					o.cancelEvent(evt);
				} else if(evt.keyCode == 33) {
					// Page up
					c.highlightPrevious(5);
					o.cancelEvent(evt);
				} else if(evt.keyCode == 8 && t.multiple) {
					// backspace
					if(!t._inputEmpty) {
						t._inputEmpty = null;
						return;
					}
					t._inputEmpty = null;
					if(input_elem.value != '')
						return;
					// If multi, delete the last element
					var values = t.get();
					if(!values && !values.length)
						return;
					var v = values.pop(), cur = d.getElementById(t.id + "-" + v.value);
					if(cur && cur.firstChild)
						t.unset(cur.firstChild, v.value);
					o.cancelEvent(evt);
				}
			});
		},
		/**
		 *
		 */
		set: function(name, value) {
			var t = this, d = document;
			if(t.multiple) {
				var blocks = {map: (t.config.map + "[]"), key: value, name: name},
					cur = d.getElementById(t.id + "-" + value);
				if(t.config.map == '')
					blocks['map'] = '';
				if(!cur)
					t.dup(t.id + "tpl", blocks, t.id + "-" + value);
				if(t.mode == 'list')
					t.content.block(value);
			} else {
				var v = d.getElementById(t.id+"_valuehidden"),
					n = d.getElementById(t.id+"_valuetext"),
					a = d.getElementById(t.id+'_add');
				if(v) v.value = value;
				if(n) n.innerHTML = name;
				if(a) a.style.display = 'none';
			}
			t.cache.lastSearch = false;
			if(t.modifiedData) {
				t.loadData(false);
				t.modifiedData = false;
			}
			t.fire('set', {el:t,name:name,value:value});
		},
		/**
		 *
		 */
		unset: function(el, value) {
			var t = this, w = window;
			w.oNamebox.deleteId(el.parentNode);
			if(t.multiple && t.mode == 'list')
				t.content.unblock(value);
			t.fire('unset', {el:t,obj:el,value:value});
		},
		/**
		 *
		 */
		get: function() {
			var t = this, d = document, ret = null;
			if(t.multiple) {
				ret = [];
				var tplElem = d.getElementById(t.id + "tpl");
				if(!tplElem)
					return ret;
				var container = tplElem.parentNode,
					elems = container.getElementsByTagName('input');
				for(var i = 0; i < elems.length; i++) {
					if(elems[i].type.toLowerCase() != 'hidden' || elems[i].name.substring(0,1) == '{')
						continue;
					var txt = elems[i].nextSibling, c = '';
					if(txt && txt.nodeType == 3) {
						if(txt.textContent)
							c = txt.textContent;
						else if(txt.nodeValue)
							c = txt.nodeValue;
						else if(txt.data)
							c = txt.data;
					}
					ret[ ret.length ] = {
						'name': c,
						'value': elems[i].value
					};
				}
			} else {
				ret = {'value':null,'name':null};
				var v = d.getElementById(t.id+"_valuehidden"),
					n = d.getElementById(t.id+"_valuetext");
				if(v) ret.value = v.value;
				if(n) ret.name = n.innerHTML;
			}
			return ret;
		},
		/**
		 *
		 */
		changeUrl: function(url, others) {
			var t = this;
			if(t.url == url)
				return false;
			t.url = url;
			if(others !== undefined && others) {
				if(others.tree)
					t.config.tree_url = others.tree;
				if(others.add)
					t.config.add_url = others.add;
			}
			t.clear();
			if(t.content && t.mode == 'list') {
				t.content.load({});
				window.Oby.xRequest(
					t.url.replace(t.config.url_keyword, ''),
					{},
					function(xhr){
						data = window.Oby.evalJSON(xhr.responseText);
						if(data) {
							t.content.load(data);
							t.data = data;
						}
				},function(xhr){});
			}
			t.fire('changeUrl', {el:t,url:url,others:others});
		},
		/**
		 *
		 */
		destroy: function() {
			var t = this, w = window, d = document,
				input_elem = d.getElementById(t.id + "_text");
			for(var f in t._fct) {
				if(!t._fct.hasOwnProperty(f))
					continue;
				 if(f.substring(0, 4) != 'doc.')
					w.Oby.removeEvent(input_elem, t._fct[f]);
				else
					w.Oby.removeEvent(d, t._fct[f]);
			}
			if(t.content)
				t.content.destroy();

			delete t._fct;
			delete t._conf;
			delete t.data;
			delete t.config;
			delete t.cache;
		},
		/**
		 *
		 */
		search: function(el) {
			var t = this, d = document, w = window,
				s = d.getElementById(t.id+"_span");
			if(typeof(el) == "string")
				el = d.getElementById(el);
			if(!el)
				return false;
			s.innerHTML = el.value;
			el.style.width = s.offsetWidth + 30 + "px";

			if(!t.content)
				return false;

			if(t.cache.lastSearch == el.value)
				return false;

			if(t.config.add) {
				var add_el = d.getElementById(t.id+'_add');
				if(add_el)
					add_el.style.display = (el.value.length == 0) ? 'none' : '';
			}

			if(!t.url) {
				t.content.search(el.value);
			} else {
				if(el.value.length < t.config.min) {
					if(t.modifiedData) {
						t.loadData(false);
						t.modifiedData = false;
					}
					t.content.search(el.value);
				} else {
					var url = t.url.replace(t.config.url_keyword, el.value);
					if(t.config.url_pagination)
						url.replace(t.config.url_pagination, 0);
					w.Oby.xRequest(
						url,
						null,
						function(xhr,params) {
							t.modifiedData = true;
							var p = w.Oby.evalJSON(xhr.responseText),
								data = (p.data ? p.data : p);
							t.loadData(data);
							if(data && data.length)
								t.content.config.gradientLoad = true;
						},
						function(xhr,params) { t.content.search(el.value); }
					);
				}
			}
			t.cache.lastSearch = el.value;
		},
		/**
		 *
		 */
		loadMore: function(el) {
			if(!this.url) {
				el.deinitScroll();
				return false;
			}
			var t = this, d = document, w = window,
				input = d.getElementById(t.id + "_text"),
				url = t.url.replace(t.config.url_keyword, input.value);
			if(t.config.url_pagination)
				url = url.replace(t.config.url_pagination, t.content.lData.length);
			w.Oby.xRequest(
				url,
				null,
				function(xhr,params) {
					var p = w.Oby.evalJSON(xhr.responseText),
						data = ((p.data) ? p.data : p),
						u = false, i = (input.value == '');
					if(data.length == 0) {
						t.content.config.gradientLoad = false;
						if(i) {
							t.url = '';
							t.content.render();
						}
						return;
					}
					for(var k in data) {
						if(!data.hasOwnProperty(k))
							continue;
						if(i && !t.data[k]) {
							t.data[k] = data[k];
							u = true;
						} else if(!i &&  !t.content.lData[k]) {
							t.content.lData[k] = data[k];
							u = true;
						}
					}
					if(u && i) {
						// Keep the highlight selection
						var hl = t.content.highlightGet();
						t.loadData(false);
						t.content.highlightSet(hl);
					}
					if(u && !i)
						t.content.render();
				},
				function(xhr,params) {}
			);
		},
		/**
		 *
		 */
		loadData: function(data) {
			var t = this;
			d = data || t.data;
			if(t.mode == 'list') {
				t.content.load(d);
				if(t.url)
					t.content.config.gradientLoad = true;
				t.content.render();
			} else {
				delete t.content.lNodes;
				t.content.lNodes = [];
				t.content.lNodes[0] = new window.oNode(0,-1);
				t.content.load(d);
				t.content.render();
			}
		},
		/**
		 *
		 */
		focus: function(el) {
			var d = document, w = window, t = this,
				c = d.getElementById(t.id);
			if(typeof(el) == "string")
				el = d.getElementById(el);
			if(el) el.focus();
			if(t.content) t.content.search(el.value);
			if(!t.container)
				return false;
			t.container.style.display = "";
			t.fire('focus', {el: t, input: el});
			var f = null;
			f = function(evt) {
				if (!evt) var evt = window.event;
				var trg = (window.event) ? evt.srcElement : evt.target;
				while(trg != null) {
					if(trg == el || trg == t.container || trg == c)
						return;
					trg = trg.parentNode;
				}
				t.container.style.display = "none";
				t.fire('blur', {el: t, input: el});
				window.Oby.removeEvent(document, "mousedown", f);
			};
			window.Oby.addEvent(document, "mousedown", f);
			return false;
		},
		/**
		 *
		 */
		clear: function() {
			var d = document, t = this,
				el = d.getElementById(t.id), e = null;
			delete t.cache;
			t.cache = {};
			if(!el)
				return false;
			if(t.multiple) {
				for(var i = el.children.length - 1; i >= 0; i--) {
					e = el.children[i];
					if(e.tagName.toLowerCase() == 'div' && e.className == 'namebox' && e.style.display != 'none')
						el.removeChild(e);
				}
				if(t.mode == 'list')
					t.content.unblock(true);
			} else
				t.set(t.config.default_text, t.config.default_value);
		},
		/**
		 *
		 */
		clean: function(el, text) {
			var t = this;
			t.set(text, t.config.default_value);
			window.Oby.cancelEvent();
		},
		/**
		 *
		 */
		create: function(el,conf) {
			var t = this, d = document, w = window;
			window.Oby.cancelEvent();
			if(!t.config.add || !t.config.add_url)
				return false;

			var n = d.getElementById(t.id+"_text"),
				l = d.getElementById(t.id+'_loading');
				value = null;
			if(!n || !n.value || n.value.length == 0)
				return false;
			var check = t.content.find(n.value, true);
			if(check !== null) {
				var node = t.content.get(check);
				t.set(node.name, node.value);
				n.value = '';
				return;
			}
			if(conf && !confirm(encodeURIComponent(n.value) + ' ?'))
				return false;
			value = 'value=' + encodeURIComponent(n.value);
			n.value = '';
			if(el) el.style.display = 'none';
			if(l) l.style.display = '';
			w.Oby.xRequest(t.config.add_url,{mode:'POST',data:value},function(xhr,params){
				if(l) l.style.display = 'none';
				if(el) el.style.display = '';
				if(el) el.parentNode.style.display = 'none';
				if(xhr.responseText) {
					var data = w.Oby.evalJSON(xhr.responseText);
					if(data && data.value && data.name) {
						if(t.mode == 'list')
							t.content.add(data.value, data.name);
						t.set(data.name, data.value);
						t.data[data.value] = data.name;
					}
				}
			},function(xhr,params){
				if(l) l.style.display = 'none';
				if(el) el.style.display = '';
				if(el) el.parentNode.style.display = 'none';
			});
			return false;
		},
		/**
		 *
		 * @param {Object} tplName
		 * @param {Object} htmlblocks
		 * @param {Object} id
		 * @param {Object} extraData
		 * @param {Object} appendTo
		 */
		dup: function(tplName, htmlblocks, id, extraData, appendTo) {
			var d = document, tplElem = d.getElementById(tplName);
			if(!tplElem) return;
			var container = tplElem.parentNode;
			elem = tplElem.cloneNode(true);
			if(!appendTo) {
				container.insertBefore(elem, tplElem);
			} else {
				if(typeof(appendTo) == "string")
					appendTo = d.getElementById(appendTo);
				appendTo.appendChild(elem);
			}
			elem.style.display = "";
			elem.id = '';
			if(id)
				elem.id = id;
			for(var k in htmlblocks) {
				elem.innerHTML = elem.innerHTML.replace(new RegExp("{"+k+"}","g"), htmlblocks[k]);
				elem.innerHTML = elem.innerHTML.replace(new RegExp("%7B"+k+"%7D","g"), htmlblocks[k]);
			}
			if(extraData) {
				for(var k in extraData) {
					elem.innerHTML = elem.innerHTML.replace(new RegExp('{'+k+'}','g'), extraData[k]);
					elem.innerHTML = elem.innerHTML.replace(new RegExp('%7B'+k+'%7D','g'), extraData[k]);
				}
			}
		},
		fire: function(name, params) {
			var t = this, ev;
			if(t.cb[name] === undefined)
				return false;
			for(var e in t.cb[name]) {
				if( e != '_id' ) {
					ev = t.cb[name][e];
					ev(params);
				}
			}
			return true;
		},
		register: function(name, fct) {
			var t = this;
			if(t.cb[name] === undefined )
				t.cb[name] = {'_id':0};
			var id = t.cb[name]['_id'];
			t.cb[name]['_id'] += 1;
			t.cb[name][id] = fct;
			return id;
		},
		unregister: function(name, id) {
			if(t.cb[name] === undefined || t.cb[name][id] === undefined)
				return false;
			t.cb[name][id] = null;
			return true;
		},
	};
	oNamebox.version = 20150807;
	if(!window.oNamebox || !window.oNamebox.version || window.oNamebox.version < oNamebox.version)
		window.oNamebox = oNamebox;
})();
