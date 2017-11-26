agile.directive('issueDetailView', function () {
    return {
        restrict: 'E',
        
        templateUrl: "/ng-pages/other/issue-detail-view.html",
        
        scope: {
            issue: '=',       //об'єкт задачі
//            selectedArr: '=',   //якщо забача вибрана, то в цей масив записується її ключ
            active: '=',
        },
        
        controller: function ($scope, $element, $attrs, $location, $timeout) {
            
            $scope.selfIssue = undefined;
            $scope.showClass = 'ng-hide';
            
            //TO DO: підвантажувати зображення, замість того, щоб їх приховувати
            var imageLinkReplace = function (text){
                var ret = '';
                try {
                    var pattern = /!([\w\W]+)\|thumbnail!/;
                    ret = text.replace(pattern, '');
                } catch (err) {
                    //text is NULL
                    ret = text;
                }
                return ret;
            };
            
            $scope.toggleClose = function (){
                $scope.active = undefined;
            };
            
            $scope.updateIssue = function (){
                console.log('upp');
            };
            
            $scope.getJiraLink = function (){
                var ret = '';
                try {
                    var pattern = /(rest\/api\/[\w\W]+)$/;
                    var home_url = $scope.issue.self.replace(pattern, '');
                    ret = home_url + '/browse/' + $scope.issue.key;
                } catch (err) {
                    //$scope.issue.self is NULL
                }
                return ret;              
            };
            
            $scope.cleanText = function (text){
                var ret = imageLinkReplace(text);
                return ret;
            };
            
            $scope.isEmptyStr = function(value){
                return ! ((typeof value === 'string') && (value.length > 0));  
            };
            
            $scope.toLocalTime = function (time){
                var d = Date.parse(time);
                return d;
            };
            
            $scope.$watch(function (){
                return JSON.stringify($scope.active);
            }, function (new_value){
                if (typeof $scope.active === 'undefined'){
                    $scope.showClass = 'ng-hide';
                } else {
                    $scope.showClass = 'ng-show';
                }
            });
            
        },
                
    }; 
});


