<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}
include '../../../../conexion.php';
include '../../../../permisos.php';
$id_ct = isset($_POST['id_csp']) ? $_POST['id_csp'] : exit('Acción no permitida');
//API URL
$url = $api . 'terceros/datos/res/listar/novedades_contrato/' . $id_ct;
$ch = curl_init($url);
//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
curl_close($ch);
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}
$novedades = json_decode($result, true);
$data = [];
$noaplica = '<div class="text-center">-</div>';
if (isset($novedades)) {
    if (!empty($novedades['adicion_prorroga'])) {
        foreach ($novedades['adicion_prorroga'] as $nv) {
            $id_ap = $nv['id_nov_con'];
            $id_tn = $nv['id_tip_nov'];
            $editar = '<a value="' . $id_ap . '|' . $id_tn . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
            $borrar = '<a value="' . $id_ap . '|' . $id_tn . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb borrar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
            $data[] = [
                'descripcion' => $nv['descripcion'],
                'valor' => isset($nv['val_adicion']) ? '<div class="text-right">' . pesos($nv['val_adicion']) . '</div>' : $noaplica,
                'fecha' => isset($nv['fec_adcion']) ? $nv['fec_adcion'] : $noaplica,
                'tipo' => $noaplica,
                'cdp' => isset($nv['cdp']) ? $nv['cdp'] : $noaplica,
                'fec_inicia' => isset($nv['fec_ini_prorroga']) ? $nv['fec_ini_prorroga'] : $noaplica,
                'fec_fin' => isset($nv['fec_fin_prorroga']) ? $nv['fec_fin_prorroga'] : $noaplica,
                'val_cte' => $noaplica,
                'val_cta' => $noaplica,
                'tercero' => $noaplica,
                'observacion' => $nv['observacion'],
                'botones' => '<div class="text-center">' . $editar . $borrar . '</div>',

            ];
        }
    }
    if (!empty($novedades['cesion'])) {
        foreach ($novedades['cesion'] as $nc) {
            $id_cs = $nc['id_cesion'];
            $id_tn = $nc['id_tipo_nov'];
            $editar = '<a value="' . $id_cs . '|' . $id_tn . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
            $borrar = '<a value="' . $id_cs . '|' . $id_tn . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb borrar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
            $data[] = [
                'descripcion' => $nc['descripcion'],
                'valor' => $noaplica,
                'fecha' => isset($nc['fec_cesion']) ? $nc['fec_cesion'] : $noaplica,
                'tipo' => $noaplica,
                'cdp' => $noaplica,
                'fec_inicia' => $noaplica,
                'fec_fin' => $noaplica,
                'val_cte' => $noaplica,
                'val_cta' => $noaplica,
                'tercero' => $nc['apellido1'] . ' ' . $nc['apellido2'] . ' ' . $nc['nombre1'] . ' ' . $nc['nombre2'] . ' ' . $nc['razon_social'],
                'observacion' => $nc['observacion'],
                'botones' => '<div class="text-center">' . $editar . $borrar . '</div>',

            ];
        }
    }
    if (!empty($novedades['suspension'])) {
        $editable = 0;
        foreach ($novedades['suspension'] as $ns) {
            $id_ss = $ns['id_suspension'];
            $id_tn = $ns['id_tipo_nov'];
            if ($editable == 0) {
                $editar = '<a value="' . $id_ss . '|' . $id_tn . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
                $borrar = '<a value="' . $id_ss . '|' . $id_tn . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb borrar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
            } else {
                $editar = null;
                $borrar = null;
            }
            $editable++;
            $data[] = [
                'descripcion' => $ns['descripcion'],
                'valor' => $noaplica,
                'fecha' => $noaplica,
                'tipo' => $noaplica,
                'cdp' => $noaplica,
                'fec_inicia' => $ns['fec_inicia'],
                'fec_fin' => $ns['fec_fin'],
                'val_cte' => $noaplica,
                'val_cta' => $noaplica,
                'tercero' => $noaplica,
                'observacion' => $ns['observacion'],
                'botones' => '<div class="text-center">' . $editar . $borrar . '</div>',

            ];
        }
    }
    if (!empty($novedades['reinicio'])) {
        $editable = 0;
        foreach ($novedades['reinicio'] as $nr) {
            $id_rn = $nr['id_reinicio'];
            $id_tn = $nr['id_tipo_nov'];
            if ($editable == 0) {
                $editar = '<a value="' . $id_rn . '|' . $id_tn . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
                $borrar = '<a value="' . $id_rn . '|' . $id_tn . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb borrar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
            } else {
                $editar = null;
                $borrar = null;
            }
            $editable++;
            $data[] = [
                'descripcion' => $nr['descripcion'],
                'valor' => $noaplica,
                'fecha' => $nr['fec_reinicia'],
                'tipo' => $noaplica,
                'cdp' => $noaplica,
                'fec_inicia' => $noaplica,
                'fec_fin' => $noaplica,
                'val_cte' => $noaplica,
                'val_cta' => $noaplica,
                'tercero' => $noaplica,
                'observacion' => $nr['observacion'],
                'botones' => '<div class="text-center">' . $editar . $borrar . '</div>',

            ];
        }
    }
    if (!empty($novedades['terminacion'])) {
        foreach ($novedades['terminacion'] as $nt) {
            $id_tm = $nt['id_terminacion'];
            $id_tn = $nt['id_tipo_nov'];
            $editar = '<a value="' . $id_tm . '|' . $id_tn . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
            $borrar = '<a value="' . $id_tm . '|' . $id_tn . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb borrar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
            $data[] = [
                'descripcion' => $nt['descripcion'],
                'valor' => $noaplica,
                'fecha' => $noaplica,
                'tipo' => $nt['desc_ter'],
                'cdp' => $noaplica,
                'fec_inicia' => $noaplica,
                'fec_fin' => $noaplica,
                'val_cte' => $noaplica,
                'val_cta' => $noaplica,
                'tercero' => $noaplica,
                'observacion' => $nt['observacion'],
                'botones' => '<div class="text-center">' . $editar . $borrar . '</div>',

            ];
        }
    }
    if (!empty($novedades['liquidacion'])) {
        foreach ($novedades['liquidacion'] as $nl) {
            $id_lq = $nl['id_liquidacion'];
            $id_tn = $nl['id_tipo_nov'];
            $editar = '<a value="' . $id_lq . '|' . $id_tn . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
            $borrar = '<a value="' . $id_lq . '|' . $id_tn . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb borrar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
            $data[] = [
                'descripcion' => $nl['descripcion'],
                'valor' => $noaplica,
                'fecha' => $nl['fec_liq'],
                'tipo' => $nl['id_t_liq'],
                'cdp' => $noaplica,
                'fec_inicia' => $noaplica,
                'fec_fin' => $noaplica,
                'val_cte' => '<div class="text-right">' . pesos($nl['val_cte']) . '</div>',
                'val_cta' => '<div class="text-right">' . pesos($nl['val_cta']) . '</div>',
                'tercero' => $noaplica,
                'observacion' => $nl['observacion'],
                'botones' => '<div class="text-center">' . $editar . $borrar . '</div>',

            ];
        }
    }
} else {
    $data = [];
}
$datos = ['data' => $data];

echo json_encode($datos);
