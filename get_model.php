<?php
include 'db.php';
if (isset($_GET['id'])) {
    $model_id = (int)$_GET['id'];
    $sql = "SELECT model_id, name, quantity FROM models WHERE model_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $model_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $model = $result->fetch_assoc();
    echo json_encode($model);
    $stmt->close();
}
?>