<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<div class="drift">
<?php
if (isset($_GET["id"])) {
    $id = $_GET["id"];
}
?>
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
  <nav aria-label="My Eggs">
    <ul class="pagination justify-content-center">
      <li class="page-item <?php echo ($page-1) < 1?"disabled":"";?>">
        <a class="page-link" href="?id=<?php echo $id;?>&page=<?php echo $page-1;?>" tabindex="-1">Previous</a>
      </li>
      <?php for($i = 0; $i < $total_pages; $i++):?>
      <li class="page-item <?php echo ($page-1) == $i?"active":"";?>"><a class="page-link" href="?id=<?php echo $id;?>&page=<?php echo ($i+1);?>"><?php echo ($i+1);?></a></li>
      <?php endfor; ?>
      <li class="page-item <?php echo ($page+1) >= $total_pages?"disabled":"";?>">
        <a class="page-link" href="?id=<?php echo $id;?>&page=<?php echo $page+1;?>">Next</a>
      </li>
    </ul>
  </nav>
</div>