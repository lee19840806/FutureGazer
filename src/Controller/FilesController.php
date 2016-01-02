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
    
    public function upload_file()
    {
    
    }
    
    public function submitFile()
    {
        if ($this->request->is('post'))
        {
            $existed = $this->Files->find()
            ->where(['user_id' => $this->Auth->user('id'), 'name' => h($this->request->data['userfile']['name'])])
            ->first();
    
            if ($existed == null)
            {
                $saveResult = $this->Files->saveFileToMySQL($this->request->data['userfile']['tmp_name'], h($this->request->data['userfile']['name']),
                    $this->request->data['dataType'], json_decode($this->request->data['csvMeta'], true), $this->Auth->user('id'));
                
                if ($saveResult)
                {
                    $this->Flash->set('The file has been uploaded successfully.', ['element' => 'success']);
                    $this->redirect(['action' => 'list_files']);
                }
                else
                {
                    $this->Flash->set('An error occured during file upload. Please upload the file again.', ['element' => 'alert']);
                    $this->redirect(['action' => 'upload']);
                }
            }
            else
            {
                $this->Flash->set('A file with the same name existed. Rename your file and upload agian.', ['element' => 'alert']);
                $this->redirect(['action' => 'upload']);
            }
        }
    }
    
    public function view($id = NULL)
    {
        if ($this->Files->isOwnedBy($id, $this->Auth->user('id')))
        {
            $file = $this->Files->find()->where(['Files.id' => $id])->contain(['FileFields', 'FileContents'])->first()->toArray();
            $this->set('fields', json_encode($file['file_fields']));
            $this->set('file', $file);
        }
        else
        {
            $this->Flash->error('You are not the owner of this file.');
            $this->redirect(['action' => 'list_files']);
        }
    }
    
    public function name_available()
    {
        $this->request->allowMethod(['post']);
        $this->layout = 'ajax';
        
        $fileName = $this->Files->find()->where(['Files.user_id' => $this->Auth->user('id'), 'Files.name' => $this->request->data['fileName']])->first();
        
        if ($fileName == null)
        {
            $this->set('result', 1);
        }
        else
        {
            $this->set('result', 0);
        }
    }
    
    public function client_save_data()
    {
        $this->request->allowMethod(['post']);
        $this->layout = 'ajax';
        
        $fieldsEntities = [];
        
        foreach (json_decode($this->request->data['fileFields'], true) as $value)
        {
            array_push($fieldsEntities, $this->Files->FileFields->newEntity(
            	array('indx' => h($value['indx']), 'name' => h($value['name']), 'type' => h($value['type']))));
        }
        
        $file = $this->Files->newEntity();
        $file->user_id = $this->Auth->user('id');
        $file->name = h($this->request->data['fileName']);
        $file->file_fields = $fieldsEntities;
        $file->file_content = $this->Files->FileContents->newEntity(array('content' => $this->request->data['fileContent']));
        
        if ($this->Files->save($file))
        {
            $this->set('result', 1);
        }
        else
        {
            $this->set('result', 0);
        }
    }
    
    public function save_compressed_data()
    {
    	$this->request->allowMethod(['post']);
    	$this->layout = 'ajax';
    	
    	$res = zip_open($this->request->data['zippedBinary']['tmp_name']);
    	$entry = zip_read($res);
    	$unzipped = zip_entry_read($entry, zip_entry_filesize($entry));
    	$file = json_decode($unzipped, true);
    	zip_close($res);
    	
    	$fieldsEntities = [];
    	
    	foreach ($file['fileFields'] as $value)
    	{
    		array_push($fieldsEntities, $this->Files->FileFields->newEntity(
    			array('indx' => h($value['indx']), 'name' => h($value['name']), 'type' => h($value['type']), 'format' => h($value['format']))));
    	}
    	
    	$f = $this->Files->newEntity();
    	$f->user_id = $this->Auth->user('id');
    	$f->name = h($file['fileName']);
    	$f->file_fields = $fieldsEntities;
    	$f->file_content = $this->Files->FileContents->newEntity(array('content' => $file['fileContent']));
    	
    	if ($this->Files->save($f))
    	{
    		$this->set('result', 1);
    	}
    	else
    	{
    		$this->set('result', 0);
    	}
    }
    
    public function ajax_get_file()
    {
    	$this->request->allowMethod(['post']);
    	$this->layout = 'ajax';
    
    	if (!$this->Files->isOwnedBy($this->request->data['file_id'], $this->Auth->user('id')))
    	{
    		$this->set('result', 'The file is not owned by the user');
    		return;
    	}
    	
    	$file = $this->Files->find()->where(['Files.id' => $this->request->data['file_id']])->contain(['FileFields', 'FileContents'])->first()->toArray();
    	$this->set('result', json_encode($file));
    }
    
    public function delete($id = NULL)
    {
        $this->request->allowMethod(['post', 'delete']);
    
        if ($this->Files->isOwnedBy($id, $this->Auth->user('id')))
        {
            $file = $this->Files->get($id);
            
            if ($this->Files->delete($file))
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
    
        $this->redirect(['action' => 'list_files']);
    }
}
