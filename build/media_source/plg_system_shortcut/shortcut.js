addShortcut = (event, selector) => {
  let actionBtn = document.querySelector(selector);
  if (actionBtn) {
    console.log(selector);
    event.preventDefault();
    actionBtn.click();
  }
};
function handleKeyPressEvent(e) {
  if (window.navigator.platform.match("Mac") ? e.metaKey : e.altKey) {
    // On Press ALT + S
    if (e.key.toLowerCase() == "s") {
      addShortcut(e, "joomla-toolbar-button button.button-apply");
    }
    // On Press ALt + N
    else if (e.key.toLowerCase() == "n") {
      addShortcut(e, "joomla-toolbar-button button.button-new");
    }
    // On Press ALT + W
    else if (e.key.toLowerCase() == "w") {
      addShortcut(e, "joomla-toolbar-button button.button-save");
    }
    // On Press ALT + N
    else if (e.shiftKey && e.key.toLowerCase() == "n") {
      addShortcut(e, "joomla-toolbar-button button.button-save-new");
    }
  }
  // On Press SHIFT + ALT + C
  else if (e.shiftKey && e.key.toLowerCase() == "c") {
    addShortcut(e, "joomla-toolbar-button button.button-save-copy");
  }
  // On Press ALT + H
  else if (e.key.toLowerCase() == "h") {
    addShortcut(e, "joomla-toolbar-button button.button-help");
  }
  // On Press ALT + Q
  else if (e.key.toLowerCase() == "q") {
    addShortcut(e, "joomla-toolbar-button button.button-cancel");
  }
}

window.addEventListener("DOMContentLoaded", (event) => {
  document.addEventListener(
    "keydown",
    function (e) {
      handleKeyPressEvent(e);
    },
    false
  );

  try {
    tinyMCE.activeEditor.on("keydown", function (e) {
      handleKeyPressEvent(e);
    });
  } catch (e) {}
});
