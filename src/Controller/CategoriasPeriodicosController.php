<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * CategoriasPeriodicos Controller
 *
 * @property \App\Model\Table\CategoriasPeriodicosTable $CategoriasPeriodicos
 *
 * @method \App\Model\Entity\CategoriasPeriodico[] paginate($object = null, array $settings = [])
 */
class CategoriasPeriodicosController extends AppController
{
    public function initialize() {
        parent::initialize();
        $this->viewBuilder()->layout('admin');
    }
    /**
     * Index method
     *
     * @return \Cake\Http\Response|void
     */
    public function index()
    {
        $this->paginate = [
            'contain' => ['Periodicos']
        ];
        $categoriasPeriodicos = $this->paginate($this->CategoriasPeriodicos);

        $this->set(compact('categoriasPeriodicos'));
        $this->set('_serialize', ['categoriasPeriodicos']);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $categoriasPeriodico = $this->CategoriasPeriodicos->newEntity();
        if ($this->request->is('post')) {
            $categoriasPeriodico = $this->CategoriasPeriodicos->patchEntity($categoriasPeriodico, $this->request->getData());
            if ($this->CategoriasPeriodicos->save($categoriasPeriodico)) {
                $this->Flash->success(__('The categorias periodico has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The categorias periodico could not be saved. Please, try again.'));
        }
        $periodicos = $this->CategoriasPeriodicos->Periodicos->find('list', 
            [
                'keyField' => 'id',
                'valueField' => 'nombre',
                'limit' => 200,
            ]  
        );
        $this->loadModel('Categorias');
        $categorias = $this->Categorias->find('list', 
            [
                'keyField' => 'id',
                'valueField' => 'nombre',
                'limit' => 200,
            ]  
        );
        $this->set(compact('categoriasPeriodico', 'periodicos', 'categorias'));
        $this->set('_serialize', ['categoriasPeriodico']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Categorias Periodico id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $categoriasPeriodico = $this->CategoriasPeriodicos->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $categoriasPeriodico = $this->CategoriasPeriodicos->patchEntity($categoriasPeriodico, $this->request->getData());
            $this->log($categoriasPeriodico);
            if ($this->CategoriasPeriodicos->save($categoriasPeriodico)) {
                $this->Flash->success(__('The categorias periodico has been saved.'));

                //return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The categorias periodico could not be saved. Please, try again.'));
        }
        $periodicos = $this->CategoriasPeriodicos->Periodicos->find('list', 
            [
                'keyField' => 'id',
                'valueField' => 'nombre',
                'limit' => 200,
            ]  
        );
        $this->loadModel('Categorias');
        $categorias = $this->Categorias->find('list', 
            [
                'keyField' => 'id',
                'valueField' => 'nombre',
                'limit' => 200,
            ]  
        );
        $this->set(compact('categoriasPeriodico', 'periodicos', 'categorias'));
        $this->set('_serialize', ['categoriasPeriodico']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Categorias Periodico id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $categoriasPeriodico = $this->CategoriasPeriodicos->get($id);
        if ($this->CategoriasPeriodicos->delete($categoriasPeriodico)) {
            $this->Flash->success(__('The categorias periodico has been deleted.'));
        } else {
            $this->Flash->error(__('The categorias periodico could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
