agile.service('issueService', function ($timeout, $http, jQueryLikeSerializeFixed){
    return {
        get: function (key){
            return $http({method: 'GET', url: '/jira/full-api/get-issue?key=' + key});
        },
        getByFilter: function (params){
            var base_param_keys = [
                'project', 'status', 'startAt'
            ];
                        
            var q = jQueryLikeSerializeFixed(params);
            return $http({method: 'GET', url: '/jira/full-api/get-issue-list?' + q});
        }
    };
});


