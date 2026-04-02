<?php
/**
 * Plugin Name:       Simple Custom Cursor
 * Plugin URI:        https://wordpress.org/plugins/simple-custom-cursor/
 * Description:       Adds a fully customisable animated cursor to the frontend of your WordPress site, with a polished admin settings page.
 * Version:           2.1.0
 * Author:            Safa Tash (NOVA Advertising)
 * Author URI:        https://www.novaadvertising.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       simple-custom-cursor
 * Requires at least: 5.5
 * Requires PHP:      7.4
 * Tested up to:      6.9
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'SIMPCUCU_VERSION',    '2.1.0' );
define( 'SIMPCUCU_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SIMPCUCU_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/* ─────────────────────────────────────────────
   Uninstall: clean up options from the database
───────────────────────────────────────────── */
register_uninstall_hook( __FILE__, 'simpcucu_uninstall' );
function simpcucu_uninstall() {
    delete_option( 'simpcucu_options' );
}

/* ─────────────────────────────────────────────
   Defaults & options helper
───────────────────────────────────────────── */
function simpcucu_defaults() {
    return array(
        'cursor_type'      => 'dot-circle',
        'dot_color'        => '#ffffff',
        'outline_color'    => '#ffffff',
        'dot_size'         => 6,
        'outline_size'     => 28,
        'outline_weight'   => 2,
        'blend_mode'       => 'difference',
        'hover_scale'      => 1.5,
        'lag_speed'        => 0.15,
        'enable_on_mobile' => 0,
    );
}

function simpcucu_get_options() {
    return wp_parse_args( get_option( 'simpcucu_options', array() ), simpcucu_defaults() );
}

/* ─────────────────────────────────────────────
   Admin menu
───────────────────────────────────────────── */
add_action( 'admin_menu', 'simpcucu_add_admin_menu' );
function simpcucu_add_admin_menu() {
    add_options_page(
        __( 'Custom Cursor Settings', 'simple-custom-cursor' ),
        __( 'Custom Cursor', 'simple-custom-cursor' ),
        'manage_options',
        'simple-custom-cursor',
        'simpcucu_settings_page'
    );
}

/* ─────────────────────────────────────────────
   Settings registration & sanitisation
───────────────────────────────────────────── */
add_action( 'admin_init', 'simpcucu_register_settings' );
function simpcucu_register_settings() {
    register_setting( 'simpcucu_options_group', 'simpcucu_options', array( 'sanitize_callback' => 'simpcucu_sanitize_options' ) );
}

function simpcucu_sanitize_options( $input ) {
    $d = simpcucu_defaults();
    $c = array();

    $c['cursor_type']      = in_array( $input['cursor_type'] ?? '', array( 'dot-circle','dot-only','circle-only','crosshair','ring-fill' ), true ) ? $input['cursor_type'] : $d['cursor_type'];
    $c['dot_color']        = sanitize_hex_color( $input['dot_color'] ?? '' )     ?: $d['dot_color'];
    $c['outline_color']    = sanitize_hex_color( $input['outline_color'] ?? '' ) ?: $d['outline_color'];
    $c['dot_size']         = absint( $input['dot_size'] ?? 0 )       ?: $d['dot_size'];
    $c['outline_size']     = absint( $input['outline_size'] ?? 0 )   ?: $d['outline_size'];
    $c['outline_weight']   = absint( $input['outline_weight'] ?? 0 ) ?: $d['outline_weight'];
    $c['blend_mode']       = in_array( $input['blend_mode'] ?? '', array( 'difference','normal' ), true ) ? $input['blend_mode'] : $d['blend_mode'];
    $c['hover_scale']      = floatval( $input['hover_scale'] ?? 0 ) ?: $d['hover_scale'];
    $c['lag_speed']        = min( 1.0, max( 0.01, floatval( $input['lag_speed'] ?? 0 ) ) );
    $c['enable_on_mobile'] = ! empty( $input['enable_on_mobile'] ) ? 1 : 0;

    return $c;
}

