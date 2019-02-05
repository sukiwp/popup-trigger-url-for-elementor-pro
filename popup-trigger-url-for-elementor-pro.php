<?php
/*
Plugin Name: Popup Trigger URL for Elementor Pro
Plugin URI: http://wordpress.org/plugins/popup-trigger-url-for-elementor-pro
Description: Helps you to trigger Elementor Pro's popups (open, close, or toggle) from menus or any kind of link.
Version: 1.0.1
Author: Suki WordPress Theme
Author URI: https://sukiwp.com/#about
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
		if ( 'popup' === $_GET['elementor_library_type'] ) {
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
		if ( 'popup' === $_GET['elementor_library_type'] && 'link' === $column_name ) {
			$id = 'elementor-pro-popup-trigger-urls-' . $post_id;
			?>
			<a href="<?php echo esc_attr( '#TB_inline?width=600&height=300&inlineId=' . $id ); ?>" onclick="javascript:;" class="thickbox button button-secondary"><?php esc_html_e( 'Show URLs', 'popup-trigger-url-for-elementor-pro' ); ?></a>
			<div id="<?php echo esc_attr( $id ); ?>" style="display: none;">
				<div>
					<h4><?php esc_html_e( 'Choose the trigger type, copy the URL, and paste into your links.', 'popup-trigger-url-for-elementor-pro' ); ?></h4>
					<table class="widefat fixed striped">
						<thead>
							<tr>
								<th width="100px"><?php esc_html_e( 'Type', 'popup-trigger-url-for-elementor-pro' ); ?></th>
								<th width="100%"><?php esc_html_e( 'URL', 'popup-trigger-url-for-elementor-pro' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							$types = array(
								'open'   => esc_html__( 'Open', 'popup-trigger-url-for-elementor-pro' ),
								'close'  => esc_html__( 'Close', 'popup-trigger-url-for-elementor-pro' ),
								'toggle' => esc_html__( 'Toggle', 'popup-trigger-url-for-elementor-pro' ),
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
		$settings = array(
			'id'     => strval( $id ),
			'toggle' => 'toggle' === $action,
		);

		return '#' . rawurlencode( 'elementor-action:action=popup:' . ( 'close' === $action ? 'close' : 'open' ) . ' settings=' . base64_encode( wp_json_encode( $settings ) ) );
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