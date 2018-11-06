<?php 
//host,username,password,database
$con = mysqli_connect('localhost', 'root', '', 'login_db');


// record counter function

function row_count($result){
	return mysqli_num_rows($result);
}





//esacaping string

function escape($string){
	global $con;

	return mysqli_real_escape_string($con, $string);
}




//sql query function for database

function query($query){

	global $con;
	return mysqli_query($con, $query);
}

//Confirmation function

function confirm($result){
	global $con;
	if(!$result){
		die("QUERY FAILED". mysqli_error($con));
	}
}


//data fetching function

 function fetch_array($result){
 	global $con;
 	return mysqli_fetch_array($result);
 }



 ?>