<?php
// redirect to home if accessed using get/other request
    if ($_SERVER["REQUEST_METHOD"] != "POST") {
        header("Location: /index.php");
    exit();
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Delete User</title>
</head>

<body> 
<nav style="text-align:right; font-size:20px">
    <a href="/logout.php">Logout</a>
</nav>
<?php
    $id = $_POST["id"];
    $mysqli = new mysqli("localhost", "root", "Carbon742!@#", "users");
    if($mysqli->connect_error) {
        die('Connect Error (' . $myssqli->connect_errno . ')' . $mysqli->connect_error);
    }
    $query = "DELETE FROM credentials WHERE id = $id";
    // echo if deletion failed
    if (!$mysqli->query($query)) {
        echo "<p>Deletion failed: (" . $mysqli->errno . ") " . $mysqli->error . "</p";
    }
    // echo if no rows were affected
    else if ($mysqli->affected_rows == 0) {
      echo "<p>User with id $id doesn't exist.</p>";
    }
    else {
        echo "<p>Deletion success!</p>";
    }
?>
  <a href="../users.php">Back to User Management</a>

</body>
</html>


