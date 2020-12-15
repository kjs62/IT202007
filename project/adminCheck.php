<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!has_role("Admin")) {
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}
?>
<div class="drift">
<form method="POST">
	<label>Lookup Users</label>
  <br>
	<input type="text" placeholder="First/Last Name" name="name"/>
  <br>
  <input type="submit" name="lookup" value="Lookup Users"/>
  <br>
  <label>Lookup Account Number</label>
  <br>
	<input type="int" maxlength="12" placeholder="Account Number" name="accNum"/>
  <br>
  <input type="submit" name="lookupUser" value="Lookup Accounts"/>

</form>

<?php
$results=[];
$db = getDB();
$stmt = $db->prepare("SELECT * from Users");
$r = $stmt->execute();
if($r)
  $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
  
$res = true;
$name = null;

if(isset($_POST["lookup"])){
  $name = $_POST['name'];
  $res = false;
}

  
$res2 = true;
$accNum = null;

if(isset($_POST["lookupUser"])){
  $accNum = $_POST['accNum'];
  $res2 = false;
}

$results2=[];
$stmt2 = $db->prepare("SELECT * from Accounts where account_number = :q");
$r2 = $stmt2->execute([":q" => $accNum]);
if($r2)
  $results2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);

?>
<label>User Search</label>
<div class="results">
    <?php if (count($results) > 0): ?>
        <div class="list-group">
              <?php foreach ($results as $r): ?>
                <?php if($res || $name == ''): ?>
                <div class="list-group-item">
                    <div>
                        <div>User:</div>
                        <a type="button" href="profile.php?id=<?php safer_echo($r['id']); ?>"><?php safer_echo($r['first_name'] . " " . $r['last_name'] . " (" . $r['username'] . ")"); ?></a>
                    </div>
                    <br>
                </div>
                <?php elseif($r['first_name'] == $name || $r['last_name'] == $name): ?>
                <div class="list-group-item">
                    <div>
                        <div>User:</div>
                        <a type="button" href="profile.php?id=<?php safer_echo($r['id']); ?>"><?php safer_echo($r['first_name'] . " " . $r['last_name'] . " (" . $r['username'] . ")"); ?></a>
                    </div>
                    <br>
                </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No results</p>
    <?php endif; ?>
</div>
<br>
<h2>Account Search</h2>
<div class="results">
    <?php if (count($results2) > 0): ?>
        <div class="list-group">
              <?php foreach ($results2 as $r2): ?>
                <div class="list-group-item">
                    <div>
                        <div>Account Number: <?php safer_echo($accNum); ?> </div>
                        <a type="button" href="transaction_history.php?id=<?php safer_echo($r2['id']); ?>">Transaction History</a>
                    </div>
                    <br>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No results</p>
    <?php endif; ?>
</div>
</div>
<?php require(__DIR__ . "/partials/flash.php");