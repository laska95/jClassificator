'use strict';

function DecisionApiProjactLangCtrl($scope, $route, $http, $timeout){
    var self = this;
    
    self.url = '/decision/full-api/project-lang';
    self.get_project_key = '';
    self.get_response = {};
    self.get_response_json = '';
    
    self.get_subbmit = function (){
        
        $.ajax({
            type: 'GET',
            url: self.url,
            beforeSend: function(xhrObj) {
                xhrObj.setRequestHeader("Agile-Api-Key-Header", $scope.api_key);
              },
            data: {'project_key': self.get_project_key},
            dataType: 'json'
        }).done(function(data) { 
           self.get_response_json = angular.toJson(data, true);
           $timeout(function (){
              $scope.$apply(); 
           });
        });        
    };
    
    self.post_project_key = '';
    self.post_params = {
        issue_arr: {
            1: {
                description: 'Ознайомитися з API',
            },
            2: {
                description: '',
            }
        },
        issue_key_arr: ['PR-8', ''],
    };
    self.post_params_json = '';
    
    self.post_response = {};
    self.post_response_json = '';
    
    self.post_subbmit = function (){
        
        $.ajax({
            type: 'POST',
            url: self.url + '?project_key=' + self.post_project_key,
            beforeSend: function(xhrObj) {
                xhrObj.setRequestHeader("Agile-Api-Key-Header", $scope.api_key);
              },
            data: self.post_params,
            dataType: 'json'
        }).done(function(data) { 
           self.post_response_json = angular.toJson(data, true);
           $timeout(function (){
              $scope.$apply(); 
           });
        });        
    };
    
    
    self.delete_project_key = '';
    
    $scope.$watch(function (){
        return JSON.stringify(self.post_params);
    }, function (new_value){
        self.post_params_json = angular.toJson(self.post_params, true);
    });
}

function DecisionApiIssueQualityCtrl($scope, $route, $http, $timeout){
    var self = this;

    self.post_response = {};
    self.post_response_json = '';
    
    self.url = '/decision/full-api/text-quality';
    
    self.post_subbmit = function (){
        $.ajax({
            type: 'POST',
            url: self.url,
            beforeSend: function(xhrObj) {
                xhrObj.setRequestHeader("Agile-Api-Key-Header", $scope.api_key);
            },
            data: self.post_params,
            dataType: 'json'
        }).done(function(data) { 
           self.post_response_json = angular.toJson(data, true);
           $timeout(function (){
              $scope.$apply(); 
           });
        });  
    };
    
    self.post_params = {
        issue_arr: {
            1: {
                description: 'Ознайомитися з API',
            },
            2: {
                description: '',
            }
        },
        issue_key_arr: ['PR-8', ''],
        lang_code: 'ua-UA',
        project_code: ''
    };
    self.post_params_json = '';
    
    
    $scope.$watch(function (){
        return JSON.stringify(self.post_params);
    }, function (new_value){
        self.post_params_json = angular.toJson(self.post_params, true);
    });
}

function DecisionApiAvailabilityDescriptionCtrl($scope, $route, $http, $timeout){
    var self = this;

    self.post_response = {};
    self.post_response_json = '';
    
    self.post_subbmit = function (){
        
    };
    
    self.post_params = {
        issue_arr: {
            1: {
                description: 'Ознайомитися з API',
            },
            2: {
                description: '',
            }
        },
        issue_key_arr: ['PR-8', ''],
        lang_code: 'ua-UA',
        project_code: ''
    };
    self.post_params_json = '';
    
    
    $scope.$watch(function (){
        return JSON.stringify(self.post_params);
    }, function (new_value){
        self.post_params_json = angular.toJson(self.post_params, true);
    });
}

function DecisionApiRecountingPriorityCtrl($scope, $route, $http, $timeout){
    var self = this;

    self.post_response = {};
    self.post_response_json = '';
    
    self.post_subbmit = function (){
        
    };
    
    self.post_params = {
        issue_arr: {
            1: {
                description: 'Ознайомитися з API',
            },
            2: {
                description: '',
            }
        },
        issue_key_arr: ['PR-8', ''],
        lang_code: 'ua-UA',
        project_code: ''
    };
    self.post_params_json = '';
    
    
    $scope.$watch(function (){
        return JSON.stringify(self.post_params);
    }, function (new_value){
        self.post_params_json = angular.toJson(self.post_params, true);
    });
}

