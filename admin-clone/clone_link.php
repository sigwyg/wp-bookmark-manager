<?php
require_once '../../../../wp-load.php';
require_once '../../../../wp-admin/includes/bookmark.php';
if ( is_user_logged_in() ) {
    wbm_save_as_new_link();
}

function wbm_save_as_new_link(){
    if (! ( isset( $_GET['link_id']) || isset( $_POST['link_id']) ) ) {
        wp_die(esc_html__('No link to duplicate has been supplied!', 'wp-bookmark-manager'));
    }
    check_admin_referer( 'bookmark_clone' );

    // Get the original link
    $id = (isset($_GET['link_id']) ? $_GET['link_id'] : $_POST['link_id']);
    $link = get_bookmark($id);

    // Copy the link and insert it
    if (isset($link) && $link != null) {
        $new_id = wbm_create_clone($link);

        $sendback = wp_get_referer();
        if (! $sendback || strpos( $sendback, 'link-manager.php' ) !== false) {
            $sendback = admin_url( 'link-manager.php' );
        } else {
            $sendback = remove_query_arg( array('trashed', 'untrashed', 'deleted', 'cloned', 'ids'), $sendback );
        }
        // Redirect to the post list screen
        wp_redirect( add_query_arg( array( 'cloned' => $new_id, 'page' => 'wp-bookmark-manager-clone'), $sendback ) );

        exit;
    } else {
        wp_die(esc_html__('Copy creation failed, could not find original:', 'wp-bookmark-manager') . ' ' . htmlspecialchars($id));
    }
}

/**
 * Create a clone from a link
 */
function wbm_create_clone($link) {

    $new_linkdata = array(
        'link_category'    => $link->link_category,
        'link_rss'         => $link->link_rss,
        'link_notes'       => $link->link_notes,
        'link_description' => $link->link_description,
        'link_target'      => $link->link_target,
        'link_image'       => $link->link_image,
        'link_url'         => $link->link_url,
        'link_name'        => $link->link_name
    );

    $new_link_id = wp_insert_link(wp_slash($new_linkdata));

    return $new_link_id;
}

?>
