agile.directive('issueAsList', function () {
    return {
        restrict: 'E',
        
        templateUrl: "/ng-pages/other/issue-as-list.html",
        
        scope: {
            issue: '=',       //об'єкт задачі
            selectedArr: '=',   //якщо забача вибрана, то в цей масив записується її ключ
            active: '=',
        },
        
        controller: function ($scope, $element, $attrs, $location, $timeout) {
              
            $scope.select = false;
                        
            $scope.toggleSelect = function (){
                console.log('toggleSelect -> ' + $scope.select);
                //$scope.select - значення після кліку 
                var i = $scope.selectedArr.indexOf($scope.issue.key);
                if ($scope.select){
                    if (i < 0){//add to array
                         $scope.selectedArr.push($scope.issue.key);
                    }
                } else {
                    if (i > -1){//remove from array
                        $scope.selectedArr.splice(i, 1);
                    }
                }                
            };
            
            $scope.toggleActive = function (){
                
                if ($scope.active == $scope.issue.key){
                    $scope.active = undefined;
                } else {
                    $scope.active = $scope.issue.key;
                }                
            };
            
        },
                
    }; 
});


