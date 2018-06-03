<?php
require_once(dirname(__FILE__).'/wbm-importer-helper.php');
require_once(dirname(__FILE__).'/wbm-csv-helper.php');

// Load Importer API
require_once ABSPATH . 'wp-admin/includes/import.php';
if ( !class_exists( 'WP_Importer' ) ) {
    $class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
    if ( file_exists( $class_wp_importer ) ) require_once $class_wp_importer;
}

class WP_Bookmark_Importer extends WP_Importer {
    function header() {
        echo '<div class="wrap">';
        echo '<h2>'.__('Import from CSV', 'wp-bookmark-manager').'</h2>';
    }

    // User interface wrapper end
    function footer() {
        echo '</div>';
    }

    // Step 1
    function greet() {
        echo '<p>'.__( 'Choose a CSV (.csv) file to upload, then click Upload file and import.', 'wp-bookmark-manager' ).'</p>';
        echo '<p>'.__( 'Excel-style CSV file is unconventional and not recommended. LibreOffice has enough export options and recommended for most users.', 'wp-bookmark-manager' ).'</p>';
        echo '<p>'.__( 'Requirements:', 'wp-bookmark-manager' ).'</p>';
        echo '<ol>';
        echo '<li>'.__( 'Select UTF-8 as charset.', 'wp-bookmark-manager' ).'</li>';
        echo '<li>'.sprintf( __( 'You must use field delimiter as "%s"', 'wp-bookmark-manager'), WBM_CSV_Helper::DELIMITER ).'</li>';
        echo '<li>'.__( 'You must quote all text cells.', 'wp-bookmark-manager' ).'</li>';
        echo '</ol>';
        echo '<p>'.__( 'Download example CSV files:', 'wp-bookmark-manager' );
        echo ' <a href="'.plugin_dir_url( __FILE__ ).'sample/sample.csv">sample.csv</a>, ';
        echo ' <a href="'.plugin_dir_url( __FILE__ ).'sample/sample_blank.csv">sample_blank.csv</a>';
        echo '<ol>';
        echo '<li>'.__( 'An empty "link_id", created as new item of link.', 'wp-bookmark-manager' ).'</li>';
        echo '<li>'.__( 'Empty data is ignored.', 'wp-bookmark-manager' ).'</li>';
        echo '<li>'.__( 'If you input "BLANK" string, then convert to empty data.', 'wp-bookmark-manager' ).'</li>';
        echo '<li>'.__( '"link_url" can not empty.', 'wp-bookmark-manager' ).'</li>';
        echo '<li>'.__( 'When "link_name" is "BLANK" string, "link_name" becomes the same value as "link_url".', 'wp-bookmark-manager' ).'</li>';
        echo '<li>'.__( 'When "link_category" is "BLANK" string, "link_category" becomes the default category.', 'wp-bookmark-manager' ).'</li>';
        echo '</ol>';
        echo '</p>';
        wp_import_upload_form( add_query_arg('step', 1) );
    }

    // Step 2
    function import() {
        $file = wp_import_handle_upload();

        if ( isset( $file['error'] ) ) {
            echo '<p><strong>' . __( 'Sorry, there has been an error.', 'wp-bookmark-manager' ) . '</strong><br />';
            echo esc_html( $file['error'] ) . '</p>';
            return false;
        } else if ( ! file_exists( $file['file'] ) ) {
            echo '<p><strong>' . __( 'Sorry, there has been an error.', 'wp-bookmark-manager' ) . '</strong><br />';
            printf( __( 'The export file could not be found at <code>%s</code>. It is likely that this was caused by a permissions problem.', 'wp-bookmark-manager' ), esc_html( $file['file'] ) );
            echo '</p>';
            return false;
        }

        $this->id = (int) $file['id'];
        $this->file = get_attached_file($this->id);
        $result = $this->process_links();
        if ( is_wp_error( $result ) )
            return $result;
    }

    /**
     * Insert link  using `WBM_Import_Link_Helper` class.
     *
     * @param array $link
     * @param bool $is_update
     * @return WBM_Import_Link_Helper
     */
    public function save_link($link,$is_update) {

        // Add or update the link
        if ($is_update) {
            $h = WBM_Import_Link_Helper::getByID($link['link_id']);
            $h->update($link);
        } else {
            $h = WBM_Import_Link_Helper::add($link);
        }

        return $h;
    }

    /**
     * 'BLANK'文字列であれば、空文字を返す。
     * 特定の項目を明示的に消去するため。
     *
     * @param string $data
     * @return string
     */
    public function set_data($data) {
        return ($data && $data !== 'BLANK') ? $data  : '';
    }

