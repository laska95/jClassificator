agile.directive('checkboxList', function () {
    return {
        restrict: 'E',
        
        templateUrl: "/ng-pages/other/check-box-list.html",
        
        scope: {
            ngModel: '=',   //куди зберігати вибрані значення
            key: '@',       //ключ для вибору
            title: '@',     //значення, що виводиться
            items: '=?',    //масив для вибору
        },
        
        controller: function ($scope, $element, $attrs, $location, $timeout) {
            console.log('checkBoxList');
            
            $scope.toggleVal = function (item){
                console.log(item[$scope.key]);
                var i = $scope.ngModel.indexOf(item[$scope.key]);
                
                if (i < 0){
                    $scope.ngModel.push(item[$scope.key]);
                } else {
                    $scope.ngModel.splice(i, 1);
                }
                
            };
            
            $scope.isChecked = function (item){
                return ($scope.ngModel.indexOf(item[$scope.key]) >= 0);
            }
        },
                
    } 
});


