<?php $this->assign('title', 'Admin'); ?>
<h1 class="h3">Admin dashboard</h1>
<p class="text-muted">Hello, <?= h($this->Identity->get('name')) ?>. The real dashboard comes next.</p>
<?= $this->Html->link('Sign out', '/logout', ['class' => 'btn btn-outline-secondary btn-sm']) ?>