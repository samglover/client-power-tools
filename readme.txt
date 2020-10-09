=== Client Power Tools ===

Contributors: samglover
Tags: access control,clients,communication,portal,restrict access,restrict pages
Requires at least: 5.5
Tested up to: 5.5.1
Requires PHP: 7.3.5
Stable tag: trunk
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.en.html

A free, easy-to-use private client dashboard and communication portal built for independent contractors, consultants, lawyers, and other professionals.


== Description ==

Client Power Tools is built for independent contractors, consultants, and professionals. Give your clients a dashboard where they can check on the status of their project and communicate with you—right on your own website!

#### Front-End Onboarding & Login

All client interactions happen on the front end of your website, with clean modal forms for logging in and setting or changing passwords.

Clients are blocked from the WordPress admin and the default login page.

#### The Client Dashboard

Clients can log in to their dashboard on your website at any time.

The dashboard itself is a WordPress page, so you can add it to your menus like any other page. Or you can include the link anywhere else, like your email newsletters.

You can change the name or permalink of the page, or select a different page entirely.

#### Status Update Request Button

Your clients want to know how things are going!

The status update request button on the client dashboard makes it easy for clients to prompt you for a status update. Once a client clicks the button, they won’t see it again for 30 days—or you can change that number to an interval that works for you.

You can designate one email address to receive all status update requests so you can respond efficiently.

#### Messaging & Notifications

Using Client Power Tools to communicate with your clients keeps all your messages in one place so nothing gets lost.

When you send a message to your client, they will receive an email notification with a link to their client dashboard so they can read it and reply.

#### Customization

Client Power Tools is built to be customizable where you need it to be. Here are some of the things you can change to suit your needs:

* **New-client email.** You can customize the email sent to newly added clients so that it reflects the name, email address, subject line, and messaging you prefer.
* **Client IDs.** When adding or updating a client, you can add a custom client ID.
* **Client statuses.** You can customize the default statuses (potential, active, inactive).
* **Status update request frequency.** Change how often the status update request button is available to your clients.
* **Status update request recipient.** Designate one person to field all the status update requests.
* **Design.** The front-end design of Client Power Tools is as minimal as possible so that Client Power Tools blends into your existing theme. But you can override the Client Power Tools styles as long as you know a little CSS. (See the [documentation](https://clientpowertools.com/documentation/) for more details.)


== Frequently Asked Questions ==


== Upgrade Notice ==

This is a bugfix release that resolves a few of lingering issues from the 1.0 release. Plus general tidying up.


== Changelog ==

= 1.0.5 =

### Changed
- Handle frontend login error on the front end.
- General tidying up.

### Removed
- Remove unused capabilities from Client Manager role (for now).
- Remove unused functions cpt_get_client_profile_link and cpt_get_client_id.

### Fixed
- Email notifications should now deliver with the intended formatting.


== Installation ==

#### Automatic

Automatic installation is the easiest option. To do an automatic install of WooCommerce, log in to your WordPress dashboard and go to **Plugins** / **Add New**. Enter “Client Power Tools" in the search field. To install Client Power Tools, click the **Install Now** button, then activate it by clicking the **Activate** button.

#### Manual

In order to install Client Power Tools manually, you will need to download the plugin here, then upload it to WordPress or use FTP software to upload it to your web server. [Visit the WordPress Codex for manual installation instructions.](https://wordpress.org/support/article/managing-plugins/#manual-upload-via-wordpress-admin)

#### Updating

I recommend enabling automatic updates for Client Power Tools. To enable automatic updates, log into your WordPress dashboard and go to **Plugins** / **Installed Plugins**. Look for Client Power Tools and click **Enable auto-updates** in the **Automatic Updates** column.
