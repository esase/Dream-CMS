
/**
 * Uniform height
 *
 * @param string elements
 * @param boolean bindWindowResize
 * @return void
 */
function uniformHeight(elements, bindWindowResize)
{
    var maxHeight = 0, max = Math.max;

    $(elements).each(function() {
        $(this).css('height', 'auto')
        maxHeight = max(maxHeight, $(this).height());
    }).height(maxHeight);

    // change elements height if window's size will change
    if (typeof bindWindowResize != 'undefined' && bindWindowResize == true) { 
        $(window).resize(function(){
            uniformHeight(elements);
        });
    }
}

/**
 * Ajax query
 *
 * @param string container
 * @param string url
 * @param string successCallback
 * @param string method
 * @return void
 */
function ajaxQuery(container, url, successCallback, method)
{
    // show a loading box
    showLoadingBox(container);

    // define an ajax query method
    method = typeof method != 'undefined' && method == 'post' ? method : 'get';

    // add a hash to url
    if (method == 'get') {
        var _r =  Math.random();
        url =  url + (url.match(/\?/) ? '&_r=' + _r : '?_r=' + _r);
    }

    $.ajax({
        type: method,
        url: url,
        success: function(data){
            // replace text into a container
            $('#' + container).html(data);

            // call a callback
            if (typeof successCallback != 'undefined') {
                successCallback.call();
            }
        }
    });
}

/**
 * Show loading box
 * 
 * @param jquery object|string container
 * @return void
 */
function showLoadingBox(container)
{
    if ($.type(container) === "string") {
        container = $('#' + container);
    }

    var loadingDiv = jQuery(
        '<div class="loading-ajax-wrapper"><div class="loading-ajax"></div></div>'
    ).appendTo(container);

    var iLeftOff = container.width()  / 2 - (loadingDiv.find('div.loading-ajax:first').width()  / 2);
    var iTopOff  = container.height() / 2 - (loadingDiv.find('div.loading-ajax:first').height() / 2) + 10;

    if (iTopOff<0) {
        iTopOff = 0;
    }

    loadingDiv.css({
        position: 'absolute',
        left: iLeftOff,
        top:  iTopOff,
        zIndex:100
    });
}

/**
 * Show popup window
 *
 * @param string url
 * @param string popupId
 * @return void
 */
function showPopup(url, popupId)
{
    // remove previously opened popup
    $('#' + popupId).remove();

    $.ajax({
    	'type'      : "get",
    	'url'       : url + '?_r=' + Math.random(),
    	'success'   : function(data) {
    	    $(document.body).append(data);
    	    $('#' + popupId).modal('show');
    	}
    });
}