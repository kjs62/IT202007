<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<div class="drift">
<?php
$query = get_user_id();
$results = [];
    $db = getDB();
    $stmt = $db->prepare("SELECT * from Accounts WHERE active = 'active' AND user_id = :q LIMIT 5");
    $r = $stmt->execute([":q" => $query]);
    if ($r) {
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    else {
        flash("There was a problem fetching the results");
    }
?>
<h3>List Accounts</h3>
<div class="results">
    <?php if (count($results) > 0): ?>
        <div class="list-group">
            <?php foreach ($results as $r): ?>
              <?php if ($r["user_id"] == get_user_id()): ?>
                <div class="list-group-item">
                    <div>
                        <div>Account Number:</div>
                        <div><?php safer_echo($r["account_number"]); ?></div>
                    </div>
                    <div>
                        <div>Account Type:</div>
                        <div><?php safer_echo($r["account_type"]); ?></div>
                    </div>
                    <div>
                        <div>Balance:</div>
                        <div><?php safer_echo($r["balance"]); ?></div>
                    </div>
                    <?php if($r["APY"] != 0): ?>
                    <div>
                        <div>APY:</div>
                        <div><?php safer_echo(($r["APY"] * 100) . "%"); ?></div>
                    </div>
                    <div>
                        <div>Next APY Date:</div>
                        <div><?php safer_echo($r["nextAPY"]); ?></div>
                    </div>
                    <?php endif; ?>
                    <div>
                        <a type="button" href="transaction_history.php?id=<?php safer_echo($r['id']); ?>">View Transaction History</a>
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
