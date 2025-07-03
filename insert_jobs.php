<?php
// Konfigurasi koneksi database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "loker";

// Data lowongan pekerjaan
$jobs = [
    [
        "id" => 1,
        "title" => "Senior Frontend Developer",
        "perusahaan" => "Tech Innovations Ltd.",
        "location" => "Balongpanggang",
        "salaryRange" => [12, 18],
        "type" => "Full-time",
        "categories" => ["IT/Computer - Software", "Web Development"],
        "description" => "Lead development of scalable and robust web applications using React, TypeScript, and other modern tools. Collaborate with design and backend teams to ensure smooth integration and great UX.\n\nRequirements:\n- 5+ years in frontend development\n- Strong React and TypeScript skills\n- Experience with Redux, GraphQL, and REST APIs",
        "applyUrl" => "https://example.com/apply/1",
    ],
    [
        "id" => 2,
        "title" => "Remote Software Tester",
        "perusahaan" => "Global Tech",
        "location" => "Duduk Sampeyan",
        "salaryRange" => [10, 14],
        "type" => "Remote",
        "categories" => ["IT/Computer - Software", "Quality Assurance"],
        "description" => "Perform manual and automated testing on software products to ensure quality and eliminate bugs before release.\n\nExperience with Selenium or similar tools preferred.",
        "applyUrl" => "https://example.com/apply/2",
    ],
];

// Membuat koneksi ke database
$conn = new mysqli($servername, $username, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Kosongkan tabel sebelum memasukkan data baru
$conn->query("TRUNCATE TABLE p");

// Siapkan query untuk insert
$stmt = $conn->prepare("INSERT INTO p (title, perusahaan, location, salary_min, salary_max, type, kategori, deskripsi, url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

foreach ($jobs as $job) {
    // Validasi data
    if (empty($job['title']) || empty($job['perusahaan']) || empty($job['location']) || empty($job['type']) || empty($job['applyUrl'])) {
        echo "Invalid data for job ID {$job['id']}: Missing required fields<br>";
        continue;
    }
    if (!filter_var($job['applyUrl'], FILTER_VALIDATE_URL)) {
        echo "Invalid URL for job ID {$job['id']}<br>";
        continue;
    }
    if ($job['salaryRange'][0] < 0 || $job['salaryRange'][1] < $job['salaryRange'][0]) {
        echo "Invalid salary range for job ID {$job['id']}<br>";
        continue;
    }

    $kategori = implode(", ", $job['categories']);
    $salary_min = $job['salaryRange'][0] * 1000000;
    $salary_max = $job['salaryRange'][1] * 1000000;
    $stmt->bind_param(
        "sssddssss",
        $job['title'],
        $job['perusahaan'],
        $job['location'],
        $salary_min,
        $salary_max,
        $job['type'],
        $kategori,
        $job['description'],
        $job['applyUrl']
    );
    if (!$stmt->execute()) {
        echo "Error inserting job ID {$job['id']}: " . $stmt->error . "<br>";
    }
}

echo "Data berhasil dimasukkan ke tabel p!";
$stmt->close();
$conn->close();
?>