'use strict';

// Declare app level module which depends on views, and components
angular.module('email', [
    'ngRoute',
    'email.builder'
]).config(['$locationProvider', '$routeProvider', function($locationProvider, $routeProvider) {
    $locationProvider.hashPrefix('!');
    $routeProvider.otherwise({redirectTo: '/'});
}]);