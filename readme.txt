=== Client Power Tools ===

Contributors: samglover
Tags: access control,clients,communication,portal,restrict access,restrict pages
Requires at least: 5.5
Tested up to: 6.0.1
Requires PHP: 7.3.5
Stable tag: trunk
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.en.html

A free, easy-to-use client dashboard and communication portal built for independent contractors, consultants, lawyers, and other professionals.


== Description ==

Client Power Tools is built for independent contractors, consultants, and professionals. Give your clients a dashboard where they can check on the status of their project, access information and resources in your clients-only knowledge base, and communicate with you—right on your own website!

### Front-End Onboarding & Login

All client interactions happen on the front end of your website, with clean modal forms for logging in and setting or changing passwords. Client Power Tools will simply blend in with most themes.

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

#### Knowledge Base Module (NEW!)

The knowledge base is a clients-only page—or a collection of pages you can use to share information and resources with your clients.

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

Version 1.4 adds four major features:

1. NEW MODULE: Knowledge Base!
2. Tabbed Navigation for the client dashboard and breadcrumb navigation within the knowledge base.
3. Now you can add your own content to the client dashboard.
4. All non-core modules can now be enabled/disabled.

The **Knowledge Base** is a clients-only page—or a collection of pages—you can use to share information and resources with your clients. Like the client dashboard, the knowledge base is a regular WordPress page with special features, including a drop-down index and breadcrumb navigation to help your clients find their way around.

The client dashboard now features a tabbed navigation menu, with a separate tab for each enabled module. When developing the new Knowledge Base module, it became obvious that the client dashboard is too big for a single page. With the tabbed navigation, there's room to grow.

And now that messaging isn't taking up the entire client dashboard, there is plenty of room for you to add your own content to the page. For example, you might want to show a welcome message to your clients and highlight some essential information or resources in your knowledge base.

Finally, by popular demand each of the three existing non-core modules can now be enabled or disabled from the settings page. Currently the non-core modules are the Status Update Request Button, Messaging, and Knowledge Base.

As usual, there are also lots of smaller updates, improvements, and tweaks.

For more information on how to take advantage of the new and updated features, plus ideas for how to use the Knowledge Base, see the [documentation](https://clientpowertools.com/documentation/).


== Changelog ==

### 1.4.9 - 2022-07-20

#### Changed
- Misc. code cleanup and reformatting.

#### Fixed
- Removed overflow: scroll from modal cards.
- Expanders in the admin now work as expected.


### 1.4.8 - 2022-03-14

#### Changed
-Moved CSS, JS, and image files to /assets.
-Configured for SASS.
-Replaced all icons with SVG images using file_get_contents() instead of <img> tags.
-Updated various styles to be more flexible and consistent across themes.

#### Fixed
-Fixed an error that prevented the login/logout modal from displaying.


### 1.4.7 - 2022-01-12

#### Changed
-Specify border for modal dismiss button for themes that might try to add a border.
-Add .button class to the password reset submit button so it's more likely to pick up the button style from the theme.

#### Fixed
-Fixed the cpt_is_client() function for users with no roles.
-Fixed the modal dismiss button when there are more than two modals on the page.


### 1.4.6 - 2022-01-01

#### Fixed
- Fixed a JS error when the page is not a client page.


### 1.4.5 - 2021-12-31

#### Changed
- Add nofollow to the login modal links.

#### Fixed
- Fix a PHP error when the page does not have an ID.


### 1.4.4 - 2021-12-28

#### Changed
- Clarified the additional status update request notification instructions on the settings page.
- Clarified the new client account activation email message body instructions on the settings page.
- Don't know messages on client profiles in the admin if messaging is disabled.


### 1.4.3 - 2021-10-08

#### Added
- Message links in the admin message list now go to the page on which the message appears.


### 1.4.2 - 2021-06-06

#### Changed
- Removed jQuery from cpt-admin.js.
- Removed jQuery from cpt-frontend.js.


### 1.4.1 - 2021-02-12

#### Added
- Added a wrapper for the status request update button.


### 1.4 - 2021-01-12

#### Added
- NEW MODULE: Knowledge Base! The knowledge base is a restricted page you can use to share information and resources with your clients. Add as many sub-pages as you like; they will be nicely organized in an index for your clients.
- Client dashboard pages now have navigation tabs, including breadcrumbs and a drop-down index to help clients navigate your knowledge base.

#### Changed
- The settings page is better organized, with general/core settings, then settings for each module.
- Each of the non-core modules can be disabled. Currently the non-core modules are Status Update Request Button, Messaging, and Knowledge Base.
- You can now add content to the main client dashboard page, which will be shown below the welcome message and status update request button (if it is enabled).

#### Fixed
- Untitled messages no longer show "Untitled" as the title.

### 1.3 - 2020-11-24

#### Added
- Client managers can now be added and removed from the new **Managers** submenu, and are also shown in the client list and client profile. As you can when adding a client, you can add a new or existing WordPress user.
- Assign a default client manager in the settings.
- Filter the client list by clients assigned to you ("Mine").

#### Changed
- The add-client form is now on the Clients page.
- Messages and status update requests now go to the client manager. (The email will be CC'd to the now-optional status update request notification email address, if you have set one.)
- New client emails will now come from the client manager instead of a default name and email address.
- Update the element expander script so it can handle multiple expanders on the same page. Also, form elements within an expander with data-required="true" will have the required attributes removed when hidden.
- Make cpt_get_notices() easier to use by changing the argument to an array so it only needs to be called once.

#### Fixed
- The standard login form no longer redirects to a blank page when a redirect is present.

**See changelog.txt for older versions.**


== Installation ==

#### Automatic

Automatic installation is the easiest option. To do an automatic install of Client Power Tools, log in to your WordPress dashboard and go to **Plugins** / **Add New**. Enter “Client Power Tools" in the search field. To install Client Power Tools, click the **Install Now** button, then activate it by clicking the **Activate** button.

#### Manual

In order to install Client Power Tools manually, you will need to download the plugin here, then upload it to WordPress or use FTP software to upload it to your web server. [Visit the WordPress Codex for manual installation instructions.](https://wordpress.org/support/article/managing-plugins/#manual-upload-via-wordpress-admin)

#### Updating

I recommend enabling automatic updates for Client Power Tools. To enable automatic updates, log into your WordPress dashboard and go to **Plugins** / **Installed Plugins**. Look for Client Power Tools and click **Enable auto-updates** in the **Automatic Updates** column.
