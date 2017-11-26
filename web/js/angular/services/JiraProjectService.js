agile.service('projectService', function ($http) { 
    return {
        'get' : function(){
            return $http({method: 'GET', url: '/jira/full-api/get-project-list'});
        }
    };
});   


