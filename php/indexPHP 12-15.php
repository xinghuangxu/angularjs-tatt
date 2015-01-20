<?php
//Calling PHP TOOLKIT functions
include 'phpkit_toolkit.php';

//Calling Domain Availability functions
//include 'DomainAvailability1.php';

//Define a Domain
//$Domain = new DomainAvailability();

// Check the domain availability
//$available = $Domain->is_available("rally.rallydev.com");

// Make actions depending on the domain availability
//if ($available) {
//echo "The domain is not registered";
//}


//else {

//Starting the session for the authentication variables
session_start();

//Check the last activitiy or request on the page
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] >
    3600)) {
    // When the Last request is more than 30 minutes ago
    session_unset(); // unset $_SESSION variable for the run-time
    session_destroy(); // destroy session data in storage
    header('HTTP/1.0 400 Bad error'); //error to the front-end to log the user out
}

// update last activity time stamp
$_SESSION['LAST_ACTIVITY'] = time();


//checks whether input_type has a value or not to make sure the connection is working between both sides
if (!isset($_GET['input_type'])) {


    // Keep logged in when you refresh the page
    //search if the username and password exists
    if (isset($_SESSION['user']) && isset($_SESSION['pass'])) {
        echo json_encode((array('data' => 'exists')));

    }


    //Authentication
    else {
        //	----Read username/password
        $postInput = file_get_contents("php://input");
        $obj = json_decode($postInput, true);

        $username = $obj['username'];
        $password = $obj['password'];
        //create the main Rally object to use all the functions
        $rally = new Rally($username, $password);
        //Save the Authentication information for every PHP call
        $_SESSION['user'] = $username;
        $_SESSION['pass'] = $password;
        echo json_encode((array('data' => 'success')));

    }

}


