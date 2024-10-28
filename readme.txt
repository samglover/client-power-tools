=== Client Power Tools Portal ===

Contributors: samglover
Tags: client management, project management, portal, restrict content, frontend login
Requires at least: 5.5
Tested up to: 6.6.2
Requires PHP: 7.4
Stable tag: trunk
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.en.html

A free, easy-to-use client portal built for designers, developers, consultants, lawyers, and other independent contractors and professionals.


== Description ==

Client Power Tools is built for designers, developers, consultants, lawyers, and other independent contractors and professionals. Give your clients a dashboard where they can check on the status of their project, access information and resources in your clients-only knowledge base, view other clients-only pages, and communicate with you—right on your own website!

### Front-End Onboarding & Login

All client interactions happen on the front end of your website, with clean modal forms for logging in—without a password! Client Power Tools will simply blend in with most themes.

### The Client Dashboard

Clients can log in to their dashboard on your website at any time.

The dashboard uses regular WordPress pages, so you can add your client dashboard to your menus just like any other page. Or you can include a link to it anywhere you need to, like your blog posts, email newsletters, etc. The only difference is that the client dashboard is only visible to logged-in clients.

You can change the name or permalink of the default dashboard page, or select a different page entirely.

### Modules

Each module can be enabled or disabled independently of the rest.

#### Projects & Stages Module

Now you can assign multiple projects to each client, create multiple project types with stages to keep track of your project progress with your clients.

You can change the project label to whatever you prefer (files, matters, dossiers, schemes, capers, etc.) and it will be reflected throughout. The new progress bar provides you and your clients with a visual indicator of each project's progress.

#### Knowledge Base Module

The knowledge base is a clients-only page—or a collection of pages—that you can use to share information and resources with your clients.

Just like the client dashboard, the knowledge base is a regular WordPress page with some special features. You can add as many child pages as you like—the knowledge base tab will use a drop-down index and breadcrumb navigation to help your clients find their way around.

#### Status Update Request Button Module

Your clients want to know how things are going!

The status update request button on the client dashboard makes it easy for clients to prompt you for a status update. Once a client clicks the button, they won’t see it again for 30 days—or you can change that number to an interval that works for you.

You can designate an additional email address to receive all status update requests so you can respond efficiently.

#### Messages Module

Using Client Power Tools to communicate with your clients keeps all your messages in one place so nothing gets lost.

When you send a message to your client, they will receive an email notification with a link to their client dashboard so they can read it and reply. Or you can send them the full message—by default or on a message-by-message basis.

### Customization

Client Power Tools is built to be customizable where you need it to be. Here are some of the things you can change to suit your needs:

