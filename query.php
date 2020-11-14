<?php
$content =  file_get_contents('php://input');
parse_str($content, $get_array);
$name = $get_array['name'];

$vars = '';
$vals = '';
foreach ($get_array as $key => $value) {
    // check if reserved words are being inserted
    if ($key == 'usage' || $key == 'fetch') {
        $vars .= '`';
        $vars .= $key;
        $vars .= '`';
        $vars .= ', ';
        $vals .= '\'';
        $vals .= $value;
        $vals .= '\'';
        $vals .= ', ';
    }
    else if ($key == 'name' || $key == 'vitalsScore') {
        //do nothing for manually handled cases
    }
    // ints dont need quotes around them
    else if ($key == 'innerHeight' || $key == 'outerHeight' || $key == 'innerWidth' || $key == 'outerWidth') {
        $vars .= $key;
        $vars .= ', ';
        $vals .= $value;
        $vals .= ', ';
    }
    // special boolean to int case
    else if($key == 'cookieEnabled') {
        $vars .= $key;
        $vars .= ', ';
        if ($value == 'true') {
            $vals .= 1;
        }
        else {
            $vals .= 0;
        }
        $vals .= ', ';
    }
    // else surround all values in quotes
    else {
        $vars .= $key;
        $vars .= ', ';
        $vals .= '\'';
        $vals .= $value;
        $vals .= '\'';
        $vals .= ', ';
      }
}
// add vitalsScores at end
$vars .= 'vitalsScore';
$vals .= '\'';
$vals .= $get_array['vitalsScore'];
$vals .= '\'';

$conn = new mysqli('localhost', 'root', 'Carbon742!@#', 'hw3');
if ($conn->connect_errno) {
    echo "Failed to connect to MySQL: (" . $conn->connect_errno . ") " .$conn->connect_error;
}

// special second storageEstimate_2 case
if ($name == "storageEstimate" && count($get_array) > 3) {
    $name .= '_2';
}
$query = "INSERT INTO $name ($vars) VALUES ($vals)";
//echo $query;
if (!$conn->query($query)) {
    echo "Tabling failed: (" . $conn->errno . ") " . $conn->error;
}

?>
