function iFrameHeight()
{
    var height = 0;
    var iframe = document.getElementById('blockrandom');
    var doc    = 'contentDocument' in iframe ? iframe.contentDocument : iframe.contentWindow.document;

    if (!document.all)
    {
        height = doc.body.scrollHeight;
        iframe.style.height = parseInt(height) + 60 + 'px';
    }
    else if (document.all)
    {
        height = doc.body.scrollHeight;
        document.all.blockrandom.style.height = parseInt(height) + 20 + 'px';
    }
}
