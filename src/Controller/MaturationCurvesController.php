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
            $fields = $this->Files->getFields($id, $this->Auth->user('id'));
            $this->set('fields', $fields);
        }
        else
        {
            $this->Flash->error('You are not the owner of this file.');
            $this->redirect(['action' => 'list_files']);
        }
    }
    
    public function calculate()
    {
        if ($this->request->is('post'))
        {
            if (in_array($this->request->data['origination'], $this->request->data['segmentVariables'])
                or in_array($this->request->data['chargeOff'], $this->request->data['segmentVariables'])
                or in_array($this->request->data['MoB'], $this->request->data['segmentVariables']))
            {
                $this->Flash->alert('Segment variables cannot be selected as one of maturation curve variables, please choose proper variables.');
                $this->redirect(['action' => 'build', $this->request->data['fileID']]);
                return;
            }
            
            if (count($this->request->data['segmentVariables']) == 0)
            {
                $this->Flash->alert('Please select at least one field as segment variable.');
                $this->redirect(['action' => 'build', $this->request->data['fileID']]);
                return;
            }
            
            $this->loadModel('Files');
        
            if ($this->Files->isOwnedBy($this->request->data['fileID'], $this->Auth->user('id')))
            {
                $fields = $this->Files->buildCurves($this->request->data, $this->Auth->user('id'));
                $this->set('fields', $fields);
            }
            else
            {
                $this->Flash->error('You are not the owner of this file.');
                $this->redirect(['action' => 'list_files']);
            }
        }
    }
}
