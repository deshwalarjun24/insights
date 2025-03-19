// DOM Elements
document.addEventListener('DOMContentLoaded', function() {
    // Mobile navigation toggle
    const mobileBreakpoint = 768;
    
    // Newsletter Form Submission
    const newsletterForm = document.querySelector('.newsletter-form');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const emailInput = this.querySelector('input[type="email"]');
            const email = emailInput.value.trim();
            
            if (validateEmail(email)) {
                // In a real implementation, you would send this to your backend
                console.log('Subscribing email:', email);
                
                // Show success message
                emailInput.value = '';
                showNotification('Thanks for subscribing!', 'success');
            } else {
                showNotification('Please enter a valid email address', 'error');
            }
        });
    }

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]:not([href="#"])').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                window.scrollTo({
                    top: targetElement.offsetTop - 100,
                    behavior: 'smooth'
                });
            }
        });
    });

    // Initialize any sliders or carousels (for future implementation)
    initializeObservers();
});

// Blog Post Creation - Function to handle new post creation
function createNewPost() {
    const newPostModal = document.getElementById('new-post-modal');
    if (newPostModal) {
        newPostModal.style.display = 'block';
    }
}

// Function to handle blog post submission from the form
function submitNewPost(form) {
    if (!form) return;
    
    const title = form.querySelector('#post-title').value.trim();
    const content = form.querySelector('#post-content').value.trim();
    const category = form.querySelector('#post-category').value;
    const image = form.querySelector('#post-image').files[0];
    
    if (!title || !content) {
        showNotification('Please fill in all required fields', 'error');
        return false;
    }
    
    // Improved image handling
    let imageUrl = null;
    
    if (image) {
        // For small images, we'll use base64 encoding
        const reader = new FileReader();
        reader.onload = function(e) {
            try {
                // Store image as base64 string
                imageUrl = e.target.result;
                
                // Create new post with loaded image
                finalizePostCreation(title, content, category, imageUrl);
            } catch (error) {
                console.error('Error processing image:', error);
                // Fallback to default image
                finalizePostCreation(title, content, category, null);
            }
        };
        
        reader.onerror = function() {
            console.error('Error reading file');
            // Fallback to default image
            finalizePostCreation(title, content, category, null);
        };
        
        // Read file as data URL (base64)
        reader.readAsDataURL(image);
    } else {
        // No image uploaded, use default category image
        let defaultImage = null;
        
        // Set default images based on category
        switch(category) {
            case 'Technology':
                defaultImage = 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?q=80&w=2370&auto=format&fit=crop';
                break;
            case 'Nature':
                defaultImage = 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?q=80&w=2370&auto=format&fit=crop';
                break;
            case 'Travel':
                defaultImage = 'https://images.unsplash.com/photo-1682686580391-615889d6f345?q=80&w=2370&auto=format&fit=crop';
                break;
            case 'Food':
                defaultImage = 'https://images.unsplash.com/photo-1504754524776-8f4f37790ca0?q=80&w=2370&auto=format&fit=crop';
                break;
            case 'Art':
                defaultImage = 'https://images.unsplash.com/photo-1547891654-e66ed7ebb968?q=80&w=2370&auto=format&fit=crop';
                break;
            case 'Literature':
                defaultImage = 'https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?q=80&w=2370&auto=format&fit=crop';
                break;
            case 'Science':
                defaultImage = 'https://images.unsplash.com/photo-1507413245164-6160d8298b31?q=80&w=2370&auto=format&fit=crop';
                break;
            default:
                defaultImage = 'https://images.unsplash.com/photo-1499750310107-5fef28a66643?q=80&w=2370&auto=format&fit=crop';
        }
        
        finalizePostCreation(title, content, category, defaultImage);
    }
    
    // Prevent form submission
    return false;
}

// Helper function to complete post creation
function finalizePostCreation(title, content, category, imageUrl) {
    // Create post object
    const newPost = {
        title,
        content,
        category,
        image: imageUrl,
        date: new Date().toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        }),
        author: 'Admin' // In a real app, this would be the logged-in user
    };
    
    console.log('New post created:', newPost);
    
    // Save to localStorage
    savePostToLocalStorage(newPost);
    
    // Show success message
    showNotification('Post created successfully!', 'success');
    
    // Reset form if it exists
    const form = document.getElementById('create-post-form');
    if (form) {
        form.reset();
    }
    
    // Redirect to homepage after a short delay
    setTimeout(() => {
        window.location.href = 'index.html';
    }, 1500);
}

