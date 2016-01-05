<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends CM_HocvienController
{
//    private $auth;

//    public function __construct()
//    {
//        parent::__construct();
//        $this->auth = $this->cm_auth->check();
//        if ($this->auth == NULL) {
//            $this->cm_string->php_redirect("backend/auth/login");
//        }
//        $data['user'] = $this->auth;
//    }


    public function index()
    {
        $this->load->model('survey');
        $this->load->model('hocvien');

        $this->data['complete_survey_one'] = $this->survey->complete_survey_one($this->auth['id']);
        $this->data['complete_survey_ck'] = $this->survey->complete_survey_ck($this->auth['id']);
//        $this->data['complete_survey_ck'] = false;

        $baigiuaki = $this->survey->get_all_post($this->auth['id'], 4);
        if (count($baigiuaki) > 0) {
            $this->data['linkbaigiuaki'] = $baigiuaki[0]['source'];
        }
        $this->data['bai_cks'] = $this->survey->get_all_post($this->auth['id'], 8);

        $this->data['bai_tap_hoc_viens'] = $this->hocvien->get_all_bai_tap();


        $this->data['template'] = "hocvien/dashboard";
        $this->load->view('hocvien/layout/home', isset($this->data) ? $this->data : NULL);
    }

    public function danhsachbuoihoc()
    {
        $this->data['template'] = "hocvien/danhsachbuoihoc";
        $this->data['lectures'] = $this->lecture->get_lecture($this->auth['id']);

        $this->load->view('hocvien/layout/home', isset($this->data) ? $this->data : NULL);
    }

    public function chuyenbuoi($lectureid)
    {
        $this->load->model('lophoc');
        $this->load->model('quanlylophoc/monhoc');
        $this->load->model('quanlylophoc/attend');

        $this->data['template'] = 'hocvien/chuyenbuoi';
        $currentClassId = $this->attend->get_classid($this->auth['id'], $lectureid);
        $this->data['lectures'] = $this->lophoc->get_lop_theo_buoi($this->monhoc->get_courseid_by_studentid($this->auth['id']), $lectureid, $currentClassId);
        $this->load->view('hocvien/layout/home', isset($this->data) ? $this->data : NULL);
    }

    /*
     * doi buoi
     * 1 la chuyen toi
     * -1 la chuyen di
     */
    public function doibuoi($classid, $lectureid, $studentid)
    {
        $this->load->model('quanlylophoc/lecture');
        $this->lecture->chuyenbuoi($classid, $lectureid, $studentid, $this->auth['classid']);
        $this->cm_string->js_redirect('Đổi lớp thành công', 'hocvien/home/danhsachbuoihoc/');
    }

    public function receive_survey()
    {
        $this->load->model('survey');
        $this->survey->insert($this->input->post());
        echo "<div><h2 class='text-center'><i class='fa fa-check fa-4x'></i>Bạn đã hoàn thành survey, bấm <strong>next</strong> để nộp bài giữa kì</h2> </div>";
    }

    public function receive_survey_ck()
    {
        $this->load->model('survey');
        $post_data = $this->input->post();
        $studentid = $post_data['studentid'];
        $gen = $this->survey->get_gen_student($studentid);
        unset($post_data['studentid']);
        $content = json_encode($post_data);

        $data = array(
            'studentid' => $studentid,
            'gen' => $gen,
            'content' => $content
        );
        $this->survey->insert_survey($data);
        echo "<div><h2 class='text-center'><i class='fa fa-check fa-4x'></i>Bạn đã hoàn thành survey, bấm <strong>next</strong> để nộp bài giữa kì</h2> </div>";
    }

    public function ajax_load_more_bt($offset)
    {
        $this->load->model('hocvien');
        $this->data['bai_tap_hoc_viens'] = $this->hocvien->get_all_bai_tap($offset, 10);
        $this->load->view('hocvien/ajax/ajax_load_more_bt', isset($this->data) ? $this->data : NULL);
    }

    public function nop_bai_ck()
    {
        error_reporting(E_ERROR | E_PARSE);
        $this->load->model('survey');
//        dd($targetPath = getcwd() . '/public/resources/baitaphocvien/');
        if (!empty($_FILES)) {
            $tempFile = $_FILES['file']['tmp_name'];
            $fileName = time() . $this->cm_string->random(10, true) . $_FILES['file']['name'];
            $targetPath = getcwd() . '/public/resources/baitaphocvien/';
            $targetFile = $targetPath . $fileName;
//            $path = $this->db->select('baigiuaki')->from('regis')->where('studentid', $this->auth['id'])->get()->row_array()['baigiuaki'];
//            deleteFiles($path);
            move_uploaded_file($tempFile, $targetFile);
            $post = array(
                'source' => '/public/resources/baitaphocvien/' . $fileName,
                'studentid' => $this->auth['id'],
                'date' => $this->cm_string->get_current_time(),
                'lectureOrder' => 8,
                'description' => ""
            );
            $id = $this->survey->insert_post($post);
            //Image Resizing
            $config['source_image'] = $targetFile;
            $config['maintain_ratio'] = TRUE;
            $config['width'] = 1000;
            $config['height'] = 1000;
            $this->load->library('image_lib', $config);
            $this->image_lib->resize();
            echo
                "
            <div class='btn-group-xoa' id='$id'style='position: relative;margin-left:10px;margin-top:10px;top:48px;'>
            <button type='button' class='btn btn-primary' style='float: left' data-toggle='collapse' data-target='#btn-xac-nhan-$id'>Xóa</button>


                    <div id='btn-xac-nhan-$id' class='collapse'>

                        <button class='btn btn-danger' onclick='xoaBaiCk($id)'>Xác nhận</button>
                    </div>


            </div>
            <img
             id='img$id'
            src='" . base_url('/public/resources/baitaphocvien/' . $fileName) . "'
                     style='width:100%;margin-bottom:5px;'/>
            ";

        }
    }

    public function ajax_xoa_post()
    {
        $this->load->model('survey');
        $id = $this->input->post('id');
        $source = $this->survey->get_post($id)['source'];
        deleteFiles($source);
        $this->survey->delete_post($id);
    }

    public function nop_baigiuaki()
    {
        error_reporting(E_ERROR | E_PARSE);
        $this->load->model('survey');
//        dd($targetPath = getcwd() . '/public/resources/baitaphocvien/');
        if (!empty($_FILES)) {
            $tempFile = $_FILES['file']['tmp_name'];
            $fileName = time() . $this->cm_string->random(10, true) . $_FILES['file']['name'];
            $targetPath = getcwd() . '/public/resources/baitaphocvien/';
            $targetFile = $targetPath . $fileName;
            $data = $this->db->select('id,source')->from('post')->where(array('studentid' => $this->auth['id'], 'lectureOrder' => 4))->get()->row_array();
            $path = $data['source'];
            deleteFiles($path);
            $this->survey->delete_post($data['id']);
            move_uploaded_file($tempFile, $targetFile);
            $post = array(
                'source' => '/public/resources/baitaphocvien/' . $fileName,
                'studentid' => $this->auth['id'],
                'date' => $this->cm_string->get_current_time(),
                'lectureOrder' => 4,
                'description' => ""
            );
            $id = $this->survey->insert_post($post);
            //Image Resizing
            $config['source_image'] = $targetFile;
            $config['maintain_ratio'] = TRUE;
            $config['width'] = 1000;
            $config['height'] = 1000;
            $this->load->library('image_lib', $config);
            $this->image_lib->resize();
            echo '<img src="' . base_url('/public/resources/baitaphocvien/' . $fileName) . '" style="width:100%"/>';

// if you want to save in db,where here
// with out model just for example
// $this->load->database(); // load database
// $this->db->insert('file_table',array('file_name' => $fileName));
        }
    }

//        $message = "Wrong method request";
//        if ($this->input->post('submit')) {
//
//
//            $this->load->model('survey');
//            $config['file_name'] = time() . $this->cm_string->random();
//            $config['upload_path'] = './public/resources/baitaphocvien';
//            $config['allowed_types'] = 'gif|jpg|png';
////            $config['max_size'] = '5120';
//
//            $config['remove_spaces'] = TRUE;
//            $this->load->library('upload', $config);
//
//            if (!empty($_FILES['userfile']['name'])) {
//                if (!$this->upload->do_upload()) {
//                    $message = "<div class='alert alert-danger text-center'>" . $this->upload->display_errors() . "</div>";
//                } else {
//
//                    $data = array('upload_data' => $this->upload->data());
//                    $baigiuaki="public/resources/baitaphocvien/" . $data['upload_data']['file_name'];
//
//                    $path = $this->db->select('baigiuaki')->from('regis')->where('studentid', $this->auth['id'])->get()->row_array()['baigiuaki'];
//                    deleteFiles($path);
//                    //Image Resizing
//                    $config['source_image'] = $this->upload->upload_path.$this->upload->file_name;
//                    $config['maintain_ratio'] = TRUE;
//                    $config['width'] = 1000;
//                    $config['height'] = 1000;
//
//                    $this->load->library('image_lib', $config);
//
//                    $this->image_lib->resize();
//
//                    $this->survey->nop_baigiuaki($baigiuaki, $this->auth['id']);
//                    $message = "<div class='alert alert-success text-center'><p>Nộp bài thành công</p></div>";
//                    $message.="<div><img src='$baigiuaki' style='width: 75%;'/></div>";
//                }
//            } else {
//                $message = "<div class='alert alert-danger text-center'>Bạn chưa chọn ảnh để up lên</div>";
//            }
//        }
//        echo $message;

}
