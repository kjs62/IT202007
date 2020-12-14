<?php
session_start();//we can start our session here so we don't need to worry about it on other pages
require_once(__DIR__ . "/db.php");
//this file will contain any helpful functions we create
//I have provided two for you
function is_logged_in() {
    return isset($_SESSION["user"]);
}

function has_role($role) {
    if (is_logged_in() && isset($_SESSION["user"]["roles"])) {
        foreach ($_SESSION["user"]["roles"] as $r) {
            if ($r["name"] == $role) {
                return true;
            }
        }
    }
    return false;
}

function get_username() {
    if (is_logged_in() && isset($_SESSION["user"]["username"])) {
        return $_SESSION["user"]["username"];
    }
    return "";
}

function get_email() {
    if (is_logged_in() && isset($_SESSION["user"]["email"])) {
        return $_SESSION["user"]["email"];
    }
    return "";
}

function get_user_id() {
    if (is_logged_in() && isset($_SESSION["user"]["id"])) {
        return $_SESSION["user"]["id"];
    }
    return -1;
}

function get_fName() {
    if (is_logged_in() && isset($_SESSION["user"]["first_name"])) {
        return $_SESSION["user"]["first_name"];
    }
    return "";
}

function get_lName() {
    if (is_logged_in() && isset($_SESSION["user"]["last_name"])) {
        return $_SESSION["user"]["last_name"];
    }
    return "";
}

function safer_echo($var) {
    if (!isset($var)) {
        echo "";
        return;
    }
    echo htmlspecialchars($var, ENT_QUOTES, "UTF-8");
}

//for flash feature
function flash($msg) {
    if (isset($_SESSION['flash'])) {
        array_push($_SESSION['flash'], $msg);
    }
    else {
        $_SESSION['flash'] = array();
        array_push($_SESSION['flash'], $msg);
    }

}

function getMessages() {
    if (isset($_SESSION['flash'])) {
        $flashes = $_SESSION['flash'];
        $_SESSION['flash'] = array();
        return $flashes;
    }
    return array();
}
function calcLoanAPY(){
	$db = getDB();
	$numOfMonths = 1;//1 for monthly
	$stmt = $db->prepare("SELECT id, APY, balance FROM Accounts WHERE account_type = 'Loan' AND IFNULL(nextAPY, TIMESTAMPADD(MONTH,:months,opened_date)) <= current_timestamp"); 
	$r = $stmt->execute([":months"=>$numOfMonths]);
	if($r){
		$accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if($accounts){
			$stmt = $db->prepare("SELECT id FROM Accounts where account_number = '000000000000'");
			$r = $stmt->execute();
			if(!$r){
				flash(var_export($stmt->errorInfo(), true), "danger");
			}
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			$world_id = $result["id"];
			foreach($accounts as $account){
				$apy = $account["APY"];
				//if monthly divide accordingly
				$apy /= 12;
				$balance = (float)$account["balance"];
				$change = $balance * $apy;
				do_bank_action2($world_id, $account["id"], ($change * -1), "interest", "APY Calc");
				
				$stmt = $db->prepare("UPDATE Accounts set balance = (SELECT IFNULL(SUM(amount),0) FROM Transactions WHERE act_src_id = :id), nextAPY = TIMESTAMPADD(MONTH,:months,current_timestamp) WHERE id = :id");
				$r = $stmt->execute([":id"=>$account["id"], ":months"=>$numOfMonths]);
				if(!$r){
					flash(var_export($stmt->errorInfo(), true), "danger");
				}
			}
		}
	}
	else{
		flash(var_export($stmt->errorInfo(), true), "danger");
	}
}

function do_bank_action2($account1, $account2, $amountChange, $memo, $actType){
  $db = getDB();
  $query = null;
  $stmt2 = $db->prepare("SELECT id, account_number, user_id, account_type, opened_date, last_updated, balance from Accounts WHERE id like :q");
  $r2 = $stmt2->execute([":q" => "%$query%"]);
  if ($r2) {
        $results = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    }

  $a1total = null;
  $a2total = null;
  foreach($results as $r)
  {
    if($account1 == $r["id"])
        $a1total = $r["balance"];
    if($account2 == $r["id"])
      $a2total = $r["balance"];
  }
	$query = "INSERT INTO `Transactions` (`act_src_id`, `act_dest_id`, `amount`, `action_type`, `expected_total`, `memo`) 
	VALUES(:p1a1, :p1a2, :p1change, :type, :a1total, :memo), 
			(:p2a1, :p2a2, :p2change, :type, :a2total, :memo)";
	
	$stmt = $db->prepare($query);
	$stmt->bindValue(":p1a1", $account1);
	$stmt->bindValue(":p1a2", $account2);
	$stmt->bindValue(":p1change", $amountChange);
	$stmt->bindValue(":type", $actType);
	$stmt->bindValue(":a1total", $a1total+$amountChange);
  $stmt->bindValue(":memo", $memo);
	//flip data for other half of transaction
	$stmt->bindValue(":p2a1", $account2);
	$stmt->bindValue(":p2a2", $account1);
	$stmt->bindValue(":p2change", ($amountChange*-1));
	$stmt->bindValue(":type", $actType);
	$stmt->bindValue(":a2total", $a2total-$amountChange);
  $stmt->bindValue(":memo", $memo);
	$result = $stmt->execute();
  if ($result) {
        flash("Interest transaction was made");
    }
    else {
        $e = $stmt->errorInfo();
        flash("Error creating: " . var_export($e, true));
    }
    $stmt = $db->prepare("UPDATE Accounts SET balance = (SELECT SUM(amount) FROM Transactions WHERE Transactions.act_src_id = Accounts.id)");
    $r = $stmt->execute();
	return $result;
}
//end flash
?>