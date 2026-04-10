/**
* @vue/shared v3.5.22
* (c) 2018-present Yuxi (Evan) You and Vue contributors
* @license MIT
**/
// @__NO_SIDE_EFFECTS__
function makeMap(str) {
  const map = /* @__PURE__ */Object.create(null);
  for (const key of str.split(",")) map[key] = 1;
  return val => val in map;
}
const EMPTY_OBJ = {};
const EMPTY_ARR = [];
const NOOP = () => {};
const NO = () => false;
const isOn = key => key.charCodeAt(0) === 111 && key.charCodeAt(1) === 110 && (
// uppercase letter
key.charCodeAt(2) > 122 || key.charCodeAt(2) < 97);
const isModelListener = key => key.startsWith("onUpdate:");
const extend = Object.assign;
const remove = (arr, el) => {
  const i = arr.indexOf(el);
  if (i > -1) {
    arr.splice(i, 1);
  }
};
const hasOwnProperty$1 = Object.prototype.hasOwnProperty;
const hasOwn = (val, key) => hasOwnProperty$1.call(val, key);
const isArray = Array.isArray;
const isMap = val => toTypeString(val) === "[object Map]";
const isSet = val => toTypeString(val) === "[object Set]";
const isFunction = val => typeof val === "function";
const isString = val => typeof val === "string";
const isSymbol = val => typeof val === "symbol";
const isObject$1 = val => val !== null && typeof val === "object";
const isPromise$1 = val => {
  return (isObject$1(val) || isFunction(val)) && isFunction(val.then) && isFunction(val.catch);
};
const objectToString = Object.prototype.toString;
const toTypeString = value => objectToString.call(value);
const toRawType = value => {
  return toTypeString(value).slice(8, -1);
};
const isPlainObject = val => toTypeString(val) === "[object Object]";
const isIntegerKey = key => isString(key) && key !== "NaN" && key[0] !== "-" && "" + parseInt(key, 10) === key;
const isReservedProp = /* @__PURE__ */makeMap(
// the leading comma is intentional so empty string "" is also included
",key,ref,ref_for,ref_key,onVnodeBeforeMount,onVnodeMounted,onVnodeBeforeUpdate,onVnodeUpdated,onVnodeBeforeUnmount,onVnodeUnmounted");
const cacheStringFunction = fn => {
  const cache = /* @__PURE__ */Object.create(null);
  return str => {
    const hit = cache[str];
    return hit || (cache[str] = fn(str));
  };
};
const camelizeRE = /-\w/g;
const camelize = cacheStringFunction(str => {
  return str.replace(camelizeRE, c => c.slice(1).toUpperCase());
});
const hyphenateRE = /\B([A-Z])/g;
const hyphenate = cacheStringFunction(str => str.replace(hyphenateRE, "-$1").toLowerCase());
const capitalize = cacheStringFunction(str => {
  return str.charAt(0).toUpperCase() + str.slice(1);
});
const toHandlerKey = cacheStringFunction(str => {
  const s = str ? "on" + capitalize(str) : "";
  return s;
});
const hasChanged = (value, oldValue) => !Object.is(value, oldValue);
const invokeArrayFns = function invokeArrayFns(fns) {
  for (var _len = arguments.length, arg = new Array(_len > 1 ? _len - 1 : 0), _key2 = 1; _key2 < _len; _key2++) {
    arg[_key2 - 1] = arguments[_key2];
  }
  for (let i = 0; i < fns.length; i++) {
    fns[i](...arg);
  }
};
const def = function def(obj, key, value, writable) {
  if (writable === void 0) {
    writable = false;
  }
  Object.defineProperty(obj, key, {
    configurable: true,
    enumerable: false,
    writable,
    value
  });
};
const looseToNumber = val => {
  const n = parseFloat(val);
  return isNaN(n) ? val : n;
};
const toNumber = val => {
  const n = isString(val) ? Number(val) : NaN;
  return isNaN(n) ? val : n;
};
let _globalThis;
const getGlobalThis = () => {
  return _globalThis || (_globalThis = typeof globalThis !== "undefined" ? globalThis : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : typeof global !== "undefined" ? global : {});
};
function normalizeStyle(value) {
  if (isArray(value)) {
    const res = {};
    for (let i = 0; i < value.length; i++) {
      const item = value[i];
      const normalized = isString(item) ? parseStringStyle(item) : normalizeStyle(item);
      if (normalized) {
        for (const key in normalized) {
          res[key] = normalized[key];
        }
      }
    }
    return res;
  } else if (isString(value) || isObject$1(value)) {
    return value;
  }
}
const listDelimiterRE = /;(?![^(]*\))/g;
const propertyDelimiterRE = /:([^]+)/;
const styleCommentRE = /\/\*[^]*?\*\//g;
function parseStringStyle(cssText) {
  const ret = {};
  cssText.replace(styleCommentRE, "").split(listDelimiterRE).forEach(item => {
    if (item) {
      const tmp = item.split(propertyDelimiterRE);
      tmp.length > 1 && (ret[tmp[0].trim()] = tmp[1].trim());
    }
  });
  return ret;
}
function normalizeClass(value) {
  let res = "";
  if (isString(value)) {
    res = value;
  } else if (isArray(value)) {
    for (let i = 0; i < value.length; i++) {
      const normalized = normalizeClass(value[i]);
      if (normalized) {
        res += normalized + " ";
      }
    }
  } else if (isObject$1(value)) {
    for (const name in value) {
      if (value[name]) {
        res += name + " ";
      }
    }
  }
  return res.trim();
}
const specialBooleanAttrs = "itemscope,allowfullscreen,formnovalidate,ismap,nomodule,novalidate,readonly";
const isSpecialBooleanAttr = /* @__PURE__ */makeMap(specialBooleanAttrs);
function includeBooleanAttr(value) {
  return !!value || value === "";
}
const isRef$1 = val => {
  return !!(val && val["__v_isRef"] === true);
};
const toDisplayString = val => {
  return isString(val) ? val : val == null ? "" : isArray(val) || isObject$1(val) && (val.toString === objectToString || !isFunction(val.toString)) ? isRef$1(val) ? toDisplayString(val.value) : JSON.stringify(val, replacer, 2) : String(val);
};
const replacer = (_key, val) => {
  if (isRef$1(val)) {
    return replacer(_key, val.value);
  } else if (isMap(val)) {
    return {
      ["Map(" + val.size + ")"]: [...val.entries()].reduce((entries, _ref, i) => {
        let [key, val2] = _ref;
        entries[stringifySymbol(key, i) + " =>"] = val2;
        return entries;
      }, {})
    };
  } else if (isSet(val)) {
    return {
      ["Set(" + val.size + ")"]: [...val.values()].map(v => stringifySymbol(v))
    };
  } else if (isSymbol(val)) {
    return stringifySymbol(val);
  } else if (isObject$1(val) && !isArray(val) && !isPlainObject(val)) {
    return String(val);
  }
  return val;
};
const stringifySymbol = function stringifySymbol(v, i) {
  if (i === void 0) {
    i = "";
  }
  var _a;
  return (
    // Symbol.description in es2019+ so we need to cast here to pass
    // the lib: es2016 check
    isSymbol(v) ? "Symbol(" + ((_a = v.description) != null ? _a : i) + ")" : v
  );
};

/**
* @vue/reactivity v3.5.22
* (c) 2018-present Yuxi (Evan) You and Vue contributors
* @license MIT
**/
let activeEffectScope;
class EffectScope {
  constructor(detached) {
    if (detached === void 0) {
      detached = false;
    }
    this.detached = detached;
    /**
     * @internal
     */
    this._active = true;
    /**
     * @internal track `on` calls, allow `on` call multiple times
     */
    this._on = 0;
    /**
     * @internal
     */
    this.effects = [];
    /**
     * @internal
     */
    this.cleanups = [];
    this._isPaused = false;
    this.parent = activeEffectScope;
    if (!detached && activeEffectScope) {
      this.index = (activeEffectScope.scopes || (activeEffectScope.scopes = [])).push(this) - 1;
    }
  }
  get active() {
    return this._active;
  }
  pause() {
    if (this._active) {
      this._isPaused = true;
      let i, l;
      if (this.scopes) {
        for (i = 0, l = this.scopes.length; i < l; i++) {
          this.scopes[i].pause();
        }
      }
      for (i = 0, l = this.effects.length; i < l; i++) {
        this.effects[i].pause();
      }
    }
  }
  /**
   * Resumes the effect scope, including all child scopes and effects.
   */
  resume() {
    if (this._active) {
      if (this._isPaused) {
        this._isPaused = false;
        let i, l;
        if (this.scopes) {
          for (i = 0, l = this.scopes.length; i < l; i++) {
            this.scopes[i].resume();
          }
        }
        for (i = 0, l = this.effects.length; i < l; i++) {
          this.effects[i].resume();
        }
      }
    }
  }
  run(fn) {
    if (this._active) {
      const currentEffectScope = activeEffectScope;
      try {
        activeEffectScope = this;
        return fn();
      } finally {
        activeEffectScope = currentEffectScope;
      }
    }
  }
  /**
   * This should only be called on non-detached scopes
   * @internal
   */
  on() {
    if (++this._on === 1) {
      this.prevScope = activeEffectScope;
      activeEffectScope = this;
    }
  }
  /**
   * This should only be called on non-detached scopes
   * @internal
   */
  off() {
    if (this._on > 0 && --this._on === 0) {
      activeEffectScope = this.prevScope;
      this.prevScope = void 0;
    }
  }
  stop(fromParent) {
    if (this._active) {
      this._active = false;
      let i, l;
      for (i = 0, l = this.effects.length; i < l; i++) {
        this.effects[i].stop();
      }
      this.effects.length = 0;
      for (i = 0, l = this.cleanups.length; i < l; i++) {
        this.cleanups[i]();
      }
      this.cleanups.length = 0;
      if (this.scopes) {
        for (i = 0, l = this.scopes.length; i < l; i++) {
          this.scopes[i].stop(true);
        }
        this.scopes.length = 0;
      }
      if (!this.detached && this.parent && !fromParent) {
        const last = this.parent.scopes.pop();
        if (last && last !== this) {
          this.parent.scopes[this.index] = last;
          last.index = this.index;
        }
      }
      this.parent = void 0;
    }
  }
}
function effectScope(detached) {
  return new EffectScope(detached);
}
function getCurrentScope() {
  return activeEffectScope;
}
let activeSub;
const pausedQueueEffects = /* @__PURE__ */new WeakSet();
class ReactiveEffect {
  constructor(fn) {
    this.fn = fn;
    /**
     * @internal
     */
    this.deps = void 0;
    /**
     * @internal
     */
    this.depsTail = void 0;
    /**
     * @internal
     */
    this.flags = 1 | 4;
    /**
     * @internal
     */
    this.next = void 0;
    /**
     * @internal
     */
    this.cleanup = void 0;
    this.scheduler = void 0;
    if (activeEffectScope && activeEffectScope.active) {
      activeEffectScope.effects.push(this);
    }
  }
  pause() {
    this.flags |= 64;
  }
  resume() {
    if (this.flags & 64) {
      this.flags &= -65;
      if (pausedQueueEffects.has(this)) {
        pausedQueueEffects.delete(this);
        this.trigger();
      }
    }
  }
  /**
   * @internal
   */
  notify() {
    if (this.flags & 2 && !(this.flags & 32)) {
      return;
    }
    if (!(this.flags & 8)) {
      batch(this);
    }
  }
  run() {
    if (!(this.flags & 1)) {
      return this.fn();
    }
    this.flags |= 2;
    cleanupEffect(this);
    prepareDeps(this);
    const prevEffect = activeSub;
    const prevShouldTrack = shouldTrack;
    activeSub = this;
    shouldTrack = true;
    try {
      return this.fn();
    } finally {
      cleanupDeps(this);
      activeSub = prevEffect;
      shouldTrack = prevShouldTrack;
      this.flags &= -3;
    }
  }
  stop() {
    if (this.flags & 1) {
      for (let link = this.deps; link; link = link.nextDep) {
        removeSub(link);
      }
      this.deps = this.depsTail = void 0;
      cleanupEffect(this);
      this.onStop && this.onStop();
      this.flags &= -2;
    }
  }
  trigger() {
    if (this.flags & 64) {
      pausedQueueEffects.add(this);
    } else if (this.scheduler) {
      this.scheduler();
    } else {
      this.runIfDirty();
    }
  }
  /**
   * @internal
   */
  runIfDirty() {
    if (isDirty(this)) {
      this.run();
    }
  }
  get dirty() {
    return isDirty(this);
  }
}
let batchDepth = 0;
let batchedSub;
let batchedComputed;
function batch(sub, isComputed) {
  if (isComputed === void 0) {
    isComputed = false;
  }
  sub.flags |= 8;
  if (isComputed) {
    sub.next = batchedComputed;
    batchedComputed = sub;
    return;
  }
  sub.next = batchedSub;
  batchedSub = sub;
}
function startBatch() {
  batchDepth++;
}
function endBatch() {
  if (--batchDepth > 0) {
    return;
  }
  if (batchedComputed) {
    let e = batchedComputed;
    batchedComputed = void 0;
    while (e) {
      const next = e.next;
      e.next = void 0;
      e.flags &= -9;
      e = next;
    }
  }
  let error;
  while (batchedSub) {
    let e = batchedSub;
    batchedSub = void 0;
    while (e) {
      const next = e.next;
      e.next = void 0;
      e.flags &= -9;
      if (e.flags & 1) {
        try {
          ;
          e.trigger();
        } catch (err) {
          if (!error) error = err;
        }
      }
      e = next;
    }
  }
  if (error) throw error;
}
function prepareDeps(sub) {
  for (let link = sub.deps; link; link = link.nextDep) {
    link.version = -1;
    link.prevActiveLink = link.dep.activeLink;
    link.dep.activeLink = link;
  }
}
function cleanupDeps(sub) {
  let head;
  let tail = sub.depsTail;
  let link = tail;
  while (link) {
    const prev = link.prevDep;
    if (link.version === -1) {
      if (link === tail) tail = prev;
      removeSub(link);
      removeDep(link);
    } else {
      head = link;
    }
    link.dep.activeLink = link.prevActiveLink;
    link.prevActiveLink = void 0;
    link = prev;
  }
  sub.deps = head;
  sub.depsTail = tail;
}
function isDirty(sub) {
  for (let link = sub.deps; link; link = link.nextDep) {
    if (link.dep.version !== link.version || link.dep.computed && (refreshComputed(link.dep.computed) || link.dep.version !== link.version)) {
      return true;
    }
  }
  if (sub._dirty) {
    return true;
  }
  return false;
}
function refreshComputed(computed) {
  if (computed.flags & 4 && !(computed.flags & 16)) {
    return;
  }
  computed.flags &= -17;
  if (computed.globalVersion === globalVersion) {
    return;
  }
  computed.globalVersion = globalVersion;
  if (!computed.isSSR && computed.flags & 128 && (!computed.deps && !computed._dirty || !isDirty(computed))) {
    return;
  }
  computed.flags |= 2;
  const dep = computed.dep;
  const prevSub = activeSub;
  const prevShouldTrack = shouldTrack;
  activeSub = computed;
  shouldTrack = true;
  try {
    prepareDeps(computed);
    const value = computed.fn(computed._value);
    if (dep.version === 0 || hasChanged(value, computed._value)) {
      computed.flags |= 128;
      computed._value = value;
      dep.version++;
    }
  } catch (err) {
    dep.version++;
    throw err;
  } finally {
    activeSub = prevSub;
    shouldTrack = prevShouldTrack;
    cleanupDeps(computed);
    computed.flags &= -3;
  }
}
function removeSub(link, soft) {
  if (soft === void 0) {
    soft = false;
  }
  const {
    dep,
    prevSub,
    nextSub
  } = link;
  if (prevSub) {
    prevSub.nextSub = nextSub;
    link.prevSub = void 0;
  }
  if (nextSub) {
    nextSub.prevSub = prevSub;
    link.nextSub = void 0;
  }
  if (dep.subs === link) {
    dep.subs = prevSub;
    if (!prevSub && dep.computed) {
      dep.computed.flags &= -5;
      for (let l = dep.computed.deps; l; l = l.nextDep) {
        removeSub(l, true);
      }
    }
  }
  if (!soft && ! --dep.sc && dep.map) {
    dep.map.delete(dep.key);
  }
}
function removeDep(link) {
  const {
    prevDep,
    nextDep
  } = link;
  if (prevDep) {
    prevDep.nextDep = nextDep;
    link.prevDep = void 0;
  }
  if (nextDep) {
    nextDep.prevDep = prevDep;
    link.nextDep = void 0;
  }
}
let shouldTrack = true;
const trackStack = [];
function pauseTracking() {
  trackStack.push(shouldTrack);
  shouldTrack = false;
}
function resetTracking() {
  const last = trackStack.pop();
  shouldTrack = last === void 0 ? true : last;
}
function cleanupEffect(e) {
  const {
    cleanup
  } = e;
  e.cleanup = void 0;
  if (cleanup) {
    const prevSub = activeSub;
    activeSub = void 0;
    try {
      cleanup();
    } finally {
      activeSub = prevSub;
    }
  }
}
let globalVersion = 0;
class Link {
  constructor(sub, dep) {
    this.sub = sub;
    this.dep = dep;
    this.version = dep.version;
    this.nextDep = this.prevDep = this.nextSub = this.prevSub = this.prevActiveLink = void 0;
  }
}
class Dep {
  // TODO isolatedDeclarations "__v_skip"
  constructor(computed) {
    this.computed = computed;
    this.version = 0;
    /**
     * Link between this dep and the current active effect
     */
    this.activeLink = void 0;
    /**
     * Doubly linked list representing the subscribing effects (tail)
     */
    this.subs = void 0;
    /**
     * For object property deps cleanup
     */
    this.map = void 0;
    this.key = void 0;
    /**
     * Subscriber counter
     */
    this.sc = 0;
    /**
     * @internal
     */
    this.__v_skip = true;
  }
  track(debugInfo) {
    if (!activeSub || !shouldTrack || activeSub === this.computed) {
      return;
    }
    let link = this.activeLink;
    if (link === void 0 || link.sub !== activeSub) {
      link = this.activeLink = new Link(activeSub, this);
      if (!activeSub.deps) {
        activeSub.deps = activeSub.depsTail = link;
      } else {
        link.prevDep = activeSub.depsTail;
        activeSub.depsTail.nextDep = link;
        activeSub.depsTail = link;
      }
      addSub(link);
    } else if (link.version === -1) {
      link.version = this.version;
      if (link.nextDep) {
        const next = link.nextDep;
        next.prevDep = link.prevDep;
        if (link.prevDep) {
          link.prevDep.nextDep = next;
        }
        link.prevDep = activeSub.depsTail;
        link.nextDep = void 0;
        activeSub.depsTail.nextDep = link;
        activeSub.depsTail = link;
        if (activeSub.deps === link) {
          activeSub.deps = next;
        }
      }
    }
    return link;
  }
  trigger(debugInfo) {
    this.version++;
    globalVersion++;
    this.notify(debugInfo);
  }
  notify(debugInfo) {
    startBatch();
    try {
      if (!!("production" !== "production")) ;
      for (let link = this.subs; link; link = link.prevSub) {
        if (link.sub.notify()) {
          ;
          link.sub.dep.notify();
        }
      }
    } finally {
      endBatch();
    }
  }
}
function addSub(link) {
  link.dep.sc++;
  if (link.sub.flags & 4) {
    const computed = link.dep.computed;
    if (computed && !link.dep.subs) {
      computed.flags |= 4 | 16;
      for (let l = computed.deps; l; l = l.nextDep) {
        addSub(l);
      }
    }
    const currentTail = link.dep.subs;
    if (currentTail !== link) {
      link.prevSub = currentTail;
      if (currentTail) currentTail.nextSub = link;
    }
    link.dep.subs = link;
  }
}
const targetMap = /* @__PURE__ */new WeakMap();
const ITERATE_KEY = Symbol("");
const MAP_KEY_ITERATE_KEY = Symbol("");
const ARRAY_ITERATE_KEY = Symbol("");
function track(target, type, key) {
  if (shouldTrack && activeSub) {
    let depsMap = targetMap.get(target);
    if (!depsMap) {
      targetMap.set(target, depsMap = /* @__PURE__ */new Map());
    }
    let dep = depsMap.get(key);
    if (!dep) {
      depsMap.set(key, dep = new Dep());
      dep.map = depsMap;
      dep.key = key;
    }
    {
      dep.track();
    }
  }
}
function trigger(target, type, key, newValue, oldValue, oldTarget) {
  const depsMap = targetMap.get(target);
  if (!depsMap) {
    globalVersion++;
    return;
  }
  const run = dep => {
    if (dep) {
      {
        dep.trigger();
      }
    }
  };
  startBatch();
  if (type === "clear") {
    depsMap.forEach(run);
  } else {
    const targetIsArray = isArray(target);
    const isArrayIndex = targetIsArray && isIntegerKey(key);
    if (targetIsArray && key === "length") {
      const newLength = Number(newValue);
      depsMap.forEach((dep, key2) => {
        if (key2 === "length" || key2 === ARRAY_ITERATE_KEY || !isSymbol(key2) && key2 >= newLength) {
          run(dep);
        }
      });
    } else {
      if (key !== void 0 || depsMap.has(void 0)) {
        run(depsMap.get(key));
      }
      if (isArrayIndex) {
        run(depsMap.get(ARRAY_ITERATE_KEY));
      }
      switch (type) {
        case "add":
          if (!targetIsArray) {
            run(depsMap.get(ITERATE_KEY));
            if (isMap(target)) {
              run(depsMap.get(MAP_KEY_ITERATE_KEY));
            }
          } else if (isArrayIndex) {
            run(depsMap.get("length"));
          }
          break;
        case "delete":
          if (!targetIsArray) {
            run(depsMap.get(ITERATE_KEY));
            if (isMap(target)) {
              run(depsMap.get(MAP_KEY_ITERATE_KEY));
            }
          }
          break;
        case "set":
          if (isMap(target)) {
            run(depsMap.get(ITERATE_KEY));
          }
          break;
      }
    }
  }
  endBatch();
}
function getDepFromReactive(object, key) {
  const depMap = targetMap.get(object);
  return depMap && depMap.get(key);
}
function reactiveReadArray(array) {
  const raw = toRaw(array);
  if (raw === array) return raw;
  track(raw, "iterate", ARRAY_ITERATE_KEY);
  return isShallow(array) ? raw : raw.map(toReactive);
}
function shallowReadArray(arr) {
  track(arr = toRaw(arr), "iterate", ARRAY_ITERATE_KEY);
  return arr;
}
const arrayInstrumentations = {
  __proto__: null,
  [Symbol.iterator]() {
    return iterator(this, Symbol.iterator, toReactive);
  },
  concat() {
    for (var _len2 = arguments.length, args = new Array(_len2), _key3 = 0; _key3 < _len2; _key3++) {
      args[_key3] = arguments[_key3];
    }
    return reactiveReadArray(this).concat(...args.map(x => isArray(x) ? reactiveReadArray(x) : x));
  },
  entries() {
    return iterator(this, "entries", value => {
      value[1] = toReactive(value[1]);
      return value;
    });
  },
  every(fn, thisArg) {
    return apply(this, "every", fn, thisArg, void 0, arguments);
  },
  filter(fn, thisArg) {
    return apply(this, "filter", fn, thisArg, v => v.map(toReactive), arguments);
  },
  find(fn, thisArg) {
    return apply(this, "find", fn, thisArg, toReactive, arguments);
  },
  findIndex(fn, thisArg) {
    return apply(this, "findIndex", fn, thisArg, void 0, arguments);
  },
  findLast(fn, thisArg) {
    return apply(this, "findLast", fn, thisArg, toReactive, arguments);
  },
  findLastIndex(fn, thisArg) {
    return apply(this, "findLastIndex", fn, thisArg, void 0, arguments);
  },
  // flat, flatMap could benefit from ARRAY_ITERATE but are not straight-forward to implement
  forEach(fn, thisArg) {
    return apply(this, "forEach", fn, thisArg, void 0, arguments);
  },
  includes() {
    for (var _len3 = arguments.length, args = new Array(_len3), _key4 = 0; _key4 < _len3; _key4++) {
      args[_key4] = arguments[_key4];
    }
    return searchProxy(this, "includes", args);
  },
  indexOf() {
    for (var _len4 = arguments.length, args = new Array(_len4), _key5 = 0; _key5 < _len4; _key5++) {
      args[_key5] = arguments[_key5];
    }
    return searchProxy(this, "indexOf", args);
  },
  join(separator) {
    return reactiveReadArray(this).join(separator);
  },
  // keys() iterator only reads `length`, no optimization required
  lastIndexOf() {
    for (var _len5 = arguments.length, args = new Array(_len5), _key6 = 0; _key6 < _len5; _key6++) {
      args[_key6] = arguments[_key6];
    }
    return searchProxy(this, "lastIndexOf", args);
  },
  map(fn, thisArg) {
    return apply(this, "map", fn, thisArg, void 0, arguments);
  },
  pop() {
    return noTracking(this, "pop");
  },
  push() {
    for (var _len6 = arguments.length, args = new Array(_len6), _key7 = 0; _key7 < _len6; _key7++) {
      args[_key7] = arguments[_key7];
    }
    return noTracking(this, "push", args);
  },
  reduce(fn) {
    for (var _len7 = arguments.length, args = new Array(_len7 > 1 ? _len7 - 1 : 0), _key8 = 1; _key8 < _len7; _key8++) {
      args[_key8 - 1] = arguments[_key8];
    }
    return reduce(this, "reduce", fn, args);
  },
  reduceRight(fn) {
    for (var _len8 = arguments.length, args = new Array(_len8 > 1 ? _len8 - 1 : 0), _key9 = 1; _key9 < _len8; _key9++) {
      args[_key9 - 1] = arguments[_key9];
    }
    return reduce(this, "reduceRight", fn, args);
  },
  shift() {
    return noTracking(this, "shift");
  },
  // slice could use ARRAY_ITERATE but also seems to beg for range tracking
  some(fn, thisArg) {
    return apply(this, "some", fn, thisArg, void 0, arguments);
  },
  splice() {
    for (var _len9 = arguments.length, args = new Array(_len9), _key0 = 0; _key0 < _len9; _key0++) {
      args[_key0] = arguments[_key0];
    }
    return noTracking(this, "splice", args);
  },
  toReversed() {
    return reactiveReadArray(this).toReversed();
  },
  toSorted(comparer) {
    return reactiveReadArray(this).toSorted(comparer);
  },
  toSpliced() {
    return reactiveReadArray(this).toSpliced(...arguments);
  },
  unshift() {
    for (var _len0 = arguments.length, args = new Array(_len0), _key1 = 0; _key1 < _len0; _key1++) {
      args[_key1] = arguments[_key1];
    }
    return noTracking(this, "unshift", args);
  },
  values() {
    return iterator(this, "values", toReactive);
  }
};
function iterator(self, method, wrapValue) {
  const arr = shallowReadArray(self);
  const iter = arr[method]();
  if (arr !== self && !isShallow(self)) {
    iter._next = iter.next;
    iter.next = () => {
      const result = iter._next();
      if (!result.done) {
        result.value = wrapValue(result.value);
      }
      return result;
    };
  }
  return iter;
}
const arrayProto = Array.prototype;
function apply(self, method, fn, thisArg, wrappedRetFn, args) {
  const arr = shallowReadArray(self);
  const needsWrap = arr !== self && !isShallow(self);
  const methodFn = arr[method];
  if (methodFn !== arrayProto[method]) {
    const result2 = methodFn.apply(self, args);
    return needsWrap ? toReactive(result2) : result2;
  }
  let wrappedFn = fn;
  if (arr !== self) {
    if (needsWrap) {
      wrappedFn = function wrappedFn(item, index) {
        return fn.call(this, toReactive(item), index, self);
      };
    } else if (fn.length > 2) {
      wrappedFn = function wrappedFn(item, index) {
        return fn.call(this, item, index, self);
      };
    }
  }
  const result = methodFn.call(arr, wrappedFn, thisArg);
  return needsWrap && wrappedRetFn ? wrappedRetFn(result) : result;
}
function reduce(self, method, fn, args) {
  const arr = shallowReadArray(self);
  let wrappedFn = fn;
  if (arr !== self) {
    if (!isShallow(self)) {
      wrappedFn = function wrappedFn(acc, item, index) {
        return fn.call(this, acc, toReactive(item), index, self);
      };
    } else if (fn.length > 3) {
      wrappedFn = function wrappedFn(acc, item, index) {
        return fn.call(this, acc, item, index, self);
      };
    }
  }
  return arr[method](wrappedFn, ...args);
}
function searchProxy(self, method, args) {
  const arr = toRaw(self);
  track(arr, "iterate", ARRAY_ITERATE_KEY);
  const res = arr[method](...args);
  if ((res === -1 || res === false) && isProxy(args[0])) {
    args[0] = toRaw(args[0]);
    return arr[method](...args);
  }
  return res;
}
function noTracking(self, method, args) {
  if (args === void 0) {
    args = [];
  }
  pauseTracking();
  startBatch();
  const res = toRaw(self)[method].apply(self, args);
  endBatch();
  resetTracking();
  return res;
}
const isNonTrackableKeys = /* @__PURE__ */makeMap("__proto__,__v_isRef,__isVue");
const builtInSymbols = new Set(/* @__PURE__ */Object.getOwnPropertyNames(Symbol).filter(key => key !== "arguments" && key !== "caller").map(key => Symbol[key]).filter(isSymbol));
function hasOwnProperty(key) {
  if (!isSymbol(key)) key = String(key);
  const obj = toRaw(this);
  track(obj, "has", key);
  return obj.hasOwnProperty(key);
}
class BaseReactiveHandler {
  constructor(_isReadonly, _isShallow) {
    if (_isReadonly === void 0) {
      _isReadonly = false;
    }
    if (_isShallow === void 0) {
      _isShallow = false;
    }
    this._isReadonly = _isReadonly;
    this._isShallow = _isShallow;
  }
  get(target, key, receiver) {
    if (key === "__v_skip") return target["__v_skip"];
    const isReadonly2 = this._isReadonly,
      isShallow2 = this._isShallow;
    if (key === "__v_isReactive") {
      return !isReadonly2;
    } else if (key === "__v_isReadonly") {
      return isReadonly2;
    } else if (key === "__v_isShallow") {
      return isShallow2;
    } else if (key === "__v_raw") {
      if (receiver === (isReadonly2 ? isShallow2 ? shallowReadonlyMap : readonlyMap : isShallow2 ? shallowReactiveMap : reactiveMap).get(target) ||
      // receiver is not the reactive proxy, but has the same prototype
      // this means the receiver is a user proxy of the reactive proxy
      Object.getPrototypeOf(target) === Object.getPrototypeOf(receiver)) {
        return target;
      }
      return;
    }
    const targetIsArray = isArray(target);
    if (!isReadonly2) {
      let fn;
      if (targetIsArray && (fn = arrayInstrumentations[key])) {
        return fn;
      }
      if (key === "hasOwnProperty") {
        return hasOwnProperty;
      }
    }
    const res = Reflect.get(target, key,
    // if this is a proxy wrapping a ref, return methods using the raw ref
    // as receiver so that we don't have to call `toRaw` on the ref in all
    // its class methods
    isRef(target) ? target : receiver);
    if (isSymbol(key) ? builtInSymbols.has(key) : isNonTrackableKeys(key)) {
      return res;
    }
    if (!isReadonly2) {
      track(target, "get", key);
    }
    if (isShallow2) {
      return res;
    }
    if (isRef(res)) {
      const value = targetIsArray && isIntegerKey(key) ? res : res.value;
      return isReadonly2 && isObject$1(value) ? readonly(value) : value;
    }
    if (isObject$1(res)) {
      return isReadonly2 ? readonly(res) : reactive(res);
    }
    return res;
  }
}
class MutableReactiveHandler extends BaseReactiveHandler {
  constructor(isShallow2) {
    if (isShallow2 === void 0) {
      isShallow2 = false;
    }
    super(false, isShallow2);
  }
  set(target, key, value, receiver) {
    let oldValue = target[key];
    if (!this._isShallow) {
      const isOldValueReadonly = isReadonly(oldValue);
      if (!isShallow(value) && !isReadonly(value)) {
        oldValue = toRaw(oldValue);
        value = toRaw(value);
      }
      if (!isArray(target) && isRef(oldValue) && !isRef(value)) {
        if (isOldValueReadonly) {
          return true;
        } else {
          oldValue.value = value;
          return true;
        }
      }
    }
    const hadKey = isArray(target) && isIntegerKey(key) ? Number(key) < target.length : hasOwn(target, key);
    const result = Reflect.set(target, key, value, isRef(target) ? target : receiver);
    if (target === toRaw(receiver)) {
      if (!hadKey) {
        trigger(target, "add", key, value);
      } else if (hasChanged(value, oldValue)) {
        trigger(target, "set", key, value);
      }
    }
    return result;
  }
  deleteProperty(target, key) {
    const hadKey = hasOwn(target, key);
    target[key];
    const result = Reflect.deleteProperty(target, key);
    if (result && hadKey) {
      trigger(target, "delete", key, void 0);
    }
    return result;
  }
  has(target, key) {
    const result = Reflect.has(target, key);
    if (!isSymbol(key) || !builtInSymbols.has(key)) {
      track(target, "has", key);
    }
    return result;
  }
  ownKeys(target) {
    track(target, "iterate", isArray(target) ? "length" : ITERATE_KEY);
    return Reflect.ownKeys(target);
  }
}
class ReadonlyReactiveHandler extends BaseReactiveHandler {
  constructor(isShallow2) {
    if (isShallow2 === void 0) {
      isShallow2 = false;
    }
    super(true, isShallow2);
  }
  set(target, key) {
    return true;
  }
  deleteProperty(target, key) {
    return true;
  }
}
const mutableHandlers = /* @__PURE__ */new MutableReactiveHandler();
const readonlyHandlers = /* @__PURE__ */new ReadonlyReactiveHandler();
const shallowReactiveHandlers = /* @__PURE__ */new MutableReactiveHandler(true);
const shallowReadonlyHandlers = /* @__PURE__ */new ReadonlyReactiveHandler(true);
const toShallow = value => value;
const getProto = v => Reflect.getPrototypeOf(v);
function createIterableMethod(method, isReadonly2, isShallow2) {
  return function () {
    const target = this["__v_raw"];
    const rawTarget = toRaw(target);
    const targetIsMap = isMap(rawTarget);
    const isPair = method === "entries" || method === Symbol.iterator && targetIsMap;
    const isKeyOnly = method === "keys" && targetIsMap;
    const innerIterator = target[method](...arguments);
    const wrap = isShallow2 ? toShallow : isReadonly2 ? toReadonly : toReactive;
    !isReadonly2 && track(rawTarget, "iterate", isKeyOnly ? MAP_KEY_ITERATE_KEY : ITERATE_KEY);
    return {
      // iterator protocol
      next() {
        const {
          value,
          done
        } = innerIterator.next();
        return done ? {
          value,
          done
        } : {
          value: isPair ? [wrap(value[0]), wrap(value[1])] : wrap(value),
          done
        };
      },
      // iterable protocol
      [Symbol.iterator]() {
        return this;
      }
    };
  };
}
function createReadonlyMethod(type) {
  return function () {
    return type === "delete" ? false : type === "clear" ? void 0 : this;
  };
}
function createInstrumentations(readonly, shallow) {
  const instrumentations = {
    get(key) {
      const target = this["__v_raw"];
      const rawTarget = toRaw(target);
      const rawKey = toRaw(key);
      if (!readonly) {
        if (hasChanged(key, rawKey)) {
          track(rawTarget, "get", key);
        }
        track(rawTarget, "get", rawKey);
      }
      const {
        has
      } = getProto(rawTarget);
      const wrap = shallow ? toShallow : readonly ? toReadonly : toReactive;
      if (has.call(rawTarget, key)) {
        return wrap(target.get(key));
      } else if (has.call(rawTarget, rawKey)) {
        return wrap(target.get(rawKey));
      } else if (target !== rawTarget) {
        target.get(key);
      }
    },
    get size() {
      const target = this["__v_raw"];
      !readonly && track(toRaw(target), "iterate", ITERATE_KEY);
      return target.size;
    },
    has(key) {
      const target = this["__v_raw"];
      const rawTarget = toRaw(target);
      const rawKey = toRaw(key);
      if (!readonly) {
        if (hasChanged(key, rawKey)) {
          track(rawTarget, "has", key);
        }
        track(rawTarget, "has", rawKey);
      }
      return key === rawKey ? target.has(key) : target.has(key) || target.has(rawKey);
    },
    forEach(callback, thisArg) {
      const observed = this;
      const target = observed["__v_raw"];
      const rawTarget = toRaw(target);
      const wrap = shallow ? toShallow : readonly ? toReadonly : toReactive;
      !readonly && track(rawTarget, "iterate", ITERATE_KEY);
      return target.forEach((value, key) => {
        return callback.call(thisArg, wrap(value), wrap(key), observed);
      });
    }
  };
  extend(instrumentations, readonly ? {
    add: createReadonlyMethod("add"),
    set: createReadonlyMethod("set"),
    delete: createReadonlyMethod("delete"),
    clear: createReadonlyMethod("clear")
  } : {
    add(value) {
      if (!shallow && !isShallow(value) && !isReadonly(value)) {
        value = toRaw(value);
      }
      const target = toRaw(this);
      const proto = getProto(target);
      const hadKey = proto.has.call(target, value);
      if (!hadKey) {
        target.add(value);
        trigger(target, "add", value, value);
      }
      return this;
    },
    set(key, value) {
      if (!shallow && !isShallow(value) && !isReadonly(value)) {
        value = toRaw(value);
      }
      const target = toRaw(this);
      const {
        has,
        get
      } = getProto(target);
      let hadKey = has.call(target, key);
      if (!hadKey) {
        key = toRaw(key);
        hadKey = has.call(target, key);
      }
      const oldValue = get.call(target, key);
      target.set(key, value);
      if (!hadKey) {
        trigger(target, "add", key, value);
      } else if (hasChanged(value, oldValue)) {
        trigger(target, "set", key, value);
      }
      return this;
    },
    delete(key) {
      const target = toRaw(this);
      const {
        has,
        get
      } = getProto(target);
      let hadKey = has.call(target, key);
      if (!hadKey) {
        key = toRaw(key);
        hadKey = has.call(target, key);
      }
      get ? get.call(target, key) : void 0;
      const result = target.delete(key);
      if (hadKey) {
        trigger(target, "delete", key, void 0);
      }
      return result;
    },
    clear() {
      const target = toRaw(this);
      const hadItems = target.size !== 0;
      const result = target.clear();
      if (hadItems) {
        trigger(target, "clear", void 0, void 0);
      }
      return result;
    }
  });
  const iteratorMethods = ["keys", "values", "entries", Symbol.iterator];
  iteratorMethods.forEach(method => {
    instrumentations[method] = createIterableMethod(method, readonly, shallow);
  });
  return instrumentations;
}
function createInstrumentationGetter(isReadonly2, shallow) {
  const instrumentations = createInstrumentations(isReadonly2, shallow);
  return (target, key, receiver) => {
    if (key === "__v_isReactive") {
      return !isReadonly2;
    } else if (key === "__v_isReadonly") {
      return isReadonly2;
    } else if (key === "__v_raw") {
      return target;
    }
    return Reflect.get(hasOwn(instrumentations, key) && key in target ? instrumentations : target, key, receiver);
  };
}
const mutableCollectionHandlers = {
  get: /* @__PURE__ */createInstrumentationGetter(false, false)
};
const shallowCollectionHandlers = {
  get: /* @__PURE__ */createInstrumentationGetter(false, true)
};
const readonlyCollectionHandlers = {
  get: /* @__PURE__ */createInstrumentationGetter(true, false)
};
const shallowReadonlyCollectionHandlers = {
  get: /* @__PURE__ */createInstrumentationGetter(true, true)
};
const reactiveMap = /* @__PURE__ */new WeakMap();
const shallowReactiveMap = /* @__PURE__ */new WeakMap();
const readonlyMap = /* @__PURE__ */new WeakMap();
const shallowReadonlyMap = /* @__PURE__ */new WeakMap();
function targetTypeMap(rawType) {
  switch (rawType) {
    case "Object":
    case "Array":
      return 1 /* COMMON */;
    case "Map":
    case "Set":
    case "WeakMap":
    case "WeakSet":
      return 2 /* COLLECTION */;
    default:
      return 0 /* INVALID */;
  }
}
function getTargetType(value) {
  return value["__v_skip"] || !Object.isExtensible(value) ? 0 /* INVALID */ : targetTypeMap(toRawType(value));
}
function reactive(target) {
  if (isReadonly(target)) {
    return target;
  }
  return createReactiveObject(target, false, mutableHandlers, mutableCollectionHandlers, reactiveMap);
}
function shallowReactive(target) {
  return createReactiveObject(target, false, shallowReactiveHandlers, shallowCollectionHandlers, shallowReactiveMap);
}
function readonly(target) {
  return createReactiveObject(target, true, readonlyHandlers, readonlyCollectionHandlers, readonlyMap);
}
function shallowReadonly(target) {
  return createReactiveObject(target, true, shallowReadonlyHandlers, shallowReadonlyCollectionHandlers, shallowReadonlyMap);
}
function createReactiveObject(target, isReadonly2, baseHandlers, collectionHandlers, proxyMap) {
  if (!isObject$1(target)) {
    return target;
  }
  if (target["__v_raw"] && !(isReadonly2 && target["__v_isReactive"])) {
    return target;
  }
  const targetType = getTargetType(target);
  if (targetType === 0 /* INVALID */) {
    return target;
  }
  const existingProxy = proxyMap.get(target);
  if (existingProxy) {
    return existingProxy;
  }
  const proxy = new Proxy(target, targetType === 2 /* COLLECTION */ ? collectionHandlers : baseHandlers);
  proxyMap.set(target, proxy);
  return proxy;
}
function isReactive(value) {
  if (isReadonly(value)) {
    return isReactive(value["__v_raw"]);
  }
  return !!(value && value["__v_isReactive"]);
}
function isReadonly(value) {
  return !!(value && value["__v_isReadonly"]);
}
function isShallow(value) {
  return !!(value && value["__v_isShallow"]);
}
function isProxy(value) {
  return value ? !!value["__v_raw"] : false;
}
function toRaw(observed) {
  const raw = observed && observed["__v_raw"];
  return raw ? toRaw(raw) : observed;
}
function markRaw(value) {
  if (!hasOwn(value, "__v_skip") && Object.isExtensible(value)) {
    def(value, "__v_skip", true);
  }
  return value;
}
const toReactive = value => isObject$1(value) ? reactive(value) : value;
const toReadonly = value => isObject$1(value) ? readonly(value) : value;
function isRef(r) {
  return r ? r["__v_isRef"] === true : false;
}
function ref(value) {
  return createRef(value, false);
}
function createRef(rawValue, shallow) {
  if (isRef(rawValue)) {
    return rawValue;
  }
  return new RefImpl(rawValue, shallow);
}
class RefImpl {
  constructor(value, isShallow2) {
    this.dep = new Dep();
    this["__v_isRef"] = true;
    this["__v_isShallow"] = false;
    this._rawValue = isShallow2 ? value : toRaw(value);
    this._value = isShallow2 ? value : toReactive(value);
    this["__v_isShallow"] = isShallow2;
  }
  get value() {
    {
      this.dep.track();
    }
    return this._value;
  }
  set value(newValue) {
    const oldValue = this._rawValue;
    const useDirectValue = this["__v_isShallow"] || isShallow(newValue) || isReadonly(newValue);
    newValue = useDirectValue ? newValue : toRaw(newValue);
    if (hasChanged(newValue, oldValue)) {
      this._rawValue = newValue;
      this._value = useDirectValue ? newValue : toReactive(newValue);
      {
        this.dep.trigger();
      }
    }
  }
}
function unref(ref2) {
  return isRef(ref2) ? ref2.value : ref2;
}
const shallowUnwrapHandlers = {
  get: (target, key, receiver) => key === "__v_raw" ? target : unref(Reflect.get(target, key, receiver)),
  set: (target, key, value, receiver) => {
    const oldValue = target[key];
    if (isRef(oldValue) && !isRef(value)) {
      oldValue.value = value;
      return true;
    } else {
      return Reflect.set(target, key, value, receiver);
    }
  }
};
function proxyRefs(objectWithRefs) {
  return isReactive(objectWithRefs) ? objectWithRefs : new Proxy(objectWithRefs, shallowUnwrapHandlers);
}
function toRefs(object) {
  const ret = isArray(object) ? new Array(object.length) : {};
  for (const key in object) {
    ret[key] = propertyToRef(object, key);
  }
  return ret;
}
class ObjectRefImpl {
  constructor(_object, _key, _defaultValue) {
    this._object = _object;
    this._key = _key;
    this._defaultValue = _defaultValue;
    this["__v_isRef"] = true;
    this._value = void 0;
  }
  get value() {
    const val = this._object[this._key];
    return this._value = val === void 0 ? this._defaultValue : val;
  }
  set value(newVal) {
    this._object[this._key] = newVal;
  }
  get dep() {
    return getDepFromReactive(toRaw(this._object), this._key);
  }
}
function propertyToRef(source, key, defaultValue) {
  const val = source[key];
  return isRef(val) ? val : new ObjectRefImpl(source, key, defaultValue);
}
class ComputedRefImpl {
  constructor(fn, setter, isSSR) {
    this.fn = fn;
    this.setter = setter;
    /**
     * @internal
     */
    this._value = void 0;
    /**
     * @internal
     */
    this.dep = new Dep(this);
    /**
     * @internal
     */
    this.__v_isRef = true;
    // TODO isolatedDeclarations "__v_isReadonly"
    // A computed is also a subscriber that tracks other deps
    /**
     * @internal
     */
    this.deps = void 0;
    /**
     * @internal
     */
    this.depsTail = void 0;
    /**
     * @internal
     */
    this.flags = 16;
    /**
     * @internal
     */
    this.globalVersion = globalVersion - 1;
    /**
     * @internal
     */
    this.next = void 0;
    // for backwards compat
    this.effect = this;
    this["__v_isReadonly"] = !setter;
    this.isSSR = isSSR;
  }
  /**
   * @internal
   */
  notify() {
    this.flags |= 16;
    if (!(this.flags & 8) &&
    // avoid infinite self recursion
    activeSub !== this) {
      batch(this, true);
      return true;
    }
  }
  get value() {
    const link = this.dep.track();
    refreshComputed(this);
    if (link) {
      link.version = this.dep.version;
    }
    return this._value;
  }
  set value(newValue) {
    if (this.setter) {
      this.setter(newValue);
    }
  }
}
function computed$1(getterOrOptions, debugOptions, isSSR) {
  if (isSSR === void 0) {
    isSSR = false;
  }
  let getter;
  let setter;
  if (isFunction(getterOrOptions)) {
    getter = getterOrOptions;
  } else {
    getter = getterOrOptions.get;
    setter = getterOrOptions.set;
  }
  const cRef = new ComputedRefImpl(getter, setter, isSSR);
  return cRef;
}
const INITIAL_WATCHER_VALUE = {};
const cleanupMap = /* @__PURE__ */new WeakMap();
let activeWatcher = void 0;
function onWatcherCleanup(cleanupFn, failSilently, owner) {
  if (owner === void 0) {
    owner = activeWatcher;
  }
  if (owner) {
    let cleanups = cleanupMap.get(owner);
    if (!cleanups) cleanupMap.set(owner, cleanups = []);
    cleanups.push(cleanupFn);
  }
}
function watch$1(source, cb, options) {
  if (options === void 0) {
    options = EMPTY_OBJ;
  }
  const {
    immediate,
    deep,
    once,
    scheduler,
    augmentJob,
    call
  } = options;
  const reactiveGetter = source2 => {
    if (deep) return source2;
    if (isShallow(source2) || deep === false || deep === 0) return traverse(source2, 1);
    return traverse(source2);
  };
  let effect;
  let getter;
  let cleanup;
  let boundCleanup;
  let forceTrigger = false;
  let isMultiSource = false;
  if (isRef(source)) {
    getter = () => source.value;
    forceTrigger = isShallow(source);
  } else if (isReactive(source)) {
    getter = () => reactiveGetter(source);
    forceTrigger = true;
  } else if (isArray(source)) {
    isMultiSource = true;
    forceTrigger = source.some(s => isReactive(s) || isShallow(s));
    getter = () => source.map(s => {
      if (isRef(s)) {
        return s.value;
      } else if (isReactive(s)) {
        return reactiveGetter(s);
      } else if (isFunction(s)) {
        return call ? call(s, 2) : s();
      } else ;
    });
  } else if (isFunction(source)) {
    if (cb) {
      getter = call ? () => call(source, 2) : source;
    } else {
      getter = () => {
        if (cleanup) {
          pauseTracking();
          try {
            cleanup();
          } finally {
            resetTracking();
          }
        }
        const currentEffect = activeWatcher;
        activeWatcher = effect;
        try {
          return call ? call(source, 3, [boundCleanup]) : source(boundCleanup);
        } finally {
          activeWatcher = currentEffect;
        }
      };
    }
  } else {
    getter = NOOP;
  }
  if (cb && deep) {
    const baseGetter = getter;
    const depth = deep === true ? Infinity : deep;
    getter = () => traverse(baseGetter(), depth);
  }
  const scope = getCurrentScope();
  const watchHandle = () => {
    effect.stop();
    if (scope && scope.active) {
      remove(scope.effects, effect);
    }
  };
  if (once && cb) {
    const _cb = cb;
    cb = function cb() {
      _cb(...arguments);
      watchHandle();
    };
  }
  let oldValue = isMultiSource ? new Array(source.length).fill(INITIAL_WATCHER_VALUE) : INITIAL_WATCHER_VALUE;
  const job = immediateFirstRun => {
    if (!(effect.flags & 1) || !effect.dirty && !immediateFirstRun) {
      return;
    }
    if (cb) {
      const newValue = effect.run();
      if (deep || forceTrigger || (isMultiSource ? newValue.some((v, i) => hasChanged(v, oldValue[i])) : hasChanged(newValue, oldValue))) {
        if (cleanup) {
          cleanup();
        }
        const currentWatcher = activeWatcher;
        activeWatcher = effect;
        try {
          const args = [newValue,
          // pass undefined as the old value when it's changed for the first time
          oldValue === INITIAL_WATCHER_VALUE ? void 0 : isMultiSource && oldValue[0] === INITIAL_WATCHER_VALUE ? [] : oldValue, boundCleanup];
          oldValue = newValue;
          call ? call(cb, 3, args) :
          // @ts-expect-error
          cb(...args);
        } finally {
          activeWatcher = currentWatcher;
        }
      }
    } else {
      effect.run();
    }
  };
  if (augmentJob) {
    augmentJob(job);
  }
  effect = new ReactiveEffect(getter);
  effect.scheduler = scheduler ? () => scheduler(job, false) : job;
  boundCleanup = fn => onWatcherCleanup(fn, false, effect);
  cleanup = effect.onStop = () => {
    const cleanups = cleanupMap.get(effect);
    if (cleanups) {
      if (call) {
        call(cleanups, 4);
      } else {
        for (const cleanup2 of cleanups) cleanup2();
      }
      cleanupMap.delete(effect);
    }
  };
  if (cb) {
    if (immediate) {
      job(true);
    } else {
      oldValue = effect.run();
    }
  } else if (scheduler) {
    scheduler(job.bind(null, true), true);
  } else {
    effect.run();
  }
  watchHandle.pause = effect.pause.bind(effect);
  watchHandle.resume = effect.resume.bind(effect);
  watchHandle.stop = watchHandle;
  return watchHandle;
}
function traverse(value, depth, seen) {
  if (depth === void 0) {
    depth = Infinity;
  }
  if (depth <= 0 || !isObject$1(value) || value["__v_skip"]) {
    return value;
  }
  seen = seen || /* @__PURE__ */new Map();
  if ((seen.get(value) || 0) >= depth) {
    return value;
  }
  seen.set(value, depth);
  depth--;
  if (isRef(value)) {
    traverse(value.value, depth, seen);
  } else if (isArray(value)) {
    for (let i = 0; i < value.length; i++) {
      traverse(value[i], depth, seen);
    }
  } else if (isSet(value) || isMap(value)) {
    value.forEach(v => {
      traverse(v, depth, seen);
    });
  } else if (isPlainObject(value)) {
    for (const key in value) {
      traverse(value[key], depth, seen);
    }
    for (const key of Object.getOwnPropertySymbols(value)) {
      if (Object.prototype.propertyIsEnumerable.call(value, key)) {
        traverse(value[key], depth, seen);
      }
    }
  }
  return value;
}

/**
* @vue/runtime-core v3.5.22
* (c) 2018-present Yuxi (Evan) You and Vue contributors
* @license MIT
**/
const stack = [];
let isWarning = false;
function warn$1(msg) {
  if (isWarning) return;
  isWarning = true;
  pauseTracking();
  const instance = stack.length ? stack[stack.length - 1].component : null;
  const appWarnHandler = instance && instance.appContext.config.warnHandler;
  const trace = getComponentTrace();
  for (var _len = arguments.length, args = new Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
    args[_key - 1] = arguments[_key];
  }
  if (appWarnHandler) {
    callWithErrorHandling(appWarnHandler, instance, 11, [
    // eslint-disable-next-line no-restricted-syntax
    msg + args.map(a => {
      var _a, _b;
      return (_b = (_a = a.toString) == null ? void 0 : _a.call(a)) != null ? _b : JSON.stringify(a);
    }).join(""), instance && instance.proxy, trace.map(_ref => {
      let {
        vnode
      } = _ref;
      return "at <" + formatComponentName(instance, vnode.type) + ">";
    }).join("\n"), trace]);
  } else {
    const warnArgs = ["[Vue warn]: " + msg, ...args];
    if (trace.length &&
    // avoid spamming console during tests
    true) {
      warnArgs.push("\n", ...formatTrace(trace));
    }
    console.warn(...warnArgs);
  }
  resetTracking();
  isWarning = false;
}
function getComponentTrace() {
  let currentVNode = stack[stack.length - 1];
  if (!currentVNode) {
    return [];
  }
  const normalizedStack = [];
  while (currentVNode) {
    const last = normalizedStack[0];
    if (last && last.vnode === currentVNode) {
      last.recurseCount++;
    } else {
      normalizedStack.push({
        vnode: currentVNode,
        recurseCount: 0
      });
    }
    const parentInstance = currentVNode.component && currentVNode.component.parent;
    currentVNode = parentInstance && parentInstance.vnode;
  }
  return normalizedStack;
}
function formatTrace(trace) {
  const logs = [];
  trace.forEach((entry, i) => {
    logs.push(...(i === 0 ? [] : ["\n"]), ...formatTraceEntry(entry));
  });
  return logs;
}
function formatTraceEntry(_ref2) {
  let {
    vnode,
    recurseCount
  } = _ref2;
  const postfix = recurseCount > 0 ? "... (" + recurseCount + " recursive calls)" : "";
  const isRoot = vnode.component ? vnode.component.parent == null : false;
  const open = " at <" + formatComponentName(vnode.component, vnode.type, isRoot);
  const close = ">" + postfix;
  return vnode.props ? [open, ...formatProps(vnode.props), close] : [open + close];
}
function formatProps(props) {
  const res = [];
  const keys = Object.keys(props);
  keys.slice(0, 3).forEach(key => {
    res.push(...formatProp(key, props[key]));
  });
  if (keys.length > 3) {
    res.push(" ...");
  }
  return res;
}
function formatProp(key, value, raw) {
  if (isString(value)) {
    value = JSON.stringify(value);
    return raw ? value : [key + "=" + value];
  } else if (typeof value === "number" || typeof value === "boolean" || value == null) {
    return raw ? value : [key + "=" + value];
  } else if (isRef(value)) {
    value = formatProp(key, toRaw(value.value), true);
    return raw ? value : [key + "=Ref<", value, ">"];
  } else if (isFunction(value)) {
    return [key + "=fn" + (value.name ? "<" + value.name + ">" : "")];
  } else {
    value = toRaw(value);
    return raw ? value : [key + "=", value];
  }
}
function callWithErrorHandling(fn, instance, type, args) {
  try {
    return args ? fn(...args) : fn();
  } catch (err) {
    handleError$1(err, instance, type);
  }
}
function callWithAsyncErrorHandling(fn, instance, type, args) {
  if (isFunction(fn)) {
    const res = callWithErrorHandling(fn, instance, type, args);
    if (res && isPromise$1(res)) {
      res.catch(err => {
        handleError$1(err, instance, type);
      });
    }
    return res;
  }
  if (isArray(fn)) {
    const values = [];
    for (let i = 0; i < fn.length; i++) {
      values.push(callWithAsyncErrorHandling(fn[i], instance, type, args));
    }
    return values;
  }
}
function handleError$1(err, instance, type, throwInDev) {
  if (throwInDev === void 0) {
    throwInDev = true;
  }
  const contextVNode = instance ? instance.vnode : null;
  const {
    errorHandler,
    throwUnhandledErrorInProduction
  } = instance && instance.appContext.config || EMPTY_OBJ;
  if (instance) {
    let cur = instance.parent;
    const exposedInstance = instance.proxy;
    const errorInfo = "https://vuejs.org/error-reference/#runtime-" + type;
    while (cur) {
      const errorCapturedHooks = cur.ec;
      if (errorCapturedHooks) {
        for (let i = 0; i < errorCapturedHooks.length; i++) {
          if (errorCapturedHooks[i](err, exposedInstance, errorInfo) === false) {
            return;
          }
        }
      }
      cur = cur.parent;
    }
    if (errorHandler) {
      pauseTracking();
      callWithErrorHandling(errorHandler, null, 10, [err, exposedInstance, errorInfo]);
      resetTracking();
      return;
    }
  }
  logError(err, type, contextVNode, throwInDev, throwUnhandledErrorInProduction);
}
function logError(err, type, contextVNode, throwInDev, throwInProd) {
  if (throwInProd === void 0) {
    throwInProd = false;
  }
  if (throwInProd) {
    throw err;
  } else {
    console.error(err);
  }
}
const queue = [];
let flushIndex = -1;
const pendingPostFlushCbs = [];
let activePostFlushCbs = null;
let postFlushIndex = 0;
const resolvedPromise = /* @__PURE__ */Promise.resolve();
let currentFlushPromise = null;
function nextTick(fn) {
  const p = currentFlushPromise || resolvedPromise;
  return fn ? p.then(this ? fn.bind(this) : fn) : p;
}
function findInsertionIndex(id) {
  let start = flushIndex + 1;
  let end = queue.length;
  while (start < end) {
    const middle = start + end >>> 1;
    const middleJob = queue[middle];
    const middleJobId = getId(middleJob);
    if (middleJobId < id || middleJobId === id && middleJob.flags & 2) {
      start = middle + 1;
    } else {
      end = middle;
    }
  }
  return start;
}
function queueJob(job) {
  if (!(job.flags & 1)) {
    const jobId = getId(job);
    const lastJob = queue[queue.length - 1];
    if (!lastJob ||
    // fast path when the job id is larger than the tail
    !(job.flags & 2) && jobId >= getId(lastJob)) {
      queue.push(job);
    } else {
      queue.splice(findInsertionIndex(jobId), 0, job);
    }
    job.flags |= 1;
    queueFlush();
  }
}
function queueFlush() {
  if (!currentFlushPromise) {
    currentFlushPromise = resolvedPromise.then(flushJobs);
  }
}
function queuePostFlushCb(cb) {
  if (!isArray(cb)) {
    if (activePostFlushCbs && cb.id === -1) {
      activePostFlushCbs.splice(postFlushIndex + 1, 0, cb);
    } else if (!(cb.flags & 1)) {
      pendingPostFlushCbs.push(cb);
      cb.flags |= 1;
    }
  } else {
    pendingPostFlushCbs.push(...cb);
  }
  queueFlush();
}
function flushPreFlushCbs(instance, seen, i) {
  if (i === void 0) {
    i = flushIndex + 1;
  }
  for (; i < queue.length; i++) {
    const cb = queue[i];
    if (cb && cb.flags & 2) {
      if (instance && cb.id !== instance.uid) {
        continue;
      }
      queue.splice(i, 1);
      i--;
      if (cb.flags & 4) {
        cb.flags &= -2;
      }
      cb();
      if (!(cb.flags & 4)) {
        cb.flags &= -2;
      }
    }
  }
}
function flushPostFlushCbs(seen) {
  if (pendingPostFlushCbs.length) {
    const deduped = [...new Set(pendingPostFlushCbs)].sort((a, b) => getId(a) - getId(b));
    pendingPostFlushCbs.length = 0;
    if (activePostFlushCbs) {
      activePostFlushCbs.push(...deduped);
      return;
    }
    activePostFlushCbs = deduped;
    for (postFlushIndex = 0; postFlushIndex < activePostFlushCbs.length; postFlushIndex++) {
      const cb = activePostFlushCbs[postFlushIndex];
      if (cb.flags & 4) {
        cb.flags &= -2;
      }
      if (!(cb.flags & 8)) cb();
      cb.flags &= -2;
    }
    activePostFlushCbs = null;
    postFlushIndex = 0;
  }
}
const getId = job => job.id == null ? job.flags & 2 ? -1 : Infinity : job.id;
function flushJobs(seen) {
  const check = NOOP;
  try {
    for (flushIndex = 0; flushIndex < queue.length; flushIndex++) {
      const job = queue[flushIndex];
      if (job && !(job.flags & 8)) {
        if (!!("production" !== "production") && check(job)) ;
        if (job.flags & 4) {
          job.flags &= ~1;
        }
        callWithErrorHandling(job, job.i, job.i ? 15 : 14);
        if (!(job.flags & 4)) {
          job.flags &= ~1;
        }
      }
    }
  } finally {
    for (; flushIndex < queue.length; flushIndex++) {
      const job = queue[flushIndex];
      if (job) {
        job.flags &= -2;
      }
    }
    flushIndex = -1;
    queue.length = 0;
    flushPostFlushCbs();
    currentFlushPromise = null;
    if (queue.length || pendingPostFlushCbs.length) {
      flushJobs();
    }
  }
}
let currentRenderingInstance = null;
let currentScopeId = null;
function setCurrentRenderingInstance(instance) {
  const prev = currentRenderingInstance;
  currentRenderingInstance = instance;
  currentScopeId = instance && instance.type.__scopeId || null;
  return prev;
}
function withCtx(fn, ctx, isNonScopedSlot) {
  if (ctx === void 0) {
    ctx = currentRenderingInstance;
  }
  if (!ctx) return fn;
  if (fn._n) {
    return fn;
  }
  const _renderFnWithContext = function renderFnWithContext() {
    if (_renderFnWithContext._d) {
      setBlockTracking(-1);
    }
    const prevInstance = setCurrentRenderingInstance(ctx);
    let res;
    try {
      res = fn(...arguments);
    } finally {
      setCurrentRenderingInstance(prevInstance);
      if (_renderFnWithContext._d) {
        setBlockTracking(1);
      }
    }
    return res;
  };
  _renderFnWithContext._n = true;
  _renderFnWithContext._c = true;
  _renderFnWithContext._d = true;
  return _renderFnWithContext;
}
function withDirectives(vnode, directives) {
  if (currentRenderingInstance === null) {
    return vnode;
  }
  const instance = getComponentPublicInstance(currentRenderingInstance);
  const bindings = vnode.dirs || (vnode.dirs = []);
  for (let i = 0; i < directives.length; i++) {
    let [dir, value, arg, modifiers = EMPTY_OBJ] = directives[i];
    if (dir) {
      if (isFunction(dir)) {
        dir = {
          mounted: dir,
          updated: dir
        };
      }
      if (dir.deep) {
        traverse(value);
      }
      bindings.push({
        dir,
        instance,
        value,
        oldValue: void 0,
        arg,
        modifiers
      });
    }
  }
  return vnode;
}
function invokeDirectiveHook(vnode, prevVNode, instance, name) {
  const bindings = vnode.dirs;
  const oldBindings = prevVNode && prevVNode.dirs;
  for (let i = 0; i < bindings.length; i++) {
    const binding = bindings[i];
    if (oldBindings) {
      binding.oldValue = oldBindings[i].value;
    }
    let hook = binding.dir[name];
    if (hook) {
      pauseTracking();
      callWithAsyncErrorHandling(hook, instance, 8, [vnode.el, binding, vnode, prevVNode]);
      resetTracking();
    }
  }
}
const TeleportEndKey = Symbol("_vte");
const isTeleport = type => type.__isTeleport;
const leaveCbKey = Symbol("_leaveCb");
const enterCbKey = Symbol("_enterCb");
function useTransitionState() {
  const state = {
    isMounted: false,
    isLeaving: false,
    isUnmounting: false,
    leavingVNodes: /* @__PURE__ */new Map()
  };
  onMounted(() => {
    state.isMounted = true;
  });
  onBeforeUnmount(() => {
    state.isUnmounting = true;
  });
  return state;
}
const TransitionHookValidator = [Function, Array];
const BaseTransitionPropsValidators = {
  mode: String,
  appear: Boolean,
  persisted: Boolean,
  // enter
  onBeforeEnter: TransitionHookValidator,
  onEnter: TransitionHookValidator,
  onAfterEnter: TransitionHookValidator,
  onEnterCancelled: TransitionHookValidator,
  // leave
  onBeforeLeave: TransitionHookValidator,
  onLeave: TransitionHookValidator,
  onAfterLeave: TransitionHookValidator,
  onLeaveCancelled: TransitionHookValidator,
  // appear
  onBeforeAppear: TransitionHookValidator,
  onAppear: TransitionHookValidator,
  onAfterAppear: TransitionHookValidator,
  onAppearCancelled: TransitionHookValidator
};
const recursiveGetSubtree = instance => {
  const subTree = instance.subTree;
  return subTree.component ? recursiveGetSubtree(subTree.component) : subTree;
};
const BaseTransitionImpl = {
  name: "BaseTransition",
  props: BaseTransitionPropsValidators,
  setup(props, _ref7) {
    let {
      slots
    } = _ref7;
    const instance = getCurrentInstance();
    const state = useTransitionState();
    return () => {
      const children = slots.default && getTransitionRawChildren(slots.default(), true);
      if (!children || !children.length) {
        return;
      }
      const child = findNonCommentChild(children);
      const rawProps = toRaw(props);
      const {
        mode
      } = rawProps;
      if (state.isLeaving) {
        return emptyPlaceholder(child);
      }
      const innerChild = getInnerChild$1(child);
      if (!innerChild) {
        return emptyPlaceholder(child);
      }
      let enterHooks = resolveTransitionHooks(innerChild, rawProps, state, instance,
      // #11061, ensure enterHooks is fresh after clone
      hooks => enterHooks = hooks);
      if (innerChild.type !== Comment) {
        setTransitionHooks(innerChild, enterHooks);
      }
      let oldInnerChild = instance.subTree && getInnerChild$1(instance.subTree);
      if (oldInnerChild && oldInnerChild.type !== Comment && !isSameVNodeType(oldInnerChild, innerChild) && recursiveGetSubtree(instance).type !== Comment) {
        let leavingHooks = resolveTransitionHooks(oldInnerChild, rawProps, state, instance);
        setTransitionHooks(oldInnerChild, leavingHooks);
        if (mode === "out-in" && innerChild.type !== Comment) {
          state.isLeaving = true;
          leavingHooks.afterLeave = () => {
            state.isLeaving = false;
            if (!(instance.job.flags & 8)) {
              instance.update();
            }
            delete leavingHooks.afterLeave;
            oldInnerChild = void 0;
          };
          return emptyPlaceholder(child);
        } else if (mode === "in-out" && innerChild.type !== Comment) {
          leavingHooks.delayLeave = (el, earlyRemove, delayedLeave) => {
            const leavingVNodesCache = getLeavingNodesForType(state, oldInnerChild);
            leavingVNodesCache[String(oldInnerChild.key)] = oldInnerChild;
            el[leaveCbKey] = () => {
              earlyRemove();
              el[leaveCbKey] = void 0;
              delete enterHooks.delayedLeave;
              oldInnerChild = void 0;
            };
            enterHooks.delayedLeave = () => {
              delayedLeave();
              delete enterHooks.delayedLeave;
              oldInnerChild = void 0;
            };
          };
        } else {
          oldInnerChild = void 0;
        }
      } else if (oldInnerChild) {
        oldInnerChild = void 0;
      }
      return child;
    };
  }
};
function findNonCommentChild(children) {
  let child = children[0];
  if (children.length > 1) {
    for (const c of children) {
      if (c.type !== Comment) {
        child = c;
        break;
      }
    }
  }
  return child;
}
const BaseTransition = BaseTransitionImpl;
function getLeavingNodesForType(state, vnode) {
  const {
    leavingVNodes
  } = state;
  let leavingVNodesCache = leavingVNodes.get(vnode.type);
  if (!leavingVNodesCache) {
    leavingVNodesCache = /* @__PURE__ */Object.create(null);
    leavingVNodes.set(vnode.type, leavingVNodesCache);
  }
  return leavingVNodesCache;
}
function resolveTransitionHooks(vnode, props, state, instance, postClone) {
  const {
    appear,
    mode,
    persisted = false,
    onBeforeEnter,
    onEnter,
    onAfterEnter,
    onEnterCancelled,
    onBeforeLeave,
    onLeave,
    onAfterLeave,
    onLeaveCancelled,
    onBeforeAppear,
    onAppear,
    onAfterAppear,
    onAppearCancelled
  } = props;
  const key = String(vnode.key);
  const leavingVNodesCache = getLeavingNodesForType(state, vnode);
  const callHook = (hook, args) => {
    hook && callWithAsyncErrorHandling(hook, instance, 9, args);
  };
  const callAsyncHook = (hook, args) => {
    const done = args[1];
    callHook(hook, args);
    if (isArray(hook)) {
      if (hook.every(hook2 => hook2.length <= 1)) done();
    } else if (hook.length <= 1) {
      done();
    }
  };
  const hooks = {
    mode,
    persisted,
    beforeEnter(el) {
      let hook = onBeforeEnter;
      if (!state.isMounted) {
        if (appear) {
          hook = onBeforeAppear || onBeforeEnter;
        } else {
          return;
        }
      }
      if (el[leaveCbKey]) {
        el[leaveCbKey](true
        /* cancelled */);
      }
      const leavingVNode = leavingVNodesCache[key];
      if (leavingVNode && isSameVNodeType(vnode, leavingVNode) && leavingVNode.el[leaveCbKey]) {
        leavingVNode.el[leaveCbKey]();
      }
      callHook(hook, [el]);
    },
    enter(el) {
      let hook = onEnter;
      let afterHook = onAfterEnter;
      let cancelHook = onEnterCancelled;
      if (!state.isMounted) {
        if (appear) {
          hook = onAppear || onEnter;
          afterHook = onAfterAppear || onAfterEnter;
          cancelHook = onAppearCancelled || onEnterCancelled;
        } else {
          return;
        }
      }
      let called = false;
      const done = el[enterCbKey] = cancelled => {
        if (called) return;
        called = true;
        if (cancelled) {
          callHook(cancelHook, [el]);
        } else {
          callHook(afterHook, [el]);
        }
        if (hooks.delayedLeave) {
          hooks.delayedLeave();
        }
        el[enterCbKey] = void 0;
      };
      if (hook) {
        callAsyncHook(hook, [el, done]);
      } else {
        done();
      }
    },
    leave(el, remove) {
      const key2 = String(vnode.key);
      if (el[enterCbKey]) {
        el[enterCbKey](true
        /* cancelled */);
      }
      if (state.isUnmounting) {
        return remove();
      }
      callHook(onBeforeLeave, [el]);
      let called = false;
      const done = el[leaveCbKey] = cancelled => {
        if (called) return;
        called = true;
        remove();
        if (cancelled) {
          callHook(onLeaveCancelled, [el]);
        } else {
          callHook(onAfterLeave, [el]);
        }
        el[leaveCbKey] = void 0;
        if (leavingVNodesCache[key2] === vnode) {
          delete leavingVNodesCache[key2];
        }
      };
      leavingVNodesCache[key2] = vnode;
      if (onLeave) {
        callAsyncHook(onLeave, [el, done]);
      } else {
        done();
      }
    },
    clone(vnode2) {
      const hooks2 = resolveTransitionHooks(vnode2, props, state, instance, postClone);
      if (postClone) postClone(hooks2);
      return hooks2;
    }
  };
  return hooks;
}
function emptyPlaceholder(vnode) {
  if (isKeepAlive(vnode)) {
    vnode = cloneVNode(vnode);
    vnode.children = null;
    return vnode;
  }
}
function getInnerChild$1(vnode) {
  if (!isKeepAlive(vnode)) {
    if (isTeleport(vnode.type) && vnode.children) {
      return findNonCommentChild(vnode.children);
    }
    return vnode;
  }
  if (vnode.component) {
    return vnode.component.subTree;
  }
  const {
    shapeFlag,
    children
  } = vnode;
  if (children) {
    if (shapeFlag & 16) {
      return children[0];
    }
    if (shapeFlag & 32 && isFunction(children.default)) {
      return children.default();
    }
  }
}
function setTransitionHooks(vnode, hooks) {
  if (vnode.shapeFlag & 6 && vnode.component) {
    vnode.transition = hooks;
    setTransitionHooks(vnode.component.subTree, hooks);
  } else if (vnode.shapeFlag & 128) {
    vnode.ssContent.transition = hooks.clone(vnode.ssContent);
    vnode.ssFallback.transition = hooks.clone(vnode.ssFallback);
  } else {
    vnode.transition = hooks;
  }
}
function getTransitionRawChildren(children, keepComment, parentKey) {
  let ret = [];
  let keyedFragmentCount = 0;
  for (let i = 0; i < children.length; i++) {
    let child = children[i];
    const key = parentKey == null ? child.key : String(parentKey) + String(child.key != null ? child.key : i);
    if (child.type === Fragment) {
      if (child.patchFlag & 128) keyedFragmentCount++;
      ret = ret.concat(getTransitionRawChildren(child.children, keepComment, key));
    } else {
      ret.push(key != null ? cloneVNode(child, {
        key
      }) : child);
    }
  }
  if (keyedFragmentCount > 1) {
    for (let i = 0; i < ret.length; i++) {
      ret[i].patchFlag = -2;
    }
  }
  return ret;
}
function markAsyncBoundary(instance) {
  instance.ids = [instance.ids[0] + instance.ids[2]++ + "-", 0, 0];
}
const pendingSetRefMap = /* @__PURE__ */new WeakMap();
function setRef(rawRef, oldRawRef, parentSuspense, vnode, isUnmount) {
  if (isUnmount === void 0) {
    isUnmount = false;
  }
  if (isArray(rawRef)) {
    rawRef.forEach((r, i) => setRef(r, oldRawRef && (isArray(oldRawRef) ? oldRawRef[i] : oldRawRef), parentSuspense, vnode, isUnmount));
    return;
  }
  if (isAsyncWrapper(vnode) && !isUnmount) {
    if (vnode.shapeFlag & 512 && vnode.type.__asyncResolved && vnode.component.subTree.component) {
      setRef(rawRef, oldRawRef, parentSuspense, vnode.component.subTree);
    }
    return;
  }
  const refValue = vnode.shapeFlag & 4 ? getComponentPublicInstance(vnode.component) : vnode.el;
  const value = isUnmount ? null : refValue;
  const {
    i: owner,
    r: ref
  } = rawRef;
  const oldRef = oldRawRef && oldRawRef.r;
  const refs = owner.refs === EMPTY_OBJ ? owner.refs = {} : owner.refs;
  const setupState = owner.setupState;
  const rawSetupState = toRaw(setupState);
  const canSetSetupRef = setupState === EMPTY_OBJ ? NO : key => {
    return hasOwn(rawSetupState, key);
  };
  if (oldRef != null && oldRef !== ref) {
    invalidatePendingSetRef(oldRawRef);
    if (isString(oldRef)) {
      refs[oldRef] = null;
      if (canSetSetupRef(oldRef)) {
        setupState[oldRef] = null;
      }
    } else if (isRef(oldRef)) {
      {
        oldRef.value = null;
      }
      const oldRawRefAtom = oldRawRef;
      if (oldRawRefAtom.k) refs[oldRawRefAtom.k] = null;
    }
  }
  if (isFunction(ref)) {
    callWithErrorHandling(ref, owner, 12, [value, refs]);
  } else {
    const _isString = isString(ref);
    const _isRef = isRef(ref);
    if (_isString || _isRef) {
      const doSet = () => {
        if (rawRef.f) {
          const existing = _isString ? canSetSetupRef(ref) ? setupState[ref] : refs[ref] : ref.value ;
          if (isUnmount) {
            isArray(existing) && remove(existing, refValue);
          } else {
            if (!isArray(existing)) {
              if (_isString) {
                refs[ref] = [refValue];
                if (canSetSetupRef(ref)) {
                  setupState[ref] = refs[ref];
                }
              } else {
                const newVal = [refValue];
                {
                  ref.value = newVal;
                }
                if (rawRef.k) refs[rawRef.k] = newVal;
              }
            } else if (!existing.includes(refValue)) {
              existing.push(refValue);
            }
          }
        } else if (_isString) {
          refs[ref] = value;
          if (canSetSetupRef(ref)) {
            setupState[ref] = value;
          }
        } else if (_isRef) {
          {
            ref.value = value;
          }
          if (rawRef.k) refs[rawRef.k] = value;
        } else ;
      };
      if (value) {
        const job = () => {
          doSet();
          pendingSetRefMap.delete(rawRef);
        };
        job.id = -1;
        pendingSetRefMap.set(rawRef, job);
        queuePostRenderEffect(job, parentSuspense);
      } else {
        invalidatePendingSetRef(rawRef);
        doSet();
      }
    }
  }
}
function invalidatePendingSetRef(rawRef) {
  const pendingSetRef = pendingSetRefMap.get(rawRef);
  if (pendingSetRef) {
    pendingSetRef.flags |= 8;
    pendingSetRefMap.delete(rawRef);
  }
}
getGlobalThis().requestIdleCallback || (cb => setTimeout(cb, 1));
getGlobalThis().cancelIdleCallback || (id => clearTimeout(id));
const isAsyncWrapper = i => !!i.type.__asyncLoader;
const isKeepAlive = vnode => vnode.type.__isKeepAlive;
function onActivated(hook, target) {
  registerKeepAliveHook(hook, "a", target);
}
function onDeactivated(hook, target) {
  registerKeepAliveHook(hook, "da", target);
}
function registerKeepAliveHook(hook, type, target) {
  if (target === void 0) {
    target = currentInstance;
  }
  const wrappedHook = hook.__wdc || (hook.__wdc = () => {
    let current = target;
    while (current) {
      if (current.isDeactivated) {
        return;
      }
      current = current.parent;
    }
    return hook();
  });
  injectHook(type, wrappedHook, target);
  if (target) {
    let current = target.parent;
    while (current && current.parent) {
      if (isKeepAlive(current.parent.vnode)) {
        injectToKeepAliveRoot(wrappedHook, type, target, current);
      }
      current = current.parent;
    }
  }
}
function injectToKeepAliveRoot(hook, type, target, keepAliveRoot) {
  const injected = injectHook(type, hook, keepAliveRoot, true
  /* prepend */);
  onUnmounted(() => {
    remove(keepAliveRoot[type], injected);
  }, target);
}
function injectHook(type, hook, target, prepend) {
  if (target === void 0) {
    target = currentInstance;
  }
  if (prepend === void 0) {
    prepend = false;
  }
  if (target) {
    const hooks = target[type] || (target[type] = []);
    const wrappedHook = hook.__weh || (hook.__weh = function () {
      pauseTracking();
      const reset = setCurrentInstance(target);
      for (var _len3 = arguments.length, args = new Array(_len3), _key3 = 0; _key3 < _len3; _key3++) {
        args[_key3] = arguments[_key3];
      }
      const res = callWithAsyncErrorHandling(hook, target, type, args);
      reset();
      resetTracking();
      return res;
    });
    if (prepend) {
      hooks.unshift(wrappedHook);
    } else {
      hooks.push(wrappedHook);
    }
    return wrappedHook;
  }
}
const createHook = lifecycle => function (hook, target) {
  if (target === void 0) {
    target = currentInstance;
  }
  if (!isInSSRComponentSetup || lifecycle === "sp") {
    injectHook(lifecycle, function () {
      return hook(...arguments);
    }, target);
  }
};
const onBeforeMount = createHook("bm");
const onMounted = createHook("m");
const onBeforeUpdate = createHook("bu");
const onUpdated = createHook("u");
const onBeforeUnmount = createHook("bum");
const onUnmounted = createHook("um");
const onServerPrefetch = createHook("sp");
const onRenderTriggered = createHook("rtg");
const onRenderTracked = createHook("rtc");
function onErrorCaptured(hook, target) {
  if (target === void 0) {
    target = currentInstance;
  }
  injectHook("ec", hook, target);
}
const COMPONENTS = "components";
function resolveComponent(name, maybeSelfReference) {
  return resolveAsset(COMPONENTS, name, true, maybeSelfReference) || name;
}
const NULL_DYNAMIC_COMPONENT = Symbol.for("v-ndc");
function resolveAsset(type, name, warnMissing, maybeSelfReference) {
  if (maybeSelfReference === void 0) {
    maybeSelfReference = false;
  }
  const instance = currentRenderingInstance || currentInstance;
  if (instance) {
    const Component = instance.type;
    {
      const selfName = getComponentName(Component, false);
      if (selfName && (selfName === name || selfName === camelize(name) || selfName === capitalize(camelize(name)))) {
        return Component;
      }
    }
    const res =
    // local registration
    // check instance[type] first which is resolved for options API
    resolve(instance[type] || Component[type], name) ||
    // global registration
    resolve(instance.appContext[type], name);
    if (!res && maybeSelfReference) {
      return Component;
    }
    return res;
  }
}
function resolve(registry, name) {
  return registry && (registry[name] || registry[camelize(name)] || registry[capitalize(camelize(name))]);
}
function renderList(source, renderItem, cache, index) {
  let ret;
  const cached = cache;
  const sourceIsArray = isArray(source);
  if (sourceIsArray || isString(source)) {
    const sourceIsReactiveArray = sourceIsArray && isReactive(source);
    let needsWrap = false;
    let isReadonlySource = false;
    if (sourceIsReactiveArray) {
      needsWrap = !isShallow(source);
      isReadonlySource = isReadonly(source);
      source = shallowReadArray(source);
    }
    ret = new Array(source.length);
    for (let i = 0, l = source.length; i < l; i++) {
      ret[i] = renderItem(needsWrap ? isReadonlySource ? toReadonly(toReactive(source[i])) : toReactive(source[i]) : source[i], i, void 0, cached);
    }
  } else if (typeof source === "number") {
    ret = new Array(source);
    for (let i = 0; i < source; i++) {
      ret[i] = renderItem(i + 1, i, void 0, cached);
    }
  } else if (isObject$1(source)) {
    if (source[Symbol.iterator]) {
      ret = Array.from(source, (item, i) => renderItem(item, i, void 0, cached));
    } else {
      const keys = Object.keys(source);
      ret = new Array(keys.length);
      for (let i = 0, l = keys.length; i < l; i++) {
        const key = keys[i];
        ret[i] = renderItem(source[key], key, i, cached);
      }
    }
  } else {
    ret = [];
  }
  return ret;
}
function renderSlot(slots, name, props, fallback, noSlotted) {
  if (props === void 0) {
    props = {};
  }
  if (currentRenderingInstance.ce || currentRenderingInstance.parent && isAsyncWrapper(currentRenderingInstance.parent) && currentRenderingInstance.parent.ce) {
    const hasProps = Object.keys(props).length > 0;
    if (name !== "default") props.name = name;
    return openBlock(), createBlock(Fragment, null, [createVNode("slot", props, fallback)], hasProps ? -2 : 64);
  }
  let slot = slots[name];
  if (slot && slot._c) {
    slot._d = false;
  }
  openBlock();
  const validSlotContent = slot && ensureValidVNode(slot(props));
  const slotKey = props.key ||
  // slot content array of a dynamic conditional slot may have a branch
  // key attached in the `createSlots` helper, respect that
  validSlotContent && validSlotContent.key;
  const rendered = createBlock(Fragment, {
    key: (slotKey && !isSymbol(slotKey) ? slotKey : "_" + name) + (
    // #7256 force differentiate fallback content from actual content
    !validSlotContent && fallback ? "_fb" : "")
  }, validSlotContent || ([]), validSlotContent && slots._ === 1 ? 64 : -2);
  if (rendered.scopeId) {
    rendered.slotScopeIds = [rendered.scopeId + "-s"];
  }
  if (slot && slot._c) {
    slot._d = true;
  }
  return rendered;
}
function ensureValidVNode(vnodes) {
  return vnodes.some(child => {
    if (!isVNode(child)) return true;
    if (child.type === Comment) return false;
    if (child.type === Fragment && !ensureValidVNode(child.children)) return false;
    return true;
  }) ? vnodes : null;
}
const getPublicInstance = i => {
  if (!i) return null;
  if (isStatefulComponent(i)) return getComponentPublicInstance(i);
  return getPublicInstance(i.parent);
};
const publicPropertiesMap =
// Move PURE marker to new line to workaround compiler discarding it
// due to type annotation
/* @__PURE__ */
extend(/* @__PURE__ */Object.create(null), {
  $: i => i,
  $el: i => i.vnode.el,
  $data: i => i.data,
  $props: i => i.props,
  $attrs: i => i.attrs,
  $slots: i => i.slots,
  $refs: i => i.refs,
  $parent: i => getPublicInstance(i.parent),
  $root: i => getPublicInstance(i.root),
  $host: i => i.ce,
  $emit: i => i.emit,
  $options: i => resolveMergedOptions(i) ,
  $forceUpdate: i => i.f || (i.f = () => {
    queueJob(i.update);
  }),
  $nextTick: i => i.n || (i.n = nextTick.bind(i.proxy)),
  $watch: i => instanceWatch.bind(i) 
});
const hasSetupBinding = (state, key) => state !== EMPTY_OBJ && !state.__isScriptSetup && hasOwn(state, key);
const PublicInstanceProxyHandlers = {
  get(_ref0, key) {
    let {
      _: instance
    } = _ref0;
    if (key === "__v_skip") {
      return true;
    }
    const {
      ctx,
      setupState,
      data,
      props,
      accessCache,
      type,
      appContext
    } = instance;
    let normalizedProps;
    if (key[0] !== "$") {
      const n = accessCache[key];
      if (n !== void 0) {
        switch (n) {
          case 1 /* SETUP */:
            return setupState[key];
          case 2 /* DATA */:
            return data[key];
          case 4 /* CONTEXT */:
            return ctx[key];
          case 3 /* PROPS */:
            return props[key];
        }
      } else if (hasSetupBinding(setupState, key)) {
        accessCache[key] = 1 /* SETUP */;
        return setupState[key];
      } else if (data !== EMPTY_OBJ && hasOwn(data, key)) {
        accessCache[key] = 2 /* DATA */;
        return data[key];
      } else if (
      // only cache other properties when instance has declared (thus stable)
      // props
      (normalizedProps = instance.propsOptions[0]) && hasOwn(normalizedProps, key)) {
        accessCache[key] = 3 /* PROPS */;
        return props[key];
      } else if (ctx !== EMPTY_OBJ && hasOwn(ctx, key)) {
        accessCache[key] = 4 /* CONTEXT */;
        return ctx[key];
      } else if (shouldCacheAccess) {
        accessCache[key] = 0 /* OTHER */;
      }
    }
    const publicGetter = publicPropertiesMap[key];
    let cssModule, globalProperties;
    if (publicGetter) {
      if (key === "$attrs") {
        track(instance.attrs, "get", "");
      }
      return publicGetter(instance);
    } else if (
    // css module (injected by vue-loader)
    (cssModule = type.__cssModules) && (cssModule = cssModule[key])) {
      return cssModule;
    } else if (ctx !== EMPTY_OBJ && hasOwn(ctx, key)) {
      accessCache[key] = 4 /* CONTEXT */;
      return ctx[key];
    } else if (
    // global properties
    globalProperties = appContext.config.globalProperties, hasOwn(globalProperties, key)) {
      {
        return globalProperties[key];
      }
    } else ;
  },
  set(_ref1, key, value) {
    let {
      _: instance
    } = _ref1;
    const {
      data,
      setupState,
      ctx
    } = instance;
    if (hasSetupBinding(setupState, key)) {
      setupState[key] = value;
      return true;
    } else if (data !== EMPTY_OBJ && hasOwn(data, key)) {
      data[key] = value;
      return true;
    } else if (hasOwn(instance.props, key)) {
      return false;
    }
    if (key[0] === "$" && key.slice(1) in instance) {
      return false;
    } else {
      {
        ctx[key] = value;
      }
    }
    return true;
  },
  has(_ref10, key) {
    let {
      _: {
        data,
        setupState,
        accessCache,
        ctx,
        appContext,
        propsOptions,
        type
      }
    } = _ref10;
    let normalizedProps, cssModules;
    return !!(accessCache[key] || data !== EMPTY_OBJ && key[0] !== "$" && hasOwn(data, key) || hasSetupBinding(setupState, key) || (normalizedProps = propsOptions[0]) && hasOwn(normalizedProps, key) || hasOwn(ctx, key) || hasOwn(publicPropertiesMap, key) || hasOwn(appContext.config.globalProperties, key) || (cssModules = type.__cssModules) && cssModules[key]);
  },
  defineProperty(target, key, descriptor) {
    if (descriptor.get != null) {
      target._.accessCache[key] = 0;
    } else if (hasOwn(descriptor, "value")) {
      this.set(target, key, descriptor.value, null);
    }
    return Reflect.defineProperty(target, key, descriptor);
  }
};
function normalizePropsOrEmits(props) {
  return isArray(props) ? props.reduce((normalized, p) => (normalized[p] = null, normalized), {}) : props;
}
let shouldCacheAccess = true;
function applyOptions(instance) {
  const options = resolveMergedOptions(instance);
  const publicThis = instance.proxy;
  const ctx = instance.ctx;
  shouldCacheAccess = false;
  if (options.beforeCreate) {
    callHook$1(options.beforeCreate, instance, "bc");
  }
  const {
    // state
    data: dataOptions,
    computed: computedOptions,
    methods,
    watch: watchOptions,
    provide: provideOptions,
    inject: injectOptions,
    // lifecycle
    created,
    beforeMount,
    mounted,
    beforeUpdate,
    updated,
    activated,
    deactivated,
    beforeDestroy,
    beforeUnmount,
    destroyed,
    unmounted,
    render,
    renderTracked,
    renderTriggered,
    errorCaptured,
    serverPrefetch,
    // public API
    expose,
    inheritAttrs,
    // assets
    components,
    directives,
    filters
  } = options;
  if (injectOptions) {
    resolveInjections(injectOptions, ctx);
  }
  if (methods) {
    for (const key in methods) {
      const methodHandler = methods[key];
      if (isFunction(methodHandler)) {
        {
          ctx[key] = methodHandler.bind(publicThis);
        }
      }
    }
  }
  if (dataOptions) {
    const data = dataOptions.call(publicThis, publicThis);
    if (!isObject$1(data)) ; else {
      instance.data = reactive(data);
    }
  }
  shouldCacheAccess = true;
  if (computedOptions) {
    for (const key in computedOptions) {
      const opt = computedOptions[key];
      const get = isFunction(opt) ? opt.bind(publicThis, publicThis) : isFunction(opt.get) ? opt.get.bind(publicThis, publicThis) : NOOP;
      const set = !isFunction(opt) && isFunction(opt.set) ? opt.set.bind(publicThis) : NOOP;
      const c = computed({
        get,
        set
      });
      Object.defineProperty(ctx, key, {
        enumerable: true,
        configurable: true,
        get: () => c.value,
        set: v => c.value = v
      });
    }
  }
  if (watchOptions) {
    for (const key in watchOptions) {
      createWatcher(watchOptions[key], ctx, publicThis, key);
    }
  }
  if (provideOptions) {
    const provides = isFunction(provideOptions) ? provideOptions.call(publicThis) : provideOptions;
    Reflect.ownKeys(provides).forEach(key => {
      provide(key, provides[key]);
    });
  }
  if (created) {
    callHook$1(created, instance, "c");
  }
  function registerLifecycleHook(register, hook) {
    if (isArray(hook)) {
      hook.forEach(_hook => register(_hook.bind(publicThis)));
    } else if (hook) {
      register(hook.bind(publicThis));
    }
  }
  registerLifecycleHook(onBeforeMount, beforeMount);
  registerLifecycleHook(onMounted, mounted);
  registerLifecycleHook(onBeforeUpdate, beforeUpdate);
  registerLifecycleHook(onUpdated, updated);
  registerLifecycleHook(onActivated, activated);
  registerLifecycleHook(onDeactivated, deactivated);
  registerLifecycleHook(onErrorCaptured, errorCaptured);
  registerLifecycleHook(onRenderTracked, renderTracked);
  registerLifecycleHook(onRenderTriggered, renderTriggered);
  registerLifecycleHook(onBeforeUnmount, beforeUnmount);
  registerLifecycleHook(onUnmounted, unmounted);
  registerLifecycleHook(onServerPrefetch, serverPrefetch);
  if (isArray(expose)) {
    if (expose.length) {
      const exposed = instance.exposed || (instance.exposed = {});
      expose.forEach(key => {
        Object.defineProperty(exposed, key, {
          get: () => publicThis[key],
          set: val => publicThis[key] = val,
          enumerable: true
        });
      });
    } else if (!instance.exposed) {
      instance.exposed = {};
    }
  }
  if (render && instance.render === NOOP) {
    instance.render = render;
  }
  if (inheritAttrs != null) {
    instance.inheritAttrs = inheritAttrs;
  }
  if (components) instance.components = components;
  if (directives) instance.directives = directives;
  if (serverPrefetch) {
    markAsyncBoundary(instance);
  }
}
function resolveInjections(injectOptions, ctx, checkDuplicateProperties) {
  if (isArray(injectOptions)) {
    injectOptions = normalizeInject(injectOptions);
  }
  for (const key in injectOptions) {
    const opt = injectOptions[key];
    let injected;
    if (isObject$1(opt)) {
      if ("default" in opt) {
        injected = inject(opt.from || key, opt.default, true);
      } else {
        injected = inject(opt.from || key);
      }
    } else {
      injected = inject(opt);
    }
    if (isRef(injected)) {
      Object.defineProperty(ctx, key, {
        enumerable: true,
        configurable: true,
        get: () => injected.value,
        set: v => injected.value = v
      });
    } else {
      ctx[key] = injected;
    }
  }
}
function callHook$1(hook, instance, type) {
  callWithAsyncErrorHandling(isArray(hook) ? hook.map(h => h.bind(instance.proxy)) : hook.bind(instance.proxy), instance, type);
}
function createWatcher(raw, ctx, publicThis, key) {
  let getter = key.includes(".") ? createPathGetter(publicThis, key) : () => publicThis[key];
  if (isString(raw)) {
    const handler = ctx[raw];
    if (isFunction(handler)) {
      {
        watch(getter, handler);
      }
    }
  } else if (isFunction(raw)) {
    {
      watch(getter, raw.bind(publicThis));
    }
  } else if (isObject$1(raw)) {
    if (isArray(raw)) {
      raw.forEach(r => createWatcher(r, ctx, publicThis, key));
    } else {
      const handler = isFunction(raw.handler) ? raw.handler.bind(publicThis) : ctx[raw.handler];
      if (isFunction(handler)) {
        watch(getter, handler, raw);
      }
    }
  } else ;
}
function resolveMergedOptions(instance) {
  const base = instance.type;
  const {
    mixins,
    extends: extendsOptions
  } = base;
  const {
    mixins: globalMixins,
    optionsCache: cache,
    config: {
      optionMergeStrategies
    }
  } = instance.appContext;
  const cached = cache.get(base);
  let resolved;
  if (cached) {
    resolved = cached;
  } else if (!globalMixins.length && !mixins && !extendsOptions) {
    {
      resolved = base;
    }
  } else {
    resolved = {};
    if (globalMixins.length) {
      globalMixins.forEach(m => mergeOptions(resolved, m, optionMergeStrategies, true));
    }
    mergeOptions(resolved, base, optionMergeStrategies);
  }
  if (isObject$1(base)) {
    cache.set(base, resolved);
  }
  return resolved;
}
function mergeOptions(to, from, strats, asMixin) {
  if (asMixin === void 0) {
    asMixin = false;
  }
  const {
    mixins,
    extends: extendsOptions
  } = from;
  if (extendsOptions) {
    mergeOptions(to, extendsOptions, strats, true);
  }
  if (mixins) {
    mixins.forEach(m => mergeOptions(to, m, strats, true));
  }
  for (const key in from) {
    if (asMixin && key === "expose") ; else {
      const strat = internalOptionMergeStrats[key] || strats && strats[key];
      to[key] = strat ? strat(to[key], from[key]) : from[key];
    }
  }
  return to;
}
const internalOptionMergeStrats = {
  data: mergeDataFn,
  props: mergeEmitsOrPropsOptions,
  emits: mergeEmitsOrPropsOptions,
  // objects
  methods: mergeObjectOptions,
  computed: mergeObjectOptions,
  // lifecycle
  beforeCreate: mergeAsArray,
  created: mergeAsArray,
  beforeMount: mergeAsArray,
  mounted: mergeAsArray,
  beforeUpdate: mergeAsArray,
  updated: mergeAsArray,
  beforeDestroy: mergeAsArray,
  beforeUnmount: mergeAsArray,
  destroyed: mergeAsArray,
  unmounted: mergeAsArray,
  activated: mergeAsArray,
  deactivated: mergeAsArray,
  errorCaptured: mergeAsArray,
  serverPrefetch: mergeAsArray,
  // assets
  components: mergeObjectOptions,
  directives: mergeObjectOptions,
  // watch
  watch: mergeWatchOptions,
  // provide / inject
  provide: mergeDataFn,
  inject: mergeInject
};
function mergeDataFn(to, from) {
  if (!from) {
    return to;
  }
  if (!to) {
    return from;
  }
  return function mergedDataFn() {
    return extend(isFunction(to) ? to.call(this, this) : to, isFunction(from) ? from.call(this, this) : from);
  };
}
function mergeInject(to, from) {
  return mergeObjectOptions(normalizeInject(to), normalizeInject(from));
}
function normalizeInject(raw) {
  if (isArray(raw)) {
    const res = {};
    for (let i = 0; i < raw.length; i++) {
      res[raw[i]] = raw[i];
    }
    return res;
  }
  return raw;
}
function mergeAsArray(to, from) {
  return to ? [...new Set([].concat(to, from))] : from;
}
function mergeObjectOptions(to, from) {
  return to ? extend(/* @__PURE__ */Object.create(null), to, from) : from;
}
function mergeEmitsOrPropsOptions(to, from) {
  if (to) {
    if (isArray(to) && isArray(from)) {
      return [... /* @__PURE__ */new Set([...to, ...from])];
    }
    return extend(/* @__PURE__ */Object.create(null), normalizePropsOrEmits(to), normalizePropsOrEmits(from != null ? from : {}));
  } else {
    return from;
  }
}
function mergeWatchOptions(to, from) {
  if (!to) return from;
  if (!from) return to;
  const merged = extend(/* @__PURE__ */Object.create(null), to);
  for (const key in from) {
    merged[key] = mergeAsArray(to[key], from[key]);
  }
  return merged;
}
function createAppContext() {
  return {
    app: null,
    config: {
      isNativeTag: NO,
      performance: false,
      globalProperties: {},
      optionMergeStrategies: {},
      errorHandler: void 0,
      warnHandler: void 0,
      compilerOptions: {}
    },
    mixins: [],
    components: {},
    directives: {},
    provides: /* @__PURE__ */Object.create(null),
    optionsCache: /* @__PURE__ */new WeakMap(),
    propsCache: /* @__PURE__ */new WeakMap(),
    emitsCache: /* @__PURE__ */new WeakMap()
  };
}
let uid$1 = 0;
function createAppAPI(render, hydrate) {
  return function createApp(rootComponent, rootProps) {
    if (rootProps === void 0) {
      rootProps = null;
    }
    if (!isFunction(rootComponent)) {
      rootComponent = extend({}, rootComponent);
    }
    if (rootProps != null && !isObject$1(rootProps)) {
      rootProps = null;
    }
    const context = createAppContext();
    const installedPlugins = /* @__PURE__ */new WeakSet();
    const pluginCleanupFns = [];
    let isMounted = false;
    const app = context.app = {
      _uid: uid$1++,
      _component: rootComponent,
      _props: rootProps,
      _container: null,
      _context: context,
      _instance: null,
      version,
      get config() {
        return context.config;
      },
      set config(v) {
      },
      use(plugin) {
        for (var _len4 = arguments.length, options = new Array(_len4 > 1 ? _len4 - 1 : 0), _key4 = 1; _key4 < _len4; _key4++) {
          options[_key4 - 1] = arguments[_key4];
        }
        if (installedPlugins.has(plugin)) ; else if (plugin && isFunction(plugin.install)) {
          installedPlugins.add(plugin);
          plugin.install(app, ...options);
        } else if (isFunction(plugin)) {
          installedPlugins.add(plugin);
          plugin(app, ...options);
        } else ;
        return app;
      },
      mixin(mixin) {
        {
          if (!context.mixins.includes(mixin)) {
            context.mixins.push(mixin);
          }
        }
        return app;
      },
      component(name, component) {
        if (!component) {
          return context.components[name];
        }
        context.components[name] = component;
        return app;
      },
      directive(name, directive) {
        if (!directive) {
          return context.directives[name];
        }
        context.directives[name] = directive;
        return app;
      },
      mount(rootContainer, isHydrate, namespace) {
        if (!isMounted) {
          const vnode = app._ceVNode || createVNode(rootComponent, rootProps);
          vnode.appContext = context;
          if (namespace === true) {
            namespace = "svg";
          } else if (namespace === false) {
            namespace = void 0;
          }
          {
            render(vnode, rootContainer, namespace);
          }
          isMounted = true;
          app._container = rootContainer;
          rootContainer.__vue_app__ = app;
          return getComponentPublicInstance(vnode.component);
        }
      },
      onUnmount(cleanupFn) {
        pluginCleanupFns.push(cleanupFn);
      },
      unmount() {
        if (isMounted) {
          callWithAsyncErrorHandling(pluginCleanupFns, app._instance, 16);
          render(null, app._container);
          delete app._container.__vue_app__;
        }
      },
      provide(key, value) {
        context.provides[key] = value;
        return app;
      },
      runWithContext(fn) {
        const lastApp = currentApp;
        currentApp = app;
        try {
          return fn();
        } finally {
          currentApp = lastApp;
        }
      }
    };
    return app;
  };
}
let currentApp = null;
function provide(key, value) {
  if (!currentInstance) ; else {
    let provides = currentInstance.provides;
    const parentProvides = currentInstance.parent && currentInstance.parent.provides;
    if (parentProvides === provides) {
      provides = currentInstance.provides = Object.create(parentProvides);
    }
    provides[key] = value;
  }
}
function inject(key, defaultValue, treatDefaultAsFactory) {
  if (treatDefaultAsFactory === void 0) {
    treatDefaultAsFactory = false;
  }
  const instance = getCurrentInstance();
  if (instance || currentApp) {
    let provides = currentApp ? currentApp._context.provides : instance ? instance.parent == null || instance.ce ? instance.vnode.appContext && instance.vnode.appContext.provides : instance.parent.provides : void 0;
    if (provides && key in provides) {
      return provides[key];
    } else if (arguments.length > 1) {
      return treatDefaultAsFactory && isFunction(defaultValue) ? defaultValue.call(instance && instance.proxy) : defaultValue;
    } else ;
  }
}
const internalObjectProto = {};
const createInternalObject = () => Object.create(internalObjectProto);
const isInternalObject = obj => Object.getPrototypeOf(obj) === internalObjectProto;
function initProps(instance, rawProps, isStateful, isSSR) {
  if (isSSR === void 0) {
    isSSR = false;
  }
  const props = {};
  const attrs = createInternalObject();
  instance.propsDefaults = /* @__PURE__ */Object.create(null);
  setFullProps(instance, rawProps, props, attrs);
  for (const key in instance.propsOptions[0]) {
    if (!(key in props)) {
      props[key] = void 0;
    }
  }
  if (isStateful) {
    instance.props = isSSR ? props : shallowReactive(props);
  } else {
    if (!instance.type.props) {
      instance.props = attrs;
    } else {
      instance.props = props;
    }
  }
  instance.attrs = attrs;
}
function updateProps(instance, rawProps, rawPrevProps, optimized) {
  const {
    props,
    attrs,
    vnode: {
      patchFlag
    }
  } = instance;
  const rawCurrentProps = toRaw(props);
  const [options] = instance.propsOptions;
  let hasAttrsChanged = false;
  if (
  // always force full diff in dev
  // - #1942 if hmr is enabled with sfc component
  // - vite#872 non-sfc component used by sfc component
  (optimized || patchFlag > 0) && !(patchFlag & 16)) {
    if (patchFlag & 8) {
      const propsToUpdate = instance.vnode.dynamicProps;
      for (let i = 0; i < propsToUpdate.length; i++) {
        let key = propsToUpdate[i];
        if (isEmitListener(instance.emitsOptions, key)) {
          continue;
        }
        const value = rawProps[key];
        if (options) {
          if (hasOwn(attrs, key)) {
            if (value !== attrs[key]) {
              attrs[key] = value;
              hasAttrsChanged = true;
            }
          } else {
            const camelizedKey = camelize(key);
            props[camelizedKey] = resolvePropValue(options, rawCurrentProps, camelizedKey, value, instance, false);
          }
        } else {
          if (value !== attrs[key]) {
            attrs[key] = value;
            hasAttrsChanged = true;
          }
        }
      }
    }
  } else {
    if (setFullProps(instance, rawProps, props, attrs)) {
      hasAttrsChanged = true;
    }
    let kebabKey;
    for (const key in rawCurrentProps) {
      if (!rawProps ||
      // for camelCase
      !hasOwn(rawProps, key) && (
      // it's possible the original props was passed in as kebab-case
      // and converted to camelCase (#955)
      (kebabKey = hyphenate(key)) === key || !hasOwn(rawProps, kebabKey))) {
        if (options) {
          if (rawPrevProps && (
          // for camelCase
          rawPrevProps[key] !== void 0 ||
          // for kebab-case
          rawPrevProps[kebabKey] !== void 0)) {
            props[key] = resolvePropValue(options, rawCurrentProps, key, void 0, instance, true);
          }
        } else {
          delete props[key];
        }
      }
    }
    if (attrs !== rawCurrentProps) {
      for (const key in attrs) {
        if (!rawProps || !hasOwn(rawProps, key) && true) {
          delete attrs[key];
          hasAttrsChanged = true;
        }
      }
    }
  }
  if (hasAttrsChanged) {
    trigger(instance.attrs, "set", "");
  }
}
function setFullProps(instance, rawProps, props, attrs) {
  const [options, needCastKeys] = instance.propsOptions;
  let hasAttrsChanged = false;
  let rawCastValues;
  if (rawProps) {
    for (let key in rawProps) {
      if (isReservedProp(key)) {
        continue;
      }
      const value = rawProps[key];
      let camelKey;
      if (options && hasOwn(options, camelKey = camelize(key))) {
        if (!needCastKeys || !needCastKeys.includes(camelKey)) {
          props[camelKey] = value;
        } else {
          (rawCastValues || (rawCastValues = {}))[camelKey] = value;
        }
      } else if (!isEmitListener(instance.emitsOptions, key)) {
        if (!(key in attrs) || value !== attrs[key]) {
          attrs[key] = value;
          hasAttrsChanged = true;
        }
      }
    }
  }
  if (needCastKeys) {
    const rawCurrentProps = toRaw(props);
    const castValues = rawCastValues || EMPTY_OBJ;
    for (let i = 0; i < needCastKeys.length; i++) {
      const key = needCastKeys[i];
      props[key] = resolvePropValue(options, rawCurrentProps, key, castValues[key], instance, !hasOwn(castValues, key));
    }
  }
  return hasAttrsChanged;
}
function resolvePropValue(options, props, key, value, instance, isAbsent) {
  const opt = options[key];
  if (opt != null) {
    const hasDefault = hasOwn(opt, "default");
    if (hasDefault && value === void 0) {
      const defaultValue = opt.default;
      if (opt.type !== Function && !opt.skipFactory && isFunction(defaultValue)) {
        const {
          propsDefaults
        } = instance;
        if (key in propsDefaults) {
          value = propsDefaults[key];
        } else {
          const reset = setCurrentInstance(instance);
          value = propsDefaults[key] = defaultValue.call(null, props);
          reset();
        }
      } else {
        value = defaultValue;
      }
      if (instance.ce) {
        instance.ce._setProp(key, value);
      }
    }
    if (opt[0 /* shouldCast */]) {
      if (isAbsent && !hasDefault) {
        value = false;
      } else if (opt[1 /* shouldCastTrue */] && (value === "" || value === hyphenate(key))) {
        value = true;
      }
    }
  }
  return value;
}
const mixinPropsCache = /* @__PURE__ */new WeakMap();
function normalizePropsOptions(comp, appContext, asMixin) {
  if (asMixin === void 0) {
    asMixin = false;
  }
  const cache = asMixin ? mixinPropsCache : appContext.propsCache;
  const cached = cache.get(comp);
  if (cached) {
    return cached;
  }
  const raw = comp.props;
  const normalized = {};
  const needCastKeys = [];
  let hasExtends = false;
  if (!isFunction(comp)) {
    const extendProps = raw2 => {
      hasExtends = true;
      const [props, keys] = normalizePropsOptions(raw2, appContext, true);
      extend(normalized, props);
      if (keys) needCastKeys.push(...keys);
    };
    if (!asMixin && appContext.mixins.length) {
      appContext.mixins.forEach(extendProps);
    }
    if (comp.extends) {
      extendProps(comp.extends);
    }
    if (comp.mixins) {
      comp.mixins.forEach(extendProps);
    }
  }
  if (!raw && !hasExtends) {
    if (isObject$1(comp)) {
      cache.set(comp, EMPTY_ARR);
    }
    return EMPTY_ARR;
  }
  if (isArray(raw)) {
    for (let i = 0; i < raw.length; i++) {
      const normalizedKey = camelize(raw[i]);
      if (validatePropName(normalizedKey)) {
        normalized[normalizedKey] = EMPTY_OBJ;
      }
    }
  } else if (raw) {
    for (const key in raw) {
      const normalizedKey = camelize(key);
      if (validatePropName(normalizedKey)) {
        const opt = raw[key];
        const prop = normalized[normalizedKey] = isArray(opt) || isFunction(opt) ? {
          type: opt
        } : extend({}, opt);
        const propType = prop.type;
        let shouldCast = false;
        let shouldCastTrue = true;
        if (isArray(propType)) {
          for (let index = 0; index < propType.length; ++index) {
            const type = propType[index];
            const typeName = isFunction(type) && type.name;
            if (typeName === "Boolean") {
              shouldCast = true;
              break;
            } else if (typeName === "String") {
              shouldCastTrue = false;
            }
          }
        } else {
          shouldCast = isFunction(propType) && propType.name === "Boolean";
        }
        prop[0 /* shouldCast */] = shouldCast;
        prop[1 /* shouldCastTrue */] = shouldCastTrue;
        if (shouldCast || hasOwn(prop, "default")) {
          needCastKeys.push(normalizedKey);
        }
      }
    }
  }
  const res = [normalized, needCastKeys];
  if (isObject$1(comp)) {
    cache.set(comp, res);
  }
  return res;
}
function validatePropName(key) {
  if (key[0] !== "$" && !isReservedProp(key)) {
    return true;
  }
  return false;
}
const isInternalKey = key => key === "_" || key === "_ctx" || key === "$stable";
const normalizeSlotValue = value => isArray(value) ? value.map(normalizeVNode) : [normalizeVNode(value)];
const normalizeSlot = (key, rawSlot, ctx) => {
  if (rawSlot._n) {
    return rawSlot;
  }
  const normalized = withCtx(function () {
    if (!!("production" !== "production") && currentInstance && !(ctx === null && currentRenderingInstance) && !(ctx && ctx.root !== currentInstance.root)) ;
    return normalizeSlotValue(rawSlot(...arguments));
  }, ctx);
  normalized._c = false;
  return normalized;
};
const normalizeObjectSlots = (rawSlots, slots, instance) => {
  const ctx = rawSlots._ctx;
  for (const key in rawSlots) {
    if (isInternalKey(key)) continue;
    const value = rawSlots[key];
    if (isFunction(value)) {
      slots[key] = normalizeSlot(key, value, ctx);
    } else if (value != null) {
      const normalized = normalizeSlotValue(value);
      slots[key] = () => normalized;
    }
  }
};
const normalizeVNodeSlots = (instance, children) => {
  const normalized = normalizeSlotValue(children);
  instance.slots.default = () => normalized;
};
const assignSlots = (slots, children, optimized) => {
  for (const key in children) {
    if (optimized || !isInternalKey(key)) {
      slots[key] = children[key];
    }
  }
};
const initSlots = (instance, children, optimized) => {
  const slots = instance.slots = createInternalObject();
  if (instance.vnode.shapeFlag & 32) {
    const type = children._;
    if (type) {
      assignSlots(slots, children, optimized);
      if (optimized) {
        def(slots, "_", type, true);
      }
    } else {
      normalizeObjectSlots(children, slots);
    }
  } else if (children) {
    normalizeVNodeSlots(instance, children);
  }
};
const updateSlots = (instance, children, optimized) => {
  const {
    vnode,
    slots
  } = instance;
  let needDeletionCheck = true;
  let deletionComparisonTarget = EMPTY_OBJ;
  if (vnode.shapeFlag & 32) {
    const type = children._;
    if (type) {
      if (optimized && type === 1) {
        needDeletionCheck = false;
      } else {
        assignSlots(slots, children, optimized);
      }
    } else {
      needDeletionCheck = !children.$stable;
      normalizeObjectSlots(children, slots);
    }
    deletionComparisonTarget = children;
  } else if (children) {
    normalizeVNodeSlots(instance, children);
    deletionComparisonTarget = {
      default: 1
    };
  }
  if (needDeletionCheck) {
    for (const key in slots) {
      if (!isInternalKey(key) && deletionComparisonTarget[key] == null) {
        delete slots[key];
      }
    }
  }
};
function initFeatureFlags() {
  if (typeof __VUE_PROD_HYDRATION_MISMATCH_DETAILS__ !== "boolean") {
    getGlobalThis().__VUE_PROD_HYDRATION_MISMATCH_DETAILS__ = false;
  }
}
const queuePostRenderEffect = queueEffectWithSuspense;
function createRenderer(options) {
  return baseCreateRenderer(options);
}
function baseCreateRenderer(options, createHydrationFns) {
  {
    initFeatureFlags();
  }
  const target = getGlobalThis();
  target.__VUE__ = true;
  const {
    insert: hostInsert,
    remove: hostRemove,
    patchProp: hostPatchProp,
    createElement: hostCreateElement,
    createText: hostCreateText,
    createComment: hostCreateComment,
    setText: hostSetText,
    setElementText: hostSetElementText,
    parentNode: hostParentNode,
    nextSibling: hostNextSibling,
    setScopeId: hostSetScopeId = NOOP,
    insertStaticContent: hostInsertStaticContent
  } = options;
  const patch = function patch(n1, n2, container, anchor, parentComponent, parentSuspense, namespace, slotScopeIds, optimized) {
    if (anchor === void 0) {
      anchor = null;
    }
    if (parentComponent === void 0) {
      parentComponent = null;
    }
    if (parentSuspense === void 0) {
      parentSuspense = null;
    }
    if (namespace === void 0) {
      namespace = void 0;
    }
    if (slotScopeIds === void 0) {
      slotScopeIds = null;
    }
    if (optimized === void 0) {
      optimized = !!n2.dynamicChildren;
    }
    if (n1 === n2) {
      return;
    }
    if (n1 && !isSameVNodeType(n1, n2)) {
      anchor = getNextHostNode(n1);
      unmount(n1, parentComponent, parentSuspense, true);
      n1 = null;
    }
    if (n2.patchFlag === -2) {
      optimized = false;
      n2.dynamicChildren = null;
    }
    const {
      type,
      ref,
      shapeFlag
    } = n2;
    switch (type) {
      case Text:
        processText(n1, n2, container, anchor);
        break;
      case Comment:
        processCommentNode(n1, n2, container, anchor);
        break;
      case Static:
        if (n1 == null) {
          mountStaticNode(n2, container, anchor, namespace);
        }
        break;
      case Fragment:
        processFragment(n1, n2, container, anchor, parentComponent, parentSuspense, namespace, slotScopeIds, optimized);
        break;
      default:
        if (shapeFlag & 1) {
          processElement(n1, n2, container, anchor, parentComponent, parentSuspense, namespace, slotScopeIds, optimized);
        } else if (shapeFlag & 6) {
          processComponent(n1, n2, container, anchor, parentComponent, parentSuspense, namespace, slotScopeIds, optimized);
        } else if (shapeFlag & 64) {
          type.process(n1, n2, container, anchor, parentComponent, parentSuspense, namespace, slotScopeIds, optimized, internals);
        } else if (shapeFlag & 128) {
          type.process(n1, n2, container, anchor, parentComponent, parentSuspense, namespace, slotScopeIds, optimized, internals);
        } else ;
    }
    if (ref != null && parentComponent) {
      setRef(ref, n1 && n1.ref, parentSuspense, n2 || n1, !n2);
    } else if (ref == null && n1 && n1.ref != null) {
      setRef(n1.ref, null, parentSuspense, n1, true);
    }
  };
  const processText = (n1, n2, container, anchor) => {
    if (n1 == null) {
      hostInsert(n2.el = hostCreateText(n2.children), container, anchor);
    } else {
      const el = n2.el = n1.el;
      if (n2.children !== n1.children) {
        hostSetText(el, n2.children);
      }
    }
  };
  const processCommentNode = (n1, n2, container, anchor) => {
    if (n1 == null) {
      hostInsert(n2.el = hostCreateComment(n2.children || ""), container, anchor);
    } else {
      n2.el = n1.el;
    }
  };
  const mountStaticNode = (n2, container, anchor, namespace) => {
    [n2.el, n2.anchor] = hostInsertStaticContent(n2.children, container, anchor, namespace, n2.el, n2.anchor);
  };
  const moveStaticNode = (_ref11, container, nextSibling) => {
    let {
      el,
      anchor
    } = _ref11;
    let next;
    while (el && el !== anchor) {
      next = hostNextSibling(el);
      hostInsert(el, container, nextSibling);
      el = next;
    }
    hostInsert(anchor, container, nextSibling);
  };
  const removeStaticNode = _ref12 => {
    let {
      el,
      anchor
    } = _ref12;
    let next;
    while (el && el !== anchor) {
      next = hostNextSibling(el);
      hostRemove(el);
      el = next;
    }
    hostRemove(anchor);
  };
  const processElement = (n1, n2, container, anchor, parentComponent, parentSuspense, namespace, slotScopeIds, optimized) => {
    if (n2.type === "svg") {
      namespace = "svg";
    } else if (n2.type === "math") {
      namespace = "mathml";
    }
    if (n1 == null) {
      mountElement(n2, container, anchor, parentComponent, parentSuspense, namespace, slotScopeIds, optimized);
    } else {
      patchElement(n1, n2, parentComponent, parentSuspense, namespace, slotScopeIds, optimized);
    }
  };
  const mountElement = (vnode, container, anchor, parentComponent, parentSuspense, namespace, slotScopeIds, optimized) => {
    let el;
    let vnodeHook;
    const {
      props,
      shapeFlag,
      transition,
      dirs
    } = vnode;
    el = vnode.el = hostCreateElement(vnode.type, namespace, props && props.is, props);
    if (shapeFlag & 8) {
      hostSetElementText(el, vnode.children);
    } else if (shapeFlag & 16) {
      mountChildren(vnode.children, el, null, parentComponent, parentSuspense, resolveChildrenNamespace(vnode, namespace), slotScopeIds, optimized);
    }
    if (dirs) {
      invokeDirectiveHook(vnode, null, parentComponent, "created");
    }
    setScopeId(el, vnode, vnode.scopeId, slotScopeIds, parentComponent);
    if (props) {
      for (const key in props) {
        if (key !== "value" && !isReservedProp(key)) {
          hostPatchProp(el, key, null, props[key], namespace, parentComponent);
        }
      }
      if ("value" in props) {
        hostPatchProp(el, "value", null, props.value, namespace);
      }
      if (vnodeHook = props.onVnodeBeforeMount) {
        invokeVNodeHook(vnodeHook, parentComponent, vnode);
      }
    }
    if (dirs) {
      invokeDirectiveHook(vnode, null, parentComponent, "beforeMount");
    }
    const needCallTransitionHooks = needTransition(parentSuspense, transition);
    if (needCallTransitionHooks) {
      transition.beforeEnter(el);
    }
    hostInsert(el, container, anchor);
    if ((vnodeHook = props && props.onVnodeMounted) || needCallTransitionHooks || dirs) {
      queuePostRenderEffect(() => {
        vnodeHook && invokeVNodeHook(vnodeHook, parentComponent, vnode);
        needCallTransitionHooks && transition.enter(el);
        dirs && invokeDirectiveHook(vnode, null, parentComponent, "mounted");
      }, parentSuspense);
    }
  };
  const setScopeId = (el, vnode, scopeId, slotScopeIds, parentComponent) => {
    if (scopeId) {
      hostSetScopeId(el, scopeId);
    }
    if (slotScopeIds) {
      for (let i = 0; i < slotScopeIds.length; i++) {
        hostSetScopeId(el, slotScopeIds[i]);
      }
    }
    if (parentComponent) {
      let subTree = parentComponent.subTree;
      if (vnode === subTree || isSuspense(subTree.type) && (subTree.ssContent === vnode || subTree.ssFallback === vnode)) {
        const parentVNode = parentComponent.vnode;
        setScopeId(el, parentVNode, parentVNode.scopeId, parentVNode.slotScopeIds, parentComponent.parent);
      }
    }
  };
  const mountChildren = function mountChildren(children, container, anchor, parentComponent, parentSuspense, namespace, slotScopeIds, optimized, start) {
    if (start === void 0) {
      start = 0;
    }
    for (let i = start; i < children.length; i++) {
      const child = children[i] = optimized ? cloneIfMounted(children[i]) : normalizeVNode(children[i]);
      patch(null, child, container, anchor, parentComponent, parentSuspense, namespace, slotScopeIds, optimized);
    }
  };
  const patchElement = (n1, n2, parentComponent, parentSuspense, namespace, slotScopeIds, optimized) => {
    const el = n2.el = n1.el;
    let {
      patchFlag,
      dynamicChildren,
      dirs
    } = n2;
    patchFlag |= n1.patchFlag & 16;
    const oldProps = n1.props || EMPTY_OBJ;
    const newProps = n2.props || EMPTY_OBJ;
    let vnodeHook;
    parentComponent && toggleRecurse(parentComponent, false);
    if (vnodeHook = newProps.onVnodeBeforeUpdate) {
      invokeVNodeHook(vnodeHook, parentComponent, n2, n1);
    }
    if (dirs) {
      invokeDirectiveHook(n2, n1, parentComponent, "beforeUpdate");
    }
    parentComponent && toggleRecurse(parentComponent, true);
    if (oldProps.innerHTML && newProps.innerHTML == null || oldProps.textContent && newProps.textContent == null) {
      hostSetElementText(el, "");
    }
    if (dynamicChildren) {
      patchBlockChildren(n1.dynamicChildren, dynamicChildren, el, parentComponent, parentSuspense, resolveChildrenNamespace(n2, namespace), slotScopeIds);
    } else if (!optimized) {
      patchChildren(n1, n2, el, null, parentComponent, parentSuspense, resolveChildrenNamespace(n2, namespace), slotScopeIds, false);
    }
    if (patchFlag > 0) {
      if (patchFlag & 16) {
        patchProps(el, oldProps, newProps, parentComponent, namespace);
      } else {
        if (patchFlag & 2) {
          if (oldProps.class !== newProps.class) {
            hostPatchProp(el, "class", null, newProps.class, namespace);
          }
        }
        if (patchFlag & 4) {
          hostPatchProp(el, "style", oldProps.style, newProps.style, namespace);
        }
        if (patchFlag & 8) {
          const propsToUpdate = n2.dynamicProps;
          for (let i = 0; i < propsToUpdate.length; i++) {
            const key = propsToUpdate[i];
            const prev = oldProps[key];
            const next = newProps[key];
            if (next !== prev || key === "value") {
              hostPatchProp(el, key, prev, next, namespace, parentComponent);
            }
          }
        }
      }
      if (patchFlag & 1) {
        if (n1.children !== n2.children) {
          hostSetElementText(el, n2.children);
        }
      }
    } else if (!optimized && dynamicChildren == null) {
      patchProps(el, oldProps, newProps, parentComponent, namespace);
    }
    if ((vnodeHook = newProps.onVnodeUpdated) || dirs) {
      queuePostRenderEffect(() => {
        vnodeHook && invokeVNodeHook(vnodeHook, parentComponent, n2, n1);
        dirs && invokeDirectiveHook(n2, n1, parentComponent, "updated");
      }, parentSuspense);
    }
  };
  const patchBlockChildren = (oldChildren, newChildren, fallbackContainer, parentComponent, parentSuspense, namespace, slotScopeIds) => {
    for (let i = 0; i < newChildren.length; i++) {
      const oldVNode = oldChildren[i];
      const newVNode = newChildren[i];
      const container =
      // oldVNode may be an errored async setup() component inside Suspense
      // which will not have a mounted element
      oldVNode.el && (
      // - In the case of a Fragment, we need to provide the actual parent
      // of the Fragment itself so it can move its children.
      oldVNode.type === Fragment ||
      // - In the case of different nodes, there is going to be a replacement
      // which also requires the correct parent container
      !isSameVNodeType(oldVNode, newVNode) ||
      // - In the case of a component, it could contain anything.
      oldVNode.shapeFlag & (6 | 64 | 128)) ? hostParentNode(oldVNode.el) :
      // In other cases, the parent container is not actually used so we
      // just pass the block element here to avoid a DOM parentNode call.
      fallbackContainer;
      patch(oldVNode, newVNode, container, null, parentComponent, parentSuspense, namespace, slotScopeIds, true);
    }
  };
  const patchProps = (el, oldProps, newProps, parentComponent, namespace) => {
    if (oldProps !== newProps) {
      if (oldProps !== EMPTY_OBJ) {
        for (const key in oldProps) {
          if (!isReservedProp(key) && !(key in newProps)) {
            hostPatchProp(el, key, oldProps[key], null, namespace, parentComponent);
          }
        }
      }
      for (const key in newProps) {
        if (isReservedProp(key)) continue;
        const next = newProps[key];
        const prev = oldProps[key];
        if (next !== prev && key !== "value") {
          hostPatchProp(el, key, prev, next, namespace, parentComponent);
        }
      }
      if ("value" in newProps) {
        hostPatchProp(el, "value", oldProps.value, newProps.value, namespace);
      }
    }
  };
  const processFragment = (n1, n2, container, anchor, parentComponent, parentSuspense, namespace, slotScopeIds, optimized) => {
    const fragmentStartAnchor = n2.el = n1 ? n1.el : hostCreateText("");
    const fragmentEndAnchor = n2.anchor = n1 ? n1.anchor : hostCreateText("");
    let {
      patchFlag,
      dynamicChildren,
      slotScopeIds: fragmentSlotScopeIds
    } = n2;
    if (fragmentSlotScopeIds) {
      slotScopeIds = slotScopeIds ? slotScopeIds.concat(fragmentSlotScopeIds) : fragmentSlotScopeIds;
    }
    if (n1 == null) {
      hostInsert(fragmentStartAnchor, container, anchor);
      hostInsert(fragmentEndAnchor, container, anchor);
      mountChildren(
      // #10007
      // such fragment like `<></>` will be compiled into
      // a fragment which doesn't have a children.
      // In this case fallback to an empty array
      n2.children || [], container, fragmentEndAnchor, parentComponent, parentSuspense, namespace, slotScopeIds, optimized);
    } else {
      if (patchFlag > 0 && patchFlag & 64 && dynamicChildren &&
      // #2715 the previous fragment could've been a BAILed one as a result
      // of renderSlot() with no valid children
      n1.dynamicChildren) {
        patchBlockChildren(n1.dynamicChildren, dynamicChildren, container, parentComponent, parentSuspense, namespace, slotScopeIds);
        if (
        // #2080 if the stable fragment has a key, it's a <template v-for> that may
        //  get moved around. Make sure all root level vnodes inherit el.
        // #2134 or if it's a component root, it may also get moved around
        // as the component is being moved.
        n2.key != null || parentComponent && n2 === parentComponent.subTree) {
          traverseStaticChildren(n1, n2);
        }
      } else {
        patchChildren(n1, n2, container, fragmentEndAnchor, parentComponent, parentSuspense, namespace, slotScopeIds, optimized);
      }
    }
  };
  const processComponent = (n1, n2, container, anchor, parentComponent, parentSuspense, namespace, slotScopeIds, optimized) => {
    n2.slotScopeIds = slotScopeIds;
    if (n1 == null) {
      if (n2.shapeFlag & 512) {
        parentComponent.ctx.activate(n2, container, anchor, namespace, optimized);
      } else {
        mountComponent(n2, container, anchor, parentComponent, parentSuspense, namespace, optimized);
      }
    } else {
      updateComponent(n1, n2, optimized);
    }
  };
  const mountComponent = (initialVNode, container, anchor, parentComponent, parentSuspense, namespace, optimized) => {
    const instance = initialVNode.component = createComponentInstance(initialVNode, parentComponent, parentSuspense);
    if (isKeepAlive(initialVNode)) {
      instance.ctx.renderer = internals;
    }
    {
      setupComponent(instance, false, optimized);
    }
    if (instance.asyncDep) {
      parentSuspense && parentSuspense.registerDep(instance, setupRenderEffect, optimized);
      if (!initialVNode.el) {
        const placeholder = instance.subTree = createVNode(Comment);
        processCommentNode(null, placeholder, container, anchor);
        initialVNode.placeholder = placeholder.el;
      }
    } else {
      setupRenderEffect(instance, initialVNode, container, anchor, parentSuspense, namespace, optimized);
    }
  };
  const updateComponent = (n1, n2, optimized) => {
    const instance = n2.component = n1.component;
    if (shouldUpdateComponent(n1, n2, optimized)) {
      if (instance.asyncDep && !instance.asyncResolved) {
        updateComponentPreRender(instance, n2, optimized);
        return;
      } else {
        instance.next = n2;
        instance.update();
      }
    } else {
      n2.el = n1.el;
      instance.vnode = n2;
    }
  };
  const setupRenderEffect = (instance, initialVNode, container, anchor, parentSuspense, namespace, optimized) => {
    const componentUpdateFn = () => {
      if (!instance.isMounted) {
        let vnodeHook;
        const {
          el,
          props
        } = initialVNode;
        const {
          bm,
          m,
          parent,
          root,
          type
        } = instance;
        const isAsyncWrapperVNode = isAsyncWrapper(initialVNode);
        toggleRecurse(instance, false);
        if (bm) {
          invokeArrayFns(bm);
        }
        if (!isAsyncWrapperVNode && (vnodeHook = props && props.onVnodeBeforeMount)) {
          invokeVNodeHook(vnodeHook, parent, initialVNode);
        }
        toggleRecurse(instance, true);
        {
          if (root.ce &&
          // @ts-expect-error _def is private
          root.ce._def.shadowRoot !== false) {
            root.ce._injectChildStyle(type);
          }
          const subTree = instance.subTree = renderComponentRoot(instance);
          patch(null, subTree, container, anchor, instance, parentSuspense, namespace);
          initialVNode.el = subTree.el;
        }
        if (m) {
          queuePostRenderEffect(m, parentSuspense);
        }
        if (!isAsyncWrapperVNode && (vnodeHook = props && props.onVnodeMounted)) {
          const scopedInitialVNode = initialVNode;
          queuePostRenderEffect(() => invokeVNodeHook(vnodeHook, parent, scopedInitialVNode), parentSuspense);
        }
        if (initialVNode.shapeFlag & 256 || parent && isAsyncWrapper(parent.vnode) && parent.vnode.shapeFlag & 256) {
          instance.a && queuePostRenderEffect(instance.a, parentSuspense);
        }
        instance.isMounted = true;
        initialVNode = container = anchor = null;
      } else {
        let {
          next,
          bu,
          u,
          parent,
          vnode
        } = instance;
        {
          const nonHydratedAsyncRoot = locateNonHydratedAsyncRoot(instance);
          if (nonHydratedAsyncRoot) {
            if (next) {
              next.el = vnode.el;
              updateComponentPreRender(instance, next, optimized);
            }
            nonHydratedAsyncRoot.asyncDep.then(() => {
              if (!instance.isUnmounted) {
                componentUpdateFn();
              }
            });
            return;
          }
        }
        let originNext = next;
        let vnodeHook;
        toggleRecurse(instance, false);
        if (next) {
          next.el = vnode.el;
          updateComponentPreRender(instance, next, optimized);
        } else {
          next = vnode;
        }
        if (bu) {
          invokeArrayFns(bu);
        }
        if (vnodeHook = next.props && next.props.onVnodeBeforeUpdate) {
          invokeVNodeHook(vnodeHook, parent, next, vnode);
        }
        toggleRecurse(instance, true);
        const nextTree = renderComponentRoot(instance);
        const prevTree = instance.subTree;
        instance.subTree = nextTree;
        patch(prevTree, nextTree,
        // parent may have changed if it's in a teleport
        hostParentNode(prevTree.el),
        // anchor may have changed if it's in a fragment
        getNextHostNode(prevTree), instance, parentSuspense, namespace);
        next.el = nextTree.el;
        if (originNext === null) {
          updateHOCHostEl(instance, nextTree.el);
        }
        if (u) {
          queuePostRenderEffect(u, parentSuspense);
        }
        if (vnodeHook = next.props && next.props.onVnodeUpdated) {
          queuePostRenderEffect(() => invokeVNodeHook(vnodeHook, parent, next, vnode), parentSuspense);
        }
      }
    };
    instance.scope.on();
    const effect = instance.effect = new ReactiveEffect(componentUpdateFn);
    instance.scope.off();
    const update = instance.update = effect.run.bind(effect);
    const job = instance.job = effect.runIfDirty.bind(effect);
    job.i = instance;
    job.id = instance.uid;
    effect.scheduler = () => queueJob(job);
    toggleRecurse(instance, true);
    update();
  };
  const updateComponentPreRender = (instance, nextVNode, optimized) => {
    nextVNode.component = instance;
    const prevProps = instance.vnode.props;
    instance.vnode = nextVNode;
    instance.next = null;
    updateProps(instance, nextVNode.props, prevProps, optimized);
    updateSlots(instance, nextVNode.children, optimized);
    pauseTracking();
    flushPreFlushCbs(instance);
    resetTracking();
  };
  const patchChildren = function patchChildren(n1, n2, container, anchor, parentComponent, parentSuspense, namespace, slotScopeIds, optimized) {
    if (optimized === void 0) {
      optimized = false;
    }
    const c1 = n1 && n1.children;
    const prevShapeFlag = n1 ? n1.shapeFlag : 0;
    const c2 = n2.children;
    const {
      patchFlag,
      shapeFlag
    } = n2;
    if (patchFlag > 0) {
      if (patchFlag & 128) {
        patchKeyedChildren(c1, c2, container, anchor, parentComponent, parentSuspense, namespace, slotScopeIds, optimized);
        return;
      } else if (patchFlag & 256) {
        patchUnkeyedChildren(c1, c2, container, anchor, parentComponent, parentSuspense, namespace, slotScopeIds, optimized);
        return;
      }
    }
    if (shapeFlag & 8) {
      if (prevShapeFlag & 16) {
        unmountChildren(c1, parentComponent, parentSuspense);
      }
      if (c2 !== c1) {
        hostSetElementText(container, c2);
      }
    } else {
      if (prevShapeFlag & 16) {
        if (shapeFlag & 16) {
          patchKeyedChildren(c1, c2, container, anchor, parentComponent, parentSuspense, namespace, slotScopeIds, optimized);
        } else {
          unmountChildren(c1, parentComponent, parentSuspense, true);
        }
      } else {
        if (prevShapeFlag & 8) {
          hostSetElementText(container, "");
        }
        if (shapeFlag & 16) {
          mountChildren(c2, container, anchor, parentComponent, parentSuspense, namespace, slotScopeIds, optimized);
        }
      }
    }
  };
  const patchUnkeyedChildren = (c1, c2, container, anchor, parentComponent, parentSuspense, namespace, slotScopeIds, optimized) => {
    c1 = c1 || EMPTY_ARR;
    c2 = c2 || EMPTY_ARR;
    const oldLength = c1.length;
    const newLength = c2.length;
    const commonLength = Math.min(oldLength, newLength);
    let i;
    for (i = 0; i < commonLength; i++) {
      const nextChild = c2[i] = optimized ? cloneIfMounted(c2[i]) : normalizeVNode(c2[i]);
      patch(c1[i], nextChild, container, null, parentComponent, parentSuspense, namespace, slotScopeIds, optimized);
    }
    if (oldLength > newLength) {
      unmountChildren(c1, parentComponent, parentSuspense, true, false, commonLength);
    } else {
      mountChildren(c2, container, anchor, parentComponent, parentSuspense, namespace, slotScopeIds, optimized, commonLength);
    }
  };
  const patchKeyedChildren = (c1, c2, container, parentAnchor, parentComponent, parentSuspense, namespace, slotScopeIds, optimized) => {
    let i = 0;
    const l2 = c2.length;
    let e1 = c1.length - 1;
    let e2 = l2 - 1;
    while (i <= e1 && i <= e2) {
      const n1 = c1[i];
      const n2 = c2[i] = optimized ? cloneIfMounted(c2[i]) : normalizeVNode(c2[i]);
      if (isSameVNodeType(n1, n2)) {
        patch(n1, n2, container, null, parentComponent, parentSuspense, namespace, slotScopeIds, optimized);
      } else {
        break;
      }
      i++;
    }
    while (i <= e1 && i <= e2) {
      const n1 = c1[e1];
      const n2 = c2[e2] = optimized ? cloneIfMounted(c2[e2]) : normalizeVNode(c2[e2]);
      if (isSameVNodeType(n1, n2)) {
        patch(n1, n2, container, null, parentComponent, parentSuspense, namespace, slotScopeIds, optimized);
      } else {
        break;
      }
      e1--;
      e2--;
    }
    if (i > e1) {
      if (i <= e2) {
        const nextPos = e2 + 1;
        const anchor = nextPos < l2 ? c2[nextPos].el : parentAnchor;
        while (i <= e2) {
          patch(null, c2[i] = optimized ? cloneIfMounted(c2[i]) : normalizeVNode(c2[i]), container, anchor, parentComponent, parentSuspense, namespace, slotScopeIds, optimized);
          i++;
        }
      }
    } else if (i > e2) {
      while (i <= e1) {
        unmount(c1[i], parentComponent, parentSuspense, true);
        i++;
      }
    } else {
      const s1 = i;
      const s2 = i;
      const keyToNewIndexMap = /* @__PURE__ */new Map();
      for (i = s2; i <= e2; i++) {
        const nextChild = c2[i] = optimized ? cloneIfMounted(c2[i]) : normalizeVNode(c2[i]);
        if (nextChild.key != null) {
          keyToNewIndexMap.set(nextChild.key, i);
        }
      }
      let j;
      let patched = 0;
      const toBePatched = e2 - s2 + 1;
      let moved = false;
      let maxNewIndexSoFar = 0;
      const newIndexToOldIndexMap = new Array(toBePatched);
      for (i = 0; i < toBePatched; i++) newIndexToOldIndexMap[i] = 0;
      for (i = s1; i <= e1; i++) {
        const prevChild = c1[i];
        if (patched >= toBePatched) {
          unmount(prevChild, parentComponent, parentSuspense, true);
          continue;
        }
        let newIndex;
        if (prevChild.key != null) {
          newIndex = keyToNewIndexMap.get(prevChild.key);
        } else {
          for (j = s2; j <= e2; j++) {
            if (newIndexToOldIndexMap[j - s2] === 0 && isSameVNodeType(prevChild, c2[j])) {
              newIndex = j;
              break;
            }
          }
        }
        if (newIndex === void 0) {
          unmount(prevChild, parentComponent, parentSuspense, true);
        } else {
          newIndexToOldIndexMap[newIndex - s2] = i + 1;
          if (newIndex >= maxNewIndexSoFar) {
            maxNewIndexSoFar = newIndex;
          } else {
            moved = true;
          }
          patch(prevChild, c2[newIndex], container, null, parentComponent, parentSuspense, namespace, slotScopeIds, optimized);
          patched++;
        }
      }
      const increasingNewIndexSequence = moved ? getSequence(newIndexToOldIndexMap) : EMPTY_ARR;
      j = increasingNewIndexSequence.length - 1;
      for (i = toBePatched - 1; i >= 0; i--) {
        const nextIndex = s2 + i;
        const nextChild = c2[nextIndex];
        const anchorVNode = c2[nextIndex + 1];
        const anchor = nextIndex + 1 < l2 ?
        // #13559, fallback to el placeholder for unresolved async component
        anchorVNode.el || anchorVNode.placeholder : parentAnchor;
        if (newIndexToOldIndexMap[i] === 0) {
          patch(null, nextChild, container, anchor, parentComponent, parentSuspense, namespace, slotScopeIds, optimized);
        } else if (moved) {
          if (j < 0 || i !== increasingNewIndexSequence[j]) {
            _move(nextChild, container, anchor, 2);
          } else {
            j--;
          }
        }
      }
    }
  };
  const _move = function move(vnode, container, anchor, moveType, parentSuspense) {
    if (parentSuspense === void 0) {
      parentSuspense = null;
    }
    const {
      el,
      type,
      transition,
      children,
      shapeFlag
    } = vnode;
    if (shapeFlag & 6) {
      _move(vnode.component.subTree, container, anchor, moveType);
      return;
    }
    if (shapeFlag & 128) {
      vnode.suspense.move(container, anchor, moveType);
      return;
    }
    if (shapeFlag & 64) {
      type.move(vnode, container, anchor, internals);
      return;
    }
    if (type === Fragment) {
      hostInsert(el, container, anchor);
      for (let i = 0; i < children.length; i++) {
        _move(children[i], container, anchor, moveType);
      }
      hostInsert(vnode.anchor, container, anchor);
      return;
    }
    if (type === Static) {
      moveStaticNode(vnode, container, anchor);
      return;
    }
    const needTransition2 = moveType !== 2 && shapeFlag & 1 && transition;
    if (needTransition2) {
      if (moveType === 0) {
        transition.beforeEnter(el);
        hostInsert(el, container, anchor);
        queuePostRenderEffect(() => transition.enter(el), parentSuspense);
      } else {
        const {
          leave,
          delayLeave,
          afterLeave
        } = transition;
        const remove2 = () => {
          if (vnode.ctx.isUnmounted) {
            hostRemove(el);
          } else {
            hostInsert(el, container, anchor);
          }
        };
        const performLeave = () => {
          if (el._isLeaving) {
            el[leaveCbKey](true
            /* cancelled */);
          }
          leave(el, () => {
            remove2();
            afterLeave && afterLeave();
          });
        };
        if (delayLeave) {
          delayLeave(el, remove2, performLeave);
        } else {
          performLeave();
        }
      }
    } else {
      hostInsert(el, container, anchor);
    }
  };
  const unmount = function unmount(vnode, parentComponent, parentSuspense, doRemove, optimized) {
    if (doRemove === void 0) {
      doRemove = false;
    }
    if (optimized === void 0) {
      optimized = false;
    }
    const {
      type,
      props,
      ref,
      children,
      dynamicChildren,
      shapeFlag,
      patchFlag,
      dirs,
      cacheIndex
    } = vnode;
    if (patchFlag === -2) {
      optimized = false;
    }
    if (ref != null) {
      pauseTracking();
      setRef(ref, null, parentSuspense, vnode, true);
      resetTracking();
    }
    if (cacheIndex != null) {
      parentComponent.renderCache[cacheIndex] = void 0;
    }
    if (shapeFlag & 256) {
      parentComponent.ctx.deactivate(vnode);
      return;
    }
    const shouldInvokeDirs = shapeFlag & 1 && dirs;
    const shouldInvokeVnodeHook = !isAsyncWrapper(vnode);
    let vnodeHook;
    if (shouldInvokeVnodeHook && (vnodeHook = props && props.onVnodeBeforeUnmount)) {
      invokeVNodeHook(vnodeHook, parentComponent, vnode);
    }
    if (shapeFlag & 6) {
      unmountComponent(vnode.component, parentSuspense, doRemove);
    } else {
      if (shapeFlag & 128) {
        vnode.suspense.unmount(parentSuspense, doRemove);
        return;
      }
      if (shouldInvokeDirs) {
        invokeDirectiveHook(vnode, null, parentComponent, "beforeUnmount");
      }
      if (shapeFlag & 64) {
        vnode.type.remove(vnode, parentComponent, parentSuspense, internals, doRemove);
      } else if (dynamicChildren &&
      // #5154
      // when v-once is used inside a block, setBlockTracking(-1) marks the
      // parent block with hasOnce: true
      // so that it doesn't take the fast path during unmount - otherwise
      // components nested in v-once are never unmounted.
      !dynamicChildren.hasOnce && (
      // #1153: fast path should not be taken for non-stable (v-for) fragments
      type !== Fragment || patchFlag > 0 && patchFlag & 64)) {
        unmountChildren(dynamicChildren, parentComponent, parentSuspense, false, true);
      } else if (type === Fragment && patchFlag & (128 | 256) || !optimized && shapeFlag & 16) {
        unmountChildren(children, parentComponent, parentSuspense);
      }
      if (doRemove) {
        remove(vnode);
      }
    }
    if (shouldInvokeVnodeHook && (vnodeHook = props && props.onVnodeUnmounted) || shouldInvokeDirs) {
      queuePostRenderEffect(() => {
        vnodeHook && invokeVNodeHook(vnodeHook, parentComponent, vnode);
        shouldInvokeDirs && invokeDirectiveHook(vnode, null, parentComponent, "unmounted");
      }, parentSuspense);
    }
  };
  const remove = vnode => {
    const {
      type,
      el,
      anchor,
      transition
    } = vnode;
    if (type === Fragment) {
      {
        removeFragment(el, anchor);
      }
      return;
    }
    if (type === Static) {
      removeStaticNode(vnode);
      return;
    }
    const performRemove = () => {
      hostRemove(el);
      if (transition && !transition.persisted && transition.afterLeave) {
        transition.afterLeave();
      }
    };
    if (vnode.shapeFlag & 1 && transition && !transition.persisted) {
      const {
        leave,
        delayLeave
      } = transition;
      const performLeave = () => leave(el, performRemove);
      if (delayLeave) {
        delayLeave(vnode.el, performRemove, performLeave);
      } else {
        performLeave();
      }
    } else {
      performRemove();
    }
  };
  const removeFragment = (cur, end) => {
    let next;
    while (cur !== end) {
      next = hostNextSibling(cur);
      hostRemove(cur);
      cur = next;
    }
    hostRemove(end);
  };
  const unmountComponent = (instance, parentSuspense, doRemove) => {
    const {
      bum,
      scope,
      job,
      subTree,
      um,
      m,
      a
    } = instance;
    invalidateMount(m);
    invalidateMount(a);
    if (bum) {
      invokeArrayFns(bum);
    }
    scope.stop();
    if (job) {
      job.flags |= 8;
      unmount(subTree, instance, parentSuspense, doRemove);
    }
    if (um) {
      queuePostRenderEffect(um, parentSuspense);
    }
    queuePostRenderEffect(() => {
      instance.isUnmounted = true;
    }, parentSuspense);
  };
  const unmountChildren = function unmountChildren(children, parentComponent, parentSuspense, doRemove, optimized, start) {
    if (doRemove === void 0) {
      doRemove = false;
    }
    if (optimized === void 0) {
      optimized = false;
    }
    if (start === void 0) {
      start = 0;
    }
    for (let i = start; i < children.length; i++) {
      unmount(children[i], parentComponent, parentSuspense, doRemove, optimized);
    }
  };
  const getNextHostNode = vnode => {
    if (vnode.shapeFlag & 6) {
      return getNextHostNode(vnode.component.subTree);
    }
    if (vnode.shapeFlag & 128) {
      return vnode.suspense.next();
    }
    const el = hostNextSibling(vnode.anchor || vnode.el);
    const teleportEnd = el && el[TeleportEndKey];
    return teleportEnd ? hostNextSibling(teleportEnd) : el;
  };
  let isFlushing = false;
  const render = (vnode, container, namespace) => {
    if (vnode == null) {
      if (container._vnode) {
        unmount(container._vnode, null, null, true);
      }
    } else {
      patch(container._vnode || null, vnode, container, null, null, null, namespace);
    }
    container._vnode = vnode;
    if (!isFlushing) {
      isFlushing = true;
      flushPreFlushCbs();
      flushPostFlushCbs();
      isFlushing = false;
    }
  };
  const internals = {
    p: patch,
    um: unmount,
    m: _move,
    r: remove,
    mt: mountComponent,
    mc: mountChildren,
    pc: patchChildren,
    pbc: patchBlockChildren,
    n: getNextHostNode,
    o: options
  };
  let hydrate;
  return {
    render,
    hydrate,
    createApp: createAppAPI(render)
  };
}
function resolveChildrenNamespace(_ref13, currentNamespace) {
  let {
    type,
    props
  } = _ref13;
  return currentNamespace === "svg" && type === "foreignObject" || currentNamespace === "mathml" && type === "annotation-xml" && props && props.encoding && props.encoding.includes("html") ? void 0 : currentNamespace;
}
function toggleRecurse(_ref14, allowed) {
  let {
    effect,
    job
  } = _ref14;
  if (allowed) {
    effect.flags |= 32;
    job.flags |= 4;
  } else {
    effect.flags &= -33;
    job.flags &= -5;
  }
}
function needTransition(parentSuspense, transition) {
  return (!parentSuspense || parentSuspense && !parentSuspense.pendingBranch) && transition && !transition.persisted;
}
function traverseStaticChildren(n1, n2, shallow) {
  const ch1 = n1.children;
  const ch2 = n2.children;
  if (isArray(ch1) && isArray(ch2)) {
    for (let i = 0; i < ch1.length; i++) {
      const c1 = ch1[i];
      let c2 = ch2[i];
      if (c2.shapeFlag & 1 && !c2.dynamicChildren) {
        if (c2.patchFlag <= 0 || c2.patchFlag === 32) {
          c2 = ch2[i] = cloneIfMounted(ch2[i]);
          c2.el = c1.el;
        }
      }
      if (c2.type === Text &&
      // avoid cached text nodes retaining detached dom nodes
      c2.patchFlag !== -1) {
        c2.el = c1.el;
      }
      if (c2.type === Comment && !c2.el) {
        c2.el = c1.el;
      }
    }
  }
}
function getSequence(arr) {
  const p = arr.slice();
  const result = [0];
  let i, j, u, v, c;
  const len = arr.length;
  for (i = 0; i < len; i++) {
    const arrI = arr[i];
    if (arrI !== 0) {
      j = result[result.length - 1];
      if (arr[j] < arrI) {
        p[i] = j;
        result.push(i);
        continue;
      }
      u = 0;
      v = result.length - 1;
      while (u < v) {
        c = u + v >> 1;
        if (arr[result[c]] < arrI) {
          u = c + 1;
        } else {
          v = c;
        }
      }
      if (arrI < arr[result[u]]) {
        if (u > 0) {
          p[i] = result[u - 1];
        }
        result[u] = i;
      }
    }
  }
  u = result.length;
  v = result[u - 1];
  while (u-- > 0) {
    result[u] = v;
    v = p[v];
  }
  return result;
}
function locateNonHydratedAsyncRoot(instance) {
  const subComponent = instance.subTree.component;
  if (subComponent) {
    if (subComponent.asyncDep && !subComponent.asyncResolved) {
      return subComponent;
    } else {
      return locateNonHydratedAsyncRoot(subComponent);
    }
  }
}
function invalidateMount(hooks) {
  if (hooks) {
    for (let i = 0; i < hooks.length; i++) hooks[i].flags |= 8;
  }
}
const ssrContextKey = Symbol.for("v-scx");
const useSSRContext = () => {
  {
    const ctx = inject(ssrContextKey);
    return ctx;
  }
};
function watch(source, cb, options) {
  return doWatch(source, cb, options);
}
function doWatch(source, cb, options) {
  if (options === void 0) {
    options = EMPTY_OBJ;
  }
  const {
    immediate,
    deep,
    flush,
    once
  } = options;
  const baseWatchOptions = extend({}, options);
  const runsImmediately = cb && immediate || !cb && flush !== "post";
  let ssrCleanup;
  if (isInSSRComponentSetup) {
    if (flush === "sync") {
      const ctx = useSSRContext();
      ssrCleanup = ctx.__watcherHandles || (ctx.__watcherHandles = []);
    } else if (!runsImmediately) {
      const watchStopHandle = () => {};
      watchStopHandle.stop = NOOP;
      watchStopHandle.resume = NOOP;
      watchStopHandle.pause = NOOP;
      return watchStopHandle;
    }
  }
  const instance = currentInstance;
  baseWatchOptions.call = (fn, type, args) => callWithAsyncErrorHandling(fn, instance, type, args);
  let isPre = false;
  if (flush === "post") {
    baseWatchOptions.scheduler = job => {
      queuePostRenderEffect(job, instance && instance.suspense);
    };
  } else if (flush !== "sync") {
    isPre = true;
    baseWatchOptions.scheduler = (job, isFirstRun) => {
      if (isFirstRun) {
        job();
      } else {
        queueJob(job);
      }
    };
  }
  baseWatchOptions.augmentJob = job => {
    if (cb) {
      job.flags |= 4;
    }
    if (isPre) {
      job.flags |= 2;
      if (instance) {
        job.id = instance.uid;
        job.i = instance;
      }
    }
  };
  const watchHandle = watch$1(source, cb, baseWatchOptions);
  if (isInSSRComponentSetup) {
    if (ssrCleanup) {
      ssrCleanup.push(watchHandle);
    } else if (runsImmediately) {
      watchHandle();
    }
  }
  return watchHandle;
}
function instanceWatch(source, value, options) {
  const publicThis = this.proxy;
  const getter = isString(source) ? source.includes(".") ? createPathGetter(publicThis, source) : () => publicThis[source] : source.bind(publicThis, publicThis);
  let cb;
  if (isFunction(value)) {
    cb = value;
  } else {
    cb = value.handler;
    options = value;
  }
  const reset = setCurrentInstance(this);
  const res = doWatch(getter, cb.bind(publicThis), options);
  reset();
  return res;
}
function createPathGetter(ctx, path) {
  const segments = path.split(".");
  return () => {
    let cur = ctx;
    for (let i = 0; i < segments.length && cur; i++) {
      cur = cur[segments[i]];
    }
    return cur;
  };
}
const getModelModifiers = (props, modelName) => {
  return modelName === "modelValue" || modelName === "model-value" ? props.modelModifiers : props[modelName + "Modifiers"] || props[camelize(modelName) + "Modifiers"] || props[hyphenate(modelName) + "Modifiers"];
};
function emit(instance, event) {
  if (instance.isUnmounted) return;
  const props = instance.vnode.props || EMPTY_OBJ;
  for (var _len6 = arguments.length, rawArgs = new Array(_len6 > 2 ? _len6 - 2 : 0), _key6 = 2; _key6 < _len6; _key6++) {
    rawArgs[_key6 - 2] = arguments[_key6];
  }
  let args = rawArgs;
  const isModelListener = event.startsWith("update:");
  const modifiers = isModelListener && getModelModifiers(props, event.slice(7));
  if (modifiers) {
    if (modifiers.trim) {
      args = rawArgs.map(a => isString(a) ? a.trim() : a);
    }
    if (modifiers.number) {
      args = rawArgs.map(looseToNumber);
    }
  }
  let handlerName;
  let handler = props[handlerName = toHandlerKey(event)] ||
  // also try camelCase event handler (#2249)
  props[handlerName = toHandlerKey(camelize(event))];
  if (!handler && isModelListener) {
    handler = props[handlerName = toHandlerKey(hyphenate(event))];
  }
  if (handler) {
    callWithAsyncErrorHandling(handler, instance, 6, args);
  }
  const onceHandler = props[handlerName + "Once"];
  if (onceHandler) {
    if (!instance.emitted) {
      instance.emitted = {};
    } else if (instance.emitted[handlerName]) {
      return;
    }
    instance.emitted[handlerName] = true;
    callWithAsyncErrorHandling(onceHandler, instance, 6, args);
  }
}
const mixinEmitsCache = /* @__PURE__ */new WeakMap();
function normalizeEmitsOptions(comp, appContext, asMixin) {
  if (asMixin === void 0) {
    asMixin = false;
  }
  const cache = asMixin ? mixinEmitsCache : appContext.emitsCache;
  const cached = cache.get(comp);
  if (cached !== void 0) {
    return cached;
  }
  const raw = comp.emits;
  let normalized = {};
  let hasExtends = false;
  if (!isFunction(comp)) {
    const extendEmits = raw2 => {
      const normalizedFromExtend = normalizeEmitsOptions(raw2, appContext, true);
      if (normalizedFromExtend) {
        hasExtends = true;
        extend(normalized, normalizedFromExtend);
      }
    };
    if (!asMixin && appContext.mixins.length) {
      appContext.mixins.forEach(extendEmits);
    }
    if (comp.extends) {
      extendEmits(comp.extends);
    }
    if (comp.mixins) {
      comp.mixins.forEach(extendEmits);
    }
  }
  if (!raw && !hasExtends) {
    if (isObject$1(comp)) {
      cache.set(comp, null);
    }
    return null;
  }
  if (isArray(raw)) {
    raw.forEach(key => normalized[key] = null);
  } else {
    extend(normalized, raw);
  }
  if (isObject$1(comp)) {
    cache.set(comp, normalized);
  }
  return normalized;
}
function isEmitListener(options, key) {
  if (!options || !isOn(key)) {
    return false;
  }
  key = key.slice(2).replace(/Once$/, "");
  return hasOwn(options, key[0].toLowerCase() + key.slice(1)) || hasOwn(options, hyphenate(key)) || hasOwn(options, key);
}
function markAttrsAccessed() {
}
function renderComponentRoot(instance) {
  const {
    type: Component,
    vnode,
    proxy,
    withProxy,
    propsOptions: [propsOptions],
    slots,
    attrs,
    emit,
    render,
    renderCache,
    props,
    data,
    setupState,
    ctx,
    inheritAttrs
  } = instance;
  const prev = setCurrentRenderingInstance(instance);
  let result;
  let fallthroughAttrs;
  try {
    if (vnode.shapeFlag & 4) {
      const proxyToUse = withProxy || proxy;
      const thisProxy = !!("production" !== "production") && setupState.__isScriptSetup ? new Proxy(proxyToUse, {
        get(target, key, receiver) {
          warn$1("Property '" + String(key) + "' was accessed via 'this'. Avoid using 'this' in templates.");
          return Reflect.get(target, key, receiver);
        }
      }) : proxyToUse;
      result = normalizeVNode(render.call(thisProxy, proxyToUse, renderCache, !!("production" !== "production") ? shallowReadonly(props) : props, setupState, data, ctx));
      fallthroughAttrs = attrs;
    } else {
      const render2 = Component;
      if (!!("production" !== "production") && attrs === props) ;
      result = normalizeVNode(render2.length > 1 ? render2(!!("production" !== "production") ? shallowReadonly(props) : props, !!("production" !== "production") ? {
        get attrs() {
          markAttrsAccessed();
          return shallowReadonly(attrs);
        },
        slots,
        emit
      } : {
        attrs,
        slots,
        emit
      }) : render2(!!("production" !== "production") ? shallowReadonly(props) : props, null));
      fallthroughAttrs = Component.props ? attrs : getFunctionalFallthrough(attrs);
    }
  } catch (err) {
    blockStack.length = 0;
    handleError$1(err, instance, 1);
    result = createVNode(Comment);
  }
  let root = result;
  if (fallthroughAttrs && inheritAttrs !== false) {
    const keys = Object.keys(fallthroughAttrs);
    const {
      shapeFlag
    } = root;
    if (keys.length) {
      if (shapeFlag & (1 | 6)) {
        if (propsOptions && keys.some(isModelListener)) {
          fallthroughAttrs = filterModelListeners(fallthroughAttrs, propsOptions);
        }
        root = cloneVNode(root, fallthroughAttrs, false, true);
      }
    }
  }
  if (vnode.dirs) {
    root = cloneVNode(root, null, false, true);
    root.dirs = root.dirs ? root.dirs.concat(vnode.dirs) : vnode.dirs;
  }
  if (vnode.transition) {
    setTransitionHooks(root, vnode.transition);
  }
  {
    result = root;
  }
  setCurrentRenderingInstance(prev);
  return result;
}
const getFunctionalFallthrough = attrs => {
  let res;
  for (const key in attrs) {
    if (key === "class" || key === "style" || isOn(key)) {
      (res || (res = {}))[key] = attrs[key];
    }
  }
  return res;
};
const filterModelListeners = (attrs, props) => {
  const res = {};
  for (const key in attrs) {
    if (!isModelListener(key) || !(key.slice(9) in props)) {
      res[key] = attrs[key];
    }
  }
  return res;
};
function shouldUpdateComponent(prevVNode, nextVNode, optimized) {
  const {
    props: prevProps,
    children: prevChildren,
    component
  } = prevVNode;
  const {
    props: nextProps,
    children: nextChildren,
    patchFlag
  } = nextVNode;
  const emits = component.emitsOptions;
  if (nextVNode.dirs || nextVNode.transition) {
    return true;
  }
  if (optimized && patchFlag >= 0) {
    if (patchFlag & 1024) {
      return true;
    }
    if (patchFlag & 16) {
      if (!prevProps) {
        return !!nextProps;
      }
      return hasPropsChanged(prevProps, nextProps, emits);
    } else if (patchFlag & 8) {
      const dynamicProps = nextVNode.dynamicProps;
      for (let i = 0; i < dynamicProps.length; i++) {
        const key = dynamicProps[i];
        if (nextProps[key] !== prevProps[key] && !isEmitListener(emits, key)) {
          return true;
        }
      }
    }
  } else {
    if (prevChildren || nextChildren) {
      if (!nextChildren || !nextChildren.$stable) {
        return true;
      }
    }
    if (prevProps === nextProps) {
      return false;
    }
    if (!prevProps) {
      return !!nextProps;
    }
    if (!nextProps) {
      return true;
    }
    return hasPropsChanged(prevProps, nextProps, emits);
  }
  return false;
}
function hasPropsChanged(prevProps, nextProps, emitsOptions) {
  const nextKeys = Object.keys(nextProps);
  if (nextKeys.length !== Object.keys(prevProps).length) {
    return true;
  }
  for (let i = 0; i < nextKeys.length; i++) {
    const key = nextKeys[i];
    if (nextProps[key] !== prevProps[key] && !isEmitListener(emitsOptions, key)) {
      return true;
    }
  }
  return false;
}
function updateHOCHostEl(_ref15, el) {
  let {
    vnode,
    parent
  } = _ref15;
  while (parent) {
    const root = parent.subTree;
    if (root.suspense && root.suspense.activeBranch === vnode) {
      root.el = vnode.el;
    }
    if (root === vnode) {
      (vnode = parent.vnode).el = el;
      parent = parent.parent;
    } else {
      break;
    }
  }
}
const isSuspense = type => type.__isSuspense;
function queueEffectWithSuspense(fn, suspense) {
  if (suspense && suspense.pendingBranch) {
    if (isArray(fn)) {
      suspense.effects.push(...fn);
    } else {
      suspense.effects.push(fn);
    }
  } else {
    queuePostFlushCb(fn);
  }
}
const Fragment = Symbol.for("v-fgt");
const Text = Symbol.for("v-txt");
const Comment = Symbol.for("v-cmt");
const Static = Symbol.for("v-stc");
const blockStack = [];
let currentBlock = null;
function openBlock(disableTracking) {
  if (disableTracking === void 0) {
    disableTracking = false;
  }
  blockStack.push(currentBlock = disableTracking ? null : []);
}
function closeBlock() {
  blockStack.pop();
  currentBlock = blockStack[blockStack.length - 1] || null;
}
let isBlockTreeEnabled = 1;
function setBlockTracking(value, inVOnce) {
  if (inVOnce === void 0) {
    inVOnce = false;
  }
  isBlockTreeEnabled += value;
  if (value < 0 && currentBlock && inVOnce) {
    currentBlock.hasOnce = true;
  }
}
function setupBlock(vnode) {
  vnode.dynamicChildren = isBlockTreeEnabled > 0 ? currentBlock || EMPTY_ARR : null;
  closeBlock();
  if (isBlockTreeEnabled > 0 && currentBlock) {
    currentBlock.push(vnode);
  }
  return vnode;
}
function createElementBlock(type, props, children, patchFlag, dynamicProps, shapeFlag) {
  return setupBlock(createBaseVNode(type, props, children, patchFlag, dynamicProps, shapeFlag, true));
}
function createBlock(type, props, children, patchFlag, dynamicProps) {
  return setupBlock(createVNode(type, props, children, patchFlag, dynamicProps, true));
}
function isVNode(value) {
  return value ? value.__v_isVNode === true : false;
}
function isSameVNodeType(n1, n2) {
  return n1.type === n2.type && n1.key === n2.key;
}
const normalizeKey = _ref17 => {
  let {
    key
  } = _ref17;
  return key != null ? key : null;
};
const normalizeRef = _ref18 => {
  let {
    ref,
    ref_key,
    ref_for
  } = _ref18;
  if (typeof ref === "number") {
    ref = "" + ref;
  }
  return ref != null ? isString(ref) || isRef(ref) || isFunction(ref) ? {
    i: currentRenderingInstance,
    r: ref,
    k: ref_key,
    f: !!ref_for
  } : ref : null;
};
function createBaseVNode(type, props, children, patchFlag, dynamicProps, shapeFlag, isBlockNode, needFullChildrenNormalization) {
  if (props === void 0) {
    props = null;
  }
  if (children === void 0) {
    children = null;
  }
  if (patchFlag === void 0) {
    patchFlag = 0;
  }
  if (dynamicProps === void 0) {
    dynamicProps = null;
  }
  if (shapeFlag === void 0) {
    shapeFlag = type === Fragment ? 0 : 1;
  }
  if (isBlockNode === void 0) {
    isBlockNode = false;
  }
  if (needFullChildrenNormalization === void 0) {
    needFullChildrenNormalization = false;
  }
  const vnode = {
    __v_isVNode: true,
    __v_skip: true,
    type,
    props,
    key: props && normalizeKey(props),
    ref: props && normalizeRef(props),
    scopeId: currentScopeId,
    slotScopeIds: null,
    children,
    component: null,
    suspense: null,
    ssContent: null,
    ssFallback: null,
    dirs: null,
    transition: null,
    el: null,
    anchor: null,
    target: null,
    targetStart: null,
    targetAnchor: null,
    staticCount: 0,
    shapeFlag,
    patchFlag,
    dynamicProps,
    dynamicChildren: null,
    appContext: null,
    ctx: currentRenderingInstance
  };
  if (needFullChildrenNormalization) {
    normalizeChildren(vnode, children);
    if (shapeFlag & 128) {
      type.normalize(vnode);
    }
  } else if (children) {
    vnode.shapeFlag |= isString(children) ? 8 : 16;
  }
  if (isBlockTreeEnabled > 0 &&
  // avoid a block node from tracking itself
  !isBlockNode &&
  // has current parent block
  currentBlock && (
  // presence of a patch flag indicates this node needs patching on updates.
  // component nodes also should always be patched, because even if the
  // component doesn't need to update, it needs to persist the instance on to
  // the next vnode so that it can be properly unmounted later.
  vnode.patchFlag > 0 || shapeFlag & 6) &&
  // the EVENTS flag is only for hydration and if it is the only flag, the
  // vnode should not be considered dynamic due to handler caching.
  vnode.patchFlag !== 32) {
    currentBlock.push(vnode);
  }
  return vnode;
}
const createVNode = _createVNode;
function _createVNode(type, props, children, patchFlag, dynamicProps, isBlockNode) {
  if (props === void 0) {
    props = null;
  }
  if (children === void 0) {
    children = null;
  }
  if (patchFlag === void 0) {
    patchFlag = 0;
  }
  if (dynamicProps === void 0) {
    dynamicProps = null;
  }
  if (isBlockNode === void 0) {
    isBlockNode = false;
  }
  if (!type || type === NULL_DYNAMIC_COMPONENT) {
    type = Comment;
  }
  if (isVNode(type)) {
    const cloned = cloneVNode(type, props, true
    /* mergeRef: true */);
    if (children) {
      normalizeChildren(cloned, children);
    }
    if (isBlockTreeEnabled > 0 && !isBlockNode && currentBlock) {
      if (cloned.shapeFlag & 6) {
        currentBlock[currentBlock.indexOf(type)] = cloned;
      } else {
        currentBlock.push(cloned);
      }
    }
    cloned.patchFlag = -2;
    return cloned;
  }
  if (isClassComponent(type)) {
    type = type.__vccOpts;
  }
  if (props) {
    props = guardReactiveProps(props);
    let {
      class: klass,
      style
    } = props;
    if (klass && !isString(klass)) {
      props.class = normalizeClass(klass);
    }
    if (isObject$1(style)) {
      if (isProxy(style) && !isArray(style)) {
        style = extend({}, style);
      }
      props.style = normalizeStyle(style);
    }
  }
  const shapeFlag = isString(type) ? 1 : isSuspense(type) ? 128 : isTeleport(type) ? 64 : isObject$1(type) ? 4 : isFunction(type) ? 2 : 0;
  return createBaseVNode(type, props, children, patchFlag, dynamicProps, shapeFlag, isBlockNode, true);
}
function guardReactiveProps(props) {
  if (!props) return null;
  return isProxy(props) || isInternalObject(props) ? extend({}, props) : props;
}
function cloneVNode(vnode, extraProps, mergeRef, cloneTransition) {
  if (mergeRef === void 0) {
    mergeRef = false;
  }
  if (cloneTransition === void 0) {
    cloneTransition = false;
  }
  const {
    props,
    ref,
    patchFlag,
    children,
    transition
  } = vnode;
  const mergedProps = extraProps ? mergeProps(props || {}, extraProps) : props;
  const cloned = {
    __v_isVNode: true,
    __v_skip: true,
    type: vnode.type,
    props: mergedProps,
    key: mergedProps && normalizeKey(mergedProps),
    ref: extraProps && extraProps.ref ?
    // #2078 in the case of <component :is="vnode" ref="extra"/>
    // if the vnode itself already has a ref, cloneVNode will need to merge
    // the refs so the single vnode can be set on multiple refs
    mergeRef && ref ? isArray(ref) ? ref.concat(normalizeRef(extraProps)) : [ref, normalizeRef(extraProps)] : normalizeRef(extraProps) : ref,
    scopeId: vnode.scopeId,
    slotScopeIds: vnode.slotScopeIds,
    children: children,
    target: vnode.target,
    targetStart: vnode.targetStart,
    targetAnchor: vnode.targetAnchor,
    staticCount: vnode.staticCount,
    shapeFlag: vnode.shapeFlag,
    // if the vnode is cloned with extra props, we can no longer assume its
    // existing patch flag to be reliable and need to add the FULL_PROPS flag.
    // note: preserve flag for fragments since they use the flag for children
    // fast paths only.
    patchFlag: extraProps && vnode.type !== Fragment ? patchFlag === -1 ? 16 : patchFlag | 16 : patchFlag,
    dynamicProps: vnode.dynamicProps,
    dynamicChildren: vnode.dynamicChildren,
    appContext: vnode.appContext,
    dirs: vnode.dirs,
    transition,
    // These should technically only be non-null on mounted VNodes. However,
    // they *should* be copied for kept-alive vnodes. So we just always copy
    // them since them being non-null during a mount doesn't affect the logic as
    // they will simply be overwritten.
    component: vnode.component,
    suspense: vnode.suspense,
    ssContent: vnode.ssContent && cloneVNode(vnode.ssContent),
    ssFallback: vnode.ssFallback && cloneVNode(vnode.ssFallback),
    placeholder: vnode.placeholder,
    el: vnode.el,
    anchor: vnode.anchor,
    ctx: vnode.ctx,
    ce: vnode.ce
  };
  if (transition && cloneTransition) {
    setTransitionHooks(cloned, transition.clone(cloned));
  }
  return cloned;
}
function createTextVNode(text, flag) {
  if (text === void 0) {
    text = " ";
  }
  if (flag === void 0) {
    flag = 0;
  }
  return createVNode(Text, null, text, flag);
}
function createCommentVNode(text, asBlock) {
  return asBlock ? (openBlock(), createBlock(Comment, null, text)) : createVNode(Comment, null, text);
}
function normalizeVNode(child) {
  if (child == null || typeof child === "boolean") {
    return createVNode(Comment);
  } else if (isArray(child)) {
    return createVNode(Fragment, null,
    // #3666, avoid reference pollution when reusing vnode
    child.slice());
  } else if (isVNode(child)) {
    return cloneIfMounted(child);
  } else {
    return createVNode(Text, null, String(child));
  }
}
function cloneIfMounted(child) {
  return child.el === null && child.patchFlag !== -1 || child.memo ? child : cloneVNode(child);
}
function normalizeChildren(vnode, children) {
  let type = 0;
  const {
    shapeFlag
  } = vnode;
  if (children == null) {
    children = null;
  } else if (isArray(children)) {
    type = 16;
  } else if (typeof children === "object") {
    if (shapeFlag & (1 | 64)) {
      const slot = children.default;
      if (slot) {
        slot._c && (slot._d = false);
        normalizeChildren(vnode, slot());
        slot._c && (slot._d = true);
      }
      return;
    } else {
      type = 32;
      const slotFlag = children._;
      if (!slotFlag && !isInternalObject(children)) {
        children._ctx = currentRenderingInstance;
      } else if (slotFlag === 3 && currentRenderingInstance) {
        if (currentRenderingInstance.slots._ === 1) {
          children._ = 1;
        } else {
          children._ = 2;
          vnode.patchFlag |= 1024;
        }
      }
    }
  } else if (isFunction(children)) {
    children = {
      default: children,
      _ctx: currentRenderingInstance
    };
    type = 32;
  } else {
    children = String(children);
    if (shapeFlag & 64) {
      type = 16;
      children = [createTextVNode(children)];
    } else {
      type = 8;
    }
  }
  vnode.children = children;
  vnode.shapeFlag |= type;
}
function mergeProps() {
  const ret = {};
  for (let i = 0; i < arguments.length; i++) {
    const toMerge = i < 0 || arguments.length <= i ? undefined : arguments[i];
    for (const key in toMerge) {
      if (key === "class") {
        if (ret.class !== toMerge.class) {
          ret.class = normalizeClass([ret.class, toMerge.class]);
        }
      } else if (key === "style") {
        ret.style = normalizeStyle([ret.style, toMerge.style]);
      } else if (isOn(key)) {
        const existing = ret[key];
        const incoming = toMerge[key];
        if (incoming && existing !== incoming && !(isArray(existing) && existing.includes(incoming))) {
          ret[key] = existing ? [].concat(existing, incoming) : incoming;
        }
      } else if (key !== "") {
        ret[key] = toMerge[key];
      }
    }
  }
  return ret;
}
function invokeVNodeHook(hook, instance, vnode, prevVNode) {
  if (prevVNode === void 0) {
    prevVNode = null;
  }
  callWithAsyncErrorHandling(hook, instance, 7, [vnode, prevVNode]);
}
const emptyAppContext = createAppContext();
let uid = 0;
function createComponentInstance(vnode, parent, suspense) {
  const type = vnode.type;
  const appContext = (parent ? parent.appContext : vnode.appContext) || emptyAppContext;
  const instance = {
    uid: uid++,
    vnode,
    type,
    parent,
    appContext,
    root: null,
    // to be immediately set
    next: null,
    subTree: null,
    // will be set synchronously right after creation
    effect: null,
    update: null,
    // will be set synchronously right after creation
    job: null,
    scope: new EffectScope(true
    /* detached */),
    render: null,
    proxy: null,
    exposed: null,
    exposeProxy: null,
    withProxy: null,
    provides: parent ? parent.provides : Object.create(appContext.provides),
    ids: parent ? parent.ids : ["", 0, 0],
    accessCache: null,
    renderCache: [],
    // local resolved assets
    components: null,
    directives: null,
    // resolved props and emits options
    propsOptions: normalizePropsOptions(type, appContext),
    emitsOptions: normalizeEmitsOptions(type, appContext),
    // emit
    emit: null,
    // to be set immediately
    emitted: null,
    // props default value
    propsDefaults: EMPTY_OBJ,
    // inheritAttrs
    inheritAttrs: type.inheritAttrs,
    // state
    ctx: EMPTY_OBJ,
    data: EMPTY_OBJ,
    props: EMPTY_OBJ,
    attrs: EMPTY_OBJ,
    slots: EMPTY_OBJ,
    refs: EMPTY_OBJ,
    setupState: EMPTY_OBJ,
    setupContext: null,
    // suspense related
    suspense,
    suspenseId: suspense ? suspense.pendingId : 0,
    asyncDep: null,
    asyncResolved: false,
    // lifecycle hooks
    // not using enums here because it results in computed properties
    isMounted: false,
    isUnmounted: false,
    isDeactivated: false,
    bc: null,
    c: null,
    bm: null,
    m: null,
    bu: null,
    u: null,
    um: null,
    bum: null,
    da: null,
    a: null,
    rtg: null,
    rtc: null,
    ec: null,
    sp: null
  };
  {
    instance.ctx = {
      _: instance
    };
  }
  instance.root = parent ? parent.root : instance;
  instance.emit = emit.bind(null, instance);
  if (vnode.ce) {
    vnode.ce(instance);
  }
  return instance;
}
let currentInstance = null;
const getCurrentInstance = () => currentInstance || currentRenderingInstance;
let internalSetCurrentInstance;
let setInSSRSetupState;
{
  const g = getGlobalThis();
  const registerGlobalSetter = (key, setter) => {
    let setters;
    if (!(setters = g[key])) setters = g[key] = [];
    setters.push(setter);
    return v => {
      if (setters.length > 1) setters.forEach(set => set(v));else setters[0](v);
    };
  };
  internalSetCurrentInstance = registerGlobalSetter("__VUE_INSTANCE_SETTERS__", v => currentInstance = v);
  setInSSRSetupState = registerGlobalSetter("__VUE_SSR_SETTERS__", v => isInSSRComponentSetup = v);
}
const setCurrentInstance = instance => {
  const prev = currentInstance;
  internalSetCurrentInstance(instance);
  instance.scope.on();
  return () => {
    instance.scope.off();
    internalSetCurrentInstance(prev);
  };
};
const unsetCurrentInstance = () => {
  currentInstance && currentInstance.scope.off();
  internalSetCurrentInstance(null);
};
function isStatefulComponent(instance) {
  return instance.vnode.shapeFlag & 4;
}
let isInSSRComponentSetup = false;
function setupComponent(instance, isSSR, optimized) {
  if (isSSR === void 0) {
    isSSR = false;
  }
  if (optimized === void 0) {
    optimized = false;
  }
  isSSR && setInSSRSetupState(isSSR);
  const {
    props,
    children
  } = instance.vnode;
  const isStateful = isStatefulComponent(instance);
  initProps(instance, props, isStateful, isSSR);
  initSlots(instance, children, optimized || isSSR);
  const setupResult = isStateful ? setupStatefulComponent(instance, isSSR) : void 0;
  isSSR && setInSSRSetupState(false);
  return setupResult;
}
function setupStatefulComponent(instance, isSSR) {
  const Component = instance.type;
  instance.accessCache = /* @__PURE__ */Object.create(null);
  instance.proxy = new Proxy(instance.ctx, PublicInstanceProxyHandlers);
  const {
    setup
  } = Component;
  if (setup) {
    pauseTracking();
    const setupContext = instance.setupContext = setup.length > 1 ? createSetupContext(instance) : null;
    const reset = setCurrentInstance(instance);
    const setupResult = callWithErrorHandling(setup, instance, 0, [instance.props, setupContext]);
    const isAsyncSetup = isPromise$1(setupResult);
    resetTracking();
    reset();
    if ((isAsyncSetup || instance.sp) && !isAsyncWrapper(instance)) {
      markAsyncBoundary(instance);
    }
    if (isAsyncSetup) {
      setupResult.then(unsetCurrentInstance, unsetCurrentInstance);
      if (isSSR) {
        return setupResult.then(resolvedResult => {
          handleSetupResult(instance, resolvedResult, isSSR);
        }).catch(e => {
          handleError$1(e, instance, 0);
        });
      } else {
        instance.asyncDep = setupResult;
      }
    } else {
      handleSetupResult(instance, setupResult, isSSR);
    }
  } else {
    finishComponentSetup(instance, isSSR);
  }
}
function handleSetupResult(instance, setupResult, isSSR) {
  if (isFunction(setupResult)) {
    if (instance.type.__ssrInlineRender) {
      instance.ssrRender = setupResult;
    } else {
      instance.render = setupResult;
    }
  } else if (isObject$1(setupResult)) {
    instance.setupState = proxyRefs(setupResult);
  } else ;
  finishComponentSetup(instance, isSSR);
}
let compile;
function finishComponentSetup(instance, isSSR, skipOptions) {
  const Component = instance.type;
  if (!instance.render) {
    if (!isSSR && compile && !Component.render) {
      const template = Component.template || resolveMergedOptions(instance).template;
      if (template) {
        const {
          isCustomElement,
          compilerOptions
        } = instance.appContext.config;
        const {
          delimiters,
          compilerOptions: componentCompilerOptions
        } = Component;
        const finalCompilerOptions = extend(extend({
          isCustomElement,
          delimiters
        }, compilerOptions), componentCompilerOptions);
        Component.render = compile(template, finalCompilerOptions);
      }
    }
    instance.render = Component.render || NOOP;
  }
  {
    const reset = setCurrentInstance(instance);
    pauseTracking();
    try {
      applyOptions(instance);
    } finally {
      resetTracking();
      reset();
    }
  }
}
const attrsProxyHandlers = {
  get(target, key) {
    track(target, "get", "");
    return target[key];
  }
};
function createSetupContext(instance) {
  const expose = exposed => {
    instance.exposed = exposed || {};
  };
  {
    return {
      attrs: new Proxy(instance.attrs, attrsProxyHandlers),
      slots: instance.slots,
      emit: instance.emit,
      expose
    };
  }
}
function getComponentPublicInstance(instance) {
  if (instance.exposed) {
    return instance.exposeProxy || (instance.exposeProxy = new Proxy(proxyRefs(markRaw(instance.exposed)), {
      get(target, key) {
        if (key in target) {
          return target[key];
        } else if (key in publicPropertiesMap) {
          return publicPropertiesMap[key](instance);
        }
      },
      has(target, key) {
        return key in target || key in publicPropertiesMap;
      }
    }));
  } else {
    return instance.proxy;
  }
}
const classifyRE = /(?:^|[-_])\w/g;
const classify = str => str.replace(classifyRE, c => c.toUpperCase()).replace(/[-_]/g, "");
function getComponentName(Component, includeInferred) {
  if (includeInferred === void 0) {
    includeInferred = true;
  }
  return isFunction(Component) ? Component.displayName || Component.name : Component.name || includeInferred && Component.__name;
}
function formatComponentName(instance, Component, isRoot) {
  if (isRoot === void 0) {
    isRoot = false;
  }
  let name = getComponentName(Component);
  if (!name && Component.__file) {
    const match = Component.__file.match(/([^/\\]+)\.\w+$/);
    if (match) {
      name = match[1];
    }
  }
  if (!name && instance && instance.parent) {
    const inferFromRegistry = registry => {
      for (const key in registry) {
        if (registry[key] === Component) {
          return key;
        }
      }
    };
    name = inferFromRegistry(instance.components || instance.parent.type.components) || inferFromRegistry(instance.appContext.components);
  }
  return name ? classify(name) : isRoot ? "App" : "Anonymous";
}
function isClassComponent(value) {
  return isFunction(value) && "__vccOpts" in value;
}
const computed = (getterOrOptions, debugOptions) => {
  const c = computed$1(getterOrOptions, debugOptions, isInSSRComponentSetup);
  return c;
};
function h(type, propsOrChildren, children) {
  try {
    setBlockTracking(-1);
    const l = arguments.length;
    if (l === 2) {
      if (isObject$1(propsOrChildren) && !isArray(propsOrChildren)) {
        if (isVNode(propsOrChildren)) {
          return createVNode(type, null, [propsOrChildren]);
        }
        return createVNode(type, propsOrChildren);
      } else {
        return createVNode(type, null, propsOrChildren);
      }
    } else {
      if (l > 3) {
        children = Array.prototype.slice.call(arguments, 2);
      } else if (l === 3 && isVNode(children)) {
        children = [children];
      }
      return createVNode(type, propsOrChildren, children);
    }
  } finally {
    setBlockTracking(1);
  }
}
const version = "3.5.22";

/**
* @vue/runtime-dom v3.5.22
* (c) 2018-present Yuxi (Evan) You and Vue contributors
* @license MIT
**/
let policy = void 0;
const tt = typeof window !== "undefined" && window.trustedTypes;
if (tt) {
  try {
    policy = /* @__PURE__ */tt.createPolicy("vue", {
      createHTML: val => val
    });
  } catch (e) {
  }
}
const unsafeToTrustedHTML = policy ? val => policy.createHTML(val) : val => val;
const svgNS = "http://www.w3.org/2000/svg";
const mathmlNS = "http://www.w3.org/1998/Math/MathML";
const doc = typeof document !== "undefined" ? document : null;
const templateContainer = doc && /* @__PURE__ */doc.createElement("template");
const nodeOps = {
  insert: (child, parent, anchor) => {
    parent.insertBefore(child, anchor || null);
  },
  remove: child => {
    const parent = child.parentNode;
    if (parent) {
      parent.removeChild(child);
    }
  },
  createElement: (tag, namespace, is, props) => {
    const el = namespace === "svg" ? doc.createElementNS(svgNS, tag) : namespace === "mathml" ? doc.createElementNS(mathmlNS, tag) : is ? doc.createElement(tag, {
      is
    }) : doc.createElement(tag);
    if (tag === "select" && props && props.multiple != null) {
      el.setAttribute("multiple", props.multiple);
    }
    return el;
  },
  createText: text => doc.createTextNode(text),
  createComment: text => doc.createComment(text),
  setText: (node, text) => {
    node.nodeValue = text;
  },
  setElementText: (el, text) => {
    el.textContent = text;
  },
  parentNode: node => node.parentNode,
  nextSibling: node => node.nextSibling,
  querySelector: selector => doc.querySelector(selector),
  setScopeId(el, id) {
    el.setAttribute(id, "");
  },
  // __UNSAFE__
  // Reason: innerHTML.
  // Static content here can only come from compiled templates.
  // As long as the user only uses trusted templates, this is safe.
  insertStaticContent(content, parent, anchor, namespace, start, end) {
    const before = anchor ? anchor.previousSibling : parent.lastChild;
    if (start && (start === end || start.nextSibling)) {
      while (true) {
        parent.insertBefore(start.cloneNode(true), anchor);
        if (start === end || !(start = start.nextSibling)) break;
      }
    } else {
      templateContainer.innerHTML = unsafeToTrustedHTML(namespace === "svg" ? "<svg>" + content + "</svg>" : namespace === "mathml" ? "<math>" + content + "</math>" : content);
      const template = templateContainer.content;
      if (namespace === "svg" || namespace === "mathml") {
        const wrapper = template.firstChild;
        while (wrapper.firstChild) {
          template.appendChild(wrapper.firstChild);
        }
        template.removeChild(wrapper);
      }
      parent.insertBefore(template, anchor);
    }
    return [
    // first
    before ? before.nextSibling : parent.firstChild,
    // last
    anchor ? anchor.previousSibling : parent.lastChild];
  }
};
const TRANSITION = "transition";
const ANIMATION = "animation";
const vtcKey = Symbol("_vtc");
const DOMTransitionPropsValidators = {
  name: String,
  type: String,
  css: {
    type: Boolean,
    default: true
  },
  duration: [String, Number, Object],
  enterFromClass: String,
  enterActiveClass: String,
  enterToClass: String,
  appearFromClass: String,
  appearActiveClass: String,
  appearToClass: String,
  leaveFromClass: String,
  leaveActiveClass: String,
  leaveToClass: String
};
const TransitionPropsValidators = /* @__PURE__ */extend({}, BaseTransitionPropsValidators, DOMTransitionPropsValidators);
const decorate$1 = t => {
  t.displayName = "Transition";
  t.props = TransitionPropsValidators;
  return t;
};
const Transition = /* @__PURE__ */decorate$1((props, _ref) => {
  let {
    slots
  } = _ref;
  return h(BaseTransition, resolveTransitionProps(props), slots);
});
const callHook = function callHook(hook, args) {
  if (args === void 0) {
    args = [];
  }
  if (isArray(hook)) {
    hook.forEach(h2 => h2(...args));
  } else if (hook) {
    hook(...args);
  }
};
const hasExplicitCallback = hook => {
  return hook ? isArray(hook) ? hook.some(h2 => h2.length > 1) : hook.length > 1 : false;
};
function resolveTransitionProps(rawProps) {
  const baseProps = {};
  for (const key in rawProps) {
    if (!(key in DOMTransitionPropsValidators)) {
      baseProps[key] = rawProps[key];
    }
  }
  if (rawProps.css === false) {
    return baseProps;
  }
  const {
    name = "v",
    type,
    duration,
    enterFromClass = name + "-enter-from",
    enterActiveClass = name + "-enter-active",
    enterToClass = name + "-enter-to",
    appearFromClass = enterFromClass,
    appearActiveClass = enterActiveClass,
    appearToClass = enterToClass,
    leaveFromClass = name + "-leave-from",
    leaveActiveClass = name + "-leave-active",
    leaveToClass = name + "-leave-to"
  } = rawProps;
  const durations = normalizeDuration(duration);
  const enterDuration = durations && durations[0];
  const leaveDuration = durations && durations[1];
  const {
    onBeforeEnter,
    onEnter,
    onEnterCancelled,
    onLeave,
    onLeaveCancelled,
    onBeforeAppear = onBeforeEnter,
    onAppear = onEnter,
    onAppearCancelled = onEnterCancelled
  } = baseProps;
  const finishEnter = (el, isAppear, done, isCancelled) => {
    el._enterCancelled = isCancelled;
    removeTransitionClass(el, isAppear ? appearToClass : enterToClass);
    removeTransitionClass(el, isAppear ? appearActiveClass : enterActiveClass);
    done && done();
  };
  const finishLeave = (el, done) => {
    el._isLeaving = false;
    removeTransitionClass(el, leaveFromClass);
    removeTransitionClass(el, leaveToClass);
    removeTransitionClass(el, leaveActiveClass);
    done && done();
  };
  const makeEnterHook = isAppear => {
    return (el, done) => {
      const hook = isAppear ? onAppear : onEnter;
      const resolve = () => finishEnter(el, isAppear, done);
      callHook(hook, [el, resolve]);
      nextFrame(() => {
        removeTransitionClass(el, isAppear ? appearFromClass : enterFromClass);
        addTransitionClass(el, isAppear ? appearToClass : enterToClass);
        if (!hasExplicitCallback(hook)) {
          whenTransitionEnds(el, type, enterDuration, resolve);
        }
      });
    };
  };
  return extend(baseProps, {
    onBeforeEnter(el) {
      callHook(onBeforeEnter, [el]);
      addTransitionClass(el, enterFromClass);
      addTransitionClass(el, enterActiveClass);
    },
    onBeforeAppear(el) {
      callHook(onBeforeAppear, [el]);
      addTransitionClass(el, appearFromClass);
      addTransitionClass(el, appearActiveClass);
    },
    onEnter: makeEnterHook(false),
    onAppear: makeEnterHook(true),
    onLeave(el, done) {
      el._isLeaving = true;
      const resolve = () => finishLeave(el, done);
      addTransitionClass(el, leaveFromClass);
      if (!el._enterCancelled) {
        forceReflow(el);
        addTransitionClass(el, leaveActiveClass);
      } else {
        addTransitionClass(el, leaveActiveClass);
        forceReflow(el);
      }
      nextFrame(() => {
        if (!el._isLeaving) {
          return;
        }
        removeTransitionClass(el, leaveFromClass);
        addTransitionClass(el, leaveToClass);
        if (!hasExplicitCallback(onLeave)) {
          whenTransitionEnds(el, type, leaveDuration, resolve);
        }
      });
      callHook(onLeave, [el, resolve]);
    },
    onEnterCancelled(el) {
      finishEnter(el, false, void 0, true);
      callHook(onEnterCancelled, [el]);
    },
    onAppearCancelled(el) {
      finishEnter(el, true, void 0, true);
      callHook(onAppearCancelled, [el]);
    },
    onLeaveCancelled(el) {
      finishLeave(el);
      callHook(onLeaveCancelled, [el]);
    }
  });
}
function normalizeDuration(duration) {
  if (duration == null) {
    return null;
  } else if (isObject$1(duration)) {
    return [NumberOf(duration.enter), NumberOf(duration.leave)];
  } else {
    const n = NumberOf(duration);
    return [n, n];
  }
}
function NumberOf(val) {
  const res = toNumber(val);
  return res;
}
function addTransitionClass(el, cls) {
  cls.split(/\s+/).forEach(c => c && el.classList.add(c));
  (el[vtcKey] || (el[vtcKey] = /* @__PURE__ */new Set())).add(cls);
}
function removeTransitionClass(el, cls) {
  cls.split(/\s+/).forEach(c => c && el.classList.remove(c));
  const _vtc = el[vtcKey];
  if (_vtc) {
    _vtc.delete(cls);
    if (!_vtc.size) {
      el[vtcKey] = void 0;
    }
  }
}
function nextFrame(cb) {
  requestAnimationFrame(() => {
    requestAnimationFrame(cb);
  });
}
let endId = 0;
function whenTransitionEnds(el, expectedType, explicitTimeout, resolve) {
  const id = el._endId = ++endId;
  const resolveIfNotStale = () => {
    if (id === el._endId) {
      resolve();
    }
  };
  if (explicitTimeout != null) {
    return setTimeout(resolveIfNotStale, explicitTimeout);
  }
  const {
    type,
    timeout,
    propCount
  } = getTransitionInfo(el, expectedType);
  if (!type) {
    return resolve();
  }
  const endEvent = type + "end";
  let ended = 0;
  const end = () => {
    el.removeEventListener(endEvent, onEnd);
    resolveIfNotStale();
  };
  const onEnd = e => {
    if (e.target === el && ++ended >= propCount) {
      end();
    }
  };
  setTimeout(() => {
    if (ended < propCount) {
      end();
    }
  }, timeout + 1);
  el.addEventListener(endEvent, onEnd);
}
function getTransitionInfo(el, expectedType) {
  const styles = window.getComputedStyle(el);
  const getStyleProperties = key => (styles[key] || "").split(", ");
  const transitionDelays = getStyleProperties(TRANSITION + "Delay");
  const transitionDurations = getStyleProperties(TRANSITION + "Duration");
  const transitionTimeout = getTimeout(transitionDelays, transitionDurations);
  const animationDelays = getStyleProperties(ANIMATION + "Delay");
  const animationDurations = getStyleProperties(ANIMATION + "Duration");
  const animationTimeout = getTimeout(animationDelays, animationDurations);
  let type = null;
  let timeout = 0;
  let propCount = 0;
  if (expectedType === TRANSITION) {
    if (transitionTimeout > 0) {
      type = TRANSITION;
      timeout = transitionTimeout;
      propCount = transitionDurations.length;
    }
  } else if (expectedType === ANIMATION) {
    if (animationTimeout > 0) {
      type = ANIMATION;
      timeout = animationTimeout;
      propCount = animationDurations.length;
    }
  } else {
    timeout = Math.max(transitionTimeout, animationTimeout);
    type = timeout > 0 ? transitionTimeout > animationTimeout ? TRANSITION : ANIMATION : null;
    propCount = type ? type === TRANSITION ? transitionDurations.length : animationDurations.length : 0;
  }
  const hasTransform = type === TRANSITION && /\b(?:transform|all)(?:,|$)/.test(getStyleProperties(TRANSITION + "Property").toString());
  return {
    type,
    timeout,
    propCount,
    hasTransform
  };
}
function getTimeout(delays, durations) {
  while (delays.length < durations.length) {
    delays = delays.concat(delays);
  }
  return Math.max(...durations.map((d, i) => toMs(d) + toMs(delays[i])));
}
function toMs(s) {
  if (s === "auto") return 0;
  return Number(s.slice(0, -1).replace(",", ".")) * 1e3;
}
function forceReflow(el) {
  const targetDocument = el ? el.ownerDocument : document;
  return targetDocument.body.offsetHeight;
}
function patchClass(el, value, isSVG) {
  const transitionClasses = el[vtcKey];
  if (transitionClasses) {
    value = (value ? [value, ...transitionClasses] : [...transitionClasses]).join(" ");
  }
  if (value == null) {
    el.removeAttribute("class");
  } else if (isSVG) {
    el.setAttribute("class", value);
  } else {
    el.className = value;
  }
}
const vShowOriginalDisplay = Symbol("_vod");
const vShowHidden = Symbol("_vsh");
const vShow = {
  // used for prop mismatch check during hydration
  name: "show",
  beforeMount(el, _ref2, _ref3) {
    let {
      value
    } = _ref2;
    let {
      transition
    } = _ref3;
    el[vShowOriginalDisplay] = el.style.display === "none" ? "" : el.style.display;
    if (transition && value) {
      transition.beforeEnter(el);
    } else {
      setDisplay(el, value);
    }
  },
  mounted(el, _ref4, _ref5) {
    let {
      value
    } = _ref4;
    let {
      transition
    } = _ref5;
    if (transition && value) {
      transition.enter(el);
    }
  },
  updated(el, _ref6, _ref7) {
    let {
      value,
      oldValue
    } = _ref6;
    let {
      transition
    } = _ref7;
    if (!value === !oldValue) return;
    if (transition) {
      if (value) {
        transition.beforeEnter(el);
        setDisplay(el, true);
        transition.enter(el);
      } else {
        transition.leave(el, () => {
          setDisplay(el, false);
        });
      }
    } else {
      setDisplay(el, value);
    }
  },
  beforeUnmount(el, _ref8) {
    let {
      value
    } = _ref8;
    setDisplay(el, value);
  }
};
function setDisplay(el, value) {
  el.style.display = value ? el[vShowOriginalDisplay] : "none";
  el[vShowHidden] = !value;
}
const CSS_VAR_TEXT = Symbol("");
const displayRE = /(?:^|;)\s*display\s*:/;
function patchStyle(el, prev, next) {
  const style = el.style;
  const isCssString = isString(next);
  let hasControlledDisplay = false;
  if (next && !isCssString) {
    if (prev) {
      if (!isString(prev)) {
        for (const key in prev) {
          if (next[key] == null) {
            setStyle(style, key, "");
          }
        }
      } else {
        for (const prevStyle of prev.split(";")) {
          const key = prevStyle.slice(0, prevStyle.indexOf(":")).trim();
          if (next[key] == null) {
            setStyle(style, key, "");
          }
        }
      }
    }
    for (const key in next) {
      if (key === "display") {
        hasControlledDisplay = true;
      }
      setStyle(style, key, next[key]);
    }
  } else {
    if (isCssString) {
      if (prev !== next) {
        const cssVarText = style[CSS_VAR_TEXT];
        if (cssVarText) {
          next += ";" + cssVarText;
        }
        style.cssText = next;
        hasControlledDisplay = displayRE.test(next);
      }
    } else if (prev) {
      el.removeAttribute("style");
    }
  }
  if (vShowOriginalDisplay in el) {
    el[vShowOriginalDisplay] = hasControlledDisplay ? style.display : "";
    if (el[vShowHidden]) {
      style.display = "none";
    }
  }
}
const importantRE = /\s*!important$/;
function setStyle(style, name, val) {
  if (isArray(val)) {
    val.forEach(v => setStyle(style, name, v));
  } else {
    if (val == null) val = "";
    if (name.startsWith("--")) {
      style.setProperty(name, val);
    } else {
      const prefixed = autoPrefix(style, name);
      if (importantRE.test(val)) {
        style.setProperty(hyphenate(prefixed), val.replace(importantRE, ""), "important");
      } else {
        style[prefixed] = val;
      }
    }
  }
}
const prefixes = ["Webkit", "Moz", "ms"];
const prefixCache = {};
function autoPrefix(style, rawName) {
  const cached = prefixCache[rawName];
  if (cached) {
    return cached;
  }
  let name = camelize(rawName);
  if (name !== "filter" && name in style) {
    return prefixCache[rawName] = name;
  }
  name = capitalize(name);
  for (let i = 0; i < prefixes.length; i++) {
    const prefixed = prefixes[i] + name;
    if (prefixed in style) {
      return prefixCache[rawName] = prefixed;
    }
  }
  return rawName;
}
const xlinkNS = "http://www.w3.org/1999/xlink";
function patchAttr(el, key, value, isSVG, instance, isBoolean) {
  if (isBoolean === void 0) {
    isBoolean = isSpecialBooleanAttr(key);
  }
  if (isSVG && key.startsWith("xlink:")) {
    if (value == null) {
      el.removeAttributeNS(xlinkNS, key.slice(6, key.length));
    } else {
      el.setAttributeNS(xlinkNS, key, value);
    }
  } else {
    if (value == null || isBoolean && !includeBooleanAttr(value)) {
      el.removeAttribute(key);
    } else {
      el.setAttribute(key, isBoolean ? "" : isSymbol(value) ? String(value) : value);
    }
  }
}
function patchDOMProp(el, key, value, parentComponent, attrName) {
  if (key === "innerHTML" || key === "textContent") {
    if (value != null) {
      el[key] = key === "innerHTML" ? unsafeToTrustedHTML(value) : value;
    }
    return;
  }
  const tag = el.tagName;
  if (key === "value" && tag !== "PROGRESS" &&
  // custom elements may use _value internally
  !tag.includes("-")) {
    const oldValue = tag === "OPTION" ? el.getAttribute("value") || "" : el.value;
    const newValue = value == null ?
    // #11647: value should be set as empty string for null and undefined,
    // but <input type="checkbox"> should be set as 'on'.
    el.type === "checkbox" ? "on" : "" : String(value);
    if (oldValue !== newValue || !("_value" in el)) {
      el.value = newValue;
    }
    if (value == null) {
      el.removeAttribute(key);
    }
    el._value = value;
    return;
  }
  let needRemove = false;
  if (value === "" || value == null) {
    const type = typeof el[key];
    if (type === "boolean") {
      value = includeBooleanAttr(value);
    } else if (value == null && type === "string") {
      value = "";
      needRemove = true;
    } else if (type === "number") {
      value = 0;
      needRemove = true;
    }
  }
  try {
    el[key] = value;
  } catch (e) {
  }
  needRemove && el.removeAttribute(attrName || key);
}
function addEventListener(el, event, handler, options) {
  el.addEventListener(event, handler, options);
}
function removeEventListener(el, event, handler, options) {
  el.removeEventListener(event, handler, options);
}
const veiKey = Symbol("_vei");
function patchEvent(el, rawName, prevValue, nextValue, instance) {
  if (instance === void 0) {
    instance = null;
  }
  const invokers = el[veiKey] || (el[veiKey] = {});
  const existingInvoker = invokers[rawName];
  if (nextValue && existingInvoker) {
    existingInvoker.value = nextValue;
  } else {
    const [name, options] = parseName(rawName);
    if (nextValue) {
      const invoker = invokers[rawName] = createInvoker(nextValue, instance);
      addEventListener(el, name, invoker, options);
    } else if (existingInvoker) {
      removeEventListener(el, name, existingInvoker, options);
      invokers[rawName] = void 0;
    }
  }
}
const optionsModifierRE = /(?:Once|Passive|Capture)$/;
function parseName(name) {
  let options;
  if (optionsModifierRE.test(name)) {
    options = {};
    let m;
    while (m = name.match(optionsModifierRE)) {
      name = name.slice(0, name.length - m[0].length);
      options[m[0].toLowerCase()] = true;
    }
  }
  const event = name[2] === ":" ? name.slice(3) : hyphenate(name.slice(2));
  return [event, options];
}
let cachedNow = 0;
const p = /* @__PURE__ */Promise.resolve();
const getNow = () => cachedNow || (p.then(() => cachedNow = 0), cachedNow = Date.now());
function createInvoker(initialValue, instance) {
  const invoker = e => {
    if (!e._vts) {
      e._vts = Date.now();
    } else if (e._vts <= invoker.attached) {
      return;
    }
    callWithAsyncErrorHandling(patchStopImmediatePropagation(e, invoker.value), instance, 5, [e]);
  };
  invoker.value = initialValue;
  invoker.attached = getNow();
  return invoker;
}
function patchStopImmediatePropagation(e, value) {
  if (isArray(value)) {
    const originalStop = e.stopImmediatePropagation;
    e.stopImmediatePropagation = () => {
      originalStop.call(e);
      e._stopped = true;
    };
    return value.map(fn => e2 => !e2._stopped && fn && fn(e2));
  } else {
    return value;
  }
}
const isNativeOn = key => key.charCodeAt(0) === 111 && key.charCodeAt(1) === 110 &&
// lowercase letter
key.charCodeAt(2) > 96 && key.charCodeAt(2) < 123;
const patchProp = (el, key, prevValue, nextValue, namespace, parentComponent) => {
  const isSVG = namespace === "svg";
  if (key === "class") {
    patchClass(el, nextValue, isSVG);
  } else if (key === "style") {
    patchStyle(el, prevValue, nextValue);
  } else if (isOn(key)) {
    if (!isModelListener(key)) {
      patchEvent(el, key, prevValue, nextValue, parentComponent);
    }
  } else if (key[0] === "." ? (key = key.slice(1), true) : key[0] === "^" ? (key = key.slice(1), false) : shouldSetAsProp(el, key, nextValue, isSVG)) {
    patchDOMProp(el, key, nextValue);
    if (!el.tagName.includes("-") && (key === "value" || key === "checked" || key === "selected")) {
      patchAttr(el, key, nextValue, isSVG, parentComponent, key !== "value");
    }
  } else if (
  // #11081 force set props for possible async custom element
  el._isVueCE && (/[A-Z]/.test(key) || !isString(nextValue))) {
    patchDOMProp(el, camelize(key), nextValue, parentComponent, key);
  } else {
    if (key === "true-value") {
      el._trueValue = nextValue;
    } else if (key === "false-value") {
      el._falseValue = nextValue;
    }
    patchAttr(el, key, nextValue, isSVG);
  }
};
function shouldSetAsProp(el, key, value, isSVG) {
  if (isSVG) {
    if (key === "innerHTML" || key === "textContent") {
      return true;
    }
    if (key in el && isNativeOn(key) && isFunction(value)) {
      return true;
    }
    return false;
  }
  if (key === "spellcheck" || key === "draggable" || key === "translate" || key === "autocorrect") {
    return false;
  }
  if (key === "form") {
    return false;
  }
  if (key === "list" && el.tagName === "INPUT") {
    return false;
  }
  if (key === "type" && el.tagName === "TEXTAREA") {
    return false;
  }
  if (key === "width" || key === "height") {
    const tag = el.tagName;
    if (tag === "IMG" || tag === "VIDEO" || tag === "CANVAS" || tag === "SOURCE") {
      return false;
    }
  }
  if (isNativeOn(key) && isString(value)) {
    return false;
  }
  return key in el;
}
const getModelAssigner = vnode => {
  const fn = vnode.props["onUpdate:modelValue"] || false;
  return isArray(fn) ? value => invokeArrayFns(fn, value) : fn;
};
function onCompositionStart(e) {
  e.target.composing = true;
}
function onCompositionEnd(e) {
  const target = e.target;
  if (target.composing) {
    target.composing = false;
    target.dispatchEvent(new Event("input"));
  }
}
const assignKey = Symbol("_assign");
const vModelText = {
  created(el, _ref1, vnode) {
    let {
      modifiers: {
        lazy,
        trim,
        number
      }
    } = _ref1;
    el[assignKey] = getModelAssigner(vnode);
    const castToNumber = number || vnode.props && vnode.props.type === "number";
    addEventListener(el, lazy ? "change" : "input", e => {
      if (e.target.composing) return;
      let domValue = el.value;
      if (trim) {
        domValue = domValue.trim();
      }
      if (castToNumber) {
        domValue = looseToNumber(domValue);
      }
      el[assignKey](domValue);
    });
    if (trim) {
      addEventListener(el, "change", () => {
        el.value = el.value.trim();
      });
    }
    if (!lazy) {
      addEventListener(el, "compositionstart", onCompositionStart);
      addEventListener(el, "compositionend", onCompositionEnd);
      addEventListener(el, "change", onCompositionEnd);
    }
  },
  // set value on mounted so it's after min/max for type="range"
  mounted(el, _ref10) {
    let {
      value
    } = _ref10;
    el.value = value == null ? "" : value;
  },
  beforeUpdate(el, _ref11, vnode) {
    let {
      value,
      oldValue,
      modifiers: {
        lazy,
        trim,
        number
      }
    } = _ref11;
    el[assignKey] = getModelAssigner(vnode);
    if (el.composing) return;
    const elValue = (number || el.type === "number") && !/^0\d/.test(el.value) ? looseToNumber(el.value) : el.value;
    const newValue = value == null ? "" : value;
    if (elValue === newValue) {
      return;
    }
    if (document.activeElement === el && el.type !== "range") {
      if (lazy && value === oldValue) {
        return;
      }
      if (trim && el.value.trim() === newValue) {
        return;
      }
    }
    el.value = newValue;
  }
};
const systemModifiers = ["ctrl", "shift", "alt", "meta"];
const modifierGuards = {
  stop: e => e.stopPropagation(),
  prevent: e => e.preventDefault(),
  self: e => e.target !== e.currentTarget,
  ctrl: e => !e.ctrlKey,
  shift: e => !e.shiftKey,
  alt: e => !e.altKey,
  meta: e => !e.metaKey,
  left: e => "button" in e && e.button !== 0,
  middle: e => "button" in e && e.button !== 1,
  right: e => "button" in e && e.button !== 2,
  exact: (e, modifiers) => systemModifiers.some(m => e[m + "Key"] && !modifiers.includes(m))
};
const withModifiers = (fn, modifiers) => {
  const cache = fn._withMods || (fn._withMods = {});
  const cacheKey = modifiers.join(".");
  return cache[cacheKey] || (cache[cacheKey] = function (event) {
    for (let i = 0; i < modifiers.length; i++) {
      const guard = modifierGuards[modifiers[i]];
      if (guard && guard(event, modifiers)) return;
    }
    for (var _len2 = arguments.length, args = new Array(_len2 > 1 ? _len2 - 1 : 0), _key2 = 1; _key2 < _len2; _key2++) {
      args[_key2 - 1] = arguments[_key2];
    }
    return fn(event, ...args);
  });
};
const keyNames = {
  esc: "escape",
  space: " ",
  up: "arrow-up",
  left: "arrow-left",
  right: "arrow-right",
  down: "arrow-down",
  delete: "backspace"
};
const withKeys = (fn, modifiers) => {
  const cache = fn._withKeys || (fn._withKeys = {});
  const cacheKey = modifiers.join(".");
  return cache[cacheKey] || (cache[cacheKey] = event => {
    if (!("key" in event)) {
      return;
    }
    const eventKey = hyphenate(event.key);
    if (modifiers.some(k => k === eventKey || keyNames[k] === eventKey)) {
      return fn(event);
    }
  });
};
const rendererOptions = /* @__PURE__ */extend({
  patchProp
}, nodeOps);
let renderer;
function ensureRenderer() {
  return renderer || (renderer = createRenderer(rendererOptions));
}
const createApp = function createApp() {
  const app = ensureRenderer().createApp(...arguments);
  const {
    mount
  } = app;
  app.mount = containerOrSelector => {
    const container = normalizeContainer(containerOrSelector);
    if (!container) return;
    const component = app._component;
    if (!isFunction(component) && !component.render && !component.template) {
      component.template = container.innerHTML;
    }
    if (container.nodeType === 1) {
      container.textContent = "";
    }
    const proxy = mount(container, false, resolveRootNamespace(container));
    if (container instanceof Element) {
      container.removeAttribute("v-cloak");
      container.setAttribute("data-v-app", "");
    }
    return proxy;
  };
  return app;
};
function resolveRootNamespace(container) {
  if (container instanceof SVGElement) {
    return "svg";
  }
  if (typeof MathMLElement === "function" && container instanceof MathMLElement) {
    return "mathml";
  }
}
function normalizeContainer(container) {
  if (isString(container)) {
    const res = document.querySelector(container);
    return res;
  }
  return container;
}

// Loading state
const SET_IS_LOADING = 'SET_IS_LOADING';

// Selecting media items
const SELECT_DIRECTORY = 'SELECT_DIRECTORY';
const SELECT_BROWSER_ITEM = 'SELECT_BROWSER_ITEM';
const SELECT_BROWSER_ITEMS = 'SELECT_BROWSER_ITEMS';
const UNSELECT_BROWSER_ITEM = 'UNSELECT_BROWSER_ITEM';
const UNSELECT_ALL_BROWSER_ITEMS = 'UNSELECT_ALL_BROWSER_ITEMS';

// In/Decrease grid item size
const INCREASE_GRID_SIZE = 'INCREASE_GRID_SIZE';
const DECREASE_GRID_SIZE = 'DECREASE_GRID_SIZE';

// Api handlers
const LOAD_CONTENTS_SUCCESS = 'LOAD_CONTENTS_SUCCESS';
const LOAD_FULL_CONTENTS_SUCCESS = 'LOAD_FULL_CONTENTS_SUCCESS';
const CREATE_DIRECTORY_SUCCESS = 'CREATE_DIRECTORY_SUCCESS';
const UPLOAD_SUCCESS = 'UPLOAD_SUCCESS';

// Create folder modal
const SHOW_CREATE_FOLDER_MODAL = 'SHOW_CREATE_FOLDER_MODAL';
const HIDE_CREATE_FOLDER_MODAL = 'HIDE_CREATE_FOLDER_MODAL';

// Confirm Delete Modal
const SHOW_CONFIRM_DELETE_MODAL = 'SHOW_CONFIRM_DELETE_MODAL';
const HIDE_CONFIRM_DELETE_MODAL = 'HIDE_CONFIRM_DELETE_MODAL';

// Infobar
const SHOW_INFOBAR = 'SHOW_INFOBAR';
const HIDE_INFOBAR = 'HIDE_INFOBAR';

// Delete items
const DELETE_SUCCESS = 'DELETE_SUCCESS';

// List view
const CHANGE_LIST_VIEW = 'CHANGE_LIST_VIEW';

// Preview modal
const SHOW_PREVIEW_MODAL = 'SHOW_PREVIEW_MODAL';
const HIDE_PREVIEW_MODAL = 'HIDE_PREVIEW_MODAL';

// Rename modal
const SHOW_RENAME_MODAL = 'SHOW_RENAME_MODAL';
const HIDE_RENAME_MODAL = 'HIDE_RENAME_MODAL';
const RENAME_SUCCESS = 'RENAME_SUCCESS';

// Share modal
const SHOW_SHARE_MODAL = 'SHOW_SHARE_MODAL';
const HIDE_SHARE_MODAL = 'HIDE_SHARE_MODAL';

// Search Query
const SET_SEARCH_QUERY = 'SET_SEARCH_QUERY';

// Update item properties
const UPDATE_ITEM_PROPERTIES = 'UPDATE_ITEM_PROPERTIES';

// Update sorting by
const UPDATE_SORT_BY = 'UPDATE_SORT_BY';

// Update sorting direction
const UPDATE_SORT_DIRECTION = 'UPDATE_SORT_DIRECTION';

function _extends() {
  return _extends = Object.assign ? Object.assign.bind() : function (n) {
    for (var e = 1; e < arguments.length; e++) {
      var t = arguments[e];
      for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]);
    }
    return n;
  }, _extends.apply(null, arguments);
}

/**
 * Send a notification
 * @param {String} message
 * @param {{}} options
 *
 */
function _notify(message, options) {
  let timer;
  if (options.type === 'message') {
    timer = 3000;
  }
  Joomla.renderMessages({
    [options.type]: [Joomla.Text._(message)]
  }, undefined, true, timer);
}
const notifications = {
  /* Send a success notification */
  success: (message, options) => {
    _notify(message, _extends({
      type: 'message',
      // @todo rename it to success
      dismiss: true
    }, options));
  },
  /* Send an error notification */
  error: (message, options) => {
    _notify(message, _extends({
      type: 'error',
      // @todo rename it to danger
      dismiss: true
    }, options));
  },
  /* Send a general notification */
  notify: (message, options) => {
    _notify(message, _extends({
      type: 'message',
      dismiss: true
    }, options));
  },
  /* Ask the user a question */
  ask: message => window.confirm(message)
};

const dirname = path => {
  if (typeof path !== 'string') {
    throw new TypeError('Path must be a string. Received ' + JSON.stringify(path));
  }
  if (path.length === 0) return '.';
  let code = path.charCodeAt(0);
  const hasRoot = code === 47;
  let end = -1;
  let matchedSlash = true;
  for (let i = path.length - 1; i >= 1; --i) {
    code = path.charCodeAt(i);
    if (code === 47) {
      if (!matchedSlash) {
        end = i;
        break;
      }
    } else {
      // We saw the first non-path separator
      matchedSlash = false;
    }
  }
  if (end === -1) return hasRoot ? '/' : '.';
  if (hasRoot && end === 1) return '//';
  return path.slice(0, end);
};

/**
 * Normalize a single item
 * @param item
 * @returns {*}
 * @private
 */
function normalizeItem(item) {
  if (item.type === 'dir') {
    item.directories = [];
    item.files = [];
  }
  item.directory = dirname(item.path);
  if (item.directory.indexOf(':', item.directory.length - 1) !== -1) {
    item.directory += '/';
  }
  return item;
}

/**
 * Normalize array data
 * @param data
 * @returns {{directories, files}}
 * @private
 */
function normalizeArray(data) {
  const directories = data.filter(item => item.type === 'dir').map(directory => normalizeItem(directory));
  const files = data.filter(item => item.type === 'file').map(file => normalizeItem(file));
  return {
    directories,
    files
  };
}

/**
 * Handle errors
 * @param error
 * @private
 *
 * @TODO DN improve error handling
 */
function handleError(error) {
  const response = JSON.parse(error.response);
  if (response.message) {
    notifications.error(response.message);
    // Check for App messages queue
    if (response.messages) {
      Object.keys(response.messages).forEach(type => {
        response.messages[type].forEach(message => {
          if (type === 'error') {
            notifications.error(message);
          } else {
            notifications.notify(message);
          }
        });
      });
    }
  } else {
    switch (error.status) {
      case 409:
        // Handled in consumer
        break;
      case 404:
        notifications.error('COM_MEDIA_ERROR_NOT_FOUND');
        break;
      case 401:
        notifications.error('COM_MEDIA_ERROR_NOT_AUTHENTICATED');
        break;
      case 403:
        notifications.error('COM_MEDIA_ERROR_NOT_AUTHORIZED');
        break;
      case 500:
        notifications.error('COM_MEDIA_SERVER_ERROR');
        break;
      default:
        notifications.error('COM_MEDIA_ERROR');
    }
  }
  throw error;
}

/**
 * Api class for communication with the server
 */
class Api {
  /**
     * Store constructor
     */
  constructor() {
    const options = Joomla.getOptions('com_media', {});
    if (options.apiBaseUrl === undefined) {
      throw new TypeError('Media api baseUrl is not defined');
    }
    if (options.csrfToken === undefined) {
      throw new TypeError('Media api csrf token is not defined');
    }
    this.baseUrl = options.apiBaseUrl;
    this.csrfToken = Joomla.getOptions('csrf.token');
    this.imagesExtensions = options.imagesExtensions;
    this.audioExtensions = options.audioExtensions;
    this.videoExtensions = options.videoExtensions;
    this.documentExtensions = options.documentExtensions;
    this.mediaVersion = new Date().getTime().toString();
    this.canCreate = options.canCreate || false;
    this.canEdit = options.canEdit || false;
    this.canDelete = options.canDelete || false;
    this.hasMediaActionPlugins = options.hasMediaActionPlugins !== false;
  }

  /**
     * Get the contents of a directory from the server
     * @param {string}   dir  The directory path
     * @param {boolean}  full whether or not the persistent url should be returned
     * @param {boolean}  content whether or not the content should be returned
     * @returns {Promise}
     */
  getContents(dir, full, content) {
    if (full === void 0) {
      full = false;
    }
    if (content === void 0) {
      content = false;
    }
    // Wrap the ajax call into a real promise
    return new Promise((resolve, reject) => {
      const url = new URL(this.baseUrl + "&task=api.files&path=" + encodeURIComponent(dir));
      if (full) {
        url.searchParams.append('url', full);
      }
      if (content) {
        url.searchParams.append('content', content);
      }
      Joomla.request({
        url: url.toString(),
        method: 'GET',
        headers: {
          'Content-Type': 'application/json'
        },
        onSuccess: response => {
          resolve(normalizeArray(JSON.parse(response).data));
        },
        onError: xhr => {
          reject(xhr);
        }
      });
    }).catch(handleError);
  }

  /**
     * Create a directory
     * @param name
     * @param parent
     * @returns {Promise.<T>}
     */
  createDirectory(name, parent) {
    // Wrap the ajax call into a real promise
    return new Promise((resolve, reject) => {
      const url = new URL(this.baseUrl + "&task=api.files&path=" + encodeURIComponent(parent));
      const data = {
        [this.csrfToken]: '1',
        name
      };
      Joomla.request({
        url: url.toString(),
        method: 'POST',
        data: JSON.stringify(data),
        headers: {
          'Content-Type': 'application/json'
        },
        onSuccess: response => {
          notifications.success('COM_MEDIA_CREATE_NEW_FOLDER_SUCCESS');
          resolve(normalizeItem(JSON.parse(response).data));
        },
        onError: xhr => {
          notifications.error('COM_MEDIA_CREATE_NEW_FOLDER_ERROR');
          reject(xhr);
        }
      });
    }).catch(handleError);
  }

  /**
     * Upload a file
     * @param name
     * @param parent
     * @param content base64 encoded string
     * @param override boolean whether or not we should override existing files
     * @return {Promise.<T>}
     */
  upload(name, parent, content, override) {
    // Wrap the ajax call into a real promise
    return new Promise((resolve, reject) => {
      const url = new URL(this.baseUrl + "&task=api.files&path=" + encodeURIComponent(parent));
      const data = {
        [this.csrfToken]: '1',
        name,
        content
      };

      // Append override
      if (override === true) {
        data.override = true;
      }
      Joomla.request({
        url: url.toString(),
        method: 'POST',
        data: JSON.stringify(data),
        headers: {
          'Content-Type': 'application/json'
        },
        onSuccess: response => {
          notifications.success('COM_MEDIA_UPLOAD_SUCCESS');
          resolve(normalizeItem(JSON.parse(response).data));
        },
        onError: xhr => {
          reject(xhr);
        }
      });
    }).catch(handleError);
  }

  /**
     * Rename an item
     * @param path
     * @param newPath
     * @return {Promise.<T>}
     */
  rename(path, newPath) {
    // Wrap the ajax call into a real promise
    return new Promise((resolve, reject) => {
      const url = new URL(this.baseUrl + "&task=api.files&path=" + encodeURIComponent(path));
      const data = {
        [this.csrfToken]: '1',
        newPath
      };
      Joomla.request({
        url: url.toString(),
        method: 'PUT',
        data: JSON.stringify(data),
        headers: {
          'Content-Type': 'application/json'
        },
        onSuccess: response => {
          notifications.success('COM_MEDIA_RENAME_SUCCESS');
          resolve(normalizeItem(JSON.parse(response).data));
        },
        onError: xhr => {
          notifications.error('COM_MEDIA_RENAME_ERROR');
          reject(xhr);
        }
      });
    }).catch(handleError);
  }

  /**
     * Delete a file
     * @param path
     * @return {Promise.<T>}
     */
  delete(path) {
    // Wrap the ajax call into a real promise
    return new Promise((resolve, reject) => {
      const url = new URL(this.baseUrl + "&task=api.files&path=" + encodeURIComponent(path));
      const data = {
        [this.csrfToken]: '1'
      };
      Joomla.request({
        url: url.toString(),
        method: 'DELETE',
        data: JSON.stringify(data),
        headers: {
          'Content-Type': 'application/json'
        },
        onSuccess: () => {
          notifications.success('COM_MEDIA_DELETE_SUCCESS');
          resolve();
        },
        onError: xhr => {
          notifications.error('COM_MEDIA_DELETE_ERROR');
          reject(xhr);
        }
      });
    }).catch(handleError);
  }
}
const api = new Api();

var navigable = {
  methods: {
    navigateTo(path) {
      this.$store.dispatch('getContents', path, false, false);
    }
  }
};

/**
 * Handle the click event
 * @param event
 * @param ctx  the context
 */
function onItemClick(event, ctx) {
  if (ctx.item.path && ctx.item.type === 'file') {
    window.parent.document.dispatchEvent(new CustomEvent('onMediaFileSelected', {
      bubbles: true,
      cancelable: false,
      detail: {
        type: ctx.item.type,
        name: ctx.item.name,
        path: ctx.item.path,
        thumb: ctx.item.thumb,
        fileType: ctx.item.mime_type ? ctx.item.mime_type : false,
        extension: ctx.item.extension ? ctx.item.extension : false,
        width: ctx.item.width ? ctx.item.width : 0,
        height: ctx.item.height ? ctx.item.height : 0
      }
    }));
  }
  if (ctx.item.type === 'dir') {
    window.parent.document.dispatchEvent(new CustomEvent('onMediaFileSelected', {
      bubbles: true,
      cancelable: false,
      detail: {
        type: ctx.item.type,
        name: ctx.item.name,
        path: ctx.item.path
      }
    }));
  }

  // Handle clicks when the item was not selected
  if (!ctx.isSelected()) {
    // Handle clicks when ctrl key was pressed
    if (event[/Mac|Mac OS|MacIntel/gi.test(window.navigator.userAgent) ? 'metaKey' : 'ctrlKey'] || event.keyCode === 17) {
      ctx.$store.commit(SELECT_BROWSER_ITEM, ctx.item);
      return;
    }

    // Unselect when shift key is not pressed
    if (!event.shiftKey && event.keyCode !== 13) {
      ctx.$store.commit(UNSELECT_ALL_BROWSER_ITEMS);
      ctx.$store.commit(SELECT_BROWSER_ITEM, ctx.item);
      return;
    }
    const currentIndex = ctx.localItems.indexOf(ctx.$store.state.selectedItems[0]);
    const endIndex = ctx.localItems.indexOf(ctx.item);
    // Handle selections from up to down
    if (currentIndex < endIndex) {
      ctx.localItems.slice(currentIndex, endIndex + 1).forEach(element => ctx.$store.commit(SELECT_BROWSER_ITEM, element));
      return;
    }

    // Handle selections from down to up
    ctx.localItems.slice(endIndex, currentIndex).forEach(element => ctx.$store.commit(SELECT_BROWSER_ITEM, element));
    return;
  }
  ctx.$store.dispatch('toggleBrowserItemSelect', ctx.item);
  window.parent.document.dispatchEvent(new CustomEvent('onMediaFileSelected', {
    bubbles: true,
    cancelable: false,
    detail: {}
  }));

  // If more than one item was selected and the user clicks again on the selected item,
  // he most probably wants to unselect all other items.
  if (ctx.$store.state.selectedItems.length > 1) {
    ctx.$store.commit(UNSELECT_ALL_BROWSER_ITEMS);
    ctx.$store.commit(SELECT_BROWSER_ITEM, ctx.item);
  }
}

var script$v = {
  name: 'MediaBrowserItemRow',
  mixins: [navigable],
  props: {
    item: {
      type: Object,
      default: () => {},
    },
    localItems: {
      type: Array,
      default: () => [],
    },
  },
  computed: {
    /* The dimension of a file */
    dimension() {
      if (!this.item.width) {
        return '';
      }
      return `${this.item.width}px * ${this.item.height}px`;
    },
    isDir() {
      return (this.item.type === 'dir');
    },
    /* The size of a file in KB */
    size() {
      if (!this.item.size) {
        return '';
      }
      return `${(this.item.size / 1024).toFixed(2)}`;
    },
    selected() {
      return !!this.isSelected();
    },
  },

  methods: {
    getURL() {
      if (!this.item.thumb_path) {
        return '';
      }

      return this.item.thumb_path.split(Joomla.getOptions('system.paths').rootFull).length > 1
        ? `${this.item.thumb_path}?${this.item.modified_date ? new Date(this.item.modified_date).valueOf() : api.mediaVersion}`
        : `${this.item.thumb_path}`;
    },
    width() {
      return this.item.naturalWidth ? this.item.naturalWidth : 300;
    },
    height() {
      return this.item.naturalHeight ? this.item.naturalHeight : 150;
    },
    setSize(event) {
      if (this.item.mime_type === 'image/svg+xml') {
        const image = event.target;
        // Update the item properties
        this.$store.dispatch('updateItemProperties', { item: this.item, width: image.naturalWidth ? image.naturalWidth : 300, height: image.naturalHeight ? image.naturalHeight : 150 });
        // @TODO Remove the fallback size (300x150) when https://bugzilla.mozilla.org/show_bug.cgi?id=1328124 is fixed
        // Also https://github.com/whatwg/html/issues/3510
      }
    },
    /* Handle the on row double click event */
    onDblClick() {
      if (this.isDir) {
        this.navigateTo(this.item.path);
        return;
      }

      // @todo remove the hardcoded extensions here
      const extensionWithPreview = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'mp4', 'mp3', 'pdf'];

      // Show preview
      if (this.item.extension
        && extensionWithPreview.includes(this.item.extension.toLowerCase())) {
        this.$store.commit(SHOW_PREVIEW_MODAL);
        this.$store.dispatch('getFullContents', this.item);
      }
    },

    /**
     * Whether or not the item is currently selected
     * @returns {boolean}
     */
    isSelected() {
      return this.$store.state.selectedItems.some((selected) => selected.path === this.item.path);
    },

    /**
     * Handle the click event
     * @param event
     */
    onClick(event) {
      return onItemClick(event, this);
    },

  },
};

const _hoisted_1$v = { key: 0 };
const _hoisted_2$k = ["src", "width", "height"];
const _hoisted_3$h = ["data-type"];
const _hoisted_4$b = {
  scope: "row",
  class: "name"
};
const _hoisted_5$a = { class: "size" };
const _hoisted_6$9 = { key: 0 };
const _hoisted_7$6 = { class: "dimension" };
const _hoisted_8$4 = { class: "created" };
const _hoisted_9$2 = { class: "modified" };

function render$v(_ctx, _cache, $props, $setup, $data, $options) {
  return (openBlock(), createElementBlock("tr", {
    class: normalizeClass(["media-browser-item", {selected: $options.selected}]),
    onDblclick: _cache[1] || (_cache[1] = withModifiers($event => ($options.onDblClick()), ["stop","prevent"])),
    onClick: _cache[2] || (_cache[2] = (...args) => ($options.onClick && $options.onClick(...args)))
  }, [
    ($props.item.mime_type === 'image/svg+xml' && $options.getURL())
      ? (openBlock(), createElementBlock("td", _hoisted_1$v, [
          createBaseVNode("img", {
            src: $options.getURL(),
            width: $props.item.width,
            height: $props.item.height,
            alt: "",
            style: {"width":"100%","height":"auto"},
            onLoad: _cache[0] || (_cache[0] = (...args) => ($options.setSize && $options.setSize(...args)))
          }, null, 40 /* PROPS, NEED_HYDRATION */, _hoisted_2$k)
        ]))
      : (openBlock(), createElementBlock("td", {
          key: 1,
          class: "type",
          "data-type": $props.item.extension
        }, null, 8 /* PROPS */, _hoisted_3$h)),
    createBaseVNode("th", _hoisted_4$b, toDisplayString($props.item.name), 1 /* TEXT */),
    createBaseVNode("td", _hoisted_5$a, [
      createTextVNode(toDisplayString($options.size), 1 /* TEXT */),
      ($options.size !== '')
        ? (openBlock(), createElementBlock("span", _hoisted_6$9, "KB"))
        : createCommentVNode("v-if", true)
    ]),
    createBaseVNode("td", _hoisted_7$6, toDisplayString($options.dimension), 1 /* TEXT */),
    createBaseVNode("td", _hoisted_8$4, toDisplayString($props.item.create_date_formatted), 1 /* TEXT */),
    createBaseVNode("td", _hoisted_9$2, toDisplayString($props.item.modified_date_formatted), 1 /* TEXT */)
  ], 34 /* CLASS, NEED_HYDRATION */))
}

script$v.render = render$v;
script$v.__file = "administrator/components/com_media/resources/scripts/components/browser/table/row.vue";

var script$u = {
  name: 'MediaBrowserTable',
  components: {
    MediaBrowserItemRow: script$v,
  },
  props: {
    localItems: {
      type: Object,
      default: () => {},
    },
    currentDirectory: {
      type: String,
      default: '',
    },
  },
  methods: {
    changeOrder(name) {
      this.$store.commit(UPDATE_SORT_BY, name);
      this.$store.commit(UPDATE_SORT_DIRECTION, this.$store.state.sortDirection === 'asc' ? 'desc' : 'asc');
    },
  },
};

const _hoisted_1$u = { class: "table media-browser-table" };
const _hoisted_2$j = { class: "visually-hidden" };
const _hoisted_3$g = { class: "media-browser-table-head" };
const _hoisted_4$a = {
  class: "name",
  scope: "col"
};
const _hoisted_5$9 = {
  class: "size",
  scope: "col"
};
const _hoisted_6$8 = {
  class: "dimension",
  scope: "col"
};
const _hoisted_7$5 = {
  class: "created",
  scope: "col"
};
const _hoisted_8$3 = {
  class: "modified",
  scope: "col"
};

function render$u(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_MediaBrowserItemRow = resolveComponent("MediaBrowserItemRow");

  return (openBlock(), createElementBlock("table", _hoisted_1$u, [
    createBaseVNode("caption", _hoisted_2$j, toDisplayString(_ctx.sprintf('COM_MEDIA_BROWSER_TABLE_CAPTION', $props.currentDirectory)), 1 /* TEXT */),
    createBaseVNode("thead", _hoisted_3$g, [
      createBaseVNode("tr", null, [
        _cache[5] || (_cache[5] = createBaseVNode("th", {
          class: "type",
          scope: "col"
        }, null, -1 /* CACHED */)),
        createBaseVNode("th", _hoisted_4$a, [
          createBaseVNode("button", {
            class: "btn btn-link",
            onClick: _cache[0] || (_cache[0] = $event => ($options.changeOrder('name')))
          }, [
            createTextVNode(toDisplayString(_ctx.translate('COM_MEDIA_MEDIA_NAME')) + " ", 1 /* TEXT */),
            createBaseVNode("span", {
              class: normalizeClass(["ms-1", {
                'icon-sort': _ctx.$store.state.sortBy !== 'name',
                'icon-caret-up': _ctx.$store.state.sortBy === 'name' && _ctx.$store.state.sortDirection === 'asc',
                'icon-caret-down': _ctx.$store.state.sortBy === 'name' && _ctx.$store.state.sortDirection === 'desc'
              }]),
              "aria-hidden": "true"
            }, null, 2 /* CLASS */)
          ])
        ]),
        createBaseVNode("th", _hoisted_5$9, [
          createBaseVNode("button", {
            class: "btn btn-link",
            onClick: _cache[1] || (_cache[1] = $event => ($options.changeOrder('size')))
          }, [
            createTextVNode(toDisplayString(_ctx.translate('COM_MEDIA_MEDIA_SIZE')) + " ", 1 /* TEXT */),
            createBaseVNode("span", {
              class: normalizeClass(["ms-1", {
                'icon-sort': _ctx.$store.state.sortBy !== 'size',
                'icon-caret-up': _ctx.$store.state.sortBy === 'size' && _ctx.$store.state.sortDirection === 'asc',
                'icon-caret-down': _ctx.$store.state.sortBy === 'size' && _ctx.$store.state.sortDirection === 'desc'
              }]),
              "aria-hidden": "true"
            }, null, 2 /* CLASS */)
          ])
        ]),
        createBaseVNode("th", _hoisted_6$8, [
          createBaseVNode("button", {
            class: "btn btn-link",
            onClick: _cache[2] || (_cache[2] = $event => ($options.changeOrder('dimension')))
          }, [
            createTextVNode(toDisplayString(_ctx.translate('COM_MEDIA_MEDIA_DIMENSION')) + " ", 1 /* TEXT */),
            createBaseVNode("span", {
              class: normalizeClass(["ms-1", {
                'icon-sort': _ctx.$store.state.sortBy !== 'dimension',
                'icon-caret-up': _ctx.$store.state.sortBy === 'dimension' && _ctx.$store.state.sortDirection === 'asc',
                'icon-caret-down': _ctx.$store.state.sortBy === 'dimension' && _ctx.$store.state.sortDirection === 'desc'
              }]),
              "aria-hidden": "true"
            }, null, 2 /* CLASS */)
          ])
        ]),
        createBaseVNode("th", _hoisted_7$5, [
          createBaseVNode("button", {
            class: "btn btn-link",
            onClick: _cache[3] || (_cache[3] = $event => ($options.changeOrder('date_created')))
          }, [
            createTextVNode(toDisplayString(_ctx.translate('COM_MEDIA_MEDIA_DATE_CREATED')) + " ", 1 /* TEXT */),
            createBaseVNode("span", {
              class: normalizeClass(["ms-1", {
                'icon-sort': _ctx.$store.state.sortBy !== 'date_created',
                'icon-caret-up': _ctx.$store.state.sortBy === 'date_created' && _ctx.$store.state.sortDirection === 'asc',
                'icon-caret-down': _ctx.$store.state.sortBy === 'date_created' && _ctx.$store.state.sortDirection === 'desc'
              }]),
              "aria-hidden": "true"
            }, null, 2 /* CLASS */)
          ])
        ]),
        createBaseVNode("th", _hoisted_8$3, [
          createBaseVNode("button", {
            class: "btn btn-link",
            onClick: _cache[4] || (_cache[4] = $event => ($options.changeOrder('date_modified')))
          }, [
            createTextVNode(toDisplayString(_ctx.translate('COM_MEDIA_MEDIA_DATE_MODIFIED')) + " ", 1 /* TEXT */),
            createBaseVNode("span", {
              class: normalizeClass(["ms-1", {
                'icon-sort': _ctx.$store.state.sortBy !== 'date_modified',
                'icon-caret-up': _ctx.$store.state.sortBy === 'date_modified' && _ctx.$store.state.sortDirection === 'asc',
                'icon-caret-down': _ctx.$store.state.sortBy === 'date_modified' && _ctx.$store.state.sortDirection === 'desc'
              }]),
              "aria-hidden": "true"
            }, null, 2 /* CLASS */)
          ])
        ])
      ])
    ]),
    createBaseVNode("tbody", null, [
      (openBlock(true), createElementBlock(Fragment, null, renderList($props.localItems, (item) => {
        return (openBlock(), createBlock(_component_MediaBrowserItemRow, {
          key: item.path,
          item: item,
          "local-items": $props.localItems
        }, null, 8 /* PROPS */, ["item", "local-items"]))
      }), 128 /* KEYED_FRAGMENT */))
    ])
  ]))
}

script$u.render = render$u;
script$u.__file = "administrator/components/com_media/resources/scripts/components/browser/table/table.vue";

var script$t = {
  name: 'MediaBrowserActionItemEdit',
  props: {
    onFocused: { type: Function, default: () => {} },
    mainAction: { type: Function, default: () => {} },
    closingAction: { type: Function, default: () => {} },
  },
  methods: {
    openRenameModal() {
      this.mainAction();
    },
    hideActions() {
      this.closingAction();
    },
    focused(bool) {
      this.onFocused(bool);
    },
    editItem() {
      this.mainAction();
    },
  },
};

const _hoisted_1$t = { class: "action-text" };

function render$t(_ctx, _cache, $props, $setup, $data, $options) {
  return (openBlock(), createElementBlock("button", {
    type: "button",
    class: "action-edit",
    onKeyup: [
      _cache[0] || (_cache[0] = withKeys($event => ($options.editItem()), ["enter"])),
      _cache[1] || (_cache[1] = withKeys($event => ($options.editItem()), ["space"])),
      _cache[5] || (_cache[5] = withKeys($event => ($options.hideActions()), ["esc"]))
    ],
    onClick: _cache[2] || (_cache[2] = withModifiers($event => ($options.editItem()), ["stop"])),
    onFocus: _cache[3] || (_cache[3] = $event => ($options.focused(true))),
    onBlur: _cache[4] || (_cache[4] = $event => ($options.focused(false)))
  }, [
    _cache[6] || (_cache[6] = createBaseVNode("span", {
      class: "image-browser-action icon-pencil-alt",
      "aria-hidden": "true"
    }, null, -1 /* CACHED */)),
    createBaseVNode("span", _hoisted_1$t, toDisplayString(_ctx.translate('COM_MEDIA_ACTION_EDIT')), 1 /* TEXT */)
  ], 32 /* NEED_HYDRATION */))
}

script$t.render = render$t;
script$t.__file = "administrator/components/com_media/resources/scripts/components/browser/actionItems/edit.vue";

var script$s = {
  name: 'MediaBrowserActionItemDelete',
  props: {
    onFocused: { type: Function, default: () => {} },
    mainAction: { type: Function, default: () => {} },
    closingAction: { type: Function, default: () => {} },
  },
  methods: {
    openConfirmDeleteModal() {
      this.mainAction();
    },
    hideActions() {
      this.hideActions();
    },
    focused(bool) {
      this.onFocused(bool);
    },
  },
};

const _hoisted_1$s = { class: "action-text" };

function render$s(_ctx, _cache, $props, $setup, $data, $options) {
  return (openBlock(), createElementBlock("button", {
    type: "button",
    class: "action-delete",
    onKeyup: [
      _cache[0] || (_cache[0] = withKeys($event => ($options.openConfirmDeleteModal()), ["enter"])),
      _cache[1] || (_cache[1] = withKeys($event => ($options.openConfirmDeleteModal()), ["space"])),
      _cache[4] || (_cache[4] = withKeys($event => ($options.hideActions()), ["esc"]))
    ],
    onFocus: _cache[2] || (_cache[2] = $event => ($options.focused(true))),
    onBlur: _cache[3] || (_cache[3] = $event => ($options.focused(false))),
    onClick: _cache[5] || (_cache[5] = withModifiers($event => ($options.openConfirmDeleteModal()), ["stop"]))
  }, [
    _cache[6] || (_cache[6] = createBaseVNode("span", {
      class: "image-browser-action icon-trash",
      "aria-hidden": "true"
    }, null, -1 /* CACHED */)),
    createBaseVNode("span", _hoisted_1$s, toDisplayString(_ctx.translate('COM_MEDIA_ACTION_DELETE')), 1 /* TEXT */)
  ], 32 /* NEED_HYDRATION */))
}

script$s.render = render$s;
script$s.__file = "administrator/components/com_media/resources/scripts/components/browser/actionItems/delete.vue";

var script$r = {
  name: 'MediaBrowserActionItemDownload',
  props: {
    onFocused: { type: Function, default: () => {} },
    mainAction: { type: Function, default: () => {} },
    closingAction: { type: Function, default: () => {} },
  },
  methods: {
    download() {
      this.mainAction();
    },
    hideActions() {
      this.closingAction();
    },
    focused(bool) {
      this.onFocused(bool);
    },
  },
};

const _hoisted_1$r = { class: "action-text" };

function render$r(_ctx, _cache, $props, $setup, $data, $options) {
  return (openBlock(), createElementBlock("button", {
    type: "button",
    class: "action-download",
    onKeyup: [
      _cache[0] || (_cache[0] = withKeys($event => ($options.download()), ["enter"])),
      _cache[1] || (_cache[1] = withKeys($event => ($options.download()), ["space"])),
      _cache[5] || (_cache[5] = withKeys($event => ($options.hideActions()), ["esc"]))
    ],
    onClick: _cache[2] || (_cache[2] = withModifiers($event => ($options.download()), ["stop"])),
    onFocus: _cache[3] || (_cache[3] = $event => ($options.focused(true))),
    onBlur: _cache[4] || (_cache[4] = $event => ($options.focused(false)))
  }, [
    _cache[6] || (_cache[6] = createBaseVNode("span", {
      class: "image-browser-action icon-download",
      "aria-hidden": "true"
    }, null, -1 /* CACHED */)),
    createBaseVNode("span", _hoisted_1$r, toDisplayString(_ctx.translate('COM_MEDIA_ACTION_DOWNLOAD')), 1 /* TEXT */)
  ], 32 /* NEED_HYDRATION */))
}

script$r.render = render$r;
script$r.__file = "administrator/components/com_media/resources/scripts/components/browser/actionItems/download.vue";

var script$q = {
  name: 'MediaBrowserActionItemPreview',
  props: {
    onFocused: { type: Function, default: () => {} },
    mainAction: { type: Function, default: () => {} },
    closingAction: { type: Function, default: () => {} },
  },
  methods: {
    openPreview() {
      this.mainAction();
    },
    hideActions() {
      this.closingAction();
    },
    focused(bool) {
      this.onFocused(bool);
    },
  },
};

const _hoisted_1$q = { class: "action-text" };

function render$q(_ctx, _cache, $props, $setup, $data, $options) {
  return (openBlock(), createElementBlock("button", {
    type: "button",
    class: "action-preview",
    onClick: _cache[0] || (_cache[0] = withModifiers($event => ($options.openPreview()), ["stop"])),
    onKeyup: [
      _cache[1] || (_cache[1] = withKeys($event => ($options.openPreview()), ["enter"])),
      _cache[2] || (_cache[2] = withKeys($event => ($options.openPreview()), ["space"])),
      _cache[5] || (_cache[5] = withKeys($event => ($options.hideActions()), ["esc"]))
    ],
    onFocus: _cache[3] || (_cache[3] = $event => ($options.focused(true))),
    onBlur: _cache[4] || (_cache[4] = $event => ($options.focused(false)))
  }, [
    _cache[6] || (_cache[6] = createBaseVNode("span", {
      class: "image-browser-action icon-search-plus",
      "aria-hidden": "true"
    }, null, -1 /* CACHED */)),
    createBaseVNode("span", _hoisted_1$q, toDisplayString(_ctx.translate('COM_MEDIA_ACTION_PREVIEW')), 1 /* TEXT */)
  ], 32 /* NEED_HYDRATION */))
}

script$q.render = render$q;
script$q.__file = "administrator/components/com_media/resources/scripts/components/browser/actionItems/preview.vue";

var script$p = {
  name: 'MediaBrowserActionItemRename',
  props: {
    onFocused: { type: Function, default: () => {} },
    mainAction: { type: Function, default: () => {} },
    closingAction: { type: Function, default: () => {} },
  },
  methods: {
    openRenameModal() {
      this.mainAction();
    },
    hideActions() {
      this.closingAction();
    },
    focused(bool) {
      this.onFocused(bool);
    },
  },
};

const _hoisted_1$p = { class: "action-text" };

function render$p(_ctx, _cache, $props, $setup, $data, $options) {
  return (openBlock(), createElementBlock("button", {
    ref: "actionRenameButton",
    type: "button",
    class: "action-rename",
    onClick: _cache[0] || (_cache[0] = withModifiers($event => ($options.openRenameModal()), ["stop"])),
    onKeyup: [
      _cache[1] || (_cache[1] = withKeys($event => ($options.openRenameModal()), ["enter"])),
      _cache[2] || (_cache[2] = withKeys($event => ($options.openRenameModal()), ["space"])),
      _cache[5] || (_cache[5] = withKeys($event => ($options.hideActions()), ["esc"]))
    ],
    onFocus: _cache[3] || (_cache[3] = $event => ($options.focused(true))),
    onBlur: _cache[4] || (_cache[4] = $event => ($options.focused(false)))
  }, [
    _cache[6] || (_cache[6] = createBaseVNode("span", {
      class: "image-browser-action fa fa-i-cursor",
      "aria-hidden": "true"
    }, null, -1 /* CACHED */)),
    createBaseVNode("span", _hoisted_1$p, toDisplayString(_ctx.translate('COM_MEDIA_ACTION_RENAME')), 1 /* TEXT */)
  ], 544 /* NEED_HYDRATION, NEED_PATCH */))
}

script$p.render = render$p;
script$p.__file = "administrator/components/com_media/resources/scripts/components/browser/actionItems/rename.vue";

var script$o = {
  name: 'MediaBrowserActionItemShare',
  props: {
    onFocused: { type: Function, default: () => {} },
    mainAction: { type: Function, default: () => {} },
    closingAction: { type: Function, default: () => {} },
  },
  methods: {
    openShareUrlModal() {
      this.mainAction();
    },
    hideActions() {
      this.closingAction();
    },
    focused(bool) {
      this.onFocused(bool);
    },
  },
};

const _hoisted_1$o = { class: "action-text" };

function render$o(_ctx, _cache, $props, $setup, $data, $options) {
  return (openBlock(), createElementBlock("button", {
    type: "button",
    class: "action-url",
    onClick: _cache[0] || (_cache[0] = withModifiers($event => ($options.openShareUrlModal()), ["stop"])),
    onKeyup: [
      _cache[1] || (_cache[1] = withKeys($event => ($options.openShareUrlModal()), ["enter"])),
      _cache[2] || (_cache[2] = withKeys($event => ($options.openShareUrlModal()), ["space"])),
      _cache[5] || (_cache[5] = withKeys($event => ($options.hideActions()), ["esc"]))
    ],
    onFocus: _cache[3] || (_cache[3] = $event => ($options.focused(true))),
    onBlur: _cache[4] || (_cache[4] = $event => ($options.focused(false)))
  }, [
    _cache[6] || (_cache[6] = createBaseVNode("span", {
      class: "image-browser-action icon-link",
      "aria-hidden": "true"
    }, null, -1 /* CACHED */)),
    createBaseVNode("span", _hoisted_1$o, toDisplayString(_ctx.translate('COM_MEDIA_ACTION_SHARE')), 1 /* TEXT */)
  ], 32 /* NEED_HYDRATION */))
}

script$o.render = render$o;
script$o.__file = "administrator/components/com_media/resources/scripts/components/browser/actionItems/share.vue";

var script$n = {
  name: 'MediaBrowserActionItemToggle',
  props: {
    mainAction: { type: Function, default: () => {} },
  },
  emits: ['on-focused'],
  methods: {
    openActions() {
      this.mainAction();
    },
    focused(bool) {
      this.$emit('on-focused', bool);
    },
  },
};

const _hoisted_1$n = ["aria-label", "title"];

function render$n(_ctx, _cache, $props, $setup, $data, $options) {
  return (openBlock(), createElementBlock("button", {
    type: "button",
    class: "action-toggle",
    "aria-label": _ctx.sprintf('COM_MEDIA_MANAGE_ITEM', (_ctx.$parent.$props.item.name)),
    title: _ctx.sprintf('COM_MEDIA_MANAGE_ITEM', (_ctx.$parent.$props.item.name)),
    onKeyup: [
      _cache[1] || (_cache[1] = withKeys($event => ($options.openActions()), ["enter"])),
      _cache[4] || (_cache[4] = withKeys($event => ($options.openActions()), ["space"]))
    ],
    onFocus: _cache[2] || (_cache[2] = $event => ($options.focused(true))),
    onBlur: _cache[3] || (_cache[3] = $event => ($options.focused(false)))
  }, [
    createBaseVNode("span", {
      class: "image-browser-action icon-ellipsis-h",
      "aria-hidden": "true",
      onClick: _cache[0] || (_cache[0] = withModifiers($event => ($options.openActions()), ["stop"]))
    })
  ], 40 /* PROPS, NEED_HYDRATION */, _hoisted_1$n))
}

script$n.render = render$n;
script$n.__file = "administrator/components/com_media/resources/scripts/components/browser/actionItems/toggle.vue";

var script$m = {
  name: 'MediaBrowserActionItemsContainer',
  components: {
    MediaBrowserActionItemEdit: script$t,
    MediaBrowserActionItemDelete: script$s,
    MediaBrowserActionItemDownload: script$r,
    MediaBrowserActionItemPreview: script$q,
    MediaBrowserActionItemRename: script$p,
    MediaBrowserActionItemShare: script$o,
    MediaBrowserActionItemToggle: script$n,
  },
  props: {
    item: { type: Object, default: () => {} },
    edit: { type: Function, default: () => {} },
    previewable: { type: Boolean, default: false },
    downloadable: { type: Boolean, default: false },
    shareable: { type: Boolean, default: false },
  },
  emits: ['toggle-settings'],
  data() {
    return {
      showActions: false,
    };
  },
  computed: {
    canEdit() {
      return api.canEdit && (typeof this.item.canEdit !== 'undefined' ? this.item.canEdit : true);
    },
    canDelete() {
      return api.canDelete && (typeof this.item.canDelete !== 'undefined' ? this.item.canDelete : true);
    },
    canOpenEditView() {
      // @TODO pass the array of allowed to edit files from PHP
      return api.hasMediaActionPlugins && ['jpg', 'jpeg', 'png'].includes(this.item.extension.toLowerCase());
    },
  },
  watch: {
    '$store.state.showRenameModal': function (show) {
      if (
        !show
        && this.$refs.actionToggle
        && this.$store.state.selectedItems.find(
          (item) => item.name === this.item.name,
        ) !== undefined
      ) {
        this.$refs.actionToggle.$el.focus();
      }
    },
  },
  methods: {
    /* Hide actions dropdown */
    hideActions() {
      this.showActions = false;
      this.$parent.$parent.$data.actionsActive = false;
    },
    /* Preview an item */
    openPreview() {
      this.$store.commit(SHOW_PREVIEW_MODAL);
      this.$store.dispatch('getFullContents', this.item);
    },
    /* Download an item */
    download() {
      this.$store.dispatch('download', this.item);
    },
    /* Opening confirm delete modal */
    openConfirmDeleteModal() {
      this.$store.commit(UNSELECT_ALL_BROWSER_ITEMS);
      this.$store.commit(SELECT_BROWSER_ITEM, this.item);
      this.$store.commit(SHOW_CONFIRM_DELETE_MODAL);
    },
    /* Rename an item */
    openRenameModal() {
      this.hideActions();
      this.$store.commit(SELECT_BROWSER_ITEM, this.item);
      this.$store.commit(SHOW_RENAME_MODAL);
    },
    /* Open modal for share url */
    openShareUrlModal() {
      this.$store.commit(SELECT_BROWSER_ITEM, this.item);
      this.$store.commit(SHOW_SHARE_MODAL);
    },
    /* Open actions dropdown */
    openActions() {
      this.showActions = true;
      this.$parent.$parent.$data.actionsActive = true;
      const buttons = [...this.$el.parentElement.querySelectorAll('.media-browser-actions-list button')];
      if (buttons.length) {
        buttons.forEach((button, i) => {
          if (i === (0)) {
            button.tabIndex = 0;
          } else {
            button.tabIndex = -1;
          }
        });
        buttons[0].focus();
      }
    },
    /* Open actions dropdown and focus on last element */
    openLastActions() {
      this.showActions = true;
      this.$parent.$parent.$data.actionsActive = true;
      const buttons = [...this.$el.parentElement.querySelectorAll('.media-browser-actions-list button')];
      if (buttons.length) {
        buttons.forEach((button, i) => {
          if (i === (buttons.length)) {
            button.tabIndex = 0;
          } else {
            button.tabIndex = -1;
          }
        });
        this.$nextTick(() => buttons[buttons.length - 1].focus());
      }
    },
    /* Focus on the next item or go to the beginning again */
    focusNext(event) {
      const active = event.target;
      const buttons = [...active.parentElement.querySelectorAll('button')];
      const lastchild = buttons[buttons.length - 1];
      active.tabIndex = -1;
      if (active === lastchild) {
        buttons[0].focus();
        buttons[0].tabIndex = 0;
      } else {
        active.nextElementSibling.focus();
        active.nextElementSibling.tabIndex = 0;
      }
    },
    /* Focus on the previous item or go to the end again */
    focusPrev(event) {
      const active = event.target;
      const buttons = [...active.parentElement.querySelectorAll('button')];
      const firstchild = buttons[0];
      active.tabIndex = -1;
      if (active === firstchild) {
        buttons[buttons.length - 1].focus();
        buttons[buttons.length - 1].tabIndex = 0;
      } else {
        active.previousElementSibling.focus();
        active.previousElementSibling.tabIndex = 0;
      }
    },
    /* Focus on the first item */
    focusFirst(event) {
      const active = event.target;
      const buttons = [...active.parentElement.querySelectorAll('button')];
      buttons[0].focus();
      buttons.forEach((button, i) => {
        if (i === 0) {
          button.tabIndex = 0;
        } else {
          button.tabIndex = -1;
        }
      });
    },
    /* Focus on the last item */
    focusLast(event) {
      const active = event.target;
      const buttons = [...active.parentElement.querySelectorAll('button')];
      buttons[buttons.length - 1].focus();
      buttons.forEach((button, i) => {
        if (i === (buttons.length)) {
          button.tabIndex = 0;
        } else {
          button.tabIndex = -1;
        }
      });
    },
    editItem() {
      this.edit();
    },
    focused(bool) {
      this.$emit('toggle-settings', bool);
    },
  },
};

const _hoisted_1$m = ["aria-label", "title"];
const _hoisted_2$i = ["aria-label"];
const _hoisted_3$f = {
  "aria-hidden": "true",
  class: "media-browser-actions-item-name"
};

function render$m(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_MediaBrowserActionItemToggle = resolveComponent("MediaBrowserActionItemToggle");
  const _component_MediaBrowserActionItemPreview = resolveComponent("MediaBrowserActionItemPreview");
  const _component_MediaBrowserActionItemDownload = resolveComponent("MediaBrowserActionItemDownload");
  const _component_MediaBrowserActionItemRename = resolveComponent("MediaBrowserActionItemRename");
  const _component_MediaBrowserActionItemEdit = resolveComponent("MediaBrowserActionItemEdit");
  const _component_MediaBrowserActionItemShare = resolveComponent("MediaBrowserActionItemShare");
  const _component_MediaBrowserActionItemDelete = resolveComponent("MediaBrowserActionItemDelete");

  return (openBlock(), createElementBlock(Fragment, null, [
    createBaseVNode("span", {
      class: "media-browser-select",
      "aria-label": _ctx.translate('COM_MEDIA_TOGGLE_SELECT_ITEM'),
      title: _ctx.translate('COM_MEDIA_TOGGLE_SELECT_ITEM'),
      tabindex: "0",
      onFocusin: _cache[0] || (_cache[0] = $event => ($options.focused(true))),
      onFocusout: _cache[1] || (_cache[1] = $event => ($options.focused(false)))
    }, null, 40 /* PROPS, NEED_HYDRATION */, _hoisted_1$m),
    createBaseVNode("div", {
      class: normalizeClass(["media-browser-actions", { active: $data.showActions }])
    }, [
      createVNode(_component_MediaBrowserActionItemToggle, {
        ref: "actionToggle",
        "main-action": $options.openActions,
        onOnFocused: $options.focused,
        onKeyup: [
          _cache[2] || (_cache[2] = withKeys($event => ($options.openLastActions()), ["up"])),
          _cache[3] || (_cache[3] = withKeys($event => ($options.openActions()), ["down"])),
          _cache[4] || (_cache[4] = withKeys($event => ($options.openLastActions()), ["end"])),
          _cache[5] || (_cache[5] = withKeys($event => ($options.openActions()), ["home"]))
        ],
        onKeydown: [
          _cache[6] || (_cache[6] = withKeys(withModifiers(() => {}, ["prevent"]), ["up"])),
          _cache[7] || (_cache[7] = withKeys(withModifiers(() => {}, ["prevent"]), ["down"])),
          _cache[8] || (_cache[8] = withKeys(withModifiers(() => {}, ["prevent"]), ["home"])),
          _cache[9] || (_cache[9] = withKeys(withModifiers(() => {}, ["prevent"]), ["end"]))
        ]
      }, null, 8 /* PROPS */, ["main-action", "onOnFocused"]),
      ($data.showActions)
        ? (openBlock(), createElementBlock("div", {
            key: 0,
            ref: "actionList",
            class: "media-browser-actions-list",
            role: "toolbar",
            "aria-orientation": "vertical",
            "aria-label": _ctx.sprintf('COM_MEDIA_ACTIONS_TOOLBAR_LABEL',(_ctx.$parent.$props.item.name))
          }, [
            createBaseVNode("span", _hoisted_3$f, [
              createBaseVNode("strong", null, toDisplayString(_ctx.$parent.$props.item.name), 1 /* TEXT */)
            ]),
            ($props.previewable)
              ? (openBlock(), createBlock(_component_MediaBrowserActionItemPreview, {
                  key: 0,
                  ref: "actionPreview",
                  "on-focused": $options.focused,
                  "main-action": $options.openPreview,
                  "closing-action": $options.hideActions,
                  onKeydown: [
                    _cache[10] || (_cache[10] = withKeys(withModifiers(() => {}, ["prevent"]), ["up"])),
                    _cache[11] || (_cache[11] = withKeys(withModifiers(() => {}, ["prevent"]), ["down"])),
                    _cache[12] || (_cache[12] = withKeys(withModifiers(() => {}, ["prevent"]), ["home"])),
                    _cache[13] || (_cache[13] = withKeys(withModifiers(() => {}, ["prevent"]), ["end"])),
                    withKeys($options.hideActions, ["tab"])
                  ],
                  onKeyup: [
                    withKeys($options.focusPrev, ["up"]),
                    withKeys($options.focusNext, ["down"]),
                    withKeys($options.focusLast, ["end"]),
                    withKeys($options.focusFirst, ["home"]),
                    withKeys($options.hideActions, ["esc"])
                  ]
                }, null, 8 /* PROPS */, ["on-focused", "main-action", "closing-action", "onKeyup", "onKeydown"]))
              : createCommentVNode("v-if", true),
            ($props.downloadable)
              ? (openBlock(), createBlock(_component_MediaBrowserActionItemDownload, {
                  key: 1,
                  ref: "actionDownload",
                  "on-focused": $options.focused,
                  "main-action": $options.download,
                  "closing-action": $options.hideActions,
                  onKeydown: [
                    _cache[14] || (_cache[14] = withKeys(withModifiers(() => {}, ["prevent"]), ["up"])),
                    _cache[15] || (_cache[15] = withKeys(withModifiers(() => {}, ["prevent"]), ["down"])),
                    withKeys($options.hideActions, ["tab"]),
                    _cache[16] || (_cache[16] = withKeys(withModifiers(() => {}, ["prevent"]), ["home"])),
                    _cache[17] || (_cache[17] = withKeys(withModifiers(() => {}, ["prevent"]), ["end"]))
                  ],
                  onKeyup: [
                    withKeys($options.focusPrev, ["up"]),
                    withKeys($options.focusNext, ["down"]),
                    withKeys($options.hideActions, ["esc"]),
                    withKeys($options.focusLast, ["end"]),
                    withKeys($options.focusFirst, ["home"])
                  ]
                }, null, 8 /* PROPS */, ["on-focused", "main-action", "closing-action", "onKeyup", "onKeydown"]))
              : createCommentVNode("v-if", true),
            ($options.canEdit)
              ? (openBlock(), createBlock(_component_MediaBrowserActionItemRename, {
                  key: 2,
                  ref: "actionRename",
                  "on-focused": $options.focused,
                  "main-action": $options.openRenameModal,
                  "closing-action": $options.hideActions,
                  onKeydown: [
                    _cache[18] || (_cache[18] = withKeys(withModifiers(() => {}, ["prevent"]), ["up"])),
                    _cache[19] || (_cache[19] = withKeys(withModifiers(() => {}, ["prevent"]), ["down"])),
                    withKeys($options.hideActions, ["tab"]),
                    _cache[20] || (_cache[20] = withKeys(withModifiers(() => {}, ["prevent"]), ["home"])),
                    _cache[21] || (_cache[21] = withKeys(withModifiers(() => {}, ["prevent"]), ["end"]))
                  ],
                  onKeyup: [
                    withKeys($options.focusPrev, ["up"]),
                    withKeys($options.focusNext, ["down"]),
                    withKeys($options.hideActions, ["esc"]),
                    withKeys($options.focusLast, ["end"]),
                    withKeys($options.focusFirst, ["home"])
                  ]
                }, null, 8 /* PROPS */, ["on-focused", "main-action", "closing-action", "onKeyup", "onKeydown"]))
              : createCommentVNode("v-if", true),
            ($options.canEdit && $options.canOpenEditView)
              ? (openBlock(), createBlock(_component_MediaBrowserActionItemEdit, {
                  key: 3,
                  ref: "actionEdit",
                  "on-focused": $options.focused,
                  "main-action": $options.editItem,
                  "closing-action": $options.hideActions,
                  onKeydown: [
                    _cache[22] || (_cache[22] = withKeys(withModifiers(() => {}, ["prevent"]), ["up"])),
                    _cache[23] || (_cache[23] = withKeys(withModifiers(() => {}, ["prevent"]), ["down"])),
                    withKeys($options.hideActions, ["tab"]),
                    _cache[24] || (_cache[24] = withKeys(withModifiers(() => {}, ["prevent"]), ["home"])),
                    _cache[25] || (_cache[25] = withKeys(withModifiers(() => {}, ["prevent"]), ["end"]))
                  ],
                  onKeyup: [
                    withKeys($options.focusPrev, ["up"]),
                    withKeys($options.focusNext, ["down"]),
                    withKeys($options.hideActions, ["esc"]),
                    withKeys($options.focusLast, ["end"]),
                    withKeys($options.focusFirst, ["home"])
                  ]
                }, null, 8 /* PROPS */, ["on-focused", "main-action", "closing-action", "onKeyup", "onKeydown"]))
              : createCommentVNode("v-if", true),
            ($props.shareable)
              ? (openBlock(), createBlock(_component_MediaBrowserActionItemShare, {
                  key: 4,
                  ref: "actionShare",
                  "on-focused": $options.focused,
                  "main-action": $options.openShareUrlModal,
                  "closing-action": $options.hideActions,
                  onKeydown: [
                    _cache[26] || (_cache[26] = withKeys(withModifiers(() => {}, ["prevent"]), ["up"])),
                    _cache[27] || (_cache[27] = withKeys(withModifiers(() => {}, ["prevent"]), ["down"])),
                    withKeys($options.hideActions, ["tab"]),
                    _cache[28] || (_cache[28] = withKeys(withModifiers(() => {}, ["prevent"]), ["home"])),
                    _cache[29] || (_cache[29] = withKeys(withModifiers(() => {}, ["prevent"]), ["end"]))
                  ],
                  onKeyup: [
                    withKeys($options.focusPrev, ["up"]),
                    withKeys($options.focusNext, ["down"]),
                    withKeys($options.hideActions, ["esc"]),
                    withKeys($options.focusLast, ["end"]),
                    withKeys($options.focusFirst, ["home"])
                  ]
                }, null, 8 /* PROPS */, ["on-focused", "main-action", "closing-action", "onKeyup", "onKeydown"]))
              : createCommentVNode("v-if", true),
            ($options.canDelete)
              ? (openBlock(), createBlock(_component_MediaBrowserActionItemDelete, {
                  key: 5,
                  ref: "actionDelete",
                  "on-focused": $options.focused,
                  "main-action": $options.openConfirmDeleteModal,
                  "hide-actions": $options.hideActions,
                  onKeydown: [
                    _cache[30] || (_cache[30] = withKeys(withModifiers(() => {}, ["prevent"]), ["up"])),
                    _cache[31] || (_cache[31] = withKeys(withModifiers(() => {}, ["prevent"]), ["down"])),
                    withKeys($options.hideActions, ["tab"]),
                    _cache[32] || (_cache[32] = withKeys(withModifiers(() => {}, ["prevent"]), ["home"])),
                    _cache[33] || (_cache[33] = withKeys(withModifiers(() => {}, ["prevent"]), ["end"]))
                  ],
                  onKeyup: [
                    withKeys($options.focusPrev, ["up"]),
                    withKeys($options.focusNext, ["down"]),
                    withKeys($options.hideActions, ["esc"]),
                    withKeys($options.focusLast, ["end"]),
                    withKeys($options.focusFirst, ["home"])
                  ]
                }, null, 8 /* PROPS */, ["on-focused", "main-action", "hide-actions", "onKeyup", "onKeydown"]))
              : createCommentVNode("v-if", true)
          ], 8 /* PROPS */, _hoisted_2$i))
        : createCommentVNode("v-if", true)
    ], 2 /* CLASS */)
  ], 64 /* STABLE_FRAGMENT */))
}

script$m.render = render$m;
script$m.__file = "administrator/components/com_media/resources/scripts/components/browser/actionItems/actionItemsContainer.vue";

var script$l = {
  name: 'MediaBrowserItemDirectory',
  components: {
    MediaBrowserActionItemsContainer: script$m,
  },
  mixins: [navigable],
  props: {
    item: {
      type: Object,
      default: () => {},
    },
  },
  emits: ['toggle-settings'],
  data() {
    return {
      showActions: false,
    };
  },
  methods: {
    /* Handle the on preview double click event */
    onPreviewDblClick() {
      this.navigateTo(this.item.path);

      window.parent.document.dispatchEvent(
        new CustomEvent('onMediaFileSelected', {
          bubbles: true,
          cancelable: false,
          detail: {
            type: this.item.type,
            name: this.item.name,
            path: this.item.path,
          },
        }),
      );
    },
    /* Hide actions dropdown */
    hideActions() {
      if (this.$refs.container) {
        this.$refs.container.hideActions();
      }
    },
    toggleSettings(bool) {
      this.$emit('toggle-settings', bool);
    },
  },
};

const _hoisted_1$l = { class: "media-browser-item-info" };

function render$l(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_MediaBrowserActionItemsContainer = resolveComponent("MediaBrowserActionItemsContainer");

  return (openBlock(), createElementBlock("div", {
    class: "media-browser-item-directory",
    onMouseleave: _cache[2] || (_cache[2] = $event => ($options.hideActions()))
  }, [
    createBaseVNode("div", {
      class: "media-browser-item-preview",
      tabindex: "0",
      onDblclick: _cache[0] || (_cache[0] = withModifiers($event => ($options.onPreviewDblClick()), ["stop","prevent"])),
      onKeyup: _cache[1] || (_cache[1] = withKeys($event => ($options.onPreviewDblClick()), ["enter"]))
    }, [...(_cache[3] || (_cache[3] = [
      createBaseVNode("div", { class: "file-background" }, [
        createBaseVNode("div", { class: "folder-icon" }, [
          createBaseVNode("span", { class: "icon-folder" })
        ])
      ], -1 /* CACHED */)
    ]))], 32 /* NEED_HYDRATION */),
    createBaseVNode("div", _hoisted_1$l, toDisplayString($props.item.name), 1 /* TEXT */),
    createVNode(_component_MediaBrowserActionItemsContainer, {
      ref: "container",
      item: $props.item,
      onToggleSettings: $options.toggleSettings
    }, null, 8 /* PROPS */, ["item", "onToggleSettings"])
  ], 32 /* NEED_HYDRATION */))
}

script$l.render = render$l;
script$l.__file = "administrator/components/com_media/resources/scripts/components/browser/items/directory.vue";

var script$k = {
  name: 'MediaBrowserItemFile',
  components: {
    MediaBrowserActionItemsContainer: script$m,
  },
  props: {
    item: {
      type: Object,
      default: () => {},
    },
    focused: {
      type: Boolean,
      default: false,
    },
  },
  emits: ['toggle-settings'],
  data() {
    return {
      showActions: false,
    };
  },
  methods: {
    /* Hide actions dropdown */
    hideActions() {
      if (this.$refs.container) {
        this.$refs.container.hideActions();
      }
    },
    /* Preview an item */
    openPreview() {
      this.$refs.container.openPreview();
    },
    toggleSettings(bool) {
      this.$emit('toggle-settings', bool);
    },
  },
};

const _hoisted_1$k = { class: "media-browser-item-info" };
const _hoisted_2$h = ["aria-label", "title"];

function render$k(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_MediaBrowserActionItemsContainer = resolveComponent("MediaBrowserActionItemsContainer");

  return (openBlock(), createElementBlock("div", {
    class: "media-browser-item-file",
    onMouseleave: _cache[0] || (_cache[0] = $event => ($options.hideActions()))
  }, [
    _cache[1] || (_cache[1] = createBaseVNode("div", { class: "media-browser-item-preview" }, [
      createBaseVNode("div", { class: "file-background" }, [
        createBaseVNode("div", { class: "file-icon" }, [
          createBaseVNode("span", { class: "icon-file-alt" })
        ])
      ])
    ], -1 /* CACHED */)),
    createBaseVNode("div", _hoisted_1$k, toDisplayString($props.item.name) + " " + toDisplayString($props.item.filetype), 1 /* TEXT */),
    createBaseVNode("span", {
      class: "media-browser-select",
      "aria-label": _ctx.translate('COM_MEDIA_TOGGLE_SELECT_ITEM'),
      title: _ctx.translate('COM_MEDIA_TOGGLE_SELECT_ITEM')
    }, null, 8 /* PROPS */, _hoisted_2$h),
    createVNode(_component_MediaBrowserActionItemsContainer, {
      ref: "container",
      item: $props.item,
      previewable: true,
      downloadable: true,
      shareable: true,
      onToggleSettings: $options.toggleSettings
    }, null, 8 /* PROPS */, ["item", "onToggleSettings"])
  ], 32 /* NEED_HYDRATION */))
}

script$k.render = render$k;
script$k.__file = "administrator/components/com_media/resources/scripts/components/browser/items/file.vue";

var script$j = {
  name: 'MediaBrowserItemImage',
  components: {
    MediaBrowserActionItemsContainer: script$m,
  },
  props: {
    item: { type: Object, required: true },
    focused: { type: Boolean, required: true, default: false },
  },
  emits: ['toggle-settings'],
  data() {
    return {
      showActions: { type: Boolean, default: false },
    };
  },
  computed: {
    getURL() {
      if (!this.item.thumb_path) {
        return '';
      }

      return this.item.thumb_path.split(Joomla.getOptions('system.paths').rootFull).length > 1
        ? `${this.item.thumb_path}?${this.item.modified_date ? new Date(this.item.modified_date).valueOf() : api.mediaVersion}`
        : `${this.item.thumb_path}`;
    },
    width() {
      return this.item.width > 0 ? this.item.width : null;
    },
    height() {
      return this.item.height > 0 ? this.item.height : null;
    },
    loading() {
      return this.item.width > 0 ? 'lazy' : null;
    },
    altTag() {
      return this.item.name;
    },
  },
  methods: {
    /* Check if the item is an image to edit */
    canEdit() {
      return ['jpg', 'jpeg', 'png'].includes(this.item.extension.toLowerCase());
    },
    /* Hide actions dropdown */
    hideActions() {
      if (this.$refs.container) {
        this.$refs.container.hideActions();
      }
    },
    /* Preview an item */
    openPreview() {
      this.$refs.container.openPreview();
    },
    /* Edit an item */
    editItem() {
      // @todo should we use relative urls here?
      const fileBaseUrl = `${Joomla.getOptions('com_media').editViewUrl}&path=`;

      window.location.href = fileBaseUrl + this.item.path;
    },
    toggleSettings(bool) {
      this.$emit('toggle-settings', bool);
    },
    setSize(event) {
      if (this.item.mime_type === 'image/svg+xml') {
        const image = event.target;
        // Update the item properties
        this.$store.dispatch('updateItemProperties', { item: this.item, width: image.naturalWidth ? image.naturalWidth : 300, height: image.naturalHeight ? image.naturalHeight : 150 });
        // @TODO Remove the fallback size (300x150) when https://bugzilla.mozilla.org/show_bug.cgi?id=1328124 is fixed
        // Also https://github.com/whatwg/html/issues/3510
      }
    },
  },
};

const _hoisted_1$j = ["title"];
const _hoisted_2$g = { class: "image-background" };
const _hoisted_3$e = ["src", "alt", "loading", "width", "height"];
const _hoisted_4$9 = {
  key: 1,
  class: "icon-eye-slash image-placeholder",
  "aria-hidden": "true"
};
const _hoisted_5$8 = ["title"];
const _hoisted_6$7 = ["aria-label", "title"];

function render$j(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_MediaBrowserActionItemsContainer = resolveComponent("MediaBrowserActionItemsContainer");

  return (openBlock(), createElementBlock("div", {
    class: "media-browser-image",
    tabindex: "0",
    onDblclick: _cache[1] || (_cache[1] = $event => ($options.openPreview())),
    onMouseleave: _cache[2] || (_cache[2] = $event => ($options.hideActions())),
    onKeyup: _cache[3] || (_cache[3] = withKeys($event => ($options.openPreview()), ["enter"]))
  }, [
    createBaseVNode("div", {
      class: "media-browser-item-preview",
      title: $props.item.name
    }, [
      createBaseVNode("div", _hoisted_2$g, [
        ($options.getURL)
          ? (openBlock(), createElementBlock("img", {
              key: 0,
              class: "image-cropped",
              src: $options.getURL,
              alt: $options.altTag,
              loading: $options.loading,
              width: $options.width,
              height: $options.height,
              onLoad: _cache[0] || (_cache[0] = (...args) => ($options.setSize && $options.setSize(...args)))
            }, null, 40 /* PROPS, NEED_HYDRATION */, _hoisted_3$e))
          : createCommentVNode("v-if", true),
        (!$options.getURL)
          ? (openBlock(), createElementBlock("span", _hoisted_4$9))
          : createCommentVNode("v-if", true)
      ])
    ], 8 /* PROPS */, _hoisted_1$j),
    createBaseVNode("div", {
      class: "media-browser-item-info",
      title: $props.item.name
    }, toDisplayString($props.item.name) + " " + toDisplayString($props.item.filetype), 9 /* TEXT, PROPS */, _hoisted_5$8),
    createBaseVNode("span", {
      class: "media-browser-select",
      "aria-label": _ctx.translate('COM_MEDIA_TOGGLE_SELECT_ITEM'),
      title: _ctx.translate('COM_MEDIA_TOGGLE_SELECT_ITEM')
    }, null, 8 /* PROPS */, _hoisted_6$7),
    createVNode(_component_MediaBrowserActionItemsContainer, {
      ref: "container",
      item: $props.item,
      edit: $options.editItem,
      previewable: true,
      downloadable: true,
      shareable: true,
      onToggleSettings: $options.toggleSettings
    }, null, 8 /* PROPS */, ["item", "edit", "onToggleSettings"])
  ], 32 /* NEED_HYDRATION */))
}

script$j.render = render$j;
script$j.__file = "administrator/components/com_media/resources/scripts/components/browser/items/image.vue";

var script$i = {
  name: 'MediaBrowserItemVideo',
  components: {
    MediaBrowserActionItemsContainer: script$m,
  },
  props: {
    item: {
      type: Object,
      default: () => {},
    },
    focused: {
      type: Boolean,
      default: false,
    },
  },
  emits: ['toggle-settings'],
  data() {
    return {
      showActions: false,
    };
  },
  methods: {
    /* Hide actions dropdown */
    hideActions() {
      if (this.$refs.container) {
        this.$refs.container.hideActions();
      }
    },
    /* Preview an item */
    openPreview() {
      this.$refs.container.openPreview();
    },
    toggleSettings(bool) {
      this.$emit('toggle-settings', bool);
    },
  },
};

const _hoisted_1$i = { class: "media-browser-item-info" };

function render$i(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_MediaBrowserActionItemsContainer = resolveComponent("MediaBrowserActionItemsContainer");

  return (openBlock(), createElementBlock("div", {
    class: "media-browser-image",
    onDblclick: _cache[0] || (_cache[0] = $event => ($options.openPreview())),
    onMouseleave: _cache[1] || (_cache[1] = $event => ($options.hideActions()))
  }, [
    _cache[2] || (_cache[2] = createBaseVNode("div", { class: "media-browser-item-preview" }, [
      createBaseVNode("div", { class: "file-background" }, [
        createBaseVNode("div", { class: "file-icon" }, [
          createBaseVNode("span", { class: "fas fa-file-video" })
        ])
      ])
    ], -1 /* CACHED */)),
    createBaseVNode("div", _hoisted_1$i, toDisplayString($props.item.name) + " " + toDisplayString($props.item.filetype), 1 /* TEXT */),
    createVNode(_component_MediaBrowserActionItemsContainer, {
      ref: "container",
      item: $props.item,
      previewable: true,
      downloadable: true,
      shareable: true,
      onToggleSettings: $options.toggleSettings
    }, null, 8 /* PROPS */, ["item", "onToggleSettings"])
  ], 32 /* NEED_HYDRATION */))
}

script$i.render = render$i;
script$i.__file = "administrator/components/com_media/resources/scripts/components/browser/items/video.vue";

var script$h = {
  name: 'MediaBrowserItemAudio',
  components: {
    MediaBrowserActionItemsContainer: script$m,
  },
  props: {
    item: {
      type: Object,
      default: () => {},
    },
    focused: {
      type: Boolean,
      default: false,
    },
  },
  emits: ['toggle-settings'],
  data() {
    return {
      showActions: false,
    };
  },
  methods: {
    /* Hide actions dropdown */
    hideActions() {
      if (this.$refs.container) {
        this.$refs.container.hideActions();
      }
    },
    /* Preview an item */
    openPreview() {
      this.$refs.container.openPreview();
    },
    toggleSettings(bool) {
      this.$emit('toggle-settings', bool);
    },
  },
};

const _hoisted_1$h = { class: "media-browser-item-info" };

function render$h(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_MediaBrowserActionItemsContainer = resolveComponent("MediaBrowserActionItemsContainer");

  return (openBlock(), createElementBlock("div", {
    class: "media-browser-audio",
    tabindex: "0",
    onDblclick: _cache[0] || (_cache[0] = $event => ($options.openPreview())),
    onMouseleave: _cache[1] || (_cache[1] = $event => ($options.hideActions())),
    onKeyup: _cache[2] || (_cache[2] = withKeys($event => ($options.openPreview()), ["enter"]))
  }, [
    _cache[3] || (_cache[3] = createBaseVNode("div", { class: "media-browser-item-preview" }, [
      createBaseVNode("div", { class: "file-background" }, [
        createBaseVNode("div", { class: "file-icon" }, [
          createBaseVNode("span", { class: "fas fa-file-audio" })
        ])
      ])
    ], -1 /* CACHED */)),
    createBaseVNode("div", _hoisted_1$h, toDisplayString($props.item.name) + " " + toDisplayString($props.item.filetype), 1 /* TEXT */),
    createVNode(_component_MediaBrowserActionItemsContainer, {
      ref: "container",
      item: $props.item,
      previewable: true,
      downloadable: true,
      shareable: true,
      onToggleSettings: $options.toggleSettings
    }, null, 8 /* PROPS */, ["item", "onToggleSettings"])
  ], 32 /* NEED_HYDRATION */))
}

script$h.render = render$h;
script$h.__file = "administrator/components/com_media/resources/scripts/components/browser/items/audio.vue";

var script$g = {
  name: 'MediaBrowserItemDocument',
  components: {
    MediaBrowserActionItemsContainer: script$m,
  },
  props: {
    item: {
      type: Object,
      default: () => {},
    },
    focused: {
      type: Boolean,
      default: false,
    },
  },
  emits: ['toggle-settings'],
  data() {
    return {
      showActions: false,
    };
  },
  methods: {
    /* Hide actions dropdown */
    hideActions() {
      if (this.$refs.container) {
        this.$refs.container.hideActions();
      }
    },
    /* Preview an item */
    openPreview() {
      this.$refs.container.openPreview();
    },
    toggleSettings(bool) {
      this.$emit('toggle-settings', bool);
    },
  },
};

const _hoisted_1$g = { class: "media-browser-item-info" };
const _hoisted_2$f = ["aria-label", "title"];

function render$g(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_MediaBrowserActionItemsContainer = resolveComponent("MediaBrowserActionItemsContainer");

  return (openBlock(), createElementBlock("div", {
    class: "media-browser-doc",
    onDblclick: _cache[0] || (_cache[0] = $event => ($options.openPreview())),
    onMouseleave: _cache[1] || (_cache[1] = $event => ($options.hideActions()))
  }, [
    _cache[2] || (_cache[2] = createBaseVNode("div", { class: "media-browser-item-preview" }, [
      createBaseVNode("div", { class: "file-background" }, [
        createBaseVNode("div", { class: "file-icon" }, [
          createBaseVNode("span", { class: "fas fa-file" })
        ])
      ])
    ], -1 /* CACHED */)),
    createBaseVNode("div", _hoisted_1$g, toDisplayString($props.item.name) + " " + toDisplayString($props.item.filetype), 1 /* TEXT */),
    createBaseVNode("span", {
      class: "media-browser-select",
      "aria-label": _ctx.translate('COM_MEDIA_TOGGLE_SELECT_ITEM'),
      title: _ctx.translate('COM_MEDIA_TOGGLE_SELECT_ITEM')
    }, null, 8 /* PROPS */, _hoisted_2$f),
    createVNode(_component_MediaBrowserActionItemsContainer, {
      ref: "container",
      item: $props.item,
      previewable: true,
      downloadable: true,
      shareable: true,
      onToggleSettings: $options.toggleSettings
    }, null, 8 /* PROPS */, ["item", "onToggleSettings"])
  ], 32 /* NEED_HYDRATION */))
}

script$g.render = render$g;
script$g.__file = "administrator/components/com_media/resources/scripts/components/browser/items/document.vue";

var MediaBrowserItem = {
  props: {
    item: {
      type: Object,
      default: () => {}
    },
    localItems: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      hoverActive: false,
      actionsActive: false
    };
  },
  methods: {
    /**
     * Return the correct item type component
     */
    itemType() {
      // Render directory items
      if (this.item.type === 'dir') return script$l;

      // Render image items
      if (this.item.extension && api.imagesExtensions.includes(this.item.extension.toLowerCase())) {
        return script$j;
      }

      // Render video items
      if (this.item.extension && api.videoExtensions.includes(this.item.extension.toLowerCase())) {
        return script$i;
      }

      // Render audio items
      if (this.item.extension && api.audioExtensions.includes(this.item.extension.toLowerCase())) {
        return script$h;
      }

      // Render document items
      if (this.item.extension && api.documentExtensions.includes(this.item.extension.toLowerCase())) {
        return script$g;
      }

      // Default to file type
      return script$k;
    },
    /**
     * Get the styles for the media browser item
     * @returns {{}}
     */
    styles() {
      return {
        width: "calc(" + this.$store.state.gridSize + "% - 20px)"
      };
    },
    /**
     * Whether or not the item is currently selected
     * @returns {boolean}
     */
    isSelected() {
      return this.$store.state.selectedItems.some(selected => selected.path === this.item.path);
    },
    /**
     * Whether or not the item is currently active (on hover or via tab)
     * @returns {boolean}
     */
    isHoverActive() {
      return this.hoverActive;
    },
    /**
     * Whether or not the item is currently active (on hover or via tab)
     * @returns {boolean}
     */
    hasActions() {
      return this.actionsActive;
    },
    /**
     * Turns on the hover class
     */
    mouseover() {
      this.hoverActive = true;
    },
    /**
     * Turns off the hover class
     */
    mouseleave() {
      this.hoverActive = false;
    },
    /**
     * Handle the click event
     * @param event
     */
    handleClick(event) {
      return onItemClick(event, this);
    },
    /**
     * Handle the when an element is focused in the child to display the layover for a11y
     * @param active
     */
    toggleSettings(active) {
      this["mouse" + (active ? 'over' : 'leave')]();
    }
  },
  render() {
    return h('div', {
      class: {
        'media-browser-item': true,
        selected: this.isSelected(),
        active: this.isHoverActive(),
        actions: this.hasActions()
      },
      onClick: this.handleClick,
      onMouseover: this.mouseover,
      onMouseleave: this.mouseleave
    }, [h(this.itemType(), {
      item: this.item,
      onToggleSettings: this.toggleSettings,
      focused: false
    })]);
  }
};

var script$f = {
  name: 'MediaInfobar',
  computed: {
    /* Get the item to show in the infobar */
    item() {
      // Check if there are selected items
      const { selectedItems } = this.$store.state;

      // If there is only one selected item, show that one.
      if (selectedItems.length === 1) {
        return selectedItems[0];
      }

      // If there are more selected items, use the last one
      if (selectedItems.length > 1) {
        return selectedItems.slice(-1)[0];
      }

      // Use the currently selected directory as a fallback
      return this.$store.getters.getSelectedDirectory;
    },
    /* Show/Hide the InfoBar */
    showInfoBar() {
      return this.$store.state.showInfoBar;
    },
  },
  methods: {
    hideInfoBar() {
      this.$store.commit(HIDE_INFOBAR);
    },
  },
};

const _hoisted_1$f = {
  key: 0,
  class: "media-infobar"
};
const _hoisted_2$e = { class: "media-infobar-inner" };
const _hoisted_3$d = {
  key: 0,
  class: "text-center"
};
const _hoisted_4$8 = { key: 1 };
const _hoisted_5$7 = { key: 0 };
const _hoisted_6$6 = { key: 1 };
const _hoisted_7$4 = { key: 2 };
const _hoisted_8$2 = { key: 3 };
const _hoisted_9$1 = { key: 4 };
const _hoisted_10$1 = { key: 5 };
const _hoisted_11$1 = { key: 6 };

function render$f(_ctx, _cache, $props, $setup, $data, $options) {
  return (openBlock(), createBlock(Transition, { name: "infobar" }, {
    default: withCtx(() => [
      ($options.showInfoBar && $options.item)
        ? (openBlock(), createElementBlock("div", _hoisted_1$f, [
            createBaseVNode("div", _hoisted_2$e, [
              createBaseVNode("span", {
                class: "infobar-close",
                onClick: _cache[0] || (_cache[0] = $event => ($options.hideInfoBar()))
              }, "×"),
              createBaseVNode("h2", null, toDisplayString($options.item.name), 1 /* TEXT */),
              ($options.item.path === '/')
                ? (openBlock(), createElementBlock("div", _hoisted_3$d, [...(_cache[1] || (_cache[1] = [
                    createBaseVNode("span", { class: "icon-file placeholder-icon" }, null, -1 /* CACHED */),
                    createTextVNode(" Select file or folder to view its details. ", -1 /* CACHED */)
                  ]))]))
                : (openBlock(), createElementBlock("dl", _hoisted_4$8, [
                    createBaseVNode("dt", null, toDisplayString(_ctx.translate('COM_MEDIA_FOLDER')), 1 /* TEXT */),
                    createBaseVNode("dd", null, toDisplayString($options.item.directory), 1 /* TEXT */),
                    createBaseVNode("dt", null, toDisplayString(_ctx.translate('COM_MEDIA_MEDIA_TYPE')), 1 /* TEXT */),
                    ($options.item.type === 'file')
                      ? (openBlock(), createElementBlock("dd", _hoisted_5$7, toDisplayString(_ctx.translate('COM_MEDIA_FILE')), 1 /* TEXT */))
                      : ($options.item.type === 'dir')
                        ? (openBlock(), createElementBlock("dd", _hoisted_6$6, toDisplayString(_ctx.translate('COM_MEDIA_FOLDER')), 1 /* TEXT */))
                        : (openBlock(), createElementBlock("dd", _hoisted_7$4, " - ")),
                    createBaseVNode("dt", null, toDisplayString(_ctx.translate('COM_MEDIA_MEDIA_DATE_CREATED')), 1 /* TEXT */),
                    createBaseVNode("dd", null, toDisplayString($options.item.create_date_formatted), 1 /* TEXT */),
                    createBaseVNode("dt", null, toDisplayString(_ctx.translate('COM_MEDIA_MEDIA_DATE_MODIFIED')), 1 /* TEXT */),
                    createBaseVNode("dd", null, toDisplayString($options.item.modified_date_formatted), 1 /* TEXT */),
                    createBaseVNode("dt", null, toDisplayString(_ctx.translate('COM_MEDIA_MEDIA_DIMENSION')), 1 /* TEXT */),
                    ($options.item.width || $options.item.height)
                      ? (openBlock(), createElementBlock("dd", _hoisted_8$2, toDisplayString($options.item.width) + "px * " + toDisplayString($options.item.height) + "px ", 1 /* TEXT */))
                      : (openBlock(), createElementBlock("dd", _hoisted_9$1, " - ")),
                    createBaseVNode("dt", null, toDisplayString(_ctx.translate('COM_MEDIA_MEDIA_SIZE')), 1 /* TEXT */),
                    ($options.item.size)
                      ? (openBlock(), createElementBlock("dd", _hoisted_10$1, toDisplayString(($options.item.size / 1024).toFixed(2)) + " KB ", 1 /* TEXT */))
                      : (openBlock(), createElementBlock("dd", _hoisted_11$1, " - ")),
                    createBaseVNode("dt", null, toDisplayString(_ctx.translate('COM_MEDIA_MEDIA_MIME_TYPE')), 1 /* TEXT */),
                    createBaseVNode("dd", null, toDisplayString($options.item.mime_type), 1 /* TEXT */),
                    createBaseVNode("dt", null, toDisplayString(_ctx.translate('COM_MEDIA_MEDIA_EXTENSION')), 1 /* TEXT */),
                    createBaseVNode("dd", null, toDisplayString($options.item.extension || '-'), 1 /* TEXT */)
                  ]))
            ])
          ]))
        : createCommentVNode("v-if", true)
    ]),
    _: 1 /* STABLE */
  }))
}

script$f.render = render$f;
script$f.__file = "administrator/components/com_media/resources/scripts/components/infobar/infobar.vue";

function sortArray(array, by, direction) {
  return array.sort((a, b) => {
    // By name
    if (by === 'name') {
      if (direction === 'asc') {
        return a.name.toUpperCase().localeCompare(b.name.toUpperCase(), 'en', { sensitivity: 'base' });
      }
      return b.name.toUpperCase().localeCompare(a.name.toUpperCase(), 'en', { sensitivity: 'base' });
    }
    // By size
    if (by === 'size') {
      if (direction === 'asc') {
        return parseInt(a.size, 10) - parseInt(b.size, 10);
      }
      return parseInt(b.size, 10) - parseInt(a.size, 10);
    }
    // By dimension
    if (by === 'dimension') {
      if (direction === 'asc') {
        return (parseInt(a.width, 10) * parseInt(a.height, 10)) - (parseInt(b.width, 10) * parseInt(b.height, 10));
      }
      return (parseInt(b.width, 10) * parseInt(b.height, 10)) - (parseInt(a.width, 10) * parseInt(a.height, 10));
    }
    // By date created
    if (by === 'date_created') {
      if (direction === 'asc') {
        return new Date(a.create_date) - new Date(b.create_date);
      }
      return new Date(b.create_date) - new Date(a.create_date);
    }
    // By date modified
    if (by === 'date_modified') {
      if (direction === 'asc') {
        return new Date(a.modified_date) - new Date(b.modified_date);
      }
      return new Date(b.modified_date) - new Date(a.modified_date);
    }

    return array;
  });
}

var script$e = {
  name: 'MediaBrowser',
  components: {
    MediaBrowserTable: script$u,
    MediaInfobar: script$f,
    MediaBrowserItem,
  },
  computed: {
    /* Get the contents of the currently selected directory */
    localItems() {
      const dirs = sortArray(this.$store.getters.getSelectedDirectoryDirectories.slice(0), this.$store.state.sortBy, this.$store.state.sortDirection);
      const files = sortArray(this.$store.getters.getSelectedDirectoryFiles.slice(0), this.$store.state.sortBy, this.$store.state.sortDirection);

      return [
        ...dirs.filter((dir) => dir.name.toLowerCase().includes(this.$store.state.search.toLowerCase())),
        ...files.filter((file) => file.name.toLowerCase().includes(this.$store.state.search.toLowerCase())),
      ];
    },
    /* The styles for the media-browser element */
    getHeight() {
      return {
        height: this.$store.state.listView === 'table' && !this.isEmpty ? 'unset' : '100%',
      };
    },
    mediaBrowserStyles() {
      return {
        width: this.$store.state.showInfoBar ? '75%' : '100%',
        height: this.$store.state.listView === 'table' && !this.isEmpty ? 'unset' : '100%',
      };
    },
    isEmptySearch() {
      return this.$store.state.search !== '' && this.localItems.length === 0;
    },
    isEmpty() {
      return !this.localItems.length && !this.$store.state.isLoading;
    },
    /* The styles for the media-browser element */
    listView() {
      return this.$store.state.listView;
    },
    mediaBrowserGridItemsClass() {
      return {
        [`media-browser-items-${this.$store.state.gridSize}`]: true,
      };
    },
    isModal() {
      return Joomla.getOptions('com_media', {}).isModal;
    },
    currentDirectory() {
      const parts = this.$store.state.selectedDirectory.split('/').filter((crumb) => crumb.length !== 0);

      // The first part is the name of the drive, so if we have a folder name display it. Else
      // find the filename
      if (parts.length !== 1) {
        return parts[parts.length - 1];
      }

      let diskName = '';

      this.$store.state.disks.forEach((disk) => {
        disk.drives.forEach((drive) => {
          if (drive.root === `${parts[0]}/`) {
            diskName = drive.displayName;
          }
        });
      });

      return diskName;
    },
  },
  created() {
    document.body.addEventListener('click', this.unselectAllBrowserItems, false);
  },
  beforeUnmount() {
    document.body.removeEventListener('click', this.unselectAllBrowserItems, false);
  },
  methods: {
    /* Unselect all browser items */
    unselectAllBrowserItems(event) {
      const clickedDelete = !!((event.target.id !== undefined && event.target.id === 'mediaDelete'));
      const notClickedBrowserItems = (this.$refs.browserItems
        && !this.$refs.browserItems.contains(event.target))
        || event.target === this.$refs.browserItems;

      const notClickedInfobar = this.$refs.infobar !== undefined
        && !this.$refs.infobar.$el.contains(event.target);

      const clickedOutside = notClickedBrowserItems && notClickedInfobar && !clickedDelete;
      if (clickedOutside) {
        this.$store.commit(UNSELECT_ALL_BROWSER_ITEMS);

        window.parent.document.dispatchEvent(
          new CustomEvent(
            'onMediaFileSelected',
            {
              bubbles: true,
              cancelable: false,
              detail: {
                name: '',
                path: '',
                thumb: false,
                fileType: false,
                extension: false,
              },
            },
          ),
        );
      }
    },

    // Listeners for drag and drop
    // Fix for Chrome
    onDragEnter(e) {
      e.stopPropagation();
      return false;
    },

    // Notify user when file is over the drop area
    onDragOver(e) {
      e.preventDefault();
      document.querySelector('.media-dragoutline').classList.add('active');
      return false;
    },

    /* Upload files */
    upload(file) {
      // Create a new file reader instance
      const reader = new FileReader();

      // Add the on load callback
      reader.onload = (progressEvent) => {
        const { result } = progressEvent.target;
        const splitIndex = result.indexOf('base64') + 7;
        const content = result.slice(splitIndex, result.length);

        // Upload the file
        this.$store.dispatch('uploadFile', {
          name: file.name,
          parent: this.$store.state.selectedDirectory,
          content,
        });
      };

      reader.readAsDataURL(file);
    },

    // Logic for the dropped file
    onDrop(e) {
      e.preventDefault();

      // Loop through array of files and upload each file
      if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length > 0) {
        Array.from(e.dataTransfer.files).forEach((file) => {
          document.querySelector('.media-dragoutline').classList.remove('active');
          this.upload(file);
        });
      }
      document.querySelector('.media-dragoutline').classList.remove('active');
    },

    // Reset the drop area border
    onDragLeave(e) {
      e.stopPropagation();
      e.preventDefault();
      document.querySelector('.media-dragoutline').classList.remove('active');
      return false;
    },
  },
};

const _hoisted_1$e = {
  key: 0,
  class: "pt-1"
};
const _hoisted_2$d = { class: "alert alert-info m-3" };
const _hoisted_3$c = { class: "visually-hidden" };
const _hoisted_4$7 = {
  key: 1,
  class: "text-center",
  style: {"display":"grid","justify-content":"center","align-content":"center","color":"var(--gray-200)","height":"100%"}
};
const _hoisted_5$6 = { class: "media-dragoutline" };
const _hoisted_6$5 = {
  key: 3,
  class: "media-browser-grid"
};

function render$e(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_MediaBrowserTable = resolveComponent("MediaBrowserTable");
  const _component_MediaBrowserItem = resolveComponent("MediaBrowserItem");
  const _component_MediaInfobar = resolveComponent("MediaInfobar");

  return (openBlock(), createElementBlock("div", {
    ref: "browserItems",
    class: "media-browser",
    style: normalizeStyle($options.getHeight),
    onDragenter: _cache[0] || (_cache[0] = (...args) => ($options.onDragEnter && $options.onDragEnter(...args))),
    onDrop: _cache[1] || (_cache[1] = (...args) => ($options.onDrop && $options.onDrop(...args))),
    onDragover: _cache[2] || (_cache[2] = (...args) => ($options.onDragOver && $options.onDragOver(...args))),
    onDragleave: _cache[3] || (_cache[3] = (...args) => ($options.onDragLeave && $options.onDragLeave(...args)))
  }, [
    ($options.isEmptySearch)
      ? (openBlock(), createElementBlock("div", _hoisted_1$e, [
          createBaseVNode("div", _hoisted_2$d, [
            _cache[4] || (_cache[4] = createBaseVNode("span", {
              class: "icon-info-circle",
              "aria-hidden": "true"
            }, null, -1 /* CACHED */)),
            createBaseVNode("span", _hoisted_3$c, toDisplayString(_ctx.translate('NOTICE')), 1 /* TEXT */),
            createTextVNode(" " + toDisplayString(_ctx.translate('JGLOBAL_NO_MATCHING_RESULTS')), 1 /* TEXT */)
          ])
        ]))
      : createCommentVNode("v-if", true),
    ($options.isEmpty)
      ? (openBlock(), createElementBlock("div", _hoisted_4$7, [
          _cache[5] || (_cache[5] = createBaseVNode("span", {
            class: "fa-8x icon-cloud-upload upload-icon",
            "aria-hidden": "true"
          }, null, -1 /* CACHED */)),
          createBaseVNode("p", null, toDisplayString(_ctx.translate("COM_MEDIA_DROP_FILE")), 1 /* TEXT */)
        ]))
      : createCommentVNode("v-if", true),
    createBaseVNode("div", _hoisted_5$6, [
      _cache[6] || (_cache[6] = createBaseVNode("span", {
        class: "icon-cloud-upload upload-icon",
        "aria-hidden": "true"
      }, null, -1 /* CACHED */)),
      createBaseVNode("p", null, toDisplayString(_ctx.translate('COM_MEDIA_DROP_FILE')), 1 /* TEXT */)
    ]),
    (($options.listView === 'table' && !$options.isEmpty && !$options.isEmptySearch))
      ? (openBlock(), createBlock(_component_MediaBrowserTable, {
          key: 2,
          "local-items": $options.localItems,
          "current-directory": $options.currentDirectory,
          style: normalizeStyle($options.mediaBrowserStyles)
        }, null, 8 /* PROPS */, ["local-items", "current-directory", "style"]))
      : createCommentVNode("v-if", true),
    (($options.listView === 'grid' && !$options.isEmpty))
      ? (openBlock(), createElementBlock("div", _hoisted_6$5, [
          createBaseVNode("div", {
            class: normalizeClass(["media-browser-items", $options.mediaBrowserGridItemsClass]),
            style: normalizeStyle($options.mediaBrowserStyles)
          }, [
            (openBlock(true), createElementBlock(Fragment, null, renderList($options.localItems, (item) => {
              return (openBlock(), createBlock(_component_MediaBrowserItem, {
                key: item.path,
                item: item,
                "local-items": $options.localItems
              }, null, 8 /* PROPS */, ["item", "local-items"]))
            }), 128 /* KEYED_FRAGMENT */))
          ], 6 /* CLASS, STYLE */)
        ]))
      : createCommentVNode("v-if", true),
    createVNode(_component_MediaInfobar, { ref: "infobar" }, null, 512 /* NEED_PATCH */)
  ], 36 /* STYLE, NEED_HYDRATION */))
}

script$e.render = render$e;
script$e.__file = "administrator/components/com_media/resources/scripts/components/browser/browser.vue";

var script$d = {
  name: 'MediaTree',
  mixins: [navigable],
  props: {
    root: {
      type: String,
      required: true,
    },
    level: {
      type: Number,
      required: true,
    },
    parentIndex: {
      type: Number,
      required: true,
    },
  },
  emits: ['move-focus-to-parent'],
  computed: {
    /* Get the directories */
    directories() {
      return this.$store.state.directories
        .filter((directory) => (directory.directory === this.root))
        // Sort alphabetically
        .sort((a, b) => ((a.name.toUpperCase() < b.name.toUpperCase()) ? -1 : 1));
    },
  },
  methods: {
    isActive(item) {
      return (item.path === this.$store.state.selectedDirectory);
    },
    getTabindex(item) {
      return this.isActive(item) ? 0 : -1;
    },
    onItemClick(item) {
      this.navigateTo(item.path);
      window.parent.document.dispatchEvent(
        new CustomEvent(
          'onMediaFileSelected',
          {
            bubbles: true,
            cancelable: false,
            detail: {
              type: item.type,
              name: item.name,
              path: item.path,
            },
          },
        ),
      );
    },
    hasChildren(item) {
      return item.directories.length > 0;
    },
    isOpen(item) {
      return this.$store.state.selectedDirectory.includes(item.path);
    },
    iconClass(item) {
      return {
        fas: false,
        'icon-folder': !this.isOpen(item),
        'icon-folder-open': this.isOpen(item),
      };
    },
    setFocusToFirstChild() {
      this.$refs[`${this.root}0`][0].focus();
    },
    moveFocusToNextElement(currentIndex) {
      if ((currentIndex + 1) === this.directories.length) {
        return;
      }
      this.$refs[this.root + (currentIndex + 1)][0].focus();
    },
    moveFocusToPreviousElement(currentIndex) {
      if (currentIndex === 0) {
        return;
      }
      this.$refs[this.root + (currentIndex - 1)][0].focus();
    },
    moveFocusToChildElement(item) {
      if (!this.hasChildren(item)) {
        return;
      }
      this.$refs[item.path][0].setFocusToFirstChild();
    },
    moveFocusToParentElement() {
      this.$emit('move-focus-to-parent', this.parentIndex);
    },
    restoreFocus(parentIndex) {
      this.$refs[this.root + parentIndex][0].focus();
    },
  },
};

const _hoisted_1$d = {
  class: "media-tree",
  role: "group"
};
const _hoisted_2$c = ["aria-level", "aria-setsize", "aria-posinset", "tabindex", "onClick", "onKeyup"];
const _hoisted_3$b = { class: "item-icon" };
const _hoisted_4$6 = { class: "item-name" };

function render$d(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_MediaTree = resolveComponent("MediaTree");

  return (openBlock(), createElementBlock("ul", _hoisted_1$d, [
    (openBlock(true), createElementBlock(Fragment, null, renderList($options.directories, (item, index) => {
      return (openBlock(), createElementBlock("li", {
        key: item.path,
        class: normalizeClass(["media-tree-item", {active: $options.isActive(item)}]),
        role: "none"
      }, [
        createBaseVNode("a", {
          ref_for: true,
          ref: $props.root + index,
          role: "treeitem",
          "aria-level": $props.level,
          "aria-setsize": $options.directories.length,
          "aria-posinset": index,
          tabindex: $options.getTabindex(item),
          onClick: withModifiers($event => ($options.onItemClick(item)), ["stop","prevent"]),
          onKeyup: [
            withKeys($event => ($options.moveFocusToPreviousElement(index)), ["up"]),
            withKeys($event => ($options.moveFocusToNextElement(index)), ["down"]),
            withKeys($event => ($options.onItemClick(item)), ["enter"]),
            withKeys($event => ($options.moveFocusToChildElement(item)), ["right"]),
            _cache[0] || (_cache[0] = withKeys($event => ($options.moveFocusToParentElement()), ["left"]))
          ]
        }, [
          createBaseVNode("span", _hoisted_3$b, [
            createBaseVNode("span", {
              class: normalizeClass($options.iconClass(item))
            }, null, 2 /* CLASS */)
          ]),
          createBaseVNode("span", _hoisted_4$6, toDisplayString(item.name), 1 /* TEXT */)
        ], 40 /* PROPS, NEED_HYDRATION */, _hoisted_2$c),
        createVNode(Transition, { name: "slide-fade" }, {
          default: withCtx(() => [
            ($options.hasChildren(item))
              ? withDirectives((openBlock(), createBlock(_component_MediaTree, {
                  key: 0,
                  ref_for: true,
                  ref: item.path,
                  "aria-expanded": $options.isOpen(item) ? 'true' : 'false',
                  root: item.path,
                  level: ($props.level+1),
                  "parent-index": index,
                  onMoveFocusToParent: $options.restoreFocus
                }, null, 8 /* PROPS */, ["aria-expanded", "root", "level", "parent-index", "onMoveFocusToParent"])), [
                  [vShow, $options.isOpen(item)]
                ])
              : createCommentVNode("v-if", true)
          ]),
          _: 2 /* DYNAMIC */
        }, 1024 /* DYNAMIC_SLOTS */)
      ], 2 /* CLASS */))
    }), 128 /* KEYED_FRAGMENT */))
  ]))
}

script$d.render = render$d;
script$d.__file = "administrator/components/com_media/resources/scripts/components/tree/tree.vue";

var script$c = {
  name: 'MediaDrive',
  components: {
    MediaTree: script$d,
  },
  mixins: [navigable],
  props: {
    drive: {
      type: Object,
      default: () => {},
    },
    total: {
      type: Number,
      default: 0,
    },
    diskId: {
      type: String,
      default: '',
    },
    counter: {
      type: Number,
      default: 0,
    },
  },
  computed: {
    /* Whether or not the item is active */
    isActive() {
      return (this.$store.state.selectedDirectory === this.drive.root);
    },
    getTabindex() {
      return this.isActive ? 0 : -1;
    },
  },
  methods: {
    /* Handle the on drive click event */
    onDriveClick() {
      this.navigateTo(this.drive.root);

      window.parent.document.dispatchEvent(
        new CustomEvent('onMediaFileSelected', {
          bubbles: true,
          cancelable: false,
          detail: {
            type: 'dir',
            path: this.drive.root,
          },
        }),
      );
    },
    moveFocusToChildElement(nextRoot) {
      this.$refs[nextRoot].setFocusToFirstChild();
    },
    restoreFocus() {
      this.$refs['drive-root'].focus();
    },
  },
};

const _hoisted_1$c = ["aria-labelledby"];
const _hoisted_2$b = ["aria-setsize", "tabindex"];
const _hoisted_3$a = { class: "item-name" };

function render$c(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_MediaTree = resolveComponent("MediaTree");

  return (openBlock(), createElementBlock("div", {
    class: "media-drive",
    onClick: _cache[2] || (_cache[2] = withModifiers($event => ($options.onDriveClick()), ["stop","prevent"]))
  }, [
    createBaseVNode("ul", {
      class: "media-tree",
      role: "tree",
      "aria-labelledby": $props.diskId
    }, [
      createBaseVNode("li", {
        class: normalizeClass({active: $options.isActive, 'media-tree-item': true, 'media-drive-name': true}),
        role: "none"
      }, [
        createBaseVNode("a", {
          ref: "drive-root",
          role: "treeitem",
          "aria-level": "1",
          "aria-setsize": $props.counter,
          "aria-posinset": 1,
          tabindex: $options.getTabindex,
          onKeyup: [
            _cache[0] || (_cache[0] = withKeys($event => ($options.moveFocusToChildElement($props.drive.root)), ["right"])),
            _cache[1] || (_cache[1] = withKeys((...args) => ($options.onDriveClick && $options.onDriveClick(...args)), ["enter"]))
          ]
        }, [
          createBaseVNode("span", _hoisted_3$a, toDisplayString($props.drive.displayName), 1 /* TEXT */)
        ], 40 /* PROPS, NEED_HYDRATION */, _hoisted_2$b),
        createVNode(_component_MediaTree, {
          ref: $props.drive.root,
          root: $props.drive.root,
          level: 2,
          "parent-index": 0,
          onMoveFocusToParent: $options.restoreFocus
        }, null, 8 /* PROPS */, ["root", "onMoveFocusToParent"])
      ], 2 /* CLASS */)
    ], 8 /* PROPS */, _hoisted_1$c)
  ]))
}

script$c.render = render$c;
script$c.__file = "administrator/components/com_media/resources/scripts/components/tree/drive.vue";

var script$b = {
  name: 'MediaDisk',
  components: {
    MediaDrive: script$c,
  },
  props: {
    disk: {
      type: Object,
      default: () => {},
    },
    uid: {
      type: String,
      default: '',
    },
  },
  computed: {
    diskId() {
      return `disk-${this.uid + 1}`;
    },
  },
};

const _hoisted_1$b = { class: "media-disk" };
const _hoisted_2$a = { open: "" };
const _hoisted_3$9 = ["id"];

function render$b(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_MediaDrive = resolveComponent("MediaDrive");

  return (openBlock(), createElementBlock("div", _hoisted_1$b, [
    createBaseVNode("details", _hoisted_2$a, [
      createBaseVNode("summary", null, [
        createBaseVNode("h2", {
          id: $options.diskId,
          class: "media-disk-name"
        }, toDisplayString($props.disk.displayName), 9 /* TEXT, PROPS */, _hoisted_3$9)
      ]),
      (openBlock(true), createElementBlock(Fragment, null, renderList($props.disk.drives, (drive, index) => {
        return (openBlock(), createBlock(_component_MediaDrive, {
          key: index,
          "disk-id": $options.diskId,
          counter: index,
          drive: drive,
          total: $props.disk.drives.length
        }, null, 8 /* PROPS */, ["disk-id", "counter", "drive", "total"]))
      }), 128 /* KEYED_FRAGMENT */))
    ])
  ]))
}

script$b.render = render$b;
script$b.__file = "administrator/components/com_media/resources/scripts/components/tree/disk.vue";

var script$a = {
  name: 'MediaBreadcrumb',
  mixins: [navigable],
  computed: {
    /* Get the crumbs from the current directory path */
    crumbs() {
      const items = [];
      const adapter = this.$store.state.selectedDirectory.split(':/');

      // Add the drive as first element
      if (adapter.length) {
        const drive = this.findDrive(adapter[0]);

        if (!drive) {
          return [];
        }

        items.push(drive);
        let path = `${adapter[0]}:`;

        adapter[1]
          .split('/')
          .filter((crumb) => crumb.length !== 0)
          .forEach((crumb, index) => {
            path = `${path}/${crumb}`;
            items.push({
              name: crumb,
              index: index + 1,
              path,
            });
          });
      }

      return items;
    },
    /* Whether or not the crumb is the last element in the list */
    isLast(item) {
      return this.crumbs.indexOf(item) === this.crumbs.length - 1;
    },
  },
  methods: {
    /* Handle the on crumb click event */
    onCrumbClick(index) {
      const destination = this.crumbs.find((crumb) => crumb.index === index);

      if (!destination) {
        return;
      }

      this.navigateTo(destination.path);
      window.parent.document.dispatchEvent(
        new CustomEvent(
          'onMediaFileSelected',
          {
            bubbles: true,
            cancelable: false,
            detail: {
              type: 'dir',
              name: destination.name,
              path: destination.path,
            },
          },
        ),
      );
    },
    findDrive(adapter) {
      let driveObject = null;

      this.$store.state.disks.forEach((disk) => {
        disk.drives.forEach((drive) => {
          if (drive.root.startsWith(adapter)) {
            driveObject = { name: drive.displayName, path: drive.root, index: 0 };
          }
        });
      });

      return driveObject;
    },
  },
};

const _hoisted_1$a = ["aria-label"];
const _hoisted_2$9 = ["aria-current", "onClick"];

function render$a(_ctx, _cache, $props, $setup, $data, $options) {
  return (openBlock(), createElementBlock("nav", {
    class: "media-breadcrumb",
    "aria-label": _ctx.translate('COM_MEDIA_BREADCRUMB_LABEL')
  }, [
    createBaseVNode("ol", null, [
      (openBlock(true), createElementBlock(Fragment, null, renderList($options.crumbs, (val, index) => {
        return (openBlock(), createElementBlock("li", {
          key: index,
          class: "media-breadcrumb-item"
        }, [
          createBaseVNode("a", {
            href: "#",
            "aria-current": (index === Object.keys($options.crumbs).length - 1) ? 'page' : undefined,
            onClick: withModifiers($event => ($options.onCrumbClick(index)), ["stop","prevent"])
          }, toDisplayString(val.name), 9 /* TEXT, PROPS */, _hoisted_2$9)
        ]))
      }), 128 /* KEYED_FRAGMENT */))
    ])
  ], 8 /* PROPS */, _hoisted_1$a))
}

script$a.render = render$a;
script$a.__file = "administrator/components/com_media/resources/scripts/components/breadcrumb/breadcrumb.vue";

var script$9 = {
  name: 'MediaToolbar',
  components: {
    MediaBreadcrumb: script$a,
  },
  data() {
    return {
      sortingOptions: false,
    };
  },
  computed: {
    toggleListViewBtnIcon() {
      return (this.isGridView) ? 'icon-list' : 'icon-th';
    },
    isLoading() {
      return this.$store.state.isLoading;
    },
    atLeastOneItemSelected() {
      return this.$store.state.selectedItems.length > 0;
    },
    isGridView() {
      return (this.$store.state.listView === 'grid');
    },
    allItemsSelected() {
      return (this.$store.getters.getSelectedDirectoryContents.length === this.$store.state.selectedItems.length);
    },
    search() {
      return this.$store.state.search;
    },
  },
  watch: {
    '$store.state.selectedItems': function () {
      if (!this.allItemsSelected) {
        this.$refs.mediaToolbarSelectAll.checked = false;
      }
    },
  },
  methods: {
    toggleInfoBar() {
      if (this.$store.state.showInfoBar) {
        this.$store.commit(HIDE_INFOBAR);
      } else {
        this.$store.commit(SHOW_INFOBAR);
      }
    },
    decreaseGridSize() {
      if (!this.isGridSize('sm')) {
        this.$store.commit(DECREASE_GRID_SIZE);
      }
    },
    increaseGridSize() {
      if (!this.isGridSize('xl')) {
        this.$store.commit(INCREASE_GRID_SIZE);
      }
    },
    changeListView() {
      if (this.$store.state.listView === 'grid') {
        this.$store.commit(CHANGE_LIST_VIEW, 'table');
      } else {
        this.$store.commit(CHANGE_LIST_VIEW, 'grid');
      }
    },
    toggleSelectAll() {
      if (this.allItemsSelected) {
        this.$store.commit(UNSELECT_ALL_BROWSER_ITEMS);
      } else {
        this.$store.commit(SELECT_BROWSER_ITEMS, this.$store.getters.getSelectedDirectoryContents);
        window.parent.document.dispatchEvent(
          new CustomEvent(
            'onMediaFileSelected',
            {
              bubbles: true,
              cancelable: false,
              detail: {},
            },
          ),
        );
      }
    },
    isGridSize(size) {
      return (this.$store.state.gridSize === size);
    },
    changeSearch(query) {
      this.$store.commit(SET_SEARCH_QUERY, query.target.value);
    },
    showSortOptions() {
      this.sortingOptions = !this.sortingOptions;
    },
    changeOrderDirection() {
      this.$store.commit(UPDATE_SORT_DIRECTION, this.$refs.orderdirection.value);
    },
    changeOrderBy() {
      this.$store.commit(UPDATE_SORT_BY, this.$refs.orderby.value);
    },
  },
};

const _hoisted_1$9 = ["aria-label"];
const _hoisted_2$8 = {
  key: 0,
  class: "media-loader"
};
const _hoisted_3$8 = { class: "media-view-icons" };
const _hoisted_4$5 = ["aria-label"];
const _hoisted_5$5 = {
  class: "media-view-search-input",
  role: "search"
};
const _hoisted_6$4 = {
  for: "media_search",
  class: "visually-hidden"
};
const _hoisted_7$3 = ["placeholder", "value"];
const _hoisted_8$1 = { class: "media-view-icons" };
const _hoisted_9 = ["aria-label"];
const _hoisted_10 = ["aria-label"];
const _hoisted_11 = ["aria-label"];
const _hoisted_12 = ["aria-label"];
const _hoisted_13 = ["aria-label"];
const _hoisted_14 = {
  key: 0,
  class: "row g-3 pt-2 pb-2 pe-3 justify-content-end",
  style: {"border-inline-start":"1px solid var(--template-bg-dark-7)","margin-left":"0"}
};
const _hoisted_15 = { class: "col-3" };
const _hoisted_16 = ["aria-label", "value"];
const _hoisted_17 = { value: "name" };
const _hoisted_18 = { value: "size" };
const _hoisted_19 = { value: "dimension" };
const _hoisted_20 = { value: "date_created" };
const _hoisted_21 = { value: "date_modified" };
const _hoisted_22 = { class: "col-3" };
const _hoisted_23 = ["aria-label", "value"];
const _hoisted_24 = { value: "asc" };
const _hoisted_25 = { value: "desc" };

function render$9(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_MediaBreadcrumb = resolveComponent("MediaBreadcrumb");

  return (openBlock(), createElementBlock(Fragment, null, [
    createBaseVNode("div", {
      class: "media-toolbar",
      role: "toolbar",
      "aria-label": _ctx.translate('COM_MEDIA_TOOLBAR_LABEL')
    }, [
      ($options.isLoading)
        ? (openBlock(), createElementBlock("div", _hoisted_2$8))
        : createCommentVNode("v-if", true),
      createBaseVNode("div", _hoisted_3$8, [
        createBaseVNode("input", {
          ref: "mediaToolbarSelectAll",
          type: "checkbox",
          class: "media-toolbar-icon media-toolbar-select-all",
          "aria-label": _ctx.translate('COM_MEDIA_SELECT_ALL'),
          onClick: _cache[0] || (_cache[0] = withModifiers((...args) => ($options.toggleSelectAll && $options.toggleSelectAll(...args)), ["stop"]))
        }, null, 8 /* PROPS */, _hoisted_4$5)
      ]),
      createVNode(_component_MediaBreadcrumb),
      createBaseVNode("div", _hoisted_5$5, [
        createBaseVNode("label", _hoisted_6$4, toDisplayString(_ctx.translate('COM_MEDIA_SEARCH')), 1 /* TEXT */),
        createBaseVNode("input", {
          id: "media_search",
          class: "form-control",
          type: "text",
          placeholder: _ctx.translate('COM_MEDIA_SEARCH'),
          value: $options.search,
          onInput: _cache[1] || (_cache[1] = (...args) => ($options.changeSearch && $options.changeSearch(...args)))
        }, null, 40 /* PROPS, NEED_HYDRATION */, _hoisted_7$3)
      ]),
      createBaseVNode("div", _hoisted_8$1, [
        ($options.isGridView)
          ? (openBlock(), createElementBlock("button", {
              key: 0,
              type: "button",
              class: normalizeClass(["media-toolbar-icon", { active: $data.sortingOptions }]),
              "aria-label": _ctx.translate('COM_MEDIA_CHANGE_ORDERING'),
              onClick: _cache[2] || (_cache[2] = $event => ($options.showSortOptions()))
            }, [...(_cache[9] || (_cache[9] = [
              createBaseVNode("span", {
                class: "fas fa-sort-amount-down-alt",
                "aria-hidden": "true"
              }, null, -1 /* CACHED */)
            ]))], 10 /* CLASS, PROPS */, _hoisted_9))
          : createCommentVNode("v-if", true),
        ($options.isGridView)
          ? (openBlock(), createElementBlock("button", {
              key: 1,
              type: "button",
              class: normalizeClass(["media-toolbar-icon media-toolbar-decrease-grid-size", {disabled: $options.isGridSize('sm')}]),
              "aria-label": _ctx.translate('COM_MEDIA_DECREASE_GRID'),
              onClick: _cache[3] || (_cache[3] = withModifiers($event => ($options.decreaseGridSize()), ["stop","prevent"]))
            }, [...(_cache[10] || (_cache[10] = [
              createBaseVNode("span", {
                class: "icon-search-minus",
                "aria-hidden": "true"
              }, null, -1 /* CACHED */)
            ]))], 10 /* CLASS, PROPS */, _hoisted_10))
          : createCommentVNode("v-if", true),
        ($options.isGridView)
          ? (openBlock(), createElementBlock("button", {
              key: 2,
              type: "button",
              class: normalizeClass(["media-toolbar-icon media-toolbar-increase-grid-size", {disabled: $options.isGridSize('xl')}]),
              "aria-label": _ctx.translate('COM_MEDIA_INCREASE_GRID'),
              onClick: _cache[4] || (_cache[4] = withModifiers($event => ($options.increaseGridSize()), ["stop","prevent"]))
            }, [...(_cache[11] || (_cache[11] = [
              createBaseVNode("span", {
                class: "icon-search-plus",
                "aria-hidden": "true"
              }, null, -1 /* CACHED */)
            ]))], 10 /* CLASS, PROPS */, _hoisted_11))
          : createCommentVNode("v-if", true),
        createBaseVNode("button", {
          type: "button",
          class: "media-toolbar-icon media-toolbar-list-view",
          "aria-label": _ctx.translate('COM_MEDIA_TOGGLE_LIST_VIEW'),
          onClick: _cache[5] || (_cache[5] = withModifiers($event => ($options.changeListView()), ["stop","prevent"]))
        }, [
          createBaseVNode("span", {
            class: normalizeClass($options.toggleListViewBtnIcon),
            "aria-hidden": "true"
          }, null, 2 /* CLASS */)
        ], 8 /* PROPS */, _hoisted_12),
        createBaseVNode("button", {
          type: "button",
          class: "media-toolbar-icon media-toolbar-info",
          "aria-label": _ctx.translate('COM_MEDIA_TOGGLE_INFO'),
          onClick: _cache[6] || (_cache[6] = withModifiers((...args) => ($options.toggleInfoBar && $options.toggleInfoBar(...args)), ["stop","prevent"]))
        }, [...(_cache[12] || (_cache[12] = [
          createBaseVNode("span", {
            class: "icon-info",
            "aria-hidden": "true"
          }, null, -1 /* CACHED */)
        ]))], 8 /* PROPS */, _hoisted_13)
      ])
    ], 8 /* PROPS */, _hoisted_1$9),
    ($options.isGridView && $data.sortingOptions)
      ? (openBlock(), createElementBlock("div", _hoisted_14, [
          createBaseVNode("div", _hoisted_15, [
            createBaseVNode("select", {
              ref: "orderby",
              class: "form-select",
              "aria-label": _ctx.translate('COM_MEDIA_ORDER_BY'),
              value: _ctx.$store.state.sortBy,
              onChange: _cache[7] || (_cache[7] = $event => ($options.changeOrderBy()))
            }, [
              createBaseVNode("option", _hoisted_17, toDisplayString(_ctx.translate('COM_MEDIA_MEDIA_NAME')), 1 /* TEXT */),
              createBaseVNode("option", _hoisted_18, toDisplayString(_ctx.translate('COM_MEDIA_MEDIA_SIZE')), 1 /* TEXT */),
              createBaseVNode("option", _hoisted_19, toDisplayString(_ctx.translate('COM_MEDIA_MEDIA_DIMENSION')), 1 /* TEXT */),
              createBaseVNode("option", _hoisted_20, toDisplayString(_ctx.translate('COM_MEDIA_MEDIA_DATE_CREATED')), 1 /* TEXT */),
              createBaseVNode("option", _hoisted_21, toDisplayString(_ctx.translate('COM_MEDIA_MEDIA_DATE_MODIFIED')), 1 /* TEXT */)
            ], 40 /* PROPS, NEED_HYDRATION */, _hoisted_16)
          ]),
          createBaseVNode("div", _hoisted_22, [
            createBaseVNode("select", {
              ref: "orderdirection",
              class: "form-select",
              "aria-label": _ctx.translate('COM_MEDIA_ORDER_DIRECTION'),
              value: _ctx.$store.state.sortDirection,
              onChange: _cache[8] || (_cache[8] = $event => ($options.changeOrderDirection()))
            }, [
              createBaseVNode("option", _hoisted_24, toDisplayString(_ctx.translate('COM_MEDIA_ORDER_ASC')), 1 /* TEXT */),
              createBaseVNode("option", _hoisted_25, toDisplayString(_ctx.translate('COM_MEDIA_ORDER_DESC')), 1 /* TEXT */)
            ], 40 /* PROPS, NEED_HYDRATION */, _hoisted_23)
          ])
        ]))
      : createCommentVNode("v-if", true)
  ], 64 /* STABLE_FRAGMENT */))
}

script$9.render = render$9;
script$9.__file = "administrator/components/com_media/resources/scripts/components/toolbar/toolbar.vue";

var script$8 = {
  name: 'MediaUpload',
  props: {
    accept: {
      type: String,
      default: '',
    },
    extensions: {
      type: Function,
      default: () => [],
    },
    name: {
      type: String,
      default: 'file',
    },
    multiple: {
      type: Boolean,
      default: true,
    },
  },
  created() {
    // Listen to the toolbar upload click event
    MediaManager.Event.listen('onClickUpload', () => this.chooseFiles());
  },
  methods: {
    /* Open the choose-file dialog */
    chooseFiles() {
      this.$refs.fileInput.click();
    },
    /* Upload files */
    upload(e) {
      e.preventDefault();
      const { files } = e.target;

      // Loop through array of files and upload each file
      Array.from(files).forEach((file) => {
        // Create a new file reader instance
        const reader = new FileReader();

        // Add the on load callback
        reader.onload = (progressEvent) => {
          const { result } = progressEvent.target;
          const splitIndex = result.indexOf('base64') + 7;
          const content = result.slice(splitIndex, result.length);

          // Upload the file
          this.$store.dispatch('uploadFile', {
            name: file.name,
            parent: this.$store.state.selectedDirectory,
            content,
          });
        };

        reader.readAsDataURL(file);
      });
    },
  },
};

const _hoisted_1$8 = ["name", "multiple", "accept"];

function render$8(_ctx, _cache, $props, $setup, $data, $options) {
  return (openBlock(), createElementBlock("input", {
    ref: "fileInput",
    type: "file",
    class: "hidden",
    name: $props.name,
    multiple: $props.multiple,
    accept: $props.accept,
    onChange: _cache[0] || (_cache[0] = (...args) => ($options.upload && $options.upload(...args)))
  }, null, 40 /* PROPS, NEED_HYDRATION */, _hoisted_1$8))
}

script$8.render = render$8;
script$8.__file = "administrator/components/com_media/resources/scripts/components/upload/upload.vue";

/**
 * defines a focus group
 */
var FOCUS_GROUP = 'data-focus-lock';
/**
 * disables element discovery inside a group marked by key
 */
var FOCUS_DISABLED = 'data-focus-lock-disabled';
/**
 * allows uncontrolled focus within the marked area, effectively disabling focus lock for it's content
 */
var FOCUS_ALLOW = 'data-no-focus-lock';
/**
 * instructs autofocus engine to pick default autofocus inside a given node
 * can be set on the element or container
 */
var FOCUS_AUTO = 'data-autofocus-inside';
/**
 * instructs autofocus to ignore elements within a given node
 * can be set on the element or container
 */
var FOCUS_NO_AUTOFOCUS = 'data-no-autofocus';

var allConstants = /*#__PURE__*/Object.freeze({
  __proto__: null,
  FOCUS_ALLOW: FOCUS_ALLOW,
  FOCUS_AUTO: FOCUS_AUTO,
  FOCUS_DISABLED: FOCUS_DISABLED,
  FOCUS_GROUP: FOCUS_GROUP,
  FOCUS_NO_AUTOFOCUS: FOCUS_NO_AUTOFOCUS
});

/*
IE11 support
 */
var toArray = function toArray(a) {
  var ret = Array(a.length);
  for (var i = 0; i < a.length; ++i) {
    ret[i] = a[i];
  }
  return ret;
};
var asArray = function asArray(a) {
  return Array.isArray(a) ? a : [a];
};
var getFirst = function getFirst(a) {
  return Array.isArray(a) ? a[0] : a;
};

var isElementHidden = function isElementHidden(node) {
  // we can measure only "elements"
  // consider others as "visible"
  if (node.nodeType !== Node.ELEMENT_NODE) {
    return false;
  }
  var computedStyle = window.getComputedStyle(node, null);
  if (!computedStyle || !computedStyle.getPropertyValue) {
    return false;
  }
  return computedStyle.getPropertyValue('display') === 'none' || computedStyle.getPropertyValue('visibility') === 'hidden';
};
var getParentNode = function getParentNode(node) {
  // DOCUMENT_FRAGMENT_NODE can also point on ShadowRoot. In this case .host will point on the next node
  return node.parentNode && node.parentNode.nodeType === Node.DOCUMENT_FRAGMENT_NODE ?
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  node.parentNode.host : node.parentNode;
};
var isTopNode = function isTopNode(node) {
  // @ts-ignore
  return node === document || node && node.nodeType === Node.DOCUMENT_NODE;
};
var isInert = function isInert(node) {
  return node.hasAttribute('inert');
};
/**
 * @see https://github.com/testing-library/jest-dom/blob/main/src/to-be-visible.js
 */
var isVisibleUncached = function isVisibleUncached(node, checkParent) {
  return !node || isTopNode(node) || !isElementHidden(node) && !isInert(node) && checkParent(getParentNode(node));
};
var _isVisibleCached = function isVisibleCached(visibilityCache, node) {
  var cached = visibilityCache.get(node);
  if (cached !== undefined) {
    return cached;
  }
  var result = isVisibleUncached(node, _isVisibleCached.bind(undefined, visibilityCache));
  visibilityCache.set(node, result);
  return result;
};
var isAutoFocusAllowedUncached = function isAutoFocusAllowedUncached(node, checkParent) {
  return node && !isTopNode(node) ? isAutoFocusAllowed(node) ? checkParent(getParentNode(node)) : false : true;
};
var _isAutoFocusAllowedCached = function isAutoFocusAllowedCached(cache, node) {
  var cached = cache.get(node);
  if (cached !== undefined) {
    return cached;
  }
  var result = isAutoFocusAllowedUncached(node, _isAutoFocusAllowedCached.bind(undefined, cache));
  cache.set(node, result);
  return result;
};
var getDataset = function getDataset(node) {
  // @ts-ignore
  return node.dataset;
};
var isHTMLButtonElement = function isHTMLButtonElement(node) {
  return node.tagName === 'BUTTON';
};
var isHTMLInputElement = function isHTMLInputElement(node) {
  return node.tagName === 'INPUT';
};
var isRadioElement = function isRadioElement(node) {
  return isHTMLInputElement(node) && node.type === 'radio';
};
var notHiddenInput = function notHiddenInput(node) {
  return !((isHTMLInputElement(node) || isHTMLButtonElement(node)) && (node.type === 'hidden' || node.disabled));
};
var isAutoFocusAllowed = function isAutoFocusAllowed(node) {
  var attribute = node.getAttribute(FOCUS_NO_AUTOFOCUS);
  return ![true, 'true', ''].includes(attribute);
};
var isGuard = function isGuard(node) {
  var _a;
  return Boolean(node && ((_a = getDataset(node)) === null || _a === void 0 ? void 0 : _a.focusGuard));
};
var isNotAGuard = function isNotAGuard(node) {
  return !isGuard(node);
};
var isDefined = function isDefined(x) {
  return Boolean(x);
};

var tabSort = function tabSort(a, b) {
  var aTab = Math.max(0, a.tabIndex);
  var bTab = Math.max(0, b.tabIndex);
  var tabDiff = aTab - bTab;
  var indexDiff = a.index - b.index;
  if (tabDiff) {
    if (!aTab) {
      return 1;
    }
    if (!bTab) {
      return -1;
    }
  }
  return tabDiff || indexDiff;
};
var getTabIndex = function getTabIndex(node) {
  if (node.tabIndex < 0) {
    // all "focusable" elements are already preselected
    // but some might have implicit negative tabIndex
    // return 0 for <audio without tabIndex attribute - it is "tabbable"
    if (!node.hasAttribute('tabindex')) {
      return 0;
    }
  }
  return node.tabIndex;
};
var orderByTabIndex = function orderByTabIndex(nodes, filterNegative, keepGuards) {
  return toArray(nodes).map(function (node, index) {
    var tabIndex = getTabIndex(node);
    return {
      node: node,
      index: index,
      tabIndex: tabIndex
    };
  }).filter(function (data) {
    return true;
  }).sort(tabSort);
};

/**
 * list of the object to be considered as focusable
 */
var tabbables = ['button:enabled', 'select:enabled', 'textarea:enabled', 'input:enabled',
// elements with explicit roles will also use explicit tabindex
// '[role="button"]',
'a[href]', 'area[href]', 'summary', 'iframe', 'object', 'embed', 'audio[controls]', 'video[controls]', '[tabindex]', '[contenteditable]', '[autofocus]'];

var queryTabbables = tabbables.join(',');
var _getFocusablesWithShadowDom = function getFocusablesWithShadowDom(parent, withGuards) {
  return toArray((parent.shadowRoot || parent).children).reduce(function (acc, child) {
    return acc.concat(child.matches(queryTabbables) ? [child] : [], _getFocusablesWithShadowDom(child));
  }, []);
};
var getFocusablesWithIFrame = function getFocusablesWithIFrame(parent, withGuards) {
  var _a;
  // contentDocument of iframe will be null if current origin cannot access it
  if (parent instanceof HTMLIFrameElement && ((_a = parent.contentDocument) === null || _a === void 0 ? void 0 : _a.body)) {
    return getFocusables([parent.contentDocument.body]);
  }
  return [parent];
};
var getFocusables = function getFocusables(parents, withGuards) {
  return parents.reduce(function (acc, parent) {
    var _a;
    var focusableWithShadowDom = _getFocusablesWithShadowDom(parent);
    var focusableWithIframes = (_a = []).concat.apply(_a, focusableWithShadowDom.map(function (node) {
      return getFocusablesWithIFrame(node);
    }));
    return acc.concat(
    // add all tabbables inside and within shadow DOMs in DOM order
    focusableWithIframes,
    // add if node is tabbable itself
    parent.parentNode ? toArray(parent.parentNode.querySelectorAll(queryTabbables)).filter(function (node) {
      return node === parent;
    }) : []);
  }, []);
};
/**
 * return a list of focusable nodes within an area marked as "auto-focusable"
 * @param parent
 */
var getParentAutofocusables = function getParentAutofocusables(parent) {
  var parentFocus = parent.querySelectorAll("[".concat(FOCUS_AUTO, "]"));
  return toArray(parentFocus).map(function (node) {
    return getFocusables([node]);
  }).reduce(function (acc, nodes) {
    return acc.concat(nodes);
  }, []);
};

/**
 * given list of focusable elements keeps the ones user can interact with
 * @param nodes
 * @param visibilityCache
 */
var filterFocusable = function filterFocusable(nodes, visibilityCache) {
  return toArray(nodes).filter(function (node) {
    return _isVisibleCached(visibilityCache, node);
  }).filter(function (node) {
    return notHiddenInput(node);
  });
};
var filterAutoFocusable = function filterAutoFocusable(nodes, cache) {
  if (cache === void 0) {
    cache = new Map();
  }
  return toArray(nodes).filter(function (node) {
    return _isAutoFocusAllowedCached(cache, node);
  });
};
/**
 * !__WARNING__! Low level API.
 *
 * @returns anything "focusable", not only tabbable. The difference is in `tabIndex=-1`
 * (without guards, as long as they are not expected to be ever focused)
 *
 * @see {@link getTabbableNodes} to get only tabble nodes element
 *
 * @param topNodes - array of top level HTMLElements to search inside
 * @param visibilityCache - an cache to store intermediate measurements. Expected to be a fresh `new Map` on every call
 */
var getFocusableNodes = function getFocusableNodes(topNodes, visibilityCache) {
  return orderByTabIndex(filterFocusable(getFocusables(topNodes), visibilityCache));
};
/**
 * return list of nodes which are expected to be auto-focused
 * @param topNode
 * @param visibilityCache
 */
var parentAutofocusables = function parentAutofocusables(topNode, visibilityCache) {
  return filterFocusable(getParentAutofocusables(topNode), visibilityCache);
};
/*
 * Determines if element is contained in scope, including nested shadow DOMs
 */
var _contains = function contains(scope, element) {
  if (scope.shadowRoot) {
    return _contains(scope.shadowRoot, element);
  } else {
    if (Object.getPrototypeOf(scope).contains !== undefined && Object.getPrototypeOf(scope).contains.call(scope, element)) {
      return true;
    }
    return toArray(scope.children).some(function (child) {
      var _a;
      if (child instanceof HTMLIFrameElement) {
        var iframeBody = (_a = child.contentDocument) === null || _a === void 0 ? void 0 : _a.body;
        if (iframeBody) {
          return _contains(iframeBody, element);
        }
        return false;
      }
      return _contains(child, element);
    });
  }
};

/**
 * in case of multiple nodes nested inside each other
 * keeps only top ones
 * this is O(nlogn)
 * @param nodes
 * @returns {*}
 */
var filterNested = function filterNested(nodes) {
  var contained = new Set();
  var l = nodes.length;
  for (var i = 0; i < l; i += 1) {
    for (var j = i + 1; j < l; j += 1) {
      var position = nodes[i].compareDocumentPosition(nodes[j]);
      /* eslint-disable no-bitwise */
      if ((position & Node.DOCUMENT_POSITION_CONTAINED_BY) > 0) {
        contained.add(j);
      }
      if ((position & Node.DOCUMENT_POSITION_CONTAINS) > 0) {
        contained.add(i);
      }
      /* eslint-enable */
    }
  }
  return nodes.filter(function (_, index) {
    return !contained.has(index);
  });
};
/**
 * finds top most parent for a node
 * @param node
 * @returns {*}
 */
var _getTopParent = function getTopParent(node) {
  return node.parentNode ? _getTopParent(node.parentNode) : node;
};
/**
 * returns all "focus containers" inside a given node
 * @param node - node or nodes to look inside
 * @returns Element[]
 */
var getAllAffectedNodes = function getAllAffectedNodes(node) {
  var nodes = asArray(node);
  return nodes.filter(Boolean).reduce(function (acc, currentNode) {
    var group = currentNode.getAttribute(FOCUS_GROUP);
    acc.push.apply(acc, group ? filterNested(toArray(_getTopParent(currentNode).querySelectorAll("[".concat(FOCUS_GROUP, "=\"").concat(group, "\"]:not([").concat(FOCUS_DISABLED, "=\"disabled\"])")))) : [currentNode]);
    return acc;
  }, []);
};

var safeProbe = function safeProbe(cb) {
  try {
    return cb();
  } catch (e) {
    return undefined;
  }
};

/**
 * returns active element from document or from nested shadowdoms
 */
/**
 * returns current active element. If the active element is a "container" itself(shadowRoot or iframe) returns active element inside it
 * @param [inDocument]
 */
var _getActiveElement = function getActiveElement(inDocument) {
  if (inDocument === void 0) {
    inDocument = document;
  }
  if (!inDocument || !inDocument.activeElement) {
    return undefined;
  }
  var activeElement = inDocument.activeElement;
  return activeElement.shadowRoot ? _getActiveElement(activeElement.shadowRoot) : activeElement instanceof HTMLIFrameElement && safeProbe(function () {
    return activeElement.contentWindow.document;
  }) ? _getActiveElement(activeElement.contentWindow.document) : activeElement;
};

var focusInFrame = function focusInFrame(frame, activeElement) {
  return frame === activeElement;
};
var focusInsideIframe = function focusInsideIframe(topNode, activeElement) {
  return Boolean(toArray(topNode.querySelectorAll('iframe')).some(function (node) {
    return focusInFrame(node, activeElement);
  }));
};
/**
 * @returns {Boolean} true, if the current focus is inside given node or nodes.
 * Supports nodes hidden inside shadowDom
 */
var focusInside = function focusInside(topNode, activeElement) {
  // const activeElement = document && getActiveElement();
  if (activeElement === void 0) {
    activeElement = _getActiveElement(getFirst(topNode).ownerDocument);
  }
  if (!activeElement || activeElement.dataset && activeElement.dataset.focusGuard) {
    return false;
  }
  return getAllAffectedNodes(topNode).some(function (node) {
    return _contains(node, activeElement) || focusInsideIframe(node, activeElement);
  });
};

/**
 * checks if focus is hidden FROM the focus-lock
 * ie contained inside a node focus-lock shall ignore
 *
 * This is a utility function coupled with {@link FOCUS_ALLOW} constant
 *
 * @returns {boolean} focus is currently is in "allow" area
 */
var focusIsHidden = function focusIsHidden(inDocument) {
  if (inDocument === void 0) {
    inDocument = document;
  }
  var activeElement = _getActiveElement(inDocument);
  if (!activeElement) {
    return false;
  }
  // this does not support setting FOCUS_ALLOW within shadow dom
  return toArray(inDocument.querySelectorAll("[".concat(FOCUS_ALLOW, "]"))).some(function (node) {
    return _contains(node, activeElement);
  });
};

var findSelectedRadio = function findSelectedRadio(node, nodes) {
  return nodes.filter(isRadioElement).filter(function (el) {
    return el.name === node.name;
  }).filter(function (el) {
    return el.checked;
  })[0] || node;
};
var correctNode = function correctNode(node, nodes) {
  if (isRadioElement(node) && node.name) {
    return findSelectedRadio(node, nodes);
  }
  return node;
};
/**
 * giving a set of radio inputs keeps only selected (tabbable) ones
 * @param nodes
 */
var correctNodes = function correctNodes(nodes) {
  // IE11 has no Set(array) constructor
  var resultSet = new Set();
  nodes.forEach(function (node) {
    return resultSet.add(correctNode(node, nodes));
  });
  // using filter to support IE11
  return nodes.filter(function (node) {
    return resultSet.has(node);
  });
};

var pickFirstFocus = function pickFirstFocus(nodes) {
  if (nodes[0] && nodes.length > 1) {
    return correctNode(nodes[0], nodes);
  }
  return nodes[0];
};
var pickFocusable = function pickFocusable(nodes, node) {
  return nodes.indexOf(correctNode(node, nodes));
};

var NEW_FOCUS = 'NEW_FOCUS';
/**
 * Main solver for the "find next focus" question
 * @param innerNodes - used to control "return focus"
 * @param innerTabbables - used to control "autofocus"
 * @param outerNodes
 * @param activeElement
 * @param lastNode
 * @returns {number|string|undefined|*}
 */
var newFocus = function newFocus(innerNodes, innerTabbables, outerNodes, activeElement, lastNode) {
  var cnt = innerNodes.length;
  var firstFocus = innerNodes[0];
  var lastFocus = innerNodes[cnt - 1];
  var isOnGuard = isGuard(activeElement);
  // focus is inside
  if (activeElement && innerNodes.indexOf(activeElement) >= 0) {
    return undefined;
  }
  var activeIndex = activeElement !== undefined ? outerNodes.indexOf(activeElement) : -1;
  var lastIndex = lastNode ? outerNodes.indexOf(lastNode) : activeIndex;
  var lastNodeInside = lastNode ? innerNodes.indexOf(lastNode) : -1;
  // no active focus (or focus is on the body)
  if (activeIndex === -1) {
    // known fallback
    if (lastNodeInside !== -1) {
      return lastNodeInside;
    }
    return NEW_FOCUS;
  }
  // new focus, nothing to calculate
  if (lastNodeInside === -1) {
    return NEW_FOCUS;
  }
  var indexDiff = activeIndex - lastIndex;
  var firstNodeIndex = outerNodes.indexOf(firstFocus);
  var lastNodeIndex = outerNodes.indexOf(lastFocus);
  var correctedNodes = correctNodes(outerNodes);
  var currentFocusableIndex = activeElement !== undefined ? correctedNodes.indexOf(activeElement) : -1;
  var previousFocusableIndex = lastNode ? correctedNodes.indexOf(lastNode) : currentFocusableIndex;
  var tabbableNodes = correctedNodes.filter(function (node) {
    return node.tabIndex >= 0;
  });
  var currentTabbableIndex = activeElement !== undefined ? tabbableNodes.indexOf(activeElement) : -1;
  var previousTabbableIndex = lastNode ? tabbableNodes.indexOf(lastNode) : currentTabbableIndex;
  var focusIndexDiff = currentTabbableIndex >= 0 && previousTabbableIndex >= 0 ?
  // old/new are tabbables, measure distance in tabbable space
  previousTabbableIndex - currentTabbableIndex :
  // or else measure in focusable space
  previousFocusableIndex - currentFocusableIndex;
  // old focus
  if (!indexDiff && lastNodeInside >= 0) {
    return lastNodeInside;
  }
  // no tabbable elements, autofocus is not possible
  if (innerTabbables.length === 0) {
    // an edge case with no tabbable elements
    // return the last focusable one
    // with some probability this will prevent focus from cycling across the lock, but there is no tabbale elements to cycle to
    return lastNodeInside;
  }
  var returnFirstNode = pickFocusable(innerNodes, innerTabbables[0]);
  var returnLastNode = pickFocusable(innerNodes, innerTabbables[innerTabbables.length - 1]);
  // first element
  if (activeIndex <= firstNodeIndex && isOnGuard && Math.abs(indexDiff) > 1) {
    return returnLastNode;
  }
  // last element
  if (activeIndex >= lastNodeIndex && isOnGuard && Math.abs(indexDiff) > 1) {
    return returnFirstNode;
  }
  // jump out, but not on the guard
  if (indexDiff && Math.abs(focusIndexDiff) > 1) {
    return lastNodeInside;
  }
  // focus above lock
  if (activeIndex <= firstNodeIndex) {
    return returnLastNode;
  }
  // focus below lock
  if (activeIndex > lastNodeIndex) {
    return returnFirstNode;
  }
  // index is inside tab order, but outside Lock
  if (indexDiff) {
    if (Math.abs(indexDiff) > 1) {
      return lastNodeInside;
    }
    return (cnt + lastNodeInside + indexDiff) % cnt;
  }
  // do nothing
  return undefined;
};

var findAutoFocused = function findAutoFocused(autoFocusables) {
  return function (node) {
    var _a;
    var autofocus = (_a = getDataset(node)) === null || _a === void 0 ? void 0 : _a.autofocus;
    return (
      // @ts-expect-error
      node.autofocus ||
      //
      autofocus !== undefined && autofocus !== 'false' ||
      //
      autoFocusables.indexOf(node) >= 0
    );
  };
};
var pickAutofocus = function pickAutofocus(nodesIndexes, orderedNodes, groups) {
  var nodes = nodesIndexes.map(function (_a) {
    var node = _a.node;
    return node;
  });
  var autoFocusable = filterAutoFocusable(nodes.filter(findAutoFocused(groups)));
  if (autoFocusable && autoFocusable.length) {
    return pickFirstFocus(autoFocusable);
  }
  return pickFirstFocus(filterAutoFocusable(orderedNodes));
};

var _getParents = function getParents(node, parents) {
  if (parents === void 0) {
    parents = [];
  }
  parents.push(node);
  if (node.parentNode) {
    _getParents(node.parentNode.host || node.parentNode, parents);
  }
  return parents;
};
/**
 * finds a parent for both nodeA and nodeB
 * @param nodeA
 * @param nodeB
 * @returns {boolean|*}
 */
var getCommonParent = function getCommonParent(nodeA, nodeB) {
  var parentsA = _getParents(nodeA);
  var parentsB = _getParents(nodeB);
  // tslint:disable-next-line:prefer-for-of
  for (var i = 0; i < parentsA.length; i += 1) {
    var currentParent = parentsA[i];
    if (parentsB.indexOf(currentParent) >= 0) {
      return currentParent;
    }
  }
  return false;
};
var getTopCommonParent = function getTopCommonParent(baseActiveElement, leftEntry, rightEntries) {
  var activeElements = asArray(baseActiveElement);
  var leftEntries = asArray(leftEntry);
  var activeElement = activeElements[0];
  var topCommon = false;
  leftEntries.filter(Boolean).forEach(function (entry) {
    topCommon = getCommonParent(topCommon || entry, entry) || topCommon;
    rightEntries.filter(Boolean).forEach(function (subEntry) {
      var common = getCommonParent(activeElement, subEntry);
      if (common) {
        if (!topCommon || _contains(common, topCommon)) {
          topCommon = common;
        } else {
          topCommon = getCommonParent(common, topCommon);
        }
      }
    });
  });
  // TODO: add assert here?
  return topCommon;
};
/**
 * return list of nodes which are expected to be autofocused inside a given top nodes
 * @param entries
 * @param visibilityCache
 */
var allParentAutofocusables = function allParentAutofocusables(entries, visibilityCache) {
  return entries.reduce(function (acc, node) {
    return acc.concat(parentAutofocusables(node, visibilityCache));
  }, []);
};

var reorderNodes = function reorderNodes(srcNodes, dstNodes) {
  var remap = new Map();
  // no Set(dstNodes) for IE11 :(
  dstNodes.forEach(function (entity) {
    return remap.set(entity.node, entity);
  });
  // remap to dstNodes
  return srcNodes.map(function (node) {
    return remap.get(node);
  }).filter(isDefined);
};
/**
 * contains the main logic of the `focus-lock` package.
 *
 * ! you probably dont need this function !
 *
 * given top node(s) and the last active element returns the element to be focused next
 * @returns element which should be focused to move focus inside
 * @param topNode
 * @param lastNode
 */
var focusSolver = function focusSolver(topNode, lastNode) {
  var activeElement = _getActiveElement(asArray(topNode).length > 0 ? document : getFirst(topNode).ownerDocument);
  var entries = getAllAffectedNodes(topNode).filter(isNotAGuard);
  var commonParent = getTopCommonParent(activeElement || topNode, topNode, entries);
  var visibilityCache = new Map();
  var anyFocusable = getFocusableNodes(entries, visibilityCache);
  var innerElements = anyFocusable.filter(function (_a) {
    var node = _a.node;
    return isNotAGuard(node);
  });
  if (!innerElements[0]) {
    return undefined;
  }
  var outerNodes = getFocusableNodes([commonParent], visibilityCache).map(function (_a) {
    var node = _a.node;
    return node;
  });
  var orderedInnerElements = reorderNodes(outerNodes, innerElements);
  // collect inner focusable and separately tabbables
  var innerFocusables = orderedInnerElements.map(function (_a) {
    var node = _a.node;
    return node;
  });
  var innerTabbable = orderedInnerElements.filter(function (_a) {
    var tabIndex = _a.tabIndex;
    return tabIndex >= 0;
  }).map(function (_a) {
    var node = _a.node;
    return node;
  });
  var newId = newFocus(innerFocusables, innerTabbable, outerNodes, activeElement, lastNode);
  if (newId === NEW_FOCUS) {
    var focusNode =
    // first try only tabbable, and the fallback to all focusable, as long as at least one element should be picked for focus
    pickAutofocus(anyFocusable, innerTabbable, allParentAutofocusables(entries, visibilityCache)) || pickAutofocus(anyFocusable, innerFocusables, allParentAutofocusables(entries, visibilityCache));
    if (focusNode) {
      return {
        node: focusNode
      };
    } else {
      console.warn('focus-lock: cannot find any node to move focus into');
      return undefined;
    }
  }
  if (newId === undefined) {
    return newId;
  }
  return orderedInnerElements[newId];
};

var focusOn = function focusOn(target, focusOptions) {
  if (!target) {
    // not clear how, but is possible https://github.com/theKashey/focus-lock/issues/53
    return;
  }
  if ('focus' in target) {
    target.focus(focusOptions);
  }
  if ('contentWindow' in target && target.contentWindow) {
    target.contentWindow.focus();
  }
};

var guardCount = 0;
var lockDisabled = false;
/**
 * The main functionality of the focus-lock package
 *
 * Contains focus at a given node.
 * The last focused element will help to determine which element(first or last) should be focused.
 * The found element will be focused.
 *
 * This is one time action (move), not a persistent focus-lock
 *
 * HTML markers (see {@link import('./constants').FOCUS_AUTO} constants) can control autofocus
 * @see {@link focusSolver} for the same functionality without autofocus
 */
var moveFocusInside = function moveFocusInside(topNode, lastNode, options) {
  if (options === void 0) {
    options = {};
  }
  var focusable = focusSolver(topNode, lastNode);
  // global local side effect to countain recursive lock activation and resolve focus-fighting
  if (lockDisabled) {
    return;
  }
  if (focusable) {
    /** +FOCUS-FIGHTING prevention **/
    if (guardCount > 2) {
      // we have recursive entered back the lock activation
      console.error('FocusLock: focus-fighting detected. Only one focus management system could be active. ' + 'See https://github.com/theKashey/focus-lock/#focus-fighting');
      lockDisabled = true;
      setTimeout(function () {
        lockDisabled = false;
      }, 1);
      return;
    }
    guardCount++;
    focusOn(focusable.node, options.focusOptions);
    guardCount--;
  }
};

/**
 * magic symbols to control focus behavior from DOM
 * see description of every particular one
 */
var constants = allConstants;
/**
 * @deprecated - please use {@link moveFocusInside} named export
 */
var deprecated_default_moveFocusInside = moveFocusInside;
//

function deferAction(action) {
    const setImmediate = window.setImmediate;

    if (typeof setImmediate !== 'undefined') {
      setImmediate(action);
    } else {
      setTimeout(action, 1);
    }
  }

  let lastActiveTrap = 0;
  let lastActiveFocus = null;

  let focusWasOutsideWindow = false;

  const focusOnBody = () => (
    document && document.activeElement === document.body
  );

  const isFreeFocus = () => focusOnBody() || focusIsHidden();

  const activateTrap = () => {
    let result = false;

    if (lastActiveTrap) {
      const {observed, onActivation} = lastActiveTrap;

      if (focusWasOutsideWindow || !isFreeFocus() || !lastActiveFocus) {
        if (observed && !focusInside(observed)) {
          onActivation();
          result = deprecated_default_moveFocusInside(observed, lastActiveFocus);
        }

        focusWasOutsideWindow = false;
        lastActiveFocus = document && document.activeElement;
      }
    }

    return result;
  };

  const reducePropsToState = (propsList) => {
    return propsList
      .filter(({disabled}) => !disabled)
      .slice(-1)[0];
  };

  const handleStateChangeOnClient = (trap) => {
    if (lastActiveTrap !== trap) {
      lastActiveTrap = null;
    }

    lastActiveTrap = trap;

    if (trap) {
      activateTrap();
      deferAction(activateTrap);
    }
  };

  let instances = [];

  const emitChange = () => {
    handleStateChangeOnClient(reducePropsToState(instances));
  };

  const onTrap = (event) => {
    if (activateTrap() && event) {
      // prevent scroll jump
      event.stopPropagation();
      event.preventDefault();
    }
  };

  const onBlur = () => {
    deferAction(activateTrap);
  };

  const onWindowBlur = () => {
    focusWasOutsideWindow = true;
  };

  const attachHandler = () => {
    document.addEventListener('focusin', onTrap, true);
    document.addEventListener('focusout', onBlur);
    window.addEventListener('blur', onWindowBlur);
  };

  const detachHandler = () => {
    document.removeEventListener('focusin', onTrap, true);
    document.removeEventListener('focusout', onBlur);
    window.removeEventListener('blur', onWindowBlur);
  };


  var script$7 = {
    name: 'Lock',
    props: {
      returnFocus: {
        type: Boolean
      },
      disabled: {
        type: Boolean
      },
      noFocusGuards: {
        type: [Boolean, String],
        default: false
      },
      group: {
        type: String
      }
    },
    setup(props) {
      const { returnFocus, disabled, noFocusGuards, group } = toRefs(props);

      const rootEl = ref(null);
      const data = ref({});
      const hidden = ref(""); //    "width: 1px;height: 0px;padding: 0;overflow: hidden;position: fixed;top: 0;left: 0;"

      const groupAttr = computed(() => {
        return {[constants.FOCUS_GROUP]: group.value};
      });

      const hasLeadingGuards = computed(() => {
        return noFocusGuards.value !== true;
      });

      const hasTailingGuards = computed(() => {
        return hasLeadingGuards.value && (noFocusGuards.value !== 'tail');
      });

      watch(disabled, () => {
        data.value.disabled = disabled.value;
        emitChange();
      });


      let originalFocusedElement;

      onMounted(() => {
        const currentInstance = getCurrentInstance();

        if (!currentInstance) {
          return;
        }

        data.value.instance = currentInstance.proxy;
        data.value.observed = rootEl.value.querySelector("[data-lock]");
        data.value.disabled = disabled.value;

        data.value.onActivation = () => {
          originalFocusedElement = originalFocusedElement || document && document.activeElement;
        };

        if (!instances.length) {
          attachHandler();
        }

        instances.push(data.value);
        emitChange();
      });

      onUnmounted(() => {
        const currentInstance = getCurrentInstance();

        if (!currentInstance) {
          return;
        }

        instances = instances.filter(({instance}) => instance !== currentInstance.proxy);

        if (!instances.length) {
          detachHandler();
        }

        if (
          returnFocus.value &&
          originalFocusedElement &&
          originalFocusedElement.focus
        ) {
          originalFocusedElement.focus();
        }

        emitChange();
      });

      return {
        groupAttr,
        hasLeadingGuards,
        hasTailingGuards,
        hidden,
        onBlur: () => deferAction(emitChange),
        rootEl,
      };
    },
  };

const _hoisted_1$7 = { ref: "rootEl" };
const _hoisted_2$7 = ["tabIndex"];
const _hoisted_3$7 = ["tabIndex"];

function render$7(_ctx, _cache, $props, $setup, $data, $options) {
  return (openBlock(), createElementBlock("div", _hoisted_1$7, [
    ($setup.hasLeadingGuards)
      ? (openBlock(), createElementBlock("div", {
          key: 0,
          tabIndex: $props.disabled ? -1 : 0,
          style: normalizeStyle($setup.hidden),
          "aria-hidden": "true",
          "data-focus-guard": ""
        }, null, 12 /* STYLE, PROPS */, _hoisted_2$7))
      : createCommentVNode("v-if", true),
    createBaseVNode("div", mergeProps($setup.groupAttr, {
      "data-lock": "",
      onFocusout: _cache[0] || (_cache[0] = (...args) => ($setup.onBlur && $setup.onBlur(...args)))
    }), [
      renderSlot(_ctx.$slots, "default")
    ], 16 /* FULL_PROPS */),
    ($setup.hasTailingGuards)
      ? (openBlock(), createElementBlock("div", {
          key: 1,
          tabIndex: $props.disabled ? -1 : 0,
          style: normalizeStyle($setup.hidden),
          "aria-hidden": "true",
          "data-focus-guard": ""
        }, null, 12 /* STYLE, PROPS */, _hoisted_3$7))
      : createCommentVNode("v-if", true)
  ], 512 /* NEED_PATCH */))
}

script$7.render = render$7;
script$7.__file = "node_modules/vue-focus-lock/src/Lock.vue";

var script$6 = {
  name: 'MediaModal',
  components: {
    Lock: script$7,
  },
  props: {
    /* Whether or not the close button in the header should be shown */
    showClose: {
      type: Boolean,
      default: true,
    },
    /* The size of the modal */
    size: {
      type: String,
      default: '',
    },
    labelElement: {
      type: String,
      required: true,
    },
  },
  emits: ['close'],
  computed: {
    /* Get the modal css class */
    modalClass() {
      return {
        'modal-sm': this.size === 'sm',
      };
    },
  },
  mounted() {
    // Listen to keydown events on the document
    document.addEventListener('keydown', this.onKeyDown);
  },
  beforeUnmount() {
    // Remove the keydown event listener
    document.removeEventListener('keydown', this.onKeyDown);
  },
  methods: {
    /* Close the modal instance */
    close() {
      this.$emit('close');
    },
    /* Handle keydown events */
    onKeyDown(event) {
      if (event.keyCode === 27) {
        this.close();
      }
    },
  },
};

const _hoisted_1$6 = ["aria-labelledby"];
const _hoisted_2$6 = { class: "modal-content" };
const _hoisted_3$6 = { class: "modal-header" };
const _hoisted_4$4 = { class: "modal-body" };
const _hoisted_5$4 = { class: "modal-footer" };

function render$6(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_Lock = resolveComponent("Lock");

  return (openBlock(), createElementBlock("div", {
    class: "media-modal-backdrop",
    onClick: _cache[2] || (_cache[2] = $event => ($options.close()))
  }, [
    createBaseVNode("div", {
      class: "modal",
      style: {"display":"flex"},
      onClick: _cache[1] || (_cache[1] = withModifiers(() => {}, ["stop"]))
    }, [
      createVNode(_component_Lock, null, {
        default: withCtx(() => [
          createBaseVNode("div", {
            class: normalizeClass(["modal-dialog", $options.modalClass]),
            role: "dialog",
            "aria-labelledby": $props.labelElement
          }, [
            createBaseVNode("div", _hoisted_2$6, [
              createBaseVNode("div", _hoisted_3$6, [
                renderSlot(_ctx.$slots, "header"),
                renderSlot(_ctx.$slots, "backdrop-close"),
                ($props.showClose)
                  ? (openBlock(), createElementBlock("button", {
                      key: 0,
                      type: "button",
                      class: "btn-close",
                      "aria-label": "Close",
                      onClick: _cache[0] || (_cache[0] = $event => ($options.close()))
                    }))
                  : createCommentVNode("v-if", true)
              ]),
              createBaseVNode("div", _hoisted_4$4, [
                renderSlot(_ctx.$slots, "body")
              ]),
              createBaseVNode("div", _hoisted_5$4, [
                renderSlot(_ctx.$slots, "footer")
              ])
            ])
          ], 10 /* CLASS, PROPS */, _hoisted_1$6)
        ]),
        _: 3 /* FORWARDED */
      })
    ])
  ]))
}

script$6.render = render$6;
script$6.__file = "administrator/components/com_media/resources/scripts/components/modals/modal.vue";

var script$5 = {
  name: 'MediaCreateFolderModal',
  components: {
    MediaModal: script$6,
  },
  data() {
    return {
      folder: '',
    };
  },
  computed: {
    /* Get the contents of the currently selected directory */
    items() {
      const directories = this.$store.getters.getSelectedDirectoryDirectories;
      const files = this.$store.getters.getSelectedDirectoryFiles;

      return [...directories, ...files];
    },
  },
  watch: {
    '$store.state.showCreateFolderModal': function (show) {
      this.$nextTick(() => {
        if (show && this.$refs.input) {
          this.$refs.input.focus();
        }
      });
    },
  },

  methods: {
    /* Check if the form is valid */
    isValid() {
      return (this.folder);
    },
    /* Check folder name is valid or not */
    isValidName() {
      if (this.folder.includes('..')) {
        return 1;
      }
      if ((this.items.filter((file) => file.name.toLowerCase() === (this.folder.toLowerCase())).length !== 0)) {
        return 2;
      }
      if ((!/^[\p{L}\p{N}\-_. ]+$/u.test(this.folder))) {
        return 3;
      }
      return 0;
    },
    /* Close the modal instance */
    close() {
      this.reset();
      this.$store.commit(HIDE_CREATE_FOLDER_MODAL);
    },
    /* Save the form and create the folder */
    save() {
      // Check if the form is valid
      if (!this.isValid()) {
        // @todo show an error message to user for insert a folder name
        // @todo mark the field as invalid
        return;
      }

      // Create the directory
      this.$store.dispatch('createDirectory', {
        name: this.folder,
        parent: this.$store.state.selectedDirectory,
      });
      this.reset();
    },
    /* Reset the form */
    reset() {
      this.folder = '';
    },
  },
};

const _hoisted_1$5 = {
  id: "createFolderTitle",
  class: "modal-title"
};
const _hoisted_2$5 = { class: "p-3" };
const _hoisted_3$5 = { class: "form-group" };
const _hoisted_4$3 = { for: "folder" };
const _hoisted_5$3 = {
  key: 0,
  id: "folderFeedback",
  class: "invalid-feedback"
};
const _hoisted_6$3 = {
  key: 1,
  id: "folderFeedback",
  class: "invalid-feedback"
};
const _hoisted_7$2 = {
  key: 2,
  id: "folderFeedback",
  class: "invalid-feedback"
};
const _hoisted_8 = ["disabled"];

function render$5(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_MediaModal = resolveComponent("MediaModal");

  return (_ctx.$store.state.showCreateFolderModal)
    ? (openBlock(), createBlock(_component_MediaModal, {
        key: 0,
        size: 'md',
        "label-element": "createFolderTitle",
        onClose: _cache[5] || (_cache[5] = $event => ($options.close()))
      }, {
        header: withCtx(() => [
          createBaseVNode("h3", _hoisted_1$5, toDisplayString(_ctx.translate('COM_MEDIA_CREATE_NEW_FOLDER')), 1 /* TEXT */)
        ]),
        body: withCtx(() => [
          createBaseVNode("div", _hoisted_2$5, [
            createBaseVNode("form", {
              class: "form",
              novalidate: "",
              onSubmit: _cache[2] || (_cache[2] = withModifiers((...args) => ($options.save && $options.save(...args)), ["prevent"]))
            }, [
              createBaseVNode("div", _hoisted_3$5, [
                createBaseVNode("label", _hoisted_4$3, toDisplayString(_ctx.translate('COM_MEDIA_FOLDER_NAME')), 1 /* TEXT */),
                withDirectives(createBaseVNode("input", {
                  id: "folder",
                  ref: "input",
                  "onUpdate:modelValue": _cache[0] || (_cache[0] = $event => (($data.folder) = $event)),
                  class: normalizeClass(["form-control", ($options.isValidName()!==0 && $options.isValid())?'is-invalid':'']),
                  type: "text",
                  required: "",
                  autocomplete: "off",
                  "aria-describedby": "folderFeedback",
                  onInput: _cache[1] || (_cache[1] = $event => ($data.folder = $event.target.value))
                }, null, 34 /* CLASS, NEED_HYDRATION */), [
                  [
                    vModelText,
                    $data.folder,
                    void 0,
                    { trim: true }
                  ]
                ]),
                ($options.isValidName()===1)
                  ? (openBlock(), createElementBlock("div", _hoisted_5$3, toDisplayString(_ctx.translate('COM_MEDIA_CREATE_NEW_FOLDER_RELATIVE_PATH_ERROR')), 1 /* TEXT */))
                  : createCommentVNode("v-if", true),
                ($options.isValidName()===2)
                  ? (openBlock(), createElementBlock("div", _hoisted_6$3, toDisplayString(_ctx.translate('COM_MEDIA_CREATE_NEW_FOLDER_EXISTING_FOLDER_ERROR')), 1 /* TEXT */))
                  : createCommentVNode("v-if", true),
                ($options.isValidName()===3 && $options.isValid())
                  ? (openBlock(), createElementBlock("div", _hoisted_7$2, toDisplayString(_ctx.translate('COM_MEDIA_CREATE_NEW_FOLDER_UNEXPECTED_CHARACTER')), 1 /* TEXT */))
                  : createCommentVNode("v-if", true)
              ])
            ], 32 /* NEED_HYDRATION */)
          ])
        ]),
        footer: withCtx(() => [
          createBaseVNode("div", null, [
            createBaseVNode("button", {
              class: "btn btn-secondary",
              onClick: _cache[3] || (_cache[3] = $event => ($options.close()))
            }, toDisplayString(_ctx.translate('JCANCEL')), 1 /* TEXT */),
            createBaseVNode("button", {
              class: "btn btn-success",
              disabled: !$options.isValid() || $options.isValidName()!==0,
              onClick: _cache[4] || (_cache[4] = $event => ($options.save()))
            }, toDisplayString(_ctx.translate('JACTION_CREATE')), 9 /* TEXT, PROPS */, _hoisted_8)
          ])
        ]),
        _: 1 /* STABLE */
      }))
    : createCommentVNode("v-if", true)
}

script$5.render = render$5;
script$5.__file = "administrator/components/com_media/resources/scripts/components/modals/create-folder-modal.vue";

var script$4 = {
  name: 'MediaPreviewModal',
  components: {
    MediaModal: script$6,
  },
  computed: {
    /* Get the item to show in the modal */
    item() {
      // Use the currently selected directory as a fallback
      return this.$store.state.selectedItem ? this.$store.state.selectedItem : this.$store.state.previewItem;
    },
    /* Get the hashed URL */
    getHashedURL() {
      if (this.item.adapter.startsWith('local-')) {
        return `${this.item.url}?${api.mediaVersion}`;
      }
      return this.item.url;
    },
    style() {
      return (this.item.mime_type !== 'image/svg+xml') ? null : 'width: clamp(300px, 1000px, 75vw)';
    },
  },
  methods: {
    /* Close the modal */
    close() {
      this.$store.commit(HIDE_PREVIEW_MODAL);
    },
    isImage() {
      return this.item.mime_type.indexOf('image/') === 0;
    },
    isVideo() {
      return this.item.mime_type.indexOf('video/') === 0;
    },
    isAudio() {
      return this.item.mime_type.indexOf('audio/') === 0;
    },
    isDoc() {
      return this.item.mime_type.indexOf('application/') === 0;
    },
  },
};

const _hoisted_1$4 = {
  id: "previewTitle",
  class: "modal-title text-light"
};
const _hoisted_2$4 = { class: "image-background" };
const _hoisted_3$4 = ["src"];
const _hoisted_4$2 = {
  key: 1,
  controls: ""
};
const _hoisted_5$2 = ["src", "type"];
const _hoisted_6$2 = ["type", "data"];
const _hoisted_7$1 = ["src", "type"];

function render$4(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_MediaModal = resolveComponent("MediaModal");

  return (_ctx.$store.state.showPreviewModal && $options.item)
    ? (openBlock(), createBlock(_component_MediaModal, {
        key: 0,
        size: 'md',
        class: "media-preview-modal",
        "label-element": "previewTitle",
        "show-close": false,
        onClose: _cache[1] || (_cache[1] = $event => ($options.close()))
      }, {
        header: withCtx(() => [
          createBaseVNode("h3", _hoisted_1$4, toDisplayString($options.item.name), 1 /* TEXT */)
        ]),
        body: withCtx(() => [
          createBaseVNode("div", _hoisted_2$4, [
            ($options.isAudio())
              ? (openBlock(), createElementBlock("audio", {
                  key: 0,
                  controls: "",
                  src: $options.item.url
                }, null, 8 /* PROPS */, _hoisted_3$4))
              : createCommentVNode("v-if", true),
            ($options.isVideo())
              ? (openBlock(), createElementBlock("video", _hoisted_4$2, [
                  createBaseVNode("source", {
                    src: $options.item.url,
                    type: $options.item.mime_type
                  }, null, 8 /* PROPS */, _hoisted_5$2)
                ]))
              : createCommentVNode("v-if", true),
            ($options.isDoc())
              ? (openBlock(), createElementBlock("object", {
                  key: 2,
                  type: $options.item.mime_type,
                  data: $options.item.url,
                  width: "800",
                  height: "600"
                }, null, 8 /* PROPS */, _hoisted_6$2))
              : createCommentVNode("v-if", true),
            ($options.isImage())
              ? (openBlock(), createElementBlock("img", {
                  key: 3,
                  src: $options.getHashedURL,
                  type: $options.item.mime_type,
                  style: normalizeStyle($options.style)
                }, null, 12 /* STYLE, PROPS */, _hoisted_7$1))
              : createCommentVNode("v-if", true)
          ])
        ]),
        "backdrop-close": withCtx(() => [
          createBaseVNode("button", {
            type: "button",
            class: "media-preview-close",
            onClick: _cache[0] || (_cache[0] = $event => ($options.close()))
          }, [...(_cache[2] || (_cache[2] = [
            createBaseVNode("span", { class: "icon-times" }, null, -1 /* CACHED */)
          ]))])
        ]),
        _: 1 /* STABLE */
      }))
    : createCommentVNode("v-if", true)
}

script$4.render = render$4;
script$4.__file = "administrator/components/com_media/resources/scripts/components/modals/preview-modal.vue";

var script$3 = {
  name: 'MediaRenameModal',
  components: {
    MediaModal: script$6,
  },
  computed: {
    item() {
      return this.$store.state.selectedItems[this.$store.state.selectedItems.length - 1];
    },
    name() {
      return this.item.name.replace(`.${this.item.extension}`, '');
    },
    extension() {
      return this.item.extension;
    },
  },
  updated() {
    this.$nextTick(() => (this.$refs.nameField ? this.$refs.nameField.focus() : null));
  },
  methods: {
    /* Check if the form is valid */
    isValid() {
      return this.item.name.length > 0;
    },
    /* Close the modal instance */
    close() {
      this.$store.commit(HIDE_RENAME_MODAL);
    },
    /* Save the form and create the folder */
    save() {
      // Check if the form is valid
      if (!this.isValid()) {
        // @todo mark the field as invalid
        return;
      }
      let newName = this.$refs.nameField.value;
      if (this.extension.length) {
        newName += `.${this.item.extension}`;
      }

      let newPath = this.item.directory;
      if (newPath.substr(-1) !== '/') {
        newPath += '/';
      }

      // Rename the item
      this.$store.dispatch('renameItem', {
        item: this.item,
        newPath: newPath + newName,
        newName,
      });
    },
  },
};

const _hoisted_1$3 = {
  id: "renameTitle",
  class: "modal-title"
};
const _hoisted_2$3 = { class: "form-group p-3" };
const _hoisted_3$3 = { for: "name" };
const _hoisted_4$1 = ["placeholder", "value"];
const _hoisted_5$1 = {
  key: 0,
  class: "input-group-text"
};
const _hoisted_6$1 = ["disabled"];

function render$3(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_MediaModal = resolveComponent("MediaModal");

  return (_ctx.$store.state.showRenameModal)
    ? (openBlock(), createBlock(_component_MediaModal, {
        key: 0,
        size: 'sm',
        "show-close": false,
        "label-element": "renameTitle",
        onClose: _cache[5] || (_cache[5] = $event => ($options.close()))
      }, {
        header: withCtx(() => [
          createBaseVNode("h3", _hoisted_1$3, toDisplayString(_ctx.translate('COM_MEDIA_RENAME')), 1 /* TEXT */)
        ]),
        body: withCtx(() => [
          createBaseVNode("div", null, [
            createBaseVNode("form", {
              class: "form",
              novalidate: "",
              onSubmit: _cache[0] || (_cache[0] = withModifiers((...args) => ($options.save && $options.save(...args)), ["prevent"]))
            }, [
              createBaseVNode("div", _hoisted_2$3, [
                createBaseVNode("label", _hoisted_3$3, toDisplayString(_ctx.translate('COM_MEDIA_NAME')), 1 /* TEXT */),
                createBaseVNode("div", {
                  class: normalizeClass({'input-group': $options.extension.length})
                }, [
                  createBaseVNode("input", {
                    id: "name",
                    ref: "nameField",
                    class: "form-control",
                    type: "text",
                    placeholder: _ctx.translate('COM_MEDIA_NAME'),
                    value: $options.name,
                    required: "",
                    autocomplete: "off"
                  }, null, 8 /* PROPS */, _hoisted_4$1),
                  ($options.extension.length)
                    ? (openBlock(), createElementBlock("span", _hoisted_5$1, toDisplayString($options.extension), 1 /* TEXT */))
                    : createCommentVNode("v-if", true)
                ], 2 /* CLASS */)
              ])
            ], 32 /* NEED_HYDRATION */)
          ])
        ]),
        footer: withCtx(() => [
          createBaseVNode("div", null, [
            createBaseVNode("button", {
              type: "button",
              class: "btn btn-secondary",
              onClick: _cache[1] || (_cache[1] = $event => ($options.close())),
              onKeyup: _cache[2] || (_cache[2] = withKeys($event => ($options.close()), ["enter"]))
            }, toDisplayString(_ctx.translate('JCANCEL')), 33 /* TEXT, NEED_HYDRATION */),
            createBaseVNode("button", {
              type: "button",
              class: "btn btn-success",
              disabled: !$options.isValid(),
              onClick: _cache[3] || (_cache[3] = $event => ($options.save())),
              onKeyup: _cache[4] || (_cache[4] = withKeys($event => ($options.save()), ["enter"]))
            }, toDisplayString(_ctx.translate('JAPPLY')), 41 /* TEXT, PROPS, NEED_HYDRATION */, _hoisted_6$1)
          ])
        ]),
        _: 1 /* STABLE */
      }))
    : createCommentVNode("v-if", true)
}

script$3.render = render$3;
script$3.__file = "administrator/components/com_media/resources/scripts/components/modals/rename-modal.vue";

/**
 * Translate plugin
 */

const Translate = {
  // Translate from Joomla text
  translate: key => Joomla.Text._(key, key),
  sprintf: function sprintf(string) {
    for (var _len = arguments.length, args = new Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
      args[_key - 1] = arguments[_key];
    }
    const newString = Translate.translate(string);
    let i = 0;
    return newString.replace(/%((%)|s|d)/g, m => {
      let val = args[i];
      if (m === '%d') {
        val = parseFloat(val);
        if (Number.isNaN(val)) {
          val = 0;
        }
      }
      i += 1;
      return val;
    });
  },
  install: Vue => Vue.mixin({
    methods: {
      translate(key) {
        return Translate.translate(key);
      },
      sprintf(key) {
        for (var _len2 = arguments.length, args = new Array(_len2 > 1 ? _len2 - 1 : 0), _key2 = 1; _key2 < _len2; _key2++) {
          args[_key2 - 1] = arguments[_key2];
        }
        return Translate.sprintf(key, args);
      }
    }
  })
};

var script$2 = {
  name: 'MediaShareModal',
  components: {
    MediaModal: script$6,
  },
  computed: {
    item() {
      return this.$store.state.selectedItems[this.$store.state.selectedItems.length - 1];
    },

    url() {
      return (this.$store.state.previewItem && Object.prototype.hasOwnProperty.call(this.$store.state.previewItem, 'url') ? this.$store.state.previewItem.url : null);
    },
  },
  methods: {
    /* Close the modal instance and reset the form */
    close() {
      this.$store.commit(HIDE_SHARE_MODAL);
      this.$store.commit(LOAD_FULL_CONTENTS_SUCCESS, null);
    },

    // Generate the url from backend
    generateUrl() {
      this.$store.dispatch('getFullContents', this.item);
    },

    // Copy to clipboard
    copyToClipboard() {
      this.$refs.urlText.focus();
      this.$refs.urlText.select();

      try {
        document.execCommand('copy');
      } catch (err) {
        // @todo Error handling in joomla way
        window.alert(Translate('COM_MEDIA_SHARE_COPY_FAILED_ERROR'));
      }
    },
  },
};

const _hoisted_1$2 = {
  id: "shareTitle",
  class: "modal-title"
};
const _hoisted_2$2 = { class: "p-3" };
const _hoisted_3$2 = { class: "desc" };
const _hoisted_4 = {
  key: 0,
  class: "control"
};
const _hoisted_5 = {
  key: 1,
  class: "control"
};
const _hoisted_6 = { class: "input-group" };
const _hoisted_7 = ["title"];

function render$2(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_MediaModal = resolveComponent("MediaModal");

  return (_ctx.$store.state.showShareModal)
    ? (openBlock(), createBlock(_component_MediaModal, {
        key: 0,
        size: 'md',
        "show-close": false,
        "label-element": "shareTitle",
        onClose: _cache[4] || (_cache[4] = $event => ($options.close()))
      }, {
        header: withCtx(() => [
          createBaseVNode("h3", _hoisted_1$2, toDisplayString(_ctx.translate('COM_MEDIA_SHARE')), 1 /* TEXT */)
        ]),
        body: withCtx(() => [
          createBaseVNode("div", _hoisted_2$2, [
            createBaseVNode("div", _hoisted_3$2, [
              createTextVNode(toDisplayString(_ctx.translate('COM_MEDIA_SHARE_DESC')) + " ", 1 /* TEXT */),
              (!$options.url)
                ? (openBlock(), createElementBlock("div", _hoisted_4, [
                    createBaseVNode("button", {
                      class: "btn btn-success w-100",
                      type: "button",
                      onClick: _cache[0] || (_cache[0] = (...args) => ($options.generateUrl && $options.generateUrl(...args)))
                    }, toDisplayString(_ctx.translate('COM_MEDIA_ACTION_SHARE')), 1 /* TEXT */)
                  ]))
                : (openBlock(), createElementBlock("div", _hoisted_5, [
                    createBaseVNode("span", _hoisted_6, [
                      withDirectives(createBaseVNode("input", {
                        id: "url",
                        ref: "urlText",
                        "onUpdate:modelValue": _cache[1] || (_cache[1] = $event => (($options.url) = $event)),
                        readonly: "",
                        type: "url",
                        class: "form-control input-xxlarge",
                        placeholder: "URL",
                        autocomplete: "off"
                      }, null, 512 /* NEED_PATCH */), [
                        [vModelText, $options.url]
                      ]),
                      createBaseVNode("button", {
                        class: "btn btn-secondary",
                        type: "button",
                        title: _ctx.translate('COM_MEDIA_SHARE_COPY'),
                        onClick: _cache[2] || (_cache[2] = (...args) => ($options.copyToClipboard && $options.copyToClipboard(...args)))
                      }, [...(_cache[5] || (_cache[5] = [
                        createBaseVNode("span", {
                          class: "icon-clipboard",
                          "aria-hidden": "true"
                        }, null, -1 /* CACHED */)
                      ]))], 8 /* PROPS */, _hoisted_7)
                    ])
                  ]))
            ])
          ])
        ]),
        footer: withCtx(() => [
          createBaseVNode("div", null, [
            createBaseVNode("button", {
              class: "btn btn-secondary",
              onClick: _cache[3] || (_cache[3] = $event => ($options.close()))
            }, toDisplayString(_ctx.translate('JCANCEL')), 1 /* TEXT */)
          ])
        ]),
        _: 1 /* STABLE */
      }))
    : createCommentVNode("v-if", true)
}

script$2.render = render$2;
script$2.__file = "administrator/components/com_media/resources/scripts/components/modals/share-modal.vue";

var script$1 = {
  name: 'MediaShareModal',
  components: {
    MediaModal: script$6,
  },
  computed: {
    item() {
      return this.$store.state.selectedItems[this.$store.state.selectedItems.length - 1];
    },
  },
  methods: {
    /* Delete Item */
    deleteItem() {
      this.$store.dispatch('deleteSelectedItems');
      this.$store.commit(HIDE_CONFIRM_DELETE_MODAL);
    },
    /* Close the modal instance */
    close() {
      this.$store.commit(HIDE_CONFIRM_DELETE_MODAL);
    },
  },
};

const _hoisted_1$1 = {
  id: "confirmDeleteTitle",
  class: "modal-title"
};
const _hoisted_2$1 = { class: "p-3" };
const _hoisted_3$1 = { class: "desc" };

function render$1(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_MediaModal = resolveComponent("MediaModal");

  return (_ctx.$store.state.showConfirmDeleteModal)
    ? (openBlock(), createBlock(_component_MediaModal, {
        key: 0,
        size: 'md',
        "show-close": false,
        "label-element": "confirmDeleteTitle",
        onClose: _cache[2] || (_cache[2] = $event => ($options.close()))
      }, {
        header: withCtx(() => [
          createBaseVNode("h3", _hoisted_1$1, toDisplayString(_ctx.translate('COM_MEDIA_CONFIRM_DELETE_MODAL_HEADING')), 1 /* TEXT */)
        ]),
        body: withCtx(() => [
          createBaseVNode("div", _hoisted_2$1, [
            createBaseVNode("div", _hoisted_3$1, toDisplayString(_ctx.translate('COM_MEDIA_DELETE')), 1 /* TEXT */)
          ])
        ]),
        footer: withCtx(() => [
          createBaseVNode("div", null, [
            createBaseVNode("button", {
              class: "btn btn-success",
              onClick: _cache[0] || (_cache[0] = $event => ($options.close()))
            }, toDisplayString(_ctx.translate('JCANCEL')), 1 /* TEXT */),
            createBaseVNode("button", {
              id: "media-delete-item",
              class: "btn btn-danger",
              onClick: _cache[1] || (_cache[1] = $event => ($options.deleteItem()))
            }, toDisplayString(_ctx.translate('COM_MEDIA_CONFIRM_DELETE_MODAL')), 1 /* TEXT */)
          ])
        ]),
        _: 1 /* STABLE */
      }))
    : createCommentVNode("v-if", true)
}

script$1.render = render$1;
script$1.__file = "administrator/components/com_media/resources/scripts/components/modals/confirm-delete-modal.vue";

var script = {
  name: 'MediaApp',
  components: {
    MediaBrowser: script$e,
    MediaDisk: script$b,
    MediaToolbar: script$9,
    MediaUpload: script$8,
    MediaCreateFolderModal: script$5,
    MediaPreviewModal: script$4,
    MediaRenameModal: script$3,
    MediaShareModal: script$2,
    MediaConfirmDeleteModal: script$1,
  },
  data() {
    return {
      // The full height of the app in px
      fullHeight: '',
    };
  },
  computed: {
    disks() {
      return this.$store.state.disks;
    },
  },
  created() {
    // Listen to the toolbar events
    MediaManager.Event.listen('onClickCreateFolder', () => this.$store.commit(SHOW_CREATE_FOLDER_MODAL));
    MediaManager.Event.listen('onClickDelete', () => {
      if (this.$store.state.selectedItems.length > 0) {
        this.$store.commit(SHOW_CONFIRM_DELETE_MODAL);
      } else {
        notifications.error('COM_MEDIA_PLEASE_SELECT_ITEM');
      }
    });
  },
  mounted() {
    // Set the full height and add event listener when dom is updated
    this.$nextTick(() => {
      this.setFullHeight();
      // Add the global resize event listener
      window.addEventListener('resize', this.setFullHeight);
    });

    // Initial load the data
    this.$store.dispatch('getContents', this.$store.state.selectedDirectory, false, false);
  },
  beforeUnmount() {
    // Remove the global resize event listener
    window.removeEventListener('resize', this.setFullHeight);
  },
  methods: {
    /* Set the full height on the app container */
    setFullHeight() {
      this.fullHeight = `${window.innerHeight - this.$el.getBoundingClientRect().top}px`;
    },
  },
};

const _hoisted_1 = { class: "media-container" };
const _hoisted_2 = { class: "media-sidebar" };
const _hoisted_3 = { class: "media-main" };

function render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_MediaDisk = resolveComponent("MediaDisk");
  const _component_MediaToolbar = resolveComponent("MediaToolbar");
  const _component_MediaBrowser = resolveComponent("MediaBrowser");
  const _component_MediaUpload = resolveComponent("MediaUpload");
  const _component_MediaCreateFolderModal = resolveComponent("MediaCreateFolderModal");
  const _component_MediaPreviewModal = resolveComponent("MediaPreviewModal");
  const _component_MediaRenameModal = resolveComponent("MediaRenameModal");
  const _component_MediaShareModal = resolveComponent("MediaShareModal");
  const _component_MediaConfirmDeleteModal = resolveComponent("MediaConfirmDeleteModal");

  return (openBlock(), createElementBlock("div", _hoisted_1, [
    createBaseVNode("div", _hoisted_2, [
      (openBlock(true), createElementBlock(Fragment, null, renderList($options.disks, (disk, index) => {
        return (openBlock(), createBlock(_component_MediaDisk, {
          key: index.toString(),
          uid: index.toString(),
          disk: disk
        }, null, 8 /* PROPS */, ["uid", "disk"]))
      }), 128 /* KEYED_FRAGMENT */))
    ]),
    createBaseVNode("div", _hoisted_3, [
      createVNode(_component_MediaToolbar),
      createVNode(_component_MediaBrowser)
    ]),
    createVNode(_component_MediaUpload),
    createVNode(_component_MediaCreateFolderModal),
    createVNode(_component_MediaPreviewModal),
    createVNode(_component_MediaRenameModal),
    createVNode(_component_MediaShareModal),
    createVNode(_component_MediaConfirmDeleteModal)
  ]))
}

script.render = render;
script.__file = "administrator/components/com_media/resources/scripts/components/app.vue";

/**
 * Media Event bus - used for communication between joomla and vue
 */
let Event$1 = class Event {
  /**
     * Media Event constructor
     */
  constructor() {
    this.events = {};
  }

  /**
     * Fire an event
     * @param event
     * @param data
     */
  fire(event, data) {
    if (data === void 0) {
      data = null;
    }
    if (this.events[event]) {
      this.events[event].forEach(fn => fn(data));
    }
  }

  /**
     * Listen to events
     * @param event
     * @param callback
     */
  listen(event, callback) {
    this.events[event] = this.events[event] || [];
    this.events[event].push(callback);
  }
};

function getDevtoolsGlobalHook() {
  return getTarget().__VUE_DEVTOOLS_GLOBAL_HOOK__;
}
function getTarget() {
  // @ts-expect-error navigator and windows are not available in all environments
  return typeof navigator !== 'undefined' && typeof window !== 'undefined' ? window : typeof globalThis !== 'undefined' ? globalThis : {};
}
const isProxyAvailable = typeof Proxy === 'function';

const HOOK_SETUP = 'devtools-plugin:setup';
const HOOK_PLUGIN_SETTINGS_SET = 'plugin:settings:set';

let supported;
let perf;
function isPerformanceSupported() {
  var _a;
  if (supported !== undefined) {
    return supported;
  }
  if (typeof window !== 'undefined' && window.performance) {
    supported = true;
    perf = window.performance;
  } else if (typeof globalThis !== 'undefined' && ((_a = globalThis.perf_hooks) === null || _a === void 0 ? void 0 : _a.performance)) {
    supported = true;
    perf = globalThis.perf_hooks.performance;
  } else {
    supported = false;
  }
  return supported;
}
function now() {
  return isPerformanceSupported() ? perf.now() : Date.now();
}

class ApiProxy {
  constructor(plugin, hook) {
    var _this = this;
    this.target = null;
    this.targetQueue = [];
    this.onQueue = [];
    this.plugin = plugin;
    this.hook = hook;
    const defaultSettings = {};
    if (plugin.settings) {
      for (const id in plugin.settings) {
        const item = plugin.settings[id];
        defaultSettings[id] = item.defaultValue;
      }
    }
    const localSettingsSaveId = "__vue-devtools-plugin-settings__" + plugin.id;
    let currentSettings = Object.assign({}, defaultSettings);
    try {
      const raw = localStorage.getItem(localSettingsSaveId);
      const data = JSON.parse(raw);
      Object.assign(currentSettings, data);
    } catch (e) {
      // noop
    }
    this.fallbacks = {
      getSettings() {
        return currentSettings;
      },
      setSettings(value) {
        try {
          localStorage.setItem(localSettingsSaveId, JSON.stringify(value));
        } catch (e) {
          // noop
        }
        currentSettings = value;
      },
      now() {
        return now();
      }
    };
    if (hook) {
      hook.on(HOOK_PLUGIN_SETTINGS_SET, (pluginId, value) => {
        if (pluginId === this.plugin.id) {
          this.fallbacks.setSettings(value);
        }
      });
    }
    this.proxiedOn = new Proxy({}, {
      get: (_target, prop) => {
        if (this.target) {
          return this.target.on[prop];
        } else {
          return function () {
            for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
              args[_key] = arguments[_key];
            }
            _this.onQueue.push({
              method: prop,
              args
            });
          };
        }
      }
    });
    this.proxiedTarget = new Proxy({}, {
      get: (_target, prop) => {
        if (this.target) {
          return this.target[prop];
        } else if (prop === 'on') {
          return this.proxiedOn;
        } else if (Object.keys(this.fallbacks).includes(prop)) {
          return function () {
            for (var _len2 = arguments.length, args = new Array(_len2), _key2 = 0; _key2 < _len2; _key2++) {
              args[_key2] = arguments[_key2];
            }
            _this.targetQueue.push({
              method: prop,
              args,
              resolve: () => {}
            });
            return _this.fallbacks[prop](...args);
          };
        } else {
          return function () {
            for (var _len3 = arguments.length, args = new Array(_len3), _key3 = 0; _key3 < _len3; _key3++) {
              args[_key3] = arguments[_key3];
            }
            return new Promise(resolve => {
              _this.targetQueue.push({
                method: prop,
                args,
                resolve
              });
            });
          };
        }
      }
    });
  }
  async setRealTarget(target) {
    this.target = target;
    for (const item of this.onQueue) {
      this.target.on[item.method](...item.args);
    }
    for (const item of this.targetQueue) {
      item.resolve(await this.target[item.method](...item.args));
    }
  }
}

function setupDevtoolsPlugin(pluginDescriptor, setupFn) {
  const descriptor = pluginDescriptor;
  const target = getTarget();
  const hook = getDevtoolsGlobalHook();
  const enableProxy = isProxyAvailable && descriptor.enableEarlyProxy;
  if (hook && (target.__VUE_DEVTOOLS_PLUGIN_API_AVAILABLE__ || !enableProxy)) {
    hook.emit(HOOK_SETUP, pluginDescriptor, setupFn);
  } else {
    const proxy = enableProxy ? new ApiProxy(descriptor, hook) : null;
    const list = target.__VUE_DEVTOOLS_PLUGINS__ = target.__VUE_DEVTOOLS_PLUGINS__ || [];
    list.push({
      pluginDescriptor: descriptor,
      setupFn,
      proxy
    });
    if (proxy) {
      setupFn(proxy.proxiedTarget);
    }
  }
}

/*!
 * vuex v4.1.0
 * (c) 2022 Evan You
 * @license MIT
 */
var storeKey = 'store';

/**
 * forEach for object
 */
function forEachValue(obj, fn) {
  Object.keys(obj).forEach(function (key) {
    return fn(obj[key], key);
  });
}
function isObject(obj) {
  return obj !== null && typeof obj === 'object';
}
function isPromise(val) {
  return val && typeof val.then === 'function';
}
function partial(fn, arg) {
  return function () {
    return fn(arg);
  };
}
function genericSubscribe(fn, subs, options) {
  if (subs.indexOf(fn) < 0) {
    options && options.prepend ? subs.unshift(fn) : subs.push(fn);
  }
  return function () {
    var i = subs.indexOf(fn);
    if (i > -1) {
      subs.splice(i, 1);
    }
  };
}
function resetStore(store, hot) {
  store._actions = Object.create(null);
  store._mutations = Object.create(null);
  store._wrappedGetters = Object.create(null);
  store._modulesNamespaceMap = Object.create(null);
  var state = store.state;
  // init all modules
  installModule(store, state, [], store._modules.root, true);
  // reset state
  resetStoreState(store, state, hot);
}
function resetStoreState(store, state, hot) {
  var oldState = store._state;
  var oldScope = store._scope;

  // bind store public getters
  store.getters = {};
  // reset local getters cache
  store._makeLocalGettersCache = Object.create(null);
  var wrappedGetters = store._wrappedGetters;
  var computedObj = {};
  var computedCache = {};

  // create a new effect scope and create computed object inside it to avoid
  // getters (computed) getting destroyed on component unmount.
  var scope = effectScope(true);
  scope.run(function () {
    forEachValue(wrappedGetters, function (fn, key) {
      // use computed to leverage its lazy-caching mechanism
      // direct inline function use will lead to closure preserving oldState.
      // using partial to return function with only arguments preserved in closure environment.
      computedObj[key] = partial(fn, store);
      computedCache[key] = computed(function () {
        return computedObj[key]();
      });
      Object.defineProperty(store.getters, key, {
        get: function get() {
          return computedCache[key].value;
        },
        enumerable: true // for local getters
      });
    });
  });
  store._state = reactive({
    data: state
  });

  // register the newly created effect scope to the store so that we can
  // dispose the effects when this method runs again in the future.
  store._scope = scope;

  // enable strict mode for new state
  if (store.strict) {
    enableStrictMode(store);
  }
  if (oldState) {
    if (hot) {
      // dispatch changes in all subscribed watchers
      // to force getter re-evaluation for hot reloading.
      store._withCommit(function () {
        oldState.data = null;
      });
    }
  }

  // dispose previously registered effect scope if there is one.
  if (oldScope) {
    oldScope.stop();
  }
}
function installModule(store, rootState, path, module, hot) {
  var isRoot = !path.length;
  var namespace = store._modules.getNamespace(path);

  // register in namespace map
  if (module.namespaced) {
    if (store._modulesNamespaceMap[namespace] && "production" !== 'production') ;
    store._modulesNamespaceMap[namespace] = module;
  }

  // set state
  if (!isRoot && !hot) {
    var parentState = getNestedState(rootState, path.slice(0, -1));
    var moduleName = path[path.length - 1];
    store._withCommit(function () {
      parentState[moduleName] = module.state;
    });
  }
  var local = module.context = makeLocalContext(store, namespace, path);
  module.forEachMutation(function (mutation, key) {
    var namespacedType = namespace + key;
    registerMutation(store, namespacedType, mutation, local);
  });
  module.forEachAction(function (action, key) {
    var type = action.root ? key : namespace + key;
    var handler = action.handler || action;
    registerAction(store, type, handler, local);
  });
  module.forEachGetter(function (getter, key) {
    var namespacedType = namespace + key;
    registerGetter(store, namespacedType, getter, local);
  });
  module.forEachChild(function (child, key) {
    installModule(store, rootState, path.concat(key), child, hot);
  });
}

/**
 * make localized dispatch, commit, getters and state
 * if there is no namespace, just use root ones
 */
function makeLocalContext(store, namespace, path) {
  var noNamespace = namespace === '';
  var local = {
    dispatch: noNamespace ? store.dispatch : function (_type, _payload, _options) {
      var args = unifyObjectStyle(_type, _payload, _options);
      var payload = args.payload;
      var options = args.options;
      var type = args.type;
      if (!options || !options.root) {
        type = namespace + type;
      }
      return store.dispatch(type, payload);
    },
    commit: noNamespace ? store.commit : function (_type, _payload, _options) {
      var args = unifyObjectStyle(_type, _payload, _options);
      var payload = args.payload;
      var options = args.options;
      var type = args.type;
      if (!options || !options.root) {
        type = namespace + type;
      }
      store.commit(type, payload, options);
    }
  };

  // getters and state object must be gotten lazily
  // because they will be changed by state update
  Object.defineProperties(local, {
    getters: {
      get: noNamespace ? function () {
        return store.getters;
      } : function () {
        return makeLocalGetters(store, namespace);
      }
    },
    state: {
      get: function get() {
        return getNestedState(store.state, path);
      }
    }
  });
  return local;
}
function makeLocalGetters(store, namespace) {
  if (!store._makeLocalGettersCache[namespace]) {
    var gettersProxy = {};
    var splitPos = namespace.length;
    Object.keys(store.getters).forEach(function (type) {
      // skip if the target getter is not match this namespace
      if (type.slice(0, splitPos) !== namespace) {
        return;
      }

      // extract local getter type
      var localType = type.slice(splitPos);

      // Add a port to the getters proxy.
      // Define as getter property because
      // we do not want to evaluate the getters in this time.
      Object.defineProperty(gettersProxy, localType, {
        get: function get() {
          return store.getters[type];
        },
        enumerable: true
      });
    });
    store._makeLocalGettersCache[namespace] = gettersProxy;
  }
  return store._makeLocalGettersCache[namespace];
}
function registerMutation(store, type, handler, local) {
  var entry = store._mutations[type] || (store._mutations[type] = []);
  entry.push(function wrappedMutationHandler(payload) {
    handler.call(store, local.state, payload);
  });
}
function registerAction(store, type, handler, local) {
  var entry = store._actions[type] || (store._actions[type] = []);
  entry.push(function wrappedActionHandler(payload) {
    var res = handler.call(store, {
      dispatch: local.dispatch,
      commit: local.commit,
      getters: local.getters,
      state: local.state,
      rootGetters: store.getters,
      rootState: store.state
    }, payload);
    if (!isPromise(res)) {
      res = Promise.resolve(res);
    }
    if (store._devtoolHook) {
      return res.catch(function (err) {
        store._devtoolHook.emit('vuex:error', err);
        throw err;
      });
    } else {
      return res;
    }
  });
}
function registerGetter(store, type, rawGetter, local) {
  if (store._wrappedGetters[type]) {
    return;
  }
  store._wrappedGetters[type] = function wrappedGetter(store) {
    return rawGetter(local.state,
    // local state
    local.getters,
    // local getters
    store.state,
    // root state
    store.getters // root getters
    );
  };
}
function enableStrictMode(store) {
  watch(function () {
    return store._state.data;
  }, function () {
  }, {
    deep: true,
    flush: 'sync'
  });
}
function getNestedState(state, path) {
  return path.reduce(function (state, key) {
    return state[key];
  }, state);
}
function unifyObjectStyle(type, payload, options) {
  if (isObject(type) && type.type) {
    options = payload;
    payload = type;
    type = type.type;
  }
  return {
    type: type,
    payload: payload,
    options: options
  };
}
var LABEL_VUEX_BINDINGS = 'vuex bindings';
var MUTATIONS_LAYER_ID = 'vuex:mutations';
var ACTIONS_LAYER_ID = 'vuex:actions';
var INSPECTOR_ID = 'vuex';
var actionId = 0;
function addDevtools(app, store) {
  setupDevtoolsPlugin({
    id: 'org.vuejs.vuex',
    app: app,
    label: 'Vuex',
    homepage: 'https://next.vuex.vuejs.org/',
    logo: 'https://vuejs.org/images/icons/favicon-96x96.png',
    packageName: 'vuex',
    componentStateTypes: [LABEL_VUEX_BINDINGS]
  }, function (api) {
    api.addTimelineLayer({
      id: MUTATIONS_LAYER_ID,
      label: 'Vuex Mutations',
      color: COLOR_LIME_500
    });
    api.addTimelineLayer({
      id: ACTIONS_LAYER_ID,
      label: 'Vuex Actions',
      color: COLOR_LIME_500
    });
    api.addInspector({
      id: INSPECTOR_ID,
      label: 'Vuex',
      icon: 'storage',
      treeFilterPlaceholder: 'Filter stores...'
    });
    api.on.getInspectorTree(function (payload) {
      if (payload.app === app && payload.inspectorId === INSPECTOR_ID) {
        if (payload.filter) {
          var nodes = [];
          flattenStoreForInspectorTree(nodes, store._modules.root, payload.filter, '');
          payload.rootNodes = nodes;
        } else {
          payload.rootNodes = [formatStoreForInspectorTree(store._modules.root, '')];
        }
      }
    });
    api.on.getInspectorState(function (payload) {
      if (payload.app === app && payload.inspectorId === INSPECTOR_ID) {
        var modulePath = payload.nodeId;
        makeLocalGetters(store, modulePath);
        payload.state = formatStoreForInspectorState(getStoreModule(store._modules, modulePath), modulePath === 'root' ? store.getters : store._makeLocalGettersCache, modulePath);
      }
    });
    api.on.editInspectorState(function (payload) {
      if (payload.app === app && payload.inspectorId === INSPECTOR_ID) {
        var modulePath = payload.nodeId;
        var path = payload.path;
        if (modulePath !== 'root') {
          path = modulePath.split('/').filter(Boolean).concat(path);
        }
        store._withCommit(function () {
          payload.set(store._state.data, path, payload.state.value);
        });
      }
    });
    store.subscribe(function (mutation, state) {
      var data = {};
      if (mutation.payload) {
        data.payload = mutation.payload;
      }
      data.state = state;
      api.notifyComponentUpdate();
      api.sendInspectorTree(INSPECTOR_ID);
      api.sendInspectorState(INSPECTOR_ID);
      api.addTimelineEvent({
        layerId: MUTATIONS_LAYER_ID,
        event: {
          time: Date.now(),
          title: mutation.type,
          data: data
        }
      });
    });
    store.subscribeAction({
      before: function before(action, state) {
        var data = {};
        if (action.payload) {
          data.payload = action.payload;
        }
        action._id = actionId++;
        action._time = Date.now();
        data.state = state;
        api.addTimelineEvent({
          layerId: ACTIONS_LAYER_ID,
          event: {
            time: action._time,
            title: action.type,
            groupId: action._id,
            subtitle: 'start',
            data: data
          }
        });
      },
      after: function after(action, state) {
        var data = {};
        var duration = Date.now() - action._time;
        data.duration = {
          _custom: {
            type: 'duration',
            display: duration + "ms",
            tooltip: 'Action duration',
            value: duration
          }
        };
        if (action.payload) {
          data.payload = action.payload;
        }
        data.state = state;
        api.addTimelineEvent({
          layerId: ACTIONS_LAYER_ID,
          event: {
            time: Date.now(),
            title: action.type,
            groupId: action._id,
            subtitle: 'end',
            data: data
          }
        });
      }
    });
  });
}

// extracted from tailwind palette
var COLOR_LIME_500 = 0x84cc16;
var COLOR_DARK = 0x666666;
var COLOR_WHITE = 0xffffff;
var TAG_NAMESPACED = {
  label: 'namespaced',
  textColor: COLOR_WHITE,
  backgroundColor: COLOR_DARK
};

/**
 * @param {string} path
 */
function extractNameFromPath(path) {
  return path && path !== 'root' ? path.split('/').slice(-2, -1)[0] : 'Root';
}

/**
 * @param {*} module
 * @return {import('@vue/devtools-api').CustomInspectorNode}
 */
function formatStoreForInspectorTree(module, path) {
  return {
    id: path || 'root',
    // all modules end with a `/`, we want the last segment only
    // cart/ -> cart
    // nested/cart/ -> cart
    label: extractNameFromPath(path),
    tags: module.namespaced ? [TAG_NAMESPACED] : [],
    children: Object.keys(module._children).map(function (moduleName) {
      return formatStoreForInspectorTree(module._children[moduleName], path + moduleName + '/');
    })
  };
}

/**
 * @param {import('@vue/devtools-api').CustomInspectorNode[]} result
 * @param {*} module
 * @param {string} filter
 * @param {string} path
 */
function flattenStoreForInspectorTree(result, module, filter, path) {
  if (path.includes(filter)) {
    result.push({
      id: path || 'root',
      label: path.endsWith('/') ? path.slice(0, path.length - 1) : path || 'Root',
      tags: module.namespaced ? [TAG_NAMESPACED] : []
    });
  }
  Object.keys(module._children).forEach(function (moduleName) {
    flattenStoreForInspectorTree(result, module._children[moduleName], filter, path + moduleName + '/');
  });
}

/**
 * @param {*} module
 * @return {import('@vue/devtools-api').CustomInspectorState}
 */
function formatStoreForInspectorState(module, getters, path) {
  getters = path === 'root' ? getters : getters[path];
  var gettersKeys = Object.keys(getters);
  var storeState = {
    state: Object.keys(module.state).map(function (key) {
      return {
        key: key,
        editable: true,
        value: module.state[key]
      };
    })
  };
  if (gettersKeys.length) {
    var tree = transformPathsToObjectTree(getters);
    storeState.getters = Object.keys(tree).map(function (key) {
      return {
        key: key.endsWith('/') ? extractNameFromPath(key) : key,
        editable: false,
        value: canThrow(function () {
          return tree[key];
        })
      };
    });
  }
  return storeState;
}
function transformPathsToObjectTree(getters) {
  var result = {};
  Object.keys(getters).forEach(function (key) {
    var path = key.split('/');
    if (path.length > 1) {
      var target = result;
      var leafKey = path.pop();
      path.forEach(function (p) {
        if (!target[p]) {
          target[p] = {
            _custom: {
              value: {},
              display: p,
              tooltip: 'Module',
              abstract: true
            }
          };
        }
        target = target[p]._custom.value;
      });
      target[leafKey] = canThrow(function () {
        return getters[key];
      });
    } else {
      result[key] = canThrow(function () {
        return getters[key];
      });
    }
  });
  return result;
}
function getStoreModule(moduleMap, path) {
  var names = path.split('/').filter(function (n) {
    return n;
  });
  return names.reduce(function (module, moduleName, i) {
    var child = module[moduleName];
    if (!child) {
      throw new Error("Missing module \"" + moduleName + "\" for path \"" + path + "\".");
    }
    return i === names.length - 1 ? child : child._children;
  }, path === 'root' ? moduleMap : moduleMap.root._children);
}
function canThrow(cb) {
  try {
    return cb();
  } catch (e) {
    return e;
  }
}

// Base data struct for store's module, package with some attribute and method
var Module = function Module(rawModule, runtime) {
  this.runtime = runtime;
  // Store some children item
  this._children = Object.create(null);
  // Store the origin module object which passed by programmer
  this._rawModule = rawModule;
  var rawState = rawModule.state;

  // Store the origin module's state
  this.state = (typeof rawState === 'function' ? rawState() : rawState) || {};
};
var prototypeAccessors$1 = {
  namespaced: {
    configurable: true
  }
};
prototypeAccessors$1.namespaced.get = function () {
  return !!this._rawModule.namespaced;
};
Module.prototype.addChild = function addChild(key, module) {
  this._children[key] = module;
};
Module.prototype.removeChild = function removeChild(key) {
  delete this._children[key];
};
Module.prototype.getChild = function getChild(key) {
  return this._children[key];
};
Module.prototype.hasChild = function hasChild(key) {
  return key in this._children;
};
Module.prototype.update = function update(rawModule) {
  this._rawModule.namespaced = rawModule.namespaced;
  if (rawModule.actions) {
    this._rawModule.actions = rawModule.actions;
  }
  if (rawModule.mutations) {
    this._rawModule.mutations = rawModule.mutations;
  }
  if (rawModule.getters) {
    this._rawModule.getters = rawModule.getters;
  }
};
Module.prototype.forEachChild = function forEachChild(fn) {
  forEachValue(this._children, fn);
};
Module.prototype.forEachGetter = function forEachGetter(fn) {
  if (this._rawModule.getters) {
    forEachValue(this._rawModule.getters, fn);
  }
};
Module.prototype.forEachAction = function forEachAction(fn) {
  if (this._rawModule.actions) {
    forEachValue(this._rawModule.actions, fn);
  }
};
Module.prototype.forEachMutation = function forEachMutation(fn) {
  if (this._rawModule.mutations) {
    forEachValue(this._rawModule.mutations, fn);
  }
};
Object.defineProperties(Module.prototype, prototypeAccessors$1);
var ModuleCollection = function ModuleCollection(rawRootModule) {
  // register root module (Vuex.Store options)
  this.register([], rawRootModule, false);
};
ModuleCollection.prototype.get = function get(path) {
  return path.reduce(function (module, key) {
    return module.getChild(key);
  }, this.root);
};
ModuleCollection.prototype.getNamespace = function getNamespace(path) {
  var module = this.root;
  return path.reduce(function (namespace, key) {
    module = module.getChild(key);
    return namespace + (module.namespaced ? key + '/' : '');
  }, '');
};
ModuleCollection.prototype.update = function update$1(rawRootModule) {
  update([], this.root, rawRootModule);
};
ModuleCollection.prototype.register = function register(path, rawModule, runtime) {
  var this$1$1 = this;
  if (runtime === void 0) runtime = true;
  var newModule = new Module(rawModule, runtime);
  if (path.length === 0) {
    this.root = newModule;
  } else {
    var parent = this.get(path.slice(0, -1));
    parent.addChild(path[path.length - 1], newModule);
  }

  // register nested modules
  if (rawModule.modules) {
    forEachValue(rawModule.modules, function (rawChildModule, key) {
      this$1$1.register(path.concat(key), rawChildModule, runtime);
    });
  }
};
ModuleCollection.prototype.unregister = function unregister(path) {
  var parent = this.get(path.slice(0, -1));
  var key = path[path.length - 1];
  var child = parent.getChild(key);
  if (!child) {
    return;
  }
  if (!child.runtime) {
    return;
  }
  parent.removeChild(key);
};
ModuleCollection.prototype.isRegistered = function isRegistered(path) {
  var parent = this.get(path.slice(0, -1));
  var key = path[path.length - 1];
  if (parent) {
    return parent.hasChild(key);
  }
  return false;
};
function update(path, targetModule, newModule) {

  // update target module
  targetModule.update(newModule);

  // update nested modules
  if (newModule.modules) {
    for (var key in newModule.modules) {
      if (!targetModule.getChild(key)) {
        return;
      }
      update(path.concat(key), targetModule.getChild(key), newModule.modules[key]);
    }
  }
}
function createStore(options) {
  return new Store(options);
}
var Store = function Store(options) {
  var this$1$1 = this;
  if (options === void 0) options = {};
  var plugins = options.plugins;
  if (plugins === void 0) plugins = [];
  var strict = options.strict;
  if (strict === void 0) strict = false;
  var devtools = options.devtools;

  // store internal state
  this._committing = false;
  this._actions = Object.create(null);
  this._actionSubscribers = [];
  this._mutations = Object.create(null);
  this._wrappedGetters = Object.create(null);
  this._modules = new ModuleCollection(options);
  this._modulesNamespaceMap = Object.create(null);
  this._subscribers = [];
  this._makeLocalGettersCache = Object.create(null);

  // EffectScope instance. when registering new getters, we wrap them inside
  // EffectScope so that getters (computed) would not be destroyed on
  // component unmount.
  this._scope = null;
  this._devtools = devtools;

  // bind commit and dispatch to self
  var store = this;
  var ref = this;
  var dispatch = ref.dispatch;
  var commit = ref.commit;
  this.dispatch = function boundDispatch(type, payload) {
    return dispatch.call(store, type, payload);
  };
  this.commit = function boundCommit(type, payload, options) {
    return commit.call(store, type, payload, options);
  };

  // strict mode
  this.strict = strict;
  var state = this._modules.root.state;

  // init root module.
  // this also recursively registers all sub-modules
  // and collects all module getters inside this._wrappedGetters
  installModule(this, state, [], this._modules.root);

  // initialize the store state, which is responsible for the reactivity
  // (also registers _wrappedGetters as computed properties)
  resetStoreState(this, state);

  // apply plugins
  plugins.forEach(function (plugin) {
    return plugin(this$1$1);
  });
};
var prototypeAccessors = {
  state: {
    configurable: true
  }
};
Store.prototype.install = function install(app, injectKey) {
  app.provide(injectKey || storeKey, this);
  app.config.globalProperties.$store = this;
  var useDevtools = this._devtools !== undefined ? this._devtools : false;
  if (useDevtools) {
    addDevtools(app, this);
  }
};
prototypeAccessors.state.get = function () {
  return this._state.data;
};
prototypeAccessors.state.set = function (v) {
};
Store.prototype.commit = function commit(_type, _payload, _options) {
  var this$1$1 = this;

  // check object-style commit
  var ref = unifyObjectStyle(_type, _payload, _options);
  var type = ref.type;
  var payload = ref.payload;
  var mutation = {
    type: type,
    payload: payload
  };
  var entry = this._mutations[type];
  if (!entry) {
    return;
  }
  this._withCommit(function () {
    entry.forEach(function commitIterator(handler) {
      handler(payload);
    });
  });
  this._subscribers.slice() // shallow copy to prevent iterator invalidation if subscriber synchronously calls unsubscribe
  .forEach(function (sub) {
    return sub(mutation, this$1$1.state);
  });
};
Store.prototype.dispatch = function dispatch(_type, _payload) {
  var this$1$1 = this;

  // check object-style dispatch
  var ref = unifyObjectStyle(_type, _payload);
  var type = ref.type;
  var payload = ref.payload;
  var action = {
    type: type,
    payload: payload
  };
  var entry = this._actions[type];
  if (!entry) {
    return;
  }
  try {
    this._actionSubscribers.slice() // shallow copy to prevent iterator invalidation if subscriber synchronously calls unsubscribe
    .filter(function (sub) {
      return sub.before;
    }).forEach(function (sub) {
      return sub.before(action, this$1$1.state);
    });
  } catch (e) {
  }
  var result = entry.length > 1 ? Promise.all(entry.map(function (handler) {
    return handler(payload);
  })) : entry[0](payload);
  return new Promise(function (resolve, reject) {
    result.then(function (res) {
      try {
        this$1$1._actionSubscribers.filter(function (sub) {
          return sub.after;
        }).forEach(function (sub) {
          return sub.after(action, this$1$1.state);
        });
      } catch (e) {
      }
      resolve(res);
    }, function (error) {
      try {
        this$1$1._actionSubscribers.filter(function (sub) {
          return sub.error;
        }).forEach(function (sub) {
          return sub.error(action, this$1$1.state, error);
        });
      } catch (e) {
      }
      reject(error);
    });
  });
};
Store.prototype.subscribe = function subscribe(fn, options) {
  return genericSubscribe(fn, this._subscribers, options);
};
Store.prototype.subscribeAction = function subscribeAction(fn, options) {
  var subs = typeof fn === 'function' ? {
    before: fn
  } : fn;
  return genericSubscribe(subs, this._actionSubscribers, options);
};
Store.prototype.watch = function watch$1(getter, cb, options) {
  var this$1$1 = this;
  return watch(function () {
    return getter(this$1$1.state, this$1$1.getters);
  }, cb, Object.assign({}, options));
};
Store.prototype.replaceState = function replaceState(state) {
  var this$1$1 = this;
  this._withCommit(function () {
    this$1$1._state.data = state;
  });
};
Store.prototype.registerModule = function registerModule(path, rawModule, options) {
  if (options === void 0) options = {};
  if (typeof path === 'string') {
    path = [path];
  }
  this._modules.register(path, rawModule);
  installModule(this, this.state, path, this._modules.get(path), options.preserveState);
  // reset store to update getters...
  resetStoreState(this, this.state);
};
Store.prototype.unregisterModule = function unregisterModule(path) {
  var this$1$1 = this;
  if (typeof path === 'string') {
    path = [path];
  }
  this._modules.unregister(path);
  this._withCommit(function () {
    var parentState = getNestedState(this$1$1.state, path.slice(0, -1));
    delete parentState[path[path.length - 1]];
  });
  resetStore(this);
};
Store.prototype.hasModule = function hasModule(path) {
  if (typeof path === 'string') {
    path = [path];
  }
  return this._modules.isRegistered(path);
};
Store.prototype.hotUpdate = function hotUpdate(newOptions) {
  this._modules.update(newOptions);
  resetStore(this, true);
};
Store.prototype._withCommit = function _withCommit(fn) {
  var committing = this._committing;
  this._committing = true;
  fn();
  this._committing = committing;
};
Object.defineProperties(Store.prototype, prototypeAccessors);

function getDefaultExportFromCjs (x) {
	return x && x.__esModule && Object.prototype.hasOwnProperty.call(x, 'default') ? x['default'] : x;
}

var cjs;
var hasRequiredCjs;
function requireCjs() {
  if (hasRequiredCjs) return cjs;
  hasRequiredCjs = 1;
  var isMergeableObject = function isMergeableObject(value) {
    return isNonNullObject(value) && !isSpecial(value);
  };
  function isNonNullObject(value) {
    return !!value && typeof value === 'object';
  }
  function isSpecial(value) {
    var stringValue = Object.prototype.toString.call(value);
    return stringValue === '[object RegExp]' || stringValue === '[object Date]' || isReactElement(value);
  }

  // see https://github.com/facebook/react/blob/b5ac963fb791d1298e7f396236383bc955f916c1/src/isomorphic/classic/element/ReactElement.js#L21-L25
  var canUseSymbol = typeof Symbol === 'function' && Symbol.for;
  var REACT_ELEMENT_TYPE = canUseSymbol ? Symbol.for('react.element') : 0xeac7;
  function isReactElement(value) {
    return value.$$typeof === REACT_ELEMENT_TYPE;
  }
  function emptyTarget(val) {
    return Array.isArray(val) ? [] : {};
  }
  function cloneUnlessOtherwiseSpecified(value, options) {
    return options.clone !== false && options.isMergeableObject(value) ? deepmerge(emptyTarget(value), value, options) : value;
  }
  function defaultArrayMerge(target, source, options) {
    return target.concat(source).map(function (element) {
      return cloneUnlessOtherwiseSpecified(element, options);
    });
  }
  function getMergeFunction(key, options) {
    if (!options.customMerge) {
      return deepmerge;
    }
    var customMerge = options.customMerge(key);
    return typeof customMerge === 'function' ? customMerge : deepmerge;
  }
  function getEnumerableOwnPropertySymbols(target) {
    return Object.getOwnPropertySymbols ? Object.getOwnPropertySymbols(target).filter(function (symbol) {
      return Object.propertyIsEnumerable.call(target, symbol);
    }) : [];
  }
  function getKeys(target) {
    return Object.keys(target).concat(getEnumerableOwnPropertySymbols(target));
  }
  function propertyIsOnObject(object, property) {
    try {
      return property in object;
    } catch (_) {
      return false;
    }
  }

  // Protects from prototype poisoning and unexpected merging up the prototype chain.
  function propertyIsUnsafe(target, key) {
    return propertyIsOnObject(target, key) // Properties are safe to merge if they don't exist in the target yet,
    && !(Object.hasOwnProperty.call(target, key) // unsafe if they exist up the prototype chain,
    && Object.propertyIsEnumerable.call(target, key)); // and also unsafe if they're nonenumerable.
  }
  function mergeObject(target, source, options) {
    var destination = {};
    if (options.isMergeableObject(target)) {
      getKeys(target).forEach(function (key) {
        destination[key] = cloneUnlessOtherwiseSpecified(target[key], options);
      });
    }
    getKeys(source).forEach(function (key) {
      if (propertyIsUnsafe(target, key)) {
        return;
      }
      if (propertyIsOnObject(target, key) && options.isMergeableObject(source[key])) {
        destination[key] = getMergeFunction(key, options)(target[key], source[key], options);
      } else {
        destination[key] = cloneUnlessOtherwiseSpecified(source[key], options);
      }
    });
    return destination;
  }
  function deepmerge(target, source, options) {
    options = options || {};
    options.arrayMerge = options.arrayMerge || defaultArrayMerge;
    options.isMergeableObject = options.isMergeableObject || isMergeableObject;
    // cloneUnlessOtherwiseSpecified is added to `options` so that custom arrayMerge()
    // implementations can use it. The caller may not replace it.
    options.cloneUnlessOtherwiseSpecified = cloneUnlessOtherwiseSpecified;
    var sourceIsArray = Array.isArray(source);
    var targetIsArray = Array.isArray(target);
    var sourceAndTargetTypesMatch = sourceIsArray === targetIsArray;
    if (!sourceAndTargetTypesMatch) {
      return cloneUnlessOtherwiseSpecified(source, options);
    } else if (sourceIsArray) {
      return options.arrayMerge(target, source, options);
    } else {
      return mergeObject(target, source, options);
    }
  }
  deepmerge.all = function deepmergeAll(array, options) {
    if (!Array.isArray(array)) {
      throw new Error('first argument should be an array');
    }
    return array.reduce(function (prev, next) {
      return deepmerge(prev, next, options);
    }, {});
  };
  var deepmerge_1 = deepmerge;
  cjs = deepmerge_1;
  return cjs;
}

var cjsExports = requireCjs();
var deepmerge = /*@__PURE__*/getDefaultExportFromCjs(cjsExports);

/**
 * Created by championswimmer on 22/07/17.
 */
let MockStorage;
// @ts-ignore
{
  MockStorage = class {
    get length() {
      return Object.keys(this).length;
    }
    key(index) {
      return Object.keys(this)[index];
    }
    setItem(key, data) {
      this[key] = data.toString();
    }
    getItem(key) {
      return this[key];
    }
    removeItem(key) {
      delete this[key];
    }
    clear() {
      for (let key of Object.keys(this)) {
        delete this[key];
      }
    }
  };
}

// tslint:disable: variable-name
class SimplePromiseQueue {
  constructor() {
    this._queue = [];
    this._flushing = false;
  }
  enqueue(promise) {
    this._queue.push(promise);
    if (!this._flushing) {
      return this.flushQueue();
    }
    return Promise.resolve();
  }
  flushQueue() {
    this._flushing = true;
    const chain = () => {
      const nextTask = this._queue.shift();
      if (nextTask) {
        return nextTask.then(chain);
      } else {
        this._flushing = false;
      }
    };
    return Promise.resolve(chain());
  }
}
const options$1 = {
  replaceArrays: {
    arrayMerge: (destinationArray, sourceArray, options) => sourceArray
  },
  concatArrays: {
    arrayMerge: (target, source, options) => target.concat(...source)
  }
};
function merge(into, from, mergeOption) {
  return deepmerge(into, from, options$1[mergeOption]);
}
let FlattedJSON = JSON;
/**
 * A class that implements the vuex persistence.
 * @type S type of the 'state' inside the store (default: any)
 */
class VuexPersistence {
  /**
   * Create a {@link VuexPersistence} object.
   * Use the <code>plugin</code> function of this class as a
   * Vuex plugin.
   * @param {PersistOptions} options
   */
  constructor(options) {
    // tslint:disable-next-line:variable-name
    this._mutex = new SimplePromiseQueue();
    /**
     * Creates a subscriber on the store. automatically is used
     * when this is used a vuex plugin. Not for manual usage.
     * @param store
     */
    this.subscriber = store => handler => store.subscribe(handler);
    if (typeof options === 'undefined') options = {};
    this.key = options.key != null ? options.key : 'vuex';
    this.subscribed = false;
    this.supportCircular = options.supportCircular || false;
    if (this.supportCircular) {
      FlattedJSON = require('flatted');
    }
    this.mergeOption = options.mergeOption || 'replaceArrays';
    let localStorageLitmus = true;
    try {
      window.localStorage.getItem('');
    } catch (err) {
      localStorageLitmus = false;
    }
    /**
     * 1. First, prefer storage sent in optinos
     * 2. Otherwise, use window.localStorage if available
     * 3. Finally, try to use MockStorage
     * 4. None of above? Well we gotta fail.
     */
    if (options.storage) {
      this.storage = options.storage;
    } else if (localStorageLitmus) {
      this.storage = window.localStorage;
    } else if (MockStorage) {
      this.storage = new MockStorage();
    } else {
      throw new Error("Neither 'window' is defined, nor 'MockStorage' is available");
    }
    /**
     * How this works is -
     *  1. If there is options.reducer function, we use that, if not;
     *  2. We check options.modules;
     *    1. If there is no options.modules array, we use entire state in reducer
     *    2. Otherwise, we create a reducer that merges all those state modules that are
     *        defined in the options.modules[] array
     * @type {((state: S) => {}) | ((state: S) => S) | ((state: any) => {})}
     */
    this.reducer = options.reducer != null ? options.reducer : options.modules == null ? state => state : state => options.modules.reduce((a, i) => merge(a, {
      [i]: state[i]
    }, this.mergeOption), {/* start empty accumulator*/});
    this.filter = options.filter || (mutation => true);
    this.strictMode = options.strictMode || false;
    this.RESTORE_MUTATION = function RESTORE_MUTATION(state, savedState) {
      const mergedState = merge(state, savedState || {}, this.mergeOption);
      for (const propertyName of Object.keys(mergedState)) {
        this._vm.$set(state, propertyName, mergedState[propertyName]);
      }
    };
    this.asyncStorage = options.asyncStorage || false;
    if (this.asyncStorage) {
      /**
       * Async {@link #VuexPersistence.restoreState} implementation
       * @type {((key: string, storage?: Storage) =>
       *      (Promise<S> | S)) | ((key: string, storage: AsyncStorage) => Promise<any>)}
       */
      this.restoreState = options.restoreState != null ? options.restoreState : (key, storage) => storage.getItem(key).then(value => typeof value === 'string' // If string, parse, or else, just return
      ? this.supportCircular ? FlattedJSON.parse(value || '{}') : JSON.parse(value || '{}') : value || {});
      /**
       * Async {@link #VuexPersistence.saveState} implementation
       * @type {((key: string, state: {}, storage?: Storage) =>
       *    (Promise<void> | void)) | ((key: string, state: {}, storage?: Storage) => Promise<void>)}
       */
      this.saveState = options.saveState != null ? options.saveState : (key, state, storage) => storage.setItem(key,
      // Second argument is state _object_ if asyc storage, stringified otherwise
      // do not stringify the state if the storage type is async
      this.asyncStorage ? merge({}, state || {}, this.mergeOption) : this.supportCircular ? FlattedJSON.stringify(state) : JSON.stringify(state));
      /**
       * Async version of plugin
       * @param {Store<S>} store
       */
      this.plugin = store => {
        /**
         * For async stores, we're capturing the Promise returned
         * by the `restoreState()` function in a `restored` property
         * on the store itself. This would allow app developers to
         * determine when and if the store's state has indeed been
         * refreshed. This approach was suggested by GitHub user @hotdogee.
         * See https://github.com/championswimmer/vuex-persist/pull/118#issuecomment-500914963
         * @since 2.1.0
         */
        store.restored = this.restoreState(this.key, this.storage).then(savedState => {
          /**
           * If in strict mode, do only via mutation
           */
          if (this.strictMode) {
            store.commit('RESTORE_MUTATION', savedState);
          } else {
            store.replaceState(merge(store.state, savedState || {}, this.mergeOption));
          }
          this.subscriber(store)((mutation, state) => {
            if (this.filter(mutation)) {
              this._mutex.enqueue(this.saveState(this.key, this.reducer(state), this.storage));
            }
          });
          this.subscribed = true;
        });
      };
    } else {
      /**
       * Sync {@link #VuexPersistence.restoreState} implementation
       * @type {((key: string, storage?: Storage) =>
       *    (Promise<S> | S)) | ((key: string, storage: Storage) => (any | string | {}))}
       */
      this.restoreState = options.restoreState != null ? options.restoreState : (key, storage) => {
        const value = storage.getItem(key);
        if (typeof value === 'string') {
          // If string, parse, or else, just return
          return this.supportCircular ? FlattedJSON.parse(value || '{}') : JSON.parse(value || '{}');
        } else {
          return value || {};
        }
      };
      /**
       * Sync {@link #VuexPersistence.saveState} implementation
       * @type {((key: string, state: {}, storage?: Storage) =>
       *     (Promise<void> | void)) | ((key: string, state: {}, storage?: Storage) => Promise<void>)}
       */
      this.saveState = options.saveState != null ? options.saveState : (key, state, storage) => storage.setItem(key,
      // Second argument is state _object_ if localforage, stringified otherwise
      this.supportCircular ? FlattedJSON.stringify(state) : JSON.stringify(state));
      /**
       * Sync version of plugin
       * @param {Store<S>} store
       */
      this.plugin = store => {
        const savedState = this.restoreState(this.key, this.storage);
        if (this.strictMode) {
          store.commit('RESTORE_MUTATION', savedState);
        } else {
          store.replaceState(merge(store.state, savedState || {}, this.mergeOption));
        }
        this.subscriber(store)((mutation, state) => {
          if (this.filter(mutation)) {
            this.saveState(this.key, this.reducer(state), this.storage);
          }
        });
        this.subscribed = true;
      };
    }
  }
}

// The options for persisting state
const persistedStateOptions = {
  storage: window.sessionStorage,
  key: 'joomla.mediamanager',
  reducer: state => ({
    selectedDirectory: state.selectedDirectory,
    showInfoBar: state.showInfoBar,
    listView: state.listView,
    gridSize: state.gridSize,
    search: state.search,
    sortBy: state.sortBy,
    sortDirection: state.sortDirection
  })
};

// Get the disks from joomla option storage
const options = Joomla.getOptions('com_media', {});
if (options.providers === undefined || options.providers.length === 0) {
  throw new TypeError('Media providers are not defined.');
}

/**
 * Get the drives
 *
 * @param  {Array}  adapterNames
 * @param  {String} provider
 *
 * @return {Array}
 */
const getDrives = (adapterNames, provider) => adapterNames.map(name => ({
  root: provider + "-" + name + ":/",
  displayName: name
}));

// Load disks from options
const loadedDisks = options.providers.map(disk => ({
  displayName: disk.displayName,
  drives: getDrives(disk.adapterNames, disk.name)
}));
const defaultDisk = loadedDisks.find(disk => disk.drives.length > 0 && disk.drives[0] !== undefined);
if (!defaultDisk) {
  throw new TypeError('No default media drive was found');
}
const storedState = JSON.parse(persistedStateOptions.storage.getItem(persistedStateOptions.key));
function setSession(path) {
  persistedStateOptions.storage.setItem(persistedStateOptions.key, JSON.stringify(_extends({}, storedState, {
    selectedDirectory: path
  })));
}

// Gracefully use the given path, the session storage state or fall back to sensible default
function getCurrentPath() {
  let path = options.currentPath;

  // Set the path from the session when available
  if (!path && storedState && storedState.selectedDirectory) {
    path = storedState.selectedDirectory;
  }

  // No path available, use the root of the first drive
  if (!path) {
    setSession(defaultDisk.drives[0].root);
    return defaultDisk.drives[0].root;
  }

  // Get the fragments
  const fragment = path.split(':/');

  // Check that we have a drive
  if (!fragment.length) {
    setSession(defaultDisk.drives[0].root);
    return defaultDisk.drives[0].root;
  }
  const drivesTmp = Object.values(loadedDisks).map(drive => drive.drives);

  // Drive doesn't exist
  if (!drivesTmp.flat().find(drive => drive.root.startsWith(fragment[0]))) {
    setSession(defaultDisk.drives[0].root);
    return defaultDisk.drives[0].root;
  }

  // Session mismatch
  setSession(path);
  return path;
}

// The initial state
var state = {
  // The general loading state
  isLoading: false,
  // Will hold the activated filesystem disks
  disks: loadedDisks,
  // The loaded directories
  directories: loadedDisks.map(() => ({
    path: defaultDisk.drives[0].root,
    name: defaultDisk.displayName,
    directories: [],
    files: [],
    directory: null
  })),
  // The loaded files
  files: [],
  // The selected disk. Providers are ordered by plugin ordering, so we set the first provider
  // in the list as the default provider and load first drive on it as default
  selectedDirectory: getCurrentPath(),
  // The currently selected items
  selectedItems: [],
  // The state of the infobar
  showInfoBar: false,
  // List view
  listView: 'grid',
  // The size of the grid items
  gridSize: 'md',
  // The state of confirm delete model
  showConfirmDeleteModal: false,
  // The state of create folder model
  showCreateFolderModal: false,
  // The state of preview model
  showPreviewModal: false,
  // The state of share model
  showShareModal: false,
  // The state of  model
  showRenameModal: false,
  // The preview item
  previewItem: null,
  // The Search Query
  search: '',
  // The sorting by
  sortBy: storedState && storedState.sortBy ? storedState.sortBy : 'name',
  // The sorting direction
  sortDirection: storedState && storedState.sortDirection ? storedState.sortDirection : 'asc'
};

// Sometimes we may need to compute derived state based on store state,
// for example filtering through a list of items and counting them.
/**
 * Get the currently selected directory
 * @param state
 * @returns {*}
 */
const getSelectedDirectory = state => state.directories.find(directory => directory.path === state.selectedDirectory);

/**
 * Get the sudirectories of the currently selected directory
 * @param state
 *
 * @returns {Array|directories|{/}|computed.directories|*|Object}
 */
const getSelectedDirectoryDirectories = state => state.directories.filter(directory => directory.directory === state.selectedDirectory);

/**
 * Get the files of the currently selected directory
 * @param state
 *
 * @returns {Array|files|{}|FileList|*}
 */
const getSelectedDirectoryFiles = state => state.files.filter(file => file.directory === state.selectedDirectory);

/**
 * Whether or not all items of the current directory are selected
 * @param state
 * @param getters
 * @returns Array
 */
const getSelectedDirectoryContents = (state, getters) => [...getters.getSelectedDirectoryDirectories, ...getters.getSelectedDirectoryFiles];

var getters = /*#__PURE__*/Object.freeze({
  __proto__: null,
  getSelectedDirectory: getSelectedDirectory,
  getSelectedDirectoryContents: getSelectedDirectoryContents,
  getSelectedDirectoryDirectories: getSelectedDirectoryDirectories,
  getSelectedDirectoryFiles: getSelectedDirectoryFiles
});

const updateUrlPath = path => {
  const currentPath = path === null ? '' : path;
  const url = new URL(window.location.href);
  if (url.searchParams.has('path')) {
    window.history.pushState(null, '', url.href.replace(/\b(path=).*?(&|$)/, "$1" + currentPath + "$2"));
  } else {
    window.history.pushState(null, '', url.href + (url.href.indexOf('?') > 0 ? '&' : '?') + "path=" + currentPath);
  }
};

/**
 * Actions are similar to mutations, the difference being that:
 * Instead of mutating the state, actions commit mutations.
 * Actions can contain arbitrary asynchronous operations.
 */

/**
 * Get contents of a directory from the api
 * @param context
 * @param payload
 */
const getContents = (context, payload) => {
  // Update the url
  updateUrlPath(payload);
  context.commit(SET_IS_LOADING, true);
  api.getContents(payload, false, false).then(contents => {
    context.commit(LOAD_CONTENTS_SUCCESS, contents);
    context.commit(UNSELECT_ALL_BROWSER_ITEMS);
    context.commit(SELECT_DIRECTORY, payload);
    context.commit(SET_IS_LOADING, false);
  }).catch(error => {
    // @todo error handling
    context.commit(SET_IS_LOADING, false);
    throw new Error(error);
  });
};

/**
 * Get the full contents of a directory
 * @param context
 * @param payload
 */
const getFullContents = (context, payload) => {
  context.commit(SET_IS_LOADING, true);
  api.getContents(payload.path, true, true).then(contents => {
    context.commit(LOAD_FULL_CONTENTS_SUCCESS, contents.files[0]);
    context.commit(SET_IS_LOADING, false);
  }).catch(error => {
    // @todo error handling
    context.commit(SET_IS_LOADING, false);
    throw new Error(error);
  });
};

/**
 * Download a file
 * @param context
 * @param payload
 */
const download = (context, payload) => {
  api.getContents(payload.path, false, true).then(contents => {
    const file = contents.files[0];

    // Download file
    const a = document.createElement('a');
    a.href = "data:" + file.mime_type + ";base64," + file.content;
    a.download = file.name;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
  }).catch(error => {
    throw new Error(error);
  });
};

/**
 * Toggle the selection state of an item
 * @param context
 * @param payload
 */
const toggleBrowserItemSelect = (context, payload) => {
  const item = payload;
  const isSelected = context.state.selectedItems.some(selected => selected.path === item.path);
  if (!isSelected) {
    context.commit(SELECT_BROWSER_ITEM, item);
  } else {
    context.commit(UNSELECT_BROWSER_ITEM, item);
  }
};

/**
 * Create a new folder
 * @param context
 * @param payload object with the new folder name and its parent directory
 */
const createDirectory = (context, payload) => {
  if (!api.canCreate) {
    return;
  }
  context.commit(SET_IS_LOADING, true);
  api.createDirectory(payload.name, payload.parent).then(folder => {
    context.commit(CREATE_DIRECTORY_SUCCESS, folder);
    context.commit(HIDE_CREATE_FOLDER_MODAL);
    context.commit(SET_IS_LOADING, false);
  }).catch(error => {
    // @todo error handling
    context.commit(SET_IS_LOADING, false);
    throw new Error(error);
  });
};

/**
 * Create a new folder
 * @param context
 * @param payload object with the new folder name and its parent directory
 */
const uploadFile = (context, payload) => {
  if (!api.canCreate) {
    return;
  }
  context.commit(SET_IS_LOADING, true);
  api.upload(payload.name, payload.parent, payload.content, payload.override || false).then(file => {
    context.commit(UPLOAD_SUCCESS, file);
    context.commit(SET_IS_LOADING, false);
  }).catch(error => {
    context.commit(SET_IS_LOADING, false);

    // Handle file exists
    if (error.status === 409) {
      if (notifications.ask(Translate.sprintf('COM_MEDIA_FILE_EXISTS_AND_OVERRIDE', payload.name), {})) {
        payload.override = true;
        uploadFile(context, payload);
      }
    }
  });
};

/**
 * Rename an item
 * @param context
 * @param payload object: the item and the new path
 */
const renameItem = (context, payload) => {
  if (!api.canEdit) {
    return;
  }
  if (typeof payload.item.canEdit !== 'undefined' && payload.item.canEdit === false) {
    return;
  }
  context.commit(SET_IS_LOADING, true);
  api.rename(payload.item.path, payload.newPath).then(item => {
    context.commit(RENAME_SUCCESS, {
      item,
      oldPath: payload.item.path,
      newName: payload.newName
    });
    context.commit(HIDE_RENAME_MODAL);
    context.commit(SET_IS_LOADING, false);
  }).catch(error => {
    // @todo error handling
    context.commit(SET_IS_LOADING, false);
    throw new Error(error);
  });
};

/**
 * Delete the selected items
 * @param context
 */
const deleteSelectedItems = context => {
  if (!api.canDelete) {
    return;
  }
  context.commit(SET_IS_LOADING, true);
  // Get the selected items from the store
  const {
    selectedItems,
    search
  } = context.state;
  if (selectedItems.length > 0) {
    selectedItems.forEach(item => {
      if (typeof item.canDelete !== 'undefined' && item.canDelete === false || search && !item.name.toLowerCase().includes(search.toLowerCase())) {
        return;
      }
      api.delete(item.path).then(() => {
        context.commit(DELETE_SUCCESS, item);
        context.commit(UNSELECT_ALL_BROWSER_ITEMS);
        context.commit(SET_IS_LOADING, false);
      }).catch(error => {
        // @todo error handling
        context.commit(SET_IS_LOADING, false);
        throw new Error(error);
      });
    });
  }
};

/**
 * Update item properties
 * @param context
 * @param payload object: the item, the width and the height
 */
const updateItemProperties = (context, payload) => context.commit(UPDATE_ITEM_PROPERTIES, payload);

var actions = /*#__PURE__*/Object.freeze({
  __proto__: null,
  createDirectory: createDirectory,
  deleteSelectedItems: deleteSelectedItems,
  download: download,
  getContents: getContents,
  getFullContents: getFullContents,
  renameItem: renameItem,
  toggleBrowserItemSelect: toggleBrowserItemSelect,
  updateItemProperties: updateItemProperties,
  uploadFile: uploadFile
});

// The only way to actually change state in a store is by committing a mutation.
// Mutations are very similar to events: each mutation has a string type and a handler.
// The handler function is where we perform actual state modifications,
// and it will receive the state as the first argument.

// The grid item sizes
const gridItemSizes = ['sm', 'md', 'lg', 'xl'];
var mutations = {
  /**
   * Select a directory
   * @param state
   * @param payload
   */
  [SELECT_DIRECTORY]: (state, payload) => {
    state.selectedDirectory = payload;
    state.search = '';
  },
  /**
   * The load content success mutation
   * @param state
   * @param payload
   */
  [LOAD_CONTENTS_SUCCESS]: (state, payload) => {
    /**
     * Create a directory from a path
     * @param path
     */
    function directoryFromPath(path) {
      const parts = path.split('/');
      let directory = dirname(path);
      if (directory.indexOf(':', directory.length - 1) !== -1) {
        directory += '/';
      }
      return {
        path,
        name: parts[parts.length - 1],
        directories: [],
        files: [],
        directory: directory !== '.' ? directory : null,
        type: 'dir',
        mime_type: 'directory'
      };
    }

    /**
     * Create the directory structure
     * @param path
     */
    function createDirectoryStructureFromPath(path) {
      const exists = state.directories.some(existing => existing.path === path);
      if (!exists) {
        const directory = directoryFromPath(path);

        // Add the sub directories and files
        directory.directories = state.directories.filter(existing => existing.directory === directory.path).map(existing => existing.path);

        // Add the directory
        state.directories.push(directory);
        if (directory.directory) {
          createDirectoryStructureFromPath(directory.directory);
        }
      }
    }

    /**
     * Add a directory
     * @param unused
     * @param directory
     */
    function addDirectory(unused, directory) {
      const parentDirectory = state.directories.find(existing => existing.path === directory.directory);
      const parentDirectoryIndex = state.directories.indexOf(parentDirectory);
      let index = state.directories.findIndex(existing => existing.path === directory.path);
      if (index === -1) {
        index = state.directories.length;
      }

      // Add the directory
      state.directories.splice(index, 1, directory);

      // Update the relation to the parent directory
      if (parentDirectoryIndex !== -1) {
        state.directories.splice(parentDirectoryIndex, 1, _extends({}, parentDirectory, {
          directories: [...parentDirectory.directories, directory.path]
        }));
      }
    }

    /**
     * Add a file
     * @param unused
     * @param directory
     */
    function addFile(unused, file) {
      const parentDirectory = state.directories.find(directory => directory.path === file.directory);
      const parentDirectoryIndex = state.directories.indexOf(parentDirectory);
      let index = state.files.findIndex(existing => existing.path === file.path);
      if (index === -1) {
        index = state.files.length;
      }

      // Add the file
      state.files.splice(index, 1, file);

      // Update the relation to the parent directory
      if (parentDirectoryIndex !== -1) {
        state.directories.splice(parentDirectoryIndex, 1, _extends({}, parentDirectory, {
          files: [...parentDirectory.files, file.path]
        }));
      }
    }

    // Create the parent directory structure if it does not exist
    createDirectoryStructureFromPath(state.selectedDirectory);

    // Add directories
    payload.directories.forEach(directory => addDirectory(null, directory));

    // Add files
    payload.files.forEach(file => addFile(null, file));
  },
  /**
   * The upload success mutation
   * @param state
   * @param payload
   */
  [UPLOAD_SUCCESS]: (state, payload) => {
    const file = payload;
    const isNew = !state.files.some(existing => existing.path === file.path);

    // @todo handle file_exists
    if (isNew) {
      const parentDirectory = state.directories.find(existing => existing.path === file.directory);
      const parentDirectoryIndex = state.directories.indexOf(parentDirectory);

      // Add the new file to the files array
      state.files.push(file);

      // Update the relation to the parent directory
      state.directories.splice(parentDirectoryIndex, 1, _extends({}, parentDirectory, {
        files: [...parentDirectory.files, file.path]
      }));
    }

    // Automatically select the last uploaded item when the media manager is inside an iframe
    if (window.location === window.parent.location || !state.files.length) {
      return;
    }
    const selectedFile = state.files.find(item => item.name === file.name);
    if (!selectedFile) {
      return;
    }
    state.selectedItems = [selectedFile];
    window.parent.document.dispatchEvent(new CustomEvent('onMediaFileSelected', {
      bubbles: true,
      cancelable: false,
      detail: {
        type: selectedFile.type,
        name: selectedFile.name,
        path: selectedFile.path,
        thumb: selectedFile.thumb,
        fileType: selectedFile.mime_type ? selectedFile.mime_type : false,
        extension: selectedFile.extension ? selectedFile.extension : false,
        width: selectedFile.width ? selectedFile.width : 0,
        height: selectedFile.height ? selectedFile.height : 0
      }
    }));
  },
  /**
   * The create directory success mutation
   * @param state
   * @param payload
   */
  [CREATE_DIRECTORY_SUCCESS]: (state, payload) => {
    const directory = payload;
    const isNew = !state.directories.some(existing => existing.path === directory.path);
    if (isNew) {
      const parentDirectory = state.directories.find(existing => existing.path === directory.directory);
      const parentDirectoryIndex = state.directories.indexOf(parentDirectory);

      // Add the new directory to the directory
      state.directories.push(directory);

      // Update the relation to the parent directory
      state.directories.splice(parentDirectoryIndex, 1, _extends({}, parentDirectory, {
        directories: [...parentDirectory.directories, directory.path]
      }));
    }
  },
  /**
   * The rename success handler
   * @param state
   * @param payload
   */
  [RENAME_SUCCESS]: (state, payload) => {
    state.selectedItems[state.selectedItems.length - 1].name = payload.newName;
    const {
      item
    } = payload;
    const {
      oldPath
    } = payload;
    if (item.type === 'file') {
      const index = state.files.findIndex(file => file.path === oldPath);
      state.files.splice(index, 1, item);
    } else {
      const index = state.directories.findIndex(directory => directory.path === oldPath);
      state.directories.splice(index, 1, item);
    }
  },
  /**
   * The delete success mutation
   * @param state
   * @param payload
   */
  [DELETE_SUCCESS]: (state, payload) => {
    const item = payload;

    // Delete file
    if (item.type === 'file') {
      state.files.splice(state.files.findIndex(file => file.path === item.path), 1);
    }

    // Delete dir
    if (item.type === 'dir') {
      state.directories.splice(state.directories.findIndex(directory => directory.path === item.path), 1);
    }
  },
  /**
   * Select a browser item
   * @param state
   * @param payload the item
   */
  [SELECT_BROWSER_ITEM]: (state, payload) => {
    state.selectedItems.push(payload);
  },
  /**
   * Select browser items
   * @param state
   * @param payload the items
   */
  [SELECT_BROWSER_ITEMS]: (state, payload) => {
    state.selectedItems = payload;
  },
  /**
   * Unselect a browser item
   * @param state
   * @param payload the item
   */
  [UNSELECT_BROWSER_ITEM]: (state, payload) => {
    const item = payload;
    state.selectedItems.splice(state.selectedItems.findIndex(selectedItem => selectedItem.path === item.path), 1);
  },
  /**
   * Unselect all browser items
   * @param state
   * @param payload the item
   */
  [UNSELECT_ALL_BROWSER_ITEMS]: state => {
    state.selectedItems = [];
  },
  /**
   * Show the create folder modal
   * @param state
   */
  [SHOW_CREATE_FOLDER_MODAL]: state => {
    state.showCreateFolderModal = true;
  },
  /**
   * Hide the create folder modal
   * @param state
   */
  [HIDE_CREATE_FOLDER_MODAL]: state => {
    state.showCreateFolderModal = false;
  },
  /**
   * Show the info bar
   * @param state
   */
  [SHOW_INFOBAR]: state => {
    state.showInfoBar = true;
  },
  /**
   * Show the info bar
   * @param state
   */
  [HIDE_INFOBAR]: state => {
    state.showInfoBar = false;
  },
  /**
   * Define the list grid view
   * @param state
   */
  [CHANGE_LIST_VIEW]: (state, view) => {
    state.listView = view;
  },
  /**
   * FUll content is loaded
   * @param state
   * @param payload
   */
  [LOAD_FULL_CONTENTS_SUCCESS]: (state, payload) => {
    state.previewItem = payload;
  },
  /**
   * Show the preview modal
   * @param state
   */
  [SHOW_PREVIEW_MODAL]: state => {
    state.showPreviewModal = true;
  },
  /**
   * Hide the preview modal
   * @param state
   */
  [HIDE_PREVIEW_MODAL]: state => {
    state.showPreviewModal = false;
  },
  /**
   * Set the is loading state
   * @param state
   */
  [SET_IS_LOADING]: (state, payload) => {
    state.isLoading = payload;
  },
  /**
   * Show the rename modal
   * @param state
   */
  [SHOW_RENAME_MODAL]: state => {
    state.showRenameModal = true;
  },
  /**
   * Hide the rename modal
   * @param state
   */
  [HIDE_RENAME_MODAL]: state => {
    state.showRenameModal = false;
  },
  /**
   * Show the share modal
   * @param state
   */
  [SHOW_SHARE_MODAL]: state => {
    state.showShareModal = true;
  },
  /**
   * Hide the share modal
   * @param state
   */
  [HIDE_SHARE_MODAL]: state => {
    state.showShareModal = false;
  },
  /**
   * Increase the size of the grid items
   * @param state
   */
  [INCREASE_GRID_SIZE]: state => {
    const currentSizeIndex = gridItemSizes.indexOf(state.gridSize);
    if (currentSizeIndex >= 0 && currentSizeIndex < gridItemSizes.length - 1) {
      state.gridSize = gridItemSizes[currentSizeIndex + 1];
    }
  },
  /**
   * Increase the size of the grid items
   * @param state
   */
  [DECREASE_GRID_SIZE]: state => {
    const currentSizeIndex = gridItemSizes.indexOf(state.gridSize);
    if (currentSizeIndex > 0 && currentSizeIndex < gridItemSizes.length) {
      state.gridSize = gridItemSizes[currentSizeIndex - 1];
    }
  },
  /**
   * Set search query
   * @param state
   * @param query
   */
  [SET_SEARCH_QUERY]: (state, query) => {
    state.search = query;
  },
  /**
   * Show the confirm modal
   * @param state
   */
  [SHOW_CONFIRM_DELETE_MODAL]: state => {
    state.showConfirmDeleteModal = true;
  },
  /**
   * Hide the confirm modal
   * @param state
   */
  [HIDE_CONFIRM_DELETE_MODAL]: state => {
    state.showConfirmDeleteModal = false;
  },
  /**
   * Update item properties
   * @param context
   * @param payload object with the item, the width and the height
   */
  [UPDATE_ITEM_PROPERTIES]: (state, payload) => {
    const {
      item,
      width,
      height
    } = payload;
    const index = state.files.findIndex(file => file.path === item.path);
    state.files[index].width = width;
    state.files[index].height = height;
  },
  /**
   * Set the sorting by
   * @param state
   * @param payload
   */
  [UPDATE_SORT_BY]: (state, payload) => {
    state.sortBy = payload;
  },
  /**
   * Set the sorting direction
   * @param state
   * @param payload
   */
  [UPDATE_SORT_DIRECTION]: (state, payload) => {
    state.sortDirection = payload === 'asc' ? 'asc' : 'desc';
  }
};

// A Vuex instance is created by combining the state, mutations, actions, and getters.
var store = createStore({
  state,
  getters,
  actions,
  mutations,
  plugins: [new VuexPersistence(persistedStateOptions).plugin],
  strict: "production" !== 'production'
});

// Register MediaManager namespace
window.MediaManager = window.MediaManager || {};
// Register the media manager event bus
window.MediaManager.Event = new Event$1();

// Create the Vue app instance
createApp(script).use(store).use(Translate).mount('#com-media');
