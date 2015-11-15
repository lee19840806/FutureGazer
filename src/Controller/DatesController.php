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
    	
    }
    
	public function connect()
    {
        $this->loadModel('Files');
        $fileNames = $this->Files->find('list')->select(['name'])->where(['Files.user_id' => $this->Auth->user('id')])->all()->toArray();
        $this->set('fileNames', $fileNames);
    }
}
















