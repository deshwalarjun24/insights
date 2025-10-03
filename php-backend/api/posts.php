<?php
require_once '../config.php';

$conn = getDBConnection();
$method = $_SERVER['REQUEST_METHOD'];

// Get action from query parameter
$action = isset($_GET['action']) ? $_GET['action'] : '';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$slug = isset($_GET['slug']) ? $_GET['slug'] : '';

switch ($method) {
    case 'GET':
        if ($slug) {
            getPostBySlug($conn, $slug);
        } elseif ($id) {
            getPostById($conn, $id);
        } else {
            getAllPosts($conn);
        }
        break;
    case 'POST':
        createPost($conn);
        break;
    case 'PUT':
    case 'PATCH':
        updatePost($conn, $id);
        break;
    case 'DELETE':
        deletePost($conn, $id);
        break;
    default:
        sendError('Method not allowed', 405);
}

function getAllPosts($conn) {
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = isset($_GET['limit']) ? max(1, min(100, intval($_GET['limit']))) : 10;
    $offset = ($page - 1) * $limit;
    $category = isset($_GET['category']) ? intval($_GET['category']) : 0;
    $status = isset($_GET['status']) ? $_GET['status'] : 'published';
    
    $where = "WHERE p.status = '" . $conn->real_escape_string($status) . "'";
    if ($category > 0) {
        $where .= " AND p.category_id = $category";
    }
    
    $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug, c.color as category_color
            FROM posts p
            LEFT JOIN categories c ON p.category_id = c.id
            $where
            ORDER BY p.created_at DESC
            LIMIT $limit OFFSET $offset";
    
    $result = $conn->query($sql);
    
    $posts = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $row['tags'] = $row['tags'] ? explode(',', $row['tags']) : [];
            $posts[] = $row;
        }
    }
    
    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM posts p $where";
    $countResult = $conn->query($countSql);
    $total = $countResult->fetch_assoc()['total'];
    
    sendResponse([
        'success' => true,
        'posts' => $posts,
        'pagination' => [
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'totalPages' => ceil($total / $limit)
        ]
    ]);
}

function getPostBySlug($conn, $slug) {
    $slug = $conn->real_escape_string($slug);
    
    $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug, c.color as category_color
            FROM posts p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.slug = '$slug'";
    
    $result = $conn->query($sql);
    
    if ($result->num_rows === 0) {
        sendError('Post not found', 404);
    }
    
    $post = $result->fetch_assoc();
    $post['tags'] = $post['tags'] ? explode(',', $post['tags']) : [];
    
    // Increment view count
    $updateSql = "UPDATE posts SET views_count = views_count + 1 WHERE id = " . $post['id'];
    $conn->query($updateSql);
    
    sendResponse([
        'success' => true,
        'post' => $post
    ]);
}

function getPostById($conn, $id) {
    $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug, c.color as category_color
            FROM posts p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.id = $id";
    
    $result = $conn->query($sql);
    
    if ($result->num_rows === 0) {
        sendError('Post not found', 404);
    }
    
    $post = $result->fetch_assoc();
    $post['tags'] = $post['tags'] ? explode(',', $post['tags']) : [];
    
    sendResponse([
        'success' => true,
        'post' => $post
    ]);
}

function createPost($conn) {
    // Get form data
    $title = isset($_POST['title']) ? $conn->real_escape_string($_POST['title']) : '';
    $content = isset($_POST['content']) ? $conn->real_escape_string($_POST['content']) : '';
    $category_id = isset($_POST['category']) ? intval($_POST['category']) : 0;
    $author = isset($_POST['author']) ? $conn->real_escape_string($_POST['author']) : 'Admin';
    $excerpt = isset($_POST['excerpt']) ? $conn->real_escape_string($_POST['excerpt']) : '';
    $tags = isset($_POST['tags']) ? $conn->real_escape_string($_POST['tags']) : '';
    $status = isset($_POST['status']) ? $conn->real_escape_string($_POST['status']) : 'published';
    
    // Validate required fields
    if (empty($title) || empty($content) || $category_id === 0) {
        sendError('Title, content, and category are required');
    }
    
    // Generate slug
    $slug = generateSlug($title);
    
    // Check if slug exists
    $checkSql = "SELECT id FROM posts WHERE slug = '$slug'";
    $checkResult = $conn->query($checkSql);
    if ($checkResult->num_rows > 0) {
        $slug .= '-' . time();
    }
    
    // Generate excerpt if not provided
    if (empty($excerpt)) {
        $excerpt = generateExcerpt($content);
    }
    
    // Calculate reading time
    $read_time = calculateReadingTime($content);
    
    // Handle file upload
    $featured_image = '';
    if (isset($_FILES['featuredImage']) && $_FILES['featuredImage']['error'] === UPLOAD_ERR_OK) {
        $featured_image = handleFileUpload($_FILES['featuredImage']);
    }
    
    // Insert post
    $sql = "INSERT INTO posts (title, slug, content, excerpt, featured_image, category_id, author, tags, status, read_time)
            VALUES ('$title', '$slug', '$content', '$excerpt', '$featured_image', $category_id, '$author', '$tags', '$status', $read_time)";
    
    if ($conn->query($sql)) {
        $post_id = $conn->insert_id();
        
        // Get the created post
        $getPostSql = "SELECT p.*, c.name as category_name FROM posts p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = $post_id";
        $result = $conn->query($getPostSql);
        $post = $result->fetch_assoc();
        
        sendResponse([
            'success' => true,
            'message' => 'Post created successfully',
            'post' => $post
        ], 201);
    } else {
        sendError('Failed to create post: ' . $conn->error, 500);
    }
}

