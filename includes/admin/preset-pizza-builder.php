<?php
do_action( 'pizzalayer_file_preset-pizza-builder_before' );
/* +=== Register the three meta boxes: Preset, Shortcode & Live Preview ===+ */
add_action( 'add_meta_boxes', 'pizzalayer_register_metaboxes' );
function pizzalayer_register_metaboxes() {
    do_action( 'func_pizzalayer_register_metaboxes_start' );
    add_meta_box(
        'pizzalayer_preset_metabox',
        __( 'Build your Pizza Preset', 'textdomain' ),
        'pizzalayer_render_preset_metabox',
        'pizzalayer_pizzas',
        'normal',
        'high'
    );
    add_meta_box(
        'pizzalayer_shortcode_metabox',
        __( 'Pizza Shortcode', 'textdomain' ),
        'pizzalayer_render_shortcode_metabox',
        'pizzalayer_pizzas',
        'side',
        'default'
    );
    add_meta_box(
        'pizzalayer_live_preview_metabox',
        __( 'Live Preview', 'textdomain' ),
        'pizzalayer_render_live_preview_metabox',
        'pizzalayer_pizzas',
        'side',
        'default'
    );
    do_action( 'func_pizzalayer_register_metaboxes_end' );
}

/* +=== Render the preset-selection panel with two nonces (save + preview) ===+ */
function pizzalayer_render_preset_metabox( $post ) {
    do_action( 'func_pizzalayer_render_preset_metabox_start' );
    wp_nonce_field( 'pizzalayer_save_preset_metabox',    'pizzalayer_preset_nonce'    );
    wp_nonce_field( 'pizzalayer_preview_nonce',         'pizzalayer_preview_nonce'   );

    $fields = array(
        'crust'    => ['label'=>'Crust',    'cpt'=>'pizzalayer_crusts',    'meta_key'=>'preset_chosen_crust',    'single'=>true,  'img'=>'crust_image'],
        'sauce'    => ['label'=>'Sauce',    'cpt'=>'pizzalayer_sauces',    'meta_key'=>'preset_chosen_sauce',    'single'=>true,  'img'=>'sauce_image'],
        'cheese'   => ['label'=>'Cheese',   'cpt'=>'pizzalayer_cheeses',   'meta_key'=>'preset_chosen_cheese',   'single'=>true,  'img'=>'cheese_image'],
        'toppings' => ['label'=>'Toppings','cpt'=>'pizzalayer_toppings', 'meta_key'=>'preset_chosen_toppings','single'=>false, 'img'=>'toppings_image'],
        'drizzle'  => ['label'=>'Drizzle',  'cpt'=>'pizzalayer_drizzles',  'meta_key'=>'preset_chosen_drizzle','single'=>true,  'img'=>'drizzle_image'],
        'cut'      => ['label'=>'Slices',   'cpt'=>'pizzalayer_cuts',      'meta_key'=>'preset_chosen_cut',     'single'=>true,  'img'=>'cut_image'],
    );

    foreach ( $fields as $key => $args ) {
        $saved = get_post_meta( $post->ID, $args['meta_key'], true );
        if ( ! $args['single'] && $saved ) {
            $saved = explode( ',', $saved );
        }
        echo '<h4>'. esc_html( $args['label'] ) .'</h4>';
        echo '<div class="pizzalayer-preset-grid">';
        $items = get_posts([
            'post_type'      => $args['cpt'],
            'posts_per_page' => -1,
            'post_status'    => 'publish',
        ]);
        foreach ( $items as $item ) {
            $title       = $item->post_title;
            $summary     = wp_trim_words( $item->post_content, 10, '…' );
            $img_id      = get_post_meta( $item->ID, $args['img'], true );
            $img_url     = $img_id ? wp_get_attachment_image_url( $img_id, 'thumbnail' ) : '';
            $is_selected = $args['single']
                ? ( $saved === $title )
                : in_array( $title, (array) $saved );
            $name_attr   = $args['single']
                ? $args['meta_key']
                : $args['meta_key'] . '[]';
            $type        = $args['single'] ? 'radio' : 'checkbox';
            $id_attr     = 'pizzalayer_'. $args['meta_key'] .'_'. $item->ID;

            echo '<label for="'. esc_attr($id_attr) .'" class="pizzalayer-preset-item'. ( $is_selected ? ' selected' : '' ) .'">';
            printf(
                '<input type="%1$s" name="%2$s" value="%3$s" id="%4$s" data-meta-key="%2$s" %5$s />',
                $type,
                esc_attr( $name_attr ),
                esc_attr( $title ),
                esc_attr( $id_attr ),
                checked( true, $is_selected, false )
            );
            if ( $img_url ) {
                printf( '<img src="%s" alt="%s" />', esc_url( $img_url ), esc_attr( $title ) );
            }
            printf(
                '<strong>%s</strong><p>%s</p>',
                esc_html( $title ),
                esc_html( $summary )
            );
            echo '</label>';
        }
        echo '</div>';
    }

    /* +=== Inline styles ===+ */
    ?>
    <style>
    .pizzalayer-preset-grid {
        display:grid;
        grid-template-columns:repeat(4,1fr);
        gap:8px;
        margin-bottom:16px;
    }
    .pizzalayer-preset-item {
        border:1px solid #ccd0d4;
        padding:8px;
        border-radius:4px;
        position:relative;
        text-align:center;
        cursor:pointer;
        background:#fff;
    }
    .pizzalayer-preset-item.selected {
        border-color:#007cba;
        background:#f0f8ff;
    }
    .pizzalayer-preset-item img {
        max-width:100%;
        height:auto;
        margin-bottom:4px;
    }
    .pizzalayer-preset-item input {
        position:absolute;
        top:6px;
        left:6px;
    }
    </style>

    <?php /* +=== Inline script to update shortcode & live preview ===+ */ ?>
    <script>
    (function($){
        function updateShortcode(){
            var crust   = $('input[name="preset_chosen_crust"]:checked').val()   || '';
            var sauce   = $('input[name="preset_chosen_sauce"]:checked').val()   || '';
            var cheese  = $('input[name="preset_chosen_cheese"]:checked').val()  || '';
            var drizzle = $('input[name="preset_chosen_drizzle"]:checked').val() || '';
            var slices  = $('input[name="preset_chosen_cut"]:checked').val()      || '';
            var tops    = $('input[name="preset_chosen_toppings[]"]:checked')
                             .map(function(){ return this.value; }).get().join(',');
            var sc = '[pizzalayer-static'
                   + ' crust="'+ crust +'"'
                   + ' sauce="'+ sauce +'"'
                   + ' cheese="'+ cheese +'"'
                   + ' drizzle="'+ drizzle +'"'
                   + ' slices="'+ slices +'"'
                   + ( tops ? ' toppings="'+ tops +'"' : '' )
                   + ']';

            $('#pizzalayer_shortcode_output').val(sc);

            // AJAX live preview
            $.post( ajaxurl, {
                action: 'pizzalayer_preview_shortcode',
                shortcode: sc,
                pizzalayer_preview_nonce: $('input[name="pizzalayer_preview_nonce"]').val()
            }, function(response){
                $('#pizzalayer_live_preview').html(response);
            });
        }

        $(document).ready(function(){
            $('.pizzalayer-preset-grid input').on('change', function(){
                var $lbl = $(this).closest('label');
                if ( this.type === 'radio' ) {
                    $('input[name="'+ this.name +'"]').closest('label').removeClass('selected');
                    $lbl.addClass('selected');
                } else {
                    $lbl.toggleClass('selected', this.checked);
                }
                updateShortcode();
            });
            updateShortcode();
        });
    })(jQuery);
    </script>
    do_action( 'func_pizzalayer_render_preset_metabox_end' );
    <?php
}

