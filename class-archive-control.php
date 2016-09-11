<?php
/**
 * Archive Control
 *
 * @package   Archive_Control
 * @author    Jesse Sutherland
 * @license   GPL-2.0+
 * @link      http://switchthemes.com
 * @copyright 2016 Jesse Sutherland
 */

/**
 * Plugin class.
 *
 * @package Archive_Control
 * @author  Jesse Sutherland
 */
class Archive_Control {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @const   string
	 */
	const VERSION = '1.0.0';

	/**
	 * Unique identifier for your plugin.
	 *
	 * Use this value (not the variable name) as the text domain when internationalizing strings of text. It should
	 * match the Text Domain file header in the main plugin file.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'archive-control';

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		// Load plugin text domain
		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );

		add_action( 'pre_get_posts', array( $this, 'archive_control_modify_archive_query' ), 1 );

		add_action( 'loop_start', array( $this, 'archive_control_loop_start' ) );
		add_action( 'loop_end', array( $this, 'archive_control_loop_end' ) );



		add_action( 'admin_menu', array( $this, 'archive_control_menu' ) );

		add_action( 'admin_init', array( $this, 'archive_control_settings' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'archive_control_custom_admin_style_scripts' ) );

		add_filter( 'get_the_archive_title', array( $this, 'archive_control_title_filter') );

	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog.
	 */
	public static function activate( $network_wide ) {

	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses "Network Deactivate" action, false if WPMU is disabled or plugin is deactivated on an individual blog.
	 */
	public static function deactivate( $network_wide ) {

	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.2
	 */
	public function load_plugin_textdomain() {

		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}


	/**
	* NOTE:  Actions are points in the execution of a page or process
	*        lifecycle that WordPress fires.
	*
	*        WordPress Actions: http://codex.wordpress.org/Plugin_API#Actions
	*        Action Reference:  http://codex.wordpress.org/Plugin_API/Action_Reference
	*
	* @since    1.0.0
	*/

	public function archive_control_custom_admin_style_scripts() {
		wp_register_style( 'archive_control_admin_css', plugin_dir_url( __FILE__ ) . '/css/admin-style.css', false, '1.0' );
		wp_enqueue_style( 'archive_control_admin_css' );
		wp_register_script( 'archive_control_admin_js', plugin_dir_url( __FILE__ ) . '/js/admin-scripts.js', 'jquery', '1.0', true );
		wp_enqueue_script( 'archive_control_admin_js' );
	}

	public function archive_control_menu() {
		add_options_page(
			'Archive Control',
			'Archive Control',
			'manage_options',
			'archive-control.php',
			array($this,'archive_control_options')
		);
		$archive_control_options = get_option('archive_control_options');
		foreach($archive_control_options as $post_type => $options) {
			if ($options['archive-title'] == 1 || $options['before'] == 1 || $options['after'] == 1) {
				if ($post_type == 'post') {
					$parent_slug = 'edit.php';
				} else {
					$parent_slug = 'edit.php?post_type=' . $post_type;
				}
				add_submenu_page(
					$parent_slug,
					'Edit Archive Page',
					'Edit Archive Page',
					'edit_posts',
					'edit-archive-' . $post_type,
					array($this,'archive_control_edit_page_callback')
				);
			} //has before or after value
		}
	}

	public function archive_control_settings() {
		register_setting( 'archive-control-options-group', 'archive_control_options' );
		$archive_control_options = get_option('archive_control_options');
		foreach($archive_control_options as $post_type => $options) {
			register_setting( 'archive-control-' . $post_type . '-group', 'archive_control_' . $post_type . '_title' );
			register_setting( 'archive-control-' . $post_type . '-group', 'archive_control_' . $post_type . '_before' );
			register_setting( 'archive-control-' . $post_type . '-group', 'archive_control_' . $post_type . '_after' );
		}
	}

	public function archive_control_edit_page_callback() {
		$screen = get_current_screen();
		$current_post_type = $screen->post_type;
		$archive_control_options = get_option('archive_control_options');
		$archive_control_cpt_title = get_option('archive_control_' . $current_post_type . '_title');
		$archive_control_cpt_before = get_option('archive_control_' . $current_post_type . '_before');
		$archive_control_cpt_after = get_option('archive_control_' . $current_post_type . '_after');
		if ($screen->post_type == '' && $screen->parent_file == 'edit.php') {
			$current_post_type = 'post';
		}
		$current_post_type_object = get_post_type_object($current_post_type);
		$current_post_type_options = $archive_control_options[$current_post_type];
		?>
		<div id="archive-control-edit-page" class="wrap">
			<h1>Edit <?php echo $current_post_type_object->label; ?> Archive Page</h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'archive-control-' . $current_post_type . '-group' );
				do_settings_sections( 'archive-control-' . $current_post_type . '-group' );
				?>
				<div id="poststuff">
					<div id="post-body" class="metabox-holder columns-2">
						<div id="postbox-container-1" class="postbox-container">
							<div id="submitdiv" class="postbox ">
								<h2 class="hndle"><span>Publish</span></h2>
								<div class="inside">
									<div id="major-publishing-actions">
										<div id="publishing-action">
											<?php submit_button(); ?>
										</div><!-- #publishing-action -->
										<div class="clear"></div>
									</div><!-- #major-publishing-actions -->
								</div><!-- .inside -->
							</div><!-- #submitdiv -->
						</div><!-- .postbox-container -->
						<div id="postbox-container-2" class="postbox-container">
							<?php if ($current_post_type_options['title'] !== 0 && $current_post_type_options['title'] !== null) : ?>
								<div id="titlediv">
									<div id="titlewrap">
										<label class="screen-reader-text" id="title-prompt-text" for="title">Enter archive title here</label>
										<input type="text" name="archive_control_<?php echo $current_post_type; ?>_title" size="30" value="<?php echo $archive_control_cpt_title; ?>" id="title" spellcheck="true" autocomplete="off">
									</div>
								</div>
							<?php endif; ?>
							<?php if ($current_post_type_options['before'] !== 0 && $current_post_type_options['before'] !== null) : ?>
								<div id="archive-control-before">
									<h2><span>Before Archive Loop</span></h2>
									<div class="inside">
										<?php $settings = array(
											textarea_name => 'archive_control_' . $current_post_type . '_before',
											textarea_rows => 10
										);?>
										<?php wp_editor( $archive_control_cpt_before, 'before-archive', $settings ); ?>
									</div><!-- .inside -->
								</div><!-- #archive-control-before -->
							<?php endif; ?>
							<?php if ($current_post_type_options['after'] !== 0 && $current_post_type_options['after'] !== null) : ?>
								<div id="archive-control-after">
									<h2><span>After Archive Loop</span></h2>
									<div class="inside">
										<?php $settings = array(
											textarea_name => 'archive_control_' . $current_post_type . '_after',
											textarea_rows => 10
										);?>
										<?php wp_editor( $archive_control_cpt_after, 'after-archive', $settings ); ?>
									</div><!-- .inside -->
								</div><!-- #archive-control-before -->
							<?php endif; ?>
						</div><!-- .postbox-container -->
					</div><!-- #post-body -->
					<br class="clear">
				</div><!-- #poststuff -->
			</form>
	    </div>
		<?php
	}

	public function archive_control_options(){
		?>
		<div id="archive-control-options" class="wrap">
			<h1>Archive Control Options</h1>
			<p>You can select options for each post type:</p>
			<form method="post" action="options.php">
				<?php
					settings_fields( 'archive-control-options-group' );
					do_settings_sections( 'archive-control-options-group' );
				?>
				<div id="poststuff">
					<div id="post-body" class="metabox-holder columns-2">
						<div id="postbox-container-1" class="postbox-container">
							<div id="submitdiv" class="postbox ">
								<h2 class="hndle"><span>Publish</span></h2>
								<div class="inside">
									<div id="major-publishing-actions">
										<div id="publishing-action">
											<?php submit_button(); ?>
										</div><!-- #publishing-action -->
										<div class="clear"></div>
									</div><!-- #major-publishing-actions -->
								</div><!-- .inside -->
							</div><!-- #submitdiv -->
						</div><!-- .postbox-container -->
						<div id="postbox-container-2" class="postbox-container">
							<?php
							$args = array(
								'public'   => true
							);
							$post_types = get_post_types($args, 'objects' );
							$archive_control_options = get_option('archive_control_options');
							foreach ($post_types as $post_type ) {
								if($post_type->name !== 'page' && $post_type->name !== 'attachment' && $post_type->name !== 'revision' && $post_type->name !== 'nav_menu_item') {
								?>
								<div id="needid" class="postbox">
									<h2 class="hndle ui-sortable-handle"><span><?php echo $post_type->label; ?></span></h2>
									<div class="inside">
										<table class="form-table">
											<tr valign="top">
									        	<th scope="row">Archive Title</th>
										        <td>
													<label><input type="checkbox" name="archive_control_options[<?php echo $post_type->name; ?>][title]" value="1"<?php if ($archive_control_options[$post_type->name]['title'] == 1) { echo " checked='checked'"; } ?> /> Enable Archive Title Override</label>
												</td>
											</tr>
											<tr valign="top">
												<th scope="row">Before Archive</th>
										        <td>
													<label><input type="checkbox" name="archive_control_options[<?php echo $post_type->name; ?>][before]" value="1"<?php if ($archive_control_options[$post_type->name]['before'] == 1) { echo " checked='checked'"; } ?> /> Enable Textarea Before Archive Loop</label>
												</td>
											</tr>
											<tr valign="top">
												<th scope="row">After Archive</th>
										        <td>
													<label><input type="checkbox" name="archive_control_options[<?php echo $post_type->name; ?>][after]" value="1"<?php if ($archive_control_options[$post_type->name]['after'] == 1) { echo " checked='checked'"; } ?> /> Enable Textarea After Archive Loop</label>
												</td>
											</tr>
											<tr valign="top">
												<th scope="row">Order By</th>
										        <td>
													<select name="archive_control_options[<?php echo $post_type->name; ?>][orderby]">
														<option value="default"<?php if ($archive_control_options[$post_type->name]['orderby'] == null) { echo "selected='selected'"; } ?>>Do not modify</option>
														<option value="date"<?php if ($archive_control_options[$post_type->name]['orderby'] == 'date') { echo "selected='selected'"; } ?>>Date Published</option>
														<option value="title"<?php if ($archive_control_options[$post_type->name]['orderby'] == 'title') { echo "selected='selected'"; } ?>>Title</option>
														<option value="modified"<?php if ($archive_control_options[$post_type->name]['orderby'] == 'modified') { echo "selected='selected'"; } ?>>Date Modified</option>
														<option value="menu_order"<?php if ($archive_control_options[$post_type->name]['orderby'] == 'menu_order') { echo "selected='selected'"; } ?>>Menu Order</option>
														<option value="rand"<?php if ($archive_control_options[$post_type->name]['orderby'] == 'rand') { echo "selected='selected'"; } ?>>Random</option>
														<option value="ID"<?php if ($archive_control_options[$post_type->name]['orderby'] == 'ID') { echo "selected='selected'"; } ?>>ID</option>
														<option value="author"<?php if ($archive_control_options[$post_type->name]['orderby'] == 'author') { echo "selected='selected'"; } ?>>Author</option>
														<option value="name"<?php if ($archive_control_options[$post_type->name]['orderby'] == 'name') { echo "selected='selected'"; } ?>>Post Slug</option>
														<option value="type"<?php if ($archive_control_options[$post_type->name]['orderby'] == 'type') { echo "selected='selected'"; } ?>>Post Type</option>
														<option value="comment_count"<?php if ($archive_control_options[$post_type->name]['orderby'] == 'comment_count') { echo "selected='selected'"; } ?>>Comment Count</option>
														<option value="parent"<?php if ($archive_control_options[$post_type->name]['orderby'] == 'parent') { echo "selected='selected'"; } ?>>Parent</option>
														<option value="meta_value"<?php if ($archive_control_options[$post_type->name]['orderby'] == 'meta_value') { echo "selected='selected'"; } ?>>Meta Value</option>
														<option value="meta_value_num"<?php if ($archive_control_options[$post_type->name]['orderby'] == 'meta_value_num') { echo "selected='selected'"; } ?>>Meta Value (Numeric)</option>
														<option value="none"<?php if ($archive_control_options[$post_type->name]['orderby'] == 'none') { echo "selected='selected'"; } ?>>No Order</option>
													</select>
												</td>
											</tr>
											<tr valign="top">
												<th scope="row"><label>Meta Key</label></th>
										        <td>
													<input type="text" name="archive_control_options[<?php echo $post_type->name; ?>][meta_key]" value="<?php echo $archive_control_options[$post_type->name]['meta_key']; ?>"/>
												</td>
											</tr>
											<tr valign="top">
												<th scope="row"><label>Order</label></th>
										        <td>
													<select name="archive_control_options[<?php echo $post_type->name; ?>][order]">
														<option value="default"<?php if ($archive_control_options[$post_type->name]['orderby'] == null) { echo "selected='selected'"; } ?>>Do not modify</option>
														<option value="asc"<?php if ($archive_control_options[$post_type->name]['order'] == 'asc') { echo "selected='selected'"; } ?>>Ascending</option>
														<option value="desc"<?php if ($archive_control_options[$post_type->name]['order'] == 'desc') { echo "selected='selected'"; } ?>>Descending</option>
													</select>
												</td>
											</tr>
											<tr valign="top">
												<th scope="row"><label>Pagination</label></th>
										        <td>
													<input type="text" name="archive_control_options[<?php echo $post_type->name; ?>][posts_per_page]" value="<?php echo $archive_control_options[$post_type->name]['posts_per_page']; ?>"/>
												</td>
									        </tr>
										</table>
									</div><!-- .inside -->
								</div><!-- .postbox -->
								<?php
								}
							}
						?>



		</form>
		</div>
		<?php
	}

	public function archive_control_modify_archive_query( $query ) {
		if( $query->is_main_query() ){
			if (is_archive()) {
				$archive_control_options = get_option('archive_control_options');
				foreach($archive_control_options as $post_type => $options) {
					if (is_post_type_archive($post_type)) {
						if ($options['order'] !== 'default' && $options['order'] !== null) {
							$query->set( 'order', $options['order'] );
						} //has order value
						if ($options['orderby'] !== 'default' && $options['orderby'] !== null) {
							$query->set( 'orderby', $options['orderby'] );
							if ($options['orderby'] == 'meta_value' || $options['orderby'] == 'meta_value_num') {
								if ($options['meta_key'] !== null) {
									$query->set( 'meta_key', $options['meta_key'] );
								} //has meta_key value
							} //meta_key value is needed
						} //has orderby value
						if ($options['posts_per_page'] !== 'default' && $options['posts_per_page'] !== null) {
							$query->set( 'posts_per_page', $options['posts_per_page'] );
						} //has orderby value
					} //is_post_type_artchive
				} //foreach
			} // is_archive
		} //is_main_query
	    return;
	}

	public function archive_control_loop_start( $query ){
		if( $query->is_main_query() ){
			if (is_archive()) {
				$archive_control_options = get_option('archive_control_options');
				foreach($archive_control_options as $post_type => $options) {
					if (is_post_type_archive($post_type)) {
						if ($options['before'] !== 0 && $options['before'] !== null) {
							$archive_control_cpt_before = get_option('archive_control_' . $post_type . '_before');
							if ($archive_control_cpt_before) {
								echo "<div class='archive-control-area archive-control-before'>";
									echo apply_filters( 'the_content', $archive_control_cpt_before );
								echo "</div>";
							}
						} //has before value
					} //is_post_type_artchive
				} //foreach
			} // is_archive
		} //is_main_query
	}

	public function archive_control_loop_end( $query ){
		if( $query->is_main_query() ){
			if (is_archive()) {
				$archive_control_options = get_option('archive_control_options');
				foreach($archive_control_options as $post_type => $options) {
					if (is_post_type_archive($post_type)) {
						if ($options['after'] !== 0 && $options['after'] !== null) {
							$archive_control_cpt_after = get_option('archive_control_' . $post_type . '_after');
							if ($archive_control_cpt_after) {
								echo "<div class='archive-control-area archive-control-after'>";
									echo apply_filters( 'the_content', $archive_control_cpt_after );
								echo "</div>";
							}
						} //has after value
					} //is_post_type_artchive
				} //foreach
			} // is_archive
		} //is_main_query
	}

	public function archive_control_title_filter($title) {
		if (is_archive()) {
			$archive_control_options = get_option('archive_control_options');
			foreach($archive_control_options as $post_type => $options) {
				if (is_post_type_archive($post_type)) {
					if ($options['title'] !== 0 && $options['title'] !== null) {
						$archive_control_cpt_title = get_option('archive_control_' . $post_type . '_title');
						if ($archive_control_cpt_title) {
							$title = $archive_control_cpt_title;
						}
					} //has before value
				} //is_post_type_artchive
			} //foreach
		} // is_archive
		return $title;
	}

}
