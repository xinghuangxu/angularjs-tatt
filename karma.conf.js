module.exports = function (config) {
    config.set({
        basePath: './',
        files: [
            'app/bower_components/angular/angular.js',
            'app/bower_components/angular-route/angular-route.js',
            'app/bower_components/angular-mocks/angular-mocks.js',
            'app/bower_components/jquery/jquery.min.js',
            'app/bower_components/angular-animate/angular-animate.min.js',
            'app/bower_components/angular-sanitize/angular-sanitize.min.js',
            'app/bower_components/angular-resource/angular-resource.min.js',
            'app/bower_components/bootstrap/dist/js/bootstrap.min.js',
            'app/bower_components/angular-bootstrap-colorpicker/js/bootstrap-colorpicker-module.js',
            'app/bower_components/angular-wysiwyg/angular-wysiwyg.js',
            'app/bower_components/jstree/dist/jstree.min.js',
            'app/bower_components/angular-strap/dist/angular-strap.min.js',
            'app/bower_components/angular-strap/dist/angular-strap.tpl.min.js',
            'app/bower_components/jquery-ui/jquery-ui.js',
            'app/components/**/*.js',
            'app/components/**/**/*.js',
            'app/view*/**/*.js'
        ],
        
        autoWatch: true,
        frameworks: ['jasmine'],
        browsers: ['Chrome'],
        plugins: [
            'karma-chrome-launcher',
            'karma-firefox-launcher',
            'karma-jasmine',
            'karma-junit-reporter'
        ],
        junitReporter: {
            outputFile: 'test_out/unit.xml',
            suite: 'unit'
        }

    });
};
