<?php
// Asegurarse de que WordPress está cargado.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Verificar si el usuario está conectado.
$current_user = wp_get_current_user();
if ( ! $current_user->exists() ) {
    echo cdb_empleo_get_mensaje( 'login_requerido' );
    return;
}

// Verificar si el usuario tiene el rol "Empleador" o "Administrator"
if ( ! in_array( 'empleador', (array) $current_user->roles ) && ! in_array( 'administrator', (array) $current_user->roles ) ) {
    echo cdb_empleo_get_mensaje( 'sin_permiso_form' );
    return;
}

// Preparar datos para el autocomplete del Bar.
$bares = get_posts( array(
    'post_type'   => 'bar',
    'numberposts' => -1,
    'orderby'     => 'title',
    'order'       => 'ASC',
    'post_status' => 'publish'
) );

$lista_bares = array();
foreach ( $bares as $bar ) {
    $lista_bares[] = array(
        'label' => $bar->post_title,
        'value' => $bar->post_title,
        'id'    => $bar->ID,
    );
}

// Obtener las posiciones (CPT "cdb_posiciones") ordenadas por puntuación (meta _cdb_posiciones_score)
$posiciones = get_posts( array(
    'post_type'      => 'cdb_posiciones',
    'posts_per_page' => -1,
    'meta_key'       => '_cdb_posiciones_score',
    'orderby'        => 'meta_value_num',
    'order'          => 'ASC',
    'post_status'    => 'publish'
) );
?>

<!-- Estilos del formulario -->
<style>
    .cdb-oferta-form {
        margin-top: 20px;
        padding: 15px;
        border: 1px solid #ddd;
        border-radius: 8px;
        background-color: #fff;
    }
    .cdb-oferta-form h2 {
        margin-bottom: 15px;
        font-size: 1.5em;
    }
    .cdb-oferta-form label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }
    .cdb-oferta-form select,
    .cdb-oferta-form input[type="text"],
    .cdb-oferta-form input[type="datetime-local"],
    .cdb-oferta-form textarea {
        width: 100%;
        padding: 8px;
        margin-bottom: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }
    .cdb-oferta-form button {
        padding: 10px 20px;
        background-color: black;
        color: #fff;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }
    .cdb-oferta-form button:hover {
        background-color: #444;
    }
</style>

<div class="cdb-oferta-form">
    <h2>Registrar Oferta de Empleo</h2>
    <form id="cdb_oferta_form">
        <!-- Acción para el AJAX -->
        <input type="hidden" name="action" value="cdb_guardar_oferta">
        <!-- Nonce de seguridad -->
        <input type="hidden" name="security" value="<?php echo wp_create_nonce('cdb_form_nonce'); ?>">
        <!-- Usuario (opcional, se puede obtener en el handler) -->
        <input type="hidden" name="user_id" value="<?php echo esc_attr($current_user->ID); ?>">

        <!-- Campo de autocompletado para Bar -->
        <label for="bar-search">Bar que lo oferta:</label>
        <input 
            type="text" 
            id="bar-search" 
            name="bar_search" 
            placeholder="Escribe el nombre del bar" 
            required
        >
        <!-- Hidden para guardar el ID real del Bar seleccionado -->
        <input type="hidden" id="bar_id" name="bar_id" value="">

        <!-- Selección de Posición -->
        <label for="posicion_id">Posición a ofertar:</label>
        <select name="posicion_id" id="posicion_id" required>
            <option value="">Selecciona una posición</option>
            <?php
            if ( $posiciones ) {
                foreach ( $posiciones as $posicion ) {
                    echo '<option value="' . esc_attr( $posicion->ID ) . '">' . esc_html( $posicion->post_title ) . '</option>';
                }
            }
            ?>
        </select>

        <!-- Tipo de Oferta -->
        <label for="tipo_oferta">Tipo de oferta:</label>
        <select name="tipo_oferta" id="tipo_oferta" required>
            <option value="">Selecciona el tipo de oferta</option>
            <option value="Extra">Extra</option>
            <option value="Cuadrilla de eventos">Cuadrilla de eventos</option>
            <option value="Jornada completa">Jornada completa</option>
            <option value="Media jornada">Media jornada</option>
        </select>

        <!-- Fecha y hora de incorporación -->
        <label for="fecha_incorporacion">Fecha y hora de incorporación:</label>
        <input type="datetime-local" name="fecha_incorporacion" id="fecha_incorporacion" required>

        <!-- Fecha y hora de fin -->
        <label for="fecha_fin">Fecha y hora de fin:</label>
        <input type="datetime-local" name="fecha_fin" id="fecha_fin" required>

        <!-- Nivel salarial -->
        <label for="nivel_salarial">Nivel salarial (0 a 4):</label>
        <select name="nivel_salarial" id="nivel_salarial" required>
            <option value="">Selecciona el nivel salarial</option>
            <?php for ( $i = 0; $i <= 4; $i++ ) : ?>
                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
            <?php endfor; ?>
        </select>

        <!-- Funciones a realizar -->
        <label for="funciones">Funciones a realizar:</label>
        <textarea name="funciones" id="funciones" rows="4" placeholder="Describe las funciones del puesto" required></textarea>

        <button type="submit">Guardar Oferta</button>
    </form>
</div>

<script>
window.cdbBaresData = <?php echo wp_json_encode($lista_bares); ?>;
</script>


<div id="cdb_oferta_mensaje"></div>
