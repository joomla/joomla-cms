import Tab from '../../../../../node_modules/bootstrap/js/src/tab';

Joomla = Joomla || {};
Joomla.Bootstrap = Joomla.Bootstrap || {};
Joomla.Bootstrap.Initialise = Joomla.Bootstrap.Initialise || {};
Joomla.Bootstrap.Instances = Joomla.Bootstrap.Instances || {};
Joomla.Bootstrap.Instances.Tab = new WeakMap();

const tabs = Joomla.getOptions('bootstrap.tab');

// Force Vanilla mode!
if (!Object.prototype.hasOwnProperty.call(document.body.dataset, 'bsNoJquery')) {
  document.body.dataset.bsNoJquery = '';
}

if (tabs) {
  // eslint-disable-next-line no-restricted-syntax
  for (const tabSelector of tabs) {
    const nSelector = tabSelector.split('.')[1];
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

            link.innerHTML = element.dataset.title; // Not safe!!!

            const li = document.createElement('li');
            li.classList.add('nav-item');
            li.setAttribute('role', 'presentation');
            li.appendChild(link);

            ul.appendChild(li);
          }
        });
      }

      Joomla.Bootstrap.Instances.Tab.set(tab, new Tab(tab));
    }
  }
}

export default Tab;
