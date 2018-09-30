<?php
/**
  * @var \App\View\AppView $this
  */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('Listado Periodicos'), ['action' => 'index']) ?></li>
    </ul>
</nav>
<div class="periodicos form large-9 medium-8 columns content">
    <?= $this->Form->create($periodico, ['type' => 'file']) ?>
    <fieldset>
        <legend><?= __('Editar Periodico') ?></legend>
        <?php
            echo $this->Form->control('nombre');
            echo $this->Form->control('slug');
            echo $this->Form->control('enlace');
            echo $this->Form->control('activo');
            echo $this->Form->control('dominio_id', ['options' => $dominios]);
            echo $this->Form->control('imagen', ['type' => 'file']);
            if (!empty($periodico->imagen)) {
                echo "<img src='$periodico->imagen'/>";
            }
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
