<!DOCTYPE html>
<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<div class="drift">
<form method="POST">
  <label>Create Savings Account</label>
  <br>
  <label>APY will automatically be set to 8%</label>
  <br>
  <label>Balance</label>
  <br>
	<input id="bal" type="float" min="5.00" name="balance"/>
 <br>
	<input type="submit" name="save" value="Create"/>
</form>
<?php
if(isset($_POST["save"])){
	//TODO add proper validation/checks
  $db = getDB();
	$accNum = rand(000000000001, 999999999999);
  for($i = strlen($accNum); $i < 12; $i++)
  {
    $accNum = ("0" . $accNum);
  }
  $accType = "Savings";
	$user = get_user_id();
  $apy = 0.08;
  $balance = $_POST["balance"];
  if($balance >= 5)
  {
    do {
      $accNum = rand(000000000000, 999999999999);
      for($i = strlen($accNum); $i < 12; $i++)
      {
        $accNum = ("0" . $accNum);
      }
      $stmt = $db->prepare("INSERT INTO Accounts (account_number, account_type, user_id, balance, APY) VALUES(:accNum, :accType, :user, :balance, :apy)");
  	$r = $stmt->execute([
  		":accNum"=>$accNum,
  		":accType"=>$accType,
  		":user"=>$user,
      ":apy" => $apy,
      ":balance"=>0
      ]);
      $e = $stmt->errorInfo();
    }while($e[0] == "23000");
    $numOfMonths = 1;
    $lastId = $db->lastInsertId();
    $stmt = $db->prepare("UPDATE Accounts set nextAPY = TIMESTAMPADD(MONTH,:months,opened_date) WHERE id = :id");
    $r = $stmt->execute([":id"=>$lastId, ":months"=>$numOfMonths]);
    if($r){
  		flash("Account created successfully with account number: " . $accNum);
  	}
  	else{
  		$e = $stmt->errorInfo();
  		flash("Error creating: " . var_export($e, true));
  	}
   
   $query = null;
   $stmt2 = $db->prepare("SELECT id, account_number, user_id, account_type, opened_date, last_updated, balance from Accounts");
    $r2 = $stmt2->execute();
    if ($r2) {
          $results = $stmt2->fetchAll(PDO::FETCH_ASSOC);
      }
   $a1total = null;
  foreach($results as $r)
  {
    if($r["id"] == 0)
        $a1total = $r["balance"];
  }
    
   $query = "INSERT INTO `Transactions` (`act_src_id`, `act_dest_id`, `amount`, `action_type`, `expected_total`) 
	VALUES(:p1a1, :p1a2, :p1change, :type, :a1total), 
			(:p2a1, :p2a2, :p2change, :type, :a2total)";
      
    $stmt = $db->prepare($query);
  	$stmt->bindValue(":p1a1", 0);
  	$stmt->bindValue(":p1a2", $lastId);
  	$stmt->bindValue(":p1change", ($balance*-1));
  	$stmt->bindValue(":type", "Deposit");
  	$stmt->bindValue(":a1total", $a1total-$balance);
  	//flip data for other half of transaction
  	$stmt->bindValue(":p2a1", $lastId);
  	$stmt->bindValue(":p2a2", 0);
  	$stmt->bindValue(":p2change", $balance);
  	$stmt->bindValue(":type", "Deposit");
  	$stmt->bindValue(":a2total", $balance);
  	$result = $stmt->execute();
    if ($result) {
          flash("Transaction created successfully");
      }
    else {
         $e = $stmt->errorInfo();
         flash("Error creating: " . var_export($e, true));
    }
    $stmt = $db->prepare("UPDATE Accounts SET balance = (SELECT SUM(amount) FROM Transactions WHERE Transactions.act_src_id = Accounts.id)");
    $r = $stmt->execute();
	  die(header("Location: list_accounts.php"));
  }
  else
  {
    flash('Balance must be $5.00 or more');
  }
}
?>
</drift>
<?php require(__DIR__ . "/partials/flash.php");