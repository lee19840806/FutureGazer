<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * MaturationCurves Controller
 *
 * @property \App\Model\Table\FilesTable $Files
 */
class MaturationCurvesController extends AppController
{
    public function list_files()
    {
        $this->loadModel('Files');
        
        $this->paginate = ['contain' => ['Users']];
        $this->set('files', $this->paginate($this->Files));
        $this->set('_serialize', ['files']);
    }
    
    public function build($id = NULL)
    {
        $this->loadModel('Files');
        
        if ($this->Files->isOwnedBy($id, $this->Auth->user('id')))
        {
            $file = $this->Files->viewFileContent($id, $this->Auth->user('id'));
            $this->set('file', json_encode($file));
        }
        else
        {
            $this->Flash->error('You are not the owner of this file.');
            $this->redirect(['action' => 'list_files']);
        }
    }
}
