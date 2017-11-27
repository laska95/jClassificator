
agile.service('JiraIssuePriority', function ($timeout, $http){
    
    return {
        'get': function (){
            return $http({
                method: 'GET', 
                url: '/jira/full-api/get-issue-priority',
                data: {
                    
                }
            });
        }
    }
    
});

