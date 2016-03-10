function iFrameHeight()
{
	var h = 0;
	if (!document.all)
	{
		h = document.getElementById('blockrandom').height;
		document.getElementById('blockrandom').style.height = parseInt(h) + 60 + 'px';
	} else if (document.all)
	{
		h = document.frames('blockrandom').document.body.scrollHeight;
		document.all.blockrandom.style.height = parseInt(h) + 20 + 'px';
	}
}
