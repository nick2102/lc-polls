var lcRequest = {

    /**
     * Base api endpoint
     */
    baseUrl : window.lcpollsApiUrl,

    /**
     * Api Endpoint
     */
    endpoint: null,
    /**
     * Request method, default post
     */
    method :'post',

    /**
     * Request
     */
    request : null,

    /**
     * Response
     */
    response : null,
    /**
     * Error messages
     */
    error : {
        title: window.lcpollsTranslations.errorTitle,
        message: ''
    },

    /**
     * Response type, possible values are:
     * arraybuffer, blob, document, json, text, stream
     */
    responseType : 'json',
    /**
     * Request headers, default is empty object
     */
    headers : { 'Content-Type': 'application/json' },
    /**
     * Post parameters
     */
    postParams : {},
    /**
     * Get parameters
     */
    getParams : {},

    /**
     * Add post parameter
     * @param key
     * @param value
     */
    addPostParam : function(key, value) {
        lcRequest.postParams[key] = value;
    },

    /**
     * Add get parameter
     * @param key
     * @param value
     */
    addGetParam : function (key, value) {
        lcRequest.getParams[key] = value;
    },

    /**
     * Execute request
     * @param success
     * @param error
     * @returns {*}
     */
    execute: function (success, error) {

        lcRequest.request = axios(lcRequest.getRequestConfig());
        lcRequest.request
            .then(function (resp){
                jQuery('.loadingScreen').css('display', 'none');
                jQuery('.loadingBtn').css('display', 'none');
                jQuery('.lc-polls-btn-send').prop('disabled', false);
                success(resp);
            })
            .catch(function(err) {
                jQuery('.loadingScreen').css('display', 'none');
                jQuery('.loadingBtn').css('display', 'none');
                jQuery('.lc-polls-btn-send').prop('disabled', false);
                if (typeof error === 'function') {
                    error(err);
                } else {
                    jQuery('.loadingScreen').css('display', 'none');
                    jQuery('.loadingBtn').css('display', 'none');
                    jQuery('.lc-polls-btn-send').prop('disabled', false);
                    if(err.response){
                        lcRequest.error.message = err.response.data.message;
                    }
                    Swal.fire(lcRequest.error.title, lcRequest.error.message, 'error');
                    return;
                }
            });
        return lcRequest.request;
    },

    /**
     * Build config for request
     * @returns {{method: string, url: String, baseURL: string, responseType: string, headers: {}}}
     */
    getRequestConfig: function () {
        var CancelToken = axios.CancelToken;
        var config = {
            method: lcRequest.method,
            url: lcRequest.endpoint,
            baseURL: lcRequest.baseUrl,
            responseType: lcRequest.responseType,
            cancelToken: new CancelToken(function(c) {
                lcRequest.abort = c;
            }),
            headers: lcRequest.headers
        };
        if (lcRequest.method === 'post' || lcRequest.method === 'put' || lcRequest.method === 'patch') {
            config.data = lcRequest.postParams;
        } else if (lcRequest.method === 'get') {
            config.params = lcRequest.getParams;
        }

        return config;
    }
}