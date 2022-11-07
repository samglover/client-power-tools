=== Client Power Tools ===

Contributors: samglover
Tags: access control,clients,communication,portal,restrict access,restrict pages
Requires at least: 5.5
Tested up to: 6.1
Requires PHP: 7.3.5
Stable tag: trunk
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.en.html

A free, easy-to-use client dashboard and communication portal built for independent contractors, consultants, lawyers, and other professionals.


== Description ==

Client Power Tools is built for independent contractors, consultants, and professionals. Give your clients a dashboard where they can check on the status of their project, access information and resources in your clients-only knowledge base, and communicate with you—right on your own website!

### Front-End Onboarding & Login

All client interactions happen on the front end of your website, with clean modal forms for logging in—without a password! Client Power Tools will simply blend in with most themes.

Clients are redirected away from the WordPress admin backend and the default WordPress login form.

### The Client Dashboard

Clients can log in to their dashboard on your website at any time.

The dashboard uses regular WordPress pages that you can add to your menus just like any other page. Or you can include the link anywhere you need to, like your blog posts, email newsletters, etc. The only difference is that the client dashboard is only visible to logged-in clients.

You can change the name or permalink of the page, or select a different page entirely.

### Modules

Each module can be enabled or disabled independently of the rest.

#### Status Update Request Button Module

Your clients want to know how things are going!

The status update request button on the client dashboard makes it easy for clients to prompt you for a status update. Once a client clicks the button, they won’t see it again for 30 days—or you can change that number to an interval that works for you.

You can designate one email address to receive all status update requests so you can respond efficiently.

#### Messages Module

Using Client Power Tools to communicate with your clients keeps all your messages in one place so nothing gets lost.

When you send a message to your client, they will receive an email notification with a link to their client dashboard so they can read it and reply. Or you can send them the full message—by default or on a message-by-message basis.

#### Knowledge Base Module

The knowledge base is a clients-only page—or a collection of pages—you can use to share information and resources with your clients.

Just like the client dashboard, the knowledge base is a regular WordPress page with some special features. You can add as many child pages as you like—the knowledge base tab will use a drop-down index and breadcrumb navigation to help your clients find their way around.

### Customization

Client Power Tools is built to be customizable where you need it to be. Here are some of the things you can change to suit your needs:

* **Enable/disable all non-core modules.** (As of 1.4, the non-core modules are the Status Update Request Button, Messaging, and Knowledge Base.)
* **New-client email.** You can customize the email sent to newly added clients so that it reflects the name, email address, subject line, and messaging you prefer.
* **Client statuses.** You can customize the default statuses (potential, active, inactive).
* **Client managers.** You can assign a client manager to each client.
* **Status update request frequency.** Change how often the status update request button is available to your clients.
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

Version 1.5 makes a major change: passwordless login. By default, clients now request a login code by email—no password necessary. (Logging in with a username and password is still supported.)

Passwordless login saves your clients from having to remember their username and password. Instead, they enter their email address to request a one-time-use login code that expires after 10 minutes. On entering the correct code, they will be logged in.

Since WordPress has long supported password reset by email, passwordless login by email is much more convenient but no less secure.

As usual, there are also lots of smaller updates, improvements, and tweaks.

For more information on how to take advantage of the new and updated features, see the [documentation](https://clientpowertools.com/documentation/).


== Changelog ==

### 1.5.2 - 2022-11-07

#### Added
- Knowledge base breadcrumbs are now optional.
- Reminder to clients to check their spam folder for the login code.
- [status-update-request-button] shortcode.
- Show last activity in the admin client list.

#### Changed
- Status update request message now makes sense when messaging is disabled.
- Status update request button is now disabled with "Status Update Requested" instead of invisible when an update has been requested.
- Change knowledge base submenu drop-down animation.

#### Removed
- Somehow a number of files had gotten duplicated in the wrong directories. They're gone now.
- Consolidated /frontend/cpt-knowledge-base.php with /frontend/cpt-client-dashboard.php since it was basically just a duplicate anyway.


### 1.5.1 - 2022-11-04

#### Fixed
- The navigation tabs now fold up nicely at narrow widths.


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
