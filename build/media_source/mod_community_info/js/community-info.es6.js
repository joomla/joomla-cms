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
    console.log(`mod_community_info: fetchAPI request failed. Status Code: ${response.status}. Message: ${response.statusText}`);

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
    console.log(`mod_community_info: fetchAPI request failed. Status Code: -. Message: ${errorcode}`);

    return null;
  }

  return data;
};

/**
 * Search for a location
 */
const searchLocation = async function () {
  const search = document.getElementById('locsearch').value;

  // Fetch search results
  const res = await fetchAPI('https://nominatim.openstreetmap.org/search.php', { q: search, format: 'jsonv2' });

  console.log(res);

  if (Array.isArray(res) && res.length > 0) {
    // Create the selection list
    let select = `<select class="form-select" size="${Math.min(res.length, 15)}" onchange="locationSelectChange(event)">`;
    res.forEach((result, i) => {
      let selected = '';
      if (i === 0) {
        selected = 'selected ';
      }

      select = `${select}<option ${selected}value="${result.lat},${result.lon}">${result.name} (${result.display_name.slice(0, 100)}...)</option>`;
    });
    select += '</select>';

    // Add first element to input
    document.getElementById('jform_lat').value = res[0].lat;
    document.getElementById('jform_lng').value = res[0].lon;

    // Activate button
    document.getElementById('saveLocBtn').disabled = false;

    // Place selection into DOM
    document.getElementById('locsearch_results').innerHTML = Joomla.sanitizeHtml(select);
  } else {
    document.getElementById('locsearch_results').innerHTML = `<p>${Joomla.Text._('MOD_COMMUNITY_MSG_NO_LOCATIONS_FOUND')}</p>`;

    // Deactivate button
    document.getElementById('saveLocBtn').disabled = true;
  }
};

/**
 * Set the automatic detected location via ajax
 *
 * @param   {String}   location   Coordinates of the current location (e.g 51.5000,0.0000)
 * @param   {Interger} moduleId  ID of the current module
 * @param   {String}   method     Name of the target method in the module helper class
 *
 * @returns {Object} Result object
 *          {success: true, status: 200, message: '', messages: {}, data: {}}
 */
const ajaxLocation = async function (location, moduleId, method) {
  // Create form data
  const formData = new FormData();
  formData.append('module_id', moduleId);
  formData.append('current_location', location);

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
    const message = Joomla.Text._('MOD_COMMUNITY_ERROR_SAVE_LOCATION');
    const message2 = Joomla.Text._('MOD_COMMUNITY_ERROR_BROWSER_CONSOLE');
    Joomla.renderMessages({ error: [`${message} ${sprintf(message2, 'Network error')}`] });

    console.log('mod_community_info: ajaxLocation request failed.');
    console.log(`Status Code: ${response.status}. Message: ${response.statusText}`);

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
    const message = Joomla.Text._('MOD_COMMUNITY_ERROR_SAVE_LOCATION');
    const message2 = Joomla.Text._('MOD_COMMUNITY_ERROR_BROWSER_CONSOLE');
    Joomla.renderMessages({ error: [`${message} ${sprintf(message2, 'PHP error')}`] });

    console.log('mod_community_info: ajaxLocation request failed.');
    console.log(txt);
  } else {
    // Response is not of type json --> probably some php warnings/notices
    const split = txt.split('\n{"');
    const temp = JSON.parse(`{"${split[1]}`);
    data = JSON.parse(temp.data);

    const message = Joomla.Text._('MOD_COMMUNITY_ERROR_SAVE_LOCATION');
    const message2 = Joomla.Text._('MOD_COMMUNITY_ERROR_BROWSER_CONSOLE');
    Joomla.renderMessages({ error: [`${message} ${sprintf(message2, 'PHP warnings')}`] });
    console.log('mod_community_info: ajaxLocation request failed.');
    console.log(`Message: ${split[0]}`);
    console.log(`Messages: ${temp.messages}`);
    console.log(`Data: ${data}`);
  }

  return data;
};

/**
 * Activate automatic location service
 */
const autoLoc = async function () {
  const location = await getCurrentLocation();
  const [latitude, longitude] = location.split(',', 2);

  document.getElementById('jform_lat').value = latitude;
  document.getElementById('jform_lng').value = longitude;
  document.getElementById('jform_autoloc').value = '1';

  document.getElementById('module_task').value = 'autoLocation';
  document.getElementById('location-form').submit();
};

/**
 * Save manually chosen location
 */
const saveLoc = function () {
  document.getElementById('jform_autoloc').value = '0';
  document.getElementById('module_task').value = 'saveLocation';
  document.getElementById('location-form').submit();
};

/**
 * Change manual location selection
 */
const locationSelectChange = function (event) {
  const selectedValue = event.target.value;
  const [latitude, longitude] = selectedValue.split(',', 2);

  // Add first element to input
  document.getElementById('jform_lat').value = latitude;
  document.getElementById('jform_lng').value = longitude;

  // Activate button
  document.getElementById('saveLocBtn').disabled = false;
};

/**
 * Opens a bootstrap modal based on the provided ID
 *
 * @param {String}  modalId    ID of the modal
 * @param {String}  location   The current location (e.g 51.5000,0.0000)
 */
const openModal = function (modalId, location) {
  const modal = document.getElementById(modalId);
  const modalBody = modal.querySelector('.modal-body');
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

  const searchButton = modalBody.querySelector('#btn-locsearch');
  if (searchButton) {
    searchButton.type = 'button';
    searchButton.classList.add('btn', 'btn-outline-secondary');
    searchButton.addEventListener('click', searchLocation);
  }

  const bsmodal = new bootstrap.Modal(document.getElementById(modalId), { keyboard: false });
  bsmodal.show();

  if (modalId === 'location-modal') {
    // Define default submit button of location form
    document.getElementById('location-form').addEventListener('keydown', (event) => {
      if (event.key === 'Enter') {
        event.preventDefault();

        // Trigger the desired button
        document.getElementById('btn-locsearch').click();
      }
    });
  }
};

/**
 * Initializes the module
 *
 * @param {Integer}  moduleId       ID of the module
 * @param {Integer}  autoLocation   Parameter setting if automatic location is enabled
 */
const iniModule = async function (moduleId, autoLocation) {
  // Prepare location picker
  const moduleBody = document.getElementById(`CommunityInfo${moduleId}`);
  const moduleHeader = moduleBody.parentNode.previousElementSibling;
  const templateContent = document.getElementById('template-location-picker').innerHTML;
  const sanitizedContent = Joomla.sanitizeHtml(templateContent);
  const tempDiv = document.createElement('div');
  tempDiv.innerHTML = sanitizedContent;

  // Append location picker
  while (tempDiv.firstChild) {
    moduleHeader.appendChild(tempDiv.firstChild);
  }

  // Install event listener
  const links = moduleHeader.querySelectorAll('a[data-modal-id]');
  links.forEach((link) => {
    link.addEventListener('click', (event) => {
      event.preventDefault();
      const modalId = link.getAttribute('data-modal-id');
      const geolocation = link.getAttribute('data-geolocation');
      openModal(modalId, geolocation);
    });
  });

  // Prepare modal
  document.getElementById('location-modal').classList.add('mod-community-info');

  // Send browsers current geolocation to com_ajax
  if (autoLocation === 1) {
    try {
      const location = await getCurrentLocation();
      console.log('Current Location:', location);

      const response = await ajaxLocation(location, moduleId, 'setLocation');
      console.log('Ajax Response:', Joomla.Text._(response));
    } catch (error) {
      console.error('Error:', error);
    }
  }
};
