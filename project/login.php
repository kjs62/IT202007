<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<div class="drift">
    <form method="POST">
        <label for="email">Email or Username:</label>
        <br>
        <input type="text" id="email" name="email" required/>
        <br>
        <label for="p1">Password:</label>
        <br>
        <input type="password" id="p1" name="password" required/>
        <br>
        <input type="submit" name="login" value="Login"/>
    </form>

<?php
if (isset($_POST["login"])) {
    $email = null;
    $password = null;
    $isEmail = false;
    if (isset($_POST["email"])) {
        $email = $_POST["email"];
    }
    if (isset($_POST["password"])) {
        $password = $_POST["password"];
    }
    $isValid = true;
    if (!isset($email) || !isset($password)) {
        $isValid = false;
        flash("Email or password missing");
    }
    if (strpos($email, "@") < strpos($email, ".")) {
        $isEmail = true;
    }
    if ($isValid) {
        $db = getDB();
        if (isset($db)) {
            if($isEmail) {
                $stmt = $db->prepare("SELECT id, email, username, password from Users WHERE email = :email LIMIT 1");
            }
            else {
                $stmt = $db->prepare("SELECT id, email, username, password from Users WHERE username = :email LIMIT 1"); 
            }

            $params = array(":email" => $email);
            $r = $stmt->execute($params);
            //echo "db returned: " . var_export($r, true);
            $e = $stmt->errorInfo();
            if ($e[0] != "00000") {
                //echo "uh oh something went wrong: " . var_export($e, true);
                flash("Something went wrong, please try again");
            }
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result && isset($result["password"])) {
                $password_hash_from_db = $result["password"];
                if (password_verify($password, $password_hash_from_db)) {
                    $stmt = $db->prepare("
SELECT Roles.name FROM Roles JOIN UserRoles on Roles.id = UserRoles.role_id where UserRoles.user_id = :user_id and Roles.is_active = 1 and UserRoles.is_active = 1");
                    $stmt->execute([":user_id" => $result["id"]]);
                    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    unset($result["password"]);//remove password so we don't leak it beyond this page
                    //let's create a session for our user based on the other data we pulled from the table
                    $_SESSION["user"] = $result;//we can save the entire result array since we removed password
                    if ($roles) {
                        $_SESSION["user"]["roles"] = $roles;
                    }
                    else {
                        $_SESSION["user"]["roles"] = [];
                    }
                    //on successful login let's serve-side redirect the user to the home page.
                    flash("Log in successful");
                    die(header("Location: home.php"));
                }
                else {
                    flash("Invalid password");
                }
            }
            else {
                if($isEmail) {
                  flash("Invalid email");
                }
                else {
                  flash("Invalid user");
                }
            }
        }
    }
    else {
        flash("There was a validation issue");
    }
}
?>
</div>
<?php require(__DIR__ . "/partials/flash.php");