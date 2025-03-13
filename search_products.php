<?php
include 'db.php';
if (isset($_GET['q'])) {
    $query = "%" . $_GET['q'] . "%";
    $sql = "SELECT * FROM Products WHERE name LIKE ? OR product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $query, $_GET['q']);
    $stmt->execute();
    $result = $stmt->get_result();
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    echo json_encode($products);
}
?>