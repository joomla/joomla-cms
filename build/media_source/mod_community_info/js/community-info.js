/**
 * Opens a bootstrap modal based on the provided ID
 * 
 * @param {String}  modalId    ID of the modal
 * @param {String}  location   The current location (e.g 51.5000,0.0000) 
 */
let openModal = function(modalId, location) {
  let modal      = document.getElementById(modalId);
  let modalBody  = modal.querySelector('.modal-body');
  modalBody.innerHTML  = document.getElementById('template-'+modalId+'-body').innerHTML;

  bsmodal = new bootstrap.Modal(document.getElementById(modalId), {keyboard: false});
  bsmodal.show();
  
  if(modalId == 'location-modal') {
    // Define default submit button of location form
    document.getElementById('location-form').addEventListener('keydown', function(event) {
      if (event.key === 'Enter') {
        event.preventDefault();

        // Trigger the desired button
        document.getElementById('btn-locsearch').click();
      }
    });
  }
}

/**
 * Activate automatic location service
 */
let autoLoc = async function() {
  let location = await getCurrentLocation();

  document.getElementById('jform_lat').value = location.split(',', 2)[0];
  document.getElementById('jform_lng').value = location.split(',', 2)[1];
  document.getElementById('jform_autoloc').value = '1';

  document.getElementById('module_task').value = 'autoLocation';
  document.getElementById('location-form').submit();
}

/**
 * Save manually chosen location
 */
let saveLoc = function() {
  document.getElementById('jform_autoloc').value = '0';
  document.getElementById('module_task').value = 'saveLocation';
  document.getElementById('location-form').submit();
}

/**
 * Search for a location
 */
let searchLocation = async function() {
  let search = document.getElementById('locsearch').value;

  // Fetch search results
  let res = await fetchAPI('https://nominatim.openstreetmap.org/search.php',{'q': search, 'format': 'jsonv2'});

  console.log(res);

  if(Array.isArray(res) && res.length > 0) {
    // Create the selection list
    let select = '<select class="form-select" size="'+Math.min(res.length, 15)+'" onchange="locationSelectChange(event)">';
    res.forEach((result, i) => {
      selected = '';
      if (i === 0) {
        selected = 'selected ';
      }

      select = select + '<option '+selected+'value="'+result.lat+','+result.lon+'">'+result.name+' ('+result.display_name.slice(0, 100) + '...)</option>';
    });
    select = select + '</select>';

    // Add first element to input
    document.getElementById('jform_lat').value = res[0].lat;
    document.getElementById('jform_lng').value = res[0].lon;

    // Activate button
    document.getElementById('saveLocBtn').disabled = false;

    // Place selection into DOM
    document.getElementById('locsearch_results').innerHTML = select;
  } else {
    document.getElementById('locsearch_results').innerHTML = '<p>'+Joomla.Text._('MOD_COMMUNITY_MSG_NO_LOCATIONS_FOUND')+'</p>';

    // Deactivate button
    document.getElementById('saveLocBtn').disabled = true;
  }
}

/**
 * Change manual location selection
 */
let locationSelectChange = function(event) {
  let selectedValue = event.target.value;

  // Add first element to input
  document.getElementById('jform_lat').value = selectedValue.split(',', 2)[0];
  document.getElementById('jform_lng').value = selectedValue.split(',', 2)[1];

  // Activate button
  document.getElementById('saveLocBtn').disabled = false;
}

/**
 * Set the automatic detected location via ajax
 * 
 * @param   {String}   location   Coordinates of the current location (e.g 51.5000,0.0000)
 * @param   {Interger} module_id  ID of the current module
 * @param   {String}   method     Name of the target method in the module helper class
 * 
 * @returns {Object} Result object
 *          {success: true, status: 200, message: '', messages: {}, data: {}}
 */
