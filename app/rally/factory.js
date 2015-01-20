//factory for sharing data between controllers
app.factory('myAuthentication', function(){
	return{loginView: false, dataView: false, actionNode: null, deleteSuccess: false,  editInfo: {nodeID: null, name: null, archID: null, iteration: null, icon: null, blocked: null}, addNode: {nodeID: null, name: null, archID: null, iteration: null, icon: null, blocked: null},  selectedNode: {nodeID: null, children: null, name: null}};
});

//factory service that calls to the php file (Old method used $http dependency)
app.factory('dataService', function($resource)
	{
		return $resource('php/indexPHP.php', {},
		{
			loginCheck: {method: 'GET', params: {}, timeout: '60000'},
			authentication: {method: 'POST',  params: {}, timeout: '60000'},
			projectList: {method: 'GET', params: {input_type:'projectList'}, timeout: '60000'},
			releaseList: {method: 'GET', params: {input_type: 'releaseList'}, timeout: '60000'},
			iterationList: {method: 'GET', params: {input_type: 'iterationList'}, timeout: '60000'},
			treeData: {method: 'GET', params: {input_type: 'treeData'}, timeout: '120000'},
			dragdrop: {method: 'GET', params: {input_type: 'dragdrop'}, timeout: '60000'},
			addNode: {method: 'GET', params: {input_type: 'addNode'}, timeout: '60000'},
			updateNode: {method: 'GET', params: {input_type:'updateNode'}, timeout: '60000'},
			logOut: {method: 'GET', params: {input_type: 'logout'}, timeout: '60000'},
			undo: {method: 'GET', params: {}, timeout: '60000'},
			metadata: {method: 'GET', params: {input_type: 'metadata'}, timeout: '60000'},
			deleteNode: {method: 'GET', params: {input_type: 'deleteNode'}, timeout: '60000'},
			EQI: {method: 'GET', params: {input_type: 'EQI'}, timeout: '60000'}
		}
		)
	}
);