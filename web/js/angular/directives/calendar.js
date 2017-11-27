agile.directive('calendar', function ($filter) {
    return {
        require: 'ngModel',
        link: function (scope, el, attr, ngModel) { 
            
            var date_format = 'dd.mm.yy';
            //'2017-11-27T18:20:12',
           
            ngModel.$parsers.push(function(value){
                if (!value) return null;  
                return moment(value, 'DD.MM.YYYY').format('YYYY-MM-DDTHH:mm:ss');
                return $filter('date')(value, moment_date_format);
            });
           
            //відображення дати
            ngModel.$formatters.unshift(function (value) {
                if (!value) return "";               
                return  $filter('date')(value, date_format);
            });

            $(el).datepicker({
                weekStart: 1,
                firstDay: 1,
                dateFormat: date_format,
                onSelect: function (dateText) {
                    scope.$apply(function () {
                        ngModel.$setViewValue(dateText);
                    });
                }
            });

        }
    };
});