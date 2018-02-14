;customElements.define('joomla-core-loader', class extends HTMLElement {
	constructor() {
		super();

		const template = document.createElement('template');
		template.innerHTML = `<style>:host{position:fixed;top:0;left:0;z-index:10000;display:block;width:100%;height:100%;overflow:hidden;background-color:#fff;opacity:.8}.box{width:300px;height:300px;margin:0 auto;-webkit-transform:rotate(45deg);transform:rotate(45deg)}.box p{float:left;margin:-20px 0 0 252px;font:normal 1.25em/1em sans-serif;color:#999;-webkit-transform:rotate(-45deg);transform:rotate(-45deg)}.box>span{-webkit-animation:jspinner 2s infinite ease-in-out;animation:jspinner 2s infinite ease-in-out}.box .red{-webkit-animation-delay:-1.5s;animation-delay:-1.5s}.box .blue{-webkit-animation-delay:-1s;animation-delay:-1s}.box .green{-webkit-animation-delay:-.5s;animation-delay:-.5s}.yellow{position:absolute;width:90px;height:90px;content:"";background:#f9a541;border-radius:90px}.yellow::before,.yellow::after{-webkit-box-sizing:content-box;box-sizing:content-box;border:50px solid #f9a541}.yellow::before{position:absolute;width:50px;height:35px;margin:60px 0 0 -30px;content:"";background:transparent;border-width:50px 50px 0;border-radius:75px 75px 0 0}.yellow::after{position:absolute;width:50px;height:101px;margin:145px 0 0 -30px;content:"";background:transparent;border-width:0 0 0 50px}.red{position:absolute;width:90px;height:90px;content:"";background:#f44321;border-radius:90px}.red::before,.red::after{-webkit-box-sizing:content-box;box-sizing:content-box;border:50px solid #f44321}.red::before{position:absolute;width:50px;height:35px;margin:60px 0 0 -30px;content:"";background:transparent;border-width:50px 50px 0;border-radius:75px 75px 0 0}.red::after{position:absolute;width:50px;height:101px;margin:145px 0 0 -30px;content:"";background:transparent;border-width:0 0 0 50px}.blue{position:absolute;width:90px;height:90px;content:"";background:#5091cd;border-radius:90px}.blue::before,.blue::after{-webkit-box-sizing:content-box;box-sizing:content-box;border:50px solid #5091cd}.blue::before{position:absolute;width:50px;height:35px;margin:60px 0 0 -30px;content:"";background:transparent;border-width:50px 50px 0;border-radius:75px 75px 0 0}.blue::after{position:absolute;width:50px;height:101px;margin:145px 0 0 -30px;content:"";background:transparent;border-width:0 0 0 50px}.green{position:absolute;width:90px;height:90px;content:"";background:#7ac143;border-radius:90px}.green::before,.green::after{-webkit-box-sizing:content-box;box-sizing:content-box;border:50px solid #7ac143}.green::before{position:absolute;width:50px;height:35px;margin:60px 0 0 -30px;content:"";background:transparent;border-width:50px 50px 0;border-radius:75px 75px 0 0}.green::after{position:absolute;width:50px;height:101px;margin:145px 0 0 -30px;content:"";background:transparent;border-width:0 0 0 50px}.yellow{margin:0 0 0 182px;-webkit-transform:rotate(0);transform:rotate(0)}.red{margin:182px 0 0 364px;-webkit-transform:rotate(90deg);transform:rotate(90deg)}.blue{margin:364px 0 0 182px;-webkit-transform:rotate(180deg);transform:rotate(180deg)}.green{margin:182px 0 0;-webkit-transform:rotate(270deg);transform:rotate(270deg)}@-webkit-keyframes jspinner{0%,40%,100%{opacity:.3}20%{opacity:1}}@keyframes jspinner{0%,40%,100%{opacity:.3}20%{opacity:1}}@media(prefers-reduced-motion:reduce){.box>span{-webkit-animation:none;animation:none}}</style>
<div class="box"><span class="yellow"></span><span class="red"></span><span class="blue"></span><span class="green"></span><p>&reg;</p></div>`;

		// Patch shadow DOM
		if (ShadyCSS) {
			ShadyCSS.prepareTemplate(template, 'joomla-core-loader');
		}

		this.attachShadow({mode: 'open'});
		this.shadowRoot.appendChild(template.content.cloneNode(true));

		// Patch shadow DOM
		if (ShadyCSS) {
			ShadyCSS.styleElement(this)
		}
	}
});