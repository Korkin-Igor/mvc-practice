<?php
$labels = [
    'pending' => 'В ожидании',
    'overdue' => 'Просроченные',
    'active' => 'Активные',
    'completed' => 'Завершённые',
];
?>
<section class="surface">
    <?php
    $hasBookings = false;
    \Collect\collection($bookingGroups)->each(function (array $groupItems) use (&$hasBookings): void {
        if (\Collect\collection($groupItems)->count() > 0) {
            $hasBookings = true;
        }
    });
    ?>
    <div class="section-head">
        <div>
            <p class="eyebrow">Кабинет читателя</p>
            <h1>Мои брони</h1>
        </div>
    </div>

    <div class="stats-row">
        <article class="stat-card">
            <span>Активные</span>
            <strong><?= (int) $bookingStats['active'] ?></strong>
        </article>
        <article class="stat-card stat-card--danger">
            <span>Просроченные</span>
            <strong><?= (int) $bookingStats['overdue'] ?></strong>
        </article>
        <article class="stat-card">
            <span>В ожидании</span>
            <strong><?= (int) $bookingStats['pending'] ?></strong>
        </article>
    </div>

    <?php foreach (['overdue', 'active', 'pending', 'completed'] as $groupName): ?>
        <?php if (empty($bookingGroups[$groupName])) continue; ?>
        <section class="booking-section booking-section--<?= $groupName ?>">
            <h2><?= $labels[$groupName] ?></h2>
            <div class="booking-list">
                <?php foreach ($bookingGroups[$groupName] as $item): ?>
                    <article class="booking-card">
                        <div class="booking-card__main">
                            <strong><?= htmlspecialchars($item['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?></strong>
                            <span><?= htmlspecialchars($item['author'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?></span>
                        </div>
                        <div class="booking-card__meta">
                            <span><?= htmlspecialchars($item['hint'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?></span>
                            <small>Дата возврата <?= htmlspecialchars($item['due_date'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?></small>
                        </div>
                        <div class="booking-card__actions">
                            <?php if (!empty($item['can_extend'])): ?>
                                <form method="post" action="<?= app()->route->getUrl('/my-bookings/' . $item['id'] . '/extend') ?>">
                                    <input name="csrf_token" type="hidden" value="<?= app()->auth::generateCSRF() ?>">
                                    <button class="button button--small" type="submit">Продлить</button>
                                </form>
                            <?php else: ?>
                                <span class="badge"><?= htmlspecialchars($item['status'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?></span>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endforeach; ?>

    <?php if (!$hasBookings): ?>
        <div class="empty-state">
            <h3>Бронирований пока нет</h3>
            <p>Когда в базе появятся ваши заявки и выдачи, они отобразятся на этой странице.</p>
        </div>
    <?php endif; ?>
</section>
