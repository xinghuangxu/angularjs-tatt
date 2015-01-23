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
$_SESSION['AllUserStories'] = array();

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

            // Sort Releases Array alphabetically
            array_multisort($project_array);

            $output = array('data' => $project_array);
            echo json_encode($output);

            break;

        //Asking for a list of Releases in Rally
        case 'releaseList':

            error_reporting(E_ALL ^ E_STRICT);

            $input = $_GET['input'];
            //Fetched Release information about a project
            $release_list = $rally->find('Release', "(Project.Name = \"{$input}\")", '', '');
            $IterationList = $rally->find('Iteration', "(Project.Name = \"{$input}\")", '', '');

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
            // Sort Releases Array alphabetically
            array_multisort($release_array);

            // Count the length of the Iteration list array 
            $IterationCount = count($IterationList);

            // Inti. an array variable
            $IterationArray = array();

            //Creating object and getting only Iteration names 
            for ($i = 0; $i < $IterationCount; $i++) {
                $IterationArray[$i] = new stdClass();
                $IterationArray[$i] = $IterationList[$i]['_refObjectName'];
            }

            // Add All Value to Iteration Array 
            $IterationArray[$IterationCount] = "All";

            // Sort Releases Array alphabetically
            array_multisort($IterationArray);

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
            }
            //Sort the array of owner lists alphabetically
            array_multisort($List);
            array_unshift($List, "No Owner");
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
                    'SpecificUser' => $specific_user,
                    'iterationList' => $IterationArray));

            echo json_encode($output);

            break;
        //Asking for a Rally Tree of Releases in Rally
        case 'treeData':
            $projname = $_GET['project'];
            $releasename = $_GET['release'];
            $Iteration = $_GET['iteration'];
            $ReleaseName = $releasename;
            $AllUserStories = $rally->find('userstory', "(Project.Name = \"{$projname}\")", '', 'Release,ScheduleState,Iteration,HasParent,Parent,c_ArchitecturalTopicID');
            $ReleasesArray = array();
            $FinalArray = array();
            $FinalArrayWithObject = array();
            $ShortAllStories = array();
            $Map = array();
            $ReleasesCounter = 0;
            for ($i = 0; $i < count($AllUserStories); $i++) {
                if (($releasename == "All" && $Iteration == null) || ($releasename == "All" && $Iteration == "All")) {
                    $FinalArrayWithObject[$i] = new stdClass();
                    $FinalArrayWithObject[$i]->id = $AllUserStories[$i]['_refObjectUUID'];
                    $FinalArrayWithObject[$i]->parent = $AllUserStories[$i]['Parent']['_refObjectUUID'];
                    $FinalArrayWithObject[$i]->text = $AllUserStories[$i]['_refObjectName'];
                    $FinalArrayWithObject[$i]->icon = $AllUserStories[$i]['ScheduleState'];
                    $FinalArrayWithObject[$i]->Blocked = $AllUserStories[$i]['Blocked'];
                    $FinalArrayWithObject[$i]->TopicID = $AllUserStories[$i]['c_ArchitecturalTopicID'];
                    $FinalArrayWithObject[$i]->Iteration = $AllUserStories[$i]['Iteration']['_refObjectName'];
                    $FinalArrayWithObject[$i]->has = $AllUserStories[$i]['HasParent'];
                    if ($FinalArrayWithObject[$i]->parent == null) {
                        $FinalArrayWithObject[$i]->parent = '#';
                    }
                    if ($FinalArrayWithObject[$i]->Blocked == 1) {
                        //Assign icons to the user stories depending on the schedule state
                        if ($FinalArrayWithObject[$i]->icon == "In-Progress") {
                            $FinalArrayWithObject[$i]->icon = "assets/img/icon_In-Progress_Blocked.png";
                        } elseif ($FinalArrayWithObject[$i]->icon == "Defined") {
                            $FinalArrayWithObject[$i]->icon = "assets/img/icon_Defined_Blocked.png";
                        } elseif ($FinalArrayWithObject[$i]->icon == "Completed") {
                            $FinalArrayWithObject[$i]->icon = "assets/img/icon_Completed_Blocked.png";
                        } elseif ($FinalArrayWithObject[$i]->icon == "Accepted") {
                            $FinalArrayWithObject[$i]->icon = "assets/img/icon_Accepted_Blocked.png";
                        }
                    } else {
                        if ($FinalArrayWithObject[$i]->icon == "In-Progress") {
                            $FinalArrayWithObject[$i]->icon = "assets/img/icon_In-Progress.png";
                        } elseif ($FinalArrayWithObject[$i]->icon == "Defined") {
                            $FinalArrayWithObject[$i]->icon = "assets/img/icon_Defined.png";
                        } elseif ($FinalArrayWithObject[$i]->icon == "Completed") {
                            $FinalArrayWithObject[$i]->icon = "assets/img/icon_Completed.png";
                        } elseif ($FinalArrayWithObject[$i]->icon == "Accepted") {
                            $FinalArrayWithObject[$i]->icon = "assets/img/icon_Accepted.png";
                        }
                    }
                } elseif ($releasename != null && $Iteration != null && $releasename != "All" && $Iteration != "All") {
                    if ($AllUserStories[$i]['Release']['_refObjectName'] == $ReleaseName && $AllUserStories[$i]['Iteration']['_refObjectName'] == $Iteration) {
                        $Map[] = $AllUserStories[$i]['_refObjectUUID'];
                        $ReleasesArray[$ReleasesCounter]['id'] = $AllUserStories[$i]['_refObjectUUID'];
                        $ReleasesArray[$ReleasesCounter]['parent'] = $AllUserStories[$i]['Parent']['_refObjectUUID'];
                        $ReleasesArray[$ReleasesCounter]['text'] = $AllUserStories[$i]['_refObjectName'];
                        $ReleasesArray[$ReleasesCounter]['icon'] = $AllUserStories[$i]['ScheduleState'];
                        $ReleasesArray[$ReleasesCounter]['Blocked'] = $AllUserStories[$i]['Blocked'];
                        $ReleasesArray[$ReleasesCounter]['TopicID'] = $AllUserStories[$i]['c_ArchitecturalTopicID'];
                        $ReleasesArray[$ReleasesCounter]['Iteration'] = $AllUserStories[$i]['Iteration']['_refObjectName'];
                        $ReleasesArray[$ReleasesCounter]['has'] = $AllUserStories[$i]['HasParent'];
                        $ReleasesCounter++;
                    } else {
                        $ShortAllStories[$AllUserStories[$i]['_refObjectUUID']]['id'] = $AllUserStories[$i]['_refObjectUUID'];
                        $ShortAllStories[$AllUserStories[$i]['_refObjectUUID']]['parent'] = $AllUserStories[$i]['Parent']['_refObjectUUID'];
                        $ShortAllStories[$AllUserStories[$i]['_refObjectUUID']]['text'] = $AllUserStories[$i]['_refObjectName'];
                        $ShortAllStories[$AllUserStories[$i]['_refObjectUUID']]['icon'] = $AllUserStories[$i]['ScheduleState'];
                        $ShortAllStories[$AllUserStories[$i]['_refObjectUUID']]['Blocked'] = $AllUserStories[$i]['Blocked'];
                        $ShortAllStories[$AllUserStories[$i]['_refObjectUUID']]['TopicID'] = $AllUserStories[$i]['c_ArchitecturalTopicID'];
                        $ShortAllStories[$AllUserStories[$i]['_refObjectUUID']]['Iteration'] = $AllUserStories[$i]['Iteration']['_refObjectName'];
                        $ShortAllStories[$AllUserStories[$i]['_refObjectUUID']]['has'] = $AllUserStories[$i]['HasParent'];
                        //$ShortAllStoriesCounter++;
                    }
                } elseif (($releasename != null && $Iteration == null) || ($releasename != null && $Iteration == "All")) {
                    if ($AllUserStories[$i]['Release']['_refObjectName'] == $ReleaseName) {
                        $Map[] = $AllUserStories[$i]['_refObjectUUID'];
                        $ReleasesArray[$ReleasesCounter]['id'] = $AllUserStories[$i]['_refObjectUUID'];
                        $ReleasesArray[$ReleasesCounter]['parent'] = $AllUserStories[$i]['Parent']['_refObjectUUID'];
                        $ReleasesArray[$ReleasesCounter]['text'] = $AllUserStories[$i]['_refObjectName'];
                        $ReleasesArray[$ReleasesCounter]['icon'] = $AllUserStories[$i]['ScheduleState'];
                        $ReleasesArray[$ReleasesCounter]['Blocked'] = $AllUserStories[$i]['Blocked'];
                        $ReleasesArray[$ReleasesCounter]['TopicID'] = $AllUserStories[$i]['c_ArchitecturalTopicID'];
                        $ReleasesArray[$ReleasesCounter]['Iteration'] = $AllUserStories[$i]['Iteration']['_refObjectName'];
                        $ReleasesArray[$ReleasesCounter]['has'] = $AllUserStories[$i]['HasParent'];
                        $ReleasesCounter++;
                    } else {
                        $ShortAllStories[$AllUserStories[$i]['_refObjectUUID']]['id'] = $AllUserStories[$i]['_refObjectUUID'];
                        $ShortAllStories[$AllUserStories[$i]['_refObjectUUID']]['parent'] = $AllUserStories[$i]['Parent']['_refObjectUUID'];
                        $ShortAllStories[$AllUserStories[$i]['_refObjectUUID']]['text'] = $AllUserStories[$i]['_refObjectName'];
                        $ShortAllStories[$AllUserStories[$i]['_refObjectUUID']]['icon'] = $AllUserStories[$i]['ScheduleState'];
                        $ShortAllStories[$AllUserStories[$i]['_refObjectUUID']]['Blocked'] = $AllUserStories[$i]['Blocked'];
                        $ShortAllStories[$AllUserStories[$i]['_refObjectUUID']]['TopicID'] = $AllUserStories[$i]['c_ArchitecturalTopicID'];
                        $ShortAllStories[$AllUserStories[$i]['_refObjectUUID']]['Iteration'] = $AllUserStories[$i]['Iteration']['_refObjectName'];
                        $ShortAllStories[$AllUserStories[$i]['_refObjectUUID']]['has'] = $AllUserStories[$i]['HasParent'];
                    }
                } elseif (($Iteration != null && $releasename == null) || ($Iteration != null && $releasename == "All")) {
                    if ($AllUserStories[$i]['Iteration']['_refObjectName'] == $Iteration) {
                        $Map[] = $AllUserStories[$i]['_refObjectUUID'];
                        $ReleasesArray[$ReleasesCounter]['id'] = $AllUserStories[$i]['_refObjectUUID'];
                        $ReleasesArray[$ReleasesCounter]['parent'] = $AllUserStories[$i]['Parent']['_refObjectUUID'];
                        $ReleasesArray[$ReleasesCounter]['text'] = $AllUserStories[$i]['_refObjectName'];
                        $ReleasesArray[$ReleasesCounter]['icon'] = $AllUserStories[$i]['ScheduleState'];
                        $ReleasesArray[$ReleasesCounter]['Blocked'] = $AllUserStories[$i]['Blocked'];
                        $ReleasesArray[$ReleasesCounter]['TopicID'] = $AllUserStories[$i]['c_ArchitecturalTopicID'];
                        $ReleasesArray[$ReleasesCounter]['Iteration'] = $AllUserStories[$i]['Iteration']['_refObjectName'];
                        $ReleasesArray[$ReleasesCounter]['has'] = $AllUserStories[$i]['HasParent'];
                        $ReleasesCounter++;
                    } else {
                        $ShortAllStories[$AllUserStories[$i]['_refObjectUUID']]['id'] = $AllUserStories[$i]['_refObjectUUID'];
                        $ShortAllStories[$AllUserStories[$i]['_refObjectUUID']]['parent'] = $AllUserStories[$i]['Parent']['_refObjectUUID'];
                        $ShortAllStories[$AllUserStories[$i]['_refObjectUUID']]['text'] = $AllUserStories[$i]['_refObjectName'];
                        $ShortAllStories[$AllUserStories[$i]['_refObjectUUID']]['icon'] = $AllUserStories[$i]['ScheduleState'];
                        $ShortAllStories[$AllUserStories[$i]['_refObjectUUID']]['Blocked'] = $AllUserStories[$i]['Blocked'];
                        $ShortAllStories[$AllUserStories[$i]['_refObjectUUID']]['TopicID'] = $AllUserStories[$i]['c_ArchitecturalTopicID'];
                        $ShortAllStories[$AllUserStories[$i]['_refObjectUUID']]['Iteration'] = $AllUserStories[$i]['Iteration']['_refObjectName'];
                        $ShortAllStories[$AllUserStories[$i]['_refObjectUUID']]['has'] = $AllUserStories[$i]['HasParent'];
                        //$ShortAllStoriesCounter++;
                    }
                }
            }

            if (count($AllUserStories) == 0) {
                $var = "No User stories";
                $output = array('data' => $var);
                echo json_encode($output);
            } elseif (count($FinalArrayWithObject) == count($AllUserStories)) {
                $output = array('data' => $FinalArrayWithObject);
                echo json_encode($output);
            } else {
                $FinalArray = $ReleasesArray;
                $Counter = count($FinalArray);
                for ($i = 0; $i < $Counter; $i++) {
                    if ($FinalArray[$i]['parent'] != "" && $FinalArray[$i]['has'] == 1) {
                        if (!in_array($FinalArray[$i]['parent'], $Map)) {
                            $Map[] = $FinalArray[$i]['parent'];
                            $FinalArray[] = array("id" => $ShortAllStories[$FinalArray[$i]['parent']]['id'],
                                "parent" => $ShortAllStories[$FinalArray[$i]['parent']]['parent'],
                                "text" => $ShortAllStories[$FinalArray[$i]['parent']]['text'],
                                "icon" => $ShortAllStories[$FinalArray[$i]['parent']]['icon'],
                                "Blocked" => $ShortAllStories[$FinalArray[$i]['parent']]['Blocked'],
                                "TopicID" => $ShortAllStories[$FinalArray[$i]['parent']]['TopicID'],
                                "Iteration" => $ShortAllStories[$FinalArray[$i]['parent']]['Iteration'],
                                "has" => $ShortAllStories[$FinalArray[$i]['parent']]['has']);
                            $Counter++;
                        }
                        $FinalArrayWithObject[$i] = new stdClass();
                        $FinalArrayWithObject[$i]->id = $FinalArray[$i]['id'];
                        $FinalArrayWithObject[$i]->parent = $FinalArray[$i]['parent'];
                        $FinalArrayWithObject[$i]->text = $FinalArray[$i]['text'];
                        $FinalArrayWithObject[$i]->icon = $FinalArray[$i]['icon'];
                        $FinalArrayWithObject[$i]->Blocked = $FinalArray[$i]['Blocked'];
                        $FinalArrayWithObject[$i]->TopicID = $FinalArray[$i]['TopicID'];
                        $FinalArrayWithObject[$i]->Iteration = $FinalArray[$i]['Iteration'];
                        $FinalArrayWithObject[$i]->has = $FinalArray[$i]['has'];
                        if ($FinalArrayWithObject[$i]->parent == null) {
                            $FinalArrayWithObject[$i]->parent = '#';
                        }
                        if ($FinalArrayWithObject[$i]->Blocked == 1) {
                            //Assign icons to the user stories depending on the schedule state
                            if ($FinalArrayWithObject[$i]->icon == "In-Progress") {
                                $FinalArrayWithObject[$i]->icon = "assets/img/icon_In-Progress_Blocked.png";
                            } elseif ($FinalArrayWithObject[$i]->icon == "Defined") {
                                $FinalArrayWithObject[$i]->icon = "assets/img/icon_Defined_Blocked.png";
                            } elseif ($FinalArrayWithObject[$i]->icon == "Completed") {
                                $FinalArrayWithObject[$i]->icon = "assets/img/icon_Completed_Blocked.png";
                            } elseif ($FinalArrayWithObject[$i]->icon == "Accepted") {
                                $FinalArrayWithObject[$i]->icon = "assets/img/icon_Accepted_Blocked.png";
                            }
                        } else {
                            if ($FinalArrayWithObject[$i]->icon == "In-Progress") {
                                $FinalArrayWithObject[$i]->icon = "assets/img/icon_In-Progress.png";
                            } elseif ($FinalArrayWithObject[$i]->icon == "Defined") {
                                $FinalArrayWithObject[$i]->icon = "assets/img/icon_Defined.png";
                            } elseif ($FinalArrayWithObject[$i]->icon == "Completed") {
                                $FinalArrayWithObject[$i]->icon = "assets/img/icon_Completed.png";
                            } elseif ($FinalArrayWithObject[$i]->icon == "Accepted") {
                                $FinalArrayWithObject[$i]->icon = "assets/img/icon_Accepted.png";
                            }
                        }
                    } else {
                        $FinalArrayWithObject[$i] = new stdClass();
                        $FinalArrayWithObject[$i]->id = $FinalArray[$i]['id'];
                        $FinalArrayWithObject[$i]->parent = $FinalArray[$i]['parent'];
                        $FinalArrayWithObject[$i]->text = $FinalArray[$i]['text'];
                        $FinalArrayWithObject[$i]->icon = $FinalArray[$i]['icon'];
                        $FinalArrayWithObject[$i]->Blocked = $FinalArray[$i]['Blocked'];
                        $FinalArrayWithObject[$i]->TopicID = $FinalArray[$i]['TopicID'];
                        $FinalArrayWithObject[$i]->Iteration = $FinalArray[$i]['Iteration'];
                        $FinalArrayWithObject[$i]->has = $FinalArray[$i]['has'];
                        if ($FinalArrayWithObject[$i]->parent == null) {
                            $FinalArrayWithObject[$i]->parent = '#';
                        }
                        if ($FinalArrayWithObject[$i]->Blocked == 1) {
                            //Assign icons to the user stories depending on the schedule state
                            if ($FinalArrayWithObject[$i]->icon == "In-Progress") {
                                $FinalArrayWithObject[$i]->icon = "assets/img/icon_In-Progress_Blocked.png";
                            } elseif ($FinalArrayWithObject[$i]->icon == "Defined") {
                                $FinalArrayWithObject[$i]->icon = "assets/img/icon_Defined_Blocked.png";
                            } elseif ($FinalArrayWithObject[$i]->icon == "Completed") {
                                $FinalArrayWithObject[$i]->icon = "assets/img/icon_Completed_Blocked.png";
                            } elseif ($FinalArrayWithObject[$i]->icon == "Accepted") {
                                $FinalArrayWithObject[$i]->icon = "assets/img/icon_Accepted_Blocked.png";
                            }
                        } else {
                            if ($FinalArrayWithObject[$i]->icon == "In-Progress") {
                                $FinalArrayWithObject[$i]->icon = "assets/img/icon_In-Progress.png";
                            } elseif ($FinalArrayWithObject[$i]->icon == "Defined") {
                                $FinalArrayWithObject[$i]->icon = "assets/img/icon_Defined.png";
                            } elseif ($FinalArrayWithObject[$i]->icon == "Completed") {
                                $FinalArrayWithObject[$i]->icon = "assets/img/icon_Completed.png";
                            } elseif ($FinalArrayWithObject[$i]->icon == "Accepted") {
                                $FinalArrayWithObject[$i]->icon = "assets/img/icon_Accepted.png";
                            }
                        }
                    }
                }
                $output = array('data' => $FinalArrayWithObject);
                echo json_encode($output);
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
            $icon = $ScheduleState;

            if ($blocked == 1) {
                if ($icon == "In-Progress") {
                    $icon = "assets/img/icon_In-Progress_Blocked.png";
                } elseif ($icon == "Defined") {
                    $icon = "assets/img/icon_Defined_Blocked.png";
                } elseif ($icon == "Completed") {
                    $icon = "assets/img/icon_Completed_Blocked.png";
                } elseif ($icon == "Accepted") {
                    $icon = "assets/img/icon_Accepted_Blocked.png";
                }
            } else {
                if ($icon == "In-Progress") {
                    $icon = "assets/img/icon_In-Progress.png";
                } elseif ($icon == "Defined") {
                    $icon = "assets/img/icon_Defined.png";
                } elseif ($icon == "Completed") {
                    $icon = "assets/img/icon_Completed.png";
                } elseif ($icon == "Accepted") {
                    $icon = "assets/img/icon_Accepted.png";
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
                    $icon = "assets/img/icon_In-Progress_Blocked.png";
                } elseif ($icon == "Defined") {
                    $icon = "assets/img/icon_Defined_Blocked.png";
                } elseif ($icon == "Completed") {
                    $icon = "assets/img/icon_Completed_Blocked.png";
                } elseif ($icon == "Accepted") {
                    $icon = "assets/img/icon_Accepted_Blocked.png";
                }
            } else {
                if ($icon == "In-Progress") {
                    $icon = "assets/img/icon_In-Progress.png";
                } elseif ($icon == "Defined") {
                    $icon = "assets/img/icon_Defined.png";
                } elseif ($icon == "Completed") {
                    $icon = "assets/img/icon_Completed.png";
                } elseif ($icon == "Accepted") {
                    $icon = "assets/img/icon_Accepted.png";
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
            $Iteration_list = $rally->find('Iteration', "(Project.Name = \"{$variable}\")", '', '');

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
        case 'boxcarChildren':  //test
            //Unsetting the session variables
            $file = 'boxcarChildren_1.json';
            if (file_exists($file)) {
                header('Content-Type: application/json');
                header('Content-Disposition: attachment; filename=' . basename($file));
                header('Content-Length: ' . filesize($file));
                readfile($file);
                exit;
            }
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

              $ID = $userstories_Epic[0]['_refObjectUUID']; */


            $ID = $_GET['input'];

            $userstories_Epic = $rally->get('HierarchicalRequirement', "/$ID");

            $c = 0;
            $child = array();
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

            if ($plannd_userStories == null) {
                $plannd_userStories = '0';
            }

            echo json_encode((array('data' => array('Planned' => $plannd_userStories, 'Accepted' => $total_count))));

            break;
    }
}
//   }
?>