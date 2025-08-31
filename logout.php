
<?php
require_once 'auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    logout();
    echo json_encode(['success' => true]);
} else {
    logout();
    header('Location: login.php');
}
?>
