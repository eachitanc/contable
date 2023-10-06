<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../index.php");</script>';
    exit();
}
include '../conexion.php';
include '../permisos.php';
include '../financiero/consultas.php';
?>
<!DOCTYPE html>
<html lang="es">

<?php include '../head.php';
// Tabla que genera el reporte datos_detalle_cdp.php
// Consulta tipo de presupuesto en la base de datos
$automatico = '';
$id_ppto = $_POST['id_ejec'];
$valoradq = '';
$tipo_dato = '';
$cod_ppto = '';
$rubro = '';

if (isset($_POST['id_adq'])) $id_adq = $_POST['id_adq'];
else  $id_adq = ''; // llega el valor de la id cuando hay una adqusición relacionada
// verifico si llega solicitude de otro si
if (isset($_POST['id_otro'])) $id_otro = $_POST['id_otro'];
else  $id_otro = '';
if (isset($_POST['id_cdp'])) {
    $id_pto_documento = $_POST['id_cdp'];
    $id_pto_doc = $id_pto_documento;
} else {
    $id_pto_documento = 0;
    $id_pto_doc = null;
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_pto_presupuestos,fecha, id_manu,objeto,num_solicitud FROM seg_pto_documento WHERE id_pto_doc='$id_pto_documento'";
    $rs = $cmd->query($sql);
    $datosCdp = $rs->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $sql = "SELECT consecutivo FROM seg_fin_maestro_doc WHERE tipo_doc ='CDP' AND estado=0";
    $res = $cmd->query($sql);
    $ducumento = $res->fetch();
    if ($ducumento['consecutivo'] == 1) {
        $automatico = 'readonly';
    }
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT sum(valor) as valorCdp FROM seg_pto_mvto WHERE id_pto_doc=$id_pto_documento";
    $rs = $cmd->query($sql);
    $totalCdp = $rs->fetch();
    // total con puntos de mailes number_format()
    $total = number_format($totalCdp['valorCdp'], 2, '.', ',');
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// Buscar si el usuario tiene registrado fecha de sesion
try {
    $sql = "SELECT fecha FROM seg_fin_fecha WHERE id_usuario = '$_SESSION[id_user]' AND vigencia = '$_SESSION[vigencia]'";
    $res = $cmd->query($sql);
    $fechases = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

if ($id_pto_documento == 0) {
    $fecha = date('Y-m-d');
} else {
    $fecha = date('Y-m-d', strtotime($datosCdp['fecha']));
}
// si el proceso llega de otro si consulto el id de la adquisición
if ($id_otro != 0) {
    $sql = "SELECT
                    seg_adquisiciones.id_adquisicion AS id_adq
                    , seg_novedad_contrato_adi_pror.val_adicion AS val_adicion
                FROM
                    seg_contrato_compra
                    INNER JOIN seg_adquisiciones 
                        ON (seg_contrato_compra.id_compra = seg_adquisiciones.id_adquisicion)
                    INNER JOIN seg_novedad_contrato_adi_pror 
                        ON (seg_novedad_contrato_adi_pror.id_adq = seg_contrato_compra.id_contrato_compra)
                WHERE (seg_novedad_contrato_adi_pror.id_nov_con = '$id_otro');";
    $res = $cmd->query($sql);
    $datosOtro = $res->fetch();
    $id_adq = $datosOtro['id_adq'];
    $valorotro = $datosOtro['val_adicion'];
}
// Si el proceso viene de adquisiciones llama el objeto y valida fecha
$objeto = $datosCdp['objeto'];
if ($id_adq != 0) {
    // consulto datos de seg_adquisiciones donde id_adq sea igual a id_adquisiciones
    $sql = "SELECT objeto,fecha_adquisicion,val_contrato FROM seg_adquisiciones WHERE id_adquisicion = '$id_adq'";
    $res = $cmd->query($sql);
    $datosAdq = $res->fetch();
    $objeto = $datosAdq['objeto'];
    $valoradq = $datosAdq['val_contrato'];
    $sql = "SELECT
    `seg_adquisiciones`.`id_adquisicion`
    , `seg_escala_honorarios`.`id_pto_cargue`
     , `seg_pto_cargue`.`nom_rubro`
     , `seg_pto_cargue`.`tipo_dato`
FROM
    `seg_escala_honorarios`
    INNER JOIN `seg_tipo_bien_servicio` 
        ON (`seg_escala_honorarios`.`id_tipo_b_s` = `seg_tipo_bien_servicio`.`id_tipo_b_s`)
    INNER JOIN `seg_pto_cargue` 
        ON (`seg_escala_honorarios`.`id_pto_cargue` = `seg_pto_cargue`.`cod_pptal`)
    INNER JOIN `seg_adquisiciones` 
        ON (`seg_adquisiciones`.`id_tipo_bn_sv` = `seg_tipo_bien_servicio`.`id_tipo_b_s`)
WHERE (`seg_adquisiciones`.`id_adquisicion` =$id_adq
    AND `seg_pto_cargue`.`vigencia` =$_SESSION[vigencia]);";
    $res = $cmd->query($sql);
    $datosPpto = $res->fetch();
    $cod_ppto = $datosPpto['id_pto_cargue'];
    $nom_rubro = $datosPpto['nom_rubro'];
    $tipo_dato = $datosPpto['tipo_dato'];
    $rubro = $cod_ppto . ' - ' . $nom_rubro;
}
if ($id_otro != 0) {
    $objeto = "OTRO SI " . $objeto;
    $valoradq = $valorotro;
}
// verificar si la variable tiene dato y llevar a valor de fecha
if ($fechases['fecha'] != '') {
    $fecha = date('Y-m-d', strtotime($fechases['fecha']));
}
// Consulta funcion fechaCierre del modulo 4
$fecha_cierre = fechaCierre($_SESSION['vigencia'], 4, $cmd);
$cmd = null;
$fecha_max = date("Y-m-d", strtotime($_SESSION['vigencia'] . '-12-31'));
?>

<body class="sb-nav-fixed <?php if ($_SESSION['navarlat'] === '1') {
                                echo 'sb-sidenav-toggled';
                            } ?>">

    <?php include '../navsuperior.php' ?>
    <div id="layoutSidenav">
        <?php include '../navlateral.php' ?>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid p-2">
                    <div class="card mb-4">
                        <div class="card-header" id="divTituloPag">
                            <div class="row">
                                <div class="col-md-11">
                                    <i class="fas fa-users fa-lg" style="color:#1D80F7"></i>
                                    DETALLE CERTIFICADO DE DISPONIBILIDAD PRESUPUESTAL <?php echo $id_adq; ?>
                                </div>


                            </div>
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <div id="divFormDoc">
                                <form id="formAddEjecutaPresupuesto">
                                    <input type="hidden" id="id_pto_mvto" name="id_pto_mvto" value="<?php echo $id_pto_doc; ?>">
                                    <input type="hidden" id="id_pto_presupuestos" name="id_pto_presupuestos" value="<?php echo $id_ppto; ?>">
                                    <input type="hidden" id="id_adq" name="id_adq" value="<?php echo $id_adq; ?>">
                                    <input type="hidden" id="id_otro" name="id_otro" value="<?php echo $id_otro; ?>">

                                    <input type="hidden" id="id_pto_docini" value="<?php echo $datosCdp['id_manu']; ?>">
                                    <div class="right-block">
                                        <div class="row">
                                            <div class="col-2">
                                                <div class="col"><label for="fecha" class="small">NUMERO CDP:</label></div>
                                            </div>
                                            <div class="col-2"><input type="number" name="numCdp" id="numCdp" class="form-control form-control-sm" value="<?php echo $datosCdp['id_manu']; ?>" onchange="buscarCdp(value,'CDP')" <?php echo $automatico; ?>></div>
                                            <div class="col-2" style="margin: 0px; padding: 0px;">
                                                <div class=""><a value="' . $id_pto . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb" title="Lista de CDP " id="botonListaCdp"><span class="far fa-list-alt fa-lg"></span></a></div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-2">
                                                <div class="col"><label for="fecha" class="small">FECHA:</label></div>
                                            </div>
                                            <div class="col-2"> <input type="date" name="fecha" id="fecha" class="form-control form-control-sm" min="<?php echo $fecha_cierre; ?>" max="<?php echo $fecha_max; ?>" value="<?php echo $fecha; ?>" onchange="buscarConsecutivo('CDP');"></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-2">
                                                <div class="col"><label for="fecha" class="small">OBJETO:</label></div>
                                            </div>
                                            <div class="col-10"> <textarea id="objeto" type="text" name="objeto" class="form-control form-control-sm py-0 sm" aria-label="Default select example" rows="3" required="required"><?php echo $objeto; ?></textarea></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-2">
                                                <div class="col"><label for="sol" class="small">No SOLICITUD:</label></div>
                                            </div>
                                            <div class="col-2"> <input type="text" name="solicitud" id="solicitud" class="form-control form-control-sm" value="<?php echo $datosCdp['num_solicitud']; ?>"></div>
                                        </div>

                                    </div>
                                </form>
                            </div>
                            <br>
                            <table id="tableEjecCdp" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                <thead>
                                    <tr>
                                        <th style="width: 60%">Codigo</th>
                                        <th style="width: 20%" class="text-center">Valor</th>
                                        <th style="width: 20%" class="text-center">Acciones</th>

                                    </tr>
                                </thead>
                                <tbody id="modificarEjecCdp">

                                </tbody>

                                <input type="hidden" id="peReg" value="<?php echo $permisos['registrar']; ?>">
                                <input type="hidden" id="tipoRubro" value="<?php echo $tipo_dato; ?>">

                                <!-- Formulario para nuevo reistro -->
                                <form id="formAddValorCdp">
                                    <tr>
                                        <th>
                                            <input type="text" name="rubroCdp" id="rubroCdp" class="form-control form-control-sm" value="<?php echo $rubro; ?>">
                                            <input type="hidden" name="id_rubroCdp" id="id_rubroCdp" class="form-control form-control-sm" value="<?php echo $cod_ppto; ?>">
                                            <input type="hidden" name="id_pto_cdp" id="id_pto_cdp" value="<?php echo $id_pto_documento; ?>">
                                        </th>
                                        <th>
                                            <input type="text" name="valorCdp" id="valorCdp" class="form-control form-control-sm" size="6" value="<?php echo $valoradq; ?>" style="text-align: right;">
                                            <input type="hidden" id="editarRubro" name="editarRubro" value="">
                                        </th>

                                        <th class="text-center">
                                            <a class="btn btn-outline-warning btn-sm btn-circle shadow-gb" title="Ver historial del rubro" onclick="verHistorialCdp(<?php echo $_SESSION['vigencia']; ?>)"><span class="far fa-list-alt fa-lg"></span></a>
                                            <a id="registrar" class="btn btn-primary btn-sm">Registrar</a>
                                        </th>
                                    </tr>
                                </form>
                                <!-- Fin formulario -->
                                <tfoot>
                                    <tr>
                                        <th>Total</th>
                                        <th>
                                            <div class="text-right" id="totalCdp"><?php echo $total; ?></div>
                                        </th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                            <div class="text-center pt-4">
                                <a type="button" class="btn btn-primary btn-sm" onclick="imprimirFormatoCdp(<?php echo $id_pto_documento; ?>);" style="width: 5rem;"> <span class="fas fa-print "></span></a>
                                <a type="button" id="volverListaCdps" class="btn btn-danger btn-sm" style="width: 5rem;" href="#"> VOLVER</a>

                            </div>
                        </div>

                    </div>
                </div>
            </main>
            <?php include '../footer.php' ?>
        </div>
        <!-- Modal formulario-->
        <div class="modal fade" id="divModalForms" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-backdrop="static" data-keyboard="false">
            <div id="divTamModalForms" class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-body text-center" id="divForms">

                    </div>
                </div>
            </div>
        </div>
        <!-- Modal -->
    </div>


    <?php include '../scripts.php' ?>

    <!-- Script -->
    <script type='text/javascript'>
        var modified = false;
        var modidet = false;

        function validarCampos() {
            if (modified)
                return confirm('Puede haber cambios sin guardar en el formulario, ¿Desea salir de todas formas?');
        }
        // Uso de fetch para realizar crud
        registrar.addEventListener("click", () => {
            let formEnvioDoc = new FormData(formAddEjecutaPresupuesto);
            let error = 0;
            for (var pair of formEnvioDoc.entries()) {
                console.log(pair[0] + ', ' + pair[1]);
                if (formEnvioDoc.get('numCdp') == '') {
                    document.querySelector("#numCdp").focus();
                    mjeError('Debe digitar un número de CDP', '');
                    error = 1;
                    return false;
                }
                if (formEnvioDoc.get('objeto') == '') {
                    document.querySelector("#objeto").focus();
                    mjeError('Debe digitar un objeto de CDP', '');
                    error = 1;
                    return false;
                }
                // Obtener atributos min y max del campo fecha
                let fecha_min = document.querySelector("#fecha").getAttribute("min");
                let fecha_max = document.querySelector("#fecha").getAttribute("max");
                // Validar que la fecha no sea mayor a la fecha maxima y menor a la fecha mínima
                if (formEnvioDoc.get('fecha') > fecha_max || formEnvioDoc.get('fecha') < fecha_min) {
                    document.querySelector("#fecha").focus();
                    mjeError('La fecha debe estar entre ' + fecha_min + ' y ' + fecha_max, '');
                    error = 1;
                    return false;
                }
                // validar que el valor no sea mayor al saldo del rubro
                let rubro = id_rubroCdp.value;
                let valor = parseFloat(valorCdp.value.replace(/\,/g, "", ""));
                fetch("datos/consultar/consultaSaldoCdp.php", {
                        method: "POST",
                        body: JSON.stringify({
                            rubro: rubro
                        }),
                    })
                    .then((response) => response.json())
                    .then((response) => {
                        let saldo = response[0].total;
                        if (saldo < valor) {
                            //document.querySelector("#valorCdp").focus();
                            mjeError('El valor no puede ser mayor al saldo del rubro', '');
                            error = 1;
                            breack;
                            return false;
                        } else {
                            if (error == 0) {
                                let id_doc = $('#id_pto_mvto').val();
                                console.log(modified);
                                try {
                                    if (modified == true) {
                                        var datos = registrarCdp();
                                        modified = false;
                                    }
                                    if (modidet == true) {
                                        // Verificar que el campo id ya este cargado
                                        let id_doc = $('#id_pto_mvto').val();
                                        if (id_doc != '') {
                                            let det = registrarCdpDetalle(id_doc);
                                            modidet = false;
                                        }
                                    }
                                } catch (e) {
                                    console.log(e);
                                }
                            }
                        }
                    })
                    .catch((error) => {
                        console.log("Error:");
                    });
            }

        });
        // Función para registrar datos generales del CDP
        async function registrarCdp() {
            const respon = await fetch("datos/registrar/registrar_cdp_doc.php", {
                method: "POST",
                body: new FormData(formAddEjecutaPresupuesto)
            }).then(response => response.json()).then(response => {
                console.log(response);
                if (response[0].value == "ok") {
                    $('#id_pto_mvto').val(response[0].id);
                    let id_doc = $('#id_pto_mvto').val();
                    let det = registrarCdpDetalle(id_doc, 1);
                    modidet = false;
                    return "cargado okey 2";
                } else {

                }
            })
        }
        // Función para registrar datos de detalle del CDP
        function registrarCdpDetalle(id_doc, nuevo) {
            let error_det = 0;
            let formEnvioDet = new FormData(formAddValorCdp);
            let ppto = $("#id_pto_presupuestos").val();
            let ruta = {
                url: "lista_ejecucion_cdp.php",
                name1: "id_cdp",
                valor1: id_doc,
                name2: "id_ejec",
                valor2: ppto,
            }
            for (var pair of formEnvioDet.entries()) {
                if (formEnvioDet.get('id_rubroCdp') == '') {
                    document.querySelector("#rubroCdp").focus();
                    mjeError('Debe digitar un rubro presupuestal', '');
                    error_det = 1;
                    return false;
                }
                if (formEnvioDet.get('rubroCdp') == '') {
                    document.querySelector("#rubroCdp").focus();
                    mjeError('Debe digitar un rubro presupuestal', '');
                    error_det = 1;
                    return false;
                }
                if (document.querySelector("#tipoRubro").value == 0) {
                    document.querySelector("#rubroCdp").focus();
                    mjeError('Debe digitar un rubro presupuestal de detalle', '');
                    return false;
                    error_det = 1;
                }
                if (formEnvioDet.get('valorCdp') == '') {
                    document.querySelector("#valorCdp").focus();
                    mjeError('Debe digitar un valor', '');
                    return false;
                    error_det = 1;
                }
            }
            if (error_det == 0) {
                formEnvioDet.append('id_pto_cdp', id_doc);
                formEnvioDet.append('id_pto_mov', '');
                fetch("datos/registrar/registrar_cdp_det.php", {
                    method: "POST",
                    body: formEnvioDet
                }).then(response => response.json()).then(response => {
                    if (response[0].value == "ok") {
                        formAddValorCdp.reset();
                        redireccionar2(ruta);
                    } else {
                        Swal.fire({
                            icon: 'success',
                            title: 'Modificado',
                            showConfirmButton: false,
                            timer: 1500
                        })
                        formAddValorCdp.reset();
                        console.log(ruta);
                        redireccionar2(ruta);
                    }
                })
            }
        }
        // Eliminar rubro asociado al CDP
        function Eliminar(id) {
            let id_doc = $('#id_pto_mvto').val();
            let ppto = $("#id_pto_presupuestos").val();
            let ruta = {
                url: "lista_ejecucion_cdp.php",
                name: "id_cdp",
                valor: id_doc,
                name2: "id_ejec",
                valor2: ppto,
            }
            Swal.fire({
                title: 'Esta seguro de eliminar?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#00994C',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Si!',
                cancelButtonText: 'NO'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch("datos/cdp/eliminar.php", {
                        method: "POST",
                        body: id
                    }).then(response => response.text()).then(response => {
                        if (response == "ok") {
                            Swal.fire({
                                icon: 'success',
                                title: 'Eliminado',
                                showConfirmButton: true,
                                timer: 1500
                            })
                            formAddValorCdp.reset();
                            redireccionar2(ruta);
                        }

                    })

                }
            })
        }

        // Editar rubro asociado al CDP
        function Editar(id) {
            fetch("datos/cdp/editar.php", {
                method: "POST",
                body: id
            }).then(response => response.json()).then(response => {
                rubroCdp.value = response.rubro + " - " + response.nom_rubro;
                id_rubroCdp.value = response.rubro;
                valorCdp.value = response.valor;
                tipoRubro.value = response.tipo_dato;
                editarRubro.value = response.id_pto_mvto;
                modidet = true;
                // registrar.value = "Actualizar"
            })
        }
        // Autocomplete rubro cdp
        $(function() {
            let valor = $('#id_pto_presupuestos').val();

            $("#rubroCdp").autocomplete({

                source: function(request, response) {
                    $.ajax({
                        url: "datos/consultar/consultaRubros.php",
                        type: 'post',
                        dataType: "json",
                        data: {
                            search: request.term,
                            valor: valor
                        },
                        success: function(data) {
                            response(data);
                        }
                    });
                },
                select: function(event, ui) {
                    $('#rubroCdp').val(ui.item.label); // display the selected text
                    $('#id_rubroCdp').val(ui.item.value);
                    $('#tipoRubro').val(ui.item.tipo);
                    // save selected id to input
                    return false;
                },
                focus: function(event, ui) {
                    $("#rubroCdp").val(ui.item.label);
                    $("#id_rubroCdp").val(ui.item.value);
                    $("#tipoRubro").val(ui.item.tipo);
                    return false;
                },
            });
        });

        function split(val) {
            return val.split(/,\s*/);
        }

        function extractLast(term) {
            return split(term).pop();
        }

        window.onload = function() {
            buscarConsecutivo('CDP');
        }

        // Función para determinar si hay modificaciones en campos de un formulario
        $("#divFormDoc").ready(function() {
            $("#numCdp").change(function() {
                modified = true;
            });
            $("#fecha").change(function() {
                modified = true;
            });
            $("#objeto").change(function() {
                modified = true;
            });
            $("#id_rubroCdp").change(function() {
                modidet = true;
            });
            $("#valorCdp").change(function() {
                modidet = true;
            });
            $("#solicitud").change(function() {
                modified = true;
            });

            let id_adq = $('#id_adq').val();
            if (id_adq != '') {
                modified = true;
            }
        });
    </script>
</body>

</html>