function updatePost($conn, $id) {
    if ($id === 0) {
        sendError('Post ID is required');
    }
    
    // Check if post exists
    $checkSql = "SELECT * FROM posts WHERE id = $id";
    $checkResult = $conn->query($checkSql);
    if ($checkResult->num_rows === 0) {
        sendError('Post not found', 404);
    }
    
    $existingPost = $checkResult->fetch_assoc();
    
    // Get form data
    $title = isset($_POST['title']) ? $conn->real_escape_string($_POST['title']) : $existingPost['title'];
    $content = isset($_POST['content']) ? $conn->real_escape_string($_POST['content']) : $existingPost['content'];
    $category_id = isset($_POST['category']) ? intval($_POST['category']) : $existingPost['category_id'];
    $author = isset($_POST['author']) ? $conn->real_escape_string($_POST['author']) : $existingPost['author'];
    $excerpt = isset($_POST['excerpt']) ? $conn->real_escape_string($_POST['excerpt']) : $existingPost['excerpt'];
    $tags = isset($_POST['tags']) ? $conn->real_escape_string($_POST['tags']) : $existingPost['tags'];
    $status = isset($_POST['status']) ? $conn->real_escape_string($_POST['status']) : $existingPost['status'];
    
    // Generate new slug if title changed
    $slug = $existingPost['slug'];
    if ($title !== $existingPost['title']) {
        $slug = generateSlug($title);
    }
    
    // Calculate reading time
    $read_time = calculateReadingTime($content);
    
    // Handle file upload
    $featured_image = $existingPost['featured_image'];
    if (isset($_FILES['featuredImage']) && $_FILES['featuredImage']['error'] === UPLOAD_ERR_OK) {
        // Delete old image
        if (!empty($existingPost['featured_image']) && file_exists(UPLOAD_DIR . $existingPost['featured_image'])) {
            unlink(UPLOAD_DIR . $existingPost['featured_image']);
        }
        $featured_image = handleFileUpload($_FILES['featuredImage']);
    }
    
    // Update post
    $sql = "UPDATE posts SET 
            title = '$title',
            slug = '$slug',
            content = '$content',
            excerpt = '$excerpt',
            featured_image = '$featured_image',
            category_id = $category_id,
            author = '$author',
            tags = '$tags',
            status = '$status',
            read_time = $read_time
            WHERE id = $id";
    
    if ($conn->query($sql)) {
        sendResponse([
            'success' => true,
            'message' => 'Post updated successfully'
        ]);
    } else {
        sendError('Failed to update post: ' . $conn->error, 500);
    }
}

function deletePost($conn, $id) {
    if ($id === 0) {
        sendError('Post ID is required');
    }
    
    // Get post to delete image
    $getSql = "SELECT featured_image FROM posts WHERE id = $id";
    $result = $conn->query($getSql);
    
    if ($result->num_rows === 0) {
        sendError('Post not found', 404);
    }
    
    $post = $result->fetch_assoc();
    
    // Delete post
    $sql = "DELETE FROM posts WHERE id = $id";
    
    if ($conn->query($sql)) {
        // Delete image file
        if (!empty($post['featured_image']) && file_exists(UPLOAD_DIR . $post['featured_image'])) {
            unlink(UPLOAD_DIR . $post['featured_image']);
        }
        
        sendResponse([
            'success' => true,
            'message' => 'Post deleted successfully'
        ]);
    } else {
        sendError('Failed to delete post: ' . $conn->error, 500);
    }
}

function handleFileUpload($file) {
    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        sendError('File size exceeds maximum allowed size (5MB)');
    }
    
    // Check file extension
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, ALLOWED_EXTENSIONS)) {
        sendError('Invalid file type. Allowed: ' . implode(', ', ALLOWED_EXTENSIONS));
    }
    
    // Generate unique filename
    $filename = 'post-' . time() . '-' . uniqid() . '.' . $extension;
    $uploadPath = UPLOAD_DIR . $filename;
    
    // Create upload directory if it doesn't exist
    if (!file_exists(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0777, true);
    }
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return $filename;
    } else {
        sendError('Failed to upload file');
    }
}

$conn->close();
?>
