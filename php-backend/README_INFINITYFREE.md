# Insights Blog - InfinityFree Deployment Guide

Complete guide to deploy your Insights blog on InfinityFree hosting with full Create/Delete post functionality.

## 📋 What You'll Get

- ✅ Free hosting with InfinityFree
- ✅ MySQL database for posts and categories
- ✅ Create new blog posts with images
- ✅ Delete posts
- ✅ View all posts
- ✅ Category management
- ✅ Image upload support

## 🚀 Step-by-Step Deployment

### Step 1: Sign Up for InfinityFree

1. Go to [infinityfree.net](https://infinityfree.net)
2. Click "Sign Up" and create an account
3. Verify your email address
4. Create a new hosting account
5. Choose a subdomain (e.g., `myinsights.infinityfreeapp.com`) or use your own domain

### Step 2: Set Up MySQL Database

1. **Login to InfinityFree Control Panel**
2. **Go to MySQL Databases**
3. **Create a new database:**
   - Database Name: `insights_blog` (or any name you prefer)
   - Click "Create Database"
4. **Note down these details:**
   ```
   Database Host: sqlXXX.infinityfree.com
   Database Name: epizXXXX_insights
   Database User: epizXXXX_user
   Database Password: [your password]
   ```

### Step 3: Import Database Structure

1. **Go to phpMyAdmin** in your InfinityFree control panel
2. **Select your database**
3. **Click on "SQL" tab**
4. **Copy and paste the contents of `database.sql`** file
5. **Click "Go" to execute**
6. **Verify:** You should see `categories` and `posts` tables created

### Step 4: Configure Backend

1. **Open `config.php`** file
2. **Update database credentials:**

```php
define('DB_HOST', 'sqlXXX.infinityfree.com');  // Your database host
define('DB_USER', 'epizXXXX_user');            // Your database user
define('DB_PASS', 'your_password');             // Your database password
define('DB_NAME', 'epizXXXX_insights');         // Your database name
```

### Step 5: Upload Files to InfinityFree

#### Using File Manager (Recommended for beginners):

1. **Login to InfinityFree Control Panel**
2. **Go to "File Manager"**
3. **Navigate to `htdocs` folder**
4. **Upload your files:**
   ```
   htdocs/
   ├── index.html
   ├── categories.html
   ├── create-post.html
   ├── about.html
   ├── css/
   ├── js/
   ├── images/
   ├── php-backend/
   │   ├── config.php
   │   ├── api/
   │   │   ├── posts.php
   │   │   └── categories.php
   └── uploads/  (create this folder)
   ```

5. **Create `uploads` folder:**
   - Click "New Folder"
   - Name it `uploads`
   - Set permissions to 755

#### Using FTP (Alternative):

1. **Get FTP credentials** from InfinityFree control panel
2. **Use FileZilla or any FTP client:**
   - Host: `ftpupload.net`
   - Username: `epizXXXX`
   - Password: [your FTP password]
   - Port: 21
3. **Upload all files to `htdocs` folder**

### Step 6: Update Frontend API URL

1. **Open `create-post.html`**
2. **Find this line:**
   ```javascript
   const API_URL = 'https://yourdomain.infinityfreeapp.com/php-backend/api';
   ```
3. **Replace with your actual domain:**
   ```javascript
   const API_URL = 'https://myinsights.infinityfreeapp.com/php-backend/api';
   ```
4. **Save and re-upload the file**

### Step 7: Test Your Website

1. **Visit your website:** `https://yourdomain.infinityfreeapp.com`
2. **Go to "Create Post" page**
3. **Try creating a test post:**
   - Fill in title, select category, add content
   - Upload an image (optional)
   - Click "Publish Post"
4. **Check if post appears on homepage**

## 🔧 File Structure on Server

```
htdocs/
├── index.html                    # Homepage
├── categories.html               # Categories page
├── create-post.html              # Create post page
├── about.html                    # About page
├── css/
│   └── style.css                 # Styles
├── js/
│   └── script.js                 # JavaScript
├── images/                       # Static images
├── uploads/                      # Uploaded post images
│   └── (post images will be here)
└── php-backend/
    ├── config.php                # Database config
    ├── database.sql              # Database structure
    └── api/
        ├── posts.php             # Posts API
        └── categories.php        # Categories API
```

## 📝 API Endpoints

### Categories

- **Get all categories:**
  ```
  GET https://yourdomain.infinityfreeapp.com/php-backend/api/categories.php
  ```

### Posts

- **Get all posts:**
  ```
  GET https://yourdomain.infinityfreeapp.com/php-backend/api/posts.php
  ```

- **Get single post by slug:**
  ```
  GET https://yourdomain.infinityfreeapp.com/php-backend/api/posts.php?slug=post-slug
  ```

- **Create new post:**
  ```
  POST https://yourdomain.infinityfreeapp.com/php-backend/api/posts.php
  Content-Type: multipart/form-data
  
  Fields:
  - title (required)
  - content (required)
  - category (required)
  - author
  - excerpt
  - tags
  - featuredImage (file)
  ```

- **Delete post:**
  ```
  DELETE https://yourdomain.infinityfreeapp.com/php-backend/api/posts.php?id=1
  ```

## 🐛 Troubleshooting

### Database Connection Error

**Problem:** "Database connection failed"

**Solution:**
1. Check database credentials in `config.php`
2. Verify database exists in phpMyAdmin
3. Make sure database host is correct (usually `sqlXXX.infinityfree.com`)

### Image Upload Not Working

**Problem:** Images not uploading

**Solution:**
1. Check if `uploads` folder exists
2. Set folder permissions to 755 or 777
3. Verify file size is under 5MB
4. Check allowed file types (jpg, jpeg, png, gif, webp)

### Posts Not Showing

**Problem:** Posts created but not visible

**Solution:**
1. Check if database tables were created properly
2. Verify API URL in `create-post.html` is correct
3. Check browser console for errors (F12)
4. Make sure posts have status = 'published'

### CORS Error

**Problem:** "Access to fetch blocked by CORS policy"

**Solution:**
- Already handled in `config.php` with:
  ```php
  header('Access-Control-Allow-Origin: *');
  ```
- If still having issues, contact InfinityFree support

### 404 Error on API Calls

**Problem:** API endpoints returning 404

**Solution:**
1. Verify file paths are correct
2. Check if `.htaccess` file exists (InfinityFree usually handles this)
3. Make sure files are in correct folders

## 🔒 Security Tips

### For Production Use:

1. **Change database credentials** regularly
2. **Add authentication** for create/delete operations
3. **Validate all inputs** (already implemented)
4. **Limit file upload size** (already set to 5MB)
5. **Use prepared statements** (consider upgrading for better security)
6. **Add rate limiting** to prevent spam

### Recommended Improvements:

```php
// Add admin password check before creating/deleting posts
if ($_POST['admin_password'] !== 'your_secret_password') {
    sendError('Unauthorized', 401);
}
```

## 📊 Database Management

### View All Posts:
```sql
SELECT * FROM posts ORDER BY created_at DESC;
```

### Delete a Post:
```sql
DELETE FROM posts WHERE id = 1;
```

### Update Post Status:
```sql
UPDATE posts SET status = 'draft' WHERE id = 1;
```

### Get Post Count by Category:
```sql
SELECT c.name, COUNT(p.id) as post_count 
FROM categories c 
LEFT JOIN posts p ON c.id = p.category_id 
GROUP BY c.id;
```

## 🎯 Features Included

- ✅ **Create Posts** - Full WYSIWYG post creation
- ✅ **Delete Posts** - Remove posts from database
- ✅ **Image Upload** - Upload featured images
- ✅ **Categories** - Organize posts by category
- ✅ **Tags** - Add multiple tags to posts
- ✅ **Auto Slug** - Automatic URL-friendly slugs
- ✅ **Reading Time** - Auto-calculated reading time
- ✅ **View Counter** - Track post views
- ✅ **Excerpts** - Auto-generated or custom excerpts
- ✅ **Responsive** - Works on all devices

## 📞 Support

If you encounter any issues:

1. **Check InfinityFree Status:** [status.infinityfree.net](https://status.infinityfree.net)
2. **InfinityFree Forum:** [forum.infinityfree.net](https://forum.infinityfree.net)
3. **Check PHP Error Logs** in your control panel

## 🎉 You're Done!

Your blog is now live with full create/delete functionality! 

Visit your website and start creating amazing content! 🚀

---

**Note:** InfinityFree has some limitations:
- Max 50,000 hits per day
- Max 10GB bandwidth per month
- No Node.js support (that's why we use PHP)
- Some file types may be restricted

For unlimited resources, consider upgrading to paid hosting in the future.
