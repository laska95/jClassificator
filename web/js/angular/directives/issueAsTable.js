agile.directive('issueAsTable', function () {
    return {
        restrict: 'E',

        templateUrl: "/ng-pages/other/issue-as-table.html",

        scope: {
            issueKeyList: '=', //об'єкт задачі
            active: '=',
            'title': '@',
            'loadQuality': '=', //true/false
        },

        controller: function ($scope, $element, $attrs, $location, $timeout) {
            console.log('Hello');
            console.log($scope.issueKeyList);
            console.log($scope.issue_key_list);
            console.log($scope.title);

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
        },

    };
});


