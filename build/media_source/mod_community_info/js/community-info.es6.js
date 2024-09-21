/**
 * Sprintf functionality for JText
 *
 * @param   {String}   text   The text string
 *
 * @returns {String}   The processed text
 */
const sprintf = function (text, ...args) {
  let i = 0;
  return text.replace(/%s/g, () => {
    const result = args[i];
    i += 1;
    return result;
  });
};

/**
 * Fix a geolocation string
 *
 * @param   {String}   geolocation   Geolocation string
 *
 * @return  {String}   Fixed string
 */
const fixGeolocation = function (geolocation) {
  // Split the input string into latitude and longitude
  const coorArr = geolocation.split(',', 2);

  // Split latitude and longitude into their integer and decimal parts
  const latArr = coorArr[0].split('.', 2);
  const lngArr = coorArr[1].split('.', 2);

  // Trim and format the geolocation to the form 51.5000,0.0000
  const fixedGeolocation = `${latArr[0].trim()}.${latArr[1].trim().substring(0, 4)},${lngArr[0].trim()}.${lngArr[1].trim().substring(0, 4)}`;

  return fixedGeolocation;
};

/**
 * Get current position of device
 *
 * @returns {Promise<String>}   A promise that resolves to the location string (e.g., "51.5000,0.0000")
 */
const getCurrentLocation = async function () {
  return new Promise((resolve, reject) => {
    if ('geolocation' in navigator) {
      navigator.geolocation.getCurrentPosition(
        (position) => {
          const { latitude, longitude } = position.coords;
          resolve(`${latitude},${longitude}`);
        },
        (error) => {
          reject(new Error(`${Joomla.Text._('MOD_COMMUNITY_ERROR_GET_LOCATION')} ${error.message}`));
        },
      );
    } else {
      reject(new Error(Joomla.Text._('MOD_COMMUNITY_MSG_GEOLOCATION_NOT_SUPPORTED')));
    }
  });
};

/**
 * Activate automatic location service
 *
 * @param   {Interger} moduleId  ID of the current module
 */
const autoLoc = async function (moduleId) {
  const location = await getCurrentLocation();
  const [latitude, longitude] = location.split(',', 2);

  document.getElementById(`jform_lat${moduleId}`).value = latitude;
  document.getElementById(`jform_lng${moduleId}`).value = longitude;
  document.getElementById(`jform_autoloc${moduleId}`).value = '1';

  document.getElementById(`module_task${moduleId}`).value = 'autoLocation';
  document.getElementById(`location-form-${moduleId}`).submit();
};

/**
 * Save manually chosen location
 *
 * @param   {Interger} moduleId  ID of the current module
 */
const saveLoc = function (moduleId) {
  document.getElementById(`jform_autoloc${moduleId}`).value = '0';
  document.getElementById(`module_task${moduleId}`).value = 'saveLocation';
  document.getElementById(`location-form-${moduleId}`).submit();
};

/**
 * Change manual location selection
 *
 * @param   {Interger} moduleId  ID of the current module
 */
const locationSelectChange = function (event, moduleId) {
  const selectedValue = event.target.value;
  const [latitude, longitude] = selectedValue.split(',', 2);

  // Add first element to input
  document.getElementById(`jform_lat${moduleId}`).value = latitude;
  document.getElementById(`jform_lng${moduleId}`).value = longitude;

  // Activate button
  document.getElementById(`btn-saveLoc${moduleId}`).disabled = false;
};

/**
 * Perform an ajax request to the helper method via com_ajax
 *
 * @param   {Interger} moduleId      ID of the current module
 * @param   {String}   method        Name of the target method in the module helper class
 * @param   {Object}   requestVars   Object with an entiry for each request variable to be set
 * @param   {String}   msgString     Message string to be appended tp MOD_COMMUNITY_ERROR_ for error output
 *
 * @returns {Object} Result object
 *          {success: true, status: 200, message: '', messages: {}, data: {}}
 */
