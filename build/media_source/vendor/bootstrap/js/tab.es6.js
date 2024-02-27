import Tab from 'bootstrap/js/src/tab';

window.Joomla = window.Joomla || {};
window.bootstrap = window.bootstrap || {};
window.bootstrap.Tab = Tab;

/**
 * Initialise the Tabs interactivity
 *
 * @param {HTMLElement} el The element that will become a collapse
 * @param {object} options The options for this collapse
 */
Joomla.initialiseTabs = (el, options) => {
  if (!(el instanceof Element) && options.isJoomla) {
    const tab = document.querySelector(`${el}Content`);
    if (tab) {
      const related = Array.from(tab.children);

      // Build the navigation
      if (related.length) {
        related.forEach((element) => {
          if (!element.classList.contains('tab-pane')) {
            return;
          }

          const isActive = element.dataset.active !== '';
          const ul = document.querySelector(`${el}Tabs`);

          if (ul) {
            const link = document.createElement('a');
            link.href = `#${element.dataset.id}`;
            link.classList.add('nav-link');
            if (isActive) {
              link.classList.add('active');
            }

            link.dataset.bsToggle = 'tab';
            link.setAttribute('role', 'tab');
            link.setAttribute('aria-controls', element.dataset.id);
            link.setAttribute('aria-selected', element.dataset.id);
            link.innerHTML = Joomla.sanitizeHtml(element.dataset.title);

            const li = document.createElement('li');
            li.classList.add('nav-item');
            li.setAttribute('role', 'presentation');
            li.appendChild(link);

            ul.appendChild(li);

            // eslint-disable-next-line no-new
            new window.bootstrap.Tab(li);
          }
        });
      }
    }
  } else {
    document.querySelectorAll(`${el} a`).forEach((tab) => new window.bootstrap.Tab(tab, options));
  }
};

if (Joomla && Joomla.getOptions) {
  // Get the elements/configurations from the PHP
  const tabs = Joomla.getOptions('bootstrap.tabs');
  // Initialise the elements
  if (typeof tabs === 'object' && tabs !== null) {
    Object.keys(tabs).map((tab) => Joomla.initialiseTabs(tab, tabs[tab]));
  }
}

export default Tab;
