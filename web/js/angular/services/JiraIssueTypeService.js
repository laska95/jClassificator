agile.service('issueTypeService', function ($http){
    return {
        getAll: function (){
            return $http({method: 'GET', url: '/jira/full-api/get-issue-type-list'});
        },
    }
});