/* ─────────────────────────────────────────────
   Admin assets
───────────────────────────────────────────── */
add_action( 'admin_enqueue_scripts', 'simpcucu_admin_assets' );
function simpcucu_admin_assets( $hook ) {
    if ( 'settings_page_simple-custom-cursor' !== $hook ) return;
    wp_enqueue_style(  'simpcucu-admin-style',  SIMPCUCU_PLUGIN_URL . 'admin/admin.css', array(), SIMPCUCU_VERSION );
    wp_enqueue_script( 'simpcucu-admin-script', SIMPCUCU_PLUGIN_URL . 'admin/admin.js',  array(), SIMPCUCU_VERSION, true );
}

/* ─────────────────────────────────────────────
   Frontend assets
───────────────────────────────────────────── */
add_action( 'wp_enqueue_scripts', 'simpcucu_enqueue_frontend' );
function simpcucu_enqueue_frontend() {
    $o = simpcucu_get_options();

    wp_enqueue_style( 'simpcucu-style', SIMPCUCU_PLUGIN_URL . 'assets/css/cursor.css', array(), SIMPCUCU_VERSION );

    $css = sprintf(
        ':root{--simpcucu-dot-size:%dpx;--simpcucu-outline-size:%dpx;--simpcucu-outline-wt:%dpx;--simpcucu-dot-color:%s;--simpcucu-outline-color:%s;--simpcucu-blend:%s;}',
        absint( $o['dot_size'] ),
        absint( $o['outline_size'] ),
        absint( $o['outline_weight'] ),
        sanitize_hex_color( $o['dot_color'] ),
        sanitize_hex_color( $o['outline_color'] ),
        ( $o['blend_mode'] === 'difference' ) ? 'difference' : 'normal'
    );
    wp_add_inline_style( 'simpcucu-style', $css );

    wp_enqueue_script( 'simpcucu-script', SIMPCUCU_PLUGIN_URL . 'assets/js/cursor.js', array(), SIMPCUCU_VERSION, true );
    wp_localize_script( 'simpcucu-script', 'simpcucuSettings', array(
        'cursorType'     => $o['cursor_type'],
        'hoverScale'     => floatval( $o['hover_scale'] ),
        'lagSpeed'       => floatval( $o['lag_speed'] ),
        'enableOnMobile' => (bool) $o['enable_on_mobile'],
    ) );
}

/* ─────────────────────────────────────────────
   SVG helpers — kses allowlist + icon library
───────────────────────────────────────────── */
function simpcucu_svg_kses_args() {
    $attrs = array(
        'viewbox'       => true,
        'fill'          => true,
        'xmlns'         => true,
        'stroke'        => true,
        'stroke-width'  => true,
        'stroke-linecap'=> true,
        'fill-opacity'  => true,
        'cx'            => true,
        'cy'            => true,
        'r'             => true,
        'x1'            => true,
        'y1'            => true,
        'x2'            => true,
        'y2'            => true,
        'width'         => true,
        'height'        => true,
        'class'         => true,
        'aria-hidden'   => true,
    );
    return array(
        'svg'    => $attrs,
        'circle' => $attrs,
        'line'   => $attrs,
        'path'   => array_merge( $attrs, array( 'd' => true ) ),
        'rect'   => array_merge( $attrs, array( 'x' => true, 'y' => true, 'rx' => true ) ),
    );
}

function simpcucu_get_type_svg( $key ) {
    $svgs = array(
        'dot-circle'  => '<svg viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><circle cx="18" cy="18" r="11" stroke="currentColor" stroke-width="2"/><circle cx="18" cy="18" r="3" fill="currentColor"/></svg>',
        'dot-only'    => '<svg viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><circle cx="18" cy="18" r="5" fill="currentColor"/></svg>',
        'circle-only' => '<svg viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><circle cx="18" cy="18" r="12" stroke="currentColor" stroke-width="2"/></svg>',
        'crosshair'   => '<svg viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><line x1="18" y1="4" x2="18" y2="32" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><line x1="4" y1="18" x2="32" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="18" cy="18" r="3" stroke="currentColor" stroke-width="1.5"/></svg>',
        'ring-fill'   => '<svg viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><circle cx="18" cy="18" r="12" stroke="currentColor" stroke-width="2" fill="currentColor" fill-opacity="0.18"/><circle cx="18" cy="18" r="3" fill="currentColor"/></svg>',
    );
    return isset( $svgs[ $key ] ) ? $svgs[ $key ] : '';
}

