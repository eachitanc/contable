<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$id_dev = isset($_POST['id_dev']) ? $_POST['id_dev'] : exit('Accion no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT 
                `id_devolucion`, `id_tercero_api`, `id_tipo_salida`, `acta_remision`, `fec_acta_remision`, `observacion`, `estado`
            FROM
                `seg_salida_dpdvo`
            WHERE `id_devolucion` = '$id_dev'";
    $rs = $cmd->query($sql);
    $devoluciones = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

$id_ter = $devoluciones['id_tercero_api'];
//API URL
$url = $api . 'terceros/datos/res/datos/id/' . $id_ter;
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res_api = curl_exec($ch);
curl_close($ch);
$dat_ter = json_decode($res_api, true);
$tercero = $dat_ter[0]['apellido1'] . ' ' . $dat_ter[0]['apellido2'] . ' ' . $dat_ter[0]['nombre2'] . ' ' . $dat_ter[0]['nombre1'] . ' ' . $dat_ter[0]['razon_social'];
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">ACTUALIZAR O MODIFICAR DEVOLUCIÓN</h5>
        </div>
        <div class="px-2">
            <form id="formActSalidaXDevol">
                <input type="hidden" name="id_devolucion" value="<?php echo $devoluciones['id_devolucion'] ?>">
                <div class="form-row text-center">
                    <div class="form-group col-md-12">
                        <label for="compleTerecero" class="small">TERCERO</label>
                        <input id="compleTerecero" type="text" class="form-control form-control-sm" placeholder="Buscar" value="<?php echo $tercero ?>">
                        <input type="hidden" id="id_tercero_pd" name="id_tercero_pd" value="<?php echo $devoluciones['id_tercero_api'] ?>">
                    </div>
                </div>
                <div class="form-row text-center">
                    <div class="form-group col-md-6">
                        <label for="numActaRemDev" class="small"># acta y/o remisión</label>
                        <input type="text" id="numActaRemDev" name="numActaRemDev" class="form-control form-control-sm" value="<?php echo $devoluciones['acta_remision'] ?>">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="fecActRem" class="small">Fecha de acta y/o remisión</label>
                        <input type="date" id="fecActRem" name="fecActRem" class="form-control form-control-sm" value="<?php echo $devoluciones['fec_acta_remision'] ?>">
                    </div>
                </div>
                <div class="form-row text-center">
                    <div class="form-group col-md-12">
                        <label for="txtaObservacionDev" class="small">Observaciones</label>
                        <textarea class="form-control" id="txtaObservacionDev" name="txtaObservacionDev" rows="3"><?php echo $devoluciones['observacion'] ?></textarea>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="text-center pt-3">
    <button id="btnActSalidaXDevol" type="button" class="btn btn-primary btn-sm">Actualizar</button>
    <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
</div>