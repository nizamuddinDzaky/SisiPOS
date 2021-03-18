<?php defined('BASEPATH') or exit('No direct script access allowed');

class Survey extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('db_model');
        $this->lang->load('feedback', $this->Settings->user_language);
        $this->lang->load('auth', $this->Settings->user_language);
        // $this->insertLogActivities();
    }

    public function index()
    {
        $this->data['title_at'] = 'Survei Pelanggan';
        $this->load->view('aksestoko/header', $this->data);
        $this->load->view('aksestoko/feedback/to_survey',$this->data);
        $this->load->view('aksestoko/footer', $this->data);
    }

    public function form()
    {
        if($this->db_model->getActiveSurveyAT()){
            if(!$this->db_model->checkCustomerResponse()){
                if ($this->isPost()) {
                    $this->db->trans_begin();
                    try {
                        $active_survey = $this->db_model->getActiveSurveyAT();
                        $company_data = $this->site->getCompanyByID($this->session->userdata('company_id'));
                        $data=[
                            'category_id'   => $active_survey->id,
                            'repeat'        => $active_survey->repeat,
                            'user_id'       => $this->session->userdata('user_id'),
                            'f_company_id'  => $this->session->userdata('company_id'),
                            'company'       => $company_data->company,
                            'user_code'     => $company_data->cf1,
                            'created_at'    => date('Y-m-d H:i:s'),
                            'flag'          => 'aksestoko'
                        ];
                        $id = $this->db_model->addFeedback($data);
                        if (!$id) {
                            throw new \Exception('Failed');
                        }
                        $this->db->trans_commit();
        
                        $data = []; $num = 0;
                        $response_id = $this->db_model->getLastResponseID();
                        for($i=1;$i<=$this->input->post('num');$i++) {
                            $type = $this->input->post('question_type_'.$i);
                            if($type == 'checkbox'){
                                $list_answer = $this->input->post('answer_'.$i.'[]');
                                for($j=0; $j< count($list_answer); $j++){
                                    $data[$num]= [
                                        'survey_id'     => $response_id->id,
                                        'question_id'   => $this->input->post('question_'.$i),
                                        'answer'        => $list_answer[$j],
                                        'created_at'    => date('Y-m-d H:i:s')
                                    ];
                                    $num++;
                                }
                            }else{
                                $data[$num]= [
                                    'survey_id'     => $response_id->id,
                                    'question_id'   => $this->input->post('question_'.$i),
                                    'answer'        => $this->input->post('answer_'.$i),
                                    'created_at'    => date('Y-m-d H:i:s')
                                ];
                            }
                            $num++;
                        }
                        $id = $this->db_model->addFeedbackResponse($data);
                        if (!$id) {
                            throw new \Exception('Failed');
                        }
                        $this->db->trans_commit();
                        $this->load->view('aksestoko/header', $this->data);
                        $this->load->view('aksestoko/feedback/feedback_thanks',$this->data);
                        $this->load->view('aksestoko/footer', $this->data);
                    } catch (\Throwable $th) {
                        $this->db->trans_rollback();
                        $this->session->set_flashdata('error', $th->getMessage());
                        redirect($_SERVER['HTTP_REFERER']);
                    }
                } else {
                    $survey_active = $this->db_model->getActiveSurveyAT();
                    $this->data['question'] = $this->db_model->getQuestion($survey_active->id);
                    foreach($this->data['question'] as $row) {
                        $row->option_list = $this->db_model->getFeedbackOption($row->id);
                    }

                    $this->page_construct_feedback_at('feedback_aksestoko', $meta, $this->data);
                }
            } else {
                $this->session->set_flashdata('error', 'Anda telah mengisi survei ini.');
                $this->load->view('aksestoko/header', $this->data);
                $this->load->view('aksestoko/feedback/feedback_thanks',$this->data);
                $this->load->view('aksestoko/footer', $this->data);
            }
        } else {
            $redirect = $this->session->userdata('redirect') ?? null;
            redirect(aksestoko_route($redirect ?? 'aksestoko/home/select_supplier'));
        }
    }

}