/* ─────────────────────────────────────────────
   Settings page HTML
───────────────────────────────────────────── */
function simpcucu_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'simple-custom-cursor' ) );
    }
    $o = simpcucu_get_options();

    /* Cursor type definitions with inline SVG previews */
    $types = array(
        'dot-circle'  => array( 'label' => 'Dot + Circle',  'svg_key' => 'dot-circle'  ),
        'dot-only'    => array( 'label' => 'Dot Only',       'svg_key' => 'dot-only'    ),
        'circle-only' => array( 'label' => 'Circle Only',    'svg_key' => 'circle-only' ),
        'crosshair'   => array( 'label' => 'Crosshair',      'svg_key' => 'crosshair'   ),
        'ring-fill'   => array( 'label' => 'Filled Ring',    'svg_key' => 'ring-fill'   ),
    );

    ?>
    <div class="wrap scc-wrap">

        <?php settings_errors(); ?>

        <!-- ── Page header ── -->
        <div class="simpcucu-header">
            <div class="simpcucu-header-icon">🖱️</div>
            <div class="simpcucu-header-text">
                <h1><?php esc_html_e( 'Simple Custom Cursor', 'simple-custom-cursor' ); ?></h1>
                <p><?php esc_html_e( 'Customise the cursor style, colors, and behaviour for your site\'s frontend.', 'simple-custom-cursor' ); ?></p>
            </div>
        </div>

        <form method="post" action="options.php">
            <?php settings_fields( 'simpcucu_options_group' ); ?>

            <div class="simpcucu-layout">

                <!-- ── Sidebar navigation ── -->
                <nav class="simpcucu-sidebar" aria-label="<?php esc_attr_e( 'Settings sections', 'simple-custom-cursor' ); ?>">
                    <ul class="simpcucu-nav">
                        <li>
                            <button type="button" class="simpcucu-nav-btn active" data-tab="style">
                                <span class="simpcucu-nav-icon">🎨</span>
                                <?php esc_html_e( 'Style', 'simple-custom-cursor' ); ?>
                            </button>
                        </li>
                        <li>
                            <button type="button" class="simpcucu-nav-btn" data-tab="colors">
                                <span class="simpcucu-nav-icon">🌈</span>
                                <?php esc_html_e( 'Colors', 'simple-custom-cursor' ); ?>
                            </button>
                        </li>
                        <li>
                            <button type="button" class="simpcucu-nav-btn" data-tab="size">
                                <span class="simpcucu-nav-icon">📐</span>
                                <?php esc_html_e( 'Size', 'simple-custom-cursor' ); ?>
                            </button>
                        </li>
                        <li>
                            <button type="button" class="simpcucu-nav-btn" data-tab="behaviour">
                                <span class="simpcucu-nav-icon">⚡</span>
                                <?php esc_html_e( 'Behaviour', 'simple-custom-cursor' ); ?>
                            </button>
                        </li>
                    </ul>
                </nav>

                <!-- ── Content area ── -->
                <div class="simpcucu-content">

                    <!-- ── Live Preview ── -->
                    <div class="simpcucu-preview-panel">
                        <div class="simpcucu-preview-header">
                            <h3><?php esc_html_e( 'Live Preview', 'simple-custom-cursor' ); ?></h3>
                            <span class="simpcucu-preview-badge"><?php esc_html_e( 'Interactive', 'simple-custom-cursor' ); ?></span>
                        </div>
                        <div class="simpcucu-preview-stage" id="simpcucu-preview-stage">
                            <span class="simpcucu-preview-center-text"><?php esc_html_e( 'Hover here', 'simple-custom-cursor' ); ?></span>
                            <div id="simpcucu-prev-dot"></div>
                            <div id="simpcucu-prev-outline"></div>
                            <div id="simpcucu-prev-ch"></div>
                            <div id="simpcucu-prev-cv"></div>
                        </div>
                    </div>

                    <!-- ════════════════════════════
                         TAB: Style
                    ════════════════════════════ -->
                    <div class="simpcucu-tab active" id="simpcucu-tab-style">
                        <div class="simpcucu-panel">
                            <p class="simpcucu-panel-title">
                                <span class="simpcucu-title-icon">🎨</span>
                                <?php esc_html_e( 'Cursor Style', 'simple-custom-cursor' ); ?>
                            </p>
                            <p class="simpcucu-panel-desc"><?php esc_html_e( 'Choose the visual style of the cursor shown on your site.', 'simple-custom-cursor' ); ?></p>

                            <div class="simpcucu-type-grid">
                                <?php foreach ( $types as $value => $meta ) : ?>
                                <label class="simpcucu-type-card <?php echo ( $o['cursor_type'] === $value ) ? 'active' : ''; ?>">
                                    <input type="radio" name="simpcucu_options[cursor_type]" value="<?php echo esc_attr( $value ); ?>" <?php checked( $o['cursor_type'], $value ); ?>>
                                    <div class="simpcucu-type-preview">
                                        <?php echo wp_kses( simpcucu_get_type_svg( $meta['svg_key'] ), simpcucu_svg_kses_args() ); ?>
                                    </div>
                                    <span class="simpcucu-type-label"><?php echo esc_html( $meta['label'] ); ?></span>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- ════════════════════════════
                         TAB: Colors
                    ════════════════════════════ -->
                    <div class="simpcucu-tab" id="simpcucu-tab-colors">
                        <div class="simpcucu-panel">
                            <p class="simpcucu-panel-title">
                                <span class="simpcucu-title-icon">🌈</span>
                                <?php esc_html_e( 'Cursor Colors', 'simple-custom-cursor' ); ?>
                            </p>
                            <p class="simpcucu-panel-desc"><?php esc_html_e( 'Set individual colors for the dot and the outline ring.', 'simple-custom-cursor' ); ?></p>

                            <!-- Dot color -->
                            <div class="simpcucu-field-row">
                                <div class="simpcucu-field-label">
                                    <?php esc_html_e( 'Dot Color', 'simple-custom-cursor' ); ?>
                                    <span class="simpcucu-field-sub"><?php esc_html_e( 'The small inner dot', 'simple-custom-cursor' ); ?></span>
                                </div>
                                <div class="simpcucu-field-control">
                                    <div class="simpcucu-color-swatch">
                                        <div class="simpcucu-color-dot" style="background:<?php echo esc_attr( $o['dot_color'] ); ?>"></div>
                                        <input type="color" id="simpcucu_dot_color_picker" data-sync="simpcucu_dot_color_hex" value="<?php echo esc_attr( $o['dot_color'] ); ?>">
                                    </div>
                                    <input type="text" id="simpcucu_dot_color_hex" name="simpcucu_options[dot_color]" value="<?php echo esc_attr( $o['dot_color'] ); ?>" class="simpcucu-hex-input" maxlength="7" placeholder="#ffffff" aria-label="<?php esc_attr_e( 'Dot color hex', 'simple-custom-cursor' ); ?>">
                                </div>
                            </div>

                            <!-- Outline color -->
                            <div class="simpcucu-field-row">
                                <div class="simpcucu-field-label">
                                    <?php esc_html_e( 'Outline / Ring Color', 'simple-custom-cursor' ); ?>
                                    <span class="simpcucu-field-sub"><?php esc_html_e( 'The trailing ring or circle', 'simple-custom-cursor' ); ?></span>
                                </div>
                                <div class="simpcucu-field-control">
                                    <div class="simpcucu-color-swatch">
                                        <div class="simpcucu-color-dot" style="background:<?php echo esc_attr( $o['outline_color'] ); ?>"></div>
                                        <input type="color" id="simpcucu_outline_color_picker" data-sync="simpcucu_outline_color_hex" value="<?php echo esc_attr( $o['outline_color'] ); ?>">
                                    </div>
                                    <input type="text" id="simpcucu_outline_color_hex" name="simpcucu_options[outline_color]" value="<?php echo esc_attr( $o['outline_color'] ); ?>" class="simpcucu-hex-input" maxlength="7" placeholder="#ffffff" aria-label="<?php esc_attr_e( 'Outline color hex', 'simple-custom-cursor' ); ?>">
                                </div>
                            </div>

                            <hr class="simpcucu-divider">

                            <!-- Blend mode -->
                            <div class="simpcucu-field-row">
                                <div class="simpcucu-field-label">
                                    <?php esc_html_e( 'Blend Mode', 'simple-custom-cursor' ); ?>
                                    <span class="simpcucu-field-sub"><?php esc_html_e( 'How the cursor blends with page content', 'simple-custom-cursor' ); ?></span>
                                </div>
                                <div class="simpcucu-field-control">
                                    <select name="simpcucu_options[blend_mode]" class="simpcucu-select">
                                        <option value="difference" <?php selected( $o['blend_mode'], 'difference' ); ?>><?php esc_html_e( 'Difference — always visible', 'simple-custom-cursor' ); ?></option>
                                        <option value="normal"     <?php selected( $o['blend_mode'], 'normal' ); ?>><?php esc_html_e( 'Normal — solid color', 'simple-custom-cursor' ); ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ════════════════════════════
                         TAB: Size
                    ════════════════════════════ -->
                    <div class="simpcucu-tab" id="simpcucu-tab-size">
                        <div class="simpcucu-panel">
                            <p class="simpcucu-panel-title">
                                <span class="simpcucu-title-icon">📐</span>
                                <?php esc_html_e( 'Size & Weight', 'simple-custom-cursor' ); ?>
                            </p>
                            <p class="simpcucu-panel-desc"><?php esc_html_e( 'Control the dimensions of each cursor element.', 'simple-custom-cursor' ); ?></p>

                            <?php
                            $sliders = array(
                                array(
                                    'name'   => 'dot_size',
                                    'label'  => __( 'Dot Size', 'simple-custom-cursor' ),
                                    'hint'   => __( 'Diameter of the inner dot', 'simple-custom-cursor' ),
                                    'min'    => 2, 'max' => 20, 'step' => 1,
                                    'suffix' => 'px',
                                    'value'  => $o['dot_size'],
                                ),
                                array(
                                    'name'   => 'outline_size',
                                    'label'  => __( 'Outline Size', 'simple-custom-cursor' ),
                                    'hint'   => __( 'Diameter of the outer ring', 'simple-custom-cursor' ),
                                    'min'    => 10, 'max' => 80, 'step' => 1,
                                    'suffix' => 'px',
                                    'value'  => $o['outline_size'],
                                ),
                                array(
                                    'name'   => 'outline_weight',
                                    'label'  => __( 'Border Thickness', 'simple-custom-cursor' ),
                                    'hint'   => __( 'Stroke width of the ring', 'simple-custom-cursor' ),
                                    'min'    => 1, 'max' => 6, 'step' => 1,
                                    'suffix' => 'px',
                                    'value'  => $o['outline_weight'],
                                ),
                            );
                            foreach ( $sliders as $sl ) :
                                $badge_id = 'simpcucu_badge_' . $sl['name'];
                                $min = $sl['min']; $max = $sl['max'];
                                $pct = round( ( ( $sl['value'] - $min ) / ( $max - $min ) ) * 100 );
                            ?>
                            <div class="simpcucu-slider-row">
                                <div class="simpcucu-slider-header">
                                    <span class="simpcucu-slider-label"><?php echo esc_html( $sl['label'] ); ?></span>
                                    <span class="simpcucu-slider-value-badge" id="<?php echo esc_attr( $badge_id ); ?>"><?php echo esc_html( $sl['value'] . $sl['suffix'] ); ?></span>
                                </div>
                                <p class="simpcucu-slider-hint"><?php echo esc_html( $sl['hint'] ); ?></p>
                                <input
                                    type="range"
                                    class="simpcucu-range-visible"
                                    name="simpcucu_options[<?php echo esc_attr( $sl['name'] ); ?>]"
                                    min="<?php echo esc_attr( $min ); ?>"
                                    max="<?php echo esc_attr( $max ); ?>"
                                    step="<?php echo esc_attr( $sl['step'] ); ?>"
                                    value="<?php echo esc_attr( $sl['value'] ); ?>"
                                    data-badge="<?php echo esc_attr( $badge_id ); ?>"
                                    data-suffix="<?php echo esc_attr( $sl['suffix'] ); ?>"
                                    style="--simpcucu-fill:<?php echo esc_attr( $pct ); ?>%"
                                >
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- ════════════════════════════
                         TAB: Behaviour
                    ════════════════════════════ -->
                    <div class="simpcucu-tab" id="simpcucu-tab-behaviour">
                        <div class="simpcucu-panel">
                            <p class="simpcucu-panel-title">
                                <span class="simpcucu-title-icon">⚡</span>
                                <?php esc_html_e( 'Behaviour', 'simple-custom-cursor' ); ?>
                            </p>
                            <p class="simpcucu-panel-desc"><?php esc_html_e( 'Fine-tune animation and interaction settings.', 'simple-custom-cursor' ); ?></p>

                            <?php
                            $bsliders = array(
                                array(
                                    'name'   => 'hover_scale',
                                    'label'  => __( 'Hover Scale', 'simple-custom-cursor' ),
                                    'hint'   => __( 'How much the ring grows when hovering over links and buttons', 'simple-custom-cursor' ),
                                    'min'    => 1.0, 'max' => 3.0, 'step' => 0.1,
                                    'suffix' => '×',
                                    'value'  => $o['hover_scale'],
                                ),
                                array(
                                    'name'   => 'lag_speed',
                                    'label'  => __( 'Outline Lag Speed', 'simple-custom-cursor' ),
                                    'hint'   => __( '0.05 = very smooth trail · 1.0 = instant snap', 'simple-custom-cursor' ),
                                    'min'    => 0.05, 'max' => 1.0, 'step' => 0.05,
                                    'suffix' => '',
                                    'value'  => $o['lag_speed'],
                                ),
                            );
                            foreach ( $bsliders as $sl ) :
                                $badge_id = 'simpcucu_badge_' . $sl['name'];
                                $min = $sl['min']; $max = $sl['max'];
                                $pct = round( ( ( $sl['value'] - $min ) / ( $max - $min ) ) * 100 );
                            ?>
                            <div class="simpcucu-slider-row">
                                <div class="simpcucu-slider-header">
                                    <span class="simpcucu-slider-label"><?php echo esc_html( $sl['label'] ); ?></span>
                                    <span class="simpcucu-slider-value-badge" id="<?php echo esc_attr( $badge_id ); ?>"><?php echo esc_html( $sl['value'] . $sl['suffix'] ); ?></span>
                                </div>
                                <p class="simpcucu-slider-hint"><?php echo esc_html( $sl['hint'] ); ?></p>
                                <input
                                    type="range"
                                    class="simpcucu-range-visible"
                                    name="simpcucu_options[<?php echo esc_attr( $sl['name'] ); ?>]"
                                    min="<?php echo esc_attr( $min ); ?>"
                                    max="<?php echo esc_attr( $max ); ?>"
                                    step="<?php echo esc_attr( $sl['step'] ); ?>"
                                    value="<?php echo esc_attr( $sl['value'] ); ?>"
                                    data-badge="<?php echo esc_attr( $badge_id ); ?>"
                                    data-suffix="<?php echo esc_attr( $sl['suffix'] ); ?>"
                                    style="--simpcucu-fill:<?php echo esc_attr( $pct ); ?>%"
                                >
                            </div>
                            <?php endforeach; ?>

                            <hr class="simpcucu-divider">

                            <!-- Mobile toggle -->
                            <div class="simpcucu-toggle-row">
                                <div class="simpcucu-toggle-info">
                                    <span class="simpcucu-toggle-title"><?php esc_html_e( 'Enable on Mobile / Touch', 'simple-custom-cursor' ); ?></span>
                                    <span class="simpcucu-toggle-desc"><?php esc_html_e( 'Off by default. Touch devices use the native cursor unless enabled.', 'simple-custom-cursor' ); ?></span>
                                </div>
                                <label class="simpcucu-toggle" aria-label="<?php esc_attr_e( 'Enable on mobile', 'simple-custom-cursor' ); ?>">
                                    <input type="checkbox" name="simpcucu_options[enable_on_mobile]" value="1" <?php checked( $o['enable_on_mobile'], 1 ); ?>>
                                    <span class="simpcucu-toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- ── Save bar ── -->
                    <div class="simpcucu-save-bar">
                        <span class="simpcucu-save-note"><?php esc_html_e( 'Changes apply to the frontend immediately after saving.', 'simple-custom-cursor' ); ?></span>
                        <button type="submit" class="simpcucu-save-btn">
                            <?php esc_html_e( 'Save Settings', 'simple-custom-cursor' ); ?>
                        </button>
                    </div>

                </div><!-- .simpcucu-content -->
            </div><!-- .simpcucu-layout -->
        </form>
    </div><!-- .simpcucu-wrap -->
    <p class="simpcucu-credit">Created by <a href="https://www.novaadvertising.com" target="_blank" rel="noopener noreferrer">NOVA Advertising</a></p>
    <?php
}
