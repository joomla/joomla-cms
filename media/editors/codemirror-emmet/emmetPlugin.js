(function (root, factory) {if (typeof define === "function" && define.amd) {define(factory);} else {root.emmetPlugin = factory();}}(this, function () {
/**
 * almond 0.2.6 Copyright (c) 2011-2012, The Dojo Foundation All Rights Reserved.
 * Available via the MIT or new BSD license.
 * see: http://github.com/jrburke/almond for details
 */
//Going sloppy to avoid 'use strict' string cost, but strict practices should
//be followed.
/*jslint sloppy: true */
/*global setTimeout: false */

var requirejs, require, define;
(function (undef) {
    var main, req, makeMap, handlers,
        defined = {},
        waiting = {},
        config = {},
        defining = {},
        hasOwn = Object.prototype.hasOwnProperty,
        aps = [].slice;

    function hasProp(obj, prop) {
        return hasOwn.call(obj, prop);
    }

    /**
     * Given a relative module name, like ./something, normalize it to
     * a real name that can be mapped to a path.
     * @param {String} name the relative name
     * @param {String} baseName a real name that the name arg is relative
     * to.
     * @returns {String} normalized name
     */
    function normalize(name, baseName) {
        var nameParts, nameSegment, mapValue, foundMap,
            foundI, foundStarMap, starI, i, j, part,
            baseParts = baseName && baseName.split("/"),
            map = config.map,
            starMap = (map && map['*']) || {};

        //Adjust any relative paths.
        if (name && name.charAt(0) === ".") {
            //If have a base name, try to normalize against it,
            //otherwise, assume it is a top-level require that will
            //be relative to baseUrl in the end.
            if (baseName) {
                //Convert baseName to array, and lop off the last part,
                //so that . matches that "directory" and not name of the baseName's
                //module. For instance, baseName of "one/two/three", maps to
                //"one/two/three.js", but we want the directory, "one/two" for
                //this normalization.
                baseParts = baseParts.slice(0, baseParts.length - 1);

                name = baseParts.concat(name.split("/"));

                //start trimDots
                for (i = 0; i < name.length; i += 1) {
                    part = name[i];
                    if (part === ".") {
                        name.splice(i, 1);
                        i -= 1;
                    } else if (part === "..") {
                        if (i === 1 && (name[2] === '..' || name[0] === '..')) {
                            //End of the line. Keep at least one non-dot
                            //path segment at the front so it can be mapped
                            //correctly to disk. Otherwise, there is likely
                            //no path mapping for a path starting with '..'.
                            //This can still fail, but catches the most reasonable
                            //uses of ..
                            break;
                        } else if (i > 0) {
                            name.splice(i - 1, 2);
                            i -= 2;
                        }
                    }
                }
                //end trimDots

                name = name.join("/");
            } else if (name.indexOf('./') === 0) {
                // No baseName, so this is ID is resolved relative
                // to baseUrl, pull off the leading dot.
                name = name.substring(2);
            }
        }

        //Apply map config if available.
        if ((baseParts || starMap) && map) {
            nameParts = name.split('/');

            for (i = nameParts.length; i > 0; i -= 1) {
                nameSegment = nameParts.slice(0, i).join("/");

                if (baseParts) {
                    //Find the longest baseName segment match in the config.
                    //So, do joins on the biggest to smallest lengths of baseParts.
                    for (j = baseParts.length; j > 0; j -= 1) {
                        mapValue = map[baseParts.slice(0, j).join('/')];

                        //baseName segment has  config, find if it has one for
                        //this name.
                        if (mapValue) {
                            mapValue = mapValue[nameSegment];
                            if (mapValue) {
                                //Match, update name to the new value.
                                foundMap = mapValue;
                                foundI = i;
                                break;
                            }
                        }
                    }
                }

                if (foundMap) {
                    break;
                }

                //Check for a star map match, but just hold on to it,
                //if there is a shorter segment match later in a matching
                //config, then favor over this star map.
                if (!foundStarMap && starMap && starMap[nameSegment]) {
                    foundStarMap = starMap[nameSegment];
                    starI = i;
                }
            }

            if (!foundMap && foundStarMap) {
                foundMap = foundStarMap;
                foundI = starI;
            }

            if (foundMap) {
                nameParts.splice(0, foundI, foundMap);
                name = nameParts.join('/');
            }
        }

        return name;
    }

    function makeRequire(relName, forceSync) {
        return function () {
            //A version of a require function that passes a moduleName
            //value for items that may need to
            //look up paths relative to the moduleName
            return req.apply(undef, aps.call(arguments, 0).concat([relName, forceSync]));
        };
    }

    function makeNormalize(relName) {
        return function (name) {
            return normalize(name, relName);
        };
    }

    function makeLoad(depName) {
        return function (value) {
            defined[depName] = value;
        };
    }

    function callDep(name) {
        if (hasProp(waiting, name)) {
            var args = waiting[name];
            delete waiting[name];
            defining[name] = true;
            main.apply(undef, args);
        }

        if (!hasProp(defined, name) && !hasProp(defining, name)) {
            throw new Error('No ' + name);
        }
        return defined[name];
    }

    //Turns a plugin!resource to [plugin, resource]
    //with the plugin being undefined if the name
    //did not have a plugin prefix.
    function splitPrefix(name) {
        var prefix,
            index = name ? name.indexOf('!') : -1;
        if (index > -1) {
            prefix = name.substring(0, index);
            name = name.substring(index + 1, name.length);
        }
        return [prefix, name];
    }

    /**
     * Makes a name map, normalizing the name, and using a plugin
     * for normalization if necessary. Grabs a ref to plugin
     * too, as an optimization.
     */
    makeMap = function (name, relName) {
        var plugin,
            parts = splitPrefix(name),
            prefix = parts[0];

        name = parts[1];

        if (prefix) {
            prefix = normalize(prefix, relName);
            plugin = callDep(prefix);
        }

        //Normalize according
        if (prefix) {
            if (plugin && plugin.normalize) {
                name = plugin.normalize(name, makeNormalize(relName));
            } else {
                name = normalize(name, relName);
            }
        } else {
            name = normalize(name, relName);
            parts = splitPrefix(name);
            prefix = parts[0];
            name = parts[1];
            if (prefix) {
                plugin = callDep(prefix);
            }
        }

        //Using ridiculous property names for space reasons
        return {
            f: prefix ? prefix + '!' + name : name, //fullName
            n: name,
            pr: prefix,
            p: plugin
        };
    };

    function makeConfig(name) {
        return function () {
            return (config && config.config && config.config[name]) || {};
        };
    }

    handlers = {
        require: function (name) {
            return makeRequire(name);
        },
        exports: function (name) {
            var e = defined[name];
            if (typeof e !== 'undefined') {
                return e;
            } else {
                return (defined[name] = {});
            }
        },
        module: function (name) {
            return {
                id: name,
                uri: '',
                exports: defined[name],
                config: makeConfig(name)
            };
        }
    };

    main = function (name, deps, callback, relName) {
        var cjsModule, depName, ret, map, i,
            args = [],
            usingExports;

        //Use name if no relName
        relName = relName || name;

        //Call the callback to define the module, if necessary.
        if (typeof callback === 'function') {

            //Pull out the defined dependencies and pass the ordered
            //values to the callback.
            //Default to [require, exports, module] if no deps
            deps = !deps.length && callback.length ? ['require', 'exports', 'module'] : deps;
            for (i = 0; i < deps.length; i += 1) {
                map = makeMap(deps[i], relName);
                depName = map.f;

                //Fast path CommonJS standard dependencies.
                if (depName === "require") {
                    args[i] = handlers.require(name);
                } else if (depName === "exports") {
                    //CommonJS module spec 1.1
                    args[i] = handlers.exports(name);
                    usingExports = true;
                } else if (depName === "module") {
                    //CommonJS module spec 1.1
                    cjsModule = args[i] = handlers.module(name);
                } else if (hasProp(defined, depName) ||
                           hasProp(waiting, depName) ||
                           hasProp(defining, depName)) {
                    args[i] = callDep(depName);
                } else if (map.p) {
                    map.p.load(map.n, makeRequire(relName, true), makeLoad(depName), {});
                    args[i] = defined[depName];
                } else {
                    throw new Error(name + ' missing ' + depName);
                }
            }

            ret = callback.apply(defined[name], args);

            if (name) {
                //If setting exports via "module" is in play,
                //favor that over return value and exports. After that,
                //favor a non-undefined return value over exports use.
                if (cjsModule && cjsModule.exports !== undef &&
                        cjsModule.exports !== defined[name]) {
                    defined[name] = cjsModule.exports;
                } else if (ret !== undef || !usingExports) {
                    //Use the return value from the function.
                    defined[name] = ret;
                }
            }
        } else if (name) {
            //May just be an object definition for the module. Only
            //worry about defining if have a module name.
            defined[name] = callback;
        }
    };

    requirejs = require = req = function (deps, callback, relName, forceSync, alt) {
        if (typeof deps === "string") {
            if (handlers[deps]) {
                //callback in this case is really relName
                return handlers[deps](callback);
            }
            //Just return the module wanted. In this scenario, the
            //deps arg is the module name, and second arg (if passed)
            //is just the relName.
            //Normalize module name, if it contains . or ..
            return callDep(makeMap(deps, callback).f);
        } else if (!deps.splice) {
            //deps is a config object, not an array.
            config = deps;
            if (callback.splice) {
                //callback is an array, which means it is a dependency list.
                //Adjust args if there are dependencies
                deps = callback;
                callback = relName;
                relName = null;
            } else {
                deps = undef;
            }
        }

        //Support require(['a'])
        callback = callback || function () {};

        //If relName is a function, it is an errback handler,
        //so remove it.
        if (typeof relName === 'function') {
            relName = forceSync;
            forceSync = alt;
        }

        //Simulate async callback;
        if (forceSync) {
            main(undef, deps, callback, relName);
        } else {
            //Using a non-zero value because of concern for what old browsers
            //do, and latest browsers "upgrade" to 4 if lower value is used:
            //http://www.whatwg.org/specs/web-apps/current-work/multipage/timers.html#dom-windowtimers-settimeout:
            //If want a value immediately, use require('id') instead -- something
            //that works in almond on the global level, but not guaranteed and
            //unlikely to work in other AMD implementations.
            setTimeout(function () {
                main(undef, deps, callback, relName);
            }, 4);
        }

        return req;
    };

    /**
     * Just drops the config on the floor, but returns req in case
     * the config return value is used.
     */
    req.config = function (cfg) {
        config = cfg;
        if (config.deps) {
            req(config.deps, config.callback);
        }
        return req;
    };

    /**
     * Expose module registry for debugging and tooling
     */
    requirejs._defined = defined;

    define = function (name, deps, callback) {

        //This module may not have dependencies
        if (!deps.splice) {
            //deps is not an array, so probably means
            //an object literal or factory function for
            //the value. Adjust args.
            callback = deps;
            deps = [];
        }

        if (!hasProp(defined, name) && !hasProp(waiting, name)) {
            waiting[name] = [name, deps, callback];
        }
    };

    define.amd = {
        jQuery: true
    };
}());

define("vendor/almond", function(){});

define('emmet/emmet', function() {
	return emmet;
});

define('emmet/utils/common', function() {
	return emmet.require('utils/common');
});

define('emmet/utils/action', function() {
	return emmet.require('utils/action');
});

define('emmet/assets/resources', function() {
	return emmet.require('assets/resources');
});

define('emmet/assets/tabStops', function() {
	return emmet.require('assets/tabStops');
});
define("shim", function(){});

/**
 * Emmet Editor interface implementation for CodeMirror.
 * Interface is optimized for multiple cursor usage: authors
 * should run acttion multiple times and update `selectionIndex`
 * property on each iteration.
 */
define('editor',['emmet/utils/common', 'emmet/utils/action', 'emmet/assets/resources', 'emmet/assets/tabStops'], function(utils, actionUtils, res, tabStops) {
	/**
	 * Converts CM’s inner representation of character
	 * position (line, ch) to character index in text
	 * @param  {CodeMirror} cm  CodeMirror instance
	 * @param  {Object}     pos Position object
	 * @return {Number}
	 */
	function posToIndex(cm, pos) {
		if (arguments.length > 2 && typeof pos !== 'object') {
			pos = {line: arguments[1], ch: arguments[2]}
		}
		return cm.indexFromPos(pos);
	}

	/**
	 * Converts charater index in text to CM’s internal object representation
	 * @param  {CodeMirror} cm CodeMirror instance
	 * @param  {Number}     ix Character index in CM document
	 * @return {Object}
	 */
	function indexToPos(cm, ix) {
		return cm.posFromIndex(ix);
	}

	return {
		context: null,
		selectionIndex: 0,
		modeMap: {
			'text/html': 'html',
			'application/xml': 'xml',
			'text/xsl': 'xsl',
			'text/css': 'css',
			'text/x-less': 'less',
			'text/x-scss': 'scss',
			'text/x-sass': 'sass'
		},

		setupContext: function(ctx, selIndex) {
			this.context = ctx;
			this.selectionIndex = selIndex || 0;
			var indentation = '\t';
			if (!ctx.getOption('indentWithTabs')) {
				indentation = utils.repeatString(' ', ctx.getOption('indentUnit'));
			}
			
			res.setVariable('indentation', indentation);
		},

		/**
		 * Returns list of selections for current CodeMirror instance. 
		 * @return {Array}
		 */
		selectionList: function() {
			var cm = this.context;
			return cm.listSelections().map(function(sel) {
				var anchor = posToIndex(cm, sel.anchor);
				var head = posToIndex(cm, sel.head);

				return {
					start: Math.min(anchor, head),
					end: Math.max(anchor, head)
				};
			});
		},

		getCaretPos: function() {
			return this.getSelectionRange().start;
		},

		setCaretPos: function(pos) {
			this.createSelection(pos);
		},

		/**
		 * Returns current selection range (for current selection index)
		 * @return {Object}
		 */
		getSelectionRange: function() {
			return this.selectionList()[this.selectionIndex];
		},

		createSelection: function(start, end) {
			if (typeof end == 'undefined') {
				end = start;
			}

			var sels = this.selectionList();
			var cm = this.context;
			sels[this.selectionIndex] = {start: start, end: end};
			this.context.setSelections(sels.map(function(sel) {
				return {
					head: indexToPos(cm, sel.start),
					anchor: indexToPos(cm, sel.end)
				};
			}));
		},

		/**
		 * Returns current selection
		 * @return {String}
		 */
		getSelection: function() {
			var sel = this.getSelectionRange();
			sel.start = indexToPos(this.context, sel.start);
			sel.end = indexToPos(this.context, sel.end);
			return this.context.getRange(sel.start, sel.end);
		},

		getCurrentLineRange: function() {
			var caret = indexToPos(this.context, this.getCaretPos());
			return {
				start: posToIndex(this.context, caret.line, 0),
				end:   posToIndex(this.context, caret.line, this.context.getLine(caret.line).length)
			};
		},

		getCurrentLine: function() {
			var caret = indexToPos(this.context, this.getCaretPos());
			return this.context.getLine(caret.line) || '';
		},

		replaceContent: function(value, start, end, noIndent) {
			if (typeof end == 'undefined') {
				end = (typeof start == 'undefined') ? this.getContent().length : start;
			}
			if (typeof start == 'undefined') {
				start = 0;
			}
			
			// indent new value
			if (!noIndent) {
				value = utils.padString(value, utils.getLinePaddingFromPosition(this.getContent(), start));
			}
			
			// find new caret position
			var tabstopData = tabStops.extract(value, {
				escape: function(ch) {
					return ch;
				}
			});
			value = tabstopData.text;

			var firstTabStop = tabstopData.tabstops[0] || {start: value.length, end: value.length};
			firstTabStop.start += start;
			firstTabStop.end += start;

			this.context.replaceRange(value, indexToPos(this.context, start), indexToPos(this.context, end));
			this.createSelection(firstTabStop.start, firstTabStop.end);
		},

		getContent: function() {
			return this.context.getValue();
		},

		getSyntax: function() {
			var syntax = this.context.getOption('mode');
			return this.modeMap[syntax] || actionUtils.detectSyntax(this, syntax);
		},

		/**
		 * Returns current output profile name (@see emmet#setupProfile)
		 * @return {String}
		 */
		getProfileName: function() {
			if (this.context.getOption('profile')) {
				return this.context.getOption('profile');
			}
			
			return actionUtils.detectProfile(this);
		},

		/**
		 * Ask user to enter something
		 * @param {String} title Dialog title
		 * @return {String} Entered data
		 */
		prompt: function(title) {
			return prompt(title);
		},

		/**
		 * Returns current editor's file path
		 * @return {String}
		 */
		getFilePath: function() {
			return location.href;
		},

		/**
		 * Check if current editor syntax is valid, e.g. is supported by Emmet
		 * @return {Boolean}
		 */
		isValidSyntax: function() {
			return res.hasSyntax(this.getSyntax());
		}
	};
});
/**
 * Emmet plugin for CodeMirror
 */
define('plugin',['./editor', 'emmet/emmet'], function(editor, emmet) {
	var mac = /Mac/.test(navigator.platform);
	var defaultKeymap = {
		'Cmd-E': 'expand_abbreviation',
		'Tab': 'expand_abbreviation_with_tab',
		'Cmd-D': 'balance_outward',
		'Shift-Cmd-D': 'balance_inward',
		'Cmd-M': 'matching_pair',
		'Shift-Cmd-A': 'wrap_with_abbreviation',
		'Ctrl-Alt-Right': 'next_edit_point',
		'Ctrl-Alt-Left': 'prev_edit_point',
		'Cmd-L': 'select_line',
		'Cmd-Shift-M': 'merge_lines',
		'Cmd-/': 'toggle_comment',
		'Cmd-J': 'split_join_tag',
		'Cmd-K': 'remove_tag',
		'Shift-Cmd-Y': 'evaluate_math_expression',

		'Ctrl-Up': 'increment_number_by_1',
		'Ctrl-Down': 'decrement_number_by_1',
		'Ctrl-Alt-Up': 'increment_number_by_01',
		'Ctrl-Alt-Down': 'decrement_number_by_01',
		'Shift-Ctrl-Up': 'increment_number_by_10',
		'Shift-Ctrl-Down': 'decrement_number_by_10',

		'Shift-Cmd-.': 'select_next_item',
		'Shift-Cmd-,': 'select_previous_item',
		'Cmd-B': 'reflect_css_value',
		
		'Enter': 'insert_formatted_line_break_only'
	};

	// actions that should be performed in single selection mode
	var singleSelectionActions = [
		'prev_edit_point', 'next_edit_point', 'merge_lines',
		'reflect_css_value', 'select_next_item', 'select_previous_item',
		'wrap_with_abbreviation', 'update_tag', 'insert_formatted_line_break_only'
	];

	// add “profile” property to CodeMirror defaults so in won’t be lost
	// then CM instance is instantiated with “profile” property
	if (CodeMirror.defineOption) {
		CodeMirror.defineOption('profile', 'html');
	} else {
		CodeMirror.defaults.profile = 'html';
	}

	function noop() {
		if (CodeMirror.version >= '3.1') {
			return CodeMirror.Pass;
		}
		
		throw CodeMirror.Pass;
	}

	/**
	 * Emmet action decorator: creates a command function
	 * for CodeMirror and executes Emmet action as single
	 * undo command
	 * @param  {String} name Action name
	 * @return {Function}
	 */
	function actionDecorator(name) {
		return function(cm) {
			editor.setupContext(cm);
			var result;
			cm.operation(function() {
				result = runAction(name, cm);
			});
			return result;
		};
	}

	/**
	 * Same as `actionDecorator()` but executes action
	 * with multiple selections
	 * @param  {String} name Action name
	 * @return {Function}
	 */
	function multiSelectionActionDecorator(name) {
		return function(cm) {
			editor.setupContext(cm);
			var selections = editor.selectionList();
			var result = null;
			cm.operation(function() {
				for (var i = 0, il = selections.length; i < il; i++) {
					editor.selectionIndex = i;
					result = runAction(name, cm);
					if (result === CodeMirror.Pass) {
						break;
					}
				}
			});
			return result;
		};
	}

	/**
	 * Runs Emmet action
	 * @param  {String}     name Action name
	 * @param  {CodeMirror} cm CodeMirror instance
	 * @return {Boolean}    Returns `true` if action is performed
	 * successfully
	 */
	function runAction(name, cm) {
		if (name == 'expand_abbreviation_with_tab' && (cm.somethingSelected() || !editor.isValidSyntax())) {
			// pass through Tab key handler if there's a selection
			return noop();
		}
		
		var result = false;
		try {
			result = emmet.run(name, editor);
			if (!result && name == 'insert_formatted_line_break_only') {
				return noop();
			}
		} catch (e) {}

		return result;
	}

	function systemKeybinding(key) {
		return !mac ? key.replace('Cmd', 'Ctrl') : key;
	}

	/**
	 * Adds given `key` as keybinding for Emmet `action`
	 * @param {String} key    Keyboard shortcut
	 * @param {String} action Emmet action name
	 */
	function addKeybinding(key, action) {
		key = systemKeybinding(key);
		CodeMirror.keyMap['default'][key] = 'emmet.' + action;
	}

	// add actions and default keybindings
	Object.keys(defaultKeymap).forEach(function(key) {
		var action = defaultKeymap[key];
		var cmCommand = 'emmet.' + action;
		if (!CodeMirror.commands[cmCommand]) {
			CodeMirror.commands[cmCommand] = ~singleSelectionActions.indexOf(action)
				? actionDecorator(action)
				: multiSelectionActionDecorator(action);
		}

		addKeybinding(key, action);
	});

	return {
		emmet: emmet,
		editor: editor,
		/**
		 * Adds new keybindings for Emmet action. The expected format
		 * of `keymap` object is the same as default `keymap`.
		 * @param {Object} keymap
		 */
		setKeymap: function(keymap) {
			Object.keys(keymap).forEach(function(key) {
				addKeybinding(key, keymap[key]);
			});
		},

		/**
		 * Clears all Emmet keybindings
		 */
		clearKeymap: function() {
			var cmMap = CodeMirror.keyMap['default'];
			var reEmmetAction = /^emmet\./;
			Object.keys(cmMap).forEach(function(p) {
				if (reEmmetAction.test(cmMap[p])) {
					delete cmMap[p];
				}
			});
		},

		addKeybinding: addKeybinding,

		/**
		 * Removes given keybinding or any keybinging bound to
		 * given action name
		 * @param  {String} name Either keybinding or Emmet action name
		 */
		removeKeybinding: function(name) {
			name = systemKeybinding(name);
			var cmMap = CodeMirror.keyMap['default'];
			if (name in cmMap) {
				delete cmMap[name];
			} else {
				name = 'emmet.' + name;
				Object.keys(cmMap).forEach(function(p) {
					if (cmMap[p] === name) {
						delete cmMap[p];
					}
				});
			}
		}
	};
});;return require('plugin');}));