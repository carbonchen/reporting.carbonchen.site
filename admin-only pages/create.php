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
  <title>Create User</title>
</head>

<body> 
<nav style="text-align:right; font-size:20px">
    <a href="/logout.php">Logout</a>
</nav>
<?php
    $username = $_POST["username"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $auth = $_POST["auth"];
    if ($auth == "basic") {
        $auth = 0;
    }
    else {
        $auth = 1;
    }

    $mysqli = new mysqli("localhost", "root", "Carbon742!@#", "users");
    if($mysqli->connect_error) {
        die('Connect Error (' . $myssqli->connect_errno . ')' . $mysqli->connect_error);
    }
    $selQuery = "SELECT * FROM credentials WHERE username = '$username'";
    $createQuery = "INSERT INTO credentials (username, password, admin) VALUES ('$username', '$password', $auth)";
    // check if username/email exists
    $result = $mysqli->query($selQuery);
    if (mysqli_num_rows($result)!=0) {
        echo "<p>Failed to create user. Username/email already exists.</p>";
    }
    // echo if insertion failed
    else if (!$mysqli->query($createQuery)) {
        echo "<p>Insertion failed: (" . $mysqli->errno . ") " . $mysqli->error . "</p";
    }
    else {
        echo "<p>User created successfully!</p>";
    }
?>
  <a href="../users.php">Back to User Management</a>

</body>
</html>


