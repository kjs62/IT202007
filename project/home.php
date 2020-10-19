<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<div class="drift">
<?php
//we use this to safely get the email to display
$email = "";
if (isset($_SESSION["user"]) && isset($_SESSION["user"]["email"])) {
    $email = $_SESSION["user"]["email"];
}
?>
    <p>Welcome, <?php echo $email; ?></p>
</div>
<?php require(__DIR__ . "/partials/flash.php");