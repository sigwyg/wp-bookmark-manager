<?php
?>
<div class="wrap">

    <h1><?php _e( 'Clone Link', 'wp-bookmark-manager' ) ?></h1>
    <p><?php _e( 'Please input a link_id that becomes a source of a new link.', 'wp-bookmark-manager' ) ?></p>
    <?php if ( isset($_GET['cloned']) && !empty($_GET['cloned']) && is_numeric($_GET['cloned']) ) : ?>
    <p class="wbm_notice"><?php printf(__('Copied to <var>link_id</var>=<var>%s</var>.', 'wp-bookmark-manager' ), esc_html( $_GET['cloned'] ) ) ?><a href="<?php echo admin_url('link.php?action=edit&link_id='.$_GET['cloned']); ?>">[<?php _e('Edit') ?>]</a></p>
    <?php endif; ?>

    <form action="<?php echo plugin_dir_url( __FILE__ ).'clone_link.php'; ?>" method="post" id="form_link">
    <?php wp_nonce_field( 'bookmark_clone' );?>
        <p class="submit">
            <input type="number" name="link_id" placeholder="100" />
            <input type="submit" class="button-primary" value="<?php _e( 'Clone Link', 'wp-bookmark-manager' ) ?>">
        </p>
    </form>
</div><!-- /.plugin-main-area -->

</div>
