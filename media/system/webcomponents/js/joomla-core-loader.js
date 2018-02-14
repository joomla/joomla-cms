;customElements.define('joomla-core-loader', class extends HTMLElement {
	constructor() {
		super();

		const template = document.createElement('template');
		template.innerHTML = `<style>:host{display:block;top:0;left:0;position:fixed;width:100%;height:100%;opacity:.8;overflow:hidden;z-index:10000;background-color:#fff}.box{width:300px;height:300px;margin:0 auto;-webkit-transform:rotate(45deg);transform:rotate(45deg)}.box p{float:left;margin:-20px 0 0 252px;font:normal 1.25em/1em sans-serif;color:#999;-webkit-transform:rotate(-45deg);transform:rotate(-45deg)}.box>span{-webkit-animation:jspinner 2s infinite ease-in-out;animation:jspinner 2s infinite ease-in-out}.box .red{-webkit-animation-delay:-1.5s;animation-delay:-1.5s}.box .blue{-webkit-animation-delay:-1s;animation-delay:-1s}.box .green{-webkit-animation-delay:-.5s;animation-delay:-.5s}.yellow{content:"";position:absolute;width:90px;height:90px;border-radius:90px;background:#f9a541}.yellow::before,.yellow::after{-webkit-box-sizing:content-box;box-sizing:content-box;border:50px solid #f9a541}.yellow::before{content:"";position:absolute;width:50px;height:35px;margin:60px 0 0 -30px;background:transparent;border-radius:75px 75px 0 0;border-width:50px 50px 0 50px}.yellow::after{content:"";position:absolute;width:50px;height:101px;margin:145px 0 0 -30px;background:transparent;border-width:0 0 0 50px}.red{content:"";position:absolute;width:90px;height:90px;border-radius:90px;background:#f44321}.red::before,.red::after{-webkit-box-sizing:content-box;box-sizing:content-box;border:50px solid #f44321}.red::before{content:"";position:absolute;width:50px;height:35px;margin:60px 0 0 -30px;background:transparent;border-radius:75px 75px 0 0;border-width:50px 50px 0 50px}.red::after{content:"";position:absolute;width:50px;height:101px;margin:145px 0 0 -30px;background:transparent;border-width:0 0 0 50px}.blue{content:"";position:absolute;width:90px;height:90px;border-radius:90px;background:#5091cd}.blue::before,.blue::after{-webkit-box-sizing:content-box;box-sizing:content-box;border:50px solid #5091cd}.blue::before{content:"";position:absolute;width:50px;height:35px;margin:60px 0 0 -30px;background:transparent;border-radius:75px 75px 0 0;border-width:50px 50px 0 50px}.blue::after{content:"";position:absolute;width:50px;height:101px;margin:145px 0 0 -30px;background:transparent;border-width:0 0 0 50px}.green{content:"";position:absolute;width:90px;height:90px;border-radius:90px;background:#7ac143}.green::before,.green::after{-webkit-box-sizing:content-box;box-sizing:content-box;border:50px solid #7ac143}.green::before{content:"";position:absolute;width:50px;height:35px;margin:60px 0 0 -30px;background:transparent;border-radius:75px 75px 0 0;border-width:50px 50px 0 50px}.green::after{content:"";position:absolute;width:50px;height:101px;margin:145px 0 0 -30px;background:transparent;border-width:0 0 0 50px}.yellow{margin:0 0 0 182px;-webkit-transform:rotate(0);transform:rotate(0)}.red{margin:182px 0 0 364px;-webkit-transform:rotate(90deg);transform:rotate(90deg)}.blue{margin:364px 0 0 182px;-webkit-transform:rotate(180deg);transform:rotate(180deg)}.green{margin:182px 0 0 0;-webkit-transform:rotate(270deg);transform:rotate(270deg)}@-webkit-keyframes jspinner{0%,40%,100%{opacity:.3}20%{opacity:1}}@keyframes jspinner{0%,40%,100%{opacity:.3}20%{opacity:1}}@media(prefers-reduced-motion:reduce){.box>span{-webkit-animation:none;animation:none}}</style>
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