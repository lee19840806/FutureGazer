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
}
















