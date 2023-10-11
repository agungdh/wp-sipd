<?php

class Wpsipd_Public_RKA {

    public function verifikasi_rka(){
        if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
        require_once WPSIPD_PLUGIN_PATH . 'public/partials/penganggaran/wpsipd-public-verifikasi-rka.php';
    }

    public function user_verikasi_rka(){
        if(!empty($_GET) && !empty($_GET['post'])){
            return '';
        }
        require_once WPSIPD_PLUGIN_PATH . 'public/partials/penganggaran/wpsipd-public-user-verifikasi-rka.php';
    }

    function tambah_user_verifikator(){
        global $wpdb;
		$ret = array();
		$ret['status'] = 'success';
		$ret['message'] = 'Berhasil tambah user!';
		if (!empty($_POST)) {
			if (!empty($_POST['api_key']) && $_POST['api_key'] == get_option( '_crb_api_key_extension' )) {
                $username = $_POST['username'];
                $password = $_POST['password'];
                $nama = $_POST['nama'];
                $email = $_POST['email'];
                $role = $_POST['role'];

                $insert_user = username_exists($username);
                if (!$insert_user) {
                    $option = array(
                        'user_login' => $username,
                        'user_pass' => $password,
                        'user_email' => $email,
                        'first_name' => $nama,
                        'display_name' => $nama,
                        'role' => $role
                    );
                    $insert_user = wp_insert_user($option);

                    if (is_wp_error($insert_user)) {
                        $ret['status'] = 'error';
                        $ret['message'] = $insert_user->get_error_message();
                    } else {
                        $ret['status'] = 'success';
                        $ret['message'] = 'User berhasil ditambahkan.';
                    }
                } else {
                    $ret['status'] = 'error';
                    $ret['message'] = 'Username sudah ada!';
                }
            } else {
				$ret['status'] = 'error';
				$ret['message'] = 'APIKEY tidak sesuai!';
			}
		} else {
			$ret['status'] = 'error';
			$ret['message'] = 'Format Salah!';
		}
		die(json_encode($ret));
    }

    function get_user_verifikator(){
        global $wpdb;
		$ret = array();
		$ret['status'] = 'success';
		$ret['message'] = 'Berhasil get user!';
		if (!empty($_POST)) {
			if (!empty($_POST['api_key']) && $_POST['api_key'] == get_option( '_crb_api_key_extension' )) {
                $user_id = um_user('ID');
                $user_meta = get_userdata($user_id);
                $params = $columns = $totalRecords = $data = array();
                $params = $_REQUEST;
                $roles = array('verifikator_bappeda', 'verifikator_bppkad', 'verifikator_pbj', 'verifikator_adbang', 'verifikator_inspektorat', 'verifikator_pupr');
                $args = array(
                    'role__in' => $roles,
                    'orderby' => 'user_nicename',
                    'order'   => 'ASC'
                );

                // check search value exist
                if (!empty($params['search']['value'])) {
                }

                $users = array();
                // get data user harus login sebagai admin
                if (in_array("administrator", $user_meta->roles)) {
                    $users = get_users( $args );
                }

                $data_user = array();
                foreach ($users as $recKey => $recVal) {
                    $btn = '<a class="btn btn-sm btn-warning" onclick="edit_data(\'' . $recVal->ID . '\'); return false;" href="#" title="Edit Data"><i class="dashicons dashicons-edit"></i></a>';
                    $btn .= '<a class="btn btn-sm btn-danger" onclick="hapus_data(\'' . $recVal->ID . '\'); return false;" href="#" title="Hapus Data"><i class="dashicons dashicons-trash"></i></a>';
                    $data_user[$recKey]['aksi'] = $btn;
                    $data_user[$recKey]['id'] = $recVal->ID;
                    $data_user[$recKey]['user'] = $recVal->user_login;
                    $data_user[$recKey]['nama'] = $recVal->display_name;
                    $data_user[$recKey]['email'] = $recVal->user_email;
                    $data_user[$recKey]['role'] = implode(', ',$recVal->roles);
                    // $data_user[$recKey]['all'] = $recVal;
                }

                $json_data = array(
                    "draw"            => intval($params['draw']),
                    "recordsTotal"    => intval(count($data_user)),
                    "recordsFiltered" => intval(count($data_user)),
                    "data"            => $data_user
                );

                die(json_encode($json_data));
            } else {
				$ret['status'] = 'error';
				$ret['message'] = 'APIKEY tidak sesuai!';
			}
		} else {
			$ret['status'] = 'error';
			$ret['message'] = 'Format Salah!';
		}
		die(json_encode($ret));
    }
}