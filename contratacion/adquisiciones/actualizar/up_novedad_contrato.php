<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$novedad = isset($_POST['slcTipoNovedad']) ? $_POST['slcTipoNovedad'] : exit('Accion no permitida');
$id_novedad = $_POST['id_novendad'];
$observacion = $_POST['txtAObservaNov'];
$iduser = $_SESSION['id_user'];
$tipouser = 'user';
$endp = 'adicion_prorroga';
$val_adicion = NULL;
$fec_adicion = NULL;
$cdp = NULL;
$fini_pro = NULL;
$ffin_pro = NULL;
switch ($novedad) {
    case '1':
        $val_adicion = $_POST['numValAdicion'];
        $fec_adicion = $_POST['datFecAdicion'];
        $cdp = $_POST['slcCDP'];
        break;
    case '2':
        $fini_pro = $_POST['datFecIniProrroga'];
        $ffin_pro = $_POST['datFecFinProrroga'];
        break;
    case '3':
        $val_adicion = $_POST['numValAdicion'];
        $fec_adicion = $_POST['datFecAdicion'];
        $cdp = $_POST['slcCDP'];
        $fini_pro = $_POST['datFecIniProrroga'];
        $ffin_pro = $_POST['datFecFinProrroga'];
        break;
    case '4':
        $fec_cesion = $_POST['datFecCesion'];
        $id_tercero = $_POST['slcTerceroCesion'];
        $id_contrato = $_POST['id_contrato'];
        $endp = 'cesion';
        $data = [
            "id_contrato" => $id_contrato,
            "id_novedad" => $id_novedad,
            "fec_cesion" => $fec_cesion,
            "id_tercero" => $id_tercero,
            "observacion" => $observacion,
            "iduser" => $iduser,
            "tipouser" => $tipouser,
        ];
        break;
    case '5':
        $fini_susp = $_POST['datFecIniSuspencion'];
        $ffin_susp = $_POST['datFecFinSuspencion'];
        $id_contrato = $_POST['id_contrato'];
        $endp = 'suspension';
        $data = [
            "id_contrato" => $id_contrato,
            "id_novedad" => $id_novedad,
            "fini_susp" => $fini_susp,
            "ffin_susp" => $ffin_susp,
            "observacion" => $observacion,
            "iduser" => $iduser,
            "tipouser" => $tipouser,
        ];
        break;
    case '6':
        $frein = $_POST['datFecReinicio'];
        $endp = 'reinicio';
        $data = [
            "id_novedad" => $id_novedad,
            "frein" => $frein,
            "observacion" => $observacion,
            "iduser" => $iduser,
            "tipouser" => $tipouser,
        ];
        break;
    case '7':
        $id_tt = $_POST['slcTipTerminacion'];
        $endp = 'terminacion';
        $data = [
            "id_novedad" => $id_novedad,
            "id_tt" => $id_tt,
            "observacion" => $observacion,
            "iduser" => $iduser,
            "tipouser" => $tipouser,
        ];
        break;
    case '8':
        $fec_liq = $_POST['datFecLiq'];
        $tip_liq = $_POST['slcTipLiquidacion'];
        $val_ctte = $_POST['numValFavorCtrate'];
        $val_ctta = $_POST['numValFavorCtrista'];
        $endp = 'liquidacion';
        $data = [
            "id_novedad" => $id_novedad,
            "fec_liq" => $fec_liq,
            "tip_liq" => $tip_liq,
            "val_ctte" => $val_ctte,
            "val_ctta" => $val_ctta,
            "observacion" => $observacion,
            "iduser" => $iduser,
            "tipouser" => $tipouser,
        ];
        break;
}
if ($novedad == '1' || $novedad == '2' || $novedad == '3') {
    $data = [
        "id_novedad" => $id_novedad,
        "tip_novedad" => $novedad,
        "val_adicion" => $val_adicion,
        "fec_adicion" => $fec_adicion,
        "cdp" => $cdp,
        "fini_pro" => $fini_pro,
        "ffin_pro" => $ffin_pro,
        "observacion" => $observacion,
        "iduser" => $iduser,
        "tipouser" => $tipouser,
    ];
}
//API URL
$url = $api . 'terceros/datos/res/actualizar/novedad/' . $endp;
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
