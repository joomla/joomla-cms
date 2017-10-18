(function () {
    "use strict";

    document.addEventListener('DOMContentLoaded', function () {
        var diffArea = document.getElementById("diff_area"),
            text1 = diffArea.innerHTML,
            text2 = parent.document.getElementById("jform_articletext_ifr").contentDocument.getElementById("tinymce").innerHTML,
            diff_text, diff_html, span, fragment, spanParent;

        parent.window.onresize = popup_position;
        popup_position();

        diff_html = JsDiff.diffWords(text1, text2);
        fragment = document.createDocumentFragment();

        diff_html.forEach(function (part) {
            span = create_diff_span(part);
            span.className = "diff_html";
            span.style.display = "none";
            fragment.appendChild(span);
        });

        diff_text = JsDiff.diffWords(clean_tags(text1), clean_tags(text2));
        diff_text.forEach(function (part) {
            span = create_diff_span(part);
            span.className = "diff_text";
            fragment.appendChild(span);
        });

        spanParent = document.createElement('div');
        spanParent.id = "diff_area";
        spanParent.appendChild(fragment);

        while (diffArea.lastChild) {
            diffArea.removeChild(diffArea.lastChild);
        }

        diffArea.appendChild(spanParent);

        document.querySelector('.diff-header').addEventListener('click', function () {
            var diff_html_text_header =
                [
                    ["show", document.querySelectorAll(".diff_html")],
                    ["show", document.querySelectorAll(".diffhtml-header")],
                    ["hide", document.querySelectorAll(".diff_text")],
                    ["hide", document.querySelectorAll(".diff-header")]
                ];

            hideOrShowNodeLists(diff_html_text_header);
        });

        document.querySelector('.diffhtml-header').addEventListener('click', function () {
            var diff_html_text_header =
                    [
                        ["hide", document.querySelectorAll(".diff_html")],
                        ["hide", document.querySelectorAll(".diffhtml-header")],
                        ["show", document.querySelectorAll(".diff_text")],
                        ["show", document.querySelectorAll(".diff-header")]
                    ];

            hideOrShowNodeLists(diff_html_text_header);
        });

    });
})();

/**
 * set css display value, depending if just the text or the text with html-tags would be displayed.
 * @param nodeLists
 */
function hideOrShowNodeLists(nodeLists) {
    var i, j;

    for (i = 0; i < nodeLists.length; ++i) {

        if (nodeLists[i][0] === "show") {
            for (j = 0; j < nodeLists[i][1].length; j++) {
                nodeLists[i][1][j].style.display = '';
            }
        }
        else if (nodeLists[i][0] === "hide") {
            for (j = 0; j < nodeLists[i][1].length; j++) {
                nodeLists[i][1][j].style.display = 'none';
            }
        }
    }
}

/**
* creating a span for diff_html and diff_text
**/
function create_diff_span(part) {
    var color, span;
    color = part.added ? '#a6f3a6' : part.removed ? '#f8cbcb' : '';
    span = document.createElement('span');
    span.style.backgroundColor = color;
    span.style.borderRadius = '.2rem';
    span.appendChild(document.createTextNode(part.value));
    return span;
}

/**
 * Deletes all HTML-Text it finds in the given text
 **/
function clean_tags(text) {
    var text_clean = String(text),
        regexp = new RegExp('<.*?>');

    while (regexp.test(text_clean)) {
        text_clean = text_clean.replace(regexp.exec(text_clean).toString(), '');
    }
    return text_clean;
}

/**
* positioning of the showdiff-popup.
**/
function popup_position() {
    var popupContainer = parent.document.getElementsByClassName("mce-floatpanel")[0],
        popupBody = popupContainer.getElementsByClassName("mce-container-body")[0],
        popupFoot = popupContainer.getElementsByClassName("mce-foot")[0],
        popupFootChild = popupFoot.firstChild,
        popupFootChildBtn = popupFootChild.getElementsByClassName("mce-btn")[0],
        editor = parent.document.getElementById("jform_articletext_ifr").parentNode,
        editTop = editor.getBoundingClientRect().top,
        editWidth = (editor.offsetWidth) - 10;

    popupContainer.style.top = editTop + 'px';
    popupContainer.style.left = '35px';
    popupContainer.style.width = editWidth + 'px';
    popupBody.style.width = editWidth + 'px';
    popupFoot.style.width = editWidth + 'px';
    popupFootChild.style.width = editWidth + 'px';
    popupFootChildBtn.style.left = "unset";
    popupFootChildBtn.style.right = '15px';
}
