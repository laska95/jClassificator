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
        jql: "project in ('PR-2', 'PR-5')",
        issue_key_arr: ['PR-8', ''],
        text: "Частотна характеристика мови проекту використовується для аналізу якості описаної задачі. Через API можна створити нову частотну характеристику, переглянути чи видалити існуючу. Запит повинен включати ключ проекту та дані для отримання опису задач, на основі яких буде створена частотна характеристика. Якщо ідентифікаційні дані не вказані, метод поверне частотну характеристику вказаного тексту, без його збереження в БД. Детальна інформація про параметри запиту наведена в таблиці 3.4."
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
                key: 'PR-10',
                description: 'Ознайомитися з API',
            },
            2: {
                key: 'PR-11',
                description: 'gf hgf hgf hv j jbbbbbvv hg',
            }
        },
        jql: "project in ('PR-2', 'PR-5')",
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
    
    self.url = '/decision/full-api/availability-description';
    
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
                key: 'PR-1',
                description: 'Ознайомитися з API',
                summary: 'Задача 1'
            },
            2: {
                key: 'PR-2',
                description: '...',
                summary: 'Задача 2'
            }
        },
        issue_key_arr: ['BRAIN-2065', 'BRAIN-2068'],

    };
    self.post_params_json = '';
    
    
    $scope.$watch(function (){
        return JSON.stringify(self.post_params);
    }, function (new_value){
        self.post_params_json = angular.toJson(self.post_params, true);
    });
}

function DecisionApiPriorityClusteringCtrl($scope, $route, $http, $timeout, JiraIssuePriority){
    var self = this;

    self.priorityList = [];

    var getIssuePriorityList = function (){
        var p = JiraIssuePriority.get();
        p.then(function (data){
            var arr = [];         
            angular.forEach(data.data, function (data_one){
                 arr.push(data_one);
            });
            self.priorityList = arr;    
            $timeout(function (){
                $scope.$apply();
            });
        }); 
    };

    getIssuePriorityList();

    self.post_response = {};
    self.post_response_json = '';
    
    self.url = '/decision/full-api/priority-clustering';
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
                key: 'PR-1',
                priority_id: '2',
                duedate: '2017-11-27T18:20:12',
                remainingEstimateSeconds: 600,
            },
            2: {
                key: 'PR-2',
                priority_id: '3',
                duedate: '2017-10-27T18:20:12',
                remainingEstimateSeconds: -300,
            }
        },
        issue_key_arr: ['BRAIN-2065', 'BRAIN-2068'],
    };
    self.post_params_json = '';
    
    
    $scope.$watch(function (){
        return JSON.stringify(self.post_params);
    }, function (new_value){
        self.post_params_json = angular.toJson(self.post_params, true);
    });
}

function DecisionApiLinksClusteringCtrl($scope, $route, $http, $timeout){
    var self = this;

    self.url = '/decision/full-api/links-clustering';

    self.post_response = {};
    self.post_response_json = '';
    
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
                key: 'PR-1',
                description: 'Ознайомитися з API',
            },
            2: {
                key: 'PR-2',
                description: 'url  http://agile2.loc/decision/full-api/priority-clustering',
            }
        },
        issue_key_arr: ['BRAIN-2065', 'BRAIN-2068'],
    };
    self.post_params_json = '';
    
    
    $scope.$watch(function (){
        return JSON.stringify(self.post_params);
    }, function (new_value){
        self.post_params_json = angular.toJson(self.post_params, true);
    });
}

