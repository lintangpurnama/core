<?php
defined('BASEPATH') or exit('No direct script access allowed');

class User extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        //security
        is_login();
    }
    public function index()
    {
        $data['title'] = 'My Profile';
        $data['user'] = $this->db->get_where('user', ['email' =>
        $this->session->userdata('email')])->row_array();

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('user/index', $data);
        $this->load->view('templates/footer');
    }

    public function edit()
    {
        $data['title'] = 'Edit Profile';
        $data['user'] = $this->db->get_where('user', ['email' =>
        $this->session->userdata('email')])->row_array();

        $this->form_validation->set_rules('name', 'Full Name', 'required|trim');

        if ($this->form_validation->run() == false) {
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('user/edit', $data);
            $this->load->view('user/footer_user');
        } else {
            $name = $this->input->post('name');
            $email = $this->input->post('email');


            //cek jika ada gambar
            $upload_img = $_FILES['image']['name'];

            if ($upload_img) {

                $config['allowed_types']        = '|jpg|png|jpeg';
                $config['max_size']             = 2048;
                $config['upload_path']          = './assets/img/profile/';


                $this->load->library('upload', $config);

                if ($this->upload->do_upload('image')) {
                    //menghapus gambar lama 
                    $old_img = $data['user']['image'];
                    if ($old_img != 'default.jpg') {
                        unlink(FCPATH . 'assets/img/profile/' . $old_img);
                    }


                    $new_img = $this->upload->data('file_name');
                    $this->db->set('image', $new_img);
                } else {
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">' . $this->upload->display_errors() . '</div>');
                    redirect('user/edit');
                }
            }


            $this->db->set('name', $name);
            $this->db->where('email', $email);
            $this->db->update('user');


            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">
               Your profile has been updated!
              </div>');
            redirect('user');
        }
    }

    public function logout()
    {
        $this->session->unset_userdata('email');
        $this->session->unset_userdata('role_id');

        $this->session->set_flashdata('message', '<div class="alert alert-primary" role="alert">
           You have been logged out!
          </div>');
        redirect('auth');
    }
    public function changepassword()
    {
        $data['title'] = 'Change Password';
        $data['user'] = $this->db->get_where('user', ['email' =>
        $this->session->userdata('email')])->row_array();

        //rules pw
        $this->form_validation->set_rules('current_password', 'Current Password', 'required|trim');

        $this->form_validation->set_rules('new_password1', 'New Password', 'required|trim|min_length[3]|matches[new_password2]');

        $this->form_validation->set_rules('new_password2', 'Repeat Password', 'required|trim|min_length[3]|matches[new_password1]');

        if ($this->form_validation->run() == false) {

            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('user/change_pw', $data);
            $this->load->view('user/footer_user');
        } else {
            //mengambil input user
            $current_pw = $this->input->post('current_password');
            $new_pw = $this->input->post('new_password1');

            //cek password kalo salah
            if (!password_verify($current_pw, $data['user']['password'])) {
                // $this->session->form_error('current_password');
                $this->session->set_flashdata('message', '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Wrong Password!</strong> The password that is entered is wrong! please enter the correct password.
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>');

                redirect('user/changepassword');
            } else {

                // New password cannot be the same as current password
                //pw baru tidak boleh sama dengan passwrd lama
                if ($current_pw == $new_pw) {
                    $this->session->set_flashdata('message', '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Password can not be changed! </strong> New password cannot be the same as current password.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>');
                    redirect('user/changepassword');
                } else {


                    //password ok
                    $password_hash = password_hash($new_pw, PASSWORD_DEFAULT);
                    $this->db->set('password', $password_hash);
                    $this->db->where('email', $this->session->userdata('email'));
                    $this->db->update('user');

                    $this->session->set_flashdata('message', '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Password changed! </strong> Please <a class="font-weight-bold" href="logout">login</a> to make sure the password has been changed .
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>');
                    redirect('user/changepassword');
                }
            }
        }
    }
}
