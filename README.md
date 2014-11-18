# Unique Title Checker #
**Contributors:** Kau-Boy  
**Tags:** title, seo, duplicate title, unique title  
**Tested up to:** 4.0  
**Stable tag:** 1.2  
**License:** GPLv3  
**License URI:** http://www.gnu.org/licenses/gpl-3.0  

A simple plugin that checks the title of any post, page or custom post type to ensure it is unique and does not hurt SEO.

## Description ##
The plugin provides a filter `unique_title_checker_arguments`, which enables you to modify the `WP_Query` arguments used to find duplicate titles. You may use it so search in more than only current post type for a duplicate title.

This is plugin is an enhancement of the [Duplicate Title Checker](https://wordpress.org/plugins/duplicate-title-checker/) by [ketanajani](https://profiles.wordpress.org/ketanajani/) which only supports posts but not pages or custom post types.

## Frequently Asked Questions ##

### Why should I use this plugin? ###
Some SEO experts say, that you should not have two pages with the same title. If you website wants to avoid duplicate titles, this plugin can help you on that task, as it checks the unqiueness, before you save or publish a post.

### Will this plugin work with my custom post type? ###
Yes! It was implemented with custom post types in mind. With the default setting, it will check for duplicate titles only in the same post type.

### Which titles will be checked? ###
With the default settings, all posts with any post status (even custom ones) will be included into the check, with the exception of the statuses "draft", "auto-draft", "inherit" and "trash".

### Will the plugin block a post from being saved, if the title is not unique? ###
No! You can always save the post. There might be good reasons for you, to have duplicate titles on your site. The plugin itself does not influence the saving in any kind. It's whole functionality is only based on some AJAX.

### When will the title be checked for it's uniqueness? ###
Every time you leave the title input field, the plugin will check the uniqueness of the new title, so you know if it will be unique, before you save the post.

### Can I customize the defaults for the check? ###
Absolutely! The plugin provides a filter called `unique_title_checker_arguments`. With this filter, you can alter the arguments used for the `WP_Query`,  the plugin uses to get posts with duplicate titles.

## Screenshots ##
### 1. A post with a unique title ###
![A post with a unique title](https://raw.githubusercontent.com/2ndkauboy/unique-title-checker/master/assets/screenshot-1.png)

### 2. A new post with a duplicate title ###
![A new post with a duplicate title](https://raw.githubusercontent.com/2ndkauboy/unique-title-checker/master/assets/screenshot-2.png)


## Changelog ##
### 1.2 ###
* Show warning when editing a post that has a duplicate title
* Time invested for this release: 30min

### 1.1 ###
* Show no warning on empty titles
* Time invested for this release: 10min

### 1.0 ###
* First stable version to be deployed in the official WordPress Plugin Repository
* Writing a readme file
* Making some screenshots
* Submitting plugin request form
* Adding plugin to official SVN repository
* Time invested for this release: 2h

### 0.1 ###
* First version of the plugin including a German translation
* Time invested for this release: 3h