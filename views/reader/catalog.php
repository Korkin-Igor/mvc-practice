<section class="surface">
    <div class="section-head">
        <div>
            <p class="eyebrow">Кабинет читателя</p>
            <h1>Каталог</h1>
        </div>
        <p class="section-note">Можно отправить заявку на бронирование экземпляра и сразу открыть электронную версию, если она есть.</p>
    </div>

    <form class="toolbar" method="get">
        <label class="search-field">
            <span class="search-field__icon">⌕</span>
            <input type="search" name="q" value="<?= htmlspecialchars($catalogSearch ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?>" placeholder="Поиск по названию, автору или описанию...">
        </label>
        <button class="button button--ghost" type="submit">Найти</button>
    </form>

    <div class="catalog-grid">
        <?php foreach ($catalogBooks as $book): ?>
            <article class="book-card">
                <div class="book-card__cover">
                    <?php if (!empty($book['cover_url'])): ?>
                        <img src="<?= app()->route->getUrl($book['cover_url']) ?>" alt="<?= htmlspecialchars($book['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?>">
                    <?php else: ?>
                        <span><?= htmlspecialchars(mb_substr($book['name'], 0, 1, 'UTF-8'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?></span>
                    <?php endif; ?>
                </div>
                <div class="book-card__content">
                    <div class="book-card__meta">
                        <span class="pill"><?= (int) $book['available_copies'] ?> в зале</span>
                    </div>
                    <h3><?= htmlspecialchars($book['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?></h3>
                    <p class="muted"><?= htmlspecialchars($book['author'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?></p>
                    <p><?= htmlspecialchars($book['description'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?></p>
                    <div class="book-card__actions">
                        <?php if (!empty($book['link'])): ?>
                            <a class="button button--small" href="<?= htmlspecialchars($book['link'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?>" target="_blank" rel="noreferrer">Читать онлайн</a>
                        <?php else: ?>
                            <span class="button button--small button--disabled">Нет онлайн-версии</span>
                        <?php endif; ?>

                        <?php if (!empty($book['can_reserve'])): ?>
                            <form method="post" action="<?= app()->route->getUrl('/catalog/' . $book['id'] . '/reserve') ?>">
                                <input name="csrf_token" type="hidden" value="<?= app()->auth::generateCSRF() ?>">
                                <button class="button button--small button--ghost" type="submit">
                                    <?= htmlspecialchars($book['reserve_label'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?>
                                </button>
                            </form>
                        <?php else: ?>
                            <span class="button button--small button--ghost button--disabled">
                                <?= htmlspecialchars($book['reserve_label'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>

    <?php if (empty($catalogBooks)): ?>
        <div class="empty-state">
            <h3>Ничего не найдено</h3>
            <p>Попробуйте изменить поисковый запрос или проверьте, что книги загружены в базу данных.</p>
        </div>
    <?php endif; ?>
</section>
