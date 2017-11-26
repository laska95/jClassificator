
agile.config(function ($locationProvider, $routeProvider, $qProvider, $provide, $httpProvider, $stateProvider) {
    $locationProvider.hashPrefix('');
    $locationProvider.html5Mode({
        enabled: true,
        rewriteLinks: false
    });

    $routeProvider.when('/jira', {
        templateUrl: '/ng-pages/jira/jira.html',
        controller: 'JiraController',
        reloadOnSearch: false,
    }).when('/decision-api', {
        templateUrl: '/ng-pages/decision_api/decision_api.html',
        reloadOnSearch: false,
    });


    $qProvider.errorOnUnhandledRejections(false);

    $provide.decorator('$controller', function ($delegate) {
        return function (constructor, locals, later, indent) {
            if (typeof constructor === 'string' && !locals.$scope.controllerName) {
                locals.$scope.$root.controllerName = constructor;
                locals.$scope.controllerName = constructor;
            }
            return $delegate(constructor, locals, later, indent);
        };
    });
    $httpProvider.defaults.paramSerializer = 'jQueryLikeSerializeFixed';

    $provide.decorator('$browser', ['$delegate', function ($delegate) {
            var superUrl = $delegate.url;
            $delegate.url = function (url, replace) {
                if (url !== undefined) {
                    return superUrl(url.replace(/\%20/g, "+").replace(/\%5B/g, "[").replace(/\%5D/g, "]"), replace);
                } else {
                    return superUrl()
                            .replace(/\+/g, "%20")
                            .replace(/\[/g, "%5B")
                            .replace(/\]/g, "%5D");
                }
            }
            return $delegate;
        }]);

}).run(function ($rootScope, $route, $location) {
    $rootScope.safeApply = function (fn) {
        var phase = this.$root.$$phase;
        if (phase === '$apply' || phase === '$digest') {
            if (fn && (typeof (fn) === 'function')) {
                fn();
            }
        } else {
            this.$apply(fn);
        }
    };

    var original = $location.path;
    $location.path = function (path, reload) {
        if (reload === false) {
            var lastRoute = $route.current;
            var un = $rootScope.$on('$locationChangeSuccess', function () {
                $route.current = lastRoute;
                un();
            });
        }
        return original.apply($location, [path]);
    };

    $rootScope.searchToUrl = function (params) {
        var nparams = {};
        angular.forEach(params, function (value, key) {
            if (isArray(value)) {
                var val = [];
                angular.forEach(value, function (v1, k1) {
                    if (v1 !== '') {
                        val.push(v1);
                    }
                });
                if (val.length) {
                    nparams[key + '[]'] = val;
                }
            } else if ($.isArray(value)) {
                var val = {};

                angular.forEach(value, function (v1, k1) {
                    if (v1 !== '') {
                        val[k1] = v1;
                    }
                });

                if (val.length) {
                    nparams[key + '[]'] = val;
                }
            } else {
                if (value !== '') {
                    nparams[key] = value;
                }
            }
        });

        var extended = angular.extend($location.search(), nparams);

        $location.search(extended);

        for (var p in params) {

            if ($.isArray(params[p]) && params[p].length == 0) {
                $location.search(p, null);
                $location.search(p + '[]', null);
            } else {
                if (!params[p]) {
                    $location.search(p, null);

                }
            }


            if ($.isArray(params[p + '[]']) && params[p + '[]'].length == 0) {
                $location.search(p + '[]', null);
                $location.search(p, null);
            } else {
                if (!$.isArray(params[p + '[]']) && !params[p + '[]']) {
//                    $location.search(p + '[]', null);

                }
            }

        }

    };

});
