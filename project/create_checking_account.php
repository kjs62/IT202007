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
  <label>Create Checking Account</labe>
  <br>
  <label>Balance</label>
  <br>
	<input type="float" min="5.00" name="balance"/>
 <br>
	<input type="submit" name="save" value="Create"/>
</form>

<?php
if(isset($_POST["save"])){
	//TODO add proper validation/checks
	$accNum = rand(000000000000, 999999999999);
  $accType = "Checking";
	$user = get_user_id();
  $balance = $_POST["balance"];
	$db = getDB();
  if($balance >= 5)
  {
    do {
      $stmt = $db->prepare("INSERT INTO Accounts (account_number, account_type, user_id, balance) VALUES(:accNum, :accType, :user, :balance)");
  	$r = $stmt->execute([
  		":accNum"=>$accNum,
  		":accType"=>$accType,
  		":user"=>$user,
      ":balance"=>$balance
      ]);
      $accNum = rand(000000000000, 999999999999);
      $e = $stmt->errorInfo();
    }while($e[0] == "23000");
    if($r){
  		flash("Created successfully with id: " . $db->lastInsertId());
      //die(header("Location: home.php"));
  	}
  	else{
  		$e = $stmt->errorInfo();
  		flash("Error creating: " . var_export($e, true));
  	}
  }
  else
  {
    flash('Balance must be $5.00 or more');
  }
}
?>
</drift>
<?php require(__DIR__ . "/partials/flash.php");