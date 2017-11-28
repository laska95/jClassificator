'use strict';

function JiraController($scope, $route, $http, $timeout, projectService,
        issueStatusService, issueService, jQueryLikeSerializeFixed) {

    var self = this;

    self.projectList = [];
    self.selectProject = undefined;

    self.issueStatusList = [];
    self.selectedIssueStatusList = [];

    self.issueList = [];
    self.selectedIssue = [];
    self.activeIssueKey = undefined;
    self.activeIssue = undefined;
    self.ifActiveIssue = function () {
        return (typeof self.activeIssueKey !== 'undefined');
    };
    self.testClick = function () {

        var params = {
            'project': 'BRAIN',
            // 'status': ['To Do', 'Backlog']
        };

        var p = issueService.getByFilter(params);
        p.then(function (data) {

        });
    };

    var getProjectList = function () {
        var p = projectService.get();
        p.then(function (data) {
            var arr = [];
            angular.forEach(data.data, function (data_one) {
                arr.push(data_one);
            });
            self.projectList = arr;
            self.selectProject = arr[0].key;

        });
    };

    var getIssueStatusList = function () {
        var p = issueStatusService.getAll();
        p.then(function (data) {
            var arr = [];
            self.selectedIssueStatusList = [];
            angular.forEach(data.data, function (data_one) {
                arr.push(data_one);
                self.selectedIssueStatusList.push(data_one.id);
            });
            self.issueStatusList = arr;
            $timeout(function () {
                $scope.$apply();
            });
        });
    };

    var getIssue = function (key) {
        var p = issueService.get(key);
        p.then(function (data) {
            self.oneIssue = data;
            $timeout(function () {
                $scope.$apply();
            });
        });
    };

    var getIssueList = function (params) {
        var p = issueService.getByFilter(params);
        p.then(function (response) {
            self.issueList = response.data.issues;
            $timeout(function () {
                $scope.$apply();
            });
        });
    };

    var findIssue = function (key) {
        var filter_fnc = function (one_issue, one_key) {
            return (one_issue.key == key);
        };
        var finds = self.issueList.filter(filter_fnc);
        return finds[0]; //issue or undefined
    };

    getProjectList();
    getIssueStatusList();

    $scope.$watch(function () {
        return JSON.stringify(self.selectedIssueStatusList) + self.selectProject;
    }, function (new_value) {
        //завантажуємо список задач згідно фільтру
        if (self.selectProject) {
            var params = {
                'project': self.selectProject,
                'status': self.selectedIssueStatusList
            };
            getIssueList(params);
        }

    });

    $scope.$watch(function () {
        return JSON.stringify(self.activeIssueKey);
    }, function (new_value) {
        if (typeof self.activeIssueKey === 'undefined') {
            self.activeIssue = undefined;
        } else {
            self.activeIssue = findIssue(self.activeIssueKey);
        }
    });

    self.selectedFilter = 'AD';
    self.selectedDoneFilter = '';

    self.resultAD = {};
    self.resultPC = {};
    self.resultLC = {};

    self.filterList = [
        {
            key: 'AD', //AvailabilityDescription
            label: 'Перевірка якісті опису',
        },
        {
            key: 'PC', //DecisionApiPriorityClusteringCtrl
            label: 'Класифікація за пріортетом',
        },
        {
            key: 'LC', //DecisionApiLinksClusteringCtrl
            label: 'Кластиризація за спільними ресурсами',
            url: '/decision/full-api/links-clustering'
        },
        {
            key: 'TC', //DecisionApiLinksClusteringCtrl
            label: 'Кластиризація за описом',
            url: '/decision/full-api/text-clustering'
        },
    ];

    self.applyFilter = function () {
        if (self.selectedFilter === 'AD') {
            adApply();
        } else if (self.selectedFilter === 'PC') {
            pcApply();
        } else if (self.selectedFilter === 'LC') {
            lcApply();
        }

    };

    var adApply = function () {
        var url = '/decision/full-api/availability-description';

        var issue_keys = [];
        angular.forEach(self.issueList, function (one) {
            issue_keys.push(one.key);
        });

        var post = {
            issue_arr: {},
            issue_key_arr: issue_keys,
        };

        $.ajax({
            type: 'POST',
            url: url,
            data: post,
            dataType: 'json'
        }).done(function (data) {
            self.resultAD = data;
            self.selectedDoneFilter = 'AD';
            $timeout(function () {
                $scope.$apply();
            });
        });
    };
    
    var pcApply = function () {
        var url = '/decision/full-api/priority-clustering';

        var issue_keys = [];
        angular.forEach(self.issueList, function (one) {
            issue_keys.push(one.key);
        });

        var post = {
            issue_arr: {},
            issue_key_arr: issue_keys,
        };

        $.ajax({
            type: 'POST',
            url: url,
            data: post,
            dataType: 'json'
        }).done(function (data) {
            self.resultPC = data;
            self.selectedDoneFilter = 'PC';
            $timeout(function () {
                $scope.$apply();
            });
        });
    };
    
    var lcApply = function () {
        var url = '/decision/full-api/availability-description';

        var issue_keys = [];
        angular.forEach(self.issueList, function (one) {
            issue_keys.push(one.key);
        });

        var post = {
            issue_arr: {},
            issue_key_arr: issue_keys,
        };

        $.ajax({
            type: 'POST',
            url: url,
            data: post,
            dataType: 'json'
        }).done(function (data) {
            console.log(data);
            self.resultAD = data;
            self.selectedDoneFilter = 'AD';
            $timeout(function () {
                $scope.$apply();
            });
        });
    };
}

angular.module('app.jira', ['ngRoute'])
        .controller('JiraController', JiraController);


