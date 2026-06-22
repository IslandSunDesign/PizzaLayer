<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
do_action( 'pizzalayer_file_pztp-containers-presentation_start' );

$pizzalayer_template_images_directory = plugin_dir_url( __FILE__ ) . 'images/';

/*
 * Metro template — [pizzalayer-visualizer] shortcode.
 * The live pizza is rendered inline above the builder sections;
 * no separate visualizer shortcode output is needed for the Metro layout.
 */

do_action( 'pizzalayer_file_pztp-containers-presentation_end' );
