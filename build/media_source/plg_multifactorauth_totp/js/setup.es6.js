/**
 * @package     Joomla.Plugin
 * @subpackage  Multifactorauth.webauthn
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

((Joomla, document, qrcode) => {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    const elTarget = document.getElementById('users-mfa-totp-qrcode');
    const qrData = Joomla.getOptions('plg_multifactorauth_totp.totp.qr');

    if (!elTarget || !qrData) {
      return;
    }

    const qr = qrcode(0, 'H');
    qr.addData(qrData);
    qr.make();
    elTarget.innerHTML = qr.createImgTag(4);
  });
// eslint-disable-next-line no-undef
})(Joomla, document, qrcode);
