<?php
  session_start();
  if (!isset($_SESSION["admin_auth"]) || $_SESSION["admin_auth"] != true) {
    header("Location: /login.php");
    exit();
  }
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>User Management</title>

  <script src="https://cdn.zinggrid.com/zinggrid.min.js" defer></script>
</head>

<body>
  <nav style="text-align:right; font-size:20px">
    <a href="/index.php">Home</a>
    <a href="/logout.php">Logout</a>
  </nav>
  <h1>User Management</h1>
  <h2>Read Users</h2>
  <script>
    <?php
        $mysqli = new mysqli("localhost", "root", "Carbon742!@#", "users");
        if ($mysqli->connect_error) {
            die('Connect Error (' . $myssqli->connect_errno . ')' . $mysqli->connect_error);
        }
        $users = [];
        if ($result = $mysqli->query("SELECT * FROM credentials")) {
            $users = $result->fetch_all(MYSQLI_ASSOC);
            $num_rows = $result->num_rows;
            $result->close();
        }
    ?>
    var userRows = <?php echo json_encode($users) ?>;
    var numRows = <?php echo $num_rows ?>;
    <?php
        $mysqli->close();
    ?>
    window.onload = function() {
        document.querySelector('zing-grid').data = userRows;
    }
  </script>

    <zing-grid
    caption="Users"
    search
    pager
    page-size="5"
    layout="row"
    viewport-stop
    >
    </zing-grid>

    <h2>Create User</h2>
    <form action="apis/create.php" method="POST">
        <label>
            Username/Email:
            <input type="text" name="username" required/>
        </label> <br />
        <label>
            Password:
            <input type="text" name="password" required/>
        </label> <br />
        <label>
            Authorization type:
            <select name="auth">
              <option value="basic">Basic</option>
              <option value="admin">Admin</option>
            </select>
        </label> <br />
        <label>
            <input type="submit" value="Create" />
        </label>
    </form>

    <h2>Update User</h2>
    <form action="apis/update.php" method="POST">
        <label>
            ID of user to update:
            <input type="number" name="id" min="1" required/>
        </label> <br />
        <label>
            New username/email:
            <input type="text" name="username" />
        </label> <br />
        <label>
            New password:
            <input type="text" name="password" />
        </label> <br />
        <label>
            New authorization type:
            <select name="auth">
              <option value="basic">Basic</option>
              <option value="admin">Admin</option>
            </select>
        </label> <br />
        <label>
            Request type:
            <select name="request">
              <option value="put">PUT</option>
              <option value="patch">PATCH</option>
            </select>
            <span style="color:red">(All fields must be filled for a PUT request)</span>
        </label> <br />
        <label>
            <input type="submit" value="Update" />
        </label>
    </form>

    <h2>Delete User</h2>
    <form action="apis/delete.php" method="POST">
        <label>
            ID of user to delete:
            <input type="number" name="id" min="1" required/>
        </label> <br />
        <label>
            <input type="submit" value="Delete" />
        </label>
    </form>

    <hr />
  
</body>

</html>

