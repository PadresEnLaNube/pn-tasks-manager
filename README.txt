=== PN Tasks Manager ===
Contributors: felixmartinez, hamlet237
Donate link: https://padresenlanube.com/
Tags: task, time manager, tasking, time tracking, performance
Requires at least: 3.0
Tested up to: 6.9.1
Stable tag: 1.0.10
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Manage your tasks and time tracking with this plugin. Create tasks, assign them to users, and track the time spent on each task.

== Description ==

PN Tasks Manager is a comprehensive WordPress plugin designed to help you manage tasks, track time, and organize your team's workflow efficiently. Whether you're managing personal tasks, team projects, or community events, this plugin provides all the tools you need to stay organized and productive.

= Core Features =

* **Task Management**: Create, edit, and manage tasks with detailed information including titles, descriptions, dates, times, and estimated hours. Tasks support rich content editing with WordPress's built-in editor.

* **User Assignment**: Assign tasks to one or multiple users, making it easy to distribute workload and track responsibilities across your team.

* **Time Tracking**: Track estimated hours for each task and monitor completion status. The plugin calculates total hours per user for performance analysis.

* **Interactive Calendar**: View your tasks in multiple calendar formats:
  * Day view: Detailed view of tasks for a specific day
  * Week view: Overview of tasks across a week
  * Month view: Complete monthly calendar with task indicators
  * Year view: Annual overview with task distribution

* **Recurring Tasks**: Set up tasks that repeat automatically with customizable periodicity:
  * Repeat every X days, weeks, or months
  * Set an end date for recurring tasks
  * Tasks are automatically generated based on your schedule

* **Task Categories**: Organize tasks using hierarchical categories with custom icons and colors for easy visual identification.

* **Public Tasks**: Create public tasks that any user can join, perfect for community events, volunteer opportunities, or collaborative projects.

* **ICS Calendar Export**: Export your tasks to ICS format for seamless integration with Google Calendar, Outlook, Apple Calendar, and other calendar applications.

* **User Ranking**: Track and display user rankings based on completed task hours, motivating team members and recognizing top contributors (admin-only feature).

* **Email Notifications**: Automatically notify users when tasks are assigned to them (requires MailPN plugin for full functionality).

* **Gutenberg Blocks**: Full integration with WordPress block editor. Use blocks to display:
  * Calendar views
  * Task lists
  * Joinable tasks
  * User rankings
  * Individual tasks

* **Shortcodes**: Flexible shortcode system for displaying plugin features anywhere on your site:
  * `[pn-tasks-manager-calendar]` - Display interactive calendar
  * `[pn-tasks-manager-task-list]` - Show task listings
  * `[pn-tasks-manager-joinable-tasks]` - List tasks users can join
  * `[pn-tasks-manager-users-ranking]` - Display user rankings
  * `[pn-tasks-manager-task]` - Display individual tasks
  * `[pn-tasks-manager-call-to-action]` - Create custom call-to-action elements

* **Role-Based Permissions**: Flexible permission system that integrates with WordPress user roles. Control who can create, edit, delete, and manage tasks.

* **Multilingual Support**: Fully translation-ready with support for multiple languages including Spanish, Catalan, Basque, Galician, Italian, and Portuguese. Compatible with Polylang for multilingual sites.

* **AJAX-Powered Interface**: Fast, responsive interface with AJAX functionality for seamless user experience without page reloads.

* **Customizable Styling**: Modern, clean interface with Material Design icons. Styles can be customized to match your theme.

* **Task Status Tracking**: Mark tasks as completed and track progress through your workflow.

* **Task Attachments**: Attach files and media to tasks for better collaboration and documentation.

* **Task Locations**: Add location information to tasks for events and meetings.

* **Task Icons and Colors**: Customize task appearance with icons and colors for quick visual identification.

= Use Cases =

* **Project Management**: Organize team projects with assigned tasks and deadlines
* **Event Planning**: Schedule and manage events with recurring dates
* **Community Management**: Create public tasks for community participation
* **Time Tracking**: Monitor time spent on various activities and projects
* **Team Collaboration**: Assign and track tasks across team members
* **Personal Organization**: Manage personal tasks and schedules

= Technical Features =

* Custom Post Type implementation for tasks
* Hierarchical taxonomy system for categories
* REST API integration (with security controls)
* WordPress coding standards compliant
* Optimized database queries
* Security-focused with nonce verification and capability checks
* Compatible with WordPress 3.0 and higher
* PHP 7.2+ required