const ajaxTask = async function (moduleId, method, requestVars, msgString) {
  // Create form data
  const formData = new FormData();
  formData.append('module_id', moduleId);

  // Append request variables as form data
  Object.entries(requestVars).forEach(([key, value]) => {
    formData.append(key, value);
  });

  // Set request parameters
  const parameters = {
    method: 'POST',
    mode: 'same-origin',
    cache: 'default',
    redirect: 'follow',
    referrerPolicy: 'no-referrer-when-downgrade',
    body: formData,
    enctype: 'multipart/form-data',
  };

  // Set the URL
  const url = `index.php?option=com_ajax&module=community_info&method=${method}&format=json`;

  // Perform the fetch request
  const response = await fetch(url, parameters);
  const txt = await response.text();

  // Initialize data
  let data = null;

  if (!response.ok) {
    // Catch network error
    const message = Joomla.Text._(`MOD_COMMUNITY_ERROR_${msgString}`);
    Joomla.renderMessages({ error: [`${message}`] });

    return data;
  }

  if (txt.startsWith('{"success"')) {
    // Response is of type json --> everything fine
    const res = JSON.parse(txt);
    try {
      data = JSON.parse(res.data);
    } catch (e) {
      // no need to parse a json string.
      data = res.data;
    }
  } else if (txt.includes('Fatal error')) {
    // PHP fatal error occurred
    const message = Joomla.Text._(`MOD_COMMUNITY_ERROR_${msgString}`);
    Joomla.renderMessages({ error: [`${message}`] });
  } else {
    // Response is not of type json --> probably some php warnings/notices
    const split = txt.split('\n{"');
    const temp = JSON.parse(`{"${split[1]}`);
    data = JSON.parse(temp.data);

    const message = Joomla.Text._(`MOD_COMMUNITY_ERROR_${msgString}`);

    if (Joomla.getOptions('mod_community_info').debug === 1) {
      Joomla.renderMessages({ error: [`${message}`, `Message: ${split[0]}`, `Messages: ${temp.messages}`, `Data: ${data}`] });
    } else {
      Joomla.renderMessages({ error: [`${message}`] });
    }
  }

  return data;
};

/**
 * Logging to the Joomla logger
 *
 * @param   {String}   msg      The message to be logged
 * @param   {String}   prio     The logging priority (error, warning, notice, info, debug)
 * @param   {Boolean}  always   True to log anyway, False only when debug is enabled
 */
const addLog = async function (msg, prio, always = false) {
  if (always || Joomla.getOptions('mod_community_info').debug === 1) {
    try {
      const result = await ajaxTask(1, 'addLog', { message: msg, priority: prio }, 'ADD_LOG');

      if (result && result !== 'True') {
        Joomla.renderMessages({ error: [result] });
      }
    } catch (error) {
      Joomla.renderMessages({ error: ['Problem reaching com_ajax.'] });
    }
  }
};

/**
 * Fetches data from an endpoint
 *
 * @param   {String}   url        Request url
 * @param   {Object}   variables  Request variables
 * @param   {String}   format     The expected format of the returned content
 *
 * @returns {Object} Result object
 */
