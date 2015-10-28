<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Collection\Collection;

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
    
    public function build_maturation_curves($id = NULL)
    {
        $this->loadModel('Files');
    
        if ($this->Files->isOwnedBy($id, $this->Auth->user('id')))
        {
            $file = $this->Files->find()->where(['Files.id' => $id])->contain(['FileFields', 'FileContents'])->first()->toArray();
            
            $fieldsCollection = new Collection($file['file_fields']);
            $fields = $fieldsCollection->extract('name')->toArray();
            $disabledItems = $fieldsCollection->map(function ($value, $key) {
                return ($value['type'] == 'string') ? $key : null;
            })->toArray();
            
            $this->set('fields', $fields);
            $this->set('fieldsJSON', json_encode($file['file_fields']));
            $this->set('disabledItems', $disabledItems);
            $this->set('file', $file);
        }
        else
        {
            $this->Flash->error('You are not the owner of this file.');
            $this->redirect(['action' => 'list_files']);
        }
    }
    
    public function build($id = NULL)
    {
    	$this->loadModel('Files');
    
    	if ($this->Files->isOwnedBy($id, $this->Auth->user('id')))
    	{
    		$file = $this->Files->find()->where(['Files.id' => $id])->contain(['FileFields', 'FileContents'])->first()->toArray();
    
    		$fieldsCollection = new Collection($file['file_fields']);
    		$fields = $fieldsCollection->extract('name')->toArray();
    		$disabledItems = $fieldsCollection->map(function ($value, $key) {
    			return ($value['type'] == 'string') ? $key : null;
    		})->toArray();
    
    		$this->set('fields', $fields);
    		$this->set('fieldsJSON', json_encode($file['file_fields']));
    		$this->set('disabledItems', $disabledItems);
    		$this->set('file', $file);
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
                $maturation = $this->Files->buildCurves($this->request->data, $this->Auth->user('id'));
                $this->set('maturation', json_encode($maturation, JSON_NUMERIC_CHECK));
                $this->set('columnHeaders', json_encode(array_keys($maturation['curves'][0])));
            }
            else
            {
                $this->Flash->error('You are not the owner of this file.');
                $this->redirect(['action' => 'list_files']);
            }
        }
    }
}
















