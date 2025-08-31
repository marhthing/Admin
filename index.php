
<?php
require_once 'auth.php';

// Require authentication
requireAuth();

// Get session info
$sessionInfo = getSessionInfo();
?>
<?php include 'index.html'; ?>
