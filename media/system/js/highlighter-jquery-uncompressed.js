/**
 * @package		Joomla.JavaScript
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// Only define the Joomla namespace if not defined.
if (typeof(Joomla) === 'undefined') {
	var Joomla = {};
}

Joomla.Highlighter = function(_options){
    var $, words, options = {
		autoUnhighlight: true,
		caseSensitive: false,
		startElement: false,
		endElement: false,
		elements: [],
		className: 'highlight',
		onlyWords: true,
		tag: 'span'
	};

	var highlight = function (words) {
		if (words.constructor === String) {
			words = [words];
		}
		if (options.autoUnhighlight) {
			unhighlight(words);
		}
		var pattern = options.onlyWords ? '\b' + pattern + '\b' : '(' + words.join('\\b|\\b') + ')';
		var regex = new RegExp(pattern, options.caseSensitive ? '' : 'i');
		options.elements.map(function(el){
		    recurse(el, regex, options.className);
		});
		return this;
	}

	var unhighlight = function (words) {
		if (words.constructor === String) {
			words = [words];
		}
		words.map(function(word){
		    word = (options.caseSensitive ? word : word.toUpperCase());
			if (words[word]) {
				var $elements = $(words[word]);
				$elements.removeClass();
				$elements.each(function (index, el) {
					var tn = document.createTextNode($(el).text());
					el.parentNode.replaceChild(tn, el);
				});
			}
		});
		return this;
	}

	var recurse = function (node, regex, klass) {
		if (node.nodeType === 3) {
			var match = node.nodeValue.match(regex);
			if (match) {
				var highlight = document.createElement(options.tag);
				$highlight = $(highlight);
				$highlight.addClass(klass);
				var wordNode = node.splitText(match.index);
				wordNode.splitText(match[0].length);
				var wordClone = wordNode.cloneNode(true);
				$highlight.append(wordClone);
				$(wordNode).replaceWith(highlight)
				$highlight.attr('rel', $highlight.text());
				var comparer = $highlight.text()
				if (!options.caseSensitive) {
					comparer = $highlight.text().toUpperCase();
				}
				if (!words[comparer]) {
					words[comparer] = [];
				}
				words[comparer].push(highlight);
				return 1;
			}
		} else if ((node.nodeType === 1 && node.childNodes) && !/(script|style|textarea|iframe)/i.test(node.tagName) && !(node.tagName === options.tag.toUpperCase() && node.className === klass)) {
			for (var i = 0; i < node.childNodes.length; i++) {
				i += recurse(node.childNodes[i], regex, klass);
			}
		}
		return 0;
	}

	var getElements = function ($start, $end) {
		var $next = $start.next();
		if ($next.attr('id') !== $end.attr('id')) {
			options.elements.push($next.get(0));
			getElements($next, $end);
		}
	}
	
    var initialize = function(_options) {
        $ = jQuery.noConflict();
        $.extend(options, _options);
        getElements($(options.startElement), $(options.endElement));
        words = [];
    }

	initialize(_options);
	
	return {	    
	    highlight: highlight,
	    unhighlight : unhighlight
	}
}
