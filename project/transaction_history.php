<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<div class="drift">
<?php
if (isset($_GET["id"])) {
    $id = $_GET["id"];
}
$transType = array("", "Transfer", "Withdraw", "Deposit");
?>
<h3>Filter Transactions</h3>
    <form method="POST">
        <label>Filter by Account Type:</label>
        <br>
        <select name="tran">
            <?php foreach($transType as $tran): ?>
              <option value="<?= $tran; ?>"><?= $tran; ?></option>
            <?php endforeach; ?>
        </select>
        <br>
        <br>
        <label>Filter between dates:</label>
        <br>
        <label>Date 1</label>
        <br>
        <input type="text" placeholder="YYYY-MM-DD" name="startDate"/>
        <br>
        <label>Date 2</label>
        <br>
        <input type="text" placeholder="YYYY-MM-DD" name="endDate"/>
        <br>
        <input type="submit" name="filter" value="Filter"/>
    </form>
<?php
$query = "";
$results = [];
if (isset($id)) {
    $db = getDB();
    $UserId = get_user_id();
    
    $page = 1;
    $per_page = 10;
    if(isset($_GET["page"])){
        try {
            $page = (int)$_GET["page"];
        }
        catch(Exception $e){
        }
    }
    
    $stmt = $db->prepare("SELECT count(*) as total from Transactions WHERE act_src_id like :q ORDER BY id DESC LIMIT 10");
    $r = $stmt->execute([":q" => "%$id%"]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $total = 0;
    if($result){
        $total = (int)$result["total"];
    }
    $total_pages = ceil($total / $per_page);
    $offset = ($page-1) * $per_page;
    
    $stmt = $db->prepare("SELECT * from Transactions WHERE act_src_id like :q ORDER BY id DESC LIMIT :offset, :count");
    $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
    $stmt->bindValue(":count", $per_page, PDO::PARAM_INT);
    $stmt->bindValue(":q", $id);
    
    
    if(isset($_POST["filter"]) || isset($_SESSION['filtered']))
    {
      if(isset($_POST["tran"]))
      {
        $_SESSION["tranActType"] = $_POST["tran"];
        $actType = $_SESSION["tranActType"];
      }
      else
        $actType = $_SESSION["tranActType"];
      
      $startDate = '0000-01-01';
      $endDate = '9999-12-31';
      
      if(isset($_POST["startDate"]) || isset($_POST["endDate"]))
      {
        if($_POST["startDate"] != "" && $_POST["endDate"] != "")
        {
          $_SESSION["tranStart"] = $_POST["startDate"];
          $_SESSION["tranEnd"] = $_POST["endDate"];
          $startDate = $_SESSION["tranStart"];
          $endDate = $_SESSION["tranEnd"];
        }
        elseif(($_POST["startDate"] != "" && $_POST["endDate"] == "") || ($_POST["endDate"] != "" && $_POST["startDate"] == ""))
        {
          echo "Please enter both date fields to properly filter<br>Will continue search using dates 0000-01-01 and 9999-12-31";
          $_SESSION["tranStart"] = '0000-01-01';
          $_SESSION["tranEnd"] = '9999-12-31';
          $startDate = $_SESSION["tranStart"];
          $endDate = $_SESSION["tranEnd"];
        }
      }
      elseif(isset($_SESSION["filtered"]))
      { 
        if($_SESSION["filtered"])
        {
          $startDate = $_SESSION["tranStart"];
          $endDate = $_SESSION["tranEnd"];
        }
      }
      else
      {
        $_SESSION["tranStart"] = '0000-01-01';
        $_SESSION["tranEnd"] = '9999-12-31';
        $startDate = $_SESSION["tranStart"];
        $endDate = $_SESSION["tranEnd"];
      }
      
      $_SESSION['filtered'] = true;
      
      if($actType != "")
      {
        $stmt = $db->prepare("SELECT count(*) as total from Transactions WHERE (action_type like :a) AND (act_src_id like :q) AND (created BETWEEN :s AND :e) ORDER BY id DESC LIMIT 10");
        $r = $stmt->execute([":q" => "%$id%", ":a" => $actType, ":s" => $startDate, ":e" => $endDate]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $total = 0;
        if($result){
          $total = (int)$result["total"];
        }
        $total_pages = ceil($total / $per_page);
        $offset = ($page-1) * $per_page;
        
        $stmt = $db->prepare("SELECT * from Transactions WHERE (action_type like :a) AND (act_src_id like :q) AND (created BETWEEN :s AND :e) ORDER BY id DESC LIMIT :offset, :count");
        $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
        $stmt->bindValue(":count", $per_page, PDO::PARAM_INT);
        $stmt->bindValue(":a", $actType);
        $stmt->bindValue(":s", $startDate);
        $stmt->bindValue(":e", $endDate);
        $stmt->bindValue(":q", $id);
      }
      else
      {
        $stmt = $db->prepare("SELECT count(*) as total from Transactions WHERE (act_src_id like :q) AND (created BETWEEN :s AND :e) ORDER BY id DESC LIMIT 10");
        $r = $stmt->execute([":q" => "%$id%", ":s" => $startDate, ":e" => $endDate]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $total = 0;
        if($result){
          $total = (int)$result["total"];
        }
        $total_pages = ceil($total / $per_page);
        $offset = ($page-1) * $per_page;
        
        $stmt = $db->prepare("SELECT * from Transactions WHERE (act_src_id like :q) AND (created BETWEEN :s AND :e) ORDER BY id DESC LIMIT :offset, :count");
        $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
        $stmt->bindValue(":count", $per_page, PDO::PARAM_INT);
        $stmt->bindValue(":s", $startDate);
        $stmt->bindValue(":e", $endDate);
        $stmt->bindValue(":q", $id);
      }
    }
    
    if(isset($_POST["reset"]))
    {
      unset($_POST["filter"]);
      unset($_SESSION["filtered"]);
      die(header("Location: transaction_history.php?id=$id&page=1"));
    }
    
    $r = $stmt->execute();
    if ($r) {
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    $stmt2 = $db->prepare("SELECT id, account_number, account_type from Accounts WHERE user_id = :UserId");
  $r2 = $stmt2->execute([":UserId" => $UserId]);
  if ($r2) {
        $results2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
<h3>List Transactions</h3>
<div class="results">
  <label>Currently filtering by type:
  <?php 
    if(isset($_SESSION['filtered'])):
      if($_SESSION['filtered']):
        if($actType != ""):
          echo $actType;
        else:
          echo "All";
        endif;
      endif;
    else:
      echo "All";
    endif; ?>
     between Dates:
    <?php 
    if(isset($_SESSION['filtered'])):
      if($_SESSION['filtered']):
        echo $startDate . " and " . $endDate;
      endif;
    else:
      echo "0000-01-01 and 9999-12-31";
    endif; ?>
    </label>
    <?php if (count($results) > 0): ?>
        <div class="list-group">
            <?php foreach ($results as $r): ?>
                <div class="list-group-item">
                    <?php foreach ($results2 as $r2): ?>
                    <?php if ($r2["id"] == $r["act_src_id"]): ?>
                    <div>
                        <div>Transaction Number:</div>
                        <div><?php safer_echo($r["id"]); ?></div>
                    </div>
                    <div>
                        <div>Balance:</div>
                        <div><?php safer_echo($r["expected_total"]); ?></div>
                    </div>
                    <div>
                        <div>Account Type:</div>
                        <div><?php safer_echo($r2["account_type"]); ?></div>
                    </div>
                    <div>
                        <div>Account Number:</div>
                            <div><?php safer_echo($r2["account_number"]); ?></div>
                    </div>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <?php echo "<br>"; ?>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No results</p>
    <?php endif; ?>
</div>
<form method="POST">
  <h3>Reset Filter</h3>
        <input type="submit" name="reset" value="Reset"/>
</form>
  <nav aria-label="Trans History">
    <ul class="pagination justify-content-center">
      <li class="page-item <?php echo ($page-1) < 1?"disabled":"";?>">
        <a class="page-link" href="?id=<?php echo $id;?>&page=<?php echo $page-1;?>" tabindex="-1">Previous</a>
      </li>
      <?php for($i = 0; $i < $total_pages; $i++):?>
      <li class="page-item <?php echo ($page-1) == $i?"active":"";?>"><a class="page-link" href="?id=<?php echo $id;?>&page=<?php echo ($i+1);?>"><?php echo ($i+1);?></a></li>
      <?php endfor; ?>
      <li class="page-item <?php echo ($page+1) > $total_pages?"disabled":"";?>">
        <a class="page-link" href="?id=<?php echo $id;?>&page=<?php echo $page+1;?>">Next</a>
      </li>
    </ul>
  </nav>
</div>