This plugin is perfect for WordPress sites that need a robust task management system without the complexity of external services. It integrates seamlessly with WordPress's native features and works with any WordPress theme.


== Credits ==
This plugin stands on the shoulders of giants

Tooltipster v4.2.8 - A rockin' custom tooltip jQuery plugin
Developed by Caleb Jacob and Louis Ameline
MIT license
https://calebjacob.github.io/tooltipster/
https://github.com/calebjacob/tooltipster/blob/master/dist/js/tooltipster.main.js
https://github.com/calebjacob/tooltipster/blob/master/dist/css/tooltipster.main.css

Owl Carousel v2.3.4
Licensed under: SEE LICENSE IN https://github.com/OwlCarousel2/OwlCarousel2/blob/master/LICENSE
Copyright 2013-2018 David Deutsch
https://owlcarousel2.github.io/OwlCarousel2/
https://github.com/OwlCarousel2/OwlCarousel2/blob/develop/dist/owl.carousel.js

Trumbowyg v2.27.3 - A lightweight WYSIWYG editor
alex-d.github.io/Trumbowyg/
License MIT - Author : Alexandre Demode (Alex-D)
https://github.com/Alex-D/Trumbowyg/blob/develop/src/ui/sass/trumbowyg.scss
https://github.com/Alex-D/Trumbowyg/blob/develop/src/ui/sass/trumbowyg.scss
https://github.com/Alex-D/Trumbowyg/blob/develop/src/trumbowyg.js


== Installation ==

1. Upload `pn-tasks-manager.php` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= How do I install the PN Tasks Manager plugin? =

To install the PN Tasks Manager plugin, you can either upload the plugin files to the /wp-content/plugins/pn-tasks-manager directory, or install the plugin through the WordPress plugins screen directly. After uploading, activate the plugin through the 'Plugins' screen in WordPress.

= Can I customize the look and feel of my listings? =

Yes, you can customize the appearance of your listings by modifying the CSS styles provided in the plugin. Additionally, you can enqueue your own custom styles to override the default plugin styles.

= Where can I find the uncompressed source code for the plugin's JavaScript and CSS files? =

You can find the uncompressed source code for the JavaScript and CSS files in the src directory of the plugin. You can also visit our GitHub repository for the complete source code.

= How do I add a new Custom Post Type to my site? =

To add a new Custom Post Type, go to the 'Task' section in the WordPress dashboard and click on 'Add New'. Fill in the required details for your Task, including any custom fields provided by the plugin. Once you're done, click 'Publish' to make the Task live on your site.

= Can I use this plugin with any WordPress theme? =

Yes, the PN Tasks Manager plugin is designed to be compatible with any WordPress theme. However, some themes may require additional customization to ensure the plugin's styles integrate seamlessly.

= Is the plugin translation-ready? =

Yes, the PN Tasks Manager plugin is fully translation-ready. You can use translation plugins such as Loco Translate to translate the plugin into your desired language.

= How do I update the plugin? =

You can update the plugin through the WordPress plugins screen just like any other plugin. When a new version is available, you will see an update notification, and you can click 'Update Now' to install the latest version.

= How do I backup my Task before updating the plugin? =

To backup your Task, you can export your posts and custom post types from the WordPress Tools > Export menu. Choose the 'Task' post type and download the export file. You can import this file later if needed.

= How do I add ratings and reviews to my Task? =

The plugin don't include a built-in ratings and reviews system yet. You can integrate third-party plugins that offer these features or customize the plugin to include them.

= How do I optimize my Task for SEO? =

To optimize your Task for SEO, ensure that you use relevant keywords in your Task titles, descriptions, and content. You can also use SEO plugins like Yoast SEO to further enhance your Task posts' search engine visibility.

= How do I get support for the PN Tasks Manager plugin? =

For support, you can visit the plugin's support forum on the WordPress.org website or contact the plugin author directly through our contact information info@padresenlanube.com.

= Is the plugin compatible with the latest version of WordPress? =

The PN Tasks Manager plugin is tested with the latest version of WordPress. However, it is always a good practice to check for any compatibility issues before updating WordPress or the plugin.

= How do I uninstall the plugin? =

To uninstall the plugin, go to the 'Plugins' screen in WordPress, find the PN Tasks Manager plugin, and click 'Deactivate'. After deactivating, you can click 'Delete' to remove the plugin and its files from your site. Note that this will not delete your Task, but you should back up your data before uninstalling any plugin.


== Changelog ==

= 1.0.0 =

Hello world!