* **Enable/disable all non-core modules.** (As of 1.4, the non-core modules are the Status Update Request Button, Messaging, and Knowledge Base.)
* **Customize project labels.** Some people have projects, others have files, matters, dossiers, schemes, capers, etc. You can use whatever label you prefer, and it will be reflected throughout Client Power Tools.
* **Project types & stages.** Add project types, and for each type specify the stages you want your client to be able to see on their progress bar.
* **Additional pages.** Restrict any page on your website to logged-in clients.
* **New-client email.** You can customize the email sent to newly added clients so that it reflects the name, email address, subject line, and messaging you prefer.
* **Client IDs.** When adding or updating a client, you can add a custom client ID.
* **Client statuses.** You can customize the default statuses (potential, active, inactive).
* **Client managers.** You can set a default client manager and assign a different client manager to each client.
* **Show/hide the status update request button.** The status update request button is a great way to empower your clients, but if you don't want to use it you can turn it off.
* **Status update request frequency.** Change how often the status update request button is available to your clients.
* **Status update request recipient.** Designate one person to get notified of all status update requests.
* **Email notifications.** By default, Client Power Tools sends a notification, not the content of your message. But you can change the default behavior or override it for individual messages.
* **Design.** The front-end design of Client Power Tools is as minimal as possible so that Client Power Tools blends into your existing theme. But you can override the Client Power Tools styles as long as you know a little CSS. (See the [documentation](https://clientpowertools.com/documentation/) for more details.)


== Frequently Asked Questions ==

= Where can I find Client Power Tools documentation and resources? =

For help using Client Power Tools, see the [documentation](https://clientpowertools.com/documentation/).

= I'm getting a warning that says my website is not using SSL (HTTPS). Should I be worried? =

You can use Client Power Tools even if your website does not have an SSL certificate, but you should probably get one. For more information, see the [Securing WordPress for Client Power Tools](https://clientpowertools.com/security/) resource page.

= Where is my client dashboard? Where is my knowledge base? =

You can find your client dashboard by checking your Client Power Tools settings page. In your WordPress dashboard, go to **Client Power Tools** / **Settings** and look for the **Client Dashboard Page** and "Knowledge Base Page" selection fields. There will be a link to your current pages, as well.

You should probably add your client dashboard to your website navigation menu so it is easy for clients to find. You may want to add your knowledge base, as well, if you want clients to be able to find it without going to their dashboard first. If you need help with this, see the [documentation](https://clientpowertools.com/documentation/).

= How do I add pages to my knowledge base? =

To add pages to your knowledge base, just go to the page you want to add (or create a new page) and look for the **Page Attributes** widget in the sidebar. Select your knowledge base page from the **Parent Page** drop-down and save the page. Now the page will appear as a sub-page in your knowledge base.


== Upgrade Notice ==

Version 1.10 adds default styles for the navigation menu. These should be overidden by themes that include styles for regular WordPress menus, and have low specificity so they are easy to override with custom CSS. (The default styles are not applied to the [Client Power Tools theme](https://github.com/samglover/cpt-theme).)

Version 1.9 includes a new \[client-dashboard] shortcode (in 1.9.2). This allows you to manually place the dashboard on the client dashboard page, which should help those experiencing wonky page layout issues with page builders.

It also addresses a number of output escaping issues identified by the WordPress Plugin Review Team's [Plugin Check plugin](https://wordpress.org/plugins/plugin-check/), and a cross-site-scripting (XSS) vulnerability.

As usual, there are also lots of smaller updates, improvements, and tweaks. See the changelog for details.

For more information on how to take advantage of the new and updated features, see the [documentation](https://clientpowertools.com/documentation/).


== Changelog ==

### 1.10.0 - 2024-10-28

#### Added
- Default styles for the navigation menu. These should be overidden by themes that include styles for regular WordPress menus, and have low specificity so they are easy to override with custom CSS. (The default styles are not applied to the [Client Power Tools theme](https://github.com/samglover/cpt-theme).)


### 1.9.3 - 2024-10-27

#### Changed
- Reorganized and streamlined some code.

#### Fixed
- Fixed duplicate dashboard output and message output.


### 1.9.2 - 2024-10-27

#### Added
- \[client-dashboard] shortcode allows the dashboard to be placed manually on the dashboard page.

#### Changed
- Abstracted several dashboard functions to make the code easier to follow and to make it easier to implement the new shortcode.

#### Fixed
- Content added to the dashboard page in the WordPress editor no longer shows up on the Messages and Projects pages/tabs.
- Enforced strict type comparison and Yoda condition checks for the cpt_is_client_dashboard() and related functions.


### 1.9.1 - 2024-10-25

#### Changed
- References to WP_User_Query now use the correct case (WP_User_Query instead of WP_USER_QUERY).

#### Fixed
- Fixed cross-site scripting (XSS) vulnerability.


### 1.9.0 - 2024-10-24

#### Added
- Installed Composer in order to use WordPress Coding Standards formatting aids.
- Added notes for translators.

#### Changed
- Include WP_List_Table from core instead of including the file.
- Reformatted PHP according to the WordPress Coding Standards.
- Switch the SVG logo used in the admin for a PNG to fix a Plugin Check alert.

#### Removed
- /includes
- /includes/class/wp-list-table.php

#### Fixed
- Added escape functions for all output to resolve Plugin Check alerts. In many cases this resulted in minor changes to the output, some of which would impact existing translations. As a result this release merits a minor version increment to 1.9.0, but since there are no actual translations (that I am aware of, at least) I don't think it will impact any existing users.


### 1.8.6 - 2024-08-25

#### Fixed
- Fixed a bug in cpt_messages() where calling get_the_ID() after cpt_messages() would return the last message ID instead of the main post ID.
- Fixed a bug in cpt_get_notices() where WordPress would add a second, unstyled dismiss button to notices on the frontend.


### 1.8.5 - 2024-06-09

#### Changed
- Admin styles and scripts are now loaded only on CPT admin pages.
- Removed cpt_redirect_clients() function from /admin/cpt-admin.php because it wasn't actually doing anything.

#### Fixed
- The guard statements in the cpt_admin_actions() function in /admin/cpt-admin.php were over-inclusive and allowing some processes through only to cause them to fail. See [issue #40](https://github.com/samglover/client-power-tools/issues/40) for an example where WP All Import uploads were failing. This is now fixed.


### 1.8.4 - 2024-01-23

#### Changed
- Name updated to Client Power Tools Portal since there is also a Client Power Tools theme and several helper plugins.
- Split up /assets/js/cpt-frontend.js into /assets/js/cpt-login-modal.js and /assets/js/cpt-notices.js.
- Simplified the login modal and notices functionality and styling. The CSS styling has been simplified with the idea that it should be relatively easy to customize. (Here is how I customize Client Power Tools for my own theme: https://github.com/samglover/cpt-theme/blob/main/assets/scss/_customize-cpt.scss. Each SCSS variable pulls in a corresponding CSS variable from theme.json that applies my styles for things like borders and shadows so that Client Power Tools looks as much a part of the theme as possible.)
- The client dashboard nav menu now uses the same classes as regular WordPress menus, so it should inherit styling and dropdown behavior, as well. This drastically reduces the amount of styling added by this plugin.
- Enabling breadcrumb navigation now applies to additional pages as well as the knowledge base.

### Fixed
- Fixed error when sending messages from the front end.
- Fixed login modal animation.

### Removed
- Removed the /frontend/images folder and its contents, which were not used anywhere.


### 1.8.3 - 2024-01-11

#### Changed
- Added .wp-element-button to all buttons for better theme.json compatibility.


### 1.8.2 - 2024-01-08

#### Changed
- Client manager and custom fields are wider.
- Disabled project type taxonomy archive pages.
- Disabled autocomplete on the new- and edit-client forms.


### 1.8.1 - 2023-10-13

#### Fixed
- Get client name for the admin client list table.
- Don't show recipients if they weren't stored when sending the message.


### 1.8 - 2023-10-13

#### Added
- Separate client name and primary contact name.
- Add additional contacts to be CCed on messages.


### 1.7.8 - 2023-08-08

#### Fixed
- The login form would sometimes fail under unusual conditions. Now it should happily log you in as expected, or else deliver error messages instead of just sitting there opaquely.


### 1.7.7 - 2023-08-03

#### Changed
- Renamed the primary dashboard page in the navigation tabs to "Home.'

#### Fixed
- The home page title would show as "Client Dashboard: Client Dashboard" which was dumb. Fixed.
- Moved the page title filter from /common/cpt-common.php to /frontend/cpt-frontend.php.

### 1.7.6 - 2023-07-27

#### Added
- cpt_is_additional_page()
- cpt_add_nav_to_addl_pages()

#### Changed
- No longer shows projects in the client list table if the Projects module is not active.
- Consolidated Knowledge Base breadcrumbs logic in the cpt_breadcrumbs function.
- Clarified cpt_is_knowledge_base() logic.
- cpt_is_client_dashboard() now functions more intuitively. If no arguments are given, it returns true if any dashboard page is being shown. Arguments may also be given in the form of an array.
- Page titles within the client dashboard now include both the dashboard title and the name of the page for all pages.

#### Removed
- Removed the confusingly named cpt_is_cpt() function from /frontend/frontend.php. cpt_is_client_dashboard() does the same job. (cpt_is_cpt() still works; it just returns the output of cpt_is_client_dashboard()).


### 1.7.5 - 2023-07-24

#### Added
- cpt_is_client_dashboard('additional page') now returns true on additional pages and their descendants.


### 1.7.4 - 2023-05-22

#### Changed
- Capitalize project labels just in case the user doesn't.
- Add body classes for the primary dashboard tabs (dashboard, projects, and messages).
- Updated front-end project page.


### 1.7.3 - 2023-05-10

#### Changed
- TinyMCE editor toolbar simplified.
- Restore message background color.

#### Fixed
- Prevent multiple CPT nav menus from showing up on the knowledge base.


### 1.7.2 - 2023-05-08

#### Changed
- Added a "no projects found" message when a client does not have any projects.

#### Removed
- Removed custom fields from project forms.

#### Fixed
- Fixed a bug that was causing messages to be sent to the sender rather than the intended recipient, which is obviously not how it is supposed to work.


### 1.7.1 - 2023-05-08

#### Changed
- Defaults for new project-related settings.
- Updated readme.txt.


### 1.7 - 2023-05-08

#### Added
- NEW MODULE: Projects & Stages! Organize your client projects and assign stages to different project types.
- The new project widget and progress bar give a visual representation of your client's projects on their profile and dashboard.
- Customize the projects label to whatever makes sense for your industry (e.g., files, accounts, matters, cases, dossiers).

#### Changed
- Change capability names (replaced hyphens with underscores).
- Removed the default client manager.
- Show an error if a page requests a nonexistent client.
- Lots of small user experience improvements and updates.
- All forms updated with an improved layout.
- Reorganized stylesheets.

#### Removed
- /assets/js/messages.js (It was empty.)

#### Fixed
- When one expander is active, clicking another expander now closes it and restores the button label.
- Fixed page titles not showing up on empty dashboard pages.
- Lots of smaller fixes in the course of adding the Projects & Stages module.


### 1.6 - 2022-12-10

#### Added
- It's now possible to add pages to the client dashboard navigation. Additional pages will be restricted to logged-in clients.
- Optionally, include child pages of additional pages in the client dashboard navigation.


### 1.5 - 2022-11-03

#### Added
- IMPORTANT: Passwordless login. The default frontend login process is now passwordless. Instead of entering a username and password, clients request a login code by email. (Username and password is still available.)

#### Changed
- Updated the new-client welcome message for passwordless login.
- The frontend login form now uses AJAX rather than reloading the page with each submission.
- Adjusted login modal max width.
- Move the new and edit client forms to their own files, /admin/cpt-new-client-form.php and /admin/cpt-edit-client-form.php.
- Simplified notices in the admin and on the front end.
- Numerous minor formatting changes, tweaks, cleanup, and reorganizing, as usual.

#### Fixed
- Fix the modal dismiss button display.
- Restore button code style to email cards.
- Added overflow scrolling for modals.

**See changelog.txt for older versions.**


== Installation ==

#### Automatic

Automatic installation is the easiest option. To do an automatic install of Client Power Tools, log in to your WordPress dashboard and go to **Plugins** / **Add New**. Enter “Client Power Tools" in the search field. To install Client Power Tools, click the **Install Now** button, then activate it by clicking the **Activate** button.

#### Manual

In order to install Client Power Tools manually, you will need to download the plugin here, then upload it to WordPress or use FTP software to upload it to your web server. [Visit the WordPress Codex for manual installation instructions.](https://wordpress.org/support/article/managing-plugins/#manual-upload-via-wordpress-admin)

#### Updating

I recommend enabling automatic updates for Client Power Tools. To enable automatic updates, log into your WordPress dashboard and go to **Plugins** / **Installed Plugins**. Look for Client Power Tools and click **Enable auto-updates** in the **Automatic Updates** column.
