// Copy to Clipboard for Article ID and URL
(function() {
  function showTooltip(el, msg) {
    el.setAttribute('data-original-title', msg);
    el.classList.add('copied');
    setTimeout(function() {
      el.classList.remove('copied');
      el.setAttribute('data-original-title', el.getAttribute('data-tooltip'));
    }, 1200);
  }

  document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.copy-clipboard-btn').forEach(function(btn) {
      btn.addEventListener('click', function(e) {
        e.preventDefault();
        var value = btn.getAttribute('data-copy-value');
        if (!value) return;
        navigator.clipboard.writeText(value).then(function() {
          showTooltip(btn, 'Copied!');
        });
      });
    });
  });
})();
