describe('rally view', function () {

    beforeEach(function () {
        browser.get('index.html#/rally');
    });


    it('should load project list release/iteration lists', function () {
       var projectSel=element(by.model('projectChosen'));
       //get options of project
       expect(projectSel.all(by.css('option')).count()).toBeGreaterThan(0);
       projectSel.element(by.cssContainingText('option','Spark')).click();
       
       var releaseSel=element(by.model('releaseChosen'));
       expect(releaseSel.all(by.css('option')).count()).toBeGreaterThan(0);
       releaseSel.element(by.cssContainingText('option','Spark 2015 WSU')).click();
       
//       var treeModel = 
//       expect(element.all(by.css('.jstree-container-ul li')).count()).
//                    toBe(el.liCount);
    });



});


