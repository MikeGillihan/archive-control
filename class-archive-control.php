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
	const VERSION = '1.3.1';

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
	private function __construct()
	{

		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );
		add_filter( 'plugin_action_links_archive-control/archive-control.php', array( $this, 'archive_control_action_link' ) );
		add_action( 'admin_bar_menu', array( $this, 'archive_control_archive_edit_link'), 81 );
		add_action( 'pre_get_posts', array( $this, 'archive_control_modify_archive_query' ), 1 );
		add_action( 'loop_start', array( $this, 'archive_control_loop_start_image' ), 2 );
		add_action( 'loop_start', array( $this, 'archive_control_loop_start_content' ), 6 );
		add_action( 'loop_end', array( $this, 'archive_control_loop_end_content' ) );
		add_action( 'admin_menu', array( $this, 'archive_control_menu' ) );
		add_action( 'admin_init', array( $this, 'archive_control_settings' ) );
		add_action( 'admin_head', array( $this, 'archive_control_remove_parent_field_from_post_taxonomy' ) );
		add_action( 'init', array( $this, 'archive_control_handle_taxonomy_fields' ) );
		add_action( 'init', array( $this, 'archive_control_handle_updates' ), 999 );
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
	public static function get_instance()
	{

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
	public static function activate( $network_wide )
	{

	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses "Network Deactivate" action, false if WPMU is disabled or plugin is deactivated on an individual blog.
	 */
	public static function deactivate( $network_wide )
	{

	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain()
	{

		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );

	}

	/**
	 * Handle database changes for upgrades
	 *
	 * @since    1.2.0
	 */
	public function archive_control_handle_updates()
	{
		include_once('db/update.php' );
	}

	/**
	 * Load up custom css and js used in the admin panels for this plugin
	 *
	 * @since    1.0.0
	 */
	public function archive_control_custom_admin_style_scripts()
	{
		$load_archive_control_scripts = false;
		if (isset($_GET['page'])) {
			if ($_GET['page'] == 'archive-control' || strpos($_GET['page'], 'edit-archive-') !== false) {
				$load_archive_control_scripts = true;
			}
		}
		if (isset($_GET['taxonomy'])) {
			$load_archive_control_scripts = true;
		}
		if ($load_archive_control_scripts == true) {
			if ( ! did_action( 'wp_enqueue_media' ) ) {
				wp_enqueue_media();
			}
			wp_register_style( 'archive_control_admin_css', plugin_dir_url( __FILE__ ) . '/css/admin-style.css', false, self::VERSION );
			wp_enqueue_style( 'archive_control_admin_css' );
			wp_register_script( 'archive_control_admin_js', plugin_dir_url( __FILE__ ) . '/js/admin-scripts.js', 'jquery', self::VERSION, true );
			$js_translations = array(
				'media_upload_title_text' => __( 'Archive Featured Image', 'archive-control' ),
				'media_upload_button_text' => __( 'Set featured image', 'archive-control' )
			);
			wp_localize_script( 'archive_control_admin_js', 'archive_control', $js_translations );
			wp_enqueue_script( 'archive_control_admin_js' );
		}
	}

	/**
	 * Used to insert a "Settings" link on the Plugin activation screen
	 *
	 * @since    1.0.0
	 *
	 * @param    array    $links    The array of existing links for the plugin
	 */
	public function archive_control_action_link( $links )
	{
		$mylinks = array(
			'<a href="' . admin_url( 'options-general.php?page=archive-control' ) . '">' . __('Settings', 'archive-control') . '</a>',
		);
		return array_merge( $links, $mylinks );
	}

	/**
	 * Activate the settings screens for the plugin
	 *
	 * @since    1.0.0
	 */
	public function archive_control_menu()
	{
		add_options_page(
			__('Archive Control', 'archive-control'),
			__('Archive Control', 'archive-control'),
			'manage_options',
			'archive-control',
			array($this,'archive_control_options')
		);
		$custom_post_types = $this->archive_control_get_cpts();
		if (!empty($custom_post_types)) {
			foreach($custom_post_types as $post_type) {
				$options = get_option('archive_control_cpt_' . $post_type->name . '_options');
				if ($options) {
					$title_val = isset($options['title']) ? $options['title'] : null;
					$image_val = isset($options['image']) ? $options['image'] : null;
					$before_val = isset($options['before']) ? $options['before'] : null;
					$after_val = isset($options['after']) ? $options['after'] : null;
					if ($title_val == 'custom' || $image_val == 'enabled' || $before_val == 'textarea' || $after_val == 'textarea') {
						if ($post_type == 'post') {
							$parent_slug = 'edit.php';
						} else {
							$parent_slug = 'edit.php?post_type=' . $post_type->name;
						}
						add_submenu_page(
							$parent_slug,
							__('Edit Archive Page', 'archive-control'),
							__('Edit Archive Page', 'archive-control'),
							'edit_posts',
							'edit-archive-' . $post_type->name,
							array($this,'archive_control_edit_page_callback')
						);
					} //has title, image, before or after value
				} //has options
			} //for each post type
		} // if not empty
	}

	/**
	 * Register the necessary settings to store for plugin use.
	 *
	 * @since    1.0.0
	 */
	public function archive_control_settings()
	{
		register_setting( 'archive-control-tax-options-group', 'archive_control_tax_category_options' );
		register_setting( 'archive-control-tax-options-group', 'archive_control_tax_post_tag_options' );
		$custom_post_types = $this->archive_control_get_cpts();
		if (!empty($custom_post_types)) {
			foreach($custom_post_types as $post_type) {
				register_setting( 'archive-control-cpt-options-group', 'archive_control_cpt_' . $post_type->name . '_options' );
				register_setting( 'archive-control-' . $post_type->name . '-group', 'archive_control_cpt_' . $post_type->name . '_title' );
				register_setting( 'archive-control-' . $post_type->name . '-group', 'archive_control_cpt_' . $post_type->name . '_image' );
				register_setting( 'archive-control-' . $post_type->name . '-group', 'archive_control_cpt_' . $post_type->name . '_before' );
				register_setting( 'archive-control-' . $post_type->name . '-group', 'archive_control_cpt_' . $post_type->name . '_after' );
			}
		}
		$custom_taxonomies = $this->archive_control_get_taxes();
		if (!empty($custom_taxonomies)) {
			foreach($custom_taxonomies as $taxonomy) {
				register_setting( 'archive-control-tax-options-group', 'archive_control_tax_' . $taxonomy->name . '_options' );
			}
		}
	}

	/**
	 * Add an Edit link to the WP Admin Toolbar
	 *
	 * @since    1.1.1
	 */
	public function archive_control_archive_edit_link($wp_admin_bar) {
		if (is_post_type_archive() && !is_admin()) {
			$post_type = get_query_var('post_type', null);
			$options = get_option('archive_control_cpt_' . $post_type . '_options');
			$edit_url = get_admin_url() . 'edit.php?post_type=' . $post_type . '&page=edit-archive-' . $post_type;
			$args = array(
				'id' => 'edit',
				'title' => __('Edit Archive Page', 'archive-control'),
				'href' => $edit_url,
				'meta' => array(
					'class' => 'ab-item',
					'title' => __('Edit Custom Post Type Archive', 'archive-control')
					)
			);
			$wp_admin_bar->add_node($args);
		}
	}

	/**
	 * Get only the custom post types that we want
	 *
	 * @since    1.2.0
	 */
	public function archive_control_get_cpts()
	{
		$custom_post_types = array();
		$args = array(
			'public'   => true,
			'_builtin' => false
		);
		$post_types = get_post_types($args, 'objects' );
		foreach ($post_types as $post_type ) {
			if($post_type->has_archive) {
				$custom_post_types[$post_type->name] = $post_type;
			}
		}
		return $custom_post_types;
	}

	/**
	 * Get only the custom post types that we want
	 *
	 * @since    1.2.0
	 */
	public function archive_control_get_taxes($with_cats_tags = false)
	{
		$custom_taxonomies = array();
		$args = array(
			'public'   => true,
  			'_builtin' => false
		);
		$custom_taxonomies = get_taxonomies($args, 'objects' );
		if ($with_cats_tags === true) {
			$default_taxonomies = array();
			$category_tax_object = get_taxonomy( 'category' );
			$default_taxonomies['category'] = $category_tax_object;
			$post_tag_tax_object = get_taxonomy( 'post_tag' );
			$default_taxonomies['post_tag'] = $post_tag_tax_object;
			$custom_taxonomies = array_merge($default_taxonomies,$custom_taxonomies);
		}
		return $custom_taxonomies;
	}

	/**
	 * Add actions for each custom taxonomy
	 *
	 * @since    1.3.0
	 */
	public function archive_control_handle_taxonomy_fields()
	{
		$custom_taxonomies = $this->archive_control_get_taxes(true);
		if (!empty($custom_taxonomies)) {
			foreach($custom_taxonomies as $taxonomy) {
				$options = get_option('archive_control_tax_' . $taxonomy->name . '_options');
				$image_val = isset($options['image']) ? $options['image'] : null;
				$before_val = isset($options['before']) ? $options['before'] : null;
				$after_val = isset($options['after']) ? $options['after'] : null;
				$term_order_pagination_val = isset($options['term_order_pagination']) ? $options['term_order_pagination'] : null;
				if ($image_val == 'enabled' || $before_val == 'textarea' || $after_val == 'textarea' || $term_order_pagination_val == 'enabled') {
					add_action( $taxonomy->name . '_add_form_fields', array( $this, 'archive_control_add_taxonomy_fields'), 10, 2 );
					add_action( $taxonomy->name . '_edit_form_fields', array( $this, 'archive_control_edit_taxonomy_fields'), 10, 2 );
					add_action( 'edited_' . $taxonomy->name, array( $this, 'archive_control_edit_taxonomy_meta'), 10, 2 );
				}
				$hide_description = isset($options['hide_description']) ? $options['hide_description'] : null;
				if ($hide_description == 'hidden') {
					add_filter( 'manage_edit-' .  $taxonomy->name . '_columns', array( $this, 'archive_control_remove_taxonomy_description_column' ) );
				}
			}
		}
	}

	/**
	 * Remove the taxonomy description column if set
	 *
	 * @since    1.3.0
	 */
	public function archive_control_remove_taxonomy_description_column($columns)
	{
		if( isset( $columns['description'] ) ) unset( $columns['description'] );
    	return $columns;
	}

	/**
	 * Add term meta when a term is saved
	 *
	 * @since    1.3.0
	 */
	public function archive_control_edit_taxonomy_meta( $term_id, $tt_id ){
		if( isset( $_POST['archive_control_term_meta']['image'] ) && $_POST['archive_control_term_meta']['image'] !==  '' ){
			if (is_numeric($_POST['archive_control_term_meta']['image']) && is_object( get_post( $_POST['archive_control_term_meta']['image'] ))) {
				$archive_control_term_image = $_POST['archive_control_term_meta']['image'];
		        update_term_meta( $term_id, 'archive_control_term_image', $archive_control_term_image );
			}
	    } else {
			delete_term_meta( $term_id, 'archive_control_term_image' );
		}
		if( isset( $_POST['archive_control_term_meta']['before'] ) && $_POST['archive_control_term_meta']['before'] !==  '' ){
	        $archive_control_term_before = wp_kses_post( $_POST['archive_control_term_meta']['before'] );
	        update_term_meta( $term_id, 'archive_control_term_before', $archive_control_term_before );
	    } else {
			delete_term_meta( $term_id, 'archive_control_term_before' );
		}
		if( isset( $_POST['archive_control_term_meta']['after'] ) && $_POST['archive_control_term_meta']['after'] !==  '' ){
	        $archive_control_term_after = wp_kses_post( $_POST['archive_control_term_meta']['after'] );
	        update_term_meta( $term_id, 'archive_control_term_after', $archive_control_term_after );
	    } else {
			delete_term_meta( $term_id, 'archive_control_term_after' );
		}

		$archive_control_term_meta = array();
		if( isset( $_POST['archive_control_term_meta']['orderby'] ) && $_POST['archive_control_term_meta']['orderby'] !==  '' ){
	        $archive_control_term_meta['orderby'] = sanitize_text_field( $_POST['archive_control_term_meta']['orderby'] );
	    }
		if( isset( $_POST['archive_control_term_meta']['meta_key'] ) && $_POST['archive_control_term_meta']['meta_key'] !==  '' ){
	        $archive_control_term_meta['meta_key'] = sanitize_text_field( $_POST['archive_control_term_meta']['meta_key'] );
	    }
		if( isset( $_POST['archive_control_term_meta']['order'] ) && $_POST['archive_control_term_meta']['order'] !==  '' ){
	        $archive_control_term_meta['order'] = sanitize_text_field( $_POST['archive_control_term_meta']['order'] );
	    }
		if( isset( $_POST['archive_control_term_meta']['pagination'] ) && $_POST['archive_control_term_meta']['pagination'] !==  '' ){
	        $archive_control_term_meta['pagination'] = sanitize_text_field( $_POST['archive_control_term_meta']['pagination'] );
	    }
		if( isset( $_POST['archive_control_term_meta']['posts_per_page'] ) && $_POST['archive_control_term_meta']['posts_per_page'] !==  '' ){
	        $archive_control_term_meta['posts_per_page'] = sanitize_text_field( $_POST['archive_control_term_meta']['posts_per_page'] );
	    }
		if (!empty($archive_control_term_meta)) {
			update_term_meta( $term_id, 'archive_control_term_meta', $archive_control_term_meta );
		}
	}

	/**
	 * Hide fields from the add taxonomy screen
	 *
	 * @since    1.3.0
	 */
	public function archive_control_add_taxonomy_fields($taxonomy) {
		$options = get_option('archive_control_tax_' . $taxonomy . '_options');
		if ($options) {
			$hide_parent_val = isset($options['hide_parent']) ? $options['hide_parent'] : null;
			if ($hide_parent_val == 'hidden') {
				?><style>#addtag .term-parent-wrap {display:none;}</style><?php
			}
			$hide_description_val = isset($options['hide_description']) ? $options['hide_description'] : null;
			if ($hide_description_val == 'hidden') {
				?><style>#addtag .term-description-wrap {display:none;}</style><?php
			}
		}
	}

	/**
	 * Hide the "Parent" field when adding a term inline (if option is set)
	 *
	 * @since    1.3.0
	 */
	public function archive_control_remove_parent_field_from_post_taxonomy()
	{
		$screen = get_current_screen();
		if ($screen->base == 'post') {
			$custom_taxonomies = $this->archive_control_get_taxes(true);
			if (!empty($custom_taxonomies)) {
				foreach($custom_taxonomies as $taxonomy) {
					$options = get_option('archive_control_tax_' . $taxonomy->name . '_options');
					$hide_parent = isset($options['hide_parent']) ? $options['hide_parent'] : null;
					if ($hide_parent == 'hidden') {
						echo "<style>#new" . $taxonomy->name . "_parent {display:none;}</style>";
					}
				}
			}
		}
	}

	/**
	 * Add custom fields to the edit taxonomy screen
	 *
	 * @since    1.3.0
	 */
	public function archive_control_edit_taxonomy_fields($term, $taxonomy) {
		$options = get_option('archive_control_tax_' . $taxonomy . '_options');
		if ($options) {
			$hide_parent_val = isset($options['hide_parent']) ? $options['hide_parent'] : null;
			if ($hide_parent_val == 'hidden') {
				?><style>.term-parent-wrap {display:none;}</style><?php
			}
			$hide_description_val = isset($options['hide_description']) ? $options['hide_description'] : null;
			if ($hide_description_val == 'hidden') {
				?><style>.term-description-wrap {display:none;}</style><?php
			}
			$image_val = isset($options['image']) ? $options['image'] : null;
			if ($image_val == 'enabled') {
				?><tr class="form-field term-group-wrap">
		        <th scope="row"><label for="feature-group"><?php _e( 'Featured Image', 'archive-control' ); ?></label></th>
		        <td id="featured-image-archive">
					<div class="taxonomy-image-area">
						<?php
						$upload_link = esc_url( get_upload_iframe_src( 'image' ) );
						$archive_control_term_image = get_term_meta( $term->term_id, 'archive_control_term_image', true );
						$featured_img_src = wp_get_attachment_image_src( $archive_control_term_image, 'full' );
						$featured_img = is_array( $featured_img_src );
						?>
						<div class="featured-image-archive-container taxonomy-width">
							<?php if ( $featured_img ) : ?>
								<img src="<?php echo $featured_img_src[0] ?>" alt="" style="max-width:100%;" />
							<?php endif; ?>
						</div>
						<p class="hide-if-no-js">
							<a class="upload-featured-image-archive-img <?php if ( $featured_img  ) { echo 'hidden'; } ?>"
							   href="<?php echo $upload_link ?>">
								<?php _e('Set featured image', 'archive-control') ?>
							</a>
							<a class="delete-featured-image-archive-img <?php if ( ! $featured_img  ) { echo 'hidden'; } ?>"
							  href="#">
								<?php _e('Remove featured image', 'archive-control') ?>
							</a>
						</p>
						<input class="featured-image-id" name="archive_control_term_meta[image]" type="hidden" value="<?php echo esc_attr( $archive_control_term_image ); ?>" />
					</div>
				</td>
		    </tr><?php
			}
			$before_val = isset($options['before']) ? $options['before'] : null;
			if ($before_val == 'textarea') {
				$archive_control_term_before = get_term_meta( $term->term_id, 'archive_control_term_before', true );
				?><tr class="form-field term-group-wrap">
		        <th scope="row"><label for="feature-group"><?php _e( 'Before Archive List', 'archive-control' ); ?></label></th>
		        <td>
					<?php $settings = array(
						'textarea_name' => 'archive_control_term_meta[before]',
						'textarea_rows' => 10
					);?>
					<?php wp_editor( $archive_control_term_before, 'before-archive', $settings ); ?>
				</td>
		    </tr><?php
			}
			$after_val = isset($options['after']) ? $options['after'] : null;
			if ($after_val == 'textarea') {
				$archive_control_term_after = get_term_meta( $term->term_id, 'archive_control_term_after', true );
				?><tr class="form-field term-group-wrap">
		        <th scope="row"><label for="feature-group"><?php _e( 'After Archive List', 'archive-control' ); ?></label></th>
		        <td>
					<?php $settings = array(
						'textarea_name' => 'archive_control_term_meta[after]',
						'textarea_rows' => 10
					);?>
					<?php wp_editor( $archive_control_term_after, 'after-archive', $settings ); ?>
				</td>
		    </tr><?php
			}
			$term_order_pagination_val = isset($options['term_order_pagination']) ? $options['term_order_pagination'] : null;
			if ($term_order_pagination_val == 'enabled' && current_user_can('administrator')) {
				$archive_control_term_meta = get_term_meta( $term->term_id, 'archive_control_term_meta', true );
				$this->archive_control_add_field_orderby(
					$archive_control_term_meta,
					'archive_control_term_meta'
				);

				$this->archive_control_add_field_order(
					$archive_control_term_meta,
					'archive_control_term_meta'
				);

				$this->archive_control_add_field_pagination(
					$archive_control_term_meta,
					'archive_control_term_meta'
				);
			}
		}
	}

	/**
	 * The variable settings screen that can be activated per custom post type
	 *
	 * @since    1.0.0
	 */
	public function archive_control_edit_page_callback()
	{
		$screen = get_current_screen();
		$current_post_type = $screen->post_type;
		$options = get_option('archive_control_cpt_' . $current_post_type . '_options');
		$archive_control_cpt_title = get_option('archive_control_cpt_' . $current_post_type . '_title');
		$archive_control_cpt_image = get_option('archive_control_cpt_' . $current_post_type . '_image');
		$archive_control_cpt_before = get_option('archive_control_cpt_' . $current_post_type . '_before');
		$archive_control_cpt_after = get_option('archive_control_cpt_' . $current_post_type . '_after');
		if ($screen->post_type == '' && $screen->parent_file == 'edit.php') {
			$current_post_type = 'post';
		}
		$current_post_type_object = get_post_type_object($current_post_type);
		$current_post_type_options = isset($options) ? $options : null;
		?>
		<div id="archive-control-edit-page" class="wrap">
			<h1><?php printf(esc_html__( 'Edit %1$s Archive Page', 'archive-control' ),$current_post_type_object->label); ?></h1>
			<?php settings_errors(); ?>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'archive-control-' . $current_post_type . '-group' );
				do_settings_sections( 'archive-control-' . $current_post_type . '-group' );
				?>
				<div id="poststuff">
					<div id="post-body" class="metabox-holder columns-2">
						<div id="postbox-container-1" class="postbox-container">
							<div id="submitdiv" class="postbox ">
								<h2 class="hndle"><span><?php _e('Publish', 'archive-control'); ?></span></h2>
								<div class="inside">
									<div id="major-publishing-actions">
										<a href="<?php echo get_post_type_archive_link( $current_post_type ); ?>" class="button" id="archive-control-view-archive"><?php _e('View Archive', 'archive-control'); ?></a>
										<div id="publishing-action">
											<?php submit_button(); ?>
										</div><!-- #publishing-action -->
										<div class="clear"></div>
									</div><!-- #major-publishing-actions -->
								</div><!-- .inside -->
							</div><!-- #submitdiv -->
							<?php $image_val = isset($current_post_type_options['image']) ? $current_post_type_options['image'] : null; ?>
							<?php if ($image_val == 'enabled') : ?>
								<div id="featured-image-archive" class="postbox ">
									<h2 class="hndle"><span><?php _e('Archive Featured Image', 'archive-control'); ?></span></h2>
									<div class="inside">
										<?php
										$upload_link = esc_url( get_upload_iframe_src( 'image' ) );
										$featured_img_src = wp_get_attachment_image_src( $archive_control_cpt_image, 'full' );
										$featured_img = is_array( $featured_img_src );
										?>
										<div class="featured-image-archive-container">
										    <?php if ( $featured_img ) : ?>
										        <img src="<?php echo $featured_img_src[0] ?>" alt="" style="max-width:100%;" />
										    <?php endif; ?>
										</div>
										<p class="hide-if-no-js">
										    <a class="upload-featured-image-archive-img <?php if ( $featured_img  ) { echo 'hidden'; } ?>"
										       href="<?php echo $upload_link ?>">
										        <?php _e('Set featured image', 'archive-control') ?>
										    </a>
										    <a class="delete-featured-image-archive-img <?php if ( ! $featured_img  ) { echo 'hidden'; } ?>"
										      href="#">
										        <?php _e('Remove featured image', 'archive-control') ?>
										    </a>
										</p>
										<input class="featured-image-id" name="archive_control_cpt_<?php echo esc_attr($current_post_type); ?>_image" type="hidden" value="<?php echo esc_attr( $archive_control_cpt_image ); ?>" />
									</div><!-- .inside -->
								</div><!-- #featured-image-archive -->
							<?php endif; ?>
						</div><!-- .postbox-container -->
						<div id="postbox-container-2" class="postbox-container">
							<?php if ($current_post_type_options['title'] == 'custom') : ?>
								<div id="titlediv">
									<div id="titlewrap">
										<label class="screen-reader-text" id="title-prompt-text" for="title"><?php _e('Enter archive title here', 'archive-control'); ?></label>
										<input type="text" name="archive_control_cpt_<?php echo esc_attr($current_post_type); ?>_title" size="30" value="<?php echo esc_attr($archive_control_cpt_title); ?>" id="title" spellcheck="true" autocomplete="off">
									</div>
								</div>
							<?php endif; ?>
							<?php if ($current_post_type_options['before'] == 'textarea') : ?>
								<div id="archive-control-before">
									<h2><span><?php _e('Before Archive List', 'archive-control'); ?></span></h2>
									<div class="inside">
										<?php $settings = array(
											'textarea_name' => 'archive_control_cpt_' . $current_post_type . '_before',
											'textarea_rows' => 10
										);?>
										<?php wp_editor( $archive_control_cpt_before, 'before-archive', $settings ); ?>
									</div><!-- .inside -->
								</div><!-- #archive-control-before -->
							<?php endif; ?>
							<?php if ($current_post_type_options['after'] == 'textarea') : ?>
								<div id="archive-control-after">
									<h2><span><?php _e('After Archive List', 'archive-control'); ?></span></h2>
									<div class="inside">
										<?php $settings = array(
											'textarea_name' => 'archive_control_cpt_' . $current_post_type . '_after',
											'textarea_rows' => 10
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

	/**
	 * Add field for archive title options
	 *
	 * @since    1.3.0
	 */
	public function archive_control_add_field_titles($options, $name, $taxonomy = null) {
		if (isset($taxonomy)) {
			$allow_custom = false;
			if ($taxonomy->name == 'category') {
				$label = __('Category Titles', 'archive-control');
				$remove_label = __('Remove Label (Category:)', 'archive-control');
			} elseif ($taxonomy->name == 'post_tag') {
				$label = __('Tag Titles', 'archive-control');
				$remove_label = __('Remove Label (Tag:)', 'archive-control');
			} else {
				$label = __('Term Titles', 'archive-control');
				$remove_label = __('Remove Label (Taxonomy:)', 'archive-control');
			}
		} else {
			$allow_custom = true;
			$label = __('CPT Archive Title', 'archive-control');
			$remove_label = __('Remove Label (Archive:)', 'archive-control');
		}
		$title_val = isset($options['title']) ? $options['title'] : null;
		?><tr valign="top">
			<th scope="row"><label><?php echo $label; ?></label></th>
			<td>
				<select class="archive-control-title" name="<?php echo esc_attr($name); ?>[title]">
					<option value="default"<?php if ($title_val == 'default') { echo "selected='selected'"; } ?>><?php _e('Do not modify', 'archive-control'); ?></option>
					<option value="no-labels"<?php if ($title_val == 'no-labels') { echo "selected='selected'"; } ?>><?php echo $remove_label; ?></option>
					<?php if ($allow_custom == true) { ?><option value="custom"<?php if ($title_val == 'custom') { echo "selected='selected'"; } ?>><?php _e('Custom Override', 'archive-control'); ?></option><?php } ?>
				</select>
				<div class="archive-control-info archive-control-title-message"<?php if ($title_val == 'default' || $title_val == null) { echo " style='display:none;'"; } ?>><?php _e('This requires that your theme use the_archive_title() function.', 'archive-control'); ?></div>
			</td>
		</tr><?php
	}

	/**
	 * Add field for archive featured image options
	 *
	 * @since    1.3.0
	 */
	public function archive_control_add_field_image($options, $name) {
		$image_val = isset($options['image']) ? $options['image'] : null;
		$image_placement_val = isset($options['image-placement']) ? $options['image-placement'] : null;
		$image_pages_val = isset($options['image-pages']) ? $options['image-pages'] : null;
		?><tr valign="top">
			<th scope="row"><label><?php _e('Featured Image', 'archive-control') ?></label></th>
			<td>
				<select class="archive-control-image" name="<?php echo esc_attr($name); ?>[image]">
					<option value="disabled"<?php if ($image_val == 'disabled') { echo "selected='selected'"; } ?>><?php _e('Disabled', 'archive-control'); ?></option>
					<option value="enabled"<?php if ($image_val == 'enabled') { echo "selected='selected'"; } ?>><?php _e('Enabled', 'archive-control'); ?></option>
				</select>

				<select class="archive-control-image-placement" name="<?php echo esc_attr($name); ?>[image-placement]"<?php if ($image_val !== 'enabled') { echo " style='display:none;'"; } ?>>
					<option value="automatic"<?php if ($image_placement_val == 'automatic') { echo "selected='selected'"; } ?>><?php _e('Automatic', 'archive-control'); ?></option>
					<option value="function"<?php if ($image_placement_val == 'function') { echo "selected='selected'"; } ?>><?php _e('Manual Function', 'archive-control'); ?></option>
				</select>

				<select class="archive-control-image-pages" name="<?php echo esc_attr($name); ?>[image-pages]"<?php if ($image_val !== 'enabled') { echo " style='display:none;'"; } ?>>
					<option value="all-pages"<?php if ($image_pages_val == 'all-pages') { echo "selected='selected'"; } ?>><?php _e('All Page Numbers', 'archive-control'); ?></option>
					<option value="first"<?php if ($image_pages_val == 'first') { echo "selected='selected'"; } ?>><?php _e('First Page Only', 'archive-control'); ?></option>
				</select>

				<div class="archive-control-info archive-control-image-automatic-message" <?php if ($image_val !== 'enabled' || $image_placement_val !== 'automatic') { echo " style='display:none;'"; } ?>><?php _e('The image will be automatically added to the archive page before the posts loop.', 'archive-control'); ?><?php if ($image_val == 'enabled') { ?> <a href="<?php echo $edit_url; ?>"><?php _e('Edit'); ?></a><?php } ?></div>

				<div class="archive-control-info archive-control-image-manual-message"<?php if ($image_val !== 'enabled' || $image_placement_val !== 'function') { echo " style='display:none;'"; } ?>><?php _e('The image will be added if you place the_archive_thumbnail("large") within your theme files.', 'archive-control'); ?><?php if ($image_val == 'enabled') { ?> <a href="<?php echo $edit_url; ?>"><?php _e('Edit'); ?></a><?php } ?></div>
			</td>
		</tr><?php
	}

	/**
	 * Add field for archive content before options
	 *
	 * @since    1.3.0
	 */
	public function archive_control_add_field_before($options, $name) {
		$before_val = isset($options['before']) ? $options['before'] : null;
		$before_placement_val = isset($options['before-placement']) ? $options['before-placement'] : null;
		$before_pages_val = isset($options['before-pages']) ? $options['before-pages'] : null;
		?><tr valign="top">
			<th scope="row"><label><?php _e('Content Before List', 'archive-control'); ?></label></th>
			<td>
				<select class="archive-control-before" name="<?php echo esc_attr($name); ?>[before]">
					<option value="default"<?php if ($before_val == 'default') { echo "selected='selected'"; } ?>><?php _e('Disabled', 'archive-control'); ?></option>
					<option value="textarea"<?php if ($before_val == 'textarea') { echo "selected='selected'"; } ?>><?php _e('Enabled', 'archive-control'); ?></option>
				</select>
				<select class="archive-control-before-placement" name="<?php echo esc_attr($name); ?>[before-placement]"<?php if ($before_val !== 'textarea') { echo " style='display:none;'"; } ?>>
					<option value="automatic"<?php if ($before_placement_val == 'automatic') { echo "selected='selected'"; } ?>><?php _e('Automatic', 'archive-control'); ?></option>
					<option value="function"<?php if ($before_placement_val == 'function') { echo "selected='selected'"; } ?>><?php _e('Manual Function', 'archive-control'); ?></option>
				</select>
				<select class="archive-control-before-pages" name="<?php echo esc_attr($name); ?>[before-pages]"<?php if ($before_val !== 'textarea') { echo " style='display:none;'"; } ?>>
					<option value="all-pages"<?php if ($before_pages_val == 'all-pages') { echo "selected='selected'"; } ?>><?php _e('All Page Numbers', 'archive-control'); ?></option>
					<option value="first"<?php if ($before_pages_val == 'first') { echo "selected='selected'"; } ?>><?php _e('First Page Only', 'archive-control'); ?></option>
				</select>
				<div class="archive-control-info archive-control-before-automatic-message" <?php if ($before_val !== 'textarea' || $before_placement_val !== 'automatic') { echo " style='display:none;'"; } ?>><?php _e('The content will be automatically added to the archive page before the posts loop.', 'archive-control'); ?><?php if ($before_val == 'textarea') { ?> <a href="<?php echo $edit_url; ?>"><?php _e('Edit', 'archive-control'); ?></a><?php } ?></div>
				<div class="archive-control-info archive-control-before-manual-message"<?php if ($before_val !== 'textarea' || $before_placement_val !== 'function') { echo " style='display:none;'"; } ?>><?php _e('The content will be added if you place the_archive_top_content() within your theme files.', 'archive-control'); ?><?php if ($before_val == 'textarea') { ?> <a href="<?php echo $edit_url; ?>"><?php _e('Edit', 'archive-control'); ?></a><?php } ?></div>
			</td>
		</tr><?php
	}

	/**
	 * Add field for archive content after options
	 *
	 * @since    1.3.0
	 */
	public function archive_control_add_field_after($options, $name) {
		$after_val = isset($options['after']) ? $options['after'] : null;
		$after_placement_val = isset($options['after-placement']) ? $options['after-placement'] : null;
		$after_pages_val = isset($options['after-pages']) ? $options['after-pages'] : null;
		?><tr valign="top">
			<th scope="row"><label><?php _e('Content After List', 'archive-control'); ?></label></th>
			<td>
				<select class="archive-control-after" name="<?php echo esc_attr($name); ?>[after]">
					<option value="default"<?php if ($after_val == 'default') { echo "selected='selected'"; } ?>><?php _e('Disabled', 'archive-control'); ?></option>
					<option value="textarea"<?php if ($after_val == 'textarea') { echo "selected='selected'"; } ?>><?php _e('Enabled', 'archive-control'); ?></option>
				</select>
				<select class="archive-control-after-placement" name="<?php echo esc_attr($name); ?>[after-placement]"<?php if ($after_val !== 'textarea') { echo " style='display:none;'"; } ?>>
					<option value="automatic"<?php if ($after_placement_val == 'automatic') { echo "selected='selected'"; } ?>><?php _e('Automatic', 'archive-control'); ?></option>
					<option value="function"<?php if ($after_placement_val == 'function') { echo "selected='selected'"; } ?>><?php _e('Manual Function', 'archive-control'); ?></option>
				</select>
				<select class="archive-control-after-pages" name="<?php echo esc_attr($name); ?>[after-pages]"<?php if ($after_val !== 'textarea') { echo " style='display:none;'"; } ?>>
					<option value="all-pages"<?php if ($after_pages_val == 'all-pages') { echo "selected='selected'"; } ?>><?php _e('All Page Numbers', 'archive-control'); ?></option>
					<option value="first"<?php if ($after_pages_val == 'first') { echo "selected='selected'"; } ?>><?php _e('First Page Only', 'archive-control'); ?></option>
				</select>
				<div class="archive-control-info archive-control-after-automatic-message" <?php if ($after_val !== 'textarea' || $after_placement_val !== 'automatic') { echo " style='display:none;'"; } ?>><?php _e('The content will be automatically added to the archive page after the posts loop.', 'archive-control'); ?><?php if ($after_val == 'textarea') { ?> <a href="<?php echo $edit_url; ?>"><?php _e('Edit'); ?></a><?php } ?></div>
				<div class="archive-control-info archive-control-after-manual-message"<?php if ($after_val !== 'textarea' || $after_placement_val !== 'function') { echo " style='display:none;'"; } ?>><?php _e('The content will be added if you place  the_archive_bottom_content() within your theme files.', 'archive-control'); ?><?php if ($after_val == 'textarea') { ?> <a href="<?php echo $edit_url; ?>"><?php _e('Edit'); ?></a><?php } ?></div>
			</td>
		</tr><?php
	}

	/**
	 * Add field for archive orderby options
	 *
	 * @since    1.3.0
	 */
	public function archive_control_add_field_orderby($options, $name) {
		$orderby_val = isset($options['orderby']) ? $options['orderby'] : null;
		$meta_key_val = isset($options['meta_key']) ? $options['meta_key'] : null;
		?><tr valign="top">
			<th scope="row"><?php _e('Order By', 'archive-control'); ?></th>
			<td>
				<select class="archive-control-order-by" name="<?php echo esc_attr($name); ?>[orderby]">
					<option value="default"<?php if ($orderby_val == 'default') { echo "selected='selected'"; } ?>><?php _e('Do not modify', 'archive-control'); ?></option>
					<option value="date"<?php if ($orderby_val == 'date') { echo "selected='selected'"; } ?>><?php _e('Date Published', 'archive-control'); ?></option>
					<option value="title"<?php if ($orderby_val == 'title') { echo "selected='selected'"; } ?>><?php _e('Title', 'archive-control'); ?></option>
					<option value="modified"<?php if ($orderby_val == 'modified') { echo "selected='selected'"; } ?>><?php _e('Date Modified', 'archive-control'); ?></option>
					<option value="menu_order"<?php if ($orderby_val == 'menu_order') { echo "selected='selected'"; } ?>><?php _e('Menu Order', 'archive-control'); ?></option>
					<option value="rand"<?php if ($orderby_val == 'rand') { echo "selected='selected'"; } ?>><?php _e('Random', 'archive-control'); ?></option>
					<option value="ID"<?php if ($orderby_val == 'ID') { echo "selected='selected'"; } ?>><?php _e('ID', 'archive-control'); ?></option>
					<option value="author"<?php if ($orderby_val == 'author') { echo "selected='selected'"; } ?>><?php _e('Author', 'archive-control'); ?></option>
					<option value="name"<?php if ($orderby_val == 'name') { echo "selected='selected'"; } ?>><?php _e('Post Slug', 'archive-control'); ?></option>
					<option value="type"<?php if ($orderby_val == 'type') { echo "selected='selected'"; } ?>><?php _e('Post Type', 'archive-control'); ?></option>
					<option value="comment_count"<?php if ($orderby_val == 'comment_count') { echo "selected='selected'"; } ?>><?php _e('Comment Count', 'archive-control'); ?></option>
					<option value="parent"<?php if ($orderby_val == 'parent') { echo "selected='selected'"; } ?>><?php _e('Parent', 'archive-control'); ?></option>
					<option value="meta_value"<?php if ($orderby_val == 'meta_value') { echo "selected='selected'"; } ?>><?php _e('Meta Value', 'archive-control'); ?></option>
					<option value="meta_value_num"<?php if ($orderby_val == 'meta_value_num') { echo "selected='selected'"; } ?>><?php _e('Meta Value (Numeric)', 'archive-control'); ?></option>
					<option value="none"<?php if ($orderby_val == 'none') { echo "selected='selected'"; } ?>><?php _e('No Order', 'archive-control'); ?></option>
				</select>
				<input class="archive-control-meta-key" type="text" name="<?php echo esc_attr($name); ?>[meta_key]" value="<?php echo esc_attr($meta_key_val); ?>" placeholder="<?php _e('Meta Key', 'archive-control'); ?>"<?php if ($orderby_val !== 'meta_value' && $orderby_val !== 'meta_value_num') { echo " style='display:none;'"; } ?>/>
			</td>
		</tr><?php
	}

	/**
	 * Add field for archive order options
	 *
	 * @since    1.3.0
	 */
	public function archive_control_add_field_order($options, $name) {
		$order_val = isset($options['order']) ? $options['order'] : null;
		?><tr valign="top">
			<th scope="row"><label><?php _e('Order', 'archive-control'); ?></label></th>
			<td>
				<select name="<?php echo esc_attr($name); ?>[order]">
					<option value="default"<?php if ($order_val == 'default') { echo "selected='selected'"; } ?>><?php _e('Do not modify', 'archive-control'); ?></option>
					<option value="asc"<?php if ($order_val == 'asc') { echo "selected='selected'"; } ?>><?php _e('Ascending', 'archive-control'); ?></option>
					<option value="desc"<?php if ($order_val == 'desc') { echo "selected='selected'"; } ?>><?php _e('Descending', 'archive-control'); ?></option>
				</select>
			</td>
		</tr><?php
	}

	/**
	 * Add field for archive pagination options
	 *
	 * @since    1.3.0
	 */
	public function archive_control_add_field_pagination($options, $name) {
		$pagination_val = isset($options['pagination']) ? $options['pagination'] : null;
		$posts_per_page_val = isset($options['posts_per_page']) ? $options['posts_per_page'] : null;
		?><tr valign="top">
			<th scope="row"><label><?php _e('Pagination', 'archive-control'); ?></label></th>
			<td>
				<select class="archive-control-pagination" name="<?php echo esc_attr($name); ?>[pagination]">
					<option value="default"<?php if ($pagination_val == 'default') { echo "selected='selected'"; } ?>><?php _e('Do not modify', 'archive-control'); ?></option>
					<option value="none"<?php if ($pagination_val == 'none') { echo "selected='selected'"; } ?>><?php _e('Show Everything', 'archive-control'); ?></option>
					<option value="custom"<?php if ($pagination_val == 'custom') { echo "selected='selected'"; } ?>><?php _e('Custom Posts Per Page', 'archive-control'); ?></option>
				</select>
				<input class="archive-control-posts-per-page" type="text" name="<?php echo esc_attr($name); ?>[posts_per_page]" value="<?php echo esc_attr($posts_per_page_val); ?>" placeholder="<?php _e('Posts per page', 'archive-control'); ?>"<?php if ($pagination_val !== 'custom') { echo " style='display:none;'"; } ?>/>
			</td>
		</tr><?php
	}

	/**
	 * Add field for archive order options
	 *
	 * @since    1.3.0
	 */
	public function archive_control_add_field_term_options($options, $name, $taxonomy) {
		$term_order_pagination_val = isset($options['term_order_pagination']) ? $options['term_order_pagination'] : null;
		$hide_parent_val = isset($options['hide_parent']) ? $options['hide_parent'] : null;
		$hide_description_val = isset($options['hide_description']) ? $options['hide_description'] : null;
		?><tr valign="top">
			<th scope="row"><label><?php _e('Term Edit Options', 'archive-control'); ?></label></th>
			<td>
				<label class="checkbox-label"><input type="checkbox" name="<?php echo esc_attr($name); ?>[term_order_pagination]" value="enabled"<?php if ($term_order_pagination_val == 'enabled') { echo "checked='checked'"; } ?>> <?php _e('Per Term Order & Pagination', 'archive-control'); ?></label>
				<label class="checkbox-label"><input type="checkbox" name="<?php echo esc_attr($name); ?>[hide_description]" value="hidden"<?php if ($hide_description_val == 'hidden') { echo "checked='checked'"; } ?>> <?php _e('Hide Description Field', 'archive-control'); ?></label>
				<?php if (is_taxonomy_hierarchical($taxonomy)) { ?>
					<label class="checkbox-label"><input type="checkbox" name="<?php echo esc_attr($name); ?>[hide_parent]" value="hidden"<?php if ($hide_parent_val == 'hidden') { echo "checked='checked'"; } ?>> <?php _e('Hide Parent Field', 'archive-control'); ?></label>
				<?php } ?>
			</td>
		</tr><?php
	}

	/**
	 * The primary settings screen(s) for the plugin
	 *
	 * @since    1.0.0
	 */
	public function archive_control_options()
	{
		?>
		<div id="archive-control-options" class="wrap">
			<h1><?php _e('Archive Control Settings', 'archive-control'); ?></h1>
			<?php
				if( isset( $_GET[ 'tab' ] ) ) {
					$active_tab = $_GET[ 'tab' ];
				} else {
					$active_tab = 'post_types';
				}
			?>
			<h2 class="nav-tab-wrapper">
                <a href="?page=archive-control&tab=post_types" class="nav-tab <?php echo $active_tab == 'post_types' ? 'nav-tab-active' : ''; ?>"><?php _e('Post Types'); ?></a>
                <a href="?page=archive-control&tab=taxonomies" class="nav-tab <?php echo $active_tab == 'taxonomies' ? 'nav-tab-active' : ''; ?>"><?php _e('Taxonomies'); ?></a>
            </h2>
			<?php if ($active_tab == 'cats_tags') { ?>
				<p><?php _e('You can select options for all categories or tags here.', 'archive-control'); ?></p>
				<form method="post" action="options.php">
					<?php
						settings_fields( 'archive-control-tax-options-group' );
						do_settings_sections( 'archive-control-tax-options-group' );
					?>
					<div id="poststuff">
						<div id="post-body" class="metabox-holder columns-2">
							<div id="postbox-container-1" class="postbox-container">
								<?php include('admin-sidebar.php' ); ?>
							</div><!-- .postbox-container -->
							<div id="postbox-container-2" class="postbox-container">

							</div><!-- .postbox-container -->
						</div><!-- #post-body -->
						<br class="clear">
					</div><!-- #poststuff -->
				</form>
			<?php } elseif($active_tab == 'post_types') { ?>
				<p><?php _e('You can select options for each custom post type. (If you do not see your custom post type in this list, you may need to set "has_archive" to true.)', 'archive-control'); ?></p>
				<form method="post" action="options.php">
					<?php
						settings_fields( 'archive-control-cpt-options-group' );
						do_settings_sections( 'archive-control-cpt-options-group' );
					?>
					<div id="poststuff">
						<div id="post-body" class="metabox-holder columns-2">
							<div id="postbox-container-1" class="postbox-container">
								<?php include('admin-sidebar.php' ); ?>
							</div><!-- .postbox-container -->
							<div id="postbox-container-2" class="postbox-container">
								<?php
								$custom_post_types = $this->archive_control_get_cpts();
								if (!empty($custom_post_types)) {
									foreach($custom_post_types as $post_type) {
										$edit_url = get_admin_url() . 'edit.php?post_type=' . $post_type->name . '&page=edit-archive-' . $post_type->name;
										$options = get_option('archive_control_cpt_' . $post_type->name . '_options');
										?>
										<div class="postbox">
											<h2 class="hndle ui-sortable-handle"><span><?php echo $post_type->label; ?></span></h2>
											<div class="inside">
												<table class="form-table">

													<?php

														$this->archive_control_add_field_titles(
															$options,
															'archive_control_cpt_' . $post_type->name . '_options'
														);

														$this->archive_control_add_field_image(
															$options,
															'archive_control_cpt_' . $post_type->name . '_options'
														);

														$this->archive_control_add_field_before(
															$options,
															'archive_control_cpt_' . $post_type->name . '_options'
														);

														$this->archive_control_add_field_after(
															$options,
															'archive_control_cpt_' . $post_type->name . '_options'
														);

														$this->archive_control_add_field_orderby(
															$options,
															'archive_control_cpt_' . $post_type->name . '_options'
														);

														$this->archive_control_add_field_order(
															$options,
															'archive_control_cpt_' . $post_type->name . '_options'
														);

														$this->archive_control_add_field_pagination(
															$options,
															'archive_control_cpt_' . $post_type->name . '_options'
														);

													?>

												</table>
											</div><!-- .inside -->
										</div><!-- .postbox -->
									<?php } //foreach ?>
								<?php } //if not empty ?>
							</div><!-- .postbox-container -->
						</div><!-- #post-body -->
						<br class="clear">
					</div><!-- #poststuff -->
				</form>
			<?php } elseif($active_tab == 'taxonomies') { ?>
				<p><?php _e('You can select options for each taxonomy. These options apply to all of the term archive pages.', 'archive-control'); ?></p>
				<form method="post" action="options.php">
					<?php
						settings_fields( 'archive-control-tax-options-group' );
						do_settings_sections( 'archive-control-tax-options-group' );
					?>
					<div id="poststuff">
						<div id="post-body" class="metabox-holder columns-2">
							<div id="postbox-container-1" class="postbox-container">
								<?php include('admin-sidebar.php' ); ?>
							</div><!-- .postbox-container -->
							<div id="postbox-container-2" class="postbox-container">
								<?php
								$taxonomies = $this->archive_control_get_taxes(true);
								if (!empty($taxonomies)) {
									foreach($taxonomies as $taxonomy) {
										$options = get_option('archive_control_tax_' . $taxonomy->name . '_options');
										?>
										<div class="postbox">
											<h2 class="hndle ui-sortable-handle"><span><?php echo $taxonomy->label; ?></span></h2>
											<div class="inside">
												<table class="form-table">

													<?php

														$this->archive_control_add_field_titles(
															$options,
															'archive_control_tax_' . $taxonomy->name . '_options',
															$taxonomy
														);

														$this->archive_control_add_field_image(
															$options,
															'archive_control_tax_' . $taxonomy->name . '_options'
														);

														$this->archive_control_add_field_before(
															$options,
															'archive_control_tax_' . $taxonomy->name . '_options'
														);

														$this->archive_control_add_field_after(
															$options,
															'archive_control_tax_' . $taxonomy->name . '_options'
														);

														$this->archive_control_add_field_orderby(
															$options,
															'archive_control_tax_' . $taxonomy->name . '_options'
														);

														$this->archive_control_add_field_order(
															$options,
															'archive_control_tax_' . $taxonomy->name . '_options'
														);

														$this->archive_control_add_field_pagination(
															$options,
															'archive_control_tax_' . $taxonomy->name . '_options'
														);

														$this->archive_control_add_field_term_options(
															$options,
															'archive_control_tax_' . $taxonomy->name . '_options',
															$taxonomy->name
														);

													?>

												</table>
											</div><!-- .inside -->
										</div><!-- .postbox -->
									<?php } //foreach ?>
								<?php } //not empty ?>
							</div><!-- .postbox-container -->
						</div><!-- #post-body -->
						<br class="clear">
					</div><!-- #poststuff -->
				</form>
			<?php } //taxonomy tab ?>
			<form id="paypal-form" action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top" style="display:none;">
				<input type="hidden" name="cmd" value="_s-xclick">
				<input type="hidden" name="hosted_button_id" value="KKQZMELKM2JHG">
				<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_paynow_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
				<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
			</form>
		</div>
		<?php
	}

	/**
	 * Modify the existing WordPress query for certain archive pages according to the settings
	 *
	 * @since    1.0.0
	 *
	 * @param    object    $query    The existing WordPress query
	 */
	public function archive_control_modify_archive_query( $query )
	{
		if ( ! is_admin() ) {
			if( $query->is_main_query() ){
				if (is_post_type_archive()) {
					$post_type = get_query_var('post_type', null);
					$options = get_option('archive_control_cpt_' . $post_type . '_options');
					if ($options) {
						$order_val = isset($options['order']) ? $options['order'] : null;
						if ($order_val !== 'default' && $order_val !== null) {
							$query->set( 'order', $options['order'] );
						} //has order value
						$orderby_val = isset($options['orderby']) ? $options['orderby'] : null;
						if ($orderby_val !== 'default' && $orderby_val !== null) {
							$query->set( 'orderby', $options['orderby'] );
							if ($options['orderby'] == 'meta_value' || $options['orderby'] == 'meta_value_num') {
								if ($options['meta_key'] !== null) {
									$query->set( 'meta_key', $options['meta_key'] );
								} //has meta_key value
							} //meta_key value is needed
						} //has orderby value
						$pagination_val = isset($options['pagination']) ? $options['pagination'] : null;
						if ($pagination_val !== 'default' && $pagination_val !== null) {
							if ($options['pagination'] == 'custom') {
								if ($options['posts_per_page'] !== null) {
									$query->set( 'posts_per_page', $options['posts_per_page'] );
								}
							}
							if ($options['pagination'] == 'none') {
								$query->set( 'posts_per_page', -1 );
							}
						} //has pagination value
					} //has options
				} // is_post_type_artchive
				if (is_tax() || is_category() || is_tag()) {
					$taxonomy = $query->tax_query->queries[0]['taxonomy'];
					$options = get_option('archive_control_tax_' . $taxonomy . '_options');
					if ($options) {
						$order_val = isset($options['order']) ? $options['order'] : null;
						if ($order_val !== 'default' && $order_val !== null) {
							$query->set( 'order', $options['order'] );
						} //has order value
						$orderby_val = isset($options['orderby']) ? $options['orderby'] : null;
						if ($orderby_val !== 'default' && $orderby_val !== null) {
							$query->set( 'orderby', $options['orderby'] );
							if ($options['orderby'] == 'meta_value' || $options['orderby'] == 'meta_value_num') {
								if ($options['meta_key'] !== null) {
									$query->set( 'meta_key', $options['meta_key'] );
								} //has meta_key value
							} //meta_key value is needed
						} //has orderby value
						$pagination_val = isset($options['pagination']) ? $options['pagination'] : null;
						if ($pagination_val !== 'default' && $pagination_val !== null) {
							if ($options['pagination'] == 'custom') {
								if ($options['posts_per_page'] !== null) {
									$query->set( 'posts_per_page', $options['posts_per_page'] );
								}
							}
							if ($options['pagination'] == 'none') {
								$query->set( 'posts_per_page', -1 );
							}
						} //has pagination value
					} //if options
					$term_slug = $query->tax_query->queries[0]['terms'][0];
					$term = get_term_by( 'slug', $term_slug, $taxonomy );
					$term_options = get_term_meta( $term->term_id, 'archive_control_term_meta', true );
					if ($term_options) {
						$order_val = isset($term_options['order']) ? $term_options['order'] : null;
						if ($order_val !== 'default' && $order_val !== null) {
							$query->set( 'order', $term_options['order'] );
						} //has order value
						$orderby_val = isset($term_options['orderby']) ? $term_options['orderby'] : null;
						if ($orderby_val !== 'default' && $orderby_val !== null) {
							$query->set( 'orderby', $term_options['orderby'] );
							if ($term_options['orderby'] == 'meta_value' || $term_options['orderby'] == 'meta_value_num') {
								if ($term_options['meta_key'] !== null) {
									$query->set( 'meta_key', $term_options['meta_key'] );
								} //has meta_key value
							} //meta_key value is needed
						} //has orderby value
						$pagination_val = isset($term_options['pagination']) ? $term_options['pagination'] : null;
						if ($pagination_val !== 'default' && $pagination_val !== null) {
							if ($term_options['pagination'] == 'custom') {
								if ($term_options['posts_per_page'] !== null) {
									$query->set( 'posts_per_page', $term_options['posts_per_page'] );
								}
							}
							if ($term_options['pagination'] == 'none') {
								$query->set( 'posts_per_page', -1 );
							}
						} //has pagination value
					}//has term options
				} // is_tax
			} //is_main_query
		} //is not admin
	    return;
	}

	/**
	 * Insert some content above the WordPress loop on certain archive pages
	 *
	 * @since    1.1.0
	 *
	 * @param    object    $query    The existing WordPress query object
	 */
	public function archive_control_loop_start_content( $query )
	{
		if( $query->is_main_query() ){
			$this->archive_control_the_archive_top_content(true,'automatic');
		} //is_main_query
	}

	/**
	 * Insert some content above the WordPress loop on certain archive pages
	 *
	 * @since    1.1.0
	 *
	 * @param    boolean    $html        Whether to show the optional html markup surrounding the textarea
	 * @param    string    	$placement	 Whether the content will be placed automatically or by a theme function
	 */
	public static function archive_control_the_archive_top_content($html = true, $placement = 'function')
	{
		if (is_post_type_archive()) {
			$post_type = get_query_var('post_type', null);
			$options = get_option('archive_control_cpt_' . $post_type . '_options');
			if ($options) {
				if ($options['before'] == 'textarea' && $options['before-placement'] == $placement) {
					$paged = get_query_var( 'paged', 0);
					if($options['before-pages'] == 'all-pages' || ($options['before-pages'] == 'first' && $paged == 0)) {
						Archive_Control::archive_control_archive_top_content($html);
					}//handle page all/first options
				} //before textarea is enabled
			} //has options
		} // is_post_type_archive
		if (is_tax() || is_category() || is_tag()) {
			$taxonomy = null;
			if (is_tax()) $taxonomy = get_query_var('taxonomy');
			if (is_category()) $taxonomy = 'category';
			if (is_tag()) $taxonomy = 'post_tag';
			$options = get_option('archive_control_tax_' . $taxonomy . '_options');
			if ($options) {
				if ($options['before'] == 'textarea' && $options['before-placement'] == $placement) {
					$paged = get_query_var( 'paged', 0);
					if($options['before-pages'] == 'all-pages' || ($options['before-pages'] == 'first' && $paged == 0)) {
						Archive_Control::archive_control_archive_top_content($html);
					}//handle page all/first options
				} //before textarea is enabled
			} //has options
		} //is_tax, cat, tag
	}

	/**
	 * Insert the top content
	 *
	 * @since    1.1.0
	 *
	 * @param    boolean    $html        Whether to show the optional html markup surrounding the textarea
	 * @param    string    	$post_type_slug	 A post type slug that the content belongs to
	 */
	public static function archive_control_archive_top_content($html = true, $post_type_slug = null, $term_id = null)
	{
		$content = Archive_Control::archive_control_get_archive_top_content($post_type_slug, $term_id);
		if ($content) {
			if ($html === true) {
				echo "<div class='archive-control-area archive-control-area-before'>";
				echo "<div class='archive-control-area-inside'>";
			}
			echo $content;
			if ($html === true) {
				echo "</div>";
				echo "</div>";
			}
		}
	}

	/**
	 * Return the top content
	 *
	 * @since    1.1.0
	 *
	 * @param    string    	$post_type_slug	 A post type slug that the content belongs to
	 * @param    string    	$term_id	 A taxonomy term id that the content belongs to
	 */
	public static function archive_control_get_archive_top_content($post_type_slug = null, $term_id = null)
	{
		if ($post_type_slug === null && $term_id === null) {
			if (is_post_type_archive()) {
				$post_type_slug = get_query_var('post_type', null);
			}
			if (is_tax() || is_category() || is_tag()) {
				$term = get_queried_object();
				$term_id = $term->term_id;
			}
		}
		$archive_control_cpt_before = null;
		if ($post_type_slug) {
			$archive_control_cpt_before = get_option('archive_control_cpt_' . $post_type_slug . '_before');
		} elseif ($term_id) {
			$archive_control_cpt_before = get_term_meta( $term_id, 'archive_control_term_before', true );
		}
		if ($archive_control_cpt_before) {
			return apply_filters( 'the_content', $archive_control_cpt_before );
		} //if has before content
	}

	/**
	 * Insert some content above the WordPress loop
	 *
	 * @since    1.1.0
	 *
	 * @param    object    $query        The existing WordPress query object
	 */
	public function archive_control_loop_start_image( $query ){
		if( $query->is_main_query() ){
			$this->archive_control_display_archive_top_image();
		} //is_main_query
	}

	/**
	 * Decide if the archive featured image should be automatically placed
	 *
	 * @since    1.1.0
	 */
	public static function archive_control_display_archive_top_image()
	{
		if (is_post_type_archive()) {
			$post_type = get_query_var('post_type', null);
			$options = get_option('archive_control_cpt_' . $post_type . '_options');
			if ($options) {
				if ($options['image'] == 'enabled' && $options['image-placement'] == 'automatic') {
					$paged = get_query_var( 'paged', 0);
					if($options['image-pages'] == 'all-pages' || ($options['image-pages'] == 'first' && $paged == 0)) {
						Archive_Control::archive_control_the_archive_thumbnail();
					}//handle page all/first options
				} //image is enabled
			} //has options
		} // is_post_type_archive
		if (is_tax() || is_category() || is_tag()) {
			$taxonomy = null;
			if (is_tax()) $taxonomy = get_query_var('taxonomy');
			if (is_category()) $taxonomy = 'category';
			if (is_tag()) $taxonomy = 'post_tag';
			$options = get_option('archive_control_tax_' . $taxonomy . '_options');
			if ($options) {
				if ($options['image'] == 'enabled' && $options['image-placement'] == 'automatic') {
					$paged = get_query_var( 'paged', 0);
					if($options['image-pages'] == 'all-pages' || ($options['image-pages'] == 'first' && $paged == 0)) {
						Archive_Control::archive_control_the_archive_thumbnail();
					}//handle page all/first options
				} //image is enabled
			} //has options
		} //is_tax, cat, tag
	}

	/**
	 * Echo the archive thumbnail with markup
	 *
	 * @since    1.1.0
	 *
	 * @param    string     $size       	 The image size that you want displayed
	 * @param    string    	$post_type_slug	 A post type slug that the content belongs to
	 * @param    string    	$term_id	 	A taxonomy term id that the content belongs to
	 */
	public static function archive_control_the_archive_thumbnail($size = 'large', $post_type_slug = null, $term_id = null)
	{
		// if ($post_type_slug === null) {
		// 	if (is_post_type_archive()) {
		// 		$post_type_slug = get_query_var('post_type', null);
		// 	}
		// }

		$featured_img_id = Archive_Control::archive_control_get_archive_thumbnail_id($post_type_slug, $term_id);
		if ($featured_img_id) {
			echo "<div class='post-thumbnail archive-thumbnail archive-type-" . esc_attr($post_type_slug) . "'>";
				echo wp_get_attachment_image($featured_img_id, $size);
			echo "</div>";
		}
	}

	/**
	 * Echo the archive thumbnail with html markup
	 *
	 * @since    1.1.0
	 *
	 * @param    string     $size        	The image size that you want displayed
	 * @param    string    	$post_type_slug	A post type slug that the content belongs to
	 * @param    string    	$term_id	 	A taxonomy term id that the content belongs to
	 */
	public static function archive_control_get_archive_thumbnail_src($size = 'large', $post_type_slug = null, $term_id = null)
	{
		$featured_img_id = Archive_Control::archive_control_get_archive_thumbnail_id($post_type_slug, $term_id);
		if ($featured_img_id) {
			$featured_img_src = wp_get_attachment_image_src( $featured_img_id, $size );
			if (is_array($featured_img_src)) {
				$src = $featured_img_src[0];
				return $src;
			}
		}
	}

	/**
	 * Get the archive thumbnail id
	 *
	 * @since    1.1.0
	 *
	 * @param    string    	$post_type_slug	A post type slug that the content belongs to
	 * @param    string    	$term_id	 	A taxonomy term id that the content belongs to
	 */
	public static function archive_control_get_archive_thumbnail_id($post_type_slug = null, $term_id = null)
	{
		if ($post_type_slug === null && $term_id === null) {
			if (is_post_type_archive()) {
				$post_type_slug = get_query_var('post_type', null);
			}
			if (is_tax() || is_category() || is_tag()) {
				$term = get_queried_object();
				$term_id = $term->term_id;
			}
		}
		$archive_control_image_id = null;
		if ($post_type_slug) {
			$archive_control_image_id = get_option('archive_control_cpt_' . $post_type_slug . '_image');
		} elseif ($term_id) {
			$archive_control_image_id = get_term_meta( $term_id, 'archive_control_term_image', true );
		}
		if ($archive_control_image_id) {
			if ( wp_attachment_is_image( $archive_control_image_id ) ) {
				return $archive_control_image_id;
			}
		}
	}

	/**
	 * Insert some content below the WordPress loop on certain archive pages
	 *
	 * @since    1.1.0
	 *
	 * @param    object    $query    The existing WordPress query object
	 */
	public function archive_control_loop_end_content( $query )
	{
		if( $query->is_main_query() ){
			$this->archive_control_the_archive_bottom_content(true,'automatic');
		} //is_main_query
	}

	/**
	 * Insert some content below the WordPress loop on certain archive pages
	 *
	 * @since    1.1.0
	 *
	 * @param    boolean    $html        Whether to show the optional html markup surrounding the textarea
	 * @param    string    	$placement	 Whether the content will be placed automatically or by a theme function
	 */
	public static function archive_control_the_archive_bottom_content($html = true, $placement = 'function')
	{
		if (is_post_type_archive()) {
			$post_type = get_query_var('post_type', null);
			$options = get_option('archive_control_cpt_' . $post_type . '_options');
			if ($options) {
				if ($options['after'] == 'textarea' && $options['after-placement'] == $placement) {
					$paged = get_query_var( 'paged', 0);
					if($options['after-pages'] == 'all-pages' || ($options['after-pages'] == 'first' && $paged == 0)) {
						Archive_Control::archive_control_archive_bottom_content($html);
					}//handle page all/first options
				} //after textarea is enabled
			} //has options
		} // is_post_type_archive
		if (is_tax() || is_category() || is_tag()) {
			$taxonomy = null;
			if (is_tax()) $taxonomy = get_query_var('taxonomy');
			if (is_category()) $taxonomy = 'category';
			if (is_tag()) $taxonomy = 'post_tag';
			$options = get_option('archive_control_tax_' . $taxonomy . '_options');
			if ($options) {
				if ($options['after'] == 'textarea' && $options['after-placement'] == $placement) {
					$paged = get_query_var( 'paged', 0);
					if($options['after-pages'] == 'all-pages' || ($options['after-pages'] == 'first' && $paged == 0)) {
						Archive_Control::archive_control_archive_bottom_content($html);
					}//handle page all/first options
				} //after textarea is enabled
			} //has options
		} //is_tax, cat, tag
	}

	/**
	 * Insert the bottom content
	 *
	 * @since    1.1.0
	 *
	 * @param    boolean    $html        		Whether to show the optional html markup surrounding the textarea
	 * @param    string    	$post_type_slug	 	A post type slug that the content belongs to
	 */
	public static function archive_control_archive_bottom_content($html = true, $post_type_slug = null, $term_id = null)
	{
		$content = Archive_Control::archive_control_get_archive_bottom_content($post_type_slug, $term_id);
		if ($content) {
			if ($html === true) {
				echo "<div class='archive-control-area archive-control-area-after'>";
				echo "<div class='archive-control-area-inside'>";
			}
			echo $content;
			if ($html === true) {
				echo "</div>";
				echo "</div>";
			}
		}
	}

	/**
	 * Return the bottom content
	 *
	 * @since    1.1.0
	 *
	 * @param    string    	$post_type_slug	 	A post type slug that the content belongs to
	 * @param    string    	$term_id	 		A taxonomy term id that the content belongs to
	 */
	public static function archive_control_get_archive_bottom_content($post_type_slug = null, $term_id = null)
	{
		if ($post_type_slug === null && $term_id === null) {
			if (is_post_type_archive()) {
				$post_type_slug = get_query_var('post_type', null);
			}
			if (is_tax() || is_category() || is_tag()) {
				$term = get_queried_object();
				$term_id = $term->term_id;
			}
		}
		$archive_control_cpt_after = null;
		if ($post_type_slug) {
			$archive_control_cpt_after = get_option('archive_control_cpt_' . $post_type_slug . '_after');
		} elseif ($term_id) {
			$archive_control_cpt_after = get_term_meta( $term_id, 'archive_control_term_after', true );
		}
		if ($archive_control_cpt_after) {
			return apply_filters( 'the_content', $archive_control_cpt_after );
		} //if has after content
	}

	/**
	 * Modify the archive title based on the user settings
	 *
	 * @since    1.0.0
	 *
	 * @param    string    	$title	 The existing archive page title
	 */
	public function archive_control_title_filter($title)
	{
		if (is_post_type_archive()) {
			$post_type = get_query_var('post_type', null);
			$options = get_option('archive_control_cpt_' . $post_type . '_options');
			if ($options) {
				if ($options['title'] == 'custom') {
					$archive_control_cpt_title = get_option('archive_control_cpt_' . $post_type . '_title');
					if ($archive_control_cpt_title) {
						$title = $archive_control_cpt_title;
					} //has title
				} //custom title setting
				if ($options['title'] == 'no-labels') {
					$title = post_type_archive_title( '', false );
				} //custom title set
			} //has options
		} // is_post_type_archive
		if (is_tax()) {
			$taxonomy = get_query_var('taxonomy');
			$options = get_option('archive_control_tax_' . $taxonomy . '_options');
			if ($options) {
				if ($options['title'] == 'no-labels') {
					$title = single_term_title( '', false );
				}
			} //has options
		} //is_tax
		if (is_category()) {
			$options = get_option('archive_control_tax_category_options');
			if ($options) {
				if ($options['title'] == 'no-labels') {
					$title = single_term_title( '', false );
				}
			} //has options
		} //is_category
		if (is_tag()) {
			$options = get_option('archive_control_tax_post_tag_options');
			if ($options) {
				if ($options['title'] == 'no-labels') {
					$title = single_term_title( '', false );
				}
			} //has options
		} //is_tag
		return $title;
	}

}

if ( ! function_exists( 'the_archive_top_content' ) )
{
    function the_archive_top_content($html = true) {
		Archive_Control::archive_control_the_archive_top_content($html);
    }
}

if ( ! function_exists( 'archive_top_content' ) )
{
    function archive_top_content($html = true, $post_type_slug = null, $term_id = null) {
		Archive_Control::archive_control_archive_top_content($html, $post_type_slug, $term_id);
    }
}

if ( ! function_exists( 'get_archive_top_content' ) )
{
    function get_archive_top_content($post_type_slug = null, $term_id = null) {
		return Archive_Control::archive_control_get_archive_top_content($post_type_slug, $term_id);
    }
}

if ( ! function_exists( 'the_archive_bottom_content' ) )
{
    function the_archive_bottom_content($html = true) {
		Archive_Control::archive_control_the_archive_bottom_content($html);
    }
}

if ( ! function_exists( 'archive_bottom_content' ) )
{
    function archive_bottom_content($html = true, $post_type_slug = null, $term_id = null) {
		Archive_Control::archive_control_archive_bottom_content($html, $post_type_slug, $term_id);
    }
}

if ( ! function_exists( 'get_archive_bottom_content' ) )
{
    function get_archive_bottom_content($post_type_slug = null, $term_id = null) {
		return Archive_Control::archive_control_get_archive_bottom_content($post_type_slug, $term_id);
    }
}

if ( ! function_exists( 'get_archive_thumbnail_id' ) )
{
    function get_archive_thumbnail_id($post_type_slug = null, $term_id = null) {
		return Archive_Control::archive_control_get_archive_thumbnail_id($post_type_slug, $term_id);
    }
}

if ( ! function_exists( 'get_archive_thumbnail_src' ) )
{
    function get_archive_thumbnail_src($size = null, $post_type_slug = null, $term_id = null) {
		return Archive_Control::archive_control_get_archive_thumbnail_src($size, $post_type_slug, $term_id);
    }
}

if ( ! function_exists( 'the_archive_thumbnail' ) )
{
    function the_archive_thumbnail($size = null, $post_type_slug = null, $term_id = null) {
		Archive_Control::archive_control_the_archive_thumbnail($size, $post_type_slug, $term_id);
    }
}
