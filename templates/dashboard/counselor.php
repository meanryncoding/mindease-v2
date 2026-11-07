<?php $this->assign('title', 'Counselor'); ?>
<h1 class="h3">Counselor dashboard</h1>
<p class="text-muted">Hello, <?= h($this->Identity->get('name')) ?>. The real dashboard comes next.</p>
<?= $this->Html->link('Sign out', '/logout', ['class' => 'btn btn-outline-secondary btn-sm']) ?>