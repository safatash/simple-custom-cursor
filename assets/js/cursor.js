( function () {
    'use strict';

    var cfg = window.simpcucuSettings || {};
    var type         = cfg.cursorType     || 'dot-circle';
    var hoverScale   = parseFloat( cfg.hoverScale )  || 1.5;
    var lagSpeed     = parseFloat( cfg.lagSpeed )     || 0.15;
    var mobileForce  = cfg.enableOnMobile === true || cfg.enableOnMobile === '1';

    /* ── Mobile / touch guard ── */
    var isTouch  = ( 'ontouchstart' in window ) || navigator.maxTouchPoints > 0;
    var isSmall  = window.innerWidth <= 1024;

    if ( ( isTouch || isSmall ) && ! mobileForce ) {
        return;
    }

    if ( mobileForce ) {
        document.body.classList.add( 'simpcucu-force-custom' );
    }

    /* ── Create elements based on cursor type ── */
    var dot       = null;
    var outline   = null;
    var crossH    = null;
    var crossV    = null;

    function makeEl( cls ) {
        var el = document.createElement( 'div' );
        el.className = cls;
        document.body.appendChild( el );
        return el;
    }

    switch ( type ) {
        case 'dot-only':
            dot = makeEl( 'simpcucu-dot' );
            break;

        case 'circle-only':
            outline = makeEl( 'simpcucu-outline' );
            break;

        case 'crosshair':
            crossH = makeEl( 'simpcucu-crosshair-h' );
            crossV = makeEl( 'simpcucu-crosshair-v' );
            break;

        case 'ring-fill':
            dot     = makeEl( 'simpcucu-dot' );
            outline = makeEl( 'simpcucu-outline simpcucu-ring-fill' );
            break;

        case 'dot-circle':
        default:
            dot     = makeEl( 'simpcucu-dot' );
            outline = makeEl( 'simpcucu-outline' );
            break;
    }

    /* ── Tracking variables ── */
    var mouseX   = 0, mouseY   = 0;
    var outlineX = 0, outlineY = 0;

    /* ── Mouse move: snap dot & crosshair instantly ── */
    document.addEventListener( 'mousemove', function ( e ) {
        mouseX = e.clientX;
        mouseY = e.clientY;

        if ( dot ) {
            dot.style.left = mouseX + 'px';
            dot.style.top  = mouseY + 'px';
        }

        if ( crossH ) {
            crossH.style.left = mouseX + 'px';
            crossH.style.top  = mouseY + 'px';
        }
        if ( crossV ) {
            crossV.style.left = mouseX + 'px';
            crossV.style.top  = mouseY + 'px';
        }
    } );

    /* ── RAF loop: smooth outline lag ── */
    function animateCursor() {
        if ( outline ) {
            outlineX += ( mouseX - outlineX ) * lagSpeed;
            outlineY += ( mouseY - outlineY ) * lagSpeed;
            outline.style.left = outlineX + 'px';
            outline.style.top  = outlineY + 'px';
        }
        requestAnimationFrame( animateCursor );
    }
    animateCursor();

    /* ── Hover targets ── */
    var hoverSelector = 'a, button, input, textarea, select, label, [role="button"], .fusion-button';

    document.addEventListener( 'mouseover', function ( e ) {
        if ( ! e.target.closest( hoverSelector ) ) return;
        if ( outline ) {
            outline.style.transform = 'translate(-50%, -50%) scale(' + hoverScale + ')';
        }
        if ( crossH ) {
            crossH.style.transform = 'translate(-50%, -50%) scale(' + hoverScale + ')';
            crossV.style.transform = 'translate(-50%, -50%) scale(' + hoverScale + ')';
        }
    } );

    document.addEventListener( 'mouseout', function ( e ) {
        if ( ! e.target.closest( hoverSelector ) ) return;
        if ( outline ) {
            outline.style.transform = 'translate(-50%, -50%) scale(1)';
        }
        if ( crossH ) {
            crossH.style.transform = 'translate(-50%, -50%) scale(1)';
            crossV.style.transform = 'translate(-50%, -50%) scale(1)';
        }
    } );

    /* ── Hide cursor when leaving window ── */
    document.addEventListener( 'mouseleave', function () {
        [ dot, outline, crossH, crossV ].forEach( function ( el ) {
            if ( el ) el.style.opacity = '0';
        } );
    } );
    document.addEventListener( 'mouseenter', function () {
        [ dot, outline, crossH, crossV ].forEach( function ( el ) {
            if ( el ) el.style.opacity = '1';
        } );
    } );

} )();
