<?php defined('BASEPATH') or exit('No direct script access allowed');
class Total_model extends CI_Model
{
    function total_rows()
    {
        return $this->db->get('user')->num_rows();
    }
    function total_sub_menu()
    {
        return $this->db->get('user_sub_menu')->num_rows();
    }
}
