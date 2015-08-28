<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * Files Controller
 *
 * @property \App\Model\Table\FilesTable $Files
 */
class FilesController extends AppController
{
    public function list_files()
    {
        $this->paginate = ['contain' => ['Users']];
        $this->set('files', $this->paginate($this->Files));
        $this->set('_serialize', ['files']);
    }
    
    public function upload()
    {
        
    }
    
    public function submit()
    {
        if ($this->request->is('post'))
        {
            $existed = $this->Files->find()
                ->where(['user_id' => $this->Auth->user('id'), 'name' => h($this->request->data['userfile']['name'])])
                ->first();
    
            if ($existed == null)
            {
                $this->Files->saveFile($this->request->data['userfile']['tmp_name'], h($this->request->data['userfile']['name']), $this->Auth->user('id'));;
            }
            else
            {
                $this->Flash->set('A file with the same name existed. Rename your file and upload agian.', ['element' => 'alert']);
            }
    
            $this->redirect(['controller' => 'Files', 'action' => 'index']);
        }
    }
    
    public function delete($id = NULL)
    {
        $this->request->allowMethod(['post', 'delete']);
    
        if ($this->Files->isOwnedBy($id, $this->Auth->user('id')))
        {
            if ($this->Files->deleteFile($id, $this->Auth->user('id')))
            {
                $this->Flash->success('The file has been deleted.');
            }
            else
            {
                $this->Flash->error('The file could not be deleted. Please try again.');
            }
        }
        else
        {
            $this->Flash->error('You are not the owner of this file.');
        }
    
        $this->redirect(['controller' => 'Files', 'action' => 'index']);
    }
}
