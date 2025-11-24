<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>BookingPro</title>
    <meta name="description" content="Book local professionals - barbers, tailors, mechanics, makeup artists, and more">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <!-- Stylesheet -->
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="icon" type="image/png" href="<?php echo SITE_URL; ?>/assets/images/logo.png">
     <!-- JavaScript -->
    <script src="/assets/main.js"></script>
</head>
<body>