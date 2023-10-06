<?php

include '../../../conexion.php';
$conexion = new mysqli($bd_servidor, $bd_usuario, $bd_clave, $bd_base);

if (isset($_POST['search'])) {
    $search = mysqli_real_escape_string($conexion, $_POST['search']);
    $presupuesto = mysqli_real_escape_string($conexion, $_POST['valor']);
    $estado = mysqli_real_escape_string($conexion, $_POST['estado']);
    $tipo_doc = mysqli_real_escape_string($conexion, $_POST['tipo_doc']);
    $id_ingreso = mysqli_real_escape_string($conexion, $_POST['id_ingreso']);
    $id_gasto = mysqli_real_escape_string($conexion, $_POST['id_gasto']);
    if ($tipo_doc == 'TRA') {
        $sql = "SELECT cod_pptal,nom_rubro,tipo_dato FROM seg_pto_cargue WHERE cod_pptal LIKE '$search%' AND id_pto_presupuestos=$id_gasto";
    } else {
        if ($estado == 'true') {
            $sql = "SELECT cod_pptal,nom_rubro,tipo_dato FROM seg_pto_cargue WHERE cod_pptal LIKE '$search%' AND id_pto_presupuestos=$id_ingreso";
        } else {
            $sql = "SELECT cod_pptal,nom_rubro,tipo_dato FROM seg_pto_cargue WHERE cod_pptal LIKE '$search%' AND id_pto_presupuestos=$id_gasto";
        }
    }
    $res = $conexion->query($sql);
    // verifico si la consulta fue exitosa
    if ($res->num_rows > 0) {
        while ($row = $res->fetch_assoc()) {
            $response[] = array("value" => $row['cod_pptal'], "label" => $row['cod_pptal'] . " - " . $row['nom_rubro'], "tipo" => $row['tipo_dato']);
        }
    } else {
        $response[] = array("value" => "", "label" => "No encontrado...", "tipo" => "3");
    }
    echo json_encode($response);
}

exit;
