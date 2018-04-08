(() => {
	// Accessible Autocomplete: https://github.com/alphagov/accessible-autocomplete
	// License: MIT
	!function(e,t){"object"==typeof exports&&"object"==typeof module?module.exports=t():"function"==typeof define&&define.amd?define([],t):"object"==typeof exports?exports.accessibleAutocomplete=t():e.accessibleAutocomplete=t()}(this,function(){return function(e){function t(o){if(n[o])return n[o].exports;var r=n[o]={i:o,l:!1,exports:{}};return e[o].call(r.exports,r,r.exports,t),r.l=!0,r.exports}var n={};return t.m=e,t.c=n,t.d=function(e,n,o){t.o(e,n)||Object.defineProperty(e,n,{configurable:!1,enumerable:!0,get:o})},t.n=function(e){var n=e&&e.__esModule?function(){return e.default}:function(){return e};return t.d(n,"a",n),n},t.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},t.p="/",t(t.s=1)}([function(e,t,n){!function(){"use strict";function t(){}function n(e,n){var o,r,l,i,u=L;for(i=arguments.length;i-- >2;)D.push(arguments[i]);for(n&&null!=n.children&&(D.length||D.push(n.children),delete n.children);D.length;)if((r=D.pop())&&void 0!==r.pop)for(i=r.length;i--;)D.push(r[i]);else!0!==r&&!1!==r||(r=null),(l="function"!=typeof e)&&(null==r?r="":"number"==typeof r?r=String(r):"string"!=typeof r&&(l=!1)),l&&o?u[u.length-1]+=r:u===L?u=[r]:u.push(r),o=l;var s=new t;return s.nodeName=e,s.children=u,s.attributes=null==n?void 0:n,s.key=null==n?void 0:n.key,void 0!==A.vnode&&A.vnode(s),s}function o(e,t){for(var n in t)e[n]=t[n];return e}function r(e,t){return n(e.nodeName,o(o({},e.attributes),t),arguments.length>2?[].slice.call(arguments,2):e.children)}function l(e){!e.__d&&(e.__d=!0)&&1==V.push(e)&&(A.debounceRendering||setTimeout)(i)}function i(){var e,t=V;for(V=[];e=t.pop();)e.__d&&N(e)}function u(e,t,n){return"string"==typeof t||"number"==typeof t?void 0!==e.splitText:"string"==typeof t.nodeName?!e._componentConstructor&&s(e,t.nodeName):n||e._componentConstructor===t.nodeName}function s(e,t){return e.__n===t||e.nodeName.toLowerCase()===t.toLowerCase()}function a(e){var t=o({},e.attributes);t.children=e.children;var n=e.nodeName.defaultProps;if(void 0!==n)for(var r in n)void 0===t[r]&&(t[r]=n[r]);return t}function p(e,t){var n=t?document.createElementNS("http://www.w3.org/2000/svg",e):document.createElement(e);return n.__n=e,n}function c(e){e.parentNode&&e.parentNode.removeChild(e)}function d(e,t,n,o,r){if("className"===t&&(t="class"),"key"===t);else if("ref"===t)n&&n(null),o&&o(e);else if("class"!==t||r)if("style"===t){if(o&&"string"!=typeof o&&"string"!=typeof n||(e.style.cssText=o||""),o&&"object"==typeof o){if("string"!=typeof n)for(var l in n)l in o||(e.style[l]="");for(var l in o)e.style[l]="number"==typeof o[l]&&!1===T.test(l)?o[l]+"px":o[l]}}else if("dangerouslySetInnerHTML"===t)o&&(e.innerHTML=o.__html||"");else if("o"==t[0]&&"n"==t[1]){var i=t!==(t=t.replace(/Capture$/,""));t=t.toLowerCase().substring(2),o?n||e.addEventListener(t,h,i):e.removeEventListener(t,h,i),(e.__l||(e.__l={}))[t]=o}else if("list"!==t&&"type"!==t&&!r&&t in e)f(e,t,null==o?"":o),null!=o&&!1!==o||e.removeAttribute(t);else{var u=r&&t!==(t=t.replace(/^xlink\:?/,""));null==o||!1===o?u?e.removeAttributeNS("http://www.w3.org/1999/xlink",t.toLowerCase()):e.removeAttribute(t):"function"!=typeof o&&(u?e.setAttributeNS("http://www.w3.org/1999/xlink",t.toLowerCase(),o):e.setAttribute(t,o))}else e.className=o||""}function f(e,t,n){try{e[t]=n}catch(e){}}function h(e){return this.__l[e.type](A.event&&A.event(e)||e)}function m(){for(var e;e=R.pop();)A.afterMount&&A.afterMount(e),e.componentDidMount&&e.componentDidMount()}function _(e,t,n,o,r,l){q++||(P=null!=r&&void 0!==r.ownerSVGElement,U=null!=e&&!("__preactattr_"in e));var i=v(e,t,n,o,l);return r&&i.parentNode!==r&&r.appendChild(i),--q||(U=!1,l||m()),i}function v(e,t,n,o,r){var l=e,i=P;if(null==t&&(t=""),"string"==typeof t)return e&&void 0!==e.splitText&&e.parentNode&&(!e._component||r)?e.nodeValue!=t&&(e.nodeValue=t):(l=document.createTextNode(t),e&&(e.parentNode&&e.parentNode.replaceChild(l,e),g(e,!0))),l.__preactattr_=!0,l;if("function"==typeof t.nodeName)return S(e,t,n,o);if(P="svg"===t.nodeName||"foreignObject"!==t.nodeName&&P,(!e||!s(e,String(t.nodeName)))&&(l=p(String(t.nodeName),P),e)){for(;e.firstChild;)l.appendChild(e.firstChild);e.parentNode&&e.parentNode.replaceChild(l,e),g(e,!0)}var u=l.firstChild,a=l.__preactattr_||(l.__preactattr_={}),c=t.children;return!U&&c&&1===c.length&&"string"==typeof c[0]&&null!=u&&void 0!==u.splitText&&null==u.nextSibling?u.nodeValue!=c[0]&&(u.nodeValue=c[0]):(c&&c.length||null!=u)&&y(l,c,n,o,U||null!=a.dangerouslySetInnerHTML),w(l,t.attributes,a),P=i,l}function y(e,t,n,o,r){var l,i,s,a,p=e.childNodes,d=[],f={},h=0,m=0,_=p.length,y=0,b=t?t.length:0;if(0!==_)for(var w=0;w<_;w++){var O=p[w],C=O.__preactattr_,x=b&&C?O._component?O._component.__k:C.key:null;null!=x?(h++,f[x]=O):(C||(void 0!==O.splitText?!r||O.nodeValue.trim():r))&&(d[y++]=O)}if(0!==b)for(var w=0;w<b;w++){s=t[w],a=null;var x=s.key;if(null!=x)h&&void 0!==f[x]&&(a=f[x],f[x]=void 0,h--);else if(!a&&m<y)for(l=m;l<y;l++)if(void 0!==d[l]&&u(i=d[l],s,r)){a=i,d[l]=void 0,l===y-1&&y--,l===m&&m++;break}(a=v(a,s,n,o))&&a!==e&&(w>=_?e.appendChild(a):a!==p[w]&&(a===p[w+1]?c(p[w]):e.insertBefore(a,p[w]||null)))}if(h)for(var w in f)void 0!==f[w]&&g(f[w],!1);for(;m<=y;)void 0!==(a=d[y--])&&g(a,!1)}function g(e,t){var n=e._component;n?I(n):(null!=e.__preactattr_&&e.__preactattr_.ref&&e.__preactattr_.ref(null),!1!==t&&null!=e.__preactattr_||c(e),b(e))}function b(e){for(e=e.lastChild;e;){var t=e.previousSibling;g(e,!0),e=t}}function w(e,t,n){var o;for(o in n)t&&null!=t[o]||null==n[o]||d(e,o,n[o],n[o]=void 0,P);for(o in t)"children"===o||"innerHTML"===o||o in n&&t[o]===("value"===o||"checked"===o?e[o]:n[o])||d(e,o,n[o],n[o]=t[o],P)}function O(e){var t=e.constructor.name;(B[t]||(B[t]=[])).push(e)}function C(e,t,n){var o,r=B[e.name];if(e.prototype&&e.prototype.render?(o=new e(t,n),k.call(o,t,n)):(o=new k(t,n),o.constructor=e,o.render=x),r)for(var l=r.length;l--;)if(r[l].constructor===e){o.__b=r[l].__b,r.splice(l,1);break}return o}function x(e,t,n){return this.constructor(e,n)}function E(e,t,n,o,r){e.__x||(e.__x=!0,(e.__r=t.ref)&&delete t.ref,(e.__k=t.key)&&delete t.key,!e.base||r?e.componentWillMount&&e.componentWillMount():e.componentWillReceiveProps&&e.componentWillReceiveProps(t,o),o&&o!==e.context&&(e.__c||(e.__c=e.context),e.context=o),e.__p||(e.__p=e.props),e.props=t,e.__x=!1,0!==n&&(1!==n&&!1===A.syncComponentUpdates&&e.base?l(e):N(e,1,r)),e.__r&&e.__r(e))}function N(e,t,n,r){if(!e.__x){var l,i,u,s=e.props,p=e.state,c=e.context,d=e.__p||s,f=e.__s||p,h=e.__c||c,v=e.base,y=e.__b,b=v||y,w=e._component,O=!1;if(v&&(e.props=d,e.state=f,e.context=h,2!==t&&e.shouldComponentUpdate&&!1===e.shouldComponentUpdate(s,p,c)?O=!0:e.componentWillUpdate&&e.componentWillUpdate(s,p,c),e.props=s,e.state=p,e.context=c),e.__p=e.__s=e.__c=e.__b=null,e.__d=!1,!O){l=e.render(s,p,c),e.getChildContext&&(c=o(o({},c),e.getChildContext()));var x,S,k=l&&l.nodeName;if("function"==typeof k){var M=a(l);i=w,i&&i.constructor===k&&M.key==i.__k?E(i,M,1,c,!1):(x=i,e._component=i=C(k,M,c),i.__b=i.__b||y,i.__u=e,E(i,M,0,c,!1),N(i,1,n,!0)),S=i.base}else u=b,x=w,x&&(u=e._component=null),(b||1===t)&&(u&&(u._component=null),S=_(u,l,c,n||!v,b&&b.parentNode,!0));if(b&&S!==b&&i!==w){var D=b.parentNode;D&&S!==D&&(D.replaceChild(S,b),x||(b._component=null,g(b,!1)))}if(x&&I(x),e.base=S,S&&!r){for(var L=e,T=e;T=T.__u;)(L=T).base=S;S._component=L,S._componentConstructor=L.constructor}}if(!v||n?R.unshift(e):O||(m(),e.componentDidUpdate&&e.componentDidUpdate(d,f,h),A.afterUpdate&&A.afterUpdate(e)),null!=e.__h)for(;e.__h.length;)e.__h.pop().call(e);q||r||m()}}function S(e,t,n,o){for(var r=e&&e._component,l=r,i=e,u=r&&e._componentConstructor===t.nodeName,s=u,p=a(t);r&&!s&&(r=r.__u);)s=r.constructor===t.nodeName;return r&&s&&(!o||r._component)?(E(r,p,3,n,o),e=r.base):(l&&!u&&(I(l),e=i=null),r=C(t.nodeName,p,n),e&&!r.__b&&(r.__b=e,i=null),E(r,p,1,n,o),e=r.base,i&&e!==i&&(i._component=null,g(i,!1))),e}function I(e){A.beforeUnmount&&A.beforeUnmount(e);var t=e.base;e.__x=!0,e.componentWillUnmount&&e.componentWillUnmount(),e.base=null;var n=e._component;n?I(n):t&&(t.__preactattr_&&t.__preactattr_.ref&&t.__preactattr_.ref(null),e.__b=t,c(t),O(e),b(t)),e.__r&&e.__r(null)}function k(e,t){this.__d=!0,this.context=t,this.props=e,this.state=this.state||{}}function M(e,t,n){return _(n,e,{},!1,t,!1)}var A={},D=[],L=[],T=/acit|ex(?:s|g|n|p|$)|rph|ows|mnc|ntw|ine[ch]|zoo|^ord/i,V=[],R=[],q=0,P=!1,U=!1,B={};o(k.prototype,{setState:function(e,t){var n=this.state;this.__s||(this.__s=o({},n)),o(n,"function"==typeof e?e(n,this.props):e),t&&(this.__h=this.__h||[]).push(t),l(this)},forceUpdate:function(e){e&&(this.__h=this.__h||[]).push(e),N(this,2)},render:function(){}});var j={h:n,createElement:n,cloneElement:r,Component:k,render:M,rerender:i,options:A};e.exports=j}()},function(e,t,n){e.exports=n(2)},function(e,t,n){"use strict";function o(e){if(!e.element)throw new Error("element is not defined");if(!e.id)throw new Error("id is not defined");if(!e.source)throw new Error("source is not defined");Array.isArray(e.source)&&(e.source=s(e.source)),(0,l.render)((0,l.createElement)(u.default,e),e.element)}var r=Object.assign||function(e){for(var t=1;t<arguments.length;t++){var n=arguments[t];for(var o in n)Object.prototype.hasOwnProperty.call(n,o)&&(e[o]=n[o])}return e},l=n(0),i=n(3),u=function(e){return e&&e.__esModule?e:{default:e}}(i),s=function(e){return function(t,n){n(e.filter(function(e){return-1!==e.toLowerCase().indexOf(t.toLowerCase())}))}};o.enhanceSelectElement=function(e){if(!e.selectElement)throw new Error("selectElement is not defined");if(!e.source){var t=[].filter.call(e.selectElement.options,function(t){return t.value||e.preserveNullOptions});e.source=t.map(function(e){return e.textContent||e.innerText})}if(e.onConfirm=e.onConfirm||function(t){var n=[].filter.call(e.selectElement.options,function(e){return(e.textContent||e.innerText)===t})[0];n&&(n.selected=!0)},e.selectElement.value||void 0===e.defaultValue){var n=e.selectElement.options[e.selectElement.options.selectedIndex];e.defaultValue=n.textContent||n.innerText}void 0===e.name&&(e.name=""),void 0===e.id&&(void 0===e.selectElement.id?e.id="":e.id=e.selectElement.id),void 0===e.autoselect&&(e.autoselect=!0);var l=document.createElement("span");e.selectElement.parentNode.insertBefore(l,e.selectElement),o(r({},e,{element:l})),e.selectElement.style.display="none",e.selectElement.id=e.selectElement.id+"-select"},e.exports=o},function(e,t,n){"use strict";function o(e){return e&&e.__esModule?e:{default:e}}function r(e,t){}function l(e,t){if(e)return!t||"object"!=typeof t&&"function"!=typeof t?e:t}function i(e,t){"function"!=typeof t&&null!==t||(e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,enumerable:!1,writable:!0,configurable:!0}}),t&&(Object.setPrototypeOf?Object.setPrototypeOf(e,t):e.__proto__=t))}function u(){return!(!navigator.userAgent.match(/(iPod|iPhone|iPad)/g)||!navigator.userAgent.match(/AppleWebKit/g))}function s(e){return e>47&&e<58||32===e||8===e||e>64&&e<91||e>95&&e<112||e>185&&e<193||e>218&&e<223}function a(e){return y?{onInput:e}:g?{onChange:e}:void 0}t.__esModule=!0,t.default=void 0;var p,c,d=Object.assign||function(e){for(var t=1;t<arguments.length;t++){var n=arguments[t];for(var o in n)Object.prototype.hasOwnProperty.call(n,o)&&(e[o]=n[o])}return e},f=n(0),h=n(4),m=o(h),_=n(5),v=o(_),y=!0,g=!1,b={13:"enter",27:"escape",32:"space",38:"up",40:"down"},w=function(){var e=document.createElement("x");return e.style.cssText="pointer-events:auto","auto"===e.style.pointerEvents}(),O=(c=p=function(e){function t(n){r(this,t);var o=l(this,e.call(this,n));return o.elementReferences={},o.state={focused:null,hovered:null,menuOpen:!1,options:n.defaultValue?[n.defaultValue]:[],query:n.defaultValue,selected:null},o.handleComponentBlur=o.handleComponentBlur.bind(o),o.handleKeyDown=o.handleKeyDown.bind(o),o.handleUpArrow=o.handleUpArrow.bind(o),o.handleDownArrow=o.handleDownArrow.bind(o),o.handleEnter=o.handleEnter.bind(o),o.handlePrintableKey=o.handlePrintableKey.bind(o),o.handleOptionBlur=o.handleOptionBlur.bind(o),o.handleOptionClick=o.handleOptionClick.bind(o),o.handleOptionFocus=o.handleOptionFocus.bind(o),o.handleOptionMouseDown=o.handleOptionMouseDown.bind(o),o.handleOptionMouseEnter=o.handleOptionMouseEnter.bind(o),o.handleOptionMouseOut=o.handleOptionMouseOut.bind(o),o.handleInputBlur=o.handleInputBlur.bind(o),o.handleInputChange=o.handleInputChange.bind(o),o.handleInputFocus=o.handleInputFocus.bind(o),o.pollInputElement=o.pollInputElement.bind(o),o.getDirectInputChanges=o.getDirectInputChanges.bind(o),o}return i(t,e),t.prototype.componentDidMount=function(){this.pollInputElement()},t.prototype.componentWillUnmount=function(){clearTimeout(this.$pollInput)},t.prototype.pollInputElement=function(){var e=this;this.getDirectInputChanges(),this.$pollInput=setTimeout(function(){e.pollInputElement()},100)},t.prototype.getDirectInputChanges=function(){var e=this.elementReferences[-1];e&&e.value!==this.state.query&&this.handleInputChange({target:{value:e.value}})},t.prototype.componentDidUpdate=function(e,t){var n=this.state.focused,o=null===n,r=t.focused!==n;r&&!o&&this.elementReferences[n].focus();var l=-1===n,i=r&&null===t.focused;if(l&&i){var u=this.elementReferences[n];u.setSelectionRange(0,u.value.length)}},t.prototype.hasAutoselect=function(){return!u()&&this.props.autoselect},t.prototype.templateInputValue=function(e){var t=this.props.templates&&this.props.templates.inputValue;return t?t(e):e},t.prototype.templateSuggestion=function(e){var t=this.props.templates&&this.props.templates.suggestion;return t?t(e):e},t.prototype.handleComponentBlur=function(e){var t=this.state,n=t.options,o=t.query,r=t.selected,l=void 0;this.props.confirmOnBlur?(l=e.query||o,this.props.onConfirm(n[r])):l=o,this.setState({focused:null,menuOpen:e.menuOpen||!1,query:l,selected:null})},t.prototype.handleOptionBlur=function(e,t){var n=this.state,o=n.focused,r=n.menuOpen,l=n.options,i=n.selected,s=null===e.relatedTarget,a=e.relatedTarget===this.elementReferences[-1],p=o!==t&&-1!==o;if(!p&&s||!p&&!a){var c=r&&u();this.handleComponentBlur({menuOpen:c,query:this.templateInputValue(l[i])})}},t.prototype.handleInputBlur=function(e){var t=this.state,n=t.focused,o=t.menuOpen,r=t.options,l=t.query,i=t.selected;if(-1===n){var s=o&&u(),a=u()?l:this.templateInputValue(r[i]);this.handleComponentBlur({menuOpen:s,query:a})}},t.prototype.handleInputChange=function(e){var t=this,n=this.props,o=n.minLength,r=n.source,l=n.showAllValues,i=this.hasAutoselect(),u=e.target.value,s=0===u.length,a=this.state.query.length!==u.length,p=u.length>=o;this.setState({query:u}),l||!s&&a&&p?r(u,function(e){var n=e.length>0;t.setState({menuOpen:n,options:e,selected:i&&n?0:-1})}):!s&&p||this.setState({menuOpen:!1,options:[]})},t.prototype.handleInputClick=function(e){this.handleInputChange(e)},t.prototype.handleInputFocus=function(e){this.setState({focused:-1})},t.prototype.handleOptionFocus=function(e){this.setState({focused:e,hovered:null,selected:e})},t.prototype.handleOptionMouseEnter=function(e,t){this.setState({hovered:t})},t.prototype.handleOptionMouseOut=function(e,t){this.setState({hovered:null})},t.prototype.handleOptionClick=function(e,t){var n=this.state.options[t],o=this.templateInputValue(n);this.props.onConfirm(n),this.setState({focused:-1,menuOpen:!1,query:o,selected:-1}),this.forceUpdate()},t.prototype.handleOptionMouseDown=function(e){e.preventDefault()},t.prototype.handleUpArrow=function(e){e.preventDefault();var t=this.state,n=t.menuOpen,o=t.selected;-1!==o&&n&&this.handleOptionFocus(o-1)},t.prototype.handleDownArrow=function(e){var t=this;if(e.preventDefault(),this.props.showAllValues&&!1===this.state.menuOpen)e.preventDefault(),this.props.source("",function(e){t.setState({menuOpen:!0,options:e,selected:0,focused:0,hovered:null})});else if(!0===this.state.menuOpen){var n=this.state,o=n.menuOpen,r=n.options,l=n.selected,i=l!==r.length-1,u=i&&o;u&&this.handleOptionFocus(l+1)}},t.prototype.handleSpace=function(e){var t=this;this.props.showAllValues&&!1===this.state.menuOpen&&(e.preventDefault(),this.props.source("",function(e){t.setState({menuOpen:!0,options:e})})),-1!==this.state.focused&&(e.preventDefault(),this.handleOptionClick(e,this.state.focused))},t.prototype.handleEnter=function(e){this.state.menuOpen&&(e.preventDefault(),this.state.selected>=0&&this.handleOptionClick(e,this.state.selected))},t.prototype.handlePrintableKey=function(e){var t=this.elementReferences[-1];e.target===t||t.focus()},t.prototype.handleKeyDown=function(e){switch(b[e.keyCode]){case"up":this.handleUpArrow(e);break;case"down":this.handleDownArrow(e);break;case"space":this.handleSpace(e);break;case"enter":this.handleEnter(e);break;case"escape":this.handleComponentBlur({query:this.state.query});break;default:s(e.keyCode)&&this.handlePrintableKey(e)}},t.prototype.render=function(){var e=this,t=this.props,n=t.cssNamespace,o=t.displayMenu,r=t.id,l=t.minLength,i=t.name,u=t.placeholder,s=t.required,p=t.showAllValues,c=t.tNoResults,h=t.tStatusQueryTooShort,_=t.tStatusNoResults,v=t.tStatusSelectedOption,y=t.tStatusResults,g=t.dropdownArrow,b=this.state,O=b.focused,C=b.hovered,x=b.menuOpen,E=b.options,N=b.query,S=b.selected,I=this.hasAutoselect(),k=-1===O,M=0===E.length,A=0!==N.length,D=N.length>=l,L=this.props.showNoOptionsFound&&k&&M&&A&&D,T=n+"__wrapper",V=n+"__input",R=null!==O,q=R?" "+V+"--focused":"",P=this.props.showAllValues?" "+V+"--show-all-values":" "+V+"--default",U=n+"__dropdown-arrow-down",B=-1!==O&&null!==O,j=n+"__menu",F=j+"--"+o,W=x||L,K=j+"--"+(W?"visible":"hidden"),H=n+"__option",Q=n+"__hint",$=this.templateInputValue(E[S]),z=$&&0===$.toLowerCase().indexOf(N.toLowerCase()),G=z&&I?N+$.substr(N.length):"",J=w&&G,X=void 0;return p&&"string"==typeof(X=g({className:U}))&&(X=(0,f.createElement)("div",{className:n+"__dropdown-arrow-down-wrapper",dangerouslySetInnerHTML:{__html:X}})),(0,f.createElement)("div",{className:T,onKeyDown:this.handleKeyDown,role:"combobox","aria-expanded":x?"true":"false"},(0,f.createElement)(m.default,{length:E.length,queryLength:N.length,minQueryLength:l,selectedOption:this.templateInputValue(E[S]),tQueryTooShort:h,tNoResults:_,tSelectedOption:v,tResults:y}),J&&(0,f.createElement)("span",null,(0,f.createElement)("input",{className:Q,readonly:!0,tabIndex:"-1",value:G})),(0,f.createElement)("input",d({"aria-activedescendant":!!B&&r+"__option--"+O,"aria-owns":r+"__listbox",autoComplete:"off",className:""+V+q+P,id:r,onClick:function(t){return e.handleInputClick(t)},onBlur:this.handleInputBlur},a(this.handleInputChange),{onFocus:this.handleInputFocus,name:i,placeholder:u,ref:function(t){e.elementReferences[-1]=t},type:"text",role:"textbox",required:s,value:N})),X,(0,f.createElement)("ul",{className:j+" "+F+" "+K,id:r+"__listbox",role:"listbox"},E.map(function(t,n){var o=-1===O?S===n:O===n,l=o&&null===C?" "+H+"--focused":"",i=n%2?" "+H+"--odd":"";return(0,f.createElement)("li",{"aria-selected":O===n,className:""+H+l+i,dangerouslySetInnerHTML:{__html:e.templateSuggestion(t)},id:r+"__option--"+n,key:n,onFocusOut:function(t){return e.handleOptionBlur(t,n)},onClick:function(t){return e.handleOptionClick(t,n)},onMouseDown:e.handleOptionMouseDown,onMouseEnter:function(t){return e.handleOptionMouseEnter(t,n)},onMouseOut:function(t){return e.handleOptionMouseOut(t,n)},ref:function(t){e.elementReferences[n]=t},role:"option",tabIndex:"-1"})}),L&&(0,f.createElement)("li",{className:H+" "+H+"--no-results"},c())))},t}(f.Component),p.defaultProps={autoselect:!1,cssNamespace:"autocomplete",defaultValue:"",displayMenu:"inline",minLength:0,name:"input-autocomplete",placeholder:"",onConfirm:function(){},confirmOnBlur:!0,showNoOptionsFound:!0,showAllValues:!1,required:!1,tNoResults:function(){return"No results found"},dropdownArrow:v.default},c);t.default=O},function(e,t,n){"use strict";function o(e,t){}function r(e,t){if(e)return!t||"object"!=typeof t&&"function"!=typeof t?e:t}function l(e,t){"function"!=typeof t&&null!==t||(e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,enumerable:!1,writable:!0,configurable:!0}}),t&&(Object.setPrototypeOf?Object.setPrototypeOf(e,t):e.__proto__=t))}t.__esModule=!0,t.default=void 0;var i,u,s=n(0),a=(u=i=function(e){function t(){var n,l,i;o(this,t);for(var u=arguments.length,s=Array(u),a=0;a<u;a++)s[a]=arguments[a];return n=l=r(this,e.call.apply(e,[this].concat(s))),l.state={bump:!1},i=n,r(l,i)}return l(t,e),t.prototype.componentWillReceiveProps=function(e){e.queryLength!==this.props.queryLength&&this.setState(function(e){return{bump:!e.bump}})},t.prototype.render=function(){var e=this.props,t=e.length,n=e.queryLength,o=e.minQueryLength,r=e.selectedOption,l=e.tQueryTooShort,i=e.tNoResults,u=e.tSelectedOption,a=e.tResults,p=this.state.bump,c=n<o,d=0===t,f=r?u(r,t):"",h=null;return h=c?l(o):d?i():a(t,f),(0,s.createElement)("div",{"aria-atomic":"true","aria-live":"polite",role:"status",style:{border:"0",clip:"rect(0 0 0 0)",height:"1px",marginBottom:"-1px",marginRight:"-1px",overflow:"hidden",padding:"0",position:"absolute",whiteSpace:"nowrap",width:"1px"}},h,(0,s.createElement)("span",null,p?",":",,"))},t}(s.Component),i.defaultProps={tQueryTooShort:function(e){return"Type in "+e+" or more characters for results."},tNoResults:function(){return"No search results."},tSelectedOption:function(e,t){return e+" (1 of "+t+") is selected."},tResults:function(e,t){var n={result:1===e?"result":"results",is:1===e?"is":"are"};return e+" "+n.result+" "+n.is+" available. "+t}},u);t.default=a},function(e,t,n){"use strict";t.__esModule=!0;var o=n(0),r=function(e){var t=e.className;return(0,o.createElement)("svg",{version:"1.1",xmlns:"http://www.w3.org/2000/svg",className:t,focusable:"false"},(0,o.createElement)("g",{stroke:"none",fill:"none","fill-rule":"evenodd"},(0,o.createElement)("polygon",{fill:"#000000",points:"0 0 22 0 11 17"})))};t.default=r}])});

	// Custom category selector
	customElements.define('joomla-field-category', class extends HTMLElement {
		constructor () {
			super();

			this.element = '';
			Joomla.loadingLayer('load', document.body);

			this.css = `.autocomplete__wrapper{position:relative}.autocomplete__hint,.autocomplete__input{-webkit-appearance:none;border:2px solid;border-radius:0;box-sizing:border-box;-moz-box-sizing:border-box;-webkit-box-sizing:border-box;margin-bottom:0;width:100%}.autocomplete__input{background-color:transparent;position:relative}.autocomplete__hint{color:#bfc1c3;position:absolute}.autocomplete__input--default{padding:4px}.autocomplete__input--focused{outline-offset:0;outline:3px solid #ffbf47}.autocomplete__input--show-all-values{padding:4px 34px 4px 4px;cursor:pointer}.autocomplete__dropdown-arrow-down{z-index:-1;display:inline-block;position:absolute;right:8px;width:24px;height:24px;top:10px}.autocomplete__menu{background-color:#fff;border:2px solid #0b0c0c;border-top:0;color:#34384b;margin:0;max-height:342px;overflow-x:hidden;padding:0;width:100%;width:calc(100% - 4px)}.autocomplete__menu--visible{display:block}.autocomplete__menu--hidden{display:none}.autocomplete__menu--overlay{box-shadow:rgba(0,0,0,.256863) 0 2px 6px;left:0;position:absolute;top:100%;z-index:100}.autocomplete__menu--inline{position:relative}.autocomplete__option{border-bottom:solid #bfc1c3;border-width:1px 0;cursor:pointer;display:block;position:relative}.autocomplete__option>*{pointer-events:none}.autocomplete__option:first-of-type{border-top-width:0}.autocomplete__option:last-of-type{border-bottom-width:0}.autocomplete__option--odd{background-color:#fafafa}.autocomplete__option--focused,.autocomplete__option:hover{background-color:#005ea5;border-color:#005ea5;color:#fff;outline:0}.autocomplete__option--no-results{background-color:#fafafa;color:#646b6f;cursor:not-allowed}.autocomplete__hint,.autocomplete__input,.autocomplete__option{font-size:16px;line-height:1.25}.autocomplete__hint,.autocomplete__option{padding:4px}@media (min-width:641px){.autocomplete__hint,.autocomplete__input,.autocomplete__option{font-size:19px;line-height:1.31579}}`;
			this.styleEl = document.createElement('style');
			this.styleEl.id = 'joomla-field-category-css';
			this.styleEl.innerHTML = this.css;

			if (!document.head.querySelector('joomla-field-category-css')) {
				document.head.appendChild(this.styleEl)
			}
		}

		connectedCallback() {
			const that = this;
			// Check if custom fields are enabled
			if (this.getAttribute('custom-fields-enabled') !== 'true') {
				return;
			}

			this.element = this.querySelector('select');

			this.check = this.check.bind(this);
			window.accessibleAutocomplete.enhanceSelectElement({
				autoselect: false,
				defaultValue: this.element.options[this.element.options.selectedIndex].innerHTML,
				minLength: 1,
				selectElement: this.element,
				showAllValues: true,
				onEnter: (value) => {
					that.check(value)
				}
			});

			this.values = [];
			this.texts = [];

			[].slice.call(this.element.options).forEach((option) => {
				if (option.value) {
					this.values.push(option.value)
				}
				if (option.innerHTML) {
					// @todo proper clean up
					this.texts.push(option.innerHTML.replace('- ', '').replace('- - ', '').replace('- - - ', '').replace('- - - - ', ''))
				}
			});
			console.log(this.values)
			console.log(this.texts)

			console.log(this.getAttribute('enable-create'))
			if (this.getAttribute('enable-create') === 'true') {
				this.addNew = this.addNew.bind(this)
				this.confirm = this.confirm.bind(this)
				const button = document.createElement('button');
				button.innerText = 'Create'
				button.classList.add('btn')
				button.classList.add('btn-success')
				button.setAttribute('type', 'button')
				button.addEventListener('click', this.addNew);

				this.insertBefore(button, this.select)
			}

			this.categoryHasChanged = this.categoryHasChanged.bind(this);

			if (!this.element.value !== this.getAttribute('custom-fields-cat-id')) {
				this.element.value = this.getAttribute('custom-fields-cat-id');
			}

			this.element.addEventListener('change', this.categoryHasChanged);
		}

		categoryHasChanged() {
			if (this.element.value === parseInt(this.element.parentNode.getAttribute('custom-fields-cat-id'))) {
				return;
			}

			Joomla.loadingLayer('show', document.body);

			document.querySelector('input[name=task]').value = this.element.parentNode.getAttribute('custom-fields-section') + '.reload';
			this.element.form.submit();
		}

		check(value) {
			if (value && this.texts.indexOf(value) === -1) {
				const el = document.createElement('option');
				el.value = 'something';
				el.innerText = value;

				this.element.insertAdjacentElement('afterbegin', el)
			}

		}

		addNew(event) {
			event.target.removeEventListener('click', this.addNew)
			this.newInput = document.createElement('input');
			this.newInput.type = 'text';
			event.target.innerText = 'apply';
			event.target.classList.remove('btn-success');
			event.target.classList.add('btn-warning');
			this.insertBefore(this.newInput, event.target)
			event.target.addEventListener('click', this.confirm);
			this.querySelector('span').style.display = 'none'
		}

		confirm(event) {
			if (this.newInput.value && this.texts.indexOf(this.newInput.value) === -1) {
				const el = document.createElement('option');
				el.value = this.newInput.value;
				[].slice.call(this.element.options).forEach((option) => {
					if (option.selected) {
						option.removeAttribute('selected')
					}
				})
				el.setAttribute('selected', 'selected')
				el.innerText = this.newInput.value;

				this.element.insertAdjacentElement('afterbegin', el)
			}

			event.target.classList.add('btn-success');
			event.target.classList.remove('btn-warning');

			event.target.removeEventListener('click', this.confirm);
			event.target.addEventListener('click', this.addNew);
			this.element = this.querySelector('select')
			this.querySelector('span').remove();
			this.newInput.remove()

			window.accessibleAutocomplete.enhanceSelectElement({
				autoselect: false,
				defaultValue: this.element.options[this.element.options.selectedIndex].innerHTML,
				minLength: 1,
				selectElement: this.element,
				showAllValues: true,
			});
		}
	});
})();

