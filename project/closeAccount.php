<?php require_once(__DIR__ . "/partials/nav.php"); ?>

<div class="drift">
<?php
$db = getDB();
$users = [];
$id = get_user_id();
$stmt = $db->prepare("SELECT * from Accounts WHERE active = 'active' AND user_id = :id");
$r = $stmt->execute([":id" => $id]);
if ($r) {
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
    <h3>Close an Account</h3>
    <form method="POST">
        <label>Account</label>
        <br>
        <select name="source">
            <?php foreach($users as $user): ?>
              <option value="<?= $user['id']; ?>"><?= $user['account_number']; ?></option>
            <?php endforeach; ?>
        </select>
        <br>
        <input type="submit" name="save" value="Close"/>
    </form>

<?php
if (isset($_POST["save"])) {
    $source = $_POST["source"];
    $user = get_user_id();
    $stmt = $db->prepare("SELECT * from Accounts WHERE active = 'active' AND id like :q LIMIT 1");
    $r = $stmt->execute([":q" => $source]);
    if ($r) {
      $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
      flash("There was a problem fetching the results");
    }
    
    foreach($results as $curr)
    {
      if($curr["balance"] == 0)
      {
        $stmt = $db->prepare("UPDATE Accounts set active = 'closed' WHERE active = 'active' AND id = :id");
        $r = $stmt->execute([":id" => $source]);
        if ($r) {
            flash("Closed Account");
            die(header("Location: closeAccount.php"));
        }
        else {
            flash("Error updating profile");
        }
      }
      else
      {
        flash("Please make sure you transfer or withdraw all of your funds");
        flash("You currently have $" . $curr['balance'] . " in this account");
      }
    }
    
}
?>
</div>
<?php require(__DIR__ . "/partials/flash.php");