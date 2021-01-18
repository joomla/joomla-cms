/**
 * @package     Joomla.Installation
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Method to check if the configuration.php has been added.
 */
Joomla.checkConfigFile = function() {
  document.body.appendChild(document.createElement('joomla-core-loader'));

  Joomla.request({
    method: "POST",
    url : Joomla.installationBaseUrl + '?task=installation.checkConfigFile&format=json',
    perform: true,
    onSuccess: function(response, xhr){
      var loaderElement = document.querySelector('joomla-core-loader');
      try {
        response = JSON.parse(response);
      } catch (e) {
        loaderElement.parentNode.removeChild(loaderElement);

        return false;
      }

      if (response.messages) {
        Joomla.renderMessages(response.messages);
      }

      if (response.error) {
        Joomla.renderMessages({'error': [response.message]});
        loaderElement.parentNode.removeChild(loaderElement);
      } else if (response.data && response.data.valid) {
        Joomla.goToPage('remove');
      }
    },
    onError:   function(xhr){
      var loaderElement = document.querySelector('joomla-core-loader');
      loaderElement.parentNode.removeChild(loaderElement);

      try {
        var r = JSON.parse(xhr.responseText);
        Joomla.replaceTokens(r.token);
        alert(r.message);
      } catch (e) {
      }
    }
  });
};

(function() {
  if (document.getElementById('checkConfigurationButton')) {
    document.getElementById('checkConfigurationButton').addEventListener('click', function (e) {
      e.preventDefault();
      e.stopPropagation();
      Joomla.checkConfigFile();
    })
  }
})();
