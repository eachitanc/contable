<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';

$usuario = $_SESSION['id_user'];
$vigencia = $_SESSION['vigencia'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT 
                `t1`.`id_bodega`, `t1`.`sede`, `t1`.`bodega`, `t2`.`id_resp`, `t2`.`documento`, `t2`.`usuario`, `t2`.`fec_reg`
            FROM 
                (SELECT
                    `seg_bodega_almacen`.`id_bodega`
                    , `seg_sedes_empresa`.`nombre` AS `sede`
                    , `seg_bodega_almacen`.`nombre` AS `bodega`
                FROM
                    `seg_bodega_almacen`
                    INNER JOIN `seg_sedes_empresa` 
                    ON (`seg_bodega_almacen`.`id_sede` = `seg_sedes_empresa`.`id_sede`)) AS `t1`
            LEFT JOIN
                (SELECT
                    `seg_usuarios`.`documento`
                    , CONCAT_WS(' ', `seg_usuarios`.`nombre1`, `seg_usuarios`.`nombre2`, `seg_usuarios`.`apellido1`, `seg_usuarios`.`apellido2`) AS `usuario`
                    , `seg_usuarios`.`estado`
                    , `seg_responsable_bodega`.`id_resp`
                    , `seg_responsable_bodega`.`id_bodega`
                    , `seg_responsable_bodega`.`fec_reg`
                FROM
                    `seg_responsable_bodega`
                    INNER JOIN `seg_usuarios` 
                    ON (`seg_responsable_bodega`.`id_usuario` = `seg_usuarios`.`id_usuario`)
                WHERE `seg_responsable_bodega`.`id_resp` IN (SELECT MAX(`id_resp`) FROM `seg_responsable_bodega` GROUP BY (`id_bodega`)) AND  `seg_usuarios`.`estado` = 1) AS `t2`
            ON (`t1`.`id_bodega` = `t2`.`id_bodega`)";
    $rs = $cmd->query($sql);
    $bodegas = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

$data = [];
if (!empty($bodegas)) {
    foreach ($bodegas as $bg) {
        $id_bg = $bg['id_bodega'];
        $asigna = '<a value="' . $id_bg . '" class="btn btn-outline-info btn-sm btn-circle shadow-gb asignaResposable" title="Asignar Resposable"><span class="fas fa-user-cog fa-lg"></span></a>';
        $editar =  '<a value="' . $id_bg . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb editaBodega" title="Editar Bodega"><span class="fas fa-pencil-alt fa-lg"></span></a>';
        $data[] = [
            "id" => $id_bg,
            "sede" => $bg['sede'],
            "bodega" => $bg['bodega'],
            "responsable" => mb_strtoupper($bg['usuario']),
            "fecha" => '<div class="text-center centro-vertical">' . $bg['fec_reg'] . '</div>',
            "botones" => '<div class="text-center centro-vertical">' . $editar . $asigna . '</div>',
        ];
    }
}
$datos = ['data' => $data];
echo json_encode($datos);
