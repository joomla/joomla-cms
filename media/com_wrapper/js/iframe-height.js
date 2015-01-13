function iFrameHeight()
        {
        var h = 0;
        if (!document.all)
        {
                h = document.getElementById('blockrandom').contentDocument.height;
                document.getElementById('blockrandom').style.height = h + 60 + 'px';
        } else if (document.all)
        {
                h = document.frames('blockrandom').document.body.scrollHeight;
                document.all.blockrandom.style.height = h + 20 + 'px';
        }
}
