function iFrameHeight(iframe)
{
    var doc    = 'contentDocument' in iframe ? iframe.contentDocument : iframe.contentWindow.document;
    var height = parseInt(doc.body.scrollHeight);

    if (!document.all)
    {
        iframe.style.height = parseInt(height) + 60 + 'px';
    }
    else if (document.all && iframe.id)
    {
        document.all[iframe.id].style.height = parseInt(height) + 20 + 'px';
    }
}
