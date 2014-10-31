Unique Title Checker
====================

A simple plugin that checks the title of any post, page or custom post type to ensure it is unique and does not hurt SEO.

This is plugin is an enhancement of the [Duplicate Title Checker](https://wordpress.org/plugins/duplicate-title-checker/) by [ketanajani](https://profiles.wordpress.org/ketanajani/) which only supports posts but not pages or custom post types.

The plugin provides a filter `unique_title_checker_arguments`, which enables you to modify the `WP_Query` arguments used to find duplicate titles. You may use it so search in more than only current post type for a duplicate title.