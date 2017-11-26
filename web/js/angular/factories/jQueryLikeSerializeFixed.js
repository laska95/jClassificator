
agile.factory('jQueryLikeSerializeFixed', function () {

    /**
     * This method is intended for encoding *key* or *value* parts of query component. We need a custom
     * method because encodeURIComponent is too aggressive and encodes stuff that doesn't have to be
     * encoded per http://tools.ietf.org/html/rfc3986:
     *    query       = *( pchar / "/" / "?" )
     *    pchar         = unreserved / pct-encoded / sub-delims / ":" / "@"
     *    unreserved    = ALPHA / DIGIT / "-" / "." / "_" / "~"
     *    pct-encoded   = "%" HEXDIG HEXDIG
     *    sub-delims    = "!" / "$" / "&" / "'" / "(" / ")"
     *                     / "*" / "+" / "," / ";" / "="
     */
    function encodeUriQuery(val, pctEncodeSpaces) {
        return encodeURIComponent(val).
                replace(/%40/gi, '@').
                replace(/%3A/gi, ':').
                replace(/%24/g, '$').
                replace(/%5B/g, '[').
                replace(/%5D/g, ']').
                replace(/%2C/gi, ',').
                replace(/%3B/gi, ';').
                replace(/%20/g, (pctEncodeSpaces ? '%20' : '+'));
    }

    function serializeValue(v) {
        if (angular.isObject(v)) {
            return angular.isDate(v) ? v.toISOString() : angular.toJson(v);
        }
        return v;
    }

    function forEachSorted(obj, iterator, context) {
        var keys = Object.keys(obj).sort();
        for (var i = 0; i < keys.length; i++) {
            iterator.call(context, obj[keys[i]], keys[i]);
        }
        return keys;
    }

    /**
     * Fixed version of $httpParamSerializerJQLike
     * $httpParamSerializerJQLike with the current version of Angular for Ionic
     * does not serialize array indices correctly. This updated version was pulled from here
     * https://github.com/ggershoni/angular.js/blob/0c98ba4105d50afc1fde3f7a308eb13d234d0e57/src/ng/http.js
     * @param  {[Object]} params
     * @return {[String]} Serialized data
     */
    function jQueryLikeParamSerializer(params) {
        if (!params)
            return '';

        var nParams = params;

        var parts = [];
        serialize(nParams, '', true);

        return parts.join('&');

        function clearEmpties(o) {
            for (var k in o) {
                if (!o[k] || typeof o[k] !== "object") {
                    continue // If null or not an object, skip to the next iteration
                }

                // The property is an object
                if (Object.keys(o[k]).length === 0) {
                    delete o[k]; // The object had no properties, so delete that property
                }
            }
        }
        
        function serialize(toSerialize, prefix, topLevel) {
            if (toSerialize === null || angular.isUndefined(toSerialize))
                return;
            if (angular.isArray(toSerialize)) {
                angular.forEach(toSerialize, function (value, index) {
                    serialize(value, prefix + '[' + (angular.isObject(value) ? index : '') + ']');
                });
            } else if (angular.isObject(toSerialize) && !angular.isDate(toSerialize)) {
                forEachSorted(toSerialize, function (value, key) {
                    serialize(value, prefix +
                            (topLevel ? '' : '[') +
                            key +
                            (topLevel ? '' : ']'));
                });
            } else {
                parts.push(encodeUriQuery(prefix) + '=' + encodeUriQuery(serializeValue(toSerialize)));
            }
        }
    }
    ;
    return jQueryLikeParamSerializer;
});
