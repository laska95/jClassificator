'use strict';

function JiraController($scope, $route, $http, $timeout, projectService, 
issueStatusService, issueService, jQueryLikeSerializeFixed){
    
    var self = this;
        
    self.projectList = [];
    self.selectProject = undefined;
       
    self.issueStatusList = [];
    self.selectedIssueStatusList = [];
    
    self.issueList = [];
    self.selectedIssue = [];
    self.activeIssueKey = undefined;
    self.activeIssue = undefined;
    
    self.testClick = function (){
        
        var params = {
            'project': 'BRAIN',
           // 'status': ['To Do', 'Backlog']
        };
        
        var p = issueService.getByFilter(params);
        p.then(function (data){
            console.log(data);
        }); 
//        $http.get('/jira/full-api/get-test', function (data){
//            console.log(data);
//        });
        
    };
    
    var getProjectList = function (){
        var p = projectService.get();
        p.then(function (data){
            var arr = [];         
            angular.forEach(data.data, function (data_one){
                 arr.push(data_one);
            });
            self.projectList = arr;
            self.selectProject = arr[0].key;
            
        }); 
    };
        
    var getIssueStatusList = function (){
        var p = issueStatusService.getAll();
        p.then(function (data){
            var arr = [];
            self.selectedIssueStatusList = [];
            angular.forEach(data.data, function (data_one){
                 arr.push(data_one);
                 self.selectedIssueStatusList.push(data_one.id);
            });
            self.issueStatusList = arr;
            $timeout(function (){
                $scope.$apply();
            });
        }); 
    };
    
    var getIssue = function (key){
        var p = issueService.get(key);
        p.then(function (data){
            self.oneIssue = data;
            $timeout(function (){
                $scope.$apply();
            });
        }); 
    };
    
    var getIssueList = function (params){
        var p = issueService.getByFilter(params);
        p.then(function (response){
            self.issueList = response.data.issues;
//            console.log(self.issueList);
            $timeout(function (){
                $scope.$apply();
            });
        }); 
    };
    
    var findIssue = function (key){        
        var filter_fnc = function (one_issue, one_key){
            return (one_issue.key == key);
        };
        var finds = self.issueList.filter(filter_fnc);
        return finds[0]; //issue or undefined
    };
    
    getProjectList();
    getIssueStatusList();
//    getIssueList();
        
    var params = {
        'project': 'BRAIN',
       // 'status': ['To Do', 'Backlog']
    };
    
    $scope.$watch(function (){
        return JSON.stringify(self.activeIssueKey);
    }, function (new_value){
        if (typeof self.activeIssueKey === 'undefined'){
            self.activeIssue = undefined;
        } else {
            self.activeIssue = findIssue(self.activeIssueKey);
        }
    });
    
    
}

angular.module('app.jira', ['ngRoute']).controller('JiraController', JiraController);


