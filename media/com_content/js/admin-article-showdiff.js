(function() {
	'use strict';

	document.addEventListener('DOMContentLoaded', function() {
		var diffArea = document.getElementById('diff_area'),
			text1 = diffArea.innerHTML,
			text2 = parent.document.getElementById('jform_articletext_ifr').contentDocument.getElementById('tinymce').innerHTML,
			diffText, diffHtml, span, fragment, spanParent;

		parent.window.onresize = movePopup;
		movePopup();

        diffHtml = JsDiff.diffWords(cleanMCE(text1), cleanMCE(text2));

        fragment = document.createDocumentFragment();

		diffHtml.forEach(function( part ) {
			span = createDiffSpan(part);
			span.className = 'diff_html';
			span.style.display = 'none';
			fragment.appendChild(span);
		});

		diffText = JsDiff.diffWords(cleanTags(text1), cleanTags(text2));
		diffText.forEach(function( part ) {
			span = createDiffSpan(part);
			span.className = 'diff_text';
			span.style.display = 'inline-block';
			fragment.appendChild(span);
		});

		spanParent = document.createElement('div');
		spanParent.id = 'diff_area';
		spanParent.appendChild(fragment);

		while ( diffArea.lastChild ) {
			diffArea.removeChild(diffArea.lastChild);
		}

		diffArea.appendChild(spanParent);

		document.querySelector('.diff-header').addEventListener('click', function() {
			var diffClasses =
				[
					['show', document.querySelectorAll('.diff_html')],
					['show', document.querySelectorAll('.diffhtml-header')],
					['hide', document.querySelectorAll('.diff_text')],
					['hide', document.querySelectorAll('.diff-header')]
				];

			hideOrShowNodeLists(diffClasses);
		});

		document.querySelector('.diffhtml-header').addEventListener('click', function() {
			var diffClasses =
				[
					['hide', document.querySelectorAll('.diff_html')],
					['hide', document.querySelectorAll('.diffhtml-header')],
					['show-inline', document.querySelectorAll('.diff_text')],
					['show', document.querySelectorAll('.diff-header')]
				];

			hideOrShowNodeLists(diffClasses);
		});

	});
})();

/*
** set css display value, depending if just the text or the text with html-tags would be displayed.
** @param  nodeLists
*/
function hideOrShowNodeLists( nodeLists ) {
	var i, j, jLength,
		iLength = nodeLists.length;

	for ( i = 0; i < iLength; ++i ) {
		jLength = nodeLists[i][1].length;

		if ( nodeLists[i][0] === 'show' ) {
			for ( j = 0; j < jLength; j++ ) {
				nodeLists[i][1][j].style.display = 'block';
			}
		}
		else if ( nodeLists[i][0] === 'show-inline' ) {
			for ( j = 0; j < jLength; j++ ) {
				nodeLists[i][1][j].style.display = 'inline-block';
			}
		}
		else if ( nodeLists[i][0] === 'hide' ) {
			for ( j = 0; j < jLength; j++ ) {
				nodeLists[i][1][j].style.display = 'none';
			}
		}
	}
}


// creating a span for diff_html and diff_text
function createDiffSpan( part ) {
	var color, span;

	color = part.added ? '#a6f3a6' : part.removed ? '#f8cbcb' : '';
	span = document.createElement('span');
	span.style.backgroundColor = color;
	span.style.borderRadius = '.2rem';
	span.appendChild(document.createTextNode(part.value));

	return span;
}

// Deletes all HTML-Text it finds in the given text
function cleanTags( text ) {
	var textClean = String(text),
		regexp = new RegExp('<.*?>');

	while ( regexp.test(textClean) ) {
		textClean = textClean.replace(regexp.exec(textClean).toString(), '');
	}

	return textClean;
}

// Deletes all HTML-Text it finds in the given text
function cleanMCE( text ) {
    var textClean = String(text),
        regexp1 = new RegExp('data-mce-href=".*?/"'),
        regexp2 = new RegExp('> <');

    while ( regexp1.test(textClean) ) {
        textClean = textClean.replace(regexp1.exec(textClean).toString(), '');
    }
    while ( regexp2.test(textClean) ) {
        textClean = textClean.replace(regexp2.exec(textClean).toString(), '');
    }

    return textClean;
}

// positioning of the showdiff-popup.
function movePopup() {
	var popupContainer = parent.document.getElementsByClassName('mce-floatpanel')[0],
		popupBody = popupContainer.getElementsByClassName('mce-container-body')[0],
		popupFoot = popupContainer.getElementsByClassName('mce-foot')[0],
		popupFootChild = popupFoot.firstChild,
		popupFootChildBtn = popupFootChild.getElementsByClassName('mce-btn')[0],
		editor = parent.document.getElementById('jform_articletext_ifr').parentNode,
		editTop = editor.getBoundingClientRect().top,
		editWidth = (editor.offsetWidth) - 10;

	popupContainer.style.top = editTop + 'px';
	popupContainer.style.left = '35px';
	popupContainer.style.width = editWidth + 'px';
	popupBody.style.width = editWidth + 'px';
	popupFoot.style.width = editWidth + 'px';
	popupFootChild.style.width = editWidth + 'px';
	popupFootChildBtn.style.left = 'unset';
	popupFootChildBtn.style.right = '15px';
}
