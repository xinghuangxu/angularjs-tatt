exports.config = {
  allScriptsTimeout: 11000,

  specs: [
    '*.js',
    'v*/*.js'
  ],

  capabilities: {
    'browserName': 'chrome'
  },

  baseUrl: 'http://localhost/rally-plugin/app/',

  framework: 'jasmine',

  jasmineNodeOpts: {
    defaultTimeoutInterval: 30000
  }
};
