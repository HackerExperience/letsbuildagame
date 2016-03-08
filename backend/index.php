<!DOCTYPE html>
<html>
<head>
    <title>HE2</title>
</head>
<body>
<?php
session_start();

if (isset($_SESSION['user_id'])) {
    echo '<p>Welcome back!</p>';
    echo "<a href='/logout.php'>Log out</a>";
} else {
    include('login_form.html');
}
?>
</body>
</html>