=== Client Power Tools Portal ===

Contributors: samglover
Tags: client management, project management, portal, restrict content, frontend login
Requires at least: 5.5
Tested up to: 6.9.4
Requires PHP: 7.4
Stable tag: trunk
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.en.html

A free, easy-to-use client portal built for designers, developers, consultants, lawyers, and other independent contractors and professionals.


== Description ==

Client Power Tools is built for designers, developers, consultants, lawyers, and other independent contractors and professionals. Give your clients a dashboard where they can check on the status of their project, access information and resources in your clients-only knowledge base, view other clients-only pages, and communicate with you—right on your own website!

### Frontend onboarding & login

All client interactions happen on the front end of your website, with clean modal forms for logging in—without a password! Client Power Tools will simply blend in with most themes.

### The client dashboard

Clients can log in to their dashboard on your website at any time.

The dashboard uses regular WordPress pages, so you can add your client dashboard to your menus just like any other page. Or you can include a link to it anywhere you need to, like your blog posts, email newsletters, etc. The only difference is that the client dashboard is only visible to logged-in clients.

You can change the name or permalink of the default dashboard page, or select a different page entirely.

### Modules

Each module can be enabled or disabled independently of the rest.

#### Projects & stages module

Now you can assign multiple projects to each client, create multiple project types with stages to keep track of your project progress with your clients.

You can change the project label to whatever you prefer (files, matters, dossiers, schemes, capers, etc.) and it will be reflected throughout. The new progress bar provides you and your clients with a visual indicator of each project's progress.

#### Knowledge base module

The knowledge base is a clients-only page—or a collection of pages—that you can use to share information and resources with your clients.

Just like the client dashboard, the knowledge base is a regular WordPress page with some special features. You can add as many child pages as you like—the knowledge base tab will use a drop-down index and breadcrumb navigation to help your clients find their way around.

#### Status update request button module

Your clients want to know how things are going!

The status update request button on the client dashboard makes it easy for clients to prompt you for a status update. Once a client clicks the button, they won’t see it again for 30 days—or you can change that number to an interval that works for you.

You can designate an additional email address to receive all status update requests so you can respond efficiently.

#### Messages module

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
* **Design.** The front-end design of Client Power Tools is as minimal as possible so that Client Power Tools blends into your existing theme. But you can override the Client Power Tools styles as long as you know a little CSS.


== Frequently asked questions ==

= Where can I find Client Power Tools documentation and resources? =

