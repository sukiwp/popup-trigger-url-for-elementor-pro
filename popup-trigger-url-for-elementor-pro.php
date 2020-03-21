<?php
/*
Plugin Name: Popup Trigger URL for Elementor Pro
Plugin URI: http://wordpress.org/plugins/popup-trigger-url-for-elementor-pro
Description: Helps you to trigger Elementor Pro's popups (open, close, or toggle) from menus or any kind of link.
Version: 1.0.2
Author: Suki WordPress Theme
Author URI: https://sukiwp.com/
License: GNU General Public License v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: popup-trigger-url-for-elementor-pro
Tags: 
*/

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) exit;

class Popup_Trigger_URL_For_Elementor_Pro {
	/**
	 * Singleton instance
	 *
	 * @var Popup_Trigger_URL_For_Elementor_Pro
	 */
	private static $instance;

	/**
	 * Get singleton instance.
	 *
	 * @return Popup_Trigger_URL_For_Elementor_Pro
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Class constructor
	 */
	protected function __construct() {
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
		add_filter( 'manage_elementor_library_posts_columns', array( $this, 'manage_list_columns' ), 20 );
		add_action( 'manage_elementor_library_posts_custom_column', array( $this, 'manage_list_columns_content' ), 10, 2 );

		// add_action( 'admin_notices', array( $this, 'render_notice_elementor_2_9' ) );
		add_action( 'wp_ajax_popup-trigger-url-for-elementor-pro--dismiss-notice--elementor-2-9', array( $this, 'ajax_dismiss_notice_elementor_2_9' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Load plugin textdomain.
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'popup-trigger-url-for-elementor-pro' );
	}

	/**
	 * Add columns on posts list.
	 *
	 * @param array $columns
	 * @return array
	 */
	public function manage_list_columns( $columns ) {
		if ( isset( $_GET['elementor_library_type'] ) && 'popup' === $_GET['elementor_library_type'] ) {
			$columns['link'] = esc_html__( 'Trigger URLs', 'popup-trigger-url-for-elementor-pro' );
		}

		return $columns;
	}

	/**
	 * Add columns content on posts list.
	 *
	 * @param array $column_name
	 * @param integer $post_id
	 * @return array
	 */
	public function manage_list_columns_content( $column_name, $post_id ) {
		if ( isset( $_GET['elementor_library_type'] ) && 'popup' === $_GET['elementor_library_type'] && 'link' === $column_name ) {
			$id = 'elementor-pro-popup-trigger-urls-' . $post_id;
			?>
			<a href="<?php echo esc_attr( '#TB_inline?width=800&height=360&inlineId=' . $id ); ?>" onclick="javascript:;" class="thickbox button button-secondary"><?php esc_html_e( 'Show URLs', 'popup-trigger-url-for-elementor-pro' ); ?></a>
			<div id="<?php echo esc_attr( $id ); ?>" style="display: none;">
				<div>
					<h4><?php esc_html_e( 'Choose the trigger type, copy the URL, and paste into your links.', 'popup-trigger-url-for-elementor-pro' ); ?></h4>
					<table class="widefat fixed striped">
						<thead>
							<tr>
								<th width="175px"><?php esc_html_e( 'Type', 'popup-trigger-url-for-elementor-pro' ); ?></th>
								<th width="100%"><?php esc_html_e( 'URL', 'popup-trigger-url-for-elementor-pro' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							$types = array(
								'open'          => esc_html__( 'Open', 'popup-trigger-url-for-elementor-pro' ),
								'toggle'        => esc_html__( 'Toggle', 'popup-trigger-url-for-elementor-pro' ),
								'close'         => esc_html__( 'Close', 'popup-trigger-url-for-elementor-pro' ),
								'close-forever' => esc_html__( 'Close (Don\'t Show Again)', 'popup-trigger-url-for-elementor-pro' ),
							);

							foreach ( $types as $action => $label ) : ?>
								<tr>
									<th><?php echo ( $label ); // WPCS: XSS OK. ?></th>
									<td><input type="text" readonly value="<?php echo esc_attr( $this->generate_url( $action, $post_id ) ); ?>" class="widefat" onclick="this.select();document.execCommand('Copy');"></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
					<div class="notice inline notice-alt notice-warning" style="margin: 1em 0 0;">
						<p><?php esc_html_e( 'IMPORTANT: You are required to set the "Display Conditions" settings of your popup to pages where you want the popup to show. Otherwise, your popup won\'t show up.', 'popup-trigger-url-for-elementor-pro' ); ?></p>
					</div>
				</div>
			</div>
			<?php
		}
	}

	/**
	 * Generate trigger URL based on the popup ID and the trigger type.
	 *
	 * @param string $action
	 * @param integer $post_id
	 * @return string
	 */
	public function generate_url( $action, $id ) {
		$url = '';

		// Generate the URL based on its action using the native Elementor's function.
		switch ( $action ) {
			case 'close':
			case 'close-forever':
				$url = \Elementor\Plugin::instance()->frontend->create_action_hash(
					'popup:close',
					array(
						'do_not_show_again' => 'close-forever' === $action ? 'yes' : '',
					)
				);
				break;
			
			case 'open':
			case 'toggle':
			default:
				$url = \Elementor\Plugin::instance()->frontend->create_action_hash(
					'popup:open',
					array(
						'id'     => strval( $id ),
						'toggle' => 'toggle' === $action,
					)
				);
				break;
		}

		// Revert back the encoded "%23" to "#" to prevent WordPress automatically adding "http://" prefix in the URL.
		// This also works as a fallback compatibility for the old version.
		$url = str_replace( '%23', '#', $url );

		return $url;
	}

	/**
	 * Render admin notice to remind users to update their manual trigger links since the new Elementor 2.9.
	 */
	public function render_notice_elementor_2_9() {
		// Show notice to users that have "edit_posts" capability.
		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}

		// Do not show the notice if Elementor version is less than 2.9.0.
		if ( ! defined( 'ELEMENTOR_VERSION' ) || version_compare( ELEMENTOR_VERSION, '2.9.0', '<' ) ) {
			return;
		}

		// Do not show the notice if notice has been dismissed before.
		if ( 1 === intval( get_option( 'popup_trigger_url_for_elementor_pro__dismiss_notice__elementor_2_9' ) ) ) {
			return;
		}
		?>
		<div id="popup-trigger-url-for-elementor-pro--notice--elementor-2-9" class="notice notice-warning is-dismissible">
			<p><span class="dashicons dashicons-warning"></span>&nbsp;&nbsp;<strong><?php esc_html_e( 'Message from "Popup Trigger URL for Elementor Pro" plugin:', 'suki' ); ?></strong></p>
			<p><?php esc_html_e( 'Since Elementor 2.9, there are some changes to the way Elementor generates the trigger URLs. This caused all your previously copied trigger URLs might not work anymore. Please review all your links. If it doesn\'t work, you can re-copy the new trigger URLs and then paste it again on your links.', 'popup-trigger-url-for-elementor-pro' ); ?></p>
		</div>
		<script type="text/javascript">
			(function( $ ) {
				'use strict';

				$( document ).on( 'click', '#popup-trigger-url-for-elementor-pro--notice--elementor-2-9 .notice-dismiss', function( e ) {
					e.preventDefault();

					return $.ajax({
						method: 'POST',
						url: ajaxurl,
						data: {
							action: 'popup-trigger-url-for-elementor-pro--dismiss-notice--elementor-2-9',
						},
					});
				});
			})( jQuery );
		</script>
		<?php
	}

	/**
	 * AJAX callback to dismiss Elementor 2.9 notice forever.
	 */
	public function ajax_dismiss_notice_elementor_2_9() {
		update_option( 'popup_trigger_url_for_elementor_pro__dismiss_notice__elementor_2_9', 1 );
		wp_die();
	}

	/**
	 * Add inline javascript for handling the old trigger URLs.
	 */
	public function enqueue_scripts() {
		ob_start();
		?>
		(function() {
			elementorFrontend.elements.$document.on( 'click', 'a[href^="#elementor-action"]', function( e ) {
				e.preventDefault();
				elementorFrontend.utils.urlActions.runAction( jQuery( e.currentTarget ).attr( 'href' ), e );
			});
		})();
		<?php
		$js = ob_get_clean();

		wp_add_inline_script( 'elementor-frontend', $js );
	}
}

/**
 * Initiate this plugin.
 */
function popup_trigger_url_for_elementor_pro() {
	// Only initiate when Elementor Pro plugin is active.
	if ( class_exists( 'ElementorPro\Plugin' ) ) {
		Popup_Trigger_URL_For_Elementor_Pro::instance();
	}
}
add_action( 'plugins_loaded', 'popup_trigger_url_for_elementor_pro' );