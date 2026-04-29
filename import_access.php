#!/usr/bin/env php
<?php
// ============================================
// import_access.php - One-time migration script
// Run from CLI: php import_access.php
// ============================================

require_once __DIR__ . '/includes/config.php';

echo "=== Label System - Import from Access ===\n\n";

$csvFile = __DIR__ . '/label_billing.csv';

if (!file_exists($csvFile)) {
    echo "❌ ไม่พบไฟล์ label_billing.csv\n";
    echo "   ให้ export จาก Access ด้วยคำสั่ง:\n";
    echo "   mdb-export LABEL_2010.mdb 'LABEL - BILLING' > label_billing.csv\n\n";
    exit(1);
}

$db = getDB();

// Clear existing data
$db->exec("TRUNCATE TABLE label_billing");
echo "✓ ล้างข้อมูลเดิมแล้ว\n";

// Read CSV
$handle = fopen($csvFile, 'r');
$header = fgetcsv($handle);  // Skip header row

$stmt = $db->prepare("
    INSERT INTO label_billing (id, contact, position, company, address, is_selected, ems, billing_note, field3, f10)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$count   = 0;
$skipped = 0;

while (($row = fgetcsv($handle)) !== false) {
    if (count($row) < 5) { $skipped++; continue; }

    $id           = (int)($row[0] ?? 0);
    $contact      = trim($row[1] ?? '');
    $position     = trim($row[2] ?? '');
    $company      = trim($row[3] ?? '');
    $address      = trim($row[4] ?? '');
    $is_selected  = (int)($row[5] ?? 0);
    $ems          = trim($row[6] ?? '');
    $billing_note = trim($row[7] ?? '');
    $field3       = trim($row[8] ?? '');
    $f10          = trim($row[9] ?? '');

    // Skip completely empty rows
    if (empty($company) && empty($contact) && empty($address)) {
        $skipped++;
        continue;
    }

    try {
        $stmt->execute([$id, $contact, $position, $company, $address, $is_selected, $ems, $billing_note, $field3, $f10]);
        $count++;
    } catch (PDOException $e) {
        echo "⚠️  Skip row id=$id: " . $e->getMessage() . "\n";
        $skipped++;
    }
}

fclose($handle);

echo "✓ นำเข้าสำเร็จ: $count รายการ\n";
echo "✓ ข้ามแถวว่าง: $skipped รายการ\n\n";
echo "🎉 พร้อมใช้งานแล้ว! เปิด http://your-server/label_system/\n";
