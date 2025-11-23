<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/SpinWheel.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$db = getDB();
$spin = new SpinWheel($db);
$result = $spin->processSpin($_SESSION['user_id']);

echo json_encode($result);
?>
