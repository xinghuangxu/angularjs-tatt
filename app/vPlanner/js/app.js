/**
 * Application configuration and setup for Spark test planning module
 * 
 * @author Randall Crock
 * @copyright 2014 NetApp, Inc.
 * @date 2014-06-27
 * 
 */

var testPlanning = angular.module('spark.testPlanning', ['ngRoute', 'ngAnimate', 'ngSanitize', 'ngResource', 'controllers', 'mgcrea.ngStrap', 'ui.sortable', 'spark.ui']);

// Do configuration
testPlanning.config(
    function($routeProvider, $controllerProvider)
    {
        // Store the $controllerProvider for later registration of controllers
        testPlanning.controllerProvider = $controllerProvider;
    }
);