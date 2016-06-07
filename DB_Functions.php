<?php
class DB_Functions {

    private $db;
    private $nf;
    
    function __construct($test) {
		
		$con = mysql_connect("localhost","learnon4_healthA","learnon4_healthA") or die("Unable to connect to MySQL". mysql_error());
		mysql_select_db("learnon4_healthyapp",$con)
		or die("Could not select examples");
    }
	
    function curdate() {    
	    
		$now = new DateTime();
		return $now->format('Y-m-d H:i:s');  
    }
	
	/*
    @name: register
    @description: To save data
    @author: Soumya
    @date: 4th May 2016    
    */

    public function register($f_name,$l_name,$mobile,$device_type,$device_id,$email,$gender,$dob,$password) {
		
	
		$response = array();
		
		// To check if Email Id already Exists
			
		$sql   = "select * from users where mobile = ".$mobile;
		$res   = mysql_query($sql);
		$count = @mysql_num_rows($res);
		
		if($count == 0){
		
			$isActive    = 1;
			$created     = $this->curdate();
			$modified    = $this->curdate();
			$md5Password = md5($password);
			$dob  = date('Y-m-d', strtotime($dob));
		
			$results = mysql_query("INSERT INTO users (f_name, l_name, mobile, device_type, device_id ,email , password, gender, dob, created_date, modified_date, is_active) VALUES ('$f_name','$l_name','$mobile',
			$device_type,'$device_id','$email', '$md5Password', '$gender', '$dob' , '$created','$modified',$isActive)");   
			
			$id = mysql_insert_id();
		
			if(!empty($id)){
			    $usql   = "select * from users where id = ".$id;
				$ures   = mysql_query($usql);
				$ucount = @mysql_num_rows($ures);	
				
				if($ucount > 0){
					$userData = mysql_fetch_assoc($ures);
				}
			    ##### Code to send OTP Message Using Nimbus ######
				$OTPData = array();
				/* Code to generate OTP */
				$digits = 5;
				$otp_value = rand(pow(10, $digits - 1), pow(10, $digits) - 1);
				$OTPData['url'] = 'http://nimbusit.net/api.php?';
				$OTPData['username'] = 't4anshapi'; // Login Id of the account
				$OTPData['password'] = '831486'; // password of the account
				$OTPData['sender']   = 'APISMS'; // Sender Id mentioned in Account
				$OTPData['sendto']   = $mobile; // Mobile No. of Receipient
				$OTPData['message']  = $otp_value; //'Your OTP is :'; //
				$OTPData['dlrUrl']   = 'www.learn-online.in/healthy_api/index.php?rule="saveOTP"&logID=$logID$%26phNo=$phNO$%26result=$result$';
				
				$Curl_Url = $OTPData['url'].'username='.$OTPData['username'].'&password='.$OTPData['password'].'&sender='.$OTPData['sender'].'&sendto=91'.
				$OTPData['sendto'].'&message='.$OTPData['message'];
				//.'&dlrUrl='.$OTPData['dlrUrl'];
				
				###### Send OTP #######
				// Get cURL resource
				$curl = curl_init();
				// Set some options - we are passing in a useragent too here
				curl_setopt_array($curl, array(
					CURLOPT_RETURNTRANSFER => 1,
					CURLOPT_URL => $Curl_Url,
					CURLOPT_USERAGENT => 'Codular Sample cURL Request'
				));
				// Send the request & save response to $resp
				$resp = curl_exec($curl);
				// Close request to clear up some resources
				curl_close($curl);
				// echo '<pre>';
				// print_r($resp);
				// die;
				if($resp['LogID'] != ''){
					$response['success']['status']  = 1;
					$response['success']['message'] = 'User is successfully registered.';
					$response['success']['user_id'] = $id;
					$response['success']['userData']= $userData;
					$response['success']['catData'] = $this->getCategoryList();
					$response['success']['OTP'] = $otp_value;
				}else{
					$response['success']['status']  = 1;
					$response['success']['message'] = 'User is successfully registered, but unable to send OTP.';
					$response['success']['user_id'] = $id;
					$response['success']['userData']= $userData;
					$response['success']['catData'] = $this->getCategoryList();
					
				}
				
			  
			}else{
			  $response['error']['status']  = 0;
			  $response['error']['message'] = 'There is some error in saving data.';
			}
		}else{
			$response['error']['status']  = 0;
			$response['error']['message'] = 'This mobile no is already registered.';
		}
		return $response;
    }
    
