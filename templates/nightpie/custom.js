/* ══════════════════════════════════════════════════════════════
   NIGHTPIE TEMPLATE — custom.js
   Handles: tab switching, fly-to animation, exclusive/topping
   selection state, "Your Pizza" summary, mobile preview bar,
   progress dots, topping counter, PizzaLayer JS API bridge.
   ══════════════════════════════════════════════════════════════ */

(function ($) {
    'use strict';

    /* ──────────────────────────────────────────────────────────
       STATE
    ────────────────────────────────────────────────────────── */
    var state = {
        crust:    null,   // { slug, title, thumb, layerImg }
        sauce:    null,
        cheese:   null,
        drizzle:  null,
        cut:      null,
        toppings: {}      // keyed by slug: { slug, title, thumb, layerImg, coverage, zindex }
    };

    var maxToppings = 99;

    /* ──────────────────────────────────────────────────────────
       INIT
    ────────────────────────────────────────────────────────── */
    $(document).ready(function () {
        var $root = $('#np-root');
        if ( ! $root.length ) { return; }

        maxToppings = parseInt( $root.data('max-toppings') ) || 99;

        NP.init();
    });

    /* ──────────────────────────────────────────────────────────
       PUBLIC API
    ────────────────────────────────────────────────────────── */
    window.NP = {

        /* ── initialise ── */
        init: function () {
            this._bindTabs();
            this._bindMobileToggle();
            this._initMobilePizzaCopy();
            this.goTab('crust');
            this._updateCounter();
        },

        /* ── Tab switching ── */
        goTab: function (tabName) {
            var $tabs   = $('.np-tab');
            var $panels = $('.np-panel');

            $tabs.each(function () {
                var t = $(this).data('tab');
                $(this)
                    .toggleClass('active', t === tabName)
                    .attr('aria-selected', t === tabName ? 'true' : 'false');
            });

            $panels.each(function () {
                var p = this.id.replace('np-panel-', '');
                $(this).toggleClass('active', p === tabName);
            });

            // Update progress dots
            var order = ['crust','sauce','cheese','toppings','drizzle','slicing','yourpizza'];
            var idx   = order.indexOf(tabName);
            $('.np-progress__dot').each(function () {
                var s = $(this).data('step');
                var si = order.indexOf(s);
                $(this)
                    .toggleClass('active', s === tabName)
                    .toggleClass('done',   si < idx);
            });

            // Update "Your Pizza" summary when that tab is opened
            if (tabName === 'yourpizza') {
                NP._renderSummary();
            }

            // Scroll tabs nav to active tab on narrow screens
            var $activeTab = $('.np-tab[data-tab="' + tabName + '"]');
            if ($activeTab.length) {
                var nav = document.getElementById('np-tabnav');
                if (nav) {
                    var tl = $activeTab[0].offsetLeft;
                    nav.scrollTo({ left: tl - 20, behavior: 'smooth' });
                }
            }
        },

        /* ── Exclusive base-layer swap (crust / sauce / cheese / drizzle / cut) ── */
        swapBase: function (layerType, slug, title, layerImg, triggerEl) {
            // Deselect any previously selected card of this type
            $('.np-card[data-layer="' + layerType + '"]').each(function () {
                $(this).removeClass('np-card--selected');
                $(this).find('.np-btn--add').show();
                $(this).find('.np-btn--remove').hide();
            });

            // Select this one
            var $card = $(triggerEl).closest('.np-card');
            $card.addClass('np-card--selected');
            $card.find('.np-btn--add').hide();
            $card.find('.np-btn--remove').show();

            // Get thumb for fly animation
            var thumb    = $card.data('thumb') || '';
            var $thumbEl = $card.find('.np-card__thumb');

            // Update state
            state[layerType] = {
                slug:     slug,
                title:    title,
                thumb:    thumb,
                layerImg: layerImg
            };

            // Fire PizzaLayer's JS swap
            if (typeof SwapBasePizzaLayer === 'function') {
                SwapBasePizzaLayer(
                    'pizzalayer-base-layer-' + layerType,
                    title,
                    layerImg
                );
            }

            // Mark tab as done
            var tabForLayer = (layerType === 'cut') ? 'slicing' : layerType;
            $('.np-tab[data-tab="' + tabForLayer + '"]').addClass('np-tab--done');

            // Fly animation
            NP._flyTo($thumbEl);

            // Update summary if visible
            NP._updateSummaryRow(layerType);
        },

        /* ── Remove exclusive base layer ── */
        removeBase: function (layerType, slug, triggerEl) {
            var $card = $(triggerEl).closest('.np-card');
            $card.removeClass('np-card--selected');
            $card.find('.np-btn--add').show();
            $card.find('.np-btn--remove').hide();

            state[layerType] = null;

            // Fire PizzaLayer's swap with blank
            if (typeof SwapBasePizzaLayer === 'function') {
                SwapBasePizzaLayer(
                    'pizzalayer-base-layer-' + layerType,
                    '',
                    ''
                );
            }

            NP._updateSummaryRow(layerType);
        },

        /* ── Add topping ── */
        addTopping: function (zindex, slug, layerImg, title, cssId, menuId, triggerEl) {
            // Check max toppings limit
            var currentCount = Object.keys(state.toppings).length;
            if (currentCount >= maxToppings) {
                NP._showToast('Maximum ' + maxToppings + ' toppings reached!');
                return;
            }

            var $card   = $(triggerEl).closest('.np-card');
            var thumb   = $card.data('thumb') || '';
            var $thumbEl = $card.find('.np-card__thumb');

            // Mark selected
            $card.addClass('np-card--selected');
            $card.find('.np-btn--add').hide();
            $card.find('.np-btn--remove').show();
            $card.find('.np-coverage').show();

            // Default coverage = whole
            var defaultCoverage = 'whole';
            $card.find('.np-cov-btn[data-fraction="whole"]').addClass('active');

            // Update state
            state.toppings[slug] = {
                slug:     slug,
                title:    title,
                thumb:    thumb,
                layerImg: layerImg,
                coverage: defaultCoverage,
                zindex:   zindex
            };

            // Fire PizzaLayer add
            if (typeof AddPizzaLayer === 'function') {
                AddPizzaLayer(zindex, slug, layerImg, title, cssId, menuId);
            }

            // Mark toppings tab done
            $('.np-tab[data-tab="toppings"]').addClass('np-tab--done');

            // Fly animation
            NP._flyTo($thumbEl);

            NP._updateCounter();
            NP._updateSummaryRow('toppings');
        },

        /* ── Remove topping ── */
        removeTopping: function (layerId, slug, triggerEl) {
            var $card = $(triggerEl).closest('.np-card');
            $card.removeClass('np-card--selected');
            $card.find('.np-btn--add').show();
            $card.find('.np-btn--remove').hide();
            $card.find('.np-coverage').hide();
            $card.find('.np-cov-btn').removeClass('active');

            delete state.toppings[slug];

            if (typeof RemovePizzaLayer === 'function') {
                RemovePizzaLayer(layerId, '', slug);
            }

            NP._updateCounter();
            NP._updateSummaryRow('toppings');
        },

        /* ── Set coverage for a topping ── */
        setCoverage: function (slug, fraction, triggerEl) {
            if ( ! state.toppings[slug] ) { return; }
            state.toppings[slug].coverage = fraction;

            // Highlight selected coverage button
            var $card = $(triggerEl).closest('.np-card');
            $card.find('.np-cov-btn').removeClass('active');
            $(triggerEl).addClass('active');

            // Fire PizzaLayer's SetToppingCoverage
            if (typeof SetToppingCoverage === 'function') {
                SetToppingCoverage(fraction, 'pizzalayer-topping-' + slug);
            }

            NP._updateSummaryRow('toppings');
        },

        /* ── Reset everything ── */
        resetAll: function () {
            state.crust   = null;
            state.sauce   = null;
            state.cheese  = null;
            state.drizzle = null;
            state.cut     = null;
            state.toppings = {};

            // Reset all card UI
            $('.np-card').removeClass('np-card--selected');
            $('.np-btn--add').show();
            $('.np-btn--remove').hide();
            $('.np-coverage').hide();
            $('.np-cov-btn').removeClass('active');
            $('.np-tab').removeClass('np-tab--done');

            NP._updateCounter();
            NP._renderSummary();
            NP.goTab('crust');
        },

        /* ── Navigate to a tab (public alias) ── */
        switchTab: function(name) { NP.goTab(name); },

        /* ─────────────────── PRIVATE ─────────────────────── */

        /* Bind tab nav clicks */
        _bindTabs: function () {
            $(document).on('click', '.np-tab', function () {
                NP.goTab($(this).data('tab'));
            });
        },

        /* Bind mobile expand toggle */
        _bindMobileToggle: function () {
            $('#np-mobile-toggle').on('click', function () {
                var $exp = $('#np-mobile-expanded');
                var open = $exp.hasClass('open');
                $exp.toggleClass('open', !open).attr('aria-hidden', open ? 'false' : 'true');
                $(this).find('.fa').toggleClass('fa-chevron-down', open).toggleClass('fa-chevron-up', !open);

                // On expand: inject the pizza DOM reference into the expanded slot
                if (!open) {
                    var $canvas = $('#np-pizza-canvas');
                    if ($canvas.length) {
                        var $expanded = $('#np-mobile-expanded');
                        $expanded.html('');
                        // We can't truly clone a live PizzaLayer pizza; instead show
                        // a scaled wrapper that references nothing — just a visual copy via CSS
                        // The real pizza lives in #np-pizza-canvas on desktop.
                        $expanded.append('<p style="color:var(--np-text-muted);font-size:13px;text-align:center;">Switch to a wider screen to see the live preview.</p>');
                    }
                }
            });
        },

        /* On mobile: the pizza is not visible in a sticky column.
           We keep the live pizza canvas hidden off-screen via Bootstrap d-none d-lg-flex,
           but the PizzaLayer JS still runs and updates the layers normally.
           The mini-bar just shows a label + the toggle. */
        _initMobilePizzaCopy: function () {
            // Nothing to do; mobile preview bar is CSS-only status indicator
        },

        /* Update topping counter badges */
        _updateCounter: function () {
            var count = Object.keys(state.toppings).length;
            $('#np-topping-count, #np-topping-count-inline').text(count);

            // Warn near limit
            if (count >= maxToppings) {
                $('#np-topping-counter').css('border-color', 'var(--np-accent)');
            } else {
                $('#np-topping-counter').css('border-color', '');
            }
        },

        /* Fly-to animation: clone the thumb image, animate it to the pizza canvas */
        _flyTo: function ($thumbEl) {
            if ( ! $thumbEl || ! $thumbEl.length ) { return; }

            // Target: the pizza canvas
            var $target = $('#np-pizza-canvas, .np-pizza-sticky__canvas').first();
            if ( ! $target.length ) {
                // Mobile: try the mobile bar pizza slot
                $target = $('#np-mobile-preview-bar__pizza').first();
            }
            if ( ! $target.length ) { return; }

            var srcRect = $thumbEl[0].getBoundingClientRect();
            var dstRect = $target[0].getBoundingClientRect();

            if ( ! srcRect.width || ! dstRect.width ) { return; }

            // Build clone
            var $clone = $('<div class="np-fly-clone"></div>');
            $clone.css({
                top:    srcRect.top,
                left:   srcRect.left,
                width:  srcRect.width,
                height: srcRect.height,
                'background-image': $thumbEl.css('background-image') || 'none',
                'background-size': 'cover',
                'background-position': 'center'
            });

            // If it's an img element, replicate as img inside clone
            if ( $thumbEl.is('img') ) {
                $clone.append(
                    $('<img>').attr('src', $thumbEl.attr('src')).css({
                        width: '100%', height: '100%', 'object-fit': 'cover', display: 'block'
                    })
                );
            }

            $('#np-fly-container').append($clone);

            // Animate to destination center via rAF double-frame
            requestAnimationFrame(function () {
                requestAnimationFrame(function () {
                    var destX = dstRect.left + dstRect.width  / 2 - srcRect.width  / 2;
                    var destY = dstRect.top  + dstRect.height / 2 - srcRect.height / 2;

                    $clone.css({
                        transition: 'top 0.65s cubic-bezier(0.4,0,0.2,1), left 0.65s cubic-bezier(0.4,0,0.2,1), transform 0.65s cubic-bezier(0.4,0,0.2,1), opacity 0.65s ease',
                        top:        destY,
                        left:       destX,
                        transform:  'scale(0.18)',
                        opacity:    '0'
                    });

                    setTimeout(function () { $clone.remove(); }, 700);
                });
            });
        },

        /* Update a single summary row without re-rendering everything */
        _updateSummaryRow: function (layerType) {
            if ($('#np-panel-yourpizza').hasClass('active')) {
                NP._renderSummary();
            }
        },

        /* Render the "Your Pizza" summary section */
        _renderSummary: function () {
            var layers = ['crust','sauce','cheese','drizzle','cut'];
            var layerIcons = {
                crust:   'fa-circle',
                sauce:   'fa-droplet',
                cheese:  'fa-cheese',
                drizzle: 'fa-wine-glass',
                cut:     'fa-pizza-slice'
            };
            var layerIdMap = {
                crust:   'np-yp-crust-val',
                sauce:   'np-yp-sauce-val',
                cheese:  'np-yp-cheese-val',
                drizzle: 'np-yp-drizzle-val',
                cut:     'np-yp-slicing-val'
            };
            var rowIdMap = {
                crust:   'np-yp-crust',
                sauce:   'np-yp-sauce',
                cheese:  'np-yp-cheese',
                drizzle: 'np-yp-drizzle',
                cut:     'np-yp-slicing'
            };

            // Exclusive layers
            layers.forEach(function (lt) {
                var $val = $('#' + layerIdMap[lt]);
                var $row = $('#' + rowIdMap[lt]);
                var sel  = state[lt];

                if (sel) {
                    $val.html(NP._selBubble(sel.thumb, sel.title, null, null, lt, sel.slug));
                    $row.addClass('has-selection');
                } else {
                    $val.html('<span class="np-yp-none">— none selected —</span>');
                    $row.removeClass('has-selection');
                }
            });

            // Toppings
            var $tVal = $('#np-yp-toppings-val');
            var $tRow = $('#np-yp-toppings-row');
            var tKeys = Object.keys(state.toppings);

            if (tKeys.length) {
                var bubblesHtml = '';
                tKeys.forEach(function (slug) {
                    var t = state.toppings[slug];
                    bubblesHtml += NP._selBubble(t.thumb, t.title, t.coverage, slug, 'toppings', slug);
                });
                $tVal.html(bubblesHtml);
                $tRow.addClass('has-selection');
            } else {
                $tVal.html('<span class="np-yp-none">— none added —</span>');
                $tRow.removeClass('has-selection');
            }
        },

        /* Build an inline selection bubble HTML string */
        _selBubble: function (thumb, title, coverage, toppingSlug, layerType, slug) {
            var imgHtml = '';
            if (thumb) {
                imgHtml = '<img src="' + NP._esc(thumb) + '" alt="' + NP._esc(title) + '" />';
            }
            var coverageHtml = coverage ? '<span class="np-yp-coverage"> · ' + coverage.replace('quarter-','Q').replace('half-','½') + '</span>' : '';

            var removeHtml = '';
            if (layerType === 'toppings' && toppingSlug) {
                var jsRemove = "NP.removeTopping('pizzalayer-topping-" + toppingSlug + "','" + toppingSlug + "',this); NP._renderSummary();";
                removeHtml = '<button class="np-yp-remove" onclick="' + jsRemove + '" title="Remove"><i class="fa fa-times"></i></button>';
            } else if (layerType && slug) {
                var jsRemoveBase = "NP.removeBase('" + layerType + "','" + slug + "',this); NP._renderSummary();";
                removeHtml = '<button class="np-yp-remove" onclick="' + jsRemoveBase + '" title="Remove"><i class="fa fa-times"></i></button>';
            }

            return '<span class="np-yp-bubble">' + imgHtml + '<span>' + NP._esc(title) + coverageHtml + '</span>' + removeHtml + '</span>';
        },

        /* Toast notification */
        _showToast: function (msg) {
            var $t = $('<div>').addClass('np-toast').text(msg).css({
                position:   'fixed',
                bottom:     '24px',
                left:       '50%',
                transform:  'translateX(-50%) translateY(40px)',
                background: '#333',
                color:      '#fff',
                padding:    '10px 20px',
                borderRadius: '999px',
                fontSize:   '13px',
                fontWeight: '600',
                zIndex:     99999,
                opacity:    0,
                transition: 'all 0.3s ease',
                boxShadow:  '0 4px 20px rgba(0,0,0,0.4)'
            });
            $('body').append($t);
            requestAnimationFrame(function () {
                $t.css({ opacity: 1, transform: 'translateX(-50%) translateY(0)' });
            });
            setTimeout(function () {
                $t.css({ opacity: 0, transform: 'translateX(-50%) translateY(10px)' });
                setTimeout(function () { $t.remove(); }, 350);
            }, 2500);
        },

        /* Minimal HTML escape */
        _esc: function (str) {
            if (!str) { return ''; }
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }
    };

})(jQuery);
