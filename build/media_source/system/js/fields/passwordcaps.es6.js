/**
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Detect Caps Lock status
function checkCapsLock(event) {
  const capsLockOn = event.getModifierState && event.getModifierState('CapsLock');
  const { target } = event;
  let capsErrorMessage = document.getElementById('capsLockError');

  if (capsLockOn && target.type === 'password') {
    // Caps Lock is on
    if (!capsErrorMessage) {
      // Create error message element if it doesn't exist
      capsErrorMessage = document.createElement('span');
      capsErrorMessage.id = 'capsLockError';
      capsErrorMessage.textContent = Joomla.Text._('JCAPSLOCKON');
      capsErrorMessage.setAttribute('class', 'invalid form-control-hint');
      target.parentNode.before(capsErrorMessage);
    }
  } else if (capsErrorMessage) {
    // Caps Lock is off or not targeting a password input field

    // Remove error message if it exists
    capsErrorMessage.parentNode.removeChild(capsErrorMessage);
  }
}

// Attach event listener to password input field
const passwordInput = document.querySelector('input[type="password"]');
passwordInput.addEventListener('keydown', checkCapsLock);
passwordInput.addEventListener('mousedown', checkCapsLock);
