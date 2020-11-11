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
if (isset($_GET["id"])) {
    $id = $_GET["id"];
}
?>
<?php
//fetching
$result = [];
if (isset($id)) {
    $db = getDB();
    $stmt = $db->prepare("select * from Transactions as T join Accounts as A on T.act_src_id = A.id where T.id = :user_id");
    $r = $stmt->execute([":user_id" => $id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$result) {
        $e = $stmt->errorInfo();
        flash($e[2]);
    }
}
?>
    <h3>View Transaction</h3>
<?php if (isset($result) && !empty($result)): ?>
    <div class="card">
        <div class="card-title">
            <?php safer_echo($result["id"]); ?>
        </div>
        <div class="card-body">
            <div>
                <p>Data</p>
                <div>Amount Change: <?php safer_echo($result["amount"]); ?></div>
                <div>Action Type: <?php safer_echo($result["action_type"]); ?></div>
                <div>Account 1: <?php safer_echo($result["act_src_id"]); ?></div>
                <div>Account 2: <?php safer_echo($result["act_dest_id"]); ?></div>
                <div>Memo: <?php safer_echo($result["memo"]); ?></div>
                <div>Expected Total: <?php safer_echo($result["expected_total"]); ?></div>
                <div>Owned by: <?php safer_echo($result["id"]); ?></div>
            </div>
        </div>
    </div>
<?php else: ?>
    <p>Error looking up id...</p>
<?php endif; ?>
</div>
<?php require(__DIR__ . "/partials/flash.php");