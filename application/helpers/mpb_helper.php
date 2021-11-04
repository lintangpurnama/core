<?php

function is_login()
{
    $ci = get_instance();
    if (!$ci->session->userdata('email')) {
        redirect('auth');
    } else {
        //session roleid kalo udh login ada
        $role_id = $ci->session->userdata('role_id');
        $menu = $ci->uri->segment(1);
        //ambil data usermenu
        $querymenu = $ci->db->get_where('user_menu', ['menu' => $menu])->row_array();
        $menuid = $querymenu['id'];

        $userAccess = $ci->db->get_where('user_access_menu', [
            'role_id' => $role_id,
            'menu_id' => $menuid
        ]);
        //jika user akses ini ada isi nya  lebih kecil dari 1 maka hasil nya 0
        if ($userAccess->num_rows() < 1) {
            redirect('auth/blocked');
        }
    }
}
