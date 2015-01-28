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
    var boxcarData = {};
    var fileterList = ['qual', 'impact', 'ownership', 'approach'];
    var iconPath = "assets/img/strategy";
    function Leaf(data) {
        this.id = data['tpid'];
        this.text = data['name'];
        this.icon = iconPath + data['type'];
        this.parent = data['parent'];
        this.qual = data['qual'];
        this.impact = data['impact'];
        this.ownership = data['ownership'];
        this.approach = data['approach'];
        this.attrs = {
            Qual: data['qual'],
            Impact: data['impact'],
            Ownership: data['ownership'],
            Scope: data['scope'],
            Risk: data['risk']
        };
    }
    Leaf.prototype.toTreeFormat = function (treeHash, parent) {
        var id = parent.id + this.id;
        treeHash[id] = {
            id: id,
            parent: parent.id,
            text: this.text,
            icon: this.icon
        };
        for (var prop in this.attrs) {
            if (this.attrs.hasOwnProperty(prop)) {
                treeHash[id + prop] = {
                    id: id + prop,
                    parent: id,
                    text: prop + ": " + this.attrs[prop],
                    icon: iconPath + "Attribute"
                };
            }
        }
    };
    function Node(data) {
        var that = this;
        this.id = data['id'];
        this.parent = "#";
        this.text = data['text'];
        this.icon = "";

        this.leafs = {};
        this.add = function (leaf) {
            that.leafs[leaf['id']] = leaf;
        };
    }
    ;
    Node.prototype.toTreeFormat = function (treeHash) {
        treeHash[this.id] = {
            id: this.id,
            parent: this.parent,
            text: this.text,
            icon: this.icon
        };
        for (var prop in this.leafs) {
            if (this.leafs.hasOwnProperty(prop)) {
                this.leafs[prop].toTreeFormat(treeHash, this);
            }
        }
    };

    function addChild(data) {

        var pr = boxcarData['pr'];
        if (!pr[data['id']]) {
            pr[data['id']] = new Node({
                id: data['id'],
                text: data['type'] + ":" + data['id'] + ":" + data['title']
            });
        }
        if (data['strategy']) {
            var leaf = new Leaf(data['strategy']);
            //add leaf to the pr list
            pr[data['id']].add(leaf);
            for (var index in fileterList) {
                var attributeName = fileterList[index]; //qual, impact, approach
                var storage= boxcarData[attributeName];
                var attrValue=leaf[attributeName];
                
                var nameList = [];
                if (attrValue.indexOf(',') > -1) {
                    nameList = attrValue.split(',');
                } else {
                    nameList.push(attrValue);
                }
                for (var i in nameList) {
                    var singleName = nameList[i].trim();
                    if (!storage[singleName]) {
                        storage[singleName] = new Node({
                            id: attributeName + singleName,
                            text: singleName
                        });
                    }
                    storage[singleName].add(leaf);
                }
            }
        }
    }

    boxcarContainer.toTreeFormat = function (classificaitonField) {
        var treeHash = [];
        for (var prop in boxcarData[classificaitonField]) {
            if (boxcarData[classificaitonField].hasOwnProperty(prop)) {
                boxcarData[classificaitonField][prop].toTreeFormat(treeHash);
            }
        }
        var treeArray = [];
        for (var prop in treeHash) {
            if (treeHash.hasOwnProperty(prop)) {
                treeArray.push(treeHash[prop]);
            }
        }
        return treeArray;
    };

    boxcarContainer.create = function (source) {
        boxcarData = {
            pr: {}
        };
        for (var index in fileterList) {
            boxcarData[fileterList[index]] = {};
        }
        for (var i = 0; i < source.length; i++) {
            addChild(source[i]);
        }
    };
    return boxcarContainer;
});


