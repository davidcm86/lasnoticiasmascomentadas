<?php
/**
  * @var \App\View\AppView $this
  */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('Categorias Periodicos'), ['action' => 'index']) ?></li>
    </ul>
</nav>
<div class="categoriasPeriodicos form large-9 medium-8 columns content">
    <?= $this->Form->create($categoriasPeriodico) ?>
    <fieldset>
        <legend><?= __('Add Categorias Periodico') ?></legend>
        <?php
            echo $this->Form->control('nombre');
            echo $this->Form->control('slug');
            echo $this->Form->control('periodico_id', ['options' => $periodicos]);
            echo $this->Form->control('categoria_id', ['options' => $categorias]);
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
