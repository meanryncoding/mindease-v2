<?php
/**
 * Language switch (English / BM). Uses the $lang view variable set in AppController.
 *
 * @var \App\View\AppView $this
 */
$current = $lang ?? 'en';
?>
<span class="lang-switch" role="group" aria-label="<?= __('Language') ?>">
    <a href="<?= $this->Url->build('/lang/en') ?>"
       class="lang-link<?= $current === 'en' ? ' lang-link-active' : '' ?>"
       lang="en" hreflang="en" title="English"<?= $current === 'en' ? ' aria-current="true"' : '' ?>>English</a>
    /
    <a href="<?= $this->Url->build('/lang/ms') ?>"
       class="lang-link<?= $current === 'ms' ? ' lang-link-active' : '' ?>"
       lang="ms" hreflang="ms" title="Bahasa Melayu"<?= $current === 'ms' ? ' aria-current="true"' : '' ?>>BM</a>
</span>