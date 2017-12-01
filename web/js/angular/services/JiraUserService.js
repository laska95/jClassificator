
agile.service('JiraUserService', function ($timeout, $http){
    
    return {
        'get': function (){
            return $http({
                method: 'GET', 
                url: '/jira/full-api/get-self',
                data: {
                    
                }
            });
        }
    }
    
});

