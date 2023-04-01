=== Unique Title Checker ===
Contributors: Kau-Boy
Tags: title, seo, duplicate title, unique title
Tested up to: 6.2
Stable tag: 1.7.0
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0

A simple plugin that checks the title of any post, page or custom post type to ensure it is unique and does not hurt SEO.

== Description ==

This plugin checks the title of a new post/page or any other post type for uniqueness. The plugin provides a filter `unique_title_checker_arguments`, which enables you to modify the `WP_Query` arguments used to find duplicate titles. You may use it to search in more than only current post type for a duplicate title.

This plugin is an enhancement of the [Duplicate Title Checker](https://wordpress.org/plugins/duplicate-title-checker/) by [ketanajani](https://profiles.wordpress.org/ketanajani/) which only supports posts but not pages or custom post types.

== Frequently Asked Questions ==

= Why should I use this plugin? =
Some SEO experts say that you should not have two pages with the same title. If you want to avoid duplicate titles on your website this plugin can help you with that task, as it checks the unqiueness of page titles, before you save or publish a page (or post).

= Will this plugin work with my custom post type? =
Yes! It was implemented with custom post types in mind. With the default setting, it will check for duplicate titles in the same post type only.

= Which titles will be checked? =
With the default settings, all posts with any post status (even custom ones) will be included into the check, with the exception of the statuses "draft", "auto-draft", "inherit" and "trash".

= Will the plugin block a post from being saved, if the title is not unique? =
No! You can always save the post. There might be good reasons for you, to have duplicate titles on your site. The plugin itself does not influence the saving in any kind. It's whole functionality is only based on some AJAX.

= When will the title be checked for it's uniqueness? =
Every time you leave the title input field, the plugin will check the uniqueness of the new title, so you know if it will be unique, before you save the post.

= Can I customize the defaults for the check? =
Absolutely! The plugin provides a filter called `unique_title_checker_arguments`. With this filter, you can alter the arguments used for the `WP_Query`, the plugin uses to get posts with duplicate titles.

= Will the plugin check the uniqueness across different post types? =
No, it only checks the uniqueness per post type. But you can customize the `WP_Query` with the filter mentioned in the previous question. For a check across all post types, you can also [use this plugin implementing the filter for such a check](https://gist.github.com/2ndkauboy/140116e47f2d6c8ae25b002592ac45eb).

= Can I only show messages, if a title is not unique? =
Yes, you can simply use the filter `unique_title_checker_only_unique_error` with `__return_true` to deactivate it ([or use this plugin which implements the filter](https://gist.github.com/140116e47f2d6c8ae25b002592ac45eb)).

== Screenshots ==

1. A post with a unique title using the Classic Editor
2. A new post with a duplicate title using the Classic Editor
3. A post with a unique title using the Block Editor
4. A new post with a duplicate title using the Block Editor

== Changelog ==

= 1.7.0 =
* Fixing the detection of Classic editor in WordPress 6.2
* Time invested for this release: 60min

= 1.6.0 =
* Fixing the check for the Block Editor in new WordPress versions.
* Time invested for this release: 90min

= 1.5.1 =
* Load the correct JavaScript with Classic Editor activate (props to @elvismdev for the issue and PR!)
* Time invested for this release: 30min

= 1.5.0 =
* Improve the translation for languages having "multiple singulars" (e.g. RU)
* Time invested for this release: 45min

= 1.4.1 =
* Fix jQuery noConflict issue with Gutenberg block editor
* Time invested for this release: 15min

= 1.4.0 =
* Add support for Gutenberg block editor
* Time invested for this release: 90min

= 1.3.0 =
* Adding a filter `unique_title_checker_only_unique_error` to hide the success message, if a title is unique
* Time invested for this release: 30min

= 1.2.3 =
* Apply WordPress Coding Standards
* Time invested for this release: 20min

= 1.2.2 =
* Fixing German translation and tag as tested up to 4.5
* Time invested for this release: 10min

= 1.2.1 =
* Fixing query arguments for admin notices
* Time invested for this release: 10min

= 1.2 =
* Show warning when editing a post that has a duplicate title
* Time invested for this release: 30min

= 1.1 =
* Show no warning on empty titles
* Time invested for this release: 10min

= 1.0 =
* First stable version to be deployed in the official WordPress Plugin Repository
* Writing a readme file
* Making some screenshots
* Submitting plugin request form
* Adding plugin to official SVN repository
* Time invested for this release: 2h

= 0.1 =
* First version of the plugin including a German translation
* Time invested for this release: 3h
