<!DOCTYPE html>
<html>
<head>
    <title>HE2</title>
    <meta name="google-signin-scope" content="profile email">
    <meta name="google-signin-client_id" content="190180432457-3bbiv7l4k0uvovgb1pvvgoul7qrm3839.apps.googleusercontent.com">
    <script src="https://apis.google.com/js/platform.js" async defer></script>
</head>
<body>
<?php
session_start();

if (isset($_SESSION['user_id'])) {
    echo '<p>Welcome back, ' . $_SESSION['user_name'] . '! </p>';
    echo "<a href='/logout.php'>Log out</a>";
} else {
    include('google.example.html');
    include('login_form.html');
    include('register_form.html');
}
?>
</body>
</html>
