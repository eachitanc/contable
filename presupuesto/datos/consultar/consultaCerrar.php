<?php

include '../../../conexion.php';
$conexion = new mysqli($bd_servidor, $bd_usuario, $bd_clave, $bd_base);
$data = file_get_contents("php://input");
//consulto el tipo de documento que se esta Procesando
$sql = "SELECT tipo_doc FROM seg_pto_documento WHERE id_pto_doc = $data";
$res = $conexion->query($sql);
$tipoDoc = $res->fetch_assoc();
$tipoDoc = $tipoDoc['tipo_doc'];
if ($tipoDoc == 'ADI') {
    $sql = "SELECT
        SUM(`seg_pto_mvto`.`valor`) as valorsum
        , `seg_pto_cargue`.`id_pto_presupuestos`
        , `seg_pto_mvto`.`id_pto_doc`
        FROM
        `seg_pto_mvto`
        INNER JOIN `seg_pto_cargue` 
            ON (`seg_pto_mvto`.`rubro` = `seg_pto_cargue`.`cod_pptal`)
        WHERE (`seg_pto_cargue`.`id_pto_presupuestos` ='1'
        AND `seg_pto_mvto`.`id_pto_doc` =$data)
        GROUP BY `seg_pto_cargue`.`id_pto_presupuestos`, `seg_pto_mvto`.`id_pto_doc`;";
    $res = $conexion->query($sql);
    $sumaMov = $res->fetch_assoc();
    $valor1 = $sumaMov['valorsum'];
    $sql = "SELECT
        SUM(`seg_pto_mvto`.`valor`) as valorsum
        , `seg_pto_cargue`.`id_pto_presupuestos`
        , `seg_pto_mvto`.`id_pto_doc`
        FROM
        `seg_pto_mvto`
        INNER JOIN `seg_pto_cargue` 
            ON (`seg_pto_mvto`.`rubro` = `seg_pto_cargue`.`cod_pptal`)
        WHERE (`seg_pto_cargue`.`id_pto_presupuestos` ='2'
        AND `seg_pto_mvto`.`id_pto_doc` =$data)
        GROUP BY `seg_pto_cargue`.`id_pto_presupuestos`, `seg_pto_mvto`.`id_pto_doc`;";
    $res = $conexion->query($sql);
    $sumaMov2 = $res->fetch_assoc();
    $valor2 = $sumaMov2['valorsum'];
}
if ($tipoDoc == 'TRA') {
    $sql = "SELECT
                SUM(`seg_pto_mvto`.`valor`) as valorsum
            FROM
                `seg_pto_mvto`
                INNER JOIN `seg_pto_documento` 
                    ON (`seg_pto_mvto`.`id_pto_doc` = `seg_pto_documento`.`id_pto_doc`)
            WHERE (`seg_pto_mvto`.`tipo_mov` ='TRA'
                AND `seg_pto_mvto`.`id_pto_doc` =$data
                AND `seg_pto_mvto`.`mov` =1)";
    $res = $conexion->query($sql);
    $sumaMov2 = $res->fetch_assoc();
    $valor2 = $sumaMov2['valorsum'];
    // Consulta segundo movimiento
    $sql = "SELECT
                SUM(`seg_pto_mvto`.`valor`) as valorsum
            FROM
                `seg_pto_mvto`
                INNER JOIN `seg_pto_documento` 
                    ON (`seg_pto_mvto`.`id_pto_doc` = `seg_pto_documento`.`id_pto_doc`)
            WHERE (`seg_pto_mvto`.`tipo_mov` ='TRA'
                AND `seg_pto_mvto`.`id_pto_doc` =$data
                AND `seg_pto_mvto`.`mov` =0)";
    $res = $conexion->query($sql);
    $sumaMov1 = $res->fetch_assoc();
    $valor1 = $sumaMov1['valorsum'];
}
$dif = $valor1 - $valor2;

if ($tipoDoc == 'APL') {
    $dif = 0;
}
if ($dif == 0) {
    // update seg_ctb_libaux set estado='C' where id_ctb_doc=$data;
    $sql = "UPDATE seg_pto_documento SET estado=0 WHERE id_pto_doc=$data";
    $res = $conexion->query($sql);
    $response[] = array("value" => "ok");
} else {
    $response[] = array("value" => "no");
}
echo json_encode($response);
$conexion->close();
exit;
