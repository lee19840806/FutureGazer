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
    public function beforeFilter(\Cake\Event\Event $event)
    {
        parent::beforeFilter($event);
        $this->Auth->allow(['login', 'register']);
    }
    
    public function register($username = null)
    {
        $this->set('username', $username);
        
        if ($this->request->is('post'))
        {
            if ($this->request->data['password'] != $this->request->data['passwordConfirm'])
            {
                $this->Flash->set('Please make sure the 2 passwords are matched', ['element' => 'alert']);
                return $this->redirect(['action' => 'register', $this->request->data['username']]);
            }
            
            $userInfo = array_merge($this->request->data, array('role' => 'basic'));
            
            $user = $this->Users->newEntity();
            $user = $this->Users->patchEntity($user, $userInfo);
            
            if ($this->Users->save($user))
            {
                $this->Flash->set('The new user has been registered successfully, please sign in.', ['element' => 'success']);
                return $this->redirect(['action' => 'login']);
            }
            else
            {
                $this->Flash->set('The user could not be saved. Please try again.', ['element' => 'error']);
                return $this->redirect(['action' => 'register']);
            }
        }
    }

    public function login()
    {
        if ($this->request->is('post'))
        {
            $user = $this->Auth->identify();
            
            if ($user)
            {
                $this->Auth->setUser($user);
                $this->request->session()->write('Users.username', $this->Auth->user('username'));
                return $this->redirect($this->Auth->redirectUrl());
            }
            
            $this->Flash->set('Your username or password is incorrect', ['element' => 'alert']);
        }
    }
    
    public function logout()
    {
        $this->request->session()->destroy();
        return $this->redirect($this->Auth->logout());
    }
}
