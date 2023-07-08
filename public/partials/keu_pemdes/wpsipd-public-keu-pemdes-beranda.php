<?php 
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}
global $wpdb;

$input = array(
    'tahun_anggaran' => '2023'
);

$cek_login = true;
// $user_id = um_user( 'ID' );
// $user_meta = get_userdata($user_id);
// if(
//     !empty($user_id)
//     && (
//         in_array("administrator", $user_meta->roles)
//         || in_array("PLT", $user_meta->roles) 
//         || in_array("PA", $user_meta->roles) 
//         || in_array("KPA", $user_meta->roles)
//     )
// ){
//     $cek_login = true;
// }

if(!empty($_GET) && !empty($_GET['tahun'])){
    $input['tahun_anggaran'] = $_GET['tahun'];
}

$tampil_pilkades = get_option("_bkk_pilkades_".$input['tahun_anggaran']);

define('BKK_INF', 'BKK Infrastruktur');
define('BKK_PIL', 'BKK Pilkades');
define('BHPD', 'Bagi Hasil Pajak Daerah');
define('BHRD', 'Bagi Hasil Retribusi Daerah');
define('BKU_ADD', 'BKU Alokasi Dana Desa');
define('BKU_DD', 'BKU Dana Desa');

$data_all = array(
    BKK_INF => array('pagu' => 0, 'pencairan' => 0, 'url' => '#'),
    BKK_PIL => array('pagu' => 0, 'pencairan' => 0, 'url' => '#'),
    BHPD => array('pagu' => 0, 'pencairan' => 0, 'url' => '#'),
    BHRD => array('pagu' => 0, 'pencairan' => 0, 'url' => '#'),
    BKU_ADD => array('pagu' => 0, 'pencairan' => 0, 'url' => '#'),
    BKU_DD => array('pagu' => 0, 'pencairan' => 0, 'url' => '#')
);

