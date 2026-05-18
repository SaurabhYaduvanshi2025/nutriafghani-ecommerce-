# Dynamic Blog Setup

This project now includes a PHP/MySQL blog system with Quill rich text editing.

## 1. Create The Table

Open phpMyAdmin, select the `nutriafghandry` database, and run:

```sql
CREATE TABLE IF NOT EXISTS `blogs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `content` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_blog_slug` (`slug`),
  KEY `idx_blogs_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

The same SQL is saved in `config/blogs.sql`.

## 2. Upload Folders

Make sure these folders exist and are writable by PHP:

- `uploads/blogs`
- `uploads/blogs/content`

Thumbnail images are saved in `uploads/blogs`. Images inserted inside Quill content are saved in `uploads/blogs/content`.

## 3. Admin Pages

- `admin/blog-list.php`: show all blogs, edit, view, and delete
- `admin/add-blog.php`: add a new blog
- `admin/edit-blog.php`: update a blog
- `admin/delete-blog.php`: delete a blog
- `admin/blog-upload.php`: AJAX upload endpoint for Quill content images

The admin sidebar now includes a Blogs menu.

## 4. Frontend Pages

- `blog.php`: dynamic blog listing
- `blog-detail.php?slug=your-blog-slug`: single blog details

Blog content is saved as Quill HTML and rendered inside a `.ql-editor` container with Quill's Snow stylesheet, so headings, colors, alignment, font sizes, image placement, and resized images display like they were written in the editor.
