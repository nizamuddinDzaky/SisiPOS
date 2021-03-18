<?php defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'core/MY_API_Distributor_Controller.php';

class Auth extends MY_API_Distributor_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->lang->load('auth', $this->Settings->user_language);
        $this->load->model('auth_model');
        $this->load->model('integration_model');
        $this->load->library('ion_auth');

        $this->digital_upload_path = 'assets/uploads/avatars';
        $this->upload_path = 'assets/uploads/';
        $this->thumbs_path = 'assets/uploads/thumbs/';
        $this->image_types = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size = '545625';
    }

    public function login_post()
    {
        try {
            $config = [
                [
                    'field' => 'username',
                    'label' => 'username',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '`%s` required',
                    ],
                ],
                [
                    'field' => 'password',
                    'label' => 'password',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '`%s` required',
                    ],
                ],
            ];

            $this->validate_form($config);

            $username = $this->body('username');
            $password = $this->body('password');

            if (!$this->ion_auth->login($username, $password, false, null)) {
                throw new Exception($this->ion_auth->errors(), 400);
            }

            if ($this->Settings->mmode) {
                if (!$this->ion_auth->in_group('owner')) {
                    throw new Exception(lang('site_is_offline_plz_try_later'), 503);
                }
            }

            if ($this->ion_auth->in_group('customer')) {
                throw new Exception(lang('site_is_offline_plz_try_later'), 503);
            }

            $response = [
                'user_id' => $this->session->userdata('user_id'),
                'company_id' => $this->session->userdata('company_id'),
            ];

            $token = $this->encrypt(json_encode($response), $this->key);
            $response['token'] = $token;

            $this->buildResponse("success", REST_Controller::HTTP_OK, $this->ion_auth->messages(), $response);
        } catch (\Throwable $th) {
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function profile_get()
    {
        $this->db->trans_begin();

        try {
            $auth = $this->authorize();

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Get Profile success", $auth);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function update_profile_put()
    {
        $this->db->trans_begin();
        try {
            $auth           = $this->authorize();
            $id             = $this->input->get('id_user');
            if (!$id) {
                throw new Exception("Params `id_user` required", 404);
            }
            $config = [
                [
                    'field' => 'username',
                    'label' => 'username',
                    'rules' => "trim|edit_unique[users.username.$id]",
                    'errors' => [
                        'edit_unique' => '%s has already taken',
                    ],
                ],
                [
                    'field' => 'email',
                    'label' => 'email',
                    'rules' => "trim|edit_unique[users.email.$id]",
                    'errors' => [
                        'edit_unique' => '%s has already taken',
                    ],
                ]
            ];
            $this->validate_form($config);

            $username       = $this->body('username') ?? $auth->user->username;
            $email          = $this->body('email') ?? $auth->user->email;
            $first_name     = $this->body('first_name') ?? $auth->user->first_name;
            $last_name      = $this->body('last_name') ?? $auth->user->last_name;
            $company        = $this->body('company') ?? $auth->user->company;
            $phone          = $this->body('phone') ?? $auth->user->phone;
            $gender         = $this->body('gender') ?? $auth->user->gender;
            $active         = $this->body('active') ?? $auth->user->active;
            $group_id       = $this->body('group_id') ?? $auth->user->group_id;
            $biller_id      = $this->body('biller_id') ?? $auth->user->biller_id;
            $warehouse_id   = $this->body('warehouse_id') ?? $auth->user->warehouse_id;
            $award_points   = $this->body('award_points') ?? $auth->user->award_points;
            $view_right     = $this->body('view_right') ?? $auth->user->username;
            $edit_right     = $this->body('edit_right') ?? $auth->user->edit_right;
            $allow_discount = $this->body('allow_discount') ?? $auth->user->allow_discount;
            $country        = $this->body('province') ?? $auth->user->country;
            $city           = $this->body('city') ?? $auth->user->city;
            $state          = $this->body('state') ?? $auth->user->state;
            $address        = $this->body('address') ?? $auth->user->address;
            $cf1            = $this->body('cf1') ?? $auth->company->cf1;
            $latitude       = $this->body('latitude') ?? $auth->company->latitude;
            $longitude      = $this->body('longitude') ?? $auth->company->longitude;
            $postal_code    = $this->body('postal_code') ?? $auth->company->postal_code;
            $cf2            = $this->body('cf2') ?? $auth->company->cf2;
            $cf3            = $this->body('cf3') ?? $auth->company->cf3;
            $cf4            = $this->body('cf4') ?? $auth->company->cf4;
            $cf5            = $this->body('cf5') ?? $auth->company->cf5;

            if (!$this->Admin && $id != $auth->user->id) {
                throw new Exception('Put Update Customer failed because access denied', 404);
            }
            if ($this->Admin) {
                if ($id == $auth->user->id) {
                    $data = [
                        'first_name'      => $first_name,
                        'last_name'       => $last_name,
                        'company'         => $company,
                        'phone'           => $phone,
                        'email'           => $email,
                        'gender'          => $gender,
                        'award_points'    => $award_points,
                        'country'         => $country,
                        'username'        => $email,
                        'city'            => $city,
                        'state'           => $state,
                        'address'         => $address,
                    ];
                } else {
                    $data =  [
                        'first_name'      => $first_name,
                        'last_name'       => $last_name,
                        'company'         => $company,
                        'phone'           => $phone,
                        'email'           => $email,
                        'group_id'        => $group_id,
                        'gender'          => $gender,
                        'warehouse_id'    => $warehouse_id,
                        'view_right'      => $view_right,
                        'edit_right'      => $edit_right,
                        'allow_discount'  => $allow_discount,
                        'award_points'    => $award_points,
                        'country'         => $country,
                        'username'        => $email,
                        'city'            => $city,
                        'state'           => $state,
                        'address'         => $address,
                    ];
                }
            } else {
                $data = array(
                    'first_name'      => $first_name,
                    'last_name'       => $last_name,
                    'company'         => $company,
                    'phone'           => $phone,
                    'gender'          => $gender
                );
            }

            if ($this->sma->UpdateAutorizedPermissions('users'))
                $data['active'] = $active;

            if ($cf1) {
                $companies = [
                    "latitude"    => $latitude,
                    "longitude"   => $longitude,
                    "cf2"         => $cf2,
                    "cf3"         => $cf3,
                    "cf4"         => $cf4,
                    "cf5"         => $cf5,
                    "postal_code" => $postal_code
                ];

                if($cf1 != $auth->company->cf1){
                    $cmp = $this->auth_model->checkCF1Distributor($cf1, $auth->company->id);
                    if ($cmp) {
                        throw new Exception('Put Update Customer failed because there was a duplicate cf1', 503);
                    }
                }
                
                $companies['cf1'] = $cf1;
            } else {
                $companies = [
                    "latitude"    => $latitude,
                    "longitude"   => $longitude,
                    "postal_code" => $postal_code
                ];
            }
            $update_data_user       = $this->ion_auth->update($id, $data);
            $update_data_company    = $this->db->update('companies', $companies, array('id' => $auth->user->biller_id));

            if (!$update_data_user) {
                throw new Exception('Put Update Customer failed because ' . $this->ion_auth->errors());
            }
            if (!$update_data_company) {
                throw new Exception('Put Update Customer failed' . $this->ion_auth->errors());
            }

            $response = [
                "profile" => [
                    "id"         => $id,
                    "company_id" => $auth->user->company_id
                ]
            ];

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Put Update Profile success", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function update_password_put()
    {
        $this->db->trans_begin();

        try {
            $auth   = $this->authorize();
            $config = [
                [
                    'field' => 'old_password',
                    'label' => 'old_password',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '`%s` required',
                    ],
                ], [
                    'field' => 'new_password',
                    'label' => 'new_password',
                    'rules' => 'required|min_length[8]|max_length[25]',
                    'errors' => [
                        'required' => '`%s` required',
                    ],
                ],
                [
                    'field' => 'new_password_confirm',
                    'label' => 'new_password_confirm',
                    'rules' => 'required|matches[new_password]',
                    'errors' => [
                        'required' => '`%s` required',
                    ],
                ],
            ];
            $this->validate_form($config);

            $old_password   = $this->body('old_password');
            $new_password   = $this->body('new_password');

            $change         = $this->ion_auth->change_password($auth->user->email, $old_password, $new_password);

            if (!$change) {
                throw new Exception('Put Update Password failed because' . $this->ion_auth->errors());
            }

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Put Update Password success");
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function forgot_password_post()
    {
        $this->db->trans_begin();

        try {

            $config = [
                [
                    'field' => 'email',
                    'label' => 'email',
                    'rules' => 'required|valid_email',
                    'errors' => [
                        'required' => '`%s` required',
                    ],
                ]
            ];
            $this->validate_form($config);
            $email    = $this->post('email');
            $identity = $this->ion_auth->where('email', strtolower($email))->users()->row();

            if (empty($identity)) {
                throw new Exception('Post Forgot Password failed because email not found');
            }

            $forgotten = $this->ion_auth->forgotten_password($identity->email);

            if (!$forgotten) {
                throw new Exception('Post Forgot Password failed because' . $this->ion_auth->errors());
            }

            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Post Forgot Password success");
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function reset_password_post()
    {
        $this->db->trans_begin();

        try {
            $config = [
                [
                    'field' => 'code',
                    'label' => 'code',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '`%s` required',
                    ],
                ], [
                    'field' => 'new_password',
                    'label' => 'new_password',
                    'rules' => 'required|min_length[8]|max_length[25]|matches[new_confirm_password]',
                    'errors' => [
                        'required' => '`%s` required',
                    ],
                ], [
                    'field' => 'new_confirm_password',
                    'label' => 'new_confirm_password',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '`%s` required',
                    ],
                ]
            ];

            $this->validate_form($config);

            $code           = $this->post('code');
            $new_password   = $this->post('new_password');
            $user           = $this->ion_auth->forgotten_password_check($code);

            if ($user) {
                throw new Exception("Post Reset Password failed because " . $this->ion_auth->errors(), 404);
            }

            $change     = $this->ion_auth->reset_password($user->email, $new_password);

            if (!$change) {
                throw new Exception("Post Reset Password failed because" . $this->ion_auth->error(), 404);
            }
            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Post Reset Password success");
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }

    public function update_avatar_post()
    {
        $this->db->trans_begin();
        try {
            $auth = $this->authorize();
            // print_r($auth->user->id);die;
            // $id_payments = $this->input->get('id_payments');

            if ($_FILES['avatar']['size'] < 0) {
                throw new Exception('Post upload avatar failed, Size less then zero', 404);
            }

            /*$this->load->library('upload');
            $config['upload_path']    = $this->digital_upload_path;
            $config['allowed_types']  = $this->image_types;
            $config['max_size']       = $this->allowed_file_size;
            $config['overwrite']      = false;
            $config['encrypt_name']   = true;
            $this->upload->initialize($config);
            if (!$this->upload->do_upload('avatar')) {
                $error = $this->upload->display_errors();
                throw new Exception('Post upload avatar failed ' . $error, 404);
            }
            $photo = $this->upload->file_name;

            $this->load->helper('file');
            $this->load->library('image_lib');
            $config['image_library'] = 'gd2';
            $config['source_image'] = 'assets/uploads/avatars/' . $photo;
            $config['new_image'] = 'assets/uploads/avatars/thumbs/' . $photo;
            $config['maintain_ratio'] = true;
            $config['width'] = 150;
            $config['height'] = 150;

            $this->image_lib->clear();
            $this->image_lib->initialize($config);

            if (!$this->image_lib->resize()) {
                throw new Exception('Post upload avatar failed ' . $error, 404);
            }*/

            $uploadedImg = $this->integration_model->upload_files($_FILES['avatar']);
            $photo       = $uploadedImg->url;

            if (!$this->auth_model->updateAvatar($auth->user->id, $photo)) {
                throw new Exception('Post upload avatar failed, Because update avatar failed');
            }

            $response = [
                "delivery" => [
                    "id"           => $auth->user->id,
                    "file_name"    => $photo
                ]
            ];
            $this->db->trans_commit();
            $this->buildResponse("success", REST_Controller::HTTP_OK, "Post upload file Avatar Profile success", $response);
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            $this->buildResponse("failed", $th->getCode() != 0 ? $th->getCode() : 500, $th->getMessage(), null);
        }
    }
}
