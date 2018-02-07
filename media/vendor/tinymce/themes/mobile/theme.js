(function () {
var mobile = (function () {
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
  var $_8z5eqrwbjd09f01q = {
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

  var never$1 = $_8z5eqrwbjd09f01q.never;
  var always$1 = $_8z5eqrwbjd09f01q.always;
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
      toString: $_8z5eqrwbjd09f01q.constant('none()')
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
  var $_8nwhzlwajd09f01m = {
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
    return r === -1 ? $_8nwhzlwajd09f01m.none() : $_8nwhzlwajd09f01m.some(r);
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
        return $_8nwhzlwajd09f01m.some(x);
      }
    }
    return $_8nwhzlwajd09f01m.none();
  };
  var findIndex = function (xs, pred) {
    for (var i = 0, len = xs.length; i < len; i++) {
      var x = xs[i];
      if (pred(x, i, xs)) {
        return $_8nwhzlwajd09f01m.some(i);
      }
    }
    return $_8nwhzlwajd09f01m.none();
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
    return xs.length === 0 ? $_8nwhzlwajd09f01m.none() : $_8nwhzlwajd09f01m.some(xs[0]);
  };
  var last = function (xs) {
    return xs.length === 0 ? $_8nwhzlwajd09f01m.none() : $_8nwhzlwajd09f01m.some(xs[xs.length - 1]);
  };
  var $_bvikd2w9jd09f01c = {
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
  var $_451is0wejd09f01w = {
    path: path,
    resolve: resolve,
    forge: forge,
    namespace: namespace
  };

  var unsafe = function (name, scope) {
    return $_451is0wejd09f01w.resolve(name, scope);
  };
  var getOrDie = function (name, scope) {
    var actual = unsafe(name, scope);
    if (actual === undefined || actual === null)
      throw name + ' not available on this browser';
    return actual;
  };
  var $_2l4l3gwdjd09f01u = { getOrDie: getOrDie };

  var node = function () {
    var f = $_2l4l3gwdjd09f01u.getOrDie('Node');
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
  var $_pesk1wcjd09f01s = {
    documentPositionPreceding: documentPositionPreceding,
    documentPositionContainedBy: documentPositionContainedBy
  };

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
  var $_9ur00cwhjd09f021 = { cached: cached };

  var firstMatch = function (regexes, s) {
    for (var i = 0; i < regexes.length; i++) {
      var x = regexes[i];
      if (x.test(s))
        return x;
    }
    return undefined;
  };
  var find$1 = function (regexes, agent) {
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
    return find$1(versionRegexes, cleanedAgent);
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
  var $_e6ovpewkjd09f02e = {
    nu: nu,
    detect: detect,
    unknown: unknown
  };

  var edge = 'Edge';
  var chrome = 'Chrome';
  var ie = 'IE';
  var opera = 'Opera';
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
      version: $_e6ovpewkjd09f02e.unknown()
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
      isIE: isBrowser(ie, current),
      isOpera: isBrowser(opera, current),
      isFirefox: isBrowser(firefox, current),
      isSafari: isBrowser(safari, current)
    };
  };
  var $_afvnm7wjjd09f023 = {
    unknown: unknown$1,
    nu: nu$1,
    edge: $_8z5eqrwbjd09f01q.constant(edge),
    chrome: $_8z5eqrwbjd09f01q.constant(chrome),
    ie: $_8z5eqrwbjd09f01q.constant(ie),
    opera: $_8z5eqrwbjd09f01q.constant(opera),
    firefox: $_8z5eqrwbjd09f01q.constant(firefox),
    safari: $_8z5eqrwbjd09f01q.constant(safari)
  };

  var windows = 'Windows';
  var ios = 'iOS';
  var android = 'Android';
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
      version: $_e6ovpewkjd09f02e.unknown()
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
      isAndroid: isOS(android, current),
      isOSX: isOS(osx, current),
      isLinux: isOS(linux, current),
      isSolaris: isOS(solaris, current),
      isFreeBSD: isOS(freebsd, current)
    };
  };
  var $_96wa6iwljd09f02g = {
    unknown: unknown$2,
    nu: nu$2,
    windows: $_8z5eqrwbjd09f01q.constant(windows),
    ios: $_8z5eqrwbjd09f01q.constant(ios),
    android: $_8z5eqrwbjd09f01q.constant(android),
    linux: $_8z5eqrwbjd09f01q.constant(linux),
    osx: $_8z5eqrwbjd09f01q.constant(osx),
    solaris: $_8z5eqrwbjd09f01q.constant(solaris),
    freebsd: $_8z5eqrwbjd09f01q.constant(freebsd)
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
      isiPad: $_8z5eqrwbjd09f01q.constant(isiPad),
      isiPhone: $_8z5eqrwbjd09f01q.constant(isiPhone),
      isTablet: $_8z5eqrwbjd09f01q.constant(isTablet),
      isPhone: $_8z5eqrwbjd09f01q.constant(isPhone),
      isTouch: $_8z5eqrwbjd09f01q.constant(isTouch),
      isAndroid: os.isAndroid,
      isiOS: os.isiOS,
      isWebView: $_8z5eqrwbjd09f01q.constant(iOSwebview)
    };
  }

  var detect$1 = function (candidates, userAgent) {
    var agent = String(userAgent).toLowerCase();
    return $_bvikd2w9jd09f01c.find(candidates, function (candidate) {
      return candidate.search(agent);
    });
  };
  var detectBrowser = function (browsers, userAgent) {
    return detect$1(browsers, userAgent).map(function (browser) {
      var version = $_e6ovpewkjd09f02e.detect(browser.versionRegexes, userAgent);
      return {
        current: browser.name,
        version: version
      };
    });
  };
  var detectOs = function (oses, userAgent) {
    return detect$1(oses, userAgent).map(function (os) {
      var version = $_e6ovpewkjd09f02e.detect(os.versionRegexes, userAgent);
      return {
        current: os.name,
        version: version
      };
    });
  };
  var $_cbr2szwnjd09f02l = {
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
  var $_7ot4z5wqjd09f02w = {
    addToStart: addToStart,
    addToEnd: addToEnd,
    removeFromStart: removeFromStart,
    removeFromEnd: removeFromEnd
  };

  var first = function (str, count) {
    return str.substr(0, count);
  };
  var last$1 = function (str, count) {
    return str.substr(str.length - count, str.length);
  };
  var head$1 = function (str) {
    return str === '' ? $_8nwhzlwajd09f01m.none() : $_8nwhzlwajd09f01m.some(str.substr(0, 1));
  };
  var tail = function (str) {
    return str === '' ? $_8nwhzlwajd09f01m.none() : $_8nwhzlwajd09f01m.some(str.substring(1));
  };
  var $_ddmboywrjd09f02x = {
    first: first,
    last: last$1,
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
    return startsWith(str, prefix) ? $_7ot4z5wqjd09f02w.removeFromStart(str, prefix.length) : str;
  };
  var removeTrailing = function (str, prefix) {
    return endsWith(str, prefix) ? $_7ot4z5wqjd09f02w.removeFromEnd(str, prefix.length) : str;
  };
  var ensureLeading = function (str, prefix) {
    return startsWith(str, prefix) ? str : $_7ot4z5wqjd09f02w.addToStart(str, prefix);
  };
  var ensureTrailing = function (str, prefix) {
    return endsWith(str, prefix) ? str : $_7ot4z5wqjd09f02w.addToEnd(str, prefix);
  };
  var contains$1 = function (str, substr) {
    return str.indexOf(substr) !== -1;
  };
  var capitalize = function (str) {
    return $_ddmboywrjd09f02x.head(str).bind(function (head) {
      return $_ddmboywrjd09f02x.tail(str).map(function (tail) {
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
  var trim = function (str) {
    return str.replace(/^\s+|\s+$/g, '');
  };
  var lTrim = function (str) {
    return str.replace(/^\s+/g, '');
  };
  var rTrim = function (str) {
    return str.replace(/\s+$/g, '');
  };
  var $_7bomvkwpjd09f02u = {
    supplant: supplant,
    startsWith: startsWith,
    removeLeading: removeLeading,
    removeTrailing: removeTrailing,
    ensureLeading: ensureLeading,
    ensureTrailing: ensureTrailing,
    endsWith: endsWith,
    contains: contains$1,
    trim: trim,
    lTrim: lTrim,
    rTrim: rTrim,
    capitalize: capitalize
  };

  var normalVersionRegex = /.*?version\/\ ?([0-9]+)\.([0-9]+).*/;
  var checkContains = function (target) {
    return function (uastring) {
      return $_7bomvkwpjd09f02u.contains(uastring, target);
    };
  };
  var browsers = [
    {
      name: 'Edge',
      versionRegexes: [/.*?edge\/ ?([0-9]+)\.([0-9]+)$/],
      search: function (uastring) {
        var monstrosity = $_7bomvkwpjd09f02u.contains(uastring, 'edge/') && $_7bomvkwpjd09f02u.contains(uastring, 'chrome') && $_7bomvkwpjd09f02u.contains(uastring, 'safari') && $_7bomvkwpjd09f02u.contains(uastring, 'applewebkit');
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
        return $_7bomvkwpjd09f02u.contains(uastring, 'chrome') && !$_7bomvkwpjd09f02u.contains(uastring, 'chromeframe');
      }
    },
    {
      name: 'IE',
      versionRegexes: [
        /.*?msie\ ?([0-9]+)\.([0-9]+).*/,
        /.*?rv:([0-9]+)\.([0-9]+).*/
      ],
      search: function (uastring) {
        return $_7bomvkwpjd09f02u.contains(uastring, 'msie') || $_7bomvkwpjd09f02u.contains(uastring, 'trident');
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
        return ($_7bomvkwpjd09f02u.contains(uastring, 'safari') || $_7bomvkwpjd09f02u.contains(uastring, 'mobile/')) && $_7bomvkwpjd09f02u.contains(uastring, 'applewebkit');
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
        return $_7bomvkwpjd09f02u.contains(uastring, 'iphone') || $_7bomvkwpjd09f02u.contains(uastring, 'ipad');
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
  var $_ckfw6uwojd09f02n = {
    browsers: $_8z5eqrwbjd09f01q.constant(browsers),
    oses: $_8z5eqrwbjd09f01q.constant(oses)
  };

  var detect$2 = function (userAgent) {
    var browsers = $_ckfw6uwojd09f02n.browsers();
    var oses = $_ckfw6uwojd09f02n.oses();
    var browser = $_cbr2szwnjd09f02l.detectBrowser(browsers, userAgent).fold($_afvnm7wjjd09f023.unknown, $_afvnm7wjjd09f023.nu);
    var os = $_cbr2szwnjd09f02l.detectOs(oses, userAgent).fold($_96wa6iwljd09f02g.unknown, $_96wa6iwljd09f02g.nu);
    var deviceType = DeviceType(os, browser, userAgent);
    return {
      browser: browser,
      os: os,
      deviceType: deviceType
    };
  };
  var $_6fm5z1wijd09f022 = { detect: detect$2 };

  var detect$3 = $_9ur00cwhjd09f021.cached(function () {
    var userAgent = navigator.userAgent;
    return $_6fm5z1wijd09f022.detect(userAgent);
  });
  var $_8zynflwgjd09f01z = { detect: detect$3 };

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
    return { dom: $_8z5eqrwbjd09f01q.constant(node) };
  };
  var fromPoint = function (doc, x, y) {
    return $_8nwhzlwajd09f01m.from(doc.dom().elementFromPoint(x, y)).map(fromDom);
  };
  var $_cnbf2uwtjd09f033 = {
    fromHtml: fromHtml,
    fromTag: fromTag,
    fromText: fromText,
    fromDom: fromDom,
    fromPoint: fromPoint
  };

  var $_7e0rsqwujd09f036 = {
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

  var ELEMENT = $_7e0rsqwujd09f036.ELEMENT;
  var DOCUMENT = $_7e0rsqwujd09f036.DOCUMENT;
  var is = function (element, selector) {
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
    return bypassSelector(base) ? [] : $_bvikd2w9jd09f01c.map(base.querySelectorAll(selector), $_cnbf2uwtjd09f033.fromDom);
  };
  var one = function (selector, scope) {
    var base = scope === undefined ? document : scope.dom();
    return bypassSelector(base) ? $_8nwhzlwajd09f01m.none() : $_8nwhzlwajd09f01m.from(base.querySelector(selector)).map($_cnbf2uwtjd09f033.fromDom);
  };
  var $_5mto6swsjd09f02z = {
    all: all,
    is: is,
    one: one
  };

  var eq = function (e1, e2) {
    return e1.dom() === e2.dom();
  };
  var isEqualNode = function (e1, e2) {
    return e1.dom().isEqualNode(e2.dom());
  };
  var member = function (element, elements) {
    return $_bvikd2w9jd09f01c.exists(elements, $_8z5eqrwbjd09f01q.curry(eq, element));
  };
  var regularContains = function (e1, e2) {
    var d1 = e1.dom(), d2 = e2.dom();
    return d1 === d2 ? false : d1.contains(d2);
  };
  var ieContains = function (e1, e2) {
    return $_pesk1wcjd09f01s.documentPositionContainedBy(e1.dom(), e2.dom());
  };
  var browser = $_8zynflwgjd09f01z.detect().browser;
  var contains$2 = browser.isIE() ? ieContains : regularContains;
  var $_6s6cs1w8jd09f014 = {
    eq: eq,
    isEqualNode: isEqualNode,
    member: member,
    contains: contains$2,
    is: $_5mto6swsjd09f02z.is
  };

  var isSource = function (component, simulatedEvent) {
    return $_6s6cs1w8jd09f014.eq(component.element(), simulatedEvent.event().target());
  };
  var $_es2jyyw7jd09f012 = { isSource: isSource };

  var $_8nqyjjwxjd09f03f = {
    contextmenu: $_8z5eqrwbjd09f01q.constant('contextmenu'),
    touchstart: $_8z5eqrwbjd09f01q.constant('touchstart'),
    touchmove: $_8z5eqrwbjd09f01q.constant('touchmove'),
    touchend: $_8z5eqrwbjd09f01q.constant('touchend'),
    gesturestart: $_8z5eqrwbjd09f01q.constant('gesturestart'),
    mousedown: $_8z5eqrwbjd09f01q.constant('mousedown'),
    mousemove: $_8z5eqrwbjd09f01q.constant('mousemove'),
    mouseout: $_8z5eqrwbjd09f01q.constant('mouseout'),
    mouseup: $_8z5eqrwbjd09f01q.constant('mouseup'),
    mouseover: $_8z5eqrwbjd09f01q.constant('mouseover'),
    focusin: $_8z5eqrwbjd09f01q.constant('focusin'),
    keydown: $_8z5eqrwbjd09f01q.constant('keydown'),
    input: $_8z5eqrwbjd09f01q.constant('input'),
    change: $_8z5eqrwbjd09f01q.constant('change'),
    focus: $_8z5eqrwbjd09f01q.constant('focus'),
    click: $_8z5eqrwbjd09f01q.constant('click'),
    transitionend: $_8z5eqrwbjd09f01q.constant('transitionend'),
    selectstart: $_8z5eqrwbjd09f01q.constant('selectstart')
  };

  var alloy = { tap: $_8z5eqrwbjd09f01q.constant('alloy.tap') };
  var $_5iytewwjd09f03c = {
    focus: $_8z5eqrwbjd09f01q.constant('alloy.focus'),
    postBlur: $_8z5eqrwbjd09f01q.constant('alloy.blur.post'),
    receive: $_8z5eqrwbjd09f01q.constant('alloy.receive'),
    execute: $_8z5eqrwbjd09f01q.constant('alloy.execute'),
    focusItem: $_8z5eqrwbjd09f01q.constant('alloy.focus.item'),
    tap: alloy.tap,
    tapOrClick: $_8zynflwgjd09f01z.detect().deviceType.isTouch() ? alloy.tap : $_8nqyjjwxjd09f03f.click,
    longpress: $_8z5eqrwbjd09f01q.constant('alloy.longpress'),
    sandboxClose: $_8z5eqrwbjd09f01q.constant('alloy.sandbox.close'),
    systemInit: $_8z5eqrwbjd09f01q.constant('alloy.system.init'),
    windowScroll: $_8z5eqrwbjd09f01q.constant('alloy.system.scroll'),
    attachedToDom: $_8z5eqrwbjd09f01q.constant('alloy.system.attached'),
    detachedFromDom: $_8z5eqrwbjd09f01q.constant('alloy.system.detached'),
    changeTab: $_8z5eqrwbjd09f01q.constant('alloy.change.tab'),
    dismissTab: $_8z5eqrwbjd09f01q.constant('alloy.dismiss.tab')
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
  var isType = function (type) {
    return function (value) {
      return typeOf(value) === type;
    };
  };
  var $_4biushwzjd09f03k = {
    isString: isType('string'),
    isObject: isType('object'),
    isArray: isType('array'),
    isNull: isType('null'),
    isBoolean: isType('boolean'),
    isUndefined: isType('undefined'),
    isFunction: isType('function'),
    isNumber: isType('number')
  };

  var shallow = function (old, nu) {
    return nu;
  };
  var deep = function (old, nu) {
    var bothObjects = $_4biushwzjd09f03k.isObject(old) && $_4biushwzjd09f03k.isObject(nu);
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
  var $_3htayhwyjd09f03i = {
    deepMerge: deepMerge,
    merge: merge
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
  var each$1 = function (obj, f) {
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
    each$1(obj, function (x, i) {
      var tuple = f(x, i, obj);
      r[tuple.k] = tuple.v;
    });
    return r;
  };
  var bifilter = function (obj, pred) {
    var t = {};
    var f = {};
    each$1(obj, function (x, i) {
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
    each$1(obj, function (value, name) {
      r.push(f(value, name));
    });
    return r;
  };
  var find$2 = function (obj, pred) {
    var props = keys(obj);
    for (var k = 0, len = props.length; k < len; k++) {
      var i = props[k];
      var x = obj[i];
      if (pred(x, i, obj)) {
        return $_8nwhzlwajd09f01m.some(x);
      }
    }
    return $_8nwhzlwajd09f01m.none();
  };
  var values = function (obj) {
    return mapToArray(obj, function (v) {
      return v;
    });
  };
  var size = function (obj) {
    return values(obj).length;
  };
  var $_32a0zdx0jd09f03l = {
    bifilter: bifilter,
    each: each$1,
    map: objectMap,
    mapToArray: mapToArray,
    tupleMap: tupleMap,
    find: find$2,
    keys: keys,
    values: values,
    size: size
  };

  var emit = function (component, event) {
    dispatchWith(component, component.element(), event, {});
  };
  var emitWith = function (component, event, properties) {
    dispatchWith(component, component.element(), event, properties);
  };
  var emitExecute = function (component) {
    emit(component, $_5iytewwjd09f03c.execute());
  };
  var dispatch = function (component, target, event) {
    dispatchWith(component, target, event, {});
  };
  var dispatchWith = function (component, target, event, properties) {
    var data = $_3htayhwyjd09f03i.deepMerge({ target: target }, properties);
    component.getSystem().triggerEvent(event, target, $_32a0zdx0jd09f03l.map(data, $_8z5eqrwbjd09f01q.constant));
  };
  var dispatchEvent = function (component, target, event, simulatedEvent) {
    component.getSystem().triggerEvent(event, target, simulatedEvent.event());
  };
  var dispatchFocus = function (component, target) {
    component.getSystem().triggerFocus(target, component.element());
  };
  var $_3b6lb8wvjd09f037 = {
    emit: emit,
    emitWith: emitWith,
    emitExecute: emitExecute,
    dispatch: dispatch,
    dispatchWith: dispatchWith,
    dispatchEvent: dispatchEvent,
    dispatchFocus: dispatchFocus
  };

  var generate = function (cases) {
    if (!$_4biushwzjd09f03k.isArray(cases)) {
      throw new Error('cases must be an array');
    }
    if (cases.length === 0) {
      throw new Error('there must be at least one case');
    }
    var constructors = [];
    var adt = {};
    $_bvikd2w9jd09f01c.each(cases, function (acase, count) {
      var keys = $_32a0zdx0jd09f03l.keys(acase);
      if (keys.length !== 1) {
        throw new Error('one and only one name per case');
      }
      var key = keys[0];
      var value = acase[key];
      if (adt[key] !== undefined) {
        throw new Error('duplicate key detected:' + key);
      } else if (key === 'cata') {
        throw new Error('cannot have a case named cata (sorry)');
      } else if (!$_4biushwzjd09f03k.isArray(value)) {
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
          var branchKeys = $_32a0zdx0jd09f03l.keys(branches);
          if (constructors.length !== branchKeys.length) {
            throw new Error('Wrong number of arguments to match. Expected: ' + constructors.join(',') + '\nActual: ' + branchKeys.join(','));
          }
          var allReqd = $_bvikd2w9jd09f01c.forall(constructors, function (reqKey) {
            return $_bvikd2w9jd09f01c.contains(branchKeys, reqKey);
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
  var $_enqz8zx4jd09f04j = { generate: generate };

  var adt = $_enqz8zx4jd09f04j.generate([
    { strict: [] },
    { defaultedThunk: ['fallbackThunk'] },
    { asOption: [] },
    { asDefaultedOptionThunk: ['fallbackThunk'] },
    { mergeWithThunk: ['baseThunk'] }
  ]);
  var defaulted = function (fallback) {
    return adt.defaultedThunk($_8z5eqrwbjd09f01q.constant(fallback));
  };
  var asDefaultedOption = function (fallback) {
    return adt.asDefaultedOptionThunk($_8z5eqrwbjd09f01q.constant(fallback));
  };
  var mergeWith = function (base) {
    return adt.mergeWithThunk($_8z5eqrwbjd09f01q.constant(base));
  };
  var $_buoc30x3jd09f047 = {
    strict: adt.strict,
    asOption: adt.asOption,
    defaulted: defaulted,
    defaultedThunk: adt.defaultedThunk,
    asDefaultedOption: asDefaultedOption,
    asDefaultedOptionThunk: adt.asDefaultedOptionThunk,
    mergeWith: mergeWith,
    mergeWithThunk: adt.mergeWithThunk
  };

  var value = function (o) {
    var is = function (v) {
      return o === v;
    };
    var or = function (opt) {
      return value(o);
    };
    var orThunk = function (f) {
      return value(o);
    };
    var map = function (f) {
      return value(f(o));
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
      return $_8nwhzlwajd09f01m.some(o);
    };
    return {
      is: is,
      isValue: $_8z5eqrwbjd09f01q.constant(true),
      isError: $_8z5eqrwbjd09f01q.constant(false),
      getOr: $_8z5eqrwbjd09f01q.constant(o),
      getOrThunk: $_8z5eqrwbjd09f01q.constant(o),
      getOrDie: $_8z5eqrwbjd09f01q.constant(o),
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
      return $_8z5eqrwbjd09f01q.die(message)();
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
      is: $_8z5eqrwbjd09f01q.constant(false),
      isValue: $_8z5eqrwbjd09f01q.constant(false),
      isError: $_8z5eqrwbjd09f01q.constant(true),
      getOr: $_8z5eqrwbjd09f01q.identity,
      getOrThunk: getOrThunk,
      getOrDie: getOrDie,
      or: or,
      orThunk: orThunk,
      fold: fold,
      map: map,
      each: $_8z5eqrwbjd09f01q.noop,
      bind: bind,
      exists: $_8z5eqrwbjd09f01q.constant(false),
      forall: $_8z5eqrwbjd09f01q.constant(true),
      toOption: $_8nwhzlwajd09f01m.none
    };
  };
  var $_anstx7x8jd09f05b = {
    value: value,
    error: error
  };

  var comparison = $_enqz8zx4jd09f04j.generate([
    {
      bothErrors: [
        'error1',
        'error2'
      ]
    },
    {
      firstError: [
        'error1',
        'value2'
      ]
    },
    {
      secondError: [
        'value1',
        'error2'
      ]
    },
    {
      bothValues: [
        'value1',
        'value2'
      ]
    }
  ]);
  var partition$1 = function (results) {
    var errors = [];
    var values = [];
    $_bvikd2w9jd09f01c.each(results, function (result) {
      result.fold(function (err) {
        errors.push(err);
      }, function (value) {
        values.push(value);
      });
    });
    return {
      errors: errors,
      values: values
    };
  };
  var compare = function (result1, result2) {
    return result1.fold(function (err1) {
      return result2.fold(function (err2) {
        return comparison.bothErrors(err1, err2);
      }, function (val2) {
        return comparison.firstError(err1, val2);
      });
    }, function (val1) {
      return result2.fold(function (err2) {
        return comparison.secondError(val1, err2);
      }, function (val2) {
        return comparison.bothValues(val1, val2);
      });
    });
  };
  var $_5hr8u4x9jd09f05e = {
    partition: partition$1,
    compare: compare
  };

  var mergeValues = function (values, base) {
    return $_anstx7x8jd09f05b.value($_3htayhwyjd09f03i.deepMerge.apply(undefined, [base].concat(values)));
  };
  var mergeErrors = function (errors) {
    return $_8z5eqrwbjd09f01q.compose($_anstx7x8jd09f05b.error, $_bvikd2w9jd09f01c.flatten)(errors);
  };
  var consolidateObj = function (objects, base) {
    var partitions = $_5hr8u4x9jd09f05e.partition(objects);
    return partitions.errors.length > 0 ? mergeErrors(partitions.errors) : mergeValues(partitions.values, base);
  };
  var consolidateArr = function (objects) {
    var partitions = $_5hr8u4x9jd09f05e.partition(objects);
    return partitions.errors.length > 0 ? mergeErrors(partitions.errors) : $_anstx7x8jd09f05b.value(partitions.values);
  };
  var $_43ns5nx7jd09f054 = {
    consolidateObj: consolidateObj,
    consolidateArr: consolidateArr
  };

  var narrow = function (obj, fields) {
    var r = {};
    $_bvikd2w9jd09f01c.each(fields, function (field) {
      if (obj[field] !== undefined && obj.hasOwnProperty(field))
        r[field] = obj[field];
    });
    return r;
  };
  var indexOnKey = function (array, key) {
    var obj = {};
    $_bvikd2w9jd09f01c.each(array, function (a) {
      var keyValue = a[key];
      obj[keyValue] = a;
    });
    return obj;
  };
  var exclude = function (obj, fields) {
    var r = {};
    $_32a0zdx0jd09f03l.each(obj, function (v, k) {
      if (!$_bvikd2w9jd09f01c.contains(fields, k)) {
        r[k] = v;
      }
    });
    return r;
  };
  var $_6vlplwxajd09f05g = {
    narrow: narrow,
    exclude: exclude,
    indexOnKey: indexOnKey
  };

  var readOpt = function (key) {
    return function (obj) {
      return obj.hasOwnProperty(key) ? $_8nwhzlwajd09f01m.from(obj[key]) : $_8nwhzlwajd09f01m.none();
    };
  };
  var readOr = function (key, fallback) {
    return function (obj) {
      return readOpt(key)(obj).getOr(fallback);
    };
  };
  var readOptFrom = function (obj, key) {
    return readOpt(key)(obj);
  };
  var hasKey = function (obj, key) {
    return obj.hasOwnProperty(key) && obj[key] !== undefined && obj[key] !== null;
  };
  var $_1abf4fxbjd09f05j = {
    readOpt: readOpt,
    readOr: readOr,
    readOptFrom: readOptFrom,
    hasKey: hasKey
  };

  var wrap = function (key, value) {
    var r = {};
    r[key] = value;
    return r;
  };
  var wrapAll = function (keyvalues) {
    var r = {};
    $_bvikd2w9jd09f01c.each(keyvalues, function (kv) {
      r[kv.key] = kv.value;
    });
    return r;
  };
  var $_7em938xcjd09f05l = {
    wrap: wrap,
    wrapAll: wrapAll
  };

  var narrow$1 = function (obj, fields) {
    return $_6vlplwxajd09f05g.narrow(obj, fields);
  };
  var exclude$1 = function (obj, fields) {
    return $_6vlplwxajd09f05g.exclude(obj, fields);
  };
  var readOpt$1 = function (key) {
    return $_1abf4fxbjd09f05j.readOpt(key);
  };
  var readOr$1 = function (key, fallback) {
    return $_1abf4fxbjd09f05j.readOr(key, fallback);
  };
  var readOptFrom$1 = function (obj, key) {
    return $_1abf4fxbjd09f05j.readOptFrom(obj, key);
  };
  var wrap$1 = function (key, value) {
    return $_7em938xcjd09f05l.wrap(key, value);
  };
  var wrapAll$1 = function (keyvalues) {
    return $_7em938xcjd09f05l.wrapAll(keyvalues);
  };
  var indexOnKey$1 = function (array, key) {
    return $_6vlplwxajd09f05g.indexOnKey(array, key);
  };
  var consolidate = function (objs, base) {
    return $_43ns5nx7jd09f054.consolidateObj(objs, base);
  };
  var hasKey$1 = function (obj, key) {
    return $_1abf4fxbjd09f05j.hasKey(obj, key);
  };
  var $_q0etsx6jd09f053 = {
    narrow: narrow$1,
    exclude: exclude$1,
    readOpt: readOpt$1,
    readOr: readOr$1,
    readOptFrom: readOptFrom$1,
    wrap: wrap$1,
    wrapAll: wrapAll$1,
    indexOnKey: indexOnKey$1,
    hasKey: hasKey$1,
    consolidate: consolidate
  };

  var json = function () {
    return $_2l4l3gwdjd09f01u.getOrDie('JSON');
  };
  var parse = function (obj) {
    return json().parse(obj);
  };
  var stringify = function (obj, replacer, space) {
    return json().stringify(obj, replacer, space);
  };
  var $_2xfxp7xfjd09f05w = {
    parse: parse,
    stringify: stringify
  };

  var formatObj = function (input) {
    return $_4biushwzjd09f03k.isObject(input) && $_32a0zdx0jd09f03l.keys(input).length > 100 ? ' removed due to size' : $_2xfxp7xfjd09f05w.stringify(input, null, 2);
  };
  var formatErrors = function (errors) {
    var es = errors.length > 10 ? errors.slice(0, 10).concat([{
        path: [],
        getErrorInfo: function () {
          return '... (only showing first ten failures)';
        }
      }]) : errors;
    return $_bvikd2w9jd09f01c.map(es, function (e) {
      return 'Failed path: (' + e.path.join(' > ') + ')\n' + e.getErrorInfo();
    });
  };
  var $_9g5qkqxejd09f05r = {
    formatObj: formatObj,
    formatErrors: formatErrors
  };

  var nu$3 = function (path, getErrorInfo) {
    return $_anstx7x8jd09f05b.error([{
        path: path,
        getErrorInfo: getErrorInfo
      }]);
  };
  var missingStrict = function (path, key, obj) {
    return nu$3(path, function () {
      return 'Could not find valid *strict* value for "' + key + '" in ' + $_9g5qkqxejd09f05r.formatObj(obj);
    });
  };
  var missingKey = function (path, key) {
    return nu$3(path, function () {
      return 'Choice schema did not contain choice key: "' + key + '"';
    });
  };
  var missingBranch = function (path, branches, branch) {
    return nu$3(path, function () {
      return 'The chosen schema: "' + branch + '" did not exist in branches: ' + $_9g5qkqxejd09f05r.formatObj(branches);
    });
  };
  var unsupportedFields = function (path, unsupported) {
    return nu$3(path, function () {
      return 'There are unsupported fields: [' + unsupported.join(', ') + '] specified';
    });
  };
  var custom = function (path, err) {
    return nu$3(path, function () {
      return err;
    });
  };
  var toString = function (error) {
    return 'Failed path: (' + error.path.join(' > ') + ')\n' + error.getErrorInfo();
  };
  var $_dto5z4xdjd09f05o = {
    missingStrict: missingStrict,
    missingKey: missingKey,
    missingBranch: missingBranch,
    unsupportedFields: unsupportedFields,
    custom: custom,
    toString: toString
  };

  var typeAdt = $_enqz8zx4jd09f04j.generate([
    {
      setOf: [
        'validator',
        'valueType'
      ]
    },
    { arrOf: ['valueType'] },
    { objOf: ['fields'] },
    { itemOf: ['validator'] },
    {
      choiceOf: [
        'key',
        'branches'
      ]
    }
  ]);
  var fieldAdt = $_enqz8zx4jd09f04j.generate([
    {
      field: [
        'name',
        'presence',
        'type'
      ]
    },
    { state: ['name'] }
  ]);
  var $_1lnq8xgjd09f05x = {
    typeAdt: typeAdt,
    fieldAdt: fieldAdt
  };

  var adt$1 = $_enqz8zx4jd09f04j.generate([
    {
      field: [
        'key',
        'okey',
        'presence',
        'prop'
      ]
    },
    {
      state: [
        'okey',
        'instantiator'
      ]
    }
  ]);
  var output = function (okey, value) {
    return adt$1.state(okey, $_8z5eqrwbjd09f01q.constant(value));
  };
  var snapshot = function (okey) {
    return adt$1.state(okey, $_8z5eqrwbjd09f01q.identity);
  };
  var strictAccess = function (path, obj, key) {
    return $_1abf4fxbjd09f05j.readOptFrom(obj, key).fold(function () {
      return $_dto5z4xdjd09f05o.missingStrict(path, key, obj);
    }, $_anstx7x8jd09f05b.value);
  };
  var fallbackAccess = function (obj, key, fallbackThunk) {
    var v = $_1abf4fxbjd09f05j.readOptFrom(obj, key).fold(function () {
      return fallbackThunk(obj);
    }, $_8z5eqrwbjd09f01q.identity);
    return $_anstx7x8jd09f05b.value(v);
  };
  var optionAccess = function (obj, key) {
    return $_anstx7x8jd09f05b.value($_1abf4fxbjd09f05j.readOptFrom(obj, key));
  };
  var optionDefaultedAccess = function (obj, key, fallback) {
    var opt = $_1abf4fxbjd09f05j.readOptFrom(obj, key).map(function (val) {
      return val === true ? fallback(obj) : val;
    });
    return $_anstx7x8jd09f05b.value(opt);
  };
  var cExtractOne = function (path, obj, field, strength) {
    return field.fold(function (key, okey, presence, prop) {
      var bundle = function (av) {
        return prop.extract(path.concat([key]), strength, av).map(function (res) {
          return $_7em938xcjd09f05l.wrap(okey, strength(res));
        });
      };
      var bundleAsOption = function (optValue) {
        return optValue.fold(function () {
          var outcome = $_7em938xcjd09f05l.wrap(okey, strength($_8nwhzlwajd09f01m.none()));
          return $_anstx7x8jd09f05b.value(outcome);
        }, function (ov) {
          return prop.extract(path.concat([key]), strength, ov).map(function (res) {
            return $_7em938xcjd09f05l.wrap(okey, strength($_8nwhzlwajd09f01m.some(res)));
          });
        });
      };
      return function () {
        return presence.fold(function () {
          return strictAccess(path, obj, key).bind(bundle);
        }, function (fallbackThunk) {
          return fallbackAccess(obj, key, fallbackThunk).bind(bundle);
        }, function () {
          return optionAccess(obj, key).bind(bundleAsOption);
        }, function (fallbackThunk) {
          return optionDefaultedAccess(obj, key, fallbackThunk).bind(bundleAsOption);
        }, function (baseThunk) {
          var base = baseThunk(obj);
          return fallbackAccess(obj, key, $_8z5eqrwbjd09f01q.constant({})).map(function (v) {
            return $_3htayhwyjd09f03i.deepMerge(base, v);
          }).bind(bundle);
        });
      }();
    }, function (okey, instantiator) {
      var state = instantiator(obj);
      return $_anstx7x8jd09f05b.value($_7em938xcjd09f05l.wrap(okey, strength(state)));
    });
  };
  var cExtract = function (path, obj, fields, strength) {
    var results = $_bvikd2w9jd09f01c.map(fields, function (field) {
      return cExtractOne(path, obj, field, strength);
    });
    return $_43ns5nx7jd09f054.consolidateObj(results, {});
  };
  var value$1 = function (validator) {
    var extract = function (path, strength, val) {
      return validator(val).fold(function (err) {
        return $_dto5z4xdjd09f05o.custom(path, err);
      }, $_anstx7x8jd09f05b.value);
    };
    var toString = function () {
      return 'val';
    };
    var toDsl = function () {
      return $_1lnq8xgjd09f05x.typeAdt.itemOf(validator);
    };
    return {
      extract: extract,
      toString: toString,
      toDsl: toDsl
    };
  };
  var getSetKeys = function (obj) {
    var keys = $_32a0zdx0jd09f03l.keys(obj);
    return $_bvikd2w9jd09f01c.filter(keys, function (k) {
      return $_q0etsx6jd09f053.hasKey(obj, k);
    });
  };
  var objOnly = function (fields) {
    var delegate = obj(fields);
    var fieldNames = $_bvikd2w9jd09f01c.foldr(fields, function (acc, f) {
      return f.fold(function (key) {
        return $_3htayhwyjd09f03i.deepMerge(acc, $_q0etsx6jd09f053.wrap(key, true));
      }, $_8z5eqrwbjd09f01q.constant(acc));
    }, {});
    var extract = function (path, strength, o) {
      var keys = $_4biushwzjd09f03k.isBoolean(o) ? [] : getSetKeys(o);
      var extra = $_bvikd2w9jd09f01c.filter(keys, function (k) {
        return !$_q0etsx6jd09f053.hasKey(fieldNames, k);
      });
      return extra.length === 0 ? delegate.extract(path, strength, o) : $_dto5z4xdjd09f05o.unsupportedFields(path, extra);
    };
    return {
      extract: extract,
      toString: delegate.toString,
      toDsl: delegate.toDsl
    };
  };
  var obj = function (fields) {
    var extract = function (path, strength, o) {
      return cExtract(path, o, fields, strength);
    };
    var toString = function () {
      var fieldStrings = $_bvikd2w9jd09f01c.map(fields, function (field) {
        return field.fold(function (key, okey, presence, prop) {
          return key + ' -> ' + prop.toString();
        }, function (okey, instantiator) {
          return 'state(' + okey + ')';
        });
      });
      return 'obj{\n' + fieldStrings.join('\n') + '}';
    };
    var toDsl = function () {
      return $_1lnq8xgjd09f05x.typeAdt.objOf($_bvikd2w9jd09f01c.map(fields, function (f) {
        return f.fold(function (key, okey, presence, prop) {
          return $_1lnq8xgjd09f05x.fieldAdt.field(key, presence, prop);
        }, function (okey, instantiator) {
          return $_1lnq8xgjd09f05x.fieldAdt.state(okey);
        });
      }));
    };
    return {
      extract: extract,
      toString: toString,
      toDsl: toDsl
    };
  };
  var arr = function (prop) {
    var extract = function (path, strength, array) {
      var results = $_bvikd2w9jd09f01c.map(array, function (a, i) {
        return prop.extract(path.concat(['[' + i + ']']), strength, a);
      });
      return $_43ns5nx7jd09f054.consolidateArr(results);
    };
    var toString = function () {
      return 'array(' + prop.toString() + ')';
    };
    var toDsl = function () {
      return $_1lnq8xgjd09f05x.typeAdt.arrOf(prop);
    };
    return {
      extract: extract,
      toString: toString,
      toDsl: toDsl
    };
  };
  var setOf = function (validator, prop) {
    var validateKeys = function (path, keys) {
      return arr(value$1(validator)).extract(path, $_8z5eqrwbjd09f01q.identity, keys);
    };
    var extract = function (path, strength, o) {
      var keys = $_32a0zdx0jd09f03l.keys(o);
      return validateKeys(path, keys).bind(function (validKeys) {
        var schema = $_bvikd2w9jd09f01c.map(validKeys, function (vk) {
          return adt$1.field(vk, vk, $_buoc30x3jd09f047.strict(), prop);
        });
        return obj(schema).extract(path, strength, o);
      });
    };
    var toString = function () {
      return 'setOf(' + prop.toString() + ')';
    };
    var toDsl = function () {
      return $_1lnq8xgjd09f05x.typeAdt.setOf(validator, prop);
    };
    return {
      extract: extract,
      toString: toString,
      toDsl: toDsl
    };
  };
  var anyValue = value$1($_anstx7x8jd09f05b.value);
  var arrOfObj = $_8z5eqrwbjd09f01q.compose(arr, obj);
  var $_f2hvxex5jd09f04o = {
    anyValue: $_8z5eqrwbjd09f01q.constant(anyValue),
    value: value$1,
    obj: obj,
    objOnly: objOnly,
    arr: arr,
    setOf: setOf,
    arrOfObj: arrOfObj,
    state: adt$1.state,
    field: adt$1.field,
    output: output,
    snapshot: snapshot
  };

  var strict = function (key) {
    return $_f2hvxex5jd09f04o.field(key, key, $_buoc30x3jd09f047.strict(), $_f2hvxex5jd09f04o.anyValue());
  };
  var strictOf = function (key, schema) {
    return $_f2hvxex5jd09f04o.field(key, key, $_buoc30x3jd09f047.strict(), schema);
  };
  var strictFunction = function (key) {
    return $_f2hvxex5jd09f04o.field(key, key, $_buoc30x3jd09f047.strict(), $_f2hvxex5jd09f04o.value(function (f) {
      return $_4biushwzjd09f03k.isFunction(f) ? $_anstx7x8jd09f05b.value(f) : $_anstx7x8jd09f05b.error('Not a function');
    }));
  };
  var forbid = function (key, message) {
    return $_f2hvxex5jd09f04o.field(key, key, $_buoc30x3jd09f047.asOption(), $_f2hvxex5jd09f04o.value(function (v) {
      return $_anstx7x8jd09f05b.error('The field: ' + key + ' is forbidden. ' + message);
    }));
  };
  var strictArrayOf = function (key, prop) {
    return strictOf(key, prop);
  };
  var strictObjOf = function (key, objSchema) {
    return $_f2hvxex5jd09f04o.field(key, key, $_buoc30x3jd09f047.strict(), $_f2hvxex5jd09f04o.obj(objSchema));
  };
  var strictArrayOfObj = function (key, objFields) {
    return $_f2hvxex5jd09f04o.field(key, key, $_buoc30x3jd09f047.strict(), $_f2hvxex5jd09f04o.arrOfObj(objFields));
  };
  var option = function (key) {
    return $_f2hvxex5jd09f04o.field(key, key, $_buoc30x3jd09f047.asOption(), $_f2hvxex5jd09f04o.anyValue());
  };
  var optionOf = function (key, schema) {
    return $_f2hvxex5jd09f04o.field(key, key, $_buoc30x3jd09f047.asOption(), schema);
  };
  var optionObjOf = function (key, objSchema) {
    return $_f2hvxex5jd09f04o.field(key, key, $_buoc30x3jd09f047.asOption(), $_f2hvxex5jd09f04o.obj(objSchema));
  };
  var optionObjOfOnly = function (key, objSchema) {
    return $_f2hvxex5jd09f04o.field(key, key, $_buoc30x3jd09f047.asOption(), $_f2hvxex5jd09f04o.objOnly(objSchema));
  };
  var defaulted$1 = function (key, fallback) {
    return $_f2hvxex5jd09f04o.field(key, key, $_buoc30x3jd09f047.defaulted(fallback), $_f2hvxex5jd09f04o.anyValue());
  };
  var defaultedOf = function (key, fallback, schema) {
    return $_f2hvxex5jd09f04o.field(key, key, $_buoc30x3jd09f047.defaulted(fallback), schema);
  };
  var defaultedObjOf = function (key, fallback, objSchema) {
    return $_f2hvxex5jd09f04o.field(key, key, $_buoc30x3jd09f047.defaulted(fallback), $_f2hvxex5jd09f04o.obj(objSchema));
  };
  var field = function (key, okey, presence, prop) {
    return $_f2hvxex5jd09f04o.field(key, okey, presence, prop);
  };
  var state = function (okey, instantiator) {
    return $_f2hvxex5jd09f04o.state(okey, instantiator);
  };
  var $_1t7kykx2jd09f043 = {
    strict: strict,
    strictOf: strictOf,
    strictObjOf: strictObjOf,
    strictArrayOf: strictArrayOf,
    strictArrayOfObj: strictArrayOfObj,
    strictFunction: strictFunction,
    forbid: forbid,
    option: option,
    optionOf: optionOf,
    optionObjOf: optionObjOf,
    optionObjOfOnly: optionObjOfOnly,
    defaulted: defaulted$1,
    defaultedOf: defaultedOf,
    defaultedObjOf: defaultedObjOf,
    field: field,
    state: state
  };

  var chooseFrom = function (path, strength, input, branches, ch) {
    var fields = $_q0etsx6jd09f053.readOptFrom(branches, ch);
    return fields.fold(function () {
      return $_dto5z4xdjd09f05o.missingBranch(path, branches, ch);
    }, function (fs) {
      return $_f2hvxex5jd09f04o.obj(fs).extract(path.concat(['branch: ' + ch]), strength, input);
    });
  };
  var choose = function (key, branches) {
    var extract = function (path, strength, input) {
      var choice = $_q0etsx6jd09f053.readOptFrom(input, key);
      return choice.fold(function () {
        return $_dto5z4xdjd09f05o.missingKey(path, key);
      }, function (chosen) {
        return chooseFrom(path, strength, input, branches, chosen);
      });
    };
    var toString = function () {
      return 'chooseOn(' + key + '). Possible values: ' + $_32a0zdx0jd09f03l.keys(branches);
    };
    var toDsl = function () {
      return $_1lnq8xgjd09f05x.typeAdt.choiceOf(key, branches);
    };
    return {
      extract: extract,
      toString: toString,
      toDsl: toDsl
    };
  };
  var $_fembkxijd09f065 = { choose: choose };

  var anyValue$1 = $_f2hvxex5jd09f04o.value($_anstx7x8jd09f05b.value);
  var arrOfObj$1 = function (objFields) {
    return $_f2hvxex5jd09f04o.arrOfObj(objFields);
  };
  var arrOfVal = function () {
    return $_f2hvxex5jd09f04o.arr(anyValue$1);
  };
  var arrOf = $_f2hvxex5jd09f04o.arr;
  var objOf = $_f2hvxex5jd09f04o.obj;
  var objOfOnly = $_f2hvxex5jd09f04o.objOnly;
  var setOf$1 = $_f2hvxex5jd09f04o.setOf;
  var valueOf = function (validator) {
    return $_f2hvxex5jd09f04o.value(validator);
  };
  var extract = function (label, prop, strength, obj) {
    return prop.extract([label], strength, obj).fold(function (errs) {
      return $_anstx7x8jd09f05b.error({
        input: obj,
        errors: errs
      });
    }, $_anstx7x8jd09f05b.value);
  };
  var asStruct = function (label, prop, obj) {
    return extract(label, prop, $_8z5eqrwbjd09f01q.constant, obj);
  };
  var asRaw = function (label, prop, obj) {
    return extract(label, prop, $_8z5eqrwbjd09f01q.identity, obj);
  };
  var getOrDie$1 = function (extraction) {
    return extraction.fold(function (errInfo) {
      throw new Error(formatError(errInfo));
    }, $_8z5eqrwbjd09f01q.identity);
  };
  var asRawOrDie = function (label, prop, obj) {
    return getOrDie$1(asRaw(label, prop, obj));
  };
  var asStructOrDie = function (label, prop, obj) {
    return getOrDie$1(asStruct(label, prop, obj));
  };
  var formatError = function (errInfo) {
    return 'Errors: \n' + $_9g5qkqxejd09f05r.formatErrors(errInfo.errors) + '\n\nInput object: ' + $_9g5qkqxejd09f05r.formatObj(errInfo.input);
  };
  var choose$1 = function (key, branches) {
    return $_fembkxijd09f065.choose(key, branches);
  };
  var $_33aoy2xhjd09f061 = {
    anyValue: $_8z5eqrwbjd09f01q.constant(anyValue$1),
    arrOfObj: arrOfObj$1,
    arrOf: arrOf,
    arrOfVal: arrOfVal,
    valueOf: valueOf,
    setOf: setOf$1,
    objOf: objOf,
    objOfOnly: objOfOnly,
    asStruct: asStruct,
    asRaw: asRaw,
    asStructOrDie: asStructOrDie,
    asRawOrDie: asRawOrDie,
    getOrDie: getOrDie$1,
    formatError: formatError,
    choose: choose$1
  };

  var nu$4 = function (parts) {
    if (!$_q0etsx6jd09f053.hasKey(parts, 'can') && !$_q0etsx6jd09f053.hasKey(parts, 'abort') && !$_q0etsx6jd09f053.hasKey(parts, 'run'))
      throw new Error('EventHandler defined by: ' + $_2xfxp7xfjd09f05w.stringify(parts, null, 2) + ' does not have can, abort, or run!');
    return $_33aoy2xhjd09f061.asRawOrDie('Extracting event.handler', $_33aoy2xhjd09f061.objOfOnly([
      $_1t7kykx2jd09f043.defaulted('can', $_8z5eqrwbjd09f01q.constant(true)),
      $_1t7kykx2jd09f043.defaulted('abort', $_8z5eqrwbjd09f01q.constant(false)),
      $_1t7kykx2jd09f043.defaulted('run', $_8z5eqrwbjd09f01q.noop)
    ]), parts);
  };
  var all$1 = function (handlers, f) {
    return function () {
      var args = Array.prototype.slice.call(arguments, 0);
      return $_bvikd2w9jd09f01c.foldl(handlers, function (acc, handler) {
        return acc && f(handler).apply(undefined, args);
      }, true);
    };
  };
  var any = function (handlers, f) {
    return function () {
      var args = Array.prototype.slice.call(arguments, 0);
      return $_bvikd2w9jd09f01c.foldl(handlers, function (acc, handler) {
        return acc || f(handler).apply(undefined, args);
      }, false);
    };
  };
  var read = function (handler) {
    return $_4biushwzjd09f03k.isFunction(handler) ? {
      can: $_8z5eqrwbjd09f01q.constant(true),
      abort: $_8z5eqrwbjd09f01q.constant(false),
      run: handler
    } : handler;
  };
  var fuse = function (handlers) {
    var can = all$1(handlers, function (handler) {
      return handler.can;
    });
    var abort = any(handlers, function (handler) {
      return handler.abort;
    });
    var run = function () {
      var args = Array.prototype.slice.call(arguments, 0);
      $_bvikd2w9jd09f01c.each(handlers, function (handler) {
        handler.run.apply(undefined, args);
      });
    };
    return nu$4({
      can: can,
      abort: abort,
      run: run
    });
  };
  var $_cdgkzwx1jd09f03t = {
    read: read,
    fuse: fuse,
    nu: nu$4
  };

  var derive = $_q0etsx6jd09f053.wrapAll;
  var abort = function (name, predicate) {
    return {
      key: name,
      value: $_cdgkzwx1jd09f03t.nu({ abort: predicate })
    };
  };
  var can = function (name, predicate) {
    return {
      key: name,
      value: $_cdgkzwx1jd09f03t.nu({ can: predicate })
    };
  };
  var preventDefault = function (name) {
    return {
      key: name,
      value: $_cdgkzwx1jd09f03t.nu({
        run: function (component, simulatedEvent) {
          simulatedEvent.event().prevent();
        }
      })
    };
  };
  var run = function (name, handler) {
    return {
      key: name,
      value: $_cdgkzwx1jd09f03t.nu({ run: handler })
    };
  };
  var runActionExtra = function (name, action, extra) {
    return {
      key: name,
      value: $_cdgkzwx1jd09f03t.nu({
        run: function (component) {
          action.apply(undefined, [component].concat(extra));
        }
      })
    };
  };
  var runOnName = function (name) {
    return function (handler) {
      return run(name, handler);
    };
  };
  var runOnSourceName = function (name) {
    return function (handler) {
      return {
        key: name,
        value: $_cdgkzwx1jd09f03t.nu({
          run: function (component, simulatedEvent) {
            if ($_es2jyyw7jd09f012.isSource(component, simulatedEvent))
              handler(component, simulatedEvent);
          }
        })
      };
    };
  };
  var redirectToUid = function (name, uid) {
    return run(name, function (component, simulatedEvent) {
      component.getSystem().getByUid(uid).each(function (redirectee) {
        $_3b6lb8wvjd09f037.dispatchEvent(redirectee, redirectee.element(), name, simulatedEvent);
      });
    });
  };
  var redirectToPart = function (name, detail, partName) {
    var uid = detail.partUids()[partName];
    return redirectToUid(name, uid);
  };
  var runWithTarget = function (name, f) {
    return run(name, function (component, simulatedEvent) {
      component.getSystem().getByDom(simulatedEvent.event().target()).each(function (target) {
        f(component, target, simulatedEvent);
      });
    });
  };
  var cutter = function (name) {
    return run(name, function (component, simulatedEvent) {
      simulatedEvent.cut();
    });
  };
  var stopper = function (name) {
    return run(name, function (component, simulatedEvent) {
      simulatedEvent.stop();
    });
  };
  var $_6j84lww6jd09f00y = {
    derive: derive,
    run: run,
    preventDefault: preventDefault,
    runActionExtra: runActionExtra,
    runOnAttached: runOnSourceName($_5iytewwjd09f03c.attachedToDom()),
    runOnDetached: runOnSourceName($_5iytewwjd09f03c.detachedFromDom()),
    runOnInit: runOnSourceName($_5iytewwjd09f03c.systemInit()),
    runOnExecute: runOnName($_5iytewwjd09f03c.execute()),
    redirectToUid: redirectToUid,
    redirectToPart: redirectToPart,
    runWithTarget: runWithTarget,
    abort: abort,
    can: can,
    cutter: cutter,
    stopper: stopper
  };

  var markAsBehaviourApi = function (f, apiName, apiFunction) {
    return f;
  };
  var markAsExtraApi = function (f, extraName) {
    return f;
  };
  var markAsSketchApi = function (f, apiFunction) {
    return f;
  };
  var getAnnotation = $_8nwhzlwajd09f01m.none;
  var $_8a1c5yxjjd09f06g = {
    markAsBehaviourApi: markAsBehaviourApi,
    markAsExtraApi: markAsExtraApi,
    markAsSketchApi: markAsSketchApi,
    getAnnotation: getAnnotation
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
      $_bvikd2w9jd09f01c.each(fields, function (name, i) {
        struct[name] = $_8z5eqrwbjd09f01q.constant(values[i]);
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
    if (!$_4biushwzjd09f03k.isArray(array))
      throw new Error('The ' + label + ' fields must be an array. Was: ' + array + '.');
    $_bvikd2w9jd09f01c.each(array, function (a) {
      if (!$_4biushwzjd09f03k.isString(a))
        throw new Error('The value ' + a + ' in the ' + label + ' fields was not a string.');
    });
  };
  var invalidTypeMessage = function (incorrect, type) {
    throw new Error('All values need to be of type: ' + type + '. Keys (' + sort$1(incorrect).join(', ') + ') were not.');
  };
  var checkDupes = function (everything) {
    var sorted = sort$1(everything);
    var dupe = $_bvikd2w9jd09f01c.find(sorted, function (s, i) {
      return i < sorted.length - 1 && s === sorted[i + 1];
    });
    dupe.each(function (d) {
      throw new Error('The field: ' + d + ' occurs more than once in the combined fields: [' + sorted.join(', ') + '].');
    });
  };
  var $_8tw0s5xpjd09f06z = {
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
    $_8tw0s5xpjd09f06z.validateStrArr('required', required);
    $_8tw0s5xpjd09f06z.validateStrArr('optional', optional);
    $_8tw0s5xpjd09f06z.checkDupes(everything);
    return function (obj) {
      var keys = $_32a0zdx0jd09f03l.keys(obj);
      var allReqd = $_bvikd2w9jd09f01c.forall(required, function (req) {
        return $_bvikd2w9jd09f01c.contains(keys, req);
      });
      if (!allReqd)
        $_8tw0s5xpjd09f06z.reqMessage(required, keys);
      var unsupported = $_bvikd2w9jd09f01c.filter(keys, function (key) {
        return !$_bvikd2w9jd09f01c.contains(everything, key);
      });
      if (unsupported.length > 0)
        $_8tw0s5xpjd09f06z.unsuppMessage(unsupported);
      var r = {};
      $_bvikd2w9jd09f01c.each(required, function (req) {
        r[req] = $_8z5eqrwbjd09f01q.constant(obj[req]);
      });
      $_bvikd2w9jd09f01c.each(optional, function (opt) {
        r[opt] = $_8z5eqrwbjd09f01q.constant(Object.prototype.hasOwnProperty.call(obj, opt) ? $_8nwhzlwajd09f01m.some(obj[opt]) : $_8nwhzlwajd09f01m.none());
      });
      return r;
    };
  }

  var $_gbd4xkxmjd09f06u = {
    immutable: Immutable,
    immutableBag: MixedBag
  };

  var nu$5 = $_gbd4xkxmjd09f06u.immutableBag(['tag'], [
    'classes',
    'attributes',
    'styles',
    'value',
    'innerHtml',
    'domChildren',
    'defChildren'
  ]);
  var defToStr = function (defn) {
    var raw = defToRaw(defn);
    return $_2xfxp7xfjd09f05w.stringify(raw, null, 2);
  };
  var defToRaw = function (defn) {
    return {
      tag: defn.tag(),
      classes: defn.classes().getOr([]),
      attributes: defn.attributes().getOr({}),
      styles: defn.styles().getOr({}),
      value: defn.value().getOr('<none>'),
      innerHtml: defn.innerHtml().getOr('<none>'),
      defChildren: defn.defChildren().getOr('<none>'),
      domChildren: defn.domChildren().fold(function () {
        return '<none>';
      }, function (children) {
        return children.length === 0 ? '0 children, but still specified' : String(children.length);
      })
    };
  };
  var $_dzk95nxljd09f06r = {
    nu: nu$5,
    defToStr: defToStr,
    defToRaw: defToRaw
  };

  var fields = [
    'classes',
    'attributes',
    'styles',
    'value',
    'innerHtml',
    'defChildren',
    'domChildren'
  ];
  var nu$6 = $_gbd4xkxmjd09f06u.immutableBag([], fields);
  var derive$1 = function (settings) {
    var r = {};
    var keys = $_32a0zdx0jd09f03l.keys(settings);
    $_bvikd2w9jd09f01c.each(keys, function (key) {
      settings[key].each(function (v) {
        r[key] = v;
      });
    });
    return nu$6(r);
  };
  var modToStr = function (mod) {
    var raw = modToRaw(mod);
    return $_2xfxp7xfjd09f05w.stringify(raw, null, 2);
  };
  var modToRaw = function (mod) {
    return {
      classes: mod.classes().getOr('<none>'),
      attributes: mod.attributes().getOr('<none>'),
      styles: mod.styles().getOr('<none>'),
      value: mod.value().getOr('<none>'),
      innerHtml: mod.innerHtml().getOr('<none>'),
      defChildren: mod.defChildren().getOr('<none>'),
      domChildren: mod.domChildren().fold(function () {
        return '<none>';
      }, function (children) {
        return children.length === 0 ? '0 children, but still specified' : String(children.length);
      })
    };
  };
  var clashingOptArrays = function (key, oArr1, oArr2) {
    return oArr1.fold(function () {
      return oArr2.fold(function () {
        return {};
      }, function (arr2) {
        return $_q0etsx6jd09f053.wrap(key, arr2);
      });
    }, function (arr1) {
      return oArr2.fold(function () {
        return $_q0etsx6jd09f053.wrap(key, arr1);
      }, function (arr2) {
        return $_q0etsx6jd09f053.wrap(key, arr2);
      });
    });
  };
  var merge$1 = function (defnA, mod) {
    var raw = $_3htayhwyjd09f03i.deepMerge({
      tag: defnA.tag(),
      classes: mod.classes().getOr([]).concat(defnA.classes().getOr([])),
      attributes: $_3htayhwyjd09f03i.merge(defnA.attributes().getOr({}), mod.attributes().getOr({})),
      styles: $_3htayhwyjd09f03i.merge(defnA.styles().getOr({}), mod.styles().getOr({}))
    }, mod.innerHtml().or(defnA.innerHtml()).map(function (innerHtml) {
      return $_q0etsx6jd09f053.wrap('innerHtml', innerHtml);
    }).getOr({}), clashingOptArrays('domChildren', mod.domChildren(), defnA.domChildren()), clashingOptArrays('defChildren', mod.defChildren(), defnA.defChildren()), mod.value().or(defnA.value()).map(function (value) {
      return $_q0etsx6jd09f053.wrap('value', value);
    }).getOr({}));
    return $_dzk95nxljd09f06r.nu(raw);
  };
  var $_brhg4pxkjd09f06j = {
    nu: nu$6,
    derive: derive$1,
    merge: merge$1,
    modToStr: modToStr,
    modToRaw: modToRaw
  };

  var executeEvent = function (bConfig, bState, executor) {
    return $_6j84lww6jd09f00y.runOnExecute(function (component) {
      executor(component, bConfig, bState);
    });
  };
  var loadEvent = function (bConfig, bState, f) {
    return $_6j84lww6jd09f00y.runOnInit(function (component, simulatedEvent) {
      f(component, bConfig, bState);
    });
  };
  var create = function (schema, name, active, apis, extra, state) {
    var configSchema = $_33aoy2xhjd09f061.objOfOnly(schema);
    var schemaSchema = $_1t7kykx2jd09f043.optionObjOf(name, [$_1t7kykx2jd09f043.optionObjOfOnly('config', schema)]);
    return doCreate(configSchema, schemaSchema, name, active, apis, extra, state);
  };
  var createModes = function (modes, name, active, apis, extra, state) {
    var configSchema = modes;
    var schemaSchema = $_1t7kykx2jd09f043.optionObjOf(name, [$_1t7kykx2jd09f043.optionOf('config', modes)]);
    return doCreate(configSchema, schemaSchema, name, active, apis, extra, state);
  };
  var wrapApi = function (bName, apiFunction, apiName) {
    var f = function (component) {
      var args = arguments;
      return component.config({ name: $_8z5eqrwbjd09f01q.constant(bName) }).fold(function () {
        throw new Error('We could not find any behaviour configuration for: ' + bName + '. Using API: ' + apiName);
      }, function (info) {
        var rest = Array.prototype.slice.call(args, 1);
        return apiFunction.apply(undefined, [
          component,
          info.config,
          info.state
        ].concat(rest));
      });
    };
    return $_8a1c5yxjjd09f06g.markAsBehaviourApi(f, apiName, apiFunction);
  };
  var revokeBehaviour = function (name) {
    return {
      key: name,
      value: undefined
    };
  };
  var doCreate = function (configSchema, schemaSchema, name, active, apis, extra, state) {
    var getConfig = function (info) {
      return $_q0etsx6jd09f053.hasKey(info, name) ? info[name]() : $_8nwhzlwajd09f01m.none();
    };
    var wrappedApis = $_32a0zdx0jd09f03l.map(apis, function (apiF, apiName) {
      return wrapApi(name, apiF, apiName);
    });
    var wrappedExtra = $_32a0zdx0jd09f03l.map(extra, function (extraF, extraName) {
      return $_8a1c5yxjjd09f06g.markAsExtraApi(extraF, extraName);
    });
    var me = $_3htayhwyjd09f03i.deepMerge(wrappedExtra, wrappedApis, {
      revoke: $_8z5eqrwbjd09f01q.curry(revokeBehaviour, name),
      config: function (spec) {
        var prepared = $_33aoy2xhjd09f061.asStructOrDie(name + '-config', configSchema, spec);
        return {
          key: name,
          value: {
            config: prepared,
            me: me,
            configAsRaw: $_9ur00cwhjd09f021.cached(function () {
              return $_33aoy2xhjd09f061.asRawOrDie(name + '-config', configSchema, spec);
            }),
            initialConfig: spec,
            state: state
          }
        };
      },
      schema: function () {
        return schemaSchema;
      },
      exhibit: function (info, base) {
        return getConfig(info).bind(function (behaviourInfo) {
          return $_q0etsx6jd09f053.readOptFrom(active, 'exhibit').map(function (exhibitor) {
            return exhibitor(base, behaviourInfo.config, behaviourInfo.state);
          });
        }).getOr($_brhg4pxkjd09f06j.nu({}));
      },
      name: function () {
        return name;
      },
      handlers: function (info) {
        return getConfig(info).bind(function (behaviourInfo) {
          return $_q0etsx6jd09f053.readOptFrom(active, 'events').map(function (events) {
            return events(behaviourInfo.config, behaviourInfo.state);
          });
        }).getOr({});
      }
    });
    return me;
  };
  var $_8zny04w5jd09f00m = {
    executeEvent: executeEvent,
    loadEvent: loadEvent,
    create: create,
    createModes: createModes
  };

  var base = function (handleUnsupported, required) {
    return baseWith(handleUnsupported, required, {
      validate: $_4biushwzjd09f03k.isFunction,
      label: 'function'
    });
  };
  var baseWith = function (handleUnsupported, required, pred) {
    if (required.length === 0)
      throw new Error('You must specify at least one required field.');
    $_8tw0s5xpjd09f06z.validateStrArr('required', required);
    $_8tw0s5xpjd09f06z.checkDupes(required);
    return function (obj) {
      var keys = $_32a0zdx0jd09f03l.keys(obj);
      var allReqd = $_bvikd2w9jd09f01c.forall(required, function (req) {
        return $_bvikd2w9jd09f01c.contains(keys, req);
      });
      if (!allReqd)
        $_8tw0s5xpjd09f06z.reqMessage(required, keys);
      handleUnsupported(required, keys);
      var invalidKeys = $_bvikd2w9jd09f01c.filter(required, function (key) {
        return !pred.validate(obj[key], key);
      });
      if (invalidKeys.length > 0)
        $_8tw0s5xpjd09f06z.invalidTypeMessage(invalidKeys, pred.label);
      return obj;
    };
  };
  var handleExact = function (required, keys) {
    var unsupported = $_bvikd2w9jd09f01c.filter(keys, function (key) {
      return !$_bvikd2w9jd09f01c.contains(required, key);
    });
    if (unsupported.length > 0)
      $_8tw0s5xpjd09f06z.unsuppMessage(unsupported);
  };
  var allowExtra = $_8z5eqrwbjd09f01q.noop;
  var $_6o1y88xsjd09f075 = {
    exactly: $_8z5eqrwbjd09f01q.curry(base, handleExact),
    ensure: $_8z5eqrwbjd09f01q.curry(base, allowExtra),
    ensureWith: $_8z5eqrwbjd09f01q.curry(baseWith, allowExtra)
  };

  var BehaviourState = $_6o1y88xsjd09f075.ensure(['readState']);

  var init = function () {
    return BehaviourState({
      readState: function () {
        return 'No State required';
      }
    });
  };
  var $_7dmh49xqjd09f072 = { init: init };

  var derive$2 = function (capabilities) {
    return $_q0etsx6jd09f053.wrapAll(capabilities);
  };
  var simpleSchema = $_33aoy2xhjd09f061.objOfOnly([
    $_1t7kykx2jd09f043.strict('fields'),
    $_1t7kykx2jd09f043.strict('name'),
    $_1t7kykx2jd09f043.defaulted('active', {}),
    $_1t7kykx2jd09f043.defaulted('apis', {}),
    $_1t7kykx2jd09f043.defaulted('extra', {}),
    $_1t7kykx2jd09f043.defaulted('state', $_7dmh49xqjd09f072)
  ]);
  var create$1 = function (data) {
    var value = $_33aoy2xhjd09f061.asRawOrDie('Creating behaviour: ' + data.name, simpleSchema, data);
    return $_8zny04w5jd09f00m.create(value.fields, value.name, value.active, value.apis, value.extra, value.state);
  };
  var modeSchema = $_33aoy2xhjd09f061.objOfOnly([
    $_1t7kykx2jd09f043.strict('branchKey'),
    $_1t7kykx2jd09f043.strict('branches'),
    $_1t7kykx2jd09f043.strict('name'),
    $_1t7kykx2jd09f043.defaulted('active', {}),
    $_1t7kykx2jd09f043.defaulted('apis', {}),
    $_1t7kykx2jd09f043.defaulted('extra', {}),
    $_1t7kykx2jd09f043.defaulted('state', $_7dmh49xqjd09f072)
  ]);
  var createModes$1 = function (data) {
    var value = $_33aoy2xhjd09f061.asRawOrDie('Creating behaviour: ' + data.name, modeSchema, data);
    return $_8zny04w5jd09f00m.createModes($_33aoy2xhjd09f061.choose(value.branchKey, value.branches), value.name, value.active, value.apis, value.extra, value.state);
  };
  var $_fq8al5w4jd09f00e = {
    derive: derive$2,
    revoke: $_8z5eqrwbjd09f01q.constant(undefined),
    noActive: $_8z5eqrwbjd09f01q.constant({}),
    noApis: $_8z5eqrwbjd09f01q.constant({}),
    noExtra: $_8z5eqrwbjd09f01q.constant({}),
    noState: $_8z5eqrwbjd09f01q.constant($_7dmh49xqjd09f072),
    create: create$1,
    createModes: createModes$1
  };

  function Toggler (turnOff, turnOn, initial) {
    var active = initial || false;
    var on = function () {
      turnOn();
      active = true;
    };
    var off = function () {
      turnOff();
      active = false;
    };
    var toggle = function () {
      var f = active ? off : on;
      f();
    };
    var isOn = function () {
      return active;
    };
    return {
      on: on,
      off: off,
      toggle: toggle,
      isOn: isOn
    };
  }

  var name = function (element) {
    var r = element.dom().nodeName;
    return r.toLowerCase();
  };
  var type = function (element) {
    return element.dom().nodeType;
  };
  var value$2 = function (element) {
    return element.dom().nodeValue;
  };
  var isType$1 = function (t) {
    return function (element) {
      return type(element) === t;
    };
  };
  var isComment = function (element) {
    return type(element) === $_7e0rsqwujd09f036.COMMENT || name(element) === '#comment';
  };
  var isElement = isType$1($_7e0rsqwujd09f036.ELEMENT);
  var isText = isType$1($_7e0rsqwujd09f036.TEXT);
  var isDocument = isType$1($_7e0rsqwujd09f036.DOCUMENT);
  var $_arrpm2xxjd09f07k = {
    name: name,
    type: type,
    value: value$2,
    isElement: isElement,
    isText: isText,
    isDocument: isDocument,
    isComment: isComment
  };

  var rawSet = function (dom, key, value) {
    if ($_4biushwzjd09f03k.isString(value) || $_4biushwzjd09f03k.isBoolean(value) || $_4biushwzjd09f03k.isNumber(value)) {
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
    $_32a0zdx0jd09f03l.each(attrs, function (v, k) {
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
    return $_bvikd2w9jd09f01c.foldl(element.dom().attributes, function (acc, attr) {
      acc[attr.name] = attr.value;
      return acc;
    }, {});
  };
  var transferOne = function (source, destination, attr) {
    if (has(source, attr) && !has(destination, attr))
      set(destination, attr, get(source, attr));
  };
  var transfer = function (source, destination, attrs) {
    if (!$_arrpm2xxjd09f07k.isElement(source) || !$_arrpm2xxjd09f07k.isElement(destination))
      return;
    $_bvikd2w9jd09f01c.each(attrs, function (attr) {
      transferOne(source, destination, attr);
    });
  };
  var $_6spjcmxwjd09f07e = {
    clone: clone,
    set: set,
    setAll: setAll,
    get: get,
    has: has,
    remove: remove,
    hasNone: hasNone,
    transfer: transfer
  };

  var read$1 = function (element, attr) {
    var value = $_6spjcmxwjd09f07e.get(element, attr);
    return value === undefined || value === '' ? [] : value.split(' ');
  };
  var add = function (element, attr, id) {
    var old = read$1(element, attr);
    var nu = old.concat([id]);
    $_6spjcmxwjd09f07e.set(element, attr, nu.join(' '));
  };
  var remove$1 = function (element, attr, id) {
    var nu = $_bvikd2w9jd09f01c.filter(read$1(element, attr), function (v) {
      return v !== id;
    });
    if (nu.length > 0)
      $_6spjcmxwjd09f07e.set(element, attr, nu.join(' '));
    else
      $_6spjcmxwjd09f07e.remove(element, attr);
  };
  var $_gijzbkxzjd09f07n = {
    read: read$1,
    add: add,
    remove: remove$1
  };

  var supports = function (element) {
    return element.dom().classList !== undefined;
  };
  var get$1 = function (element) {
    return $_gijzbkxzjd09f07n.read(element, 'class');
  };
  var add$1 = function (element, clazz) {
    return $_gijzbkxzjd09f07n.add(element, 'class', clazz);
  };
  var remove$2 = function (element, clazz) {
    return $_gijzbkxzjd09f07n.remove(element, 'class', clazz);
  };
  var toggle = function (element, clazz) {
    if ($_bvikd2w9jd09f01c.contains(get$1(element), clazz)) {
      remove$2(element, clazz);
    } else {
      add$1(element, clazz);
    }
  };
  var $_7m9egxyjd09f07l = {
    get: get$1,
    add: add$1,
    remove: remove$2,
    toggle: toggle,
    supports: supports
  };

  var add$2 = function (element, clazz) {
    if ($_7m9egxyjd09f07l.supports(element))
      element.dom().classList.add(clazz);
    else
      $_7m9egxyjd09f07l.add(element, clazz);
  };
  var cleanClass = function (element) {
    var classList = $_7m9egxyjd09f07l.supports(element) ? element.dom().classList : $_7m9egxyjd09f07l.get(element);
    if (classList.length === 0) {
      $_6spjcmxwjd09f07e.remove(element, 'class');
    }
  };
  var remove$3 = function (element, clazz) {
    if ($_7m9egxyjd09f07l.supports(element)) {
      var classList = element.dom().classList;
      classList.remove(clazz);
    } else
      $_7m9egxyjd09f07l.remove(element, clazz);
    cleanClass(element);
  };
  var toggle$1 = function (element, clazz) {
    return $_7m9egxyjd09f07l.supports(element) ? element.dom().classList.toggle(clazz) : $_7m9egxyjd09f07l.toggle(element, clazz);
  };
  var toggler = function (element, clazz) {
    var hasClasslist = $_7m9egxyjd09f07l.supports(element);
    var classList = element.dom().classList;
    var off = function () {
      if (hasClasslist)
        classList.remove(clazz);
      else
        $_7m9egxyjd09f07l.remove(element, clazz);
    };
    var on = function () {
      if (hasClasslist)
        classList.add(clazz);
      else
        $_7m9egxyjd09f07l.add(element, clazz);
    };
    return Toggler(off, on, has$1(element, clazz));
  };
  var has$1 = function (element, clazz) {
    return $_7m9egxyjd09f07l.supports(element) && element.dom().classList.contains(clazz);
  };
  var $_e7d6ttxujd09f07b = {
    add: add$2,
    remove: remove$3,
    toggle: toggle$1,
    toggler: toggler,
    has: has$1
  };

  var swap = function (element, addCls, removeCls) {
    $_e7d6ttxujd09f07b.remove(element, removeCls);
    $_e7d6ttxujd09f07b.add(element, addCls);
  };
  var toAlpha = function (component, swapConfig, swapState) {
    swap(component.element(), swapConfig.alpha(), swapConfig.omega());
  };
  var toOmega = function (component, swapConfig, swapState) {
    swap(component.element(), swapConfig.omega(), swapConfig.alpha());
  };
  var clear = function (component, swapConfig, swapState) {
    $_e7d6ttxujd09f07b.remove(component.element(), swapConfig.alpha());
    $_e7d6ttxujd09f07b.remove(component.element(), swapConfig.omega());
  };
  var isAlpha = function (component, swapConfig, swapState) {
    return $_e7d6ttxujd09f07b.has(component.element(), swapConfig.alpha());
  };
  var isOmega = function (component, swapConfig, swapState) {
    return $_e7d6ttxujd09f07b.has(component.element(), swapConfig.omega());
  };
  var $_94yb7nxtjd09f078 = {
    toAlpha: toAlpha,
    toOmega: toOmega,
    isAlpha: isAlpha,
    isOmega: isOmega,
    clear: clear
  };

  var SwapSchema = [
    $_1t7kykx2jd09f043.strict('alpha'),
    $_1t7kykx2jd09f043.strict('omega')
  ];

  var Swapping = $_fq8al5w4jd09f00e.create({
    fields: SwapSchema,
    name: 'swapping',
    apis: $_94yb7nxtjd09f078
  });

  var toArray = function (target, f) {
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
  var $_1s7hv7y4jd09f08i = { toArray: toArray };

  var owner = function (element) {
    return $_cnbf2uwtjd09f033.fromDom(element.dom().ownerDocument);
  };
  var documentElement = function (element) {
    var doc = owner(element);
    return $_cnbf2uwtjd09f033.fromDom(doc.dom().documentElement);
  };
  var defaultView = function (element) {
    var el = element.dom();
    var defaultView = el.ownerDocument.defaultView;
    return $_cnbf2uwtjd09f033.fromDom(defaultView);
  };
  var parent = function (element) {
    var dom = element.dom();
    return $_8nwhzlwajd09f01m.from(dom.parentNode).map($_cnbf2uwtjd09f033.fromDom);
  };
  var findIndex$1 = function (element) {
    return parent(element).bind(function (p) {
      var kin = children(p);
      return $_bvikd2w9jd09f01c.findIndex(kin, function (elem) {
        return $_6s6cs1w8jd09f014.eq(element, elem);
      });
    });
  };
  var parents = function (element, isRoot) {
    var stop = $_4biushwzjd09f03k.isFunction(isRoot) ? isRoot : $_8z5eqrwbjd09f01q.constant(false);
    var dom = element.dom();
    var ret = [];
    while (dom.parentNode !== null && dom.parentNode !== undefined) {
      var rawParent = dom.parentNode;
      var parent = $_cnbf2uwtjd09f033.fromDom(rawParent);
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
      return $_bvikd2w9jd09f01c.filter(elements, function (x) {
        return !$_6s6cs1w8jd09f014.eq(element, x);
      });
    };
    return parent(element).map(children).map(filterSelf).getOr([]);
  };
  var offsetParent = function (element) {
    var dom = element.dom();
    return $_8nwhzlwajd09f01m.from(dom.offsetParent).map($_cnbf2uwtjd09f033.fromDom);
  };
  var prevSibling = function (element) {
    var dom = element.dom();
    return $_8nwhzlwajd09f01m.from(dom.previousSibling).map($_cnbf2uwtjd09f033.fromDom);
  };
  var nextSibling = function (element) {
    var dom = element.dom();
    return $_8nwhzlwajd09f01m.from(dom.nextSibling).map($_cnbf2uwtjd09f033.fromDom);
  };
  var prevSiblings = function (element) {
    return $_bvikd2w9jd09f01c.reverse($_1s7hv7y4jd09f08i.toArray(element, prevSibling));
  };
  var nextSiblings = function (element) {
    return $_1s7hv7y4jd09f08i.toArray(element, nextSibling);
  };
  var children = function (element) {
    var dom = element.dom();
    return $_bvikd2w9jd09f01c.map(dom.childNodes, $_cnbf2uwtjd09f033.fromDom);
  };
  var child = function (element, index) {
    var children = element.dom().childNodes;
    return $_8nwhzlwajd09f01m.from(children[index]).map($_cnbf2uwtjd09f033.fromDom);
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
  var spot = $_gbd4xkxmjd09f06u.immutable('element', 'offset');
  var leaf = function (element, offset) {
    var cs = children(element);
    return cs.length > 0 && offset < cs.length ? spot(cs[offset], 0) : spot(element, offset);
  };
  var $_sdd74y3jd09f08a = {
    owner: owner,
    defaultView: defaultView,
    documentElement: documentElement,
    parent: parent,
    findIndex: findIndex$1,
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

  var before = function (marker, element) {
    var parent = $_sdd74y3jd09f08a.parent(marker);
    parent.each(function (v) {
      v.dom().insertBefore(element.dom(), marker.dom());
    });
  };
  var after = function (marker, element) {
    var sibling = $_sdd74y3jd09f08a.nextSibling(marker);
    sibling.fold(function () {
      var parent = $_sdd74y3jd09f08a.parent(marker);
      parent.each(function (v) {
        append(v, element);
      });
    }, function (v) {
      before(v, element);
    });
  };
  var prepend = function (parent, element) {
    var firstChild = $_sdd74y3jd09f08a.firstChild(parent);
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
    $_sdd74y3jd09f08a.child(parent, index).fold(function () {
      append(parent, element);
    }, function (v) {
      before(v, element);
    });
  };
  var wrap$2 = function (element, wrapper) {
    before(element, wrapper);
    append(wrapper, element);
  };
  var $_3dpbyky2jd09f081 = {
    before: before,
    after: after,
    prepend: prepend,
    append: append,
    appendAt: appendAt,
    wrap: wrap$2
  };

  var before$1 = function (marker, elements) {
    $_bvikd2w9jd09f01c.each(elements, function (x) {
      $_3dpbyky2jd09f081.before(marker, x);
    });
  };
  var after$1 = function (marker, elements) {
    $_bvikd2w9jd09f01c.each(elements, function (x, i) {
      var e = i === 0 ? marker : elements[i - 1];
      $_3dpbyky2jd09f081.after(e, x);
    });
  };
  var prepend$1 = function (parent, elements) {
    $_bvikd2w9jd09f01c.each(elements.slice().reverse(), function (x) {
      $_3dpbyky2jd09f081.prepend(parent, x);
    });
  };
  var append$1 = function (parent, elements) {
    $_bvikd2w9jd09f01c.each(elements, function (x) {
      $_3dpbyky2jd09f081.append(parent, x);
    });
  };
  var $_9afv6jy6jd09f08m = {
    before: before$1,
    after: after$1,
    prepend: prepend$1,
    append: append$1
  };

  var empty = function (element) {
    element.dom().textContent = '';
    $_bvikd2w9jd09f01c.each($_sdd74y3jd09f08a.children(element), function (rogue) {
      remove$4(rogue);
    });
  };
  var remove$4 = function (element) {
    var dom = element.dom();
    if (dom.parentNode !== null)
      dom.parentNode.removeChild(dom);
  };
  var unwrap = function (wrapper) {
    var children = $_sdd74y3jd09f08a.children(wrapper);
    if (children.length > 0)
      $_9afv6jy6jd09f08m.before(wrapper, children);
    remove$4(wrapper);
  };
  var $_72wxi1y5jd09f08k = {
    empty: empty,
    remove: remove$4,
    unwrap: unwrap
  };

  var inBody = function (element) {
    var dom = $_arrpm2xxjd09f07k.isText(element) ? element.dom().parentNode : element.dom();
    return dom !== undefined && dom !== null && dom.ownerDocument.body.contains(dom);
  };
  var body = $_9ur00cwhjd09f021.cached(function () {
    return getBody($_cnbf2uwtjd09f033.fromDom(document));
  });
  var getBody = function (doc) {
    var body = doc.dom().body;
    if (body === null || body === undefined)
      throw 'Body is not available yet';
    return $_cnbf2uwtjd09f033.fromDom(body);
  };
  var $_7m8dypy7jd09f08o = {
    body: body,
    getBody: getBody,
    inBody: inBody
  };

  var fireDetaching = function (component) {
    $_3b6lb8wvjd09f037.emit(component, $_5iytewwjd09f03c.detachedFromDom());
    var children = component.components();
    $_bvikd2w9jd09f01c.each(children, fireDetaching);
  };
  var fireAttaching = function (component) {
    var children = component.components();
    $_bvikd2w9jd09f01c.each(children, fireAttaching);
    $_3b6lb8wvjd09f037.emit(component, $_5iytewwjd09f03c.attachedToDom());
  };
  var attach = function (parent, child) {
    attachWith(parent, child, $_3dpbyky2jd09f081.append);
  };
  var attachWith = function (parent, child, insertion) {
    parent.getSystem().addToWorld(child);
    insertion(parent.element(), child.element());
    if ($_7m8dypy7jd09f08o.inBody(parent.element()))
      fireAttaching(child);
    parent.syncComponents();
  };
  var doDetach = function (component) {
    fireDetaching(component);
    $_72wxi1y5jd09f08k.remove(component.element());
    component.getSystem().removeFromWorld(component);
  };
  var detach = function (component) {
    var parent = $_sdd74y3jd09f08a.parent(component.element()).bind(function (p) {
      return component.getSystem().getByDom(p).fold($_8nwhzlwajd09f01m.none, $_8nwhzlwajd09f01m.some);
    });
    doDetach(component);
    parent.each(function (p) {
      p.syncComponents();
    });
  };
  var detachChildren = function (component) {
    var subs = component.components();
    $_bvikd2w9jd09f01c.each(subs, doDetach);
    $_72wxi1y5jd09f08k.empty(component.element());
    component.syncComponents();
  };
  var attachSystem = function (element, guiSystem) {
    $_3dpbyky2jd09f081.append(element, guiSystem.element());
    var children = $_sdd74y3jd09f08a.children(guiSystem.element());
    $_bvikd2w9jd09f01c.each(children, function (child) {
      guiSystem.getByDom(child).each(fireAttaching);
    });
  };
  var detachSystem = function (guiSystem) {
    var children = $_sdd74y3jd09f08a.children(guiSystem.element());
    $_bvikd2w9jd09f01c.each(children, function (child) {
      guiSystem.getByDom(child).each(fireDetaching);
    });
    $_72wxi1y5jd09f08k.remove(guiSystem.element());
  };
  var $_crdx3oy1jd09f07s = {
    attach: attach,
    attachWith: attachWith,
    detach: detach,
    detachChildren: detachChildren,
    attachSystem: attachSystem,
    detachSystem: detachSystem
  };

  var fromHtml$1 = function (html, scope) {
    var doc = scope || document;
    var div = doc.createElement('div');
    div.innerHTML = html;
    return $_sdd74y3jd09f08a.children($_cnbf2uwtjd09f033.fromDom(div));
  };
  var fromTags = function (tags, scope) {
    return $_bvikd2w9jd09f01c.map(tags, function (x) {
      return $_cnbf2uwtjd09f033.fromTag(x, scope);
    });
  };
  var fromText$1 = function (texts, scope) {
    return $_bvikd2w9jd09f01c.map(texts, function (x) {
      return $_cnbf2uwtjd09f033.fromText(x, scope);
    });
  };
  var fromDom$1 = function (nodes) {
    return $_bvikd2w9jd09f01c.map(nodes, $_cnbf2uwtjd09f033.fromDom);
  };
  var $_5vzwgqycjd09f097 = {
    fromHtml: fromHtml$1,
    fromTags: fromTags,
    fromText: fromText$1,
    fromDom: fromDom$1
  };

  var get$2 = function (element) {
    return element.dom().innerHTML;
  };
  var set$1 = function (element, content) {
    var owner = $_sdd74y3jd09f08a.owner(element);
    var docDom = owner.dom();
    var fragment = $_cnbf2uwtjd09f033.fromDom(docDom.createDocumentFragment());
    var contentElements = $_5vzwgqycjd09f097.fromHtml(content, docDom);
    $_9afv6jy6jd09f08m.append(fragment, contentElements);
    $_72wxi1y5jd09f08k.empty(element);
    $_3dpbyky2jd09f081.append(element, fragment);
  };
  var getOuter = function (element) {
    var container = $_cnbf2uwtjd09f033.fromTag('div');
    var clone = $_cnbf2uwtjd09f033.fromDom(element.dom().cloneNode(true));
    $_3dpbyky2jd09f081.append(container, clone);
    return get$2(container);
  };
  var $_ck4yooybjd09f095 = {
    get: get$2,
    set: set$1,
    getOuter: getOuter
  };

  var clone$1 = function (original, deep) {
    return $_cnbf2uwtjd09f033.fromDom(original.dom().cloneNode(deep));
  };
  var shallow$1 = function (original) {
    return clone$1(original, false);
  };
  var deep$1 = function (original) {
    return clone$1(original, true);
  };
  var shallowAs = function (original, tag) {
    var nu = $_cnbf2uwtjd09f033.fromTag(tag);
    var attributes = $_6spjcmxwjd09f07e.clone(original);
    $_6spjcmxwjd09f07e.setAll(nu, attributes);
    return nu;
  };
  var copy = function (original, tag) {
    var nu = shallowAs(original, tag);
    var cloneChildren = $_sdd74y3jd09f08a.children(deep$1(original));
    $_9afv6jy6jd09f08m.append(nu, cloneChildren);
    return nu;
  };
  var mutate = function (original, tag) {
    var nu = shallowAs(original, tag);
    $_3dpbyky2jd09f081.before(original, nu);
    var children = $_sdd74y3jd09f08a.children(original);
    $_9afv6jy6jd09f08m.append(nu, children);
    $_72wxi1y5jd09f08k.remove(original);
    return nu;
  };
  var $_x1ewzydjd09f099 = {
    shallow: shallow$1,
    shallowAs: shallowAs,
    deep: deep$1,
    copy: copy,
    mutate: mutate
  };

  var getHtml = function (element) {
    var clone = $_x1ewzydjd09f099.shallow(element);
    return $_ck4yooybjd09f095.getOuter(clone);
  };
  var $_dxtpmiyajd09f092 = { getHtml: getHtml };

  var element = function (elem) {
    return $_dxtpmiyajd09f092.getHtml(elem);
  };
  var $_9zn22gy9jd09f091 = { element: element };

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
    return $_8nwhzlwajd09f01m.none();
  };
  var liftN = function (arr, f) {
    var r = [];
    for (var i = 0; i < arr.length; i++) {
      var x = arr[i];
      if (x.isSome()) {
        r.push(x.getOrDie());
      } else {
        return $_8nwhzlwajd09f01m.none();
      }
    }
    return $_8nwhzlwajd09f01m.some(f.apply(null, r));
  };
  var $_851ithyejd09f09c = {
    cat: cat,
    findMap: findMap,
    liftN: liftN
  };

  var unknown$3 = 'unknown';
  var debugging = true;
  var CHROME_INSPECTOR_GLOBAL = '__CHROME_INSPECTOR_CONNECTION_TO_ALLOY__';
  var eventsMonitored = [];
  var path$1 = [
    'alloy/data/Fields',
    'alloy/debugging/Debugging'
  ];
  var getTrace = function () {
    if (debugging === false)
      return unknown$3;
    var err = new Error();
    if (err.stack !== undefined) {
      var lines = err.stack.split('\n');
      return $_bvikd2w9jd09f01c.find(lines, function (line) {
        return line.indexOf('alloy') > 0 && !$_bvikd2w9jd09f01c.exists(path$1, function (p) {
          return line.indexOf(p) > -1;
        });
      }).getOr(unknown$3);
    } else {
      return unknown$3;
    }
  };
  var logHandler = function (label, handlerName, trace) {
  };
  var ignoreEvent = {
    logEventCut: $_8z5eqrwbjd09f01q.noop,
    logEventStopped: $_8z5eqrwbjd09f01q.noop,
    logNoParent: $_8z5eqrwbjd09f01q.noop,
    logEventNoHandlers: $_8z5eqrwbjd09f01q.noop,
    logEventResponse: $_8z5eqrwbjd09f01q.noop,
    write: $_8z5eqrwbjd09f01q.noop
  };
  var monitorEvent = function (eventName, initialTarget, f) {
    var logger = debugging && (eventsMonitored === '*' || $_bvikd2w9jd09f01c.contains(eventsMonitored, eventName)) ? function () {
      var sequence = [];
      return {
        logEventCut: function (name, target, purpose) {
          sequence.push({
            outcome: 'cut',
            target: target,
            purpose: purpose
          });
        },
        logEventStopped: function (name, target, purpose) {
          sequence.push({
            outcome: 'stopped',
            target: target,
            purpose: purpose
          });
        },
        logNoParent: function (name, target, purpose) {
          sequence.push({
            outcome: 'no-parent',
            target: target,
            purpose: purpose
          });
        },
        logEventNoHandlers: function (name, target) {
          sequence.push({
            outcome: 'no-handlers-left',
            target: target
          });
        },
        logEventResponse: function (name, target, purpose) {
          sequence.push({
            outcome: 'response',
            purpose: purpose,
            target: target
          });
        },
        write: function () {
          if ($_bvikd2w9jd09f01c.contains([
              'mousemove',
              'mouseover',
              'mouseout',
              $_5iytewwjd09f03c.systemInit()
            ], eventName))
            return;
          console.log(eventName, {
            event: eventName,
            target: initialTarget.dom(),
            sequence: $_bvikd2w9jd09f01c.map(sequence, function (s) {
              if (!$_bvikd2w9jd09f01c.contains([
                  'cut',
                  'stopped',
                  'response'
                ], s.outcome))
                return s.outcome;
              else
                return '{' + s.purpose + '} ' + s.outcome + ' at (' + $_9zn22gy9jd09f091.element(s.target) + ')';
            })
          });
        }
      };
    }() : ignoreEvent;
    var output = f(logger);
    logger.write();
    return output;
  };
  var inspectorInfo = function (comp) {
    var go = function (c) {
      var cSpec = c.spec();
      return {
        '(original.spec)': cSpec,
        '(dom.ref)': c.element().dom(),
        '(element)': $_9zn22gy9jd09f091.element(c.element()),
        '(initComponents)': $_bvikd2w9jd09f01c.map(cSpec.components !== undefined ? cSpec.components : [], go),
        '(components)': $_bvikd2w9jd09f01c.map(c.components(), go),
        '(bound.events)': $_32a0zdx0jd09f03l.mapToArray(c.events(), function (v, k) {
          return [k];
        }).join(', '),
        '(behaviours)': cSpec.behaviours !== undefined ? $_32a0zdx0jd09f03l.map(cSpec.behaviours, function (v, k) {
          return v === undefined ? '--revoked--' : {
            config: v.configAsRaw(),
            'original-config': v.initialConfig,
            state: c.readState(k)
          };
        }) : 'none'
      };
    };
    return go(comp);
  };
  var getOrInitConnection = function () {
    if (window[CHROME_INSPECTOR_GLOBAL] !== undefined)
      return window[CHROME_INSPECTOR_GLOBAL];
    else {
      window[CHROME_INSPECTOR_GLOBAL] = {
        systems: {},
        lookup: function (uid) {
          var systems = window[CHROME_INSPECTOR_GLOBAL].systems;
          var connections = $_32a0zdx0jd09f03l.keys(systems);
          return $_851ithyejd09f09c.findMap(connections, function (conn) {
            var connGui = systems[conn];
            return connGui.getByUid(uid).toOption().map(function (comp) {
              return $_q0etsx6jd09f053.wrap($_9zn22gy9jd09f091.element(comp.element()), inspectorInfo(comp));
            });
          });
        }
      };
      return window[CHROME_INSPECTOR_GLOBAL];
    }
  };
  var registerInspector = function (name, gui) {
    var connection = getOrInitConnection();
    connection.systems[name] = gui;
  };
  var $_fgkb9dy8jd09f08t = {
    logHandler: logHandler,
    noLogger: $_8z5eqrwbjd09f01q.constant(ignoreEvent),
    getTrace: getTrace,
    monitorEvent: monitorEvent,
    isDebugging: $_8z5eqrwbjd09f01q.constant(debugging),
    registerInspector: registerInspector
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

  function ClosestOrAncestor (is, ancestor, scope, a, isRoot) {
    return is(scope, a) ? $_8nwhzlwajd09f01m.some(scope) : $_4biushwzjd09f03k.isFunction(isRoot) && isRoot(scope) ? $_8nwhzlwajd09f01m.none() : ancestor(scope, a, isRoot);
  }

  var first$1 = function (predicate) {
    return descendant($_7m8dypy7jd09f08o.body(), predicate);
  };
  var ancestor = function (scope, predicate, isRoot) {
    var element = scope.dom();
    var stop = $_4biushwzjd09f03k.isFunction(isRoot) ? isRoot : $_8z5eqrwbjd09f01q.constant(false);
    while (element.parentNode) {
      element = element.parentNode;
      var el = $_cnbf2uwtjd09f033.fromDom(element);
      if (predicate(el))
        return $_8nwhzlwajd09f01m.some(el);
      else if (stop(el))
        break;
    }
    return $_8nwhzlwajd09f01m.none();
  };
  var closest = function (scope, predicate, isRoot) {
    var is = function (scope) {
      return predicate(scope);
    };
    return ClosestOrAncestor(is, ancestor, scope, predicate, isRoot);
  };
  var sibling = function (scope, predicate) {
    var element = scope.dom();
    if (!element.parentNode)
      return $_8nwhzlwajd09f01m.none();
    return child$1($_cnbf2uwtjd09f033.fromDom(element.parentNode), function (x) {
      return !$_6s6cs1w8jd09f014.eq(scope, x) && predicate(x);
    });
  };
  var child$1 = function (scope, predicate) {
    var result = $_bvikd2w9jd09f01c.find(scope.dom().childNodes, $_8z5eqrwbjd09f01q.compose(predicate, $_cnbf2uwtjd09f033.fromDom));
    return result.map($_cnbf2uwtjd09f033.fromDom);
  };
  var descendant = function (scope, predicate) {
    var descend = function (element) {
      for (var i = 0; i < element.childNodes.length; i++) {
        if (predicate($_cnbf2uwtjd09f033.fromDom(element.childNodes[i])))
          return $_8nwhzlwajd09f01m.some($_cnbf2uwtjd09f033.fromDom(element.childNodes[i]));
        var res = descend(element.childNodes[i]);
        if (res.isSome())
          return res;
      }
      return $_8nwhzlwajd09f01m.none();
    };
    return descend(scope.dom());
  };
  var $_605gqayijd09f09k = {
    first: first$1,
    ancestor: ancestor,
    closest: closest,
    sibling: sibling,
    child: child$1,
    descendant: descendant
  };

  var any$1 = function (predicate) {
    return $_605gqayijd09f09k.first(predicate).isSome();
  };
  var ancestor$1 = function (scope, predicate, isRoot) {
    return $_605gqayijd09f09k.ancestor(scope, predicate, isRoot).isSome();
  };
  var closest$1 = function (scope, predicate, isRoot) {
    return $_605gqayijd09f09k.closest(scope, predicate, isRoot).isSome();
  };
  var sibling$1 = function (scope, predicate) {
    return $_605gqayijd09f09k.sibling(scope, predicate).isSome();
  };
  var child$2 = function (scope, predicate) {
    return $_605gqayijd09f09k.child(scope, predicate).isSome();
  };
  var descendant$1 = function (scope, predicate) {
    return $_605gqayijd09f09k.descendant(scope, predicate).isSome();
  };
  var $_g9nhb7yhjd09f09j = {
    any: any$1,
    ancestor: ancestor$1,
    closest: closest$1,
    sibling: sibling$1,
    child: child$2,
    descendant: descendant$1
  };

  var focus = function (element) {
    element.dom().focus();
  };
  var blur = function (element) {
    element.dom().blur();
  };
  var hasFocus = function (element) {
    var doc = $_sdd74y3jd09f08a.owner(element).dom();
    return element.dom() === doc.activeElement;
  };
  var active = function (_doc) {
    var doc = _doc !== undefined ? _doc.dom() : document;
    return $_8nwhzlwajd09f01m.from(doc.activeElement).map($_cnbf2uwtjd09f033.fromDom);
  };
  var focusInside = function (element) {
    var doc = $_sdd74y3jd09f08a.owner(element);
    var inside = active(doc).filter(function (a) {
      return $_g9nhb7yhjd09f09j.closest(a, $_8z5eqrwbjd09f01q.curry($_6s6cs1w8jd09f014.eq, element));
    });
    inside.fold(function () {
      focus(element);
    }, $_8z5eqrwbjd09f01q.noop);
  };
  var search = function (element) {
    return active($_sdd74y3jd09f08a.owner(element)).filter(function (e) {
      return element.dom().contains(e.dom());
    });
  };
  var $_ccs2jvygjd09f09f = {
    hasFocus: hasFocus,
    focus: focus,
    blur: blur,
    active: active,
    search: search,
    focusInside: focusInside
  };

  var ThemeManager = tinymce.util.Tools.resolve('tinymce.ThemeManager');

  var DOMUtils = tinymce.util.Tools.resolve('tinymce.dom.DOMUtils');

  var openLink = function (target) {
    var link = document.createElement('a');
    link.target = '_blank';
    link.href = target.href;
    link.rel = 'noreferrer noopener';
    var nuEvt = document.createEvent('MouseEvents');
    nuEvt.initMouseEvent('click', true, true, window, 0, 0, 0, 0, 0, false, false, false, false, 0, null);
    document.body.appendChild(link);
    link.dispatchEvent(nuEvt);
    document.body.removeChild(link);
  };
  var $_d5bc7symjd09f09w = { openLink: openLink };

  var isSkinDisabled = function (editor) {
    return editor.settings.skin === false;
  };
  var $_99bfffynjd09f09x = { isSkinDisabled: isSkinDisabled };

  var formatChanged = 'formatChanged';
  var orientationChanged = 'orientationChanged';
  var dropupDismissed = 'dropupDismissed';
  var $_b1cma0yojd09f09y = {
    formatChanged: $_8z5eqrwbjd09f01q.constant(formatChanged),
    orientationChanged: $_8z5eqrwbjd09f01q.constant(orientationChanged),
    dropupDismissed: $_8z5eqrwbjd09f01q.constant(dropupDismissed)
  };

  var chooseChannels = function (channels, message) {
    return message.universal() ? channels : $_bvikd2w9jd09f01c.filter(channels, function (ch) {
      return $_bvikd2w9jd09f01c.contains(message.channels(), ch);
    });
  };
  var events = function (receiveConfig) {
    return $_6j84lww6jd09f00y.derive([$_6j84lww6jd09f00y.run($_5iytewwjd09f03c.receive(), function (component, message) {
        var channelMap = receiveConfig.channels();
        var channels = $_32a0zdx0jd09f03l.keys(channelMap);
        var targetChannels = chooseChannels(channels, message);
        $_bvikd2w9jd09f01c.each(targetChannels, function (ch) {
          var channelInfo = channelMap[ch]();
          var channelSchema = channelInfo.schema();
          var data = $_33aoy2xhjd09f061.asStructOrDie('channel[' + ch + '] data\nReceiver: ' + $_9zn22gy9jd09f091.element(component.element()), channelSchema, message.data());
          channelInfo.onReceive()(component, data);
        });
      })]);
  };
  var $_e2q6xmyrjd09f0ai = { events: events };

  var menuFields = [
    $_1t7kykx2jd09f043.strict('menu'),
    $_1t7kykx2jd09f043.strict('selectedMenu')
  ];
  var itemFields = [
    $_1t7kykx2jd09f043.strict('item'),
    $_1t7kykx2jd09f043.strict('selectedItem')
  ];
  var schema = $_33aoy2xhjd09f061.objOfOnly(itemFields.concat(menuFields));
  var itemSchema = $_33aoy2xhjd09f061.objOfOnly(itemFields);
  var $_cns83syujd09f0b1 = {
    menuFields: $_8z5eqrwbjd09f01q.constant(menuFields),
    itemFields: $_8z5eqrwbjd09f01q.constant(itemFields),
    schema: $_8z5eqrwbjd09f01q.constant(schema),
    itemSchema: $_8z5eqrwbjd09f01q.constant(itemSchema)
  };

  var initSize = $_1t7kykx2jd09f043.strictObjOf('initSize', [
    $_1t7kykx2jd09f043.strict('numColumns'),
    $_1t7kykx2jd09f043.strict('numRows')
  ]);
  var itemMarkers = function () {
    return $_1t7kykx2jd09f043.strictOf('markers', $_cns83syujd09f0b1.itemSchema());
  };
  var menuMarkers = function () {
    return $_1t7kykx2jd09f043.strictOf('markers', $_cns83syujd09f0b1.schema());
  };
  var tieredMenuMarkers = function () {
    return $_1t7kykx2jd09f043.strictObjOf('markers', [$_1t7kykx2jd09f043.strict('backgroundMenu')].concat($_cns83syujd09f0b1.menuFields()).concat($_cns83syujd09f0b1.itemFields()));
  };
  var markers = function (required) {
    return $_1t7kykx2jd09f043.strictObjOf('markers', $_bvikd2w9jd09f01c.map(required, $_1t7kykx2jd09f043.strict));
  };
  var onPresenceHandler = function (label, fieldName, presence) {
    var trace = $_fgkb9dy8jd09f08t.getTrace();
    return $_1t7kykx2jd09f043.field(fieldName, fieldName, presence, $_33aoy2xhjd09f061.valueOf(function (f) {
      return $_anstx7x8jd09f05b.value(function () {
        $_fgkb9dy8jd09f08t.logHandler(label, fieldName, trace);
        return f.apply(undefined, arguments);
      });
    }));
  };
  var onHandler = function (fieldName) {
    return onPresenceHandler('onHandler', fieldName, $_buoc30x3jd09f047.defaulted($_8z5eqrwbjd09f01q.noop));
  };
  var onKeyboardHandler = function (fieldName) {
    return onPresenceHandler('onKeyboardHandler', fieldName, $_buoc30x3jd09f047.defaulted($_8nwhzlwajd09f01m.none));
  };
  var onStrictHandler = function (fieldName) {
    return onPresenceHandler('onHandler', fieldName, $_buoc30x3jd09f047.strict());
  };
  var onStrictKeyboardHandler = function (fieldName) {
    return onPresenceHandler('onKeyboardHandler', fieldName, $_buoc30x3jd09f047.strict());
  };
  var output$1 = function (name, value) {
    return $_1t7kykx2jd09f043.state(name, $_8z5eqrwbjd09f01q.constant(value));
  };
  var snapshot$1 = function (name) {
    return $_1t7kykx2jd09f043.state(name, $_8z5eqrwbjd09f01q.identity);
  };
  var $_1l599yytjd09f0ar = {
    initSize: $_8z5eqrwbjd09f01q.constant(initSize),
    itemMarkers: itemMarkers,
    menuMarkers: menuMarkers,
    tieredMenuMarkers: tieredMenuMarkers,
    markers: markers,
    onHandler: onHandler,
    onKeyboardHandler: onKeyboardHandler,
    onStrictHandler: onStrictHandler,
    onStrictKeyboardHandler: onStrictKeyboardHandler,
    output: output$1,
    snapshot: snapshot$1
  };

  var ReceivingSchema = [$_1t7kykx2jd09f043.strictOf('channels', $_33aoy2xhjd09f061.setOf($_anstx7x8jd09f05b.value, $_33aoy2xhjd09f061.objOfOnly([
      $_1l599yytjd09f0ar.onStrictHandler('onReceive'),
      $_1t7kykx2jd09f043.defaulted('schema', $_33aoy2xhjd09f061.anyValue())
    ])))];

  var Receiving = $_fq8al5w4jd09f00e.create({
    fields: ReceivingSchema,
    name: 'receiving',
    active: $_e2q6xmyrjd09f0ai
  });

  var updateAriaState = function (component, toggleConfig) {
    var pressed = isOn(component, toggleConfig);
    var ariaInfo = toggleConfig.aria();
    ariaInfo.update()(component, ariaInfo, pressed);
  };
  var toggle$2 = function (component, toggleConfig, toggleState) {
    $_e7d6ttxujd09f07b.toggle(component.element(), toggleConfig.toggleClass());
    updateAriaState(component, toggleConfig);
  };
  var on = function (component, toggleConfig, toggleState) {
    $_e7d6ttxujd09f07b.add(component.element(), toggleConfig.toggleClass());
    updateAriaState(component, toggleConfig);
  };
  var off = function (component, toggleConfig, toggleState) {
    $_e7d6ttxujd09f07b.remove(component.element(), toggleConfig.toggleClass());
    updateAriaState(component, toggleConfig);
  };
  var isOn = function (component, toggleConfig) {
    return $_e7d6ttxujd09f07b.has(component.element(), toggleConfig.toggleClass());
  };
  var onLoad = function (component, toggleConfig, toggleState) {
    var api = toggleConfig.selected() ? on : off;
    api(component, toggleConfig, toggleState);
  };
  var $_a3e7iyyxjd09f0b9 = {
    onLoad: onLoad,
    toggle: toggle$2,
    isOn: isOn,
    on: on,
    off: off
  };

  var exhibit = function (base, toggleConfig, toggleState) {
    return $_brhg4pxkjd09f06j.nu({});
  };
  var events$1 = function (toggleConfig, toggleState) {
    var execute = $_8zny04w5jd09f00m.executeEvent(toggleConfig, toggleState, $_a3e7iyyxjd09f0b9.toggle);
    var load = $_8zny04w5jd09f00m.loadEvent(toggleConfig, toggleState, $_a3e7iyyxjd09f0b9.onLoad);
    return $_6j84lww6jd09f00y.derive($_bvikd2w9jd09f01c.flatten([
      toggleConfig.toggleOnExecute() ? [execute] : [],
      [load]
    ]));
  };
  var $_wbne2ywjd09f0b7 = {
    exhibit: exhibit,
    events: events$1
  };

  var updatePressed = function (component, ariaInfo, status) {
    $_6spjcmxwjd09f07e.set(component.element(), 'aria-pressed', status);
    if (ariaInfo.syncWithExpanded())
      updateExpanded(component, ariaInfo, status);
  };
  var updateSelected = function (component, ariaInfo, status) {
    $_6spjcmxwjd09f07e.set(component.element(), 'aria-selected', status);
  };
  var updateChecked = function (component, ariaInfo, status) {
    $_6spjcmxwjd09f07e.set(component.element(), 'aria-checked', status);
  };
  var updateExpanded = function (component, ariaInfo, status) {
    $_6spjcmxwjd09f07e.set(component.element(), 'aria-expanded', status);
  };
  var tagAttributes = {
    button: ['aria-pressed'],
    'input:checkbox': ['aria-checked']
  };
  var roleAttributes = {
    'button': ['aria-pressed'],
    'listbox': [
      'aria-pressed',
      'aria-expanded'
    ],
    'menuitemcheckbox': ['aria-checked']
  };
  var detectFromTag = function (component) {
    var elem = component.element();
    var rawTag = $_arrpm2xxjd09f07k.name(elem);
    var suffix = rawTag === 'input' && $_6spjcmxwjd09f07e.has(elem, 'type') ? ':' + $_6spjcmxwjd09f07e.get(elem, 'type') : '';
    return $_q0etsx6jd09f053.readOptFrom(tagAttributes, rawTag + suffix);
  };
  var detectFromRole = function (component) {
    var elem = component.element();
    if (!$_6spjcmxwjd09f07e.has(elem, 'role'))
      return $_8nwhzlwajd09f01m.none();
    else {
      var role = $_6spjcmxwjd09f07e.get(elem, 'role');
      return $_q0etsx6jd09f053.readOptFrom(roleAttributes, role);
    }
  };
  var updateAuto = function (component, ariaInfo, status) {
    var attributes = detectFromRole(component).orThunk(function () {
      return detectFromTag(component);
    }).getOr([]);
    $_bvikd2w9jd09f01c.each(attributes, function (attr) {
      $_6spjcmxwjd09f07e.set(component.element(), attr, status);
    });
  };
  var $_c0yf33yzjd09f0bh = {
    updatePressed: updatePressed,
    updateSelected: updateSelected,
    updateChecked: updateChecked,
    updateExpanded: updateExpanded,
    updateAuto: updateAuto
  };

  var ToggleSchema = [
    $_1t7kykx2jd09f043.defaulted('selected', false),
    $_1t7kykx2jd09f043.strict('toggleClass'),
    $_1t7kykx2jd09f043.defaulted('toggleOnExecute', true),
    $_1t7kykx2jd09f043.defaultedOf('aria', { mode: 'none' }, $_33aoy2xhjd09f061.choose('mode', {
      'pressed': [
        $_1t7kykx2jd09f043.defaulted('syncWithExpanded', false),
        $_1l599yytjd09f0ar.output('update', $_c0yf33yzjd09f0bh.updatePressed)
      ],
      'checked': [$_1l599yytjd09f0ar.output('update', $_c0yf33yzjd09f0bh.updateChecked)],
      'expanded': [$_1l599yytjd09f0ar.output('update', $_c0yf33yzjd09f0bh.updateExpanded)],
      'selected': [$_1l599yytjd09f0ar.output('update', $_c0yf33yzjd09f0bh.updateSelected)],
      'none': [$_1l599yytjd09f0ar.output('update', $_8z5eqrwbjd09f01q.noop)]
    }))
  ];

  var Toggling = $_fq8al5w4jd09f00e.create({
    fields: ToggleSchema,
    name: 'toggling',
    active: $_wbne2ywjd09f0b7,
    apis: $_a3e7iyyxjd09f0b9
  });

  var format = function (command, update) {
    return Receiving.config({
      channels: $_q0etsx6jd09f053.wrap($_b1cma0yojd09f09y.formatChanged(), {
        onReceive: function (button, data) {
          if (data.command === command) {
            update(button, data.state);
          }
        }
      })
    });
  };
  var orientation = function (onReceive) {
    return Receiving.config({ channels: $_q0etsx6jd09f053.wrap($_b1cma0yojd09f09y.orientationChanged(), { onReceive: onReceive }) });
  };
  var receive = function (channel, onReceive) {
    return {
      key: channel,
      value: { onReceive: onReceive }
    };
  };
  var $_2jq3vfz0jd09f0bp = {
    format: format,
    orientation: orientation,
    receive: receive
  };

  var prefix = 'tinymce-mobile';
  var resolve$1 = function (p) {
    return prefix + '-' + p;
  };
  var $_3584u7z1jd09f0bs = {
    resolve: resolve$1,
    prefix: $_8z5eqrwbjd09f01q.constant(prefix)
  };

  var exhibit$1 = function (base, unselectConfig) {
    return $_brhg4pxkjd09f06j.nu({
      styles: {
        '-webkit-user-select': 'none',
        'user-select': 'none',
        '-ms-user-select': 'none',
        '-moz-user-select': '-moz-none'
      },
      attributes: { 'unselectable': 'on' }
    });
  };
  var events$2 = function (unselectConfig) {
    return $_6j84lww6jd09f00y.derive([$_6j84lww6jd09f00y.abort($_8nqyjjwxjd09f03f.selectstart(), $_8z5eqrwbjd09f01q.constant(true))]);
  };
  var $_cfij4jz4jd09f0c6 = {
    events: events$2,
    exhibit: exhibit$1
  };

  var Unselecting = $_fq8al5w4jd09f00e.create({
    fields: [],
    name: 'unselecting',
    active: $_cfij4jz4jd09f0c6
  });

  var focus$1 = function (component, focusConfig) {
    if (!focusConfig.ignore()) {
      $_ccs2jvygjd09f09f.focus(component.element());
      focusConfig.onFocus()(component);
    }
  };
  var blur$1 = function (component, focusConfig) {
    if (!focusConfig.ignore()) {
      $_ccs2jvygjd09f09f.blur(component.element());
    }
  };
  var isFocused = function (component) {
    return $_ccs2jvygjd09f09f.hasFocus(component.element());
  };
  var $_f73fehz8jd09f0ch = {
    focus: focus$1,
    blur: blur$1,
    isFocused: isFocused
  };

  var exhibit$2 = function (base, focusConfig) {
    if (focusConfig.ignore())
      return $_brhg4pxkjd09f06j.nu({});
    else
      return $_brhg4pxkjd09f06j.nu({ attributes: { 'tabindex': '-1' } });
  };
  var events$3 = function (focusConfig) {
    return $_6j84lww6jd09f00y.derive([$_6j84lww6jd09f00y.run($_5iytewwjd09f03c.focus(), function (component, simulatedEvent) {
        $_f73fehz8jd09f0ch.focus(component, focusConfig);
        simulatedEvent.stop();
      })]);
  };
  var $_ciswvlz7jd09f0cf = {
    exhibit: exhibit$2,
    events: events$3
  };

  var FocusSchema = [
    $_1l599yytjd09f0ar.onHandler('onFocus'),
    $_1t7kykx2jd09f043.defaulted('ignore', false)
  ];

  var Focusing = $_fq8al5w4jd09f00e.create({
    fields: FocusSchema,
    name: 'focusing',
    active: $_ciswvlz7jd09f0cf,
    apis: $_f73fehz8jd09f0ch
  });

  var $_xd6lgzejd09f0d6 = {
    BACKSPACE: $_8z5eqrwbjd09f01q.constant([8]),
    TAB: $_8z5eqrwbjd09f01q.constant([9]),
    ENTER: $_8z5eqrwbjd09f01q.constant([13]),
    SHIFT: $_8z5eqrwbjd09f01q.constant([16]),
    CTRL: $_8z5eqrwbjd09f01q.constant([17]),
    ALT: $_8z5eqrwbjd09f01q.constant([18]),
    CAPSLOCK: $_8z5eqrwbjd09f01q.constant([20]),
    ESCAPE: $_8z5eqrwbjd09f01q.constant([27]),
    SPACE: $_8z5eqrwbjd09f01q.constant([32]),
    PAGEUP: $_8z5eqrwbjd09f01q.constant([33]),
    PAGEDOWN: $_8z5eqrwbjd09f01q.constant([34]),
    END: $_8z5eqrwbjd09f01q.constant([35]),
    HOME: $_8z5eqrwbjd09f01q.constant([36]),
    LEFT: $_8z5eqrwbjd09f01q.constant([37]),
    UP: $_8z5eqrwbjd09f01q.constant([38]),
    RIGHT: $_8z5eqrwbjd09f01q.constant([39]),
    DOWN: $_8z5eqrwbjd09f01q.constant([40]),
    INSERT: $_8z5eqrwbjd09f01q.constant([45]),
    DEL: $_8z5eqrwbjd09f01q.constant([46]),
    META: $_8z5eqrwbjd09f01q.constant([
      91,
      93,
      224
    ]),
    F10: $_8z5eqrwbjd09f01q.constant([121])
  };

  var cycleBy = function (value, delta, min, max) {
    var r = value + delta;
    if (r > max)
      return min;
    else
      return r < min ? max : r;
  };
  var cap = function (value, min, max) {
    if (value <= min)
      return min;
    else
      return value >= max ? max : value;
  };
  var $_6ufqj2zjjd09f0dy = {
    cycleBy: cycleBy,
    cap: cap
  };

  var all$2 = function (predicate) {
    return descendants($_7m8dypy7jd09f08o.body(), predicate);
  };
  var ancestors = function (scope, predicate, isRoot) {
    return $_bvikd2w9jd09f01c.filter($_sdd74y3jd09f08a.parents(scope, isRoot), predicate);
  };
  var siblings$1 = function (scope, predicate) {
    return $_bvikd2w9jd09f01c.filter($_sdd74y3jd09f08a.siblings(scope), predicate);
  };
  var children$1 = function (scope, predicate) {
    return $_bvikd2w9jd09f01c.filter($_sdd74y3jd09f08a.children(scope), predicate);
  };
  var descendants = function (scope, predicate) {
    var result = [];
    $_bvikd2w9jd09f01c.each($_sdd74y3jd09f08a.children(scope), function (x) {
      if (predicate(x)) {
        result = result.concat([x]);
      }
      result = result.concat(descendants(x, predicate));
    });
    return result;
  };
  var $_6u79yrzljd09f0e1 = {
    all: all$2,
    ancestors: ancestors,
    siblings: siblings$1,
    children: children$1,
    descendants: descendants
  };

  var all$3 = function (selector) {
    return $_5mto6swsjd09f02z.all(selector);
  };
  var ancestors$1 = function (scope, selector, isRoot) {
    return $_6u79yrzljd09f0e1.ancestors(scope, function (e) {
      return $_5mto6swsjd09f02z.is(e, selector);
    }, isRoot);
  };
  var siblings$2 = function (scope, selector) {
    return $_6u79yrzljd09f0e1.siblings(scope, function (e) {
      return $_5mto6swsjd09f02z.is(e, selector);
    });
  };
  var children$2 = function (scope, selector) {
    return $_6u79yrzljd09f0e1.children(scope, function (e) {
      return $_5mto6swsjd09f02z.is(e, selector);
    });
  };
  var descendants$1 = function (scope, selector) {
    return $_5mto6swsjd09f02z.all(selector, scope);
  };
  var $_23mimrzkjd09f0dz = {
    all: all$3,
    ancestors: ancestors$1,
    siblings: siblings$2,
    children: children$2,
    descendants: descendants$1
  };

  var first$2 = function (selector) {
    return $_5mto6swsjd09f02z.one(selector);
  };
  var ancestor$2 = function (scope, selector, isRoot) {
    return $_605gqayijd09f09k.ancestor(scope, function (e) {
      return $_5mto6swsjd09f02z.is(e, selector);
    }, isRoot);
  };
  var sibling$2 = function (scope, selector) {
    return $_605gqayijd09f09k.sibling(scope, function (e) {
      return $_5mto6swsjd09f02z.is(e, selector);
    });
  };
  var child$3 = function (scope, selector) {
    return $_605gqayijd09f09k.child(scope, function (e) {
      return $_5mto6swsjd09f02z.is(e, selector);
    });
  };
  var descendant$2 = function (scope, selector) {
    return $_5mto6swsjd09f02z.one(selector, scope);
  };
  var closest$2 = function (scope, selector, isRoot) {
    return ClosestOrAncestor($_5mto6swsjd09f02z.is, ancestor$2, scope, selector, isRoot);
  };
  var $_74vb1xzmjd09f0e3 = {
    first: first$2,
    ancestor: ancestor$2,
    sibling: sibling$2,
    child: child$3,
    descendant: descendant$2,
    closest: closest$2
  };

  var dehighlightAll = function (component, hConfig, hState) {
    var highlighted = $_23mimrzkjd09f0dz.descendants(component.element(), '.' + hConfig.highlightClass());
    $_bvikd2w9jd09f01c.each(highlighted, function (h) {
      $_e7d6ttxujd09f07b.remove(h, hConfig.highlightClass());
      component.getSystem().getByDom(h).each(function (target) {
        hConfig.onDehighlight()(component, target);
      });
    });
  };
  var dehighlight = function (component, hConfig, hState, target) {
    var wasHighlighted = isHighlighted(component, hConfig, hState, target);
    $_e7d6ttxujd09f07b.remove(target.element(), hConfig.highlightClass());
    if (wasHighlighted)
      hConfig.onDehighlight()(component, target);
  };
  var highlight = function (component, hConfig, hState, target) {
    var wasHighlighted = isHighlighted(component, hConfig, hState, target);
    dehighlightAll(component, hConfig, hState);
    $_e7d6ttxujd09f07b.add(target.element(), hConfig.highlightClass());
    if (!wasHighlighted)
      hConfig.onHighlight()(component, target);
  };
  var highlightFirst = function (component, hConfig, hState) {
    getFirst(component, hConfig, hState).each(function (firstComp) {
      highlight(component, hConfig, hState, firstComp);
    });
  };
  var highlightLast = function (component, hConfig, hState) {
    getLast(component, hConfig, hState).each(function (lastComp) {
      highlight(component, hConfig, hState, lastComp);
    });
  };
  var highlightAt = function (component, hConfig, hState, index) {
    getByIndex(component, hConfig, hState, index).fold(function (err) {
      throw new Error(err);
    }, function (firstComp) {
      highlight(component, hConfig, hState, firstComp);
    });
  };
  var highlightBy = function (component, hConfig, hState, predicate) {
    var items = $_23mimrzkjd09f0dz.descendants(component.element(), '.' + hConfig.itemClass());
    var itemComps = $_851ithyejd09f09c.cat($_bvikd2w9jd09f01c.map(items, function (i) {
      return component.getSystem().getByDom(i).toOption();
    }));
    var targetComp = $_bvikd2w9jd09f01c.find(itemComps, predicate);
    targetComp.each(function (c) {
      highlight(component, hConfig, hState, c);
    });
  };
  var isHighlighted = function (component, hConfig, hState, queryTarget) {
    return $_e7d6ttxujd09f07b.has(queryTarget.element(), hConfig.highlightClass());
  };
  var getHighlighted = function (component, hConfig, hState) {
    return $_74vb1xzmjd09f0e3.descendant(component.element(), '.' + hConfig.highlightClass()).bind(component.getSystem().getByDom);
  };
  var getByIndex = function (component, hConfig, hState, index) {
    var items = $_23mimrzkjd09f0dz.descendants(component.element(), '.' + hConfig.itemClass());
    return $_8nwhzlwajd09f01m.from(items[index]).fold(function () {
      return $_anstx7x8jd09f05b.error('No element found with index ' + index);
    }, component.getSystem().getByDom);
  };
  var getFirst = function (component, hConfig, hState) {
    return $_74vb1xzmjd09f0e3.descendant(component.element(), '.' + hConfig.itemClass()).bind(component.getSystem().getByDom);
  };
  var getLast = function (component, hConfig, hState) {
    var items = $_23mimrzkjd09f0dz.descendants(component.element(), '.' + hConfig.itemClass());
    var last = items.length > 0 ? $_8nwhzlwajd09f01m.some(items[items.length - 1]) : $_8nwhzlwajd09f01m.none();
    return last.bind(component.getSystem().getByDom);
  };
  var getDelta = function (component, hConfig, hState, delta) {
    var items = $_23mimrzkjd09f0dz.descendants(component.element(), '.' + hConfig.itemClass());
    var current = $_bvikd2w9jd09f01c.findIndex(items, function (item) {
      return $_e7d6ttxujd09f07b.has(item, hConfig.highlightClass());
    });
    return current.bind(function (selected) {
      var dest = $_6ufqj2zjjd09f0dy.cycleBy(selected, delta, 0, items.length - 1);
      return component.getSystem().getByDom(items[dest]);
    });
  };
  var getPrevious = function (component, hConfig, hState) {
    return getDelta(component, hConfig, hState, -1);
  };
  var getNext = function (component, hConfig, hState) {
    return getDelta(component, hConfig, hState, +1);
  };
  var $_9njmxbzijd09f0di = {
    dehighlightAll: dehighlightAll,
    dehighlight: dehighlight,
    highlight: highlight,
    highlightFirst: highlightFirst,
    highlightLast: highlightLast,
    highlightAt: highlightAt,
    highlightBy: highlightBy,
    isHighlighted: isHighlighted,
    getHighlighted: getHighlighted,
    getFirst: getFirst,
    getLast: getLast,
    getPrevious: getPrevious,
    getNext: getNext
  };

  var HighlightSchema = [
    $_1t7kykx2jd09f043.strict('highlightClass'),
    $_1t7kykx2jd09f043.strict('itemClass'),
    $_1l599yytjd09f0ar.onHandler('onHighlight'),
    $_1l599yytjd09f0ar.onHandler('onDehighlight')
  ];

  var Highlighting = $_fq8al5w4jd09f00e.create({
    fields: HighlightSchema,
    name: 'highlighting',
    apis: $_9njmxbzijd09f0di
  });

  var dom = function () {
    var get = function (component) {
      return $_ccs2jvygjd09f09f.search(component.element());
    };
    var set = function (component, focusee) {
      component.getSystem().triggerFocus(focusee, component.element());
    };
    return {
      get: get,
      set: set
    };
  };
  var highlights = function () {
    var get = function (component) {
      return Highlighting.getHighlighted(component).map(function (item) {
        return item.element();
      });
    };
    var set = function (component, element) {
      component.getSystem().getByDom(element).fold($_8z5eqrwbjd09f01q.noop, function (item) {
        Highlighting.highlight(component, item);
      });
    };
    return {
      get: get,
      set: set
    };
  };
  var $_axg0l9zgjd09f0dd = {
    dom: dom,
    highlights: highlights
  };

  var inSet = function (keys) {
    return function (event) {
      return $_bvikd2w9jd09f01c.contains(keys, event.raw().which);
    };
  };
  var and = function (preds) {
    return function (event) {
      return $_bvikd2w9jd09f01c.forall(preds, function (pred) {
        return pred(event);
      });
    };
  };
  var is$1 = function (key) {
    return function (event) {
      return event.raw().which === key;
    };
  };
  var isShift = function (event) {
    return event.raw().shiftKey === true;
  };
  var isControl = function (event) {
    return event.raw().ctrlKey === true;
  };
  var $_cmpov0zpjd09f0ea = {
    inSet: inSet,
    and: and,
    is: is$1,
    isShift: isShift,
    isNotShift: $_8z5eqrwbjd09f01q.not(isShift),
    isControl: isControl,
    isNotControl: $_8z5eqrwbjd09f01q.not(isControl)
  };

  var basic = function (key, action) {
    return {
      matches: $_cmpov0zpjd09f0ea.is(key),
      classification: action
    };
  };
  var rule = function (matches, action) {
    return {
      matches: matches,
      classification: action
    };
  };
  var choose$2 = function (transitions, event) {
    var transition = $_bvikd2w9jd09f01c.find(transitions, function (t) {
      return t.matches(event);
    });
    return transition.map(function (t) {
      return t.classification;
    });
  };
  var $_651xtzzojd09f0e8 = {
    basic: basic,
    rule: rule,
    choose: choose$2
  };

  var typical = function (infoSchema, stateInit, getRules, getEvents, getApis, optFocusIn) {
    var schema = function () {
      return infoSchema.concat([
        $_1t7kykx2jd09f043.defaulted('focusManager', $_axg0l9zgjd09f0dd.dom()),
        $_1l599yytjd09f0ar.output('handler', me),
        $_1l599yytjd09f0ar.output('state', stateInit)
      ]);
    };
    var processKey = function (component, simulatedEvent, keyingConfig, keyingState) {
      var rules = getRules(component, simulatedEvent, keyingConfig, keyingState);
      return $_651xtzzojd09f0e8.choose(rules, simulatedEvent.event()).bind(function (rule) {
        return rule(component, simulatedEvent, keyingConfig, keyingState);
      });
    };
    var toEvents = function (keyingConfig, keyingState) {
      var otherEvents = getEvents(keyingConfig, keyingState);
      var keyEvents = $_6j84lww6jd09f00y.derive(optFocusIn.map(function (focusIn) {
        return $_6j84lww6jd09f00y.run($_5iytewwjd09f03c.focus(), function (component, simulatedEvent) {
          focusIn(component, keyingConfig, keyingState, simulatedEvent);
          simulatedEvent.stop();
        });
      }).toArray().concat([$_6j84lww6jd09f00y.run($_8nqyjjwxjd09f03f.keydown(), function (component, simulatedEvent) {
          processKey(component, simulatedEvent, keyingConfig, keyingState).each(function (_) {
            simulatedEvent.stop();
          });
        })]));
      return $_3htayhwyjd09f03i.deepMerge(otherEvents, keyEvents);
    };
    var me = {
      schema: schema,
      processKey: processKey,
      toEvents: toEvents,
      toApis: getApis
    };
    return me;
  };
  var $_5arm0mzfjd09f0d8 = { typical: typical };

  var cyclePrev = function (values, index, predicate) {
    var before = $_bvikd2w9jd09f01c.reverse(values.slice(0, index));
    var after = $_bvikd2w9jd09f01c.reverse(values.slice(index + 1));
    return $_bvikd2w9jd09f01c.find(before.concat(after), predicate);
  };
  var tryPrev = function (values, index, predicate) {
    var before = $_bvikd2w9jd09f01c.reverse(values.slice(0, index));
    return $_bvikd2w9jd09f01c.find(before, predicate);
  };
  var cycleNext = function (values, index, predicate) {
    var before = values.slice(0, index);
    var after = values.slice(index + 1);
    return $_bvikd2w9jd09f01c.find(after.concat(before), predicate);
  };
  var tryNext = function (values, index, predicate) {
    var after = values.slice(index + 1);
    return $_bvikd2w9jd09f01c.find(after, predicate);
  };
  var $_8iir6ezqjd09f0ee = {
    cyclePrev: cyclePrev,
    cycleNext: cycleNext,
    tryPrev: tryPrev,
    tryNext: tryNext
  };

  var isSupported = function (dom) {
    return dom.style !== undefined;
  };
  var $_45h4qvztjd09f0es = { isSupported: isSupported };

  var internalSet = function (dom, property, value) {
    if (!$_4biushwzjd09f03k.isString(value)) {
      console.error('Invalid call to CSS.set. Property ', property, ':: Value ', value, ':: Element ', dom);
      throw new Error('CSS value must be a string: ' + value);
    }
    if ($_45h4qvztjd09f0es.isSupported(dom))
      dom.style.setProperty(property, value);
  };
  var internalRemove = function (dom, property) {
    if ($_45h4qvztjd09f0es.isSupported(dom))
      dom.style.removeProperty(property);
  };
  var set$2 = function (element, property, value) {
    var dom = element.dom();
    internalSet(dom, property, value);
  };
  var setAll$1 = function (element, css) {
    var dom = element.dom();
    $_32a0zdx0jd09f03l.each(css, function (v, k) {
      internalSet(dom, k, v);
    });
  };
  var setOptions = function (element, css) {
    var dom = element.dom();
    $_32a0zdx0jd09f03l.each(css, function (v, k) {
      v.fold(function () {
        internalRemove(dom, k);
      }, function (value) {
        internalSet(dom, k, value);
      });
    });
  };
  var get$3 = function (element, property) {
    var dom = element.dom();
    var styles = window.getComputedStyle(dom);
    var r = styles.getPropertyValue(property);
    var v = r === '' && !$_7m8dypy7jd09f08o.inBody(element) ? getUnsafeProperty(dom, property) : r;
    return v === null ? undefined : v;
  };
  var getUnsafeProperty = function (dom, property) {
    return $_45h4qvztjd09f0es.isSupported(dom) ? dom.style.getPropertyValue(property) : '';
  };
  var getRaw = function (element, property) {
    var dom = element.dom();
    var raw = getUnsafeProperty(dom, property);
    return $_8nwhzlwajd09f01m.from(raw).filter(function (r) {
      return r.length > 0;
    });
  };
  var getAllRaw = function (element) {
    var css = {};
    var dom = element.dom();
    if ($_45h4qvztjd09f0es.isSupported(dom)) {
      for (var i = 0; i < dom.style.length; i++) {
        var ruleName = dom.style.item(i);
        css[ruleName] = dom.style[ruleName];
      }
    }
    return css;
  };
  var isValidValue = function (tag, property, value) {
    var element = $_cnbf2uwtjd09f033.fromTag(tag);
    set$2(element, property, value);
    var style = getRaw(element, property);
    return style.isSome();
  };
  var remove$5 = function (element, property) {
    var dom = element.dom();
    internalRemove(dom, property);
    if ($_6spjcmxwjd09f07e.has(element, 'style') && $_7bomvkwpjd09f02u.trim($_6spjcmxwjd09f07e.get(element, 'style')) === '') {
      $_6spjcmxwjd09f07e.remove(element, 'style');
    }
  };
  var preserve = function (element, f) {
    var oldStyles = $_6spjcmxwjd09f07e.get(element, 'style');
    var result = f(element);
    var restore = oldStyles === undefined ? $_6spjcmxwjd09f07e.remove : $_6spjcmxwjd09f07e.set;
    restore(element, 'style', oldStyles);
    return result;
  };
  var copy$1 = function (source, target) {
    var sourceDom = source.dom();
    var targetDom = target.dom();
    if ($_45h4qvztjd09f0es.isSupported(sourceDom) && $_45h4qvztjd09f0es.isSupported(targetDom)) {
      targetDom.style.cssText = sourceDom.style.cssText;
    }
  };
  var reflow = function (e) {
    return e.dom().offsetWidth;
  };
  var transferOne$1 = function (source, destination, style) {
    getRaw(source, style).each(function (value) {
      if (getRaw(destination, style).isNone())
        set$2(destination, style, value);
    });
  };
  var transfer$1 = function (source, destination, styles) {
    if (!$_arrpm2xxjd09f07k.isElement(source) || !$_arrpm2xxjd09f07k.isElement(destination))
      return;
    $_bvikd2w9jd09f01c.each(styles, function (style) {
      transferOne$1(source, destination, style);
    });
  };
  var $_a9ctnkzsjd09f0ej = {
    copy: copy$1,
    set: set$2,
    preserve: preserve,
    setAll: setAll$1,
    setOptions: setOptions,
    remove: remove$5,
    get: get$3,
    getRaw: getRaw,
    getAllRaw: getAllRaw,
    isValidValue: isValidValue,
    reflow: reflow,
    transfer: transfer$1
  };

  function Dimension (name, getOffset) {
    var set = function (element, h) {
      if (!$_4biushwzjd09f03k.isNumber(h) && !h.match(/^[0-9]+$/))
        throw name + '.set accepts only positive integer values. Value was ' + h;
      var dom = element.dom();
      if ($_45h4qvztjd09f0es.isSupported(dom))
        dom.style[name] = h + 'px';
    };
    var get = function (element) {
      var r = getOffset(element);
      if (r <= 0 || r === null) {
        var css = $_a9ctnkzsjd09f0ej.get(element, name);
        return parseFloat(css) || 0;
      }
      return r;
    };
    var getOuter = get;
    var aggregate = function (element, properties) {
      return $_bvikd2w9jd09f01c.foldl(properties, function (acc, property) {
        var val = $_a9ctnkzsjd09f0ej.get(element, property);
        var value = val === undefined ? 0 : parseInt(val, 10);
        return isNaN(value) ? acc : acc + value;
      }, 0);
    };
    var max = function (element, value, properties) {
      var cumulativeInclusions = aggregate(element, properties);
      var absoluteMax = value > cumulativeInclusions ? value - cumulativeInclusions : 0;
      return absoluteMax;
    };
    return {
      set: set,
      get: get,
      getOuter: getOuter,
      aggregate: aggregate,
      max: max
    };
  }

  var api = Dimension('height', function (element) {
    return $_7m8dypy7jd09f08o.inBody(element) ? element.dom().getBoundingClientRect().height : element.dom().offsetHeight;
  });
  var set$3 = function (element, h) {
    api.set(element, h);
  };
  var get$4 = function (element) {
    return api.get(element);
  };
  var getOuter$1 = function (element) {
    return api.getOuter(element);
  };
  var setMax = function (element, value) {
    var inclusions = [
      'margin-top',
      'border-top-width',
      'padding-top',
      'padding-bottom',
      'border-bottom-width',
      'margin-bottom'
    ];
    var absMax = api.max(element, value, inclusions);
    $_a9ctnkzsjd09f0ej.set(element, 'max-height', absMax + 'px');
  };
  var $_52xa2jzrjd09f0eh = {
    set: set$3,
    get: get$4,
    getOuter: getOuter$1,
    setMax: setMax
  };

  var create$2 = function (cyclicField) {
    var schema = [
      $_1t7kykx2jd09f043.option('onEscape'),
      $_1t7kykx2jd09f043.option('onEnter'),
      $_1t7kykx2jd09f043.defaulted('selector', '[data-alloy-tabstop="true"]'),
      $_1t7kykx2jd09f043.defaulted('firstTabstop', 0),
      $_1t7kykx2jd09f043.defaulted('useTabstopAt', $_8z5eqrwbjd09f01q.constant(true)),
      $_1t7kykx2jd09f043.option('visibilitySelector')
    ].concat([cyclicField]);
    var isVisible = function (tabbingConfig, element) {
      var target = tabbingConfig.visibilitySelector().bind(function (sel) {
        return $_74vb1xzmjd09f0e3.closest(element, sel);
      }).getOr(element);
      return $_52xa2jzrjd09f0eh.get(target) > 0;
    };
    var findInitial = function (component, tabbingConfig) {
      var tabstops = $_23mimrzkjd09f0dz.descendants(component.element(), tabbingConfig.selector());
      var visibles = $_bvikd2w9jd09f01c.filter(tabstops, function (elem) {
        return isVisible(tabbingConfig, elem);
      });
      return $_8nwhzlwajd09f01m.from(visibles[tabbingConfig.firstTabstop()]);
    };
    var findCurrent = function (component, tabbingConfig) {
      return tabbingConfig.focusManager().get(component).bind(function (elem) {
        return $_74vb1xzmjd09f0e3.closest(elem, tabbingConfig.selector());
      });
    };
    var isTabstop = function (tabbingConfig, element) {
      return isVisible(tabbingConfig, element) && tabbingConfig.useTabstopAt()(element);
    };
    var focusIn = function (component, tabbingConfig, tabbingState) {
      findInitial(component, tabbingConfig).each(function (target) {
        tabbingConfig.focusManager().set(component, target);
      });
    };
    var goFromTabstop = function (component, tabstops, stopIndex, tabbingConfig, cycle) {
      return cycle(tabstops, stopIndex, function (elem) {
        return isTabstop(tabbingConfig, elem);
      }).fold(function () {
        return tabbingConfig.cyclic() ? $_8nwhzlwajd09f01m.some(true) : $_8nwhzlwajd09f01m.none();
      }, function (target) {
        tabbingConfig.focusManager().set(component, target);
        return $_8nwhzlwajd09f01m.some(true);
      });
    };
    var go = function (component, simulatedEvent, tabbingConfig, cycle) {
      var tabstops = $_23mimrzkjd09f0dz.descendants(component.element(), tabbingConfig.selector());
      return findCurrent(component, tabbingConfig).bind(function (tabstop) {
        var optStopIndex = $_bvikd2w9jd09f01c.findIndex(tabstops, $_8z5eqrwbjd09f01q.curry($_6s6cs1w8jd09f014.eq, tabstop));
        return optStopIndex.bind(function (stopIndex) {
          return goFromTabstop(component, tabstops, stopIndex, tabbingConfig, cycle);
        });
      });
    };
    var goBackwards = function (component, simulatedEvent, tabbingConfig, tabbingState) {
      var navigate = tabbingConfig.cyclic() ? $_8iir6ezqjd09f0ee.cyclePrev : $_8iir6ezqjd09f0ee.tryPrev;
      return go(component, simulatedEvent, tabbingConfig, navigate);
    };
    var goForwards = function (component, simulatedEvent, tabbingConfig, tabbingState) {
      var navigate = tabbingConfig.cyclic() ? $_8iir6ezqjd09f0ee.cycleNext : $_8iir6ezqjd09f0ee.tryNext;
      return go(component, simulatedEvent, tabbingConfig, navigate);
    };
    var execute = function (component, simulatedEvent, tabbingConfig, tabbingState) {
      return tabbingConfig.onEnter().bind(function (f) {
        return f(component, simulatedEvent);
      });
    };
    var exit = function (component, simulatedEvent, tabbingConfig, tabbingState) {
      return tabbingConfig.onEscape().bind(function (f) {
        return f(component, simulatedEvent);
      });
    };
    var getRules = $_8z5eqrwbjd09f01q.constant([
      $_651xtzzojd09f0e8.rule($_cmpov0zpjd09f0ea.and([
        $_cmpov0zpjd09f0ea.isShift,
        $_cmpov0zpjd09f0ea.inSet($_xd6lgzejd09f0d6.TAB())
      ]), goBackwards),
      $_651xtzzojd09f0e8.rule($_cmpov0zpjd09f0ea.inSet($_xd6lgzejd09f0d6.TAB()), goForwards),
      $_651xtzzojd09f0e8.rule($_cmpov0zpjd09f0ea.inSet($_xd6lgzejd09f0d6.ESCAPE()), exit),
      $_651xtzzojd09f0e8.rule($_cmpov0zpjd09f0ea.and([
        $_cmpov0zpjd09f0ea.isNotShift,
        $_cmpov0zpjd09f0ea.inSet($_xd6lgzejd09f0d6.ENTER())
      ]), execute)
    ]);
    var getEvents = $_8z5eqrwbjd09f01q.constant({});
    var getApis = $_8z5eqrwbjd09f01q.constant({});
    return $_5arm0mzfjd09f0d8.typical(schema, $_7dmh49xqjd09f072.init, getRules, getEvents, getApis, $_8nwhzlwajd09f01m.some(focusIn));
  };
  var $_f4jbepzdjd09f0ct = { create: create$2 };

  var AcyclicType = $_f4jbepzdjd09f0ct.create($_1t7kykx2jd09f043.state('cyclic', $_8z5eqrwbjd09f01q.constant(false)));

  var CyclicType = $_f4jbepzdjd09f0ct.create($_1t7kykx2jd09f043.state('cyclic', $_8z5eqrwbjd09f01q.constant(true)));

  var inside = function (target) {
    return $_arrpm2xxjd09f07k.name(target) === 'input' && $_6spjcmxwjd09f07e.get(target, 'type') !== 'radio' || $_arrpm2xxjd09f07k.name(target) === 'textarea';
  };
  var $_brva0izxjd09f0f6 = { inside: inside };

  var doDefaultExecute = function (component, simulatedEvent, focused) {
    $_3b6lb8wvjd09f037.dispatch(component, focused, $_5iytewwjd09f03c.execute());
    return $_8nwhzlwajd09f01m.some(true);
  };
  var defaultExecute = function (component, simulatedEvent, focused) {
    return $_brva0izxjd09f0f6.inside(focused) && $_cmpov0zpjd09f0ea.inSet($_xd6lgzejd09f0d6.SPACE())(simulatedEvent.event()) ? $_8nwhzlwajd09f01m.none() : doDefaultExecute(component, simulatedEvent, focused);
  };
  var $_bb704szyjd09f0f9 = { defaultExecute: defaultExecute };

  var schema$1 = [
    $_1t7kykx2jd09f043.defaulted('execute', $_bb704szyjd09f0f9.defaultExecute),
    $_1t7kykx2jd09f043.defaulted('useSpace', false),
    $_1t7kykx2jd09f043.defaulted('useEnter', true),
    $_1t7kykx2jd09f043.defaulted('useControlEnter', false),
    $_1t7kykx2jd09f043.defaulted('useDown', false)
  ];
  var execute = function (component, simulatedEvent, executeConfig, executeState) {
    return executeConfig.execute()(component, simulatedEvent, component.element());
  };
  var getRules = function (component, simulatedEvent, executeConfig, executeState) {
    var spaceExec = executeConfig.useSpace() && !$_brva0izxjd09f0f6.inside(component.element()) ? $_xd6lgzejd09f0d6.SPACE() : [];
    var enterExec = executeConfig.useEnter() ? $_xd6lgzejd09f0d6.ENTER() : [];
    var downExec = executeConfig.useDown() ? $_xd6lgzejd09f0d6.DOWN() : [];
    var execKeys = spaceExec.concat(enterExec).concat(downExec);
    return [$_651xtzzojd09f0e8.rule($_cmpov0zpjd09f0ea.inSet(execKeys), execute)].concat(executeConfig.useControlEnter() ? [$_651xtzzojd09f0e8.rule($_cmpov0zpjd09f0ea.and([
        $_cmpov0zpjd09f0ea.isControl,
        $_cmpov0zpjd09f0ea.inSet($_xd6lgzejd09f0d6.ENTER())
      ]), execute)] : []);
  };
  var getEvents = $_8z5eqrwbjd09f01q.constant({});
  var getApis = $_8z5eqrwbjd09f01q.constant({});
  var ExecutionType = $_5arm0mzfjd09f0d8.typical(schema$1, $_7dmh49xqjd09f072.init, getRules, getEvents, getApis, $_8nwhzlwajd09f01m.none());

  var flatgrid = function (spec) {
    var dimensions = Cell($_8nwhzlwajd09f01m.none());
    var setGridSize = function (numRows, numColumns) {
      dimensions.set($_8nwhzlwajd09f01m.some({
        numRows: $_8z5eqrwbjd09f01q.constant(numRows),
        numColumns: $_8z5eqrwbjd09f01q.constant(numColumns)
      }));
    };
    var getNumRows = function () {
      return dimensions.get().map(function (d) {
        return d.numRows();
      });
    };
    var getNumColumns = function () {
      return dimensions.get().map(function (d) {
        return d.numColumns();
      });
    };
    return BehaviourState({
      readState: $_8z5eqrwbjd09f01q.constant({}),
      setGridSize: setGridSize,
      getNumRows: getNumRows,
      getNumColumns: getNumColumns
    });
  };
  var init$1 = function (spec) {
    return spec.state()(spec);
  };
  var $_fsr3m7100jd09f0fl = {
    flatgrid: flatgrid,
    init: init$1
  };

  var onDirection = function (isLtr, isRtl) {
    return function (element) {
      return getDirection(element) === 'rtl' ? isRtl : isLtr;
    };
  };
  var getDirection = function (element) {
    return $_a9ctnkzsjd09f0ej.get(element, 'direction') === 'rtl' ? 'rtl' : 'ltr';
  };
  var $_6om2ow102jd09f0fz = {
    onDirection: onDirection,
    getDirection: getDirection
  };

  var useH = function (movement) {
    return function (component, simulatedEvent, config, state) {
      var move = movement(component.element());
      return use(move, component, simulatedEvent, config, state);
    };
  };
  var west = function (moveLeft, moveRight) {
    var movement = $_6om2ow102jd09f0fz.onDirection(moveLeft, moveRight);
    return useH(movement);
  };
  var east = function (moveLeft, moveRight) {
    var movement = $_6om2ow102jd09f0fz.onDirection(moveRight, moveLeft);
    return useH(movement);
  };
  var useV = function (move) {
    return function (component, simulatedEvent, config, state) {
      return use(move, component, simulatedEvent, config, state);
    };
  };
  var use = function (move, component, simulatedEvent, config, state) {
    var outcome = config.focusManager().get(component).bind(function (focused) {
      return move(component.element(), focused, config, state);
    });
    return outcome.map(function (newFocus) {
      config.focusManager().set(component, newFocus);
      return true;
    });
  };
  var $_dv9mmz101jd09f0fx = {
    east: east,
    west: west,
    north: useV,
    south: useV,
    move: useV
  };

  var indexInfo = $_gbd4xkxmjd09f06u.immutableBag([
    'index',
    'candidates'
  ], []);
  var locate = function (candidates, predicate) {
    return $_bvikd2w9jd09f01c.findIndex(candidates, predicate).map(function (index) {
      return indexInfo({
        index: index,
        candidates: candidates
      });
    });
  };
  var $_7ahvc7104jd09f0g7 = { locate: locate };

  var visibilityToggler = function (element, property, hiddenValue, visibleValue) {
    var initial = $_a9ctnkzsjd09f0ej.get(element, property);
    if (initial === undefined)
      initial = '';
    var value = initial === hiddenValue ? visibleValue : hiddenValue;
    var off = $_8z5eqrwbjd09f01q.curry($_a9ctnkzsjd09f0ej.set, element, property, initial);
    var on = $_8z5eqrwbjd09f01q.curry($_a9ctnkzsjd09f0ej.set, element, property, value);
    return Toggler(off, on, false);
  };
  var toggler$1 = function (element) {
    return visibilityToggler(element, 'visibility', 'hidden', 'visible');
  };
  var displayToggler = function (element, value) {
    return visibilityToggler(element, 'display', 'none', value);
  };
  var isHidden = function (dom) {
    return dom.offsetWidth <= 0 && dom.offsetHeight <= 0;
  };
  var isVisible = function (element) {
    var dom = element.dom();
    return !isHidden(dom);
  };
  var $_89r9jz105jd09f0ga = {
    toggler: toggler$1,
    displayToggler: displayToggler,
    isVisible: isVisible
  };

  var locateVisible = function (container, current, selector) {
    var filter = $_89r9jz105jd09f0ga.isVisible;
    return locateIn(container, current, selector, filter);
  };
  var locateIn = function (container, current, selector, filter) {
    var predicate = $_8z5eqrwbjd09f01q.curry($_6s6cs1w8jd09f014.eq, current);
    var candidates = $_23mimrzkjd09f0dz.descendants(container, selector);
    var visible = $_bvikd2w9jd09f01c.filter(candidates, $_89r9jz105jd09f0ga.isVisible);
    return $_7ahvc7104jd09f0g7.locate(visible, predicate);
  };
  var findIndex$2 = function (elements, target) {
    return $_bvikd2w9jd09f01c.findIndex(elements, function (elem) {
      return $_6s6cs1w8jd09f014.eq(target, elem);
    });
  };
  var $_7egw14103jd09f0g0 = {
    locateVisible: locateVisible,
    locateIn: locateIn,
    findIndex: findIndex$2
  };

  var withGrid = function (values, index, numCols, f) {
    var oldRow = Math.floor(index / numCols);
    var oldColumn = index % numCols;
    return f(oldRow, oldColumn).bind(function (address) {
      var newIndex = address.row() * numCols + address.column();
      return newIndex >= 0 && newIndex < values.length ? $_8nwhzlwajd09f01m.some(values[newIndex]) : $_8nwhzlwajd09f01m.none();
    });
  };
  var cycleHorizontal = function (values, index, numRows, numCols, delta) {
    return withGrid(values, index, numCols, function (oldRow, oldColumn) {
      var onLastRow = oldRow === numRows - 1;
      var colsInRow = onLastRow ? values.length - oldRow * numCols : numCols;
      var newColumn = $_6ufqj2zjjd09f0dy.cycleBy(oldColumn, delta, 0, colsInRow - 1);
      return $_8nwhzlwajd09f01m.some({
        row: $_8z5eqrwbjd09f01q.constant(oldRow),
        column: $_8z5eqrwbjd09f01q.constant(newColumn)
      });
    });
  };
  var cycleVertical = function (values, index, numRows, numCols, delta) {
    return withGrid(values, index, numCols, function (oldRow, oldColumn) {
      var newRow = $_6ufqj2zjjd09f0dy.cycleBy(oldRow, delta, 0, numRows - 1);
      var onLastRow = newRow === numRows - 1;
      var colsInRow = onLastRow ? values.length - newRow * numCols : numCols;
      var newCol = $_6ufqj2zjjd09f0dy.cap(oldColumn, 0, colsInRow - 1);
      return $_8nwhzlwajd09f01m.some({
        row: $_8z5eqrwbjd09f01q.constant(newRow),
        column: $_8z5eqrwbjd09f01q.constant(newCol)
      });
    });
  };
  var cycleRight = function (values, index, numRows, numCols) {
    return cycleHorizontal(values, index, numRows, numCols, +1);
  };
  var cycleLeft = function (values, index, numRows, numCols) {
    return cycleHorizontal(values, index, numRows, numCols, -1);
  };
  var cycleUp = function (values, index, numRows, numCols) {
    return cycleVertical(values, index, numRows, numCols, -1);
  };
  var cycleDown = function (values, index, numRows, numCols) {
    return cycleVertical(values, index, numRows, numCols, +1);
  };
  var $_c8ct0x106jd09f0gd = {
    cycleDown: cycleDown,
    cycleUp: cycleUp,
    cycleLeft: cycleLeft,
    cycleRight: cycleRight
  };

  var schema$2 = [
    $_1t7kykx2jd09f043.strict('selector'),
    $_1t7kykx2jd09f043.defaulted('execute', $_bb704szyjd09f0f9.defaultExecute),
    $_1l599yytjd09f0ar.onKeyboardHandler('onEscape'),
    $_1t7kykx2jd09f043.defaulted('captureTab', false),
    $_1l599yytjd09f0ar.initSize()
  ];
  var focusIn = function (component, gridConfig, gridState) {
    $_74vb1xzmjd09f0e3.descendant(component.element(), gridConfig.selector()).each(function (first) {
      gridConfig.focusManager().set(component, first);
    });
  };
  var findCurrent = function (component, gridConfig) {
    return gridConfig.focusManager().get(component).bind(function (elem) {
      return $_74vb1xzmjd09f0e3.closest(elem, gridConfig.selector());
    });
  };
  var execute$1 = function (component, simulatedEvent, gridConfig, gridState) {
    return findCurrent(component, gridConfig).bind(function (focused) {
      return gridConfig.execute()(component, simulatedEvent, focused);
    });
  };
  var doMove = function (cycle) {
    return function (element, focused, gridConfig, gridState) {
      return $_7egw14103jd09f0g0.locateVisible(element, focused, gridConfig.selector()).bind(function (identified) {
        return cycle(identified.candidates(), identified.index(), gridState.getNumRows().getOr(gridConfig.initSize().numRows()), gridState.getNumColumns().getOr(gridConfig.initSize().numColumns()));
      });
    };
  };
  var handleTab = function (component, simulatedEvent, gridConfig, gridState) {
    return gridConfig.captureTab() ? $_8nwhzlwajd09f01m.some(true) : $_8nwhzlwajd09f01m.none();
  };
  var doEscape = function (component, simulatedEvent, gridConfig, gridState) {
    return gridConfig.onEscape()(component, simulatedEvent);
  };
  var moveLeft = doMove($_c8ct0x106jd09f0gd.cycleLeft);
  var moveRight = doMove($_c8ct0x106jd09f0gd.cycleRight);
  var moveNorth = doMove($_c8ct0x106jd09f0gd.cycleUp);
  var moveSouth = doMove($_c8ct0x106jd09f0gd.cycleDown);
  var getRules$1 = $_8z5eqrwbjd09f01q.constant([
    $_651xtzzojd09f0e8.rule($_cmpov0zpjd09f0ea.inSet($_xd6lgzejd09f0d6.LEFT()), $_dv9mmz101jd09f0fx.west(moveLeft, moveRight)),
    $_651xtzzojd09f0e8.rule($_cmpov0zpjd09f0ea.inSet($_xd6lgzejd09f0d6.RIGHT()), $_dv9mmz101jd09f0fx.east(moveLeft, moveRight)),
    $_651xtzzojd09f0e8.rule($_cmpov0zpjd09f0ea.inSet($_xd6lgzejd09f0d6.UP()), $_dv9mmz101jd09f0fx.north(moveNorth)),
    $_651xtzzojd09f0e8.rule($_cmpov0zpjd09f0ea.inSet($_xd6lgzejd09f0d6.DOWN()), $_dv9mmz101jd09f0fx.south(moveSouth)),
    $_651xtzzojd09f0e8.rule($_cmpov0zpjd09f0ea.and([
      $_cmpov0zpjd09f0ea.isShift,
      $_cmpov0zpjd09f0ea.inSet($_xd6lgzejd09f0d6.TAB())
    ]), handleTab),
    $_651xtzzojd09f0e8.rule($_cmpov0zpjd09f0ea.and([
      $_cmpov0zpjd09f0ea.isNotShift,
      $_cmpov0zpjd09f0ea.inSet($_xd6lgzejd09f0d6.TAB())
    ]), handleTab),
    $_651xtzzojd09f0e8.rule($_cmpov0zpjd09f0ea.inSet($_xd6lgzejd09f0d6.ESCAPE()), doEscape),
    $_651xtzzojd09f0e8.rule($_cmpov0zpjd09f0ea.inSet($_xd6lgzejd09f0d6.SPACE().concat($_xd6lgzejd09f0d6.ENTER())), execute$1)
  ]);
  var getEvents$1 = $_8z5eqrwbjd09f01q.constant({});
  var getApis$1 = {};
  var FlatgridType = $_5arm0mzfjd09f0d8.typical(schema$2, $_fsr3m7100jd09f0fl.flatgrid, getRules$1, getEvents$1, getApis$1, $_8nwhzlwajd09f01m.some(focusIn));

  var horizontal = function (container, selector, current, delta) {
    return $_7egw14103jd09f0g0.locateVisible(container, current, selector, $_8z5eqrwbjd09f01q.constant(true)).bind(function (identified) {
      var index = identified.index();
      var candidates = identified.candidates();
      var newIndex = $_6ufqj2zjjd09f0dy.cycleBy(index, delta, 0, candidates.length - 1);
      return $_8nwhzlwajd09f01m.from(candidates[newIndex]);
    });
  };
  var $_cll0no108jd09f0gu = { horizontal: horizontal };

  var schema$3 = [
    $_1t7kykx2jd09f043.strict('selector'),
    $_1t7kykx2jd09f043.defaulted('getInitial', $_8nwhzlwajd09f01m.none),
    $_1t7kykx2jd09f043.defaulted('execute', $_bb704szyjd09f0f9.defaultExecute),
    $_1t7kykx2jd09f043.defaulted('executeOnMove', false)
  ];
  var findCurrent$1 = function (component, flowConfig) {
    return flowConfig.focusManager().get(component).bind(function (elem) {
      return $_74vb1xzmjd09f0e3.closest(elem, flowConfig.selector());
    });
  };
  var execute$2 = function (component, simulatedEvent, flowConfig) {
    return findCurrent$1(component, flowConfig).bind(function (focused) {
      return flowConfig.execute()(component, simulatedEvent, focused);
    });
  };
  var focusIn$1 = function (component, flowConfig) {
    flowConfig.getInitial()(component).or($_74vb1xzmjd09f0e3.descendant(component.element(), flowConfig.selector())).each(function (first) {
      flowConfig.focusManager().set(component, first);
    });
  };
  var moveLeft$1 = function (element, focused, info) {
    return $_cll0no108jd09f0gu.horizontal(element, info.selector(), focused, -1);
  };
  var moveRight$1 = function (element, focused, info) {
    return $_cll0no108jd09f0gu.horizontal(element, info.selector(), focused, +1);
  };
  var doMove$1 = function (movement) {
    return function (component, simulatedEvent, flowConfig) {
      return movement(component, simulatedEvent, flowConfig).bind(function () {
        return flowConfig.executeOnMove() ? execute$2(component, simulatedEvent, flowConfig) : $_8nwhzlwajd09f01m.some(true);
      });
    };
  };
  var getRules$2 = function (_) {
    return [
      $_651xtzzojd09f0e8.rule($_cmpov0zpjd09f0ea.inSet($_xd6lgzejd09f0d6.LEFT().concat($_xd6lgzejd09f0d6.UP())), doMove$1($_dv9mmz101jd09f0fx.west(moveLeft$1, moveRight$1))),
      $_651xtzzojd09f0e8.rule($_cmpov0zpjd09f0ea.inSet($_xd6lgzejd09f0d6.RIGHT().concat($_xd6lgzejd09f0d6.DOWN())), doMove$1($_dv9mmz101jd09f0fx.east(moveLeft$1, moveRight$1))),
      $_651xtzzojd09f0e8.rule($_cmpov0zpjd09f0ea.inSet($_xd6lgzejd09f0d6.ENTER()), execute$2),
      $_651xtzzojd09f0e8.rule($_cmpov0zpjd09f0ea.inSet($_xd6lgzejd09f0d6.SPACE()), execute$2)
    ];
  };
  var getEvents$2 = $_8z5eqrwbjd09f01q.constant({});
  var getApis$2 = $_8z5eqrwbjd09f01q.constant({});
  var FlowType = $_5arm0mzfjd09f0d8.typical(schema$3, $_7dmh49xqjd09f072.init, getRules$2, getEvents$2, getApis$2, $_8nwhzlwajd09f01m.some(focusIn$1));

  var outcome = $_gbd4xkxmjd09f06u.immutableBag([
    'rowIndex',
    'columnIndex',
    'cell'
  ], []);
  var toCell = function (matrix, rowIndex, columnIndex) {
    return $_8nwhzlwajd09f01m.from(matrix[rowIndex]).bind(function (row) {
      return $_8nwhzlwajd09f01m.from(row[columnIndex]).map(function (cell) {
        return outcome({
          rowIndex: rowIndex,
          columnIndex: columnIndex,
          cell: cell
        });
      });
    });
  };
  var cycleHorizontal$1 = function (matrix, rowIndex, startCol, deltaCol) {
    var row = matrix[rowIndex];
    var colsInRow = row.length;
    var newColIndex = $_6ufqj2zjjd09f0dy.cycleBy(startCol, deltaCol, 0, colsInRow - 1);
    return toCell(matrix, rowIndex, newColIndex);
  };
  var cycleVertical$1 = function (matrix, colIndex, startRow, deltaRow) {
    var nextRowIndex = $_6ufqj2zjjd09f0dy.cycleBy(startRow, deltaRow, 0, matrix.length - 1);
    var colsInNextRow = matrix[nextRowIndex].length;
    var nextColIndex = $_6ufqj2zjjd09f0dy.cap(colIndex, 0, colsInNextRow - 1);
    return toCell(matrix, nextRowIndex, nextColIndex);
  };
  var moveHorizontal = function (matrix, rowIndex, startCol, deltaCol) {
    var row = matrix[rowIndex];
    var colsInRow = row.length;
    var newColIndex = $_6ufqj2zjjd09f0dy.cap(startCol + deltaCol, 0, colsInRow - 1);
    return toCell(matrix, rowIndex, newColIndex);
  };
  var moveVertical = function (matrix, colIndex, startRow, deltaRow) {
    var nextRowIndex = $_6ufqj2zjjd09f0dy.cap(startRow + deltaRow, 0, matrix.length - 1);
    var colsInNextRow = matrix[nextRowIndex].length;
    var nextColIndex = $_6ufqj2zjjd09f0dy.cap(colIndex, 0, colsInNextRow - 1);
    return toCell(matrix, nextRowIndex, nextColIndex);
  };
  var cycleRight$1 = function (matrix, startRow, startCol) {
    return cycleHorizontal$1(matrix, startRow, startCol, +1);
  };
  var cycleLeft$1 = function (matrix, startRow, startCol) {
    return cycleHorizontal$1(matrix, startRow, startCol, -1);
  };
  var cycleUp$1 = function (matrix, startRow, startCol) {
    return cycleVertical$1(matrix, startCol, startRow, -1);
  };
  var cycleDown$1 = function (matrix, startRow, startCol) {
    return cycleVertical$1(matrix, startCol, startRow, +1);
  };
  var moveLeft$2 = function (matrix, startRow, startCol) {
    return moveHorizontal(matrix, startRow, startCol, -1);
  };
  var moveRight$2 = function (matrix, startRow, startCol) {
    return moveHorizontal(matrix, startRow, startCol, +1);
  };
  var moveUp = function (matrix, startRow, startCol) {
    return moveVertical(matrix, startCol, startRow, -1);
  };
  var moveDown = function (matrix, startRow, startCol) {
    return moveVertical(matrix, startCol, startRow, +1);
  };
  var $_fj46e10ajd09f0h9 = {
    cycleRight: cycleRight$1,
    cycleLeft: cycleLeft$1,
    cycleUp: cycleUp$1,
    cycleDown: cycleDown$1,
    moveLeft: moveLeft$2,
    moveRight: moveRight$2,
    moveUp: moveUp,
    moveDown: moveDown
  };

  var schema$4 = [
    $_1t7kykx2jd09f043.strictObjOf('selectors', [
      $_1t7kykx2jd09f043.strict('row'),
      $_1t7kykx2jd09f043.strict('cell')
    ]),
    $_1t7kykx2jd09f043.defaulted('cycles', true),
    $_1t7kykx2jd09f043.defaulted('previousSelector', $_8nwhzlwajd09f01m.none),
    $_1t7kykx2jd09f043.defaulted('execute', $_bb704szyjd09f0f9.defaultExecute)
  ];
  var focusIn$2 = function (component, matrixConfig) {
    var focused = matrixConfig.previousSelector()(component).orThunk(function () {
      var selectors = matrixConfig.selectors();
      return $_74vb1xzmjd09f0e3.descendant(component.element(), selectors.cell());
    });
    focused.each(function (cell) {
      matrixConfig.focusManager().set(component, cell);
    });
  };
  var execute$3 = function (component, simulatedEvent, matrixConfig) {
    return $_ccs2jvygjd09f09f.search(component.element()).bind(function (focused) {
      return matrixConfig.execute()(component, simulatedEvent, focused);
    });
  };
  var toMatrix = function (rows, matrixConfig) {
    return $_bvikd2w9jd09f01c.map(rows, function (row) {
      return $_23mimrzkjd09f0dz.descendants(row, matrixConfig.selectors().cell());
    });
  };
  var doMove$2 = function (ifCycle, ifMove) {
    return function (element, focused, matrixConfig) {
      var move = matrixConfig.cycles() ? ifCycle : ifMove;
      return $_74vb1xzmjd09f0e3.closest(focused, matrixConfig.selectors().row()).bind(function (inRow) {
        var cellsInRow = $_23mimrzkjd09f0dz.descendants(inRow, matrixConfig.selectors().cell());
        return $_7egw14103jd09f0g0.findIndex(cellsInRow, focused).bind(function (colIndex) {
          var allRows = $_23mimrzkjd09f0dz.descendants(element, matrixConfig.selectors().row());
          return $_7egw14103jd09f0g0.findIndex(allRows, inRow).bind(function (rowIndex) {
            var matrix = toMatrix(allRows, matrixConfig);
            return move(matrix, rowIndex, colIndex).map(function (next) {
              return next.cell();
            });
          });
        });
      });
    };
  };
  var moveLeft$3 = doMove$2($_fj46e10ajd09f0h9.cycleLeft, $_fj46e10ajd09f0h9.moveLeft);
  var moveRight$3 = doMove$2($_fj46e10ajd09f0h9.cycleRight, $_fj46e10ajd09f0h9.moveRight);
  var moveNorth$1 = doMove$2($_fj46e10ajd09f0h9.cycleUp, $_fj46e10ajd09f0h9.moveUp);
  var moveSouth$1 = doMove$2($_fj46e10ajd09f0h9.cycleDown, $_fj46e10ajd09f0h9.moveDown);
  var getRules$3 = $_8z5eqrwbjd09f01q.constant([
    $_651xtzzojd09f0e8.rule($_cmpov0zpjd09f0ea.inSet($_xd6lgzejd09f0d6.LEFT()), $_dv9mmz101jd09f0fx.west(moveLeft$3, moveRight$3)),
    $_651xtzzojd09f0e8.rule($_cmpov0zpjd09f0ea.inSet($_xd6lgzejd09f0d6.RIGHT()), $_dv9mmz101jd09f0fx.east(moveLeft$3, moveRight$3)),
    $_651xtzzojd09f0e8.rule($_cmpov0zpjd09f0ea.inSet($_xd6lgzejd09f0d6.UP()), $_dv9mmz101jd09f0fx.north(moveNorth$1)),
    $_651xtzzojd09f0e8.rule($_cmpov0zpjd09f0ea.inSet($_xd6lgzejd09f0d6.DOWN()), $_dv9mmz101jd09f0fx.south(moveSouth$1)),
    $_651xtzzojd09f0e8.rule($_cmpov0zpjd09f0ea.inSet($_xd6lgzejd09f0d6.SPACE().concat($_xd6lgzejd09f0d6.ENTER())), execute$3)
  ]);
  var getEvents$3 = $_8z5eqrwbjd09f01q.constant({});
  var getApis$3 = $_8z5eqrwbjd09f01q.constant({});
  var MatrixType = $_5arm0mzfjd09f0d8.typical(schema$4, $_7dmh49xqjd09f072.init, getRules$3, getEvents$3, getApis$3, $_8nwhzlwajd09f01m.some(focusIn$2));

  var schema$5 = [
    $_1t7kykx2jd09f043.strict('selector'),
    $_1t7kykx2jd09f043.defaulted('execute', $_bb704szyjd09f0f9.defaultExecute),
    $_1t7kykx2jd09f043.defaulted('moveOnTab', false)
  ];
  var execute$4 = function (component, simulatedEvent, menuConfig) {
    return menuConfig.focusManager().get(component).bind(function (focused) {
      return menuConfig.execute()(component, simulatedEvent, focused);
    });
  };
  var focusIn$3 = function (component, menuConfig, simulatedEvent) {
    $_74vb1xzmjd09f0e3.descendant(component.element(), menuConfig.selector()).each(function (first) {
      menuConfig.focusManager().set(component, first);
    });
  };
  var moveUp$1 = function (element, focused, info) {
    return $_cll0no108jd09f0gu.horizontal(element, info.selector(), focused, -1);
  };
  var moveDown$1 = function (element, focused, info) {
    return $_cll0no108jd09f0gu.horizontal(element, info.selector(), focused, +1);
  };
  var fireShiftTab = function (component, simulatedEvent, menuConfig) {
    return menuConfig.moveOnTab() ? $_dv9mmz101jd09f0fx.move(moveUp$1)(component, simulatedEvent, menuConfig) : $_8nwhzlwajd09f01m.none();
  };
  var fireTab = function (component, simulatedEvent, menuConfig) {
    return menuConfig.moveOnTab() ? $_dv9mmz101jd09f0fx.move(moveDown$1)(component, simulatedEvent, menuConfig) : $_8nwhzlwajd09f01m.none();
  };
  var getRules$4 = $_8z5eqrwbjd09f01q.constant([
    $_651xtzzojd09f0e8.rule($_cmpov0zpjd09f0ea.inSet($_xd6lgzejd09f0d6.UP()), $_dv9mmz101jd09f0fx.move(moveUp$1)),
    $_651xtzzojd09f0e8.rule($_cmpov0zpjd09f0ea.inSet($_xd6lgzejd09f0d6.DOWN()), $_dv9mmz101jd09f0fx.move(moveDown$1)),
    $_651xtzzojd09f0e8.rule($_cmpov0zpjd09f0ea.and([
      $_cmpov0zpjd09f0ea.isShift,
      $_cmpov0zpjd09f0ea.inSet($_xd6lgzejd09f0d6.TAB())
    ]), fireShiftTab),
    $_651xtzzojd09f0e8.rule($_cmpov0zpjd09f0ea.and([
      $_cmpov0zpjd09f0ea.isNotShift,
      $_cmpov0zpjd09f0ea.inSet($_xd6lgzejd09f0d6.TAB())
    ]), fireTab),
    $_651xtzzojd09f0e8.rule($_cmpov0zpjd09f0ea.inSet($_xd6lgzejd09f0d6.ENTER()), execute$4),
    $_651xtzzojd09f0e8.rule($_cmpov0zpjd09f0ea.inSet($_xd6lgzejd09f0d6.SPACE()), execute$4)
  ]);
  var getEvents$4 = $_8z5eqrwbjd09f01q.constant({});
  var getApis$4 = $_8z5eqrwbjd09f01q.constant({});
  var MenuType = $_5arm0mzfjd09f0d8.typical(schema$5, $_7dmh49xqjd09f072.init, getRules$4, getEvents$4, getApis$4, $_8nwhzlwajd09f01m.some(focusIn$3));

  var schema$6 = [
    $_1l599yytjd09f0ar.onKeyboardHandler('onSpace'),
    $_1l599yytjd09f0ar.onKeyboardHandler('onEnter'),
    $_1l599yytjd09f0ar.onKeyboardHandler('onShiftEnter'),
    $_1l599yytjd09f0ar.onKeyboardHandler('onLeft'),
    $_1l599yytjd09f0ar.onKeyboardHandler('onRight'),
    $_1l599yytjd09f0ar.onKeyboardHandler('onTab'),
    $_1l599yytjd09f0ar.onKeyboardHandler('onShiftTab'),
    $_1l599yytjd09f0ar.onKeyboardHandler('onUp'),
    $_1l599yytjd09f0ar.onKeyboardHandler('onDown'),
    $_1l599yytjd09f0ar.onKeyboardHandler('onEscape'),
    $_1t7kykx2jd09f043.option('focusIn')
  ];
  var getRules$5 = function (component, simulatedEvent, executeInfo) {
    return [
      $_651xtzzojd09f0e8.rule($_cmpov0zpjd09f0ea.inSet($_xd6lgzejd09f0d6.SPACE()), executeInfo.onSpace()),
      $_651xtzzojd09f0e8.rule($_cmpov0zpjd09f0ea.and([
        $_cmpov0zpjd09f0ea.isNotShift,
        $_cmpov0zpjd09f0ea.inSet($_xd6lgzejd09f0d6.ENTER())
      ]), executeInfo.onEnter()),
      $_651xtzzojd09f0e8.rule($_cmpov0zpjd09f0ea.and([
        $_cmpov0zpjd09f0ea.isShift,
        $_cmpov0zpjd09f0ea.inSet($_xd6lgzejd09f0d6.ENTER())
      ]), executeInfo.onShiftEnter()),
      $_651xtzzojd09f0e8.rule($_cmpov0zpjd09f0ea.and([
        $_cmpov0zpjd09f0ea.isShift,
        $_cmpov0zpjd09f0ea.inSet($_xd6lgzejd09f0d6.TAB())
      ]), executeInfo.onShiftTab()),
      $_651xtzzojd09f0e8.rule($_cmpov0zpjd09f0ea.and([
        $_cmpov0zpjd09f0ea.isNotShift,
        $_cmpov0zpjd09f0ea.inSet($_xd6lgzejd09f0d6.TAB())
      ]), executeInfo.onTab()),
      $_651xtzzojd09f0e8.rule($_cmpov0zpjd09f0ea.inSet($_xd6lgzejd09f0d6.UP()), executeInfo.onUp()),
      $_651xtzzojd09f0e8.rule($_cmpov0zpjd09f0ea.inSet($_xd6lgzejd09f0d6.DOWN()), executeInfo.onDown()),
      $_651xtzzojd09f0e8.rule($_cmpov0zpjd09f0ea.inSet($_xd6lgzejd09f0d6.LEFT()), executeInfo.onLeft()),
      $_651xtzzojd09f0e8.rule($_cmpov0zpjd09f0ea.inSet($_xd6lgzejd09f0d6.RIGHT()), executeInfo.onRight()),
      $_651xtzzojd09f0e8.rule($_cmpov0zpjd09f0ea.inSet($_xd6lgzejd09f0d6.SPACE()), executeInfo.onSpace()),
      $_651xtzzojd09f0e8.rule($_cmpov0zpjd09f0ea.inSet($_xd6lgzejd09f0d6.ESCAPE()), executeInfo.onEscape())
    ];
  };
  var focusIn$4 = function (component, executeInfo) {
    return executeInfo.focusIn().bind(function (f) {
      return f(component, executeInfo);
    });
  };
  var getEvents$5 = $_8z5eqrwbjd09f01q.constant({});
  var getApis$5 = $_8z5eqrwbjd09f01q.constant({});
  var SpecialType = $_5arm0mzfjd09f0d8.typical(schema$6, $_7dmh49xqjd09f072.init, getRules$5, getEvents$5, getApis$5, $_8nwhzlwajd09f01m.some(focusIn$4));

  var $_gj9yzpzbjd09f0cn = {
    acyclic: AcyclicType.schema(),
    cyclic: CyclicType.schema(),
    flow: FlowType.schema(),
    flatgrid: FlatgridType.schema(),
    matrix: MatrixType.schema(),
    execution: ExecutionType.schema(),
    menu: MenuType.schema(),
    special: SpecialType.schema()
  };

  var Keying = $_fq8al5w4jd09f00e.createModes({
    branchKey: 'mode',
    branches: $_gj9yzpzbjd09f0cn,
    name: 'keying',
    active: {
      events: function (keyingConfig, keyingState) {
        var handler = keyingConfig.handler();
        return handler.toEvents(keyingConfig, keyingState);
      }
    },
    apis: {
      focusIn: function (component) {
        component.getSystem().triggerFocus(component.element(), component.element());
      },
      setGridSize: function (component, keyConfig, keyState, numRows, numColumns) {
        if (!$_q0etsx6jd09f053.hasKey(keyState, 'setGridSize')) {
          console.error('Layout does not support setGridSize');
        } else {
          keyState.setGridSize(numRows, numColumns);
        }
      }
    },
    state: $_fsr3m7100jd09f0fl
  });

  var field$1 = function (name, forbidden) {
    return $_1t7kykx2jd09f043.defaultedObjOf(name, {}, $_bvikd2w9jd09f01c.map(forbidden, function (f) {
      return $_1t7kykx2jd09f043.forbid(f.name(), 'Cannot configure ' + f.name() + ' for ' + name);
    }).concat([$_1t7kykx2jd09f043.state('dump', $_8z5eqrwbjd09f01q.identity)]));
  };
  var get$5 = function (data) {
    return data.dump();
  };
  var $_beskyy10djd09f0i0 = {
    field: field$1,
    get: get$5
  };

  var unique = 0;
  var generate$1 = function (prefix) {
    var date = new Date();
    var time = date.getTime();
    var random = Math.floor(Math.random() * 1000000000);
    unique++;
    return prefix + '_' + random + unique + String(time);
  };
  var $_3x6e2m10gjd09f0ir = { generate: generate$1 };

  var premadeTag = $_3x6e2m10gjd09f0ir.generate('alloy-premade');
  var apiConfig = $_3x6e2m10gjd09f0ir.generate('api');
  var premade = function (comp) {
    return $_q0etsx6jd09f053.wrap(premadeTag, comp);
  };
  var getPremade = function (spec) {
    return $_q0etsx6jd09f053.readOptFrom(spec, premadeTag);
  };
  var makeApi = function (f) {
    return $_8a1c5yxjjd09f06g.markAsSketchApi(function (component) {
      var args = Array.prototype.slice.call(arguments, 0);
      var spi = component.config(apiConfig);
      return f.apply(undefined, [spi].concat(args));
    }, f);
  };
  var $_fdvkh210fjd09f0ij = {
    apiConfig: $_8z5eqrwbjd09f01q.constant(apiConfig),
    makeApi: makeApi,
    premade: premade,
    getPremade: getPremade
  };

  var adt$2 = $_enqz8zx4jd09f04j.generate([
    { required: ['data'] },
    { external: ['data'] },
    { optional: ['data'] },
    { group: ['data'] }
  ]);
  var fFactory = $_1t7kykx2jd09f043.defaulted('factory', { sketch: $_8z5eqrwbjd09f01q.identity });
  var fSchema = $_1t7kykx2jd09f043.defaulted('schema', []);
  var fName = $_1t7kykx2jd09f043.strict('name');
  var fPname = $_1t7kykx2jd09f043.field('pname', 'pname', $_buoc30x3jd09f047.defaultedThunk(function (typeSpec) {
    return '<alloy.' + $_3x6e2m10gjd09f0ir.generate(typeSpec.name) + '>';
  }), $_33aoy2xhjd09f061.anyValue());
  var fDefaults = $_1t7kykx2jd09f043.defaulted('defaults', $_8z5eqrwbjd09f01q.constant({}));
  var fOverrides = $_1t7kykx2jd09f043.defaulted('overrides', $_8z5eqrwbjd09f01q.constant({}));
  var requiredSpec = $_33aoy2xhjd09f061.objOf([
    fFactory,
    fSchema,
    fName,
    fPname,
    fDefaults,
    fOverrides
  ]);
  var externalSpec = $_33aoy2xhjd09f061.objOf([
    fFactory,
    fSchema,
    fName,
    fDefaults,
    fOverrides
  ]);
  var optionalSpec = $_33aoy2xhjd09f061.objOf([
    fFactory,
    fSchema,
    fName,
    fPname,
    fDefaults,
    fOverrides
  ]);
  var groupSpec = $_33aoy2xhjd09f061.objOf([
    fFactory,
    fSchema,
    fName,
    $_1t7kykx2jd09f043.strict('unit'),
    fPname,
    fDefaults,
    fOverrides
  ]);
  var asNamedPart = function (part) {
    return part.fold($_8nwhzlwajd09f01m.some, $_8nwhzlwajd09f01m.none, $_8nwhzlwajd09f01m.some, $_8nwhzlwajd09f01m.some);
  };
  var name$1 = function (part) {
    var get = function (data) {
      return data.name();
    };
    return part.fold(get, get, get, get);
  };
  var asCommon = function (part) {
    return part.fold($_8z5eqrwbjd09f01q.identity, $_8z5eqrwbjd09f01q.identity, $_8z5eqrwbjd09f01q.identity, $_8z5eqrwbjd09f01q.identity);
  };
  var convert = function (adtConstructor, partSpec) {
    return function (spec) {
      var data = $_33aoy2xhjd09f061.asStructOrDie('Converting part type', partSpec, spec);
      return adtConstructor(data);
    };
  };
  var $_37ihar10kjd09f0js = {
    required: convert(adt$2.required, requiredSpec),
    external: convert(adt$2.external, externalSpec),
    optional: convert(adt$2.optional, optionalSpec),
    group: convert(adt$2.group, groupSpec),
    asNamedPart: asNamedPart,
    name: name$1,
    asCommon: asCommon,
    original: $_8z5eqrwbjd09f01q.constant('entirety')
  };

  var placeholder = 'placeholder';
  var adt$3 = $_enqz8zx4jd09f04j.generate([
    {
      single: [
        'required',
        'valueThunk'
      ]
    },
    {
      multiple: [
        'required',
        'valueThunks'
      ]
    }
  ]);
  var isSubstitute = function (uiType) {
    return $_bvikd2w9jd09f01c.contains([placeholder], uiType);
  };
  var subPlaceholder = function (owner, detail, compSpec, placeholders) {
    if (owner.exists(function (o) {
        return o !== compSpec.owner;
      }))
      return adt$3.single(true, $_8z5eqrwbjd09f01q.constant(compSpec));
    return $_q0etsx6jd09f053.readOptFrom(placeholders, compSpec.name).fold(function () {
      throw new Error('Unknown placeholder component: ' + compSpec.name + '\nKnown: [' + $_32a0zdx0jd09f03l.keys(placeholders) + ']\nNamespace: ' + owner.getOr('none') + '\nSpec: ' + $_2xfxp7xfjd09f05w.stringify(compSpec, null, 2));
    }, function (newSpec) {
      return newSpec.replace();
    });
  };
  var scan = function (owner, detail, compSpec, placeholders) {
    if (compSpec.uiType === placeholder)
      return subPlaceholder(owner, detail, compSpec, placeholders);
    else
      return adt$3.single(false, $_8z5eqrwbjd09f01q.constant(compSpec));
  };
  var substitute = function (owner, detail, compSpec, placeholders) {
    var base = scan(owner, detail, compSpec, placeholders);
    return base.fold(function (req, valueThunk) {
      var value = valueThunk(detail, compSpec.config, compSpec.validated);
      var childSpecs = $_q0etsx6jd09f053.readOptFrom(value, 'components').getOr([]);
      var substituted = $_bvikd2w9jd09f01c.bind(childSpecs, function (c) {
        return substitute(owner, detail, c, placeholders);
      });
      return [$_3htayhwyjd09f03i.deepMerge(value, { components: substituted })];
    }, function (req, valuesThunk) {
      var values = valuesThunk(detail, compSpec.config, compSpec.validated);
      return values;
    });
  };
  var substituteAll = function (owner, detail, components, placeholders) {
    return $_bvikd2w9jd09f01c.bind(components, function (c) {
      return substitute(owner, detail, c, placeholders);
    });
  };
  var oneReplace = function (label, replacements) {
    var called = false;
    var used = function () {
      return called;
    };
    var replace = function () {
      if (called === true)
        throw new Error('Trying to use the same placeholder more than once: ' + label);
      called = true;
      return replacements;
    };
    var required = function () {
      return replacements.fold(function (req, _) {
        return req;
      }, function (req, _) {
        return req;
      });
    };
    return {
      name: $_8z5eqrwbjd09f01q.constant(label),
      required: required,
      used: used,
      replace: replace
    };
  };
  var substitutePlaces = function (owner, detail, components, placeholders) {
    var ps = $_32a0zdx0jd09f03l.map(placeholders, function (ph, name) {
      return oneReplace(name, ph);
    });
    var outcome = substituteAll(owner, detail, components, ps);
    $_32a0zdx0jd09f03l.each(ps, function (p) {
      if (p.used() === false && p.required()) {
        throw new Error('Placeholder: ' + p.name() + ' was not found in components list\nNamespace: ' + owner.getOr('none') + '\nComponents: ' + $_2xfxp7xfjd09f05w.stringify(detail.components(), null, 2));
      }
    });
    return outcome;
  };
  var singleReplace = function (detail, p) {
    var replacement = p;
    return replacement.fold(function (req, valueThunk) {
      return [valueThunk(detail)];
    }, function (req, valuesThunk) {
      return valuesThunk(detail);
    });
  };
  var $_ikgvt10ljd09f0k7 = {
    single: adt$3.single,
    multiple: adt$3.multiple,
    isSubstitute: isSubstitute,
    placeholder: $_8z5eqrwbjd09f01q.constant(placeholder),
    substituteAll: substituteAll,
    substitutePlaces: substitutePlaces,
    singleReplace: singleReplace
  };

  var combine = function (detail, data, partSpec, partValidated) {
    var spec = partSpec;
    return $_3htayhwyjd09f03i.deepMerge(data.defaults()(detail, partSpec, partValidated), partSpec, { uid: detail.partUids()[data.name()] }, data.overrides()(detail, partSpec, partValidated), { 'debug.sketcher': $_q0etsx6jd09f053.wrap('part-' + data.name(), spec) });
  };
  var subs = function (owner, detail, parts) {
    var internals = {};
    var externals = {};
    $_bvikd2w9jd09f01c.each(parts, function (part) {
      part.fold(function (data) {
        internals[data.pname()] = $_ikgvt10ljd09f0k7.single(true, function (detail, partSpec, partValidated) {
          return data.factory().sketch(combine(detail, data, partSpec, partValidated));
        });
      }, function (data) {
        var partSpec = detail.parts()[data.name()]();
        externals[data.name()] = $_8z5eqrwbjd09f01q.constant(combine(detail, data, partSpec[$_37ihar10kjd09f0js.original()]()));
      }, function (data) {
        internals[data.pname()] = $_ikgvt10ljd09f0k7.single(false, function (detail, partSpec, partValidated) {
          return data.factory().sketch(combine(detail, data, partSpec, partValidated));
        });
      }, function (data) {
        internals[data.pname()] = $_ikgvt10ljd09f0k7.multiple(true, function (detail, _partSpec, _partValidated) {
          var units = detail[data.name()]();
          return $_bvikd2w9jd09f01c.map(units, function (u) {
            return data.factory().sketch($_3htayhwyjd09f03i.deepMerge(data.defaults()(detail, u), u, data.overrides()(detail, u)));
          });
        });
      });
    });
    return {
      internals: $_8z5eqrwbjd09f01q.constant(internals),
      externals: $_8z5eqrwbjd09f01q.constant(externals)
    };
  };
  var $_c0njb910jjd09f0ji = { subs: subs };

  var generate$2 = function (owner, parts) {
    var r = {};
    $_bvikd2w9jd09f01c.each(parts, function (part) {
      $_37ihar10kjd09f0js.asNamedPart(part).each(function (np) {
        var g = doGenerateOne(owner, np.pname());
        r[np.name()] = function (config) {
          var validated = $_33aoy2xhjd09f061.asRawOrDie('Part: ' + np.name() + ' in ' + owner, $_33aoy2xhjd09f061.objOf(np.schema()), config);
          return $_3htayhwyjd09f03i.deepMerge(g, {
            config: config,
            validated: validated
          });
        };
      });
    });
    return r;
  };
  var doGenerateOne = function (owner, pname) {
    return {
      uiType: $_ikgvt10ljd09f0k7.placeholder(),
      owner: owner,
      name: pname
    };
  };
  var generateOne = function (owner, pname, config) {
    return {
      uiType: $_ikgvt10ljd09f0k7.placeholder(),
      owner: owner,
      name: pname,
      config: config,
      validated: {}
    };
  };
  var schemas = function (parts) {
    return $_bvikd2w9jd09f01c.bind(parts, function (part) {
      return part.fold($_8nwhzlwajd09f01m.none, $_8nwhzlwajd09f01m.some, $_8nwhzlwajd09f01m.none, $_8nwhzlwajd09f01m.none).map(function (data) {
        return $_1t7kykx2jd09f043.strictObjOf(data.name(), data.schema().concat([$_1l599yytjd09f0ar.snapshot($_37ihar10kjd09f0js.original())]));
      }).toArray();
    });
  };
  var names = function (parts) {
    return $_bvikd2w9jd09f01c.map(parts, $_37ihar10kjd09f0js.name);
  };
  var substitutes = function (owner, detail, parts) {
    return $_c0njb910jjd09f0ji.subs(owner, detail, parts);
  };
  var components = function (owner, detail, internals) {
    return $_ikgvt10ljd09f0k7.substitutePlaces($_8nwhzlwajd09f01m.some(owner), detail, detail.components(), internals);
  };
  var getPart = function (component, detail, partKey) {
    var uid = detail.partUids()[partKey];
    return component.getSystem().getByUid(uid).toOption();
  };
  var getPartOrDie = function (component, detail, partKey) {
    return getPart(component, detail, partKey).getOrDie('Could not find part: ' + partKey);
  };
  var getParts = function (component, detail, partKeys) {
    var r = {};
    var uids = detail.partUids();
    var system = component.getSystem();
    $_bvikd2w9jd09f01c.each(partKeys, function (pk) {
      r[pk] = system.getByUid(uids[pk]);
    });
    return $_32a0zdx0jd09f03l.map(r, $_8z5eqrwbjd09f01q.constant);
  };
  var getAllParts = function (component, detail) {
    var system = component.getSystem();
    return $_32a0zdx0jd09f03l.map(detail.partUids(), function (pUid, k) {
      return $_8z5eqrwbjd09f01q.constant(system.getByUid(pUid));
    });
  };
  var getPartsOrDie = function (component, detail, partKeys) {
    var r = {};
    var uids = detail.partUids();
    var system = component.getSystem();
    $_bvikd2w9jd09f01c.each(partKeys, function (pk) {
      r[pk] = system.getByUid(uids[pk]).getOrDie();
    });
    return $_32a0zdx0jd09f03l.map(r, $_8z5eqrwbjd09f01q.constant);
  };
  var defaultUids = function (baseUid, partTypes) {
    var partNames = names(partTypes);
    return $_q0etsx6jd09f053.wrapAll($_bvikd2w9jd09f01c.map(partNames, function (pn) {
      return {
        key: pn,
        value: baseUid + '-' + pn
      };
    }));
  };
  var defaultUidsSchema = function (partTypes) {
    return $_1t7kykx2jd09f043.field('partUids', 'partUids', $_buoc30x3jd09f047.mergeWithThunk(function (spec) {
      return defaultUids(spec.uid, partTypes);
    }), $_33aoy2xhjd09f061.anyValue());
  };
  var $_7vfhnq10ijd09f0iz = {
    generate: generate$2,
    generateOne: generateOne,
    schemas: schemas,
    names: names,
    substitutes: substitutes,
    components: components,
    defaultUids: defaultUids,
    defaultUidsSchema: defaultUidsSchema,
    getAllParts: getAllParts,
    getPart: getPart,
    getPartOrDie: getPartOrDie,
    getParts: getParts,
    getPartsOrDie: getPartsOrDie
  };

  var prefix$1 = 'alloy-id-';
  var idAttr = 'data-alloy-id';
  var $_2t9yx510njd09f0l2 = {
    prefix: $_8z5eqrwbjd09f01q.constant(prefix$1),
    idAttr: $_8z5eqrwbjd09f01q.constant(idAttr)
  };

  var prefix$2 = $_2t9yx510njd09f0l2.prefix();
  var idAttr$1 = $_2t9yx510njd09f0l2.idAttr();
  var write = function (label, elem) {
    var id = $_3x6e2m10gjd09f0ir.generate(prefix$2 + label);
    $_6spjcmxwjd09f07e.set(elem, idAttr$1, id);
    return id;
  };
  var writeOnly = function (elem, uid) {
    $_6spjcmxwjd09f07e.set(elem, idAttr$1, uid);
  };
  var read$2 = function (elem) {
    var id = $_arrpm2xxjd09f07k.isElement(elem) ? $_6spjcmxwjd09f07e.get(elem, idAttr$1) : null;
    return $_8nwhzlwajd09f01m.from(id);
  };
  var find$3 = function (container, id) {
    return $_74vb1xzmjd09f0e3.descendant(container, id);
  };
  var generate$3 = function (prefix) {
    return $_3x6e2m10gjd09f0ir.generate(prefix);
  };
  var revoke = function (elem) {
    $_6spjcmxwjd09f07e.remove(elem, idAttr$1);
  };
  var $_69difv10mjd09f0kv = {
    revoke: revoke,
    write: write,
    writeOnly: writeOnly,
    read: read$2,
    find: find$3,
    generate: generate$3,
    attribute: $_8z5eqrwbjd09f01q.constant(idAttr$1)
  };

  var getPartsSchema = function (partNames, _optPartNames, _owner) {
    var owner = _owner !== undefined ? _owner : 'Unknown owner';
    var fallbackThunk = function () {
      return [$_1l599yytjd09f0ar.output('partUids', {})];
    };
    var optPartNames = _optPartNames !== undefined ? _optPartNames : fallbackThunk();
    if (partNames.length === 0 && optPartNames.length === 0)
      return fallbackThunk();
    var partsSchema = $_1t7kykx2jd09f043.strictObjOf('parts', $_bvikd2w9jd09f01c.flatten([
      $_bvikd2w9jd09f01c.map(partNames, $_1t7kykx2jd09f043.strict),
      $_bvikd2w9jd09f01c.map(optPartNames, function (optPart) {
        return $_1t7kykx2jd09f043.defaulted(optPart, $_ikgvt10ljd09f0k7.single(false, function () {
          throw new Error('The optional part: ' + optPart + ' was not specified in the config, but it was used in components');
        }));
      })
    ]));
    var partUidsSchema = $_1t7kykx2jd09f043.state('partUids', function (spec) {
      if (!$_q0etsx6jd09f053.hasKey(spec, 'parts')) {
        throw new Error('Part uid definition for owner: ' + owner + ' requires "parts"\nExpected parts: ' + partNames.join(', ') + '\nSpec: ' + $_2xfxp7xfjd09f05w.stringify(spec, null, 2));
      }
      var uids = $_32a0zdx0jd09f03l.map(spec.parts, function (v, k) {
        return $_q0etsx6jd09f053.readOptFrom(v, 'uid').getOrThunk(function () {
          return spec.uid + '-' + k;
        });
      });
      return uids;
    });
    return [
      partsSchema,
      partUidsSchema
    ];
  };
  var base$1 = function (label, partSchemas, partUidsSchemas, spec) {
    var ps = partSchemas.length > 0 ? [$_1t7kykx2jd09f043.strictObjOf('parts', partSchemas)] : [];
    return ps.concat([
      $_1t7kykx2jd09f043.strict('uid'),
      $_1t7kykx2jd09f043.defaulted('dom', {}),
      $_1t7kykx2jd09f043.defaulted('components', []),
      $_1l599yytjd09f0ar.snapshot('originalSpec'),
      $_1t7kykx2jd09f043.defaulted('debug.sketcher', {})
    ]).concat(partUidsSchemas);
  };
  var asRawOrDie$1 = function (label, schema, spec, partSchemas, partUidsSchemas) {
    var baseS = base$1(label, partSchemas, spec, partUidsSchemas);
    return $_33aoy2xhjd09f061.asRawOrDie(label + ' [SpecSchema]', $_33aoy2xhjd09f061.objOfOnly(baseS.concat(schema)), spec);
  };
  var asStructOrDie$1 = function (label, schema, spec, partSchemas, partUidsSchemas) {
    var baseS = base$1(label, partSchemas, partUidsSchemas, spec);
    return $_33aoy2xhjd09f061.asStructOrDie(label + ' [SpecSchema]', $_33aoy2xhjd09f061.objOfOnly(baseS.concat(schema)), spec);
  };
  var extend = function (builder, original, nu) {
    var newSpec = $_3htayhwyjd09f03i.deepMerge(original, nu);
    return builder(newSpec);
  };
  var addBehaviours = function (original, behaviours) {
    return $_3htayhwyjd09f03i.deepMerge(original, behaviours);
  };
  var $_frtoav10ojd09f0l6 = {
    asRawOrDie: asRawOrDie$1,
    asStructOrDie: asStructOrDie$1,
    addBehaviours: addBehaviours,
    getPartsSchema: getPartsSchema,
    extend: extend
  };

  var single = function (owner, schema, factory, spec) {
    var specWithUid = supplyUid(spec);
    var detail = $_frtoav10ojd09f0l6.asStructOrDie(owner, schema, specWithUid, [], []);
    return $_3htayhwyjd09f03i.deepMerge(factory(detail, specWithUid), { 'debug.sketcher': $_q0etsx6jd09f053.wrap(owner, spec) });
  };
  var composite = function (owner, schema, partTypes, factory, spec) {
    var specWithUid = supplyUid(spec);
    var partSchemas = $_7vfhnq10ijd09f0iz.schemas(partTypes);
    var partUidsSchema = $_7vfhnq10ijd09f0iz.defaultUidsSchema(partTypes);
    var detail = $_frtoav10ojd09f0l6.asStructOrDie(owner, schema, specWithUid, partSchemas, [partUidsSchema]);
    var subs = $_7vfhnq10ijd09f0iz.substitutes(owner, detail, partTypes);
    var components = $_7vfhnq10ijd09f0iz.components(owner, detail, subs.internals());
    return $_3htayhwyjd09f03i.deepMerge(factory(detail, components, specWithUid, subs.externals()), { 'debug.sketcher': $_q0etsx6jd09f053.wrap(owner, spec) });
  };
  var supplyUid = function (spec) {
    return $_3htayhwyjd09f03i.deepMerge({ uid: $_69difv10mjd09f0kv.generate('uid') }, spec);
  };
  var $_azz8wi10hjd09f0is = {
    supplyUid: supplyUid,
    single: single,
    composite: composite
  };

  var singleSchema = $_33aoy2xhjd09f061.objOfOnly([
    $_1t7kykx2jd09f043.strict('name'),
    $_1t7kykx2jd09f043.strict('factory'),
    $_1t7kykx2jd09f043.strict('configFields'),
    $_1t7kykx2jd09f043.defaulted('apis', {}),
    $_1t7kykx2jd09f043.defaulted('extraApis', {})
  ]);
  var compositeSchema = $_33aoy2xhjd09f061.objOfOnly([
    $_1t7kykx2jd09f043.strict('name'),
    $_1t7kykx2jd09f043.strict('factory'),
    $_1t7kykx2jd09f043.strict('configFields'),
    $_1t7kykx2jd09f043.strict('partFields'),
    $_1t7kykx2jd09f043.defaulted('apis', {}),
    $_1t7kykx2jd09f043.defaulted('extraApis', {})
  ]);
  var single$1 = function (rawConfig) {
    var config = $_33aoy2xhjd09f061.asRawOrDie('Sketcher for ' + rawConfig.name, singleSchema, rawConfig);
    var sketch = function (spec) {
      return $_azz8wi10hjd09f0is.single(config.name, config.configFields, config.factory, spec);
    };
    var apis = $_32a0zdx0jd09f03l.map(config.apis, $_fdvkh210fjd09f0ij.makeApi);
    var extraApis = $_32a0zdx0jd09f03l.map(config.extraApis, function (f, k) {
      return $_8a1c5yxjjd09f06g.markAsExtraApi(f, k);
    });
    return $_3htayhwyjd09f03i.deepMerge({
      name: $_8z5eqrwbjd09f01q.constant(config.name),
      partFields: $_8z5eqrwbjd09f01q.constant([]),
      configFields: $_8z5eqrwbjd09f01q.constant(config.configFields),
      sketch: sketch
    }, apis, extraApis);
  };
  var composite$1 = function (rawConfig) {
    var config = $_33aoy2xhjd09f061.asRawOrDie('Sketcher for ' + rawConfig.name, compositeSchema, rawConfig);
    var sketch = function (spec) {
      return $_azz8wi10hjd09f0is.composite(config.name, config.configFields, config.partFields, config.factory, spec);
    };
    var parts = $_7vfhnq10ijd09f0iz.generate(config.name, config.partFields);
    var apis = $_32a0zdx0jd09f03l.map(config.apis, $_fdvkh210fjd09f0ij.makeApi);
    var extraApis = $_32a0zdx0jd09f03l.map(config.extraApis, function (f, k) {
      return $_8a1c5yxjjd09f06g.markAsExtraApi(f, k);
    });
    return $_3htayhwyjd09f03i.deepMerge({
      name: $_8z5eqrwbjd09f01q.constant(config.name),
      partFields: $_8z5eqrwbjd09f01q.constant(config.partFields),
      configFields: $_8z5eqrwbjd09f01q.constant(config.configFields),
      sketch: sketch,
      parts: $_8z5eqrwbjd09f01q.constant(parts)
    }, apis, extraApis);
  };
  var $_4q1unv10ejd09f0i8 = {
    single: single$1,
    composite: composite$1
  };

  var events$4 = function (optAction) {
    var executeHandler = function (action) {
      return $_6j84lww6jd09f00y.run($_5iytewwjd09f03c.execute(), function (component, simulatedEvent) {
        action(component);
        simulatedEvent.stop();
      });
    };
    var onClick = function (component, simulatedEvent) {
      simulatedEvent.stop();
      $_3b6lb8wvjd09f037.emitExecute(component);
    };
    var onMousedown = function (component, simulatedEvent) {
      simulatedEvent.cut();
    };
    var pointerEvents = $_8zynflwgjd09f01z.detect().deviceType.isTouch() ? [$_6j84lww6jd09f00y.run($_5iytewwjd09f03c.tap(), onClick)] : [
      $_6j84lww6jd09f00y.run($_8nqyjjwxjd09f03f.click(), onClick),
      $_6j84lww6jd09f00y.run($_8nqyjjwxjd09f03f.mousedown(), onMousedown)
    ];
    return $_6j84lww6jd09f00y.derive($_bvikd2w9jd09f01c.flatten([
      optAction.map(executeHandler).toArray(),
      pointerEvents
    ]));
  };
  var $_4ss7db10pjd09f0lh = { events: events$4 };

  var factory = function (detail, spec) {
    var events = $_4ss7db10pjd09f0lh.events(detail.action());
    var optType = $_q0etsx6jd09f053.readOptFrom(detail.dom(), 'attributes').bind($_q0etsx6jd09f053.readOpt('type'));
    var optTag = $_q0etsx6jd09f053.readOptFrom(detail.dom(), 'tag');
    return {
      uid: detail.uid(),
      dom: detail.dom(),
      components: detail.components(),
      events: events,
      behaviours: $_3htayhwyjd09f03i.deepMerge($_fq8al5w4jd09f00e.derive([
        Focusing.config({}),
        Keying.config({
          mode: 'execution',
          useSpace: true,
          useEnter: true
        })
      ]), $_beskyy10djd09f0i0.get(detail.buttonBehaviours())),
      domModification: {
        attributes: $_3htayhwyjd09f03i.deepMerge(optType.fold(function () {
          return optTag.is('button') ? { type: 'button' } : {};
        }, function (t) {
          return {};
        }), { role: detail.role().getOr('button') })
      },
      eventOrder: detail.eventOrder()
    };
  };
  var Button = $_4q1unv10ejd09f0i8.single({
    name: 'Button',
    factory: factory,
    configFields: [
      $_1t7kykx2jd09f043.defaulted('uid', undefined),
      $_1t7kykx2jd09f043.strict('dom'),
      $_1t7kykx2jd09f043.defaulted('components', []),
      $_beskyy10djd09f0i0.field('buttonBehaviours', [
        Focusing,
        Keying
      ]),
      $_1t7kykx2jd09f043.option('action'),
      $_1t7kykx2jd09f043.option('role'),
      $_1t7kykx2jd09f043.defaulted('eventOrder', {})
    ]
  });

  var getAttrs = function (elem) {
    var attributes = elem.dom().attributes !== undefined ? elem.dom().attributes : [];
    return $_bvikd2w9jd09f01c.foldl(attributes, function (b, attr) {
      if (attr.name === 'class')
        return b;
      else
        return $_3htayhwyjd09f03i.deepMerge(b, $_q0etsx6jd09f053.wrap(attr.name, attr.value));
    }, {});
  };
  var getClasses = function (elem) {
    return Array.prototype.slice.call(elem.dom().classList, 0);
  };
  var fromHtml$2 = function (html) {
    var elem = $_cnbf2uwtjd09f033.fromHtml(html);
    var children = $_sdd74y3jd09f08a.children(elem);
    var attrs = getAttrs(elem);
    var classes = getClasses(elem);
    var contents = children.length === 0 ? {} : { innerHtml: $_ck4yooybjd09f095.get(elem) };
    return $_3htayhwyjd09f03i.deepMerge({
      tag: $_arrpm2xxjd09f07k.name(elem),
      classes: classes,
      attributes: attrs
    }, contents);
  };
  var sketch = function (sketcher, html, config) {
    return sketcher.sketch($_3htayhwyjd09f03i.deepMerge({ dom: fromHtml$2(html) }, config));
  };
  var $_6sy65p10rjd09f0lo = {
    fromHtml: fromHtml$2,
    sketch: sketch
  };

  var dom$1 = function (rawHtml) {
    var html = $_7bomvkwpjd09f02u.supplant(rawHtml, { prefix: $_3584u7z1jd09f0bs.prefix() });
    return $_6sy65p10rjd09f0lo.fromHtml(html);
  };
  var spec = function (rawHtml) {
    var sDom = dom$1(rawHtml);
    return { dom: sDom };
  };
  var $_1sirkj10qjd09f0ll = {
    dom: dom$1,
    spec: spec
  };

  var forToolbarCommand = function (editor, command) {
    return forToolbar(command, function () {
      editor.execCommand(command);
    }, {});
  };
  var getToggleBehaviours = function (command) {
    return $_fq8al5w4jd09f00e.derive([
      Toggling.config({
        toggleClass: $_3584u7z1jd09f0bs.resolve('toolbar-button-selected'),
        toggleOnExecute: false,
        aria: { mode: 'pressed' }
      }),
      $_2jq3vfz0jd09f0bp.format(command, function (button, status) {
        var toggle = status ? Toggling.on : Toggling.off;
        toggle(button);
      })
    ]);
  };
  var forToolbarStateCommand = function (editor, command) {
    var extraBehaviours = getToggleBehaviours(command);
    return forToolbar(command, function () {
      editor.execCommand(command);
    }, extraBehaviours);
  };
  var forToolbarStateAction = function (editor, clazz, command, action) {
    var extraBehaviours = getToggleBehaviours(command);
    return forToolbar(clazz, action, extraBehaviours);
  };
  var forToolbar = function (clazz, action, extraBehaviours) {
    return Button.sketch({
      dom: $_1sirkj10qjd09f0ll.dom('<span class="${prefix}-toolbar-button ${prefix}-icon-' + clazz + ' ${prefix}-icon"></span>'),
      action: action,
      buttonBehaviours: $_3htayhwyjd09f03i.deepMerge($_fq8al5w4jd09f00e.derive([Unselecting.config({})]), extraBehaviours)
    });
  };
  var $_ch9li7z2jd09f0c0 = {
    forToolbar: forToolbar,
    forToolbarCommand: forToolbarCommand,
    forToolbarStateAction: forToolbarStateAction,
    forToolbarStateCommand: forToolbarStateCommand
  };

  var reduceBy = function (value, min, max, step) {
    if (value < min)
      return value;
    else if (value > max)
      return max;
    else if (value === min)
      return min - 1;
    else
      return Math.max(min, value - step);
  };
  var increaseBy = function (value, min, max, step) {
    if (value > max)
      return value;
    else if (value < min)
      return min;
    else if (value === max)
      return max + 1;
    else
      return Math.min(max, value + step);
  };
  var capValue = function (value, min, max) {
    return Math.max(min, Math.min(max, value));
  };
  var snapValueOfX = function (bounds, value, min, max, step, snapStart) {
    return snapStart.fold(function () {
      var initValue = value - min;
      var extraValue = Math.round(initValue / step) * step;
      return capValue(min + extraValue, min - 1, max + 1);
    }, function (start) {
      var remainder = (value - start) % step;
      var adjustment = Math.round(remainder / step);
      var rawSteps = Math.floor((value - start) / step);
      var maxSteps = Math.floor((max - start) / step);
      var numSteps = Math.min(maxSteps, rawSteps + adjustment);
      var r = start + numSteps * step;
      return Math.max(start, r);
    });
  };
  var findValueOfX = function (bounds, min, max, xValue, step, snapToGrid, snapStart) {
    var range = max - min;
    if (xValue < bounds.left)
      return min - 1;
    else if (xValue > bounds.right)
      return max + 1;
    else {
      var xOffset = Math.min(bounds.right, Math.max(xValue, bounds.left)) - bounds.left;
      var newValue = capValue(xOffset / bounds.width * range + min, min - 1, max + 1);
      var roundedValue = Math.round(newValue);
      return snapToGrid && newValue >= min && newValue <= max ? snapValueOfX(bounds, newValue, min, max, step, snapStart) : roundedValue;
    }
  };
  var $_dgdqbk10wjd09f0mq = {
    reduceBy: reduceBy,
    increaseBy: increaseBy,
    findValueOfX: findValueOfX
  };

  var changeEvent = 'slider.change.value';
  var isTouch = $_8zynflwgjd09f01z.detect().deviceType.isTouch();
  var getEventSource = function (simulatedEvent) {
    var evt = simulatedEvent.event().raw();
    if (isTouch && evt.touches !== undefined && evt.touches.length === 1)
      return $_8nwhzlwajd09f01m.some(evt.touches[0]);
    else if (isTouch && evt.touches !== undefined)
      return $_8nwhzlwajd09f01m.none();
    else if (!isTouch && evt.clientX !== undefined)
      return $_8nwhzlwajd09f01m.some(evt);
    else
      return $_8nwhzlwajd09f01m.none();
  };
  var getEventX = function (simulatedEvent) {
    var spot = getEventSource(simulatedEvent);
    return spot.map(function (s) {
      return s.clientX;
    });
  };
  var fireChange = function (component, value) {
    $_3b6lb8wvjd09f037.emitWith(component, changeEvent, { value: value });
  };
  var moveRightFromLedge = function (ledge, detail) {
    fireChange(ledge, detail.min());
  };
  var moveLeftFromRedge = function (redge, detail) {
    fireChange(redge, detail.max());
  };
  var setToRedge = function (redge, detail) {
    fireChange(redge, detail.max() + 1);
  };
  var setToLedge = function (ledge, detail) {
    fireChange(ledge, detail.min() - 1);
  };
  var setToX = function (spectrum, spectrumBounds, detail, xValue) {
    var value = $_dgdqbk10wjd09f0mq.findValueOfX(spectrumBounds, detail.min(), detail.max(), xValue, detail.stepSize(), detail.snapToGrid(), detail.snapStart());
    fireChange(spectrum, value);
  };
  var setXFromEvent = function (spectrum, detail, spectrumBounds, simulatedEvent) {
    return getEventX(simulatedEvent).map(function (xValue) {
      setToX(spectrum, spectrumBounds, detail, xValue);
      return xValue;
    });
  };
  var moveLeft$4 = function (spectrum, detail) {
    var newValue = $_dgdqbk10wjd09f0mq.reduceBy(detail.value().get(), detail.min(), detail.max(), detail.stepSize());
    fireChange(spectrum, newValue);
  };
  var moveRight$4 = function (spectrum, detail) {
    var newValue = $_dgdqbk10wjd09f0mq.increaseBy(detail.value().get(), detail.min(), detail.max(), detail.stepSize());
    fireChange(spectrum, newValue);
  };
  var $_fg8j5y10vjd09f0mk = {
    setXFromEvent: setXFromEvent,
    setToLedge: setToLedge,
    setToRedge: setToRedge,
    moveLeftFromRedge: moveLeftFromRedge,
    moveRightFromLedge: moveRightFromLedge,
    moveLeft: moveLeft$4,
    moveRight: moveRight$4,
    changeEvent: $_8z5eqrwbjd09f01q.constant(changeEvent)
  };

  var platform = $_8zynflwgjd09f01z.detect();
  var isTouch$1 = platform.deviceType.isTouch();
  var edgePart = function (name, action) {
    return $_37ihar10kjd09f0js.optional({
      name: '' + name + '-edge',
      overrides: function (detail) {
        var touchEvents = $_6j84lww6jd09f00y.derive([$_6j84lww6jd09f00y.runActionExtra($_8nqyjjwxjd09f03f.touchstart(), action, [detail])]);
        var mouseEvents = $_6j84lww6jd09f00y.derive([
          $_6j84lww6jd09f00y.runActionExtra($_8nqyjjwxjd09f03f.mousedown(), action, [detail]),
          $_6j84lww6jd09f00y.runActionExtra($_8nqyjjwxjd09f03f.mousemove(), function (l, det) {
            if (det.mouseIsDown().get())
              action(l, det);
          }, [detail])
        ]);
        return { events: isTouch$1 ? touchEvents : mouseEvents };
      }
    });
  };
  var ledgePart = edgePart('left', $_fg8j5y10vjd09f0mk.setToLedge);
  var redgePart = edgePart('right', $_fg8j5y10vjd09f0mk.setToRedge);
  var thumbPart = $_37ihar10kjd09f0js.required({
    name: 'thumb',
    defaults: $_8z5eqrwbjd09f01q.constant({ dom: { styles: { position: 'absolute' } } }),
    overrides: function (detail) {
      return {
        events: $_6j84lww6jd09f00y.derive([
          $_6j84lww6jd09f00y.redirectToPart($_8nqyjjwxjd09f03f.touchstart(), detail, 'spectrum'),
          $_6j84lww6jd09f00y.redirectToPart($_8nqyjjwxjd09f03f.touchmove(), detail, 'spectrum'),
          $_6j84lww6jd09f00y.redirectToPart($_8nqyjjwxjd09f03f.touchend(), detail, 'spectrum')
        ])
      };
    }
  });
  var spectrumPart = $_37ihar10kjd09f0js.required({
    schema: [$_1t7kykx2jd09f043.state('mouseIsDown', function () {
        return Cell(false);
      })],
    name: 'spectrum',
    overrides: function (detail) {
      var moveToX = function (spectrum, simulatedEvent) {
        var spectrumBounds = spectrum.element().dom().getBoundingClientRect();
        $_fg8j5y10vjd09f0mk.setXFromEvent(spectrum, detail, spectrumBounds, simulatedEvent);
      };
      var touchEvents = $_6j84lww6jd09f00y.derive([
        $_6j84lww6jd09f00y.run($_8nqyjjwxjd09f03f.touchstart(), moveToX),
        $_6j84lww6jd09f00y.run($_8nqyjjwxjd09f03f.touchmove(), moveToX)
      ]);
      var mouseEvents = $_6j84lww6jd09f00y.derive([
        $_6j84lww6jd09f00y.run($_8nqyjjwxjd09f03f.mousedown(), moveToX),
        $_6j84lww6jd09f00y.run($_8nqyjjwxjd09f03f.mousemove(), function (spectrum, se) {
          if (detail.mouseIsDown().get())
            moveToX(spectrum, se);
        })
      ]);
      return {
        behaviours: $_fq8al5w4jd09f00e.derive(isTouch$1 ? [] : [
          Keying.config({
            mode: 'special',
            onLeft: function (spectrum) {
              $_fg8j5y10vjd09f0mk.moveLeft(spectrum, detail);
              return $_8nwhzlwajd09f01m.some(true);
            },
            onRight: function (spectrum) {
              $_fg8j5y10vjd09f0mk.moveRight(spectrum, detail);
              return $_8nwhzlwajd09f01m.some(true);
            }
          }),
          Focusing.config({})
        ]),
        events: isTouch$1 ? touchEvents : mouseEvents
      };
    }
  });
  var SliderParts = [
    ledgePart,
    redgePart,
    thumbPart,
    spectrumPart
  ];

  var onLoad$1 = function (component, repConfig, repState) {
    repConfig.store().manager().onLoad(component, repConfig, repState);
  };
  var onUnload = function (component, repConfig, repState) {
    repConfig.store().manager().onUnload(component, repConfig, repState);
  };
  var setValue = function (component, repConfig, repState, data) {
    repConfig.store().manager().setValue(component, repConfig, repState, data);
  };
  var getValue = function (component, repConfig, repState) {
    return repConfig.store().manager().getValue(component, repConfig, repState);
  };
  var $_euqsz1110jd09f0n1 = {
    onLoad: onLoad$1,
    onUnload: onUnload,
    setValue: setValue,
    getValue: getValue
  };

  var events$5 = function (repConfig, repState) {
    var es = repConfig.resetOnDom() ? [
      $_6j84lww6jd09f00y.runOnAttached(function (comp, se) {
        $_euqsz1110jd09f0n1.onLoad(comp, repConfig, repState);
      }),
      $_6j84lww6jd09f00y.runOnDetached(function (comp, se) {
        $_euqsz1110jd09f0n1.onUnload(comp, repConfig, repState);
      })
    ] : [$_8zny04w5jd09f00m.loadEvent(repConfig, repState, $_euqsz1110jd09f0n1.onLoad)];
    return $_6j84lww6jd09f00y.derive(es);
  };
  var $_2p0kjh10zjd09f0n0 = { events: events$5 };

  var memory = function () {
    var data = Cell(null);
    var readState = function () {
      return {
        mode: 'memory',
        value: data.get()
      };
    };
    var isNotSet = function () {
      return data.get() === null;
    };
    var clear = function () {
      data.set(null);
    };
    return BehaviourState({
      set: data.set,
      get: data.get,
      isNotSet: isNotSet,
      clear: clear,
      readState: readState
    });
  };
  var manual = function () {
    var readState = function () {
    };
    return BehaviourState({ readState: readState });
  };
  var dataset = function () {
    var data = Cell({});
    var readState = function () {
      return {
        mode: 'dataset',
        dataset: data.get()
      };
    };
    return BehaviourState({
      readState: readState,
      set: data.set,
      get: data.get
    });
  };
  var init$2 = function (spec) {
    return spec.store().manager().state(spec);
  };
  var $_rvc20113jd09f0na = {
    memory: memory,
    dataset: dataset,
    manual: manual,
    init: init$2
  };

  var setValue$1 = function (component, repConfig, repState, data) {
    var dataKey = repConfig.store().getDataKey();
    repState.set({});
    repConfig.store().setData()(component, data);
    repConfig.onSetValue()(component, data);
  };
  var getValue$1 = function (component, repConfig, repState) {
    var key = repConfig.store().getDataKey()(component);
    var dataset = repState.get();
    return $_q0etsx6jd09f053.readOptFrom(dataset, key).fold(function () {
      return repConfig.store().getFallbackEntry()(key);
    }, function (data) {
      return data;
    });
  };
  var onLoad$2 = function (component, repConfig, repState) {
    repConfig.store().initialValue().each(function (data) {
      setValue$1(component, repConfig, repState, data);
    });
  };
  var onUnload$1 = function (component, repConfig, repState) {
    repState.set({});
  };
  var DatasetStore = [
    $_1t7kykx2jd09f043.option('initialValue'),
    $_1t7kykx2jd09f043.strict('getFallbackEntry'),
    $_1t7kykx2jd09f043.strict('getDataKey'),
    $_1t7kykx2jd09f043.strict('setData'),
    $_1l599yytjd09f0ar.output('manager', {
      setValue: setValue$1,
      getValue: getValue$1,
      onLoad: onLoad$2,
      onUnload: onUnload$1,
      state: $_rvc20113jd09f0na.dataset
    })
  ];

  var getValue$2 = function (component, repConfig, repState) {
    return repConfig.store().getValue()(component);
  };
  var setValue$2 = function (component, repConfig, repState, data) {
    repConfig.store().setValue()(component, data);
    repConfig.onSetValue()(component, data);
  };
  var onLoad$3 = function (component, repConfig, repState) {
    repConfig.store().initialValue().each(function (data) {
      repConfig.store().setValue()(component, data);
    });
  };
  var ManualStore = [
    $_1t7kykx2jd09f043.strict('getValue'),
    $_1t7kykx2jd09f043.defaulted('setValue', $_8z5eqrwbjd09f01q.noop),
    $_1t7kykx2jd09f043.option('initialValue'),
    $_1l599yytjd09f0ar.output('manager', {
      setValue: setValue$2,
      getValue: getValue$2,
      onLoad: onLoad$3,
      onUnload: $_8z5eqrwbjd09f01q.noop,
      state: $_7dmh49xqjd09f072.init
    })
  ];

  var setValue$3 = function (component, repConfig, repState, data) {
    repState.set(data);
    repConfig.onSetValue()(component, data);
  };
  var getValue$3 = function (component, repConfig, repState) {
    return repState.get();
  };
  var onLoad$4 = function (component, repConfig, repState) {
    repConfig.store().initialValue().each(function (initVal) {
      if (repState.isNotSet())
        repState.set(initVal);
    });
  };
  var onUnload$2 = function (component, repConfig, repState) {
    repState.clear();
  };
  var MemoryStore = [
    $_1t7kykx2jd09f043.option('initialValue'),
    $_1l599yytjd09f0ar.output('manager', {
      setValue: setValue$3,
      getValue: getValue$3,
      onLoad: onLoad$4,
      onUnload: onUnload$2,
      state: $_rvc20113jd09f0na.memory
    })
  ];

  var RepresentSchema = [
    $_1t7kykx2jd09f043.defaultedOf('store', { mode: 'memory' }, $_33aoy2xhjd09f061.choose('mode', {
      memory: MemoryStore,
      manual: ManualStore,
      dataset: DatasetStore
    })),
    $_1l599yytjd09f0ar.onHandler('onSetValue'),
    $_1t7kykx2jd09f043.defaulted('resetOnDom', false)
  ];

  var me = $_fq8al5w4jd09f00e.create({
    fields: RepresentSchema,
    name: 'representing',
    active: $_2p0kjh10zjd09f0n0,
    apis: $_euqsz1110jd09f0n1,
    extra: {
      setValueFrom: function (component, source) {
        var value = me.getValue(source);
        me.setValue(component, value);
      }
    },
    state: $_rvc20113jd09f0na
  });

  var isTouch$2 = $_8zynflwgjd09f01z.detect().deviceType.isTouch();
  var SliderSchema = [
    $_1t7kykx2jd09f043.strict('min'),
    $_1t7kykx2jd09f043.strict('max'),
    $_1t7kykx2jd09f043.defaulted('stepSize', 1),
    $_1t7kykx2jd09f043.defaulted('onChange', $_8z5eqrwbjd09f01q.noop),
    $_1t7kykx2jd09f043.defaulted('onInit', $_8z5eqrwbjd09f01q.noop),
    $_1t7kykx2jd09f043.defaulted('onDragStart', $_8z5eqrwbjd09f01q.noop),
    $_1t7kykx2jd09f043.defaulted('onDragEnd', $_8z5eqrwbjd09f01q.noop),
    $_1t7kykx2jd09f043.defaulted('snapToGrid', false),
    $_1t7kykx2jd09f043.option('snapStart'),
    $_1t7kykx2jd09f043.strict('getInitialValue'),
    $_beskyy10djd09f0i0.field('sliderBehaviours', [
      Keying,
      me
    ]),
    $_1t7kykx2jd09f043.state('value', function (spec) {
      return Cell(spec.min);
    })
  ].concat(!isTouch$2 ? [$_1t7kykx2jd09f043.state('mouseIsDown', function () {
      return Cell(false);
    })] : []);

  var api$1 = Dimension('width', function (element) {
    return element.dom().offsetWidth;
  });
  var set$4 = function (element, h) {
    api$1.set(element, h);
  };
  var get$6 = function (element) {
    return api$1.get(element);
  };
  var getOuter$2 = function (element) {
    return api$1.getOuter(element);
  };
  var setMax$1 = function (element, value) {
    var inclusions = [
      'margin-left',
      'border-left-width',
      'padding-left',
      'padding-right',
      'border-right-width',
      'margin-right'
    ];
    var absMax = api$1.max(element, value, inclusions);
    $_a9ctnkzsjd09f0ej.set(element, 'max-width', absMax + 'px');
  };
  var $_cyexp9117jd09f0o3 = {
    set: set$4,
    get: get$6,
    getOuter: getOuter$2,
    setMax: setMax$1
  };

  var isTouch$3 = $_8zynflwgjd09f01z.detect().deviceType.isTouch();
  var sketch$1 = function (detail, components, spec, externals) {
    var range = detail.max() - detail.min();
    var getXCentre = function (component) {
      var rect = component.element().dom().getBoundingClientRect();
      return (rect.left + rect.right) / 2;
    };
    var getThumb = function (component) {
      return $_7vfhnq10ijd09f0iz.getPartOrDie(component, detail, 'thumb');
    };
    var getXOffset = function (slider, spectrumBounds, detail) {
      var v = detail.value().get();
      if (v < detail.min()) {
        return $_7vfhnq10ijd09f0iz.getPart(slider, detail, 'left-edge').fold(function () {
          return 0;
        }, function (ledge) {
          return getXCentre(ledge) - spectrumBounds.left;
        });
      } else if (v > detail.max()) {
        return $_7vfhnq10ijd09f0iz.getPart(slider, detail, 'right-edge').fold(function () {
          return spectrumBounds.width;
        }, function (redge) {
          return getXCentre(redge) - spectrumBounds.left;
        });
      } else {
        return (detail.value().get() - detail.min()) / range * spectrumBounds.width;
      }
    };
    var getXPos = function (slider) {
      var spectrum = $_7vfhnq10ijd09f0iz.getPartOrDie(slider, detail, 'spectrum');
      var spectrumBounds = spectrum.element().dom().getBoundingClientRect();
      var sliderBounds = slider.element().dom().getBoundingClientRect();
      var xOffset = getXOffset(slider, spectrumBounds, detail);
      return spectrumBounds.left - sliderBounds.left + xOffset;
    };
    var refresh = function (component) {
      var pos = getXPos(component);
      var thumb = getThumb(component);
      var thumbRadius = $_cyexp9117jd09f0o3.get(thumb.element()) / 2;
      $_a9ctnkzsjd09f0ej.set(thumb.element(), 'left', pos - thumbRadius + 'px');
    };
    var changeValue = function (component, newValue) {
      var oldValue = detail.value().get();
      var thumb = getThumb(component);
      if (oldValue !== newValue || $_a9ctnkzsjd09f0ej.getRaw(thumb.element(), 'left').isNone()) {
        detail.value().set(newValue);
        refresh(component);
        detail.onChange()(component, thumb, newValue);
        return $_8nwhzlwajd09f01m.some(true);
      } else {
        return $_8nwhzlwajd09f01m.none();
      }
    };
    var resetToMin = function (slider) {
      changeValue(slider, detail.min());
    };
    var resetToMax = function (slider) {
      changeValue(slider, detail.max());
    };
    var uiEventsArr = isTouch$3 ? [
      $_6j84lww6jd09f00y.run($_8nqyjjwxjd09f03f.touchstart(), function (slider, simulatedEvent) {
        detail.onDragStart()(slider, getThumb(slider));
      }),
      $_6j84lww6jd09f00y.run($_8nqyjjwxjd09f03f.touchend(), function (slider, simulatedEvent) {
        detail.onDragEnd()(slider, getThumb(slider));
      })
    ] : [
      $_6j84lww6jd09f00y.run($_8nqyjjwxjd09f03f.mousedown(), function (slider, simulatedEvent) {
        simulatedEvent.stop();
        detail.onDragStart()(slider, getThumb(slider));
        detail.mouseIsDown().set(true);
      }),
      $_6j84lww6jd09f00y.run($_8nqyjjwxjd09f03f.mouseup(), function (slider, simulatedEvent) {
        detail.onDragEnd()(slider, getThumb(slider));
        detail.mouseIsDown().set(false);
      })
    ];
    return {
      uid: detail.uid(),
      dom: detail.dom(),
      components: components,
      behaviours: $_3htayhwyjd09f03i.deepMerge($_fq8al5w4jd09f00e.derive($_bvikd2w9jd09f01c.flatten([
        !isTouch$3 ? [Keying.config({
            mode: 'special',
            focusIn: function (slider) {
              return $_7vfhnq10ijd09f0iz.getPart(slider, detail, 'spectrum').map(Keying.focusIn).map($_8z5eqrwbjd09f01q.constant(true));
            }
          })] : [],
        [me.config({
            store: {
              mode: 'manual',
              getValue: function (_) {
                return detail.value().get();
              }
            }
          })]
      ])), $_beskyy10djd09f0i0.get(detail.sliderBehaviours())),
      events: $_6j84lww6jd09f00y.derive([
        $_6j84lww6jd09f00y.run($_fg8j5y10vjd09f0mk.changeEvent(), function (slider, simulatedEvent) {
          changeValue(slider, simulatedEvent.event().value());
        }),
        $_6j84lww6jd09f00y.runOnAttached(function (slider, simulatedEvent) {
          detail.value().set(detail.getInitialValue()());
          var thumb = getThumb(slider);
          refresh(slider);
          detail.onInit()(slider, thumb, detail.value().get());
        })
      ].concat(uiEventsArr)),
      apis: {
        resetToMin: resetToMin,
        resetToMax: resetToMax,
        refresh: refresh
      },
      domModification: { styles: { position: 'relative' } }
    };
  };
  var $_2zi4jg116jd09f0np = { sketch: sketch$1 };

  var Slider = $_4q1unv10ejd09f0i8.composite({
    name: 'Slider',
    configFields: SliderSchema,
    partFields: SliderParts,
    factory: $_2zi4jg116jd09f0np.sketch,
    apis: {
      resetToMin: function (apis, slider) {
        apis.resetToMin(slider);
      },
      resetToMax: function (apis, slider) {
        apis.resetToMax(slider);
      },
      refresh: function (apis, slider) {
        apis.refresh(slider);
      }
    }
  });

  var button = function (realm, clazz, makeItems) {
    return $_ch9li7z2jd09f0c0.forToolbar(clazz, function () {
      var items = makeItems();
      realm.setContextToolbar([{
          label: clazz + ' group',
          items: items
        }]);
    }, {});
  };
  var $_a6gg0q118jd09f0o5 = { button: button };

  var BLACK = -1;
  var makeSlider = function (spec) {
    var getColor = function (hue) {
      if (hue < 0) {
        return 'black';
      } else if (hue > 360) {
        return 'white';
      } else {
        return 'hsl(' + hue + ', 100%, 50%)';
      }
    };
    var onInit = function (slider, thumb, value) {
      var color = getColor(value);
      $_a9ctnkzsjd09f0ej.set(thumb.element(), 'background-color', color);
    };
    var onChange = function (slider, thumb, value) {
      var color = getColor(value);
      $_a9ctnkzsjd09f0ej.set(thumb.element(), 'background-color', color);
      spec.onChange(slider, thumb, color);
    };
    return Slider.sketch({
      dom: $_1sirkj10qjd09f0ll.dom('<div class="${prefix}-slider ${prefix}-hue-slider-container"></div>'),
      components: [
        Slider.parts()['left-edge']($_1sirkj10qjd09f0ll.spec('<div class="${prefix}-hue-slider-black"></div>')),
        Slider.parts().spectrum({
          dom: $_1sirkj10qjd09f0ll.dom('<div class="${prefix}-slider-gradient-container"></div>'),
          components: [$_1sirkj10qjd09f0ll.spec('<div class="${prefix}-slider-gradient"></div>')],
          behaviours: $_fq8al5w4jd09f00e.derive([Toggling.config({ toggleClass: $_3584u7z1jd09f0bs.resolve('thumb-active') })])
        }),
        Slider.parts()['right-edge']($_1sirkj10qjd09f0ll.spec('<div class="${prefix}-hue-slider-white"></div>')),
        Slider.parts().thumb({
          dom: $_1sirkj10qjd09f0ll.dom('<div class="${prefix}-slider-thumb"></div>'),
          behaviours: $_fq8al5w4jd09f00e.derive([Toggling.config({ toggleClass: $_3584u7z1jd09f0bs.resolve('thumb-active') })])
        })
      ],
      onChange: onChange,
      onDragStart: function (slider, thumb) {
        Toggling.on(thumb);
      },
      onDragEnd: function (slider, thumb) {
        Toggling.off(thumb);
      },
      onInit: onInit,
      stepSize: 10,
      min: 0,
      max: 360,
      getInitialValue: spec.getInitialValue,
      sliderBehaviours: $_fq8al5w4jd09f00e.derive([$_2jq3vfz0jd09f0bp.orientation(Slider.refresh)])
    });
  };
  var makeItems = function (spec) {
    return [makeSlider(spec)];
  };
  var sketch$2 = function (realm, editor) {
    var spec = {
      onChange: function (slider, thumb, color) {
        editor.undoManager.transact(function () {
          editor.formatter.apply('forecolor', { value: color });
          editor.nodeChanged();
        });
      },
      getInitialValue: function () {
        return BLACK;
      }
    };
    return $_a6gg0q118jd09f0o5.button(realm, 'color', function () {
      return makeItems(spec);
    });
  };
  var $_1nrbo710sjd09f0ly = {
    makeItems: makeItems,
    sketch: sketch$2
  };

  var schema$7 = $_33aoy2xhjd09f061.objOfOnly([
    $_1t7kykx2jd09f043.strict('getInitialValue'),
    $_1t7kykx2jd09f043.strict('onChange'),
    $_1t7kykx2jd09f043.strict('category'),
    $_1t7kykx2jd09f043.strict('sizes')
  ]);
  var sketch$3 = function (rawSpec) {
    var spec = $_33aoy2xhjd09f061.asRawOrDie('SizeSlider', schema$7, rawSpec);
    var isValidValue = function (valueIndex) {
      return valueIndex >= 0 && valueIndex < spec.sizes.length;
    };
    var onChange = function (slider, thumb, valueIndex) {
      if (isValidValue(valueIndex)) {
        spec.onChange(valueIndex);
      }
    };
    return Slider.sketch({
      dom: {
        tag: 'div',
        classes: [
          $_3584u7z1jd09f0bs.resolve('slider-' + spec.category + '-size-container'),
          $_3584u7z1jd09f0bs.resolve('slider'),
          $_3584u7z1jd09f0bs.resolve('slider-size-container')
        ]
      },
      onChange: onChange,
      onDragStart: function (slider, thumb) {
        Toggling.on(thumb);
      },
      onDragEnd: function (slider, thumb) {
        Toggling.off(thumb);
      },
      min: 0,
      max: spec.sizes.length - 1,
      stepSize: 1,
      getInitialValue: spec.getInitialValue,
      snapToGrid: true,
      sliderBehaviours: $_fq8al5w4jd09f00e.derive([$_2jq3vfz0jd09f0bp.orientation(Slider.refresh)]),
      components: [
        Slider.parts().spectrum({
          dom: $_1sirkj10qjd09f0ll.dom('<div class="${prefix}-slider-size-container"></div>'),
          components: [$_1sirkj10qjd09f0ll.spec('<div class="${prefix}-slider-size-line"></div>')]
        }),
        Slider.parts().thumb({
          dom: $_1sirkj10qjd09f0ll.dom('<div class="${prefix}-slider-thumb"></div>'),
          behaviours: $_fq8al5w4jd09f00e.derive([Toggling.config({ toggleClass: $_3584u7z1jd09f0bs.resolve('thumb-active') })])
        })
      ]
    });
  };
  var $_9s0r1b11ajd09f0o8 = { sketch: sketch$3 };

  var ancestor$3 = function (scope, transform, isRoot) {
    var element = scope.dom();
    var stop = $_4biushwzjd09f03k.isFunction(isRoot) ? isRoot : $_8z5eqrwbjd09f01q.constant(false);
    while (element.parentNode) {
      element = element.parentNode;
      var el = $_cnbf2uwtjd09f033.fromDom(element);
      var transformed = transform(el);
      if (transformed.isSome())
        return transformed;
      else if (stop(el))
        break;
    }
    return $_8nwhzlwajd09f01m.none();
  };
  var closest$3 = function (scope, transform, isRoot) {
    var current = transform(scope);
    return current.orThunk(function () {
      return isRoot(scope) ? $_8nwhzlwajd09f01m.none() : ancestor$3(scope, transform, isRoot);
    });
  };
  var $_9o1wlo11cjd09f0ot = {
    ancestor: ancestor$3,
    closest: closest$3
  };

  var candidates = [
    '9px',
    '10px',
    '11px',
    '12px',
    '14px',
    '16px',
    '18px',
    '20px',
    '24px',
    '32px',
    '36px'
  ];
  var defaultSize = 'medium';
  var defaultIndex = 2;
  var indexToSize = function (index) {
    return $_8nwhzlwajd09f01m.from(candidates[index]);
  };
  var sizeToIndex = function (size) {
    return $_bvikd2w9jd09f01c.findIndex(candidates, function (v) {
      return v === size;
    });
  };
  var getRawOrComputed = function (isRoot, rawStart) {
    var optStart = $_arrpm2xxjd09f07k.isElement(rawStart) ? $_8nwhzlwajd09f01m.some(rawStart) : $_sdd74y3jd09f08a.parent(rawStart);
    return optStart.map(function (start) {
      var inline = $_9o1wlo11cjd09f0ot.closest(start, function (elem) {
        return $_a9ctnkzsjd09f0ej.getRaw(elem, 'font-size');
      }, isRoot);
      return inline.getOrThunk(function () {
        return $_a9ctnkzsjd09f0ej.get(start, 'font-size');
      });
    }).getOr('');
  };
  var getSize = function (editor) {
    var node = editor.selection.getStart();
    var elem = $_cnbf2uwtjd09f033.fromDom(node);
    var root = $_cnbf2uwtjd09f033.fromDom(editor.getBody());
    var isRoot = function (e) {
      return $_6s6cs1w8jd09f014.eq(root, e);
    };
    var elemSize = getRawOrComputed(isRoot, elem);
    return $_bvikd2w9jd09f01c.find(candidates, function (size) {
      return elemSize === size;
    }).getOr(defaultSize);
  };
  var applySize = function (editor, value) {
    var currentValue = getSize(editor);
    if (currentValue !== value) {
      editor.execCommand('fontSize', false, value);
    }
  };
  var get$7 = function (editor) {
    var size = getSize(editor);
    return sizeToIndex(size).getOr(defaultIndex);
  };
  var apply$1 = function (editor, index) {
    indexToSize(index).each(function (size) {
      applySize(editor, size);
    });
  };
  var $_83s45111bjd09f0ok = {
    candidates: $_8z5eqrwbjd09f01q.constant(candidates),
    get: get$7,
    apply: apply$1
  };

  var sizes = $_83s45111bjd09f0ok.candidates();
  var makeSlider$1 = function (spec) {
    return $_9s0r1b11ajd09f0o8.sketch({
      onChange: spec.onChange,
      sizes: sizes,
      category: 'font',
      getInitialValue: spec.getInitialValue
    });
  };
  var makeItems$1 = function (spec) {
    return [
      $_1sirkj10qjd09f0ll.spec('<span class="${prefix}-toolbar-button ${prefix}-icon-small-font ${prefix}-icon"></span>'),
      makeSlider$1(spec),
      $_1sirkj10qjd09f0ll.spec('<span class="${prefix}-toolbar-button ${prefix}-icon-large-font ${prefix}-icon"></span>')
    ];
  };
  var sketch$4 = function (realm, editor) {
    var spec = {
      onChange: function (value) {
        $_83s45111bjd09f0ok.apply(editor, value);
      },
      getInitialValue: function () {
        return $_83s45111bjd09f0ok.get(editor);
      }
    };
    return $_a6gg0q118jd09f0o5.button(realm, 'font-size', function () {
      return makeItems$1(spec);
    });
  };
  var $_4sc391119jd09f0o6 = {
    makeItems: makeItems$1,
    sketch: sketch$4
  };

  var record = function (spec) {
    var uid = $_q0etsx6jd09f053.hasKey(spec, 'uid') ? spec.uid : $_69difv10mjd09f0kv.generate('memento');
    var get = function (any) {
      return any.getSystem().getByUid(uid).getOrDie();
    };
    var getOpt = function (any) {
      return any.getSystem().getByUid(uid).fold($_8nwhzlwajd09f01m.none, $_8nwhzlwajd09f01m.some);
    };
    var asSpec = function () {
      return $_3htayhwyjd09f03i.deepMerge(spec, { uid: uid });
    };
    return {
      get: get,
      getOpt: getOpt,
      asSpec: asSpec
    };
  };
  var $_16gwpy11ejd09f0p7 = { record: record };

  function create$3(width, height) {
    return resize(document.createElement('canvas'), width, height);
  }
  function clone$2(canvas) {
    var tCanvas, ctx;
    tCanvas = create$3(canvas.width, canvas.height);
    ctx = get2dContext(tCanvas);
    ctx.drawImage(canvas, 0, 0);
    return tCanvas;
  }
  function get2dContext(canvas) {
    return canvas.getContext('2d');
  }
  function get3dContext(canvas) {
    var gl = null;
    try {
      gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
    } catch (e) {
    }
    if (!gl) {
      gl = null;
    }
    return gl;
  }
  function resize(canvas, width, height) {
    canvas.width = width;
    canvas.height = height;
    return canvas;
  }
  var $_fxmq2e11hjd09f0pp = {
    create: create$3,
    clone: clone$2,
    resize: resize,
    get2dContext: get2dContext,
    get3dContext: get3dContext
  };

  function getWidth(image) {
    return image.naturalWidth || image.width;
  }
  function getHeight(image) {
    return image.naturalHeight || image.height;
  }
  var $_5k191c11ijd09f0pr = {
    getWidth: getWidth,
    getHeight: getHeight
  };

  var promise = function () {
    var Promise = function (fn) {
      if (typeof this !== 'object')
        throw new TypeError('Promises must be constructed via new');
      if (typeof fn !== 'function')
        throw new TypeError('not a function');
      this._state = null;
      this._value = null;
      this._deferreds = [];
      doResolve(fn, bind(resolve, this), bind(reject, this));
    };
    var asap = Promise.immediateFn || typeof setImmediate === 'function' && setImmediate || function (fn) {
      setTimeout(fn, 1);
    };
    function bind(fn, thisArg) {
      return function () {
        fn.apply(thisArg, arguments);
      };
    }
    var isArray = Array.isArray || function (value) {
      return Object.prototype.toString.call(value) === '[object Array]';
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
        if (newValue === this)
          throw new TypeError('A promise cannot be resolved with itself.');
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
          if (done)
            return;
          done = true;
          onFulfilled(value);
        }, function (reason) {
          if (done)
            return;
          done = true;
          onRejected(reason);
        });
      } catch (ex) {
        if (done)
          return;
        done = true;
        onRejected(ex);
      }
    }
    Promise.prototype['catch'] = function (onRejected) {
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
        if (args.length === 0)
          return resolve([]);
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
  var Promise = window.Promise ? window.Promise : promise();

  function Blob (parts, properties) {
    var f = $_2l4l3gwdjd09f01u.getOrDie('Blob');
    return new f(parts, properties);
  }

  function FileReader () {
    var f = $_2l4l3gwdjd09f01u.getOrDie('FileReader');
    return new f();
  }

  function Uint8Array (arr) {
    var f = $_2l4l3gwdjd09f01u.getOrDie('Uint8Array');
    return new f(arr);
  }

  var requestAnimationFrame = function (callback) {
    var f = $_2l4l3gwdjd09f01u.getOrDie('requestAnimationFrame');
    f(callback);
  };
  var atob = function (base64) {
    var f = $_2l4l3gwdjd09f01u.getOrDie('atob');
    return f(base64);
  };
  var $_cod2vk11njd09f0pz = {
    atob: atob,
    requestAnimationFrame: requestAnimationFrame
  };

  function loadImage(image) {
    return new Promise(function (resolve) {
      function loaded() {
        image.removeEventListener('load', loaded);
        resolve(image);
      }
      if (image.complete) {
        resolve(image);
      } else {
        image.addEventListener('load', loaded);
      }
    });
  }
  function imageToBlob(image) {
    return loadImage(image).then(function (image) {
      var src = image.src;
      if (src.indexOf('blob:') === 0) {
        return anyUriToBlob(src);
      }
      if (src.indexOf('data:') === 0) {
        return dataUriToBlob(src);
      }
      return anyUriToBlob(src);
    });
  }
  function blobToImage(blob) {
    return new Promise(function (resolve, reject) {
      var blobUrl = URL.createObjectURL(blob);
      var image = new Image();
      var removeListeners = function () {
        image.removeEventListener('load', loaded);
        image.removeEventListener('error', error);
      };
      function loaded() {
        removeListeners();
        resolve(image);
      }
      function error() {
        removeListeners();
        reject('Unable to load data of type ' + blob.type + ': ' + blobUrl);
      }
      image.addEventListener('load', loaded);
      image.addEventListener('error', error);
      image.src = blobUrl;
      if (image.complete) {
        loaded();
      }
    });
  }
  function anyUriToBlob(url) {
    return new Promise(function (resolve, reject) {
      var xhr = new XMLHttpRequest();
      xhr.open('GET', url, true);
      xhr.responseType = 'blob';
      xhr.onload = function () {
        if (this.status == 200) {
          resolve(this.response);
        }
      };
      xhr.onerror = function () {
        var _this = this;
        var corsError = function () {
          var obj = new Error('No access to download image');
          obj.code = 18;
          obj.name = 'SecurityError';
          return obj;
        };
        var genericError = function () {
          return new Error('Error ' + _this.status + ' downloading image');
        };
        reject(this.status === 0 ? corsError() : genericError());
      };
      xhr.send();
    });
  }
  function dataUriToBlobSync(uri) {
    var data = uri.split(',');
    var matches = /data:([^;]+)/.exec(data[0]);
    if (!matches)
      return $_8nwhzlwajd09f01m.none();
    var mimetype = matches[1];
    var base64 = data[1];
    var sliceSize = 1024;
    var byteCharacters = $_cod2vk11njd09f0pz.atob(base64);
    var bytesLength = byteCharacters.length;
    var slicesCount = Math.ceil(bytesLength / sliceSize);
    var byteArrays = new Array(slicesCount);
    for (var sliceIndex = 0; sliceIndex < slicesCount; ++sliceIndex) {
      var begin = sliceIndex * sliceSize;
      var end = Math.min(begin + sliceSize, bytesLength);
      var bytes = new Array(end - begin);
      for (var offset = begin, i = 0; offset < end; ++i, ++offset) {
        bytes[i] = byteCharacters[offset].charCodeAt(0);
      }
      byteArrays[sliceIndex] = Uint8Array(bytes);
    }
    return $_8nwhzlwajd09f01m.some(Blob(byteArrays, { type: mimetype }));
  }
  function dataUriToBlob(uri) {
    return new Promise(function (resolve, reject) {
      dataUriToBlobSync(uri).fold(function () {
        reject('uri is not base64: ' + uri);
      }, resolve);
    });
  }
  function uriToBlob(url) {
    if (url.indexOf('blob:') === 0) {
      return anyUriToBlob(url);
    }
    if (url.indexOf('data:') === 0) {
      return dataUriToBlob(url);
    }
    return null;
  }
  function canvasToBlob(canvas, type, quality) {
    type = type || 'image/png';
    if (HTMLCanvasElement.prototype.toBlob) {
      return new Promise(function (resolve) {
        canvas.toBlob(function (blob) {
          resolve(blob);
        }, type, quality);
      });
    } else {
      return dataUriToBlob(canvas.toDataURL(type, quality));
    }
  }
  function canvasToDataURL(getCanvas, type, quality) {
    type = type || 'image/png';
    return getCanvas.then(function (canvas) {
      return canvas.toDataURL(type, quality);
    });
  }
  function blobToCanvas(blob) {
    return blobToImage(blob).then(function (image) {
      revokeImageUrl(image);
      var context, canvas;
      canvas = $_fxmq2e11hjd09f0pp.create($_5k191c11ijd09f0pr.getWidth(image), $_5k191c11ijd09f0pr.getHeight(image));
      context = $_fxmq2e11hjd09f0pp.get2dContext(canvas);
      context.drawImage(image, 0, 0);
      return canvas;
    });
  }
  function blobToDataUri(blob) {
    return new Promise(function (resolve) {
      var reader = new FileReader();
      reader.onloadend = function () {
        resolve(reader.result);
      };
      reader.readAsDataURL(blob);
    });
  }
  function blobToBase64(blob) {
    return blobToDataUri(blob).then(function (dataUri) {
      return dataUri.split(',')[1];
    });
  }
  function revokeImageUrl(image) {
    URL.revokeObjectURL(image.src);
  }
  var $_3v92m311gjd09f0pg = {
    blobToImage: blobToImage,
    imageToBlob: imageToBlob,
    blobToDataUri: blobToDataUri,
    blobToBase64: blobToBase64,
    dataUriToBlobSync: dataUriToBlobSync,
    canvasToBlob: canvasToBlob,
    canvasToDataURL: canvasToDataURL,
    blobToCanvas: blobToCanvas,
    uriToBlob: uriToBlob
  };

  var blobToImage$1 = function (image) {
    return $_3v92m311gjd09f0pg.blobToImage(image);
  };
  var imageToBlob$1 = function (blob) {
    return $_3v92m311gjd09f0pg.imageToBlob(blob);
  };
  var blobToDataUri$1 = function (blob) {
    return $_3v92m311gjd09f0pg.blobToDataUri(blob);
  };
  var blobToBase64$1 = function (blob) {
    return $_3v92m311gjd09f0pg.blobToBase64(blob);
  };
  var dataUriToBlobSync$1 = function (uri) {
    return $_3v92m311gjd09f0pg.dataUriToBlobSync(uri);
  };
  var uriToBlob$1 = function (uri) {
    return $_8nwhzlwajd09f01m.from($_3v92m311gjd09f0pg.uriToBlob(uri));
  };
  var $_3adrhc11fjd09f0pd = {
    blobToImage: blobToImage$1,
    imageToBlob: imageToBlob$1,
    blobToDataUri: blobToDataUri$1,
    blobToBase64: blobToBase64$1,
    dataUriToBlobSync: dataUriToBlobSync$1,
    uriToBlob: uriToBlob$1
  };

  var addImage = function (editor, blob) {
    $_3adrhc11fjd09f0pd.blobToBase64(blob).then(function (base64) {
      editor.undoManager.transact(function () {
        var cache = editor.editorUpload.blobCache;
        var info = cache.create($_3x6e2m10gjd09f0ir.generate('mceu'), blob, base64);
        cache.add(info);
        var img = editor.dom.createHTML('img', { src: info.blobUri() });
        editor.insertContent(img);
      });
    });
  };
  var extractBlob = function (simulatedEvent) {
    var event = simulatedEvent.event();
    var files = event.raw().target.files || event.raw().dataTransfer.files;
    return $_8nwhzlwajd09f01m.from(files[0]);
  };
  var sketch$5 = function (editor) {
    var pickerDom = {
      tag: 'input',
      attributes: {
        accept: 'image/*',
        type: 'file',
        title: ''
      },
      styles: {
        visibility: 'hidden',
        position: 'absolute'
      }
    };
    var memPicker = $_16gwpy11ejd09f0p7.record({
      dom: pickerDom,
      events: $_6j84lww6jd09f00y.derive([
        $_6j84lww6jd09f00y.cutter($_8nqyjjwxjd09f03f.click()),
        $_6j84lww6jd09f00y.run($_8nqyjjwxjd09f03f.change(), function (picker, simulatedEvent) {
          extractBlob(simulatedEvent).each(function (blob) {
            addImage(editor, blob);
          });
        })
      ])
    });
    return Button.sketch({
      dom: $_1sirkj10qjd09f0ll.dom('<span class="${prefix}-toolbar-button ${prefix}-icon-image ${prefix}-icon"></span>'),
      components: [memPicker.asSpec()],
      action: function (button) {
        var picker = memPicker.get(button);
        picker.element().dom().click();
      }
    });
  };
  var $_eehf7n11djd09f0p0 = { sketch: sketch$5 };

  var get$8 = function (element) {
    return element.dom().textContent;
  };
  var set$5 = function (element, value) {
    element.dom().textContent = value;
  };
  var $_61xg4v11qjd09f0qj = {
    get: get$8,
    set: set$5
  };

  var isNotEmpty = function (val) {
    return val.length > 0;
  };
  var defaultToEmpty = function (str) {
    return str === undefined || str === null ? '' : str;
  };
  var noLink = function (editor) {
    var text = editor.selection.getContent({ format: 'text' });
    return {
      url: '',
      text: text,
      title: '',
      target: '',
      link: $_8nwhzlwajd09f01m.none()
    };
  };
  var fromLink = function (link) {
    var text = $_61xg4v11qjd09f0qj.get(link);
    var url = $_6spjcmxwjd09f07e.get(link, 'href');
    var title = $_6spjcmxwjd09f07e.get(link, 'title');
    var target = $_6spjcmxwjd09f07e.get(link, 'target');
    return {
      url: defaultToEmpty(url),
      text: text !== url ? defaultToEmpty(text) : '',
      title: defaultToEmpty(title),
      target: defaultToEmpty(target),
      link: $_8nwhzlwajd09f01m.some(link)
    };
  };
  var getInfo = function (editor) {
    return query(editor).fold(function () {
      return noLink(editor);
    }, function (link) {
      return fromLink(link);
    });
  };
  var wasSimple = function (link) {
    var prevHref = $_6spjcmxwjd09f07e.get(link, 'href');
    var prevText = $_61xg4v11qjd09f0qj.get(link);
    return prevHref === prevText;
  };
  var getTextToApply = function (link, url, info) {
    return info.text.filter(isNotEmpty).fold(function () {
      return wasSimple(link) ? $_8nwhzlwajd09f01m.some(url) : $_8nwhzlwajd09f01m.none();
    }, $_8nwhzlwajd09f01m.some);
  };
  var unlinkIfRequired = function (editor, info) {
    var activeLink = info.link.bind($_8z5eqrwbjd09f01q.identity);
    activeLink.each(function (link) {
      editor.execCommand('unlink');
    });
  };
  var getAttrs$1 = function (url, info) {
    var attrs = {};
    attrs.href = url;
    info.title.filter(isNotEmpty).each(function (title) {
      attrs.title = title;
    });
    info.target.filter(isNotEmpty).each(function (target) {
      attrs.target = target;
    });
    return attrs;
  };
  var applyInfo = function (editor, info) {
    info.url.filter(isNotEmpty).fold(function () {
      unlinkIfRequired(editor, info);
    }, function (url) {
      var attrs = getAttrs$1(url, info);
      var activeLink = info.link.bind($_8z5eqrwbjd09f01q.identity);
      activeLink.fold(function () {
        var text = info.text.filter(isNotEmpty).getOr(url);
        editor.insertContent(editor.dom.createHTML('a', attrs, editor.dom.encode(text)));
      }, function (link) {
        var text = getTextToApply(link, url, info);
        $_6spjcmxwjd09f07e.setAll(link, attrs);
        text.each(function (newText) {
          $_61xg4v11qjd09f0qj.set(link, newText);
        });
      });
    });
  };
  var query = function (editor) {
    var start = $_cnbf2uwtjd09f033.fromDom(editor.selection.getStart());
    return $_74vb1xzmjd09f0e3.closest(start, 'a');
  };
  var $_7okzkp11pjd09f0qc = {
    getInfo: getInfo,
    applyInfo: applyInfo,
    query: query
  };

  var events$6 = function (name, eventHandlers) {
    var events = $_6j84lww6jd09f00y.derive(eventHandlers);
    return $_fq8al5w4jd09f00e.create({
      fields: [$_1t7kykx2jd09f043.strict('enabled')],
      name: name,
      active: { events: $_8z5eqrwbjd09f01q.constant(events) }
    });
  };
  var config = function (name, eventHandlers) {
    var me = events$6(name, eventHandlers);
    return {
      key: name,
      value: {
        config: {},
        me: me,
        configAsRaw: $_8z5eqrwbjd09f01q.constant({}),
        initialConfig: {},
        state: $_fq8al5w4jd09f00e.noState()
      }
    };
  };
  var $_2qc4or11sjd09f0qy = {
    events: events$6,
    config: config
  };

  var getCurrent = function (component, composeConfig, composeState) {
    return composeConfig.find()(component);
  };
  var $_ejn0z411ujd09f0r3 = { getCurrent: getCurrent };

  var ComposeSchema = [$_1t7kykx2jd09f043.strict('find')];

  var Composing = $_fq8al5w4jd09f00e.create({
    fields: ComposeSchema,
    name: 'composing',
    apis: $_ejn0z411ujd09f0r3
  });

  var factory$1 = function (detail, spec) {
    return {
      uid: detail.uid(),
      dom: $_3htayhwyjd09f03i.deepMerge({
        tag: 'div',
        attributes: { role: 'presentation' }
      }, detail.dom()),
      components: detail.components(),
      behaviours: $_beskyy10djd09f0i0.get(detail.containerBehaviours()),
      events: detail.events(),
      domModification: detail.domModification(),
      eventOrder: detail.eventOrder()
    };
  };
  var Container = $_4q1unv10ejd09f0i8.single({
    name: 'Container',
    factory: factory$1,
    configFields: [
      $_1t7kykx2jd09f043.defaulted('components', []),
      $_beskyy10djd09f0i0.field('containerBehaviours', []),
      $_1t7kykx2jd09f043.defaulted('events', {}),
      $_1t7kykx2jd09f043.defaulted('domModification', {}),
      $_1t7kykx2jd09f043.defaulted('eventOrder', {})
    ]
  });

  var factory$2 = function (detail, spec) {
    return {
      uid: detail.uid(),
      dom: detail.dom(),
      behaviours: $_3htayhwyjd09f03i.deepMerge($_fq8al5w4jd09f00e.derive([
        me.config({
          store: {
            mode: 'memory',
            initialValue: detail.getInitialValue()()
          }
        }),
        Composing.config({ find: $_8nwhzlwajd09f01m.some })
      ]), $_beskyy10djd09f0i0.get(detail.dataBehaviours())),
      events: $_6j84lww6jd09f00y.derive([$_6j84lww6jd09f00y.runOnAttached(function (component, simulatedEvent) {
          me.setValue(component, detail.getInitialValue()());
        })])
    };
  };
  var DataField = $_4q1unv10ejd09f0i8.single({
    name: 'DataField',
    factory: factory$2,
    configFields: [
      $_1t7kykx2jd09f043.strict('uid'),
      $_1t7kykx2jd09f043.strict('dom'),
      $_1t7kykx2jd09f043.strict('getInitialValue'),
      $_beskyy10djd09f0i0.field('dataBehaviours', [
        me,
        Composing
      ])
    ]
  });

  var get$9 = function (element) {
    return element.dom().value;
  };
  var set$6 = function (element, value) {
    if (value === undefined)
      throw new Error('Value.set was undefined');
    element.dom().value = value;
  };
  var $_eoyy91120jd09f0rq = {
    set: set$6,
    get: get$9
  };

  var schema$8 = [
    $_1t7kykx2jd09f043.option('data'),
    $_1t7kykx2jd09f043.defaulted('inputAttributes', {}),
    $_1t7kykx2jd09f043.defaulted('inputStyles', {}),
    $_1t7kykx2jd09f043.defaulted('type', 'input'),
    $_1t7kykx2jd09f043.defaulted('tag', 'input'),
    $_1t7kykx2jd09f043.defaulted('inputClasses', []),
    $_1l599yytjd09f0ar.onHandler('onSetValue'),
    $_1t7kykx2jd09f043.defaulted('styles', {}),
    $_1t7kykx2jd09f043.option('placeholder'),
    $_1t7kykx2jd09f043.defaulted('eventOrder', {}),
    $_beskyy10djd09f0i0.field('inputBehaviours', [
      me,
      Focusing
    ]),
    $_1t7kykx2jd09f043.defaulted('selectOnFocus', true)
  ];
  var behaviours = function (detail) {
    return $_3htayhwyjd09f03i.deepMerge($_fq8al5w4jd09f00e.derive([
      me.config({
        store: {
          mode: 'manual',
          initialValue: detail.data().getOr(undefined),
          getValue: function (input) {
            return $_eoyy91120jd09f0rq.get(input.element());
          },
          setValue: function (input, data) {
            var current = $_eoyy91120jd09f0rq.get(input.element());
            if (current !== data) {
              $_eoyy91120jd09f0rq.set(input.element(), data);
            }
          }
        },
        onSetValue: detail.onSetValue()
      }),
      Focusing.config({
        onFocus: detail.selectOnFocus() === false ? $_8z5eqrwbjd09f01q.noop : function (component) {
          var input = component.element();
          var value = $_eoyy91120jd09f0rq.get(input);
          input.dom().setSelectionRange(0, value.length);
        }
      })
    ]), $_beskyy10djd09f0i0.get(detail.inputBehaviours()));
  };
  var dom$2 = function (detail) {
    return {
      tag: detail.tag(),
      attributes: $_3htayhwyjd09f03i.deepMerge($_q0etsx6jd09f053.wrapAll([{
          key: 'type',
          value: detail.type()
        }].concat(detail.placeholder().map(function (pc) {
        return {
          key: 'placeholder',
          value: pc
        };
      }).toArray())), detail.inputAttributes()),
      styles: detail.inputStyles(),
      classes: detail.inputClasses()
    };
  };
  var $_6g1s9111zjd09f0rh = {
    schema: $_8z5eqrwbjd09f01q.constant(schema$8),
    behaviours: behaviours,
    dom: dom$2
  };

  var factory$3 = function (detail, spec) {
    return {
      uid: detail.uid(),
      dom: $_6g1s9111zjd09f0rh.dom(detail),
      components: [],
      behaviours: $_6g1s9111zjd09f0rh.behaviours(detail),
      eventOrder: detail.eventOrder()
    };
  };
  var Input = $_4q1unv10ejd09f0i8.single({
    name: 'Input',
    configFields: $_6g1s9111zjd09f0rh.schema(),
    factory: factory$3
  });

  var exhibit$3 = function (base, tabConfig) {
    return $_brhg4pxkjd09f06j.nu({
      attributes: $_q0etsx6jd09f053.wrapAll([{
          key: tabConfig.tabAttr(),
          value: 'true'
        }])
    });
  };
  var $_ydev2122jd09f0rt = { exhibit: exhibit$3 };

  var TabstopSchema = [$_1t7kykx2jd09f043.defaulted('tabAttr', 'data-alloy-tabstop')];

  var Tabstopping = $_fq8al5w4jd09f00e.create({
    fields: TabstopSchema,
    name: 'tabstopping',
    active: $_ydev2122jd09f0rt
  });

  var clearInputBehaviour = 'input-clearing';
  var field$2 = function (name, placeholder) {
    var inputSpec = $_16gwpy11ejd09f0p7.record(Input.sketch({
      placeholder: placeholder,
      onSetValue: function (input, data) {
        $_3b6lb8wvjd09f037.emit(input, $_8nqyjjwxjd09f03f.input());
      },
      inputBehaviours: $_fq8al5w4jd09f00e.derive([
        Composing.config({ find: $_8nwhzlwajd09f01m.some }),
        Tabstopping.config({}),
        Keying.config({ mode: 'execution' })
      ]),
      selectOnFocus: false
    }));
    var buttonSpec = $_16gwpy11ejd09f0p7.record(Button.sketch({
      dom: $_1sirkj10qjd09f0ll.dom('<button class="${prefix}-input-container-x ${prefix}-icon-cancel-circle ${prefix}-icon"></button>'),
      action: function (button) {
        var input = inputSpec.get(button);
        me.setValue(input, '');
      }
    }));
    return {
      name: name,
      spec: Container.sketch({
        dom: $_1sirkj10qjd09f0ll.dom('<div class="${prefix}-input-container"></div>'),
        components: [
          inputSpec.asSpec(),
          buttonSpec.asSpec()
        ],
        containerBehaviours: $_fq8al5w4jd09f00e.derive([
          Toggling.config({ toggleClass: $_3584u7z1jd09f0bs.resolve('input-container-empty') }),
          Composing.config({
            find: function (comp) {
              return $_8nwhzlwajd09f01m.some(inputSpec.get(comp));
            }
          }),
          $_2qc4or11sjd09f0qy.config(clearInputBehaviour, [$_6j84lww6jd09f00y.run($_8nqyjjwxjd09f03f.input(), function (iContainer) {
              var input = inputSpec.get(iContainer);
              var val = me.getValue(input);
              var f = val.length > 0 ? Toggling.off : Toggling.on;
              f(iContainer);
            })])
        ])
      })
    };
  };
  var hidden = function (name) {
    return {
      name: name,
      spec: DataField.sketch({
        dom: {
          tag: 'span',
          styles: { display: 'none' }
        },
        getInitialValue: function () {
          return $_8nwhzlwajd09f01m.none();
        }
      })
    };
  };
  var $_3ytd8911rjd09f0qk = {
    field: field$2,
    hidden: hidden
  };

  var nativeDisabled = [
    'input',
    'button',
    'textarea'
  ];
  var onLoad$5 = function (component, disableConfig, disableState) {
    if (disableConfig.disabled())
      disable(component, disableConfig, disableState);
  };
  var hasNative = function (component) {
    return $_bvikd2w9jd09f01c.contains(nativeDisabled, $_arrpm2xxjd09f07k.name(component.element()));
  };
  var nativeIsDisabled = function (component) {
    return $_6spjcmxwjd09f07e.has(component.element(), 'disabled');
  };
  var nativeDisable = function (component) {
    $_6spjcmxwjd09f07e.set(component.element(), 'disabled', 'disabled');
  };
  var nativeEnable = function (component) {
    $_6spjcmxwjd09f07e.remove(component.element(), 'disabled');
  };
  var ariaIsDisabled = function (component) {
    return $_6spjcmxwjd09f07e.get(component.element(), 'aria-disabled') === 'true';
  };
  var ariaDisable = function (component) {
    $_6spjcmxwjd09f07e.set(component.element(), 'aria-disabled', 'true');
  };
  var ariaEnable = function (component) {
    $_6spjcmxwjd09f07e.set(component.element(), 'aria-disabled', 'false');
  };
  var disable = function (component, disableConfig, disableState) {
    disableConfig.disableClass().each(function (disableClass) {
      $_e7d6ttxujd09f07b.add(component.element(), disableClass);
    });
    var f = hasNative(component) ? nativeDisable : ariaDisable;
    f(component);
  };
  var enable = function (component, disableConfig, disableState) {
    disableConfig.disableClass().each(function (disableClass) {
      $_e7d6ttxujd09f07b.remove(component.element(), disableClass);
    });
    var f = hasNative(component) ? nativeEnable : ariaEnable;
    f(component);
  };
  var isDisabled = function (component) {
    return hasNative(component) ? nativeIsDisabled(component) : ariaIsDisabled(component);
  };
  var $_2oih2u127jd09f0su = {
    enable: enable,
    disable: disable,
    isDisabled: isDisabled,
    onLoad: onLoad$5
  };

  var exhibit$4 = function (base, disableConfig, disableState) {
    return $_brhg4pxkjd09f06j.nu({ classes: disableConfig.disabled() ? disableConfig.disableClass().map($_bvikd2w9jd09f01c.pure).getOr([]) : [] });
  };
  var events$7 = function (disableConfig, disableState) {
    return $_6j84lww6jd09f00y.derive([
      $_6j84lww6jd09f00y.abort($_5iytewwjd09f03c.execute(), function (component, simulatedEvent) {
        return $_2oih2u127jd09f0su.isDisabled(component, disableConfig, disableState);
      }),
      $_8zny04w5jd09f00m.loadEvent(disableConfig, disableState, $_2oih2u127jd09f0su.onLoad)
    ]);
  };
  var $_fp7je8126jd09f0sr = {
    exhibit: exhibit$4,
    events: events$7
  };

  var DisableSchema = [
    $_1t7kykx2jd09f043.defaulted('disabled', false),
    $_1t7kykx2jd09f043.option('disableClass')
  ];

  var Disabling = $_fq8al5w4jd09f00e.create({
    fields: DisableSchema,
    name: 'disabling',
    active: $_fp7je8126jd09f0sr,
    apis: $_2oih2u127jd09f0su
  });

  var owner$1 = 'form';
  var schema$9 = [$_beskyy10djd09f0i0.field('formBehaviours', [me])];
  var getPartName = function (name) {
    return '<alloy.field.' + name + '>';
  };
  var sketch$6 = function (fSpec) {
    var parts = function () {
      var record = [];
      var field = function (name, config) {
        record.push(name);
        return $_7vfhnq10ijd09f0iz.generateOne(owner$1, getPartName(name), config);
      };
      return {
        field: field,
        record: function () {
          return record;
        }
      };
    }();
    var spec = fSpec(parts);
    var partNames = parts.record();
    var fieldParts = $_bvikd2w9jd09f01c.map(partNames, function (n) {
      return $_37ihar10kjd09f0js.required({
        name: n,
        pname: getPartName(n)
      });
    });
    return $_azz8wi10hjd09f0is.composite(owner$1, schema$9, fieldParts, make, spec);
  };
  var make = function (detail, components, spec) {
    return $_3htayhwyjd09f03i.deepMerge({
      'debug.sketcher': { 'Form': spec },
      uid: detail.uid(),
      dom: detail.dom(),
      components: components,
      behaviours: $_3htayhwyjd09f03i.deepMerge($_fq8al5w4jd09f00e.derive([me.config({
          store: {
            mode: 'manual',
            getValue: function (form) {
              var optPs = $_7vfhnq10ijd09f0iz.getAllParts(form, detail);
              return $_32a0zdx0jd09f03l.map(optPs, function (optPThunk, pName) {
                return optPThunk().bind(Composing.getCurrent).map(me.getValue);
              });
            },
            setValue: function (form, values) {
              $_32a0zdx0jd09f03l.each(values, function (newValue, key) {
                $_7vfhnq10ijd09f0iz.getPart(form, detail, key).each(function (wrapper) {
                  Composing.getCurrent(wrapper).each(function (field) {
                    me.setValue(field, newValue);
                  });
                });
              });
            }
          }
        })]), $_beskyy10djd09f0i0.get(detail.formBehaviours())),
      apis: {
        getField: function (form, key) {
          return $_7vfhnq10ijd09f0iz.getPart(form, detail, key).bind(Composing.getCurrent);
        }
      }
    });
  };
  var $_b2qa9m129jd09f0t4 = {
    getField: $_fdvkh210fjd09f0ij.makeApi(function (apis, component, key) {
      return apis.getField(component, key);
    }),
    sketch: sketch$6
  };

  var revocable = function (doRevoke) {
    var subject = Cell($_8nwhzlwajd09f01m.none());
    var revoke = function () {
      subject.get().each(doRevoke);
    };
    var clear = function () {
      revoke();
      subject.set($_8nwhzlwajd09f01m.none());
    };
    var set = function (s) {
      revoke();
      subject.set($_8nwhzlwajd09f01m.some(s));
    };
    var isSet = function () {
      return subject.get().isSome();
    };
    return {
      clear: clear,
      isSet: isSet,
      set: set
    };
  };
  var destroyable = function () {
    return revocable(function (s) {
      s.destroy();
    });
  };
  var unbindable = function () {
    return revocable(function (s) {
      s.unbind();
    });
  };
  var api$2 = function () {
    var subject = Cell($_8nwhzlwajd09f01m.none());
    var revoke = function () {
      subject.get().each(function (s) {
        s.destroy();
      });
    };
    var clear = function () {
      revoke();
      subject.set($_8nwhzlwajd09f01m.none());
    };
    var set = function (s) {
      revoke();
      subject.set($_8nwhzlwajd09f01m.some(s));
    };
    var run = function (f) {
      subject.get().each(f);
    };
    var isSet = function () {
      return subject.get().isSome();
    };
    return {
      clear: clear,
      isSet: isSet,
      set: set,
      run: run
    };
  };
  var value$3 = function () {
    var subject = Cell($_8nwhzlwajd09f01m.none());
    var clear = function () {
      subject.set($_8nwhzlwajd09f01m.none());
    };
    var set = function (s) {
      subject.set($_8nwhzlwajd09f01m.some(s));
    };
    var on = function (f) {
      subject.get().each(f);
    };
    var isSet = function () {
      return subject.get().isSome();
    };
    return {
      clear: clear,
      set: set,
      isSet: isSet,
      on: on
    };
  };
  var $_eq1tgp12ajd09f0tb = {
    destroyable: destroyable,
    unbindable: unbindable,
    api: api$2,
    value: value$3
  };

  var SWIPING_LEFT = 1;
  var SWIPING_RIGHT = -1;
  var SWIPING_NONE = 0;
  var init$3 = function (xValue) {
    return {
      xValue: xValue,
      points: []
    };
  };
  var move = function (model, xValue) {
    if (xValue === model.xValue) {
      return model;
    }
    var currentDirection = xValue - model.xValue > 0 ? SWIPING_LEFT : SWIPING_RIGHT;
    var newPoint = {
      direction: currentDirection,
      xValue: xValue
    };
    var priorPoints = function () {
      if (model.points.length === 0) {
        return [];
      } else {
        var prev = model.points[model.points.length - 1];
        return prev.direction === currentDirection ? model.points.slice(0, model.points.length - 1) : model.points;
      }
    }();
    return {
      xValue: xValue,
      points: priorPoints.concat([newPoint])
    };
  };
  var complete = function (model) {
    if (model.points.length === 0) {
      return SWIPING_NONE;
    } else {
      var firstDirection = model.points[0].direction;
      var lastDirection = model.points[model.points.length - 1].direction;
      return firstDirection === SWIPING_RIGHT && lastDirection === SWIPING_RIGHT ? SWIPING_RIGHT : firstDirection === SWIPING_LEFT && lastDirection === SWIPING_LEFT ? SWIPING_LEFT : SWIPING_NONE;
    }
  };
  var $_333t1r12bjd09f0te = {
    init: init$3,
    move: move,
    complete: complete
  };

  var sketch$7 = function (rawSpec) {
    var navigateEvent = 'navigateEvent';
    var wrapperAdhocEvents = 'serializer-wrapper-events';
    var formAdhocEvents = 'form-events';
    var schema = $_33aoy2xhjd09f061.objOf([
      $_1t7kykx2jd09f043.strict('fields'),
      $_1t7kykx2jd09f043.defaulted('maxFieldIndex', rawSpec.fields.length - 1),
      $_1t7kykx2jd09f043.strict('onExecute'),
      $_1t7kykx2jd09f043.strict('getInitialValue'),
      $_1t7kykx2jd09f043.state('state', function () {
        return {
          dialogSwipeState: $_eq1tgp12ajd09f0tb.value(),
          currentScreen: Cell(0)
        };
      })
    ]);
    var spec = $_33aoy2xhjd09f061.asRawOrDie('SerialisedDialog', schema, rawSpec);
    var navigationButton = function (direction, directionName, enabled) {
      return Button.sketch({
        dom: $_1sirkj10qjd09f0ll.dom('<span class="${prefix}-icon-' + directionName + ' ${prefix}-icon"></span>'),
        action: function (button) {
          $_3b6lb8wvjd09f037.emitWith(button, navigateEvent, { direction: direction });
        },
        buttonBehaviours: $_fq8al5w4jd09f00e.derive([Disabling.config({
            disableClass: $_3584u7z1jd09f0bs.resolve('toolbar-navigation-disabled'),
            disabled: !enabled
          })])
      });
    };
    var reposition = function (dialog, message) {
      $_74vb1xzmjd09f0e3.descendant(dialog.element(), '.' + $_3584u7z1jd09f0bs.resolve('serialised-dialog-chain')).each(function (parent) {
        $_a9ctnkzsjd09f0ej.set(parent, 'left', -spec.state.currentScreen.get() * message.width + 'px');
      });
    };
    var navigate = function (dialog, direction) {
      var screens = $_23mimrzkjd09f0dz.descendants(dialog.element(), '.' + $_3584u7z1jd09f0bs.resolve('serialised-dialog-screen'));
      $_74vb1xzmjd09f0e3.descendant(dialog.element(), '.' + $_3584u7z1jd09f0bs.resolve('serialised-dialog-chain')).each(function (parent) {
        if (spec.state.currentScreen.get() + direction >= 0 && spec.state.currentScreen.get() + direction < screens.length) {
          $_a9ctnkzsjd09f0ej.getRaw(parent, 'left').each(function (left) {
            var currentLeft = parseInt(left, 10);
            var w = $_cyexp9117jd09f0o3.get(screens[0]);
            $_a9ctnkzsjd09f0ej.set(parent, 'left', currentLeft - direction * w + 'px');
          });
          spec.state.currentScreen.set(spec.state.currentScreen.get() + direction);
        }
      });
    };
    var focusInput = function (dialog) {
      var inputs = $_23mimrzkjd09f0dz.descendants(dialog.element(), 'input');
      var optInput = $_8nwhzlwajd09f01m.from(inputs[spec.state.currentScreen.get()]);
      optInput.each(function (input) {
        dialog.getSystem().getByDom(input).each(function (inputComp) {
          $_3b6lb8wvjd09f037.dispatchFocus(dialog, inputComp.element());
        });
      });
      var dotitems = memDots.get(dialog);
      Highlighting.highlightAt(dotitems, spec.state.currentScreen.get());
    };
    var resetState = function () {
      spec.state.currentScreen.set(0);
      spec.state.dialogSwipeState.clear();
    };
    var memForm = $_16gwpy11ejd09f0p7.record($_b2qa9m129jd09f0t4.sketch(function (parts) {
      return {
        dom: $_1sirkj10qjd09f0ll.dom('<div class="${prefix}-serialised-dialog"></div>'),
        components: [Container.sketch({
            dom: $_1sirkj10qjd09f0ll.dom('<div class="${prefix}-serialised-dialog-chain" style="left: 0px; position: absolute;"></div>'),
            components: $_bvikd2w9jd09f01c.map(spec.fields, function (field, i) {
              return i <= spec.maxFieldIndex ? Container.sketch({
                dom: $_1sirkj10qjd09f0ll.dom('<div class="${prefix}-serialised-dialog-screen"></div>'),
                components: $_bvikd2w9jd09f01c.flatten([
                  [navigationButton(-1, 'previous', i > 0)],
                  [parts.field(field.name, field.spec)],
                  [navigationButton(+1, 'next', i < spec.maxFieldIndex)]
                ])
              }) : parts.field(field.name, field.spec);
            })
          })],
        formBehaviours: $_fq8al5w4jd09f00e.derive([
          $_2jq3vfz0jd09f0bp.orientation(function (dialog, message) {
            reposition(dialog, message);
          }),
          Keying.config({
            mode: 'special',
            focusIn: function (dialog) {
              focusInput(dialog);
            },
            onTab: function (dialog) {
              navigate(dialog, +1);
              return $_8nwhzlwajd09f01m.some(true);
            },
            onShiftTab: function (dialog) {
              navigate(dialog, -1);
              return $_8nwhzlwajd09f01m.some(true);
            }
          }),
          $_2qc4or11sjd09f0qy.config(formAdhocEvents, [
            $_6j84lww6jd09f00y.runOnAttached(function (dialog, simulatedEvent) {
              resetState();
              var dotitems = memDots.get(dialog);
              Highlighting.highlightFirst(dotitems);
              spec.getInitialValue(dialog).each(function (v) {
                me.setValue(dialog, v);
              });
            }),
            $_6j84lww6jd09f00y.runOnExecute(spec.onExecute),
            $_6j84lww6jd09f00y.run($_8nqyjjwxjd09f03f.transitionend(), function (dialog, simulatedEvent) {
              if (simulatedEvent.event().raw().propertyName === 'left') {
                focusInput(dialog);
              }
            }),
            $_6j84lww6jd09f00y.run(navigateEvent, function (dialog, simulatedEvent) {
              var direction = simulatedEvent.event().direction();
              navigate(dialog, direction);
            })
          ])
        ])
      };
    }));
    var memDots = $_16gwpy11ejd09f0p7.record({
      dom: $_1sirkj10qjd09f0ll.dom('<div class="${prefix}-dot-container"></div>'),
      behaviours: $_fq8al5w4jd09f00e.derive([Highlighting.config({
          highlightClass: $_3584u7z1jd09f0bs.resolve('dot-active'),
          itemClass: $_3584u7z1jd09f0bs.resolve('dot-item')
        })]),
      components: $_bvikd2w9jd09f01c.bind(spec.fields, function (_f, i) {
        return i <= spec.maxFieldIndex ? [$_1sirkj10qjd09f0ll.spec('<div class="${prefix}-dot-item ${prefix}-icon-full-dot ${prefix}-icon"></div>')] : [];
      })
    });
    return {
      dom: $_1sirkj10qjd09f0ll.dom('<div class="${prefix}-serializer-wrapper"></div>'),
      components: [
        memForm.asSpec(),
        memDots.asSpec()
      ],
      behaviours: $_fq8al5w4jd09f00e.derive([
        Keying.config({
          mode: 'special',
          focusIn: function (wrapper) {
            var form = memForm.get(wrapper);
            Keying.focusIn(form);
          }
        }),
        $_2qc4or11sjd09f0qy.config(wrapperAdhocEvents, [
          $_6j84lww6jd09f00y.run($_8nqyjjwxjd09f03f.touchstart(), function (wrapper, simulatedEvent) {
            spec.state.dialogSwipeState.set($_333t1r12bjd09f0te.init(simulatedEvent.event().raw().touches[0].clientX));
          }),
          $_6j84lww6jd09f00y.run($_8nqyjjwxjd09f03f.touchmove(), function (wrapper, simulatedEvent) {
            spec.state.dialogSwipeState.on(function (state) {
              simulatedEvent.event().prevent();
              spec.state.dialogSwipeState.set($_333t1r12bjd09f0te.move(state, simulatedEvent.event().raw().touches[0].clientX));
            });
          }),
          $_6j84lww6jd09f00y.run($_8nqyjjwxjd09f03f.touchend(), function (wrapper) {
            spec.state.dialogSwipeState.on(function (state) {
              var dialog = memForm.get(wrapper);
              var direction = -1 * $_333t1r12bjd09f0te.complete(state);
              navigate(dialog, direction);
            });
          })
        ])
      ])
    };
  };
  var $_22xhfp124jd09f0ry = { sketch: sketch$7 };

  var platform$1 = $_8zynflwgjd09f01z.detect();
  var preserve$1 = function (f, editor) {
    var rng = editor.selection.getRng();
    f();
    editor.selection.setRng(rng);
  };
  var forAndroid = function (editor, f) {
    var wrapper = platform$1.os.isAndroid() ? preserve$1 : $_8z5eqrwbjd09f01q.apply;
    wrapper(f, editor);
  };
  var $_6e6cgo12cjd09f0tg = { forAndroid: forAndroid };

  var getGroups = $_9ur00cwhjd09f021.cached(function (realm, editor) {
    return [{
        label: 'the link group',
        items: [$_22xhfp124jd09f0ry.sketch({
            fields: [
              $_3ytd8911rjd09f0qk.field('url', 'Type or paste URL'),
              $_3ytd8911rjd09f0qk.field('text', 'Link text'),
              $_3ytd8911rjd09f0qk.field('title', 'Link title'),
              $_3ytd8911rjd09f0qk.field('target', 'Link target'),
              $_3ytd8911rjd09f0qk.hidden('link')
            ],
            maxFieldIndex: [
              'url',
              'text',
              'title',
              'target'
            ].length - 1,
            getInitialValue: function () {
              return $_8nwhzlwajd09f01m.some($_7okzkp11pjd09f0qc.getInfo(editor));
            },
            onExecute: function (dialog) {
              var info = me.getValue(dialog);
              $_7okzkp11pjd09f0qc.applyInfo(editor, info);
              realm.restoreToolbar();
              editor.focus();
            }
          })]
      }];
  });
  var sketch$8 = function (realm, editor) {
    return $_ch9li7z2jd09f0c0.forToolbarStateAction(editor, 'link', 'link', function () {
      var groups = getGroups(realm, editor);
      realm.setContextToolbar(groups);
      $_6e6cgo12cjd09f0tg.forAndroid(editor, function () {
        realm.focusToolbar();
      });
      $_7okzkp11pjd09f0qc.query(editor).each(function (link) {
        editor.selection.select(link.dom());
      });
    });
  };
  var $_180n5r11ojd09f0q1 = { sketch: sketch$8 };

  var DefaultStyleFormats = [
    {
      title: 'Headings',
      items: [
        {
          title: 'Heading 1',
          format: 'h1'
        },
        {
          title: 'Heading 2',
          format: 'h2'
        },
        {
          title: 'Heading 3',
          format: 'h3'
        },
        {
          title: 'Heading 4',
          format: 'h4'
        },
        {
          title: 'Heading 5',
          format: 'h5'
        },
        {
          title: 'Heading 6',
          format: 'h6'
        }
      ]
    },
    {
      title: 'Inline',
      items: [
        {
          title: 'Bold',
          icon: 'bold',
          format: 'bold'
        },
        {
          title: 'Italic',
          icon: 'italic',
          format: 'italic'
        },
        {
          title: 'Underline',
          icon: 'underline',
          format: 'underline'
        },
        {
          title: 'Strikethrough',
          icon: 'strikethrough',
          format: 'strikethrough'
        },
        {
          title: 'Superscript',
          icon: 'superscript',
          format: 'superscript'
        },
        {
          title: 'Subscript',
          icon: 'subscript',
          format: 'subscript'
        },
        {
          title: 'Code',
          icon: 'code',
          format: 'code'
        }
      ]
    },
    {
      title: 'Blocks',
      items: [
        {
          title: 'Paragraph',
          format: 'p'
        },
        {
          title: 'Blockquote',
          format: 'blockquote'
        },
        {
          title: 'Div',
          format: 'div'
        },
        {
          title: 'Pre',
          format: 'pre'
        }
      ]
    },
    {
      title: 'Alignment',
      items: [
        {
          title: 'Left',
          icon: 'alignleft',
          format: 'alignleft'
        },
        {
          title: 'Center',
          icon: 'aligncenter',
          format: 'aligncenter'
        },
        {
          title: 'Right',
          icon: 'alignright',
          format: 'alignright'
        },
        {
          title: 'Justify',
          icon: 'alignjustify',
          format: 'alignjustify'
        }
      ]
    }
  ];

  var findRoute = function (component, transConfig, transState, route) {
    return $_q0etsx6jd09f053.readOptFrom(transConfig.routes(), route.start()).map($_8z5eqrwbjd09f01q.apply).bind(function (sConfig) {
      return $_q0etsx6jd09f053.readOptFrom(sConfig, route.destination()).map($_8z5eqrwbjd09f01q.apply);
    });
  };
  var getTransition = function (comp, transConfig, transState) {
    var route = getCurrentRoute(comp, transConfig, transState);
    return route.bind(function (r) {
      return getTransitionOf(comp, transConfig, transState, r);
    });
  };
  var getTransitionOf = function (comp, transConfig, transState, route) {
    return findRoute(comp, transConfig, transState, route).bind(function (r) {
      return r.transition().map(function (t) {
        return {
          transition: $_8z5eqrwbjd09f01q.constant(t),
          route: $_8z5eqrwbjd09f01q.constant(r)
        };
      });
    });
  };
  var disableTransition = function (comp, transConfig, transState) {
    getTransition(comp, transConfig, transState).each(function (routeTransition) {
      var t = routeTransition.transition();
      $_e7d6ttxujd09f07b.remove(comp.element(), t.transitionClass());
      $_6spjcmxwjd09f07e.remove(comp.element(), transConfig.destinationAttr());
    });
  };
  var getNewRoute = function (comp, transConfig, transState, destination) {
    return {
      start: $_8z5eqrwbjd09f01q.constant($_6spjcmxwjd09f07e.get(comp.element(), transConfig.stateAttr())),
      destination: $_8z5eqrwbjd09f01q.constant(destination)
    };
  };
  var getCurrentRoute = function (comp, transConfig, transState) {
    var el = comp.element();
    return $_6spjcmxwjd09f07e.has(el, transConfig.destinationAttr()) ? $_8nwhzlwajd09f01m.some({
      start: $_8z5eqrwbjd09f01q.constant($_6spjcmxwjd09f07e.get(comp.element(), transConfig.stateAttr())),
      destination: $_8z5eqrwbjd09f01q.constant($_6spjcmxwjd09f07e.get(comp.element(), transConfig.destinationAttr()))
    }) : $_8nwhzlwajd09f01m.none();
  };
  var jumpTo = function (comp, transConfig, transState, destination) {
    disableTransition(comp, transConfig, transState);
    if ($_6spjcmxwjd09f07e.has(comp.element(), transConfig.stateAttr()) && $_6spjcmxwjd09f07e.get(comp.element(), transConfig.stateAttr()) !== destination)
      transConfig.onFinish()(comp, destination);
    $_6spjcmxwjd09f07e.set(comp.element(), transConfig.stateAttr(), destination);
  };
  var fasttrack = function (comp, transConfig, transState, destination) {
    if ($_6spjcmxwjd09f07e.has(comp.element(), transConfig.destinationAttr())) {
      $_6spjcmxwjd09f07e.set(comp.element(), transConfig.stateAttr(), $_6spjcmxwjd09f07e.get(comp.element(), transConfig.destinationAttr()));
      $_6spjcmxwjd09f07e.remove(comp.element(), transConfig.destinationAttr());
    }
  };
  var progressTo = function (comp, transConfig, transState, destination) {
    fasttrack(comp, transConfig, transState, destination);
    var route = getNewRoute(comp, transConfig, transState, destination);
    getTransitionOf(comp, transConfig, transState, route).fold(function () {
      jumpTo(comp, transConfig, transState, destination);
    }, function (routeTransition) {
      disableTransition(comp, transConfig, transState);
      var t = routeTransition.transition();
      $_e7d6ttxujd09f07b.add(comp.element(), t.transitionClass());
      $_6spjcmxwjd09f07e.set(comp.element(), transConfig.destinationAttr(), destination);
    });
  };
  var getState = function (comp, transConfig, transState) {
    var e = comp.element();
    return $_6spjcmxwjd09f07e.has(e, transConfig.stateAttr()) ? $_8nwhzlwajd09f01m.some($_6spjcmxwjd09f07e.get(e, transConfig.stateAttr())) : $_8nwhzlwajd09f01m.none();
  };
  var $_1s63b812ijd09f0us = {
    findRoute: findRoute,
    disableTransition: disableTransition,
    getCurrentRoute: getCurrentRoute,
    jumpTo: jumpTo,
    progressTo: progressTo,
    getState: getState
  };

  var events$8 = function (transConfig, transState) {
    return $_6j84lww6jd09f00y.derive([
      $_6j84lww6jd09f00y.run($_8nqyjjwxjd09f03f.transitionend(), function (component, simulatedEvent) {
        var raw = simulatedEvent.event().raw();
        $_1s63b812ijd09f0us.getCurrentRoute(component, transConfig, transState).each(function (route) {
          $_1s63b812ijd09f0us.findRoute(component, transConfig, transState, route).each(function (rInfo) {
            rInfo.transition().each(function (rTransition) {
              if (raw.propertyName === rTransition.property()) {
                $_1s63b812ijd09f0us.jumpTo(component, transConfig, transState, route.destination());
                transConfig.onTransition()(component, route);
              }
            });
          });
        });
      }),
      $_6j84lww6jd09f00y.runOnAttached(function (comp, se) {
        $_1s63b812ijd09f0us.jumpTo(comp, transConfig, transState, transConfig.initialState());
      })
    ]);
  };
  var $_6y1jhx12hjd09f0uq = { events: events$8 };

  var TransitionSchema = [
    $_1t7kykx2jd09f043.defaulted('destinationAttr', 'data-transitioning-destination'),
    $_1t7kykx2jd09f043.defaulted('stateAttr', 'data-transitioning-state'),
    $_1t7kykx2jd09f043.strict('initialState'),
    $_1l599yytjd09f0ar.onHandler('onTransition'),
    $_1l599yytjd09f0ar.onHandler('onFinish'),
    $_1t7kykx2jd09f043.strictOf('routes', $_33aoy2xhjd09f061.setOf($_anstx7x8jd09f05b.value, $_33aoy2xhjd09f061.setOf($_anstx7x8jd09f05b.value, $_33aoy2xhjd09f061.objOfOnly([$_1t7kykx2jd09f043.optionObjOfOnly('transition', [
        $_1t7kykx2jd09f043.strict('property'),
        $_1t7kykx2jd09f043.strict('transitionClass')
      ])]))))
  ];

  var createRoutes = function (routes) {
    var r = {};
    $_32a0zdx0jd09f03l.each(routes, function (v, k) {
      var waypoints = k.split('<->');
      r[waypoints[0]] = $_q0etsx6jd09f053.wrap(waypoints[1], v);
      r[waypoints[1]] = $_q0etsx6jd09f053.wrap(waypoints[0], v);
    });
    return r;
  };
  var createBistate = function (first, second, transitions) {
    return $_q0etsx6jd09f053.wrapAll([
      {
        key: first,
        value: $_q0etsx6jd09f053.wrap(second, transitions)
      },
      {
        key: second,
        value: $_q0etsx6jd09f053.wrap(first, transitions)
      }
    ]);
  };
  var createTristate = function (first, second, third, transitions) {
    return $_q0etsx6jd09f053.wrapAll([
      {
        key: first,
        value: $_q0etsx6jd09f053.wrapAll([
          {
            key: second,
            value: transitions
          },
          {
            key: third,
            value: transitions
          }
        ])
      },
      {
        key: second,
        value: $_q0etsx6jd09f053.wrapAll([
          {
            key: first,
            value: transitions
          },
          {
            key: third,
            value: transitions
          }
        ])
      },
      {
        key: third,
        value: $_q0etsx6jd09f053.wrapAll([
          {
            key: first,
            value: transitions
          },
          {
            key: second,
            value: transitions
          }
        ])
      }
    ]);
  };
  var Transitioning = $_fq8al5w4jd09f00e.create({
    fields: TransitionSchema,
    name: 'transitioning',
    active: $_6y1jhx12hjd09f0uq,
    apis: $_1s63b812ijd09f0us,
    extra: {
      createRoutes: createRoutes,
      createBistate: createBistate,
      createTristate: createTristate
    }
  });

  var generateFrom = function (spec, all) {
    var schema = $_bvikd2w9jd09f01c.map(all, function (a) {
      return $_1t7kykx2jd09f043.field(a.name(), a.name(), $_buoc30x3jd09f047.asOption(), $_33aoy2xhjd09f061.objOf([
        $_1t7kykx2jd09f043.strict('config'),
        $_1t7kykx2jd09f043.defaulted('state', $_7dmh49xqjd09f072)
      ]));
    });
    var validated = $_33aoy2xhjd09f061.asStruct('component.behaviours', $_33aoy2xhjd09f061.objOf(schema), spec.behaviours).fold(function (errInfo) {
      throw new Error($_33aoy2xhjd09f061.formatError(errInfo) + '\nComplete spec:\n' + $_2xfxp7xfjd09f05w.stringify(spec, null, 2));
    }, $_8z5eqrwbjd09f01q.identity);
    return {
      list: all,
      data: $_32a0zdx0jd09f03l.map(validated, function (blobOptionThunk) {
        var blobOption = blobOptionThunk();
        return $_8z5eqrwbjd09f01q.constant(blobOption.map(function (blob) {
          return {
            config: blob.config(),
            state: blob.state().init(blob.config())
          };
        }));
      })
    };
  };
  var getBehaviours = function (bData) {
    return bData.list;
  };
  var getData = function (bData) {
    return bData.data;
  };
  var $_f0rkwu12njd09f0w6 = {
    generateFrom: generateFrom,
    getBehaviours: getBehaviours,
    getData: getData
  };

  var getBehaviours$1 = function (spec) {
    var behaviours = $_q0etsx6jd09f053.readOptFrom(spec, 'behaviours').getOr({});
    var keys = $_bvikd2w9jd09f01c.filter($_32a0zdx0jd09f03l.keys(behaviours), function (k) {
      return behaviours[k] !== undefined;
    });
    return $_bvikd2w9jd09f01c.map(keys, function (k) {
      return spec.behaviours[k].me;
    });
  };
  var generateFrom$1 = function (spec, all) {
    return $_f0rkwu12njd09f0w6.generateFrom(spec, all);
  };
  var generate$4 = function (spec) {
    var all = getBehaviours$1(spec);
    return generateFrom$1(spec, all);
  };
  var $_afapnh12mjd09f0w0 = {
    generate: generate$4,
    generateFrom: generateFrom$1
  };

  var ComponentApi = $_6o1y88xsjd09f075.exactly([
    'getSystem',
    'config',
    'hasConfigured',
    'spec',
    'connect',
    'disconnect',
    'element',
    'syncComponents',
    'readState',
    'components',
    'events'
  ]);

  var SystemApi = $_6o1y88xsjd09f075.exactly([
    'debugInfo',
    'triggerFocus',
    'triggerEvent',
    'triggerEscape',
    'addToWorld',
    'removeFromWorld',
    'addToGui',
    'removeFromGui',
    'build',
    'getByUid',
    'getByDom',
    'broadcast',
    'broadcastOn'
  ]);

  function NoContextApi (getComp) {
    var fail = function (event) {
      return function () {
        throw new Error('The component must be in a context to send: ' + event + '\n' + $_9zn22gy9jd09f091.element(getComp().element()) + ' is not in context.');
      };
    };
    return SystemApi({
      debugInfo: $_8z5eqrwbjd09f01q.constant('fake'),
      triggerEvent: fail('triggerEvent'),
      triggerFocus: fail('triggerFocus'),
      triggerEscape: fail('triggerEscape'),
      build: fail('build'),
      addToWorld: fail('addToWorld'),
      removeFromWorld: fail('removeFromWorld'),
      addToGui: fail('addToGui'),
      removeFromGui: fail('removeFromGui'),
      getByUid: fail('getByUid'),
      getByDom: fail('getByDom'),
      broadcast: fail('broadcast'),
      broadcastOn: fail('broadcastOn')
    });
  }

  var byInnerKey = function (data, tuple) {
    var r = {};
    $_32a0zdx0jd09f03l.each(data, function (detail, key) {
      $_32a0zdx0jd09f03l.each(detail, function (value, indexKey) {
        var chain = $_q0etsx6jd09f053.readOr(indexKey, [])(r);
        r[indexKey] = chain.concat([tuple(key, value)]);
      });
    });
    return r;
  };
  var $_5e0r4c12sjd09f0x2 = { byInnerKey: byInnerKey };

  var behaviourDom = function (name, modification) {
    return {
      name: $_8z5eqrwbjd09f01q.constant(name),
      modification: modification
    };
  };
  var concat = function (chain, aspect) {
    var values = $_bvikd2w9jd09f01c.bind(chain, function (c) {
      return c.modification().getOr([]);
    });
    return $_anstx7x8jd09f05b.value($_q0etsx6jd09f053.wrap(aspect, values));
  };
  var onlyOne = function (chain, aspect, order) {
    if (chain.length > 1)
      return $_anstx7x8jd09f05b.error('Multiple behaviours have tried to change DOM "' + aspect + '". The guilty behaviours are: ' + $_2xfxp7xfjd09f05w.stringify($_bvikd2w9jd09f01c.map(chain, function (b) {
        return b.name();
      })) + '. At this stage, this ' + 'is not supported. Future releases might provide strategies for resolving this.');
    else if (chain.length === 0)
      return $_anstx7x8jd09f05b.value({});
    else
      return $_anstx7x8jd09f05b.value(chain[0].modification().fold(function () {
        return {};
      }, function (m) {
        return $_q0etsx6jd09f053.wrap(aspect, m);
      }));
  };
  var duplicate = function (aspect, k, obj, behaviours) {
    return $_anstx7x8jd09f05b.error('Mulitple behaviours have tried to change the _' + k + '_ "' + aspect + '"' + '. The guilty behaviours are: ' + $_2xfxp7xfjd09f05w.stringify($_bvikd2w9jd09f01c.bind(behaviours, function (b) {
      return b.modification().getOr({})[k] !== undefined ? [b.name()] : [];
    }), null, 2) + '. This is not currently supported.');
  };
  var safeMerge = function (chain, aspect) {
    var y = $_bvikd2w9jd09f01c.foldl(chain, function (acc, c) {
      var obj = c.modification().getOr({});
      return acc.bind(function (accRest) {
        var parts = $_32a0zdx0jd09f03l.mapToArray(obj, function (v, k) {
          return accRest[k] !== undefined ? duplicate(aspect, k, obj, chain) : $_anstx7x8jd09f05b.value($_q0etsx6jd09f053.wrap(k, v));
        });
        return $_q0etsx6jd09f053.consolidate(parts, accRest);
      });
    }, $_anstx7x8jd09f05b.value({}));
    return y.map(function (yValue) {
      return $_q0etsx6jd09f053.wrap(aspect, yValue);
    });
  };
  var mergeTypes = {
    classes: concat,
    attributes: safeMerge,
    styles: safeMerge,
    domChildren: onlyOne,
    defChildren: onlyOne,
    innerHtml: onlyOne,
    value: onlyOne
  };
  var combine$1 = function (info, baseMod, behaviours, base) {
    var behaviourDoms = $_3htayhwyjd09f03i.deepMerge({}, baseMod);
    $_bvikd2w9jd09f01c.each(behaviours, function (behaviour) {
      behaviourDoms[behaviour.name()] = behaviour.exhibit(info, base);
    });
    var byAspect = $_5e0r4c12sjd09f0x2.byInnerKey(behaviourDoms, behaviourDom);
    var usedAspect = $_32a0zdx0jd09f03l.map(byAspect, function (values, aspect) {
      return $_bvikd2w9jd09f01c.bind(values, function (value) {
        return value.modification().fold(function () {
          return [];
        }, function (v) {
          return [value];
        });
      });
    });
    var modifications = $_32a0zdx0jd09f03l.mapToArray(usedAspect, function (values, aspect) {
      return $_q0etsx6jd09f053.readOptFrom(mergeTypes, aspect).fold(function () {
        return $_anstx7x8jd09f05b.error('Unknown field type: ' + aspect);
      }, function (merger) {
        return merger(values, aspect);
      });
    });
    var consolidated = $_q0etsx6jd09f053.consolidate(modifications, {});
    return consolidated.map($_brhg4pxkjd09f06j.nu);
  };
  var $_djpso12rjd09f0wn = { combine: combine$1 };

  var sortKeys = function (label, keyName, array, order) {
    var sliced = array.slice(0);
    try {
      var sorted = sliced.sort(function (a, b) {
        var aKey = a[keyName]();
        var bKey = b[keyName]();
        var aIndex = order.indexOf(aKey);
        var bIndex = order.indexOf(bKey);
        if (aIndex === -1)
          throw new Error('The ordering for ' + label + ' does not have an entry for ' + aKey + '.\nOrder specified: ' + $_2xfxp7xfjd09f05w.stringify(order, null, 2));
        if (bIndex === -1)
          throw new Error('The ordering for ' + label + ' does not have an entry for ' + bKey + '.\nOrder specified: ' + $_2xfxp7xfjd09f05w.stringify(order, null, 2));
        if (aIndex < bIndex)
          return -1;
        else if (bIndex < aIndex)
          return 1;
        else
          return 0;
      });
      return $_anstx7x8jd09f05b.value(sorted);
    } catch (err) {
      return $_anstx7x8jd09f05b.error([err]);
    }
  };
  var $_18bt7d12ujd09f0xg = { sortKeys: sortKeys };

  var nu$7 = function (handler, purpose) {
    return {
      handler: handler,
      purpose: $_8z5eqrwbjd09f01q.constant(purpose)
    };
  };
  var curryArgs = function (descHandler, extraArgs) {
    return {
      handler: $_8z5eqrwbjd09f01q.curry.apply(undefined, [descHandler.handler].concat(extraArgs)),
      purpose: descHandler.purpose
    };
  };
  var getHandler = function (descHandler) {
    return descHandler.handler;
  };
  var $_jmzzy12vjd09f0xk = {
    nu: nu$7,
    curryArgs: curryArgs,
    getHandler: getHandler
  };

  var behaviourTuple = function (name, handler) {
    return {
      name: $_8z5eqrwbjd09f01q.constant(name),
      handler: $_8z5eqrwbjd09f01q.constant(handler)
    };
  };
  var nameToHandlers = function (behaviours, info) {
    var r = {};
    $_bvikd2w9jd09f01c.each(behaviours, function (behaviour) {
      r[behaviour.name()] = behaviour.handlers(info);
    });
    return r;
  };
  var groupByEvents = function (info, behaviours, base) {
    var behaviourEvents = $_3htayhwyjd09f03i.deepMerge(base, nameToHandlers(behaviours, info));
    return $_5e0r4c12sjd09f0x2.byInnerKey(behaviourEvents, behaviourTuple);
  };
  var combine$2 = function (info, eventOrder, behaviours, base) {
    var byEventName = groupByEvents(info, behaviours, base);
    return combineGroups(byEventName, eventOrder);
  };
  var assemble = function (rawHandler) {
    var handler = $_cdgkzwx1jd09f03t.read(rawHandler);
    return function (component, simulatedEvent) {
      var args = Array.prototype.slice.call(arguments, 0);
      if (handler.abort.apply(undefined, args)) {
        simulatedEvent.stop();
      } else if (handler.can.apply(undefined, args)) {
        handler.run.apply(undefined, args);
      }
    };
  };
  var missingOrderError = function (eventName, tuples) {
    return new $_anstx7x8jd09f05b.error(['The event (' + eventName + ') has more than one behaviour that listens to it.\nWhen this occurs, you must ' + 'specify an event ordering for the behaviours in your spec (e.g. [ "listing", "toggling" ]).\nThe behaviours that ' + 'can trigger it are: ' + $_2xfxp7xfjd09f05w.stringify($_bvikd2w9jd09f01c.map(tuples, function (c) {
        return c.name();
      }), null, 2)]);
  };
  var fuse$1 = function (tuples, eventOrder, eventName) {
    var order = eventOrder[eventName];
    if (!order)
      return missingOrderError(eventName, tuples);
    else
      return $_18bt7d12ujd09f0xg.sortKeys('Event: ' + eventName, 'name', tuples, order).map(function (sortedTuples) {
        var handlers = $_bvikd2w9jd09f01c.map(sortedTuples, function (tuple) {
          return tuple.handler();
        });
        return $_cdgkzwx1jd09f03t.fuse(handlers);
      });
  };
  var combineGroups = function (byEventName, eventOrder) {
    var r = $_32a0zdx0jd09f03l.mapToArray(byEventName, function (tuples, eventName) {
      var combined = tuples.length === 1 ? $_anstx7x8jd09f05b.value(tuples[0].handler()) : fuse$1(tuples, eventOrder, eventName);
      return combined.map(function (handler) {
        var assembled = assemble(handler);
        var purpose = tuples.length > 1 ? $_bvikd2w9jd09f01c.filter(eventOrder, function (o) {
          return $_bvikd2w9jd09f01c.contains(tuples, function (t) {
            return t.name() === o;
          });
        }).join(' > ') : tuples[0].name();
        return $_q0etsx6jd09f053.wrap(eventName, $_jmzzy12vjd09f0xk.nu(assembled, purpose));
      });
    });
    return $_q0etsx6jd09f053.consolidate(r, {});
  };
  var $_exqtw312tjd09f0x6 = { combine: combine$2 };

  var toInfo = function (spec) {
    return $_33aoy2xhjd09f061.asStruct('custom.definition', $_33aoy2xhjd09f061.objOfOnly([
      $_1t7kykx2jd09f043.field('dom', 'dom', $_buoc30x3jd09f047.strict(), $_33aoy2xhjd09f061.objOfOnly([
        $_1t7kykx2jd09f043.strict('tag'),
        $_1t7kykx2jd09f043.defaulted('styles', {}),
        $_1t7kykx2jd09f043.defaulted('classes', []),
        $_1t7kykx2jd09f043.defaulted('attributes', {}),
        $_1t7kykx2jd09f043.option('value'),
        $_1t7kykx2jd09f043.option('innerHtml')
      ])),
      $_1t7kykx2jd09f043.strict('components'),
      $_1t7kykx2jd09f043.strict('uid'),
      $_1t7kykx2jd09f043.defaulted('events', {}),
      $_1t7kykx2jd09f043.defaulted('apis', $_8z5eqrwbjd09f01q.constant({})),
      $_1t7kykx2jd09f043.field('eventOrder', 'eventOrder', $_buoc30x3jd09f047.mergeWith({
        'alloy.execute': [
          'disabling',
          'alloy.base.behaviour',
          'toggling'
        ],
        'alloy.focus': [
          'alloy.base.behaviour',
          'focusing',
          'keying'
        ],
        'alloy.system.init': [
          'alloy.base.behaviour',
          'disabling',
          'toggling',
          'representing'
        ],
        'input': [
          'alloy.base.behaviour',
          'representing',
          'streaming',
          'invalidating'
        ],
        'alloy.system.detached': [
          'alloy.base.behaviour',
          'representing'
        ]
      }), $_33aoy2xhjd09f061.anyValue()),
      $_1t7kykx2jd09f043.option('domModification'),
      $_1l599yytjd09f0ar.snapshot('originalSpec'),
      $_1t7kykx2jd09f043.defaulted('debug.sketcher', 'unknown')
    ]), spec);
  };
  var getUid = function (info) {
    return $_q0etsx6jd09f053.wrap($_2t9yx510njd09f0l2.idAttr(), info.uid());
  };
  var toDefinition = function (info) {
    var base = {
      tag: info.dom().tag(),
      classes: info.dom().classes(),
      attributes: $_3htayhwyjd09f03i.deepMerge(getUid(info), info.dom().attributes()),
      styles: info.dom().styles(),
      domChildren: $_bvikd2w9jd09f01c.map(info.components(), function (comp) {
        return comp.element();
      })
    };
    return $_dzk95nxljd09f06r.nu($_3htayhwyjd09f03i.deepMerge(base, info.dom().innerHtml().map(function (h) {
      return $_q0etsx6jd09f053.wrap('innerHtml', h);
    }).getOr({}), info.dom().value().map(function (h) {
      return $_q0etsx6jd09f053.wrap('value', h);
    }).getOr({})));
  };
  var toModification = function (info) {
    return info.domModification().fold(function () {
      return $_brhg4pxkjd09f06j.nu({});
    }, $_brhg4pxkjd09f06j.nu);
  };
  var toApis = function (info) {
    return info.apis();
  };
  var toEvents = function (info) {
    return info.events();
  };
  var $_gfydmc12wjd09f0xn = {
    toInfo: toInfo,
    toDefinition: toDefinition,
    toModification: toModification,
    toApis: toApis,
    toEvents: toEvents
  };

  var add$3 = function (element, classes) {
    $_bvikd2w9jd09f01c.each(classes, function (x) {
      $_e7d6ttxujd09f07b.add(element, x);
    });
  };
  var remove$6 = function (element, classes) {
    $_bvikd2w9jd09f01c.each(classes, function (x) {
      $_e7d6ttxujd09f07b.remove(element, x);
    });
  };
  var toggle$3 = function (element, classes) {
    $_bvikd2w9jd09f01c.each(classes, function (x) {
      $_e7d6ttxujd09f07b.toggle(element, x);
    });
  };
  var hasAll = function (element, classes) {
    return $_bvikd2w9jd09f01c.forall(classes, function (clazz) {
      return $_e7d6ttxujd09f07b.has(element, clazz);
    });
  };
  var hasAny = function (element, classes) {
    return $_bvikd2w9jd09f01c.exists(classes, function (clazz) {
      return $_e7d6ttxujd09f07b.has(element, clazz);
    });
  };
  var getNative = function (element) {
    var classList = element.dom().classList;
    var r = new Array(classList.length);
    for (var i = 0; i < classList.length; i++) {
      r[i] = classList.item(i);
    }
    return r;
  };
  var get$10 = function (element) {
    return $_7m9egxyjd09f07l.supports(element) ? getNative(element) : $_7m9egxyjd09f07l.get(element);
  };
  var $_92oj9712yjd09f0yc = {
    add: add$3,
    remove: remove$6,
    toggle: toggle$3,
    hasAll: hasAll,
    hasAny: hasAny,
    get: get$10
  };

  var getChildren = function (definition) {
    if (definition.domChildren().isSome() && definition.defChildren().isSome()) {
      throw new Error('Cannot specify children and child specs! Must be one or the other.\nDef: ' + $_dzk95nxljd09f06r.defToStr(definition));
    } else {
      return definition.domChildren().fold(function () {
        var defChildren = definition.defChildren().getOr([]);
        return $_bvikd2w9jd09f01c.map(defChildren, renderDef);
      }, function (domChildren) {
        return domChildren;
      });
    }
  };
  var renderToDom = function (definition) {
    var subject = $_cnbf2uwtjd09f033.fromTag(definition.tag());
    $_6spjcmxwjd09f07e.setAll(subject, definition.attributes().getOr({}));
    $_92oj9712yjd09f0yc.add(subject, definition.classes().getOr([]));
    $_a9ctnkzsjd09f0ej.setAll(subject, definition.styles().getOr({}));
    $_ck4yooybjd09f095.set(subject, definition.innerHtml().getOr(''));
    var children = getChildren(definition);
    $_9afv6jy6jd09f08m.append(subject, children);
    definition.value().each(function (value) {
      $_eoyy91120jd09f0rq.set(subject, value);
    });
    return subject;
  };
  var renderDef = function (spec) {
    var definition = $_dzk95nxljd09f06r.nu(spec);
    return renderToDom(definition);
  };
  var $_2xhkz612xjd09f0y2 = { renderToDom: renderToDom };

  var build = function (spec) {
    var getMe = function () {
      return me;
    };
    var systemApi = Cell(NoContextApi(getMe));
    var info = $_33aoy2xhjd09f061.getOrDie($_gfydmc12wjd09f0xn.toInfo($_3htayhwyjd09f03i.deepMerge(spec, { behaviours: undefined })));
    var bBlob = $_afapnh12mjd09f0w0.generate(spec);
    var bList = $_f0rkwu12njd09f0w6.getBehaviours(bBlob);
    var bData = $_f0rkwu12njd09f0w6.getData(bBlob);
    var definition = $_gfydmc12wjd09f0xn.toDefinition(info);
    var baseModification = { 'alloy.base.modification': $_gfydmc12wjd09f0xn.toModification(info) };
    var modification = $_djpso12rjd09f0wn.combine(bData, baseModification, bList, definition).getOrDie();
    var modDefinition = $_brhg4pxkjd09f06j.merge(definition, modification);
    var item = $_2xhkz612xjd09f0y2.renderToDom(modDefinition);
    var baseEvents = { 'alloy.base.behaviour': $_gfydmc12wjd09f0xn.toEvents(info) };
    var events = $_exqtw312tjd09f0x6.combine(bData, info.eventOrder(), bList, baseEvents).getOrDie();
    var subcomponents = Cell(info.components());
    var connect = function (newApi) {
      systemApi.set(newApi);
    };
    var disconnect = function () {
      systemApi.set(NoContextApi(getMe));
    };
    var syncComponents = function () {
      var children = $_sdd74y3jd09f08a.children(item);
      var subs = $_bvikd2w9jd09f01c.bind(children, function (child) {
        return systemApi.get().getByDom(child).fold(function () {
          return [];
        }, function (c) {
          return [c];
        });
      });
      subcomponents.set(subs);
    };
    var config = function (behaviour) {
      if (behaviour === $_fdvkh210fjd09f0ij.apiConfig())
        return info.apis();
      var b = bData;
      var f = $_4biushwzjd09f03k.isFunction(b[behaviour.name()]) ? b[behaviour.name()] : function () {
        throw new Error('Could not find ' + behaviour.name() + ' in ' + $_2xfxp7xfjd09f05w.stringify(spec, null, 2));
      };
      return f();
    };
    var hasConfigured = function (behaviour) {
      return $_4biushwzjd09f03k.isFunction(bData[behaviour.name()]);
    };
    var readState = function (behaviourName) {
      return bData[behaviourName]().map(function (b) {
        return b.state.readState();
      }).getOr('not enabled');
    };
    var me = ComponentApi({
      getSystem: systemApi.get,
      config: config,
      hasConfigured: hasConfigured,
      spec: $_8z5eqrwbjd09f01q.constant(spec),
      readState: readState,
      connect: connect,
      disconnect: disconnect,
      element: $_8z5eqrwbjd09f01q.constant(item),
      syncComponents: syncComponents,
      components: subcomponents.get,
      events: $_8z5eqrwbjd09f01q.constant(events)
    });
    return me;
  };
  var $_9r5rw512ljd09f0vj = { build: build };

  var isRecursive = function (component, originator, target) {
    return $_6s6cs1w8jd09f014.eq(originator, component.element()) && !$_6s6cs1w8jd09f014.eq(originator, target);
  };
  var $_418de712zjd09f0yg = {
    events: $_6j84lww6jd09f00y.derive([$_6j84lww6jd09f00y.can($_5iytewwjd09f03c.focus(), function (component, simulatedEvent) {
        var originator = simulatedEvent.event().originator();
        var target = simulatedEvent.event().target();
        if (isRecursive(component, originator, target)) {
          console.warn($_5iytewwjd09f03c.focus() + ' did not get interpreted by the desired target. ' + '\nOriginator: ' + $_9zn22gy9jd09f091.element(originator) + '\nTarget: ' + $_9zn22gy9jd09f091.element(target) + '\nCheck the ' + $_5iytewwjd09f03c.focus() + ' event handlers');
          return false;
        } else {
          return true;
        }
      })])
  };

  var make$1 = function (spec) {
    return spec;
  };
  var $_bip8bd130jd09f0yj = { make: make$1 };

  var buildSubcomponents = function (spec) {
    var components = $_q0etsx6jd09f053.readOr('components', [])(spec);
    return $_bvikd2w9jd09f01c.map(components, build$1);
  };
  var buildFromSpec = function (userSpec) {
    var spec = $_bip8bd130jd09f0yj.make(userSpec);
    var components = buildSubcomponents(spec);
    var completeSpec = $_3htayhwyjd09f03i.deepMerge($_418de712zjd09f0yg, spec, $_q0etsx6jd09f053.wrap('components', components));
    return $_anstx7x8jd09f05b.value($_9r5rw512ljd09f0vj.build(completeSpec));
  };
  var text = function (textContent) {
    var element = $_cnbf2uwtjd09f033.fromText(textContent);
    return external({ element: element });
  };
  var external = function (spec) {
    var extSpec = $_33aoy2xhjd09f061.asStructOrDie('external.component', $_33aoy2xhjd09f061.objOfOnly([
      $_1t7kykx2jd09f043.strict('element'),
      $_1t7kykx2jd09f043.option('uid')
    ]), spec);
    var systemApi = Cell(NoContextApi());
    var connect = function (newApi) {
      systemApi.set(newApi);
    };
    var disconnect = function () {
      systemApi.set(NoContextApi(function () {
        return me;
      }));
    };
    extSpec.uid().each(function (uid) {
      $_69difv10mjd09f0kv.writeOnly(extSpec.element(), uid);
    });
    var me = ComponentApi({
      getSystem: systemApi.get,
      config: $_8nwhzlwajd09f01m.none,
      hasConfigured: $_8z5eqrwbjd09f01q.constant(false),
      connect: connect,
      disconnect: disconnect,
      element: $_8z5eqrwbjd09f01q.constant(extSpec.element()),
      spec: $_8z5eqrwbjd09f01q.constant(spec),
      readState: $_8z5eqrwbjd09f01q.constant('No state'),
      syncComponents: $_8z5eqrwbjd09f01q.noop,
      components: $_8z5eqrwbjd09f01q.constant([]),
      events: $_8z5eqrwbjd09f01q.constant({})
    });
    return $_fdvkh210fjd09f0ij.premade(me);
  };
  var build$1 = function (rawUserSpec) {
    return $_fdvkh210fjd09f0ij.getPremade(rawUserSpec).fold(function () {
      var userSpecWithUid = $_3htayhwyjd09f03i.deepMerge({ uid: $_69difv10mjd09f0kv.generate('') }, rawUserSpec);
      return buildFromSpec(userSpecWithUid).getOrDie();
    }, function (prebuilt) {
      return prebuilt;
    });
  };
  var $_60hy6b12kjd09f0v5 = {
    build: build$1,
    premade: $_fdvkh210fjd09f0ij.premade,
    external: external,
    text: text
  };

  var hoverEvent = 'alloy.item-hover';
  var focusEvent = 'alloy.item-focus';
  var onHover = function (item) {
    if ($_ccs2jvygjd09f09f.search(item.element()).isNone() || Focusing.isFocused(item)) {
      if (!Focusing.isFocused(item))
        Focusing.focus(item);
      $_3b6lb8wvjd09f037.emitWith(item, hoverEvent, { item: item });
    }
  };
  var onFocus = function (item) {
    $_3b6lb8wvjd09f037.emitWith(item, focusEvent, { item: item });
  };
  var $_1azhjm134jd09f0yz = {
    hover: $_8z5eqrwbjd09f01q.constant(hoverEvent),
    focus: $_8z5eqrwbjd09f01q.constant(focusEvent),
    onHover: onHover,
    onFocus: onFocus
  };

  var builder = function (info) {
    return {
      dom: $_3htayhwyjd09f03i.deepMerge(info.dom(), { attributes: { role: info.toggling().isSome() ? 'menuitemcheckbox' : 'menuitem' } }),
      behaviours: $_3htayhwyjd09f03i.deepMerge($_fq8al5w4jd09f00e.derive([
        info.toggling().fold(Toggling.revoke, function (tConfig) {
          return Toggling.config($_3htayhwyjd09f03i.deepMerge({ aria: { mode: 'checked' } }, tConfig));
        }),
        Focusing.config({
          ignore: info.ignoreFocus(),
          onFocus: function (component) {
            $_1azhjm134jd09f0yz.onFocus(component);
          }
        }),
        Keying.config({ mode: 'execution' }),
        me.config({
          store: {
            mode: 'memory',
            initialValue: info.data()
          }
        })
      ]), info.itemBehaviours()),
      events: $_6j84lww6jd09f00y.derive([
        $_6j84lww6jd09f00y.runWithTarget($_5iytewwjd09f03c.tapOrClick(), $_3b6lb8wvjd09f037.emitExecute),
        $_6j84lww6jd09f00y.cutter($_8nqyjjwxjd09f03f.mousedown()),
        $_6j84lww6jd09f00y.run($_8nqyjjwxjd09f03f.mouseover(), $_1azhjm134jd09f0yz.onHover),
        $_6j84lww6jd09f00y.run($_5iytewwjd09f03c.focusItem(), Focusing.focus)
      ]),
      components: info.components(),
      domModification: info.domModification()
    };
  };
  var schema$10 = [
    $_1t7kykx2jd09f043.strict('data'),
    $_1t7kykx2jd09f043.strict('components'),
    $_1t7kykx2jd09f043.strict('dom'),
    $_1t7kykx2jd09f043.option('toggling'),
    $_1t7kykx2jd09f043.defaulted('itemBehaviours', {}),
    $_1t7kykx2jd09f043.defaulted('ignoreFocus', false),
    $_1t7kykx2jd09f043.defaulted('domModification', {}),
    $_1l599yytjd09f0ar.output('builder', builder)
  ];

  var builder$1 = function (detail) {
    return {
      dom: detail.dom(),
      components: detail.components(),
      events: $_6j84lww6jd09f00y.derive([$_6j84lww6jd09f00y.stopper($_5iytewwjd09f03c.focusItem())])
    };
  };
  var schema$11 = [
    $_1t7kykx2jd09f043.strict('dom'),
    $_1t7kykx2jd09f043.strict('components'),
    $_1l599yytjd09f0ar.output('builder', builder$1)
  ];

  var owner$2 = 'item-widget';
  var partTypes = [$_37ihar10kjd09f0js.required({
      name: 'widget',
      overrides: function (detail) {
        return {
          behaviours: $_fq8al5w4jd09f00e.derive([me.config({
              store: {
                mode: 'manual',
                getValue: function (component) {
                  return detail.data();
                },
                setValue: function () {
                }
              }
            })])
        };
      }
    })];
  var $_eu6vl137jd09f0zd = {
    owner: $_8z5eqrwbjd09f01q.constant(owner$2),
    parts: $_8z5eqrwbjd09f01q.constant(partTypes)
  };

  var builder$2 = function (info) {
    var subs = $_7vfhnq10ijd09f0iz.substitutes($_eu6vl137jd09f0zd.owner(), info, $_eu6vl137jd09f0zd.parts());
    var components = $_7vfhnq10ijd09f0iz.components($_eu6vl137jd09f0zd.owner(), info, subs.internals());
    var focusWidget = function (component) {
      return $_7vfhnq10ijd09f0iz.getPart(component, info, 'widget').map(function (widget) {
        Keying.focusIn(widget);
        return widget;
      });
    };
    var onHorizontalArrow = function (component, simulatedEvent) {
      return $_brva0izxjd09f0f6.inside(simulatedEvent.event().target()) ? $_8nwhzlwajd09f01m.none() : function () {
        if (info.autofocus()) {
          simulatedEvent.setSource(component.element());
          return $_8nwhzlwajd09f01m.none();
        } else {
          return $_8nwhzlwajd09f01m.none();
        }
      }();
    };
    return $_3htayhwyjd09f03i.deepMerge({
      dom: info.dom(),
      components: components,
      domModification: info.domModification(),
      events: $_6j84lww6jd09f00y.derive([
        $_6j84lww6jd09f00y.runOnExecute(function (component, simulatedEvent) {
          focusWidget(component).each(function (widget) {
            simulatedEvent.stop();
          });
        }),
        $_6j84lww6jd09f00y.run($_8nqyjjwxjd09f03f.mouseover(), $_1azhjm134jd09f0yz.onHover),
        $_6j84lww6jd09f00y.run($_5iytewwjd09f03c.focusItem(), function (component, simulatedEvent) {
          if (info.autofocus())
            focusWidget(component);
          else
            Focusing.focus(component);
        })
      ]),
      behaviours: $_fq8al5w4jd09f00e.derive([
        me.config({
          store: {
            mode: 'memory',
            initialValue: info.data()
          }
        }),
        Focusing.config({
          onFocus: function (component) {
            $_1azhjm134jd09f0yz.onFocus(component);
          }
        }),
        Keying.config({
          mode: 'special',
          onLeft: onHorizontalArrow,
          onRight: onHorizontalArrow,
          onEscape: function (component, simulatedEvent) {
            if (!Focusing.isFocused(component) && !info.autofocus()) {
              Focusing.focus(component);
              return $_8nwhzlwajd09f01m.some(true);
            } else if (info.autofocus()) {
              simulatedEvent.setSource(component.element());
              return $_8nwhzlwajd09f01m.none();
            } else {
              return $_8nwhzlwajd09f01m.none();
            }
          }
        })
      ])
    });
  };
  var schema$12 = [
    $_1t7kykx2jd09f043.strict('uid'),
    $_1t7kykx2jd09f043.strict('data'),
    $_1t7kykx2jd09f043.strict('components'),
    $_1t7kykx2jd09f043.strict('dom'),
    $_1t7kykx2jd09f043.defaulted('autofocus', false),
    $_1t7kykx2jd09f043.defaulted('domModification', {}),
    $_7vfhnq10ijd09f0iz.defaultUidsSchema($_eu6vl137jd09f0zd.parts()),
    $_1l599yytjd09f0ar.output('builder', builder$2)
  ];

  var itemSchema$1 = $_33aoy2xhjd09f061.choose('type', {
    widget: schema$12,
    item: schema$10,
    separator: schema$11
  });
  var configureGrid = function (detail, movementInfo) {
    return {
      mode: 'flatgrid',
      selector: '.' + detail.markers().item(),
      initSize: {
        numColumns: movementInfo.initSize().numColumns(),
        numRows: movementInfo.initSize().numRows()
      },
      focusManager: detail.focusManager()
    };
  };
  var configureMenu = function (detail, movementInfo) {
    return {
      mode: 'menu',
      selector: '.' + detail.markers().item(),
      moveOnTab: movementInfo.moveOnTab(),
      focusManager: detail.focusManager()
    };
  };
  var parts = [$_37ihar10kjd09f0js.group({
      factory: {
        sketch: function (spec) {
          var itemInfo = $_33aoy2xhjd09f061.asStructOrDie('menu.spec item', itemSchema$1, spec);
          return itemInfo.builder()(itemInfo);
        }
      },
      name: 'items',
      unit: 'item',
      defaults: function (detail, u) {
        var fallbackUid = $_69difv10mjd09f0kv.generate('');
        return $_3htayhwyjd09f03i.deepMerge({ uid: fallbackUid }, u);
      },
      overrides: function (detail, u) {
        return {
          type: u.type,
          ignoreFocus: detail.fakeFocus(),
          domModification: { classes: [detail.markers().item()] }
        };
      }
    })];
  var schema$13 = [
    $_1t7kykx2jd09f043.strict('value'),
    $_1t7kykx2jd09f043.strict('items'),
    $_1t7kykx2jd09f043.strict('dom'),
    $_1t7kykx2jd09f043.strict('components'),
    $_1t7kykx2jd09f043.defaulted('eventOrder', {}),
    $_beskyy10djd09f0i0.field('menuBehaviours', [
      Highlighting,
      me,
      Composing,
      Keying
    ]),
    $_1t7kykx2jd09f043.defaultedOf('movement', {
      mode: 'menu',
      moveOnTab: true
    }, $_33aoy2xhjd09f061.choose('mode', {
      grid: [
        $_1l599yytjd09f0ar.initSize(),
        $_1l599yytjd09f0ar.output('config', configureGrid)
      ],
      menu: [
        $_1t7kykx2jd09f043.defaulted('moveOnTab', true),
        $_1l599yytjd09f0ar.output('config', configureMenu)
      ]
    })),
    $_1l599yytjd09f0ar.itemMarkers(),
    $_1t7kykx2jd09f043.defaulted('fakeFocus', false),
    $_1t7kykx2jd09f043.defaulted('focusManager', $_axg0l9zgjd09f0dd.dom()),
    $_1l599yytjd09f0ar.onHandler('onHighlight')
  ];
  var $_4iokaw132jd09f0ym = {
    name: $_8z5eqrwbjd09f01q.constant('Menu'),
    schema: $_8z5eqrwbjd09f01q.constant(schema$13),
    parts: $_8z5eqrwbjd09f01q.constant(parts)
  };

  var focusEvent$1 = 'alloy.menu-focus';
  var $_6dcn9y139jd09f0zr = { focus: $_8z5eqrwbjd09f01q.constant(focusEvent$1) };

  var make$2 = function (detail, components, spec, externals) {
    return $_3htayhwyjd09f03i.deepMerge({
      dom: $_3htayhwyjd09f03i.deepMerge(detail.dom(), { attributes: { role: 'menu' } }),
      uid: detail.uid(),
      behaviours: $_3htayhwyjd09f03i.deepMerge($_fq8al5w4jd09f00e.derive([
        Highlighting.config({
          highlightClass: detail.markers().selectedItem(),
          itemClass: detail.markers().item(),
          onHighlight: detail.onHighlight()
        }),
        me.config({
          store: {
            mode: 'memory',
            initialValue: detail.value()
          }
        }),
        Composing.config({ find: $_8z5eqrwbjd09f01q.identity }),
        Keying.config(detail.movement().config()(detail, detail.movement()))
      ]), $_beskyy10djd09f0i0.get(detail.menuBehaviours())),
      events: $_6j84lww6jd09f00y.derive([
        $_6j84lww6jd09f00y.run($_1azhjm134jd09f0yz.focus(), function (menu, simulatedEvent) {
          var event = simulatedEvent.event();
          menu.getSystem().getByDom(event.target()).each(function (item) {
            Highlighting.highlight(menu, item);
            simulatedEvent.stop();
            $_3b6lb8wvjd09f037.emitWith(menu, $_6dcn9y139jd09f0zr.focus(), {
              menu: menu,
              item: item
            });
          });
        }),
        $_6j84lww6jd09f00y.run($_1azhjm134jd09f0yz.hover(), function (menu, simulatedEvent) {
          var item = simulatedEvent.event().item();
          Highlighting.highlight(menu, item);
        })
      ]),
      components: components,
      eventOrder: detail.eventOrder()
    });
  };
  var $_dyuhs2138jd09f0zh = { make: make$2 };

  var Menu = $_4q1unv10ejd09f0i8.composite({
    name: 'Menu',
    configFields: $_4iokaw132jd09f0ym.schema(),
    partFields: $_4iokaw132jd09f0ym.parts(),
    factory: $_dyuhs2138jd09f0zh.make
  });

  var preserve$2 = function (f, container) {
    var ownerDoc = $_sdd74y3jd09f08a.owner(container);
    var refocus = $_ccs2jvygjd09f09f.active(ownerDoc).bind(function (focused) {
      var hasFocus = function (elem) {
        return $_6s6cs1w8jd09f014.eq(focused, elem);
      };
      return hasFocus(container) ? $_8nwhzlwajd09f01m.some(container) : $_605gqayijd09f09k.descendant(container, hasFocus);
    });
    var result = f(container);
    refocus.each(function (oldFocus) {
      $_ccs2jvygjd09f09f.active(ownerDoc).filter(function (newFocus) {
        return $_6s6cs1w8jd09f014.eq(newFocus, oldFocus);
      }).orThunk(function () {
        $_ccs2jvygjd09f09f.focus(oldFocus);
      });
    });
    return result;
  };
  var $_2bwfv713djd09f105 = { preserve: preserve$2 };

  var set$7 = function (component, replaceConfig, replaceState, data) {
    $_crdx3oy1jd09f07s.detachChildren(component);
    $_2bwfv713djd09f105.preserve(function () {
      var children = $_bvikd2w9jd09f01c.map(data, component.getSystem().build);
      $_bvikd2w9jd09f01c.each(children, function (l) {
        $_crdx3oy1jd09f07s.attach(component, l);
      });
    }, component.element());
  };
  var insert = function (component, replaceConfig, insertion, childSpec) {
    var child = component.getSystem().build(childSpec);
    $_crdx3oy1jd09f07s.attachWith(component, child, insertion);
  };
  var append$2 = function (component, replaceConfig, replaceState, appendee) {
    insert(component, replaceConfig, $_3dpbyky2jd09f081.append, appendee);
  };
  var prepend$2 = function (component, replaceConfig, replaceState, prependee) {
    insert(component, replaceConfig, $_3dpbyky2jd09f081.prepend, prependee);
  };
  var remove$7 = function (component, replaceConfig, replaceState, removee) {
    var children = contents(component, replaceConfig);
    var foundChild = $_bvikd2w9jd09f01c.find(children, function (child) {
      return $_6s6cs1w8jd09f014.eq(removee.element(), child.element());
    });
    foundChild.each($_crdx3oy1jd09f07s.detach);
  };
  var contents = function (component, replaceConfig) {
    return component.components();
  };
  var $_7qofoh13cjd09f100 = {
    append: append$2,
    prepend: prepend$2,
    remove: remove$7,
    set: set$7,
    contents: contents
  };

  var Replacing = $_fq8al5w4jd09f00e.create({
    fields: [],
    name: 'replacing',
    apis: $_7qofoh13cjd09f100
  });

  var transpose = function (obj) {
    return $_32a0zdx0jd09f03l.tupleMap(obj, function (v, k) {
      return {
        k: v,
        v: k
      };
    });
  };
  var trace = function (items, byItem, byMenu, finish) {
    return $_q0etsx6jd09f053.readOptFrom(byMenu, finish).bind(function (triggerItem) {
      return $_q0etsx6jd09f053.readOptFrom(items, triggerItem).bind(function (triggerMenu) {
        var rest = trace(items, byItem, byMenu, triggerMenu);
        return $_8nwhzlwajd09f01m.some([triggerMenu].concat(rest));
      });
    }).getOr([]);
  };
  var generate$5 = function (menus, expansions) {
    var items = {};
    $_32a0zdx0jd09f03l.each(menus, function (menuItems, menu) {
      $_bvikd2w9jd09f01c.each(menuItems, function (item) {
        items[item] = menu;
      });
    });
    var byItem = expansions;
    var byMenu = transpose(expansions);
    var menuPaths = $_32a0zdx0jd09f03l.map(byMenu, function (triggerItem, submenu) {
      return [submenu].concat(trace(items, byItem, byMenu, submenu));
    });
    return $_32a0zdx0jd09f03l.map(items, function (path) {
      return $_q0etsx6jd09f053.readOptFrom(menuPaths, path).getOr([path]);
    });
  };
  var $_8chp0i13gjd09f115 = { generate: generate$5 };

  function LayeredState () {
    var expansions = Cell({});
    var menus = Cell({});
    var paths = Cell({});
    var primary = Cell($_8nwhzlwajd09f01m.none());
    var toItemValues = Cell($_8z5eqrwbjd09f01q.constant([]));
    var clear = function () {
      expansions.set({});
      menus.set({});
      paths.set({});
      primary.set($_8nwhzlwajd09f01m.none());
    };
    var isClear = function () {
      return primary.get().isNone();
    };
    var setContents = function (sPrimary, sMenus, sExpansions, sToItemValues) {
      primary.set($_8nwhzlwajd09f01m.some(sPrimary));
      expansions.set(sExpansions);
      menus.set(sMenus);
      toItemValues.set(sToItemValues);
      var menuValues = sToItemValues(sMenus);
      var sPaths = $_8chp0i13gjd09f115.generate(menuValues, sExpansions);
      paths.set(sPaths);
    };
    var expand = function (itemValue) {
      return $_q0etsx6jd09f053.readOptFrom(expansions.get(), itemValue).map(function (menu) {
        var current = $_q0etsx6jd09f053.readOptFrom(paths.get(), itemValue).getOr([]);
        return [menu].concat(current);
      });
    };
    var collapse = function (itemValue) {
      return $_q0etsx6jd09f053.readOptFrom(paths.get(), itemValue).bind(function (path) {
        return path.length > 1 ? $_8nwhzlwajd09f01m.some(path.slice(1)) : $_8nwhzlwajd09f01m.none();
      });
    };
    var refresh = function (itemValue) {
      return $_q0etsx6jd09f053.readOptFrom(paths.get(), itemValue);
    };
    var lookupMenu = function (menuValue) {
      return $_q0etsx6jd09f053.readOptFrom(menus.get(), menuValue);
    };
    var otherMenus = function (path) {
      var menuValues = toItemValues.get()(menus.get());
      return $_bvikd2w9jd09f01c.difference($_32a0zdx0jd09f03l.keys(menuValues), path);
    };
    var getPrimary = function () {
      return primary.get().bind(lookupMenu);
    };
    var getMenus = function () {
      return menus.get();
    };
    return {
      setContents: setContents,
      expand: expand,
      refresh: refresh,
      collapse: collapse,
      lookupMenu: lookupMenu,
      otherMenus: otherMenus,
      getPrimary: getPrimary,
      getMenus: getMenus,
      clear: clear,
      isClear: isClear
    };
  }

  var make$3 = function (detail, rawUiSpec) {
    var buildMenus = function (container, menus) {
      return $_32a0zdx0jd09f03l.map(menus, function (spec, name) {
        var data = Menu.sketch($_3htayhwyjd09f03i.deepMerge(spec, {
          value: name,
          items: spec.items,
          markers: $_q0etsx6jd09f053.narrow(rawUiSpec.markers, [
            'item',
            'selectedItem'
          ]),
          fakeFocus: detail.fakeFocus(),
          onHighlight: detail.onHighlight(),
          focusManager: detail.fakeFocus() ? $_axg0l9zgjd09f0dd.highlights() : $_axg0l9zgjd09f0dd.dom()
        }));
        return container.getSystem().build(data);
      });
    };
    var state = LayeredState();
    var setup = function (container) {
      var componentMap = buildMenus(container, detail.data().menus());
      state.setContents(detail.data().primary(), componentMap, detail.data().expansions(), function (sMenus) {
        return toMenuValues(container, sMenus);
      });
      return state.getPrimary();
    };
    var getItemValue = function (item) {
      return me.getValue(item).value;
    };
    var toMenuValues = function (container, sMenus) {
      return $_32a0zdx0jd09f03l.map(detail.data().menus(), function (data, menuName) {
        return $_bvikd2w9jd09f01c.bind(data.items, function (item) {
          return item.type === 'separator' ? [] : [item.data.value];
        });
      });
    };
    var setActiveMenu = function (container, menu) {
      Highlighting.highlight(container, menu);
      Highlighting.getHighlighted(menu).orThunk(function () {
        return Highlighting.getFirst(menu);
      }).each(function (item) {
        $_3b6lb8wvjd09f037.dispatch(container, item.element(), $_5iytewwjd09f03c.focusItem());
      });
    };
    var getMenus = function (state, menuValues) {
      return $_851ithyejd09f09c.cat($_bvikd2w9jd09f01c.map(menuValues, state.lookupMenu));
    };
    var updateMenuPath = function (container, state, path) {
      return $_8nwhzlwajd09f01m.from(path[0]).bind(state.lookupMenu).map(function (activeMenu) {
        var rest = getMenus(state, path.slice(1));
        $_bvikd2w9jd09f01c.each(rest, function (r) {
          $_e7d6ttxujd09f07b.add(r.element(), detail.markers().backgroundMenu());
        });
        if (!$_7m8dypy7jd09f08o.inBody(activeMenu.element())) {
          Replacing.append(container, $_60hy6b12kjd09f0v5.premade(activeMenu));
        }
        $_92oj9712yjd09f0yc.remove(activeMenu.element(), [detail.markers().backgroundMenu()]);
        setActiveMenu(container, activeMenu);
        var others = getMenus(state, state.otherMenus(path));
        $_bvikd2w9jd09f01c.each(others, function (o) {
          $_92oj9712yjd09f0yc.remove(o.element(), [detail.markers().backgroundMenu()]);
          if (!detail.stayInDom())
            Replacing.remove(container, o);
        });
        return activeMenu;
      });
    };
    var expandRight = function (container, item) {
      var value = getItemValue(item);
      return state.expand(value).bind(function (path) {
        $_8nwhzlwajd09f01m.from(path[0]).bind(state.lookupMenu).each(function (activeMenu) {
          if (!$_7m8dypy7jd09f08o.inBody(activeMenu.element())) {
            Replacing.append(container, $_60hy6b12kjd09f0v5.premade(activeMenu));
          }
          detail.onOpenSubmenu()(container, item, activeMenu);
          Highlighting.highlightFirst(activeMenu);
        });
        return updateMenuPath(container, state, path);
      });
    };
    var collapseLeft = function (container, item) {
      var value = getItemValue(item);
      return state.collapse(value).bind(function (path) {
        return updateMenuPath(container, state, path).map(function (activeMenu) {
          detail.onCollapseMenu()(container, item, activeMenu);
          return activeMenu;
        });
      });
    };
    var updateView = function (container, item) {
      var value = getItemValue(item);
      return state.refresh(value).bind(function (path) {
        return updateMenuPath(container, state, path);
      });
    };
    var onRight = function (container, item) {
      return $_brva0izxjd09f0f6.inside(item.element()) ? $_8nwhzlwajd09f01m.none() : expandRight(container, item);
    };
    var onLeft = function (container, item) {
      return $_brva0izxjd09f0f6.inside(item.element()) ? $_8nwhzlwajd09f01m.none() : collapseLeft(container, item);
    };
    var onEscape = function (container, item) {
      return collapseLeft(container, item).orThunk(function () {
        return detail.onEscape()(container, item);
      });
    };
    var keyOnItem = function (f) {
      return function (container, simulatedEvent) {
        return $_74vb1xzmjd09f0e3.closest(simulatedEvent.getSource(), '.' + detail.markers().item()).bind(function (target) {
          return container.getSystem().getByDom(target).bind(function (item) {
            return f(container, item);
          });
        });
      };
    };
    var events = $_6j84lww6jd09f00y.derive([
      $_6j84lww6jd09f00y.run($_6dcn9y139jd09f0zr.focus(), function (sandbox, simulatedEvent) {
        var menu = simulatedEvent.event().menu();
        Highlighting.highlight(sandbox, menu);
      }),
      $_6j84lww6jd09f00y.runOnExecute(function (sandbox, simulatedEvent) {
        var target = simulatedEvent.event().target();
        return sandbox.getSystem().getByDom(target).bind(function (item) {
          var itemValue = getItemValue(item);
          if (itemValue.indexOf('collapse-item') === 0) {
            return collapseLeft(sandbox, item);
          }
          return expandRight(sandbox, item).orThunk(function () {
            return detail.onExecute()(sandbox, item);
          });
        });
      }),
      $_6j84lww6jd09f00y.runOnAttached(function (container, simulatedEvent) {
        setup(container).each(function (primary) {
          Replacing.append(container, $_60hy6b12kjd09f0v5.premade(primary));
          if (detail.openImmediately()) {
            setActiveMenu(container, primary);
            detail.onOpenMenu()(container, primary);
          }
        });
      })
    ].concat(detail.navigateOnHover() ? [$_6j84lww6jd09f00y.run($_1azhjm134jd09f0yz.hover(), function (sandbox, simulatedEvent) {
        var item = simulatedEvent.event().item();
        updateView(sandbox, item);
        expandRight(sandbox, item);
        detail.onHover()(sandbox, item);
      })] : []));
    var collapseMenuApi = function (container) {
      Highlighting.getHighlighted(container).each(function (currentMenu) {
        Highlighting.getHighlighted(currentMenu).each(function (currentItem) {
          collapseLeft(container, currentItem);
        });
      });
    };
    return {
      uid: detail.uid(),
      dom: detail.dom(),
      behaviours: $_3htayhwyjd09f03i.deepMerge($_fq8al5w4jd09f00e.derive([
        Keying.config({
          mode: 'special',
          onRight: keyOnItem(onRight),
          onLeft: keyOnItem(onLeft),
          onEscape: keyOnItem(onEscape),
          focusIn: function (container, keyInfo) {
            state.getPrimary().each(function (primary) {
              $_3b6lb8wvjd09f037.dispatch(container, primary.element(), $_5iytewwjd09f03c.focusItem());
            });
          }
        }),
        Highlighting.config({
          highlightClass: detail.markers().selectedMenu(),
          itemClass: detail.markers().menu()
        }),
        Composing.config({
          find: function (container) {
            return Highlighting.getHighlighted(container);
          }
        }),
        Replacing.config({})
      ]), $_beskyy10djd09f0i0.get(detail.tmenuBehaviours())),
      eventOrder: detail.eventOrder(),
      apis: { collapseMenu: collapseMenuApi },
      events: events
    };
  };
  var $_feb49l13ejd09f10d = {
    make: make$3,
    collapseItem: $_8z5eqrwbjd09f01q.constant('collapse-item')
  };

  var tieredData = function (primary, menus, expansions) {
    return {
      primary: primary,
      menus: menus,
      expansions: expansions
    };
  };
  var singleData = function (name, menu) {
    return {
      primary: name,
      menus: $_q0etsx6jd09f053.wrap(name, menu),
      expansions: {}
    };
  };
  var collapseItem = function (text) {
    return {
      value: $_3x6e2m10gjd09f0ir.generate($_feb49l13ejd09f10d.collapseItem()),
      text: text
    };
  };
  var TieredMenu = $_4q1unv10ejd09f0i8.single({
    name: 'TieredMenu',
    configFields: [
      $_1l599yytjd09f0ar.onStrictKeyboardHandler('onExecute'),
      $_1l599yytjd09f0ar.onStrictKeyboardHandler('onEscape'),
      $_1l599yytjd09f0ar.onStrictHandler('onOpenMenu'),
      $_1l599yytjd09f0ar.onStrictHandler('onOpenSubmenu'),
      $_1l599yytjd09f0ar.onHandler('onCollapseMenu'),
      $_1t7kykx2jd09f043.defaulted('openImmediately', true),
      $_1t7kykx2jd09f043.strictObjOf('data', [
        $_1t7kykx2jd09f043.strict('primary'),
        $_1t7kykx2jd09f043.strict('menus'),
        $_1t7kykx2jd09f043.strict('expansions')
      ]),
      $_1t7kykx2jd09f043.defaulted('fakeFocus', false),
      $_1l599yytjd09f0ar.onHandler('onHighlight'),
      $_1l599yytjd09f0ar.onHandler('onHover'),
      $_1l599yytjd09f0ar.tieredMenuMarkers(),
      $_1t7kykx2jd09f043.strict('dom'),
      $_1t7kykx2jd09f043.defaulted('navigateOnHover', true),
      $_1t7kykx2jd09f043.defaulted('stayInDom', false),
      $_beskyy10djd09f0i0.field('tmenuBehaviours', [
        Keying,
        Highlighting,
        Composing,
        Replacing
      ]),
      $_1t7kykx2jd09f043.defaulted('eventOrder', {})
    ],
    apis: {
      collapseMenu: function (apis, tmenu) {
        apis.collapseMenu(tmenu);
      }
    },
    factory: $_feb49l13ejd09f10d.make,
    extraApis: {
      tieredData: tieredData,
      singleData: singleData,
      collapseItem: collapseItem
    }
  });

  var scrollable = $_3584u7z1jd09f0bs.resolve('scrollable');
  var register = function (element) {
    $_e7d6ttxujd09f07b.add(element, scrollable);
  };
  var deregister = function (element) {
    $_e7d6ttxujd09f07b.remove(element, scrollable);
  };
  var $_1p83kb13hjd09f11i = {
    register: register,
    deregister: deregister,
    scrollable: $_8z5eqrwbjd09f01q.constant(scrollable)
  };

  var getValue$4 = function (item) {
    return $_q0etsx6jd09f053.readOptFrom(item, 'format').getOr(item.title);
  };
  var convert$1 = function (formats, memMenuThunk) {
    var mainMenu = makeMenu('Styles', [].concat($_bvikd2w9jd09f01c.map(formats.items, function (k) {
      return makeItem(getValue$4(k), k.title, k.isSelected(), k.getPreview(), $_q0etsx6jd09f053.hasKey(formats.expansions, getValue$4(k)));
    })), memMenuThunk, false);
    var submenus = $_32a0zdx0jd09f03l.map(formats.menus, function (menuItems, menuName) {
      var items = $_bvikd2w9jd09f01c.map(menuItems, function (item) {
        return makeItem(getValue$4(item), item.title, item.isSelected !== undefined ? item.isSelected() : false, item.getPreview !== undefined ? item.getPreview() : '', $_q0etsx6jd09f053.hasKey(formats.expansions, getValue$4(item)));
      });
      return makeMenu(menuName, items, memMenuThunk, true);
    });
    var menus = $_3htayhwyjd09f03i.deepMerge(submenus, $_q0etsx6jd09f053.wrap('styles', mainMenu));
    var tmenu = TieredMenu.tieredData('styles', menus, formats.expansions);
    return { tmenu: tmenu };
  };
  var makeItem = function (value, text, selected, preview, isMenu) {
    return {
      data: {
        value: value,
        text: text
      },
      type: 'item',
      dom: {
        tag: 'div',
        classes: isMenu ? [$_3584u7z1jd09f0bs.resolve('styles-item-is-menu')] : []
      },
      toggling: {
        toggleOnExecute: false,
        toggleClass: $_3584u7z1jd09f0bs.resolve('format-matches'),
        selected: selected
      },
      itemBehaviours: $_fq8al5w4jd09f00e.derive(isMenu ? [] : [$_2jq3vfz0jd09f0bp.format(value, function (comp, status) {
          var toggle = status ? Toggling.on : Toggling.off;
          toggle(comp);
        })]),
      components: [{
          dom: {
            tag: 'div',
            attributes: { style: preview },
            innerHtml: text
          }
        }]
    };
  };
  var makeMenu = function (value, items, memMenuThunk, collapsable) {
    return {
      value: value,
      dom: { tag: 'div' },
      components: [
        Button.sketch({
          dom: {
            tag: 'div',
            classes: [$_3584u7z1jd09f0bs.resolve('styles-collapser')]
          },
          components: collapsable ? [
            {
              dom: {
                tag: 'span',
                classes: [$_3584u7z1jd09f0bs.resolve('styles-collapse-icon')]
              }
            },
            $_60hy6b12kjd09f0v5.text(value)
          ] : [$_60hy6b12kjd09f0v5.text(value)],
          action: function (item) {
            if (collapsable) {
              var comp = memMenuThunk().get(item);
              TieredMenu.collapseMenu(comp);
            }
          }
        }),
        {
          dom: {
            tag: 'div',
            classes: [$_3584u7z1jd09f0bs.resolve('styles-menu-items-container')]
          },
          components: [Menu.parts().items({})],
          behaviours: $_fq8al5w4jd09f00e.derive([$_2qc4or11sjd09f0qy.config('adhoc-scrollable-menu', [
              $_6j84lww6jd09f00y.runOnAttached(function (component, simulatedEvent) {
                $_a9ctnkzsjd09f0ej.set(component.element(), 'overflow-y', 'auto');
                $_a9ctnkzsjd09f0ej.set(component.element(), '-webkit-overflow-scrolling', 'touch');
                $_1p83kb13hjd09f11i.register(component.element());
              }),
              $_6j84lww6jd09f00y.runOnDetached(function (component) {
                $_a9ctnkzsjd09f0ej.remove(component.element(), 'overflow-y');
                $_a9ctnkzsjd09f0ej.remove(component.element(), '-webkit-overflow-scrolling');
                $_1p83kb13hjd09f11i.deregister(component.element());
              })
            ])])
        }
      ],
      items: items,
      menuBehaviours: $_fq8al5w4jd09f00e.derive([Transitioning.config({
          initialState: 'after',
          routes: Transitioning.createTristate('before', 'current', 'after', {
            transition: {
              property: 'transform',
              transitionClass: 'transitioning'
            }
          })
        })])
    };
  };
  var sketch$9 = function (settings) {
    var dataset = convert$1(settings.formats, function () {
      return memMenu;
    });
    var memMenu = $_16gwpy11ejd09f0p7.record(TieredMenu.sketch({
      dom: {
        tag: 'div',
        classes: [$_3584u7z1jd09f0bs.resolve('styles-menu')]
      },
      components: [],
      fakeFocus: true,
      stayInDom: true,
      onExecute: function (tmenu, item) {
        var v = me.getValue(item);
        settings.handle(item, v.value);
      },
      onEscape: function () {
      },
      onOpenMenu: function (container, menu) {
        var w = $_cyexp9117jd09f0o3.get(container.element());
        $_cyexp9117jd09f0o3.set(menu.element(), w);
        Transitioning.jumpTo(menu, 'current');
      },
      onOpenSubmenu: function (container, item, submenu) {
        var w = $_cyexp9117jd09f0o3.get(container.element());
        var menu = $_74vb1xzmjd09f0e3.ancestor(item.element(), '[role="menu"]').getOrDie('hacky');
        var menuComp = container.getSystem().getByDom(menu).getOrDie();
        $_cyexp9117jd09f0o3.set(submenu.element(), w);
        Transitioning.progressTo(menuComp, 'before');
        Transitioning.jumpTo(submenu, 'after');
        Transitioning.progressTo(submenu, 'current');
      },
      onCollapseMenu: function (container, item, menu) {
        var submenu = $_74vb1xzmjd09f0e3.ancestor(item.element(), '[role="menu"]').getOrDie('hacky');
        var submenuComp = container.getSystem().getByDom(submenu).getOrDie();
        Transitioning.progressTo(submenuComp, 'after');
        Transitioning.progressTo(menu, 'current');
      },
      navigateOnHover: false,
      openImmediately: true,
      data: dataset.tmenu,
      markers: {
        backgroundMenu: $_3584u7z1jd09f0bs.resolve('styles-background-menu'),
        menu: $_3584u7z1jd09f0bs.resolve('styles-menu'),
        selectedMenu: $_3584u7z1jd09f0bs.resolve('styles-selected-menu'),
        item: $_3584u7z1jd09f0bs.resolve('styles-item'),
        selectedItem: $_3584u7z1jd09f0bs.resolve('styles-selected-item')
      }
    }));
    return memMenu.asSpec();
  };
  var $_etyxhm12fjd09f0tw = { sketch: sketch$9 };

  var getFromExpandingItem = function (item) {
    var newItem = $_3htayhwyjd09f03i.deepMerge($_q0etsx6jd09f053.exclude(item, ['items']), { menu: true });
    var rest = expand(item.items);
    var newMenus = $_3htayhwyjd09f03i.deepMerge(rest.menus, $_q0etsx6jd09f053.wrap(item.title, rest.items));
    var newExpansions = $_3htayhwyjd09f03i.deepMerge(rest.expansions, $_q0etsx6jd09f053.wrap(item.title, item.title));
    return {
      item: newItem,
      menus: newMenus,
      expansions: newExpansions
    };
  };
  var getFromItem = function (item) {
    return $_q0etsx6jd09f053.hasKey(item, 'items') ? getFromExpandingItem(item) : {
      item: item,
      menus: {},
      expansions: {}
    };
  };
  var expand = function (items) {
    return $_bvikd2w9jd09f01c.foldr(items, function (acc, item) {
      var newData = getFromItem(item);
      return {
        menus: $_3htayhwyjd09f03i.deepMerge(acc.menus, newData.menus),
        items: [newData.item].concat(acc.items),
        expansions: $_3htayhwyjd09f03i.deepMerge(acc.expansions, newData.expansions)
      };
    }, {
      menus: {},
      expansions: {},
      items: []
    });
  };
  var $_2t2i0813ijd09f11l = { expand: expand };

  var register$1 = function (editor, settings) {
    var isSelectedFor = function (format) {
      return function () {
        return editor.formatter.match(format);
      };
    };
    var getPreview = function (format) {
      return function () {
        var styles = editor.formatter.getCssText(format);
        return styles;
      };
    };
    var enrichSupported = function (item) {
      return $_3htayhwyjd09f03i.deepMerge(item, {
        isSelected: isSelectedFor(item.format),
        getPreview: getPreview(item.format)
      });
    };
    var enrichMenu = function (item) {
      return $_3htayhwyjd09f03i.deepMerge(item, {
        isSelected: $_8z5eqrwbjd09f01q.constant(false),
        getPreview: $_8z5eqrwbjd09f01q.constant('')
      });
    };
    var enrichCustom = function (item) {
      var formatName = $_3x6e2m10gjd09f0ir.generate(item.title);
      var newItem = $_3htayhwyjd09f03i.deepMerge(item, {
        format: formatName,
        isSelected: isSelectedFor(formatName),
        getPreview: getPreview(formatName)
      });
      editor.formatter.register(formatName, newItem);
      return newItem;
    };
    var formats = $_q0etsx6jd09f053.readOptFrom(settings, 'style_formats').getOr(DefaultStyleFormats);
    var doEnrich = function (items) {
      return $_bvikd2w9jd09f01c.map(items, function (item) {
        if ($_q0etsx6jd09f053.hasKey(item, 'items')) {
          var newItems = doEnrich(item.items);
          return $_3htayhwyjd09f03i.deepMerge(enrichMenu(item), { items: newItems });
        } else if ($_q0etsx6jd09f053.hasKey(item, 'format')) {
          return enrichSupported(item);
        } else {
          return enrichCustom(item);
        }
      });
    };
    return doEnrich(formats);
  };
  var prune = function (editor, formats) {
    var doPrune = function (items) {
      return $_bvikd2w9jd09f01c.bind(items, function (item) {
        if (item.items !== undefined) {
          var newItems = doPrune(item.items);
          return newItems.length > 0 ? [item] : [];
        } else {
          var keep = $_q0etsx6jd09f053.hasKey(item, 'format') ? editor.formatter.canApply(item.format) : true;
          return keep ? [item] : [];
        }
      });
    };
    var prunedItems = doPrune(formats);
    return $_2t2i0813ijd09f11l.expand(prunedItems);
  };
  var ui = function (editor, formats, onDone) {
    var pruned = prune(editor, formats);
    return $_etyxhm12fjd09f0tw.sketch({
      formats: pruned,
      handle: function (item, value) {
        editor.undoManager.transact(function () {
          if (Toggling.isOn(item)) {
            editor.formatter.remove(value);
          } else {
            editor.formatter.apply(value);
          }
        });
        onDone();
      }
    });
  };
  var $_91ss0112djd09f0tl = {
    register: register$1,
    ui: ui
  };

  var defaults = [
    'undo',
    'bold',
    'italic',
    'link',
    'image',
    'bullist',
    'styleselect'
  ];
  var extract$1 = function (rawToolbar) {
    var toolbar = rawToolbar.replace(/\|/g, ' ').trim();
    return toolbar.length > 0 ? toolbar.split(/\s+/) : [];
  };
  var identifyFromArray = function (toolbar) {
    return $_bvikd2w9jd09f01c.bind(toolbar, function (item) {
      return $_4biushwzjd09f03k.isArray(item) ? identifyFromArray(item) : extract$1(item);
    });
  };
  var identify = function (settings) {
    var toolbar = settings.toolbar !== undefined ? settings.toolbar : defaults;
    return $_4biushwzjd09f03k.isArray(toolbar) ? identifyFromArray(toolbar) : extract$1(toolbar);
  };
  var setup = function (realm, editor) {
    var commandSketch = function (name) {
      return function () {
        return $_ch9li7z2jd09f0c0.forToolbarCommand(editor, name);
      };
    };
    var stateCommandSketch = function (name) {
      return function () {
        return $_ch9li7z2jd09f0c0.forToolbarStateCommand(editor, name);
      };
    };
    var actionSketch = function (name, query, action) {
      return function () {
        return $_ch9li7z2jd09f0c0.forToolbarStateAction(editor, name, query, action);
      };
    };
    var undo = commandSketch('undo');
    var redo = commandSketch('redo');
    var bold = stateCommandSketch('bold');
    var italic = stateCommandSketch('italic');
    var underline = stateCommandSketch('underline');
    var removeformat = commandSketch('removeformat');
    var link = function () {
      return $_180n5r11ojd09f0q1.sketch(realm, editor);
    };
    var unlink = actionSketch('unlink', 'link', function () {
      editor.execCommand('unlink', null, false);
    });
    var image = function () {
      return $_eehf7n11djd09f0p0.sketch(editor);
    };
    var bullist = actionSketch('unordered-list', 'ul', function () {
      editor.execCommand('InsertUnorderedList', null, false);
    });
    var numlist = actionSketch('ordered-list', 'ol', function () {
      editor.execCommand('InsertOrderedList', null, false);
    });
    var fontsizeselect = function () {
      return $_4sc391119jd09f0o6.sketch(realm, editor);
    };
    var forecolor = function () {
      return $_1nrbo710sjd09f0ly.sketch(realm, editor);
    };
    var styleFormats = $_91ss0112djd09f0tl.register(editor, editor.settings);
    var styleFormatsMenu = function () {
      return $_91ss0112djd09f0tl.ui(editor, styleFormats, function () {
        editor.fire('scrollIntoView');
      });
    };
    var styleselect = function () {
      return $_ch9li7z2jd09f0c0.forToolbar('style-formats', function (button) {
        editor.fire('toReading');
        realm.dropup().appear(styleFormatsMenu, Toggling.on, button);
      }, $_fq8al5w4jd09f00e.derive([
        Toggling.config({
          toggleClass: $_3584u7z1jd09f0bs.resolve('toolbar-button-selected'),
          toggleOnExecute: false,
          aria: { mode: 'pressed' }
        }),
        Receiving.config({
          channels: $_q0etsx6jd09f053.wrapAll([
            $_2jq3vfz0jd09f0bp.receive($_b1cma0yojd09f09y.orientationChanged(), Toggling.off),
            $_2jq3vfz0jd09f0bp.receive($_b1cma0yojd09f09y.dropupDismissed(), Toggling.off)
          ])
        })
      ]));
    };
    var feature = function (prereq, sketch) {
      return {
        isSupported: function () {
          return prereq.forall(function (p) {
            return $_q0etsx6jd09f053.hasKey(editor.buttons, p);
          });
        },
        sketch: sketch
      };
    };
    return {
      undo: feature($_8nwhzlwajd09f01m.none(), undo),
      redo: feature($_8nwhzlwajd09f01m.none(), redo),
      bold: feature($_8nwhzlwajd09f01m.none(), bold),
      italic: feature($_8nwhzlwajd09f01m.none(), italic),
      underline: feature($_8nwhzlwajd09f01m.none(), underline),
      removeformat: feature($_8nwhzlwajd09f01m.none(), removeformat),
      link: feature($_8nwhzlwajd09f01m.none(), link),
      unlink: feature($_8nwhzlwajd09f01m.none(), unlink),
      image: feature($_8nwhzlwajd09f01m.none(), image),
      bullist: feature($_8nwhzlwajd09f01m.some('bullist'), bullist),
      numlist: feature($_8nwhzlwajd09f01m.some('numlist'), numlist),
      fontsizeselect: feature($_8nwhzlwajd09f01m.none(), fontsizeselect),
      forecolor: feature($_8nwhzlwajd09f01m.none(), forecolor),
      styleselect: feature($_8nwhzlwajd09f01m.none(), styleselect)
    };
  };
  var detect$4 = function (settings, features) {
    var itemNames = identify(settings);
    var present = {};
    return $_bvikd2w9jd09f01c.bind(itemNames, function (iName) {
      var r = !$_q0etsx6jd09f053.hasKey(present, iName) && $_q0etsx6jd09f053.hasKey(features, iName) && features[iName].isSupported() ? [features[iName].sketch()] : [];
      present[iName] = true;
      return r;
    });
  };
  var $_2kilxeypjd09f0a7 = {
    identify: identify,
    setup: setup,
    detect: detect$4
  };

  var mkEvent = function (target, x, y, stop, prevent, kill, raw) {
    return {
      'target': $_8z5eqrwbjd09f01q.constant(target),
      'x': $_8z5eqrwbjd09f01q.constant(x),
      'y': $_8z5eqrwbjd09f01q.constant(y),
      'stop': stop,
      'prevent': prevent,
      'kill': kill,
      'raw': $_8z5eqrwbjd09f01q.constant(raw)
    };
  };
  var handle = function (filter, handler) {
    return function (rawEvent) {
      if (!filter(rawEvent))
        return;
      var target = $_cnbf2uwtjd09f033.fromDom(rawEvent.target);
      var stop = function () {
        rawEvent.stopPropagation();
      };
      var prevent = function () {
        rawEvent.preventDefault();
      };
      var kill = $_8z5eqrwbjd09f01q.compose(prevent, stop);
      var evt = mkEvent(target, rawEvent.clientX, rawEvent.clientY, stop, prevent, kill, rawEvent);
      handler(evt);
    };
  };
  var binder = function (element, event, filter, handler, useCapture) {
    var wrapped = handle(filter, handler);
    element.dom().addEventListener(event, wrapped, useCapture);
    return { unbind: $_8z5eqrwbjd09f01q.curry(unbind, element, event, wrapped, useCapture) };
  };
  var bind$1 = function (element, event, filter, handler) {
    return binder(element, event, filter, handler, false);
  };
  var capture = function (element, event, filter, handler) {
    return binder(element, event, filter, handler, true);
  };
  var unbind = function (element, event, handler, useCapture) {
    element.dom().removeEventListener(event, handler, useCapture);
  };
  var $_4d7k8513ljd09f11x = {
    bind: bind$1,
    capture: capture
  };

  var filter$1 = $_8z5eqrwbjd09f01q.constant(true);
  var bind$2 = function (element, event, handler) {
    return $_4d7k8513ljd09f11x.bind(element, event, filter$1, handler);
  };
  var capture$1 = function (element, event, handler) {
    return $_4d7k8513ljd09f11x.capture(element, event, filter$1, handler);
  };
  var $_5k3ae313kjd09f11v = {
    bind: bind$2,
    capture: capture$1
  };

  var INTERVAL = 50;
  var INSURANCE = 1000 / INTERVAL;
  var get$11 = function (outerWindow) {
    var isPortrait = outerWindow.matchMedia('(orientation: portrait)').matches;
    return { isPortrait: $_8z5eqrwbjd09f01q.constant(isPortrait) };
  };
  var getActualWidth = function (outerWindow) {
    var isIos = $_8zynflwgjd09f01z.detect().os.isiOS();
    var isPortrait = get$11(outerWindow).isPortrait();
    return isIos && !isPortrait ? outerWindow.screen.height : outerWindow.screen.width;
  };
  var onChange = function (outerWindow, listeners) {
    var win = $_cnbf2uwtjd09f033.fromDom(outerWindow);
    var poller = null;
    var change = function () {
      clearInterval(poller);
      var orientation = get$11(outerWindow);
      listeners.onChange(orientation);
      onAdjustment(function () {
        listeners.onReady(orientation);
      });
    };
    var orientationHandle = $_5k3ae313kjd09f11v.bind(win, 'orientationchange', change);
    var onAdjustment = function (f) {
      clearInterval(poller);
      var flag = outerWindow.innerHeight;
      var insurance = 0;
      poller = setInterval(function () {
        if (flag !== outerWindow.innerHeight) {
          clearInterval(poller);
          f($_8nwhzlwajd09f01m.some(outerWindow.innerHeight));
        } else if (insurance > INSURANCE) {
          clearInterval(poller);
          f($_8nwhzlwajd09f01m.none());
        }
        insurance++;
      }, INTERVAL);
    };
    var destroy = function () {
      orientationHandle.unbind();
    };
    return {
      onAdjustment: onAdjustment,
      destroy: destroy
    };
  };
  var $_4vd4wl13jjd09f11p = {
    get: get$11,
    onChange: onChange,
    getActualWidth: getActualWidth
  };

  function DelayedFunction (fun, delay) {
    var ref = null;
    var schedule = function () {
      var args = arguments;
      ref = setTimeout(function () {
        fun.apply(null, args);
        ref = null;
      }, delay);
    };
    var cancel = function () {
      if (ref !== null) {
        clearTimeout(ref);
        ref = null;
      }
    };
    return {
      cancel: cancel,
      schedule: schedule
    };
  }

  var SIGNIFICANT_MOVE = 5;
  var LONGPRESS_DELAY = 400;
  var getTouch = function (event) {
    if (event.raw().touches === undefined || event.raw().touches.length !== 1)
      return $_8nwhzlwajd09f01m.none();
    return $_8nwhzlwajd09f01m.some(event.raw().touches[0]);
  };
  var isFarEnough = function (touch, data) {
    var distX = Math.abs(touch.clientX - data.x());
    var distY = Math.abs(touch.clientY - data.y());
    return distX > SIGNIFICANT_MOVE || distY > SIGNIFICANT_MOVE;
  };
  var monitor = function (settings) {
    var startData = Cell($_8nwhzlwajd09f01m.none());
    var longpress = DelayedFunction(function (event) {
      startData.set($_8nwhzlwajd09f01m.none());
      settings.triggerEvent($_5iytewwjd09f03c.longpress(), event);
    }, LONGPRESS_DELAY);
    var handleTouchstart = function (event) {
      getTouch(event).each(function (touch) {
        longpress.cancel();
        var data = {
          x: $_8z5eqrwbjd09f01q.constant(touch.clientX),
          y: $_8z5eqrwbjd09f01q.constant(touch.clientY),
          target: event.target
        };
        longpress.schedule(data);
        startData.set($_8nwhzlwajd09f01m.some(data));
      });
      return $_8nwhzlwajd09f01m.none();
    };
    var handleTouchmove = function (event) {
      longpress.cancel();
      getTouch(event).each(function (touch) {
        startData.get().each(function (data) {
          if (isFarEnough(touch, data))
            startData.set($_8nwhzlwajd09f01m.none());
        });
      });
      return $_8nwhzlwajd09f01m.none();
    };
    var handleTouchend = function (event) {
      longpress.cancel();
      var isSame = function (data) {
        return $_6s6cs1w8jd09f014.eq(data.target(), event.target());
      };
      return startData.get().filter(isSame).map(function (data) {
        return settings.triggerEvent($_5iytewwjd09f03c.tap(), event);
      });
    };
    var handlers = $_q0etsx6jd09f053.wrapAll([
      {
        key: $_8nqyjjwxjd09f03f.touchstart(),
        value: handleTouchstart
      },
      {
        key: $_8nqyjjwxjd09f03f.touchmove(),
        value: handleTouchmove
      },
      {
        key: $_8nqyjjwxjd09f03f.touchend(),
        value: handleTouchend
      }
    ]);
    var fireIfReady = function (event, type) {
      return $_q0etsx6jd09f053.readOptFrom(handlers, type).bind(function (handler) {
        return handler(event);
      });
    };
    return { fireIfReady: fireIfReady };
  };
  var $_colzkd13rjd09f12w = { monitor: monitor };

  var monitor$1 = function (editorApi) {
    var tapEvent = $_colzkd13rjd09f12w.monitor({
      triggerEvent: function (type, evt) {
        editorApi.onTapContent(evt);
      }
    });
    var onTouchend = function () {
      return $_5k3ae313kjd09f11v.bind(editorApi.body(), 'touchend', function (evt) {
        tapEvent.fireIfReady(evt, 'touchend');
      });
    };
    var onTouchmove = function () {
      return $_5k3ae313kjd09f11v.bind(editorApi.body(), 'touchmove', function (evt) {
        tapEvent.fireIfReady(evt, 'touchmove');
      });
    };
    var fireTouchstart = function (evt) {
      tapEvent.fireIfReady(evt, 'touchstart');
    };
    return {
      fireTouchstart: fireTouchstart,
      onTouchend: onTouchend,
      onTouchmove: onTouchmove
    };
  };
  var $_1deljb13qjd09f12s = { monitor: monitor$1 };

  var isAndroid6 = $_8zynflwgjd09f01z.detect().os.version.major >= 6;
  var initEvents = function (editorApi, toolstrip, alloy) {
    var tapping = $_1deljb13qjd09f12s.monitor(editorApi);
    var outerDoc = $_sdd74y3jd09f08a.owner(toolstrip);
    var isRanged = function (sel) {
      return !$_6s6cs1w8jd09f014.eq(sel.start(), sel.finish()) || sel.soffset() !== sel.foffset();
    };
    var hasRangeInUi = function () {
      return $_ccs2jvygjd09f09f.active(outerDoc).filter(function (input) {
        return $_arrpm2xxjd09f07k.name(input) === 'input';
      }).exists(function (input) {
        return input.dom().selectionStart !== input.dom().selectionEnd;
      });
    };
    var updateMargin = function () {
      var rangeInContent = editorApi.doc().dom().hasFocus() && editorApi.getSelection().exists(isRanged);
      alloy.getByDom(toolstrip).each((rangeInContent || hasRangeInUi()) === true ? Toggling.on : Toggling.off);
    };
    var listeners = [
      $_5k3ae313kjd09f11v.bind(editorApi.body(), 'touchstart', function (evt) {
        editorApi.onTouchContent();
        tapping.fireTouchstart(evt);
      }),
      tapping.onTouchmove(),
      tapping.onTouchend(),
      $_5k3ae313kjd09f11v.bind(toolstrip, 'touchstart', function (evt) {
        editorApi.onTouchToolstrip();
      }),
      editorApi.onToReading(function () {
        $_ccs2jvygjd09f09f.blur(editorApi.body());
      }),
      editorApi.onToEditing($_8z5eqrwbjd09f01q.noop),
      editorApi.onScrollToCursor(function (tinyEvent) {
        tinyEvent.preventDefault();
        editorApi.getCursorBox().each(function (bounds) {
          var cWin = editorApi.win();
          var isOutside = bounds.top() > cWin.innerHeight || bounds.bottom() > cWin.innerHeight;
          var cScrollBy = isOutside ? bounds.bottom() - cWin.innerHeight + 50 : 0;
          if (cScrollBy !== 0) {
            cWin.scrollTo(cWin.pageXOffset, cWin.pageYOffset + cScrollBy);
          }
        });
      })
    ].concat(isAndroid6 === true ? [] : [
      $_5k3ae313kjd09f11v.bind($_cnbf2uwtjd09f033.fromDom(editorApi.win()), 'blur', function () {
        alloy.getByDom(toolstrip).each(Toggling.off);
      }),
      $_5k3ae313kjd09f11v.bind(outerDoc, 'select', updateMargin),
      $_5k3ae313kjd09f11v.bind(editorApi.doc(), 'selectionchange', updateMargin)
    ]);
    var destroy = function () {
      $_bvikd2w9jd09f01c.each(listeners, function (l) {
        l.unbind();
      });
    };
    return { destroy: destroy };
  };
  var $_65bvv513pjd09f12f = { initEvents: initEvents };

  var autocompleteHack = function () {
    return function (f) {
      setTimeout(function () {
        f();
      }, 0);
    };
  };
  var resume = function (cWin) {
    cWin.focus();
    var iBody = $_cnbf2uwtjd09f033.fromDom(cWin.document.body);
    var inInput = $_ccs2jvygjd09f09f.active().exists(function (elem) {
      return $_bvikd2w9jd09f01c.contains([
        'input',
        'textarea'
      ], $_arrpm2xxjd09f07k.name(elem));
    });
    var transaction = inInput ? autocompleteHack() : $_8z5eqrwbjd09f01q.apply;
    transaction(function () {
      $_ccs2jvygjd09f09f.active().each($_ccs2jvygjd09f09f.blur);
      $_ccs2jvygjd09f09f.focus(iBody);
    });
  };
  var $_5ymhj513ujd09f13i = { resume: resume };

  var safeParse = function (element, attribute) {
    var parsed = parseInt($_6spjcmxwjd09f07e.get(element, attribute), 10);
    return isNaN(parsed) ? 0 : parsed;
  };
  var $_43tcqw13vjd09f13o = { safeParse: safeParse };

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
        return $_8nwhzlwajd09f01m.none();
      }
    };
    var getOptionSafe = function (element) {
      return is(element) ? $_8nwhzlwajd09f01m.from(element.dom().nodeValue) : $_8nwhzlwajd09f01m.none();
    };
    var browser = $_8zynflwgjd09f01z.detect().browser;
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

  var api$3 = NodeValue($_arrpm2xxjd09f07k.isText, 'text');
  var get$12 = function (element) {
    return api$3.get(element);
  };
  var getOption = function (element) {
    return api$3.getOption(element);
  };
  var set$8 = function (element, value) {
    api$3.set(element, value);
  };
  var $_1c5s2l13yjd09f140 = {
    get: get$12,
    getOption: getOption,
    set: set$8
  };

  var getEnd = function (element) {
    return $_arrpm2xxjd09f07k.name(element) === 'img' ? 1 : $_1c5s2l13yjd09f140.getOption(element).fold(function () {
      return $_sdd74y3jd09f08a.children(element).length;
    }, function (v) {
      return v.length;
    });
  };
  var isEnd = function (element, offset) {
    return getEnd(element) === offset;
  };
  var isStart = function (element, offset) {
    return offset === 0;
  };
  var NBSP = '\xA0';
  var isTextNodeWithCursorPosition = function (el) {
    return $_1c5s2l13yjd09f140.getOption(el).filter(function (text) {
      return text.trim().length !== 0 || text.indexOf(NBSP) > -1;
    }).isSome();
  };
  var elementsWithCursorPosition = [
    'img',
    'br'
  ];
  var isCursorPosition = function (elem) {
    var hasCursorPosition = isTextNodeWithCursorPosition(elem);
    return hasCursorPosition || $_bvikd2w9jd09f01c.contains(elementsWithCursorPosition, $_arrpm2xxjd09f07k.name(elem));
  };
  var $_bfp3jf13xjd09f13x = {
    getEnd: getEnd,
    isEnd: isEnd,
    isStart: isStart,
    isCursorPosition: isCursorPosition
  };

  var adt$4 = $_enqz8zx4jd09f04j.generate([
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
    return situ.fold($_8z5eqrwbjd09f01q.identity, $_8z5eqrwbjd09f01q.identity, $_8z5eqrwbjd09f01q.identity);
  };
  var $_g6h6it141jd09f149 = {
    before: adt$4.before,
    on: adt$4.on,
    after: adt$4.after,
    cata: cata,
    getStart: getStart
  };

  var type$1 = $_enqz8zx4jd09f04j.generate([
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
  var range$1 = $_gbd4xkxmjd09f06u.immutable('start', 'soffset', 'finish', 'foffset');
  var exactFromRange = function (simRange) {
    return type$1.exact(simRange.start(), simRange.soffset(), simRange.finish(), simRange.foffset());
  };
  var getStart$1 = function (selection) {
    return selection.match({
      domRange: function (rng) {
        return $_cnbf2uwtjd09f033.fromDom(rng.startContainer);
      },
      relative: function (startSitu, finishSitu) {
        return $_g6h6it141jd09f149.getStart(startSitu);
      },
      exact: function (start, soffset, finish, foffset) {
        return start;
      }
    });
  };
  var getWin = function (selection) {
    var start = getStart$1(selection);
    return $_sdd74y3jd09f08a.defaultView(start);
  };
  var $_2di69m140jd09f145 = {
    domRange: type$1.domRange,
    relative: type$1.relative,
    exact: type$1.exact,
    exactFromRange: exactFromRange,
    range: range$1,
    getWin: getWin
  };

  var makeRange = function (start, soffset, finish, foffset) {
    var doc = $_sdd74y3jd09f08a.owner(start);
    var rng = doc.dom().createRange();
    rng.setStart(start.dom(), soffset);
    rng.setEnd(finish.dom(), foffset);
    return rng;
  };
  var commonAncestorContainer = function (start, soffset, finish, foffset) {
    var r = makeRange(start, soffset, finish, foffset);
    return $_cnbf2uwtjd09f033.fromDom(r.commonAncestorContainer);
  };
  var after$2 = function (start, soffset, finish, foffset) {
    var r = makeRange(start, soffset, finish, foffset);
    var same = $_6s6cs1w8jd09f014.eq(start, finish) && soffset === foffset;
    return r.collapsed && !same;
  };
  var $_bki277143jd09f14h = {
    after: after$2,
    commonAncestorContainer: commonAncestorContainer
  };

  var fromElements = function (elements, scope) {
    var doc = scope || document;
    var fragment = doc.createDocumentFragment();
    $_bvikd2w9jd09f01c.each(elements, function (element) {
      fragment.appendChild(element.dom());
    });
    return $_cnbf2uwtjd09f033.fromDom(fragment);
  };
  var $_g1wsgp144jd09f14j = { fromElements: fromElements };

  var selectNodeContents = function (win, element) {
    var rng = win.document.createRange();
    selectNodeContentsUsing(rng, element);
    return rng;
  };
  var selectNodeContentsUsing = function (rng, element) {
    rng.selectNodeContents(element.dom());
  };
  var isWithin = function (outerRange, innerRange) {
    return innerRange.compareBoundaryPoints(outerRange.END_TO_START, outerRange) < 1 && innerRange.compareBoundaryPoints(outerRange.START_TO_END, outerRange) > -1;
  };
  var create$4 = function (win) {
    return win.document.createRange();
  };
  var setStart = function (rng, situ) {
    situ.fold(function (e) {
      rng.setStartBefore(e.dom());
    }, function (e, o) {
      rng.setStart(e.dom(), o);
    }, function (e) {
      rng.setStartAfter(e.dom());
    });
  };
  var setFinish = function (rng, situ) {
    situ.fold(function (e) {
      rng.setEndBefore(e.dom());
    }, function (e, o) {
      rng.setEnd(e.dom(), o);
    }, function (e) {
      rng.setEndAfter(e.dom());
    });
  };
  var replaceWith = function (rng, fragment) {
    deleteContents(rng);
    rng.insertNode(fragment.dom());
  };
  var relativeToNative = function (win, startSitu, finishSitu) {
    var range = win.document.createRange();
    setStart(range, startSitu);
    setFinish(range, finishSitu);
    return range;
  };
  var exactToNative = function (win, start, soffset, finish, foffset) {
    var rng = win.document.createRange();
    rng.setStart(start.dom(), soffset);
    rng.setEnd(finish.dom(), foffset);
    return rng;
  };
  var deleteContents = function (rng) {
    rng.deleteContents();
  };
  var cloneFragment = function (rng) {
    var fragment = rng.cloneContents();
    return $_cnbf2uwtjd09f033.fromDom(fragment);
  };
  var toRect = function (rect) {
    return {
      left: $_8z5eqrwbjd09f01q.constant(rect.left),
      top: $_8z5eqrwbjd09f01q.constant(rect.top),
      right: $_8z5eqrwbjd09f01q.constant(rect.right),
      bottom: $_8z5eqrwbjd09f01q.constant(rect.bottom),
      width: $_8z5eqrwbjd09f01q.constant(rect.width),
      height: $_8z5eqrwbjd09f01q.constant(rect.height)
    };
  };
  var getFirstRect = function (rng) {
    var rects = rng.getClientRects();
    var rect = rects.length > 0 ? rects[0] : rng.getBoundingClientRect();
    return rect.width > 0 || rect.height > 0 ? $_8nwhzlwajd09f01m.some(rect).map(toRect) : $_8nwhzlwajd09f01m.none();
  };
  var getBounds = function (rng) {
    var rect = rng.getBoundingClientRect();
    return rect.width > 0 || rect.height > 0 ? $_8nwhzlwajd09f01m.some(rect).map(toRect) : $_8nwhzlwajd09f01m.none();
  };
  var toString$1 = function (rng) {
    return rng.toString();
  };
  var $_3phg65145jd09f14l = {
    create: create$4,
    replaceWith: replaceWith,
    selectNodeContents: selectNodeContents,
    selectNodeContentsUsing: selectNodeContentsUsing,
    relativeToNative: relativeToNative,
    exactToNative: exactToNative,
    deleteContents: deleteContents,
    cloneFragment: cloneFragment,
    getFirstRect: getFirstRect,
    getBounds: getBounds,
    isWithin: isWithin,
    toString: toString$1
  };

  var adt$5 = $_enqz8zx4jd09f04j.generate([
    {
      ltr: [
        'start',
        'soffset',
        'finish',
        'foffset'
      ]
    },
    {
      rtl: [
        'start',
        'soffset',
        'finish',
        'foffset'
      ]
    }
  ]);
  var fromRange = function (win, type, range) {
    return type($_cnbf2uwtjd09f033.fromDom(range.startContainer), range.startOffset, $_cnbf2uwtjd09f033.fromDom(range.endContainer), range.endOffset);
  };
  var getRanges = function (win, selection) {
    return selection.match({
      domRange: function (rng) {
        return {
          ltr: $_8z5eqrwbjd09f01q.constant(rng),
          rtl: $_8nwhzlwajd09f01m.none
        };
      },
      relative: function (startSitu, finishSitu) {
        return {
          ltr: $_9ur00cwhjd09f021.cached(function () {
            return $_3phg65145jd09f14l.relativeToNative(win, startSitu, finishSitu);
          }),
          rtl: $_9ur00cwhjd09f021.cached(function () {
            return $_8nwhzlwajd09f01m.some($_3phg65145jd09f14l.relativeToNative(win, finishSitu, startSitu));
          })
        };
      },
      exact: function (start, soffset, finish, foffset) {
        return {
          ltr: $_9ur00cwhjd09f021.cached(function () {
            return $_3phg65145jd09f14l.exactToNative(win, start, soffset, finish, foffset);
          }),
          rtl: $_9ur00cwhjd09f021.cached(function () {
            return $_8nwhzlwajd09f01m.some($_3phg65145jd09f14l.exactToNative(win, finish, foffset, start, soffset));
          })
        };
      }
    });
  };
  var doDiagnose = function (win, ranges) {
    var rng = ranges.ltr();
    if (rng.collapsed) {
      var reversed = ranges.rtl().filter(function (rev) {
        return rev.collapsed === false;
      });
      return reversed.map(function (rev) {
        return adt$5.rtl($_cnbf2uwtjd09f033.fromDom(rev.endContainer), rev.endOffset, $_cnbf2uwtjd09f033.fromDom(rev.startContainer), rev.startOffset);
      }).getOrThunk(function () {
        return fromRange(win, adt$5.ltr, rng);
      });
    } else {
      return fromRange(win, adt$5.ltr, rng);
    }
  };
  var diagnose = function (win, selection) {
    var ranges = getRanges(win, selection);
    return doDiagnose(win, ranges);
  };
  var asLtrRange = function (win, selection) {
    var diagnosis = diagnose(win, selection);
    return diagnosis.match({
      ltr: function (start, soffset, finish, foffset) {
        var rng = win.document.createRange();
        rng.setStart(start.dom(), soffset);
        rng.setEnd(finish.dom(), foffset);
        return rng;
      },
      rtl: function (start, soffset, finish, foffset) {
        var rng = win.document.createRange();
        rng.setStart(finish.dom(), foffset);
        rng.setEnd(start.dom(), soffset);
        return rng;
      }
    });
  };
  var $_djds0g146jd09f14s = {
    ltr: adt$5.ltr,
    rtl: adt$5.rtl,
    diagnose: diagnose,
    asLtrRange: asLtrRange
  };

  var searchForPoint = function (rectForOffset, x, y, maxX, length) {
    if (length === 0)
      return 0;
    else if (x === maxX)
      return length - 1;
    var xDelta = maxX;
    for (var i = 1; i < length; i++) {
      var rect = rectForOffset(i);
      var curDeltaX = Math.abs(x - rect.left);
      if (y > rect.bottom) {
      } else if (y < rect.top || curDeltaX > xDelta) {
        return i - 1;
      } else {
        xDelta = curDeltaX;
      }
    }
    return 0;
  };
  var inRect = function (rect, x, y) {
    return x >= rect.left && x <= rect.right && y >= rect.top && y <= rect.bottom;
  };
  var $_ansq79149jd09f15c = {
    inRect: inRect,
    searchForPoint: searchForPoint
  };

  var locateOffset = function (doc, textnode, x, y, rect) {
    var rangeForOffset = function (offset) {
      var r = doc.dom().createRange();
      r.setStart(textnode.dom(), offset);
      r.collapse(true);
      return r;
    };
    var rectForOffset = function (offset) {
      var r = rangeForOffset(offset);
      return r.getBoundingClientRect();
    };
    var length = $_1c5s2l13yjd09f140.get(textnode).length;
    var offset = $_ansq79149jd09f15c.searchForPoint(rectForOffset, x, y, rect.right, length);
    return rangeForOffset(offset);
  };
  var locate$1 = function (doc, node, x, y) {
    var r = doc.dom().createRange();
    r.selectNode(node.dom());
    var rects = r.getClientRects();
    var foundRect = $_851ithyejd09f09c.findMap(rects, function (rect) {
      return $_ansq79149jd09f15c.inRect(rect, x, y) ? $_8nwhzlwajd09f01m.some(rect) : $_8nwhzlwajd09f01m.none();
    });
    return foundRect.map(function (rect) {
      return locateOffset(doc, node, x, y, rect);
    });
  };
  var $_3bn5lk14ajd09f15e = { locate: locate$1 };

  var searchInChildren = function (doc, node, x, y) {
    var r = doc.dom().createRange();
    var nodes = $_sdd74y3jd09f08a.children(node);
    return $_851ithyejd09f09c.findMap(nodes, function (n) {
      r.selectNode(n.dom());
      return $_ansq79149jd09f15c.inRect(r.getBoundingClientRect(), x, y) ? locateNode(doc, n, x, y) : $_8nwhzlwajd09f01m.none();
    });
  };
  var locateNode = function (doc, node, x, y) {
    var locator = $_arrpm2xxjd09f07k.isText(node) ? $_3bn5lk14ajd09f15e.locate : searchInChildren;
    return locator(doc, node, x, y);
  };
  var locate$2 = function (doc, node, x, y) {
    var r = doc.dom().createRange();
    r.selectNode(node.dom());
    var rect = r.getBoundingClientRect();
    var boundedX = Math.max(rect.left, Math.min(rect.right, x));
    var boundedY = Math.max(rect.top, Math.min(rect.bottom, y));
    return locateNode(doc, node, boundedX, boundedY);
  };
  var $_5z44sd148jd09f158 = { locate: locate$2 };

  var first$3 = function (element) {
    return $_605gqayijd09f09k.descendant(element, $_bfp3jf13xjd09f13x.isCursorPosition);
  };
  var last$2 = function (element) {
    return descendantRtl(element, $_bfp3jf13xjd09f13x.isCursorPosition);
  };
  var descendantRtl = function (scope, predicate) {
    var descend = function (element) {
      var children = $_sdd74y3jd09f08a.children(element);
      for (var i = children.length - 1; i >= 0; i--) {
        var child = children[i];
        if (predicate(child))
          return $_8nwhzlwajd09f01m.some(child);
        var res = descend(child);
        if (res.isSome())
          return res;
      }
      return $_8nwhzlwajd09f01m.none();
    };
    return descend(scope);
  };
  var $_4fkukb14cjd09f15l = {
    first: first$3,
    last: last$2
  };

  var COLLAPSE_TO_LEFT = true;
  var COLLAPSE_TO_RIGHT = false;
  var getCollapseDirection = function (rect, x) {
    return x - rect.left < rect.right - x ? COLLAPSE_TO_LEFT : COLLAPSE_TO_RIGHT;
  };
  var createCollapsedNode = function (doc, target, collapseDirection) {
    var r = doc.dom().createRange();
    r.selectNode(target.dom());
    r.collapse(collapseDirection);
    return r;
  };
  var locateInElement = function (doc, node, x) {
    var cursorRange = doc.dom().createRange();
    cursorRange.selectNode(node.dom());
    var rect = cursorRange.getBoundingClientRect();
    var collapseDirection = getCollapseDirection(rect, x);
    var f = collapseDirection === COLLAPSE_TO_LEFT ? $_4fkukb14cjd09f15l.first : $_4fkukb14cjd09f15l.last;
    return f(node).map(function (target) {
      return createCollapsedNode(doc, target, collapseDirection);
    });
  };
  var locateInEmpty = function (doc, node, x) {
    var rect = node.dom().getBoundingClientRect();
    var collapseDirection = getCollapseDirection(rect, x);
    return $_8nwhzlwajd09f01m.some(createCollapsedNode(doc, node, collapseDirection));
  };
  var search$1 = function (doc, node, x) {
    var f = $_sdd74y3jd09f08a.children(node).length === 0 ? locateInEmpty : locateInElement;
    return f(doc, node, x);
  };
  var $_cmxe6j14bjd09f15i = { search: search$1 };

  var caretPositionFromPoint = function (doc, x, y) {
    return $_8nwhzlwajd09f01m.from(doc.dom().caretPositionFromPoint(x, y)).bind(function (pos) {
      if (pos.offsetNode === null)
        return $_8nwhzlwajd09f01m.none();
      var r = doc.dom().createRange();
      r.setStart(pos.offsetNode, pos.offset);
      r.collapse();
      return $_8nwhzlwajd09f01m.some(r);
    });
  };
  var caretRangeFromPoint = function (doc, x, y) {
    return $_8nwhzlwajd09f01m.from(doc.dom().caretRangeFromPoint(x, y));
  };
  var searchTextNodes = function (doc, node, x, y) {
    var r = doc.dom().createRange();
    r.selectNode(node.dom());
    var rect = r.getBoundingClientRect();
    var boundedX = Math.max(rect.left, Math.min(rect.right, x));
    var boundedY = Math.max(rect.top, Math.min(rect.bottom, y));
    return $_5z44sd148jd09f158.locate(doc, node, boundedX, boundedY);
  };
  var searchFromPoint = function (doc, x, y) {
    return $_cnbf2uwtjd09f033.fromPoint(doc, x, y).bind(function (elem) {
      var fallback = function () {
        return $_cmxe6j14bjd09f15i.search(doc, elem, x);
      };
      return $_sdd74y3jd09f08a.children(elem).length === 0 ? fallback() : searchTextNodes(doc, elem, x, y).orThunk(fallback);
    });
  };
  var availableSearch = document.caretPositionFromPoint ? caretPositionFromPoint : document.caretRangeFromPoint ? caretRangeFromPoint : searchFromPoint;
  var fromPoint$1 = function (win, x, y) {
    var doc = $_cnbf2uwtjd09f033.fromDom(win.document);
    return availableSearch(doc, x, y).map(function (rng) {
      return $_2di69m140jd09f145.range($_cnbf2uwtjd09f033.fromDom(rng.startContainer), rng.startOffset, $_cnbf2uwtjd09f033.fromDom(rng.endContainer), rng.endOffset);
    });
  };
  var $_6twnx7147jd09f14z = { fromPoint: fromPoint$1 };

  var withinContainer = function (win, ancestor, outerRange, selector) {
    var innerRange = $_3phg65145jd09f14l.create(win);
    var self = $_5mto6swsjd09f02z.is(ancestor, selector) ? [ancestor] : [];
    var elements = self.concat($_23mimrzkjd09f0dz.descendants(ancestor, selector));
    return $_bvikd2w9jd09f01c.filter(elements, function (elem) {
      $_3phg65145jd09f14l.selectNodeContentsUsing(innerRange, elem);
      return $_3phg65145jd09f14l.isWithin(outerRange, innerRange);
    });
  };
  var find$4 = function (win, selection, selector) {
    var outerRange = $_djds0g146jd09f14s.asLtrRange(win, selection);
    var ancestor = $_cnbf2uwtjd09f033.fromDom(outerRange.commonAncestorContainer);
    return $_arrpm2xxjd09f07k.isElement(ancestor) ? withinContainer(win, ancestor, outerRange, selector) : [];
  };
  var $_edu6ly14djd09f15n = { find: find$4 };

  var beforeSpecial = function (element, offset) {
    var name = $_arrpm2xxjd09f07k.name(element);
    if ('input' === name)
      return $_g6h6it141jd09f149.after(element);
    else if (!$_bvikd2w9jd09f01c.contains([
        'br',
        'img'
      ], name))
      return $_g6h6it141jd09f149.on(element, offset);
    else
      return offset === 0 ? $_g6h6it141jd09f149.before(element) : $_g6h6it141jd09f149.after(element);
  };
  var preprocessRelative = function (startSitu, finishSitu) {
    var start = startSitu.fold($_g6h6it141jd09f149.before, beforeSpecial, $_g6h6it141jd09f149.after);
    var finish = finishSitu.fold($_g6h6it141jd09f149.before, beforeSpecial, $_g6h6it141jd09f149.after);
    return $_2di69m140jd09f145.relative(start, finish);
  };
  var preprocessExact = function (start, soffset, finish, foffset) {
    var startSitu = beforeSpecial(start, soffset);
    var finishSitu = beforeSpecial(finish, foffset);
    return $_2di69m140jd09f145.relative(startSitu, finishSitu);
  };
  var preprocess = function (selection) {
    return selection.match({
      domRange: function (rng) {
        var start = $_cnbf2uwtjd09f033.fromDom(rng.startContainer);
        var finish = $_cnbf2uwtjd09f033.fromDom(rng.endContainer);
        return preprocessExact(start, rng.startOffset, finish, rng.endOffset);
      },
      relative: preprocessRelative,
      exact: preprocessExact
    });
  };
  var $_27t2i814ejd09f15q = {
    beforeSpecial: beforeSpecial,
    preprocess: preprocess,
    preprocessRelative: preprocessRelative,
    preprocessExact: preprocessExact
  };

  var doSetNativeRange = function (win, rng) {
    $_8nwhzlwajd09f01m.from(win.getSelection()).each(function (selection) {
      selection.removeAllRanges();
      selection.addRange(rng);
    });
  };
  var doSetRange = function (win, start, soffset, finish, foffset) {
    var rng = $_3phg65145jd09f14l.exactToNative(win, start, soffset, finish, foffset);
    doSetNativeRange(win, rng);
  };
  var findWithin = function (win, selection, selector) {
    return $_edu6ly14djd09f15n.find(win, selection, selector);
  };
  var setRangeFromRelative = function (win, relative) {
    return $_djds0g146jd09f14s.diagnose(win, relative).match({
      ltr: function (start, soffset, finish, foffset) {
        doSetRange(win, start, soffset, finish, foffset);
      },
      rtl: function (start, soffset, finish, foffset) {
        var selection = win.getSelection();
        if (selection.extend) {
          selection.collapse(start.dom(), soffset);
          selection.extend(finish.dom(), foffset);
        } else {
          doSetRange(win, finish, foffset, start, soffset);
        }
      }
    });
  };
  var setExact = function (win, start, soffset, finish, foffset) {
    var relative = $_27t2i814ejd09f15q.preprocessExact(start, soffset, finish, foffset);
    setRangeFromRelative(win, relative);
  };
  var setRelative = function (win, startSitu, finishSitu) {
    var relative = $_27t2i814ejd09f15q.preprocessRelative(startSitu, finishSitu);
    setRangeFromRelative(win, relative);
  };
  var toNative = function (selection) {
    var win = $_2di69m140jd09f145.getWin(selection).dom();
    var getDomRange = function (start, soffset, finish, foffset) {
      return $_3phg65145jd09f14l.exactToNative(win, start, soffset, finish, foffset);
    };
    var filtered = $_27t2i814ejd09f15q.preprocess(selection);
    return $_djds0g146jd09f14s.diagnose(win, filtered).match({
      ltr: getDomRange,
      rtl: getDomRange
    });
  };
  var readRange = function (selection) {
    if (selection.rangeCount > 0) {
      var firstRng = selection.getRangeAt(0);
      var lastRng = selection.getRangeAt(selection.rangeCount - 1);
      return $_8nwhzlwajd09f01m.some($_2di69m140jd09f145.range($_cnbf2uwtjd09f033.fromDom(firstRng.startContainer), firstRng.startOffset, $_cnbf2uwtjd09f033.fromDom(lastRng.endContainer), lastRng.endOffset));
    } else {
      return $_8nwhzlwajd09f01m.none();
    }
  };
  var doGetExact = function (selection) {
    var anchorNode = $_cnbf2uwtjd09f033.fromDom(selection.anchorNode);
    var focusNode = $_cnbf2uwtjd09f033.fromDom(selection.focusNode);
    return $_bki277143jd09f14h.after(anchorNode, selection.anchorOffset, focusNode, selection.focusOffset) ? $_8nwhzlwajd09f01m.some($_2di69m140jd09f145.range($_cnbf2uwtjd09f033.fromDom(selection.anchorNode), selection.anchorOffset, $_cnbf2uwtjd09f033.fromDom(selection.focusNode), selection.focusOffset)) : readRange(selection);
  };
  var setToElement = function (win, element) {
    var rng = $_3phg65145jd09f14l.selectNodeContents(win, element);
    doSetNativeRange(win, rng);
  };
  var forElement = function (win, element) {
    var rng = $_3phg65145jd09f14l.selectNodeContents(win, element);
    return $_2di69m140jd09f145.range($_cnbf2uwtjd09f033.fromDom(rng.startContainer), rng.startOffset, $_cnbf2uwtjd09f033.fromDom(rng.endContainer), rng.endOffset);
  };
  var getExact = function (win) {
    var selection = win.getSelection();
    return selection.rangeCount > 0 ? doGetExact(selection) : $_8nwhzlwajd09f01m.none();
  };
  var get$13 = function (win) {
    return getExact(win).map(function (range) {
      return $_2di69m140jd09f145.exact(range.start(), range.soffset(), range.finish(), range.foffset());
    });
  };
  var getFirstRect$1 = function (win, selection) {
    var rng = $_djds0g146jd09f14s.asLtrRange(win, selection);
    return $_3phg65145jd09f14l.getFirstRect(rng);
  };
  var getBounds$1 = function (win, selection) {
    var rng = $_djds0g146jd09f14s.asLtrRange(win, selection);
    return $_3phg65145jd09f14l.getBounds(rng);
  };
  var getAtPoint = function (win, x, y) {
    return $_6twnx7147jd09f14z.fromPoint(win, x, y);
  };
  var getAsString = function (win, selection) {
    var rng = $_djds0g146jd09f14s.asLtrRange(win, selection);
    return $_3phg65145jd09f14l.toString(rng);
  };
  var clear$1 = function (win) {
    var selection = win.getSelection();
    selection.removeAllRanges();
  };
  var clone$3 = function (win, selection) {
    var rng = $_djds0g146jd09f14s.asLtrRange(win, selection);
    return $_3phg65145jd09f14l.cloneFragment(rng);
  };
  var replace = function (win, selection, elements) {
    var rng = $_djds0g146jd09f14s.asLtrRange(win, selection);
    var fragment = $_g1wsgp144jd09f14j.fromElements(elements, win.document);
    $_3phg65145jd09f14l.replaceWith(rng, fragment);
  };
  var deleteAt = function (win, selection) {
    var rng = $_djds0g146jd09f14s.asLtrRange(win, selection);
    $_3phg65145jd09f14l.deleteContents(rng);
  };
  var isCollapsed = function (start, soffset, finish, foffset) {
    return $_6s6cs1w8jd09f014.eq(start, finish) && soffset === foffset;
  };
  var $_7l7kgd142jd09f14c = {
    setExact: setExact,
    getExact: getExact,
    get: get$13,
    setRelative: setRelative,
    toNative: toNative,
    setToElement: setToElement,
    clear: clear$1,
    clone: clone$3,
    replace: replace,
    deleteAt: deleteAt,
    forElement: forElement,
    getFirstRect: getFirstRect$1,
    getBounds: getBounds$1,
    getAtPoint: getAtPoint,
    findWithin: findWithin,
    getAsString: getAsString,
    isCollapsed: isCollapsed
  };

  var COLLAPSED_WIDTH = 2;
  var collapsedRect = function (rect) {
    return {
      left: rect.left,
      top: rect.top,
      right: rect.right,
      bottom: rect.bottom,
      width: $_8z5eqrwbjd09f01q.constant(COLLAPSED_WIDTH),
      height: rect.height
    };
  };
  var toRect$1 = function (rawRect) {
    return {
      left: $_8z5eqrwbjd09f01q.constant(rawRect.left),
      top: $_8z5eqrwbjd09f01q.constant(rawRect.top),
      right: $_8z5eqrwbjd09f01q.constant(rawRect.right),
      bottom: $_8z5eqrwbjd09f01q.constant(rawRect.bottom),
      width: $_8z5eqrwbjd09f01q.constant(rawRect.width),
      height: $_8z5eqrwbjd09f01q.constant(rawRect.height)
    };
  };
  var getRectsFromRange = function (range) {
    if (!range.collapsed) {
      return $_bvikd2w9jd09f01c.map(range.getClientRects(), toRect$1);
    } else {
      var start_1 = $_cnbf2uwtjd09f033.fromDom(range.startContainer);
      return $_sdd74y3jd09f08a.parent(start_1).bind(function (parent) {
        var selection = $_2di69m140jd09f145.exact(start_1, range.startOffset, parent, $_bfp3jf13xjd09f13x.getEnd(parent));
        var optRect = $_7l7kgd142jd09f14c.getFirstRect(range.startContainer.ownerDocument.defaultView, selection);
        return optRect.map(collapsedRect).map($_bvikd2w9jd09f01c.pure);
      }).getOr([]);
    }
  };
  var getRectangles = function (cWin) {
    var sel = cWin.getSelection();
    return sel !== undefined && sel.rangeCount > 0 ? getRectsFromRange(sel.getRangeAt(0)) : [];
  };
  var $_3p64o613wjd09f13q = { getRectangles: getRectangles };

  var EXTRA_SPACING = 50;
  var data = 'data-' + $_3584u7z1jd09f0bs.resolve('last-outer-height');
  var setLastHeight = function (cBody, value) {
    $_6spjcmxwjd09f07e.set(cBody, data, value);
  };
  var getLastHeight = function (cBody) {
    return $_43tcqw13vjd09f13o.safeParse(cBody, data);
  };
  var getBoundsFrom = function (rect) {
    return {
      top: $_8z5eqrwbjd09f01q.constant(rect.top()),
      bottom: $_8z5eqrwbjd09f01q.constant(rect.top() + rect.height())
    };
  };
  var getBounds$2 = function (cWin) {
    var rects = $_3p64o613wjd09f13q.getRectangles(cWin);
    return rects.length > 0 ? $_8nwhzlwajd09f01m.some(rects[0]).map(getBoundsFrom) : $_8nwhzlwajd09f01m.none();
  };
  var findDelta = function (outerWindow, cBody) {
    var last = getLastHeight(cBody);
    var current = outerWindow.innerHeight;
    return last > current ? $_8nwhzlwajd09f01m.some(last - current) : $_8nwhzlwajd09f01m.none();
  };
  var calculate = function (cWin, bounds, delta) {
    var isOutside = bounds.top() > cWin.innerHeight || bounds.bottom() > cWin.innerHeight;
    return isOutside ? Math.min(delta, bounds.bottom() - cWin.innerHeight + EXTRA_SPACING) : 0;
  };
  var setup$1 = function (outerWindow, cWin) {
    var cBody = $_cnbf2uwtjd09f033.fromDom(cWin.document.body);
    var toEditing = function () {
      $_5ymhj513ujd09f13i.resume(cWin);
    };
    var onResize = $_5k3ae313kjd09f11v.bind($_cnbf2uwtjd09f033.fromDom(outerWindow), 'resize', function () {
      findDelta(outerWindow, cBody).each(function (delta) {
        getBounds$2(cWin).each(function (bounds) {
          var cScrollBy = calculate(cWin, bounds, delta);
          if (cScrollBy !== 0) {
            cWin.scrollTo(cWin.pageXOffset, cWin.pageYOffset + cScrollBy);
          }
        });
      });
      setLastHeight(cBody, outerWindow.innerHeight);
    });
    setLastHeight(cBody, outerWindow.innerHeight);
    var destroy = function () {
      onResize.unbind();
    };
    return {
      toEditing: toEditing,
      destroy: destroy
    };
  };
  var $_6zw5un13tjd09f13b = { setup: setup$1 };

  var getBodyFromFrame = function (frame) {
    return $_8nwhzlwajd09f01m.some($_cnbf2uwtjd09f033.fromDom(frame.dom().contentWindow.document.body));
  };
  var getDocFromFrame = function (frame) {
    return $_8nwhzlwajd09f01m.some($_cnbf2uwtjd09f033.fromDom(frame.dom().contentWindow.document));
  };
  var getWinFromFrame = function (frame) {
    return $_8nwhzlwajd09f01m.from(frame.dom().contentWindow);
  };
  var getSelectionFromFrame = function (frame) {
    var optWin = getWinFromFrame(frame);
    return optWin.bind($_7l7kgd142jd09f14c.getExact);
  };
  var getFrame = function (editor) {
    return editor.getFrame();
  };
  var getOrDerive = function (name, f) {
    return function (editor) {
      var g = editor[name].getOrThunk(function () {
        var frame = getFrame(editor);
        return function () {
          return f(frame);
        };
      });
      return g();
    };
  };
  var getOrListen = function (editor, doc, name, type) {
    return editor[name].getOrThunk(function () {
      return function (handler) {
        return $_5k3ae313kjd09f11v.bind(doc, type, handler);
      };
    });
  };
  var toRect$2 = function (rect) {
    return {
      left: $_8z5eqrwbjd09f01q.constant(rect.left),
      top: $_8z5eqrwbjd09f01q.constant(rect.top),
      right: $_8z5eqrwbjd09f01q.constant(rect.right),
      bottom: $_8z5eqrwbjd09f01q.constant(rect.bottom),
      width: $_8z5eqrwbjd09f01q.constant(rect.width),
      height: $_8z5eqrwbjd09f01q.constant(rect.height)
    };
  };
  var getActiveApi = function (editor) {
    var frame = getFrame(editor);
    var tryFallbackBox = function (win) {
      var isCollapsed = function (sel) {
        return $_6s6cs1w8jd09f014.eq(sel.start(), sel.finish()) && sel.soffset() === sel.foffset();
      };
      var toStartRect = function (sel) {
        var rect = sel.start().dom().getBoundingClientRect();
        return rect.width > 0 || rect.height > 0 ? $_8nwhzlwajd09f01m.some(rect).map(toRect$2) : $_8nwhzlwajd09f01m.none();
      };
      return $_7l7kgd142jd09f14c.getExact(win).filter(isCollapsed).bind(toStartRect);
    };
    return getBodyFromFrame(frame).bind(function (body) {
      return getDocFromFrame(frame).bind(function (doc) {
        return getWinFromFrame(frame).map(function (win) {
          var html = $_cnbf2uwtjd09f033.fromDom(doc.dom().documentElement);
          var getCursorBox = editor.getCursorBox.getOrThunk(function () {
            return function () {
              return $_7l7kgd142jd09f14c.get(win).bind(function (sel) {
                return $_7l7kgd142jd09f14c.getFirstRect(win, sel).orThunk(function () {
                  return tryFallbackBox(win);
                });
              });
            };
          });
          var setSelection = editor.setSelection.getOrThunk(function () {
            return function (start, soffset, finish, foffset) {
              $_7l7kgd142jd09f14c.setExact(win, start, soffset, finish, foffset);
            };
          });
          var clearSelection = editor.clearSelection.getOrThunk(function () {
            return function () {
              $_7l7kgd142jd09f14c.clear(win);
            };
          });
          return {
            body: $_8z5eqrwbjd09f01q.constant(body),
            doc: $_8z5eqrwbjd09f01q.constant(doc),
            win: $_8z5eqrwbjd09f01q.constant(win),
            html: $_8z5eqrwbjd09f01q.constant(html),
            getSelection: $_8z5eqrwbjd09f01q.curry(getSelectionFromFrame, frame),
            setSelection: setSelection,
            clearSelection: clearSelection,
            frame: $_8z5eqrwbjd09f01q.constant(frame),
            onKeyup: getOrListen(editor, doc, 'onKeyup', 'keyup'),
            onNodeChanged: getOrListen(editor, doc, 'onNodeChanged', 'selectionchange'),
            onDomChanged: editor.onDomChanged,
            onScrollToCursor: editor.onScrollToCursor,
            onScrollToElement: editor.onScrollToElement,
            onToReading: editor.onToReading,
            onToEditing: editor.onToEditing,
            onToolbarScrollStart: editor.onToolbarScrollStart,
            onTouchContent: editor.onTouchContent,
            onTapContent: editor.onTapContent,
            onTouchToolstrip: editor.onTouchToolstrip,
            getCursorBox: getCursorBox
          };
        });
      });
    });
  };
  var $_1ej5a814fjd09f15u = {
    getBody: getOrDerive('getBody', getBodyFromFrame),
    getDoc: getOrDerive('getDoc', getDocFromFrame),
    getWin: getOrDerive('getWin', getWinFromFrame),
    getSelection: getOrDerive('getSelection', getSelectionFromFrame),
    getFrame: getFrame,
    getActiveApi: getActiveApi
  };

  var attr = 'data-ephox-mobile-fullscreen-style';
  var siblingStyles = 'display:none!important;';
  var ancestorPosition = 'position:absolute!important;';
  var ancestorStyles = 'top:0!important;left:0!important;margin:0' + '!important;padding:0!important;width:100%!important;';
  var bgFallback = 'background-color:rgb(255,255,255)!important;';
  var isAndroid = $_8zynflwgjd09f01z.detect().os.isAndroid();
  var matchColor = function (editorBody) {
    var color = $_a9ctnkzsjd09f0ej.get(editorBody, 'background-color');
    return color !== undefined && color !== '' ? 'background-color:' + color + '!important' : bgFallback;
  };
  var clobberStyles = function (container, editorBody) {
    var gatherSibilings = function (element) {
      var siblings = $_23mimrzkjd09f0dz.siblings(element, '*');
      return siblings;
    };
    var clobber = function (clobberStyle) {
      return function (element) {
        var styles = $_6spjcmxwjd09f07e.get(element, 'style');
        var backup = styles === undefined ? 'no-styles' : styles.trim();
        if (backup === clobberStyle) {
          return;
        } else {
          $_6spjcmxwjd09f07e.set(element, attr, backup);
          $_6spjcmxwjd09f07e.set(element, 'style', clobberStyle);
        }
      };
    };
    var ancestors = $_23mimrzkjd09f0dz.ancestors(container, '*');
    var siblings = $_bvikd2w9jd09f01c.bind(ancestors, gatherSibilings);
    var bgColor = matchColor(editorBody);
    $_bvikd2w9jd09f01c.each(siblings, clobber(siblingStyles));
    $_bvikd2w9jd09f01c.each(ancestors, clobber(ancestorPosition + ancestorStyles + bgColor));
    var containerStyles = isAndroid === true ? '' : ancestorPosition;
    clobber(containerStyles + ancestorStyles + bgColor)(container);
  };
  var restoreStyles = function () {
    var clobberedEls = $_23mimrzkjd09f0dz.all('[' + attr + ']');
    $_bvikd2w9jd09f01c.each(clobberedEls, function (element) {
      var restore = $_6spjcmxwjd09f07e.get(element, attr);
      if (restore !== 'no-styles') {
        $_6spjcmxwjd09f07e.set(element, 'style', restore);
      } else {
        $_6spjcmxwjd09f07e.remove(element, 'style');
      }
      $_6spjcmxwjd09f07e.remove(element, attr);
    });
  };
  var $_6pcgv014gjd09f162 = {
    clobberStyles: clobberStyles,
    restoreStyles: restoreStyles
  };

  var tag = function () {
    var head = $_74vb1xzmjd09f0e3.first('head').getOrDie();
    var nu = function () {
      var meta = $_cnbf2uwtjd09f033.fromTag('meta');
      $_6spjcmxwjd09f07e.set(meta, 'name', 'viewport');
      $_3dpbyky2jd09f081.append(head, meta);
      return meta;
    };
    var element = $_74vb1xzmjd09f0e3.first('meta[name="viewport"]').getOrThunk(nu);
    var backup = $_6spjcmxwjd09f07e.get(element, 'content');
    var maximize = function () {
      $_6spjcmxwjd09f07e.set(element, 'content', 'width=device-width, initial-scale=1.0, user-scalable=no, maximum-scale=1.0');
    };
    var restore = function () {
      if (backup !== undefined && backup !== null && backup.length > 0) {
        $_6spjcmxwjd09f07e.set(element, 'content', backup);
      } else {
        $_6spjcmxwjd09f07e.set(element, 'content', 'user-scalable=yes');
      }
    };
    return {
      maximize: maximize,
      restore: restore
    };
  };
  var $_jj7n114hjd09f168 = { tag: tag };

  var create$5 = function (platform, mask) {
    var meta = $_jj7n114hjd09f168.tag();
    var androidApi = $_eq1tgp12ajd09f0tb.api();
    var androidEvents = $_eq1tgp12ajd09f0tb.api();
    var enter = function () {
      mask.hide();
      $_e7d6ttxujd09f07b.add(platform.container, $_3584u7z1jd09f0bs.resolve('fullscreen-maximized'));
      $_e7d6ttxujd09f07b.add(platform.container, $_3584u7z1jd09f0bs.resolve('android-maximized'));
      meta.maximize();
      $_e7d6ttxujd09f07b.add(platform.body, $_3584u7z1jd09f0bs.resolve('android-scroll-reload'));
      androidApi.set($_6zw5un13tjd09f13b.setup(platform.win, $_1ej5a814fjd09f15u.getWin(platform.editor).getOrDie('no')));
      $_1ej5a814fjd09f15u.getActiveApi(platform.editor).each(function (editorApi) {
        $_6pcgv014gjd09f162.clobberStyles(platform.container, editorApi.body());
        androidEvents.set($_65bvv513pjd09f12f.initEvents(editorApi, platform.toolstrip, platform.alloy));
      });
    };
    var exit = function () {
      meta.restore();
      mask.show();
      $_e7d6ttxujd09f07b.remove(platform.container, $_3584u7z1jd09f0bs.resolve('fullscreen-maximized'));
      $_e7d6ttxujd09f07b.remove(platform.container, $_3584u7z1jd09f0bs.resolve('android-maximized'));
      $_6pcgv014gjd09f162.restoreStyles();
      $_e7d6ttxujd09f07b.remove(platform.body, $_3584u7z1jd09f0bs.resolve('android-scroll-reload'));
      androidEvents.clear();
      androidApi.clear();
    };
    return {
      enter: enter,
      exit: exit
    };
  };
  var $_a8ka3q13ojd09f12b = { create: create$5 };

  var MobileSchema = $_33aoy2xhjd09f061.objOf([
    $_1t7kykx2jd09f043.strictObjOf('editor', [
      $_1t7kykx2jd09f043.strict('getFrame'),
      $_1t7kykx2jd09f043.option('getBody'),
      $_1t7kykx2jd09f043.option('getDoc'),
      $_1t7kykx2jd09f043.option('getWin'),
      $_1t7kykx2jd09f043.option('getSelection'),
      $_1t7kykx2jd09f043.option('setSelection'),
      $_1t7kykx2jd09f043.option('clearSelection'),
      $_1t7kykx2jd09f043.option('cursorSaver'),
      $_1t7kykx2jd09f043.option('onKeyup'),
      $_1t7kykx2jd09f043.option('onNodeChanged'),
      $_1t7kykx2jd09f043.option('getCursorBox'),
      $_1t7kykx2jd09f043.strict('onDomChanged'),
      $_1t7kykx2jd09f043.defaulted('onTouchContent', $_8z5eqrwbjd09f01q.noop),
      $_1t7kykx2jd09f043.defaulted('onTapContent', $_8z5eqrwbjd09f01q.noop),
      $_1t7kykx2jd09f043.defaulted('onTouchToolstrip', $_8z5eqrwbjd09f01q.noop),
      $_1t7kykx2jd09f043.defaulted('onScrollToCursor', $_8z5eqrwbjd09f01q.constant({ unbind: $_8z5eqrwbjd09f01q.noop })),
      $_1t7kykx2jd09f043.defaulted('onScrollToElement', $_8z5eqrwbjd09f01q.constant({ unbind: $_8z5eqrwbjd09f01q.noop })),
      $_1t7kykx2jd09f043.defaulted('onToEditing', $_8z5eqrwbjd09f01q.constant({ unbind: $_8z5eqrwbjd09f01q.noop })),
      $_1t7kykx2jd09f043.defaulted('onToReading', $_8z5eqrwbjd09f01q.constant({ unbind: $_8z5eqrwbjd09f01q.noop })),
      $_1t7kykx2jd09f043.defaulted('onToolbarScrollStart', $_8z5eqrwbjd09f01q.identity)
    ]),
    $_1t7kykx2jd09f043.strict('socket'),
    $_1t7kykx2jd09f043.strict('toolstrip'),
    $_1t7kykx2jd09f043.strict('dropup'),
    $_1t7kykx2jd09f043.strict('toolbar'),
    $_1t7kykx2jd09f043.strict('container'),
    $_1t7kykx2jd09f043.strict('alloy'),
    $_1t7kykx2jd09f043.state('win', function (spec) {
      return $_sdd74y3jd09f08a.owner(spec.socket).dom().defaultView;
    }),
    $_1t7kykx2jd09f043.state('body', function (spec) {
      return $_cnbf2uwtjd09f033.fromDom(spec.socket.dom().ownerDocument.body);
    }),
    $_1t7kykx2jd09f043.defaulted('translate', $_8z5eqrwbjd09f01q.identity),
    $_1t7kykx2jd09f043.defaulted('setReadOnly', $_8z5eqrwbjd09f01q.noop)
  ]);

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
  var first$4 = function (fn, rate) {
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
  var $_9g2t2114kjd09f16p = {
    adaptable: adaptable,
    first: first$4,
    last: last$3
  };

  var sketch$10 = function (onView, translate) {
    var memIcon = $_16gwpy11ejd09f0p7.record(Container.sketch({
      dom: $_1sirkj10qjd09f0ll.dom('<div aria-hidden="true" class="${prefix}-mask-tap-icon"></div>'),
      containerBehaviours: $_fq8al5w4jd09f00e.derive([Toggling.config({
          toggleClass: $_3584u7z1jd09f0bs.resolve('mask-tap-icon-selected'),
          toggleOnExecute: false
        })])
    }));
    var onViewThrottle = $_9g2t2114kjd09f16p.first(onView, 200);
    return Container.sketch({
      dom: $_1sirkj10qjd09f0ll.dom('<div class="${prefix}-disabled-mask"></div>'),
      components: [Container.sketch({
          dom: $_1sirkj10qjd09f0ll.dom('<div class="${prefix}-content-container"></div>'),
          components: [Button.sketch({
              dom: $_1sirkj10qjd09f0ll.dom('<div class="${prefix}-content-tap-section"></div>'),
              components: [memIcon.asSpec()],
              action: function (button) {
                onViewThrottle.throttle();
              },
              buttonBehaviours: $_fq8al5w4jd09f00e.derive([Toggling.config({ toggleClass: $_3584u7z1jd09f0bs.resolve('mask-tap-icon-selected') })])
            })]
        })]
    });
  };
  var $_bk44n014jjd09f16i = { sketch: sketch$10 };

  var produce = function (raw) {
    var mobile = $_33aoy2xhjd09f061.asRawOrDie('Getting AndroidWebapp schema', MobileSchema, raw);
    $_a9ctnkzsjd09f0ej.set(mobile.toolstrip, 'width', '100%');
    var onTap = function () {
      mobile.setReadOnly(true);
      mode.enter();
    };
    var mask = $_60hy6b12kjd09f0v5.build($_bk44n014jjd09f16i.sketch(onTap, mobile.translate));
    mobile.alloy.add(mask);
    var maskApi = {
      show: function () {
        mobile.alloy.add(mask);
      },
      hide: function () {
        mobile.alloy.remove(mask);
      }
    };
    $_3dpbyky2jd09f081.append(mobile.container, mask.element());
    var mode = $_a8ka3q13ojd09f12b.create(mobile, maskApi);
    return {
      setReadOnly: mobile.setReadOnly,
      refreshStructure: $_8z5eqrwbjd09f01q.noop,
      enter: mode.enter,
      exit: mode.exit,
      destroy: $_8z5eqrwbjd09f01q.noop
    };
  };
  var $_gcpus113njd09f125 = { produce: produce };

  var schema$14 = [
    $_1t7kykx2jd09f043.defaulted('shell', true),
    $_beskyy10djd09f0i0.field('toolbarBehaviours', [Replacing])
  ];
  var enhanceGroups = function (detail) {
    return { behaviours: $_fq8al5w4jd09f00e.derive([Replacing.config({})]) };
  };
  var partTypes$1 = [$_37ihar10kjd09f0js.optional({
      name: 'groups',
      overrides: enhanceGroups
    })];
  var $_fob57r14njd09f17f = {
    name: $_8z5eqrwbjd09f01q.constant('Toolbar'),
    schema: $_8z5eqrwbjd09f01q.constant(schema$14),
    parts: $_8z5eqrwbjd09f01q.constant(partTypes$1)
  };

  var factory$4 = function (detail, components, spec, _externals) {
    var setGroups = function (toolbar, groups) {
      getGroupContainer(toolbar).fold(function () {
        console.error('Toolbar was defined to not be a shell, but no groups container was specified in components');
        throw new Error('Toolbar was defined to not be a shell, but no groups container was specified in components');
      }, function (container) {
        Replacing.set(container, groups);
      });
    };
    var getGroupContainer = function (component) {
      return detail.shell() ? $_8nwhzlwajd09f01m.some(component) : $_7vfhnq10ijd09f0iz.getPart(component, detail, 'groups');
    };
    var extra = detail.shell() ? {
      behaviours: [Replacing.config({})],
      components: []
    } : {
      behaviours: [],
      components: components
    };
    return {
      uid: detail.uid(),
      dom: detail.dom(),
      components: extra.components,
      behaviours: $_3htayhwyjd09f03i.deepMerge($_fq8al5w4jd09f00e.derive(extra.behaviours), $_beskyy10djd09f0i0.get(detail.toolbarBehaviours())),
      apis: { setGroups: setGroups },
      domModification: { attributes: { role: 'group' } }
    };
  };
  var Toolbar = $_4q1unv10ejd09f0i8.composite({
    name: 'Toolbar',
    configFields: $_fob57r14njd09f17f.schema(),
    partFields: $_fob57r14njd09f17f.parts(),
    factory: factory$4,
    apis: {
      setGroups: function (apis, toolbar, groups) {
        apis.setGroups(toolbar, groups);
      }
    }
  });

  var schema$15 = [
    $_1t7kykx2jd09f043.strict('items'),
    $_1l599yytjd09f0ar.markers(['itemClass']),
    $_beskyy10djd09f0i0.field('tgroupBehaviours', [Keying])
  ];
  var partTypes$2 = [$_37ihar10kjd09f0js.group({
      name: 'items',
      unit: 'item',
      overrides: function (detail) {
        return { domModification: { classes: [detail.markers().itemClass()] } };
      }
    })];
  var $_6zub5714pjd09f17l = {
    name: $_8z5eqrwbjd09f01q.constant('ToolbarGroup'),
    schema: $_8z5eqrwbjd09f01q.constant(schema$15),
    parts: $_8z5eqrwbjd09f01q.constant(partTypes$2)
  };

  var factory$5 = function (detail, components, spec, _externals) {
    return $_3htayhwyjd09f03i.deepMerge({ dom: { attributes: { role: 'toolbar' } } }, {
      uid: detail.uid(),
      dom: detail.dom(),
      components: components,
      behaviours: $_3htayhwyjd09f03i.deepMerge($_fq8al5w4jd09f00e.derive([Keying.config({
          mode: 'flow',
          selector: '.' + detail.markers().itemClass()
        })]), $_beskyy10djd09f0i0.get(detail.tgroupBehaviours())),
      'debug.sketcher': spec['debug.sketcher']
    });
  };
  var ToolbarGroup = $_4q1unv10ejd09f0i8.composite({
    name: 'ToolbarGroup',
    configFields: $_6zub5714pjd09f17l.schema(),
    partFields: $_6zub5714pjd09f17l.parts(),
    factory: factory$5
  });

  var dataHorizontal = 'data-' + $_3584u7z1jd09f0bs.resolve('horizontal-scroll');
  var canScrollVertically = function (container) {
    container.dom().scrollTop = 1;
    var result = container.dom().scrollTop !== 0;
    container.dom().scrollTop = 0;
    return result;
  };
  var canScrollHorizontally = function (container) {
    container.dom().scrollLeft = 1;
    var result = container.dom().scrollLeft !== 0;
    container.dom().scrollLeft = 0;
    return result;
  };
  var hasVerticalScroll = function (container) {
    return container.dom().scrollTop > 0 || canScrollVertically(container);
  };
  var hasHorizontalScroll = function (container) {
    return container.dom().scrollLeft > 0 || canScrollHorizontally(container);
  };
  var markAsHorizontal = function (container) {
    $_6spjcmxwjd09f07e.set(container, dataHorizontal, 'true');
  };
  var hasScroll = function (container) {
    return $_6spjcmxwjd09f07e.get(container, dataHorizontal) === 'true' ? hasHorizontalScroll : hasVerticalScroll;
  };
  var exclusive = function (scope, selector) {
    return $_5k3ae313kjd09f11v.bind(scope, 'touchmove', function (event) {
      $_74vb1xzmjd09f0e3.closest(event.target(), selector).filter(hasScroll).fold(function () {
        event.raw().preventDefault();
      }, $_8z5eqrwbjd09f01q.noop);
    });
  };
  var $_f0swo714qjd09f17q = {
    exclusive: exclusive,
    markAsHorizontal: markAsHorizontal
  };

  function ScrollingToolbar () {
    var makeGroup = function (gSpec) {
      var scrollClass = gSpec.scrollable === true ? '${prefix}-toolbar-scrollable-group' : '';
      return {
        dom: $_1sirkj10qjd09f0ll.dom('<div aria-label="' + gSpec.label + '" class="${prefix}-toolbar-group ' + scrollClass + '"></div>'),
        tgroupBehaviours: $_fq8al5w4jd09f00e.derive([$_2qc4or11sjd09f0qy.config('adhoc-scrollable-toolbar', gSpec.scrollable === true ? [$_6j84lww6jd09f00y.runOnInit(function (component, simulatedEvent) {
              $_a9ctnkzsjd09f0ej.set(component.element(), 'overflow-x', 'auto');
              $_f0swo714qjd09f17q.markAsHorizontal(component.element());
              $_1p83kb13hjd09f11i.register(component.element());
            })] : [])]),
        components: [Container.sketch({ components: [ToolbarGroup.parts().items({})] })],
        markers: { itemClass: $_3584u7z1jd09f0bs.resolve('toolbar-group-item') },
        items: gSpec.items
      };
    };
    var toolbar = $_60hy6b12kjd09f0v5.build(Toolbar.sketch({
      dom: $_1sirkj10qjd09f0ll.dom('<div class="${prefix}-toolbar"></div>'),
      components: [Toolbar.parts().groups({})],
      toolbarBehaviours: $_fq8al5w4jd09f00e.derive([
        Toggling.config({
          toggleClass: $_3584u7z1jd09f0bs.resolve('context-toolbar'),
          toggleOnExecute: false,
          aria: { mode: 'none' }
        }),
        Keying.config({ mode: 'cyclic' })
      ]),
      shell: true
    }));
    var wrapper = $_60hy6b12kjd09f0v5.build(Container.sketch({
      dom: { classes: [$_3584u7z1jd09f0bs.resolve('toolstrip')] },
      components: [$_60hy6b12kjd09f0v5.premade(toolbar)],
      containerBehaviours: $_fq8al5w4jd09f00e.derive([Toggling.config({
          toggleClass: $_3584u7z1jd09f0bs.resolve('android-selection-context-toolbar'),
          toggleOnExecute: false
        })])
    }));
    var resetGroups = function () {
      Toolbar.setGroups(toolbar, initGroups.get());
      Toggling.off(toolbar);
    };
    var initGroups = Cell([]);
    var setGroups = function (gs) {
      initGroups.set(gs);
      resetGroups();
    };
    var createGroups = function (gs) {
      return $_bvikd2w9jd09f01c.map(gs, $_8z5eqrwbjd09f01q.compose(ToolbarGroup.sketch, makeGroup));
    };
    var refresh = function () {
      Toolbar.refresh(toolbar);
    };
    var setContextToolbar = function (gs) {
      Toggling.on(toolbar);
      Toolbar.setGroups(toolbar, gs);
    };
    var restoreToolbar = function () {
      if (Toggling.isOn(toolbar)) {
        resetGroups();
      }
    };
    var focus = function () {
      Keying.focusIn(toolbar);
    };
    return {
      wrapper: $_8z5eqrwbjd09f01q.constant(wrapper),
      toolbar: $_8z5eqrwbjd09f01q.constant(toolbar),
      createGroups: createGroups,
      setGroups: setGroups,
      setContextToolbar: setContextToolbar,
      restoreToolbar: restoreToolbar,
      refresh: refresh,
      focus: focus
    };
  }

  var makeEditSwitch = function (webapp) {
    return $_60hy6b12kjd09f0v5.build(Button.sketch({
      dom: $_1sirkj10qjd09f0ll.dom('<div class="${prefix}-mask-edit-icon ${prefix}-icon"></div>'),
      action: function () {
        webapp.run(function (w) {
          w.setReadOnly(false);
        });
      }
    }));
  };
  var makeSocket = function () {
    return $_60hy6b12kjd09f0v5.build(Container.sketch({
      dom: $_1sirkj10qjd09f0ll.dom('<div class="${prefix}-editor-socket"></div>'),
      components: [],
      containerBehaviours: $_fq8al5w4jd09f00e.derive([Replacing.config({})])
    }));
  };
  var showEdit = function (socket, switchToEdit) {
    Replacing.append(socket, $_60hy6b12kjd09f0v5.premade(switchToEdit));
  };
  var hideEdit = function (socket, switchToEdit) {
    Replacing.remove(socket, switchToEdit);
  };
  var updateMode = function (socket, switchToEdit, readOnly, root) {
    var swap = readOnly === true ? Swapping.toAlpha : Swapping.toOmega;
    swap(root);
    var f = readOnly ? showEdit : hideEdit;
    f(socket, switchToEdit);
  };
  var $_2ba61a14rjd09f17w = {
    makeEditSwitch: makeEditSwitch,
    makeSocket: makeSocket,
    updateMode: updateMode
  };

  var getAnimationRoot = function (component, slideConfig) {
    return slideConfig.getAnimationRoot().fold(function () {
      return component.element();
    }, function (get) {
      return get(component);
    });
  };
  var getDimensionProperty = function (slideConfig) {
    return slideConfig.dimension().property();
  };
  var getDimension = function (slideConfig, elem) {
    return slideConfig.dimension().getDimension()(elem);
  };
  var disableTransitions = function (component, slideConfig) {
    var root = getAnimationRoot(component, slideConfig);
    $_92oj9712yjd09f0yc.remove(root, [
      slideConfig.shrinkingClass(),
      slideConfig.growingClass()
    ]);
  };
  var setShrunk = function (component, slideConfig) {
    $_e7d6ttxujd09f07b.remove(component.element(), slideConfig.openClass());
    $_e7d6ttxujd09f07b.add(component.element(), slideConfig.closedClass());
    $_a9ctnkzsjd09f0ej.set(component.element(), getDimensionProperty(slideConfig), '0px');
    $_a9ctnkzsjd09f0ej.reflow(component.element());
  };
  var measureTargetSize = function (component, slideConfig) {
    setGrown(component, slideConfig);
    var expanded = getDimension(slideConfig, component.element());
    setShrunk(component, slideConfig);
    return expanded;
  };
  var setGrown = function (component, slideConfig) {
    $_e7d6ttxujd09f07b.remove(component.element(), slideConfig.closedClass());
    $_e7d6ttxujd09f07b.add(component.element(), slideConfig.openClass());
    $_a9ctnkzsjd09f0ej.remove(component.element(), getDimensionProperty(slideConfig));
  };
  var doImmediateShrink = function (component, slideConfig, slideState) {
    slideState.setCollapsed();
    $_a9ctnkzsjd09f0ej.set(component.element(), getDimensionProperty(slideConfig), getDimension(slideConfig, component.element()));
    $_a9ctnkzsjd09f0ej.reflow(component.element());
    disableTransitions(component, slideConfig);
    setShrunk(component, slideConfig);
    slideConfig.onStartShrink()(component);
    slideConfig.onShrunk()(component);
  };
  var doStartShrink = function (component, slideConfig, slideState) {
    slideState.setCollapsed();
    $_a9ctnkzsjd09f0ej.set(component.element(), getDimensionProperty(slideConfig), getDimension(slideConfig, component.element()));
    $_a9ctnkzsjd09f0ej.reflow(component.element());
    var root = getAnimationRoot(component, slideConfig);
    $_e7d6ttxujd09f07b.add(root, slideConfig.shrinkingClass());
    setShrunk(component, slideConfig);
    slideConfig.onStartShrink()(component);
  };
  var doStartGrow = function (component, slideConfig, slideState) {
    var fullSize = measureTargetSize(component, slideConfig);
    var root = getAnimationRoot(component, slideConfig);
    $_e7d6ttxujd09f07b.add(root, slideConfig.growingClass());
    setGrown(component, slideConfig);
    $_a9ctnkzsjd09f0ej.set(component.element(), getDimensionProperty(slideConfig), fullSize);
    slideState.setExpanded();
    slideConfig.onStartGrow()(component);
  };
  var grow = function (component, slideConfig, slideState) {
    if (!slideState.isExpanded())
      doStartGrow(component, slideConfig, slideState);
  };
  var shrink = function (component, slideConfig, slideState) {
    if (slideState.isExpanded())
      doStartShrink(component, slideConfig, slideState);
  };
  var immediateShrink = function (component, slideConfig, slideState) {
    if (slideState.isExpanded())
      doImmediateShrink(component, slideConfig, slideState);
  };
  var hasGrown = function (component, slideConfig, slideState) {
    return slideState.isExpanded();
  };
  var hasShrunk = function (component, slideConfig, slideState) {
    return slideState.isCollapsed();
  };
  var isGrowing = function (component, slideConfig, slideState) {
    var root = getAnimationRoot(component, slideConfig);
    return $_e7d6ttxujd09f07b.has(root, slideConfig.growingClass()) === true;
  };
  var isShrinking = function (component, slideConfig, slideState) {
    var root = getAnimationRoot(component, slideConfig);
    return $_e7d6ttxujd09f07b.has(root, slideConfig.shrinkingClass()) === true;
  };
  var isTransitioning = function (component, slideConfig, slideState) {
    return isGrowing(component, slideConfig, slideState) === true || isShrinking(component, slideConfig, slideState) === true;
  };
  var toggleGrow = function (component, slideConfig, slideState) {
    var f = slideState.isExpanded() ? doStartShrink : doStartGrow;
    f(component, slideConfig, slideState);
  };
  var $_1knrmu14vjd09f18f = {
    grow: grow,
    shrink: shrink,
    immediateShrink: immediateShrink,
    hasGrown: hasGrown,
    hasShrunk: hasShrunk,
    isGrowing: isGrowing,
    isShrinking: isShrinking,
    isTransitioning: isTransitioning,
    toggleGrow: toggleGrow,
    disableTransitions: disableTransitions
  };

  var exhibit$5 = function (base, slideConfig) {
    var expanded = slideConfig.expanded();
    return expanded ? $_brhg4pxkjd09f06j.nu({
      classes: [slideConfig.openClass()],
      styles: {}
    }) : $_brhg4pxkjd09f06j.nu({
      classes: [slideConfig.closedClass()],
      styles: $_q0etsx6jd09f053.wrap(slideConfig.dimension().property(), '0px')
    });
  };
  var events$9 = function (slideConfig, slideState) {
    return $_6j84lww6jd09f00y.derive([$_6j84lww6jd09f00y.run($_8nqyjjwxjd09f03f.transitionend(), function (component, simulatedEvent) {
        var raw = simulatedEvent.event().raw();
        if (raw.propertyName === slideConfig.dimension().property()) {
          $_1knrmu14vjd09f18f.disableTransitions(component, slideConfig, slideState);
          if (slideState.isExpanded())
            $_a9ctnkzsjd09f0ej.remove(component.element(), slideConfig.dimension().property());
          var notify = slideState.isExpanded() ? slideConfig.onGrown() : slideConfig.onShrunk();
          notify(component, simulatedEvent);
        }
      })]);
  };
  var $_74ddft14ujd09f189 = {
    exhibit: exhibit$5,
    events: events$9
  };

  var SlidingSchema = [
    $_1t7kykx2jd09f043.strict('closedClass'),
    $_1t7kykx2jd09f043.strict('openClass'),
    $_1t7kykx2jd09f043.strict('shrinkingClass'),
    $_1t7kykx2jd09f043.strict('growingClass'),
    $_1t7kykx2jd09f043.option('getAnimationRoot'),
    $_1l599yytjd09f0ar.onHandler('onShrunk'),
    $_1l599yytjd09f0ar.onHandler('onStartShrink'),
    $_1l599yytjd09f0ar.onHandler('onGrown'),
    $_1l599yytjd09f0ar.onHandler('onStartGrow'),
    $_1t7kykx2jd09f043.defaulted('expanded', false),
    $_1t7kykx2jd09f043.strictOf('dimension', $_33aoy2xhjd09f061.choose('property', {
      width: [
        $_1l599yytjd09f0ar.output('property', 'width'),
        $_1l599yytjd09f0ar.output('getDimension', function (elem) {
          return $_cyexp9117jd09f0o3.get(elem) + 'px';
        })
      ],
      height: [
        $_1l599yytjd09f0ar.output('property', 'height'),
        $_1l599yytjd09f0ar.output('getDimension', function (elem) {
          return $_52xa2jzrjd09f0eh.get(elem) + 'px';
        })
      ]
    }))
  ];

  var init$4 = function (spec) {
    var state = Cell(spec.expanded());
    var readState = function () {
      return 'expanded: ' + state.get();
    };
    return BehaviourState({
      isExpanded: function () {
        return state.get() === true;
      },
      isCollapsed: function () {
        return state.get() === false;
      },
      setCollapsed: $_8z5eqrwbjd09f01q.curry(state.set, false),
      setExpanded: $_8z5eqrwbjd09f01q.curry(state.set, true),
      readState: readState
    });
  };
  var $_6d24xk14xjd09f18y = { init: init$4 };

  var Sliding = $_fq8al5w4jd09f00e.create({
    fields: SlidingSchema,
    name: 'sliding',
    active: $_74ddft14ujd09f189,
    apis: $_1knrmu14vjd09f18f,
    state: $_6d24xk14xjd09f18y
  });

  var build$2 = function (refresh, scrollIntoView) {
    var dropup = $_60hy6b12kjd09f0v5.build(Container.sketch({
      dom: {
        tag: 'div',
        classes: $_3584u7z1jd09f0bs.resolve('dropup')
      },
      components: [],
      containerBehaviours: $_fq8al5w4jd09f00e.derive([
        Replacing.config({}),
        Sliding.config({
          closedClass: $_3584u7z1jd09f0bs.resolve('dropup-closed'),
          openClass: $_3584u7z1jd09f0bs.resolve('dropup-open'),
          shrinkingClass: $_3584u7z1jd09f0bs.resolve('dropup-shrinking'),
          growingClass: $_3584u7z1jd09f0bs.resolve('dropup-growing'),
          dimension: { property: 'height' },
          onShrunk: function (component) {
            refresh();
            scrollIntoView();
            Replacing.set(component, []);
          },
          onGrown: function (component) {
            refresh();
            scrollIntoView();
          }
        }),
        $_2jq3vfz0jd09f0bp.orientation(function (component, data) {
          disappear($_8z5eqrwbjd09f01q.noop);
        })
      ])
    }));
    var appear = function (menu, update, component) {
      if (Sliding.hasShrunk(dropup) === true && Sliding.isTransitioning(dropup) === false) {
        window.requestAnimationFrame(function () {
          update(component);
          Replacing.set(dropup, [menu()]);
          Sliding.grow(dropup);
        });
      }
    };
    var disappear = function (onReadyToShrink) {
      window.requestAnimationFrame(function () {
        onReadyToShrink();
        Sliding.shrink(dropup);
      });
    };
    return {
      appear: appear,
      disappear: disappear,
      component: $_8z5eqrwbjd09f01q.constant(dropup),
      element: dropup.element
    };
  };
  var $_f797mu14sjd09f182 = { build: build$2 };

  var isDangerous = function (event) {
    return event.raw().which === $_xd6lgzejd09f0d6.BACKSPACE()[0] && !$_bvikd2w9jd09f01c.contains([
      'input',
      'textarea'
    ], $_arrpm2xxjd09f07k.name(event.target()));
  };
  var isFirefox = $_8zynflwgjd09f01z.detect().browser.isFirefox();
  var settingsSchema = $_33aoy2xhjd09f061.objOfOnly([
    $_1t7kykx2jd09f043.strictFunction('triggerEvent'),
    $_1t7kykx2jd09f043.strictFunction('broadcastEvent'),
    $_1t7kykx2jd09f043.defaulted('stopBackspace', true)
  ]);
  var bindFocus = function (container, handler) {
    if (isFirefox) {
      return $_5k3ae313kjd09f11v.capture(container, 'focus', handler);
    } else {
      return $_5k3ae313kjd09f11v.bind(container, 'focusin', handler);
    }
  };
  var bindBlur = function (container, handler) {
    if (isFirefox) {
      return $_5k3ae313kjd09f11v.capture(container, 'blur', handler);
    } else {
      return $_5k3ae313kjd09f11v.bind(container, 'focusout', handler);
    }
  };
  var setup$2 = function (container, rawSettings) {
    var settings = $_33aoy2xhjd09f061.asRawOrDie('Getting GUI events settings', settingsSchema, rawSettings);
    var pointerEvents = $_8zynflwgjd09f01z.detect().deviceType.isTouch() ? [
      'touchstart',
      'touchmove',
      'touchend',
      'gesturestart'
    ] : [
      'mousedown',
      'mouseup',
      'mouseover',
      'mousemove',
      'mouseout',
      'click'
    ];
    var tapEvent = $_colzkd13rjd09f12w.monitor(settings);
    var simpleEvents = $_bvikd2w9jd09f01c.map(pointerEvents.concat([
      'selectstart',
      'input',
      'contextmenu',
      'change',
      'transitionend',
      'dragstart',
      'dragover',
      'drop'
    ]), function (type) {
      return $_5k3ae313kjd09f11v.bind(container, type, function (event) {
        tapEvent.fireIfReady(event, type).each(function (tapStopped) {
          if (tapStopped)
            event.kill();
        });
        var stopped = settings.triggerEvent(type, event);
        if (stopped)
          event.kill();
      });
    });
    var onKeydown = $_5k3ae313kjd09f11v.bind(container, 'keydown', function (event) {
      var stopped = settings.triggerEvent('keydown', event);
      if (stopped)
        event.kill();
      else if (settings.stopBackspace === true && isDangerous(event)) {
        event.prevent();
      }
    });
    var onFocusIn = bindFocus(container, function (event) {
      var stopped = settings.triggerEvent('focusin', event);
      if (stopped)
        event.kill();
    });
    var onFocusOut = bindBlur(container, function (event) {
      var stopped = settings.triggerEvent('focusout', event);
      if (stopped)
        event.kill();
      setTimeout(function () {
        settings.triggerEvent($_5iytewwjd09f03c.postBlur(), event);
      }, 0);
    });
    var defaultView = $_sdd74y3jd09f08a.defaultView(container);
    var onWindowScroll = $_5k3ae313kjd09f11v.bind(defaultView, 'scroll', function (event) {
      var stopped = settings.broadcastEvent($_5iytewwjd09f03c.windowScroll(), event);
      if (stopped)
        event.kill();
    });
    var unbind = function () {
      $_bvikd2w9jd09f01c.each(simpleEvents, function (e) {
        e.unbind();
      });
      onKeydown.unbind();
      onFocusIn.unbind();
      onFocusOut.unbind();
      onWindowScroll.unbind();
    };
    return { unbind: unbind };
  };
  var $_4ui16f150jd09f19m = { setup: setup$2 };

  var derive$3 = function (rawEvent, rawTarget) {
    var source = $_q0etsx6jd09f053.readOptFrom(rawEvent, 'target').map(function (getTarget) {
      return getTarget();
    }).getOr(rawTarget);
    return Cell(source);
  };
  var $_bzgacs152jd09f1a3 = { derive: derive$3 };

  var fromSource = function (event, source) {
    var stopper = Cell(false);
    var cutter = Cell(false);
    var stop = function () {
      stopper.set(true);
    };
    var cut = function () {
      cutter.set(true);
    };
    return {
      stop: stop,
      cut: cut,
      isStopped: stopper.get,
      isCut: cutter.get,
      event: $_8z5eqrwbjd09f01q.constant(event),
      setSource: source.set,
      getSource: source.get
    };
  };
  var fromExternal = function (event) {
    var stopper = Cell(false);
    var stop = function () {
      stopper.set(true);
    };
    return {
      stop: stop,
      cut: $_8z5eqrwbjd09f01q.noop,
      isStopped: stopper.get,
      isCut: $_8z5eqrwbjd09f01q.constant(false),
      event: $_8z5eqrwbjd09f01q.constant(event),
      setTarget: $_8z5eqrwbjd09f01q.die(new Error('Cannot set target of a broadcasted event')),
      getTarget: $_8z5eqrwbjd09f01q.die(new Error('Cannot get target of a broadcasted event'))
    };
  };
  var fromTarget = function (event, target) {
    var source = Cell(target);
    return fromSource(event, source);
  };
  var $_kjh31153jd09f1a8 = {
    fromSource: fromSource,
    fromExternal: fromExternal,
    fromTarget: fromTarget
  };

  var adt$6 = $_enqz8zx4jd09f04j.generate([
    { stopped: [] },
    { resume: ['element'] },
    { complete: [] }
  ]);
  var doTriggerHandler = function (lookup, eventType, rawEvent, target, source, logger) {
    var handler = lookup(eventType, target);
    var simulatedEvent = $_kjh31153jd09f1a8.fromSource(rawEvent, source);
    return handler.fold(function () {
      logger.logEventNoHandlers(eventType, target);
      return adt$6.complete();
    }, function (handlerInfo) {
      var descHandler = handlerInfo.descHandler();
      var eventHandler = $_jmzzy12vjd09f0xk.getHandler(descHandler);
      eventHandler(simulatedEvent);
      if (simulatedEvent.isStopped()) {
        logger.logEventStopped(eventType, handlerInfo.element(), descHandler.purpose());
        return adt$6.stopped();
      } else if (simulatedEvent.isCut()) {
        logger.logEventCut(eventType, handlerInfo.element(), descHandler.purpose());
        return adt$6.complete();
      } else
        return $_sdd74y3jd09f08a.parent(handlerInfo.element()).fold(function () {
          logger.logNoParent(eventType, handlerInfo.element(), descHandler.purpose());
          return adt$6.complete();
        }, function (parent) {
          logger.logEventResponse(eventType, handlerInfo.element(), descHandler.purpose());
          return adt$6.resume(parent);
        });
    });
  };
  var doTriggerOnUntilStopped = function (lookup, eventType, rawEvent, rawTarget, source, logger) {
    return doTriggerHandler(lookup, eventType, rawEvent, rawTarget, source, logger).fold(function () {
      return true;
    }, function (parent) {
      return doTriggerOnUntilStopped(lookup, eventType, rawEvent, parent, source, logger);
    }, function () {
      return false;
    });
  };
  var triggerHandler = function (lookup, eventType, rawEvent, target, logger) {
    var source = $_bzgacs152jd09f1a3.derive(rawEvent, target);
    return doTriggerHandler(lookup, eventType, rawEvent, target, source, logger);
  };
  var broadcast = function (listeners, rawEvent, logger) {
    var simulatedEvent = $_kjh31153jd09f1a8.fromExternal(rawEvent);
    $_bvikd2w9jd09f01c.each(listeners, function (listener) {
      var descHandler = listener.descHandler();
      var handler = $_jmzzy12vjd09f0xk.getHandler(descHandler);
      handler(simulatedEvent);
    });
    return simulatedEvent.isStopped();
  };
  var triggerUntilStopped = function (lookup, eventType, rawEvent, logger) {
    var rawTarget = rawEvent.target();
    return triggerOnUntilStopped(lookup, eventType, rawEvent, rawTarget, logger);
  };
  var triggerOnUntilStopped = function (lookup, eventType, rawEvent, rawTarget, logger) {
    var source = $_bzgacs152jd09f1a3.derive(rawEvent, rawTarget);
    return doTriggerOnUntilStopped(lookup, eventType, rawEvent, rawTarget, source, logger);
  };
  var $_4xpjn6151jd09f19x = {
    triggerHandler: triggerHandler,
    triggerUntilStopped: triggerUntilStopped,
    triggerOnUntilStopped: triggerOnUntilStopped,
    broadcast: broadcast
  };

  var closest$4 = function (target, transform, isRoot) {
    var delegate = $_605gqayijd09f09k.closest(target, function (elem) {
      return transform(elem).isSome();
    }, isRoot);
    return delegate.bind(transform);
  };
  var $_8sk0lb156jd09f1au = { closest: closest$4 };

  var eventHandler = $_gbd4xkxmjd09f06u.immutable('element', 'descHandler');
  var messageHandler = function (id, handler) {
    return {
      id: $_8z5eqrwbjd09f01q.constant(id),
      descHandler: $_8z5eqrwbjd09f01q.constant(handler)
    };
  };
  function EventRegistry () {
    var registry = {};
    var registerId = function (extraArgs, id, events) {
      $_32a0zdx0jd09f03l.each(events, function (v, k) {
        var handlers = registry[k] !== undefined ? registry[k] : {};
        handlers[id] = $_jmzzy12vjd09f0xk.curryArgs(v, extraArgs);
        registry[k] = handlers;
      });
    };
    var findHandler = function (handlers, elem) {
      return $_69difv10mjd09f0kv.read(elem).fold(function (err) {
        return $_8nwhzlwajd09f01m.none();
      }, function (id) {
        var reader = $_q0etsx6jd09f053.readOpt(id);
        return handlers.bind(reader).map(function (descHandler) {
          return eventHandler(elem, descHandler);
        });
      });
    };
    var filterByType = function (type) {
      return $_q0etsx6jd09f053.readOptFrom(registry, type).map(function (handlers) {
        return $_32a0zdx0jd09f03l.mapToArray(handlers, function (f, id) {
          return messageHandler(id, f);
        });
      }).getOr([]);
    };
    var find = function (isAboveRoot, type, target) {
      var readType = $_q0etsx6jd09f053.readOpt(type);
      var handlers = readType(registry);
      return $_8sk0lb156jd09f1au.closest(target, function (elem) {
        return findHandler(handlers, elem);
      }, isAboveRoot);
    };
    var unregisterId = function (id) {
      $_32a0zdx0jd09f03l.each(registry, function (handlersById, eventName) {
        if (handlersById.hasOwnProperty(id))
          delete handlersById[id];
      });
    };
    return {
      registerId: registerId,
      unregisterId: unregisterId,
      filterByType: filterByType,
      find: find
    };
  }

  function Registry () {
    var events = EventRegistry();
    var components = {};
    var readOrTag = function (component) {
      var elem = component.element();
      return $_69difv10mjd09f0kv.read(elem).fold(function () {
        return $_69difv10mjd09f0kv.write('uid-', component.element());
      }, function (uid) {
        return uid;
      });
    };
    var failOnDuplicate = function (component, tagId) {
      var conflict = components[tagId];
      if (conflict === component)
        unregister(component);
      else
        throw new Error('The tagId "' + tagId + '" is already used by: ' + $_9zn22gy9jd09f091.element(conflict.element()) + '\nCannot use it for: ' + $_9zn22gy9jd09f091.element(component.element()) + '\n' + 'The conflicting element is' + ($_7m8dypy7jd09f08o.inBody(conflict.element()) ? ' ' : ' not ') + 'already in the DOM');
    };
    var register = function (component) {
      var tagId = readOrTag(component);
      if ($_q0etsx6jd09f053.hasKey(components, tagId))
        failOnDuplicate(component, tagId);
      var extraArgs = [component];
      events.registerId(extraArgs, tagId, component.events());
      components[tagId] = component;
    };
    var unregister = function (component) {
      $_69difv10mjd09f0kv.read(component.element()).each(function (tagId) {
        components[tagId] = undefined;
        events.unregisterId(tagId);
      });
    };
    var filter = function (type) {
      return events.filterByType(type);
    };
    var find = function (isAboveRoot, type, target) {
      return events.find(isAboveRoot, type, target);
    };
    var getById = function (id) {
      return $_q0etsx6jd09f053.readOpt(id)(components);
    };
    return {
      find: find,
      filter: filter,
      register: register,
      unregister: unregister,
      getById: getById
    };
  }

  var create$6 = function () {
    var root = $_60hy6b12kjd09f0v5.build(Container.sketch({ dom: { tag: 'div' } }));
    return takeover(root);
  };
  var takeover = function (root) {
    var isAboveRoot = function (el) {
      return $_sdd74y3jd09f08a.parent(root.element()).fold(function () {
        return true;
      }, function (parent) {
        return $_6s6cs1w8jd09f014.eq(el, parent);
      });
    };
    var registry = Registry();
    var lookup = function (eventName, target) {
      return registry.find(isAboveRoot, eventName, target);
    };
    var domEvents = $_4ui16f150jd09f19m.setup(root.element(), {
      triggerEvent: function (eventName, event) {
        return $_fgkb9dy8jd09f08t.monitorEvent(eventName, event.target(), function (logger) {
          return $_4xpjn6151jd09f19x.triggerUntilStopped(lookup, eventName, event, logger);
        });
      },
      broadcastEvent: function (eventName, event) {
        var listeners = registry.filter(eventName);
        return $_4xpjn6151jd09f19x.broadcast(listeners, event);
      }
    });
    var systemApi = SystemApi({
      debugInfo: $_8z5eqrwbjd09f01q.constant('real'),
      triggerEvent: function (customType, target, data) {
        $_fgkb9dy8jd09f08t.monitorEvent(customType, target, function (logger) {
          $_4xpjn6151jd09f19x.triggerOnUntilStopped(lookup, customType, data, target, logger);
        });
      },
      triggerFocus: function (target, originator) {
        $_69difv10mjd09f0kv.read(target).fold(function () {
          $_ccs2jvygjd09f09f.focus(target);
        }, function (_alloyId) {
          $_fgkb9dy8jd09f08t.monitorEvent($_5iytewwjd09f03c.focus(), target, function (logger) {
            $_4xpjn6151jd09f19x.triggerHandler(lookup, $_5iytewwjd09f03c.focus(), {
              originator: $_8z5eqrwbjd09f01q.constant(originator),
              target: $_8z5eqrwbjd09f01q.constant(target)
            }, target, logger);
          });
        });
      },
      triggerEscape: function (comp, simulatedEvent) {
        systemApi.triggerEvent('keydown', comp.element(), simulatedEvent.event());
      },
      getByUid: function (uid) {
        return getByUid(uid);
      },
      getByDom: function (elem) {
        return getByDom(elem);
      },
      build: $_60hy6b12kjd09f0v5.build,
      addToGui: function (c) {
        add(c);
      },
      removeFromGui: function (c) {
        remove(c);
      },
      addToWorld: function (c) {
        addToWorld(c);
      },
      removeFromWorld: function (c) {
        removeFromWorld(c);
      },
      broadcast: function (message) {
        broadcast(message);
      },
      broadcastOn: function (channels, message) {
        broadcastOn(channels, message);
      }
    });
    var addToWorld = function (component) {
      component.connect(systemApi);
      if (!$_arrpm2xxjd09f07k.isText(component.element())) {
        registry.register(component);
        $_bvikd2w9jd09f01c.each(component.components(), addToWorld);
        systemApi.triggerEvent($_5iytewwjd09f03c.systemInit(), component.element(), { target: $_8z5eqrwbjd09f01q.constant(component.element()) });
      }
    };
    var removeFromWorld = function (component) {
      if (!$_arrpm2xxjd09f07k.isText(component.element())) {
        $_bvikd2w9jd09f01c.each(component.components(), removeFromWorld);
        registry.unregister(component);
      }
      component.disconnect();
    };
    var add = function (component) {
      $_crdx3oy1jd09f07s.attach(root, component);
    };
    var remove = function (component) {
      $_crdx3oy1jd09f07s.detach(component);
    };
    var destroy = function () {
      domEvents.unbind();
      $_72wxi1y5jd09f08k.remove(root.element());
    };
    var broadcastData = function (data) {
      var receivers = registry.filter($_5iytewwjd09f03c.receive());
      $_bvikd2w9jd09f01c.each(receivers, function (receiver) {
        var descHandler = receiver.descHandler();
        var handler = $_jmzzy12vjd09f0xk.getHandler(descHandler);
        handler(data);
      });
    };
    var broadcast = function (message) {
      broadcastData({
        universal: $_8z5eqrwbjd09f01q.constant(true),
        data: $_8z5eqrwbjd09f01q.constant(message)
      });
    };
    var broadcastOn = function (channels, message) {
      broadcastData({
        universal: $_8z5eqrwbjd09f01q.constant(false),
        channels: $_8z5eqrwbjd09f01q.constant(channels),
        data: $_8z5eqrwbjd09f01q.constant(message)
      });
    };
    var getByUid = function (uid) {
      return registry.getById(uid).fold(function () {
        return $_anstx7x8jd09f05b.error(new Error('Could not find component with uid: "' + uid + '" in system.'));
      }, $_anstx7x8jd09f05b.value);
    };
    var getByDom = function (elem) {
      return $_69difv10mjd09f0kv.read(elem).bind(getByUid);
    };
    addToWorld(root);
    return {
      root: $_8z5eqrwbjd09f01q.constant(root),
      element: root.element,
      destroy: destroy,
      add: add,
      remove: remove,
      getByUid: getByUid,
      getByDom: getByDom,
      addToWorld: addToWorld,
      removeFromWorld: removeFromWorld,
      broadcast: broadcast,
      broadcastOn: broadcastOn
    };
  };
  var $_a00rtk14zjd09f198 = {
    create: create$6,
    takeover: takeover
  };

  var READ_ONLY_MODE_CLASS = $_8z5eqrwbjd09f01q.constant($_3584u7z1jd09f0bs.resolve('readonly-mode'));
  var EDIT_MODE_CLASS = $_8z5eqrwbjd09f01q.constant($_3584u7z1jd09f0bs.resolve('edit-mode'));
  function OuterContainer (spec) {
    var root = $_60hy6b12kjd09f0v5.build(Container.sketch({
      dom: { classes: [$_3584u7z1jd09f0bs.resolve('outer-container')].concat(spec.classes) },
      containerBehaviours: $_fq8al5w4jd09f00e.derive([Swapping.config({
          alpha: READ_ONLY_MODE_CLASS(),
          omega: EDIT_MODE_CLASS()
        })])
    }));
    return $_a00rtk14zjd09f198.takeover(root);
  }

  function AndroidRealm (scrollIntoView) {
    var alloy = OuterContainer({ classes: [$_3584u7z1jd09f0bs.resolve('android-container')] });
    var toolbar = ScrollingToolbar();
    var webapp = $_eq1tgp12ajd09f0tb.api();
    var switchToEdit = $_2ba61a14rjd09f17w.makeEditSwitch(webapp);
    var socket = $_2ba61a14rjd09f17w.makeSocket();
    var dropup = $_f797mu14sjd09f182.build($_8z5eqrwbjd09f01q.noop, scrollIntoView);
    alloy.add(toolbar.wrapper());
    alloy.add(socket);
    alloy.add(dropup.component());
    var setToolbarGroups = function (rawGroups) {
      var groups = toolbar.createGroups(rawGroups);
      toolbar.setGroups(groups);
    };
    var setContextToolbar = function (rawGroups) {
      var groups = toolbar.createGroups(rawGroups);
      toolbar.setContextToolbar(groups);
    };
    var focusToolbar = function () {
      toolbar.focus();
    };
    var restoreToolbar = function () {
      toolbar.restoreToolbar();
    };
    var init = function (spec) {
      webapp.set($_gcpus113njd09f125.produce(spec));
    };
    var exit = function () {
      webapp.run(function (w) {
        w.exit();
        Replacing.remove(socket, switchToEdit);
      });
    };
    var updateMode = function (readOnly) {
      $_2ba61a14rjd09f17w.updateMode(socket, switchToEdit, readOnly, alloy.root());
    };
    return {
      system: $_8z5eqrwbjd09f01q.constant(alloy),
      element: alloy.element,
      init: init,
      exit: exit,
      setToolbarGroups: setToolbarGroups,
      setContextToolbar: setContextToolbar,
      focusToolbar: focusToolbar,
      restoreToolbar: restoreToolbar,
      updateMode: updateMode,
      socket: $_8z5eqrwbjd09f01q.constant(socket),
      dropup: $_8z5eqrwbjd09f01q.constant(dropup)
    };
  }

  var initEvents$1 = function (editorApi, iosApi, toolstrip, socket, dropup) {
    var saveSelectionFirst = function () {
      iosApi.run(function (api) {
        api.highlightSelection();
      });
    };
    var refreshIosSelection = function () {
      iosApi.run(function (api) {
        api.refreshSelection();
      });
    };
    var scrollToY = function (yTop, height) {
      var y = yTop - socket.dom().scrollTop;
      iosApi.run(function (api) {
        api.scrollIntoView(y, y + height);
      });
    };
    var scrollToElement = function (target) {
      scrollToY(iosApi, socket);
    };
    var scrollToCursor = function () {
      editorApi.getCursorBox().each(function (box) {
        scrollToY(box.top(), box.height());
      });
    };
    var clearSelection = function () {
      iosApi.run(function (api) {
        api.clearSelection();
      });
    };
    var clearAndRefresh = function () {
      clearSelection();
      refreshThrottle.throttle();
    };
    var refreshView = function () {
      scrollToCursor();
      iosApi.run(function (api) {
        api.syncHeight();
      });
    };
    var reposition = function () {
      var toolbarHeight = $_52xa2jzrjd09f0eh.get(toolstrip);
      iosApi.run(function (api) {
        api.setViewportOffset(toolbarHeight);
      });
      refreshIosSelection();
      refreshView();
    };
    var toEditing = function () {
      iosApi.run(function (api) {
        api.toEditing();
      });
    };
    var toReading = function () {
      iosApi.run(function (api) {
        api.toReading();
      });
    };
    var onToolbarTouch = function (event) {
      iosApi.run(function (api) {
        api.onToolbarTouch(event);
      });
    };
    var tapping = $_1deljb13qjd09f12s.monitor(editorApi);
    var refreshThrottle = $_9g2t2114kjd09f16p.last(refreshView, 300);
    var listeners = [
      editorApi.onKeyup(clearAndRefresh),
      editorApi.onNodeChanged(refreshIosSelection),
      editorApi.onDomChanged(refreshThrottle.throttle),
      editorApi.onDomChanged(refreshIosSelection),
      editorApi.onScrollToCursor(function (tinyEvent) {
        tinyEvent.preventDefault();
        refreshThrottle.throttle();
      }),
      editorApi.onScrollToElement(function (event) {
        scrollToElement(event.element());
      }),
      editorApi.onToEditing(toEditing),
      editorApi.onToReading(toReading),
      $_5k3ae313kjd09f11v.bind(editorApi.doc(), 'touchend', function (touchEvent) {
        if ($_6s6cs1w8jd09f014.eq(editorApi.html(), touchEvent.target()) || $_6s6cs1w8jd09f014.eq(editorApi.body(), touchEvent.target())) {
        }
      }),
      $_5k3ae313kjd09f11v.bind(toolstrip, 'transitionend', function (transitionEvent) {
        if (transitionEvent.raw().propertyName === 'height') {
          reposition();
        }
      }),
      $_5k3ae313kjd09f11v.capture(toolstrip, 'touchstart', function (touchEvent) {
        saveSelectionFirst();
        onToolbarTouch(touchEvent);
        editorApi.onTouchToolstrip();
      }),
      $_5k3ae313kjd09f11v.bind(editorApi.body(), 'touchstart', function (evt) {
        clearSelection();
        editorApi.onTouchContent();
        tapping.fireTouchstart(evt);
      }),
      tapping.onTouchmove(),
      tapping.onTouchend(),
      $_5k3ae313kjd09f11v.bind(editorApi.body(), 'click', function (event) {
        event.kill();
      }),
      $_5k3ae313kjd09f11v.bind(toolstrip, 'touchmove', function () {
        editorApi.onToolbarScrollStart();
      })
    ];
    var destroy = function () {
      $_bvikd2w9jd09f01c.each(listeners, function (l) {
        l.unbind();
      });
    };
    return { destroy: destroy };
  };
  var $_7l3hw15ajd09f1bi = { initEvents: initEvents$1 };

  var refreshInput = function (input) {
    var start = input.dom().selectionStart;
    var end = input.dom().selectionEnd;
    var dir = input.dom().selectionDirection;
    setTimeout(function () {
      input.dom().setSelectionRange(start, end, dir);
      $_ccs2jvygjd09f09f.focus(input);
    }, 50);
  };
  var refresh = function (winScope) {
    var sel = winScope.getSelection();
    if (sel.rangeCount > 0) {
      var br = sel.getRangeAt(0);
      var r = winScope.document.createRange();
      r.setStart(br.startContainer, br.startOffset);
      r.setEnd(br.endContainer, br.endOffset);
      sel.removeAllRanges();
      sel.addRange(r);
    }
  };
  var $_c5z93t15ejd09f1cl = {
    refreshInput: refreshInput,
    refresh: refresh
  };

  var resume$1 = function (cWin, frame) {
    $_ccs2jvygjd09f09f.active().each(function (active) {
      if (!$_6s6cs1w8jd09f014.eq(active, frame)) {
        $_ccs2jvygjd09f09f.blur(active);
      }
    });
    cWin.focus();
    $_ccs2jvygjd09f09f.focus($_cnbf2uwtjd09f033.fromDom(cWin.document.body));
    $_c5z93t15ejd09f1cl.refresh(cWin);
  };
  var $_5kld7615djd09f1ch = { resume: resume$1 };

  function FakeSelection (win, frame) {
    var doc = win.document;
    var container = $_cnbf2uwtjd09f033.fromTag('div');
    $_e7d6ttxujd09f07b.add(container, $_3584u7z1jd09f0bs.resolve('unfocused-selections'));
    $_3dpbyky2jd09f081.append($_cnbf2uwtjd09f033.fromDom(doc.documentElement), container);
    var onTouch = $_5k3ae313kjd09f11v.bind(container, 'touchstart', function (event) {
      event.prevent();
      $_5kld7615djd09f1ch.resume(win, frame);
      clear();
    });
    var make = function (rectangle) {
      var span = $_cnbf2uwtjd09f033.fromTag('span');
      $_92oj9712yjd09f0yc.add(span, [
        $_3584u7z1jd09f0bs.resolve('layer-editor'),
        $_3584u7z1jd09f0bs.resolve('unfocused-selection')
      ]);
      $_a9ctnkzsjd09f0ej.setAll(span, {
        left: rectangle.left() + 'px',
        top: rectangle.top() + 'px',
        width: rectangle.width() + 'px',
        height: rectangle.height() + 'px'
      });
      return span;
    };
    var update = function () {
      clear();
      var rectangles = $_3p64o613wjd09f13q.getRectangles(win);
      var spans = $_bvikd2w9jd09f01c.map(rectangles, make);
      $_9afv6jy6jd09f08m.append(container, spans);
    };
    var clear = function () {
      $_72wxi1y5jd09f08k.empty(container);
    };
    var destroy = function () {
      onTouch.unbind();
      $_72wxi1y5jd09f08k.remove(container);
    };
    var isActive = function () {
      return $_sdd74y3jd09f08a.children(container).length > 0;
    };
    return {
      update: update,
      isActive: isActive,
      destroy: destroy,
      clear: clear
    };
  }

  var nu$8 = function (baseFn) {
    var data = $_8nwhzlwajd09f01m.none();
    var callbacks = [];
    var map = function (f) {
      return nu$8(function (nCallback) {
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
      data = $_8nwhzlwajd09f01m.some(x);
      run(callbacks);
      callbacks = [];
    };
    var isReady = function () {
      return data.isSome();
    };
    var run = function (cbs) {
      $_bvikd2w9jd09f01c.each(cbs, call);
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
    return nu$8(function (callback) {
      callback(a);
    });
  };
  var $_a780lr15hjd09f1cw = {
    nu: nu$8,
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
  var $_7yw8j015ijd09f1cx = { bounce: bounce };

  var nu$9 = function (baseFn) {
    var get = function (callback) {
      baseFn($_7yw8j015ijd09f1cx.bounce(callback));
    };
    var map = function (fab) {
      return nu$9(function (callback) {
        get(function (a) {
          var value = fab(a);
          callback(value);
        });
      });
    };
    var bind = function (aFutureB) {
      return nu$9(function (callback) {
        get(function (a) {
          aFutureB(a).get(callback);
        });
      });
    };
    var anonBind = function (futureB) {
      return nu$9(function (callback) {
        get(function (a) {
          futureB.get(callback);
        });
      });
    };
    var toLazy = function () {
      return $_a780lr15hjd09f1cw.nu(get);
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
    return nu$9(function (callback) {
      callback(a);
    });
  };
  var $_xnne215gjd09f1cu = {
    nu: nu$9,
    pure: pure$2
  };

  var adjust = function (value, destination, amount) {
    if (Math.abs(value - destination) <= amount) {
      return $_8nwhzlwajd09f01m.none();
    } else if (value < destination) {
      return $_8nwhzlwajd09f01m.some(value + amount);
    } else {
      return $_8nwhzlwajd09f01m.some(value - amount);
    }
  };
  var create$7 = function () {
    var interval = null;
    var animate = function (getCurrent, destination, amount, increment, doFinish, rate) {
      var finished = false;
      var finish = function (v) {
        finished = true;
        doFinish(v);
      };
      clearInterval(interval);
      var abort = function (v) {
        clearInterval(interval);
        finish(v);
      };
      interval = setInterval(function () {
        var value = getCurrent();
        adjust(value, destination, amount).fold(function () {
          clearInterval(interval);
          finish(destination);
        }, function (s) {
          increment(s, abort);
          if (!finished) {
            var newValue = getCurrent();
            if (newValue !== s || Math.abs(newValue - destination) > Math.abs(value - destination)) {
              clearInterval(interval);
              finish(destination);
            }
          }
        });
      }, rate);
    };
    return { animate: animate };
  };
  var $_8yd5c615jjd09f1cz = {
    create: create$7,
    adjust: adjust
  };

  var findDevice = function (deviceWidth, deviceHeight) {
    var devices = [
      {
        width: 320,
        height: 480,
        keyboard: {
          portrait: 300,
          landscape: 240
        }
      },
      {
        width: 320,
        height: 568,
        keyboard: {
          portrait: 300,
          landscape: 240
        }
      },
      {
        width: 375,
        height: 667,
        keyboard: {
          portrait: 305,
          landscape: 240
        }
      },
      {
        width: 414,
        height: 736,
        keyboard: {
          portrait: 320,
          landscape: 240
        }
      },
      {
        width: 768,
        height: 1024,
        keyboard: {
          portrait: 320,
          landscape: 400
        }
      },
      {
        width: 1024,
        height: 1366,
        keyboard: {
          portrait: 380,
          landscape: 460
        }
      }
    ];
    return $_851ithyejd09f09c.findMap(devices, function (device) {
      return deviceWidth <= device.width && deviceHeight <= device.height ? $_8nwhzlwajd09f01m.some(device.keyboard) : $_8nwhzlwajd09f01m.none();
    }).getOr({
      portrait: deviceHeight / 5,
      landscape: deviceWidth / 4
    });
  };
  var $_6mtse515mjd09f1dh = { findDevice: findDevice };

  var softKeyboardLimits = function (outerWindow) {
    return $_6mtse515mjd09f1dh.findDevice(outerWindow.screen.width, outerWindow.screen.height);
  };
  var accountableKeyboardHeight = function (outerWindow) {
    var portrait = $_4vd4wl13jjd09f11p.get(outerWindow).isPortrait();
    var limits = softKeyboardLimits(outerWindow);
    var keyboard = portrait ? limits.portrait : limits.landscape;
    var visualScreenHeight = portrait ? outerWindow.screen.height : outerWindow.screen.width;
    return visualScreenHeight - outerWindow.innerHeight > keyboard ? 0 : keyboard;
  };
  var getGreenzone = function (socket, dropup) {
    var outerWindow = $_sdd74y3jd09f08a.owner(socket).dom().defaultView;
    var viewportHeight = $_52xa2jzrjd09f0eh.get(socket) + $_52xa2jzrjd09f0eh.get(dropup);
    var acc = accountableKeyboardHeight(outerWindow);
    return viewportHeight - acc;
  };
  var updatePadding = function (contentBody, socket, dropup) {
    var greenzoneHeight = getGreenzone(socket, dropup);
    var deltaHeight = $_52xa2jzrjd09f0eh.get(socket) + $_52xa2jzrjd09f0eh.get(dropup) - greenzoneHeight;
    $_a9ctnkzsjd09f0ej.set(contentBody, 'padding-bottom', deltaHeight + 'px');
  };
  var $_3rmf6j15ljd09f1dd = {
    getGreenzone: getGreenzone,
    updatePadding: updatePadding
  };

  var fixture = $_enqz8zx4jd09f04j.generate([
    {
      fixed: [
        'element',
        'property',
        'offsetY'
      ]
    },
    {
      scroller: [
        'element',
        'offsetY'
      ]
    }
  ]);
  var yFixedData = 'data-' + $_3584u7z1jd09f0bs.resolve('position-y-fixed');
  var yFixedProperty = 'data-' + $_3584u7z1jd09f0bs.resolve('y-property');
  var yScrollingData = 'data-' + $_3584u7z1jd09f0bs.resolve('scrolling');
  var windowSizeData = 'data-' + $_3584u7z1jd09f0bs.resolve('last-window-height');
  var getYFixedData = function (element) {
    return $_43tcqw13vjd09f13o.safeParse(element, yFixedData);
  };
  var getYFixedProperty = function (element) {
    return $_6spjcmxwjd09f07e.get(element, yFixedProperty);
  };
  var getLastWindowSize = function (element) {
    return $_43tcqw13vjd09f13o.safeParse(element, windowSizeData);
  };
  var classifyFixed = function (element, offsetY) {
    var prop = getYFixedProperty(element);
    return fixture.fixed(element, prop, offsetY);
  };
  var classifyScrolling = function (element, offsetY) {
    return fixture.scroller(element, offsetY);
  };
  var classify = function (element) {
    var offsetY = getYFixedData(element);
    var classifier = $_6spjcmxwjd09f07e.get(element, yScrollingData) === 'true' ? classifyScrolling : classifyFixed;
    return classifier(element, offsetY);
  };
  var findFixtures = function (container) {
    var candidates = $_23mimrzkjd09f0dz.descendants(container, '[' + yFixedData + ']');
    return $_bvikd2w9jd09f01c.map(candidates, classify);
  };
  var takeoverToolbar = function (toolbar) {
    var oldToolbarStyle = $_6spjcmxwjd09f07e.get(toolbar, 'style');
    $_a9ctnkzsjd09f0ej.setAll(toolbar, {
      position: 'absolute',
      top: '0px'
    });
    $_6spjcmxwjd09f07e.set(toolbar, yFixedData, '0px');
    $_6spjcmxwjd09f07e.set(toolbar, yFixedProperty, 'top');
    var restore = function () {
      $_6spjcmxwjd09f07e.set(toolbar, 'style', oldToolbarStyle || '');
      $_6spjcmxwjd09f07e.remove(toolbar, yFixedData);
      $_6spjcmxwjd09f07e.remove(toolbar, yFixedProperty);
    };
    return { restore: restore };
  };
  var takeoverViewport = function (toolbarHeight, height, viewport) {
    var oldViewportStyle = $_6spjcmxwjd09f07e.get(viewport, 'style');
    $_1p83kb13hjd09f11i.register(viewport);
    $_a9ctnkzsjd09f0ej.setAll(viewport, {
      position: 'absolute',
      height: height + 'px',
      width: '100%',
      top: toolbarHeight + 'px'
    });
    $_6spjcmxwjd09f07e.set(viewport, yFixedData, toolbarHeight + 'px');
    $_6spjcmxwjd09f07e.set(viewport, yScrollingData, 'true');
    $_6spjcmxwjd09f07e.set(viewport, yFixedProperty, 'top');
    var restore = function () {
      $_1p83kb13hjd09f11i.deregister(viewport);
      $_6spjcmxwjd09f07e.set(viewport, 'style', oldViewportStyle || '');
      $_6spjcmxwjd09f07e.remove(viewport, yFixedData);
      $_6spjcmxwjd09f07e.remove(viewport, yScrollingData);
      $_6spjcmxwjd09f07e.remove(viewport, yFixedProperty);
    };
    return { restore: restore };
  };
  var takeoverDropup = function (dropup, toolbarHeight, viewportHeight) {
    var oldDropupStyle = $_6spjcmxwjd09f07e.get(dropup, 'style');
    $_a9ctnkzsjd09f0ej.setAll(dropup, {
      position: 'absolute',
      bottom: '0px'
    });
    $_6spjcmxwjd09f07e.set(dropup, yFixedData, '0px');
    $_6spjcmxwjd09f07e.set(dropup, yFixedProperty, 'bottom');
    var restore = function () {
      $_6spjcmxwjd09f07e.set(dropup, 'style', oldDropupStyle || '');
      $_6spjcmxwjd09f07e.remove(dropup, yFixedData);
      $_6spjcmxwjd09f07e.remove(dropup, yFixedProperty);
    };
    return { restore: restore };
  };
  var deriveViewportHeight = function (viewport, toolbarHeight, dropupHeight) {
    var outerWindow = $_sdd74y3jd09f08a.owner(viewport).dom().defaultView;
    var winH = outerWindow.innerHeight;
    $_6spjcmxwjd09f07e.set(viewport, windowSizeData, winH + 'px');
    return winH - toolbarHeight - dropupHeight;
  };
  var takeover$1 = function (viewport, contentBody, toolbar, dropup) {
    var outerWindow = $_sdd74y3jd09f08a.owner(viewport).dom().defaultView;
    var toolbarSetup = takeoverToolbar(toolbar);
    var toolbarHeight = $_52xa2jzrjd09f0eh.get(toolbar);
    var dropupHeight = $_52xa2jzrjd09f0eh.get(dropup);
    var viewportHeight = deriveViewportHeight(viewport, toolbarHeight, dropupHeight);
    var viewportSetup = takeoverViewport(toolbarHeight, viewportHeight, viewport);
    var dropupSetup = takeoverDropup(dropup, toolbarHeight, viewportHeight);
    var isActive = true;
    var restore = function () {
      isActive = false;
      toolbarSetup.restore();
      viewportSetup.restore();
      dropupSetup.restore();
    };
    var isExpanding = function () {
      var currentWinHeight = outerWindow.innerHeight;
      var lastWinHeight = getLastWindowSize(viewport);
      return currentWinHeight > lastWinHeight;
    };
    var refresh = function () {
      if (isActive) {
        var newToolbarHeight = $_52xa2jzrjd09f0eh.get(toolbar);
        var dropupHeight_1 = $_52xa2jzrjd09f0eh.get(dropup);
        var newHeight = deriveViewportHeight(viewport, newToolbarHeight, dropupHeight_1);
        $_6spjcmxwjd09f07e.set(viewport, yFixedData, newToolbarHeight + 'px');
        $_a9ctnkzsjd09f0ej.set(viewport, 'height', newHeight + 'px');
        $_a9ctnkzsjd09f0ej.set(dropup, 'bottom', -(newToolbarHeight + newHeight + dropupHeight_1) + 'px');
        $_3rmf6j15ljd09f1dd.updatePadding(contentBody, viewport, dropup);
      }
    };
    var setViewportOffset = function (newYOffset) {
      var offsetPx = newYOffset + 'px';
      $_6spjcmxwjd09f07e.set(viewport, yFixedData, offsetPx);
      refresh();
    };
    $_3rmf6j15ljd09f1dd.updatePadding(contentBody, viewport, dropup);
    return {
      setViewportOffset: setViewportOffset,
      isExpanding: isExpanding,
      isShrinking: $_8z5eqrwbjd09f01q.not(isExpanding),
      refresh: refresh,
      restore: restore
    };
  };
  var $_ak0p2a15kjd09f1d2 = {
    findFixtures: findFixtures,
    takeover: takeover$1,
    getYFixedData: getYFixedData
  };

  var animator = $_8yd5c615jjd09f1cz.create();
  var ANIMATION_STEP = 15;
  var NUM_TOP_ANIMATION_FRAMES = 10;
  var ANIMATION_RATE = 10;
  var lastScroll = 'data-' + $_3584u7z1jd09f0bs.resolve('last-scroll-top');
  var getTop = function (element) {
    var raw = $_a9ctnkzsjd09f0ej.getRaw(element, 'top').getOr(0);
    return parseInt(raw, 10);
  };
  var getScrollTop = function (element) {
    return parseInt(element.dom().scrollTop, 10);
  };
  var moveScrollAndTop = function (element, destination, finalTop) {
    return $_xnne215gjd09f1cu.nu(function (callback) {
      var getCurrent = $_8z5eqrwbjd09f01q.curry(getScrollTop, element);
      var update = function (newScroll) {
        element.dom().scrollTop = newScroll;
        $_a9ctnkzsjd09f0ej.set(element, 'top', getTop(element) + ANIMATION_STEP + 'px');
      };
      var finish = function () {
        element.dom().scrollTop = destination;
        $_a9ctnkzsjd09f0ej.set(element, 'top', finalTop + 'px');
        callback(destination);
      };
      animator.animate(getCurrent, destination, ANIMATION_STEP, update, finish, ANIMATION_RATE);
    });
  };
  var moveOnlyScroll = function (element, destination) {
    return $_xnne215gjd09f1cu.nu(function (callback) {
      var getCurrent = $_8z5eqrwbjd09f01q.curry(getScrollTop, element);
      $_6spjcmxwjd09f07e.set(element, lastScroll, getCurrent());
      var update = function (newScroll, abort) {
        var previous = $_43tcqw13vjd09f13o.safeParse(element, lastScroll);
        if (previous !== element.dom().scrollTop) {
          abort(element.dom().scrollTop);
        } else {
          element.dom().scrollTop = newScroll;
          $_6spjcmxwjd09f07e.set(element, lastScroll, newScroll);
        }
      };
      var finish = function () {
        element.dom().scrollTop = destination;
        $_6spjcmxwjd09f07e.set(element, lastScroll, destination);
        callback(destination);
      };
      var distance = Math.abs(destination - getCurrent());
      var step = Math.ceil(distance / NUM_TOP_ANIMATION_FRAMES);
      animator.animate(getCurrent, destination, step, update, finish, ANIMATION_RATE);
    });
  };
  var moveOnlyTop = function (element, destination) {
    return $_xnne215gjd09f1cu.nu(function (callback) {
      var getCurrent = $_8z5eqrwbjd09f01q.curry(getTop, element);
      var update = function (newTop) {
        $_a9ctnkzsjd09f0ej.set(element, 'top', newTop + 'px');
      };
      var finish = function () {
        update(destination);
        callback(destination);
      };
      var distance = Math.abs(destination - getCurrent());
      var step = Math.ceil(distance / NUM_TOP_ANIMATION_FRAMES);
      animator.animate(getCurrent, destination, step, update, finish, ANIMATION_RATE);
    });
  };
  var updateTop = function (element, amount) {
    var newTop = amount + $_ak0p2a15kjd09f1d2.getYFixedData(element) + 'px';
    $_a9ctnkzsjd09f0ej.set(element, 'top', newTop);
  };
  var moveWindowScroll = function (toolbar, viewport, destY) {
    var outerWindow = $_sdd74y3jd09f08a.owner(toolbar).dom().defaultView;
    return $_xnne215gjd09f1cu.nu(function (callback) {
      updateTop(toolbar, destY);
      updateTop(viewport, destY);
      outerWindow.scrollTo(0, destY);
      callback(destY);
    });
  };
  var $_1hbql015fjd09f1co = {
    moveScrollAndTop: moveScrollAndTop,
    moveOnlyScroll: moveOnlyScroll,
    moveOnlyTop: moveOnlyTop,
    moveWindowScroll: moveWindowScroll
  };

  function BackgroundActivity (doAction) {
    var action = Cell($_a780lr15hjd09f1cw.pure({}));
    var start = function (value) {
      var future = $_a780lr15hjd09f1cw.nu(function (callback) {
        return doAction(value).get(callback);
      });
      action.set(future);
    };
    var idle = function (g) {
      action.get().get(function () {
        g();
      });
    };
    return {
      start: start,
      idle: idle
    };
  }

  var scrollIntoView = function (cWin, socket, dropup, top, bottom) {
    var greenzone = $_3rmf6j15ljd09f1dd.getGreenzone(socket, dropup);
    var refreshCursor = $_8z5eqrwbjd09f01q.curry($_c5z93t15ejd09f1cl.refresh, cWin);
    if (top > greenzone || bottom > greenzone) {
      $_1hbql015fjd09f1co.moveOnlyScroll(socket, socket.dom().scrollTop - greenzone + bottom).get(refreshCursor);
    } else if (top < 0) {
      $_1hbql015fjd09f1co.moveOnlyScroll(socket, socket.dom().scrollTop + top).get(refreshCursor);
    } else {
    }
  };
  var $_39q2a515ojd09f1do = { scrollIntoView: scrollIntoView };

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
        $_bvikd2w9jd09f01c.each(asyncValues, function (asyncValue, i) {
          asyncValue.get(cb(i));
        });
      }
    });
  };
  var $_8mv3hq15rjd09f1dy = { par: par };

  var par$1 = function (futures) {
    return $_8mv3hq15rjd09f1dy.par(futures, $_xnne215gjd09f1cu.nu);
  };
  var mapM = function (array, fn) {
    var futures = $_bvikd2w9jd09f01c.map(array, fn);
    return par$1(futures);
  };
  var compose$1 = function (f, g) {
    return function (a) {
      return g(a).bind(f);
    };
  };
  var $_f2odd115qjd09f1dx = {
    par: par$1,
    mapM: mapM,
    compose: compose$1
  };

  var updateFixed = function (element, property, winY, offsetY) {
    var destination = winY + offsetY;
    $_a9ctnkzsjd09f0ej.set(element, property, destination + 'px');
    return $_xnne215gjd09f1cu.pure(offsetY);
  };
  var updateScrollingFixed = function (element, winY, offsetY) {
    var destTop = winY + offsetY;
    var oldProp = $_a9ctnkzsjd09f0ej.getRaw(element, 'top').getOr(offsetY);
    var delta = destTop - parseInt(oldProp, 10);
    var destScroll = element.dom().scrollTop + delta;
    return $_1hbql015fjd09f1co.moveScrollAndTop(element, destScroll, destTop);
  };
  var updateFixture = function (fixture, winY) {
    return fixture.fold(function (element, property, offsetY) {
      return updateFixed(element, property, winY, offsetY);
    }, function (element, offsetY) {
      return updateScrollingFixed(element, winY, offsetY);
    });
  };
  var updatePositions = function (container, winY) {
    var fixtures = $_ak0p2a15kjd09f1d2.findFixtures(container);
    var updates = $_bvikd2w9jd09f01c.map(fixtures, function (fixture) {
      return updateFixture(fixture, winY);
    });
    return $_f2odd115qjd09f1dx.par(updates);
  };
  var $_2jp4fh15pjd09f1dr = { updatePositions: updatePositions };

  var input = function (parent, operation) {
    var input = $_cnbf2uwtjd09f033.fromTag('input');
    $_a9ctnkzsjd09f0ej.setAll(input, {
      opacity: '0',
      position: 'absolute',
      top: '-1000px',
      left: '-1000px'
    });
    $_3dpbyky2jd09f081.append(parent, input);
    $_ccs2jvygjd09f09f.focus(input);
    operation(input);
    $_72wxi1y5jd09f08k.remove(input);
  };
  var $_268lxt15sjd09f1e1 = { input: input };

  var VIEW_MARGIN = 5;
  var register$2 = function (toolstrip, socket, container, outerWindow, structure, cWin) {
    var scroller = BackgroundActivity(function (y) {
      return $_1hbql015fjd09f1co.moveWindowScroll(toolstrip, socket, y);
    });
    var scrollBounds = function () {
      var rects = $_3p64o613wjd09f13q.getRectangles(cWin);
      return $_8nwhzlwajd09f01m.from(rects[0]).bind(function (rect) {
        var viewTop = rect.top() - socket.dom().scrollTop;
        var outside = viewTop > outerWindow.innerHeight + VIEW_MARGIN || viewTop < -VIEW_MARGIN;
        return outside ? $_8nwhzlwajd09f01m.some({
          top: $_8z5eqrwbjd09f01q.constant(viewTop),
          bottom: $_8z5eqrwbjd09f01q.constant(viewTop + rect.height())
        }) : $_8nwhzlwajd09f01m.none();
      });
    };
    var scrollThrottle = $_9g2t2114kjd09f16p.last(function () {
      scroller.idle(function () {
        $_2jp4fh15pjd09f1dr.updatePositions(container, outerWindow.pageYOffset).get(function () {
          var extraScroll = scrollBounds();
          extraScroll.each(function (extra) {
            socket.dom().scrollTop = socket.dom().scrollTop + extra.top();
          });
          scroller.start(0);
          structure.refresh();
        });
      });
    }, 1000);
    var onScroll = $_5k3ae313kjd09f11v.bind($_cnbf2uwtjd09f033.fromDom(outerWindow), 'scroll', function () {
      if (outerWindow.pageYOffset < 0) {
        return;
      }
      scrollThrottle.throttle();
    });
    $_2jp4fh15pjd09f1dr.updatePositions(container, outerWindow.pageYOffset).get($_8z5eqrwbjd09f01q.identity);
    return { unbind: onScroll.unbind };
  };
  var setup$3 = function (bag) {
    var cWin = bag.cWin();
    var ceBody = bag.ceBody();
    var socket = bag.socket();
    var toolstrip = bag.toolstrip();
    var toolbar = bag.toolbar();
    var contentElement = bag.contentElement();
    var keyboardType = bag.keyboardType();
    var outerWindow = bag.outerWindow();
    var dropup = bag.dropup();
    var structure = $_ak0p2a15kjd09f1d2.takeover(socket, ceBody, toolstrip, dropup);
    var keyboardModel = keyboardType(bag.outerBody(), cWin, $_7m8dypy7jd09f08o.body(), contentElement, toolstrip, toolbar);
    var toEditing = function () {
      keyboardModel.toEditing();
      clearSelection();
    };
    var toReading = function () {
      keyboardModel.toReading();
    };
    var onToolbarTouch = function (event) {
      keyboardModel.onToolbarTouch(event);
    };
    var onOrientation = $_4vd4wl13jjd09f11p.onChange(outerWindow, {
      onChange: $_8z5eqrwbjd09f01q.noop,
      onReady: structure.refresh
    });
    onOrientation.onAdjustment(function () {
      structure.refresh();
    });
    var onResize = $_5k3ae313kjd09f11v.bind($_cnbf2uwtjd09f033.fromDom(outerWindow), 'resize', function () {
      if (structure.isExpanding()) {
        structure.refresh();
      }
    });
    var onScroll = register$2(toolstrip, socket, bag.outerBody(), outerWindow, structure, cWin);
    var unfocusedSelection = FakeSelection(cWin, contentElement);
    var refreshSelection = function () {
      if (unfocusedSelection.isActive()) {
        unfocusedSelection.update();
      }
    };
    var highlightSelection = function () {
      unfocusedSelection.update();
    };
    var clearSelection = function () {
      unfocusedSelection.clear();
    };
    var scrollIntoView = function (top, bottom) {
      $_39q2a515ojd09f1do.scrollIntoView(cWin, socket, dropup, top, bottom);
    };
    var syncHeight = function () {
      $_a9ctnkzsjd09f0ej.set(contentElement, 'height', contentElement.dom().contentWindow.document.body.scrollHeight + 'px');
    };
    var setViewportOffset = function (newYOffset) {
      structure.setViewportOffset(newYOffset);
      $_1hbql015fjd09f1co.moveOnlyTop(socket, newYOffset).get($_8z5eqrwbjd09f01q.identity);
    };
    var destroy = function () {
      structure.restore();
      onOrientation.destroy();
      onScroll.unbind();
      onResize.unbind();
      keyboardModel.destroy();
      unfocusedSelection.destroy();
      $_268lxt15sjd09f1e1.input($_7m8dypy7jd09f08o.body(), $_ccs2jvygjd09f09f.blur);
    };
    return {
      toEditing: toEditing,
      toReading: toReading,
      onToolbarTouch: onToolbarTouch,
      refreshSelection: refreshSelection,
      clearSelection: clearSelection,
      highlightSelection: highlightSelection,
      scrollIntoView: scrollIntoView,
      updateToolbarPadding: $_8z5eqrwbjd09f01q.noop,
      setViewportOffset: setViewportOffset,
      syncHeight: syncHeight,
      refreshStructure: structure.refresh,
      destroy: destroy
    };
  };
  var $_ai9q0n15bjd09f1bq = { setup: setup$3 };

  var stubborn = function (outerBody, cWin, page, frame) {
    var toEditing = function () {
      $_5kld7615djd09f1ch.resume(cWin, frame);
    };
    var toReading = function () {
      $_268lxt15sjd09f1e1.input(outerBody, $_ccs2jvygjd09f09f.blur);
    };
    var captureInput = $_5k3ae313kjd09f11v.bind(page, 'keydown', function (evt) {
      if (!$_bvikd2w9jd09f01c.contains([
          'input',
          'textarea'
        ], $_arrpm2xxjd09f07k.name(evt.target()))) {
        toEditing();
      }
    });
    var onToolbarTouch = function () {
    };
    var destroy = function () {
      captureInput.unbind();
    };
    return {
      toReading: toReading,
      toEditing: toEditing,
      onToolbarTouch: onToolbarTouch,
      destroy: destroy
    };
  };
  var timid = function (outerBody, cWin, page, frame) {
    var dismissKeyboard = function () {
      $_ccs2jvygjd09f09f.blur(frame);
    };
    var onToolbarTouch = function () {
      dismissKeyboard();
    };
    var toReading = function () {
      dismissKeyboard();
    };
    var toEditing = function () {
      $_5kld7615djd09f1ch.resume(cWin, frame);
    };
    return {
      toReading: toReading,
      toEditing: toEditing,
      onToolbarTouch: onToolbarTouch,
      destroy: $_8z5eqrwbjd09f01q.noop
    };
  };
  var $_4b3g5115tjd09f1kl = {
    stubborn: stubborn,
    timid: timid
  };

  var create$8 = function (platform, mask) {
    var meta = $_jj7n114hjd09f168.tag();
    var priorState = $_eq1tgp12ajd09f0tb.value();
    var scrollEvents = $_eq1tgp12ajd09f0tb.value();
    var iosApi = $_eq1tgp12ajd09f0tb.api();
    var iosEvents = $_eq1tgp12ajd09f0tb.api();
    var enter = function () {
      mask.hide();
      var doc = $_cnbf2uwtjd09f033.fromDom(document);
      $_1ej5a814fjd09f15u.getActiveApi(platform.editor).each(function (editorApi) {
        priorState.set({
          socketHeight: $_a9ctnkzsjd09f0ej.getRaw(platform.socket, 'height'),
          iframeHeight: $_a9ctnkzsjd09f0ej.getRaw(editorApi.frame(), 'height'),
          outerScroll: document.body.scrollTop
        });
        scrollEvents.set({ exclusives: $_f0swo714qjd09f17q.exclusive(doc, '.' + $_1p83kb13hjd09f11i.scrollable()) });
        $_e7d6ttxujd09f07b.add(platform.container, $_3584u7z1jd09f0bs.resolve('fullscreen-maximized'));
        $_6pcgv014gjd09f162.clobberStyles(platform.container, editorApi.body());
        meta.maximize();
        $_a9ctnkzsjd09f0ej.set(platform.socket, 'overflow', 'scroll');
        $_a9ctnkzsjd09f0ej.set(platform.socket, '-webkit-overflow-scrolling', 'touch');
        $_ccs2jvygjd09f09f.focus(editorApi.body());
        var setupBag = $_gbd4xkxmjd09f06u.immutableBag([
          'cWin',
          'ceBody',
          'socket',
          'toolstrip',
          'toolbar',
          'dropup',
          'contentElement',
          'cursor',
          'keyboardType',
          'isScrolling',
          'outerWindow',
          'outerBody'
        ], []);
        iosApi.set($_ai9q0n15bjd09f1bq.setup(setupBag({
          cWin: editorApi.win(),
          ceBody: editorApi.body(),
          socket: platform.socket,
          toolstrip: platform.toolstrip,
          toolbar: platform.toolbar,
          dropup: platform.dropup.element(),
          contentElement: editorApi.frame(),
          cursor: $_8z5eqrwbjd09f01q.noop,
          outerBody: platform.body,
          outerWindow: platform.win,
          keyboardType: $_4b3g5115tjd09f1kl.stubborn,
          isScrolling: function () {
            return scrollEvents.get().exists(function (s) {
              return s.socket.isScrolling();
            });
          }
        })));
        iosApi.run(function (api) {
          api.syncHeight();
        });
        iosEvents.set($_7l3hw15ajd09f1bi.initEvents(editorApi, iosApi, platform.toolstrip, platform.socket, platform.dropup));
      });
    };
    var exit = function () {
      meta.restore();
      iosEvents.clear();
      iosApi.clear();
      mask.show();
      priorState.on(function (s) {
        s.socketHeight.each(function (h) {
          $_a9ctnkzsjd09f0ej.set(platform.socket, 'height', h);
        });
        s.iframeHeight.each(function (h) {
          $_a9ctnkzsjd09f0ej.set(platform.editor.getFrame(), 'height', h);
        });
        document.body.scrollTop = s.scrollTop;
      });
      priorState.clear();
      scrollEvents.on(function (s) {
        s.exclusives.unbind();
      });
      scrollEvents.clear();
      $_e7d6ttxujd09f07b.remove(platform.container, $_3584u7z1jd09f0bs.resolve('fullscreen-maximized'));
      $_6pcgv014gjd09f162.restoreStyles();
      $_1p83kb13hjd09f11i.deregister(platform.toolbar);
      $_a9ctnkzsjd09f0ej.remove(platform.socket, 'overflow');
      $_a9ctnkzsjd09f0ej.remove(platform.socket, '-webkit-overflow-scrolling');
      $_ccs2jvygjd09f09f.blur(platform.editor.getFrame());
      $_1ej5a814fjd09f15u.getActiveApi(platform.editor).each(function (editorApi) {
        editorApi.clearSelection();
      });
    };
    var refreshStructure = function () {
      iosApi.run(function (api) {
        api.refreshStructure();
      });
    };
    return {
      enter: enter,
      refreshStructure: refreshStructure,
      exit: exit
    };
  };
  var $_b4avkv159jd09f1b9 = { create: create$8 };

  var produce$1 = function (raw) {
    var mobile = $_33aoy2xhjd09f061.asRawOrDie('Getting IosWebapp schema', MobileSchema, raw);
    $_a9ctnkzsjd09f0ej.set(mobile.toolstrip, 'width', '100%');
    $_a9ctnkzsjd09f0ej.set(mobile.container, 'position', 'relative');
    var onView = function () {
      mobile.setReadOnly(true);
      mode.enter();
    };
    var mask = $_60hy6b12kjd09f0v5.build($_bk44n014jjd09f16i.sketch(onView, mobile.translate));
    mobile.alloy.add(mask);
    var maskApi = {
      show: function () {
        mobile.alloy.add(mask);
      },
      hide: function () {
        mobile.alloy.remove(mask);
      }
    };
    var mode = $_b4avkv159jd09f1b9.create(mobile, maskApi);
    return {
      setReadOnly: mobile.setReadOnly,
      refreshStructure: mode.refreshStructure,
      enter: mode.enter,
      exit: mode.exit,
      destroy: $_8z5eqrwbjd09f01q.noop
    };
  };
  var $_c6bj2x158jd09f1b4 = { produce: produce$1 };

  function IosRealm (scrollIntoView) {
    var alloy = OuterContainer({ classes: [$_3584u7z1jd09f0bs.resolve('ios-container')] });
    var toolbar = ScrollingToolbar();
    var webapp = $_eq1tgp12ajd09f0tb.api();
    var switchToEdit = $_2ba61a14rjd09f17w.makeEditSwitch(webapp);
    var socket = $_2ba61a14rjd09f17w.makeSocket();
    var dropup = $_f797mu14sjd09f182.build(function () {
      webapp.run(function (w) {
        w.refreshStructure();
      });
    }, scrollIntoView);
    alloy.add(toolbar.wrapper());
    alloy.add(socket);
    alloy.add(dropup.component());
    var setToolbarGroups = function (rawGroups) {
      var groups = toolbar.createGroups(rawGroups);
      toolbar.setGroups(groups);
    };
    var setContextToolbar = function (rawGroups) {
      var groups = toolbar.createGroups(rawGroups);
      toolbar.setContextToolbar(groups);
    };
    var focusToolbar = function () {
      toolbar.focus();
    };
    var restoreToolbar = function () {
      toolbar.restoreToolbar();
    };
    var init = function (spec) {
      webapp.set($_c6bj2x158jd09f1b4.produce(spec));
    };
    var exit = function () {
      webapp.run(function (w) {
        Replacing.remove(socket, switchToEdit);
        w.exit();
      });
    };
    var updateMode = function (readOnly) {
      $_2ba61a14rjd09f17w.updateMode(socket, switchToEdit, readOnly, alloy.root());
    };
    return {
      system: $_8z5eqrwbjd09f01q.constant(alloy),
      element: alloy.element,
      init: init,
      exit: exit,
      setToolbarGroups: setToolbarGroups,
      setContextToolbar: setContextToolbar,
      focusToolbar: focusToolbar,
      restoreToolbar: restoreToolbar,
      updateMode: updateMode,
      socket: $_8z5eqrwbjd09f01q.constant(socket),
      dropup: $_8z5eqrwbjd09f01q.constant(dropup)
    };
  }

  var EditorManager = tinymce.util.Tools.resolve('tinymce.EditorManager');

  var derive$4 = function (editor) {
    var base = $_q0etsx6jd09f053.readOptFrom(editor.settings, 'skin_url').fold(function () {
      return EditorManager.baseURL + '/skins/' + 'lightgray';
    }, function (url) {
      return url;
    });
    return {
      content: base + '/content.mobile.min.css',
      ui: base + '/skin.mobile.min.css'
    };
  };
  var $_bnv6gs15ujd09f1l2 = { derive: derive$4 };

  var fontSizes = [
    'x-small',
    'small',
    'medium',
    'large',
    'x-large'
  ];
  var fireChange$1 = function (realm, command, state) {
    realm.system().broadcastOn([$_b1cma0yojd09f09y.formatChanged()], {
      command: command,
      state: state
    });
  };
  var init$5 = function (realm, editor) {
    var allFormats = $_32a0zdx0jd09f03l.keys(editor.formatter.get());
    $_bvikd2w9jd09f01c.each(allFormats, function (command) {
      editor.formatter.formatChanged(command, function (state) {
        fireChange$1(realm, command, state);
      });
    });
    $_bvikd2w9jd09f01c.each([
      'ul',
      'ol'
    ], function (command) {
      editor.selection.selectorChanged(command, function (state, data) {
        fireChange$1(realm, command, state);
      });
    });
  };
  var $_48v0pi15wjd09f1l5 = {
    init: init$5,
    fontSizes: $_8z5eqrwbjd09f01q.constant(fontSizes)
  };

  var fireSkinLoaded = function (editor) {
    var done = function () {
      editor._skinLoaded = true;
      editor.fire('SkinLoaded');
    };
    return function () {
      if (editor.initialized) {
        done();
      } else {
        editor.on('init', done);
      }
    };
  };
  var $_9s7zjm15xjd09f1la = { fireSkinLoaded: fireSkinLoaded };

  var READING = $_8z5eqrwbjd09f01q.constant('toReading');
  var EDITING = $_8z5eqrwbjd09f01q.constant('toEditing');
  ThemeManager.add('mobile', function (editor) {
    var renderUI = function (args) {
      var cssUrls = $_bnv6gs15ujd09f1l2.derive(editor);
      if ($_99bfffynjd09f09x.isSkinDisabled(editor) === false) {
        editor.contentCSS.push(cssUrls.content);
        DOMUtils.DOM.styleSheetLoader.load(cssUrls.ui, $_9s7zjm15xjd09f1la.fireSkinLoaded(editor));
      } else {
        $_9s7zjm15xjd09f1la.fireSkinLoaded(editor)();
      }
      var doScrollIntoView = function () {
        editor.fire('scrollIntoView');
      };
      var wrapper = $_cnbf2uwtjd09f033.fromTag('div');
      var realm = $_8zynflwgjd09f01z.detect().os.isAndroid() ? AndroidRealm(doScrollIntoView) : IosRealm(doScrollIntoView);
      var original = $_cnbf2uwtjd09f033.fromDom(args.targetNode);
      $_3dpbyky2jd09f081.after(original, wrapper);
      $_crdx3oy1jd09f07s.attachSystem(wrapper, realm.system());
      var findFocusIn = function (elem) {
        return $_ccs2jvygjd09f09f.search(elem).bind(function (focused) {
          return realm.system().getByDom(focused).toOption();
        });
      };
      var outerWindow = args.targetNode.ownerDocument.defaultView;
      var orientation = $_4vd4wl13jjd09f11p.onChange(outerWindow, {
        onChange: function () {
          var alloy = realm.system();
          alloy.broadcastOn([$_b1cma0yojd09f09y.orientationChanged()], { width: $_4vd4wl13jjd09f11p.getActualWidth(outerWindow) });
        },
        onReady: $_8z5eqrwbjd09f01q.noop
      });
      var setReadOnly = function (readOnlyGroups, mainGroups, ro) {
        if (ro === false) {
          editor.selection.collapse();
        }
        realm.setToolbarGroups(ro ? readOnlyGroups.get() : mainGroups.get());
        editor.setMode(ro === true ? 'readonly' : 'design');
        editor.fire(ro === true ? READING() : EDITING());
        realm.updateMode(ro);
      };
      var bindHandler = function (label, handler) {
        editor.on(label, handler);
        return {
          unbind: function () {
            editor.off(label);
          }
        };
      };
      editor.on('init', function () {
        realm.init({
          editor: {
            getFrame: function () {
              return $_cnbf2uwtjd09f033.fromDom(editor.contentAreaContainer.querySelector('iframe'));
            },
            onDomChanged: function () {
              return { unbind: $_8z5eqrwbjd09f01q.noop };
            },
            onToReading: function (handler) {
              return bindHandler(READING(), handler);
            },
            onToEditing: function (handler) {
              return bindHandler(EDITING(), handler);
            },
            onScrollToCursor: function (handler) {
              editor.on('scrollIntoView', function (tinyEvent) {
                handler(tinyEvent);
              });
              var unbind = function () {
                editor.off('scrollIntoView');
                orientation.destroy();
              };
              return { unbind: unbind };
            },
            onTouchToolstrip: function () {
              hideDropup();
            },
            onTouchContent: function () {
              var toolbar = $_cnbf2uwtjd09f033.fromDom(editor.editorContainer.querySelector('.' + $_3584u7z1jd09f0bs.resolve('toolbar')));
              findFocusIn(toolbar).each($_3b6lb8wvjd09f037.emitExecute);
              realm.restoreToolbar();
              hideDropup();
            },
            onTapContent: function (evt) {
              var target = evt.target();
              if ($_arrpm2xxjd09f07k.name(target) === 'img') {
                editor.selection.select(target.dom());
                evt.kill();
              } else if ($_arrpm2xxjd09f07k.name(target) === 'a') {
                var component = realm.system().getByDom($_cnbf2uwtjd09f033.fromDom(editor.editorContainer));
                component.each(function (container) {
                  if (Swapping.isAlpha(container)) {
                    $_d5bc7symjd09f09w.openLink(target.dom());
                  }
                });
              }
            }
          },
          container: $_cnbf2uwtjd09f033.fromDom(editor.editorContainer),
          socket: $_cnbf2uwtjd09f033.fromDom(editor.contentAreaContainer),
          toolstrip: $_cnbf2uwtjd09f033.fromDom(editor.editorContainer.querySelector('.' + $_3584u7z1jd09f0bs.resolve('toolstrip'))),
          toolbar: $_cnbf2uwtjd09f033.fromDom(editor.editorContainer.querySelector('.' + $_3584u7z1jd09f0bs.resolve('toolbar'))),
          dropup: realm.dropup(),
          alloy: realm.system(),
          translate: $_8z5eqrwbjd09f01q.noop,
          setReadOnly: function (ro) {
            setReadOnly(readOnlyGroups, mainGroups, ro);
          }
        });
        var hideDropup = function () {
          realm.dropup().disappear(function () {
            realm.system().broadcastOn([$_b1cma0yojd09f09y.dropupDismissed()], {});
          });
        };
        $_fgkb9dy8jd09f08t.registerInspector('remove this', realm.system());
        var backToMaskGroup = {
          label: 'The first group',
          scrollable: false,
          items: [$_ch9li7z2jd09f0c0.forToolbar('back', function () {
              editor.selection.collapse();
              realm.exit();
            }, {})]
        };
        var backToReadOnlyGroup = {
          label: 'Back to read only',
          scrollable: false,
          items: [$_ch9li7z2jd09f0c0.forToolbar('readonly-back', function () {
              setReadOnly(readOnlyGroups, mainGroups, true);
            }, {})]
        };
        var readOnlyGroup = {
          label: 'The read only mode group',
          scrollable: true,
          items: []
        };
        var features = $_2kilxeypjd09f0a7.setup(realm, editor);
        var items = $_2kilxeypjd09f0a7.detect(editor.settings, features);
        var actionGroup = {
          label: 'the action group',
          scrollable: true,
          items: items
        };
        var extraGroup = {
          label: 'The extra group',
          scrollable: false,
          items: []
        };
        var mainGroups = Cell([
          backToReadOnlyGroup,
          actionGroup,
          extraGroup
        ]);
        var readOnlyGroups = Cell([
          backToMaskGroup,
          readOnlyGroup,
          extraGroup
        ]);
        $_48v0pi15wjd09f1l5.init(realm, editor);
      });
      return {
        iframeContainer: realm.socket().element().dom(),
        editorContainer: realm.element().dom()
      };
    };
    return {
      getNotificationManagerImpl: function () {
        return {
          open: $_8z5eqrwbjd09f01q.identity,
          close: $_8z5eqrwbjd09f01q.noop,
          reposition: $_8z5eqrwbjd09f01q.noop,
          getArgs: $_8z5eqrwbjd09f01q.identity
        };
      },
      renderUI: renderUI
    };
  });
  function Theme () {
  }

  return Theme;

}());
})()
