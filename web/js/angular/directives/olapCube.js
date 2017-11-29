agile.directive('olapCube', function ($q) {
    return {
        restrict: 'E',
        templateUrl: "/ng-pages/other/olap-cube.html",
        scope: {
            xcode: '=', //об'єкт задачі
            ycode: '=',
            issues: '=', //issue_keys
            apply: '='
        },
        controller: function ($scope, $element, $attrs, $location, $timeout) {
            $scope.xData = {};
            $scope.yData = {};
            $scope.xRet = {};
            $scope.yRet = {};
            $scope.allKeys = [];

            $scope.getFilterObj = function (code) {

                var arr = {
                    AD: {
                        key: 'AD', //AvailabilityDescription
                        label: 'Якісті опису',
                        url: '/decision/full-api/availability-description',
                    },
                    PC: {
                        key: 'PC', //DecisionApiPriorityClusteringCtrl
                        label: 'Класифікація за пріортетом',
                        url: '/decision/full-api/priority-clustering'
                    },
                    LC: {
                        key: 'LC', //DecisionApiLinksClusteringCtrl
                        label: 'Кластиризація за спільними ресурсами',
                        url: '/decision/full-api/links-clustering'
                    },
                    TC: {
                        key: 'TC', //DecisionApiLinksClusteringCtrl
                        label: 'Кластиризація за описом',
                        url: '/decision/full-api/text-clustering'
                    },
                };

                return arr[code];
            };

            var loadData = function () {
                var x = $scope.getFilterObj($scope.xcode);
                var y = $scope.getFilterObj($scope.ycode);
                
                if ($scope.issues.length == 0){
                    var deferred = $q.defer();
                    deferred.resolve(null);
                    return deferred.promise;
                }

                var d1 = $q.defer();
                var d2 = $q.defer();
                var all_keys = [];
                var deferred = $q.all(d1, d2);

                //xCode
                $.ajax({
                    type: 'POST',
                    url: x.url,
                    data: {issue_key_arr: $scope.issues},
                    dataType: 'json'
                }).done(function (data) {
                    $scope.xRet = data;

                    angular.forEach(data, function (d_one) {
                        if ($scope.xcode === 'TC') {
                            all_keys.push(d_one);
                        } else {
                            angular.forEach(d_one.items, function (one_key) {
                                all_keys.push(one_key);
                            });
                        }
                    });
                    $scope.xData = toAsgn($scope.xcode, data);
                    d1.resolve(data);
                });

                //yCode
                $.ajax({
                    type: 'POST',
                    url: y.url,
                    data: {issue_key_arr: $scope.issues},
                    dataType: 'json'
                }).done(function (data) {
                    $scope.yRet = data;
                    angular.forEach(data, function (d_one) {
                        if ($scope.xcode === 'TC') {
                            all_keys.push(d_one);
                        } else {
                            angular.forEach(d_one.items, function (one_key) {
                                all_keys.push(one_key);
                            });
                        }
                    });
                    $scope.yData = toAsgn($scope.ycode, data);
                    $timeout(function () {
                        $scope.$apply();
                    });
                    d2.resolve(data);
                });

                return deferred;
            };

            var toAsgn = function (code, data) {
                var ret = [];

                if ((code === 'AD')) {

                    angular.forEach(data, function (data_one) {
                        var r_one = {
                            id: data_one.class.id,
                            label: data_one.class.label,
                            items: data_one.items
                        };
                        ret.push(r_one);
                    });

                } else if ((code === 'PC')) {
                    angular.forEach(data, function (data_one, key) {
                        var r_one = {
                            id: data_one.class.id,
                            label: 'Джерело ' + key,
                            items: data_one.items
                        };
                        ret.push(r_one);
                    });
                }
                
                return ret;
            };

            $scope.$watch(function (){
                return JSON.stringify($scope.apply);
            }, function () {
                var p = loadData();
                p.then(function (data){
                    $timeout(function () {
                        $scope.$apply();
                    });
                });
            });

            $scope.isCross = function (elem, arr){
                var i = arr.indexOf(elem);
                return (i > -1)? true : false;
            };
            
            $scope.isHasCross = function (x_elem, y_elem){
                var ret = false;
                angular.forEach(x_elem.items, function (one_x){
                    var i = y_elem.items.indexOf(one_x);
                    if (i > -1){
                        ret = true;
                    }
                });
                return ret;
            };

        }
    };
});


