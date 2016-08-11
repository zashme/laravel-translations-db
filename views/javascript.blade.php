<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.3.16/angular.min.js"></script>
<script>
    jQuery.noConflict();

    var app = angular.module('trans', [], function($interpolateProvider) {
        $interpolateProvider.startSymbol('[[');
        $interpolateProvider.endSymbol(']]');
    });

    app.controller('Translations', ['$scope', '$http', function($scope, $http) {
        $scope.setMessage = function(c,t) {
            $scope.message = {
                'type': t,
                'text': c
            };
        };

        $scope.clear = function() {
            $scope.items = [];
        };

        $scope.translateResult = {'total': 0, 'loading': 0, 'errors': 0, 'skip': 0, 'success': 0};

        $scope.translate = function(text, key, success, error) {
            $http.post("{{ URL::route('translations.translate') }}", {
                'key': key,
                'origin': $scope.currentLocale,
                'target': $scope.currentEditable,
                'text': text
            }).success(success)
            .error(error);
        };

        $scope.delete = function($index) {
            $http.post("{{ URL::route('translations.delete') }}", {
                'name': $scope.items[$index].name
            }).success(function() {
                $scope.items.splice($index, 1);
            });
        };

        $scope.fetch = function() {
            $http.post("{{ URL::route('translations.items') }}", {
                'group': $scope.currentGroup,
                'locale': $scope.currentLocale,
                'translate': $scope.currentEditable
            }).success(function(data) {
                $scope.items = data;
            })
            .error(function(data, status, headers, config) {
                $scope.setMessage(status, 'danger');
            });
        };

        $scope.store = function($index) {
            $http.post("{{ URL::route('translations.store') }}", {
                'name': $scope.items[$index].name,
                'value': $scope.items[$index].translation,
                'locale': $scope.currentEditable,
                'group': $scope.currentGroup
            })
            .error(function(data, status, headers, config) {
                $scope.setMessage(status, 'danger');
            });
            $scope.items[$index].check = false;
        };

        $scope.locales = [];
        $scope.groups = [];
        $scope.currentLocale = null;
        $scope.currentGroup = null;
        $scope.currentEditable = null;
        $scope.items = [];
        $scope.message = null;
        $scope.showEmptyOnly = false;

        $http.get("{{ URL::route('translations.locales') }}").success(function(data) {
            $scope.locales = data;
        }).error(function(data, status, headers, config) {
            $scope.setMessage(status, 'danger');
        });

        $http.get("{{ URL::route('translations.groups') }}").success(function(data) {
            $scope.groups = data;
        }).error(function(data, status, headers, config) {
            $scope.setMessage(status, 'danger');
        });
    }])
</script>
