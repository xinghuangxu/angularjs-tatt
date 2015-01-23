boxcar.factory('boxcarDataService', function ($resource) {
    var boxcarDataService = {
        editInfo: {nodeID: null, name: null, archID: null, iteration: null, icon: null, blocked: null},
        addNode: {nodeID: null, name: null, archID: null, iteration: null, icon: null, blocked: null},
        selectedNode: {nodeID: null, children: null, name: null}
    };
    boxcarDataService.resource = $resource('php/indexPHP.php', {},
            {
                children: {method: 'GET', params: {input_type: 'boxcarChildren'}, timeout: '60000'}
            }
    );
    return boxcarDataService;
});

