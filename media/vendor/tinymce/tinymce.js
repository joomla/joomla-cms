// 4.7.9 (2018-02-27)
(function () {
(function () {
  'use strict';

  var noop = function () {
  };
  var noarg = function (f) {
    return function () {
      return f();
    };
  };
  var compose = function (fa, fb) {
    return function () {
      return fa(fb.apply(null, arguments));
    };
  };
  var constant = function (value) {
    return function () {
      return value;
    };
  };
  var identity = function (x) {
    return x;
  };
  var tripleEquals = function (a, b) {
    return a === b;
  };
  var curry = function (f) {
    var args = new Array(arguments.length - 1);
    for (var i = 1; i < arguments.length; i++)
      args[i - 1] = arguments[i];
    return function () {
      var newArgs = new Array(arguments.length);
      for (var j = 0; j < newArgs.length; j++)
        newArgs[j] = arguments[j];
      var all = args.concat(newArgs);
      return f.apply(null, all);
    };
  };
  var not = function (f) {
    return function () {
      return !f.apply(null, arguments);
    };
  };
  var die = function (msg) {
    return function () {
      throw new Error(msg);
    };
  };
  var apply = function (f) {
    return f();
  };
  var call = function (f) {
    f();
  };
  var never = constant(false);
  var always = constant(true);
  var $_enfhkv6je5o2ply = {
    noop: noop,
    noarg: noarg,
    compose: compose,
    constant: constant,
    identity: identity,
    tripleEquals: tripleEquals,
    curry: curry,
    not: not,
    die: die,
    apply: apply,
    call: call,
    never: never,
    always: always
  };

  var never$1 = $_enfhkv6je5o2ply.never;
  var always$1 = $_enfhkv6je5o2ply.always;
  var none = function () {
    return NONE;
  };
  var NONE = function () {
    var eq = function (o) {
      return o.isNone();
    };
    var call = function (thunk) {
      return thunk();
    };
    var id = function (n) {
      return n;
    };
    var noop = function () {
    };
    var me = {
      fold: function (n, s) {
        return n();
      },
      is: never$1,
      isSome: never$1,
      isNone: always$1,
      getOr: id,
      getOrThunk: call,
      getOrDie: function (msg) {
        throw new Error(msg || 'error: getOrDie called on none.');
      },
      or: id,
      orThunk: call,
      map: none,
      ap: none,
      each: noop,
      bind: none,
      flatten: none,
      exists: never$1,
      forall: always$1,
      filter: none,
      equals: eq,
      equals_: eq,
      toArray: function () {
        return [];
      },
      toString: $_enfhkv6je5o2ply.constant('none()')
    };
    if (Object.freeze)
      Object.freeze(me);
    return me;
  }();
  var some = function (a) {
    var constant_a = function () {
      return a;
    };
    var self = function () {
      return me;
    };
    var map = function (f) {
      return some(f(a));
    };
    var bind = function (f) {
      return f(a);
    };
    var me = {
      fold: function (n, s) {
        return s(a);
      },
      is: function (v) {
        return a === v;
      },
      isSome: always$1,
      isNone: never$1,
      getOr: constant_a,
      getOrThunk: constant_a,
      getOrDie: constant_a,
      or: self,
      orThunk: self,
      map: map,
      ap: function (optfab) {
        return optfab.fold(none, function (fab) {
          return some(fab(a));
        });
      },
      each: function (f) {
        f(a);
      },
      bind: bind,
      flatten: constant_a,
      exists: bind,
      forall: bind,
      filter: function (f) {
        return f(a) ? me : NONE;
      },
      equals: function (o) {
        return o.is(a);
      },
      equals_: function (o, elementEq) {
        return o.fold(never$1, function (b) {
          return elementEq(a, b);
        });
      },
      toArray: function () {
        return [a];
      },
      toString: function () {
        return 'some(' + a + ')';
      }
    };
    return me;
  };
  var from = function (value) {
    return value === null || value === undefined ? NONE : some(value);
  };
  var Option = {
    some: some,
    none: none,
    from: from
  };

  var rawIndexOf = function () {
    var pIndexOf = Array.prototype.indexOf;
    var fastIndex = function (xs, x) {
      return pIndexOf.call(xs, x);
    };
    var slowIndex = function (xs, x) {
      return slowIndexOf(xs, x);
    };
    return pIndexOf === undefined ? slowIndex : fastIndex;
  }();
  var indexOf = function (xs, x) {
    var r = rawIndexOf(xs, x);
    return r === -1 ? Option.none() : Option.some(r);
  };
  var contains = function (xs, x) {
    return rawIndexOf(xs, x) > -1;
  };
  var exists = function (xs, pred) {
    return findIndex(xs, pred).isSome();
  };
  var range = function (num, f) {
    var r = [];
    for (var i = 0; i < num; i++) {
      r.push(f(i));
    }
    return r;
  };
  var chunk = function (array, size) {
    var r = [];
    for (var i = 0; i < array.length; i += size) {
      var s = array.slice(i, i + size);
      r.push(s);
    }
    return r;
  };
  var map = function (xs, f) {
    var len = xs.length;
    var r = new Array(len);
    for (var i = 0; i < len; i++) {
      var x = xs[i];
      r[i] = f(x, i, xs);
    }
    return r;
  };
  var each = function (xs, f) {
    for (var i = 0, len = xs.length; i < len; i++) {
      var x = xs[i];
      f(x, i, xs);
    }
  };
  var eachr = function (xs, f) {
    for (var i = xs.length - 1; i >= 0; i--) {
      var x = xs[i];
      f(x, i, xs);
    }
  };
  var partition = function (xs, pred) {
    var pass = [];
    var fail = [];
    for (var i = 0, len = xs.length; i < len; i++) {
      var x = xs[i];
      var arr = pred(x, i, xs) ? pass : fail;
      arr.push(x);
    }
    return {
      pass: pass,
      fail: fail
    };
  };
  var filter = function (xs, pred) {
    var r = [];
    for (var i = 0, len = xs.length; i < len; i++) {
      var x = xs[i];
      if (pred(x, i, xs)) {
        r.push(x);
      }
    }
    return r;
  };
  var groupBy = function (xs, f) {
    if (xs.length === 0) {
      return [];
    } else {
      var wasType = f(xs[0]);
      var r = [];
      var group = [];
      for (var i = 0, len = xs.length; i < len; i++) {
        var x = xs[i];
        var type = f(x);
        if (type !== wasType) {
          r.push(group);
          group = [];
        }
        wasType = type;
        group.push(x);
      }
      if (group.length !== 0) {
        r.push(group);
      }
      return r;
    }
  };
  var foldr = function (xs, f, acc) {
    eachr(xs, function (x) {
      acc = f(acc, x);
    });
    return acc;
  };
  var foldl = function (xs, f, acc) {
    each(xs, function (x) {
      acc = f(acc, x);
    });
    return acc;
  };
  var find = function (xs, pred) {
    for (var i = 0, len = xs.length; i < len; i++) {
      var x = xs[i];
      if (pred(x, i, xs)) {
        return Option.some(x);
      }
    }
    return Option.none();
  };
  var findIndex = function (xs, pred) {
    for (var i = 0, len = xs.length; i < len; i++) {
      var x = xs[i];
      if (pred(x, i, xs)) {
        return Option.some(i);
      }
    }
    return Option.none();
  };
  var slowIndexOf = function (xs, x) {
    for (var i = 0, len = xs.length; i < len; ++i) {
      if (xs[i] === x) {
        return i;
      }
    }
    return -1;
  };
  var push = Array.prototype.push;
  var flatten = function (xs) {
    var r = [];
    for (var i = 0, len = xs.length; i < len; ++i) {
      if (!Array.prototype.isPrototypeOf(xs[i]))
        throw new Error('Arr.flatten item ' + i + ' was not an array, input: ' + xs);
      push.apply(r, xs[i]);
    }
    return r;
  };
  var bind = function (xs, f) {
    var output = map(xs, f);
    return flatten(output);
  };
  var forall = function (xs, pred) {
    for (var i = 0, len = xs.length; i < len; ++i) {
      var x = xs[i];
      if (pred(x, i, xs) !== true) {
        return false;
      }
    }
    return true;
  };
  var equal = function (a1, a2) {
    return a1.length === a2.length && forall(a1, function (x, i) {
      return x === a2[i];
    });
  };
  var slice = Array.prototype.slice;
  var reverse = function (xs) {
    var r = slice.call(xs, 0);
    r.reverse();
    return r;
  };
  var difference = function (a1, a2) {
    return filter(a1, function (x) {
      return !contains(a2, x);
    });
  };
  var mapToObject = function (xs, f) {
    var r = {};
    for (var i = 0, len = xs.length; i < len; i++) {
      var x = xs[i];
      r[String(x)] = f(x, i);
    }
    return r;
  };
  var pure = function (x) {
    return [x];
  };
  var sort = function (xs, comparator) {
    var copy = slice.call(xs, 0);
    copy.sort(comparator);
    return copy;
  };
  var head = function (xs) {
    return xs.length === 0 ? Option.none() : Option.some(xs[0]);
  };
  var last = function (xs) {
    return xs.length === 0 ? Option.none() : Option.some(xs[xs.length - 1]);
  };
  var $_bwcteb4je5o2pl4 = {
    map: map,
    each: each,
    eachr: eachr,
    partition: partition,
    filter: filter,
    groupBy: groupBy,
    indexOf: indexOf,
    foldr: foldr,
    foldl: foldl,
    find: find,
    findIndex: findIndex,
    flatten: flatten,
    bind: bind,
    forall: forall,
    exists: exists,
    contains: contains,
    equal: equal,
    reverse: reverse,
    chunk: chunk,
    difference: difference,
    mapToObject: mapToObject,
    pure: pure,
    sort: sort,
    range: range,
    head: head,
    last: last
  };

  var global = typeof window !== 'undefined' ? window : Function('return this;')();

  var path = function (parts, scope) {
    var o = scope !== undefined && scope !== null ? scope : global;
    for (var i = 0; i < parts.length && o !== undefined && o !== null; ++i)
      o = o[parts[i]];
    return o;
  };
  var resolve = function (p, scope) {
    var parts = p.split('.');
    return path(parts, scope);
  };
  var step = function (o, part) {
    if (o[part] === undefined || o[part] === null)
      o[part] = {};
    return o[part];
  };
  var forge = function (parts, target) {
    var o = target !== undefined ? target : global;
    for (var i = 0; i < parts.length; ++i)
      o = step(o, parts[i]);
    return o;
  };
  var namespace = function (name, target) {
    var parts = name.split('.');
    return forge(parts, target);
  };
  var $_m1ooucje5o2ppc = {
    path: path,
    resolve: resolve,
    forge: forge,
    namespace: namespace
  };

  var unsafe = function (name, scope) {
    return $_m1ooucje5o2ppc.resolve(name, scope);
  };
  var getOrDie = function (name, scope) {
    var actual = unsafe(name, scope);
    if (actual === undefined || actual === null)
      throw name + ' not available on this browser';
    return actual;
  };
  var $_lmz5vbje5o2pp9 = { getOrDie: getOrDie };

  var url = function () {
    return $_lmz5vbje5o2pp9.getOrDie('URL');
  };
  var createObjectURL = function (blob) {
    return url().createObjectURL(blob);
  };
  var revokeObjectURL = function (u) {
    url().revokeObjectURL(u);
  };
  var $_g0v736aje5o2pp8 = {
    createObjectURL: createObjectURL,
    revokeObjectURL: revokeObjectURL
  };

  var nav = navigator;
  var userAgent = nav.userAgent;
  var opera;
  var webkit;
  var ie;
  var ie11;
  var ie12;
  var gecko;
  var mac;
  var iDevice;
  var android;
  var fileApi;
  var phone;
  var tablet;
  var windowsPhone;
  var matchMediaQuery = function (query) {
    return 'matchMedia' in window ? matchMedia(query).matches : false;
  };
  opera = false;
  android = /Android/.test(userAgent);
  webkit = /WebKit/.test(userAgent);
  ie = !webkit && !opera && /MSIE/gi.test(userAgent) && /Explorer/gi.test(nav.appName);
  ie = ie && /MSIE (\w+)\./.exec(userAgent)[1];
  ie11 = userAgent.indexOf('Trident/') !== -1 && (userAgent.indexOf('rv:') !== -1 || nav.appName.indexOf('Netscape') !== -1) ? 11 : false;
  ie12 = userAgent.indexOf('Edge/') !== -1 && !ie && !ie11 ? 12 : false;
  ie = ie || ie11 || ie12;
  gecko = !webkit && !ie11 && /Gecko/.test(userAgent);
  mac = userAgent.indexOf('Mac') !== -1;
  iDevice = /(iPad|iPhone)/.test(userAgent);
  fileApi = 'FormData' in window && 'FileReader' in window && 'URL' in window && !!$_g0v736aje5o2pp8.createObjectURL;
  phone = matchMediaQuery('only screen and (max-device-width: 480px)') && (android || iDevice);
  tablet = matchMediaQuery('only screen and (min-width: 800px)') && (android || iDevice);
  windowsPhone = userAgent.indexOf('Windows Phone') !== -1;
  if (ie12) {
    webkit = false;
  }
  var contentEditable = !iDevice || fileApi || parseInt(userAgent.match(/AppleWebKit\/(\d*)/)[1], 10) >= 534;
  var $_9bd8pn9je5o2pp2 = {
    opera: opera,
    webkit: webkit,
    ie: ie,
    gecko: gecko,
    mac: mac,
    iOS: iDevice,
    android: android,
    contentEditable: contentEditable,
    transparentSrc: 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7',
    caretAfter: ie !== 8,
    range: window.getSelection && 'Range' in window,
    documentMode: ie && !ie12 ? document.documentMode || 7 : 10,
    fileApi: fileApi,
    ceFalse: ie === false || ie > 8,
    cacheSuffix: '',
    container: null,
    overrideViewPort: null,
    experimentalShadowDom: false,
    canHaveCSP: ie === false || ie > 11,
    desktop: !phone && !tablet,
    windowsPhone: windowsPhone
  };

  var promise = function () {
    function bind(fn, thisArg) {
      return function () {
        fn.apply(thisArg, arguments);
      };
    }
    var isArray = Array.isArray || function (value) {
      return Object.prototype.toString.call(value) === '[object Array]';
    };
    var Promise = function (fn) {
      if (typeof this !== 'object') {
        throw new TypeError('Promises must be constructed via new');
      }
      if (typeof fn !== 'function') {
        throw new TypeError('not a function');
      }
      this._state = null;
      this._value = null;
      this._deferreds = [];
      doResolve(fn, bind(resolve, this), bind(reject, this));
    };
    var asap = Promise.immediateFn || typeof setImmediate === 'function' && setImmediate || function (fn) {
      setTimeout(fn, 1);
    };
    function handle(deferred) {
      var me = this;
      if (this._state === null) {
        this._deferreds.push(deferred);
        return;
      }
      asap(function () {
        var cb = me._state ? deferred.onFulfilled : deferred.onRejected;
        if (cb === null) {
          (me._state ? deferred.resolve : deferred.reject)(me._value);
          return;
        }
        var ret;
        try {
          ret = cb(me._value);
        } catch (e) {
          deferred.reject(e);
          return;
        }
        deferred.resolve(ret);
      });
    }
    function resolve(newValue) {
      try {
        if (newValue === this) {
          throw new TypeError('A promise cannot be resolved with itself.');
        }
        if (newValue && (typeof newValue === 'object' || typeof newValue === 'function')) {
          var then = newValue.then;
          if (typeof then === 'function') {
            doResolve(bind(then, newValue), bind(resolve, this), bind(reject, this));
            return;
          }
        }
        this._state = true;
        this._value = newValue;
        finale.call(this);
      } catch (e) {
        reject.call(this, e);
      }
    }
    function reject(newValue) {
      this._state = false;
      this._value = newValue;
      finale.call(this);
    }
    function finale() {
      for (var i = 0, len = this._deferreds.length; i < len; i++) {
        handle.call(this, this._deferreds[i]);
      }
      this._deferreds = null;
    }
    function Handler(onFulfilled, onRejected, resolve, reject) {
      this.onFulfilled = typeof onFulfilled === 'function' ? onFulfilled : null;
      this.onRejected = typeof onRejected === 'function' ? onRejected : null;
      this.resolve = resolve;
      this.reject = reject;
    }
    function doResolve(fn, onFulfilled, onRejected) {
      var done = false;
      try {
        fn(function (value) {
          if (done) {
            return;
          }
          done = true;
          onFulfilled(value);
        }, function (reason) {
          if (done) {
            return;
          }
          done = true;
          onRejected(reason);
        });
      } catch (ex) {
        if (done) {
          return;
        }
        done = true;
        onRejected(ex);
      }
    }
    Promise.prototype.catch = function (onRejected) {
      return this.then(null, onRejected);
    };
    Promise.prototype.then = function (onFulfilled, onRejected) {
      var me = this;
      return new Promise(function (resolve, reject) {
        handle.call(me, new Handler(onFulfilled, onRejected, resolve, reject));
      });
    };
    Promise.all = function () {
      var args = Array.prototype.slice.call(arguments.length === 1 && isArray(arguments[0]) ? arguments[0] : arguments);
      return new Promise(function (resolve, reject) {
        if (args.length === 0) {
          return resolve([]);
        }
        var remaining = args.length;
        function res(i, val) {
          try {
            if (val && (typeof val === 'object' || typeof val === 'function')) {
              var then = val.then;
              if (typeof then === 'function') {
                then.call(val, function (val) {
                  res(i, val);
                }, reject);
                return;
              }
            }
            args[i] = val;
            if (--remaining === 0) {
              resolve(args);
            }
          } catch (ex) {
            reject(ex);
          }
        }
        for (var i = 0; i < args.length; i++) {
          res(i, args[i]);
        }
      });
    };
    Promise.resolve = function (value) {
      if (value && typeof value === 'object' && value.constructor === Promise) {
        return value;
      }
      return new Promise(function (resolve) {
        resolve(value);
      });
    };
    Promise.reject = function (value) {
      return new Promise(function (resolve, reject) {
        reject(value);
      });
    };
    Promise.race = function (values) {
      return new Promise(function (resolve, reject) {
        for (var i = 0, len = values.length; i < len; i++) {
          values[i].then(resolve, reject);
        }
      });
    };
    return Promise;
  };
  var promiseObj = window.Promise ? window.Promise : promise();

  var requestAnimationFramePromise;
  var requestAnimationFrame = function (callback, element) {
    var i, requestAnimationFrameFunc = window.requestAnimationFrame;
    var vendors = [
      'ms',
      'moz',
      'webkit'
    ];
    var featurefill = function (callback) {
      window.setTimeout(callback, 0);
    };
    for (i = 0; i < vendors.length && !requestAnimationFrameFunc; i++) {
      requestAnimationFrameFunc = window[vendors[i] + 'RequestAnimationFrame'];
    }
    if (!requestAnimationFrameFunc) {
      requestAnimationFrameFunc = featurefill;
    }
    requestAnimationFrameFunc(callback, element);
  };
  var wrappedSetTimeout = function (callback, time) {
    if (typeof time !== 'number') {
      time = 0;
    }
    return setTimeout(callback, time);
  };
  var wrappedSetInterval = function (callback, time) {
    if (typeof time !== 'number') {
      time = 1;
    }
    return setInterval(callback, time);
  };
  var wrappedClearTimeout = function (id) {
    return clearTimeout(id);
  };
  var wrappedClearInterval = function (id) {
    return clearInterval(id);
  };
  var debounce = function (callback, time) {
    var timer, func;
    func = function () {
      var args = arguments;
      clearTimeout(timer);
      timer = wrappedSetTimeout(function () {
        callback.apply(this, args);
      }, time);
    };
    func.stop = function () {
      clearTimeout(timer);
    };
    return func;
  };
  var $_db41wogje5o2ps3 = {
    requestAnimationFrame: function (callback, element) {
      if (requestAnimationFramePromise) {
        requestAnimationFramePromise.then(callback);
        return;
      }
      requestAnimationFramePromise = new promiseObj(function (resolve) {
        if (!element) {
          element = document.body;
        }
        requestAnimationFrame(resolve, element);
      }).then(callback);
    },
    setTimeout: wrappedSetTimeout,
    setInterval: wrappedSetInterval,
    setEditorTimeout: function (editor, callback, time) {
      return wrappedSetTimeout(function () {
        if (!editor.removed) {
          callback();
        }
      }, time);
    },
    setEditorInterval: function (editor, callback, time) {
      var timer;
      timer = wrappedSetInterval(function () {
        if (!editor.removed) {
          callback();
        } else {
          clearInterval(timer);
        }
      }, time);
      return timer;
    },
    debounce: debounce,
    throttle: debounce,
    clearInterval: wrappedClearInterval,
    clearTimeout: wrappedClearTimeout
  };

  var eventExpandoPrefix = 'mce-data-';
  var mouseEventRe = /^(?:mouse|contextmenu)|click/;
  var deprecated = {
    keyLocation: 1,
    layerX: 1,
    layerY: 1,
    returnValue: 1,
    webkitMovementX: 1,
    webkitMovementY: 1,
    keyIdentifier: 1
  };
  var hasIsDefaultPrevented = function (event) {
    return event.isDefaultPrevented === returnTrue || event.isDefaultPrevented === returnFalse;
  };
  var returnFalse = function () {
    return false;
  };
  var returnTrue = function () {
    return true;
  };
  var addEvent = function (target, name, callback, capture) {
    if (target.addEventListener) {
      target.addEventListener(name, callback, capture || false);
    } else if (target.attachEvent) {
      target.attachEvent('on' + name, callback);
    }
  };
  var removeEvent = function (target, name, callback, capture) {
    if (target.removeEventListener) {
      target.removeEventListener(name, callback, capture || false);
    } else if (target.detachEvent) {
      target.detachEvent('on' + name, callback);
    }
  };
  var getTargetFromShadowDom = function (event, defaultTarget) {
    var path, target = defaultTarget;
    path = event.path;
    if (path && path.length > 0) {
      target = path[0];
    }
    if (event.composedPath) {
      path = event.composedPath();
      if (path && path.length > 0) {
        target = path[0];
      }
    }
    return target;
  };
  var fix = function (originalEvent, data) {
    var name;
    var event = data || {};
    for (name in originalEvent) {
      if (!deprecated[name]) {
        event[name] = originalEvent[name];
      }
    }
    if (!event.target) {
      event.target = event.srcElement || document;
    }
    if ($_9bd8pn9je5o2pp2.experimentalShadowDom) {
      event.target = getTargetFromShadowDom(originalEvent, event.target);
    }
    if (originalEvent && mouseEventRe.test(originalEvent.type) && originalEvent.pageX === undefined && originalEvent.clientX !== undefined) {
      var eventDoc = event.target.ownerDocument || document;
      var doc = eventDoc.documentElement;
      var body = eventDoc.body;
      event.pageX = originalEvent.clientX + (doc && doc.scrollLeft || body && body.scrollLeft || 0) - (doc && doc.clientLeft || body && body.clientLeft || 0);
      event.pageY = originalEvent.clientY + (doc && doc.scrollTop || body && body.scrollTop || 0) - (doc && doc.clientTop || body && body.clientTop || 0);
    }
    event.preventDefault = function () {
      event.isDefaultPrevented = returnTrue;
      if (originalEvent) {
        if (originalEvent.preventDefault) {
          originalEvent.preventDefault();
        } else {
          originalEvent.returnValue = false;
        }
      }
    };
    event.stopPropagation = function () {
      event.isPropagationStopped = returnTrue;
      if (originalEvent) {
        if (originalEvent.stopPropagation) {
          originalEvent.stopPropagation();
        } else {
          originalEvent.cancelBubble = true;
        }
      }
    };
    event.stopImmediatePropagation = function () {
      event.isImmediatePropagationStopped = returnTrue;
      event.stopPropagation();
    };
    if (hasIsDefaultPrevented(event) === false) {
      event.isDefaultPrevented = returnFalse;
      event.isPropagationStopped = returnFalse;
      event.isImmediatePropagationStopped = returnFalse;
    }
    if (typeof event.metaKey === 'undefined') {
      event.metaKey = false;
    }
    return event;
  };
  var bindOnReady = function (win, callback, eventUtils) {
    var doc = win.document, event = { type: 'ready' };
    if (eventUtils.domLoaded) {
      callback(event);
      return;
    }
    var isDocReady = function () {
      return doc.readyState === 'complete' || doc.readyState === 'interactive' && doc.body;
    };
    var readyHandler = function () {
      if (!eventUtils.domLoaded) {
        eventUtils.domLoaded = true;
        callback(event);
      }
    };
    var waitForDomLoaded = function () {
      if (isDocReady()) {
        removeEvent(doc, 'readystatechange', waitForDomLoaded);
        readyHandler();
      }
    };
    var tryScroll = function () {
      try {
        doc.documentElement.doScroll('left');
      } catch (ex) {
        $_db41wogje5o2ps3.setTimeout(tryScroll);
        return;
      }
      readyHandler();
    };
    if (doc.addEventListener && !($_9bd8pn9je5o2pp2.ie && $_9bd8pn9je5o2pp2.ie < 11)) {
      if (isDocReady()) {
        readyHandler();
      } else {
        addEvent(win, 'DOMContentLoaded', readyHandler);
      }
    } else {
      addEvent(doc, 'readystatechange', waitForDomLoaded);
      if (doc.documentElement.doScroll && win.self === win.top) {
        tryScroll();
      }
    }
    addEvent(win, 'load', readyHandler);
  };
  var EventUtils = function () {
    var self = this;
    var events = {}, count, expando, hasFocusIn, hasMouseEnterLeave, mouseEnterLeave;
    expando = eventExpandoPrefix + (+new Date()).toString(32);
    hasMouseEnterLeave = 'onmouseenter' in document.documentElement;
    hasFocusIn = 'onfocusin' in document.documentElement;
    mouseEnterLeave = {
      mouseenter: 'mouseover',
      mouseleave: 'mouseout'
    };
    count = 1;
    self.domLoaded = false;
    self.events = events;
    var executeHandlers = function (evt, id) {
      var callbackList, i, l, callback;
      var container = events[id];
      callbackList = container && container[evt.type];
      if (callbackList) {
        for (i = 0, l = callbackList.length; i < l; i++) {
          callback = callbackList[i];
          if (callback && callback.func.call(callback.scope, evt) === false) {
            evt.preventDefault();
          }
          if (evt.isImmediatePropagationStopped()) {
            return;
          }
        }
      }
    };
    self.bind = function (target, names, callback, scope) {
      var id, callbackList, i, name, fakeName, nativeHandler, capture;
      var win = window;
      var defaultNativeHandler = function (evt) {
        executeHandlers(fix(evt || win.event), id);
      };
      if (!target || target.nodeType === 3 || target.nodeType === 8) {
        return;
      }
      if (!target[expando]) {
        id = count++;
        target[expando] = id;
        events[id] = {};
      } else {
        id = target[expando];
      }
      scope = scope || target;
      names = names.split(' ');
      i = names.length;
      while (i--) {
        name = names[i];
        nativeHandler = defaultNativeHandler;
        fakeName = capture = false;
        if (name === 'DOMContentLoaded') {
          name = 'ready';
        }
        if (self.domLoaded && name === 'ready' && target.readyState === 'complete') {
          callback.call(scope, fix({ type: name }));
          continue;
        }
        if (!hasMouseEnterLeave) {
          fakeName = mouseEnterLeave[name];
          if (fakeName) {
            nativeHandler = function (evt) {
              var current, related;
              current = evt.currentTarget;
              related = evt.relatedTarget;
              if (related && current.contains) {
                related = current.contains(related);
              } else {
                while (related && related !== current) {
                  related = related.parentNode;
                }
              }
              if (!related) {
                evt = fix(evt || win.event);
                evt.type = evt.type === 'mouseout' ? 'mouseleave' : 'mouseenter';
                evt.target = current;
                executeHandlers(evt, id);
              }
            };
          }
        }
        if (!hasFocusIn && (name === 'focusin' || name === 'focusout')) {
          capture = true;
          fakeName = name === 'focusin' ? 'focus' : 'blur';
          nativeHandler = function (evt) {
            evt = fix(evt || win.event);
            evt.type = evt.type === 'focus' ? 'focusin' : 'focusout';
            executeHandlers(evt, id);
          };
        }
        callbackList = events[id][name];
        if (!callbackList) {
          events[id][name] = callbackList = [{
              func: callback,
              scope: scope
            }];
          callbackList.fakeName = fakeName;
          callbackList.capture = capture;
          callbackList.nativeHandler = nativeHandler;
          if (name === 'ready') {
            bindOnReady(target, nativeHandler, self);
          } else {
            addEvent(target, fakeName || name, nativeHandler, capture);
          }
        } else {
          if (name === 'ready' && self.domLoaded) {
            callback({ type: name });
          } else {
            callbackList.push({
              func: callback,
              scope: scope
            });
          }
        }
      }
      target = callbackList = 0;
      return callback;
    };
    self.unbind = function (target, names, callback) {
      var id, callbackList, i, ci, name, eventMap;
      if (!target || target.nodeType === 3 || target.nodeType === 8) {
        return self;
      }
      id = target[expando];
      if (id) {
        eventMap = events[id];
        if (names) {
          names = names.split(' ');
          i = names.length;
          while (i--) {
            name = names[i];
            callbackList = eventMap[name];
            if (callbackList) {
              if (callback) {
                ci = callbackList.length;
                while (ci--) {
                  if (callbackList[ci].func === callback) {
                    var nativeHandler = callbackList.nativeHandler;
                    var fakeName = callbackList.fakeName, capture = callbackList.capture;
                    callbackList = callbackList.slice(0, ci).concat(callbackList.slice(ci + 1));
                    callbackList.nativeHandler = nativeHandler;
                    callbackList.fakeName = fakeName;
                    callbackList.capture = capture;
                    eventMap[name] = callbackList;
                  }
                }
              }
              if (!callback || callbackList.length === 0) {
                delete eventMap[name];
                removeEvent(target, callbackList.fakeName || name, callbackList.nativeHandler, callbackList.capture);
              }
            }
          }
        } else {
          for (name in eventMap) {
            callbackList = eventMap[name];
            removeEvent(target, callbackList.fakeName || name, callbackList.nativeHandler, callbackList.capture);
          }
          eventMap = {};
        }
        for (name in eventMap) {
          return self;
        }
        delete events[id];
        try {
          delete target[expando];
        } catch (ex) {
          target[expando] = null;
        }
      }
      return self;
    };
    self.fire = function (target, name, args) {
      var id;
      if (!target || target.nodeType === 3 || target.nodeType === 8) {
        return self;
      }
      args = fix(null, args);
      args.type = name;
      args.target = target;
      do {
        id = target[expando];
        if (id) {
          executeHandlers(args, id);
        }
        target = target.parentNode || target.ownerDocument || target.defaultView || target.parentWindow;
      } while (target && !args.isPropagationStopped());
      return self;
    };
    self.clean = function (target) {
      var i, children;
      var unbind = self.unbind;
      if (!target || target.nodeType === 3 || target.nodeType === 8) {
        return self;
      }
      if (target[expando]) {
        unbind(target);
      }
      if (!target.getElementsByTagName) {
        target = target.document;
      }
      if (target && target.getElementsByTagName) {
        unbind(target);
        children = target.getElementsByTagName('*');
        i = children.length;
        while (i--) {
          target = children[i];
          if (target[expando]) {
            unbind(target);
          }
        }
      }
      return self;
    };
    self.destroy = function () {
      events = {};
    };
    self.cancel = function (e) {
      if (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
      }
      return false;
    };
  };
  EventUtils.Event = new EventUtils();
  EventUtils.Event.bind(window, 'ready', function () {
  });

  var i;
  var support;
  var Expr;
  var getText;
  var isXML;
  var tokenize;
  var compile;
  var select;
  var outermostContext;
  var sortInput;
  var hasDuplicate;
  var setDocument;
  var document$1;
  var docElem;
  var documentIsHTML;
  var rbuggyQSA;
  var rbuggyMatches;
  var matches;
  var contains$1;
  var expando = 'sizzle' + -new Date();
  var preferredDoc = window.document;
  var dirruns = 0;
  var done = 0;
  var classCache = createCache();
  var tokenCache = createCache();
  var compilerCache = createCache();
  var sortOrder = function (a, b) {
      if (a === b) {
        hasDuplicate = true;
      }
      return 0;
    };
  var strundefined = typeof undefined;
  var MAX_NEGATIVE = 1 << 31;
  var hasOwn = {}.hasOwnProperty;
  var arr = [];
  var pop = arr.pop;
  var push_native = arr.push;
  var push$1 = arr.push;
  var slice$1 = arr.slice;
  var indexOf$1 = arr.indexOf || function (elem) {
      var i = 0, len = this.length;
      for (; i < len; i++) {
        if (this[i] === elem) {
          return i;
        }
      }
      return -1;
    };
  var booleans = 'checked|selected|async|autofocus|autoplay|controls|defer|disabled|hidden|ismap|loop|multiple|open|readonly|required|scoped';
  var whitespace = '[\\x20\\t\\r\\n\\f]';
  var identifier = '(?:\\\\.|[\\w-]|[^\\x00-\\xa0])+';
  var attributes = '\\[' + whitespace + '*(' + identifier + ')(?:' + whitespace + '*([*^$|!~]?=)' + whitespace + '*(?:\'((?:\\\\.|[^\\\\\'])*)\'|"((?:\\\\.|[^\\\\"])*)"|(' + identifier + '))|)' + whitespace + '*\\]';
  var pseudos = ':(' + identifier + ')(?:\\((' + '(\'((?:\\\\.|[^\\\\\'])*)\'|"((?:\\\\.|[^\\\\"])*)")|' + '((?:\\\\.|[^\\\\()[\\]]|' + attributes + ')*)|' + '.*' + ')\\)|)';
  var rtrim = new RegExp('^' + whitespace + '+|((?:^|[^\\\\])(?:\\\\.)*)' + whitespace + '+$', 'g');
  var rcomma = new RegExp('^' + whitespace + '*,' + whitespace + '*');
  var rcombinators = new RegExp('^' + whitespace + '*([>+~]|' + whitespace + ')' + whitespace + '*');
  var rattributeQuotes = new RegExp('=' + whitespace + '*([^\\]\'"]*?)' + whitespace + '*\\]', 'g');
  var rpseudo = new RegExp(pseudos);
  var ridentifier = new RegExp('^' + identifier + '$');
  var matchExpr = {
      ID: new RegExp('^#(' + identifier + ')'),
      CLASS: new RegExp('^\\.(' + identifier + ')'),
      TAG: new RegExp('^(' + identifier + '|[*])'),
      ATTR: new RegExp('^' + attributes),
      PSEUDO: new RegExp('^' + pseudos),
      CHILD: new RegExp('^:(only|first|last|nth|nth-last)-(child|of-type)(?:\\(' + whitespace + '*(even|odd|(([+-]|)(\\d*)n|)' + whitespace + '*(?:([+-]|)' + whitespace + '*(\\d+)|))' + whitespace + '*\\)|)', 'i'),
      bool: new RegExp('^(?:' + booleans + ')$', 'i'),
      needsContext: new RegExp('^' + whitespace + '*[>+~]|:(even|odd|eq|gt|lt|nth|first|last)(?:\\(' + whitespace + '*((?:-\\d)?\\d*)' + whitespace + '*\\)|)(?=[^-]|$)', 'i')
    };
  var rinputs = /^(?:input|select|textarea|button)$/i;
  var rheader = /^h\d$/i;
  var rnative = /^[^{]+\{\s*\[native \w/;
  var rquickExpr = /^(?:#([\w-]+)|(\w+)|\.([\w-]+))$/;
  var rsibling = /[+~]/;
  var rescape = /'|\\/g;
  var runescape = new RegExp('\\\\([\\da-f]{1,6}' + whitespace + '?|(' + whitespace + ')|.)', 'ig');
  var funescape = function (_, escaped, escapedWhitespace) {
      var high = '0x' + escaped - 65536;
      return high !== high || escapedWhitespace ? escaped : high < 0 ? String.fromCharCode(high + 65536) : String.fromCharCode(high >> 10 | 55296, high & 1023 | 56320);
    };
  try {
    push$1.apply(arr = slice$1.call(preferredDoc.childNodes), preferredDoc.childNodes);
    arr[preferredDoc.childNodes.length].nodeType;
  } catch (e) {
    push$1 = {
      apply: arr.length ? function (target, els) {
        push_native.apply(target, slice$1.call(els));
      } : function (target, els) {
        var j = target.length, i = 0;
        while (target[j++] = els[i++]) {
        }
        target.length = j - 1;
      }
    };
  }
  var Sizzle = function (selector, context, results, seed) {
    var match, elem, m, nodeType, i, groups, old, nid, newContext, newSelector;
    if ((context ? context.ownerDocument || context : preferredDoc) !== document$1) {
      setDocument(context);
    }
    context = context || document$1;
    results = results || [];
    if (!selector || typeof selector !== 'string') {
      return results;
    }
    if ((nodeType = context.nodeType) !== 1 && nodeType !== 9) {
      return [];
    }
    if (documentIsHTML && !seed) {
      if (match = rquickExpr.exec(selector)) {
        if (m = match[1]) {
          if (nodeType === 9) {
            elem = context.getElementById(m);
            if (elem && elem.parentNode) {
              if (elem.id === m) {
                results.push(elem);
                return results;
              }
            } else {
              return results;
            }
          } else {
            if (context.ownerDocument && (elem = context.ownerDocument.getElementById(m)) && contains$1(context, elem) && elem.id === m) {
              results.push(elem);
              return results;
            }
          }
        } else if (match[2]) {
          push$1.apply(results, context.getElementsByTagName(selector));
          return results;
        } else if ((m = match[3]) && support.getElementsByClassName) {
          push$1.apply(results, context.getElementsByClassName(m));
          return results;
        }
      }
      if (support.qsa && (!rbuggyQSA || !rbuggyQSA.test(selector))) {
        nid = old = expando;
        newContext = context;
        newSelector = nodeType === 9 && selector;
        if (nodeType === 1 && context.nodeName.toLowerCase() !== 'object') {
          groups = tokenize(selector);
          if (old = context.getAttribute('id')) {
            nid = old.replace(rescape, '\\$&');
          } else {
            context.setAttribute('id', nid);
          }
          nid = '[id=\'' + nid + '\'] ';
          i = groups.length;
          while (i--) {
            groups[i] = nid + toSelector(groups[i]);
          }
          newContext = rsibling.test(selector) && testContext(context.parentNode) || context;
          newSelector = groups.join(',');
        }
        if (newSelector) {
          try {
            push$1.apply(results, newContext.querySelectorAll(newSelector));
            return results;
          } catch (qsaError) {
          } finally {
            if (!old) {
              context.removeAttribute('id');
            }
          }
        }
      }
    }
    return select(selector.replace(rtrim, '$1'), context, results, seed);
  };
  function createCache() {
    var keys = [];
    function cache(key, value) {
      if (keys.push(key + ' ') > Expr.cacheLength) {
        delete cache[keys.shift()];
      }
      return cache[key + ' '] = value;
    }
    return cache;
  }
  function markFunction(fn) {
    fn[expando] = true;
    return fn;
  }
  function siblingCheck(a, b) {
    var cur = b && a, diff = cur && a.nodeType === 1 && b.nodeType === 1 && (~b.sourceIndex || MAX_NEGATIVE) - (~a.sourceIndex || MAX_NEGATIVE);
    if (diff) {
      return diff;
    }
    if (cur) {
      while (cur = cur.nextSibling) {
        if (cur === b) {
          return -1;
        }
      }
    }
    return a ? 1 : -1;
  }
  function createInputPseudo(type) {
    return function (elem) {
      var name = elem.nodeName.toLowerCase();
      return name === 'input' && elem.type === type;
    };
  }
  function createButtonPseudo(type) {
    return function (elem) {
      var name = elem.nodeName.toLowerCase();
      return (name === 'input' || name === 'button') && elem.type === type;
    };
  }
  function createPositionalPseudo(fn) {
    return markFunction(function (argument) {
      argument = +argument;
      return markFunction(function (seed, matches) {
        var j, matchIndexes = fn([], seed.length, argument), i = matchIndexes.length;
        while (i--) {
          if (seed[j = matchIndexes[i]]) {
            seed[j] = !(matches[j] = seed[j]);
          }
        }
      });
    });
  }
  function testContext(context) {
    return context && typeof context.getElementsByTagName !== strundefined && context;
  }
  support = Sizzle.support = {};
  isXML = Sizzle.isXML = function (elem) {
    var documentElement = elem && (elem.ownerDocument || elem).documentElement;
    return documentElement ? documentElement.nodeName !== 'HTML' : false;
  };
  setDocument = Sizzle.setDocument = function (node) {
    var hasCompare, doc = node ? node.ownerDocument || node : preferredDoc, parent = doc.defaultView;
    function getTop(win) {
      try {
        return win.top;
      } catch (ex) {
      }
      return null;
    }
    if (doc === document$1 || doc.nodeType !== 9 || !doc.documentElement) {
      return document$1;
    }
    document$1 = doc;
    docElem = doc.documentElement;
    documentIsHTML = !isXML(doc);
    if (parent && parent !== getTop(parent)) {
      if (parent.addEventListener) {
        parent.addEventListener('unload', function () {
          setDocument();
        }, false);
      } else if (parent.attachEvent) {
        parent.attachEvent('onunload', function () {
          setDocument();
        });
      }
    }
    support.attributes = true;
    support.getElementsByTagName = true;
    support.getElementsByClassName = rnative.test(doc.getElementsByClassName);
    support.getById = true;
    Expr.find.ID = function (id, context) {
      if (typeof context.getElementById !== strundefined && documentIsHTML) {
        var m = context.getElementById(id);
        return m && m.parentNode ? [m] : [];
      }
    };
    Expr.filter.ID = function (id) {
      var attrId = id.replace(runescape, funescape);
      return function (elem) {
        return elem.getAttribute('id') === attrId;
      };
    };
    Expr.find.TAG = support.getElementsByTagName ? function (tag, context) {
      if (typeof context.getElementsByTagName !== strundefined) {
        return context.getElementsByTagName(tag);
      }
    } : function (tag, context) {
      var elem, tmp = [], i = 0, results = context.getElementsByTagName(tag);
      if (tag === '*') {
        while (elem = results[i++]) {
          if (elem.nodeType === 1) {
            tmp.push(elem);
          }
        }
        return tmp;
      }
      return results;
    };
    Expr.find.CLASS = support.getElementsByClassName && function (className, context) {
      if (documentIsHTML) {
        return context.getElementsByClassName(className);
      }
    };
    rbuggyMatches = [];
    rbuggyQSA = [];
    support.disconnectedMatch = true;
    rbuggyQSA = rbuggyQSA.length && new RegExp(rbuggyQSA.join('|'));
    rbuggyMatches = rbuggyMatches.length && new RegExp(rbuggyMatches.join('|'));
    hasCompare = rnative.test(docElem.compareDocumentPosition);
    contains$1 = hasCompare || rnative.test(docElem.contains) ? function (a, b) {
      var adown = a.nodeType === 9 ? a.documentElement : a, bup = b && b.parentNode;
      return a === bup || !!(bup && bup.nodeType === 1 && (adown.contains ? adown.contains(bup) : a.compareDocumentPosition && a.compareDocumentPosition(bup) & 16));
    } : function (a, b) {
      if (b) {
        while (b = b.parentNode) {
          if (b === a) {
            return true;
          }
        }
      }
      return false;
    };
    sortOrder = hasCompare ? function (a, b) {
      if (a === b) {
        hasDuplicate = true;
        return 0;
      }
      var compare = !a.compareDocumentPosition - !b.compareDocumentPosition;
      if (compare) {
        return compare;
      }
      compare = (a.ownerDocument || a) === (b.ownerDocument || b) ? a.compareDocumentPosition(b) : 1;
      if (compare & 1 || !support.sortDetached && b.compareDocumentPosition(a) === compare) {
        if (a === doc || a.ownerDocument === preferredDoc && contains$1(preferredDoc, a)) {
          return -1;
        }
        if (b === doc || b.ownerDocument === preferredDoc && contains$1(preferredDoc, b)) {
          return 1;
        }
        return sortInput ? indexOf$1.call(sortInput, a) - indexOf$1.call(sortInput, b) : 0;
      }
      return compare & 4 ? -1 : 1;
    } : function (a, b) {
      if (a === b) {
        hasDuplicate = true;
        return 0;
      }
      var cur, i = 0, aup = a.parentNode, bup = b.parentNode, ap = [a], bp = [b];
      if (!aup || !bup) {
        return a === doc ? -1 : b === doc ? 1 : aup ? -1 : bup ? 1 : sortInput ? indexOf$1.call(sortInput, a) - indexOf$1.call(sortInput, b) : 0;
      } else if (aup === bup) {
        return siblingCheck(a, b);
      }
      cur = a;
      while (cur = cur.parentNode) {
        ap.unshift(cur);
      }
      cur = b;
      while (cur = cur.parentNode) {
        bp.unshift(cur);
      }
      while (ap[i] === bp[i]) {
        i++;
      }
      return i ? siblingCheck(ap[i], bp[i]) : ap[i] === preferredDoc ? -1 : bp[i] === preferredDoc ? 1 : 0;
    };
    return doc;
  };
  Sizzle.matches = function (expr, elements) {
    return Sizzle(expr, null, null, elements);
  };
  Sizzle.matchesSelector = function (elem, expr) {
    if ((elem.ownerDocument || elem) !== document$1) {
      setDocument(elem);
    }
    expr = expr.replace(rattributeQuotes, '=\'$1\']');
    if (support.matchesSelector && documentIsHTML && (!rbuggyMatches || !rbuggyMatches.test(expr)) && (!rbuggyQSA || !rbuggyQSA.test(expr))) {
      try {
        var ret = matches.call(elem, expr);
        if (ret || support.disconnectedMatch || elem.document && elem.document.nodeType !== 11) {
          return ret;
        }
      } catch (e) {
      }
    }
    return Sizzle(expr, document$1, null, [elem]).length > 0;
  };
  Sizzle.contains = function (context, elem) {
    if ((context.ownerDocument || context) !== document$1) {
      setDocument(context);
    }
    return contains$1(context, elem);
  };
  Sizzle.attr = function (elem, name) {
    if ((elem.ownerDocument || elem) !== document$1) {
      setDocument(elem);
    }
    var fn = Expr.attrHandle[name.toLowerCase()], val = fn && hasOwn.call(Expr.attrHandle, name.toLowerCase()) ? fn(elem, name, !documentIsHTML) : undefined;
    return val !== undefined ? val : support.attributes || !documentIsHTML ? elem.getAttribute(name) : (val = elem.getAttributeNode(name)) && val.specified ? val.value : null;
  };
  Sizzle.error = function (msg) {
    throw new Error('Syntax error, unrecognized expression: ' + msg);
  };
  Sizzle.uniqueSort = function (results) {
    var elem, duplicates = [], j = 0, i = 0;
    hasDuplicate = !support.detectDuplicates;
    sortInput = !support.sortStable && results.slice(0);
    results.sort(sortOrder);
    if (hasDuplicate) {
      while (elem = results[i++]) {
        if (elem === results[i]) {
          j = duplicates.push(i);
        }
      }
      while (j--) {
        results.splice(duplicates[j], 1);
      }
    }
    sortInput = null;
    return results;
  };
  getText = Sizzle.getText = function (elem) {
    var node, ret = '', i = 0, nodeType = elem.nodeType;
    if (!nodeType) {
      while (node = elem[i++]) {
        ret += getText(node);
      }
    } else if (nodeType === 1 || nodeType === 9 || nodeType === 11) {
      if (typeof elem.textContent === 'string') {
        return elem.textContent;
      } else {
        for (elem = elem.firstChild; elem; elem = elem.nextSibling) {
          ret += getText(elem);
        }
      }
    } else if (nodeType === 3 || nodeType === 4) {
      return elem.nodeValue;
    }
    return ret;
  };
  Expr = Sizzle.selectors = {
    cacheLength: 50,
    createPseudo: markFunction,
    match: matchExpr,
    attrHandle: {},
    find: {},
    relative: {
      '>': {
        dir: 'parentNode',
        first: true
      },
      ' ': { dir: 'parentNode' },
      '+': {
        dir: 'previousSibling',
        first: true
      },
      '~': { dir: 'previousSibling' }
    },
    preFilter: {
      ATTR: function (match) {
        match[1] = match[1].replace(runescape, funescape);
        match[3] = (match[3] || match[4] || match[5] || '').replace(runescape, funescape);
        if (match[2] === '~=') {
          match[3] = ' ' + match[3] + ' ';
        }
        return match.slice(0, 4);
      },
      CHILD: function (match) {
        match[1] = match[1].toLowerCase();
        if (match[1].slice(0, 3) === 'nth') {
          if (!match[3]) {
            Sizzle.error(match[0]);
          }
          match[4] = +(match[4] ? match[5] + (match[6] || 1) : 2 * (match[3] === 'even' || match[3] === 'odd'));
          match[5] = +(match[7] + match[8] || match[3] === 'odd');
        } else if (match[3]) {
          Sizzle.error(match[0]);
        }
        return match;
      },
      PSEUDO: function (match) {
        var excess, unquoted = !match[6] && match[2];
        if (matchExpr.CHILD.test(match[0])) {
          return null;
        }
        if (match[3]) {
          match[2] = match[4] || match[5] || '';
        } else if (unquoted && rpseudo.test(unquoted) && (excess = tokenize(unquoted, true)) && (excess = unquoted.indexOf(')', unquoted.length - excess) - unquoted.length)) {
          match[0] = match[0].slice(0, excess);
          match[2] = unquoted.slice(0, excess);
        }
        return match.slice(0, 3);
      }
    },
    filter: {
      TAG: function (nodeNameSelector) {
        var nodeName = nodeNameSelector.replace(runescape, funescape).toLowerCase();
        return nodeNameSelector === '*' ? function () {
          return true;
        } : function (elem) {
          return elem.nodeName && elem.nodeName.toLowerCase() === nodeName;
        };
      },
      CLASS: function (className) {
        var pattern = classCache[className + ' '];
        return pattern || (pattern = new RegExp('(^|' + whitespace + ')' + className + '(' + whitespace + '|$)')) && classCache(className, function (elem) {
          return pattern.test(typeof elem.className === 'string' && elem.className || typeof elem.getAttribute !== strundefined && elem.getAttribute('class') || '');
        });
      },
      ATTR: function (name, operator, check) {
        return function (elem) {
          var result = Sizzle.attr(elem, name);
          if (result == null) {
            return operator === '!=';
          }
          if (!operator) {
            return true;
          }
          result += '';
          return operator === '=' ? result === check : operator === '!=' ? result !== check : operator === '^=' ? check && result.indexOf(check) === 0 : operator === '*=' ? check && result.indexOf(check) > -1 : operator === '$=' ? check && result.slice(-check.length) === check : operator === '~=' ? (' ' + result + ' ').indexOf(check) > -1 : operator === '|=' ? result === check || result.slice(0, check.length + 1) === check + '-' : false;
        };
      },
      CHILD: function (type, what, argument, first, last) {
        var simple = type.slice(0, 3) !== 'nth', forward = type.slice(-4) !== 'last', ofType = what === 'of-type';
        return first === 1 && last === 0 ? function (elem) {
          return !!elem.parentNode;
        } : function (elem, context, xml) {
          var cache, outerCache, node, diff, nodeIndex, start, dir = simple !== forward ? 'nextSibling' : 'previousSibling', parent = elem.parentNode, name = ofType && elem.nodeName.toLowerCase(), useCache = !xml && !ofType;
          if (parent) {
            if (simple) {
              while (dir) {
                node = elem;
                while (node = node[dir]) {
                  if (ofType ? node.nodeName.toLowerCase() === name : node.nodeType === 1) {
                    return false;
                  }
                }
                start = dir = type === 'only' && !start && 'nextSibling';
              }
              return true;
            }
            start = [forward ? parent.firstChild : parent.lastChild];
            if (forward && useCache) {
              outerCache = parent[expando] || (parent[expando] = {});
              cache = outerCache[type] || [];
              nodeIndex = cache[0] === dirruns && cache[1];
              diff = cache[0] === dirruns && cache[2];
              node = nodeIndex && parent.childNodes[nodeIndex];
              while (node = ++nodeIndex && node && node[dir] || (diff = nodeIndex = 0) || start.pop()) {
                if (node.nodeType === 1 && ++diff && node === elem) {
                  outerCache[type] = [
                    dirruns,
                    nodeIndex,
                    diff
                  ];
                  break;
                }
              }
            } else if (useCache && (cache = (elem[expando] || (elem[expando] = {}))[type]) && cache[0] === dirruns) {
              diff = cache[1];
            } else {
              while (node = ++nodeIndex && node && node[dir] || (diff = nodeIndex = 0) || start.pop()) {
                if ((ofType ? node.nodeName.toLowerCase() === name : node.nodeType === 1) && ++diff) {
                  if (useCache) {
                    (node[expando] || (node[expando] = {}))[type] = [
                      dirruns,
                      diff
                    ];
                  }
                  if (node === elem) {
                    break;
                  }
                }
              }
            }
            diff -= last;
            return diff === first || diff % first === 0 && diff / first >= 0;
          }
        };
      },
      PSEUDO: function (pseudo, argument) {
        var args, fn = Expr.pseudos[pseudo] || Expr.setFilters[pseudo.toLowerCase()] || Sizzle.error('unsupported pseudo: ' + pseudo);
        if (fn[expando]) {
          return fn(argument);
        }
        if (fn.length > 1) {
          args = [
            pseudo,
            pseudo,
            '',
            argument
          ];
          return Expr.setFilters.hasOwnProperty(pseudo.toLowerCase()) ? markFunction(function (seed, matches) {
            var idx, matched = fn(seed, argument), i = matched.length;
            while (i--) {
              idx = indexOf$1.call(seed, matched[i]);
              seed[idx] = !(matches[idx] = matched[i]);
            }
          }) : function (elem) {
            return fn(elem, 0, args);
          };
        }
        return fn;
      }
    },
    pseudos: {
      not: markFunction(function (selector) {
        var input = [], results = [], matcher = compile(selector.replace(rtrim, '$1'));
        return matcher[expando] ? markFunction(function (seed, matches, context, xml) {
          var elem, unmatched = matcher(seed, null, xml, []), i = seed.length;
          while (i--) {
            if (elem = unmatched[i]) {
              seed[i] = !(matches[i] = elem);
            }
          }
        }) : function (elem, context, xml) {
          input[0] = elem;
          matcher(input, null, xml, results);
          return !results.pop();
        };
      }),
      has: markFunction(function (selector) {
        return function (elem) {
          return Sizzle(selector, elem).length > 0;
        };
      }),
      contains: markFunction(function (text) {
        text = text.replace(runescape, funescape);
        return function (elem) {
          return (elem.textContent || elem.innerText || getText(elem)).indexOf(text) > -1;
        };
      }),
      lang: markFunction(function (lang) {
        if (!ridentifier.test(lang || '')) {
          Sizzle.error('unsupported lang: ' + lang);
        }
        lang = lang.replace(runescape, funescape).toLowerCase();
        return function (elem) {
          var elemLang;
          do {
            if (elemLang = documentIsHTML ? elem.lang : elem.getAttribute('xml:lang') || elem.getAttribute('lang')) {
              elemLang = elemLang.toLowerCase();
              return elemLang === lang || elemLang.indexOf(lang + '-') === 0;
            }
          } while ((elem = elem.parentNode) && elem.nodeType === 1);
          return false;
        };
      }),
      target: function (elem) {
        var hash = window.location && window.location.hash;
        return hash && hash.slice(1) === elem.id;
      },
      root: function (elem) {
        return elem === docElem;
      },
      focus: function (elem) {
        return elem === document$1.activeElement && (!document$1.hasFocus || document$1.hasFocus()) && !!(elem.type || elem.href || ~elem.tabIndex);
      },
      enabled: function (elem) {
        return elem.disabled === false;
      },
      disabled: function (elem) {
        return elem.disabled === true;
      },
      checked: function (elem) {
        var nodeName = elem.nodeName.toLowerCase();
        return nodeName === 'input' && !!elem.checked || nodeName === 'option' && !!elem.selected;
      },
      selected: function (elem) {
        if (elem.parentNode) {
          elem.parentNode.selectedIndex;
        }
        return elem.selected === true;
      },
      empty: function (elem) {
        for (elem = elem.firstChild; elem; elem = elem.nextSibling) {
          if (elem.nodeType < 6) {
            return false;
          }
        }
        return true;
      },
      parent: function (elem) {
        return !Expr.pseudos.empty(elem);
      },
      header: function (elem) {
        return rheader.test(elem.nodeName);
      },
      input: function (elem) {
        return rinputs.test(elem.nodeName);
      },
      button: function (elem) {
        var name = elem.nodeName.toLowerCase();
        return name === 'input' && elem.type === 'button' || name === 'button';
      },
      text: function (elem) {
        var attr;
        return elem.nodeName.toLowerCase() === 'input' && elem.type === 'text' && ((attr = elem.getAttribute('type')) == null || attr.toLowerCase() === 'text');
      },
      first: createPositionalPseudo(function () {
        return [0];
      }),
      last: createPositionalPseudo(function (matchIndexes, length) {
        return [length - 1];
      }),
      eq: createPositionalPseudo(function (matchIndexes, length, argument) {
        return [argument < 0 ? argument + length : argument];
      }),
      even: createPositionalPseudo(function (matchIndexes, length) {
        var i = 0;
        for (; i < length; i += 2) {
          matchIndexes.push(i);
        }
        return matchIndexes;
      }),
      odd: createPositionalPseudo(function (matchIndexes, length) {
        var i = 1;
        for (; i < length; i += 2) {
          matchIndexes.push(i);
        }
        return matchIndexes;
      }),
      lt: createPositionalPseudo(function (matchIndexes, length, argument) {
        var i = argument < 0 ? argument + length : argument;
        for (; --i >= 0;) {
          matchIndexes.push(i);
        }
        return matchIndexes;
      }),
      gt: createPositionalPseudo(function (matchIndexes, length, argument) {
        var i = argument < 0 ? argument + length : argument;
        for (; ++i < length;) {
          matchIndexes.push(i);
        }
        return matchIndexes;
      })
    }
  };
  Expr.pseudos.nth = Expr.pseudos.eq;
  for (i in {
      radio: true,
      checkbox: true,
      file: true,
      password: true,
      image: true
    }) {
    Expr.pseudos[i] = createInputPseudo(i);
  }
  for (i in {
      submit: true,
      reset: true
    }) {
    Expr.pseudos[i] = createButtonPseudo(i);
  }
  function setFilters() {
  }
  setFilters.prototype = Expr.filters = Expr.pseudos;
  Expr.setFilters = new setFilters();
  tokenize = Sizzle.tokenize = function (selector, parseOnly) {
    var matched, match, tokens, type, soFar, groups, preFilters, cached = tokenCache[selector + ' '];
    if (cached) {
      return parseOnly ? 0 : cached.slice(0);
    }
    soFar = selector;
    groups = [];
    preFilters = Expr.preFilter;
    while (soFar) {
      if (!matched || (match = rcomma.exec(soFar))) {
        if (match) {
          soFar = soFar.slice(match[0].length) || soFar;
        }
        groups.push(tokens = []);
      }
      matched = false;
      if (match = rcombinators.exec(soFar)) {
        matched = match.shift();
        tokens.push({
          value: matched,
          type: match[0].replace(rtrim, ' ')
        });
        soFar = soFar.slice(matched.length);
      }
      for (type in Expr.filter) {
        if ((match = matchExpr[type].exec(soFar)) && (!preFilters[type] || (match = preFilters[type](match)))) {
          matched = match.shift();
          tokens.push({
            value: matched,
            type: type,
            matches: match
          });
          soFar = soFar.slice(matched.length);
        }
      }
      if (!matched) {
        break;
      }
    }
    return parseOnly ? soFar.length : soFar ? Sizzle.error(selector) : tokenCache(selector, groups).slice(0);
  };
  function toSelector(tokens) {
    var i = 0, len = tokens.length, selector = '';
    for (; i < len; i++) {
      selector += tokens[i].value;
    }
    return selector;
  }
  function addCombinator(matcher, combinator, base) {
    var dir = combinator.dir, checkNonElements = base && dir === 'parentNode', doneName = done++;
    return combinator.first ? function (elem, context, xml) {
      while (elem = elem[dir]) {
        if (elem.nodeType === 1 || checkNonElements) {
          return matcher(elem, context, xml);
        }
      }
    } : function (elem, context, xml) {
      var oldCache, outerCache, newCache = [
          dirruns,
          doneName
        ];
      if (xml) {
        while (elem = elem[dir]) {
          if (elem.nodeType === 1 || checkNonElements) {
            if (matcher(elem, context, xml)) {
              return true;
            }
          }
        }
      } else {
        while (elem = elem[dir]) {
          if (elem.nodeType === 1 || checkNonElements) {
            outerCache = elem[expando] || (elem[expando] = {});
            if ((oldCache = outerCache[dir]) && oldCache[0] === dirruns && oldCache[1] === doneName) {
              return newCache[2] = oldCache[2];
            } else {
              outerCache[dir] = newCache;
              if (newCache[2] = matcher(elem, context, xml)) {
                return true;
              }
            }
          }
        }
      }
    };
  }
  function elementMatcher(matchers) {
    return matchers.length > 1 ? function (elem, context, xml) {
      var i = matchers.length;
      while (i--) {
        if (!matchers[i](elem, context, xml)) {
          return false;
        }
      }
      return true;
    } : matchers[0];
  }
  function multipleContexts(selector, contexts, results) {
    var i = 0, len = contexts.length;
    for (; i < len; i++) {
      Sizzle(selector, contexts[i], results);
    }
    return results;
  }
  function condense(unmatched, map, filter, context, xml) {
    var elem, newUnmatched = [], i = 0, len = unmatched.length, mapped = map != null;
    for (; i < len; i++) {
      if (elem = unmatched[i]) {
        if (!filter || filter(elem, context, xml)) {
          newUnmatched.push(elem);
          if (mapped) {
            map.push(i);
          }
        }
      }
    }
    return newUnmatched;
  }
  function setMatcher(preFilter, selector, matcher, postFilter, postFinder, postSelector) {
    if (postFilter && !postFilter[expando]) {
      postFilter = setMatcher(postFilter);
    }
    if (postFinder && !postFinder[expando]) {
      postFinder = setMatcher(postFinder, postSelector);
    }
    return markFunction(function (seed, results, context, xml) {
      var temp, i, elem, preMap = [], postMap = [], preexisting = results.length, elems = seed || multipleContexts(selector || '*', context.nodeType ? [context] : context, []), matcherIn = preFilter && (seed || !selector) ? condense(elems, preMap, preFilter, context, xml) : elems, matcherOut = matcher ? postFinder || (seed ? preFilter : preexisting || postFilter) ? [] : results : matcherIn;
      if (matcher) {
        matcher(matcherIn, matcherOut, context, xml);
      }
      if (postFilter) {
        temp = condense(matcherOut, postMap);
        postFilter(temp, [], context, xml);
        i = temp.length;
        while (i--) {
          if (elem = temp[i]) {
            matcherOut[postMap[i]] = !(matcherIn[postMap[i]] = elem);
          }
        }
      }
      if (seed) {
        if (postFinder || preFilter) {
          if (postFinder) {
            temp = [];
            i = matcherOut.length;
            while (i--) {
              if (elem = matcherOut[i]) {
                temp.push(matcherIn[i] = elem);
              }
            }
            postFinder(null, matcherOut = [], temp, xml);
          }
          i = matcherOut.length;
          while (i--) {
            if ((elem = matcherOut[i]) && (temp = postFinder ? indexOf$1.call(seed, elem) : preMap[i]) > -1) {
              seed[temp] = !(results[temp] = elem);
            }
          }
        }
      } else {
        matcherOut = condense(matcherOut === results ? matcherOut.splice(preexisting, matcherOut.length) : matcherOut);
        if (postFinder) {
          postFinder(null, results, matcherOut, xml);
        } else {
          push$1.apply(results, matcherOut);
        }
      }
    });
  }
  function matcherFromTokens(tokens) {
    var checkContext, matcher, j, len = tokens.length, leadingRelative = Expr.relative[tokens[0].type], implicitRelative = leadingRelative || Expr.relative[' '], i = leadingRelative ? 1 : 0, matchContext = addCombinator(function (elem) {
        return elem === checkContext;
      }, implicitRelative, true), matchAnyContext = addCombinator(function (elem) {
        return indexOf$1.call(checkContext, elem) > -1;
      }, implicitRelative, true), matchers = [function (elem, context, xml) {
          return !leadingRelative && (xml || context !== outermostContext) || ((checkContext = context).nodeType ? matchContext(elem, context, xml) : matchAnyContext(elem, context, xml));
        }];
    for (; i < len; i++) {
      if (matcher = Expr.relative[tokens[i].type]) {
        matchers = [addCombinator(elementMatcher(matchers), matcher)];
      } else {
        matcher = Expr.filter[tokens[i].type].apply(null, tokens[i].matches);
        if (matcher[expando]) {
          j = ++i;
          for (; j < len; j++) {
            if (Expr.relative[tokens[j].type]) {
              break;
            }
          }
          return setMatcher(i > 1 && elementMatcher(matchers), i > 1 && toSelector(tokens.slice(0, i - 1).concat({ value: tokens[i - 2].type === ' ' ? '*' : '' })).replace(rtrim, '$1'), matcher, i < j && matcherFromTokens(tokens.slice(i, j)), j < len && matcherFromTokens(tokens = tokens.slice(j)), j < len && toSelector(tokens));
        }
        matchers.push(matcher);
      }
    }
    return elementMatcher(matchers);
  }
  function matcherFromGroupMatchers(elementMatchers, setMatchers) {
    var bySet = setMatchers.length > 0, byElement = elementMatchers.length > 0, superMatcher = function (seed, context, xml, results, outermost) {
        var elem, j, matcher, matchedCount = 0, i = '0', unmatched = seed && [], setMatched = [], contextBackup = outermostContext, elems = seed || byElement && Expr.find.TAG('*', outermost), dirrunsUnique = dirruns += contextBackup == null ? 1 : Math.random() || 0.1, len = elems.length;
        if (outermost) {
          outermostContext = context !== document$1 && context;
        }
        for (; i !== len && (elem = elems[i]) != null; i++) {
          if (byElement && elem) {
            j = 0;
            while (matcher = elementMatchers[j++]) {
              if (matcher(elem, context, xml)) {
                results.push(elem);
                break;
              }
            }
            if (outermost) {
              dirruns = dirrunsUnique;
            }
          }
          if (bySet) {
            if (elem = !matcher && elem) {
              matchedCount--;
            }
            if (seed) {
              unmatched.push(elem);
            }
          }
        }
        matchedCount += i;
        if (bySet && i !== matchedCount) {
          j = 0;
          while (matcher = setMatchers[j++]) {
            matcher(unmatched, setMatched, context, xml);
          }
          if (seed) {
            if (matchedCount > 0) {
              while (i--) {
                if (!(unmatched[i] || setMatched[i])) {
                  setMatched[i] = pop.call(results);
                }
              }
            }
            setMatched = condense(setMatched);
          }
          push$1.apply(results, setMatched);
          if (outermost && !seed && setMatched.length > 0 && matchedCount + setMatchers.length > 1) {
            Sizzle.uniqueSort(results);
          }
        }
        if (outermost) {
          dirruns = dirrunsUnique;
          outermostContext = contextBackup;
        }
        return unmatched;
      };
    return bySet ? markFunction(superMatcher) : superMatcher;
  }
  compile = Sizzle.compile = function (selector, match) {
    var i, setMatchers = [], elementMatchers = [], cached = compilerCache[selector + ' '];
    if (!cached) {
      if (!match) {
        match = tokenize(selector);
      }
      i = match.length;
      while (i--) {
        cached = matcherFromTokens(match[i]);
        if (cached[expando]) {
          setMatchers.push(cached);
        } else {
          elementMatchers.push(cached);
        }
      }
      cached = compilerCache(selector, matcherFromGroupMatchers(elementMatchers, setMatchers));
      cached.selector = selector;
    }
    return cached;
  };
  select = Sizzle.select = function (selector, context, results, seed) {
    var i, tokens, token, type, find, compiled = typeof selector === 'function' && selector, match = !seed && tokenize(selector = compiled.selector || selector);
    results = results || [];
    if (match.length === 1) {
      tokens = match[0] = match[0].slice(0);
      if (tokens.length > 2 && (token = tokens[0]).type === 'ID' && support.getById && context.nodeType === 9 && documentIsHTML && Expr.relative[tokens[1].type]) {
        context = (Expr.find.ID(token.matches[0].replace(runescape, funescape), context) || [])[0];
        if (!context) {
          return results;
        } else if (compiled) {
          context = context.parentNode;
        }
        selector = selector.slice(tokens.shift().value.length);
      }
      i = matchExpr.needsContext.test(selector) ? 0 : tokens.length;
      while (i--) {
        token = tokens[i];
        if (Expr.relative[type = token.type]) {
          break;
        }
        if (find = Expr.find[type]) {
          if (seed = find(token.matches[0].replace(runescape, funescape), rsibling.test(tokens[0].type) && testContext(context.parentNode) || context)) {
            tokens.splice(i, 1);
            selector = seed.length && toSelector(tokens);
            if (!selector) {
              push$1.apply(results, seed);
              return results;
            }
            break;
          }
        }
      }
    }
    (compiled || compile(selector, match))(seed, context, !documentIsHTML, results, rsibling.test(selector) && testContext(context.parentNode) || context);
    return results;
  };
  support.sortStable = expando.split('').sort(sortOrder).join('') === expando;
  support.detectDuplicates = !!hasDuplicate;
  setDocument();
  support.sortDetached = true;

  var isArray = Array.isArray || function (obj) {
    return Object.prototype.toString.call(obj) === '[object Array]';
  };
  var toArray = function (obj) {
    var array = obj, i, l;
    if (!isArray(obj)) {
      array = [];
      for (i = 0, l = obj.length; i < l; i++) {
        array[i] = obj[i];
      }
    }
    return array;
  };
  var each$1 = function (o, cb, s) {
    var n, l;
    if (!o) {
      return 0;
    }
    s = s || o;
    if (o.length !== undefined) {
      for (n = 0, l = o.length; n < l; n++) {
        if (cb.call(s, o[n], n, o) === false) {
          return 0;
        }
      }
    } else {
      for (n in o) {
        if (o.hasOwnProperty(n)) {
          if (cb.call(s, o[n], n, o) === false) {
            return 0;
          }
        }
      }
    }
    return 1;
  };
  var map$1 = function (array, callback) {
    var out = [];
    each$1(array, function (item, index) {
      out.push(callback(item, index, array));
    });
    return out;
  };
  var filter$1 = function (a, f) {
    var o = [];
    each$1(a, function (v, index) {
      if (!f || f(v, index, a)) {
        o.push(v);
      }
    });
    return o;
  };
  var indexOf$2 = function (a, v) {
    var i, l;
    if (a) {
      for (i = 0, l = a.length; i < l; i++) {
        if (a[i] === v) {
          return i;
        }
      }
    }
    return -1;
  };
  var reduce = function (collection, iteratee, accumulator, thisArg) {
    var i = 0;
    if (arguments.length < 3) {
      accumulator = collection[0];
    }
    for (; i < collection.length; i++) {
      accumulator = iteratee.call(thisArg, accumulator, collection[i], i);
    }
    return accumulator;
  };
  var findIndex$1 = function (array, predicate, thisArg) {
    var i, l;
    for (i = 0, l = array.length; i < l; i++) {
      if (predicate.call(thisArg, array[i], i, array)) {
        return i;
      }
    }
    return -1;
  };
  var find$1 = function (array, predicate, thisArg) {
    var idx = findIndex$1(array, predicate, thisArg);
    if (idx !== -1) {
      return array[idx];
    }
    return undefined;
  };
  var last$1 = function (collection) {
    return collection[collection.length - 1];
  };
  var $_c2vc3pkje5o2puw = {
    isArray: isArray,
    toArray: toArray,
    each: each$1,
    map: map$1,
    filter: filter$1,
    indexOf: indexOf$2,
    reduce: reduce,
    findIndex: findIndex$1,
    find: find$1,
    last: last$1
  };

  var whiteSpaceRegExp = /^\s*|\s*$/g;
  var trim = function (str) {
    return str === null || str === undefined ? '' : ('' + str).replace(whiteSpaceRegExp, '');
  };
  var is = function (obj, type) {
    if (!type) {
      return obj !== undefined;
    }
    if (type === 'array' && $_c2vc3pkje5o2puw.isArray(obj)) {
      return true;
    }
    return typeof obj === type;
  };
  var makeMap = function (items, delim, map) {
    var i;
    items = items || [];
    delim = delim || ',';
    if (typeof items === 'string') {
      items = items.split(delim);
    }
    map = map || {};
    i = items.length;
    while (i--) {
      map[items[i]] = {};
    }
    return map;
  };
  var hasOwnProperty = function (obj, prop) {
    return Object.prototype.hasOwnProperty.call(obj, prop);
  };
  var create = function (s, p, root) {
    var self = this;
    var sp, ns, cn, scn, c, de = 0;
    s = /^((static) )?([\w.]+)(:([\w.]+))?/.exec(s);
    cn = s[3].match(/(^|\.)(\w+)$/i)[2];
    ns = self.createNS(s[3].replace(/\.\w+$/, ''), root);
    if (ns[cn]) {
      return;
    }
    if (s[2] === 'static') {
      ns[cn] = p;
      if (this.onCreate) {
        this.onCreate(s[2], s[3], ns[cn]);
      }
      return;
    }
    if (!p[cn]) {
      p[cn] = function () {
      };
      de = 1;
    }
    ns[cn] = p[cn];
    self.extend(ns[cn].prototype, p);
    if (s[5]) {
      sp = self.resolve(s[5]).prototype;
      scn = s[5].match(/\.(\w+)$/i)[1];
      c = ns[cn];
      if (de) {
        ns[cn] = function () {
          return sp[scn].apply(this, arguments);
        };
      } else {
        ns[cn] = function () {
          this.parent = sp[scn];
          return c.apply(this, arguments);
        };
      }
      ns[cn].prototype[cn] = ns[cn];
      self.each(sp, function (f, n) {
        ns[cn].prototype[n] = sp[n];
      });
      self.each(p, function (f, n) {
        if (sp[n]) {
          ns[cn].prototype[n] = function () {
            this.parent = sp[n];
            return f.apply(this, arguments);
          };
        } else {
          if (n !== cn) {
            ns[cn].prototype[n] = f;
          }
        }
      });
    }
    self.each(p.static, function (f, n) {
      ns[cn][n] = f;
    });
  };
  var extend = function (obj, ext) {
    var x = [];
    for (var _i = 2; _i < arguments.length; _i++) {
      x[_i - 2] = arguments[_i];
    }
    var i, l, name;
    var args = arguments;
    var value;
    for (i = 1, l = args.length; i < l; i++) {
      ext = args[i];
      for (name in ext) {
        if (ext.hasOwnProperty(name)) {
          value = ext[name];
          if (value !== undefined) {
            obj[name] = value;
          }
        }
      }
    }
    return obj;
  };
  var walk = function (o, f, n, s) {
    s = s || this;
    if (o) {
      if (n) {
        o = o[n];
      }
      $_c2vc3pkje5o2puw.each(o, function (o, i) {
        if (f.call(s, o, i, n) === false) {
          return false;
        }
        walk(o, f, n, s);
      });
    }
  };
  var createNS = function (n, o) {
    var i, v;
    o = o || window;
    n = n.split('.');
    for (i = 0; i < n.length; i++) {
      v = n[i];
      if (!o[v]) {
        o[v] = {};
      }
      o = o[v];
    }
    return o;
  };
  var resolve$1 = function (n, o) {
    var i, l;
    o = o || window;
    n = n.split('.');
    for (i = 0, l = n.length; i < l; i++) {
      o = o[n[i]];
      if (!o) {
        break;
      }
    }
    return o;
  };
  var explode = function (s, d) {
    if (!s || is(s, 'array')) {
      return s;
    }
    return $_c2vc3pkje5o2puw.map(s.split(d || ','), trim);
  };
  var _addCacheSuffix = function (url) {
    var cacheSuffix = $_9bd8pn9je5o2pp2.cacheSuffix;
    if (cacheSuffix) {
      url += (url.indexOf('?') === -1 ? '?' : '&') + cacheSuffix;
    }
    return url;
  };
  var $_a5gegtjje5o2pum = {
    trim: trim,
    isArray: $_c2vc3pkje5o2puw.isArray,
    is: is,
    toArray: $_c2vc3pkje5o2puw.toArray,
    makeMap: makeMap,
    each: $_c2vc3pkje5o2puw.each,
    map: $_c2vc3pkje5o2puw.map,
    grep: $_c2vc3pkje5o2puw.filter,
    inArray: $_c2vc3pkje5o2puw.indexOf,
    hasOwn: hasOwnProperty,
    extend: extend,
    create: create,
    walk: walk,
    createNS: createNS,
    resolve: resolve$1,
    explode: explode,
    _addCacheSuffix: _addCacheSuffix
  };

  var doc = document;
  var push$2 = Array.prototype.push;
  var slice$2 = Array.prototype.slice;
  var rquickExpr$1 = /^(?:[^#<]*(<[\w\W]+>)[^>]*$|#([\w\-]*)$)/;
  var Event = EventUtils.Event;
  var skipUniques = $_a5gegtjje5o2pum.makeMap('children,contents,next,prev');
  var isDefined = function (obj) {
    return typeof obj !== 'undefined';
  };
  var isString = function (obj) {
    return typeof obj === 'string';
  };
  var isWindow = function (obj) {
    return obj && obj === obj.window;
  };
  var createFragment = function (html, fragDoc) {
    var frag, node, container;
    fragDoc = fragDoc || doc;
    container = fragDoc.createElement('div');
    frag = fragDoc.createDocumentFragment();
    container.innerHTML = html;
    while (node = container.firstChild) {
      frag.appendChild(node);
    }
    return frag;
  };
  var domManipulate = function (targetNodes, sourceItem, callback, reverse) {
    var i;
    if (isString(sourceItem)) {
      sourceItem = createFragment(sourceItem, getElementDocument(targetNodes[0]));
    } else if (sourceItem.length && !sourceItem.nodeType) {
      sourceItem = DomQuery.makeArray(sourceItem);
      if (reverse) {
        for (i = sourceItem.length - 1; i >= 0; i--) {
          domManipulate(targetNodes, sourceItem[i], callback, reverse);
        }
      } else {
        for (i = 0; i < sourceItem.length; i++) {
          domManipulate(targetNodes, sourceItem[i], callback, reverse);
        }
      }
      return targetNodes;
    }
    if (sourceItem.nodeType) {
      i = targetNodes.length;
      while (i--) {
        callback.call(targetNodes[i], sourceItem);
      }
    }
    return targetNodes;
  };
  var hasClass = function (node, className) {
    return node && className && (' ' + node.className + ' ').indexOf(' ' + className + ' ') !== -1;
  };
  var wrap = function (elements, wrapper, all) {
    var lastParent, newWrapper;
    wrapper = DomQuery(wrapper)[0];
    elements.each(function () {
      var self = this;
      if (!all || lastParent !== self.parentNode) {
        lastParent = self.parentNode;
        newWrapper = wrapper.cloneNode(false);
        self.parentNode.insertBefore(newWrapper, self);
        newWrapper.appendChild(self);
      } else {
        newWrapper.appendChild(self);
      }
    });
    return elements;
  };
  var numericCssMap = $_a5gegtjje5o2pum.makeMap('fillOpacity fontWeight lineHeight opacity orphans widows zIndex zoom', ' ');
  var booleanMap = $_a5gegtjje5o2pum.makeMap('checked compact declare defer disabled ismap multiple nohref noshade nowrap readonly selected', ' ');
  var propFix = {
    for: 'htmlFor',
    class: 'className',
    readonly: 'readOnly'
  };
  var cssFix = { float: 'cssFloat' };
  var attrHooks = {};
  var cssHooks = {};
  var DomQuery = function (selector, context) {
    return new DomQuery.fn.init(selector, context);
  };
  var inArray = function (item, array) {
    var i;
    if (array.indexOf) {
      return array.indexOf(item);
    }
    i = array.length;
    while (i--) {
      if (array[i] === item) {
        return i;
      }
    }
    return -1;
  };
  var whiteSpaceRegExp$1 = /^\s*|\s*$/g;
  var trim$1 = function (str) {
    return str === null || str === undefined ? '' : ('' + str).replace(whiteSpaceRegExp$1, '');
  };
  var each$2 = function (obj, callback) {
    var length, key, i, value;
    if (obj) {
      length = obj.length;
      if (length === undefined) {
        for (key in obj) {
          if (obj.hasOwnProperty(key)) {
            value = obj[key];
            if (callback.call(value, key, value) === false) {
              break;
            }
          }
        }
      } else {
        for (i = 0; i < length; i++) {
          value = obj[i];
          if (callback.call(value, i, value) === false) {
            break;
          }
        }
      }
    }
    return obj;
  };
  var grep = function (array, callback) {
    var out = [];
    each$2(array, function (i, item) {
      if (callback(item, i)) {
        out.push(item);
      }
    });
    return out;
  };
  var getElementDocument = function (element) {
    if (!element) {
      return doc;
    }
    if (element.nodeType === 9) {
      return element;
    }
    return element.ownerDocument;
  };
  DomQuery.fn = DomQuery.prototype = {
    constructor: DomQuery,
    selector: '',
    context: null,
    length: 0,
    init: function (selector, context) {
      var self = this;
      var match, node;
      if (!selector) {
        return self;
      }
      if (selector.nodeType) {
        self.context = self[0] = selector;
        self.length = 1;
        return self;
      }
      if (context && context.nodeType) {
        self.context = context;
      } else {
        if (context) {
          return DomQuery(selector).attr(context);
        }
        self.context = context = document;
      }
      if (isString(selector)) {
        self.selector = selector;
        if (selector.charAt(0) === '<' && selector.charAt(selector.length - 1) === '>' && selector.length >= 3) {
          match = [
            null,
            selector,
            null
          ];
        } else {
          match = rquickExpr$1.exec(selector);
        }
        if (match) {
          if (match[1]) {
            node = createFragment(selector, getElementDocument(context)).firstChild;
            while (node) {
              push$2.call(self, node);
              node = node.nextSibling;
            }
          } else {
            node = getElementDocument(context).getElementById(match[2]);
            if (!node) {
              return self;
            }
            if (node.id !== match[2]) {
              return self.find(selector);
            }
            self.length = 1;
            self[0] = node;
          }
        } else {
          return DomQuery(context).find(selector);
        }
      } else {
        this.add(selector, false);
      }
      return self;
    },
    toArray: function () {
      return $_a5gegtjje5o2pum.toArray(this);
    },
    add: function (items, sort) {
      var self = this;
      var nodes, i;
      if (isString(items)) {
        return self.add(DomQuery(items));
      }
      if (sort !== false) {
        nodes = DomQuery.unique(self.toArray().concat(DomQuery.makeArray(items)));
        self.length = nodes.length;
        for (i = 0; i < nodes.length; i++) {
          self[i] = nodes[i];
        }
      } else {
        push$2.apply(self, DomQuery.makeArray(items));
      }
      return self;
    },
    attr: function (name, value) {
      var self = this;
      var hook;
      if (typeof name === 'object') {
        each$2(name, function (name, value) {
          self.attr(name, value);
        });
      } else if (isDefined(value)) {
        this.each(function () {
          var hook;
          if (this.nodeType === 1) {
            hook = attrHooks[name];
            if (hook && hook.set) {
              hook.set(this, value);
              return;
            }
            if (value === null) {
              this.removeAttribute(name, 2);
            } else {
              this.setAttribute(name, value, 2);
            }
          }
        });
      } else {
        if (self[0] && self[0].nodeType === 1) {
          hook = attrHooks[name];
          if (hook && hook.get) {
            return hook.get(self[0], name);
          }
          if (booleanMap[name]) {
            return self.prop(name) ? name : undefined;
          }
          value = self[0].getAttribute(name, 2);
          if (value === null) {
            value = undefined;
          }
        }
        return value;
      }
      return self;
    },
    removeAttr: function (name) {
      return this.attr(name, null);
    },
    prop: function (name, value) {
      var self = this;
      name = propFix[name] || name;
      if (typeof name === 'object') {
        each$2(name, function (name, value) {
          self.prop(name, value);
        });
      } else if (isDefined(value)) {
        this.each(function () {
          if (this.nodeType === 1) {
            this[name] = value;
          }
        });
      } else {
        if (self[0] && self[0].nodeType && name in self[0]) {
          return self[0][name];
        }
        return value;
      }
      return self;
    },
    css: function (name, value) {
      var self = this;
      var elm, hook;
      var camel = function (name) {
        return name.replace(/-(\D)/g, function (a, b) {
          return b.toUpperCase();
        });
      };
      var dashed = function (name) {
        return name.replace(/[A-Z]/g, function (a) {
          return '-' + a;
        });
      };
      if (typeof name === 'object') {
        each$2(name, function (name, value) {
          self.css(name, value);
        });
      } else {
        if (isDefined(value)) {
          name = camel(name);
          if (typeof value === 'number' && !numericCssMap[name]) {
            value = value.toString() + 'px';
          }
          self.each(function () {
            var style = this.style;
            hook = cssHooks[name];
            if (hook && hook.set) {
              hook.set(this, value);
              return;
            }
            try {
              this.style[cssFix[name] || name] = value;
            } catch (ex) {
            }
            if (value === null || value === '') {
              if (style.removeProperty) {
                style.removeProperty(dashed(name));
              } else {
                style.removeAttribute(name);
              }
            }
          });
        } else {
          elm = self[0];
          hook = cssHooks[name];
          if (hook && hook.get) {
            return hook.get(elm);
          }
          if (elm.ownerDocument.defaultView) {
            try {
              return elm.ownerDocument.defaultView.getComputedStyle(elm, null).getPropertyValue(dashed(name));
            } catch (ex) {
              return undefined;
            }
          } else if (elm.currentStyle) {
            return elm.currentStyle[camel(name)];
          }
        }
      }
      return self;
    },
    remove: function () {
      var self = this;
      var node, i = this.length;
      while (i--) {
        node = self[i];
        Event.clean(node);
        if (node.parentNode) {
          node.parentNode.removeChild(node);
        }
      }
      return this;
    },
    empty: function () {
      var self = this;
      var node, i = this.length;
      while (i--) {
        node = self[i];
        while (node.firstChild) {
          node.removeChild(node.firstChild);
        }
      }
      return this;
    },
    html: function (value) {
      var self = this;
      var i;
      if (isDefined(value)) {
        i = self.length;
        try {
          while (i--) {
            self[i].innerHTML = value;
          }
        } catch (ex) {
          DomQuery(self[i]).empty().append(value);
        }
        return self;
      }
      return self[0] ? self[0].innerHTML : '';
    },
    text: function (value) {
      var self = this;
      var i;
      if (isDefined(value)) {
        i = self.length;
        while (i--) {
          if ('innerText' in self[i]) {
            self[i].innerText = value;
          } else {
            self[0].textContent = value;
          }
        }
        return self;
      }
      return self[0] ? self[0].innerText || self[0].textContent : '';
    },
    append: function () {
      return domManipulate(this, arguments, function (node) {
        if (this.nodeType === 1 || this.host && this.host.nodeType === 1) {
          this.appendChild(node);
        }
      });
    },
    prepend: function () {
      return domManipulate(this, arguments, function (node) {
        if (this.nodeType === 1 || this.host && this.host.nodeType === 1) {
          this.insertBefore(node, this.firstChild);
        }
      }, true);
    },
    before: function () {
      var self = this;
      if (self[0] && self[0].parentNode) {
        return domManipulate(self, arguments, function (node) {
          this.parentNode.insertBefore(node, this);
        });
      }
      return self;
    },
    after: function () {
      var self = this;
      if (self[0] && self[0].parentNode) {
        return domManipulate(self, arguments, function (node) {
          this.parentNode.insertBefore(node, this.nextSibling);
        }, true);
      }
      return self;
    },
    appendTo: function (val) {
      DomQuery(val).append(this);
      return this;
    },
    prependTo: function (val) {
      DomQuery(val).prepend(this);
      return this;
    },
    replaceWith: function (content) {
      return this.before(content).remove();
    },
    wrap: function (content) {
      return wrap(this, content);
    },
    wrapAll: function (content) {
      return wrap(this, content, true);
    },
    wrapInner: function (content) {
      this.each(function () {
        DomQuery(this).contents().wrapAll(content);
      });
      return this;
    },
    unwrap: function () {
      return this.parent().each(function () {
        DomQuery(this).replaceWith(this.childNodes);
      });
    },
    clone: function () {
      var result = [];
      this.each(function () {
        result.push(this.cloneNode(true));
      });
      return DomQuery(result);
    },
    addClass: function (className) {
      return this.toggleClass(className, true);
    },
    removeClass: function (className) {
      return this.toggleClass(className, false);
    },
    toggleClass: function (className, state) {
      var self = this;
      if (typeof className !== 'string') {
        return self;
      }
      if (className.indexOf(' ') !== -1) {
        each$2(className.split(' '), function () {
          self.toggleClass(this, state);
        });
      } else {
        self.each(function (index, node) {
          var existingClassName, classState;
          classState = hasClass(node, className);
          if (classState !== state) {
            existingClassName = node.className;
            if (classState) {
              node.className = trim$1((' ' + existingClassName + ' ').replace(' ' + className + ' ', ' '));
            } else {
              node.className += existingClassName ? ' ' + className : className;
            }
          }
        });
      }
      return self;
    },
    hasClass: function (className) {
      return hasClass(this[0], className);
    },
    each: function (callback) {
      return each$2(this, callback);
    },
    on: function (name, callback) {
      return this.each(function () {
        Event.bind(this, name, callback);
      });
    },
    off: function (name, callback) {
      return this.each(function () {
        Event.unbind(this, name, callback);
      });
    },
    trigger: function (name) {
      return this.each(function () {
        if (typeof name === 'object') {
          Event.fire(this, name.type, name);
        } else {
          Event.fire(this, name);
        }
      });
    },
    show: function () {
      return this.css('display', '');
    },
    hide: function () {
      return this.css('display', 'none');
    },
    slice: function () {
      return new DomQuery(slice$2.apply(this, arguments));
    },
    eq: function (index) {
      return index === -1 ? this.slice(index) : this.slice(index, +index + 1);
    },
    first: function () {
      return this.eq(0);
    },
    last: function () {
      return this.eq(-1);
    },
    find: function (selector) {
      var i, l;
      var ret = [];
      for (i = 0, l = this.length; i < l; i++) {
        DomQuery.find(selector, this[i], ret);
      }
      return DomQuery(ret);
    },
    filter: function (selector) {
      if (typeof selector === 'function') {
        return DomQuery(grep(this.toArray(), function (item, i) {
          return selector(i, item);
        }));
      }
      return DomQuery(DomQuery.filter(selector, this.toArray()));
    },
    closest: function (selector) {
      var result = [];
      if (selector instanceof DomQuery) {
        selector = selector[0];
      }
      this.each(function (i, node) {
        while (node) {
          if (typeof selector === 'string' && DomQuery(node).is(selector)) {
            result.push(node);
            break;
          } else if (node === selector) {
            result.push(node);
            break;
          }
          node = node.parentNode;
        }
      });
      return DomQuery(result);
    },
    offset: function (offset) {
      var elm, doc, docElm;
      var x = 0, y = 0, pos;
      if (!offset) {
        elm = this[0];
        if (elm) {
          doc = elm.ownerDocument;
          docElm = doc.documentElement;
          if (elm.getBoundingClientRect) {
            pos = elm.getBoundingClientRect();
            x = pos.left + (docElm.scrollLeft || doc.body.scrollLeft) - docElm.clientLeft;
            y = pos.top + (docElm.scrollTop || doc.body.scrollTop) - docElm.clientTop;
          }
        }
        return {
          left: x,
          top: y
        };
      }
      return this.css(offset);
    },
    push: push$2,
    sort: [].sort,
    splice: [].splice
  };
  $_a5gegtjje5o2pum.extend(DomQuery, {
    extend: $_a5gegtjje5o2pum.extend,
    makeArray: function (object) {
      if (isWindow(object) || object.nodeType) {
        return [object];
      }
      return $_a5gegtjje5o2pum.toArray(object);
    },
    inArray: inArray,
    isArray: $_a5gegtjje5o2pum.isArray,
    each: each$2,
    trim: trim$1,
    grep: grep,
    find: Sizzle,
    expr: Sizzle.selectors,
    unique: Sizzle.uniqueSort,
    text: Sizzle.getText,
    contains: Sizzle.contains,
    filter: function (expr, elems, not) {
      var i = elems.length;
      if (not) {
        expr = ':not(' + expr + ')';
      }
      while (i--) {
        if (elems[i].nodeType !== 1) {
          elems.splice(i, 1);
        }
      }
      if (elems.length === 1) {
        elems = DomQuery.find.matchesSelector(elems[0], expr) ? [elems[0]] : [];
      } else {
        elems = DomQuery.find.matches(expr, elems);
      }
      return elems;
    }
  });
  var dir = function (el, prop, until) {
    var matched = [];
    var cur = el[prop];
    if (typeof until !== 'string' && until instanceof DomQuery) {
      until = until[0];
    }
    while (cur && cur.nodeType !== 9) {
      if (until !== undefined) {
        if (cur === until) {
          break;
        }
        if (typeof until === 'string' && DomQuery(cur).is(until)) {
          break;
        }
      }
      if (cur.nodeType === 1) {
        matched.push(cur);
      }
      cur = cur[prop];
    }
    return matched;
  };
  var sibling = function (node, siblingName, nodeType, until) {
    var result = [];
    if (until instanceof DomQuery) {
      until = until[0];
    }
    for (; node; node = node[siblingName]) {
      if (nodeType && node.nodeType !== nodeType) {
        continue;
      }
      if (until !== undefined) {
        if (node === until) {
          break;
        }
        if (typeof until === 'string' && DomQuery(node).is(until)) {
          break;
        }
      }
      result.push(node);
    }
    return result;
  };
  var firstSibling = function (node, siblingName, nodeType) {
    for (node = node[siblingName]; node; node = node[siblingName]) {
      if (node.nodeType === nodeType) {
        return node;
      }
    }
    return null;
  };
  each$2({
    parent: function (node) {
      var parent = node.parentNode;
      return parent && parent.nodeType !== 11 ? parent : null;
    },
    parents: function (node) {
      return dir(node, 'parentNode');
    },
    next: function (node) {
      return firstSibling(node, 'nextSibling', 1);
    },
    prev: function (node) {
      return firstSibling(node, 'previousSibling', 1);
    },
    children: function (node) {
      return sibling(node.firstChild, 'nextSibling', 1);
    },
    contents: function (node) {
      return $_a5gegtjje5o2pum.toArray((node.nodeName === 'iframe' ? node.contentDocument || node.contentWindow.document : node).childNodes);
    }
  }, function (name, fn) {
    DomQuery.fn[name] = function (selector) {
      var self = this;
      var result = [];
      self.each(function () {
        var nodes = fn.call(result, this, selector, result);
        if (nodes) {
          if (DomQuery.isArray(nodes)) {
            result.push.apply(result, nodes);
          } else {
            result.push(nodes);
          }
        }
      });
      if (this.length > 1) {
        if (!skipUniques[name]) {
          result = DomQuery.unique(result);
        }
        if (name.indexOf('parents') === 0) {
          result = result.reverse();
        }
      }
      result = DomQuery(result);
      if (selector) {
        return result.filter(selector);
      }
      return result;
    };
  });
  each$2({
    parentsUntil: function (node, until) {
      return dir(node, 'parentNode', until);
    },
    nextUntil: function (node, until) {
      return sibling(node, 'nextSibling', 1, until).slice(1);
    },
    prevUntil: function (node, until) {
      return sibling(node, 'previousSibling', 1, until).slice(1);
    }
  }, function (name, fn) {
    DomQuery.fn[name] = function (selector, filter) {
      var self = this;
      var result = [];
      self.each(function () {
        var nodes = fn.call(result, this, selector, result);
        if (nodes) {
          if (DomQuery.isArray(nodes)) {
            result.push.apply(result, nodes);
          } else {
            result.push(nodes);
          }
        }
      });
      if (this.length > 1) {
        result = DomQuery.unique(result);
        if (name.indexOf('parents') === 0 || name === 'prevUntil') {
          result = result.reverse();
        }
      }
      result = DomQuery(result);
      if (filter) {
        return result.filter(filter);
      }
      return result;
    };
  });
  DomQuery.fn.is = function (selector) {
    return !!selector && this.filter(selector).length > 0;
  };
  DomQuery.fn.init.prototype = DomQuery.fn;
  DomQuery.overrideDefaults = function (callback) {
    var defaults;
    var sub = function (selector, context) {
      defaults = defaults || callback();
      if (arguments.length === 0) {
        selector = defaults.element;
      }
      if (!context) {
        context = defaults.context;
      }
      return new sub.fn.init(selector, context);
    };
    DomQuery.extend(sub, this);
    return sub;
  };
  var appendHooks = function (targetHooks, prop, hooks) {
    each$2(hooks, function (name, func) {
      targetHooks[name] = targetHooks[name] || {};
      targetHooks[name][prop] = func;
    });
  };
  if ($_9bd8pn9je5o2pp2.ie && $_9bd8pn9je5o2pp2.ie < 8) {
    appendHooks(attrHooks, 'get', {
      maxlength: function (elm) {
        var value = elm.maxLength;
        if (value === 2147483647) {
          return undefined;
        }
        return value;
      },
      size: function (elm) {
        var value = elm.size;
        if (value === 20) {
          return undefined;
        }
        return value;
      },
      class: function (elm) {
        return elm.className;
      },
      style: function (elm) {
        var value = elm.style.cssText;
        if (value.length === 0) {
          return undefined;
        }
        return value;
      }
    });
    appendHooks(attrHooks, 'set', {
      class: function (elm, value) {
        elm.className = value;
      },
      style: function (elm, value) {
        elm.style.cssText = value;
      }
    });
  }
  if ($_9bd8pn9je5o2pp2.ie && $_9bd8pn9je5o2pp2.ie < 9) {
    cssFix.float = 'styleFloat';
    appendHooks(cssHooks, 'set', {
      opacity: function (elm, value) {
        var style = elm.style;
        if (value === null || value === '') {
          style.removeAttribute('filter');
        } else {
          style.zoom = 1;
          style.filter = 'alpha(opacity=' + value * 100 + ')';
        }
      }
    });
  }
  DomQuery.attrHooks = attrHooks;
  DomQuery.cssHooks = cssHooks;

  var cached = function (f) {
    var called = false;
    var r;
    return function () {
      if (!called) {
        called = true;
        r = f.apply(null, arguments);
      }
      return r;
    };
  };
  var $_g85o1xnje5o2pvw = { cached: cached };

  var firstMatch = function (regexes, s) {
    for (var i = 0; i < regexes.length; i++) {
      var x = regexes[i];
      if (x.test(s))
        return x;
    }
    return undefined;
  };
  var find$2 = function (regexes, agent) {
    var r = firstMatch(regexes, agent);
    if (!r)
      return {
        major: 0,
        minor: 0
      };
    var group = function (i) {
      return Number(agent.replace(r, '$' + i));
    };
    return nu(group(1), group(2));
  };
  var detect = function (versionRegexes, agent) {
    var cleanedAgent = String(agent).toLowerCase();
    if (versionRegexes.length === 0)
      return unknown();
    return find$2(versionRegexes, cleanedAgent);
  };
  var unknown = function () {
    return nu(0, 0);
  };
  var nu = function (major, minor) {
    return {
      major: major,
      minor: minor
    };
  };
  var $_df13jvqje5o2pw3 = {
    nu: nu,
    detect: detect,
    unknown: unknown
  };

  var edge = 'Edge';
  var chrome = 'Chrome';
  var ie$1 = 'IE';
  var opera$1 = 'Opera';
  var firefox = 'Firefox';
  var safari = 'Safari';
  var isBrowser = function (name, current) {
    return function () {
      return current === name;
    };
  };
  var unknown$1 = function () {
    return nu$1({
      current: undefined,
      version: $_df13jvqje5o2pw3.unknown()
    });
  };
  var nu$1 = function (info) {
    var current = info.current;
    var version = info.version;
    return {
      current: current,
      version: version,
      isEdge: isBrowser(edge, current),
      isChrome: isBrowser(chrome, current),
      isIE: isBrowser(ie$1, current),
      isOpera: isBrowser(opera$1, current),
      isFirefox: isBrowser(firefox, current),
      isSafari: isBrowser(safari, current)
    };
  };
  var $_3tg2x6pje5o2pvz = {
    unknown: unknown$1,
    nu: nu$1,
    edge: $_enfhkv6je5o2ply.constant(edge),
    chrome: $_enfhkv6je5o2ply.constant(chrome),
    ie: $_enfhkv6je5o2ply.constant(ie$1),
    opera: $_enfhkv6je5o2ply.constant(opera$1),
    firefox: $_enfhkv6je5o2ply.constant(firefox),
    safari: $_enfhkv6je5o2ply.constant(safari)
  };

  var windows = 'Windows';
  var ios = 'iOS';
  var android$1 = 'Android';
  var linux = 'Linux';
  var osx = 'OSX';
  var solaris = 'Solaris';
  var freebsd = 'FreeBSD';
  var isOS = function (name, current) {
    return function () {
      return current === name;
    };
  };
  var unknown$2 = function () {
    return nu$2({
      current: undefined,
      version: $_df13jvqje5o2pw3.unknown()
    });
  };
  var nu$2 = function (info) {
    var current = info.current;
    var version = info.version;
    return {
      current: current,
      version: version,
      isWindows: isOS(windows, current),
      isiOS: isOS(ios, current),
      isAndroid: isOS(android$1, current),
      isOSX: isOS(osx, current),
      isLinux: isOS(linux, current),
      isSolaris: isOS(solaris, current),
      isFreeBSD: isOS(freebsd, current)
    };
  };
  var $_w4a0lrje5o2pw4 = {
    unknown: unknown$2,
    nu: nu$2,
    windows: $_enfhkv6je5o2ply.constant(windows),
    ios: $_enfhkv6je5o2ply.constant(ios),
    android: $_enfhkv6je5o2ply.constant(android$1),
    linux: $_enfhkv6je5o2ply.constant(linux),
    osx: $_enfhkv6je5o2ply.constant(osx),
    solaris: $_enfhkv6je5o2ply.constant(solaris),
    freebsd: $_enfhkv6je5o2ply.constant(freebsd)
  };

  function DeviceType (os, browser, userAgent) {
    var isiPad = os.isiOS() && /ipad/i.test(userAgent) === true;
    var isiPhone = os.isiOS() && !isiPad;
    var isAndroid3 = os.isAndroid() && os.version.major === 3;
    var isAndroid4 = os.isAndroid() && os.version.major === 4;
    var isTablet = isiPad || isAndroid3 || isAndroid4 && /mobile/i.test(userAgent) === true;
    var isTouch = os.isiOS() || os.isAndroid();
    var isPhone = isTouch && !isTablet;
    var iOSwebview = browser.isSafari() && os.isiOS() && /safari/i.test(userAgent) === false;
    return {
      isiPad: $_enfhkv6je5o2ply.constant(isiPad),
      isiPhone: $_enfhkv6je5o2ply.constant(isiPhone),
      isTablet: $_enfhkv6je5o2ply.constant(isTablet),
      isPhone: $_enfhkv6je5o2ply.constant(isPhone),
      isTouch: $_enfhkv6je5o2ply.constant(isTouch),
      isAndroid: os.isAndroid,
      isiOS: os.isiOS,
      isWebView: $_enfhkv6je5o2ply.constant(iOSwebview)
    };
  }

  var detect$1 = function (candidates, userAgent) {
    var agent = String(userAgent).toLowerCase();
    return $_bwcteb4je5o2pl4.find(candidates, function (candidate) {
      return candidate.search(agent);
    });
  };
  var detectBrowser = function (browsers, userAgent) {
    return detect$1(browsers, userAgent).map(function (browser) {
      var version = $_df13jvqje5o2pw3.detect(browser.versionRegexes, userAgent);
      return {
        current: browser.name,
        version: version
      };
    });
  };
  var detectOs = function (oses, userAgent) {
    return detect$1(oses, userAgent).map(function (os) {
      var version = $_df13jvqje5o2pw3.detect(os.versionRegexes, userAgent);
      return {
        current: os.name,
        version: version
      };
    });
  };
  var $_4evi4wtje5o2pwb = {
    detectBrowser: detectBrowser,
    detectOs: detectOs
  };

  var addToStart = function (str, prefix) {
    return prefix + str;
  };
  var addToEnd = function (str, suffix) {
    return str + suffix;
  };
  var removeFromStart = function (str, numChars) {
    return str.substring(numChars);
  };
  var removeFromEnd = function (str, numChars) {
    return str.substring(0, str.length - numChars);
  };
  var $_3qinixwje5o2pwr = {
    addToStart: addToStart,
    addToEnd: addToEnd,
    removeFromStart: removeFromStart,
    removeFromEnd: removeFromEnd
  };

  var first = function (str, count) {
    return str.substr(0, count);
  };
  var last$2 = function (str, count) {
    return str.substr(str.length - count, str.length);
  };
  var head$1 = function (str) {
    return str === '' ? Option.none() : Option.some(str.substr(0, 1));
  };
  var tail = function (str) {
    return str === '' ? Option.none() : Option.some(str.substring(1));
  };
  var $_7ingkmxje5o2pws = {
    first: first,
    last: last$2,
    head: head$1,
    tail: tail
  };

  var checkRange = function (str, substr, start) {
    if (substr === '')
      return true;
    if (str.length < substr.length)
      return false;
    var x = str.substr(start, start + substr.length);
    return x === substr;
  };
  var supplant = function (str, obj) {
    var isStringOrNumber = function (a) {
      var t = typeof a;
      return t === 'string' || t === 'number';
    };
    return str.replace(/\${([^{}]*)}/g, function (a, b) {
      var value = obj[b];
      return isStringOrNumber(value) ? value : a;
    });
  };
  var removeLeading = function (str, prefix) {
    return startsWith(str, prefix) ? $_3qinixwje5o2pwr.removeFromStart(str, prefix.length) : str;
  };
  var removeTrailing = function (str, prefix) {
    return endsWith(str, prefix) ? $_3qinixwje5o2pwr.removeFromEnd(str, prefix.length) : str;
  };
  var ensureLeading = function (str, prefix) {
    return startsWith(str, prefix) ? str : $_3qinixwje5o2pwr.addToStart(str, prefix);
  };
  var ensureTrailing = function (str, prefix) {
    return endsWith(str, prefix) ? str : $_3qinixwje5o2pwr.addToEnd(str, prefix);
  };
  var contains$2 = function (str, substr) {
    return str.indexOf(substr) !== -1;
  };
  var capitalize = function (str) {
    return $_7ingkmxje5o2pws.head(str).bind(function (head) {
      return $_7ingkmxje5o2pws.tail(str).map(function (tail) {
        return head.toUpperCase() + tail;
      });
    }).getOr(str);
  };
  var startsWith = function (str, prefix) {
    return checkRange(str, prefix, 0);
  };
  var endsWith = function (str, suffix) {
    return checkRange(str, suffix, str.length - suffix.length);
  };
  var trim$2 = function (str) {
    return str.replace(/^\s+|\s+$/g, '');
  };
  var lTrim = function (str) {
    return str.replace(/^\s+/g, '');
  };
  var rTrim = function (str) {
    return str.replace(/\s+$/g, '');
  };
  var $_6dh61xvje5o2pwo = {
    supplant: supplant,
    startsWith: startsWith,
    removeLeading: removeLeading,
    removeTrailing: removeTrailing,
    ensureLeading: ensureLeading,
    ensureTrailing: ensureTrailing,
    endsWith: endsWith,
    contains: contains$2,
    trim: trim$2,
    lTrim: lTrim,
    rTrim: rTrim,
    capitalize: capitalize
  };

  var normalVersionRegex = /.*?version\/\ ?([0-9]+)\.([0-9]+).*/;
  var checkContains = function (target) {
    return function (uastring) {
      return $_6dh61xvje5o2pwo.contains(uastring, target);
    };
  };
  var browsers = [
    {
      name: 'Edge',
      versionRegexes: [/.*?edge\/ ?([0-9]+)\.([0-9]+)$/],
      search: function (uastring) {
        var monstrosity = $_6dh61xvje5o2pwo.contains(uastring, 'edge/') && $_6dh61xvje5o2pwo.contains(uastring, 'chrome') && $_6dh61xvje5o2pwo.contains(uastring, 'safari') && $_6dh61xvje5o2pwo.contains(uastring, 'applewebkit');
        return monstrosity;
      }
    },
    {
      name: 'Chrome',
      versionRegexes: [
        /.*?chrome\/([0-9]+)\.([0-9]+).*/,
        normalVersionRegex
      ],
      search: function (uastring) {
        return $_6dh61xvje5o2pwo.contains(uastring, 'chrome') && !$_6dh61xvje5o2pwo.contains(uastring, 'chromeframe');
      }
    },
    {
      name: 'IE',
      versionRegexes: [
        /.*?msie\ ?([0-9]+)\.([0-9]+).*/,
        /.*?rv:([0-9]+)\.([0-9]+).*/
      ],
      search: function (uastring) {
        return $_6dh61xvje5o2pwo.contains(uastring, 'msie') || $_6dh61xvje5o2pwo.contains(uastring, 'trident');
      }
    },
    {
      name: 'Opera',
      versionRegexes: [
        normalVersionRegex,
        /.*?opera\/([0-9]+)\.([0-9]+).*/
      ],
      search: checkContains('opera')
    },
    {
      name: 'Firefox',
      versionRegexes: [/.*?firefox\/\ ?([0-9]+)\.([0-9]+).*/],
      search: checkContains('firefox')
    },
    {
      name: 'Safari',
      versionRegexes: [
        normalVersionRegex,
        /.*?cpu os ([0-9]+)_([0-9]+).*/
      ],
      search: function (uastring) {
        return ($_6dh61xvje5o2pwo.contains(uastring, 'safari') || $_6dh61xvje5o2pwo.contains(uastring, 'mobile/')) && $_6dh61xvje5o2pwo.contains(uastring, 'applewebkit');
      }
    }
  ];
  var oses = [
    {
      name: 'Windows',
      search: checkContains('win'),
      versionRegexes: [/.*?windows\ nt\ ?([0-9]+)\.([0-9]+).*/]
    },
    {
      name: 'iOS',
      search: function (uastring) {
        return $_6dh61xvje5o2pwo.contains(uastring, 'iphone') || $_6dh61xvje5o2pwo.contains(uastring, 'ipad');
      },
      versionRegexes: [
        /.*?version\/\ ?([0-9]+)\.([0-9]+).*/,
        /.*cpu os ([0-9]+)_([0-9]+).*/,
        /.*cpu iphone os ([0-9]+)_([0-9]+).*/
      ]
    },
    {
      name: 'Android',
      search: checkContains('android'),
      versionRegexes: [/.*?android\ ?([0-9]+)\.([0-9]+).*/]
    },
    {
      name: 'OSX',
      search: checkContains('os x'),
      versionRegexes: [/.*?os\ x\ ?([0-9]+)_([0-9]+).*/]
    },
    {
      name: 'Linux',
      search: checkContains('linux'),
      versionRegexes: []
    },
    {
      name: 'Solaris',
      search: checkContains('sunos'),
      versionRegexes: []
    },
    {
      name: 'FreeBSD',
      search: checkContains('freebsd'),
      versionRegexes: []
    }
  ];
  var $_5dts3kuje5o2pwh = {
    browsers: $_enfhkv6je5o2ply.constant(browsers),
    oses: $_enfhkv6je5o2ply.constant(oses)
  };

  var detect$2 = function (userAgent) {
    var browsers = $_5dts3kuje5o2pwh.browsers();
    var oses = $_5dts3kuje5o2pwh.oses();
    var browser = $_4evi4wtje5o2pwb.detectBrowser(browsers, userAgent).fold($_3tg2x6pje5o2pvz.unknown, $_3tg2x6pje5o2pvz.nu);
    var os = $_4evi4wtje5o2pwb.detectOs(oses, userAgent).fold($_w4a0lrje5o2pw4.unknown, $_w4a0lrje5o2pw4.nu);
    var deviceType = DeviceType(os, browser, userAgent);
    return {
      browser: browser,
      os: os,
      deviceType: deviceType
    };
  };
  var $_7glnnooje5o2pvy = { detect: detect$2 };

  var detect$3 = $_g85o1xnje5o2pvw.cached(function () {
    var userAgent = navigator.userAgent;
    return $_7glnnooje5o2pvy.detect(userAgent);
  });
  var $_2hq237mje5o2pvu = { detect: detect$3 };

  var fromHtml = function (html, scope) {
    var doc = scope || document;
    var div = doc.createElement('div');
    div.innerHTML = html;
    if (!div.hasChildNodes() || div.childNodes.length > 1) {
      console.error('HTML does not have a single root node', html);
      throw 'HTML must have a single root node';
    }
    return fromDom(div.childNodes[0]);
  };
  var fromTag = function (tag, scope) {
    var doc = scope || document;
    var node = doc.createElement(tag);
    return fromDom(node);
  };
  var fromText = function (text, scope) {
    var doc = scope || document;
    var node = doc.createTextNode(text);
    return fromDom(node);
  };
  var fromDom = function (node) {
    if (node === null || node === undefined)
      throw new Error('Node cannot be null or undefined');
    return { dom: $_enfhkv6je5o2ply.constant(node) };
  };
  var fromPoint = function (doc, x, y) {
    return Option.from(doc.dom().elementFromPoint(x, y)).map(fromDom);
  };
  var $_5tcb2dyje5o2pwu = {
    fromHtml: fromHtml,
    fromTag: fromTag,
    fromText: fromText,
    fromDom: fromDom,
    fromPoint: fromPoint
  };

  var $_62vbac10je5o2px2 = {
    ATTRIBUTE: 2,
    CDATA_SECTION: 4,
    COMMENT: 8,
    DOCUMENT: 9,
    DOCUMENT_TYPE: 10,
    DOCUMENT_FRAGMENT: 11,
    ELEMENT: 1,
    TEXT: 3,
    PROCESSING_INSTRUCTION: 7,
    ENTITY_REFERENCE: 5,
    ENTITY: 6,
    NOTATION: 12
  };

  var name = function (element) {
    var r = element.dom().nodeName;
    return r.toLowerCase();
  };
  var type = function (element) {
    return element.dom().nodeType;
  };
  var value = function (element) {
    return element.dom().nodeValue;
  };
  var isType = function (t) {
    return function (element) {
      return type(element) === t;
    };
  };
  var isComment = function (element) {
    return type(element) === $_62vbac10je5o2px2.COMMENT || name(element) === '#comment';
  };
  var isElement = isType($_62vbac10je5o2px2.ELEMENT);
  var isText = isType($_62vbac10je5o2px2.TEXT);
  var isDocument = isType($_62vbac10je5o2px2.DOCUMENT);
  var $_95wn8qzje5o2px0 = {
    name: name,
    type: type,
    value: value,
    isElement: isElement,
    isText: isText,
    isDocument: isDocument,
    isComment: isComment
  };

  var typeOf = function (x) {
    if (x === null)
      return 'null';
    var t = typeof x;
    if (t === 'object' && Array.prototype.isPrototypeOf(x))
      return 'array';
    if (t === 'object' && String.prototype.isPrototypeOf(x))
      return 'string';
    return t;
  };
  var isType$1 = function (type) {
    return function (value) {
      return typeOf(value) === type;
    };
  };
  var $_be3q8w12je5o2pxj = {
    isString: isType$1('string'),
    isObject: isType$1('object'),
    isArray: isType$1('array'),
    isNull: isType$1('null'),
    isBoolean: isType$1('boolean'),
    isUndefined: isType$1('undefined'),
    isFunction: isType$1('function'),
    isNumber: isType$1('number')
  };

  var keys = function () {
    var fastKeys = Object.keys;
    var slowKeys = function (o) {
      var r = [];
      for (var i in o) {
        if (o.hasOwnProperty(i)) {
          r.push(i);
        }
      }
      return r;
    };
    return fastKeys === undefined ? slowKeys : fastKeys;
  }();
  var each$3 = function (obj, f) {
    var props = keys(obj);
    for (var k = 0, len = props.length; k < len; k++) {
      var i = props[k];
      var x = obj[i];
      f(x, i, obj);
    }
  };
  var objectMap = function (obj, f) {
    return tupleMap(obj, function (x, i, obj) {
      return {
        k: i,
        v: f(x, i, obj)
      };
    });
  };
  var tupleMap = function (obj, f) {
    var r = {};
    each$3(obj, function (x, i) {
      var tuple = f(x, i, obj);
      r[tuple.k] = tuple.v;
    });
    return r;
  };
  var bifilter = function (obj, pred) {
    var t = {};
    var f = {};
    each$3(obj, function (x, i) {
      var branch = pred(x, i) ? t : f;
      branch[i] = x;
    });
    return {
      t: t,
      f: f
    };
  };
  var mapToArray = function (obj, f) {
    var r = [];
    each$3(obj, function (value, name) {
      r.push(f(value, name));
    });
    return r;
  };
  var find$3 = function (obj, pred) {
    var props = keys(obj);
    for (var k = 0, len = props.length; k < len; k++) {
      var i = props[k];
      var x = obj[i];
      if (pred(x, i, obj)) {
        return Option.some(x);
      }
    }
    return Option.none();
  };
  var values = function (obj) {
    return mapToArray(obj, function (v) {
      return v;
    });
  };
  var size = function (obj) {
    return values(obj).length;
  };
  var $_8v44sy13je5o2pxm = {
    bifilter: bifilter,
    each: each$3,
    map: objectMap,
    mapToArray: mapToArray,
    tupleMap: tupleMap,
    find: find$3,
    keys: keys,
    values: values,
    size: size
  };

  var rawSet = function (dom, key, value) {
    if ($_be3q8w12je5o2pxj.isString(value) || $_be3q8w12je5o2pxj.isBoolean(value) || $_be3q8w12je5o2pxj.isNumber(value)) {
      dom.setAttribute(key, value + '');
    } else {
      console.error('Invalid call to Attr.set. Key ', key, ':: Value ', value, ':: Element ', dom);
      throw new Error('Attribute value was not simple');
    }
  };
  var set = function (element, key, value) {
    rawSet(element.dom(), key, value);
  };
  var setAll = function (element, attrs) {
    var dom = element.dom();
    $_8v44sy13je5o2pxm.each(attrs, function (v, k) {
      rawSet(dom, k, v);
    });
  };
  var get = function (element, key) {
    var v = element.dom().getAttribute(key);
    return v === null ? undefined : v;
  };
  var has = function (element, key) {
    var dom = element.dom();
    return dom && dom.hasAttribute ? dom.hasAttribute(key) : false;
  };
  var remove = function (element, key) {
    element.dom().removeAttribute(key);
  };
  var hasNone = function (element) {
    var attrs = element.dom().attributes;
    return attrs === undefined || attrs === null || attrs.length === 0;
  };
  var clone = function (element) {
    return $_bwcteb4je5o2pl4.foldl(element.dom().attributes, function (acc, attr) {
      acc[attr.name] = attr.value;
      return acc;
    }, {});
  };
  var transferOne = function (source, destination, attr) {
    if (has(source, attr) && !has(destination, attr))
      set(destination, attr, get(source, attr));
  };
  var transfer = function (source, destination, attrs) {
    if (!$_95wn8qzje5o2px0.isElement(source) || !$_95wn8qzje5o2px0.isElement(destination))
      return;
    $_bwcteb4je5o2pl4.each(attrs, function (attr) {
      transferOne(source, destination, attr);
    });
  };
  var $_3uo9pl14je5o2pxq = {
    clone: clone,
    set: set,
    setAll: setAll,
    get: get,
    has: has,
    remove: remove,
    hasNone: hasNone,
    transfer: transfer
  };

  var inBody = function (element) {
    var dom = $_95wn8qzje5o2px0.isText(element) ? element.dom().parentNode : element.dom();
    return dom !== undefined && dom !== null && dom.ownerDocument.body.contains(dom);
  };
  var body = $_g85o1xnje5o2pvw.cached(function () {
    return getBody($_5tcb2dyje5o2pwu.fromDom(document));
  });
  var getBody = function (doc) {
    var body = doc.dom().body;
    if (body === null || body === undefined)
      throw 'Body is not available yet';
    return $_5tcb2dyje5o2pwu.fromDom(body);
  };
  var $_484buq15je5o2py8 = {
    body: body,
    getBody: getBody,
    inBody: inBody
  };

  var isSupported = function (dom) {
    return dom.style !== undefined;
  };
  var $_g9o6j616je5o2pyb = { isSupported: isSupported };

  var internalSet = function (dom, property, value) {
    if (!$_be3q8w12je5o2pxj.isString(value)) {
      console.error('Invalid call to CSS.set. Property ', property, ':: Value ', value, ':: Element ', dom);
      throw new Error('CSS value must be a string: ' + value);
    }
    if ($_g9o6j616je5o2pyb.isSupported(dom))
      dom.style.setProperty(property, value);
  };
  var internalRemove = function (dom, property) {
    if ($_g9o6j616je5o2pyb.isSupported(dom))
      dom.style.removeProperty(property);
  };
  var set$1 = function (element, property, value) {
    var dom = element.dom();
    internalSet(dom, property, value);
  };
  var setAll$1 = function (element, css) {
    var dom = element.dom();
    $_8v44sy13je5o2pxm.each(css, function (v, k) {
      internalSet(dom, k, v);
    });
  };
  var setOptions = function (element, css) {
    var dom = element.dom();
    $_8v44sy13je5o2pxm.each(css, function (v, k) {
      v.fold(function () {
        internalRemove(dom, k);
      }, function (value) {
        internalSet(dom, k, value);
      });
    });
  };
  var get$1 = function (element, property) {
    var dom = element.dom();
    var styles = window.getComputedStyle(dom);
    var r = styles.getPropertyValue(property);
    var v = r === '' && !$_484buq15je5o2py8.inBody(element) ? getUnsafeProperty(dom, property) : r;
    return v === null ? undefined : v;
  };
  var getUnsafeProperty = function (dom, property) {
    return $_g9o6j616je5o2pyb.isSupported(dom) ? dom.style.getPropertyValue(property) : '';
  };
  var getRaw = function (element, property) {
    var dom = element.dom();
    var raw = getUnsafeProperty(dom, property);
    return Option.from(raw).filter(function (r) {
      return r.length > 0;
    });
  };
  var getAllRaw = function (element) {
    var css = {};
    var dom = element.dom();
    if ($_g9o6j616je5o2pyb.isSupported(dom)) {
      for (var i = 0; i < dom.style.length; i++) {
        var ruleName = dom.style.item(i);
        css[ruleName] = dom.style[ruleName];
      }
    }
    return css;
  };
  var isValidValue = function (tag, property, value) {
    var element = $_5tcb2dyje5o2pwu.fromTag(tag);
    set$1(element, property, value);
    var style = getRaw(element, property);
    return style.isSome();
  };
  var remove$1 = function (element, property) {
    var dom = element.dom();
    internalRemove(dom, property);
    if ($_3uo9pl14je5o2pxq.has(element, 'style') && $_6dh61xvje5o2pwo.trim($_3uo9pl14je5o2pxq.get(element, 'style')) === '') {
      $_3uo9pl14je5o2pxq.remove(element, 'style');
    }
  };
  var preserve = function (element, f) {
    var oldStyles = $_3uo9pl14je5o2pxq.get(element, 'style');
    var result = f(element);
    var restore = oldStyles === undefined ? $_3uo9pl14je5o2pxq.remove : $_3uo9pl14je5o2pxq.set;
    restore(element, 'style', oldStyles);
    return result;
  };
  var copy = function (source, target) {
    var sourceDom = source.dom();
    var targetDom = target.dom();
    if ($_g9o6j616je5o2pyb.isSupported(sourceDom) && $_g9o6j616je5o2pyb.isSupported(targetDom)) {
      targetDom.style.cssText = sourceDom.style.cssText;
    }
  };
  var reflow = function (e) {
    return e.dom().offsetWidth;
  };
  var transferOne$1 = function (source, destination, style) {
    getRaw(source, style).each(function (value) {
      if (getRaw(destination, style).isNone())
        set$1(destination, style, value);
    });
  };
  var transfer$1 = function (source, destination, styles) {
    if (!$_95wn8qzje5o2px0.isElement(source) || !$_95wn8qzje5o2px0.isElement(destination))
      return;
    $_bwcteb4je5o2pl4.each(styles, function (style) {
      transferOne$1(source, destination, style);
    });
  };
  var $_1ioope11je5o2px4 = {
    copy: copy,
    set: set$1,
    preserve: preserve,
    setAll: setAll$1,
    setOptions: setOptions,
    remove: remove$1,
    get: get$1,
    getRaw: getRaw,
    getAllRaw: getAllRaw,
    isValidValue: isValidValue,
    reflow: reflow,
    transfer: transfer$1
  };

  function Immutable () {
    var fields = arguments;
    return function () {
      var values = new Array(arguments.length);
      for (var i = 0; i < values.length; i++)
        values[i] = arguments[i];
      if (fields.length !== values.length)
        throw new Error('Wrong number of arguments to struct. Expected "[' + fields.length + ']", got ' + values.length + ' arguments');
      var struct = {};
      $_bwcteb4je5o2pl4.each(fields, function (name, i) {
        struct[name] = $_enfhkv6je5o2ply.constant(values[i]);
      });
      return struct;
    };
  }

  var sort$1 = function (arr) {
    return arr.slice(0).sort();
  };
  var reqMessage = function (required, keys) {
    throw new Error('All required keys (' + sort$1(required).join(', ') + ') were not specified. Specified keys were: ' + sort$1(keys).join(', ') + '.');
  };
  var unsuppMessage = function (unsupported) {
    throw new Error('Unsupported keys for object: ' + sort$1(unsupported).join(', '));
  };
  var validateStrArr = function (label, array) {
    if (!$_be3q8w12je5o2pxj.isArray(array))
      throw new Error('The ' + label + ' fields must be an array. Was: ' + array + '.');
    $_bwcteb4je5o2pl4.each(array, function (a) {
      if (!$_be3q8w12je5o2pxj.isString(a))
        throw new Error('The value ' + a + ' in the ' + label + ' fields was not a string.');
    });
  };
  var invalidTypeMessage = function (incorrect, type) {
    throw new Error('All values need to be of type: ' + type + '. Keys (' + sort$1(incorrect).join(', ') + ') were not.');
  };
  var checkDupes = function (everything) {
    var sorted = sort$1(everything);
    var dupe = $_bwcteb4je5o2pl4.find(sorted, function (s, i) {
      return i < sorted.length - 1 && s === sorted[i + 1];
    });
    dupe.each(function (d) {
      throw new Error('The field: ' + d + ' occurs more than once in the combined fields: [' + sorted.join(', ') + '].');
    });
  };
  var $_avjnh51bje5o2pyw = {
    sort: sort$1,
    reqMessage: reqMessage,
    unsuppMessage: unsuppMessage,
    validateStrArr: validateStrArr,
    invalidTypeMessage: invalidTypeMessage,
    checkDupes: checkDupes
  };

  function MixedBag (required, optional) {
    var everything = required.concat(optional);
    if (everything.length === 0)
      throw new Error('You must specify at least one required or optional field.');
    $_avjnh51bje5o2pyw.validateStrArr('required', required);
    $_avjnh51bje5o2pyw.validateStrArr('optional', optional);
    $_avjnh51bje5o2pyw.checkDupes(everything);
    return function (obj) {
      var keys = $_8v44sy13je5o2pxm.keys(obj);
      var allReqd = $_bwcteb4je5o2pl4.forall(required, function (req) {
        return $_bwcteb4je5o2pl4.contains(keys, req);
      });
      if (!allReqd)
        $_avjnh51bje5o2pyw.reqMessage(required, keys);
      var unsupported = $_bwcteb4je5o2pl4.filter(keys, function (key) {
        return !$_bwcteb4je5o2pl4.contains(everything, key);
      });
      if (unsupported.length > 0)
        $_avjnh51bje5o2pyw.unsuppMessage(unsupported);
      var r = {};
      $_bwcteb4je5o2pl4.each(required, function (req) {
        r[req] = $_enfhkv6je5o2ply.constant(obj[req]);
      });
      $_bwcteb4je5o2pl4.each(optional, function (opt) {
        r[opt] = $_enfhkv6je5o2ply.constant(Object.prototype.hasOwnProperty.call(obj, opt) ? Option.some(obj[opt]) : Option.none());
      });
      return r;
    };
  }

  var $_86drv18je5o2pyn = {
    immutable: Immutable,
    immutableBag: MixedBag
  };

  var toArray$1 = function (target, f) {
    var r = [];
    var recurse = function (e) {
      r.push(e);
      return f(e);
    };
    var cur = f(target);
    do {
      cur = cur.bind(recurse);
    } while (cur.isSome());
    return r;
  };
  var $_zryvs1cje5o2pyz = { toArray: toArray$1 };

  var node = function () {
    var f = $_lmz5vbje5o2pp9.getOrDie('Node');
    return f;
  };
  var compareDocumentPosition = function (a, b, match) {
    return (a.compareDocumentPosition(b) & match) !== 0;
  };
  var documentPositionPreceding = function (a, b) {
    return compareDocumentPosition(a, b, node().DOCUMENT_POSITION_PRECEDING);
  };
  var documentPositionContainedBy = function (a, b) {
    return compareDocumentPosition(a, b, node().DOCUMENT_POSITION_CONTAINED_BY);
  };
  var $_3s8zkc1eje5o2pz7 = {
    documentPositionPreceding: documentPositionPreceding,
    documentPositionContainedBy: documentPositionContainedBy
  };

  var ELEMENT = $_62vbac10je5o2px2.ELEMENT;
  var DOCUMENT = $_62vbac10je5o2px2.DOCUMENT;
  var is$1 = function (element, selector) {
    var elem = element.dom();
    if (elem.nodeType !== ELEMENT)
      return false;
    else if (elem.matches !== undefined)
      return elem.matches(selector);
    else if (elem.msMatchesSelector !== undefined)
      return elem.msMatchesSelector(selector);
    else if (elem.webkitMatchesSelector !== undefined)
      return elem.webkitMatchesSelector(selector);
    else if (elem.mozMatchesSelector !== undefined)
      return elem.mozMatchesSelector(selector);
    else
      throw new Error('Browser lacks native selectors');
  };
  var bypassSelector = function (dom) {
    return dom.nodeType !== ELEMENT && dom.nodeType !== DOCUMENT || dom.childElementCount === 0;
  };
  var all = function (selector, scope) {
    var base = scope === undefined ? document : scope.dom();
    return bypassSelector(base) ? [] : $_bwcteb4je5o2pl4.map(base.querySelectorAll(selector), $_5tcb2dyje5o2pwu.fromDom);
  };
  var one = function (selector, scope) {
    var base = scope === undefined ? document : scope.dom();
    return bypassSelector(base) ? Option.none() : Option.from(base.querySelector(selector)).map($_5tcb2dyje5o2pwu.fromDom);
  };
  var $_87zu5k1fje5o2pz9 = {
    all: all,
    is: is$1,
    one: one
  };

  var eq = function (e1, e2) {
    return e1.dom() === e2.dom();
  };
  var isEqualNode = function (e1, e2) {
    return e1.dom().isEqualNode(e2.dom());
  };
  var member = function (element, elements) {
    return $_bwcteb4je5o2pl4.exists(elements, $_enfhkv6je5o2ply.curry(eq, element));
  };
  var regularContains = function (e1, e2) {
    var d1 = e1.dom(), d2 = e2.dom();
    return d1 === d2 ? false : d1.contains(d2);
  };
  var ieContains = function (e1, e2) {
    return $_3s8zkc1eje5o2pz7.documentPositionContainedBy(e1.dom(), e2.dom());
  };
  var browser = $_2hq237mje5o2pvu.detect().browser;
  var contains$3 = browser.isIE() ? ieContains : regularContains;
  var $_7oppmo1dje5o2pz1 = {
    eq: eq,
    isEqualNode: isEqualNode,
    member: member,
    contains: contains$3,
    is: $_87zu5k1fje5o2pz9.is
  };

  var owner = function (element) {
    return $_5tcb2dyje5o2pwu.fromDom(element.dom().ownerDocument);
  };
  var documentElement = function (element) {
    var doc = owner(element);
    return $_5tcb2dyje5o2pwu.fromDom(doc.dom().documentElement);
  };
  var defaultView = function (element) {
    var el = element.dom();
    var defaultView = el.ownerDocument.defaultView;
    return $_5tcb2dyje5o2pwu.fromDom(defaultView);
  };
  var parent = function (element) {
    var dom = element.dom();
    return Option.from(dom.parentNode).map($_5tcb2dyje5o2pwu.fromDom);
  };
  var findIndex$2 = function (element) {
    return parent(element).bind(function (p) {
      var kin = children(p);
      return $_bwcteb4je5o2pl4.findIndex(kin, function (elem) {
        return $_7oppmo1dje5o2pz1.eq(element, elem);
      });
    });
  };
  var parents = function (element, isRoot) {
    var stop = $_be3q8w12je5o2pxj.isFunction(isRoot) ? isRoot : $_enfhkv6je5o2ply.constant(false);
    var dom = element.dom();
    var ret = [];
    while (dom.parentNode !== null && dom.parentNode !== undefined) {
      var rawParent = dom.parentNode;
      var parent = $_5tcb2dyje5o2pwu.fromDom(rawParent);
      ret.push(parent);
      if (stop(parent) === true)
        break;
      else
        dom = rawParent;
    }
    return ret;
  };
  var siblings = function (element) {
    var filterSelf = function (elements) {
      return $_bwcteb4je5o2pl4.filter(elements, function (x) {
        return !$_7oppmo1dje5o2pz1.eq(element, x);
      });
    };
    return parent(element).map(children).map(filterSelf).getOr([]);
  };
  var offsetParent = function (element) {
    var dom = element.dom();
    return Option.from(dom.offsetParent).map($_5tcb2dyje5o2pwu.fromDom);
  };
  var prevSibling = function (element) {
    var dom = element.dom();
    return Option.from(dom.previousSibling).map($_5tcb2dyje5o2pwu.fromDom);
  };
  var nextSibling = function (element) {
    var dom = element.dom();
    return Option.from(dom.nextSibling).map($_5tcb2dyje5o2pwu.fromDom);
  };
  var prevSiblings = function (element) {
    return $_bwcteb4je5o2pl4.reverse($_zryvs1cje5o2pyz.toArray(element, prevSibling));
  };
  var nextSiblings = function (element) {
    return $_zryvs1cje5o2pyz.toArray(element, nextSibling);
  };
  var children = function (element) {
    var dom = element.dom();
    return $_bwcteb4je5o2pl4.map(dom.childNodes, $_5tcb2dyje5o2pwu.fromDom);
  };
  var child = function (element, index) {
    var children = element.dom().childNodes;
    return Option.from(children[index]).map($_5tcb2dyje5o2pwu.fromDom);
  };
  var firstChild = function (element) {
    return child(element, 0);
  };
  var lastChild = function (element) {
    return child(element, element.dom().childNodes.length - 1);
  };
  var childNodesCount = function (element) {
    return element.dom().childNodes.length;
  };
  var hasChildNodes = function (element) {
    return element.dom().hasChildNodes();
  };
  var spot = $_86drv18je5o2pyn.immutable('element', 'offset');
  var leaf = function (element, offset) {
    var cs = children(element);
    return cs.length > 0 && offset < cs.length ? spot(cs[offset], 0) : spot(element, offset);
  };
  var $_a6mttx17je5o2pyd = {
    owner: owner,
    defaultView: defaultView,
    documentElement: documentElement,
    parent: parent,
    findIndex: findIndex$2,
    parents: parents,
    siblings: siblings,
    prevSibling: prevSibling,
    offsetParent: offsetParent,
    prevSiblings: prevSiblings,
    nextSibling: nextSibling,
    nextSiblings: nextSiblings,
    children: children,
    child: child,
    firstChild: firstChild,
    lastChild: lastChild,
    childNodesCount: childNodesCount,
    hasChildNodes: hasChildNodes,
    leaf: leaf
  };

  var browser$1 = $_2hq237mje5o2pvu.detect().browser;
  var firstElement = function (nodes) {
    return $_bwcteb4je5o2pl4.find(nodes, $_95wn8qzje5o2px0.isElement);
  };
  var getTableCaptionDeltaY = function (elm) {
    if (browser$1.isFirefox() && $_95wn8qzje5o2px0.name(elm) === 'table') {
      return firstElement($_a6mttx17je5o2pyd.children(elm)).filter(function (elm) {
        return $_95wn8qzje5o2px0.name(elm) === 'caption';
      }).bind(function (caption) {
        return firstElement($_a6mttx17je5o2pyd.nextSiblings(caption)).map(function (body) {
          var bodyTop = body.dom().offsetTop;
          var captionTop = caption.dom().offsetTop;
          var captionHeight = caption.dom().offsetHeight;
          return bodyTop <= captionTop ? -captionHeight : 0;
        });
      }).getOr(0);
    } else {
      return 0;
    }
  };
  var getPos = function (body, elm, rootElm) {
    var x = 0, y = 0, offsetParent;
    var doc = body.ownerDocument;
    var pos;
    rootElm = rootElm ? rootElm : body;
    if (elm) {
      if (rootElm === body && elm.getBoundingClientRect && $_1ioope11je5o2px4.get($_5tcb2dyje5o2pwu.fromDom(body), 'position') === 'static') {
        pos = elm.getBoundingClientRect();
        x = pos.left + (doc.documentElement.scrollLeft || body.scrollLeft) - doc.documentElement.clientLeft;
        y = pos.top + (doc.documentElement.scrollTop || body.scrollTop) - doc.documentElement.clientTop;
        return {
          x: x,
          y: y
        };
      }
      offsetParent = elm;
      while (offsetParent && offsetParent !== rootElm && offsetParent.nodeType) {
        x += offsetParent.offsetLeft || 0;
        y += offsetParent.offsetTop || 0;
        offsetParent = offsetParent.offsetParent;
      }
      offsetParent = elm.parentNode;
      while (offsetParent && offsetParent !== rootElm && offsetParent.nodeType) {
        x -= offsetParent.scrollLeft || 0;
        y -= offsetParent.scrollTop || 0;
        offsetParent = offsetParent.parentNode;
      }
      y += getTableCaptionDeltaY($_5tcb2dyje5o2pwu.fromDom(elm));
    }
    return {
      x: x,
      y: y
    };
  };
  var $_5uqvv2lje5o2pvj = { getPos: getPos };

  var nu$3 = function (baseFn) {
    var data = Option.none();
    var callbacks = [];
    var map = function (f) {
      return nu$3(function (nCallback) {
        get(function (data) {
          nCallback(f(data));
        });
      });
    };
    var get = function (nCallback) {
      if (isReady())
        call(nCallback);
      else
        callbacks.push(nCallback);
    };
    var set = function (x) {
      data = Option.some(x);
      run(callbacks);
      callbacks = [];
    };
    var isReady = function () {
      return data.isSome();
    };
    var run = function (cbs) {
      $_bwcteb4je5o2pl4.each(cbs, call);
    };
    var call = function (cb) {
      data.each(function (x) {
        setTimeout(function () {
          cb(x);
        }, 0);
      });
    };
    baseFn(set);
    return {
      get: get,
      map: map,
      isReady: isReady
    };
  };
  var pure$1 = function (a) {
    return nu$3(function (callback) {
      callback(a);
    });
  };
  var LazyValue = {
    nu: nu$3,
    pure: pure$1
  };

  var bounce = function (f) {
    return function () {
      var args = Array.prototype.slice.call(arguments);
      var me = this;
      setTimeout(function () {
        f.apply(me, args);
      }, 0);
    };
  };
  var $_bz4se01jje5o2pzu = { bounce: bounce };

  var nu$4 = function (baseFn) {
    var get = function (callback) {
      baseFn($_bz4se01jje5o2pzu.bounce(callback));
    };
    var map = function (fab) {
      return nu$4(function (callback) {
        get(function (a) {
          var value = fab(a);
          callback(value);
        });
      });
    };
    var bind = function (aFutureB) {
      return nu$4(function (callback) {
        get(function (a) {
          aFutureB(a).get(callback);
        });
      });
    };
    var anonBind = function (futureB) {
      return nu$4(function (callback) {
        get(function (a) {
          futureB.get(callback);
        });
      });
    };
    var toLazy = function () {
      return LazyValue.nu(get);
    };
    return {
      map: map,
      bind: bind,
      anonBind: anonBind,
      toLazy: toLazy,
      get: get
    };
  };
  var pure$2 = function (a) {
    return nu$4(function (callback) {
      callback(a);
    });
  };
  var Future = {
    nu: nu$4,
    pure: pure$2
  };

  var par = function (asyncValues, nu) {
    return nu(function (callback) {
      var r = [];
      var count = 0;
      var cb = function (i) {
        return function (value) {
          r[i] = value;
          count++;
          if (count >= asyncValues.length) {
            callback(r);
          }
        };
      };
      if (asyncValues.length === 0) {
        callback([]);
      } else {
        $_bwcteb4je5o2pl4.each(asyncValues, function (asyncValue, i) {
          asyncValue.get(cb(i));
        });
      }
    });
  };
  var $_7idrm1lje5o2pzx = { par: par };

  var par$1 = function (futures) {
    return $_7idrm1lje5o2pzx.par(futures, Future.nu);
  };
  var mapM = function (array, fn) {
    var futures = $_bwcteb4je5o2pl4.map(array, fn);
    return par$1(futures);
  };
  var compose$1 = function (f, g) {
    return function (a) {
      return g(a).bind(f);
    };
  };
  var $_bqrv8i1kje5o2pzv = {
    par: par$1,
    mapM: mapM,
    compose: compose$1
  };

  var value$1 = function (o) {
    var is = function (v) {
      return o === v;
    };
    var or = function (opt) {
      return value$1(o);
    };
    var orThunk = function (f) {
      return value$1(o);
    };
    var map = function (f) {
      return value$1(f(o));
    };
    var each = function (f) {
      f(o);
    };
    var bind = function (f) {
      return f(o);
    };
    var fold = function (_, onValue) {
      return onValue(o);
    };
    var exists = function (f) {
      return f(o);
    };
    var forall = function (f) {
      return f(o);
    };
    var toOption = function () {
      return Option.some(o);
    };
    return {
      is: is,
      isValue: $_enfhkv6je5o2ply.always,
      isError: $_enfhkv6je5o2ply.never,
      getOr: $_enfhkv6je5o2ply.constant(o),
      getOrThunk: $_enfhkv6je5o2ply.constant(o),
      getOrDie: $_enfhkv6je5o2ply.constant(o),
      or: or,
      orThunk: orThunk,
      fold: fold,
      map: map,
      each: each,
      bind: bind,
      exists: exists,
      forall: forall,
      toOption: toOption
    };
  };
  var error = function (message) {
    var getOrThunk = function (f) {
      return f();
    };
    var getOrDie = function () {
      return $_enfhkv6je5o2ply.die(message)();
    };
    var or = function (opt) {
      return opt;
    };
    var orThunk = function (f) {
      return f();
    };
    var map = function (f) {
      return error(message);
    };
    var bind = function (f) {
      return error(message);
    };
    var fold = function (onError, _) {
      return onError(message);
    };
    return {
      is: $_enfhkv6je5o2ply.never,
      isValue: $_enfhkv6je5o2ply.never,
      isError: $_enfhkv6je5o2ply.always,
      getOr: $_enfhkv6je5o2ply.identity,
      getOrThunk: getOrThunk,
      getOrDie: getOrDie,
      or: or,
      orThunk: orThunk,
      fold: fold,
      map: map,
      each: $_enfhkv6je5o2ply.noop,
      bind: bind,
      exists: $_enfhkv6je5o2ply.never,
      forall: $_enfhkv6je5o2ply.always,
      toOption: Option.none
    };
  };
  var Result = {
    value: value$1,
    error: error
  };

  function StyleSheetLoader (document, settings) {
    var idCount = 0;
    var loadedStates = {};
    var maxLoadTime;
    settings = settings || {};
    maxLoadTime = settings.maxLoadTime || 5000;
    var appendToHead = function (node) {
      document.getElementsByTagName('head')[0].appendChild(node);
    };
    var load = function (url, loadedCallback, errorCallback) {
      var link, style, startTime, state;
      var passed = function () {
        var callbacks = state.passed;
        var i = callbacks.length;
        while (i--) {
          callbacks[i]();
        }
        state.status = 2;
        state.passed = [];
        state.failed = [];
      };
      var failed = function () {
        var callbacks = state.failed;
        var i = callbacks.length;
        while (i--) {
          callbacks[i]();
        }
        state.status = 3;
        state.passed = [];
        state.failed = [];
      };
      var isOldWebKit = function () {
        var webKitChunks = navigator.userAgent.match(/WebKit\/(\d*)/);
        return !!(webKitChunks && parseInt(webKitChunks[1], 10) < 536);
      };
      var wait = function (testCallback, waitCallback) {
        if (!testCallback()) {
          if (new Date().getTime() - startTime < maxLoadTime) {
            $_db41wogje5o2ps3.setTimeout(waitCallback);
          } else {
            failed();
          }
        }
      };
      var waitForWebKitLinkLoaded = function () {
        wait(function () {
          var styleSheets = document.styleSheets;
          var styleSheet, i = styleSheets.length, owner;
          while (i--) {
            styleSheet = styleSheets[i];
            owner = styleSheet.ownerNode ? styleSheet.ownerNode : styleSheet.owningElement;
            if (owner && owner.id === link.id) {
              passed();
              return true;
            }
          }
        }, waitForWebKitLinkLoaded);
      };
      var waitForGeckoLinkLoaded = function () {
        wait(function () {
          try {
            var cssRules = style.sheet.cssRules;
            passed();
            return !!cssRules;
          } catch (ex) {
          }
        }, waitForGeckoLinkLoaded);
      };
      url = $_a5gegtjje5o2pum._addCacheSuffix(url);
      if (!loadedStates[url]) {
        state = {
          passed: [],
          failed: []
        };
        loadedStates[url] = state;
      } else {
        state = loadedStates[url];
      }
      if (loadedCallback) {
        state.passed.push(loadedCallback);
      }
      if (errorCallback) {
        state.failed.push(errorCallback);
      }
      if (state.status === 1) {
        return;
      }
      if (state.status === 2) {
        passed();
        return;
      }
      if (state.status === 3) {
        failed();
        return;
      }
      state.status = 1;
      link = document.createElement('link');
      link.rel = 'stylesheet';
      link.type = 'text/css';
      link.id = 'u' + idCount++;
      link.async = false;
      link.defer = false;
      startTime = new Date().getTime();
      if ('onload' in link && !isOldWebKit()) {
        link.onload = waitForWebKitLinkLoaded;
        link.onerror = failed;
      } else {
        if (navigator.userAgent.indexOf('Firefox') > 0) {
          style = document.createElement('style');
          style.textContent = '@import "' + url + '"';
          waitForGeckoLinkLoaded();
          appendToHead(style);
          return;
        }
        waitForWebKitLinkLoaded();
      }
      appendToHead(link);
      link.href = url;
    };
    var loadF = function (url) {
      return Future.nu(function (resolve) {
        load(url, $_enfhkv6je5o2ply.compose(resolve, $_enfhkv6je5o2ply.constant(Result.value(url))), $_enfhkv6je5o2ply.compose(resolve, $_enfhkv6je5o2ply.constant(Result.error(url))));
      });
    };
    var unbox = function (result) {
      return result.fold($_enfhkv6je5o2ply.identity, $_enfhkv6je5o2ply.identity);
    };
    var loadAll = function (urls, success, failure) {
      $_bqrv8i1kje5o2pzv.par($_bwcteb4je5o2pl4.map(urls, loadF)).get(function (result) {
        var parts = $_bwcteb4je5o2pl4.partition(result, function (r) {
          return r.isValue();
        });
        if (parts.fail.length > 0) {
          failure(parts.fail.map(unbox));
        } else {
          success(parts.pass.map(unbox));
        }
      });
    };
    return {
      load: load,
      loadAll: loadAll
    };
  }

  function TreeWalker (startNode, rootNode) {
    var node = startNode;
    var findSibling = function (node, startName, siblingName, shallow) {
      var sibling, parent;
      if (node) {
        if (!shallow && node[startName]) {
          return node[startName];
        }
        if (node !== rootNode) {
          sibling = node[siblingName];
          if (sibling) {
            return sibling;
          }
          for (parent = node.parentNode; parent && parent !== rootNode; parent = parent.parentNode) {
            sibling = parent[siblingName];
            if (sibling) {
              return sibling;
            }
          }
        }
      }
    };
    var findPreviousNode = function (node, startName, siblingName, shallow) {
      var sibling, parent, child;
      if (node) {
        sibling = node[siblingName];
        if (rootNode && sibling === rootNode) {
          return;
        }
        if (sibling) {
          if (!shallow) {
            for (child = sibling[startName]; child; child = child[startName]) {
              if (!child[startName]) {
                return child;
              }
            }
          }
          return sibling;
        }
        parent = node.parentNode;
        if (parent && parent !== rootNode) {
          return parent;
        }
      }
    };
    this.current = function () {
      return node;
    };
    this.next = function (shallow) {
      node = findSibling(node, 'firstChild', 'nextSibling', shallow);
      return node;
    };
    this.prev = function (shallow) {
      node = findSibling(node, 'lastChild', 'previousSibling', shallow);
      return node;
    };
    this.prev2 = function (shallow) {
      node = findPreviousNode(node, 'lastChild', 'previousSibling', shallow);
      return node;
    };
  }

  var blocks = [
    'article',
    'aside',
    'details',
    'div',
    'dt',
    'figcaption',
    'footer',
    'form',
    'fieldset',
    'header',
    'hgroup',
    'html',
    'main',
    'nav',
    'section',
    'summary',
    'body',
    'p',
    'dl',
    'multicol',
    'dd',
    'figure',
    'address',
    'center',
    'blockquote',
    'h1',
    'h2',
    'h3',
    'h4',
    'h5',
    'h6',
    'listing',
    'xmp',
    'pre',
    'plaintext',
    'menu',
    'dir',
    'ul',
    'ol',
    'li',
    'hr',
    'table',
    'tbody',
    'thead',
    'tfoot',
    'th',
    'tr',
    'td',
    'caption'
  ];
  var voids = [
    'area',
    'base',
    'basefont',
    'br',
    'col',
    'frame',
    'hr',
    'img',
    'input',
    'isindex',
    'link',
    'meta',
    'param',
    'embed',
    'source',
    'wbr',
    'track'
  ];
  var tableCells = [
    'td',
    'th'
  ];
  var tableSections = [
    'thead',
    'tbody',
    'tfoot'
  ];
  var textBlocks = [
    'h1',
    'h2',
    'h3',
    'h4',
    'h5',
    'h6',
    'p',
    'div',
    'address',
    'pre',
    'form',
    'blockquote',
    'center',
    'dir',
    'fieldset',
    'header',
    'footer',
    'article',
    'section',
    'hgroup',
    'aside',
    'nav',
    'figure'
  ];
  var headings = [
    'h1',
    'h2',
    'h3',
    'h4',
    'h5',
    'h6'
  ];
  var listItems = [
    'li',
    'dd',
    'dt'
  ];
  var lists = [
    'ul',
    'ol',
    'dl'
  ];
  var lazyLookup = function (items) {
    var lookup;
    return function (node) {
      lookup = lookup ? lookup : $_bwcteb4je5o2pl4.mapToObject(items, $_enfhkv6je5o2ply.constant(true));
      return lookup.hasOwnProperty($_95wn8qzje5o2px0.name(node));
    };
  };
  var isHeading = lazyLookup(headings);
  var isBlock = lazyLookup(blocks);
  var isInline = function (node) {
    return $_95wn8qzje5o2px0.isElement(node) && !isBlock(node);
  };
  var isBr = function (node) {
    return $_95wn8qzje5o2px0.isElement(node) && $_95wn8qzje5o2px0.name(node) === 'br';
  };
  var isTextBlock = lazyLookup(textBlocks);
  var isList = lazyLookup(lists);
  var isListItem = lazyLookup(listItems);
  var isVoid = lazyLookup(voids);
  var isTableSection = lazyLookup(tableSections);
  var isTableCell = lazyLookup(tableCells);

  var isNodeType = function (type) {
    return function (node) {
      return !!node && node.nodeType === type;
    };
  };
  var isElement$1 = isNodeType(1);
  var matchNodeNames = function (names) {
    var items = names.toLowerCase().split(' ');
    return function (node) {
      var i, name;
      if (node && node.nodeType) {
        name = node.nodeName.toLowerCase();
        for (i = 0; i < items.length; i++) {
          if (name === items[i]) {
            return true;
          }
        }
      }
      return false;
    };
  };
  var matchStyleValues = function (name, values) {
    var items = values.toLowerCase().split(' ');
    return function (node) {
      var i, cssValue;
      if (isElement$1(node)) {
        for (i = 0; i < items.length; i++) {
          cssValue = node.ownerDocument.defaultView.getComputedStyle(node, null).getPropertyValue(name);
          if (cssValue === items[i]) {
            return true;
          }
        }
      }
      return false;
    };
  };
  var hasPropValue = function (propName, propValue) {
    return function (node) {
      return isElement$1(node) && node[propName] === propValue;
    };
  };
  var hasAttribute = function (attrName, attrValue) {
    return function (node) {
      return isElement$1(node) && node.hasAttribute(attrName);
    };
  };
  var hasAttributeValue = function (attrName, attrValue) {
    return function (node) {
      return isElement$1(node) && node.getAttribute(attrName) === attrValue;
    };
  };
  var isBogus = function (node) {
    return isElement$1(node) && node.hasAttribute('data-mce-bogus');
  };
  var isBogusAll = function (node) {
    return isElement$1(node) && node.getAttribute('data-mce-bogus') === 'all';
  };
  var isTable = function (node) {
    return isElement$1(node) && node.tagName === 'TABLE';
  };
  var hasContentEditableState = function (value) {
    return function (node) {
      if (isElement$1(node)) {
        if (node.contentEditable === value) {
          return true;
        }
        if (node.getAttribute('data-mce-contenteditable') === value) {
          return true;
        }
      }
      return false;
    };
  };
  var isText$1 = isNodeType(3);
  var isComment$1 = isNodeType(8);
  var isDocument$1 = isNodeType(9);
  var isBr$1 = matchNodeNames('br');
  var isContentEditableTrue = hasContentEditableState('true');
  var isContentEditableFalse = hasContentEditableState('false');
  var $_9owqcy1qje5o2q0e = {
    isText: isText$1,
    isElement: isElement$1,
    isComment: isComment$1,
    isDocument: isDocument$1,
    isBr: isBr$1,
    isContentEditableTrue: isContentEditableTrue,
    isContentEditableFalse: isContentEditableFalse,
    matchNodeNames: matchNodeNames,
    hasPropValue: hasPropValue,
    hasAttribute: hasAttribute,
    hasAttributeValue: hasAttributeValue,
    matchStyleValues: matchStyleValues,
    isBogus: isBogus,
    isBogusAll: isBogusAll,
    isTable: isTable
  };

  var surroundedBySpans = function (node) {
    var previousIsSpan = node.previousSibling && node.previousSibling.nodeName === 'SPAN';
    var nextIsSpan = node.nextSibling && node.nextSibling.nodeName === 'SPAN';
    return previousIsSpan && nextIsSpan;
  };
  var isBookmarkNode = function (node) {
    return node && node.tagName === 'SPAN' && node.getAttribute('data-mce-type') === 'bookmark';
  };
  var trimNode = function (dom, node) {
    var i, children = node.childNodes;
    if ($_9owqcy1qje5o2q0e.isElement(node) && isBookmarkNode(node)) {
      return;
    }
    for (i = children.length - 1; i >= 0; i--) {
      trimNode(dom, children[i]);
    }
    if ($_9owqcy1qje5o2q0e.isDocument(node) === false) {
      if ($_9owqcy1qje5o2q0e.isText(node) && node.nodeValue.length > 0) {
        var trimmedLength = $_a5gegtjje5o2pum.trim(node.nodeValue).length;
        if (dom.isBlock(node.parentNode) || trimmedLength > 0) {
          return;
        }
        if (trimmedLength === 0 && surroundedBySpans(node)) {
          return;
        }
      } else if ($_9owqcy1qje5o2q0e.isElement(node)) {
        children = node.childNodes;
        if (children.length === 1 && isBookmarkNode(children[0])) {
          node.parentNode.insertBefore(children[0], node);
        }
        if (children.length || isVoid($_5tcb2dyje5o2pwu.fromDom(node))) {
          return;
        }
      }
      dom.remove(node);
    }
    return node;
  };
  var $_cfe5l31oje5o2q05 = { trimNode: trimNode };

  var makeMap$1 = $_a5gegtjje5o2pum.makeMap;
  var namedEntities;
  var baseEntities;
  var reverseEntities;
  var attrsCharsRegExp = /[&<>\"\u0060\u007E-\uD7FF\uE000-\uFFEF]|[\uD800-\uDBFF][\uDC00-\uDFFF]/g;
  var textCharsRegExp = /[<>&\u007E-\uD7FF\uE000-\uFFEF]|[\uD800-\uDBFF][\uDC00-\uDFFF]/g;
  var rawCharsRegExp = /[<>&\"\']/g;
  var entityRegExp = /&#([a-z0-9]+);?|&([a-z0-9]+);/gi;
  var asciiMap = {
    128: '\u20AC',
    130: '\u201A',
    131: '\u0192',
    132: '\u201E',
    133: '\u2026',
    134: '\u2020',
    135: '\u2021',
    136: '\u02c6',
    137: '\u2030',
    138: '\u0160',
    139: '\u2039',
    140: '\u0152',
    142: '\u017d',
    145: '\u2018',
    146: '\u2019',
    147: '\u201C',
    148: '\u201D',
    149: '\u2022',
    150: '\u2013',
    151: '\u2014',
    152: '\u02DC',
    153: '\u2122',
    154: '\u0161',
    155: '\u203A',
    156: '\u0153',
    158: '\u017e',
    159: '\u0178'
  };
  baseEntities = {
    '"': '&quot;',
    '\'': '&#39;',
    '<': '&lt;',
    '>': '&gt;',
    '&': '&amp;',
    '`': '&#96;'
  };
  reverseEntities = {
    '&lt;': '<',
    '&gt;': '>',
    '&amp;': '&',
    '&quot;': '"',
    '&apos;': '\''
  };
  var nativeDecode = function (text) {
    var elm;
    elm = $_5tcb2dyje5o2pwu.fromTag('div').dom();
    elm.innerHTML = text;
    return elm.textContent || elm.innerText || text;
  };
  var buildEntitiesLookup = function (items, radix) {
    var i, chr, entity;
    var lookup = {};
    if (items) {
      items = items.split(',');
      radix = radix || 10;
      for (i = 0; i < items.length; i += 2) {
        chr = String.fromCharCode(parseInt(items[i], radix));
        if (!baseEntities[chr]) {
          entity = '&' + items[i + 1] + ';';
          lookup[chr] = entity;
          lookup[entity] = chr;
        }
      }
      return lookup;
    }
  };
  namedEntities = buildEntitiesLookup('50,nbsp,51,iexcl,52,cent,53,pound,54,curren,55,yen,56,brvbar,57,sect,58,uml,59,copy,' + '5a,ordf,5b,laquo,5c,not,5d,shy,5e,reg,5f,macr,5g,deg,5h,plusmn,5i,sup2,5j,sup3,5k,acute,' + '5l,micro,5m,para,5n,middot,5o,cedil,5p,sup1,5q,ordm,5r,raquo,5s,frac14,5t,frac12,5u,frac34,' + '5v,iquest,60,Agrave,61,Aacute,62,Acirc,63,Atilde,64,Auml,65,Aring,66,AElig,67,Ccedil,' + '68,Egrave,69,Eacute,6a,Ecirc,6b,Euml,6c,Igrave,6d,Iacute,6e,Icirc,6f,Iuml,6g,ETH,6h,Ntilde,' + '6i,Ograve,6j,Oacute,6k,Ocirc,6l,Otilde,6m,Ouml,6n,times,6o,Oslash,6p,Ugrave,6q,Uacute,' + '6r,Ucirc,6s,Uuml,6t,Yacute,6u,THORN,6v,szlig,70,agrave,71,aacute,72,acirc,73,atilde,74,auml,' + '75,aring,76,aelig,77,ccedil,78,egrave,79,eacute,7a,ecirc,7b,euml,7c,igrave,7d,iacute,7e,icirc,' + '7f,iuml,7g,eth,7h,ntilde,7i,ograve,7j,oacute,7k,ocirc,7l,otilde,7m,ouml,7n,divide,7o,oslash,' + '7p,ugrave,7q,uacute,7r,ucirc,7s,uuml,7t,yacute,7u,thorn,7v,yuml,ci,fnof,sh,Alpha,si,Beta,' + 'sj,Gamma,sk,Delta,sl,Epsilon,sm,Zeta,sn,Eta,so,Theta,sp,Iota,sq,Kappa,sr,Lambda,ss,Mu,' + 'st,Nu,su,Xi,sv,Omicron,t0,Pi,t1,Rho,t3,Sigma,t4,Tau,t5,Upsilon,t6,Phi,t7,Chi,t8,Psi,' + 't9,Omega,th,alpha,ti,beta,tj,gamma,tk,delta,tl,epsilon,tm,zeta,tn,eta,to,theta,tp,iota,' + 'tq,kappa,tr,lambda,ts,mu,tt,nu,tu,xi,tv,omicron,u0,pi,u1,rho,u2,sigmaf,u3,sigma,u4,tau,' + 'u5,upsilon,u6,phi,u7,chi,u8,psi,u9,omega,uh,thetasym,ui,upsih,um,piv,812,bull,816,hellip,' + '81i,prime,81j,Prime,81u,oline,824,frasl,88o,weierp,88h,image,88s,real,892,trade,89l,alefsym,' + '8cg,larr,8ch,uarr,8ci,rarr,8cj,darr,8ck,harr,8dl,crarr,8eg,lArr,8eh,uArr,8ei,rArr,8ej,dArr,' + '8ek,hArr,8g0,forall,8g2,part,8g3,exist,8g5,empty,8g7,nabla,8g8,isin,8g9,notin,8gb,ni,8gf,prod,' + '8gh,sum,8gi,minus,8gn,lowast,8gq,radic,8gt,prop,8gu,infin,8h0,ang,8h7,and,8h8,or,8h9,cap,8ha,cup,' + '8hb,int,8hk,there4,8hs,sim,8i5,cong,8i8,asymp,8j0,ne,8j1,equiv,8j4,le,8j5,ge,8k2,sub,8k3,sup,8k4,' + 'nsub,8k6,sube,8k7,supe,8kl,oplus,8kn,otimes,8l5,perp,8m5,sdot,8o8,lceil,8o9,rceil,8oa,lfloor,8ob,' + 'rfloor,8p9,lang,8pa,rang,9ea,loz,9j0,spades,9j3,clubs,9j5,hearts,9j6,diams,ai,OElig,aj,oelig,b0,' + 'Scaron,b1,scaron,bo,Yuml,m6,circ,ms,tilde,802,ensp,803,emsp,809,thinsp,80c,zwnj,80d,zwj,80e,lrm,' + '80f,rlm,80j,ndash,80k,mdash,80o,lsquo,80p,rsquo,80q,sbquo,80s,ldquo,80t,rdquo,80u,bdquo,810,dagger,' + '811,Dagger,81g,permil,81p,lsaquo,81q,rsaquo,85c,euro', 32);
  var encodeRaw = function (text, attr) {
    return text.replace(attr ? attrsCharsRegExp : textCharsRegExp, function (chr) {
      return baseEntities[chr] || chr;
    });
  };
  var encodeAllRaw = function (text) {
    return ('' + text).replace(rawCharsRegExp, function (chr) {
      return baseEntities[chr] || chr;
    });
  };
  var encodeNumeric = function (text, attr) {
    return text.replace(attr ? attrsCharsRegExp : textCharsRegExp, function (chr) {
      if (chr.length > 1) {
        return '&#' + ((chr.charCodeAt(0) - 55296) * 1024 + (chr.charCodeAt(1) - 56320) + 65536) + ';';
      }
      return baseEntities[chr] || '&#' + chr.charCodeAt(0) + ';';
    });
  };
  var encodeNamed = function (text, attr, entities) {
    entities = entities || namedEntities;
    return text.replace(attr ? attrsCharsRegExp : textCharsRegExp, function (chr) {
      return baseEntities[chr] || entities[chr] || chr;
    });
  };
  var getEncodeFunc = function (name, entities) {
    var entitiesMap = buildEntitiesLookup(entities) || namedEntities;
    var encodeNamedAndNumeric = function (text, attr) {
      return text.replace(attr ? attrsCharsRegExp : textCharsRegExp, function (chr) {
        if (baseEntities[chr] !== undefined) {
          return baseEntities[chr];
        }
        if (entitiesMap[chr] !== undefined) {
          return entitiesMap[chr];
        }
        if (chr.length > 1) {
          return '&#' + ((chr.charCodeAt(0) - 55296) * 1024 + (chr.charCodeAt(1) - 56320) + 65536) + ';';
        }
        return '&#' + chr.charCodeAt(0) + ';';
      });
    };
    var encodeCustomNamed = function (text, attr) {
      return encodeNamed(text, attr, entitiesMap);
    };
    var nameMap = makeMap$1(name.replace(/\+/g, ','));
    if (nameMap.named && nameMap.numeric) {
      return encodeNamedAndNumeric;
    }
    if (nameMap.named) {
      if (entities) {
        return encodeCustomNamed;
      }
      return encodeNamed;
    }
    if (nameMap.numeric) {
      return encodeNumeric;
    }
    return encodeRaw;
  };
  var decode = function (text) {
    return text.replace(entityRegExp, function (all, numeric) {
      if (numeric) {
        if (numeric.charAt(0).toLowerCase() === 'x') {
          numeric = parseInt(numeric.substr(1), 16);
        } else {
          numeric = parseInt(numeric, 10);
        }
        if (numeric > 65535) {
          numeric -= 65536;
          return String.fromCharCode(55296 + (numeric >> 10), 56320 + (numeric & 1023));
        }
        return asciiMap[numeric] || String.fromCharCode(numeric);
      }
      return reverseEntities[all] || namedEntities[all] || nativeDecode(all);
    });
  };
  var $_fzh4ld1rje5o2q0t = {
    encodeRaw: encodeRaw,
    encodeAllRaw: encodeAllRaw,
    encodeNumeric: encodeNumeric,
    encodeNamed: encodeNamed,
    getEncodeFunc: getEncodeFunc,
    decode: decode
  };

  var mapCache = {};
  var dummyObj = {};
  var makeMap$2 = $_a5gegtjje5o2pum.makeMap;
  var each$4 = $_a5gegtjje5o2pum.each;
  var extend$1 = $_a5gegtjje5o2pum.extend;
  var explode$1 = $_a5gegtjje5o2pum.explode;
  var inArray$1 = $_a5gegtjje5o2pum.inArray;
  var split = function (items, delim) {
    items = $_a5gegtjje5o2pum.trim(items);
    return items ? items.split(delim || ' ') : [];
  };
  var compileSchema = function (type) {
    var schema = {};
    var globalAttributes, blockContent;
    var phrasingContent, flowContent, html4BlockContent, html4PhrasingContent;
    var add = function (name, attributes, children) {
      var ni, attributesOrder, element;
      var arrayToMap = function (array, obj) {
        var map = {};
        var i, l;
        for (i = 0, l = array.length; i < l; i++) {
          map[array[i]] = obj || {};
        }
        return map;
      };
      children = children || [];
      attributes = attributes || '';
      if (typeof children === 'string') {
        children = split(children);
      }
      name = split(name);
      ni = name.length;
      while (ni--) {
        attributesOrder = split([
          globalAttributes,
          attributes
        ].join(' '));
        element = {
          attributes: arrayToMap(attributesOrder),
          attributesOrder: attributesOrder,
          children: arrayToMap(children, dummyObj)
        };
        schema[name[ni]] = element;
      }
    };
    var addAttrs = function (name, attributes) {
      var ni, schemaItem, i, l;
      name = split(name);
      ni = name.length;
      attributes = split(attributes);
      while (ni--) {
        schemaItem = schema[name[ni]];
        for (i = 0, l = attributes.length; i < l; i++) {
          schemaItem.attributes[attributes[i]] = {};
          schemaItem.attributesOrder.push(attributes[i]);
        }
      }
    };
    if (mapCache[type]) {
      return mapCache[type];
    }
    globalAttributes = 'id accesskey class dir lang style tabindex title role';
    blockContent = 'address blockquote div dl fieldset form h1 h2 h3 h4 h5 h6 hr menu ol p pre table ul';
    phrasingContent = 'a abbr b bdo br button cite code del dfn em embed i iframe img input ins kbd ' + 'label map noscript object q s samp script select small span strong sub sup ' + 'textarea u var #text #comment';
    if (type !== 'html4') {
      globalAttributes += ' contenteditable contextmenu draggable dropzone ' + 'hidden spellcheck translate';
      blockContent += ' article aside details dialog figure header footer hgroup section nav';
      phrasingContent += ' audio canvas command datalist mark meter output picture ' + 'progress time wbr video ruby bdi keygen';
    }
    if (type !== 'html5-strict') {
      globalAttributes += ' xml:lang';
      html4PhrasingContent = 'acronym applet basefont big font strike tt';
      phrasingContent = [
        phrasingContent,
        html4PhrasingContent
      ].join(' ');
      each$4(split(html4PhrasingContent), function (name) {
        add(name, '', phrasingContent);
      });
      html4BlockContent = 'center dir isindex noframes';
      blockContent = [
        blockContent,
        html4BlockContent
      ].join(' ');
      flowContent = [
        blockContent,
        phrasingContent
      ].join(' ');
      each$4(split(html4BlockContent), function (name) {
        add(name, '', flowContent);
      });
    }
    flowContent = flowContent || [
      blockContent,
      phrasingContent
    ].join(' ');
    add('html', 'manifest', 'head body');
    add('head', '', 'base command link meta noscript script style title');
    add('title hr noscript br');
    add('base', 'href target');
    add('link', 'href rel media hreflang type sizes hreflang');
    add('meta', 'name http-equiv content charset');
    add('style', 'media type scoped');
    add('script', 'src async defer type charset');
    add('body', 'onafterprint onbeforeprint onbeforeunload onblur onerror onfocus ' + 'onhashchange onload onmessage onoffline ononline onpagehide onpageshow ' + 'onpopstate onresize onscroll onstorage onunload', flowContent);
    add('address dt dd div caption', '', flowContent);
    add('h1 h2 h3 h4 h5 h6 pre p abbr code var samp kbd sub sup i b u bdo span legend em strong small s cite dfn', '', phrasingContent);
    add('blockquote', 'cite', flowContent);
    add('ol', 'reversed start type', 'li');
    add('ul', '', 'li');
    add('li', 'value', flowContent);
    add('dl', '', 'dt dd');
    add('a', 'href target rel media hreflang type', phrasingContent);
    add('q', 'cite', phrasingContent);
    add('ins del', 'cite datetime', flowContent);
    add('img', 'src sizes srcset alt usemap ismap width height');
    add('iframe', 'src name width height', flowContent);
    add('embed', 'src type width height');
    add('object', 'data type typemustmatch name usemap form width height', [
      flowContent,
      'param'
    ].join(' '));
    add('param', 'name value');
    add('map', 'name', [
      flowContent,
      'area'
    ].join(' '));
    add('area', 'alt coords shape href target rel media hreflang type');
    add('table', 'border', 'caption colgroup thead tfoot tbody tr' + (type === 'html4' ? ' col' : ''));
    add('colgroup', 'span', 'col');
    add('col', 'span');
    add('tbody thead tfoot', '', 'tr');
    add('tr', '', 'td th');
    add('td', 'colspan rowspan headers', flowContent);
    add('th', 'colspan rowspan headers scope abbr', flowContent);
    add('form', 'accept-charset action autocomplete enctype method name novalidate target', flowContent);
    add('fieldset', 'disabled form name', [
      flowContent,
      'legend'
    ].join(' '));
    add('label', 'form for', phrasingContent);
    add('input', 'accept alt autocomplete checked dirname disabled form formaction formenctype formmethod formnovalidate ' + 'formtarget height list max maxlength min multiple name pattern readonly required size src step type value width');
    add('button', 'disabled form formaction formenctype formmethod formnovalidate formtarget name type value', type === 'html4' ? flowContent : phrasingContent);
    add('select', 'disabled form multiple name required size', 'option optgroup');
    add('optgroup', 'disabled label', 'option');
    add('option', 'disabled label selected value');
    add('textarea', 'cols dirname disabled form maxlength name readonly required rows wrap');
    add('menu', 'type label', [
      flowContent,
      'li'
    ].join(' '));
    add('noscript', '', flowContent);
    if (type !== 'html4') {
      add('wbr');
      add('ruby', '', [
        phrasingContent,
        'rt rp'
      ].join(' '));
      add('figcaption', '', flowContent);
      add('mark rt rp summary bdi', '', phrasingContent);
      add('canvas', 'width height', flowContent);
      add('video', 'src crossorigin poster preload autoplay mediagroup loop ' + 'muted controls width height buffered', [
        flowContent,
        'track source'
      ].join(' '));
      add('audio', 'src crossorigin preload autoplay mediagroup loop muted controls ' + 'buffered volume', [
        flowContent,
        'track source'
      ].join(' '));
      add('picture', '', 'img source');
      add('source', 'src srcset type media sizes');
      add('track', 'kind src srclang label default');
      add('datalist', '', [
        phrasingContent,
        'option'
      ].join(' '));
      add('article section nav aside header footer', '', flowContent);
      add('hgroup', '', 'h1 h2 h3 h4 h5 h6');
      add('figure', '', [
        flowContent,
        'figcaption'
      ].join(' '));
      add('time', 'datetime', phrasingContent);
      add('dialog', 'open', flowContent);
      add('command', 'type label icon disabled checked radiogroup command');
      add('output', 'for form name', phrasingContent);
      add('progress', 'value max', phrasingContent);
      add('meter', 'value min max low high optimum', phrasingContent);
      add('details', 'open', [
        flowContent,
        'summary'
      ].join(' '));
      add('keygen', 'autofocus challenge disabled form keytype name');
    }
    if (type !== 'html5-strict') {
      addAttrs('script', 'language xml:space');
      addAttrs('style', 'xml:space');
      addAttrs('object', 'declare classid code codebase codetype archive standby align border hspace vspace');
      addAttrs('embed', 'align name hspace vspace');
      addAttrs('param', 'valuetype type');
      addAttrs('a', 'charset name rev shape coords');
      addAttrs('br', 'clear');
      addAttrs('applet', 'codebase archive code object alt name width height align hspace vspace');
      addAttrs('img', 'name longdesc align border hspace vspace');
      addAttrs('iframe', 'longdesc frameborder marginwidth marginheight scrolling align');
      addAttrs('font basefont', 'size color face');
      addAttrs('input', 'usemap align');
      addAttrs('select', 'onchange');
      addAttrs('textarea');
      addAttrs('h1 h2 h3 h4 h5 h6 div p legend caption', 'align');
      addAttrs('ul', 'type compact');
      addAttrs('li', 'type');
      addAttrs('ol dl menu dir', 'compact');
      addAttrs('pre', 'width xml:space');
      addAttrs('hr', 'align noshade size width');
      addAttrs('isindex', 'prompt');
      addAttrs('table', 'summary width frame rules cellspacing cellpadding align bgcolor');
      addAttrs('col', 'width align char charoff valign');
      addAttrs('colgroup', 'width align char charoff valign');
      addAttrs('thead', 'align char charoff valign');
      addAttrs('tr', 'align char charoff valign bgcolor');
      addAttrs('th', 'axis align char charoff valign nowrap bgcolor width height');
      addAttrs('form', 'accept');
      addAttrs('td', 'abbr axis scope align char charoff valign nowrap bgcolor width height');
      addAttrs('tfoot', 'align char charoff valign');
      addAttrs('tbody', 'align char charoff valign');
      addAttrs('area', 'nohref');
      addAttrs('body', 'background bgcolor text link vlink alink');
    }
    if (type !== 'html4') {
      addAttrs('input button select textarea', 'autofocus');
      addAttrs('input textarea', 'placeholder');
      addAttrs('a', 'download');
      addAttrs('link script img', 'crossorigin');
      addAttrs('iframe', 'sandbox seamless allowfullscreen');
    }
    each$4(split('a form meter progress dfn'), function (name) {
      if (schema[name]) {
        delete schema[name].children[name];
      }
    });
    delete schema.caption.children.table;
    delete schema.script;
    mapCache[type] = schema;
    return schema;
  };
  var compileElementMap = function (value, mode) {
    var styles;
    if (value) {
      styles = {};
      if (typeof value === 'string') {
        value = { '*': value };
      }
      each$4(value, function (value, key) {
        styles[key] = styles[key.toUpperCase()] = mode === 'map' ? makeMap$2(value, /[, ]/) : explode$1(value, /[, ]/);
      });
    }
    return styles;
  };
  function Schema(settings) {
    var elements = {};
    var children = {};
    var patternElements = [];
    var validStyles;
    var invalidStyles;
    var schemaItems;
    var whiteSpaceElementsMap, selfClosingElementsMap, shortEndedElementsMap, boolAttrMap, validClasses;
    var blockElementsMap, nonEmptyElementsMap, moveCaretBeforeOnEnterElementsMap, textBlockElementsMap, textInlineElementsMap;
    var customElementsMap = {}, specialElements = {};
    var createLookupTable = function (option, defaultValue, extendWith) {
      var value = settings[option];
      if (!value) {
        value = mapCache[option];
        if (!value) {
          value = makeMap$2(defaultValue, ' ', makeMap$2(defaultValue.toUpperCase(), ' '));
          value = extend$1(value, extendWith);
          mapCache[option] = value;
        }
      } else {
        value = makeMap$2(value, /[, ]/, makeMap$2(value.toUpperCase(), /[, ]/));
      }
      return value;
    };
    settings = settings || {};
    schemaItems = compileSchema(settings.schema);
    if (settings.verify_html === false) {
      settings.valid_elements = '*[*]';
    }
    validStyles = compileElementMap(settings.valid_styles);
    invalidStyles = compileElementMap(settings.invalid_styles, 'map');
    validClasses = compileElementMap(settings.valid_classes, 'map');
    whiteSpaceElementsMap = createLookupTable('whitespace_elements', 'pre script noscript style textarea video audio iframe object code');
    selfClosingElementsMap = createLookupTable('self_closing_elements', 'colgroup dd dt li option p td tfoot th thead tr');
    shortEndedElementsMap = createLookupTable('short_ended_elements', 'area base basefont br col frame hr img input isindex link ' + 'meta param embed source wbr track');
    boolAttrMap = createLookupTable('boolean_attributes', 'checked compact declare defer disabled ismap multiple nohref noresize ' + 'noshade nowrap readonly selected autoplay loop controls');
    nonEmptyElementsMap = createLookupTable('non_empty_elements', 'td th iframe video audio object ' + 'script pre code', shortEndedElementsMap);
    moveCaretBeforeOnEnterElementsMap = createLookupTable('move_caret_before_on_enter_elements', 'table', nonEmptyElementsMap);
    textBlockElementsMap = createLookupTable('text_block_elements', 'h1 h2 h3 h4 h5 h6 p div address pre form ' + 'blockquote center dir fieldset header footer article section hgroup aside nav figure');
    blockElementsMap = createLookupTable('block_elements', 'hr table tbody thead tfoot ' + 'th tr td li ol ul caption dl dt dd noscript menu isindex option ' + 'datalist select optgroup figcaption', textBlockElementsMap);
    textInlineElementsMap = createLookupTable('text_inline_elements', 'span strong b em i font strike u var cite ' + 'dfn code mark q sup sub samp');
    each$4((settings.special || 'script noscript noframes noembed title style textarea xmp').split(' '), function (name) {
      specialElements[name] = new RegExp('</' + name + '[^>]*>', 'gi');
    });
    var patternToRegExp = function (str) {
      return new RegExp('^' + str.replace(/([?+*])/g, '.$1') + '$');
    };
    var addValidElements = function (validElements) {
      var ei, el, ai, al, matches, element, attr, attrData, elementName, attrName, attrType, attributes, attributesOrder, prefix, outputName, globalAttributes, globalAttributesOrder, key, value;
      var elementRuleRegExp = /^([#+\-])?([^\[!\/]+)(?:\/([^\[!]+))?(?:(!?)\[([^\]]+)\])?$/, attrRuleRegExp = /^([!\-])?(\w+[\\:]:\w+|[^=:<]+)?(?:([=:<])(.*))?$/, hasPatternsRegExp = /[*?+]/;
      if (validElements) {
        validElements = split(validElements, ',');
        if (elements['@']) {
          globalAttributes = elements['@'].attributes;
          globalAttributesOrder = elements['@'].attributesOrder;
        }
        for (ei = 0, el = validElements.length; ei < el; ei++) {
          matches = elementRuleRegExp.exec(validElements[ei]);
          if (matches) {
            prefix = matches[1];
            elementName = matches[2];
            outputName = matches[3];
            attrData = matches[5];
            attributes = {};
            attributesOrder = [];
            element = {
              attributes: attributes,
              attributesOrder: attributesOrder
            };
            if (prefix === '#') {
              element.paddEmpty = true;
            }
            if (prefix === '-') {
              element.removeEmpty = true;
            }
            if (matches[4] === '!') {
              element.removeEmptyAttrs = true;
            }
            if (globalAttributes) {
              for (key in globalAttributes) {
                attributes[key] = globalAttributes[key];
              }
              attributesOrder.push.apply(attributesOrder, globalAttributesOrder);
            }
            if (attrData) {
              attrData = split(attrData, '|');
              for (ai = 0, al = attrData.length; ai < al; ai++) {
                matches = attrRuleRegExp.exec(attrData[ai]);
                if (matches) {
                  attr = {};
                  attrType = matches[1];
                  attrName = matches[2].replace(/[\\:]:/g, ':');
                  prefix = matches[3];
                  value = matches[4];
                  if (attrType === '!') {
                    element.attributesRequired = element.attributesRequired || [];
                    element.attributesRequired.push(attrName);
                    attr.required = true;
                  }
                  if (attrType === '-') {
                    delete attributes[attrName];
                    attributesOrder.splice(inArray$1(attributesOrder, attrName), 1);
                    continue;
                  }
                  if (prefix) {
                    if (prefix === '=') {
                      element.attributesDefault = element.attributesDefault || [];
                      element.attributesDefault.push({
                        name: attrName,
                        value: value
                      });
                      attr.defaultValue = value;
                    }
                    if (prefix === ':') {
                      element.attributesForced = element.attributesForced || [];
                      element.attributesForced.push({
                        name: attrName,
                        value: value
                      });
                      attr.forcedValue = value;
                    }
                    if (prefix === '<') {
                      attr.validValues = makeMap$2(value, '?');
                    }
                  }
                  if (hasPatternsRegExp.test(attrName)) {
                    element.attributePatterns = element.attributePatterns || [];
                    attr.pattern = patternToRegExp(attrName);
                    element.attributePatterns.push(attr);
                  } else {
                    if (!attributes[attrName]) {
                      attributesOrder.push(attrName);
                    }
                    attributes[attrName] = attr;
                  }
                }
              }
            }
            if (!globalAttributes && elementName === '@') {
              globalAttributes = attributes;
              globalAttributesOrder = attributesOrder;
            }
            if (outputName) {
              element.outputName = elementName;
              elements[outputName] = element;
            }
            if (hasPatternsRegExp.test(elementName)) {
              element.pattern = patternToRegExp(elementName);
              patternElements.push(element);
            } else {
              elements[elementName] = element;
            }
          }
        }
      }
    };
    var setValidElements = function (validElements) {
      elements = {};
      patternElements = [];
      addValidElements(validElements);
      each$4(schemaItems, function (element, name) {
        children[name] = element.children;
      });
    };
    var addCustomElements = function (customElements) {
      var customElementRegExp = /^(~)?(.+)$/;
      if (customElements) {
        mapCache.text_block_elements = mapCache.block_elements = null;
        each$4(split(customElements, ','), function (rule) {
          var matches = customElementRegExp.exec(rule), inline = matches[1] === '~', cloneName = inline ? 'span' : 'div', name = matches[2];
          children[name] = children[cloneName];
          customElementsMap[name] = cloneName;
          if (!inline) {
            blockElementsMap[name.toUpperCase()] = {};
            blockElementsMap[name] = {};
          }
          if (!elements[name]) {
            var customRule = elements[cloneName];
            customRule = extend$1({}, customRule);
            delete customRule.removeEmptyAttrs;
            delete customRule.removeEmpty;
            elements[name] = customRule;
          }
          each$4(children, function (element, elmName) {
            if (element[cloneName]) {
              children[elmName] = element = extend$1({}, children[elmName]);
              element[name] = element[cloneName];
            }
          });
        });
      }
    };
    var addValidChildren = function (validChildren) {
      var childRuleRegExp = /^([+\-]?)(\w+)\[([^\]]+)\]$/;
      mapCache[settings.schema] = null;
      if (validChildren) {
        each$4(split(validChildren, ','), function (rule) {
          var matches = childRuleRegExp.exec(rule);
          var parent, prefix;
          if (matches) {
            prefix = matches[1];
            if (prefix) {
              parent = children[matches[2]];
            } else {
              parent = children[matches[2]] = { '#comment': {} };
            }
            parent = children[matches[2]];
            each$4(split(matches[3], '|'), function (child) {
              if (prefix === '-') {
                delete parent[child];
              } else {
                parent[child] = {};
              }
            });
          }
        });
      }
    };
    var getElementRule = function (name) {
      var element = elements[name], i;
      if (element) {
        return element;
      }
      i = patternElements.length;
      while (i--) {
        element = patternElements[i];
        if (element.pattern.test(name)) {
          return element;
        }
      }
    };
    if (!settings.valid_elements) {
      each$4(schemaItems, function (element, name) {
        elements[name] = {
          attributes: element.attributes,
          attributesOrder: element.attributesOrder
        };
        children[name] = element.children;
      });
      if (settings.schema !== 'html5') {
        each$4(split('strong/b em/i'), function (item) {
          item = split(item, '/');
          elements[item[1]].outputName = item[0];
        });
      }
      each$4(split('ol ul sub sup blockquote span font a table tbody tr strong em b i'), function (name) {
        if (elements[name]) {
          elements[name].removeEmpty = true;
        }
      });
      each$4(split('p h1 h2 h3 h4 h5 h6 th td pre div address caption li'), function (name) {
        elements[name].paddEmpty = true;
      });
      each$4(split('span'), function (name) {
        elements[name].removeEmptyAttrs = true;
      });
    } else {
      setValidElements(settings.valid_elements);
    }
    addCustomElements(settings.custom_elements);
    addValidChildren(settings.valid_children);
    addValidElements(settings.extended_valid_elements);
    addValidChildren('+ol[ul|ol],+ul[ul|ol]');
    each$4({
      dd: 'dl',
      dt: 'dl',
      li: 'ul ol',
      td: 'tr',
      th: 'tr',
      tr: 'tbody thead tfoot',
      tbody: 'table',
      thead: 'table',
      tfoot: 'table',
      legend: 'fieldset',
      area: 'map',
      param: 'video audio object'
    }, function (parents, item) {
      if (elements[item]) {
        elements[item].parentsRequired = split(parents);
      }
    });
    if (settings.invalid_elements) {
      each$4(explode$1(settings.invalid_elements), function (item) {
        if (elements[item]) {
          delete elements[item];
        }
      });
    }
    if (!getElementRule('span')) {
      addValidElements('span[!data-mce-type|*]');
    }
    var getValidStyles = function () {
      return validStyles;
    };
    var getInvalidStyles = function () {
      return invalidStyles;
    };
    var getValidClasses = function () {
      return validClasses;
    };
    var getBoolAttrs = function () {
      return boolAttrMap;
    };
    var getBlockElements = function () {
      return blockElementsMap;
    };
    var getTextBlockElements = function () {
      return textBlockElementsMap;
    };
    var getTextInlineElements = function () {
      return textInlineElementsMap;
    };
    var getShortEndedElements = function () {
      return shortEndedElementsMap;
    };
    var getSelfClosingElements = function () {
      return selfClosingElementsMap;
    };
    var getNonEmptyElements = function () {
      return nonEmptyElementsMap;
    };
    var getMoveCaretBeforeOnEnterElements = function () {
      return moveCaretBeforeOnEnterElementsMap;
    };
    var getWhiteSpaceElements = function () {
      return whiteSpaceElementsMap;
    };
    var getSpecialElements = function () {
      return specialElements;
    };
    var isValidChild = function (name, child) {
      var parent = children[name.toLowerCase()];
      return !!(parent && parent[child.toLowerCase()]);
    };
    var isValid = function (name, attr) {
      var attrPatterns, i;
      var rule = getElementRule(name);
      if (rule) {
        if (attr) {
          if (rule.attributes[attr]) {
            return true;
          }
          attrPatterns = rule.attributePatterns;
          if (attrPatterns) {
            i = attrPatterns.length;
            while (i--) {
              if (attrPatterns[i].pattern.test(name)) {
                return true;
              }
            }
          }
        } else {
          return true;
        }
      }
      return false;
    };
    var getCustomElements = function () {
      return customElementsMap;
    };
    return {
      children: children,
      elements: elements,
      getValidStyles: getValidStyles,
      getValidClasses: getValidClasses,
      getBlockElements: getBlockElements,
      getInvalidStyles: getInvalidStyles,
      getShortEndedElements: getShortEndedElements,
      getTextBlockElements: getTextBlockElements,
      getTextInlineElements: getTextInlineElements,
      getBoolAttrs: getBoolAttrs,
      getElementRule: getElementRule,
      getSelfClosingElements: getSelfClosingElements,
      getNonEmptyElements: getNonEmptyElements,
      getMoveCaretBeforeOnEnterElements: getMoveCaretBeforeOnEnterElements,
      getWhiteSpaceElements: getWhiteSpaceElements,
      getSpecialElements: getSpecialElements,
      isValidChild: isValidChild,
      isValid: isValid,
      getCustomElements: getCustomElements,
      addValidElements: addValidElements,
      setValidElements: setValidElements,
      addCustomElements: addCustomElements,
      addValidChildren: addValidChildren
    };
  }

  var toHex = function (match, r, g, b) {
    var hex = function (val) {
      val = parseInt(val, 10).toString(16);
      return val.length > 1 ? val : '0' + val;
    };
    return '#' + hex(r) + hex(g) + hex(b);
  };
  function Styles (settings, schema) {
    var rgbRegExp = /rgb\s*\(\s*([0-9]+)\s*,\s*([0-9]+)\s*,\s*([0-9]+)\s*\)/gi;
    var urlOrStrRegExp = /(?:url(?:(?:\(\s*\"([^\"]+)\"\s*\))|(?:\(\s*\'([^\']+)\'\s*\))|(?:\(\s*([^)\s]+)\s*\))))|(?:\'([^\']+)\')|(?:\"([^\"]+)\")/gi;
    var styleRegExp = /\s*([^:]+):\s*([^;]+);?/g;
    var trimRightRegExp = /\s+$/;
    var i;
    var encodingLookup = {};
    var encodingItems;
    var validStyles;
    var invalidStyles;
    var invisibleChar = '\uFEFF';
    settings = settings || {};
    if (schema) {
      validStyles = schema.getValidStyles();
      invalidStyles = schema.getInvalidStyles();
    }
    encodingItems = ('\\" \\\' \\; \\: ; : ' + invisibleChar).split(' ');
    for (i = 0; i < encodingItems.length; i++) {
      encodingLookup[encodingItems[i]] = invisibleChar + i;
      encodingLookup[invisibleChar + i] = encodingItems[i];
    }
    return {
      toHex: function (color) {
        return color.replace(rgbRegExp, toHex);
      },
      parse: function (css) {
        var styles = {};
        var matches, name, value, isEncoded;
        var urlConverter = settings.url_converter;
        var urlConverterScope = settings.url_converter_scope || this;
        var compress = function (prefix, suffix, noJoin) {
          var top, right, bottom, left;
          top = styles[prefix + '-top' + suffix];
          if (!top) {
            return;
          }
          right = styles[prefix + '-right' + suffix];
          if (!right) {
            return;
          }
          bottom = styles[prefix + '-bottom' + suffix];
          if (!bottom) {
            return;
          }
          left = styles[prefix + '-left' + suffix];
          if (!left) {
            return;
          }
          var box = [
            top,
            right,
            bottom,
            left
          ];
          i = box.length - 1;
          while (i--) {
            if (box[i] !== box[i + 1]) {
              break;
            }
          }
          if (i > -1 && noJoin) {
            return;
          }
          styles[prefix + suffix] = i === -1 ? box[0] : box.join(' ');
          delete styles[prefix + '-top' + suffix];
          delete styles[prefix + '-right' + suffix];
          delete styles[prefix + '-bottom' + suffix];
          delete styles[prefix + '-left' + suffix];
        };
        var canCompress = function (key) {
          var value = styles[key], i;
          if (!value) {
            return;
          }
          value = value.split(' ');
          i = value.length;
          while (i--) {
            if (value[i] !== value[0]) {
              return false;
            }
          }
          styles[key] = value[0];
          return true;
        };
        var compress2 = function (target, a, b, c) {
          if (!canCompress(a)) {
            return;
          }
          if (!canCompress(b)) {
            return;
          }
          if (!canCompress(c)) {
            return;
          }
          styles[target] = styles[a] + ' ' + styles[b] + ' ' + styles[c];
          delete styles[a];
          delete styles[b];
          delete styles[c];
        };
        var encode = function (str) {
          isEncoded = true;
          return encodingLookup[str];
        };
        var decode = function (str, keepSlashes) {
          if (isEncoded) {
            str = str.replace(/\uFEFF[0-9]/g, function (str) {
              return encodingLookup[str];
            });
          }
          if (!keepSlashes) {
            str = str.replace(/\\([\'\";:])/g, '$1');
          }
          return str;
        };
        var decodeSingleHexSequence = function (escSeq) {
          return String.fromCharCode(parseInt(escSeq.slice(1), 16));
        };
        var decodeHexSequences = function (value) {
          return value.replace(/\\[0-9a-f]+/gi, decodeSingleHexSequence);
        };
        var processUrl = function (match, url, url2, url3, str, str2) {
          str = str || str2;
          if (str) {
            str = decode(str);
            return '\'' + str.replace(/\'/g, '\\\'') + '\'';
          }
          url = decode(url || url2 || url3);
          if (!settings.allow_script_urls) {
            var scriptUrl = url.replace(/[\s\r\n]+/g, '');
            if (/(java|vb)script:/i.test(scriptUrl)) {
              return '';
            }
            if (!settings.allow_svg_data_urls && /^data:image\/svg/i.test(scriptUrl)) {
              return '';
            }
          }
          if (urlConverter) {
            url = urlConverter.call(urlConverterScope, url, 'style');
          }
          return 'url(\'' + url.replace(/\'/g, '\\\'') + '\')';
        };
        if (css) {
          css = css.replace(/[\u0000-\u001F]/g, '');
          css = css.replace(/\\[\"\';:\uFEFF]/g, encode).replace(/\"[^\"]+\"|\'[^\']+\'/g, function (str) {
            return str.replace(/[;:]/g, encode);
          });
          while (matches = styleRegExp.exec(css)) {
            styleRegExp.lastIndex = matches.index + matches[0].length;
            name = matches[1].replace(trimRightRegExp, '').toLowerCase();
            value = matches[2].replace(trimRightRegExp, '');
            if (name && value) {
              name = decodeHexSequences(name);
              value = decodeHexSequences(value);
              if (name.indexOf(invisibleChar) !== -1 || name.indexOf('"') !== -1) {
                continue;
              }
              if (!settings.allow_script_urls && (name === 'behavior' || /expression\s*\(|\/\*|\*\//.test(value))) {
                continue;
              }
              if (name === 'font-weight' && value === '700') {
                value = 'bold';
              } else if (name === 'color' || name === 'background-color') {
                value = value.toLowerCase();
              }
              value = value.replace(rgbRegExp, toHex);
              value = value.replace(urlOrStrRegExp, processUrl);
              styles[name] = isEncoded ? decode(value, true) : value;
            }
          }
          compress('border', '', true);
          compress('border', '-width');
          compress('border', '-color');
          compress('border', '-style');
          compress('padding', '');
          compress('margin', '');
          compress2('border', 'border-width', 'border-style', 'border-color');
          if (styles.border === 'medium none') {
            delete styles.border;
          }
          if (styles['border-image'] === 'none') {
            delete styles['border-image'];
          }
        }
        return styles;
      },
      serialize: function (styles, elementName) {
        var css = '', name, value;
        var serializeStyles = function (name) {
          var styleList, i, l, value;
          styleList = validStyles[name];
          if (styleList) {
            for (i = 0, l = styleList.length; i < l; i++) {
              name = styleList[i];
              value = styles[name];
              if (value) {
                css += (css.length > 0 ? ' ' : '') + name + ': ' + value + ';';
              }
            }
          }
        };
        var isValid = function (name, elementName) {
          var styleMap;
          styleMap = invalidStyles['*'];
          if (styleMap && styleMap[name]) {
            return false;
          }
          styleMap = invalidStyles[elementName];
          if (styleMap && styleMap[name]) {
            return false;
          }
          return true;
        };
        if (elementName && validStyles) {
          serializeStyles('*');
          serializeStyles(elementName);
        } else {
          for (name in styles) {
            value = styles[name];
            if (value && (!invalidStyles || isValid(name, elementName))) {
              css += (css.length > 0 ? ' ' : '') + name + ': ' + value + ';';
            }
          }
        }
        return css;
      }
    };
  }

  var each$5 = $_a5gegtjje5o2pum.each;
  var is$2 = $_a5gegtjje5o2pum.is;
  var grep$1 = $_a5gegtjje5o2pum.grep;
  var isIE = $_9bd8pn9je5o2pp2.ie;
  var simpleSelectorRe = /^([a-z0-9],?)+$/i;
  var whiteSpaceRegExp$2 = /^[ \t\r\n]*$/;
  var setupAttrHooks = function (domUtils, settings) {
    var attrHooks = {};
    var keepValues = settings.keep_values;
    var keepUrlHook;
    keepUrlHook = {
      set: function ($elm, value, name) {
        if (settings.url_converter) {
          value = settings.url_converter.call(settings.url_converter_scope || domUtils, value, name, $elm[0]);
        }
        $elm.attr('data-mce-' + name, value).attr(name, value);
      },
      get: function ($elm, name) {
        return $elm.attr('data-mce-' + name) || $elm.attr(name);
      }
    };
    attrHooks = {
      style: {
        set: function ($elm, value) {
          if (value !== null && typeof value === 'object') {
            $elm.css(value);
            return;
          }
          if (keepValues) {
            $elm.attr('data-mce-style', value);
          }
          $elm.attr('style', value);
        },
        get: function ($elm) {
          var value = $elm.attr('data-mce-style') || $elm.attr('style');
          value = domUtils.serializeStyle(domUtils.parseStyle(value), $elm[0].nodeName);
          return value;
        }
      }
    };
    if (keepValues) {
      attrHooks.href = attrHooks.src = keepUrlHook;
    }
    return attrHooks;
  };
  var updateInternalStyleAttr = function (domUtils, $elm) {
    var value = $elm.attr('style');
    value = domUtils.serializeStyle(domUtils.parseStyle(value), $elm[0].nodeName);
    if (!value) {
      value = null;
    }
    $elm.attr('data-mce-style', value);
  };
  var nodeIndex = function (node, normalized) {
    var idx = 0, lastNodeType, nodeType;
    if (node) {
      for (lastNodeType = node.nodeType, node = node.previousSibling; node; node = node.previousSibling) {
        nodeType = node.nodeType;
        if (normalized && nodeType === 3) {
          if (nodeType === lastNodeType || !node.nodeValue.length) {
            continue;
          }
        }
        idx++;
        lastNodeType = nodeType;
      }
    }
    return idx;
  };
  var DOMUtils = function (doc, settings) {
    var self = this;
    var blockElementsMap;
    self.doc = doc;
    self.win = window;
    self.files = {};
    self.counter = 0;
    self.stdMode = !isIE || doc.documentMode >= 8;
    self.boxModel = !isIE || doc.compatMode === 'CSS1Compat' || self.stdMode;
    self.styleSheetLoader = StyleSheetLoader(doc);
    self.boundEvents = [];
    self.settings = settings = settings || {};
    self.schema = settings.schema ? settings.schema : Schema({});
    self.styles = Styles({
      url_converter: settings.url_converter,
      url_converter_scope: settings.url_converter_scope
    }, settings.schema);
    self.fixDoc(doc);
    self.events = settings.ownEvents ? new EventUtils(settings.proxy) : EventUtils.Event;
    self.attrHooks = setupAttrHooks(self, settings);
    blockElementsMap = self.schema.getBlockElements();
    self.$ = DomQuery.overrideDefaults(function () {
      return {
        context: doc,
        element: self.getRoot()
      };
    });
    self.isBlock = function (node) {
      if (!node) {
        return false;
      }
      var type = node.nodeType;
      if (type) {
        return !!(type === 1 && blockElementsMap[node.nodeName]);
      }
      return !!blockElementsMap[node];
    };
  };
  DOMUtils.prototype = {
    $$: function (elm) {
      if (typeof elm === 'string') {
        elm = this.get(elm);
      }
      return this.$(elm);
    },
    root: null,
    fixDoc: function (doc) {
    },
    clone: function (node, deep) {
      var self = this;
      var clone, doc;
      if (!isIE || node.nodeType !== 1 || deep) {
        return node.cloneNode(deep);
      }
      doc = self.doc;
      if (!deep) {
        clone = doc.createElement(node.nodeName);
        each$5(self.getAttribs(node), function (attr) {
          self.setAttrib(clone, attr.nodeName, self.getAttrib(node, attr.nodeName));
        });
        return clone;
      }
      return clone.firstChild;
    },
    getRoot: function () {
      var self = this;
      return self.settings.root_element || self.doc.body;
    },
    getViewPort: function (win) {
      var doc, rootElm;
      win = !win ? this.win : win;
      doc = win.document;
      rootElm = this.boxModel ? doc.documentElement : doc.body;
      return {
        x: win.pageXOffset || rootElm.scrollLeft,
        y: win.pageYOffset || rootElm.scrollTop,
        w: win.innerWidth || rootElm.clientWidth,
        h: win.innerHeight || rootElm.clientHeight
      };
    },
    getRect: function (elm) {
      var self = this;
      var pos, size;
      elm = self.get(elm);
      pos = self.getPos(elm);
      size = self.getSize(elm);
      return {
        x: pos.x,
        y: pos.y,
        w: size.w,
        h: size.h
      };
    },
    getSize: function (elm) {
      var self = this;
      var w, h;
      elm = self.get(elm);
      w = self.getStyle(elm, 'width');
      h = self.getStyle(elm, 'height');
      if (w.indexOf('px') === -1) {
        w = 0;
      }
      if (h.indexOf('px') === -1) {
        h = 0;
      }
      return {
        w: parseInt(w, 10) || elm.offsetWidth || elm.clientWidth,
        h: parseInt(h, 10) || elm.offsetHeight || elm.clientHeight
      };
    },
    getParent: function (node, selector, root) {
      return this.getParents(node, selector, root, false);
    },
    getParents: function (node, selector, root, collect) {
      var self = this;
      var selectorVal;
      var result = [];
      node = self.get(node);
      collect = collect === undefined;
      root = root || (self.getRoot().nodeName !== 'BODY' ? self.getRoot().parentNode : null);
      if (is$2(selector, 'string')) {
        selectorVal = selector;
        if (selector === '*') {
          selector = function (node) {
            return node.nodeType === 1;
          };
        } else {
          selector = function (node) {
            return self.is(node, selectorVal);
          };
        }
      }
      while (node) {
        if (node === root || !node.nodeType || node.nodeType === 9) {
          break;
        }
        if (!selector || selector(node)) {
          if (collect) {
            result.push(node);
          } else {
            return node;
          }
        }
        node = node.parentNode;
      }
      return collect ? result : null;
    },
    get: function (elm) {
      var name;
      if (elm && this.doc && typeof elm === 'string') {
        name = elm;
        elm = this.doc.getElementById(elm);
        if (elm && elm.id !== name) {
          return this.doc.getElementsByName(name)[1];
        }
      }
      return elm;
    },
    getNext: function (node, selector) {
      return this._findSib(node, selector, 'nextSibling');
    },
    getPrev: function (node, selector) {
      return this._findSib(node, selector, 'previousSibling');
    },
    select: function (selector, scope) {
      var self = this;
      return Sizzle(selector, self.get(scope) || self.settings.root_element || self.doc, []);
    },
    is: function (elm, selector) {
      var i;
      if (!elm) {
        return false;
      }
      if (elm.length === undefined) {
        if (selector === '*') {
          return elm.nodeType === 1;
        }
        if (simpleSelectorRe.test(selector)) {
          selector = selector.toLowerCase().split(/,/);
          elm = elm.nodeName.toLowerCase();
          for (i = selector.length - 1; i >= 0; i--) {
            if (selector[i] === elm) {
              return true;
            }
          }
          return false;
        }
      }
      if (elm.nodeType && elm.nodeType !== 1) {
        return false;
      }
      var elms = elm.nodeType ? [elm] : elm;
      return Sizzle(selector, elms[0].ownerDocument || elms[0], null, elms).length > 0;
    },
    add: function (parentElm, name, attrs, html, create) {
      var self = this;
      return this.run(parentElm, function (parentElm) {
        var newElm;
        newElm = is$2(name, 'string') ? self.doc.createElement(name) : name;
        self.setAttribs(newElm, attrs);
        if (html) {
          if (html.nodeType) {
            newElm.appendChild(html);
          } else {
            self.setHTML(newElm, html);
          }
        }
        return !create ? parentElm.appendChild(newElm) : newElm;
      });
    },
    create: function (name, attrs, html) {
      return this.add(this.doc.createElement(name), name, attrs, html, 1);
    },
    createHTML: function (name, attrs, html) {
      var outHtml = '', key;
      outHtml += '<' + name;
      for (key in attrs) {
        if (attrs.hasOwnProperty(key) && attrs[key] !== null && typeof attrs[key] !== 'undefined') {
          outHtml += ' ' + key + '="' + this.encode(attrs[key]) + '"';
        }
      }
      if (typeof html !== 'undefined') {
        return outHtml + '>' + html + '</' + name + '>';
      }
      return outHtml + ' />';
    },
    createFragment: function (html) {
      var frag, node;
      var doc = this.doc;
      var container;
      container = doc.createElement('div');
      frag = doc.createDocumentFragment();
      if (html) {
        container.innerHTML = html;
      }
      while (node = container.firstChild) {
        frag.appendChild(node);
      }
      return frag;
    },
    remove: function (node, keepChildren) {
      node = this.$$(node);
      if (keepChildren) {
        node.each(function () {
          var child;
          while (child = this.firstChild) {
            if (child.nodeType === 3 && child.data.length === 0) {
              this.removeChild(child);
            } else {
              this.parentNode.insertBefore(child, this);
            }
          }
        }).remove();
      } else {
        node.remove();
      }
      return node.length > 1 ? node.toArray() : node[0];
    },
    setStyle: function (elm, name, value) {
      elm = this.$$(elm).css(name, value);
      if (this.settings.update_styles) {
        updateInternalStyleAttr(this, elm);
      }
    },
    getStyle: function (elm, name, computed) {
      elm = this.$$(elm);
      if (computed) {
        return elm.css(name);
      }
      name = name.replace(/-(\D)/g, function (a, b) {
        return b.toUpperCase();
      });
      if (name === 'float') {
        name = $_9bd8pn9je5o2pp2.ie && $_9bd8pn9je5o2pp2.ie < 12 ? 'styleFloat' : 'cssFloat';
      }
      return elm[0] && elm[0].style ? elm[0].style[name] : undefined;
    },
    setStyles: function (elm, styles) {
      elm = this.$$(elm).css(styles);
      if (this.settings.update_styles) {
        updateInternalStyleAttr(this, elm);
      }
    },
    removeAllAttribs: function (e) {
      return this.run(e, function (e) {
        var i;
        var attrs = e.attributes;
        for (i = attrs.length - 1; i >= 0; i--) {
          e.removeAttributeNode(attrs.item(i));
        }
      });
    },
    setAttrib: function (elm, name, value) {
      var self = this;
      var originalValue, hook;
      var settings = self.settings;
      if (value === '') {
        value = null;
      }
      elm = self.$$(elm);
      originalValue = elm.attr(name);
      if (!elm.length) {
        return;
      }
      hook = self.attrHooks[name];
      if (hook && hook.set) {
        hook.set(elm, value, name);
      } else {
        elm.attr(name, value);
      }
      if (originalValue !== value && settings.onSetAttrib) {
        settings.onSetAttrib({
          attrElm: elm,
          attrName: name,
          attrValue: value
        });
      }
    },
    setAttribs: function (elm, attrs) {
      var self = this;
      self.$$(elm).each(function (i, node) {
        each$5(attrs, function (value, name) {
          self.setAttrib(node, name, value);
        });
      });
    },
    getAttrib: function (elm, name, defaultVal) {
      var self = this;
      var hook, value;
      elm = self.$$(elm);
      if (elm.length) {
        hook = self.attrHooks[name];
        if (hook && hook.get) {
          value = hook.get(elm, name);
        } else {
          value = elm.attr(name);
        }
      }
      if (typeof value === 'undefined') {
        value = defaultVal || '';
      }
      return value;
    },
    getPos: function (elm, rootElm) {
      return $_5uqvv2lje5o2pvj.getPos(this.doc.body, this.get(elm), rootElm);
    },
    parseStyle: function (cssText) {
      return this.styles.parse(cssText);
    },
    serializeStyle: function (styles, name) {
      return this.styles.serialize(styles, name);
    },
    addStyle: function (cssText) {
      var self = this;
      var doc = self.doc;
      var head, styleElm;
      if (self !== DOMUtils.DOM && doc === document) {
        var addedStyles = DOMUtils.DOM.addedStyles;
        addedStyles = addedStyles || [];
        if (addedStyles[cssText]) {
          return;
        }
        addedStyles[cssText] = true;
        DOMUtils.DOM.addedStyles = addedStyles;
      }
      styleElm = doc.getElementById('mceDefaultStyles');
      if (!styleElm) {
        styleElm = doc.createElement('style');
        styleElm.id = 'mceDefaultStyles';
        styleElm.type = 'text/css';
        head = doc.getElementsByTagName('head')[0];
        if (head.firstChild) {
          head.insertBefore(styleElm, head.firstChild);
        } else {
          head.appendChild(styleElm);
        }
      }
      if (styleElm.styleSheet) {
        styleElm.styleSheet.cssText += cssText;
      } else {
        styleElm.appendChild(doc.createTextNode(cssText));
      }
    },
    loadCSS: function (url) {
      var self = this;
      var doc = self.doc;
      var head;
      if (self !== DOMUtils.DOM && doc === document) {
        DOMUtils.DOM.loadCSS(url);
        return;
      }
      if (!url) {
        url = '';
      }
      head = doc.getElementsByTagName('head')[0];
      each$5(url.split(','), function (url) {
        var link;
        url = $_a5gegtjje5o2pum._addCacheSuffix(url);
        if (self.files[url]) {
          return;
        }
        self.files[url] = true;
        link = self.create('link', {
          rel: 'stylesheet',
          href: url
        });
        if (isIE && doc.documentMode && doc.recalc) {
          link.onload = function () {
            if (doc.recalc) {
              doc.recalc();
            }
            link.onload = null;
          };
        }
        head.appendChild(link);
      });
    },
    addClass: function (elm, cls) {
      this.$$(elm).addClass(cls);
    },
    removeClass: function (elm, cls) {
      this.toggleClass(elm, cls, false);
    },
    hasClass: function (elm, cls) {
      return this.$$(elm).hasClass(cls);
    },
    toggleClass: function (elm, cls, state) {
      this.$$(elm).toggleClass(cls, state).each(function () {
        if (this.className === '') {
          DomQuery(this).attr('class', null);
        }
      });
    },
    show: function (elm) {
      this.$$(elm).show();
    },
    hide: function (elm) {
      this.$$(elm).hide();
    },
    isHidden: function (elm) {
      return this.$$(elm).css('display') === 'none';
    },
    uniqueId: function (prefix) {
      return (!prefix ? 'mce_' : prefix) + this.counter++;
    },
    setHTML: function (elm, html) {
      elm = this.$$(elm);
      if (isIE) {
        elm.each(function (i, target) {
          if (target.canHaveHTML === false) {
            return;
          }
          while (target.firstChild) {
            target.removeChild(target.firstChild);
          }
          try {
            target.innerHTML = '<br>' + html;
            target.removeChild(target.firstChild);
          } catch (ex) {
            DomQuery('<div></div>').html('<br>' + html).contents().slice(1).appendTo(target);
          }
          return html;
        });
      } else {
        elm.html(html);
      }
    },
    getOuterHTML: function (elm) {
      elm = this.get(elm);
      return elm.nodeType === 1 && 'outerHTML' in elm ? elm.outerHTML : DomQuery('<div></div>').append(DomQuery(elm).clone()).html();
    },
    setOuterHTML: function (elm, html) {
      var self = this;
      self.$$(elm).each(function () {
        try {
          if ('outerHTML' in this) {
            this.outerHTML = html;
            return;
          }
        } catch (ex) {
        }
        self.remove(DomQuery(this).html(html), true);
      });
    },
    decode: $_fzh4ld1rje5o2q0t.decode,
    encode: $_fzh4ld1rje5o2q0t.encodeAllRaw,
    insertAfter: function (node, referenceNode) {
      referenceNode = this.get(referenceNode);
      return this.run(node, function (node) {
        var parent, nextSibling;
        parent = referenceNode.parentNode;
        nextSibling = referenceNode.nextSibling;
        if (nextSibling) {
          parent.insertBefore(node, nextSibling);
        } else {
          parent.appendChild(node);
        }
        return node;
      });
    },
    replace: function (newElm, oldElm, keepChildren) {
      var self = this;
      return self.run(oldElm, function (oldElm) {
        if (is$2(oldElm, 'array')) {
          newElm = newElm.cloneNode(true);
        }
        if (keepChildren) {
          each$5(grep$1(oldElm.childNodes), function (node) {
            newElm.appendChild(node);
          });
        }
        return oldElm.parentNode.replaceChild(newElm, oldElm);
      });
    },
    rename: function (elm, name) {
      var self = this;
      var newElm;
      if (elm.nodeName !== name.toUpperCase()) {
        newElm = self.create(name);
        each$5(self.getAttribs(elm), function (attrNode) {
          self.setAttrib(newElm, attrNode.nodeName, self.getAttrib(elm, attrNode.nodeName));
        });
        self.replace(newElm, elm, 1);
      }
      return newElm || elm;
    },
    findCommonAncestor: function (a, b) {
      var ps = a, pe;
      while (ps) {
        pe = b;
        while (pe && ps !== pe) {
          pe = pe.parentNode;
        }
        if (ps === pe) {
          break;
        }
        ps = ps.parentNode;
      }
      if (!ps && a.ownerDocument) {
        return a.ownerDocument.documentElement;
      }
      return ps;
    },
    toHex: function (rgbVal) {
      return this.styles.toHex($_a5gegtjje5o2pum.trim(rgbVal));
    },
    run: function (elm, func, scope) {
      var self = this;
      var result;
      if (typeof elm === 'string') {
        elm = self.get(elm);
      }
      if (!elm) {
        return false;
      }
      scope = scope || this;
      if (!elm.nodeType && (elm.length || elm.length === 0)) {
        result = [];
        each$5(elm, function (elm, i) {
          if (elm) {
            if (typeof elm === 'string') {
              elm = self.get(elm);
            }
            result.push(func.call(scope, elm, i));
          }
        });
        return result;
      }
      return func.call(scope, elm);
    },
    getAttribs: function (elm) {
      var attrs;
      elm = this.get(elm);
      if (!elm) {
        return [];
      }
      if (isIE) {
        attrs = [];
        if (elm.nodeName === 'OBJECT') {
          return elm.attributes;
        }
        if (elm.nodeName === 'OPTION' && this.getAttrib(elm, 'selected')) {
          attrs.push({
            specified: 1,
            nodeName: 'selected'
          });
        }
        var attrRegExp = /<\/?[\w:\-]+ ?|=[\"][^\"]+\"|=\'[^\']+\'|=[\w\-]+|>/gi;
        elm.cloneNode(false).outerHTML.replace(attrRegExp, '').replace(/[\w:\-]+/gi, function (a) {
          attrs.push({
            specified: 1,
            nodeName: a
          });
        });
        return attrs;
      }
      return elm.attributes;
    },
    isEmpty: function (node, elements) {
      var self = this;
      var i, attributes, type, whitespace, walker, name, brCount = 0;
      node = node.firstChild;
      if (node) {
        walker = new TreeWalker(node, node.parentNode);
        elements = elements || (self.schema ? self.schema.getNonEmptyElements() : null);
        whitespace = self.schema ? self.schema.getWhiteSpaceElements() : {};
        do {
          type = node.nodeType;
          if (type === 1) {
            var bogusVal = node.getAttribute('data-mce-bogus');
            if (bogusVal) {
              node = walker.next(bogusVal === 'all');
              continue;
            }
            name = node.nodeName.toLowerCase();
            if (elements && elements[name]) {
              if (name === 'br') {
                brCount++;
                node = walker.next();
                continue;
              }
              return false;
            }
            attributes = self.getAttribs(node);
            i = attributes.length;
            while (i--) {
              name = attributes[i].nodeName;
              if (name === 'name' || name === 'data-mce-bookmark') {
                return false;
              }
            }
          }
          if (type === 8) {
            return false;
          }
          if (type === 3 && !whiteSpaceRegExp$2.test(node.nodeValue)) {
            return false;
          }
          if (type === 3 && node.parentNode && whitespace[node.parentNode.nodeName] && whiteSpaceRegExp$2.test(node.nodeValue)) {
            return false;
          }
          node = walker.next();
        } while (node);
      }
      return brCount <= 1;
    },
    createRng: function () {
      return this.doc.createRange();
    },
    nodeIndex: nodeIndex,
    split: function (parentElm, splitElm, replacementElm) {
      var self = this;
      var r = self.createRng(), bef, aft, pa;
      if (parentElm && splitElm) {
        r.setStart(parentElm.parentNode, self.nodeIndex(parentElm));
        r.setEnd(splitElm.parentNode, self.nodeIndex(splitElm));
        bef = r.extractContents();
        r = self.createRng();
        r.setStart(splitElm.parentNode, self.nodeIndex(splitElm) + 1);
        r.setEnd(parentElm.parentNode, self.nodeIndex(parentElm) + 1);
        aft = r.extractContents();
        pa = parentElm.parentNode;
        pa.insertBefore($_cfe5l31oje5o2q05.trimNode(self, bef), parentElm);
        if (replacementElm) {
          pa.insertBefore(replacementElm, parentElm);
        } else {
          pa.insertBefore(splitElm, parentElm);
        }
        pa.insertBefore($_cfe5l31oje5o2q05.trimNode(self, aft), parentElm);
        self.remove(parentElm);
        return replacementElm || splitElm;
      }
    },
    bind: function (target, name, func, scope) {
      var self = this;
      if ($_a5gegtjje5o2pum.isArray(target)) {
        var i = target.length;
        while (i--) {
          target[i] = self.bind(target[i], name, func, scope);
        }
        return target;
      }
      if (self.settings.collect && (target === self.doc || target === self.win)) {
        self.boundEvents.push([
          target,
          name,
          func,
          scope
        ]);
      }
      return self.events.bind(target, name, func, scope || self);
    },
    unbind: function (target, name, func) {
      var self = this;
      var i;
      if ($_a5gegtjje5o2pum.isArray(target)) {
        i = target.length;
        while (i--) {
          target[i] = self.unbind(target[i], name, func);
        }
        return target;
      }
      if (self.boundEvents && (target === self.doc || target === self.win)) {
        i = self.boundEvents.length;
        while (i--) {
          var item = self.boundEvents[i];
          if (target === item[0] && (!name || name === item[1]) && (!func || func === item[2])) {
            this.events.unbind(item[0], item[1], item[2]);
          }
        }
      }
      return this.events.unbind(target, name, func);
    },
    fire: function (target, name, evt) {
      return this.events.fire(target, name, evt);
    },
    getContentEditable: function (node) {
      var contentEditable;
      if (!node || node.nodeType !== 1) {
        return null;
      }
      contentEditable = node.getAttribute('data-mce-contenteditable');
      if (contentEditable && contentEditable !== 'inherit') {
        return contentEditable;
      }
      return node.contentEditable !== 'inherit' ? node.contentEditable : null;
    },
    getContentEditableParent: function (node) {
      var root = this.getRoot();
      var state = null;
      for (; node && node !== root; node = node.parentNode) {
        state = this.getContentEditable(node);
        if (state !== null) {
          break;
        }
      }
      return state;
    },
    destroy: function () {
      var self = this;
      if (self.boundEvents) {
        var i = self.boundEvents.length;
        while (i--) {
          var item = self.boundEvents[i];
          this.events.unbind(item[0], item[1], item[2]);
        }
        self.boundEvents = null;
      }
      if (Sizzle.setDocument) {
        Sizzle.setDocument();
      }
      self.win = self.doc = self.root = self.events = self.frag = null;
    },
    isChildOf: function (node, parent) {
      while (node) {
        if (parent === node) {
          return true;
        }
        node = node.parentNode;
      }
      return false;
    },
    dumpRng: function (r) {
      return 'startContainer: ' + r.startContainer.nodeName + ', startOffset: ' + r.startOffset + ', endContainer: ' + r.endContainer.nodeName + ', endOffset: ' + r.endOffset;
    },
    _findSib: function (node, selector, name) {
      var self = this;
      var func = selector;
      if (node) {
        if (typeof func === 'string') {
          func = function (node) {
            return self.is(node, selector);
          };
        }
        for (node = node[name]; node; node = node[name]) {
          if (func(node)) {
            return node;
          }
        }
      }
      return null;
    }
  };
  DOMUtils.DOM = new DOMUtils(document);
  DOMUtils.nodeIndex = nodeIndex;

  var DOM = DOMUtils.DOM;
  var each$6 = $_a5gegtjje5o2pum.each;
  var grep$2 = $_a5gegtjje5o2pum.grep;
  var isFunction = function (f) {
    return typeof f === 'function';
  };
  var ScriptLoader = function () {
    var QUEUED = 0;
    var LOADING = 1;
    var LOADED = 2;
    var FAILED = 3;
    var states = {};
    var queue = [];
    var scriptLoadedCallbacks = {};
    var queueLoadedCallbacks = [];
    var loading = 0;
    var loadScript = function (url, success, failure) {
      var dom = DOM;
      var elm, id;
      var done = function () {
        dom.remove(id);
        if (elm) {
          elm.onreadystatechange = elm.onload = elm = null;
        }
        success();
      };
      var error = function () {
        if (isFunction(failure)) {
          failure();
        } else {
          if (typeof console !== 'undefined' && console.log) {
            console.log('Failed to load script: ' + url);
          }
        }
      };
      id = dom.uniqueId();
      elm = document.createElement('script');
      elm.id = id;
      elm.type = 'text/javascript';
      elm.src = $_a5gegtjje5o2pum._addCacheSuffix(url);
      if ('onreadystatechange' in elm) {
        elm.onreadystatechange = function () {
          if (/loaded|complete/.test(elm.readyState)) {
            done();
          }
        };
      } else {
        elm.onload = done;
      }
      elm.onerror = error;
      (document.getElementsByTagName('head')[0] || document.body).appendChild(elm);
    };
    this.isDone = function (url) {
      return states[url] === LOADED;
    };
    this.markDone = function (url) {
      states[url] = LOADED;
    };
    this.add = this.load = function (url, success, scope, failure) {
      var state = states[url];
      if (state === undefined) {
        queue.push(url);
        states[url] = QUEUED;
      }
      if (success) {
        if (!scriptLoadedCallbacks[url]) {
          scriptLoadedCallbacks[url] = [];
        }
        scriptLoadedCallbacks[url].push({
          success: success,
          failure: failure,
          scope: scope || this
        });
      }
    };
    this.remove = function (url) {
      delete states[url];
      delete scriptLoadedCallbacks[url];
    };
    this.loadQueue = function (success, scope, failure) {
      this.loadScripts(queue, success, scope, failure);
    };
    this.loadScripts = function (scripts, success, scope, failure) {
      var loadScripts;
      var failures = [];
      var execCallbacks = function (name, url) {
        each$6(scriptLoadedCallbacks[url], function (callback) {
          if (isFunction(callback[name])) {
            callback[name].call(callback.scope);
          }
        });
        scriptLoadedCallbacks[url] = undefined;
      };
      queueLoadedCallbacks.push({
        success: success,
        failure: failure,
        scope: scope || this
      });
      loadScripts = function () {
        var loadingScripts = grep$2(scripts);
        scripts.length = 0;
        each$6(loadingScripts, function (url) {
          if (states[url] === LOADED) {
            execCallbacks('success', url);
            return;
          }
          if (states[url] === FAILED) {
            execCallbacks('failure', url);
            return;
          }
          if (states[url] !== LOADING) {
            states[url] = LOADING;
            loading++;
            loadScript(url, function () {
              states[url] = LOADED;
              loading--;
              execCallbacks('success', url);
              loadScripts();
            }, function () {
              states[url] = FAILED;
              loading--;
              failures.push(url);
              execCallbacks('failure', url);
              loadScripts();
            });
          }
        });
        if (!loading) {
          var notifyCallbacks = queueLoadedCallbacks.slice(0);
          queueLoadedCallbacks.length = 0;
          each$6(notifyCallbacks, function (callback) {
            if (failures.length === 0) {
              if (isFunction(callback.success)) {
                callback.success.call(callback.scope);
              }
            } else {
              if (isFunction(callback.failure)) {
                callback.failure.call(callback.scope, failures);
              }
            }
          });
        }
      };
      loadScripts();
    };
  };
  ScriptLoader.ScriptLoader = new ScriptLoader();

  var each$7 = $_a5gegtjje5o2pum.each;
  var AddOnManager = function () {
    var self = this;
    self.items = [];
    self.urls = {};
    self.lookup = {};
    self._listeners = [];
  };
  AddOnManager.prototype = {
    get: function (name) {
      if (this.lookup[name]) {
        return this.lookup[name].instance;
      }
      return undefined;
    },
    dependencies: function (name) {
      var result;
      if (this.lookup[name]) {
        result = this.lookup[name].dependencies;
      }
      return result || [];
    },
    requireLangPack: function (name, languages) {
      var language = AddOnManager.language;
      if (language && AddOnManager.languageLoad !== false) {
        if (languages) {
          languages = ',' + languages + ',';
          if (languages.indexOf(',' + language.substr(0, 2) + ',') !== -1) {
            language = language.substr(0, 2);
          } else if (languages.indexOf(',' + language + ',') === -1) {
            return;
          }
        }
        ScriptLoader.ScriptLoader.add(this.urls[name] + '/langs/' + language + '.js');
      }
    },
    add: function (id, addOn, dependencies) {
      this.items.push(addOn);
      this.lookup[id] = {
        instance: addOn,
        dependencies: dependencies
      };
      var result = $_bwcteb4je5o2pl4.partition(this._listeners, function (listener) {
        return listener.name === id;
      });
      this._listeners = result.fail;
      each$7(result.pass, function (listener) {
        listener.callback();
      });
      return addOn;
    },
    remove: function (name) {
      delete this.urls[name];
      delete this.lookup[name];
    },
    createUrl: function (baseUrl, dep) {
      if (typeof dep === 'object') {
        return dep;
      }
      return {
        prefix: baseUrl.prefix,
        resource: dep,
        suffix: baseUrl.suffix
      };
    },
    addComponents: function (pluginName, scripts) {
      var pluginUrl = this.urls[pluginName];
      each$7(scripts, function (script) {
        ScriptLoader.ScriptLoader.add(pluginUrl + '/' + script);
      });
    },
    load: function (name, addOnUrl, success, scope, failure) {
      var self = this;
      var url = addOnUrl;
      var loadDependencies = function () {
        var dependencies = self.dependencies(name);
        each$7(dependencies, function (dep) {
          var newUrl = self.createUrl(addOnUrl, dep);
          self.load(newUrl.resource, newUrl, undefined, undefined);
        });
        if (success) {
          if (scope) {
            success.call(scope);
          } else {
            success.call(ScriptLoader);
          }
        }
      };
      if (self.urls[name]) {
        return;
      }
      if (typeof addOnUrl === 'object') {
        url = addOnUrl.prefix + addOnUrl.resource + addOnUrl.suffix;
      }
      if (url.indexOf('/') !== 0 && url.indexOf('://') === -1) {
        url = AddOnManager.baseURL + '/' + url;
      }
      self.urls[name] = url.substring(0, url.lastIndexOf('/'));
      if (self.lookup[name]) {
        loadDependencies();
      } else {
        ScriptLoader.ScriptLoader.add(url, loadDependencies, scope, failure);
      }
    },
    waitFor: function (name, callback) {
      if (this.lookup.hasOwnProperty(name)) {
        callback();
      } else {
        this._listeners.push({
          name: name,
          callback: callback
        });
      }
    }
  };
  AddOnManager.PluginManager = new AddOnManager();
  AddOnManager.ThemeManager = new AddOnManager();

  var ZWSP = '\uFEFF';
  var isZwsp = function (chr) {
    return chr === ZWSP;
  };
  var trim$3 = function (text) {
    return text.replace(new RegExp(ZWSP, 'g'), '');
  };
  var $_4md31c21je5o2q5f = {
    isZwsp: isZwsp,
    ZWSP: ZWSP,
    trim: trim$3
  };

  var isElement$2 = $_9owqcy1qje5o2q0e.isElement;
  var isText$2 = $_9owqcy1qje5o2q0e.isText;
  var isCaretContainerBlock = function (node) {
    if (isText$2(node)) {
      node = node.parentNode;
    }
    return isElement$2(node) && node.hasAttribute('data-mce-caret');
  };
  var isCaretContainerInline = function (node) {
    return isText$2(node) && $_4md31c21je5o2q5f.isZwsp(node.data);
  };
  var isCaretContainer = function (node) {
    return isCaretContainerBlock(node) || isCaretContainerInline(node);
  };
  var hasContent = function (node) {
    return node.firstChild !== node.lastChild || !$_9owqcy1qje5o2q0e.isBr(node.firstChild);
  };
  var insertInline = function (node, before) {
    var doc, sibling, textNode, parentNode;
    doc = node.ownerDocument;
    textNode = doc.createTextNode($_4md31c21je5o2q5f.ZWSP);
    parentNode = node.parentNode;
    if (!before) {
      sibling = node.nextSibling;
      if (isText$2(sibling)) {
        if (isCaretContainer(sibling)) {
          return sibling;
        }
        if (startsWithCaretContainer(sibling)) {
          sibling.splitText(1);
          return sibling;
        }
      }
      if (node.nextSibling) {
        parentNode.insertBefore(textNode, node.nextSibling);
      } else {
        parentNode.appendChild(textNode);
      }
    } else {
      sibling = node.previousSibling;
      if (isText$2(sibling)) {
        if (isCaretContainer(sibling)) {
          return sibling;
        }
        if (endsWithCaretContainer(sibling)) {
          return sibling.splitText(sibling.data.length - 1);
        }
      }
      parentNode.insertBefore(textNode, node);
    }
    return textNode;
  };
  var isBeforeInline = function (pos) {
    var container = pos.container();
    return pos && $_9owqcy1qje5o2q0e.isText(container) && container.data.charAt(pos.offset()) === $_4md31c21je5o2q5f.ZWSP;
  };
  var isAfterInline = function (pos) {
    var container = pos.container();
    return pos && $_9owqcy1qje5o2q0e.isText(container) && container.data.charAt(pos.offset() - 1) === $_4md31c21je5o2q5f.ZWSP;
  };
  var createBogusBr = function () {
    var br = document.createElement('br');
    br.setAttribute('data-mce-bogus', '1');
    return br;
  };
  var insertBlock = function (blockName, node, before) {
    var doc, blockNode, parentNode;
    doc = node.ownerDocument;
    blockNode = doc.createElement(blockName);
    blockNode.setAttribute('data-mce-caret', before ? 'before' : 'after');
    blockNode.setAttribute('data-mce-bogus', 'all');
    blockNode.appendChild(createBogusBr());
    parentNode = node.parentNode;
    if (!before) {
      if (node.nextSibling) {
        parentNode.insertBefore(blockNode, node.nextSibling);
      } else {
        parentNode.appendChild(blockNode);
      }
    } else {
      parentNode.insertBefore(blockNode, node);
    }
    return blockNode;
  };
  var startsWithCaretContainer = function (node) {
    return isText$2(node) && node.data[0] === $_4md31c21je5o2q5f.ZWSP;
  };
  var endsWithCaretContainer = function (node) {
    return isText$2(node) && node.data[node.data.length - 1] === $_4md31c21je5o2q5f.ZWSP;
  };
  var trimBogusBr = function (elm) {
    var brs = elm.getElementsByTagName('br');
    var lastBr = brs[brs.length - 1];
    if ($_9owqcy1qje5o2q0e.isBogus(lastBr)) {
      lastBr.parentNode.removeChild(lastBr);
    }
  };
  var showCaretContainerBlock = function (caretContainer) {
    if (caretContainer && caretContainer.hasAttribute('data-mce-caret')) {
      trimBogusBr(caretContainer);
      caretContainer.removeAttribute('data-mce-caret');
      caretContainer.removeAttribute('data-mce-bogus');
      caretContainer.removeAttribute('style');
      caretContainer.removeAttribute('_moz_abspos');
      return caretContainer;
    }
    return null;
  };
  var isRangeInCaretContainerBlock = function (range) {
    return isCaretContainerBlock(range.startContainer);
  };

  var isContentEditableTrue$1 = $_9owqcy1qje5o2q0e.isContentEditableTrue;
  var isContentEditableFalse$1 = $_9owqcy1qje5o2q0e.isContentEditableFalse;
  var isBr$2 = $_9owqcy1qje5o2q0e.isBr;
  var isText$3 = $_9owqcy1qje5o2q0e.isText;
  var isInvalidTextElement = $_9owqcy1qje5o2q0e.matchNodeNames('script style textarea');
  var isAtomicInline = $_9owqcy1qje5o2q0e.matchNodeNames('img input textarea hr iframe video audio object');
  var isTable$1 = $_9owqcy1qje5o2q0e.matchNodeNames('table');
  var isCaretContainer$1 = isCaretContainer;
  var isCaretCandidate = function (node) {
    if (isCaretContainer$1(node)) {
      return false;
    }
    if (isText$3(node)) {
      if (isInvalidTextElement(node.parentNode)) {
        return false;
      }
      return true;
    }
    return isAtomicInline(node) || isBr$2(node) || isTable$1(node) || isContentEditableFalse$1(node);
  };
  var isInEditable = function (node, root) {
    for (node = node.parentNode; node && node !== root; node = node.parentNode) {
      if (isContentEditableFalse$1(node)) {
        return false;
      }
      if (isContentEditableTrue$1(node)) {
        return true;
      }
    }
    return true;
  };
  var isAtomicContentEditableFalse = function (node) {
    if (!isContentEditableFalse$1(node)) {
      return false;
    }
    return $_c2vc3pkje5o2puw.reduce(node.getElementsByTagName('*'), function (result, elm) {
      return result || isContentEditableTrue$1(elm);
    }, false) !== true;
  };
  var isAtomic = function (node) {
    return isAtomicInline(node) || isAtomicContentEditableFalse(node);
  };
  var isEditableCaretCandidate = function (node, root) {
    return isCaretCandidate(node) && isInEditable(node, root);
  };

  var round = Math.round;
  var clone$1 = function (rect) {
    if (!rect) {
      return {
        left: 0,
        top: 0,
        bottom: 0,
        right: 0,
        width: 0,
        height: 0
      };
    }
    return {
      left: round(rect.left),
      top: round(rect.top),
      bottom: round(rect.bottom),
      right: round(rect.right),
      width: round(rect.width),
      height: round(rect.height)
    };
  };
  var collapse = function (rect, toStart) {
    rect = clone$1(rect);
    if (toStart) {
      rect.right = rect.left;
    } else {
      rect.left = rect.left + rect.width;
      rect.right = rect.left;
    }
    rect.width = 0;
    return rect;
  };
  var isEqual = function (rect1, rect2) {
    return rect1.left === rect2.left && rect1.top === rect2.top && rect1.bottom === rect2.bottom && rect1.right === rect2.right;
  };
  var isValidOverflow = function (overflowY, rect1, rect2) {
    return overflowY >= 0 && overflowY <= Math.min(rect1.height, rect2.height) / 2;
  };
  var isAbove = function (rect1, rect2) {
    if (rect1.bottom - rect1.height / 2 < rect2.top) {
      return true;
    }
    if (rect1.top > rect2.bottom) {
      return false;
    }
    return isValidOverflow(rect2.top - rect1.bottom, rect1, rect2);
  };
  var isBelow = function (rect1, rect2) {
    if (rect1.top > rect2.bottom) {
      return true;
    }
    if (rect1.bottom < rect2.top) {
      return false;
    }
    return isValidOverflow(rect2.bottom - rect1.top, rect1, rect2);
  };
  var containsXY = function (rect, clientX, clientY) {
    return clientX >= rect.left && clientX <= rect.right && clientY >= rect.top && clientY <= rect.bottom;
  };

  var getSelectedNode = function (range) {
    var startContainer = range.startContainer, startOffset = range.startOffset;
    if (startContainer.hasChildNodes() && range.endOffset === startOffset + 1) {
      return startContainer.childNodes[startOffset];
    }
    return null;
  };
  var getNode = function (container, offset) {
    if (container.nodeType === 1 && container.hasChildNodes()) {
      if (offset >= container.childNodes.length) {
        offset = container.childNodes.length - 1;
      }
      container = container.childNodes[offset];
    }
    return container;
  };

  var extendingChars = new RegExp('[\u0300-\u036f\u0483-\u0487\u0488-\u0489\u0591-\u05bd\u05bf\u05c1-\u05c2\u05c4-\u05c5\u05c7\u0610-\u061a' + '\u064b-\u065f\u0670\u06d6-\u06dc\u06df-\u06e4\u06e7-\u06e8\u06ea-\u06ed\u0711\u0730-\u074a\u07a6-\u07b0' + '\u07eb-\u07f3\u0816-\u0819\u081b-\u0823\u0825-\u0827\u0829-\u082d\u0859-\u085b\u08E3-\u0902\u093a\u093c' + '\u0941-\u0948\u094d\u0951-\u0957\u0962-\u0963\u0981\u09bc\u09be\u09c1-\u09c4\u09cd\u09d7\u09e2-\u09e3' + '\u0a01-\u0a02\u0a3c\u0a41-\u0a42\u0a47-\u0a48\u0a4b-\u0a4d\u0a51\u0a70-\u0a71\u0a75\u0a81-\u0a82\u0abc' + '\u0ac1-\u0ac5\u0ac7-\u0ac8\u0acd\u0ae2-\u0ae3\u0b01\u0b3c\u0b3e\u0b3f\u0b41-\u0b44\u0b4d\u0b56\u0b57' + '\u0b62-\u0b63\u0b82\u0bbe\u0bc0\u0bcd\u0bd7\u0c00\u0c3e-\u0c40\u0c46-\u0c48\u0c4a-\u0c4d\u0c55-\u0c56' + '\u0c62-\u0c63\u0c81\u0cbc\u0cbf\u0cc2\u0cc6\u0ccc-\u0ccd\u0cd5-\u0cd6\u0ce2-\u0ce3\u0d01\u0d3e\u0d41-\u0d44' + '\u0d4d\u0d57\u0d62-\u0d63\u0dca\u0dcf\u0dd2-\u0dd4\u0dd6\u0ddf\u0e31\u0e34-\u0e3a\u0e47-\u0e4e\u0eb1\u0eb4-\u0eb9' + '\u0ebb-\u0ebc\u0ec8-\u0ecd\u0f18-\u0f19\u0f35\u0f37\u0f39\u0f71-\u0f7e\u0f80-\u0f84\u0f86-\u0f87\u0f8d-\u0f97' + '\u0f99-\u0fbc\u0fc6\u102d-\u1030\u1032-\u1037\u1039-\u103a\u103d-\u103e\u1058-\u1059\u105e-\u1060\u1071-\u1074' + '\u1082\u1085-\u1086\u108d\u109d\u135d-\u135f\u1712-\u1714\u1732-\u1734\u1752-\u1753\u1772-\u1773\u17b4-\u17b5' + '\u17b7-\u17bd\u17c6\u17c9-\u17d3\u17dd\u180b-\u180d\u18a9\u1920-\u1922\u1927-\u1928\u1932\u1939-\u193b\u1a17-\u1a18' + '\u1a1b\u1a56\u1a58-\u1a5e\u1a60\u1a62\u1a65-\u1a6c\u1a73-\u1a7c\u1a7f\u1ab0-\u1abd\u1ABE\u1b00-\u1b03\u1b34' + '\u1b36-\u1b3a\u1b3c\u1b42\u1b6b-\u1b73\u1b80-\u1b81\u1ba2-\u1ba5\u1ba8-\u1ba9\u1bab-\u1bad\u1be6\u1be8-\u1be9' + '\u1bed\u1bef-\u1bf1\u1c2c-\u1c33\u1c36-\u1c37\u1cd0-\u1cd2\u1cd4-\u1ce0\u1ce2-\u1ce8\u1ced\u1cf4\u1cf8-\u1cf9' + '\u1dc0-\u1df5\u1dfc-\u1dff\u200c-\u200d\u20d0-\u20dc\u20DD-\u20E0\u20e1\u20E2-\u20E4\u20e5-\u20f0\u2cef-\u2cf1' + '\u2d7f\u2de0-\u2dff\u302a-\u302d\u302e-\u302f\u3099-\u309a\ua66f\uA670-\uA672\ua674-\ua67d\uA69E-\ua69f\ua6f0-\ua6f1' + '\ua802\ua806\ua80b\ua825-\ua826\ua8c4\ua8e0-\ua8f1\ua926-\ua92d\ua947-\ua951\ua980-\ua982\ua9b3\ua9b6-\ua9b9\ua9bc' + '\ua9e5\uaa29-\uaa2e\uaa31-\uaa32\uaa35-\uaa36\uaa43\uaa4c\uaa7c\uaab0\uaab2-\uaab4\uaab7-\uaab8\uaabe-\uaabf\uaac1' + '\uaaec-\uaaed\uaaf6\uabe5\uabe8\uabed\ufb1e\ufe00-\ufe0f\ufe20-\uFE2F\uff9e-\uff9f]');
  var isExtendingChar = function (ch) {
    return typeof ch === 'string' && ch.charCodeAt(0) >= 768 && extendingChars.test(ch);
  };

  var slice$3 = [].slice;
  var constant$1 = function (value) {
    return function () {
      return value;
    };
  };
  var negate = function (predicate) {
    return function (x) {
      return !predicate(x);
    };
  };
  var compose$2 = function (f, g) {
    return function (x) {
      return f(g(x));
    };
  };
  var or = function () {
    var x = [];
    for (var _i = 0; _i < arguments.length; _i++) {
      x[_i] = arguments[_i];
    }
    var args = slice$3.call(arguments);
    return function (x) {
      for (var i = 0; i < args.length; i++) {
        if (args[i](x)) {
          return true;
        }
      }
      return false;
    };
  };
  var and = function () {
    var x = [];
    for (var _i = 0; _i < arguments.length; _i++) {
      x[_i] = arguments[_i];
    }
    var args = slice$3.call(arguments);
    return function (x) {
      for (var i = 0; i < args.length; i++) {
        if (!args[i](x)) {
          return false;
        }
      }
      return true;
    };
  };
  var curry$1 = function (fn) {
    var x = [];
    for (var _i = 1; _i < arguments.length; _i++) {
      x[_i - 1] = arguments[_i];
    }
    var args = slice$3.call(arguments);
    if (args.length - 1 >= fn.length) {
      return fn.apply(this, args.slice(1));
    }
    return function () {
      var tempArgs = args.concat([].slice.call(arguments));
      return curry$1.apply(this, tempArgs);
    };
  };
  var noop$1 = function () {
  };
  var $_ghq3nq25je5o2q5q = {
    constant: constant$1,
    negate: negate,
    and: and,
    or: or,
    curry: curry$1,
    compose: compose$2,
    noop: noop$1
  };

  var isElement$3 = $_9owqcy1qje5o2q0e.isElement;
  var isCaretCandidate$1 = isCaretCandidate;
  var isBlock$1 = $_9owqcy1qje5o2q0e.matchStyleValues('display', 'block table');
  var isFloated = $_9owqcy1qje5o2q0e.matchStyleValues('float', 'left right');
  var isValidElementCaretCandidate = $_ghq3nq25je5o2q5q.and(isElement$3, isCaretCandidate$1, $_ghq3nq25je5o2q5q.negate(isFloated));
  var isNotPre = $_ghq3nq25je5o2q5q.negate($_9owqcy1qje5o2q0e.matchStyleValues('white-space', 'pre pre-line pre-wrap'));
  var isText$4 = $_9owqcy1qje5o2q0e.isText;
  var isBr$3 = $_9owqcy1qje5o2q0e.isBr;
  var nodeIndex$1 = DOMUtils.nodeIndex;
  var resolveIndex = getNode;
  var createRange = function (doc) {
    return 'createRange' in doc ? doc.createRange() : DOMUtils.DOM.createRng();
  };
  var isWhiteSpace = function (chr) {
    return chr && /[\r\n\t ]/.test(chr);
  };
  var isRange = function (rng) {
    return !!rng.setStart && !!rng.setEnd;
  };
  var isHiddenWhiteSpaceRange = function (range) {
    var container = range.startContainer;
    var offset = range.startOffset;
    var text;
    if (isWhiteSpace(range.toString()) && isNotPre(container.parentNode) && $_9owqcy1qje5o2q0e.isText(container)) {
      text = container.data;
      if (isWhiteSpace(text[offset - 1]) || isWhiteSpace(text[offset + 1])) {
        return true;
      }
    }
    return false;
  };
  var getBrClientRect = function (brNode) {
    var doc = brNode.ownerDocument;
    var rng = createRange(doc);
    var nbsp = doc.createTextNode('\xA0');
    var parentNode = brNode.parentNode;
    var clientRect;
    parentNode.insertBefore(nbsp, brNode);
    rng.setStart(nbsp, 0);
    rng.setEnd(nbsp, 1);
    clientRect = clone$1(rng.getBoundingClientRect());
    parentNode.removeChild(nbsp);
    return clientRect;
  };
  var getBoundingClientRectWebKitText = function (rng) {
    var sc = rng.startContainer;
    var ec = rng.endContainer;
    var so = rng.startOffset;
    var eo = rng.endOffset;
    if (sc === ec && $_9owqcy1qje5o2q0e.isText(ec) && so === 0 && eo === 1) {
      var newRng = rng.cloneRange();
      newRng.setEndAfter(ec);
      return getBoundingClientRect(newRng);
    } else {
      return null;
    }
  };
  var isZeroRect = function (r) {
    return r.left === 0 && r.right === 0 && r.top === 0 && r.bottom === 0;
  };
  var getBoundingClientRect = function (item) {
    var clientRect, clientRects;
    clientRects = item.getClientRects();
    if (clientRects.length > 0) {
      clientRect = clone$1(clientRects[0]);
    } else {
      clientRect = clone$1(item.getBoundingClientRect());
    }
    if (!isRange(item) && isBr$3(item) && isZeroRect(clientRect)) {
      return getBrClientRect(item);
    }
    if (isZeroRect(clientRect) && isRange(item)) {
      return getBoundingClientRectWebKitText(item);
    }
    return clientRect;
  };
  var collapseAndInflateWidth = function (clientRect, toStart) {
    var newClientRect = collapse(clientRect, toStart);
    newClientRect.width = 1;
    newClientRect.right = newClientRect.left + 1;
    return newClientRect;
  };
  var getCaretPositionClientRects = function (caretPosition) {
    var clientRects = [];
    var beforeNode, node;
    var addUniqueAndValidRect = function (clientRect) {
      if (clientRect.height === 0) {
        return;
      }
      if (clientRects.length > 0) {
        if (isEqual(clientRect, clientRects[clientRects.length - 1])) {
          return;
        }
      }
      clientRects.push(clientRect);
    };
    var addCharacterOffset = function (container, offset) {
      var range = createRange(container.ownerDocument);
      if (offset < container.data.length) {
        if (isExtendingChar(container.data[offset])) {
          return clientRects;
        }
        if (isExtendingChar(container.data[offset - 1])) {
          range.setStart(container, offset);
          range.setEnd(container, offset + 1);
          if (!isHiddenWhiteSpaceRange(range)) {
            addUniqueAndValidRect(collapseAndInflateWidth(getBoundingClientRect(range), false));
            return clientRects;
          }
        }
      }
      if (offset > 0) {
        range.setStart(container, offset - 1);
        range.setEnd(container, offset);
        if (!isHiddenWhiteSpaceRange(range)) {
          addUniqueAndValidRect(collapseAndInflateWidth(getBoundingClientRect(range), false));
        }
      }
      if (offset < container.data.length) {
        range.setStart(container, offset);
        range.setEnd(container, offset + 1);
        if (!isHiddenWhiteSpaceRange(range)) {
          addUniqueAndValidRect(collapseAndInflateWidth(getBoundingClientRect(range), true));
        }
      }
    };
    if (isText$4(caretPosition.container())) {
      addCharacterOffset(caretPosition.container(), caretPosition.offset());
      return clientRects;
    }
    if (isElement$3(caretPosition.container())) {
      if (caretPosition.isAtEnd()) {
        node = resolveIndex(caretPosition.container(), caretPosition.offset());
        if (isText$4(node)) {
          addCharacterOffset(node, node.data.length);
        }
        if (isValidElementCaretCandidate(node) && !isBr$3(node)) {
          addUniqueAndValidRect(collapseAndInflateWidth(getBoundingClientRect(node), false));
        }
      } else {
        node = resolveIndex(caretPosition.container(), caretPosition.offset());
        if (isText$4(node)) {
          addCharacterOffset(node, 0);
        }
        if (isValidElementCaretCandidate(node) && caretPosition.isAtEnd()) {
          addUniqueAndValidRect(collapseAndInflateWidth(getBoundingClientRect(node), false));
          return clientRects;
        }
        beforeNode = resolveIndex(caretPosition.container(), caretPosition.offset() - 1);
        if (isValidElementCaretCandidate(beforeNode) && !isBr$3(beforeNode)) {
          if (isBlock$1(beforeNode) || isBlock$1(node) || !isValidElementCaretCandidate(node)) {
            addUniqueAndValidRect(collapseAndInflateWidth(getBoundingClientRect(beforeNode), false));
          }
        }
        if (isValidElementCaretCandidate(node)) {
          addUniqueAndValidRect(collapseAndInflateWidth(getBoundingClientRect(node), true));
        }
      }
    }
    return clientRects;
  };
  function CaretPosition(container, offset, clientRects) {
    var isAtStart = function () {
      if (isText$4(container)) {
        return offset === 0;
      }
      return offset === 0;
    };
    var isAtEnd = function () {
      if (isText$4(container)) {
        return offset >= container.data.length;
      }
      return offset >= container.childNodes.length;
    };
    var toRange = function () {
      var range;
      range = createRange(container.ownerDocument);
      range.setStart(container, offset);
      range.setEnd(container, offset);
      return range;
    };
    var getClientRects = function () {
      if (!clientRects) {
        clientRects = getCaretPositionClientRects(CaretPosition(container, offset));
      }
      return clientRects;
    };
    var isVisible = function () {
      return getClientRects().length > 0;
    };
    var isEqual$$1 = function (caretPosition) {
      return caretPosition && container === caretPosition.container() && offset === caretPosition.offset();
    };
    var getNode$$1 = function (before) {
      return resolveIndex(container, before ? offset - 1 : offset);
    };
    return {
      container: $_ghq3nq25je5o2q5q.constant(container),
      offset: $_ghq3nq25je5o2q5q.constant(offset),
      toRange: toRange,
      getClientRects: getClientRects,
      isVisible: isVisible,
      isAtStart: isAtStart,
      isAtEnd: isAtEnd,
      isEqual: isEqual$$1,
      getNode: getNode$$1
    };
  }
  (function (CaretPosition) {
    CaretPosition.fromRangeStart = function (range) {
      return CaretPosition(range.startContainer, range.startOffset);
    };
    CaretPosition.fromRangeEnd = function (range) {
      return CaretPosition(range.endContainer, range.endOffset);
    };
    CaretPosition.after = function (node) {
      return CaretPosition(node.parentNode, nodeIndex$1(node) + 1);
    };
    CaretPosition.before = function (node) {
      return CaretPosition(node.parentNode, nodeIndex$1(node));
    };
    CaretPosition.isAtStart = function (pos) {
      return pos ? pos.isAtStart() : false;
    };
    CaretPosition.isAtEnd = function (pos) {
      return pos ? pos.isAtEnd() : false;
    };
    CaretPosition.isTextPosition = function (pos) {
      return pos ? $_9owqcy1qje5o2q0e.isText(pos.container()) : false;
    };
  }(CaretPosition || (CaretPosition = {})));
  var CaretPosition$1 = CaretPosition;

  var isElement$4 = $_9owqcy1qje5o2q0e.isElement;
  var isText$5 = $_9owqcy1qje5o2q0e.isText;
  var removeNode = function (node) {
    var parentNode = node.parentNode;
    if (parentNode) {
      parentNode.removeChild(node);
    }
  };
  var getNodeValue = function (node) {
    try {
      return node.nodeValue;
    } catch (ex) {
      return '';
    }
  };
  var setNodeValue = function (node, text) {
    if (text.length === 0) {
      removeNode(node);
    } else {
      node.nodeValue = text;
    }
  };
  var trimCount = function (text) {
    var trimmedText = $_4md31c21je5o2q5f.trim(text);
    return {
      count: text.length - trimmedText.length,
      text: trimmedText
    };
  };
  var removeUnchanged = function (caretContainer, pos) {
    remove$2(caretContainer);
    return pos;
  };
  var removeTextAndReposition = function (caretContainer, pos) {
    var before = trimCount(caretContainer.data.substr(0, pos.offset()));
    var after = trimCount(caretContainer.data.substr(pos.offset()));
    var text = before.text + after.text;
    if (text.length > 0) {
      setNodeValue(caretContainer, text);
      return CaretPosition$1(caretContainer, pos.offset() - before.count);
    } else {
      return pos;
    }
  };
  var removeElementAndReposition = function (caretContainer, pos) {
    var parentNode = pos.container();
    var newPosition = $_bwcteb4je5o2pl4.indexOf(parentNode.childNodes, caretContainer).map(function (index) {
      return index < pos.offset() ? CaretPosition$1(parentNode, pos.offset() - 1) : pos;
    }).getOr(pos);
    remove$2(caretContainer);
    return newPosition;
  };
  var removeTextCaretContainer = function (caretContainer, pos) {
    return isText$5(caretContainer) && pos.container() === caretContainer ? removeTextAndReposition(caretContainer, pos) : removeUnchanged(caretContainer, pos);
  };
  var removeElementCaretContainer = function (caretContainer, pos) {
    return pos.container() === caretContainer.parentNode ? removeElementAndReposition(caretContainer, pos) : removeUnchanged(caretContainer, pos);
  };
  var removeAndReposition = function (container, pos) {
    return CaretPosition$1.isTextPosition(pos) ? removeTextCaretContainer(container, pos) : removeElementCaretContainer(container, pos);
  };
  var remove$2 = function (caretContainerNode) {
    if (isElement$4(caretContainerNode) && isCaretContainer(caretContainerNode)) {
      if (hasContent(caretContainerNode)) {
        caretContainerNode.removeAttribute('data-mce-caret');
      } else {
        removeNode(caretContainerNode);
      }
    }
    if (isText$5(caretContainerNode)) {
      var text = $_4md31c21je5o2q5f.trim(getNodeValue(caretContainerNode));
      setNodeValue(caretContainerNode, text);
    }
  };
  var $_3djwbs29je5o2q74 = {
    removeAndReposition: removeAndReposition,
    remove: remove$2
  };

  var walkToPositionIn = function (forward, root, start) {
    var position = forward ? CaretPosition$1.before(start) : CaretPosition$1.after(start);
    return fromPosition(forward, root, position);
  };
  var afterElement = function (node) {
    return $_9owqcy1qje5o2q0e.isBr(node) ? CaretPosition$1.before(node) : CaretPosition$1.after(node);
  };
  var isBeforeOrStart = function (position) {
    if (CaretPosition$1.isTextPosition(position)) {
      return position.offset() === 0;
    } else {
      return isCaretCandidate(position.getNode());
    }
  };
  var isAfterOrEnd = function (position) {
    if (CaretPosition$1.isTextPosition(position)) {
      var container = position.container();
      return position.offset() === container.data.length;
    } else {
      return isCaretCandidate(position.getNode(true));
    }
  };
  var isBeforeAfterSameElement = function (from, to) {
    return !CaretPosition$1.isTextPosition(from) && !CaretPosition$1.isTextPosition(to) && from.getNode() === to.getNode(true);
  };
  var isAtBr = function (position) {
    return !CaretPosition$1.isTextPosition(position) && $_9owqcy1qje5o2q0e.isBr(position.getNode());
  };
  var shouldSkipPosition = function (forward, from, to) {
    if (forward) {
      return !isBeforeAfterSameElement(from, to) && !isAtBr(from) && isAfterOrEnd(from) && isBeforeOrStart(to);
    } else {
      return !isBeforeAfterSameElement(to, from) && isBeforeOrStart(from) && isAfterOrEnd(to);
    }
  };
  var fromPosition = function (forward, root, pos) {
    var walker = CaretWalker(root);
    return Option.from(forward ? walker.next(pos) : walker.prev(pos));
  };
  var navigate = function (forward, root, from) {
    return fromPosition(forward, root, from).bind(function (to) {
      if (isInSameBlock(from, to, root) && shouldSkipPosition(forward, from, to)) {
        return fromPosition(forward, root, to);
      } else {
        return Option.some(to);
      }
    });
  };
  var positionIn = function (forward, element) {
    var startNode = forward ? element.firstChild : element.lastChild;
    if ($_9owqcy1qje5o2q0e.isText(startNode)) {
      return Option.some(CaretPosition$1(startNode, forward ? 0 : startNode.data.length));
    } else if (startNode) {
      if (isCaretCandidate(startNode)) {
        return Option.some(forward ? CaretPosition$1.before(startNode) : afterElement(startNode));
      } else {
        return walkToPositionIn(forward, element, startNode);
      }
    } else {
      return Option.none();
    }
  };
  var $_g2kzjo2bje5o2q7n = {
    fromPosition: fromPosition,
    nextPosition: $_enfhkv6je5o2ply.curry(fromPosition, true),
    prevPosition: $_enfhkv6je5o2ply.curry(fromPosition, false),
    navigate: navigate,
    positionIn: positionIn,
    firstPositionIn: $_enfhkv6je5o2ply.curry(positionIn, true),
    lastPositionIn: $_enfhkv6je5o2ply.curry(positionIn, false)
  };

  var isContentEditableTrue$2 = $_9owqcy1qje5o2q0e.isContentEditableTrue;
  var isContentEditableFalse$2 = $_9owqcy1qje5o2q0e.isContentEditableFalse;
  var showCaret = function (direction, editor, node, before, scrollIntoView) {
    return editor._selectionOverrides.showCaret(direction, node, before, scrollIntoView);
  };
  var getNodeRange = function (node) {
    var rng = node.ownerDocument.createRange();
    rng.selectNode(node);
    return rng;
  };
  var selectNode = function (editor, node) {
    var e;
    e = editor.fire('BeforeObjectSelected', { target: node });
    if (e.isDefaultPrevented()) {
      return null;
    }
    return getNodeRange(node);
  };
  var renderCaretAtRange = function (editor, range, scrollIntoView) {
    var caretPosition, ceRoot;
    range = normalizeRange(1, editor.getBody(), range);
    caretPosition = CaretPosition$1.fromRangeStart(range);
    if (isContentEditableFalse$2(caretPosition.getNode())) {
      return showCaret(1, editor, caretPosition.getNode(), !caretPosition.isAtEnd(), false);
    }
    if (isContentEditableFalse$2(caretPosition.getNode(true))) {
      return showCaret(1, editor, caretPosition.getNode(true), false, false);
    }
    ceRoot = editor.dom.getParent(caretPosition.getNode(), function (node) {
      return isContentEditableFalse$2(node) || isContentEditableTrue$2(node);
    });
    if (isContentEditableFalse$2(ceRoot)) {
      return showCaret(1, editor, ceRoot, false, scrollIntoView);
    }
    return null;
  };
  var renderRangeCaret = function (editor, range, scrollIntoView) {
    var caretRange;
    if (!range || !range.collapsed) {
      return range;
    }
    caretRange = renderCaretAtRange(editor, range, scrollIntoView);
    if (caretRange) {
      return caretRange;
    }
    return range;
  };

  var cat = function (arr) {
    var r = [];
    var push = function (x) {
      r.push(x);
    };
    for (var i = 0; i < arr.length; i++) {
      arr[i].each(push);
    }
    return r;
  };
  var findMap = function (arr, f) {
    for (var i = 0; i < arr.length; i++) {
      var r = f(arr[i], i);
      if (r.isSome()) {
        return r;
      }
    }
    return Option.none();
  };
  var liftN = function (arr, f) {
    var r = [];
    for (var i = 0; i < arr.length; i++) {
      var x = arr[i];
      if (x.isSome()) {
        r.push(x.getOrDie());
      } else {
        return Option.none();
      }
    }
    return Option.some(f.apply(null, r));
  };
  var $_c8weyc2eje5o2q85 = {
    cat: cat,
    findMap: findMap,
    liftN: liftN
  };

  var BreakType;
  (function (BreakType) {
    BreakType[BreakType['Br'] = 0] = 'Br';
    BreakType[BreakType['Block'] = 1] = 'Block';
    BreakType[BreakType['Wrap'] = 2] = 'Wrap';
    BreakType[BreakType['Eol'] = 3] = 'Eol';
  }(BreakType || (BreakType = {})));
  var isAbove$1 = function (pos1, pos2) {
    return $_c8weyc2eje5o2q85.liftN([
      $_bwcteb4je5o2pl4.head(pos2.getClientRects()),
      $_bwcteb4je5o2pl4.last(pos1.getClientRects())
    ], isAbove).getOr(false);
  };
  var isBelow$1 = function (pos1, pos2) {
    return $_c8weyc2eje5o2q85.liftN([
      $_bwcteb4je5o2pl4.last(pos2.getClientRects()),
      $_bwcteb4je5o2pl4.head(pos1.getClientRects())
    ], function (r1, r2) {
      return isBelow(r1, r2);
    }).getOr(false);
  };
  var flip = function (direction, positions) {
    return direction === HDirection.Backwards ? positions.reverse() : positions;
  };
  var walk$1 = function (direction, caretWalker, pos) {
    return direction === HDirection.Forwards ? caretWalker.next(pos) : caretWalker.prev(pos);
  };
  var getBreakType = function (scope, direction, currentPos, nextPos) {
    if ($_9owqcy1qje5o2q0e.isBr(nextPos.getNode(direction === HDirection.Forwards))) {
      return BreakType.Br;
    } else if (isInSameBlock(currentPos, nextPos) === false) {
      return BreakType.Block;
    } else {
      return BreakType.Wrap;
    }
  };
  var getPositionsUntil = function (predicate, direction, scope, start) {
    var caretWalker = CaretWalker(scope);
    var currentPos = start, nextPos;
    var positions = [];
    while (currentPos) {
      nextPos = walk$1(direction, caretWalker, currentPos);
      if (!nextPos) {
        break;
      }
      if ($_9owqcy1qje5o2q0e.isBr(nextPos.getNode(false))) {
        if (direction === HDirection.Forwards) {
          return {
            positions: flip(direction, positions).concat([nextPos]),
            breakType: BreakType.Br,
            breakAt: Option.some(nextPos)
          };
        } else {
          return {
            positions: flip(direction, positions),
            breakType: BreakType.Br,
            breakAt: Option.some(nextPos)
          };
        }
      }
      if (!nextPos.isVisible()) {
        currentPos = nextPos;
        continue;
      }
      if (predicate(currentPos, nextPos)) {
        var breakType = getBreakType(scope, direction, currentPos, nextPos);
        return {
          positions: flip(direction, positions),
          breakType: breakType,
          breakAt: Option.some(nextPos)
        };
      }
      positions.push(nextPos);
      currentPos = nextPos;
    }
    return {
      positions: flip(direction, positions),
      breakType: BreakType.Eol,
      breakAt: Option.none()
    };
  };
  var getAdjacentLinePositions = function (direction, getPositionsUntilBreak, scope, start) {
    return getPositionsUntilBreak(scope, start).breakAt.map(function (pos) {
      var positions = getPositionsUntilBreak(scope, pos).positions;
      return direction === HDirection.Backwards ? positions.concat(pos) : [pos].concat(positions);
    }).getOr([]);
  };
  var findClosestHorizontalPositionFromPoint = function (positions, x) {
    return $_bwcteb4je5o2pl4.foldl(positions, function (acc, newPos) {
      return acc.fold(function () {
        return Option.some(newPos);
      }, function (lastPos) {
        return $_c8weyc2eje5o2q85.liftN([
          $_bwcteb4je5o2pl4.head(lastPos.getClientRects()),
          $_bwcteb4je5o2pl4.head(newPos.getClientRects())
        ], function (lastRect, newRect) {
          var lastDist = Math.abs(x - lastRect.left);
          var newDist = Math.abs(x - newRect.left);
          return newDist <= lastDist ? newPos : lastPos;
        }).or(acc);
      });
    }, Option.none());
  };
  var findClosestHorizontalPosition = function (positions, pos) {
    return $_bwcteb4je5o2pl4.head(pos.getClientRects()).bind(function (targetRect) {
      return findClosestHorizontalPositionFromPoint(positions, targetRect.left);
    });
  };
  var getPositionsUntilPreviousLine = $_enfhkv6je5o2ply.curry(getPositionsUntil, isAbove$1, -1);
  var getPositionsUntilNextLine = $_enfhkv6je5o2ply.curry(getPositionsUntil, isBelow$1, 1);
  var getPositionsAbove = $_enfhkv6je5o2ply.curry(getAdjacentLinePositions, -1, getPositionsUntilPreviousLine);
  var getPositionsBelow = $_enfhkv6je5o2ply.curry(getAdjacentLinePositions, 1, getPositionsUntilNextLine);
  var getFirstLinePositions = function (scope) {
    return $_g2kzjo2bje5o2q7n.firstPositionIn(scope).map(function (pos) {
      return [pos].concat(getPositionsUntilNextLine(scope, pos).positions);
    }).getOr([]);
  };
  var getLastLinePositions = function (scope) {
    return $_g2kzjo2bje5o2q7n.lastPositionIn(scope).map(function (pos) {
      return getPositionsUntilPreviousLine(scope, pos).positions.concat(pos);
    }).getOr([]);
  };

  var all$1 = function (predicate) {
    return descendants($_484buq15je5o2py8.body(), predicate);
  };
  var ancestors = function (scope, predicate, isRoot) {
    return $_bwcteb4je5o2pl4.filter($_a6mttx17je5o2pyd.parents(scope, isRoot), predicate);
  };
  var siblings$1 = function (scope, predicate) {
    return $_bwcteb4je5o2pl4.filter($_a6mttx17je5o2pyd.siblings(scope), predicate);
  };
  var children$1 = function (scope, predicate) {
    return $_bwcteb4je5o2pl4.filter($_a6mttx17je5o2pyd.children(scope), predicate);
  };
  var descendants = function (scope, predicate) {
    var result = [];
    $_bwcteb4je5o2pl4.each($_a6mttx17je5o2pyd.children(scope), function (x) {
      if (predicate(x)) {
        result = result.concat([x]);
      }
      result = result.concat(descendants(x, predicate));
    });
    return result;
  };
  var $_47brb52hje5o2q8h = {
    all: all$1,
    ancestors: ancestors,
    siblings: siblings$1,
    children: children$1,
    descendants: descendants
  };

  var all$2 = function (selector) {
    return $_87zu5k1fje5o2pz9.all(selector);
  };
  var ancestors$1 = function (scope, selector, isRoot) {
    return $_47brb52hje5o2q8h.ancestors(scope, function (e) {
      return $_87zu5k1fje5o2pz9.is(e, selector);
    }, isRoot);
  };
  var siblings$2 = function (scope, selector) {
    return $_47brb52hje5o2q8h.siblings(scope, function (e) {
      return $_87zu5k1fje5o2pz9.is(e, selector);
    });
  };
  var children$2 = function (scope, selector) {
    return $_47brb52hje5o2q8h.children(scope, function (e) {
      return $_87zu5k1fje5o2pz9.is(e, selector);
    });
  };
  var descendants$1 = function (scope, selector) {
    return $_87zu5k1fje5o2pz9.all(selector, scope);
  };
  var $_b4jhpu2gje5o2q8e = {
    all: all$2,
    ancestors: ancestors$1,
    siblings: siblings$2,
    children: children$2,
    descendants: descendants$1
  };

  var deflate = function (rect, delta) {
    return {
      left: rect.left - delta,
      top: rect.top - delta,
      right: rect.right + delta * 2,
      bottom: rect.bottom + delta * 2,
      width: rect.width + delta,
      height: rect.height + delta
    };
  };
  var getCorners = function (getYAxisValue, tds) {
    return $_bwcteb4je5o2pl4.bind(tds, function (td) {
      var rect = deflate(td.getBoundingClientRect(), -1);
      return [
        {
          x: rect.left,
          y: getYAxisValue(rect),
          cell: td
        },
        {
          x: rect.right,
          y: getYAxisValue(rect),
          cell: td
        }
      ];
    });
  };
  var findClosestCorner = function (corners, x, y) {
    return $_bwcteb4je5o2pl4.foldl(corners, function (acc, newCorner) {
      return acc.fold(function () {
        return Option.some(newCorner);
      }, function (oldCorner) {
        var oldDist = Math.sqrt(Math.abs(oldCorner.x - x) + Math.abs(oldCorner.y - y));
        var newDist = Math.sqrt(Math.abs(newCorner.x - x) + Math.abs(newCorner.y - y));
        return Option.some(newDist < oldDist ? newCorner : oldCorner);
      });
    }, Option.none());
  };
  var getClosestCell = function (getYAxisValue, isTargetCorner, table, x, y) {
    var cells = $_b4jhpu2gje5o2q8e.descendants($_5tcb2dyje5o2pwu.fromDom(table), 'td,th').map(function (e) {
      return e.dom();
    });
    var corners = $_bwcteb4je5o2pl4.filter(getCorners(getYAxisValue, cells), function (corner) {
      return isTargetCorner(corner, y);
    });
    return findClosestCorner(corners, x, y).map(function (corner) {
      return corner.cell;
    });
  };
  var getBottomValue = function (rect) {
    return rect.bottom;
  };
  var getTopValue = function (rect) {
    return rect.top;
  };
  var isAbove$2 = function (corner, y) {
    return corner.y < y;
  };
  var isBelow$2 = function (corner, y) {
    return corner.y > y;
  };
  var getClosestCellAbove = $_enfhkv6je5o2ply.curry(getClosestCell, getBottomValue, isAbove$2);
  var getClosestCellBelow = $_enfhkv6je5o2ply.curry(getClosestCell, getTopValue, isBelow$2);
  var findClosestPositionInAboveCell = function (table, pos) {
    return $_bwcteb4je5o2pl4.head(pos.getClientRects()).bind(function (rect) {
      return getClosestCellAbove(table, rect.left, rect.top);
    }).bind(function (cell) {
      return findClosestHorizontalPosition(getLastLinePositions(cell), pos);
    });
  };
  var findClosestPositionInBelowCell = function (table, pos) {
    return $_bwcteb4je5o2pl4.last(pos.getClientRects()).bind(function (rect) {
      return getClosestCellBelow(table, rect.left, rect.top);
    }).bind(function (cell) {
      return findClosestHorizontalPosition(getFirstLinePositions(cell), pos);
    });
  };

  var browser$2 = $_2hq237mje5o2pvu.detect().browser;
  var isFakeCaretTableBrowser = function () {
    return browser$2.isIE() || browser$2.isEdge() || browser$2.isFirefox();
  };
  var isAtTableCellLine = function (getPositionsUntil, scope, pos) {
    var lineInfo = getPositionsUntil(scope, pos);
    if (lineInfo.breakType === BreakType.Wrap && lineInfo.positions.length === 0) {
      return lineInfo.breakAt.map(function (breakPos) {
        return getPositionsUntil(scope, breakPos).breakAt.isNone();
      }).getOr(true);
    } else {
      return lineInfo.breakAt.isNone();
    }
  };
  var isAtFirstTableCellLine = $_ghq3nq25je5o2q5q.curry(isAtTableCellLine, getPositionsUntilPreviousLine);
  var isAtLastTableCellLine = $_ghq3nq25je5o2q5q.curry(isAtTableCellLine, getPositionsUntilNextLine);
  var isCaretAtStartOrEndOfTable = function (forward, rng, table) {
    var caretPos = CaretPosition$1.fromRangeStart(rng);
    return $_g2kzjo2bje5o2q7n.positionIn(!forward, table).map(function (pos) {
      return pos.isEqual(caretPos);
    }).getOr(false);
  };
  var navigateHorizontally = function (editor, forward, table, td) {
    var rng = editor.selection.getRng();
    var direction = forward ? 1 : -1;
    if (isFakeCaretTableBrowser() && isCaretAtStartOrEndOfTable(forward, rng, table)) {
      var newRng = showCaret(direction, editor, table, !forward, true);
      editor.selection.setRng(newRng);
      return true;
    }
    return false;
  };
  var getClosestAbovePosition = function (root, table, start) {
    return findClosestPositionInAboveCell(table, start).orThunk(function () {
      return $_bwcteb4je5o2pl4.head(start.getClientRects()).bind(function (rect) {
        return findClosestHorizontalPositionFromPoint(getPositionsAbove(root, CaretPosition$1.before(table)), rect.left);
      });
    }).getOr(CaretPosition$1.before(table));
  };
  var getClosestBelowPosition = function (root, table, start) {
    return findClosestPositionInBelowCell(table, start).orThunk(function () {
      return $_bwcteb4je5o2pl4.head(start.getClientRects()).bind(function (rect) {
        return findClosestHorizontalPositionFromPoint(getPositionsBelow(root, CaretPosition$1.after(table)), rect.left);
      });
    }).getOr(CaretPosition$1.after(table));
  };
  var navigateVertically = function (editor, down, table, td) {
    var rng = editor.selection.getRng();
    var pos = CaretPosition$1.fromRangeStart(rng);
    var root = editor.getBody();
    if (!down && isAtFirstTableCellLine(td, pos)) {
      var newPos = getClosestAbovePosition(root, table, pos);
      editor.selection.setRng(newPos.toRange());
      return true;
    } else if (down && isAtLastTableCellLine(td, pos)) {
      var newPos = getClosestBelowPosition(root, table, pos);
      editor.selection.setRng(newPos.toRange());
      return true;
    } else {
      return false;
    }
  };
  var moveH = function (editor, forward) {
    return function () {
      return Option.from(editor.dom.getParent(editor.selection.getNode(), 'td,th')).bind(function (td) {
        return Option.from(editor.dom.getParent(td, 'table')).map(function (table) {
          return navigateHorizontally(editor, forward, table, td);
        });
      }).getOr(false);
    };
  };
  var moveV = function (editor, forward) {
    return function () {
      return Option.from(editor.dom.getParent(editor.selection.getNode(), 'td,th')).bind(function (td) {
        return Option.from(editor.dom.getParent(td, 'table')).map(function (table) {
          return navigateVertically(editor, forward, table, td);
        });
      }).getOr(false);
    };
  };

  var isContentEditableFalse$3 = $_9owqcy1qje5o2q0e.isContentEditableFalse;
  var isTableCell$1 = function (node) {
    return $_9owqcy1qje5o2q0e.isElement(node) && /^(TD|TH)$/i.test(node.tagName);
  };
  var getAbsoluteClientRect = function (root, element, before) {
    var clientRect = collapse(element.getBoundingClientRect(), before);
    var docElm, scrollX, scrollY, margin, rootRect;
    if (root.tagName === 'BODY') {
      docElm = root.ownerDocument.documentElement;
      scrollX = root.scrollLeft || docElm.scrollLeft;
      scrollY = root.scrollTop || docElm.scrollTop;
    } else {
      rootRect = root.getBoundingClientRect();
      scrollX = root.scrollLeft - rootRect.left;
      scrollY = root.scrollTop - rootRect.top;
    }
    clientRect.left += scrollX;
    clientRect.right += scrollX;
    clientRect.top += scrollY;
    clientRect.bottom += scrollY;
    clientRect.width = 1;
    margin = element.offsetWidth - element.clientWidth;
    if (margin > 0) {
      if (before) {
        margin *= -1;
      }
      clientRect.left += margin;
      clientRect.right += margin;
    }
    return clientRect;
  };
  var trimInlineCaretContainers = function (root) {
    var contentEditableFalseNodes, node, sibling, i, data;
    contentEditableFalseNodes = DomQuery('*[contentEditable=false]', root);
    for (i = 0; i < contentEditableFalseNodes.length; i++) {
      node = contentEditableFalseNodes[i];
      sibling = node.previousSibling;
      if (endsWithCaretContainer(sibling)) {
        data = sibling.data;
        if (data.length === 1) {
          sibling.parentNode.removeChild(sibling);
        } else {
          sibling.deleteData(data.length - 1, 1);
        }
      }
      sibling = node.nextSibling;
      if (startsWithCaretContainer(sibling)) {
        data = sibling.data;
        if (data.length === 1) {
          sibling.parentNode.removeChild(sibling);
        } else {
          sibling.deleteData(0, 1);
        }
      }
    }
  };
  var FakeCaret = function (root, isBlock, hasFocus) {
    var cursorInterval, $lastVisualCaret = null, caretContainerNode;
    var show = function (before, element) {
      var clientRect, rng;
      hide();
      if (isTableCell$1(element)) {
        return null;
      }
      if (isBlock(element)) {
        caretContainerNode = insertBlock('p', element, before);
        clientRect = getAbsoluteClientRect(root, element, before);
        DomQuery(caretContainerNode).css('top', clientRect.top);
        $lastVisualCaret = DomQuery('<div class="mce-visual-caret" data-mce-bogus="all"></div>').css(clientRect).appendTo(root);
        if (before) {
          $lastVisualCaret.addClass('mce-visual-caret-before');
        }
        startBlink();
        rng = element.ownerDocument.createRange();
        rng.setStart(caretContainerNode, 0);
        rng.setEnd(caretContainerNode, 0);
      } else {
        caretContainerNode = insertInline(element, before);
        rng = element.ownerDocument.createRange();
        if (isContentEditableFalse$3(caretContainerNode.nextSibling)) {
          rng.setStart(caretContainerNode, 0);
          rng.setEnd(caretContainerNode, 0);
        } else {
          rng.setStart(caretContainerNode, 1);
          rng.setEnd(caretContainerNode, 1);
        }
        return rng;
      }
      return rng;
    };
    var hide = function () {
      trimInlineCaretContainers(root);
      if (caretContainerNode) {
        $_3djwbs29je5o2q74.remove(caretContainerNode);
        caretContainerNode = null;
      }
      if ($lastVisualCaret) {
        $lastVisualCaret.remove();
        $lastVisualCaret = null;
      }
      clearInterval(cursorInterval);
    };
    var startBlink = function () {
      cursorInterval = $_db41wogje5o2ps3.setInterval(function () {
        if (hasFocus()) {
          DomQuery('div.mce-visual-caret', root).toggleClass('mce-visual-caret-hidden');
        } else {
          DomQuery('div.mce-visual-caret', root).addClass('mce-visual-caret-hidden');
        }
      }, 500);
    };
    var destroy = function () {
      return $_db41wogje5o2ps3.clearInterval(cursorInterval);
    };
    var getCss = function () {
      return '.mce-visual-caret {' + 'position: absolute;' + 'background-color: black;' + 'background-color: currentcolor;' + '}' + '.mce-visual-caret-hidden {' + 'display: none;' + '}' + '*[data-mce-caret] {' + 'position: absolute;' + 'left: -1000px;' + 'right: auto;' + 'top: 0;' + 'margin: 0;' + 'padding: 0;' + '}';
    };
    return {
      show: show,
      hide: hide,
      getCss: getCss,
      destroy: destroy
    };
  };
  var isFakeCaretTarget = function (node) {
    return isContentEditableFalse$3(node) || $_9owqcy1qje5o2q0e.isTable(node) && isFakeCaretTableBrowser();
  };

  var isContentEditableFalse$4 = $_9owqcy1qje5o2q0e.isContentEditableFalse;
  var isBlockLike = $_9owqcy1qje5o2q0e.matchStyleValues('display', 'block table table-cell table-caption list-item');
  var isCaretContainer$2 = isCaretContainer;
  var isCaretContainerBlock$1 = isCaretContainerBlock;
  var curry$2 = $_ghq3nq25je5o2q5q.curry;
  var isElement$5 = $_9owqcy1qje5o2q0e.isElement;
  var isCaretCandidate$2 = isCaretCandidate;
  var isForwards = function (direction) {
    return direction > 0;
  };
  var isBackwards = function (direction) {
    return direction < 0;
  };
  var skipCaretContainers = function (walk, shallow) {
    var node;
    while (node = walk(shallow)) {
      if (!isCaretContainerBlock$1(node)) {
        return node;
      }
    }
    return null;
  };
  var findNode = function (node, direction, predicateFn, rootNode, shallow) {
    var walker = new TreeWalker(node, rootNode);
    if (isBackwards(direction)) {
      if (isContentEditableFalse$4(node) || isCaretContainerBlock$1(node)) {
        node = skipCaretContainers(walker.prev, true);
        if (predicateFn(node)) {
          return node;
        }
      }
      while (node = skipCaretContainers(walker.prev, shallow)) {
        if (predicateFn(node)) {
          return node;
        }
      }
    }
    if (isForwards(direction)) {
      if (isContentEditableFalse$4(node) || isCaretContainerBlock$1(node)) {
        node = skipCaretContainers(walker.next, true);
        if (predicateFn(node)) {
          return node;
        }
      }
      while (node = skipCaretContainers(walker.next, shallow)) {
        if (predicateFn(node)) {
          return node;
        }
      }
    }
    return null;
  };
  var getParentBlock = function (node, rootNode) {
    while (node && node !== rootNode) {
      if (isBlockLike(node)) {
        return node;
      }
      node = node.parentNode;
    }
    return null;
  };
  var isInSameBlock = function (caretPosition1, caretPosition2, rootNode) {
    return getParentBlock(caretPosition1.container(), rootNode) === getParentBlock(caretPosition2.container(), rootNode);
  };
  var getChildNodeAtRelativeOffset = function (relativeOffset, caretPosition) {
    var container, offset;
    if (!caretPosition) {
      return null;
    }
    container = caretPosition.container();
    offset = caretPosition.offset();
    if (!isElement$5(container)) {
      return null;
    }
    return container.childNodes[offset + relativeOffset];
  };
  var beforeAfter = function (before, node) {
    var range = node.ownerDocument.createRange();
    if (before) {
      range.setStartBefore(node);
      range.setEndBefore(node);
    } else {
      range.setStartAfter(node);
      range.setEndAfter(node);
    }
    return range;
  };
  var isNodesInSameBlock = function (root, node1, node2) {
    return getParentBlock(node1, root) === getParentBlock(node2, root);
  };
  var lean = function (left, root, node) {
    var sibling, siblingName;
    if (left) {
      siblingName = 'previousSibling';
    } else {
      siblingName = 'nextSibling';
    }
    while (node && node !== root) {
      sibling = node[siblingName];
      if (isCaretContainer$2(sibling)) {
        sibling = sibling[siblingName];
      }
      if (isContentEditableFalse$4(sibling)) {
        if (isNodesInSameBlock(root, sibling, node)) {
          return sibling;
        }
        break;
      }
      if (isCaretCandidate$2(sibling)) {
        break;
      }
      node = node.parentNode;
    }
    return null;
  };
  var before = curry$2(beforeAfter, true);
  var after = curry$2(beforeAfter, false);
  var normalizeRange = function (direction, root, range) {
    var node, container, offset, location;
    var leanLeft = curry$2(lean, true, root);
    var leanRight = curry$2(lean, false, root);
    container = range.startContainer;
    offset = range.startOffset;
    if (isCaretContainerBlock(container)) {
      if (!isElement$5(container)) {
        container = container.parentNode;
      }
      location = container.getAttribute('data-mce-caret');
      if (location === 'before') {
        node = container.nextSibling;
        if (isFakeCaretTarget(node)) {
          return before(node);
        }
      }
      if (location === 'after') {
        node = container.previousSibling;
        if (isFakeCaretTarget(node)) {
          return after(node);
        }
      }
    }
    if (!range.collapsed) {
      return range;
    }
    if ($_9owqcy1qje5o2q0e.isText(container)) {
      if (isCaretContainer$2(container)) {
        if (direction === 1) {
          node = leanRight(container);
          if (node) {
            return before(node);
          }
          node = leanLeft(container);
          if (node) {
            return after(node);
          }
        }
        if (direction === -1) {
          node = leanLeft(container);
          if (node) {
            return after(node);
          }
          node = leanRight(container);
          if (node) {
            return before(node);
          }
        }
        return range;
      }
      if (endsWithCaretContainer(container) && offset >= container.data.length - 1) {
        if (direction === 1) {
          node = leanRight(container);
          if (node) {
            return before(node);
          }
        }
        return range;
      }
      if (startsWithCaretContainer(container) && offset <= 1) {
        if (direction === -1) {
          node = leanLeft(container);
          if (node) {
            return after(node);
          }
        }
        return range;
      }
      if (offset === container.data.length) {
        node = leanRight(container);
        if (node) {
          return before(node);
        }
        return range;
      }
      if (offset === 0) {
        node = leanLeft(container);
        if (node) {
          return after(node);
        }
        return range;
      }
    }
    return range;
  };
  var isNextToContentEditableFalse = function (relativeOffset, caretPosition) {
    var node = getChildNodeAtRelativeOffset(relativeOffset, caretPosition);
    return isContentEditableFalse$4(node) && !$_9owqcy1qje5o2q0e.isBogusAll(node);
  };
  var isNextToTable = function (relativeOffset, caretPosition) {
    return $_9owqcy1qje5o2q0e.isTable(getChildNodeAtRelativeOffset(relativeOffset, caretPosition));
  };
  var getRelativeCefElm = function (forward, caretPosition) {
    return Option.from(getChildNodeAtRelativeOffset(forward ? 0 : -1, caretPosition)).filter(isContentEditableFalse$4);
  };
  var getNormalizedRangeEndPoint = function (direction, root, range) {
    var normalizedRange = normalizeRange(direction, root, range);
    if (direction === -1) {
      return CaretPosition.fromRangeStart(normalizedRange);
    }
    return CaretPosition.fromRangeEnd(normalizedRange);
  };
  var isBeforeContentEditableFalse = curry$2(isNextToContentEditableFalse, 0);
  var isAfterContentEditableFalse = curry$2(isNextToContentEditableFalse, -1);
  var isBeforeTable = curry$2(isNextToTable, 0);
  var isAfterTable = curry$2(isNextToTable, -1);

  var HDirection;
  (function (HDirection) {
    HDirection[HDirection['Backwards'] = -1] = 'Backwards';
    HDirection[HDirection['Forwards'] = 1] = 'Forwards';
  }(HDirection || (HDirection = {})));
  var isContentEditableFalse$5 = $_9owqcy1qje5o2q0e.isContentEditableFalse;
  var isText$6 = $_9owqcy1qje5o2q0e.isText;
  var isElement$6 = $_9owqcy1qje5o2q0e.isElement;
  var isBr$4 = $_9owqcy1qje5o2q0e.isBr;
  var isCaretCandidate$3 = isCaretCandidate;
  var isAtomic$1 = isAtomic;
  var isEditableCaretCandidate$1 = isEditableCaretCandidate;
  var getParents = function (node, root) {
    var parents = [];
    while (node && node !== root) {
      parents.push(node);
      node = node.parentNode;
    }
    return parents;
  };
  var nodeAtIndex = function (container, offset) {
    if (container.hasChildNodes() && offset < container.childNodes.length) {
      return container.childNodes[offset];
    }
    return null;
  };
  var getCaretCandidatePosition = function (direction, node) {
    if (isForwards(direction)) {
      if (isCaretCandidate$3(node.previousSibling) && !isText$6(node.previousSibling)) {
        return CaretPosition$1.before(node);
      }
      if (isText$6(node)) {
        return CaretPosition$1(node, 0);
      }
    }
    if (isBackwards(direction)) {
      if (isCaretCandidate$3(node.nextSibling) && !isText$6(node.nextSibling)) {
        return CaretPosition$1.after(node);
      }
      if (isText$6(node)) {
        return CaretPosition$1(node, node.data.length);
      }
    }
    if (isBackwards(direction)) {
      if (isBr$4(node)) {
        return CaretPosition$1.before(node);
      }
      return CaretPosition$1.after(node);
    }
    return CaretPosition$1.before(node);
  };
  var isBrBeforeBlock = function (node, root) {
    var next;
    if (!$_9owqcy1qje5o2q0e.isBr(node)) {
      return false;
    }
    next = findCaretPosition(1, CaretPosition$1.after(node), root);
    if (!next) {
      return false;
    }
    return !isInSameBlock(CaretPosition$1.before(node), CaretPosition$1.before(next), root);
  };
  var findCaretPosition = function (direction, startPos, root) {
    var node, nextNode, innerNode;
    var rootContentEditableFalseElm, caretPosition;
    if (!isElement$6(root) || !startPos) {
      return null;
    }
    if (startPos.isEqual(CaretPosition$1.after(root)) && root.lastChild) {
      caretPosition = CaretPosition$1.after(root.lastChild);
      if (isBackwards(direction) && isCaretCandidate$3(root.lastChild) && isElement$6(root.lastChild)) {
        return isBr$4(root.lastChild) ? CaretPosition$1.before(root.lastChild) : caretPosition;
      }
    } else {
      caretPosition = startPos;
    }
    var container = caretPosition.container();
    var offset = caretPosition.offset();
    if (isText$6(container)) {
      if (isBackwards(direction) && offset > 0) {
        return CaretPosition$1(container, --offset);
      }
      if (isForwards(direction) && offset < container.length) {
        return CaretPosition$1(container, ++offset);
      }
      node = container;
    } else {
      if (isBackwards(direction) && offset > 0) {
        nextNode = nodeAtIndex(container, offset - 1);
        if (isCaretCandidate$3(nextNode)) {
          if (!isAtomic$1(nextNode)) {
            innerNode = findNode(nextNode, direction, isEditableCaretCandidate$1, nextNode);
            if (innerNode) {
              if (isText$6(innerNode)) {
                return CaretPosition$1(innerNode, innerNode.data.length);
              }
              return CaretPosition$1.after(innerNode);
            }
          }
          if (isText$6(nextNode)) {
            return CaretPosition$1(nextNode, nextNode.data.length);
          }
          return CaretPosition$1.before(nextNode);
        }
      }
      if (isForwards(direction) && offset < container.childNodes.length) {
        nextNode = nodeAtIndex(container, offset);
        if (isCaretCandidate$3(nextNode)) {
          if (isBrBeforeBlock(nextNode, root)) {
            return findCaretPosition(direction, CaretPosition$1.after(nextNode), root);
          }
          if (!isAtomic$1(nextNode)) {
            innerNode = findNode(nextNode, direction, isEditableCaretCandidate$1, nextNode);
            if (innerNode) {
              if (isText$6(innerNode)) {
                return CaretPosition$1(innerNode, 0);
              }
              return CaretPosition$1.before(innerNode);
            }
          }
          if (isText$6(nextNode)) {
            return CaretPosition$1(nextNode, 0);
          }
          return CaretPosition$1.after(nextNode);
        }
      }
      node = nextNode ? nextNode : caretPosition.getNode();
    }
    if (isForwards(direction) && caretPosition.isAtEnd() || isBackwards(direction) && caretPosition.isAtStart()) {
      node = findNode(node, direction, $_ghq3nq25je5o2q5q.constant(true), root, true);
      if (isEditableCaretCandidate$1(node, root)) {
        return getCaretCandidatePosition(direction, node);
      }
    }
    nextNode = findNode(node, direction, isEditableCaretCandidate$1, root);
    rootContentEditableFalseElm = $_c2vc3pkje5o2puw.last($_c2vc3pkje5o2puw.filter(getParents(container, root), isContentEditableFalse$5));
    if (rootContentEditableFalseElm && (!nextNode || !rootContentEditableFalseElm.contains(nextNode))) {
      if (isForwards(direction)) {
        caretPosition = CaretPosition$1.after(rootContentEditableFalseElm);
      } else {
        caretPosition = CaretPosition$1.before(rootContentEditableFalseElm);
      }
      return caretPosition;
    }
    if (nextNode) {
      return getCaretCandidatePosition(direction, nextNode);
    }
    return null;
  };
  var CaretWalker = function (root) {
    return {
      next: function (caretPosition) {
        return findCaretPosition(HDirection.Forwards, caretPosition, root);
      },
      prev: function (caretPosition) {
        return findCaretPosition(HDirection.Backwards, caretPosition, root);
      }
    };
  };

  var hasOnlyOneChild = function (node) {
    return node.firstChild && node.firstChild === node.lastChild;
  };
  var isPaddingNode = function (node) {
    return node.name === 'br' || node.value === '\xA0';
  };
  var isPaddedEmptyBlock = function (schema, node) {
    var blockElements = schema.getBlockElements();
    return blockElements[node.name] && hasOnlyOneChild(node) && isPaddingNode(node.firstChild);
  };
  var isEmptyFragmentElement = function (schema, node) {
    var nonEmptyElements = schema.getNonEmptyElements();
    return node && (node.isEmpty(nonEmptyElements) || isPaddedEmptyBlock(schema, node));
  };
  var isListFragment = function (schema, fragment) {
    var firstChild = fragment.firstChild;
    var lastChild = fragment.lastChild;
    if (firstChild && firstChild.name === 'meta') {
      firstChild = firstChild.next;
    }
    if (lastChild && lastChild.attr('id') === 'mce_marker') {
      lastChild = lastChild.prev;
    }
    if (isEmptyFragmentElement(schema, lastChild)) {
      lastChild = lastChild.prev;
    }
    if (!firstChild || firstChild !== lastChild) {
      return false;
    }
    return firstChild.name === 'ul' || firstChild.name === 'ol';
  };
  var cleanupDomFragment = function (domFragment) {
    var firstChild = domFragment.firstChild;
    var lastChild = domFragment.lastChild;
    if (firstChild && firstChild.nodeName === 'META') {
      firstChild.parentNode.removeChild(firstChild);
    }
    if (lastChild && lastChild.id === 'mce_marker') {
      lastChild.parentNode.removeChild(lastChild);
    }
    return domFragment;
  };
  var toDomFragment = function (dom, serializer, fragment) {
    var html = serializer.serialize(fragment);
    var domFragment = dom.createFragment(html);
    return cleanupDomFragment(domFragment);
  };
  var listItems$1 = function (elm) {
    return $_a5gegtjje5o2pum.grep(elm.childNodes, function (child) {
      return child.nodeName === 'LI';
    });
  };
  var isPadding = function (node) {
    return node.data === '\xA0' || $_9owqcy1qje5o2q0e.isBr(node);
  };
  var isListItemPadded = function (node) {
    return node && node.firstChild && node.firstChild === node.lastChild && isPadding(node.firstChild);
  };
  var isEmptyOrPadded = function (elm) {
    return !elm.firstChild || isListItemPadded(elm);
  };
  var trimListItems = function (elms) {
    return elms.length > 0 && isEmptyOrPadded(elms[elms.length - 1]) ? elms.slice(0, -1) : elms;
  };
  var getParentLi = function (dom, node) {
    var parentBlock = dom.getParent(node, dom.isBlock);
    return parentBlock && parentBlock.nodeName === 'LI' ? parentBlock : null;
  };
  var isParentBlockLi = function (dom, node) {
    return !!getParentLi(dom, node);
  };
  var getSplit = function (parentNode, rng) {
    var beforeRng = rng.cloneRange();
    var afterRng = rng.cloneRange();
    beforeRng.setStartBefore(parentNode);
    afterRng.setEndAfter(parentNode);
    return [
      beforeRng.cloneContents(),
      afterRng.cloneContents()
    ];
  };
  var findFirstIn = function (node, rootNode) {
    var caretPos = CaretPosition$1.before(node);
    var caretWalker = CaretWalker(rootNode);
    var newCaretPos = caretWalker.next(caretPos);
    return newCaretPos ? newCaretPos.toRange() : null;
  };
  var findLastOf = function (node, rootNode) {
    var caretPos = CaretPosition$1.after(node);
    var caretWalker = CaretWalker(rootNode);
    var newCaretPos = caretWalker.prev(caretPos);
    return newCaretPos ? newCaretPos.toRange() : null;
  };
  var insertMiddle = function (target, elms, rootNode, rng) {
    var parts = getSplit(target, rng);
    var parentElm = target.parentNode;
    parentElm.insertBefore(parts[0], target);
    $_a5gegtjje5o2pum.each(elms, function (li) {
      parentElm.insertBefore(li, target);
    });
    parentElm.insertBefore(parts[1], target);
    parentElm.removeChild(target);
    return findLastOf(elms[elms.length - 1], rootNode);
  };
  var insertBefore = function (target, elms, rootNode) {
    var parentElm = target.parentNode;
    $_a5gegtjje5o2pum.each(elms, function (elm) {
      parentElm.insertBefore(elm, target);
    });
    return findFirstIn(target, rootNode);
  };
  var insertAfter = function (target, elms, rootNode, dom) {
    dom.insertAfter(elms.reverse(), target);
    return findLastOf(elms[0], rootNode);
  };
  var insertAtCaret = function (serializer, dom, rng, fragment) {
    var domFragment = toDomFragment(dom, serializer, fragment);
    var liTarget = getParentLi(dom, rng.startContainer);
    var liElms = trimListItems(listItems$1(domFragment.firstChild));
    var BEGINNING = 1, END = 2;
    var rootNode = dom.getRoot();
    var isAt = function (location) {
      var caretPos = CaretPosition$1.fromRangeStart(rng);
      var caretWalker = CaretWalker(dom.getRoot());
      var newPos = location === BEGINNING ? caretWalker.prev(caretPos) : caretWalker.next(caretPos);
      return newPos ? getParentLi(dom, newPos.getNode()) !== liTarget : true;
    };
    if (isAt(BEGINNING)) {
      return insertBefore(liTarget, liElms, rootNode);
    } else if (isAt(END)) {
      return insertAfter(liTarget, liElms, rootNode, dom);
    }
    return insertMiddle(liTarget, liElms, rootNode, rng);
  };
  var $_3j1y5o1xje5o2q4o = {
    isListFragment: isListFragment,
    insertAtCaret: insertAtCaret,
    isParentBlockLi: isParentBlockLi,
    trimListItems: trimListItems,
    listItems: listItems$1
  };

  var isText$7 = $_9owqcy1qje5o2q0e.isText;
  var isBogus$1 = $_9owqcy1qje5o2q0e.isBogus;
  var nodeIndex$2 = DOMUtils.nodeIndex;
  var normalizedParent = function (node) {
    var parentNode = node.parentNode;
    if (isBogus$1(parentNode)) {
      return normalizedParent(parentNode);
    }
    return parentNode;
  };
  var getChildNodes = function (node) {
    if (!node) {
      return [];
    }
    return $_c2vc3pkje5o2puw.reduce(node.childNodes, function (result, node) {
      if (isBogus$1(node) && node.nodeName !== 'BR') {
        result = result.concat(getChildNodes(node));
      } else {
        result.push(node);
      }
      return result;
    }, []);
  };
  var normalizedTextOffset = function (node, offset) {
    while (node = node.previousSibling) {
      if (!isText$7(node)) {
        break;
      }
      offset += node.data.length;
    }
    return offset;
  };
  var equal$1 = function (a) {
    return function (b) {
      return a === b;
    };
  };
  var normalizedNodeIndex = function (node) {
    var nodes, index, numTextFragments;
    nodes = getChildNodes(normalizedParent(node));
    index = $_c2vc3pkje5o2puw.findIndex(nodes, equal$1(node), node);
    nodes = nodes.slice(0, index + 1);
    numTextFragments = $_c2vc3pkje5o2puw.reduce(nodes, function (result, node, i) {
      if (isText$7(node) && isText$7(nodes[i - 1])) {
        result++;
      }
      return result;
    }, 0);
    nodes = $_c2vc3pkje5o2puw.filter(nodes, $_9owqcy1qje5o2q0e.matchNodeNames(node.nodeName));
    index = $_c2vc3pkje5o2puw.findIndex(nodes, equal$1(node), node);
    return index - numTextFragments;
  };
  var createPathItem = function (node) {
    var name;
    if (isText$7(node)) {
      name = 'text()';
    } else {
      name = node.nodeName.toLowerCase();
    }
    return name + '[' + normalizedNodeIndex(node) + ']';
  };
  var parentsUntil = function (root, node, predicate) {
    var parents = [];
    for (node = node.parentNode; node !== root; node = node.parentNode) {
      if (predicate && predicate(node)) {
        break;
      }
      parents.push(node);
    }
    return parents;
  };
  var create$1 = function (root, caretPosition) {
    var container, offset, path = [], outputOffset, childNodes, parents;
    container = caretPosition.container();
    offset = caretPosition.offset();
    if (isText$7(container)) {
      outputOffset = normalizedTextOffset(container, offset);
    } else {
      childNodes = container.childNodes;
      if (offset >= childNodes.length) {
        outputOffset = 'after';
        offset = childNodes.length - 1;
      } else {
        outputOffset = 'before';
      }
      container = childNodes[offset];
    }
    path.push(createPathItem(container));
    parents = parentsUntil(root, container);
    parents = $_c2vc3pkje5o2puw.filter(parents, $_ghq3nq25je5o2q5q.negate($_9owqcy1qje5o2q0e.isBogus));
    path = path.concat($_c2vc3pkje5o2puw.map(parents, function (node) {
      return createPathItem(node);
    }));
    return path.reverse().join('/') + ',' + outputOffset;
  };
  var resolvePathItem = function (node, name, index) {
    var nodes = getChildNodes(node);
    nodes = $_c2vc3pkje5o2puw.filter(nodes, function (node, index) {
      return !isText$7(node) || !isText$7(nodes[index - 1]);
    });
    nodes = $_c2vc3pkje5o2puw.filter(nodes, $_9owqcy1qje5o2q0e.matchNodeNames(name));
    return nodes[index];
  };
  var findTextPosition = function (container, offset) {
    var node = container, targetOffset = 0, dataLen;
    while (isText$7(node)) {
      dataLen = node.data.length;
      if (offset >= targetOffset && offset <= targetOffset + dataLen) {
        container = node;
        offset = offset - targetOffset;
        break;
      }
      if (!isText$7(node.nextSibling)) {
        container = node;
        offset = dataLen;
        break;
      }
      targetOffset += dataLen;
      node = node.nextSibling;
    }
    if (isText$7(container) && offset > container.data.length) {
      offset = container.data.length;
    }
    return CaretPosition$1(container, offset);
  };
  var resolve$2 = function (root, path) {
    var parts, container, offset;
    if (!path) {
      return null;
    }
    parts = path.split(',');
    path = parts[0].split('/');
    offset = parts.length > 1 ? parts[1] : 'before';
    container = $_c2vc3pkje5o2puw.reduce(path, function (result, value) {
      value = /([\w\-\(\)]+)\[([0-9]+)\]/.exec(value);
      if (!value) {
        return null;
      }
      if (value[1] === 'text()') {
        value[1] = '#text';
      }
      return resolvePathItem(result, value[1], parseInt(value[2], 10));
    }, root);
    if (!container) {
      return null;
    }
    if (!isText$7(container)) {
      if (offset === 'after') {
        offset = nodeIndex$2(container) + 1;
      } else {
        offset = nodeIndex$2(container);
      }
      return CaretPosition$1(container.parentNode, offset);
    }
    return findTextPosition(container, parseInt(offset, 10));
  };

  var isContentEditableFalse$6 = $_9owqcy1qje5o2q0e.isContentEditableFalse;
  var getNormalizedTextOffset = function (trim, container, offset) {
    var node, trimmedOffset;
    trimmedOffset = trim(container.data.slice(0, offset)).length;
    for (node = container.previousSibling; node && $_9owqcy1qje5o2q0e.isText(node); node = node.previousSibling) {
      trimmedOffset += trim(node.data).length;
    }
    return trimmedOffset;
  };
  var getPoint = function (dom, trim, normalized, rng, start) {
    var container = rng[start ? 'startContainer' : 'endContainer'];
    var offset = rng[start ? 'startOffset' : 'endOffset'];
    var point = [];
    var childNodes, after = 0;
    var root = dom.getRoot();
    if ($_9owqcy1qje5o2q0e.isText(container)) {
      point.push(normalized ? getNormalizedTextOffset(trim, container, offset) : offset);
    } else {
      childNodes = container.childNodes;
      if (offset >= childNodes.length && childNodes.length) {
        after = 1;
        offset = Math.max(0, childNodes.length - 1);
      }
      point.push(dom.nodeIndex(childNodes[offset], normalized) + after);
    }
    for (; container && container !== root; container = container.parentNode) {
      point.push(dom.nodeIndex(container, normalized));
    }
    return point;
  };
  var getLocation = function (trim, selection, normalized, rng) {
    var dom = selection.dom, bookmark = {};
    bookmark.start = getPoint(dom, trim, normalized, rng, true);
    if (!selection.isCollapsed()) {
      bookmark.end = getPoint(dom, trim, normalized, rng, false);
    }
    return bookmark;
  };
  var trimEmptyTextNode = function (node) {
    if ($_9owqcy1qje5o2q0e.isText(node) && node.data.length === 0) {
      node.parentNode.removeChild(node);
    }
  };
  var findIndex$3 = function (dom, name, element) {
    var count = 0;
    $_a5gegtjje5o2pum.each(dom.select(name), function (node) {
      if (node.getAttribute('data-mce-bogus') === 'all') {
        return;
      }
      if (node === element) {
        return false;
      }
      count++;
    });
    return count;
  };
  var moveEndPoint = function (rng, start) {
    var container, offset, childNodes;
    var prefix = start ? 'start' : 'end';
    container = rng[prefix + 'Container'];
    offset = rng[prefix + 'Offset'];
    if ($_9owqcy1qje5o2q0e.isElement(container) && container.nodeName === 'TR') {
      childNodes = container.childNodes;
      container = childNodes[Math.min(start ? offset : offset - 1, childNodes.length - 1)];
      if (container) {
        offset = start ? 0 : container.childNodes.length;
        rng['set' + (start ? 'Start' : 'End')](container, offset);
      }
    }
  };
  var normalizeTableCellSelection = function (rng) {
    moveEndPoint(rng, true);
    moveEndPoint(rng, false);
    return rng;
  };
  var findSibling = function (node, offset) {
    var sibling;
    if ($_9owqcy1qje5o2q0e.isElement(node)) {
      node = getNode(node, offset);
      if (isContentEditableFalse$6(node)) {
        return node;
      }
    }
    if (isCaretContainer(node)) {
      if ($_9owqcy1qje5o2q0e.isText(node) && isCaretContainerBlock(node)) {
        node = node.parentNode;
      }
      sibling = node.previousSibling;
      if (isContentEditableFalse$6(sibling)) {
        return sibling;
      }
      sibling = node.nextSibling;
      if (isContentEditableFalse$6(sibling)) {
        return sibling;
      }
    }
  };
  var findAdjacentContentEditableFalseElm = function (rng) {
    return findSibling(rng.startContainer, rng.startOffset) || findSibling(rng.endContainer, rng.endOffset);
  };
  var getOffsetBookmark = function (trim, normalized, selection) {
    var element = selection.getNode();
    var name = element ? element.nodeName : null;
    var rng = selection.getRng();
    if (isContentEditableFalse$6(element) || name === 'IMG') {
      return {
        name: name,
        index: findIndex$3(selection.dom, name, element)
      };
    }
    element = findAdjacentContentEditableFalseElm(rng);
    if (element) {
      name = element.tagName;
      return {
        name: name,
        index: findIndex$3(selection.dom, name, element)
      };
    }
    return getLocation(trim, selection, normalized, rng);
  };
  var getCaretBookmark = function (selection) {
    var rng = selection.getRng();
    return {
      start: create$1(selection.dom.getRoot(), CaretPosition$1.fromRangeStart(rng)),
      end: create$1(selection.dom.getRoot(), CaretPosition$1.fromRangeEnd(rng))
    };
  };
  var getRangeBookmark = function (selection) {
    return { rng: selection.getRng() };
  };
  var getPersistentBookmark = function (selection) {
    var dom = selection.dom;
    var rng = selection.getRng();
    var id = dom.uniqueId();
    var collapsed = selection.isCollapsed();
    var styles = 'overflow:hidden;line-height:0px';
    var element = selection.getNode();
    var name = element.nodeName;
    var chr = '&#xFEFF;';
    if (name === 'IMG') {
      return {
        name: name,
        index: findIndex$3(dom, name, element)
      };
    }
    var rng2 = normalizeTableCellSelection(rng.cloneRange());
    if (!collapsed) {
      rng2.collapse(false);
      var endBookmarkNode = dom.create('span', {
        'data-mce-type': 'bookmark',
        'id': id + '_end',
        'style': styles
      }, chr);
      rng2.insertNode(endBookmarkNode);
      trimEmptyTextNode(endBookmarkNode.nextSibling);
    }
    rng = normalizeTableCellSelection(rng);
    rng.collapse(true);
    var startBookmarkNode = dom.create('span', {
      'data-mce-type': 'bookmark',
      'id': id + '_start',
      'style': styles
    }, chr);
    rng.insertNode(startBookmarkNode);
    trimEmptyTextNode(startBookmarkNode.previousSibling);
    selection.moveToBookmark({
      id: id,
      keep: 1
    });
    return { id: id };
  };
  var getBookmark = function (selection, type, normalized) {
    if (type === 2) {
      return getOffsetBookmark($_4md31c21je5o2q5f.trim, normalized, selection);
    } else if (type === 3) {
      return getCaretBookmark(selection);
    } else if (type) {
      return getRangeBookmark(selection);
    } else {
      return getPersistentBookmark(selection);
    }
  };
  var $_9eyzd52kje5o2q8r = {
    getBookmark: getBookmark,
    getUndoBookmark: $_enfhkv6je5o2ply.curry(getOffsetBookmark, $_enfhkv6je5o2ply.identity, true)
  };

  var addBogus = function (dom, node) {
    if (dom.isBlock(node) && !node.innerHTML && !$_9bd8pn9je5o2pp2.ie) {
      node.innerHTML = '<br data-mce-bogus="1" />';
    }
    return node;
  };
  var resolveCaretPositionBookmark = function (dom, bookmark) {
    var rng, pos;
    rng = dom.createRng();
    pos = resolve$2(dom.getRoot(), bookmark.start);
    rng.setStart(pos.container(), pos.offset());
    pos = resolve$2(dom.getRoot(), bookmark.end);
    rng.setEnd(pos.container(), pos.offset());
    return rng;
  };
  var setEndPoint = function (dom, start, bookmark, rng) {
    var point = bookmark[start ? 'start' : 'end'];
    var i, node, offset, children;
    var root = dom.getRoot();
    if (point) {
      offset = point[0];
      for (node = root, i = point.length - 1; i >= 1; i--) {
        children = node.childNodes;
        if (point[i] > children.length - 1) {
          return;
        }
        node = children[point[i]];
      }
      if (node.nodeType === 3) {
        offset = Math.min(point[0], node.nodeValue.length);
      }
      if (node.nodeType === 1) {
        offset = Math.min(point[0], node.childNodes.length);
      }
      if (start) {
        rng.setStart(node, offset);
      } else {
        rng.setEnd(node, offset);
      }
    }
    return true;
  };
  var restoreEndPoint = function (dom, suffix, bookmark) {
    var marker = dom.get(bookmark.id + '_' + suffix), node, idx, next, prev;
    var keep = bookmark.keep;
    var container, offset;
    if (marker) {
      node = marker.parentNode;
      if (suffix === 'start') {
        if (!keep) {
          idx = dom.nodeIndex(marker);
        } else {
          node = marker.firstChild;
          idx = 1;
        }
        container = node;
        offset = idx;
      } else {
        if (!keep) {
          idx = dom.nodeIndex(marker);
        } else {
          node = marker.firstChild;
          idx = 1;
        }
        container = node;
        offset = idx;
      }
      if (!keep) {
        prev = marker.previousSibling;
        next = marker.nextSibling;
        $_a5gegtjje5o2pum.each($_a5gegtjje5o2pum.grep(marker.childNodes), function (node) {
          if ($_9owqcy1qje5o2q0e.isText(node)) {
            node.nodeValue = node.nodeValue.replace(/\uFEFF/g, '');
          }
        });
        while (marker = dom.get(bookmark.id + '_' + suffix)) {
          dom.remove(marker, 1);
        }
        if (prev && next && prev.nodeType === next.nodeType && $_9owqcy1qje5o2q0e.isText(prev) && !$_9bd8pn9je5o2pp2.opera) {
          idx = prev.nodeValue.length;
          prev.appendData(next.nodeValue);
          dom.remove(next);
          if (suffix === 'start') {
            container = prev;
            offset = idx;
          } else {
            container = prev;
            offset = idx;
          }
        }
      }
      return Option.some(CaretPosition$1(container, offset));
    } else {
      return Option.none();
    }
  };
  var alt = function (o1, o2) {
    return o1.isSome() ? o1 : o2;
  };
  var resolvePaths = function (dom, bookmark) {
    var rng = dom.createRng();
    if (setEndPoint(dom, true, bookmark, rng) && setEndPoint(dom, false, bookmark, rng)) {
      return Option.some(rng);
    } else {
      return Option.none();
    }
  };
  var resolveId = function (dom, bookmark) {
    var startPos = restoreEndPoint(dom, 'start', bookmark);
    var endPos = restoreEndPoint(dom, 'end', bookmark);
    return $_c8weyc2eje5o2q85.liftN([
      startPos,
      alt(endPos, startPos)
    ], function (spos, epos) {
      var rng = dom.createRng();
      rng.setStart(addBogus(dom, spos.container()), spos.offset());
      rng.setEnd(addBogus(dom, epos.container()), epos.offset());
      return rng;
    });
  };
  var resolveIndex$1 = function (dom, bookmark) {
    return Option.from(dom.select(bookmark.name)[bookmark.index]).map(function (elm) {
      var rng = dom.createRng();
      rng.selectNode(elm);
      return rng;
    });
  };
  var resolve$3 = function (selection, bookmark) {
    var dom = selection.dom;
    if (bookmark) {
      if ($_a5gegtjje5o2pum.isArray(bookmark.start)) {
        return resolvePaths(dom, bookmark);
      } else if (typeof bookmark.start === 'string') {
        return Option.some(resolveCaretPositionBookmark(dom, bookmark));
      } else if (bookmark.id) {
        return resolveId(dom, bookmark);
      } else if (bookmark.name) {
        return resolveIndex$1(dom, bookmark);
      } else if (bookmark.rng) {
        return Option.some(bookmark.rng);
      }
    }
    return Option.none();
  };
  var $_eb5pra2mje5o2q9j = { resolve: resolve$3 };

  var getBookmark$1 = function (selection, type, normalized) {
    return $_9eyzd52kje5o2q8r.getBookmark(selection, type, normalized);
  };
  var moveToBookmark = function (selection, bookmark) {
    $_eb5pra2mje5o2q9j.resolve(selection, bookmark).each(function (rng) {
      selection.setRng(rng);
    });
  };
  var isBookmarkNode$1 = function (node) {
    return $_9owqcy1qje5o2q0e.isElement(node) && node.tagName === 'SPAN' && node.getAttribute('data-mce-type') === 'bookmark';
  };
  var $_83h91t2jje5o2q8n = {
    getBookmark: getBookmark$1,
    moveToBookmark: moveToBookmark,
    isBookmarkNode: isBookmarkNode$1
  };

  var each$8 = $_a5gegtjje5o2pum.each;
  var ElementUtils = function (dom) {
    this.compare = function (node1, node2) {
      if (node1.nodeName !== node2.nodeName) {
        return false;
      }
      var getAttribs = function (node) {
        var attribs = {};
        each$8(dom.getAttribs(node), function (attr) {
          var name = attr.nodeName.toLowerCase();
          if (name.indexOf('_') !== 0 && name !== 'style' && name.indexOf('data-') !== 0) {
            attribs[name] = dom.getAttrib(node, name);
          }
        });
        return attribs;
      };
      var compareObjects = function (obj1, obj2) {
        var value, name;
        for (name in obj1) {
          if (obj1.hasOwnProperty(name)) {
            value = obj2[name];
            if (typeof value === 'undefined') {
              return false;
            }
            if (obj1[name] !== value) {
              return false;
            }
            delete obj2[name];
          }
        }
        for (name in obj2) {
          if (obj2.hasOwnProperty(name)) {
            return false;
          }
        }
        return true;
      };
      if (!compareObjects(getAttribs(node1), getAttribs(node2))) {
        return false;
      }
      if (!compareObjects(dom.parseStyle(dom.getAttrib(node1, 'style')), dom.parseStyle(dom.getAttrib(node2, 'style')))) {
        return false;
      }
      return !$_83h91t2jje5o2q8n.isBookmarkNode(node1) && !$_83h91t2jje5o2q8n.isBookmarkNode(node2);
    };
  };

  var before$1 = function (marker, element) {
    var parent = $_a6mttx17je5o2pyd.parent(marker);
    parent.each(function (v) {
      v.dom().insertBefore(element.dom(), marker.dom());
    });
  };
  var after$1 = function (marker, element) {
    var sibling = $_a6mttx17je5o2pyd.nextSibling(marker);
    sibling.fold(function () {
      var parent = $_a6mttx17je5o2pyd.parent(marker);
      parent.each(function (v) {
        append(v, element);
      });
    }, function (v) {
      before$1(v, element);
    });
  };
  var prepend = function (parent, element) {
    var firstChild = $_a6mttx17je5o2pyd.firstChild(parent);
    firstChild.fold(function () {
      append(parent, element);
    }, function (v) {
      parent.dom().insertBefore(element.dom(), v.dom());
    });
  };
  var append = function (parent, element) {
    parent.dom().appendChild(element.dom());
  };
  var appendAt = function (parent, element, index) {
    $_a6mttx17je5o2pyd.child(parent, index).fold(function () {
      append(parent, element);
    }, function (v) {
      before$1(v, element);
    });
  };
  var wrap$1 = function (element, wrapper) {
    before$1(element, wrapper);
    append(wrapper, element);
  };
  var $_elbl2s2oje5o2q9z = {
    before: before$1,
    after: after$1,
    prepend: prepend,
    append: append,
    appendAt: appendAt,
    wrap: wrap$1
  };

  var before$2 = function (marker, elements) {
    $_bwcteb4je5o2pl4.each(elements, function (x) {
      $_elbl2s2oje5o2q9z.before(marker, x);
    });
  };
  var after$2 = function (marker, elements) {
    $_bwcteb4je5o2pl4.each(elements, function (x, i) {
      var e = i === 0 ? marker : elements[i - 1];
      $_elbl2s2oje5o2q9z.after(e, x);
    });
  };
  var prepend$1 = function (parent, elements) {
    $_bwcteb4je5o2pl4.each(elements.slice().reverse(), function (x) {
      $_elbl2s2oje5o2q9z.prepend(parent, x);
    });
  };
  var append$1 = function (parent, elements) {
    $_bwcteb4je5o2pl4.each(elements, function (x) {
      $_elbl2s2oje5o2q9z.append(parent, x);
    });
  };
  var $_93ro7k2qje5o2qa4 = {
    before: before$2,
    after: after$2,
    prepend: prepend$1,
    append: append$1
  };

  var empty = function (element) {
    element.dom().textContent = '';
    $_bwcteb4je5o2pl4.each($_a6mttx17je5o2pyd.children(element), function (rogue) {
      remove$3(rogue);
    });
  };
  var remove$3 = function (element) {
    var dom = element.dom();
    if (dom.parentNode !== null)
      dom.parentNode.removeChild(dom);
  };
  var unwrap = function (wrapper) {
    var children = $_a6mttx17je5o2pyd.children(wrapper);
    if (children.length > 0)
      $_93ro7k2qje5o2qa4.before(wrapper, children);
    remove$3(wrapper);
  };
  var $_cr1seh2pje5o2qa1 = {
    empty: empty,
    remove: remove$3,
    unwrap: unwrap
  };

  function NodeValue (is, name) {
    var get = function (element) {
      if (!is(element))
        throw new Error('Can only get ' + name + ' value of a ' + name + ' node');
      return getOption(element).getOr('');
    };
    var getOptionIE10 = function (element) {
      try {
        return getOptionSafe(element);
      } catch (e) {
        return Option.none();
      }
    };
    var getOptionSafe = function (element) {
      return is(element) ? Option.from(element.dom().nodeValue) : Option.none();
    };
    var browser = $_2hq237mje5o2pvu.detect().browser;
    var getOption = browser.isIE() && browser.version.major === 10 ? getOptionIE10 : getOptionSafe;
    var set = function (element, value) {
      if (!is(element))
        throw new Error('Can only set raw ' + name + ' value of a ' + name + ' node');
      element.dom().nodeValue = value;
    };
    return {
      get: get,
      getOption: getOption,
      set: set
    };
  }

  var api = NodeValue($_95wn8qzje5o2px0.isText, 'text');
  var get$2 = function (element) {
    return api.get(element);
  };
  var getOption = function (element) {
    return api.getOption(element);
  };
  var set$2 = function (element, value) {
    api.set(element, value);
  };
  var $_3600ts2rje5o2qa7 = {
    get: get$2,
    getOption: getOption,
    set: set$2
  };

  var getLastChildren = function (elm) {
    var children = [];
    var rawNode = elm.dom();
    while (rawNode) {
      children.push($_5tcb2dyje5o2pwu.fromDom(rawNode));
      rawNode = rawNode.lastChild;
    }
    return children;
  };
  var removeTrailingBr = function (elm) {
    var allBrs = $_b4jhpu2gje5o2q8e.descendants(elm, 'br');
    var brs = $_bwcteb4je5o2pl4.filter(getLastChildren(elm).slice(-1), isBr);
    if (allBrs.length === brs.length) {
      $_bwcteb4je5o2pl4.each(brs, $_cr1seh2pje5o2qa1.remove);
    }
  };
  var fillWithPaddingBr = function (elm) {
    $_cr1seh2pje5o2qa1.empty(elm);
    $_elbl2s2oje5o2q9z.append(elm, $_5tcb2dyje5o2pwu.fromHtml('<br data-mce-bogus="1">'));
  };
  var isPaddingContents = function (elm) {
    return $_95wn8qzje5o2px0.isText(elm) ? $_3600ts2rje5o2qa7.get(elm) === '\xA0' : isBr(elm);
  };
  var isPaddedElement = function (elm) {
    return $_bwcteb4je5o2pl4.filter($_a6mttx17je5o2pyd.children(elm), isPaddingContents).length === 1;
  };
  var trimBlockTrailingBr = function (elm) {
    $_a6mttx17je5o2pyd.lastChild(elm).each(function (lastChild) {
      $_a6mttx17je5o2pyd.prevSibling(lastChild).each(function (lastChildPrevSibling) {
        if (isBlock(elm) && isBr(lastChild) && isBlock(lastChildPrevSibling)) {
          $_cr1seh2pje5o2qa1.remove(lastChild);
        }
      });
    });
  };
  var $_4kbg8z2nje5o2q9s = {
    removeTrailingBr: removeTrailingBr,
    fillWithPaddingBr: fillWithPaddingBr,
    isPaddedElement: isPaddedElement,
    trimBlockTrailingBr: trimBlockTrailingBr
  };

  var makeMap$3 = $_a5gegtjje5o2pum.makeMap;
  function Writer (settings) {
    var html = [];
    var indent, indentBefore, indentAfter, encode, htmlOutput;
    settings = settings || {};
    indent = settings.indent;
    indentBefore = makeMap$3(settings.indent_before || '');
    indentAfter = makeMap$3(settings.indent_after || '');
    encode = $_fzh4ld1rje5o2q0t.getEncodeFunc(settings.entity_encoding || 'raw', settings.entities);
    htmlOutput = settings.element_format === 'html';
    return {
      start: function (name, attrs, empty) {
        var i, l, attr, value;
        if (indent && indentBefore[name] && html.length > 0) {
          value = html[html.length - 1];
          if (value.length > 0 && value !== '\n') {
            html.push('\n');
          }
        }
        html.push('<', name);
        if (attrs) {
          for (i = 0, l = attrs.length; i < l; i++) {
            attr = attrs[i];
            html.push(' ', attr.name, '="', encode(attr.value, true), '"');
          }
        }
        if (!empty || htmlOutput) {
          html[html.length] = '>';
        } else {
          html[html.length] = ' />';
        }
        if (empty && indent && indentAfter[name] && html.length > 0) {
          value = html[html.length - 1];
          if (value.length > 0 && value !== '\n') {
            html.push('\n');
          }
        }
      },
      end: function (name) {
        var value;
        html.push('</', name, '>');
        if (indent && indentAfter[name] && html.length > 0) {
          value = html[html.length - 1];
          if (value.length > 0 && value !== '\n') {
            html.push('\n');
          }
        }
      },
      text: function (text, raw) {
        if (text.length > 0) {
          html[html.length] = raw ? text : encode(text);
        }
      },
      cdata: function (text) {
        html.push('<![CDATA[', text, ']]>');
      },
      comment: function (text) {
        html.push('<!--', text, '-->');
      },
      pi: function (name, text) {
        if (text) {
          html.push('<?', name, ' ', encode(text), '?>');
        } else {
          html.push('<?', name, '?>');
        }
        if (indent) {
          html.push('\n');
        }
      },
      doctype: function (text) {
        html.push('<!DOCTYPE', text, '>', indent ? '\n' : '');
      },
      reset: function () {
        html.length = 0;
      },
      getContent: function () {
        return html.join('').replace(/\n$/, '');
      }
    };
  }

  function HtmlSerializer (settings, schema) {
    if (schema === void 0) {
      schema = Schema();
    }
    var writer = Writer(settings);
    settings = settings || {};
    settings.validate = 'validate' in settings ? settings.validate : true;
    var serialize = function (node) {
      var handlers, validate;
      validate = settings.validate;
      handlers = {
        3: function (node) {
          writer.text(node.value, node.raw);
        },
        8: function (node) {
          writer.comment(node.value);
        },
        7: function (node) {
          writer.pi(node.name, node.value);
        },
        10: function (node) {
          writer.doctype(node.value);
        },
        4: function (node) {
          writer.cdata(node.value);
        },
        11: function (node) {
          if (node = node.firstChild) {
            do {
              walk(node);
            } while (node = node.next);
          }
        }
      };
      writer.reset();
      var walk = function (node) {
        var handler = handlers[node.type];
        var name, isEmpty, attrs, attrName, attrValue, sortedAttrs, i, l, elementRule;
        if (!handler) {
          name = node.name;
          isEmpty = node.shortEnded;
          attrs = node.attributes;
          if (validate && attrs && attrs.length > 1) {
            sortedAttrs = [];
            sortedAttrs.map = {};
            elementRule = schema.getElementRule(node.name);
            if (elementRule) {
              for (i = 0, l = elementRule.attributesOrder.length; i < l; i++) {
                attrName = elementRule.attributesOrder[i];
                if (attrName in attrs.map) {
                  attrValue = attrs.map[attrName];
                  sortedAttrs.map[attrName] = attrValue;
                  sortedAttrs.push({
                    name: attrName,
                    value: attrValue
                  });
                }
              }
              for (i = 0, l = attrs.length; i < l; i++) {
                attrName = attrs[i].name;
                if (!(attrName in sortedAttrs.map)) {
                  attrValue = attrs.map[attrName];
                  sortedAttrs.map[attrName] = attrValue;
                  sortedAttrs.push({
                    name: attrName,
                    value: attrValue
                  });
                }
              }
              attrs = sortedAttrs;
            }
          }
          writer.start(node.name, attrs, isEmpty);
          if (!isEmpty) {
            if (node = node.firstChild) {
              do {
                walk(node);
              } while (node = node.next);
            }
            writer.end(name);
          }
        } else {
          handler(node);
        }
      };
      if (node.type === 1 && !settings.inner) {
        walk(node);
      } else {
        handlers[11](node);
      }
      return writer.getContent();
    };
    return { serialize: serialize };
  }

  var createRange$1 = function (sc, so, ec, eo) {
    var rng = document.createRange();
    rng.setStart(sc, so);
    rng.setEnd(ec, eo);
    return rng;
  };
  var normalizeBlockSelectionRange = function (rng) {
    var startPos = CaretPosition$1.fromRangeStart(rng);
    var endPos = CaretPosition$1.fromRangeEnd(rng);
    var rootNode = rng.commonAncestorContainer;
    return $_g2kzjo2bje5o2q7n.fromPosition(false, rootNode, endPos).map(function (newEndPos) {
      if (!isInSameBlock(startPos, endPos, rootNode) && isInSameBlock(startPos, newEndPos, rootNode)) {
        return createRange$1(startPos.container(), startPos.offset(), newEndPos.container(), newEndPos.offset());
      } else {
        return rng;
      }
    }).getOr(rng);
  };
  var normalizeBlockSelection = function (rng) {
    return rng.collapsed ? rng : normalizeBlockSelectionRange(rng);
  };
  var normalize = function (rng) {
    return normalizeBlockSelection(rng);
  };
  var $_8xjhg22vje5o2qak = { normalize: normalize };

  var isTableCell$2 = $_9owqcy1qje5o2q0e.matchNodeNames('td th');
  var validInsertion = function (editor, value, parentNode) {
    if (parentNode.getAttribute('data-mce-bogus') === 'all') {
      parentNode.parentNode.insertBefore(editor.dom.createFragment(value), parentNode);
    } else {
      var node = parentNode.firstChild;
      var node2 = parentNode.lastChild;
      if (!node || node === node2 && node.nodeName === 'BR') {
        editor.dom.setHTML(parentNode, value);
      } else {
        editor.selection.setContent(value);
      }
    }
  };
  var trimBrsFromTableCell = function (dom, elm) {
    Option.from(dom.getParent(elm, 'td,th')).map($_5tcb2dyje5o2pwu.fromDom).each($_4kbg8z2nje5o2q9s.trimBlockTrailingBr);
  };
  var insertHtmlAtCaret = function (editor, value, details) {
    var parser, serializer, parentNode, rootNode, fragment, args;
    var marker, rng, node, node2, bookmarkHtml, merge;
    var textInlineElements = editor.schema.getTextInlineElements();
    var selection = editor.selection, dom = editor.dom;
    var trimOrPaddLeftRight = function (html) {
      var rng, container, offset;
      rng = selection.getRng();
      container = rng.startContainer;
      offset = rng.startOffset;
      var hasSiblingText = function (siblingName) {
        return container[siblingName] && container[siblingName].nodeType === 3;
      };
      if (container.nodeType === 3) {
        if (offset > 0) {
          html = html.replace(/^&nbsp;/, ' ');
        } else if (!hasSiblingText('previousSibling')) {
          html = html.replace(/^ /, '&nbsp;');
        }
        if (offset < container.length) {
          html = html.replace(/&nbsp;(<br>|)$/, ' ');
        } else if (!hasSiblingText('nextSibling')) {
          html = html.replace(/(&nbsp;| )(<br>|)$/, '&nbsp;');
        }
      }
      return html;
    };
    var trimNbspAfterDeleteAndPaddValue = function () {
      var rng, container, offset;
      rng = selection.getRng();
      container = rng.startContainer;
      offset = rng.startOffset;
      if (container.nodeType === 3 && rng.collapsed) {
        if (container.data[offset] === '\xA0') {
          container.deleteData(offset, 1);
          if (!/[\u00a0| ]$/.test(value)) {
            value += ' ';
          }
        } else if (container.data[offset - 1] === '\xA0') {
          container.deleteData(offset - 1, 1);
          if (!/[\u00a0| ]$/.test(value)) {
            value = ' ' + value;
          }
        }
      }
    };
    var reduceInlineTextElements = function () {
      if (merge) {
        var root_1 = editor.getBody(), elementUtils_1 = new ElementUtils(dom);
        $_a5gegtjje5o2pum.each(dom.select('*[data-mce-fragment]'), function (node) {
          for (var testNode = node.parentNode; testNode && testNode !== root_1; testNode = testNode.parentNode) {
            if (textInlineElements[node.nodeName.toLowerCase()] && elementUtils_1.compare(testNode, node)) {
              dom.remove(node, true);
            }
          }
        });
      }
    };
    var markFragmentElements = function (fragment) {
      var node = fragment;
      while (node = node.walk()) {
        if (node.type === 1) {
          node.attr('data-mce-fragment', '1');
        }
      }
    };
    var umarkFragmentElements = function (elm) {
      $_a5gegtjje5o2pum.each(elm.getElementsByTagName('*'), function (elm) {
        elm.removeAttribute('data-mce-fragment');
      });
    };
    var isPartOfFragment = function (node) {
      return !!node.getAttribute('data-mce-fragment');
    };
    var canHaveChildren = function (node) {
      return node && !editor.schema.getShortEndedElements()[node.nodeName];
    };
    var moveSelectionToMarker = function (marker) {
      var parentEditableFalseElm, parentBlock, nextRng;
      var getContentEditableFalseParent = function (node) {
        var root = editor.getBody();
        for (; node && node !== root; node = node.parentNode) {
          if (editor.dom.getContentEditable(node) === 'false') {
            return node;
          }
        }
        return null;
      };
      if (!marker) {
        return;
      }
      selection.scrollIntoView(marker);
      parentEditableFalseElm = getContentEditableFalseParent(marker);
      if (parentEditableFalseElm) {
        dom.remove(marker);
        selection.select(parentEditableFalseElm);
        return;
      }
      rng = dom.createRng();
      node = marker.previousSibling;
      if (node && node.nodeType === 3) {
        rng.setStart(node, node.nodeValue.length);
        if (!$_9bd8pn9je5o2pp2.ie) {
          node2 = marker.nextSibling;
          if (node2 && node2.nodeType === 3) {
            node.appendData(node2.data);
            node2.parentNode.removeChild(node2);
          }
        }
      } else {
        rng.setStartBefore(marker);
        rng.setEndBefore(marker);
      }
      var findNextCaretRng = function (rng) {
        var caretPos = CaretPosition$1.fromRangeStart(rng);
        var caretWalker = CaretWalker(editor.getBody());
        caretPos = caretWalker.next(caretPos);
        if (caretPos) {
          return caretPos.toRange();
        }
      };
      parentBlock = dom.getParent(marker, dom.isBlock);
      dom.remove(marker);
      if (parentBlock && dom.isEmpty(parentBlock)) {
        editor.$(parentBlock).empty();
        rng.setStart(parentBlock, 0);
        rng.setEnd(parentBlock, 0);
        if (!isTableCell$2(parentBlock) && !isPartOfFragment(parentBlock) && (nextRng = findNextCaretRng(rng))) {
          rng = nextRng;
          dom.remove(parentBlock);
        } else {
          dom.add(parentBlock, dom.create('br', { 'data-mce-bogus': '1' }));
        }
      }
      selection.setRng(rng);
    };
    if (/^ | $/.test(value)) {
      value = trimOrPaddLeftRight(value);
    }
    parser = editor.parser;
    merge = details.merge;
    serializer = HtmlSerializer({ validate: editor.settings.validate }, editor.schema);
    bookmarkHtml = '<span id="mce_marker" data-mce-type="bookmark">&#xFEFF;&#x200B;</span>';
    args = {
      content: value,
      format: 'html',
      selection: true,
      paste: details.paste
    };
    args = editor.fire('BeforeSetContent', args);
    if (args.isDefaultPrevented()) {
      editor.fire('SetContent', {
        content: args.content,
        format: 'html',
        selection: true,
        paste: details.paste
      });
      return;
    }
    value = args.content;
    if (value.indexOf('{$caret}') === -1) {
      value += '{$caret}';
    }
    value = value.replace(/\{\$caret\}/, bookmarkHtml);
    rng = selection.getRng();
    var caretElement = rng.startContainer || (rng.parentElement ? rng.parentElement() : null);
    var body = editor.getBody();
    if (caretElement === body && selection.isCollapsed()) {
      if (dom.isBlock(body.firstChild) && canHaveChildren(body.firstChild) && dom.isEmpty(body.firstChild)) {
        rng = dom.createRng();
        rng.setStart(body.firstChild, 0);
        rng.setEnd(body.firstChild, 0);
        selection.setRng(rng);
      }
    }
    if (!selection.isCollapsed()) {
      editor.selection.setRng($_8xjhg22vje5o2qak.normalize(editor.selection.getRng()));
      editor.getDoc().execCommand('Delete', false, null);
      trimNbspAfterDeleteAndPaddValue();
    }
    parentNode = selection.getNode();
    var parserArgs = {
      context: parentNode.nodeName.toLowerCase(),
      data: details.data,
      insert: true
    };
    fragment = parser.parse(value, parserArgs);
    if (details.paste === true && $_3j1y5o1xje5o2q4o.isListFragment(editor.schema, fragment) && $_3j1y5o1xje5o2q4o.isParentBlockLi(dom, parentNode)) {
      rng = $_3j1y5o1xje5o2q4o.insertAtCaret(serializer, dom, editor.selection.getRng(true), fragment);
      editor.selection.setRng(rng);
      editor.fire('SetContent', args);
      return;
    }
    markFragmentElements(fragment);
    node = fragment.lastChild;
    if (node.attr('id') === 'mce_marker') {
      marker = node;
      for (node = node.prev; node; node = node.walk(true)) {
        if (node.type === 3 || !dom.isBlock(node.name)) {
          if (editor.schema.isValidChild(node.parent.name, 'span')) {
            node.parent.insert(marker, node, node.name === 'br');
          }
          break;
        }
      }
    }
    editor._selectionOverrides.showBlockCaretContainer(parentNode);
    if (!parserArgs.invalid) {
      value = serializer.serialize(fragment);
      validInsertion(editor, value, parentNode);
    } else {
      selection.setContent(bookmarkHtml);
      parentNode = selection.getNode();
      rootNode = editor.getBody();
      if (parentNode.nodeType === 9) {
        parentNode = node = rootNode;
      } else {
        node = parentNode;
      }
      while (node !== rootNode) {
        parentNode = node;
        node = node.parentNode;
      }
      value = parentNode === rootNode ? rootNode.innerHTML : dom.getOuterHTML(parentNode);
      value = serializer.serialize(parser.parse(value.replace(/<span (id="mce_marker"|id=mce_marker).+?<\/span>/i, function () {
        return serializer.serialize(fragment);
      })));
      if (parentNode === rootNode) {
        dom.setHTML(rootNode, value);
      } else {
        dom.setOuterHTML(parentNode, value);
      }
    }
    reduceInlineTextElements();
    moveSelectionToMarker(dom.get('mce_marker'));
    umarkFragmentElements(editor.getBody());
    trimBrsFromTableCell(editor.dom, editor.selection.getStart());
    editor.fire('SetContent', args);
    editor.addVisual();
  };
  var processValue = function (value) {
    var details;
    if (typeof value !== 'string') {
      details = $_a5gegtjje5o2pum.extend({
        paste: value.paste,
        data: { paste: value.paste }
      }, value);
      return {
        content: value.content,
        details: details
      };
    }
    return {
      content: value,
      details: {}
    };
  };
  var insertAtCaret$1 = function (editor, value) {
    var result = processValue(value);
    insertHtmlAtCaret(editor, result.content, result.details);
  };
  var $_d42uuu1wje5o2q4b = { insertAtCaret: insertAtCaret$1 };

  function ClosestOrAncestor (is, ancestor, scope, a, isRoot) {
    return is(scope, a) ? Option.some(scope) : $_be3q8w12je5o2pxj.isFunction(isRoot) && isRoot(scope) ? Option.none() : ancestor(scope, a, isRoot);
  }

  var first$1 = function (predicate) {
    return descendant($_484buq15je5o2py8.body(), predicate);
  };
  var ancestor = function (scope, predicate, isRoot) {
    var element = scope.dom();
    var stop = $_be3q8w12je5o2pxj.isFunction(isRoot) ? isRoot : $_enfhkv6je5o2ply.constant(false);
    while (element.parentNode) {
      element = element.parentNode;
      var el = $_5tcb2dyje5o2pwu.fromDom(element);
      if (predicate(el))
        return Option.some(el);
      else if (stop(el))
        break;
    }
    return Option.none();
  };
  var closest = function (scope, predicate, isRoot) {
    var is = function (scope) {
      return predicate(scope);
    };
    return ClosestOrAncestor(is, ancestor, scope, predicate, isRoot);
  };
  var sibling$1 = function (scope, predicate) {
    var element = scope.dom();
    if (!element.parentNode)
      return Option.none();
    return child$1($_5tcb2dyje5o2pwu.fromDom(element.parentNode), function (x) {
      return !$_7oppmo1dje5o2pz1.eq(scope, x) && predicate(x);
    });
  };
  var child$1 = function (scope, predicate) {
    var result = $_bwcteb4je5o2pl4.find(scope.dom().childNodes, $_enfhkv6je5o2ply.compose(predicate, $_5tcb2dyje5o2pwu.fromDom));
    return result.map($_5tcb2dyje5o2pwu.fromDom);
  };
  var descendant = function (scope, predicate) {
    var descend = function (element) {
      for (var i = 0; i < element.childNodes.length; i++) {
        if (predicate($_5tcb2dyje5o2pwu.fromDom(element.childNodes[i])))
          return Option.some($_5tcb2dyje5o2pwu.fromDom(element.childNodes[i]));
        var res = descend(element.childNodes[i]);
        if (res.isSome())
          return res;
      }
      return Option.none();
    };
    return descend(scope.dom());
  };
  var $_au2qfe30je5o2qb4 = {
    first: first$1,
    ancestor: ancestor,
    closest: closest,
    sibling: sibling$1,
    child: child$1,
    descendant: descendant
  };

  var sectionResult = $_86drv18je5o2pyn.immutable('sections', 'settings');
  var detection = $_2hq237mje5o2pvu.detect();
  var isTouch = detection.deviceType.isTouch();
  var mobilePlugins = [
    'lists',
    'autolink',
    'autosave'
  ];
  var defaultMobileSettings = { theme: 'mobile' };
  var normalizePlugins = function (plugins) {
    var pluginNames = $_be3q8w12je5o2pxj.isArray(plugins) ? plugins.join(' ') : plugins;
    var trimmedPlugins = $_bwcteb4je5o2pl4.map($_be3q8w12je5o2pxj.isString(pluginNames) ? pluginNames.split(' ') : [], $_6dh61xvje5o2pwo.trim);
    return $_bwcteb4je5o2pl4.filter(trimmedPlugins, function (item) {
      return item.length > 0;
    });
  };
  var filterMobilePlugins = function (plugins) {
    return $_bwcteb4je5o2pl4.filter(plugins, $_enfhkv6je5o2ply.curry($_bwcteb4je5o2pl4.contains, mobilePlugins));
  };
  var extractSections = function (keys, settings) {
    var result = $_8v44sy13je5o2pxm.bifilter(settings, function (value, key) {
      return $_bwcteb4je5o2pl4.contains(keys, key);
    });
    return sectionResult(result.t, result.f);
  };
  var getSection = function (sectionResult, name, defaults) {
    var sections = sectionResult.sections();
    var sectionSettings = sections.hasOwnProperty(name) ? sections[name] : {};
    return $_a5gegtjje5o2pum.extend({}, defaults, sectionSettings);
  };
  var hasSection = function (sectionResult, name) {
    return sectionResult.sections().hasOwnProperty(name);
  };
  var getDefaultSettings = function (id, documentBaseUrl, editor) {
    return {
      id: id,
      theme: 'modern',
      delta_width: 0,
      delta_height: 0,
      popup_css: '',
      plugins: '',
      document_base_url: documentBaseUrl,
      add_form_submit_trigger: true,
      submit_patch: true,
      add_unload_trigger: true,
      convert_urls: true,
      relative_urls: true,
      remove_script_host: true,
      object_resizing: true,
      doctype: '<!DOCTYPE html>',
      visual: true,
      font_size_style_values: 'xx-small,x-small,small,medium,large,x-large,xx-large',
      font_size_legacy_values: 'xx-small,small,medium,large,x-large,xx-large,300%',
      forced_root_block: 'p',
      hidden_input: true,
      padd_empty_editor: true,
      render_ui: true,
      indentation: '30px',
      inline_styles: true,
      convert_fonts_to_spans: true,
      indent: 'simple',
      indent_before: 'p,h1,h2,h3,h4,h5,h6,blockquote,div,title,style,pre,script,td,th,ul,ol,li,dl,dt,dd,area,table,thead,' + 'tfoot,tbody,tr,section,article,hgroup,aside,figure,figcaption,option,optgroup,datalist',
      indent_after: 'p,h1,h2,h3,h4,h5,h6,blockquote,div,title,style,pre,script,td,th,ul,ol,li,dl,dt,dd,area,table,thead,' + 'tfoot,tbody,tr,section,article,hgroup,aside,figure,figcaption,option,optgroup,datalist',
      entity_encoding: 'named',
      url_converter: editor.convertURL,
      url_converter_scope: editor,
      ie7_compat: true
    };
  };
  var getExternalPlugins = function (overrideSettings, settings) {
    var userDefinedExternalPlugins = settings.external_plugins ? settings.external_plugins : {};
    if (overrideSettings && overrideSettings.external_plugins) {
      return $_a5gegtjje5o2pum.extend({}, overrideSettings.external_plugins, userDefinedExternalPlugins);
    } else {
      return userDefinedExternalPlugins;
    }
  };
  var combinePlugins = function (forcedPlugins, plugins) {
    return [].concat(normalizePlugins(forcedPlugins)).concat(normalizePlugins(plugins));
  };
  var processPlugins = function (isTouchDevice, sectionResult, defaultOverrideSettings, settings) {
    var forcedPlugins = normalizePlugins(defaultOverrideSettings.forced_plugins);
    var plugins = normalizePlugins(settings.plugins);
    var platformPlugins = isTouchDevice && hasSection(sectionResult, 'mobile') ? filterMobilePlugins(plugins) : plugins;
    var combinedPlugins = combinePlugins(forcedPlugins, platformPlugins);
    return $_a5gegtjje5o2pum.extend(settings, { plugins: combinedPlugins.join(' ') });
  };
  var isOnMobile = function (isTouchDevice, sectionResult) {
    var isInline = sectionResult.settings().inline;
    return isTouchDevice && hasSection(sectionResult, 'mobile') && !isInline;
  };
  var combineSettings = function (isTouchDevice, defaultSettings, defaultOverrideSettings, settings) {
    var sectionResult = extractSections(['mobile'], settings);
    var extendedSettings = $_a5gegtjje5o2pum.extend(defaultSettings, defaultOverrideSettings, sectionResult.settings(), isOnMobile(isTouchDevice, sectionResult) ? getSection(sectionResult, 'mobile', defaultMobileSettings) : {}, {
      validate: true,
      content_editable: sectionResult.settings().inline,
      external_plugins: getExternalPlugins(defaultOverrideSettings, sectionResult.settings())
    });
    return processPlugins(isTouchDevice, sectionResult, defaultOverrideSettings, extendedSettings);
  };
  var getEditorSettings = function (editor, id, documentBaseUrl, defaultOverrideSettings, settings) {
    var defaultSettings = getDefaultSettings(id, documentBaseUrl, editor);
    return combineSettings(isTouch, defaultSettings, defaultOverrideSettings, settings);
  };
  var getFiltered = function (predicate, editor, name) {
    return Option.from(editor.settings[name]).filter(predicate);
  };
  var getString = $_enfhkv6je5o2ply.curry(getFiltered, $_be3q8w12je5o2pxj.isString);
  var getParamObject = function (value) {
    var output = {};
    if (typeof value === 'string') {
      $_bwcteb4je5o2pl4.each(value.indexOf('=') > 0 ? value.split(/[;,](?![^=;,]*(?:[;,]|$))/) : value.split(','), function (value) {
        value = value.split('=');
        if (value.length > 1) {
          output[$_a5gegtjje5o2pum.trim(value[0])] = $_a5gegtjje5o2pum.trim(value[1]);
        } else {
          output[$_a5gegtjje5o2pum.trim(value[0])] = $_a5gegtjje5o2pum.trim(value);
        }
      });
    } else {
      output = value;
    }
    return output;
  };
  var getParam = function (editor, name, defaultVal, type) {
    var value = name in editor.settings ? editor.settings[name] : defaultVal;
    if (type === 'hash') {
      return getParamObject(value);
    } else if (type === 'string') {
      return getFiltered($_be3q8w12je5o2pxj.isString, editor, name).getOr(defaultVal);
    } else if (type === 'number') {
      return getFiltered($_be3q8w12je5o2pxj.isNumber, editor, name).getOr(defaultVal);
    } else if (type === 'boolean') {
      return getFiltered($_be3q8w12je5o2pxj.isBoolean, editor, name).getOr(defaultVal);
    } else if (type === 'object') {
      return getFiltered($_be3q8w12je5o2pxj.isObject, editor, name).getOr(defaultVal);
    } else if (type === 'array') {
      return getFiltered($_be3q8w12je5o2pxj.isArray, editor, name).getOr(defaultVal);
    } else if (type === 'function') {
      return getFiltered($_be3q8w12je5o2pxj.isFunction, editor, name).getOr(defaultVal);
    } else {
      return value;
    }
  };

  var strongRtl = /[\u0591-\u07FF\uFB1D-\uFDFF\uFE70-\uFEFC]/;
  var hasStrongRtl = function (text) {
    return strongRtl.test(text);
  };

  var isInlineTarget = function (editor, elm) {
    var selector = getString(editor, 'inline_boundaries_selector').getOr('a[href],code');
    return $_87zu5k1fje5o2pz9.is($_5tcb2dyje5o2pwu.fromDom(elm), selector);
  };
  var isRtl = function (element) {
    return DOMUtils.DOM.getStyle(element, 'direction', true) === 'rtl' || hasStrongRtl(element.textContent);
  };
  var findInlineParents = function (isInlineTarget, rootNode, pos) {
    return $_bwcteb4je5o2pl4.filter(DOMUtils.DOM.getParents(pos.container(), '*', rootNode), isInlineTarget);
  };
  var findRootInline = function (isInlineTarget, rootNode, pos) {
    var parents = findInlineParents(isInlineTarget, rootNode, pos);
    return Option.from(parents[parents.length - 1]);
  };
  var hasSameParentBlock = function (rootNode, node1, node2) {
    var block1 = getParentBlock(node1, rootNode);
    var block2 = getParentBlock(node2, rootNode);
    return block1 && block1 === block2;
  };
  var isAtZwsp = function (pos) {
    return isBeforeInline(pos) || isAfterInline(pos);
  };
  var normalizePosition = function (forward, pos) {
    var container = pos.container(), offset = pos.offset();
    if (forward) {
      if (isCaretContainerInline(container)) {
        if ($_9owqcy1qje5o2q0e.isText(container.nextSibling)) {
          return CaretPosition$1(container.nextSibling, 0);
        } else {
          return CaretPosition$1.after(container);
        }
      } else {
        return isBeforeInline(pos) ? CaretPosition$1(container, offset + 1) : pos;
      }
    } else {
      if (isCaretContainerInline(container)) {
        if ($_9owqcy1qje5o2q0e.isText(container.previousSibling)) {
          return CaretPosition$1(container.previousSibling, container.previousSibling.data.length);
        } else {
          return CaretPosition$1.before(container);
        }
      } else {
        return isAfterInline(pos) ? CaretPosition$1(container, offset - 1) : pos;
      }
    }
  };
  var normalizeForwards = $_enfhkv6je5o2ply.curry(normalizePosition, true);
  var normalizeBackwards = $_enfhkv6je5o2ply.curry(normalizePosition, false);
  var $_6skw9732je5o2qbp = {
    isInlineTarget: isInlineTarget,
    findRootInline: findRootInline,
    isRtl: isRtl,
    isAtZwsp: isAtZwsp,
    normalizePosition: normalizePosition,
    normalizeForwards: normalizeForwards,
    normalizeBackwards: normalizeBackwards,
    hasSameParentBlock: hasSameParentBlock
  };

  var isBeforeRoot = function (rootNode) {
    return function (elm) {
      return $_7oppmo1dje5o2pz1.eq(rootNode, $_5tcb2dyje5o2pwu.fromDom(elm.dom().parentNode));
    };
  };
  var getParentBlock$1 = function (rootNode, elm) {
    return $_7oppmo1dje5o2pz1.contains(rootNode, elm) ? $_au2qfe30je5o2qb4.closest(elm, function (element) {
      return isTextBlock(element) || isListItem(element);
    }, isBeforeRoot(rootNode)) : Option.none();
  };
  var placeCaretInEmptyBody = function (editor) {
    var body = editor.getBody();
    var node = body.firstChild && editor.dom.isBlock(body.firstChild) ? body.firstChild : body;
    editor.selection.setCursorLocation(node, 0);
  };
  var paddEmptyBody = function (editor) {
    if (editor.dom.isEmpty(editor.getBody())) {
      editor.setContent('');
      placeCaretInEmptyBody(editor);
    }
  };
  var willDeleteLastPositionInElement = function (forward, fromPos, elm) {
    return $_c8weyc2eje5o2q85.liftN([
      $_g2kzjo2bje5o2q7n.firstPositionIn(elm),
      $_g2kzjo2bje5o2q7n.lastPositionIn(elm)
    ], function (firstPos, lastPos) {
      var normalizedFirstPos = $_6skw9732je5o2qbp.normalizePosition(true, firstPos);
      var normalizedLastPos = $_6skw9732je5o2qbp.normalizePosition(false, lastPos);
      var normalizedFromPos = $_6skw9732je5o2qbp.normalizePosition(false, fromPos);
      if (forward) {
        return $_g2kzjo2bje5o2q7n.nextPosition(elm, normalizedFromPos).map(function (nextPos) {
          return nextPos.isEqual(normalizedLastPos) && fromPos.isEqual(normalizedFirstPos);
        }).getOr(false);
      } else {
        return $_g2kzjo2bje5o2q7n.prevPosition(elm, normalizedFromPos).map(function (prevPos) {
          return prevPos.isEqual(normalizedFirstPos) && fromPos.isEqual(normalizedLastPos);
        }).getOr(false);
      }
    }).getOr(true);
  };
  var $_7twey62zje5o2qay = {
    getParentBlock: getParentBlock$1,
    paddEmptyBody: paddEmptyBody,
    willDeleteLastPositionInElement: willDeleteLastPositionInElement
  };

  var first$2 = function (selector) {
    return $_87zu5k1fje5o2pz9.one(selector);
  };
  var ancestor$1 = function (scope, selector, isRoot) {
    return $_au2qfe30je5o2qb4.ancestor(scope, function (e) {
      return $_87zu5k1fje5o2pz9.is(e, selector);
    }, isRoot);
  };
  var sibling$2 = function (scope, selector) {
    return $_au2qfe30je5o2qb4.sibling(scope, function (e) {
      return $_87zu5k1fje5o2pz9.is(e, selector);
    });
  };
  var child$2 = function (scope, selector) {
    return $_au2qfe30je5o2qb4.child(scope, function (e) {
      return $_87zu5k1fje5o2pz9.is(e, selector);
    });
  };
  var descendant$1 = function (scope, selector) {
    return $_87zu5k1fje5o2pz9.one(selector, scope);
  };
  var closest$1 = function (scope, selector, isRoot) {
    return ClosestOrAncestor($_87zu5k1fje5o2pz9.is, ancestor$1, scope, selector, isRoot);
  };
  var $_9fzewc37je5o2qck = {
    first: first$2,
    ancestor: ancestor$1,
    sibling: sibling$2,
    child: child$2,
    descendant: descendant$1,
    closest: closest$1
  };

  var any = function (selector) {
    return $_9fzewc37je5o2qck.first(selector).isSome();
  };
  var ancestor$2 = function (scope, selector, isRoot) {
    return $_9fzewc37je5o2qck.ancestor(scope, selector, isRoot).isSome();
  };
  var sibling$3 = function (scope, selector) {
    return $_9fzewc37je5o2qck.sibling(scope, selector).isSome();
  };
  var child$3 = function (scope, selector) {
    return $_9fzewc37je5o2qck.child(scope, selector).isSome();
  };
  var descendant$2 = function (scope, selector) {
    return $_9fzewc37je5o2qck.descendant(scope, selector).isSome();
  };
  var closest$2 = function (scope, selector, isRoot) {
    return $_9fzewc37je5o2qck.closest(scope, selector, isRoot).isSome();
  };
  var $_9o2t0k36je5o2qci = {
    any: any,
    ancestor: ancestor$2,
    sibling: sibling$3,
    child: child$3,
    descendant: descendant$2,
    closest: closest$2
  };

  var hasWhitespacePreserveParent = function (rootNode, node) {
    var rootElement = $_5tcb2dyje5o2pwu.fromDom(rootNode);
    var startNode = $_5tcb2dyje5o2pwu.fromDom(node);
    return $_9o2t0k36je5o2qci.ancestor(startNode, 'pre,code', $_enfhkv6je5o2ply.curry($_7oppmo1dje5o2pz1.eq, rootElement));
  };
  var isWhitespace = function (rootNode, node) {
    return $_9owqcy1qje5o2q0e.isText(node) && /^[ \t\r\n]*$/.test(node.data) && hasWhitespacePreserveParent(rootNode, node) === false;
  };
  var isNamedAnchor = function (node) {
    return $_9owqcy1qje5o2q0e.isElement(node) && node.nodeName === 'A' && node.hasAttribute('name');
  };
  var isContent = function (rootNode, node) {
    return isCaretCandidate(node) && isWhitespace(rootNode, node) === false || isNamedAnchor(node) || isBookmark(node);
  };
  var isBookmark = $_9owqcy1qje5o2q0e.hasAttribute('data-mce-bookmark');
  var isBogus$2 = $_9owqcy1qje5o2q0e.hasAttribute('data-mce-bogus');
  var isBogusAll$1 = $_9owqcy1qje5o2q0e.hasAttributeValue('data-mce-bogus', 'all');
  var isEmptyNode = function (targetNode) {
    var walker, node, brCount = 0;
    if (isContent(targetNode, targetNode)) {
      return false;
    } else {
      node = targetNode.firstChild;
      if (!node) {
        return true;
      }
      walker = new TreeWalker(node, targetNode);
      do {
        if (isBogusAll$1(node)) {
          node = walker.next(true);
          continue;
        }
        if (isBogus$2(node)) {
          node = walker.next();
          continue;
        }
        if ($_9owqcy1qje5o2q0e.isBr(node)) {
          brCount++;
          node = walker.next();
          continue;
        }
        if (isContent(targetNode, node)) {
          return false;
        }
        node = walker.next();
      } while (node);
      return brCount <= 1;
    }
  };
  var isEmpty = function (elm) {
    return isEmptyNode(elm.dom());
  };
  var $_dizx5c35je5o2qcb = { isEmpty: isEmpty };

  var BlockPosition = $_86drv18je5o2pyn.immutable('block', 'position');
  var BlockBoundary = $_86drv18je5o2pyn.immutable('from', 'to');
  var getBlockPosition = function (rootNode, pos) {
    var rootElm = $_5tcb2dyje5o2pwu.fromDom(rootNode);
    var containerElm = $_5tcb2dyje5o2pwu.fromDom(pos.container());
    return $_7twey62zje5o2qay.getParentBlock(rootElm, containerElm).map(function (block) {
      return BlockPosition(block, pos);
    });
  };
  var isDifferentBlocks = function (blockBoundary) {
    return $_7oppmo1dje5o2pz1.eq(blockBoundary.from().block(), blockBoundary.to().block()) === false;
  };
  var hasSameParent = function (blockBoundary) {
    return $_a6mttx17je5o2pyd.parent(blockBoundary.from().block()).bind(function (parent1) {
      return $_a6mttx17je5o2pyd.parent(blockBoundary.to().block()).filter(function (parent2) {
        return $_7oppmo1dje5o2pz1.eq(parent1, parent2);
      });
    }).isSome();
  };
  var isEditable = function (blockBoundary) {
    return $_9owqcy1qje5o2q0e.isContentEditableFalse(blockBoundary.from().block()) === false && $_9owqcy1qje5o2q0e.isContentEditableFalse(blockBoundary.to().block()) === false;
  };
  var skipLastBr = function (rootNode, forward, blockPosition) {
    if ($_9owqcy1qje5o2q0e.isBr(blockPosition.position().getNode()) && $_dizx5c35je5o2qcb.isEmpty(blockPosition.block()) === false) {
      return $_g2kzjo2bje5o2q7n.positionIn(false, blockPosition.block().dom()).bind(function (lastPositionInBlock) {
        if (lastPositionInBlock.isEqual(blockPosition.position())) {
          return $_g2kzjo2bje5o2q7n.fromPosition(forward, rootNode, lastPositionInBlock).bind(function (to) {
            return getBlockPosition(rootNode, to);
          });
        } else {
          return Option.some(blockPosition);
        }
      }).getOr(blockPosition);
    } else {
      return blockPosition;
    }
  };
  var readFromRange = function (rootNode, forward, rng) {
    var fromBlockPos = getBlockPosition(rootNode, CaretPosition$1.fromRangeStart(rng));
    var toBlockPos = fromBlockPos.bind(function (blockPos) {
      return $_g2kzjo2bje5o2q7n.fromPosition(forward, rootNode, blockPos.position()).bind(function (to) {
        return getBlockPosition(rootNode, to).map(function (blockPos) {
          return skipLastBr(rootNode, forward, blockPos);
        });
      });
    });
    return $_c8weyc2eje5o2q85.liftN([
      fromBlockPos,
      toBlockPos
    ], BlockBoundary).filter(function (blockBoundary) {
      return isDifferentBlocks(blockBoundary) && hasSameParent(blockBoundary) && isEditable(blockBoundary);
    });
  };
  var read = function (rootNode, forward, rng) {
    return rng.collapsed ? readFromRange(rootNode, forward, rng) : Option.none();
  };
  var $_8rdvj02yje5o2qar = { read: read };

  var dropLast = function (xs) {
    return xs.slice(0, -1);
  };
  var parentsUntil$1 = function (startNode, rootElm, predicate) {
    if ($_7oppmo1dje5o2pz1.contains(rootElm, startNode)) {
      return dropLast($_a6mttx17je5o2pyd.parents(startNode, function (elm) {
        return predicate(elm) || $_7oppmo1dje5o2pz1.eq(elm, rootElm);
      }));
    } else {
      return [];
    }
  };
  var parents$1 = function (startNode, rootElm) {
    return parentsUntil$1(startNode, rootElm, $_enfhkv6je5o2ply.constant(false));
  };
  var parentsAndSelf = function (startNode, rootElm) {
    return [startNode].concat(parents$1(startNode, rootElm));
  };
  var $_yqcmh39je5o2qcw = {
    parentsUntil: parentsUntil$1,
    parents: parents$1,
    parentsAndSelf: parentsAndSelf
  };

  var getChildrenUntilBlockBoundary = function (block) {
    var children = $_a6mttx17je5o2pyd.children(block);
    return $_bwcteb4je5o2pl4.findIndex(children, isBlock).fold(function () {
      return children;
    }, function (index) {
      return children.slice(0, index);
    });
  };
  var extractChildren = function (block) {
    var children = getChildrenUntilBlockBoundary(block);
    $_bwcteb4je5o2pl4.each(children, function (node) {
      $_cr1seh2pje5o2qa1.remove(node);
    });
    return children;
  };
  var trimBr = function (first, block) {
    $_g2kzjo2bje5o2q7n.positionIn(first, block.dom()).each(function (position) {
      var node = position.getNode();
      if ($_9owqcy1qje5o2q0e.isBr(node)) {
        $_cr1seh2pje5o2qa1.remove($_5tcb2dyje5o2pwu.fromDom(node));
      }
    });
  };
  var removeEmptyRoot = function (rootNode, block) {
    var parents = $_yqcmh39je5o2qcw.parentsAndSelf(block, rootNode);
    return $_bwcteb4je5o2pl4.find(parents.reverse(), $_dizx5c35je5o2qcb.isEmpty).each($_cr1seh2pje5o2qa1.remove);
  };
  var findParentInsertPoint = function (toBlock, block) {
    var parents = $_a6mttx17je5o2pyd.parents(block, function (elm) {
      return $_7oppmo1dje5o2pz1.eq(elm, toBlock);
    });
    return Option.from(parents[parents.length - 2]);
  };
  var getInsertionPoint = function (fromBlock, toBlock) {
    if ($_7oppmo1dje5o2pz1.contains(toBlock, fromBlock)) {
      return $_a6mttx17je5o2pyd.parent(fromBlock).bind(function (parent) {
        return $_7oppmo1dje5o2pz1.eq(parent, toBlock) ? Option.some(fromBlock) : findParentInsertPoint(toBlock, fromBlock);
      });
    } else {
      return Option.none();
    }
  };
  var mergeBlockInto = function (rootNode, fromBlock, toBlock) {
    if ($_dizx5c35je5o2qcb.isEmpty(toBlock)) {
      $_cr1seh2pje5o2qa1.remove(toBlock);
      if ($_dizx5c35je5o2qcb.isEmpty(fromBlock)) {
        $_4kbg8z2nje5o2q9s.fillWithPaddingBr(fromBlock);
      }
      return $_g2kzjo2bje5o2q7n.firstPositionIn(fromBlock.dom());
    } else {
      trimBr(true, fromBlock);
      trimBr(false, toBlock);
      var children_1 = extractChildren(fromBlock);
      return getInsertionPoint(fromBlock, toBlock).fold(function () {
        removeEmptyRoot(rootNode, fromBlock);
        var position = $_g2kzjo2bje5o2q7n.lastPositionIn(toBlock.dom());
        $_bwcteb4je5o2pl4.each(children_1, function (node) {
          $_elbl2s2oje5o2q9z.append(toBlock, node);
        });
        return position;
      }, function (target) {
        var position = $_g2kzjo2bje5o2q7n.prevPosition(toBlock.dom(), CaretPosition$1.before(target.dom()));
        $_bwcteb4je5o2pl4.each(children_1, function (node) {
          $_elbl2s2oje5o2q9z.before(target, node);
        });
        removeEmptyRoot(rootNode, fromBlock);
        return position;
      });
    }
  };
  var mergeBlocks = function (rootNode, forward, block1, block2) {
    return forward ? mergeBlockInto(rootNode, block2, block1) : mergeBlockInto(rootNode, block1, block2);
  };
  var $_46j1ps38je5o2qcn = { mergeBlocks: mergeBlocks };

  var backspaceDelete = function (editor, forward) {
    var position;
    var rootNode = $_5tcb2dyje5o2pwu.fromDom(editor.getBody());
    position = $_8rdvj02yje5o2qar.read(rootNode.dom(), forward, editor.selection.getRng()).bind(function (blockBoundary) {
      return $_46j1ps38je5o2qcn.mergeBlocks(rootNode, forward, blockBoundary.from().block(), blockBoundary.to().block());
    });
    position.each(function (pos) {
      editor.selection.setRng(pos.toRange());
    });
    return position.isSome();
  };
  var $_3qfe0j2xje5o2qao = { backspaceDelete: backspaceDelete };

  var deleteRangeMergeBlocks = function (rootNode, selection) {
    var rng = selection.getRng();
    return $_c8weyc2eje5o2q85.liftN([
      $_7twey62zje5o2qay.getParentBlock(rootNode, $_5tcb2dyje5o2pwu.fromDom(rng.startContainer)),
      $_7twey62zje5o2qay.getParentBlock(rootNode, $_5tcb2dyje5o2pwu.fromDom(rng.endContainer))
    ], function (block1, block2) {
      if ($_7oppmo1dje5o2pz1.eq(block1, block2) === false) {
        rng.deleteContents();
        $_46j1ps38je5o2qcn.mergeBlocks(rootNode, true, block1, block2).each(function (pos) {
          selection.setRng(pos.toRange());
        });
        return true;
      } else {
        return false;
      }
    }).getOr(false);
  };
  var isRawNodeInTable = function (root, rawNode) {
    var node = $_5tcb2dyje5o2pwu.fromDom(rawNode);
    var isRoot = $_enfhkv6je5o2ply.curry($_7oppmo1dje5o2pz1.eq, root);
    return $_au2qfe30je5o2qb4.ancestor(node, isTableCell, isRoot).isSome();
  };
  var isSelectionInTable = function (root, rng) {
    return isRawNodeInTable(root, rng.startContainer) || isRawNodeInTable(root, rng.endContainer);
  };
  var isEverythingSelected = function (root, rng) {
    var noPrevious = $_g2kzjo2bje5o2q7n.prevPosition(root.dom(), CaretPosition$1.fromRangeStart(rng)).isNone();
    var noNext = $_g2kzjo2bje5o2q7n.nextPosition(root.dom(), CaretPosition$1.fromRangeEnd(rng)).isNone();
    return !isSelectionInTable(root, rng) && noPrevious && noNext;
  };
  var emptyEditor = function (editor) {
    editor.setContent('');
    editor.selection.setCursorLocation();
    return true;
  };
  var deleteRange = function (editor) {
    var rootNode = $_5tcb2dyje5o2pwu.fromDom(editor.getBody());
    var rng = editor.selection.getRng();
    return isEverythingSelected(rootNode, rng) ? emptyEditor(editor) : deleteRangeMergeBlocks(rootNode, editor.selection);
  };
  var backspaceDelete$1 = function (editor, forward) {
    return editor.selection.isCollapsed() ? false : deleteRange(editor);
  };
  var $_a476lo3aje5o2qd0 = { backspaceDelete: backspaceDelete$1 };

  var generate = function (cases) {
    if (!$_be3q8w12je5o2pxj.isArray(cases)) {
      throw new Error('cases must be an array');
    }
    if (cases.length === 0) {
      throw new Error('there must be at least one case');
    }
    var constructors = [];
    var adt = {};
    $_bwcteb4je5o2pl4.each(cases, function (acase, count) {
      var keys = $_8v44sy13je5o2pxm.keys(acase);
      if (keys.length !== 1) {
        throw new Error('one and only one name per case');
      }
      var key = keys[0];
      var value = acase[key];
      if (adt[key] !== undefined) {
        throw new Error('duplicate key detected:' + key);
      } else if (key === 'cata') {
        throw new Error('cannot have a case named cata (sorry)');
      } else if (!$_be3q8w12je5o2pxj.isArray(value)) {
        throw new Error('case arguments must be an array');
      }
      constructors.push(key);
      adt[key] = function () {
        var argLength = arguments.length;
        if (argLength !== value.length) {
          throw new Error('Wrong number of arguments to case ' + key + '. Expected ' + value.length + ' (' + value + '), got ' + argLength);
        }
        var args = new Array(argLength);
        for (var i = 0; i < args.length; i++)
          args[i] = arguments[i];
        var match = function (branches) {
          var branchKeys = $_8v44sy13je5o2pxm.keys(branches);
          if (constructors.length !== branchKeys.length) {
            throw new Error('Wrong number of arguments to match. Expected: ' + constructors.join(',') + '\nActual: ' + branchKeys.join(','));
          }
          var allReqd = $_bwcteb4je5o2pl4.forall(constructors, function (reqKey) {
            return $_bwcteb4je5o2pl4.contains(branchKeys, reqKey);
          });
          if (!allReqd)
            throw new Error('Not all branches were specified when using match. Specified: ' + branchKeys.join(', ') + '\nRequired: ' + constructors.join(', '));
          return branches[key].apply(null, args);
        };
        return {
          fold: function () {
            if (arguments.length !== cases.length) {
              throw new Error('Wrong number of arguments to fold. Expected ' + cases.length + ', got ' + arguments.length);
            }
            var target = arguments[count];
            return target.apply(null, args);
          },
          match: match,
          log: function (label) {
            console.log(label, {
              constructors: constructors,
              constructor: key,
              params: args
            });
          }
        };
      };
    });
    return adt;
  };
  var $_e73lvt3dje5o2qdq = { generate: generate };

  var isCompoundElement = function (node) {
    return isTableCell($_5tcb2dyje5o2pwu.fromDom(node)) || isListItem($_5tcb2dyje5o2pwu.fromDom(node));
  };
  var DeleteAction = $_e73lvt3dje5o2qdq.generate([
    { remove: ['element'] },
    { moveToElement: ['element'] },
    { moveToPosition: ['position'] }
  ]);
  var isAtContentEditableBlockCaret = function (forward, from) {
    var elm = from.getNode(forward === false);
    var caretLocation = forward ? 'after' : 'before';
    return $_9owqcy1qje5o2q0e.isElement(elm) && elm.getAttribute('data-mce-caret') === caretLocation;
  };
  var isDeleteFromCefDifferentBlocks = function (root, forward, from, to) {
    var inSameBlock = function (elm) {
      return isInline($_5tcb2dyje5o2pwu.fromDom(elm)) && !isInSameBlock(from, to, root);
    };
    return getRelativeCefElm(!forward, from).fold(function () {
      return getRelativeCefElm(forward, to).fold($_enfhkv6je5o2ply.constant(false), inSameBlock);
    }, inSameBlock);
  };
  var deleteEmptyBlockOrMoveToCef = function (root, forward, from, to) {
    var toCefElm = to.getNode(forward === false);
    return $_7twey62zje5o2qay.getParentBlock($_5tcb2dyje5o2pwu.fromDom(root), $_5tcb2dyje5o2pwu.fromDom(from.getNode())).map(function (blockElm) {
      return $_dizx5c35je5o2qcb.isEmpty(blockElm) ? DeleteAction.remove(blockElm.dom()) : DeleteAction.moveToElement(toCefElm);
    }).orThunk(function () {
      return Option.some(DeleteAction.moveToElement(toCefElm));
    });
  };
  var findCefPosition = function (root, forward, from) {
    return $_g2kzjo2bje5o2q7n.fromPosition(forward, root, from).bind(function (to) {
      if (isCompoundElement(to.getNode())) {
        return Option.none();
      } else if (isDeleteFromCefDifferentBlocks(root, forward, from, to)) {
        return Option.none();
      } else if (forward && $_9owqcy1qje5o2q0e.isContentEditableFalse(to.getNode())) {
        return deleteEmptyBlockOrMoveToCef(root, forward, from, to);
      } else if (forward === false && $_9owqcy1qje5o2q0e.isContentEditableFalse(to.getNode(true))) {
        return deleteEmptyBlockOrMoveToCef(root, forward, from, to);
      } else if (forward && isAfterContentEditableFalse(from)) {
        return Option.some(DeleteAction.moveToPosition(to));
      } else if (forward === false && isBeforeContentEditableFalse(from)) {
        return Option.some(DeleteAction.moveToPosition(to));
      } else {
        return Option.none();
      }
    });
  };
  var getContentEditableBlockAction = function (forward, elm) {
    if (forward && $_9owqcy1qje5o2q0e.isContentEditableFalse(elm.nextSibling)) {
      return Option.some(DeleteAction.moveToElement(elm.nextSibling));
    } else if (forward === false && $_9owqcy1qje5o2q0e.isContentEditableFalse(elm.previousSibling)) {
      return Option.some(DeleteAction.moveToElement(elm.previousSibling));
    } else {
      return Option.none();
    }
  };
  var skipMoveToActionFromInlineCefToContent = function (root, from, deleteAction) {
    return deleteAction.fold(function (elm) {
      return Option.some(DeleteAction.remove(elm));
    }, function (elm) {
      return Option.some(DeleteAction.moveToElement(elm));
    }, function (to) {
      if (isInSameBlock(from, to, root)) {
        return Option.none();
      } else {
        return Option.some(DeleteAction.moveToPosition(to));
      }
    });
  };
  var getContentEditableAction = function (root, forward, from) {
    if (isAtContentEditableBlockCaret(forward, from)) {
      return getContentEditableBlockAction(forward, from.getNode(forward === false)).fold(function () {
        return findCefPosition(root, forward, from);
      }, Option.some);
    } else {
      return findCefPosition(root, forward, from).bind(function (deleteAction) {
        return skipMoveToActionFromInlineCefToContent(root, from, deleteAction);
      });
    }
  };
  var read$1 = function (root, forward, rng) {
    var normalizedRange = normalizeRange(forward ? 1 : -1, root, rng);
    var from = CaretPosition$1.fromRangeStart(normalizedRange);
    if (forward === false && isAfterContentEditableFalse(from)) {
      return Option.some(DeleteAction.remove(from.getNode(true)));
    } else if (forward && isBeforeContentEditableFalse(from)) {
      return Option.some(DeleteAction.remove(from.getNode()));
    } else {
      return getContentEditableAction(root, forward, from);
    }
  };

  var needsReposition = function (pos, elm) {
    var container = pos.container();
    var offset = pos.offset();
    return CaretPosition$1.isTextPosition(pos) === false && container === elm.parentNode && offset > CaretPosition$1.before(elm).offset();
  };
  var reposition = function (elm, pos) {
    return needsReposition(pos, elm) ? CaretPosition$1(pos.container(), pos.offset() - 1) : pos;
  };
  var beforeOrStartOf = function (node) {
    return $_9owqcy1qje5o2q0e.isText(node) ? CaretPosition$1(node, 0) : CaretPosition$1.before(node);
  };
  var afterOrEndOf = function (node) {
    return $_9owqcy1qje5o2q0e.isText(node) ? CaretPosition$1(node, node.data.length) : CaretPosition$1.after(node);
  };
  var getPreviousSiblingCaretPosition = function (elm) {
    if (isCaretCandidate(elm.previousSibling)) {
      return Option.some(afterOrEndOf(elm.previousSibling));
    } else {
      return elm.previousSibling ? $_g2kzjo2bje5o2q7n.lastPositionIn(elm.previousSibling) : Option.none();
    }
  };
  var getNextSiblingCaretPosition = function (elm) {
    if (isCaretCandidate(elm.nextSibling)) {
      return Option.some(beforeOrStartOf(elm.nextSibling));
    } else {
      return elm.nextSibling ? $_g2kzjo2bje5o2q7n.firstPositionIn(elm.nextSibling) : Option.none();
    }
  };
  var findCaretPositionBackwardsFromElm = function (rootElement, elm) {
    var startPosition = CaretPosition$1.before(elm.previousSibling ? elm.previousSibling : elm.parentNode);
    return $_g2kzjo2bje5o2q7n.prevPosition(rootElement, startPosition).fold(function () {
      return $_g2kzjo2bje5o2q7n.nextPosition(rootElement, CaretPosition$1.after(elm));
    }, Option.some);
  };
  var findCaretPositionForwardsFromElm = function (rootElement, elm) {
    return $_g2kzjo2bje5o2q7n.nextPosition(rootElement, CaretPosition$1.after(elm)).fold(function () {
      return $_g2kzjo2bje5o2q7n.prevPosition(rootElement, CaretPosition$1.before(elm));
    }, Option.some);
  };
  var findCaretPositionBackwards = function (rootElement, elm) {
    return getPreviousSiblingCaretPosition(elm).orThunk(function () {
      return getNextSiblingCaretPosition(elm);
    }).orThunk(function () {
      return findCaretPositionBackwardsFromElm(rootElement, elm);
    });
  };
  var findCaretPositionForward = function (rootElement, elm) {
    return getNextSiblingCaretPosition(elm).orThunk(function () {
      return getPreviousSiblingCaretPosition(elm);
    }).orThunk(function () {
      return findCaretPositionForwardsFromElm(rootElement, elm);
    });
  };
  var findCaretPosition$1 = function (forward, rootElement, elm) {
    return forward ? findCaretPositionForward(rootElement, elm) : findCaretPositionBackwards(rootElement, elm);
  };
  var findCaretPosOutsideElmAfterDelete = function (forward, rootElement, elm) {
    return findCaretPosition$1(forward, rootElement, elm).map($_enfhkv6je5o2ply.curry(reposition, elm));
  };
  var setSelection = function (editor, forward, pos) {
    pos.fold(function () {
      editor.focus();
    }, function (pos) {
      editor.selection.setRng(pos.toRange(), forward);
    });
  };
  var eqRawNode = function (rawNode) {
    return function (elm) {
      return elm.dom() === rawNode;
    };
  };
  var isBlock$2 = function (editor, elm) {
    return elm && editor.schema.getBlockElements().hasOwnProperty($_95wn8qzje5o2px0.name(elm));
  };
  var paddEmptyBlock = function (elm) {
    if ($_dizx5c35je5o2qcb.isEmpty(elm)) {
      var br = $_5tcb2dyje5o2pwu.fromHtml('<br data-mce-bogus="1">');
      $_cr1seh2pje5o2qa1.empty(elm);
      $_elbl2s2oje5o2q9z.append(elm, br);
      return Option.some(CaretPosition$1.before(br.dom()));
    } else {
      return Option.none();
    }
  };
  var deleteNormalized = function (elm, afterDeletePosOpt) {
    return $_c8weyc2eje5o2q85.liftN([
      $_a6mttx17je5o2pyd.prevSibling(elm),
      $_a6mttx17je5o2pyd.nextSibling(elm),
      afterDeletePosOpt
    ], function (prev, next, afterDeletePos) {
      var offset;
      var prevNode = prev.dom();
      var nextNode = next.dom();
      if ($_9owqcy1qje5o2q0e.isText(prevNode) && $_9owqcy1qje5o2q0e.isText(nextNode)) {
        offset = prevNode.data.length;
        prevNode.appendData(nextNode.data);
        $_cr1seh2pje5o2qa1.remove(next);
        $_cr1seh2pje5o2qa1.remove(elm);
        if (afterDeletePos.container() === nextNode) {
          return CaretPosition$1(prevNode, offset);
        } else {
          return afterDeletePos;
        }
      } else {
        $_cr1seh2pje5o2qa1.remove(elm);
        return afterDeletePos;
      }
    }).orThunk(function () {
      $_cr1seh2pje5o2qa1.remove(elm);
      return afterDeletePosOpt;
    });
  };
  var deleteElement = function (editor, forward, elm) {
    var afterDeletePos = findCaretPosOutsideElmAfterDelete(forward, editor.getBody(), elm.dom());
    var parentBlock = $_au2qfe30je5o2qb4.ancestor(elm, $_enfhkv6je5o2ply.curry(isBlock$2, editor), eqRawNode(editor.getBody()));
    var normalizedAfterDeletePos = deleteNormalized(elm, afterDeletePos);
    if (editor.dom.isEmpty(editor.getBody())) {
      editor.setContent('');
      editor.selection.setCursorLocation();
    } else {
      parentBlock.bind(paddEmptyBlock).fold(function () {
        setSelection(editor, forward, normalizedAfterDeletePos);
      }, function (paddPos) {
        setSelection(editor, forward, Option.some(paddPos));
      });
    }
  };
  var $_8fuvl23eje5o2qdv = { deleteElement: deleteElement };

  var deleteElement$1 = function (editor, forward) {
    return function (element) {
      editor._selectionOverrides.hideFakeCaret();
      $_8fuvl23eje5o2qdv.deleteElement(editor, forward, $_5tcb2dyje5o2pwu.fromDom(element));
      return true;
    };
  };
  var moveToElement = function (editor, forward) {
    return function (element) {
      var pos = forward ? CaretPosition$1.before(element) : CaretPosition$1.after(element);
      editor.selection.setRng(pos.toRange());
      return true;
    };
  };
  var moveToPosition = function (editor) {
    return function (pos) {
      editor.selection.setRng(pos.toRange());
      return true;
    };
  };
  var backspaceDeleteCaret = function (editor, forward) {
    var result = read$1(editor.getBody(), forward, editor.selection.getRng()).map(function (deleteAction) {
      return deleteAction.fold(deleteElement$1(editor, forward), moveToElement(editor, forward), moveToPosition(editor));
    });
    return result.getOr(false);
  };
  var deleteOffscreenSelection = function (rootElement) {
    $_bwcteb4je5o2pl4.each($_b4jhpu2gje5o2q8e.descendants(rootElement, '.mce-offscreen-selection'), $_cr1seh2pje5o2qa1.remove);
  };
  var backspaceDeleteRange = function (editor, forward) {
    var selectedElement = editor.selection.getNode();
    if ($_9owqcy1qje5o2q0e.isContentEditableFalse(selectedElement)) {
      deleteOffscreenSelection($_5tcb2dyje5o2pwu.fromDom(editor.getBody()));
      $_8fuvl23eje5o2qdv.deleteElement(editor, forward, $_5tcb2dyje5o2pwu.fromDom(editor.selection.getNode()));
      $_7twey62zje5o2qay.paddEmptyBody(editor);
      return true;
    } else {
      return false;
    }
  };
  var getContentEditableRoot = function (root, node) {
    while (node && node !== root) {
      if ($_9owqcy1qje5o2q0e.isContentEditableTrue(node) || $_9owqcy1qje5o2q0e.isContentEditableFalse(node)) {
        return node;
      }
      node = node.parentNode;
    }
    return null;
  };
  var paddEmptyElement = function (editor) {
    var br;
    var ceRoot = getContentEditableRoot(editor.getBody(), editor.selection.getNode());
    if ($_9owqcy1qje5o2q0e.isContentEditableTrue(ceRoot) && editor.dom.isBlock(ceRoot) && editor.dom.isEmpty(ceRoot)) {
      br = editor.dom.create('br', { 'data-mce-bogus': '1' });
      editor.dom.setHTML(ceRoot, '');
      ceRoot.appendChild(br);
      editor.selection.setRng(CaretPosition$1.before(br).toRange());
    }
    return true;
  };
  var backspaceDelete$2 = function (editor, forward) {
    if (editor.selection.isCollapsed()) {
      return backspaceDeleteCaret(editor, forward);
    } else {
      return backspaceDeleteRange(editor, forward);
    }
  };
  var $_c7csnv3bje5o2qd6 = {
    backspaceDelete: backspaceDelete$2,
    paddEmptyElement: paddEmptyElement
  };

  var isText$8 = $_9owqcy1qje5o2q0e.isText;
  var startsWithCaretContainer$1 = function (node) {
    return isText$8(node) && node.data[0] === $_4md31c21je5o2q5f.ZWSP;
  };
  var endsWithCaretContainer$1 = function (node) {
    return isText$8(node) && node.data[node.data.length - 1] === $_4md31c21je5o2q5f.ZWSP;
  };
  var createZwsp = function (node) {
    return node.ownerDocument.createTextNode($_4md31c21je5o2q5f.ZWSP);
  };
  var insertBefore$1 = function (node) {
    if (isText$8(node.previousSibling)) {
      if (endsWithCaretContainer$1(node.previousSibling)) {
        return node.previousSibling;
      } else {
        node.previousSibling.appendData($_4md31c21je5o2q5f.ZWSP);
        return node.previousSibling;
      }
    } else if (isText$8(node)) {
      if (startsWithCaretContainer$1(node)) {
        return node;
      } else {
        node.insertData(0, $_4md31c21je5o2q5f.ZWSP);
        return node;
      }
    } else {
      var newNode = createZwsp(node);
      node.parentNode.insertBefore(newNode, node);
      return newNode;
    }
  };
  var insertAfter$1 = function (node) {
    if (isText$8(node.nextSibling)) {
      if (startsWithCaretContainer$1(node.nextSibling)) {
        return node.nextSibling;
      } else {
        node.nextSibling.insertData(0, $_4md31c21je5o2q5f.ZWSP);
        return node.nextSibling;
      }
    } else if (isText$8(node)) {
      if (endsWithCaretContainer$1(node)) {
        return node;
      } else {
        node.appendData($_4md31c21je5o2q5f.ZWSP);
        return node;
      }
    } else {
      var newNode = createZwsp(node);
      if (node.nextSibling) {
        node.parentNode.insertBefore(newNode, node.nextSibling);
      } else {
        node.parentNode.appendChild(newNode);
      }
      return newNode;
    }
  };
  var insertInline$1 = function (before, node) {
    return before ? insertBefore$1(node) : insertAfter$1(node);
  };
  var insertInlineBefore = $_enfhkv6je5o2ply.curry(insertInline$1, true);
  var insertInlineAfter = $_enfhkv6je5o2ply.curry(insertInline$1, false);

  var insertInlinePos = function (pos, before) {
    if ($_9owqcy1qje5o2q0e.isText(pos.container())) {
      return insertInline$1(before, pos.container());
    } else {
      return insertInline$1(before, pos.getNode());
    }
  };
  var isPosCaretContainer = function (pos, caret) {
    var caretNode = caret.get();
    return caretNode && pos.container() === caretNode && isCaretContainerInline(caretNode);
  };
  var renderCaret = function (caret, location) {
    return location.fold(function (element) {
      $_3djwbs29je5o2q74.remove(caret.get());
      var text = insertInlineBefore(element);
      caret.set(text);
      return Option.some(CaretPosition$1(text, text.length - 1));
    }, function (element) {
      return $_g2kzjo2bje5o2q7n.firstPositionIn(element).map(function (pos) {
        if (!isPosCaretContainer(pos, caret)) {
          $_3djwbs29je5o2q74.remove(caret.get());
          var text = insertInlinePos(pos, true);
          caret.set(text);
          return CaretPosition$1(text, 1);
        } else {
          return CaretPosition$1(caret.get(), 1);
        }
      });
    }, function (element) {
      return $_g2kzjo2bje5o2q7n.lastPositionIn(element).map(function (pos) {
        if (!isPosCaretContainer(pos, caret)) {
          $_3djwbs29je5o2q74.remove(caret.get());
          var text = insertInlinePos(pos, false);
          caret.set(text);
          return CaretPosition$1(text, text.length - 1);
        } else {
          return CaretPosition$1(caret.get(), caret.get().length - 1);
        }
      });
    }, function (element) {
      $_3djwbs29je5o2q74.remove(caret.get());
      var text = insertInlineAfter(element);
      caret.set(text);
      return Option.some(CaretPosition$1(text, 1));
    });
  };
  var $_5a4sgg3gje5o2qee = { renderCaret: renderCaret };

  var isInlineBlock = function (node) {
    return node && /^(IMG)$/.test(node.nodeName);
  };
  var moveStart = function (dom, selection, rng) {
    var container = rng.startContainer, offset = rng.startOffset, walker, node, nodes;
    if (rng.startContainer === rng.endContainer) {
      if (isInlineBlock(rng.startContainer.childNodes[rng.startOffset])) {
        return;
      }
    }
    if (container.nodeType === 3 && offset >= container.nodeValue.length) {
      offset = dom.nodeIndex(container);
      container = container.parentNode;
    }
    if (container.nodeType === 1) {
      nodes = container.childNodes;
      if (offset < nodes.length) {
        container = nodes[offset];
        walker = new TreeWalker(container, dom.getParent(container, dom.isBlock));
      } else {
        container = nodes[nodes.length - 1];
        walker = new TreeWalker(container, dom.getParent(container, dom.isBlock));
        walker.next(true);
      }
      for (node = walker.current(); node; node = walker.next()) {
        if (node.nodeType === 3 && !isWhiteSpaceNode(node)) {
          rng.setStart(node, 0);
          selection.setRng(rng);
          return;
        }
      }
    }
  };
  var getNonWhiteSpaceSibling = function (node, next, inc) {
    if (node) {
      next = next ? 'nextSibling' : 'previousSibling';
      for (node = inc ? node : node[next]; node; node = node[next]) {
        if (node.nodeType === 1 || !isWhiteSpaceNode(node)) {
          return node;
        }
      }
    }
  };
  var isTextBlock$1 = function (editor, name) {
    if (name.nodeType) {
      name = name.nodeName;
    }
    return !!editor.schema.getTextBlockElements()[name.toLowerCase()];
  };
  var isValid = function (ed, parent, child) {
    return ed.schema.isValidChild(parent, child);
  };
  var isWhiteSpaceNode = function (node) {
    return node && node.nodeType === 3 && /^([\t \r\n]+|)$/.test(node.nodeValue);
  };
  var replaceVars = function (value, vars) {
    if (typeof value !== 'string') {
      value = value(vars);
    } else if (vars) {
      value = value.replace(/%(\w+)/g, function (str, name) {
        return vars[name] || str;
      });
    }
    return value;
  };
  var isEq = function (str1, str2) {
    str1 = str1 || '';
    str2 = str2 || '';
    str1 = '' + (str1.nodeName || str1);
    str2 = '' + (str2.nodeName || str2);
    return str1.toLowerCase() === str2.toLowerCase();
  };
  var normalizeStyleValue = function (dom, value, name) {
    if (name === 'color' || name === 'backgroundColor') {
      value = dom.toHex(value);
    }
    if (name === 'fontWeight' && value === 700) {
      value = 'bold';
    }
    if (name === 'fontFamily') {
      value = value.replace(/[\'\"]/g, '').replace(/,\s+/g, ',');
    }
    return '' + value;
  };
  var getStyle = function (dom, node, name) {
    return normalizeStyleValue(dom, dom.getStyle(node, name), name);
  };
  var getTextDecoration = function (dom, node) {
    var decoration;
    dom.getParent(node, function (n) {
      decoration = dom.getStyle(n, 'text-decoration');
      return decoration && decoration !== 'none';
    });
    return decoration;
  };
  var getParents$1 = function (dom, node, selector) {
    return dom.getParents(node, selector, dom.getRoot());
  };
  var $_b2hqtg3lje5o2qft = {
    isInlineBlock: isInlineBlock,
    moveStart: moveStart,
    getNonWhiteSpaceSibling: getNonWhiteSpaceSibling,
    isTextBlock: isTextBlock$1,
    isValid: isValid,
    isWhiteSpaceNode: isWhiteSpaceNode,
    replaceVars: replaceVars,
    isEq: isEq,
    normalizeStyleValue: normalizeStyleValue,
    getStyle: getStyle,
    getTextDecoration: getTextDecoration,
    getParents: getParents$1
  };

  var isBookmarkNode$2 = $_83h91t2jje5o2q8n.isBookmarkNode;
  var getParents$2 = $_b2hqtg3lje5o2qft.getParents;
  var isWhiteSpaceNode$1 = $_b2hqtg3lje5o2qft.isWhiteSpaceNode;
  var isTextBlock$2 = $_b2hqtg3lje5o2qft.isTextBlock;
  var findLeaf = function (node, offset) {
    if (typeof offset === 'undefined') {
      offset = node.nodeType === 3 ? node.length : node.childNodes.length;
    }
    while (node && node.hasChildNodes()) {
      node = node.childNodes[offset];
      if (node) {
        offset = node.nodeType === 3 ? node.length : node.childNodes.length;
      }
    }
    return {
      node: node,
      offset: offset
    };
  };
  var excludeTrailingWhitespace = function (endContainer, endOffset) {
    var leaf = findLeaf(endContainer, endOffset);
    if (leaf.node) {
      while (leaf.node && leaf.offset === 0 && leaf.node.previousSibling) {
        leaf = findLeaf(leaf.node.previousSibling);
      }
      if (leaf.node && leaf.offset > 0 && leaf.node.nodeType === 3 && leaf.node.nodeValue.charAt(leaf.offset - 1) === ' ') {
        if (leaf.offset > 1) {
          endContainer = leaf.node;
          endContainer.splitText(leaf.offset - 1);
        }
      }
    }
    return endContainer;
  };
  var isBogusBr = function (node) {
    return node.nodeName === 'BR' && node.getAttribute('data-mce-bogus') && !node.nextSibling;
  };
  var findParentContentEditable = function (dom, node) {
    var parent = node;
    while (parent) {
      if (parent.nodeType === 1 && dom.getContentEditable(parent)) {
        return dom.getContentEditable(parent) === 'false' ? parent : node;
      }
      parent = parent.parentNode;
    }
    return node;
  };
  var findSpace = function (start, remove, node, offset) {
    var pos, pos2;
    var str = node.nodeValue;
    if (typeof offset === 'undefined') {
      offset = start ? str.length : 0;
    }
    if (start) {
      pos = str.lastIndexOf(' ', offset);
      pos2 = str.lastIndexOf('\xA0', offset);
      pos = pos > pos2 ? pos : pos2;
      if (pos !== -1 && !remove) {
        pos++;
      }
    } else {
      pos = str.indexOf(' ', offset);
      pos2 = str.indexOf('\xA0', offset);
      pos = pos !== -1 && (pos2 === -1 || pos < pos2) ? pos : pos2;
    }
    return pos;
  };
  var findWordEndPoint = function (dom, body, container, offset, start, remove) {
    var walker, node, pos, lastTextNode;
    if (container.nodeType === 3) {
      pos = findSpace(start, remove, container, offset);
      if (pos !== -1) {
        return {
          container: container,
          offset: pos
        };
      }
      lastTextNode = container;
    }
    walker = new TreeWalker(container, dom.getParent(container, dom.isBlock) || body);
    while (node = walker[start ? 'prev' : 'next']()) {
      if (node.nodeType === 3) {
        lastTextNode = node;
        pos = findSpace(start, remove, node);
        if (pos !== -1) {
          return {
            container: node,
            offset: pos
          };
        }
      } else if (dom.isBlock(node)) {
        break;
      }
    }
    if (lastTextNode) {
      if (start) {
        offset = 0;
      } else {
        offset = lastTextNode.length;
      }
      return {
        container: lastTextNode,
        offset: offset
      };
    }
  };
  var findSelectorEndPoint = function (dom, format, rng, container, siblingName) {
    var parents, i, y, curFormat;
    if (container.nodeType === 3 && container.nodeValue.length === 0 && container[siblingName]) {
      container = container[siblingName];
    }
    parents = getParents$2(dom, container);
    for (i = 0; i < parents.length; i++) {
      for (y = 0; y < format.length; y++) {
        curFormat = format[y];
        if ('collapsed' in curFormat && curFormat.collapsed !== rng.collapsed) {
          continue;
        }
        if (dom.is(parents[i], curFormat.selector)) {
          return parents[i];
        }
      }
    }
    return container;
  };
  var findBlockEndPoint = function (editor, format, container, siblingName) {
    var node;
    var dom = editor.dom;
    var root = dom.getRoot();
    if (!format[0].wrapper) {
      node = dom.getParent(container, format[0].block, root);
    }
    if (!node) {
      var scopeRoot = dom.getParent(container, 'LI,TD,TH');
      node = dom.getParent(container.nodeType === 3 ? container.parentNode : container, function (node) {
        return node !== root && isTextBlock$2(editor, node);
      }, scopeRoot);
    }
    if (node && format[0].wrapper) {
      node = getParents$2(dom, node, 'ul,ol').reverse()[0] || node;
    }
    if (!node) {
      node = container;
      while (node[siblingName] && !dom.isBlock(node[siblingName])) {
        node = node[siblingName];
        if ($_b2hqtg3lje5o2qft.isEq(node, 'br')) {
          break;
        }
      }
    }
    return node || container;
  };
  var findParentContainer = function (dom, format, startContainer, startOffset, endContainer, endOffset, start) {
    var container, parent, sibling, siblingName, root;
    container = parent = start ? startContainer : endContainer;
    siblingName = start ? 'previousSibling' : 'nextSibling';
    root = dom.getRoot();
    if (container.nodeType === 3 && !isWhiteSpaceNode$1(container)) {
      if (start ? startOffset > 0 : endOffset < container.nodeValue.length) {
        return container;
      }
    }
    while (true) {
      if (!format[0].block_expand && dom.isBlock(parent)) {
        return parent;
      }
      for (sibling = parent[siblingName]; sibling; sibling = sibling[siblingName]) {
        if (!isBookmarkNode$2(sibling) && !isWhiteSpaceNode$1(sibling) && !isBogusBr(sibling)) {
          return parent;
        }
      }
      if (parent === root || parent.parentNode === root) {
        container = parent;
        break;
      }
      parent = parent.parentNode;
    }
    return container;
  };
  var expandRng = function (editor, rng, format, remove) {
    var endPoint, startContainer = rng.startContainer, startOffset = rng.startOffset, endContainer = rng.endContainer, endOffset = rng.endOffset;
    var dom = editor.dom;
    if (startContainer.nodeType === 1 && startContainer.hasChildNodes()) {
      startContainer = getNode(startContainer, startOffset);
      if (startContainer.nodeType === 3) {
        startOffset = 0;
      }
    }
    if (endContainer.nodeType === 1 && endContainer.hasChildNodes()) {
      endContainer = getNode(endContainer, rng.collapsed ? endOffset : endOffset - 1);
      if (endContainer.nodeType === 3) {
        endOffset = endContainer.nodeValue.length;
      }
    }
    startContainer = findParentContentEditable(dom, startContainer);
    endContainer = findParentContentEditable(dom, endContainer);
    if (isBookmarkNode$2(startContainer.parentNode) || isBookmarkNode$2(startContainer)) {
      startContainer = isBookmarkNode$2(startContainer) ? startContainer : startContainer.parentNode;
      startContainer = startContainer.nextSibling || startContainer;
      if (startContainer.nodeType === 3) {
        startOffset = 0;
      }
    }
    if (isBookmarkNode$2(endContainer.parentNode) || isBookmarkNode$2(endContainer)) {
      endContainer = isBookmarkNode$2(endContainer) ? endContainer : endContainer.parentNode;
      endContainer = endContainer.previousSibling || endContainer;
      if (endContainer.nodeType === 3) {
        endOffset = endContainer.length;
      }
    }
    if (format[0].inline) {
      if (rng.collapsed) {
        endPoint = findWordEndPoint(dom, editor.getBody(), startContainer, startOffset, true, remove);
        if (endPoint) {
          startContainer = endPoint.container;
          startOffset = endPoint.offset;
        }
        endPoint = findWordEndPoint(dom, editor.getBody(), endContainer, endOffset, false, remove);
        if (endPoint) {
          endContainer = endPoint.container;
          endOffset = endPoint.offset;
        }
      }
      endContainer = remove ? endContainer : excludeTrailingWhitespace(endContainer, endOffset);
    }
    if (format[0].inline || format[0].block_expand) {
      if (!format[0].inline || (startContainer.nodeType !== 3 || startOffset === 0)) {
        startContainer = findParentContainer(dom, format, startContainer, startOffset, endContainer, endOffset, true);
      }
      if (!format[0].inline || (endContainer.nodeType !== 3 || endOffset === endContainer.nodeValue.length)) {
        endContainer = findParentContainer(dom, format, startContainer, startOffset, endContainer, endOffset, false);
      }
    }
    if (format[0].selector && format[0].expand !== false && !format[0].inline) {
      startContainer = findSelectorEndPoint(dom, format, rng, startContainer, 'previousSibling');
      endContainer = findSelectorEndPoint(dom, format, rng, endContainer, 'nextSibling');
    }
    if (format[0].block || format[0].selector) {
      startContainer = findBlockEndPoint(editor, format, startContainer, 'previousSibling');
      endContainer = findBlockEndPoint(editor, format, endContainer, 'nextSibling');
      if (format[0].block) {
        if (!dom.isBlock(startContainer)) {
          startContainer = findParentContainer(dom, format, startContainer, startOffset, endContainer, endOffset, true);
        }
        if (!dom.isBlock(endContainer)) {
          endContainer = findParentContainer(dom, format, startContainer, startOffset, endContainer, endOffset, false);
        }
      }
    }
    if (startContainer.nodeType === 1) {
      startOffset = dom.nodeIndex(startContainer);
      startContainer = startContainer.parentNode;
    }
    if (endContainer.nodeType === 1) {
      endOffset = dom.nodeIndex(endContainer) + 1;
      endContainer = endContainer.parentNode;
    }
    return {
      startContainer: startContainer,
      startOffset: startOffset,
      endContainer: endContainer,
      endOffset: endOffset
    };
  };
  var $_8wd5fu3kje5o2qfi = { expandRng: expandRng };

  var isEq$1 = $_b2hqtg3lje5o2qft.isEq;
  var matchesUnInheritedFormatSelector = function (ed, node, name) {
    var formatList = ed.formatter.get(name);
    if (formatList) {
      for (var i = 0; i < formatList.length; i++) {
        if (formatList[i].inherit === false && ed.dom.is(node, formatList[i].selector)) {
          return true;
        }
      }
    }
    return false;
  };
  var matchParents = function (editor, node, name, vars) {
    var root = editor.dom.getRoot();
    if (node === root) {
      return false;
    }
    node = editor.dom.getParent(node, function (node) {
      if (matchesUnInheritedFormatSelector(editor, node, name)) {
        return true;
      }
      return node.parentNode === root || !!matchNode(editor, node, name, vars, true);
    });
    return matchNode(editor, node, name, vars);
  };
  var matchName = function (dom, node, format) {
    if (isEq$1(node, format.inline)) {
      return true;
    }
    if (isEq$1(node, format.block)) {
      return true;
    }
    if (format.selector) {
      return node.nodeType === 1 && dom.is(node, format.selector);
    }
  };
  var matchItems = function (dom, node, format, itemName, similar, vars) {
    var key, value;
    var items = format[itemName];
    var i;
    if (format.onmatch) {
      return format.onmatch(node, format, itemName);
    }
    if (items) {
      if (typeof items.length === 'undefined') {
        for (key in items) {
          if (items.hasOwnProperty(key)) {
            if (itemName === 'attributes') {
              value = dom.getAttrib(node, key);
            } else {
              value = $_b2hqtg3lje5o2qft.getStyle(dom, node, key);
            }
            if (similar && !value && !format.exact) {
              return;
            }
            if ((!similar || format.exact) && !isEq$1(value, $_b2hqtg3lje5o2qft.normalizeStyleValue(dom, $_b2hqtg3lje5o2qft.replaceVars(items[key], vars), key))) {
              return;
            }
          }
        }
      } else {
        for (i = 0; i < items.length; i++) {
          if (itemName === 'attributes' ? dom.getAttrib(node, items[i]) : $_b2hqtg3lje5o2qft.getStyle(dom, node, items[i])) {
            return format;
          }
        }
      }
    }
    return format;
  };
  var matchNode = function (ed, node, name, vars, similar) {
    var formatList = ed.formatter.get(name);
    var format, i, x, classes;
    var dom = ed.dom;
    if (formatList && node) {
      for (i = 0; i < formatList.length; i++) {
        format = formatList[i];
        if (matchName(ed.dom, node, format) && matchItems(dom, node, format, 'attributes', similar, vars) && matchItems(dom, node, format, 'styles', similar, vars)) {
          if (classes = format.classes) {
            for (x = 0; x < classes.length; x++) {
              if (!ed.dom.hasClass(node, classes[x])) {
                return;
              }
            }
          }
          return format;
        }
      }
    }
  };
  var match = function (editor, name, vars, node) {
    var startNode;
    if (node) {
      return matchParents(editor, node, name, vars);
    }
    node = editor.selection.getNode();
    if (matchParents(editor, node, name, vars)) {
      return true;
    }
    startNode = editor.selection.getStart();
    if (startNode !== node) {
      if (matchParents(editor, startNode, name, vars)) {
        return true;
      }
    }
    return false;
  };
  var matchAll = function (editor, names, vars) {
    var startElement;
    var matchedFormatNames = [];
    var checkedMap = {};
    startElement = editor.selection.getStart();
    editor.dom.getParent(startElement, function (node) {
      var i, name;
      for (i = 0; i < names.length; i++) {
        name = names[i];
        if (!checkedMap[name] && matchNode(editor, node, name, vars)) {
          checkedMap[name] = true;
          matchedFormatNames.push(name);
        }
      }
    }, editor.dom.getRoot());
    return matchedFormatNames;
  };
  var canApply = function (editor, name) {
    var formatList = editor.formatter.get(name);
    var startNode, parents, i, x, selector;
    var dom = editor.dom;
    if (formatList) {
      startNode = editor.selection.getStart();
      parents = $_b2hqtg3lje5o2qft.getParents(dom, startNode);
      for (x = formatList.length - 1; x >= 0; x--) {
        selector = formatList[x].selector;
        if (!selector || formatList[x].defaultBlock) {
          return true;
        }
        for (i = parents.length - 1; i >= 0; i--) {
          if (dom.is(parents[i], selector)) {
            return true;
          }
        }
      }
    }
    return false;
  };
  var $_g32xfv3mje5o2qg1 = {
    matchNode: matchNode,
    matchName: matchName,
    match: match,
    matchAll: matchAll,
    canApply: canApply,
    matchesUnInheritedFormatSelector: matchesUnInheritedFormatSelector
  };

  var splitText = function (node, offset) {
    return node.splitText(offset);
  };
  var split$1 = function (rng) {
    var startContainer = rng.startContainer, startOffset = rng.startOffset, endContainer = rng.endContainer, endOffset = rng.endOffset;
    if (startContainer === endContainer && $_9owqcy1qje5o2q0e.isText(startContainer)) {
      if (startOffset > 0 && startOffset < startContainer.nodeValue.length) {
        endContainer = splitText(startContainer, startOffset);
        startContainer = endContainer.previousSibling;
        if (endOffset > startOffset) {
          endOffset = endOffset - startOffset;
          startContainer = endContainer = splitText(endContainer, endOffset).previousSibling;
          endOffset = endContainer.nodeValue.length;
          startOffset = 0;
        } else {
          endOffset = 0;
        }
      }
    } else {
      if ($_9owqcy1qje5o2q0e.isText(startContainer) && startOffset > 0 && startOffset < startContainer.nodeValue.length) {
        startContainer = splitText(startContainer, startOffset);
        startOffset = 0;
      }
      if ($_9owqcy1qje5o2q0e.isText(endContainer) && endOffset > 0 && endOffset < endContainer.nodeValue.length) {
        endContainer = splitText(endContainer, endOffset).previousSibling;
        endOffset = endContainer.nodeValue.length;
      }
    }
    return {
      startContainer: startContainer,
      startOffset: startOffset,
      endContainer: endContainer,
      endOffset: endOffset
    };
  };
  var $_3ksquu3nje5o2qg7 = { split: split$1 };

  var ZWSP$1 = $_4md31c21je5o2q5f.ZWSP;
  var CARET_ID = '_mce_caret';
  var importNode = function (ownerDocument, node) {
    return ownerDocument.importNode(node, true);
  };
  var isCaretNode = function (node) {
    return node.nodeType === 1 && node.id === CARET_ID;
  };
  var getEmptyCaretContainers = function (node) {
    var nodes = [];
    while (node) {
      if (node.nodeType === 3 && node.nodeValue !== ZWSP$1 || node.childNodes.length > 1) {
        return [];
      }
      if (node.nodeType === 1) {
        nodes.push(node);
      }
      node = node.firstChild;
    }
    return nodes;
  };
  var isCaretContainerEmpty = function (node) {
    return getEmptyCaretContainers(node).length > 0;
  };
  var findFirstTextNode = function (node) {
    var walker;
    if (node) {
      walker = new TreeWalker(node, node);
      for (node = walker.current(); node; node = walker.next()) {
        if (node.nodeType === 3) {
          return node;
        }
      }
    }
    return null;
  };
  var createCaretContainer = function (fill) {
    var caretContainer = $_5tcb2dyje5o2pwu.fromTag('span');
    $_3uo9pl14je5o2pxq.setAll(caretContainer, {
      'id': CARET_ID,
      'data-mce-bogus': '1',
      'data-mce-type': 'format-caret'
    });
    if (fill) {
      $_elbl2s2oje5o2q9z.append(caretContainer, $_5tcb2dyje5o2pwu.fromText(ZWSP$1));
    }
    return caretContainer;
  };
  var getParentCaretContainer = function (body, node) {
    while (node && node !== body) {
      if (node.id === CARET_ID) {
        return node;
      }
      node = node.parentNode;
    }
    return null;
  };
  var trimZwspFromCaretContainer = function (caretContainerNode) {
    var textNode = findFirstTextNode(caretContainerNode);
    if (textNode && textNode.nodeValue.charAt(0) === ZWSP$1) {
      textNode.deleteData(0, 1);
    }
    return textNode;
  };
  var removeCaretContainerNode = function (dom, selection, node, moveCaret) {
    var rng, block, textNode;
    rng = selection.getRng(true);
    block = dom.getParent(node, dom.isBlock);
    if (isCaretContainerEmpty(node)) {
      if (moveCaret !== false) {
        rng.setStartBefore(node);
        rng.setEndBefore(node);
      }
      dom.remove(node);
    } else {
      textNode = trimZwspFromCaretContainer(node);
      if (rng.startContainer === textNode && rng.startOffset > 0) {
        rng.setStart(textNode, rng.startOffset - 1);
      }
      if (rng.endContainer === textNode && rng.endOffset > 0) {
        rng.setEnd(textNode, rng.endOffset - 1);
      }
      dom.remove(node, true);
    }
    if (block && dom.isEmpty(block)) {
      $_4kbg8z2nje5o2q9s.fillWithPaddingBr($_5tcb2dyje5o2pwu.fromDom(block));
    }
    selection.setRng(rng);
  };
  var removeCaretContainer = function (body, dom, selection, node, moveCaret) {
    if (!node) {
      node = getParentCaretContainer(body, selection.getStart());
      if (!node) {
        while (node = dom.get(CARET_ID)) {
          removeCaretContainerNode(dom, selection, node, false);
        }
      }
    } else {
      removeCaretContainerNode(dom, selection, node, moveCaret);
    }
  };
  var insertCaretContainerNode = function (editor, caretContainer, formatNode) {
    var dom = editor.dom, block = dom.getParent(formatNode, $_ghq3nq25je5o2q5q.curry($_b2hqtg3lje5o2qft.isTextBlock, editor));
    if (block && dom.isEmpty(block)) {
      formatNode.parentNode.replaceChild(caretContainer, formatNode);
    } else {
      $_4kbg8z2nje5o2q9s.removeTrailingBr($_5tcb2dyje5o2pwu.fromDom(formatNode));
      if (dom.isEmpty(formatNode)) {
        formatNode.parentNode.replaceChild(caretContainer, formatNode);
      } else {
        dom.insertAfter(caretContainer, formatNode);
      }
    }
  };
  var appendNode = function (parentNode, node) {
    parentNode.appendChild(node);
    return node;
  };
  var insertFormatNodesIntoCaretContainer = function (formatNodes, caretContainer) {
    var innerMostFormatNode = $_bwcteb4je5o2pl4.foldr(formatNodes, function (parentNode, formatNode) {
      return appendNode(parentNode, formatNode.cloneNode(false));
    }, caretContainer);
    return appendNode(innerMostFormatNode, innerMostFormatNode.ownerDocument.createTextNode(ZWSP$1));
  };
  var applyCaretFormat = function (editor, name, vars) {
    var rng, caretContainer, textNode, offset, bookmark, container, text;
    var selection = editor.selection;
    rng = selection.getRng(true);
    offset = rng.startOffset;
    container = rng.startContainer;
    text = container.nodeValue;
    caretContainer = getParentCaretContainer(editor.getBody(), selection.getStart());
    if (caretContainer) {
      textNode = findFirstTextNode(caretContainer);
    }
    var wordcharRegex = /[^\s\u00a0\u00ad\u200b\ufeff]/;
    if (text && offset > 0 && offset < text.length && wordcharRegex.test(text.charAt(offset)) && wordcharRegex.test(text.charAt(offset - 1))) {
      bookmark = selection.getBookmark();
      rng.collapse(true);
      rng = $_8wd5fu3kje5o2qfi.expandRng(editor, rng, editor.formatter.get(name));
      rng = $_3ksquu3nje5o2qg7.split(rng);
      editor.formatter.apply(name, vars, rng);
      selection.moveToBookmark(bookmark);
    } else {
      if (!caretContainer || textNode.nodeValue !== ZWSP$1) {
        caretContainer = importNode(editor.getDoc(), createCaretContainer(true).dom());
        textNode = caretContainer.firstChild;
        rng.insertNode(caretContainer);
        offset = 1;
        editor.formatter.apply(name, vars, caretContainer);
      } else {
        editor.formatter.apply(name, vars, caretContainer);
      }
      selection.setCursorLocation(textNode, offset);
    }
  };
  var removeCaretFormat = function (editor, name, vars, similar) {
    var dom = editor.dom, selection = editor.selection;
    var container, offset, bookmark;
    var hasContentAfter, node, formatNode;
    var parents = [], rng = selection.getRng();
    var caretContainer;
    container = rng.startContainer;
    offset = rng.startOffset;
    node = container;
    if (container.nodeType === 3) {
      if (offset !== container.nodeValue.length) {
        hasContentAfter = true;
      }
      node = node.parentNode;
    }
    while (node) {
      if ($_g32xfv3mje5o2qg1.matchNode(editor, node, name, vars, similar)) {
        formatNode = node;
        break;
      }
      if (node.nextSibling) {
        hasContentAfter = true;
      }
      parents.push(node);
      node = node.parentNode;
    }
    if (!formatNode) {
      return;
    }
    if (hasContentAfter) {
      bookmark = selection.getBookmark();
      rng.collapse(true);
      var expandedRng = $_8wd5fu3kje5o2qfi.expandRng(editor, rng, editor.formatter.get(name), true);
      expandedRng = $_3ksquu3nje5o2qg7.split(expandedRng);
      editor.formatter.remove(name, vars, expandedRng);
      selection.moveToBookmark(bookmark);
    } else {
      caretContainer = getParentCaretContainer(editor.getBody(), formatNode);
      var newCaretContainer = createCaretContainer(false).dom();
      var caretNode = insertFormatNodesIntoCaretContainer(parents, newCaretContainer);
      if (caretContainer) {
        insertCaretContainerNode(editor, newCaretContainer, caretContainer);
      } else {
        insertCaretContainerNode(editor, newCaretContainer, formatNode);
      }
      removeCaretContainerNode(dom, selection, caretContainer, false);
      selection.setCursorLocation(caretNode, 1);
      if (dom.isEmpty(formatNode)) {
        dom.remove(formatNode);
      }
    }
  };
  var disableCaretContainer = function (body, dom, selection, keyCode) {
    removeCaretContainer(body, dom, selection, null, false);
    if (keyCode === 8 && selection.isCollapsed() && selection.getStart().innerHTML === ZWSP$1) {
      removeCaretContainer(body, dom, selection, getParentCaretContainer(body, selection.getStart()));
    }
    if (keyCode === 37 || keyCode === 39) {
      removeCaretContainer(body, dom, selection, getParentCaretContainer(body, selection.getStart()));
    }
  };
  var setup = function (editor) {
    var dom = editor.dom, selection = editor.selection;
    var body = editor.getBody();
    editor.on('mouseup keydown', function (e) {
      disableCaretContainer(body, dom, selection, e.keyCode);
    });
  };
  var replaceWithCaretFormat = function (targetNode, formatNodes) {
    var caretContainer = createCaretContainer(false);
    var innerMost = insertFormatNodesIntoCaretContainer(formatNodes, caretContainer.dom());
    $_elbl2s2oje5o2q9z.before($_5tcb2dyje5o2pwu.fromDom(targetNode), caretContainer);
    $_cr1seh2pje5o2qa1.remove($_5tcb2dyje5o2pwu.fromDom(targetNode));
    return CaretPosition$1(innerMost, 0);
  };
  var isFormatElement = function (editor, element) {
    var inlineElements = editor.schema.getTextInlineElements();
    return inlineElements.hasOwnProperty($_95wn8qzje5o2px0.name(element)) && !isCaretNode(element.dom()) && !$_9owqcy1qje5o2q0e.isBogus(element.dom());
  };
  var $_4066yu3jje5o2qev = {
    setup: setup,
    applyCaretFormat: applyCaretFormat,
    removeCaretFormat: removeCaretFormat,
    isCaretNode: isCaretNode,
    getParentCaretContainer: getParentCaretContainer,
    replaceWithCaretFormat: replaceWithCaretFormat,
    isFormatElement: isFormatElement
  };

  var evaluateUntil = function (fns, args) {
    for (var i = 0; i < fns.length; i++) {
      var result = fns[i].apply(null, args);
      if (result.isSome()) {
        return result;
      }
    }
    return Option.none();
  };
  var $_cq1fkk3oje5o2qg9 = { evaluateUntil: evaluateUntil };

  var Location = $_e73lvt3dje5o2qdq.generate([
    { before: ['element'] },
    { start: ['element'] },
    { end: ['element'] },
    { after: ['element'] }
  ]);
  var rescope = function (rootNode, node) {
    var parentBlock = getParentBlock(node, rootNode);
    return parentBlock ? parentBlock : rootNode;
  };
  var before$3 = function (isInlineTarget, rootNode, pos) {
    var nPos = $_6skw9732je5o2qbp.normalizeForwards(pos);
    var scope = rescope(rootNode, nPos.container());
    return $_6skw9732je5o2qbp.findRootInline(isInlineTarget, scope, nPos).fold(function () {
      return $_g2kzjo2bje5o2q7n.nextPosition(scope, nPos).bind($_enfhkv6je5o2ply.curry($_6skw9732je5o2qbp.findRootInline, isInlineTarget, scope)).map(function (inline) {
        return Location.before(inline);
      });
    }, Option.none);
  };
  var isNotInsideFormatCaretContainer = function (rootNode, elm) {
    return $_4066yu3jje5o2qev.getParentCaretContainer(rootNode, elm) === null;
  };
  var findInsideRootInline = function (isInlineTarget, rootNode, pos) {
    return $_6skw9732je5o2qbp.findRootInline(isInlineTarget, rootNode, pos).filter($_enfhkv6je5o2ply.curry(isNotInsideFormatCaretContainer, rootNode));
  };
  var start = function (isInlineTarget, rootNode, pos) {
    var nPos = $_6skw9732je5o2qbp.normalizeBackwards(pos);
    return findInsideRootInline(isInlineTarget, rootNode, nPos).bind(function (inline) {
      var prevPos = $_g2kzjo2bje5o2q7n.prevPosition(inline, nPos);
      return prevPos.isNone() ? Option.some(Location.start(inline)) : Option.none();
    });
  };
  var end = function (isInlineTarget, rootNode, pos) {
    var nPos = $_6skw9732je5o2qbp.normalizeForwards(pos);
    return findInsideRootInline(isInlineTarget, rootNode, nPos).bind(function (inline) {
      var nextPos = $_g2kzjo2bje5o2q7n.nextPosition(inline, nPos);
      return nextPos.isNone() ? Option.some(Location.end(inline)) : Option.none();
    });
  };
  var after$3 = function (isInlineTarget, rootNode, pos) {
    var nPos = $_6skw9732je5o2qbp.normalizeBackwards(pos);
    var scope = rescope(rootNode, nPos.container());
    return $_6skw9732je5o2qbp.findRootInline(isInlineTarget, scope, nPos).fold(function () {
      return $_g2kzjo2bje5o2q7n.prevPosition(scope, nPos).bind($_enfhkv6je5o2ply.curry($_6skw9732je5o2qbp.findRootInline, isInlineTarget, scope)).map(function (inline) {
        return Location.after(inline);
      });
    }, Option.none);
  };
  var isValidLocation = function (location) {
    return $_6skw9732je5o2qbp.isRtl(getElement(location)) === false;
  };
  var readLocation = function (isInlineTarget, rootNode, pos) {
    var location = $_cq1fkk3oje5o2qg9.evaluateUntil([
      before$3,
      start,
      end,
      after$3
    ], [
      isInlineTarget,
      rootNode,
      pos
    ]);
    return location.filter(isValidLocation);
  };
  var getElement = function (location) {
    return location.fold($_enfhkv6je5o2ply.identity, $_enfhkv6je5o2ply.identity, $_enfhkv6je5o2ply.identity, $_enfhkv6je5o2ply.identity);
  };
  var getName = function (location) {
    return location.fold($_enfhkv6je5o2ply.constant('before'), $_enfhkv6je5o2ply.constant('start'), $_enfhkv6je5o2ply.constant('end'), $_enfhkv6je5o2ply.constant('after'));
  };
  var outside = function (location) {
    return location.fold(Location.before, Location.before, Location.after, Location.after);
  };
  var inside = function (location) {
    return location.fold(Location.start, Location.start, Location.end, Location.end);
  };
  var isEq$2 = function (location1, location2) {
    return getName(location1) === getName(location2) && getElement(location1) === getElement(location2);
  };
  var betweenInlines = function (forward, isInlineTarget, rootNode, from, to, location) {
    return $_c8weyc2eje5o2q85.liftN([
      $_6skw9732je5o2qbp.findRootInline(isInlineTarget, rootNode, from),
      $_6skw9732je5o2qbp.findRootInline(isInlineTarget, rootNode, to)
    ], function (fromInline, toInline) {
      if (fromInline !== toInline && $_6skw9732je5o2qbp.hasSameParentBlock(rootNode, fromInline, toInline)) {
        return Location.after(forward ? fromInline : toInline);
      } else {
        return location;
      }
    }).getOr(location);
  };
  var skipNoMovement = function (fromLocation, toLocation) {
    return fromLocation.fold($_enfhkv6je5o2ply.constant(true), function (fromLocation) {
      return !isEq$2(fromLocation, toLocation);
    });
  };
  var findLocationTraverse = function (forward, isInlineTarget, rootNode, fromLocation, pos) {
    var from = $_6skw9732je5o2qbp.normalizePosition(forward, pos);
    var to = $_g2kzjo2bje5o2q7n.fromPosition(forward, rootNode, from).map($_enfhkv6je5o2ply.curry($_6skw9732je5o2qbp.normalizePosition, forward));
    var location = to.fold(function () {
      return fromLocation.map(outside);
    }, function (to) {
      return readLocation(isInlineTarget, rootNode, to).map($_enfhkv6je5o2ply.curry(betweenInlines, forward, isInlineTarget, rootNode, from, to)).filter($_enfhkv6je5o2ply.curry(skipNoMovement, fromLocation));
    });
    return location.filter(isValidLocation);
  };
  var findLocationSimple = function (forward, location) {
    if (forward) {
      return location.fold($_enfhkv6je5o2ply.compose(Option.some, Location.start), Option.none, $_enfhkv6je5o2ply.compose(Option.some, Location.after), Option.none);
    } else {
      return location.fold(Option.none, $_enfhkv6je5o2ply.compose(Option.some, Location.before), Option.none, $_enfhkv6je5o2ply.compose(Option.some, Location.end));
    }
  };
  var findLocation = function (forward, isInlineTarget, rootNode, pos) {
    var from = $_6skw9732je5o2qbp.normalizePosition(forward, pos);
    var fromLocation = readLocation(isInlineTarget, rootNode, from);
    return readLocation(isInlineTarget, rootNode, from).bind($_enfhkv6je5o2ply.curry(findLocationSimple, forward)).orThunk(function () {
      return findLocationTraverse(forward, isInlineTarget, rootNode, fromLocation, pos);
    });
  };
  var $_62vpc23ije5o2qem = {
    readLocation: readLocation,
    findLocation: findLocation,
    prevLocation: $_enfhkv6je5o2ply.curry(findLocation, false),
    nextLocation: $_enfhkv6je5o2ply.curry(findLocation, true),
    getElement: getElement,
    outside: outside,
    inside: inside
  };

  var Cell = function (initial) {
    var value = initial;
    var get = function () {
      return value;
    };
    var set = function (v) {
      value = v;
    };
    var clone = function () {
      return Cell(get());
    };
    return {
      get: get,
      set: set,
      clone: clone
    };
  };

  var hasSelectionModifyApi = function (editor) {
    return $_be3q8w12je5o2pxj.isFunction(editor.selection.getSel().modify);
  };
  var moveRel = function (forward, selection, pos) {
    var delta = forward ? 1 : -1;
    selection.setRng(CaretPosition$1(pos.container(), pos.offset() + delta).toRange());
    selection.getSel().modify('move', forward ? 'forward' : 'backward', 'word');
    return true;
  };
  var moveByWord = function (forward, editor) {
    var rng = editor.selection.getRng();
    var pos = forward ? CaretPosition$1.fromRangeEnd(rng) : CaretPosition$1.fromRangeStart(rng);
    if (!hasSelectionModifyApi(editor)) {
      return false;
    } else if (forward && isBeforeInline(pos)) {
      return moveRel(true, editor.selection, pos);
    } else if (!forward && isAfterInline(pos)) {
      return moveRel(false, editor.selection, pos);
    } else {
      return false;
    }
  };
  var $_ffszpz3rje5o2qgl = {
    hasSelectionModifyApi: hasSelectionModifyApi,
    moveByWord: moveByWord
  };

  var setCaretPosition = function (editor, pos) {
    var rng = editor.dom.createRng();
    rng.setStart(pos.container(), pos.offset());
    rng.setEnd(pos.container(), pos.offset());
    editor.selection.setRng(rng);
  };
  var isFeatureEnabled = function (editor) {
    return editor.settings.inline_boundaries !== false;
  };
  var setSelected = function (state, elm) {
    if (state) {
      elm.setAttribute('data-mce-selected', 'inline-boundary');
    } else {
      elm.removeAttribute('data-mce-selected');
    }
  };
  var renderCaretLocation = function (editor, caret, location) {
    return $_5a4sgg3gje5o2qee.renderCaret(caret, location).map(function (pos) {
      setCaretPosition(editor, pos);
      return location;
    });
  };
  var findLocation$1 = function (editor, caret, forward) {
    var rootNode = editor.getBody();
    var from = CaretPosition$1.fromRangeStart(editor.selection.getRng());
    var isInlineTarget = $_enfhkv6je5o2ply.curry($_6skw9732je5o2qbp.isInlineTarget, editor);
    var location = $_62vpc23ije5o2qem.findLocation(forward, isInlineTarget, rootNode, from);
    return location.bind(function (location) {
      return renderCaretLocation(editor, caret, location);
    });
  };
  var toggleInlines = function (isInlineTarget, dom, elms) {
    var selectedInlines = $_bwcteb4je5o2pl4.filter(dom.select('*[data-mce-selected="inline-boundary"]'), isInlineTarget);
    var targetInlines = $_bwcteb4je5o2pl4.filter(elms, isInlineTarget);
    $_bwcteb4je5o2pl4.each($_bwcteb4je5o2pl4.difference(selectedInlines, targetInlines), $_enfhkv6je5o2ply.curry(setSelected, false));
    $_bwcteb4je5o2pl4.each($_bwcteb4je5o2pl4.difference(targetInlines, selectedInlines), $_enfhkv6je5o2ply.curry(setSelected, true));
  };
  var safeRemoveCaretContainer = function (editor, caret) {
    if (editor.selection.isCollapsed() && editor.composing !== true && caret.get()) {
      var pos = CaretPosition$1.fromRangeStart(editor.selection.getRng());
      if (CaretPosition$1.isTextPosition(pos) && $_6skw9732je5o2qbp.isAtZwsp(pos) === false) {
        setCaretPosition(editor, $_3djwbs29je5o2q74.removeAndReposition(caret.get(), pos));
        caret.set(null);
      }
    }
  };
  var renderInsideInlineCaret = function (isInlineTarget, editor, caret, elms) {
    if (editor.selection.isCollapsed()) {
      var inlines = $_bwcteb4je5o2pl4.filter(elms, isInlineTarget);
      $_bwcteb4je5o2pl4.each(inlines, function (inline) {
        var pos = CaretPosition$1.fromRangeStart(editor.selection.getRng());
        $_62vpc23ije5o2qem.readLocation(isInlineTarget, editor.getBody(), pos).bind(function (location) {
          return renderCaretLocation(editor, caret, location);
        });
      });
    }
  };
  var move = function (editor, caret, forward) {
    return function () {
      return isFeatureEnabled(editor) ? findLocation$1(editor, caret, forward).isSome() : false;
    };
  };
  var moveWord = function (forward, editor, caret) {
    return function () {
      return isFeatureEnabled(editor) ? $_ffszpz3rje5o2qgl.moveByWord(forward, editor) : false;
    };
  };
  var setupSelectedState = function (editor) {
    var caret = Cell(null);
    var isInlineTarget = $_enfhkv6je5o2ply.curry($_6skw9732je5o2qbp.isInlineTarget, editor);
    editor.on('NodeChange', function (e) {
      if (isFeatureEnabled(editor)) {
        toggleInlines(isInlineTarget, editor.dom, e.parents);
        safeRemoveCaretContainer(editor, caret);
        renderInsideInlineCaret(isInlineTarget, editor, caret, e.parents);
      }
    });
    return caret;
  };
  var $_2dnted3pje5o2qgc = {
    move: move,
    moveNextWord: $_enfhkv6je5o2ply.curry(moveWord, true),
    movePrevWord: $_enfhkv6je5o2ply.curry(moveWord, false),
    setupSelectedState: setupSelectedState,
    setCaretPosition: setCaretPosition
  };

  var isFeatureEnabled$1 = function (editor) {
    return editor.settings.inline_boundaries !== false;
  };
  var rangeFromPositions = function (from, to) {
    var range = document.createRange();
    range.setStart(from.container(), from.offset());
    range.setEnd(to.container(), to.offset());
    return range;
  };
  var hasOnlyTwoOrLessPositionsLeft = function (elm) {
    return $_c8weyc2eje5o2q85.liftN([
      $_g2kzjo2bje5o2q7n.firstPositionIn(elm),
      $_g2kzjo2bje5o2q7n.lastPositionIn(elm)
    ], function (firstPos, lastPos) {
      var normalizedFirstPos = $_6skw9732je5o2qbp.normalizePosition(true, firstPos);
      var normalizedLastPos = $_6skw9732je5o2qbp.normalizePosition(false, lastPos);
      return $_g2kzjo2bje5o2q7n.nextPosition(elm, normalizedFirstPos).map(function (pos) {
        return pos.isEqual(normalizedLastPos);
      }).getOr(true);
    }).getOr(true);
  };
  var setCaretLocation = function (editor, caret) {
    return function (location) {
      return $_5a4sgg3gje5o2qee.renderCaret(caret, location).map(function (pos) {
        $_2dnted3pje5o2qgc.setCaretPosition(editor, pos);
        return true;
      }).getOr(false);
    };
  };
  var deleteFromTo = function (editor, caret, from, to) {
    var rootNode = editor.getBody();
    var isInlineTarget = $_enfhkv6je5o2ply.curry($_6skw9732je5o2qbp.isInlineTarget, editor);
    editor.undoManager.ignore(function () {
      editor.selection.setRng(rangeFromPositions(from, to));
      editor.execCommand('Delete');
      $_62vpc23ije5o2qem.readLocation(isInlineTarget, rootNode, CaretPosition$1.fromRangeStart(editor.selection.getRng())).map($_62vpc23ije5o2qem.inside).map(setCaretLocation(editor, caret));
    });
    editor.nodeChanged();
  };
  var rescope$1 = function (rootNode, node) {
    var parentBlock = getParentBlock(node, rootNode);
    return parentBlock ? parentBlock : rootNode;
  };
  var backspaceDeleteCollapsed = function (editor, caret, forward, from) {
    var rootNode = rescope$1(editor.getBody(), from.container());
    var isInlineTarget = $_enfhkv6je5o2ply.curry($_6skw9732je5o2qbp.isInlineTarget, editor);
    var fromLocation = $_62vpc23ije5o2qem.readLocation(isInlineTarget, rootNode, from);
    return fromLocation.bind(function (location) {
      if (forward) {
        return location.fold($_enfhkv6je5o2ply.constant(Option.some($_62vpc23ije5o2qem.inside(location))), Option.none, $_enfhkv6je5o2ply.constant(Option.some($_62vpc23ije5o2qem.outside(location))), Option.none);
      } else {
        return location.fold(Option.none, $_enfhkv6je5o2ply.constant(Option.some($_62vpc23ije5o2qem.outside(location))), Option.none, $_enfhkv6je5o2ply.constant(Option.some($_62vpc23ije5o2qem.inside(location))));
      }
    }).map(setCaretLocation(editor, caret)).getOrThunk(function () {
      var toPosition = $_g2kzjo2bje5o2q7n.navigate(forward, rootNode, from);
      var toLocation = toPosition.bind(function (pos) {
        return $_62vpc23ije5o2qem.readLocation(isInlineTarget, rootNode, pos);
      });
      if (fromLocation.isSome() && toLocation.isSome()) {
        return $_6skw9732je5o2qbp.findRootInline(isInlineTarget, rootNode, from).map(function (elm) {
          if (hasOnlyTwoOrLessPositionsLeft(elm)) {
            $_8fuvl23eje5o2qdv.deleteElement(editor, forward, $_5tcb2dyje5o2pwu.fromDom(elm));
            return true;
          } else {
            return false;
          }
        }).getOr(false);
      } else {
        return toLocation.bind(function (_) {
          return toPosition.map(function (to) {
            if (forward) {
              deleteFromTo(editor, caret, from, to);
            } else {
              deleteFromTo(editor, caret, to, from);
            }
            return true;
          });
        }).getOr(false);
      }
    });
  };
  var backspaceDelete$3 = function (editor, caret, forward) {
    if (editor.selection.isCollapsed() && isFeatureEnabled$1(editor)) {
      var from = CaretPosition$1.fromRangeStart(editor.selection.getRng());
      return backspaceDeleteCollapsed(editor, caret, forward, from);
    }
    return false;
  };
  var $_bzwx3b3fje5o2qe7 = { backspaceDelete: backspaceDelete$3 };

  var tableCellRng = $_86drv18je5o2pyn.immutable('start', 'end');
  var tableSelection = $_86drv18je5o2pyn.immutable('rng', 'table', 'cells');
  var deleteAction = $_e73lvt3dje5o2qdq.generate([
    { removeTable: ['element'] },
    { emptyCells: ['cells'] }
  ]);
  var isRootFromElement = function (root) {
    return $_enfhkv6je5o2ply.curry($_7oppmo1dje5o2pz1.eq, root);
  };
  var getClosestCell$1 = function (container, isRoot) {
    return $_9fzewc37je5o2qck.closest($_5tcb2dyje5o2pwu.fromDom(container), 'td,th', isRoot);
  };
  var getClosestTable = function (cell, isRoot) {
    return $_9fzewc37je5o2qck.ancestor(cell, 'table', isRoot);
  };
  var isExpandedCellRng = function (cellRng) {
    return $_7oppmo1dje5o2pz1.eq(cellRng.start(), cellRng.end()) === false;
  };
  var getTableFromCellRng = function (cellRng, isRoot) {
    return getClosestTable(cellRng.start(), isRoot).bind(function (startParentTable) {
      return getClosestTable(cellRng.end(), isRoot).bind(function (endParentTable) {
        return $_7oppmo1dje5o2pz1.eq(startParentTable, endParentTable) ? Option.some(startParentTable) : Option.none();
      });
    });
  };
  var getTableCells = function (table) {
    return $_b4jhpu2gje5o2q8e.descendants(table, 'td,th');
  };
  var getCellRangeFromStartTable = function (cellRng, isRoot) {
    return getClosestTable(cellRng.start(), isRoot).bind(function (table) {
      return $_bwcteb4je5o2pl4.last(getTableCells(table)).map(function (endCell) {
        return tableCellRng(cellRng.start(), endCell);
      });
    });
  };
  var partialSelection = function (isRoot, rng) {
    var startCell = getClosestCell$1(rng.startContainer, isRoot);
    var endCell = getClosestCell$1(rng.endContainer, isRoot);
    return rng.collapsed ? Option.none() : $_c8weyc2eje5o2q85.liftN([
      startCell,
      endCell
    ], tableCellRng).fold(function () {
      return startCell.fold(function () {
        return endCell.bind(function (endCell) {
          return getClosestTable(endCell, isRoot).bind(function (table) {
            return $_bwcteb4je5o2pl4.head(getTableCells(table)).map(function (startCell) {
              return tableCellRng(startCell, endCell);
            });
          });
        });
      }, function (startCell) {
        return getClosestTable(startCell, isRoot).bind(function (table) {
          return $_bwcteb4je5o2pl4.last(getTableCells(table)).map(function (endCell) {
            return tableCellRng(startCell, endCell);
          });
        });
      });
    }, function (cellRng) {
      return isWithinSameTable(isRoot, cellRng) ? Option.none() : getCellRangeFromStartTable(cellRng, isRoot);
    });
  };
  var isWithinSameTable = function (isRoot, cellRng) {
    return getTableFromCellRng(cellRng, isRoot).isSome();
  };
  var getCellRng = function (rng, isRoot) {
    var startCell = getClosestCell$1(rng.startContainer, isRoot);
    var endCell = getClosestCell$1(rng.endContainer, isRoot);
    return $_c8weyc2eje5o2q85.liftN([
      startCell,
      endCell
    ], tableCellRng).filter(isExpandedCellRng).filter(function (cellRng) {
      return isWithinSameTable(isRoot, cellRng);
    }).orThunk(function () {
      return partialSelection(isRoot, rng);
    });
  };
  var getTableSelectionFromCellRng = function (cellRng, isRoot) {
    return getTableFromCellRng(cellRng, isRoot).map(function (table) {
      return tableSelection(cellRng, table, getTableCells(table));
    });
  };
  var getTableSelectionFromRng = function (root, rng) {
    var isRoot = isRootFromElement(root);
    return getCellRng(rng, isRoot).bind(function (cellRng) {
      return getTableSelectionFromCellRng(cellRng, isRoot);
    });
  };
  var getCellIndex = function (cells, cell) {
    return $_bwcteb4je5o2pl4.findIndex(cells, function (x) {
      return $_7oppmo1dje5o2pz1.eq(x, cell);
    });
  };
  var getSelectedCells = function (tableSelection) {
    return $_c8weyc2eje5o2q85.liftN([
      getCellIndex(tableSelection.cells(), tableSelection.rng().start()),
      getCellIndex(tableSelection.cells(), tableSelection.rng().end())
    ], function (startIndex, endIndex) {
      return tableSelection.cells().slice(startIndex, endIndex + 1);
    });
  };
  var getAction = function (tableSelection) {
    return getSelectedCells(tableSelection).map(function (selected) {
      var cells = tableSelection.cells();
      return selected.length === cells.length ? deleteAction.removeTable(tableSelection.table()) : deleteAction.emptyCells(selected);
    });
  };
  var getActionFromCells = function (cells) {
    return deleteAction.emptyCells(cells);
  };
  var getActionFromRange = function (root, rng) {
    return getTableSelectionFromRng(root, rng).bind(getAction);
  };
  var $_fdeflh3tje5o2qh0 = {
    getActionFromRange: getActionFromRange,
    getActionFromCells: getActionFromCells
  };

  var getRanges = function (selection) {
    var ranges = [];
    if (selection) {
      for (var i = 0; i < selection.rangeCount; i++) {
        ranges.push(selection.getRangeAt(i));
      }
    }
    return ranges;
  };
  var getSelectedNodes = function (ranges) {
    return $_bwcteb4je5o2pl4.bind(ranges, function (range) {
      var node = getSelectedNode(range);
      return node ? [$_5tcb2dyje5o2pwu.fromDom(node)] : [];
    });
  };
  var hasMultipleRanges = function (selection) {
    return getRanges(selection).length > 1;
  };
  var $_b8hi2e3vje5o2qhh = {
    getRanges: getRanges,
    getSelectedNodes: getSelectedNodes,
    hasMultipleRanges: hasMultipleRanges
  };

  var getCellsFromRanges = function (ranges) {
    return $_bwcteb4je5o2pl4.filter($_b8hi2e3vje5o2qhh.getSelectedNodes(ranges), isTableCell);
  };
  var getCellsFromElement = function (elm) {
    var selectedCells = $_b4jhpu2gje5o2q8e.descendants(elm, 'td[data-mce-selected],th[data-mce-selected]');
    return selectedCells;
  };
  var getCellsFromElementOrRanges = function (ranges, element) {
    var selectedCells = getCellsFromElement(element);
    var rangeCells = getCellsFromRanges(ranges);
    return selectedCells.length > 0 ? selectedCells : rangeCells;
  };
  var getCellsFromEditor = function (editor) {
    return getCellsFromElementOrRanges($_b8hi2e3vje5o2qhh.getRanges(editor.selection.getSel()), $_5tcb2dyje5o2pwu.fromDom(editor.getBody()));
  };
  var $_9il6og3uje5o2qhd = {
    getCellsFromRanges: getCellsFromRanges,
    getCellsFromElement: getCellsFromElement,
    getCellsFromElementOrRanges: getCellsFromElementOrRanges,
    getCellsFromEditor: getCellsFromEditor
  };

  var emptyCells = function (editor, cells) {
    $_bwcteb4je5o2pl4.each(cells, $_4kbg8z2nje5o2q9s.fillWithPaddingBr);
    editor.selection.setCursorLocation(cells[0].dom(), 0);
    return true;
  };
  var deleteTableElement = function (editor, table) {
    $_8fuvl23eje5o2qdv.deleteElement(editor, false, table);
    return true;
  };
  var deleteCellRange = function (editor, rootElm, rng) {
    return $_fdeflh3tje5o2qh0.getActionFromRange(rootElm, rng).map(function (action) {
      return action.fold($_enfhkv6je5o2ply.curry(deleteTableElement, editor), $_enfhkv6je5o2ply.curry(emptyCells, editor));
    });
  };
  var deleteCaptionRange = function (editor, caption) {
    return emptyElement(editor, caption);
  };
  var deleteTableRange = function (editor, rootElm, rng, startElm) {
    return getParentCaption(rootElm, startElm).fold(function () {
      return deleteCellRange(editor, rootElm, rng);
    }, function (caption) {
      return deleteCaptionRange(editor, caption);
    }).getOr(false);
  };
  var deleteRange$1 = function (editor, startElm) {
    var rootNode = $_5tcb2dyje5o2pwu.fromDom(editor.getBody());
    var rng = editor.selection.getRng();
    var selectedCells = $_9il6og3uje5o2qhd.getCellsFromEditor(editor);
    return selectedCells.length !== 0 ? emptyCells(editor, selectedCells) : deleteTableRange(editor, rootNode, rng, startElm);
  };
  var getParentCell = function (rootElm, elm) {
    return $_bwcteb4je5o2pl4.find($_yqcmh39je5o2qcw.parentsAndSelf(elm, rootElm), isTableCell);
  };
  var getParentCaption = function (rootElm, elm) {
    return $_bwcteb4je5o2pl4.find($_yqcmh39je5o2qcw.parentsAndSelf(elm, rootElm), function (elm) {
      return $_95wn8qzje5o2px0.name(elm) === 'caption';
    });
  };
  var deleteBetweenCells = function (editor, rootElm, forward, fromCell, from) {
    return $_g2kzjo2bje5o2q7n.navigate(forward, editor.getBody(), from).bind(function (to) {
      return getParentCell(rootElm, $_5tcb2dyje5o2pwu.fromDom(to.getNode())).map(function (toCell) {
        return $_7oppmo1dje5o2pz1.eq(toCell, fromCell) === false;
      });
    });
  };
  var emptyElement = function (editor, elm) {
    $_4kbg8z2nje5o2q9s.fillWithPaddingBr(elm);
    editor.selection.setCursorLocation(elm.dom(), 0);
    return Option.some(true);
  };
  var isDeleteOfLastCharPos = function (fromCaption, forward, from, to) {
    return $_g2kzjo2bje5o2q7n.firstPositionIn(fromCaption.dom()).bind(function (first) {
      return $_g2kzjo2bje5o2q7n.lastPositionIn(fromCaption.dom()).map(function (last) {
        return forward ? from.isEqual(first) && to.isEqual(last) : from.isEqual(last) && to.isEqual(first);
      });
    }).getOr(true);
  };
  var emptyCaretCaption = function (editor, elm) {
    return emptyElement(editor, elm);
  };
  var validateCaretCaption = function (rootElm, fromCaption, to) {
    return getParentCaption(rootElm, $_5tcb2dyje5o2pwu.fromDom(to.getNode())).map(function (toCaption) {
      return $_7oppmo1dje5o2pz1.eq(toCaption, fromCaption) === false;
    });
  };
  var deleteCaretInsideCaption = function (editor, rootElm, forward, fromCaption, from) {
    return $_g2kzjo2bje5o2q7n.navigate(forward, editor.getBody(), from).bind(function (to) {
      return isDeleteOfLastCharPos(fromCaption, forward, from, to) ? emptyCaretCaption(editor, fromCaption) : validateCaretCaption(rootElm, fromCaption, to);
    }).or(Option.some(true));
  };
  var deleteCaretCells = function (editor, forward, rootElm, startElm) {
    var from = CaretPosition$1.fromRangeStart(editor.selection.getRng());
    return getParentCell(rootElm, startElm).bind(function (fromCell) {
      return $_dizx5c35je5o2qcb.isEmpty(fromCell) ? emptyElement(editor, fromCell) : deleteBetweenCells(editor, rootElm, forward, fromCell, from);
    });
  };
  var deleteCaretCaption = function (editor, forward, rootElm, fromCaption) {
    var from = CaretPosition$1.fromRangeStart(editor.selection.getRng());
    return $_dizx5c35je5o2qcb.isEmpty(fromCaption) ? emptyElement(editor, fromCaption) : deleteCaretInsideCaption(editor, rootElm, forward, fromCaption, from);
  };
  var deleteCaret = function (editor, forward, startElm) {
    var rootElm = $_5tcb2dyje5o2pwu.fromDom(editor.getBody());
    return getParentCaption(rootElm, startElm).fold(function () {
      return deleteCaretCells(editor, forward, rootElm, startElm);
    }, function (fromCaption) {
      return deleteCaretCaption(editor, forward, rootElm, fromCaption);
    }).getOr(false);
  };
  var backspaceDelete$4 = function (editor, forward) {
    var startElm = $_5tcb2dyje5o2pwu.fromDom(editor.selection.getStart(true));
    var cells = $_9il6og3uje5o2qhd.getCellsFromEditor(editor);
    return editor.selection.isCollapsed() && cells.length === 0 ? deleteCaret(editor, forward, startElm) : deleteRange$1(editor, startElm);
  };
  var $_8pfx7e3sje5o2qgp = { backspaceDelete: backspaceDelete$4 };

  var nativeCommand = function (editor, command) {
    editor.getDoc().execCommand(command, false, null);
  };
  var deleteCommand = function (editor) {
    if ($_c7csnv3bje5o2qd6.backspaceDelete(editor, false)) {
      return;
    } else if ($_bzwx3b3fje5o2qe7.backspaceDelete(editor, false)) {
      return;
    } else if ($_3qfe0j2xje5o2qao.backspaceDelete(editor, false)) {
      return;
    } else if ($_8pfx7e3sje5o2qgp.backspaceDelete(editor)) {
      return;
    } else if ($_a476lo3aje5o2qd0.backspaceDelete(editor, false)) {
      return;
    } else {
      nativeCommand(editor, 'Delete');
      $_7twey62zje5o2qay.paddEmptyBody(editor);
    }
  };
  var forwardDeleteCommand = function (editor) {
    if ($_c7csnv3bje5o2qd6.backspaceDelete(editor, true)) {
      return;
    } else if ($_bzwx3b3fje5o2qe7.backspaceDelete(editor, true)) {
      return;
    } else if ($_3qfe0j2xje5o2qao.backspaceDelete(editor, true)) {
      return;
    } else if ($_8pfx7e3sje5o2qgp.backspaceDelete(editor)) {
      return;
    } else if ($_a476lo3aje5o2qd0.backspaceDelete(editor, true)) {
      return;
    } else {
      nativeCommand(editor, 'ForwardDelete');
    }
  };
  var $_e390q12wje5o2qam = {
    deleteCommand: deleteCommand,
    forwardDeleteCommand: forwardDeleteCommand
  };

  var isEq$3 = function (rng1, rng2) {
    return rng1 && rng2 && (rng1.startContainer === rng2.startContainer && rng1.startOffset === rng2.startOffset) && (rng1.endContainer === rng2.endContainer && rng1.endOffset === rng2.endOffset);
  };
  var $_aieo7z3yje5o2qlw = { isEq: isEq$3 };

  var position = $_86drv18je5o2pyn.immutable('container', 'offset');
  var findParent = function (node, rootNode, predicate) {
    while (node && node !== rootNode) {
      if (predicate(node)) {
        return node;
      }
      node = node.parentNode;
    }
    return null;
  };
  var hasParent = function (node, rootNode, predicate) {
    return findParent(node, rootNode, predicate) !== null;
  };
  var hasParentWithName = function (node, rootNode, name) {
    return hasParent(node, rootNode, function (node) {
      return node.nodeName === name;
    });
  };
  var isTable$2 = function (node) {
    return node && node.nodeName === 'TABLE';
  };
  var isTableCell$3 = function (node) {
    return node && /^(TD|TH|CAPTION)$/.test(node.nodeName);
  };
  var isCeFalseCaretContainer = function (node, rootNode) {
    return isCaretContainer(node) && hasParent(node, rootNode, $_4066yu3jje5o2qev.isCaretNode) === false;
  };
  var hasBrBeforeAfter = function (dom, node, left) {
    var walker = new TreeWalker(node, dom.getParent(node.parentNode, dom.isBlock) || dom.getRoot());
    while (node = walker[left ? 'prev' : 'next']()) {
      if ($_9owqcy1qje5o2q0e.isBr(node)) {
        return true;
      }
    }
  };
  var isPrevNode = function (node, name) {
    return node.previousSibling && node.previousSibling.nodeName === name;
  };
  var hasContentEditableFalseParent = function (body, node) {
    while (node && node !== body) {
      if ($_9owqcy1qje5o2q0e.isContentEditableFalse(node)) {
        return true;
      }
      node = node.parentNode;
    }
    return false;
  };
  var findTextNodeRelative = function (dom, isAfterNode, collapsed, left, startNode) {
    var walker, lastInlineElement, parentBlockContainer;
    var body = dom.getRoot();
    var node;
    var nonEmptyElementsMap = dom.schema.getNonEmptyElements();
    parentBlockContainer = dom.getParent(startNode.parentNode, dom.isBlock) || body;
    if (left && $_9owqcy1qje5o2q0e.isBr(startNode) && isAfterNode && dom.isEmpty(parentBlockContainer)) {
      return Option.some(position(startNode.parentNode, dom.nodeIndex(startNode)));
    }
    walker = new TreeWalker(startNode, parentBlockContainer);
    while (node = walker[left ? 'prev' : 'next']()) {
      if (dom.getContentEditableParent(node) === 'false' || isCeFalseCaretContainer(node, body)) {
        return Option.none();
      }
      if ($_9owqcy1qje5o2q0e.isText(node) && node.nodeValue.length > 0) {
        if (hasParentWithName(node, body, 'A') === false) {
          return Option.some(position(node, left ? node.nodeValue.length : 0));
        }
        return Option.none();
      }
      if (dom.isBlock(node) || nonEmptyElementsMap[node.nodeName.toLowerCase()]) {
        return Option.none();
      }
      lastInlineElement = node;
    }
    if (collapsed && lastInlineElement) {
      return Option.some(position(lastInlineElement, 0));
    }
    return Option.none();
  };
  var normalizeEndPoint = function (dom, collapsed, start, rng) {
    var container, offset, walker;
    var body = dom.getRoot();
    var node, nonEmptyElementsMap;
    var directionLeft, isAfterNode, normalized = false;
    container = rng[(start ? 'start' : 'end') + 'Container'];
    offset = rng[(start ? 'start' : 'end') + 'Offset'];
    isAfterNode = $_9owqcy1qje5o2q0e.isElement(container) && offset === container.childNodes.length;
    nonEmptyElementsMap = dom.schema.getNonEmptyElements();
    directionLeft = start;
    if (isCaretContainer(container)) {
      return Option.none();
    }
    if ($_9owqcy1qje5o2q0e.isElement(container) && offset > container.childNodes.length - 1) {
      directionLeft = false;
    }
    if ($_9owqcy1qje5o2q0e.isDocument(container)) {
      container = body;
      offset = 0;
    }
    if (container === body) {
      if (directionLeft) {
        node = container.childNodes[offset > 0 ? offset - 1 : 0];
        if (node) {
          if (isCaretContainer(node)) {
            return Option.none();
          }
          if (nonEmptyElementsMap[node.nodeName] || isTable$2(node)) {
            return Option.none();
          }
        }
      }
      if (container.hasChildNodes()) {
        offset = Math.min(!directionLeft && offset > 0 ? offset - 1 : offset, container.childNodes.length - 1);
        container = container.childNodes[offset];
        offset = $_9owqcy1qje5o2q0e.isText(container) && isAfterNode ? container.data.length : 0;
        if (!collapsed && container === body.lastChild && isTable$2(container)) {
          return Option.none();
        }
        if (hasContentEditableFalseParent(body, container) || isCaretContainer(container)) {
          return Option.none();
        }
        if (container.hasChildNodes() && isTable$2(container) === false) {
          node = container;
          walker = new TreeWalker(container, body);
          do {
            if ($_9owqcy1qje5o2q0e.isContentEditableFalse(node) || isCaretContainer(node)) {
              normalized = false;
              break;
            }
            if ($_9owqcy1qje5o2q0e.isText(node) && node.nodeValue.length > 0) {
              offset = directionLeft ? 0 : node.nodeValue.length;
              container = node;
              normalized = true;
              break;
            }
            if (nonEmptyElementsMap[node.nodeName.toLowerCase()] && !isTableCell$3(node)) {
              offset = dom.nodeIndex(node);
              container = node.parentNode;
              if ((node.nodeName === 'IMG' || node.nodeName === 'PRE') && !directionLeft) {
                offset++;
              }
              normalized = true;
              break;
            }
          } while (node = directionLeft ? walker.next() : walker.prev());
        }
      }
    }
    if (collapsed) {
      if ($_9owqcy1qje5o2q0e.isText(container) && offset === 0) {
        findTextNodeRelative(dom, isAfterNode, collapsed, true, container).each(function (pos) {
          container = pos.container();
          offset = pos.offset();
          normalized = true;
        });
      }
      if ($_9owqcy1qje5o2q0e.isElement(container)) {
        node = container.childNodes[offset];
        if (!node) {
          node = container.childNodes[offset - 1];
        }
        if (node && $_9owqcy1qje5o2q0e.isBr(node) && !isPrevNode(node, 'A') && !hasBrBeforeAfter(dom, node, false) && !hasBrBeforeAfter(dom, node, true)) {
          findTextNodeRelative(dom, isAfterNode, collapsed, true, node).each(function (pos) {
            container = pos.container();
            offset = pos.offset();
            normalized = true;
          });
        }
      }
    }
    if (directionLeft && !collapsed && $_9owqcy1qje5o2q0e.isText(container) && offset === container.nodeValue.length) {
      findTextNodeRelative(dom, isAfterNode, collapsed, false, container).each(function (pos) {
        container = pos.container();
        offset = pos.offset();
        normalized = true;
      });
    }
    return normalized ? Option.some(position(container, offset)) : Option.none();
  };
  var normalize$1 = function (dom, rng) {
    var collapsed = rng.collapsed, normRng = rng.cloneRange();
    normalizeEndPoint(dom, collapsed, true, normRng).each(function (pos) {
      normRng.setStart(pos.container(), pos.offset());
    });
    if (!collapsed) {
      normalizeEndPoint(dom, collapsed, false, normRng).each(function (pos) {
        normRng.setEnd(pos.container(), pos.offset());
      });
    }
    if (collapsed) {
      normRng.collapse(true);
    }
    return $_aieo7z3yje5o2qlw.isEq(rng, normRng) ? Option.none() : Option.some(normRng);
  };
  var $_1t8l6x3xje5o2qlk = { normalize: normalize$1 };

  var hasRightSideContent = function (schema, container, parentBlock) {
    var walker = new TreeWalker(container, parentBlock);
    var node;
    var nonEmptyElementsMap = schema.getNonEmptyElements();
    while (node = walker.next()) {
      if (nonEmptyElementsMap[node.nodeName.toLowerCase()] || node.length > 0) {
        return true;
      }
    }
  };
  var scrollToBr = function (dom, selection, brElm) {
    var marker = dom.create('span', {}, '&nbsp;');
    brElm.parentNode.insertBefore(marker, brElm);
    selection.scrollIntoView(marker);
    dom.remove(marker);
  };
  var moveSelectionToBr = function (dom, selection, brElm, extraBr) {
    var rng = dom.createRng();
    if (!extraBr) {
      rng.setStartAfter(brElm);
      rng.setEndAfter(brElm);
    } else {
      rng.setStartBefore(brElm);
      rng.setEndBefore(brElm);
    }
    selection.setRng(rng);
  };
  var insertBrAtCaret = function (editor, evt) {
    var selection = editor.selection, dom = editor.dom;
    var brElm, extraBr;
    var rng = selection.getRng();
    $_1t8l6x3xje5o2qlk.normalize(dom, rng).each(function (normRng) {
      rng.setStart(normRng.startContainer, normRng.startOffset);
      rng.setEnd(normRng.endContainer, normRng.endOffset);
    });
    var offset = rng.startOffset;
    var container = rng.startContainer;
    if (container.nodeType === 1 && container.hasChildNodes()) {
      var isAfterLastNodeInContainer = offset > container.childNodes.length - 1;
      container = container.childNodes[Math.min(offset, container.childNodes.length - 1)] || container;
      if (isAfterLastNodeInContainer && container.nodeType === 3) {
        offset = container.nodeValue.length;
      } else {
        offset = 0;
      }
    }
    var parentBlock = dom.getParent(container, dom.isBlock);
    var containerBlock = parentBlock ? dom.getParent(parentBlock.parentNode, dom.isBlock) : null;
    var containerBlockName = containerBlock ? containerBlock.nodeName.toUpperCase() : '';
    var isControlKey = evt && evt.ctrlKey;
    if (containerBlockName === 'LI' && !isControlKey) {
      parentBlock = containerBlock;
    }
    if (container && container.nodeType === 3 && offset >= container.nodeValue.length) {
      if (!hasRightSideContent(editor.schema, container, parentBlock)) {
        brElm = dom.create('br');
        rng.insertNode(brElm);
        rng.setStartAfter(brElm);
        rng.setEndAfter(brElm);
        extraBr = true;
      }
    }
    brElm = dom.create('br');
    rng.insertNode(brElm);
    scrollToBr(dom, selection, brElm);
    moveSelectionToBr(dom, selection, brElm, extraBr);
    editor.undoManager.add();
  };
  var insertBrBefore = function (editor, inline) {
    var br = $_5tcb2dyje5o2pwu.fromTag('br');
    $_elbl2s2oje5o2q9z.before($_5tcb2dyje5o2pwu.fromDom(inline), br);
    editor.undoManager.add();
  };
  var insertBrAfter = function (editor, inline) {
    if (!hasBrAfter(editor.getBody(), inline)) {
      $_elbl2s2oje5o2q9z.after($_5tcb2dyje5o2pwu.fromDom(inline), $_5tcb2dyje5o2pwu.fromTag('br'));
    }
    var br = $_5tcb2dyje5o2pwu.fromTag('br');
    $_elbl2s2oje5o2q9z.after($_5tcb2dyje5o2pwu.fromDom(inline), br);
    scrollToBr(editor.dom, editor.selection, br.dom());
    moveSelectionToBr(editor.dom, editor.selection, br.dom(), false);
    editor.undoManager.add();
  };
  var isBeforeBr = function (pos) {
    return $_9owqcy1qje5o2q0e.isBr(pos.getNode());
  };
  var hasBrAfter = function (rootNode, startNode) {
    if (isBeforeBr(CaretPosition$1.after(startNode))) {
      return true;
    } else {
      return $_g2kzjo2bje5o2q7n.nextPosition(rootNode, CaretPosition$1.after(startNode)).map(function (pos) {
        return $_9owqcy1qje5o2q0e.isBr(pos.getNode());
      }).getOr(false);
    }
  };
  var isAnchorLink = function (elm) {
    return elm && elm.nodeName === 'A' && 'href' in elm;
  };
  var isInsideAnchor = function (location) {
    return location.fold($_enfhkv6je5o2ply.constant(false), isAnchorLink, isAnchorLink, $_enfhkv6je5o2ply.constant(false));
  };
  var readInlineAnchorLocation = function (editor) {
    var isInlineTarget = $_enfhkv6je5o2ply.curry($_6skw9732je5o2qbp.isInlineTarget, editor);
    var position = CaretPosition$1.fromRangeStart(editor.selection.getRng());
    return $_62vpc23ije5o2qem.readLocation(isInlineTarget, editor.getBody(), position).filter(isInsideAnchor);
  };
  var insertBrOutsideAnchor = function (editor, location) {
    location.fold($_enfhkv6je5o2ply.noop, $_enfhkv6je5o2ply.curry(insertBrBefore, editor), $_enfhkv6je5o2ply.curry(insertBrAfter, editor), $_enfhkv6je5o2ply.noop);
  };
  var insert = function (editor, evt) {
    var anchorLocation = readInlineAnchorLocation(editor);
    if (anchorLocation.isSome()) {
      anchorLocation.each($_enfhkv6je5o2ply.curry(insertBrOutsideAnchor, editor));
    } else {
      insertBrAtCaret(editor, evt);
    }
  };
  var $_1esduy3wje5o2qhl = { insert: insert };

  var adt = $_e73lvt3dje5o2qdq.generate([
    { 'before': ['element'] },
    {
      'on': [
        'element',
        'offset'
      ]
    },
    { after: ['element'] }
  ]);
  var cata = function (subject, onBefore, onOn, onAfter) {
    return subject.fold(onBefore, onOn, onAfter);
  };
  var getStart = function (situ) {
    return situ.fold($_enfhkv6je5o2ply.identity, $_enfhkv6je5o2ply.identity, $_enfhkv6je5o2ply.identity);
  };
  var $_78xwh641je5o2qmd = {
    before: adt.before,
    on: adt.on,
    after: adt.after,
    cata: cata,
    getStart: getStart
  };

  var type$1 = $_e73lvt3dje5o2qdq.generate([
    { domRange: ['rng'] },
    {
      relative: [
        'startSitu',
        'finishSitu'
      ]
    },
    {
      exact: [
        'start',
        'soffset',
        'finish',
        'foffset'
      ]
    }
  ]);
  var range$1 = $_86drv18je5o2pyn.immutable('start', 'soffset', 'finish', 'foffset');
  var exactFromRange = function (simRange) {
    return type$1.exact(simRange.start(), simRange.soffset(), simRange.finish(), simRange.foffset());
  };
  var getStart$1 = function (selection) {
    return selection.match({
      domRange: function (rng) {
        return $_5tcb2dyje5o2pwu.fromDom(rng.startContainer);
      },
      relative: function (startSitu, finishSitu) {
        return $_78xwh641je5o2qmd.getStart(startSitu);
      },
      exact: function (start, soffset, finish, foffset) {
        return start;
      }
    });
  };
  var getWin = function (selection) {
    var start = getStart$1(selection);
    return $_a6mttx17je5o2pyd.defaultView(start);
  };
  var $_8b9nu40je5o2qm8 = {
    domRange: type$1.domRange,
    relative: type$1.relative,
    exact: type$1.exact,
    exactFromRange: exactFromRange,
    range: range$1,
    getWin: getWin
  };

  var browser$3 = $_2hq237mje5o2pvu.detect().browser;
  var clamp = function (offset, element) {
    var max = $_95wn8qzje5o2px0.isText(element) ? $_3600ts2rje5o2qa7.get(element).length : $_a6mttx17je5o2pyd.children(element).length + 1;
    if (offset > max) {
      return max;
    } else if (offset < 0) {
      return 0;
    }
    return offset;
  };
  var normalizeRng = function (rng) {
    return $_8b9nu40je5o2qm8.range(rng.start(), clamp(rng.soffset(), rng.start()), rng.finish(), clamp(rng.foffset(), rng.finish()));
  };
  var isOrContains = function (root, elm) {
    return $_7oppmo1dje5o2pz1.contains(root, elm) || $_7oppmo1dje5o2pz1.eq(root, elm);
  };
  var isRngInRoot = function (root) {
    return function (rng) {
      return isOrContains(root, rng.start()) && isOrContains(root, rng.finish());
    };
  };
  var shouldStore = function (editor) {
    return editor.inline === true || browser$3.isIE();
  };
  var nativeRangeToSelectionRange = function (r) {
    return $_8b9nu40je5o2qm8.range($_5tcb2dyje5o2pwu.fromDom(r.startContainer), r.startOffset, $_5tcb2dyje5o2pwu.fromDom(r.endContainer), r.endOffset);
  };
  var readRange = function (win) {
    var selection = win.getSelection();
    var rng = !selection || selection.rangeCount === 0 ? Option.none() : Option.from(selection.getRangeAt(0));
    return rng.map(nativeRangeToSelectionRange);
  };
  var getBookmark$2 = function (root) {
    var win = $_a6mttx17je5o2pyd.defaultView(root);
    return readRange(win.dom()).filter(isRngInRoot(root));
  };
  var validate = function (root, bookmark) {
    return Option.from(bookmark).filter(isRngInRoot(root)).map(normalizeRng);
  };
  var bookmarkToNativeRng = function (bookmark) {
    var rng = document.createRange();
    rng.setStart(bookmark.start().dom(), bookmark.soffset());
    rng.setEnd(bookmark.finish().dom(), bookmark.foffset());
    return Option.some(rng);
  };
  var store = function (editor) {
    var newBookmark = shouldStore(editor) ? getBookmark$2($_5tcb2dyje5o2pwu.fromDom(editor.getBody())) : Option.none();
    editor.bookmark = newBookmark.isSome() ? newBookmark : editor.bookmark;
  };
  var storeNative = function (editor, rng) {
    var root = $_5tcb2dyje5o2pwu.fromDom(editor.getBody());
    var range = shouldStore(editor) ? Option.from(rng) : Option.none();
    var newBookmark = range.map(nativeRangeToSelectionRange).filter(isRngInRoot(root));
    editor.bookmark = newBookmark.isSome() ? newBookmark : editor.bookmark;
  };
  var getRng = function (editor) {
    var bookmark = editor.bookmark ? editor.bookmark : Option.none();
    return bookmark.bind($_enfhkv6je5o2ply.curry(validate, $_5tcb2dyje5o2pwu.fromDom(editor.getBody()))).bind(bookmarkToNativeRng);
  };
  var restore = function (editor) {
    getRng(editor).each(function (rng) {
      editor.selection.setRng(rng);
    });
  };
  var $_fnd7h33zje5o2qly = {
    store: store,
    storeNative: storeNative,
    readRange: readRange,
    restore: restore,
    getRng: getRng,
    getBookmark: getBookmark$2,
    validate: validate
  };

  var indentElement = function (dom, command, useMargin, value, unit, element) {
    if (dom.getContentEditable(element) === 'false') {
      return;
    }
    if (element.nodeName !== 'LI') {
      var indentStyleName = useMargin ? 'margin' : 'padding';
      indentStyleName = element.nodeName === 'TABLE' ? 'margin' : indentStyleName;
      indentStyleName += dom.getStyle(element, 'direction', true) === 'rtl' ? 'Right' : 'Left';
      if (command === 'outdent') {
        var styleValue = Math.max(0, parseInt(element.style[indentStyleName] || 0, 10) - value);
        dom.setStyle(element, indentStyleName, styleValue ? styleValue + unit : '');
      } else {
        var styleValue = parseInt(element.style[indentStyleName] || 0, 10) + value + unit;
        dom.setStyle(element, indentStyleName, styleValue);
      }
    }
  };
  var handle = function (editor, command) {
    var settings = editor.settings, dom = editor.dom, selection = editor.selection, formatter = editor.formatter;
    var indentUnit = /[a-z%]+$/i.exec(settings.indentation)[0];
    var indentValue = parseInt(settings.indentation, 10);
    var useMargin = editor.getParam('indent_use_margin', false);
    if (!editor.queryCommandState('InsertUnorderedList') && !editor.queryCommandState('InsertOrderedList')) {
      if (!settings.forced_root_block && !dom.getParent(selection.getNode(), dom.isBlock)) {
        formatter.apply('div');
      }
      $_bwcteb4je5o2pl4.each(selection.getSelectedBlocks(), function (element) {
        return indentElement(dom, command, useMargin, indentValue, indentUnit, element);
      });
    }
  };

  var each$9 = $_a5gegtjje5o2pum.each;
  var extend$2 = $_a5gegtjje5o2pum.extend;
  var map$2 = $_a5gegtjje5o2pum.map;
  var inArray$2 = $_a5gegtjje5o2pum.inArray;
  var explode$2 = $_a5gegtjje5o2pum.explode;
  var TRUE = true;
  var FALSE = false;
  function EditorCommands (editor) {
    var dom, selection, formatter;
    var commands = {
      state: {},
      exec: {},
      value: {}
    };
    var settings = editor.settings, bookmark;
    editor.on('PreInit', function () {
      dom = editor.dom;
      selection = editor.selection;
      settings = editor.settings;
      formatter = editor.formatter;
    });
    var execCommand = function (command, ui, value, args) {
      var func, customCommand, state = false;
      if (editor.removed) {
        return;
      }
      if (!/^(mceAddUndoLevel|mceEndUndoLevel|mceBeginUndoLevel|mceRepaint)$/.test(command) && (!args || !args.skip_focus)) {
        editor.focus();
      } else {
        $_fnd7h33zje5o2qly.restore(editor);
      }
      args = editor.fire('BeforeExecCommand', {
        command: command,
        ui: ui,
        value: value
      });
      if (args.isDefaultPrevented()) {
        return false;
      }
      customCommand = command.toLowerCase();
      if (func = commands.exec[customCommand]) {
        func(customCommand, ui, value);
        editor.fire('ExecCommand', {
          command: command,
          ui: ui,
          value: value
        });
        return true;
      }
      each$9(editor.plugins, function (p) {
        if (p.execCommand && p.execCommand(command, ui, value)) {
          editor.fire('ExecCommand', {
            command: command,
            ui: ui,
            value: value
          });
          state = true;
          return false;
        }
      });
      if (state) {
        return state;
      }
      if (editor.theme && editor.theme.execCommand && editor.theme.execCommand(command, ui, value)) {
        editor.fire('ExecCommand', {
          command: command,
          ui: ui,
          value: value
        });
        return true;
      }
      try {
        state = editor.getDoc().execCommand(command, ui, value);
      } catch (ex) {
      }
      if (state) {
        editor.fire('ExecCommand', {
          command: command,
          ui: ui,
          value: value
        });
        return true;
      }
      return false;
    };
    var queryCommandState = function (command) {
      var func;
      if (editor.quirks.isHidden() || editor.removed) {
        return;
      }
      command = command.toLowerCase();
      if (func = commands.state[command]) {
        return func(command);
      }
      try {
        return editor.getDoc().queryCommandState(command);
      } catch (ex) {
      }
      return false;
    };
    var queryCommandValue = function (command) {
      var func;
      if (editor.quirks.isHidden() || editor.removed) {
        return;
      }
      command = command.toLowerCase();
      if (func = commands.value[command]) {
        return func(command);
      }
      try {
        return editor.getDoc().queryCommandValue(command);
      } catch (ex) {
      }
    };
    var addCommands = function (commandList, type) {
      type = type || 'exec';
      each$9(commandList, function (callback, command) {
        each$9(command.toLowerCase().split(','), function (command) {
          commands[type][command] = callback;
        });
      });
    };
    var addCommand = function (command, callback, scope) {
      command = command.toLowerCase();
      commands.exec[command] = function (command, ui, value, args) {
        return callback.call(scope || editor, ui, value, args);
      };
    };
    var queryCommandSupported = function (command) {
      command = command.toLowerCase();
      if (commands.exec[command]) {
        return true;
      }
      try {
        return editor.getDoc().queryCommandSupported(command);
      } catch (ex) {
      }
      return false;
    };
    var addQueryStateHandler = function (command, callback, scope) {
      command = command.toLowerCase();
      commands.state[command] = function () {
        return callback.call(scope || editor);
      };
    };
    var addQueryValueHandler = function (command, callback, scope) {
      command = command.toLowerCase();
      commands.value[command] = function () {
        return callback.call(scope || editor);
      };
    };
    var hasCustomCommand = function (command) {
      command = command.toLowerCase();
      return !!commands.exec[command];
    };
    extend$2(this, {
      execCommand: execCommand,
      queryCommandState: queryCommandState,
      queryCommandValue: queryCommandValue,
      queryCommandSupported: queryCommandSupported,
      addCommands: addCommands,
      addCommand: addCommand,
      addQueryStateHandler: addQueryStateHandler,
      addQueryValueHandler: addQueryValueHandler,
      hasCustomCommand: hasCustomCommand
    });
    var execNativeCommand = function (command, ui, value) {
      if (ui === undefined) {
        ui = FALSE;
      }
      if (value === undefined) {
        value = null;
      }
      return editor.getDoc().execCommand(command, ui, value);
    };
    var isFormatMatch = function (name) {
      return formatter.match(name);
    };
    var toggleFormat = function (name, value) {
      formatter.toggle(name, value ? { value: value } : undefined);
      editor.nodeChanged();
    };
    var storeSelection = function (type) {
      bookmark = selection.getBookmark(type);
    };
    var restoreSelection = function () {
      selection.moveToBookmark(bookmark);
    };
    addCommands({
      'mceResetDesignMode,mceBeginUndoLevel': function () {
      },
      'mceEndUndoLevel,mceAddUndoLevel': function () {
        editor.undoManager.add();
      },
      'Cut,Copy,Paste': function (command) {
        var doc = editor.getDoc();
        var failed;
        try {
          execNativeCommand(command);
        } catch (ex) {
          failed = TRUE;
        }
        if (command === 'paste' && !doc.queryCommandEnabled(command)) {
          failed = true;
        }
        if (failed || !doc.queryCommandSupported(command)) {
          var msg = editor.translate('Your browser doesn\'t support direct access to the clipboard. ' + 'Please use the Ctrl+X/C/V keyboard shortcuts instead.');
          if ($_9bd8pn9je5o2pp2.mac) {
            msg = msg.replace(/Ctrl\+/g, '\u2318+');
          }
          editor.notificationManager.open({
            text: msg,
            type: 'error'
          });
        }
      },
      'unlink': function () {
        if (selection.isCollapsed()) {
          var elm = editor.dom.getParent(editor.selection.getStart(), 'a');
          if (elm) {
            editor.dom.remove(elm, true);
          }
          return;
        }
        formatter.remove('link');
      },
      'JustifyLeft,JustifyCenter,JustifyRight,JustifyFull,JustifyNone': function (command) {
        var align = command.substring(7);
        if (align === 'full') {
          align = 'justify';
        }
        each$9('left,center,right,justify'.split(','), function (name) {
          if (align !== name) {
            formatter.remove('align' + name);
          }
        });
        if (align !== 'none') {
          toggleFormat('align' + align);
        }
      },
      'InsertUnorderedList,InsertOrderedList': function (command) {
        var listElm, listParent;
        execNativeCommand(command);
        listElm = dom.getParent(selection.getNode(), 'ol,ul');
        if (listElm) {
          listParent = listElm.parentNode;
          if (/^(H[1-6]|P|ADDRESS|PRE)$/.test(listParent.nodeName)) {
            storeSelection();
            dom.split(listParent, listElm);
            restoreSelection();
          }
        }
      },
      'Bold,Italic,Underline,Strikethrough,Superscript,Subscript': function (command) {
        toggleFormat(command);
      },
      'ForeColor,HiliteColor,FontName': function (command, ui, value) {
        toggleFormat(command, value);
      },
      'FontSize': function (command, ui, value) {
        var fontClasses, fontSizes;
        if (value >= 1 && value <= 7) {
          fontSizes = explode$2(settings.font_size_style_values);
          fontClasses = explode$2(settings.font_size_classes);
          if (fontClasses) {
            value = fontClasses[value - 1] || value;
          } else {
            value = fontSizes[value - 1] || value;
          }
        }
        toggleFormat(command, value);
      },
      'RemoveFormat': function (command) {
        formatter.remove(command);
      },
      'mceBlockQuote': function () {
        toggleFormat('blockquote');
      },
      'FormatBlock': function (command, ui, value) {
        return toggleFormat(value || 'p');
      },
      'mceCleanup': function () {
        var bookmark = selection.getBookmark();
        editor.setContent(editor.getContent({ cleanup: TRUE }), { cleanup: TRUE });
        selection.moveToBookmark(bookmark);
      },
      'mceRemoveNode': function (command, ui, value) {
        var node = value || selection.getNode();
        if (node !== editor.getBody()) {
          storeSelection();
          editor.dom.remove(node, TRUE);
          restoreSelection();
        }
      },
      'mceSelectNodeDepth': function (command, ui, value) {
        var counter = 0;
        dom.getParent(selection.getNode(), function (node) {
          if (node.nodeType === 1 && counter++ === value) {
            selection.select(node);
            return FALSE;
          }
        }, editor.getBody());
      },
      'mceSelectNode': function (command, ui, value) {
        selection.select(value);
      },
      'mceInsertContent': function (command, ui, value) {
        $_d42uuu1wje5o2q4b.insertAtCaret(editor, value);
      },
      'mceInsertRawHTML': function (command, ui, value) {
        selection.setContent('tiny_mce_marker');
        editor.setContent(editor.getContent().replace(/tiny_mce_marker/g, function () {
          return value;
        }));
      },
      'mceToggleFormat': function (command, ui, value) {
        toggleFormat(value);
      },
      'mceSetContent': function (command, ui, value) {
        editor.setContent(value);
      },
      'Indent,Outdent': function (command) {
        handle(editor, command);
      },
      'mceRepaint': function () {
      },
      'InsertHorizontalRule': function () {
        editor.execCommand('mceInsertContent', false, '<hr />');
      },
      'mceToggleVisualAid': function () {
        editor.hasVisual = !editor.hasVisual;
        editor.addVisual();
      },
      'mceReplaceContent': function (command, ui, value) {
        editor.execCommand('mceInsertContent', false, value.replace(/\{\$selection\}/g, selection.getContent({ format: 'text' })));
      },
      'mceInsertLink': function (command, ui, value) {
        var anchor;
        if (typeof value === 'string') {
          value = { href: value };
        }
        anchor = dom.getParent(selection.getNode(), 'a');
        value.href = value.href.replace(' ', '%20');
        if (!anchor || !value.href) {
          formatter.remove('link');
        }
        if (value.href) {
          formatter.apply('link', value, anchor);
        }
      },
      'selectAll': function () {
        var editingHost = dom.getParent(selection.getStart(), $_9owqcy1qje5o2q0e.isContentEditableTrue);
        if (editingHost) {
          var rng = dom.createRng();
          rng.selectNodeContents(editingHost);
          selection.setRng(rng);
        }
      },
      'delete': function () {
        $_e390q12wje5o2qam.deleteCommand(editor);
      },
      'forwardDelete': function () {
        $_e390q12wje5o2qam.forwardDeleteCommand(editor);
      },
      'mceNewDocument': function () {
        editor.setContent('');
      },
      'InsertLineBreak': function (command, ui, value) {
        $_1esduy3wje5o2qhl.insert(editor, value);
        return true;
      }
    });
    addCommands({
      'JustifyLeft,JustifyCenter,JustifyRight,JustifyFull': function (command) {
        var name = 'align' + command.substring(7);
        var nodes = selection.isCollapsed() ? [dom.getParent(selection.getNode(), dom.isBlock)] : selection.getSelectedBlocks();
        var matches = map$2(nodes, function (node) {
          return !!formatter.matchNode(node, name);
        });
        return inArray$2(matches, TRUE) !== -1;
      },
      'Bold,Italic,Underline,Strikethrough,Superscript,Subscript': function (command) {
        return isFormatMatch(command);
      },
      'mceBlockQuote': function () {
        return isFormatMatch('blockquote');
      },
      'Outdent': function () {
        var node;
        if (settings.inline_styles) {
          if ((node = dom.getParent(selection.getStart(), dom.isBlock)) && parseInt(node.style.paddingLeft, 10) > 0) {
            return TRUE;
          }
          if ((node = dom.getParent(selection.getEnd(), dom.isBlock)) && parseInt(node.style.paddingLeft, 10) > 0) {
            return TRUE;
          }
        }
        return queryCommandState('InsertUnorderedList') || queryCommandState('InsertOrderedList') || !settings.inline_styles && !!dom.getParent(selection.getNode(), 'BLOCKQUOTE');
      },
      'InsertUnorderedList,InsertOrderedList': function (command) {
        var list = dom.getParent(selection.getNode(), 'ul,ol');
        return list && (command === 'insertunorderedlist' && list.tagName === 'UL' || command === 'insertorderedlist' && list.tagName === 'OL');
      }
    }, 'state');
    addCommands({
      'FontSize,FontName': function (command) {
        var value = 0, parent;
        if (parent = dom.getParent(selection.getNode(), 'span')) {
          if (command === 'fontsize') {
            value = parent.style.fontSize;
          } else {
            value = parent.style.fontFamily.replace(/, /g, ',').replace(/[\'\"]/g, '').toLowerCase();
          }
        }
        return value;
      }
    }, 'value');
    addCommands({
      Undo: function () {
        editor.undoManager.undo();
      },
      Redo: function () {
        editor.undoManager.redo();
      }
    });
  }

  var nativeEvents = $_a5gegtjje5o2pum.makeMap('focus blur focusin focusout click dblclick mousedown mouseup mousemove mouseover beforepaste paste cut copy selectionchange ' + 'mouseout mouseenter mouseleave wheel keydown keypress keyup input contextmenu dragstart dragend dragover ' + 'draggesture dragdrop drop drag submit ' + 'compositionstart compositionend compositionupdate touchstart touchmove touchend', ' ');
  var Dispatcher = function (settings) {
    var self = this;
    var scope, bindings = {}, toggleEvent;
    var returnFalse = function () {
      return false;
    };
    var returnTrue = function () {
      return true;
    };
    settings = settings || {};
    scope = settings.scope || self;
    toggleEvent = settings.toggleEvent || returnFalse;
    var fire = function (name, args) {
      var handlers, i, l, callback;
      name = name.toLowerCase();
      args = args || {};
      args.type = name;
      if (!args.target) {
        args.target = scope;
      }
      if (!args.preventDefault) {
        args.preventDefault = function () {
          args.isDefaultPrevented = returnTrue;
        };
        args.stopPropagation = function () {
          args.isPropagationStopped = returnTrue;
        };
        args.stopImmediatePropagation = function () {
          args.isImmediatePropagationStopped = returnTrue;
        };
        args.isDefaultPrevented = returnFalse;
        args.isPropagationStopped = returnFalse;
        args.isImmediatePropagationStopped = returnFalse;
      }
      if (settings.beforeFire) {
        settings.beforeFire(args);
      }
      handlers = bindings[name];
      if (handlers) {
        for (i = 0, l = handlers.length; i < l; i++) {
          callback = handlers[i];
          if (callback.once) {
            off(name, callback.func);
          }
          if (args.isImmediatePropagationStopped()) {
            args.stopPropagation();
            return args;
          }
          if (callback.func.call(scope, args) === false) {
            args.preventDefault();
            return args;
          }
        }
      }
      return args;
    };
    var on = function (name, callback, prepend, extra) {
      var handlers, names, i;
      if (callback === false) {
        callback = returnFalse;
      }
      if (callback) {
        callback = { func: callback };
        if (extra) {
          $_a5gegtjje5o2pum.extend(callback, extra);
        }
        names = name.toLowerCase().split(' ');
        i = names.length;
        while (i--) {
          name = names[i];
          handlers = bindings[name];
          if (!handlers) {
            handlers = bindings[name] = [];
            toggleEvent(name, true);
          }
          if (prepend) {
            handlers.unshift(callback);
          } else {
            handlers.push(callback);
          }
        }
      }
      return self;
    };
    var off = function (name, callback) {
      var i, handlers, bindingName, names, hi;
      if (name) {
        names = name.toLowerCase().split(' ');
        i = names.length;
        while (i--) {
          name = names[i];
          handlers = bindings[name];
          if (!name) {
            for (bindingName in bindings) {
              toggleEvent(bindingName, false);
              delete bindings[bindingName];
            }
            return self;
          }
          if (handlers) {
            if (!callback) {
              handlers.length = 0;
            } else {
              hi = handlers.length;
              while (hi--) {
                if (handlers[hi].func === callback) {
                  handlers = handlers.slice(0, hi).concat(handlers.slice(hi + 1));
                  bindings[name] = handlers;
                }
              }
            }
            if (!handlers.length) {
              toggleEvent(name, false);
              delete bindings[name];
            }
          }
        }
      } else {
        for (name in bindings) {
          toggleEvent(name, false);
        }
        bindings = {};
      }
      return self;
    };
    var once = function (name, callback, prepend) {
      return on(name, callback, prepend, { once: true });
    };
    var has = function (name) {
      name = name.toLowerCase();
      return !(!bindings[name] || bindings[name].length === 0);
    };
    self.fire = fire;
    self.on = on;
    self.off = off;
    self.once = once;
    self.has = has;
  };
  Dispatcher.isNative = function (name) {
    return !!nativeEvents[name.toLowerCase()];
  };

  var getEventDispatcher = function (obj) {
    if (!obj._eventDispatcher) {
      obj._eventDispatcher = new Dispatcher({
        scope: obj,
        toggleEvent: function (name, state) {
          if (Dispatcher.isNative(name) && obj.toggleNativeEvent) {
            obj.toggleNativeEvent(name, state);
          }
        }
      });
    }
    return obj._eventDispatcher;
  };
  var $_8mfz0u44je5o2qmp = {
    fire: function (name, args, bubble) {
      var self = this;
      if (self.removed && name !== 'remove') {
        return args;
      }
      args = getEventDispatcher(self).fire(name, args, bubble);
      if (bubble !== false && self.parent) {
        var parent_1 = self.parent();
        while (parent_1 && !args.isPropagationStopped()) {
          parent_1.fire(name, args, false);
          parent_1 = parent_1.parent();
        }
      }
      return args;
    },
    on: function (name, callback, prepend) {
      return getEventDispatcher(this).on(name, callback, prepend);
    },
    off: function (name, callback) {
      return getEventDispatcher(this).off(name, callback);
    },
    once: function (name, callback) {
      return getEventDispatcher(this).once(name, callback);
    },
    hasEventListeners: function (name) {
      return getEventDispatcher(this).has(name);
    }
  };

  var DOM$1 = DOMUtils.DOM;
  var customEventRootDelegates;
  var getEventTarget = function (editor, eventName) {
    if (eventName === 'selectionchange') {
      return editor.getDoc();
    }
    if (!editor.inline && /^mouse|touch|click|contextmenu|drop|dragover|dragend/.test(eventName)) {
      return editor.getDoc().documentElement;
    }
    if (editor.settings.event_root) {
      if (!editor.eventRoot) {
        editor.eventRoot = DOM$1.select(editor.settings.event_root)[0];
      }
      return editor.eventRoot;
    }
    return editor.getBody();
  };
  var bindEventDelegate = function (editor, eventName) {
    var eventRootElm, delegate;
    var isListening = function (editor) {
      return !editor.hidden && !editor.readonly;
    };
    if (!editor.delegates) {
      editor.delegates = {};
    }
    if (editor.delegates[eventName] || editor.removed) {
      return;
    }
    eventRootElm = getEventTarget(editor, eventName);
    if (editor.settings.event_root) {
      if (!customEventRootDelegates) {
        customEventRootDelegates = {};
        editor.editorManager.on('removeEditor', function () {
          var name;
          if (!editor.editorManager.activeEditor) {
            if (customEventRootDelegates) {
              for (name in customEventRootDelegates) {
                editor.dom.unbind(getEventTarget(editor, name));
              }
              customEventRootDelegates = null;
            }
          }
        });
      }
      if (customEventRootDelegates[eventName]) {
        return;
      }
      delegate = function (e) {
        var target = e.target;
        var editors = editor.editorManager.get();
        var i = editors.length;
        while (i--) {
          var body = editors[i].getBody();
          if (body === target || DOM$1.isChildOf(target, body)) {
            if (isListening(editors[i])) {
              editors[i].fire(eventName, e);
            }
          }
        }
      };
      customEventRootDelegates[eventName] = delegate;
      DOM$1.bind(eventRootElm, eventName, delegate);
    } else {
      delegate = function (e) {
        if (isListening(editor)) {
          editor.fire(eventName, e);
        }
      };
      DOM$1.bind(eventRootElm, eventName, delegate);
      editor.delegates[eventName] = delegate;
    }
  };
  var EditorObservable = {
    bindPendingEventDelegates: function () {
      var self = this;
      $_a5gegtjje5o2pum.each(self._pendingNativeEvents, function (name) {
        bindEventDelegate(self, name);
      });
    },
    toggleNativeEvent: function (name, state) {
      var self = this;
      if (name === 'focus' || name === 'blur') {
        return;
      }
      if (state) {
        if (self.initialized) {
          bindEventDelegate(self, name);
        } else {
          if (!self._pendingNativeEvents) {
            self._pendingNativeEvents = [name];
          } else {
            self._pendingNativeEvents.push(name);
          }
        }
      } else if (self.initialized) {
        self.dom.unbind(getEventTarget(self, name), name, self.delegates[name]);
        delete self.delegates[name];
      }
    },
    unbindAllNativeEvents: function () {
      var self = this;
      var body = self.getBody();
      var dom = self.dom;
      var name;
      if (self.delegates) {
        for (name in self.delegates) {
          self.dom.unbind(getEventTarget(self, name), name, self.delegates[name]);
        }
        delete self.delegates;
      }
      if (!self.inline && body && dom) {
        body.onload = null;
        dom.unbind(self.getWin());
        dom.unbind(self.getDoc());
      }
      if (dom) {
        dom.unbind(body);
        dom.unbind(self.getContainer());
      }
    }
  };
  EditorObservable = $_a5gegtjje5o2pum.extend({}, $_8mfz0u44je5o2qmp, EditorObservable);
  var EditorObservable$1 = EditorObservable;

  var setEditorCommandState = function (editor, cmd, state) {
    try {
      editor.getDoc().execCommand(cmd, false, state);
    } catch (ex) {
    }
  };
  var clickBlocker = function (editor) {
    var target, handler;
    target = editor.getBody();
    handler = function (e) {
      if (editor.dom.getParents(e.target, 'a').length > 0) {
        e.preventDefault();
      }
    };
    editor.dom.bind(target, 'click', handler);
    return {
      unbind: function () {
        editor.dom.unbind(target, 'click', handler);
      }
    };
  };
  var toggleReadOnly = function (editor, state) {
    if (editor._clickBlocker) {
      editor._clickBlocker.unbind();
      editor._clickBlocker = null;
    }
    if (state) {
      editor._clickBlocker = clickBlocker(editor);
      editor.selection.controlSelection.hideResizeRect();
      editor.readonly = true;
      editor.getBody().contentEditable = false;
    } else {
      editor.readonly = false;
      editor.getBody().contentEditable = true;
      setEditorCommandState(editor, 'StyleWithCSS', false);
      setEditorCommandState(editor, 'enableInlineTableEditing', false);
      setEditorCommandState(editor, 'enableObjectResizing', false);
      editor.focus();
      editor.nodeChanged();
    }
  };
  var setMode = function (editor, mode) {
    var currentMode = editor.readonly ? 'readonly' : 'design';
    if (mode === currentMode) {
      return;
    }
    if (editor.initialized) {
      toggleReadOnly(editor, mode === 'readonly');
    } else {
      editor.on('init', function () {
        toggleReadOnly(editor, mode === 'readonly');
      });
    }
    editor.fire('SwitchMode', { mode: mode });
  };
  var $_7uzwj846je5o2qmw = { setMode: setMode };

  var each$10 = $_a5gegtjje5o2pum.each;
  var explode$3 = $_a5gegtjje5o2pum.explode;
  var keyCodeLookup = {
    f9: 120,
    f10: 121,
    f11: 122
  };
  var modifierNames = $_a5gegtjje5o2pum.makeMap('alt,ctrl,shift,meta,access');
  function Shortcuts (editor) {
    var self = this;
    var shortcuts = {};
    var pendingPatterns = [];
    var parseShortcut = function (pattern) {
      var id, key;
      var shortcut = {};
      each$10(explode$3(pattern, '+'), function (value) {
        if (value in modifierNames) {
          shortcut[value] = true;
        } else {
          if (/^[0-9]{2,}$/.test(value)) {
            shortcut.keyCode = parseInt(value, 10);
          } else {
            shortcut.charCode = value.charCodeAt(0);
            shortcut.keyCode = keyCodeLookup[value] || value.toUpperCase().charCodeAt(0);
          }
        }
      });
      id = [shortcut.keyCode];
      for (key in modifierNames) {
        if (shortcut[key]) {
          id.push(key);
        } else {
          shortcut[key] = false;
        }
      }
      shortcut.id = id.join(',');
      if (shortcut.access) {
        shortcut.alt = true;
        if ($_9bd8pn9je5o2pp2.mac) {
          shortcut.ctrl = true;
        } else {
          shortcut.shift = true;
        }
      }
      if (shortcut.meta) {
        if ($_9bd8pn9je5o2pp2.mac) {
          shortcut.meta = true;
        } else {
          shortcut.ctrl = true;
          shortcut.meta = false;
        }
      }
      return shortcut;
    };
    var createShortcut = function (pattern, desc, cmdFunc, scope) {
      var shortcuts;
      shortcuts = $_a5gegtjje5o2pum.map(explode$3(pattern, '>'), parseShortcut);
      shortcuts[shortcuts.length - 1] = $_a5gegtjje5o2pum.extend(shortcuts[shortcuts.length - 1], {
        func: cmdFunc,
        scope: scope || editor
      });
      return $_a5gegtjje5o2pum.extend(shortcuts[0], {
        desc: editor.translate(desc),
        subpatterns: shortcuts.slice(1)
      });
    };
    var hasModifier = function (e) {
      return e.altKey || e.ctrlKey || e.metaKey;
    };
    var isFunctionKey = function (e) {
      return e.type === 'keydown' && e.keyCode >= 112 && e.keyCode <= 123;
    };
    var matchShortcut = function (e, shortcut) {
      if (!shortcut) {
        return false;
      }
      if (shortcut.ctrl !== e.ctrlKey || shortcut.meta !== e.metaKey) {
        return false;
      }
      if (shortcut.alt !== e.altKey || shortcut.shift !== e.shiftKey) {
        return false;
      }
      if (e.keyCode === shortcut.keyCode || e.charCode && e.charCode === shortcut.charCode) {
        e.preventDefault();
        return true;
      }
      return false;
    };
    var executeShortcutAction = function (shortcut) {
      return shortcut.func ? shortcut.func.call(shortcut.scope) : null;
    };
    editor.on('keyup keypress keydown', function (e) {
      if ((hasModifier(e) || isFunctionKey(e)) && !e.isDefaultPrevented()) {
        each$10(shortcuts, function (shortcut) {
          if (matchShortcut(e, shortcut)) {
            pendingPatterns = shortcut.subpatterns.slice(0);
            if (e.type === 'keydown') {
              executeShortcutAction(shortcut);
            }
            return true;
          }
        });
        if (matchShortcut(e, pendingPatterns[0])) {
          if (pendingPatterns.length === 1) {
            if (e.type === 'keydown') {
              executeShortcutAction(pendingPatterns[0]);
            }
          }
          pendingPatterns.shift();
        }
      }
    });
    self.add = function (pattern, desc, cmdFunc, scope) {
      var cmd;
      cmd = cmdFunc;
      if (typeof cmdFunc === 'string') {
        cmdFunc = function () {
          editor.execCommand(cmd, false, null);
        };
      } else if ($_a5gegtjje5o2pum.isArray(cmd)) {
        cmdFunc = function () {
          editor.execCommand(cmd[0], cmd[1], cmd[2]);
        };
      }
      each$10(explode$3($_a5gegtjje5o2pum.trim(pattern.toLowerCase())), function (pattern) {
        var shortcut = createShortcut(pattern, desc, cmdFunc, scope);
        shortcuts[shortcut.id] = shortcut;
      });
      return true;
    };
    self.remove = function (pattern) {
      var shortcut = createShortcut(pattern);
      if (shortcuts[shortcut.id]) {
        delete shortcuts[shortcut.id];
        return true;
      }
      return false;
    };
  }

  var any$1 = function (predicate) {
    return $_au2qfe30je5o2qb4.first(predicate).isSome();
  };
  var ancestor$3 = function (scope, predicate, isRoot) {
    return $_au2qfe30je5o2qb4.ancestor(scope, predicate, isRoot).isSome();
  };
  var closest$3 = function (scope, predicate, isRoot) {
    return $_au2qfe30je5o2qb4.closest(scope, predicate, isRoot).isSome();
  };
  var sibling$4 = function (scope, predicate) {
    return $_au2qfe30je5o2qb4.sibling(scope, predicate).isSome();
  };
  var child$4 = function (scope, predicate) {
    return $_au2qfe30je5o2qb4.child(scope, predicate).isSome();
  };
  var descendant$3 = function (scope, predicate) {
    return $_au2qfe30je5o2qb4.descendant(scope, predicate).isSome();
  };
  var $_byozzr4aje5o2qnk = {
    any: any$1,
    ancestor: ancestor$3,
    closest: closest$3,
    sibling: sibling$4,
    child: child$4,
    descendant: descendant$3
  };

  var focus = function (element) {
    element.dom().focus();
  };
  var blur = function (element) {
    element.dom().blur();
  };
  var hasFocus = function (element) {
    var doc = $_a6mttx17je5o2pyd.owner(element).dom();
    return element.dom() === doc.activeElement;
  };
  var active = function (_doc) {
    var doc = _doc !== undefined ? _doc.dom() : document;
    return Option.from(doc.activeElement).map($_5tcb2dyje5o2pwu.fromDom);
  };
  var focusInside = function (element) {
    var doc = $_a6mttx17je5o2pyd.owner(element);
    var inside = active(doc).filter(function (a) {
      return $_byozzr4aje5o2qnk.closest(a, $_enfhkv6je5o2ply.curry($_7oppmo1dje5o2pz1.eq, element));
    });
    inside.fold(function () {
      focus(element);
    }, $_enfhkv6je5o2ply.noop);
  };
  var search = function (element) {
    return active($_a6mttx17je5o2pyd.owner(element)).filter(function (e) {
      return element.dom().contains(e.dom());
    });
  };
  var $_7ndv7y49je5o2qng = {
    hasFocus: hasFocus,
    focus: focus,
    blur: blur,
    active: active,
    search: search,
    focusInside: focusInside
  };

  var getContentEditableHost = function (editor, node) {
    return editor.dom.getParent(node, function (node) {
      return editor.dom.getContentEditable(node) === 'true';
    });
  };
  var getCollapsedNode = function (rng) {
    return rng.collapsed ? Option.from(getNode(rng.startContainer, rng.startOffset)).map($_5tcb2dyje5o2pwu.fromDom) : Option.none();
  };
  var getFocusInElement = function (root, rng) {
    return getCollapsedNode(rng).bind(function (node) {
      if (isTableSection(node)) {
        return Option.some(node);
      } else if ($_7oppmo1dje5o2pz1.contains(root, node) === false) {
        return Option.some(root);
      } else {
        return Option.none();
      }
    });
  };
  var normalizeSelection = function (editor, rng) {
    getFocusInElement($_5tcb2dyje5o2pwu.fromDom(editor.getBody()), rng).bind(function (elm) {
      return $_g2kzjo2bje5o2q7n.firstPositionIn(elm.dom());
    }).fold(function () {
      return editor.selection.normalize();
    }, function (caretPos) {
      return editor.selection.setRng(caretPos.toRange());
    });
  };
  var focusBody = function (body) {
    if (body.setActive) {
      try {
        body.setActive();
      } catch (ex) {
        body.focus();
      }
    } else {
      body.focus();
    }
  };
  var hasElementFocus = function (elm) {
    return $_7ndv7y49je5o2qng.hasFocus(elm) || $_7ndv7y49je5o2qng.search(elm).isSome();
  };
  var hasIframeFocus = function (editor) {
    return editor.iframeElement && $_7ndv7y49je5o2qng.hasFocus($_5tcb2dyje5o2pwu.fromDom(editor.iframeElement));
  };
  var hasInlineFocus = function (editor) {
    var rawBody = editor.getBody();
    return rawBody && hasElementFocus($_5tcb2dyje5o2pwu.fromDom(rawBody));
  };
  var hasFocus$1 = function (editor) {
    return editor.inline ? hasInlineFocus(editor) : hasIframeFocus(editor);
  };
  var focusEditor = function (editor) {
    var selection = editor.selection, contentEditable = editor.settings.content_editable;
    var body = editor.getBody();
    var contentEditableHost, rng = selection.getRng();
    editor.quirks.refreshContentEditable();
    contentEditableHost = getContentEditableHost(editor, selection.getNode());
    if (editor.$.contains(body, contentEditableHost)) {
      focusBody(contentEditableHost);
      normalizeSelection(editor, rng);
      activateEditor(editor);
      return;
    }
    if (editor.bookmark !== undefined && hasFocus$1(editor) === false) {
      $_fnd7h33zje5o2qly.getRng(editor).each(function (bookmarkRng) {
        editor.selection.setRng(bookmarkRng);
        rng = bookmarkRng;
      });
    }
    if (!contentEditable) {
      if (!$_9bd8pn9je5o2pp2.opera) {
        focusBody(body);
      }
      editor.getWin().focus();
    }
    if ($_9bd8pn9je5o2pp2.gecko || contentEditable) {
      focusBody(body);
      normalizeSelection(editor, rng);
    }
    activateEditor(editor);
  };
  var activateEditor = function (editor) {
    editor.editorManager.setActive(editor);
  };
  var focus$1 = function (editor, skipFocus) {
    if (editor.removed) {
      return;
    }
    skipFocus ? activateEditor(editor) : focusEditor(editor);
  };
  var $_llx5i48je5o2qna = {
    focus: focus$1,
    hasFocus: hasFocus$1
  };

  var getProp = function (propName, elm) {
    var rawElm = elm.dom();
    return rawElm[propName];
  };
  var getComputedSizeProp = function (propName, elm) {
    return parseInt($_1ioope11je5o2px4.get(elm, propName), 10);
  };
  var getClientWidth = $_enfhkv6je5o2ply.curry(getProp, 'clientWidth');
  var getClientHeight = $_enfhkv6je5o2ply.curry(getProp, 'clientHeight');
  var getMarginTop = $_enfhkv6je5o2ply.curry(getComputedSizeProp, 'margin-top');
  var getMarginLeft = $_enfhkv6je5o2ply.curry(getComputedSizeProp, 'margin-left');
  var getBoundingClientRect$1 = function (elm) {
    return elm.dom().getBoundingClientRect();
  };
  var isInsideElementContentArea = function (bodyElm, clientX, clientY) {
    var clientWidth = getClientWidth(bodyElm);
    var clientHeight = getClientHeight(bodyElm);
    return clientX >= 0 && clientY >= 0 && clientX <= clientWidth && clientY <= clientHeight;
  };
  var transpose = function (inline, elm, clientX, clientY) {
    var clientRect = getBoundingClientRect$1(elm);
    var deltaX = inline ? clientRect.left + elm.dom().clientLeft + getMarginLeft(elm) : 0;
    var deltaY = inline ? clientRect.top + elm.dom().clientTop + getMarginTop(elm) : 0;
    var x = clientX - deltaX;
    var y = clientY - deltaY;
    return {
      x: x,
      y: y
    };
  };
  var isXYInContentArea = function (editor, clientX, clientY) {
    var bodyElm = $_5tcb2dyje5o2pwu.fromDom(editor.getBody());
    var targetElm = editor.inline ? bodyElm : $_a6mttx17je5o2pyd.documentElement(bodyElm);
    var transposedPoint = transpose(editor.inline, targetElm, clientX, clientY);
    return isInsideElementContentArea(targetElm, transposedPoint.x, transposedPoint.y);
  };
  var fromDomSafe = function (node) {
    return Option.from(node).map($_5tcb2dyje5o2pwu.fromDom);
  };
  var isEditorAttachedToDom = function (editor) {
    var rawContainer = editor.inline ? editor.getBody() : editor.getContentAreaContainer();
    return fromDomSafe(rawContainer).map(function (container) {
      return $_7oppmo1dje5o2pz1.contains($_a6mttx17je5o2pyd.owner(container), container);
    }).getOr(false);
  };
  var $_3f7nxx4dje5o2qoa = {
    isXYInContentArea: isXYInContentArea,
    isEditorAttachedToDom: isEditorAttachedToDom
  };

  function NotificationManagerImpl () {
    var unimplemented = function () {
      throw new Error('Theme did not provide a NotificationManager implementation.');
    };
    return {
      open: unimplemented,
      close: unimplemented,
      reposition: unimplemented,
      getArgs: unimplemented
    };
  }

  function NotificationManager (editor) {
    var notifications = [];
    var getImplementation = function () {
      var theme = editor.theme;
      return theme && theme.getNotificationManagerImpl ? theme.getNotificationManagerImpl() : NotificationManagerImpl();
    };
    var getTopNotification = function () {
      return Option.from(notifications[0]);
    };
    var isEqual = function (a, b) {
      return a.type === b.type && a.text === b.text && !a.progressBar && !a.timeout && !b.progressBar && !b.timeout;
    };
    var reposition = function () {
      if (notifications.length > 0) {
        getImplementation().reposition(notifications);
      }
    };
    var addNotification = function (notification) {
      notifications.push(notification);
    };
    var closeNotification = function (notification) {
      $_bwcteb4je5o2pl4.findIndex(notifications, function (otherNotification) {
        return otherNotification === notification;
      }).each(function (index) {
        notifications.splice(index, 1);
      });
    };
    var open = function (args) {
      if (editor.removed || !$_3f7nxx4dje5o2qoa.isEditorAttachedToDom(editor)) {
        return;
      }
      return $_bwcteb4je5o2pl4.find(notifications, function (notification) {
        return isEqual(getImplementation().getArgs(notification), args);
      }).getOrThunk(function () {
        editor.editorManager.setActive(editor);
        var notification = getImplementation().open(args, function () {
          closeNotification(notification);
          reposition();
        });
        addNotification(notification);
        reposition();
        return notification;
      });
    };
    var close = function () {
      getTopNotification().each(function (notification) {
        getImplementation().close(notification);
        closeNotification(notification);
        reposition();
      });
    };
    var getNotifications = function () {
      return notifications;
    };
    var registerEvents = function (editor) {
      editor.on('SkinLoaded', function () {
        var serviceMessage = editor.settings.service_message;
        if (serviceMessage) {
          open({
            text: serviceMessage,
            type: 'warning',
            timeout: 0,
            icon: ''
          });
        }
      });
      editor.on('ResizeEditor ResizeWindow', function () {
        $_db41wogje5o2ps3.requestAnimationFrame(reposition);
      });
      editor.on('remove', function () {
        $_bwcteb4je5o2pl4.each(notifications, function (notification) {
          getImplementation().close(notification);
        });
      });
    };
    registerEvents(editor);
    return {
      open: open,
      close: close,
      getNotifications: getNotifications
    };
  }

  function WindowManagerImpl () {
    var unimplemented = function () {
      throw new Error('Theme did not provide a WindowManager implementation.');
    };
    return {
      open: unimplemented,
      alert: unimplemented,
      confirm: unimplemented,
      close: unimplemented,
      getParams: unimplemented,
      setParams: unimplemented
    };
  }

  function WindowManager (editor) {
    var windows = [];
    var getImplementation = function () {
      var theme = editor.theme;
      return theme && theme.getWindowManagerImpl ? theme.getWindowManagerImpl() : WindowManagerImpl();
    };
    var funcBind = function (scope, f) {
      return function () {
        return f ? f.apply(scope, arguments) : undefined;
      };
    };
    var fireOpenEvent = function (win) {
      editor.fire('OpenWindow', { win: win });
    };
    var fireCloseEvent = function (win) {
      editor.fire('CloseWindow', { win: win });
    };
    var addWindow = function (win) {
      windows.push(win);
      fireOpenEvent(win);
    };
    var closeWindow = function (win) {
      $_bwcteb4je5o2pl4.findIndex(windows, function (otherWindow) {
        return otherWindow === win;
      }).each(function (index) {
        windows.splice(index, 1);
        fireCloseEvent(win);
        if (windows.length === 0) {
          editor.focus();
        }
      });
    };
    var getTopWindow = function () {
      return Option.from(windows[windows.length - 1]);
    };
    var open = function (args, params) {
      editor.editorManager.setActive(editor);
      $_fnd7h33zje5o2qly.store(editor);
      var win = getImplementation().open(args, params, closeWindow);
      addWindow(win);
      return win;
    };
    var alert = function (message, callback, scope) {
      var win = getImplementation().alert(message, funcBind(scope ? scope : this, callback), closeWindow);
      addWindow(win);
    };
    var confirm = function (message, callback, scope) {
      var win = getImplementation().confirm(message, funcBind(scope ? scope : this, callback), closeWindow);
      addWindow(win);
    };
    var close = function () {
      getTopWindow().each(function (win) {
        getImplementation().close(win);
        closeWindow(win);
      });
    };
    var getParams = function () {
      return getTopWindow().map(getImplementation().getParams).getOr(null);
    };
    var setParams = function (params) {
      getTopWindow().each(function (win) {
        getImplementation().setParams(win, params);
      });
    };
    var getWindows = function () {
      return windows;
    };
    editor.on('remove', function () {
      $_bwcteb4je5o2pl4.each(windows.slice(0), function (win) {
        getImplementation().close(win);
      });
    });
    return {
      windows: windows,
      open: open,
      alert: alert,
      confirm: confirm,
      close: close,
      getParams: getParams,
      setParams: setParams,
      getWindows: getWindows
    };
  }

  var PluginManager = AddOnManager.PluginManager;
  var resolvePluginName = function (targetUrl, suffix) {
    for (var name_1 in PluginManager.urls) {
      var matchUrl = PluginManager.urls[name_1] + '/plugin' + suffix + '.js';
      if (matchUrl === targetUrl) {
        return name_1;
      }
    }
    return null;
  };
  var pluginUrlToMessage = function (editor, url) {
    var plugin = resolvePluginName(url, editor.suffix);
    return plugin ? 'Failed to load plugin: ' + plugin + ' from url ' + url : 'Failed to load plugin url: ' + url;
  };
  var displayNotification = function (editor, message) {
    editor.notificationManager.open({
      type: 'error',
      text: message
    });
  };
  var displayError = function (editor, message) {
    if (editor._skinLoaded) {
      displayNotification(editor, message);
    } else {
      editor.on('SkinLoaded', function () {
        displayNotification(editor, message);
      });
    }
  };
  var uploadError = function (editor, message) {
    displayError(editor, 'Failed to upload image: ' + message);
  };
  var pluginLoadError = function (editor, url) {
    displayError(editor, pluginUrlToMessage(editor, url));
  };
  var initError = function (message) {
    var x = [];
    for (var _i = 1; _i < arguments.length; _i++) {
      x[_i - 1] = arguments[_i];
    }
    var console = window.console;
    if (console) {
      if (console.error) {
        console.error.apply(console, arguments);
      } else {
        console.log.apply(console, arguments);
      }
    }
  };
  var $_axrd1j4hje5o2qor = {
    pluginLoadError: pluginLoadError,
    uploadError: uploadError,
    displayError: displayError,
    initError: initError
  };

  var PluginManager$1 = AddOnManager.PluginManager;

  var ThemeManager = AddOnManager.ThemeManager;

  function XMLHttpRequest () {
    var f = $_lmz5vbje5o2pp9.getOrDie('XMLHttpRequest');
    return new f();
  }

  function Uploader (uploadStatus, settings) {
    var pendingPromises = {};
    var pathJoin = function (path1, path2) {
      if (path1) {
        return path1.replace(/\/$/, '') + '/' + path2.replace(/^\//, '');
      }
      return path2;
    };
    var defaultHandler = function (blobInfo, success, failure, progress) {
      var xhr, formData;
      xhr = new XMLHttpRequest();
      xhr.open('POST', settings.url);
      xhr.withCredentials = settings.credentials;
      xhr.upload.onprogress = function (e) {
        progress(e.loaded / e.total * 100);
      };
      xhr.onerror = function () {
        failure('Image upload failed due to a XHR Transport error. Code: ' + xhr.status);
      };
      xhr.onload = function () {
        var json;
        if (xhr.status < 200 || xhr.status >= 300) {
          failure('HTTP Error: ' + xhr.status);
          return;
        }
        json = JSON.parse(xhr.responseText);
        if (!json || typeof json.location !== 'string') {
          failure('Invalid JSON: ' + xhr.responseText);
          return;
        }
        success(pathJoin(settings.basePath, json.location));
      };
      formData = new FormData();
      formData.append('file', blobInfo.blob(), blobInfo.filename());
      xhr.send(formData);
    };
    var noUpload = function () {
      return new promiseObj(function (resolve) {
        resolve([]);
      });
    };
    var handlerSuccess = function (blobInfo, url) {
      return {
        url: url,
        blobInfo: blobInfo,
        status: true
      };
    };
    var handlerFailure = function (blobInfo, error) {
      return {
        url: '',
        blobInfo: blobInfo,
        status: false,
        error: error
      };
    };
    var resolvePending = function (blobUri, result) {
      $_a5gegtjje5o2pum.each(pendingPromises[blobUri], function (resolve) {
        resolve(result);
      });
      delete pendingPromises[blobUri];
    };
    var uploadBlobInfo = function (blobInfo, handler, openNotification) {
      uploadStatus.markPending(blobInfo.blobUri());
      return new promiseObj(function (resolve) {
        var notification, progress;
        var noop = function () {
        };
        try {
          var closeNotification_1 = function () {
            if (notification) {
              notification.close();
              progress = noop;
            }
          };
          var success = function (url) {
            closeNotification_1();
            uploadStatus.markUploaded(blobInfo.blobUri(), url);
            resolvePending(blobInfo.blobUri(), handlerSuccess(blobInfo, url));
            resolve(handlerSuccess(blobInfo, url));
          };
          var failure = function (error) {
            closeNotification_1();
            uploadStatus.removeFailed(blobInfo.blobUri());
            resolvePending(blobInfo.blobUri(), handlerFailure(blobInfo, error));
            resolve(handlerFailure(blobInfo, error));
          };
          progress = function (percent) {
            if (percent < 0 || percent > 100) {
              return;
            }
            if (!notification) {
              notification = openNotification();
            }
            notification.progressBar.value(percent);
          };
          handler(blobInfo, success, failure, progress);
        } catch (ex) {
          resolve(handlerFailure(blobInfo, ex.message));
        }
      });
    };
    var isDefaultHandler = function (handler) {
      return handler === defaultHandler;
    };
    var pendingUploadBlobInfo = function (blobInfo) {
      var blobUri = blobInfo.blobUri();
      return new promiseObj(function (resolve) {
        pendingPromises[blobUri] = pendingPromises[blobUri] || [];
        pendingPromises[blobUri].push(resolve);
      });
    };
    var uploadBlobs = function (blobInfos, openNotification) {
      blobInfos = $_a5gegtjje5o2pum.grep(blobInfos, function (blobInfo) {
        return !uploadStatus.isUploaded(blobInfo.blobUri());
      });
      return promiseObj.all($_a5gegtjje5o2pum.map(blobInfos, function (blobInfo) {
        return uploadStatus.isPending(blobInfo.blobUri()) ? pendingUploadBlobInfo(blobInfo) : uploadBlobInfo(blobInfo, settings.handler, openNotification);
      }));
    };
    var upload = function (blobInfos, openNotification) {
      return !settings.url && isDefaultHandler(settings.handler) ? noUpload() : uploadBlobs(blobInfos, openNotification);
    };
    settings = $_a5gegtjje5o2pum.extend({
      credentials: false,
      handler: defaultHandler
    }, settings);
    return { upload: upload };
  }

  function Blob (parts, properties) {
    var f = $_lmz5vbje5o2pp9.getOrDie('Blob');
    return new f(parts, properties);
  }

  function FileReader () {
    var f = $_lmz5vbje5o2pp9.getOrDie('FileReader');
    return new f();
  }

  function Uint8Array (arr) {
    var f = $_lmz5vbje5o2pp9.getOrDie('Uint8Array');
    return new f(arr);
  }

  var requestAnimationFrame$1 = function (callback) {
    var f = $_lmz5vbje5o2pp9.getOrDie('requestAnimationFrame');
    f(callback);
  };
  var atob = function (base64) {
    var f = $_lmz5vbje5o2pp9.getOrDie('atob');
    return f(base64);
  };
  var $_9okclz4uje5o2qq7 = {
    atob: atob,
    requestAnimationFrame: requestAnimationFrame$1
  };

  var blobUriToBlob = function (url) {
    return new promiseObj(function (resolve, reject) {
      var rejectWithError = function () {
        reject('Cannot convert ' + url + ' to Blob. Resource might not exist or is inaccessible.');
      };
      try {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', url, true);
        xhr.responseType = 'blob';
        xhr.onload = function () {
          if (this.status === 200) {
            resolve(this.response);
          } else {
            rejectWithError();
          }
        };
        xhr.onerror = rejectWithError;
        xhr.send();
      } catch (ex) {
        rejectWithError();
      }
    });
  };
  var parseDataUri = function (uri) {
    var type, matches;
    uri = decodeURIComponent(uri).split(',');
    matches = /data:([^;]+)/.exec(uri[0]);
    if (matches) {
      type = matches[1];
    }
    return {
      type: type,
      data: uri[1]
    };
  };
  var dataUriToBlob = function (uri) {
    return new promiseObj(function (resolve) {
      var str, arr, i;
      uri = parseDataUri(uri);
      try {
        str = $_9okclz4uje5o2qq7.atob(uri.data);
      } catch (e) {
        resolve(new Blob([]));
        return;
      }
      arr = new Uint8Array(str.length);
      for (i = 0; i < arr.length; i++) {
        arr[i] = str.charCodeAt(i);
      }
      resolve(new Blob([arr], { type: uri.type }));
    });
  };
  var uriToBlob = function (url) {
    if (url.indexOf('blob:') === 0) {
      return blobUriToBlob(url);
    }
    if (url.indexOf('data:') === 0) {
      return dataUriToBlob(url);
    }
    return null;
  };
  var blobToDataUri = function (blob) {
    return new promiseObj(function (resolve) {
      var reader = new FileReader();
      reader.onloadend = function () {
        resolve(reader.result);
      };
      reader.readAsDataURL(blob);
    });
  };
  var $_8a34kg4qje5o2qpz = {
    uriToBlob: uriToBlob,
    blobToDataUri: blobToDataUri,
    parseDataUri: parseDataUri
  };

  var count = 0;
  var uniqueId = function (prefix) {
    return (prefix || 'blobid') + count++;
  };
  var imageToBlobInfo = function (blobCache, img, resolve, reject) {
    var base64, blobInfo;
    if (img.src.indexOf('blob:') === 0) {
      blobInfo = blobCache.getByUri(img.src);
      if (blobInfo) {
        resolve({
          image: img,
          blobInfo: blobInfo
        });
      } else {
        $_8a34kg4qje5o2qpz.uriToBlob(img.src).then(function (blob) {
          $_8a34kg4qje5o2qpz.blobToDataUri(blob).then(function (dataUri) {
            base64 = $_8a34kg4qje5o2qpz.parseDataUri(dataUri).data;
            blobInfo = blobCache.create(uniqueId(), blob, base64);
            blobCache.add(blobInfo);
            resolve({
              image: img,
              blobInfo: blobInfo
            });
          });
        }, function (err) {
          reject(err);
        });
      }
      return;
    }
    base64 = $_8a34kg4qje5o2qpz.parseDataUri(img.src).data;
    blobInfo = blobCache.findFirst(function (cachedBlobInfo) {
      return cachedBlobInfo.base64() === base64;
    });
    if (blobInfo) {
      resolve({
        image: img,
        blobInfo: blobInfo
      });
    } else {
      $_8a34kg4qje5o2qpz.uriToBlob(img.src).then(function (blob) {
        blobInfo = blobCache.create(uniqueId(), blob, base64);
        blobCache.add(blobInfo);
        resolve({
          image: img,
          blobInfo: blobInfo
        });
      }, function (err) {
        reject(err);
      });
    }
  };
  var getAllImages = function (elm) {
    return elm ? elm.getElementsByTagName('img') : [];
  };
  function ImageScanner (uploadStatus, blobCache) {
    var cachedPromises = {};
    var findAll = function (elm, predicate) {
      var images;
      if (!predicate) {
        predicate = $_ghq3nq25je5o2q5q.constant(true);
      }
      images = $_c2vc3pkje5o2puw.filter(getAllImages(elm), function (img) {
        var src = img.src;
        if (!$_9bd8pn9je5o2pp2.fileApi) {
          return false;
        }
        if (img.hasAttribute('data-mce-bogus')) {
          return false;
        }
        if (img.hasAttribute('data-mce-placeholder')) {
          return false;
        }
        if (!src || src === $_9bd8pn9je5o2pp2.transparentSrc) {
          return false;
        }
        if (src.indexOf('blob:') === 0) {
          return !uploadStatus.isUploaded(src);
        }
        if (src.indexOf('data:') === 0) {
          return predicate(img);
        }
        return false;
      });
      var promises = $_c2vc3pkje5o2puw.map(images, function (img) {
        if (cachedPromises[img.src]) {
          return new promiseObj(function (resolve) {
            cachedPromises[img.src].then(function (imageInfo) {
              if (typeof imageInfo === 'string') {
                return imageInfo;
              }
              resolve({
                image: img,
                blobInfo: imageInfo.blobInfo
              });
            });
          });
        }
        var newPromise = new promiseObj(function (resolve, reject) {
          imageToBlobInfo(blobCache, img, resolve, reject);
        }).then(function (result) {
          delete cachedPromises[result.image.src];
          return result;
        }).catch(function (error) {
          delete cachedPromises[img.src];
          return error;
        });
        cachedPromises[img.src] = newPromise;
        return newPromise;
      });
      return promiseObj.all(promises);
    };
    return { findAll: findAll };
  }

  var count$1 = 0;
  var seed = function () {
    var rnd = function () {
      return Math.round(Math.random() * 4294967295).toString(36);
    };
    var now = new Date().getTime();
    return 's' + now.toString(36) + rnd() + rnd() + rnd();
  };
  var uuid = function (prefix) {
    return prefix + count$1++ + seed();
  };
  var $_2bgddd4wje5o2qqo = { uuid: uuid };

  function BlobCache () {
    var cache = [];
    var constant = $_ghq3nq25je5o2q5q.constant;
    var mimeToExt = function (mime) {
      var mimes = {
        'image/jpeg': 'jpg',
        'image/jpg': 'jpg',
        'image/gif': 'gif',
        'image/png': 'png'
      };
      return mimes[mime.toLowerCase()] || 'dat';
    };
    var create = function (o, blob, base64, filename) {
      return typeof o === 'object' ? toBlobInfo(o) : toBlobInfo({
        id: o,
        name: filename,
        blob: blob,
        base64: base64
      });
    };
    var toBlobInfo = function (o) {
      var id, name;
      if (!o.blob || !o.base64) {
        throw new Error('blob and base64 representations of the image are required for BlobInfo to be created');
      }
      id = o.id || $_2bgddd4wje5o2qqo.uuid('blobid');
      name = o.name || id;
      return {
        id: constant(id),
        name: constant(name),
        filename: constant(name + '.' + mimeToExt(o.blob.type)),
        blob: constant(o.blob),
        base64: constant(o.base64),
        blobUri: constant(o.blobUri || $_g0v736aje5o2pp8.createObjectURL(o.blob)),
        uri: constant(o.uri)
      };
    };
    var add = function (blobInfo) {
      if (!get(blobInfo.id())) {
        cache.push(blobInfo);
      }
    };
    var get = function (id) {
      return findFirst(function (cachedBlobInfo) {
        return cachedBlobInfo.id() === id;
      });
    };
    var findFirst = function (predicate) {
      return $_c2vc3pkje5o2puw.filter(cache, predicate)[0];
    };
    var getByUri = function (blobUri) {
      return findFirst(function (blobInfo) {
        return blobInfo.blobUri() === blobUri;
      });
    };
    var removeByUri = function (blobUri) {
      cache = $_c2vc3pkje5o2puw.filter(cache, function (blobInfo) {
        if (blobInfo.blobUri() === blobUri) {
          $_g0v736aje5o2pp8.revokeObjectURL(blobInfo.blobUri());
          return false;
        }
        return true;
      });
    };
    var destroy = function () {
      $_c2vc3pkje5o2puw.each(cache, function (cachedBlobInfo) {
        $_g0v736aje5o2pp8.revokeObjectURL(cachedBlobInfo.blobUri());
      });
      cache = [];
    };
    return {
      create: create,
      add: add,
      get: get,
      getByUri: getByUri,
      findFirst: findFirst,
      removeByUri: removeByUri,
      destroy: destroy
    };
  }

  function UploadStatus () {
    var PENDING = 1, UPLOADED = 2;
    var blobUriStatuses = {};
    var createStatus = function (status, resultUri) {
      return {
        status: status,
        resultUri: resultUri
      };
    };
    var hasBlobUri = function (blobUri) {
      return blobUri in blobUriStatuses;
    };
    var getResultUri = function (blobUri) {
      var result = blobUriStatuses[blobUri];
      return result ? result.resultUri : null;
    };
    var isPending = function (blobUri) {
      return hasBlobUri(blobUri) ? blobUriStatuses[blobUri].status === PENDING : false;
    };
    var isUploaded = function (blobUri) {
      return hasBlobUri(blobUri) ? blobUriStatuses[blobUri].status === UPLOADED : false;
    };
    var markPending = function (blobUri) {
      blobUriStatuses[blobUri] = createStatus(PENDING, null);
    };
    var markUploaded = function (blobUri, resultUri) {
      blobUriStatuses[blobUri] = createStatus(UPLOADED, resultUri);
    };
    var removeFailed = function (blobUri) {
      delete blobUriStatuses[blobUri];
    };
    var destroy = function () {
      blobUriStatuses = {};
    };
    return {
      hasBlobUri: hasBlobUri,
      getResultUri: getResultUri,
      isPending: isPending,
      isUploaded: isUploaded,
      markPending: markPending,
      markUploaded: markUploaded,
      removeFailed: removeFailed,
      destroy: destroy
    };
  }

  function EditorUpload (editor) {
    var blobCache = BlobCache();
    var uploader, imageScanner;
    var settings = editor.settings;
    var uploadStatus = UploadStatus();
    var aliveGuard = function (callback) {
      return function (result) {
        if (editor.selection) {
          return callback(result);
        }
        return [];
      };
    };
    var cacheInvalidator = function () {
      return '?' + new Date().getTime();
    };
    var replaceString = function (content, search, replace) {
      var index = 0;
      do {
        index = content.indexOf(search, index);
        if (index !== -1) {
          content = content.substring(0, index) + replace + content.substr(index + search.length);
          index += replace.length - search.length + 1;
        }
      } while (index !== -1);
      return content;
    };
    var replaceImageUrl = function (content, targetUrl, replacementUrl) {
      content = replaceString(content, 'src="' + targetUrl + '"', 'src="' + replacementUrl + '"');
      content = replaceString(content, 'data-mce-src="' + targetUrl + '"', 'data-mce-src="' + replacementUrl + '"');
      return content;
    };
    var replaceUrlInUndoStack = function (targetUrl, replacementUrl) {
      $_c2vc3pkje5o2puw.each(editor.undoManager.data, function (level) {
        if (level.type === 'fragmented') {
          level.fragments = $_c2vc3pkje5o2puw.map(level.fragments, function (fragment) {
            return replaceImageUrl(fragment, targetUrl, replacementUrl);
          });
        } else {
          level.content = replaceImageUrl(level.content, targetUrl, replacementUrl);
        }
      });
    };
    var openNotification = function () {
      return editor.notificationManager.open({
        text: editor.translate('Image uploading...'),
        type: 'info',
        timeout: -1,
        progressBar: true
      });
    };
    var replaceImageUri = function (image, resultUri) {
      blobCache.removeByUri(image.src);
      replaceUrlInUndoStack(image.src, resultUri);
      editor.$(image).attr({
        'src': settings.images_reuse_filename ? resultUri + cacheInvalidator() : resultUri,
        'data-mce-src': editor.convertURL(resultUri, 'src')
      });
    };
    var uploadImages = function (callback) {
      if (!uploader) {
        uploader = Uploader(uploadStatus, {
          url: settings.images_upload_url,
          basePath: settings.images_upload_base_path,
          credentials: settings.images_upload_credentials,
          handler: settings.images_upload_handler
        });
      }
      return scanForImages().then(aliveGuard(function (imageInfos) {
        var blobInfos;
        blobInfos = $_c2vc3pkje5o2puw.map(imageInfos, function (imageInfo) {
          return imageInfo.blobInfo;
        });
        return uploader.upload(blobInfos, openNotification).then(aliveGuard(function (result) {
          var filteredResult = $_c2vc3pkje5o2puw.map(result, function (uploadInfo, index) {
            var image = imageInfos[index].image;
            if (uploadInfo.status && editor.settings.images_replace_blob_uris !== false) {
              replaceImageUri(image, uploadInfo.url);
            } else if (uploadInfo.error) {
              $_axrd1j4hje5o2qor.uploadError(editor, uploadInfo.error);
            }
            return {
              element: image,
              status: uploadInfo.status
            };
          });
          if (callback) {
            callback(filteredResult);
          }
          return filteredResult;
        }));
      }));
    };
    var uploadImagesAuto = function (callback) {
      if (settings.automatic_uploads !== false) {
        return uploadImages(callback);
      }
    };
    var isValidDataUriImage = function (imgElm) {
      return settings.images_dataimg_filter ? settings.images_dataimg_filter(imgElm) : true;
    };
    var scanForImages = function () {
      if (!imageScanner) {
        imageScanner = ImageScanner(uploadStatus, blobCache);
      }
      return imageScanner.findAll(editor.getBody(), isValidDataUriImage).then(aliveGuard(function (result) {
        result = $_c2vc3pkje5o2puw.filter(result, function (resultItem) {
          if (typeof resultItem === 'string') {
            $_axrd1j4hje5o2qor.displayError(editor, resultItem);
            return false;
          }
          return true;
        });
        $_c2vc3pkje5o2puw.each(result, function (resultItem) {
          replaceUrlInUndoStack(resultItem.image.src, resultItem.blobInfo.blobUri());
          resultItem.image.src = resultItem.blobInfo.blobUri();
          resultItem.image.removeAttribute('data-mce-src');
        });
        return result;
      }));
    };
    var destroy = function () {
      blobCache.destroy();
      uploadStatus.destroy();
      imageScanner = uploader = null;
    };
    var replaceBlobUris = function (content) {
      return content.replace(/src="(blob:[^"]+)"/g, function (match, blobUri) {
        var resultUri = uploadStatus.getResultUri(blobUri);
        if (resultUri) {
          return 'src="' + resultUri + '"';
        }
        var blobInfo = blobCache.getByUri(blobUri);
        if (!blobInfo) {
          blobInfo = $_c2vc3pkje5o2puw.reduce(editor.editorManager.get(), function (result, editor) {
            return result || editor.editorUpload && editor.editorUpload.blobCache.getByUri(blobUri);
          }, null);
        }
        if (blobInfo) {
          return 'src="data:' + blobInfo.blob().type + ';base64,' + blobInfo.base64() + '"';
        }
        return match;
      });
    };
    editor.on('setContent', function () {
      if (editor.settings.automatic_uploads !== false) {
        uploadImagesAuto();
      } else {
        scanForImages();
      }
    });
    editor.on('RawSaveContent', function (e) {
      e.content = replaceBlobUris(e.content);
    });
    editor.on('getContent', function (e) {
      if (e.source_view || e.format === 'raw') {
        return;
      }
      e.content = replaceBlobUris(e.content);
    });
    editor.on('PostRender', function () {
      editor.parser.addNodeFilter('img', function (images) {
        $_c2vc3pkje5o2puw.each(images, function (img) {
          var src = img.attr('src');
          if (blobCache.getByUri(src)) {
            return;
          }
          var resultUri = uploadStatus.getResultUri(src);
          if (resultUri) {
            img.attr('src', resultUri);
          }
        });
      });
    });
    return {
      blobCache: blobCache,
      uploadImages: uploadImages,
      uploadImagesAuto: uploadImagesAuto,
      scanForImages: scanForImages,
      destroy: destroy
    };
  }

  var isBlockElement = function (blockElements, node) {
    return blockElements.hasOwnProperty(node.nodeName);
  };
  var isValidTarget = function (blockElements, node) {
    if ($_9owqcy1qje5o2q0e.isText(node)) {
      return true;
    } else if ($_9owqcy1qje5o2q0e.isElement(node)) {
      return !isBlockElement(blockElements, node) && !$_83h91t2jje5o2q8n.isBookmarkNode(node);
    } else {
      return false;
    }
  };
  var hasBlockParent = function (blockElements, root, node) {
    return $_bwcteb4je5o2pl4.exists($_yqcmh39je5o2qcw.parents($_5tcb2dyje5o2pwu.fromDom(node), $_5tcb2dyje5o2pwu.fromDom(root)), function (elm) {
      return isBlockElement(blockElements, elm.dom());
    });
  };
  var shouldRemoveTextNode = function (blockElements, node) {
    if ($_9owqcy1qje5o2q0e.isText(node)) {
      if (node.nodeValue.length === 0) {
        return true;
      } else if (/^\s+$/.test(node.nodeValue) && (!node.nextSibling || isBlockElement(blockElements, node.nextSibling))) {
        return true;
      }
    }
    return false;
  };
  var addRootBlocks = function (editor) {
    var settings = editor.settings, dom = editor.dom, selection = editor.selection;
    var schema = editor.schema, blockElements = schema.getBlockElements();
    var node = selection.getStart();
    var rootNode = editor.getBody();
    var rng;
    var startContainer, startOffset, endContainer, endOffset, rootBlockNode;
    var tempNode, wrapped, restoreSelection;
    var rootNodeName, forcedRootBlock;
    forcedRootBlock = settings.forced_root_block;
    if (!node || !$_9owqcy1qje5o2q0e.isElement(node) || !forcedRootBlock) {
      return;
    }
    rootNodeName = rootNode.nodeName.toLowerCase();
    if (!schema.isValidChild(rootNodeName, forcedRootBlock.toLowerCase()) || hasBlockParent(blockElements, rootNode, node)) {
      return;
    }
    rng = selection.getRng();
    startContainer = rng.startContainer;
    startOffset = rng.startOffset;
    endContainer = rng.endContainer;
    endOffset = rng.endOffset;
    restoreSelection = $_llx5i48je5o2qna.hasFocus(editor);
    node = rootNode.firstChild;
    while (node) {
      if (isValidTarget(blockElements, node)) {
        if (shouldRemoveTextNode(blockElements, node)) {
          tempNode = node;
          node = node.nextSibling;
          dom.remove(tempNode);
          continue;
        }
        if (!rootBlockNode) {
          rootBlockNode = dom.create(forcedRootBlock, editor.settings.forced_root_block_attrs);
          node.parentNode.insertBefore(rootBlockNode, node);
          wrapped = true;
        }
        tempNode = node;
        node = node.nextSibling;
        rootBlockNode.appendChild(tempNode);
      } else {
        rootBlockNode = null;
        node = node.nextSibling;
      }
    }
    if (wrapped && restoreSelection) {
      rng.setStart(startContainer, startOffset);
      rng.setEnd(endContainer, endOffset);
      selection.setRng(rng);
      editor.nodeChanged();
    }
  };
  var setup$1 = function (editor) {
    if (editor.settings.forced_root_block) {
      editor.on('NodeChange', $_enfhkv6je5o2ply.curry(addRootBlocks, editor));
    }
  };
  var $_fekqo94yje5o2qqs = { setup: setup$1 };

  function NodeChange (editor) {
    var lastRng, lastPath = [];
    var isSameElementPath = function (startElm) {
      var i, currentPath;
      currentPath = editor.$(startElm).parentsUntil(editor.getBody()).add(startElm);
      if (currentPath.length === lastPath.length) {
        for (i = currentPath.length; i >= 0; i--) {
          if (currentPath[i] !== lastPath[i]) {
            break;
          }
        }
        if (i === -1) {
          lastPath = currentPath;
          return true;
        }
      }
      lastPath = currentPath;
      return false;
    };
    if (!('onselectionchange' in editor.getDoc())) {
      editor.on('NodeChange Click MouseUp KeyUp Focus', function (e) {
        var nativeRng, fakeRng;
        nativeRng = editor.selection.getRng();
        fakeRng = {
          startContainer: nativeRng.startContainer,
          startOffset: nativeRng.startOffset,
          endContainer: nativeRng.endContainer,
          endOffset: nativeRng.endOffset
        };
        if (e.type === 'nodechange' || !$_aieo7z3yje5o2qlw.isEq(fakeRng, lastRng)) {
          editor.fire('SelectionChange');
        }
        lastRng = fakeRng;
      });
    }
    editor.on('contextmenu', function () {
      editor.fire('SelectionChange');
    });
    editor.on('SelectionChange', function () {
      var startElm = editor.selection.getStart(true);
      if (!startElm || !$_9bd8pn9je5o2pp2.range && editor.selection.isCollapsed()) {
        return;
      }
      if (!isSameElementPath(startElm) && editor.dom.isChildOf(startElm, editor.getBody())) {
        editor.nodeChanged({ selectionChange: true });
      }
    });
    editor.on('MouseUp', function (e) {
      if (!e.isDefaultPrevented()) {
        if (editor.selection.getNode().nodeName === 'IMG') {
          $_db41wogje5o2ps3.setEditorTimeout(editor, function () {
            editor.nodeChanged();
          });
        } else {
          editor.nodeChanged();
        }
      }
    });
    this.nodeChanged = function (args) {
      var selection = editor.selection;
      var node, parents, root;
      if (editor.initialized && selection && !editor.settings.disable_nodechange && !editor.readonly) {
        root = editor.getBody();
        node = selection.getStart(true) || root;
        if (node.ownerDocument !== editor.getDoc() || !editor.dom.isChildOf(node, root)) {
          node = root;
        }
        parents = [];
        editor.dom.getParent(node, function (node) {
          if (node === root) {
            return true;
          }
          parents.push(node);
        });
        args = args || {};
        args.element = node;
        args.parents = parents;
        editor.fire('NodeChange', args);
      }
    };
  }

  var getAbsolutePosition = function (elm) {
    var doc, docElem, win, clientRect;
    clientRect = elm.getBoundingClientRect();
    doc = elm.ownerDocument;
    docElem = doc.documentElement;
    win = doc.defaultView;
    return {
      top: clientRect.top + win.pageYOffset - docElem.clientTop,
      left: clientRect.left + win.pageXOffset - docElem.clientLeft
    };
  };
  var getBodyPosition = function (editor) {
    return editor.inline ? getAbsolutePosition(editor.getBody()) : {
      left: 0,
      top: 0
    };
  };
  var getScrollPosition = function (editor) {
    var body = editor.getBody();
    return editor.inline ? {
      left: body.scrollLeft,
      top: body.scrollTop
    } : {
      left: 0,
      top: 0
    };
  };
  var getBodyScroll = function (editor) {
    var body = editor.getBody(), docElm = editor.getDoc().documentElement;
    var inlineScroll = {
      left: body.scrollLeft,
      top: body.scrollTop
    };
    var iframeScroll = {
      left: body.scrollLeft || docElm.scrollLeft,
      top: body.scrollTop || docElm.scrollTop
    };
    return editor.inline ? inlineScroll : iframeScroll;
  };
  var getMousePosition = function (editor, event) {
    if (event.target.ownerDocument !== editor.getDoc()) {
      var iframePosition = getAbsolutePosition(editor.getContentAreaContainer());
      var scrollPosition = getBodyScroll(editor);
      return {
        left: event.pageX - iframePosition.left + scrollPosition.left,
        top: event.pageY - iframePosition.top + scrollPosition.top
      };
    }
    return {
      left: event.pageX,
      top: event.pageY
    };
  };
  var calculatePosition = function (bodyPosition, scrollPosition, mousePosition) {
    return {
      pageX: mousePosition.left - bodyPosition.left + scrollPosition.left,
      pageY: mousePosition.top - bodyPosition.top + scrollPosition.top
    };
  };
  var calc = function (editor, event) {
    return calculatePosition(getBodyPosition(editor), getScrollPosition(editor), getMousePosition(editor, event));
  };
  var $_f685v452je5o2qrw = { calc: calc };

  var isContentEditableFalse$7 = $_9owqcy1qje5o2q0e.isContentEditableFalse;
  var isContentEditableTrue$4 = $_9owqcy1qje5o2q0e.isContentEditableTrue;
  var isDraggable = function (rootElm, elm) {
    return isContentEditableFalse$7(elm) && elm !== rootElm;
  };
  var isValidDropTarget = function (editor, targetElement, dragElement) {
    if (targetElement === dragElement || editor.dom.isChildOf(targetElement, dragElement)) {
      return false;
    }
    if (isContentEditableFalse$7(targetElement)) {
      return false;
    }
    return true;
  };
  var cloneElement = function (elm) {
    var cloneElm = elm.cloneNode(true);
    cloneElm.removeAttribute('data-mce-selected');
    return cloneElm;
  };
  var createGhost = function (editor, elm, width, height) {
    var clonedElm = elm.cloneNode(true);
    editor.dom.setStyles(clonedElm, {
      width: width,
      height: height
    });
    editor.dom.setAttrib(clonedElm, 'data-mce-selected', null);
    var ghostElm = editor.dom.create('div', {
      'class': 'mce-drag-container',
      'data-mce-bogus': 'all',
      'unselectable': 'on',
      'contenteditable': 'false'
    });
    editor.dom.setStyles(ghostElm, {
      position: 'absolute',
      opacity: 0.5,
      overflow: 'hidden',
      border: 0,
      padding: 0,
      margin: 0,
      width: width,
      height: height
    });
    editor.dom.setStyles(clonedElm, {
      margin: 0,
      boxSizing: 'border-box'
    });
    ghostElm.appendChild(clonedElm);
    return ghostElm;
  };
  var appendGhostToBody = function (ghostElm, bodyElm) {
    if (ghostElm.parentNode !== bodyElm) {
      bodyElm.appendChild(ghostElm);
    }
  };
  var moveGhost = function (ghostElm, position, width, height, maxX, maxY) {
    var overflowX = 0, overflowY = 0;
    ghostElm.style.left = position.pageX + 'px';
    ghostElm.style.top = position.pageY + 'px';
    if (position.pageX + width > maxX) {
      overflowX = position.pageX + width - maxX;
    }
    if (position.pageY + height > maxY) {
      overflowY = position.pageY + height - maxY;
    }
    ghostElm.style.width = width - overflowX + 'px';
    ghostElm.style.height = height - overflowY + 'px';
  };
  var removeElement = function (elm) {
    if (elm && elm.parentNode) {
      elm.parentNode.removeChild(elm);
    }
  };
  var isLeftMouseButtonPressed = function (e) {
    return e.button === 0;
  };
  var hasDraggableElement = function (state) {
    return state.element;
  };
  var applyRelPos = function (state, position) {
    return {
      pageX: position.pageX - state.relX,
      pageY: position.pageY + 5
    };
  };
  var start$1 = function (state, editor) {
    return function (e) {
      if (isLeftMouseButtonPressed(e)) {
        var ceElm = $_c2vc3pkje5o2puw.find(editor.dom.getParents(e.target), $_ghq3nq25je5o2q5q.or(isContentEditableFalse$7, isContentEditableTrue$4));
        if (isDraggable(editor.getBody(), ceElm)) {
          var elmPos = editor.dom.getPos(ceElm);
          var bodyElm = editor.getBody();
          var docElm = editor.getDoc().documentElement;
          state.element = ceElm;
          state.screenX = e.screenX;
          state.screenY = e.screenY;
          state.maxX = (editor.inline ? bodyElm.scrollWidth : docElm.offsetWidth) - 2;
          state.maxY = (editor.inline ? bodyElm.scrollHeight : docElm.offsetHeight) - 2;
          state.relX = e.pageX - elmPos.x;
          state.relY = e.pageY - elmPos.y;
          state.width = ceElm.offsetWidth;
          state.height = ceElm.offsetHeight;
          state.ghost = createGhost(editor, ceElm, state.width, state.height);
        }
      }
    };
  };
  var move$1 = function (state, editor) {
    var throttledPlaceCaretAt = $_db41wogje5o2ps3.throttle(function (clientX, clientY) {
      editor._selectionOverrides.hideFakeCaret();
      editor.selection.placeCaretAt(clientX, clientY);
    }, 0);
    return function (e) {
      var movement = Math.max(Math.abs(e.screenX - state.screenX), Math.abs(e.screenY - state.screenY));
      if (hasDraggableElement(state) && !state.dragging && movement > 10) {
        var args = editor.fire('dragstart', { target: state.element });
        if (args.isDefaultPrevented()) {
          return;
        }
        state.dragging = true;
        editor.focus();
      }
      if (state.dragging) {
        var targetPos = applyRelPos(state, $_f685v452je5o2qrw.calc(editor, e));
        appendGhostToBody(state.ghost, editor.getBody());
        moveGhost(state.ghost, targetPos, state.width, state.height, state.maxX, state.maxY);
        throttledPlaceCaretAt(e.clientX, e.clientY);
      }
    };
  };
  var getRawTarget = function (selection) {
    var rng = selection.getSel().getRangeAt(0);
    var startContainer = rng.startContainer;
    return startContainer.nodeType === 3 ? startContainer.parentNode : startContainer;
  };
  var drop = function (state, editor) {
    return function (e) {
      if (state.dragging) {
        if (isValidDropTarget(editor, getRawTarget(editor.selection), state.element)) {
          var targetClone_1 = cloneElement(state.element);
          var args = editor.fire('drop', {
            targetClone: targetClone_1,
            clientX: e.clientX,
            clientY: e.clientY
          });
          if (!args.isDefaultPrevented()) {
            targetClone_1 = args.targetClone;
            editor.undoManager.transact(function () {
              removeElement(state.element);
              editor.insertContent(editor.dom.getOuterHTML(targetClone_1));
              editor._selectionOverrides.hideFakeCaret();
            });
          }
        }
      }
      removeDragState(state);
    };
  };
  var stop = function (state, editor) {
    return function () {
      removeDragState(state);
      if (state.dragging) {
        editor.fire('dragend');
      }
    };
  };
  var removeDragState = function (state) {
    state.dragging = false;
    state.element = null;
    removeElement(state.ghost);
  };
  var bindFakeDragEvents = function (editor) {
    var state = {};
    var pageDom, dragStartHandler, dragHandler, dropHandler, dragEndHandler, rootDocument;
    pageDom = DOMUtils.DOM;
    rootDocument = document;
    dragStartHandler = start$1(state, editor);
    dragHandler = move$1(state, editor);
    dropHandler = drop(state, editor);
    dragEndHandler = stop(state, editor);
    editor.on('mousedown', dragStartHandler);
    editor.on('mousemove', dragHandler);
    editor.on('mouseup', dropHandler);
    pageDom.bind(rootDocument, 'mousemove', dragHandler);
    pageDom.bind(rootDocument, 'mouseup', dragEndHandler);
    editor.on('remove', function () {
      pageDom.unbind(rootDocument, 'mousemove', dragHandler);
      pageDom.unbind(rootDocument, 'mouseup', dragEndHandler);
    });
  };
  var blockIeDrop = function (editor) {
    editor.on('drop', function (e) {
      var realTarget = typeof e.clientX !== 'undefined' ? editor.getDoc().elementFromPoint(e.clientX, e.clientY) : null;
      if (isContentEditableFalse$7(realTarget) || isContentEditableFalse$7(editor.dom.getContentEditableParent(realTarget))) {
        e.preventDefault();
      }
    });
  };
  var init = function (editor) {
    bindFakeDragEvents(editor);
    blockIeDrop(editor);
  };
  var $_dmqizi51je5o2qrp = { init: init };

  var getNodeClientRects = function (node) {
    var toArrayWithNode = function (clientRects) {
      return $_c2vc3pkje5o2puw.map(clientRects, function (clientRect) {
        clientRect = clone$1(clientRect);
        clientRect.node = node;
        return clientRect;
      });
    };
    if ($_9owqcy1qje5o2q0e.isElement(node)) {
      return toArrayWithNode(node.getClientRects());
    }
    if ($_9owqcy1qje5o2q0e.isText(node)) {
      var rng = node.ownerDocument.createRange();
      rng.setStart(node, 0);
      rng.setEnd(node, node.data.length);
      return toArrayWithNode(rng.getClientRects());
    }
  };
  var getClientRects = function (node) {
    return $_c2vc3pkje5o2puw.reduce(node, function (result, node) {
      return result.concat(getNodeClientRects(node));
    }, []);
  };

  var VDirection;
  (function (VDirection) {
    VDirection[VDirection['Up'] = -1] = 'Up';
    VDirection[VDirection['Down'] = 1] = 'Down';
  }(VDirection || (VDirection = {})));
  var findUntil = function (direction, root, predicateFn, node) {
    while (node = findNode(node, direction, isEditableCaretCandidate, root)) {
      if (predicateFn(node)) {
        return;
      }
    }
  };
  var walkUntil = function (direction, isAboveFn, isBeflowFn, root, predicateFn, caretPosition) {
    var line = 0, node;
    var result = [];
    var targetClientRect;
    var add = function (node) {
      var i, clientRect, clientRects;
      clientRects = getClientRects([node]);
      if (direction === -1) {
        clientRects = clientRects.reverse();
      }
      for (i = 0; i < clientRects.length; i++) {
        clientRect = clientRects[i];
        if (isBeflowFn(clientRect, targetClientRect)) {
          continue;
        }
        if (result.length > 0 && isAboveFn(clientRect, $_c2vc3pkje5o2puw.last(result))) {
          line++;
        }
        clientRect.line = line;
        if (predicateFn(clientRect)) {
          return true;
        }
        result.push(clientRect);
      }
    };
    targetClientRect = $_c2vc3pkje5o2puw.last(caretPosition.getClientRects());
    if (!targetClientRect) {
      return result;
    }
    node = caretPosition.getNode();
    add(node);
    findUntil(direction, root, add, node);
    return result;
  };
  var aboveLineNumber = function (lineNumber, clientRect) {
    return clientRect.line > lineNumber;
  };
  var isLineNumber = function (lineNumber, clientRect) {
    return clientRect.line === lineNumber;
  };
  var upUntil = $_enfhkv6je5o2ply.curry(walkUntil, VDirection.Up, isAbove, isBelow);
  var downUntil = $_enfhkv6je5o2ply.curry(walkUntil, VDirection.Down, isBelow, isAbove);
  var positionsUntil = function (direction, root, predicateFn, node) {
    var caretWalker = CaretWalker(root);
    var walkFn, isBelowFn, isAboveFn, caretPosition;
    var result = [];
    var line = 0, clientRect, targetClientRect;
    var getClientRect = function (caretPosition) {
      if (direction === 1) {
        return $_c2vc3pkje5o2puw.last(caretPosition.getClientRects());
      }
      return $_c2vc3pkje5o2puw.last(caretPosition.getClientRects());
    };
    if (direction === 1) {
      walkFn = caretWalker.next;
      isBelowFn = isBelow;
      isAboveFn = isAbove;
      caretPosition = CaretPosition$1.after(node);
    } else {
      walkFn = caretWalker.prev;
      isBelowFn = isAbove;
      isAboveFn = isBelow;
      caretPosition = CaretPosition$1.before(node);
    }
    targetClientRect = getClientRect(caretPosition);
    do {
      if (!caretPosition.isVisible()) {
        continue;
      }
      clientRect = getClientRect(caretPosition);
      if (isAboveFn(clientRect, targetClientRect)) {
        continue;
      }
      if (result.length > 0 && isBelowFn(clientRect, $_c2vc3pkje5o2puw.last(result))) {
        line++;
      }
      clientRect = clone$1(clientRect);
      clientRect.position = caretPosition;
      clientRect.line = line;
      if (predicateFn(clientRect)) {
        return result;
      }
      result.push(clientRect);
    } while (caretPosition = walkFn(caretPosition));
    return result;
  };
  var isAboveLine = function (lineNumber) {
    return function (clientRect) {
      return aboveLineNumber(lineNumber, clientRect);
    };
  };
  var isLine = function (lineNumber) {
    return function (clientRect) {
      return isLineNumber(lineNumber, clientRect);
    };
  };

  var isContentEditableFalse$8 = $_9owqcy1qje5o2q0e.isContentEditableFalse;
  var findNode$1 = findNode;
  var distanceToRectLeft = function (clientRect, clientX) {
    return Math.abs(clientRect.left - clientX);
  };
  var distanceToRectRight = function (clientRect, clientX) {
    return Math.abs(clientRect.right - clientX);
  };
  var isInside = function (clientX, clientRect) {
    return clientX >= clientRect.left && clientX <= clientRect.right;
  };
  var findClosestClientRect = function (clientRects, clientX) {
    return $_c2vc3pkje5o2puw.reduce(clientRects, function (oldClientRect, clientRect) {
      var oldDistance, newDistance;
      oldDistance = Math.min(distanceToRectLeft(oldClientRect, clientX), distanceToRectRight(oldClientRect, clientX));
      newDistance = Math.min(distanceToRectLeft(clientRect, clientX), distanceToRectRight(clientRect, clientX));
      if (isInside(clientX, clientRect)) {
        return clientRect;
      }
      if (isInside(clientX, oldClientRect)) {
        return oldClientRect;
      }
      if (newDistance === oldDistance && isContentEditableFalse$8(clientRect.node)) {
        return clientRect;
      }
      if (newDistance < oldDistance) {
        return clientRect;
      }
      return oldClientRect;
    });
  };
  var walkUntil$1 = function (direction, root, predicateFn, node) {
    while (node = findNode$1(node, direction, isEditableCaretCandidate, root)) {
      if (predicateFn(node)) {
        return;
      }
    }
  };
  var findLineNodeRects = function (root, targetNodeRect) {
    var clientRects = [];
    var collect = function (checkPosFn, node) {
      var lineRects;
      lineRects = $_c2vc3pkje5o2puw.filter(getClientRects([node]), function (clientRect) {
        return !checkPosFn(clientRect, targetNodeRect);
      });
      clientRects = clientRects.concat(lineRects);
      return lineRects.length === 0;
    };
    clientRects.push(targetNodeRect);
    walkUntil$1(VDirection.Up, root, $_enfhkv6je5o2ply.curry(collect, isAbove), targetNodeRect.node);
    walkUntil$1(VDirection.Down, root, $_enfhkv6je5o2ply.curry(collect, isBelow), targetNodeRect.node);
    return clientRects;
  };
  var getFakeCaretTargets = function (root) {
    return $_c2vc3pkje5o2puw.filter($_c2vc3pkje5o2puw.toArray(root.getElementsByTagName('*')), isFakeCaretTarget);
  };
  var caretInfo = function (clientRect, clientX) {
    return {
      node: clientRect.node,
      before: distanceToRectLeft(clientRect, clientX) < distanceToRectRight(clientRect, clientX)
    };
  };
  var closestCaret = function (root, clientX, clientY) {
    var closestNodeRect;
    var contentEditableFalseNodeRects = getClientRects(getFakeCaretTargets(root));
    var targetNodeRects = $_c2vc3pkje5o2puw.filter(contentEditableFalseNodeRects, function (rect) {
      return clientY >= rect.top && clientY <= rect.bottom;
    });
    closestNodeRect = findClosestClientRect(targetNodeRects, clientX);
    if (closestNodeRect) {
      closestNodeRect = findClosestClientRect(findLineNodeRects(root, closestNodeRect), clientX);
      if (closestNodeRect && isFakeCaretTarget(closestNodeRect.node)) {
        return caretInfo(closestNodeRect, clientX);
      }
    }
    return null;
  };

  var isXYWithinRange = function (clientX, clientY, range) {
    if (range.collapsed) {
      return false;
    }
    return $_bwcteb4je5o2pl4.foldl(range.getClientRects(), function (state, rect) {
      return state || containsXY(rect, clientX, clientY);
    }, false);
  };
  var $_40xr2y56je5o2qsc = { isXYWithinRange: isXYWithinRange };

  var adaptable = function (fn, rate) {
    var timer = null;
    var args = null;
    var cancel = function () {
      if (timer !== null) {
        clearTimeout(timer);
        timer = null;
        args = null;
      }
    };
    var throttle = function () {
      args = arguments;
      if (timer === null) {
        timer = setTimeout(function () {
          fn.apply(null, args);
          timer = null;
          args = null;
        }, rate);
      }
    };
    return {
      cancel: cancel,
      throttle: throttle
    };
  };
  var first$3 = function (fn, rate) {
    var timer = null;
    var cancel = function () {
      if (timer !== null) {
        clearTimeout(timer);
        timer = null;
      }
    };
    var throttle = function () {
      var args = arguments;
      if (timer === null) {
        timer = setTimeout(function () {
          fn.apply(null, args);
          timer = null;
          args = null;
        }, rate);
      }
    };
    return {
      cancel: cancel,
      throttle: throttle
    };
  };
  var last$3 = function (fn, rate) {
    var timer = null;
    var cancel = function () {
      if (timer !== null) {
        clearTimeout(timer);
        timer = null;
      }
    };
    var throttle = function () {
      var args = arguments;
      if (timer !== null)
        clearTimeout(timer);
      timer = setTimeout(function () {
        fn.apply(null, args);
        timer = null;
        args = null;
      }, rate);
    };
    return {
      cancel: cancel,
      throttle: throttle
    };
  };
  var $_7qjijn58je5o2qsg = {
    adaptable: adaptable,
    first: first$3,
    last: last$3
  };

  var setup$2 = function (editor) {
    var renderFocusCaret = $_7qjijn58je5o2qsg.first(function () {
      if (!editor.removed) {
        var rng = editor.selection.getRng();
        if (rng.collapsed) {
          var caretRange = renderRangeCaret(editor, editor.selection.getRng(), false);
          editor.selection.setRng(caretRange);
        }
      }
    }, 0);
    editor.on('focus', function () {
      renderFocusCaret.throttle();
    });
    editor.on('blur', function () {
      renderFocusCaret.cancel();
    });
  };
  var $_ceg6uc57je5o2qse = { setup: setup$2 };

  var $_99mhlk59je5o2qss = {
    BACKSPACE: 8,
    DELETE: 46,
    DOWN: 40,
    ENTER: 13,
    LEFT: 37,
    RIGHT: 39,
    SPACEBAR: 32,
    TAB: 9,
    UP: 38,
    modifierPressed: function (e) {
      return e.shiftKey || e.ctrlKey || e.altKey || this.metaKeyPressed(e);
    },
    metaKeyPressed: function (e) {
      return $_9bd8pn9je5o2pp2.mac ? e.metaKey : e.ctrlKey && !e.altKey;
    }
  };

  var isContentEditableTrue$5 = $_9owqcy1qje5o2q0e.isContentEditableTrue;
  var isContentEditableFalse$9 = $_9owqcy1qje5o2q0e.isContentEditableFalse;
  var isAfterContentEditableFalse$1 = isAfterContentEditableFalse;
  var isBeforeContentEditableFalse$1 = isBeforeContentEditableFalse;
  var SelectionOverrides = function (editor) {
    var isBlock = function (node) {
      return editor.dom.isBlock(node);
    };
    var rootNode = editor.getBody();
    var fakeCaret = FakeCaret(editor.getBody(), isBlock, function () {
      return $_llx5i48je5o2qna.hasFocus(editor);
    });
    var realSelectionId = 'sel-' + editor.dom.uniqueId();
    var selectedContentEditableNode;
    var isFakeSelectionElement = function (elm) {
      return editor.dom.hasClass(elm, 'mce-offscreen-selection');
    };
    var getRealSelectionElement = function () {
      var container = editor.dom.get(realSelectionId);
      return container ? container.getElementsByTagName('*')[0] : container;
    };
    var setRange = function (range) {
      if (range) {
        editor.selection.setRng(range);
      }
    };
    var getRange = function () {
      return editor.selection.getRng();
    };
    var showCaret$$1 = function (direction, node, before, scrollIntoView) {
      if (scrollIntoView === void 0) {
        scrollIntoView = true;
      }
      var e;
      e = editor.fire('ShowCaret', {
        target: node,
        direction: direction,
        before: before
      });
      if (e.isDefaultPrevented()) {
        return null;
      }
      if (scrollIntoView) {
        editor.selection.scrollIntoView(node, direction === -1);
      }
      return fakeCaret.show(before, node);
    };
    var getNormalizedRangeEndPoint$$1 = function (direction, range) {
      range = normalizeRange(direction, rootNode, range);
      if (direction === -1) {
        return CaretPosition$1.fromRangeStart(range);
      }
      return CaretPosition$1.fromRangeEnd(range);
    };
    var showBlockCaretContainer = function (blockCaretContainer) {
      if (blockCaretContainer.hasAttribute('data-mce-caret')) {
        showCaretContainerBlock(blockCaretContainer);
        setRange(getRange());
        editor.selection.scrollIntoView(blockCaretContainer[0]);
      }
    };
    var registerEvents = function () {
      var getContentEditableRoot = function (node) {
        var root = editor.getBody();
        while (node && node !== root) {
          if (isContentEditableTrue$5(node) || isContentEditableFalse$9(node)) {
            return node;
          }
          node = node.parentNode;
        }
        return null;
      };
      editor.on('mouseup', function (e) {
        var range = getRange();
        if (range.collapsed && $_3f7nxx4dje5o2qoa.isXYInContentArea(editor, e.clientX, e.clientY)) {
          setRange(renderCaretAtRange(editor, range, false));
        }
      });
      editor.on('click', function (e) {
        var contentEditableRoot;
        contentEditableRoot = getContentEditableRoot(e.target);
        if (contentEditableRoot) {
          if (isContentEditableFalse$9(contentEditableRoot)) {
            e.preventDefault();
            editor.focus();
          }
          if (isContentEditableTrue$5(contentEditableRoot)) {
            if (editor.dom.isChildOf(contentEditableRoot, editor.selection.getNode())) {
              removeContentEditableSelection();
            }
          }
        }
      });
      editor.on('blur NewBlock', function () {
        removeContentEditableSelection();
      });
      var handleTouchSelect = function (editor) {
        var moved = false;
        editor.on('touchstart', function () {
          moved = false;
        });
        editor.on('touchmove', function () {
          moved = true;
        });
        editor.on('touchend', function (e) {
          var contentEditableRoot = getContentEditableRoot(e.target);
          if (isContentEditableFalse$9(contentEditableRoot)) {
            if (!moved) {
              e.preventDefault();
              setContentEditableSelection(selectNode(editor, contentEditableRoot));
            }
          }
        });
      };
      var hasNormalCaretPosition = function (elm) {
        var caretWalker = CaretWalker(elm);
        if (!elm.firstChild) {
          return false;
        }
        var startPos = CaretPosition$1.before(elm.firstChild);
        var newPos = caretWalker.next(startPos);
        return newPos && !isBeforeContentEditableFalse$1(newPos) && !isAfterContentEditableFalse$1(newPos);
      };
      var isInSameBlock$$1 = function (node1, node2) {
        var block1 = editor.dom.getParent(node1, editor.dom.isBlock);
        var block2 = editor.dom.getParent(node2, editor.dom.isBlock);
        return block1 === block2;
      };
      var hasBetterMouseTarget = function (targetNode, caretNode) {
        var targetBlock = editor.dom.getParent(targetNode, editor.dom.isBlock);
        var caretBlock = editor.dom.getParent(caretNode, editor.dom.isBlock);
        return targetBlock && !isInSameBlock$$1(targetBlock, caretBlock) && hasNormalCaretPosition(targetBlock);
      };
      handleTouchSelect(editor);
      editor.on('mousedown', function (e) {
        var contentEditableRoot;
        var targetElm = e.target;
        if (targetElm !== rootNode && targetElm.nodeName !== 'HTML' && !editor.dom.isChildOf(targetElm, rootNode)) {
          return;
        }
        if ($_3f7nxx4dje5o2qoa.isXYInContentArea(editor, e.clientX, e.clientY) === false) {
          return;
        }
        contentEditableRoot = getContentEditableRoot(targetElm);
        if (contentEditableRoot) {
          if (isContentEditableFalse$9(contentEditableRoot)) {
            e.preventDefault();
            setContentEditableSelection(selectNode(editor, contentEditableRoot));
          } else {
            removeContentEditableSelection();
            if (!(isContentEditableTrue$5(contentEditableRoot) && e.shiftKey) && !$_40xr2y56je5o2qsc.isXYWithinRange(e.clientX, e.clientY, editor.selection.getRng())) {
              editor.selection.placeCaretAt(e.clientX, e.clientY);
            }
          }
        } else if (isFakeCaretTarget(targetElm) === false) {
          removeContentEditableSelection();
          hideFakeCaret();
          var caretInfo = closestCaret(rootNode, e.clientX, e.clientY);
          if (caretInfo) {
            if (!hasBetterMouseTarget(e.target, caretInfo.node)) {
              e.preventDefault();
              var range = showCaret$$1(1, caretInfo.node, caretInfo.before, false);
              editor.getBody().focus();
              setRange(range);
            }
          }
        }
      });
      editor.on('keypress', function (e) {
        if ($_99mhlk59je5o2qss.modifierPressed(e)) {
          return;
        }
        switch (e.keyCode) {
        default:
          if (isContentEditableFalse$9(editor.selection.getNode())) {
            e.preventDefault();
          }
          break;
        }
      });
      editor.on('getSelectionRange', function (e) {
        var rng = e.range;
        if (selectedContentEditableNode) {
          if (!selectedContentEditableNode.parentNode) {
            selectedContentEditableNode = null;
            return;
          }
          rng = rng.cloneRange();
          rng.selectNode(selectedContentEditableNode);
          e.range = rng;
        }
      });
      editor.on('setSelectionRange', function (e) {
        var rng;
        rng = setContentEditableSelection(e.range, e.forward);
        if (rng) {
          e.range = rng;
        }
      });
      editor.on('AfterSetSelectionRange', function (e) {
        var rng = e.range;
        if (!isRangeInCaretContainer(rng)) {
          hideFakeCaret();
        }
        if (!isFakeSelectionElement(rng.startContainer.parentNode)) {
          removeContentEditableSelection();
        }
      });
      editor.on('copy', function (e) {
        var clipboardData = e.clipboardData;
        if (!e.isDefaultPrevented() && e.clipboardData && !$_9bd8pn9je5o2pp2.ie) {
          var realSelectionElement = getRealSelectionElement();
          if (realSelectionElement) {
            e.preventDefault();
            clipboardData.clearData();
            clipboardData.setData('text/html', realSelectionElement.outerHTML);
            clipboardData.setData('text/plain', realSelectionElement.outerText);
          }
        }
      });
      $_dmqizi51je5o2qrp.init(editor);
      $_ceg6uc57je5o2qse.setup(editor);
    };
    var addCss = function () {
      var styles = editor.contentStyles, rootClass = '.mce-content-body';
      styles.push(fakeCaret.getCss());
      styles.push(rootClass + ' .mce-offscreen-selection {' + 'position: absolute;' + 'left: -9999999999px;' + 'max-width: 1000000px;' + '}' + rootClass + ' *[contentEditable=false] {' + 'cursor: default;' + '}' + rootClass + ' *[contentEditable=true] {' + 'cursor: text;' + '}');
    };
    var isWithinCaretContainer = function (node) {
      return isCaretContainer(node) || startsWithCaretContainer(node) || endsWithCaretContainer(node);
    };
    var isRangeInCaretContainer = function (rng) {
      return isWithinCaretContainer(rng.startContainer) || isWithinCaretContainer(rng.endContainer);
    };
    var setContentEditableSelection = function (range, forward) {
      var node;
      var $ = editor.$;
      var dom = editor.dom;
      var $realSelectionContainer, sel, startContainer, startOffset, endOffset, e, caretPosition, targetClone, origTargetClone;
      if (!range) {
        return null;
      }
      if (range.collapsed) {
        if (!isRangeInCaretContainer(range)) {
          if (forward === false) {
            caretPosition = getNormalizedRangeEndPoint$$1(-1, range);
            if (isFakeCaretTarget(caretPosition.getNode(true))) {
              return showCaret$$1(-1, caretPosition.getNode(true), false, false);
            }
            if (isFakeCaretTarget(caretPosition.getNode())) {
              return showCaret$$1(-1, caretPosition.getNode(), !caretPosition.isAtEnd(), false);
            }
          } else {
            caretPosition = getNormalizedRangeEndPoint$$1(1, range);
            if (isFakeCaretTarget(caretPosition.getNode())) {
              return showCaret$$1(1, caretPosition.getNode(), !caretPosition.isAtEnd(), false);
            }
            if (isFakeCaretTarget(caretPosition.getNode(true))) {
              return showCaret$$1(1, caretPosition.getNode(true), false, false);
            }
          }
        }
        return null;
      }
      startContainer = range.startContainer;
      startOffset = range.startOffset;
      endOffset = range.endOffset;
      if (startContainer.nodeType === 3 && startOffset === 0 && isContentEditableFalse$9(startContainer.parentNode)) {
        startContainer = startContainer.parentNode;
        startOffset = dom.nodeIndex(startContainer);
        startContainer = startContainer.parentNode;
      }
      if (startContainer.nodeType !== 1) {
        return null;
      }
      if (endOffset === startOffset + 1) {
        node = startContainer.childNodes[startOffset];
      }
      if (!isContentEditableFalse$9(node)) {
        return null;
      }
      targetClone = origTargetClone = node.cloneNode(true);
      e = editor.fire('ObjectSelected', {
        target: node,
        targetClone: targetClone
      });
      if (e.isDefaultPrevented()) {
        return null;
      }
      $realSelectionContainer = $_9fzewc37je5o2qck.descendant($_5tcb2dyje5o2pwu.fromDom(editor.getBody()), '#' + realSelectionId).fold(function () {
        return $([]);
      }, function (elm) {
        return $([elm.dom()]);
      });
      targetClone = e.targetClone;
      if ($realSelectionContainer.length === 0) {
        $realSelectionContainer = $('<div data-mce-bogus="all" class="mce-offscreen-selection"></div>').attr('id', realSelectionId);
        $realSelectionContainer.appendTo(editor.getBody());
      }
      range = editor.dom.createRng();
      if (targetClone === origTargetClone && $_9bd8pn9je5o2pp2.ie) {
        $realSelectionContainer.empty().append('<p style="font-size: 0" data-mce-bogus="all">\xA0</p>').append(targetClone);
        range.setStartAfter($realSelectionContainer[0].firstChild.firstChild);
        range.setEndAfter(targetClone);
      } else {
        $realSelectionContainer.empty().append('\xA0').append(targetClone).append('\xA0');
        range.setStart($realSelectionContainer[0].firstChild, 1);
        range.setEnd($realSelectionContainer[0].lastChild, 0);
      }
      $realSelectionContainer.css({ top: dom.getPos(node, editor.getBody()).y });
      $realSelectionContainer[0].focus();
      sel = editor.selection.getSel();
      sel.removeAllRanges();
      sel.addRange(range);
      $_bwcteb4je5o2pl4.each($_b4jhpu2gje5o2q8e.descendants($_5tcb2dyje5o2pwu.fromDom(editor.getBody()), '*[data-mce-selected]'), function (elm) {
        $_3uo9pl14je5o2pxq.remove(elm, 'data-mce-selected');
      });
      node.setAttribute('data-mce-selected', '1');
      selectedContentEditableNode = node;
      hideFakeCaret();
      return range;
    };
    var removeContentEditableSelection = function () {
      if (selectedContentEditableNode) {
        selectedContentEditableNode.removeAttribute('data-mce-selected');
        $_9fzewc37je5o2qck.descendant($_5tcb2dyje5o2pwu.fromDom(editor.getBody()), '#' + realSelectionId).each($_cr1seh2pje5o2qa1.remove);
        selectedContentEditableNode = null;
      }
    };
    var destroy = function () {
      fakeCaret.destroy();
      selectedContentEditableNode = null;
    };
    var hideFakeCaret = function () {
      fakeCaret.hide();
    };
    if ($_9bd8pn9je5o2pp2.ceFalse) {
      registerEvents();
      addCss();
    }
    return {
      showCaret: showCaret$$1,
      showBlockCaretContainer: showBlockCaretContainer,
      hideFakeCaret: hideFakeCaret,
      destroy: destroy
    };
  };

  var each$11 = $_a5gegtjje5o2pum.each;
  var isValidPrefixAttrName = function (name) {
    return name.indexOf('data-') === 0 || name.indexOf('aria-') === 0;
  };
  var trimComments = function (text) {
    return text.replace(/<!--|-->/g, '');
  };
  var findEndTagIndex = function (schema, html, startIndex) {
    var count = 1, index, matches, tokenRegExp, shortEndedElements;
    shortEndedElements = schema.getShortEndedElements();
    tokenRegExp = /<([!?\/])?([A-Za-z0-9\-_\:\.]+)((?:\s+[^"\'>]+(?:(?:"[^"]*")|(?:\'[^\']*\')|[^>]*))*|\/|\s+)>/g;
    tokenRegExp.lastIndex = index = startIndex;
    while (matches = tokenRegExp.exec(html)) {
      index = tokenRegExp.lastIndex;
      if (matches[1] === '/') {
        count--;
      } else if (!matches[1]) {
        if (matches[2] in shortEndedElements) {
          continue;
        }
        count++;
      }
      if (count === 0) {
        break;
      }
    }
    return index;
  };
  function SaxParser(settings, schema) {
    if (schema === void 0) {
      schema = Schema();
    }
    var noop = function () {
    };
    settings = settings || {};
    if (settings.fix_self_closing !== false) {
      settings.fix_self_closing = true;
    }
    each$11('comment cdata text start end pi doctype'.split(' '), function (name) {
      if (name) {
        self[name] = settings[name] || noop;
      }
    });
    var comment = settings.comment ? settings.comment : noop;
    var cdata = settings.cdata ? settings.cdata : noop;
    var text = settings.text ? settings.text : noop;
    var start = settings.start ? settings.start : noop;
    var end = settings.end ? settings.end : noop;
    var pi = settings.pi ? settings.pi : noop;
    var doctype = settings.doctype ? settings.doctype : noop;
    var parse = function (html) {
      var matches, index = 0, value, endRegExp;
      var stack = [];
      var attrList, i, textData, name;
      var isInternalElement, removeInternalElements, shortEndedElements, fillAttrsMap, isShortEnded;
      var validate, elementRule, isValidElement, attr, attribsValue, validAttributesMap, validAttributePatterns;
      var attributesRequired, attributesDefault, attributesForced, processHtml;
      var anyAttributesRequired, selfClosing, tokenRegExp, attrRegExp, specialElements, attrValue, idCount = 0;
      var decode = $_fzh4ld1rje5o2q0t.decode;
      var fixSelfClosing;
      var filteredUrlAttrs = $_a5gegtjje5o2pum.makeMap('src,href,data,background,formaction,poster');
      var scriptUriRegExp = /((java|vb)script|mhtml):/i, dataUriRegExp = /^data:/i;
      var processEndTag = function (name) {
        var pos, i;
        pos = stack.length;
        while (pos--) {
          if (stack[pos].name === name) {
            break;
          }
        }
        if (pos >= 0) {
          for (i = stack.length - 1; i >= pos; i--) {
            name = stack[i];
            if (name.valid) {
              end(name.name);
            }
          }
          stack.length = pos;
        }
      };
      var parseAttribute = function (match, name, value, val2, val3) {
        var attrRule, i;
        var trimRegExp = /[\s\u0000-\u001F]+/g;
        name = name.toLowerCase();
        value = name in fillAttrsMap ? name : decode(value || val2 || val3 || '');
        if (validate && !isInternalElement && isValidPrefixAttrName(name) === false) {
          attrRule = validAttributesMap[name];
          if (!attrRule && validAttributePatterns) {
            i = validAttributePatterns.length;
            while (i--) {
              attrRule = validAttributePatterns[i];
              if (attrRule.pattern.test(name)) {
                break;
              }
            }
            if (i === -1) {
              attrRule = null;
            }
          }
          if (!attrRule) {
            return;
          }
          if (attrRule.validValues && !(value in attrRule.validValues)) {
            return;
          }
        }
        if (filteredUrlAttrs[name] && !settings.allow_script_urls) {
          var uri = value.replace(trimRegExp, '');
          try {
            uri = decodeURIComponent(uri);
          } catch (ex) {
            uri = unescape(uri);
          }
          if (scriptUriRegExp.test(uri)) {
            return;
          }
          if (!settings.allow_html_data_urls && dataUriRegExp.test(uri) && !/^data:image\//i.test(uri)) {
            return;
          }
        }
        if (isInternalElement && (name in filteredUrlAttrs || name.indexOf('on') === 0)) {
          return;
        }
        attrList.map[name] = value;
        attrList.push({
          name: name,
          value: value
        });
      };
      tokenRegExp = new RegExp('<(?:' + '(?:!--([\\w\\W]*?)-->)|' + '(?:!\\[CDATA\\[([\\w\\W]*?)\\]\\]>)|' + '(?:!DOCTYPE([\\w\\W]*?)>)|' + '(?:\\?([^\\s\\/<>]+) ?([\\w\\W]*?)[?/]>)|' + '(?:\\/([A-Za-z][A-Za-z0-9\\-_\\:\\.]*)>)|' + '(?:([A-Za-z][A-Za-z0-9\\-_\\:\\.]*)((?:\\s+[^"\'>]+(?:(?:"[^"]*")|(?:\'[^\']*\')|[^>]*))*|\\/|\\s+)>)' + ')', 'g');
      attrRegExp = /([\w:\-]+)(?:\s*=\s*(?:(?:\"((?:[^\"])*)\")|(?:\'((?:[^\'])*)\')|([^>\s]+)))?/g;
      shortEndedElements = schema.getShortEndedElements();
      selfClosing = settings.self_closing_elements || schema.getSelfClosingElements();
      fillAttrsMap = schema.getBoolAttrs();
      validate = settings.validate;
      removeInternalElements = settings.remove_internals;
      fixSelfClosing = settings.fix_self_closing;
      specialElements = schema.getSpecialElements();
      processHtml = html + '>';
      while (matches = tokenRegExp.exec(processHtml)) {
        if (index < matches.index) {
          text(decode(html.substr(index, matches.index - index)));
        }
        if (value = matches[6]) {
          value = value.toLowerCase();
          if (value.charAt(0) === ':') {
            value = value.substr(1);
          }
          processEndTag(value);
        } else if (value = matches[7]) {
          if (matches.index + matches[0].length > html.length) {
            text(decode(html.substr(matches.index)));
            index = matches.index + matches[0].length;
            continue;
          }
          value = value.toLowerCase();
          if (value.charAt(0) === ':') {
            value = value.substr(1);
          }
          isShortEnded = value in shortEndedElements;
          if (fixSelfClosing && selfClosing[value] && stack.length > 0 && stack[stack.length - 1].name === value) {
            processEndTag(value);
          }
          if (!validate || (elementRule = schema.getElementRule(value))) {
            isValidElement = true;
            if (validate) {
              validAttributesMap = elementRule.attributes;
              validAttributePatterns = elementRule.attributePatterns;
            }
            if (attribsValue = matches[8]) {
              isInternalElement = attribsValue.indexOf('data-mce-type') !== -1;
              if (isInternalElement && removeInternalElements) {
                isValidElement = false;
              }
              attrList = [];
              attrList.map = {};
              attribsValue.replace(attrRegExp, parseAttribute);
            } else {
              attrList = [];
              attrList.map = {};
            }
            if (validate && !isInternalElement) {
              attributesRequired = elementRule.attributesRequired;
              attributesDefault = elementRule.attributesDefault;
              attributesForced = elementRule.attributesForced;
              anyAttributesRequired = elementRule.removeEmptyAttrs;
              if (anyAttributesRequired && !attrList.length) {
                isValidElement = false;
              }
              if (attributesForced) {
                i = attributesForced.length;
                while (i--) {
                  attr = attributesForced[i];
                  name = attr.name;
                  attrValue = attr.value;
                  if (attrValue === '{$uid}') {
                    attrValue = 'mce_' + idCount++;
                  }
                  attrList.map[name] = attrValue;
                  attrList.push({
                    name: name,
                    value: attrValue
                  });
                }
              }
              if (attributesDefault) {
                i = attributesDefault.length;
                while (i--) {
                  attr = attributesDefault[i];
                  name = attr.name;
                  if (!(name in attrList.map)) {
                    attrValue = attr.value;
                    if (attrValue === '{$uid}') {
                      attrValue = 'mce_' + idCount++;
                    }
                    attrList.map[name] = attrValue;
                    attrList.push({
                      name: name,
                      value: attrValue
                    });
                  }
                }
              }
              if (attributesRequired) {
                i = attributesRequired.length;
                while (i--) {
                  if (attributesRequired[i] in attrList.map) {
                    break;
                  }
                }
                if (i === -1) {
                  isValidElement = false;
                }
              }
              if (attr = attrList.map['data-mce-bogus']) {
                if (attr === 'all') {
                  index = findEndTagIndex(schema, html, tokenRegExp.lastIndex);
                  tokenRegExp.lastIndex = index;
                  continue;
                }
                isValidElement = false;
              }
            }
            if (isValidElement) {
              start(value, attrList, isShortEnded);
            }
          } else {
            isValidElement = false;
          }
          if (endRegExp = specialElements[value]) {
            endRegExp.lastIndex = index = matches.index + matches[0].length;
            if (matches = endRegExp.exec(html)) {
              if (isValidElement) {
                textData = html.substr(index, matches.index - index);
              }
              index = matches.index + matches[0].length;
            } else {
              textData = html.substr(index);
              index = html.length;
            }
            if (isValidElement) {
              if (textData.length > 0) {
                text(textData, true);
              }
              end(value);
            }
            tokenRegExp.lastIndex = index;
            continue;
          }
          if (!isShortEnded) {
            if (!attribsValue || attribsValue.indexOf('/') !== attribsValue.length - 1) {
              stack.push({
                name: value,
                valid: isValidElement
              });
            } else if (isValidElement) {
              end(value);
            }
          }
        } else if (value = matches[1]) {
          if (value.charAt(0) === '>') {
            value = ' ' + value;
          }
          if (!settings.allow_conditional_comments && value.substr(0, 3).toLowerCase() === '[if') {
            value = ' ' + value;
          }
          comment(value);
        } else if (value = matches[2]) {
          cdata(trimComments(value));
        } else if (value = matches[3]) {
          doctype(value);
        } else if (value = matches[4]) {
          pi(value, matches[5]);
        }
        index = matches.index + matches[0].length;
      }
      if (index < html.length) {
        text(decode(html.substr(index)));
      }
      for (i = stack.length - 1; i >= 0; i--) {
        value = stack[i];
        if (value.valid) {
          end(value.name);
        }
      }
    };
    return { parse: parse };
  }
  (function (SaxParser) {
    SaxParser.findEndTag = findEndTagIndex;
  }(SaxParser || (SaxParser = {})));
  var SaxParser$1 = SaxParser;

  var trimHtml = function (tempAttrs, html) {
    var trimContentRegExp = new RegExp(['\\s?(' + tempAttrs.join('|') + ')="[^"]+"'].join('|'), 'gi');
    return html.replace(trimContentRegExp, '');
  };
  var trimInternal = function (serializer, html) {
    var content = html;
    var bogusAllRegExp = /<(\w+) [^>]*data-mce-bogus="all"[^>]*>/g;
    var endTagIndex, index, matchLength, matches, shortEndedElements;
    var schema = serializer.schema;
    content = trimHtml(serializer.getTempAttrs(), content);
    shortEndedElements = schema.getShortEndedElements();
    while (matches = bogusAllRegExp.exec(content)) {
      index = bogusAllRegExp.lastIndex;
      matchLength = matches[0].length;
      if (shortEndedElements[matches[1]]) {
        endTagIndex = index;
      } else {
        endTagIndex = SaxParser$1.findEndTag(schema, content, index);
      }
      content = content.substring(0, index - matchLength) + content.substring(endTagIndex);
      bogusAllRegExp.lastIndex = index - matchLength;
    }
    return content;
  };
  var trimExternal = function (serializer, html) {
    return $_4md31c21je5o2q5f.trim(trimInternal(serializer, html));
  };
  var $_15ih885cje5o2qt6 = {
    trimExternal: trimExternal,
    trimInternal: trimInternal
  };

  var KEEP = 0;
  var INSERT = 1;
  var DELETE = 2;
  var diff = function (left, right) {
    var size = left.length + right.length + 2;
    var vDown = new Array(size);
    var vUp = new Array(size);
    var snake = function (start, end, diag) {
      return {
        start: start,
        end: end,
        diag: diag
      };
    };
    var buildScript = function (start1, end1, start2, end2, script) {
      var middle = getMiddleSnake(start1, end1, start2, end2);
      if (middle === null || middle.start === end1 && middle.diag === end1 - end2 || middle.end === start1 && middle.diag === start1 - start2) {
        var i = start1;
        var j = start2;
        while (i < end1 || j < end2) {
          if (i < end1 && j < end2 && left[i] === right[j]) {
            script.push([
              KEEP,
              left[i]
            ]);
            ++i;
            ++j;
          } else {
            if (end1 - start1 > end2 - start2) {
              script.push([
                DELETE,
                left[i]
              ]);
              ++i;
            } else {
              script.push([
                INSERT,
                right[j]
              ]);
              ++j;
            }
          }
        }
      } else {
        buildScript(start1, middle.start, start2, middle.start - middle.diag, script);
        for (var i2 = middle.start; i2 < middle.end; ++i2) {
          script.push([
            KEEP,
            left[i2]
          ]);
        }
        buildScript(middle.end, end1, middle.end - middle.diag, end2, script);
      }
    };
    var buildSnake = function (start, diag, end1, end2) {
      var end = start;
      while (end - diag < end2 && end < end1 && left[end] === right[end - diag]) {
        ++end;
      }
      return snake(start, end, diag);
    };
    var getMiddleSnake = function (start1, end1, start2, end2) {
      var m = end1 - start1;
      var n = end2 - start2;
      if (m === 0 || n === 0) {
        return null;
      }
      var delta = m - n;
      var sum = n + m;
      var offset = (sum % 2 === 0 ? sum : sum + 1) / 2;
      vDown[1 + offset] = start1;
      vUp[1 + offset] = end1 + 1;
      var d, k, i, x, y;
      for (d = 0; d <= offset; ++d) {
        for (k = -d; k <= d; k += 2) {
          i = k + offset;
          if (k === -d || k !== d && vDown[i - 1] < vDown[i + 1]) {
            vDown[i] = vDown[i + 1];
          } else {
            vDown[i] = vDown[i - 1] + 1;
          }
          x = vDown[i];
          y = x - start1 + start2 - k;
          while (x < end1 && y < end2 && left[x] === right[y]) {
            vDown[i] = ++x;
            ++y;
          }
          if (delta % 2 !== 0 && delta - d <= k && k <= delta + d) {
            if (vUp[i - delta] <= vDown[i]) {
              return buildSnake(vUp[i - delta], k + start1 - start2, end1, end2);
            }
          }
        }
        for (k = delta - d; k <= delta + d; k += 2) {
          i = k + offset - delta;
          if (k === delta - d || k !== delta + d && vUp[i + 1] <= vUp[i - 1]) {
            vUp[i] = vUp[i + 1] - 1;
          } else {
            vUp[i] = vUp[i - 1];
          }
          x = vUp[i] - 1;
          y = x - start1 + start2 - k;
          while (x >= start1 && y >= start2 && left[x] === right[y]) {
            vUp[i] = x--;
            y--;
          }
          if (delta % 2 === 0 && -d <= k && k <= d) {
            if (vUp[i] <= vDown[i + delta]) {
              return buildSnake(vUp[i], k + start1 - start2, end1, end2);
            }
          }
        }
      }
    };
    var script = [];
    buildScript(0, left.length, 0, right.length, script);
    return script;
  };
  var $_gf7th55fje5o2qtr = {
    KEEP: KEEP,
    DELETE: DELETE,
    INSERT: INSERT,
    diff: diff
  };

  var getOuterHtml = function (elm) {
    if (elm.nodeType === 1) {
      return elm.outerHTML;
    } else if (elm.nodeType === 3) {
      return $_fzh4ld1rje5o2q0t.encodeRaw(elm.data, false);
    } else if (elm.nodeType === 8) {
      return '<!--' + elm.data + '-->';
    }
    return '';
  };
  var createFragment$1 = function (html) {
    var frag, node, container;
    container = document.createElement('div');
    frag = document.createDocumentFragment();
    if (html) {
      container.innerHTML = html;
    }
    while (node = container.firstChild) {
      frag.appendChild(node);
    }
    return frag;
  };
  var insertAt = function (elm, html, index) {
    var fragment = createFragment$1(html);
    if (elm.hasChildNodes() && index < elm.childNodes.length) {
      var target = elm.childNodes[index];
      target.parentNode.insertBefore(fragment, target);
    } else {
      elm.appendChild(fragment);
    }
  };
  var removeAt = function (elm, index) {
    if (elm.hasChildNodes() && index < elm.childNodes.length) {
      var target = elm.childNodes[index];
      target.parentNode.removeChild(target);
    }
  };
  var applyDiff = function (diff, elm) {
    var index = 0;
    $_c2vc3pkje5o2puw.each(diff, function (action) {
      if (action[0] === $_gf7th55fje5o2qtr.KEEP) {
        index++;
      } else if (action[0] === $_gf7th55fje5o2qtr.INSERT) {
        insertAt(elm, action[1], index);
        index++;
      } else if (action[0] === $_gf7th55fje5o2qtr.DELETE) {
        removeAt(elm, index);
      }
    });
  };
  var read$2 = function (elm) {
    return $_c2vc3pkje5o2puw.filter($_c2vc3pkje5o2puw.map(elm.childNodes, getOuterHtml), function (item) {
      return item.length > 0;
    });
  };
  var write = function (fragments, elm) {
    var currentFragments = $_c2vc3pkje5o2puw.map(elm.childNodes, getOuterHtml);
    applyDiff($_gf7th55fje5o2qtr.diff(currentFragments, fragments), elm);
    return elm;
  };
  var $_fjm93l5eje5o2qto = {
    read: read$2,
    write: write
  };

  var hasIframes = function (html) {
    return html.indexOf('</iframe>') !== -1;
  };
  var createFragmentedLevel = function (fragments) {
    return {
      type: 'fragmented',
      fragments: fragments,
      content: '',
      bookmark: null,
      beforeBookmark: null
    };
  };
  var createCompleteLevel = function (content) {
    return {
      type: 'complete',
      fragments: null,
      content: content,
      bookmark: null,
      beforeBookmark: null
    };
  };
  var createFromEditor = function (editor) {
    var fragments, content, trimmedFragments;
    fragments = $_fjm93l5eje5o2qto.read(editor.getBody());
    trimmedFragments = $_bwcteb4je5o2pl4.bind(fragments, function (html) {
      var trimmed = $_15ih885cje5o2qt6.trimInternal(editor.serializer, html);
      return trimmed.length > 0 ? [trimmed] : [];
    });
    content = trimmedFragments.join('');
    return hasIframes(content) ? createFragmentedLevel(trimmedFragments) : createCompleteLevel(content);
  };
  var applyToEditor = function (editor, level, before) {
    if (level.type === 'fragmented') {
      $_fjm93l5eje5o2qto.write(level.fragments, editor.getBody());
    } else {
      editor.setContent(level.content, { format: 'raw' });
    }
    editor.selection.moveToBookmark(before ? level.beforeBookmark : level.bookmark);
  };
  var getLevelContent = function (level) {
    return level.type === 'fragmented' ? level.fragments.join('') : level.content;
  };
  var isEq$4 = function (level1, level2) {
    return !!level1 && !!level2 && getLevelContent(level1) === getLevelContent(level2);
  };
  var $_dv64z5bje5o2qt3 = {
    createFragmentedLevel: createFragmentedLevel,
    createCompleteLevel: createCompleteLevel,
    createFromEditor: createFromEditor,
    applyToEditor: applyToEditor,
    isEq: isEq$4
  };

  function UndoManager (editor) {
    var self = this, index = 0, data = [], beforeBookmark, isFirstTypedCharacter, locks = 0;
    var isUnlocked = function () {
      return locks === 0;
    };
    var setTyping = function (typing) {
      if (isUnlocked()) {
        self.typing = typing;
      }
    };
    var setDirty = function (state) {
      editor.setDirty(state);
    };
    var addNonTypingUndoLevel = function (e) {
      setTyping(false);
      self.add({}, e);
    };
    var endTyping = function () {
      if (self.typing) {
        setTyping(false);
        self.add();
      }
    };
    editor.on('init', function () {
      self.add();
    });
    editor.on('BeforeExecCommand', function (e) {
      var cmd = e.command;
      if (cmd !== 'Undo' && cmd !== 'Redo' && cmd !== 'mceRepaint') {
        endTyping();
        self.beforeChange();
      }
    });
    editor.on('ExecCommand', function (e) {
      var cmd = e.command;
      if (cmd !== 'Undo' && cmd !== 'Redo' && cmd !== 'mceRepaint') {
        addNonTypingUndoLevel(e);
      }
    });
    editor.on('ObjectResizeStart Cut', function () {
      self.beforeChange();
    });
    editor.on('SaveContent ObjectResized blur', addNonTypingUndoLevel);
    editor.on('DragEnd', addNonTypingUndoLevel);
    editor.on('KeyUp', function (e) {
      var keyCode = e.keyCode;
      if (e.isDefaultPrevented()) {
        return;
      }
      if (keyCode >= 33 && keyCode <= 36 || keyCode >= 37 && keyCode <= 40 || keyCode === 45 || e.ctrlKey) {
        addNonTypingUndoLevel();
        editor.nodeChanged();
      }
      if (keyCode === 46 || keyCode === 8) {
        editor.nodeChanged();
      }
      if (isFirstTypedCharacter && self.typing && $_dv64z5bje5o2qt3.isEq($_dv64z5bje5o2qt3.createFromEditor(editor), data[0]) === false) {
        if (editor.isDirty() === false) {
          setDirty(true);
          editor.fire('change', {
            level: data[0],
            lastLevel: null
          });
        }
        editor.fire('TypingUndo');
        isFirstTypedCharacter = false;
        editor.nodeChanged();
      }
    });
    editor.on('KeyDown', function (e) {
      var keyCode = e.keyCode;
      if (e.isDefaultPrevented()) {
        return;
      }
      if (keyCode >= 33 && keyCode <= 36 || keyCode >= 37 && keyCode <= 40 || keyCode === 45) {
        if (self.typing) {
          addNonTypingUndoLevel(e);
        }
        return;
      }
      var modKey = e.ctrlKey && !e.altKey || e.metaKey;
      if ((keyCode < 16 || keyCode > 20) && keyCode !== 224 && keyCode !== 91 && !self.typing && !modKey) {
        self.beforeChange();
        setTyping(true);
        self.add({}, e);
        isFirstTypedCharacter = true;
      }
    });
    editor.on('MouseDown', function (e) {
      if (self.typing) {
        addNonTypingUndoLevel(e);
      }
    });
    var isInsertReplacementText = function (event) {
      return event.inputType === 'insertReplacementText';
    };
    var isInsertTextDataNull = function (event) {
      return event.inputType === 'insertText' && event.data === null;
    };
    editor.on('input', function (e) {
      if (e.inputType && (isInsertReplacementText(e) || isInsertTextDataNull(e))) {
        addNonTypingUndoLevel(e);
      }
    });
    editor.addShortcut('meta+z', '', 'Undo');
    editor.addShortcut('meta+y,meta+shift+z', '', 'Redo');
    editor.on('AddUndo Undo Redo ClearUndos', function (e) {
      if (!e.isDefaultPrevented()) {
        editor.nodeChanged();
      }
    });
    self = {
      data: data,
      typing: false,
      beforeChange: function () {
        if (isUnlocked()) {
          beforeBookmark = $_9eyzd52kje5o2q8r.getUndoBookmark(editor.selection);
        }
      },
      add: function (level, event) {
        var i;
        var settings = editor.settings;
        var lastLevel, currentLevel;
        currentLevel = $_dv64z5bje5o2qt3.createFromEditor(editor);
        level = level || {};
        level = $_a5gegtjje5o2pum.extend(level, currentLevel);
        if (isUnlocked() === false || editor.removed) {
          return null;
        }
        lastLevel = data[index];
        if (editor.fire('BeforeAddUndo', {
            level: level,
            lastLevel: lastLevel,
            originalEvent: event
          }).isDefaultPrevented()) {
          return null;
        }
        if (lastLevel && $_dv64z5bje5o2qt3.isEq(lastLevel, level)) {
          return null;
        }
        if (data[index]) {
          data[index].beforeBookmark = beforeBookmark;
        }
        if (settings.custom_undo_redo_levels) {
          if (data.length > settings.custom_undo_redo_levels) {
            for (i = 0; i < data.length - 1; i++) {
              data[i] = data[i + 1];
            }
            data.length--;
            index = data.length;
          }
        }
        level.bookmark = $_9eyzd52kje5o2q8r.getUndoBookmark(editor.selection);
        if (index < data.length - 1) {
          data.length = index + 1;
        }
        data.push(level);
        index = data.length - 1;
        var args = {
          level: level,
          lastLevel: lastLevel,
          originalEvent: event
        };
        editor.fire('AddUndo', args);
        if (index > 0) {
          setDirty(true);
          editor.fire('change', args);
        }
        return level;
      },
      undo: function () {
        var level;
        if (self.typing) {
          self.add();
          self.typing = false;
          setTyping(false);
        }
        if (index > 0) {
          level = data[--index];
          $_dv64z5bje5o2qt3.applyToEditor(editor, level, true);
          setDirty(true);
          editor.fire('undo', { level: level });
        }
        return level;
      },
      redo: function () {
        var level;
        if (index < data.length - 1) {
          level = data[++index];
          $_dv64z5bje5o2qt3.applyToEditor(editor, level, false);
          setDirty(true);
          editor.fire('redo', { level: level });
        }
        return level;
      },
      clear: function () {
        data = [];
        index = 0;
        self.typing = false;
        self.data = data;
        editor.fire('ClearUndos');
      },
      hasUndo: function () {
        return index > 0 || self.typing && data[0] && !$_dv64z5bje5o2qt3.isEq($_dv64z5bje5o2qt3.createFromEditor(editor), data[0]);
      },
      hasRedo: function () {
        return index < data.length - 1 && !self.typing;
      },
      transact: function (callback) {
        endTyping();
        self.beforeChange();
        self.ignore(callback);
        return self.add();
      },
      ignore: function (callback) {
        try {
          locks++;
          callback();
        } finally {
          locks--;
        }
      },
      extra: function (callback1, callback2) {
        var lastLevel, bookmark;
        if (self.transact(callback1)) {
          bookmark = data[index].bookmark;
          lastLevel = data[index - 1];
          $_dv64z5bje5o2qt3.applyToEditor(editor, lastLevel, true);
          if (self.transact(callback2)) {
            data[index - 1].beforeBookmark = bookmark;
          }
        }
      }
    };
    return self;
  }

  var postProcessHooks = {};
  var filter$2 = $_c2vc3pkje5o2puw.filter;
  var each$12 = $_c2vc3pkje5o2puw.each;
  var addPostProcessHook = function (name, hook) {
    var hooks = postProcessHooks[name];
    if (!hooks) {
      postProcessHooks[name] = hooks = [];
    }
    postProcessHooks[name].push(hook);
  };
  var postProcess = function (name, editor) {
    each$12(postProcessHooks[name], function (hook) {
      hook(editor);
    });
  };
  addPostProcessHook('pre', function (editor) {
    var rng = editor.selection.getRng();
    var isPre, blocks;
    var hasPreSibling = function (pre) {
      return isPre(pre.previousSibling) && $_c2vc3pkje5o2puw.indexOf(blocks, pre.previousSibling) !== -1;
    };
    var joinPre = function (pre1, pre2) {
      DomQuery(pre2).remove();
      DomQuery(pre1).append('<br><br>').append(pre2.childNodes);
    };
    isPre = $_9owqcy1qje5o2q0e.matchNodeNames('pre');
    if (!rng.collapsed) {
      blocks = editor.selection.getSelectedBlocks();
      each$12(filter$2(filter$2(blocks, isPre), hasPreSibling), function (pre) {
        joinPre(pre.previousSibling, pre);
      });
    }
  });
  var $_6kqshy5ije5o2qu7 = { postProcess: postProcess };

  var each$13 = $_a5gegtjje5o2pum.each;
  var getEndChild = function (container, index) {
    var childNodes = container.childNodes;
    index--;
    if (index > childNodes.length - 1) {
      index = childNodes.length - 1;
    } else if (index < 0) {
      index = 0;
    }
    return childNodes[index] || container;
  };
  var walk$2 = function (dom, rng, callback) {
    var startContainer = rng.startContainer;
    var startOffset = rng.startOffset;
    var endContainer = rng.endContainer;
    var endOffset = rng.endOffset;
    var ancestor;
    var startPoint;
    var endPoint;
    var node;
    var parent;
    var siblings;
    var nodes;
    nodes = dom.select('td[data-mce-selected],th[data-mce-selected]');
    if (nodes.length > 0) {
      each$13(nodes, function (node) {
        callback([node]);
      });
      return;
    }
    var exclude = function (nodes) {
      var node;
      node = nodes[0];
      if (node.nodeType === 3 && node === startContainer && startOffset >= node.nodeValue.length) {
        nodes.splice(0, 1);
      }
      node = nodes[nodes.length - 1];
      if (endOffset === 0 && nodes.length > 0 && node === endContainer && node.nodeType === 3) {
        nodes.splice(nodes.length - 1, 1);
      }
      return nodes;
    };
    var collectSiblings = function (node, name, endNode) {
      var siblings = [];
      for (; node && node !== endNode; node = node[name]) {
        siblings.push(node);
      }
      return siblings;
    };
    var findEndPoint = function (node, root) {
      do {
        if (node.parentNode === root) {
          return node;
        }
        node = node.parentNode;
      } while (node);
    };
    var walkBoundary = function (startNode, endNode, next) {
      var siblingName = next ? 'nextSibling' : 'previousSibling';
      for (node = startNode, parent = node.parentNode; node && node !== endNode; node = parent) {
        parent = node.parentNode;
        siblings = collectSiblings(node === startNode ? node : node[siblingName], siblingName);
        if (siblings.length) {
          if (!next) {
            siblings.reverse();
          }
          callback(exclude(siblings));
        }
      }
    };
    if (startContainer.nodeType === 1 && startContainer.hasChildNodes()) {
      startContainer = startContainer.childNodes[startOffset];
    }
    if (endContainer.nodeType === 1 && endContainer.hasChildNodes()) {
      endContainer = getEndChild(endContainer, endOffset);
    }
    if (startContainer === endContainer) {
      return callback(exclude([startContainer]));
    }
    ancestor = dom.findCommonAncestor(startContainer, endContainer);
    for (node = startContainer; node; node = node.parentNode) {
      if (node === endContainer) {
        return walkBoundary(startContainer, ancestor, true);
      }
      if (node === ancestor) {
        break;
      }
    }
    for (node = endContainer; node; node = node.parentNode) {
      if (node === startContainer) {
        return walkBoundary(endContainer, ancestor);
      }
      if (node === ancestor) {
        break;
      }
    }
    startPoint = findEndPoint(startContainer, ancestor) || startContainer;
    endPoint = findEndPoint(endContainer, ancestor) || endContainer;
    walkBoundary(startContainer, startPoint, true);
    siblings = collectSiblings(startPoint === startContainer ? startPoint : startPoint.nextSibling, 'nextSibling', endPoint === endContainer ? endPoint.nextSibling : endPoint);
    if (siblings.length) {
      callback(exclude(siblings));
    }
    walkBoundary(endContainer, endPoint);
  };
  var $_1jk9p5lje5o2qv9 = { walk: walk$2 };

  var MCE_ATTR_RE = /^(src|href|style)$/;
  var each$14 = $_a5gegtjje5o2pum.each;
  var isEq$5 = $_b2hqtg3lje5o2qft.isEq;
  var isTableCell$4 = function (node) {
    return /^(TH|TD)$/.test(node.nodeName);
  };
  var getContainer = function (ed, rng, start) {
    var container, offset, lastIdx;
    container = rng[start ? 'startContainer' : 'endContainer'];
    offset = rng[start ? 'startOffset' : 'endOffset'];
    if ($_9owqcy1qje5o2q0e.isElement(container)) {
      lastIdx = container.childNodes.length - 1;
      if (!start && offset) {
        offset--;
      }
      container = container.childNodes[offset > lastIdx ? lastIdx : offset];
    }
    if ($_9owqcy1qje5o2q0e.isText(container) && start && offset >= container.nodeValue.length) {
      container = new TreeWalker(container, ed.getBody()).next() || container;
    }
    if ($_9owqcy1qje5o2q0e.isText(container) && !start && offset === 0) {
      container = new TreeWalker(container, ed.getBody()).prev() || container;
    }
    return container;
  };
  var wrap$2 = function (dom, node, name, attrs) {
    var wrapper = dom.create(name, attrs);
    node.parentNode.insertBefore(wrapper, node);
    wrapper.appendChild(node);
    return wrapper;
  };
  var matchName$1 = function (dom, node, format) {
    if (isEq$5(node, format.inline)) {
      return true;
    }
    if (isEq$5(node, format.block)) {
      return true;
    }
    if (format.selector) {
      return $_9owqcy1qje5o2q0e.isElement(node) && dom.is(node, format.selector);
    }
  };
  var isColorFormatAndAnchor = function (node, format) {
    return format.links && node.tagName === 'A';
  };
  var find$4 = function (dom, node, next, inc) {
    node = $_b2hqtg3lje5o2qft.getNonWhiteSpaceSibling(node, next, inc);
    return !node || (node.nodeName === 'BR' || dom.isBlock(node));
  };
  var removeNode$1 = function (ed, node, format) {
    var parentNode = node.parentNode;
    var rootBlockElm;
    var dom = ed.dom, forcedRootBlock = ed.settings.forced_root_block;
    if (format.block) {
      if (!forcedRootBlock) {
        if (dom.isBlock(node) && !dom.isBlock(parentNode)) {
          if (!find$4(dom, node, false) && !find$4(dom, node.firstChild, true, 1)) {
            node.insertBefore(dom.create('br'), node.firstChild);
          }
          if (!find$4(dom, node, true) && !find$4(dom, node.lastChild, false, 1)) {
            node.appendChild(dom.create('br'));
          }
        }
      } else {
        if (parentNode === dom.getRoot()) {
          if (!format.list_block || !isEq$5(node, format.list_block)) {
            each$14($_a5gegtjje5o2pum.grep(node.childNodes), function (node) {
              if ($_b2hqtg3lje5o2qft.isValid(ed, forcedRootBlock, node.nodeName.toLowerCase())) {
                if (!rootBlockElm) {
                  rootBlockElm = wrap$2(dom, node, forcedRootBlock);
                  dom.setAttribs(rootBlockElm, ed.settings.forced_root_block_attrs);
                } else {
                  rootBlockElm.appendChild(node);
                }
              } else {
                rootBlockElm = 0;
              }
            });
          }
        }
      }
    }
    if (format.selector && format.inline && !isEq$5(format.inline, node)) {
      return;
    }
    dom.remove(node, 1);
  };
  var removeFormat = function (ed, format, vars, node, compareNode) {
    var i, attrs, stylesModified;
    var dom = ed.dom;
    if (!matchName$1(dom, node, format) && !isColorFormatAndAnchor(node, format)) {
      return false;
    }
    if (format.remove !== 'all') {
      each$14(format.styles, function (value, name) {
        value = $_b2hqtg3lje5o2qft.normalizeStyleValue(dom, $_b2hqtg3lje5o2qft.replaceVars(value, vars), name);
        if (typeof name === 'number') {
          name = value;
          compareNode = 0;
        }
        if (format.remove_similar || (!compareNode || isEq$5($_b2hqtg3lje5o2qft.getStyle(dom, compareNode, name), value))) {
          dom.setStyle(node, name, '');
        }
        stylesModified = 1;
      });
      if (stylesModified && dom.getAttrib(node, 'style') === '') {
        node.removeAttribute('style');
        node.removeAttribute('data-mce-style');
      }
      each$14(format.attributes, function (value, name) {
        var valueOut;
        value = $_b2hqtg3lje5o2qft.replaceVars(value, vars);
        if (typeof name === 'number') {
          name = value;
          compareNode = 0;
        }
        if (!compareNode || isEq$5(dom.getAttrib(compareNode, name), value)) {
          if (name === 'class') {
            value = dom.getAttrib(node, name);
            if (value) {
              valueOut = '';
              each$14(value.split(/\s+/), function (cls) {
                if (/mce\-\w+/.test(cls)) {
                  valueOut += (valueOut ? ' ' : '') + cls;
                }
              });
              if (valueOut) {
                dom.setAttrib(node, name, valueOut);
                return;
              }
            }
          }
          if (name === 'class') {
            node.removeAttribute('className');
          }
          if (MCE_ATTR_RE.test(name)) {
            node.removeAttribute('data-mce-' + name);
          }
          node.removeAttribute(name);
        }
      });
      each$14(format.classes, function (value) {
        value = $_b2hqtg3lje5o2qft.replaceVars(value, vars);
        if (!compareNode || dom.hasClass(compareNode, value)) {
          dom.removeClass(node, value);
        }
      });
      attrs = dom.getAttribs(node);
      for (i = 0; i < attrs.length; i++) {
        var attrName = attrs[i].nodeName;
        if (attrName.indexOf('_') !== 0 && attrName.indexOf('data-') !== 0) {
          return false;
        }
      }
    }
    if (format.remove !== 'none') {
      removeNode$1(ed, node, format);
      return true;
    }
  };
  var findFormatRoot = function (editor, container, name, vars, similar) {
    var formatRoot;
    each$14($_b2hqtg3lje5o2qft.getParents(editor.dom, container.parentNode).reverse(), function (parent) {
      var format;
      if (!formatRoot && parent.id !== '_start' && parent.id !== '_end') {
        format = $_g32xfv3mje5o2qg1.matchNode(editor, parent, name, vars, similar);
        if (format && format.split !== false) {
          formatRoot = parent;
        }
      }
    });
    return formatRoot;
  };
  var wrapAndSplit = function (editor, formatList, formatRoot, container, target, split, format, vars) {
    var parent, clone, lastClone, firstClone, i, formatRootParent;
    var dom = editor.dom;
    if (formatRoot) {
      formatRootParent = formatRoot.parentNode;
      for (parent = container.parentNode; parent && parent !== formatRootParent; parent = parent.parentNode) {
        clone = dom.clone(parent, false);
        for (i = 0; i < formatList.length; i++) {
          if (removeFormat(editor, formatList[i], vars, clone, clone)) {
            clone = 0;
            break;
          }
        }
        if (clone) {
          if (lastClone) {
            clone.appendChild(lastClone);
          }
          if (!firstClone) {
            firstClone = clone;
          }
          lastClone = clone;
        }
      }
      if (split && (!format.mixed || !dom.isBlock(formatRoot))) {
        container = dom.split(formatRoot, container);
      }
      if (lastClone) {
        target.parentNode.insertBefore(lastClone, target);
        firstClone.appendChild(target);
      }
    }
    return container;
  };
  var remove$4 = function (ed, name, vars, node, similar) {
    var formatList = ed.formatter.get(name), format = formatList[0];
    var bookmark, rng, contentEditable = true;
    var dom = ed.dom;
    var selection = ed.selection;
    var splitToFormatRoot = function (container) {
      var formatRoot = findFormatRoot(ed, container, name, vars, similar);
      return wrapAndSplit(ed, formatList, formatRoot, container, container, true, format, vars);
    };
    var process = function (node) {
      var children, i, l, lastContentEditable, hasContentEditableState;
      if ($_9owqcy1qje5o2q0e.isElement(node) && dom.getContentEditable(node)) {
        lastContentEditable = contentEditable;
        contentEditable = dom.getContentEditable(node) === 'true';
        hasContentEditableState = true;
      }
      children = $_a5gegtjje5o2pum.grep(node.childNodes);
      if (contentEditable && !hasContentEditableState) {
        for (i = 0, l = formatList.length; i < l; i++) {
          if (removeFormat(ed, formatList[i], vars, node, node)) {
            break;
          }
        }
      }
      if (format.deep) {
        if (children.length) {
          for (i = 0, l = children.length; i < l; i++) {
            process(children[i]);
          }
          if (hasContentEditableState) {
            contentEditable = lastContentEditable;
          }
        }
      }
    };
    var unwrap = function (start) {
      var node = dom.get(start ? '_start' : '_end');
      var out = node[start ? 'firstChild' : 'lastChild'];
      if ($_83h91t2jje5o2q8n.isBookmarkNode(out)) {
        out = out[start ? 'firstChild' : 'lastChild'];
      }
      if ($_9owqcy1qje5o2q0e.isText(out) && out.data.length === 0) {
        out = start ? node.previousSibling || node.nextSibling : node.nextSibling || node.previousSibling;
      }
      dom.remove(node, true);
      return out;
    };
    var removeRngStyle = function (rng) {
      var startContainer, endContainer;
      var commonAncestorContainer = rng.commonAncestorContainer;
      rng = $_8wd5fu3kje5o2qfi.expandRng(ed, rng, formatList, true);
      if (format.split) {
        startContainer = getContainer(ed, rng, true);
        endContainer = getContainer(ed, rng);
        if (startContainer !== endContainer) {
          if (/^(TR|TH|TD)$/.test(startContainer.nodeName) && startContainer.firstChild) {
            if (startContainer.nodeName === 'TR') {
              startContainer = startContainer.firstChild.firstChild || startContainer;
            } else {
              startContainer = startContainer.firstChild || startContainer;
            }
          }
          if (commonAncestorContainer && /^T(HEAD|BODY|FOOT|R)$/.test(commonAncestorContainer.nodeName) && isTableCell$4(endContainer) && endContainer.firstChild) {
            endContainer = endContainer.firstChild || endContainer;
          }
          if (dom.isChildOf(startContainer, endContainer) && startContainer !== endContainer && !dom.isBlock(endContainer) && !isTableCell$4(startContainer) && !isTableCell$4(endContainer)) {
            startContainer = wrap$2(dom, startContainer, 'span', {
              'id': '_start',
              'data-mce-type': 'bookmark'
            });
            splitToFormatRoot(startContainer);
            startContainer = unwrap(true);
            return;
          }
          startContainer = wrap$2(dom, startContainer, 'span', {
            'id': '_start',
            'data-mce-type': 'bookmark'
          });
          endContainer = wrap$2(dom, endContainer, 'span', {
            'id': '_end',
            'data-mce-type': 'bookmark'
          });
          splitToFormatRoot(startContainer);
          splitToFormatRoot(endContainer);
          startContainer = unwrap(true);
          endContainer = unwrap();
        } else {
          startContainer = endContainer = splitToFormatRoot(startContainer);
        }
        rng.startContainer = startContainer.parentNode ? startContainer.parentNode : startContainer;
        rng.startOffset = dom.nodeIndex(startContainer);
        rng.endContainer = endContainer.parentNode ? endContainer.parentNode : endContainer;
        rng.endOffset = dom.nodeIndex(endContainer) + 1;
      }
      $_1jk9p5lje5o2qv9.walk(dom, rng, function (nodes) {
        each$14(nodes, function (node) {
          process(node);
          if ($_9owqcy1qje5o2q0e.isElement(node) && ed.dom.getStyle(node, 'text-decoration') === 'underline' && node.parentNode && $_b2hqtg3lje5o2qft.getTextDecoration(dom, node.parentNode) === 'underline') {
            removeFormat(ed, {
              deep: false,
              exact: true,
              inline: 'span',
              styles: { textDecoration: 'underline' }
            }, null, node);
          }
        });
      });
    };
    if (node) {
      if (node.nodeType) {
        rng = dom.createRng();
        rng.setStartBefore(node);
        rng.setEndAfter(node);
        removeRngStyle(rng);
      } else {
        removeRngStyle(node);
      }
      return;
    }
    if (dom.getContentEditable(selection.getNode()) === 'false') {
      node = selection.getNode();
      for (var i = 0, l = formatList.length; i < l; i++) {
        if (formatList[i].ceFalseOverride) {
          if (removeFormat(ed, formatList[i], vars, node, node)) {
            break;
          }
        }
      }
      return;
    }
    if (!selection.isCollapsed() || !format.inline || dom.select('td[data-mce-selected],th[data-mce-selected]').length) {
      bookmark = selection.getBookmark();
      removeRngStyle(selection.getRng());
      selection.moveToBookmark(bookmark);
      if (format.inline && $_g32xfv3mje5o2qg1.match(ed, name, vars, selection.getStart())) {
        $_b2hqtg3lje5o2qft.moveStart(dom, selection, selection.getRng());
      }
      ed.nodeChanged();
    } else {
      $_4066yu3jje5o2qev.removeCaretFormat(ed, name, vars, similar);
    }
  };
  var $_2afve25kje5o2qul = {
    removeFormat: removeFormat,
    remove: remove$4
  };

  var each$15 = $_a5gegtjje5o2pum.each;
  var isElementNode = function (node) {
    return node && node.nodeType === 1 && !$_83h91t2jje5o2q8n.isBookmarkNode(node) && !$_4066yu3jje5o2qev.isCaretNode(node) && !$_9owqcy1qje5o2q0e.isBogus(node);
  };
  var findElementSibling = function (node, siblingName) {
    var sibling;
    for (sibling = node; sibling; sibling = sibling[siblingName]) {
      if (sibling.nodeType === 3 && sibling.nodeValue.length !== 0) {
        return node;
      }
      if (sibling.nodeType === 1 && !$_83h91t2jje5o2q8n.isBookmarkNode(sibling)) {
        return sibling;
      }
    }
    return node;
  };
  var mergeSiblingsNodes = function (dom, prev, next) {
    var sibling, tmpSibling;
    var elementUtils = new ElementUtils(dom);
    if (prev && next) {
      prev = findElementSibling(prev, 'previousSibling');
      next = findElementSibling(next, 'nextSibling');
      if (elementUtils.compare(prev, next)) {
        for (sibling = prev.nextSibling; sibling && sibling !== next;) {
          tmpSibling = sibling;
          sibling = sibling.nextSibling;
          prev.appendChild(tmpSibling);
        }
        dom.remove(next);
        $_a5gegtjje5o2pum.each($_a5gegtjje5o2pum.grep(next.childNodes), function (node) {
          prev.appendChild(node);
        });
        return prev;
      }
    }
    return next;
  };
  var processChildElements = function (node, filter, process) {
    each$15(node.childNodes, function (node) {
      if (isElementNode(node)) {
        if (filter(node)) {
          process(node);
        }
        if (node.hasChildNodes()) {
          processChildElements(node, filter, process);
        }
      }
    });
  };
  var hasStyle = function (dom, name) {
    return $_enfhkv6je5o2ply.curry(function (name, node) {
      return !!(node && $_b2hqtg3lje5o2qft.getStyle(dom, node, name));
    }, name);
  };
  var applyStyle = function (dom, name, value) {
    return $_enfhkv6je5o2ply.curry(function (name, value, node) {
      dom.setStyle(node, name, value);
      if (node.getAttribute('style') === '') {
        node.removeAttribute('style');
      }
      unwrapEmptySpan(dom, node);
    }, name, value);
  };
  var unwrapEmptySpan = function (dom, node) {
    if (node.nodeName === 'SPAN' && dom.getAttribs(node).length === 0) {
      dom.remove(node, true);
    }
  };
  var processUnderlineAndColor = function (dom, node) {
    var textDecoration;
    if (node.nodeType === 1 && node.parentNode && node.parentNode.nodeType === 1) {
      textDecoration = $_b2hqtg3lje5o2qft.getTextDecoration(dom, node.parentNode);
      if (dom.getStyle(node, 'color') && textDecoration) {
        dom.setStyle(node, 'text-decoration', textDecoration);
      } else if (dom.getStyle(node, 'text-decoration') === textDecoration) {
        dom.setStyle(node, 'text-decoration', null);
      }
    }
  };
  var mergeUnderlineAndColor = function (dom, format, vars, node) {
    if (format.styles.color || format.styles.textDecoration) {
      $_a5gegtjje5o2pum.walk(node, $_enfhkv6je5o2ply.curry(processUnderlineAndColor, dom), 'childNodes');
      processUnderlineAndColor(dom, node);
    }
  };
  var mergeBackgroundColorAndFontSize = function (dom, format, vars, node) {
    if (format.styles && format.styles.backgroundColor) {
      processChildElements(node, hasStyle(dom, 'fontSize'), applyStyle(dom, 'backgroundColor', $_b2hqtg3lje5o2qft.replaceVars(format.styles.backgroundColor, vars)));
    }
  };
  var mergeSubSup = function (dom, format, vars, node) {
    if (format.inline === 'sub' || format.inline === 'sup') {
      processChildElements(node, hasStyle(dom, 'fontSize'), applyStyle(dom, 'fontSize', ''));
      dom.remove(dom.select(format.inline === 'sup' ? 'sub' : 'sup', node), true);
    }
  };
  var mergeSiblings = function (dom, format, vars, node) {
    if (node && format.merge_siblings !== false) {
      node = mergeSiblingsNodes(dom, $_b2hqtg3lje5o2qft.getNonWhiteSpaceSibling(node), node);
      node = mergeSiblingsNodes(dom, node, $_b2hqtg3lje5o2qft.getNonWhiteSpaceSibling(node, true));
    }
  };
  var clearChildStyles = function (dom, format, node) {
    if (format.clear_child_styles) {
      var selector = format.links ? '*:not(a)' : '*';
      each$15(dom.select(selector, node), function (node) {
        if (isElementNode(node)) {
          each$15(format.styles, function (value, name) {
            dom.setStyle(node, name, '');
          });
        }
      });
    }
  };
  var mergeWithChildren = function (editor, formatList, vars, node) {
    each$15(formatList, function (format) {
      each$15(editor.dom.select(format.inline, node), function (child) {
        if (!isElementNode(child)) {
          return;
        }
        $_2afve25kje5o2qul.removeFormat(editor, format, vars, child, format.exact ? child : null);
      });
      clearChildStyles(editor.dom, format, node);
    });
  };
  var mergeWithParents = function (editor, format, name, vars, node) {
    if ($_g32xfv3mje5o2qg1.matchNode(editor, node.parentNode, name, vars)) {
      if ($_2afve25kje5o2qul.removeFormat(editor, format, vars, node)) {
        return;
      }
    }
    if (format.merge_with_parents) {
      editor.dom.getParent(node.parentNode, function (parent) {
        if ($_g32xfv3mje5o2qg1.matchNode(editor, parent, name, vars)) {
          $_2afve25kje5o2qul.removeFormat(editor, format, vars, node);
          return true;
        }
      });
    }
  };
  var $_ddwhlm5jje5o2qua = {
    mergeWithChildren: mergeWithChildren,
    mergeUnderlineAndColor: mergeUnderlineAndColor,
    mergeBackgroundColorAndFontSize: mergeBackgroundColorAndFontSize,
    mergeSubSup: mergeSubSup,
    mergeSiblings: mergeSiblings,
    mergeWithParents: mergeWithParents
  };

  var each$16 = $_a5gegtjje5o2pum.each;
  var isElementNode$1 = function (node) {
    return node && node.nodeType === 1 && !$_83h91t2jje5o2q8n.isBookmarkNode(node) && !$_4066yu3jje5o2qev.isCaretNode(node) && !$_9owqcy1qje5o2q0e.isBogus(node);
  };
  var applyFormat = function (ed, name, vars, node) {
    var formatList = ed.formatter.get(name);
    var format = formatList[0];
    var bookmark, rng;
    var isCollapsed = !node && ed.selection.isCollapsed();
    var dom = ed.dom, selection = ed.selection;
    var setElementFormat = function (elm, fmt) {
      fmt = fmt || format;
      if (elm) {
        if (fmt.onformat) {
          fmt.onformat(elm, fmt, vars, node);
        }
        each$16(fmt.styles, function (value, name) {
          dom.setStyle(elm, name, $_b2hqtg3lje5o2qft.replaceVars(value, vars));
        });
        if (fmt.styles) {
          var styleVal = dom.getAttrib(elm, 'style');
          if (styleVal) {
            elm.setAttribute('data-mce-style', styleVal);
          }
        }
        each$16(fmt.attributes, function (value, name) {
          dom.setAttrib(elm, name, $_b2hqtg3lje5o2qft.replaceVars(value, vars));
        });
        each$16(fmt.classes, function (value) {
          value = $_b2hqtg3lje5o2qft.replaceVars(value, vars);
          if (!dom.hasClass(elm, value)) {
            dom.addClass(elm, value);
          }
        });
      }
    };
    var applyNodeStyle = function (formatList, node) {
      var found = false;
      if (!format.selector) {
        return false;
      }
      each$16(formatList, function (format) {
        if ('collapsed' in format && format.collapsed !== isCollapsed) {
          return;
        }
        if (dom.is(node, format.selector) && !$_4066yu3jje5o2qev.isCaretNode(node)) {
          setElementFormat(node, format);
          found = true;
          return false;
        }
      });
      return found;
    };
    var applyRngStyle = function (dom, rng, bookmark, nodeSpecific) {
      var newWrappers = [];
      var wrapName, wrapElm, contentEditable = true;
      wrapName = format.inline || format.block;
      wrapElm = dom.create(wrapName);
      setElementFormat(wrapElm);
      $_1jk9p5lje5o2qv9.walk(dom, rng, function (nodes) {
        var currentWrapElm;
        var process = function (node) {
          var nodeName, parentName, hasContentEditableState, lastContentEditable;
          lastContentEditable = contentEditable;
          nodeName = node.nodeName.toLowerCase();
          parentName = node.parentNode.nodeName.toLowerCase();
          if (node.nodeType === 1 && dom.getContentEditable(node)) {
            lastContentEditable = contentEditable;
            contentEditable = dom.getContentEditable(node) === 'true';
            hasContentEditableState = true;
          }
          if ($_b2hqtg3lje5o2qft.isEq(nodeName, 'br')) {
            currentWrapElm = 0;
            if (format.block) {
              dom.remove(node);
            }
            return;
          }
          if (format.wrapper && $_g32xfv3mje5o2qg1.matchNode(ed, node, name, vars)) {
            currentWrapElm = 0;
            return;
          }
          if (contentEditable && !hasContentEditableState && format.block && !format.wrapper && $_b2hqtg3lje5o2qft.isTextBlock(ed, nodeName) && $_b2hqtg3lje5o2qft.isValid(ed, parentName, wrapName)) {
            node = dom.rename(node, wrapName);
            setElementFormat(node);
            newWrappers.push(node);
            currentWrapElm = 0;
            return;
          }
          if (format.selector) {
            var found = applyNodeStyle(formatList, node);
            if (!format.inline || found) {
              currentWrapElm = 0;
              return;
            }
          }
          if (contentEditable && !hasContentEditableState && $_b2hqtg3lje5o2qft.isValid(ed, wrapName, nodeName) && $_b2hqtg3lje5o2qft.isValid(ed, parentName, wrapName) && !(!nodeSpecific && node.nodeType === 3 && node.nodeValue.length === 1 && node.nodeValue.charCodeAt(0) === 65279) && !$_4066yu3jje5o2qev.isCaretNode(node) && (!format.inline || !dom.isBlock(node))) {
            if (!currentWrapElm) {
              currentWrapElm = dom.clone(wrapElm, false);
              node.parentNode.insertBefore(currentWrapElm, node);
              newWrappers.push(currentWrapElm);
            }
            currentWrapElm.appendChild(node);
          } else {
            currentWrapElm = 0;
            each$16($_a5gegtjje5o2pum.grep(node.childNodes), process);
            if (hasContentEditableState) {
              contentEditable = lastContentEditable;
            }
            currentWrapElm = 0;
          }
        };
        each$16(nodes, process);
      });
      if (format.links === true) {
        each$16(newWrappers, function (node) {
          var process = function (node) {
            if (node.nodeName === 'A') {
              setElementFormat(node, format);
            }
            each$16($_a5gegtjje5o2pum.grep(node.childNodes), process);
          };
          process(node);
        });
      }
      each$16(newWrappers, function (node) {
        var childCount;
        var getChildCount = function (node) {
          var count = 0;
          each$16(node.childNodes, function (node) {
            if (!$_b2hqtg3lje5o2qft.isWhiteSpaceNode(node) && !$_83h91t2jje5o2q8n.isBookmarkNode(node)) {
              count++;
            }
          });
          return count;
        };
        var getChildElementNode = function (root) {
          var child = false;
          each$16(root.childNodes, function (node) {
            if (isElementNode$1(node)) {
              child = node;
              return false;
            }
          });
          return child;
        };
        var mergeStyles = function (node) {
          var child, clone;
          child = getChildElementNode(node);
          if (child && !$_83h91t2jje5o2q8n.isBookmarkNode(child) && $_g32xfv3mje5o2qg1.matchName(dom, child, format)) {
            clone = dom.clone(child, false);
            setElementFormat(clone);
            dom.replace(clone, node, true);
            dom.remove(child, 1);
          }
          return clone || node;
        };
        childCount = getChildCount(node);
        if ((newWrappers.length > 1 || !dom.isBlock(node)) && childCount === 0) {
          dom.remove(node, 1);
          return;
        }
        if (format.inline || format.wrapper) {
          if (!format.exact && childCount === 1) {
            node = mergeStyles(node);
          }
          $_ddwhlm5jje5o2qua.mergeWithChildren(ed, formatList, vars, node);
          $_ddwhlm5jje5o2qua.mergeWithParents(ed, format, name, vars, node);
          $_ddwhlm5jje5o2qua.mergeBackgroundColorAndFontSize(dom, format, vars, node);
          $_ddwhlm5jje5o2qua.mergeSubSup(dom, format, vars, node);
          $_ddwhlm5jje5o2qua.mergeSiblings(dom, format, vars, node);
        }
      });
    };
    if (dom.getContentEditable(selection.getNode()) === 'false') {
      node = selection.getNode();
      for (var i = 0, l = formatList.length; i < l; i++) {
        if (formatList[i].ceFalseOverride && dom.is(node, formatList[i].selector)) {
          setElementFormat(node, formatList[i]);
          return;
        }
      }
      return;
    }
    if (format) {
      if (node) {
        if (node.nodeType) {
          if (!applyNodeStyle(formatList, node)) {
            rng = dom.createRng();
            rng.setStartBefore(node);
            rng.setEndAfter(node);
            applyRngStyle(dom, $_8wd5fu3kje5o2qfi.expandRng(ed, rng, formatList), null, true);
          }
        } else {
          applyRngStyle(dom, node, null, true);
        }
      } else {
        if (!isCollapsed || !format.inline || dom.select('td[data-mce-selected],th[data-mce-selected]').length) {
          var curSelNode = ed.selection.getNode();
          if (!ed.settings.forced_root_block && formatList[0].defaultBlock && !dom.getParent(curSelNode, dom.isBlock)) {
            applyFormat(ed, formatList[0].defaultBlock);
          }
          ed.selection.setRng($_8xjhg22vje5o2qak.normalize(ed.selection.getRng()));
          bookmark = selection.getBookmark();
          applyRngStyle(dom, $_8wd5fu3kje5o2qfi.expandRng(ed, selection.getRng(), formatList), bookmark);
          if (format.styles) {
            $_ddwhlm5jje5o2qua.mergeUnderlineAndColor(dom, format, vars, curSelNode);
          }
          selection.moveToBookmark(bookmark);
          $_b2hqtg3lje5o2qft.moveStart(dom, selection, selection.getRng());
          ed.nodeChanged();
        } else {
          $_4066yu3jje5o2qev.applyCaretFormat(ed, name, vars);
        }
      }
      $_6kqshy5ije5o2qu7.postProcess(name, ed);
    }
  };
  var $_a1bxau5hje5o2qu0 = { applyFormat: applyFormat };

  var each$17 = $_a5gegtjje5o2pum.each;
  var setup$3 = function (formatChangeData, editor) {
    var currentFormats = {};
    formatChangeData.set({});
    editor.on('NodeChange', function (e) {
      var parents = $_b2hqtg3lje5o2qft.getParents(editor.dom, e.element);
      var matchedFormats = {};
      parents = $_a5gegtjje5o2pum.grep(parents, function (node) {
        return node.nodeType === 1 && !node.getAttribute('data-mce-bogus');
      });
      each$17(formatChangeData.get(), function (callbacks, format) {
        each$17(parents, function (node) {
          if (editor.formatter.matchNode(node, format, {}, callbacks.similar)) {
            if (!currentFormats[format]) {
              each$17(callbacks, function (callback) {
                callback(true, {
                  node: node,
                  format: format,
                  parents: parents
                });
              });
              currentFormats[format] = callbacks;
            }
            matchedFormats[format] = callbacks;
            return false;
          }
          if ($_g32xfv3mje5o2qg1.matchesUnInheritedFormatSelector(editor, node, format)) {
            return false;
          }
        });
      });
      each$17(currentFormats, function (callbacks, format) {
        if (!matchedFormats[format]) {
          delete currentFormats[format];
          each$17(callbacks, function (callback) {
            callback(false, {
              node: e.element,
              format: format,
              parents: parents
            });
          });
        }
      });
    });
  };
  var addListeners = function (formatChangeData, formats, callback, similar) {
    var formatChangeItems = formatChangeData.get();
    each$17(formats.split(','), function (format) {
      if (!formatChangeItems[format]) {
        formatChangeItems[format] = [];
        formatChangeItems[format].similar = similar;
      }
      formatChangeItems[format].push(callback);
    });
    formatChangeData.set(formatChangeItems);
  };
  var formatChanged = function (editor, formatChangeState, formats, callback, similar) {
    if (formatChangeState.get() === null) {
      setup$3(formatChangeState, editor);
    }
    addListeners(formatChangeState, formats, callback, similar);
  };
  var $_eb5vf85mje5o2qvd = { formatChanged: formatChanged };

  var get$4 = function (dom) {
    var formats = {
      valigntop: [{
          selector: 'td,th',
          styles: { verticalAlign: 'top' }
        }],
      valignmiddle: [{
          selector: 'td,th',
          styles: { verticalAlign: 'middle' }
        }],
      valignbottom: [{
          selector: 'td,th',
          styles: { verticalAlign: 'bottom' }
        }],
      alignleft: [
        {
          selector: 'figure.image',
          collapsed: false,
          classes: 'align-left',
          ceFalseOverride: true,
          preview: 'font-family font-size'
        },
        {
          selector: 'figure,p,h1,h2,h3,h4,h5,h6,td,th,tr,div,ul,ol,li',
          styles: { textAlign: 'left' },
          inherit: false,
          preview: false,
          defaultBlock: 'div'
        },
        {
          selector: 'img,table',
          collapsed: false,
          styles: { float: 'left' },
          preview: 'font-family font-size'
        }
      ],
      aligncenter: [
        {
          selector: 'figure,p,h1,h2,h3,h4,h5,h6,td,th,tr,div,ul,ol,li',
          styles: { textAlign: 'center' },
          inherit: false,
          preview: 'font-family font-size',
          defaultBlock: 'div'
        },
        {
          selector: 'figure.image',
          collapsed: false,
          classes: 'align-center',
          ceFalseOverride: true,
          preview: 'font-family font-size'
        },
        {
          selector: 'img',
          collapsed: false,
          styles: {
            display: 'block',
            marginLeft: 'auto',
            marginRight: 'auto'
          },
          preview: false
        },
        {
          selector: 'table',
          collapsed: false,
          styles: {
            marginLeft: 'auto',
            marginRight: 'auto'
          },
          preview: 'font-family font-size'
        }
      ],
      alignright: [
        {
          selector: 'figure.image',
          collapsed: false,
          classes: 'align-right',
          ceFalseOverride: true,
          preview: 'font-family font-size'
        },
        {
          selector: 'figure,p,h1,h2,h3,h4,h5,h6,td,th,tr,div,ul,ol,li',
          styles: { textAlign: 'right' },
          inherit: false,
          preview: 'font-family font-size',
          defaultBlock: 'div'
        },
        {
          selector: 'img,table',
          collapsed: false,
          styles: { float: 'right' },
          preview: 'font-family font-size'
        }
      ],
      alignjustify: [{
          selector: 'figure,p,h1,h2,h3,h4,h5,h6,td,th,tr,div,ul,ol,li',
          styles: { textAlign: 'justify' },
          inherit: false,
          defaultBlock: 'div',
          preview: 'font-family font-size'
        }],
      bold: [
        {
          inline: 'strong',
          remove: 'all'
        },
        {
          inline: 'span',
          styles: { fontWeight: 'bold' }
        },
        {
          inline: 'b',
          remove: 'all'
        }
      ],
      italic: [
        {
          inline: 'em',
          remove: 'all'
        },
        {
          inline: 'span',
          styles: { fontStyle: 'italic' }
        },
        {
          inline: 'i',
          remove: 'all'
        }
      ],
      underline: [
        {
          inline: 'span',
          styles: { textDecoration: 'underline' },
          exact: true
        },
        {
          inline: 'u',
          remove: 'all'
        }
      ],
      strikethrough: [
        {
          inline: 'span',
          styles: { textDecoration: 'line-through' },
          exact: true
        },
        {
          inline: 'strike',
          remove: 'all'
        }
      ],
      forecolor: {
        inline: 'span',
        styles: { color: '%value' },
        links: true,
        remove_similar: true,
        clear_child_styles: true
      },
      hilitecolor: {
        inline: 'span',
        styles: { backgroundColor: '%value' },
        links: true,
        remove_similar: true,
        clear_child_styles: true
      },
      fontname: {
        inline: 'span',
        toggle: false,
        styles: { fontFamily: '%value' },
        clear_child_styles: true
      },
      fontsize: {
        inline: 'span',
        toggle: false,
        styles: { fontSize: '%value' },
        clear_child_styles: true
      },
      fontsize_class: {
        inline: 'span',
        attributes: { class: '%value' }
      },
      blockquote: {
        block: 'blockquote',
        wrapper: 1,
        remove: 'all'
      },
      subscript: { inline: 'sub' },
      superscript: { inline: 'sup' },
      code: { inline: 'code' },
      link: {
        inline: 'a',
        selector: 'a',
        remove: 'all',
        split: true,
        deep: true,
        onmatch: function () {
          return true;
        },
        onformat: function (elm, fmt, vars) {
          $_a5gegtjje5o2pum.each(vars, function (value, key) {
            dom.setAttrib(elm, key, value);
          });
        }
      },
      removeformat: [
        {
          selector: 'b,strong,em,i,font,u,strike,sub,sup,dfn,code,samp,kbd,var,cite,mark,q,del,ins',
          remove: 'all',
          split: true,
          expand: false,
          block_expand: true,
          deep: true
        },
        {
          selector: 'span',
          attributes: [
            'style',
            'class'
          ],
          remove: 'empty',
          split: true,
          expand: false,
          deep: true
        },
        {
          selector: '*',
          attributes: [
            'style',
            'class'
          ],
          split: false,
          expand: false,
          deep: true
        }
      ]
    };
    $_a5gegtjje5o2pum.each('p h1 h2 h3 h4 h5 h6 div address pre div dt dd samp'.split(/\s/), function (name) {
      formats[name] = {
        block: name,
        remove: 'all'
      };
    });
    return formats;
  };
  var $_1t175j5oje5o2qvi = { get: get$4 };

  function FormatRegistry (editor) {
    var formats = {};
    var get = function (name) {
      return name ? formats[name] : formats;
    };
    var register = function (name, format) {
      if (name) {
        if (typeof name !== 'string') {
          $_a5gegtjje5o2pum.each(name, function (format, name) {
            register(name, format);
          });
        } else {
          format = format.length ? format : [format];
          $_a5gegtjje5o2pum.each(format, function (format) {
            if (typeof format.deep === 'undefined') {
              format.deep = !format.selector;
            }
            if (typeof format.split === 'undefined') {
              format.split = !format.selector || format.inline;
            }
            if (typeof format.remove === 'undefined' && format.selector && !format.inline) {
              format.remove = 'none';
            }
            if (format.selector && format.inline) {
              format.mixed = true;
              format.block_expand = true;
            }
            if (typeof format.classes === 'string') {
              format.classes = format.classes.split(/\s+/);
            }
          });
          formats[name] = format;
        }
      }
    };
    var unregister = function (name) {
      if (name && formats[name]) {
        delete formats[name];
      }
      return formats;
    };
    register($_1t175j5oje5o2qvi.get(editor.dom));
    register(editor.settings.formats);
    return {
      get: get,
      register: register,
      unregister: unregister
    };
  }

  var each$18 = $_a5gegtjje5o2pum.each;
  var dom = DOMUtils.DOM;
  var parsedSelectorToHtml = function (ancestry, editor) {
    var elm, item, fragment;
    var schema = editor && editor.schema || Schema({});
    var decorate = function (elm, item) {
      if (item.classes.length) {
        dom.addClass(elm, item.classes.join(' '));
      }
      dom.setAttribs(elm, item.attrs);
    };
    var createElement = function (sItem) {
      var elm;
      item = typeof sItem === 'string' ? {
        name: sItem,
        classes: [],
        attrs: {}
      } : sItem;
      elm = dom.create(item.name);
      decorate(elm, item);
      return elm;
    };
    var getRequiredParent = function (elm, candidate) {
      var name = typeof elm !== 'string' ? elm.nodeName.toLowerCase() : elm;
      var elmRule = schema.getElementRule(name);
      var parentsRequired = elmRule && elmRule.parentsRequired;
      if (parentsRequired && parentsRequired.length) {
        return candidate && $_a5gegtjje5o2pum.inArray(parentsRequired, candidate) !== -1 ? candidate : parentsRequired[0];
      } else {
        return false;
      }
    };
    var wrapInHtml = function (elm, ancestry, siblings) {
      var parent, parentCandidate, parentRequired;
      var ancestor = ancestry.length > 0 && ancestry[0];
      var ancestorName = ancestor && ancestor.name;
      parentRequired = getRequiredParent(elm, ancestorName);
      if (parentRequired) {
        if (ancestorName === parentRequired) {
          parentCandidate = ancestry[0];
          ancestry = ancestry.slice(1);
        } else {
          parentCandidate = parentRequired;
        }
      } else if (ancestor) {
        parentCandidate = ancestry[0];
        ancestry = ancestry.slice(1);
      } else if (!siblings) {
        return elm;
      }
      if (parentCandidate) {
        parent = createElement(parentCandidate);
        parent.appendChild(elm);
      }
      if (siblings) {
        if (!parent) {
          parent = dom.create('div');
          parent.appendChild(elm);
        }
        $_a5gegtjje5o2pum.each(siblings, function (sibling) {
          var siblingElm = createElement(sibling);
          parent.insertBefore(siblingElm, elm);
        });
      }
      return wrapInHtml(parent, ancestry, parentCandidate && parentCandidate.siblings);
    };
    if (ancestry && ancestry.length) {
      item = ancestry[0];
      elm = createElement(item);
      fragment = dom.create('div');
      fragment.appendChild(wrapInHtml(elm, ancestry.slice(1), item.siblings));
      return fragment;
    } else {
      return '';
    }
  };
  var selectorToHtml = function (selector, editor) {
    return parsedSelectorToHtml(parseSelector(selector), editor);
  };
  var parseSelectorItem = function (item) {
    var tagName;
    var obj = {
      classes: [],
      attrs: {}
    };
    item = obj.selector = $_a5gegtjje5o2pum.trim(item);
    if (item !== '*') {
      tagName = item.replace(/(?:([#\.]|::?)([\w\-]+)|(\[)([^\]]+)\]?)/g, function ($0, $1, $2, $3, $4) {
        switch ($1) {
        case '#':
          obj.attrs.id = $2;
          break;
        case '.':
          obj.classes.push($2);
          break;
        case ':':
          if ($_a5gegtjje5o2pum.inArray('checked disabled enabled read-only required'.split(' '), $2) !== -1) {
            obj.attrs[$2] = $2;
          }
          break;
        }
        if ($3 === '[') {
          var m = $4.match(/([\w\-]+)(?:\=\"([^\"]+))?/);
          if (m) {
            obj.attrs[m[1]] = m[2];
          }
        }
        return '';
      });
    }
    obj.name = tagName || 'div';
    return obj;
  };
  var parseSelector = function (selector) {
    if (!selector || typeof selector !== 'string') {
      return [];
    }
    selector = selector.split(/\s*,\s*/)[0];
    selector = selector.replace(/\s*(~\+|~|\+|>)\s*/g, '$1');
    return $_a5gegtjje5o2pum.map(selector.split(/(?:>|\s+(?![^\[\]]+\]))/), function (item) {
      var siblings = $_a5gegtjje5o2pum.map(item.split(/(?:~\+|~|\+)/), parseSelectorItem);
      var obj = siblings.pop();
      if (siblings.length) {
        obj.siblings = siblings;
      }
      return obj;
    }).reverse();
  };
  var getCssText = function (editor, format) {
    var name, previewFrag, previewElm, items;
    var previewCss = '', parentFontSize, previewStyles;
    previewStyles = editor.settings.preview_styles;
    if (previewStyles === false) {
      return '';
    }
    if (typeof previewStyles !== 'string') {
      previewStyles = 'font-family font-size font-weight font-style text-decoration ' + 'text-transform color background-color border border-radius outline text-shadow';
    }
    var removeVars = function (val) {
      return val.replace(/%(\w+)/g, '');
    };
    if (typeof format === 'string') {
      format = editor.formatter.get(format);
      if (!format) {
        return;
      }
      format = format[0];
    }
    if ('preview' in format) {
      previewStyles = format.preview;
      if (previewStyles === false) {
        return '';
      }
    }
    name = format.block || format.inline || 'span';
    items = parseSelector(format.selector);
    if (items.length) {
      if (!items[0].name) {
        items[0].name = name;
      }
      name = format.selector;
      previewFrag = parsedSelectorToHtml(items, editor);
    } else {
      previewFrag = parsedSelectorToHtml([name], editor);
    }
    previewElm = dom.select(name, previewFrag)[0] || previewFrag.firstChild;
    each$18(format.styles, function (value, name) {
      value = removeVars(value);
      if (value) {
        dom.setStyle(previewElm, name, value);
      }
    });
    each$18(format.attributes, function (value, name) {
      value = removeVars(value);
      if (value) {
        dom.setAttrib(previewElm, name, value);
      }
    });
    each$18(format.classes, function (value) {
      value = removeVars(value);
      if (!dom.hasClass(previewElm, value)) {
        dom.addClass(previewElm, value);
      }
    });
    editor.fire('PreviewFormats');
    dom.setStyles(previewFrag, {
      position: 'absolute',
      left: -65535
    });
    editor.getBody().appendChild(previewFrag);
    parentFontSize = dom.getStyle(editor.getBody(), 'fontSize', true);
    parentFontSize = /px$/.test(parentFontSize) ? parseInt(parentFontSize, 10) : 0;
    each$18(previewStyles.split(' '), function (name) {
      var value = dom.getStyle(previewElm, name, true);
      if (name === 'background-color' && /transparent|rgba\s*\([^)]+,\s*0\)/.test(value)) {
        value = dom.getStyle(editor.getBody(), name, true);
        if (dom.toHex(value).toLowerCase() === '#ffffff') {
          return;
        }
      }
      if (name === 'color') {
        if (dom.toHex(value).toLowerCase() === '#000000') {
          return;
        }
      }
      if (name === 'font-size') {
        if (/em|%$/.test(value)) {
          if (parentFontSize === 0) {
            return;
          }
          value = parseFloat(value) / (/%$/.test(value) ? 100 : 1);
          value = value * parentFontSize + 'px';
        }
      }
      if (name === 'border' && value) {
        previewCss += 'padding:0 2px;';
      }
      previewCss += name + ':' + value + ';';
    });
    editor.fire('AfterPreviewFormats');
    dom.remove(previewFrag);
    return previewCss;
  };
  var $_f93fum5pje5o2qvn = {
    getCssText: getCssText,
    parseSelector: parseSelector,
    selectorToHtml: selectorToHtml
  };

  var toggle = function (editor, formats, name, vars, node) {
    var fmt = formats.get(name);
    if ($_g32xfv3mje5o2qg1.match(editor, name, vars, node) && (!('toggle' in fmt[0]) || fmt[0].toggle)) {
      $_2afve25kje5o2qul.remove(editor, name, vars, node);
    } else {
      $_a1bxau5hje5o2qu0.applyFormat(editor, name, vars, node);
    }
  };
  var $_bzwbov5qje5o2qvu = { toggle: toggle };

  var setup$4 = function (editor) {
    editor.addShortcut('meta+b', '', 'Bold');
    editor.addShortcut('meta+i', '', 'Italic');
    editor.addShortcut('meta+u', '', 'Underline');
    for (var i = 1; i <= 6; i++) {
      editor.addShortcut('access+' + i, '', [
        'FormatBlock',
        false,
        'h' + i
      ]);
    }
    editor.addShortcut('access+7', '', [
      'FormatBlock',
      false,
      'p'
    ]);
    editor.addShortcut('access+8', '', [
      'FormatBlock',
      false,
      'div'
    ]);
    editor.addShortcut('access+9', '', [
      'FormatBlock',
      false,
      'address'
    ]);
  };
  var $_2qm5rd5rje5o2qvv = { setup: setup$4 };

  function Formatter (editor) {
    var formats = FormatRegistry(editor);
    var formatChangeState = Cell(null);
    $_2qm5rd5rje5o2qvv.setup(editor);
    $_4066yu3jje5o2qev.setup(editor);
    return {
      get: formats.get,
      register: formats.register,
      unregister: formats.unregister,
      apply: $_enfhkv6je5o2ply.curry($_a1bxau5hje5o2qu0.applyFormat, editor),
      remove: $_enfhkv6je5o2ply.curry($_2afve25kje5o2qul.remove, editor),
      toggle: $_enfhkv6je5o2ply.curry($_bzwbov5qje5o2qvu.toggle, editor, formats),
      match: $_enfhkv6je5o2ply.curry($_g32xfv3mje5o2qg1.match, editor),
      matchAll: $_enfhkv6je5o2ply.curry($_g32xfv3mje5o2qg1.matchAll, editor),
      matchNode: $_enfhkv6je5o2ply.curry($_g32xfv3mje5o2qg1.matchNode, editor),
      canApply: $_enfhkv6je5o2ply.curry($_g32xfv3mje5o2qg1.canApply, editor),
      formatChanged: $_enfhkv6je5o2ply.curry($_eb5vf85mje5o2qvd.formatChanged, editor, formatChangeState),
      getCssText: $_enfhkv6je5o2ply.curry($_f93fum5pje5o2qvn.getCssText, editor)
    };
  }

  var shallow = function (old, nu) {
    return nu;
  };
  var deep = function (old, nu) {
    var bothObjects = $_be3q8w12je5o2pxj.isObject(old) && $_be3q8w12je5o2pxj.isObject(nu);
    return bothObjects ? deepMerge(old, nu) : nu;
  };
  var baseMerge = function (merger) {
    return function () {
      var objects = new Array(arguments.length);
      for (var i = 0; i < objects.length; i++)
        objects[i] = arguments[i];
      if (objects.length === 0)
        throw new Error('Can\'t merge zero objects');
      var ret = {};
      for (var j = 0; j < objects.length; j++) {
        var curObject = objects[j];
        for (var key in curObject)
          if (curObject.hasOwnProperty(key)) {
            ret[key] = merger(ret[key], curObject[key]);
          }
      }
      return ret;
    };
  };
  var deepMerge = baseMerge(deep);
  var merge = baseMerge(shallow);
  var $_atjucy5uje5o2qw2 = {
    deepMerge: deepMerge,
    merge: merge
  };

  var firePreProcess = function (editor, args) {
    return editor.fire('PreProcess', args);
  };
  var firePostProcess = function (editor, args) {
    return editor.fire('PostProcess', args);
  };
  var fireRemove = function (editor) {
    return editor.fire('remove');
  };
  var $_d6jl355vje5o2qw5 = {
    firePreProcess: firePreProcess,
    firePostProcess: firePostProcess,
    fireRemove: fireRemove
  };

  var register = function (htmlParser, settings, dom) {
    htmlParser.addAttributeFilter('data-mce-tabindex', function (nodes, name) {
      var i = nodes.length, node;
      while (i--) {
        node = nodes[i];
        node.attr('tabindex', node.attributes.map['data-mce-tabindex']);
        node.attr(name, null);
      }
    });
    htmlParser.addAttributeFilter('src,href,style', function (nodes, name) {
      var i = nodes.length, node, value;
      var internalName = 'data-mce-' + name;
      var urlConverter = settings.url_converter;
      var urlConverterScope = settings.url_converter_scope;
      while (i--) {
        node = nodes[i];
        value = node.attributes.map[internalName];
        if (value !== undefined) {
          node.attr(name, value.length > 0 ? value : null);
          node.attr(internalName, null);
        } else {
          value = node.attributes.map[name];
          if (name === 'style') {
            value = dom.serializeStyle(dom.parseStyle(value), node.name);
          } else if (urlConverter) {
            value = urlConverter.call(urlConverterScope, value, name, node.name);
          }
          node.attr(name, value.length > 0 ? value : null);
        }
      }
    });
    htmlParser.addAttributeFilter('class', function (nodes) {
      var i = nodes.length, node, value;
      while (i--) {
        node = nodes[i];
        value = node.attr('class');
        if (value) {
          value = node.attr('class').replace(/(?:^|\s)mce-item-\w+(?!\S)/g, '');
          node.attr('class', value.length > 0 ? value : null);
        }
      }
    });
    htmlParser.addAttributeFilter('data-mce-type', function (nodes, name, args) {
      var i = nodes.length, node;
      while (i--) {
        node = nodes[i];
        if (node.attributes.map['data-mce-type'] === 'bookmark' && !args.cleanup) {
          node.remove();
        }
      }
    });
    htmlParser.addNodeFilter('noscript', function (nodes) {
      var i = nodes.length, node;
      while (i--) {
        node = nodes[i].firstChild;
        if (node) {
          node.value = $_fzh4ld1rje5o2q0t.decode(node.value);
        }
      }
    });
    htmlParser.addNodeFilter('script,style', function (nodes, name) {
      var i = nodes.length, node, value, type;
      var trim = function (value) {
        return value.replace(/(<!--\[CDATA\[|\]\]-->)/g, '\n').replace(/^[\r\n]*|[\r\n]*$/g, '').replace(/^\s*((<!--)?(\s*\/\/)?\s*<!\[CDATA\[|(<!--\s*)?\/\*\s*<!\[CDATA\[\s*\*\/|(\/\/)?\s*<!--|\/\*\s*<!--\s*\*\/)\s*[\r\n]*/gi, '').replace(/\s*(\/\*\s*\]\]>\s*\*\/(-->)?|\s*\/\/\s*\]\]>(-->)?|\/\/\s*(-->)?|\]\]>|\/\*\s*-->\s*\*\/|\s*-->\s*)\s*$/g, '');
      };
      while (i--) {
        node = nodes[i];
        value = node.firstChild ? node.firstChild.value : '';
        if (name === 'script') {
          type = node.attr('type');
          if (type) {
            node.attr('type', type === 'mce-no/type' ? null : type.replace(/^mce\-/, ''));
          }
          if (settings.element_format === 'xhtml' && value.length > 0) {
            node.firstChild.value = '// <![CDATA[\n' + trim(value) + '\n// ]]>';
          }
        } else {
          if (settings.element_format === 'xhtml' && value.length > 0) {
            node.firstChild.value = '<!--\n' + trim(value) + '\n-->';
          }
        }
      }
    });
    htmlParser.addNodeFilter('#comment', function (nodes) {
      var i = nodes.length, node;
      while (i--) {
        node = nodes[i];
        if (node.value.indexOf('[CDATA[') === 0) {
          node.name = '#cdata';
          node.type = 4;
          node.value = node.value.replace(/^\[CDATA\[|\]\]$/g, '');
        } else if (node.value.indexOf('mce:protected ') === 0) {
          node.name = '#text';
          node.type = 3;
          node.raw = true;
          node.value = unescape(node.value).substr(14);
        }
      }
    });
    htmlParser.addNodeFilter('xml:namespace,input', function (nodes, name) {
      var i = nodes.length, node;
      while (i--) {
        node = nodes[i];
        if (node.type === 7) {
          node.remove();
        } else if (node.type === 1) {
          if (name === 'input' && !('type' in node.attributes.map)) {
            node.attr('type', 'text');
          }
        }
      }
    });
    htmlParser.addAttributeFilter('data-mce-type', function (nodes) {
      $_bwcteb4je5o2pl4.each(nodes, function (node) {
        if (node.attr('data-mce-type') === 'format-caret') {
          if (node.isEmpty(htmlParser.schema.getNonEmptyElements())) {
            node.remove();
          } else {
            node.unwrap();
          }
        }
      });
    });
    htmlParser.addAttributeFilter('data-mce-src,data-mce-href,data-mce-style,' + 'data-mce-selected,data-mce-expando,' + 'data-mce-type,data-mce-resize', function (nodes, name) {
      var i = nodes.length;
      while (i--) {
        nodes[i].attr(name, null);
      }
    });
  };
  var trimTrailingBr = function (rootNode) {
    var brNode1, brNode2;
    var isBr = function (node) {
      return node && node.name === 'br';
    };
    brNode1 = rootNode.lastChild;
    if (isBr(brNode1)) {
      brNode2 = brNode1.prev;
      if (isBr(brNode2)) {
        brNode1.remove();
        brNode2.remove();
      }
    }
  };
  var $_97epr95wje5o2qw7 = {
    register: register,
    trimTrailingBr: trimTrailingBr
  };

  var preProcess = function (editor, node, args) {
    var impl, doc, oldDoc;
    var dom = editor.dom;
    node = node.cloneNode(true);
    impl = document.implementation;
    if (impl.createHTMLDocument) {
      doc = impl.createHTMLDocument('');
      $_a5gegtjje5o2pum.each(node.nodeName === 'BODY' ? node.childNodes : [node], function (node) {
        doc.body.appendChild(doc.importNode(node, true));
      });
      if (node.nodeName !== 'BODY') {
        node = doc.body.firstChild;
      } else {
        node = doc.body;
      }
      oldDoc = dom.doc;
      dom.doc = doc;
    }
    $_d6jl355vje5o2qw5.firePreProcess(editor, $_atjucy5uje5o2qw2.merge(args, { node: node }));
    if (oldDoc) {
      dom.doc = oldDoc;
    }
    return node;
  };
  var shouldFireEvent = function (editor, args) {
    return editor && editor.hasEventListeners('PreProcess') && !args.no_events;
  };
  var process = function (editor, node, args) {
    return shouldFireEvent(editor, args) ? preProcess(editor, node, args) : node;
  };
  var $_70hszr5xje5o2qwd = { process: process };

  var removeAttrs = function (node, names) {
    $_bwcteb4je5o2pl4.each(names, function (name) {
      node.attr(name, null);
    });
  };
  var addFontToSpansFilter = function (domParser, styles, fontSizes) {
    domParser.addNodeFilter('font', function (nodes) {
      $_bwcteb4je5o2pl4.each(nodes, function (node) {
        var props = styles.parse(node.attr('style'));
        var color = node.attr('color');
        var face = node.attr('face');
        var size = node.attr('size');
        if (color) {
          props.color = color;
        }
        if (face) {
          props['font-family'] = face;
        }
        if (size) {
          props['font-size'] = fontSizes[parseInt(node.attr('size'), 10) - 1];
        }
        node.name = 'span';
        node.attr('style', styles.serialize(props));
        removeAttrs(node, [
          'color',
          'face',
          'size'
        ]);
      });
    });
  };
  var addStrikeToSpanFilter = function (domParser, styles) {
    domParser.addNodeFilter('strike', function (nodes) {
      $_bwcteb4je5o2pl4.each(nodes, function (node) {
        var props = styles.parse(node.attr('style'));
        props['text-decoration'] = 'line-through';
        node.name = 'span';
        node.attr('style', styles.serialize(props));
      });
    });
  };
  var addFilters = function (domParser, settings) {
    var styles = Styles();
    if (settings.convert_fonts_to_spans) {
      addFontToSpansFilter(domParser, styles, $_a5gegtjje5o2pum.explode(settings.font_size_legacy_values));
    }
    addStrikeToSpanFilter(domParser, styles);
  };
  var register$1 = function (domParser, settings) {
    if (settings.inline_styles) {
      addFilters(domParser, settings);
    }
  };
  var $_6tznke5zje5o2qx6 = { register: register$1 };

  var whiteSpaceRegExp$3 = /^[ \t\r\n]*$/;
  var typeLookup = {
    '#text': 3,
    '#comment': 8,
    '#cdata': 4,
    '#pi': 7,
    '#doctype': 10,
    '#document-fragment': 11
  };
  var walk$3 = function (node, root, prev) {
    var sibling;
    var parent;
    var startName = prev ? 'lastChild' : 'firstChild';
    var siblingName = prev ? 'prev' : 'next';
    if (node[startName]) {
      return node[startName];
    }
    if (node !== root) {
      sibling = node[siblingName];
      if (sibling) {
        return sibling;
      }
      for (parent = node.parent; parent && parent !== root; parent = parent.parent) {
        sibling = parent[siblingName];
        if (sibling) {
          return sibling;
        }
      }
    }
  };
  var Node$2 = function () {
    function Node(name, type) {
      this.name = name;
      this.type = type;
      if (type === 1) {
        this.attributes = [];
        this.attributes.map = {};
      }
    }
    Node.create = function (name, attrs) {
      var node, attrName;
      node = new Node(name, typeLookup[name] || 1);
      if (attrs) {
        for (attrName in attrs) {
          node.attr(attrName, attrs[attrName]);
        }
      }
      return node;
    };
    Node.prototype.replace = function (node) {
      var self = this;
      if (node.parent) {
        node.remove();
      }
      self.insert(node, self);
      self.remove();
      return self;
    };
    Node.prototype.attr = function (name, value) {
      var self = this;
      var attrs, i;
      if (typeof name !== 'string') {
        for (i in name) {
          self.attr(i, name[i]);
        }
        return self;
      }
      if (attrs = self.attributes) {
        if (value !== undefined) {
          if (value === null) {
            if (name in attrs.map) {
              delete attrs.map[name];
              i = attrs.length;
              while (i--) {
                if (attrs[i].name === name) {
                  attrs = attrs.splice(i, 1);
                  return self;
                }
              }
            }
            return self;
          }
          if (name in attrs.map) {
            i = attrs.length;
            while (i--) {
              if (attrs[i].name === name) {
                attrs[i].value = value;
                break;
              }
            }
          } else {
            attrs.push({
              name: name,
              value: value
            });
          }
          attrs.map[name] = value;
          return self;
        }
        return attrs.map[name];
      }
    };
    Node.prototype.clone = function () {
      var self = this;
      var clone = new Node(self.name, self.type);
      var i, l, selfAttrs, selfAttr, cloneAttrs;
      if (selfAttrs = self.attributes) {
        cloneAttrs = [];
        cloneAttrs.map = {};
        for (i = 0, l = selfAttrs.length; i < l; i++) {
          selfAttr = selfAttrs[i];
          if (selfAttr.name !== 'id') {
            cloneAttrs[cloneAttrs.length] = {
              name: selfAttr.name,
              value: selfAttr.value
            };
            cloneAttrs.map[selfAttr.name] = selfAttr.value;
          }
        }
        clone.attributes = cloneAttrs;
      }
      clone.value = self.value;
      clone.shortEnded = self.shortEnded;
      return clone;
    };
    Node.prototype.wrap = function (wrapper) {
      var self = this;
      self.parent.insert(wrapper, self);
      wrapper.append(self);
      return self;
    };
    Node.prototype.unwrap = function () {
      var self = this;
      var node, next;
      for (node = self.firstChild; node;) {
        next = node.next;
        self.insert(node, self, true);
        node = next;
      }
      self.remove();
    };
    Node.prototype.remove = function () {
      var self = this, parent = self.parent, next = self.next, prev = self.prev;
      if (parent) {
        if (parent.firstChild === self) {
          parent.firstChild = next;
          if (next) {
            next.prev = null;
          }
        } else {
          prev.next = next;
        }
        if (parent.lastChild === self) {
          parent.lastChild = prev;
          if (prev) {
            prev.next = null;
          }
        } else {
          next.prev = prev;
        }
        self.parent = self.next = self.prev = null;
      }
      return self;
    };
    Node.prototype.append = function (node) {
      var self = this;
      var last;
      if (node.parent) {
        node.remove();
      }
      last = self.lastChild;
      if (last) {
        last.next = node;
        node.prev = last;
        self.lastChild = node;
      } else {
        self.lastChild = self.firstChild = node;
      }
      node.parent = self;
      return node;
    };
    Node.prototype.insert = function (node, refNode, before) {
      var parent;
      if (node.parent) {
        node.remove();
      }
      parent = refNode.parent || this;
      if (before) {
        if (refNode === parent.firstChild) {
          parent.firstChild = node;
        } else {
          refNode.prev.next = node;
        }
        node.prev = refNode.prev;
        node.next = refNode;
        refNode.prev = node;
      } else {
        if (refNode === parent.lastChild) {
          parent.lastChild = node;
        } else {
          refNode.next.prev = node;
        }
        node.next = refNode.next;
        node.prev = refNode;
        refNode.next = node;
      }
      node.parent = parent;
      return node;
    };
    Node.prototype.getAll = function (name) {
      var self = this;
      var node;
      var collection = [];
      for (node = self.firstChild; node; node = walk$3(node, self)) {
        if (node.name === name) {
          collection.push(node);
        }
      }
      return collection;
    };
    Node.prototype.empty = function () {
      var self = this;
      var nodes, i, node;
      if (self.firstChild) {
        nodes = [];
        for (node = self.firstChild; node; node = walk$3(node, self)) {
          nodes.push(node);
        }
        i = nodes.length;
        while (i--) {
          node = nodes[i];
          node.parent = node.firstChild = node.lastChild = node.next = node.prev = null;
        }
      }
      self.firstChild = self.lastChild = null;
      return self;
    };
    Node.prototype.isEmpty = function (elements, whitespace, predicate) {
      var self = this;
      var node = self.firstChild, i, name;
      whitespace = whitespace || {};
      if (node) {
        do {
          if (node.type === 1) {
            if (node.attributes.map['data-mce-bogus']) {
              continue;
            }
            if (elements[node.name]) {
              return false;
            }
            i = node.attributes.length;
            while (i--) {
              name = node.attributes[i].name;
              if (name === 'name' || name.indexOf('data-mce-bookmark') === 0) {
                return false;
              }
            }
          }
          if (node.type === 8) {
            return false;
          }
          if (node.type === 3 && !whiteSpaceRegExp$3.test(node.value)) {
            return false;
          }
          if (node.type === 3 && node.parent && whitespace[node.parent.name] && whiteSpaceRegExp$3.test(node.value)) {
            return false;
          }
          if (predicate && predicate(node)) {
            return false;
          }
        } while (node = walk$3(node, self));
      }
      return true;
    };
    Node.prototype.walk = function (prev) {
      return walk$3(this, null, prev);
    };
    return Node;
  }();

  var paddEmptyNode = function (settings, args, blockElements, node) {
    var brPreferred = settings.padd_empty_with_br || args.insert;
    if (brPreferred && blockElements[node.name]) {
      node.empty().append(new Node$2('br', 1)).shortEnded = true;
    } else {
      node.empty().append(new Node$2('#text', 3)).value = '\xA0';
    }
  };
  var isPaddedWithNbsp = function (node) {
    return hasOnlyChild(node, '#text') && node.firstChild.value === '\xA0';
  };
  var hasOnlyChild = function (node, name) {
    return node && node.firstChild && node.firstChild === node.lastChild && node.firstChild.name === name;
  };
  var isPadded = function (schema, node) {
    var rule = schema.getElementRule(node.name);
    return rule && rule.paddEmpty;
  };
  var isEmpty$1 = function (schema, nonEmptyElements, whitespaceElements, node) {
    return node.isEmpty(nonEmptyElements, whitespaceElements, function (node) {
      return isPadded(schema, node);
    });
  };
  var isLineBreakNode = function (node, blockElements) {
    return node && (blockElements[node.name] || node.name === 'br');
  };

  var register$2 = function (parser, settings) {
    var schema = parser.schema;
    if (settings.remove_trailing_brs) {
      parser.addNodeFilter('br', function (nodes, _, args) {
        var i;
        var l = nodes.length;
        var node;
        var blockElements = $_a5gegtjje5o2pum.extend({}, schema.getBlockElements());
        var nonEmptyElements = schema.getNonEmptyElements();
        var parent, lastParent, prev, prevName;
        var whiteSpaceElements = schema.getNonEmptyElements();
        var elementRule, textNode;
        blockElements.body = 1;
        for (i = 0; i < l; i++) {
          node = nodes[i];
          parent = node.parent;
          if (blockElements[node.parent.name] && node === parent.lastChild) {
            prev = node.prev;
            while (prev) {
              prevName = prev.name;
              if (prevName !== 'span' || prev.attr('data-mce-type') !== 'bookmark') {
                if (prevName !== 'br') {
                  break;
                }
                if (prevName === 'br') {
                  node = null;
                  break;
                }
              }
              prev = prev.prev;
            }
            if (node) {
              node.remove();
              if (isEmpty$1(schema, nonEmptyElements, whiteSpaceElements, parent)) {
                elementRule = schema.getElementRule(parent.name);
                if (elementRule) {
                  if (elementRule.removeEmpty) {
                    parent.remove();
                  } else if (elementRule.paddEmpty) {
                    paddEmptyNode(settings, args, blockElements, parent);
                  }
                }
              }
            }
          } else {
            lastParent = node;
            while (parent && parent.firstChild === lastParent && parent.lastChild === lastParent) {
              lastParent = parent;
              if (blockElements[parent.name]) {
                break;
              }
              parent = parent.parent;
            }
            if (lastParent === parent && settings.padd_empty_with_br !== true) {
              textNode = new Node$2('#text', 3);
              textNode.value = '\xA0';
              node.replace(textNode);
            }
          }
        }
      });
    }
    parser.addAttributeFilter('href', function (nodes) {
      var i = nodes.length, node;
      var appendRel = function (rel) {
        var parts = rel.split(' ').filter(function (p) {
          return p.length > 0;
        });
        return parts.concat(['noopener']).sort().join(' ');
      };
      var addNoOpener = function (rel) {
        var newRel = rel ? $_a5gegtjje5o2pum.trim(rel) : '';
        if (!/\b(noopener)\b/g.test(newRel)) {
          return appendRel(newRel);
        } else {
          return newRel;
        }
      };
      if (!settings.allow_unsafe_link_target) {
        while (i--) {
          node = nodes[i];
          if (node.name === 'a' && node.attr('target') === '_blank') {
            node.attr('rel', addNoOpener(node.attr('rel')));
          }
        }
      }
    });
    if (!settings.allow_html_in_named_anchor) {
      parser.addAttributeFilter('id,name', function (nodes) {
        var i = nodes.length, sibling, prevSibling, parent, node;
        while (i--) {
          node = nodes[i];
          if (node.name === 'a' && node.firstChild && !node.attr('href')) {
            parent = node.parent;
            sibling = node.lastChild;
            do {
              prevSibling = sibling.prev;
              parent.insert(sibling, node);
              sibling = prevSibling;
            } while (sibling);
          }
        }
      });
    }
    if (settings.fix_list_elements) {
      parser.addNodeFilter('ul,ol', function (nodes) {
        var i = nodes.length, node, parentNode;
        while (i--) {
          node = nodes[i];
          parentNode = node.parent;
          if (parentNode.name === 'ul' || parentNode.name === 'ol') {
            if (node.prev && node.prev.name === 'li') {
              node.prev.append(node);
            } else {
              var li = new Node$2('li', 1);
              li.attr('style', 'list-style-type: none');
              node.wrap(li);
            }
          }
        }
      });
    }
    if (settings.validate && schema.getValidClasses()) {
      parser.addAttributeFilter('class', function (nodes) {
        var i = nodes.length, node, classList, ci, className, classValue;
        var validClasses = schema.getValidClasses();
        var validClassesMap, valid;
        while (i--) {
          node = nodes[i];
          classList = node.attr('class').split(' ');
          classValue = '';
          for (ci = 0; ci < classList.length; ci++) {
            className = classList[ci];
            valid = false;
            validClassesMap = validClasses['*'];
            if (validClassesMap && validClassesMap[className]) {
              valid = true;
            }
            validClassesMap = validClasses[node.name];
            if (!valid && validClassesMap && validClassesMap[className]) {
              valid = true;
            }
            if (valid) {
              if (classValue) {
                classValue += ' ';
              }
              classValue += className;
            }
          }
          if (!classValue.length) {
            classValue = null;
          }
          node.attr('class', classValue);
        }
      });
    }
  };

  var makeMap$4 = $_a5gegtjje5o2pum.makeMap;
  var each$19 = $_a5gegtjje5o2pum.each;
  var explode$4 = $_a5gegtjje5o2pum.explode;
  var extend$3 = $_a5gegtjje5o2pum.extend;
  function DomParser (settings, schema) {
    if (schema === void 0) {
      schema = Schema();
    }
    var nodeFilters = {};
    var attributeFilters = [];
    var matchedNodes = {};
    var matchedAttributes = {};
    settings = settings || {};
    settings.validate = 'validate' in settings ? settings.validate : true;
    settings.root_name = settings.root_name || 'body';
    var fixInvalidChildren = function (nodes) {
      var ni, node, parent, parents, newParent, currentNode, tempNode, childNode, i;
      var nonEmptyElements, whitespaceElements, nonSplitableElements, textBlockElements, specialElements, sibling, nextNode;
      nonSplitableElements = makeMap$4('tr,td,th,tbody,thead,tfoot,table');
      nonEmptyElements = schema.getNonEmptyElements();
      whitespaceElements = schema.getWhiteSpaceElements();
      textBlockElements = schema.getTextBlockElements();
      specialElements = schema.getSpecialElements();
      for (ni = 0; ni < nodes.length; ni++) {
        node = nodes[ni];
        if (!node.parent || node.fixed) {
          continue;
        }
        if (textBlockElements[node.name] && node.parent.name === 'li') {
          sibling = node.next;
          while (sibling) {
            if (textBlockElements[sibling.name]) {
              sibling.name = 'li';
              sibling.fixed = true;
              node.parent.insert(sibling, node.parent);
            } else {
              break;
            }
            sibling = sibling.next;
          }
          node.unwrap(node);
          continue;
        }
        parents = [node];
        for (parent = node.parent; parent && !schema.isValidChild(parent.name, node.name) && !nonSplitableElements[parent.name]; parent = parent.parent) {
          parents.push(parent);
        }
        if (parent && parents.length > 1) {
          parents.reverse();
          newParent = currentNode = filterNode(parents[0].clone());
          for (i = 0; i < parents.length - 1; i++) {
            if (schema.isValidChild(currentNode.name, parents[i].name)) {
              tempNode = filterNode(parents[i].clone());
              currentNode.append(tempNode);
            } else {
              tempNode = currentNode;
            }
            for (childNode = parents[i].firstChild; childNode && childNode !== parents[i + 1];) {
              nextNode = childNode.next;
              tempNode.append(childNode);
              childNode = nextNode;
            }
            currentNode = tempNode;
          }
          if (!isEmpty$1(schema, nonEmptyElements, whitespaceElements, newParent)) {
            parent.insert(newParent, parents[0], true);
            parent.insert(node, newParent);
          } else {
            parent.insert(node, parents[0], true);
          }
          parent = parents[0];
          if (isEmpty$1(schema, nonEmptyElements, whitespaceElements, parent) || hasOnlyChild(parent, 'br')) {
            parent.empty().remove();
          }
        } else if (node.parent) {
          if (node.name === 'li') {
            sibling = node.prev;
            if (sibling && (sibling.name === 'ul' || sibling.name === 'ul')) {
              sibling.append(node);
              continue;
            }
            sibling = node.next;
            if (sibling && (sibling.name === 'ul' || sibling.name === 'ul')) {
              sibling.insert(node, sibling.firstChild, true);
              continue;
            }
            node.wrap(filterNode(new Node$2('ul', 1)));
            continue;
          }
          if (schema.isValidChild(node.parent.name, 'div') && schema.isValidChild('div', node.name)) {
            node.wrap(filterNode(new Node$2('div', 1)));
          } else {
            if (specialElements[node.name]) {
              node.empty().remove();
            } else {
              node.unwrap();
            }
          }
        }
      }
    };
    var filterNode = function (node) {
      var i, name, list;
      if (name in nodeFilters) {
        list = matchedNodes[name];
        if (list) {
          list.push(node);
        } else {
          matchedNodes[name] = [node];
        }
      }
      i = attributeFilters.length;
      while (i--) {
        name = attributeFilters[i].name;
        if (name in node.attributes.map) {
          list = matchedAttributes[name];
          if (list) {
            list.push(node);
          } else {
            matchedAttributes[name] = [node];
          }
        }
      }
      return node;
    };
    var addNodeFilter = function (name, callback) {
      each$19(explode$4(name), function (name) {
        var list = nodeFilters[name];
        if (!list) {
          nodeFilters[name] = list = [];
        }
        list.push(callback);
      });
    };
    var getNodeFilters = function () {
      var out = [];
      for (var name_1 in nodeFilters) {
        if (nodeFilters.hasOwnProperty(name_1)) {
          out.push({
            name: name_1,
            callbacks: nodeFilters[name_1]
          });
        }
      }
      return out;
    };
    var addAttributeFilter = function (name, callback) {
      each$19(explode$4(name), function (name) {
        var i;
        for (i = 0; i < attributeFilters.length; i++) {
          if (attributeFilters[i].name === name) {
            attributeFilters[i].callbacks.push(callback);
            return;
          }
        }
        attributeFilters.push({
          name: name,
          callbacks: [callback]
        });
      });
    };
    var getAttributeFilters = function () {
      return [].concat(attributeFilters);
    };
    var parse = function (html, args) {
      var parser, nodes, i, l, fi, fl, list, name;
      var blockElements;
      var invalidChildren = [];
      var isInWhiteSpacePreservedElement;
      var node;
      args = args || {};
      matchedNodes = {};
      matchedAttributes = {};
      blockElements = extend$3(makeMap$4('script,style,head,html,body,title,meta,param'), schema.getBlockElements());
      var nonEmptyElements = schema.getNonEmptyElements();
      var children = schema.children;
      var validate = settings.validate;
      var rootBlockName = 'forced_root_block' in args ? args.forced_root_block : settings.forced_root_block;
      var whiteSpaceElements = schema.getWhiteSpaceElements();
      var startWhiteSpaceRegExp = /^[ \t\r\n]+/;
      var endWhiteSpaceRegExp = /[ \t\r\n]+$/;
      var allWhiteSpaceRegExp = /[ \t\r\n]+/g;
      var isAllWhiteSpaceRegExp = /^[ \t\r\n]+$/;
      var addRootBlocks = function () {
        var node = rootNode.firstChild, next, rootBlockNode;
        var trim = function (rootBlockNode) {
          if (rootBlockNode) {
            node = rootBlockNode.firstChild;
            if (node && node.type === 3) {
              node.value = node.value.replace(startWhiteSpaceRegExp, '');
            }
            node = rootBlockNode.lastChild;
            if (node && node.type === 3) {
              node.value = node.value.replace(endWhiteSpaceRegExp, '');
            }
          }
        };
        if (!schema.isValidChild(rootNode.name, rootBlockName.toLowerCase())) {
          return;
        }
        while (node) {
          next = node.next;
          if (node.type === 3 || node.type === 1 && node.name !== 'p' && !blockElements[node.name] && !node.attr('data-mce-type')) {
            if (!rootBlockNode) {
              rootBlockNode = createNode(rootBlockName, 1);
              rootBlockNode.attr(settings.forced_root_block_attrs);
              rootNode.insert(rootBlockNode, node);
              rootBlockNode.append(node);
            } else {
              rootBlockNode.append(node);
            }
          } else {
            trim(rootBlockNode);
            rootBlockNode = null;
          }
          node = next;
        }
        trim(rootBlockNode);
      };
      var createNode = function (name, type) {
        var node = new Node$2(name, type);
        var list;
        if (name in nodeFilters) {
          list = matchedNodes[name];
          if (list) {
            list.push(node);
          } else {
            matchedNodes[name] = [node];
          }
        }
        return node;
      };
      var removeWhitespaceBefore = function (node) {
        var textNode, textNodeNext, textVal, sibling;
        var blockElements = schema.getBlockElements();
        for (textNode = node.prev; textNode && textNode.type === 3;) {
          textVal = textNode.value.replace(endWhiteSpaceRegExp, '');
          if (textVal.length > 0) {
            textNode.value = textVal;
            return;
          }
          textNodeNext = textNode.next;
          if (textNodeNext) {
            if (textNodeNext.type === 3 && textNodeNext.value.length) {
              textNode = textNode.prev;
              continue;
            }
            if (!blockElements[textNodeNext.name] && textNodeNext.name !== 'script' && textNodeNext.name !== 'style') {
              textNode = textNode.prev;
              continue;
            }
          }
          sibling = textNode.prev;
          textNode.remove();
          textNode = sibling;
        }
      };
      var cloneAndExcludeBlocks = function (input) {
        var name;
        var output = {};
        for (name in input) {
          if (name !== 'li' && name !== 'p') {
            output[name] = input[name];
          }
        }
        return output;
      };
      parser = SaxParser$1({
        validate: validate,
        allow_script_urls: settings.allow_script_urls,
        allow_conditional_comments: settings.allow_conditional_comments,
        self_closing_elements: cloneAndExcludeBlocks(schema.getSelfClosingElements()),
        cdata: function (text) {
          node.append(createNode('#cdata', 4)).value = text;
        },
        text: function (text, raw) {
          var textNode;
          if (!isInWhiteSpacePreservedElement) {
            text = text.replace(allWhiteSpaceRegExp, ' ');
            if (isLineBreakNode(node.lastChild, blockElements)) {
              text = text.replace(startWhiteSpaceRegExp, '');
            }
          }
          if (text.length !== 0) {
            textNode = createNode('#text', 3);
            textNode.raw = !!raw;
            node.append(textNode).value = text;
          }
        },
        comment: function (text) {
          node.append(createNode('#comment', 8)).value = text;
        },
        pi: function (name, text) {
          node.append(createNode(name, 7)).value = text;
          removeWhitespaceBefore(node);
        },
        doctype: function (text) {
          var newNode;
          newNode = node.append(createNode('#doctype', 10));
          newNode.value = text;
          removeWhitespaceBefore(node);
        },
        start: function (name, attrs, empty) {
          var newNode, attrFiltersLen, elementRule, attrName, parent;
          elementRule = validate ? schema.getElementRule(name) : {};
          if (elementRule) {
            newNode = createNode(elementRule.outputName || name, 1);
            newNode.attributes = attrs;
            newNode.shortEnded = empty;
            node.append(newNode);
            parent = children[node.name];
            if (parent && children[newNode.name] && !parent[newNode.name]) {
              invalidChildren.push(newNode);
            }
            attrFiltersLen = attributeFilters.length;
            while (attrFiltersLen--) {
              attrName = attributeFilters[attrFiltersLen].name;
              if (attrName in attrs.map) {
                list = matchedAttributes[attrName];
                if (list) {
                  list.push(newNode);
                } else {
                  matchedAttributes[attrName] = [newNode];
                }
              }
            }
            if (blockElements[name]) {
              removeWhitespaceBefore(newNode);
            }
            if (!empty) {
              node = newNode;
            }
            if (!isInWhiteSpacePreservedElement && whiteSpaceElements[name]) {
              isInWhiteSpacePreservedElement = true;
            }
          }
        },
        end: function (name) {
          var textNode, elementRule, text, sibling, tempNode;
          elementRule = validate ? schema.getElementRule(name) : {};
          if (elementRule) {
            if (blockElements[name]) {
              if (!isInWhiteSpacePreservedElement) {
                textNode = node.firstChild;
                if (textNode && textNode.type === 3) {
                  text = textNode.value.replace(startWhiteSpaceRegExp, '');
                  if (text.length > 0) {
                    textNode.value = text;
                    textNode = textNode.next;
                  } else {
                    sibling = textNode.next;
                    textNode.remove();
                    textNode = sibling;
                    while (textNode && textNode.type === 3) {
                      text = textNode.value;
                      sibling = textNode.next;
                      if (text.length === 0 || isAllWhiteSpaceRegExp.test(text)) {
                        textNode.remove();
                        textNode = sibling;
                      }
                      textNode = sibling;
                    }
                  }
                }
                textNode = node.lastChild;
                if (textNode && textNode.type === 3) {
                  text = textNode.value.replace(endWhiteSpaceRegExp, '');
                  if (text.length > 0) {
                    textNode.value = text;
                    textNode = textNode.prev;
                  } else {
                    sibling = textNode.prev;
                    textNode.remove();
                    textNode = sibling;
                    while (textNode && textNode.type === 3) {
                      text = textNode.value;
                      sibling = textNode.prev;
                      if (text.length === 0 || isAllWhiteSpaceRegExp.test(text)) {
                        textNode.remove();
                        textNode = sibling;
                      }
                      textNode = sibling;
                    }
                  }
                }
              }
            }
            if (isInWhiteSpacePreservedElement && whiteSpaceElements[name]) {
              isInWhiteSpacePreservedElement = false;
            }
            if (elementRule.removeEmpty && isEmpty$1(schema, nonEmptyElements, whiteSpaceElements, node)) {
              if (!node.attributes.map.name && !node.attr('id')) {
                tempNode = node.parent;
                if (blockElements[node.name]) {
                  node.empty().remove();
                } else {
                  node.unwrap();
                }
                node = tempNode;
                return;
              }
            }
            if (elementRule.paddEmpty && (isPaddedWithNbsp(node) || isEmpty$1(schema, nonEmptyElements, whiteSpaceElements, node))) {
              paddEmptyNode(settings, args, blockElements, node);
            }
            node = node.parent;
          }
        }
      }, schema);
      var rootNode = node = new Node$2(args.context || settings.root_name, 11);
      parser.parse(html);
      if (validate && invalidChildren.length) {
        if (!args.context) {
          fixInvalidChildren(invalidChildren);
        } else {
          args.invalid = true;
        }
      }
      if (rootBlockName && (rootNode.name === 'body' || args.isRootContent)) {
        addRootBlocks();
      }
      if (!args.invalid) {
        for (name in matchedNodes) {
          list = nodeFilters[name];
          nodes = matchedNodes[name];
          fi = nodes.length;
          while (fi--) {
            if (!nodes[fi].parent) {
              nodes.splice(fi, 1);
            }
          }
          for (i = 0, l = list.length; i < l; i++) {
            list[i](nodes, name, args);
          }
        }
        for (i = 0, l = attributeFilters.length; i < l; i++) {
          list = attributeFilters[i];
          if (list.name in matchedAttributes) {
            nodes = matchedAttributes[list.name];
            fi = nodes.length;
            while (fi--) {
              if (!nodes[fi].parent) {
                nodes.splice(fi, 1);
              }
            }
            for (fi = 0, fl = list.callbacks.length; fi < fl; fi++) {
              list.callbacks[fi](nodes, list.name, args);
            }
          }
        }
      }
      return rootNode;
    };
    var exports = {
      schema: schema,
      addAttributeFilter: addAttributeFilter,
      getAttributeFilters: getAttributeFilters,
      addNodeFilter: addNodeFilter,
      getNodeFilters: getNodeFilters,
      filterNode: filterNode,
      parse: parse
    };
    register$2(exports, settings);
    $_6tznke5zje5o2qx6.register(exports, settings);
    return exports;
  }

  var addTempAttr = function (htmlParser, tempAttrs, name) {
    if ($_a5gegtjje5o2pum.inArray(tempAttrs, name) === -1) {
      htmlParser.addAttributeFilter(name, function (nodes, name) {
        var i = nodes.length;
        while (i--) {
          nodes[i].attr(name, null);
        }
      });
      tempAttrs.push(name);
    }
  };
  var postProcess$1 = function (editor, args, content) {
    if (!args.no_events && editor) {
      var outArgs = $_d6jl355vje5o2qw5.firePostProcess(editor, $_atjucy5uje5o2qw2.merge(args, { content: content }));
      return outArgs.content;
    } else {
      return content;
    }
  };
  var getHtmlFromNode = function (dom, node, args) {
    var html = $_4md31c21je5o2q5f.trim(args.getInner ? node.innerHTML : dom.getOuterHTML(node));
    return args.selection ? html : $_a5gegtjje5o2pum.trim(html);
  };
  var parseHtml = function (htmlParser, dom, html, args) {
    var parserArgs = args.selection ? $_atjucy5uje5o2qw2.merge({ forced_root_block: false }, args) : args;
    var rootNode = htmlParser.parse(html, parserArgs);
    $_97epr95wje5o2qw7.trimTrailingBr(rootNode);
    return rootNode;
  };
  var serializeNode = function (settings, schema, node) {
    var htmlSerializer = HtmlSerializer(settings, schema);
    return htmlSerializer.serialize(node);
  };
  var toHtml = function (editor, settings, schema, rootNode, args) {
    var content = serializeNode(settings, schema, rootNode);
    return postProcess$1(editor, args, content);
  };
  function DomSerializer (settings, editor) {
    var dom, schema, htmlParser;
    var tempAttrs = ['data-mce-selected'];
    dom = editor && editor.dom ? editor.dom : DOMUtils.DOM;
    schema = editor && editor.schema ? editor.schema : Schema(settings);
    settings.entity_encoding = settings.entity_encoding || 'named';
    settings.remove_trailing_brs = 'remove_trailing_brs' in settings ? settings.remove_trailing_brs : true;
    htmlParser = DomParser(settings, schema);
    $_97epr95wje5o2qw7.register(htmlParser, settings, dom);
    var serialize = function (node, parserArgs) {
      var args = $_atjucy5uje5o2qw2.merge({ format: 'html' }, parserArgs ? parserArgs : {});
      var targetNode = $_70hszr5xje5o2qwd.process(editor, node, args);
      var html = getHtmlFromNode(dom, targetNode, args);
      var rootNode = parseHtml(htmlParser, dom, html, args);
      return args.format === 'tree' ? rootNode : toHtml(editor, settings, schema, rootNode, args);
    };
    return {
      schema: schema,
      addNodeFilter: htmlParser.addNodeFilter,
      addAttributeFilter: htmlParser.addAttributeFilter,
      serialize: serialize,
      addRules: function (rules) {
        schema.addValidElements(rules);
      },
      setRules: function (rules) {
        schema.setValidElements(rules);
      },
      addTempAttr: $_enfhkv6je5o2ply.curry(addTempAttr, htmlParser, tempAttrs),
      getTempAttrs: function () {
        return tempAttrs;
      }
    };
  }

  function DomSerializer$1 (settings, editor) {
    var domSerializer = DomSerializer(settings, editor);
    return {
      schema: domSerializer.schema,
      addNodeFilter: domSerializer.addNodeFilter,
      addAttributeFilter: domSerializer.addAttributeFilter,
      serialize: domSerializer.serialize,
      addRules: domSerializer.addRules,
      setRules: domSerializer.setRules,
      addTempAttr: domSerializer.addTempAttr,
      getTempAttrs: domSerializer.getTempAttrs
    };
  }

  var findBlockCaretContainer = function (editor) {
    return $_9fzewc37je5o2qck.descendant($_5tcb2dyje5o2pwu.fromDom(editor.getBody()), '*[data-mce-caret]').fold($_enfhkv6je5o2ply.constant(null), function (elm) {
      return elm.dom();
    });
  };
  var removeIeControlRect = function (editor) {
    editor.selection.setRng(editor.selection.getRng());
  };
  var showBlockCaretContainer = function (editor, blockCaretContainer) {
    if (blockCaretContainer.hasAttribute('data-mce-caret')) {
      showCaretContainerBlock(blockCaretContainer);
      removeIeControlRect(editor);
      editor.selection.scrollIntoView(blockCaretContainer);
    }
  };
  var handleBlockContainer = function (editor, e) {
    var blockCaretContainer = findBlockCaretContainer(editor);
    if (!blockCaretContainer) {
      return;
    }
    if (e.type === 'compositionstart') {
      e.preventDefault();
      e.stopPropagation();
      showBlockCaretContainer(editor, blockCaretContainer);
      return;
    }
    if (hasContent(blockCaretContainer)) {
      showBlockCaretContainer(editor, blockCaretContainer);
    }
  };
  var setup$5 = function (editor) {
    editor.on('keyup compositionstart', $_enfhkv6je5o2ply.curry(handleBlockContainer, editor));
  };
  var $_acfnaa63je5o2qxq = { setup: setup$5 };

  function BookmarkManager(selection) {
    return {
      getBookmark: $_enfhkv6je5o2ply.curry($_83h91t2jje5o2q8n.getBookmark, selection),
      moveToBookmark: $_enfhkv6je5o2ply.curry($_83h91t2jje5o2q8n.moveToBookmark, selection)
    };
  }
  (function (BookmarkManager) {
    BookmarkManager.isBookmarkNode = $_83h91t2jje5o2q8n.isBookmarkNode;
  }(BookmarkManager || (BookmarkManager = {})));
  var BookmarkManager$1 = BookmarkManager;

  var isContentEditableFalse$10 = $_9owqcy1qje5o2q0e.isContentEditableFalse;
  var isContentEditableTrue$6 = $_9owqcy1qje5o2q0e.isContentEditableTrue;
  var getContentEditableRoot$1 = function (root, node) {
    while (node && node !== root) {
      if (isContentEditableTrue$6(node) || isContentEditableFalse$10(node)) {
        return node;
      }
      node = node.parentNode;
    }
    return null;
  };
  var ControlSelection = function (selection, editor) {
    var dom = editor.dom, each = $_a5gegtjje5o2pum.each;
    var selectedElm, selectedElmGhost, resizeHelper, resizeHandles, selectedHandle;
    var startX, startY, selectedElmX, selectedElmY, startW, startH, ratio, resizeStarted;
    var width, height;
    var editableDoc = editor.getDoc(), rootDocument = document;
    var abs = Math.abs, round = Math.round, rootElement = editor.getBody();
    var startScrollWidth, startScrollHeight;
    resizeHandles = {
      nw: [
        0,
        0,
        -1,
        -1
      ],
      ne: [
        1,
        0,
        1,
        -1
      ],
      se: [
        1,
        1,
        1,
        1
      ],
      sw: [
        0,
        1,
        -1,
        1
      ]
    };
    var rootClass = '.mce-content-body';
    editor.contentStyles.push(rootClass + ' div.mce-resizehandle {' + 'position: absolute;' + 'border: 1px solid black;' + 'box-sizing: content-box;' + 'background: #FFF;' + 'width: 7px;' + 'height: 7px;' + 'z-index: 10000' + '}' + rootClass + ' .mce-resizehandle:hover {' + 'background: #000' + '}' + rootClass + ' img[data-mce-selected],' + rootClass + ' hr[data-mce-selected] {' + 'outline: 1px solid black;' + 'resize: none' + '}' + rootClass + ' .mce-clonedresizable {' + 'position: absolute;' + ($_9bd8pn9je5o2pp2.gecko ? '' : 'outline: 1px dashed black;') + 'opacity: .5;' + 'filter: alpha(opacity=50);' + 'z-index: 10000' + '}' + rootClass + ' .mce-resize-helper {' + 'background: #555;' + 'background: rgba(0,0,0,0.75);' + 'border-radius: 3px;' + 'border: 1px;' + 'color: white;' + 'display: none;' + 'font-family: sans-serif;' + 'font-size: 12px;' + 'white-space: nowrap;' + 'line-height: 14px;' + 'margin: 5px 10px;' + 'padding: 5px;' + 'position: absolute;' + 'z-index: 10001' + '}');
    var isImage = function (elm) {
      return elm && (elm.nodeName === 'IMG' || editor.dom.is(elm, 'figure.image'));
    };
    var isEventOnImageOutsideRange = function (evt, range) {
      return isImage(evt.target) && !$_40xr2y56je5o2qsc.isXYWithinRange(evt.clientX, evt.clientY, range);
    };
    var contextMenuSelectImage = function (evt) {
      var target = evt.target;
      if (isEventOnImageOutsideRange(evt, editor.selection.getRng()) && !evt.isDefaultPrevented()) {
        evt.preventDefault();
        editor.selection.select(target);
      }
    };
    var getResizeTarget = function (elm) {
      return editor.dom.is(elm, 'figure.image') ? elm.querySelector('img') : elm;
    };
    var isResizable = function (elm) {
      var selector = editor.settings.object_resizing;
      if (selector === false || $_9bd8pn9je5o2pp2.iOS) {
        return false;
      }
      if (typeof selector !== 'string') {
        selector = 'table,img,figure.image,div';
      }
      if (elm.getAttribute('data-mce-resize') === 'false') {
        return false;
      }
      if (elm === editor.getBody()) {
        return false;
      }
      return $_87zu5k1fje5o2pz9.is($_5tcb2dyje5o2pwu.fromDom(elm), selector);
    };
    var resizeGhostElement = function (e) {
      var deltaX, deltaY, proportional;
      var resizeHelperX, resizeHelperY;
      deltaX = e.screenX - startX;
      deltaY = e.screenY - startY;
      width = deltaX * selectedHandle[2] + startW;
      height = deltaY * selectedHandle[3] + startH;
      width = width < 5 ? 5 : width;
      height = height < 5 ? 5 : height;
      if (isImage(selectedElm) && editor.settings.resize_img_proportional !== false) {
        proportional = !$_99mhlk59je5o2qss.modifierPressed(e);
      } else {
        proportional = $_99mhlk59je5o2qss.modifierPressed(e) || isImage(selectedElm) && selectedHandle[2] * selectedHandle[3] !== 0;
      }
      if (proportional) {
        if (abs(deltaX) > abs(deltaY)) {
          height = round(width * ratio);
          width = round(height / ratio);
        } else {
          width = round(height / ratio);
          height = round(width * ratio);
        }
      }
      dom.setStyles(getResizeTarget(selectedElmGhost), {
        width: width,
        height: height
      });
      resizeHelperX = selectedHandle.startPos.x + deltaX;
      resizeHelperY = selectedHandle.startPos.y + deltaY;
      resizeHelperX = resizeHelperX > 0 ? resizeHelperX : 0;
      resizeHelperY = resizeHelperY > 0 ? resizeHelperY : 0;
      dom.setStyles(resizeHelper, {
        left: resizeHelperX,
        top: resizeHelperY,
        display: 'block'
      });
      resizeHelper.innerHTML = width + ' &times; ' + height;
      if (selectedHandle[2] < 0 && selectedElmGhost.clientWidth <= width) {
        dom.setStyle(selectedElmGhost, 'left', selectedElmX + (startW - width));
      }
      if (selectedHandle[3] < 0 && selectedElmGhost.clientHeight <= height) {
        dom.setStyle(selectedElmGhost, 'top', selectedElmY + (startH - height));
      }
      deltaX = rootElement.scrollWidth - startScrollWidth;
      deltaY = rootElement.scrollHeight - startScrollHeight;
      if (deltaX + deltaY !== 0) {
        dom.setStyles(resizeHelper, {
          left: resizeHelperX - deltaX,
          top: resizeHelperY - deltaY
        });
      }
      if (!resizeStarted) {
        editor.fire('ObjectResizeStart', {
          target: selectedElm,
          width: startW,
          height: startH
        });
        resizeStarted = true;
      }
    };
    var endGhostResize = function () {
      resizeStarted = false;
      var setSizeProp = function (name, value) {
        if (value) {
          if (selectedElm.style[name] || !editor.schema.isValid(selectedElm.nodeName.toLowerCase(), name)) {
            dom.setStyle(getResizeTarget(selectedElm), name, value);
          } else {
            dom.setAttrib(getResizeTarget(selectedElm), name, value);
          }
        }
      };
      setSizeProp('width', width);
      setSizeProp('height', height);
      dom.unbind(editableDoc, 'mousemove', resizeGhostElement);
      dom.unbind(editableDoc, 'mouseup', endGhostResize);
      if (rootDocument !== editableDoc) {
        dom.unbind(rootDocument, 'mousemove', resizeGhostElement);
        dom.unbind(rootDocument, 'mouseup', endGhostResize);
      }
      dom.remove(selectedElmGhost);
      dom.remove(resizeHelper);
      showResizeRect(selectedElm);
      editor.fire('ObjectResized', {
        target: selectedElm,
        width: width,
        height: height
      });
      dom.setAttrib(selectedElm, 'style', dom.getAttrib(selectedElm, 'style'));
      editor.nodeChanged();
    };
    var showResizeRect = function (targetElm) {
      var position, targetWidth, targetHeight, e, rect;
      hideResizeRect();
      unbindResizeHandleEvents();
      position = dom.getPos(targetElm, rootElement);
      selectedElmX = position.x;
      selectedElmY = position.y;
      rect = targetElm.getBoundingClientRect();
      targetWidth = rect.width || rect.right - rect.left;
      targetHeight = rect.height || rect.bottom - rect.top;
      if (selectedElm !== targetElm) {
        selectedElm = targetElm;
        width = height = 0;
      }
      e = editor.fire('ObjectSelected', { target: targetElm });
      if (isResizable(targetElm) && !e.isDefaultPrevented()) {
        each(resizeHandles, function (handle, name) {
          var handleElm;
          var startDrag = function (e) {
            startX = e.screenX;
            startY = e.screenY;
            startW = getResizeTarget(selectedElm).clientWidth;
            startH = getResizeTarget(selectedElm).clientHeight;
            ratio = startH / startW;
            selectedHandle = handle;
            handle.startPos = {
              x: targetWidth * handle[0] + selectedElmX,
              y: targetHeight * handle[1] + selectedElmY
            };
            startScrollWidth = rootElement.scrollWidth;
            startScrollHeight = rootElement.scrollHeight;
            selectedElmGhost = selectedElm.cloneNode(true);
            dom.addClass(selectedElmGhost, 'mce-clonedresizable');
            dom.setAttrib(selectedElmGhost, 'data-mce-bogus', 'all');
            selectedElmGhost.contentEditable = false;
            selectedElmGhost.unSelectabe = true;
            dom.setStyles(selectedElmGhost, {
              left: selectedElmX,
              top: selectedElmY,
              margin: 0
            });
            selectedElmGhost.removeAttribute('data-mce-selected');
            rootElement.appendChild(selectedElmGhost);
            dom.bind(editableDoc, 'mousemove', resizeGhostElement);
            dom.bind(editableDoc, 'mouseup', endGhostResize);
            if (rootDocument !== editableDoc) {
              dom.bind(rootDocument, 'mousemove', resizeGhostElement);
              dom.bind(rootDocument, 'mouseup', endGhostResize);
            }
            resizeHelper = dom.add(rootElement, 'div', {
              'class': 'mce-resize-helper',
              'data-mce-bogus': 'all'
            }, startW + ' &times; ' + startH);
          };
          handleElm = dom.get('mceResizeHandle' + name);
          if (handleElm) {
            dom.remove(handleElm);
          }
          handleElm = dom.add(rootElement, 'div', {
            'id': 'mceResizeHandle' + name,
            'data-mce-bogus': 'all',
            'class': 'mce-resizehandle',
            'unselectable': true,
            'style': 'cursor:' + name + '-resize; margin:0; padding:0'
          });
          if ($_9bd8pn9je5o2pp2.ie) {
            handleElm.contentEditable = false;
          }
          dom.bind(handleElm, 'mousedown', function (e) {
            e.stopImmediatePropagation();
            e.preventDefault();
            startDrag(e);
          });
          handle.elm = handleElm;
          dom.setStyles(handleElm, {
            left: targetWidth * handle[0] + selectedElmX - handleElm.offsetWidth / 2,
            top: targetHeight * handle[1] + selectedElmY - handleElm.offsetHeight / 2
          });
        });
      } else {
        hideResizeRect();
      }
      selectedElm.setAttribute('data-mce-selected', '1');
    };
    var hideResizeRect = function () {
      var name, handleElm;
      unbindResizeHandleEvents();
      if (selectedElm) {
        selectedElm.removeAttribute('data-mce-selected');
      }
      for (name in resizeHandles) {
        handleElm = dom.get('mceResizeHandle' + name);
        if (handleElm) {
          dom.unbind(handleElm);
          dom.remove(handleElm);
        }
      }
    };
    var updateResizeRect = function (e) {
      var startElm, controlElm;
      var isChildOrEqual = function (node, parent) {
        if (node) {
          do {
            if (node === parent) {
              return true;
            }
          } while (node = node.parentNode);
        }
      };
      if (resizeStarted || editor.removed) {
        return;
      }
      each(dom.select('img[data-mce-selected],hr[data-mce-selected]'), function (img) {
        img.removeAttribute('data-mce-selected');
      });
      controlElm = e.type === 'mousedown' ? e.target : selection.getNode();
      controlElm = dom.$(controlElm).closest('table,img,figure.image,hr')[0];
      if (isChildOrEqual(controlElm, rootElement)) {
        disableGeckoResize();
        startElm = selection.getStart(true);
        if (isChildOrEqual(startElm, controlElm) && isChildOrEqual(selection.getEnd(true), controlElm)) {
          showResizeRect(controlElm);
          return;
        }
      }
      hideResizeRect();
    };
    var isWithinContentEditableFalse = function (elm) {
      return isContentEditableFalse$10(getContentEditableRoot$1(editor.getBody(), elm));
    };
    var unbindResizeHandleEvents = function () {
      for (var name_1 in resizeHandles) {
        var handle = resizeHandles[name_1];
        if (handle.elm) {
          dom.unbind(handle.elm);
          delete handle.elm;
        }
      }
    };
    var disableGeckoResize = function () {
      try {
        editor.getDoc().execCommand('enableObjectResizing', false, false);
      } catch (ex) {
      }
    };
    editor.on('init', function () {
      disableGeckoResize();
      if ($_9bd8pn9je5o2pp2.ie && $_9bd8pn9je5o2pp2.ie >= 11) {
        editor.on('mousedown click', function (e) {
          var target = e.target, nodeName = target.nodeName;
          if (!resizeStarted && /^(TABLE|IMG|HR)$/.test(nodeName) && !isWithinContentEditableFalse(target)) {
            if (e.button !== 2) {
              editor.selection.select(target, nodeName === 'TABLE');
            }
            if (e.type === 'mousedown') {
              editor.nodeChanged();
            }
          }
        });
        editor.dom.bind(rootElement, 'mscontrolselect', function (e) {
          var delayedSelect = function (node) {
            $_db41wogje5o2ps3.setEditorTimeout(editor, function () {
              editor.selection.select(node);
            });
          };
          if (isWithinContentEditableFalse(e.target)) {
            e.preventDefault();
            delayedSelect(e.target);
            return;
          }
          if (/^(TABLE|IMG|HR)$/.test(e.target.nodeName)) {
            e.preventDefault();
            if (e.target.tagName === 'IMG') {
              delayedSelect(e.target);
            }
          }
        });
      }
      var throttledUpdateResizeRect = $_db41wogje5o2ps3.throttle(function (e) {
        if (!editor.composing) {
          updateResizeRect(e);
        }
      });
      editor.on('nodechange ResizeEditor ResizeWindow drop FullscreenStateChanged', throttledUpdateResizeRect);
      editor.on('keyup compositionend', function (e) {
        if (selectedElm && selectedElm.nodeName === 'TABLE') {
          throttledUpdateResizeRect(e);
        }
      });
      editor.on('hide blur', hideResizeRect);
      editor.on('contextmenu', contextMenuSelectImage);
    });
    editor.on('remove', unbindResizeHandleEvents);
    var destroy = function () {
      selectedElm = selectedElmGhost = null;
    };
    return {
      isResizable: isResizable,
      showResizeRect: showResizeRect,
      hideResizeRect: hideResizeRect,
      updateResizeRect: updateResizeRect,
      destroy: destroy
    };
  };

  var getPos$1 = function (elm) {
    var x = 0, y = 0;
    var offsetParent = elm;
    while (offsetParent && offsetParent.nodeType) {
      x += offsetParent.offsetLeft || 0;
      y += offsetParent.offsetTop || 0;
      offsetParent = offsetParent.offsetParent;
    }
    return {
      x: x,
      y: y
    };
  };
  var fireScrollIntoViewEvent = function (editor, elm, alignToTop) {
    var scrollEvent = {
      elm: elm,
      alignToTop: alignToTop
    };
    editor.fire('scrollIntoView', scrollEvent);
    return scrollEvent.isDefaultPrevented();
  };
  var scrollIntoView = function (editor, elm, alignToTop) {
    var y, viewPort;
    var dom = editor.dom;
    var root = dom.getRoot();
    var viewPortY, viewPortH, offsetY = 0;
    if (fireScrollIntoViewEvent(editor, elm, alignToTop)) {
      return;
    }
    if (!$_9owqcy1qje5o2q0e.isElement(elm)) {
      return;
    }
    if (alignToTop === false) {
      offsetY = elm.offsetHeight;
    }
    if (root.nodeName !== 'BODY') {
      var scrollContainer = editor.selection.getScrollContainer();
      if (scrollContainer) {
        y = getPos$1(elm).y - getPos$1(scrollContainer).y + offsetY;
        viewPortH = scrollContainer.clientHeight;
        viewPortY = scrollContainer.scrollTop;
        if (y < viewPortY || y + 25 > viewPortY + viewPortH) {
          scrollContainer.scrollTop = y < viewPortY ? y : y - viewPortH + 25;
        }
        return;
      }
    }
    viewPort = dom.getViewPort(editor.getWin());
    y = dom.getPos(elm).y + offsetY;
    viewPortY = viewPort.y;
    viewPortH = viewPort.h;
    if (y < viewPort.y || y + 25 > viewPortY + viewPortH) {
      editor.getWin().scrollTo(0, y < viewPortY ? y : y - viewPortH + 25);
    }
  };
  var $_82tr6v67je5o2qyr = { scrollIntoView: scrollIntoView };

  var hasCeProperty = function (node) {
    return $_9owqcy1qje5o2q0e.isContentEditableTrue(node) || $_9owqcy1qje5o2q0e.isContentEditableFalse(node);
  };
  var findParent$1 = function (node, rootNode, predicate) {
    while (node && node !== rootNode) {
      if (predicate(node)) {
        return node;
      }
      node = node.parentNode;
    }
    return null;
  };
  var findClosestIeRange = function (clientX, clientY, doc) {
    var element, rng, rects;
    element = doc.elementFromPoint(clientX, clientY);
    rng = doc.body.createTextRange();
    if (!element || element.tagName === 'HTML') {
      element = doc.body;
    }
    rng.moveToElementText(element);
    rects = $_a5gegtjje5o2pum.toArray(rng.getClientRects());
    rects = rects.sort(function (a, b) {
      a = Math.abs(Math.max(a.top - clientY, a.bottom - clientY));
      b = Math.abs(Math.max(b.top - clientY, b.bottom - clientY));
      return a - b;
    });
    if (rects.length > 0) {
      clientY = (rects[0].bottom + rects[0].top) / 2;
      try {
        rng.moveToPoint(clientX, clientY);
        rng.collapse(true);
        return rng;
      } catch (ex) {
      }
    }
    return null;
  };
  var moveOutOfContentEditableFalse = function (rng, rootNode) {
    var parentElement = rng && rng.parentElement ? rng.parentElement() : null;
    return $_9owqcy1qje5o2q0e.isContentEditableFalse(findParent$1(parentElement, rootNode, hasCeProperty)) ? null : rng;
  };
  var fromPoint$1 = function (clientX, clientY, doc) {
    var rng, point;
    var pointDoc = doc;
    if (pointDoc.caretPositionFromPoint) {
      point = pointDoc.caretPositionFromPoint(clientX, clientY);
      if (point) {
        rng = doc.createRange();
        rng.setStart(point.offsetNode, point.offset);
        rng.collapse(true);
      }
    } else if (doc.caretRangeFromPoint) {
      rng = doc.caretRangeFromPoint(clientX, clientY);
    } else if (pointDoc.body.createTextRange) {
      rng = pointDoc.body.createTextRange();
      try {
        rng.moveToPoint(clientX, clientY);
        rng.collapse(true);
      } catch (ex) {
        rng = findClosestIeRange(clientX, clientY, doc);
      }
      return moveOutOfContentEditableFalse(rng, doc.body);
    }
    return rng;
  };
  var $_drs2u868je5o2qyu = { fromPoint: fromPoint$1 };

  var processRanges = function (editor, ranges) {
    return $_bwcteb4je5o2pl4.map(ranges, function (range) {
      var evt = editor.fire('GetSelectionRange', { range: range });
      return evt.range !== range ? evt.range : range;
    });
  };
  var $_fe3rbo69je5o2qzb = { processRanges: processRanges };

  var clone$2 = function (original, deep) {
    return $_5tcb2dyje5o2pwu.fromDom(original.dom().cloneNode(deep));
  };
  var shallow$1 = function (original) {
    return clone$2(original, false);
  };
  var deep$1 = function (original) {
    return clone$2(original, true);
  };
  var shallowAs = function (original, tag) {
    var nu = $_5tcb2dyje5o2pwu.fromTag(tag);
    var attributes = $_3uo9pl14je5o2pxq.clone(original);
    $_3uo9pl14je5o2pxq.setAll(nu, attributes);
    return nu;
  };
  var copy$1 = function (original, tag) {
    var nu = shallowAs(original, tag);
    var cloneChildren = $_a6mttx17je5o2pyd.children(deep$1(original));
    $_93ro7k2qje5o2qa4.append(nu, cloneChildren);
    return nu;
  };
  var mutate = function (original, tag) {
    var nu = shallowAs(original, tag);
    $_elbl2s2oje5o2q9z.before(original, nu);
    var children = $_a6mttx17je5o2pyd.children(original);
    $_93ro7k2qje5o2qa4.append(nu, children);
    $_cr1seh2pje5o2qa1.remove(original);
    return nu;
  };
  var $_cpi15k6cje5o2qzr = {
    shallow: shallow$1,
    shallowAs: shallowAs,
    deep: deep$1,
    copy: copy$1,
    mutate: mutate
  };

  var fromElements = function (elements, scope) {
    var doc = scope || document;
    var fragment = doc.createDocumentFragment();
    $_bwcteb4je5o2pl4.each(elements, function (element) {
      fragment.appendChild(element.dom());
    });
    return $_5tcb2dyje5o2pwu.fromDom(fragment);
  };
  var $_g8hjki6dje5o2qzt = { fromElements: fromElements };

  var getStartNode = function (rng) {
    var sc = rng.startContainer, so = rng.startOffset;
    if ($_9owqcy1qje5o2q0e.isText(sc)) {
      return so === 0 ? Option.some($_5tcb2dyje5o2pwu.fromDom(sc)) : Option.none();
    } else {
      return Option.from(sc.childNodes[so]).map($_5tcb2dyje5o2pwu.fromDom);
    }
  };
  var getEndNode = function (rng) {
    var ec = rng.endContainer, eo = rng.endOffset;
    if ($_9owqcy1qje5o2q0e.isText(ec)) {
      return eo === ec.data.length ? Option.some($_5tcb2dyje5o2pwu.fromDom(ec)) : Option.none();
    } else {
      return Option.from(ec.childNodes[eo - 1]).map($_5tcb2dyje5o2pwu.fromDom);
    }
  };
  var getFirstChildren = function (node) {
    return $_a6mttx17je5o2pyd.firstChild(node).fold($_enfhkv6je5o2ply.constant([node]), function (child) {
      return [node].concat(getFirstChildren(child));
    });
  };
  var getLastChildren$1 = function (node) {
    return $_a6mttx17je5o2pyd.lastChild(node).fold($_enfhkv6je5o2ply.constant([node]), function (child) {
      if ($_95wn8qzje5o2px0.name(child) === 'br') {
        return $_a6mttx17je5o2pyd.prevSibling(child).map(function (sibling) {
          return [node].concat(getLastChildren$1(sibling));
        }).getOr([]);
      } else {
        return [node].concat(getLastChildren$1(child));
      }
    });
  };
  var hasAllContentsSelected = function (elm, rng) {
    return $_c8weyc2eje5o2q85.liftN([
      getStartNode(rng),
      getEndNode(rng)
    ], function (startNode, endNode) {
      var start = $_bwcteb4je5o2pl4.find(getFirstChildren(elm), $_enfhkv6je5o2ply.curry($_7oppmo1dje5o2pz1.eq, startNode));
      var end = $_bwcteb4je5o2pl4.find(getLastChildren$1(elm), $_enfhkv6je5o2ply.curry($_7oppmo1dje5o2pz1.eq, endNode));
      return start.isSome() && end.isSome();
    }).getOr(false);
  };
  var moveEndPoint$1 = function (dom, rng, node, start) {
    var root = node, walker = new TreeWalker(node, root);
    var nonEmptyElementsMap = dom.schema.getNonEmptyElements();
    do {
      if (node.nodeType === 3 && $_a5gegtjje5o2pum.trim(node.nodeValue).length !== 0) {
        if (start) {
          rng.setStart(node, 0);
        } else {
          rng.setEnd(node, node.nodeValue.length);
        }
        return;
      }
      if (nonEmptyElementsMap[node.nodeName] && !/^(TD|TH)$/.test(node.nodeName)) {
        if (start) {
          rng.setStartBefore(node);
        } else {
          if (node.nodeName === 'BR') {
            rng.setEndBefore(node);
          } else {
            rng.setEndAfter(node);
          }
        }
        return;
      }
      if ($_9bd8pn9je5o2pp2.ie && $_9bd8pn9je5o2pp2.ie < 11 && dom.isBlock(node) && dom.isEmpty(node)) {
        if (start) {
          rng.setStart(node, 0);
        } else {
          rng.setEnd(node, 0);
        }
        return;
      }
    } while (node = start ? walker.next() : walker.prev());
    if (root.nodeName === 'BODY') {
      if (start) {
        rng.setStart(root, 0);
      } else {
        rng.setEnd(root, root.childNodes.length);
      }
    }
  };

  var tableModel = $_86drv18je5o2pyn.immutable('element', 'width', 'rows');
  var tableRow = $_86drv18je5o2pyn.immutable('element', 'cells');
  var cellPosition = $_86drv18je5o2pyn.immutable('x', 'y');
  var getSpan = function (td, key) {
    var value = parseInt($_3uo9pl14je5o2pxq.get(td, key), 10);
    return isNaN(value) ? 1 : value;
  };
  var fillout = function (table, x, y, tr, td) {
    var rowspan = getSpan(td, 'rowspan');
    var colspan = getSpan(td, 'colspan');
    var rows = table.rows();
    for (var y2 = y; y2 < y + rowspan; y2++) {
      if (!rows[y2]) {
        rows[y2] = tableRow($_cpi15k6cje5o2qzr.deep(tr), []);
      }
      for (var x2 = x; x2 < x + colspan; x2++) {
        var cells = rows[y2].cells();
        cells[x2] = y2 === y && x2 === x ? td : $_cpi15k6cje5o2qzr.shallow(td);
      }
    }
  };
  var cellExists = function (table, x, y) {
    var rows = table.rows();
    var cells = rows[y] ? rows[y].cells() : [];
    return !!cells[x];
  };
  var skipCellsX = function (table, x, y) {
    while (cellExists(table, x, y)) {
      x++;
    }
    return x;
  };
  var getWidth = function (rows) {
    return $_bwcteb4je5o2pl4.foldl(rows, function (acc, row) {
      return row.cells().length > acc ? row.cells().length : acc;
    }, 0);
  };
  var findElementPos = function (table, element) {
    var rows = table.rows();
    for (var y = 0; y < rows.length; y++) {
      var cells = rows[y].cells();
      for (var x = 0; x < cells.length; x++) {
        if ($_7oppmo1dje5o2pz1.eq(cells[x], element)) {
          return Option.some(cellPosition(x, y));
        }
      }
    }
    return Option.none();
  };
  var extractRows = function (table, sx, sy, ex, ey) {
    var newRows = [];
    var rows = table.rows();
    for (var y = sy; y <= ey; y++) {
      var cells = rows[y].cells();
      var slice = sx < ex ? cells.slice(sx, ex + 1) : cells.slice(ex, sx + 1);
      newRows.push(tableRow(rows[y].element(), slice));
    }
    return newRows;
  };
  var subTable = function (table, startPos, endPos) {
    var sx = startPos.x(), sy = startPos.y();
    var ex = endPos.x(), ey = endPos.y();
    var newRows = sy < ey ? extractRows(table, sx, sy, ex, ey) : extractRows(table, sx, ey, ex, sy);
    return tableModel(table.element(), getWidth(newRows), newRows);
  };
  var createDomTable = function (table, rows) {
    var tableElement = $_cpi15k6cje5o2qzr.shallow(table.element());
    var tableBody = $_5tcb2dyje5o2pwu.fromTag('tbody');
    $_93ro7k2qje5o2qa4.append(tableBody, rows);
    $_elbl2s2oje5o2q9z.append(tableElement, tableBody);
    return tableElement;
  };
  var modelRowsToDomRows = function (table) {
    return $_bwcteb4je5o2pl4.map(table.rows(), function (row) {
      var cells = $_bwcteb4je5o2pl4.map(row.cells(), function (cell) {
        var td = $_cpi15k6cje5o2qzr.deep(cell);
        $_3uo9pl14je5o2pxq.remove(td, 'colspan');
        $_3uo9pl14je5o2pxq.remove(td, 'rowspan');
        return td;
      });
      var tr = $_cpi15k6cje5o2qzr.shallow(row.element());
      $_93ro7k2qje5o2qa4.append(tr, cells);
      return tr;
    });
  };
  var fromDom$1 = function (tableElm) {
    var table = tableModel($_cpi15k6cje5o2qzr.shallow(tableElm), 0, []);
    $_bwcteb4je5o2pl4.each($_b4jhpu2gje5o2q8e.descendants(tableElm, 'tr'), function (tr, y) {
      $_bwcteb4je5o2pl4.each($_b4jhpu2gje5o2q8e.descendants(tr, 'td,th'), function (td, x) {
        fillout(table, skipCellsX(table, x, y), y, tr, td);
      });
    });
    return tableModel(table.element(), getWidth(table.rows()), table.rows());
  };
  var toDom = function (table) {
    return createDomTable(table, modelRowsToDomRows(table));
  };
  var subsection = function (table, startElement, endElement) {
    return findElementPos(table, startElement).bind(function (startPos) {
      return findElementPos(table, endElement).map(function (endPos) {
        return subTable(table, startPos, endPos);
      });
    });
  };
  var $_drw27r6fje5o2r07 = {
    fromDom: fromDom$1,
    toDom: toDom,
    subsection: subsection
  };

  var findParentListContainer = function (parents) {
    return $_bwcteb4je5o2pl4.find(parents, function (elm) {
      return $_95wn8qzje5o2px0.name(elm) === 'ul' || $_95wn8qzje5o2px0.name(elm) === 'ol';
    });
  };
  var getFullySelectedListWrappers = function (parents, rng) {
    return $_bwcteb4je5o2pl4.find(parents, function (elm) {
      return $_95wn8qzje5o2px0.name(elm) === 'li' && hasAllContentsSelected(elm, rng);
    }).fold($_enfhkv6je5o2ply.constant([]), function (li) {
      return findParentListContainer(parents).map(function (listCont) {
        return [
          $_5tcb2dyje5o2pwu.fromTag('li'),
          $_5tcb2dyje5o2pwu.fromTag($_95wn8qzje5o2px0.name(listCont))
        ];
      }).getOr([]);
    });
  };
  var wrap$3 = function (innerElm, elms) {
    var wrapped = $_bwcteb4je5o2pl4.foldl(elms, function (acc, elm) {
      $_elbl2s2oje5o2q9z.append(elm, acc);
      return elm;
    }, innerElm);
    return elms.length > 0 ? $_g8hjki6dje5o2qzt.fromElements([wrapped]) : wrapped;
  };
  var directListWrappers = function (commonAnchorContainer) {
    if (isListItem(commonAnchorContainer)) {
      return $_a6mttx17je5o2pyd.parent(commonAnchorContainer).filter(isList).fold($_enfhkv6je5o2ply.constant([]), function (listElm) {
        return [
          commonAnchorContainer,
          listElm
        ];
      });
    } else {
      return isList(commonAnchorContainer) ? [commonAnchorContainer] : [];
    }
  };
  var getWrapElements = function (rootNode, rng) {
    var commonAnchorContainer = $_5tcb2dyje5o2pwu.fromDom(rng.commonAncestorContainer);
    var parents = $_yqcmh39je5o2qcw.parentsAndSelf(commonAnchorContainer, rootNode);
    var wrapElements = $_bwcteb4je5o2pl4.filter(parents, function (elm) {
      return isInline(elm) || isHeading(elm);
    });
    var listWrappers = getFullySelectedListWrappers(parents, rng);
    var allWrappers = wrapElements.concat(listWrappers.length ? listWrappers : directListWrappers(commonAnchorContainer));
    return $_bwcteb4je5o2pl4.map(allWrappers, $_cpi15k6cje5o2qzr.shallow);
  };
  var emptyFragment = function () {
    return $_g8hjki6dje5o2qzt.fromElements([]);
  };
  var getFragmentFromRange = function (rootNode, rng) {
    return wrap$3($_5tcb2dyje5o2pwu.fromDom(rng.cloneContents()), getWrapElements(rootNode, rng));
  };
  var getParentTable = function (rootElm, cell) {
    return $_9fzewc37je5o2qck.ancestor(cell, 'table', $_enfhkv6je5o2ply.curry($_7oppmo1dje5o2pz1.eq, rootElm));
  };
  var getTableFragment = function (rootNode, selectedTableCells) {
    return getParentTable(rootNode, selectedTableCells[0]).bind(function (tableElm) {
      var firstCell = selectedTableCells[0];
      var lastCell = selectedTableCells[selectedTableCells.length - 1];
      var fullTableModel = $_drw27r6fje5o2r07.fromDom(tableElm);
      return $_drw27r6fje5o2r07.subsection(fullTableModel, firstCell, lastCell).map(function (sectionedTableModel) {
        return $_g8hjki6dje5o2qzt.fromElements([$_drw27r6fje5o2r07.toDom(sectionedTableModel)]);
      });
    }).getOrThunk(emptyFragment);
  };
  var getSelectionFragment = function (rootNode, ranges) {
    return ranges.length > 0 && ranges[0].collapsed ? emptyFragment() : getFragmentFromRange(rootNode, ranges[0]);
  };
  var read$3 = function (rootNode, ranges) {
    var selectedCells = $_9il6og3uje5o2qhd.getCellsFromElementOrRanges(ranges, rootNode);
    return selectedCells.length > 0 ? getTableFragment(rootNode, selectedCells) : getSelectionFragment(rootNode, ranges);
  };
  var $_51h1rc6bje5o2qzh = { read: read$3 };

  var getContent = function (editor, args) {
    var rng = editor.selection.getRng(), tmpElm = editor.dom.create('body');
    var sel = editor.selection.getSel();
    var fragment;
    var ranges = $_fe3rbo69je5o2qzb.processRanges(editor, $_b8hi2e3vje5o2qhh.getRanges(sel));
    args = args || {};
    args.get = true;
    args.format = args.format || 'html';
    args.selection = true;
    args = editor.fire('BeforeGetContent', args);
    if (args.isDefaultPrevented()) {
      editor.fire('GetContent', args);
      return args.content;
    }
    if (args.format === 'text') {
      return editor.selection.isCollapsed() ? '' : $_4md31c21je5o2q5f.trim(rng.text || (sel.toString ? sel.toString() : ''));
    }
    if (rng.cloneContents) {
      fragment = args.contextual ? $_51h1rc6bje5o2qzh.read($_5tcb2dyje5o2pwu.fromDom(editor.getBody()), ranges).dom() : rng.cloneContents();
      if (fragment) {
        tmpElm.appendChild(fragment);
      }
    } else if (rng.item !== undefined || rng.htmlText !== undefined) {
      tmpElm.innerHTML = '<br>' + (rng.item ? rng.item(0).outerHTML : rng.htmlText);
      tmpElm.removeChild(tmpElm.firstChild);
    } else {
      tmpElm.innerHTML = rng.toString();
    }
    args.getInner = true;
    var content = editor.selection.serializer.serialize(tmpElm, args);
    if (args.format === 'tree') {
      return content;
    }
    args.content = editor.selection.isCollapsed() ? '' : content;
    editor.fire('GetContent', args);
    return args.content;
  };
  var $_b349jo6aje5o2qzd = { getContent: getContent };

  var setContent = function (editor, content, args) {
    var rng = editor.selection.getRng(), caretNode;
    var doc = editor.getDoc();
    var frag, temp;
    args = args || { format: 'html' };
    args.set = true;
    args.selection = true;
    args.content = content;
    if (!args.no_events) {
      args = editor.fire('BeforeSetContent', args);
      if (args.isDefaultPrevented()) {
        editor.fire('SetContent', args);
        return;
      }
    }
    content = args.content;
    if (rng.insertNode) {
      content += '<span id="__caret">_</span>';
      if (rng.startContainer === doc && rng.endContainer === doc) {
        doc.body.innerHTML = content;
      } else {
        rng.deleteContents();
        if (doc.body.childNodes.length === 0) {
          doc.body.innerHTML = content;
        } else {
          if (rng.createContextualFragment) {
            rng.insertNode(rng.createContextualFragment(content));
          } else {
            frag = doc.createDocumentFragment();
            temp = doc.createElement('div');
            frag.appendChild(temp);
            temp.outerHTML = content;
            rng.insertNode(frag);
          }
        }
      }
      caretNode = editor.dom.get('__caret');
      rng = doc.createRange();
      rng.setStartBefore(caretNode);
      rng.setEndBefore(caretNode);
      editor.selection.setRng(rng);
      editor.dom.remove('__caret');
      try {
        editor.selection.setRng(rng);
      } catch (ex) {
      }
    } else {
      if (rng.item) {
        doc.execCommand('Delete', false, null);
        rng = editor.getRng();
      }
      if (/^\s+/.test(content)) {
        rng.pasteHTML('<span id="__mce_tmp">_</span>' + content);
        editor.dom.remove('__mce_tmp');
      } else {
        rng.pasteHTML(content);
      }
    }
    if (!args.no_events) {
      editor.fire('SetContent', args);
    }
  };
  var $_e4xow86gje5o2r0i = { setContent: setContent };

  var getEndpointElement = function (root, rng, start, real, resolve) {
    var container = start ? rng.startContainer : rng.endContainer;
    var offset = start ? rng.startOffset : rng.endOffset;
    return Option.from(container).map($_5tcb2dyje5o2pwu.fromDom).map(function (elm) {
      return !real || !rng.collapsed ? $_a6mttx17je5o2pyd.child(elm, resolve(elm, offset)).getOr(elm) : elm;
    }).bind(function (elm) {
      return $_95wn8qzje5o2px0.isElement(elm) ? Option.some(elm) : $_a6mttx17je5o2pyd.parent(elm);
    }).map(function (elm) {
      return elm.dom();
    }).getOr(root);
  };
  var getStart$2 = function (root, rng, real) {
    return getEndpointElement(root, rng, true, real, function (elm, offset) {
      return Math.min($_a6mttx17je5o2pyd.childNodesCount(elm), offset);
    });
  };
  var getEnd = function (root, rng, real) {
    return getEndpointElement(root, rng, false, real, function (elm, offset) {
      return offset > 0 ? offset - 1 : offset;
    });
  };
  var skipEmptyTextNodes = function (node, forwards) {
    var orig = node;
    while (node && $_9owqcy1qje5o2q0e.isText(node) && node.length === 0) {
      node = forwards ? node.nextSibling : node.previousSibling;
    }
    return node || orig;
  };
  var getNode$1 = function (root, rng) {
    var elm, startContainer, endContainer, startOffset, endOffset;
    if (!rng) {
      return root;
    }
    startContainer = rng.startContainer;
    endContainer = rng.endContainer;
    startOffset = rng.startOffset;
    endOffset = rng.endOffset;
    elm = rng.commonAncestorContainer;
    if (!rng.collapsed) {
      if (startContainer === endContainer) {
        if (endOffset - startOffset < 2) {
          if (startContainer.hasChildNodes()) {
            elm = startContainer.childNodes[startOffset];
          }
        }
      }
      if (startContainer.nodeType === 3 && endContainer.nodeType === 3) {
        if (startContainer.length === startOffset) {
          startContainer = skipEmptyTextNodes(startContainer.nextSibling, true);
        } else {
          startContainer = startContainer.parentNode;
        }
        if (endOffset === 0) {
          endContainer = skipEmptyTextNodes(endContainer.previousSibling, false);
        } else {
          endContainer = endContainer.parentNode;
        }
        if (startContainer && startContainer === endContainer) {
          return startContainer;
        }
      }
    }
    if (elm && elm.nodeType === 3) {
      return elm.parentNode;
    }
    return elm;
  };
  var getSelectedBlocks = function (dom, rng, startElm, endElm) {
    var node, root;
    var selectedBlocks = [];
    root = dom.getRoot();
    startElm = dom.getParent(startElm || getStart$2(root, rng, false), dom.isBlock);
    endElm = dom.getParent(endElm || getEnd(root, rng, false), dom.isBlock);
    if (startElm && startElm !== root) {
      selectedBlocks.push(startElm);
    }
    if (startElm && endElm && startElm !== endElm) {
      node = startElm;
      var walker = new TreeWalker(startElm, root);
      while ((node = walker.next()) && node !== endElm) {
        if (dom.isBlock(node)) {
          selectedBlocks.push(node);
        }
      }
    }
    if (endElm && startElm !== endElm && endElm !== root) {
      selectedBlocks.push(endElm);
    }
    return selectedBlocks;
  };
  var select$1 = function (dom, node, content) {
    return Option.from(node).map(function (node) {
      var idx = dom.nodeIndex(node);
      var rng = dom.createRng();
      rng.setStart(node.parentNode, idx);
      rng.setEnd(node.parentNode, idx + 1);
      if (content) {
        moveEndPoint$1(dom, rng, node, true);
        moveEndPoint$1(dom, rng, node, false);
      }
      return rng;
    });
  };

  var each$20 = $_a5gegtjje5o2pum.each;
  var isNativeIeSelection = function (rng) {
    return !!rng.select;
  };
  var isAttachedToDom = function (node) {
    return !!(node && node.ownerDocument) && $_7oppmo1dje5o2pz1.contains($_5tcb2dyje5o2pwu.fromDom(node.ownerDocument), $_5tcb2dyje5o2pwu.fromDom(node));
  };
  var isValidRange = function (rng) {
    if (!rng) {
      return false;
    } else if (isNativeIeSelection(rng)) {
      return true;
    } else {
      return isAttachedToDom(rng.startContainer) && isAttachedToDom(rng.endContainer);
    }
  };
  var Selection$1 = function (dom, win, serializer, editor) {
    var bookmarkManager, controlSelection;
    var selectedRange, explicitRange, selectorChangedData;
    var setCursorLocation = function (node, offset) {
      var rng = dom.createRng();
      if (!node) {
        moveEndPoint$1(dom, rng, editor.getBody(), true);
        setRng(rng);
      } else {
        rng.setStart(node, offset);
        rng.setEnd(node, offset);
        setRng(rng);
        collapse(false);
      }
    };
    var getContent = function (args) {
      return $_b349jo6aje5o2qzd.getContent(editor, args);
    };
    var setContent = function (content, args) {
      return $_e4xow86gje5o2r0i.setContent(editor, content, args);
    };
    var getStart = function (real) {
      return getStart$2(editor.getBody(), getRng(), real);
    };
    var getEnd$$1 = function (real) {
      return getEnd(editor.getBody(), getRng(), real);
    };
    var getBookmark = function (type, normalized) {
      return bookmarkManager.getBookmark(type, normalized);
    };
    var moveToBookmark = function (bookmark) {
      return bookmarkManager.moveToBookmark(bookmark);
    };
    var select = function (node, content) {
      select$1(dom, node, content).each(setRng);
      return node;
    };
    var isCollapsed = function () {
      var rng = getRng(), sel = getSel();
      if (!rng || rng.item) {
        return false;
      }
      if (rng.compareEndPoints) {
        return rng.compareEndPoints('StartToEnd', rng) === 0;
      }
      return !sel || rng.collapsed;
    };
    var collapse = function (toStart) {
      var rng = getRng();
      rng.collapse(!!toStart);
      setRng(rng);
    };
    var getSel = function () {
      return win.getSelection ? win.getSelection() : win.document.selection;
    };
    var getRng = function () {
      var selection, rng, elm, doc;
      var tryCompareBoundaryPoints = function (how, sourceRange, destinationRange) {
        try {
          return sourceRange.compareBoundaryPoints(how, destinationRange);
        } catch (ex) {
          return -1;
        }
      };
      if (!win) {
        return null;
      }
      doc = win.document;
      if (typeof doc === 'undefined' || doc === null) {
        return null;
      }
      if (editor.bookmark !== undefined && $_llx5i48je5o2qna.hasFocus(editor) === false) {
        var bookmark = $_fnd7h33zje5o2qly.getRng(editor);
        if (bookmark.isSome()) {
          return bookmark.map(function (r) {
            return $_fe3rbo69je5o2qzb.processRanges(editor, [r])[0];
          }).getOr(doc.createRange());
        }
      }
      try {
        if (selection = getSel()) {
          if (selection.rangeCount > 0) {
            rng = selection.getRangeAt(0);
          } else {
            rng = selection.createRange ? selection.createRange() : doc.createRange();
          }
        }
      } catch (ex) {
      }
      rng = $_fe3rbo69je5o2qzb.processRanges(editor, [rng])[0];
      if (!rng) {
        rng = doc.createRange ? doc.createRange() : doc.body.createTextRange();
      }
      if (rng.setStart && rng.startContainer.nodeType === 9 && rng.collapsed) {
        elm = dom.getRoot();
        rng.setStart(elm, 0);
        rng.setEnd(elm, 0);
      }
      if (selectedRange && explicitRange) {
        if (tryCompareBoundaryPoints(rng.START_TO_START, rng, selectedRange) === 0 && tryCompareBoundaryPoints(rng.END_TO_END, rng, selectedRange) === 0) {
          rng = explicitRange;
        } else {
          selectedRange = null;
          explicitRange = null;
        }
      }
      return rng;
    };
    var setRng = function (rng, forward) {
      var sel, node, evt;
      if (!isValidRange(rng)) {
        return;
      }
      var ieRange = isNativeIeSelection(rng) ? rng : null;
      if (ieRange) {
        explicitRange = null;
        try {
          ieRange.select();
        } catch (ex) {
        }
        return;
      }
      sel = getSel();
      evt = editor.fire('SetSelectionRange', {
        range: rng,
        forward: forward
      });
      rng = evt.range;
      if (sel) {
        explicitRange = rng;
        try {
          sel.removeAllRanges();
          sel.addRange(rng);
        } catch (ex) {
        }
        if (forward === false && sel.extend) {
          sel.collapse(rng.endContainer, rng.endOffset);
          sel.extend(rng.startContainer, rng.startOffset);
        }
        selectedRange = sel.rangeCount > 0 ? sel.getRangeAt(0) : null;
      }
      if (!rng.collapsed && rng.startContainer === rng.endContainer && sel.setBaseAndExtent && !$_9bd8pn9je5o2pp2.ie) {
        if (rng.endOffset - rng.startOffset < 2) {
          if (rng.startContainer.hasChildNodes()) {
            node = rng.startContainer.childNodes[rng.startOffset];
            if (node && node.tagName === 'IMG') {
              sel.setBaseAndExtent(rng.startContainer, rng.startOffset, rng.endContainer, rng.endOffset);
              if (sel.anchorNode !== rng.startContainer || sel.focusNode !== rng.endContainer) {
                sel.setBaseAndExtent(node, 0, node, 1);
              }
            }
          }
        }
      }
      editor.fire('AfterSetSelectionRange', {
        range: rng,
        forward: forward
      });
    };
    var setNode = function (elm) {
      setContent(dom.getOuterHTML(elm));
      return elm;
    };
    var getNode = function () {
      return getNode$1(editor.getBody(), getRng());
    };
    var getSelectedBlocks$$1 = function (startElm, endElm) {
      return getSelectedBlocks(dom, getRng(), startElm, endElm);
    };
    var isForward = function () {
      var sel = getSel();
      var anchorRange, focusRange;
      if (!sel || !sel.anchorNode || !sel.focusNode) {
        return true;
      }
      anchorRange = dom.createRng();
      anchorRange.setStart(sel.anchorNode, sel.anchorOffset);
      anchorRange.collapse(true);
      focusRange = dom.createRng();
      focusRange.setStart(sel.focusNode, sel.focusOffset);
      focusRange.collapse(true);
      return anchorRange.compareBoundaryPoints(anchorRange.START_TO_START, focusRange) <= 0;
    };
    var normalize = function () {
      var rng = getRng();
      if (!$_b8hi2e3vje5o2qhh.hasMultipleRanges(getSel())) {
        var normRng = $_1t8l6x3xje5o2qlk.normalize(dom, rng);
        normRng.each(function (normRng) {
          setRng(normRng, isForward());
        });
        return normRng.getOr(rng);
      }
      return rng;
    };
    var selectorChanged = function (selector, callback) {
      var currentSelectors;
      if (!selectorChangedData) {
        selectorChangedData = {};
        currentSelectors = {};
        editor.on('NodeChange', function (e) {
          var node = e.element, parents = dom.getParents(node, null, dom.getRoot()), matchedSelectors = {};
          each$20(selectorChangedData, function (callbacks, selector) {
            each$20(parents, function (node) {
              if (dom.is(node, selector)) {
                if (!currentSelectors[selector]) {
                  each$20(callbacks, function (callback) {
                    callback(true, {
                      node: node,
                      selector: selector,
                      parents: parents
                    });
                  });
                  currentSelectors[selector] = callbacks;
                }
                matchedSelectors[selector] = callbacks;
                return false;
              }
            });
          });
          each$20(currentSelectors, function (callbacks, selector) {
            if (!matchedSelectors[selector]) {
              delete currentSelectors[selector];
              each$20(callbacks, function (callback) {
                callback(false, {
                  node: node,
                  selector: selector,
                  parents: parents
                });
              });
            }
          });
        });
      }
      if (!selectorChangedData[selector]) {
        selectorChangedData[selector] = [];
      }
      selectorChangedData[selector].push(callback);
      return exports;
    };
    var getScrollContainer = function () {
      var scrollContainer, node = dom.getRoot();
      while (node && node.nodeName !== 'BODY') {
        if (node.scrollHeight > node.clientHeight) {
          scrollContainer = node;
          break;
        }
        node = node.parentNode;
      }
      return scrollContainer;
    };
    var scrollIntoView = function (elm, alignToTop) {
      return $_82tr6v67je5o2qyr.scrollIntoView(editor, elm, alignToTop);
    };
    var placeCaretAt = function (clientX, clientY) {
      return setRng($_drs2u868je5o2qyu.fromPoint(clientX, clientY, editor.getDoc()));
    };
    var getBoundingClientRect = function () {
      var rng = getRng();
      return rng.collapsed ? CaretPosition$1.fromRangeStart(rng).getClientRects()[0] : rng.getBoundingClientRect();
    };
    var destroy = function () {
      win = selectedRange = explicitRange = null;
      controlSelection.destroy();
    };
    var exports = {
      bookmarkManager: null,
      controlSelection: null,
      dom: dom,
      win: win,
      serializer: serializer,
      editor: editor,
      collapse: collapse,
      setCursorLocation: setCursorLocation,
      getContent: getContent,
      setContent: setContent,
      getBookmark: getBookmark,
      moveToBookmark: moveToBookmark,
      select: select,
      isCollapsed: isCollapsed,
      isForward: isForward,
      setNode: setNode,
      getNode: getNode,
      getSel: getSel,
      setRng: setRng,
      getRng: getRng,
      getStart: getStart,
      getEnd: getEnd$$1,
      getSelectedBlocks: getSelectedBlocks$$1,
      normalize: normalize,
      selectorChanged: selectorChanged,
      getScrollContainer: getScrollContainer,
      scrollIntoView: scrollIntoView,
      placeCaretAt: placeCaretAt,
      getBoundingClientRect: getBoundingClientRect,
      destroy: destroy
    };
    bookmarkManager = BookmarkManager$1(exports);
    controlSelection = ControlSelection(exports, editor);
    exports.bookmarkManager = bookmarkManager;
    exports.controlSelection = controlSelection;
    return exports;
  };

  var isContentEditableFalse$11 = $_9owqcy1qje5o2q0e.isContentEditableFalse;
  var getSelectedNode$1 = getSelectedNode;
  var isAfterContentEditableFalse$2 = isAfterContentEditableFalse;
  var isBeforeContentEditableFalse$2 = isBeforeContentEditableFalse;
  var getVisualCaretPosition = function (walkFn, caretPosition) {
    while (caretPosition = walkFn(caretPosition)) {
      if (caretPosition.isVisible()) {
        return caretPosition;
      }
    }
    return caretPosition;
  };
  var isMoveInsideSameBlock = function (from, to) {
    var inSameBlock = isInSameBlock(from, to);
    if (!inSameBlock && $_9owqcy1qje5o2q0e.isBr(from.getNode())) {
      return true;
    }
    return inSameBlock;
  };
  var moveToCeFalseHorizontally = function (direction, editor, getNextPosFn, range) {
    var node, caretPosition, peekCaretPosition, rangeIsInContainerBlock;
    var forwards = direction === HDirection.Forwards;
    var isBeforeContentEditableFalseFn = forwards ? isBeforeContentEditableFalse$2 : isAfterContentEditableFalse$2;
    if (!range.collapsed) {
      node = getSelectedNode$1(range);
      if (isContentEditableFalse$11(node)) {
        return showCaret(direction, editor, node, direction === HDirection.Backwards, true);
      }
    }
    rangeIsInContainerBlock = isRangeInCaretContainerBlock(range);
    caretPosition = getNormalizedRangeEndPoint(direction, editor.getBody(), range);
    if (isBeforeContentEditableFalseFn(caretPosition)) {
      return selectNode(editor, caretPosition.getNode(!forwards));
    }
    caretPosition = getNextPosFn(caretPosition);
    if (!caretPosition) {
      if (rangeIsInContainerBlock) {
        return range;
      }
      return null;
    }
    if (isBeforeContentEditableFalseFn(caretPosition)) {
      return showCaret(direction, editor, caretPosition.getNode(!forwards), forwards, true);
    }
    peekCaretPosition = getNextPosFn(caretPosition);
    if (isBeforeContentEditableFalseFn(peekCaretPosition)) {
      if (isMoveInsideSameBlock(caretPosition, peekCaretPosition)) {
        return showCaret(direction, editor, peekCaretPosition.getNode(!forwards), forwards, true);
      }
    }
    if (rangeIsInContainerBlock) {
      return renderRangeCaret(editor, caretPosition.toRange(), true);
    }
    return null;
  };
  var moveToCeFalseVertically = function (direction, editor, walkerFn, range) {
    var caretPosition, linePositions, nextLinePositions;
    var closestNextLineRect, caretClientRect, clientX;
    var dist1, dist2, contentEditableFalseNode;
    contentEditableFalseNode = getSelectedNode$1(range);
    caretPosition = getNormalizedRangeEndPoint(direction, editor.getBody(), range);
    linePositions = walkerFn(editor.getBody(), isAboveLine(1), caretPosition);
    nextLinePositions = $_c2vc3pkje5o2puw.filter(linePositions, isLine(1));
    caretClientRect = $_c2vc3pkje5o2puw.last(caretPosition.getClientRects());
    if (isBeforeContentEditableFalse$2(caretPosition) || isBeforeTable(caretPosition)) {
      contentEditableFalseNode = caretPosition.getNode();
    }
    if (isAfterContentEditableFalse$2(caretPosition) || isAfterTable(caretPosition)) {
      contentEditableFalseNode = caretPosition.getNode(true);
    }
    if (!caretClientRect) {
      return null;
    }
    clientX = caretClientRect.left;
    closestNextLineRect = findClosestClientRect(nextLinePositions, clientX);
    if (closestNextLineRect) {
      if (isContentEditableFalse$11(closestNextLineRect.node)) {
        dist1 = Math.abs(clientX - closestNextLineRect.left);
        dist2 = Math.abs(clientX - closestNextLineRect.right);
        return showCaret(direction, editor, closestNextLineRect.node, dist1 < dist2, true);
      }
    }
    if (contentEditableFalseNode) {
      var caretPositions = positionsUntil(direction, editor.getBody(), isAboveLine(1), contentEditableFalseNode);
      closestNextLineRect = findClosestClientRect($_c2vc3pkje5o2puw.filter(caretPositions, isLine(1)), clientX);
      if (closestNextLineRect) {
        return renderRangeCaret(editor, closestNextLineRect.position.toRange(), true);
      }
      closestNextLineRect = $_c2vc3pkje5o2puw.last($_c2vc3pkje5o2puw.filter(caretPositions, isLine(0)));
      if (closestNextLineRect) {
        return renderRangeCaret(editor, closestNextLineRect.position.toRange(), true);
      }
    }
  };
  var createTextBlock = function (editor) {
    var textBlock = editor.dom.create(editor.settings.forced_root_block);
    if (!$_9bd8pn9je5o2pp2.ie || $_9bd8pn9je5o2pp2.ie >= 11) {
      textBlock.innerHTML = '<br data-mce-bogus="1">';
    }
    return textBlock;
  };
  var exitPreBlock = function (editor, direction, range) {
    var pre, caretPos, newBlock;
    var caretWalker = CaretWalker(editor.getBody());
    var getNextVisualCaretPosition = $_ghq3nq25je5o2q5q.curry(getVisualCaretPosition, caretWalker.next);
    var getPrevVisualCaretPosition = $_ghq3nq25je5o2q5q.curry(getVisualCaretPosition, caretWalker.prev);
    if (range.collapsed && editor.settings.forced_root_block) {
      pre = editor.dom.getParent(range.startContainer, 'PRE');
      if (!pre) {
        return;
      }
      if (direction === 1) {
        caretPos = getNextVisualCaretPosition(CaretPosition$1.fromRangeStart(range));
      } else {
        caretPos = getPrevVisualCaretPosition(CaretPosition$1.fromRangeStart(range));
      }
      if (!caretPos) {
        newBlock = createTextBlock(editor);
        if (direction === 1) {
          editor.$(pre).after(newBlock);
        } else {
          editor.$(pre).before(newBlock);
        }
        editor.selection.select(newBlock, true);
        editor.selection.collapse();
      }
    }
  };
  var getHorizontalRange = function (editor, forward) {
    var caretWalker = CaretWalker(editor.getBody());
    var getNextVisualCaretPosition = $_ghq3nq25je5o2q5q.curry(getVisualCaretPosition, caretWalker.next);
    var getPrevVisualCaretPosition = $_ghq3nq25je5o2q5q.curry(getVisualCaretPosition, caretWalker.prev);
    var newRange;
    var direction = forward ? HDirection.Forwards : HDirection.Backwards;
    var getNextPosFn = forward ? getNextVisualCaretPosition : getPrevVisualCaretPosition;
    var range = editor.selection.getRng();
    newRange = moveToCeFalseHorizontally(direction, editor, getNextPosFn, range);
    if (newRange) {
      return newRange;
    }
    newRange = exitPreBlock(editor, direction, range);
    if (newRange) {
      return newRange;
    }
    return null;
  };
  var getVerticalRange = function (editor, down) {
    var newRange;
    var direction = down ? 1 : -1;
    var walkerFn = down ? downUntil : upUntil;
    var range = editor.selection.getRng();
    newRange = moveToCeFalseVertically(direction, editor, walkerFn, range);
    if (newRange) {
      return newRange;
    }
    newRange = exitPreBlock(editor, direction, range);
    if (newRange) {
      return newRange;
    }
    return null;
  };
  var moveH$1 = function (editor, forward) {
    return function () {
      var newRng = getHorizontalRange(editor, forward);
      if (newRng) {
        editor.selection.setRng(newRng);
        return true;
      } else {
        return false;
      }
    };
  };
  var moveV$1 = function (editor, down) {
    return function () {
      var newRng = getVerticalRange(editor, down);
      if (newRng) {
        editor.selection.setRng(newRng);
        return true;
      } else {
        return false;
      }
    };
  };

  var defaultPatterns = function (patterns) {
    return $_bwcteb4je5o2pl4.map(patterns, function (pattern) {
      return $_atjucy5uje5o2qw2.merge({
        shiftKey: false,
        altKey: false,
        ctrlKey: false,
        metaKey: false,
        keyCode: 0,
        action: $_enfhkv6je5o2ply.noop
      }, pattern);
    });
  };
  var matchesEvent = function (pattern, evt) {
    return evt.keyCode === pattern.keyCode && evt.shiftKey === pattern.shiftKey && evt.altKey === pattern.altKey && evt.ctrlKey === pattern.ctrlKey && evt.metaKey === pattern.metaKey;
  };
  var match$1 = function (patterns, evt) {
    return $_bwcteb4je5o2pl4.bind(defaultPatterns(patterns), function (pattern) {
      return matchesEvent(pattern, evt) ? [pattern] : [];
    });
  };
  var action = function (f) {
    var x = [];
    for (var _i = 1; _i < arguments.length; _i++) {
      x[_i - 1] = arguments[_i];
    }
    var args = Array.prototype.slice.call(arguments, 1);
    return function () {
      return f.apply(null, args);
    };
  };
  var execute = function (patterns, evt) {
    return $_bwcteb4je5o2pl4.find(match$1(patterns, evt), function (pattern) {
      return pattern.action();
    });
  };
  var $_fc81iw6lje5o2r1d = {
    match: match$1,
    action: action,
    execute: execute
  };

  var executeKeydownOverride = function (editor, caret, evt) {
    var os = $_2hq237mje5o2pvu.detect().os;
    $_fc81iw6lje5o2r1d.execute([
      {
        keyCode: $_99mhlk59je5o2qss.RIGHT,
        action: moveH$1(editor, true)
      },
      {
        keyCode: $_99mhlk59je5o2qss.LEFT,
        action: moveH$1(editor, false)
      },
      {
        keyCode: $_99mhlk59je5o2qss.UP,
        action: moveV$1(editor, false)
      },
      {
        keyCode: $_99mhlk59je5o2qss.DOWN,
        action: moveV$1(editor, true)
      },
      {
        keyCode: $_99mhlk59je5o2qss.RIGHT,
        action: moveH(editor, true)
      },
      {
        keyCode: $_99mhlk59je5o2qss.LEFT,
        action: moveH(editor, false)
      },
      {
        keyCode: $_99mhlk59je5o2qss.UP,
        action: moveV(editor, false)
      },
      {
        keyCode: $_99mhlk59je5o2qss.DOWN,
        action: moveV(editor, true)
      },
      {
        keyCode: $_99mhlk59je5o2qss.RIGHT,
        action: $_2dnted3pje5o2qgc.move(editor, caret, true)
      },
      {
        keyCode: $_99mhlk59je5o2qss.LEFT,
        action: $_2dnted3pje5o2qgc.move(editor, caret, false)
      },
      {
        keyCode: $_99mhlk59je5o2qss.RIGHT,
        ctrlKey: !os.isOSX(),
        altKey: os.isOSX(),
        action: $_2dnted3pje5o2qgc.moveNextWord(editor, caret)
      },
      {
        keyCode: $_99mhlk59je5o2qss.LEFT,
        ctrlKey: !os.isOSX(),
        altKey: os.isOSX(),
        action: $_2dnted3pje5o2qgc.movePrevWord(editor, caret)
      }
    ], evt).each(function (_) {
      evt.preventDefault();
    });
  };
  var setup$6 = function (editor, caret) {
    editor.on('keydown', function (evt) {
      if (evt.isDefaultPrevented() === false) {
        executeKeydownOverride(editor, caret, evt);
      }
    });
  };
  var $_a5oax76jje5o2r0t = { setup: setup$6 };

  var getParentInlines = function (rootElm, startElm) {
    var parents = $_yqcmh39je5o2qcw.parentsAndSelf(startElm, rootElm);
    return $_bwcteb4je5o2pl4.findIndex(parents, isBlock).fold($_enfhkv6je5o2ply.constant(parents), function (index) {
      return parents.slice(0, index);
    });
  };
  var hasOnlyOneChild$1 = function (elm) {
    return $_a6mttx17je5o2pyd.children(elm).length === 1;
  };
  var deleteLastPosition = function (forward, editor, target, parentInlines) {
    var isFormatElement = $_enfhkv6je5o2ply.curry($_4066yu3jje5o2qev.isFormatElement, editor);
    var formatNodes = $_bwcteb4je5o2pl4.map($_bwcteb4je5o2pl4.filter(parentInlines, isFormatElement), function (elm) {
      return elm.dom();
    });
    if (formatNodes.length === 0) {
      $_8fuvl23eje5o2qdv.deleteElement(editor, forward, target);
    } else {
      var pos = $_4066yu3jje5o2qev.replaceWithCaretFormat(target.dom(), formatNodes);
      editor.selection.setRng(pos.toRange());
    }
  };
  var deleteCaret$1 = function (editor, forward) {
    var rootElm = $_5tcb2dyje5o2pwu.fromDom(editor.getBody());
    var startElm = $_5tcb2dyje5o2pwu.fromDom(editor.selection.getStart());
    var parentInlines = $_bwcteb4je5o2pl4.filter(getParentInlines(rootElm, startElm), hasOnlyOneChild$1);
    return $_bwcteb4je5o2pl4.last(parentInlines).map(function (target) {
      var fromPos = CaretPosition$1.fromRangeStart(editor.selection.getRng());
      if ($_7twey62zje5o2qay.willDeleteLastPositionInElement(forward, fromPos, target.dom())) {
        deleteLastPosition(forward, editor, target, parentInlines);
        return true;
      } else {
        return false;
      }
    }).getOr(false);
  };
  var backspaceDelete$5 = function (editor, forward) {
    return editor.selection.isCollapsed() ? deleteCaret$1(editor, forward) : false;
  };
  var $_f5ktyq6nje5o2r1k = { backspaceDelete: backspaceDelete$5 };

  var executeKeydownOverride$1 = function (editor, caret, evt) {
    $_fc81iw6lje5o2r1d.execute([
      {
        keyCode: $_99mhlk59je5o2qss.BACKSPACE,
        action: $_fc81iw6lje5o2r1d.action($_c7csnv3bje5o2qd6.backspaceDelete, editor, false)
      },
      {
        keyCode: $_99mhlk59je5o2qss.DELETE,
        action: $_fc81iw6lje5o2r1d.action($_c7csnv3bje5o2qd6.backspaceDelete, editor, true)
      },
      {
        keyCode: $_99mhlk59je5o2qss.BACKSPACE,
        action: $_fc81iw6lje5o2r1d.action($_bzwx3b3fje5o2qe7.backspaceDelete, editor, caret, false)
      },
      {
        keyCode: $_99mhlk59je5o2qss.DELETE,
        action: $_fc81iw6lje5o2r1d.action($_bzwx3b3fje5o2qe7.backspaceDelete, editor, caret, true)
      },
      {
        keyCode: $_99mhlk59je5o2qss.BACKSPACE,
        action: $_fc81iw6lje5o2r1d.action($_a476lo3aje5o2qd0.backspaceDelete, editor, false)
      },
      {
        keyCode: $_99mhlk59je5o2qss.DELETE,
        action: $_fc81iw6lje5o2r1d.action($_a476lo3aje5o2qd0.backspaceDelete, editor, true)
      },
      {
        keyCode: $_99mhlk59je5o2qss.BACKSPACE,
        action: $_fc81iw6lje5o2r1d.action($_3qfe0j2xje5o2qao.backspaceDelete, editor, false)
      },
      {
        keyCode: $_99mhlk59je5o2qss.DELETE,
        action: $_fc81iw6lje5o2r1d.action($_3qfe0j2xje5o2qao.backspaceDelete, editor, true)
      },
      {
        keyCode: $_99mhlk59je5o2qss.BACKSPACE,
        action: $_fc81iw6lje5o2r1d.action($_8pfx7e3sje5o2qgp.backspaceDelete, editor, false)
      },
      {
        keyCode: $_99mhlk59je5o2qss.DELETE,
        action: $_fc81iw6lje5o2r1d.action($_8pfx7e3sje5o2qgp.backspaceDelete, editor, true)
      },
      {
        keyCode: $_99mhlk59je5o2qss.BACKSPACE,
        action: $_fc81iw6lje5o2r1d.action($_f5ktyq6nje5o2r1k.backspaceDelete, editor, false)
      },
      {
        keyCode: $_99mhlk59je5o2qss.DELETE,
        action: $_fc81iw6lje5o2r1d.action($_f5ktyq6nje5o2r1k.backspaceDelete, editor, true)
      }
    ], evt).each(function (_) {
      evt.preventDefault();
    });
  };
  var executeKeyupOverride = function (editor, evt) {
    $_fc81iw6lje5o2r1d.execute([
      {
        keyCode: $_99mhlk59je5o2qss.BACKSPACE,
        action: $_fc81iw6lje5o2r1d.action($_c7csnv3bje5o2qd6.paddEmptyElement, editor)
      },
      {
        keyCode: $_99mhlk59je5o2qss.DELETE,
        action: $_fc81iw6lje5o2r1d.action($_c7csnv3bje5o2qd6.paddEmptyElement, editor)
      }
    ], evt);
  };
  var setup$7 = function (editor, caret) {
    editor.on('keydown', function (evt) {
      if (evt.isDefaultPrevented() === false) {
        executeKeydownOverride$1(editor, caret, evt);
      }
    });
    editor.on('keyup', function (evt) {
      if (evt.isDefaultPrevented() === false) {
        executeKeyupOverride(editor, evt);
      }
    });
  };
  var $_6wiawx6mje5o2r1h = { setup: setup$7 };

  var getBodySetting = function (editor, name, defaultValue) {
    var value = editor.getParam(name, defaultValue);
    if (value.indexOf('=') !== -1) {
      var bodyObj = editor.getParam(name, '', 'hash');
      return bodyObj.hasOwnProperty(editor.id) ? bodyObj[editor.id] : defaultValue;
    } else {
      return value;
    }
  };
  var getIframeAttrs = function (editor) {
    return editor.getParam('iframe_attrs', {});
  };
  var getDocType = function (editor) {
    return editor.getParam('doctype', '<!DOCTYPE html>');
  };
  var getDocumentBaseUrl = function (editor) {
    return editor.getParam('document_base_url', '');
  };
  var getBodyId = function (editor) {
    return getBodySetting(editor, 'body_id', 'tinymce');
  };
  var getBodyClass = function (editor) {
    return getBodySetting(editor, 'body_class', '');
  };
  var getContentSecurityPolicy = function (editor) {
    return editor.getParam('content_security_policy', '');
  };
  var shouldPutBrInPre = function (editor) {
    return editor.getParam('br_in_pre', true);
  };
  var getForcedRootBlock = function (editor) {
    if (editor.getParam('force_p_newlines', false)) {
      return 'p';
    }
    var block = editor.getParam('forced_root_block', 'p');
    return block === false ? '' : block;
  };
  var getForcedRootBlockAttrs = function (editor) {
    return editor.getParam('forced_root_block_attrs', {});
  };
  var getBrNewLineSelector = function (editor) {
    return editor.getParam('br_newline_selector', '.mce-toc h2,figcaption,caption');
  };
  var getNoNewLineSelector = function (editor) {
    return editor.getParam('no_newline_selector', '');
  };
  var shouldKeepStyles = function (editor) {
    return editor.getParam('keep_styles', true);
  };
  var shouldEndContainerOnEmtpyBlock = function (editor) {
    return editor.getParam('end_container_on_empty_block', false);
  };
  var $_ahj8826rje5o2r2f = {
    getIframeAttrs: getIframeAttrs,
    getDocType: getDocType,
    getDocumentBaseUrl: getDocumentBaseUrl,
    getBodyId: getBodyId,
    getBodyClass: getBodyClass,
    getContentSecurityPolicy: getContentSecurityPolicy,
    shouldPutBrInPre: shouldPutBrInPre,
    getForcedRootBlock: getForcedRootBlock,
    getForcedRootBlockAttrs: getForcedRootBlockAttrs,
    getBrNewLineSelector: getBrNewLineSelector,
    getNoNewLineSelector: getNoNewLineSelector,
    shouldKeepStyles: shouldKeepStyles,
    shouldEndContainerOnEmtpyBlock: shouldEndContainerOnEmtpyBlock
  };

  var firstNonWhiteSpaceNodeSibling = function (node) {
    while (node) {
      if (node.nodeType === 1 || node.nodeType === 3 && node.data && /[\r\n\s]/.test(node.data)) {
        return node;
      }
      node = node.nextSibling;
    }
  };
  var moveToCaretPosition = function (editor, root) {
    var walker, node, rng, lastNode = root, tempElm;
    var dom = editor.dom;
    var moveCaretBeforeOnEnterElementsMap = editor.schema.getMoveCaretBeforeOnEnterElements();
    if (!root) {
      return;
    }
    if (/^(LI|DT|DD)$/.test(root.nodeName)) {
      var firstChild = firstNonWhiteSpaceNodeSibling(root.firstChild);
      if (firstChild && /^(UL|OL|DL)$/.test(firstChild.nodeName)) {
        root.insertBefore(dom.doc.createTextNode('\xA0'), root.firstChild);
      }
    }
    rng = dom.createRng();
    root.normalize();
    if (root.hasChildNodes()) {
      walker = new TreeWalker(root, root);
      while (node = walker.current()) {
        if ($_9owqcy1qje5o2q0e.isText(node)) {
          rng.setStart(node, 0);
          rng.setEnd(node, 0);
          break;
        }
        if (moveCaretBeforeOnEnterElementsMap[node.nodeName.toLowerCase()]) {
          rng.setStartBefore(node);
          rng.setEndBefore(node);
          break;
        }
        lastNode = node;
        node = walker.next();
      }
      if (!node) {
        rng.setStart(lastNode, 0);
        rng.setEnd(lastNode, 0);
      }
    } else {
      if ($_9owqcy1qje5o2q0e.isBr(root)) {
        if (root.nextSibling && dom.isBlock(root.nextSibling)) {
          rng.setStartBefore(root);
          rng.setEndBefore(root);
        } else {
          rng.setStartAfter(root);
          rng.setEndAfter(root);
        }
      } else {
        rng.setStart(root, 0);
        rng.setEnd(root, 0);
      }
    }
    editor.selection.setRng(rng);
    dom.remove(tempElm);
    editor.selection.scrollIntoView(root);
  };
  var getEditableRoot = function (dom, node) {
    var root = dom.getRoot();
    var parent, editableRoot;
    parent = node;
    while (parent !== root && dom.getContentEditable(parent) !== 'false') {
      if (dom.getContentEditable(parent) === 'true') {
        editableRoot = parent;
      }
      parent = parent.parentNode;
    }
    return parent !== root ? editableRoot : root;
  };
  var getParentBlock$2 = function (editor) {
    return Option.from(editor.dom.getParent(editor.selection.getStart(true), editor.dom.isBlock));
  };
  var getParentBlockName = function (editor) {
    return getParentBlock$2(editor).fold($_enfhkv6je5o2ply.constant(''), function (parentBlock) {
      return parentBlock.nodeName.toUpperCase();
    });
  };
  var isListItemParentBlock = function (editor) {
    return getParentBlock$2(editor).filter(function (elm) {
      return isListItem($_5tcb2dyje5o2pwu.fromDom(elm));
    }).isSome();
  };
  var $_f0yz616tje5o2r2l = {
    moveToCaretPosition: moveToCaretPosition,
    getEditableRoot: getEditableRoot,
    getParentBlock: getParentBlock$2,
    getParentBlockName: getParentBlockName,
    isListItemParentBlock: isListItemParentBlock
  };

  var hasFirstChild = function (elm, name) {
    return elm.firstChild && elm.firstChild.nodeName === name;
  };
  var hasParent$1 = function (elm, parentName) {
    return elm && elm.parentNode && elm.parentNode.nodeName === parentName;
  };
  var isListBlock = function (elm) {
    return elm && /^(OL|UL|LI)$/.test(elm.nodeName);
  };
  var isNestedList = function (elm) {
    return isListBlock(elm) && isListBlock(elm.parentNode);
  };
  var getContainerBlock = function (containerBlock) {
    var containerBlockParent = containerBlock.parentNode;
    if (/^(LI|DT|DD)$/.test(containerBlockParent.nodeName)) {
      return containerBlockParent;
    }
    return containerBlock;
  };
  var isFirstOrLastLi = function (containerBlock, parentBlock, first) {
    var node = containerBlock[first ? 'firstChild' : 'lastChild'];
    while (node) {
      if ($_9owqcy1qje5o2q0e.isElement(node)) {
        break;
      }
      node = node[first ? 'nextSibling' : 'previousSibling'];
    }
    return node === parentBlock;
  };
  var insert$1 = function (editor, createNewBlock, containerBlock, parentBlock, newBlockName) {
    var dom = editor.dom;
    var rng = editor.selection.getRng();
    if (containerBlock === editor.getBody()) {
      return;
    }
    if (isNestedList(containerBlock)) {
      newBlockName = 'LI';
    }
    var newBlock = newBlockName ? createNewBlock(newBlockName) : dom.create('BR');
    if (isFirstOrLastLi(containerBlock, parentBlock, true) && isFirstOrLastLi(containerBlock, parentBlock, false)) {
      if (hasParent$1(containerBlock, 'LI')) {
        dom.insertAfter(newBlock, getContainerBlock(containerBlock));
      } else {
        dom.replace(newBlock, containerBlock);
      }
    } else if (isFirstOrLastLi(containerBlock, parentBlock, true)) {
      if (hasParent$1(containerBlock, 'LI')) {
        dom.insertAfter(newBlock, getContainerBlock(containerBlock));
        newBlock.appendChild(dom.doc.createTextNode(' '));
        newBlock.appendChild(containerBlock);
      } else {
        containerBlock.parentNode.insertBefore(newBlock, containerBlock);
      }
    } else if (isFirstOrLastLi(containerBlock, parentBlock, false)) {
      dom.insertAfter(newBlock, getContainerBlock(containerBlock));
    } else {
      containerBlock = getContainerBlock(containerBlock);
      var tmpRng = rng.cloneRange();
      tmpRng.setStartAfter(parentBlock);
      tmpRng.setEndAfter(containerBlock);
      var fragment = tmpRng.extractContents();
      if (newBlockName === 'LI' && hasFirstChild(fragment, 'LI')) {
        newBlock = fragment.firstChild;
        dom.insertAfter(fragment, containerBlock);
      } else {
        dom.insertAfter(fragment, containerBlock);
        dom.insertAfter(newBlock, containerBlock);
      }
    }
    dom.remove(parentBlock);
    $_f0yz616tje5o2r2l.moveToCaretPosition(editor, newBlock);
  };
  var $_52slpd6sje5o2r2i = { insert: insert$1 };

  var isEmptyAnchor = function (elm) {
    return elm && elm.nodeName === 'A' && $_a5gegtjje5o2pum.trim($_4md31c21je5o2q5f.trim(elm.innerText || elm.textContent)).length === 0;
  };
  var isTableCell$5 = function (node) {
    return node && /^(TD|TH|CAPTION)$/.test(node.nodeName);
  };
  var emptyBlock = function (elm) {
    elm.innerHTML = '<br data-mce-bogus="1">';
  };
  var containerAndSiblingName = function (container, nodeName) {
    return container.nodeName === nodeName || container.previousSibling && container.previousSibling.nodeName === nodeName;
  };
  var canSplitBlock = function (dom, node) {
    return node && dom.isBlock(node) && !/^(TD|TH|CAPTION|FORM)$/.test(node.nodeName) && !/^(fixed|absolute)/i.test(node.style.position) && dom.getContentEditable(node) !== 'true';
  };
  var trimInlineElementsOnLeftSideOfBlock = function (dom, nonEmptyElementsMap, block) {
    var node = block;
    var firstChilds = [];
    var i;
    if (!node) {
      return;
    }
    while (node = node.firstChild) {
      if (dom.isBlock(node)) {
        return;
      }
      if ($_9owqcy1qje5o2q0e.isElement(node) && !nonEmptyElementsMap[node.nodeName.toLowerCase()]) {
        firstChilds.push(node);
      }
    }
    i = firstChilds.length;
    while (i--) {
      node = firstChilds[i];
      if (!node.hasChildNodes() || node.firstChild === node.lastChild && node.firstChild.nodeValue === '') {
        dom.remove(node);
      } else {
        if (isEmptyAnchor(node)) {
          dom.remove(node);
        }
      }
    }
  };
  var normalizeZwspOffset = function (start, container, offset) {
    if ($_9owqcy1qje5o2q0e.isText(container) === false) {
      return offset;
    } else if (start) {
      return offset === 1 && container.data.charAt(offset - 1) === $_4md31c21je5o2q5f.ZWSP ? 0 : offset;
    } else {
      return offset === container.data.length - 1 && container.data.charAt(offset) === $_4md31c21je5o2q5f.ZWSP ? container.data.length : offset;
    }
  };
  var includeZwspInRange = function (rng) {
    var newRng = rng.cloneRange();
    newRng.setStart(rng.startContainer, normalizeZwspOffset(true, rng.startContainer, rng.startOffset));
    newRng.setEnd(rng.endContainer, normalizeZwspOffset(false, rng.endContainer, rng.endOffset));
    return newRng;
  };
  var trimLeadingLineBreaks = function (node) {
    do {
      if ($_9owqcy1qje5o2q0e.isText(node)) {
        node.nodeValue = node.nodeValue.replace(/^[\r\n]+/, '');
      }
      node = node.firstChild;
    } while (node);
  };
  var getEditableRoot$1 = function (dom, node) {
    var root = dom.getRoot();
    var parent, editableRoot;
    parent = node;
    while (parent !== root && dom.getContentEditable(parent) !== 'false') {
      if (dom.getContentEditable(parent) === 'true') {
        editableRoot = parent;
      }
      parent = parent.parentNode;
    }
    return parent !== root ? editableRoot : root;
  };
  var setForcedBlockAttrs = function (editor, node) {
    var forcedRootBlockName = $_ahj8826rje5o2r2f.getForcedRootBlock(editor);
    if (forcedRootBlockName && forcedRootBlockName.toLowerCase() === node.tagName.toLowerCase()) {
      editor.dom.setAttribs(node, $_ahj8826rje5o2r2f.getForcedRootBlockAttrs(editor));
    }
  };
  var wrapSelfAndSiblingsInDefaultBlock = function (editor, newBlockName, rng, container, offset) {
    var newBlock, parentBlock, startNode, node, next, rootBlockName;
    var blockName = newBlockName || 'P';
    var dom = editor.dom, editableRoot = getEditableRoot$1(dom, container);
    parentBlock = dom.getParent(container, dom.isBlock);
    if (!parentBlock || !canSplitBlock(dom, parentBlock)) {
      parentBlock = parentBlock || editableRoot;
      if (parentBlock === editor.getBody() || isTableCell$5(parentBlock)) {
        rootBlockName = parentBlock.nodeName.toLowerCase();
      } else {
        rootBlockName = parentBlock.parentNode.nodeName.toLowerCase();
      }
      if (!parentBlock.hasChildNodes()) {
        newBlock = dom.create(blockName);
        setForcedBlockAttrs(editor, newBlock);
        parentBlock.appendChild(newBlock);
        rng.setStart(newBlock, 0);
        rng.setEnd(newBlock, 0);
        return newBlock;
      }
      node = container;
      while (node.parentNode !== parentBlock) {
        node = node.parentNode;
      }
      while (node && !dom.isBlock(node)) {
        startNode = node;
        node = node.previousSibling;
      }
      if (startNode && editor.schema.isValidChild(rootBlockName, blockName.toLowerCase())) {
        newBlock = dom.create(blockName);
        setForcedBlockAttrs(editor, newBlock);
        startNode.parentNode.insertBefore(newBlock, startNode);
        node = startNode;
        while (node && !dom.isBlock(node)) {
          next = node.nextSibling;
          newBlock.appendChild(node);
          node = next;
        }
        rng.setStart(container, offset);
        rng.setEnd(container, offset);
      }
    }
    return container;
  };
  var addBrToBlockIfNeeded = function (dom, block) {
    var lastChild;
    block.normalize();
    lastChild = block.lastChild;
    if (!lastChild || /^(left|right)$/gi.test(dom.getStyle(lastChild, 'float', true))) {
      dom.add(block, 'br');
    }
  };
  var insert$2 = function (editor, evt) {
    var tmpRng, editableRoot, container, offset, parentBlock, shiftKey;
    var newBlock, fragment, containerBlock, parentBlockName, containerBlockName, newBlockName, isAfterLastNodeInContainer;
    var dom = editor.dom;
    var schema = editor.schema, nonEmptyElementsMap = schema.getNonEmptyElements();
    var rng = editor.selection.getRng();
    var createNewBlock = function (name) {
      var node = container, block, clonedNode, caretNode;
      var textInlineElements = schema.getTextInlineElements();
      if (name || parentBlockName === 'TABLE' || parentBlockName === 'HR') {
        block = dom.create(name || newBlockName);
        setForcedBlockAttrs(editor, block);
      } else {
        block = parentBlock.cloneNode(false);
      }
      caretNode = block;
      if ($_ahj8826rje5o2r2f.shouldKeepStyles(editor) === false) {
        dom.setAttrib(block, 'style', null);
        dom.setAttrib(block, 'class', null);
      } else {
        do {
          if (textInlineElements[node.nodeName]) {
            if ($_4066yu3jje5o2qev.isCaretNode(node)) {
              continue;
            }
            clonedNode = node.cloneNode(false);
            dom.setAttrib(clonedNode, 'id', '');
            if (block.hasChildNodes()) {
              clonedNode.appendChild(block.firstChild);
              block.appendChild(clonedNode);
            } else {
              caretNode = clonedNode;
              block.appendChild(clonedNode);
            }
          }
        } while ((node = node.parentNode) && node !== editableRoot);
      }
      emptyBlock(caretNode);
      return block;
    };
    var isCaretAtStartOrEndOfBlock = function (start) {
      var walker, node, name, normalizedOffset;
      normalizedOffset = normalizeZwspOffset(start, container, offset);
      if ($_9owqcy1qje5o2q0e.isText(container) && (start ? normalizedOffset > 0 : normalizedOffset < container.nodeValue.length)) {
        return false;
      }
      if (container.parentNode === parentBlock && isAfterLastNodeInContainer && !start) {
        return true;
      }
      if (start && $_9owqcy1qje5o2q0e.isElement(container) && container === parentBlock.firstChild) {
        return true;
      }
      if (containerAndSiblingName(container, 'TABLE') || containerAndSiblingName(container, 'HR')) {
        return isAfterLastNodeInContainer && !start || !isAfterLastNodeInContainer && start;
      }
      walker = new TreeWalker(container, parentBlock);
      if ($_9owqcy1qje5o2q0e.isText(container)) {
        if (start && normalizedOffset === 0) {
          walker.prev();
        } else if (!start && normalizedOffset === container.nodeValue.length) {
          walker.next();
        }
      }
      while (node = walker.current()) {
        if ($_9owqcy1qje5o2q0e.isElement(node)) {
          if (!node.getAttribute('data-mce-bogus')) {
            name = node.nodeName.toLowerCase();
            if (nonEmptyElementsMap[name] && name !== 'br') {
              return false;
            }
          }
        } else if ($_9owqcy1qje5o2q0e.isText(node) && !/^[ \t\r\n]*$/.test(node.nodeValue)) {
          return false;
        }
        if (start) {
          walker.prev();
        } else {
          walker.next();
        }
      }
      return true;
    };
    var insertNewBlockAfter = function () {
      if (/^(H[1-6]|PRE|FIGURE)$/.test(parentBlockName) && containerBlockName !== 'HGROUP') {
        newBlock = createNewBlock(newBlockName);
      } else {
        newBlock = createNewBlock();
      }
      if ($_ahj8826rje5o2r2f.shouldEndContainerOnEmtpyBlock(editor) && canSplitBlock(dom, containerBlock) && dom.isEmpty(parentBlock)) {
        newBlock = dom.split(containerBlock, parentBlock);
      } else {
        dom.insertAfter(newBlock, parentBlock);
      }
      $_f0yz616tje5o2r2l.moveToCaretPosition(editor, newBlock);
    };
    $_1t8l6x3xje5o2qlk.normalize(dom, rng).each(function (normRng) {
      rng.setStart(normRng.startContainer, normRng.startOffset);
      rng.setEnd(normRng.endContainer, normRng.endOffset);
    });
    container = rng.startContainer;
    offset = rng.startOffset;
    newBlockName = $_ahj8826rje5o2r2f.getForcedRootBlock(editor);
    shiftKey = evt.shiftKey;
    if ($_9owqcy1qje5o2q0e.isElement(container) && container.hasChildNodes()) {
      isAfterLastNodeInContainer = offset > container.childNodes.length - 1;
      container = container.childNodes[Math.min(offset, container.childNodes.length - 1)] || container;
      if (isAfterLastNodeInContainer && $_9owqcy1qje5o2q0e.isText(container)) {
        offset = container.nodeValue.length;
      } else {
        offset = 0;
      }
    }
    editableRoot = getEditableRoot$1(dom, container);
    if (!editableRoot) {
      return;
    }
    if (newBlockName && !shiftKey || !newBlockName && shiftKey) {
      container = wrapSelfAndSiblingsInDefaultBlock(editor, newBlockName, rng, container, offset);
    }
    parentBlock = dom.getParent(container, dom.isBlock);
    containerBlock = parentBlock ? dom.getParent(parentBlock.parentNode, dom.isBlock) : null;
    parentBlockName = parentBlock ? parentBlock.nodeName.toUpperCase() : '';
    containerBlockName = containerBlock ? containerBlock.nodeName.toUpperCase() : '';
    if (containerBlockName === 'LI' && !evt.ctrlKey) {
      parentBlock = containerBlock;
      containerBlock = containerBlock.parentNode;
      parentBlockName = containerBlockName;
    }
    if (/^(LI|DT|DD)$/.test(parentBlockName)) {
      if (dom.isEmpty(parentBlock)) {
        $_52slpd6sje5o2r2i.insert(editor, createNewBlock, containerBlock, parentBlock, newBlockName);
        return;
      }
    }
    if (newBlockName && parentBlock === editor.getBody()) {
      return;
    }
    newBlockName = newBlockName || 'P';
    if (isCaretContainerBlock(parentBlock)) {
      newBlock = showCaretContainerBlock(parentBlock);
      if (dom.isEmpty(parentBlock)) {
        emptyBlock(parentBlock);
      }
      $_f0yz616tje5o2r2l.moveToCaretPosition(editor, newBlock);
    } else if (isCaretAtStartOrEndOfBlock()) {
      insertNewBlockAfter();
    } else if (isCaretAtStartOrEndOfBlock(true)) {
      newBlock = parentBlock.parentNode.insertBefore(createNewBlock(), parentBlock);
      $_f0yz616tje5o2r2l.moveToCaretPosition(editor, containerAndSiblingName(parentBlock, 'HR') ? newBlock : parentBlock);
    } else {
      tmpRng = includeZwspInRange(rng).cloneRange();
      tmpRng.setEndAfter(parentBlock);
      fragment = tmpRng.extractContents();
      trimLeadingLineBreaks(fragment);
      newBlock = fragment.firstChild;
      dom.insertAfter(fragment, parentBlock);
      trimInlineElementsOnLeftSideOfBlock(dom, nonEmptyElementsMap, newBlock);
      addBrToBlockIfNeeded(dom, parentBlock);
      if (dom.isEmpty(parentBlock)) {
        emptyBlock(parentBlock);
      }
      newBlock.normalize();
      if (dom.isEmpty(newBlock)) {
        dom.remove(newBlock);
        insertNewBlockAfter();
      } else {
        $_f0yz616tje5o2r2l.moveToCaretPosition(editor, newBlock);
      }
    }
    dom.setAttrib(newBlock, 'id', '');
    editor.fire('NewBlock', { newBlock: newBlock });
  };
  var $_54gcs16qje5o2r1x = { insert: insert$2 };

  var matchesSelector = function (editor, selector) {
    return $_f0yz616tje5o2r2l.getParentBlock(editor).filter(function (parentBlock) {
      return selector.length > 0 && $_87zu5k1fje5o2pz9.is($_5tcb2dyje5o2pwu.fromDom(parentBlock), selector);
    }).isSome();
  };
  var shouldInsertBr = function (editor) {
    return matchesSelector(editor, $_ahj8826rje5o2r2f.getBrNewLineSelector(editor));
  };
  var shouldBlockNewLine = function (editor) {
    return matchesSelector(editor, $_ahj8826rje5o2r2f.getNoNewLineSelector(editor));
  };
  var $_23pm1k6vje5o2r2w = {
    shouldInsertBr: shouldInsertBr,
    shouldBlockNewLine: shouldBlockNewLine
  };

  var newLineAction = $_e73lvt3dje5o2qdq.generate([
    { br: [] },
    { block: [] },
    { none: [] }
  ]);
  var shouldBlockNewLine$1 = function (editor, shiftKey) {
    return $_23pm1k6vje5o2r2w.shouldBlockNewLine(editor);
  };
  var isBrMode = function (requiredState) {
    return function (editor, shiftKey) {
      var brMode = $_ahj8826rje5o2r2f.getForcedRootBlock(editor) === '';
      return brMode === requiredState;
    };
  };
  var inListBlock = function (requiredState) {
    return function (editor, shiftKey) {
      return $_f0yz616tje5o2r2l.isListItemParentBlock(editor) === requiredState;
    };
  };
  var inPreBlock = function (requiredState) {
    return function (editor, shiftKey) {
      var inPre = $_f0yz616tje5o2r2l.getParentBlockName(editor) === 'PRE';
      return inPre === requiredState;
    };
  };
  var shouldPutBrInPre$1 = function (requiredState) {
    return function (editor, shiftKey) {
      return $_ahj8826rje5o2r2f.shouldPutBrInPre(editor) === requiredState;
    };
  };
  var inBrContext = function (editor, shiftKey) {
    return $_23pm1k6vje5o2r2w.shouldInsertBr(editor);
  };
  var hasShiftKey = function (editor, shiftKey) {
    return shiftKey;
  };
  var canInsertIntoEditableRoot = function (editor) {
    var forcedRootBlock = $_ahj8826rje5o2r2f.getForcedRootBlock(editor);
    var rootEditable = $_f0yz616tje5o2r2l.getEditableRoot(editor.dom, editor.selection.getStart());
    return rootEditable && editor.schema.isValidChild(rootEditable.nodeName, forcedRootBlock ? forcedRootBlock : 'P');
  };
  var match$2 = function (predicates, action) {
    return function (editor, shiftKey) {
      var isMatch = $_bwcteb4je5o2pl4.foldl(predicates, function (res, p) {
        return res && p(editor, shiftKey);
      }, true);
      return isMatch ? Option.some(action) : Option.none();
    };
  };
  var getAction$1 = function (editor, evt) {
    return $_cq1fkk3oje5o2qg9.evaluateUntil([
      match$2([shouldBlockNewLine$1], newLineAction.none()),
      match$2([
        inPreBlock(true),
        shouldPutBrInPre$1(false),
        hasShiftKey
      ], newLineAction.br()),
      match$2([
        inPreBlock(true),
        shouldPutBrInPre$1(false)
      ], newLineAction.block()),
      match$2([
        inPreBlock(true),
        shouldPutBrInPre$1(true),
        hasShiftKey
      ], newLineAction.block()),
      match$2([
        inPreBlock(true),
        shouldPutBrInPre$1(true)
      ], newLineAction.br()),
      match$2([
        inListBlock(true),
        hasShiftKey
      ], newLineAction.br()),
      match$2([inListBlock(true)], newLineAction.block()),
      match$2([
        isBrMode(true),
        hasShiftKey,
        canInsertIntoEditableRoot
      ], newLineAction.block()),
      match$2([isBrMode(true)], newLineAction.br()),
      match$2([inBrContext], newLineAction.br()),
      match$2([
        isBrMode(false),
        hasShiftKey
      ], newLineAction.br()),
      match$2([canInsertIntoEditableRoot], newLineAction.block())
    ], [
      editor,
      evt.shiftKey
    ]).getOr(newLineAction.none());
  };
  var $_3fpic86uje5o2r2r = { getAction: getAction$1 };

  var insert$3 = function (editor, evt) {
    $_3fpic86uje5o2r2r.getAction(editor, evt).fold(function () {
      $_1esduy3wje5o2qhl.insert(editor, evt);
    }, function () {
      $_54gcs16qje5o2r1x.insert(editor, evt);
    }, $_enfhkv6je5o2ply.noop);
  };
  var $_ccur376pje5o2r1r = { insert: insert$3 };

  var endTypingLevel = function (undoManager) {
    if (undoManager.typing) {
      undoManager.typing = false;
      undoManager.add();
    }
  };
  var handleEnterKeyEvent = function (editor, event) {
    if (event.isDefaultPrevented()) {
      return;
    }
    event.preventDefault();
    endTypingLevel(editor.undoManager);
    editor.undoManager.transact(function () {
      if (editor.selection.isCollapsed() === false) {
        editor.execCommand('Delete');
      }
      $_ccur376pje5o2r1r.insert(editor, event);
    });
  };
  var setup$8 = function (editor) {
    editor.on('keydown', function (event) {
      if (event.keyCode === $_99mhlk59je5o2qss.ENTER) {
        handleEnterKeyEvent(editor, event);
      }
    });
  };
  var $_8j6q9k6oje5o2r1q = { setup: setup$8 };

  var isValidInsertPoint = function (location, caretPosition) {
    return isAtStartOrEnd(location) && $_9owqcy1qje5o2q0e.isText(caretPosition.container());
  };
  var insertNbspAtPosition = function (editor, caretPosition) {
    var container = caretPosition.container();
    var offset = caretPosition.offset();
    container.insertData(offset, '\xA0');
    editor.selection.setCursorLocation(container, offset + 1);
  };
  var insertAtLocation = function (editor, caretPosition, location) {
    if (isValidInsertPoint(location, caretPosition)) {
      insertNbspAtPosition(editor, caretPosition);
      return true;
    } else {
      return false;
    }
  };
  var insertAtCaret$2 = function (editor) {
    var isInlineTarget = $_enfhkv6je5o2ply.curry($_6skw9732je5o2qbp.isInlineTarget, editor);
    var caretPosition = CaretPosition$1.fromRangeStart(editor.selection.getRng());
    var boundaryLocation = $_62vpc23ije5o2qem.readLocation(isInlineTarget, editor.getBody(), caretPosition);
    return boundaryLocation.map($_enfhkv6je5o2ply.curry(insertAtLocation, editor, caretPosition)).getOr(false);
  };
  var isAtStartOrEnd = function (location) {
    return location.fold($_enfhkv6je5o2ply.constant(false), $_enfhkv6je5o2ply.constant(true), $_enfhkv6je5o2ply.constant(true), $_enfhkv6je5o2ply.constant(false));
  };
  var insertAtSelection = function (editor) {
    return editor.selection.isCollapsed() ? insertAtCaret$2(editor) : false;
  };
  var $_aorlpm6xje5o2r30 = { insertAtSelection: insertAtSelection };

  var executeKeydownOverride$2 = function (editor, evt) {
    $_fc81iw6lje5o2r1d.execute([{
        keyCode: $_99mhlk59je5o2qss.SPACEBAR,
        action: $_fc81iw6lje5o2r1d.action($_aorlpm6xje5o2r30.insertAtSelection, editor)
      }], evt).each(function (_) {
      evt.preventDefault();
    });
  };
  var setup$9 = function (editor) {
    editor.on('keydown', function (evt) {
      if (evt.isDefaultPrevented() === false) {
        executeKeydownOverride$2(editor, evt);
      }
    });
  };
  var $_6pyahj6wje5o2r2z = { setup: setup$9 };

  var setup$10 = function (editor) {
    var caret = $_2dnted3pje5o2qgc.setupSelectedState(editor);
    $_a5oax76jje5o2r0t.setup(editor, caret);
    $_6wiawx6mje5o2r1h.setup(editor, caret);
    $_8j6q9k6oje5o2r1q.setup(editor);
    $_6pyahj6wje5o2r2z.setup(editor);
  };
  var $_b7lkfw6ije5o2r0s = { setup: setup$10 };

  function Quirks (editor) {
    var each = $_a5gegtjje5o2pum.each;
    var BACKSPACE = $_99mhlk59je5o2qss.BACKSPACE, DELETE = $_99mhlk59je5o2qss.DELETE, dom = editor.dom, selection = editor.selection, settings = editor.settings, parser = editor.parser;
    var isGecko = $_9bd8pn9je5o2pp2.gecko, isIE = $_9bd8pn9je5o2pp2.ie, isWebKit = $_9bd8pn9je5o2pp2.webkit;
    var mceInternalUrlPrefix = 'data:text/mce-internal,';
    var mceInternalDataType = isIE ? 'Text' : 'URL';
    var setEditorCommandState = function (cmd, state) {
      try {
        editor.getDoc().execCommand(cmd, false, state);
      } catch (ex) {
      }
    };
    var isDefaultPrevented = function (e) {
      return e.isDefaultPrevented();
    };
    var setMceInternalContent = function (e) {
      var selectionHtml, internalContent;
      if (e.dataTransfer) {
        if (editor.selection.isCollapsed() && e.target.tagName === 'IMG') {
          selection.select(e.target);
        }
        selectionHtml = editor.selection.getContent();
        if (selectionHtml.length > 0) {
          internalContent = mceInternalUrlPrefix + escape(editor.id) + ',' + escape(selectionHtml);
          e.dataTransfer.setData(mceInternalDataType, internalContent);
        }
      }
    };
    var getMceInternalContent = function (e) {
      var internalContent;
      if (e.dataTransfer) {
        internalContent = e.dataTransfer.getData(mceInternalDataType);
        if (internalContent && internalContent.indexOf(mceInternalUrlPrefix) >= 0) {
          internalContent = internalContent.substr(mceInternalUrlPrefix.length).split(',');
          return {
            id: unescape(internalContent[0]),
            html: unescape(internalContent[1])
          };
        }
      }
      return null;
    };
    var insertClipboardContents = function (content, internal) {
      if (editor.queryCommandSupported('mceInsertClipboardContent')) {
        editor.execCommand('mceInsertClipboardContent', false, {
          content: content,
          internal: internal
        });
      } else {
        editor.execCommand('mceInsertContent', false, content);
      }
    };
    var emptyEditorWhenDeleting = function () {
      var serializeRng = function (rng) {
        var body = dom.create('body');
        var contents = rng.cloneContents();
        body.appendChild(contents);
        return selection.serializer.serialize(body, { format: 'html' });
      };
      var allContentsSelected = function (rng) {
        var selection = serializeRng(rng);
        var allRng = dom.createRng();
        allRng.selectNode(editor.getBody());
        var allSelection = serializeRng(allRng);
        return selection === allSelection;
      };
      editor.on('keydown', function (e) {
        var keyCode = e.keyCode;
        var isCollapsed, body;
        if (!isDefaultPrevented(e) && (keyCode === DELETE || keyCode === BACKSPACE)) {
          isCollapsed = editor.selection.isCollapsed();
          body = editor.getBody();
          if (isCollapsed && !dom.isEmpty(body)) {
            return;
          }
          if (!isCollapsed && !allContentsSelected(editor.selection.getRng())) {
            return;
          }
          e.preventDefault();
          editor.setContent('');
          if (body.firstChild && dom.isBlock(body.firstChild)) {
            editor.selection.setCursorLocation(body.firstChild, 0);
          } else {
            editor.selection.setCursorLocation(body, 0);
          }
          editor.nodeChanged();
        }
      });
    };
    var selectAll = function () {
      editor.shortcuts.add('meta+a', null, 'SelectAll');
    };
    var inputMethodFocus = function () {
      if (!editor.settings.content_editable) {
        dom.bind(editor.getDoc(), 'mousedown mouseup', function (e) {
          var rng;
          if (e.target === editor.getDoc().documentElement) {
            rng = selection.getRng();
            editor.getBody().focus();
            if (e.type === 'mousedown') {
              if (isCaretContainer(rng.startContainer)) {
                return;
              }
              selection.placeCaretAt(e.clientX, e.clientY);
            } else {
              selection.setRng(rng);
            }
          }
        });
      }
    };
    var removeHrOnBackspace = function () {
      editor.on('keydown', function (e) {
        if (!isDefaultPrevented(e) && e.keyCode === BACKSPACE) {
          if (!editor.getBody().getElementsByTagName('hr').length) {
            return;
          }
          if (selection.isCollapsed() && selection.getRng().startOffset === 0) {
            var node = selection.getNode();
            var previousSibling = node.previousSibling;
            if (node.nodeName === 'HR') {
              dom.remove(node);
              e.preventDefault();
              return;
            }
            if (previousSibling && previousSibling.nodeName && previousSibling.nodeName.toLowerCase() === 'hr') {
              dom.remove(previousSibling);
              e.preventDefault();
            }
          }
        }
      });
    };
    var focusBody = function () {
      if (!Range.prototype.getClientRects) {
        editor.on('mousedown', function (e) {
          if (!isDefaultPrevented(e) && e.target.nodeName === 'HTML') {
            var body_1 = editor.getBody();
            body_1.blur();
            $_db41wogje5o2ps3.setEditorTimeout(editor, function () {
              body_1.focus();
            });
          }
        });
      }
    };
    var selectControlElements = function () {
      editor.on('click', function (e) {
        var target = e.target;
        if (/^(IMG|HR)$/.test(target.nodeName) && dom.getContentEditableParent(target) !== 'false') {
          e.preventDefault();
          editor.selection.select(target);
          editor.nodeChanged();
        }
        if (target.nodeName === 'A' && dom.hasClass(target, 'mce-item-anchor')) {
          e.preventDefault();
          selection.select(target);
        }
      });
    };
    var removeStylesWhenDeletingAcrossBlockElements = function () {
      var getAttributeApplyFunction = function () {
        var template = dom.getAttribs(selection.getStart().cloneNode(false));
        return function () {
          var target = selection.getStart();
          if (target !== editor.getBody()) {
            dom.setAttrib(target, 'style', null);
            each(template, function (attr) {
              target.setAttributeNode(attr.cloneNode(true));
            });
          }
        };
      };
      var isSelectionAcrossElements = function () {
        return !selection.isCollapsed() && dom.getParent(selection.getStart(), dom.isBlock) !== dom.getParent(selection.getEnd(), dom.isBlock);
      };
      editor.on('keypress', function (e) {
        var applyAttributes;
        if (!isDefaultPrevented(e) && (e.keyCode === 8 || e.keyCode === 46) && isSelectionAcrossElements()) {
          applyAttributes = getAttributeApplyFunction();
          editor.getDoc().execCommand('delete', false, null);
          applyAttributes();
          e.preventDefault();
          return false;
        }
      });
      dom.bind(editor.getDoc(), 'cut', function (e) {
        var applyAttributes;
        if (!isDefaultPrevented(e) && isSelectionAcrossElements()) {
          applyAttributes = getAttributeApplyFunction();
          $_db41wogje5o2ps3.setEditorTimeout(editor, function () {
            applyAttributes();
          });
        }
      });
    };
    var disableBackspaceIntoATable = function () {
      editor.on('keydown', function (e) {
        if (!isDefaultPrevented(e) && e.keyCode === BACKSPACE) {
          if (selection.isCollapsed() && selection.getRng().startOffset === 0) {
            var previousSibling = selection.getNode().previousSibling;
            if (previousSibling && previousSibling.nodeName && previousSibling.nodeName.toLowerCase() === 'table') {
              e.preventDefault();
              return false;
            }
          }
        }
      });
    };
    var removeBlockQuoteOnBackSpace = function () {
      editor.on('keydown', function (e) {
        var rng, container, offset, root, parent;
        if (isDefaultPrevented(e) || e.keyCode !== $_99mhlk59je5o2qss.BACKSPACE) {
          return;
        }
        rng = selection.getRng();
        container = rng.startContainer;
        offset = rng.startOffset;
        root = dom.getRoot();
        parent = container;
        if (!rng.collapsed || offset !== 0) {
          return;
        }
        while (parent && parent.parentNode && parent.parentNode.firstChild === parent && parent.parentNode !== root) {
          parent = parent.parentNode;
        }
        if (parent.tagName === 'BLOCKQUOTE') {
          editor.formatter.toggle('blockquote', null, parent);
          rng = dom.createRng();
          rng.setStart(container, 0);
          rng.setEnd(container, 0);
          selection.setRng(rng);
        }
      });
    };
    var setGeckoEditingOptions = function () {
      var setOpts = function () {
        setEditorCommandState('StyleWithCSS', false);
        setEditorCommandState('enableInlineTableEditing', false);
        if (!settings.object_resizing) {
          setEditorCommandState('enableObjectResizing', false);
        }
      };
      if (!settings.readonly) {
        editor.on('BeforeExecCommand MouseDown', setOpts);
      }
    };
    var addBrAfterLastLinks = function () {
      var fixLinks = function () {
        each(dom.select('a'), function (node) {
          var parentNode = node.parentNode;
          var root = dom.getRoot();
          if (parentNode.lastChild === node) {
            while (parentNode && !dom.isBlock(parentNode)) {
              if (parentNode.parentNode.lastChild !== parentNode || parentNode === root) {
                return;
              }
              parentNode = parentNode.parentNode;
            }
            dom.add(parentNode, 'br', { 'data-mce-bogus': 1 });
          }
        });
      };
      editor.on('SetContent ExecCommand', function (e) {
        if (e.type === 'setcontent' || e.command === 'mceInsertLink') {
          fixLinks();
        }
      });
    };
    var setDefaultBlockType = function () {
      if (settings.forced_root_block) {
        editor.on('init', function () {
          setEditorCommandState('DefaultParagraphSeparator', settings.forced_root_block);
        });
      }
    };
    var normalizeSelection = function () {
      editor.on('keyup focusin mouseup', function (e) {
        if (!$_99mhlk59je5o2qss.modifierPressed(e)) {
          selection.normalize();
        }
      }, true);
    };
    var showBrokenImageIcon = function () {
      editor.contentStyles.push('img:-moz-broken {' + '-moz-force-broken-image-icon:1;' + 'min-width:24px;' + 'min-height:24px' + '}');
    };
    var restoreFocusOnKeyDown = function () {
      if (!editor.inline) {
        editor.on('keydown', function () {
          if (document.activeElement === document.body) {
            editor.getWin().focus();
          }
        });
      }
    };
    var bodyHeight = function () {
      if (!editor.inline) {
        editor.contentStyles.push('body {min-height: 150px}');
        editor.on('click', function (e) {
          var rng;
          if (e.target.nodeName === 'HTML') {
            if ($_9bd8pn9je5o2pp2.ie > 11) {
              editor.getBody().focus();
              return;
            }
            rng = editor.selection.getRng();
            editor.getBody().focus();
            editor.selection.setRng(rng);
            editor.selection.normalize();
            editor.nodeChanged();
          }
        });
      }
    };
    var blockCmdArrowNavigation = function () {
      if ($_9bd8pn9je5o2pp2.mac) {
        editor.on('keydown', function (e) {
          if ($_99mhlk59je5o2qss.metaKeyPressed(e) && !e.shiftKey && (e.keyCode === 37 || e.keyCode === 39)) {
            e.preventDefault();
            editor.selection.getSel().modify('move', e.keyCode === 37 ? 'backward' : 'forward', 'lineboundary');
          }
        });
      }
    };
    var disableAutoUrlDetect = function () {
      setEditorCommandState('AutoUrlDetect', false);
    };
    var tapLinksAndImages = function () {
      editor.on('click', function (e) {
        var elm = e.target;
        do {
          if (elm.tagName === 'A') {
            e.preventDefault();
            return;
          }
        } while (elm = elm.parentNode);
      });
      editor.contentStyles.push('.mce-content-body {-webkit-touch-callout: none}');
    };
    var blockFormSubmitInsideEditor = function () {
      editor.on('init', function () {
        editor.dom.bind(editor.getBody(), 'submit', function (e) {
          e.preventDefault();
        });
      });
    };
    var removeAppleInterchangeBrs = function () {
      parser.addNodeFilter('br', function (nodes) {
        var i = nodes.length;
        while (i--) {
          if (nodes[i].attr('class') === 'Apple-interchange-newline') {
            nodes[i].remove();
          }
        }
      });
    };
    var ieInternalDragAndDrop = function () {
      editor.on('dragstart', function (e) {
        setMceInternalContent(e);
      });
      editor.on('drop', function (e) {
        if (!isDefaultPrevented(e)) {
          var internalContent = getMceInternalContent(e);
          if (internalContent && internalContent.id !== editor.id) {
            e.preventDefault();
            var rng = $_drs2u868je5o2qyu.fromPoint(e.x, e.y, editor.getDoc());
            selection.setRng(rng);
            insertClipboardContents(internalContent.html, true);
          }
        }
      });
    };
    var refreshContentEditable = function () {
    };
    var isHidden = function () {
      var sel;
      if (!isGecko || editor.removed) {
        return 0;
      }
      sel = editor.selection.getSel();
      return !sel || !sel.rangeCount || sel.rangeCount === 0;
    };
    removeBlockQuoteOnBackSpace();
    emptyEditorWhenDeleting();
    if (!$_9bd8pn9je5o2pp2.windowsPhone) {
      normalizeSelection();
    }
    if (isWebKit) {
      inputMethodFocus();
      selectControlElements();
      setDefaultBlockType();
      blockFormSubmitInsideEditor();
      disableBackspaceIntoATable();
      removeAppleInterchangeBrs();
      if ($_9bd8pn9je5o2pp2.iOS) {
        restoreFocusOnKeyDown();
        bodyHeight();
        tapLinksAndImages();
      } else {
        selectAll();
      }
    }
    if ($_9bd8pn9je5o2pp2.ie >= 11) {
      bodyHeight();
      disableBackspaceIntoATable();
    }
    if ($_9bd8pn9je5o2pp2.ie) {
      selectAll();
      disableAutoUrlDetect();
      ieInternalDragAndDrop();
    }
    if (isGecko) {
      removeHrOnBackspace();
      focusBody();
      removeStylesWhenDeletingAcrossBlockElements();
      setGeckoEditingOptions();
      addBrAfterLastLinks();
      showBrokenImageIcon();
      blockCmdArrowNavigation();
      disableBackspaceIntoATable();
    }
    return {
      refreshContentEditable: refreshContentEditable,
      isHidden: isHidden
    };
  }

  var DOM$2 = DOMUtils.DOM;
  var appendStyle = function (editor, text) {
    var head = $_5tcb2dyje5o2pwu.fromDom(editor.getDoc().head);
    var tag = $_5tcb2dyje5o2pwu.fromTag('style');
    $_3uo9pl14je5o2pxq.set(tag, 'type', 'text/css');
    $_elbl2s2oje5o2q9z.append(tag, $_5tcb2dyje5o2pwu.fromText(text));
    $_elbl2s2oje5o2q9z.append(head, tag);
  };
  var createParser = function (editor) {
    var parser = DomParser(editor.settings, editor.schema);
    parser.addAttributeFilter('src,href,style,tabindex', function (nodes, name) {
      var i = nodes.length, node;
      var dom = editor.dom;
      var value, internalName;
      while (i--) {
        node = nodes[i];
        value = node.attr(name);
        internalName = 'data-mce-' + name;
        if (!node.attributes.map[internalName]) {
          if (value.indexOf('data:') === 0 || value.indexOf('blob:') === 0) {
            continue;
          }
          if (name === 'style') {
            value = dom.serializeStyle(dom.parseStyle(value), node.name);
            if (!value.length) {
              value = null;
            }
            node.attr(internalName, value);
            node.attr(name, value);
          } else if (name === 'tabindex') {
            node.attr(internalName, value);
            node.attr(name, null);
          } else {
            node.attr(internalName, editor.convertURL(value, name, node.name));
          }
        }
      }
    });
    parser.addNodeFilter('script', function (nodes) {
      var i = nodes.length, node, type;
      while (i--) {
        node = nodes[i];
        type = node.attr('type') || 'no/type';
        if (type.indexOf('mce-') !== 0) {
          node.attr('type', 'mce-' + type);
        }
      }
    });
    parser.addNodeFilter('#cdata', function (nodes) {
      var i = nodes.length, node;
      while (i--) {
        node = nodes[i];
        node.type = 8;
        node.name = '#comment';
        node.value = '[CDATA[' + node.value + ']]';
      }
    });
    parser.addNodeFilter('p,h1,h2,h3,h4,h5,h6,div', function (nodes) {
      var i = nodes.length, node;
      var nonEmptyElements = editor.schema.getNonEmptyElements();
      while (i--) {
        node = nodes[i];
        if (node.isEmpty(nonEmptyElements) && node.getAll('br').length === 0) {
          node.append(new Node$2('br', 1)).shortEnded = true;
        }
      }
    });
    return parser;
  };
  var autoFocus = function (editor) {
    if (editor.settings.auto_focus) {
      $_db41wogje5o2ps3.setEditorTimeout(editor, function () {
        var focusEditor;
        if (editor.settings.auto_focus === true) {
          focusEditor = editor;
        } else {
          focusEditor = editor.editorManager.get(editor.settings.auto_focus);
        }
        if (!focusEditor.destroyed) {
          focusEditor.focus();
        }
      }, 100);
    }
  };
  var initEditor = function (editor) {
    editor.bindPendingEventDelegates();
    editor.initialized = true;
    editor.fire('init');
    editor.focus(true);
    editor.nodeChanged({ initial: true });
    editor.execCallback('init_instance_callback', editor);
    autoFocus(editor);
  };
  var getStyleSheetLoader = function (editor) {
    return editor.inline ? DOM$2.styleSheetLoader : editor.dom.styleSheetLoader;
  };
  var initContentBody = function (editor, skipWrite) {
    var settings = editor.settings;
    var targetElm = editor.getElement();
    var doc = editor.getDoc(), body, contentCssText;
    if (!settings.inline) {
      editor.getElement().style.visibility = editor.orgVisibility;
    }
    if (!skipWrite && !settings.content_editable) {
      doc.open();
      doc.write(editor.iframeHTML);
      doc.close();
    }
    if (settings.content_editable) {
      editor.on('remove', function () {
        var bodyEl = this.getBody();
        DOM$2.removeClass(bodyEl, 'mce-content-body');
        DOM$2.removeClass(bodyEl, 'mce-edit-focus');
        DOM$2.setAttrib(bodyEl, 'contentEditable', null);
      });
      DOM$2.addClass(targetElm, 'mce-content-body');
      editor.contentDocument = doc = settings.content_document || document;
      editor.contentWindow = settings.content_window || window;
      editor.bodyElement = targetElm;
      settings.content_document = settings.content_window = null;
      settings.root_name = targetElm.nodeName.toLowerCase();
    }
    body = editor.getBody();
    body.disabled = true;
    editor.readonly = settings.readonly;
    if (!editor.readonly) {
      if (editor.inline && DOM$2.getStyle(body, 'position', true) === 'static') {
        body.style.position = 'relative';
      }
      body.contentEditable = editor.getParam('content_editable_state', true);
    }
    body.disabled = false;
    editor.editorUpload = EditorUpload(editor);
    editor.schema = Schema(settings);
    editor.dom = new DOMUtils(doc, {
      keep_values: true,
      url_converter: editor.convertURL,
      url_converter_scope: editor,
      hex_colors: settings.force_hex_style_colors,
      class_filter: settings.class_filter,
      update_styles: true,
      root_element: editor.inline ? editor.getBody() : null,
      collect: settings.content_editable,
      schema: editor.schema,
      onSetAttrib: function (e) {
        editor.fire('SetAttrib', e);
      }
    });
    editor.parser = createParser(editor);
    editor.serializer = DomSerializer$1(settings, editor);
    editor.selection = Selection$1(editor.dom, editor.getWin(), editor.serializer, editor);
    editor.formatter = Formatter(editor);
    editor.undoManager = UndoManager(editor);
    editor._nodeChangeDispatcher = new NodeChange(editor);
    editor._selectionOverrides = SelectionOverrides(editor);
    $_acfnaa63je5o2qxq.setup(editor);
    $_b7lkfw6ije5o2r0s.setup(editor);
    $_fekqo94yje5o2qqs.setup(editor);
    editor.fire('PreInit');
    if (!settings.browser_spellcheck && !settings.gecko_spellcheck) {
      doc.body.spellcheck = false;
      DOM$2.setAttrib(body, 'spellcheck', 'false');
    }
    editor.quirks = Quirks(editor);
    editor.fire('PostRender');
    if (settings.directionality) {
      body.dir = settings.directionality;
    }
    if (settings.nowrap) {
      body.style.whiteSpace = 'nowrap';
    }
    if (settings.protect) {
      editor.on('BeforeSetContent', function (e) {
        $_a5gegtjje5o2pum.each(settings.protect, function (pattern) {
          e.content = e.content.replace(pattern, function (str) {
            return '<!--mce:protected ' + escape(str) + '-->';
          });
        });
      });
    }
    editor.on('SetContent', function () {
      editor.addVisual(editor.getBody());
    });
    if (settings.padd_empty_editor) {
      editor.on('PostProcess', function (e) {
        e.content = e.content.replace(/^(<p[^>]*>(&nbsp;|&#160;|\s|\u00a0|<br \/>|)<\/p>[\r\n]*|<br \/>[\r\n]*)$/, '');
      });
    }
    editor.load({
      initial: true,
      format: 'html'
    });
    editor.startContent = editor.getContent({ format: 'raw' });
    editor.on('compositionstart compositionend', function (e) {
      editor.composing = e.type === 'compositionstart';
    });
    if (editor.contentStyles.length > 0) {
      contentCssText = '';
      $_a5gegtjje5o2pum.each(editor.contentStyles, function (style) {
        contentCssText += style + '\r\n';
      });
      editor.dom.addStyle(contentCssText);
    }
    getStyleSheetLoader(editor).loadAll(editor.contentCSS, function (_) {
      initEditor(editor);
    }, function (urls) {
      initEditor(editor);
    });
    if (settings.content_style) {
      appendStyle(editor, settings.content_style);
    }
  };
  var $_aixfus4lje5o2qp4 = { initContentBody: initContentBody };

  var DOM$3 = DOMUtils.DOM;
  var relaxDomain = function (editor, ifr) {
    if (document.domain !== window.location.hostname && $_9bd8pn9je5o2pp2.ie && $_9bd8pn9je5o2pp2.ie < 12) {
      var bodyUuid = $_2bgddd4wje5o2qqo.uuid('mce');
      editor[bodyUuid] = function () {
        $_aixfus4lje5o2qp4.initContentBody(editor);
      };
      var domainRelaxUrl = 'javascript:(function(){' + 'document.open();document.domain="' + document.domain + '";' + 'var ed = window.parent.tinymce.get("' + editor.id + '");document.write(ed.iframeHTML);' + 'document.close();ed.' + bodyUuid + '(true);})()';
      DOM$3.setAttrib(ifr, 'src', domainRelaxUrl);
      return true;
    }
    return false;
  };
  var normalizeHeight = function (height) {
    var normalizedHeight = typeof height === 'number' ? height + 'px' : height;
    return normalizedHeight ? normalizedHeight : '';
  };
  var createIframeElement = function (id, title, height, customAttrs) {
    var iframe = $_5tcb2dyje5o2pwu.fromTag('iframe');
    $_3uo9pl14je5o2pxq.setAll(iframe, customAttrs);
    $_3uo9pl14je5o2pxq.setAll(iframe, {
      id: id + '_ifr',
      frameBorder: '0',
      allowTransparency: 'true',
      title: title
    });
    $_1ioope11je5o2px4.setAll(iframe, {
      width: '100%',
      height: normalizeHeight(height),
      display: 'block'
    });
    return iframe;
  };
  var getIframeHtml = function (editor) {
    var bodyId, bodyClass, iframeHTML;
    iframeHTML = $_ahj8826rje5o2r2f.getDocType(editor) + '<html><head>';
    if ($_ahj8826rje5o2r2f.getDocumentBaseUrl(editor) !== editor.documentBaseUrl) {
      iframeHTML += '<base href="' + editor.documentBaseURI.getURI() + '" />';
    }
    iframeHTML += '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
    bodyId = $_ahj8826rje5o2r2f.getBodyId(editor);
    bodyClass = $_ahj8826rje5o2r2f.getBodyClass(editor);
    if ($_ahj8826rje5o2r2f.getContentSecurityPolicy(editor)) {
      iframeHTML += '<meta http-equiv="Content-Security-Policy" content="' + $_ahj8826rje5o2r2f.getContentSecurityPolicy(editor) + '" />';
    }
    iframeHTML += '</head><body id="' + bodyId + '" class="mce-content-body ' + bodyClass + '" data-id="' + editor.id + '"><br></body></html>';
    return iframeHTML;
  };
  var createIframe = function (editor, o) {
    var title = editor.editorManager.translate('Rich Text Area. Press ALT-F9 for menu. ' + 'Press ALT-F10 for toolbar. Press ALT-0 for help');
    var ifr = createIframeElement(editor.id, title, o.height, $_ahj8826rje5o2r2f.getIframeAttrs(editor)).dom();
    ifr.onload = function () {
      ifr.onload = null;
      editor.fire('load');
    };
    var isDomainRelaxed = relaxDomain(editor, ifr);
    editor.contentAreaContainer = o.iframeContainer;
    editor.iframeElement = ifr;
    editor.iframeHTML = getIframeHtml(editor);
    DOM$3.add(o.iframeContainer, ifr);
    return isDomainRelaxed;
  };
  var init$1 = function (editor, boxInfo) {
    var isDomainRelaxed = createIframe(editor, boxInfo);
    if (boxInfo.editorContainer) {
      DOM$3.get(boxInfo.editorContainer).style.display = editor.orgDisplay;
      editor.hidden = DOM$3.isHidden(boxInfo.editorContainer);
    }
    editor.getElement().style.display = 'none';
    DOM$3.setAttrib(editor.id, 'aria-hidden', true);
    if (!isDomainRelaxed) {
      $_aixfus4lje5o2qp4.initContentBody(editor);
    }
  };
  var $_3w1bbh6zje5o2r3o = { init: init$1 };

  var DOM$4 = DOMUtils.DOM;
  var initPlugin = function (editor, initializedPlugins, plugin) {
    var Plugin = PluginManager$1.get(plugin);
    var pluginUrl, pluginInstance;
    pluginUrl = PluginManager$1.urls[plugin] || editor.documentBaseUrl.replace(/\/$/, '');
    plugin = $_a5gegtjje5o2pum.trim(plugin);
    if (Plugin && $_a5gegtjje5o2pum.inArray(initializedPlugins, plugin) === -1) {
      $_a5gegtjje5o2pum.each(PluginManager$1.dependencies(plugin), function (dep) {
        initPlugin(editor, initializedPlugins, dep);
      });
      if (editor.plugins[plugin]) {
        return;
      }
      pluginInstance = new Plugin(editor, pluginUrl, editor.$);
      editor.plugins[plugin] = pluginInstance;
      if (pluginInstance.init) {
        pluginInstance.init(editor, pluginUrl);
        initializedPlugins.push(plugin);
      }
    }
  };
  var trimLegacyPrefix = function (name) {
    return name.replace(/^\-/, '');
  };
  var initPlugins = function (editor) {
    var initializedPlugins = [];
    $_a5gegtjje5o2pum.each(editor.settings.plugins.split(/[ ,]/), function (name) {
      initPlugin(editor, initializedPlugins, trimLegacyPrefix(name));
    });
  };
  var initTheme = function (editor) {
    var Theme;
    var theme = editor.settings.theme;
    if ($_be3q8w12je5o2pxj.isString(theme)) {
      editor.settings.theme = trimLegacyPrefix(theme);
      Theme = ThemeManager.get(theme);
      editor.theme = new Theme(editor, ThemeManager.urls[theme]);
      if (editor.theme.init) {
        editor.theme.init(editor, ThemeManager.urls[theme] || editor.documentBaseUrl.replace(/\/$/, ''), editor.$);
      }
    } else {
      editor.theme = {};
    }
  };
  var renderFromLoadedTheme = function (editor) {
    var w, h, minHeight, re, info;
    var settings = editor.settings;
    var elm = editor.getElement();
    w = settings.width || DOM$4.getStyle(elm, 'width') || '100%';
    h = settings.height || DOM$4.getStyle(elm, 'height') || elm.offsetHeight;
    minHeight = settings.min_height || 100;
    re = /^[0-9\.]+(|px)$/i;
    if (re.test('' + w)) {
      w = Math.max(parseInt(w, 10), 100);
    }
    if (re.test('' + h)) {
      h = Math.max(parseInt(h, 10), minHeight);
    }
    info = editor.theme.renderUI({
      targetNode: elm,
      width: w,
      height: h,
      deltaWidth: settings.delta_width,
      deltaHeight: settings.delta_height
    });
    if (!settings.content_editable) {
      h = (info.iframeHeight || h) + (typeof h === 'number' ? info.deltaHeight || 0 : '');
      if (h < minHeight) {
        h = minHeight;
      }
    }
    info.height = h;
    return info;
  };
  var renderFromThemeFunc = function (editor) {
    var info;
    var elm = editor.getElement();
    info = editor.settings.theme(editor, elm);
    if (info.editorContainer.nodeType) {
      info.editorContainer.id = info.editorContainer.id || editor.id + '_parent';
    }
    if (info.iframeContainer && info.iframeContainer.nodeType) {
      info.iframeContainer.id = info.iframeContainer.id || editor.id + '_iframecontainer';
    }
    info.height = info.iframeHeight ? info.iframeHeight : elm.offsetHeight;
    return info;
  };
  var createThemeFalseResult = function (element) {
    return {
      editorContainer: element,
      iframeContainer: element
    };
  };
  var renderThemeFalseIframe = function (targetElement) {
    var iframeContainer = DOM$4.create('div');
    DOM$4.insertAfter(iframeContainer, targetElement);
    return createThemeFalseResult(iframeContainer);
  };
  var renderThemeFalse = function (editor) {
    var targetElement = editor.getElement();
    return editor.inline ? createThemeFalseResult(null) : renderThemeFalseIframe(targetElement);
  };
  var renderThemeUi = function (editor) {
    var settings = editor.settings, elm = editor.getElement();
    editor.orgDisplay = elm.style.display;
    if ($_be3q8w12je5o2pxj.isString(settings.theme)) {
      return renderFromLoadedTheme(editor);
    } else if ($_be3q8w12je5o2pxj.isFunction(settings.theme)) {
      return renderFromThemeFunc(editor);
    } else {
      return renderThemeFalse(editor);
    }
  };
  var init$2 = function (editor) {
    var settings = editor.settings;
    var elm = editor.getElement();
    var boxInfo;
    editor.rtl = settings.rtl_ui || editor.editorManager.i18n.rtl;
    editor.editorManager.i18n.setCode(settings.language);
    settings.aria_label = settings.aria_label || DOM$4.getAttrib(elm, 'aria-label', editor.getLang('aria.rich_text_area'));
    editor.fire('ScriptsLoaded');
    initTheme(editor);
    initPlugins(editor);
    boxInfo = renderThemeUi(editor);
    editor.editorContainer = boxInfo.editorContainer ? boxInfo.editorContainer : null;
    if (settings.content_css) {
      $_a5gegtjje5o2pum.each($_a5gegtjje5o2pum.explode(settings.content_css), function (u) {
        editor.contentCSS.push(editor.documentBaseURI.toAbsolute(u));
      });
    }
    if (settings.content_editable) {
      return $_aixfus4lje5o2qp4.initContentBody(editor);
    } else {
      return $_3w1bbh6zje5o2r3o.init(editor, boxInfo);
    }
  };
  var $_v6l1f4ije5o2qou = { init: init$2 };

  var DOM$5 = DOMUtils.DOM;
  var hasSkipLoadPrefix = function (name) {
    return name.charAt(0) === '-';
  };
  var loadLanguage = function (scriptLoader, editor) {
    var settings = editor.settings;
    if (settings.language && settings.language !== 'en' && !settings.language_url) {
      settings.language_url = editor.editorManager.baseURL + '/langs/' + settings.language + '.js';
    }
    if (settings.language_url && !editor.editorManager.i18n.data[settings.language]) {
      scriptLoader.add(settings.language_url);
    }
  };
  var loadTheme = function (scriptLoader, editor, suffix, callback) {
    var settings = editor.settings, theme = settings.theme;
    if ($_be3q8w12je5o2pxj.isString(theme)) {
      if (!hasSkipLoadPrefix(theme) && !ThemeManager.urls.hasOwnProperty(theme)) {
        var themeUrl = settings.theme_url;
        if (themeUrl) {
          ThemeManager.load(theme, editor.documentBaseURI.toAbsolute(themeUrl));
        } else {
          ThemeManager.load(theme, 'themes/' + theme + '/theme' + suffix + '.js');
        }
      }
      scriptLoader.loadQueue(function () {
        ThemeManager.waitFor(theme, callback);
      });
    } else {
      callback();
    }
  };
  var loadPlugins = function (settings, suffix) {
    if ($_a5gegtjje5o2pum.isArray(settings.plugins)) {
      settings.plugins = settings.plugins.join(' ');
    }
    $_a5gegtjje5o2pum.each(settings.external_plugins, function (url, name) {
      PluginManager$1.load(name, url);
      settings.plugins += ' ' + name;
    });
    $_a5gegtjje5o2pum.each(settings.plugins.split(/[ ,]/), function (plugin) {
      plugin = $_a5gegtjje5o2pum.trim(plugin);
      if (plugin && !PluginManager$1.urls[plugin]) {
        if (hasSkipLoadPrefix(plugin)) {
          plugin = plugin.substr(1, plugin.length);
          var dependencies = PluginManager$1.dependencies(plugin);
          $_a5gegtjje5o2pum.each(dependencies, function (dep) {
            var defaultSettings = {
              prefix: 'plugins/',
              resource: dep,
              suffix: '/plugin' + suffix + '.js'
            };
            dep = PluginManager$1.createUrl(defaultSettings, dep);
            PluginManager$1.load(dep.resource, dep);
          });
        } else {
          PluginManager$1.load(plugin, {
            prefix: 'plugins/',
            resource: plugin,
            suffix: '/plugin' + suffix + '.js'
          });
        }
      }
    });
  };
  var loadScripts = function (editor, suffix) {
    var scriptLoader = ScriptLoader.ScriptLoader;
    loadTheme(scriptLoader, editor, suffix, function () {
      loadLanguage(scriptLoader, editor);
      loadPlugins(editor.settings, suffix);
      scriptLoader.loadQueue(function () {
        if (!editor.removed) {
          $_v6l1f4ije5o2qou.init(editor);
        }
      }, editor, function (urls) {
        $_axrd1j4hje5o2qor.pluginLoadError(editor, urls[0]);
        if (!editor.removed) {
          $_v6l1f4ije5o2qou.init(editor);
        }
      });
    });
  };
  var render = function (editor) {
    var settings = editor.settings, id = editor.id;
    var readyHandler = function () {
      DOM$5.unbind(window, 'ready', readyHandler);
      editor.render();
    };
    if (!EventUtils.Event.domLoaded) {
      DOM$5.bind(window, 'ready', readyHandler);
      return;
    }
    if (!editor.getElement()) {
      return;
    }
    if (!$_9bd8pn9je5o2pp2.contentEditable) {
      return;
    }
    if (!settings.inline) {
      editor.orgVisibility = editor.getElement().style.visibility;
      editor.getElement().style.visibility = 'hidden';
    } else {
      editor.inline = true;
    }
    var form = editor.getElement().form || DOM$5.getParent(id, 'form');
    if (form) {
      editor.formElement = form;
      if (settings.hidden_input && !/TEXTAREA|INPUT/i.test(editor.getElement().nodeName)) {
        DOM$5.insertAfter(DOM$5.create('input', {
          type: 'hidden',
          name: id
        }), id);
        editor.hasHiddenInput = true;
      }
      editor.formEventDelegate = function (e) {
        editor.fire(e.type, e);
      };
      DOM$5.bind(form, 'submit reset', editor.formEventDelegate);
      editor.on('reset', function () {
        editor.setContent(editor.startContent, { format: 'raw' });
      });
      if (settings.submit_patch && !form.submit.nodeType && !form.submit.length && !form._mceOldSubmit) {
        form._mceOldSubmit = form.submit;
        form.submit = function () {
          editor.editorManager.triggerSave();
          editor.setDirty(false);
          return form._mceOldSubmit(form);
        };
      }
    }
    editor.windowManager = WindowManager(editor);
    editor.notificationManager = NotificationManager(editor);
    if (settings.encoding === 'xml') {
      editor.on('GetContent', function (e) {
        if (e.save) {
          e.content = DOM$5.encode(e.content);
        }
      });
    }
    if (settings.add_form_submit_trigger) {
      editor.on('submit', function () {
        if (editor.initialized) {
          editor.save();
        }
      });
    }
    if (settings.add_unload_trigger) {
      editor._beforeUnload = function () {
        if (editor.initialized && !editor.destroyed && !editor.isHidden()) {
          editor.save({
            format: 'raw',
            no_events: true,
            set_dirty: false
          });
        }
      };
      editor.editorManager.on('BeforeUnload', editor._beforeUnload);
    }
    editor.editorManager.add(editor);
    loadScripts(editor, editor.suffix);
  };
  var $_4352oh4bje5o2qnn = { render: render };

  var add = function (editor, name, settings) {
    var sidebars = editor.sidebars ? editor.sidebars : [];
    sidebars.push({
      name: name,
      settings: settings
    });
    editor.sidebars = sidebars;
  };
  var $_8qp82n70je5o2r3u = { add: add };

  var each$21 = $_a5gegtjje5o2pum.each;
  var trim$4 = $_a5gegtjje5o2pum.trim;
  var queryParts = 'source protocol authority userInfo user password host port relative path directory file query anchor'.split(' ');
  var DEFAULT_PORTS = {
    ftp: 21,
    http: 80,
    https: 443,
    mailto: 25
  };
  var URI = function (url, settings) {
    var self = this;
    var baseUri, baseUrl;
    url = trim$4(url);
    settings = self.settings = settings || {};
    baseUri = settings.base_uri;
    if (/^([\w\-]+):([^\/]{2})/i.test(url) || /^\s*#/.test(url)) {
      self.source = url;
      return;
    }
    var isProtocolRelative = url.indexOf('//') === 0;
    if (url.indexOf('/') === 0 && !isProtocolRelative) {
      url = (baseUri ? baseUri.protocol || 'http' : 'http') + '://mce_host' + url;
    }
    if (!/^[\w\-]*:?\/\//.test(url)) {
      baseUrl = settings.base_uri ? settings.base_uri.path : new URI(document.location.href).directory;
      if (settings.base_uri.protocol == '') {
        url = '//mce_host' + self.toAbsPath(baseUrl, url);
      } else {
        url = /([^#?]*)([#?]?.*)/.exec(url);
        url = (baseUri && baseUri.protocol || 'http') + '://mce_host' + self.toAbsPath(baseUrl, url[1]) + url[2];
      }
    }
    url = url.replace(/@@/g, '(mce_at)');
    url = /^(?:(?![^:@]+:[^:@\/]*@)([^:\/?#.]+):)?(?:\/\/)?((?:(([^:@\/]*):?([^:@\/]*))?@)?([^:\/?#]*)(?::(\d*))?)(((\/(?:[^?#](?![^?#\/]*\.[^?#\/.]+(?:[?#]|$)))*\/?)?([^?#\/]*))(?:\?([^#]*))?(?:#(.*))?)/.exec(url);
    each$21(queryParts, function (v, i) {
      var part = url[i];
      if (part) {
        part = part.replace(/\(mce_at\)/g, '@@');
      }
      self[v] = part;
    });
    if (baseUri) {
      if (!self.protocol) {
        self.protocol = baseUri.protocol;
      }
      if (!self.userInfo) {
        self.userInfo = baseUri.userInfo;
      }
      if (!self.port && self.host === 'mce_host') {
        self.port = baseUri.port;
      }
      if (!self.host || self.host === 'mce_host') {
        self.host = baseUri.host;
      }
      self.source = '';
    }
    if (isProtocolRelative) {
      self.protocol = '';
    }
  };
  URI.prototype = {
    setPath: function (path) {
      var self = this;
      path = /^(.*?)\/?(\w+)?$/.exec(path);
      self.path = path[0];
      self.directory = path[1];
      self.file = path[2];
      self.source = '';
      self.getURI();
    },
    toRelative: function (uri) {
      var self = this;
      var output;
      if (uri === './') {
        return uri;
      }
      uri = new URI(uri, { base_uri: self });
      if (uri.host !== 'mce_host' && self.host !== uri.host && uri.host || self.port !== uri.port || self.protocol !== uri.protocol && uri.protocol !== '') {
        return uri.getURI();
      }
      var tu = self.getURI(), uu = uri.getURI();
      if (tu === uu || tu.charAt(tu.length - 1) === '/' && tu.substr(0, tu.length - 1) === uu) {
        return tu;
      }
      output = self.toRelPath(self.path, uri.path);
      if (uri.query) {
        output += '?' + uri.query;
      }
      if (uri.anchor) {
        output += '#' + uri.anchor;
      }
      return output;
    },
    toAbsolute: function (uri, noHost) {
      uri = new URI(uri, { base_uri: this });
      return uri.getURI(noHost && this.isSameOrigin(uri));
    },
    isSameOrigin: function (uri) {
      if (this.host == uri.host && this.protocol == uri.protocol) {
        if (this.port == uri.port) {
          return true;
        }
        var defaultPort = DEFAULT_PORTS[this.protocol];
        if (defaultPort && (this.port || defaultPort) == (uri.port || defaultPort)) {
          return true;
        }
      }
      return false;
    },
    toRelPath: function (base, path) {
      var items, breakPoint = 0, out = '', i, l;
      base = base.substring(0, base.lastIndexOf('/'));
      base = base.split('/');
      items = path.split('/');
      if (base.length >= items.length) {
        for (i = 0, l = base.length; i < l; i++) {
          if (i >= items.length || base[i] !== items[i]) {
            breakPoint = i + 1;
            break;
          }
        }
      }
      if (base.length < items.length) {
        for (i = 0, l = items.length; i < l; i++) {
          if (i >= base.length || base[i] !== items[i]) {
            breakPoint = i + 1;
            break;
          }
        }
      }
      if (breakPoint === 1) {
        return path;
      }
      for (i = 0, l = base.length - (breakPoint - 1); i < l; i++) {
        out += '../';
      }
      for (i = breakPoint - 1, l = items.length; i < l; i++) {
        if (i !== breakPoint - 1) {
          out += '/' + items[i];
        } else {
          out += items[i];
        }
      }
      return out;
    },
    toAbsPath: function (base, path) {
      var i, nb = 0, o = [], tr, outPath;
      tr = /\/$/.test(path) ? '/' : '';
      base = base.split('/');
      path = path.split('/');
      each$21(base, function (k) {
        if (k) {
          o.push(k);
        }
      });
      base = o;
      for (i = path.length - 1, o = []; i >= 0; i--) {
        if (path[i].length === 0 || path[i] === '.') {
          continue;
        }
        if (path[i] === '..') {
          nb++;
          continue;
        }
        if (nb > 0) {
          nb--;
          continue;
        }
        o.push(path[i]);
      }
      i = base.length - nb;
      if (i <= 0) {
        outPath = o.reverse().join('/');
      } else {
        outPath = base.slice(0, i).join('/') + '/' + o.reverse().join('/');
      }
      if (outPath.indexOf('/') !== 0) {
        outPath = '/' + outPath;
      }
      if (tr && outPath.lastIndexOf('/') !== outPath.length - 1) {
        outPath += tr;
      }
      return outPath;
    },
    getURI: function (noProtoHost) {
      var s;
      var self = this;
      if (!self.source || noProtoHost) {
        s = '';
        if (!noProtoHost) {
          if (self.protocol) {
            s += self.protocol + '://';
          } else {
            s += '//';
          }
          if (self.userInfo) {
            s += self.userInfo + '@';
          }
          if (self.host) {
            s += self.host;
          }
          if (self.port) {
            s += ':' + self.port;
          }
        }
        if (self.path) {
          s += self.path;
        }
        if (self.query) {
          s += '?' + self.query;
        }
        if (self.anchor) {
          s += '#' + self.anchor;
        }
        self.source = s;
      }
      return self.source;
    }
  };
  URI.parseDataUri = function (uri) {
    var type, matches;
    uri = decodeURIComponent(uri).split(',');
    matches = /data:([^;]+)/.exec(uri[0]);
    if (matches) {
      type = matches[1];
    }
    return {
      type: type,
      data: uri[1]
    };
  };
  URI.getDocumentBaseUrl = function (loc) {
    var baseUrl;
    if (loc.protocol.indexOf('http') !== 0 && loc.protocol !== 'file:') {
      baseUrl = loc.href;
    } else {
      baseUrl = loc.protocol + '//' + loc.host + loc.pathname;
    }
    if (/^[^:]+:\/\/\/?[^\/]+\//.test(baseUrl)) {
      baseUrl = baseUrl.replace(/[\?#].*$/, '').replace(/[\/\\][^\/]+$/, '');
      if (!/[\/\\]$/.test(baseUrl)) {
        baseUrl += '/';
      }
    }
    return baseUrl;
  };

  var traverse = function (node, fn) {
    fn(node);
    if (node.firstChild) {
      traverse(node.firstChild, fn);
    }
    if (node.next) {
      traverse(node.next, fn);
    }
  };
  var findMatchingNodes = function (nodeFilters, attributeFilters, node) {
    var nodeMatches = {};
    var attrMatches = {};
    var matches = [];
    if (node.firstChild) {
      traverse(node.firstChild, function (node) {
        $_bwcteb4je5o2pl4.each(nodeFilters, function (filter) {
          if (filter.name === node.name) {
            if (nodeMatches[filter.name]) {
              nodeMatches[filter.name].nodes.push(node);
            } else {
              nodeMatches[filter.name] = {
                filter: filter,
                nodes: [node]
              };
            }
          }
        });
        $_bwcteb4je5o2pl4.each(attributeFilters, function (filter) {
          if (typeof node.attr(filter.name) === 'string') {
            if (attrMatches[filter.name]) {
              attrMatches[filter.name].nodes.push(node);
            } else {
              attrMatches[filter.name] = {
                filter: filter,
                nodes: [node]
              };
            }
          }
        });
      });
    }
    for (var name_1 in nodeMatches) {
      if (nodeMatches.hasOwnProperty(name_1)) {
        matches.push(nodeMatches[name_1]);
      }
    }
    for (var name_2 in attrMatches) {
      if (attrMatches.hasOwnProperty(name_2)) {
        matches.push(attrMatches[name_2]);
      }
    }
    return matches;
  };
  var filter$3 = function (nodeFilters, attributeFilters, node) {
    var matches = findMatchingNodes(nodeFilters, attributeFilters, node);
    $_bwcteb4je5o2pl4.each(matches, function (match) {
      $_bwcteb4je5o2pl4.each(match.filter.callbacks, function (callback) {
        callback(match.nodes, match.filter.name, {});
      });
    });
  };

  var defaultFormat = 'html';
  var isTreeNode = function (content) {
    return content instanceof Node$2;
  };
  var setContentString = function (editor, content, args) {
    var body = editor.getBody();
    var forcedRootBlockName, padd;
    if (content.length === 0 || /^\s+$/.test(content)) {
      padd = '<br data-mce-bogus="1">';
      if (body.nodeName === 'TABLE') {
        content = '<tr><td>' + padd + '</td></tr>';
      } else if (/^(UL|OL)$/.test(body.nodeName)) {
        content = '<li>' + padd + '</li>';
      }
      forcedRootBlockName = editor.settings.forced_root_block;
      if (forcedRootBlockName && editor.schema.isValidChild(body.nodeName.toLowerCase(), forcedRootBlockName.toLowerCase())) {
        content = padd;
        content = editor.dom.createHTML(forcedRootBlockName, editor.settings.forced_root_block_attrs, content);
      } else if (!content) {
        content = '<br data-mce-bogus="1">';
      }
      editor.dom.setHTML(body, content);
      editor.fire('SetContent', args);
    } else {
      if (args.format !== 'raw') {
        content = HtmlSerializer({ validate: editor.validate }, editor.schema).serialize(editor.parser.parse(content, {
          isRootContent: true,
          insert: true
        }));
      }
      args.content = $_a5gegtjje5o2pum.trim(content);
      editor.dom.setHTML(body, args.content);
      if (!args.no_events) {
        editor.fire('SetContent', args);
      }
    }
    return args.content;
  };
  var setContentTree = function (editor, content, args) {
    filter$3(editor.parser.getNodeFilters(), editor.parser.getAttributeFilters(), content);
    var html = HtmlSerializer({ validate: editor.validate }, editor.schema).serialize(content);
    args.content = $_a5gegtjje5o2pum.trim(html);
    editor.dom.setHTML(editor.getBody(), args.content);
    if (!args.no_events) {
      editor.fire('SetContent', args);
    }
    return content;
  };
  var setContent$1 = function (editor, content, args) {
    if (args === void 0) {
      args = {};
    }
    args.format = args.format ? args.format : defaultFormat;
    args.set = true;
    args.content = isTreeNode(content) ? '' : content;
    if (!isTreeNode(content) && !args.no_events) {
      editor.fire('BeforeSetContent', args);
      content = args.content;
    }
    return isTreeNode(content) ? setContentTree(editor, content, args) : setContentString(editor, content, args);
  };
  var getContent$1 = function (editor, args) {
    if (args === void 0) {
      args = {};
    }
    var content;
    var body = editor.getBody();
    if (editor.removed) {
      return '';
    }
    args.format = args.format ? args.format : defaultFormat;
    args.get = true;
    args.getInner = true;
    if (!args.no_events) {
      editor.fire('BeforeGetContent', args);
    }
    if (args.format === 'raw') {
      content = $_a5gegtjje5o2pum.trim($_15ih885cje5o2qt6.trimExternal(editor.serializer, body.innerHTML));
    } else if (args.format === 'text') {
      content = body.innerText || body.textContent;
    } else if (args.format === 'tree') {
      return editor.serializer.serialize(body, args);
    } else {
      content = editor.serializer.serialize(body, args);
    }
    if (args.format !== 'text') {
      args.content = $_a5gegtjje5o2pum.trim(content);
    } else {
      args.content = content;
    }
    if (!args.no_events) {
      editor.fire('GetContent', args);
    }
    return args.content;
  };

  var DOM$6 = DOMUtils.DOM;
  var restoreOriginalStyles = function (editor) {
    DOM$6.setStyle(editor.id, 'display', editor.orgDisplay);
  };
  var safeDestroy = function (x) {
    return Option.from(x).each(function (x) {
      return x.destroy();
    });
  };
  var clearDomReferences = function (editor) {
    editor.contentAreaContainer = editor.formElement = editor.container = editor.editorContainer = null;
    editor.bodyElement = editor.contentDocument = editor.contentWindow = null;
    editor.iframeElement = editor.targetElm = null;
    if (editor.selection) {
      editor.selection = editor.selection.win = editor.selection.dom = editor.selection.dom.doc = null;
    }
  };
  var restoreForm = function (editor) {
    var form = editor.formElement;
    if (form) {
      if (form._mceOldSubmit) {
        form.submit = form._mceOldSubmit;
        form._mceOldSubmit = null;
      }
      DOM$6.unbind(form, 'submit reset', editor.formEventDelegate);
    }
  };
  var remove$5 = function (editor) {
    if (!editor.removed) {
      var _selectionOverrides = editor._selectionOverrides, editorUpload = editor.editorUpload;
      var body = editor.getBody();
      var element = editor.getElement();
      if (body) {
        editor.save();
      }
      editor.removed = 1;
      editor.unbindAllNativeEvents();
      if (editor.hasHiddenInput && element) {
        DOM$6.remove(element.nextSibling);
      }
      if (!editor.inline && body) {
        restoreOriginalStyles(editor);
      }
      $_d6jl355vje5o2qw5.fireRemove(editor);
      editor.editorManager.remove(editor);
      DOM$6.remove(editor.getContainer());
      safeDestroy(_selectionOverrides);
      safeDestroy(editorUpload);
      editor.destroy();
    }
  };
  var destroy = function (editor, automatic) {
    var selection = editor.selection, dom = editor.dom;
    if (editor.destroyed) {
      return;
    }
    if (!automatic && !editor.removed) {
      editor.remove();
      return;
    }
    if (!automatic) {
      editor.editorManager.off('beforeunload', editor._beforeUnload);
      if (editor.theme && editor.theme.destroy) {
        editor.theme.destroy();
      }
      safeDestroy(selection);
      safeDestroy(dom);
    }
    restoreForm(editor);
    clearDomReferences(editor);
    editor.destroyed = 1;
  };

  var DOM$7 = DOMUtils.DOM;
  var extend$4 = $_a5gegtjje5o2pum.extend;
  var each$22 = $_a5gegtjje5o2pum.each;
  var resolve$4 = $_a5gegtjje5o2pum.resolve;
  var ie$2 = $_9bd8pn9je5o2pp2.ie;
  var Editor = function (id, settings, editorManager) {
    var self = this;
    var documentBaseUrl, baseUri;
    documentBaseUrl = self.documentBaseUrl = editorManager.documentBaseURL;
    baseUri = editorManager.baseURI;
    settings = getEditorSettings(self, id, documentBaseUrl, editorManager.defaultSettings, settings);
    self.settings = settings;
    AddOnManager.language = settings.language || 'en';
    AddOnManager.languageLoad = settings.language_load;
    AddOnManager.baseURL = editorManager.baseURL;
    self.id = id;
    self.setDirty(false);
    self.plugins = {};
    self.documentBaseURI = new URI(settings.document_base_url, { base_uri: baseUri });
    self.baseURI = baseUri;
    self.contentCSS = [];
    self.contentStyles = [];
    self.shortcuts = new Shortcuts(self);
    self.loadedCSS = {};
    self.editorCommands = new EditorCommands(self);
    self.suffix = editorManager.suffix;
    self.editorManager = editorManager;
    self.inline = settings.inline;
    self.buttons = {};
    self.menuItems = {};
    if (settings.cache_suffix) {
      $_9bd8pn9je5o2pp2.cacheSuffix = settings.cache_suffix.replace(/^[\?\&]+/, '');
    }
    if (settings.override_viewport === false) {
      $_9bd8pn9je5o2pp2.overrideViewPort = false;
    }
    editorManager.fire('SetupEditor', { editor: self });
    self.execCallback('setup', self);
    self.$ = DomQuery.overrideDefaults(function () {
      return {
        context: self.inline ? self.getBody() : self.getDoc(),
        element: self.getBody()
      };
    });
  };
  Editor.prototype = {
    render: function () {
      $_4352oh4bje5o2qnn.render(this);
    },
    focus: function (skipFocus) {
      $_llx5i48je5o2qna.focus(this, skipFocus);
    },
    execCallback: function (name) {
      var self = this;
      var callback = self.settings[name], scope;
      if (!callback) {
        return;
      }
      if (self.callbackLookup && (scope = self.callbackLookup[name])) {
        callback = scope.func;
        scope = scope.scope;
      }
      if (typeof callback === 'string') {
        scope = callback.replace(/\.\w+$/, '');
        scope = scope ? resolve$4(scope) : 0;
        callback = resolve$4(callback);
        self.callbackLookup = self.callbackLookup || {};
        self.callbackLookup[name] = {
          func: callback,
          scope: scope
        };
      }
      return callback.apply(scope || self, Array.prototype.slice.call(arguments, 1));
    },
    translate: function (text) {
      if (text && $_a5gegtjje5o2pum.is(text, 'string')) {
        var lang_1 = this.settings.language || 'en', i18n_1 = this.editorManager.i18n;
        text = i18n_1.data[lang_1 + '.' + text] || text.replace(/\{\#([^\}]+)\}/g, function (a, b) {
          return i18n_1.data[lang_1 + '.' + b] || '{#' + b + '}';
        });
      }
      return this.editorManager.translate(text);
    },
    getLang: function (name, defaultVal) {
      return this.editorManager.i18n.data[(this.settings.language || 'en') + '.' + name] || (defaultVal !== undefined ? defaultVal : '{#' + name + '}');
    },
    getParam: function (name, defaultVal, type) {
      return getParam(this, name, defaultVal, type);
    },
    nodeChanged: function (args) {
      this._nodeChangeDispatcher.nodeChanged(args);
    },
    addButton: function (name, settings) {
      var self = this;
      if (settings.cmd) {
        settings.onclick = function () {
          self.execCommand(settings.cmd);
        };
      }
      if (settings.stateSelector && typeof settings.active === 'undefined') {
        settings.active = false;
      }
      if (!settings.text && !settings.icon) {
        settings.icon = name;
      }
      self.buttons = self.buttons;
      settings.tooltip = settings.tooltip || settings.title;
      self.buttons[name] = settings;
    },
    addSidebar: function (name, settings) {
      return $_8qp82n70je5o2r3u.add(this, name, settings);
    },
    addMenuItem: function (name, settings) {
      var self = this;
      if (settings.cmd) {
        settings.onclick = function () {
          self.execCommand(settings.cmd);
        };
      }
      self.menuItems = self.menuItems;
      self.menuItems[name] = settings;
    },
    addContextToolbar: function (predicate, items) {
      var self = this;
      var selector;
      self.contextToolbars = self.contextToolbars || [];
      if (typeof predicate === 'string') {
        selector = predicate;
        predicate = function (elm) {
          return self.dom.is(elm, selector);
        };
      }
      self.contextToolbars.push({
        id: $_2bgddd4wje5o2qqo.uuid('mcet'),
        predicate: predicate,
        items: items
      });
    },
    addCommand: function (name, callback, scope) {
      this.editorCommands.addCommand(name, callback, scope);
    },
    addQueryStateHandler: function (name, callback, scope) {
      this.editorCommands.addQueryStateHandler(name, callback, scope);
    },
    addQueryValueHandler: function (name, callback, scope) {
      this.editorCommands.addQueryValueHandler(name, callback, scope);
    },
    addShortcut: function (pattern, desc, cmdFunc, scope) {
      this.shortcuts.add(pattern, desc, cmdFunc, scope);
    },
    execCommand: function (cmd, ui, value, args) {
      return this.editorCommands.execCommand(cmd, ui, value, args);
    },
    queryCommandState: function (cmd) {
      return this.editorCommands.queryCommandState(cmd);
    },
    queryCommandValue: function (cmd) {
      return this.editorCommands.queryCommandValue(cmd);
    },
    queryCommandSupported: function (cmd) {
      return this.editorCommands.queryCommandSupported(cmd);
    },
    show: function () {
      var self = this;
      if (self.hidden) {
        self.hidden = false;
        if (self.inline) {
          self.getBody().contentEditable = true;
        } else {
          DOM$7.show(self.getContainer());
          DOM$7.hide(self.id);
        }
        self.load();
        self.fire('show');
      }
    },
    hide: function () {
      var self = this, doc = self.getDoc();
      if (!self.hidden) {
        if (ie$2 && doc && !self.inline) {
          doc.execCommand('SelectAll');
        }
        self.save();
        if (self.inline) {
          self.getBody().contentEditable = false;
          if (self === self.editorManager.focusedEditor) {
            self.editorManager.focusedEditor = null;
          }
        } else {
          DOM$7.hide(self.getContainer());
          DOM$7.setStyle(self.id, 'display', self.orgDisplay);
        }
        self.hidden = true;
        self.fire('hide');
      }
    },
    isHidden: function () {
      return !!this.hidden;
    },
    setProgressState: function (state, time) {
      this.fire('ProgressState', {
        state: state,
        time: time
      });
    },
    load: function (args) {
      var self = this;
      var elm = self.getElement(), html;
      if (self.removed) {
        return '';
      }
      if (elm) {
        args = args || {};
        args.load = true;
        html = self.setContent(elm.value !== undefined ? elm.value : elm.innerHTML, args);
        args.element = elm;
        if (!args.no_events) {
          self.fire('LoadContent', args);
        }
        args.element = elm = null;
        return html;
      }
    },
    save: function (args) {
      var self = this;
      var elm = self.getElement(), html, form;
      if (!elm || !self.initialized || self.removed) {
        return;
      }
      args = args || {};
      args.save = true;
      args.element = elm;
      html = args.content = self.getContent(args);
      if (!args.no_events) {
        self.fire('SaveContent', args);
      }
      if (args.format === 'raw') {
        self.fire('RawSaveContent', args);
      }
      html = args.content;
      if (!/TEXTAREA|INPUT/i.test(elm.nodeName)) {
        if (!self.inline) {
          elm.innerHTML = html;
        }
        if (form = DOM$7.getParent(self.id, 'form')) {
          each$22(form.elements, function (elm) {
            if (elm.name === self.id) {
              elm.value = html;
              return false;
            }
          });
        }
      } else {
        elm.value = html;
      }
      args.element = elm = null;
      if (args.set_dirty !== false) {
        self.setDirty(false);
      }
      return html;
    },
    setContent: function (content, args) {
      return setContent$1(this, content, args);
    },
    getContent: function (args) {
      return getContent$1(this, args);
    },
    insertContent: function (content, args) {
      if (args) {
        content = extend$4({ content: content }, args);
      }
      this.execCommand('mceInsertContent', false, content);
    },
    isDirty: function () {
      return !this.isNotDirty;
    },
    setDirty: function (state) {
      var oldState = !this.isNotDirty;
      this.isNotDirty = !state;
      if (state && state !== oldState) {
        this.fire('dirty');
      }
    },
    setMode: function (mode) {
      $_7uzwj846je5o2qmw.setMode(this, mode);
    },
    getContainer: function () {
      var self = this;
      if (!self.container) {
        self.container = DOM$7.get(self.editorContainer || self.id + '_parent');
      }
      return self.container;
    },
    getContentAreaContainer: function () {
      return this.contentAreaContainer;
    },
    getElement: function () {
      if (!this.targetElm) {
        this.targetElm = DOM$7.get(this.id);
      }
      return this.targetElm;
    },
    getWin: function () {
      var self = this;
      var elm;
      if (!self.contentWindow) {
        elm = self.iframeElement;
        if (elm) {
          self.contentWindow = elm.contentWindow;
        }
      }
      return self.contentWindow;
    },
    getDoc: function () {
      var self = this;
      var win;
      if (!self.contentDocument) {
        win = self.getWin();
        if (win) {
          self.contentDocument = win.document;
        }
      }
      return self.contentDocument;
    },
    getBody: function () {
      var doc = this.getDoc();
      return this.bodyElement || (doc ? doc.body : null);
    },
    convertURL: function (url, name, elm) {
      var self = this, settings = self.settings;
      if (settings.urlconverter_callback) {
        return self.execCallback('urlconverter_callback', url, elm, true, name);
      }
      if (!settings.convert_urls || elm && elm.nodeName === 'LINK' || url.indexOf('file:') === 0 || url.length === 0) {
        return url;
      }
      if (settings.relative_urls) {
        return self.documentBaseURI.toRelative(url);
      }
      url = self.documentBaseURI.toAbsolute(url, settings.remove_script_host);
      return url;
    },
    addVisual: function (elm) {
      var self = this;
      var settings = self.settings;
      var dom = self.dom;
      var cls;
      elm = elm || self.getBody();
      if (self.hasVisual === undefined) {
        self.hasVisual = settings.visual;
      }
      each$22(dom.select('table,a', elm), function (elm) {
        var value;
        switch (elm.nodeName) {
        case 'TABLE':
          cls = settings.visual_table_class || 'mce-item-table';
          value = dom.getAttrib(elm, 'border');
          if ((!value || value === '0') && self.hasVisual) {
            dom.addClass(elm, cls);
          } else {
            dom.removeClass(elm, cls);
          }
          return;
        case 'A':
          if (!dom.getAttrib(elm, 'href', false)) {
            value = dom.getAttrib(elm, 'name') || elm.id;
            cls = settings.visual_anchor_class || 'mce-item-anchor';
            if (value && self.hasVisual) {
              dom.addClass(elm, cls);
            } else {
              dom.removeClass(elm, cls);
            }
          }
          return;
        }
      });
      self.fire('VisualAid', {
        element: elm,
        hasVisual: self.hasVisual
      });
    },
    remove: function () {
      remove$5(this);
    },
    destroy: function (automatic) {
      destroy(this, automatic);
    },
    uploadImages: function (callback) {
      return this.editorUpload.uploadImages(callback);
    },
    _scanForImages: function () {
      return this.editorUpload.scanForImages();
    }
  };
  extend$4(Editor.prototype, EditorObservable$1);

  var isEditorUIElement = function (elm) {
    return elm.className.toString().indexOf('mce-') !== -1;
  };
  var $_97d4iv77je5o2r4x = { isEditorUIElement: isEditorUIElement };

  var isManualNodeChange = function (e) {
    return e.type === 'nodechange' && e.selectionChange;
  };
  var registerPageMouseUp = function (editor, throttledStore) {
    var mouseUpPage = function () {
      throttledStore.throttle();
    };
    DOMUtils.DOM.bind(document, 'mouseup', mouseUpPage);
    editor.on('remove', function () {
      DOMUtils.DOM.unbind(document, 'mouseup', mouseUpPage);
    });
  };
  var registerFocusOut = function (editor) {
    editor.on('focusout', function () {
      $_fnd7h33zje5o2qly.store(editor);
    });
  };
  var registerMouseUp = function (editor, throttledStore) {
    editor.on('mouseup touchend', function (e) {
      throttledStore.throttle();
    });
  };
  var registerEditorEvents = function (editor, throttledStore) {
    var browser = $_2hq237mje5o2pvu.detect().browser;
    if (browser.isIE() || browser.isEdge()) {
      registerFocusOut(editor);
    } else {
      registerMouseUp(editor, throttledStore);
    }
    editor.on('keyup nodechange', function (e) {
      if (!isManualNodeChange(e)) {
        $_fnd7h33zje5o2qly.store(editor);
      }
    });
  };
  var register$3 = function (editor) {
    var throttledStore = $_7qjijn58je5o2qsg.first(function () {
      $_fnd7h33zje5o2qly.store(editor);
    }, 0);
    if (editor.inline) {
      registerPageMouseUp(editor, throttledStore);
    }
    editor.on('init', function () {
      registerEditorEvents(editor, throttledStore);
    });
    editor.on('remove', function () {
      throttledStore.cancel();
    });
  };
  var $_eam19v78je5o2r4y = { register: register$3 };

  var documentFocusInHandler;
  var DOM$8 = DOMUtils.DOM;
  var isEditorUIElement$1 = function (elm) {
    return $_97d4iv77je5o2r4x.isEditorUIElement(elm);
  };
  var isUIElement = function (editor, elm) {
    var customSelector = editor ? editor.settings.custom_ui_selector : '';
    var parent = DOM$8.getParent(elm, function (elm) {
      return isEditorUIElement$1(elm) || (customSelector ? editor.dom.is(elm, customSelector) : false);
    });
    return parent !== null;
  };
  var getActiveElement = function () {
    try {
      return document.activeElement;
    } catch (ex) {
      return document.body;
    }
  };
  var registerEvents = function (editorManager, e) {
    var editor = e.editor;
    $_eam19v78je5o2r4y.register(editor);
    editor.on('focusin', function () {
      var self = this;
      var focusedEditor = editorManager.focusedEditor;
      if (focusedEditor !== self) {
        if (focusedEditor) {
          focusedEditor.fire('blur', { focusedEditor: self });
        }
        editorManager.setActive(self);
        editorManager.focusedEditor = self;
        self.fire('focus', { blurredEditor: focusedEditor });
        self.focus(true);
      }
    });
    editor.on('focusout', function () {
      var self = this;
      $_db41wogje5o2ps3.setEditorTimeout(self, function () {
        var focusedEditor = editorManager.focusedEditor;
        if (!isUIElement(self, getActiveElement()) && focusedEditor === self) {
          self.fire('blur', { focusedEditor: null });
          editorManager.focusedEditor = null;
        }
      });
    });
    if (!documentFocusInHandler) {
      documentFocusInHandler = function (e) {
        var activeEditor = editorManager.activeEditor;
        var target;
        target = e.target;
        if (activeEditor && target.ownerDocument === document) {
          if (target !== document.body && !isUIElement(activeEditor, target) && editorManager.focusedEditor === activeEditor) {
            activeEditor.fire('blur', { focusedEditor: null });
            editorManager.focusedEditor = null;
          }
        }
      };
      DOM$8.bind(document, 'focusin', documentFocusInHandler);
    }
  };
  var unregisterDocumentEvents = function (editorManager, e) {
    if (editorManager.focusedEditor === e.editor) {
      editorManager.focusedEditor = null;
    }
    if (!editorManager.activeEditor) {
      DOM$8.unbind(document, 'focusin', documentFocusInHandler);
      documentFocusInHandler = null;
    }
  };
  var setup$11 = function (editorManager) {
    editorManager.on('AddEditor', $_enfhkv6je5o2ply.curry(registerEvents, editorManager));
    editorManager.on('RemoveEditor', $_enfhkv6je5o2ply.curry(unregisterDocumentEvents, editorManager));
  };
  var $_cuv7pv76je5o2r4u = {
    setup: setup$11,
    isEditorUIElement: isEditorUIElement$1,
    isUIElement: isUIElement
  };

  var data = {};
  var code = 'en';
  var $_34fkt779je5o2r5d = {
    setCode: function (newCode) {
      if (newCode) {
        code = newCode;
        this.rtl = this.data[newCode] ? this.data[newCode]._dir === 'rtl' : false;
      }
    },
    getCode: function () {
      return code;
    },
    rtl: false,
    add: function (code, items) {
      var langData = data[code];
      if (!langData) {
        data[code] = langData = {};
      }
      for (var name_1 in items) {
        langData[name_1] = items[name_1];
      }
      this.setCode(code);
    },
    translate: function (text) {
      var langData = data[code] || {};
      var toString = function (obj) {
        if ($_a5gegtjje5o2pum.is(obj, 'function')) {
          return Object.prototype.toString.call(obj);
        }
        return !isEmpty(obj) ? '' + obj : '';
      };
      var isEmpty = function (text) {
        return text === '' || text === null || $_a5gegtjje5o2pum.is(text, 'undefined');
      };
      var getLangData = function (text) {
        text = toString(text);
        return $_a5gegtjje5o2pum.hasOwn(langData, text) ? toString(langData[text]) : text;
      };
      if (isEmpty(text)) {
        return '';
      }
      if ($_a5gegtjje5o2pum.is(text, 'object') && $_a5gegtjje5o2pum.hasOwn(text, 'raw')) {
        return toString(text.raw);
      }
      if ($_a5gegtjje5o2pum.is(text, 'array')) {
        var values_1 = text.slice(1);
        text = getLangData(text[0]).replace(/\{([0-9]+)\}/g, function ($1, $2) {
          return $_a5gegtjje5o2pum.hasOwn(values_1, $2) ? toString(values_1[$2]) : $1;
        });
      }
      return getLangData(text).replace(/{context:\w+}$/, '');
    },
    data: data
  };

  var DOM$9 = DOMUtils.DOM;
  var explode$5 = $_a5gegtjje5o2pum.explode;
  var each$23 = $_a5gegtjje5o2pum.each;
  var extend$5 = $_a5gegtjje5o2pum.extend;
  var instanceCounter = 0;
  var beforeUnloadDelegate;
  var EditorManager;
  var boundGlobalEvents = false;
  var legacyEditors = [];
  var editors = [];
  var isValidLegacyKey = function (id) {
    return id !== 'length';
  };
  var globalEventDelegate = function (e) {
    each$23(EditorManager.get(), function (editor) {
      if (e.type === 'scroll') {
        editor.fire('ScrollWindow', e);
      } else {
        editor.fire('ResizeWindow', e);
      }
    });
  };
  var toggleGlobalEvents = function (state) {
    if (state !== boundGlobalEvents) {
      if (state) {
        DomQuery(window).on('resize scroll', globalEventDelegate);
      } else {
        DomQuery(window).off('resize scroll', globalEventDelegate);
      }
      boundGlobalEvents = state;
    }
  };
  var removeEditorFromList = function (targetEditor) {
    var oldEditors = editors;
    delete legacyEditors[targetEditor.id];
    for (var i = 0; i < legacyEditors.length; i++) {
      if (legacyEditors[i] === targetEditor) {
        legacyEditors.splice(i, 1);
        break;
      }
    }
    editors = $_bwcteb4je5o2pl4.filter(editors, function (editor) {
      return targetEditor !== editor;
    });
    if (EditorManager.activeEditor === targetEditor) {
      EditorManager.activeEditor = editors.length > 0 ? editors[0] : null;
    }
    if (EditorManager.focusedEditor === targetEditor) {
      EditorManager.focusedEditor = null;
    }
    return oldEditors.length !== editors.length;
  };
  var purgeDestroyedEditor = function (editor) {
    if (editor && editor.initialized && !(editor.getContainer() || editor.getBody()).parentNode) {
      removeEditorFromList(editor);
      editor.unbindAllNativeEvents();
      editor.destroy(true);
      editor.removed = true;
      editor = null;
    }
    return editor;
  };
  EditorManager = {
    defaultSettings: {},
    $: DomQuery,
    majorVersion: '4',
    minorVersion: '7.9',
    releaseDate: '2018-02-27',
    editors: legacyEditors,
    i18n: $_34fkt779je5o2r5d,
    activeEditor: null,
    settings: {},
    setup: function () {
      var self = this;
      var baseURL, documentBaseURL, suffix = '', preInit, src;
      documentBaseURL = URI.getDocumentBaseUrl(document.location);
      if (/^[^:]+:\/\/\/?[^\/]+\//.test(documentBaseURL)) {
        documentBaseURL = documentBaseURL.replace(/[\?#].*$/, '').replace(/[\/\\][^\/]+$/, '');
        if (!/[\/\\]$/.test(documentBaseURL)) {
          documentBaseURL += '/';
        }
      }
      preInit = window.tinymce || window.tinyMCEPreInit;
      if (preInit) {
        baseURL = preInit.base || preInit.baseURL;
        suffix = preInit.suffix;
      } else {
        var scripts = document.getElementsByTagName('script');
        for (var i = 0; i < scripts.length; i++) {
          src = scripts[i].src;
          var srcScript = src.substring(src.lastIndexOf('/'));
          if (/tinymce(\.full|\.jquery|)(\.min|\.dev|)\.js/.test(src)) {
            if (srcScript.indexOf('.min') !== -1) {
              suffix = '.min';
            }
            baseURL = src.substring(0, src.lastIndexOf('/'));
            break;
          }
        }
        if (!baseURL && document.currentScript) {
          src = document.currentScript.src;
          if (src.indexOf('.min') !== -1) {
            suffix = '.min';
          }
          baseURL = src.substring(0, src.lastIndexOf('/'));
        }
      }
      self.baseURL = new URI(documentBaseURL).toAbsolute(baseURL);
      self.documentBaseURL = documentBaseURL;
      self.baseURI = new URI(self.baseURL);
      self.suffix = suffix;
      $_cuv7pv76je5o2r4u.setup(self);
    },
    overrideDefaults: function (defaultSettings) {
      var baseUrl, suffix;
      baseUrl = defaultSettings.base_url;
      if (baseUrl) {
        this.baseURL = new URI(this.documentBaseURL).toAbsolute(baseUrl.replace(/\/+$/, ''));
        this.baseURI = new URI(this.baseURL);
      }
      suffix = defaultSettings.suffix;
      if (defaultSettings.suffix) {
        this.suffix = suffix;
      }
      this.defaultSettings = defaultSettings;
      var pluginBaseUrls = defaultSettings.plugin_base_urls;
      for (var name_1 in pluginBaseUrls) {
        AddOnManager.PluginManager.urls[name_1] = pluginBaseUrls[name_1];
      }
    },
    init: function (settings) {
      var self = this;
      var result, invalidInlineTargets;
      invalidInlineTargets = $_a5gegtjje5o2pum.makeMap('area base basefont br col frame hr img input isindex link meta param embed source wbr track ' + 'colgroup option tbody tfoot thead tr script noscript style textarea video audio iframe object menu', ' ');
      var isInvalidInlineTarget = function (settings, elm) {
        return settings.inline && elm.tagName.toLowerCase() in invalidInlineTargets;
      };
      var createId = function (elm) {
        var id = elm.id;
        if (!id) {
          id = elm.name;
          if (id && !DOM$9.get(id)) {
            id = elm.name;
          } else {
            id = DOM$9.uniqueId();
          }
          elm.setAttribute('id', id);
        }
        return id;
      };
      var execCallback = function (name) {
        var callback = settings[name];
        if (!callback) {
          return;
        }
        return callback.apply(self, Array.prototype.slice.call(arguments, 2));
      };
      var hasClass = function (elm, className) {
        return className.constructor === RegExp ? className.test(elm.className) : DOM$9.hasClass(elm, className);
      };
      var findTargets = function (settings) {
        var l, targets = [];
        if ($_9bd8pn9je5o2pp2.ie && $_9bd8pn9je5o2pp2.ie < 11) {
          $_axrd1j4hje5o2qor.initError('TinyMCE does not support the browser you are using. For a list of supported' + ' browsers please see: https://www.tinymce.com/docs/get-started/system-requirements/');
          return [];
        }
        if (settings.types) {
          each$23(settings.types, function (type) {
            targets = targets.concat(DOM$9.select(type.selector));
          });
          return targets;
        } else if (settings.selector) {
          return DOM$9.select(settings.selector);
        } else if (settings.target) {
          return [settings.target];
        }
        switch (settings.mode) {
        case 'exact':
          l = settings.elements || '';
          if (l.length > 0) {
            each$23(explode$5(l), function (id) {
              var elm;
              if (elm = DOM$9.get(id)) {
                targets.push(elm);
              } else {
                each$23(document.forms, function (f) {
                  each$23(f.elements, function (e) {
                    if (e.name === id) {
                      id = 'mce_editor_' + instanceCounter++;
                      DOM$9.setAttrib(e, 'id', id);
                      targets.push(e);
                    }
                  });
                });
              }
            });
          }
          break;
        case 'textareas':
        case 'specific_textareas':
          each$23(DOM$9.select('textarea'), function (elm) {
            if (settings.editor_deselector && hasClass(elm, settings.editor_deselector)) {
              return;
            }
            if (!settings.editor_selector || hasClass(elm, settings.editor_selector)) {
              targets.push(elm);
            }
          });
          break;
        }
        return targets;
      };
      var provideResults = function (editors) {
        result = editors;
      };
      var initEditors = function () {
        var initCount = 0;
        var editors = [];
        var targets;
        var createEditor = function (id, settings, targetElm) {
          var editor = new Editor(id, settings, self);
          editors.push(editor);
          editor.on('init', function () {
            if (++initCount === targets.length) {
              provideResults(editors);
            }
          });
          editor.targetElm = editor.targetElm || targetElm;
          editor.render();
        };
        DOM$9.unbind(window, 'ready', initEditors);
        execCallback('onpageload');
        targets = DomQuery.unique(findTargets(settings));
        if (settings.types) {
          each$23(settings.types, function (type) {
            $_a5gegtjje5o2pum.each(targets, function (elm) {
              if (DOM$9.is(elm, type.selector)) {
                createEditor(createId(elm), extend$5({}, settings, type), elm);
                return false;
              }
              return true;
            });
          });
          return;
        }
        $_a5gegtjje5o2pum.each(targets, function (elm) {
          purgeDestroyedEditor(self.get(elm.id));
        });
        targets = $_a5gegtjje5o2pum.grep(targets, function (elm) {
          return !self.get(elm.id);
        });
        if (targets.length === 0) {
          provideResults([]);
        } else {
          each$23(targets, function (elm) {
            if (isInvalidInlineTarget(settings, elm)) {
              $_axrd1j4hje5o2qor.initError('Could not initialize inline editor on invalid inline target element', elm);
            } else {
              createEditor(createId(elm), settings, elm);
            }
          });
        }
      };
      self.settings = settings;
      DOM$9.bind(window, 'ready', initEditors);
      return new promiseObj(function (resolve) {
        if (result) {
          resolve(result);
        } else {
          provideResults = function (editors) {
            resolve(editors);
          };
        }
      });
    },
    get: function (id) {
      if (arguments.length === 0) {
        return editors.slice(0);
      } else if ($_be3q8w12je5o2pxj.isString(id)) {
        return $_bwcteb4je5o2pl4.find(editors, function (editor) {
          return editor.id === id;
        }).getOr(null);
      } else if ($_be3q8w12je5o2pxj.isNumber(id)) {
        return editors[id] ? editors[id] : null;
      } else {
        return null;
      }
    },
    add: function (editor) {
      var self = this;
      var existingEditor;
      existingEditor = legacyEditors[editor.id];
      if (existingEditor === editor) {
        return editor;
      }
      if (self.get(editor.id) === null) {
        if (isValidLegacyKey(editor.id)) {
          legacyEditors[editor.id] = editor;
        }
        legacyEditors.push(editor);
        editors.push(editor);
      }
      toggleGlobalEvents(true);
      self.activeEditor = editor;
      self.fire('AddEditor', { editor: editor });
      if (!beforeUnloadDelegate) {
        beforeUnloadDelegate = function () {
          self.fire('BeforeUnload');
        };
        DOM$9.bind(window, 'beforeunload', beforeUnloadDelegate);
      }
      return editor;
    },
    createEditor: function (id, settings) {
      return this.add(new Editor(id, settings, this));
    },
    remove: function (selector) {
      var self = this;
      var i, editor;
      if (!selector) {
        for (i = editors.length - 1; i >= 0; i--) {
          self.remove(editors[i]);
        }
        return;
      }
      if ($_be3q8w12je5o2pxj.isString(selector)) {
        selector = selector.selector || selector;
        each$23(DOM$9.select(selector), function (elm) {
          editor = self.get(elm.id);
          if (editor) {
            self.remove(editor);
          }
        });
        return;
      }
      editor = selector;
      if ($_be3q8w12je5o2pxj.isNull(self.get(editor.id))) {
        return null;
      }
      if (removeEditorFromList(editor)) {
        self.fire('RemoveEditor', { editor: editor });
      }
      if (editors.length === 0) {
        DOM$9.unbind(window, 'beforeunload', beforeUnloadDelegate);
      }
      editor.remove();
      toggleGlobalEvents(editors.length > 0);
      return editor;
    },
    execCommand: function (cmd, ui, value) {
      var self = this, editor = self.get(value);
      switch (cmd) {
      case 'mceAddEditor':
        if (!self.get(value)) {
          new Editor(value, self.settings, self).render();
        }
        return true;
      case 'mceRemoveEditor':
        if (editor) {
          editor.remove();
        }
        return true;
      case 'mceToggleEditor':
        if (!editor) {
          self.execCommand('mceAddEditor', 0, value);
          return true;
        }
        if (editor.isHidden()) {
          editor.show();
        } else {
          editor.hide();
        }
        return true;
      }
      if (self.activeEditor) {
        return self.activeEditor.execCommand(cmd, ui, value);
      }
      return false;
    },
    triggerSave: function () {
      each$23(editors, function (editor) {
        editor.save();
      });
    },
    addI18n: function (code, items) {
      $_34fkt779je5o2r5d.add(code, items);
    },
    translate: function (text) {
      return $_34fkt779je5o2r5d.translate(text);
    },
    setActive: function (editor) {
      var activeEditor = this.activeEditor;
      if (this.activeEditor !== editor) {
        if (activeEditor) {
          activeEditor.fire('deactivate', { relatedTarget: editor });
        }
        editor.fire('activate', { relatedTarget: activeEditor });
      }
      this.activeEditor = editor;
    }
  };
  extend$5(EditorManager, $_8mfz0u44je5o2qmp);
  EditorManager.setup();
  var EditorManager$1 = EditorManager;

  function RangeUtils(dom) {
    var walk = function (rng, callback) {
      return $_1jk9p5lje5o2qv9.walk(dom, rng, callback);
    };
    var split = $_3ksquu3nje5o2qg7.split;
    var normalize = function (rng) {
      return $_1t8l6x3xje5o2qlk.normalize(dom, rng).fold($_enfhkv6je5o2ply.constant(false), function (normalizedRng) {
        rng.setStart(normalizedRng.startContainer, normalizedRng.startOffset);
        rng.setEnd(normalizedRng.endContainer, normalizedRng.endOffset);
        return true;
      });
    };
    return {
      walk: walk,
      split: split,
      normalize: normalize
    };
  }
  (function (RangeUtils) {
    RangeUtils.compareRanges = $_aieo7z3yje5o2qlw.isEq;
    RangeUtils.getCaretRangeFromPoint = $_drs2u868je5o2qyu.fromPoint;
    RangeUtils.getSelectedNode = getSelectedNode;
    RangeUtils.getNode = getNode;
  }(RangeUtils || (RangeUtils = {})));
  var RangeUtils$1 = RangeUtils;

  var min = Math.min;
  var max = Math.max;
  var round$1 = Math.round;
  var relativePosition = function (rect, targetRect, rel) {
    var x, y, w, h, targetW, targetH;
    x = targetRect.x;
    y = targetRect.y;
    w = rect.w;
    h = rect.h;
    targetW = targetRect.w;
    targetH = targetRect.h;
    rel = (rel || '').split('');
    if (rel[0] === 'b') {
      y += targetH;
    }
    if (rel[1] === 'r') {
      x += targetW;
    }
    if (rel[0] === 'c') {
      y += round$1(targetH / 2);
    }
    if (rel[1] === 'c') {
      x += round$1(targetW / 2);
    }
    if (rel[3] === 'b') {
      y -= h;
    }
    if (rel[4] === 'r') {
      x -= w;
    }
    if (rel[3] === 'c') {
      y -= round$1(h / 2);
    }
    if (rel[4] === 'c') {
      x -= round$1(w / 2);
    }
    return create$2(x, y, w, h);
  };
  var findBestRelativePosition = function (rect, targetRect, constrainRect, rels) {
    var pos, i;
    for (i = 0; i < rels.length; i++) {
      pos = relativePosition(rect, targetRect, rels[i]);
      if (pos.x >= constrainRect.x && pos.x + pos.w <= constrainRect.w + constrainRect.x && pos.y >= constrainRect.y && pos.y + pos.h <= constrainRect.h + constrainRect.y) {
        return rels[i];
      }
    }
    return null;
  };
  var inflate = function (rect, w, h) {
    return create$2(rect.x - w, rect.y - h, rect.w + w * 2, rect.h + h * 2);
  };
  var intersect = function (rect, cropRect) {
    var x1, y1, x2, y2;
    x1 = max(rect.x, cropRect.x);
    y1 = max(rect.y, cropRect.y);
    x2 = min(rect.x + rect.w, cropRect.x + cropRect.w);
    y2 = min(rect.y + rect.h, cropRect.y + cropRect.h);
    if (x2 - x1 < 0 || y2 - y1 < 0) {
      return null;
    }
    return create$2(x1, y1, x2 - x1, y2 - y1);
  };
  var clamp$1 = function (rect, clampRect, fixedSize) {
    var underflowX1, underflowY1, overflowX2, overflowY2, x1, y1, x2, y2, cx2, cy2;
    x1 = rect.x;
    y1 = rect.y;
    x2 = rect.x + rect.w;
    y2 = rect.y + rect.h;
    cx2 = clampRect.x + clampRect.w;
    cy2 = clampRect.y + clampRect.h;
    underflowX1 = max(0, clampRect.x - x1);
    underflowY1 = max(0, clampRect.y - y1);
    overflowX2 = max(0, x2 - cx2);
    overflowY2 = max(0, y2 - cy2);
    x1 += underflowX1;
    y1 += underflowY1;
    if (fixedSize) {
      x2 += underflowX1;
      y2 += underflowY1;
      x1 -= overflowX2;
      y1 -= overflowY2;
    }
    x2 -= overflowX2;
    y2 -= overflowY2;
    return create$2(x1, y1, x2 - x1, y2 - y1);
  };
  var create$2 = function (x, y, w, h) {
    return {
      x: x,
      y: y,
      w: w,
      h: h
    };
  };
  var fromClientRect = function (clientRect) {
    return create$2(clientRect.left, clientRect.top, clientRect.width, clientRect.height);
  };
  var $_2r1euc7bje5o2r5k = {
    inflate: inflate,
    relativePosition: relativePosition,
    findBestRelativePosition: findBestRelativePosition,
    intersect: intersect,
    clamp: clamp$1,
    create: create$2,
    fromClientRect: fromClientRect
  };

  var types = {};
  var $_cxa5dr7cje5o2r5q = {
    add: function (type, typeClass) {
      types[type.toLowerCase()] = typeClass;
    },
    has: function (type) {
      return !!types[type.toLowerCase()];
    },
    get: function (type) {
      var lctype = type.toLowerCase();
      var controlType = types.hasOwnProperty(lctype) ? types[lctype] : null;
      if (controlType === null) {
        throw new Error('Could not find module for type: ' + type);
      }
      return controlType;
    },
    create: function (type, settings) {
      var ControlType;
      if (typeof type === 'string') {
        settings = settings || {};
        settings.type = type;
      } else {
        settings = type;
        type = settings.type;
      }
      type = type.toLowerCase();
      ControlType = types[type];
      if (!ControlType) {
        throw new Error('Could not find control by type: ' + type);
      }
      ControlType = new ControlType(settings);
      ControlType.type = type;
      return ControlType;
    }
  };

  var each$24 = $_a5gegtjje5o2pum.each;
  var extend$6 = $_a5gegtjje5o2pum.extend;
  var extendClass;
  var initializing;
  var Class = function () {
  };
  Class.extend = extendClass = function (prop) {
    var self = this;
    var _super = self.prototype;
    var prototype, name, member;
    var Class = function () {
      var i, mixins, mixin;
      var self = this;
      if (!initializing) {
        if (self.init) {
          self.init.apply(self, arguments);
        }
        mixins = self.Mixins;
        if (mixins) {
          i = mixins.length;
          while (i--) {
            mixin = mixins[i];
            if (mixin.init) {
              mixin.init.apply(self, arguments);
            }
          }
        }
      }
    };
    var dummy = function () {
      return this;
    };
    var createMethod = function (name, fn) {
      return function () {
        var self = this;
        var tmp = self._super;
        var ret;
        self._super = _super[name];
        ret = fn.apply(self, arguments);
        self._super = tmp;
        return ret;
      };
    };
    initializing = true;
    prototype = new self();
    initializing = false;
    if (prop.Mixins) {
      each$24(prop.Mixins, function (mixin) {
        for (var name_1 in mixin) {
          if (name_1 !== 'init') {
            prop[name_1] = mixin[name_1];
          }
        }
      });
      if (_super.Mixins) {
        prop.Mixins = _super.Mixins.concat(prop.Mixins);
      }
    }
    if (prop.Methods) {
      each$24(prop.Methods.split(','), function (name) {
        prop[name] = dummy;
      });
    }
    if (prop.Properties) {
      each$24(prop.Properties.split(','), function (name) {
        var fieldName = '_' + name;
        prop[name] = function (value) {
          var self = this;
          if (value !== undefined) {
            self[fieldName] = value;
            return self;
          }
          return self[fieldName];
        };
      });
    }
    if (prop.Statics) {
      each$24(prop.Statics, function (func, name) {
        Class[name] = func;
      });
    }
    if (prop.Defaults && _super.Defaults) {
      prop.Defaults = extend$6({}, _super.Defaults, prop.Defaults);
    }
    for (name in prop) {
      member = prop[name];
      if (typeof member === 'function' && _super[name]) {
        prototype[name] = createMethod(name, member);
      } else {
        prototype[name] = member;
      }
    }
    Class.prototype = prototype;
    Class.constructor = Class;
    Class.extend = extendClass;
    return Class;
  };

  var min$1 = Math.min;
  var max$1 = Math.max;
  var round$2 = Math.round;
  var Color = function (value) {
    var self = {};
    var r = 0, g = 0, b = 0;
    var rgb2hsv = function (r, g, b) {
      var h, s, v, d, minRGB, maxRGB;
      h = 0;
      s = 0;
      v = 0;
      r = r / 255;
      g = g / 255;
      b = b / 255;
      minRGB = min$1(r, min$1(g, b));
      maxRGB = max$1(r, max$1(g, b));
      if (minRGB === maxRGB) {
        v = minRGB;
        return {
          h: 0,
          s: 0,
          v: v * 100
        };
      }
      d = r === minRGB ? g - b : b === minRGB ? r - g : b - r;
      h = r === minRGB ? 3 : b === minRGB ? 1 : 5;
      h = 60 * (h - d / (maxRGB - minRGB));
      s = (maxRGB - minRGB) / maxRGB;
      v = maxRGB;
      return {
        h: round$2(h),
        s: round$2(s * 100),
        v: round$2(v * 100)
      };
    };
    var hsvToRgb = function (hue, saturation, brightness) {
      var side, chroma, x, match;
      hue = (parseInt(hue, 10) || 0) % 360;
      saturation = parseInt(saturation, 10) / 100;
      brightness = parseInt(brightness, 10) / 100;
      saturation = max$1(0, min$1(saturation, 1));
      brightness = max$1(0, min$1(brightness, 1));
      if (saturation === 0) {
        r = g = b = round$2(255 * brightness);
        return;
      }
      side = hue / 60;
      chroma = brightness * saturation;
      x = chroma * (1 - Math.abs(side % 2 - 1));
      match = brightness - chroma;
      switch (Math.floor(side)) {
      case 0:
        r = chroma;
        g = x;
        b = 0;
        break;
      case 1:
        r = x;
        g = chroma;
        b = 0;
        break;
      case 2:
        r = 0;
        g = chroma;
        b = x;
        break;
      case 3:
        r = 0;
        g = x;
        b = chroma;
        break;
      case 4:
        r = x;
        g = 0;
        b = chroma;
        break;
      case 5:
        r = chroma;
        g = 0;
        b = x;
        break;
      default:
        r = g = b = 0;
      }
      r = round$2(255 * (r + match));
      g = round$2(255 * (g + match));
      b = round$2(255 * (b + match));
    };
    var toHex = function () {
      var hex = function (val) {
        val = parseInt(val, 10).toString(16);
        return val.length > 1 ? val : '0' + val;
      };
      return '#' + hex(r) + hex(g) + hex(b);
    };
    var toRgb = function () {
      return {
        r: r,
        g: g,
        b: b
      };
    };
    var toHsv = function () {
      return rgb2hsv(r, g, b);
    };
    var parse = function (value) {
      var matches;
      if (typeof value === 'object') {
        if ('r' in value) {
          r = value.r;
          g = value.g;
          b = value.b;
        } else if ('v' in value) {
          hsvToRgb(value.h, value.s, value.v);
        }
      } else {
        if (matches = /rgb\s*\(\s*([0-9]+)\s*,\s*([0-9]+)\s*,\s*([0-9]+)[^\)]*\)/gi.exec(value)) {
          r = parseInt(matches[1], 10);
          g = parseInt(matches[2], 10);
          b = parseInt(matches[3], 10);
        } else if (matches = /#([0-F]{2})([0-F]{2})([0-F]{2})/gi.exec(value)) {
          r = parseInt(matches[1], 16);
          g = parseInt(matches[2], 16);
          b = parseInt(matches[3], 16);
        } else if (matches = /#([0-F])([0-F])([0-F])/gi.exec(value)) {
          r = parseInt(matches[1] + matches[1], 16);
          g = parseInt(matches[2] + matches[2], 16);
          b = parseInt(matches[3] + matches[3], 16);
        }
      }
      r = r < 0 ? 0 : r > 255 ? 255 : r;
      g = g < 0 ? 0 : g > 255 ? 255 : g;
      b = b < 0 ? 0 : b > 255 ? 255 : b;
      return self;
    };
    if (value) {
      parse(value);
    }
    self.toRgb = toRgb;
    self.toHsv = toHsv;
    self.toHex = toHex;
    self.parse = parse;
    return self;
  };

  var serialize = function (o, quote) {
    var i, v, t, name;
    quote = quote || '"';
    if (o === null) {
      return 'null';
    }
    t = typeof o;
    if (t === 'string') {
      v = '\bb\tt\nn\ff\rr""\'\'\\\\';
      return quote + o.replace(/([\u0080-\uFFFF\x00-\x1f\"\'\\])/g, function (a, b) {
        if (quote === '"' && a === '\'') {
          return a;
        }
        i = v.indexOf(b);
        if (i + 1) {
          return '\\' + v.charAt(i + 1);
        }
        a = b.charCodeAt().toString(16);
        return '\\u' + '0000'.substring(a.length) + a;
      }) + quote;
    }
    if (t === 'object') {
      if (o.hasOwnProperty && Object.prototype.toString.call(o) === '[object Array]') {
        for (i = 0, v = '['; i < o.length; i++) {
          v += (i > 0 ? ',' : '') + serialize(o[i], quote);
        }
        return v + ']';
      }
      v = '{';
      for (name in o) {
        if (o.hasOwnProperty(name)) {
          v += typeof o[name] !== 'function' ? (v.length > 1 ? ',' + quote : quote) + name + quote + ':' + serialize(o[name], quote) : '';
        }
      }
      return v + '}';
    }
    return '' + o;
  };
  var $_6tjbfu7fje5o2r61 = {
    serialize: serialize,
    parse: function (text) {
      try {
        return JSON.parse(text);
      } catch (ex) {
      }
    }
  };

  var $_3nz9gz7gje5o2r63 = {
    callbacks: {},
    count: 0,
    send: function (settings) {
      var self = this, dom = DOMUtils.DOM, count = settings.count !== undefined ? settings.count : self.count;
      var id = 'tinymce_jsonp_' + count;
      self.callbacks[count] = function (json) {
        dom.remove(id);
        delete self.callbacks[count];
        settings.callback(json);
      };
      dom.add(dom.doc.body, 'script', {
        id: id,
        src: settings.url,
        type: 'text/javascript'
      });
      self.count++;
    }
  };

  var XHR = {
    send: function (settings) {
      var xhr, count = 0;
      var ready = function () {
        if (!settings.async || xhr.readyState === 4 || count++ > 10000) {
          if (settings.success && count < 10000 && xhr.status === 200) {
            settings.success.call(settings.success_scope, '' + xhr.responseText, xhr, settings);
          } else if (settings.error) {
            settings.error.call(settings.error_scope, count > 10000 ? 'TIMED_OUT' : 'GENERAL', xhr, settings);
          }
          xhr = null;
        } else {
          setTimeout(ready, 10);
        }
      };
      settings.scope = settings.scope || this;
      settings.success_scope = settings.success_scope || settings.scope;
      settings.error_scope = settings.error_scope || settings.scope;
      settings.async = settings.async === false ? false : true;
      settings.data = settings.data || '';
      XHR.fire('beforeInitialize', { settings: settings });
      xhr = new XMLHttpRequest();
      if (xhr) {
        if (xhr.overrideMimeType) {
          xhr.overrideMimeType(settings.content_type);
        }
        xhr.open(settings.type || (settings.data ? 'POST' : 'GET'), settings.url, settings.async);
        if (settings.crossDomain) {
          xhr.withCredentials = true;
        }
        if (settings.content_type) {
          xhr.setRequestHeader('Content-Type', settings.content_type);
        }
        if (settings.requestheaders) {
          $_a5gegtjje5o2pum.each(settings.requestheaders, function (header) {
            xhr.setRequestHeader(header.key, header.value);
          });
        }
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr = XHR.fire('beforeSend', {
          xhr: xhr,
          settings: settings
        }).xhr;
        xhr.send(settings.data);
        if (!settings.async) {
          return ready();
        }
        setTimeout(ready, 10);
      }
    }
  };
  $_a5gegtjje5o2pum.extend(XHR, $_8mfz0u44je5o2qmp);

  var extend$7 = $_a5gegtjje5o2pum.extend;
  var JSONRequest = function (settings) {
    this.settings = extend$7({}, settings);
    this.count = 0;
  };
  JSONRequest.sendRPC = function (o) {
    return new JSONRequest().send(o);
  };
  JSONRequest.prototype = {
    send: function (args) {
      var ecb = args.error, scb = args.success;
      args = extend$7(this.settings, args);
      args.success = function (c, x) {
        c = $_6tjbfu7fje5o2r61.parse(c);
        if (typeof c === 'undefined') {
          c = { error: 'JSON Parse error.' };
        }
        if (c.error) {
          ecb.call(args.error_scope || args.scope, c.error, x);
        } else {
          scb.call(args.success_scope || args.scope, c.result);
        }
      };
      args.error = function (ty, x) {
        if (ecb) {
          ecb.call(args.error_scope || args.scope, ty, x);
        }
      };
      args.data = $_6tjbfu7fje5o2r61.serialize({
        id: args.id || 'c' + this.count++,
        method: args.method,
        params: args.params
      });
      args.content_type = 'application/json';
      XHR.send(args);
    }
  };

  var localStorage = window.localStorage;

  var tinymce = EditorManager$1;
  var publicApi = {
    geom: { Rect: $_2r1euc7bje5o2r5k },
    util: {
      Promise: promiseObj,
      Delay: $_db41wogje5o2ps3,
      Tools: $_a5gegtjje5o2pum,
      VK: $_99mhlk59je5o2qss,
      URI: URI,
      Class: Class,
      EventDispatcher: Dispatcher,
      Observable: $_8mfz0u44je5o2qmp,
      I18n: $_34fkt779je5o2r5d,
      XHR: XHR,
      JSON: $_6tjbfu7fje5o2r61,
      JSONRequest: JSONRequest,
      JSONP: $_3nz9gz7gje5o2r63,
      LocalStorage: localStorage,
      Color: Color
    },
    dom: {
      EventUtils: EventUtils,
      Sizzle: Sizzle,
      DomQuery: DomQuery,
      TreeWalker: TreeWalker,
      DOMUtils: DOMUtils,
      ScriptLoader: ScriptLoader,
      RangeUtils: RangeUtils$1,
      Serializer: DomSerializer$1,
      ControlSelection: ControlSelection,
      BookmarkManager: BookmarkManager$1,
      Selection: Selection$1,
      Event: EventUtils.Event
    },
    html: {
      Styles: Styles,
      Entities: $_fzh4ld1rje5o2q0t,
      Node: Node$2,
      Schema: Schema,
      SaxParser: SaxParser$1,
      DomParser: DomParser,
      Writer: Writer,
      Serializer: HtmlSerializer
    },
    ui: { Factory: $_cxa5dr7cje5o2r5q },
    Env: $_9bd8pn9je5o2pp2,
    AddOnManager: AddOnManager,
    Formatter: Formatter,
    UndoManager: UndoManager,
    EditorCommands: EditorCommands,
    WindowManager: WindowManager,
    NotificationManager: NotificationManager,
    EditorObservable: EditorObservable$1,
    Shortcuts: Shortcuts,
    Editor: Editor,
    FocusManager: $_97d4iv77je5o2r4x,
    EditorManager: EditorManager$1,
    DOM: DOMUtils.DOM,
    ScriptLoader: ScriptLoader.ScriptLoader,
    PluginManager: AddOnManager.PluginManager,
    ThemeManager: AddOnManager.ThemeManager,
    trim: $_a5gegtjje5o2pum.trim,
    isArray: $_a5gegtjje5o2pum.isArray,
    is: $_a5gegtjje5o2pum.is,
    toArray: $_a5gegtjje5o2pum.toArray,
    makeMap: $_a5gegtjje5o2pum.makeMap,
    each: $_a5gegtjje5o2pum.each,
    map: $_a5gegtjje5o2pum.map,
    grep: $_a5gegtjje5o2pum.grep,
    inArray: $_a5gegtjje5o2pum.inArray,
    extend: $_a5gegtjje5o2pum.extend,
    create: $_a5gegtjje5o2pum.create,
    walk: $_a5gegtjje5o2pum.walk,
    createNS: $_a5gegtjje5o2pum.createNS,
    resolve: $_a5gegtjje5o2pum.resolve,
    explode: $_a5gegtjje5o2pum.explode,
    _addCacheSuffix: $_a5gegtjje5o2pum._addCacheSuffix,
    isOpera: $_9bd8pn9je5o2pp2.opera,
    isWebKit: $_9bd8pn9je5o2pp2.webkit,
    isIE: $_9bd8pn9je5o2pp2.ie,
    isGecko: $_9bd8pn9je5o2pp2.gecko,
    isMac: $_9bd8pn9je5o2pp2.mac
  };
  tinymce = $_a5gegtjje5o2pum.extend(tinymce, publicApi);
  var Tinymce = tinymce;

  var exportToModuleLoaders = function (tinymce) {
    if (typeof module === 'object') {
      try {
        module.exports = tinymce;
      } catch (_) {
      }
    }
  };
  var exportToWindowGlobal = function (tinymce) {
    window.tinymce = tinymce;
    window.tinyMCE = tinymce;
  };
  exportToWindowGlobal(Tinymce);
  exportToModuleLoaders(Tinymce);

}());
})();
