<?php
namespace App\Controller;

use App\Controller\AppController;


/**
 * Periodicos Controller
 *
 * @property \App\Model\Table\PeriodicosTable $Periodicos
 *
 * @method \App\Model\Entity\Periodico[] paginate($object = null, array $settings = [])
 */
class PeriodicosController extends AppController
{
    public function initialize() {
        parent::initialize();
        $this->viewBuilder()->layout('admin');
        $this->loadComponent('FuncionesGenerales');
    }
    
    /**
     * Index method
     *
     * @return \Cake\Http\Response|void
     */
    public function index()
    {
        $periodicos = $this->paginate($this->Periodicos);

        $this->set(compact('periodicos'));
        $this->set('_serialize', ['periodicos']);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $periodico = $this->Periodicos->newEntity();
        if ($this->request->is('post')) {
            if (isset($this->request->data['imagen']['tmp_name']) && !empty($this->request->data['imagen']['tmp_name'])) {
                $this->request->data['imagen'] = $this->FuncionesGenerales->guardarImagen($this->request->data['imagen']['tmp_name'], $this->request->data['imagen']['name'], 'img/periodicos');
            }
            $periodico = $this->Periodicos->patchEntity($periodico, $this->request->getData());
            if ($this->Periodicos->save($periodico)) {
                $this->Flash->success(__('The periodico has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The periodico could not be saved. Please, try again.'));
        }
        $this->loadModel('Dominios');
        $dominios = $this->Dominios->find('list', 
            [
                'keyField' => 'id',
                'valueField' => 'pais',
                'limit' => 200,
            ]  
        );
        $this->set(compact('periodico', 'dominios'));
        $this->set('_serialize', ['periodico']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Periodico id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $periodico = $this->Periodicos->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            if (isset($this->request->data['imagen']['tmp_name']) && !empty($this->request->data['imagen']['tmp_name'])) {
                $this->request->data['imagen'] = $this->FuncionesGenerales->guardarImagen($this->request->data['imagen']['tmp_name'], $this->request->data['imagen']['name'], 'img/periodicos', $id);
            } else {
                // quitamos la imagen para que no lo meta vacio
                unset($this->request->data['imagen']);
            }
            $periodico = $this->Periodicos->patchEntity($periodico, $this->request->getData());
            if ($this->Periodicos->save($periodico)) {
                $this->Flash->success(__('The periodico has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The periodico could not be saved. Please, try again.'));
        }
        $dominios = $this->Dominios->find('list', 
            [
                'keyField' => 'id',
                'valueField' => 'pais',
                'limit' => 200,
            ]  
        );
        $this->set(compact('periodico', 'dominios'));
        $this->set('_serialize', ['periodico']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Periodico id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $periodico = $this->Periodicos->get($id);
        if (isset($periodico->imagen) && !empty($periodico->imagen)) {
            if (file_exists(WWW_ROOT . $periodico->imagen)) {
                unlink(WWW_ROOT . $periodico->imagen);
            }
        }
        if ($this->Periodicos->delete($periodico)) {
            $this->Flash->success(__('The periodico has been deleted.'));
        } else {
            $this->Flash->error(__('The periodico could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
