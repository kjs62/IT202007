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
    <h3>Transfer Transaction between single user</h3>
    <form method="POST">
        <label>Account</label>
        <br>
        <select name="source">
            <?php foreach($users as $user): ?>
              <option value="<?= $user['id']; ?>"><?= $user['account_number']; ?></option>
            <?php endforeach; ?>
        </select>
        <br>
        
        <label>Transaction Destination</label>
        <br>
        <select name="dest">
            <?php foreach($users as $user): ?>
              <option value="<?= $user['id']; ?>"><?= $user['account_number']; ?></option>
            <?php endforeach; ?>
        </select>
        
        <br>
        <label>Amount</label>
        <br>
        <input type="float" min="0.00" name="amount"/>
        <br>
        <label>Memo (Optional)</label>
        <br>
        <input type="text" placeholder-"Optional" name="memo"/>
        <br>
        <input type="submit" name="save" value="Create"/>
    </form>

<?php
function do_bank_action($account1, $account2, $amountChange, $memo){
  $db = getDB();
  $query = null;
  $stmt2 = $db->prepare("SELECT id, account_number, user_id, account_type, opened_date, last_updated, balance from Accounts WHERE active = 'active' AND id like :q");
  $r2 = $stmt2->execute([":q" => "%$query%"]);
  if ($r2) {
        $results = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    }
  
  $a1total = null;
  $a2total = null;
  $acc1Loan = false;
  $acc2Loan = false;
  foreach($results as $r)
  {
    if($account1 == $r["id"])
    {
        $a1total = $r["balance"];
        if($r['account_type'] == 'Loan')
          $acc1Loan = true;
    }
    if($account2 == $r["id"])
    {
      $a2total = $r["balance"];
      if($r['account_type'] == 'Loan')
          $acc2Loan = true;
    }
  }
  
  if(!$acc1Loan)
  {
    if($acc2Loan && ((-$amountChange) > $a2total))
    {
      flash('Your loan only has a balance of $' . $a2total . ' that needs to be payed off. Enter a transfer balance of $' . $a2total . " or less");
    }
    else
    {
      if($a1total+$amountChange >= 0)
      {
    	$query = "INSERT INTO `Transactions` (`act_src_id`, `act_dest_id`, `amount`, `action_type`, `expected_total`, `memo`) 
    	VALUES(:p1a1, :p1a2, :p1change, :type, :a1total, :memo), 
    			(:p2a1, :p2a2, :p2change, :type, :a2total, :memo)";
    	
    	$stmt = $db->prepare($query);
    	$stmt->bindValue(":p1a1", $account1);
    	$stmt->bindValue(":p1a2", $account2);
    	$stmt->bindValue(":p1change", $amountChange);
    	$stmt->bindValue(":type", "Transfer");
    	$stmt->bindValue(":a1total", $a1total+$amountChange);
      $stmt->bindValue(":memo", $memo);
    	//flip data for other half of transaction
    	$stmt->bindValue(":p2a1", $account2);
    	$stmt->bindValue(":p2a2", $account1);
      if($acc2Loan)
      {
      	$stmt->bindValue(":p2change", $amountChange);
      	$stmt->bindValue(":type", "Transfer");
      	$stmt->bindValue(":a2total", $a2total+$amountChange);
      }
      else
      {
        $stmt->bindValue(":p2change", ($amountChange*-1));
      	$stmt->bindValue(":type", "Transfer");
      	$stmt->bindValue(":a2total", $a2total-$amountChange);
      }
      $stmt->bindValue(":memo", $memo);
    	$result = $stmt->execute();
      if ($result) {
            flash("Transfer successfully made");
        }
        else {
            $e = $stmt->errorInfo();
            flash("Error creating: " . var_export($e, true));
        }
        $stmt = $db->prepare("UPDATE Accounts SET balance = (SELECT SUM(amount) FROM Transactions WHERE Transactions.act_src_id = Accounts.id)");
        $r = $stmt->execute();
    	return $result;
     }
     else
      {
        flash("Error: You cannot transfer more than you have in your source account");
      }
     }
  }
  else
  {
    flash("Error: you cannot transfer your balance from your loan, you can only transfer into your loan");
  }
}

if (isset($_POST["save"])) {
    $amount = (float)$_POST["amount"];
    $source = $_POST["source"];
    $dest = $_POST["dest"];
    $memo = $_POST["memo"];
    $user = get_user_id();
    if($amount > 0 && $source != $dest)
      do_bank_action($source, $dest, ($amount * -1), $memo);
    else
    {
      if($amount <= 0)
        flash("Enter a positive value");
      if($source == $dest)
        flash("Cannot transfer to the same account");
    }
}
?>
</div>
<?php require(__DIR__ . "/partials/flash.php");