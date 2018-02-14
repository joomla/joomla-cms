;customElements.define('joomla-core-loader', class extends HTMLElement {
	constructor() {
		super();

		// Define some things
		this.css = `
joomla-core-loader {
display: block;
top:0;
left:0;
position: fixed;
width: 100%;
height: 100%;
opacity: .8;
overflow: hidden;
z-index: 10000;
background-color: #fff;
}
#joomla-loader{
    transform: rotate(45deg);
    position: absolute;
    left: 50%;
}

#joomla-loader-first, #joomla-loader-second, #joomla-loader-third, #joomla-loader-fourth{
    width: 200px;
    position: absolute;
}
#joomla-loader-first{
    left: 200px;
}
#joomla-loader-second{
    transform: rotate(90deg);
    left: 340px;
    top: 153px;
}
#joomla-loader-third{
    transform: rotate(180deg);
    top: 290px;
    left: 198px;
}
#joomla-loader-fourth{
    transform: rotate(270deg);
    top: 148px;
    left:
        59px;
}

.top-circle{
    border-radius: 50%;
    width: 115px;
    height: 115px;
    margin-left: 45px;
}

.top-arch{
    width: 200px;
    height: 100px;
    /*border-radius: 50%;*/
    margin-top: -30px;
    margin-bottom: -1px;
    margin-left: -1px;

}
.inner-arch{
    width: 100px;
    height: 100px;
    border-radius: 50%;
    position: relative;
    margin: auto;
    top: 50px;
}

.bottom-rect{
    width: 61px;
}

.one {
    height: 85px;
}
.two {
    height: 50px;
    margin-top: -1px;
}
#joomla-loader-second .one{
    height: 15px;
}
#joomla-loader-second .two{
    height: 61px;
}
#joomla-loader-second .three{
    height: 55px;
    margin-top: -1px;
}
#joomla-loader-third .one{
    height: 15px;
}
#joomla-loader-third .two{
    height: 120px;
}

#joomla-loader-first .top-circle, #joomla-loader-first .bottom-rect .one, #joomla-loader-first .bottom-rect .two{
    background-color: #F9A541;
}
#joomla-loader-first .top-arch{
    background: rgba(255, 255, 255, 0); /* Old browsers */
    background: -moz-radial-gradient(center, ellipse cover,  rgba(255, 255, 255, 0) 0%, rgba(255, 255, 255, 0) 27%, #f9a541 27%, #f9a541 70%, rgba(255, 255, 255, 0) 70%, rgba(255, 255, 255, 0) 100%); /* FF3.6-15 */
    background: -webkit-radial-gradient(center, ellipse cover,  rgba(255, 255, 255, 0) 0%,rgba(255, 255, 255, 0) 27%,#f9a541 27%,#f9a541 70%,rgba(255, 255, 255, 0) 70%,rgba(255, 255, 255, 0) 100%); /* Chrome10-25,Safari5.1-6 */
    background: radial-gradient(ellipse at center,  rgba(255, 255, 255, 0) 0%,rgba(255, 255, 255, 0) 27%,#f9a541 27%,#f9a541 70%,rgba(255, 255, 255, 0) 70%,rgba(255, 255, 255, 0) 100%); /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */
    filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='rgba(255, 255, 255, 0)', endColorstr='#f9a541',GradientType=1 ); /* IE6-9 fallback on horizontal gradient */
    background-size: 200px 200px;
    background-position: top center;
}

#joomla-loader-second .top-circle, #joomla-loader-second .bottom-rect .one, #joomla-loader-second .bottom-rect .two, #joomla-loader-second .bottom-rect .three{
    background-color: #F44321;
}
#joomla-loader-second .top-arch{
    background: rgba(255, 255, 255, 0); /* Old browsers */
    background: -moz-radial-gradient(center, ellipse cover,  rgba(255, 255, 255, 0) 0%, rgba(255, 255, 255, 0) 27%, #F44321 27%, #F44321 70%, rgba(255, 255, 255, 0) 70%, rgba(255, 255, 255, 0) 100%); /* FF3.6-15 */
    background: -webkit-radial-gradient(center, ellipse cover,  rgba(255, 255, 255, 0) 0%,rgba(255, 255, 255, 0) 27%,#F44321 27%,#F44321 70%,rgba(255, 255, 255, 0) 70%,rgba(255, 255, 255, 0) 100%); /* Chrome10-25,Safari5.1-6 */
    background: radial-gradient(ellipse at center,  rgba(255, 255, 255, 0) 0%,rgba(255, 255, 255, 0) 27%,#F44321 27%,#F44321 70%,rgba(255, 255, 255, 0) 70%,rgba(255, 255, 255, 0) 100%); /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */
    filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='rgba(255, 255, 255, 0)', endColorstr='#F44321',GradientType=1 ); /* IE6-9 fallback on horizontal gradient */
    background-size: 200px 200px;
    background-position: top center;
}

#joomla-loader-third .top-circle, #joomla-loader-third .bottom-rect .one, #joomla-loader-third .bottom-rect .two{
    background-color: #5091CD;
}
#joomla-loader-third .top-arch{
    background: rgba(255, 255, 255, 0); /* Old browsers */
    background: -moz-radial-gradient(center, ellipse cover,  rgba(255, 255, 255, 0) 0%, rgba(255, 255, 255, 0) 27%, #5091CD 27%, #5091CD 70%, rgba(255, 255, 255, 0) 70%, rgba(255, 255, 255, 0) 100%); /* FF3.6-15 */
    background: -webkit-radial-gradient(center, ellipse cover,  rgba(255, 255, 255, 0) 0%,rgba(255, 255, 255, 0) 27%,#5091CD 27%,#5091CD 70%,rgba(255, 255, 255, 0) 70%,rgba(255, 255, 255, 0) 100%); /* Chrome10-25,Safari5.1-6 */
    background: radial-gradient(ellipse at center,  rgba(255, 255, 255, 0) 0%,rgba(255, 255, 255, 0) 27%,#5091CD 27%,#5091CD 70%,rgba(255, 255, 255, 0) 70%,rgba(255, 255, 255, 0) 100%); /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */
    filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='rgba(255, 255, 255, 0)', endColorstr='#5091CD',GradientType=1 ); /* IE6-9 fallback on horizontal gradient */
    background-size: 200px 200px;
    background-position: top center;
}

#joomla-loader-fourth .top-circle, #joomla-loader-fourth .bottom-rect .one, #joomla-loader-fourth .bottom-rect .two{
    background-color: #7AC143;
}
#joomla-loader-fourth .top-arch{
    background: rgba(255, 255, 255, 0); /* Old browsers */
    background: -moz-radial-gradient(center, ellipse cover,  rgba(255, 255, 255, 0) 0%, rgba(255, 255, 255, 0) 27%, #7AC143 27%, #7AC143 70%, rgba(255, 255, 255, 0) 70%, rgba(255, 255, 255, 0) 100%); /* FF3.6-15 */
    background: -webkit-radial-gradient(center, ellipse cover,  rgba(255, 255, 255, 0) 0%,rgba(255, 255, 255, 0) 27%,#7AC143 27%,#7AC143 70%,rgba(255, 255, 255, 0) 70%,rgba(255, 255, 255, 0) 100%); /* Chrome10-25,Safari5.1-6 */
    background: radial-gradient(ellipse at center,  rgba(255, 255, 255, 0) 0%,rgba(255, 255, 255, 0) 27%,#7AC143 27%,#7AC143 70%,rgba(255, 255, 255, 0) 70%,rgba(255, 255, 255, 0) 100%); /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */
    filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='rgba(255, 255, 255, 0)', endColorstr='#7AC143',GradientType=1 ); /* IE6-9 fallback on horizontal gradient */
    background-size: 200px 200px;
    background-position: top center;
}

.vanish{
    animation: vanish 2s infinite;
}

@keyframes vanish{
    0%{
        opacity: 1;
    }
    50%{
        opacity: 0;
    }
    100%{
        opacity: 1;
    }
}`;
		this.styleEl = document.createElement('style');
		this.styleEl.id = 'joomla-loader-css';
		this.styleEl.innerHTML = this.css;

		this.element = document.createElement('div');
		this.element.id = 'joomla-loader';
		this.element.innerHTML = `<div id="joomla-loader-first">
            <div class="top-circle vanish"></div>
            <div class="top-arch"></div>
            <div class="bottom-rect">
                <div class="one"></div>
                <div class="two vanish"></div>
            </div>
        </div>

        <div id="joomla-loader-second">
            <div class="top-circle vanish"></div>
            <div class="top-arch vanish"></div>
            <div class="bottom-rect">
                <div class="one vanish"></div>
                <div class="two"></div>
                <div class="three vanish"></div>
            </div>
        </div>

        <div id="joomla-loader-third">
            <div class="top-circle vanish"></div>
            <div class="top-arch vanish"></div>
            <div class="bottom-rect">
                <div class="one vanish"></div>
                <div class="two"></div>
            </div>
        </div>

        <div id="joomla-loader-fourth">
            <div class="top-circle vanish"></div>
            <div class="top-arch"></div>
            <div class="bottom-rect">
                <div class="one"></div>
                <div class="two"></div>
            </div>
        </div>`;

		if (!document.head.querySelector('#joomla-loader-css')) {
			document.head.appendChild(this.styleEl)
		}
	}

	connectedCallback() {
		this.appendChild(this.element);
	}
});