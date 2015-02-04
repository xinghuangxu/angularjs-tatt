

/**
 * Shared settings for use by any test planning controller
 */
testPlanner.factory('settingsData', 
    function() 
    {
        var placeholder = { 
                phase: { releaseName: null, stackLayer: null, subLayer: null },
                timeline: { start: "2014-05-02", end: "2015-05-05"},
                alm : 
                {
                    domain: 'apg_qa_producttest_db',
                    folder: "\\Boxcar\\SAM-EF",
                    prefix: "LSIP2000001234_xxxx_"
                },
                rally: { projects: ["SAM-EF Scrum 1", "SAM-EF Scrum 2"] }
        };
    
        return placeholder;
        
        
        return { 
            phase: { releaseName: null, stackLayer: null, subLayer: null },
            timeline: { start: null, end: null },
            alm: {
                domain: null,
                folder: null,
                prefix: null
            },
            rally: {
                projects: []
            }
        };
    }
);

/**
 * Retrieve information about ALM and the test folder structure
 */
testPlanner.factory('almFolderData',
    function($resource)
    {
        // Since all the data sources are scattered, we are not setting
        // any defaults on the $resource. All URL and other parameters
        // must be set on each action definition.
        return $resource("", {},
            {
                // Get the list of valid ALM domains
                getDomainList: { url: '../../03AJAX/AJAX_Scoping.php', method: 'GET', params: { service: 'fetchALMDomains' }, isArray: true, cache: true },
                
                // Get the root folders for a domain
                getRootFolder: {  url: '../xref/misc/jstree_alm_roots_json.php', method: 'GET', params: { alm_db: null }, isArray: true, cache: true },
                
                // Get a single subfolder's contents
                getFolder: { url: '../xref/misc/jstree_alm_children_json.php', method: 'GET', params: { alm_db: null, id: null }, isArray: true, cache: true }
            }
        );
    }
);