	/*
    @name: login
    @description: To save data
    @author: Soumya
    @date: 4th May 2016    
    */

    public function login($mobile,$device_type,$device_id,$password) {
		
		$response = array();
		$encryptedPassword = md5($password);
		
		// To check if Email Id already Exists
		$sql   = "select * from users where mobile = ".$mobile." AND password = '$encryptedPassword'";
		$res   = mysql_query($sql);
		$count = @mysql_num_rows($res);
		
		if($count > 0){
			
			$updateQuery = mysql_query("Update users SET device_type = ". $device_type. ", device_id =  '$device_id' where mobile = ".$mobile);
			if($updateQuery){
				$selectQuery = mysql_query("select * from users where mobile = ".$mobile);
				$rows = mysql_fetch_assoc($selectQuery);					
				$userdetails = $rows;
				
				if(!empty($userdetails)){
					
					$response['success']['status']   = 1;
					$response['success']['message']  = 'Successful login.';
					$response['success']['userData'] = $userdetails;
					$response['success']['catData'] = $this->getCategoryList();
					// select questions answered by user
					$Qsql   = "select uq.*, q.id, q.question from user_questions as uq inner join questions as q on (uq.question_id = q.id) where uq.user_id = ".$id." ";
					$Qres   = mysql_query($Qsql);
					$Qcount = @mysql_num_rows($Qres);
					
					if($Qcount > 0){
						
						$allQues = mysql_fetch_assoc($Qres);
						$response['success']['questions'] = $allQues;
					}else{
						$response['success']['questions'] = "No Questions";
					}
					  
				}else{
				  $response['error']['status']  = 0;
				  $response['error']['message'] = 'No user details.';
				}
			}
		}else{
			$response['error']['status']  = 0;
			$response['error']['message'] = 'User not found';
		}
		return $response;
    }
	
	public function get_questions($user_id,$device_type,$device_id,$cat_id) {
		
		$response = array();
		
		$sql   = "select ques.id,ques.question,ques.advice,ques.category_id, cat.name as category_name from questions as ques inner join categories as cat on (ques.category_id = cat.id) where ques.category_id = ".$cat_id;
		$res   = mysql_query($sql);
		$count = mysql_num_rows($res);
		    
		if($count > 0){
			$questionsArray = [];
		
			while($questions = mysql_fetch_assoc($res)){
				
				array_push($questionsArray,$questions);
			}
			
			$response['success']['status']  = 1;
			$response['success']['message'] = 'Successful.';
			$response['success']['questions'][] = $questionsArray;
		}else{
			$response['error']['status']  = 0;
			$response['error']['message'] = 'No questions for this category';
		}
		return $response;
    }
	
	public function save_data($device_type, $device_id, $user_id, $timestamp, $questions = array(), $cat_id) {
		
		$response  = array();
		
		// To check if User Exists
		$usql   = mysql_query("select * from users where id = ".$user_id);
		$ucount = @mysql_num_rows($usql);
		
		if($ucount > 0){
			$userData = mysql_fetch_assoc($usql);
			$user_id  = $userData['id'];
		
			//$deleteAll = mysql_query("Delete from user_questions where user_id = '$user_id'");
			
			$created   = date('Y-m-d G:i:s');
			foreach($questions as $ques){
				
				$q_id    = $ques['q_id'];
				$rate    = $ques['rate'];
				if($rate != '0'){
					$results = mysql_query("INSERT INTO user_questions (category_id, user_id, question_id, rate, created_date,rate_timestamp) VALUES ('$cat_id','$user_id','$q_id','$rate',
					'$created', '$timestamp')"); 
				}
			}
			
			$response['success']['status']  = 1;
			$response['success']['message'] = 'Data Saved Successfully';

		}else{
			$response['error']['status']  = 0;
			$response['error']['message'] = 'User not registered.';
		}
		return $response;
    }
	
