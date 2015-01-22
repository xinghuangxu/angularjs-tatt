<?php


/**
 * Rally API Connector
 *
 * Simple class for interacting with RallyDev web services
 *
 * @version 2.0
 * @author St. John Johnson <stjohn@yahoo-inc.com>  && Leon Xu <xinghuangxu@gmail.com>


 *


 */
class Rally
{


    // Curl Object
    private $_curl;

    // Rally's Domain
    private $_domain;
    // Just for debugging
    private $_debug = false;
    // Some fancy user agent here
    private $_agent = 'PHP - Rally Api - 1.4';
    // Current API version
    private $_version = 'v2.0';
    // Current Workspace
    private $_workspace;
    // These headers are required to get valid JSON responses
    private $_headers_request = array('Content-Type: text/java_script');
    // Silly object translation
    private $_objectTranslation = array(
        'story' => 'hierarchicalrequirement',
        'userstory' => 'hierarchicalrequirement',
        'feature' => 'portfolioitem/feature',
        'initiative' => 'portfolioitem/initiative',
        'theme' => 'portfolioitem/theme',
        );
    // User object
    protected $_user = '';


    //Security Token
    private $_key = "";


    /**
     * Create Rally Api Object
     *
     * @param string $username
     *   The username for Rally
     * @param string $password
     *   The password for Rally (probably hunter2)
     * @param string $domain
     *   Override for Domain to talk to
     */


    public function __construct($username, $password, $domain =
        'rally1.rallydev.com')
    {
        $this->_domain = $domain;


        $this->_curl = curl_init();

        set_time_limit(0);
        //ini_set('display_errors', '0');
        $this->_setopt(CURLOPT_RETURNTRANSFER, true);
        $this->_setopt(CURLOPT_HTTPHEADER, $this->_headers_request);
        $this->_setopt(CURLOPT_VERBOSE, $this->_debug);
        $this->_setopt(CURLOPT_USERAGENT, $this->_agent);
        $this->_setopt(CURLOPT_HEADER, 0);
        $this->_setopt(CURLOPT_SSL_VERIFYHOST, 0);
        $this->_setopt(CURLOPT_SSL_VERIFYPEER, 0);
        $this->_setopt(CURLOPT_COOKIEJAR, dirname(__file__) . '/cookie.txt');
        // Authentication
        $this->_setopt(CURLOPT_USERPWD, "$username:$password");
        $this->_setopt(CURLOPT_HTTPAUTH, CURLAUTH_ANY);


        // Validate Login was Successful
        $user_data = $this->find('user', "(EmailAddress = \"{$username}\")");
        $security_data = $this->_getSecurityToken('security/authorize');
        //print_r($security_data);

        // global $token;
        $this->_key = $security_data['SecurityToken'];
        //print_r($security_data['SecurityToken']);
        $_SESSION['token'] = $security_data['SecurityToken'];
        $x = $_SESSION['token'];

        // echo $_SESSION['token'];
        // print_r($this->_key);
        // print_r($this->_key['SecurityToken']);
        //print_r($user_data);
        $this->_user = $user_data[0];
    }


    /**
     * Return Reference to User
     *
     * @return string
     *   Reference link to User
     */
    public function me()
    {
        return $this->_user['_ref'];
    }


    /**
     * Translate object types
     *
     * This is only really for
     *   story -> hierarchicalrequirement
     *
     * @param string $object
     *   Rally Object Type
     * @return string
     *   Translated Object
     */
    protected function _translate($object)
    {
        $object = strtolower($object);
        if (isset($this->_objectTranslation[$object])) {
            return $this->_objectTranslation[$object];
        }
        return $object;
    }


    /**
     * Set current workspace
     *
     * @param string $workspace_ref
     *   Workspace URL Reference
     */
    public function setWorkspace($workspace_ref)
    {
        $this->_workspace = $workspace_ref;
    }

    public function Iteration_Name($projname, $IterationName)
    {

        $Iteration_list = $this->find('Iteration', "(Project.Name = \"{$projname}\")",
            '', '');

        $c = count($Iteration_list);
        $final_it = array();

        //echo $IterationName;
        //Fetches iteration details
        for ($i = 0; $i < $c; $i++) {

            if ($Iteration_list[$i]['_refObjectName'] == $IterationName) {

                $final_it = $Iteration_list[$i];
            }
        }

        return $final_it;
    }

    public function Release_Name($ProjectName, $ReleaseName)
    {
        $Releaselist = $this->find('Release', "(Project.Name = \"{$ProjectName}\")", '',
            '');
        $b = count($Releaselist);
        //Fetches Release details
        for ($i = 0; $i < $b; $i++) {

            if ($Releaselist[$i]['_refObjectName'] == $ReleaseName) {
                $ReleaseName = $Releaselist[$i];
            }
        }
        return $ReleaseName;
    }


