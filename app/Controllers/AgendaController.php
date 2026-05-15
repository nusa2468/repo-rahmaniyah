<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AgendaModel;

class AgendaController extends BaseController
{
    protected $agendaModel;

    public function __construct()
    {
        $this->agendaModel = new AgendaModel();
        helper('form');
    }

    public function index()
    {
        $data = [
            'title'          => 'Manajemen Agenda',
            'current_module' => 'humas',
            'agenda'         => $this->agendaModel->orderBy('tanggal_mulai', 'DESC')->findAll(),
        ];
        return view('humas/agenda/index', $data);
    }

    public function new()
    {
        $data = [
            'title'          => 'Buat Agenda Baru',
            'current_module' => 'humas',
        ];
        return view('humas/agenda/form', $data);
    }

    public function create()
    {
        if ($this->agendaModel->save($this->request->getPost())) {
            return redirect()->to('app/agenda')->with('success', 'Agenda berhasil ditambahkan.');
        } else {
            return redirect()->back()->withInput()->with('errors', $this->agendaModel->errors());
        }
    }

    public function edit($id = null)
    {
        $agenda = $this->agendaModel->find($id);
        if (!$agenda) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = [
            'title'          => 'Edit Agenda',
            'current_module' => 'humas',
            'agenda'         => $agenda,
        ];
        return view('humas/agenda/form', $data);
    }

    public function update($id = null)
    {
        if ($this->agendaModel->update($id, $this->request->getPost())) {
            return redirect()->to('app/agenda')->with('success', 'Agenda berhasil diperbarui.');
        } else {
            return redirect()->back()->withInput()->with('errors', $this->agendaModel->errors());
        }
    }

    public function delete($id = null)
    {
        if ($this->agendaModel->delete($id)) {
            return redirect()->to('app/agenda')->with('success', 'Agenda berhasil dihapus.');
        } else {
            return redirect()->to('app/agenda')->with('error', 'Gagal menghapus agenda.');
        }
    }
}

