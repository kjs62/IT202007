<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!has_role("Admin")) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}
?>
<div class="drift">
<?php
$db = getDB();
$users = [];
$stmt = $db->prepare("SELECT * from Users WHERE deactivated = 'false' AND (username != 'WorldUser')");
$r = $stmt->execute();
if ($r) {
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$users2 = [];
$stmt = $db->prepare("SELECT * from Users WHERE deactivated = 'true'");
$r = $stmt->execute();
if ($r) {
    $users2 = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if (isset($_POST["save"])) {
  $deactivate = $_POST['deactivate'];
  $stmt = $db->prepare("UPDATE Users set deactivated = 'true' where id = :id");
  $r = $stmt->execute([":id" => $deactivate]);
  if ($r) {
    flash("User was Deactivated");
    die(header("Location: adminDeactivate.php"));
  }
  else {
    flash("Error updating account");
  }
}

if (isset($_POST["save2"])) {
  $activate = $_POST['activate'];
  $stmt = $db->prepare("UPDATE Users set deactivated = 'false' where id = :id");
  $r = $stmt->execute([":id" => $activate]);
  if ($r) {
    flash("User was activated");
    die(header("Location: adminDeactivate.php"));
  }
  else {
    flash("Error updating account");
  }
}
?>

<form method="POST">
        <label>User to Deactivate</label>
        <br>
        <select name="deactivate">
            <?php foreach($users as $user): ?>
              <option value="<?= $user['id']; ?>"><?= $user['username']; ?></option>
            <?php endforeach; ?>
        </select>
        <br>
        <input type="submit" name="save" value="Deactivate"/>
        
        <label>User to Reactivate</label>
        <br>
        <select name="activate">
            <?php foreach($users2 as $user2): ?>
              <option value="<?= $user2['id']; ?>"><?= $user2['username']; ?></option>
            <?php endforeach; ?>
        </select>
        <br>
        <input type="submit" name="save2" value="Re-Activate"/>
</drift>
<?php require(__DIR__ . "/partials/flash.php");