function DecisionApiTextClusteringCtrl($scope, $route, $http, $timeout){
    var self = this;

    self.url = '/decision/full-api/text-clustering';

    self.post_response = {};
    self.post_response_json = '';
    
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
                'key': 'PR-1',
                summary: 'Швидкість',
                description: 'Швикість — фізична величина, що відповідає відношенню переміщення тіла до проміжку часу, за який це переміщення відбувалось. Швидкість — величина векторна, тобто вона має абсолютну величину і напрямок. Швидкість, як векторна величина здебільшого позначається літерою {\displaystyle \mathbf {v} }  \mathbf{v}  або {\displaystyle {\vec {v}}} {\displaystyle {\vec {v}}}, а коли йде мова тільки про кількісне значення швидкості — {\displaystyle v} {\displaystyle v} (від лат.Velocitas — швидкість). У системі СІ швидкість (точніше її абсолютна величина) вимірюється в метрах за секунду — м / с.В системі СГС одиницею вимірювання швидкості є сантиметр за секунду — см / с.В повсякденному житті найпрактичнішою одиницею вимірювання швидкості є кілометр на годину — км / год.В певних областях людської діяльності чи країнах використовуються специфічні одиниці швидкості, як, наприклад, вузол чи фут на секунду.'
            },
            2: {
                'key': 'PR-2',
                summary: 'Прискорення',
                description: 'Приско́рення  — векторна фізична величина, похідна швидкості по часу і за величиною дорівнює зміні швидкості тіла за одиницю часу. Прискорення, як векторна величина здебільшого позначається літерою {\displaystyle \mathbf {a} } {\mathbf  {a}} або {\displaystyle {\vec {a}}} {\displaystyle {\vec {a}}}, а коли йдеться лише про кількісне значення прискорення — a (від лат. acceleratio — прискорення)                      Часто у фізиці для позначення прискорення використовують дві крапки над позначенням координати чи радіуса - вектора, або одну крапку над символом швидкості:'
            },
            3: {
                'key': 'PR-3',
                summary: 'Риби',
                description: 'Ри́би (Pisces) — парафілетична група водних хребетних тварин, зазвичай холоднокровних (точніше екзотермних) із вкритим лусками тілом та зябрами, наявними протягом всього життя. Активно рухаються за допомогою плавців (часто видозмінених) або руху всього тіла. Риби поширені як у морських, так і в прісноводних середовищах, від глибоких океанічних западин до гірських струмків. Риби мають велике значення для всіх водних екосистем як складова частина харчових ланцюгів та велике економічне значення для людини через споживання їх у їжу. Люди як виловлюють диких риб, так і розводять їх у створених з цією метою господарствах.'
            },
            4: {
                'key': 'PR-4',
                summary: 'Планктон',
                description: 'Планктон складається з різних видів бактерій (бактеріопланктон), деяких водоростей (фітопланктон), найпростіших, молюсків, ракоподібних, личинок риб та багатьох безхребетних (зоопланктон). Слугує безпосередньо або через трофічні ланцюги їжею для інших тварин, які живуть у водоймі. Продуктивність планктону залежить від комплексу різних факторів. Наприклад, як от: світловий, температурний, хімічний режим, а також антропогенного впливу. Планктонні організми трапляються на всіх глибинах, що пов\'язано з рухом водних мас, разом з тим їх найбільше біля поверхні води.'
            },
            5: {
                'key': 'PR-5',
                summary: 'Система автоматизованого проектування і розрахунку',
                description: 'Система автоматизо́ваного проектуваня (САП або САПР) або автоматизо́вана систе́ма проектува́ння (АСП) — автоматизована система, призначена для автоматизації технологічного процесу проектування виробу, результатом якого є комплект проектно-конструкторської документації, достатньої для виготовлення та подальшої експлуатації об\'єкта проектування[1].Реалізується на базі спеціального програмного забезпечення, автоматизованих банків даних, широкого набору периферійних пристроїв.'
            },
        },
        issue_key_arr: ['PR-8', ''],
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
        .controller('DecisionApiPriorityClusteringCtrl', DecisionApiPriorityClusteringCtrl)
        .controller('DecisionApiLinksClusteringCtrl', DecisionApiLinksClusteringCtrl)
        .controller('DecisionApiTextClusteringCtrl', DecisionApiTextClusteringCtrl)
        .controller('DecisionApiSearchSimilarCtrl', DecisionApiSearchSimilarCtrl)
        .run(function ($rootScope, $location, $anchorScroll, $routeParams){
            $rootScope.$on('$routeChangeSuccess', function(newRoute, oldRoute) {
                $location.hash($routeParams.scrollTo);
                $anchorScroll();  
              });
});



