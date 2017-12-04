agile.directive('issueAsTable', function () {
    
//    var linker = function($scope, element, attrs) {
////        var xprop = $scope.xprop;
////        var yprop = scope.yprop;
////        var title = scope.title;
//        var cluster = $scope.cluster;
//
//        $scope.$watch('cluster', function(newVal){
//            cluster = newVal;
//            console.log(newVal);
//            if( typeof cluster !== 'undefined' ) console.log(cluster + '**');
//        });
//    };
    
    return {
        restrict: 'E',

        templateUrl: "/ng-pages/other/issue-as-table.html",

        scope: {
            details: '=?',
                    issueKeyList: '=', //об'єкт задачі
            active: '=',
            'title': '@',
            'loadQuality': '=', //true/false
            total: '@'
        },
//        link: linker,
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
              
            $scope.count = (typeof $scope.issueKeyList === 'object') ?  Object.keys($scope.issueKeyList).length : 0;

            $scope.hasQuality = function (issue_key){
                var e = $scope.details[issue_key];
                
                if (typeof e === 'undefined'){
                    return false;
                }
                
                var q = e['quality']['value'];
                
                if (typeof q === 'undefined'){
                    return false;
                }
                return true;
            };

            $scope.getQuality = function (issue_key){
                return $scope.details[issue_key]['quality']['value'];
            };

        },

    };
});


