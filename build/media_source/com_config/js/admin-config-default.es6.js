Joomla = window.Joomla || {};

((Joomla) => {
  'use strict';

  /**
   * Method reset DB Encryption fields when localhost is chosen
   *
   * @param {HTMLElement}  element  The element that initiates the call
   * @returns {void}
   * @since   4.0
   */
  Joomla.resetDbEncryptionFields = (element) => {
    if (element.value === 'localhost') {
      document.getElementById('jform_dbsslverifyservercert0').checked = true;
      document.getElementById('jform_dbsslverifyservercert1').checked = false;
      document.getElementById('jform_dbsslkey').value = '';
      document.getElementById('jform_dbsslcert').value = '';
      document.getElementById('jform_dbsslca').value = '';
      document.getElementById('jform_dbsslcapath').value = '';
      document.getElementById('jform_dbsslcipher').value = '';
      document.getElementById('jform_dbencryption').value = 0;
    }
  };
})(Joomla);
