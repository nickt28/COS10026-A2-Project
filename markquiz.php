<?php

function sanitise_inputs($input) {
	$input = trim($input);
	$input = htmlspecialchars($input);
	return $input;
}

function has_characters($input) {
	if (!preg_match("#^(([a-zA-Z]|-| ){1,30})$#", $input)) {  // If Character and between 1 to 50
		return false;
	}
	else {
		return true;
	}
}

function has_only_numbers($input) {
	if (preg_match("|^([0-9]{1,10})$|", $input)) {  // If Number and 1-10
		return (int)$input;
	}
	else {
		//print ("Error: Number Issue");
		//echo"<p>has_only_numers validation failure</p>";
		return 'failback';
	}
}

function validate_accordingly($inputvalue, $validation_index) {
	$validation_preference = ['number_only', 'character_only'];
	$errorfound = false;
	if ($inputvalue != "") {
		if (count($validation_preference) > $validation_index) {
			if ($validation_preference[$validation_index] == 'number_only') {
				if (has_only_numbers($inputvalue) == 'failback') {
					$errorfound = true;
					//echo"<p>issue</p>";
					return "Input out of range! ID from 1 to 10 ONLY!";
				}
			}
			elseif ($validation_preference[$validation_index] == 'character_only') {
				if (has_characters($inputvalue) == false) {
					$errorfound = true;
					return "Input other than characters found!";
				};
			}
		}
	}
	if ($errorfound == false) {
		return "no_error";
	}
}

function deconstruct_array_sanitisation($array) {
	$sanitised_array = [];
	for ($counter=0;$counter<count($array);$counter++) {
		$sanitised_array[$counter] = sanitise_inputs($array[$counter]); // Rebuild sanitised array.
	}
	return $sanitised_array;
}

function show_error_debug($array_of_errors) {
	for ($counter=0;$counter<count($array_of_errors);$counter++) {
		echo("<h2>$array_of_errors[$counter]</h2>");
	}
}

function get_post_values($post_value_ids, $get_errors){
	$input_array = [];
	$issue_found = false;
	$issue_array = [];
	foreach ($post_value_ids as $key => $value) {
		//echo "<p>$post_value_ids[$key]</p>";
		if (isset($_POST[$post_value_ids[$key]])) {
			array_push($input_array, sanitization_and_type_check($_POST[$post_value_ids[$key]], $post_value_ids[$key], false));
		}
		else {
			array_push($input_array, "fallback");
		}
		if ($get_errors == true) {
			if ($input_array[$key] != 'fallback' and $input_array[$key] == 'failback') { // Not existant.
				$error_returned = sanitization_and_type_check($_POST[$post_value_ids[$key]], $post_value_ids[$key], true);
				if ($issue_found == false) {
					array_push($issue_array, "ERROR_ARRAY");
					$issue_found = true;
				}
				array_push($issue_array, $error_returned);
			}
		}
	}
	if ($issue_found == true) {
		return $issue_array;
	}
	else {
		return $input_array;
	}
}

function sanitization_and_type_check($input, $context, $debug) {
	if ($input != 'fallback') { // Check if theres actually input
		if (!is_array($input)) {
			//print($context);
			//print($input);
			$input = sanitization_and_type_processing($input, $context, $debug);
		} else {
			$replacement_array = [];
			foreach ($input as $input2) {
				//echo"<p>$input2</p>";
				$input2 = sanitization_and_type_processing($input2, "no_prevention", $debug);
				array_push($replacement_array, $input2);
			}
			$input = $replacement_array;
		}
	}
	return $input;
}

function sanitization_and_type_processing($input, $context, $debug) { // Sanitization type confirmation ----------- Broken Needs Fixing
	$input = sanitise_inputs($input);
	if ($input == "") {
		if ($debug == true) {
			return "Input $context has not been filled!";
		}
		return 'failback';
	}
	//print ($input."<br>"); // Unpacked data debug dump
	//print ($context."<br>"); // Unpacked data debug dump
	if ($context == "given_name" or $context == "family_name") {
			$validation_output = validate_accordingly($input, 1);
			if ($validation_output == "no_error") {
				return $input;
			}
			else {
				if ($debug == true) {
					return $validation_output;
				}
				return 'failback';
			}
	}
	if ($context == "ID") {
		if (is_numeric($input)) {
			$validation_output = validate_accordingly($input, 0);
			if ($validation_output == "no_error") {
				return $input;
			}
			else {
				if ($debug == true) {
					return $validation_output;
				}
				return 'failback';
			}
		}
		else {
			if ($debug == true) {
				return 'ID has non-numeric input.';
			}
			return 'failback';
		}
	}
	// print (gettype($input)."<br>"); // Unpacked data debug dump
	return $input;
}