    public function Owner_Name($ProjectName, $OwnerName)
    {
        $proj_list = $this->find('Project', '', '', 'true');
        $p = count($proj_list);
        //Fetches project ID
        for ($y = 0; $y < $p; $y++) {
            if ($proj_list[$y]['_refObjectName'] == $ProjectName) {
                $ProjectID = $proj_list[$y]['ObjectID'];

            }
        }

        $ownerlist = $this->get1('Project', "/{$ProjectID}");
        global $Glob_owner;
        //print_r($Glob_owner);
        $owerlist_count = count($Glob_owner);
        //Fetches list of owner list


        for ($i = 0; $i < $owerlist_count; $i++) {

            if ($Glob_owner['Results'][$i]['_refObjectName'] == "$OwnerName") {

                $Owner = $Glob_owner['Results'][$i];
            }
        }
        // print_r($own);

        return $Owner;
    }


    /**
     * Generates a reference URL to the Object
     *
     * @param string $object
     *   Rally Object Type
     * @param int $id
     *   Rally Object ID
     * @return string
     *   Proper URL or _ref to use
     */


    public function getRef($object, $id)
    {
        $object = $this->_translate($object);
        $ref = "/{$object}";
        if ($id) {
            $ref .= "/{$id}";
        }
        error_reporting(E_ALL ^ E_STRICT);
        return $ref;
    }


    /**
     * Find Rally objects
     *
     * This method automatically traverses
     * and strips out Rally API gibbledy-gook
     *
     * @param string $object
     *   Rally Object Type
     * @param string $query
     *   Query String
     * @param string $order
     *   Field to sort on
     * @param bool $fetch
     *   Fetch all content
     * @return array
     *   Returned Objects
     */

    /*
    public function findmeta($object, $query, $order = '', $fetch = true) {
    $object = $this->_translate($object);
    $params = array(
    'query' => $query,
    'fetch' => ($fetch ? 'true' : 'false'),
    'pagesize' => 100,
    'start' => 1,
    );
    if (!empty($order)) {
    $params['order'] = $order;
    }


    // Loop through and ask for results
    $results = array();
    for (;;) { // I hate infinite loops
    $objects = $this->_get($this->_addWorkspace("{$object}", $params));
    $results = array_merge($results, $objects['Results']);
    //            print_r("objects:");
    //            print_r($objects);
    // Continue only if there are more
    if ($objects['TotalResultCount'] > 99 + $params['start']) {
    $params['start'] += 100;
    continue;
    }


    // We're done, break
    break;
    }
    //        print_r("results:");
    //        print_r($results);
    return $results;
    }
    */

    public function find($object, $query, $order = '', $fetch = '')
    {
        $object = $this->_translate($object);
        $params = array(
            'query' => $query,
            //'fetch' => ($fetch ? 'true' : 'false'),
            'fetch' => $fetch,
            'pagesize' => 100,
            'start' => 1,
            );
        if (!empty($order)) {
            $params['order'] = $order;
        }


        // Loop through and ask for results
        $results = array();
        for (;; ) { // I hate infinite loops
            $objects = $this->_get($this->_addWorkspace("{$object}", $params));
            $results = array_merge($results, $objects['Results']);
            //            print_r("objects:");
            //            print_r($objects);
            // Continue only if there are more
            if ($objects['TotalResultCount'] > 99 + $params['start']) {
                $params['start'] += 100;
                continue;
            }


            // We're done, break
            break;
        }
        //        print_r("results:");
        //        print_r($results);
        return $results;
    }


    private function _getSecurityToken($object)
    {
        $object = $this->_get($this->_addWorkspace("{$object}"));
        //        print_r("Security Key:");
        //        print_r($object);
        return $object;
    }


    private function _addKey($method)
    {
        return $method . "?key=" . $this->_key;
    }


    /**
     * Get a Rally object
     *
     * @param string $object
     *   Rally Object Type
     * @param int $id
     *   Rally Object ID
     * @return array
     *   Rally API response
     */


    public function get($object, $id)
    {
        return reset($this->_get($this->_addWorkspace($this->getRef($object, $id))));
        error_reporting(E_STRICT);
    }

    public function get1($object, $id)
    {

        return reset($this->_get1($this->_addWorkspace($this->getRef($object, $id))));
        error_reporting(E_STRICT);
    }
    public function get2($object, $id)
    {

        return reset($this->_get2($this->_addWorkspace($this->getRef($object, $id))));
        error_reporting(E_STRICT);
    }
    /**
     * Create a Rally object
     *
     * @param string $object
     *   Rally Object Type
     * @param array $params
     *   Fields to create with
     * @return array
     *   Rally API response
     */
    public function create($object, array $params)
    {
        $url = $this->_addWorkspace($this->getRef($object, 'create'));


        $object = $this->_put($url, $params);
        return $object['Object'];
    }


