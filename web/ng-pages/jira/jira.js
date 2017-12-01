'use strict';

function JiraController($scope, $route, $http, $timeout, projectService,
        issueStatusService, issueService, jQueryLikeSerializeFixed, JiraUserService, issueTypeService) {

    var self = this;

    self.user = {};

    self.projectList = [];
    self.selectProject = undefined;
    self.lang_code = 'project';
    self.issueStatusList = [];
    self.selectedIssueStatusList = [];

    self.issueTypeList = [];
    self.selectedIssueTypeList = [];

    self.issueList = [];
    self.selectedIssue = [];
    self.responseGetIssues = {};
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

    var getUser = function () {
        var p = JiraUserService.get();
        p.then(function (data) {
            self.user = data.data;
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

    var getIssueTypeList = function () {
        var p = issueTypeService.getAll();
        p.then(function (data) {
            var arr = [];
            self.selectedIssueTypeList = [];
            angular.forEach(data.data, function (data_one) {
                arr.push(data_one);
                self.selectedIssueTypeList.push(data_one.id);
            });
            self.issueTypeList = arr;
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
            self.responseGetIssues = response.data;
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

    getUser();
    getProjectList();
    getIssueStatusList();
    getIssueTypeList();

     self.jql = '';

    $scope.$watch(function () {
        return JSON.stringify(self.selectedIssueStatusList) 
                + JSON.stringify(self.selectedIssueTypeList) 
                + self.selectProject;
    }, function (new_value) {
        //завантажуємо список задач згідно фільтру
        if (self.selectProject) {
            var params = {
                'project': self.selectProject,
                'status': self.selectedIssueStatusList
            };
            getIssueList(params);
             self.jql = getJQL();
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
    self.selectedFilter1 = 'AD';
    self.selectedFilter2 = 'AD';
    self.selectedDoneFilter = '';
    self.olapApply = true;

    self.resultAD = {};
    self.resultPC = {};
    self.resultLC = {};
    self.resultTC = {};

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
        },
        {
            key: 'TC', //DecisionApiLinksClusteringCtrl
            label: 'Кластиризація за описом',
        },
    ];

    self.applyFilter = function () {
        if (self.selectedFilter === 'AD') {
            adApply();
        } else if (self.selectedFilter === 'PC') {
            pcApply();
        } else if (self.selectedFilter === 'LC') {
            lcApply();
        } else if (self.selectedFilter === 'TC') {
            tcApply();
        }

    };

    self.olapTriger = true;
    self.olapParams = [];
    self.applyFilter2 = function () {
        var issue_keys = [];
        angular.forEach(self.issueList, function (one) {
            issue_keys.push(one.key);
        });
        self.olapParams = issue_keys;
        self.olapTriger = !self.olapTriger;
        self.selectedDoneFilter = 'OLAP';
    };

    var getJQL = function () {
        var jql = '(project="' + self.selectProject + '")';

        if (self.selectedIssueStatusList.length > 0) {
            var statusArr = [];
            angular.forEach(self.selectedIssueStatusList, function (one_status) {
                statusArr.push('"' + one_status + '"');
            });
            jql += 'AND (status IN (' + statusArr.join() + '))';
        }

        if (self.selectedIssueTypeList.length > 0) {
            var typeArr = [];
            angular.forEach(self.selectedIssueTypeList, function (one_status) {
                typeArr.push('"' + one_status + '"');
            });
            jql += 'AND (issuetype IN (' + typeArr.join() + '))';
        }
        return jql;
    };

    var adApply = function () {
        var url = '/decision/full-api/availability-description';

        var issue_keys = [];
        angular.forEach(self.issueList, function (one) {
            issue_keys.push(one.key);
        });

        var post = {
            issue_arr: {},
//            issue_key_arr: issue_keys,
            jql: self.jql,
            lang_code: self.lang_code,
            project_code: self.selectProject
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
//            issue_key_arr: issue_keys,
            jql: self.jql,
            lang_code: self.lang_code,
            project_code: self.selectProject
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
        var url = '/decision/full-api/links-clustering';

        var issue_keys = [];
        angular.forEach(self.issueList, function (one) {
            issue_keys.push(one.key);
        });

        var post = {
            issue_arr: {},
//            issue_key_arr: issue_keys,
            jql: self.jql,
            lang_code: self.lang_code,
            project_code: self.selectProject
        };

        $.ajax({
            type: 'POST',
            url: url,
            data: post,
            dataType: 'json',
            lang_code: self.lang_code,
            project_code: self.selectProject
        }).done(function (data) {
            self.resultLC = data;
            self.selectedDoneFilter = 'LC';

            $timeout(function () {
                $scope.$apply();
            });
        });
    };

    var tcApply = function () {
        var url = '/decision/full-api/text-clustering';

        var issue_keys = [];
        angular.forEach(self.issueList, function (one) {
            issue_keys.push(one.key);
        });

        var post = {
            issue_arr: {},
//            issue_key_arr: issue_keys,
            lang_code: self.lang_code,
            jql: self.jql,
            project_code: self.selectProject
        };

        $.ajax({
            type: 'POST',
            url: url,
            data: post,
            dataType: 'json'
        }).done(function (data) {
            console.log(data);
            self.resultTC = data;
            self.selectedDoneFilter = 'TC';
            $timeout(function () {
                $scope.$apply();
            });
        });
    };

    self.getTotalRet = function (ret) {
        var total = 0;
        angular.forEach(ret, function (one) {
            total += one.items.length;
        });
        return total;
    };
}

angular.module('app.jira', ['ngRoute'])
        .controller('JiraController', JiraController);


