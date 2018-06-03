<?php
$temp_dir = dirname(__FILE__).'/temp/';
?>
<div class="wrap">
<h1><?php _e( 'Export to CSV', 'wp-bookmark-manager' ) ?></h1>
<p><?php _e( 'Please set the fields you would like to export with CSV.', 'wp-bookmark-manager' ) ?></p>

<?php if ( !is_writable( $temp_dir ) ) : ?>
    <div class="wbm_error">
        <p>
            <?php _e( 'Please adjust your permissions so that you are able to edit the below directory.', 'wp-bookmark-manager' ) ?><br>
            <strong><?php echo $temp_dir; ?></strong>
        </p>
    </div>
<?php endif; ?>


<form action="<?php echo plugin_dir_url( __FILE__ ).'download.php'; ?>" method="post" id="form_link" target="_blank">
    <?php wp_nonce_field( 'bookmark_exporter' );?>
    <ul>
        <li><label><input type="radio" name="link_id" value="link_id" checked="checked" required>*<?php _e( 'Link ID', 'wp-bookmark-manager' ) ?></label></li>
        <li><label><input type="checkbox" name="links_values[]" value="link_url" checked="checked"><?php _e( 'URL', 'wp-bookmark-manager' ) ?></label></li>
        <li><label><input type="checkbox" name="links_values[]" value="link_name" checked="checked"><?php _e( 'Name', 'wp-bookmark-manager' ) ?></label></li>
        <li><label><input type="checkbox" name="links_values[]" value="link_image"><?php _e( 'Image URL', 'wp-bookmark-manager' ) ?></label></li>
        <li><label><input type="checkbox" name="links_values[]" value="link_target"><?php _e( 'Target', 'wp-bookmark-manager' ) ?></label></li>
        <li><label><input type="checkbox" name="links_values[]" value="link_description"><?php _e( 'Description', 'wp-bookmark-manager' ) ?></label></li>
        <li><label><input type="checkbox" name="links_values[]" value="link_notes"><?php _e( 'Notes', 'wp-bookmark-manager' ) ?></label></li>
        <li><label><input type="checkbox" name="link_category" value="link_category"><?php _e( 'Category', 'wp-bookmark-manager' ) ?></label></li>
    </ul>

    <hr>

    <table class="wbm_optionTable">
        <tr>
            <th><?php _e( 'Number of links to download.', 'wp-bookmark-manager' ) ?></th>
            <td><input type="number" name="limit" class="limit" value="0" data-target=".offset-link"> <?php _e( '*All downloaded if "0" selected.', 'wp-bookmark-manager' ) ?></td>
        </tr>
        <tr>
            <th><?php _e( 'Sorting by ID.', 'wp-bookmark-manager' ) ?></th>
            <td class="vt">
                <label><input type="radio" name="order_by" value="DESC" checked="checked"> <?php _e( 'DESC', 'wp-bookmark-manager' ) ?></label>
                <label><input type="radio" name="order_by" value="ASC"> <?php _e( 'ASC', 'wp-bookmark-manager' ) ?></label>
            </td>
        </tr>
        <tr>
            <th><?php _e( 'Select ID range.', 'wp-bookmark-manager' ) ?></th>
            <td>
            <label for="link_id_from">From</label>
            <input type="number" name="link_id_from" placeholder="100" />
            <label for="link_id_to">To</label>
            <input type="number" name="link_id_to" placeholder="200" />
            </td>
        </tr>
    </table>

    <p class="submit">
        <input type="submit" class="button button-primary" value="<?php _e( 'Export', 'wp-bookmark-manager' ) ?> CSV" <?php if ( !is_writable( $temp_dir ) ) : ?>disabled<?php endif; ?> />
    </p>
</form>

</div><!-- /.plugin-main-area -->
