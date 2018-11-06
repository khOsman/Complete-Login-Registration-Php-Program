<?php 


/********** Helper Funtions ***********/

//String cleaning function
function clean($string){
	$data = htmlentities($string);
	$data = htmlspecialchars($data);
	return $data;
}

//Redirect function
function redirect($location){
	return header("Location: {$location}");
}

//message throwing functions
function set_message($message){
	if (!empty($message)) {
		$_SESSION['message'] = $message;
	}else{
		$message = "";
	}
}

function display_message(){
	if (isset($_SESSION['message'])) {
		echo $_SESSION['message'];
		unset($_SESSION['message']);
	}
}

// token generator function
function token_generator(){

	//uniqueid(mt_rand(),true) = a unique ID with a random number as a prefix
	//md5(var) = encrypts the data
	$token = $_SESSION['token'] = md5(uniqid(mt_rand(), true));
	return $token;
}


//Displaying Validation error function

function validation_errors($error_message){
		echo '
				<div class="alert alert-danger alert-dismissible" role="alert">
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button><strong>WARNNING!!!</strong> '.$error_message.'
</div>
				';
} 

//Existing email checker function
function email_exists($email){

	$sql = " SELECT id FROM users WHERE email = '$email'";
	$result = query($sql);

	if (row_count($result)==1) {
		return true;
	}else{
		return false;
	}
}

//Existing username checker function
function username_exists($username){

	$sql = " SELECT id FROM users WHERE username = '$username'";
	$result = query($sql);

	if (row_count($result)==1) {
		return true;
	}else{
		return false;
	}
}

//mail function
function send_email($email, $subject, $message, $headers){
	//return mail($email, $subject, $message, $headers);
}



/********************* Validation Funtions *****************************/

//user validation function

function validate_user_registration(){

	$errors = [];
	$min   = 3;
	$max   = 20;

	if ($_SERVER['REQUEST_METHOD'] == "POST") {
		$first_name       = clean($_POST['first_name']);
		$last_name        = clean($_POST['last_name']);
		$username         = clean($_POST['username']);
		$email            = clean($_POST['email']);
		$password         = clean($_POST['password']);
		$confirm_password = clean($_POST['confirm_password']);

		if (strlen($first_name)<$min) {
			$errors[] = "Your first name cannot be less then {$min} characters";
		}

		if (strlen($first_name)>$max) {
			$errors[] = "Your first name cannot be more then {$max} characters";
		}

		if (strlen($last_name)<$min) {
			$errors[] = "Your last name cannot be less then {$min} characters";
		}

		if (strlen($last_name)>$max) {
			$errors[] = "Your last name cannot be more then {$max} characters";
		}


		if (username_exists($username)) {
			$errors[] = "Sorry that user name is already taken";
		}

		if (strlen($username)<$min) {
			$errors[] = "Your user name cannot be less then {$min} characters";
		}

		if (strlen($username)>$max) {
			$errors[] = "Your user name cannot be more then {$max} characters";
		}

		if (email_exists($email)) {
			$errors[] = "Sorry that email is already registered";
		}

		if ($password !== $confirm_password) {
			$errors[] = "Your password feild do not match.";
		}
		if (!empty($errors)) {
			foreach ($errors as  $error) {
				
				//echo $error;
				echo validation_errors($error);
				
			}
		}else{
			if ( register_user($first_name, $last_name, $username, $email, $password )) {
				
				$message = "<p class='bg-success text-center'>Please check your email for an activation link.</p>";
				set_message($message);
				redirect("index.php");

			}else{
			 
				$message = "<p class='bg-danger text-center'>SOMETHING IS WRONG !!! Please, TRY AGAIN</p>";
				set_message($message);
				redirect("index.php");

				}
			}


	}//post request
	
 
 }//function end


/********************* Register user Funtions *****************************/

// register user function
 function register_user($first_name, $last_name, $username, $email, $password ){

 	$first_name = escape($first_name);
 	$last_name  = escape($last_name);
 	$username   = escape($username);
 	$email      = escape($email);
 	$password   = escape($password);

 	if (email_exists($email)) {
 		return false;
 	}elseif (username_exists($username)) {
 		return false;
 	}else{

 		$password = md5($password);

 		$validation_code = md5($username + microtime());

 		$sql = "INSERT INTO users(first_name, last_name, username, email, password, validation_code, active)";
 		$sql.= " VALUES('$first_name', '$last_name', '$username', '$email', '$password', '$validation_code ', 0)";

 		$result = query($sql);
 		

 		$subject = "Activate Account";
 		$message = "Pleas click the link below to activate your Account
 		http://localhost/lrapp/activate.php?email=$email&code=$validation_code
 		";

 		$headers = "From: noreply@meosman.com";

 		send_email($email, $subject, $message, $headers);


 		return true;

 	}

 }//function


/********************* Activate user Funtions *****************************/

function activate_user(){

	if ($_SERVER['REQUEST_METHOD']== "GET") {
		
		if (isset($_GET['email'])) {
			
			$email = clean($_GET['email']);
			$validation_code =clean($_GET['code']);
			
			$sql = "SELECT id FROM users WHERE email = '".clean($_GET['email'])."' AND validation_code = '".clean($_GET['code'])."' ";

			$result = query($sql);
          

			if (row_count($result)) {

				$sql2 = "UPDATE users SET active = 1, validation_code = 0 WHERE email = '".clean($email)."' AND validation_code = '".clean($validation_code)."' ";

				$result2 = query($sql2);
          	    


				$message = "<p class='bg-success text-center'>Your account has been activated. Please login.</p>";
				set_message($message);
				redirect("login.php");
			}else{
				$message = "<p class='bg-danger text-center'>Sorry your account could not be activated.</p>";
				set_message($message);
				redirect("login.php");
			}	

		}

	}//GET request
}//function