	public function get_rating($device_type, $device_id, $user_id, $cat_id, $timestamp) {
		
		$response = array();
		$ratingTimestamp = date('Y-m-d', $timestamp);
		$startDate = $ratingTimestamp. ' 00:00:00';
		$endDate   = $ratingTimestamp. ' 23:59:59'; 
		// To check if User Exists
		$usql   = mysql_query("select * from users where id = ".$user_id." AND device_id = '$device_id' AND device_type = ". $device_type );
		$ucount = @mysql_num_rows($usql);
		
		if($ucount > 0){
			
			$quesQuery = mysql_query("select uq.id, uq.user_id, uq.question_id, uq.rate, uq.created_date, qu.question from user_questions as uq inner join questions as qu on (uq.question_id = qu.id) where uq.user_id = ". $user_id . " AND uq.rate_timestamp	>= '$timestamp' group by uq.question_id order by uq.rate asc");
			$quesCount = mysql_num_rows($quesQuery);
			
			if($quesCount > 0){
				$allQuestions = array();
				while($ques = mysql_fetch_assoc($quesQuery)){
					if (in_array($ques['rate'], [1,2,3])){
						$ques['rate'] = '1-3';
					}else if(in_array($ques['rate'], [4,5,6])){
						$ques['rate'] = '4-6';
					}else if(in_array($ques['rate'], [7,8,9,10])){
						$ques['rate'] = '7-10';
					} 
					array_push($allQuestions, $ques);	
				}
				$response['success']['status']    = 1;
				$response['success']['message']   = 'All Questions';
				$response['success']['questions'] = $allQuestions;
			}else{
				$response['success']['status']    = 0;
				$response['success']['message']   = 'No Ratings for this User.';
				
			}
		}else{
			$response['error']['status']  = 0;
			$response['error']['message'] = 'User not registered.';
		}	
		return $response;
    }
	
	/**/
	
	public function forget_password($device_type, $device_id, $mobile){
		$response = array();
		// To check if User Exists
		$usql   = mysql_query("select * from users where mobile = ".$mobile);
		$ucount = @mysql_num_rows($usql);
		
		if($ucount > 0){
			##### Code to send OTP Message Using Nimbus ######
			
			$OTPData = array();
			
			/* Code to generate OTP */
				$digits = 5;
				$otp_value = rand(pow(10, $digits - 1), pow(10, $digits) - 1);
				$OTPData['url'] = 'http://nimbusit.net/api.php?';
				$OTPData['username'] = 't4anshapi'; // Login Id of the account
				$OTPData['password'] = '831486'; // password of the account
				$OTPData['sender']   = 'APISMS'; // Sender Id mentioned in Account
				$OTPData['sendto']   = $mobile; // Mobile No. of Receipient
				$OTPData['message']  = $otp_value; //'Your Password is :'; //
				
				
				$Curl_Url = $OTPData['url'].'username='.$OTPData['username'].'&password='.$OTPData['password'].'&sender='.$OTPData['sender'].'&sendto=91'.
				$OTPData['sendto'].'&message='.$OTPData['message'];
				
				
				###### Send OTP #######
				// Get cURL resource
				$curl = curl_init();
				// Set some options - we are passing in a useragent too here
				curl_setopt_array($curl, array(
					CURLOPT_RETURNTRANSFER => 1,
					CURLOPT_URL => $Curl_Url,
					CURLOPT_USERAGENT => 'Codular Sample cURL Request'
				));
				// Send the request & save response to $resp
				$resp = curl_exec($curl);
				// Close request to clear up some resources
				curl_close($curl);
				
				if($resp['LogID'] != ''){
					/* Update password in database */
					$encryptPswd = md5($otp_value); 
					$updatePswd  = mysql_query("Update users SET password= '$encryptPswd' where mobile = ".$mobile);
					if($updatePswd){
						$response['success']['status']  = 1;
						$response['success']['message'] = 'Password successfully changed';
						$response['success']['new_password'] = $otp_value;
					}else{
						$response['error']['status']  = 0;
						$response['error']['message'] = 'Unable to update password.Please try again.';
					}
				}
		}else{
			$response['error']['status']  = 0;
			$response['error']['message'] = 'User not registered.';
		}	
		return $response;
	}
	
