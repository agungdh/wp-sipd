<?php
global $wpdb;

$input = shortcode_atts(array(
    'tahun_anggaran' => ''
), $atts);
if (!empty($_GET) && !empty($_GET['tahun_anggaran'])) {
    $input['tahun_anggaran'] = $wpdb->prepare('%d', $_GET['tahun_anggaran']);
}

$idtahun = $wpdb->get_results("select distinct tahun_anggaran from data_unit", ARRAY_A);
$tahun = "<option value='-1'>Pilih Tahun</option>";
foreach ($idtahun as $val) {
    $selected = '';
    if (!empty($input['tahun_anggaran']) && $val['tahun_anggaran'] == $input['tahun_anggaran']) {
        $selected = 'selected';
    }
    $tahun .= "<option value='$val[tahun_anggaran]' $selected>$val[tahun_anggaran]</option>";
}
?>

<style type="text/css">
    .wrap-table {
        overflow: auto;
        max-height: 100vh;
        width: 100%;
    }
</style>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<div class="cetak">
    <div style="padding: 10px;margin:0 0 3rem 0;">
        <input type="hidden" value="<?php echo get_option('_crb_api_key_extension'); ?>" id="api_key">
        <h1 class="text-center" style="margin:3rem;">Manajemen Data BKU DD</h1>
        <div style="margin-bottom: 25px;">
            <button class="btn btn-primary" onclick="tambah_data_bku_dd();"><i class="dashicons dashicons-plus"></i> Tambah Data</button>
        </div>
        <div class="wrap-table">
            <table id="management_data_table" cellpadding="2" cellspacing="0" style="font-family:\'Open Sans\',-apple-system,BlinkMacSystemFont,\'Segoe UI\',sans-serif; border-collapse: collapse; width:100%; overflow-wrap: break-word;" class="table table-bordered">
                <thead>
                    <tr>
                        <th class="text-center">Kecamatan</th>
                        <th class="text-center">Desa</th>
                        <th class="text-center">Total</th>
                        <th class="text-center">Tahun Anggaran</th>
                        <th class="text-center" style="width: 150px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade mt-4" id="modalTambahDataBKUDD" tabindex="-1" role="dialog" aria-labelledby="modalTambahDataBKUDDLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTambahDataBKUDDLabel">Data BKUDD</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type='hidden' id='id_data' name="id_data" placeholder=''>
                <div class="form-group">
                    <label>Tahun Anggaran</label>
                    <select class="form-control" id="tahun" onchange="get_kecamatan();">
                        <?php echo $tahun ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Kecamatan</label>
                    <select class="form-control" id="kec" onchange="get_desa();">
                    </select>
                </div>
                <div class="form-group">
                    <label>Desa</label>
                    <select class="form-control" id="desa">
                    </select>
                </div>
                <div class="form-group">
                    <label for='total' style='display:inline-block'>Total</label>
                    <input type="text" id='total' name="total" class="form-control" placeholder='' />
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary submitBtn" onclick="submitTambahDataFormBKUDD()">Simpan</button>
                <button type="submit" class="components-button btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<script>
    jQuery(document).ready(function() {
        get_data_bku_dd();
        window.alamat_global = {};
    });

    function get_data_bku_dd() {
        if (typeof databku_dd == 'undefined') {
            window.databku_dd = jQuery('#management_data_table').on('preXhr.dt', function(e, settings, data) {
                jQuery("#wrap-loading").show();
            }).DataTable({
                "processing": true,
                "serverSide": true,
                "ajax": {
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'post',
                    dataType: 'json',
                    data: {
                        'action': 'get_datatable_bku_dd',
                        'api_key': '<?php echo get_option('_crb_api_key_extension'); ?>',
                    }
                },
                lengthMenu: [
                    [20, 50, 100, -1],
                    [20, 50, 100, "All"]
                ],
                order: [
                    [0, 'asc']
                ],
                "drawCallback": function(settings) {
                    jQuery("#wrap-loading").hide();
                },
                "columns": [
                    {
                        "data": 'kecamatan',
                        className: "text-center"
                    },
                    {
                        "data": 'desa',
                        className: "text-center"
                    },
                    {
                        "data": 'total',
                        className: "text-right"
                    },
                    {
                        "data": 'tahun_anggaran',
                        className: "text-center"
                    },
                    {
                        "data": 'aksi',
                        className: "text-center"
                    }
                ]
            });
        } else {
            databku_dd.draw();
        }
    }

    function hapus_data(id) {
        let confirmDelete = confirm("Apakah anda yakin akan menghapus data ini?");
        if (confirmDelete) {
            jQuery('#wrap-loading').show();
            jQuery.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'post',
                data: {
                    'action': 'hapus_data_bku_dd_by_id',
                    'api_key': '<?php echo get_option('_crb_api_key_extension'); ?>',
                    'id': id
                },
                dataType: 'json',
                success: function(response) {
                    jQuery('#wrap-loading').hide();
                    if (response.status == 'success') {
                        get_data_bku_dd();
                    } else {
                        alert(`GAGAL! \n${response.message}`);
                    }
                }
            });
        }
    }

    function edit_data(_id) {
        jQuery('#wrap-loading').show();
        jQuery.ajax({
            method: 'post',
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            dataType: 'json',
            data: {
                'action': 'get_data_bku_dd_by_id',
                'api_key': '<?php echo get_option('_crb_api_key_extension'); ?>',
                'id': _id,
            },
            success: function(res) {
                if (res.status == 'success') {
                    jQuery('#id_data').val(res.data.id);
                    jQuery('#tahun').val(res.data.tahun_anggaran).prop('disabled', false);
                    get_kecamatan()
                    .then(function() {
                        jQuery('#kec').val(res.data.kecamatan).trigger('change').prop('disabled', false);
                        jQuery('#desa').val(res.data.desa).trigger('change').prop('disabled', false);
                            jQuery('#total').val(res.data.total).prop('disabled', false);
                            jQuery('#modalTambahDataBKUDD .send_data').show();
                            jQuery('#modalTambahDataBKUDD').modal('show');
                    });
                } else {
                    alert(res.message);
                }
                jQuery('#wrap-loading').hide();
            }
        });
    }

    //show tambah data
    function tambah_data_bku_dd() {
        jQuery('#id_data').val('').prop('disabled', false);
        jQuery('#id_kecamatan').val('').prop('disabled', false);
        jQuery('#id_desa').val('').prop('disabled', false);
        jQuery('#tahun').val('<?php echo $input['tahun_anggaran']; ?>').prop('disabled', false);
        new Promise(function(resolve, reject) {
                if ('<?php echo $input['tahun_anggaran']; ?>' != '') {
                    get_kecamatan().then(function() {
                        resolve();
                    });
                } else {
                    resolve();
                }
            })
        .then(function() {
            jQuery('#kec').val('').prop('disabled', false);
            jQuery('#desa').val('').prop('disabled', false);
            jQuery('#total').val('');
            jQuery('#modalTambahDataBKUDD').modal('show');
        });
    }

    function submitTambahDataFormBKUDD() {
        var id_data = jQuery('#id_data').val();

        var id_kel = jQuery('#desa').val();
        if (id_kel == '') {
            return alert('Data desa tidak boleh kosong!');
        }
        var desa = jQuery("#desa option:selected").text();

        var id_kec = jQuery('#kec').val();
        if (id_kec == '') {
            return alert('Data kecamatan tidak boleh kosong!');
        }
        var kecamatan = jQuery("#kec option:selected").text();

        var total = jQuery('#total').val();
        if (total == '') {
            return alert('Data total tidak boleh kosong!');
        }

        var tahun = jQuery('#tahun').val();
        if (tahun == '') {
            return alert('Data tahun anggaran tidak tidak boleh kosong!');
        }

        jQuery('#wrap-loading').show();
        jQuery.ajax({
            method: 'post',
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            dataType: 'json',
            data: {
                'action': 'tambah_data_bku_dd',
                'api_key': '<?php echo get_option('_crb_api_key_extension'); ?>',
                'id_data': id_data,
                'id_kec': id_kec,
                'id_desa': id_kel,
                'kecamatan': kecamatan,
                'desa': desa,
                'total': total,
                'tahun_anggaran': tahun
            },
            success: function(res) {
                alert(res.message);
                if (res.status == 'success') {
                    jQuery('#modalTambahDataBKUDD').modal('hide');
                    get_data_bku_dd();
                } else {
                    jQuery('#wrap-loading').hide();
                }
            }
        });
    }
       
    function get_kecamatan() {
        return new Promise(function(resolve, reject) {
            var tahun = jQuery('#tahun').val();
            if (tahun == '' || tahun == '-1') {
                alert('Pilih tahun anggaran dulu!');
                return resolve();
            }
            if (typeof alamat_global == 'undefined') {
                window.alamat_global = {};
            }

            if (!alamat_global[tahun]) {
                jQuery('#wrap-loading').show();
                jQuery.ajax({
                    url: "<?php echo admin_url('admin-ajax.php'); ?>",
                    type: "post",
                    data: {
                        'action': "get_pemdes_bkk",
                        'api_key': jQuery("#api_key").val(),
                        'tahun_anggaran': tahun
                    },
                    dataType: "json",
                    success: function(response) {
                        alamat_global[tahun] = response.data;
                        window.kecamatan_all = {};
                        alamat_global[tahun].map(function(b, i) {
                            if (!kecamatan_all[b.kecamatan]) {
                                kecamatan_all[b.kecamatan] = {};
                            }
                            if (!kecamatan_all[b.kecamatan][b.desa]) {
                                kecamatan_all[b.kecamatan][b.desa] = [];
                            }
                            kecamatan_all[b.kecamatan][b.desa].push(b);
                        });
                        var kecamatan = '<option value="-1">Pilih Kecamatan</option>';
                        for (var i in kecamatan_all) {
                            kecamatan += '<option value="' + i + '">' + i + '</option>';
                        }
                        jQuery('#kec').html(kecamatan);
                        jQuery('#wrap-loading').hide();
                        return resolve();
                    }
                });
            } else {
                return resolve();
            }
        })
    }

    function get_desa() {
        var kec = jQuery('#kec').val();
        if (kec == '' || kec == '-1') {
            return alert('Pilih kecamatan dulu!');
        }
        var desa = '<option value="-1">Pilih Desa</option>';
        for (var ii in kecamatan_all[kec]) {
            desa += '<option value="' + ii + '">' + ii + '</option>';
        }
        jQuery('#desa').html(desa);
    }
</script>