/**
 * Created by icampus on 28.08.2017.
 */


jQuery(document).ready(function () {
        var text1 = document.getElementById("diff_area").innerHTML,
            text2 = parent.document.getElementById("jform_articletext_ifr").contentDocument.getElementById("tinymce").innerHTML,
            diff_text, diff_html, span, color, fragment, spanParent;

        parent.window.onresize = popup_position;
        popup_position();

        diff_html = JsDiff.diffWords(text1, text2);
        fragment = document.createDocumentFragment();

        diff_html.forEach(function (part) {
            color = part.added ? '#a6f3a6' : part.removed ? '#f8cbcb' : '';
            span = document.createElement('span');
            span.style.backgroundColor = color;
            span.style.borderRadius = '.2rem';
            span.appendChild(document.createTextNode(part.value));
            span.className = "diff_html";
            span.style.display = "none";
            fragment.appendChild(span);
        });

        diff_text = JsDiff.diffWords(clean_tags(text1), clean_tags(text2));
        diff_text.forEach(function (part) {
            color = part.added ? '#a6f3a6' : part.removed ? '#f8cbcb' : '';
            span = document.createElement('span');
            span.style.backgroundColor = color;
            span.style.borderRadius = '.2rem';
            span.appendChild(document.createTextNode(part.value));
            span.className = "diff_text";
            fragment.appendChild(span);
        });

        spanParent = document.createElement('div');
        spanParent.id = "diff_area";
        spanParent.appendChild(fragment);

        document.getElementById("diff_area").replaceWith(spanParent);

    }
);

//Deletes all HTML-Text it finds in the given text
function clean_tags(text) {
    var text_clean = new String(text),
        regexp = new RegExp('<.*?>');

    while (regexp.test(text_clean)) {
        text_clean = text_clean.replace(regexp.exec(text_clean).toString(), '');
    }
    return text_clean;
}

/*
/ positioning of the show-diff-popup.
*/
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