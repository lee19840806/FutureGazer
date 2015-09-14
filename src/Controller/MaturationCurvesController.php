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
            $segmentVariables = ($this->request->data['segmentVariables'] == null) ? array() : $this->request->data['segmentVariables'];
            
            if (in_array($this->request->data['origination'], $segmentVariables)
                or in_array($this->request->data['chargeOff'], $segmentVariables)
                or in_array($this->request->data['MoB'], $segmentVariables))
            {
                $this->Flash->alert('Segment variables cannot be selected as one of maturation curve variables, please choose proper variables.');
                $this->redirect(['action' => 'build', $this->request->data['fileID']]);
                return;
            }
            
            $this->loadModel('Files');
        
            if ($this->Files->isOwnedBy($this->request->data['fileID'], $this->Auth->user('id')))
            {
                $maturationCurves = $this->Files->buildCurves($this->request->data, $this->Auth->user('id'));
                $this->set('maturationCurves', json_encode($maturationCurves));
                $this->set('columnHeaders', json_encode(array_keys($maturationCurves[0])));
            }
            else
            {
                $this->Flash->error('You are not the owner of this file.');
                $this->redirect(['action' => 'list_files']);
            }
        }
    }
}
