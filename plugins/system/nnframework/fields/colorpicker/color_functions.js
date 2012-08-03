function baseConverter(number, ob, nb)
{
	number = number+"";
	number = number.toUpperCase();
	var list = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	var dec = 0;
	for (var i = 0; i <= number.length; i++) {
		dec += (list.indexOf(number.charAt(i)))*(Math.pow(ob, (number.length-i-1)));
	}
	number = "";
	var magnitude = Math.floor((Math.log(dec))/(Math.log(nb)));
	for (var i = magnitude; i >= 0; i--) {
		var amount = Math.floor(dec/Math.pow(nb, i));
		number = number+list.charAt(amount);
		dec -= amount*(Math.pow(nb, i));
	}
	if (number.length == 0) {
		number = 0;
	}
	return number;
}

// Converts a RGB color to HSV
function toHSV(rgbColor)
{
	rgbColor = rgbColor.replace('#', '');

	red = baseConverter(rgbColor.substr(0, 2), 16, 10);
	green = baseConverter(rgbColor.substr(2, 2), 16, 10);
	blue = baseConverter(rgbColor.substr(4, 2), 16, 10);
	if (red.length == 0) {
		red = 0;
	}
	if (green.length == 0) {
		green = 0;
	}
	if (blue.length == 0) {
		blue = 0;
	}
	red = red/255;
	green = green/255;
	blue = blue/255;

	maxValue = Math.max(red, green, blue);
	minValue = Math.min(red, green, blue);

	var hue = 0;

	if (maxValue == minValue) {
		hue = 0;
		saturation = 0;
	} else {
		if (red == maxValue) {
			hue = (green-blue)/(maxValue-minValue)/1;
		} else if (green == maxValue) {
			hue = 2+(blue-red)/1/(maxValue-minValue)/1;
		} else if (blue == maxValue) {
			hue = 4+(red-green)/(maxValue-minValue)/1;
		}
		saturation = (maxValue-minValue)/maxValue;
	}
	hue = hue*60;
	valueBrightness = maxValue;

	if (valueBrightness/1 < 0.5) {
		//saturation = (maxValue - minValue) / (maxValue + minValue);
	}
	if (valueBrightness/1 >= 0.5) {
		//saturation = (maxValue - minValue) / (2 - maxValue - minValue);
	}

	returnArray = [hue, saturation, valueBrightness];
	return returnArray;
}

function toRgb(hue, saturation, valueBrightness)
{
	Hi = Math.floor(hue/60);
	if (hue == 360) {
		Hi = 0;
	}
	f = hue/60-Hi;
	p = (valueBrightness*(1-saturation)).toPrecision(2);
	q = (valueBrightness*(1-(f*saturation))).toPrecision(2);
	t = (valueBrightness*(1-((1-f)*saturation))).toPrecision(2);

	switch (Hi) {
		case 0:
			red = valueBrightness;
			green = t;
			blue = p;
			break;
		case 1:
			red = q;
			green = valueBrightness;
			blue = p;
			break;
		case 2:
			red = q;
			green = valueBrightness;
			blue = t;
			break;
		case 3:
			red = p;
			green = q;
			;
			blue = valueBrightness;
			break;
		case 4:
			red = t;
			green = p;
			blue = valueBrightness;
			break;
		case 5:
			red = valueBrightness;
			green = p;
			blue = q;
			break;
	}

	if (saturation == 0) {
		red = valueBrightness;
		green = valueBrightness;
		blue = valueBrightness;
	}

	red *= 255;
	green *= 255;
	blue *= 255;

	red = Math.round(red);
	green = Math.round(green);
	blue = Math.round(blue);

	red = baseConverter(red, 10, 16);
	green = baseConverter(green, 10, 16);
	blue = baseConverter(blue, 10, 16);

	red = red+"";
	green = green+"";
	blue = blue+"";

	while (red.length < 2) {
		red = "0"+red;
	}
	while (green.length < 2) {
		green = "0"+green;
	}
	while (blue.length < 2) {
		blue = "0"+""+blue;
	}
	rgbColor = "#"+red+""+green+""+blue;
	return rgbColor.toUpperCase();
}

function findColorByDegrees(rgbColor, degrees)
{
	rgbColor = rgbColor.replace('#', '');
	myArray = toHSV(rgbColor);
	myArray[0] += degrees;
	if (myArray[0] >= 360) {
		myArray[0] -= 360;
	}
	if (myArray[0] < 0) {
		myArray[0] += 360;
	}
	return toRgb(myArray[0], myArray[1], myArray[2]);
}

function findColorByBrightness(rgbColor, brightness)
{

	rgbColor = rgbColor.replace('#', '');
	myArray = toHSV(rgbColor);

	myArray[2] += brightness/100;
	if (myArray[2] > 1) {
		myArray[2] = 1;
	}
	if (myArray[2] < 0) {
		myArray[2] = 0;
	}

	myArray[1] += brightness/100;
	if (myArray[1] > 1) {
		myArray[1] = 1;
	}
	if (myArray[1] < 0) {
		myArray[1] = 0;
	}

	return toRgb(myArray[0], myArray[1], myArray[2]);

}
