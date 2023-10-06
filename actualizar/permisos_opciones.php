<?php

session_start();
include '../conexion.php';
$iduser = isset($_POST['iduser']) ? $_POST['iduser'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_modulo`
            FROM
                `seg_permisos_modulos`
            WHERE `id_usuario` = '$iduser'";
    $rs = $cmd->query($sql);
    $modulos = $rs->fetchAll();
    if (!empty($modulos)) {
        $idMod = [];
        foreach ($modulos as $m) {
            $idMod[] = $m['id_modulo'];
        }
        $idMod = implode(',', $idMod);
        $sql = "SELECT
                    `seg_opciones_modulos`.`id_opcion`, `seg_modulos`.`descripcion`, `seg_opciones_modulos`.`opcion`
                FROM
                    `seg_opciones_modulos`
                INNER JOIN `seg_modulos` 
                    ON (`seg_opciones_modulos`.`id_modulo` = `seg_modulos`.`id_modulo`)
                WHERE `seg_opciones_modulos`.`id_modulo` IN ($idMod)";
        $rs = $cmd->query($sql);
        $opciones = $rs->fetchAll();
        $idOpc = [];
        foreach ($opciones as $o) {
            $idOpc[] = $o['id_opcion'];
        }
        $idOpc = implode(',', $idOpc);
        $sql = "SELECT
                    `id_opcion`,`id_permiso`
                FROM
                    `seg_permiso_opciones`
                WHERE `id_usuario` = '$iduser' AND `id_opcion` IN ($idOpc)";
        $rs = $cmd->query($sql);
        $permisos = $rs->fetchAll();
        $lista = '';
        $lista = '<div class="px-4">
                    <table class="table-striped table-bordered table-sm nowrap w-100">
                    <thead>
                        <tr>
                            <th>MÓDULO</th>
                            <th>OPCIÓN</th>
                            <th>PERMISO</th>
                        </tr>
                        </thead>';
        foreach ($opciones as $o) {
            $key = array_search($o['id_opcion'], array_column($permisos, 'id_opcion'));
            $id_permiso = $key !== false ? $permisos[$key]['id_permiso'] : '0';
            $icono = $key !== false ? 'toggle-on' : 'toggle-off';
            $color = $key !== false ? 'success' : 'secondary';
            $lista .= '<tr>
                            <td class="text-left">' . $o['descripcion'] . '</td>
                            <td class="text-left">' . $o['opcion'] . '</td>
                            <td>
                                <button class="btn-estado">
                                    <span class="fas fa-' . $icono . ' fa-lg text-' . $color . ' permOpc" value="' . $id_permiso.'|'.$o['id_opcion']. '" aria-hidden="true"></span>
                                </button>
                            </td>
                        </tr>';
        }
        $lista .= '</table>
                </div>';
        echo $lista;
    } else {
        echo '<div class="alert alert-warning" role="alert"><strong>Atención!</strong> No se ha asignado ningún módulo a este usuario.</div>';
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
