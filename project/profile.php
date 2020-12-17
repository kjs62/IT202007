<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<div class="drift">
<?php
if (!is_logged_in()) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You must be logged in to access this page");
    die(header("Location: login.php"));
}

if (isset($_GET["id"])) {
    $id = $_GET["id"];
}

if (isset($id)) {
$db = getDB();
$stmt = $db->prepare("SELECT * from Users where id = :id LIMIT 1");
$r = $stmt->execute([":id" => $id]);
$results = $stmt->fetch(PDO::FETCH_ASSOC);
$isVis = null;
if(isset($results['id']))
{
  $isVis = $results['isPublic'];
}
else
{
  $id = -1;
}

if (isset($_POST["saved"])) {
    $isValid = true;
    //check if our email changed
    $newEmail = $results['email'];
    if ($results['email'] != $_POST["email"]) {
        //TODO we'll need to check if the email is available
        $email = $_POST["email"];
        $stmt = $db->prepare("SELECT COUNT(1) as InUse from Users where email = :email");
        $stmt->execute([":email" => $email]);
        $results2 = $stmt->fetch(PDO::FETCH_ASSOC);
          
        $inUse = 1;//default it to a failure scenario
        if ($results2 && isset($results2["InUse"])) {
            try {
                $inUse = intval($results2["InUse"]);
            }
            catch (Exception $e) {

            }
        }
        if ($inUse > 0) {
            flash("Email already in use");
            //for now we can just stop the rest of the update
            $isValid = false;
        }
        else {
            $newEmail = $email;
        }
    }
    
    $newUsername = $results['username'];
    if ($results['username'] != $_POST["username"]) {
        $username = $_POST["username"];
        $stmt = $db->prepare("SELECT COUNT(1) as InUse from Users where username = :username");
         $stmt->execute([":username" => $username]);
         $result = $stmt->fetch(PDO::FETCH_ASSOC);
         $inUse = 1;//default it to a failure scenario
         if ($result && isset($result["InUse"])) {
             try {
                 $inUse = intval($result["InUse"]);
             }
             catch (Exception $e) {
 
             }
         }
         if ($inUse > 0) {
             flash("Username already in use");
             //for now we can just stop the rest of the update
             $isValid = false;
         }
         else if(strlen($username) >= 5)
         {
             $newUsername = $username;
         }
         else
         {
             flash("New username not at least 5 characters");
             $isValid = false;
         }
         
    }
    
    $newFName = get_fName();
    if ((get_fName() != $_POST["fName"])) {
        $newFName = $_POST["fName"];
    }
    
    $newLName = get_lName();
    if ((get_lName() != $_POST["lName"])) {
        $newLName = $_POST["lName"];
    }
    
    $isPublic = $_SESSION["user"]["isPublic"];
      
    if (($isPublic != $_POST["priv"])) {
        $isPublic = $_POST["priv"];
    }
    
    if ($isValid) {
        $stmt = $db->prepare("UPDATE Users set email = :email, username= :username, first_name = :fName, last_name = :lName, isPublic = :public where id = :id");
        $r = $stmt->execute([":email" => $newEmail, ":username" => $newUsername, ":fName" => $newFName, ":lName" => $newLName, ":public" => $isPublic, ":id" => $id]);
        if ($r) {
            flash("Updated Username/Email/Privacy");
        }
        else {
            flash("Error updating profile");
        }
        
        if (!empty($_POST["password"]) && !empty($_POST["confirm"]) && !empty($_POST["current"])) {
        
          $curr = $_POST["current"];
          
          $stmt = $db->prepare("SELECT password from Users WHERE id = :userid");
          $stmt->execute([":userid" => get_user_id()]);
          $result = $stmt->fetch(PDO::FETCH_ASSOC);
          
          if ($result && isset($result["password"])) {
            $password_hash_from_db = $result["password"];
            if(password_verify($curr, $password_hash_from_db))
            {      
              if ($_POST["password"] == $_POST["confirm"])
              {  
                if(strlen($_POST["password"]) >= 8)
                {
                  $password = $_POST["password"];
                  $hash = password_hash($password, PASSWORD_BCRYPT);
                  $stmt = $db->prepare("UPDATE Users set password = :password where id = :id");
                  $r = $stmt->execute([":id" => $id, ":password" => $hash]);
                  if ($r) {
                      flash("Reset Password");
                  }
                  else {
                      flash("Error resetting password");
                  }
                }
                else if(strlen($_POST["password"]) < 8)
                {
                  flash("New password not at least 8 characters");
                }
              }
              else
              {
                flash("New passwords do not match");
              }
            }
            else
            {
              flash("Current password is incorrect");
            }
          }
        }
//fetch/select fresh data in case anything changed
        $stmt = $db->prepare("SELECT email, username, first_name, last_name, isPublic from Users WHERE id = :id LIMIT 1");
        $stmt->execute([":id" => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result && ! has_role("Admin")) {
            $email = $result["email"];
            $username = $result["username"];
            $fName = $result["first_name"];
            $lName = $result["last_name"];
            $isPublic = $result["isPublic"];
            //let's update our session too
            $_SESSION["user"]["email"] = $email;
            $_SESSION["user"]["username"] = $username;
            $_SESSION["user"]["first_name"] = $fName;
            $_SESSION["user"]["last_name"] = $lName;
            $_SESSION["user"]["isPublic"] = $isPublic;
        }
        die(header("Location: profile.php?id=$id"));
    }
    else {
        //else for $isValid, though don't need to put anything here since the specific failure will output the message
    }
  }
}

?>
  <?php if($id != -1): ?>
    <form method="POST">
        <?php if(($isVis == '1') || ($id == get_user_id()) || has_role("Admin")): ?>
        <label for="email">Email</label>
        <br>
        <input type="email" name="email" value="<?php safer_echo($results['email']); ?>"/>
        <br>
        <?php endif; ?>
        
        <label for="username">Username</label>
        <br>
        <input type="text" maxlength="60" name="username" value="<?php safer_echo($results['username']); ?>"/>
        <br>
        
        <label for="fName">First Name</label>
        <br>
        <input type="text" name="fName" value="<?php safer_echo($results['first_name']); ?>"/>
        <br>
        
        <label for="lName">Last Name</label>
        <br>
        <input type="text" name="lName" value="<?php safer_echo($results['last_name']); ?>"/>
        <br>
        
        <?php if($id == get_user_id() || has_role("Admin")): ?>
        <!-- DO NOT PRELOAD PASSWORD-->
        
        <label for="current">Current Password</label>
        <br>
        <input type="password" name="current"/>
        <br>
        
        <label for="pw">New Password</label>
        <br>
        <input type="password" name="password"/>
        <br>
        <label for="cpw">Confirm New Password</label>
        <br>
        <input type="password" name="confirm"/>
        <br>
        
        <label for="privacy">Privacy Setting</label>
        <br>
        <div class="shift">
        <input type="radio" name="priv" value="1" <?php echo ($_SESSION["user"]["isPublic"] == '1') ? 'checked="checked"' : ''; ?> />
        <label>Public</label>
        <br>
        <input type="radio" name="priv" value="0" <?php echo ($_SESSION["user"]["isPublic"] == '0') ? 'checked="checked"' : ''; ?> />
        <label>Private</label>
        <br>
        </div>
        <input type="submit" name="saved" value="Save Profile"/>
        <?php endif; ?>
    </form>
  <?php else: ?>
    <label>User id does not exist</label>
  <?php endif; ?>
</div>
<?php require(__DIR__ . "/partials/flash.php");
