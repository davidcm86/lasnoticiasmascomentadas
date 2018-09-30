<?php
/**
  * @var \App\View\AppView $this
  * @var \App\Model\Entity\CategoriasPeriodico[]|\Cake\Collection\CollectionInterface $categoriasPeriodicos
  */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('Periodicos'), ['controller' => 'Periodicos', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('Categorias'), ['controller' => 'Categorias', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('Añadir Categoria Periodico'), ['controller' => 'categorias_periodicos', 'action' => 'add']) ?></li>
    </ul>
</nav>
<div class="categoriasPeriodicos index large-9 medium-8 columns content">
    <h3><?= __('Categorias Periodicos') ?></h3>
    <table cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th scope="col"><?= $this->Paginator->sort('id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('nombre') ?></th>
                <th scope="col"><?= $this->Paginator->sort('slug') ?></th>
                <th scope="col"><?= $this->Paginator->sort('periodico_id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('modified') ?></th>
                <th scope="col"><?= $this->Paginator->sort('created') ?></th>
                <th scope="col" class="actions"><?= __('Actions') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categoriasPeriodicos as $categoriasPeriodico): ?>
            <tr>
                <td><?= $this->Number->format($categoriasPeriodico->id) ?></td>
                <td><?= h($categoriasPeriodico->nombre) ?></td>
                <td><?= h($categoriasPeriodico->slug) ?></td>
                <td><?= h($categoriasPeriodico->periodico->nombre) ?></td>
                <td><?= h($categoriasPeriodico->modified) ?></td>
                <td><?= h($categoriasPeriodico->created) ?></td>
                <td class="actions">
                    <?= $this->Html->link(__('Editar'), ['action' => 'edit', $categoriasPeriodico->id]) ?>
                    <?= $this->Form->postLink(__('Borrar'), ['action' => 'delete', $categoriasPeriodico->id], ['confirm' => __('Are you sure you want to delete # {0}?', $categoriasPeriodico->id)]) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="paginator">
        <ul class="pagination">
            <?= $this->Paginator->first('<< ' . __('first')) ?>
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
            <?= $this->Paginator->last(__('last') . ' >>') ?>
        </ul>
        <p><?= $this->Paginator->counter(['format' => __('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')]) ?></p>
    </div>
</div>
