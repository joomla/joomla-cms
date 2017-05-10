/**
 * @package     Joomla.JavaScript
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

Joomla = window.Joomla || {};

(function(window, Joomla) {
	Joomla.Highlighter = function(_options){
		var words, options = {
			    autoUnhighlight: true,
			    caseSensitive: false,
			    startElement: false,
			    endElement: false,
			    elements: [],
			    className: 'highlight',
			    onlyWords: true,
			    tag: 'span'
		    },

		    highlight = function (words) {
			    if (words.constructor === String) {
				    words = [words];
			    }
			    if (options.autoUnhighlight) {
				    unhighlight(words);
			    }
			    var pattern = options.onlyWords ? '\b' + pattern + '\b' : '(' + words.join('\\b|\\b') + ')',
			        regex = new RegExp(pattern, options.caseSensitive ? '' : 'i');
			    options.elements.map(function(el){
				    recurse(el, regex, options.className);
			    });
			    return this;
		    },

		    unhighlight = function (words) {
			    if (words.constructor === String) {
				    words = [words];
			    }

			    var $elements, tn;
			    words.map(function(word){
				    word = (options.caseSensitive ? word : word.toUpperCase());
				    if (words[word]) {
					    $elements = [].splice.call(document.querySelectorAll(words[word]));
					    $elements.removeClass();
					    $elements.forEach(function (el) {
						    tn = document.createTextNode(el.tagName);
						    el.parentNode.replaceChild(tn, el);
					    });
				    }
			    });
			    return this;
		    },

		    recurse = function (node, regex, klass) {
			    if (node.nodeType === 3) {
				    var match = node.nodeValue.match(regex), highlight, $highlight, wordNode, wordClone, comparer, i;
				    if (match) {
					    highlight = document.createElement(options.tag);
					    $highlight = highlight;
					    $highlight.classList.add(klass);
					    wordNode = node.splitText(match.index);
					    wordNode.splitText(match[0].length);
					    wordClone = wordNode.cloneNode(true);
					    $highlight.appendChild(wordClone);
					    document.querySelector(wordNode).replaceWith(highlight)
					    $highlight.setAttribute('rel', $highlight.innerText);
					    comparer = $highlight.innerText
					    if (!options.caseSensitive) {
						    comparer = $highlight.innerText.toUpperCase();
					    }
					    if (!words[comparer]) {
						    words[comparer] = [];
					    }
					    words[comparer].push(highlight);
					    return 1;
				    }
			    } else if ((node.nodeType === 1 && node.childNodes) && !/(script|style|textarea|iframe)/i.test(node.tagName) && !(node.tagName === options.tag.toUpperCase() && node.className === klass)) {
				    for (i = 0; i < node.childNodes.length; i++) {
					    i += recurse(node.childNodes[i], regex, klass);
				    }
			    }
			    return 0;
		    },

		    getElements = function ($start, $end) {
			    var $next = $start.next();
			    if ($next.getAttribute('id') !== $end.getAttribute('id')) {
				    options.elements.push($next);
				    getElements($next, $end);
			    }
		    },

		    initialize = function(_options) {
			    Joomla.extend(options, _options);
			    getElements(document.querySelector(options.startElement), document.querySelector(options.endElement));
			    words = [];
		    };

		initialize(_options);

		return {
			highlight: highlight,
			unhighlight : unhighlight
		};
	};

	document.addEventListener('DOMContentLoaded',  function() {
		var options = Joomla.getOptions('js-highlighter');

		if (options.length) {

			var start = document.getElementById(options.start);
			var end = document.getElementById(options.end);
			if (!start || !end || !Joomla.Highlighter) {
				return true;
			}
			highlighter = new Joomla.Highlighter({
				startElement: start,
				endElement: end,
				className: options['class'],
				onlyWords: false,
				tag: options.tag
			}).highlight(options.terms);
			document.querySelector(start).remove();
			document.querySelector(end).remove();
		}
	});
})(window, Joomla);
