function handleKeyPressEvent(e) {
    console.log(e);
    // On Press CTRL + S
    if (
      (window.navigator.platform.match("Mac") ? e.metaKey : e.ctrlKey) &&
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
    // On Press SHIFT + CTRL + C
    else if (
      (window.navigator.platform.match("Mac") ? e.metaKey : e.ctrlKey) &&
      e.shiftKey &&
      e.key.toLowerCase() == "c"
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
    // On Pres Escape
    else if (e.key == "Escape") {
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
  