function DecisionApiGroupingByLinksCtrl($scope, $route, $http, $timeout){
    var self = this;

    self.post_response = {};
    self.post_response_json = '';
    
    self.post_subbmit = function (){
        
    };
    
    self.post_params = {
        issue_arr: {
            1: {
                description: 'Ознайомитися з API',
            },
            2: {
                description: '',
            }
        },
        issue_key_arr: ['PR-8', ''],
        lang_code: 'ua-UA',
        project_code: ''
    };
    self.post_params_json = '';
    
    
    $scope.$watch(function (){
        return JSON.stringify(self.post_params);
    }, function (new_value){
        self.post_params_json = angular.toJson(self.post_params, true);
    });
}

function DecisionApiClusteringByDescriptionCtrl($scope, $route, $http, $timeout){
    var self = this;

    self.post_response = {};
    self.post_response_json = '';
    
    self.post_subbmit = function (){
        
    };
    
    self.post_params = {
        issue_arr: {
            1: {
                description: 'Ознайомитися з API',
            },
            2: {
                description: '',
            }
        },
        issue_key_arr: ['PR-8', ''],
        lang_code: 'ua-UA',
        project_code: ''
    };
    self.post_params_json = '';
    
    
    $scope.$watch(function (){
        return JSON.stringify(self.post_params);
    }, function (new_value){
        self.post_params_json = angular.toJson(self.post_params, true);
    });
}

function DecisionApiSearchSimilarCtrl($scope, $route, $http, $timeout){
    var self = this;

    self.post_response = {};
    self.post_response_json = '';
    
    self.post_subbmit = function (){
        
    };
    
    self.post_params = {
        issue_arr: {
            1: {
                description: 'Ознайомитися з API',
            },
            2: {
                description: '',
            }
        },
        issue_key_arr: ['PR-8', ''],
        lang_code: 'ua-UA',
        project_code: ''
    };
    self.post_params_json = '';
    
    
    $scope.$watch(function (){
        return JSON.stringify(self.post_params);
    }, function (new_value){
        self.post_params_json = angular.toJson(self.post_params, true);
    });
}


function DecisionApiController($scope, $route, $http, $timeout){
    var self = this;
    self.api_key  = '';
    
    /*Створення алфавіту*/
    
    self.a0_abc_f = {};
    self.a0_project_key = '';
    self.a0_get_response = '';
    self.a0_post_response = '';
    
    self.a0_issues = {
        1: {
            description: '',
        },
        2: {
            description: ''
        }
    };
    
    /*Аналіз якості опису задачі*/
    
    self.a1_issue = {
        description: ''
    };
    
    self.a1_lang = {
        lang_code: 'ua-UA',
        project_code: '',
    };
    
    self.a1_post_data = '';
    self.a1_request = '';
    
    self.a1_subbmit = function (){       
        var data = {
            issue: self.a1_issue,
            property: self.a1_lang,
        };
        
        self.a1_post_data = angular.toJson(data, true);
        
        $.post( "/decision/full-api/text-quality", data, function (request){
                    console.log(request);
                    self.a1_request = angular.toJson(request, true);
                     console.log(angular.toJson(request, true));
                    
                    $timeout(function (){
                        $scope.$apply();
                    });
                });
    };
    
}

angular.module('app.decisionApi', ['ngRoute'])
        .controller('DecisionApiProjactLangCtrl', DecisionApiProjactLangCtrl)
        .controller('DecisionApiIssueQualityCtrl', DecisionApiIssueQualityCtrl)
        .controller('DecisionApiAvailabilityDescriptionCtrl', DecisionApiAvailabilityDescriptionCtrl)
        .controller('DecisionApiRecountingPriorityCtrl', DecisionApiRecountingPriorityCtrl)
        .controller('DecisionApiGroupingByLinksCtrl', DecisionApiGroupingByLinksCtrl)
        .controller('DecisionApiClusteringByDescriptionCtrl', DecisionApiClusteringByDescriptionCtrl)
        .controller('DecisionApiSearchSimilarCtrl', DecisionApiSearchSimilarCtrl)
        .run(function ($rootScope, $location, $anchorScroll, $routeParams){
            $rootScope.$on('$routeChangeSuccess', function(newRoute, oldRoute) {
                $location.hash($routeParams.scrollTo);
                $anchorScroll();  
              });
});



