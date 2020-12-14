<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<div class="drift">
<?php
//we use this to safely get the email to display
$email = "";
if (isset($_SESSION["user"]) && isset($_SESSION["user"]["email"])) {
    $email = $_SESSION["user"]["email"];
}
$query = "";
$db = getDB();
$user = get_user_id();
$stmt = $db->prepare("SELECT nextAPY, id from Accounts WHERE id like :q");
    $r = $stmt->execute([":q" => "%$query%"]);
    if ($r) {
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    foreach($results as $result)
    {
      $isValid = false;
      if(date("Y-m-d H:i:s") > $result['nextAPY'])
      {
        $isValid = true;
        $currId = $result['id'];
      }
      if($isValid)
      {
        echo "$currId";
        $stmt = $db->prepare("UPDATE Accounts SET balance = balance + (balance*(APY/12)), nextAPY = timestampadd(month, 1, nextAPY) where id = :q");
        $r = $stmt->execute([":q" => $currId]);
        if ($r) {
            flash("Transfer successfully made");
        }
        else {
            $e = $stmt->errorInfo();
            flash("Error creating: " . var_export($e, true));
        }
      }
    }
?>
    <p>Welcome, <?php echo $email; ?></p>
</div>
<?php require(__DIR__ . "/partials/flash.php");