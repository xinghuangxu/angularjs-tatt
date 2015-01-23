//factory for sharing data between controllers
rally.factory('myAuthentication', function (dataService, $location, $log) {
    var myAuthentication = {
        loginView: true,
        dataView: false,
        actionNode: null,
        deleteSuccess: false,
        editInfo: {nodeID: null, name: null, archID: null, iteration: null, icon: null, blocked: null},
        addNode: {nodeID: null, name: null, archID: null, iteration: null, icon: null, blocked: null},
        selectedNode: {nodeID: null, children: null, name: null}
    };
    var timeoutCount = 0;
    var maxTimeoutAttempts = 3;
    myAuthentication.loginCheck = function loginCheck() {
        dataService.loginCheck(
                {},
                {},
                function (val, response)
                {
                    if (val.data == 'The domain is not registered') {
                        alert("Rally service is unavailable");
                    }
                    else {
                        if (val.data == 'exists') {
                            myAuthentication.loginView = false;
                            $location.path("/rally");
                            myAuthentication.dataView = true;
                            timeoutCount = 0;
                        }
                    }
                },
                function (response)
                {
                    //Error cases
                    switch (response.status) {
                        case 0:
                            if (timeoutCount < maxTimeoutAttempts - 1) {
                                $log.log("PHP timeout: Retrying connection...");
                                $alert({title: 'PHP Timeout:', content: 'Retrying connection...', container: '#alert-location', type: 'warning', duration: 5, dismissable: false});
                                timeoutCount++;
                                loginCheck(); //try to login again
                            } else {
                                $log.log("Timed Out: Could not reach php server");
                                $alert({title: 'Timed Out:', content: 'Could not reach php server', container: '#alert-location', type: 'danger', duration: 5, dismissable: false});
                                timeoutCount = 0;
                            }
                            break;
                        case 400:
                            $log.log("Login Credentials not found");
                            myAuthentication.loginView = true;
                            myAuthentication.dataView = false;
                            break;
                    }
                }
        );
    };
    var logoutTimeoutCount = 0;
    myAuthentication.logout = function logout() {

        //Service call to log out the user
        dataService.logOut(
                {},
                {},
                function (val, response)
                {
                    if (val.data == 'The domain is not registered') {
                        alert("Rally service is unavailable");
                    }
                    else {
                        $log.log("Logout Successful");
                        //Reset data fields if the user chooses to login again
                        $location.path("/rally/login");
                        myAuthentication.loginView = true;
                        myAuthentication.dataView = false;
                        logoutTimeoutCount = 0;
                    }
                },
                function (response)
                {
                    //Error cases
                    switch (response.status) {
                        case 0:
                            if (logoutTimeoutCount < maxTimeoutAttempts) {
                                $log.log("PHP timeout: Retrying connection...");
                                $alert({title: 'PHP Timeout:', content: 'Retrying connection...', container: '#alert-location', type: 'warning', duration: 5, dismissable: false});
                                logoutTimeoutCount++;
                                logout(); //try to logout again
                            } else {
                                $log.log("Timed Out: Could not reach php server");
                                $alert({title: 'Timed Out:', content: 'Could not reach php server', container: '#alert-location', type: 'danger', duration: 5, dismissable: false});
                                logoutTimeoutCount = 0;
                            }
                            break;
                        case 400:
                            $log.log("Session Timeout");
                            $alert({title: 'Session Timeout:', content: 'Please relogin', container: '#alert-location', type: 'danger', duration: 5, dismissable: false});
                            $location.path("/rally/login");
                            myAuthentication.loginView = true;
                            myAuthentication.dataView = false;
                            break;
                    }
                }
        );
    };
    var loginTimeoutCount = 0;
    myAuthentication.login = function login(username, password) {
        //Service call for authentication data
        dataService.authentication(
                //Post parameters
                        {
                            username: username,
                            password: password
                        },
                function (val, response)
                {
                    if (val.data == 'The domain is not registered') {
                        alert("Rally service is unavailable");
                    }
                    else {
                        $location.path("/rally");
                        myAuthentication.dataView = true;
                        loginTimeoutCount = 0;
                    }
                },
                        function (response)
                        {
                            //Error cases
                            switch (response.status) {
                                case 0:
                                    if (loginTimeoutCount < maxTimeoutAttempts - 1) {
                                        $log.log("PHP timeout: Retrying connection...");
                                        $alert({title: 'PHP Timeout:', content: 'Retrying connection...', container: '#alert-location', type: 'warning', duration: 5, dismissable: false});
                                        loginTimeoutCount++;
                                        login(username, password); //try to login
                                    } else {
                                        $log.log("Timed Out: Could not reach php server");
                                        $alert({title: 'Timed Out:', content: 'Could not reach php server', container: '#alert-location', type: 'danger', duration: 5, dismissable: false});
                                        loginTimeoutCount = 0;
                                    }
                                    break;
                                case 400:
                                    $alert({title: 'Error:', content: 'Invalid Login Information', container: '#alert-location', type: 'danger', duration: 5, dismissable: false});
                                    break;
                            }
                        }
                );
            };
    return myAuthentication;
});

//factory service that calls to the php file (Old method used $http dependency)
rally.factory('dataService', ['$resource', '$location', function ($resource, $location)
    {
        return $resource('php/indexPHP.php', {},
                {
                    loginCheck: {method: 'GET', params: {}, timeout: '60000'},
                    authentication: {method: 'POST', params: {}, timeout: '60000'},
                    projectList: {method: 'GET', params: {input_type: 'projectList'}, timeout: '60000'},
                    releaseList: {method: 'GET', params: {input_type: 'releaseList'}, timeout: '60000'},
                    iterationList: {method: 'GET', params: {input_type: 'iterationList'}, timeout: '60000'},
                    treeData: {method: 'GET', params: {input_type: 'treeData'}, timeout: '120000'},
                    dragdrop: {method: 'GET', params: {input_type: 'dragdrop'}, timeout: '60000'},
                    addNode: {method: 'GET', params: {input_type: 'addNode'}, timeout: '60000'},
                    updateNode: {method: 'GET', params: {input_type: 'updateNode'}, timeout: '60000'},
                    logOut: {method: 'GET', params: {input_type: 'logout'}, timeout: '60000'},
                    undo: {method: 'GET', params: {}, timeout: '60000'},
                    metadata: {method: 'GET', params: {input_type: 'metadata'}, timeout: '60000'},
                    deleteNode: {method: 'GET', params: {input_type: 'deleteNode'}, timeout: '60000'},
                    EQI: {method: 'GET', params: {input_type: 'EQI'}, timeout: '60000'}
                }
        )
    }]
        );