const fetchAPI = async function (url, variables = {}, format = 'json') {
  const urlSearchParams = new URLSearchParams(variables);

  let targetUrl = url;
  if (Object.keys(variables).length !== 0) {
    targetUrl = `${url}?${urlSearchParams.toString()}`;
  }

  // Set request parameters
  const parameters = {
    method: 'GET',
    mode: 'cors',
    cache: 'default',
    redirect: 'follow',
    referrerPolicy: 'origin',
  };

  // Perform the fetch request
  const response = await fetch(targetUrl, parameters);

  // Initialize data
  let data = null;

  if (!response.ok) {
    // Catch network error
    const message = Joomla.Text._('MOD_COMMUNITY_ERROR_FETCH_API');
    Joomla.renderMessages({ error: [sprintf(message, targetUrl, response.status, response.statusText)] });
    addLog(`fetchAPI request failed, Status Code: ${response.status}, Message: ${response.statusText}`, 'error', true);

    return data;
  }

  // Request successful
  const txt = await response.text();
  let error = false;
  let errorcode = '';

  if (format === 'json') {
    try {
      data = JSON.parse(txt);
    } catch (err) {
      error = true;
      errorcode = err.message;
    }
  } else if (format === 'xml') {
    const parser = new DOMParser();
    data = parser.parseFromString(txt, 'text/xml');

    if (data.getElementsByTagName('parsererror').length > 0) {
      error = true;
      errorcode = data.getElementsByTagName('parsererror')[0].textContent;
    }
  } else {
    data = txt;
  }

  if (error) {
    // Parsing error
    const message = Joomla.Text._('MOD_COMMUNITY_ERROR_FETCH_API');
    Joomla.renderMessages({ error: [sprintf(message, targetUrl, '-', errorcode)] });
    addLog(`fetchAPI request failed, Status Code: -, Message: ${errorcode}`, 'error', true);

    return null;
  }

  return data;
};

/**
 * Search for a location
 *
 * @param   {Interger} moduleId  ID of the current module
 */
const searchLocation = async function (moduleId) {
  const search = document.getElementById(`locsearch${moduleId}`).value;

  // Fetch search results
  const res = await fetchAPI('https://nominatim.openstreetmap.org/search.php', { q: search, format: 'jsonv2' });

  if (Array.isArray(res) && res.length > 0) {
    // Create the selection list
    const select = document.createElement('select');
    select.className = 'form-select';
    select.size = Math.max(Math.min(res.length, 15), 2);

    res.forEach((result, i) => {
      const option = document.createElement('option');
      option.value = `${result.lat},${result.lon}`;
      option.textContent = `${result.name} (${result.display_name.slice(0, 100)}...)`;
      if (i === 0) {
        option.selected = true;
        // Add first element to input
        document.getElementById(`jform_lat${moduleId}`).value = result.lat;
        document.getElementById(`jform_lng${moduleId}`).value = result.lon;
      }
      select.appendChild(option);
    });

    // Place selection into DOM
    const resultsContainer = document.getElementById(`locsearch_results${moduleId}`);
    resultsContainer.innerHTML = '';
    resultsContainer.appendChild(select);

    // Install event listener
    select.addEventListener('change', (event) => {
      locationSelectChange(event, moduleId);
    });

    // Activate button
    document.getElementById(`btn-saveLoc${moduleId}`).disabled = false;
  } else {
    document.getElementById(`locsearch_results${moduleId}`).innerHTML = `<p>${Joomla.Text._('MOD_COMMUNITY_MSG_NO_LOCATIONS_FOUND')}</p>`;

    // Deactivate button
    document.getElementById(`btn-saveLoc${moduleId}`).disabled = true;
  }
};

/**
 * Opens a bootstrap modal based on the provided ID
 *
 * @param {Integer} moduleId   ID of the module
 * @param {String}  modalId    ID of the modal
 */
