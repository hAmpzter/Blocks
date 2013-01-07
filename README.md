# Blocks - Dynamic content areas for WordPress 3.5 +

Blocks is like widgets, let's you add content to specific areas on your site. But Blocks let's you reuse the content on different pages and posts, eisley drag & drop Blocks to wanted areas. 
You controll the Areas by adding them to the database via ´´Blocks -> Settings -> Add new areas´´ 

In your page-template where you want blocks add this (example: Left column) in page.php:

```php
<?php // Block Areas: left ?>

<?php get_blocks('left'); ?>

```
