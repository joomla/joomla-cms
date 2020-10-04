/**
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
import { GoogleFontLocaliser } from './_shared/_google-font-localizer.esm';

if (!window.customElements.get('google-font-localizer')) {
  window.customElements.define('google-font-localizer', GoogleFontLocaliser);
}
