/**
 * PizzaLayer — PizzaLayer Image Block
 *
 * Pure vanilla JS / wp.element / wp.components — no JSX, no NPM required.
 * Renders server-side via BlockRegistrar::render_layer().
 */
( function ( blocks, element, components, blockEditor, i18n ) {
    'use strict';

    var el          = element.createElement;
    var __          = i18n.__;

    var registerBlockType = blocks.registerBlockType;
    var InspectorControls = blockEditor.InspectorControls;
    var useBlockProps     = blockEditor.useBlockProps;
    var ServerSideRender  = wp.serverSideRender;

    var PanelBody     = components.PanelBody;
    var TextControl   = components.TextControl;
    var SelectControl = components.SelectControl;
    var Spinner       = components.Spinner;
    var Placeholder   = components.Placeholder;

    var LAYER_TYPES = [
        { value: 'crust',   label: __( 'Crust',   'pizzalayer' ) },
        { value: 'sauce',   label: __( 'Sauce',   'pizzalayer' ) },
        { value: 'cheese',  label: __( 'Cheese',  'pizzalayer' ) },
        { value: 'topping', label: __( 'Topping', 'pizzalayer' ) },
        { value: 'drizzle', label: __( 'Drizzle', 'pizzalayer' ) },
        { value: 'cut',     label: __( 'Cut',     'pizzalayer' ) },
    ];

    var IMAGE_FIELDS = [
        { value: 'list',  label: __( 'List image (menu/product photo)', 'pizzalayer' ) },
        { value: 'layer', label: __( 'Layer image (transparent stack image)', 'pizzalayer' ) },
    ];

    registerBlockType( 'pizzalayer/pizza-layer', {

        edit: function ( props ) {
            var attributes    = props.attributes;
            var setAttributes = props.setAttributes;

            var blockProps = useBlockProps( {
                className: 'pizzalayer-layer-block-wrap',
                style: { minHeight: '60px' }
            } );

            var inspectorPanel = el(
                InspectorControls,
                null,
                el( PanelBody, { title: __( 'Layer Settings', 'pizzalayer' ), initialOpen: true },

                    el( SelectControl, {
                        label:    __( 'Layer type', 'pizzalayer' ),
                        value:    attributes.layerType,
                        options:  LAYER_TYPES,
                        onChange: function ( v ) { setAttributes( { layerType: v } ); }
                    } ),

                    el( TextControl, {
                        label: __( 'Slug', 'pizzalayer' ),
                        help:  __( 'The post slug of the layer entry, e.g. "thin-crust" or "pepperoni".', 'pizzalayer' ),
                        value: attributes.slug,
                        onChange: function ( v ) { setAttributes( { slug: v } ); }
                    } ),

                    el( SelectControl, {
                        label:    __( 'Image field', 'pizzalayer' ),
                        value:    attributes.imageField,
                        options:  IMAGE_FIELDS,
                        onChange: function ( v ) { setAttributes( { imageField: v } ); }
                    } ),

                    el( TextControl, {
                        label: __( 'Extra CSS class', 'pizzalayer' ),
                        help:  __( 'Optional CSS class(es) added to the <img> tag.', 'pizzalayer' ),
                        value: attributes.cssClass,
                        onChange: function ( v ) { setAttributes( { cssClass: v } ); }
                    } )
                )
            );

            var preview;
            if ( attributes.slug && attributes.slug.trim() !== '' ) {
                preview = el( ServerSideRender, {
                    block:      'pizzalayer/pizza-layer',
                    attributes: attributes,
                    EmptyResponsePlaceholder: function () {
                        return el( Placeholder, { icon: 'format-image', label: __( 'PizzaLayer', 'pizzalayer' ) },
                            el( Spinner )
                        );
                    },
                    ErrorResponsePlaceholder: function ( p ) {
                        return el( Placeholder, { icon: 'warning', label: __( 'PizzaLayer — Error', 'pizzalayer' ) },
                            el( 'p', null, p.response && p.response.message
                                ? p.response.message
                                : __( 'Layer not found. Check the type and slug.', 'pizzalayer' ) )
                        );
                    }
                } );
            } else {
                /* No slug yet — show a helpful placeholder */
                var typeLabel = ( LAYER_TYPES.find( function ( t ) { return t.value === attributes.layerType; } ) || LAYER_TYPES[0] ).label;
                preview = el( Placeholder, {
                    icon:  'format-image',
                    label: __( 'PizzaLayer Image', 'pizzalayer' ),
                }, el( 'p', { style: { margin: 0 } },
                    /* translators: %s = layer type e.g. "Crust" */
                    __( 'Enter a %s slug in the settings panel →', 'pizzalayer' ).replace( '%s', typeLabel )
                ) );
            }

            return el( 'div', blockProps, inspectorPanel, preview );
        },

        save: function () {
            return null;
        }
    } );

} )(
    window.wp.blocks,
    window.wp.element,
    window.wp.components,
    window.wp.blockEditor,
    window.wp.i18n
);
