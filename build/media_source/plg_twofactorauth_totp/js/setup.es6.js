/**
 * @package   AkeebaLoginGuard
 * @copyright Copyright (c)2016-2022 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

((Joomla, document, qrcode) => {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    const elTarget = document.getElementById('users-tfa-totp-qrcode');
    const qrData = Joomla.getOptions('plg_twofactorauth_totp.totp.qr');

    if (!elTarget || !qrData) {
      return;
    }

    const qr = qrcode(0, 'H');
    qr.addData(qrData);
    qr.make();
    elTarget.innerHTML = qr.createImgTag(4);
  });
})(Joomla, document, qrcode);
