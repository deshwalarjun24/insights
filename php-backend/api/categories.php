<?php
require_once '../config.php';

$conn = getDBConnection();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        getCategories($conn);
        break;
    case 'POST':
        createCategory($conn);
        break;
    default:
        sendError('Method not allowed', 405);
}

function getCategories($conn) {
    $sql = "SELECT * FROM categories ORDER BY name ASC";
    $result = $conn->query($sql);
    
    $categories = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
    }
    
    sendResponse([
        'success' => true,
        'categories' => $categories
    ]);
}

function createCategory($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (empty($data['name'])) {
        sendError('Category name is required');
    }
    
    $name = $conn->real_escape_string($data['name']);
    $slug = generateSlug($name);
    $description = isset($data['description']) ? $conn->real_escape_string($data['description']) : '';
    $icon = isset($data['icon']) ? $conn->real_escape_string($data['icon']) : 'fas fa-folder';
    $color = isset($data['color']) ? $conn->real_escape_string($data['color']) : '#6B73FF';
    
    // Check if category exists
    $checkSql = "SELECT id FROM categories WHERE name = '$name' OR slug = '$slug'";
    $checkResult = $conn->query($checkSql);
    
    if ($checkResult->num_rows > 0) {
        sendError('Category already exists');
    }
    
    $sql = "INSERT INTO categories (name, slug, description, icon, color) 
            VALUES ('$name', '$slug', '$description', '$icon', '$color')";
    
    if ($conn->query($sql)) {
        sendResponse([
            'success' => true,
            'message' => 'Category created successfully',
            'category_id' => $conn->insert_id
        ], 201);
    } else {
        sendError('Failed to create category: ' . $conn->error, 500);
    }
}

$conn->close();
?>
