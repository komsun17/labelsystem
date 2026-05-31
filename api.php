<?php
// ============================================
// api.php - AJAX Handler
// ============================================
require_once 'includes/config.php';
header('Content-Type: application/json');
auth_check_api();

$db   = getDB();
$body = json_decode(file_get_contents('php://input'), true);

$action = $body['action'] ?? '';

switch ($action) {

    // Toggle single record selection
    case 'toggle':
        $token = (string)($body['csrf'] ?? '');
        if (!csrf_verify_token($token)) { http_response_code(403); echo json_encode(['error' => 'Forbidden']); break; }
        $id  = (int)($body['id']  ?? 0);
        $val = (int)($body['val'] ?? 0);
        $db->prepare("UPDATE label_billing SET is_selected=? WHERE id=?")
           ->execute([$val, $id]);
        echo json_encode(['ok' => true]);
        break;

    // Toggle multiple records
    case 'toggle_many':
        $token = (string)($body['csrf'] ?? '');
        if (!csrf_verify_token($token)) { http_response_code(403); echo json_encode(['error' => 'Forbidden']); break; }
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

    // Add a new record
    case 'add':
        $token = (string)($body['csrf'] ?? '');
        if (!csrf_verify_token($token)) { http_response_code(403); echo json_encode(['error' => 'Forbidden']); break; }
        $company      = trim($body['company']      ?? '');
        $contact      = trim($body['contact']      ?? '');
        $position     = trim($body['position']     ?? '');
        $address      = trim($body['address']      ?? '');
        $ems          = trim($body['ems']          ?? '');
        $billing_note = trim($body['billing_note'] ?? '');
        if (empty($company) && empty($address)) {
            http_response_code(422);
            echo json_encode(['error' => 'กรุณากรอกชื่อบริษัท หรือที่อยู่อย่างน้อยหนึ่งอย่าง']);
            break;
        }
        $db->prepare("INSERT INTO label_billing (company,contact,position,address,ems,billing_note) VALUES(?,?,?,?,?,?)")
           ->execute([$company, $contact, $position, $address, $ems, $billing_note]);
        echo json_encode(['ok' => true, 'id' => (int)$db->lastInsertId()]);
        break;

    // Delete a record (AJAX with CSRF)
    case 'delete':
        $id    = (int)($body['id']   ?? 0);
        $token = (string)($body['csrf'] ?? '');
        if ($id <= 0 || !csrf_verify_token($token)) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            break;
        }
        $db->prepare("DELETE FROM label_billing WHERE id=?")->execute([$id]);
        echo json_encode(['ok' => true]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Unknown action']);
}