const openModal = function (moduleId, modalId) {
  const modal = document.getElementById(modalId);
  const modalBody = modal.querySelector('.modal-body');
  const modalFooter = modal.querySelector('.modal-footer');
  const templateContent = document.getElementById(`template-${modalId}-body`).innerHTML;

  const allowedTags = {
    form: ['name', 'id', 'class', 'aria-label'],
  };

  const sanitizedContent = Joomla.sanitizeHtml(templateContent, allowedTags);
  modalBody.innerHTML = sanitizedContent;

  // Add missing attributes and event listeners
  const form = modalBody.querySelector('form');
  if (form) {
    form.action = window.location.href;
    form.method = 'post';
    form.enctype = 'multipart/form-data';
  }

  // Install event listener on btn-locsearch
  const searchButton = modalBody.querySelector(`#btn-locsearch${moduleId}`);
  if (searchButton) {
    searchButton.addEventListener('click', () => searchLocation(moduleId));
  }

  // Install event listener on btn-autoLoc
  const autoLocButton = modalFooter.querySelector(`#btn-autoLoc${moduleId}`);
  if (autoLocButton) {
    autoLocButton.addEventListener('click', () => autoLoc(moduleId));
  }

  // Install event listener on btn-saveLoc
  const saveLocButton = modalFooter.querySelector(`#btn-saveLoc${moduleId}`);
  if (saveLocButton) {
    saveLocButton.addEventListener('click', () => saveLoc(moduleId));
  }

  // Open modal
  const bsmodal = new bootstrap.Modal(document.getElementById(modalId), { keyboard: false });
  bsmodal.show();

  // Define default submit button of location form
  document.getElementById(`location-form-${moduleId}`).addEventListener('keydown', (event) => {
    if (event.key === 'Enter') {
      event.preventDefault();

      // Trigger the desired button
      document.getElementById(`btn-locsearch${moduleId}`).click();
    }
  });
};

/**
 * Checks if loaded data is still valid
 *
 * @param   {String}   datetime   Timestamp of the cached data
 * @param   {Integer}  moduleId   ID of the module
 *
 * @returns {Bool}     True if cached data is still valid, false otherwise
 */
const checkCache = function (datetime, moduleId) {
  // Convert to JavaScript Date object
  const date = new Date(datetime.replace(' ', 'T'));

  // Get cachtime param of module
  const cachetime = parseInt(document.getElementById(`CommunityInfo${moduleId}`).getAttribute('data-cachetime'), 10);

  // Calculate the cachetime limit
  const now = new Date();
  const limit = new Date(now.getTime() - cachetime * 60 * 60 * 1000);

  if (date < limit) {
    // Datetime is older than allowed cachetime
    return false;
  }

  // Datetime is within the allowed cachetime
  return true;
};

/**
 * Fetches new content and updates it in the module
 *
 * @param {Integer}  moduleId      ID of the module
 * @param {Bool}     forceUpdate   True to force an update of the content
 */
const updateContent = async function (moduleId, forceUpdate = false) {
  let update = false;
  let communityLinks = {};
  let communityNews = {};
  let communityEvents = {};

  const linksTime = document.getElementById(`contactTxt${moduleId}`).getAttribute('data-fetch-time');

  if (forceUpdate || !checkCache(linksTime, moduleId)) {
    // Links are outdated and need update
    communityLinks = await ajaxTask(moduleId, 'getLinks', {}, 'FETCH_LINKS');
    addLog(`Fetched community links: ${communityLinks}`, 'debug', false);

    // Get current link texts
    const contactTxt = document.getElementById(`contactTxt${moduleId}`);
    const contributeTxt = document.getElementById(`contributeTxt${moduleId}`);

    if (communityLinks && contactTxt !== null) {
      // Exchange contact link text
      const tempDiv = document.createElement('div');
      tempDiv.innerHTML = communityLinks.html.contact;
      contactTxt.parentNode.replaceChild(tempDiv.firstChild, contactTxt);
    }

    if (communityLinks && contributeTxt !== null) {
      // Exchange contribute link text
      const tempDiv = document.createElement('div');
      tempDiv.innerHTML = communityLinks.html.contribute;
      const contributeTxtParent = contributeTxt.parentNode;
      contributeTxtParent.replaceChild(tempDiv.firstChild, contributeTxt);
      contributeTxtParent.appendChild(tempDiv.lastChild);
    }

    // Links successfully updated
    update = true;
  }

  const newsTime = document.getElementById(`collapseNews${moduleId}`).getAttribute('data-fetch-time');

  if (update || !checkCache(newsTime, moduleId)) {
    // Fetch news feed
    communityNews = await ajaxTask(moduleId, 'getNewsFeed', { url: communityLinks.links.news_feed }, 'FETCH_NEWS');
    addLog(`Fetched news feed: ${communityNews}`, 'debug', false);

    // Get current news feed table
    const newsFeetTable = document.getElementById(`collapseNews${moduleId}`);

    if (communityNews && newsFeetTable !== null) {
      // Exchange news feed table
      const tempDiv = document.createElement('div');
      tempDiv.innerHTML = communityNews.html;
      newsFeetTable.parentNode.replaceChild(tempDiv.firstChild, newsFeetTable);
    }
  }

  const eventsTime = document.getElementById(`collapseEvents${moduleId}`).getAttribute('data-fetch-time');

  if (update || !checkCache(eventsTime, moduleId)) {
    // Fetch events feed
    communityEvents = await ajaxTask(moduleId, 'getEventsFeed', { url: communityLinks.links.events_feed }, 'FETCH_EVENTS');
    addLog(`Fetched events feed: ${communityEvents}`, 'debug', false);

    // Get current events feed table
    const eventsFeetTable = document.getElementById(`collapseEvents${moduleId}`);

    if (communityEvents && eventsFeetTable !== null) {
      // Exchange events feed table
      const tempDiv = document.createElement('div');
      tempDiv.innerHTML = communityEvents.html;
      eventsFeetTable.parentNode.replaceChild(tempDiv.firstChild, eventsFeetTable);
    }
  }
};

