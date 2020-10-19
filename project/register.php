<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<div class="drift">
<?php
if (isset($_POST["register"])) {
    $email = null;
    $password = null;
    $confirm = null;
    $username = null;
    if (isset($_POST["email"])) {
        $email = $_POST["email"];
    }
    if (isset($_POST["password"])) {
        $password = $_POST["password"];
    }
    if (isset($_POST["confirm"])) {
        $confirm = $_POST["confirm"];
    }
    if (isset($_POST["username"])) {
        $username = $_POST["username"];
    }
    $isValid = true;
    //check if passwords match on the server side
    if ($password == $confirm) {
        //not necessary to show
        //echo "Passwords match <br>";
    }
    else {
        flash("Passwords don't match");
        $isValid = false;
    }
    if (!isset($email) || !isset($password) || !isset($confirm)) {
        $isValid = false;
    }
    if(strlen($password) < 8)
    {
        flash("Password must be at least 8 characters");
        $isValid = false;
    }
    if(strlen($username) < 5)
    {
        flash("Username must be at least 5 characters");
        $isValid = false;
    }
    //TODO other validation as desired, remember this is the last line of defense
    if ($isValid) {
        $hash = password_hash($password, PASSWORD_BCRYPT);

        $db = getDB();
        if (isset($db)) {
            //here we'll use placeholders to let PDO map and sanitize our data
            $stmt = $db->prepare("INSERT INTO Users(email, username, password) VALUES(:email,:username, :password)");
            //here's the data map for the parameter to data
            $params = array(":email" => $email, ":username" => $username, ":password" => $hash);
            $r = $stmt->execute($params);
            $e = $stmt->errorInfo();
            if ($e[0] == "00000") {
                flash("Successfully registered! Please login.");
            }
            else {
                if ($e[0] == "23000") {//code for duplicate entry
                    flash("Username or email already exists.");
                }
                else {
                    flash("An error occurred, please try again");
                }
            }
        }
    }
    else {
        flash( "There was a validation issue");
    }
}
//safety measure to prevent php warnings
if (!isset($email)) {
    $email = "";
}
if (!isset($username)) {
    $username = "";
}
?>
    <form method="POST">
        <label for="email">Email:</label>
        <br>
        <input type="email" id="email" name="email" required value="<?php safer_echo($email); ?>"/>
        <br>
        <label for="user">Username:</label>
        <br>
        <input type="text" id="user" name="username" required maxlength="60" value="<?php safer_echo($username); ?>"/>
        <br>
        <label for="p1">Password:</label>
        <br>
        <input type="password" id="p1" name="password" required maxlength="60"/>
        <br>
        <label for="p2">Confirm Password:</label>
        <br>
        <input type="password" id="p2" name="confirm" required maxlength="60"/>
        <br>
        <input type="submit" name="register" value="Register"/>
    </form>
</div>
<?php require(__DIR__ . "/partials/flash.php");