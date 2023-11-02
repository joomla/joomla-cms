/**
 * @package     Joomla.Installation
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
(function() {
  // Make sure that we have the Joomla object
  Joomla = window.Joomla || {};
  Joomla.installation = Joomla.installation || {};

  Joomla.serialiseForm = function( form ) {
    var i, l, obj = [], elements = form.querySelectorAll( "input, select, textarea" );
    for(i = 0, l = elements.length; i < l; i++) {
      var name = elements[i].name;
      var value = elements[i].value;
      if(name) {
        if (((elements[i].type === 'checkbox' || elements[i].type === 'radio') && elements[i].checked === true) || (elements[i].type !== 'checkbox' && elements[i].type !== 'radio')) {
          obj.push(name.replace('[', '%5B').replace(']', '%5D') + '=' + encodeURIComponent(value));
        }
      }
    }
    return obj.join("&");
  };


  /**
   * Method to request a different page via AJAX
   *
   * @param  page        The name of the view to request
   * @param  fromSubmit  Unknown use
   *
   * @return {Boolean}
   */
  Joomla.goToPage = function(page, fromSubmit) {
    if (!fromSubmit) {
      Joomla.removeMessages();
      document.body.appendChild(document.createElement('joomla-core-loader'));
    }

    if (page) {
      window.location = Joomla.baseUrl + '?view=' + page + '&layout=default';
    }

    return false;
  };

  /**
   * Method to submit a form from the installer via AJAX
   *
   * @return {Boolean}
   */
  Joomla.submitform = function(form) {
    var data = Joomla.serialiseForm(form);

    document.body.appendChild(document.createElement('joomla-core-loader'));
    Joomla.removeMessages();

    Joomla.request({
      type     : "POST",
      url      : Joomla.baseUrl,
      data     : data,
      dataType : 'json',
      onSuccess: function (response, xhr) {
        response = JSON.parse(response);
        var spinnerElement = document.querySelector('joomla-core-loader');

        if (response.messages) {
          Joomla.renderMessages(response.messages);
        }

        if (response.error) {
          Joomla.renderMessages({'error': [response.message]});
          spinnerElement.parentNode.removeChild(spinnerElement);
        } else {
          spinnerElement.parentNode.removeChild(spinnerElement);
          if (response.data && response.data.view) {
            Install.goToPage(response.data.view, true);
          }
        }
      },
      onError  : function (xhr) {
        var spinnerElement = document.querySelector('joomla-core-loader');
        spinnerElement.parentNode.removeChild(spinnerElement);
        busy = false;
        try {
          var r = JSON.parse(xhr.responseText);
          Joomla.replaceTokens(r.token);
          alert(r.message);
        } catch (e) {
        }
      }
    });

    return false;
  };

  Joomla.scrollTo = function (elem, pos)
  {
    var y = elem.scrollTop;
    y += (pos - y) * 0.3;
    if (Math.abs(y-pos) < 2)
    {
      elem.scrollTop = pos;
      return;
    }
    elem.scrollTop = y;
    setTimeout(Joomla.scrollTo, 40, elem, pos);
  };

  Joomla.checkFormField = function(fields) {
    var state = [];
    fields.forEach(function(field) {
      state.push(document.formvalidator.validate(document.querySelector(field)));
    });

    if (state.indexOf(false) > -1) {
      return false;
    }
    return true;
  };

  // Init on dom content loaded event
  Joomla.makeRandomDbPrefix = function() {
    var numbers = '0123456789', letters = 'abcdefghijklmnopqrstuvwxyz', symbols = numbers + letters;
    var prefix = letters[Math.floor(Math.random() * 24)];

    for (var i = 0; i < 4; i++ ) {
      prefix += symbols[Math.floor(Math.random() * 34)];
    }

    document.getElementById('jform_db_prefix').value = prefix + '_';

    return prefix + '_';
  };

  /**
   * Initializes JavaScript events on each request, required for AJAX
   */
  Joomla.pageInit = function() {
    // Attach the validator
    [].slice.call(document.querySelectorAll('form.form-validate')).forEach(function(form) {
      document.formvalidator.attachToForm(form);
    });

    Joomla.installation = Joomla.installation || {};

    return 'Loaded...'
  };


  /**
   * Executes the required tasks to complete site installation
   *
   * @param tasks       An array of install tasks to execute
   */
  Joomla.install = function(tasks, form) {
    const progress = document.getElementById('progressbar');
    const progress_text = document.getElementById('progress-text');
    if (!form) {
      throw new Error('No form provided')
    }
    if (!tasks.length) {
      if (progress_text) {
        progress_text.innerText = Joomla.Text._('INSTL_FINISHED');
      }
      setTimeout(Joomla.goToPage, 2000, 'remove');
      return;
    }

    var task = tasks.shift();
    var data = Joomla.serialiseForm(form);

    Joomla.request({
      method: "POST",
      url : Joomla.baseUrl + '?task=installation.' + task + '&format=json',
      data: data,
      perform: true,
      onSuccess: function(response, xhr){
        try {
          response = JSON.parse(response);
        } catch (e) {
          if (progress_text) {
            progress_text.setAttribute('role', 'alert');
            progress_text.classList.add('error');
            progress_text.innerText = response;
          }
          console.error('Error in ' + task + ' Endpoint');
          console.error(response);
          Joomla.renderMessages({'error': [Joomla.Text._('INSTL_DATABASE_RESPONSE_ERROR')]});

          return false;
        }

        Joomla.replaceTokens(response.token);

        if (response.error === true)
        {
          progress_text.setAttribute('role', 'alert');
          progress_text.classList.add('error');
          progress_text.innerText = response.message;

          if (response.messages) {
            Joomla.renderMessages(response.messages);
          }

          if (response.message) {
            Joomla.renderMessages({"error": [response.message]});
          }

          // @todo: Add a delay and red background before removing the progress bar?
          // Reveal the install steps so the user has a chance to resubmit with the data
          document.getElementById('installStep1').classList.add('active');
          document.getElementById('installStep2').classList.add('active');
          document.getElementById('installStep3').classList.add('active');
          document.getElementById('installStep4').classList.remove('active');

          return false;
        }

        if (response.messages) {
          Joomla.renderMessages(response.messages);
          return false;
        }

        if (progress) {
          progress.setAttribute('value', parseInt(progress.getAttribute('value')) + 1);
          progress_text.innerText = Joomla.Text._('INSTL_IN_PROGRESS');
        }
        Joomla.install(tasks, form);
      },
      onError: function(xhr){
        if (progress_text) {
          progress_text.setAttribute('role', 'alert');
          progress_text.classList.add('error');
          progress_text.innerText = xhr.responseText;
        }
        Joomla.renderMessages([['', Joomla.Text._('JLIB_DATABASE_ERROR_DATABASE_CONNECT', 'A Database error occurred.')]]);
        Joomla.goToPage('remove');

        try {
          var r = JSON.parse(xhr.responseText);
          Joomla.replaceTokens(r.token);
          alert(r.message);
        } catch (e) {
        }
      }
    });
  };

  /* Load scripts async */
  document.addEventListener('DOMContentLoaded', function() {
    var page = document.getElementById('installer-view');

    // Set the base URL
    Joomla.baseUrl = Joomla.getOptions('system.installation').url ? Joomla.getOptions('system.installation').url.replace(/&amp;/g, '&') : 'index.php';

    // Show the container
    var container = document.getElementById('container-installation');
    if (container) {
      Joomla.installationBaseUrl = container.getAttribute('data-base-url');
      Joomla.installationBaseUrl += "installation/index.php"
    } else {
      throw new Error('"container-installation" container is missed')
    }

    if (page && page.getAttribute('data-page-name')) {
      var script = document.querySelector('script[src*="template.js"]');
      el = document.createElement('script');
      el.src = script.src.replace("template.js", page.getAttribute('data-page-name') + '.js');
      document.head.appendChild(el);
    }

    if (container) {
      container.classList.remove('no-js');
      container.classList.remove('hidden');
    }
  });
})();