/**
 * Initialize all com_community_info modules
 *
 */
const iniModules = async function () {
  // Select all elements whose id starts with 'CommunityInfo'
  const modules = document.querySelectorAll('[id^="CommunityInfo"]');

  await Promise.all(Array.from(modules).map(async (moduleBody) => {
    const idPattern = /^CommunityInfo(\d+)$/; // Pattern to match IDs like 'CommunityInfo111'
    const match = moduleBody.id.match(idPattern);
    const moduleId = match ? parseInt(match[1], 10) : 0;

    // Prepare location picker
    const moduleHeader = moduleBody.parentNode.previousElementSibling;
    const locationTemplateContent = document.getElementById('template-location-picker').innerHTML;
    const locationContent = Joomla.sanitizeHtml(locationTemplateContent);
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = locationContent;

    // Append location picker
    while (tempDiv.firstChild) {
      moduleHeader.appendChild(tempDiv.firstChild);
    }

    // Install event listener
    const alinks = moduleHeader.querySelectorAll('a[data-modal-id]');
    alinks.forEach((link) => {
      link.addEventListener('click', (event) => {
        event.preventDefault();
        const modalId = link.getAttribute('data-modal-id');
        openModal(moduleId, modalId);
      });
    });

    // Prepare modal
    document.getElementById(`location-modal${moduleId}`).classList.add('mod-community-info');

    // Get parameter auto_location
    let autoLocation = moduleBody.getAttribute('data-autoloc');
    autoLocation = parseInt(autoLocation, 10);

    // Get old location from html
    let locChanged = false;
    let oldLocation = document.querySelectorAll(`[data-modal-id="location-modal${moduleId}"]`)[0].getAttribute('data-geolocation');
    oldLocation = fixGeolocation(oldLocation);

    // Get auto location
    if (autoLocation === 1 && moduleId > 0) {
      try {
        let location = await getCurrentLocation();
        location = fixGeolocation(location);

        if (oldLocation !== location) {
          // Location has changed
          locChanged = true;
          const response = await ajaxTask(moduleId, 'setLocation', { current_location: location }, 'SAVE_LOCATION');
          addLog(`Update location: ${Joomla.Text._(response)}`, 'debug', false);
        } else {
          addLog('Location is up to date.', 'debug', false);
        }
      } catch (error) {
        addLog(`Error during autolocation: ${error}`, 'debug', false);
      }
    }

    // Update module content
    updateContent(moduleId, locChanged);
  }));
};

iniModules();