	/*
    @name: edit_profile
    @description: To edit profile
    @author: Soumya Mishra
    @date: 10th May 2016    
    */
	public function edit_profile($device_type, $device_id, $mobile,$f_name,$l_name,$pswd,$email){
		
		$response = array();
		
		// To check if User Exists
		$usql   = mysql_query("select * from users where mobile = ".$mobile);
		$ucount = @mysql_num_rows($usql);
		
		if($ucount > 0){
			$encryptPswd = md5($pswd);
			$updateQuery = mysql_query("Update users SET f_name = '$f_name' , l_name =  '$l_name', password = '$encryptPswd', email = '$email' where mobile = ".$mobile);	
			if($updateQuery){
				$response['success']['status']    = 1;
				$response['success']['message']   = 'Profile updated successfully.';
			}else{
				$response['error']['status']    = 2;
				$response['error']['message']   = 'Unable to update profile.';
			}
		}else{
			$response['error']['status']    = 0;
			$response['error']['message']   = 'User not registered.';
		}
		return $response;
	}
	
	/*
    @name: categoryList
    @description: To get list of categories
    @author: Soumya Mishra
    @date: 7th May 2016    
    */
	
	private function getCategoryList(){
		
		$catQuery  = mysql_query("Select id,name from categories");
		$catCount  = mysql_num_rows($catQuery);
		
		if($catCount > 0){
			$catArray = array();
			while($catData = mysql_fetch_assoc($catQuery)){
				array_push($catArray, $catData);
			}
			return $catArray;
		}
	}
	
	/*
    @name: get_levelTwoQuestions
    @description: To get level two questions 
    @author: Soumya Mishra
    @date: 8th May 2016    
    */
	public function get_levelTwoQuestions($user_id,$device_type,$device_id,$cat_id) {
		
		$response = array();
		
		$sql   = "select ques.id, ques.link , ques.advice , ques.category_id, cat.name as category_name from level_two_questions
                 as ques inner join categories as cat on (ques.category_id = cat.id) where ques.category_id = ".$cat_id;
		$res   = mysql_query($sql);
		$count = mysql_num_rows($res);
		    
		if($count > 0){
			$questionsArray = [];
			
			while($questions = mysql_fetch_assoc($res)){
				$questions['link']   = $_SERVER['HTTP_HOST'].'/healthy_api/'.$questions['link']; 
				$questions['advice'] = $_SERVER['HTTP_HOST'].'/healthy_api/'.$questions['advice']; 
				array_push($questionsArray,$questions);
			}
			$response['success']['status']    = 1;
			$response['success']['message']   = 'Successful.';
			$response['success']['questions'] = $questionsArray;
			
		}else{
			$response['error']['status']  = 0; 
			$response['error']['message'] = 'No second level questions for this category';
		}
		return $response;
    }
	
