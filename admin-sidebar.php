<div id="other-plugins" class="postbox">
    <div class="inside">
        <p>
            <?php _e('Want more control over your Custom Post Types and Taxonomies? Check out these other well made plugins:', 'archive-control'); ?>
        </p>
        <ul>
            <li>
                <a href="https://wordpress.org/plugins/custom-post-type-ui/" target="_blank">Custom Post Type UI</a>
            </li>
            <li>
                <a href="https://wordpress.org/plugins/advanced-custom-fields/" target="_blank">Advanced Custom Fields</a>
            </li>
            <li>
                <a href="https://wordpress.org/plugins/wp-pagenavi/" target="_blank">WP-PageNavi</a>
            </li>
            <li>
                <a href="https://wordpress.org/plugins/intuitive-custom-post-order/" target="_blank">Intuitive Custom Post Type Order</a>
            </li>
        </ul>
    </div>
</div><!-- #other-plugins -->
<div id="bugs-features" class="postbox">
    <div class="inside">
        <p>
            <?php
                $pluginlink = "<a href='https://wordpress.org/plugins/archive-control/' target='_blank'>";
                $githublink = "<a href='https://github.com/TheJester12/archive-control' target='_blank'>";
                $endlink = "</a>";
                printf( __( 'Found a bug or have a feature request? Let me know on the %1$sWordPress plugin directory%2$s, or send a pull request with %3$sGitHub%4$s.', 'archive-control' ), $pluginlink, $endlink, $githublink, $endlink );
            ?>
        </p>
    </div>
</div><!-- #bugs-features -->
<div id="buy-coffee" class="postbox">
    <div class="inside">
        <p><?php _e('Find yourself using this plugin often? Fuel it\'s future development with a nice cup of hot joe!', 'archive-control'); ?></p>
        <div><a href="#" class="button" id="buy-coffee-button"><?php _e('Buy Me a Coffee', 'archive-control'); ?><img src="<?php echo plugin_dir_url(__FILE__); ?>/img/coffee.svg"></a></div>
    </div><!-- .inside -->
</div><!-- .buy-coffee -->
