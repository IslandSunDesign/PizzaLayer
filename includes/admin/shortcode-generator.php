<?php
/**
 * Render the Shortcode Generator page in the admin dashboard.
 */
function pztpro_shortcode_generator_page() {
    // +=============================+
    // | Fetch PizzaLayer CPT Items |
    // +=============================+
    $crusts   = get_posts(['post_type' => 'pizzalayer_crusts',   'posts_per_page' => -1, 'post_status' => 'publish']);
    $sauces   = get_posts(['post_type' => 'pizzalayer_sauces',   'posts_per_page' => -1, 'post_status' => 'publish']);
    $cheeses  = get_posts(['post_type' => 'pizzalayer_cheeses',  'posts_per_page' => -1, 'post_status' => 'publish']);
    $toppings = get_posts(['post_type' => 'pizzalayer_toppings', 'posts_per_page' => -1, 'post_status' => 'publish']);
    $drizzles = get_posts(['post_type' => 'pizzalayer_drizzles', 'posts_per_page' => -1, 'post_status' => 'publish']);
    $slices   = get_posts(['post_type' => 'pizzalayer_cuts',     'posts_per_page' => -1, 'post_status' => 'publish']);

    echo '<div class="wrap">';
    echo '<h1>Create an embed code to display your pizza</h1>';

    // +==============================+
    // | Main Selection Field Layout |
    // +==============================+
    echo '<div class="postbox" style="padding: 20px; margin-bottom: 30px;">';
    echo '<form id="pztpro_shortcode_generator">';
    echo '<div style="display:flex; flex-wrap:wrap; gap:20px;">';

    // Left Column (crust, sauce, cheese, drizzle, slices)
    echo '<div style="flex:1; min-width:300px;">';

    $render_field = function( $label, $id, $items, $multiple = false ) {
        $multiple_attr = $multiple ? ' multiple' : '';
        $name_attr = $multiple ? $id . '[]' : $id;
        $placeholder = $multiple ? '' : '<option value="">— Select ' . $label . ' —</option>';

        echo '<div style="margin-bottom:20px; padding:15px; background:#f9f9f9; border:1px solid #ccd0d4;">';
        echo '<label for="pztpro_' . esc_attr( $id ) . '"><strong>' . esc_html( $label ) . ':</strong></label><br>';
        echo '<select id="pztpro_' . esc_attr( $id ) . '" name="' . esc_attr( $name_attr ) . '" class="widefat" ' . $multiple_attr . '>';
        echo $placeholder;
        foreach ( $items as $item ) {
            echo '<option value="' . esc_attr( $item->post_title ) . '">' . esc_html( $item->post_title ) . '</option>';
        }
        echo '</select>';
        echo '</div>';
    };

    $render_field( 'Crust',   'crust',   $crusts );
    $render_field( 'Sauce',   'sauce',   $sauces );
    $render_field( 'Cheese',  'cheese',  $cheeses );
    $render_field( 'Drizzle', 'drizzle', $drizzles );
    $render_field( 'Slices',  'slices',  $slices );

    echo '</div>'; // end left column

    // Right Column (Toppings full height)
    echo '<div style="flex:1; min-width:300px; display:flex; flex-direction:column;">';
    echo '<div style="flex:1; padding:15px; background:#f9f9f9; border:1px solid #ccd0d4; height:100%;">';
    echo '<label for="pztpro_toppings"><strong>Toppings:</strong></label><br>';
    echo '<select id="pztpro_toppings" name="toppings[]" class="widefat" multiple style="min-height:220px;">';
    foreach ( $toppings as $t ) {
        echo '<option value="' . esc_attr( $t->post_title ) . '">' . esc_html( $t->post_title ) . '</option>';
    }
    echo '</select>';
    echo '</div>';
    echo '</div>'; // end right column

    echo '</div>'; // end flex container
    echo '</form>';
    echo '</div>'; // end postbox

    // +===============================+
    // | Shortcode Output & Copy Box  |
    // +===============================+
    echo '<div class="postbox" style="padding: 20px;">';
    echo '<h2 class="hndle">Generated Shortcode</h2>';
    echo '<div style="display:flex; align-items:center; gap:10px;">';
    echo '<textarea id="pztpro_shortcode_output" class="widefat" rows="2" readonly style="margin:0;"></textarea>';
    echo '<button id="pztpro_copy_button" class="button" type="button" title="Copy to Clipboard" style="height:34px;"><span class="dashicons dashicons-clipboard"></span></button>';
    echo '</div>';
    echo '</div>';

    // +=============================+
    // | JavaScript for Shortcode   |
    // +=============================+
    ?>
    <script>
    (function(){
        const fields = ['crust','sauce','cheese','drizzle','slices'];
        const $output = document.getElementById('pztpro_shortcode_output');
        const $toppings = document.getElementById('pztpro_toppings');

        function buildShortcode() {
            let attrs = [];
            fields.forEach(function(name){
                const el = document.getElementById('pztpro_' + name);
                if (el && el.value) {
                    attrs.push(name + '="' + el.value + '"');
                }
            });
            let tops = Array.from($toppings.selectedOptions).map(o => o.value);
            if (tops.length) {
                attrs.push('toppings="' + tops.join(',') + '"');
            }
            const short = '[pizzalayer-static ' + attrs.join(' ') + ']';
            $output.value = short;
        }

        document.getElementById('pztpro_shortcode_generator').addEventListener('change', buildShortcode);

        document.getElementById('pztpro_copy_button').addEventListener('click', function(e){
            e.preventDefault();
            $output.select();
            document.execCommand('copy');
        });

        buildShortcode();
    })();
    </script>
    <?php

    echo '</div>'; // wrap
}

