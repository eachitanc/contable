<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}
$vigencia = $_SESSION['vigencia'];
$tipoe = isset($_GET['tipo']) ? $_GET['tipo'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT 
                `id_entrada`,`consecutivo`,`id_tipo_entrada`,`id_tercero_api`,`no_factura`, `acta_remision`,`fec_entrada`,`estado`, `observacion` 
            FROM `seg_entrada_almacen` WHERE `vigencia` = '$vigencia' AND `id_tipo_entrada` = '$tipoe'";
    $rs = $cmd->query($sql);
    $listentradas = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$data = [];
if (isset($listentradas)) {
    if ($tipoe != 4) {
        $idst = [];
        $idin = '0';
        foreach ($listentradas as $l) {
            if ($l['id_tercero_api'] != '') {
                $idst[] = $l['id_tercero_api'];
            }
            $idin .= ',' . $l['id_entrada'];
        }
        $payload = json_encode($idst);
        //API URL
        $url = $api . 'terceros/datos/res/lista/terceros';
        $ch = curl_init($url);
        //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        $dat_ter = json_decode($result, true);
        if (empty($dat_ter) || $dat_ter == 0) {
            $dat_ter = [];
        }
        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
            $sql = "SELECT 
                    `id_entrada`, `id_entra`, `cant_ingresa`, `valu_ingresa`, `iva`
                FROM
                    `seg_detalle_entrada_almacen`
                WHERE `id_entra` IN ($idin)";
            $rs = $cmd->query($sql);
            $vals = $rs->fetchAll();
            $cmd = null;
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
        }
        foreach ($listentradas as $le) {
            $id_pret_dona = $le['id_entrada'];
            $detalles = $editar = $borrar = null;
            if ((intval($permisos['editar'])) == 1 && $le['estado'] <= 2) {
                $editar = '<button value="' . $id_pret_dona . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb editar" title="Actualizar o modificar"><span class="fas fa-pencil-alt fa-lg"></span></button>';
            }
            if ($le['estado'] == 1) {
                if ((intval($permisos['borrar'])) == 1) {
                    $borrar = '<button value="' . $id_pret_dona . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb borrar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></button>';
                }
            }
            $total = 0;
            $iva = 0;
            if (!empty($vals)) {
                foreach ($vals as $v) {
                    if ($v['id_entra'] == $id_pret_dona) {
                        $iva = $v['cant_ingresa'] * $v['valu_ingresa'] * $v['iva'] / 100;
                        $total = $total + $v['cant_ingresa'] * $v['valu_ingresa'] + $iva;
                    }
                }
            }
            $pdf = null;
            switch ($le['estado']) {
                case 1:
                    $estado = 'INICIALIZADO';
                    $coloricon = 'warning';
                    break;
                case 2:
                    $estado = 'ABIERTO';
                    $coloricon = 'secondary';
                    break;
                case 3:
                    $estado = 'CERRADO';
                    $coloricon = 'info';
                    break;
                default:
                    $estado = 'OTRO';
                    $coloricon = 'light';
                    break;
            }
            if ((intval($permisos['listar'])) == 1) {
                $detalles = '<button value="' . $id_pret_dona . '" class="btn btn-outline-' . $coloricon . ' btn-sm btn-circle shadow-gb detalles" title="Detalles"><span class="fas fa-eye fa-lg"></span></button>';
            }
            $key = array_search($le['id_tercero_api'], array_column($dat_ter, 'id_tercero'));
            if (false !== $key) {
                $ccnit = $dat_ter[$key]['cc_nit'];
                $nom_completo = $dat_ter[$key]['apellido1'] . ' ' . $dat_ter[$key]['apellido2'] . ' ' . $dat_ter[$key]['nombre1'] . ' ' . $dat_ter[$key]['nombre2'] . ' ' . $dat_ter[$key]['razon_social'];
            } else {
                $ccnit = $nom_completo = null;
            }
            if ($le['estado'] > 1) {
                $pdf = '<button value="' . $id_pret_dona . '" class="btn btn-outline-success btn-sm btn-circle shadow-gb imprimir" title="Imprimir entrada"><span class="fas fa-print fa-lg"></span></button>';
            }
            if ($id_pret_dona < 0) {
                $id_pret_dona = $id_pret_dona * -1;
            }
            $data[] = [
                'id_pd' => $id_pret_dona,
                'consecutivo' => $le['consecutivo'],
                'ccnit' => $ccnit,
                'nom_completo' => $nom_completo,
                'acta_remision' => $le['acta_remision'],
                'fec_presta_dona' => $le['fec_entrada'],
                'detalle' => $le['observacion'],
                'total' => pesos($total),
                'estado' => $estado,
                'botones' => '<div class="text-center">' . $editar . $borrar . $detalles . $pdf . '</div>',
            ];
        }
    } else {
        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base_f;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
            $sql = "SELECT 
                        `id_ingreso` AS `id_entrada`
                        ,`tipo_ingreso` AS`id_tipo_entrada`
                        ,`nit_tercero` AS`id_tercero_api`
                        ,`num_factura` AS `no_factura`
                        ,'' AS `acta_remision`
                        ,`fec_factura` AS `fec_entrada`
                        ,`estado`
                        ,`detalle`
                        ,'CRHON' AS `procede` 
                    FROM `vista_entrada_farmacia`
                    WHERE  `id_ingreso` NOT IN (SELECT `id_cronhis` FROM $bd_base.`seg_entrada_almacen` WHERE `vigencia` = '$vigencia' AND `id_cronhis` <> 0) AND (`id_tipo_ingreso` = 3 OR `id_tipo_ingreso` = 6)";
            $rs = $cmd->query($sql);
            $listentradas = $rs->fetchAll(PDO::FETCH_ASSOC);
            $cmd = null;
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
        }
        $terceros = [];
        $idta = [];
        foreach ($listentradas as $le) {
            if ($le['procede'] == 'EACI' && $le['id_tercero_api'] != '') {
                $idta[] = $le['id_tercero_api'];
            }
        }
        if (!empty($idta)) {
            $payload = json_encode($idta);
            //API URL
            $url = $api . 'terceros/datos/res/lista/terceros';
            $ch = curl_init($url);
            //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            curl_close($ch);
            $terceros = json_decode($result, true);
        }
        $terceros_nit = [];
        $id_nit = [];
        foreach ($listentradas as $le) {
            if ($le['procede'] == 'CRHON' && $le['id_tercero_api'] != '') {
                $id_nit[] = explode('-', $le['id_tercero_api'])[0];
            }
        }
        if (!empty($id_nit)) {
            $payload = json_encode($id_nit);
            //API URL
            $url = $api . 'terceros/datos/res/lista/terceros/xdcto';
            $ch = curl_init($url);
            //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            curl_close($ch);
            $terceros_nit = json_decode($result, true);
        }
        $data = [];
        foreach ($listentradas as $le) {
            $id_entrada = $le['id_entrada'];
            $detalle = $estado = $pdf =  null;
            if ($le['estado'] == 1 && $le['procede'] == 'CRHON') {
                if ((intval($permisos['editar'])) == 1) {
                    $detalle = '<button value="' . $id_entrada . '" class="btn btn-outline-success btn-sm btn-circle shadow-gb recibir" title="Recibir"><span class="fas fa-download"></span></button>';
                    $estado = '<span class="badge badge-pill badge-warning">ABIERTO</span>';
                }
            }
            if ($le['estado'] >= 1 && $le['procede'] == 'EACI') {
                if ((intval($permisos['listar'])) == 1) {
                    $detalle = '<button value="' . $id_entrada . '" class="btn btn-outline-warning btn-sm btn-circle shadow-gb detalles" title="Detalles"><span class="fas fa-eye fa-lg"></span></button>';
                    $estado = '<span class="badge badge-pill badge-info">CERRADO</span>';
                    $pdf = '<button value="' . $id_entrada . '" class="btn btn-outline-success btn-sm btn-circle shadow-gb imprimir" title="Imprimir entrada"><span class="fas fa-print fa-lg"></span></button>';
                }
            }
            if ($le['procede'] == 'CRHON') {
                $idf = explode('-', $le['id_tercero_api'])[0];
                $key = array_search($idf, array_column($terceros_nit, 'cc_nit'));
                if (false !== $key) {
                    $nom_completo = $terceros_nit[$key]['apellido1'] . ' ' . $terceros_nit[$key]['apellido2'] . ' ' . $terceros_nit[$key]['nombre1'] . ' ' . $terceros_nit[$key]['nombre2'] . ' ' . $terceros_nit[$key]['razon_social'];
                    $ccnit = $terceros_nit[$key]['cc_nit'];
                } else {
                    $nom_completo = null;
                    $ccnit = null;
                }
            }
            if ($le['procede'] == 'EACI') {
                $key = array_search($le['id_tercero_api'], array_column($terceros, 'id_tercero'));
                if (false !== $key) {
                    $ccnit = $terceros[$key]['cc_nit'];
                    $nom_completo = $terceros[$key]['apellido1'] . ' ' . $terceros[$key]['apellido2'] . ' ' . $terceros[$key]['nombre1'] . ' ' . $terceros[$key]['nombre2'] . ' ' . $terceros[$key]['razon_social'];
                } else {
                    $nom_completo = null;
                    $ccnit = null;
                }
            }
            $data[] = [
                'id_pd' => $id_entrada,
                'consecutivo' => '',
                'ccnit' => $ccnit,
                'nom_completo' => $nom_completo,
                'acta_remision' => $le['no_factura'],
                'fec_presta_dona' => $le['fec_entrada'],
                'detalle' => $le['detalle'],
                'total' => '',
                'estado' => $estado,
                'botones' => '<div class="text-center">' . $detalle . $pdf . '</div>',
            ];
        }
    }
}
$datos = ['data' => $data];
echo json_encode($datos);
