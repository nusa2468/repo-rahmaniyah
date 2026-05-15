<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Elearning\ElearningCourseModel;
use App\Models\Elearning\ElearningMaterialModel;
use App\Models\Elearning\ElearningAssignmentModel;
use App\Models\Elearning\ElearningSubmissionModel;
use App\Models\Elearning\ElearningCommentModel;
use App\Models\Elearning\ElearningLogModel;

class BaseElearningController extends BaseController
{
    protected $db;
    protected $courseModel;
    protected $materialModel;
    protected $assignmentModel;
    protected $submissionModel;
    protected $commentModel;
    protected $logModel;
    protected $userData;

    /**
     * Konstruktor untuk inisialisasi semua Model yang dibutuhkan
     * oleh fitur-fitur Elearning secara terpusat.
     */
    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        // Jalankan initController milik BaseController
        parent::initController($request, $response, $logger);

        // Inisialisasi Database & Models
        $this->db              = \Config\Database::connect();
        $this->courseModel     = new ElearningCourseModel();
        $this->materialModel   = new ElearningMaterialModel();
        $this->assignmentModel = new ElearningAssignmentModel();
        $this->submissionModel = new ElearningSubmissionModel();
        $this->commentModel    = new ElearningCommentModel();
        $this->logModel        = new ElearningLogModel();

        // Ambil data user dari session secara global
        $this->userData = [
            'user_id'   => session()->get('id') ?? session()->get('user_id'),
            'role_name' => session()->get('role_name'),
            'nama'      => session()->get('nama_lengkap')
        ];
    }

    /**
     * Fungsi Global untuk mencatat log aktivitas Elearning ke database
     * * @param int|null $courseId
     * @param string $activityType
     * @param string $description
     */
    protected function recordLog($courseId, $activityType, $description)
    {
        try {
            $agent = $this->request->getUserAgent();
            
            $this->logModel->insert([
                'course_id'     => $courseId,
                'user_id'       => $this->userData['user_id'],
                'user_role'     => $this->userData['role_name'],
                'user_agent'    => $agent->getAgentString(),
                'activity_type' => $activityType,
                'description'   => $description,
                'ip_address'    => $this->request->getIPAddress(),
                'created_at'    => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            log_message('error', '[ElearningLog] Gagal mencatat log: ' . $e->getMessage());
        }
    }
}