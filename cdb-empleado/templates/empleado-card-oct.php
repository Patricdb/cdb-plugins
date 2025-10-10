<?php
/** Tarjeta empleado basada en SVG con contorno y textos */
if ( ! function_exists('cdb_empleado_get_card_data') ) return;

$empleado_id = $empleado_id ?? get_the_ID();
$data     = cdb_empleado_get_card_data( (int) $empleado_id );
$name     = $data['name'] ?? '';
$rank     = $data['rank_current'] ?? null;
$rank_str = is_numeric($rank) ? str_pad((string) (int) $rank, 2, '0', STR_PAD_LEFT) : 'ND';
$card_id  = 'empcard8-' . (int) $empleado_id;

$parts = preg_split('/\s+/', trim($name));
$line1 = mb_strtoupper( $parts[0] ?? '', 'UTF-8' );
$line2 = isset($parts[1]) ? mb_strtoupper( implode(' ', array_slice($parts,1)), 'UTF-8' ) : '';

$bg_svg = get_option( 'tarjeta_oct_bg_svg', '' );
if ( '' === $bg_svg ) {
    $bg_svg = file_get_contents( plugin_dir_path( __FILE__ ) . '../assets/svg/tarjeta-oct-bg.svg' );
}

$viewBox    = '0 0 888 874';
$user_note  = '';

// Extrae el contenido interno del SVG y los atributos necesarios de forma robusta.
if ( '' !== trim( $bg_svg ) ) {
    $doc = new DOMDocument();
    libxml_use_internal_errors( true );
    $doc->loadXML( $bg_svg );
    libxml_clear_errors();

    $svgs = $doc->getElementsByTagName( 'svg' );
    if ( $svgs->length > 0 ) {
        $svg       = $svgs->item( 0 );
        $vb_attr   = '';
        $w_attr    = '';
        $h_attr    = '';

        // Obtiene atributos sin importar mayúsculas/minúsculas.
        foreach ( $svg->attributes as $attr ) {
            if ( 0 === strcasecmp( $attr->nodeName, 'viewBox' ) ) {
                $vb_attr = $attr->nodeValue;
            } elseif ( 0 === strcasecmp( $attr->nodeName, 'width' ) ) {
                $w_attr = $attr->nodeValue;
            } elseif ( 0 === strcasecmp( $attr->nodeName, 'height' ) ) {
                $h_attr = $attr->nodeValue;
            }
        }

        if ( '' !== $vb_attr ) {
            $viewBox = $vb_attr;
        } elseif ( '' !== $w_attr && '' !== $h_attr ) {
            $w = preg_replace( '/[^0-9.]/', '', $w_attr );
            $h = preg_replace( '/[^0-9.]/', '', $h_attr );
            if ( '' !== $w && '' !== $h ) {
                $viewBox = "0 0 $w $h";
            } else {
                $user_note = esc_html__( 'Nota: el SVG necesita un viewBox definido.', 'cdb-empleado' );
            }
        } else {
            $user_note = esc_html__( 'Nota: el SVG necesita un viewBox definido.', 'cdb-empleado' );
        }

        // Serializa solamente los nodos hijos del SVG.
        $inner = '';
        foreach ( $svg->childNodes as $child ) {
            $inner .= $doc->saveXML( $child );
        }

        $bg_svg = $inner;
    }

    libxml_use_internal_errors( false );
}
?>

<div class="cdb-empcard8" role="region" aria-labelledby="<?php echo esc_attr($card_id); ?>">
  <svg viewBox="<?php echo esc_attr( $viewBox ); ?>" aria-label="<?php esc_attr_e('Tarjeta empleado', 'cdb-empleado'); ?>">
    <?php echo $bg_svg; ?>
    <g class="t" text-anchor="middle">
      <text class="t name" x="50%" y="155" font-size="90" id="<?php echo esc_attr($card_id); ?>">
        <tspan x="50%" dy="0"><?php echo esc_html($line1); ?></tspan>
        <?php if ( $line2 ) : ?>
        <tspan x="50%" dy="88"><?php echo esc_html($line2); ?></tspan>
        <?php endif; ?>
      </text>
    </g>
    <g class="t t--small" text-anchor="start">
      <text class="puesto" x="620" y="320" font-size="40"><?php esc_html_e('Puesto', 'cdb-empleado'); ?></text>
      <text class="num" x="620" y="540" font-size="180" font-weight="900"><?php echo esc_html($rank_str); ?></text>
    </g>
  </svg>
</div>
<?php if ( $user_note ) : ?>
<!-- <?php echo $user_note; ?> -->
<?php endif; ?>

