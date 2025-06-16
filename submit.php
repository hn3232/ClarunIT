<?php
// submit.php

// Always return JSON
header('Content-Type: application/json');

// Path to our CSV file
$file = __DIR__ . '/names.csv';

// Read incoming JSON payload
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
    exit;
}

// Ensure we have the expected fields
$fields = ['firstName', 'lastName', 'email', 'phone', 'submittedAt'];

// Add timestamp
$input['submittedAt'] = date('c');

// Open CSV for append (create and write header if new)
$needHeader = !file_exists($file);
if (!$fp = fopen($file, 'a')) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Cannot open CSV file']);
    exit;
}

if ($needHeader) {
    // Write CSV header
    fputcsv($fp, $fields);
}

// Build row in correct order
$row = [];
foreach ($fields as $f) {
    $row[] = isset($input[$f]) ? $input[$f] : '';
}

// Write the data row
if (fputcsv($fp, $row) === false) {
    fclose($fp);
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to write to CSV']);
} else {
    fclose($fp);
    echo json_encode(['status' => 'success']);
}
