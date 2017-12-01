agile.directive('issueAsTable', function () {
    return {
        restrict: 'E',

        templateUrl: "/ng-pages/other/issue-as-table.html",

        scope: {
            issueKeyList: '=', //об'єкт задачі
            active: '=',
            'title': '@',
            'loadQuality': '=', //true/false
            total: '@'
        },

        controller: function ($scope, $element, $attrs, $location, $timeout) {

            $scope.ifActiveIssue = function () {
                return (typeof $scope.active !== 'undefined');
            };
            
            $scope.toggleActive = function (key) {

                if ($scope.active == key) {
                    $scope.active = undefined;
                } else {
                    $scope.active = key;
                }
            };
              
            $scope.count = (typeof $scope.issueKeyList === 'object') ?  $scope.issueKeyList.length : 0;

        },

    };
});


