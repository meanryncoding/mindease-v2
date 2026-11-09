<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Appointment|null $next
 */
?>
<section class="card-soft" aria-labelledby="session-title">
    <h2 id="session-title" class="h6 fw-semibold mb-3"><?= __('Your next session') ?></h2>

    <?php if ($next === null) : ?>
        <p class="small text-muted mb-3"><?= __('You have no upcoming session. Taking the first step is easy.') ?></p>
        <a href="<?= $this->Url->build('/student/book') ?>" class="btn btn-me w-100 btn-sm"><?= __('Book a session') ?></a>
    <?php else :
        $words = preg_split('/\s+/', trim((string)$next->counselor->name)) ?: [];
        $initials = '';
        foreach (array_slice($words, 0, 2) as $word) {
            $initials .= mb_strtoupper(mb_substr($word, 0, 1));
        }
        $start = $next->requested_start; ?>
        <div class="d-flex align-items-center gap-2 mb-3">
            <span class="avatar" aria-hidden="true"><?= h($initials) ?></span>
            <div class="small lh-sm">
                <div class="fw-semibold"><?= h($next->counselor->name) ?></div>
                <div class="text-muted"><?= h((string)$next->counselor->specialisation) ?></div>
            </div>
        </div>
        <p class="small mb-1"><?= h($start->i18nFormat('EEE, dd MMM y') . ' · ' . $start->i18nFormat('h:mm a')) ?></p>
        <p class="small text-muted mb-2"><?= h($next->modeLabel()) ?></p>
        <p class="mb-3"><span class="<?= h($next->status->chipClass()) ?>"><?= h($next->status->label()) ?></span></p>
        <a href="<?= $this->Url->build('/student/sessions/' . $next->id) ?>" class="btn btn-outline-secondary w-100 btn-sm"><?= __('View appointment') ?></a>
    <?php endif; ?>
</section>