/********************* Validate user login Funtions *****************************/



function validate_user_login(){
	$errors = [];

	$min = 3;
	$max =20;

	if ($_SERVER['REQUEST_METHOD'] == "POST") {
		//echo "It works";
		$email      = clean($_POST['email']);
 	    $password   = clean($_POST['password']);
 	    $remember   = isset($_POST['remember']);

 	    if (empty($email)) {
 	    	$errors = "Email field cannot be empty.";
 	    }

 	    if (empty($password)) {
 	    	$errors = "Password field cannot be empty.";
 	    }


 	    if (!empty($errors)) {
			foreach ($errors as  $error) {
				
				//echo $error;
				echo validation_errors($error);
				
			}
		}else{

			if (login_user($email, $password, $remember)) {
				redirect("admin.php");
			}else{
				$message = "Your credentials are not correct.";
				echo validation_errors($message);
			}


		}


	}//POST request
}//function



/********************* User login Funtions *****************************/

function login_user($email, $password, $remember){

	$sql = " SELECT password, id FROM users WHERE email = '".escape($email)."' AND active=1";
	$result = query($sql);


	if (row_count($result) == 1) {


		$row = fetch_array($result);
		$db_password = $row['password'];


		if (md5($password) === $db_password) {

			if ($remember == "on") {
				setcookie('email',$email, time() + 86400);
			}

			$_SESSION['email'] = $email;


			return true;
		}else{
			return false;
		}

		return true;

	}else{

		return false;
	}


}//function



/********************* logged in Funtions *****************************/


function logged_in(){

	if (isset($_SESSION['email']) || isset($_COOKIE['email'])) {
		return true;
	}else{
		return false;
	}


}//function


/********************* Recover password Funtions *****************************/

function recover_password(){

	if ($_SERVER['REQUEST_METHOD'] == "POST") {
		
		if (isset($_SESSION['token']) && $_POST['token'] === $_SESSION['token']) {

			$email = clean($_POST['email']);

			if (email_exists($email)){

				$validation_code = md5($email + microtime());

				setcookie('temp_access_code',$validation_code, time() + 900);

				$sql = "UPDATE users SET validation_code = '".escape($validation_code)."' WHERE email = '".escape($email)."' ";

				$result = query($sql);
				


				$subject = "Please reset your password";
				$message = "Here is your password reset code {$validation_code}
				Click here to reset your password http://localhost/lrapp/code.php?email=$email&code=$validation_code
				";
				$headers = "From: noreply@meosman.com";


				if(!send_email($email, $subject, $message, $headers)){

					$error_message = "Email could not be sent";
					echo validation_errors($error_message);
				}


				$msg = "<p class='bg-success' text-center>Please check email for a password reset code</p>";
				set_message($msg);

				redirect("index.php");


			
			}else{
				$error_message = "This email does not exist";
				echo validation_errors($error_message);
			}
			
		}else{
			redirect("index.php");
		}//session request


		if (isset($_POST['cancel_submit'])) {
			redirect("login.php");
		}

	}//POST request

}//function



/********************* Code validation Funtions *****************************/

function validate_code(){


	if (isset($_COOKIE['temp_access_code'])) {

			
			if (!isset($_GET['email']) && !isset($_GET['code'])) {
				
				redirect("index.php");

			}else if (empty($_GET['email']) || empty($_GET['code']) ) {
				
				redirect("index.php");

			}else{

				if(isset($_POST['code'])){

					$email           = clean($_GET['email']);
					$validation_code = clean($_POST['code']);

					$sql = "SELECT id FROM users WHERE validation_code = '".escape($validation_code)."' AND email = '".escape($email)."'";
					$result = query($sql);

					if (row_count($result) == 1) {

						setcookie('temp_access_code',$validation_code, time() + 300);

						redirect("reset.php?email=$email&code=$validation_code");

					}else{

						$msg = "<p class='bg-danger' text-center>Sorry, Wrong validation code</p>";
						set_message($msg);

					}

				}

			}

		
	}else{
		
		$msg = "<p class='bg-danger' text-center>Sorry your validation code expired.</p>";
		set_message($msg);

		redirect("recover.php");
	}


}//function


/********************* Password reset Funtions *****************************/

function password_reset(){


    if (isset($_COOKIE['temp_access_code'])) {

    	if (isset($_GET['email']) && isset($_GET['code'])) {		

    			if (isset($_SESSION['token']) && isset($_POST['token']) && $_POST['token'] === $_SESSION['token']){

			
	     			if ($_POST['password'] === $_POST['confirm_password']) {

	     				$updated_password =md5($_POST['password']);
	     				
	     				$sql = "UPDATE users SET password = '".escape($updated_password)."', validation_code=0 WHERE  email = '".escape($_GET['email'])."'";

	     			    query($sql);

	     			    $msg = "<p class='bg-success' text-center>Your password has been updated.</p>";
						set_message($msg);
						redirect("login.php");
	     			}

			    }

    	}
    	
  }else{

			set_message("<p class='bg-danger' text-center>Sorry your time has expired.</p>");
			redirect("recover.php");

		}
}//function
























 ?>