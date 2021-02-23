/**
 * Joomla Color Picker Web Component
 * Implements TinyColor https://github.com/scttcper/tinycolor
 * 
 * Copyright Dimitris Grammatikogiannis & Dan Partac
 * License MIT
**/

(function() {
	class ColorPicker extends HTMLElement {
		constructor() {
			super();
			// self = this;
			// all instances must have a unique ID
			const elementID = this.getAttribute('id') || `color-picker-${Math.floor(Math.random() * 999)}`;
			this.value = this.getAttribute('value') || 'rgba(0,0,0,1)';
			// move attributes to input
			// Joomla will likely want to read a form input
			this.removeAttribute('id');
			this.removeAttribute('value');
			// set internals
			this.format = this.getAttribute('format');
			this.color = window.tinycolor( this.value, {format: this.format} );
			this.dragElement = null;
			this.isOpen = false;
			this.isMobile = 'ontouchstart' in document && /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);
			this.keyTimer = null;
			// control positions
			this.controlPositions = {
				c1x: 0, // hex/rgb 
				c1y: 0, // hex/rgb
				c2y: 0, // hex/rgb/hsl
				c3y: 0 // rgb/hsl
			};

			// set templates
			this.inputsTemplate = '';

			if ( this.format === 'rgb' ) {
				const rgb = this.color.toRgb();

				this.inputsTemplate =
`<label for="#${elementID}-red">R</label>
<input id="${elementID}-red" value="${rgb.r}" class="color-input" type="number" placeholder="Red" min="0" max="255" autocomplete="off" spellcheck="false">
<label for="#${elementID}-green">G</label>
<input id="${elementID}-green" value="${rgb.g}" class="color-input" type="number" placeholder="Green" min="0" max="255" autocomplete="off" spellcheck="false">
<label for="#${elementID}-blue">B</label>
<input id="${elementID}-blue" value="${rgb.b}" class="color-input" type="number" placeholder="Blue" min="0" max="255" autocomplete="off" spellcheck="false">
<label for="#${elementID}-alpha">A</label>
<input id="${elementID}-alpha" value="${rgb.a}" class="color-input" type="number" placeholder="Alpha" min="0" max="1" step="0.01" autocomplete="off" spellcheck="false">`;
			}

			if ( this.format === 'hsl' ) {
				const hsl = this.color.toHsl();

				this.inputsTemplate = 
`<label for="#${elementID}-hue">H</label>
<input id="${elementID}-hue" value="${hsl.h}" class="color-input" type="number" placeholder="Hue" min="0" max="360" autocomplete="off" spellcheck="false">
<label for="#${elementID}-saturation">S</label>
<input id="${elementID}-saturation" value="${hsl.s}" class="color-input" type="number" placeholder="Saturation" min="0" max="100" autocomplete="off" spellcheck="false">
<label for="#${elementID}-lightness">L</label>
<input id="${elementID}-lightness" value="${hsl.l}" class="color-input" type="number" placeholder="Lightness" min="0" max="100" autocomplete="off" spellcheck="false">
<label for="#${elementID}-alpha">A</label>
<input id="${elementID}-alpha" value="${hsl.a}" class="color-input" type="number" placeholder="Alpha" min="0" max="1" step="0.01" autocomplete="off" spellcheck="false">`;
			}

			if ( this.format === 'hex' ) {
				this.inputsTemplate =
`<label for="#${elementID}-hex" class="hex-label">Hex</label>
<input id="${elementID}-hex" value="${this.color.toHexString()}" placeholder="Hex" class="color-input color-input-hex" type="text" autocomplete="off" spellcheck="false">`;
			}

			// make the controls smaller on mobile
			const cv1w = this.isMobile ? 150 : 230,
				cvh = this.isMobile ? 150 : 230,
				dropClass = this.isMobile ? ' mobile' : '';
			
			this.controlsTemplate = 
`<div class="color-control">
	<canvas class="color-control1" height="${cvh}" width="${cv1w}"></canvas>
	<div class="color-pointer"></div>
</div>
<div class="color-control">
	<canvas class="color-control2" height="${cvh}" width="21" ></canvas>
	<div class="color-slider"></div>
</div>`;

			if (this.format !== 'hex') {
				this.controlsTemplate +=
`<div class="color-control">
	<canvas class="color-control3" height="${cvh}" width="21"></canvas>
	<div class="color-slider"></div>
</div>`;
			}

			this.template = document.createElement('template');

			this.template.innerHTML = 
`<style>
.picker-box {
	position: relative;
	user-select: none;
}
.color-dropdown {
	width: 280px;
	background: rgba(0,0,0,0.75);
	color: rgba(255,255,255,0.8);
	box-shadow: 0 6px 12px rgba(0, 0, 0, 0.4);
	position: absolute;
	padding: 0.5rem;
	border-radius: 0.5rem;
	display: none;
	left:0;
	flex-wrap: wrap;
	justify-content: space-between;
	z-index: 1
}

[format="hex"] + .color-dropdown {
	width: 255px
}

.color-dropdown.mobile {
	width: 210px
}

[format="hex"] + .color-dropdown.mobile {
	width: 180px
}

.color-dropdown.show {
	top: calc(100% + 5px);
	display: flex
}
.color-dropdown.show-top {
	bottom: calc(100% + 5px);
	display: flex;
	top: auto
}

.color-preview {
	border: 0;
	outline: none;
	box-shadow: 0 0 1px 1px rgba(120,120,120,0.33) inset;
	line-height: 1rem;
	height: 1rem;
	border-radius: 3px;
	padding: 0.5rem 0.75rem
}

.color-form {
	font: 12px Arial;
	display: flex;
	flex-wrap: wrap;
	flex-direction: inherit;
	width: 100%;
	align-items: center;
	padding: 0.25rem 0
}

.color-form * {
	flex: 1 0 0%;
	max-width: 17.5%;
	width: 17.5%
}

.color-form label {
	text-align: center;
	max-width: 7.5%;
	width: 7.5%
}
label.hex-label {
	max-width: 12.5%;
	width: 12.5%
}
input.color-input-hex {
	max-width: 87.5%;
	width: 87.5%
}

.color-input {
	background: transparent;
	border: 1px solid rgba(255,255,255,0.15);
	text-align: right;
	outline: none;
	color: inherit
}

.color-input:active,
.color-input:focus {
	background: rgba(0,0,0,0.25);
	border: 1px solid rgba(255,255,255,0.33);
}

.color-control1 {
	cursor: crosshair;
}

.color-control2,
.color-control3 {
	cursor: ns-resize;
}

.color-control {
	position:relative;
	display: inline-block
}

.color-control:focus canvas:active {
	cursor: none;
}

.color-pointer,
.color-slider {
	position:absolute;
	background: #000;
	border: 1px solid rgba(255,255,255,0.8);
	height: 5px;
	cursor: inherit;
	user-select: none;
	pointer-events: none
}
.color-pointer {
	width: 5px;
	border-radius: 5px
}
.color-slider {
	left: 0;
	width: calc(100% - 2px);
}
</style>
<label for="#${elementID}">Select a color: </label>

<div class="picker-box">
	<input id="${elementID}" value="${this.value}" format="${this.format}" type="text" class="color-preview" autocomplete="off" tabindex="0" spellcheck="false">

	<div class="color-dropdown${dropClass}">
		${this.controlsTemplate}
		<div class="color-form">
			${this.inputsTemplate}
		</div>
	</div>
</div>`;

			// Patch shadow DOM
			if (window.ShadyCSS) {
				window.ShadyCSS.prepareTemplate(this.template, 'color-picker');
			}

			this.attachShadow({ mode: 'open' });
			this.shadowRoot.appendChild(this.template.content.cloneNode(true));

			// Patch shadow DOM
			if (window.ShadyCSS) {
				window.ShadyCSS.styleElement(this)
			}

			// set main elements
			this.inputs = this.shadowRoot.querySelectorAll('.color-input');
			this.input = this.shadowRoot.querySelector('.color-preview');
			this.dropdown = this.shadowRoot.querySelector('.color-dropdown');
			this.control1 = this.shadowRoot.querySelector('.color-pointer');
			this.control2 = this.shadowRoot.querySelectorAll('.color-slider')[0];
			this.controls = Array.from(this.shadowRoot.querySelectorAll('canvas'));
			// set dimensions
			this.width1 = this.controls[0].width;
			this.height1 = this.controls[0].height;
			this.width2 = this.controls[1].width;
			this.height2 = this.controls[1].height;
			// set main controls
			this.ctx1 = this.controls[0].getContext('2d');
			this.ctx2 = this.controls[1].getContext('2d');
			this.ctx1.rect(0, 0, this.width1, this.height1);
			this.ctx2.rect(0, 0, this.width2, this.height2);

			// set alpha control except hex
			if ( this.format !== 'hex' ) {
				this.control3 = this.shadowRoot.querySelectorAll( '.color-slider' )[1];
				this.width3 = this.controls[2].width;
				this.height3 = this.controls[2].height;
				this.ctx3 = this.controls[2].getContext('2d');
				this.ctx3.rect(0, 0, this.width3, this.height3);
			}

			// attach main event
			this.toggleEvents(1);
		}

		toggleEvents(action) {
			action = action ? 'addEventListener' : 'removeEventListener';
			this.input[action]( 'focusin', this.show );
		}

		toggleEventsOnShown(action) {
			action = action ? 'addEventListener' : 'removeEventListener';
			const pointerEvents = 'ontouchstart' in document
				? { down: 'touchstart', move: 'touchmove', up: 'touchend' }
				: { down: 'mousedown', move: 'mousemove', up: 'mouseup' };

			this.controls.map( x => x[action]( pointerEvents.down, this.pointerDown ) );

			window[action]( 'scroll', this.handleScroll);

			Array.from(this.inputs).concat(this.input)
				.map(x => x[action]( 'change', this.changeHandler ) );
			
			document[action]( pointerEvents.move, this.pointerMove );
			document[action]( pointerEvents.up, this.pointerUp );
			window[action]( 'keyup', this.keyHandler );
		}

		connectedCallback() {		 
			this.observeInputs();
			this.setControlPositions();
			this.updateInputs(1); // don't trigger change in this context
			this.updateControls();
			this.render();
		}

		render() {
			const rgb = this.color.toRgb();

			if ( this.format !== 'hsl' ) {
				const hue = Math.floor( this.controlPositions.c2y / this.height2 * 360 );

				this.ctx1.fillStyle = window.tinycolor(`hsl(${hue},100%,50%)`).toRgbString();
				this.ctx1.fillRect(0, 0, this.width1, this.height1);

				const whiteGrad = this.ctx2.createLinearGradient(0, 0, this.width1, 0);
				whiteGrad.addColorStop(0, 'rgba(255,255,255,1)');
				whiteGrad.addColorStop(1, 'rgba(255,255,255,0)');
				this.ctx1.fillStyle = whiteGrad;
				this.ctx1.fillRect(0, 0, this.width1, this.height1);
	
				const blackGrad = this.ctx2.createLinearGradient(0, 0, 0, this.height1);
				blackGrad.addColorStop(0, 'rgba(0,0,0,0)');
				blackGrad.addColorStop(1, 'rgba(0,0,0,1)');
				this.ctx1.fillStyle = blackGrad;
				this.ctx1.fillRect(0, 0, this.width1, this.height1);
	
				const hueGrad = this.ctx2.createLinearGradient(0, 0, 0, this.height1);
				hueGrad.addColorStop(0, 'rgba(255, 0, 0, 1)');
				hueGrad.addColorStop(0.17, 'rgba(255, 255, 0, 1)');
				hueGrad.addColorStop(0.34, 'rgba(0, 255, 0, 1)');
				hueGrad.addColorStop(0.51, 'rgba(0, 255, 255, 1)');
				hueGrad.addColorStop(0.68, 'rgba(0, 0, 255, 1)');
				hueGrad.addColorStop(0.85, 'rgba(255, 0, 255, 1)');
				hueGrad.addColorStop(1, 'rgba(255, 0, 0, 1)');
				this.ctx2.fillStyle = hueGrad;
				this.ctx2.fill();

			} else {

				const hueGrad = this.ctx1.createLinearGradient(0, 0, this.width1, 0),
					saturation = Math.round( (1 - this.controlPositions.c2y / this.height2) * 100 );

				hueGrad.addColorStop(0, window.tinycolor('rgb(255, 0, 0)').desaturate(100 - saturation).toRgbString());
				hueGrad.addColorStop(0.17, window.tinycolor('rgb(255, 255, 0)').desaturate(100 - saturation).toRgbString());
				hueGrad.addColorStop(0.34, window.tinycolor('rgb(0, 255, 0)').desaturate(100 - saturation).toRgbString());
				hueGrad.addColorStop(0.51, window.tinycolor('rgb(0, 255, 255)').desaturate(100 - saturation).toRgbString());
				hueGrad.addColorStop(0.68, window.tinycolor('rgb(0, 0, 255)').desaturate(100 - saturation).toRgbString());
				hueGrad.addColorStop(0.85, window.tinycolor('rgb(255, 0, 255)').desaturate(100 - saturation).toRgbString());
				hueGrad.addColorStop(1, window.tinycolor('rgb(255, 0, 0)').desaturate(100 - saturation).toRgbString());
				this.ctx1.fillStyle = hueGrad;
				this.ctx1.fill();
	
				const whiteGrad = this.ctx1.createLinearGradient(0, 0, 0, this.height1);
				whiteGrad.addColorStop(0, 'rgba(255,255,255,1)');
				whiteGrad.addColorStop(0.5, 'rgba(255,255,255,0)');
				this.ctx1.fillStyle = whiteGrad;
				this.ctx1.fillRect(0, 0, this.width1, this.height1);
	
				const blackGrad = this.ctx1.createLinearGradient(0, 0, 0, this.height1);
				blackGrad.addColorStop(0.5, 'rgba(0,0,0,0)');
				blackGrad.addColorStop(1, 'rgba(0,0,0,1)');
				this.ctx1.fillStyle = blackGrad;
				this.ctx1.fillRect(0, 0, this.width1, this.height1);

				const saturationGrad = this.ctx2.createLinearGradient(0, 0, 0, this.height2),
					incolor = window.tinycolor( this.color.toRgbString() ).greyscale().toRgb();

				saturationGrad.addColorStop(0, 'rgb(' + rgb.r + ',' + rgb.g + ',' + rgb.b + ')' );
				saturationGrad.addColorStop(1, 'rgb(' + incolor.r + ',' + incolor.g + ',' + incolor.b + ')' );

				this.ctx2.fillStyle = saturationGrad;
				this.ctx2.fillRect(0, 0, this.width3, this.height3);
			}

			// alpha
			if (this.format !== 'hex' ) {
				this.ctx3.clearRect(0, 0, this.width3, this.height3);
				const alphaGrad = this.ctx3.createLinearGradient(0, 0, 0, this.height3);
				alphaGrad.addColorStop(0, 'rgba(' + rgb.r + ',' + rgb.g + ',' + rgb.b + ',1)');
				alphaGrad.addColorStop(1, 'rgba(' + rgb.r + ',' + rgb.g + ',' + rgb.b + ',0)');
				this.ctx3.fillStyle = alphaGrad;
				this.ctx3.fillRect(0, 0, this.width3, this.height3);
			}
		}

		handleScroll(e){
			const self = document.querySelector( 'color-picker.open' );
			
			if ( self ) {
				// prevent scroll when updating controls on mobile
				if (self.isMobile && self.dragElement) {
					e.preventDefault();
					e.stopPropagation();
				}
				// update color-dropdown position
				self.updateDropdown(e);
			}
		}

		pointerDown(e) {
			const eTarget = e.target, self = eTarget.getRootNode().host,

				controlRect = eTarget.getBoundingClientRect(),
				pageX = e.type === 'touchstart'
					? e.touches[0].pageX 
					: e.pageX,
				pageY = e.type === 'touchstart'
					? e.touches[0].pageY 
					: e.pageY,
				offsetX = pageX - window.pageXOffset - controlRect.left,
				offsetY = pageY - window.pageYOffset - controlRect.top;
			
			if ( eTarget === self.controls[0] || eTarget === self.control1 ) {
				self.dragElement = self.controls[0];
				self.changeControl1({offsetX,offsetY});
			} else if ( eTarget === self.controls[1] || eTarget === self.control2 ) {
				self.dragElement = self.controls[1];
				self.changeControl2({offsetY});
			} else if ( self.format !== 'hex' && (eTarget === self.controls[2] || eTarget === self.control3) ) {
				self.dragElement = self.controls[2];
				self.changeAlpha({offsetY});
			}
			e.preventDefault()
		}

		pointerUp(e) {
			const self = document.querySelector( 'color-picker.open' );

			if ( !self.dragElement && !document.getSelection().toString().length && !self.contains(e.target) ) {
				self.hide();
			}
			
			self.dragElement = null
		}

		pointerMove(e) {
			const self = document.querySelector( 'color-picker.open' ),
				controlInFocus = self.dragElement;

			if ( !controlInFocus ) return;

			const controlRect = controlInFocus.getBoundingClientRect(),
						pageX = e.type === 'touchmove'
							? e.touches[0].pageX 
							: e.pageX,
						pageY = e.type === 'touchmove'
							? e.touches[0].pageY 
							: e.pageY,
						offsetX = pageX - window.pageXOffset - controlRect.left,
						offsetY = pageY - window.pageYOffset - controlRect.top;

			if ( controlInFocus === self.controls[0] ) {
				self.changeControl1({offsetX,offsetY});
			}
			
			if ( controlInFocus === self.controls[1] ) {
				self.changeControl2({offsetY});
			}

			if ( controlInFocus === self.controls[2] && self.format !== 'hex' ) {
				self.changeAlpha({offsetY});
			}
		}

		changeHandler(e){
			const self = document.querySelector( 'color-picker.open' ),
						activeEl = self.shadowRoot.activeElement, 
						inputs = Array.from(self.inputs);

			if ( activeEl === self.input || ( self.isOpen && inputs.includes(activeEl) ) ) {
				const colorSource = activeEl === self.input ? self.input.value 
					: self.format === 'hex' ? inputs[0].value
					: self.format === 'hsl' ? `hsla(${inputs[0].value||this.hue},${inputs[1].value}%,${inputs[2].value}%,${inputs[3].value})`
					: `rgba(${inputs.map(x=>x.value).join(',')})`;

				self.color = window.tinycolor(colorSource, {format: this.format});
				self.setControlPositions();
				self.updateInputs();
				self.updateControls();
				self.render();
			}
		}

		keyHandler(e){
			const self = document.querySelector( 'color-picker.open' );

			if ( self.isOpen ) {
				if ( e.which === 27 ) {
					self.hide();
					return;
				}

				clearTimeout(self.keyTimer);
				self.keyTimer = setTimeout(() => {
					const focusedInput = Array.from( self.inputs )
						.concat( self.input )
						.find( x => x === self.shadowRoot.activeElement )

					focusedInput && focusedInput.dispatchEvent( new Event('change') )
				}, 500)
			}
		}

		changeControl1(e) {
			const offsetX = e.offsetX <= 0 ? 0 : e.offsetX > this.width1 ? this.width1 : e.offsetX,
						offsetY = e.offsetY <= 0 ? 0 : e.offsetY > this.height1 ? this.height1 : e.offsetY,

				hue = this.format !== 'hsl' 
					? Math.floor( this.controlPositions.c2y / this.height2 * 360 ) 
					: Math.floor( offsetX / this.width1 * 360 ),
				saturation = this.format !== 'hsl' 
					? Math.floor( offsetX / this.width1 * 100 )
					: Math.floor( ( 1 - this.controlPositions.c2y / this.height2 ) * 100 ),
				lightness = Math.floor( ( 1 - offsetY / this.height1 ) * 100 ),
				alpha = this.format !== 'hex' ? Math.floor( ( 1 -  this.controlPositions.c3y / this.height3 ) * 100 ) / 100 : 1,
				colorFormat = this.format !== 'hsl' ? 'hsva' : 'hsla';

			// new color
			this.color = window.tinycolor( `${colorFormat}(${hue},${saturation}%,${lightness}%,${alpha})`, {format: this.format} );
			// new positions
			this.controlPositions.c1x = offsetX;
			this.controlPositions.c1y = offsetY;
			// update color picker
			this.updateInputs();
			this.updateControls();
			this.render();
		}

		changeControl2(e) {
			const offsetY = e.offsetY <= 0 ? 0 : e.offsetY > this.height2 ? this.height2 : e.offsetY,

				hue = this.format !== 'hsl' 
					? Math.floor( offsetY / this.height2 * 360 ) 
					: Math.floor( this.controlPositions.c1x / this.width1 * 360 ),
				saturation = this.format !== 'hsl' 
					? Math.floor( this.controlPositions.c1x / this.width1 * 100 )
					: Math.floor( ( 1 - offsetY / this.height2  ) * 100 ),
				lightness = Math.floor( ( 1 - this.controlPositions.c1y / this.height1 ) * 100 ),
				alpha = this.format !== 'hex' ? Math.floor( ( 1 -  this.controlPositions.c3y / this.height3 ) * 100 ) / 100 : 1,
				colorFormat = this.format !== 'hsl' ? 'hsva' : 'hsla';

			// new color
			this.color = window.tinycolor( `${colorFormat}(${hue},${saturation}%,${lightness}%,${alpha})`, {format: this.format} );
			// new position
			this.controlPositions.c2y = offsetY;
			// update color picker
			this.updateInputs();
			this.updateControls();
			this.render();
		}

		changeAlpha(e) {
			const offsetY = e.offsetY <= 0 ? 0 : e.offsetY > this.height3 ? this.height3 : e.offsetY;

			// update color alpha
			this.color.setAlpha(Math.floor( ( 1 - offsetY / this.height3 ) * 100 ) / 100)
			// update position
			this.controlPositions.c3y = offsetY;
			// update color picker
			this.updateInputs();
			this.updateControls();
		}

		// Set up a requestAnimationFrame loop
		observeInputs() {
			// this.updateColor();
			// this.updateInputs()
		}

		updateDropdown(e){
			const self = !e ? this : document.querySelector( 'color-picker.open' ),
				elRect = self.input.parentElement.getBoundingClientRect(),
				elHeight = self.input.parentElement.offsetHeight,
				windowHeight = document.documentElement.clientHeight,
				dropHeight = self.dropdown.offsetHeight,
				distanceBottom =  windowHeight - elRect.bottom, 
				distanceTop = elRect.top,
        bottomExceed = elRect.top  + dropHeight + elHeight > windowHeight, // show
        topExceed = elRect.top - dropHeight < 0; // show-top

			if ( self.dropdown.classList.contains('show') && distanceBottom < distanceTop && bottomExceed ) {
				self.dropdown.classList.remove('show');
				self.dropdown.classList.add('show-top');
			} 
			if ( self.dropdown.classList.contains('show-top') && distanceBottom > distanceTop && topExceed ) {
				self.dropdown.classList.remove('show-top');
				self.dropdown.classList.add('show');
			} 
		}

		setControlPositions(){
			const hsv = this.color.toHsv(),
				hsl = this.color.toHsl(),
				hue = hsl.h,
				saturation = this.format !== 'hsl' ? hsv.s : hsl.s,
				lightness = this.format !== 'hsl' ? hsv.v : hsl.l,
				alpha = hsv.a;

			this.controlPositions.c1x = this.format !== 'hsl'
				? saturation * this.width1
				: hue / 360 * this.width1;

			this.controlPositions.c1y = (1 - lightness) * this.height1;

			this.controlPositions.c2y = this.format !== 'hsl'
				? hue / 360 * this.height2
				: (1 - saturation) * this.height2;

			if (this.format !== 'hex'){
				this.controlPositions.c3y = (1 - alpha) * this.height3
			}
		}

		updateControls(){
			this.control1.style.left = `${this.controlPositions.c1x - 3}px`;
			this.control1.style.top = `${this.controlPositions.c1y - 3}px`;
			this.control2.style.top = `${this.controlPositions.c2y - 3}px`;

			if ( this.format !== 'hex' ) {
				this.control3.style.top = `${this.controlPositions.c3y - 3}px`;
			}
		}

		dispatchCustomEvent(eventName) {
			const OriginalCustomEvent = new CustomEvent(eventName);
			OriginalCustomEvent.relatedTarget = this;
			this.dispatchEvent(OriginalCustomEvent);
			this.removeEventListener(eventName, this);
		}

		updateInputs(isInit) {
			const oldColor = this.input.value;
			let newColor = '', hsl, rgb;

			if ( this.format === 'hex' ) {
				newColor = this.color.toHexString();
				this.inputs[0].value = newColor;
			} else if ( this.format === 'hsl' ) {
				newColor = this.color.toHslString();
				hsl = this.color.toHsl();
				this.inputs[0].value = Math.floor(hsl.h);
				this.inputs[1].value = Math.round(hsl.s * 100);
				this.inputs[2].value = Math.round(hsl.l * 100);
				this.inputs[3].value = hsl.a;
			} else if ( this.format === 'rgb' ) {
				newColor = this.color.toRgbString();
				rgb = this.color.toRgb();
				this.inputs[0].value = rgb.r;
				this.inputs[1].value = rgb.g;
				this.inputs[2].value = rgb.b;
				this.inputs[3].value = rgb.a;
			}
			// update the main input
			this.input.value = newColor;
			this.input.style.background = newColor;
			this.input.style.color = !this.color.isDark() || this.color.getAlpha() < 0.45 ? '#000' : '#fff';

			// don't trigger the custom event unless it's really changed
			if ( !isInit && newColor !== oldColor ) {
				this.dispatchCustomEvent('joomla.colorpicker.change')
			}
		}

		show(e){
			const current = document.querySelector( 'color-picker.open' );
			current && current.hide();

			const self = !e ? this : e.target.getRootNode().host;

			if ( !self.isOpen ) {
				self.dropdown.classList.add('show');
				self.updateDropdown();
				self.classList.add('open');
				self.toggleEventsOnShown(1)
				self.isOpen = true
			}
		}

		hide(){
			if ( this.isOpen ) {
				this.toggleEventsOnShown();
				this.isOpen = false;

				this.classList.remove('open');
				['show','show-top'].map( x => this.dropdown.classList.remove(x) );
			}
		}
	};

	customElements.define('color-picker', ColorPicker);

})();
