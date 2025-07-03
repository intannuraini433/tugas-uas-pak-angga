<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "loker";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed. Please try again later."]);
    exit;
}

$result = $conn->query("SELECT * FROM p");
if (!$result) {
    error_log("Query failed: " . $conn->error);
    http_response_code(500);
    echo json_encode(["error" => "Query failed: " . $conn->error]);
    exit;
}

$jobs = [];
while ($row = $result->fetch_assoc()) {
    $categories = !empty($row['kategori']) ? explode(", ", $row['kategori']) : [];
    $jobs[] = [
        "id" => (int)$row['ID'],
        "title" => $row['title'] ?? '',
        "company" => $row['perusahaan'] ?? '',
        "location" => $row['location'] ?? '',
        "salaryRange" => [floatval($row['salary_min'] / 1000000), floatval($row['salary_max'] / 1000000)],
        "salaryText" => "Rp " . number_format($row['salary_min'], 0, ',', ',') . " - Rp " . number_format($row['salary_max'], 0, ',', ','),
        "type" => $row['type'] ?? '',
        "categories" => $categories,
        "description" => $row['deskripsi'] ?? '',
        "applyUrl" => $row['url'] ?? ''
    ];
}

echo json_encode($jobs);
$conn->close();
?>