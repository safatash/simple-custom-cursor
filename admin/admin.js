( function () {
    'use strict';

    /* ══════════════════════════════════════════
       TAB NAVIGATION
    ══════════════════════════════════════════ */
    var navBtns = document.querySelectorAll( '.simpcucu-nav-btn' );
    var tabs    = document.querySelectorAll( '.simpcucu-tab' );

    navBtns.forEach( function ( btn ) {
        btn.addEventListener( 'click', function () {
            var target = btn.getAttribute( 'data-tab' );

            navBtns.forEach( function ( b ) { b.classList.remove( 'active' ); } );
            tabs.forEach( function ( t ) { t.classList.remove( 'active' ); } );

            btn.classList.add( 'active' );
            var panel = document.getElementById( 'simpcucu-tab-' + target );
            if ( panel ) panel.classList.add( 'active' );
        } );
    } );

    /* ══════════════════════════════════════════
       CURSOR TYPE CARDS
    ══════════════════════════════════════════ */
    var typeCards = document.querySelectorAll( '.simpcucu-type-card' );

    typeCards.forEach( function ( card ) {
        card.addEventListener( 'click', function () {
            typeCards.forEach( function ( c ) { c.classList.remove( 'active' ); } );
            card.classList.add( 'active' );
            updatePreview();
        } );
    } );

    /* ══════════════════════════════════════════
       RANGE SLIDERS — fill track + live badge
    ══════════════════════════════════════════ */
    function initSlider( rangeEl ) {
        var badgeId = rangeEl.getAttribute( 'data-badge' );
        var badge   = badgeId ? document.getElementById( badgeId ) : null;
        var suffix  = rangeEl.getAttribute( 'data-suffix' ) || '';

        function update() {
            var min = parseFloat( rangeEl.min ) || 0;
            var max = parseFloat( rangeEl.max ) || 100;
            var val = parseFloat( rangeEl.value );
            var pct = ( ( val - min ) / ( max - min ) ) * 100;

            // Update fill via CSS custom property on the input itself
            rangeEl.style.setProperty( '--simpcucu-fill', pct + '%' );

            // Update badge
            if ( badge ) {
                badge.textContent = val + suffix;
            }
            updatePreview();
        }

        rangeEl.addEventListener( 'input', update );
        update(); // initialise on load
    }

    document.querySelectorAll( '.simpcucu-range-visible' ).forEach( initSlider );

    /* ══════════════════════════════════════════
       COLOR PICKERS — swatch + hex sync
    ══════════════════════════════════════════ */
    document.querySelectorAll( '.simpcucu-color-swatch' ).forEach( function ( swatch ) {
        var picker    = swatch.querySelector( 'input[type="color"]' );
        var dot       = swatch.querySelector( '.simpcucu-color-dot' );
        var syncId    = picker ? picker.getAttribute( 'data-sync' ) : null;
        var hexInput  = syncId ? document.getElementById( syncId ) : null;

        function syncFromPicker() {
            if ( dot )      dot.style.background = picker.value;
            if ( hexInput ) hexInput.value = picker.value;
            updatePreview();
        }

        function syncFromHex() {
            var val = hexInput.value.trim();
            if ( /^#[0-9a-fA-F]{6}$/.test( val ) ) {
                picker.value = val;
                if ( dot ) dot.style.background = val;
                updatePreview();
            }
        }

        if ( picker ) picker.addEventListener( 'input', syncFromPicker );
        if ( hexInput ) hexInput.addEventListener( 'input', syncFromHex );

        // Initialise dot color on load
        if ( dot && picker ) dot.style.background = picker.value;
    } );

    /* ══════════════════════════════════════════
       LIVE PREVIEW
    ══════════════════════════════════════════ */
    var stage      = document.getElementById( 'simpcucu-preview-stage' );
    var prevDot    = document.getElementById( 'simpcucu-prev-dot' );
    var prevOutline= document.getElementById( 'simpcucu-prev-outline' );
    var prevCH     = document.getElementById( 'simpcucu-prev-ch' );
    var prevCV     = document.getElementById( 'simpcucu-prev-cv' );

    var previewMouseX = 0, previewMouseY = 0;
    var previewOutX   = 0, previewOutY   = 0;
    var previewActive = false;

    function getSettings() {
        // Cursor type
        var typeCard = document.querySelector( '.simpcucu-type-card.active' );
        var type = typeCard ? typeCard.querySelector( 'input[type="radio"]' ).value : 'dot-circle';

        // Colors — read from the hex text inputs (single source of truth)
        var dotColorEl     = document.getElementById( 'simpcucu_dot_color_hex' );
        var outlineColorEl = document.getElementById( 'simpcucu_outline_color_hex' );
        var dotColor     = dotColorEl     ? dotColorEl.value     : '#ffffff';
        var outlineColor = outlineColorEl ? outlineColorEl.value : '#ffffff';

        // Sizes
        var dotSizeEl      = document.querySelector( 'input[name="simpcucu_options[dot_size]"]' );
        var outlineSizeEl  = document.querySelector( 'input[name="simpcucu_options[outline_size]"]' );
        var outlineWtEl    = document.querySelector( 'input[name="simpcucu_options[outline_weight]"]' );
        var dotSize      = dotSizeEl     ? parseInt( dotSizeEl.value )     : 6;
        var outlineSize  = outlineSizeEl ? parseInt( outlineSizeEl.value ) : 28;
        var outlineWt    = outlineWtEl   ? parseInt( outlineWtEl.value )   : 2;

        // Blend
        var blendEl  = document.querySelector( 'select[name="simpcucu_options[blend_mode]"]' );
        var blend    = blendEl ? blendEl.value : 'difference';

        // Lag speed
        var lagEl    = document.querySelector( 'input[name="simpcucu_options[lag_speed]"]' );
        var lagSpeed = lagEl ? parseFloat( lagEl.value ) : 0.15;

        return { type, dotColor, outlineColor, dotSize, outlineSize, outlineWt, blend, lagSpeed };
    }

    function updatePreview() {
        if ( ! stage ) return;
        var s = getSettings();

        // Reset visibility
        [ prevDot, prevOutline, prevCH, prevCV ].forEach( function ( el ) {
            if ( el ) el.style.display = 'none';
        } );

        // Apply styles
        if ( prevDot ) {
            prevDot.style.width          = s.dotSize + 'px';
            prevDot.style.height         = s.dotSize + 'px';
            prevDot.style.background     = s.dotColor;
            prevDot.style.mixBlendMode   = s.blend;
        }
        if ( prevOutline ) {
            prevOutline.style.width        = s.outlineSize + 'px';
            prevOutline.style.height       = s.outlineSize + 'px';
            prevOutline.style.border       = s.outlineWt + 'px solid ' + s.outlineColor;
            prevOutline.style.mixBlendMode = s.blend;
            prevOutline.style.background   = ( s.type === 'ring-fill' )
                ? hexToRgba( s.outlineColor, 0.18 )
                : 'transparent';
        }
        if ( prevCH ) {
            prevCH.style.width        = s.outlineSize + 'px';
            prevCH.style.height       = s.outlineWt + 'px';
            prevCH.style.background   = s.outlineColor;
            prevCH.style.mixBlendMode = s.blend;
            prevCH.style.borderRadius = '0';
        }
        if ( prevCV ) {
            prevCV.style.width        = s.outlineWt + 'px';
            prevCV.style.height       = s.outlineSize + 'px';
            prevCV.style.background   = s.outlineColor;
            prevCV.style.mixBlendMode = s.blend;
            prevCV.style.borderRadius = '0';
        }

        // Show elements per type
        switch ( s.type ) {
            case 'dot-only':
                if ( prevDot ) prevDot.style.display = 'block';
                break;
            case 'circle-only':
                if ( prevOutline ) prevOutline.style.display = 'block';
                break;
            case 'crosshair':
                if ( prevCH ) prevCH.style.display = 'block';
                if ( prevCV ) prevCV.style.display = 'block';
                break;
            case 'ring-fill':
                if ( prevDot )     prevDot.style.display     = 'block';
                if ( prevOutline ) prevOutline.style.display = 'block';
                break;
            case 'dot-circle':
            default:
                if ( prevDot )     prevDot.style.display     = 'block';
                if ( prevOutline ) prevOutline.style.display = 'block';
                break;
        }
    }

    function hexToRgba( hex, alpha ) {
        var r = parseInt( hex.slice(1,3), 16 );
        var g = parseInt( hex.slice(3,5), 16 );
        var b = parseInt( hex.slice(5,7), 16 );
        return 'rgba(' + r + ',' + g + ',' + b + ',' + alpha + ')';
    }

    // Mouse tracking inside preview stage
    if ( stage ) {
        stage.addEventListener( 'mouseenter', function () { previewActive = true; } );
        stage.addEventListener( 'mouseleave', function () {
            previewActive = false;
            [ prevDot, prevOutline, prevCH, prevCV ].forEach( function ( el ) {
                if ( el ) el.style.opacity = '0';
            } );
        } );

        stage.addEventListener( 'mousemove', function ( e ) {
            var rect = stage.getBoundingClientRect();
            previewMouseX = e.clientX - rect.left;
            previewMouseY = e.clientY - rect.top;

            [ prevDot, prevOutline, prevCH, prevCV ].forEach( function ( el ) {
                if ( el ) el.style.opacity = '1';
            } );

            // Snap dot + crosshair
            if ( prevDot ) {
                prevDot.style.left = previewMouseX + 'px';
                prevDot.style.top  = previewMouseY + 'px';
            }
            if ( prevCH ) {
                prevCH.style.left = previewMouseX + 'px';
                prevCH.style.top  = previewMouseY + 'px';
            }
            if ( prevCV ) {
                prevCV.style.left = previewMouseX + 'px';
                prevCV.style.top  = previewMouseY + 'px';
            }
        } );

        // Smooth outline lag loop
        ( function animatePreview() {
            if ( previewActive && prevOutline ) {
                var s = getSettings();
                previewOutX += ( previewMouseX - previewOutX ) * s.lagSpeed;
                previewOutY += ( previewMouseY - previewOutY ) * s.lagSpeed;
                prevOutline.style.left = previewOutX + 'px';
                prevOutline.style.top  = previewOutY + 'px';
            }
            requestAnimationFrame( animatePreview );
        } )();
    }

    // Initial render
    updatePreview();

    // Also re-run preview when blend mode changes
    var blendSelect = document.querySelector( 'select[name="simpcucu_options[blend_mode]"]' );
    if ( blendSelect ) blendSelect.addEventListener( 'change', updatePreview );

} )();
