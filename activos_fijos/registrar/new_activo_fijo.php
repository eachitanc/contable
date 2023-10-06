<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
include '../../conexion.php';
//API URL
$cantidad = isset($_POST['cantidad']) ? $_POST['cantidad'] : exit('Acción no permitida');
$id_contrato = $_POST['id_contrato'];
$id_entrd = $_POST['id_entrada'];
$cantMaxima = $_POST['numCantMax'];
$idApi = $_POST['idApi'];
$idProd = $_POST['idProd'];
$idadq = $_POST['idadq'];
$idTerApi = $_POST['idTerApi'];
$valunit = str_replace(',', '.', str_replace('.', '', str_replace('$', '', $_POST['valor_unit'])));
$mantenimiento = $_POST['mantenimiento'];
$depresiacion = $_POST['slcDepresiacion'];
$marca = $_POST['txtMarca'];
$modelo = $_POST['txtModelo'];
$tipo_activo = $_POST['slcTipoActivo'];
$series = explode('|', $_POST['txtSeriales']);
$observacion = $_POST['txtObservaActFijo'];
$iduser = $_SESSION['id_user'];
$tipuser = 'user';
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$penul = $_POST['penul'];
$recibido = 0;
$buscar_serial = str_replace('|', ',',  $_POST['txtSeriales']);
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `num_serial` FROM  `seg_num_serial` WHERE `num_serial` IN ($buscar_serial)";
    $rs = $cmd->query($sql);
    $lseriales = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