    // process parse csv ind insert links
    function process_links() {
        $h = new WBM_CSV_Helper;

        $handle = $h->fopen($this->file, 'r');
        if ( $handle == false ) {
            echo '<p><strong>'.__( 'Failed to open file.', 'wp-bookmark-manager' ).'</strong></p>';
            wp_import_cleanup($this->id);
            return false;
        }

        $is_first = true;

        echo '<ol>';

        while (($data = $h->fgetcsv($handle)) !== FALSE) {
            if ($is_first) {
                $h->parse_columns( $this, $data );
                $is_first = false;
            } else {
                echo '<li>';

                $link = array();
                $is_update = false;
                $error = new WP_Error();

                // (integer) if updating, the ID of the existing link
                $link_id = $h->get_data($this,$data,'link_id');
                $link_id = ($link_id) ? $link_id : $h->get_data($this,$data,'link_id');
                if ($link_id) {
                    $link_exist = get_bookmark($link_id);
                    if ( is_null( $link_exist ) ) { // if the link id is not exists
                        $link['import_id'] = $link_id;
                    } else {
                        $link['link_id'] = $link_id;
                        $is_update = true;
                    }
                }

                // (varchar) the URL the link points to
                // 必須。空だとwp_insert_link()がreturn 0
                $link_url = $h->get_data($this,$data,'link_url');
                if ($link_url) {
                    $link['link_url'] = $link_url;
                }

                // (varchar) the title of the link
                // 空だとwp_insert_link()で$link_name = $link_urlとなる
                $link_name = $h->get_data($this,$data,'link_name');
                if ($link_name) {
                    $link['link_name'] = $this->set_data($link_name);
                }

                // (varchar) a URL of an image
                $link_image = $h->get_data($this,$data,'link_image');
                if ($link_image) {
                    $link['link_image'] = $this->set_data($link_image);
                }

                // (varchar) the target element for the anchor tag
                $link_target = $h->get_data($this,$data,'link_target');
                if ($link_target) {
                    $link['link_target'] = $this->set_data($link_target);
                }

                // (varchar) a short description of the link
                $link_description = $h->get_data($this,$data,'link_description');
                if ($link_description) {
                    $link['link_description'] = $this->set_data($link_description);
                }

                // (text) an extended description of or notes on the link
                $link_notes = $h->get_data($this,$data,'link_notes');
                if ($link_notes) {
                    $link['link_notes'] = $this->set_data($link_notes);
                }

                // (int) the term ID of the link category. if empty, uses default link category
                $link_category = $h->get_data($this,$data,'link_category');
                if ($link_category) {
                    // wp_update_link() is required is_array( $link_cats )
                    $link['link_category'] = ($link_category !== 'BLANK')
                        ? explode(",", $link_category)
                        : array(); // wp_insert_link()にてデフォルトカテゴリーに設定される
                }

                /**
                 * Filter link data.
                 *
                 * @param array $link (required)
                 * @param bool $is_update
                 */
                $link = apply_filters( 'wp_bookmark_manager_save_link', $link, $is_update );

                /**
                 * Option for dry run testing
                 *
                 * @since 0.5.7
                 *
                 * @param bool false
                 */
                $dry_run = apply_filters( 'wp_bookmark_manager_dry_run', false );


                if (!$error->get_error_codes() && $dry_run == false) {

                    $result = $this->save_link($link,$is_update);

                    if ($result->isError()) {
                        $error = $result->getError();
                    } else {
                        $link_object = $result->getLink();

                        if (is_object($link_object)) {
                            /**
                             * Fires adter the link imported.
                             *
                             * @since 1.0
                             *
                             * @param WP_Link $link_object
                             */
                            do_action( 'wp_bookmark_manager_link_saved', $link_object );
                        }

                        echo esc_html(sprintf(__('ID "%s" : ', 'wp-bookmark-manager'), $link_id));
                        echo esc_html(sprintf(__('Processing "%s" done.', 'wp-bookmark-manager'), $link_name));
                    }
                }

                // show error messages
                foreach ($error->get_error_messages() as $message) {
                    echo esc_html($message).'<br>';
                }

                echo '</li>';
            }
        }

        echo '</ol>';

        $h->fclose($handle);

        wp_import_cleanup($this->id);

        echo '<h3>'.__('All Done.', 'wp-bookmark-manager').'</h3>';
    }

    // dispatcher
    function dispatch() {
        $this->header();

        if (empty ($_GET['step']))
            $step = 0;
        else
            $step = (int) $_GET['step'];

        switch ($step) {
            case 0 :
                $this->greet();
                break;
            case 1 :
                check_admin_referer('import-upload');
                set_time_limit(0);
                $result = $this->import();
                if ( is_wp_error( $result ) )
                    echo $result->get_error_message();
                break;
        }

        $this->footer();
    }
}

?>
