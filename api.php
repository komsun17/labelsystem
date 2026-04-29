<?php
// ============================================
// api.php - AJAX Handler
// ============================================
require_once 'includes/config.php';
header('Content-Type: application/json');

$db   = getDB();
$body = json_decode(file_get_contents('php://input'), true);

$action = $body['action'] ?? '';

switch ($action) {

    // Toggle single record selection
    case 'toggle':
        $id  = (int)($body['id']  ?? 0);
        $val = (int)($body['val'] ?? 0);
        $db->prepare("UPDATE label_billing SET is_selected=? WHERE id=?")
           ->execute([$val, $id]);
        echo json_encode(['ok' => true]);
        break;

    // Toggle multiple records
    case 'toggle_many':
        $ids = array_map('intval', $body['ids'] ?? []);
        $val = (int)($body['val'] ?? 0);
        if (!empty($ids)) {
            $ph = implode(',', array_fill(0, count($ids), '?'));
            $params = array_merge([$val], $ids);
            $db->prepare("UPDATE label_billing SET is_selected=? WHERE id IN ($ph)")
               ->execute($params);
        }
        echo json_encode(['ok' => true]);
        break;

    // Get count of selected
    case 'count':
        $count = $db->query("SELECT COUNT(*) FROM label_billing WHERE is_selected=1")->fetchColumn();
        echo json_encode(['count' => (int)$count]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Unknown action']);
}
