<!DOCTYPE html>
<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<div class="drift">
<?php
$db = getDB();
$users = [];
$id = get_user_id();
$stmt = $db->prepare("SELECT * from Accounts WHERE user_id = :id");
$r = $stmt->execute([":id" => $id]);
if ($r) {
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<form method="POST">
  <label>Take out Loan</labe>
  <br>
  <label>APY will automatically be set to 8%</label>
  <br>
  <label>Balance</label>
  <br>
	<input id="bal" type="float" min="5.00" name="balance"/>
 <br>
 <label>Account deposit</label>
 <br>
        <select name="source">
            <?php foreach($users as $user): ?>
              <option value="<?= $user['id']; ?>"><?= $user['account_number']; ?></option>
            <?php endforeach; ?>
        </select>
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
  $accType = "Loan";
	$user = get_user_id();
  $apy = 0.08;
  $balance = $_POST["balance"];
  if($balance >= 500)
  {
    do {
      $stmt = $db->prepare("INSERT INTO Accounts (account_number, account_type, user_id, balance, APY) VALUES(:accNum, :accType, :user, :balance, :apy)");
  	$r = $stmt->execute([
  		":accNum"=>$accNum,
  		":accType"=>$accType,
  		":user"=>$user,
      ":apy" => $apy,
      ":balance"=>0
      ]);
      $accNum = rand(000000000000, 999999999999);
      for($i = strlen($accNum); $i < 12; $i++)
      {
        $accNum = ("0" . $accNum);
      }
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
   
   $source = $_POST["source"];
   $query = null;
   $stmt2 = $db->prepare("SELECT id, account_number, user_id, account_type, opened_date, last_updated, balance from Accounts WHERE id like :q");
    $r2 = $stmt2->execute([":q" => "%$query%"]);
    if ($r2) {
          $results = $stmt2->fetchAll(PDO::FETCH_ASSOC);
      }
      
    foreach($results as $r)
    {
      if($source == $r["id"])
          $a2total = $r["balance"];
    }
  
   $query = "INSERT INTO `Transactions` (`act_src_id`, `act_dest_id`, `amount`, `action_type`, `expected_total`) 
	VALUES(:p1a1, :p1a2, :p1change, :type, :a1total), 
			(:p2a1, :p2a2, :p2change, :type, :a2total)";
      
    $stmt = $db->prepare($query);
  	$stmt->bindValue(":p1a1", $lastId);
  	$stmt->bindValue(":p1a2", $source);
  	$stmt->bindValue(":p1change", $balance);
  	$stmt->bindValue(":type", "Deposit");
  	$stmt->bindValue(":a1total", $balance);
  	//flip data for other half of transaction
  	$stmt->bindValue(":p2a1", $source);
  	$stmt->bindValue(":p2a2", $lastId);
  	$stmt->bindValue(":p2change", $balance);
  	$stmt->bindValue(":type", "Deposit");
  	$stmt->bindValue(":a2total", $a2total+$balance);
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
    flash('Balance must be $500.00 or more');
  }
}
?>
</drift>
<?php require(__DIR__ . "/partials/flash.php");