/* +=== Render the dynamic shortcode panel ===+ */
function pizzalayer_render_shortcode_metabox( $post ) {
    do_action( 'func_pizzalayer_render_shortcode_metabox_start' );
    echo '<p>'. esc_html__( 'Copy and paste this shortcode:', 'textdomain' ) .'</p>';
    echo '<textarea id="pizzalayer_shortcode_output" class="widefat code" rows="2" readonly></textarea>';
    do_action( 'func_pizzalayer_render_shortcode_metabox_end' );
}

/* +=== Render the live preview panel ===+ */
function pizzalayer_render_live_preview_metabox( $post ) {
    do_action( 'func_pizzalayer_render_live_preview_metabox_start' );
    echo '<p>'. esc_html__( 'Preview of your pizza preset:', 'textdomain' ) .'</p>';
    echo '<div id="pizzalayer_live_preview" style="border:1px solid #ccd0d4; padding:8px; min-height:80px; background:#fff;"></div>';
    do_action( 'func_pizzalayer_render_live_preview_metabox_end' );
}

/* +=== AJAX handler for live preview ===+ */
add_action( 'wp_ajax_pizzalayer_preview_shortcode', 'pizzalayer_ajax_preview_shortcode' );
function pizzalayer_ajax_preview_shortcode() {
    do_action( 'func_pizzalayer_ajax_preview_shortcode_start' );
    if ( empty( pizzalayer_sanitize_text($_POST['pizzalayer_preview_nonce']) )
      || ! wp_verify_nonce( $_POST['pizzalayer_preview_nonce'], 'pizzalayer_preview_nonce' ) ) {
        wp_die( 'Invalid nonce' );
    }
    $sc = sanitize_text_field( wp_unslash( $_POST['shortcode'] ) );
    echo do_shortcode( $sc );
    do_action( 'func_pizzalayer_ajax_preview_shortcode_end' );
    wp_die();
}

/* +=== Save the preset data on post save ===+ */
add_action( 'save_post_pizzalayer_pizzas', 'pizzalayer_save_preset_metabox', 10, 2 );
function pizzalayer_save_preset_metabox( $post_id, $post ) {
    do_action( 'func_pizzalayer_save_preset_metabox_start' );
    if ( empty( pizzalayer_sanitize_text($_POST['pizzalayer_preset_nonce']) )
      || ! wp_verify_nonce( pizzalayer_sanitize_text($_POST['pizzalayer_preset_nonce']), 'pizzalayer_save_preset_metabox' )
      || ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )
      || ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    // save exclusive fields
    $singles = [
        'preset_chosen_crust',
        'preset_chosen_sauce',
        'preset_chosen_cheese',
        'preset_chosen_drizzle',
        'preset_chosen_cut',
    ];
    foreach ( $singles as $key ) {
        if ( isset( $_POST[ $key ] ) ) {
            update_post_meta( $post_id, $key, sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) );
        } else {
            delete_post_meta( $post_id, $key );
        }
    }

    // save multi-select toppings
    if ( ! empty( $_POST['preset_chosen_toppings'] ) && is_array( $_POST['preset_chosen_toppings'] ) ) {
        $clean = array_map( 'sanitize_text_field', wp_unslash( $_POST['preset_chosen_toppings'] ) );
        update_post_meta( $post_id, 'preset_chosen_toppings', implode( ',', $clean ) );
    } else {
        delete_post_meta( $post_id, 'preset_chosen_toppings' );
    }
    do_action( 'func_pizzalayer_save_preset_metabox_end' );
}
do_action( 'pizzalayer_file_preset-pizza-builder_after' );