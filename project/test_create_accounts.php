<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!has_role("Admin")) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}
?>
<div class="drift">
<form method="POST">
	<label>Account Number</labe>
 <br>
	<input type="text" minlength="12" placeholder="Account Number" name="accNum"/>
  <br>
  <label>Account Type</label>
  <br>
	<input name="accType" placeholder="Account"/>
 <br>
  <label>Balance</labe>
  <br>
	<input type="float" min="0.00" name="balance"/>
 <br>
	<input type="submit" name="save" value="Create"/>
</form>

<?php
if(isset($_POST["save"])){
	//TODO add proper validation/checks
	$accNum = $_POST["accNum"];
  $accType = $_POST["accType"];
	$user = get_user_id();
  $balance = $_POST["balance"];
	$db = getDB();
	$stmt = $db->prepare("INSERT INTO Accounts (account_number, account_type, user_id, balance) VALUES(:accNum, :accType, :user, :balance)");
	$r = $stmt->execute([
		":accNum"=>$accNum,
		":accType"=>$accType,
		":user"=>$user,
    ":balance"=>$balance
	]);
	if($r){
		flash("Created successfully with id: " . $db->lastInsertId());
	}
	else{
		$e = $stmt->errorInfo();
		flash("Error creating: " . var_export($e, true));
	}
}
?>
</drift>
<?php require(__DIR__ . "/partials/flash.php");