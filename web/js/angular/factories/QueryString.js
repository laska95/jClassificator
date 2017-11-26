
agile.factory('QueryString', [function () {


        var forEach = angular.forEach;
        var hasOwnProperty = angular.hasOwnProperty;
        var isArray = angular.isArray;
        var isDefined = angular.isDefined;

        // a private function in angular.js
        function encodeUriQuery(val, pctEncodeSpaces) {

            return encodeURIComponent(val).
                    replace(/%40/gi, '@').
                    replace(/%3A/gi, ':').
//                    replace(/%5B/gi, '[').
//                    replace(/%5D/gi, ']').
                    replace(/%24/g, '$').
                    replace(/%2C/gi, ',').
                    replace(/%20/g, (pctEncodeSpaces ? '%20' : '+'));
        }

        // a private function in angular.js
        function tryDecodeURIComponent(value) {
            try {
                return decodeURIComponent(value);
            } catch (e) {
                // Ignore any invalid uri component
            }
        }

        // a private function in angular.js
        function parseKeyValue(/**string*/keyValue) {
            var obj = {}, key_value, key;
            forEach((keyValue || "").split('&'), function (keyValue) {
                if (keyValue) {
                    key_value = keyValue.split('=');
                    key = tryDecodeURIComponent(key_value[0]);
                    if (isDefined(key)) {
                        var val = isDefined(key_value[1]) ? tryDecodeURIComponent(key_value[1]) : true;
                        if (!hasOwnProperty.call(obj, key)) {
                            obj[key] = val;
                        } else if (isArray(obj[key])) {
                            obj[key].push(val);
                        } else {
                            obj[key] = [obj[key], val];
                        }
                    }
                }
            });
            return obj;
        }

        // a private function in angular.js
        function toKeyValue(obj) {
            var parts = [];
            forEach(obj, function (value, key) {
                if (isArray(value)) {
                    forEach(value, function (arrayValue) {
                        parts.push(encodeUriQuery(key, true) +
                                (arrayValue === true ? '' : '=' + encodeUriQuery(arrayValue, true)));
                    });
                } else {
                    parts.push(encodeUriQuery(key, true) +
                            (value === true ? '' : '=' + encodeUriQuery(value, true)));
                }
            });
            return parts.length ? parts.join('&') : '';
        }

        return {
            parseKeyValue: parseKeyValue,
            toKeyValue: toKeyValue
        }
    }]);
