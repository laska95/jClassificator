
agile.service('JiraUserService', function ($timeout){
    
    return {
        'get': function (){
            return $http({
                method: 'GET', 
                url: '/jira/full-api/get-issue',
                data: {
                    
                }
            });
        }
    }
    
});

