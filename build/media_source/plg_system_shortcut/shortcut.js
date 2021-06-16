function handleKeyPressEvent(e) {
  console.log(e);
  // On Press ALT + S
  if (
    (window.navigator.platform.match("Mac") ? e.metaKey : e.altKey) &&
    e.key.toLowerCase() == "s"
  ) {
    let actionBtn = document.querySelector(
      "joomla-toolbar-button button.button-apply"
    );
    if (actionBtn) {
      console.log("Save");
      e.preventDefault();
      actionBtn.click();
    }
  }
  // On Press ALt + N
  else if (
    (window.navigator.platform.match("Mac") ? e.metaKey : e.altKey) &&
    e.key.toLowerCase() == "n"
  ) {
    let actionBtn = document.querySelector(
      "joomla-toolbar-button button.button-new"
    );
    if (actionBtn) {
      console.log("New");
      e.preventDefault();
      actionBtn.click();
    }
  }

  // On Press ALT + O
  else if (
    (window.navigator.platform.match("Mac") ? e.metaKey : e.altKey) &&
    e.key.toLowerCase() == "o"
  ) {
    let actionBtn = document.querySelector(
      "joomla-toolbar-button button.button-options"
    );
    if (actionBtn) {
      console.log("Options");
      e.preventDefault();
      actionBtn.click();
    }
  }

  // On Press ALT + w
  else if (
    (window.navigator.platform.match("Mac") ? e.metaKey : e.altKey) &&
    e.key.toLowerCase() == "w"
  ) {
    let actionBtn = document.querySelector(
      "joomla-toolbar-button button.button-save"
    );
    if (actionBtn) {
      console.log("Save & Close");
      e.preventDefault();
      actionBtn.click();
    }
  }
   // On Press ALT + N
   else if (
    (window.navigator.platform.match("Mac") ? e.metaKey : e.altKey) &&
    e.key.toLowerCase() == "n"
  ) {
    let actionBtn = document.querySelector(
      "joomla-toolbar-button button.button-save-new"
    );
    if (actionBtn) {
      console.log("Save & New");
      e.preventDefault();
      actionBtn.click();
    }
  }
  // On Press SHIFT + ALT + C
  else if (
    (window.navigator.platform.match("Mac") ? e.metaKey : e.altKey) &&
      e.shiftKey &&
    e.key.toLowerCase() == "c"
  ) {
    let actionBtn = document.querySelector(
      "joomla-toolbar-button button.button-save-copy"
    );
    if (actionBtn) {
      console.log("Save as Copy");
      e.preventDefault();
      actionBtn.click();
    }
  }
  // On Press ALT + H
  else if (
    (window.navigator.platform.match("Mac") ? e.metaKey : e.altKey) &&
    e.key.toLowerCase() == "h"
  ) {
    let actionBtn = document.querySelector(
      "joomla-toolbar-button button.button-help"
    );
    if (actionBtn) {
      console.log("Help");
      e.preventDefault();
      actionBtn.click();
    }
  }
  // On Pres ALT + Q
  else if (
    (window.navigator.platform.match("Mac") ? e.metaKey : e.altKey) &&
  e.key.toLowerCase() == "q"
) {
    let actionBtn = document.querySelector(
      "joomla-toolbar-button button.button-cancel"
    );
    if (actionBtn) {
      console.log("Close");
      e.preventDefault();
      actionBtn.click();
    }
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
