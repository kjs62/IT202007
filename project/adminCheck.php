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

?>
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
</div>
<?php require(__DIR__ . "/partials/flash.php");