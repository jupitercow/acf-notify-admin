# ACF { Notify Admin

Can be used to email admins when an ACF update is made.

## Compatibility

This add-on will work with:

* Tested on version 4

## Installation

This add-on can be treated as a WP plugin.

### Install as Plugin

1. Copy the folder into your plugins folder
2. Activate the plugin via the Plugins admin page

## Use

By default notifications are turned on for all post types on the front end. You can turn them off globally and then turn them on for specific post types or turn them off on specific post types using the filters.

**Deactivate for a single post type**

```php
add_filter( "acf/notify_admin/off/type={$post_type}", '__return_true' ); // Turn off (off = true)
```

**Deactivate globally, activate for a single post type**

```php
add_filter( "acf/notify_admin/off", '__return_true' ); // Turn all off (off = true)
add_filter( "acf/notify_admin/off/type={$post_type}", '__return_false' ); // Turn back on
```

## Filters

* `acf/notify_admin/email/addresses/`
	* An array of email addresses to notify globally.
	* Default: array( get_option('admin_email') )
* `acf/notify_admin/email/addresses/type={$post_type}`
	* An array of email addresses to notify for a specific post_type.
* `acf/notify_admin/email/subject/`
	* The subject of the email globally.
	* Parameters: $post_id.
	* Default: get_option('blogname') . ': Post Edited By ' . $users_full_name
* `acf/notify_admin/email/subject/type={$post_type}`
	* The subject of the email for a specific post type.
* `acf/notify_admin/email/message/`
	* The message of the email globally.
	* Parameters: $post_id.
	* Default: Some summary info
* `acf/notify_admin/email/message/type={$post_type}`
	* The message of the email for a specific post type.