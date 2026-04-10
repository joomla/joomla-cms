/**
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

((window, document) => {
  window.iFrameHeight = iframe => {
    const doc = 'contentDocument' in iframe ? iframe.contentDocument : iframe.contentWindow.document;
    const height = parseInt(doc.body.scrollHeight, 10);
    if (!document.all) {
      iframe.style.height = `${parseInt(height, 10) + 60}px`;
    } else if (document.all && iframe.id) {
      document.all[iframe.id].style.height = `${parseInt(height, 10) + 20}px`;
    }
  };
})(window, document);
