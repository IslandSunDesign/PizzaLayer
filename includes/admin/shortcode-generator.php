<?php
/**
 * Render the Shortcode Generator page in the admin dashboard.
 */
function pztpro_shortcode_generator_page() {
    /* +=== Fetch all published crusts ===+ */
    $crusts   = get_posts( array(
        'post_type'      => 'pizzalayer_crusts',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    ) );
    /* +=== Fetch all published sauces ===+ */
    $sauces   = get_posts( array(
        'post_type'      => 'pizzalayer_sauces',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    ) );
    /* +=== Fetch all published cheeses ===+ */
    $cheeses  = get_posts( array(
        'post_type'      => 'pizzalayer_cheeses',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    ) );
    /* +=== Fetch all published toppings ===+ */
    $toppings = get_posts( array(
        'post_type'      => 'pizzalayer_toppings',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    ) );
    /* +=== Fetch all published drizzles ===+ */
    $drizzles = get_posts( array(
        'post_type'      => 'pizzalayer_drizzles',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    ) );
    /* +=== Fetch all published cuts ===+ */
    $slices   = get_posts( array(
        'post_type'      => 'pizzalayer_cuts',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    ) );

    echo '<div class="wrap">';
    echo '<h2>Create an embed code to display your pizza</h2>';
    echo '<form id="pztpro_shortcode_generator">';

    /* +=== Crust selector ===+ */
    echo '<label for="pztpro_crust">Crust:</label><br>';
    echo '<select id="pztpro_crust" name="crust" class="widefat">';
    echo '<option value="">— Select Crust —</option>';
    foreach ( $crusts as $c ) {
        echo '<option value="' . esc_attr( $c->post_title ) . '">' . esc_html( $c->post_title ) . '</option>';
    }
    echo '</select><br><br>';

    /* +=== Sauce selector ===+ */
    echo '<label for="pztpro_sauce">Sauce:</label><br>';
    echo '<select id="pztpro_sauce" name="sauce" class="widefat">';
    echo '<option value="">— Select Sauce —</option>';
    foreach ( $sauces as $s ) {
        echo '<option value="' . esc_attr( $s->post_title ) . '">' . esc_html( $s->post_title ) . '</option>';
    }
    echo '</select><br><br>';

    /* +=== Cheese selector ===+ */
    echo '<label for="pztpro_cheese">Cheese:</label><br>';
    echo '<select id="pztpro_cheese" name="cheese" class="widefat">';
    echo '<option value="">— Select Cheese —</option>';
    foreach ( $cheeses as $ch ) {
        echo '<option value="' . esc_attr( $ch->post_title ) . '">' . esc_html( $ch->post_title ) . '</option>';
    }
    echo '</select><br><br>';

    /* +=== Toppings multi-select ===+ */
    echo '<label for="pztpro_toppings">Toppings:</label><br>';
    echo '<select id="pztpro_toppings" name="toppings[]" class="widefat" multiple>';
    foreach ( $toppings as $t ) {
        echo '<option value="' . esc_attr( $t->post_title ) . '">' . esc_html( $t->post_title ) . '</option>';
    }
    echo '</select><br><br>';

    /* +=== Drizzle selector ===+ */
    echo '<label for="pztpro_drizzle">Drizzle:</label><br>';
    echo '<select id="pztpro_drizzle" name="drizzle" class="widefat">';
    echo '<option value="">— Select Drizzle —</option>';
    foreach ( $drizzles as $d ) {
        echo '<option value="' . esc_attr( $d->post_title ) . '">' . esc_html( $d->post_title ) . '</option>';
    }
    echo '</select><br><br>';

    /* +=== Slices selector ===+ */
    echo '<label for="pztpro_slices">Slices:</label><br>';
    echo '<select id="pztpro_slices" name="slices" class="widefat">';
    echo '<option value="">— Select Slices —</option>';
    foreach ( $slices as $sl ) {
        echo '<option value="' . esc_attr( $sl->post_title ) . '">' . esc_html( $sl->post_title ) . '</option>';
    }
    echo '</select><br><br>';

    echo '</form>';

    /* +=== Output textarea and copy button ===+ */
    echo '<h2>Generated Shortcode</h2>';
    echo '<textarea id="pztpro_shortcode_output" class="widefat" rows="2" readonly></textarea><br><br>';
    echo '<button id="pztpro_copy_button" class="button button-primary">Copy to Clipboard</button>';

    /* +=== Inline JavaScript to build and copy shortcode ===+ */
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
            // handle toppings separately (multi-select)
            let tops = Array.from($toppings.selectedOptions).map(o => o.value);
            if (tops.length) {
                attrs.push('toppings="' + tops.join(',') + '"');
            }
            const short = '[pizzalayer-static ' + attrs.join(' ') + ']';
            $output.value = short;
        }

        // Watch all inputs for change
        document.getElementById('pztpro_shortcode_generator').addEventListener('change', buildShortcode);

        // Copy button handler
        document.getElementById('pztpro_copy_button').addEventListener('click', function(e){
            e.preventDefault();
            $output.select();
            document.execCommand('copy');
        });

        // initialize
        buildShortcode();
    })();
    </script>
    <?php

    echo '</div>';
}