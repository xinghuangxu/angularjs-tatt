describe('boxcar tree view', function () {

    beforeEach(function () {
        browser.get('index.html#/boxcar');
    });


    it('should filer tree elements by using the search key', function () {
        var searchKey = element.all(by.model('treeSearchKey'));
        searchKey.sendKeys('support');
        expect(element.all(by.css('.jstree-container-ul li')).count()).
                toBe(19);
        searchKey.clear().then(function () {
            searchKey.sendKeys('other');
            expect(element.all(by.css('.jstree-container-ul li')).count()).
                    toBe(19);
        });
    });


    it('should arrange by the selected option', function () {
        var listOfTexts = [
            {
                text: "PR",
                liCount: 8
            },
            {
                text: "Qualification Area",
                liCount: 6
            },
            {
                text: "Impact Area",
                liCount: 4
            },
            {
                text: "Approach",
                liCount: 4
            },
            {
                text: "Ownership",
                liCount: 2
            }
        ];
        for (var i in listOfTexts) {
            var el = listOfTexts[i];
            element(by.cssContainingText('option', el.text)).click();
            expect(element.all(by.css('.jstree-container-ul li')).count()).
                    toBe(el.liCount);
        }
    });

});