For help using Client Power Tools, see the [support forum](https://wordpress.org/support/plugin/client-power-tools/).

= I'm getting a warning that says my website is not using SSL (HTTPS). Should I be worried? =

You can use Client Power Tools even if your website does not have an SSL certificate, but you should probably get one.

= Where is my client dashboard? Where is my knowledge base? =

You can find your client dashboard by checking your Client Power Tools settings page. In your WordPress dashboard, go to **Client Power Tools** / **Settings** and look for the **Client Dashboard Page** and "Knowledge Base Page" selection fields. There will be a link to your current pages, as well.

You should probably add your client dashboard to your website navigation menu so it is easy for clients to find. You may want to add your knowledge base, as well, if you want clients to be able to find it without going to their dashboard first.

= How do I add pages to my knowledge base? =

To add pages to your knowledge base, just go to the page you want to add (or create a new page) and look for the **Page Attributes** widget in the sidebar. Select your knowledge base page from the **Parent Page** drop-down and save the page. Now the page will appear as a sub-page in your knowledge base.


== Upgrade notice ==

Version 1.11.0 is a thorough refactor to comply with WordPress PHP coding and documentation standards, improve data sanitization and escaping, and fix a couple of bugs.

Version 1.10.0 adds default styles for the navigation menu. These should be overidden by themes that include styles for regular WordPress menus, and have low specificity so they are easy to override with custom CSS. (The default styles are not applied to the [Client Power Tools theme](https://github.com/samglover/cpt-theme).)

Version 1.9.0 includes a new \[client-dashboard] shortcode (in 1.9.2). This allows you to manually place the dashboard on the client dashboard page, which should help those experiencing wonky page layout issues with page builders.

It also addresses a number of output escaping issues identified by the WordPress Plugin Review Team's [Plugin Check plugin](https://wordpress.org/plugins/plugin-check/), and a cross-site-scripting (XSS) vulnerability.

As usual, there are also lots of smaller updates, improvements, and tweaks. See the changelog for details.

== Changelog ==

### 1.11.7 - 2026-04-17

#### Added
- PHPCS ruleset with custom capabilities

#### Fixed
- Fix select field rendering and processing (e.g., in the the edit client and edit project forms)
- Replace custom settings saved notification with `settings_errors()`


### 1.11.6 - 2025-11-15

#### Fixed
- Fixed a Javascript error on non-client-dashboard pages


### 1.11.5 - 2025-10-26

#### Changed
- Now uses updated icons from cpt-theme


### 1.11.4 - 2025-09-29

#### Changed
- Convert status update request button label to sentence case


### 1.11.3 - 2025-09-01

#### Added
- New `/assets/js/cpt-prevent-offscreen-submenus.js` prevents submenus from appearing offscreen

#### Changed
- Inactive clients are now dimmed in the admin client list. [See note.](https://github.com/samglover/client-power-tools/pull/87)
- Margins removed from menu items and sub-menus by default to help with theme compatibility
- Drop-down menus updated with improvements from `cpt-theme`: [samglover/cpt-theme@aaf14d1](https://github.com/samglover/cpt-theme/commit/aaf14d172983e220f9034f55f5d1bedcd8cff622) & [samglover/cpt-theme@9c6d62a](https://github.com/samglover/cpt-theme/commit/9c6d62a787cb7f4acc58c09e62b066a54f1e3f92)


### 1.11.2 - 2025-08-31

#### Changed
- Migrated `@import` SCSS rules to `@use`


### 1.11.1 - 2025-03-17

#### Added
-  POT file for localization


### 1.11.0 - 2025-03-16

#### Added
- PHP documentation according to the WordPress Coding Standards
- New functions in /common/cpt-common.php for retrieving and sanitizing user data and user meta from `$_POST`:
  - `cpt_get_client_userdata_keys()`
  - `cpt_get_sanitized_userdata_from_post()`
  - `cpt_get_client_usermeta_keys()`
  - `cpt_get_sanitized_usermeta_from_post()`
- Sanitize `$_POST` variables in /common/cpt-status-update-request-button.php

#### Changed
- Tidied up in general while adding documentation
- Rename `cpt_days_since_last_request()` to `cpt_get_days_since_last_request()`, and now it returns false instead of null
- Clear location before reloading the page on successful login
- Refactor and simplify `cpt_is_client_dashboard()` function
- Rename files according to WP Coding Standards:
  - cpt-clients-table.php -> class-client-list-table.php
  - cpt-projects-table.php -> class-project-list-table.php
  - cpt-admin-messages-table.php -> class-project-list-table.php
  - cpt-project-types-table.php -> class-project-types-list-table.php
- Replace `wp_redirect( $_POST['_wp_http_referer'] )` with `wp_safe_redirect( wp_get_referer() )` throughout
- Refactor deleting a project type

#### Removed
- `cpt_admin_actions()` because it wasn't actually doing anything
- `cpt_remove_client_manager()` because it was simple enough to handle in `cpt_process_client_manager_actions()`

**See changelog.txt for older versions.**


== Installation ==

#### Automatic

Automatic installation is the easiest option. To do an automatic install of Client Power Tools, log in to your WordPress dashboard and go to **Plugins** / **Add New**. Enter “Client Power Tools" in the search field. To install Client Power Tools, click the **Install Now** button, then activate it by clicking the **Activate** button.

#### Manual

In order to install Client Power Tools manually, you will need to download the plugin here, then upload it to WordPress or use FTP software to upload it to your web server. [Visit the WordPress Codex for manual installation instructions.](https://wordpress.org/support/article/managing-plugins/#manual-upload-via-wordpress-admin)

#### Updating

I recommend enabling automatic updates for Client Power Tools. To enable automatic updates, log into your WordPress dashboard and go to **Plugins** / **Installed Plugins**. Look for Client Power Tools and click **Enable auto-updates** in the **Automatic Updates** column.