//Logged in and search for the specific case
else {


    error_reporting(E_ALL & ~ E_NOTICE);
    //Load the username and password
    $username = $_SESSION['user'];
    $password = $_SESSION['pass'];
    $rally = new Rally($username, $password);

    $input_type = $_GET['input_type'];
    //Switch Cases for the frontend calls
    //Receive a request call from front-end and respoends to it
    switch ($input_type) {

            //Asking for a list of projects in Rally
        case 'projectList':


            //Fetches  Information about all the  projects from Rally
            $proj_list = $rally->find('Project', '', '', 'ScheduleState,HasParent,Parent');


            $projects_count = count($proj_list);

            $project_array = array();

            //Fetching only Projects name
            for ($x = 0; $x < $projects_count; $x++) {

                $project_array[$x] = new stdClass();
                $project_array[$x] = $proj_list[$x]['_refObjectName'];
            }

            $output = array('data' => $project_array);
            echo json_encode($output);

            break;

            //Asking for a list of Releases in Rally
        case 'releaseList':

            error_reporting(E_ALL ^ E_STRICT);

            $input = $_GET['input'];
            //Fetched Release information about a project
            $release_list = $rally->find('Release', "(Project.Name = \"{$input}\")", '',
                'ScheduleState,HasParent,Parent');

            //Counts the length of the Release_list array
            $release_list_count = count($release_list);
            //print_r($release_list_count);
            //Empty array
            $release_array = array();
            //Creating objects and getting only release names
            for ($x = 0; $x < $release_list_count; $x++) {

                $release_array[$x] = new stdClass();
                $release_array[$x] = $release_list[$x]['_refObjectName'];

            }
            $release_array[$release_list_count] = "All";
            array_unshift($release_array, "No Release");
            
            //$final11=json_encode($release_array);
            //$output = array('data' => $release_array);


            //OwnerList

            //Fetching all projects
            $proj_list = $rally->find('Project', '', '', 'true');
            $p = count($proj_list);


            //Findig the objectID for a specific project
            for ($y = 0; $y < $p; $y++) {
                if ($proj_list[$y]['_refObjectName'] == $input) {
                    $ProjectID = $proj_list[$y]['ObjectID'];
                }
            }
            $result = $rally->get1('Project', "/{$ProjectID}");


            $Owner_list_count = count($Glob_owner['Results']);
            for ($i = 0; $i < $Owner_list_count; $i++) {
                $List[$i] = new stdClass();
                $List[$i] = $Glob_owner['Results'][$i]['DisplayName'];
                //Sort the array of owner lists alphabetically
                array_multisort($List);
                array_unshift($List, "No Owner");
            }
            //
            //Finding specific user
            $specific_user = array();

            for ($i = 0; $i < $Owner_list_count; $i++) {
                if ($Glob_owner['Results'][$i]['EmailAddress'] == $username) {
                    $specific_user = $Glob_owner['Results'][$i]['DisplayName'];
                }

            }

            //Send Both lists
            $output = array('data' => array(
                    'releaseList' => $release_array,
                    'ownerList' => $List,
                    'SpecificUser' => $specific_user));

            echo json_encode($output);

            break;
            //Asking for a Rally Tree of Releases in Rally
        case 'treeData':
            $projname = $_GET['project'];
            $releasename = $_GET['release'];
            $Iteration = $_GET['iteration'];
            if ($releasename == "All" || $Iteration == "All"){
                 $userstories_all = $rally->find('userstory', "(Project.Name = \"{$projname}\")",
                    '', 'ScheduleState,Iteration,HasParent,Parent,c_ArchitecturalTopicID');
                //gets the size of the userstories_all array
                $userstories_count = count($userstories_all);

                //Empty array
                $tree_data_array_all = array();

                //Creating objects and fetching Userstories OBjectUUID and their parents Objects UUID
                for ($x = 0; $x < $userstories_count; $x++) {
                    $tree_data_array_all[$x] = new stdClass();
                    $tree_data_array_all[$x]->id = $userstories_all[$x]['_refObjectUUID'];
                    $tree_data_array_all[$x]->parent = $userstories_all[$x]['Parent']['_refObjectUUID'];

                    //Checks whether the parents user story has parents or it is an EPIC
                    if ($tree_data_array_all[$x]->parent == null) {
                        $tree_data_array_all[$x]->parent = '#';
                    }

                    //Assigning values to objects
                    $tree_data_array_all[$x]->text = $userstories_all[$x]['_refObjectName'];

                    $tree_data_array_all[$x]->icon = $userstories_all[$x]['ScheduleState'];

                    $tree_data_array_all[$x]->Blocked = $userstories_all[$x]['Blocked'];

                    //checks whether the user story status is blocked or not in the Rally to give the specific Block ICON
                    if ($tree_data_array_all[$x]->Blocked == 1) {
                        //Assign icons to the user stories depending on the schedule state
                        if ($tree_data_array_all[$x]->icon == "In-Progress") {
                            $tree_data_array_all[$x]->icon =
                                "./lib/jstree/themes/default/icon_In-Progress_Blocked.png";
                        } elseif ($tree_data_array_all[$x]->icon == "Defined") {
                            $tree_data_array_all[$x]->icon =
                                "./lib/jstree/themes/default/icon_Defined_Blocked.png";
                        } elseif ($tree_data_array_all[$x]->icon == "Completed") {
                            $tree_data_array_all[$x]->icon =
                                "./lib/jstree/themes/default/icon_Completed_Blocked.png";

                        } elseif ($tree_data_array_all[$x]->icon == "Accepted") {
                            $tree_data_array_all[$x]->icon =
                                "./lib/jstree/themes/default/icon_Accepted_Blocked.png";
                        }
                    } else {

                        if ($tree_data_array_all[$x]->icon == "In-Progress") {
                            $tree_data_array_all[$x]->icon =
                                "./lib/jstree/themes/default/icon_In-Progress.png";
                        } elseif ($tree_data_array_all[$x]->icon == "Defined") {
                            $tree_data_array_all[$x]->icon = "./lib/jstree/themes/default/icon_Defined.png";
                        } elseif ($tree_data_array_all[$x]->icon == "Completed") {
                            $tree_data_array_all[$x]->icon =
                                "./lib/jstree/themes/default/icon_Completed.png";
                        } elseif ($tree_data_array_all[$x]->icon == "Accepted") {
                            $tree_data_array_all[$x]->icon = "./lib/jstree/themes/default/icon_Accepted.png";
                        }
                    }

                    $tree_data_array_all[$x]->TopicID = $userstories_all[$x]['c_ArchitecturalTopicID'];
                    $tree_data_array_all[$x]->Iteration = $userstories_all[$x]['Iteration']['_refObjectName'];
                }


                $output = array('data' => $tree_data_array_all);
                echo json_encode($output);
                
            }
            elseif ($releasename != null && $Iteration != null){
                $ReleaseName = $releasename;
                $AllUserStories = $rally->find('userstory', "((Iteration.Name = \"{$Iteration}\") and (Release.Name = \"{$ReleaseName}\"))", '', 'ScheduleState,Iteration,HasParent,Parent,Release,c_ArchitecturalTopicID');
                $ReleasesArray = array();
                $FinalArray = array();
                $ShortAllStories = array();
                $Map = array();
                $ReleasesCounter = 0;
                $ShortAllStoriesCounter = 0;
                for ($i=0; $i<count($AllUserStories); $i++)
                {
                        $Map[] = $AllUserStories[$i]['_refObjectUUID'];
                        $ReleasesArray[$i]['id'] = $AllUserStories[$i]['_refObjectUUID'];
                        $ReleasesArray[$i]['parent'] = $AllUserStories[$i]['Parent']['_refObjectUUID'];
                        $ReleasesArray[$i]['text'] = $AllUserStories[$i]['_refObjectName'];
                        $ReleasesArray[$i]['icon'] = $AllUserStories[$i]['ScheduleState'];
                        $ReleasesArray[$i]['Blocked'] = $AllUserStories[$i]['Blocked'];
                        $ReleasesArray[$i]['TopicID'] = $AllUserStories[$i]['c_ArchitecturalTopicID'];
                        $ReleasesArray[$i]['Iteration'] = $AllUserStories[$i]['Iteration']['_refObjectName'];
                        $ReleasesArray[$i]['has'] = $AllUserStories[$i]['HasParent'];
                }

                if (count($ReleasesArray) == 0){
                    $var = "No User stories";
                    $output = array('data' => $var);
                    echo json_encode($output);
                }
                else{
                    $FinalArray = $ReleasesArray;
                $FinalArrayWithObj = array();
                $Counter = count($FinalArray);
                for ($i=0; $i<$Counter; $i++){
                    if ($FinalArray[$i]['parent'] != "" && $FinalArray[$i]['has'] == 1){
                        if (!in_array($FinalArray[$i]['parent'],$Map)){
                            $Map[] = $FinalArray[$i]['parent']; 
                            $ShortAllStories = $rally->get('userstory',$FinalArray[$i]['parent']);
                            $FinalArray[] = array("id"=>$ShortAllStories['_refObjectUUID'],
                                                  "parent"=>$ShortAllStories['Parent']['_refObjectUUID'],
                                                  "text"=>$ShortAllStories['_refObjectName'],
                                                  "icon"=>$ShortAllStories['ScheduleState'],
                                                  "Blocked"=>$ShortAllStories['Blocked'],
                                                  "TopicID"=>$ShortAllStories['c_ArchitecturalTopicID'],
                                                  "Iteration"=>$ShortAllStories['Iteration']['_refObjectName'],
                                                  "has"=>$ShortAllStories['HasParent']);
                            $Counter++; 
                        }
                    }
                }
                }
                for($i=0;$i<count($FinalArray);$i++){
                    $FinalArrayWithObj[$i] = new stdClass();
                            $FinalArrayWithObj[$i]->id = $FinalArray[$i]['id'];
                            $FinalArrayWithObj[$i]->parent = $FinalArray[$i]['parent'];
                            $FinalArrayWithObj[$i]->text = $FinalArray[$i]['text'];
                            $FinalArrayWithObj[$i]->icon = $FinalArray[$i]['icon'];
                            $FinalArrayWithObj[$i]->Blocked = $FinalArray[$i]['Blocked'];
                            $FinalArrayWithObj[$i]->TopicID = $FinalArray[$i]['TopicID'];
                            $FinalArrayWithObj[$i]->Iteration = $FinalArray[$i]['Iteration'];
                            $FinalArrayWithObj[$i]->has = $FinalArray[$i]['has'];
                            if($FinalArrayWithObj[$i]->parent == null){
                                $FinalArrayWithObj[$i]->parent = '#';
                            }
                            if ($FinalArrayWithObj[$i]->Blocked == 1) {
                                            //Assign icons to the user stories depending on the schedule state
                                            if ($FinalArrayWithObj[$i]->icon == "In-Progress") {
                                                $FinalArrayWithObj[$i]->icon =
                                                    "./lib/jstree/themes/default/icon_In-Progress_Blocked.png";
                                            } elseif ($FinalArrayWithObj[$i]->icon == "Defined") {
                                                $FinalArrayWithObj[$i]->icon =
                                                    "./lib/jstree/themes/default/icon_Defined_Blocked.png";
                                            } elseif ($FinalArrayWithObj[$i]->icon == "Completed") {
                                                $FinalArrayWithObj[$i]->icon =
                                                    "./lib/jstree/themes/default/icon_Completed_Blocked.png";
                                            } elseif ($FinalArrayWithObj[$i]->icon == "Accepted") {
                                                $FinalArrayWithObj[$i]->icon =
                                                    "./lib/jstree/themes/default/icon_Accepted_Blocked.png";
                                            }
                                        } else {

                                            if ($FinalArrayWithObj[$i]->icon == "In-Progress") {
                                                $FinalArrayWithObj[$i]->icon = "./lib/jstree/themes/default/icon_In-Progress.png";
                                            } elseif ($FinalArrayWithObj[$i]->icon == "Defined") {
                                                $FinalArrayWithObj[$i]->icon = "./lib/jstree/themes/default/icon_Defined.png";
                                            } elseif ($FinalArrayWithObj[$i]->icon == "Completed") {
                                                $FinalArrayWithObj[$i]->icon = "./lib/jstree/themes/default/icon_Completed.png";
                                            } elseif ($FinalArrayWithObj[$i]->icon == "Accepted") {
                                                $FinalArrayWithObj[$i]->icon = "./lib/jstree/themes/default/icon_Accepted.png";
                                            }
                                        }
                }
                
                $output = array('data' => $FinalArrayWithObj);
                echo json_encode($output);
                
            }
            elseif ($releasename != null && $Iteration == null){
                $ReleaseName = $releasename;
                $AllUserStories = $rally->find('userstory', "(Release.Name = \"$ReleaseName\")", '', 'ScheduleState,Iteration,HasParent,Parent,Release,c_ArchitecturalTopicID');;
                $ReleasesArray = array();
                $FinalArray = array();
                $ShortAllStories = array();
                $Map = array();
                $ReleasesCounter = 0;
                $ShortAllStoriesCounter = 0;
                for ($i=0; $i<count($AllUserStories); $i++)
                {
                        $Map[] = $AllUserStories[$i]['_refObjectUUID'];
                        $ReleasesArray[$i]['id'] = $AllUserStories[$i]['_refObjectUUID'];
                        $ReleasesArray[$i]['parent'] = $AllUserStories[$i]['Parent']['_refObjectUUID'];
                        $ReleasesArray[$i]['text'] = $AllUserStories[$i]['_refObjectName'];
                        $ReleasesArray[$i]['icon'] = $AllUserStories[$i]['ScheduleState'];
                        $ReleasesArray[$i]['Blocked'] = $AllUserStories[$i]['Blocked'];
                        $ReleasesArray[$i]['TopicID'] = $AllUserStories[$i]['c_ArchitecturalTopicID'];
                        $ReleasesArray[$i]['Iteration'] = $AllUserStories[$i]['Iteration']['_refObjectName'];
                        $ReleasesArray[$i]['has'] = $AllUserStories[$i]['HasParent'];
                }

                if (count($ReleasesArray) == 0){
                    $var = "No User stories";
                    $output = array('data' => $var);
                    echo json_encode($output);
                }
                else{
                    $FinalArray = $ReleasesArray;
                $FinalArrayWithObj = array();
                $Counter = count($FinalArray);
                for ($i=0; $i<$Counter; $i++){
                    if ($FinalArray[$i]['parent'] != "" && $FinalArray[$i]['has'] == 1){
                        if (!in_array($FinalArray[$i]['parent'],$Map)){
                            $Map[] = $FinalArray[$i]['parent']; 
                            $ShortAllStories = $rally->get('userstory',$FinalArray[$i]['parent']);
                            $FinalArray[] = array("id"=>$ShortAllStories['_refObjectUUID'],
                                                  "parent"=>$ShortAllStories['Parent']['_refObjectUUID'],
                                                  "text"=>$ShortAllStories['_refObjectName'],
                                                  "icon"=>$ShortAllStories['ScheduleState'],
                                                  "Blocked"=>$ShortAllStories['Blocked'],
                                                  "TopicID"=>$ShortAllStories['c_ArchitecturalTopicID'],
                                                  "Iteration"=>$ShortAllStories['Iteration']['_refObjectName'],
                                                  "has"=>$ShortAllStories['HasParent']);
                            $Counter++; 
                        }
                    }
                }
                }
                for($i=0;$i<count($FinalArray);$i++){
                    $FinalArrayWithObj[$i] = new stdClass();
                            $FinalArrayWithObj[$i]->id = $FinalArray[$i]['id'];
                            $FinalArrayWithObj[$i]->parent = $FinalArray[$i]['parent'];
                            $FinalArrayWithObj[$i]->text = $FinalArray[$i]['text'];
                            $FinalArrayWithObj[$i]->icon = $FinalArray[$i]['icon'];
                            $FinalArrayWithObj[$i]->Blocked = $FinalArray[$i]['Blocked'];
                            $FinalArrayWithObj[$i]->TopicID = $FinalArray[$i]['TopicID'];
                            $FinalArrayWithObj[$i]->Iteration = $FinalArray[$i]['Iteration'];
                            $FinalArrayWithObj[$i]->has = $FinalArray[$i]['has'];
                            if($FinalArrayWithObj[$i]->parent == null){
                                $FinalArrayWithObj[$i]->parent = '#';
                            }
                            if ($FinalArrayWithObj[$i]->Blocked == 1) {
                                            //Assign icons to the user stories depending on the schedule state
                                            if ($FinalArrayWithObj[$i]->icon == "In-Progress") {
                                                $FinalArrayWithObj[$i]->icon =
                                                    "./lib/jstree/themes/default/icon_In-Progress_Blocked.png";
                                            } elseif ($FinalArrayWithObj[$i]->icon == "Defined") {
                                                $FinalArrayWithObj[$i]->icon =
                                                    "./lib/jstree/themes/default/icon_Defined_Blocked.png";
                                            } elseif ($FinalArrayWithObj[$i]->icon == "Completed") {
                                                $FinalArrayWithObj[$i]->icon =
                                                    "./lib/jstree/themes/default/icon_Completed_Blocked.png";
                                            } elseif ($FinalArrayWithObj[$i]->icon == "Accepted") {
                                                $FinalArrayWithObj[$i]->icon =
                                                    "./lib/jstree/themes/default/icon_Accepted_Blocked.png";
                                            }
                                        } else {

                                            if ($FinalArrayWithObj[$i]->icon == "In-Progress") {
                                                $FinalArrayWithObj[$i]->icon = "./lib/jstree/themes/default/icon_In-Progress.png";
                                            } elseif ($FinalArrayWithObj[$i]->icon == "Defined") {
                                                $FinalArrayWithObj[$i]->icon = "./lib/jstree/themes/default/icon_Defined.png";
                                            } elseif ($FinalArrayWithObj[$i]->icon == "Completed") {
                                                $FinalArrayWithObj[$i]->icon = "./lib/jstree/themes/default/icon_Completed.png";
                                            } elseif ($FinalArrayWithObj[$i]->icon == "Accepted") {
                                                $FinalArrayWithObj[$i]->icon = "./lib/jstree/themes/default/icon_Accepted.png";
                                            }
                                        }
                }
                
                $output = array('data' => $FinalArrayWithObj);
                echo json_encode($output);
                
            }
            elseif ($Iteration != null && $releasename == null){
                $AllUserStories = $rally->find('userstory', "(Iteration.Name = \"$Iteration\")", '', 'ScheduleState,Iteration,HasParent,Parent,Release,c_ArchitecturalTopicID');;
                $ReleasesArray = array();
                $FinalArray = array();
                $ShortAllStories = array();
                $Map = array();
                $ReleasesCounter = 0;
                $ShortAllStoriesCounter = 0;
                for ($i=0; $i<count($AllUserStories); $i++)
                {
                        $Map[] = $AllUserStories[$i]['_refObjectUUID'];
                        $ReleasesArray[$i]['id'] = $AllUserStories[$i]['_refObjectUUID'];
                        $ReleasesArray[$i]['parent'] = $AllUserStories[$i]['Parent']['_refObjectUUID'];
                        $ReleasesArray[$i]['text'] = $AllUserStories[$i]['_refObjectName'];
                        $ReleasesArray[$i]['icon'] = $AllUserStories[$i]['ScheduleState'];
                        $ReleasesArray[$i]['Blocked'] = $AllUserStories[$i]['Blocked'];
                        $ReleasesArray[$i]['TopicID'] = $AllUserStories[$i]['c_ArchitecturalTopicID'];
                        $ReleasesArray[$i]['Iteration'] = $AllUserStories[$i]['Iteration']['_refObjectName'];
                        $ReleasesArray[$i]['has'] = $AllUserStories[$i]['HasParent'];
                }

                if (count($ReleasesArray) == 0){
                    $var = "No User stories";
                    $output = array('data' => $var);
                    echo json_encode($output);
                }
                else{
                    $FinalArray = $ReleasesArray;
                $FinalArrayWithObj = array();
                $Counter = count($FinalArray);
                for ($i=0; $i<$Counter; $i++){
                    if ($FinalArray[$i]['parent'] != "" && $FinalArray[$i]['has'] == 1){
                        if (!in_array($FinalArray[$i]['parent'],$Map)){
                            $Map[] = $FinalArray[$i]['parent']; 
                            $ShortAllStories = $rally->get('userstory',$FinalArray[$i]['parent']);
                            $FinalArray[] = array("id"=>$ShortAllStories['_refObjectUUID'],
                                                  "parent"=>$ShortAllStories['Parent']['_refObjectUUID'],
                                                  "text"=>$ShortAllStories['_refObjectName'],
                                                  "icon"=>$ShortAllStories['ScheduleState'],
                                                  "Blocked"=>$ShortAllStories['Blocked'],
                                                  "TopicID"=>$ShortAllStories['c_ArchitecturalTopicID'],
                                                  "Iteration"=>$ShortAllStories['Iteration']['_refObjectName'],
                                                  "has"=>$ShortAllStories['HasParent']);
                            $Counter++; 
                        }
                    }
                }
                }
                for($i=0;$i<count($FinalArray);$i++){
                    $FinalArrayWithObj[$i] = new stdClass();
                            $FinalArrayWithObj[$i]->id = $FinalArray[$i]['id'];
                            $FinalArrayWithObj[$i]->parent = $FinalArray[$i]['parent'];
                            $FinalArrayWithObj[$i]->text = $FinalArray[$i]['text'];
                            $FinalArrayWithObj[$i]->icon = $FinalArray[$i]['icon'];
                            $FinalArrayWithObj[$i]->Blocked = $FinalArray[$i]['Blocked'];
                            $FinalArrayWithObj[$i]->TopicID = $FinalArray[$i]['TopicID'];
                            $FinalArrayWithObj[$i]->Iteration = $FinalArray[$i]['Iteration'];
                            $FinalArrayWithObj[$i]->has = $FinalArray[$i]['has'];
                            if($FinalArrayWithObj[$i]->parent == null){
                                $FinalArrayWithObj[$i]->parent = '#';
                            }
                            if ($FinalArrayWithObj[$i]->Blocked == 1) {
                                            //Assign icons to the user stories depending on the schedule state
                                            if ($FinalArrayWithObj[$i]->icon == "In-Progress") {
                                                $FinalArrayWithObj[$i]->icon =
                                                    "./lib/jstree/themes/default/icon_In-Progress_Blocked.png";
                                            } elseif ($FinalArrayWithObj[$i]->icon == "Defined") {
                                                $FinalArrayWithObj[$i]->icon =
                                                    "./lib/jstree/themes/default/icon_Defined_Blocked.png";
                                            } elseif ($FinalArrayWithObj[$i]->icon == "Completed") {
                                                $FinalArrayWithObj[$i]->icon =
                                                    "./lib/jstree/themes/default/icon_Completed_Blocked.png";
                                            } elseif ($FinalArrayWithObj[$i]->icon == "Accepted") {
                                                $FinalArrayWithObj[$i]->icon =
                                                    "./lib/jstree/themes/default/icon_Accepted_Blocked.png";
                                            }
                                        } else {

                                            if ($FinalArrayWithObj[$i]->icon == "In-Progress") {
                                                $FinalArrayWithObj[$i]->icon = "./lib/jstree/themes/default/icon_In-Progress.png";
                                            } elseif ($FinalArrayWithObj[$i]->icon == "Defined") {
                                                $FinalArrayWithObj[$i]->icon = "./lib/jstree/themes/default/icon_Defined.png";
                                            } elseif ($FinalArrayWithObj[$i]->icon == "Completed") {
                                                $FinalArrayWithObj[$i]->icon = "./lib/jstree/themes/default/icon_Completed.png";
                                            } elseif ($FinalArrayWithObj[$i]->icon == "Accepted") {
                                                $FinalArrayWithObj[$i]->icon = "./lib/jstree/themes/default/icon_Accepted.png";
                                            }
                                        }
                }
                
                $output = array('data' => $FinalArrayWithObj);
                echo json_encode($output);
                
            }
            


            //Fetches all the UserStories from Rally for all releases
            if ($releasename == "All") {
               

            } else {
                

            }


            break;

        case 'metadata':

            error_reporting(E_ALL ^ E_STRICT);

            $input = $_GET['input'];

            //Getting information about the User Story from Rally
            $User_Details = $rally->get('HierarchicalRequirement', "/{$input}");
            //Creating empty object
            $meta_data_array = new stdClass();
            //Assigning values to the object
            $meta_data_array->id = $User_Details['FormattedID'];
            $meta_data_array->arch = $User_Details['c_ArchitecturalTopicID'];
            $meta_data_array->state = $User_Details['ScheduleState'];
            $meta_data_array->owner = $User_Details['Owner']['_refObjectName'];
            $meta_data_array->iteration = $User_Details['Iteration']['_refObjectName'];
            $meta_data_array->Title = $User_Details['_refObjectName'];
            $meta_data_array->points = $User_Details['PlanEstimate'];
            $meta_data_array->release = $User_Details['Release']['_refObjectName'];
            $meta_data_array->description = $User_Details['Description'];

            //Encoding  the array into JSON
            $output = array('data' => $meta_data_array);
            echo (json_encode($output));
            break;


        case 'updatearch':

            /*
            $userstory_newtopicID = json_decode($input);
            $userStoryId =  $userstory_newtopicID->userstory;
            $newarchtopicID = $userstory_newtopicID->newtopicID;     
            $userstoryinformation= $rally->get('userstory',$userStoryId);
            $temptopicid=$userstoryinformation['c_ArchitecturalTopicID'];
            $comma=";";
            $finaltopicid2=$temptopicid.$comma.$newarchtopicID;
            $result = $rally->update('userstory', $userStoryId, array('c_ArchitecturalTopicID' => $finaltopicid2));
            */

            break;


        case 'deleteNode':

            $input = $_GET['input'];
            $userStoryId = $input;
            $userstoryinformation = $rally->get('userstory', $userStoryId);
            $childcount = $userstoryinformation['DirectChildrenCount'];

            //Checks whether the user story has any children
            if ($childcount == 0) {

                $rally->delete('userstory', $userStoryId);
            } else {
                echo (" Not Allowed");
            }

            break;


        case 'updateNode':

            /*  $ProjectName = "Spark Sandbox";
            $OwnerName = 'Chinta Rachina';
            $UserID = "e1d068f2-63c9-4c56-a9de-d19684672488";
            $Name = "He Man";
            $Description = "";
            $ArchitecturalTopicID = "gfj689-bjdkldsdgb-dfjsm";
            $SchedulePlan_Est = "";
            $ScheduleState = "";
            $IterationName = "";
            $ReleaseName = ""; */
            $ProjectName = $_GET['project'];
            $OwnerName = $_GET['owner'];
            $UserID = $_GET['newNodeID'];
            $Name = $_GET['title'];
            $Description = $_GET['description'];
            $ArchitecturalTopicID = $_GET['arch'];
            $SchedulePlan_Est = $_GET['points'];
            $ScheduleState = $_GET['state'];
            $IterationName = $_GET['iteration'];
            $ReleaseName = $_GET['release'];
            //Fetches info. about the user story
            $User_Details = $rally->get('userstory', "/{$UserID}");
            //echo "<Pre>";print_r($User_Details);echo "</Pre>";

            //Checks whether the Name value passed is null or not
            if ($Name != '') {
                if ($User_Details['_refObjectName'] != $Name) {


                    $update['Name'] = $Name;
                }
            }

            //Checks whether the Description value passed is null or not
            // if ($Description != '') {
            if ($User_Details['Description'] != $Description) {
                $update['Description'] = "$Description";


            }
            // }

            //Checks whether the Arch.Topic ID value passed is null or not
            //  if ($ArchitecturalTopicID != '') {
            if ($User_Details['c_ArchitecturalTopicID'] != $ArchitecturalTopicID) {
                $update['c_ArchitecturalTopicID'] = "$ArchitecturalTopicID";
            }
            //}

            //Checks whether the Schedule state value passed is null or not
            if ($ScheduleState != '') {
                if ($User_Details['ScheduleState'] != $ScheduleState) {
                    $update['ScheduleState'] = "$ScheduleState";
                }
            }

            //Checks whether the Schedule state Plan value passed is null or not
            if ($SchedulePlan_Est != '') {
                if ($User_Details['PlanEstimate'] != $SchedulePlan_Est) {
                    $update['PlanEstimate'] = "$SchedulePlan_Est";
                }
            }

            //Checks whether the IterationName value passed is null or not
            if ($IterationName != '') {
                if ($User_Details['Iteration']['_refObjectName'] != $IterationName) {
                    $IterationName = $rally->Iteration_Name("{$ProjectName}", "{$IterationName}");
                    $update['Iteration'] = new stdClass();
                    $update['Iteration'] = $IterationName;
                }
            }

            //Checks whether the Release Name value passed is null or not
            if ($ReleaseName != '') {
                if ($ReleaseName == "No Release") {
                    unset($update['Release']);
                } else {

                    if ($User_Details['Release']['_refObjectName'] != $ReleaseName) {
                        $Releaseobject = $rally->Release_Name("{$ProjectName}", "{$ReleaseName}");
                        $update['Release'] = new stdClass();
                        $update['Release'] = $Releaseobject;
                    }
                }
            }

            //Checks whether the Owner Name value passed is null or not


            if ($OwnerName != '') {

                if ($OwnerName == "No Owner") {
                    unset($update['Owner']);
                } else {
                    if ($User_Details['Owner']['_refObjectName'] != $OwnerName) {
                        $Owner = $rally->Owner_Name("{$ProjectName}", "{$OwnerName}");
                        $update['Owner'] = new stdClass();
                        $update['Owner'] = $Owner;
                    }
                }
            }

            $rally->update('userstory', $UserID, $update);
            
            $blocked = $User_Details['Blocked'];
            $icon=$ScheduleState;
            
            if ($blocked == 1) {
                if ($icon == "In-Progress") {
                    $icon = "./lib/jstree/themes/default/icon_In-Progress_Blocked.png";
                } elseif ($icon == "Defined") {
                    $icon = "./lib/jstree/themes/default/icon_Defined_Blocked.png";
                } elseif ($icon == "Completed") {
                    $icon = "./lib/jstree/themes/default/icon_Completed_Blocked.png";
                } elseif ($icon == "Accepted") {
                    $icon = "./lib/jstree/themes/default/icon_Accepted_Blocked.png";
                }
            } else {
                if ($icon == "In-Progress") {
                    $icon = "./lib/jstree/themes/default/icon_In-Progress.png";
                } elseif ($icon == "Defined") {
                    $icon = "./lib/jstree/themes/default/icon_Defined.png";
                } elseif ($icon == "Completed") {
                    $icon = "./lib/jstree/themes/default/icon_Completed.png";
                } elseif ($icon == "Accepted") {
                    $icon = "./lib/jstree/themes/default/icon_Accepted.png";
                }
            }
            if ($blocked == 1) {
                $blocked = true;
            } else {
                $blocked = false;
            }

            $output = array('data' => array(
                    'icon' => $icon,
                    'Blocked' => $blocked));
            echo (json_encode($output));
            
            break;


        case 'addNode':

            ///...DYNAMIC...
            $projname = $_GET['project'];
            $ParentID = $_GET['newNodeID'];
            $Name = $_GET['title'];
            $Description = $_GET['description'];
            $ArchitecturalTopicID = $_GET['arch'];
            $SchedulePlan_Est = $_GET['points'];
            $ScheduleState = $_GET['state'];
            $IterationName = $_GET['iteration'];
            $ReleaseName = $_GET['release'];
            $OwnerName = $_GET['owner'];

            if ($Name != '') {

                $create['Name'] = $Name;
            }
            if ($Description != '') {

                $create['Description'] = $Description;
            }

            if ($ArchitecturalTopicID != '') {

                $create['c_ArchitecturalTopicID'] = $ArchitecturalTopicID;
            }

            if ($ScheduleState != '') {

                $create['ScheduleState'] = $ScheduleState;
            }

            if ($SchedulePlan_Est != '') {

                $create['PlanEstimate'] = $SchedulePlan_Est;
            }
            if ($ParentID != '') {

                if ($ParentID == '#') {
                    $create['Parent'] = $ParentID;
                    unset($create['Parent']);
                } else {

                    error_reporting(E_ALL ^ E_STRICT);
                    $Parent_details = $rally->get('userstory', "$ParentID");
                    $create['Parent'] = new stdclass;
                    $create['Parent'] = $Parent_details;
                }
            }

            //Owner Array
            if ($OwnerName != '') {
                if ($OwnerName == "No Owner") {
                    unset($create['Owner']);
                } else {
                    $Owner = $rally->Owner_Name("{$projname}", "{$OwnerName}");
                    $create['Owner'] = new stdClass();
                    $create['Owner'] = $Owner;
                }
            }
            //Iteration Array

            if ($IterationName != '') {
                $final_it = $rally->Iteration_Name("{$projname}", "{$IterationName}");
                $create['Iteration'] = new stdclass;
                $create['Iteration'] = $final_it;
            }
            //Release Array

            if ($ReleaseName != '') {

                if ($ReleaseName == "No Release") {
                    unset($create['Release']);
                } else {

                    $final_Rel = $rally->Release_Name("{$projname}", "{$ReleaseName}");
                    $create['Release'] = new stdclass;
                    $create['Release'] = $final_Rel;
                }
            }

            $createUS_Root = $rally->create('userstory', $create);
            $ID = $createUS_Root['_refObjectUUID'];
            $icon = $createUS_Root['ScheduleState'];
            $blocked = $createUS_Root['Blocked'];

            if ($blocked == 1) {
                if ($icon == "In-Progress") {
                    $icon = "./lib/jstree/themes/default/icon_In-Progress_Blocked.png";
                } elseif ($icon == "Defined") {
                    $icon = "./lib/jstree/themes/default/icon_Defined_Blocked.png";
                } elseif ($icon == "Completed") {
                    $icon = "./lib/jstree/themes/default/icon_Completed_Blocked.png";
                } elseif ($icon == "Accepted") {
                    $icon = "./lib/jstree/themes/default/icon_Accepted_Blocked.png";
                }
            } else {
                if ($icon == "In-Progress") {
                    $icon = "./lib/jstree/themes/default/icon_In-Progress.png";
                } elseif ($icon == "Defined") {
                    $icon = "./lib/jstree/themes/default/icon_Defined.png";
                } elseif ($icon == "Completed") {
                    $icon = "./lib/jstree/themes/default/icon_Completed.png";
                } elseif ($icon == "Accepted") {
                    $icon = "./lib/jstree/themes/default/icon_Accepted.png";
                }
            }
            if ($blocked == 1) {
                $blocked = true;
            } else {
                $blocked = false;
            }

            $output = array('data' => array(
                    'ID' => $ID,
                    'icon' => $icon,
                    'Blocked' => $blocked));
            echo (json_encode($output));
            break;

        case 'dragdrop':

            $input = $_GET['input'];

            //Contains the user story ID and new parent ID
            $node_parent = json_decode($input);
            $userStoryId = $node_parent->node;
            $Parent2 = $node_parent->parent;


            if ($Parent2 == "#") {
                $Parent1 = "null";

            } else {
                //Fetches Information about new parent from rally
                $Parent2_UserStory = $rally->get('userstory', $Parent2);

                $Parent1 = $Parent2_UserStory;
            }

            //Udpates the User story parent with the new parent in the rally
            $Updated_UserStory = $rally->update('userstory', $userStoryId, array('Parent' =>
                    $Parent1));

            echo "User Story ID: ", $userStoryId, "New Parent ID: ", $Parent2, "Parent ?: ",
                $Parent1;

            break;

        case 'IterationList';

            $ProjectID = $_GET['input'];

            //Gets the project details from ID
            $ProjectDetail = $rally->get('Project', "/{$ProjectID}");

            $variable = $ProjectDetail['_refObjectName'];

            //Fetches Iteration details using project name
            $Iteration_list = $rally->find('Iteration', "(Project.Name = \"{$variable}\")",
                '', '');

            //Fetching Iteration Name & Iteration ID
            for ($i = 0; $i < count($Iteration_list); $i++) {

                $Iteration[$i] = new stdClass();
                $Iteration[$i]->IternationName = $Iteration_list[$i]['_refObjectName'];
                $Iteration[$i]->IternationID = $Iteration_list[$i]['_refObjectUUID'];
            }
            //Encoding  the array into JSON
			$output = $iteration;
            echo json_encode($output);

            break;

        case 'logout':
            //Unsetting the session variables
            unset($_SESSION['user']);
            unset($_SESSION['pass']);

            break;
			
			case 'EQI':


                /*
                $input = "LSIP1234567890 – P0";
                $UserName = rtrim($input, " – P0");


                $ProjectName = "Spark Sandbox";

                $userstories_Epic = $rally->find('userstory', "(Name contains   \"$UserName\")",
                '', 'ScheduleState,Iteration,Children,DirectChildrenCount,Release,PlanEstimate');

                print_r($userstories_Epic);
                die();
                $plannd_userStories = $userstories_Epic[0]['PlanEstimate'];

                $ID = $userstories_Epic[0]['_refObjectUUID'];*/


                $ID = $_GET['input'];
				
                $userstories_Epic = $rally->get('HierarchicalRequirement', "/$ID");
				
				$c = 0;

                $plannd_userStories = $userstories_Epic['PlanEstimate'];

                $result = $rally->get2('HierarchicalRequirement', "/{$ID}");

                $b = count($Glob_owner['Results']);

                for ($x = 0; $x < $b; $x++) {
                    $child[$c] = $Glob_owner['Results'][$x];
                    $c++;
                }

                $FinalArray = array();
                $FinalArray = $child;
                $total_count = 0;
                $Counter = count($Glob_owner['Results']);

                for ($y = 0; $y < $Counter; $y++) {

                    if ($FinalArray[$y]['DirectChildrenCount'] != 0) {
                        $I = $FinalArray[$y]['_refObjectUUID'];
                        $result = $rally->get2('HierarchicalRequirement', "/$I");

                        for ($i = 0; $i < count($Glob_owner['Results']); $i++) {
                            $FinalArray[] = $Glob_owner['Results'][$i];
                        }

                        $Counter = 0;
                        $Counter = count($FinalArray);


                    }

                }


                for ($x = 0; $x < count($FinalArray); $x++) {
                    if ($FinalArray[$x]['DirectChildrenCount'] == 0 && $FinalArray[$x]['ScheduleState'] ==
                        'Accepted') {
                        $total_count = $total_count + $FinalArray[$x]['PlanEstimate'];
                    }

                }
				echo json_encode((array('data' =>array('Planned' => $plannd_userStories, 'Accepted' => $total_count))));
				
				break;
    }
}
//   }















?>