document.addEventListener("DOMContentLoaded", function(event) {
  const mm1 = new MetisMenu("#menu").on("shown.metisMenu", function(event) {
    window.addEventListener("click", function mmClick1(e) {
      if (!event.target.contains(e.target)) {
        mm1.hide(event.detail.shownElement);
        window.removeEventListener("click", mmClick1);
      }
    });
  });
});
