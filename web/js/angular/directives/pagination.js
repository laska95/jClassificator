agile.directive('pagination', function () {
    return {
        restrict: 'E',

        templateUrl: "/ng-pages/other/pagination.html",

        scope: {
            ngModel: '=',
        },

        controller: function ($scope, $element, $attrs, $location, $timeout) {

            var recount = function () {
                var total = $scope.ngModel.total;
                var max = $scope.ngModel.maxResults;
                var start = $scope.ngModel.startAt;

                $scope.total = total;
                $scope.pageCount = (total >= max) ? Math.ceil(total / max) : 1;
                $scope.currentPage = Math.ceil((start + max) / max);
                $scope.from = $scope.currentPage + ($scope.currentPage - 1) * max;
                $scope.to = max * $scope.currentPage;
                if ($scope.currentPage == $scope.pageCount) {
                    $scope.to = $scope.total - ($scope.pageCount * max);
                }

                $scope.pageArr = [];
                for (var i = 1; i <= $scope.pageCount; i++) {
                    $scope.pageArr.push(i);
                }

            };

            $scope.$watch(function () {
                return JSON.stringify($scope.ngModel);
            }, function (newValue) {
                recount();
            });

        },

    };
});


