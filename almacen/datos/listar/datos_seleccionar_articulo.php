<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$busca = isset($_POST['term']) ? $_POST['term'] : exit('Acción no permitida');
$tipo = $_POST['tipoB'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    switch ($tipo) {
        case '1':
            $sql = "SELECT `id_b_s`,`bien_servicio` AS `label` 
                    FROM (SELECT
                            `seg_bien_servicio`.`id_b_s`
                            , `seg_bien_servicio`.`bien_servicio`
                            , `seg_tipo_contrata`.`id_tipo_compra`
                            , `seg_tipo_contrata`.`id_tipo`
                        FROM
                            `seg_bien_servicio`
                            INNER JOIN `seg_tipo_bien_servicio` 
                                ON (`seg_bien_servicio`.`id_tipo_bn_sv` = `seg_tipo_bien_servicio`.`id_tipo_b_s`)
                            INNER JOIN `seg_tipo_contrata` 
                                ON (`seg_tipo_bien_servicio`.`id_tipo_cotrato` = `seg_tipo_contrata`.`id_tipo`)
                        WHERE `seg_tipo_contrata`.`id_tipo_compra` = 1 AND `seg_tipo_contrata`.`id_tipo` <> 7) AS t 
                    WHERE `bien_servicio` LIKE '%$busca%' OR `id_b_s` LIKE '%$busca%' ORDER BY `bien_servicio` ASC";
            break;
        case '2':
            $sql = "SELECT `id_b_s`,`lote` AS `label`
                    FROM (SELECT
                            `seg_bien_servicio`.`id_b_s`
                            , `seg_bien_servicio`.`id_tipo_bn_sv`
                            , `seg_bien_servicio`.`bien_servicio`
                            , `seg_tipo_contrata`.`id_tipo_compra`
                            , `seg_tipo_bien_servicio`.`id_tipo_cotrato`
                            , `seg_tipo_contrata`.`id_tipo`
                            , `seg_detalle_entrada_almacen`.`lote`
                        FROM
                            `seg_bien_servicio`
                            INNER JOIN `seg_tipo_bien_servicio` 
                                ON (`seg_bien_servicio`.`id_tipo_bn_sv` = `seg_tipo_bien_servicio`.`id_tipo_b_s`)
                            INNER JOIN `seg_tipo_contrata` 
                                ON (`seg_tipo_bien_servicio`.`id_tipo_cotrato` = `seg_tipo_contrata`.`id_tipo`)
                            INNER JOIN `seg_detalle_entrada_almacen` 
                                ON (`seg_detalle_entrada_almacen`.`id_prod` = `seg_bien_servicio`.`id_b_s`)
                        WHERE `seg_tipo_contrata`.`id_tipo_compra` = 1 AND `seg_tipo_contrata`.`id_tipo` <> 7) AS t 
                    WHERE `lote` LIKE '%$busca%' ORDER BY `bien_servicio` ASC";
            break;
    }
    $rs = $cmd->query($sql);
    $bs_se = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
foreach ($bs_se as $bs) {
    $data[] = [
        'id' => $bs['id_b_s'],
        'label' => $bs['label'],
    ];
}
if (empty($data)) {
    $data[] = [
        'id' => '0',
        'label' => 'No se encontraron coincidencias...',
    ];
}
echo json_encode($data);
