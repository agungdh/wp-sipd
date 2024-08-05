<?php
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
global $wpdb;

$id_unit = $_GET['id_unit'] ?? '';
$id_jadwal_lokal = $_GET['id_jadwal_lokal'] ?? '';
$type = $_GET['type'] ?? '';
$dari_simda = $_GET['dari_simda'] ?? '';
$nama_pemda = get_option('_crb_daerah');

if (empty($id_jadwal_lokal)) {
    die('<h1 class="text_tengah">ID Jadwal Lokal Tidak Boleh Kosong!</h1>');
}

if (empty($_GET['id_unit'])) {
    die('<h1 class="text-center">ID SKPD tidak boleh kosong!</h1>');
}

$input = shortcode_atts(array(
    'id_skpd' => $id_unit,
    'id_jadwal_lokal' => $id_jadwal_lokal,
    'tahun_anggaran' => ''
), $atts);

$jadwal_lokal = $wpdb->get_row(
    $wpdb->prepare("
        SELECT 
            j.nama AS nama_jadwal,
            j.tahun_anggaran,
            j.status,
            j.status_jadwal_pergeseran,
            t.nama_tipe 
        FROM `data_jadwal_lokal` j
        INNER JOIN `data_tipe_perencanaan` t ON t.id=j.id_tipe 
        WHERE j.id_jadwal_lokal=%d
    ", $id_jadwal_lokal)
);

$_suffix = '';
$where_jadwal = '';
if ($jadwal_lokal->status == 1) {
    $_suffix = '_history';
    $where_jadwal = ' AND id_jadwal=' . $wpdb->prepare("%d", $id_jadwal_lokal);
}
$input['tahun_anggaran'] = $jadwal_lokal->tahun_anggaran;

$_suffix_sipd = '';
if (strpos($jadwal_lokal->nama_tipe, '_sipd') == false) {
    $_suffix_sipd = '_lokal';
}

if (
    !empty($input['id_skpd'])
    && $input['id_skpd'] != 'all'
) {
    $data_skpd = $wpdb->get_results($wpdb->prepare("
        SELECT 
            s.*,
            u.kode_skpd AS kode_unit,
            u.nama_skpd AS nama_unit
        FROM data_unit s
        JOIN data_unit u on u.id_skpd = s.id_unit
            AND u.active=s.active
            AND u.tahun_anggaran=s.tahun_anggaran
        WHERE s.tahun_anggaran=%d
            and s.active=1
            and s.id_skpd=%d
    ", $input['tahun_anggaran'], $input['id_skpd']), ARRAY_A);
    if (!empty($data_skpd)) {
        $nama_skpd = $data_skpd[0]['kode_skpd'] . ' ' . $data_skpd[0]['nama_skpd'];
        $nama_skpd = '<br>' . $nama_skpd;
    } else {
        die('<h1 class="text_tengah">SKPD tidak ditemukan!</h1>');
    }
} else {
    $data_skpd = $wpdb->get_results($wpdb->prepare("
    select 
        s.*,
        u.kode_skpd AS kode_unit,
        u.nama_skpd AS nama_unit
    FROM data_unit s
    JOIN data_unit u on u.id_skpd = s.id_unit
        AND u.active=s.active
        AND u.tahun_anggaran=s.tahun_anggaran
    WHERE s.tahun_anggaran=%d
        and s.active=1
    order by kode_skpd ASC
", $input['tahun_anggaran']), ARRAY_A);
}

$body = '';
$total_operasi = 0;
$total_modal = 0;
$total_tak_terduga = 0;
$total_transfer = 0;
$total_all = 0;
$total_operasi_murni = 0;
$total_modal_murni = 0;
$total_tak_terduga_murni = 0;
$total_transfer_murni = 0;
$total_all_murni = 0;
$data_all = array();
foreach ($data_skpd as $skpd) {
    $sql = "
        SELECT 
            *
        FROM data_sub_keg_bl" . $_suffix_sipd . "" . $_suffix . "
        WHERE id_sub_skpd=%d
            AND tahun_anggaran=%d
            AND active=1
            " . $where_jadwal . "
            ORDER BY kode_giat ASC, kode_sub_giat ASC";
    $subkeg = $wpdb->get_results($wpdb->prepare($sql, $skpd['id_skpd'], $input['tahun_anggaran']), ARRAY_A);
    foreach ($subkeg as $kk => $sub) {
        $where_jadwal_new = '';
        if (!empty($where_jadwal)) {
            $where_jadwal_new = str_replace('AND id_jadwal', 'AND r.id_jadwal', $where_jadwal);
        }
        $rincian_all = $wpdb->get_results(
            $wpdb->prepare("
                SELECT 
                    r.rincian_murni,
                    r.rincian,
                    r.kode_akun
                FROM data_rka" . $_suffix_sipd . "" . $_suffix . " r
                WHERE r.tahun_anggaran=%d
                    AND r.active=1
                    AND r.kode_sbl=%s
                    " . $where_jadwal_new . "
            ", $input['tahun_anggaran'], $sub['kode_sbl']),
            ARRAY_A
        );

        $data_pendapatan =  $wpdb->get_results(
            $wpdb->prepare("
                SELECT 
                    SUM(total) AS total,
                    SUM(nilaimurni) AS totalmurni
                FROM data_pendapatan" . $_suffix_sipd . "" . $_suffix . "
                WHERE tahun_anggaran=%d
                    AND active=1
                    AND id_skpd=%d
                    " . $where_jadwal . "
                GROUP BY kode_akun
                ORDER BY kode_akun ASC
            ", $input['tahun_anggaran'], $sub['id_sub_skpd']),
            ARRAY_A
        );

        foreach ($rincian_all as $rincian) {
            if (empty($data_all[$sub['id_urusan']])) {
                $data_all[$sub['id_urusan']] = array(
                    'id' => $sub['id_urusan'],
                    'kode' => $sub['kode_urusan'],
                    'nama' => $sub['nama_urusan'],
                    'operasi' => 0,
                    'modal' => 0,
                    'tak_terduga' => 0,
                    'transfer' => 0,
                    'total' => 0,
                    'operasi_murni' => 0,
                    'modal_murni' => 0,
                    'tak_terduga_murni' => 0,
                    'transfer_murni' => 0,
                    'total_murni' => 0,
                    'pendapatan' => $data_pendapatan,
                    'data' => array()
                );
            }
            if (empty($data_all[$sub['id_urusan']]['data'][$sub['id_bidang_urusan']])) {
                $data_all[$sub['id_urusan']]['data'][$sub['id_bidang_urusan']] = array(
                    'id' => $sub['id_bidang_urusan'],
                    'kode' => $sub['kode_bidang_urusan'],
                    'nama' => $sub['nama_bidang_urusan'],
                    'operasi' => 0,
                    'modal' => 0,
                    'tak_terduga' => 0,
                    'transfer' => 0,
                    'total' => 0,
                    'operasi_murni' => 0,
                    'modal_murni' => 0,
                    'tak_terduga_murni' => 0,
                    'transfer_murni' => 0,
                    'total_murni' => 0,
                    'pendapatan' => $data_pendapatan,
                    'data' => array()
                );
            }
            if (empty($data_all[$sub['id_urusan']]['data'][$sub['id_bidang_urusan']]['data'][$sub['id_sub_skpd']])) {
                $data_all[$sub['id_urusan']]['data'][$sub['id_bidang_urusan']]['data'][$sub['id_sub_skpd']] = array(
                    'id' => $sub['id_sub_skpd'],
                    'kode' => $sub['kode_sub_skpd'],
                    'nama' => $sub['nama_sub_skpd'],
                    'id_unit' => $skpd['id_unit'],
                    'kode_unit' => $skpd['kode_unit'],
                    'nama_unit' => $skpd['nama_unit'],
                    'operasi' => 0,
                    'modal' => 0,
                    'tak_terduga' => 0,
                    'transfer' => 0,
                    'total' => 0,
                    'operasi_murni' => 0,
                    'modal_murni' => 0,
                    'tak_terduga_murni' => 0,
                    'transfer_murni' => 0,
                    'total_murni' => 0,
                    'pendapatan' => $data_pendapatan,
                    'data' => array()
                );
            }

            $data_all[$sub['id_urusan']]['total'] += $rincian['rincian'];
            $data_all[$sub['id_urusan']]['total_murni'] += $rincian['rincian_murni'];

            $data_all[$sub['id_urusan']]['data'][$sub['id_bidang_urusan']]['total'] += $rincian['rincian'];
            $data_all[$sub['id_urusan']]['data'][$sub['id_bidang_urusan']]['total_murni'] += $rincian['rincian_murni'];

            $data_all[$sub['id_urusan']]['data'][$sub['id_bidang_urusan']]['data'][$sub['id_sub_skpd']]['total'] += $rincian['rincian'];
            $data_all[$sub['id_urusan']]['data'][$sub['id_bidang_urusan']]['data'][$sub['id_sub_skpd']]['total_murni'] += $rincian['rincian_murni'];

            $rek = explode('.', $rincian['kode_akun']);
            $tipe_belanja = $rek[0] . '.' . $rek[1];
            if ($tipe_belanja == '5.1') {
                $data_all[$sub['id_urusan']]['operasi'] += $rincian['rincian'];
                $data_all[$sub['id_urusan']]['operasi_murni'] += $rincian['rincian_murni'];

                $data_all[$sub['id_urusan']]['data'][$sub['id_bidang_urusan']]['operasi'] += $rincian['rincian'];
                $data_all[$sub['id_urusan']]['data'][$sub['id_bidang_urusan']]['operasi_murni'] += $rincian['rincian_murni'];

                $data_all[$sub['id_urusan']]['data'][$sub['id_bidang_urusan']]['data'][$sub['id_sub_skpd']]['operasi'] += $rincian['rincian'];
                $data_all[$sub['id_urusan']]['data'][$sub['id_bidang_urusan']]['data'][$sub['id_sub_skpd']]['operasi_murni'] += $rincian['rincian_murni'];
            } else if ($tipe_belanja == '5.2') {
                $data_all[$sub['id_urusan']]['modal'] += $rincian['rincian'];
                $data_all[$sub['id_urusan']]['modal_murni'] += $rincian['rincian_murni'];

                $data_all[$sub['id_urusan']]['data'][$sub['id_bidang_urusan']]['modal'] += $rincian['rincian'];
                $data_all[$sub['id_urusan']]['data'][$sub['id_bidang_urusan']]['modal_murni'] += $rincian['rincian_murni'];

                $data_all[$sub['id_urusan']]['data'][$sub['id_bidang_urusan']]['data'][$sub['id_sub_skpd']]['modal'] += $rincian['rincian'];
                $data_all[$sub['id_urusan']]['data'][$sub['id_bidang_urusan']]['data'][$sub['id_sub_skpd']]['modal_murni'] += $rincian['rincian_murni'];
            } else if ($tipe_belanja == '5.3') {
                $data_all[$sub['id_urusan']]['tak_terduga'] += $rincian['rincian'];
                $data_all[$sub['id_urusan']]['tak_terduga_murni'] += $rincian['rincian_murni'];

                $data_all[$sub['id_urusan']]['data'][$sub['id_bidang_urusan']]['tak_terduga'] += $rincian['rincian'];
                $data_all[$sub['id_urusan']]['data'][$sub['id_bidang_urusan']]['tak_terduga_murni'] += $rincian['rincian_murni'];

                $data_all[$sub['id_urusan']]['data'][$sub['id_bidang_urusan']]['data'][$sub['id_sub_skpd']]['tak_terduga'] += $rincian['rincian'];
                $data_all[$sub['id_urusan']]['data'][$sub['id_bidang_urusan']]['data'][$sub['id_sub_skpd']]['tak_terduga_murni'] += $rincian['rincian_murni'];
            } else if ($tipe_belanja == '5.4') {
                $data_all[$sub['id_urusan']]['transfer'] += $rincian['rincian'];
                $data_all[$sub['id_urusan']]['transfer_murni'] += $rincian['rincian_murni'];

                $data_all[$sub['id_urusan']]['data'][$sub['id_bidang_urusan']]['transfer'] += $rincian['rincian'];
                $data_all[$sub['id_urusan']]['data'][$sub['id_bidang_urusan']]['transfer_murni'] += $rincian['rincian_murni'];

                $data_all[$sub['id_urusan']]['data'][$sub['id_bidang_urusan']]['data'][$sub['id_sub_skpd']]['transfer'] += $rincian['rincian'];
                $data_all[$sub['id_urusan']]['data'][$sub['id_bidang_urusan']]['data'][$sub['id_sub_skpd']]['transfer_murni'] += $rincian['rincian_murni'];
            }
        }
    }
    $counter = 1;
    $counter_bidang_urusan = 1;
    foreach ($data_all as $urusan) {
        $body .= '
                <tr data-id="' . $urusan['id'] . '">
                    <td class="kanan bawah atas kiri text_tengah">' . $counter . '</td>
                    <td class="kanan bawah atas kiri">' . '</td>
                    <td class="kanan bawah atas kiri">' . '</td>
                    <td class="kanan bawah atas kiri text_kiri">' . $urusan['nama'] . '</td>
                    <td class="kanan bawah atas kiri text_kanan">' . $this->_number_format($urusan['pendapatan']) . '</td>
                    <td class="kanan bawah atas kiri text_kanan">' . $this->_number_format($urusan['operasi']) . '</td>
                    <td class="kanan bawah atas kiri text_kanan">' . $this->_number_format($urusan['modal']) . '</td>
                    <td class="kanan bawah atas kiri text_kanan">' . $this->_number_format($urusan['tak_terduga']) . '</td>
                    <td class="kanan bawah atas kiri text_kanan">' . $this->_number_format($urusan['transfer']) . '</td>
                    <td class="kanan bawah atas kiri text_kanan">' . $this->_number_format($urusan['total']) . '</td>
                </tr>';
        foreach ($urusan['data'] as $bidang_urusan) {
            $body .= '
                <tr data-id="' . $bidang_urusan['id'] . '">
                    <td class="kanan bawah atas kiri text_tengah">' . $counter . '</td>
                    <td class="kanan bawah atas kiri text_tengah">' . $counter_bidang_urusan . '</td>
                    <td class="kanan bawah atas kiri text_kiri">' . $bidang_urusan['kode'] . '</td>
                    <td class="kanan bawah atas kiri text_kiri">' . $bidang_urusan['nama'] . '</td>
                    <td class="kanan bawah atas kiri text_kanan">' . $this->_number_format($bidang_urusan['pendapatan']) . '</td>
                    <td class="kanan bawah atas kiri text_kanan">' . $this->_number_format($bidang_urusan['operasi']) . '</td>
                    <td class="kanan bawah atas kiri text_kanan">' . $this->_number_format($bidang_urusan['modal']) . '</td>
                    <td class="kanan bawah atas kiri text_kanan">' . $this->_number_format($bidang_urusan['tak_terduga']) . '</td>
                    <td class="kanan bawah atas kiri text_kanan">' . $this->_number_format($bidang_urusan['transfer']) . '</td>
                    <td class="kanan bawah atas kiri text_kanan">' . $this->_number_format($bidang_urusan['total']) . '</td>
                </tr>';
            foreach ($bidang_urusan['data'] as $skpd) {
                $body .= '
                    <tr data-id="' . $skpd['id'] . '">
                    <td class="kanan bawah atas kiri text_tengah">' . $counter . '</td>
                    <td class="kanan bawah atas kiri text_tengah">' . $counter_bidang_urusan . '</td>
                        <td class="kanan bawah atas kiri text_kiri">' . $skpd['kode'] . '</td>
                        <td class="kanan bawah atas kiri text_kiri">' . $skpd['nama'] . '</td>
                        <td class="kanan bawah atas kiri text_kanan">' . $this->_number_format($skpd['pendapatan']) . '</td>
                        <td class="kanan bawah atas kiri text_kanan">' . $this->_number_format($skpd['operasi']) . '</td>
                        <td class="kanan bawah atas kiri text_kanan">' . $this->_number_format($skpd['modal']) . '</td>
                        <td class="kanan bawah atas kiri text_kanan">' . $this->_number_format($skpd['tak_terduga']) . '</td>
                        <td class="kanan bawah atas kiri text_kanan">' . $this->_number_format($skpd['transfer']) . '</td>
                        <td class="kanan bawah atas kiri text_kanan">' . $this->_number_format($skpd['total']) . '</td>
                    </tr>';
            }
            $counter_bidang_urusan++;
        }
        $counter++;
    }
}
?>
<div id="cetak" title="Laporan APBD PERDA Lampiran II Tahun Anggaran <?php echo $input['tahun_anggaran']; ?>" style="padding: 5px;">
    <table align="right" class="no-border no-padding" style="width:280px; font-size: 12px;">
        <tr>
            <td width="80" class="align-top">Lampiran II </td>
            <td width="10" class="align-top">:</td>
            <td colspan="3" class="align-top" contenteditable="true"> Peraturan Daerah xxxxx </td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td width="10">&nbsp;</td>
            <td width="60" class="text-start">Nomor</td>
            <td width="10">:</td>
            <td class="text-start" contenteditable="true">&nbsp;xx Desember xxxx</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td width="10">&nbsp;</td>
            <td width="60" class="text-start">Tanggal</td>
            <td width="10">:</td>
            <td class="text-start" contenteditable="true">&nbsp;xx Desember xxxx</td>
        </tr>
    </table>
    <h3 class="table-header text-uppercase" style="text-align: center;">
        <?php echo $nama_pemda; ?>
        <br>RINGKASAN APBD YANG DIKLASIFIKASIKAN MENURUT URUSAN PEMERINTAHAN DAERAH DAN ORGANISASI
        <br>TAHUN ANGGARAN <?php echo $input['tahun_anggaran']; ?>
    </h3>
    <table cellpadding="3" cellspacing="0" class="apbd-perda" width="100%">
        <thead>
            <tr>
                <td class="atas kanan bawah kiri text_tengah text_blok colspan_kurang" colspan="3" rowspan="2">Kode</td>
                <td class="atas kanan bawah text_tengah text_blok" rowspan="2">Urusan Pemerintah Daerah</td>
                <td class="atas kanan bawah text_tengah text_blok" rowspan="2">Pendapatan</td>
                <td class="atas kanan bawah text_tengah text_blok" colspan="5">Belanja</td>
            </tr>
            <tr>
                <td class="atas kanan bawah text_tengah text_blok">Operasi</td>
                <td class="atas kanan bawah text_tengah text_blok">Modal</td>
                <td class="atas kanan bawah text_tengah text_blok">Tidak Terduga</td>
                <td class="atas kanan bawah text_tengah text_blok">Transfer</td>
                <td class="atas kanan bawah text_tengah text_blok">Jumlah Belanja</td>
            </tr>
        </thead>
        <tbody>
            <?php
            echo $body;
            ?>
        </tbody>
    </table>
    <table width="25%" class="table-ttd no-border no-padding" align="right" cellpadding="2" cellspacing="0" style="width:280px; font-size: 12px;">
        <tr>
            <td colspan="3" class="text_tengah" height="20px"></td>
        </tr>
        <tr>
            <td colspan="3" class="text_tengah text_15" contenteditable="true">Bupati XXXX </td>
        </tr>
        <tr>
            <td colspan="3" height="80">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="3" class="text_tengah" contenteditable="true">XXXXXXXXXXX</td>
        </tr>
        <tr>
            <td colspan="3" class="text_tengah"></td>
        </tr>
    </table>
</div>

<script type="text/javascript">
    jQuery(document).ready(function() {
        run_download_excel();

        var currentUrl = window.location.href;
        var url = new URL(currentUrl);

        var _url = url.origin + url.pathname + '?key=' + url.searchParams.get('key');

        var type = url.searchParams.get("type");
        var id_jadwal_lokal = url.searchParams.get("id_jadwal_lokal");
        var id_unit = url.searchParams.get("id_unit");

        if (id_jadwal_lokal) {
            _url += '&id_jadwal_lokal=' + id_jadwal_lokal;
        }
        if (id_unit) {
            _url += '&id_unit=' + id_unit;
        }

        var extend_action = '';
        if (type && type === 'pergeseran') {
            extend_action += '<a class="btn btn-primary" target="_blank" href="' + _url + '" style="margin-left: 10px;"><span class="dashicons dashicons-controls-back"></span> Halaman APBD Lampiran 2</a>';
        } else {
            extend_action += '<a class="btn btn-primary" target="_blank" href="' + _url + '&type=pergeseran" style="margin-left: 10px;"><span class="dashicons dashicons-controls-forward"></span> Halaman Pergeseran/Perubahan APBD Lampiran 2</a>';
        }

        extend_action += '' +
            '<div style="margin-top: 15px">' +
            '<label><input id="hide1" type="checkbox" onclick="hide_header_ttd(this, 1)"> Sembunyikan header & TTD</label>' +
            '<label style="margin-left: 25px;"><input id="hide2" type="checkbox" onclick="hide_header_ttd(this, 2)"> Sembunyikan header</label>' +
            '<label style="margin-left: 25px;"><input id="hide3" type="checkbox" onclick="hide_header_ttd(this, 3)"> Sembunyikan TTD</label>' +
            '<label style="margin-left: 25px;"><input type="checkbox" onclick="hide_rekening_objek(this)"> Sembunyikan Rekening Objek & Sub Rekening Objek</label>' +
            '</div>';

        jQuery('#action-sipd').append(extend_action);
    });

    function hide_header_ttd(that, type) {
        var checked = jQuery(that).is(':checked');
        var hide2 = jQuery('#hide2').is(':checked');
        var hide3 = jQuery('#hide3').is(':checked');
        jQuery('.table-ttd').show();
        jQuery('.table-header').show();
        if (checked) {
            if (type == 1) {
                jQuery('#hide2').prop('checked', false);
                jQuery('#hide3').prop('checked', false);
                jQuery('.table-ttd').hide();
                jQuery('.table-header').hide();
            } else if (type == 2) {
                jQuery('#hide1').prop('checked', false);
                if (hide3) {
                    jQuery('.table-ttd').hide();
                }
                jQuery('.table-header').hide();
            } else if (type == 3) {
                jQuery('#hide1').prop('checked', false);
                if (hide2) {
                    jQuery('.table-header').hide();
                }
                jQuery('.table-ttd').hide();
            }
        }
    }

    function hide_rekening_objek(that) {
        var checked = jQuery(that).is(':checked');
        if (checked) {
            jQuery('.rincian_objek').hide();
            jQuery('.sub_rincian_objek').hide();
            jQuery('.colspan_kurang').map(function(i, b) {
                var colspan = +jQuery(b).attr('colspan');
                jQuery(b).attr('colspan', colspan - 2);
            });
        } else {
            jQuery('.rincian_objek').show();
            jQuery('.sub_rincian_objek').show();
            jQuery('.colspan_kurang').map(function(i, b) {
                var colspan = +jQuery(b).attr('colspan');
                jQuery(b).attr('colspan', colspan + 2);
            });
        }
    }
</script>