	/*
    @name: level_two_save_data
    @description: To get level two save data 
    @author: Soumya Mishra
    @date: 8th May 2016    
    */
	public function level_two_save_data($device_type, $device_id, $user_id, $timestamp, $questions = array(), $cat_id) {
		
		$response  = array();
		
		// To check if User Exists
		$usql   = mysql_query("select * from users where id = ".$user_id." AND device_id = '$device_id' AND device_type = '$device_type' ");
		$ucount = @mysql_num_rows($usql);
		
		if($ucount > 0){
			$userData = mysql_fetch_assoc($usql);
			$user_id  = $userData['id'];
		
			$created   = date('Y-m-d H:i:s' , $timestamp);
			
			foreach($questions as $ques) {
				
				$q_id    = $ques['q_id'];
				$rate    = $ques['rate'];
				if($rate != 0){
					$results = mysql_query("INSERT INTO level_two_user_questions (category_id, user_id, question_id, rate , created_date) VALUES ($cat_id, $user_id, $q_id, $rate,
					'$created')");
				}	 
			}
			$response['success']['status']  = 1;
			$response['success']['message'] = 'Data Saved Successfully';
		
		}else{

			$response['error']['status']  = 0;
			$response['error']['message'] = 'User not registered.';
		}
		return $response;
    }
	
	/*
    @name: get_levelTwoRatings
    @description: To get level two save data 
    @author: Soumya Mishra
    @date: 8th May 2016    
    */
	
