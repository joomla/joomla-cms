/**
 * @copyright  (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Aspect Ratio Calculator Module
 */
(() => {
  const initCalculator = () => {
    const widthInput = document.getElementById('calc-width');
    const heightInput = document.getElementById('calc-height');
    const output = document.getElementById('calc-output');
    const copyBtn = document.getElementById('copy-calc-value');

    if (!widthInput || !heightInput || !output || !copyBtn) {
      return;
    }

    /**
     * Calculate aspect ratio from width and height
     */
    const calculateRatio = () => {
      const width = parseFloat(widthInput.value);
      const height = parseFloat(heightInput.value);

      if (width > 0 && height > 0) {
        const ratio = width / height;
        output.textContent = ratio.toString();
      } else {
        output.textContent = '0';
      }
    };

    /**
     * Show copy feedback on button
     */
    const showCopyFeedback = () => {
      const originalHTML = copyBtn.innerHTML;
      const copiedIcon = copyBtn.getAttribute('data-copied-icon');
      const copiedText = copyBtn.getAttribute('data-copied-text');

      copyBtn.innerHTML = copiedIcon + copiedText;
      copyBtn.disabled = true;

      setTimeout(() => {
        copyBtn.innerHTML = originalHTML;
        copyBtn.disabled = false;
      }, 2000);
    };

    /**
     * Copy the calculated value to clipboard
     */
    const copyToClipboard = () => {
      const value = output.textContent;

      navigator.clipboard.writeText(value)
        .then(() => {
          showCopyFeedback();
        })
        .catch((err) => {
          console.error('Clipboard copy failed:', err);
        });
    };

    // Attach event listeners
    widthInput.addEventListener('input', calculateRatio);
    heightInput.addEventListener('input', calculateRatio);
    copyBtn.addEventListener('click', copyToClipboard);
    output.addEventListener('click', copyToClipboard);

    // Calculate initial ratio
    calculateRatio();
  };

  // Initialize when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCalculator);
  } else {
    // DOM already loaded, run with slight delay for form rendering
    setTimeout(initCalculator, 100);
  }
})();
