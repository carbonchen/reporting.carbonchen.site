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
  <title>Update User</title>
</head>

<body>
<nav style="text-align:right; font-size:20px">
    <a href="/logout.php">Logout</a>
</nav>
<?php
    $id = $_POST["id"];
    $username = $_POST["username"];
    $password = $_POST["password"];
    $auth = $_POST["auth"];
    if ($auth == "basic") {
        $auth = 0;
    }
    else {
        $auth = 1;
    }
    $request = $_POST["request"];

    // all fields need to be filled for put requests, else terminate script
    if ($request == "put" && (empty($username) || empty($password))) {
        echo "<p>All fields need to be filled for a put request</p>";
        echo "<a href=\"../users.php\">Back to User Management</a><br />";
        echo "<a href=\"../logout.php\">Logout</a>";
        exit();
    }

    $mysqli = new mysqli("localhost", "root", "Carbon742!@#", "users");
    if($mysqli->connect_error) {
        die('Connect Error (' . $myssqli->connect_errno . ')' . $mysqli->connect_error);
    }

    if ($request == "put") {
        $password = password_hash($password, PASSWORD_DEFAULT);
        $query = "UPDATE credentials SET username='$username',password='$password',admin=$auth WHERE id=$id";
        // echo if update failed
        if (!$mysqli->query($query)) {
            echo "<p>Update failed: (" . $mysqli->errno . ") " . $mysqli->error . "</p";
        }
        // echo if no rows were affected
        else if ($mysqli->affected_rows == 0) {
        echo "<p>User with id $id doesn't exist.</p>";
        }
        else {
            echo "<p>Update (PUT) successful!</p>";
        }
    }

    if ($request == "patch") {
        $vals = "";
        if (!empty($username)) {
            $vals .= "username=$username,";
        }
        if (!empty($password)) {
            $password = password_hash($password, PASSWORD_DEFAULT);
            $vals .= "password='$password',";
        }
        $vals .= "admin=$auth";
        $query = "UPDATE credentials SET $vals WHERE id=$id";

        // echo if update failed
        if (!$mysqli->query($query)) {
            echo "<p>Update failed: (" . $mysqli->errno . ") " . $mysqli->error . "</p";
        }
        // echo if no rows were affected
        else if ($mysqli->affected_rows == 0) {
        echo "<p>User with id $id doesn't exist.</p>";
        }
        else {
            echo "<p>Update (PATCH) successful!</p>";
        }
    }

?>
  <a href="../users.php">Back to User Management</a>

</body>
</html>


