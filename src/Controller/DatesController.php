<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Collection\Collection;

/**
 * Dates Controller
 *
 * @property \App\Model\Table\FilesTable $Files
 */
class DatesController extends AppController
{
    public function index()
    {
        $this->loadModel('Files');
        
        $this->paginate = ['contain' => ['Users']];
        $this->set('files', $this->paginate($this->Files));
        $this->set('_serialize', ['files']);
    }
}
