function marking($post_values_array, $answers){ //Inputs must be pre sanitised & type checked
	$results = [];
	foreach ($post_values_array as $key => $value) {
		if (!is_array($value)){
			if ($value == $answers[$key]) {
				array_push($results, [1, $value]);
			} else {
				array_push($results, [0, $value]);
			}
		} else {
			$correctness_and_input_array = [];
			foreach ($value as $v2) {
				if (in_array($v2, $answers[$key], true)) {
					array_push($correctness_and_input_array, [1, $v2]);
				} else {
					array_push($correctness_and_input_array, [0, $v2]);
				}
			}
			$count_correct = 0;
			foreach ($correctness_and_input_array as $v2) {
				if ($v2[0] == 1) {
					$count_correct++;
				}
			}
			if ($count_correct == count($answers[$key]) && count($correctness_and_input_array) == count($answers[$key])) {
				$correct = 1;
			} else {
				$correct = 0;
			}
			array_push($results, [$correct, $correctness_and_input_array]);
		}
	}
	return $results;
}

function debug_dump($results) {
	print_r($results);
	print("<br><br>");

	foreach ($results as $value) {
		foreach ($value as $v2) {
			if (!is_array($v2)) {
				print($v2);
			} else {
				foreach ($v2 as $v3) {
					print_r($v3);
				}
			}
		}
		print("<br>");
	}
}

function score($results) {
	$score = 0;
	foreach ($results as $value) {
		if ($value[0] == 1) {
			$score++;
		}
	}
	return $score;
}

function db_connect() {
	$servername = "localhost";
	$username = "root";
	$password = "";
	$dbname = "a_patchy_team";
	try {
		$conn = new mysqli($servername, $username, $password, $dbname);
	} catch(Exception $e) {
	  echo '<p>MySQLi Connection Error: ' .$e->getMessage().'</p>';
		return false;
	}

	// Check Table Exists
	try {
		$conn->query("SELECT 1 FROM attempts WHERE 1");
		return $conn;
	} catch(Exception $e) {
		echo '<p>MySQLi Created Table: ' .$e->getMessage().'</p>';
		$create_table = "CREATE TABLE IF NOT EXISTS attempts (
			id int(11) NOT NULL AUTO_INCREMENT,
			created datetime NOT NULL DEFAULT current_timestamp(),
			first_name varchar(255) NOT NULL,
			last_name varchar(255) NOT NULL,
			student_number int(11) NOT NULL,
			attempt int(11) NOT NULL,
			score int(11) NOT NULL,
			PRIMARY KEY (id)
		)";
		$conn->query($create_table);
		return $conn;
	}
}

function save_db_data($id, $score){
	$conn = db_connect();
	if ($conn == true) {
		// Check if user exists
		$sql = "SELECT COUNT(*) FROM attempts WHERE first_name = '$id[0]' AND last_name = '$id[1]' AND student_number = '$id[2]'";
		$user_exists = $conn->query($sql)->fetch_assoc()["COUNT(*)"];

		// Create User
		if ($user_exists == 0) {
			$sql_insert = "INSERT INTO `attempts`(`first_name`, `last_name`, `student_number`, `attempt`, `score`) VALUES ('$id[0]','$id[1]','$id[2]','1','$score')";
			$conn->query($sql_insert);
			print("<h2>User Added</h2>");
		}

		// For existing user update attempt details
		if ($user_exists >= 1) {
			$sql = "SELECT attempt FROM attempts WHERE first_name = '$id[0]' AND last_name = '$id[1]' AND student_number = '$id[2]'";
			$attempts = $conn->query($sql)->fetch_assoc()["attempt"]+1;

			if ($attempts <= 2) {
				$sql_update_attempts = "UPDATE attempts SET attempt ='$attempts', score = '$score' WHERE first_name = '$id[0]' AND last_name = '$id[1]' AND student_number = '$id[2]'";
				$conn->query($sql_update_attempts);
			} else {
				print ("<h2>Maximum Attempts Reached</h2>");
			}
		}
		$conn->close();
	}
}

function submission_check($results){
	$count = 0;
	foreach ($results as $value) {
		if ($value == 'fallback') {
			$count++;
		}
	}
	if ($count == count($results)) {
		return false;
	} else {
		return true;
	}
}

$post_id_inputs = ['given_name','family_name','ID'];
$post_question_inputs = ['quiz-question-1','quiz-question-2','quiz-question-3','quiz-question-4','quiz-question-5'];
$answers = ['slowloris',['process-based_mode','hybrid_mode'],['bob','sky'],'2004','1994'];
$post_ids_values_array = get_post_values($post_id_inputs, false); // Read ID Values
$error_id_inputs_array = get_post_values($post_id_inputs,true); // Get any errors.
if (submission_check($post_ids_values_array) == true) {
	print ('<div id="results" class="quiz-content quiz-results">');
	if ($error_id_inputs_array[0] == "ERROR_ARRAY") {
		foreach ($error_id_inputs_array as $index => $result) {
			if ($index != 0) {
				echo "<p>$result</p>";
			}
			else {
				echo "<p><strong>Attention! Errors in your input have prevented any further processing!</strong></p>"; // Start error message
			}
		}
	}
	$post_questions_values_array = get_post_values($post_question_inputs, false); // Read Questions Values
	$results = marking($post_questions_values_array, $answers); // Calulate results - Input arrays must be same size
	$score = score($results);
	//print("$score");
	save_db_data($post_ids_values_array, $score);
	print ("<p>Score: ".$score."/5</p>");
	// debug_dump($results);
	print ('</div>');
}

?>
