(function (global, factory) {
  typeof exports === 'object' && typeof module !== 'undefined' ? module.exports = factory() :
  typeof define === 'function' && define.amd ? define(factory) :
  (global = typeof globalThis !== 'undefined' ? globalThis : global || self, global.Jooa11y = factory());
})(this, (function () { 'use strict';

  /**
   * Utility methods
   */

  // Determine element visibility
  const isElementHidden = ($el) => {
    if ($el.getAttribute('hidden') || ($el.offsetWidth === 0 && $el.offsetHeight === 0)) {
      return true;
    } else {
      const compStyles = getComputedStyle($el);
      return compStyles.getPropertyValue('display') === 'none';
    }
  };

  // Escape HTML, encode HTML symbols
  const escapeHTML = (text) => {
    const $div = document.createElement('div');
    $div.textContent = text;
    return $div.innerHTML.replaceAll('"', '&quot;').replaceAll("'", '&#039;').replaceAll("`", '&#x60;');
  };

  /**
   * Jooa11y Translation object
   */
  const Lang = {
    langStrings: {},
    addI18n: function (strings) {
      this.langStrings = strings;
    },
    _: function (string) {
      return this.translate(string);
    },
    sprintf: function (string, ...args) {
      let transString = this._(string);

      if (args && args.length) {
        args.forEach((arg) => {
          transString = transString.replace(/%\([a-zA-z]+\)/, arg);
        });
      }
      return transString;
    },
    translate: function (string) {
      return this.langStrings[string] || string;
    },
  };

  if (window.Joomla && Joomla.Text && Joomla.Text._)
  {
    const stringPrefix = 'JOOMLA_A11Y_CHECKER_';
    Lang.translate = (string) => {
      return Joomla.Text._(stringPrefix + string, string);
    };
  }

  /**
   * Jooa11y default options
   */
    const defaultOptions = {
      langCode: 'en',

      // Target area to scan.
      checkRoot: 'body', // A content container

      // Readability configuration.
      readabilityRoot: 'body',
      readabilityLang: 'en',

      // Inclusions and exclusions. Use commas to seperate classes or elements.
      containerIgnore: '.jooa11y-ignore', // Ignore specific regions.
      outlineIgnore: '', // Exclude headings from outline panel.
      headerIgnore: '', // Ignore specific headings. E.g. "h1.jumbotron-heading"
      imageIgnore: '', // Ignore specific images.
      linkIgnore: '', // Ignore specific links.
      linkIgnoreSpan: 'noscript, span.sr-only-example', // Ignore specific classes within links. Example: <a href="#">learn more <span class="sr-only-example">(opens new tab)</span></a>.
      linksToFlag: '', // Links you don't want your content editors pointing to (e.g. development environments).

      // Embedded content.
      videoContent: "video, [src*='youtube.com'], [src*='vimeo.com'], [src*='yuja.com'], [src*='panopto.com']",
      audioContent: "audio, [src*='soundcloud.com'], [src*='simplecast.com'], [src*='podbean.com'], [src*='buzzsprout.com'], [src*='blubrry.com'], [src*='transistor.fm'], [src*='fusebox.fm'], [src*='libsyn.com']",
      embeddedContent: '',

      // Alt Text stop words.
      suspiciousAltWords: ['image', 'graphic', 'picture', 'photo'],
      placeholderAltStopWords: [
        'alt',
        'image',
        'photo',
        'decorative',
        'photo',
        'placeholder',
        'placeholder image',
        'spacer',
        '.',
      ],
      // Link Text stop words
      partialAltStopWords: [
        'click',
        'click here',
        'click here for more',
        'click here to learn more',
        'click here to learn more.',
        'check out',
        'download',
        'download here',
        'download here.',
        'find out',
        'find out more',
        'find out more.',
        'form',
        'here',
        'here.',
        'info',
        'information',
        'link',
        'learn',
        'learn more',
        'learn more.',
        'learn to',
        'more',
        'page',
        'paper',
        'read more',
        'read',
        'read this',
        'this',
        'this page',
        'this page.',
        'this website',
        'this website.',
        'view',
        'view our',
        'website',
        '.',
      ],
      warningAltWords: [
        '< ',
        ' >',
        'click here',
      ],
      // Link Text (Advanced)
      newWindowPhrases: [
        'external',
        'new tab',
        'new window',
        'pop-up',
        'pop up',
      ],
      // Link Text (Advanced). Only some items in list would need to be translated.
      fileTypePhrases: [
        'document',
        'pdf',
        'doc',
        'docx',
        'word',
        'mp3',
        'ppt',
        'text',
        'pptx',
        'powerpoint',
        'txt',
        'exe',
        'dmg',
        'rtf',
        'install',
        'windows',
        'macos',
        'spreadsheet',
        'worksheet',
        'csv',
        'xls',
        'xlsx',
        'video',
        'mp4',
        'mov',
        'avi',
      ],
    };
    defaultOptions.embeddedContent = `${defaultOptions.videoContent}, ${defaultOptions.audioContent}`;

    /**
     * Load and validate options
     *
     * @param {Jooa11y}  instance
     * @param {Object} customOptions
     * @returns {Object}
     */
    const loadOptions = (instance, customOptions) => {
      const options = customOptions ? Object.assign(defaultOptions, customOptions) : defaultOptions;

      // Check required options
      ['langCode', 'checkRoot'].forEach((option) => {
        if (!options[option]) {
          throw new Error(`Option [${option}] is required`);
        }
      });

      if (!options.readabilityRoot) {
        options.readabilityRoot = options.checkRoot;
      }

      // Container ignores apply to self and children.
      if (options.containerIgnore) {
        let containerSelectors = options.containerIgnore.split(',').map((el) => {
          return `${el} *, ${el}`
        });

        options.containerIgnore = '[aria-hidden="true"], #jooa11y-container *, .jooa11y-instance *, ' + containerSelectors.join(', ');
      } else {
        options.containerIgnore = '[aria-hidden="true"], #jooa11y-container *, .jooa11y-instance *';
      }
      instance.containerIgnore = options.containerIgnore;

      // Images ignore
      instance.imageIgnore = instance.containerIgnore + ', [role="presentation"], [src^="https://trck.youvisit.com"]';

      if (options.imageIgnore) {
        instance.imageIgnore = options.imageIgnore + ',' + instance.imageIgnore;
      }

      // Ignore specific headings
      instance.headerIgnore = options.containerIgnore;

      if (options.headerIgnore) {
        instance.headerIgnore = options.headerIgnore + ',' + instance.headerIgnore;
      }

      // Links ignore defaults plus jooa11y links.
      instance.linkIgnore = instance.containerIgnore + ', [aria-hidden="true"], .anchorjs-link';

      if (options.linkIgnore) {
        instance.linkIgnore = options.linkIgnore + ',' + instance.linkIgnore;
      }

      return options;
    };

  /**
   * Jooa11y class
   */
  class Jooa11y {
          constructor(options) {
            this.containerIgnore = '';
            this.imageIgnore = '';
            this.headerIgnore = '';
            this.linkIgnore = '';

            // Load options
            this.options = loadOptions(this, options);

              //Icon on the main toggle. Easy to replace.
              const MainToggleIcon =
                  "<svg role='img' focusable='false' width='35px' height='35px' aria-hidden='true' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'><path fill='#ffffff' d='M256 48c114.953 0 208 93.029 208 208 0 114.953-93.029 208-208 208-114.953 0-208-93.029-208-208 0-114.953 93.029-208 208-208m0-40C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 56C149.961 64 64 149.961 64 256s85.961 192 192 192 192-85.961 192-192S362.039 64 256 64zm0 44c19.882 0 36 16.118 36 36s-16.118 36-36 36-36-16.118-36-36 16.118-36 36-36zm117.741 98.023c-28.712 6.779-55.511 12.748-82.14 15.807.851 101.023 12.306 123.052 25.037 155.621 3.617 9.26-.957 19.698-10.217 23.315-9.261 3.617-19.699-.957-23.316-10.217-8.705-22.308-17.086-40.636-22.261-78.549h-9.686c-5.167 37.851-13.534 56.208-22.262 78.549-3.615 9.255-14.05 13.836-23.315 10.217-9.26-3.617-13.834-14.056-10.217-23.315 12.713-32.541 24.185-54.541 25.037-155.621-26.629-3.058-53.428-9.027-82.141-15.807-8.6-2.031-13.926-10.648-11.895-19.249s10.647-13.926 19.249-11.895c96.686 22.829 124.283 22.783 220.775 0 8.599-2.03 17.218 3.294 19.249 11.895 2.029 8.601-3.297 17.219-11.897 19.249z'/></svg>";

              const jooa11ycontainer = document.createElement("div");
              jooa11ycontainer.setAttribute("id", "jooa11y-container");
              jooa11ycontainer.setAttribute("role", "region");
              jooa11ycontainer.setAttribute("lang", this.options.langCode);
              jooa11ycontainer.setAttribute("aria-label", Lang._('CONTAINER_LABEL'));

              let loadContrastPreference =
                  localStorage.getItem("jooa11y-remember-contrast") === "On";

              let loadLabelsPreference =
                  localStorage.getItem("jooa11y-remember-labels") === "On";

              let loadChangeRequestPreference =
                  localStorage.getItem("jooa11y-remember-links-advanced") === "On";

              let loadReadabilityPreference =
                  localStorage.getItem("jooa11y-remember-readability") === "On";

              jooa11ycontainer.innerHTML =

                  //Main toggle button.
                  `<button type="button" aria-expanded="false" id="jooa11y-toggle" aria-describedby="jooa11y-notification-badge" aria-label="${Lang._('MAIN_TOGGLE_LABEL')}">
                    ${MainToggleIcon}
                    <div id="jooa11y-notification-badge">
                        <span id="jooa11y-notification-count"></span>
                    </div>
                </button>` +

                  //Start of main container.
                  `<div id="jooa11y-panel">` +

                  //Page Outline tab.
                  `<div id="jooa11y-outline-panel" role="tabpanel" aria-labelledby="jooa11y-outline-header">
                <div id="jooa11y-outline-header" class="jooa11y-header-text">
                    <h2 tabindex="-1">${Lang._('PAGE_OUTLINE')}</h2>
                </div>
                <div id="jooa11y-outline-content">
                    <ul id="jooa11y-outline-list"></ul>
                </div>` +

                  //Readability tab.
                  `<div id="jooa11y-readability-panel">
                    <div id="jooa11y-readability-content">
                        <h2 class="jooa11y-header-text-inline">${Lang._('READABILITY')}</h2>
                        <p id="jooa11y-readability-info"></p>
                        <ul id="jooa11y-readability-details"></ul>
                    </div>
                </div>
            </div>` + //End of Page Outline tab.

                  //Settings tab.
                  `<div id="jooa11y-settings-panel" role="tabpanel" aria-labelledby="jooa11y-settings-header">
                <div id="jooa11y-settings-header" class="jooa11y-header-text">
                    <h2 tabindex="-1">${Lang._('SETTINGS')}</h2>
                </div>
                <div id="jooa11y-settings-content">
                    <ul id="jooa11y-settings-options">
                        <li>
                            <label id="check-contrast" for="jooa11y-contrast-toggle">${Lang._('CONTRAST')}</label>
                            <button id="jooa11y-contrast-toggle"
                            aria-labelledby="check-contrast"
                            class="jooa11y-settings-switch"
                            aria-pressed="${
                                loadContrastPreference ? "true" : "false"
                            }">${loadContrastPreference ? Lang._('ON') : Lang._('OFF')}</button>
                        </li>
                        <li>
                            <label id="check-labels" for="jooa11y-labels-toggle">${Lang._('FORM_LABELS')}</label>
                            <button id="jooa11y-labels-toggle" aria-labelledby="check-labels" class="jooa11y-settings-switch"
                            aria-pressed="${
                                loadLabelsPreference ? "true" : "false"
                            }">${loadLabelsPreference ? Lang._('ON') : Lang._('OFF')}</button>
                        </li>
                        <li>
                            <label id="check-changerequest" for="jooa11y-links-advanced-toggle">${Lang._('LINKS_ADVANCED')}<span class="jooa11y-badge">AAA</span></label>
                            <button id="jooa11y-links-advanced-toggle" aria-labelledby="check-changerequest" class="jooa11y-settings-switch"
                            aria-pressed="${
                                loadChangeRequestPreference ? "true" : "false"
                            }">${loadChangeRequestPreference ? Lang._('ON') : Lang._('OFF')}</button>
                        </li>
                        <li>
                            <label id="check-readability" for="jooa11y-readability-toggle">${Lang._('READABILITY')}<span class="jooa11y-badge">AAA</span></label>
                            <button id="jooa11y-readability-toggle" aria-labelledby="check-readability" class="jooa11y-settings-switch"
                            aria-pressed="${
                                loadReadabilityPreference ? "true" : "false"
                            }">${loadReadabilityPreference ? Lang._('ON') : Lang._('OFF')}</button>
                        </li>
                        <li>
                            <label id="dark-mode" for="jooa11y-theme-toggle">${Lang._('DARK_MODE')}</label>
                            <button id="jooa11y-theme-toggle" aria-labelledby="dark-mode" class="jooa11y-settings-switch"></button>
                        </li>
                    </ul>
                </div>
            </div>` +

                  //Console warning messages.
                  `<div id="jooa11y-panel-alert">
                <div class="jooa11y-header-text">
                    <button id="jooa11y-close-alert" class="jooa11y-close-btn" aria-label="${Lang._('ALERT_CLOSE')}" aria-describedby="jooa11y-alert-heading jooa11y-panel-alert-text"></button>
                    <h2 id="jooa11y-alert-heading">${Lang._('ALERT_TEXT')}</h2>
                </div>
                <p id="jooa11y-panel-alert-text"></p>
                <div id="jooa11y-panel-alert-preview"></div>
            </div>` +

                  //Main panel that conveys state of page.
                  `<div id="jooa11y-panel-content">
                <button id="jooa11y-cycle-toggle" type="button" aria-label="${Lang._('SHORTCUT_SR')}">
                    <div class="jooa11y-panel-icon"></div>
                </button>
                <div id="jooa11y-panel-text"><p id="jooa11y-status" aria-live="polite"></p></div>
            </div>` +

                  //Show Outline & Show Settings button.
                  `<div id="jooa11y-panel-controls" role="tablist" aria-orientation="horizontal">
                <button type="button" role="tab" aria-expanded="false" id="jooa11y-outline-toggle" aria-controls="jooa11y-outline-panel">
                    ${Lang._('SHOW_OUTLINE')}
                </button>
                <button type="button" role="tab" aria-expanded="false" id="jooa11y-settings-toggle" aria-controls="jooa11y-settings-panel">
                    ${Lang._('SHOW_SETTINGS')}
                </button>
                <div style="width:35px"></div>
            </div>` +

                  //End of main container.
                  `</div>`;

              document.body.append(jooa11ycontainer);

              //Put before document.ready because of CSS flicker when dark mode is enabled.
              this.settingPanelToggles();

              // Preload before CheckAll function.
              this.jooa11yMainToggle();
              this.sanitizeHTMLandComputeARIA();
              this.initializeJumpToIssueTooltip();
          }

          //----------------------------------------------------------------------
          // Main toggle button
          //----------------------------------------------------------------------
          jooa11yMainToggle() {

              //Keeps checker active when navigating between pages until it is toggled off.
              const jooa11yToggle = document.getElementById("jooa11y-toggle");
              jooa11yToggle.addEventListener('click', (e) => {
                  if (localStorage.getItem("jooa11y-remember-panel") === "Opened") {
                      localStorage.setItem("jooa11y-remember-panel", "Closed");
                      jooa11yToggle.classList.remove("jooa11y-on");
                      jooa11yToggle.setAttribute("aria-expanded", "false");
                      this.resetAll();
                      this.updateBadge();
                      e.preventDefault();
                  } else {
                      localStorage.setItem("jooa11y-remember-panel", "Opened");
                      jooa11yToggle.classList.add("jooa11y-on");
                      jooa11yToggle.setAttribute("aria-expanded", "true");
                      this.checkAll();
                      //Don't show badge when panel is opened.
                      document.getElementById("jooa11y-notification-badge").style.display = 'none';
                      e.preventDefault();
                  }
              });

              //Remember to leave it open
              if (localStorage.getItem("jooa11y-remember-panel") === "Opened") {
                  jooa11yToggle.classList.add("jooa11y-on");
                  jooa11yToggle.setAttribute("aria-expanded", "true");
              }

              //Crudely give a little time to load any other content or slow post-rendered JS, iFrames, etc.
              if (jooa11yToggle.classList.contains("jooa11y-on")) {
                  jooa11yToggle.classList.toggle("loading-jooa11y");
                  jooa11yToggle.setAttribute("aria-expanded", "true");
                  setTimeout(this.checkAll, 800);
              }

              //Escape key to shutdown.
              document.onkeydown = (evt) => {
                  evt = evt || window.event;
                  var isEscape = false;
                  if ("key" in evt) {
                      isEscape = (evt.key === "Escape" || evt.key === "Esc");
                  } else {
                      isEscape = (evt.keyCode === 27);
                  }
                  if (isEscape && document.getElementById("jooa11y-panel").classList.contains("jooa11y-active")) {
                      tippy.hideAll();
                      jooa11yToggle.setAttribute("aria-expanded", "false");
                      jooa11yToggle.classList.remove("jooa11y-on");
                      jooa11yToggle.click();
                      this.resetAll();
                  }
              };
          }

          // ============================================================
          // Helpers: Sanitize HTML and compute ARIA for hyperlinks
          // ============================================================
          sanitizeHTMLandComputeARIA() {

              //Helper: Compute alt text on images within a text node.
              this.computeTextNodeWithImage = function ($el) {
                const imgArray = Array.from($el.querySelectorAll("img"));
                let returnText = "";
                  //No image, has text.
                  if (imgArray.length === 0 && $el.textContent.trim().length > 1) {
                    returnText = $el.textContent.trim();
                  }
                  //Has image, no text.
                  else if (imgArray.length && $el.textContent.trim().length === 0) {
                    let imgalt = imgArray[0].getAttribute("alt");
                      if (!imgalt || imgalt === " ") {
                          returnText = " ";
                        } else if (imgalt !== undefined) {
                          returnText = imgalt;
                      }
                  }
                  //Has image and text.
                  //To-do: This is a hack? Any way to do this better?
                  else if (imgArray.length && $el.textContent.trim().length) {
                    imgArray.forEach(element => {
                        element.insertAdjacentHTML("afterend", " <span class='jooa11y-clone-image-text' aria-hidden='true'>" + imgArray[0].getAttribute("alt") + "</span> ");
                    });
                    returnText = $el.textContent.trim();
                  }
                  return returnText;
              };

              //Helper: Handle ARIA labels for Link Text module.
              this.computeAriaLabel = function (el) {

                if (el.matches("[aria-label]")) {
                    return el.getAttribute("aria-label");
                }
                else if (el.matches("[aria-labelledby]")) {
                      let target = el.getAttribute("aria-labelledby").split(/\s+/);
                      if (target.length > 0) {
                          let returnText = "";
                          target.forEach((x) => {
                            if (document.querySelector("#" + x) === null) {
                                returnText += " ";
                            } else {
                                returnText += document.querySelector("#" + x).firstChild.nodeValue + " ";
                            }
                        });
                          return returnText;
                      } else {
                          return "";
                      }
                  }
                  //Children of element.
                  else if (Array.from(el.children).filter(x => x.matches("[aria-label]")).length > 0) {
                    return Array.from(el.children)[0].getAttribute("aria-label");
                  }
                  else if (Array.from(el.children).filter(x => x.matches("[title]")).length > 0) {
                    return Array.from(el.children)[0].getAttribute("title");
                  }
                  else if (Array.from(el.children).filter(x => x.matches("[aria-labelledby]")).length > 0) {
                    let target = Array.from(el.children)[0].getAttribute("aria-labelledby").split(/\s+/);
                      if (target.length > 0) {
                          let returnText = "";
                          target.forEach((x) => {
                            if (document.querySelector("#" + x) === null) {
                                returnText += " ";
                            } else {
                                returnText += document.querySelector("#" + x).firstChild.nodeValue + " ";
                            }
                        });
                          return returnText;
                      } else {
                          return "";
                      }
                  }
                  else {
                      return "noAria";
                  }
              };
          }

          //----------------------------------------------------------------------
          // Setting's panel: Additional ruleset toggles.
          //----------------------------------------------------------------------
          settingPanelToggles() {
              //Toggle: Contrast
              const $jooa11yContrastCheck = document.getElementById("jooa11y-contrast-toggle");
              $jooa11yContrastCheck.onclick = async () => {
                  if (localStorage.getItem("jooa11y-remember-contrast") === "On") {
                      localStorage.setItem("jooa11y-remember-contrast", "Off");
                      $jooa11yContrastCheck.textContent = Lang._('OFF');
                      $jooa11yContrastCheck.setAttribute("aria-pressed", "false");
                      this.resetAll(false);
                      await this.checkAll();
                  } else {
                      localStorage.setItem("jooa11y-remember-contrast", "On");
                      $jooa11yContrastCheck.textContent = Lang._('ON');
                      $jooa11yContrastCheck.setAttribute("aria-pressed", "true");
                      this.resetAll(false);
                      await this.checkAll();
                  }
              };

              //Toggle: Form labels
              const $jooa11yLabelsCheck = document.getElementById("jooa11y-labels-toggle");
              $jooa11yLabelsCheck.onclick = async () => {
                  if (localStorage.getItem("jooa11y-remember-labels") === "On") {
                      localStorage.setItem("jooa11y-remember-labels", "Off");
                      $jooa11yLabelsCheck.textContent = Lang._('OFF');
                      $jooa11yLabelsCheck.setAttribute("aria-pressed", "false");
                      this.resetAll(false);
                      await this.checkAll();
                  } else {
                      localStorage.setItem("jooa11y-remember-labels", "On");
                      $jooa11yLabelsCheck.textContent = Lang._('ON');
                      $jooa11yLabelsCheck.setAttribute("aria-pressed", "true");
                      this.resetAll(false);
                      await this.checkAll();
                  }
              };

              //Toggle: Links (Advanced)
              const $jooa11yChangeRequestCheck = document.getElementById("jooa11y-links-advanced-toggle");
              $jooa11yChangeRequestCheck.onclick = async () => {
                  if (localStorage.getItem("jooa11y-remember-links-advanced") === "On") {
                      localStorage.setItem("jooa11y-remember-links-advanced", "Off");
                      $jooa11yChangeRequestCheck.textContent = Lang._('OFF');
                      $jooa11yChangeRequestCheck.setAttribute("aria-pressed", "false");
                      this.resetAll(false);
                      await this.checkAll();
                  } else {
                      localStorage.setItem("jooa11y-remember-links-advanced", "On");
                      $jooa11yChangeRequestCheck.textContent = Lang._('ON');
                      $jooa11yChangeRequestCheck.setAttribute("aria-pressed", "true");
                      this.resetAll(false);
                      await this.checkAll();
                  }
              };

              //Toggle: Readability
              const $jooa11yReadabilityCheck = document.getElementById("jooa11y-readability-toggle");
              $jooa11yReadabilityCheck.onclick = async () => {
                  if (localStorage.getItem("jooa11y-remember-readability") === "On") {
                      localStorage.setItem("jooa11y-remember-readability", "Off");
                      $jooa11yReadabilityCheck.textContent = Lang._('OFF');
                      $jooa11yReadabilityCheck.setAttribute("aria-pressed", "false");
                      document.getElementById("jooa11y-readability-panel").classList.remove("jooa11y-active");
                      this.resetAll(false);
                      await this.checkAll();
                  } else {
                      localStorage.setItem("jooa11y-remember-readability", "On");
                      $jooa11yReadabilityCheck.textContent = Lang._('ON');
                      $jooa11yReadabilityCheck.setAttribute("aria-pressed", "true");
                      document.getElementById("jooa11y-readability-panel").classList.add("jooa11y-active");
                      this.resetAll(false);
                      await this.checkAll();
                  }
              };

              if (localStorage.getItem("jooa11y-remember-readability") === "On") {
                  document.getElementById("jooa11y-readability-panel").classList.add("jooa11y-active");
              }

              //Toggle: Dark mode. (Credits: https://derekkedziora.com/blog/dark-mode-revisited)

              let systemInitiatedDark = window.matchMedia(
                  "(prefers-color-scheme: dark)"
              );
              const $jooa11yTheme = document.getElementById("jooa11y-theme-toggle");
              const html = document.querySelector("html");
              const theme = localStorage.getItem("jooa11y-remember-theme");
              if (systemInitiatedDark.matches) {
                  $jooa11yTheme.textContent = Lang._('ON');
                  $jooa11yTheme.setAttribute("aria-pressed", "true");
              } else {
                  $jooa11yTheme.textContent = Lang._('OFF');
                  $jooa11yTheme.setAttribute("aria-pressed", "false");
              }

              function prefersColorTest(systemInitiatedDark) {
                  if (systemInitiatedDark.matches) {
                      html.setAttribute("data-jooa11y-theme", "dark");
                      $jooa11yTheme.textContent = Lang._('ON');
                      $jooa11yTheme.setAttribute("aria-pressed", "true");
                      localStorage.setItem("jooa11y-remember-theme", "");
                  } else {
                      html.setAttribute("data-jooa11y-theme", "light");
                      $jooa11yTheme.textContent = Lang._('OFF');
                      $jooa11yTheme.setAttribute("aria-pressed", "false");
                      localStorage.setItem("jooa11y-remember-theme", "");
                  }
              }

              systemInitiatedDark.addListener(prefersColorTest);
              $jooa11yTheme.onclick = async () => {
                  const theme = localStorage.getItem("jooa11y-remember-theme");
                  if (theme === "dark") {
                      html.setAttribute("data-jooa11y-theme", "light");
                      localStorage.setItem("jooa11y-remember-theme", "light");
                      $jooa11yTheme.textContent = Lang._('OFF');
                      $jooa11yTheme.setAttribute("aria-pressed", "false");
                  } else if (theme === "light") {
                      html.setAttribute("data-jooa11y-theme", "dark");
                      localStorage.setItem("jooa11y-remember-theme", "dark");
                      $jooa11yTheme.textContent = Lang._('ON');
                      $jooa11yTheme.setAttribute("aria-pressed", "true");
                  } else if (systemInitiatedDark.matches) {
                      html.setAttribute("data-jooa11y-theme", "light");
                      localStorage.setItem("jooa11y-remember-theme", "light");
                      $jooa11yTheme.textContent = Lang._('OFF');
                      $jooa11yTheme.setAttribute("aria-pressed", "false");
                  } else {
                      html.setAttribute("data-jooa11y-theme", "dark");
                      localStorage.setItem("jooa11y-remember-theme", "dark");
                      $jooa11yTheme.textContent = Lang._('OFF');
                      $jooa11yTheme.setAttribute("aria-pressed", "true");
                  }
              };
              if (theme === "dark") {
                  html.setAttribute("data-jooa11y-theme", "dark");
                  localStorage.setItem("jooa11y-remember-theme", "dark");
                  $jooa11yTheme.textContent = Lang._('ON');
                  $jooa11yTheme.setAttribute("aria-pressed", "true");
              } else if (theme === "light") {
                  html.setAttribute("data-jooa11y-theme", "light");
                  localStorage.setItem("jooa11y-remember-theme", "light");
                  $jooa11yTheme.textContent = Lang._('OFF');
                  $jooa11yTheme.setAttribute("aria-pressed", "false");
              }
          }

          //----------------------------------------------------------------------
          // Tooltip for Jump-to-Issue button.
          //----------------------------------------------------------------------
          initializeJumpToIssueTooltip() {
              tippy('#jooa11y-cycle-toggle', {
                  content: `<div style="text-align:center">${Lang._('SHORTCUT_TOOLTIP')} &raquo;<br><span class="jooa11y-shortcut-icon"></span></div>`,
                  allowHTML: true,
                  delay: [900, 0],
                  trigger: "mouseenter focusin",
                  arrow: true,
                  placement: 'top',
                  theme: "jooa11y-theme",
                  aria: {
                      content: null,
                      expanded: false,
                  },
                  appendTo: document.body,
              });
          }

          // ----------------------------------------------------------------------
          // Do Initial check
          // ----------------------------------------------------------------------
          doInitialCheck() {
              if (localStorage.getItem("jooa11y-remember-panel") === "Closed" || !localStorage.getItem("jooa11y-remember-panel")) {
                  this.panelActive = true; // Prevent panel popping up after initial check
                  this.checkAll();
               }
          }

          // ----------------------------------------------------------------------
          // Check all
          // ----------------------------------------------------------------------
          checkAll = async () => {
              this.errorCount = 0;
              this.warningCount = 0;
              this.$root = document.querySelector(this.options.checkRoot);

              this.findElements();

              //Ruleset checks
              this.checkHeaders();
              this.checkLinkText();
              this.checkAltText();

              if (localStorage.getItem("jooa11y-remember-contrast") === "On") {
                  this.checkContrast();
              }

              if (localStorage.getItem("jooa11y-remember-labels") === "On") {
                  this.checkLabels();
              }

              if (localStorage.getItem("jooa11y-remember-links-advanced") === "On") {
                  this.checkLinksAdvanced();
              }

              if (localStorage.getItem("jooa11y-remember-readability") === "On") {
                  this.checkReadability();
              }

              this.checkEmbeddedContent();
              this.checkQA();

              //Update panel
              if (this.panelActive) {
                  this.resetAll();
              } else {
                  this.updatePanel();
              }
              this.initializeTooltips();
              this.detectOverflow();

              //Don't show badge when panel is opened.
              if (!document.getElementsByClassName('jooa11y-on').length) {
                  this.updateBadge();
              }
          };

          // ============================================================
          // Reset all
          // ============================================================
          resetAll (restartPanel = true) {
              this.panelActive = false;
              this.clearEverything();

              //Remove eventListeners on the Show Outline and Show Panel toggles.
              const $outlineToggle = document.getElementById("jooa11y-outline-toggle");
              const resetOutline = $outlineToggle.cloneNode(true);
              $outlineToggle.parentNode.replaceChild(resetOutline, $outlineToggle);

              const $settingsToggle = document.getElementById("jooa11y-settings-toggle");
              const resetSettings = $settingsToggle.cloneNode(true);
              $settingsToggle.parentNode.replaceChild(resetSettings, $settingsToggle);

              //Errors
              document.querySelectorAll('.jooa11y-error-border').forEach((el) => el.classList.remove('jooa11y-error-border'));
              document.querySelectorAll('.jooa11y-error-heading').forEach((el) => el.classList.remove('jooa11y-error-heading'));
              document.querySelectorAll('.jooa11y-error-text').forEach((el) => el.classList.remove('jooa11y-error-text'));

              //Warnings
              document.querySelectorAll('.jooa11y-warning-border').forEach((el) => el.classList.remove('jooa11y-warning-border'));
              document.querySelectorAll('.jooa11y-warning-text').forEach((el) => el.classList.remove('jooa11y-warning-text'));
              document.querySelectorAll('p').forEach((el) => el.classList.remove('jooa11y-fake-list'));
              let allcaps = document.querySelectorAll('.jooa11y-warning-uppercase');
              allcaps.forEach(el => el.outerHTML = el.innerHTML);

              //Good
              document.querySelectorAll('.jooa11y-good-border').forEach((el) => el.classList.remove('jooa11y-good-border'));
              document.querySelectorAll('.jooa11y-good-text').forEach((el) => el.classList.remove('jooa11y-good-text'));

              //Remove
              document.querySelectorAll(`
                .jooa11y-instance,
                .jooa11y-instance-inline,
                .jooa11y-heading-label,
                #jooa11y-outline-list li,
                .jooa11y-readability-period,
                #jooa11y-readability-info span,
                #jooa11y-readability-details li,
                .jooa11y-clone-image-text
            `).forEach(el => el.parentNode.removeChild(el));

              //Etc
              document.querySelectorAll('.jooa11y-overflow').forEach((el) => el.classList.remove('jooa11y-overflow'));
              document.querySelectorAll('.jooa11y-fake-heading').forEach((el) => el.classList.remove('jooa11y-fake-heading'));
              document.querySelectorAll('.jooa11y-pulse-border').forEach((el) => el.classList.remove('jooa11y-pulse-border'));
              document.querySelector('#jooa11y-panel-alert').classList.remove("jooa11y-active");

              var empty = document.querySelector('#jooa11y-panel-alert-text');
              while(empty.firstChild) empty.removeChild(empty.firstChild);

              var clearStatus = document.querySelector('#jooa11y-status');
              while(clearStatus.firstChild) clearStatus.removeChild(clearStatus.firstChild);

              if (restartPanel) {
                  document.querySelector('#jooa11y-panel').classList.remove("jooa11y-active");
              }
          };
          clearEverything () {};

          // ============================================================
          // Initialize tooltips for error/warning/pass buttons: (Tippy.js)
          // Although you can also swap this with Bootstrap's tooltip library for example.
          // ============================================================
          initializeTooltips () {
              tippy(".jooa11y-btn", {
                  interactive: true,
                  trigger: "mouseenter click focusin", //Focusin trigger to ensure "Jump to issue" button displays tooltip.
                  arrow: true,
                  delay: [200, 0], //Slight delay to ensure mouse doesn't quickly trigger and hide tooltip.
                  theme: "jooa11y-theme",
                  placement: 'bottom',
                  allowHTML: true,
                  aria: {
                      content: 'describedby',
                  },
                  appendTo: document.body,
              });
          }

          // ============================================================
          // Detect parent containers that have hidden overflow.
          // ============================================================
          detectOverflow () {
              const findParentWithOverflow = ($el, property, value) => {
              while($el !== null) {
                  const style = window.getComputedStyle($el);
                  const propValue = style.getPropertyValue(property);
                          if (propValue === value) {
                              return $el;
                          }
                      $el = $el.parentElement;
                  }
                  return null;
              };
              const $findButtons = document.querySelectorAll('.jooa11y-btn');
              $findButtons.forEach(function ($el) {
                  const overflowing = findParentWithOverflow($el, 'overflow', 'hidden');
                  if (overflowing !== null) {
                      overflowing.classList.add('jooa11y-overflow');
                  }
              });
          }

          // ============================================================
          // Update iOS style notification badge on icon.
          // ============================================================
          updateBadge () {
              let totalCount = this.errorCount + this.warningCount;
              let warningCount = this.warningCount;
              const notifBadge = document.getElementById("jooa11y-notification-badge");
              if (totalCount === 0) {
                  notifBadge.style.display = "none";
              } else if (this.warningCount > 0 && this.errorCount === 0) {
                  notifBadge.style.display = "flex";
                  notifBadge.classList.add("jooa11y-notification-badge-warning");
                  document.getElementById('jooa11y-notification-count').innerHTML = Lang.sprintf('PANEL_STATUS_10', warningCount);
              } else {
                  notifBadge.style.display = "flex";
                  notifBadge.classList.remove("jooa11y-notification-badge-warning");
                  document.getElementById('jooa11y-notification-count').innerHTML = Lang.sprintf('PANEL_STATUS_10', totalCount);
              }
          }

          // ----------------------------------------------------------------------
          // Main panel: Display and update panel.
          // ----------------------------------------------------------------------
          updatePanel () {
              this.panelActive = true;
              let totalCount = this.errorCount + this.warningCount;

              this.buildPanel();
              this.skipToIssue();

              const $jooa11ySkipBtn = document.getElementById("jooa11y-cycle-toggle");

              $jooa11ySkipBtn.disabled = false;
              $jooa11ySkipBtn.setAttribute("style", "cursor: pointer !important;");

              const $jooa11yPanel = document.getElementById("jooa11y-panel");
              $jooa11yPanel.classList.add("jooa11y-active");

              const $panelContent = document.getElementById("jooa11y-panel-content");
              const $jooa11yStatus = document.getElementById("jooa11y-status");
              const $findButtons = document.querySelectorAll('.jooa11y-btn');

              if (this.errorCount === 1 && this.warningCount === 1) {
                  $panelContent.setAttribute("class", "jooa11y-errors");
                  $jooa11yStatus.textContent = Lang._('PANEL_STATUS_1');
              }
              else if (this.errorCount === 1 && this.warningCount > 0) {
                  $panelContent.setAttribute("class", "jooa11y-errors");
                  $jooa11yStatus.textContent = Lang.sprintf('PANEL_STATUS_2', this.warningCount);
              }
              else if (this.errorCount > 0 && this.warningCount === 1) {
                  $panelContent.setAttribute("class", "jooa11y-errors");
                  $jooa11yStatus.textContent = Lang.sprintf('PANEL_STATUS_3', this.errorCount);
              }
              else if (this.errorCount > 0 && this.warningCount > 0) {
                  $panelContent.setAttribute("class", "jooa11y-errors");
                  $jooa11yStatus.textContent = Lang.sprintf('PANEL_STATUS_4', this.errorCount, this.warningCount);
              }
              else if (this.errorCount > 0) {
                  $panelContent.setAttribute("class", "jooa11y-errors");
                  $jooa11yStatus.textContent = this.errorCount === 1 ?
                    Lang._('PANEL_STATUS_5') :
                    Lang.sprintf('PANEL_STATUS_6', this.errorCount)
                  ;
              }
              else if (this.warningCount > 0) {
                  $panelContent.setAttribute("class", "jooa11y-warnings");
                  $jooa11yStatus.textContent = totalCount === 1 ?
                      Lang._('PANEL_STATUS_7'):
                      Lang.sprintf('PANEL_STATUS_8', this.warningCount)
                  ;
              }
              else {
                  $panelContent.setAttribute("class", "jooa11y-good");
                  $jooa11yStatus.textContent = Lang._('PANEL_STATUS_9');

                  if ($findButtons.length === 0) {
                      $jooa11ySkipBtn.disabled = true;
                      $jooa11ySkipBtn.setAttribute("style", "cursor: default !important;");
                  }
              }
          };

          // ----------------------------------------------------------------------
          // Main panel: Build Show Outline and Settings tabs.
          // ----------------------------------------------------------------------
          buildPanel = () => {

              const $outlineToggle = document.getElementById("jooa11y-outline-toggle");
              const $outlinePanel = document.getElementById("jooa11y-outline-panel");
              const $outlineList = document.getElementById("jooa11y-outline-list");

              const $settingsToggle = document.getElementById("jooa11y-settings-toggle");
              const $settingsPanel = document.getElementById("jooa11y-settings-panel");
              const $settingsContent = document.getElementById("jooa11y-settings-content");

              const $headingAnnotations = document.querySelectorAll(".jooa11y-heading-label");

              //Show outline panel
              $outlineToggle.addEventListener('click', () => {
                  if ($outlineToggle.getAttribute("aria-expanded") === "true") {
                      $outlineToggle.classList.remove("jooa11y-outline-active");
                      $outlinePanel.classList.remove("jooa11y-active");
                      $outlineToggle.textContent = Lang._('SHOW_OUTLINE');
                      $outlineToggle.setAttribute("aria-expanded", "false");
                      localStorage.setItem("jooa11y-remember-outline", "Closed");
                  } else {
                      $outlineToggle.classList.add("jooa11y-outline-active");
                      $outlinePanel.classList.add("jooa11y-active");
                      $outlineToggle.textContent = Lang._('HIDE_OUTLINE');
                      $outlineToggle.setAttribute("aria-expanded", "true");
                      localStorage.setItem("jooa11y-remember-outline", "Opened");
                  }

                  //Set focus on Page Outline heading for accessibility.
                  document.querySelector("#jooa11y-outline-header > h2").focus();

                  //Show heading level annotations.
                  $headingAnnotations.forEach(($el) => $el.classList.toggle("jooa11y-label-visible"));

                  //Close Settings panel when Show Outline is active.
                  $settingsPanel.classList.remove("jooa11y-active");
                  $settingsToggle.classList.remove("jooa11y-settings-active");
                  $settingsToggle.setAttribute("aria-expanded", "false");
                  $settingsToggle.textContent = Lang._('SHOW_SETTINGS');

                  //Keyboard accessibility fix for scrollable panel content.
                  if ($outlineList.clientHeight > 250) {
                      $outlineList.setAttribute("tabindex", "0");
                  }
              });

              //Remember to leave outline open
              if (localStorage.getItem("jooa11y-remember-outline") === "Opened") {
                  $outlineToggle.classList.add("jooa11y-outline-active");
                  $outlinePanel.classList.add("jooa11y-active");
                  $outlineToggle.textContent = Lang._('HIDE_OUTLINE');
                  $outlineToggle.setAttribute("aria-expanded", "true");
                  $headingAnnotations.forEach(($el) => $el.classList.toggle("jooa11y-label-visible"));
                  //Keyboard accessibility fix for scrollable panel content.
                  if ($outlineList.clientHeight > 250) {
                      $outlineList.setAttribute("tabindex", "0");
                  }
              }

              //Show settings panel
              $settingsToggle.addEventListener('click', () => {
                  if ($settingsToggle.getAttribute("aria-expanded") === "true") {
                      $settingsToggle.classList.remove("jooa11y-settings-active");
                      $settingsPanel.classList.remove("jooa11y-active");
                      $settingsToggle.textContent = Lang._('SHOW_SETTINGS');
                      $settingsToggle.setAttribute("aria-expanded", "false");
                  } else {
                      $settingsToggle.classList.add("jooa11y-settings-active");
                      $settingsPanel.classList.add("jooa11y-active");
                      $settingsToggle.textContent = Lang._('HIDE_SETTINGS');
                      $settingsToggle.setAttribute("aria-expanded", "true");
                  }

                  //Set focus on Settings heading for accessibility.
                  document.querySelector("#jooa11y-settings-header > h2").focus();

                  //Close Show Outline panel when Settings is active.
                  $outlinePanel.classList.remove("jooa11y-active");
                  $outlineToggle.classList.remove("jooa11y-outline-active");
                  $outlineToggle.setAttribute("aria-expanded", "false");
                  $outlineToggle.textContent = Lang._('SHOW_OUTLINE');
                  $headingAnnotations.forEach(($el) => $el.classList.remove("jooa11y-label-visible"));
                  localStorage.setItem("jooa11y-remember-outline", "Closed");

                  //Keyboard accessibility fix for scrollable panel content.
                  if ($settingsContent.clientHeight > 350) {
                      $settingsContent.setAttribute("tabindex", "0");
                  }
              });

              //Enhanced keyboard accessibility for panel.
              document.getElementById('jooa11y-panel-controls').addEventListener('keydown', function(e) {
              const $tab = document.querySelectorAll('#jooa11y-outline-toggle[role=tab], #jooa11y-settings-toggle[role=tab]');
                  if (e.key === 'ArrowRight') {
                      for (let i = 0; i < $tab.length; i++) {
                          if ($tab[i].getAttribute('aria-expanded') === "true" || $tab[i].getAttribute('aria-expanded') === "false") {
                              $tab[i+1].focus();
                              e.preventDefault();
                              break;
                          }
                      }
                  }
                  if (e.key === 'ArrowDown') {
                      for (let i = 0; i < $tab.length; i++) {
                          if ($tab[i].getAttribute('aria-expanded') === "true" || $tab[i].getAttribute('aria-expanded') === "false") {
                              $tab[i+1].focus();
                              e.preventDefault();
                              break;
                          }
                      }
                  }
                  if (e.key === 'ArrowLeft') {
                      for (let i = $tab.length-1; i > 0; i--) {
                          if ($tab[i].getAttribute('aria-expanded') === "true" || $tab[i].getAttribute('aria-expanded') === "false") {
                              $tab[i-1].focus();
                              e.preventDefault();
                              break;
                          }
                      }
                  }
                  if (e.key === 'ArrowUp') {
                      for (let i = $tab.length-1; i > 0; i--) {
                          if ($tab[i].getAttribute('aria-expanded') === "true" || $tab[i].getAttribute('aria-expanded') === "false") {
                              $tab[i-1].focus();
                              e.preventDefault();
                              break;
                          }
                      }
                  }
              });

              const $closeAlertToggle = document.getElementById("jooa11y-close-alert");
              const $alertPanel = document.getElementById("jooa11y-panel-alert");
              const $alertText = document.getElementById("jooa11y-panel-alert-text");
              const $jooa11ySkipBtn = document.getElementById("jooa11y-cycle-toggle");

              $closeAlertToggle.addEventListener('click', () => {
                  $alertPanel.classList.remove("jooa11y-active");
                  while($alertText.firstChild) $alertText.removeChild($alertText.firstChild);
                  document.querySelectorAll('.jooa11y-pulse-border').forEach((el) => el.classList.remove('jooa11y-pulse-border'));
                  $jooa11ySkipBtn.focus();
              });
          }

          // ============================================================
          // Main panel: Skip to issue button.
          // ============================================================

          skipToIssue = () => {
              /* Polyfill for scrollTo. scrollTo instead of .animate(), so Jooa11y could use jQuery slim build. Credit: https://stackoverflow.com/a/67108752 & https://github.com/iamdustan/smoothscroll */
              //let reducedMotionQuery = false;
              //let scrollBehavior = 'smooth';
              /*
              if (!('scrollBehavior' in document.documentElement.style)) {
                  var js = document.createElement('script');
                  js.src = "https://cdn.jsdelivr.net/npm/smoothscroll-polyfill@0.4.4/dist/smoothscroll.min.js";
                  document.head.appendChild(js);
              }
              if (!(document.documentMode)) {
                  if (typeof window.matchMedia === "function") {
                      reducedMotionQuery = window.matchMedia("(prefers-reduced-motion: reduce)");
                  }
                  if (!reducedMotionQuery || reducedMotionQuery.matches) {
                      scrollBehavior = "auto";
                  }
              }
              */

              let jooa11yBtnLocation = 0;
              const findJooa11yBtn = document.querySelectorAll('.jooa11y-btn').length;

              //Jump to issue using keyboard shortcut.
              document.addEventListener('keyup', (e) => {
                  if (e.altKey && e.code === "Period") {
                      skipToIssueToggle();
                      e.preventDefault();
                  }
              });

              //Jump to issue using click.
              const $skipToggle = document.getElementById("jooa11y-cycle-toggle");
              $skipToggle.addEventListener('click', (e) => {
                  skipToIssueToggle();
                  e.preventDefault();
              });

              const skipToIssueToggle = function () {

                  //Calculate location of both visible and hidden buttons.
                  const $findButtons = document.querySelectorAll('.jooa11y-btn');
                  const $alertPanel = document.getElementById("jooa11y-panel-alert");
                  const $alertText = document.getElementById("jooa11y-panel-alert-text");
                  const $alertPanelPreview = document.getElementById("jooa11y-panel-alert-preview");
                  //const $closeAlertToggle = document.getElementById("jooa11y-close-alert");

                   //Mini function: Find visibible parent of hidden element.
                  const findVisibleParent = ($el, property, value) => {
                      while($el !== null) {
                          const style = window.getComputedStyle($el);
                          const propValue = style.getPropertyValue(property);
                                  if (propValue === value) {
                                      return $el;
                                  }
                              $el = $el.parentElement;
                          }
                          return null;
                      };

                  //Mini function: Calculate top of element.
                  const offset = ($el) => {
                    let rect = $el.getBoundingClientRect(),
                    scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                    return { top: rect.top + scrollTop}
                };

                  //'offsetTop' will always return 0 if element is hidden. We rely on offsetTop to determine if element is hidden, although we use 'getBoundingClientRect' to set the scroll position.
                  let scrollPosition;
                  let offsetTopPosition = $findButtons[jooa11yBtnLocation].offsetTop;
                  if (offsetTopPosition === 0) {
                      let visiblePosition = findVisibleParent($findButtons[jooa11yBtnLocation], 'display', 'none');
                      scrollPosition = offset(visiblePosition.previousElementSibling).top - 50;
                  } else {
                      scrollPosition = offset($findButtons[jooa11yBtnLocation]).top - 50;
                  }

                  //Scroll to element if offsetTop is less than or equal to 0.
                  if (offsetTopPosition >= 0) {
                      setTimeout(function() {
                          window.scrollTo({
                              top: scrollPosition,
                              behavior: 'smooth'
                          });
                      }, 1);

                      //Add pulsing border to visible parent of hidden element.
                      $findButtons.forEach(function ($el) {
                        const overflowing = findVisibleParent($el, 'display', 'none');
                        if (overflowing !== null) {
                            let hiddenparent = overflowing.previousElementSibling;
                            hiddenparent.classList.add("jooa11y-pulse-border");
                        }
                      });

                      $findButtons[jooa11yBtnLocation].focus();
                  }
                  else {
                      $findButtons[jooa11yBtnLocation].focus();
                  }

                  //Alert if element is hidden.
                  if (offsetTopPosition === 0) {
                      $alertPanel.classList.add("jooa11y-active");
                      $alertText.textContent = `${Lang._('PANEL_STATUS_12')}`;
                      $alertPanelPreview.innerHTML = $findButtons[jooa11yBtnLocation].getAttribute('data-tippy-content');
                  } else if (offsetTopPosition < 1) {
                      $alertPanel.classList.remove("jooa11y-active");
                      document.querySelectorAll('.jooa11y-pulse-border').forEach(($el) => $el.classList.remove('jooa11y-pulse-border'));
                  }

                  //Reset index so it scrolls back to top of page.
                  jooa11yBtnLocation += 1;
                  if (jooa11yBtnLocation >= findJooa11yBtn) {
                      jooa11yBtnLocation = 0;
                  }
              };
          }

          // ============================================================
          // Finds all elements and caches them
          // ============================================================
          findElements () {
            const allHeadings = Array.from(this.$root.querySelectorAll("h1, h2, h3, h4, h5, h6, [role='heading'][aria-level]"));
            const allPs = Array.from(this.$root.querySelectorAll("p"));

            this.$containerExclusions = Array.from(document.querySelectorAll(this.containerIgnore));

            this.$h = allHeadings.filter(heading => !this.$containerExclusions.includes(heading));
            this.$p = allPs.filter(p => !this.$containerExclusions.includes(p));
          };

          // ============================================================
          // Rulesets: Check Headings
          // ============================================================
          checkHeaders () {
              let prevLevel;

              this.$h.forEach((el, i) => {
                let text = this.computeTextNodeWithImage(el);
                  let htext = escapeHTML(text);
                  let level;

                  if (el.getAttribute("aria-level")) {
                    level = +el.getAttribute("aria-level");
                  } else {
                    level = +el.tagName.slice(1);
                  }

                  let headingLength = el.textContent.trim().length;
                  let error = null;
                  let warning = null;

                  if (level - prevLevel > 1 && i !== 0) {
                      error = Lang.sprintf('HEADING_NON_CONSECUTIVE_LEVEL', prevLevel, level);
                    } else if (el.textContent.trim().length === 0) {
                      if (el.querySelectorAll("img").length) {
                          const imgalt = el.querySelector("img").getAttribute("alt");
                          if (imgalt === undefined || imgalt === " " || imgalt === "") {
                              error = Lang.sprintf('HEADING_EMPTY_WITH_IMAGE', level);
                              el.classList.add("jooa11y-error-text");
                          }
                      } else {
                          error = Lang.sprintf('HEADING_EMPTY', level);
                          el.classList.add("jooa11y-error-text");
                        }
                  } else if (i === 0 && level !== 1 && level !== 2) {
                      error = Lang._('HEADING_FIRST');
                    } else if (el.textContent.trim().length > 170) {
                      warning = `${Lang._('HEADING_LONG')} . ${Lang.sprintf('HEADING_LONG_INFO', headingLength)}`;
                  }

                  prevLevel = level;

                  let li =
                  `<li class='jooa11y-outline-${level}'>
                <span class='jooa11y-badge'>${level}</span>
                <span class='jooa11y-outline-list-item'>${htext}</span>
            </li>`;

                  let liError =
                      `<li class='jooa11y-outline-${level}'>
                <span class='jooa11y-badge jooa11y-error-badge'>
                <span aria-hidden='true'>&#10007;</span>
                <span class='jooa11y-visually-hidden'>${Lang._('ERROR')}</span> ${level}</span>
                <span class='jooa11y-outline-list-item jooa11y-red-text jooa11y-bold'>${htext}</span>
            </li>`;

                  let liWarning =
                      `<li class='jooa11y-outline-${level}'>
                <span class='jooa11y-badge jooa11y-warning-badge'>
                <span aria-hidden='true'>&#x3f;</span>
                <span class='jooa11y-visually-hidden'>${Lang._('WARNING')}</span> ${level}</span>
                <span class='jooa11y-outline-list-item jooa11y-yellow-text jooa11y-bold'>${htext}</span>
            </li>`;

              let ignoreArray = [];
              if (this.options.outlineIgnore) {
                  ignoreArray = Array.from(document.querySelectorAll(this.options.outlineIgnore));
              }

              if (!ignoreArray.includes(el)) {
                      //Append heading labels.
                      el.insertAdjacentHTML("beforeend", `<span class='jooa11y-heading-label'>H${level}</span>`);

                      //Heading errors
                      if (error != null && el.closest("a")) {
                          this.errorCount++;
                          el.classList.add("jooa11y-error-heading");
                          el.closest("a").insertAdjacentHTML("afterend", this.annotate(Lang._('ERROR'), error, true));
                          document.querySelector("#jooa11y-outline-list").insertAdjacentHTML("beforeend", liError);
                      } else if (error != null) {
                          this.errorCount++;
                          el.classList.add("jooa11y-error-heading");
                          el.insertAdjacentHTML("beforebegin", this.annotate(Lang._('ERROR'), error));
                          document.querySelector("#jooa11y-outline-list").insertAdjacentHTML("beforeend", liError);
                      }

                      //Heading warnings
                      else if (warning != null && el.closest("a")) {
                          this.warningCount++;
                          el.closest("a").insertAdjacentHTML("afterend", this.annotate(Lang._('WARNING'), warning));
                          document.querySelector("#jooa11y-outline-list").insertAdjacentHTML("beforeend", liWarning);
                      } else if (warning != null) {
                          el.insertAdjacentHTML("beforebegin", this.annotate(Lang._('WARNING'), warning));
                          document.querySelector("#jooa11y-outline-list").insertAdjacentHTML("beforeend", liWarning);
                      }

                      //Not an error or warning
                      else if (error == null || warning == null) {
                          document.querySelector("#jooa11y-outline-list").insertAdjacentHTML("beforeend", li);
                      }
                  }
              });

              //Check to see there is at least one H1 on the page.
              const $h1 = Array.from(this.$root.querySelectorAll('h1, [role="heading"][aria-level="1"]'))
                .filter($h => !this.$containerExclusions.includes($h));

              if ($h1.length === 0) {
                  this.errorCount++;

                  document.querySelector('#jooa11y-outline-header').insertAdjacentHTML(
                    'afterend',
                    `<div class='jooa11y-instance jooa11y-missing-h1'>
                    <span class='jooa11y-badge jooa11y-error-badge'><span aria-hidden='true'>&#10007;</span><span class='jooa11y-visually-hidden'>${Lang._('ERROR')}</span></span>
                    <span class='jooa11y-red-text jooa11y-bold'>${Lang._('PANEL_HEADING_MISSING_ONE')}</span>
                </div>`
                  );

                  document.querySelector("#jooa11y-container").insertAdjacentHTML(
                    'afterend',
                    this.annotateBanner(Lang._('ERROR'), Lang._('HEADING_MISSING_ONE'))
                  );
              }
          };

          // ============================================================
          // Rulesets: Link text
          // ============================================================
          checkLinkText () {
              const containsLinkTextStopWords = (textContent) => {
                  let urlText = [
                      "http",
                      ".asp",
                      ".htm",
                      ".php",
                      ".edu/",
                      ".com/",
                      ".net/",
                      ".org/",
                      ".us/",
                      ".ca/",
                      ".de/",
                      ".icu/",
                      ".uk/",
                      ".ru/",
                      ".info/",
                      ".top/",
                      ".xyz/",
                      ".tk/",
                      ".cn/",
                      ".ga/",
                      ".cf/",
                      ".nl/",
                      ".io/"
                  ];

                  let hit = [null, null, null];

                  // Flag partial stop words.
                  this.options.partialAltStopWords.forEach((word) => {
                      if (
                          textContent.length === word.length &&
                          textContent.toLowerCase().indexOf(word) >= 0
                      ) {
                          hit[0] = word;
                          return false;
                      }
                  });

                  // Other warnings we want to add.
                  this.options.warningAltWords.forEach((word) => {
                      if (textContent.toLowerCase().indexOf(word) >= 0) {
                          hit[1] = word;
                          return false;
                      }
                  });

                  // Flag link text containing URLs.
                  urlText.forEach((word) => {
                      if (textContent.toLowerCase().indexOf(word) >= 0) {
                          hit[2] = word;
                          return false;
                      }
                  });
                  return hit;
              };

              /* Mini function if you need to exclude any text contained with a span. We created this function to ignore automatically appended sr-only text for external links and document filetypes.

              $.fn.ignore = function(sel){
                  return this.clone().find(sel||">*").remove().end();
              };

              $el.ignore("span.sr-only").text().trim();

              Example: <a href="#">learn more <span class="sr-only">(external)</span></a>

              This function will ignore the text "(external)", and correctly flag this link as an error for non descript link text. */
              const fnIgnore = (element, selector) => {
                const $clone = element.cloneNode(true);
                const $excluded = Array.from(selector ? $clone.querySelectorAll(selector) : $clone.children);
                $excluded.forEach(($c) => {
                  $c.parentElement.removeChild($c);
                });
                return $clone;
              };

              const $linkIgnore = Array.from(this.$root.querySelectorAll(this.linkIgnore));
              const $links = Array.from(this.$root.querySelectorAll('a[href]'))
                .filter($a => !$linkIgnore.includes($a));

              $links.forEach((el) => {
                  let linkText = this.computeAriaLabel(el);
                  let hasAriaLabelledBy = el.getAttribute('aria-labelledby');
                  let hasAriaLabel = el.getAttribute('aria-label');
                  let hasTitle = el.getAttribute('title');
                  let childAriaLabelledBy = null;
                  let childAriaLabel = null;
                  let childTitle = null;

                  if (el.children.length) {
                    let $firstChild = el.children[0];
                    childAriaLabelledBy = $firstChild.getAttribute('aria-labelledby');
                    childAriaLabel = $firstChild.getAttribute('aria-label');
                    childTitle = $firstChild.getAttribute('title');
                  }

                  let error = containsLinkTextStopWords(fnIgnore(el, this.options.linkIgnoreSpan).textContent.trim());

                  if (linkText === 'noAria') {
                    linkText = el.textContent;
                  }

                  //Flag empty hyperlinks
                  if ( el.getAttribute('href') && !el.textContent.trim() ) {
                      if (el.querySelectorAll('img').length) ; else if (hasAriaLabelledBy || hasAriaLabel) {
                          el.classList.add("jooa11y-good-border");
                          el.insertAdjacentHTML(
                            'beforebegin',
                            this.annotate(Lang._('GOOD'), Lang.sprintf('LINK_LABEL', linkText), true)
                          );
                      } else if (hasTitle) {
                          let linkText = hasTitle;
                          el.classList.add("jooa11y-good-border");
                          el.insertAdjacentHTML(
                            'beforebegin',
                            this.annotate(Lang._('GOOD'), Lang.sprintf('LINK_LABEL', linkText), true)
                          );
                      } else if (el.children.length) {
                          if (childAriaLabelledBy || childAriaLabel || childTitle) {
                            el.classList.add("jooa11y-good-border");
                            el.insertAdjacentHTML(
                              'beforebegin',
                              this.annotate(Lang._('GOOD'), Lang.sprintf('LINK_LABEL', linkText), true)
                            );
                          } else {
                            this.errorCount++;
                            el.classList.add("jooa11y-error-border");
                            el.insertAdjacentHTML(
                              'afterend',
                              this.annotate(Lang._('ERROR'), Lang.sprintf('LINK_EMPTY_LINK_NO_LABEL'), true)
                            );
                          }
                      } else {
                        this.errorCount++;
                        el.classList.add("jooa11y-error-border");
                        el.insertAdjacentHTML(
                          'afterend',
                          this.annotate(Lang._('ERROR'), Lang._('LINK_EMPTY'), true)
                        );
                      }
                  } else if (error[0] !== null) {
                      if (hasAriaLabelledBy) {
                        el.insertAdjacentHTML(
                          'beforebegin',
                          this.annotate(Lang._('GOOD'), Lang.sprintf('LINK_LABEL', linkText), true)
                        );
                      } else if (hasAriaLabel) {
                        el.insertAdjacentHTML(
                          'beforebegin',
                          this.annotate(Lang._('GOOD'), Lang.sprintf('LINK_LABEL', hasAriaLabel), true)
                        );
                      } else if (el.getAttribute('aria-hidden') === 'true' && el.getAttribute('tabindex') === '-1') ; else {
                        this.errorCount++;
                        el.classList.add("jooa11y-error-text");
                        el.insertAdjacentHTML(
                          'afterend',
                          this.annotate(
                            Lang._('ERROR'),
                            `${Lang.sprintf('LINK_STOPWORD', error[0])} <hr aria-hidden="true"> ${Lang._('LINK_STOPWORD_TIP')}`,
                            true
                          )
                        );
                      }
                  } else if (error[1] !== null) {
                    this.warningCount++;
                    el.classList.add("jooa11y-warning-text");
                    el.insertAdjacentHTML(
                      'afterend',
                      this.annotate(
                        Lang._('WARNING'),
                        `${Lang.sprintf('LINK_BEST_PRACTICES', error[1])} <hr aria-hidden="true"> ${Lang._('LINK_BEST_PRACTICES_DETAILS')}`,
                        true
                      )
                    );
                  } else if (error[2] != null) {
                      if (linkText.length > 40) {
                        this.warningCount++;
                        el.classList.add("jooa11y-warning-text");
                        el.insertAdjacentHTML(
                          'afterend',
                          this.annotate(
                            Lang._('WARNING'),
                            `${Lang._('LINK_URL')} <hr aria-hidden="true"> ${Lang._('LINK_URL_TIP')}`,
                            true
                          )
                        );
                      }
                  }
              });
          };

          // ============================================================
          // Rulesets: Links (Advanced)
          // ============================================================
          checkLinksAdvanced () {
              const $linkIgnore = Array.from(this.$root.querySelectorAll(this.linkIgnore + ', #jooa11y-container a, .jooa11y-exclude'));
              const $linksTargetBlank = Array.from(this.$root.querySelectorAll('a[href]'))
                .filter($a => !$linkIgnore.includes($a));

              let seen = {};
              $linksTargetBlank.forEach((el) => {
                  let linkText = this.computeAriaLabel(el);

                  if (linkText === 'noAria') {
                      linkText = el.textContent;
                  }

                  const fileTypeMatch = el.matches(`
                    a[href$='.pdf'],
                    a[href$='.doc'],
                    a[href$='.zip'],
                    a[href$='.mp3'],
                    a[href$='.txt'],
                    a[href$='.exe'],
                    a[href$='.dmg'],
                    a[href$='.rtf'],
                    a[href$='.pptx'],
                    a[href$='.ppt'],
                    a[href$='.xls'],
                    a[href$='.xlsx'],
                    a[href$='.csv'],
                    a[href$='.mp4'],
                    a[href$='.mov'],
                    a[href$='.avi']
                `);

                  //Links with identical accessible names have equivalent purpose.

                  //If link has an image, process alt attribute,
                  //To-do: Kinda hacky. Doesn't return accessible name of link in correct order.
                  const $img = el.querySelector('img');
                  let alt = $img ? ($img.getAttribute('alt') || '') : '';

                  //Return link text and image's alt text.
                  let linkTextTrimmed = linkText.trim().toLowerCase() + " " + alt;
                  let href = el.getAttribute("href");

                  if (seen[linkTextTrimmed] && linkTextTrimmed.length !== 0) {
                      if (seen[href]) ; else {
                        this.warningCount++;
                        el.classList.add("jooa11y-warning-text");
                        el.insertAdjacentHTML(
                          'afterend',
                          this.annotate(
                            Lang._('WARNING'),
                            `${Lang._('LINK_IDENTICAL_NAME')} <hr aria-hidden="true"> ${Lang.sprintf('LINK_IDENTICAL_NAME_TIP', linkText)}`,
                            true
                          )
                        );
                      }
                  } else {
                      seen[linkTextTrimmed] = true;
                      seen[href] = true;
                  }

                  //New tab or new window.
                  const containsNewWindowPhrases = this.options.newWindowPhrases.some(function (pass) {
                      return linkText.toLowerCase().indexOf(pass) >= 0;
                  });

                  //Link that points to a file type indicates that it does.
                  const containsFileTypePhrases = this.options.fileTypePhrases.some(function (pass) {
                      return linkText.toLowerCase().indexOf(pass) >= 0;
                  });

                  if (el.getAttribute("target") === "_blank" && !fileTypeMatch && !containsNewWindowPhrases) {
                    this.warningCount++;
                    el.classList.add("jooa11y-warning-text");
                    el.insertAdjacentHTML(
                      'afterend',
                      this.annotate(
                        Lang._('WARNING'),
                        `${Lang._('NEW_TAB_WARNING')} <hr aria-hidden="true"> ${Lang._('NEW_TAB_WARNING_TIP')}`,
                        true
                      )
                    );
                  }

                  if (fileTypeMatch && !containsFileTypePhrases) {
                    this.warningCount++;
                    el.classList.add("jooa11y-warning-text");
                    el.insertAdjacentHTML(
                      'afterend',
                      this.annotate(
                        Lang._('WARNING'),
                        `${Lang._('FILE_TYPE_WARNING')} <hr aria-hidden="true"> ${Lang._('FILE_TYPE_WARNING_TIP')}`,
                        true
                      )
                    );
                  }
              });
          }

          // ============================================================
          // Ruleset: Alternative text
          // ============================================================
          checkAltText () {
              const containsAltTextStopWords = (alt) => {
                  const altUrl = [
                      ".png",
                      ".jpg",
                      ".jpeg",
                      ".gif",
                      ".tiff",
                      ".svg"
                  ];

                  let hit = [null, null, null];
                  altUrl.forEach((word) => {
                      if (alt.toLowerCase().indexOf(word) >= 0) {
                          hit[0] = word;
                      }
                  });
                  this.options.suspiciousAltWords.forEach((word) => {
                      if (alt.toLowerCase().indexOf(word) >= 0) {
                          hit[1] = word;
                      }
                  });
                  this.options.placeholderAltStopWords.forEach((word) => {
                      if (alt.length === word.length && alt.toLowerCase().indexOf(word) >= 0) {
                          hit[2] = word;
                      }
                  });
                  return hit;
              };

              // Stores the corresponding issue text to alternative text
              const images = Array.from(this.$root.querySelectorAll("img"));
              const excludeimages = Array.from(this.$root.querySelectorAll(this.imageIgnore));
              const $img = images.filter($el => !excludeimages.includes($el));

              $img.forEach(($el) => {
                  let alt = $el.getAttribute("alt");
                  if ( alt === null ) {
                      if ($el.closest('a[href]')) {
                          if ($el.closest('a[href]').textContent.trim().length > 1) {
                              $el.classList.add("jooa11y-error-border");
                              $el.closest('a[href]').insertAdjacentHTML('beforebegin', this.annotate(Lang._('ERROR'), Lang._('MISSING_ALT_LINK_BUT_HAS_TEXT_MESSAGE'), false, true));
                          }
                          else if ($el.closest('a[href]').textContent.trim().length === 0) {
                              $el.classList.add("jooa11y-error-border");
                              $el.closest('a[href]').insertAdjacentHTML('beforebegin', this.annotate(Lang._('ERROR'), Lang._('MISSING_ALT_LINK_MESSAGE'), false, true));
                          }
                      }
                      // General failure message if image is missing alt.
                      else {
                          $el.classList.add("jooa11y-error-border");
                          $el.insertAdjacentHTML('beforebegin', this.annotate(Lang._('ERROR'), Lang._('MISSING_ALT_MESSAGE'), false, true));
                      }
                  }
                  // If alt attribute is present, further tests are done.
                  else {
                      let altText = escapeHTML(alt); //Prevent tooltip from breaking.
                      let error = containsAltTextStopWords(altText);
                      let altLength = alt.length;

                      // Image fails if a stop word was found.
                      if (error[0] != null && $el.closest("a[href]")) {
                          this.errorCount++;
                          $el.classList.add("jooa11y-error-border");
                          $el.closest("a[href]").insertAdjacentHTML(
                            'beforebegin',
                            this.annotate(
                              Lang._('ERROR'),
                              `${Lang.sprintf('LINK_IMAGE_BAD_ALT_MESSAGE', altText, error[0])} <hr aria-hidden="true"> ${Lang._('LINK_IMAGE_BAD_ALT_MESSAGE_INFO')}`,
                              false
                            )
                          );
                      }
                      else if (error[2] != null && $el.closest("a[href]")) {
                          this.errorCount++;
                          $el.classList.add("jooa11y-error-border");
                          $el.closest("a[href]").insertAdjacentHTML('beforebegin', this.annotate(Lang._('ERROR'), Lang.sprintf('LINK_IMAGE_PLACEHOLDER_ALT_MESSAGE', altText), false, true));
                      }
                      else if (error[1] != null && $el.closest("a[href]")) {
                          this.warningCount++;
                          $el.classList.add("jooa11y-warning-border");
                          $el.closest("a[href]").insertAdjacentHTML(
                            'beforebegin',
                            this.annotate(
                              Lang._('WARNING'),
                              `${Lang.sprintf('LINK_IMAGE_SUS_ALT_MESSAGE', altText, error[1])} <hr aria-hidden="true"> ${Lang.sprintf('LINK_IMAGE_SUS_ALT_MESSAGE_INFO', altText)}`,
                              false
                            )
                          );
                      }
                      else if (error[0] != null) {
                          this.errorCount++;
                          $el.classList.add("jooa11y-error-border");
                          $el.insertAdjacentHTML(
                            'beforebegin',
                            this.annotate(
                              Lang._('ERROR'),
                              `${Lang._('LINK_ALT_HAS_BAD_WORD_MESSAGE')} <hr aria-hidden="true"> ${Lang.sprintf('LINK_ALT_HAS_BAD_WORD_MESSAGE_INFO', error[0], altText)}`,
                              false
                            )
                          );
                      }
                      else if (error[2] != null) {
                          this.errorCount++;
                          $el.classList.add("jooa11y-error-border");
                          $el.insertAdjacentHTML('beforebegin', this.annotate(Lang._('ERROR'), Lang.sprintf('LINK_ALT_PLACEHOLDER_MESSAGE', altText), false));
                      }
                      else if (error[1] != null) {
                          this.warningCount++;
                          $el.classList.add("jooa11y-warning-border");
                          $el.insertAdjacentHTML(
                            'beforebegin',
                            this.annotate(
                              Lang._('WARNING'),
                              `${Lang.sprintf('LINK_ALT_HAS_SUS_WORD_MESSAGE', altText, error[1])} <hr aria-hidden="true"> ${Lang.sprintf('LINK_ALT_HAS_SUS_WORD_MESSAGE_INFO', altText)}`,
                              false
                            )
                          );
                      }
                      else if ((alt === "" || alt === " ") && $el.closest("a[href]")) {
                          if ($el.closest("a[href]").getAttribute("tabindex") === "-1" && $el.closest("a[href]").getAttribute("aria-hidden") === "true") ;
                          else if ($el.closest("a[href]").getAttribute("aria-hidden") === "true") {
                              this.errorCount++;
                              $el.classList.add("jooa11y-error-border");
                              $el.closest("a[href]").insertAdjacentHTML('beforebegin', this.annotate(Lang._('ERROR'), Lang._('LINK_HYPERLINKED_IMAGE_ARIA_HIDDEN'), false, true));
                          }
                          else if ($el.closest("a[href]").textContent.trim().length === 0) {
                              this.errorCount++;
                              $el.classList.add("jooa11y-error-border");
                              $el.closest("a[href]").insertAdjacentHTML('beforebegin', this.annotate(Lang._('ERROR'), Lang._('LINK_IMAGE_LINK_NULL_ALT_NO_TEXT_MESSAGE'), false, true));
                          }
                          else {
                              $el.closest("a[href]").insertAdjacentHTML('beforebegin', this.annotate(Lang._('GOOD'), Lang._('LINK_LINK_HAS_ALT_MESSAGE'), false, true));
                          }
                      }

                      //Link and contains alt text.
                      else if (alt.length > 250 && $el.closest("a[href]")) {
                          this.warningCount++;
                          $el.classList.add("jooa11y-warning-border");
                          $el.closest("a[href]").insertAdjacentHTML(
                            'beforebegin',
                            this.annotate(
                              Lang._('WARNING'),
                              `${Lang._('HYPERLINK_ALT_LENGTH_MESSAGE')} <hr aria-hidden="true"> ${Lang.sprintf('HYPERLINK_ALT_LENGTH_MESSAGE_INFO', altText, altLength)}`,
                              false
                            )
                          );
                      }

                      //Link and contains an alt text.
                      else if (alt !== "" && $el.closest("a[href]") && $el.closest("a[href]").textContent.trim().length === 0) {
                          this.warningCount++;
                          $el.classList.add("jooa11y-warning-border");
                          $el.closest("a[href]").insertAdjacentHTML(
                            'beforebegin',
                            this.annotate(
                              Lang._('WARNING'),
                              `${Lang._('LINK_IMAGE_LINK_ALT_TEXT_MESSAGE')} <hr aria-hidden="true"> ${Lang.sprintf('LINK_IMAGE_LINK_ALT_TEXT_MESSAGE_INFO', altText)}`,
                              false
                            )
                          );
                      }

                      //Contains alt text & surrounding link text.
                      else if (alt !== "" && $el.closest("a[href]") && $el.closest("a[href]").textContent.trim().length > 1) {
                          this.warningCount++;
                          $el.classList.add("jooa11y-warning-border");
                          $el.closest("a[href]").insertAdjacentHTML(
                            'beforebegin',
                            this.annotate(Lang._('WARNING'),
                              `${Lang._('LINK_ANCHOR_LINK_AND_ALT_MESSAGE')} <hr aria-hidden="true"> ${Lang.sprintf('LINK_ANCHOR_LINK_AND_ALT_MESSAGE_INFO', altText)}`,
                              false
                            )
                          );
                      }

                      //Decorative alt and not a link. TODO: ADD NOT (ANCHOR) SELECTOR
                      else if (alt === "" || alt === " ") {
                          this.warningCount++;
                          $el.classList.add("jooa11y-warning-border");
                          $el.insertAdjacentHTML('beforebegin', this.annotate(Lang._('WARNING'),  Lang._('LINK_DECORATIVE_MESSAGE'), false, true));
                      }
                      else if (alt.length > 250) {
                          this.warningCount++;
                          $el.classList.add("jooa11y-warning-border");
                          $el.insertAdjacentHTML(
                            'beforebegin',
                            this.annotate(Lang._('WARNING'),
                              `${Lang._('LINK_ALT_TOO_LONG_MESSAGE')} <hr aria-hidden="true"> ${Lang.sprintf('LINK_ALT_TOO_LONG_MESSAGE_INFO', altText, altLength)}`,
                              false
                            )
                          );
                      }
                      else if (alt !== "") {
                          $el.insertAdjacentHTML('beforebegin', this.annotate(Lang._('GOOD'),  Lang.sprintf('LINK_PASS_ALT', altText), false, true));
                      }
                  }
              });
          };

          // ============================================================
          // Rulesets: Labels
          // ============================================================
          checkLabels () {
              const $inputs = Array.from(this.$root.querySelectorAll('input, select, textarea'))
                .filter($i => {
                  return !this.$containerExclusions.includes($i) && !isElementHidden($i);
                });

              $inputs.forEach((el) => {
                  let ariaLabel = this.computeAriaLabel(el);
                  const type = el.getAttribute('type');

                  //If button type is submit or button: pass
                  if (type === "submit" || type === "button" || type === "hidden") ;
                  //Inputs where type="image".
                  else if (type === "image") {
                      let imgalt = el.getAttribute("alt");
                      if (!imgalt || imgalt === ' ') {
                          if (el.getAttribute("aria-label")) ; else {
                              this.errorCount++;
                              el.classList.add("jooa11y-error-border");
                              el.insertAdjacentHTML(
                                'afterend',
                                this.annotate(Lang._('ERROR'), Lang._('LABELS_MISSING_IMAGE_INPUT_MESSAGE'), true)
                              );
                          }
                      }
                  }
                  //Recommendation to remove reset buttons.
                  else if (type === "reset") {
                    this.warningCount++;
                    el.classList.add("jooa11y-warning-border");
                    el.insertAdjacentHTML(
                      'afterend',
                      this.annotate(
                        Lang._('WARNING'),
                        `${Lang._('LABELS_INPUT_RESET_MESSAGE')} <hr aria-hidden="true"> ${Lang._('LABELS_INPUT_RESET_MESSAGE_TIP')}`,
                        true
                      )
                    );
                  }
                  //Uses ARIA. Warn them to ensure there's a visible label.
                  else if (el.getAttribute("aria-label") || el.getAttribute("aria-labelledby") || el.getAttribute("title")) {
                      if (el.getAttribute("title")) {
                        let ariaLabel = el.getAttribute("title");
                        this.warningCount++;
                        el.classList.add("jooa11y-warning-border");
                        el.insertAdjacentHTML(
                          'afterend',
                          this.annotate(
                            Lang._('WARNING'),
                            `${Lang._('LABELS_ARIA_LABEL_INPUT_MESSAGE')} <hr aria-hidden="true"> ${Lang.sprintf('LABELS_ARIA_LABEL_INPUT_MESSAGE_INFO', ariaLabel)}`,
                            true
                          )
                        );
                      } else {
                        this.warningCount++;
                        el.classList.add("jooa11y-warning-border");
                        el.insertAdjacentHTML(
                          'afterend',
                          this.annotate(
                            Lang._('WARNING'),
                            `${Lang._('LABELS_ARIA_LABEL_INPUT_MESSAGE')} <hr aria-hidden="true"> ${Lang.sprintf('LABELS_ARIA_LABEL_INPUT_MESSAGE_INFO', ariaLabel)}`,
                            true
                          )
                        );
                      }
                  }
                  //Implicit labels.
                  else if (el.closest('label') && el.closest('label').textContent.trim()) ;
                  //Has an ID but doesn't have a matching FOR attribute.
                  else if (el.getAttribute("id")
                    && Array.from(el.parentElement.children).filter($c => $c.nodeName === 'LABEL').length
                  ) {
                    const $labels = Array.from(el.parentElement.children).filter($c => $c.nodeName === 'LABEL');
                    let hasFor = false;

                    $labels.forEach(($l) => {
                      if (hasFor) return;

                      if ($l.getAttribute('for') === el.getAttribute('id')){
                        hasFor = true;
                      }
                    });

                    if (!hasFor) {
                      this.errorCount++;
                      el.classList.add("jooa11y-error-border");
                      el.insertAdjacentHTML(
                        'afterend',
                        this.annotate(
                          Lang._('ERROR'),
                          `${Lang._('LABELS_NO_FOR_ATTRIBUTE_MESSAGE')} <hr aria-hidden="true"> ${Lang.sprintf('LABELS_NO_FOR_ATTRIBUTE_MESSAGE_INFO', el.getAttribute('id'))}`,
                          true
                        )
                      );
                    }
                  }
                  else {
                    this.errorCount++;
                    el.classList.add("jooa11y-error-border");
                    el.insertAdjacentHTML(
                      'afterend',
                      this.annotate(Lang._('ERROR'), Lang._('LABELS_MISSING_LABEL_MESSAGE'), true)
                    );
                  }
              });
          };

          // ============================================================
          // Rulesets: Embedded content.
          // ============================================================
          checkEmbeddedContent() {
              const $findiframes = Array.from(this.$root.querySelectorAll("iframe, audio, video"));
              const $iframes = $findiframes.filter($el => !this.$containerExclusions.includes($el));

              //Warning: Video content.
              const $videos = $iframes.filter($el => $el.matches(this.options.videoContent));
              $videos.forEach(($el) => {
                  let track = $el.getElementsByTagName('TRACK');
                  if ($el.tagName === "VIDEO" && track.length) ; else {
                      this.warningCount++;
                      $el.classList.add("jooa11y-warning-border");
                      $el.insertAdjacentHTML('beforebegin', this.annotate(Lang._('WARNING'), Lang._('EMBED_VIDEO')));
                  }
              });

              //Warning: Audio content.
              const $audio = $iframes.filter($el => $el.matches(this.options.audioContent));
              $audio.forEach(($el) => {
                  this.warningCount++;
                  $el.classList.add("jooa11y-warning-border");
                  $el.insertAdjacentHTML('beforebegin', this.annotate(Lang._('WARNING'), Lang._('EMBED_AUDIO')));
                });

              //Error: iFrame is missing accessible name.
              $iframes.forEach(($el) => {
                  if ($el.tagName === "VIDEO" ||
                      $el.tagName === "AUDIO" ||
                      $el.getAttribute("aria-hidden") === "true" ||
                      $el.getAttribute("hidden") !== null ||
                      $el.style.display === 'none' ||
                      $el.getAttribute("role") === "presentation")
                      ;
                  else if ($el.getAttribute("title") === null || $el.getAttribute("title") === '') {
                      if ($el.getAttribute("aria-label") === null || $el.getAttribute("aria-label") === '') {
                          if ($el.getAttribute("aria-labelledby") === null) {
                              //Make sure red error border takes precedence
                              if ($el.classList.contains("jooa11y-warning-border")) {
                                  $el.classList.remove("jooa11y-warning-border");
                              }
                              this.errorCount++;
                              $el.classList.add("jooa11y-error-border");
                              $el.insertAdjacentHTML('beforebegin',
                                  this.annotate(Lang._('ERROR'), Lang._('EMBED_MISSING_TITLE'))
                              );
                          }
                      }
                  }
                  else ;
              });

              const $embeddedcontent = $iframes.filter($el => !$el.matches(this.options.embeddedContent));
              $embeddedcontent.forEach($el => {
                  if ($el.tagName === "VIDEO" ||
                      $el.tagName === "AUDIO" ||
                      $el.getAttribute("aria-hidden") === "true" ||
                      $el.getAttribute("hidden") !== null ||
                      $el.style.display === 'none' ||
                      $el.getAttribute("role") === "presentation" ||
                      $el.getAttribute("tabindex") === "-1")
                      ;
                  else {
                      this.warningCount++;
                      $el.classList.add("jooa11y-warning-border");
                      $el.insertAdjacentHTML('beforebegin',
                          this.annotate(Lang._('WARNING'), Lang._('EMBED_GENERAL_WARNING'))
                      );
                  }
              });
          }

          // ============================================================
          // Rulesets: QA
          // ============================================================
          checkQA() {

              //Error: Find all links pointing to development environment.
              const $findbadDevLinks = this.options.linksToFlag ? Array.from(this.$root.querySelectorAll(this.options.linksToFlag)) : [];
              const $badDevLinks = $findbadDevLinks.filter($el => !this.$containerExclusions.includes($el));
              $badDevLinks.forEach(($el) => {
                  this.errorCount++;
                  $el.classList.add("jooa11y-error-text");
                  $el.insertAdjacentHTML(
                    'afterend',
                    this.annotate(Lang._('ERROR'), Lang.sprintf('QA_BAD_LINK', $el.getAttribute('href')), true)
                  );
              });

              //Warning: Find all PDFs. Although only append warning icon to first PDF on page.
              let checkPDF = Array.from(this.$root.querySelectorAll('a[href$=".pdf"]'))
                .filter(p => !this.$containerExclusions.includes(p));
              let firstPDF = checkPDF[0];
              let pdfCount = checkPDF.length;

              if (checkPDF.length > 0) {
                this.warningCount++;
                checkPDF.forEach(($pdf) => {
                  $pdf.classList.add('jooa11y-warning-text');
                  if ($pdf.querySelector('img')) {
                    $pdf.classList.remove('jooa11y-warning-text');
                  }
                });
                firstPDF.insertAdjacentHTML(
                  'afterend',
                  this.annotate(Lang._('WARNING'), Lang.sprintf('QA_PDF_COUNT', pdfCount), true)
                );
              }

              //Warning: Detect uppercase.
              const $findallcaps = Array.from(this.$root.querySelectorAll("h1, h2, h3, h4, h5, h6, p, li:not([class^='jooa11y']), blockquote"));
              const $allcaps = $findallcaps.filter($el => !this.$containerExclusions.includes($el));
              $allcaps.forEach(function ($el) {
                  let uppercasePattern = /(?!<a[^>]*?>)(\b[A-Z][',!:A-Z\s]{15,}|\b[A-Z]{15,}\b)(?![^<]*?<\/a>)/g;

                  let html = $el.innerHTML;
                  $el.innerHTML = html.replace(uppercasePattern, "<span class='jooa11y-warning-uppercase'>$1</span>");
              });

              const $warningUppercase = document.querySelectorAll(".jooa11y-warning-uppercase");

              $warningUppercase.forEach(($el) => {
                  $el.insertAdjacentHTML('afterend', this.annotate(Lang._('WARNING'), Lang._('QA_UPPERCASE_WARNING'), true));
              });

              if ($warningUppercase.length > 0) {
                  this.warningCount++;
              }

              //Tables check.
              const $findtables = Array.from(this.$root.querySelectorAll("table:not([role='presentation'])"));
              const $tables = $findtables.filter($el => !this.$containerExclusions.includes($el));
              $tables.forEach(($el) => {
                  let findTHeaders = $el.querySelectorAll("th");
                  let findHeadingTags = $el.querySelectorAll("h1, h2, h3, h4, h5, h6");
                  if (findTHeaders.length === 0) {
                      this.errorCount++;
                      $el.classList.add("jooa11y-error-border");
                      $el.insertAdjacentHTML('beforebegin',
                        this.annotate(Lang._('ERROR'), Lang._('TABLES_MISSING_HEADINGS'))
                      );
                  }
                  if (findHeadingTags.length > 0) {
                      this.errorCount++;
                      findHeadingTags.forEach(($el) => {
                          $el.classList.add("jooa11y-error-heading");
                          $el.parentElement.classList.add("jooa11y-error-border");
                          $el.insertAdjacentHTML(
                            'beforebegin',
                            this.annotate(
                              Lang._('ERROR'),
                              `${Lang._('TABLES_SEMANTIC_HEADING')} <hr aria-hidden="true"> ${Lang._('TABLES_SEMANTIC_HEADING_INFO')}`
                            )
                          );
                      });
                  }
                  findTHeaders.forEach(($el) => {
                      if ($el.textContent.trim().length === 0) {
                          this.errorCount++;
                          $el.classList.add("jooa11y-error-border");
                          $el.innerHTML = this.annotate(
                            Lang._('ERROR'),
                            `${Lang._('TABLES_EMPTY_HEADING')} <hr aria-hidden="true"> ${Lang._('TABLES_EMPTY_HEADING_INFO')}`
                          );
                      }
                  });
              });

              //Error: Missing language tag. Lang should be at least 2 characters.
              const lang = document.querySelector("html").getAttribute("lang");
              if (!lang || lang.length < 2) {
                  this.errorCount++;
                  const jooa11yContainer = document.getElementById("jooa11y-container");
                  jooa11yContainer.insertAdjacentHTML('afterend', this.annotateBanner(Lang._('ERROR'), Lang._('QA_PAGE_LANGUAGE_MESSAGE')));
              }

              //Excessive bolding or italics.
              const $findstrongitalics = Array.from(this.$root.querySelectorAll("strong, em"));
              const $strongitalics = $findstrongitalics.filter($el => !this.$containerExclusions.includes($el));
              $strongitalics.forEach(($el) => {
                  if ($el.textContent.trim().length > 400) {
                      this.warningCount++;
                      $el.insertAdjacentHTML('beforebegin', this.annotate(Lang._('WARNING'), Lang._('QA_BAD_ITALICS')));
                    }
              });

              //Find blockquotes used as headers.
              const $findblockquotes = Array.from(this.$root.querySelectorAll("blockquote"));
              const $blockquotes = $findblockquotes.filter($el => !this.$containerExclusions.includes($el));
              $blockquotes.forEach(($el) => {
                  let bqHeadingText = $el.textContent;
                  if (bqHeadingText.trim().length < 25) {
                      this.warningCount++;
                      $el.classList.add("jooa11y-warning-border");
                      $el.insertAdjacentHTML(
                        'beforebegin',
                        this.annotate(
                          Lang._('WARNING'),
                          `${Lang.sprintf('QA_BLOCKQUOTE_MESSAGE', bqHeadingText)} <hr aria-hidden="true"> ${Lang._('QA_BLOCKQUOTE_MESSAGE_TIP')}`
                        )
                      );
                  }
              });

              // Warning: Detect fake headings.
              this.$p.forEach(($el) => {
                  let brAfter = $el.innerHTML.indexOf("</strong><br>");
                  let brBefore = $el.innerHTML.indexOf("<br></strong>");

                  //Check paragraphs greater than x characters.
                  if ($el && $el.textContent.trim().length >= 300) {
                    let firstChild = $el.firstChild;

                      //If paragraph starts with <strong> tag and ends with <br>.
                      if (firstChild.tagName === "STRONG" && (brBefore !== -1 || brAfter !== -1)) {
                        let boldtext = firstChild.textContent;

                          if ($el && boldtext.length <= 120) {
                            firstChild.classList.add("jooa11y-fake-heading", "jooa11y-error-heading");
                            $el.insertAdjacentHTML('beforebegin',
                                this.annotate(
                                  Lang._('WARNING'),
                                  `${Lang.sprintf('QA_FAKE_HEADING', boldtext)} <hr aria-hidden="true"> ${Lang._('QA_FAKE_HEADING_INFO')}`
                                )
                              );
                          }
                      }
                  }

                  // If paragraph only contains <p><strong>...</strong></p>.
                  if (/^<(strong)>.+<\/\1>$/.test($el.innerHTML.trim())) {
                    //Although only flag if it:
                    // 1) Has less than 120 characters (typical heading length).
                    // 2) The previous element is not a heading.
                    const prevElement = $el.previousElementSibling;
                    let tagName = "";
                    if (prevElement !== null) {
                        tagName = prevElement.tagName;
                    }
                    if ($el.textContent.length <= 120 && tagName.charAt(0) !== "H") {
                        let boldtext = $el.textContent;
                        $el.classList.add("jooa11y-fake-heading", "jooa11y-error-heading");
                        $el.firstChild.insertAdjacentHTML("afterend",
                        this.annotate(
                          Lang._('WARNING'),
                          `${Lang.sprintf('QA_FAKE_HEADING', boldtext)} <hr aria-hidden="true"> ${Lang._('QA_FAKE_HEADING_INFO')}`
                        )
                      );
                     }
                 }
              });
              if (this.$root.querySelectorAll(".jooa11y-fake-heading").length > 0) {
                  this.warningCount++;
              }

              /* Thanks to John Jameson from PrincetonU for this ruleset! */
              // Detect paragraphs that should be lists.
              let activeMatch = "";
              let prefixDecrement = {
                  b: "a",
                  B: "A",
                  2: "1",
              };
              let prefixMatch = /a\.|a\)|A\.|A\)|1\.|1\)|\*\s|-\s|--|•\s|→\s|✓\s|✔\s|✗\s|✖\s|✘\s|❯\s|›\s|»\s/;
              let decrement = function (el) {
                  return el.replace(/^b|^B|^2/, function (match) {
                      return prefixDecrement[match];
                  });
              };
              this.$p.forEach((el) => {
                  let hit = false;
                  // Grab first two characters.
                  let firstPrefix = el.textContent.substring(0, 2);
                  if (
                      firstPrefix.trim().length > 0 &&
                      firstPrefix !== activeMatch &&
                      firstPrefix.match(prefixMatch)
                  ) {
                      // We have a prefix and a possible hit
                      // Split p by carriage return if present and compare.
                      let hasBreak = el.innerHTML.indexOf("<br>");
                      if (hasBreak !== -1) {
                          let subParagraph = el.innerHTML.substring(hasBreak + 4).trim();
                          let subPrefix = subParagraph.substring(0, 2);
                          if (firstPrefix === decrement(subPrefix)) {
                              hit = true;
                          }
                      }
                      // Decrement the second p prefix and compare .
                      if (!hit) {
                          let $second = el.nextElementSibling.nodeName === 'P' ? el.nextElementSibling : null;
                          if ($second) {
                              let secondPrefix = decrement(
                                el.nextElementSibling.textContent.substring(0, 2)
                              );
                              if (firstPrefix === secondPrefix) {
                                  hit = true;
                              }
                          }
                      }
                      if (hit) {
                          this.warningCount++;
                          el.insertAdjacentHTML(
                            'beforebegin',
                            this.annotate(
                              Lang._('WARNING'),
                              `${Lang.sprintf('QA_SHOULD_BE_LIST', firstPrefix)} <hr aria-hidden="true"> ${Lang._('QA_SHOULD_BE_LIST_TIP')}`
                            )
                          );
                          el.classList.add("jooa11y-fake-list");
                          activeMatch = firstPrefix;
                      } else {
                          activeMatch = "";
                      }
                  } else {
                      activeMatch = "";
                  }
              });
              if (this.$root.querySelectorAll('.jooa11y-fake-list').length > 0) {
                  this.warningCount++;
              }
        };

          // ============================================================
          // Rulesets: Contrast
          // Color contrast plugin by jasonday: https://github.com/jasonday/color-contrast
          // ============================================================
          checkContrast () {
              const $findcontrast = Array.from(this.$root.querySelectorAll("* > :not(.jooa11y-heading-label)"));
              const $contrast = $findcontrast.filter($el => !this.$containerExclusions.includes($el));

              var contrastErrors = {
                  errors: [],
                  warnings: []
              };

              let elements = $contrast;
              let contrast = {
                  // Parse rgb(r, g, b) and rgba(r, g, b, a) strings into an array.
                  // Adapted from https://github.com/gka/chroma.js
                  parseRgb: function (css) {
                      let i, m, rgb, _i, _j;
                      if (m = css.match(/rgb\(\s*(\-?\d+),\s*(\-?\d+)\s*,\s*(\-?\d+)\s*\)/)) {
                          rgb = m.slice(1, 4);
                          for (i = _i = 0; _i <= 2; i = ++_i) {
                              rgb[i] = +rgb[i];
                          }
                          rgb[3] = 1;
                      } else if (m = css.match(/rgba\(\s*(\-?\d+),\s*(\-?\d+)\s*,\s*(\-?\d+)\s*,\s*([01]|[01]?\.\d+)\)/)) {
                          rgb = m.slice(1, 5);
                          for (i = _j = 0; _j <= 3; i = ++_j) {
                              rgb[i] = +rgb[i];
                          }
                      }
                      return rgb;

                  },
                  // Based on http://www.w3.org/TR/WCAG20/#relativeluminancedef
                  relativeLuminance: function (c) {
                      let lum = [];
                      for (let i = 0; i < 3; i++) {
                        let v = c[i] / 255;
                          lum.push(v < 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4));
                      }
                      return (0.2126 * lum[0]) + (0.7152 * lum[1]) + (0.0722 * lum[2]);
                  },
                  // Based on http://www.w3.org/TR/WCAG20/#contrast-ratiodef
                  contrastRatio: function (x, y) {
                    let l1 = contrast.relativeLuminance(contrast.parseRgb(x));
                    let l2 = contrast.relativeLuminance(contrast.parseRgb(y));
                    return (Math.max(l1, l2) + 0.05) / (Math.min(l1, l2) + 0.05);
                  },

                  getBackground: function (el) {
                      let styles = getComputedStyle(el),
                          bgColor = styles.backgroundColor,
                          bgImage = styles.backgroundImage,
                          rgb = contrast.parseRgb(bgColor) + '',
                          alpha = rgb.split(',');

                      // if background has alpha transparency, flag manual check
                      if (alpha[3] < 1 && alpha[3] > 0) {
                          return "alpha";
                      }

                      // if element has no background image, or transparent background (alpha == 0) return bgColor
                      if (bgColor !== 'rgba(0, 0, 0, 0)' && bgColor !== 'transparent' && bgImage === "none" && alpha[3] !== '0') {
                          return bgColor;
                      } else if (bgImage !== "none") {
                          return "image";
                      }

                      // retest if not returned above
                      if (el.tagName === 'HTML') {
                          return 'rgb(255, 255, 255)';
                      } else {
                          return contrast.getBackground(el.parentNode);
                      }
                  },
                  // check visibility - based on jQuery method
                  // isVisible: function (el) {
                  //     return !!(el.offsetWidth || el.offsetHeight || el.getClientRects().length);
                  // },
                  check: function () {
                      // resets results
                      contrastErrors = {
                          errors: [],
                          warnings: []
                      };

                      for (let i = 0; i < elements.length; i++) {
                          (function (elem) {

                              // Test if visible. Although we want invisible too.
                              if (contrast /* .isVisible(elem) */) {
                                  let style = getComputedStyle(elem),
                                      color = style.color,
                                      fill = style.fill,
                                      fontSize = parseInt(style.fontSize),
                                      pointSize = fontSize * 3 / 4,
                                      fontWeight = style.fontWeight,
                                      htmlTag = elem.tagName,
                                      background = contrast.getBackground(elem),
                                      textString = [].reduce.call(elem.childNodes, function (a, b) {
                                          return a + (b.nodeType === 3 ? b.textContent : '');
                                      }, ''),
                                      text = textString.trim(),
                                      ratio,
                                      error,
                                      warning;

                                  if (htmlTag === "SVG") {
                                      ratio = Math.round(contrast.contrastRatio(fill, background) * 100) / 100;
                                      if (ratio < 3) {
                                          error = {
                                              elem: elem,
                                              ratio: ratio + ':1'
                                          };
                                          contrastErrors.errors.push(error);
                                      }
                                  } else if (text.length || htmlTag === "INPUT" || htmlTag === "SELECT" || htmlTag === "TEXTAREA") {
                                      // does element have a background image - needs to be manually reviewed
                                      if (background === "image") {
                                          warning = {
                                              elem: elem
                                          };
                                          contrastErrors.warnings.push(warning);
                                      } else if (background === "alpha") {
                                          warning = {
                                              elem: elem
                                          };
                                          contrastErrors.warnings.push(warning);
                                      } else {
                                          ratio = Math.round(contrast.contrastRatio(color, background) * 100) / 100;
                                          if (pointSize >= 18 || (pointSize >= 14 && fontWeight >= 700)) {
                                              if (ratio < 3) {
                                                  error = {
                                                      elem: elem,
                                                      ratio: ratio + ':1'
                                                  };
                                                  contrastErrors.errors.push(error);
                                              }
                                          } else {
                                              if (ratio < 4.5) {
                                                  error = {
                                                      elem: elem,
                                                      ratio: ratio + ':1'
                                                  };
                                                  contrastErrors.errors.push(error);
                                              }
                                          }
                                      }
                                  }
                              }
                          })(elements[i]);
                      }
                      return contrastErrors;
                  }
              };

              contrast.check();
              //const {errorMessage, warningMessage} = jooa11yIM["contrast"];

              contrastErrors.errors.forEach(item => {
                  let name = item.elem;
                  let cratio = item.ratio;
                  let clone = name.cloneNode(true);
                  let removeJooa11yHeadingLabel = clone.querySelectorAll('.jooa11y-heading-label');
                  for(let i = 0; i < removeJooa11yHeadingLabel.length; i++){
                      clone.removeChild(removeJooa11yHeadingLabel[i]);
                  }
                  let nodetext = clone.textContent;

                  this.errorCount++;
                  name.insertAdjacentHTML(
                    'beforebegin',
                    this.annotate(
                        Lang._('ERROR'),
                        `${Lang.sprintf('CONTRAST_ERROR_MESSAGE', cratio, nodetext)}
                        <hr aria-hidden="true">
                        ${Lang.sprintf('CONTRAST_ERROR_MESSAGE_INFO', cratio, nodetext)}`,
                        true
                      )
                  );
              });

              contrastErrors.warnings.forEach(item => {
                  let name = item.elem;
                  let clone = name.cloneNode(true);
                  let removeJooa11yHeadingLabel = clone.querySelectorAll('.jooa11y-heading-label');
                  for(let i = 0; i < removeJooa11yHeadingLabel.length; i++){
                      clone.removeChild(removeJooa11yHeadingLabel[i]);
                  }
                  let nodetext = clone.textContent;

                  this.warningCount++;
                  name.insertAdjacentHTML(
                    'beforebegin',
                    this.annotate(
                        Lang._('WARNING'),
                        `${Lang._('CONTRAST_WARNING_MESSAGE')} <hr aria-hidden="true"> ${Lang.sprintf('CONTRAST_WARNING_MESSAGE_INFO', nodetext)}`, true
                      )
                  );
              });
          };

          // ============================================================
          // Rulesets: Readability
          // Adapted from Greg Kraus' readability script: https://accessibility.oit.ncsu.edu/it-accessibility-at-nc-state/developers/tools/readability-bookmarklet/
          // ============================================================
          checkReadability () {
              const container = document.querySelector(this.options.readabilityRoot);
              const $findreadability = Array.from(container.querySelectorAll("p, li"));
              const $readability = $findreadability.filter($el => !this.$containerExclusions.includes($el));

              //Crude hack to add a period to the end of list items to make a complete sentence.
              $readability.forEach($el => {
                  let listText = $el.textContent;
                  if (listText.charAt(listText.length - 1) !== ".") {
                      $el.insertAdjacentHTML("beforeend", "<span class='jooa11y-readability-period jooa11y-visually-hidden'>.</span>");
                  }
              });

              // Compute syllables: http://stackoverflow.com/questions/5686483/how-to-compute-number-of-syllables-in-a-word-in-javascript
              function number_of_syllables(wordCheck) {
                  wordCheck = wordCheck.toLowerCase().replace('.', '').replace('\n', '');
                  if (wordCheck.length <= 3) {
                      return 1;
                  }
                  wordCheck = wordCheck.replace(/(?:[^laeiouy]es|ed|[^laeiouy]e)$/, '');
                  wordCheck = wordCheck.replace(/^y/, '');
                  let syllable_string = wordCheck.match(/[aeiouy]{1,2}/g);
                  let syllables = 0;

                  if (!!syllable_string) {
                      syllables = syllable_string.length;
                  }

                  return syllables;
              }

              let readabilityarray = [];
              for (let i = 0; i < $readability.length; i++) {
                  var current = $readability[i];
                  if (current.textContent.replace(/ |\n/g,'') !== '') {
                      readabilityarray.push(current.textContent);
                  }
              }

              let paragraphtext = readabilityarray.join(' ').trim().toString();
              let words_raw = paragraphtext.replace(/[.!?-]+/g, ' ').split(' ');
              let words = 0;
              for (let i = 0; i < words_raw.length; i++) {
                  if (words_raw[i] != 0) {
                      words = words + 1;
                  }
              }

              let sentences_raw = paragraphtext.split(/[.!?]+/);
              let sentences = 0;
              for (let i = 0; i < sentences_raw.length; i++) {
                  if (sentences_raw[i] !== '') {
                      sentences = sentences + 1;
                  }
              }

              let total_syllables = 0;
              let syllables1 = 0;
              let syllables2 = 0;
              for (let i = 0; i < words_raw.length; i++) {
                  if (words_raw[i] != 0) {
                      var syllable_count = number_of_syllables(words_raw[i]);
                      if (syllable_count === 1) {
                          syllables1 = syllables1 + 1;
                      }
                      if (syllable_count === 2) {
                          syllables2 = syllables2 + 1;
                      }
                      total_syllables = total_syllables + syllable_count;
                  }
              }

              //var characters = paragraphtext.replace(/[.!?|\s]+/g, '').length;
              //Reference: https://core.ac.uk/download/pdf/6552422.pdf
              //Reference: https://github.com/Yoast/YoastSEO.js/issues/267

              let flesch_reading_ease;
              if (this.options.readabilityLang === 'en') {
                  flesch_reading_ease = 206.835 - (1.015 * words / sentences) - (84.6 * total_syllables / words);
              } else if (this.options.readabilityLang === 'fr') {
                  //French (Kandel & Moles)
                  flesch_reading_ease = 207 - (1.015 * words / sentences) - (73.6 * total_syllables / words);
              } else if (this.options.readabilityLang === 'es') {
                  flesch_reading_ease = 206.84 - (1.02 * words / sentences) - (0.60 * (100 * total_syllables / words));
              }

              if (flesch_reading_ease > 100) {
                  flesch_reading_ease = 100;
              } else if (flesch_reading_ease < 0) {
                  flesch_reading_ease = 0;
              }

              const $readabilityinfo = document.getElementById("jooa11y-readability-info");

              if (paragraphtext.length === 0) {
                  $readabilityinfo.innerHTML = Lang._('READABILITY_NO_P_OR_LI_MESSAGE');
              }
              else if (words > 30) {
                  let fleschScore = flesch_reading_ease.toFixed(1);
                  let avgWordsPerSentence = (words / sentences).toFixed(1);
                  let complexWords = Math.round(100 * ((words - (syllables1 + syllables2)) / words));

                  //WCAG AAA pass if greater than 60
                  if (fleschScore >= 0 && fleschScore < 30) {
                      $readabilityinfo.innerHTML =
                          `<span>${fleschScore}</span> <span class="jooa11y-readability-score">${Lang._('VERY_DIFFICULT_READABILITY')}</span>`;

                  } else if (fleschScore > 31 && fleschScore < 49) {
                      $readabilityinfo.innerHTML =
                          `<span>${fleschScore}</span> <span class="jooa11y-readability-score">${Lang._('DIFFICULT_READABILITY')}</span>`;

                  } else if (fleschScore > 50 && fleschScore < 60) {
                      $readabilityinfo.innerHTML =
                          `<span>${fleschScore}</span> <span class="jooa11y-readability-score">${Lang._('FAIRLY_DIFFICULT_READABILITY')}</span>`;
                  } else {
                      $readabilityinfo.innerHTML =
                          `<span>${fleschScore}</span> <span class="jooa11y-readability-score">${Lang._('GOOD_READABILITY')}</span>`;
                  }

                  document.getElementById("jooa11y-readability-details").innerHTML =
                  `<li><span class='jooa11y-bold'>${Lang._('AVG_WORD_PER_SENTENCE')}</span> ${avgWordsPerSentence}</li>
                <li><span class='jooa11y-bold'>${Lang._('COMPLEX_WORDS')}</span> ${complexWords}%</li>
                <li><span class='jooa11y-bold'>${Lang._('TOTAL_WORDS')}</span> ${words}</li>`;
              }
              else {
                  $readabilityinfo.textContent = Lang._('READABILITY_NOT_ENOUGH_CONTENT_MESSAGE');
              }
          };

      //----------------------------------------------------------------------
      // Templating for Error, Warning and Pass buttons.
      //----------------------------------------------------------------------
      annotate(type, content, inline = false) {
        const validTypes = [
          Lang._('ERROR'),
          Lang._('WARNING'),
          Lang._('GOOD'),
        ];

        if (validTypes.indexOf(type) === -1) {
          throw Error(`Invalid type [${type}] for annotation`);
        }

        const CSSName = {
          [validTypes[0]]: "error",
          [validTypes[1]]: "warning",
          [validTypes[2]]: "good",
        };

        // Check if content is a function
        if (content && {}.toString.call(content) === "[object Function]") {
          // if it is, call it and get the value.
          content = content();
        }

        // Escape content, it is need because it used inside data-tippy-content=""
        content = escapeHTML(content);

        return `
        <div class=${inline ? "jooa11y-instance-inline" : "jooa11y-instance"}>
            <button
            type="button"
            aria-label="${[type]}"
            class="jooa11y-btn jooa11y-${CSSName[type]}-btn${inline ? "-text" : ""}"
            data-tippy-content="<div lang='${this.options.langCode}'>
                <div class='jooa11y-header-text'>${[type]}</div>
                ${content}
            </div>
        ">
        </button>
        </div>`;
      };

      //----------------------------------------------------------------------
      // Templating for full-width banners.
      //----------------------------------------------------------------------
      annotateBanner(type, content) {
        const validTypes = [
          Lang._('ERROR'),
          Lang._('WARNING'),
          Lang._('GOOD'),
        ];

        if (validTypes.indexOf(type) === -1) {
          throw Error(`Invalid type [${type}] for annotation`);
        }

        const CSSName = {
          [validTypes[0]]: "error",
          [validTypes[1]]: "warning",
          [validTypes[2]]: "good",
        };

        // Check if content is a function
        if (content && {}.toString.call(content) === "[object Function]") {
          // if it is, call it and get the value.
          content = content();
        }

        return `<div class="jooa11y-instance jooa11y-${CSSName[type]}-message-container">
      <div role="region" aria-label="${[type]}" class="jooa11y-${CSSName[type]}-message" lang="${this.options.langCode}">
          ${content}
      </div>
  </div>`;
      };

    }

  var jooa11y = {
    Lang,
    Jooa11y,
  };



  /*-----------------------------------------------------------------------
  sa11y: the accessibility quality assurance assistant.
  Author: Development led by Adam Chaboryk at Ryerson University.
  All acknowledgements and contributors: https://github.com/ryersondmp/jooa11y
  License: https://github.com/ryersondmp/jooa11y/blob/master/LICENSE.md
  Copyright (c) 2020 - 2021 Ryerson University
  The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
  ------------------------------------------------------------------------*/

  return jooa11y;

}));
