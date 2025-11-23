<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/Mission.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$mission_id = $data['mission_id'] ?? null;

if (!$mission_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid mission']);
    exit;
}

$db = getDB();
$mission = new Mission($db);
$result = $mission->completeMission($_SESSION['user_id'], $mission_id);

echo json_encode($result);
?>
