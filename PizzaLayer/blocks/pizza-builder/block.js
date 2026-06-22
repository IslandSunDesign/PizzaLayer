/**
 * PizzaLayer — Pizza Builder Block
 *
 * Pure vanilla JS / wp.element / wp.components — no JSX, no NPM required.
 * Registered via block.json; rendered server-side by BlockRegistrar::render_builder().
 */
( function ( blocks, element, components, blockEditor, i18n ) {
    'use strict';

    var el               = element.createElement;
    var __               = i18n.__;
    var registerBlockType = blocks.registerBlockType;
    var InspectorControls = blockEditor.InspectorControls;
    var useBlockProps     = blockEditor.useBlockProps;
    var ServerSideRender  = wp.serverSideRender;

    var PanelBody       = components.PanelBody;
    var TextControl     = components.TextControl;
    var SelectControl   = components.SelectControl;
    var CheckboxControl = components.CheckboxControl;
    var Spinner         = components.Spinner;
    var Placeholder     = components.Placeholder;

    /* ── Shape options ─────────────────────────────────────────── */
    var SHAPE_OPTIONS = [
        { value: '',          label: __( '— Site default —',                'pizzalayer' ) },
        { value: 'round',     label: __( 'Round (circle)',                   'pizzalayer' ) },
        { value: 'square',    label: __( 'Square (rounded corners)',         'pizzalayer' ) },
        { value: 'rectangle', label: __( 'Rectangle / custom ratio',        'pizzalayer' ) },
        { value: 'custom',    label: __( 'Custom (aspect ratio + radius)',   'pizzalayer' ) },
    ];

    /* ── Animation options ─────────────────────────────────────── */
    var ANIM_OPTIONS = [
        { value: '',         label: __( '— Site default —',      'pizzalayer' ) },
        { value: 'fade',     label: __( 'Fade In',               'pizzalayer' ) },
        { value: 'scale-in', label: __( 'Scale In (pop)',        'pizzalayer' ) },
        { value: 'slide-up', label: __( 'Slide Up',              'pizzalayer' ) },
        { value: 'flip-in',  label: __( 'Flip In (3-D rotate)',  'pizzalayer' ) },
        { value: 'drop-in',  label: __( 'Drop In (from above)',  'pizzalayer' ) },
        { value: 'instant',  label: __( 'Instant (no animation)','pizzalayer' ) },
    ];

    /* ── Tab options ───────────────────────────────────────────── */
    var ALL_TABS = [
        { value: 'crust',     label: __( 'Crust',     'pizzalayer' ) },
        { value: 'sauce',     label: __( 'Sauce',     'pizzalayer' ) },
        { value: 'cheese',    label: __( 'Cheese',    'pizzalayer' ) },
        { value: 'toppings',  label: __( 'Toppings',  'pizzalayer' ) },
        { value: 'drizzle',   label: __( 'Drizzle',   'pizzalayer' ) },
        { value: 'slicing',   label: __( 'Slicing',   'pizzalayer' ) },
        { value: 'yourpizza', label: __( 'Your Pizza','pizzalayer' ) },
    ];

    /* ── Helpers: comma list ↔ Set ─────────────────────────────── */
    function listToSet( str ) {
        return new Set( ( str || '' ).split( ',' ).map( function ( s ) { return s.trim(); } ).filter( Boolean ) );
    }
    function setToList( set ) {
        return Array.from( set ).join( ',' );
    }

    /* ══════════════════════════════════════════════════════════════
       BLOCK REGISTRATION
       ══════════════════════════════════════════════════════════════ */
    registerBlockType( 'pizzalayer/pizza-builder', {

        edit: function ( props ) {
            var attributes    = props.attributes;
            var setAttributes = props.setAttributes;

            var blockProps = useBlockProps( {
                className: 'pizzalayer-block-wrap',
                style: { minHeight: '120px' }
            } );

            /* Hidden-tabs checkbox state */
            var hiddenSet = listToSet( attributes.hideTabs );
            function toggleTab( tabValue, checked ) {
                var next = new Set( hiddenSet );
                if ( checked ) { next.delete( tabValue ); } else { next.add( tabValue ); }
                setAttributes( { hideTabs: setToList( next ) } );
            }

            var currentShape = attributes.pizzaShape || '';

            /* ── Inspector panels ───────────────────────────────── */
            var inspectorPanel = el( InspectorControls, null,

                /* 1. Builder Settings */
                el( PanelBody, { title: __( 'Builder Settings', 'pizzalayer' ), initialOpen: true },

                    el( TextControl, {
                        label:    __( 'Instance ID', 'pizzalayer' ),
                        help:     __( 'Leave blank to auto-generate. Set explicitly when placing two builders on the same page.', 'pizzalayer' ),
                        value:    attributes.instanceId,
                        onChange: function ( v ) { setAttributes( { instanceId: v } ); }
                    } ),

                    el( TextControl, {
                        label:    __( 'Template slug', 'pizzalayer' ),
                        help:     __( 'Override the active template for this block only, e.g. "nightpie".', 'pizzalayer' ),
                        value:    attributes.template,
                        onChange: function ( v ) { setAttributes( { template: v } ); }
                    } ),

                    el( TextControl, {
                        label:    __( 'Max toppings', 'pizzalayer' ),
                        help:     __( 'Override the global max toppings limit for this builder.', 'pizzalayer' ),
                        type:     'number',
                        value:    attributes.maxToppings,
                        onChange: function ( v ) { setAttributes( { maxToppings: v } ); }
                    } )
                ),

                /* 2. Pizza Shape */
                el( PanelBody, { title: __( 'Pizza Shape', 'pizzalayer' ), initialOpen: false },

                    el( SelectControl, {
                        label:    __( 'Shape', 'pizzalayer' ),
                        help:     __( 'Overrides the site-wide shape for this block only.', 'pizzalayer' ),
                        value:    attributes.pizzaShape,
                        options:  SHAPE_OPTIONS,
                        onChange: function ( v ) { setAttributes( { pizzaShape: v } ); }
                    } ),

                    ( currentShape === 'rectangle' || currentShape === 'custom' )
                        ? el( TextControl, {
                            label:    __( 'Aspect ratio', 'pizzalayer' ),
                            help:     __( 'CSS aspect-ratio value, e.g. "4 / 3" or "16 / 9".', 'pizzalayer' ),
                            value:    attributes.pizzaAspect,
                            onChange: function ( v ) { setAttributes( { pizzaAspect: v } ); }
                          } )
                        : null,

                    currentShape === 'custom'
                        ? el( TextControl, {
                            label:    __( 'Border radius', 'pizzalayer' ),
                            help:     __( 'CSS border-radius, e.g. "12px" or "50%".', 'pizzalayer' ),
                            value:    attributes.pizzaRadius,
                            onChange: function ( v ) { setAttributes( { pizzaRadius: v } ); }
                          } )
                        : null
                ),

                /* 3. Layer Animation */
                el( PanelBody, { title: __( 'Layer Animation', 'pizzalayer' ), initialOpen: false },

                    el( SelectControl, {
                        label:    __( 'Animation style', 'pizzalayer' ),
                        help:     __( 'Animation when a layer is added to the pizza. Overrides the site-wide setting for this block.', 'pizzalayer' ),
                        value:    attributes.layerAnim,
                        options:  ANIM_OPTIONS,
                        onChange: function ( v ) { setAttributes( { layerAnim: v } ); }
                    } )
                ),

                /* 4. Default Layers */
                el( PanelBody, { title: __( 'Default Layers', 'pizzalayer' ), initialOpen: false },

                    el( TextControl, {
                        label:    __( 'Default crust slug', 'pizzalayer' ),
                        help:     __( 'e.g. "thin-crust" — pre-selects on load.', 'pizzalayer' ),
                        value:    attributes.defaultCrust,
                        onChange: function ( v ) { setAttributes( { defaultCrust: v } ); }
                    } ),

                    el( TextControl, {
                        label:    __( 'Default sauce slug', 'pizzalayer' ),
                        value:    attributes.defaultSauce,
                        onChange: function ( v ) { setAttributes( { defaultSauce: v } ); }
                    } ),

                    el( TextControl, {
                        label:    __( 'Default cheese slug', 'pizzalayer' ),
                        value:    attributes.defaultCheese,
                        onChange: function ( v ) { setAttributes( { defaultCheese: v } ); }
                    } )
                ),

                /* 5. Visible Tabs */
                el( PanelBody, { title: __( 'Visible Tabs', 'pizzalayer' ), initialOpen: false },

                    el( 'p', { style: { margin: '0 0 8px', fontSize: '12px', color: '#757575' } },
                        __( 'Uncheck tabs to hide them from the builder.', 'pizzalayer' )
                    ),

                    ALL_TABS.map( function ( tab ) {
                        return el( CheckboxControl, {
                            key:      tab.value,
                            label:    tab.label,
                            checked:  ! hiddenSet.has( tab.value ),
                            onChange: function ( checked ) { toggleTab( tab.value, checked ); }
                        } );
                    } )
                )
            );

            /* ── Server-side live preview ──────────────────────── */
            return el( 'div', blockProps,
                inspectorPanel,
                el( ServerSideRender, {
                    block:      'pizzalayer/pizza-builder',
                    attributes: attributes,
                    EmptyResponsePlaceholder: function () {
                        return el( Placeholder, {
                            icon:  'pizza-slice',
                            label: __( 'Pizza Builder', 'pizzalayer' ),
                        }, el( Spinner ) );
                    },
                    ErrorResponsePlaceholder: function ( p ) {
                        return el( Placeholder, {
                            icon:  'warning',
                            label: __( 'Pizza Builder — Preview Error', 'pizzalayer' ),
                        }, el( 'p', null,
                            p.response && p.response.message
                                ? p.response.message
                                : __( 'Could not render preview.', 'pizzalayer' )
                        ) );
                    }
                } )
            );
        },

        save: function () {
            /* Dynamic block — rendered server-side */
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
