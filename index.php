<?php
//echo phpinfo();
error_reporting(E_ALL);
/** W3ebServices Index * */
/**

 * File to handle all API requests

 * Accepts GET and GET

 * Each request will be identified by TAG

 * Response will be JSON data

/**

 * check for GET request 

 */
		
//header('Content-Type: application/x-www-form-urlencoded');
if (isset($_REQUEST['rule']) && $_REQUEST['rule'] != '') {

    // get tag

    $tag = $_REQUEST['rule'];
    // include db handler
   
    include_once 'DB_Functions.php';
	
    $db = new DB_Functions('hello World');
	
    // response Array
    $response = array("tag" => $tag, "success" => 0, "error" => 0);
	
    switch($tag){
	
        case 'register':
           
	    
	    $f_name       = $_REQUEST['f_name'];
        $l_name       = $_REQUEST['l_name'];
        $mobile       = $_REQUEST['mobile'];
	    $device_type  = $_REQUEST['device_type'];
	    $device_id    = $_REQUEST['device_id'];
	    $email        = $_REQUEST['email'];
	    $gender       = $_REQUEST['gender'];
	    $dob          = $_REQUEST['dob'];
	    $password     = $_REQUEST['password'];
	    	
	    $user_data = $db->register($f_name,$l_name,$mobile,$device_type,$device_id,$email,$gender,$dob,$password);
		
	    echo json_encode($user_data);
	    break;
			
	case 'login':
			
		$device_type  = $_REQUEST['device_type'];
        $device_id    = $_REQUEST['device_id'];
        $mobile       = $_REQUEST['mobile'];
	    $password     = $_REQUEST['password'];
	    	
	    $login_data = $db->login($mobile,$device_type,$device_id,$password);
		
	    echo json_encode($login_data);
	    break;
		
	case 'getQuestions':
			
		$device_type  = $_REQUEST['device_type'];
        $device_id    = $_REQUEST['device_id'];
        $user_id      = $_REQUEST['user_id'];
		$cat_id       = $_REQUEST['cat_id'];
		
		$question_data = $db->get_questions($user_id,$device_type,$device_id,$cat_id);		
	    echo json_encode($question_data);
	    break;
	
	case 'saveData':
		
		$device_type  = $_REQUEST['device_type'];
        $device_id    = $_REQUEST['device_id'];
		$user_id      = $_REQUEST['user_id'];
        $timestamp    = $_REQUEST['timestamp'];
		$questions    = $_REQUEST['questions'];
		$cat_id    = $_REQUEST['cat_id'];
		$saveData = $db->save_data($device_type, $device_id,$user_id,$timestamp,$questions,$cat_id);

		echo json_encode($saveData);
		break;
	
	case 'getRating':
		
		$device_type  = $_REQUEST['device_type'];
        $device_id    = $_REQUEST['device_id'];
		$user_id      = $_REQUEST['user_id'];
		$cat_id       = $_REQUEST['cat_id'];
		$timestamp    = $_REQUEST['timestamp'];
        
		$getRating = $db->get_rating($device_type, $device_id,$user_id, $cat_id, $timestamp);

		echo json_encode($getRating);
		break;
	
	case 'forgetPassword':
		
		$device_type  = $_REQUEST['device_type'];
        $device_id    = $_REQUEST['device_id'];
		$mobile       = $_REQUEST['mobile'];
		
		$forgetPassword = $db->forget_password($device_type, $device_id, $mobile);
		echo json_encode($forgetPassword);
		break;
			
	case 'editProfile':
		
		$device_type  = $_REQUEST['device_type'];
        $device_id    = $_REQUEST['device_id'];
		$mobile       = $_REQUEST['mobile'];
		$f_name       = $_REQUEST['f_name'];
		$l_name       = $_REQUEST['l_name'];
		$pswd         = $_REQUEST['password'];
		$email        = $_REQUEST['email'];
		
		$editProfile = $db->edit_profile($device_type, $device_id, $mobile,$f_name,$l_name,$pswd,$email);
		echo json_encode($editProfile);
		break;
		
	######### API FOR LEVEL 2 #########
	
	case 'levelTwoQues':
		
		$device_type  = $_REQUEST['device_type'];
        $device_id    = $_REQUEST['device_id'];
        $user_id      = $_REQUEST['user_id'];
		$cat_id       = $_REQUEST['cat_id'];
		
		$levelTwoQuestion_data = $db->get_levelTwoQuestions($user_id,$device_type,$device_id,$cat_id);		
	    echo json_encode($levelTwoQuestion_data);
	    break;
	
	case 'levelTwoSaveData':
		
		$device_type  = $_REQUEST['device_type'];
        $device_id    = $_REQUEST['device_id'];
		$user_id      = $_REQUEST['user_id'];
        $timestamp    = time();//$_REQUEST['timestamp'];
		$questions    = $_REQUEST['questions'];
		$cat_id       = $_REQUEST['cat_id'];
		
		$saveData = $db->level_two_save_data($device_type, $device_id, $user_id, $timestamp, $questions, $cat_id);

		echo json_encode($saveData);
		break;
		
	case 'levelTwoRatings':
		
		$device_type  = $_REQUEST['device_type'];
        $device_id    = $_REQUEST['device_id'];
		$user_id      = $_REQUEST['user_id'];
		$cat_id       = $_REQUEST['cat_id'];
		$timestamp    = time(); //$_REQUEST['timestamp'];
		
		$levelTwoRatings_data = $db->get_levelTwoRatings($device_type, $device_id, $user_id, $cat_id, $timestamp);		
	    echo json_encode($levelTwoRatings_data);
	    break;
	
	######### API FOR LEVEL 3 #########
	
	case 'levelThreeQues':
		
		$device_type  = $_REQUEST['device_type'];
        $device_id    = $_REQUEST['device_id'];
        $user_id      = $_REQUEST['user_id'];
		$cat_id       = $_REQUEST['cat_id'];
		
		$levelThreeQuestion_data = $db->get_levelThreeQuestions($user_id,$device_type,$device_id,$cat_id);		
	    echo json_encode($levelThreeQuestion_data);
	    break;
	
	case 'levelThreeSaveData':
		
		$device_type  = $_REQUEST['device_type'];
        $device_id    = $_REQUEST['device_id'];
		$user_id      = $_REQUEST['user_id'];
        $timestamp    = $_REQUEST['timestamp'];
		$questions    = $_REQUEST['questions'];
		$cat_id       = $_REQUEST['cat_id'];
		
		$saveData = $db->level_three_save_data($device_type, $device_id, $user_id, $timestamp, $questions, $cat_id);

		echo json_encode($saveData);
		break;
		
	case 'help':
		
		$device_type  = $_REQUEST['device_type'];
        $device_id    = $_REQUEST['device_id'];
        $user_id      = $_REQUEST['user_id'];
		$helpText     = $_REQUEST['help_text'];
		
		$saveHelpRequest = $db->save_helpRequest($device_type,$device_id,$user_id,$helpText);		
	    echo json_encode($saveHelpRequest);
	    break;
		
	default:
	
		echo "Access Denied";
		break;
			
	} // End of Switch Case
  }else{

	$response = array("tag" => $variable, "success" => 0, "error" => 0);
	echo json_encode($response);
 }
?>

