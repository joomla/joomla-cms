(function() {
	'use strict';

	document.addEventListener('DOMContentLoaded', function() {
		var DURATION = 150,
		    ringElem = null,
		    movingId = 0,
		    prevFocused = null,
		    keyDownTime = 0,
		    win = window,
		    doc = document,
		    body = doc.body;

		doc.addEventListener('keydown', function(event) {
			var code = event.which;

			// Show animation only upon Tab or Arrow keys press.
			if (code === 9 || (code > 36 && code < 41)) {
				keyDownTime = Date.now();
			}
		}, false);

		doc.addEventListener('focus', function(event) {
			var target = event.target;

			if (target.id === 'flying-focus') {
				return;
			}

			var isFirstFocus = false;

			if (!ringElem) {
				isFirstFocus = true;
				initialize();
			}

			if (body.contains(target))
			{
				var offset            = offsetOf(target);
				ringElem.style.left   = offset.left + 'px';
				ringElem.style.top    = offset.top + 'px';
				ringElem.style.width  = target.offsetWidth + 'px';
				ringElem.style.height = target.offsetHeight + 'px';
			}

			if (isFirstFocus || !isJustPressed()) {
				return;
			}

			onEnd();
			target.classList.add('flying-focus_target');
			ringElem.classList.add('flying-focus_visible');
			prevFocused = target;
			movingId = setTimeout(onEnd, DURATION);
		}, true);

		doc.addEventListener('blur', function() {
			onEnd();
		}, true);

		function initialize() {
			// use uniq element name to decrease the chances of a conflict with website styles
			ringElem = doc.createElement('flying-focus');
			ringElem.id = 'flying-focus';
			ringElem.style.transitionDuration = ringElem.style.WebkitTransitionDuration = DURATION / 1000 + 's';
			body.appendChild(ringElem);
		}

		function onEnd() {
			if (!movingId) {
				return;
			}

			clearTimeout(movingId);
			movingId = 0;
			ringElem.classList.remove('flying-focus_visible');
			prevFocused.classList.remove('flying-focus_target');
			prevFocused = null;
		}

		function isJustPressed() {
			return Date.now() - keyDownTime < 42
		}

		function offsetOf(elem) {
			var rect       = elem.getBoundingClientRect();
			var clientLeft = doc.clientLeft || body.clientLeft;
			var clientTop  = doc.clientTop  || body.clientTop;
			var scrollLeft = win.pageXOffset || doc.scrollLeft || body.scrollLeft;
			var scrollTop  = win.pageYOffset || doc.scrollTop  || body.scrollTop;
			var left       = rect.left + scrollLeft - clientLeft;
			var top        = rect.top  + scrollTop  - clientTop;

			return {
				top: top || 0,
				left: left || 0
			};
		}
	});
})();
