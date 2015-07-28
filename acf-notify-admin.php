<?php

/**
 * @link              https://github.com/jupitercow/
 * @since             1.1.0
 * @package           Acf_Notify_Admin
 *
 * @wordpress-plugin
 * Plugin Name:       Advanced Custom Fields: Notify Admin
 * Plugin URI:        http://Jupitercow.com/
 * Description:       Allows a form to notify the adminitstrator or a set list of email addresses when submitted.
 * Version:           1.1.0
 * Author:            Jupitercow
 * Author URI:        http://Jupitercow.com/
 * Contributor:       Jake Snyder
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       acf_notify_admin
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

$class_name = 'Acf_Notify_Admin';
if (! class_exists($class_name) ) :

class Acf_Notify_Admin
{
	/**
	 * The unique prefix for ACF.
	 *
	 * @since    1.1.0
	 * @access   protected
	 * @var      string    $prefix         The string used to uniquely prefix for Sewn In.
	 */
	protected $prefix;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.1.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.1.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Load the plugin.
	 *
	 * @since	1.1.0
	 * @return	void
	 */
	public function run()
	{
		if (! $this->test_requirements() ) { return false; }

		add_action( 'init',                   array($this, 'init') );
	}

	/**
	 * Make sure that any neccessary dependancies exist
	 *
	 * @author  Jake Snyder
	 * @since	1.1.0
	 * @return	bool True if everything exists
	 */
	public function test_requirements()
	{
		// Look for ACF
		if (! class_exists('acf') ) { return false; }
		return true;
	}

	/**
	 * Class settings
	 *
	 * @author  Jake Snyder
	 * @since	1.1.0
	 * @return	void
	 */
	public function settings()
	{
		$this->prefix      = 'acf';
		$this->plugin_name = strtolower(__CLASS__);
		$this->version     = '1.1.0';
	}

	/**
	 * Initialize the Class
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function init()
	{
		// Add action to email the admin when a form has been submitted
		add_action( 'acf/save_post', array($this, 'notify'), 999 );
	}

	/**
	 * Send emails to the admin
	 *
	 * @author  Jake Snyder
	 * @since	0.1
	 * @return	void
	 */
	public function notify( $post_id )
	{
		// get the post
		$post = get_post($post_id);
		if (! is_object($post) ) { return; }
		$post_type = $post->post_type;

		// Make sure this isn't an ACF post update
		if ( 'acf' == $post_type ) { return; }

		// Make sure this is not in the admin, unless allowed for some reason...
		if ( (is_admin() && apply_filters( "{$this->prefix}/notify_admin/allow_admin", false )) || ! is_admin() )
		{
			// Test if notifications are on/off globally or on/off for post_type.
			if (! apply_filters( "{$this->prefix}/notify_admin/on/type={$post_type}", apply_filters( "{$this->prefix}/notify_admin/on", true ) ) ) { return; }

			// get the current user
			$current_user = wp_get_current_user();

			$fields = (! empty($_POST['fields']) ) ? $_POST['fields'] : array();

			if ( is_object($post) && $fields )
			{
				$admin_emails = array( get_option('admin_email') );
				$admin_emails = apply_filters( "{$this->prefix}/notify_admin/email/addresses/type={$post_type}", apply_filters( "{$this->prefix}/notify_admin/email/addresses", $admin_emails ) );

				$full_name = $current_user->first_name . ' ' . $current_user->last_name;
				$subject  = get_option('blogname') . ': Post Edited By ' . esc_html($full_name);
				$subject  = apply_filters( "{$this->prefix}/notify_admin/email/subject/type={$post_type}", apply_filters( "{$this->prefix}/notify_admin/email/subject", $subject, $post_id ) );

				$message  = 'A post was recently edited on ' . home_url() . ".\n\n";
				$message .= 'Date: ' . date_i18n(get_option('date_format')) . ', ' . date_i18n(get_option('time_format')) . ".\n\n";
				$message .= 'Post title: ' . get_the_title($post_id) . ".\n\n";
				$message .= "User who edited:\n";
					$message .= "\t Name: " . $full_name . ".\n";
					$message .= "\t Email: " . $current_user->user_login . ".\n";
					$message .= "\t Username: " . $current_user->user_email . ".\n\n";
				$message .= 'View post: ' . get_permalink($post_id) . ".\n";
				$message .= 'Edit post: ' . $this->get_edit_post_link($post_id, '') . ".\n\n";
				$message  = apply_filters( "{$this->prefix}/notify_admin/email/message/type={$post_type}", apply_filters( "{$this->prefix}/notify_admin/email/message", $message, $post_id ), $post_id );

				if ( is_array($admin_emails) ) {
					foreach ( $admin_emails as $to ) {
						wp_mail( $to, $subject, $message );
					}
				} elseif ( is_string($admin_emails) ) {
					wp_mail( $admin_emails, $subject, $message );
				}
			}
		}
	}

	/**
	 * get_edit_post_link even when current user doesn't officially have access to edit post.
	 *
	 * @author  Jake Snyder
	 * @since	0.1
	 * @return	string The link.
	 */
	public function get_edit_post_link( $id = 0, $context = 'display' )
	{
		if (! $post = get_post($id) ) { return; }

		if ( 'revision' === $post->post_type ) {
			$action = '';
		} elseif ( 'display' == $context ) {
			$action = '&amp;action=edit';
		} else {
			$action = '&action=edit';
		}
	
		$post_type_object = get_post_type_object( $post->post_type );
		if (! $post_type_object ) { return; }

		return apply_filters( 'get_edit_post_link', admin_url( sprintf($post_type_object->_edit_link . $action, $post->ID) ), $post->ID, $context );
	}
}

$$class_name = new $class_name;
$$class_name->run();
unset($class_name);

endif;