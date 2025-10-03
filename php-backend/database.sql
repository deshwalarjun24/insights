-- Create database
CREATE DATABASE IF NOT EXISTS insights_blog CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE insights_blog;

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    icon VARCHAR(50) DEFAULT 'fas fa-folder',
    color VARCHAR(7) DEFAULT '#6B73FF',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Posts table
CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    content LONGTEXT NOT NULL,
    excerpt TEXT,
    featured_image VARCHAR(255),
    category_id INT NOT NULL,
    author VARCHAR(100) DEFAULT 'Admin',
    tags TEXT,
    status ENUM('draft', 'published') DEFAULT 'published',
    views_count INT DEFAULT 0,
    read_time INT DEFAULT 5,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    INDEX idx_slug (slug),
    INDEX idx_category (category_id),
    INDEX idx_status (status),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default categories
INSERT INTO categories (name, slug, description, icon, color) VALUES
('Technology', 'technology', 'Latest in tech, programming, and digital innovation', 'fas fa-laptop-code', '#6B73FF'),
('Business', 'business', 'Entrepreneurship, startups, and market trends', 'fas fa-briefcase', '#FF6B6B'),
('Science', 'science', 'Discoveries, research, and scientific breakthroughs', 'fas fa-flask', '#4ECDC4'),
('Travel', 'travel', 'Explore destinations and travel experiences', 'fas fa-plane', '#45B7D1'),
('Food', 'food', 'Recipes, restaurants, and culinary adventures', 'fas fa-utensils', '#FFA07A'),
('Lifestyle', 'lifestyle', 'Health, wellness, and daily living', 'fas fa-heart', '#98D8C8'),
('Art', 'art', 'Visual arts, design, and creativity', 'fas fa-palette', '#F7B731'),
('Literature', 'literature', 'Books, writing, and literary discussions', 'fas fa-book', '#5F27CD'),
('Nature', 'nature', 'Environment, wildlife, and natural wonders', 'fas fa-tree', '#26DE81')
ON DUPLICATE KEY UPDATE name=name;
