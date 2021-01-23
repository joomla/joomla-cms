import Tab from '../../../../../node_modules/bootstrap/js/src/tab';

Joomla = Joomla || {};
Joomla.Bootstrap = Joomla.Bootstrap || {};
Joomla.Bootstrap.Initialise = Joomla.Bootstrap.Initialise || {};
Joomla.Bootstrap.Instances = Joomla.Bootstrap.Instances || {};
Joomla.Bootstrap.Instances.Tab = new WeakMap();

/**
 * Initialise the Tabs interactivity
 *
 * @param {HTMLElement} el The element that will become an collapse
 * @param {object} options The options for this collapse
 */
Joomla.Bootstrap.Initialise.Tab = (el, options) => {
  if (!(el instanceof Element) && options.isJoomla) {
    const nSelector = el.split('.')[1];
    if (!nSelector) {
      throw new Error('The selector is invalid, check your PHP side');
    }
    const tab = document.querySelector(`#${nSelector}Content`);
    if (tab) {
      const related = Array.from(tab.children);

      // Build the navigation
      if (related.length) {
        related.forEach((element) => {
          if (!element.classList.contains('tab-pane')) {
            return;
          }

          const isActive = element.dataset.active !== '';
          const ul = document.querySelector(`#${nSelector}Tabs`);

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

            // As we are re-rendering text already displayed on the page we judge that there isn't a risk of XSS attacks
            link.innerHTML = element.dataset.title;

            const li = document.createElement('li');
            li.classList.add('nav-item');
            li.setAttribute('role', 'presentation');
            li.appendChild(link);

            ul.appendChild(li);
          }
        });
      }

      if (Joomla.Bootstrap.Instances.Tab.get(tab) && tab.dispose) {
        tab.dispose();
      }

      Joomla.Bootstrap.Instances.Tab.set(tab, new Tab(tab));
    }
  } else {
    if (!(el instanceof Element)) {
      return;
    }
    if (Joomla.Bootstrap.Instances.Tab.get(el) && el.dispose) {
      el.dispose();
    }

    Joomla.Bootstrap.Instances.Tab.set(el, new Tab(el, options));
  }
};

// Ensure vanilla mode, for consistency of the events
if (!Object.prototype.hasOwnProperty.call(document.body.dataset, 'bsNoJquery')) {
  document.body.dataset.bsNoJquery = '';
}

// Get the elements/configurations from the PHP
const tabs = Joomla.getOptions('bootstrap.tabs');
// Initialise the elements
if (typeof tabs === 'object' && tabs !== null) {
  Object.keys(tabs).forEach((tab) => {
    Joomla.Bootstrap.Initialise.Tab(tab, tabs[tab]);
  });
}

export default Tab;
