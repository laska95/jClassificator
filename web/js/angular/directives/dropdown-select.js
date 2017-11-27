
agile.directive('dropdownSelect', function () {
    return {
        restrict: 'E',
        
        templateUrl: "/ng-pages/other/dropdown-select.html",
        
        scope: {
            ngModel: '=',   //куди зберігати вибрані значення
            key: '@',       //ключ для вибору
            title: '@',     //значення, що виводиться
            items: '=?',    //масив для вибору
        },
        
        controller: function ($scope, $element, $attrs, $location, $timeout) {
            
            $scope.selectItem = undefined;
            
            $scope.toggleVal = function (item){
                $scope.ngModel = item[$scope.key];
                $scope.selectItem = item;
            };
            
            $scope.$watch(function () {
                return JSON.stringify($scope.ngModel) + JSON.stringify($scope.items);
            }, function (new_value){
                angular.forEach($scope.items, function (one){
                   if (one[$scope.key] + '' == $scope.ngModel + ''){
                       $scope.selectItem = one;
                       $timeout(function (){
                          $scope.$apply(); 
                       });
                   } 
                });
            });
        }
        
    } 
});

