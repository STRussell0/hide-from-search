<div class="wrap">
    <h2>Post Type Controls</h2>
    <form method="post">
        <?php wp_nonce_field('hps_save_post_types'); ?>
        <table class="form-table">
            <tbody>
                <?php foreach ($post_types as $post_type): ?>
                    <tr>
                        <th scope="row"><?php echo esc_html($post_type->labels->name); ?></th>
                        <td>
                            <input type="checkbox" name="hps_post_types[]" value="<?php echo esc_attr($post_type->name); ?>" 
                                <?php checked(in_array($post_type->name, $enabled_post_types)); ?>>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php submit_button('Save Settings'); ?>
    </form>
</div>