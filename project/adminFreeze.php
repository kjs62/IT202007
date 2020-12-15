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
$stmt = $db->prepare("SELECT * from Accounts WHERE active = 'active' AND frozen = 'false'");
$r = $stmt->execute();
if ($r) {
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$users2 = [];
$stmt = $db->prepare("SELECT * from Accounts WHERE active = 'active' AND frozen = 'true'");
$r = $stmt->execute();
if ($r) {
    $users2 = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if (isset($_POST["save"])) {
  $toFreeze = $_POST['toFreeze'];
  $stmt = $db->prepare("UPDATE Accounts set frozen = 'true' where active = 'active' and id = :id");
  $r = $stmt->execute([":id" => $toFreeze]);
  if ($r) {
    flash("Account was frozen");
    die(header("Location: adminFreeze.php"));
  }
  else {
    flash("Error updating account");
  }
}

if (isset($_POST["save2"])) {
  $toUnfreeze = $_POST['toUnfreeze'];
  $stmt = $db->prepare("UPDATE Accounts set frozen = 'false' where active = 'active' and id = :id");
  $r = $stmt->execute([":id" => $toUnfreeze]);
  if ($r) {
    flash("Account was unfrozen");
    die(header("Location: adminFreeze.php"));
  }
  else {
    flash("Error updating account");
  }
}
?>

<form method="POST">
        <label>Account to Freeze</label>
        <br>
        <select name="toFreeze">
            <?php foreach($users as $user): ?>
              <option value="<?= $user['id']; ?>"><?= $user['account_number']; ?></option>
            <?php endforeach; ?>
        </select>
        <br>
        <input type="submit" name="save" value="Freeze"/>
        
        <label>Account to Unfreeze</label>
        <br>
        <select name="toUnfreeze">
            <?php foreach($users2 as $user2): ?>
              <option value="<?= $user2['id']; ?>"><?= $user2['account_number']; ?></option>
            <?php endforeach; ?>
        </select>
        <br>
        <input type="submit" name="save2" value="Unfreeze"/>
</drift>
<?php require(__DIR__ . "/partials/flash.php");