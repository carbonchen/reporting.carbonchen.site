<?php
  session_start();
  // redirect to index if already logged in
  if (isset($_SESSION["basic_auth"]) && $_SESSION["basic_auth"] == true) {
    header("Location: /index.php");
    exit();
  }
  if (isset($_SESSION["admin_auth"]) && $_SESSION["admin_auth"] == true) {
    header("Location: /index-admin.php");
    exit();
  }

  // connect to sql db
  $mysqli = new mysqli("localhost", "root", "Carbon742!@#", "users");
            if($mysqli->connect_error) {
                die('Connect Error (' . $myssqli->connect_errno . ')' . $mysqli->connect_error);
            }

  $error = "";
  $username = "";
  $password = "";

  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // check if either field is empty
    if (empty(trim($_POST["username"]))) {
      $error = "Enter your username";
    }
    else if (empty($_POST["password"])) {
      $error = "Enter your password";
    }
    else {
      $username = trim($_POST["username"]);
      $password = $_POST["password"];
    }
    
    // validate
    if (empty($error)) {
      if ($result = $mysqli->query("SELECT * FROM credentials WHERE username = '$username'")) {
        $user = mysqli_fetch_assoc($result);
        $result->close();
        // validated user and password
        if (password_verify($password, $user["password"])) {
          // admin authorized
          if ($user["admin"] == 1) {
            $mysqli->close();
            $_SESSION["admin_auth"] = true;
            $_SESSION["username"] = $username;
            header("Location: /index-admin.php");
            exit();
          }
          else {
            // basic authorized
            $mysqli->close();
            $_SESSION["basic_auth"] = true;
            $_SESSION["username"] = $username;
            header("Location: /index.php");
            exit();
          }
        }
        else {
          $mysqli->close();
          $error = "Incorrect username or password";
        }
      }
    }
  }

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login</title>
  <style> #err { color: red } </style>
</head>

<body>
  <h1>Login</h1>
  <p id="err"><?php echo $error; ?></p>
  <form action="/login.php" method="POST">
    <label>
      Username or Email
      <input type="text" name="username" />
    </label>
    <br />
    <label>
      Password
      <input type="password" name="password" />
    </label>
    <br />
    <label>
      <input type="submit" value="Log in" />
    </label>
  </form>

</body>
</html>

