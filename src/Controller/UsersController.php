<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 */
class UsersController extends AppController
{

    /**
     * View method
     *
     * @param string|null $id User id.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $user = $this->Users->get($id, [
            'contain' => []
        ]);
        $this->set('user', $user);
        $this->set('_serialize', ['user']);
    }

    /**
     * Add method
     *
     * @return void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $user = $this->Users->newEntity();
        if ($this->request->is('post')) {
            $user = $this->Users->patchEntity($user, $this->request->data);
            if ($this->Users->save($user)) {
                $this->Flash->success(__('The user has been saved.'));
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The user could not be saved. Please, try again.'));
            }
        }
        $this->set(compact('user'));
        $this->set('_serialize', ['user']);
    }
    
    public function register()
    {
        if ($this->request->is('post'))
        {
            if ($this->request->data['password'] != $this->request->data['passwordConfirm'])
            {
                $this->Flash->set('Make sure the 2 passwords are matched', ['element' => 'alert']);
                return $this->redirect(['action' => 'add']);
            }
            
            $userInfo = array_merge($this->request->data, array('role' => 'basic'));
            
            $user = $this->Users->newEntity();
            $user = $this->Users->patchEntity($user, $userInfo);
            
            if ($this->Users->save($user))
            {
                $this->Flash->set('The user has been registered, please sign in.', ['element' => 'success']);
                return $this->redirect(['action' => 'login']);
            }
            else
            {
                $this->Flash->set('The user could not be saved. Please try again.', ['element' => 'error']);
                return $this->redirect(['action' => 'add']);
            }
        }
    }

    public function login()
    {
        
    }
}
