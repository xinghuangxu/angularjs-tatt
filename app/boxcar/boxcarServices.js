boxcar.factory('boxcarDataService', function ($resource) {
    var boxcarDataService = {
        editInfo: {nodeID: null, name: null, archID: null, iteration: null, icon: null, blocked: null},
        addNode: {nodeID: null, name: null, archID: null, iteration: null, icon: null, blocked: null},
        selectedNode: {nodeID: null, children: null, name: null}
    };
    boxcarDataService.resource = $resource('php/boxcar.php', {},
            {
                children: {method: 'GET', params: {}, timeout: '60000', isArray: true}
            }
    );
    return boxcarDataService;
});

boxcar.factory('boxcarContainer', function () {
    var boxcarContainer = {};
    var prs = {};
    var iconPath = "assets/img/strategy";
    function Strategy(data) {
        this.id = data['tpid'];
        this.text = data['name'];
        this.icon = iconPath + data['type'];
        this.parent = data['parent'];
        this.qual = data['qual'];
        this.impact = data['impact'];
        this.ownership = data['ownership'];
        this.approach = data['approach'];
        this.attrs = {
            qual: data['qual'],
            impact: data['impact'],
            scope: data['scope'],
            risk: data['risk']
        };
    }
    Strategy.prototype.toTreeFormat = function (treeArray, classifier) {
        treeArray.push({
            id: this.id,
            parent: classifier ? (this.parent + this[classifier]) : this.parent,
            text: this.text,
            icon: this.icon
        });
    }
    function PR(data) {
        var that = this;
        this.id = data['id'];
        this.parent = "#";
        this.text = data['type'] + ":" + data['id'] + ":" + data['title'];
        this.icon = "";

        this.qual = {};
        this.impact = {};
        this.ownership = {};
        this.approach = {};

        this.strategies = {};
        this.addStrategy = function (data) {
            data['parent'] = that.id;
            that.strategies[data['tpid']] = new Strategy(data);
            that.qual[data['qual']] = true;
            that.impact[data['impact']] = true;
            that.ownership[data['ownership']] = true;
            that.approach[data['approach']] = true;
        };
    }
    PR.prototype.toTreeFormat = function (treeArray, classifier) {
        treeArray.push({
            id: this.id,
            parent: this.parent,
            text: this.text,
            icon: this.icon
        });
        if (classifier) {
            for (var prop in this[classifier]) {
                if (this[classifier].hasOwnProperty(prop)) {
                    treeArray.push({
                        id: this.id + prop,
                        parent: this.id,
                        text: prop,
                        icon: ""
                    });
                }
            }
        }
        for (var prop in this.strategies) {
            if (this.strategies.hasOwnProperty(prop)) {
                this.strategies[prop].toTreeFormat(treeArray, classifier);
            }
        }
    };

    function addChild(data) {
        if (!prs[data['id']]) {
            prs[data['id']] = new PR(data);
        }
        if (data['strategy']) {
            prs[data['id']].addStrategy(data['strategy']);
        }
    }

    boxcarContainer.toTreeFormat = function (classificaitonField) {
        var treeArray = [];
        for (var prop in prs) {
            if (prs.hasOwnProperty(prop)) {
                prs[prop].toTreeFormat(treeArray, classificaitonField);
            }
        }
        return treeArray;
    };

    boxcarContainer.create = function (source) {
        prs = {};
        for (var i = 0; i < source.length; i++) {
            addChild(source[i]);
        }
    };
    return boxcarContainer;
});


