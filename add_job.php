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

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(["error" => "No data received"]);
    exit;
}

$title = $conn->real_escape_string($data['title'] ?? '');
$company = $conn->real_escape_string($data['company'] ?? '');
$location = $conn->real_escape_string($data['location'] ?? '');
$salary_min = floatval($data['salary_min'] ?? 0) * 1000000;
$salary_max = floatval($data['salary_max'] ?? 0) * 1000000;
$type = $conn->real_escape_string($data['type'] ?? '');
$kategori = $conn->real_escape_string($data['categories'] ?? '');
$deskripsi = $conn->real_escape_string($data['description'] ?? '');
$url = $data['url'] ?? '';

if (empty($title) || empty($company) || empty($location) || empty($type) || empty($kategori) || empty($deskripsi) || empty($url)) {
    http_response_code(400);
    echo json_encode(["error" => "All fields are required"]);
    exit;
}
if (!filter_var($url, FILTER_VALIDATE_URL)) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid URL format"]);
    exit;
}
if ($salary_min < 0 || $salary_max < $salary_min) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid salary range"]);
    exit;
}

$stmt = $conn->prepare("INSERT INTO p (title, perusahaan, location, salary_min, salary_max, type, kategori, deskripsi, url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
if (!$stmt) {
    error_log("Prepare failed: " . $conn->error);
    http_response_code(500);
    echo json_encode(["error" => "Prepare failed: " . $conn->error]);
    exit;
}

$stmt->bind_param("sssddssss", $title, $company, $location, $salary_min, $salary_max, $type, $kategori, $deskripsi, $url);

if ($stmt->execute()) {
    $newId = $conn->insert_id;
    echo json_encode([
        "success" => true,
        "job" => [
            "id" => $newId,
            "title" => $title,
            "company" => $company,
            "location" => $location,
            "salaryRange" => [floatval($salary_min / 1000000), floatval($salary_max / 1000000)],
            "salaryText" => "Rp " . number_format($salary_min, 0, ',', ',') . " - Rp " . number_format($salary_max, 0, ',', ','),
            "type" => $type,
            "categories" => explode(", ", $kategori),
            "description" => $deskripsi,
            "applyUrl" => $url
        ]
    ]);
} else {
    error_log("Insert failed: " . $stmt->error);
    http_response_code(500);
    echo json_encode(["error" => "Insert failed: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>