let ajaxLocation = async function(location, module_id, method) {
  // Create form data
  let formData = new FormData();
  formData.append('module_id', module_id);
  formData.append('current_location', location);

  // Set request parameters
  let parameters = {
    method: 'POST',
    mode: 'same-origin',
    cache: 'default',
    redirect: 'follow',
    referrerPolicy: 'no-referrer-when-downgrade',
    body: formData,
  };

  // Set the URL
  let url = `index.php?option=com_ajax&module=community_info&method=${method}&format=json`;

  // Perform the fetch request
  let response = await fetch(url, parameters);
  let txt      = await response.text();

  if (!response.ok) {
    // Catch network error
    let message = Joomla.Text._('MOD_COMMUNITY_ERROR_SAVE_LOCATION');
    let message2 = Joomla.Text._('MOD_COMMUNITY_ERROR_BROWSER_CONSOLE');
    Joomla.renderMessages({'error':[message+' '+sprintf(message2, 'Network error')]});

    console.log('mod_community_info: ajaxLocation request failed.');
    console.log('Status Code: '+response.status+'. Message: '+response.statusText);
    return;
  }

  let data = null;

  if(txt.startsWith('{"success"')) {
    // Response is of type json --> everything fine
    let res = JSON.parse(txt);
    try {
      data = JSON.parse(res.data);
    } catch (e) {
      // no need to parse a json string.
      data = res.data;
    }
  } else if (txt.includes('Fatal error')) {
    // PHP fatal error occurred
    let message = Joomla.Text._('MOD_COMMUNITY_ERROR_SAVE_LOCATION');
    let message2 = Joomla.Text._('MOD_COMMUNITY_ERROR_BROWSER_CONSOLE');
    Joomla.renderMessages({'error':[message+' '+sprintf(message2, 'PHP error')]});

    console.log('mod_community_info: ajaxLocation request failed.');
    console.log(txt);
  } else {
    // Response is not of type json --> probably some php warnings/notices
    let split = txt.split('\n{"');
    let temp  = JSON.parse('{"'+split[1]);
    let data  = JSON.parse(temp.data);

    let message = Joomla.Text._('MOD_COMMUNITY_ERROR_SAVE_LOCATION');
    let message2 = Joomla.Text._('MOD_COMMUNITY_ERROR_BROWSER_CONSOLE');
    Joomla.renderMessages({'error':[message+' '+sprintf(message2, 'PHP warnings')]});
    console.log('mod_community_info: ajaxLocation request failed.');
    console.log('Message: '+split[0]);
    console.log('Messages: '+temp.messages);
    console.log('Data: '+data);
  }

  return data;
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
let fetchAPI = async function(url, variables={}, format='json') {
  let urlSearchParams = new URLSearchParams(variables);

  if(Object.keys(variables).length != 0) {
    url = url+'?'+urlSearchParams.toString();
  }

  // Set request parameters
  let parameters = {
    method: 'GET',
    mode: 'cors',
    cache: 'default',
    redirect: 'follow',
    referrerPolicy: 'origin'
  };

  // Perform the fetch request
  let response = await fetch(url, parameters);

  if (!response.ok) {
    // Catch network error
    let message = Joomla.Text._('MOD_COMMUNITY_ERROR_FETCH_API');
    Joomla.renderMessages({'error':[sprintf(message, url, response.status, response.statusText)]});
    console.log('mod_community_info: fetchAPI request failed. Status Code: '+response.status+'. Message: '+response.statusText);
    return;
  }

  // Request successful
  let txt       = await response.text();
  let error     = false;
  let errorcode = '';
  let data;

  if(format == 'json') {
    try {
      data = JSON.parse(txt);
    } catch (error) {
      error     = true;
      errorcode = error.message;
    }
  } else if(format == 'xml') {
    let parser = new DOMParser();
    data = parser.parseFromString(txt, "text/xml");

    if(data.getElementsByTagName("parsererror").length > 0) {
      error     = true;
      errorcode = data.getElementsByTagName("parsererror")[0].textContent;
    }
  } else {
    data = txt;
  }

  if(error) {
    // Parsing error
    let message = Joomla.Text._('MOD_COMMUNITY_ERROR_FETCH_API');
    Joomla.renderMessages({'error':[sprintf(message, url, '-', errorcode)]});
    console.log('mod_community_info: fetchAPI request failed. Status Code: -. Message: '+errorcode);
  } else {
    return data;
  }
}

/**
 * Get current position of device
 * 
 * @returns {Promise<String>}   A promise that resolves to the location string (e.g., "51.5000,0.0000")
 */
let getCurrentLocation = async function() {
  return new Promise((resolve, reject) => {
    if ('geolocation' in navigator) {
      navigator.geolocation.getCurrentPosition(
        (position) => {
          const { latitude, longitude } = position.coords;
          resolve(`${latitude},${longitude}`);
        },
        (error) => {
          reject(Joomla.Text._('MOD_COMMUNITY_ERROR_GET_LOCATION')+' '+error.message);
        }
      );
    } else {
      reject(Joomla.Text._('MOD_COMMUNITY_MSG_GEOLOCATION_NOT_SUPPORTED'));
    }
  });
};

/**
 * Sprintf functionality for JText
 * 
 * @param   {String}   text   The text string
 * 
 * @returns {String}   The processed text
 */
let sprintf = function(text) {
  var args = Array.prototype.slice.call(arguments, 1);
  var i = 0;
  return text.replace(/%s/g, function() {
    return args[i++];
  });
}
