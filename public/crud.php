<?php
header('Content-Type: application/json');

// Konfigurasi database
$host = "localhost";
$user = "root";
$pass = "";
$db = "barang_db";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die(json_encode(["error" => $conn->connect_error]));
}

// Tangani permintaan POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    // Tambah barang baru
    if ($action === 'create') {
        $name = $_POST['name'];
        $price = $_POST['price'];
        $description = $_POST['description'];

        // Upload gambar
        $targetDir = "uploads/";
        $imageName = time() . "_" . basename($_FILES["image"]["name"]);
        $targetFile = $targetDir . $imageName;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
            $stmt = $conn->prepare("INSERT INTO barang (name, price, image, description) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sdss", $name, $price, $imageName, $description);
            $stmt->execute();
            echo json_encode(["message" => "Barang berhasil ditambahkan"]);
        } else {
            echo json_encode(["error" => "Gagal mengunggah gambar"]);
        }

    // Hapus barang
    } elseif ($action === 'delete') {
        $id = $_POST['id'];

        // Hapus data dan file gambar
        $stmt = $conn->prepare("SELECT image FROM barang WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row && file_exists("uploads/" . $row['image'])) {
            unlink("uploads/" . $row['image']);
        }

        $stmt = $conn->prepare("DELETE FROM barang WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        echo json_encode(["message" => "Barang berhasil dihapus"]);

    // Edit barang
    } elseif ($action === 'edit') {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $price = $_POST['price'];
        $description = $_POST['description'];

        // Periksa apakah ada gambar baru yang diunggah
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $stmt = $conn->prepare("SELECT image FROM barang WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            if ($row && file_exists("uploads/" . $row['image'])) {
                unlink("uploads/" . $row['image']);
            }

            $targetDir = "uploads/";
            $imageName = time() . "_" . basename($_FILES["image"]["name"]);
            $targetFile = $targetDir . $imageName;

            if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
                $stmt = $conn->prepare("UPDATE barang SET name = ?, price = ?, image = ?, description = ? WHERE id = ?");
                $stmt->bind_param("sdssi", $name, $price, $imageName, $description, $id);
            } else {
                echo json_encode(["error" => "Gagal mengunggah gambar baru"]);
                exit;
            }
        } else {
            $stmt = $conn->prepare("UPDATE barang SET name = ?, price = ?, description = ? WHERE id = ?");
            $stmt->bind_param("sdsi", $name, $price, $description, $id);
        }

        $stmt->execute();
        echo json_encode(["message" => "Barang berhasil diperbarui"]);
    }
    
// Tangani permintaan GET
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $result = $conn->query("SELECT * FROM barang");
    $data = [];

    while ($row = $result->fetch_assoc()) {
        $row['image'] = 'uploads/' . $row['image']; // Tambahkan path ke file gambar
        $row['price'] = 'Rp ' . number_format($row['price'], 0, ',', '.'); // Format harga dengan Rp
        $data[] = $row;
    }

    echo json_encode($data);
}

$conn->close();
?>