$data_all[BKK_INF]['pagu'] = $wpdb->get_var($wpdb->prepare("
    SELECT 
        sum(total) as total 
    from data_bkk_desa 
    WHERE tahun_anggaran=%d
        and active=1
", $input['tahun_anggaran']));
$data_all[BKK_INF]['pencairan'] = $wpdb->get_var($wpdb->prepare("
    SELECT 
        SUM(p.total_pencairan) 
    FROM data_pencairan_bkk_desa p
    INNER JOIN data_bkk_desa b on p.id_kegiatan=b.id
    WHERE b.active=1
        AND b.tahun_anggaran=%d
", $input['tahun_anggaran']));
if($cek_login){
    $data_all[BKK_INF]['url'] =$this->generatePage('Laporan Keuangan Pemerintah Desa Bantuan Keuangan Khusus (BKK) Infrastruktur '.$input['tahun_anggaran'], false, '[keu_pemdes_bkk_inf tahun_anggaran="'.$input['tahun_anggaran'].'"]');
}

if(!empty($tampil_pilkades)){
    $data_all[BKK_PIL]['pagu'] = $wpdb->get_var($wpdb->prepare("
        SELECT 
            sum(total) as total 
        from data_bkk_pilkades_desa 
        WHERE tahun_anggaran=%d
            and active=1
    ", $input['tahun_anggaran']));
    $data_all[BKK_PIL]['pencairan'] = $wpdb->get_var($wpdb->prepare("
        SELECT 
            SUM(p.total_pencairan) 
        FROM data_pencairan_bkk_pilkades_desa p
        INNER JOIN data_bkk_pilkades_desa b on p.id_bkk_pilkades=b.id
        WHERE b.active=1
            AND b.tahun_anggaran=%d
    ", $input['tahun_anggaran']));
    if($cek_login){
        $data_all[BKK_PIL]['url'] = $this->generatePage('Laporan Keuangan Pemerintah Desa Bantuan Keuangan Khusus (BKK) Pilkades '.$input['tahun_anggaran'], false, '[keu_pemdes_bkk_pilkades tahun_anggaran="'.$input['tahun_anggaran'].'"]');
    }
}

$data_all[BHPD]['pagu'] = $wpdb->get_var($wpdb->prepare("
    SELECT 
        sum(total) as total 
    from data_bhpd_desa 
    WHERE tahun_anggaran=%d
        and active=1
", $input['tahun_anggaran']));
$data_all[BHPD]['pencairan'] = $wpdb->get_var($wpdb->prepare("
    SELECT 
        SUM(p.total_pencairan) 
    FROM data_pencairan_bhpd_desa p
    INNER JOIN data_bhpd_desa b on p.id_bhpd=b.id
    WHERE b.active=1
        AND b.tahun_anggaran=%d
", $input['tahun_anggaran']));
if($cek_login){
    $data_all[BHPD]['url'] = $this->generatePage('Laporan Keuangan Pemerintah Desa Bagi Hasil Pajak Daerah (BHPD) '.$input['tahun_anggaran'], false, '[keu_pemdes_bhpd tahun_anggaran="'.$input['tahun_anggaran'].'"]');
}

$data_all[BHRD]['pagu'] = $wpdb->get_var($wpdb->prepare("
    SELECT 
        sum(total) as total 
    from data_bhrd_desa 
    WHERE tahun_anggaran=%d
        and active=1
", $input['tahun_anggaran']));
$data_all[BHRD]['pencairan'] = $wpdb->get_var($wpdb->prepare("
    SELECT 
        SUM(p.total_pencairan) 
    FROM data_pencairan_bhrd_desa p
    INNER JOIN data_bhrd_desa b on p.id_bhrd=b.id
    WHERE b.active=1
        AND b.tahun_anggaran=%d
", $input['tahun_anggaran']));
if($cek_login){
    $data_all[BHRD]['url'] = $this->generatePage('Laporan Keuangan Pemerintah Desa Bagi Hasil Retribusi Daerah (BHRD) '.$input['tahun_anggaran'], false, '[keu_pemdes_bhrd tahun_anggaran="'.$input['tahun_anggaran'].'"]');
}

$data_all[BKU_DD]['pagu'] = $wpdb->get_var($wpdb->prepare("
    SELECT 
        sum(total) as total 
    from data_bku_dd_desa 
    WHERE tahun_anggaran=%d
        and active=1
", $input['tahun_anggaran']));
$data_all[BKU_DD]['pencairan'] = $wpdb->get_var($wpdb->prepare("
    SELECT 
        SUM(p.total_pencairan) 
    FROM data_pencairan_bku_dd_desa p
    INNER JOIN data_bku_dd_desa b on p.id_bku_dd=b.id
    WHERE b.active=1
        AND b.tahun_anggaran=%d
", $input['tahun_anggaran']));
if($cek_login){
    $data_all[BKU_DD]['url'] = $this->generatePage('Laporan Keuangan Pemerintah Desa Bantuan Keuangan Umum (BKU) Desa Dana Desa (DD) '.$input['tahun_anggaran'], false, '[keu_pemdes_bku_dd tahun_anggaran="'.$input['tahun_anggaran'].'"]');
}

$data_all[BKU_ADD]['pagu'] = $wpdb->get_var($wpdb->prepare("
    SELECT 
        sum(total) as total 
    from data_bku_add_desa 
    WHERE tahun_anggaran=%d
        and active=1
", $input['tahun_anggaran']));
$data_all[BKU_ADD]['pencairan'] = $wpdb->get_var($wpdb->prepare("
    SELECT 
        SUM(p.total_pencairan) 
    FROM data_pencairan_bku_add_desa p
    INNER JOIN data_bku_add_desa b on p.id_bku_add=b.id
    WHERE b.active=1
        AND b.tahun_anggaran=%d
", $input['tahun_anggaran']));
if($cek_login){
    $data_all[BKU_ADD]['url'] = $this->generatePage('Laporan Keuangan Pemerintah Desa Bantuan Keuangan Umum (BKU) Alokasi Dana Desa (ADD) '.$input['tahun_anggaran'], false, '[keu_pemdes_bku_add tahun_anggaran="'.$input['tahun_anggaran'].'"]');
}

function generateRandomColor($k){
    $color = array('#f44336', '#9c27b0', '#2196f3', '#009688', '#4caf50', '#cddc39', '#ff9800', '#795548', '#9e9e9e', '#607d8b');
    return $color[$k%10];
}

if(empty($tampil_pilkades)){
    unset($data_all[BKK_PIL]);
}

$body = '';
$desa = array();
$total_all = 0;
$belum_all = 0;
$realisasi_all = 0;
$persen_all = 0;

// grafik kec
$chart_keuangan = array(
    'label' => array(),
    'label1' => 'Anggaran',
    'label2' => 'Realisasi',
    'data1'  => array(),
    'color1' => array(),
    'data2'  => array(),
    'color2' => array()
);
$no = 0;
foreach($data_all as $jenis_keuangan => $val){
    $no++;
    $jenis_keuangan_render = $jenis_keuangan;
    if($cek_login){
        $jenis_keuangan_render = '<a href="'.$val['url'].'" target="_blank">'.$jenis_keuangan.'</a>';
    }
    $realisasi = $val['pencairan'];
    $belum_realisasi = $val['pagu'] - $realisasi;
    if($realisasi == 0){
        $persen = 0;
    }else{
        $persen = round(($realisasi/$val['pagu']) * 100, 2);
    }
    $body .= '
    <tr>
        <td class="text-center">'.($no).'</td>
        <td>'.$jenis_keuangan_render.'</td>
        <td class="text-right">'.number_format($val['pagu'],0,",",".").'</td>
        <td class="text-right">'.number_format($realisasi,0,",",".").'</td>
        <td class="text-right">'.number_format($belum_realisasi,0,",",".").'</td>
        <td class="text-center">'.$persen.'%</td>
    </tr>
    ';
    $total_all += $val['pagu'];
    $belum_all += $belum_realisasi;
    $realisasi_all += $realisasi;

    $chart_keuangan['label'][] = $jenis_keuangan;
    $chart_keuangan['data1'][] = $val['pagu'];
    $chart_keuangan['color1'][] = generateRandomColor(1);
    $chart_keuangan['data2'][] = $realisasi;
    $chart_keuangan['color2'][] = generateRandomColor(2);
}
if($realisasi_all == 0){
    $persen_all = 0;
}else{
    $persen_all = round(($realisasi_all/$total_all)*100, 2);
}

$url_per_kecamatan = $this->generatePage('Laporan Realisasi Keuangan Desa per Kecamatan', false, '[laporan_keu_pemdes_per_kecamatan]');
?>

<h1 class="text-center">Keuangan Pemerintah Desa<br>Rekapitulasi Per Jenis Keuangan<br>Tahun <?php echo $input['tahun_anggaran']; ?></h1>
<div class="cetak">
    <div style="padding: 5px;">
        <div class="row">
            <div class="col-md-12">
                <div style="width: 100%; margin:auto 5px;">
                    <canvas id="chart_per_jenis"></canvas>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <h2 class="text-center" style="margin-top: 25px;">Tabel Keuangan Pemerintah Desa<br>Rekapitulasi Per Jenis Keuangan<br>Tahun <?php echo $input['tahun_anggaran']; ?></h2>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th class="atas kanan bawah kiri text_tengah text_blok" style="width: 30px;">No</th>
                            <th class="text-center">Jenis Keuangan</th>
                            <th class="atas kanan bawah text_tengah text_blok">Anggaran</th>
                            <th class="atas kanan bawah text_tengah text_blok">Realisasi</th>
                            <th class="atas kanan bawah text_tengah text_blok">Belum Realisasi</th>
                            <th class="atas kanan bawah text_tengah text_blok">% Realisasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php echo $body; ?>
                    </tbody>
                    <tfoot>
                        <th colspan="2">JUMLAH</th>
                        <th class="text-right"><?php echo number_format($total_all,0,",","."); ?></th>
                        <th class="text-right"><?php echo number_format($realisasi_all,0,",","."); ?></th>
                        <th class="text-right"><?php echo number_format($belum_all,0,",","."); ?></th>
                        <th class="text-center"><?php echo $persen_all; ?>%</th>
                    </tfoot>
                </table>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 text-center">
                <a style="margin: 20px; text-decoration: none;" href="<?php echo $url_per_kecamatan; ?>" target="_blank" class="btn btn-primary">Laporan Relisasi Keuangan Pemerintah Desa Per Kecamatan</a>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
window.keu = <?php echo json_encode($chart_keuangan); ?>;
window.pieChartkeua = new Chart(document.getElementById('chart_per_jenis'), {
    type: 'bar',
    data: {
        labels: keu.label,
        datasets: [
            {
                label: keu.label1,
                data: keu.data1,
                backgroundColor: keu.color1
            },
            {
                label: keu.label2,
                data: keu.data2,
                backgroundColor: keu.color2
            }
        ]
    }
});
</script>