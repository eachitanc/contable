<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$id_tipo = isset($_POST['id_tipo']) ? $_POST['id_tipo'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_salida`,`descripcion` FROM `seg_tipo_salidas` WHERE `id_salida` = '$id_tipo'";
    $rs = $cmd->query($sql);
    $tsalida = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_entrada`, `consecutivo`, `id_tercero_api`, `acta_remision` 
            FROM `seg_entrada_almacen`
            WHERE `estado` > 0 AND `id_tipo_entrada` = 8";
    $rs = $cmd->query($sql);
    $fianzas = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$id_t = [];
$id_t[] = 0;
foreach ($fianzas as $fz) {
    if ($fz['id_tercero_api'] != '') {
        $id_t[] = $fz['id_tercero_api'];
    }
}
$payload = json_encode($id_t);
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
$terceros = $terceros != '0' ? $terceros : [];
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">REGISTRAR <?php echo $tsalida['descripcion'] ?></h5>
        </div>
        <div class="px-2">
            <form id="formAddSalidaXDevol">
                <input id="id_tipo_sal" type="hidden" name="id_tipo_sal" value="<?php echo $id_tipo ?>">
                <div class="form-row text-center">
                    <div class="form-group col-md-12">
                        <?php
                        if ($id_tipo != '8') { ?>
                            <label for="compleTerecero" class="small">TERCERO</label>
                            <input id="compleTerecero" type="text" class="form-control form-control-sm" placeholder="Buscar">
                            <input type="hidden" id="id_tercero_pd" name="id_tercero_pd" value="0">
                        <?php
                        } else {
                        ?>
                            <label for="id_tercero_pd" class="small">FIANZA</label>
                            <select id="id_tercero_pd" name="id_tercero_pd" class="form-control form-control-sm">
                                <option value="0">--Seleccione--</option>
                                <?php
                                foreach ($fianzas as $f) {
                                    $key = array_search($f['id_tercero_api'], array_column($terceros, 'id_tercero'));
                                    $terc = $key !== false ? $terceros[$key]['nombre1'] . ' ' . $terceros[$key]['nombre2'] . ' ' . $terceros[$key]['apellido1'] . ' ' . $terceros[$key]['apellido2'] . ' ' . $terceros[$key]['razon_social'] : '';
                                    $terc = trim($terc);
                                    $terc = $terc == '' ? $terc : ' -> ' . $terc;
                                ?>
                                    <option value="<?php echo $f['id_entrada'] . '|' . $f['id_tercero_api'] ?>"><?php echo 'FIANZA ENTRADA ' . str_pad($f['consecutivo'], 5, "0", STR_PAD_LEFT) . $terc ?></option>
                                <?php
                                }
                                ?>
                            </select>
                        <?php
                        }
                        ?>
                    </div>
                </div>
                <div class="form-row text-center">
                    <div class="form-group col-md-6">
                        <label for="numActaRemDev" class="small"># acta y/o remisión</label>
                        <input type="text" id="numActaRemDev" name="numActaRemDev" class="form-control form-control-sm">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="fecActRem" class="small">Fecha de acta y/o remisión</label>
                        <input type="date" id="fecActRem" name="fecActRem" class="form-control form-control-sm">
                    </div>
                </div>
                <div class="form-row text-center">
                    <div class="form-group col-md-12">
                        <label for="txtaObservacionDev" class="small">Observaciones</label>
                        <textarea class="form-control" id="txtaObservacionDev" name="txtaObservacionDev" rows="3"></textarea>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="text-center pt-3">
    <button id="btnAddSalidaXDevol" type="button" class="btn btn-primary btn-sm">Registrar</button>
    <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
</div>