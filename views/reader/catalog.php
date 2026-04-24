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
            <input type="search" name="q" value="<?= htmlspecialchars($catalogSearch ?? '') ?>" placeholder="Поиск по названию, автору или описанию...">
        </label>
        <button class="button button--ghost" type="submit">Найти</button>
    </form>

    <div class="catalog-grid">
        <?php foreach ($catalogBooks as $book): ?>
            <article class="book-card">
                <div class="book-card__cover">
                    <span><?= htmlspecialchars(mb_substr($book['name'], 0, 1, 'UTF-8')) ?></span>
                </div>
                <div class="book-card__content">
                    <div class="book-card__meta">
                        <span class="pill"><?= (int) $book['available_copies'] ?> в зале</span>
                    </div>
                    <h3><?= htmlspecialchars($book['name']) ?></h3>
                    <p class="muted"><?= htmlspecialchars($book['author']) ?></p>
                    <p><?= htmlspecialchars($book['description']) ?></p>
                    <div class="book-card__actions">
                        <?php if (!empty($book['link'])): ?>
                            <a class="button button--small" href="<?= htmlspecialchars($book['link']) ?>" target="_blank" rel="noreferrer">Читать онлайн</a>
                        <?php else: ?>
                            <span class="button button--small button--disabled">Нет онлайн-версии</span>
                        <?php endif; ?>

                        <?php if (!empty($book['can_reserve'])): ?>
                            <form method="post" action="<?= app()->route->getUrl('/catalog/' . $book['id'] . '/reserve') ?>">
                                <button class="button button--small button--ghost" type="submit">
                                    <?= htmlspecialchars($book['reserve_label']) ?>
                                </button>
                            </form>
                        <?php else: ?>
                            <span class="button button--small button--ghost button--disabled">
                                <?= htmlspecialchars($book['reserve_label']) ?>
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