	public function get_levelTwoRatings($device_type, $device_id, $user_id, $cat_id, $timestamp) {
		
		$response = array();
		
		$ratingTimestamp = date('Y-m-d', $timestamp);
		$startDate = $ratingTimestamp. ' 00:00:00';
		$endDate   = $ratingTimestamp. ' 23:59:59';
		
		// To check if User Exists
		$usql   = mysql_query("select * from users where id = ".$user_id." AND device_id = '$device_id' AND device_type = ". $device_type );
		$ucount = @mysql_num_rows($usql);
		
		if($ucount > 0){
			
			$quesQuery = mysql_query("select uq.id, uq.user_id, uq.question_id, uq.rate, uq.created_date, qu.link, qu.advice from level_two_user_questions
			as uq inner join level_two_questions as qu on (uq.question_id = qu.id) where uq.user_id = ". $user_id . " AND uq.created_date BETWEEN '$startDate' AND '$endDate' group by uq.question_id order by uq.rate desc");
			
			$quesCount = mysql_num_rows($quesQuery);
			if($quesCount > 0){
				$allQuestions = array();
				while($ques = mysql_fetch_assoc($quesQuery)){
					$ques['link']   = $_SERVER['HTTP_HOST'].'/healthy_api/'.$ques['link']; 
					$ques['advice'] = $_SERVER['HTTP_HOST'].'/healthy_api/'.$ques['advice']; 
					array_push($allQuestions, $ques);	
				}
				$response['success']['status']    = 1;
				$response['success']['message']   = 'All Questions';
				$response['success']['questions'] = $allQuestions;				
			}
		}else{
			$response['error']['status']  = 0;
			$response['error']['message'] = 'User not registered.';
		}
		return $response;
    }
	
	
	/*
    @name: get_levelThreeQuestions
    @description: To get level three questions 
    @author: Soumya Mishra
    @date: 8th May 2016    
    */
	public function get_levelThreeQuestions($user_id,$device_type,$device_id,$cat_id) {
		
		$response = array();
		
		$sql = "select ques.id, ques.category_id , ques.type , ques.question , ques.answer_1, ques.answer_2, ques.answer_3, ques.answer_4, ques.answer_5,
				ques.answer_1_type, ques.answer_2_type, ques.answer_3_type, ques.answer_4_type, ques.answer_5_type, cat.name as category_name 
				from level_three_questions as ques inner join categories as cat on (ques.category_id = cat.id) where ques.category_id = ".$cat_id;
		$res   = mysql_query($sql);
		$count = mysql_num_rows($res);
		    
		if($count > 0){
			$questionsArray = [];
			while($questions = mysql_fetch_assoc($res)) {
				if($questions['type'] != 'text') { 
					$questions['question'] = $_SERVER['HTTP_HOST'].'/healthy_api/'.$questions['question'];
				}else {
					$questions['question'] = $questions['question'];
				}

				if($questions['answer_1_type'] != 'text') {
					$questions['answer_1'] = $_SERVER['HTTP_HOST'].'/healthy_api/'.$questions['answer_1'];
				}else {
					$questions['answer_1'] = $questions['answer_1'];
				}

				if($questions['answer_2_type'] != 'text') {
					$questions['answer_2'] = $_SERVER['HTTP_HOST'].'/healthy_api/'.$questions['answer_2'];
				}else {
					$questions['answer_2'] = $questions['answer_2'];
				}

				if($questions['answer_3_type'] != 'text') {
					$questions['answer_3'] = $_SERVER['HTTP_HOST'].'/healthy_api/'.$questions['answer_3'];
				}else {
					$questions['answer_3'] = $questions['answer_3'];
				}

				if($questions['answer_4_type'] != 'text'){
					$questions['answer_4'] = $_SERVER['HTTP_HOST'].'/healthy_api/'.$questions['answer_4'];
				}else {
					$questions['answer_4'] = $questions['answer_4'];
				}

				if($questions['answer_5_type'] != 'text'){
					$questions['answer_5'] = $_SERVER['HTTP_HOST'].'/healthy_api/'.$questions['answer_5'];
				}else {
					$questions['answer_5'] = $questions['answer_5'];
				}

				$questions['right_answer'] = $questions['right_answer'];

				unset($questions['answer_1_type']);
				unset($questions['answer_2_type']);
				unset($questions['answer_3_type']);
				unset($questions['answer_4_type']);
				unset($questions['answer_5_type']);

				array_push($questionsArray,$questions);
			}
				
			$response['success']['status']    = 1;
			$response['success']['message']   = 'Successful.';
			$response['success']['questions'] = $questionsArray;
			
		}else {
			$response['error']['status']  = 0; 
			$response['error']['message'] = 'No third level questions for this category';
		}
		return $response;
    }

	/*
    @name: save_helpRequest
    @description: To save help request
    @author: Soumya Mishra
    @date: 12th May 2016    
    */
	public function save_helpRequest($device_type,$device_id,$user_id,$helpText){
		
		$response = array();
		
		// To check if User Exists
		$usql   = mysql_query("select * from users where id = ".$user_id." AND device_id = '$device_id' AND device_type = ". $device_type );
		$ucount = @mysql_num_rows($usql);
		
		if($ucount > 0){
			$created     = $this->curdate();
			$saveHelp = mysql_query("INSERT INTO help_requests (user_id, help_text, created_date) VALUES ($user_id, '$helpText','$created')");
			if($saveHelp){
				$response['success']['status']  = 1; 
				$response['success']['message'] = 'Successful saved.';
			}else{
				$response['error']['status']  = 0; 
				$response['error']['message'] = 'Unable to save.';
			}
		}
		return $response;
	}

	/*
    @name: level_three_save_data
    @description: To get level three save data 
    @author: Soumya Mishra
    @date: 13th May 2016    
    */
	public function level_three_save_data ($device_type, $device_id, $user_id, $timestamp, $questions = array(), $cat_id) {
		
		$response  = array();
		
		// To check if User Exists
		$usql   = mysql_query("select * from users where id = ".$user_id." AND device_id = '$device_id' AND device_type = '$device_type' ");
		$ucount = @mysql_num_rows($usql);
		
		if($ucount > 0){
			if(!empty($questions)){
				echo "<pre>";
				print_r($questions);
				//die;
				foreach($questions as $ques){
					
					$q_id     = $ques['q_id'];
					$ans      = $ques['ans_id'];
					$rightAns = $ques['right_answer'];
					if($ans != 0){
						$results  = mysql_query("INSERT INTO level_three_user_questions (category_id, user_id, question_id, answer, correct_answer) VALUES ($cat_id, $user_id, $q_id, $ans,
						$rightAns)");
					}
				}
			}	
			if($results){
				$response['success']['status']  = 1;
				$response['success']['message'] = 'Data Saved Successfully';
			}	
		}else{
			$response['error']['status']  = 0;
			$response['error']['message'] = 'User not registered.';
		}
		return $response;
    } 
} // end of class
?>
