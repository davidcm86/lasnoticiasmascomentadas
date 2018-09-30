<?php
/**
  * @var \App\View\AppView $this
  * @var \App\Model\Entity\Periodico[]|\Cake\Collection\CollectionInterface $periodicos
  */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('Añadir periodico'), ['action' => 'add']) ?></li>
        <li><?= $this->Html->link(__('Categorias'), ['controller' => 'categorias', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('Categorias Periodicos'), ['controller' => 'categorias_periodicos', 'action' => 'index']) ?></li>
    </ul>
</nav>
<div class="periodicos index large-9 medium-8 columns content">
    <h3><?= __('Periodicos') ?></h3>
    <table cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th scope="col"><?= $this->Paginator->sort('id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('nombre') ?></th>
                <th scope="col"><?= $this->Paginator->sort('activo') ?></th>
                <th scope="col"><?= $this->Paginator->sort('imagen') ?></th>
                <th scope="col"><?= $this->Paginator->sort('modified') ?></th>
                <th scope="col"><?= $this->Paginator->sort('created') ?></th>
                <th scope="col" class="actions"><?= __('Actions') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($periodicos as $periodico): ?>
            <tr>
                <td><?= $this->Number->format($periodico->id) ?></td>
                <td><?= h($periodico->nombre) ?></td>
                <td><?= h($periodico->activo) ?></td>
                <td><?= h($periodico->imagen) ?></td>
                <td><?= h($periodico->modified) ?></td>
                <td><?= h($periodico->created) ?></td>
                <td class="actions">
                    <?= $this->Html->link(__('Editar'), ['action' => 'edit', $periodico->id]) ?>
                    <?= $this->Form->postLink(__('Borrar'), ['action' => 'delete', $periodico->id], ['confirm' => __('Are you sure you want to delete # {0}?', $periodico->id)]) ?>
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
