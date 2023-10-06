<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
include '../../conexion.php';
//API URL
$cantidades = isset($_POST['cantidad']) ? $_POST['cantidad'] : exit('Acción no permitida');
$id_contrato = $_POST['id_contrato'];
$cantMaxima = $_POST['numCantMax'];
$idApi = $_POST['idApi'];
$idProd = $_POST['idProd'];
$idtentrada = $_POST['idtentrada'];
$sede = 1;
$bodega = 1;
$id_entrada = $_POST['id_entrada'];
$idadq = $_POST['idadq'];
$idTerApi = $_POST['idTerApi'];
$valunit = str_replace('.', '', str_replace('$', '', $_POST['valor']));
$valunit = str_replace(',', '.', $valunit);
$lotes = $_POST['lote'];
$fec_vence = $_POST['fec_vence'];
$marcas = $_POST['txtMarca'];
$iduser = $_SESSION['id_user'];
$tipuser = 'user';
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$recibido = 0;
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT `val_prom`, `existencia`, `fec_reg` FROM `seg_detalle_entrada_almacen` WHERE  `id_prod` = '$idProd' ORDER BY `id_entrada` DESC LIMIT 1";
    $rs = $cmd->query($sql);
    $valpromedio = $rs->fetch();
    $valprom = $valpromedio['val_prom'] == '' ? $valunit : ($valpromedio['val_prom'] + $valunit) / 2;
    $sql = "INSERT INTO `seg_detalle_entrada_almacen`(`id_prod`,`id_sede`,`id_bodega`,`id_tercero_api`,`id_tipo_entrada` ,`id_entra`,`cant_ingresa`,`valu_ingresa`,`val_prom`,`lote`,`id_marca`,`fecha_vence`, `existencia`,`id_user_reg`,`fec_reg`) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $idProd, PDO::PARAM_INT);
    $sql->bindParam(2, $sede, PDO::PARAM_INT);
    $sql->bindParam(3, $bodega, PDO::PARAM_INT);
    $sql->bindParam(4, $idTerApi, PDO::PARAM_INT);
    $sql->bindParam(5, $idtentrada, PDO::PARAM_INT);
    $sql->bindParam(6, $id_entrada, PDO::PARAM_INT);
    $sql->bindParam(7, $cntdd, PDO::PARAM_INT);
    $sql->bindParam(8, $valunit, PDO::PARAM_STR);
    $sql->bindParam(9, $valprom, PDO::PARAM_STR);
    $sql->bindParam(10, $lote, PDO::PARAM_STR);
    $sql->bindParam(11, $id_marca, PDO::PARAM_INT);
    $sql->bindParam(12, $vence, PDO::PARAM_STR);
    $sql->bindParam(13, $existe, PDO::PARAM_INT);
    $sql->bindParam(14, $iduser, PDO::PARAM_INT);
    $sql->bindValue(15, $date->format('Y-m-d H:i:s'));
    foreach ($cantidades as $key => $value) {
        $query = "SELECT `existencia`, `fec_reg` FROM `seg_detalle_entrada_almacen` WHERE  `id_prod` = '$idProd' ORDER BY `id_entrada` DESC LIMIT 1";
        $rs = $cmd->query($query);
        $existencia_entrada = $rs->fetch();
        $e_entrada = $existencia_entrada['fec_reg'] == '' ?  '0' : $existencia_entrada['fec_reg'];
        $query = "SELECT `existencia`, `fec_reg` FROM `seg_salidas_almacen` WHERE  `id_producto` = '$idProd' ORDER BY `id_entrada` DESC LIMIT 1";
        $rs = $cmd->query($query);
        $existencia_salida = $rs->fetch();
        $e_salida = $existencia_salida['fec_reg'] == '' ?  '0' : $existencia_salida['fec_reg'];
        $cntdd = $value;
        if ($e_salida == '0' && $e_entrada == '0') {
            $existe = $cntdd;
        } else if ($e_entrada > $e_salida) {
            $existe = $existencia_entrada['existencia'] + $cntdd;
        } else {
            $existe = $existencia_salida['existencia'] + $cntdd;
        }
        $lote = $lotes[$key];
        $id_marca = $marcas[$key];
        $vence = $fec_vence[$key];
        $sql->execute();
        if ($cmd->lastInsertId() > 0) {
            $recibido += $cntdd;
        } else {
            echo $sql->errorInfo()[2];
        }
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
if ($cntdd == $cantMaxima) {
    $estado = '1';
} else {
    $estado = '2';
}
$data = [
    "id" => $idApi,
    "cant_rec" => $cntdd,
    "iduser" => $iduser,
    "tipuser" => $tipuser,
    "estado" => $estado,
    "id_contrato" => $id_contrato
];

//API
$url = $api . 'terceros/datos/res/actualizar/estado_entrega';
$ch = curl_init($url);
//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$payload = json_encode($data);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res = curl_exec($ch);
curl_close($ch);
echo json_decode($res, true);
