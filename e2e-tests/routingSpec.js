'use strict';

/* https://github.com/angular/protractor/blob/master/docs/toc.md */

describe('spark routing', function() {

  browser.get('index.html');

  it('should automatically redirect to /doc when location hash/fragment is empty', function() {
    expect(browser.getLocationAbsUrl()).toMatch("/doc");
  });


  describe('rally view', function() {

    beforeEach(function() {
      browser.get('index.html#/rally');
    });


    it('should render view1 when user navigates to /rally', function() {
      expect(element.all(by.css('[ng-view] label')).first().getText()).
        toMatch(/Project Name:/);
    });

  });


  describe('boxcar view', function() {

    beforeEach(function() {
      browser.get('index.html#/boxcar');
    });


    it('should render view2 when user navigates to /boxcar', function() {
      expect(element.all(by.css('[ng-view] label')).first().getText()).
        toMatch(/Arrange By:/);
    });

  });
});
