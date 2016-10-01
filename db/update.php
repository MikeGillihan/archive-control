<?php

    /**
     * The current version of the plugin
     */
	$archive_control_db_current_version = get_option( 'archive_control_db_current_version', null );

    /**
     * If the database option was not already set
     */
	if (!isset($archive_control_db_current_version)) {
		add_option('archive_control_db_current_version', '1.1.1');
		$archive_control_db_current_version = '1.1.1';
	}

    /**
     * If the version doesn't match, run some db updates
     */
	if ($archive_control_db_current_version !=  self::VERSION) {
        
	    if (version_compare($archive_control_db_current_version, '1.1.1', '<=')) {
            $archive_control_options = get_option('archive_control_options');
            if ($archive_control_options) {
                foreach($archive_control_options as $post_type => $options) {
                    add_option( 'archive_control_cpt_' . $post_type . "_options", $options);
                    $cpt_title = get_option('archive_control_' . $post_type . '_title');
                    if ($cpt_title) {
                        add_option( 'archive_control_cpt_' . $post_type . '_title', $cpt_title);
                        //delete_option('archive_control_' . $post_type . '_title');
                    }
                    $cpt_image = get_option('archive_control_' . $post_type . '_image');
                    if ($cpt_image) {
                        add_option( 'archive_control_cpt_' . $post_type . '_image', $cpt_image);
                        //delete_option('archive_control_' . $post_type . '_image');
                    }
                    $cpt_before = get_option('archive_control_' . $post_type . '_before');
                    if ($cpt_before) {
                        add_option( 'archive_control_cpt_' . $post_type . '_before', $cpt_before);
                        //delete_option('archive_control_' . $post_type . '_before');
                    }
                    $cpt_after = get_option('archive_control_' . $post_type . '_after');
                    if ($cpt_after) {
                        add_option( 'archive_control_cpt_' . $post_type . '_after', $cpt_after);
                        //delete_option('archive_control_' . $post_type . '_after');
                    }
                }//foreach
                //add_option( 'archive_control_backup_options', $archive_control_options, '', 'no' );
                delete_option('archive_control_options');
            }//if archive control options
        }//version compare

        update_option('archive_control_db_current_version',  self::VERSION);

    } //doesn't match version
