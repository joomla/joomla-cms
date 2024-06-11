# Images Used in System Tests Documentation

Software architecture images are:
* Stored in their original format as OpenOffice Draw (ODG) files and used as SVGs.
* Designed to work in both light and dark color modes.
* Using Joomla logo colours and the Ubuntu font.
* Avoid using transparency, as it may not be displayed correctly in Firefox and Safari.

To create an SVG from an ODG file format:
1. Export in OpenOffice Draw as a PDF with the following options:
   * **General** – Embed Standard Fonts
   * **Graphics** – Lossless Compression
2. Convert the PDF to SVG using the command line tool `pdf2svg`.
3. Modify the SVG file to use `<svg ... width="100%" height="auto" ...>`