    /**
     * Update a Rally object
     *
     * @param string $object
     *   Rally Object Type
     * @param int $id
     *   Rally Object ID
     * @param array $params
     *   Fields to update
     * @return array
     *   Rally API response
     */
    public function update($object, $id, array $params)
    {
        $url = $this->_addWorkspace($this->getRef($object, $id));
        //        print_r($url);
        //        print_r($params);
        $object = $this->_post($url, $params);
        return $object['Object'];
    }


    /**
     * Delete a Rally object
     *
     * @param string $object
     *   Rally Object Type
     * @param int $id
     *   Rally Object ID
     * @return bool
     */
    public function delete($object, $id)
    {
        $url = $this->_addWorkspace($this->getRef($object, $id));


        // There are no values that return here
        $this->_delete($url);
        return true;
    }


    /**
     * Wraps Workspace around URL
     *
     * @param string $url
     *   URL to access
     * @param array $params
     *   Any additional parameters to put on the Query String
     */
    protected function _addWorkspace($url, array $params = array())
    {
        // Add workspace
        if ($this->_workspace) {
            $params['workspace'] = $this->_workspace;
        }


        // Add params as url
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }


        return $url;
    }


    /**
     * Perform a HTTP Get
     *
     * @param string $method
     *   Method of the API to execute
     * @return array
     *   API return data
     */
    protected function _get($method)
    {
        $this->_setopt(CURLOPT_CUSTOMREQUEST, 'GET');
        $this->_setopt(CURLOPT_POSTFIELDS, '');


        return $this->_execute($this->_addKey($method));
    }

    protected function _get1($method)
    {
        $this->_setopt(CURLOPT_CUSTOMREQUEST, 'GET');
        $this->_setopt(CURLOPT_POSTFIELDS, '');

        return $this->_execute1($method);
    }
    protected function _get2($method)
    {
        $this->_setopt(CURLOPT_CUSTOMREQUEST, 'GET');
        $this->_setopt(CURLOPT_POSTFIELDS, '');

        return $this->_execute2($method);
    }
    /**
     * Perform a HTTP Post
     *
     * @param string $method
     *   Method of the API to execute
     * @param array $params
     *   Paramters to pass
     * @return array
     *   API return data
     */
    protected function _post($method, array $params = array())
    {
        $this->_setopt(CURLOPT_CUSTOMREQUEST, 'POST');


        $payload = json_encode(array('Content' => $params));
        $this->_setopt(CURLOPT_POSTFIELDS, $payload);


        return $this->_execute($this->_addKey($method));
    }


    /**
     * Perform a HTTP Put
     *
     * @param string $method
     *   Method of the API to execute
     * @param array $params
     *   Paramters to pass
     * @return array
     *   API return data
     */
    protected function _put($method, array $params = array())
    {
        $this->_setopt(CURLOPT_CUSTOMREQUEST, 'PUT');


        $payload = json_encode(array('Content' => $params));
        $this->_setopt(CURLOPT_POSTFIELDS, $payload);
        //        print_r("put method:");
        //        print_r($method);
        return $this->_execute($this->_addKey($method));
    }


    /**
     * Perform a HTTP Delete
     *
     * @param string $method
     *   Method of the API to execute
     * @return array
     *   API return data
     */
    protected function _delete($method)
    {
        $this->_setopt(CURLOPT_CUSTOMREQUEST, 'DELETE');


        return $this->_execute($this->_addKey($method));
    }


    /**
     * Execute the Curl object
     *
     * @param string $method
     *   Method of the API to execute
     * @return array
     *   API return data
     * @throws RallyApiException
     *   On Curl errors
     */
    protected function _execute($method)
    {
        $method = ltrim($method, '/');
        $url = "https://{$this->_domain}/slm/webservice/{$this->_version}/{$method}";


        $this->_setopt(CURLOPT_URL, $url);
        //        print_r("URL-leonx:");
        //        print_r($url);
        $response = curl_exec($this->_curl);
        //        print_r($response);
        //        return;
        if (curl_errno($this->_curl)) {
            throw new RallyApiException(curl_error($this->_curl));
        }


        $info = curl_getinfo($this->_curl);
        //        print_r("Response-Leonx");
        //        print_r($response);
        return $this->_result($response, $info);
    }


    protected function _execute1($method)
    {
        $method = ltrim($method, '/');
        $url = "https://{$this->_domain}/slm/webservice/{$this->_version}/{$method}/TeamMembers";
        $this->_setopt(CURLOPT_URL, $url);
        //        print_r("URL-leonx:");

        $response1 = curl_exec($this->_curl);

        // echo $response;
        //return $response;
        if (curl_errno($this->_curl)) {
            throw new RallyApiException(curl_error($this->_curl));
        }


        $info = curl_getinfo($this->_curl);
        //        print_r("Response-Leonx");

        // $object = json_decode($response, true);
        //print_r($object);
        return $this->_result1($response1, $info);

    }
    protected function _execute2($method)
    {
        $method = ltrim($method, '/');

        $url = "https://{$this->_domain}/slm/webservice/{$this->_version}/{$method}/Children";
        $this->_setopt(CURLOPT_URL, $url);
        // print_r("URL-leonx:");

        $response1 = curl_exec($this->_curl);

        //echo $response;
        //return $response;
        if (curl_errno($this->_curl)) {
            throw new RallyApiException(curl_error($this->_curl));
        }


        $info = curl_getinfo($this->_curl);
        // print_r("Response-Leonx");

        // $object = json_decode($response, true);
        //print_r($object);
        return $this->_result2($response1, $info);

    }

    /**
     * Perform Json Decryption of the output
     *
     * @param string $response
     *   Curl Response
     * @param array $info
     *   Curl Info Array
     * @return array
     *   API return data
     * @throws RallyApiException
     *   On non-2xx responses
     */
    protected function _result($response, array $info)
    {
        // Panic on non-200 responses
        if ($info['http_code'] >= 300 || $info['http_code'] < 200) {
            header('HTTP/1.0 400 Bad error');
            throw new RallyApiException($response, $info['http_code']);
        }


        $object = json_decode($response, true);


        $wrappers = array(
            'OperationResult',
            'CreateResult',
            'QueryResult');
        // If we have one of these formats, strip out errors
        if (in_array(key($object), $wrappers)) {
            // Strip key
            $object = reset($object);


            // Look for errors and warnings
            if (!empty($object['Errors'])) {
                throw new RallyApiError(implode(PHP_EOL, $object['Errors']));
            }
            if (!empty($object['Warnings'])) {
                throw new RallyApiWarning(implode(PHP_EOL, $object['Warnings']));
            }
        }
        //        print_r("ResultObject-Leonx");
        //        print_r($object);
        return $object;
    }

    protected function _result1($response1, array $info)
    {
        // Panic on non-200 responses
        if ($info['http_code'] >= 300 || $info['http_code'] < 200) {
            throw new RallyApiException($response1, $info['http_code']);
        }


        $object = json_decode($response1, true);

        //print_r($object);

        $wrappers = array(
            'OperationResult',
            'CreateResult',
            'QueryResult');
        // If we have one of these formats, strip out errors
        if (in_array(key($object), $wrappers)) {
            // Strip key
            $object = reset($object);


            // Look for errors and warnings
            if (!empty($object['Errors'])) {
                throw new RallyApiError(implode(PHP_EOL, $object['Errors']));
            }
            if (!empty($object['Warnings'])) {
                throw new RallyApiWarning(implode(PHP_EOL, $object['Warnings']));
            }
        }
        //  print_r("ResultObject-Leonx");
        //print_r($object);
        global $Glob_owner;
        $Glob_owner = $object;
        //print_r($Glob_owner);


        error_reporting(E_ALL ^ E_STRICT);

        return $object;
    }

    protected function _result2($response1, array $info)
    {
        // Panic on non-200 responses
        if ($info['http_code'] >= 300 || $info['http_code'] < 200) {
            throw new RallyApiException($response1, $info['http_code']);
        }


        $object = json_decode($response1, true);

        //print_r($object);

        $wrappers = array(
            'OperationResult',
            'CreateResult',
            'QueryResult');
        // If we have one of these formats, strip out errors
        if (in_array(key($object), $wrappers)) {
            // Strip key
            $object = reset($object);


            // Look for errors and warnings
            if (!empty($object['Errors'])) {
                throw new RallyApiError(implode(PHP_EOL, $object['Errors']));
            }
            if (!empty($object['Warnings'])) {
                throw new RallyApiWarning(implode(PHP_EOL, $object['Warnings']));
            }
        }
        //  print_r("ResultObject-Leonx");
        //print_r($object);
        global $Glob_owner;
        $Glob_owner = $object;

        //print_r($Glob_owner);


        error_reporting(E_ALL ^ E_STRICT);

        return $object;
    }

    /**
     * Wrapper for curp_setopt
     *
     * @param string $option
     *   the CURLOPT_XXX option to set
     * @param varied $value
     *   the value
     */
    protected function _setopt($option, $value)
    {
        curl_setopt($this->_curl, $option, $value);
    }


}


class RallyApiException extends Exception
{

}


class RallyApiError extends RallyApiException
{

}


class RallyApiWarning extends RallyApiException
{

}






?>