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
//we'll put this at the top so both php block have access to it
if(isset($_GET["id"])){
	$id = $_GET["id"];
}
?>
<?php
//saving
if(isset($_POST["save"])){
	//TODO add proper validation/checks
	$accNum = $_POST["accNum"];
  $accType = $_POST["accType"];
  $balance = $_POST["balance"];
	$user = get_user_id();
	$db = getDB();
	if(isset($id)){
		$stmt = $db->prepare("UPDATE Accounts set account_number=:accNum, account_type=:accType, user_id = :user, balance = :balance where id=:id");
		$r = $stmt->execute([
			":accNum"=>$accNum,
  		":accType"=>$accType,
  		":user"=>$user,
      ":balance"=>$balance,
      ":id"=>$id
		]);
		if($r){
			flash("Updated successfully with id: " . $id);
		}
		else{
			$e = $stmt->errorInfo();
			flash("Error updating: " . var_export($e, true));
		}
	}
	else{
		flash("ID isn't set, we need an ID in order to update");
	}
}
?>
<?php
//fetching
$result = [];
if(isset($id)){
	$id = $_GET["id"];
	$db = getDB();
	$stmt = $db->prepare("SELECT * FROM Accounts where id = :id");
	$r = $stmt->execute([":id"=>$id]);
	$result = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<form method="POST">
	<label>Account Number</label>
 <br>
  <input type="text" minlength="12" placeholder="Account Number" name="accNum" value="<?php echo $result["account_number"];?>" />
  <br>
  <label>Account Type</label>
  <br>
	<input name="accType" placeholder="Account" value="<?php echo $result["account_type"];?>"/>
 <br>
  <label>Balance</label>
  <br>
	<input type="float" min="0.00" name="balance" value="<?php echo $result["balance"];?>"/>
 <br>
	<input type="submit" name="save" value="Update"/>
</form>

</drift>
<?php require(__DIR__ . "/partials/flash.php");
