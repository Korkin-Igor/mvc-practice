<?php
$labels = [
    'pending' => 'Ожидают подтверждения',
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
            <p class="eyebrow">Рабочее место библиотекаря</p>
            <h1>Брони</h1>
        </div>
    </div>

    <div class="stats-row">
        <article class="stat-card">
            <span>В ожидании</span>
            <strong><?= (int) $bookingStats['pending'] ?></strong>
        </article>
        <article class="stat-card">
            <span>Подтверждённые</span>
            <strong><?= (int) $bookingStats['approved'] ?></strong>
        </article>
        <article class="stat-card">
            <span>Завершённые</span>
            <strong><?= (int) $bookingStats['completed'] ?></strong>
        </article>
    </div>

    <?php foreach (['pending', 'overdue', 'active', 'completed'] as $groupName): ?>
        <?php if (empty($bookingGroups[$groupName])) continue; ?>
        <section class="booking-section booking-section--<?= $groupName ?>">
            <h2><?= $labels[$groupName] ?></h2>
            <div class="booking-list">
                <?php foreach ($bookingGroups[$groupName] as $item): ?>
                    <article class="booking-card">
                        <div class="booking-card__main">
                            <strong><?= htmlspecialchars($item['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?></strong>
                            <span><?= htmlspecialchars($item['author'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?></span>
                            <small><?= htmlspecialchars($item['reader_name'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?></small>
                        </div>
                        <div class="booking-card__meta">
                            <span><?= htmlspecialchars($item['hint'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?></span>
                            <small>С <?= htmlspecialchars($item['created_at'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?> до <?= htmlspecialchars($item['due_date'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?></small>
                        </div>
                        <div class="booking-card__actions booking-card__actions--stack">
                            <?php if (!empty($item['can_approve'])): ?>
                                <form method="post" action="<?= app()->route->getUrl('/bookings/' . $item['id'] . '/approve') ?>">
                                    <input name="csrf_token" type="hidden" value="<?= app()->auth::generateCSRF() ?>">
                                    <button class="button button--small" type="submit">Подтвердить</button>
                                </form>
                            <?php endif; ?>

                            <?php if (!empty($item['can_reject'])): ?>
                                <form method="post" action="<?= app()->route->getUrl('/bookings/' . $item['id'] . '/reject') ?>">
                                    <input name="csrf_token" type="hidden" value="<?= app()->auth::generateCSRF() ?>">
                                    <button class="button button--small button--ghost" type="submit">Отказать</button>
                                </form>
                            <?php endif; ?>

                            <?php if (!empty($item['can_return'])): ?>
                                <form method="post" action="<?= app()->route->getUrl('/bookings/' . $item['id'] . '/return') ?>">
                                    <input name="csrf_token" type="hidden" value="<?= app()->auth::generateCSRF() ?>">
                                    <button class="button button--small" type="submit">Вернуть</button>
                                </form>
                            <?php endif; ?>

                            <?php if (empty($item['can_approve']) && empty($item['can_reject']) && empty($item['can_return'])): ?>
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
            <p>Когда в базе появятся заявки и выдачи, они отобразятся в этом разделе.</p>
        </div>
    <?php endif; ?>
</section>