if (!empty($lseriales)) {
    $lseriales_exist = '';
    foreach ($lseriales as $serial) {
        $lseriales_exist .= $serial['num_serial'] . '<br>';
    }
    echo 'Serial(es) ya existentes : <br>' . $lseriales_exist;
    exit();
} else {
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "INSERT INTO `seg_entra_detalle_activos_fijos`
                    (`id_prod`, `id_entra_acfijo_do`, `mantenimiento`, `depreciable`, `marca`, `modelo`, `val_unit`, `cantidad`, `descripcion`, `id_tipo_activo`, `id_user_reg`, `fec_reg`)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $idProd, PDO::PARAM_INT);
        $sql->bindParam(2, $id_entrd, PDO::PARAM_INT);
        $sql->bindParam(3, $mantenimiento, PDO::PARAM_INT);
        $sql->bindParam(4, $depresiacion, PDO::PARAM_INT);
        $sql->bindParam(5, $marca, PDO::PARAM_STR);
        $sql->bindParam(6, $modelo, PDO::PARAM_STR);
        $sql->bindParam(7, $valunit, PDO::PARAM_STR);
        $sql->bindParam(8, $cantidad, PDO::PARAM_INT);
        $sql->bindParam(9, $observacion, PDO::PARAM_STR);
        $sql->bindParam(10, $tipo_activo, PDO::PARAM_INT);
        $sql->bindParam(11, $iduser, PDO::PARAM_INT);
        $sql->bindValue(12, $date->format('Y-m-d H:i:s'));
        $sql->execute();
        $id_reg = $cmd->lastInsertId();
        if ($id_reg > 0) {
            $sql = "SELECT
                        `seg_tipo_compra`.`id_tipo` AS `id_tipo_compra`
                        , `seg_tipo_compra`.`tipo_compra`
                        , `seg_tipo_contrata`.`id_tipo` AS `id_tipo_contrato`
                        , `seg_tipo_contrata`.`tipo_contrato`
                        , `seg_tipo_bien_servicio`.`id_tipo_b_s`
                        , `seg_tipo_bien_servicio`.`tipo_bn_sv`
                        , `seg_bien_servicio`.`id_b_s`
                        , `seg_bien_servicio`.`bien_servicio`
                    FROM
                        `seg_bien_servicio`
                        INNER JOIN `seg_tipo_bien_servicio` 
                            ON (`seg_bien_servicio`.`id_tipo_bn_sv` = `seg_tipo_bien_servicio`.`id_tipo_b_s`)
                        INNER JOIN `seg_tipo_contrata` 
                            ON (`seg_tipo_bien_servicio`.`id_tipo_cotrato` = `seg_tipo_contrata`.`id_tipo`)
                        INNER JOIN `seg_tipo_compra` 
                            ON (`seg_tipo_contrata`.`id_tipo_compra` = `seg_tipo_compra`.`id_tipo`)
                    WHERE `seg_bien_servicio`.`id_b_s` = $idProd";
            $rs = $cmd->query($sql);
            $placa_gen = $rs->fetch();
            $baseP = str_pad($placa_gen['id_tipo_compra'], 2, '0', STR_PAD_LEFT) . str_pad($placa_gen['id_tipo_contrato'], 2, '0', STR_PAD_LEFT) . str_pad($placa_gen['id_tipo_b_s'], 3, '0', STR_PAD_LEFT) . str_pad($placa_gen['id_b_s'], 4, '0', STR_PAD_LEFT);
            foreach ($series as $serie) {
                if ($serie != '0') {
                    $sql = "INSERT INTO `seg_num_serial` (`id_activo_fijo`, `num_serial`, `id_user_reg`, `fec_reg`)
                            VALUES (?, ?, ?, ?)";
                    $sql = $cmd->prepare($sql);
                    $sql->bindParam(1, $id_reg, PDO::PARAM_INT);
                    $sql->bindParam(2, $serie, PDO::PARAM_STR);
                    $sql->bindParam(3, $iduser, PDO::PARAM_INT);
                    $sql->bindValue(4, $date->format('Y-m-d H:i:s'));
                    $sql->execute();
                    $id_serpl = $cmd->lastInsertId();
                    if ($id_serpl > 0) {
                        $placa = $baseP . str_pad($id_serpl, 4, '0', STR_PAD_LEFT);
                        $sql = "UPDATE `seg_num_serial` SET `placa` = ? WHERE `id_serial` = ?";
                        $sql = $cmd->prepare($sql);
                        $sql->bindParam(1, $placa, PDO::PARAM_STR);
                        $sql->bindParam(2, $id_serpl, PDO::PARAM_INT);
                        $sql->execute();
                        $recibido++;
                        $centro_costo = 1;
                        $estado_af = 1;
                        $sql = "INSERT INTO `seg_ubica_traslado_centro_costo` (`id_serial`, `id_centro_costo`, `fecha`, `estado`, `id_user_reg`, `fec_reg`) 
                                VALUES (? , ? , ? , ? , ? , ?)";
                        $sql = $cmd->prepare($sql);
                        $sql->bindParam(1, $id_serpl, PDO::PARAM_INT);
                        $sql->bindParam(2, $centro_costo, PDO::PARAM_INT);
                        $sql->bindValue(3, $date->format('Y-m-d'));
                        $sql->bindParam(4, $estado_af, PDO::PARAM_INT);
                        $sql->bindParam(5, $iduser, PDO::PARAM_INT);
                        $sql->bindValue(6, $date->format('Y-m-d H:i:s'));
                        $sql->execute();
                    } else {
                        echo $sql->errorInfo()[2] . '<br> Serial: ' . $serie . '<br>';
                    }
                }
            }
        }
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
    if ($cantidad == $cantMaxima) {
        $estado = '1';
    } else {
        $estado = '2';
    }
    $data = [
        "id" => $idApi,
        "cant_rec" => $cantidad,
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
    $response = json_decode($res, true);
    if ($response == '1' && $penul == '1') {
        $estado = 11;
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "UPDATE `seg_adquisiciones` SET `estado` = ?, `id_user_act`= ?, `fec_act` = ? WHERE `id_adquisicion` = ?";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $estado, PDO::PARAM_INT);
        $sql->bindParam(2, $iduser, PDO::PARAM_INT);
        $sql->bindValue(3, $date->format('Y-m-d H:i:s'));
        $sql->bindParam(4, $idadq, PDO::PARAM_INT);
        $sql->execute();
        if ($sql->rowCount() > 0) {
            echo '1';
        } else {
            echo $sql->errorInfo()[2];
        }
        $cmd = null;
    } else {
        echo $response;
    }
}