// Helper function to save post to localStorage
function savePostToLocalStorage(post) {
    let posts = JSON.parse(localStorage.getItem('blog_posts')) || [];
    posts.unshift(post); // Add new post to the beginning
    localStorage.setItem('blog_posts', JSON.stringify(posts));
}

// Helper function to load posts from localStorage
function loadPostsFromLocalStorage() {
    return JSON.parse(localStorage.getItem('blog_posts')) || [];
}

// Function to display posts on the page
function displayPosts() {
    const posts = loadPostsFromLocalStorage();
    const featuredPostsContainer = document.querySelector('.posts-grid');
    const recentPostsContainer = document.querySelector('.posts-list');
    
    if (!featuredPostsContainer || !recentPostsContainer) return;
    
    // Clear existing posts from the containers
    // We're keeping the initial demo posts in HTML, but in a real app
    // you'd probably want to clear everything here
    
    // Display featured posts (up to 3)
    const featuredPosts = posts.slice(0, 3);
    featuredPosts.forEach(post => {
        const postElement = createPostElement(post, 'featured');
        featuredPostsContainer.prepend(postElement);
    });
    
    // Display recent posts (up to 4)
    const recentPosts = posts.slice(3, 7);
    recentPosts.forEach(post => {
        const postElement = createPostElement(post, 'recent');
        recentPostsContainer.prepend(postElement);
    });
}

// Helper function to create post HTML elements
function createPostElement(post, type) {
    let postElement;
    
    // Ensure we have a valid image URL
    const imageUrl = post.image || getDefaultImageForCategory(post.category);
    
    if (type === 'featured') {
        postElement = document.createElement('article');
        postElement.className = 'post';
        postElement.innerHTML = `
            <div class="post-image">
                <img src="${imageUrl}" alt="${post.title}">
            </div>
            <div class="post-content">
                <span class="post-category">${post.category}</span>
                <h3><a href="blog-post.html">${post.title}</a></h3>
                <p class="post-excerpt">${post.content.substring(0, 120)}...</p>
                <div class="post-meta">
                    <span class="post-date">${post.date}</span>
                    <span class="post-author">by ${post.author}</span>
                </div>
            </div>
        `;
    } else {
        postElement = document.createElement('article');
        postElement.className = 'post-item';
        postElement.innerHTML = `
            <div class="post-thumbnail">
                <img src="${imageUrl}" alt="${post.title}">
            </div>
            <div class="post-summary">
                <span class="post-category">${post.category}</span>
                <h3><a href="blog-post.html">${post.title}</a></h3>
                <p class="post-excerpt">${post.content.substring(0, 80)}...</p>
                <div class="post-meta">
                    <span class="post-date">${post.date}</span>
                </div>
            </div>
        `;
    }
    
    return postElement;
}

// Helper function to get a default image based on category
function getDefaultImageForCategory(category) {
    switch(category) {
        case 'Technology':
            return 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?q=80&w=2370&auto=format&fit=crop';
        case 'Nature':
            return 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?q=80&w=2370&auto=format&fit=crop';
        case 'Travel':
            return 'https://images.unsplash.com/photo-1682686580391-615889d6f345?q=80&w=2370&auto=format&fit=crop';
        case 'Food':
            return 'https://images.unsplash.com/photo-1504754524776-8f4f37790ca0?q=80&w=2370&auto=format&fit=crop';
        case 'Art':
            return 'https://images.unsplash.com/photo-1547891654-e66ed7ebb968?q=80&w=2370&auto=format&fit=crop';
        case 'Literature':
            return 'https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?q=80&w=2370&auto=format&fit=crop';
        case 'Science':
            return 'https://images.unsplash.com/photo-1507413245164-6160d8298b31?q=80&w=2370&auto=format&fit=crop';
        default:
            return 'https://images.unsplash.com/photo-1499750310107-5fef28a66643?q=80&w=2370&auto=format&fit=crop';
    }
}

// Utility function to validate email
function validateEmail(email) {
    const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(String(email).toLowerCase());
}

// Utility function to show notifications
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Show notification
    setTimeout(() => {
        notification.classList.add('show');
    }, 10);
    
    // Hide and remove notification after 3 seconds
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

// Initialize Intersection Observer for animations
function initializeObservers() {
    const fadeElements = document.querySelectorAll('.post, .post-item');
    
    if (!fadeElements.length) return;
    
    // Create an observer instance
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
                observer.unobserve(entry.target);
            }
        });
    }, {
        root: null,
        threshold: 0.1,
        rootMargin: '0px 0px -100px 0px'
    });
    
    // Start observing elements
    fadeElements.forEach(element => {
        observer.observe(element);
    });
}

// Call displayPosts on page load
document.addEventListener('DOMContentLoaded', function() {
    displayPosts();
}); 